<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Vars;
use Func;
use Auth;
use Log;
use ExcelFunc;
use FastExcel;
use Excel;
use Image;
use FilFunc;
use Trade;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Config\BatchController;

class LumpInMoneyOneShot extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LumpInMoney:InMoneyOneShot {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '입금일괄소급처리';

    private $requirArray;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // 필수키
        $this->requirArray    = Array(
                                    "loan_info_no"  => "계약번호",
                                    "trade_type"    => "입금구분", 
                                    "trade_date"    => "입금일", 
                                    "trade_money"   => "입금액", 
                                    "bank_cd"       => "입금은행", 
                                    "bank_ssn"      => "입금모계좌"
                                );

        $trade_type_arr = array_flip(Vars::$arrayTradeType);

        // 배치 기록
        $stime = time();
        //$batchLogNo = $this->startBatchLog($stime);

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $file = "";

        // 메인쿼리
        $result = DB::TABLE("lump_master_log")->SELECT("*")->WHERE('division','I')
                                                            ->WHERE('status','W')
                                                            ->ORDERBY('no', 'asc')
                                                            ->first();

        if(!empty($result))
        {
            // 배치 등록직원
            $reg_id       = $result->reg_id;
            $lump_No      = $result->no;
            $total_cnt    = 0;
            $i            = 0;

            $path = $result->excute_file_path."/".$result->excute_file_name;

            if(Storage::disk('public')->exists($path))
            {
                echo "파일 O\n";

                $file = Storage::path('/public/'.$path);

                $colHeader  = array("계약번호","입금구분","입금일","입금액","입금경로","입금은행","입금모계좌","입금자명","처리구분","입금No","화해No","메모");
                $colNm      = array(
                    "loan_info_no"	        => "0",	    // 계약번호
                    "trade_type"            => "1",     // 입금구분
                    "trade_date"            => "2",     // 입금일
                    "trade_money"           => "3",     // 입금액
                    "trade_path_cd"         => "4",     // 입금경로
                    "bank_cd"               => "5",     // 입금은행
                    "bank_ssn"              => "6",     // 입금모계좌
                    "in_name"               => "7",     // 입금자명
                    "process_div"           => "8",     // 처리구분
                    "loan_info_trade_no"    => "9",     // 입금No
                    "loan_settle_no"        => "10",    // 화해No
                    "memo"                  => "11",    // 메모
                );

                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    // 엑셀파일 헤더 불일치
                    //$rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 헤더 불일치 오류','status'=>'E',], ['division'=>'I', 'no'=>$result->no]);
                    
                    echo "엑셀 형식에 맞지않음";
                }
                else
                {
                    // 상태 '진행중' 변경
                    //$rslt = DB::dataProcess("UPD", "lump_master_log", ['status'=>'P',], ['division'=>'I', 'no'=>$result->no]);

                    $EXCEL = Array();
                    $ERROR = Array();
                    $firstDate = Array();
                    $ERROR_CNO = Array();
                    
                    //DB::connection('pgsql')->beginTransaction();

                    foreach($excelData as $_DATA) 
                    {
                        unset($_INS);
                        $arrayCheck = Array();

                        // 데이터 정리
                        foreach($_DATA as $key => $val) 
                        {
                            $val = trim($val);
                            $_INS[$key] = $val;
                        }

                        $total_cnt++;

                        $loan_info_no        = $_INS['loan_info_no'];
                        $_INS['process_div'] = Func::nvl(Vars::$arrayProcess[$_INS['process_div']], '');
                        $_INS['trade_type']  = Func::nvl($trade_type_arr[$_INS['trade_type']], '');

                        $this->trade = new Trade($loan_info_no);
                        
                        $arrayCheck = $this->validCheck($_INS);

                        // 엑셀파일 에러존재시 Continue
                        if(isset($arrayCheck["waring"]))
                        {
                            $_INS['err_msg'] = $arrayCheck['waring'];
                            $ERROR[$i] = $_INS;
                            $i++;
                            
                            continue;
                        }

                        $trade_date = $_INS['trade_date'];
                        $val        = $this->trade->setInterest($_INS['trade_date']);

                        if($_INS['process_div'] == "D" || $_INS['process_div'] == "U") 
                        {

                            if($_INS['process_div'] == "D") 
                            {
                                $delIn[$loan_info_no][] = $arrayCheck[$loan_info_no];
                            }
        
                            $trade_date = $arrayCheck["trade_date"];
                            $_INS["trade_date"] = $trade_date;
                        }                   

                        $firstDate[$loan_info_no][] = $trade_date;

                        // 계약확인
                        $chk_loan =  DB::TABLE("loan_info")->SELECT("*")
                                                            ->WHERE('no', $_INS['loan_info_no'])
                                                            ->WHERE('save_status','Y')
                                                            ->first();
                        
                        $loan_date[$loan_info_no]       = $chk_loan->loan_date;        //매입일
                        $fullpay_date[$loan_info_no]    = $chk_loan->fullpay_date;     //완제일
                        $cust_info_no[$loan_info_no]    = $chk_loan->cust_info_no;     //회원번호
                        $status[$loan_info_no]          = $chk_loan->status;           //상태

                        $_INS['reg_id'] = $reg_id;
                       
                        $EXCEL[$loan_info_no][] = $_INS;

                        ksort($EXCEL);

                        foreach($EXCEL as $key => $arrayValue)
			            {
                            foreach($arrayValue as $index => $value)
				            {
                                unset($total_tail_money);

                                // 계약확인
                                $chk_loan =  DB::TABLE("loan_info")->SELECT("loan_info", "cust_info_no", "fullpay_money")
                                                                    ->WHERE('no', $key)
                                                                    ->WHERE('save_status','Y')
                                                                    ->first();
                                
                                // 완납금액
                                $total_tail_money = $chk_loan->fullpay_money;

                                // 입금액이 잔금보다 더 큰 경우 입금액은 잔금으로 세팅, 나머지 금액은 불명금으로 세팅한다.
                                if( $_INS['trade_money'] > $total_tail_money )
                                {
                                    // 신용회복, 개인회생 건이면서 결재완료 된 건이 하나라도 있고, 입금액이 잔금보다 클경우 무조건 불명금 등록 
                                    $chk_settle =  DB::TABLE("loan_settle")->SELECT("no")
                                                                    ->WHERE('loan_info_no', $key)
                                                                    ->WHERE('cust_info_no', $chk_loan->cust_info_no)
                                                                    ->WHERE('save_status','Y')
                                                                    ->first();
                                    if(isset($chk_settle))
                                    {
                                        unset($n);

                                        $n['cust_info_no']	    = $chk_loan->cust_info_no;					// 회원번호
                                        $n['bank_ssn']			= $_INS['bank_ssn'];		                // 모계좌번호
                                        $n['trade_path_cd']     = $_INS['trade_path_cd'];					// 입금경로
                                        $n['in_name'] 			= $_INS['in_name'];						    // 입금이름
                                        $n['trade_money']		= $_INS['trade_money'];					    // 불명금액
                                        $n['trade_date']		= $_INS['trade_date'];						// 거래일자
                                        $n['save_id']   		= $reg_id;									// 작업자
                                        $n['save_time'] 		= date("YmdHis");							// 저장시간
                                        $n['save_status'] 		= "Y";										// 저장상태
                                        $n['status'] 			= "A";										// 상태 (A : 미해결)
                                        $n['reg_div']			= "U";										// 계약번호
                                        $n['loan_info_nos']		= $key;										// 계약번호
                                        $n['memo'] 				= "개인회생,신용회복 OPB금액 초과입금";		   // 불명금메모
                                        $n['div'] 				= 'D';										// 불명금등록구분 (D : 가수금발생불명금)
                                        $n['origin_trade_money']= $n['trade_money'];						// 원입금액 2021-07-06 김효진

                                        $value['waring'] = "계약번호 : ".$key." 개인회생,신용회복 OPB금액 초과입금이므로 불명금등록";
							            $ERROR[] = $value;
                                        
                                        // 알림쪽지
                                        // member_send_msg("SYSTEM", $value["manager_id"], "OPB금액 초과입금 불명금등록", "계약번호 : ".$key." 개인회생,신용회복 OPB금액 초과입금이므로 불명금등록", "", "", "", "", "");
	
                                        unset($EXCEL[$key]);
                                        continue;
                                    }
                                }



                                if($_INS['process_div'] == 'A' && ($fullpay_date[$key] || $status[$key] == 'E'|| $status[$key] == 'H' || $status[$key] =='M') && $_INS['trade_money'] > 0)
					            {
                                    unset($n);

                                    $n['cust_info_no']	    = $chk_loan->cust_info_no;					// 회원번호
                                    $n['bank_ssn']			= $_INS['bank_ssn'];		                // 모계좌번호
                                    $n['trade_path_cd']     = $_INS['trade_path_cd'];					// 입금경로
                                    $n['in_name'] 			= $_INS['in_name'];						    // 입금이름
                                    $n['trade_money']		= $_INS['trade_money'];					    // 불명금액
                                    $n['trade_date']		= $_INS['trade_date'];						// 거래일자
                                    $n['save_id']   		= $reg_id;									// 작업자
                                    $n['save_time'] 		= date("YmdHis");							// 저장시간
                                    $n['save_status'] 		= "Y";										// 저장상태
                                    $n['status'] 			= "A";										// 상태 (A : 미해결)
                                    $n['reg_div']			= "U";										// 계약번호
                                    $n['loan_info_nos']		= $key;										// 계약번호
                                    $n['memo'] 				= "완제일 이후 입금 불명금";		          // 불명금메모
                                    $n['div'] 				= 'D';										// 불명금등록구분 (D : 가수금발생불명금)

                                    $value['waring'] = "계약번호 : ".$key." 완제상태이므로 불명금등록";
                                    $ERROR[] = $value;

                                    unset($EXCEL[$key]);
                                    continue;
                                }
                            }
                        }

                        $tradeType = ["C" => "LOAN_INFO_LAW_COST"];
			            $columns = Array(
                                            "C" => "*",
                                        );

                        // 거래 삭제
                        foreach($EXCEL as $key => $arrayValue)
                        {
                            $arrayApply = Array();
                            $save_pay_arr = Array();

                            $trade_date = min($firstDate[$key]);
                            $max_trade_date = max($firstDate[$key]);
                            $del_time = time();

                            $loan_info_no = $key;
                            $before = $trade_date;
                            $dailyCheck = "";
                            unset($settle_type);
                            unset($min_trade_date);		//	JACK
                            unset($sort);
                            $_LOSE = array();
                            
                            //최초거래일
                            $min_trade_date = $trade_date;

                            // 거래원장 갱신일자를 정한다.
                            $Build_date = $min_trade_date;
                            $rcnt = 0;
                            
                            echo $min_trade_date;
                            // 계약확인
                            $rslt =  DB::TABLE("loan_info_trade")->SELECT("*")
                                                                ->WHERE('loan_info_no', $key)
                                                                ->WHERE('trade_date', '>=', $min_trade_date)
                                                                ->WHERE('save_status','Y')
                                                                ->ORDERBY('no')
                                                                ->ORDERBY('trade_date')
                                                                ->ORDERBY('save_time')
                                                                ->get();
                            $rslt = Func::chungDec(["loan_info_trade"], $rslt);	// CHUNG DATABASE DECRYPT
                            
                            foreach ($rslt as $v)
                            {
                                unset($del_not_flag, $up_del_type);

                                // 매입거래는 건드리지 않는다.
                                if($loan_date[$loan_info_no] == $v->trade_date && $v->trade_div == "O")
                                {
                                    continue;
                                }

                                $excelKey = array();

                                //	JACK 추가
                                $rcnt++;

                                if($v["trade_type"] == "P")
                                {
                                    $WhereNo = "pre_money_key = ".$v["table_no"];
                                } else {
                                    $WhereNo = "no = ".$v["table_no"];
                                }

                                // 계약확인
                                $rslt =  DB::TABLE("loan_info_trade")->SELECT("*")
                                                                    ->WHERE('loan_info_no', $key)
                                                                    ->WHERE('no', $v->no)
                                                                    ->WHERE('save_status','Y')
                                                                    ->first();

                                $rslt = DB::dataProcess("UPD", "loan_info_trade", ['save_status'=>'N','del_id'=> $reg_id, 'del_time'=> date("YmdHis")], 
                                ['loan_info_no'=> $key , 'no'=>$rslt->no]);

                            }
                        }
                    }

                    //DB::connection('pgsql')->commit();

                    echo "성공";
                }
            }
            else 
            {
                echo "파일없음\n";
            }

        }
        else
        {
            echo "대기중인 입금일괄처리 미존재\n";
        }
        
        

        dd('end');

        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.

        if(!empty($ERROR) && count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'inmoney_oneshot');

            $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);

            $file_path = $file_arr[1]."/".$file_arr[2];    //오류파일경로
            $file_name = $file_arr[3];                                      //오류파일이름

            $rslt = DB::dataProcess('UPD', "lump_master_log", ['fail_file_path'=>$file_path,'fail_file_name'=>$file_name], ['no'=>$result->no]);
	    }

        // 배치 종료 기록
        // if($batchLogNo>0)
        // {
        //     $note = '';
        //     BatchController::setBatchLog($this->argument('batchNo'), $batchLogNo, $note, $stime);
        // }
    }

    public function validCheck($value) 
    {

        $waring = "";
        $return = Array();
    
        if(!in_array($value['process_div'], Vars::$arrayProcess)) 
        {
            $waring = "유효한 처리구분이 없습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 계약확인
        $chk_loan =  DB::TABLE("loan_info")->SELECT("*")
                                            ->WHERE('no', $value['loan_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(empty($chk_loan))
        {
            $waring = "B:유효한 계약이 없습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        if($value['process_div'] == "A" || $value['process_div'] == "U" || $value['process_div'] == "L1")
        {
            // 수정인경우 입금일, 입금구분, 입금액 필수키 제외
            if($value['process_div'] == "U") unset($this->requirArray['trade_date'], $this->requirArray['trade_type'], $this->requirArray['trade_money']); 

            // 손실일 경우 입금은행,입금모계좌 제외
            if($value['process_div'] == "L1") unset($this->requirArray['bank_cd'], $this->requirArray['bank_ssn']);

            foreach($this->requirArray as $chk_key => $chk_val)
            {
                if($value[$chk_key] == '')
                {
                    $waring = "필수값 체크 : ".$chk_val." 확인필요"; 
                    $return["waring"] = $waring;
                    return $return;
                }
            }

            if($value['process_div'] == "A")
            {
                if(!is_numeric($value["loan_info_no"]))
                {
                    $waring = "계약번호 형식 에러입니다.";
                    $return["waring"] = $waring;
                    return $return;
                }

                $value['trade_date'] = str_replace("-", "", $value['trade_date']);

                if($value['trade_date'] < $this->trade->loan->loanInfo['take_date'] )
                {
                    $waring = "입금일은 이전거래일보다 이전으로 등록 할 수 없습니다.";
                    $return["waring"] = $waring;
                    return $return;
                }

                if($chk_loan->status=="S" || $chk_loan->status=="M" || $chk_loan->status=="H" ) 
                {
                    $waring = "상각/매각/환매채권은 처리가 불가능합니다.";
                    $return["waring"] = $waring;
                    return $return;
                }
                
                // 화해입금
                if( $value["trade_type"] == "08" || $value["trade_type"] == "09")
                {
                    if(empty(["loan_settle_no"]))
                    {
                        $waring = "화해입금의 경우 화해번호를 입력해주세요.";
                        $return["waring"] = $waring;
                        return $return;
                    }
                    else
                    {
                        // 계약확인
                        $chk_settle=  DB::TABLE("loan_settle")->SELECT("no")
                                                                ->WHERE('no', $value['loan_info_no'])
                                                                ->WHERE('save_status','Y')
                                                                ->first();

                        if(empty($chk_settle))
                        {
                            $waring = "유효한 화해번호를 입력해주세요.";
                            $return["waring"] = $waring;
                            return $return;
                        }
                    }
                }

                // 2021-06-07 김효진 입금일 유효성 검사 추가
                if( !preg_match("/^([0-9]{4})([0-9]{2})([0-9]{2})$/", $value["trade_date"], $match) || 
                    !checkdate($match[2], $match[3], $match[1]) ) 
                {
                    $waring = "날짜 형식 에러입니다.";
                    $return["waring"] = $waring;
                    return $return;
                } 
                else 
                {
                    if($value["trade_date"] > date("Ymd")) 
                    {
                        $waring = "입금일이 오늘 날짜보다 큽니다.";
                        $return["waring"] = $waring;
                        return $return;
                    }
                }
                
                if(date("Ymd", strtotime($chk_loan->loan_date)) > $value["trade_date"]) 
                {
                    $waring = "매입출금 이전 일자로는 입금할 수 없습니다.";
                    $return["waring"] = $waring;
                    return $return;
                }

                if(str_replace(",", "", $value["trade_money"]) <= 0) 
                {
                    $waring = "입금액을 입력해주세요.";
                    $return["waring"] = $waring;
                    return $return;
                }

                /*
                if(!in_array($value["in_bank"], $arrayAccount)) 
                {
                    $waring .= "K:유효한 입금모계좌를 입력해주세요.";
                }

                */
            }
            elseif($value['process_div'] == "U")
            {

                if(is_numeric($value["loan_info_trade_no"])) 
                {
                    // 계약확인
                    $chk_trade=  DB::TABLE("loan_info_trade")->SELECT("cust_info_no", "trade_date")
                                                                ->WHERE('no', $value['loan_info_trade_no'])
                                                                ->WHERE('loan_info_no', $value['loan_info_no'])
                                                                ->WHERE('save_status','Y')
                                                                ->first();
                    
                    if(!empty($chk_trade))
                    {
                        $return["cust_info_no"] = $chk_trade->cust_info_no;
                        $return["trade_date"] = $chk_trade->trade_date;
                    }
                    else
                    {
                        $waring = "해당 입금번호가 존재하지않습니다.";
                        $return["waring"] = $waring;
                        return $return;
                    }

                    if($value["trade_date"]) 
                    {
                        $waring = "입금일은 수정할 수 없습니다.";
                        $return["waring"] = $waring;
                        return $return;
                    }
                }
                else
                {
                    $waring = "유효한 입금NO를 입력해주세요.";
                    $return["waring"] = $waring;
                    return $return;
                }

                // 화해입금
                if( $value["trade_type"] == "08" || $value["trade_type"] == "09")
                {
                    if(empty(["loan_settle_no"]))
                    {
                        $waring = "C,O : 화해입금의 경우 화해번호를 입력해주세요.";
                        $return["waring"] = $waring;
                        return $return;
                    }
                    else
                    {
                        // 계약확인
                        $chk_settle=  DB::TABLE("loan_settle")->SELECT("no")
                                                                ->WHERE('no', $value['loan_info_no'])
                                                                ->WHERE('save_status','Y')
                                                                ->first();

                        if(empty($chk_settle))
                        {
                            $waring = "유효한 화해번호를 입력해주세요.";
                            $return["waring"] = $waring;
                            return $return;
                        }
                    }
                }
            }
            elseif($value['process_div'] == "L1")
            {

            }
        }
        else
        {
            if(is_numeric($value["loan_info_trade_no"])) 
            {

                // 계약확인
                $chk_trade=  DB::TABLE("loan_info_trade")->SELECT("no")
                                                        ->WHERE('no', $value['loan_info_trade_no'])
                                                        ->WHERE('loan_info_no', $value['loan_info_no'])
                                                        ->WHERE('save_status','Y')
                                                        ->first();
                                                        
    
                if(!empty($chk_trade)) 
                {
                    $return["cust_info_no"] = $chk_trade->cust_info_no;
                    $return["trade_date"] = $chk_trade->trade_date;
                    $return[$value["loan_info_no"]] = $chk_trade->no;
    
                } 
                else 
                {		
                    $waring = "유효한 입금이 없습니다.";
                    $return["waring"] = $waring;
                    return $return;
                }
            } 
            else 
            {
                $waring = "유효한 입금NO를 입력해주세요.";
                $return["waring"] = $waring;
                return $return;
            }

        }

        return $return;
    }

    // 배치로그 시작
    public function startBatchLog($stime)
    {
        $batchNo = $this->argument('batchNo');
        $batchLogNo =  0;
        if(!empty($batchNo))
        {
            $batchLogNo = BatchController::setBatchLog($batchNo, 0, '', $stime);
        }

        return $batchLogNo;
    }
}
