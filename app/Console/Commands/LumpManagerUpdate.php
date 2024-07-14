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

class LumpManagerUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LumpManager:ManagerUpdate {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '담당자일괄업데이트';

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
        $this->requirArray = Array(
                                    'cust_info_no' => '회원번호', 
                                    'loan_info_no' => '계약번호', 
                                );

        // 담당자 및 부서
        $this->userBranchArray = Func::getBranch();
        
        // 배치 기록
        $stime = time();
        $batchLogNo = $this->startBatchLog($stime);

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $file = "";
        $ERROR = Array();

        // 메인쿼리
        $result = DB::TABLE("lump_master_log")->SELECT("*")->WHERE('division','M')
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

                $colHeader  = array('회원번호', '계약번호', '이름', '변경부서', '변경담당자');
                $colNm      = array(
                    'cust_info_no'	     => '0',	    // 회원번호(필수)
                    'loan_info_no'	     => '1',	    // 계약번호(필수)
                    'name'	             => '2',	    // 이름
                    'manager_code'       => '3',        // 변경부서
                    'manager_id'         => '4',        // 변경담당자
                    );

                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    // 엑셀파일 헤더 불일치
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 헤더 불일치 오류','status'=>'X',], ['division'=>'M', 'no'=>$result->no]);
                }
                else
                {
                    // 상태 '진행중' 변경
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['status'=>'P','start_time'=>time()], ['division'=>'M', 'no'=>$result->no]);

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

                        $_UPD['manager_code'] = array_flip($this->userBranchArray)[$_UPD['manager_code']];

                        $chk_sql =  DB::TABLE("users")->SELECT('branch_code', 'id')
                                                        ->WHERE('branch_code', $_UPD['manager_code'])
                                                        ->WHERE('name', Func::encrypt($_UPD['manager_id'], 'ENC_KEY_SOL'))
                                                        ->WHERE('save_status','Y')
                                                        ->first();
    
                        $_UPD['manager_code'] = $chk_sql->branch_code;
                        $_UPD['manager_id'] = $chk_sql->id;

                        $rslt = DB::dataProcess('UPD', 'loan_info', ["manager_code"=>$_UPD['manager_code'], "manager_id"=>$_UPD['manager_id']], ["no"=>$_UPD['loan_info_no']]);
                    }

                   $rslt = DB::dataProcess('UPD', 'lump_master_log', ["status"=>"C","finish_time"=>time(),"total_count"=>$total_cnt, "ok_count"=>(($total_cnt)-count($ERROR)), "fail_count"=>count($ERROR), "remark"=>count($ERROR)."건 실패"], ["no"=>$result->no]);
                }
            }
            else
            {
                $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 읽기오류','status'=>'X',], ['division'=>'M', 'no'=>$result->no]);
                echo "엑셀파일 미존재\n";
            }
        }
        else
        {
            echo "대기중인 담당자업데이트 미존재\n";
        }



        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'manager');

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

        // 계약확인
        $chk_cust =  DB::TABLE("loan_info")->SELECT("*")
                                            ->WHERE('no', $value['loan_info_no'])
                                            ->WHERE('cust_info_no', $value['cust_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(empty($chk_cust))
        {
            $waring = "회원번호 및 계약번호를 올바르게 입력바랍니다.";
            $return["waring"] = $waring;
            return $return;
        }

        $value['manager_code'] = array_flip($this->userBranchArray)[$value['manager_code']];

        $chk_sql =  DB::TABLE("users")->SELECT("*")
                                    ->WHERE('branch_code', $value['manager_code'])
                                    ->WHERE('name', Func::encrypt($value['manager_id'], 'ENC_KEY_SOL'))
                                    ->WHERE('save_status','Y')
                                    ->first();
        if(empty($chk_sql))
        {
            $waring = "변경부서 및 담당자를 확인해주세요.";
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
