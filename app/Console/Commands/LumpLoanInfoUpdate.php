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

class LumpLoanInfoUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LumpLoanInfo:LoanInfoUpdate {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '채권정보일괄업데이트';

    private $requirArray;
    private $courtCaseNumberA;
    private $courtCaseNumberB;
    private $courtCaseNumberC;
    private $courtCaseNumberD;
    private $courtCaseNumberFlag;

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
                                );

        // 사건번호 테이블 매핑
        $this->courtCaseNumberA = Array('A_court', 'A_case_number', 'A_name',);
        $this->courtCaseNumberB = Array('B_court', 'B_case_number', 'B_name',);
        $this->courtCaseNumberC = Array('C_court', 'C_case_number', 'C_name',);
        $this->courtCaseNumberD = Array('D_court', 'D_case_number', 'D_name',);

        // 사건번호 구분
        $this->courtCaseNumberFlag = Array('A', 'B', 'C', 'D');

        // 코드관리
        $this->courtCodeArray   = array_flip(Func::getConfigArr('court_cd'));
        $this->loanCat1Array    = Func::getConfigArr('loan_cat_1_cd');
        $this->loanCat2Array    = Func::getConfigArr('loan_cat_2_cd');

        // 배치 기록
        $stime = time();
        $batchLogNo = $this->startBatchLog($stime);

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $file = "";
        $ERROR = Array();

        // 메인쿼리
        $result = DB::TABLE("lump_master_log")->SELECT("*")->WHERE('division','LI')
                                                         ->WHERE('status','W')
                                                         ->ORDERBY('no', 'asc')
                                                         ->first();
        
        if(!empty($result))
        {
            $_COURT_CASE_A = Array();
            $_COURT_CASE_B = Array();
            $_COURT_CASE_C = Array();
            $_COURT_CASE_D = Array();
            $custCourt = Array();

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

                $colHeader  = array('계약번호', '시효중단여부', '시효중단일자', '매입금액', '채권구분1', '채권구분2',
                                    '개인회생 법원명', '개인회생 사건번호', '개인회생 당사자명',
                                    '파산 법원명', '파산 사건번호', '파산 당사자명',
                                    '면책 법원명', '면책 사건번호', '면책 당사자명',
                                    '기타 법원명', '기타 사건번호', '기타 당사자명',
                                    '최초연체일', '최초대출일');
                $colNm      = array(
                    'loan_info_no'	                => '0',	    // 계약번호(필수)
                    'null_limit_stop_div'           => '1',     // 시효중단여부
                    'null_limit_stop_date'          => '2',     // 시효중단일자
                    'base_cost'                     => '3',     // 매입금액
                    'loan_cat_1_cd'                 => '4',     // 채권구분1                    
                    'loan_cat_2_cd'                 => '5',     // 채권구분2
                    'A_court'                       => '6',    // 개인회생 법원명
                    'A_case_number'                 => '7',    // 개인회생 사건번호
                    'A_name'                        => '8',    // 개인회생 당사자명
                    'B_court'                       => '9',    // 파산 법원명
                    'B_case_number'                 => '10',    // 파산 사건번호
                    'B_name'                        => '11',    // 파산 당사자명
                    'C_court'                       => '12',    // 면책 법원명
                    'C_case_number'                 => '13',    // 면책 사건번호
                    'C_name'                        => '14',    // 면책 당사자명
                    'D_court'                       => '15',    // 기타 법원명
                    'D_case_number'                 => '16',    // 기타 사건번호
                    'D_name'                        => '17',    // 기타 당사자명
                    'first_delay_date'              => '18',    // 최초연체일
                    'first_loan_date'               => '19',    // 최초대출일
                );

                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 회원명
                $this->userIdArray = Func::getUserId();

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    // 엑셀파일 헤더 불일치
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 헤더 불일치 오류','status'=>'X',], ['division'=>'LI', 'no'=>$result->no]);
                }
                else
                {
                    // 상태 '진행중' 변경
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['status'=>'P','start_time'=>time()], ['division'=>'LI', 'no'=>$result->no]);

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

                        $arrayCheck = $this->validCheck($_UPD);

                        if(isset($arrayCheck["waring"]))
                        {
                            $_UPD['err_msg'] = $arrayCheck["waring"];
                            $ERROR[$i] = $_UPD;
                            $i++;
                            
                            continue;
                        }

                        // null을 입력했을경우는 빈값으로 업데이트
                        foreach($_UPD as $key=>$val)
                        {
                            // 값이 없으면 unset
                            if($val == "")
                            {
                                unset($_UPD[$key]);
                            }

                            // 값이 null 이면 빈값으로 업데이트
                            if($val == "null" || $val == "NULL")
                            {
                                $_UPD[$key] = '';
                            }
                        }

                        // 고객번호 추출
                        $chk_cust =  DB::TABLE("loan_info")->SELECT('cust_info_no')
                                                                    ->WHERE('no', $_UPD['loan_info_no'])
                                                                    ->WHERE('save_status','Y')
                                                                    ->first();

                        if(!empty($_UPD['loan_cat_1_cd']))
                        {
                            $_UPD['loan_cat_1_cd']    = Func::getArrayName(array_flip($this->loanCat1Array), $_UPD['loan_cat_1_cd']);
                        }
                        if(!empty($_UPD['loan_cat_2_cd']))
                        {
                            $_UPD['loan_cat_2_cd']    = Func::getArrayName(array_flip($this->loanCat2Array), $_UPD['loan_cat_2_cd']);
                        }
                        if(!empty($_UPD['first_delay_date']))
                        {
                            $_UPD['first_delay_date'] = str_replace("-", "", $_UPD['first_delay_date']);
                        }
                        if(!empty($_UPD['first_loan_date']))
                        {
                            $_UPD['first_loan_date']  = str_replace("-", "", $_UPD['first_loan_date']);
                        }

                        $_UPD['save_time']        = date("YmdHis");
                        $_UPD['save_id']          = $reg_id;

                        $rslt = DB::dataProcess('UPD', 'loan_info', $_UPD, ["no"=>$_UPD['loan_info_no']]);
                    }

                    $rslt = DB::dataProcess('UPD', 'lump_master_log', ["status"=>"C","finish_time"=>time(),"total_count"=>$total_cnt, "ok_count"=>(($total_cnt)-count($ERROR)), "fail_count"=>count($ERROR), "remark"=>count($ERROR)."건 실패"], ["no"=>$result->no]);
                }
            }
            else
            {
                $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 읽기오류','status'=>'X',], ['division'=>'LI', 'no'=>$result->no]);
                echo "엑셀파일 미존재\n";
            }
        }
        else
        {
            echo "대기중인 계약정보일괄업데이트 미존재\n";
        }



        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'loan_info');

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

        // 날짜형식확인
        if(!empty($value['first_delay_date']) || !empty($value['first_loan_date']))
        {
            if(!empty($value['first_delay_date']))
            {
                $val = date('Ymd', strtotime($value['first_delay_date']));
            }
            else
            {
                $val = date('Ymd', strtotime($value['first_loan_date']));
            }

            if(!checkdate(substr($val, 4, 2), substr($val, 6, 2), substr($val, 0, 4)))
            {
                $waring = "[데이터오류] 날짜형식"; 
                $return["waring"] = $waring;
                return $return;
            }
        }

        if(!empty($value['loan_cat_1_cd']))
        {
            // 채권구분1 확인
            if(!in_array($value['loan_cat_1_cd'], $this->loanCat1Array)) 
            {
                $waring = "채권구분1 입력값을 확인해주세요.";
                $return["waring"] = $waring;
                return $return;
            }
        }

        if(!empty($value['loan_cat_2_cd']))
        {
            // 채권구분2 확인
            if(!in_array($value['loan_cat_2_cd'], $this->loanCat2Array)) 
            {
                $waring = "채권구분2 입력값을 확인해주세요.";
                $return["waring"] = $waring;
                return $return;
            }
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
