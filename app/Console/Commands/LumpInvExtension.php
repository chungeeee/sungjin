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
use FilFunc;
use Invest;
use App\Chung\ExcelCustomExport;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Config\BatchController;

class LumpInvExtension extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LumpExtension:Inv {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '투자 연장';

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
        $this->requirArray = Array('loan_info_no' => '계약번호', 
                                    'loan_usr_info_no' => '투자자일련번호',
                                    'trade_date' => '투자개시일',
                                    'new_contract_end_date' => '연장만기일',
                                    'contract_day' => '이수일',
                                    'pay_term' => '상환주기',
                                    'ratio' => '수익률'
                                );  

        // 배치 기록
        $stime = time();
        $batchLogNo = $this->startBatchLog($stime);

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $file = "";
        $ERROR = Array();

        // 메인쿼리
        $result = DB::TABLE("lump_master_log")->SELECT("*")->WHERE('division','EI')
                                                         ->WHERE('status','W')
                                                         ->ORDERBY('no', 'asc')
                                                         ->first();
        
        if(!empty($result))
        {
            // 배치 등록직원
            $reg_id = $result->reg_id;
            $lump_No = $result->no;

            $path = $result->excute_file_path."/".$result->excute_file_name;
            
            $save_time = date("YmdHis");

            $total_cnt = 0; 
            $i = 0;
            if(Storage::disk('public')->exists($path))
            {
                echo "파일 O\n";

                $file = Storage::path('/public/'.$path);

                $colHeader  = array('투자자일련번호','계약번호','투자개시일','연장만기일','이수일','상환주기','수익률');
                $colNm      = array(
                    'loan_usr_info_no'	    => '0',	    // 투자자일련번호
                    'loan_info_no'          => '1',	    // 계약번호
                    'trade_date'            => '2',     // 투자개시일
                    'new_contract_end_date' => '3',     // 연장만기일
                    'contract_day'          => '4',     // 이수일
                    'pay_term'              => '5',     // 상환주기
                    'ratio'                 => '6',     // 수익률
                );

                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    // 엑셀파일 헤더 불일치
                    Log::debug("엑셀파일 헤더 불일치\n");
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 헤더 불일치 오류','status'=>'X',], ['division'=>'EI', 'no'=>$result->no]);
                }
                else
                {
                    // 상태 '진행중' 변경
                    Log::debug("상태 '진행중' 변경\n");
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['status'=>'P','start_time'=>time()], ['division'=>'EI', 'no'=>$result->no]);

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
                        $arrayCheck = $this->validCheck($_INS);

                        if(isset($arrayCheck["waring"]))
                        {
                            $_INS['err_msg'] = $arrayCheck["waring"];
                            $ERROR[$i] = $_INS;
                            $i++;
                            
                            continue;
                        }

                        $_DATA['trade_date'] = str_replace('-', '', $_DATA['trade_date']);
                        $_DATA['new_contract_end_date'] = str_replace('-', '', $_DATA['new_contract_end_date']);

                        $loan = DB::TABLE('loan_info')->SELECT('no')
                                                    ->WHERE('save_status','Y')
                                                    ->WHERE('no',$_DATA['loan_info_no'])
                                                    ->FIRST();
                        // 계약확인
                        if(empty($loan->no))
                        {
                            $_INS['err_msg'] = "해당계약이 존재하지않습니다.";
                            $ERROR[$i] = $_INS;
                            $i++;

                            continue;
                        }

                        $rs = DB::TABLE('loan_info')
                                ->WHERE('loan_info.save_status','Y')
                                ->WHEREIN('loan_info.status', ['A','B'])
                                ->WHERE('loan_info.balance','>',0)
                                ->WHERE('loan_info.no',$_DATA['loan_info_no'])
                                ->WHERE('loan_info.loan_usr_info_no',$_DATA['loan_usr_info_no'])
                                ->WHERE('loan_info.contract_end_date', '>', date("Ymd"));
                                                       
                        $endCheck = $rs->COUNT();
                        // 만기도래전
                        if($endCheck > 0)
                        {
                            $_INS['err_msg'] = "만기일이 도래하지 않은 투자건이 존재합니다.";
                            $ERROR[$i] = $_INS;
                            $i++;

                            continue;
                        }
                        
                        // 23-11-02 임세린 과장 요청 : 개발테스트에서는 테스트를위해 주석처리
                        //                            운영에 적용시에 해당 주석은 제거
                        /*
                        $rs = DB::TABLE('loan_info')->JOIN('loan_info_return_plan','loan_info.no','=','loan_info_return_plan.loan_info_no')
                                                    ->WHERE('loan_info.save_status','Y')
                                                    ->WHEREIN('loan_info.status', ['A','B'])
                                                    ->WHERE('loan_info.balance','>',0)
                                                    ->WHERE('loan_info.no',$_DATA['loan_info_no'])
                                                    ->WHERE('loan_info.loan_usr_info_no',$_DATA['loan_usr_info_no'])
                                                    ->WHERERAW("(loan_info_return_plan.divide_flag is null or loan_info_return_plan.divide_flag != 'Y')");
                                                    
                        $scheduleCheck = $rs->COUNT();
                        // 이자 미지급 스케줄 존재건
                        if($scheduleCheck > 0)
                        {
                            $_INS['err_msg'] = "이자 미지급된 투자건이 존재합니다.";
                            $ERROR[$i] = $_INS;
                            $i++;

                            continue;
                        }
                        */


                        // 데이터 입력
                        DB::beginTransaction();

                        // 기존 투자건 종결처리
                        $rs = DB::TABLE('loan_info')->JOIN('loan_usr_info','loan_usr_info.no','=','loan_info.loan_usr_info_no')
                                ->SELECT('loan_info.loan_money, loan_info.contract_date, loan_info.contract_end_date, loan_info.pro_cd, loan_info.platform_fee_rate, loan_usr_info.tax_free, loan_info.no, loan_info.balance')
                                ->WHERE('loan_info.save_status','Y')
                                ->WHEREIN('loan_info.status', ['A','B'])
                                ->WHERE('loan_info.balance','>',0)
                                ->WHERE('loan_info.no',$_DATA['loan_info_no'])
                                ->WHERE('loan_info.loan_usr_info_no',$_DATA['loan_usr_info_no']);
                                
                        $rs = $rs->GET();

                        // 연장대상 투자잔액
                        $successFlag = "Y";
                        $contract_date = $pro_cd = $tax_free = "";
                        $loan_money = $sum_inv_tail_money = $platform_fee_rate = 0;
                        foreach($rs as $v)
                        {
                            unset($_UP);
                            $_UP['fullpay_date'] = $v->contract_end_date;
                            $_UP['save_id'] = $reg_id;
                            $_UP['save_time'] = $save_time;
                            $_UP['inv_tail_money'] = 0;
                            Log::debug("loan_info UPDATE [".$v->no."]");
                            Log::debug(print_r($_UP, true));
                            $rslt = DB::dataProcess('UPD', 'loan_info', $_UP, ["no"=>$v->no]);
                            
                            if(!isset($rslt) || $rslt != "Y")
                            {
                                $successFlag = "N";
                                $_INS['err_msg'] = "기존 투자 종결처리 실패#1";
                                $ERROR[$i] = $_INS;
                                $i++;

                                DB::rollback();
                                break;
                            }

                            // 종결된 금액 지정
                            $valmoney = [];
                            $valmoney['loan_info_no']          = $v->no;
                            $valmoney['trade_date']      = $v->contract_end_date;
                            $valmoney['inv_tail_money']  = 0;
                            $valmoney['save_status']     = 'Y';
                            $valmoney['save_time']       = $save_time;
                            $valmoney['save_id']         = $reg_id;
                            Log::debug("종결금액 입력 -> INV_TAIL_MONEY");
                            Log::debug(print_r($valmoney, true));
                            $rslt = DB::dataProcess('INS', 'INV_TAIL_MONEY', $valmoney);
                            
                            if( $rslt!="Y" )
                            {
                                $successFlag = "N";
                                $_INS['err_msg'] = "기존 투자 종결처리 실패#2";
                                $ERROR[$i] = $_INS;
                                $i++;

                                DB::rollback();
                                break;
                            }

                            $sum_inv_tail_money+=$v->inv_tail_money;
                            $platform_fee_rate = $v->platform_fee_rate;
                            $loan_money = $v->loan_money;
                            $contract_date = $v->contract_date;
                            $pro_cd = $v->pro_cd;
                            $tax_free = $v->tax_free;
                        }

                        // 상단에서 기존 투자건 종결처리 완료되면 실행. 실패시는 돌리지 않는다.
                        if($successFlag=="Y")
                        {
                            $_INV = [];
                            $_INV['loan_usr_info_no'] = $_DATA['loan_usr_info_no'];
                            $_INV['loan_info_no'] = $_DATA['loan_info_no'];
                            $_INV['loan_money'] = $loan_money;
                            $_INV['trade_money'] = $_INV['inv_tail_money'] = $sum_inv_tail_money;
                            $_INV['trade_date'] = $_DATA['trade_date'];
                            $_INV['contract_date'] = $contract_date;
                            $_INV['contract_end_date'] = $_DATA['new_contract_end_date'];
                            $_INV['status'] = 'E';
                            $_INV['save_status'] = "Y";
                            $_INV['save_id'] = $reg_id;
                            $_INV['save_time'] = $save_time;
                            $_INV['fullpay_date'] = $_DATA['new_contract_end_date'];
                            $_INV['pro_cd'] = $pro_cd;
                            $_INV['platform_fee_rate'] = $platform_fee_rate;
                            $_INV['ratio'] = $_DATA['ratio'];
                            $_INV['contract_day'] = $_DATA['contract_day'];
                            $_INV['pay_term'] = $_DATA['pay_term'];
                            $_INV['tax_free'] = $tax_free;
                            $_INV['ph_chk'] = 'ph1';
                            $_INV['addr_chk'] = 'addr1';
                            $_INV['bank_chk'] = 'bank1';
                                 
                            Log::debug("투자입력 -> loan_info");
                            Log::debug(print_r($_INV, true));
                            $rslt = DB::dataProcess('INS', 'loan_info', $_INV, null, $loan_info_no);
                            $_INV['no'] = $loan_info_no;
                            
                            if(!isset($rslt) || $rslt != "Y")
                            {
                                $_INS['err_msg'] = "연장투자 내역 생성 실패#1";
                                $ERROR[$i] = $_INS;
                                $i++;

                                DB::rollback();
                                continue;
                            }

                            $valratio = [];
                            $valratio['loan_info_no']          = $loan_info_no;
                            $valratio['rate_date']       = $_DATA['trade_date'];
                            $valratio['ratio']           = $_DATA['ratio'];
                            $valratio['save_status']     = 'Y';
                            $valratio['save_time']       = $save_time;
                            $valratio['save_id']         = $reg_id;
                            Log::debug("투자 수익률 로그 입력 -> INV_RATIO");
                            Log::debug(print_r($valratio, true));
                            $rslt = DB::dataProcess('INS', 'INV_RATIO', $valratio);
                            
                            if(!isset($rslt) || $rslt != "Y")
                            {
                                $_INS['err_msg'] = "연장투자 내역 생성 실패#2";
                                $ERROR[$i] = $_INS;
                                $i++;

                                DB::rollback();
                                continue;
                            }

                            $valfee = [];
                            $valfee['loan_info_no']          = $loan_info_no;
                            $valfee['rate_date']       = $_DATA['trade_date'];
                            $valfee['platform_fee_rate'] = 0;
                            $valfee['save_status']     = 'Y';
                            $valfee['save_time']       = $save_time;
                            $valfee['save_id']         = $reg_id;
                            Log::debug("투자 플랫폼 수수료율 로그 입력 -> platform_fee_rate");
                            Log::debug(print_r($valfee, true));
                            $rslt = DB::dataProcess('INS', 'platform_fee_rate', $valfee);
                            $rslt = "Y";
                            
                            if(!isset($rslt) || $rslt != "Y")
                            {
                                $_INS['err_msg'] = "연장투자 내역 생성 실패#3";
                                $ERROR[$i] = $_INS;
                                $i++;

                                DB::rollback();
                                continue;
                            }

                            $valmoney = [];
                            $valmoney['loan_info_no']          = $loan_info_no;
                            $valmoney['trade_date']      = $_DATA['trade_date'];
                            $valmoney['inv_tail_money']  = $sum_inv_tail_money;
                            $valmoney['save_status']     = 'Y';
                            $valmoney['save_time']       = $save_time;
                            $valmoney['save_id']         = $reg_id;
                            Log::debug("투자잔액 로그 입력 -> INV_TAIL_MONEY");
                            Log::debug(print_r($valmoney, true));
                            $rslt = DB::dataProcess('INS', 'INV_TAIL_MONEY', $valmoney);

                            if(!isset($rslt) || $rslt != "Y")
                            {
                                $_INS['err_msg'] = "연장투자 내역 생성 실패#4";
                                $ERROR[$i] = $_INS;
                                $i++;

                                DB::rollback();
                                continue;
                            }

                            // 분배예정스케줄 생성
                            $inv = new Invest($_INV); 
                            $array_plan = $inv->buildPlanData($_DATA['trade_date']);
                            Log::debug("스케줄 생성 -> loan_info_return_plan");
                            Log::debug(print_r($array_plan, true));
                            $rslt = $inv->savePlan($array_plan, $_DATA['trade_date']);

                            if(!isset($rslt) || $rslt != "Y")
                            {
                                $_INS['err_msg'] = "연장투자 내역 생성 실패#5";
                                $ERROR[$i] = $_INS;
                                $i++;

                                DB::rollback();
                                continue;
                            }
                        }


                        DB::commit();
                    }

                    $rslt = DB::dataProcess('UPD', 'lump_master_log', ["status"=>"C","finish_time"=>time(),"total_count"=>$total_cnt, "ok_count"=>(($total_cnt)-count($ERROR)), "fail_count"=>count($ERROR), "remark"=>count($ERROR)."건 실패"], ['division'=>'EI', "no"=>$result->no]);
                }
            }
            else
            {
                $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 읽기오류','status'=>'X',], ['division'=>'EI', 'no'=>$result->no]);
                echo "엑셀파일 미존재\n";
            }
        }
        else
        {
            echo "대기중인 투자연장 배치 미존재\n";
        }



        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'interest_payment');

            $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);
            $file_path = $file_arr[1]."/".$file_arr[2];
            $file_name = $file_arr[3];

            $rslt = DB::dataProcess("UPD", "lump_master_log", ['fail_file_path'=>$file_path,'fail_file_name'=>$file_name], ['division'=>'EI', 'no'=>$result->no]);
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

        // 금액확인
        if(!empty($value['interest_money']))
        {
            if(!is_numeric(str_replace(",", "", $value['interest_money'])))
            {
                $waring = "[데이터오류] 금액 형식"; 
                $return["waring"] = $waring;
                return $return;
            }
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
