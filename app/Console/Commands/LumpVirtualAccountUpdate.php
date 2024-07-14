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
use App\Chung\ExcelCustomExport;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Config\BatchController;

class LumpVirtualAccountUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'VirtualAccount:VirtualAccountUpdate {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '가상계좌일괄등록';


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
        $this->requirArray = Array('va_flag'        => '구분', 
                                   'cust_info_no'   => '회원번호',
                                   'loan_info_no'   => '계약번호',
                                );

        // 은행코드배열
        $this->bankCodeArray = array_flip(Func::getConfigArr('bank_cd'));

        // 배치 기록
        $stime = time();
        $batchLogNo = $this->startBatchLog($stime);

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $file = "";
        $ERROR = Array();
        $_INS_VIR = Array();
        $_UPD_VIR = Array();
        $_UPD_LOAN = Array();
        

        // 메인쿼리
        $result = DB::TABLE("lump_master_log")->SELECT("*")->WHERE('division','VA')
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

                $colHeader  = array('구분', '회원번호', '계약번호', '가상계좌번호', '은행명');
                $colNm      = array(
                    'va_flag'	            => '0',	    // 구분
                    'cust_info_no'	        => '1',	    // 회원번호
                    'loan_info_no'          => '2',     // 계약번호
                    'vir_acct_ssn'          => '3',     // 가상계좌번호
                    'vir_acct_mo_bank_cd'   => '4',     // 은행명
                );

                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    // 엑셀파일 헤더 불일치
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 헤더 불일치 오류','status'=>'X',], ['division'=>'VA', 'no'=>$result->no]);
                }
                else
                {
                    // 상태 '진행중' 변경
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['status'=>'P','start_time'=>time()], ['division'=>'VA', 'no'=>$result->no]);

                    foreach($excelData as $_DATA) 
                    {
                        unset($_UPD);
                        $arrayCheck = Array();

                        // 데이터 정리
                        foreach($_DATA as $key => $val) 
                        {
                            $val = trim($val);
                            $_UPD[$key] = $val;
                        }

                        $total_cnt++;

                        // 회원번호에 zb를 붙여서 업로드할경 제거
                        if(strpos($_UPD['cust_info_no'], 'zb') !== false)
                        {
                            $_UPD['cust_info_no'] = str_replace('zb', '', $_UPD['cust_info_no']);
                        }

                        $arrayCheck = $this->validCheck($_UPD);

                        if(isset($arrayCheck["waring"]))
                        {
                            $_UPD['err_msg'] = $arrayCheck["waring"];
                            $ERROR[$i] = $_UPD;
                            $i++;
                            
                            continue;
                        }

                        $_UPD_LOAN['vir_acct_ssn']          = $_UPD['vir_acct_ssn'];
                        $_UPD_LOAN['vir_acct_mo_bank_cd']   = $this->bankCodeArray[$_UPD['vir_acct_mo_bank_cd']];

                        if($_UPD['va_flag'] == "등록")
                        {     
                            $rslt = DB::dataProcess('UPD', 'loan_info', $_UPD_LOAN, ['no'=>$_UPD['loan_info_no'], 'cust_info_no'=>$_UPD['cust_info_no']]);

                            $_INS_VIR['cust_info_no']   = $_UPD['cust_info_no'];
                            $_INS_VIR['loan_info_no']   = $_UPD['loan_info_no'];
                            $_INS_VIR['vir_acct_ssn']   = $_UPD['vir_acct_ssn'];
                            $_INS_VIR['bank_cd']        = $this->bankCodeArray[$_UPD['vir_acct_mo_bank_cd']];
                            $_INS_VIR['worker_id']      = $reg_id;
                            $_INS_VIR['save_time']      = date('YmdHis');
                            $_INS_VIR['save_status']    = 'Y';
                            $_INS_VIR['reg_date']       = date('Ymd');

                            $rslt = DB::dataProcess('INS', 'vir_acct', $_INS_VIR);                                    
                        }
                        elseif($_UPD['va_flag'] == "수정")
                        {
                            $rslt = DB::dataProcess('UPD', 'loan_info', $_UPD_LOAN, ["no"=>$_UPD['loan_info_no'], 'cust_info_no'=>$_UPD['cust_info_no']]);

                            $_UPD_VIR['vir_acct_ssn']   = $_UPD['vir_acct_ssn'];
                            $_UPD_VIR['bank_cd']        = $this->bankCodeArray[$_UPD['vir_acct_mo_bank_cd']];
                            $_UPD_VIR['worker_id']      = $reg_id;
                            $_UPD_VIR['save_time']      = date('YmdHis');

                            $rslt = DB::dataProcess('UPD', 'vir_acct', $_UPD_VIR, ['loan_info_no'=>$_UPD['loan_info_no'], 'cust_info_no'=>$_UPD['cust_info_no']]);
                        }
                    }

                    $rslt = DB::dataProcess('UPD', 'lump_master_log', ["status"=>"C","finish_time"=>time(),"total_count"=>$total_cnt, "ok_count"=>(($total_cnt)-count($ERROR)), "fail_count"=>count($ERROR), "remark"=>count($ERROR)."건 실패"], ["no"=>$result->no]);
                }
            }
            else
            {
                $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 읽기오류','status'=>'X',], ['division'=>'VA', 'no'=>$result->no]);
                echo "엑셀파일 미존재\n";
            }
        }
        else
        {
            echo "대기중인 가상계좌일괄등록 미존재\n";
        }



        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'virtual');

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

        // 구분값 확인
        if($value['va_flag'] != '등록' && $value['va_flag'] != '수정')
        {
            $waring = "유효한 구분이 없습니다."; 
            $return["waring"] = $waring;
            return $return;
        }
        else
        {

            // 가상계좌 확인
            $chk_vir =  DB::TABLE("vir_acct")->SELECT("count(1) as cnt")
                                                ->WHERE('loan_info_no', $value['loan_info_no'])
                                                ->WHERE('cust_info_no', $value['cust_info_no'])
                                                ->WHERE('save_status','Y')
                                                ->first();

            // 구분값이 '등록'인데 이미 가상계좌가 존재할 경우
            if($chk_vir->cnt > 0)
            {
                if($value['va_flag'] == "등록")
                {
                    $waring = "가상계좌가 이미 존재합니다."; 
                    $return["waring"] = $waring;
                    return $return;
                }
            }
            else
            {
                if($value['va_flag'] == "수정")
                {
                    $waring = "가상계좌가 존재하지않습니다. 등록을 진행해주세요."; 
                    $return["waring"] = $waring;
                    return $return;
                }

                // 계약확인
                $chk_loan =  DB::TABLE("loan_info")->SELECT("count(1) as cnt")
                                                    ->WHERE('no', $value['loan_info_no'])
                                                    ->WHERE('cust_info_no', $value['cust_info_no'])
                                                    ->WHERE('save_status','Y')
                                                    ->first();

                // 구분값이 '등록'인데 이미 가상계좌가 존재할 경우
                if($chk_loan->cnt < 1)
                {
                    $waring = "해당 계약이 존재하지않습니다."; 
                    $return["waring"] = $waring;
                    return $return;
                }
                
            }
        }

        // 계약확인
        $chk_cust =  DB::TABLE("loan_info")->SELECT("*")
                                            ->WHERE('no', $value['loan_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(empty($chk_cust))
        {
            $waring = "A:계약번호를 올바르게 입력바랍니다.";
            $return["waring"] = $waring;
            return $return;
        }

        
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
