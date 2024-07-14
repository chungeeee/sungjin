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

class LumpBorrowUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LumpBorrow:BorrowUpdate {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '채권담보관리일괄업데이트';

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
        $this->requirArray = Array('process_div' => '구분',
                                    'cust_info_no' => '회원번호',
                                    'loan_info_no' => '계약번호', 
                                    'bank_name' => '담보제공처', 
                                    'start_date' => '담보등록일', 
                                );

        // 구분값
        $this->arrayProcess = Array("A" => "등록", "D" => "해지", );

        // 해지사유
        $this->borrowEndReason = Func::getConfigArr('borrow_end_reason_cd');

        
        // 배치 기록
        $stime = time();
        $batchLogNo = $this->startBatchLog($stime);

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $file = "";
        $ERROR = Array();

        // 메인쿼리
        $result = DB::TABLE("lump_master_log")->SELECT("*")->WHERE('division','B')
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

                $colHeader  = array('구분', '해지사유', '해지일자', '회원번호', '계약번호', '담보제공처', '담보등록일',);
                $colNm      = array(
                    'process_div'       => '0',	    // 구분(필수)
                    'end_reason_cd'     => '1',	    // 해지사유
                    'end_date'          => '2',	    // 해지일자
                    'cust_info_no'      => '3',	    // 회원번호(필수)
                    'loan_info_no'	    => '4',	    // 계약번호(필수)
                    'bank_name'	        => '5',	    // 담보제공처(필수)
                    'start_date'        => '6',     // 담보등록일(필수)
                );

                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    // 엑셀파일 헤더 불일치
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 헤더 불일치 오류','status'=>'X',], ['division'=>'B', 'no'=>$result->no]);
                }
                else
                {
                    // 상태 '진행중' 변경
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['status'=>'P','start_time'=>time()], ['division'=>'B', 'no'=>$result->no]);

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

                        $_INS['process_div'] = Func::nvl(array_flip($this->arrayProcess)[$_INS['process_div']], '');
                        $_INS['end_date']    = str_replace('-', '', $_INS['end_date']);
                        $_INS['start_date']  = str_replace('-', '', $_INS['start_date']);
                        $_INS['save_time']   = date("YmdHis");
                        $_INS['save_id']     = $reg_id;
                        $_INS['save_status'] = "Y";

                        // 담보제공처확인
                        $chk_borrow =  DB::TABLE("borrow_comp")->SELECT("no")
                                                        ->WHERE('bank_name', Func::chungEncOne($_INS['bank_name']))
                                                        ->WHERE('save_status','Y')
                                                        ->first();

                        if($_INS['process_div'] == 'A')
                        {
                            // 관리번호 부여
                            $bo_mng   = DB::TABLE('BORROW')->SELECT(DB::raw('COALESCE(max(mng_no),0)+1 as mng_no'))
                                                            ->WHERE('save_status','Y')
                                                            ->WHERE('borrow_comp_no', $chk_borrow->no)
                                                            ->WHERE('borrow_comp_sub_no', '1')
                                                            ->FIRST();

                            // 담보등록으로 넘길때 해지정보 초기화
                            $_INS['end_date']           = $_INS['end_reason_cd'] = null;
                            $_INS['mng_no']             = $bo_mng->mng_no;
                            $_INS['status']             = 'S';
                            $_INS['borrow_comp_no']     = $chk_borrow->no;
                            $_INS['borrow_comp_sub_no'] = '1';

                            $rslt = DB::dataProcess('INS', 'borrow', $_INS);
                            $loan_rslt = DB::dataProcess('UPD', 'loan_info', ['borrow_yn'=>"Y","save_id"=>$_INS['save_id'],"save_time"=>$_INS['save_time']], ["no"=>$_INS['loan_info_no']]);
                        }
                        elseif($_INS['process_div'] == 'D')
                        {
                            $_INS['status'] = 'E';
                            $_INS['end_reason_cd'] = array_flip($this->borrowEndReason)[$_INS['end_reason_cd']];

                            $rslt = DB::dataProcess('UPD', 'borrow', $_INS, ["no" => $chk_borrow->no]);
                            $loan_rslt = DB::dataProcess('UPD', 'loan_info', ['borrow_yn'=>"N","save_id"=>$_INS['save_id'],"save_time"=>$_INS['save_time']], ["no"=>$_INS['loan_info_no']]);
                        }
                    }

                    $rslt = DB::dataProcess('UPD', 'lump_master_log', ["status"=>"C","finish_time"=>time(),"total_count"=>$total_cnt, "ok_count"=>(($total_cnt)-count($ERROR)), "fail_count"=>count($ERROR), "remark"=>count($ERROR)."건 실패"], ["no"=>$result->no]);
                }
            }
            else
            {
                $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 읽기오류','status'=>'X',], ['division'=>'B', 'no'=>$result->no]);
                echo "엑셀파일 미존재\n";
            }
        }
        else
        {
            echo "대기중인 채권담보관리일괄업데이트 미존재\n";
        }



        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'borrow');

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
            if($value[$chk_key] == '' || empty($value[$chk_key]))
            {
                $waring = "필수값 체크 : ".$chk_val." 확인필요"; 
                $return["waring"] = $waring;
                return $return;
            }
        }

        if(!in_array($value['process_div'], $this->arrayProcess)) 
        {
            $waring = "유효한 처리구분이 없습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        $start_val = date('Ymd', strtotime($value['start_date']));
        if($start_val == '19700101' || !checkdate(substr($start_val, 4, 2), substr($start_val, 6, 2), substr($start_val, 0, 4)))
        {
            $waring = "[데이터오류] 날짜형식을 텍스트 타입으로 변환해주세요."; 
            $return["waring"] = $waring;
            return $return;
        }

        if($value['process_div'] == 'D')
        {
            if($value['end_reason_cd'] == '' || empty($value['end_reason_cd']) )
            {
                $waring = "해지사유를 등록해주세요.";
                $return["waring"] = $waring;
                return $return;
            }

            if($value['end_date'] == '' || empty($value['end_date']) )
            {
                $waring = "해지일자를 등록해주세요.";
                $return["waring"] = $waring;
                return $return;
            }

            if(!in_array($value['end_reason_cd'], $this->borrowEndReason))
            {
                $waring = "해지사유를 확인해주세요.";
                $return["waring"] = $waring;
                return $return;
            }  

            $end_val = date('Ymd', strtotime($value['end_date']));
            if($end_val == '19700101' || !checkdate(substr($end_val, 4, 2), substr($end_val, 6, 2), substr($end_val, 0, 4)))
            {
                $waring = "[데이터오류] 날짜형식을 텍스트 타입으로 변환해주세요."; 
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
        $chk_loan =  DB::TABLE("loan_info")->SELECT("no")
                                            ->WHERE('no', $value['loan_info_no'])
                                            ->WHERE('cust_info_no', $value['cust_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(empty($chk_loan))
        {
            $waring = "계약번호 및 회원번호를 올바르게 입력바랍니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 담보제공처확인
        $chk_borrow =  DB::TABLE("borrow_comp")->SELECT("no")
                                            ->WHERE('bank_name', Func::chungEncOne($value['bank_name']))
                                            ->WHERE('save_status','Y')
                                            ->first();
        
        if(empty($chk_borrow))
        {
            $waring = "담보제공처를 확인해주세요.";
            $return["waring"] = $waring;
            return $return;
        }
        else
        {
            if($value['process_div'] == 'A')
            {
                // 담보제공계약일
                $comp_sub = DB::table('borrow_comp_sub')->SELECT('sub_trade_sdate')
                                                            ->WHERE('save_status','Y')
                                                            ->WHERE('borrow_comp_no',$chk_borrow->no)
                                                            ->WHERE('sub_no', '1')
                                                            ->FIRST();
    
                // 채권계약일
                $l_info = DB::table('loan_info')->SELECT('contract_date')
                                                        ->WHERE('save_status','Y')
                                                        ->WHERE('no', $value['loan_info_no'])
                                                        ->FIRST();
                
                // 채권계약일이 담보제공계약일보다 이전일경우 등록불가
                if($l_info->contract_date < $comp_sub->sub_trade_sdate )
                {
                    $waring = "담보제공계약일 이전 계약입니다.";
                    $return["waring"] = $waring;
                    return $return;
                }
    
                // 해당회원의 채권들 중 타차입처 담보등록건 확인 
                $other_comp =  DB::table('borrow')->SELECT('no')
                                                ->WHERE('cust_info_no', $value['cust_info_no'])
                                                ->WHERE('borrow_comp_no', '!=', $chk_borrow->no)
                                                ->WHERE('STATUS','S')
                                                ->WHERE('SAVE_STATUS','Y')
                                                ->FIRST();
                if(!empty($other_comp))
                {
                    $waring = "타차입처 담보등록 회원입니다.";
                    $return["waring"] = $waring;
                    return $return;
                }
    
                // 해당 담보채권 서류스캔 상태 확인
                // 서류스캔이 모두 Y로 안들어가있음(마이그이슈?)
                // $scan_status =  DB::table('borrow')->SELECT('no')
                //                                     ->WHERE('loan_info_no', $value['loan_info_no'])
                //                                     ->WHERE('SCAN_STATUS', '!=', 'Y')
                //                                     ->WHERE('SAVE_STATUS','Y')
                //                                     ->FIRST();
                // if($scan_status)
                // {
                //     $waring = "서류스캔상태가 미상태입니다.";
                //     $return["waring"] = $waring;
                //     return $return;
                // }
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
