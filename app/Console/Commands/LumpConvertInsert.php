<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Vars;
use Func;
use Auth;
use Loan;
use Trade;
use Log;
use ExcelFunc;
use FastExcel;
use Excel;
use Image;
use Carbon;
use FilFunc;
use Cache;
use Illuminate\Http\Request;
use App\Http\Controllers\Erp\SettleController;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Config\BatchController;

use function PHPUnit\Framework\isEmpty;

class LumpConvertInsert extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Lump:ConvertInsert { fileName? : 파일이름} {batchNo?}';
    
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '기관차입일괄등록';

    // 필수값 배열
    private $custRequirArray;
    private $loanRequirArray;
    private $planRequirArray;
    
    // 투자계약엑셀양식 날짜만
    private $loanDateArray;
    // 투자계약엑셀양식 숫자만
    private $loanNumberArray;

    // 스케줄엑셀양식 날짜만
    private $planDateArray;
    // 스케줄엑셀양식 숫자만
    private $planNumberArray;

    // 에러값 뱉을 시트
    private $sheet_title;

    // 휴일 체크
	private $holiday  = Array();

    // 코드관리
    private $proCodeArray;
    private $returnMethodArray;
    private $bankCodeArray;

    //
    private $phoneKeyArray;

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
        // 휴일 체크
		$this->holiday = Cache::remember('lump_convert_Holiday', 86400, function()
		{
			$rslt = DB::table("day_conf")->select("*")->get();
			foreach( $rslt as $v )
			{
				$day           = str_replace("-","",$v->day);
				$holiday[$day] = $day;
			}
			return $holiday;
		});

        // 1. 투자자정보
        $this->custRequirArray = Array(
            'convert_c_no'      => '임시 투자자번호',
            'name'              => '투자자명',
            'company_yn'        => '개인/기업',
            'ssn'               => '주민번호(법인번호)',
            'bank_cd'           => '은행',
            'bank_ssn'          => '계좌번호',
            'in_name'           => '예금주명'
        );
        // 2. 투자계약정보
        $this->loanRequirArray = Array(
            'convert_c_no'      => '임시 투자자번호',
            'convert_l_no'      => '임시 계약번호',
            'cust_info_no'      => '차입자번호',
            'contract_date'     => '투자일자',
            'contract_end_date' => '만기일자',
            'contract_day'      => '약정일',
            'loan_pay_term'     => '이자지급주기',
            'loan_money'        => '투자금액',
            'pro_cd'            => '상품명',
            'invest_rate'       => '투자이율',
            'income_rate'       => '소득세율',
            'local_rate'        => '지방소득세율',
            'loan_bank_cd'      => '은행',
            'loan_bank_ssn'     => '계좌번호',
            'loan_bank_name'    => '예금주명',
            'viewing_return_method'  => '상환방법'
        );
        // 3. 스케줄정보
        $this->planRequirArray = Array(
            'convert_c_no'      => '임시 투자자번호',
            'convert_l_no'      => '임시 계약번호',
            'seq'               => '회차',
            'plan_date'         => '수익지급일',
            'plan_origin'       => '상환원금',
            'plan_interest'     => '상환이자',
            'divide_flag'       => '입금여부'
        );

        // 투자계약정보 날짜 형식체크
        $this->loanDateArray = Array(
            'contract_date',
            'contract_end_date'
        );
        // 투자계약정보 숫자만
        $this->loanNumberArray = Array(
            'contract_date',
            'contract_end_date',
            'contract_day',
            'loan_pay_term',
            'loan_money'
        );
        // 스케줄정보 날짜 형식체크
        $this->planDateArray = Array(
            'plan_date'
        );
        // 스케줄정보 숫자만
        $this->planNumberArray = Array(
            'seq',
            'plan_date',
            'plan_origin',
            'plan_interest'
        );
        
        // 시트 타이틀
        $this->sheet_title = Array(
            '1. 투자자정보',
            '2. 투자계약정보',
            '3. 스케줄정보',
        );

        // 전화번호 키
        $this->phoneKeyArray = Array('ph1', 'ph2', 'ph3', 'ph4');

        // 코드관리
        $this->proCodeArray       = Func::getConfigArr('pro_cd');
        $this->returnMethodArray  = Func::getConfigArr('viewing_return_method');
        $this->bankCodeArray      = Func::getConfigArr('bank_cd');
        
        // 파일
        $file = "";
        $ERROR = Array();

        // 엑셀 헤더 변수
        $colHeader        = array();
        $colNm            = array();
        $excelData        = array();

        // 배치 기록
        $stime = time();
        $batchLogNo = $this->startBatchLog($stime);

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        // 메인쿼리
        // $result = DB::TABLE("lump_master_log")->SELECT("*")->WHERE('division','CONVERT')
        //                                                     ->WHERE('status','W')
        //                                                     ->ORDERBY('no', 'asc')
        //                                                     ->first();
        
        // if(!empty($result))
        // {
            // $path = $result->excute_file_path."/".$result->excute_file_name;
        $excel_file_name = $this->argument('fileName');
        $path = "test/".$excel_file_name;

        $cust_total_cnt = 0; 
        $loan_total_cnt = 0; 
        $plan_total_cnt = 0;

        $total_cnt = 0; 
        $error_cnt = 0; 

        $a = $b = $c = 0;

        $save_id     = 'SYSTEM';
        $save_time   = date("YmdHis");
        $save_status = 'Y';
        $handle_code = "";
        if ($excel_file_name == "test_tech.xlsx") {
            $handle_code = "1";
        } else if ($excel_file_name == "test_yumi.xlsx") {
            $handle_code = "3";
        }

        // if(Storage::disk('lumplog')->exists($path))
        if(Storage::disk('public')->exists($path))
        {
            log::channel('lump')->info("[START]----------------------------------------------------------");
            log::channel('lump')->info("파일 O");

            $file = Storage::path('/public/'.$path);
            // $file = Storage::path('/lumplog/'.$path);

            $convertHeader = Array(
                // 1. 투자자정보 엑셀양식 헤더 
                '0'=> Array(
                    '임시 투자자번호', "투자자명", '개인/기업', '주민번호(법인번호)', '관계',
                    '전화번호1', '전화번호2', '전화번호3', '이메일', '사업자번호',
                    '은행', '계좌번호','예금주명', '은행2','계좌번호2','예금주명2', '은행3','계좌번호3','예금주명3', '은행4','계좌번호4','예금주명4',
                    '주소1(우편번호)', '주소1(주소)','주소1(상세주소)','주소2(우편번호)', '주소2(주소)', '주소2(상세주소)', '특이사항'
                ),
                // 2. 투자계약정보 엑셀양식 헤더
                '1'=> Array(
                    '임시 투자자번호', "임시 계약번호","차입자번호", '투자일자', '만기일자',
                    "약정일", '이자지급주기', '투자금액', '상품명', '상환방법',
                    '투자이율','소득세율', '지방소득세율', '은행','계좌번호', '예금주명', '특이사항'
                ),
                // 3. 스케줄정보 엑셀양식 헤더
                '2'=> Array(
                    '임시 투자자번호', "임시 계약번호", "회차", "수익지급일", '상환원금', '상환이자', '입금여부'
                )
            );

            $convertCol = Array(
                // 1. 투자자정보 엑셀양식 컬럼
                '0'=> Array(
                    'convert_c_no'          => '0',	    // 임시 투자자번호
                    'name'	                => '1',	    // 투자자명
                    'company_yn'	        => '2',	    // 개인/기업
                    'ssn'                   => '3',	    // 주민번호\n(법인번호)
                    'relation'              => '4',     // 관계
                    'ph1'	                => '5',	    // 전화번호1
                    'ph2'                   => '6',     // 전화번호2
                    'ph3'	                => '7',	    // 전화번호3
                    'email'                 => '8',     // 이메일
                    'com_ssn'	            => '9',	    // 사업자번호
                    'bank_cd'               => '10',    // 은행
                    'bank_ssn'              => '11',	// 계좌번호
                    'in_name'               => '12',	// 예금주명
                    'bank_cd2'              => '13',    // 은행2
                    'bank_ssn2'	            => '14',	// 계좌번호2
                    'in_name2'              => '15',	// 예금주명2
                    'bank_cd3'              => '16',    // 은행3
                    'bank_ssn3'	            => '17',	// 계좌번호3
                    'in_name3'              => '18',	// 예금주명3
                    'bank_cd4'              => '19',    // 은행4
                    'bank_ssn4'	            => '20',	// 계좌번호4
                    'in_name4'              => '21',	// 예금주명4
                    'zip1'                  => '22',    // 주소1(우편번호)
                    'addr11'	            => '23',	// 주소1(주소)
                    'addr12'                => '24',    // 주소1(상세주소)
                    'zip2'  	            => '25',	// 주소2(우편번호)
                    'addr21'                => '26',    // 주소2(주소)
                    'addr22'                => '27',	// 주소2(상세주소)
                    'memo'	                => '28',	// 특이사항
                ),
                // 2. 투자계약정보 엑셀양식 컬럼
                '1'=> Array(
                    'convert_c_no'          => '0',	    // 임시 투자자번호
                    'convert_l_no'	        => '1',	    // 임시 계약번호
                    'cust_info_no'	        => '2',	    // 차입자번호
                    'contract_date'         => '3',	    // 투자일자
                    'contract_end_date'     => '4',     // 만기일자
                    'contract_day'	        => '5',	    // 약정일
                    'loan_pay_term'         => '6',     // 이자지급주기
                    'loan_money'	        => '7',	    // 투자금액
                    'pro_cd'                => '8',     // 상품명
                    'viewing_return_method' => '9',	    // 상환방법
                    'invest_rate'           => '10',    // 투자이율
                    'income_rate'           => '11',	// 소득세율
                    'local_rate'            => '12',    // 지방소득세율
                    'loan_bank_cd'          => '13',    // 은행
                    'loan_bank_ssn'         => '14',	// 계좌번호
                    'loan_bank_name'        => '15',    // 예금주명
                    'loan_memo'             => '16',	// 특이사항
                ),
                // 3. 스케줄정보 엑셀양식 컬럼
                '2'=> Array(
                    'convert_c_no'          => '0',	    // 임시 투자자번호
                    'convert_l_no'	        => '1',	    // 임시 계약번호
                    'seq'	                => '2',	    // 회차
                    'plan_date'	            => '3',	    // 수익지급일
                    "plan_origin"           => "4",     // 상환원금
                    "plan_interest"         => "5",     // 상환이자
                    "divide_flag"           => "6",     // 입금여부
                )
            );

            if(count($convertHeader) != count($convertCol))
            {
                // 배열 불일치
                log::channel('lump')->info("소스 헤더 배열 불일치 오류");
                // $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'소스 헤더 배열 불일치 오류','status'=>'X',], ['division'=>'CONVERT', 'no'=>$result->no]);
            }
            else
            {
                $excelHeaderCheck = 'Y';

                for ($i=0; $i<count($convertHeader); $i++)
                {
                    $excelData[$i] = ExcelFunc::readExcel($file, $convertCol[$i], 0, $i, $convertHeader[$i],0);

                    if(!isset($excelData[$i]))
                    {
                        $excelHeaderCheck = 'N';
                    }
                }

                // 엑셀 유효성 검사
                if($excelHeaderCheck == 'N')
                {
                    // 엑셀파일 헤더 불일치
                    log::channel('lump')->info("엑셀파일 헤더 배열 불일치 오류");
                    // $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 헤더 불일치 오류','status'=>'X',], ['division'=>'CONVERT', 'no'=>$result->no]);
                }
                else
                {
                    // 상태 '진행중' 변경
                    log::channel('lump')->info("엑셀파일 헤더 일치. 진행중");
                    // $rslt = DB::dataProcess("UPD", "lump_master_log", ['status'=>'P','start_time'=>time()], ['division'=>'CONVERT', 'no'=>$result->no]);

                    // 빈 로우 삭제
                    foreach ($excelData as $sheet => &$data) {
                        foreach ($data as $index => $row) {
                            $isEmpty = true;
                            foreach ($row as $value) {
                                if (!empty($value)) {
                                    $isEmpty = false;
                                    break;
                                }
                            }

                            if ($isEmpty) {
                                unset($data[$index]);
                                continue;
                            }
                        }
                    }

                    $excel_cnt = 0;

                    // 1. 투자자정보 엑셀 처리
                    foreach($excelData[$excel_cnt] as $_DATA) 
                    {
                        $cust_total_cnt++;

                        unset($custArrayCheck, $_INS);

                        $custArrayCheck = Array();



                        // 데이터 정리
                        foreach($_DATA as $key => $val) 
                        {
                            $val = trim($val);
                            $_INS[$key] = $val;
                        }

                        $_INS['handle_code'] = $handle_code;
                        $custArrayCheck = $this->custCheck($_INS);
                        if(isset($custArrayCheck["waring"]))
                        {
                            $_INS['err_msg'] = $custArrayCheck["waring"];
                            if (isset($custArrayCheck['col']))
                            {
                                $_INS[$custArrayCheck['col']] = 'ERROR';
                            }
                            $ERROR[$excel_cnt][$a] = $_INS;
                            $a++;
                            
                            continue;
                        }

                        // 데이터 추출 및 정리
                        foreach($_INS as $key => $val)
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

                        $_INS['ssn'] = preg_replace('/[^0-9]/', '', $_INS['ssn']);

                        // company_yn
                        if(!empty($_INS['company_yn']) && $_INS['company_yn'] == '법인')
                        {
                            $_INS['company_yn'] = 'Y';
                        }
                        else
                        {
                            $_INS['company_yn'] = 'N';
                        }

                        if(!empty($_INS['bank_cd']))
                        {
                            $_INS['bank_cd'] = Func::getArrayName(array_flip($this->bankCodeArray), $_INS['bank_cd']);
                        }
                        if(!empty($_INS['bank_cd2']))
                        {
                            $_INS['bank_cd2'] = Func::getArrayName(array_flip($this->bankCodeArray), $_INS['bank_cd2']);
                        }
                        if(!empty($_INS['bank_cd3']))
                        {
                            $_INS['bank_cd3'] = Func::getArrayName(array_flip($this->bankCodeArray), $_INS['bank_cd3']);
                        }
                        if(!empty($_INS['bank_cd4']))
                        {
                            $_INS['bank_cd4'] = Func::getArrayName(array_flip($this->bankCodeArray), $_INS['bank_cd4']);
                        }

                        $_INS['tax_free'] = 'N';

                        $_INS['save_id']     = $save_id;
                        $_INS['save_time']   = $save_time;
                        $_INS['save_status'] = $save_status;
                        $_INS['handle_code'] = $handle_code;

                        // cust_info_extra
                        if(!empty($_INS['ph1']))
                        {                               
                            $_INS['ph1'] = explode('-', $_INS['ph1']);
                            
                            if( count($_INS['ph1']) == 3 )
                            {
                                $_INS['ph11'] = $_INS['ph1'][0];
                                $_INS['ph12'] = $_INS['ph1'][1];
                                $_INS['ph13'] = $_INS['ph1'][2];
                            }
                            else
                            {
                                $_INS['ph11'] = '';
                                $_INS['ph12'] = '';
                                $_INS['ph13'] = '';
                            }
                        }

                        // cust_info_extra
                        if(!empty($_INS['ph2']))
                        {
                            $_INS['ph2'] = explode('-', $_INS['ph2']);

                            if( count($_INS['ph2']) == 3 )
                            {
                                $_INS['ph21'] = $_INS['ph2'][0];
                                $_INS['ph22'] = $_INS['ph2'][1];
                                $_INS['ph23'] = $_INS['ph2'][2];
                            }
                            else
                            {
                                $_INS['ph21'] = '';
                                $_INS['ph22'] = '';
                                $_INS['ph23'] = '';
                            }
                        }

                        // cust_info_extra
                        if(isset($_INS['ph3']))
                        {
                            $_INS['ph3'] = explode('-', $_INS['ph3']);

                            if( count($_INS['ph3']) == 3 )
                            {
                                $_INS['ph31'] = $_INS['ph3'][0];
                                $_INS['ph32'] = $_INS['ph3'][1];
                                $_INS['ph33'] = $_INS['ph3'][2];
                            }
                            else
                            {
                                $_INS['ph31'] = '';
                                $_INS['ph32'] = '';
                                $_INS['ph33'] = '';
                            }
                        }

                        unset($_INS['ph1'], $_INS['ph2'], $_INS['ph3'], $max_ino);

                        $max_ino = DB::table('loan_usr_info')->select('max(investor_no) as inv_no')->first();
                        $_INS['investor_no'] = ( $max_ino->inv_no ) ? $max_ino->inv_no + 1 : 1 ;

                        $rslt = DB::dataProcess('INS', 'loan_usr_info', $_INS);
                    }

                    $excel_cnt++;
                    log::channel('lump')->info("채무자 등록 종료");

                    // 2. 투자계약정보 엑셀 처리
                    foreach($excelData[$excel_cnt] as $_DATA) 
                    {
                        $loan_total_cnt++;

                        unset(
                            $loanArrayCheck,
                            $_INS,
                            $LOAN,
                            $_LOAN_TRADE,
                            $_LOAN_RATE,
                            $_LOAN_CDAY,
                            $_LOAN_INVEST,
                            $loan_info_no,
                            $loan_info_trade_no
                        );

                        $loanArrayCheck = Array();

                        // 데이터 정리
                        foreach($_DATA as $key => $val) 
                        {
                            $val = trim($val);
                            $_INS[$key] = $val;
                        }

                        $_INS['handle_code'] = $handle_code;
                        $loanArrayCheck = $this->loanCheck($_INS);
                        if(isset($loanArrayCheck["waring"]))
                        {
                            $_INS['err_msg'] = $loanArrayCheck["waring"];
                            if (isset($loanArrayCheck['col']))
                            {
                                $_INS[$loanArrayCheck['col']] = 'ERROR';
                            }
                            $ERROR[$excel_cnt][$b] = $_INS;
                            $b++;
                            
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
                            
                            // 숫자
                            if(in_array($key, $this->loanNumberArray))
                            {
                                if(!empty($_INS[$key]))
                                {
                                    $_INS[$key] = preg_replace('/[^0-9]/', '', $_INS[$key]);
                                }
                                
                                continue;
                            }
                        }

                        // 은행 코드
                        if(!empty($_INS['loan_bank_cd']))
                        {
                            $_INS['loan_bank_cd'] = Func::getArrayName(array_flip($this->bankCodeArray), $_INS['loan_bank_cd']);
                        }

                        // 상품명
                        if(!empty($_INS['pro_cd']))
                        {
                            $_INS['pro_cd'] = Func::getArrayName(array_flip($this->proCodeArray), $_INS['pro_cd']);
                        }

                        // 이자상환방법
                        if(!empty($_INS['viewing_return_method']))
                        {
                            $_INS['return_method_cd'] = 'F';
                            $_INS['viewing_return_method'] = Func::getArrayName(array_flip($this->returnMethodArray), $_INS['viewing_return_method']);
                        }

                        // 투자자정보 추출
                        $chk_usr =  DB::table("loan_usr_info")->select('*')->where('handle_code', $_INS['handle_code'])->where('convert_c_no', $_INS['convert_c_no'])->where('save_status','Y')->first();
                        $chk_usr = Func::chungDec(["loan_usr_info"], $chk_usr);	// CHUNG DATABASE DECRYPT

                        // 차입자 정보 추출
                        $chk_cust =  DB::table("cust_info")->select('*')->where('no', $_INS['cust_info_no'])->where('save_status','Y')->first();
                        $chk_cust = Func::chungDec(["cust_info"], $chk_cust);	// CHUNG DATABASE DECRYPT
                        

                        // 차입자 상세정보 추출
                        $chk_extra =  DB::table("cust_info_extra")->select('*')->where('cust_info_no', $_INS['cust_info_no'])->first();
                        $chk_extra = Func::chungDec(["cust_info_extra"], $chk_extra);	// CHUNG DATABASE DECRYPT


                        // 계약등록
                        $LOAN['cust_info_no']          = $chk_cust->no;
                        $LOAN['cust_bank_name']        = $chk_cust->name;
                        $LOAN['cust_bank_cd']          = $chk_extra->bank_cd;
                        $LOAN['cust_bank_ssn']         = $chk_extra->bank_ssn;
                        $LOAN['loan_usr_info_no']      = $chk_usr->no;
                        $LOAN['investor_no']           = $chk_usr->investor_no;
                        $LOAN['convert_c_no']          = $chk_usr->convert_c_no;
                        $LOAN['convert_l_no']          = $_INS['convert_l_no'];
                        $LOAN['loan_bank_name']        = $_INS['loan_bank_name'];
                        $LOAN['loan_bank_nick']        = $_INS['loan_bank_name'];
                        $LOAN['loan_bank_cd']          = $_INS['loan_bank_cd'];
                        $LOAN['loan_bank_ssn']         = $_INS['loan_bank_ssn'];
                        $LOAN['loan_bank_status']      = 'N';
                        $LOAN['handle_code']           = $_INS['handle_code'];
                        $LOAN['contract_date']         = $_INS['contract_date'];
                        $LOAN['contract_end_date']     = $_INS['contract_end_date'];
                        $LOAN['contract_day']          = $_INS['contract_day'];
                        $LOAN['loan_money']            = $_INS['loan_money'];
                        $LOAN['pro_cd']                = $_INS['pro_cd'];
                        $LOAN['viewing_return_method'] = $_INS['viewing_return_method'];
                        $LOAN['return_method_cd']      = $_INS['return_method_cd'];
                        $LOAN['loan_pay_term']         = $_INS['loan_pay_term'];
                        $LOAN['invest_rate']           = $_INS['invest_rate'];
                        $LOAN['income_rate']           = $_INS['income_rate'];
                        $LOAN['local_rate']            = $_INS['local_rate'];
                        $LOAN['loan_memo']             = $_INS['loan_memo'] ?? '';

                        $LOAN['loan_date']             = $LOAN['take_date'] = $LOAN['app_date'] = $LOAN['contract_date'];
                        $LOAN['invest_rate']           = $LOAN['loan_rate'] = $LOAN['loan_delay_rate'] = sprintf('%0.2f', $LOAN['invest_rate']);
                        $LOAN['balance']               = $LOAN['platform_fee_rate'] = 0;
                        $LOAN['legal_rate']            = Vars::$curMaxRate;
                        $LOAN['app_money']             = $LOAN['total_loan_money'] = $LOAN['first_loan_money'] = $LOAN['loan_money'];
                        $LOAN['monthly_return_money']  = 0;
                        $LOAN['loan_type']             = '01';
                        $LOAN['pay_term']              = $LOAN['loan_pay_term'];

                        $date1 = Carbon::parse($LOAN['contract_date']);
                        $date2 = Carbon::parse($LOAN['contract_end_date']);
                        $LOAN['loan_term'] = $date1->diffInMonths($date2);

                        $LOAN['monthly_return_gubun']  = "";
                        $LOAN['status']      		   = "N";
                        $LOAN['save_status'] 		   = $save_status;
                        $LOAN['save_id']     		   = $save_id;
                        $LOAN['save_time']   		   = $save_time;
                        $LOAN['handle_code']           = $handle_code;
                        $LOAN['take_date']			   = $LOAN['loan_date'];
                        $LOAN['loan_pay_term'] 		   = $LOAN['pay_term'] = isset($LOAN['loan_pay_term']) ? $LOAN['loan_pay_term'] : '1';	// 차주 이자납입주기
                        
                        $uv = DB::table('loan_usr_info')->select('tax_free')->where('no',$LOAN['loan_usr_info_no'])->where("save_status", 'Y')->first();
                        $LOAN['tax_free'] = !empty($uv->tax_free) ? $uv->tax_free : "N";

                        $vl = DB::table("LOAN_INFO")->select("MAX(LOAN_SEQ) as seq")->where("cust_info_no", $LOAN['cust_info_no'])->where("save_status", 'Y')->first();
                        $LOAN['loan_seq'] = ( $vl->seq ) ? $vl->seq + 1 : 1 ;

                        $vu = DB::table("LOAN_INFO")->select("MAX(INV_SEQ) as seq")->where("loan_usr_info_no", $LOAN['loan_usr_info_no'])->where("save_status", 'Y')->first();
                        $LOAN['inv_seq'] = ( $vu->seq ) ? $vu->seq + 1 : 1 ;

                        $rslt = DB::dataProcess('INS', 'loan_info', $LOAN, null, $loan_info_no);

                        $_LOAN_RATE['loan_info_no']                 = $loan_info_no;
                        $_LOAN_RATE['rate_date']                    = $LOAN['contract_date'];
                        $_LOAN_RATE['loan_rate']                    = $LOAN['loan_rate'];
                        $_LOAN_RATE['loan_delay_rate']              = $LOAN['loan_delay_rate'];
                        $_LOAN_RATE['save_time']                    = date("YmdHis");
                        $_LOAN_RATE['save_id']                      = $save_id;
                        $_LOAN_RATE['save_status']                  = $save_status;
                        $_LOAN_RATE['reg_time']                     = date("YmdHis");

                        $rslt5 = DB::dataProcess('UST', 'loan_info_rate', $_LOAN_RATE, ['loan_info_no'=>$_LOAN_RATE['loan_info_no'], 'rate_date'=>$_LOAN_RATE['rate_date']]);

                        $_LOAN_CDAY['loan_info_no']                 = $loan_info_no;
                        $_LOAN_CDAY['cday_date']                    = $LOAN['contract_date'];
                        $_LOAN_CDAY['contract_day']                 = $LOAN['contract_day'];
                        $_LOAN_CDAY['save_time']                    = date("YmdHis");
                        $_LOAN_CDAY['save_id']                      = $save_id;
                        $_LOAN_CDAY['save_status']                  = $save_status;
                        $_LOAN_CDAY['reg_time']                     = date("YmdHis");

                        $rslt6 = DB::dataProcess('UST', 'loan_info_cday', $_LOAN_CDAY, ['loan_info_no'=>$_LOAN_CDAY['loan_info_no'], 'cday_date'=>$_LOAN_CDAY['cday_date']]);

                        $_LOAN_INVEST['loan_info_no']                 = $loan_info_no;
                        $_LOAN_INVEST['rate_date']                    = $LOAN['contract_date'];
                        $_LOAN_INVEST['invest_rate']                  = $LOAN['invest_rate'];
                        $_LOAN_INVEST['save_time']                    = date("YmdHis");
                        $_LOAN_INVEST['save_id']                      = $save_id;
                        $_LOAN_INVEST['save_status']                  = $save_status;
                        $_LOAN_INVEST['reg_time']                     = date("YmdHis");

                        $rslt7 = DB::dataProcess('UST', 'loan_info_invest_rate', $_LOAN_INVEST, ['loan_info_no'=>$_LOAN_INVEST['loan_info_no'], 'rate_date'=>$_LOAN_INVEST['rate_date']]);
                    }

                    $excel_cnt++;
                    log::channel('lump')->info("계약 등록 종료");

                    // 3. 스케줄정보 엑셀 처리
                    $trade_out_insert = Array();
                    $trade_in_insert = Array();
                    $sum_interest = 0;
                    foreach($excelData[$excel_cnt] as $_DATA) 
                    {
                        $plan_total_cnt++;

                        $planArrayCheck = Array();
                        $_PLAN = Array();
                        $_INS = Array();

                        // 데이터 정리
                        foreach($_DATA as $key => $val) 
                        {
                            $val = trim($val);
                            $_INS[$key] = $val;
                        }

                        $_INS['handle_code'] = $handle_code;
                        $planArrayCheck = $this->planCheck($_INS);

                        if(isset($planArrayCheck["waring"]))
                        {
                            $_INS['err_msg'] = $planArrayCheck["waring"];
                            if (isset($planArrayCheck['col']))
                            {
                                $_INS[$planArrayCheck['col']] = 'ERROR';
                            }
                            $ERROR[$excel_cnt][$c] = $_INS;
                            $c++;
                            
                            continue;
                        }

                        // 데이터 추출 및 정리
                        foreach($_INS as $key => $val)
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
                            
                            // 숫자
                            if(in_array($key, $this->planNumberArray))
                            {
                                if(!empty($_INS[$key]))
                                {
                                    $_INS[$key] = preg_replace('/[^0-9]/', '', $_INS[$key]);
                                }
                                
                                continue;
                            }
                        }

                        // 계약번호 추출
                        $chk_loan =  DB::TABLE("loan_info")->SELECT('*')
                                                                ->WHERE('convert_c_no', $_INS['convert_c_no'])
                                                                ->WHERE('convert_l_no', $_INS['convert_l_no'])
                                                                ->WHERE('handle_code', $_INS['handle_code'])
                                                                ->WHERE('save_status','Y')
                                                                ->first();
                        $chk_loan = Func::chungDec(["loan_info"], $chk_loan);	                        // CHUNG DATABASE DECRYPT

                        if(empty($old_loan_info_no) || (!empty($old_loan_info_no) && $old_loan_info_no != $chk_loan->no))
                        {
                            $main_money = $chk_loan->loan_money;
                        }
                        
                        $_PLAN['seq']                   = $_INS['seq'];
                        $_PLAN['plan_date']             = preg_replace('/[^0-9]/', '', $_INS['plan_date']);
                        $_PLAN['plan_date_biz']         = Func::getBizDay($_PLAN['plan_date']);

                        $_PLAN['plan_origin']           = $_INS['plan_origin'] ?? 0;
                        $_PLAN['plan_interest']         = $_INS['plan_interest'] ?? 0;
                        $_PLAN['divide_flag']           = $_INS['divide_flag'];
                        
                        $_PLAN['invest_rate']           = $chk_loan->invest_rate;
                        $_PLAN['income_rate']	        = $chk_loan->income_rate;
                        $_PLAN['local_rate']	        = $chk_loan->local_rate;

                        // 기관차입은 원천징수가 얼마인지를 모르겠음 일단 0
                        $_PLAN['income_tax']            = floor( $_PLAN['plan_interest'] * ($_PLAN['income_rate'] / 100) / 10 ) * 10;	        // 소득세 : TRUNC(세전이자금액*원천징수세율/100/10,0)*10
                        $_PLAN['local_tax']             = floor( $_PLAN['income_tax'] * ($_PLAN['local_rate'] / 100) / 10 ) * 10;				// 지방소득세 : TRUNC( 소득세*0.01,0)*10
                        $_PLAN['withholding_tax']       = $_PLAN['income_tax'] + $_PLAN['local_tax'];
                        
                        $_PLAN['plan_interest_real']    = $_PLAN['plan_interest'] - $_PLAN['withholding_tax'];
                        $_PLAN['plan_money']            = $_PLAN['plan_origin'] + $_PLAN['plan_interest_real'];

                        $_PLAN['loan_money']            = $chk_loan->loan_money;
                        $main_money                     -= $_PLAN['plan_origin'];
                        $_PLAN['plan_balance']          = $main_money;
                        
                        $_PLAN['plan_interest_term']  	= 0;

                        $_PLAN['plan_interest_sdate']	= $chk_loan->contract_date;
                        $_PLAN['plan_interest_edate'] 	= $chk_loan->contract_end_date;
                        
                        $_PLAN['platform_fee_rate']     = 0;
                        $_PLAN['platform_fee']          = 0;
                        
                        $_PLAN['loan_info_no']          = $chk_loan->no;
                        $_PLAN['loan_usr_info_no']      = $chk_loan->loan_usr_info_no;
                        $_PLAN['investor_no']           = $chk_loan->investor_no;
                        $_PLAN['inv_seq']               = $chk_loan->inv_seq;

                        $_PLAN['handle_code']           = $chk_loan->handle_code;
                        $_PLAN['pro_cd']                = $chk_loan->pro_cd;
            
                        $_PLAN['cust_bank_ssn']         = $chk_loan->cust_bank_ssn;
            
                        $_PLAN['loan_bank_cd']          = $chk_loan->loan_bank_cd;
                        $_PLAN['loan_bank_ssn']         = $chk_loan->loan_bank_ssn;
                        $_PLAN['loan_bank_name']        = $_PLAN['loan_bank_owner'] = $chk_loan->loan_bank_name;

                        $_PLAN['save_status']           = $save_status;
                        $_PLAN['save_time']             = $save_time;
                        $_PLAN['save_id']               = $save_id;
                        $_PLAN['handle_code']           = $handle_code;

                        $rslt = DB::dataProcess('INS', 'loan_info_return_plan', $_PLAN);

                        $old_loan_info_no = $_PLAN['loan_info_no'];

                        // 스케줄 회차가 1일 때, 출금 거래원장(차입금) insert를 위한 배열 생성
                        if ($_PLAN['seq'] == 1) {
                            $_LOAN_TRADE_OUT = Array();
                            $_LOAN_TRADE_OUT['trade_type']       = '11';    // 차입금
                            $_LOAN_TRADE_OUT['trade_date']       = $chk_loan->contract_date;
                            $_LOAN_TRADE_OUT['trade_money']      = $chk_loan->loan_money;
                            $_LOAN_TRADE_OUT['loan_usr_info_no'] = $chk_loan->loan_usr_info_no;
                            $_LOAN_TRADE_OUT['cust_info_no']     = $chk_loan->cust_info_no;
                            $_LOAN_TRADE_OUT['loan_info_no']     = $chk_loan->no;
                            $_LOAN_TRADE_OUT['trade_fee']        = 0;

                            $trade_out_insert[$chk_loan->no] = $_LOAN_TRADE_OUT;
                        }

                        // 스케줄에서 입금 여부가 Y일 때, 입금 거래원장 insert를 위한 배열 생성
                        if ($_INS['divide_flag'] == 'Y') {
                            $_LOAN_TRADE_IN = Array();
                            $_LOAN_TRADE_IN['action_mode']      = "INSERT";
                            $_LOAN_TRADE_IN['trade_type']       = "01";
                            $_LOAN_TRADE_IN['trade_path_cd']    = "1";
                            $_LOAN_TRADE_IN['loan_usr_info_no'] = $chk_loan->loan_usr_info_no;
                            $_LOAN_TRADE_IN['cust_info_no']     = $chk_loan->cust_info_no;
                            $_LOAN_TRADE_IN['loan_info_no']     = $chk_loan->no;
                            $_LOAN_TRADE_IN['trade_date']       = $_PLAN['plan_date'];

                            $_LOAN_TRADE_IN['invest_rate']      = $_PLAN['invest_rate'];   
                            $_LOAN_TRADE_IN['income_rate']      = $_PLAN['income_rate'];
                            $_LOAN_TRADE_IN['local_rate']       = $_PLAN['local_rate'];	

                            $_LOAN_TRADE_IN['trade_money']      = $_PLAN['plan_money'];
                            $_LOAN_TRADE_IN['lose_money']       = 0;

                            $_LOAN_TRADE_IN['return_money']     = $chk_loan->return_money;
                            $_LOAN_TRADE_IN['withholding_tax']  = $_PLAN['withholding_tax'];
                            $_LOAN_TRADE_IN['income_tax']       = $_PLAN['income_tax'];
                            $_LOAN_TRADE_IN['local_tax']        = $_PLAN['local_tax'];
                            $_LOAN_TRADE_IN['memo']             = "수익지급처리";
                            
                            $_LOAN_TRADE_IN['loan_bank_cd']     = $chk_loan->loan_bank_cd;
                            $_LOAN_TRADE_IN['loan_bank_ssn']    = $chk_loan->loan_bank_ssn;
                            $_LOAN_TRADE_IN['loan_bank_name']   = $chk_loan->loan_bank_name;
                            $_LOAN_TRADE_IN['cust_bank_cd']     = $chk_loan->cust_bank_cd;
                            $_LOAN_TRADE_IN['cust_bank_ssn']    = $chk_loan->cust_bank_ssn;
                            $_LOAN_TRADE_IN['cust_bank_name']   = $chk_loan->cust_bank_name;

                            $trade_in_insert[$chk_loan->no][$_PLAN['seq']] = $_LOAN_TRADE_IN;
                        }
                    }

                    // 3-1. 출금 거래원장 등록
                    echo "출금 거래원장 등록";
                    foreach ($trade_out_insert as $loan_info_no => $trade_info) {
                        $t = new Trade($loan_info_no);
                        $loan_info_trade_no = $t->tradeOutInsert($trade_info, $save_id);
                        echo "... ";
                    }
                    
                    // 3-2. 입금 거래원장 등록
                    echo "입금 거래원장 등록";
                    foreach ($trade_in_insert as $loan_info_no => $trade_seq) {
                        $sum_interest = DB::table("loan_info_return_plan")->select(DB::RAW("coalesce(sum(plan_interest),0) as sum_plan_interest"))
                                            ->where("loan_info_no", $loan_info_no)
                                            ->where("save_status", "Y")->first()->sum_plan_interest;
                        DB::dataProcess("UPD", "loan_info", ['sum_interest'=>$sum_interest], ['no'=>$loan_info_no]);            

                        foreach ($trade_seq as $trade_info) {
                            $t = new Trade($loan_info_no);
                            $loan_info_trade_no = $t->tradeInInsert($trade_info, $save_id);
                            echo "... ";
                        }
                    }
                    
                    log::channel('lump')->info("스케줄정보 등록 종료");

                    $total_cnt = $cust_total_cnt + $loan_total_cnt + $plan_total_cnt; 
                    log::channel('lump')->info("cust_total_cnt : ".$cust_total_cnt);
                    log::channel('lump')->info("loan_total_cnt : ".$loan_total_cnt);
                    log::channel('lump')->info("plan_total_cnt : ".$plan_total_cnt);

                    log::channel('lump')->info($ERROR);

                    for ($r=0; $r <= $excel_cnt; $r++)
                    {
                        if(!empty($ERROR[$r]))
                        {
                            $error_cnt += count($ERROR[$r]);
                        }
                    }

                    $ok_count = $total_cnt - $error_cnt;

                    log::channel('lump')->info("전체건수 : ".$total_cnt);
                    log::channel('lump')->info("성공건수 : ".$ok_count);
                    log::channel('lump')->info("실패건수 : ".$error_cnt);
                    
                    // $rslt = DB::dataProcess('UPD', 'lump_master_log', ["status"=>"C","finish_time"=>time(),"total_count"=>$total_cnt, "ok_count"=>$ok_count, "fail_count"=>$error_cnt, "remark"=>$error_cnt."건 실패"], ['division'=>'CONVERT', "no"=>$result->no]);
                }
            }

            #################################
            # 실패 로그파일 생성
            #################################
            // 실패건이 있을시 결과파일 만든다.
            if($error_cnt>0)
            {    
                $excel = Func::failExcelSheetsMake($colHeader, $ERROR, 'convert', $i, $this->sheet_title);
    
                $file_arr = (array) explode('/', $excel['filepath'].$excel['filename']);
                $file_path = $file_arr[1]."/".$file_arr[2]."/".$file_arr[3];
                $file_name = $file_arr[4];
    
                log::channel('lump')->info("실패건 파일생성완료");
                echo "실패건 파일생성완료\n";

                // $rslt = DB::dataProcess("UPD", "lump_master_log", ['fail_file_path'=>$file_path,'fail_file_name'=>$file_name], ['division'=>'CONVERT', 'no'=>$result->no]);
            }

            log::channel('lump')->info("기관차입일괄처리 완료");
        }
        else
        {
            log::channel('lump')->info("엑셀파일 미존재");

            // $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 읽기오류','status'=>'X',], ['division'=>'CONVERT', 'no'=>$result->no]);
        }
        // }
        // else
        // {
        //     echo "대기중인 매입일괄업데이트 미존재\n";
        // }
        
        //배치 종료 기록
        if($batchLogNo>0)
        {
            $note = '';
            BatchController::setBatchLog($this->argument('batchNo'), $batchLogNo, $note, $stime);
        }
    }

    public function custCheck($value) 
    {
        $waring = "";
        $return = Array();

        unset($ph1_explode, $ph2_explode, $ph3_explode);
        
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
        foreach($this->custRequirArray as $chk_key => $chk_val)
        {
            if($value[$chk_key] == '' || !isset($value[$chk_key]))
            {
                $waring = "필수값 체크 : ".$chk_val." 확인필요"; 
                $return["waring"] = $waring;
                return $return;
            }
        } 

        // 투자자존재 확인
        $chk_cust1 =  DB::TABLE("loan_usr_info")->SELECT("*")
                                            ->WHERE('convert_c_no', $value['convert_c_no'])
                                            ->WHERE('handle_code', $value['handle_code'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(!empty($chk_cust1))
        {
            $waring = "투자자가 등록되어 있습니다.";
            $return["waring"] = $waring;
            return $return;
        }
        
        $value['ssn'] = preg_replace('/[^0-9]/', '', $value['ssn']);

        if(strlen($value['ssn']) != '10' && strlen($value['ssn']) != '13')
        {
            $waring = "주민등록번호 또는 법인번호를 올바르게 입력해주세요.";
            $return["waring"] = $waring;
            return $return;
        }

        // 고객확인
        $chk_cust2 =  DB::TABLE("loan_usr_info")->SELECT("*")
                                            ->where('handle_code', $value['handle_code'])
                                            ->WHERE('name', Func::encrypt($value['name'], 'ENC_KEY_SOL'))
                                            ->WHERE('ssn',  Func::encrypt($value['ssn'], 'ENC_KEY_SOL'))
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(!empty($chk_cust2))
        {
            $rslt = DB::dataProcess("UPD", "loan_usr_info", ['convert_c_no'=>$value['convert_c_no'],], ['no'=>$chk_cust2->no]);
            
            $waring = "존재하는 고객정보 입니다.";
            $return["waring"] = $waring;
            return $return;
        }

        if($value['company_yn'] != '개인' && $value['company_yn'] != '기업')
        {
            $waring = "개인 또는 기업으로 올바르게 작성해주세요";
            $return["waring"] = $waring;
            return $return;
        }
        
        foreach($value as $key => $val)
        {
            // 전화번호형식 확인
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

        if(!empty($value['email']))
        {
            $check_email = preg_match("/^[_\.0-9a-zA-Z-]+@([0-9a-zA-Z][0-9a-zA-Z-]+\.)+[a-zA-Z]/i", $value['email']);
            
            if($check_email==false)
            {
                $waring = "이메일 형식을 확인해주세요";
                $return["waring"] = $waring;
                return $return;
            }
        }

        if(!empty($value['bank_cd']))
        {
            // 은행 확인
            if(!in_array($value['bank_cd'], $this->bankCodeArray)) 
            {
                $waring = "은행 입력값을 확인해주세요.";
                $return["waring"] = $waring;
                return $return;
            }
        }
    }

    public function loanCheck($value) 
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
        foreach($this->loanRequirArray as $chk_key => $chk_val)
        {
            if($value[$chk_key] == '' || !isset($value[$chk_key]))
            {
                $waring = "필수값 체크 : ".$chk_val." 확인필요"; 
                $return["waring"] = $waring;
                return $return;
            }
        }

        // 투자자 확인
        $chk_cust =  DB::TABLE("loan_usr_info")->SELECT("*")
                                            ->WHERE('handle_code', $value['handle_code'])
                                            ->WHERE('convert_c_no', $value['convert_c_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(empty($chk_cust))
        {
            $waring = "해당 계약의 투자자가 존재하지않습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 차입자 확인
        $chk_cust2 =  DB::TABLE("cust_info")->SELECT("*")
                                            ->WHERE('no', $value['cust_info_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(empty($chk_cust2))
        {
            $waring = "해당 계약의 차입자가 존재하지않습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 계약존재 확인
        $chk_loan =  DB::TABLE("loan_info")->SELECT("*")
                                            ->WHERE('convert_c_no', $value['convert_c_no'])
                                            ->WHERE('convert_l_no', $value['convert_l_no'])
                                            ->WHERE('handle_code', $value['handle_code'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(!empty($chk_loan))
        {
            $waring = "계약이 등록되어 있습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        if(!empty($value['pro_cd']))
        {
            // 상품명 확인
            if(!in_array($value['pro_cd'], $this->proCodeArray)) 
            {
                $waring = "상품명 입력값을 확인해주세요.";
                $return["waring"] = $waring;
                return $return;
            }
        }

        if(!empty($value['viewing_return_method']))
        {
            // 상환방식 확인
            if(!in_array($value['viewing_return_method'], $this->returnMethodArray)) 
            {
                $waring = "상환방식 입력값을 확인해주세요.";
                $return["waring"] = $waring;
                return $return;
            }
        }

        // 날짜형식 확인
        foreach($value as $key => $val)
        {
            if(in_array($key, $this->loanDateArray))
            {
                if(!empty($val))
                {
                    $val = date('Ymd', strtotime(preg_replace('/[^0-9]/', '', $val)));

                    if($val == '19700101' || !checkdate(substr($val, 4, 2), substr($val, 6, 2), substr($val, 0, 4)))
                    {
                        $waring = "[데이터오류] 날짜형식";
                        $return["waring"] = $waring;
                        return $return;
                    }
                }   
            }
        }
    }

    public function planCheck($value) 
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
        foreach($this->planRequirArray as $chk_key => $chk_val)
        {
            if($value[$chk_key] == '' || !isset($value[$chk_key]))
            {
                $waring = "필수값 체크 : ".$chk_val." 확인필요"; 
                $return["waring"] = $waring;
                return $return;
            }
        }

        // 투자자 확인
        $chk_cust =  DB::TABLE("loan_usr_info")->SELECT("*")
                                            ->WHERE('handle_code', $value['handle_code'])
                                            ->WHERE('convert_c_no', $value['convert_c_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(empty($chk_cust))
        {
            $waring = "해당 계약의 투자자가 존재하지않습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 계약존재 확인
        $chk_loan =  DB::TABLE("loan_info")->SELECT("*")
                                            ->WHERE('handle_code', $value['handle_code'])
                                            ->WHERE('convert_c_no', $value['convert_c_no'])
                                            ->WHERE('convert_l_no', $value['convert_l_no'])
                                            ->WHERE('save_status','Y')
                                            ->first();
        if(empty($chk_loan))
        {
            $waring = "해당 계약이 존재하지않습니다.";
            $return["waring"] = $waring;
            return $return;
        }

        // 날짜형식 확인
        foreach($value as $key => $val)
        {
            if(in_array($key, $this->planDateArray))
            {
                if(!empty($val))
                {
                    $val = date('Ymd', strtotime(preg_replace('/[^0-9]/', '', $val)));

                    if($val == '19700101' || !checkdate(substr($val, 4, 2), substr($val, 6, 2), substr($val, 0, 4)))
                    {
                        $waring = "[데이터오류] 날짜형식";
                        $return["waring"] = $waring;
                        return $return;
                    }
                }   
            }
        }
    }

	public function getBizDay($today)
	{
		while( in_array($today, $this->holiday) )
		{
			$today = $this->addDay($today);
		}
		return $today;
	}

	/**
	* 일수 증가
	*
	* @param  Date  - 기준일자 YYYYMMDD
	* @param  Int   - 증가일수 (기본값 1)
	* @return Date  - 증가된일자 YYYYMMDD
	*/
	public function addDay($today, $cnt=1)
	{
		return date("Ymd", (Loan::dateToUnixtime($today) + (86400 * $cnt)));
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
