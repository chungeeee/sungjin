<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Func;
use Log;
use DB;
use Loan;
use Trade;
use Cache;
use Carbon;
use Storage;
use ExcelFunc;
use App\Chung\Sms;

class QuickActionHistory extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Quick:actionhistory {--flag=}{--opt=}{--opt2=}';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '1회성 처리 저장소';
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
     * @return mixed
     */
	public function handle()
	{
		$flag = $this->option('flag');
		$opt = $this->option('opt');
		$opt2 = $this->option('opt2');

		if($flag == 'encryptDecrypt') {
			self::encryptDecrypt($opt, $opt2);
		} else if($flag == 'basecost'){
			self::basecost();
		} else if($flag == 'lumpAbstractAction'){
			self::lumpAbstractAction();
		} else if($flag == 'courtCdChange'){
			self::courtCdChange();
		} else if($flag == 'updateReport'){
			self::updateReport();
		} else if($flag == 'updateCloseData'){
			self::updateCloseData();
		} else if($flag == 'removeImages1'){
			self::removeImages1();
		} else if($flag == 'removeImages2'){
			self::removeImages2();
		} else if($flag == 'updatePlan'){
			self::updatePlan();
		} else if($flag == 'plWork'){
			self::plWork();
		} else if($flag == 'abstractUpdate'){
			self::abstractUpdate();
		} else if($flag == 'memoUpdate'){
			self::memoUpdate();
		} else if($flag == 'chkMan'){
			self::chkMan();
        } else if($flag == 'changePh'){
			self::changePh();
		} else if($flag == 'insertPrevCloseData'){
			self::insertPrevCloseData();
		} else if($flag == 'chgStatus'){
			self::chgStatus();
		} else if($flag == 'chgInterestSum'){
			self::chgInterestSum();
		} else if($flag == 'deleteCloseData'){
			self::deleteCloseData();
		} else if($flag == 'processingRetroactiveC'){
			self::processingRetroactiveC();
		} else if($flag == 'processingRetroactiveA'){
			self::processingRetroactiveA();
		} else if($flag == 'deleteDuplicateTradeIn'){
			self::deleteDuplicateTradeIn();
		} else if($flag == 'insertVirAcct'){
            self::insertVirAcct();
        } else if($flag == 'updateVirAcctWithCust'){
            self::updateVirAcctWithCust();
        } else if($flag == 'lumpBorrowUpdate'){
            self::lumpBorrowUpdate();
        } else if($flag == 'borrowBackup'){
            self::borrowBackup();
        } else if($flag == 'loanTradeKihanDate'){
            self::loanTradeKihanDate();
        } else if($flag == 'dayConfMig'){
            self::dayConfMig();
        } else if($flag == 'updateDateBiz'){
            self::updateDateBiz();
        } else if($flag == 'KsnetOutMoneyTEST1'){
            self::KsnetOutMoneyTEST1();
		} else if($flag == 'KsnetOutMoneyTEST2'){
            self::KsnetOutMoneyTEST2();
		} else if($flag == 'insertMoney'){
            self::insertMoney($opt);
		} else if($flag == 'devTest'){
            self::devTest();
		}
	}

    // 암호화, 복호화
	public function encryptDecrypt($mode, $value)
	{
		if($mode == 'e')
		{
			echo Func::encrypt($value, 'ENC_KEY_SOL');
		}
		else
		{
			echo Func::decrypt($value, 'ENC_KEY_SOL');
		}
	}

    // 잔여원가 업데이트
    public function basecost()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $path = "test/test.xlsx";

        // 파일 저장 위치
        if(Storage::disk('public')->exists($path))
        {
            echo "파일 O\n";

            $file = Storage::path('/public/'.$path);
            $colHeader = array('계약번호', '매입원가');
            $colNm = array(
                'no'         => '0',        // 계약번호
                'base_cost'  => '1',        // 매입원가
            );

            $excelData = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader, 0); 

            // 엑셀 유효성 검사
            if (!isset($excelData)) 
            {
                // 엑셀파일 헤더 불일치
                echo "헤더 불일치\n";
            }
            else 
            {
                // 상태 '진행중' 변경
                echo "[상태: 진행중]\n";

                foreach ($excelData as $_DATA) 
                {
                    unset($_UPD);

                    // 데이터 정리
                    foreach($_DATA as $key => $val) 
                    {
                        $val = preg_replace('/[^0-9]/', '', $val);
                        $_UPD[$key] = $val;
                    }

                    $contract_number = $_UPD['no'];
                    $base_cost = $_UPD['base_cost'] ?? 0;
                    
                    // loan_info_trade 잔여원가(첫 잔여원가 = 매입원가), 원가상환(0), 원가수익(0) 업데이트
                    $rslt1 = DB::dataProcess('UPD', 'loan_info_trade', ["now_cost"=>$base_cost, "return_bond_cost"=>'0',"profit_money"=>'0'], ["loan_info_no"=>$contract_number, 'save_status'=>'Y', 'seq'=>'1', 'trade_div'=>'O']);
                    
                    if ($rslt1=='Y')
                    {
                        $loan_info_trade = DB::table('loan_info_trade')->select('no','now_cost', 'return_bond_cost','profit_money')
                                                                        ->where('loan_info_no', $contract_number)
                                                                        ->where('save_status','Y')
                                                                        ->orderBy('no', 'asc')
                                                                        ->get();
                                                                       
                        $prev_now_cost = $base_cost;
                        $profit_money = $loan_info_trade->first()->profit_money;

                        foreach ($loan_info_trade as $v) {
                            $return_bond_cost = $v->return_bond_cost ?? 0;
                            $now_cost = $prev_now_cost - $return_bond_cost;
                        
                            if ($now_cost < 0) {
                                $profit_money += abs($now_cost); 
                                $now_cost = 0; 
                            } else {
                                $profit_money += 0; 
                            }
                            
                            // 잔여원가, 원가수익 업데이트
                            $rslt2 = DB::dataProcess('UPD', 'loan_info_trade', ["now_cost"=>$now_cost, "profit_money"=>$profit_money], ["no"=>$v->no]);

                            if($rslt2 != 'Y')
                            {
                                echo "잔여원가, 원가수익 업데이트 no update\n";
                            }

                            $prev_now_cost = $now_cost;
                        }

                        // 마지막 now_cost를 loan_info의 now_cost로 업데이트, base_cost 업데이트
                        $rslt3 = DB::dataProcess('UPD', 'loan_info', ["base_cost"=>$base_cost, "now_cost"=>$now_cost], ["no"=>$contract_number]);

                        if($rslt3 != 'Y')
                        {
                            echo "마지막 now_cost no update\n";
                        }
                    }
                    else 
                    {
                        echo "loan_info_trade 첫 잔여원가 no update\n";
                    }
                }
            }
        }
        else
        {
            echo "파일 X\n";
        }
    }

    // 초본열람 일괄 신청 
    public function lumpAbstractAction()
	{
        // 필수키
        $this->requirArray = Array('cust_info_no'  => '회원번호',
                                    'loan_info_no'   => '계약번호',
                                    'target_div'  => '구분',
                                    );

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $path = 'lumplog_upload/test.xlsx';
        $file = Storage::path('/public/'.$path);
        $ERROR = Array();

        $colHeader  = array('회원번호', '계약번호', 
                                    '고객명', '구분',
                                    '보증인번호', '보증인명'
                                    );
                                    
        $colNm      = array(
                            'cust_info_no'           => '0',	    // 회원번호
                            'loan_info_no'           => '1',	    // 계약번호
                            'name'                   => '2',	    // 고객명
                            'target_div'             => '3',	    // 구분
                            'loan_info_guarantor_no' => '4',	    // 보증인번호
                            'g_name'    	         => '5',	    // 보증인명
                            );

        $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

        $cnt = 0;
        $i = 0;

        if(Storage::disk('public')->exists($path))
        {
            // 엑셀 유효성 검사
            if(!isset($excelData))
            {
                echo '헤더불일치';
            }
            else
            {
                echo '값 존재함';
                foreach($excelData as $_DATA) 
                {
                    unset($_ABS);
                    $arrayCheck = Array();

                    // 데이터 정리
                    foreach($_DATA as $key => $val) 
                    {
                        $val = trim($val);
                        $ARR[$key] = $val;
                    }

                    $cnt++;
                    
                    $arrayCheck = $this->validCheck($ARR);

                    if(isset($arrayCheck["waring"]))
                    {
                        $ARR['err_msg'] = $arrayCheck["waring"];
                        $ERROR[$i] = $ARR;
                        $i++;
                        
                        continue;
                    }

                    // 회원번호에 zb를 붙여서 업로드할경우 제거
                    if(strpos($ARR['cust_info_no'], 'zb') !== false)
                    {
                        $ARR['cust_info_no'] = str_replace('zb', '', $ARR['cust_info_no']);
                    }
                    
                    if($ARR['target_div']=='차주')
                    {
                        // 열람신청(A), 진행(S) 상태인 경우 중복 신청 방지
                        $dupchk = DB::table("abstract")->where("cust_info_no",$ARR['cust_info_no'])
                                                        ->where("target_div","C")
                                                        ->wherein("status",["A","S"])
                                                        ->where("save_status","Y")
                                                        ->count();
                        
                        if ($dupchk > 0)
                        {
                            $ARR['err_msg'] = '(차주)이미 신청하셨습니다.';
                            $ERROR[$i] = $ARR;
                            $i++;
                            
                            continue;
                        }
                        else
                        {
                            // 고객정보 - 초본열람신청
                            $abs_info = DB::table("cust_info c")->JOIN("cust_info_extra e", "c.no", "=", "e.cust_info_no")
                                                                ->JOIN("loan_info l", "c.no", "=", "l.cust_info_no")
                                                                ->select("c.*", 'l.no as loan_info_no', 'l.manager_code', 'l.manager_id','l.seller_no','e.zip2','e.zip2','e.addr21','e.addr22')
                                                                ->where('c.save_status', 'Y')
                                                                ->where('c.no', $ARR['cust_info_no'])
                                                                ->orderby('loan_info_no')
                                                                ->first();
                                                                
                            $abs_info = Func::chungDec(["CUST_INFO", "cust_info_extra"], $abs_info);        // CHUNG DATABASE DECRYPT

                            $_ABS = [];

                            unset($_ABS);
                            $_ABS['cust_info_no'] = $ARR['cust_info_no'];
                            $_ABS['name'] = $abs_info->name ?? '';
                            $_ABS['ssn'] = $abs_info->ssn ?? '';
                            $_ABS['req_date'] = date('Ymd');
                            $_ABS['manager_code'] = $abs_info->manager_code ?? '';
                            $_ABS['manager_id'] = $abs_info->manager_id ?? '';
                            $_ABS['seller_no'] = $abs_info->seller_no ?? '';
                            $_ABS['target_div'] = 'C';
                            $_ABS['worker_id'] = 'SYSTEM';
                            $_ABS['origin_zip2'] = $abs_info->zip2 ?? '';
                            $_ABS['origin_addr21'] = $abs_info->addr21 ?? '';
                            $_ABS['origin_addr22'] = $abs_info->addr22 ?? '';
                            $_ABS['loan_info_no'] = $abs_info->loan_info_no ?? '';

                            $_ABS['status'] = 'A';
                            $_ABS['save_status'] = "Y";

                            $rslt = DB::dataProcess('INS', 'abstract', $_ABS);
                            if( $rslt!="Y" )
                            {
                                $ARR['err_msg'] = '초본열람신청 실패';
                                $ERROR[$i] = $ARR;
                                $i++;
                                
                                continue;
                            }
                        }
                    }
                    else if($ARR['target_div']=='보증인')
                    {
                        // 열람신청(A), 진행(S) 상태인 경우 중복 신청 방지
                        $dupchk = DB::table("abstract")->where("loan_info_guarantor_no",$ARR['loan_info_guarantor_no'])
                        ->wherein("status",["A","S"])
                        ->where("save_status","Y")
                        ->count();

                        if ($dupchk > 0)
                        {
                            $ARR['err_msg'] = '(보증인)이미 신청하셨습니다.';
                            $ERROR[$i] = $ARR;
                            $i++;
                            
                            continue;
                        }
                        else
                        {
                            // 보증인정보 - 초본열람신청
                            $abs_info = DB::table("loan_info_guarantor g")->join("loan_info l", "g.loan_info_no", "=", "l.no")
                                                        ->select('g.no','g.cust_info_no','g.loan_info_no','g.name','g.ssn','g.zip2','g.addr21','g.addr22','l.manager_code','l.manager_id')
                                                        ->where('g.save_status', 'Y')
                                                        ->where('g.no', $ARR['loan_info_guarantor_no'])
                                                        ->first();
                                            
                            $abs_info = Func::chungDec(["loan_info_guarantor"], $abs_info);	// CHUNG DATABASE DECRYPT

                            $_ABS = [];

                            unset($_ABS);
                            $_ABS['loan_info_guarantor_no'] = $ARR['loan_info_guarantor_no'];
                            $_ABS['cust_info_no'] = $abs_info->cust_info_no;
                            $_ABS['loan_info_no'] = $abs_info->loan_info_no;
                            $_ABS['name'] = $abs_info->name ?? '';
                            $_ABS['ssn'] = $abs_info->ssn ?? '';
                            $_ABS['req_date'] = date('Ymd');
                            $_ABS['manager_code'] = $abs_info->manager_code ?? '';
                            $_ABS['manager_id'] = $abs_info->manager_id ?? '';
                            $_ABS['target_div'] = 'G';
                            $_ABS['worker_id'] = 'SYSTEM';
                            $_ABS['origin_zip2'] = $abs_info->zip2 ?? '';
                            $_ABS['origin_addr21'] = $abs_info->addr21 ?? '';
                            $_ABS['origin_addr22'] = $abs_info->addr22 ?? '';

                            $_ABS['status'] = 'A';
                            $_ABS['save_status'] = "Y";

                            $rslt    = DB::dataProcess('INS', 'abstract', $_ABS);
                            if( $rslt != "Y" )
                            {
                                $ARR['err_msg'] = '초본열람신청 실패';
                                $ERROR[$i] = $ARR;
                                $i++;
                                
                                continue;
                            }
                        }
                    }
                    else
                    {
                        $ARR['err_msg'] = '차주랑 보증인 둘다 없음';
                        $ERROR[$i] = $ARR;
                        $i++;
                        
                        continue;
                    }
                }
            }
        }
        else
        {
            echo '엑셀파일 미존재';
        }

        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'abs');

            $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);
            $file_path = $file_arr[1]."/".$file_arr[2];
            $file_name = $file_arr[3];

            echo "실패 로그파일 생성: 실패건이 있을시 결과파일 만든다.\n";
	    }
	}

    // 초본열람 일괄 신청_유효성 검사
    public function validCheck_lumpAbstractAction($value) 
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
        
        if(!($value['target_div']=="보증인" || $value['target_div']=="차주"))
        {
            $waring = "구분을 올바르게 입력바랍니다.\n(차주 또는 보증인 입력)";
            $return["waring"] = $waring;
            return $return;
        }

        // 회원번호에 zb를 붙여서 업로드할경우 제거
        if(strpos($value['cust_info_no'], 'zb') !== false)
        {
            $value['cust_info_no'] = str_replace('zb', '', $value['cust_info_no']);
        }

        if($value['target_div']=="차주")
        {
            if(!empty($value['cust_info_no']))
            {
                // 회원번호 유효성
                $chk_cust =  DB::TABLE("cust_info")->SELECT("*")
                                                    ->WHERE('no', $value['cust_info_no'])
                                                    ->WHERE('save_status','Y')
                                                    ->first();

                if(empty($chk_cust))
                {
                    $waring = "회원번호가 유효하지 않습니다.";
                    $return["waring"] = $waring;
                    return $return;
                }
            }
            else
            {
                $waring = "필수값 체크 : 회원번호 확인필요"; 
                $return["waring"] = $waring;
                return $return;
            }
        }
        else
        {
            if(!empty($value['loan_info_guarantor_no']))
            {
                // 보증인번호 유효성
                $chk_g =  DB::TABLE("loan_info_guarantor")->SELECT("*")
                                                    ->WHERE('no', $value['loan_info_guarantor_no'])
                                                    ->WHERE('save_status','Y')
                                                    ->first();

                if(empty($chk_g))
                {
                    $waring = "보증인번호가 유효하지 않습니다.";
                    $return["waring"] = $waring;
                    return $return;
                }
            }
            else
            {
                $waring = "필수값 체크 : 보증인번호 확인필요"; 
                $return["waring"] = $waring;
                return $return;
            }
        }

        // 계약번호 유효성
        $chk_loan =  DB::TABLE("loan_info")->SELECT("*")
                                            ->WHERE('no', $value['loan_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();

        if(empty($chk_loan))
        {
            $waring = "계약번호가 유효하지 않습니다.";
            $return["waring"] = $waring;
            return $return;
        }
    }

    // 법원코드 업데이트
    public function courtCdChange()
	{
        // 필수키
        $this->requirArray = Array('court_nm'        => '법원명',
                                    'no'             => '법착번호',
                                    'cust_info_no'   => '고객번호',
                                    'loan_info_no'   => '계약번호',
                                    'code_nm'        => '코드명',
                                );

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $file = "";
        $ERROR = Array();

        $result = 1;
        if(!empty($result))
        {
            $path = "청 법착관리 법원명 (20231010).xlsx";

            $total_cnt = 0; 
            $i = 0;

            $null_cnt = 0;
            $upd_cnt = 0;

            if(Storage::disk('public')->exists($path))
            {
                echo "파일 O\n";

                $file = Storage::path('/public/'.$path);

                $colHeader  = array('법원명', '법착번호', 
                                    '고객번호', '계약번호',
                                    '코드', '코드명'
                                    );
                                    
                $colNm      = array(
                                    'court_nm'           => '0',	    // 법원명(필수)
                                    'no'                 => '1',	    // 법착번호(필수)
                                    'cust_info_no'	     => '2',	    // 고객번호(필수)
                                    'loan_info_no'       => '3',        // 계약번호(필수)
                                    'court_cd'	         => '4',	    // 코드
                                    'code_nm'            => '5',        // 코드명(필수)
                                    );

                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    echo "엑셀파일 헤더 불일치\n";
                }
                else
                {
                    echo "진행\n";

                    foreach($excelData as $_DATA) 
                    {
                        unset($_UPD, $chk);

                        $arrayCheck = Array();

                        // 데이터 정리
                        foreach($_DATA as $key => $val) 
                        {
                            $val = trim($val);
                            $_UPD[$key] = $val;
                        }

                        $total_cnt++;
                        
                        $arrayCheck = $this->validCheck1($_UPD);

                        if(isset($arrayCheck["waring"]))
                        {
                            $_UPD['err_msg'] = $arrayCheck["waring"];
                            $ERROR[$i] = $_UPD;
                            $i++;
                            
                            continue;
                        }

                        // 회원번호에 zb를 붙여서 업로드할경우 제거
                        if(strpos($_UPD['cust_info_no'], 'zb') !== false)
                        {
                            $_UPD['cust_info_no'] = str_replace('zb', '', $_UPD['cust_info_no']);
                        }  
                        
                        if($_UPD['code_nm'] == '확인불가')
                        {
                            $null_cnt ++;
                        }
                        else
                        {
                            $upd_cnt ++;
                            $rslt = DB::dataProcess("UPD", "loan_info_law", ['court_cd'=>$_UPD['court_cd']], ["no"=>$_UPD['no'], "cust_info_no"=>$_UPD['cust_info_no'], "loan_info_no"=>$_UPD['loan_info_no']]);
                        }
                    }
                    echo "미입력: ".$null_cnt."건\n";
                    echo "업데이트: ".$upd_cnt."건\n";
                }
            }
            else
            {
                echo "엑셀파일 미존재\n";
            }
        }
        else
        {
            echo "대기중인 일괄업데이트 미존재\n";
        }

        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'abs');

            $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);
            $file_path = $file_arr[1]."/".$file_arr[2];
            $file_name = $file_arr[3];

            echo "실패 로그파일 생성: 실패건이 있을시 결과파일 만든다.\n";
	    }
	}

    // 법원코드 업데이트_유효성 검사
    public function validCheck_courtCdChange($value) 
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

        if(strpos($value['cust_info_no'], 'zb') !== false)
        {
            $value['cust_info_no'] = str_replace('zb', '', $value['cust_info_no']);
        }

        // 회원번호 유효성
        $chk_cust =  DB::TABLE("cust_info")->SELECT("*")
                                            ->WHERE('no', $value['cust_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();

        if(empty($chk_cust))
        {
            $waring = "회원번호가 유효하지 않습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 계약번호 유효성
        $chk_loan =  DB::TABLE("loan_info")->SELECT("*")
                                            ->WHERE('no', $value['loan_info_no'])
                                            ->WHERE('cust_info_no', $value['cust_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();

        if(empty($chk_loan))
        {
            $waring = "계약번호가 유효하지 않습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 법착번호 유효성
        $chk_law =  DB::TABLE("loan_info_law")->SELECT("*")
                                            ->WHERE('no', $value['no'])
                                            ->WHERE('cust_info_no',$value['cust_info_no'])
                                            ->WHERE('loan_info_no',$value['loan_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();

        if(empty($chk_law))
        {
            $waring = "법착번호가 유효하지 않습니다.";
            $return["waring"] = $waring;
            return $return;
        }
    }

    // 업데이트 영업일보
    public function updateReport()
    {
        $start_date = Carbon::createFromDate(2021, 1, 1);
        $end_date = Carbon::createFromDate(2023, 12, 13);

        // 시작 날짜부터 종료 날짜까지 반복
        while ($start_date->lte($end_date)) 
        {
            $info_date = $start_date->format('Ymd');

            // Artisan 명령 실행
            Artisan::call("InsertReport:DailyLoan '' " . $info_date);

            // 다음 날짜로 이동
            $start_date->addDay();
        }
    }

    // 마감데이터 업데이트
    public function updateCloseData()
    {
        echo "start\n";
        $loan_info_no = DB::table('loan_info_trade')->SELECT(DB::raw('distinct on (loan_info_no) loan_info_no, coalesce(now_cost,0) as now_cost'))
                                          ->WHERE('save_status','Y')
                                          ->WHERE('save_time ','<','202307010000')
                                          ->WHERERAW("( LOAN_INFO_NO in (SELECT LOAN_INFO_NO FROM CLOSE_DATA WHERE INFO_DATE = '20230630') )")
                                          ->ORDERBY("loan_info_no")
                                          ->ORDERBY("seq","desc")                         
                                          ->get();

        foreach ($loan_info_no as $key => $value)
        {    
            $rslt = DB::dataProcess("UPD", "CLOSE_DATA", ['now_cost'=>$value->now_cost], ["loan_info_no"=>$value->loan_info_no,"INFO_DATE"=>'20230630']);
        }
        echo "end\n";
    }

    // 스윗 API 테스트
    private function go($url, $data = [], $post_yn=false) {
        $curl_opt = array(
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,                    
            CURLOPT_POST => false,
            CURLOPT_SSL_VERIFYPEER=> false,
            CURLOPT_HTTPHEADER => array(
                'Content-Type: application/json',
                'Accept: application/json',
                'Authorization: Bearer '
            ),
        );

        if ($post_yn) {
            $curl_opt[CURLOPT_POSTFIELDS] = json_encode($data);
            $curl_opt[CURLOPT_POST]       = true;
        }

        $curl = curl_init();
        curl_setopt_array($curl, $curl_opt);

        $result = curl_exec($curl);

        return $result = json_decode($result, 1);
    }

    // 일괄이미지 삭제1(수정해서 사용할 것)
    public function removeImages1()
    {
        $file = "";
        $ERROR = Array();

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $path = "test/img1.xlsx";

        $i = 0;
        // 파일 저장 위치
        if(Storage::disk('public')->exists($path))
        {
            echo "파일 O\n";

            $file = Storage::path('/public/'.$path);
            $colHeader = array('회원번호', '계약번호', '구분', '파일경로', '파일명');
            $colNm = array(
                'cust_info_no'	            => '0',	    // 회원번호(필수)
                'loan_info_no'              => '1',     // 계약번호(필수)
                'img_div'                   => '2',     // 구분
                'origin_filepath'           => '3',     // 파일경로
                'origin_filename'           => '4',     // 파일명   
            );

            $excelData = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader, 0); 

            // 엑셀 유효성 검사
            if (!isset($excelData)) 
            {
                // 엑셀파일 헤더 불일치
                echo "헤더 불일치\n";
            }
            else 
            {
                // 상태 '진행중' 변경
                echo "[상태: 진행중]\n";

                foreach ($excelData as $_DATA) 
                {
                    unset($_INS);

                    // 데이터 정리
                    foreach($_DATA as $key => $val) 
                    {
                        $val = trim($val);
                        $_INS[$key] = $val;

                    }
                        
                    $arrayCheck = $this->validCheckImages($_INS);

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

                    $name_array = explode(".", $_INS['origin_filename']);
                    
                    // 이미지구분
                    $imgDivArray    = array_flip(Func::getConfigArr('img_div_cd'));                    // 구분

                    // 청에서 sftp 로 업로드한 파일위치
                    $_IMG['extension']              = $name_array[sizeof($name_array) - 1];
                    $_IMG['img_div_cd']             = $_IMG['taskname'] = $imgDivArray[$_INS['img_div']];

                    $result = DB::TABLE("cust_info_img")
                                                    ->SELECT("*")
                                                    ->WHERE('cust_info_no',$_INS['cust_info_no'])
                                                    ->WHERE('loan_info_no',$_INS['loan_info_no'])
                                                    ->WHERE('origin_filename',$_INS['origin_filename'])
                                                    ->WHERE('extension',$_IMG['extension'])
                                                    ->WHERE('save_status','Y')
                                                    ->WHERE('img_div_cd',$_IMG['img_div_cd'])
                                                    ->WHERE('taskname',$_IMG['taskname'])
                                                    ->WHERE('folder_name','20230809')
                                                    ->first();

                    if(!empty($result->file_path))
                    {
                        $rs   = Storage::disk('erp_data_img')->delete("/".$result->file_path);

                        if($rs)
                        {
                            $rs = DB::TABLE("cust_info_img")
                                                    ->WHERE('cust_info_no',$result->cust_info_no)
                                                    ->WHERE('loan_info_no',$result->loan_info_no)
                                                    ->WHERE('origin_filename',$result->origin_filename)
                                                    ->WHERE('extension',$result->extension)
                                                    ->WHERE('save_status','Y')
                                                    ->WHERE('img_div_cd',$result->img_div_cd)
                                                    ->WHERE('taskname',$result->taskname)
                                                    ->WHERE('folder_name','20230809')
                                                    ->DELETE();
                        }
                        else
                        {
                            $_INS['err_msg'] = "파일삭제에 실패하였습니다. 관리자에게 문의해주세요.";
                            $ERROR[$i] = $_INS;
                            $i++;
                            
                            continue;
                        }
                    }
                    else
                    {
                        $_INS['err_msg'] = "일치하는 데이터가 없습니다. 관리자에게 문의해주세요.";
                        $ERROR[$i] = $_INS;
                        $i++;
                        
                        continue;
                    }
                }
            }
        }
        else
        {
            echo "파일 X\n";
        }

        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'img');

            $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);

            echo "\n";
            echo print_r($file_arr,1)."\n";
        }
    }

    // 일괄이미지 삭제2(수정해서 사용할 것)
    public function removeImages2()
    {
        $file = "";
        $ERROR = Array();

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $path = "test/img2.xlsx";

        $i = 0;
        // 파일 저장 위치
        if(Storage::disk('public')->exists($path))
        {
            echo "파일 O\n";

            $file = Storage::path('/public/'.$path);
            $colHeader = array('회원번호', '계약번호', '구분', '파일경로', '파일명');
            $colNm = array(
                'cust_info_no'	            => '0',	    // 회원번호(필수)
                'loan_info_no'              => '1',     // 계약번호(필수)
                'img_div'                   => '2',     // 구분
                'origin_filepath'           => '3',     // 파일경로
                'origin_filename'           => '4',     // 파일명   
            );

            $excelData = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader, 0); 

            // 엑셀 유효성 검사
            if (!isset($excelData)) 
            {
                // 엑셀파일 헤더 불일치
                echo "헤더 불일치\n";
            }
            else 
            {
                // 상태 '진행중' 변경
                echo "[상태: 진행중]\n";

                foreach ($excelData as $_DATA) 
                {
                    unset($_INS);

                    // 데이터 정리
                    foreach($_DATA as $key => $val) 
                    {
                        $val = trim($val);
                        $_INS[$key] = $val;

                    }
                        
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

                    $name_array = explode(".", $_INS['origin_filename']);
                    
                    // 이미지구분
                    $imgDivArray    = array_flip(Func::getConfigArr('img_div_cd'));                    // 구분

                    // 청에서 sftp 로 업로드한 파일위치
                    $_IMG['extension']              = $name_array[sizeof($name_array) - 1];
                    $_IMG['img_div_cd']             = $_IMG['taskname'] = $imgDivArray[$_INS['img_div']];

                    $result = DB::TABLE("cust_info_img")
                                                    ->SELECT("*")
                                                    ->WHERE('cust_info_no',$_INS['cust_info_no'])
                                                    ->WHERE('loan_info_no',$_INS['loan_info_no'])
                                                    ->WHERE('origin_filename',$_INS['origin_filename'])
                                                    ->WHERE('extension',$_IMG['extension'])
                                                    ->WHERE('save_status','Y')
                                                    ->WHERE('img_div_cd',$_IMG['img_div_cd'])
                                                    ->WHERE('taskname',$_IMG['taskname'])
                                                    ->WHERE('folder_name','20230809')
                                                    ->first();

                    if(!empty($result->file_path))
                    {
                        $rs   = Storage::disk('erp_data_img')->delete("/".$result->file_path);

                        if($rs)
                        {
                            $rs = DB::TABLE("cust_info_img")
                                                    ->WHERE('cust_info_no',$result->cust_info_no)
                                                    ->WHERE('loan_info_no',$result->loan_info_no)
                                                    ->WHERE('origin_filename',$result->origin_filename)
                                                    ->WHERE('extension',$result->extension)
                                                    ->WHERE('save_status','Y')
                                                    ->WHERE('img_div_cd',$result->img_div_cd)
                                                    ->WHERE('taskname',$result->taskname)
                                                    ->WHERE('folder_name','20230809')
                                                    ->DELETE();
                        }
                        else
                        {
                            $_INS['err_msg'] = "파일삭제에 실패하였습니다. 관리자에게 문의해주세요.";
                            $ERROR[$i] = $_INS;
                            $i++;
                            
                            continue;
                        }
                    }
                    else
                    {
                        $_INS['err_msg'] = "일치하는 데이터가 없습니다. 관리자에게 문의해주세요.";
                        $ERROR[$i] = $_INS;
                        $i++;
                        
                        continue;
                    }
                }
            }
        }
        else
        {
            echo "파일 X\n";
        }

        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'img');

            $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);

            echo "\n";
            echo print_r($file_arr,1)."\n";
        }
    }

    // 이미지삭제 함수의 유효성체크
    public function validCheckImages($value) 
    {
        $waring = "";
        $return = Array();
        
        
        // 필수키
        $requirArray = Array('cust_info_no'       => '회원번호',
                                    'loan_info_no'      => '계약번호', 
                                    'img_div'           => '구분',
                                    'origin_filepath'   => '파일경로', 
                                    'origin_filename'   => '파일명',
                                );

        // 이미지구분
        $imgDivArray    = array_flip(Func::getConfigArr('img_div_cd'));                    // 구분

        // 필수값 확인
        foreach($requirArray as $chk_key => $chk_val)
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
            $waring = "해당채권을 찾을 수 없습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 결제방법 확인
        if(!array_key_exists($value['img_div'], $imgDivArray)) 
        {
            $waring = "유효한 구분이 없습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 파일존재여부 확인
        $value['origin_filepath'] = config('app.sftp_img_path').$value['origin_filepath'];        
        if(!file_exists($value['origin_filepath']."/".$value['origin_filename']))
        {
            $waring = "업로드한 파일이 존재하지 않습니다. 파일경로를 확인해주세요.";
            $return["waring"] = $waring;
            return $return;
        }
    }

    // 스케줄갱신
    public function updatePlan()
    {
        $loan   = new Loan('268727	');
        $loan->updateSettlePlan('21945', '');

        exit;
    }

    // 초본열람명세 업데이트 1회성처리
    public function abstractUpdate()
	{
        // 필수키
        $this->requirArray = Array('cust_info_no'  => '회원번호',
                                    'issue_date'   => '발급일자',
                                    'move_date'  => '전입일자',
                                    );

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $path = 'test/초본열람명세_20231017171836_k694.xlsx';
        $file = Storage::path('/public/'.$path);
        $ERROR = Array();

        $colHeader  = array('회원번호', '보증인번호', 
                                    '열람신청대상', '발급일자',
                                    '전입일자'
                                    );
                                    
        $colNm      = array(
                            'cust_info_no'           => '0',	    // 회원번호
                            'loan_info_guarantor_no' => '1',	    // 고객명
                            'target_div'             => '2',	    // 열람신청대상
                            'issue_date'             => '3',	    // 발급일자
                            'move_date'    	         => '4',	    // 전입일자
                            );

        $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

        $cnt = 0;
        $i = 0;

        if(Storage::disk('public')->exists($path))
        {
            // 엑셀 유효성 검사
            if(!isset($excelData))
            {
                echo '헤더불일치';
            }
            else
            {
                echo '값 존재함';
                foreach($excelData as $_DATA) 
                {
                    unset($_ABS);
                    $arrayCheck = Array();

                    // 데이터 정리
                    foreach($_DATA as $key => $val) 
                    {
                        $val = trim($val);
                        $ARR[$key] = $val;
                    }

                    $cnt++;
                    
                    $arrayCheck = $this->abstractUpdateCheck($ARR);

                    if(isset($arrayCheck["waring"]))
                    {
                        $ARR['err_msg'] = $arrayCheck["waring"];
                        $ERROR[$i] = $ARR;
                        $i++;
                        
                        continue;
                    }

                    // 회원번호에 zb를 붙여서 업로드할경우 제거
                    if(strpos($ARR['cust_info_no'], 'zb') !== false)
                    {
                        $ARR['cust_info_no'] = str_replace('zb', '', $ARR['cust_info_no']);
                    }

                    $ARR['issue_date'] = preg_replace('/[^0-9]/', '', $ARR['issue_date']);
                    $ARR['move_date']  = preg_replace('/[^0-9]/', '', $ARR['move_date']);
                    
                    if($ARR['target_div']=='차주')
                    {
                        $rslt1 = DB::dataProcess('UPD', 'abstract', ["issue_date"=>$ARR['issue_date'], "move_date"=>$ARR['move_date']], ["cust_info_no"=>$ARR['cust_info_no']]);
                        $rslt2 = DB::dataProcess('UPD', 'abstract_extra', ["issue_date"=>$ARR['issue_date'], "move_date"=>$ARR['move_date']], ["cust_info_no"=>$ARR['cust_info_no']]);
                    }
                    else if($ARR['target_div']=='보증인')
                    {
                        $rslt1 = DB::dataProcess('UPD', 'abstract', ["issue_date"=>$ARR['issue_date'], "move_date"=>$ARR['move_date']], ["loan_info_guarantor_no"=>$ARR['loan_info_guarantor_no']]);
                        $rslt2 = DB::dataProcess('UPD', 'abstract_extra', ["issue_date"=>$ARR['issue_date'], "move_date"=>$ARR['move_date']], ["loan_info_guarantor_no"=>$ARR['loan_info_guarantor_no']]);
                    }
                    else
                    {
                        $ARR['err_msg'] = '차주랑 보증인 둘다 없음';
                        $ERROR[$i] = $ARR;
                        $i++;
                        
                        continue;
                    }
                }
            }
        }
        else
        {
            echo '엑셀파일 미존재';
        }

        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'abs');

            $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);
            $file_path = $file_arr[1]."/".$file_arr[2];
            $file_name = $file_arr[3];

            echo "실패 로그파일 생성: 실패건이 있을시 결과파일 만든다.\n";
	    }
	}

    // 테스트용2
    public function abstractUpdateCheck($value)
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
        
        if(!($value['target_div']=="보증인" || $value['target_div']=="차주"))
        {
            $waring = "구분을 올바르게 입력바랍니다.\n(차주 또는 보증인 입력)";
            $return["waring"] = $waring;
            return $return;
        }

        // 회원번호에 zb를 붙여서 업로드할경우 제거
        if(strpos($value['cust_info_no'], 'zb') !== false)
        {
            $value['cust_info_no'] = str_replace('zb', '', $value['cust_info_no']);
        }

        if($value['target_div']=="차주")
        {
            if(!empty($value['cust_info_no']))
            {
                // 회원번호 유효성
                $chk_cust =  DB::TABLE("cust_info")->SELECT("*")
                                                    ->WHERE('no', $value['cust_info_no'])
                                                    ->WHERE('save_status','Y')
                                                    ->first();

                if(empty($chk_cust))
                {
                    $waring = "회원번호가 유효하지 않습니다.";
                    $return["waring"] = $waring;
                    return $return;
                }

                // 열람신청(A), 진행(S) 상태인 경우 중복 신청 방지
                $chk_loan = DB::table("abstract")->where("cust_info_no",$value['cust_info_no'])
                                                ->where("target_div","C")
                                                ->where("status","Y")
                                                ->where("save_status","Y")
                                                ->first();
        
                if(empty($chk_loan))
                {
                    $waring = "완료 안된거";
                    $return["waring"] = $waring;
                    return $return;
                }
            }
            else
            {
                $waring = "필수값 체크 : 회원번호 확인필요"; 
                $return["waring"] = $waring;
                return $return;
            }
        }
        else
        {
            if(!empty($value['loan_info_guarantor_no']))
            {
                // 보증인번호 유효성
                $chk_g =  DB::TABLE("loan_info_guarantor")->SELECT("*")
                                                    ->WHERE('no', $value['loan_info_guarantor_no'])
                                                    ->WHERE('save_status','Y')
                                                    ->first();

                if(empty($chk_g))
                {
                    $waring = "보증인번호가 유효하지 않습니다.";
                    $return["waring"] = $waring;
                    return $return;
                }

                // 열람신청(A), 진행(S) 상태인 경우 중복 신청 방지
                $chk_loan = DB::table("abstract")->where("loan_info_guarantor_no",$value['loan_info_guarantor_no'])
                                                ->where("target_div","G")
                                                ->where("status","Y")
                                                ->where("save_status","Y")
                                                ->first();
        
                if(empty($chk_loan))
                {
                    $waring = "완료 안된거";
                    $return["waring"] = $waring;
                    return $return;
                }
            }
            else
            {
                $waring = "필수값 체크 : 보증인번호 확인필요"; 
                $return["waring"] = $waring;
                return $return;
            }
        }
    }

    // 전체메모 계약번호 업데이트
    public function memoUpdate()
	{
        $loan_info_update = DB::table('CUST_INFO_MEMO')
                                ->SELECT('*')
                                ->WHERERAW("LOAN_INFO_NO = CUST_INFO_NO")
                                ->get();

        foreach ($loan_info_update as $key => $val)
        {
            $loan_info_no = DB::TABLE('loan_info')->SELECT('no')->WHERE('cust_info_no',$val->cust_info_no)->FIRST();

            if(!empty($loan_info_no->no))
            {
                DB::dataProcess('UPD','CUST_INFO_MEMO', ['loan_info_no'=>$loan_info_no->no], ['no'=>$val->no]);
            }
        }
	}

    // 연서주임이 사용한 - 이미지 체크웹
	public function robertTest(Request $request)
    {
		$test = DB::table('cust_info_img')
			->select('no', 'loan_info_no', 'file_path')
			->where('save_status', 'Y')
			->where('del_id', 'k825')
			->where('del_time', '20231024082815')
			->orderBy('no', 'desc')
			->get();

		$y_loan_info_no_list = $n_loan_info_no_list = [];
		$y_cnt = $n_cnt = 0;
		foreach ($test as $key => $val) {
			if (file_exists(storage_path('app/ERP/data_img').'/'.$val->file_path)) {
				array_push($y_loan_info_no_list, $val->loan_info_no);
				$y_cnt++;
			} else {
				array_push($n_loan_info_no_list, ['loan_info_no' => $val->loan_info_no, 'no' => $val->no]);
				$n_cnt++;
			}
		}

		echo '<pre>';
		print_r($n_loan_info_no_list);
		echo '</pre>';
    }

    // 남 여 사업자 체크
    public function chkMan()
	{
        // 매입사의 계약번호 추출
        $loan_info =  DB::TABLE("loan_info l")->SELECT("c.cust_type, c.ssn, l.buy_loan_money")
                                            ->JOIN("cust_info c", "c.no", "=", "l.cust_info_no")
                                            ->WHERE('l.seller_no', '32')      //뉴아리원대부
                                            ->WHERE('c.save_status','Y')
                                            ->WHERE('l.save_status','Y')
                                            ->get();

        $arr_gender = array();
        $arr_gender['사업자']['count'] = 0;
        $arr_gender['사업자']['buy_loan_money'] = 0;
        $arr_gender['총합']['count'] = 0;
        $arr_gender['총합']['buy_loan_money'] = 0;
        $arr_gender['남']['count'] = 0;
        $arr_gender['남']['buy_loan_money'] = 0;
        $arr_gender['여']['count'] = 0;
        $arr_gender['여']['buy_loan_money'] = 0;
        foreach ($loan_info as $k => $v)
        {
            if(!empty($v->cust_type) && $v->cust_type == "2")
            {
                $arr_gender['사업자']['count']++;
                $arr_gender['사업자']['buy_loan_money'] += $v->buy_loan_money;
            }
            else
            {
                $gender_flag = substr(Func::decrypt($v->ssn, 'ENC_KEY_SOL'), 6 ,1);

                if( $gender_flag=="1" || $gender_flag=="3" || $gender_flag=="5" || $gender_flag=="7" )
                {
                    $arr_gender['남']['count']++;
                    $arr_gender['남']['buy_loan_money'] += $v->buy_loan_money;
                }
                else if( $gender_flag=="2" || $gender_flag=="4" || $gender_flag=="6" || $gender_flag=="8" )
                {
                    $arr_gender['여']['count']++;
                    $arr_gender['여']['buy_loan_money'] += $v->buy_loan_money;
                }
            }

            $arr_gender['총합']['count']++;
            $arr_gender['총합']['buy_loan_money'] += $v->buy_loan_money;
        }

        echo print_r($arr_gender,1);
	}

    public function misu()
    {
        // 필수키
        $this->requirArray = Array('seller_no' => '계약번호', 
                                    'convert_c_no' => '매입사회원번호', 
                                    'convert_l_no' => '매입사계약번호', 
                                );

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $file = "";
        $ERROR = Array();

        $path = "test/NA2_MISU.xlsx";

        $total_cnt = 0; 
        $error_cnt = 0; 
        $i = 0;
        if(Storage::disk('public')->exists($path))
        {
            echo "파일 O\n";

            $file = Storage::path('/public/'.$path);

            $colHeader  = array('매입사번호', '매입사회원번호','매입사계약번호', '최초미수이자');
            $colNm      = array(
                'seller_no'         => '0',	    // 매입사번호
                'convert_c_no'	    => '1',	    // 매입사회원번호
                'convert_l_no'      => '2',	    // 매입사계약번호
                'buy_misu_money'	=> '3',	    // 최초미수이자
            );

            $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

            // 엑셀 유효성 검사
            if(!isset($excelData))
            {
                echo "엑셀파일 헤더 불일치\n";
            }
            else
            {
                echo "진행중\n";

                foreach($excelData as $_DATA) 
                {
                    unset(
                        $loanArrayCheck,
                        $_INS,
                        $_COURT_CASE_A,
                        $_COURT_CASE_B,
                        $_COURT_CASE_C,
                        $_COURT_CASE_D,
                        $_CUST,
                        $_LOAN_MEMO,
                        $_LOAN_TRADE,
                        $_LOAN_RATE,
                        $_LOAN_CDAY,
                        $_BEN,
                        $loan_info_no,
                        $loan_info_trade_no,
                        $date1,
                        $date2,
                        $loan_info
                    );

                    $loanArrayCheck = Array();

                    // 데이터 정리
                    foreach($_DATA as $key => $val) 
                    {
                        $val = trim($val);
                        $_INS[$key] = $val;
                    }

                    $total_cnt++;

                    $loanArrayCheck = $this->validMisuCheck($_INS);

                    if(isset($loanArrayCheck["waring"]))
                    {
                        $_INS['err_msg'] = $loanArrayCheck["waring"];
                        $ERROR[$i] = $_INS;
                        $i++;
                        
                        continue;
                    }

                    // 사건번호 데이터 추출 및 빈값 업데이트 제거
                    // null을 입력했을경우는 빈값으로 업데이트
                    foreach($_INS as $key=>$val)
                    {
                        // 값이 없으면 unset
                        if($val == "")
                        {
                            unset($_INS[$key]);
                            continue;
                        }

                        // 값이 null 이면 빈값으로 업데이트
                        if($val == "null" || $val == "NULL")
                        {
                            $_INS[$key] = '';
                            continue;
                        }
                    }

                    $loan_info =  DB::TABLE("loan_info")->SELECT("*")
                                            ->WHERE('seller_no', $_INS['seller_no'])
                                            ->WHERE('convert_c_no', $_INS['convert_c_no'])
                                            ->WHERE('convert_l_no', $_INS['convert_l_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();

                    $_INS['misu_money']         = $_INS['buy_misu_money'] ?? 0;
                    $_INS['interest_sum']       = $_INS['misu_money'] + $loan_info->interest_sum;
                    
                    $_INS['return_date_interest']= $_INS['misu_money'] + $loan_info->return_date_interest;
                    $_INS['charge_money']       = $_INS['misu_money'] + $loan_info->charge_money;
                    $_INS['fullpay_money']      = $_INS['misu_money'] + $loan_info->fullpay_money;

                    if( $loan_info->return_date_biz >= date('Ymd') )
                    {
                        $_INS['gugan_interest_sum']  = 0;
                        $_INS['gugan_interest_date'] = "";
                    }
                    else
                    {
                        $_INS['gugan_interest_sum'] = $_INS['return_date_interest'];
                        $_INS['gugan_interest_date']= $loan_info->return_date_biz;
                    }

                    if(empty($_INS['buy_misu_money']))
                    {
                        $_INS['buy_misu_money']     = 0;    // 최초미수이자
                    }

                    $rslt = DB::dataProcess('UPD', 'loan_info', $_INS, ["no"=>$loan_info->no]);

                    // 매입한거 거래원장에 찍어야됨
                    $_LOAN_TRADE['misu_money']  = $_INS['misu_money'];

                    $rslt3 = DB::dataProcess('UPD', 'loan_info_trade', $_LOAN_TRADE, ["loan_info_no"=>$loan_info->no, "trade_type"=>'31']);
                }

                if(!empty($ERROR))
                {
                    $error_cnt = count($ERROR);
                }
                $ok_count = $total_cnt-$error_cnt;
                
                echo "전체건수".$total_cnt."\n";
                echo "성공건수".$ok_count."\n";
                echo "실패건수".$error_cnt."\n";
                
                echo "종료\n";
            }
        }
        else
        {
            echo "엑셀파일 미존재\n";
        }

        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'buy');

            $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);
            $file_path = $file_arr[1]."/".$file_arr[2]."/".$file_arr[3];
            $file_name = $file_arr[4];
            
            echo "에러파일생성완료\n";
	    }
    }

    public function validMisuCheck($value) 
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

        // 채무자 확인
        $chk_cust =  DB::TABLE("cust_info")->SELECT("*")
                                            ->WHERE('seller_no', $value['seller_no'])
                                            ->WHERE('convert_c_no', $value['convert_c_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(empty($chk_cust))
        {
            $waring = "해당 매입사의 채무자 존재하지않습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 계약존재 확인
        $chk_loan =  DB::TABLE("loan_info")->SELECT("*")
                                            ->WHERE('seller_no', $value['seller_no'])
                                            ->WHERE('convert_c_no', $value['convert_c_no'])
                                            ->WHERE('convert_l_no', $value['convert_l_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(empty($chk_loan))
        {
            $waring = "계약이 존재하지않습니다.";
            $return["waring"] = $waring;
            return $return;
        }
    }

    // 매입사번호, 매입사회원번호로 전화번호 일괄 업데이트
    public function changePh()
    {
        // 필수키
        $this->requirArray = Array('seller_no' => '매입사번호', 
                                'convert_c_no' => '매입사회원번호(차주일련번호)', 
                                );

        // 전화번호 키
        $this->phoneKeyArray = Array('ph2', 'ph3');

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $ERROR = Array();

            $path = "test/NA2_PH.xlsx"; // test 폴더 안에 파일 넣기

            $total_cnt = 0; 
            $i = 0;
            if(Storage::disk('public')->exists($path))
            {
                echo "파일 O\n";

                $file = Storage::path('/public/'.$path);

                $colHeader  = array('매입사번호', '매입사회원번호(차주일련번호)', '휴대전화', '직장전화');
                $colNm      = array(
                    'seller_no'	        => '0',	    // 매입사번호
                    'convert_c_no'	    => '1',	    // 매입사회원번호(차주일련번호)
                    'ph2'	            => '2',	    // 휴대전화
                    'ph3'               => '3',     // 직장전화
                );

                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    echo "엑셀파일 헤더 불일치\n";
                }
                else
                {
                    echo "엑셀파일 헤더 일치\n";
                    foreach($excelData as $_DATA) 
                    {
                        unset($_UPD, $_CUST_EXTRA, $chk_cust);

                        $arrayCheck = Array();
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
                                $_UPD[$key] = '';
                            }
                        }

                        $arrayCheck = $this->validCheck_changePh($_UPD);

                        if(isset($arrayCheck["waring"]))
                        {
                            $_UPD['err_msg'] = $arrayCheck["waring"];
                            $ERROR[$i] = $_UPD;
                            $i++;
                            
                            continue;
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

                        unset($_UPD['ph2'], $_UPD['ph3'], $_CUST_EXTRA['seller_no'], $_CUST_EXTRA['convert_c_no']);

                        if(!empty($_CUST_EXTRA))
                        {
                            $chk_cust =  DB::TABLE("cust_info")->SELECT("*")
                            ->WHERE('seller_no', $_UPD['seller_no'])
                            ->WHERE('convert_c_no', $_UPD['convert_c_no'])
                            ->WHERE('save_status','Y')
                            ->first();
                            $rslt = DB::dataProcess('UPD', 'cust_info_extra', $_CUST_EXTRA, ["cust_info_no"=>$chk_cust->no]);
                        }   
                    }

                }
                echo "성공!\n";
            }
            else
            {
                echo "엑셀파일 미존재\n";
            }
        
        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'cust_info');

            $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);
            $file_path = $file_arr[1]."/".$file_arr[2]."/".$file_arr[3];
            $file_name = $file_arr[4];
        }
    }

    public function validCheck_changePh($value) 
    {

        $waring = "";
        $return = Array();

        unset($ph2_explode, $ph3_explode);

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

        // 매입사 회원번호 확인
        $chk_cust =  DB::TABLE("cust_info")->SELECT("*")
                                            ->WHERE('seller_no', $value['seller_no'])
                                            ->WHERE('convert_c_no', $value['convert_c_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(empty($chk_cust))
        {
            $waring = "A:매입사 회원번호 혹은 매입사번호를 올바르게 입력바랍니다.";
            $return["waring"] = $waring;
            return $return;
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

    // 법착 확정일자 소멸시효 일괄처리
    public function law_confirm_date()
	{
        unset($COUNT_LOAN);

        $COUNT_LOAN = DB::TABLE("loan_info l")->JOIN('loan_info_law w', 'w.loan_info_no', '=', 'l.no')
                                        ->SELECT("w.law_confirm_date, l.lost_date, w.no")
                                        ->whereRaw("(l.lost_date < w.law_confirm_date)")
                                        ->WHERE('l.save_status', 'Y')
                                        ->WHERE('w.save_status', 'Y')
                                        ->get();

        foreach ($COUNT_LOAN as $key => $val)
        {
            Trade::updateLoanInfoLostDate('L', $val->no);
            
            if($key%100==0)
            {
                echo $key."\n";
            }
        }
	}
    
    // 일괄메모삭제
    public function delMemo()
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

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $file = "";
        $ERROR = Array();

        $path = "test/memo_delete1.xlsx";

        $total_cnt = 0; 
        $error_cnt = 0; 
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
                echo "엑셀파일 헤더 불일치\n";
            }
            else
            {
                echo "진행중\n";

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

                    $arrayCheck = $this->validmemoCheck($_INS);
    
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
                    $_INS['save_status'] = "Y";
                    $_INS['is_batch'] = "Y";

                    $_UPD['save_status'] = "N";
                    $_UPD['del_time'] = date("YmdHis");
                    $_UPD['del_id'] = 'SYSTEM';

                    $rslt = DB::dataProcess('UPD', 'cust_info_memo', $_UPD, $_INS);
                }
            }
        }
        else
        {
            echo "엑셀파일 미존재\n";
        }

        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'memo');

            $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);
            $file_path = $file_arr[1]."/".$file_arr[2]."/".$file_arr[3];
            $file_name = $file_arr[4];
            
            echo "에러파일생성완료\n";
	    }
    }

    public function validmemoCheck($value) 
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

        if(!preg_match('/^[0-9]+$/', $value['cust_info_no']) || !preg_match('/^[0-9]+$/', $value['loan_info_no']))
        {
            $waring = "계약번호 또는 회원번호를 올바르게 입력바랍니다.";
            $return["waring"] = $waring;
            return $return;
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

    // 문자 실패건 다시보내기
    public function sendSms()
	{
        $sms_log = DB::TABLE("submit_sms_log")
                                ->SELECT("*")
                                ->whereRaw("(no > 3955849 and send_result = 'q')")
                                ->WHERE('save_status', 'Y')
                                ->get();
        
        foreach ($sms_log as $key => $val)
        {
            unset($arrayMsg, $_DATA);

            $arrayMsg = array();

            // 데이터 정리
            foreach($val as $k => $v) 
            {
                $arrayMsg[$k] = $v;
            }

            log::channel('pl_work')->info(print_r($arrayMsg,1));

            // 띄어쓰기나 하이픈(-) 제외
            if(!empty($arrayMsg['sender']) || !empty($arrayMsg['receiver']))
            {
                $arrayMsg['sender']   = preg_replace('/[^0-9]/', '', $arrayMsg['sender']);
                $arrayMsg['receiver'] = preg_replace('/[^0-9]/', '', $arrayMsg['receiver']);
            }

            $cmp_msg_id = 0;
            DB::beginTransaction();
            
            // 예약시간
            $reserve_time = isset($arrayMsg['reserve_time']) ? $arrayMsg['reserve_time'] : '';

            // 문자발송 로그 데이터 세팅
            $_DATA = [
                "loan_app_no"	    =>	$arrayMsg['loan_app_no'] ?? null,
                "div"		        =>	'S',
                "ups_erp"		    =>	$arrayMsg['ups_erp'],
                "sms_div"	        =>	isset($arrayMsg['sms_div']) ? $arrayMsg['sms_div'] : NULL,
                "sender"	        =>	$arrayMsg['sender'],
                "receiver"	        =>	$arrayMsg['receiver'],
                "message"	        =>	$arrayMsg['message'],
                "save_id"	        =>	$arrayMsg['save_id'],
                "save_time"	        =>	date("YmdHis"),
                "save_status"	    =>	"Y",
                "reserve_time"	    =>	$reserve_time,
                "cust_info_no"      =>  $arrayMsg['cust_info_no'] ?? null,
            ];
            
            $rslt = DB::dataProcess("INS", "SUBMIT_SMS_LOG", $_DATA, null, $cmp_msg_id);

            if($rslt == 'Y')
            {
                unset($_DEL);

                $_DEL['save_status'] = 'N';
                $_DEL['del_id']      = 'SYSTEM';
                $_DEL['del_time']    = date("YmdHis");

                DB::dataProcess('UPD','SUBMIT_SMS_LOG', $_DEL, ['no'=>$arrayMsg['no']]);

                unset($_DATA);
                
                $msgByte = 0;
                if( mb_detect_encoding($arrayMsg["message"], ['UTF-8','EUC-KR'], true)=="UTF-8" )
                {
                    $msgByte = mb_strwidth(iconv("UTF-8","EUC-KR", $arrayMsg["message"]), "EUC-KR");
                }
                else 
                {
                    $msgByte = mb_strwidth($arrayMsg["message"], "EUC-KR");
                }

                $encMsg = Sms::npro_encode($arrayMsg['message']);

                // LMS 발송
                if( $msgByte > 90)
                {
                    // 6. MMS(LMS)
                    $MSG_TYPE = '6';
                    $sms_lms_div = 'L';

                    // 문자 컨텐츠 세팅
                    $_DATA = [
                        "MMS_REQ_DATE"	=>	($reserve_time != '') ? date("Y-m-d H:i:s", strtotime($reserve_time)) : DB::raw("NOW()"),
                        "FILE_CNT"		=>	1,
                        "MMS_BODY"		=>	$encMsg,
                        "MMS_SUBJECT"	=> 	Sms::npro_encode('[청대부]'),
                    ];

                    // 운영환경만
                    if(config('app.env')=='prod')
                    {
                        $CONT_SEQ = DB::connection("sms")->table("MMS_CONTENTS_INFO")->insertGetId($_DATA, "CONT_SEQ");
                    }
                    else 
                    {
                        $CONT_SEQ = rand(100000, 999999);
                    }
                }
                else 
                {
                    $MSG_TYPE = '4';
                    $sms_lms_div = 'S';
                }
                
                unset($_DATA);

                // 문자 메인 세팅
                $_DATA = [
                    "CUR_STATE"	=>	0,
                    "REQ_DATE"	=>	isset($arrayMsg["reserve_time"]) ? date("Y-m-d H:i:s", strtotime($arrayMsg["reserve_time"])) : DB::raw("NOW()"),
                    "CALL_TO"	=>	Sms::npro_encode($arrayMsg['receiver']),
                    "CALL_FROM"	=>	Sms::npro_encode($arrayMsg["sender"]),
                    "MSG_TYPE"	=>	$MSG_TYPE,
                    "CONT_SEQ"	=>	isset($CONT_SEQ) ? $CONT_SEQ : NULL,
                    "TRAN_ETC1"	=>	'K',	// 예전에 사용하던지점인데 필요시 세팅할 것.
                    "TRAN_ETC2"	=>	isset($cmp_msg_id) ? $cmp_msg_id : NULL,
                    "TRAN_ETC3"	=>	'N',
                ];

                if($MSG_TYPE == "4")
                {
                    $_DATA["SMS_TXT"] = $encMsg;
                }
                
                // 운영환경만
                if(config('app.env')=='prod')
                {
                    $msg_no = DB::connection('sms')->table("MSG_DATA")->insertGetId($_DATA, "MSG_SEQ");
                }
                else 
                {
                    $msg_no = rand(100000, 999999);
                }
                            
                // SMS/LMS 테이블 no 값 문자로그테이블에 저장
                if( $rslt=="Y" && $msg_no>0 )
                {
                    unset($_DATA);
                    $_DATA["send_msg_no"] = $msg_no;
                    $_DATA['sms_lms_div'] = $sms_lms_div;
                    DB::dataProcess('UPD','SUBMIT_SMS_LOG', $_DATA, ['no'=>$cmp_msg_id]);

                    DB::commit();

                    echo "Y\n";
                }
                else
                {
                    DB::rollback();

                    echo "N1\n";
                }
            }
            else
            {
                DB::rollback();

                echo 'N2';
            }    
        }   
	}

    // 완납금액 0원 완제처리
    public function endStatusE()
	{
        // 필수키
        $this->requirArray = Array('loan_info_no'  => '계약번호',
                                    'fullpay_date' => '완납일',
                                    );

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $path = 'test/fullpaydate.xlsx';
        $file = Storage::path('/public/'.$path);
        $ERROR = Array();

        $colHeader  = array('계약번호', '완납일');
                                    
        $colNm      = array(
                            'loan_info_no'           => '0',	    // 계약번호
                            'fullpay_date'           => '1',	    // 완납일
                            );

        $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

        $cnt = 0;
        $i = 0;

        if(Storage::disk('public')->exists($path))
        {
            // 엑셀 유효성 검사
            if(!isset($excelData))
            {
                echo '헤더불일치';
            }
            else
            {
                echo '값 존재함';
                foreach($excelData as $_DATA) 
                {
                    unset($_UPD, $arrayCheck, $v, $vl,$loan, $loan_info_trade_no);

                    $arrayCheck = Array();

                    // 데이터 정리
                    foreach($_DATA as $key => $val) 
                    {
                        $val = trim($val);
                        $_UPD[$key] = $val;
                    }

                    $cnt++;
                    
                    $arrayCheck = $this->validECheck($_UPD);

                    if(isset($arrayCheck["waring"]))
                    {
                        $_UPD['err_msg'] = $arrayCheck["waring"];
                        $ERROR[$i] = $_UPD;
                        $i++;
                        
                        continue;
                    }

                    // 거래순번
                    $loan = DB::TABLE("LOAN_INFO")->SELECT("*")->WHERE("no", $_UPD['loan_info_no'])->FIRST();
                    $loan = Func::chungDec(["LOAN_INFO_TRADE"], $loan);	// CHUNG DATABASE DECRYPT
                    $vl = (Array) $loan;

                    // 거래순번
                    $vt = DB::TABLE("LOAN_INFO_TRADE")->SELECT("MAX(SEQ) as seq")->WHERE("LOAN_INFO_NO", $_UPD['loan_info_no'])->FIRST();

		            // 직전 거래원장 그대로
                    $rslt = DB::TABLE("LOAN_INFO_TRADE")->SELECT("*")->WHERE("LOAN_INFO_NO", $_UPD['loan_info_no'])->WHERE("SAVE_STATUS", "Y")->ORDERBY("NO","DESC")->FIRST();
                    $rslt = Func::chungDec(["LOAN_INFO_TRADE"], $rslt);	// CHUNG DATABASE DECRYPT
                    $vc = (Array) $rslt;

                    // 완제
                    if( $vl['balance']==0 && $vl['settle_interest']==0 && $vl['sanggak_interest']==0 && ($vl['misu_money'] + $vl['cost_money'] + $vl['cost_origin'] + $vl['lack_delay_interest'] + $vl['lack_delay_money'] + $vl['lack_interest'] + $vl['settle_interest'])==0 )
                    {
                        // 계약정보 업데이트
                        unset($vc['no']);
                        
                        $vc['loan_info_no']         = $_UPD['loan_info_no'];
                        $vc['save_id']              = 'SYSTEM' ;
                        $vc['save_time']            = date("YmdHis");
                        $vc['trade_date']           = str_replace("-", "", $_UPD['fullpay_date']);
                        $vc['trade_money']          = 0;
                        $vc['save_status']          = "Y";
                        $vc['trade_div']            = 'I';
                        $vc['trade_type']           = '01';
                        $vc['seq']                  = ( $vt->seq ) ? $vt->seq + 1 : 1 ;
                        $vc['cost_money']           = 0;
                        $vc['cost_origin']          = 0;
                        $vc['lose_money']           = 0;
                        $vc['profit_money']         = 0;
                        $vc['trade_fee']            = 0;
                
                        $vc['trade_path_cd']        = "";
                        $vc['interest_detail']      = "";
                        $vc['replan_yn']            = "";
                        $vc['trade_money_real']     = 0;
                
                        // 계산
                        $vc['interest']             = 0;
                        $vc['delay_money']          = 0;
                        $vc['delay_interest']       = 0;
                        $vc['interest_term']        = 0;
                        $vc['interest_sdate']       = "";
                        $vc['interest_edate']       = "";
                        $vc['delay_term']           = 0;
                        $vc['delay_money_term']     = 0;
                        $vc['delay_money_sdate']    = "";
                        $vc['delay_money_edate']    = "";
                        $vc['delay_interest_term']  = 0;
                        $vc['delay_interest_sdate'] = "";
                        $vc['delay_interest_edate'] = "";

                        // 입금
                        $vc['return_dambo_set_fee']       = 0;
                        $vc['return_cost_money']          = 0;
                        $vc['return_cost_origin']         = 0;
                        $vc['return_misu_money']          = 0;
                        $vc['return_lack_delay_interest'] = 0;
                        $vc['return_lack_delay_money']    = 0;
                        $vc['return_lack_interest']       = 0;
                        $vc['return_delay_interest']      = 0;
                        $vc['return_delay_money']         = 0;
                        $vc['return_interest']            = 0;
                        $vc['return_settle_interest']     = 0;
                        $vc['return_interest_sum']        = 0;
                        $vc['return_origin']              = 0;

                        // 감면
                        $vc['lose_cost_money']            = 0;
                        $vc['lose_cost_origin']           = 0;
                        $vc['lose_misu_money']            = 0;
                        $vc['lose_lack_delay_interest']   = 0;
                        $vc['lose_lack_delay_money']      = 0;
                        $vc['lose_lack_interest']         = 0;
                        $vc['lose_delay_interest']        = 0;
                        $vc['lose_delay_money']           = 0;
                        $vc['lose_interest']              = 0;
                        $vc['lose_settle_interest']       = 0;
                        $vc['lose_interest_sum']          = 0;
                        $vc['lose_origin']                = 0;
                        $vc['memo']                       = "매입 후 완제처리";
                
                        $vc['in_name']      = "";
                        $vc['bank_cd']      = "";
                        $vc['bank_ssn']     = "";
                        $vc['vir_acct_ssn'] = "";

                        // 거래원장 등록
                        $rslt = DB::dataProcess("INS", "LOAN_INFO_TRADE", $vc, '', $loan_info_trade_no);

                        // 계약정보 업데이트
                        $vl = [];
                        $vl['last_trade_date']      = $vc['trade_date'];
                        $vl['loan_info_trade_no']   = $loan_info_trade_no;
                        $vl['save_time']            = $vc['save_time'];
                        $vl['save_id']              = $vc['save_id'];

                        $vl['status'] = 'E';

                        $vl['cost_money']           = 0;
                        $vl['delay_interest']       = 0;
                        $vl['delay_money']          = 0;
                        $vl['interest']             = 0;
                        $vl['interest_sum']         = 0;
                        $vl['delay_term']           = 0;
                        $vl['delay_interest_term']  = 0;
                        $vl['delay_interest_sdate'] = "";
                        $vl['delay_interest_edate'] = "";
                        $vl['delay_money_term']     = 0;
                        $vl['delay_money_sdate']    = "";
                        $vl['delay_money_edate']    = "";
                        $vl['interest_term']        = 0;
                        $vl['interest_sdate']       = "";
                        $vl['interest_edate']       = "";
                        $vl['charge_money']         = 0;
                        $vl['fullpay_money']        = 0;
                        $vl['fullpay_date']         = $vc['trade_date'];
                        $vl['fullpay_cd']           = "";
                        
                        $vl['fullpay_origin']       = 0;
                    }

                    $rslt = DB::dataProcess('UPD', 'LOAN_INFO', $vl, ["no"=>$vc['loan_info_no']]);
                    
                    $rslt = Loan::updateLoanInfoInterest($vc['loan_info_no'], date("Ymd"));
                }
            }
        }
        else
        {
            echo '엑셀파일 미존재';
        }

        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'trade');

            $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);
            $file_path = $file_arr[1]."/".$file_arr[2];
            $file_name = $file_arr[3];

            echo "실패 로그파일 생성: 실패건이 있을시 결과파일 만든다.\n";
        }
    }

    public function validECheck($value) 
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

        // 계약존재 확인
        $chk_loan =  DB::TABLE("loan_info")->SELECT("*")
                                            ->WHERE('no', $value['loan_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(empty($chk_loan))
        {
            $waring = "계약이 존재하지않습니다.";
            $return["waring"] = $waring;
            return $return;
        }
    }
    
    // 잔여원가 출력엑셀 23.8월경에도 요청함
    public function balance_excel()
	{ 
       echo "start\n";

       
       $trade_date = '20090120';
       $cnt = 0;
       $loanArray = [];
       $excelArray = [];

       //limit 해제해야함;
       $loan_info_no = DB::table('loan_info')
                        ->SELECT(DB::raw('no'))
                        ->ADDSELECT('buy_date')
                        ->orderby('loan_info')
                        ->LIMIT(50)
                        ->get();
        

        //계약번호 전체 배열에 담기
        foreach($loan_info_no as $key => $value)
        {
            $loanArray[$value->no] = $value->buy_date;
        }
        
        //각각의 계약마다 매입일이 존재하는지 확인
        foreach($loanArray as $loan_info_no => $buy_date)
        {
            //매입일이 존재할경우, (매입일 이후, 거래일 전) -> 개발 거래원장에 데이터가 존재하는지 확인
            if(isset($buy_date))
            {
                $results = DB::table('LOAN_INFO_TRADE')
                ->select( DB::raw("$trade_date as trade_date "),'loan_info_no', 'balance')
                ->whereIn('no', function ($query) use ($trade_date){
                    $query->select(DB::raw('MAX(no)'))
                        ->from('LOAN_INFO_TRADE')
                        ->where('TRADE_DATE', '<=', $trade_date)
                        ->where('SAVE_STATUS', '=', 'Y')
                        ->groupBy('LOAN_INFO_NO')
                        ->orderBy('LOAN_INFO_NO');
                })
                ->where('LOAN_INFO_TRADE.trade_date', '>', function ($query) use($loan_info_no) {
                    $query->select('buy_date')
                        ->from('loan_info')
                        ->where('no', '=', $loan_info_no);
                })
                ->where('loan_info_no', '=', $loan_info_no)
                ->orderBy('LOAN_INFO_NO')
                ->get();
            }
            else                    //매입일이 존재하지 않음
            {
                $results = DB::table('LOAN_INFO_TRADE')
                ->select(DB::raw("$trade_date as trade_date"),'loan_info_no', 'balance')
                ->whereIn('no', function ($query) use($trade_date) {
                    $query->select(DB::raw('MAX(no)'))
                        ->from('LOAN_INFO_TRADE')
                        ->where('TRADE_DATE', '<=', $trade_date)
                        ->where('SAVE_STATUS', '=', 'Y')
                        ->groupBy('LOAN_INFO_NO')
                        ->orderBy('LOAN_INFO_NO');
                })
                ->where('loan_info_no', '=', $loan_info_no)
                ->orderBy('LOAN_INFO_NO')
                ->get();
            }

            //쿼리 결과값이 존재하면, $excel출력 배열에 담음
            if(isset($results))
            {
                foreach($results as $key => $value)
                {   
                    $excelArray[] = $value; 
                    $cnt++;
                }
            }
        }

        // 엑셀 헤더 -----------------------------------------------------------------------------
        $div = $trade_date;
        $result['filepath'] = "/storage/app/excel/fail_".$div;
		$result['filename'] = "/balance_".$div."_".date("YmdHis").".xls";	// 서버 저장파일명

        $header  = array('날짜', '계약번호', '잔액');
		$body = $excelArray;
        
		// 폴더가 없으면 생성
		if(!file_exists(Storage::path($result['filepath'])))
		{
			umask(0);
			mkdir(Storage::path($result['filepath']), "755", true);
		}

        $col_idx = Coordinate::stringFromColumnIndex(count($header));
        // 헤더전체 BORDER,스타일
		$style['custom'] = [
			'A1:'.$col_idx.'1'=> [
				'font' => ['bold'=>true], 
				'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'ebebec']],
				'borders' => [
					'allBorders'=>['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
				],
			],
		];

        Excel::store(new ExcelCustomExport($header, $body,'sheet1',$style), $result['filepath'].$result['filename']);

        echo "\nend";
    }

    public static function parseDate($dataType, $val)
    {
        if($dataType=="date")
        {
            return Carbon::instance(\PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($val))->format('Y-m-d');
        }
        if($dataType=="integer")
        {
            return number_format($val);
        }
        else
        {
            return $val;
        }
    }

    /**
     * 난수 생성함수
     * Argv1 : 난수길이
     * Argv2 : 난수Char 복수type으로 설정 가능
     *         N -> 숫자
     *         a -> 영문 소문자
     *         A -> 영문 대문자
     *         S -> 특수문자
     */
    private static function randFunc($randLength, $type)
    {
        $chars = $randStr = "";

        // 난수 생성대상 문자열 생성
        for($i=0;$i<strlen($type);$i++)
        {
            $subType = substr($type, $i, 1);

            // 숫자 배열 추가
            if($subType=="N")
            {
                for($j=48; $j<=57; $j++)
                {
                    $chars.=chr($j);
                }
            }
            // 영문 소문자
            else if($subType=="a")
            {
                for($j=97; $j<=122; $j++)
                {
                    $chars.=chr($j);
                }
            }
            // 영문 대문자
            else if($subType=="A")
            {
                for($j=65; $j<=90; $j++)
                {
                    $chars.=chr($j);
                }
            }
            // 특수문자
            else if($subType=="S")
            {
                for($j=33; $j<=126; $j++)
                {
                    // 숫자 배열 제외
                    if($j>=48 && $j>=57) continue;
                    // 영문 소문자 배열 제외
                    if($j>=97 && $j>=122) continue;
                    // 영문 대문자 배열 제외
                    if($j>=65 && $j>=90) continue;

                    $chars.=chr($j);
                }
            }
        }

        for($i=0; $i<$randLength; $i++) $randStr .= substr($chars, rand(0, strlen($chars)-1), 1);
        return $randStr;
    }

    // 개인회생명세에서 화해건 지우기
    public function irl_save_status_N()
	{
        $file = "";
        $ERROR = Array();

        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $path = "test/irl.xlsx";

        $i = 0;
        // 파일 저장 위치
        if(Storage::disk('public')->exists($path))
        {
            echo "파일 O\n";

            $file = Storage::path('/public/'.$path);
            $colHeader = array('화해번호', '회원번호', '계약번호', '고객명', '채권상태');
            $colNm = array(
                'no'                        => '0',     // 화해번호
                'cust_info_no'	            => '1',	    // 회원번호(필수)
                'loan_info_no'              => '2',     // 계약번호(필수)
                'name'                      => '3',     // 고객명
                'status'                    => '4',     // 채권상태   
            );

            $excelData = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader, 0); 

            // 엑셀 유효성 검사
            if (!isset($excelData)) 
            {
                // 엑셀파일 헤더 불일치
                echo "헤더 불일치\n";
            }
            else 
            {
                // 상태 '진행중' 변경
                echo "[상태: 진행중]\n";

                foreach ($excelData as $_DATA) 
                {
                    unset($_INS);

                    // 데이터 정리
                    foreach($_DATA as $key => $val) 
                    {
                        $val = trim($val);
                        $_INS[$key] = $val;
                    }
                        
                    $arrayCheck = $this->validCheck_irlstatus($_INS);

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

                    $rslt = DB::dataProcess('UPD', 'loan_settle', ["save_status"=>'N'], ["no"=>$_INS['no']]);
                }
            }
        }
        else
        {
            echo "파일 X\n";
        }

        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'irl');

            $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);

            echo "\n";
            echo print_r($file_arr,1)."\n";
        }
	}

    // 화해 업데이트_유효성 검사
    public function validCheck_irlstatus($value) 
    {
        // 필수키
        $requirArray = Array('no'           => '화해번호',
                            'cust_info_no'  => '회원번호', 
                            'loan_info_no'  => '계약번호',
                            'name'          => '고객명', 
                            'status'        => '채권상태',
        );

        $waring = "";
        $return = Array();

        // 필수값 확인
        foreach($requirArray as $chk_key => $chk_val)
        {
            if($value[$chk_key] == '' || empty($value[$chk_key]))
            {
                $waring = "필수값 체크 : ".$chk_val." 확인필요"; 
                $return["waring"] = $waring;
                return $return;
            }
        } 

        if(strpos($value['cust_info_no'], 'zb') !== false)
        {
            $value['cust_info_no'] = str_replace('zb', '', $value['cust_info_no']);
        }

        // 회원번호 유효성
        $chk_cust =  DB::TABLE("cust_info")->SELECT("*")
                                            ->WHERE('no', $value['cust_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();

        if(empty($chk_cust))
        {
            $waring = "회원번호가 유효하지 않습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 계약번호 유효성
        $chk_loan =  DB::TABLE("loan_info")->SELECT("*")
                                            ->WHERE('no', $value['loan_info_no'])
                                            ->WHERE('cust_info_no', $value['cust_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();

        if(empty($chk_loan))
        {
            $waring = "계약번호가 유효하지 않습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 화해번호 유효성
        $chk_settle =  DB::TABLE("loan_settle")->SELECT("*")
                                            ->WHERE('no', $value['no'])
                                            ->WHERE('cust_info_no',$value['cust_info_no'])
                                            ->WHERE('loan_info_no',$value['loan_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();

        if(empty($chk_settle))
        {
            $waring = "화해번호가 유효하지 않습니다.";
            $return["waring"] = $waring;
            return $return;
        }
    }

    // 구전산 마감데이터 생성
    // php artisan Quick:actionhistory --flag=insertPrevCloseData
    public function insertPrevCloseData()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        // 로그 시간 찍기용
        $start_time = date("Y-m-d H:i:s");

        // 기준일 구간 설정
        $start_date = Carbon::createFromDate(2011, 01, 01);
        $origin_start_date = $start_date->format('Ymd');
        $end_date = Carbon::createFromDate(2020, 12, 31);
        
        echo "[start: ".$start_time."] info_date ".$origin_start_date." ~ ".$end_date->format('Ymd')." 마감데이터 생성 시작\n";
        log::channel('pl_work')->info("[start: ".$start_time."] info_date ".$origin_start_date." ~ ".$end_date->format('Ymd')." 마감데이터 생성 시작");

        // 시작 날짜부터 종료 날짜까지 반복
        while ($start_date->lte($end_date)) 
        {
            // 기준일
            $info_date = $start_date->format('Ymt');
            $info_month = substr($info_date,0,6);
            echo "\tinfo_date: ".$info_date."\n";
            log::channel('pl_work')->info("\tinfo_date: ".$info_date);

            // 마감데이터
            // -------------------------------------------------------------------------------------
            try
            {
                unset($count);
                $count = DB::TABLE('pg_tables')->WHERE('tablename', 'close_data_'.$info_month)->count();
                if( $count==0 )
                {
                    DB::STATEMENT("CREATE TABLE close_data_".$info_month." PARTITION OF close_data FOR VALUES FROM ('".$info_month."01') TO ('".date('Ymd',strtotime("+1 month", strtotime($info_month."01")))."')");
                    echo "\tCREATE TABLE: close_data_".$info_month."\n";
                    log::channel('pl_work')->info("\tCREATE TABLE: close_data_".$info_month);
                }  
            }
            catch(QueryException $e)
            {
                
            }

            // 기준일자 데이터 존재할경우 삭제
            unset($old_arr, $old_master);
            $old_arr    = array();
            $old_master = DB::TABLE("close_data")->SELECT("loan_info_no")->WHERE("info_date", $info_date)->COUNT();
            if($old_master > 0)
            {
                DB::TABLE('close_data')->WHERE("info_date", $info_date)->DELETE();
                echo "\tDELETE TABLE: close_data_".$info_date."\n";
                log::channel('pl_work')->info("\tDELETE TABLE: close_data_".$info_date);
            }

            // 미수이자명세 데이터 select
            $sql1 = 
            "
            INSERT INTO close_data (cust_info_no, loan_info_no, info_date, name, ssn, save_time,
                ph31, ph32, ph33, ph34, com_name, marry_status_cd, job_cd, house_type_cd, post_send_cd,
                app_date, app_money, loan_date, loan_money, loan_term, contract_date, contract_end_date,
                return_method_cd, monthly_return_money, cost_money, misu_money, lack_delay_interest, lack_delay_money, lack_interest,
                delay_interest, delay_money, interest, settle_interest, balance, over_money, take_date, return_date, return_date_biz,
                kihan_date, kihan_date_biz, loan_info_trade_no, delay_interest_sdate, delay_interest_edate,
                delay_money_term, delay_money_sdate, delay_money_edate, interest_term, interest_sdate, interest_edate,
                return_date_interest, calc_date, buy_corp, buy_date, first_delay_date,
                limit_money, sell_corp, return_fee_rate, return_fee_cd,
                legal_rate, reg_time, cust_type, com_div, pro_cd, last_trade_date, loan_app_no, attribute_delay_cd,
                vir_acct_mo_bank_cd, vir_acct_mo_ssn, vir_acct_ssn, doc_status_cd, pay_method, loan_bank_cd, loan_bank_ssn, loan_bank_name,
                misu_rev_money, except_cb, loan_reg_no, path_cd, agent_cd, marketing_cd, loan_type,
                branch_cd, last_loan_date, manager_code, manager_id, manager_date, borrow_yn, contract_method_cd,
                loan_cat_1_cd, loan_cat_2_cd, loan_cat_3_cd, loan_cat_4_cd, loan_cat_5_cd, first_app_date, buy_balance, nice_yn, lack_basis_money,
                dambo_set_fee_target,
                sanggak_interest,
                return_sms_7, return_sms_6, return_sms_5, return_sms_4, return_sms_3, return_sms_2, return_sms_1,
                seller_no, buy_closing_date, cost_origin,
                first_loan_money, total_loan_money, base_cost,
                person_manage, invalid_flag, first_loan_date, cb_yn, last_in_money, convert_flag, safekey, kfb_yn,
                handle_code, now_cost, buy_loan_money, buy_cost_money, buy_misu_money,
                posting_start_date, posting_end_date,
                ltv, max_ltv, dambo_addr11, dambo_addr12
                , settle_date, settle_div_cd, settle_detail_cd, settle_money, settle_rsn_cd 
                , last_in_date
                , loan_rate
                , loan_delay_rate
                , contract_day
                , interest_sum
                , fullpay_money 
                , fullpay_origin
                , sg_fullpay_cd
                , sanggak_date
                , sg_reason_cd
                , bad_reg_date
                , bad_post_date
                , cancel_date
                , cancel_id
                , final_end_date
                , deaths_date
                , first_prescription_date
                , first_prescription_date_memo
                , lost_date
                , lost_date_memo
                , delay_term
                , delay_interest_term
                , delay_term_max
                , delay_term_sum

                , fullpay_date
                , sell_date
                , status
            ) 
            SELECT 
                li.cust_info_no, li.no, '".$info_date."' as info_date, name, ssn, t.save_time,
                ce.ph31, ce.ph32, ce.ph33, ce.ph34, ce.com_name, ce.marry_status_cd, ce.job_cd, ce.house_type_cd, ce.post_send_cd,
                app_date, app_money, loan_date, loan_money, loan_term, contract_date, contract_end_date,
                return_method_cd, monthly_return_money, t.cost_money, t.misu_money, t.lack_delay_interest, t.lack_delay_money, t.lack_interest,
                t.delay_interest, t.delay_money, t.interest, t.settle_interest, t.balance, t.over_money, t.take_date, t.return_date, t.return_date_biz,
                t.kihan_date, t.kihan_date_biz, t.no, t.delay_interest_sdate, t.delay_interest_edate,
                t.delay_money_term, t.delay_money_sdate, t.delay_money_edate, t.interest_term, t.interest_sdate, t.interest_edate,
                return_date_interest, '".$info_date."' as calc_date, buy_corp, buy_date, first_delay_date,
                limit_money, sell_corp, return_fee_rate, return_fee_cd,
                legal_rate, t.reg_time, cust_type, com_div, li.pro_cd, t.trade_date as last_trade_date, li.loan_app_no, ci.attribute_delay_cd,
                li.vir_acct_mo_bank_cd, li.vir_acct_mo_ssn, t.vir_acct_ssn, li.doc_status_cd, li.pay_method, ci.loan_bank_cd, ci.loan_bank_ssn, ci.loan_bank_name,
                li.misu_rev_money, li.except_cb, li.loan_reg_no, li.path_cd, li.agent_cd, li.marketing_cd, li.loan_type,
                li.branch_cd, li.last_loan_date, t.manager_code, t.manager_id, li.manager_date, li.borrow_yn, li.contract_method_cd,
                li.loan_cat_1_cd, li.loan_cat_2_cd, li.loan_cat_3_cd, li.loan_cat_4_cd, li.loan_cat_5_cd, ci.first_app_date, li.buy_balance, li.nice_yn, li.lack_basis_money,
                li.dambo_set_fee_target,
                t.sanggak_interest,
                ci.return_sms_7, ci.return_sms_6, ci.return_sms_5, ci.return_sms_4, ci.return_sms_3, ci.return_sms_2, ci.return_sms_1,
                li.seller_no, li.buy_closing_date, t.cost_origin,
                li.first_loan_money, li.total_loan_money, li.base_cost,
                ci.person_manage, li.invalid_flag, li.first_loan_date, li.cb_yn, t.trade_money, li.convert_flag, ci.safekey, li.kfb_yn,
                li.handle_code, t.now_cost, li.buy_loan_money, li.buy_cost_money, li.buy_misu_money,
                (select to_char(posting_start_date, 'YYYYMMDD') from loan_app_extra where loan_app_no = li.loan_app_no) as posting_start_date
                ,(select substring(save_time, 0, 9) from loan_app_sta_log where loan_app_no = li.loan_app_no and status = 'H' order by loan_app_sta_log.seq desc limit 1) as posting_end_date
                
                , (select case when '".$info_date."' >= li.settle_date then li.settle_date
                    end) as settle_date
                , (select case when '".$info_date."' >= li.settle_date then li.settle_div_cd
                    end) as settle_div_cd
                , (select case when '".$info_date."' >= li.settle_date then li.settle_detail_cd
                    end) as settle_detail_cd
                , (select case when '".$info_date."' >= li.settle_date then li.settle_money
                    end) as settle_money
                , (select case when '".$info_date."' >= li.settle_date then li.settle_rsn_cd
                    end) as settle_rsn_cd

                , (select trade_date from loan_info_trade where loan_info_no=li.no and save_status='Y' and '".$info_date."' >= trade_date and trade_div ='I' order by trade_date desc limit 1) as last_in_date
                , (select loan_rate from loan_info_rate where loan_info_no=li.no and save_status='Y' and '".$info_date."' >= rate_date order by save_time desc limit 1) as loan_rate
                , (select loan_delay_rate from loan_info_rate where loan_info_no=li.no and save_status='Y' and '".$info_date."' >= rate_date order by save_time desc limit 1) as loan_delay_rate

                , (select contract_day from loan_info_cday where loan_info_no=li.no and save_status='Y' and '".$info_date."' >= cday_date order by save_time desc limit 1) as contract_day

                , (coalesce(t.interest, 0) + coalesce(t.delay_interest, 0) + coalesce(t.lack_interest, 0) + coalesce(t.misu_money, 0) + coalesce(t.settle_interest, 0)) as interest_sum

                , t.balance + coalesce(t.interest, 0) + coalesce(t.delay_interest, 0) + coalesce(t.lack_interest, 0) + coalesce(t.misu_money, 0) + coalesce(t.settle_interest, 0) as fullpay_money
                , (select sum(return_origin) from loan_info_trade where loan_info_no=li.no and save_status='Y' and '".$info_date."' >= trade_date and trade_div ='I' and trade_type !='11' and trade_path_cd !='6') as fullpay_origin

                , (select case when '".$info_date."' >= li.fullpay_date then li.sg_fullpay_cd
                    end) as sg_fullpay_cd
                , (select case when '".$info_date."' >= li.sanggak_date then li.sanggak_date
                    end) as sanggak_date
                , (select case when '".$info_date."' >= li.sanggak_date 
                    then (select sg_reason_cd from loan_sanggak where loan_info_no = li.no and sanggak_status = 'Y' and save_status = 'Y' order by seq desc limit 1)
                    end) as sg_reason_cd
                , (select case when '".$info_date."' >= li.bad_reg_date then li.bad_reg_date
                    end) as bad_reg_date
                , (select case when '".$info_date."' >= li.bad_post_date then li.bad_post_date
                    end) as bad_post_date
                , (select case when '".$info_date."' >= li.cancel_date then li.cancel_date
                    end) as cancel_date
                , (select case when '".$info_date."' >= li.cancel_date then li.cancel_id
                    end) as cancel_id
                , (select case when '".$info_date."' >= li.final_end_date then li.final_end_date
                    end) as final_end_date
                , (select case when '".$info_date."' >= li.deaths_date then li.deaths_date
                    end) as deaths_date

                , (select case when '".$info_date."' >= li.first_prescription_date then li.first_prescription_date
                    end) as first_prescription_date
                , (select case when '".$info_date."' >= li.first_prescription_date then li.first_prescription_date_memo
                    end) as first_prescription_date_memo
                
                , (select case when (select count(*) from loan_info_lost_date where loan_info_no=li.no and save_status='Y' and '".$info_date."' >= trade_date) > 0
                    then (select lost_date from loan_info_lost_date where loan_info_no=li.no and save_status='Y' and '".$info_date."' >= trade_date order by save_time desc limit 1)
                    else li.lost_date end) as lost_date
                , (select case when (select count(*) from loan_info_lost_date where loan_info_no=li.no and save_status='Y' and '".$info_date."' >= trade_date) > 0
                    then (select lost_date_memo from loan_info_lost_date where loan_info_no=li.no and save_status='Y' and '".$info_date."' >= trade_date order by save_time desc limit 1)
                    else li.lost_date_memo end) as lost_date_memo

                , (select case when '".$info_date."' > t.return_date then '".$info_date."'::date - t.return_date::date
                    else 0 end) as delay_term
                , (case when '".$info_date."'>t.return_date and t.return_date>=t.trade_date then '".$info_date."'::date-t.return_date::date 
                    when '".$info_date."'>t.return_date and t.return_date<t.trade_date then '".$info_date."'::date-t.trade_date::date 
                    else 0 end ) as delay_interest_term
                
                , (SELECT COALESCE(MAX( DELAY_INTEREST_TERM_SUM ),0) 
                    FROM ( 
                        SELECT P_RETURN_DATE, TRADE_DATE, SEQ, DELAY_INTEREST_TERM, SUM(DELAY_INTEREST_TERM) OVER ( PARTITION BY P_RETURN_DATE ORDER BY A.ROW_NUM ) DELAY_INTEREST_TERM_SUM, RETURN_DATE_BIZ 
                        FROM      ( SELECT TRADE_DIV, TRADE_DATE, SEQ, RETURN_DATE_BIZ, ( CASE WHEN COALESCE(DELAY_MONEY_TERM,0)>0 THEN DELAY_MONEY_TERM ELSE DELAY_INTEREST_TERM END ) AS DELAY_INTEREST_TERM, DELAY_INTEREST, DELAY_MONEY, ROW_NUMBER() OVER ( ORDER BY SEQ ASC ) AS ROW_NUM FROM loan_info_trade WHERE LOAN_INFO_NO=li.no AND SAVE_STATUS='Y' ORDER BY SEQ DESC ) A 
                        LEFT JOIN ( SELECT RETURN_DATE_BIZ AS P_RETURN_DATE, ROW_NUMBER() OVER ( ORDER BY SEQ ASC ) AS ROW_NUM FROM loan_info_trade WHERE LOAN_INFO_NO=li.no AND SAVE_STATUS='Y' ORDER BY SEQ DESC ) B 
                            ON A.ROW_NUM=B.ROW_NUM+1 
                    WHERE ( DELAY_INTEREST>0 OR DELAY_MONEY>0 ) 
                        AND ( RETURN_DATE_BIZ != (select RETURN_DATE_BIZ from loan_info where save_status ='Y' and ( COALESCE(BALANCE,'0') + COALESCE(SETTLE_INTEREST,'0') + COALESCE(SANGGAK_INTEREST,'0') + COALESCE(INTEREST_SUM,'0') + COALESCE(COST_MONEY,'0') + COALESCE(cost_origin,'0') + COALESCE(OVER_MONEY,'0') ) > '0' and status in ('A','B','C','D','S','E') and no = li.no
                        ) OR RETURN_DATE_BIZ>TRADE_DATE ) AND RETURN_DATE_BIZ!='' 
                    ) as foo   
                ) as delay_term_max

                , (SELECT COALESCE(SUM( DELAY_INTEREST_TERM ),0) 
                    FROM ( 
                        SELECT P_RETURN_DATE, TRADE_DATE, SEQ, DELAY_INTEREST_TERM, SUM(DELAY_INTEREST_TERM) OVER ( PARTITION BY P_RETURN_DATE ORDER BY A.ROW_NUM ) DELAY_INTEREST_TERM_SUM, RETURN_DATE_BIZ 
                        FROM      ( SELECT TRADE_DIV, TRADE_DATE, SEQ, RETURN_DATE_BIZ, ( CASE WHEN COALESCE(DELAY_MONEY_TERM,0)>0 THEN DELAY_MONEY_TERM ELSE DELAY_INTEREST_TERM END ) AS DELAY_INTEREST_TERM, DELAY_INTEREST, DELAY_MONEY, ROW_NUMBER() OVER ( ORDER BY SEQ ASC ) AS ROW_NUM FROM loan_info_trade WHERE LOAN_INFO_NO=li.no AND SAVE_STATUS='Y' ORDER BY SEQ DESC ) A 
                        LEFT JOIN ( SELECT RETURN_DATE_BIZ AS P_RETURN_DATE, ROW_NUMBER() OVER ( ORDER BY SEQ ASC ) AS ROW_NUM FROM loan_info_trade WHERE LOAN_INFO_NO=li.no AND SAVE_STATUS='Y' ORDER BY SEQ DESC ) B 
                            ON A.ROW_NUM=B.ROW_NUM+1 
                    WHERE ( DELAY_INTEREST>0 OR DELAY_MONEY>0 ) 
                        AND ( RETURN_DATE_BIZ != (select RETURN_DATE_BIZ from loan_info where save_status ='Y' and ( COALESCE(BALANCE,'0') + COALESCE(SETTLE_INTEREST,'0') + COALESCE(SANGGAK_INTEREST,'0') + COALESCE(INTEREST_SUM,'0') + COALESCE(COST_MONEY,'0') + COALESCE(cost_origin,'0') + COALESCE(OVER_MONEY,'0') ) > '0' and status in ('A','B','C','D','S','E') and no = li.no
                        ) OR RETURN_DATE_BIZ>TRADE_DATE ) AND RETURN_DATE_BIZ!='' 
                    ) as foo   
                ) as delay_term_sum

                ,(select fullpay_date from loan_info where no=li.no and save_status='Y' and fullpay_date <= '".$info_date."' order by no desc limit 1) as fullpay_date
                ,(select sell_date from loan_info where no=li.no and save_status='Y' and sell_date <= '".$info_date."' order by no desc limit 1) as sell_date
                ,(select case 
                    when (fullpay_date <= '".$info_date."' and fullpay_date != '') then 'E' 
                    when (sell_date <= '".$info_date."' and sell_date != '') then 'M'
                    when (sanggak_date <= '".$info_date."' and sanggak_date != '') then 'S' 
                    when (yangdo_date <= '".$info_date."' and yangdo_date != '') then 'Y' 
                    when (resale_date <= '".$info_date."' and resale_date != '') then 'H' 
                    when (li.settle_date > '".$info_date."' or li.settle_date is null) and t.return_date >= '".$info_date."' then 'A'
                    when (li.settle_date > '".$info_date."' or li.settle_date is null) and t.return_date < '".$info_date."' then 'B'
                    when li.settle_date <= '".$info_date."' and t.return_date >= '".$info_date."' then 'C'
                    when li.settle_date <= '".$info_date."' and t.return_date < '".$info_date."' then 'D'
                    else 'B' end as status) 

            from cust_info ci, cust_info_extra ce, loan_info li,
            (select *
                from ( select *
                from loan_info_trade where (loan_info_no, no) in ( select loan_info_no, max(no) from loan_info_trade where substr(save_time,1,8) <= '".$info_date."'
                and ( substr(del_time,1,8) >'".$info_date."' or save_status = 'Y') group by loan_info_no ) ) as foo ) t
            where ci.no = li.cust_info_no and ci.no = ce.cust_info_no and li.no = t.loan_info_no
                and ci.save_status='Y' and li.save_status='Y'
                and (li.buy_date is null or li.buy_date <= '".$info_date."' )
                and li.contract_date <= '".$info_date."'
            ";

            $rslt1 = DB::STATEMENT($sql1);

            // 다음 날짜로 이동
            $start_date->addMonth();
        }	

        // 로그 시간 찍기용
        $end_time = date("Y-m-d H:i:s");
        
        echo "[start: ".$start_time." ~ end: ".$end_time."] info_date: ".$origin_start_date." ~ ".$end_date->format('Ymd')." 마감데이터 생성 완료\n";
        log::channel('pl_work')->info("[start: ".$start_time." ~ end: ".$end_time."] info_date: ".$origin_start_date." ~ ".$end_date->format('Ymd')." 마감데이터 생성 완료");
    }

    // 21년 이전 마감데이터 생성 이후 status, manager_code, manager_id 업데이트
    // php artisan Quick:actionhistory --flag=chgStatus
    public function chgStatus()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        
        // 로그 시간 찍기용
        $start_time = date("Y-m-d H:i:s");

        $start_date = Carbon::createFromDate(2015, 01, 01);
        $origin_start_date = $start_date->format('Ymd');
        $end_date = Carbon::createFromDate(2020, 12, 31);
        
        echo "[start: ".$start_time."] info_date ".$origin_start_date." ~ ".$end_date->format('Ymd')." status, manager_code, manager_id 업데이트 시작\n";
        log::channel('pl_work')->info("[start: ".$start_time."] info_date ".$origin_start_date." ~ ".$end_date->format('Ymd')." status, manager_code, manager_id 업데이트 시작");

        // 시작 날짜부터 종료 날짜까지 반복
        while ($start_date->lte($end_date)) 
        {
            $info_date = $start_date->format('Ymt');
            $info_month = $start_date->format('Ym');
            echo "\tinfo_date: ".$info_date."\n";
            log::channel('pl_work')->info("\tinfo_date: ".$info_date);

            $rs1 = DB::connection('mig_erp')->table('contract_info_log_status')
                                        ->select('contract_info_no','status')
                                        ->whereRaw("no in (select max(no) from contract_info_log_status where save_status = 'Y' and trade_date <='".$info_date."' group by contract_info_no)")
                                        ->get();
            foreach($rs1 as $v)
            {
                unset($_UP);
                $_UP['settle_div_cd'] = "";
                $_UP['status'] = $v->status;

                if($v->status=="C" || $v->status=="D" || $v->status=="I" || $v->status=="J" || $v->status=="F" || $v->status=="G")
                {
                    if($v->status=="C" || $v->status=="D") $settle_div_cd = "1";                // 일반화해
                    else if($v->status=="I" ) 
                    {
                        $settle_div_cd = "2";           // 개인회생
                        $_UP['status'] = 'C';
                    }
                    else if($v->status=="J") 
                    {
                        $settle_div_cd = "2";           // 개인회생
                        $_UP['status'] = 'D';
                    }
                    else if($v->status=="F") 
                    {
                        $settle_div_cd = "3";           // 신용회복
                        $_UP['status'] = 'C';
                    }
                    else if($v->status=="G") 
                    {
                        $settle_div_cd = "3";           // 신용회복
                        $_UP['status'] = 'D';
                    }
                    else $settle_div_cd = "";

                    $_UP['settle_div_cd'] = $settle_div_cd;
                }

                DB::table("close_data_".$info_month)->where(['info_date'=>$info_date, 'loan_info_no'=>$v->contract_info_no])->update($_UP);
            }

            $rs2 = DB::connection('mig_erp')->table('contract_info_log_manager_code')
                                            ->select('contract_info_no','manager_code')
                                            ->whereRaw("no in (select max(no) from contract_info_log_manager_code where save_status = 'Y' and trade_date <='".$info_date."' group by contract_info_no)")
                                            ->get();
            foreach($rs2 as $v)
            {
                unset($_UP);
                $_UP['manager_code'] = $v->manager_code;

                DB::table("close_data_".$info_month)->where(['info_date'=>$info_date, 'loan_info_no'=>$v->contract_info_no])->update($_UP);
            }

            $rs3 = DB::connection('mig_erp')->table('contract_info_log_manager_id')
                                            ->select('contract_info_no','manager_id')
                                            ->whereRaw("no in (select max(no) from contract_info_log_manager_id where save_status = 'Y' and trade_date <='".$info_date."' group by contract_info_no)")
                                            ->get();
            foreach($rs3 as $v)
            {
                unset($_UP);
                $_UP['manager_id'] = $v->manager_id;

                DB::table("close_data_".$info_month)->where(['info_date'=>$info_date, 'loan_info_no'=>$v->contract_info_no])->update($_UP);
            }
            
            $start_date->addMonth();
        }
        // 로그 시간 찍기용
        $end_time = date("Y-m-d H:i:s");

        echo "[start: ".$start_time." ~ end: ".$end_time."] info_date: ".$origin_start_date." ~ ".$end_date->format('Ymd')." status, manager_code, manager_id 업데이트 끝\n";
        log::channel('pl_work')->info("[start: ".$start_time." ~ end: ".$end_time."] info_date: ".$origin_start_date." ~ ".$end_date->format('Ymd')." status, manager_code, manager_id 업데이트 끝");
    }

    // 21년 이전 마감데이터 생성 이후 이자(interest_sum) 업데이트
    // php artisan Quick:actionhistory --flag=chgInterestSum
    public function chgInterestSum()
    {
        ini_set('memory_limit', '-1');
        
        // 로그 시간 찍기용
        $start_time = date("Y-m-d H:i:s");

        $start_date = Carbon::createFromDate(2007, 12, 01);
        $origin_start_date = $start_date->format('Ymd');
        $end_date = Carbon::createFromDate(2020, 12, 31);

        echo "[start: ".$start_time."] info_date ".$origin_start_date." ~ ".$end_date->format('Ymd')." 이자(interest_sum) 업데이트 시작\n";
        log::channel('pl_work')->info("[start: ".$start_time."] info_date ".$origin_start_date." ~ ".$end_date->format('Ymd')." 이자(interest_sum) 업데이트 시작");
        
        // 시작 날짜부터 종료 날짜까지 반복
        while ($start_date->lte($end_date)) 
        {
            $dt = $start_date->format('Ymt');
            $info_month = $start_date->format('Ym');

            echo "\tinfo_date: ".$dt."\n";
            log::channel('pl_work')->info("\tinfo_date: ".$dt);

            // loan_info_trade의 settle_interest값 마감데이터 반영 2024.02.19
            // 화해이자 반복문을 위한 쿼리
            $SETTLE_QUERY = "select no, loan_info_no, coalesce(settle_interest, 0) as settle_interest 
                            from loan_info_trade where (loan_info_no, no) in ( select loan_info_no, max(no) from loan_info_trade where substr(save_time,1,8) <= '".$dt."' 
                            and ( substr(del_time,1,8) > '".$dt."' or save_status = 'Y') group by loan_info_no )"; 
            $SETTLE_INTEREST = DB::SELECT($SETTLE_QUERY);
            $SETTLE_INTEREST = Func::chungDec("loan_info_trade", $SETTLE_INTEREST);
            $SETTLE_INTEREST = json_decode(json_encode($SETTLE_INTEREST, JSON_UNESCAPED_UNICODE), TRUE);


            // 마감데이터에 화해이자 업데이트하기
            foreach( $SETTLE_INTEREST as $v ) 
            {
                $sql_insert_settle_interest = "update close_data set settle_interest = ".$v["settle_interest"]." where loan_info_no = ".$v["loan_info_no"]." and info_date = '".$dt."'";
                DB::UPDATE($sql_insert_settle_interest);
            }

            // 기본쿼리
            $LOAN = DB::TABLE("close_data_".$info_month)->SELECT("*")->WHERE('info_date',$dt)->GET();
            $LOAN = Func::chungDec(["close_data_".$info_month], $LOAN);	// CHUNG DATABASE DECRYPT
            $LOAN = json_decode(json_encode($LOAN, JSON_UNESCAPED_UNICODE),TRUE);
            
            foreach( $LOAN as $v ) 
            {
                unset($_DATA, $val);

                $val = $this->getInterest($v, $dt);
                $_DATA['INTEREST_SUM'] = !empty($val['interest_sum']) ? $val['interest_sum'] : 0;
                $_DATA['INTEREST_SUM'] += $v['lack_interest'];
                $_DATA['INTEREST_SUM'] += $v['misu_money'];
                $_DATA['INTEREST_SUM'] += $v['settle_interest'];
                $_DATA['return_date_interest'] = !empty($val['return_date_interest']) ? $val['return_date_interest'] : 0;

                DB::table("close_data_".$info_month)->where(['info_date'=>$dt, 'loan_info_no'=>$v['loan_info_no']])->update($_DATA);
            }
            $start_date->addMonth();
        }
        // 로그 시간 찍기용
        $end_time = date("Y-m-d H:i:s");
        
        echo "[start: ".$start_time." ~ end: ".$end_time."] info_date: ".$origin_start_date." ~ ".$end_date->format('Ymd')." 이자(interest_sum) 업데이트 끝\n";
        log::channel('pl_work')->info("[start: ".$start_time." ~ end: ".$end_time."] info_date: ".$origin_start_date." ~ ".$end_date->format('Ymd')." 이자(interest_sum) 업데이트 끝");
    }
    
    // 21년 이전 생성한 마감데이터 중 월말 데이터 제외 DELETE
    // php artisan Quick:actionhistory --flag=deleteCloseData
    public function deleteCloseData()
    {
        ini_set('memory_limit', '-1');
        
        // 로그 시간 찍기용
        $start_time = date("Y-m-d H:i:s");

        $start_date = Carbon::createFromDate(2011, 01, 01);
        $origin_start_date = $start_date->format('Ymd');
        $end_date = Carbon::createFromDate(2020, 12, 30);
        
        echo "[start: ".$start_time."] info_date ".$origin_start_date." ~ ".$end_date->format('Ymd')." 월말 제외 삭제 START\n";
        log::channel('pl_work')->info("[start: ".$start_time."] info_date ".$origin_start_date." ~ ".$end_date->format('Ymd')." 월말 제외 삭제 START");

        // 시작 날짜부터 종료 날짜까지 반복
        while ($start_date->lte($end_date)) 
        {
            $info_date = $start_date->format('Ymd');
            // $info_month = $start_date->format('Ym');
            $not_info_date = $start_date->format('Ymt');

            if($info_date != $not_info_date)
            {
                // DELETE
                echo "\tDELETE info_date: ".$info_date."\n";
                log::channel('pl_work')->info("\tDELETE info_date: ".$info_date);
                DB::TABLE("close_data")->WHERE('info_date',$info_date)->DELETE();
            }
            
            $start_date->addDay();
        }

        // 로그 시간 찍기용
        $end_time = date("Y-m-d H:i:s");
                    
        echo "[start: ".$start_time." ~ end: ".$end_time."] info_date: ".$origin_start_date." ~ ".$end_date->format('Ymd')." 월말 제외 삭제 END\n";
        log::channel('pl_work')->info("[start: ".$start_time." ~ end: ".$end_time."] info_date: ".$origin_start_date." ~ ".$end_date->format('Ymd')." 월말 제외 삭제 END");
}

    public $holiday  = Array();
    public $rateInfo  = Array();

    public function getInterest($param, $today)
	{
        // 휴일
        $this->holiday = Cache::remember('Loan_Holiday', 86400, function()
        {
            $rslt = DB::TABLE("DAY_CONF")->SELECT("*")->GET();
            foreach( $rslt as $v )
            {
                $day           = str_replace("-","",$v->day);
                $holiday[$day] = $day;
            }
            return $holiday;
        });

        // 파라미터에 의한 값 셋팅
        if( is_array($param) )
        {
            if(!empty($param['contract_end_date']))
            {
                $date1 = Carbon::parse($param['contract_date']);
                $date2 = Carbon::parse($param['contract_end_date'])->addDay();

                $param['loan_term'] = $date1->diffInMonths($date2);
            }
            else
            {
                $param['loan_term'] = 1;
            }

            // 필수값
            if( !$param['loan_date'] || !$param['loan_term'] || $param['loan_rate']=='' || !$param['contract_day'] || !$param['return_method_cd'] || !$param['balance'] )
            {
                return false;
            }
            // 입력없으면 자동 셋팅
            if( !isset($param['loan_delay_rate']) || !$param['loan_delay_rate'] )
            {
                $param['loan_delay_rate'] = $param['loan_rate'] + 3 ;
            }
            if( !isset($param['take_date']) || !$param['take_date'] )
            {
                $param['take_date'] = $param['loan_date'];
            }
            if( !isset($param['contract_end_date']) || !$param['contract_end_date'] )
            {
                $param['contract_end_date'] = $this->addMonth($param['loan_date'], $param['loan_term']);
            }

            $param['monthly_return_money'] = 0;
            $param['sanggak_interest']    = 0;
            $param['settle_interest']     = 0;
            $param['lack_interest']       = 0;
            $param['lack_delay_money']    = 0;
            $param['lack_delay_interest'] = 0;
            $param['misu_money']   = 0;
            $param['cost_origin']  = 0;
            $param['cost_money']   = 0;
            $param['over_money']   = 0;
            
            $loanInfo = $param;

            if( !isset($param['return_date']) || !$param['return_date'] )
            {
                $param['return_date'] = $this->getNextReturnDate($param['take_date'],   $param['contract_day']);
            }
            if( !isset($param['kihan_date']) || !$param['kihan_date'] )
            {
                $param['kihan_date'] = $this->getNextReturnDate($param['return_date'], $param['contract_day']);

                // 자유상환, 만기일시는 2회차 후로 지정
                if( $loanInfo['return_method_cd']=="F" || $loanInfo['return_method_cd']=="M" )
                {
                    $param['kihan_date']  = $this->getNextReturnDate($param['kihan_date'], $param['contract_day']);
                }
            }
            $param['return_date_biz'] = $this->getBizDay($param['return_date']);
            $param['kihan_date_biz']  = $this->getBizDay($param['kihan_date']);


            $loanInfo = $param;
            $this->rateInfo = Array( $param['loan_date'] => Array( "rate_date" => $param['loan_date'], "loan_rate" => $param['loan_rate'], "loan_delay_rate" => $param['loan_delay_rate'] ) );
            $cdayInfo = Array( $param['loan_date'] => Array( "cday_date" => $param['loan_date'], "contract_day" => $param['contract_day'] ) );
        }

        $interestInfo = Array();
		if( sizeof($loanInfo)==0 )
		{
			Log::debug("Loan Object의 계약정보가 등록되지 않았습니다.");
			return false;
		}

		$today = str_replace("-","",$today);
		$val['return_method_cd'] = $loanInfo['return_method_cd'];

		$val['today']      = $today;
		$val['balance']    = $loanInfo['balance'] * 1;
		$val['misu_money'] = $loanInfo['misu_money'] * 1;
		$val['cost_origin']= $loanInfo['cost_origin'] * 1;
		$val['cost_money'] = $loanInfo['cost_money'] * 1;
		$val['over_money'] = $loanInfo['over_money'] * 1;

		$val['sanggak_interest']    = $loanInfo['sanggak_interest'] * 1;
		$val['settle_interest']     = $loanInfo['settle_interest'] * 1;
		$val['lack_interest']       = $loanInfo['lack_interest'] * 1;
		$val['lack_delay_money']    = $loanInfo['lack_delay_money'] * 1;
		$val['lack_delay_interest'] = $loanInfo['lack_delay_interest'] * 1;
	
		$val['take_date']       = $loanInfo['take_date'];
		$val['return_date']     = $loanInfo['return_date'];
		$val['return_date_biz'] = $loanInfo['return_date_biz'];
		$val['kihan_date']      = $loanInfo['kihan_date'];
		$val['kihan_date_biz']  = $loanInfo['kihan_date_biz'];

		$val['contract_end_date']    = $loanInfo['contract_end_date'];
		$val['monthly_return_money'] = $loanInfo['monthly_return_money'];
		$val['legal_rate']           = ( isset($loanInfo['legal_rate']) && $loanInfo['legal_rate']>0 ) ? $loanInfo['legal_rate'] : Vars::$curMaxRate ;

        $settleFlag   = false;
        
		if( !$val['balance'] && !$settleFlag && !$loanInfo['interest_sum'] && !$loanInfo['sanggak_interest'] && !$loanInfo['cost_money'] && !$loanInfo['cost_origin'])
		{
			log::debug("대출잔액이 없습니다. 계약번호 : ".$loanInfo['loan_info_no']);
			return false;
		}
        
		// 상각케이스 START ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if( $loanInfo['status']=="S" )
		{
			if($loanInfo['fullpay_date']!='')
			{
				log::debug("상각완제 채권입니다. 계약번호 : ".$loanInfo['loan_info_no']);
				return false;
			}

			$val['misu_money']          = 0;
			$val['interest']            = 0;
			$val['delay_money']         = 0;
			$val['delay_interest']      = 0;
			$val['settle_interest']     = 0;
			$val['lack_interest']       = 0;
			$val['lack_delay_money']    = 0;
			$val['lack_delay_interest'] = 0;
			$val['sanggak_interest']    = ( $loanInfo['sanggak_interest']>0 ) ? $loanInfo['sanggak_interest'] * 1 : $loanInfo['interest_sum'] * 1 ;
			$val['interest_sum']        = $val['sanggak_interest'] * 1;

			$val['interest_term']        = 0;
			$val['interest_sdate']       = "";
			$val['interest_edate']       = "";
			$val['delay_money_term']     = 0;
			$val['delay_money_sdate']    = "";
			$val['delay_money_edate']    = "";
			$val['delay_interest_term']  = 0;
			$val['delay_interest_sdate'] = "";
			$val['delay_interest_edate'] = "";

			$val['return_fee']      = 0;
			$val['return_fee_rate'] = 0;
			$val['return_fee_max']  = 0;

			$val['charge_delay_interest'] = 0;
			$val['charge_delay_money']    = 0;
			$val['charge_interest']       = 0;
			$val['charge_origin']         = $val['balance'];				// 상환순서때문에 이렇다
			$val['no_charge_interest']    = 0;
			$val['no_charge_origin']      = 0;
			$val['fullpay_money']         = $val['balance'] + $val['interest_sum'];

			$val['plan_interest']     = 0;
			$val['plan_origin']       = $val['charge_origin'];
			$val['plan_money']        = $val['charge_origin'];
			$val['charge_money']      = $val['fullpay_money'];
			$val['no_charge_money']   = $val['no_charge_interest'];
			$val['plan_charge_money'] = $val['charge_origin'];

			$val['return_date_interest'] = 0;
			$val['gugan_interest_sum']   = 0;
			$val['gugan_interest_date']  = "";

			$val['return_plan'][$today] = $val;

			$interestInfo = $val;
			return $val;
		}
		// 상각케이스 END //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		// 화해케이스 START ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		if( $settleFlag )
		{
			$val['interest']       = 0;
			$val['delay_money']    = 0;
			$val['delay_interest'] = 0;
			$val['interest_sum']   = 0;
			$val['interest_sum']  += $val['settle_interest'];
			$val['interest_sum']  += $val['lack_interest'] + $val['lack_delay_money'] + $val['lack_delay_interest'];	// 원래 있으면 안되는데, 혹시몰라서....
			$val['interest_sum']  += $val['misu_money'];																// 원래 있으면 안되는데, 혹시몰라서....
			$val['interest_sum']  += $val['interest'] + $val['delay_money'] + $val['delay_interest'];					// 원래 있으면 안되는데, 혹시몰라서....

			$val['interest_term']        = 0;
			$val['interest_sdate']       = "";
			$val['interest_edate']       = "";
			$val['delay_money_term']     = 0;
			$val['delay_money_sdate']    = "";
			$val['delay_money_edate']    = "";
			$val['delay_interest_term']  = 0;
			$val['delay_interest_sdate'] = "";
			$val['delay_interest_edate'] = "";
			
			$val['return_fee']      = 0;
			$val['return_fee_rate'] = 0;
			$val['return_fee_max']  = 0;
			

			// 청구금액, 완제금액 계산 +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

			$vs = DB::TABLE("LOAN_SETTLE")->SELECT("NO")->WHERE("LOAN_INFO_NO", $loanInfo['loan_info_no'])->WHERE("SAVE_STATUS", "Y")->WHERE("STATUS", "Y")->ORDERBY("NO","DESC")->FIRST();

			$charge_origin   = 0;
			$charge_interest = 0;
			$charge_money    = 0;
			$no_charge_origin   = 0;
			$no_charge_interest = 0;
			$no_charge_money    = 0;
			// if(isset($vs))
			// {
			// 	// 완료 안된 아이들만 뽑아.
			// 	$rtmp = DB::TABLE("LOAN_SETTLE_PLAN")->SELECT("plan_date, plan_money, plan_origin, plan_interest, trade_money")->WHERE('LOAN_SETTLE_NO',$vs->no)->WHERE('STATUS','Y')->ORDERBY('SEQ')->GET();
			// 	$rtmp = Func::chungDec(["LOAN_SETTLE_PLAN"], $rtmp);	// CHUNG DATABASE DECRYPT

			// 	if(isset($rtmp))
			// 	{
			// 		foreach( $rtmp as $vtmp )
			// 		{
			// 			// 청구
			// 			if( $vtmp->plan_date<=Loan::addDay($today,'11') )
			// 			{
			// 				$charge_money  += ( $vtmp->plan_money - $vtmp->trade_money );
			// 				if( $vtmp->plan_origin > $vtmp->trade_money )
			// 				{
			// 					$charge_origin  += $vtmp->plan_origin - $vtmp->trade_money;
			// 					$charge_interest+= $vtmp->plan_interest;
			// 				}
			// 				else
			// 				{
			// 					$charge_origin  += 0;
			// 					$charge_interest+= $vtmp->plan_interest - ( $vtmp->trade_money - $vtmp->plan_origin ) ;
			// 				}
			// 			}
			// 			// 미청구
			// 			else
			// 			{
			// 				$no_charge_money  += ( $vtmp->plan_money - $vtmp->trade_money );
			// 				if( $vtmp->plan_origin > $vtmp->trade_money )
			// 				{
			// 					$no_charge_origin  += $vtmp->plan_origin - $vtmp->trade_money;
			// 					$no_charge_interest+= $vtmp->plan_interest;
			// 				}
			// 				else
			// 				{
			// 					$no_charge_origin  += 0;
			// 					$no_charge_interest+= $vtmp->plan_interest - ( $vtmp->trade_money - $vtmp->plan_origin ) ;
			// 				}
			// 			}
			// 		}
			// 	}
			// }

			// 스케줄에는 입금예정금액만 있기 때문에 감면예정이자분은 포함이 안되어 있다.
			$lose_settle_interest = ( $val['settle_interest'] > ( $charge_interest + $no_charge_interest ) ) ? $val['settle_interest'] - ( $charge_interest + $no_charge_interest ) : 0 ;
			$lose_settle_origin   = ( $val['balance'] > ( $charge_origin + $no_charge_origin ) ) ? $val['balance'] - ( $charge_origin + $no_charge_origin ) : 0 ;

			$val['charge_delay_interest'] = 0;
			$val['charge_delay_money']    = 0;
			$val['charge_interest']       = 0;
			$val['charge_origin']         = $val['balance'];				// 상환순서때문에 이렇다.... 원금우선, 화해이자 순.... 그래서 청구원금에 원금전체, 화해이자에 이자 전체를 넣는다.
			$val['settle_interest']       = $charge_interest + $no_charge_interest + $lose_settle_interest ;
			$val['no_charge_interest']    = 0;
			$val['no_charge_origin']      = 0;
			$val['fullpay_money']         = $charge_money + $no_charge_money;

			$val['plan_interest']     = 0;
			$val['plan_origin']       = $val['charge_origin'];
			$val['plan_money']        = $val['charge_origin'];
			$val['charge_money']      = $charge_money;
			$val['no_charge_money']   = $no_charge_money;
			$val['plan_charge_money'] = $charge_money;

			$val['return_date_interest'] = 0;
			$val['gugan_interest_sum']   = 0;
			$val['gugan_interest_date']  = "";

			$val['return_plan'][$today] = $val;

			$interestInfo = $val;

			return $val;
		}
		// 화해케이스 END //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

		// F:자유상환, M:만기일시, R:원리금균등, B:원금균등
		if( $val['return_method_cd']=="F" )
		{
			$val = $this->getInterestFree($val);		//, "return_date", "kihan_date" ++++++ 기한이익상실 기준으로 연체이자 계산 언제부터??? ++++++++++++++++++
			$val['plan_charge_money'] = $val['interest'] + $val['delay_money'] + $val['delay_interest'];
			$val['return_plan'][$today] = $val;
		}

		// 상환일이자 - 이자만계산
		$valr = $val;
		$valr['today'] = $val['return_date_biz'];
		$valr = $this->getInterestFree($valr);

		$val['return_date_interest'] = isset($val['settle_interest']) ? $val['settle_interest'] : 0 ;
		$val['return_date_interest']+= $val['lack_interest'] + $val['lack_delay_money'] + $val['lack_delay_interest'];
		$val['return_date_interest']+= $val['misu_money'];
		$val['return_date_interest']+= $valr['interest'] + $valr['delay_money'] + $valr['delay_interest'];

		// 이자합계
		$val['interest_sum'] = 0;
		$val['interest_sum']+= isset($val['sanggak_interest']) ? $val['sanggak_interest'] : 0 ;
		$val['interest_sum']+= isset($val['settle_interest'])  ? $val['settle_interest']  : 0 ;
		$val['interest_sum']+= $val['lack_interest'] + $val['lack_delay_money'] + $val['lack_delay_interest'];
		$val['interest_sum']+= $val['misu_money'];
		$val['interest_sum']+= ($val['interest'] ?? 0) + ($val['delay_money'] ?? 0) + ($val['delay_interest'] ?? 0);

		// 완납금액
		$val['fullpay_money'] = $val['balance'] + $val['interest_sum'] + $val['cost_money'] + $val['cost_origin'];

		$interestInfo = $val;
		return $val;
	}
    
	/**
	* 자유상환 이자 구하기
	*
	* @param  Array - 계약정보배열 [take_date] [today] [return_date] [balance] / 기생성 getCurrRate, getCurrCday
	* @param  String - 연체계산기준일 - 기본값은 상환일이나 자유상환의 경우는 특이하게 기한이익상실로 계산하는 경우가 있어서 파라미터로 받음
	* @return Array - 계약정보배열에 이자정보 추가 응답
	*/
	public function getInterestFree($val, $delay_rate_basis_colnm="return_date")
	{
		// 정상이자
		$val['interest']       = 0;
		$val['interest_term']  = 0;
		$val['interest_sdate'] = "";
		$val['interest_edate'] = "";
		// 연체이자(이자분)
		$val['delay_money']       = 0;
		$val['delay_money_term']  = 0;
		$val['delay_money_sdate'] = "";
		$val['delay_money_edate'] = "";
		// 연체이자(원금분)
		$val['delay_interest']       = 0;
		$val['delay_interest_term']  = 0;
		$val['delay_interest_sdate'] = "";
		$val['delay_interest_edate'] = "";

		// 이자계산 상세정보 (검증데이터)
		$val['interest_detail_chk']  = Array();

		$array_rate_set   = Array('A'=>Array(),'B'=>Array());
		$base_money       = $val['balance'];
		$delay_basis_date = ( isset($val['return_date_biz']) ) ? $val['return_date_biz'] : $val['return_date'] ;
		// 연체 중 입금했는데, 연체중일 때... take_date가 더 크다.
		if( $delay_basis_date < $val['take_date'] )
		{
			$delay_basis_date = $val['take_date'];
		}

		// 정상구간 이자,금리셋 - 초일산입
		$sdate = $val['take_date'];	// take_date 부터 받고
		$edate = $this->addDay(min($val['today'], $delay_basis_date),-1);	//min(오늘, 상환일) -1 까지 받는다.
		
		// 정상구간 이자,금리셋 - 후일산입
        // $sdate = $this->addDay($val['take_date']);
		// $edate = min($val['today'], $delay_basis_date);
		
        for( $d = $sdate; $d <= $edate; $d = $this->addDay($d) )
		{
			// 정상이자
			$loan_rate = $this->getCurrRate($d)['loan_rate'];
			$rate = (string) $this->yunRate( $loan_rate, $d );
			$days = $this->countUp($array_rate_set['A'][$rate]);

			$val['interest_term']++;
			if( !$val['interest_sdate'] )
			{
				$val['interest_sdate'] = $d;
			}
			$val['interest_edate'] = $d;
		}


		// 연체금리 기준일 - 상환일 그냥 따라가는 경우
		if( $delay_rate_basis_colnm=="return_date" )
		{
			$delay_rate_basis_date = $delay_basis_date;
		}
		// 연체금리 기준일 - 기한이익 상실일인 경우
		else
		{
			$delay_rate_basis_date = ( isset($val['kihan_date_biz']) ) ? $val['kihan_date_biz'] : $val['kihan_date'] ;			
		}


		// 연체구간 이자,금리셋 - 초일산입
		$sdate = $this->addDay($edate); // 위의 정상이자 edate + 1일
		$edate = $val['today'];

		// 연체구간 이자,금리셋 - 후일산입
		// $sdate = $this->addDay($delay_basis_date);	// 상환일 + 1일부터 받고, 
		// $edate = $val['today'];
		
        for( $d = $sdate; $d < $edate; $d = $this->addDay($d) ) // 조건문에 등호 제거(기준일 포함X)
		{
			// 연체이자
			if( $d>$delay_rate_basis_date )
			{
				$loan_delay_rate = $this->getCurrRate($d)['loan_delay_rate'];
				$rate = (string) $this->yunRate( $loan_delay_rate, $d );
			}
			// 정상이자
			else
			{
				$loan_delay_rate = $this->getCurrRate($d)['loan_rate'];		// 연체중이더라도 기한이익상실전까지는 정상금리를 적용
				$rate = (string) $this->yunRate( $loan_delay_rate, $d );
			}
			$days = $this->countUp($array_rate_set['B'][$rate]);

			$val['delay_interest_term']++;
			if( !$val['delay_interest_sdate'] )
			{
				$val['delay_interest_sdate'] = $d;
			}
			$val['delay_interest_edate'] = $d;

		}

		$val['interest']       = floor((string) $this->getInterestTerm($array_rate_set['A'], $base_money));
		$val['delay_interest'] = floor((string) $this->getInterestTerm($array_rate_set['B'], $base_money));

		return $val;
	}
	/**
	* 다음상환일 구하기 ( cdayInfo 셋팅전제 )
	*
	* @param  Date   - 기준일
	* @param  String - 약정일 (기본값은 cdayInfo에서 기준일로 참조 )
	* @return Date   - 다음상환일
	*/
	public function getNextReturnDate($today, $contractDay="")
	{
		if( !$contractDay )
		{
			$contractDay = sprintf("%02d",$this->getCurrCday($today));
		}
		
		// log::debug("CONTRACT_DAY : ".$contractDay);
		
		$today = $this->addDay($today, '11');
		
		while( true )
		{		
			// 약정일이면 끝
			if( substr($today,-2) == $contractDay )
			{
				break;
			}
			if( $contractDay >= "28" )
			{
				// 말일자
				$y = substr($today,0,4);
				$m = substr($today,4,2);
				$d = substr($today,6,2);
				if( $contractDay=="31" && $today==date("Ymt", $this->dateToUnixtime($today)) )
				{
					break;
				}
				// 말일자는 아니지만 해당월에 약정일이 없다. ex) 약정일이 30일 인데, 달은 2월 2월 30일은 존재하지 않으므로 2월 말일자로 지정되야 할 경우.
				if( $contractDay>$d && $today==date("Ymt", $this->dateToUnixtime($today)) )
				{
					break;
				}
			}
			$today = $this->addDay($today);
		}

		return $today;
	}
		// $ymd = str_replace("-","",$today);
		// if( !is_numeric($ymd) || strlen($ymd)!=8 )
		// {
		// 	//return false;
		// }
		// $y = substr($ymd,0,4);
		// $m = substr($ymd,4,2);
		// $d = substr($ymd,6,2);
		// return date("Ymd", (mktime(0, 0, 0, $m, $d, $y) + (86400 * $cnt)));

	public function getBizDay($today)
	{
		while( in_array($today, $this->holiday) )
		{
			$today = $this->addDay($today);
		}
		return $today;
	}

	/**
	* 금리셋트로 이자 계산 ( 같은 이자구간(정상,연체)내에서 금리변동이 발생할 수 있어서 사용 )
	*
	* @param  Array - 금리셋트 [금리] = 계산일수
	* @param  Int   - 이자계산의 기준금액 (잔액, 월상환금액 등등)
	* @return Int   - 이자응답
	*/
	private function getInterestTerm(&$array_rate_set, &$basemoney)
	{
		$interest = 0;
		if( isset($array_rate_set) && isset($basemoney) )
		{
			foreach( $array_rate_set as $rate => $cnt )
			{
				$rate = (float) $rate;
				$interest+=  ( $basemoney * $cnt * ($rate/100)) / 365 ;
			}
		}
		return $interest;
	}

	/**
	* 카운트 증가 ( 배열형태의 경우, index가 없어서 에러나는 경우, 0으로 배열초기화 후 카운트 진행 )
	*
	* @param  Date  - 기준일자 YYYYMMDD
	* @param  Int   - 증가일수 기본값 1
	* @return Date  - 증가된일자 YYYYMMDD
	*/
	public static function countUp(&$val)
	{
		if( !isset($val) )
		{
			$val = 0;
		}
		$val++;

		return $val;
	}

	/**
	* 기준일이 윤년인 경우, 금리를 366일 기준으로 변경처리
	*
	* @param  Float - 금리
	* @param  Date  - 기준일
	* @return Float - 윤년 반영된 금리
	*/	
	private function yunRate($app_rate, $d)
	{
		if( (substr($d,0,4)%4)==0 && substr($d,0,4)>="2012" )
		{
			$app_rate = (float) ( ( $app_rate * 365 ) / 366 );
		}
		return $app_rate;
	}

	/**
	* 기준일의 금리셋
	*
	* @param  Date  - 기준일
	* @return Array - 금리 = ['loan_rate' | 'loan_delay_rate'] = 정상금리 | 연체금리
	*/	
	public function getCurrRate($today)
	{
		$val = Array('loan_rate'=>0, 'loan_delay_rate'=>0);
		foreach( $this->rateInfo as $v )
		{
			if( $v['rate_date']>$today )
			{
				break;
			}
			$val = $v;
		}
		return $val;
	}

	/**
	* 특정일자의 Unixtime 구하기
	*
	* @param  Date - 일자
	* @return Int  - Unixtime
	*/
	public static function dateToUnixtime($ymd)
	{
		$ymd = str_replace("-","",$ymd);
		if( !is_numeric($ymd) || strlen($ymd)!=8 )
		{
			//return false;
		}
		$y = substr($ymd,0,4);
		$m = substr($ymd,4,2);
		$d = substr($ymd,6,2);
		return mktime(0, 0, 0, $m, $d, $y);
	}

	/**
	* 일수 증가
	*
	* @param  Date  - 기준일자 YYYYMMDD
	* @param  Int   - 증가일수 (기본값 1)
	* @return Date  - 증가된일자 YYYYMMDD
	*/
	public static function addDay($today, $cnt=1)
	{
		$ymd = str_replace("-","",$today);
		if( !is_numeric($ymd) || strlen($ymd)!=8 )
		{
			//return false;
		}
		$y = substr($ymd,0,4);
		$m = substr($ymd,4,2);
		$d = substr($ymd,6,2);
		return date("Ymd", (mktime(0, 0, 0, $m, $d, $y) + (86400 * $cnt)));
	}

	// 월수 증가
	public static function addMonth($today, $t)
	{
		$today = str_replace("-","",$today);

		$y = substr($today,0,4) * 1;
		$m = substr($today,4,2) * 1;
		$d = substr($today,6,2) * 1;

        $m+= $t;
        if( $m>0 )
        {
            $y+= floor($m/12);
        }
        else if( $m<0 )
        {
            $y+= ceil($m/12);
        }
        $m = $m % 12;
        if( $m<=0 )
        {
            $m = 12 + $m;
            $y--;
        }
		if( !checkdate( $m, $d, $y ) )
		{
			$d = date("t", mktime(0,0,0,$m,1,$y));
		}
		return sprintf("%04d",$y).sprintf("%02d",$m).sprintf("%02d",$d);
	}

    // 소멸시효 일괄 신청_유효성 검사
    public function validLostCheck($value) 
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

        // 회원번호에 zb를 붙여서 업로드할경우 제거
        if(strpos($value['cust_info_no'], 'zb') !== false)
        {
            $value['cust_info_no'] = str_replace('zb', '', $value['cust_info_no']);
        }

        // 회원번호 유효성
        $chk_cust =  DB::TABLE("cust_info")->SELECT("*")
                                            ->WHERE('no', $value['cust_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();

        if(empty($chk_cust))
        {
            $waring = "회원번호가 유효하지 않습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 계약번호 유효성
        $chk_loan =  DB::TABLE("loan_info")->SELECT("*")
                                            ->WHERE('no', $value['loan_info_no'])
                                            ->WHERE('cust_info_no', $value['cust_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();

        if(empty($chk_loan))
        {
            $waring = "계약번호가 유효하지 않습니다.";
            $return["waring"] = $waring;
            return $return;
        }
    }

    // 법착 소멸시효 일괄삭제처리
    public function law_lost_date()
    {
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        $path = "test/law_lost_date.xlsx";

        // 파일 저장 위치
        if(Storage::disk('public')->exists($path))
        {
            echo "파일 O\n";

            $ERROR = Array();
            $file = Storage::path('/public/'.$path);
            $colHeader = array('법착번호');
            $colNm = array(
                'loan_info_law_no' => '0',        // 법착번호
            );

            $excelData = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader, 0); 

            $cnt = 0;
            $i = 0;

            // 엑셀 유효성 검사
            if (!isset($excelData)) 
            {
                // 엑셀파일 헤더 불일치
                echo "헤더 불일치\n";
            }
            else 
            {
                // 상태 '진행중' 변경
                echo "[상태: 진행중]\n";

                foreach ($excelData as $_DATA) 
                {
                    unset($_UPD, $value, $data);

                    // 데이터 정리
                    foreach($_DATA as $key => $val) 
                    {
                        $val = preg_replace('/[^0-9]/', '', $val);
                        $_UPD[$key] = $val;
                    }

                    // 소멸시효일
                    $lost_date = Trade::updateLoanInfoLostDate('L', $_UPD['loan_info_law_no']);
                    if( !is_numeric($lost_date) )
                    {
                        $_INS['err_msg'] = "실행오류(소멸시효)";
                        $ERROR[$i] = $_INS;
                        $i++;
                        
                        continue;
                    }
                }
            }
        }
        else
        {
            echo "파일 X\n";
        }
    }
        
    // 마이그 안된 화해이자 거래원장에 업데이트
    public function settleInterest()
	{
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        
        // 로그 시간 찍기용
        $start_time = date("Y-m-d H:i:s");
        $record     = 0;
        
        echo "[start: ".$start_time."] 거래원장에 화해이자 업데이트 시작\n";
        log::channel('pl_work')->info("[start: ".$start_time."] 거래원장에 화해이자 업데이트 시작");

        $loan_info_no = '0';

        $rs1 = DB::connection('mig_erp')->table('trade_book_all')
                                    ->select('no','member_list_no','contract_info_no','settle_interest', 'save_time')
                                    ->WHERE('settle_interest', '>', '0')
                                    ->orderBy('contract_info_no','asc')
                                    ->orderBy('trade_date','asc')
                                    ->orderBy('save_time','asc')
                                    ->orderBy('no','asc')
                                    ->get();
        foreach($rs1 as $v)
        {
            if($loan_info_no != $v->contract_info_no)
            {
                $loan_info_no = $v->contract_info_no;

                echo "계약번호 : ".$loan_info_no." 업데이트 스타트 \n";
                log::channel('pl_work')->info("[계약번호 : ".$loan_info_no." 업데이트 스타트]");
            }

            unset($_UP, $_WHERE);

            $_WHERE['cust_info_no']  = $v->member_list_no;
            $_WHERE['loan_info_no']  = $v->contract_info_no;
            $_WHERE['mig_insert_no'] = $v->contract_info_no.'_'.$v->no;

            if(!empty($v->save_time))
            {
                $v->save_time = preg_replace('/\D/', '', $v->save_time);

                // Unix Timestamp 값인 경우
                if (strlen($v->save_time) == 10)
                {
                    $_WHERE['save_time'] = date('YmdHis',$v->save_time);
                }
            }
            
            $_UP['settle_interest']  = $v->settle_interest;

            DB::table("loan_info_trade")->where($_WHERE)->update($_UP);
            
            $record++;

            if($record%1000==0) echo $record."\n";
        }

        // 로그 시간 찍기용
        $end_time = date("Y-m-d H:i:s");

        echo "[start: ".$start_time." ~ end: ".$end_time."] 거래원장에 화해이자 업데이트 끝\n";
        log::channel('pl_work')->info("[start: ".$start_time." ~ end: ".$end_time."] 거래원장에 화해이자 업데이트 끝");
    }

    // 마이그 안된 감면이자 거래원장에 업데이트
    public function loseInterest()
	{
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);
        
        // 로그 시간 찍기용
        $start_time = date("Y-m-d H:i:s");
        $record     = 0;
        
        echo "[start: ".$start_time."] 거래원장에 감면이자 업데이트 시작\n";
        log::channel('pl_work')->info("[start: ".$start_time."] 거래원장에 감면이자 업데이트 시작");

        $loan_info_no = '0';

        $rs1 = DB::connection('mig_erp')->table('trade_book_all')
                                    ->select('no','member_list_no','contract_info_no','lose_interest', 'save_time')
                                    ->WHERE('lose_interest', '>', '0')
                                    ->orderBy('contract_info_no','asc')
                                    ->orderBy('trade_date','asc')
                                    ->orderBy('save_time','asc')
                                    ->orderBy('no','asc')
                                    ->get();
        foreach($rs1 as $v)
        {
            if($loan_info_no != $v->contract_info_no)
            {
                $loan_info_no = $v->contract_info_no;

                echo "계약번호 : ".$loan_info_no." 업데이트 스타트 \n";
                log::channel('pl_work')->info("[계약번호 : ".$loan_info_no." 업데이트 스타트]");
            }

            unset($_UP, $_WHERE);

            $_WHERE['cust_info_no']  = $v->member_list_no;
            $_WHERE['loan_info_no']  = $v->contract_info_no;
            $_WHERE['mig_insert_no'] = $v->contract_info_no.'_'.$v->no;

            if(!empty($v->save_time))
            {
                $v->save_time = preg_replace('/\D/', '', $v->save_time);

                // Unix Timestamp 값인 경우
                if (strlen($v->save_time) == 10)
                {
                    $_WHERE['save_time'] = date('YmdHis',$v->save_time);
                }
            }
            
            $_UP['lose_interest']  = $v->lose_interest;
            $_UP['lose_interest_sum']  = $v->lose_interest;

            DB::table("loan_info_trade")->where($_WHERE)->update($_UP);
            
            $record++;

            if($record%1000==0) echo $record."\n";
        }

        // 로그 시간 찍기용
        $end_time = date("Y-m-d H:i:s");

        echo "[start: ".$start_time." ~ end: ".$end_time."] 거래원장에 감면이자 업데이트 끝\n";
        log::channel('pl_work')->info("[start: ".$start_time." ~ end: ".$end_time."] 거래원장에 감면이자 업데이트 끝");
    }
    
    // 채권변동파일 소멸시효날짜 업데이트
    public function lostKcbDate()
	{
        ini_set('memory_limit', '-1');
        ini_set('max_execution_time', 0);

        // 필수키
        $this->requirArray = Array(
                                'loan_info_no'      => '0',        // 계약번호
                                'lost_date'         => '1',        // 소멸시효
                                );

        $path = "test/kfb_lost_date.xlsx";

        // 파일 저장 위치
        if(Storage::disk('public')->exists($path))
        {
            echo "파일 O\n";

            $ERROR = Array();
            $file = Storage::path('/public/'.$path);
            $colHeader = array('계약번호', '소멸시효');
            $colNm = array(
                'loan_info_no'      => '0',        // 계약번호
                'lost_date'         => '1',        // 소멸시효
            );

            $excelData = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader, 0); 

            $cnt = 0;
            $i = 0;

            // 엑셀 유효성 검사
            if (!isset($excelData)) 
            {
                // 엑셀파일 헤더 불일치
                echo "헤더 불일치\n";
            }
            else 
            {
                // 상태 '진행중' 변경
                echo "[상태: 진행중]\n";

                foreach ($excelData as $_DATA) 
                {
                    unset($_UPD);

                    // 데이터 정리
                    foreach($_DATA as $key => $val) 
                    {
                        $val = preg_replace('/[^0-9]/', '', $val);
                        $_UPD[$key] = $val;
                    }

                    $cnt++;
                    
                    $arrayCheck = $this->validKfbNLCheck($_UPD);

                    if(isset($arrayCheck["waring"]))
                    {
                        $_UPD['err_msg'] = $arrayCheck["waring"];
                        $ERROR[$i] = $_UPD;
                        $i++;
                        
                        continue;
                    }
                    
                    $rslt = DB::dataProcess("UPD", "kfb_ln9077", ['null_limit_date'=>$_UPD['lost_date']], ['loan_info_no'=>$_UPD['loan_info_no'], 'trade_date'=>'20240131']);
                }

                #################################
                # 실패 로그파일 생성
                #################################
                // 실패건이 있을시 결과파일 만든다.
                if(count($ERROR)>0)
                {      
                    $excel = Func::failExcelMake($colHeader, $ERROR, 'loan');
        
                    $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);
                    $file_path = $file_arr[1]."/".$file_arr[2];
                    $file_name = $file_arr[3];
        
                    echo "실패 로그파일 생성: 실패건이 있을시 결과파일 만든다.\n";
                }
            }
        }
        else
        {
            echo "파일 X\n";
        }
	}

    // 유효성 검사
    public function validKfbNLCheck($value) 
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

        // 계약번호 유효성
        $chk_loan =  DB::TABLE("loan_info")->SELECT("*")
                                            ->WHERE('no', $value['loan_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();

        if(empty($chk_loan))
        {
            $waring = "계약번호가 유효하지 않습니다.";
            $return["waring"] = $waring;
            return $return;
        }
    }
        
    // 화해채권 소급적용
    // php artisan Quick:actionhistory --flag=processingRetroactiveC
    public function processingRetroactiveC()
	{
        // 로그 시간 찍기용
        $start_time = date("Y-m-d H:i:s");

        echo "[start: ".$start_time."] 거래원장에 화해채권 소급적용 시작\n";
        log::channel('pl_work')->info("[start: ".$start_time."] 거래원장에 화해채권 소급적용 시작");

        $record = 0;

        $loan_info_trade = DB::table('loan_info_trade')->select('loan_info_no', DB::raw("min(no) as loan_info_trade_no"))
                                                        ->where('save_time', '>', '20240101000000')
                                                        ->where('settle_interest', '>', '0')
                                                        ->where('trade_type', '!=', '02')
                                                        ->where('save_status', 'Y')
                                                        ->whereRaw("( loan_info_no in (select no from loan_info where status in ('C', 'D') ) )")
                                                        ->groupBy('loan_info_no')
                                                        ->orderBy('loan_info_no', 'desc')
                                                        ->get();

        foreach ($loan_info_trade as $key => $value)
        {
            echo "계약번호 : ".$value->loan_info_no." 스타트 \n";
            log::channel('pl_work')->info("[계약번호 : ".$value->loan_info_no." 스타트]");
            
            $array_reaction_trade      = [];
            $array_reaction_cost_trade = [];

            // 1. 입금 모두 취소
            $trade  = new Trade($value->loan_info_no);
            $DELTRD = DB::TABLE("LOAN_INFO_TRADE")->SELECT("*")->WHERE("LOAN_INFO_NO", $value->loan_info_no)->WHERE("SAVE_STATUS", "Y")->WHERE("NO", ">=", $value->loan_info_trade_no)->ORDERBY("NO", "DESC");
            $deltrd = $DELTRD->GET();
            $deltrd = Func::chungDec(["LOAN_INFO_TRADE"], $deltrd);	// CHUNG DATABASE DECRYPT

            foreach( $deltrd as $val )
            {
                if( $val->no >= $value->loan_info_trade_no )
                {
                    if($val->trade_div == 'I')
                    {
                        $rslt1 = $trade->tradeInDelete($val->no);
        
                        if( is_string($rslt1) )
                        {
                            log::channel('pl_work')->info('입금취소 실패함 거래원장번호'.$val->no);
                        }
                    }
                    else if($val->trade_div == 'C')
                    {
                        $rslt1 = $trade->tradeCostDelete($val->no);
        
                        if( is_string($rslt1) )
                        {
                            log::channel('pl_work')->info('법비용취소 실패함 거래원장번호'.$val->no);
                        }
                        
                        $DELCOST = DB::TABLE("LOAN_INFO_LAW_COST")->SELECT("*")->WHERE("loan_info_trade_no", $val->no)->WHERE("SAVE_STATUS", "Y")->ORDERBY("NO", "DESC")->get();
                        $DELCOST = Func::chungDec(["LOAN_INFO_LAW_COST"], $DELCOST);	// CHUNG DATABASE DECRYPT
                        $DELCOST = json_decode(json_encode($DELCOST, JSON_UNESCAPED_UNICODE),TRUE);

                        foreach ($DELCOST as $k => $v)
                        {
                            $array_reaction_cost_trade[$v['loan_info_trade_no']] = $v;
                        }
                        
                        $rslt2 = DB::dataProcess("UPD", 'LOAN_INFO_LAW_COST', ["SAVE_STATUS"=>"N","DEL_ID"=>'SYSTEM',"DEL_TIME"=>date("YmdHis")],["loan_info_trade_no"=>$val->no]);
                    }
                    else if($val->trade_div == 'O')
                    {
                        $rslt1 = $trade->tradeOutDelete($val->no);
        
                        if( is_string($rslt1) )
                        {
                            log::channel('pl_work')->info('출금취소 실패함 거래원장번호'.$val->no);
                        }
                    }
                    else
                    {
                        log::channel('pl_work')->info('있을리가 없는데 무슨타입? 거래원장번호'.$val->no);
                    }

                    // 재입금 대상 번호
                    $array_reaction_trade[$val->no] = $val;
                }
            }

            // 2. 입금 모두 등록
            if( sizeof($array_reaction_trade)>0 )
            {
                $save_time = date("YmdHis");

                asort($array_reaction_trade);

                foreach( $array_reaction_trade as $val )
                {
                    $trade = new Trade($value->loan_info_no);    
                    
                    $_INS = [];
                    $_INS['action_mode']   = "INSERT";
                    $_INS['cust_info_no']  = $val->cust_info_no;
                    $_INS['loan_info_no']  = $val->loan_info_no;
                    $_INS['trade_type']    = $val->trade_type;
                    $_INS['trade_path_cd'] = $val->trade_path_cd;
                    $_INS['in_name']       = $val->in_name;
                    $_INS['bank_cd']       = $val->bank_cd;
                    $_INS['bank_ssn']      = $val->bank_ssn;
                    $_INS['vir_acct_ssn']  = $val->vir_acct_ssn;
                    $_INS['manager_code']  = $val->manager_code;
                    $_INS['manager_id']    = $val->manager_id;
                    $_INS['trade_date']    = $val->trade_date;
                    $_INS['trade_money']   = $val->trade_money;
                    $_INS['lose_money']    = "0";
                    $_INS['save_time']     = $save_time;
                    $_INS['memo']          = "화해상환 입금재처리\n원거래 No = ".$val->no;

                    if($val->trade_div == 'I')
                    {
                        $rslt3 = $trade->tradeInInsert($_INS, 'SYSTEM');
                        
                        if( !is_numeric($rslt3) )
                        {
                            log::channel('pl_work')->info('입금처리 실패함 거래원장번호'.$val->no);
                        }
                    }
                    else if($val->trade_div == 'C')
                    {
                        $rslt3 = $trade->tradeCostInsert($_INS, 'SYSTEM');
                        
                        if( !is_numeric($rslt3) )
                        {
                            log::channel('pl_work')->info('법비용등록 실패함. 메세지 : '.$rslt3.' 거래원장번호'.$val->no);
                        }
                        
                        if(!empty($array_reaction_cost_trade[$val->no]))
                        {
                            $COST                       = [];
                            $COST                       = $array_reaction_cost_trade[$val->no];
                            $COST['loan_info_trade_no'] = $rslt3;
                            $COST['save_id']            = 'SYSTEM';
                            $COST['save_time']          = $save_time;

                            unset($COST['no']);

                            $rslt4 = DB::dataProcess("INS", 'LOAN_INFO_LAW_COST',$COST);
                        }
                        else
                        {
                            log::channel('pl_work')->info('법비용등록 실패함. 거래원장번호 : '.$val->no.' 멈추자');
                            exit;
                        }
                    }
                    else if($val->trade_div == 'O')
                    {
                        $rslt3 = $trade->tradeOutInsert($_INS, 'SYSTEM');
                        
                        if( !is_numeric($rslt3) )
                        {
                            log::channel('pl_work')->info('출금처리 실패함 거래원장번호'.$val->no);
                        }
                    }
                    else
                    {
                        log::channel('pl_work')->info('있을리가 없는데 무슨타입? 거래원장번호'.$val->seq);
                    }
                }
            }

            $record++;

            if($record%1000==0) echo $record."\n";
        }

        // 로그 시간 찍기용
        $end_time = date("Y-m-d H:i:s");

        echo "[start: ".$start_time." ~ end: ".$end_time."] 화해채권 거래원장에 소급적용 끝\n";
        log::channel('pl_work')->info("[start: ".$start_time." ~ end: ".$end_time."] 화해채권 거래원장에 소급적용 끝");
	}

    // 일반채권 소급적용
    // php artisan Quick:actionhistory --flag=processingRetroactiveA
    public function processingRetroactiveA()
    {
        // 로그 시간 찍기용
        $start_time = date("Y-m-d H:i:s");

        echo "[start: ".$start_time."] 일반채권 거래원장에 소급적용 시작\n";
        log::channel('pl_work')->info("[start: ".$start_time."] 일반채권 거래원장에 소급적용 시작");

        $record = 0;

        $loan_info_trade = DB::table('loan_info_trade')->select('loan_info_no', DB::raw("min(no) as loan_info_trade_no"))
                                                        ->where('save_time', '>', '20240101000000')
                                                        ->where('balance', '>', '0')
                                                        ->where('save_status', 'Y')
                                                        ->whereRaw("( loan_info_no in (select no from loan_info where status in ('A', 'B') ) )")
                                                        ->groupBy('loan_info_no')
                                                        ->orderBy('loan_info_no', 'desc')
                                                        ->get();

        foreach ($loan_info_trade as $key => $value)
        {
            echo "계약번호 : ".$value->loan_info_no." 스타트 \n";
            log::channel('pl_work')->info("[계약번호 : ".$value->loan_info_no." 스타트]");
            
            $array_reaction_trade      = [];
            $array_reaction_cost_trade = [];

            // 1. 입금 모두 취소
            $trade  = new Trade($value->loan_info_no);
            $DELTRD = DB::TABLE("LOAN_INFO_TRADE")->SELECT("*")->WHERE("LOAN_INFO_NO", $value->loan_info_no)->WHERE("SAVE_STATUS", "Y")->WHERE("NO", ">=", $value->loan_info_trade_no)->ORDERBY("NO", "DESC");
            $deltrd = $DELTRD->GET();
            $deltrd = Func::chungDec(["LOAN_INFO_TRADE"], $deltrd);	// CHUNG DATABASE DECRYPT

            foreach( $deltrd as $val )
            {
                if( $val->no >= $value->loan_info_trade_no )
                {
                    if($val->trade_div == 'I')
                    {
                        $rslt1 = $trade->tradeInDelete($val->no);
        
                        if( is_string($rslt1) )
                        {
                            log::channel('pl_work')->info('입금취소 실패함 거래원장번호'.$val->no);
                        }
                    }
                    else if($val->trade_div == 'C')
                    {
                        $rslt1 = $trade->tradeCostDelete($val->no);
        
                        if( is_string($rslt1) )
                        {
                            log::channel('pl_work')->info('법비용취소 실패함 거래원장번호'.$val->no);
                        }
                        
                        $DELCOST = DB::TABLE("LOAN_INFO_LAW_COST")->SELECT("*")->WHERE("loan_info_trade_no", $val->no)->WHERE("SAVE_STATUS", "Y")->ORDERBY("NO", "DESC")->get();
                        $DELCOST = Func::chungDec(["LOAN_INFO_LAW_COST"], $DELCOST);	// CHUNG DATABASE DECRYPT
                        $DELCOST = json_decode(json_encode($DELCOST, JSON_UNESCAPED_UNICODE),TRUE);

                        foreach ($DELCOST as $k => $v)
                        {
                            $array_reaction_cost_trade[$v['loan_info_trade_no']] = $v;
                        }
                        
                        $rslt2 = DB::dataProcess("UPD", 'LOAN_INFO_LAW_COST', ["SAVE_STATUS"=>"N","DEL_ID"=>'SYSTEM',"DEL_TIME"=>date("YmdHis")],["loan_info_trade_no"=>$val->no]);
                    }
                    else if($val->trade_div == 'O')
                    {
                        $rslt1 = $trade->tradeOutDelete($val->no);
        
                        if( is_string($rslt1) )
                        {
                            log::channel('pl_work')->info('출금취소 실패함 거래원장번호'.$val->no);
                        }
                    }
                    else
                    {
                        log::channel('pl_work')->info('있을리가 없는데 무슨타입? 거래원장번호'.$val->no);
                    }

                    // 재입금 대상 번호
                    $array_reaction_trade[$val->no] = $val;
                }
            }

            // 2. 입금 모두 등록
            if( sizeof($array_reaction_trade)>0 )
            {
                $save_time = date("YmdHis");

                asort($array_reaction_trade);

                foreach( $array_reaction_trade as $val )
                {
                    $trade = new Trade($value->loan_info_no);    
                    
                    $_INS = [];
                    $_INS['action_mode']   = "INSERT";
                    $_INS['cust_info_no']  = $val->cust_info_no;
                    $_INS['loan_info_no']  = $val->loan_info_no;
                    $_INS['trade_type']    = $val->trade_type;
                    $_INS['trade_path_cd'] = $val->trade_path_cd;
                    $_INS['in_name']       = $val->in_name;
                    $_INS['bank_cd']       = $val->bank_cd;
                    $_INS['bank_ssn']      = $val->bank_ssn;
                    $_INS['vir_acct_ssn']  = $val->vir_acct_ssn;
                    $_INS['manager_code']  = $val->manager_code;
                    $_INS['manager_id']    = $val->manager_id;
                    $_INS['trade_date']    = $val->trade_date;
                    $_INS['trade_money']   = $val->trade_money;
                    $_INS['lose_money']    = "0";
                    $_INS['save_time']     = $save_time;
                    $_INS['memo']          = "화해상환 입금재처리\n원거래 No = ".$val->no;

                    if($val->trade_div == 'I')
                    {
                        $rslt3 = $trade->tradeInInsert($_INS, 'SYSTEM');
                        
                        if( !is_numeric($rslt3) )
                        {
                            log::channel('pl_work')->info('입금처리 실패함 거래원장번호'.$val->no);
                        }
                    }
                    else if($val->trade_div == 'C')
                    {
                        $rslt3 = $trade->tradeCostInsert($_INS, 'SYSTEM');
                        
                        if( !is_numeric($rslt3) )
                        {
                            log::channel('pl_work')->info('법비용등록 실패함. 메세지 : '.$rslt3.' 거래원장번호'.$val->no);
                        }
                        
                        if(!empty($array_reaction_cost_trade[$val->no]))
                        {
                            $COST                       = [];
                            $COST                       = $array_reaction_cost_trade[$val->no];
                            $COST['loan_info_trade_no'] = $rslt3;
                            $COST['save_id']            = 'SYSTEM';
                            $COST['save_time']          = $save_time;

                            unset($COST['no']);

                            $rslt4 = DB::dataProcess("INS", 'LOAN_INFO_LAW_COST',$COST);
                        }
                        else
                        {
                            log::channel('pl_work')->info('법비용등록 실패함. 거래원장번호 : '.$val->no.' 멈추자');
                            exit;
                        }
                    }
                    else if($val->trade_div == 'O')
                    {
                        $rslt3 = $trade->tradeOutInsert($_INS, 'SYSTEM');
                        
                        if( !is_numeric($rslt3) )
                        {
                            log::channel('pl_work')->info('출금처리 실패함 거래원장번호'.$val->no);
                        }
                    }
                    else
                    {
                        log::channel('pl_work')->info('있을리가 없는데 무슨타입? 거래원장번호'.$val->seq);
                    }
                }
            }

            $record++;

            if($record%1000==0) echo $record."\n";
        }

        // 로그 시간 찍기용
        $end_time = date("Y-m-d H:i:s");

        echo "[start: ".$start_time." ~ end: ".$end_time."] 일반채권 거래원장에 소급적용 끝\n";
        log::channel('pl_work')->info("[start: ".$start_time." ~ end: ".$end_time."] 일반채권 거래원장에 소급적용 끝");
    }

    // 화해감면입금 중복건 삭제
    // php artisan Quick:actionhistory --flag=deleteDuplicateTradeIn
    public function deleteDuplicateTradeIn()
    {
        // 로그 시간 찍기용
        $start_time = date("Y-m-d H:i:s");

        echo "[start: ".$start_time."] 화해감면입금 중복건 삭제 시작\n";
        log::channel('pl_work')->info("[start: ".$start_time."] 화해감면입금 중복건 삭제");

        $record = 0;
        $loan_info_trade = DB::table('loan_info_trade')->select('loan_info_no', 'no as loan_info_trade_no')
                                                        ->where('save_status', 'Y')
                                                        ->where('loan_info_no', '271049')
                                                        ->where('no', '000000') // 거래원장 번호 바꿔주기 !!! 
                                                        ->orderBy('loan_info_no', 'desc')
                                                        ->get();

        foreach ($loan_info_trade as $key => $value)
        {
            echo "계약번호 : ".$value->loan_info_no." 스타트 \n";
            log::channel('pl_work')->info("[계약번호 : ".$value->loan_info_no." 스타트]");
            
            // 입금 취소
            $trade  = new Trade($value->loan_info_no);
            $DELTRD = DB::TABLE("LOAN_INFO_TRADE")->SELECT("*")->WHERE("LOAN_INFO_NO", $value->loan_info_no)->WHERE("SAVE_STATUS", "Y")->WHERE("NO", ">=", $value->loan_info_trade_no)->ORDERBY("NO", "DESC");
            $deltrd = $DELTRD->GET();
            $deltrd = Func::chungDec(["LOAN_INFO_TRADE"], $deltrd);	// CHUNG DATABASE DECRYPT

            foreach( $deltrd as $val )
            {
                if( $val->no >= $value->loan_info_trade_no )
                {
                    if($val->trade_div == 'I')
                    {
                        $rslt1 = $trade->tradeInDelete($val->no, "", "SETTLE_FORM");
        
                        if( is_string($rslt1) )
                        {
                            log::channel('pl_work')->info('입금취소 실패함 거래원장번호'.$val->no);
                        }
                    }
                    else
                    {
                        log::channel('pl_work')->info('있을리가 없는데 무슨타입? 거래원장번호'.$val->no);
                    }
                }
            }

            $record++;

            if($record%1000==0) echo $record."\n";
        }

        // 로그 시간 찍기용
        $end_time = date("Y-m-d H:i:s");

        echo "[start: ".$start_time." ~ end: ".$end_time."] 화해감면입금 중복건 삭제 끝\n";
        log::channel('pl_work')->info("[start: ".$start_time." ~ end: ".$end_time."] 화해감면입금 중복건 삭제 끝");
    }

    // 가상계좌 등록
    // php artisan Quick:actionhistory --flag=insertVirAcct
    public function insertVirAcct()
    {
        // 로그 시간 찍기용
        $start_time = date("Y-m-d H:i:s");

        echo "[start: ".$start_time."] 가상계좌 등록 시작\n";
        log::channel('pl_work')->info("[start: ".$start_time."] 가상계좌 등록");

        // 필수키
        $this->requirArray = Array('vir_acct_ssn' => '가상계좌번호',);

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $file = "";
        $ERROR = Array();

        $path = "test/vir_ins.xlsx"; // 엑셀 파일 위치

        $total_cnt = 0; 
        $error_cnt = 0; 
        $i = 0;
        if(Storage::disk('public')->exists($path)) 
        {
            $file = Storage::path('/public/'.$path);

            $colHeader  = array('가상계좌번호',);
            $colNm      = array(
                'vir_acct_ssn'         => '0',	    // 가상계좌번호
            );

            $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

            // 엑셀 유효성 검사
            if(!isset($excelData))
            {
                echo "엑셀파일 헤더 불일치\n";
            }
            else
            {
                echo "진행중\n";

                foreach($excelData as $_DATA) 
                {
                    unset(
                        $arrayCheck,
                        $_INS,
                    );

                    $arrayCheck = Array();

                    // 데이터 정리
                    foreach($_DATA as $val) 
                    {
                        $val = trim($val);
                        $_INS['vir_acct_ssn'] = $val;
                    }

                    $total_cnt++;

                    $arrayCheck = $this->validVirAcctCheck($_INS);

                    if(isset($arrayCheck["waring"]))
                    {
                        $_INS['err_msg'] = $arrayCheck["waring"];
                        if (isset($arrayCheck['col']))
                        {
                            $_INS[$arrayCheck['col']] = 'ERROR';
                        }
                        $ERROR[$i] = $_INS;
                        $i++;
                        
                        continue;
                    }

                    $_INS['bank_cd'] = '020';
                    $_INS['save_status'] = 'Y';
                    $_INS['reg_date'] = date('Ymd');
                    $_INS['cust_info_no'] = 0;
                    $_INS['worker_id'] = 'SYSTEM';
                    $_INS['save_time'] = date('YmdHis');

                    $rslt = DB::dataProcess('INS', 'vir_acct', $_INS);
                }

                if(!empty($ERROR))
                {
                    $error_cnt = count($ERROR);
                }
                $ok_count = $total_cnt-$error_cnt;
                
                echo "전체건수".$total_cnt."\n";
                echo "성공건수".$ok_count."\n";
                echo "실패건수".$error_cnt."\n";
                $end_time = date("Y-m-d H:i:s");

                echo "[start: ".$end_time."] 가상계좌 등록 종료\n";
                echo "종료\n";
            }
        }
        else
        {
            echo "엑셀파일 미존재\n";
        }

        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'virAcct');

            $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);
            $file_path = $file_arr[1]."/".$file_arr[2]."/".$file_arr[3];
            $file_name = $file_arr[4];
            
            echo $file_path."\n";
            echo $file_name."\n";
            echo "에러파일생성완료\n";
	    }
    }

    public function validVirAcctCheck($value) 
    {
        $waring = "";
        $return = Array();
        
        // 외부문서참조함수 확인
        foreach($value as $key => $val) 
        {
            if (substr($val,0,1) === '=')
            {
                $waring = "엑셀 함수는 사용이 불가합니다.";
                $return["waring"] = $waring;
                $return["col"] = $key;
                return $return;
            }
        }

        if($value['vir_acct_ssn'] == '' || empty($value['vir_acct_ssn']))
        {
            $waring = "계좌번호 비어있음"; 
            $return["waring"] = $waring;
            return $return;
        }

        if(!preg_match('/^[0-9]+$/', $value['vir_acct_ssn']))
        {
            $waring = "계좌번호 확인";
            $return["waring"] = $waring;
            return $return;
        }
    }

    // 가상계좌 회원 등록
    // php artisan Quick:actionhistory --flag=updateVirAcctWithCust
    public function updateVirAcctWithCust()
    {
        // 로그 시간 찍기용
        $start_time = date("Y-m-d H:i:s");

        echo "[start: ".$start_time."] 가상계좌 회원 등록 시작\n";
        log::channel('pl_work')->info("[start: ".$start_time."] 가상계좌 회원 등록");

        // 필수키
        $this->requirArray = Array('cust_info_no' => '회원번호',);

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $file = "";
        $ERROR = Array();

        $path = "test/vir_cust_upd.xlsx"; // 엑셀 파일 위치

        $total_cnt = 0; 
        $error_cnt = 0; 
        $i = 0;
        if(Storage::disk('public')->exists($path)) 
        {
            $file = Storage::path('/public/'.$path);

            $colHeader  = array('회원번호',);
            $colNm      = array(
                'cust_info_no'         => '0',	    // 회원번호
            );

            $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

            // 엑셀 유효성 검사
            if(!isset($excelData))
            {
                echo "엑셀파일 헤더 불일치\n";
            }
            else
            {
                echo "진행중\n";

                $save_time = date("YmdHis");
                foreach($excelData as $_DATA) 
                {
                    unset(
                        $arrayCheck,
                        $_INS,
                    );
                    
                    $arrayCheck = Array();
                    
                    // 데이터 정리
                    foreach($_DATA as $val) 
                    {
                        $val = trim($val);
                        $_INS['cust_info_no'] = $val;
                    }

                    $total_cnt++;

                    $arrayCheck = $this->validCustNoCheck($_INS);

                    if(isset($arrayCheck["waring"]))
                    {
                        $_INS['err_msg'] = $arrayCheck["waring"];
                        if (isset($arrayCheck['col']))
                        {
                            $_INS[$arrayCheck['col']] = 'ERROR';
                        }
                        $ERROR[$i] = $_INS;
                        $i++;
                        
                        continue;
                    }

                    
                    // 회원번호에 zb를 붙여서 업로드할 경우 제거
                    if(strpos($_INS['cust_info_no'], 'zb') !== false)
                    {
                        $_INS['cust_info_no'] = str_replace('zb', '', $_INS['cust_info_no']);
                    }
                    
                    // 가장 높은 loan_info_no 가져오기
                    $loan_info_no = DB::TABLE("LOAN_INFO")->WHERE("CUST_INFO_NO", $_INS['cust_info_no'])->MAX("NO"); 
                    
                    
                    // 유효한 가상계좌 찾기
                    $VIR_NO = DB::TABLE("VIR_ACCT")->WHERERAW("COALESCE(CUST_INFO_NO,0)=0 ")->WHERE("SAVE_STATUS","Y")->MIN("NO");
                    
                    $VIR = DB::TABLE('VIR_ACCT')->SELECT('MO_SSN, BANK_CD, VIR_ACCT_SSN')->WHERE('NO',$VIR_NO)->FIRST();
                    $VIR = Func::chungDec(["VIR_ACCT"], $VIR);	// CHUNG DATABASE DECRYPT

                    $cust = DB::TABLE("CUST_INFO")->select("NAME")->WHERE("SAVE_STATUS","Y")->WHERE("NO", $_INS['cust_info_no'])->ORDERBY("NO")->FIRST();
                    $cust = Func::chungDec(["CUST_INFO"], $cust);	// CHUNG DATABASE DECRYPT
                    $name = $cust->name;

                    // 가상계좌 발급
                    $rslt_stb = self::setVirtualAccount($_INS['cust_info_no'], $loan_info_no, $name);
                    if($rslt_stb=='duple')
                    {
                        $_INS['err_msg'] = "이미 동일한 회원번호로 발급된 가상계좌가 있습니다. 관리자에게 문의해 주세요.";
                        $ERROR[$i] = $_INS;
                        $i++;
                        
                        continue;
                    }
                    else if($rslt_stb=='N')
                    {
                        $_INS['err_msg'] = "가상계좌 발급중 오류가 발생했습니다. 관리자에게 문의해 주세요.";
                        $ERROR[$i] = $_INS;
                        $i++;
                        
                        continue;
                    }
                    else 
                    {
                        // 계약정보 업데이트
                        $rslt_loan = DB::dataProcess("UPD", 'LOAN_INFO', ['VIR_ACCT_MO_BANK_CD'=>$VIR->bank_cd,'VIR_ACCT_MO_SSN'=>$VIR->mo_ssn, 'SAVE_ID'=>'SYSTEM', 'SAVE_TIME'=>$save_time], ["CUST_INFO_NO"=>$_INS['cust_info_no']]);
                        if($rslt_loan != 'Y')
                        {
                            $_INS['err_msg'] = "계약정보 업데이트 실패";
                            $ERROR[$i] = $_INS;
                            $i++;
                            
                            continue;
                        }
                        else 
                        {
                            //  원장변경내역 등록
                            $_wch = [
                                "cust_info_no"  =>  $_INS['cust_info_no'],
                                "loan_info_no"  =>  $loan_info_no,
                                "worker_id"     =>  "SYSTEM",
                                "work_time"     =>  date("Ymd"),
                                "worker_code"   =>  "",
                                "loan_status"   =>  "",
                                "manager_code"  =>  "",
                                "div_nm"        =>  "가상계좌번호변경(발급)",
                                "before_data"   =>  "",
                                "after_data"    =>  $VIR->bank_cd.",".$VIR->vir_acct_ssn,
                                "trade_type"    =>  "",
                                "sms_yn"        =>  "N",
                                "memo"          =>  "",
                            ];

                            $result_wch = Func::saveWonjangChgHist($_wch);
                            if( $result_wch != "Y" )
                            {
                                echo "가상계좌번호변경(발급) - 원장변경내역 저장 실패 계약번호 : ".$loan_info_no."\n";
                                $ERROR[$i] = $_INS;
                                $i++;

                                continue;
                            }
                        }
                    }
                }

                if(!empty($ERROR))
                {
                    $error_cnt = count($ERROR);
                }
                $ok_count = $total_cnt-$error_cnt;
                
                echo "전체건수".$total_cnt."\n";
                echo "성공건수".$ok_count."\n";
                echo "실패건수".$error_cnt."\n";
                $end_time = date("Y-m-d H:i:s");

                echo "[start: ".$end_time."] 가상계좌 등록 종료\n";
                echo "종료\n";
            }
        }
        else
        {
            echo "엑셀파일 미존재\n";
            return;
        }

        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
            $excel = Func::failExcelMake($colHeader, $ERROR, 'virAcct');

            $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);
            $file_path = $file_arr[1]."/".$file_arr[2]."/".$file_arr[3];
            $file_name = $file_arr[4];
            
            echo $file_path."\n";
            echo $file_name."\n";
            echo "에러파일생성완료\n";
	    }
    }

    public function validCustNoCheck($value) 
    {
        $waring = "";
        $return = Array();
        
        // 외부문서참조함수 확인
        if (substr($val,0,1) === '=')
        {
            $waring = "엑셀 함수는 사용이 불가합니다.";
            $return["waring"] = $waring;
            $return["col"] = $key;
            return $return;
        }
        

        if($value['cust_info_no'] == '' || empty($value['cust_info_no']))
        {
            $waring = "회원번호가 비어있습니다"; 
            $return["waring"] = $waring;
            return $return;
        }
    }

    /**                         
	 * 가상계좌 셋팅 
	 * 
	 * @param String $cust_info_no,$loan_info_no
	 * @return String $receive_msg
	 */
    function setVirtualAccount($cust_info_no, $loan_info_no, $name)
    {
        $vir = DB::TABLE('VIR_ACCT')->SELECT("*")->WHERE("SAVE_STATUS","Y")->WHERE("CUST_INFO_NO",$cust_info_no)->ORDERBY("NO")->FIRST(); 
        Log::debug((array)$vir);
        // 해당 고객번호로 등록된 가상계좌가 있음.
        if(isset($vir))
        {
            return 'duple';
        }
        else 
        {
            $v = DB::TABLE("VIR_ACCT")->SELECT("*")->WHERERAW("COALESCE(CUST_INFO_NO,0)=0 ")->WHERE("SAVE_STATUS","Y")->ORDERBY("NO")->FIRST();

            // VIR_ACCT 테이블에 관련내용 업데이트
            if(!isset($v))
            {
                return 'N';
            }
            // 가상계좌 등록
            else
            {
                // 가상계좌 업데이트
                $UP['loan_info_no'] = $loan_info_no;
                $UP['cust_info_no'] = $cust_info_no;
                $UP['save_time']    = date("YmdHis"); 
                $UP['reg_date']     = date("Ymd");
                $UP['save_id']      = "SYSTEM";
                $upd                = DB::dataProcess("UPD", 'vir_acct', $UP, ["no" => $v->no]);

                // 세팅정보 넣기 VIR_ACCT_SET
                $_IN['cust_info_no']    = $cust_info_no;
                $_IN['loan_info_no']    = $loan_info_no;
                $_IN['bank_ssn']        = Func::chungDecOne($v->vir_acct_ssn);
                $_IN['name']            = $name;
                $_IN['adddate']         = date("Ymd");
                $_IN['addtime']         = date("His");
                $_IN['hb']              = 'HB';
                $_IN['company_id']      = 'CO_KICO';                // env로 빼자.
                $_IN['bukrs']           = '1';
                $_IN['tr_key']          = '';
                $_IN['trcode']          = 'WBV01';
                $_IN['header1']         = '';
                $_IN['header2']         = '';
                $_IN['money_set']       = '0';
                $_IN['s_date']          = date("Ymd");
                $_IN['e_date']          = '99991231';
                $_IN['s_time']          = '000001';
                $_IN['e_time']          = '235959';
                $_IN['type_code']       = 'D';
                $_IN['money_code']      = '2';
                $_IN['result_code']     = '';
                $_IN['result_memo']     = '';
                $_IN['worker_id']       = "SYSTEM";
                $_IN['save_time']       = date("YmdHis");
                $_IN['save_status']     = 'Y';
                $no = DB::table('vir_acct_set')->insertGetId($_IN, 'no');
                Log::debug("INSERT NO :".$no);
                
                if(empty($no))
                {
                    return 'N';
                }
                else 
                {
                    // WBV01 DT테이블 입력 (실시간)
                    $_DT['COMPANY_ID']          = $_IN['company_id'];     // 업체코드(은행부여 업체코드)
                    $_DT['REQUEST_DATE']        = $_IN['adddate'];        // 요청일자(현재일)
                    $_DT['TR_CODE']             = $_IN['trcode'];         // 거래코드(WBV01)
                    $_DT['TR_KEY']              = sprintf("%05d", $no);   // 거래키(일별/거래별 UNIQUE 키)
                    $_DT['SEQ']                 = '1';                    // 거래순번(반복부내 거래순번) 현재 1로 고정
                    $_DT['BUKRS']               = $_IN['bukrs'];          // 회사코드(고객지정 여분 키 필드
                    $_DT['REQ_RES']             = 'R';                    // 요청/응답구분 요청(R) 응답(S)
                    $_DT['STATUS']              = '11';                   // 상태(최초'11')
                    $_DT['STATUS_DESC']         = '처리대기중';           // 상태메세지(최초'처리대기중')
                    $_DT['FIELD1']              = $_IN['bank_ssn'];       // 가상계좌번호
                    $_DT['FIELD2']              = '09122202';             // 업체고객번호(업체에서 부여하는 구분 Key)
                    $_DT['FIELD3']              = '0';                    // 납부금액
                    $_DT['FIELD4']              = $_IN['s_date'];         // 납부시작일자 (ex: 20060601)
                    $_DT['FIELD5']              = $_IN['e_date'];         // 납부종료일자 (ex: 20061231)
                    $_DT['FIELD6']              = $_IN['s_time'];         // 납부시작시간 (ex: 000001)
                    $_DT['FIELD7']              = $_IN['e_time'];         // 납부종료시간 (ex: 235959)
                    $_DT['FIELD8']              = 'D';                    // 등록/해지 구분코드 (D-등록, C-해지)
                    $_DT['FIELD9']              = '2';                    // 금액체크 구분 코드 기존에 2번 사용
                    $_DT['FIELD10']             = $_IN['name'];           // 고객이름

                    // 운영만 등록
                    if(config('app.env')=='prod')
                    {
                        DB::connection('mybank')->table('WCMS_GW_TR_DT')->insert($_DT);
                    }
                    
                    // WBV01 헤더테이블 입력 (실시간)
                    $_HDR['COMPANY_ID']         = $_IN['company_id'];     // 업체코드(은행부여 업체코드)
                    $_HDR['REQUEST_DATE']       = $_IN['adddate'];        // 요청일자(현재일)
                    $_HDR['TR_CODE']            = $_IN['trcode'];         // 거래코드(WBV01)
                    $_HDR['TR_KEY']             = $_DT['TR_KEY'];         // 거래키(일별/거래별 UNIQUE 키)
                    $_HDR['BUKRS']              = $_IN['bukrs'];          // 회사코드(고객지정 여분 키 필드)
                    $_HDR['TARGET']             = 'WBANK';                // 전송대상(WBANK)
                    $_HDR['TR_FLAG']            = 'B';                    // 거래구분('B' 배치)
                    $_HDR['REQUEST_TIME']       = $_IN['addtime'];        // 요청시간(현재시간)
                    $_HDR['EXEC_DATE']          = $_IN['adddate'];        // 실행일자(실행예정일)
                    $_HDR['IBANKING_ID']        = 'KICORES';              // banking ID env로 빼자. 
                    $_HDR['IBANKING_MST_ID']    = $_HDR['IBANKING_ID'];
                    $_HDR['BANK_EXEC_DATE']     = $_IN['adddate'];        // 은행실행일자(실행일자(EXEC_DATE)와 동일일로 세팅
                    $_HDR['STATUS']             = '11';                   // 상태(최초'11')
                    $_HDR['STATUS_DESC']        = '처리대기중';           // 상태메세지(최초'처리대기중')
                    $_HDR['TOTAL_CNT']          = '1';                    // 총건수(반복부 총건수)	

                    // 운영만 등록
                    if(config('app.env')=='prod')
                    {
                        DB::connection('mybank')->table('WCMS_GW_TR_HDR')->insert($_HDR);
                    }

                    // WBV02 헤더테이블 입력 (실시간)WCMS_GW_TR_HDR
                    $_HDR['TR_CODE']            = 'WBV02';                // 거래코드(WBV02)    
                    $_HDR['FIELD1']            = $_IN['adddate'];         // 원거래일자(원거래일자)
                    $_HDR['FIELD2']            = $_DT['TR_KEY'];          // 원거래키(원거래키) -- 원거래키가 뭘 말하는지 파악할것                    
                    
                    // 운영만 등록
                    if(config('app.env')=='prod')
                    {
                        DB::connection('mybank')->table('WCMS_GW_TR_HDR')->insert($_HDR);
                    }

                    return 'Y';
                }
            }

            
        }
    }

    // 채권담보관리일괄배치 해지
    // php artisan Quick:actionhistory --flag=lumpBorrowUpdate
    public function lumpBorrowUpdate()
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

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);

        $file = "";
        $ERROR = Array();

        $path = "test/lumpborrowdelete_1.xlsx";

        $total_cnt = $cnt = 0; 
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
            echo "엑셀파일 헤더 불일치";
            }
            else
            {
                echo "상태 '진행중' 변경";

                foreach($excelData as $_DATA) 
                {
                    unset($_INS, $_WHERE);
                    $arrayCheck = Array();

                    // 데이터 정리
                    foreach($_DATA as $key => $val) 
                    {
                        $val = trim($val);
                        $_INS[$key] = $val;
                    }

                    $total_cnt++;

                    $arrayCheck = $this->validCheckLumpBorrow($_INS);

                    if(isset($arrayCheck["waring"]))
                    {
                        $_INS['err_msg'] = $arrayCheck["waring"];
                        if (isset($arrayCheck['col']))
                        {
                            $_INS[$arrayCheck['col']] = 'ERROR';
                        }
                        $ERROR[$i] = $_INS;
                        $i++;
                        
                        continue;
                    }

                    // 회원번호에 zb를 붙여서 업로드할경 제거
                    if(strpos($_INS['cust_info_no'], 'zb') !== false)
                    {
                        $_INS['cust_info_no'] = str_replace('zb', '', $_INS['cust_info_no']);
                    }

                    $_INS['process_div'] = Func::nvl(array_flip($this->arrayProcess)[$_INS['process_div']], '');
                    $_INS['end_date']    = str_replace('-', '', $_INS['end_date']);
                    $_INS['start_date']  = str_replace('-', '', $_INS['start_date']);
                    $_INS['save_time']   = date("YmdHis");
                    $_INS['save_id']     = 'k105';
                    $_INS['save_status'] = "Y";

                    // 담보제공처확인
                    $chk_borrow =  DB::TABLE("borrow_comp")->SELECT("no")
                                                    ->WHERE('bank_name', Func::chungEncOne($_INS['bank_name']))
                                                    ->WHERE('save_status','Y')
                                                    ->first();

                    if($_INS['process_div'] == 'A')
                    {
                        echo "등록";
                    }
                    elseif($_INS['process_div'] == 'D')
                    {
                        $_INS['status'] = 'E';
                        $_INS['end_reason_cd'] = array_flip($this->borrowEndReason)[$_INS['end_reason_cd']];

                        $_WHERE['cust_info_no'] = $_INS['cust_info_no'];
                        $_WHERE['loan_info_no'] = $_INS['loan_info_no'];
                        $_WHERE['borrow_comp_no'] = $chk_borrow->no;
                        $_WHERE['start_date'] = $_INS['start_date'];

                        $rslt = DB::dataProcess('UPD', 'borrow', $_INS, $_WHERE);
                        
                        $cnt++;

                        if($cnt%1000==0)
                        {
                            echo $cnt."\n";
                        }
                    }
                }
            }
            echo "전체 건수: ".$total_cnt;
        }
        else
        {
            echo "엑셀파일 미존재\n";
        }

        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
        $excel = Func::failExcelMake($colHeader, $ERROR, 'borrow');

        $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);
        $file_path = $file_arr[1]."/".$file_arr[2]."/".$file_arr[3];
        $file_name = $file_arr[4];
        }
    }

    public function validCheckLumpBorrow($value) 
    {
        $waring = "";
        $return = Array();

        // 외부문서참조함수 확인
        foreach($value as $key => $val) 
        {
            if (substr($val,0,1) === '=')
            {
                $waring = "엑셀 함수는 사용이 불가합니다.";
                $return["waring"] = $waring;
                $return["col"] = $key;
                return $return;
            }
        }
        
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

        if(!preg_match('/^[0-9]+$/', $value['cust_info_no']) || !preg_match('/^[0-9]+$/', $value['loan_info_no']))
        {
            $waring = "계약번호 또는 회원번호를 올바르게 입력바랍니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 계약확인
        $chk_loan =  DB::TABLE("loan_info")->SELECT("no")
                                            ->WHERE('no', $value['loan_info_no'])
                                            ->WHERE('cust_info_no', $value['cust_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(empty($chk_loan))
        {
            $waring = "A:계약번호 및 회원번호를 올바르게 입력바랍니다.";
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
            }
        }
    }

    // 채권담보관리일괄배치 해지_데이터 복원
    // php artisan Quick:actionhistory --flag=borrowBackup
    public function borrowBackup()
    {
        // 필수키
        $this->requirArray = Array('borrow_no' => 'borrow_no',
                                    'mng_no' => 'mng_no',
                                    'loan_info_no' => 'loan_info_no', 
                                    'cust_info_no' => 'cust_info_no', 
                                    'start_date' => 'start_date', 
                                    'end_date' => 'end_date', 
                                    'end_reason_cd' => 'end_reason_cd', 
                                    );

        // 해지사유
        $this->borrowEndReason = Func::getConfigArr('borrow_end_reason_cd');

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);

        $file = "";
        $ERROR = Array();

        $path = "test/borrowbackup_1.xlsx";

        $total_cnt = 0; 
        $i = 0;
        if(Storage::disk('public')->exists($path))
        {
            echo "파일 O\n";

            $file = Storage::path('/public/'.$path);

            $colHeader  = array('borrow_no', 'mng_no', 'loan_info_no', 'cust_info_no', 'start_date', 'end_date', 'end_reason_cd',);
            $colNm      = array(
            'borrow_no'       => '0',	    
            'mng_no'          => '1',	    
            'loan_info_no'    => '2',	    
            'cust_info_no'    => '3',	  
            'start_date'	  => '4',	   
            'end_date'	      => '5',	  
            'end_reason_cd'   => '6',    
            );

            $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

            // 엑셀 유효성 검사
            if(!isset($excelData))
            {
            echo "엑셀파일 헤더 불일치\n";
            }
            else
            {
                echo "상태 '진행중' 변경\n";
                foreach($excelData as $_DATA) 
                {
                    unset($_INS, $_UPD);
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
                        $_UPD['cust_info_no'] = str_replace('zb', '', $_INS['cust_info_no']);
                    }
                    else
                    {
                        $_UPD['cust_info_no'] = $_INS['cust_info_no'];
                    }
                    $_UPD['mng_no'] = $_INS['mng_no'];
                    $_UPD['loan_info_no'] = $_INS['loan_info_no'];
                    $_UPD['end_date']    = str_replace('-', '', $_INS['end_date']);
                    $_UPD['start_date']  = str_replace('-', '', $_INS['start_date']);
                    // $_UPD['save_time']   = date("YmdHis");
                    $_UPD['save_id']     = 'SYSTEM';
                    $_UPD['save_status'] = "Y";
                    $_UPD['status'] = 'S';
                    $_UPD['end_reason_cd'] = $_INS['end_reason_cd'];

                    $rslt = DB::dataProcess('UPD', 'borrow', $_UPD, ["no" => $_INS['borrow_no'], "borrow_comp_no" => "7"]);
                }
            }
        }
        else
        {
            echo "엑셀파일 미존재\n";
        }

        #################################
        # 실패 로그파일 생성
        #################################
        // 실패건이 있을시 결과파일 만든다.
        if(count($ERROR)>0)
        {      
        $excel = Func::failExcelMake($colHeader, $ERROR, 'borrow');

        $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);
        $file_path = $file_arr[1]."/".$file_arr[2]."/".$file_arr[3];
        $file_name = $file_arr[4];
        }
    }

    // 거래원장에 기한이익상실일 0인거 업데이트
    // php artisan Quick:actionhistory --flag=loanTradeKihanDate
    public function loanTradeKihanDate()
	{
        echo "start\n";
        $i = 0;

        // 휴일
        $this->holiday = Cache::remember('Loan_Holiday', 86400, function()
        {
            $rslt = DB::table("DAY_CONF")->select("*")->get();
            foreach( $rslt as $v )
            {
                $day           = str_replace("-","",$v->day);
                $holiday[$day] = $day;
            }
            return $holiday;
        });
        
        $loan_info_trade = DB::table('loan_info_trade')->select('*')
                                          ->where('kihan_date','0')
                                          ->orderBy("loan_info_no")
                                          ->orderBy("seq","desc")
                                          ->get();

        foreach ($loan_info_trade as $key => $val)
        {
            unset($_INS);
            $i++;
            if(!empty($val->return_date))
            {
                $_INS['kihan_date']     = $this->addMonth($val->return_date, 2);
                $_INS['kihan_date_biz'] = $this->getBizDay($_INS['kihan_date']);

                $rslt = DB::dataProcess("UPD", "loan_info_trade", $_INS, ["no" => $val->no]);

                if($rslt=='Y')
                {
                    log::channel('pl_work')->info("[test]업데이트 성공! 계약번호: ".$val->loan_info_no);
                }
                else
                {
                    log::channel('pl_work')->info("[test]=========실패! 계약번호: ".$val->loan_info_no);
                }
            }
        }
        echo "total_cnt: ".$i."건, end\n";
	}

    // day_conf 마이그레이션
    // php artisan Quick:actionhistory --flag=dayConfMig
    public function dayConfMig()
    {
        ini_set('memory_limit', '-1');
        
        // 로그 시간 찍기용
        $start_time = date("Y-m-d H:i:s");
        $record     = 0;
        
        echo "[start: ".$start_time."] day_conf 마이그레이션 시작\n";

        $dayList = DB::connection('mig_erp')->table('day_conf')
                                    ->select('day','type')
                                    ->where('day', '>=', '2002-01-01')
                                    ->where('day', '<=', '2002-12-31')
                                    ->orderBy('day','asc')
                                    ->get();

        foreach($dayList as $data)
        {
            unset($_DATA);
            $date = $data->day;
            $type = '';
            log::channel('pl_work')->info("day: ".$date.", ");

            $target_yoil = date('w', strtotime($date));
            log::channel('pl_work')->info($target_yoil."번째\n");

            // 토요일
            if($target_yoil == 6)
            {
                $type = '03';
            }
            // 일요일
            elseif($target_yoil == 0)
            {
                $type = '02';
            }
            // 공휴일(평일) - 기존에 공휴일처리했던 경우 
            else if($data->type == 'B')
            {
                $type = '04';
            }

            //평일이면 등록하지 않음
            if(empty($type)) continue; 

            $_DATA['day'] = $date;
            $_DATA['type'] = $type;
            $_DATA['save_status'] = 'Y';
            $_DATA['save_id'] = 'SYSTEM';
            $_DATA['save_time'] = date('YmdHis');
            $_DATA['reg_time'] = date('YmdHis');

            $rslt = DB::dataProcess('INS', 'day_conf', $_DATA);
            if($rslt == 'Y') log::channel('pl_work')->info("인서트 date: ".$_DATA['day'].", 타입: ".$_DATA['type']."\n");

            $record++;
            if($record%1000==0) echo $record."\n";
        }
        // 로그 시간 찍기용
        $end_time = date("Y-m-d H:i:s");
        echo "[start: ".$start_time." ~ end: ".$end_time."] day_conf 마이그레이션 끝\n";
    }

    // 영업일 반영 안된 매입건들의 영업일 업데이트
    // php artisan Quick:actionhistory --flag=updateDateBiz
    public function updateDateBiz()
	{
        echo "start\n";
        $cnt = $cnt2 = $cnt3 = $cnt4 = $cnt5 = $cnt6 = 0;
        
        // 1. loan_info의 return_date_biz와 kihan_date_biz 영업일 업데이트
        $loan_info_no_list = [];
        $loan_info = DB::table('loan_info')->select('no', 'return_date', 'kihan_date')
                                            ->whereIn('seller_no',['33', '34', '35'])
                                            ->where('save_status','Y')
                                            ->get();

        foreach ($loan_info as $key => $val)
        {
            $loan_info_no_list[] = $val->no;
            // echo "계약번호 배열".print_r($loan_info_no_list,1);
            unset($L_INS, $L_INS2);

            if (!empty($val->return_date))
            {
                $L_INS['return_date_biz'] = Func::getBizDay($val->return_date);
                $rslt1 = DB::dataProcess("UPD", "loan_info", $L_INS, ["no" => $val->no]);
                // $rslt1 = DB::table('loan_info')->where('no', $val->no)->update(['return_date_biz'=>$L_INS['return_date_biz']]);

                if($rslt1 != 'Y')
                {
                    log::channel('pl_work')->info("loan_info return_date_biz 업데이트 실패! 계약번호: ".$val->no."\n");
                }
                else 
                {
                    // echo "loan_info return_date_biz 업데이트 성공! 결과: ".$rslt1.", 계약번호: ".$val->no."\n";
                    $cnt++;
                }
            }

            if (!empty($val->kihan_date))
            {
                $L_INS2['kihan_date_biz'] = Func::getBizDay($val->kihan_date);
                $rslt2 = DB::dataProcess("UPD", "loan_info", $L_INS2, ["no" => $val->no]);
                // $rslt2 = DB::TABLE('loan_info')->WHERE('no', $val->no)->UPDATE(['kihan_date_biz'=>$L_INS2['kihan_date_biz']]);

                if($rslt2 != 'Y')
                {
                    log::channel('pl_work')->info("loan_info kihan_date_biz 업데이트 실패! 계약번호: ".$val->no."\n");
                }
                else 
                {
                    // echo "loan_info kihan_date_biz 업데이트 성공! 결과: ".$rslt2.", 계약번호: ".$val->no."\n";
                    $cnt2++;
                }
            }
        }
        echo "\n-------1. loan_info return_date_biz 업데이트 건수 : ".$cnt.", loan_info kihan_date_biz 업데이트 건수 : ".$cnt2."\n";

        // 2. loan_info_trade의 return_date_biz와 kihan_date_biz 영업일 업데이트
        $loan_info_trade = DB::table('loan_info_trade')->select('no', 'return_date', 'kihan_date')
                                                    ->whereIn('loan_info_no', $loan_info_no_list)
                                                    ->get();

        foreach ($loan_info_trade as $key => $val)
        {
            unset($LT_INS, $LT_INS2);

            // loan_info_no로 조회한 loan_info_trade를 no별로 영업일 업데이트
            if (!empty($val->return_date))
            {
                $LT_INS['return_date_biz'] = Func::getBizDay($val->return_date);
                $rslt3 = DB::dataProcess("UPD", "loan_info_trade", $LT_INS, ["no" => $val->no]);
                // $rslt3 = DB::TABLE('loan_info_trade')->WHERE('no', $val->no)->UPDATE(['return_date_biz'=>$LT_INS["return_date_biz"]]);
                if($rslt3 != 'Y')
                {
                    log::channel('pl_work')->info("loan_info_trade return_date_biz 업데이트 실패! 거래원장 번호: ".$val->no."\n");
                }
                else 
                {
                    $cnt3++;
                }
            }

            if (!empty($val->kihan_date))
            {
                $LT_INS2['kihan_date_biz'] = Func::getBizDay($val->kihan_date);
                $rslt4 = DB::dataProcess("UPD", "loan_info_trade", $LT_INS2, ["no" => $val->no]);
                // $rslt4 = DB::TABLE('loan_info_trade')->WHERE('no', $val->no)->UPDATE(['kihan_date_biz'=>$LT_INS2["kihan_date_biz"]]);
                if($rslt4 != 'Y')
                {
                    log::channel('pl_work')->info("loan_info_trade kihan_date_biz 업데이트 실패! 거래원장 번호: ".$val->no."\n");
                }
                else 
                {
                    $cnt4++;
                }
            }
        }
        echo "\n-------2. nloan_info_trade return_date_biz 업데이트 건수 : ".$cnt3.", loan_info_trade kihan_date_biz 업데이트 건수 : ".$cnt4."\n";
        
        // 3. loan_settle_plan 조회를 위한 loan_settle_no 조회
        $loan_settle_no_list = [];
        $loan_settle = DB::table('loan_settle')->select('no')
                                            ->whereIn('loan_info_no', $loan_info_no_list)
                                            ->get();

        foreach ($loan_settle as $key => $val)
        {
            $loan_settle_no_list[] = $val->no;
            $cnt5++;
        }
        echo "\n-------3. loan_settle_no_list 업데이트 건수 : ".$cnt5."\n";
        
        // 4. loan_settle_plan의 plan_date_biz 영업일 업데이트
        $loan_settle_plan = DB::table('loan_settle_plan')->select('loan_settle_no', 'seq', 'plan_date')
                                                        ->whereIn('loan_settle_no', $loan_settle_no_list)
                                                        ->get();
        foreach ($loan_settle_plan as $key => $val) 
        {
            unset($LS_INS);
            if (!empty($val->plan_date))
            {
                $LS_INS['plan_date_biz'] = Func::getBizDay($val->plan_date);

                // loan_settle_plan은 loan_settle과 1대다 관계이므로 loan_settle_no와 seq로 조건을 걸어 영업일 업데이트
                $rslt6 = DB::dataProcess("UPD", "loan_settle_plan", $LS_INS, ['loan_settle_no' => $val->loan_settle_no, 'seq' => $val->seq]);
                // $rslt6 = DB::TABLE('loan_settle_plan')->WHERE('loan_settle_no', $val->loan_settle_no)->WHERE('seq', $val->seq)->UPDATE(['plan_date_biz'=>$LS_INS["plan_date_biz"]]);

                if($rslt6 != 'Y')
                {
                    log::channel('pl_work')->info("loan_settle_plan plan_date_biz 업데이트 실패! 화해번호: ".$val->loan_settle_no.", 화해seq: ".$val->seq."\n");
                }
                else 
                {
                    $cnt6++;
                }
            }
        }
        echo "\n-------4. loan_settle_plan 업데이트 건수 : ".$cnt6."\n";
        echo "\nend\n";
	}

    // 1원송금 테스트1
    // php artisan Quick:actionhistory --flag=KsnetOutMoneyTEST1
    public function KsnetOutMoneyTEST1()
    {
        $ksnet = new Ksnet(date("Ymd"));
        
        log::channel('ksnet')->info("[테스트 계좌세팅시작]");
        $ksnet->setInBank('TEST1');
        log::channel('ksnet')->info("[테스트 계좌세팅완료]");

        log::channel('ksnet')->info("[업무개시 시작]");
        $ksnet->startCheck();
        log::channel('ksnet')->info("[업무개시 종료]");
        
        log::channel('ksnet')->info("[테스트 송금시작]");
        $ksnet->testTransferBanking();
        log::channel('ksnet')->info("[테스트 송금종료]");
    }

    // 1원송금 테스트2
    // php artisan Quick:actionhistory --flag=KsnetOutMoneyTEST2
    public function KsnetOutMoneyTEST2()
    {
        $ksnet = new Ksnet(date("Ymd"));
        
        log::channel('ksnet')->info("[테스트 계좌세팅시작]");
        $ksnet->setInBank('TEST2');
        log::channel('ksnet')->info("[테스트 계좌세팅완료]");

        log::channel('ksnet')->info("[업무개시 시작]");
        $ksnet->startCheck();
        log::channel('ksnet')->info("[업무개시 종료]");
        
        log::channel('ksnet')->info("[테스트 송금시작]");
        $ksnet->testTransferBanking();
        log::channel('ksnet')->info("[테스트 송금종료]");
    }
        
    // 수기입금처리
    // php artisan Quick:actionhistory --flag=insertMoney --opt=2001
    public function insertMoney($loan_info_no)
	{
        $loanInfo = DB::table("loan_info")->where("no", $loan_info_no)->where("save_status", 'Y')->first();
        $loanInfo = Func::chungDec(["loan_info"], $loanInfo);	// CHUNG DATABASE DECRYPT

        // 입금배열
        $array_insert = Array();
        $array_insert['action_mode']      = "INSERT";
        $array_insert['trade_type']       = "01";
        $array_insert['trade_path_cd']    = "1";
        $array_insert['cust_info_no']     = $loanInfo->cust_info_no;
        $array_insert['loan_usr_info_no'] = $loanInfo->loan_usr_info_no;
        $array_insert['loan_info_no']     = $loanInfo->no;
        $array_insert['trade_date']       = date("Ymd");
        
        // 이율
        $array_insert['invest_rate']      = $loanInfo->invest_rate;
        $array_insert['income_rate']      = $loanInfo->income_rate;
        $array_insert['local_rate']       = $loanInfo->local_rate;

        // 입금액
        $array_insert['trade_money']      = $loanInfo->return_origin + $loanInfo->return_interest;
        $array_insert['lose_money']       = 0;

        // 원천징수
        $array_insert['return_money']     = $loanInfo->return_money;
        $array_insert['withholding_tax']  = $loanInfo->withholding_tax;
        $array_insert['income_tax']       = $loanInfo->income_tax;
        $array_insert['local_tax']        = $loanInfo->local_tax;
        $array_insert['memo']             = "수익지급처리";
        
        // 계좌
        $array_insert['loan_bank_cd']     = $loanInfo->loan_bank_cd;
        $array_insert['loan_bank_ssn']    = $loanInfo->loan_bank_ssn;
        $array_insert['loan_bank_name']   = $loanInfo->loan_bank_name;
        $array_insert['cust_bank_cd']     = $loanInfo->cust_bank_cd;
        $array_insert['cust_bank_ssn']    = $loanInfo->cust_bank_ssn;
        $array_insert['cust_bank_name']   = $loanInfo->cust_bank_name;

        $trade = new Trade($loan_info_no);
        $loan_info_trade_no = $trade->tradeInInsert($array_insert, 'SYSTEM');

        // 정상 처리될 경우, loan_info_trade의 no가 응답, 오류인경우 오류 메세지 응답
        if( !is_numeric($loan_info_trade_no) )
        {
            DB::rollBack();
            $array_result['rs']  = "N";
            $array_result['msg'] = "송금금액 입금거래등록에 실패하였습니다.";

            echo print_r($array_result,1);
        }

        echo $loan_info_trade_no;
	}
    
    // 테스트용3
    public function checkPlan()
	{
        $LOAN = DB::TABLE("loan_info")->SELECT("no, return_interest, return_origin, return_money")
                                    ->WHERE('SAVE_STATUS','Y')
                                    ->WHERE('STATUS','A')
                                    ->GET();

        foreach($LOAN as $val)
        {         
            $chk = DB::TABLE("loan_info_return_plan")->SELECT("no, investor_no, inv_seq, plan_interest, plan_origin, plan_money")
                                    ->WHERE('SAVE_STATUS','Y')
                                    ->WHERE('loan_info_no', $val->no)
                                    ->WHERE('divide_flag', 'N')
                                    ->ORDERBY('seq')
                                    ->ORDERBY('plan_date')
                                    ->first();

            if(!empty($chk->no))
            {
                if($val->return_interest != $chk->plan_interest)
                {
                    echo $val->no." 1\n";
                }
                else if($val->return_origin != $chk->plan_origin)
                {
                    echo $val->no." 2\n";
                }
                else if($val->return_money != $chk->plan_money)
                {
                    echo $val->no." 3\n";
                }
            }
        }
	}

    // 테스트용
    public function devTest()
    {
    }
}