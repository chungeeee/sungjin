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
use App\Chung\ExcelCustomExport;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Config\BatchController;

class LumpInMoneyUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LumpInMoney:InMoneyUpdate {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '입금배치등록';

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
        $this->requirArray = Array('cust_info_no' => '회원번호', 
                                    'loan_info_no' => '계약번호', 
                                );
        
        // 코드관리
        $this->tradeInPathArray = array_flip(Func::getConfigArr('trade_in_path'));

        // 배치 기록
        $stime = time();
        $batchLogNo = $this->startBatchLog($stime);

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $file = "";
        $ERROR = Array();

        // 메인쿼리
        $result = DB::TABLE("lump_master_log")->SELECT("*")->WHERE('division','I')
                                                         ->WHERE('status','W')
                                                         ->ORDERBY('no', 'asc')
                                                         ->first();
        
        if(!empty($result))
        {
            // 배치 등록직원
            $reg_id = $result->reg_id;
            $lump_No = $result->no;

            $path = $result->excute_file_path."/".$result->excute_file_name;

            $total_cnt = 0; 
            $i = 0;
            if(Storage::disk('public')->exists($path))
            {
                echo "파일 O\n";

                $file = Storage::path('/public/'.$path);

                $colHeader  = array('회원번호', '계약번호', '주민등록번호', '이름', '입금액', '입금일자', '입금경로', '입금자명');
                $colNm      = array(
                    'cust_info_no'	    => '0',	    // 회원번호(필수)
                    'loan_info_no'	    => '1',	    // 계약번호(필수)
                    'ssn'	            => '2',	    // 주민등록번호
                    'name'              => '3',     // 이름
                    'trade_money'       => '4',     // 입금액
                    'trade_date'        => '5',     // 입금일자
                    'in_path'           => '6',     // 입금경로
                    'in_name'	        => '7',	    // 입금자명
                );

                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    // 엑셀파일 헤더 불일치
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 헤더 불일치 오류','status'=>'X',], ['division'=>'I', 'no'=>$result->no]);
                }
                else
                {
                    // 상태 '진행중' 변경
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['status'=>'P','start_time'=>time()], ['division'=>'I', 'no'=>$result->no]);

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

                        
                        // 회원번호에 zb를 붙여서 업로드할경 제거
                        if(strpos($_INS['cust_info_no'], 'zb') !== false)
                        {
                            $_INS['cust_info_no'] = str_replace('zb', '', $_INS['cust_info_no']);
                        }

                        $arrayCheck = $this->validCheck($_INS);

                        if(isset($arrayCheck["waring"]))
                        {
                            $_INS['err_msg'] = $arrayCheck["waring"];
                            $ERROR[$i] = $_INS;
                            $i++;
                            
                            continue;
                        }

                        $_INS['lose_money']  = 0;
                        $_INS['trade_date']  = str_replace('-', '', $_INS['trade_date']);
                        $_INS['trade_money'] = str_replace(',', '', $_INS['trade_money']);
                        
                        $trade = new Trade($_INS['loan_info_no']);

                        //$val = $trade->setInterest($_INS['trade_date']);

                        $_INS['trade_path_cd']    = $this->tradeInPathArray[$_INS['in_path']];
                        $_INS['trade_type']       = '01';
                        $_INS['bank_cd']          = '';
                        $_INS['bank_ssn']         = '';
                        $_INS['manager_code']     = $trade->loan->loanInfo['manager_code'];
                        $_INS['manager_id']       = $trade->loan->loanInfo['manager_id'];
                        $_INS['save_status']      = "Y";
                        $_INS['save_time']        = date('YmdHis');
                        $_INS['save_id']          = $reg_id;

                        // 입금데이터 생성
                        $_INS['action_mode'] = "INSERT";
                        $rslt = $trade->tradeInInsert($_INS, $reg_id);
                        // 정상 처리될 경우, loan_info_trade의 no가 응답, 오류인경우 오류 메세지 응답
                        if( !is_numeric($rslt) )
                        {
                            Log::debug('입금 INSERT Error');

                            $_INS['err_msg'] = '입금실패, 관리자에게 문의주세요.';
                            $ERROR[$i] = $_INS;
                            $i++;
                        }
                    }

                    $rslt = DB::dataProcess('UPD', 'lump_master_log', ["status"=>"C","finish_time"=>time(),"total_count"=>$total_cnt, "ok_count"=>(($total_cnt)-count($ERROR)), "fail_count"=>count($ERROR), "remark"=>count($ERROR)."건 실패"], ["no"=>$result->no]);
                }
            }
            else
            {
                $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 읽기오류','status'=>'X',], ['division'=>'I', 'no'=>$result->no]);
                echo "엑셀파일 미존재\n";
            }
        }
        else
        {
            echo "대기중인 입금배치 미존재\n";
        }



        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'inmoney');

            $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);
            $file_path = $file_arr[1]."/".$file_arr[2];
            $file_name = $file_arr[3];

            $rslt = DB::dataProcess("UPD", "lump_master_log", ['fail_file_path'=>$file_path,'fail_file_name'=>$file_name], ['no'=>$result->no]);
	    }
        
        //배치 종료 기록
        if($batchLogNo>0)
        {
            $note = '';
            BatchController::setBatchLog($this->argument('batchNo'), $batchLogNo, $note, $stime);
        }
    }

    public function validCheck($value) 
    {

        $waring = "";
        $return = Array();

        // 필수값 확인
        foreach($this->requirArray as $chk_key => $chk_val)
        {
            if($value[$chk_key] == '')
            {
                $waring = "필수값 체크 : ".$chk_val." 확인필요"; 
                $return["waring"] = $waring;
                return $return;
            }
        }

        // 회원번호에 zb를 붙여서 업로드할경 제거
        if(strpos($value['cust_info_no'], 'zb') !== false)
        {
            $value['cust_info_no'] = str_replace('zb', '', $value['cust_info_no']);
        }

        // 계약확인
        $chk_cust =  DB::TABLE("loan_info")->SELECT("*")
                                            ->WHERE('no', $value['loan_info_no'])
                                            ->WHERE('cust_info_no', $value['cust_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(empty($chk_cust))
        {
            $waring = "계약번호 및 회원번호를 확인해주세요.";
            $return["waring"] = $waring;
            return $return;
        }

        // 날짜형식확인
        if(!empty($value['trade_date']))
        {
            $val = date('Ymd', strtotime($value['trade_date']));

            if(!checkdate(substr($val, 4, 2), substr($val, 6, 2), substr($val, 0, 4)))
            {
                $waring = "[데이터오류] 날짜형식"; 
                $return["waring"] = $waring;
                return $return;
            }

            $value['trade_date'] = str_replace('-', '', $value['trade_date']);

            // 이후거래확인
            $chk_cust =  DB::TABLE("loan_info_trade")->SELECT("count(1) as cnt")
                                                        ->WHERE('loan_info_no', $value['loan_info_no'])
                                                        ->WHERE('trade_date', '>', $value['trade_date'])
                                                        ->WHERE('save_status','Y')
                                                        ->first();
            if( $chk_cust->cnt > 0 )
            {
                $waring = "등록일자 이후 거래가 존재합니다";
                $return["waring"] = $waring;
                return $return;
            }
        }
        
        // 입금경로 확인
        if(!in_array($value['in_path'], Func::getConfigArr('trade_in_path'))) 
        {
            $waring = "유효한 입금경로가 없습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 입금액확인
        if($value['trade_money'] <= 0 )
        {
            $waring = "입금액을 올바르게 입력바랍니다."; 
            $return["waring"] = $waring;
            return $return;
        }
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
