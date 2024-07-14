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

class LumpMemoUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LumpMemo:MemoUpdate {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '메모일괄업데이트';

    private $requirArray;
    private $colorArray;

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
        $this->requirArray = Array( "cust_info_no" => "회원번호",
                                    "loan_info_no" => "계약번호", 
                                );

        $this->colorArray = array(
                                    "빨강"	=>	"red",
                                    "노랑"	=>	"yellow",
                                    "초록"	=>	"green",
                                    "파랑"	=>	"blue",
                                    "보라"	=>	"purple",
                                    "핑크"	=>	"pink",
                                    "검정"	=>	"black",
                                );

        // 배치 기록
        $stime = time();
        $batchLogNo = $this->startBatchLog($stime);

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $file = "";
        $ERROR = Array();

        // 메인쿼리
        $result = DB::TABLE("lump_master_log")->SELECT("*")->WHERE('division','A')
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

                $colHeader  = array("회원번호", "계약번호", "구분", "중요메모", "메모", "색 지정(가능 범위 - 검정,빨강,노랑,초록,파랑,보라,핑크)");
                $colNm      = array(
                    "cust_info_no"	        => "0",	    // 회원번호
                    "loan_info_no"	        => "1",	    // 계약번호(필수)
                    "div"                   => "2",     // 구분
                    "important_check"       => "3",     // 중요메모
                    "memo"                  => "4",     // 메모
                    "memo_color"            => "5",     // 색

                );

                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    // 엑셀파일 헤더 불일치
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 헤더 불일치 오류','status'=>'X',], ['division'=>'A', 'no'=>$result->no]);
                }
                else
                {
                    // 상태 '진행중' 변경
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['status'=>'P','start_time'=>time()], ['division'=>'A', 'no'=>$result->no]);

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
       
                        // 회원번호에 zb를 붙여서 업로드할경 제거
                        if(strpos($_INS['cust_info_no'], 'zb') !== false)
                        {
                            $_INS['cust_info_no'] = str_replace('zb', '', $_INS['cust_info_no']);
                        }

                        if(isset($arrayCheck["waring"]))
                        {
                            $_INS['err_msg'] = $arrayCheck["waring"];
                            $ERROR[$i] = $_INS;
                            $i++;
                            
                            continue;
                        }

                        if($_INS['important_check'] != 'Y') $_INS['important_check'] = "";
                        $_INS['memo_color'] = $this->colorArray[$_INS['memo_color']];
                        $_INS['save_time'] = date("YmdHis");
                        $_INS['save_id'] = $reg_id;
                        $_INS['save_status'] = "Y";
                        $_INS['is_batch'] = "Y";

                        $rslt = DB::dataProcess('INS', 'cust_info_memo', $_INS);
                    }

                    $rslt = DB::dataProcess('UPD', 'lump_master_log', ["status"=>"C","finish_time"=>time(),"total_count"=>$total_cnt, "ok_count"=>(($total_cnt)-count($ERROR)), "fail_count"=>count($ERROR), "remark"=>count($ERROR)."건 실패"], ["no"=>$result->no]);
                }
            }
            else
            {
                $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 읽기오류','status'=>'X',], ['division'=>'A', 'no'=>$result->no]);
                echo "엑셀파일 미존재\n";
            }
        }
        else
        {
            echo "대기중인 메모일괄업데이트 미존재\n";
        }



        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'memo');

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
            $waring = "A:계약번호 또는 회원번호를 올바르게 입력바랍니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 색상 확인
        if(!array_key_exists($value['memo_color'], $this->colorArray))
        {
            $waring = "유효한 색상이 아닙니다.";
            $return["waring"] = $waring;
            return $return;
        }
        
        // 구분 확인
        if(!in_array($value['div'], Func::getConfigArr('memo_div'))) 
        {
            $waring = "유효한 구분이 없습니다.";
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
