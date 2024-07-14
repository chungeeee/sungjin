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

class LumpInterestIB extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LumpInterest:InterestIB {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '이자지급내역(신한은행)';

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
        $this->requirArray = Array('bank_cd' => '은행코드', 
                                    'bank_ssn' => '계좌번호', 
                                    'interest_money' => '금액',
                                );
        $this->bankCd       = Func::getConfigArr('bank_cd');       // 은행코드
        
        // 배치 기록
        $stime = time();
        $batchLogNo = $this->startBatchLog($stime);

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $file = "";
        $ERROR = Array();

        // 메인쿼리
        $result = DB::TABLE("lump_master_log")->SELECT("*")->WHERE('division','IB')
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

                $colHeader  = array('*입금은행','*입금계좌','고객관리성명','*입금액','출금통장표시내용','입금통장표시내용','입금인코드','비고','업체사용key');
                $colNm      = array(
                    'bank_cd'	            => '0',	    // *입금은행
                    'bank_ssn'	            => '1',	    // *입금계좌
                    'bank_owner'            => '2',     // 고객관리성명
                    'interest_money'	    => '3',	    // *입금액
                    'sender_memo'           => '4',      // 출금통장표시내용
                    'receiver_memo'         => '5',     // 입금통장표시내용
                    'sender_code'           => '6',     // 입금인코드
                    'comment'               => '7',     // 비고
                    'user_pkey'             => '8',     // 업체사용key
                );

                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    // 엑셀파일 헤더 불일치
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 헤더 불일치 오류','status'=>'X',], ['division'=>'IB', 'no'=>$result->no]);
                }
                else
                {
                    // 상태 '진행중' 변경
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['status'=>'P','start_time'=>time()], ['division'=>'IB', 'no'=>$result->no]);

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

                        $_INS['bank_cd'] = sprintf("%03d", $_INS['bank_cd']);
                        $_INS['interest_money'] = str_replace(",","",$_INS['interest_money']);
                        $_INS['save_status'] = 'Y';
                        $_INS['save_id'] = $result->reg_id;
                        $_INS['save_time'] = $save_time;                        
                        $rslt = DB::dataProcess('INS', 'interest_payment', $_INS);
                        if($rslt!="Y")
                        {
                            $_INS['err_msg'] = "[DB입력오류]";
                            $ERROR[$i] = $_INS;
                            $i++;
                        }
                    }

                    $rslt = DB::dataProcess('UPD', 'lump_master_log', ["status"=>"C","finish_time"=>time(),"total_count"=>$total_cnt, "ok_count"=>(($total_cnt)-count($ERROR)), "fail_count"=>count($ERROR), "remark"=>count($ERROR)."건 실패"], ['division'=>'IB', "no"=>$result->no]);
                }
            }
            else
            {
                $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 읽기오류','status'=>'X',], ['division'=>'IB', 'no'=>$result->no]);
                echo "엑셀파일 미존재\n";
            }
        }
        else
        {
            echo "대기중인 이자지급내역(신한) 배치 미존재\n";
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

            $rslt = DB::dataProcess("UPD", "lump_master_log", ['fail_file_path'=>$file_path,'fail_file_name'=>$file_name], ['division'=>'IB', 'no'=>$result->no]);
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

        // 은행코드 확인
        if(!array_key_exists(sprintf("%03d", $value['bank_cd']), $this->bankCd))
        {
            $waring = "은행코드를 올바르게 입력바랍니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 계좌번호 확인
        if(!empty($value['bank_ssn']))
        {
            if(!is_numeric(str_replace("-", "", $value['bank_ssn'])))
            {
                $waring = "[데이터오류] 계좌번호 형식"; 
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
