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

class LumpCustInfoUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LumpCustInfo:CustInfoUpdate {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '채무자정보일괄업데이트';

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
                                );


        // 우편물발송주소
        $this->addrCdArray = Func::getConfigArr('addr_cd');

        // 전화번호 키
        $this->phoneKeyArray = Array('ph1', 'ph2', 'ph3', 'ph4');

        // 배치 기록
        $stime = time();
        $batchLogNo = $this->startBatchLog($stime);

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $file = "";
        $ERROR = Array();

        // 메인쿼리
        $result = DB::TABLE("lump_master_log")->SELECT("*")->WHERE('division','CU')
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

                $colHeader  = array('회원번호', '이름', '생년월일(주민등록번호)', '집전화', '휴대전화', '직장전화', '기타전화', 
                                    '실거주주소(우편번호)', '실거주주소', '실거주주소(상세)', 
                                    '등본주소(우편번호)', '등본주소','등본주소(상세)', 
                                    '직장주소(우편번호)','직장주소','직장주소(상세)', 
                                    '기타주소(우편번호)','기타주소','기타주소(상세)', 
                                    '우편물주소', '이메일', '직장명'
                                );
                $colNm      = array(
                    'cust_info_no'      => '0',	    // 회원번호(필수)
                    'name'	            => '1',	    // 이름
                    'ssn'	            => '2',	    // 생년월일(주민등록번호)
                    'ph1'               => '3',     // 집전화
                    'ph2'	            => '4',	    // 휴대전화
                    'ph3'               => '5',     // 직장전화
                    'ph4'	            => '6',	    // 기타전화
                    'zip1'              => '7',     // 실거주주소(우편번호)
                    'addr11'	        => '8',	    // 실거주주소
                    'addr12'            => '9',     // 실거주주소(상세)
                    'zip2'	            => '10',	// 등본주소(우편번호)
                    'addr21'            => '11',    // 등본주소
                    'addr22'	        => '12',	// 등본주소(상세)
                    'zip3'              => '13',    // 직장주소(우편번호)
                    'addr31'	        => '14',	// 직장주소
                    'addr32'            => '15',    // 직장주소(상세)
                    'zip4'	            => '16',	// 기타주소
                    'addr41'            => '17',    // 기타주소(우편번호)
                    'addr42'	        => '18',	// 기타주소(상세)
                    'post_send_cd'      => '19',    // 우편물주소
                    'email'	            => '20',	// 이메일
                    'com_name'          => '21',    // 직장명
                );

                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    // 엑셀파일 헤더 불일치
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 헤더 불일치 오류','status'=>'X',], ['division'=>'CU', 'no'=>$result->no]);
                }
                else
                {
                    // 상태 '진행중' 변경
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['status'=>'P','start_time'=>time()], ['division'=>'CU', 'no'=>$result->no]);

                    foreach($excelData as $_DATA) 
                    {
                        unset($_UPD, $_CUST_INFO, $_CUST_EXTRA);

                        $arrayCheck = Array();
                        $_CUST_INFO = Array();
                        $_CUST_EXTRA = Array();

                        // 데이터 정리
                        foreach($_DATA as $key => $val) 
                        {
                            $val = trim($val);
                            $_UPD[$key] = $val;
                        }

                        $total_cnt++;

                        // 데이터 추출 및 정리
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
                                if($key == 'addr11')
                                {
                                    $_CUST_EXTRA['old_addr11'] = '';
                                }
                                if($key == 'addr21')
                                {
                                    $_CUST_EXTRA['old_addr21'] = '';
                                }
                                if($key == 'addr31')
                                {
                                    $_CUST_EXTRA['old_addr31'] = '';
                                }
                                if($key == 'addr41')
                                {
                                    $_CUST_EXTRA['old_addr41'] = '';
                                }
                                $_UPD[$key] = '';
                            }
                        }

                        $arrayCheck = $this->validCheck($_UPD);

                        // 회원번호에 zb를 붙여서 업로드할경 제거
                        if(strpos($_UPD['cust_info_no'], 'zb') !== false)
                        {
                            $_UPD['cust_info_no'] = str_replace('zb', '', $_UPD['cust_info_no']);
                        }

                        if(isset($arrayCheck["waring"]))
                        {
                            $_UPD['err_msg'] = $arrayCheck["waring"];
                            $ERROR[$i] = $_UPD;
                            $i++;
                            
                            continue;
                        }

                        // cust_info
                        if(isset($_UPD['ssn']))
                        {
                            $_CUST_INFO['ssn'] = str_replace("-" ,"", $_UPD['ssn']);
                        }

                        // cust_info
                        if(isset($_UPD['name']))
                        {
                            $_CUST_INFO['name'] = $_UPD['name'];
                        }

                        // cust_info_extra
                        if(isset($_UPD['ph1']))
                        {                               
                            $_UPD['ph1'] = explode('-', $_UPD['ph1']);
                            
                            if( count($_UPD['ph1']) == 3 )
                            {
                                $_CUST_EXTRA['ph11'] = $_UPD['ph1'][0];
                                $_CUST_EXTRA['ph12'] = $_UPD['ph1'][1];
                                $_CUST_EXTRA['ph13'] = $_UPD['ph1'][2];
                            }
                            else
                            {
                                $_CUST_EXTRA['ph11'] = '';
                                $_CUST_EXTRA['ph12'] = '';
                                $_CUST_EXTRA['ph13'] = '';
                            }
                        }

                        // cust_info_extra
                        if(isset($_UPD['ph2']))
                        {
                            $_UPD['ph2'] = explode('-', $_UPD['ph2']);

                            if( count($_UPD['ph2']) == 3 )
                            {
                                $_CUST_EXTRA['ph21'] = $_UPD['ph2'][0];
                                $_CUST_EXTRA['ph22'] = $_UPD['ph2'][1];
                                $_CUST_EXTRA['ph23'] = $_UPD['ph2'][2];
                            }
                        }

                        // cust_info_extra
                        if(isset($_UPD['ph3']))
                        {
                            $_UPD['ph3'] = explode('-', $_UPD['ph3']);

                            if( count($_UPD['ph3']) == 3 )
                            {
                                $_CUST_EXTRA['ph31'] = $_UPD['ph3'][0];
                                $_CUST_EXTRA['ph32'] = $_UPD['ph3'][1];
                                $_CUST_EXTRA['ph33'] = $_UPD['ph3'][2];
                            }
                        }

                        // cust_info_extra
                        if(isset($_UPD['ph4']))
                        {
                            $_UPD['ph4'] = explode('-', $_UPD['ph4']);

                            if( count($_UPD['ph4']) == 3 )
                            {
                                $_CUST_EXTRA['ph41'] = $_UPD['ph4'][0];
                                $_CUST_EXTRA['ph42'] = $_UPD['ph4'][1];
                                $_CUST_EXTRA['ph43'] = $_UPD['ph4'][2];
                            }
                        }

                        unset($_UPD['ph1'], $_UPD['ph2'], $_UPD['ph3'], $_UPD['ph4']);

                        $_CUST_EXTRA = $_UPD;
                        $_CUST_EXTRA['post_send_cd'] = array_flip($this->addrCdArray)[$_UPD['post_send_cd']];
                        $_CUST_EXTRA['email']	     = $_UPD['email'];
                        $_CUST_EXTRA['com_name']     = $_UPD['com_name'];

                        if(!empty($_CUST_INFO))
                        {
                            $rslt = DB::dataProcess('UPD', 'cust_info', $_CUST_INFO, ["no"=>$_UPD['cust_info_no']]);
                        }

                        if(!empty($_CUST_EXTRA))
                        {
                            $rslt = DB::dataProcess('UPD', 'cust_info_extra', $_CUST_EXTRA, ["cust_info_no"=>$_UPD['cust_info_no']]);
                        }   
                    }

                    $rslt = DB::dataProcess('UPD', 'lump_master_log', ["status"=>"C","finish_time"=>time(),"total_count"=>$total_cnt, "ok_count"=>(($total_cnt)-count($ERROR)), "fail_count"=>count($ERROR), "remark"=>count($ERROR)."건 실패"], ["no"=>$result->no]);
                }
            }
            else
            {
                $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 읽기오류','status'=>'X',], ['division'=>'CU', 'no'=>$result->no]);
                echo "엑셀파일 미존재\n";
            }
        }
        else
        {
            echo "대기중인 채무자정보일괄업데이트 미존재\n";
        }



        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'cust_info');

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

        unset($ph1_explode, $ph2_explode, $ph3_explode);

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

        // 회원번호에 zb를 붙여서 업로드할경 제거
        if(strpos($value['cust_info_no'], 'zb') !== false)
        {
            $value['cust_info_no'] = str_replace('zb', '', $value['cust_info_no']);
        }

        // 계약확인
        $chk_cust =  DB::TABLE("cust_info")->SELECT("*")
                                            ->WHERE('no', $value['cust_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(empty($chk_cust))
        {
            $waring = "회원번호를 올바르게 입력바랍니다.";
            $return["waring"] = $waring;
            return $return;
        }

        if(!in_array($value['post_send_cd'], $this->addrCdArray))
        {
            $waring = "우편물주소를 확인해주세요.";
            $return["waring"] = $waring;
            return $return;
        }  
        
        if(!empty($value['ssn']))
        {
            $value['ssn'] = str_replace("-" ,"", $value['ssn']);

            if(strlen($value['ssn']) != '10' && strlen($value['ssn']) != '13')
            {
                $waring = "주민등록번호를 올바르게 입력해주세요.";
                $return["waring"] = $waring;
                return $return;
            }
        }
        
        foreach($value as $key => $val)
        {
            if(in_array($key, $this->phoneKeyArray))
            {
                if(!empty($val))
                {
                    $ph = explode('-' ,$val);

                    if(count($ph) != 3)
                    {
                        $waring = "전화번호 형식을 확인해주세요";
                        $return["waring"] = $waring;
                        return $return;
                    }
                }   
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
