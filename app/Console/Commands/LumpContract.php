<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Vars;
use Func;
use Auth;
use Invest;
use Log;
use ExcelFunc;
use FastExcel;
use Excel;
use Image;
use FilFunc;
use ContractExec;
use Throwable;
use App\Chung\ExcelCustomExport;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Config\BatchController;

class LumpContract extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'LumpContract:ContractInsert {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '대출&투자자입력';

    private $requirArray;
    private $conInfo;
    private $arrConf;
    private $arrConfValue;
    private $contractDt;

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
        // 환경설정
        $this->arrConf = Func::getConfigArr();

        // 전화번호 키
        $this->phoneKeyArray = Array('ph1', 'ph2', 'ph3', 'ph4');

        // 배치 기록
        $stime = time();
        $batchLogNo = $this->startBatchLog($stime);

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);
        
        $file = "";
        $ERROR = $DATAERROR = Array();
        $arrayExcelData = [];

        // 메인쿼리
        $result = DB::TABLE("lump_master_log")->SELECT("*")->WHERE('division','USR')
                                                        ->WHERE('status','W')
                                                        ->ORDERBY('no', 'asc')
                                                        ->first();
        
        if(!empty($result))
        {
            try
            {
                // 배치 등록직원
                $reg_id = $result->reg_id;
                $today = date("Ymd");
                $save_time = date("YmdHis");
                $lump_No = $result->no;

                $path = $result->excute_file_path."/".$result->excute_file_name;

                $total_cnt = 0;
                if(Storage::disk('public')->exists($path))
                {
                    // 환경설정값을 value=>key 형태로 변형. 엑셀에서 한글로 값을 받는다.
                    $arrayTarget = array('bank_cd','path_cd','pro_cd','source_funds_cd','object_cd');
                    foreach($arrayTarget as $catCode)
                    {
                        foreach($this->arrConf[$catCode] as $key => $val)
                        {
                            $this->arrConfValue['array_'.$catCode][$val] = $key;
                        }
                    }

                    $file = Storage::path('/public/'.$path);

                    $requirArrays = array(
                        0 => array('contract_seq'=>'대출일련번호','name'=>'고객명','ssn'=>'주민등록번호','ph1'=>'전화번호1','zip1'=>'우편번호1','addr11'=>'주소1','addr12'=>'상세주소1'),
                        1 => array('contract_seq'=>'대출일련번호','path_cd'=>'접수경로','pro_cd'=>'상품구분','contract_date'=>'계약일','contract_end_date'=>'만기일','loan_term'=>'대출기간(개월)','contract_day'=>'약정일','loan_money'=>'대출금액','rate_date'=>'(1차)이자적용일','loan_rate'=>'(1차)정상이자','loan_delay_rate'=>'(1차)연체이자','product_name'=>'상품명','loan_bank_cd'=>'송금은행','loan_bank_ssn'=>'송금계좌번호','loan_bank_name'=>'송금 예금주명','invest_rate'=>'기본 투자 수익률','platform_fee_rate'=>'기본 플랫폼 수수료'),
                        2 => array('contract_seq'=>'대출일련번호','company_yn'=>'투자자구분','name'=>'투자자명','ssn'=>'주민등록번호','ph1'=>'전화번호1','zip1'=>'우편번호1','addr11'=>'주소1','addr12'=>'상세주소1','bank_cd'=>'입금은행','bank_ssn'=>'입금계좌번호','in_name'=>'입금 예금주명','trade_money'=>'투자금액','ratio_date'=>'(1차)수익률적용일','ratio'=>'(1차)수익률(%)','platform_fee_rate_date'=>'(1차)수수료율적용일','platform_fee_rate'=>'(1차)수수료율(%)')
                    );
                    $colHeaders = array(
                        0 => array('대출일련번호','고객명','대출자별칭','주민등록번호','전화번호1','전화번호2','전화번호3','우편번호1','주소1','상세주소1','우편번호2','주소2','상세주소2','직장명','직장전화','직장 우편번호','직장주소','직장 상세주소','사업자번호','이메일'),
                        1 => array('대출일련번호','그룹코드','하부그룹코드','접수경로','상품구분','계약일','만기일','대출기간(개월)','약정일','대출금액','(1차)이자적용일','(1차)정상이자','(1차)연체이자','(2차)이자적용일','(2차)정상이자','(2차)연체이자','(3차)이자적용일','(3차)정상이자','(3차)연체이자','상품명','송금은행','송금계좌번호','송금 예금주명','조기상환수수료율','기본 투자 수익률','기본 플랫폼 수수료','기본 상환주기','사모사채 상품명', '담보물우편번호', '담보물주소', '담보물상세주소'),
                        2 => array('대출일련번호','투자자구분','투자자명','투자자별칭','주민등록번호','전화번호1','전화번호2','우편번호1','주소1','상세주소1','입금은행','입금계좌번호','입금 예금주명','자금출처','거래목적','국적','거주국적','이메일','영문명(성)','영문명(이름)','투자금액','약정일(미입력시 상품의 약정일 기본 설정)','상환주기(미입력시 기본 1)','면세여부','(1차)수익률적용일','(1차)수익률(%)','(2차)수익률적용일','(2차)수익률(%)','(3차)수익률적용일','(3차)수익률(%)','(1차)수수료율적용일','(1차)수수료율(%)','(2차)수수료율적용일','(2차)수수료율(%)','(3차)수수료율적용일','(3차)수수료율(%)','사업자번호')
                    );
                    $colNms = array(
                        0 => array(
                            'contract_seq' => '0',
                            'name' => '1',
                            'relation' => '2',
                            'ssn' => '3',
                            'ph1' => '4',
                            'ph2' => '5',
                            'ph4' => '6',
                            'zip1' => '7',
                            'addr11' => '8',
                            'addr12' => '9',
                            'zip2' => '10',
                            'addr21' => '11',
                            'addr22' => '12',
                            'com_name' => '13',
                            'ph3' => '14',
                            'zip3' => '15',
                            'addr31' => '16',
                            'addr32' => '17',
                            'com_ssn' => '18',
                            'email' => '19'
                        ),
                        1 => array(
                            'contract_seq' => '0',
                            'group_cd' => '1',
                            'sub_group_cd' => '2',
                            'path_cd' => '3',
                            'pro_cd' => '4',
                            'contract_date' => '5',
                            'contract_end_date' => '6',
                            'loan_term' => '7',
                            'contract_day' => '8',
                            'loan_money' => '9',
                            'rate_date' => '10',
                            'loan_rate' => '11',
                            'loan_delay_rate' => '12',
                            'rate_date_2th' => '13',
                            'loan_rate_2th' => '14',
                            'loan_delay_rate_2th' => '15',
                            'rate_date_3th' => '16',
                            'loan_rate_3th' => '17',
                            'loan_delay_rate_3th' => '18',
                            'product_name' => '19',
                            'loan_bank_cd' => '20',
                            'loan_bank_ssn' => '21',
                            'loan_bank_name' => '22',
                            'return_fee_cd' => '23',
                            'invest_rate' => '24',
                            'platform_fee_rate' => '25',
                            'pay_term' => '26',
                            'add_product_name' => '27',
                            'dambo_zip' => '28',
                            'dambo_addr11' => '29',
                            'dambo_addr12' => '30'
                        ),
                        2 => array(
                            'contract_seq' => '0',
                            'company_yn' => '1',
                            'name' => '2',
                            'relation' => '3',
                            'ssn' => '4',
                            'ph1' => '5',
                            'ph2' => '6',
                            'zip1' => '7',
                            'addr11' => '8',
                            'addr12' => '9',
                            'bank_cd' => '10',
                            'bank_ssn' => '11',
                            'in_name' => '12',
                            'source_funds' => '13',
                            'object_cd '=> '14',
                            'nationality' => '15',
                            'nationality_residence' => '16',
                            'email' => '17',
                            'family_name' => '18', 
                            'given_name' => '19',
                            'trade_money' => '20',
                            'contract_day' => '21',
                            'pay_term' => '22',
                            'tax_free' => '23',
                            'ratio_date' => '24',
                            'ratio' => '25',
                            'ratio_date_2th' => '26',
                            'ratio_2th' => '27',
                            'ratio_date_3th' => '28',
                            'ratio_3th' => '29',
                            'platform_fee_rate_date' => '30',
                            'platform_fee_rate' => '31',
                            'platform_fee_rate_date_2th' => '32',
                            'platform_fee_rate_2th' => '33',
                            'platform_fee_rate_date_3th' => '34',
                            'platform_fee_rate_3th' => '35',
                            'com_ssn' => '36'
                        ),
                    );

                    // 엑셀 데이터 가공
                    foreach($colHeaders as $sheet => $colHeader)
                    {
                        // 필수키
                        $this->requirArray = $requirArrays[$sheet];

                        $colNm = $colNms[$sheet];

                        unset($excelData);
                        $excelData  = ExcelFunc::readExcel($file, $colNm, 0, $sheet, $colHeader,0);
                        
                        // 엑셀 유효성 검사
                        if(!isset($excelData))
                        {
                            // 엑셀파일 헤더 불일치
                            $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 헤더 불일치 오류','status'=>'X',], ['division'=>'USR', 'no'=>$result->no]);
                            exit;
                        }
                        else
                        {
                            $record = 2;
                            foreach($excelData as $_DATA) 
                            {
                                // 기본데이터(대출일련번호)가 없는경우는 무시한다. row 데이터를 지웠으나 엑셀상 row를 인식하는 경우를 제외하기 위함.
                                if(empty($_DATA['contract_seq'])) continue;

                                // 대출, 투자자 시트에서 대출일련번호 검증. 대출고객쪽은 첫번째 시트라 의미 없다.
                                if($sheet > 0)
                                {
                                    if(!isset($arrayExcelData[$_DATA['contract_seq']]))
                                    {
                                        $ERROR[] = $_DATA['contract_seq']."번 계약 등록 실패 - 대출일련번호 오류 [ ".$sheet."시트 > ".$record."행 ]\n";
                                        $record++;
                                        continue;
                                    }
                                }

                                $arrayCheck = Array();

                                // 데이터 정리
                                foreach($_DATA as $key => $val) 
                                {
                                    $val = trim($val);
                                    if($val=="-") $val = "";        // - 로 들어온 데이터는 없는 데이터로 인식
                                    
                                    // 코드형으로 변형 대상
                                    if($key=="path_cd") $val = $this->getMappingData($val, 'path_cd');
                                    if($key=="pro_cd") $val = $this->getMappingData($val, 'pro_cd');
                                    if($key=="loan_bank_cd" || $key=="bank_cd") $val = $this->getMappingData($val, 'bank_cd');
                                    if($key=="source_funds") $val = $this->getMappingData($val, 'source_funds_cd');
                                    if($key=="object_cd") $val = $this->getMappingData($val, 'object_cd');
                                    if($key=="nationality" || $key=="nationality_residence") $val = $this->getMappingData($val, 'country_cd');

                                    if($key=="company_yn") $val = ($val=="기업") ? "Y" : "N";
                                    $_DATA[$key] = $val;
                                }


                                $_DATA['save_id'] = $reg_id;
                                $_DATA['save_time'] = $save_time;
                                $_DATA['reg_time'] = $save_time;
                                $_DATA['save_status'] = 'Y';

                                // 데이터 추출 및 정리
                                foreach($_DATA as $key=>$val)
                                {
                                    // 값이 없으면 unset
                                    if($val == "")
                                    {
                                        unset($_DATA[$key]);
                                    }

                                    // 값이 null 이면 빈값으로 업데이트
                                    if($val == "null" || $val == "NULL")
                                    {
                                        $_DATA[$key] = '';
                                    }
                                }

                                $arrayCheck = $this->validCheck($_DATA);
                                if(isset($arrayCheck["waring"]))
                                {
                                    $DATAERROR[$_DATA['contract_seq']][] = $sheet."시트 > ".$record."행 > ".$arrayCheck["waring"];
                                    $record++;
                                    continue;
                                }

                                // 날짜형식은 - 를 지운다.
                                foreach($_DATA as $key=>$val)
                                {
                                    if(substr($key, -5)=='_date') $_DATA[$key] = str_replace("-","",$val);
                                }

                                // 계약 Sheet(시트 일련번호 1)의 계약일을 적재.
                                if($sheet=="0")
                                {
                                   if(!empty($_DATA['relation']))
                                   {
                                       $i_cnt = DB::TABLE('cust_info')->WHERE('relation', $_DATA['relation'])->WHERE('save_status', 'Y')->COUNT();
                                       if($i_cnt>0)
                                       {
                                           $DATAERROR[$_DATA['contract_seq']][] = $sheet."시트 > ".$record."행 > 별칭이 중복등록되었습니다. 확인해주세요";
                                           $record++;
                                           continue;
                                           
                                       }
                                   }
                                }
                                else if($sheet=="1")
                                {
                                    $_DATA['loan_money'] = str_replace(",", "", $_DATA['loan_money']);
                                    
                                    $this->contractDt[$_DATA['contract_seq']] = $_DATA['contract_date'];
                                }
                                // 투자 Sheet(시트 일련번호 2)는 (1차)수익률적용일과 (1차)수수료율적용일이 계약일과 일치하는지 확인.
                                else if($sheet=="2")
                                {
                                    if(!empty($_DATA['relation']))
                                    {
                                        $u_cnt = DB::TABLE('loan_usr_info')->WHERE('relation', $_DATA['relation'])->WHERE('save_status', 'Y')->COUNT();
                                        if($u_cnt>0)
                                        {
                                            $DATAERROR[$_DATA['contract_seq']][] = $sheet."시트 > ".$record."행 > 별칭이 중복등록되었습니다. 확인해주세요";
                                            $record++;
                                            continue;
                                            
                                        }
                                    }

                                    if(!empty($this->contractDt[$_DATA['contract_seq']]))
                                    {
                                        if($this->contractDt[$_DATA['contract_seq']]!=$_DATA['ratio_date'])
                                        {
                                            $DATAERROR[$_DATA['contract_seq']][] = $sheet."시트 > ".$record."행 > 수익률 적용일[".$_DATA['ratio_date']."]을 확인해주세요";
                                            $record++;
                                            continue;
                                        }
                                        if($this->contractDt[$_DATA['contract_seq']]!=$_DATA['platform_fee_rate_date'])
                                        {
                                            $DATAERROR[$_DATA['contract_seq']][] = $sheet."시트 > ".$record."행 > 수수료율 적용일[".$_DATA['ratio_date']."]을 확인해주세요";
                                            $record++;
                                            continue;
                                        }
                                    }
                                    else
                                    {
                                        $DATAERROR[$_DATA['contract_seq']][] = $sheet."시트 > ".$record."행 > 대출정보의 계약일이 올바르게 세팅되지않았습니다. 대출정보시트에 오류가없는지 확인해주세요";
                                        $record++;
                                        continue;
                                    }
                                   
                                }

                                // cust_info
                                if(isset($_DATA['ssn']))
                                {
                                    $_DATA['ssn'] = str_replace("-" ,"", $_DATA['ssn']);
                                }

                                // cust_info_extra, loan_usr_info
                                if(isset($_DATA['com_ssn']))
                                {
                                    $_DATA['com_ssn'] = str_replace("-" ,"", $_DATA['com_ssn']);
                                }

                                // cust_info_extra
                                if(isset($_DATA['ph1']))
                                {                               
                                    $arrPh = explode('-', $_DATA['ph1']);
                                    
                                    if( count($arrPh) == 3 )
                                    {
                                        $_DATA['ph11'] = $arrPh[0];
                                        $_DATA['ph12'] = $arrPh[1];
                                        $_DATA['ph13'] = $arrPh[2];
                                    }
                                    else
                                    {
                                        $_DATA['ph11'] = '';
                                        $_DATA['ph12'] = '';
                                        $_DATA['ph13'] = '';
                                    }
                                }

                                // cust_info_extra
                                if(isset($_DATA['ph2']))
                                {
                                    $arrPh = explode('-', $_DATA['ph2']);

                                    if( count($arrPh) == 3 )
                                    {
                                        $_DATA['ph21'] = $arrPh[0];
                                        $_DATA['ph22'] = $arrPh[1];
                                        $_DATA['ph23'] = $arrPh[2];
                                    }
                                    else
                                    {
                                        $_DATA['ph21'] = '';
                                        $_DATA['ph22'] = '';
                                        $_DATA['ph23'] = '';
                                    }
                                }

                                // cust_info_extra
                                if(isset($_DATA['ph3']))
                                {
                                    $arrPh = explode('-', $_DATA['ph3']);

                                    if( count($arrPh) == 3 )
                                    {
                                        $_DATA['ph31'] = $arrPh[0];
                                        $_DATA['ph32'] = $arrPh[1];
                                        $_DATA['ph33'] = $arrPh[2];
                                    }
                                    else
                                    {
                                        $_DATA['ph31'] = '';
                                        $_DATA['ph32'] = '';
                                        $_DATA['ph33'] = '';
                                    }
                                }

                                // cust_info_extra
                                if(isset($_DATA['ph4']))
                                {
                                    $arrPh = explode('-', $_DATA['ph4']);

                                    if( count($arrPh) == 3 )
                                    {
                                        $_DATA['ph41'] = $arrPh[0];
                                        $_DATA['ph42'] = $arrPh[1];
                                        $_DATA['ph43'] = $arrPh[2];
                                    }
                                    else
                                    {
                                        $_DATA['ph41'] = '';
                                        $_DATA['ph42'] = '';
                                        $_DATA['ph43'] = '';
                                    }
                                }

                                unset($_DATA['ph1'], $_DATA['ph2'], $_DATA['ph3'], $_DATA['ph4']);
                                $arrayExcelData[$_DATA['contract_seq']][$sheet][] = $_DATA;
                                $record++;
                            }
                        }
                    }


                    if(sizeof($arrayExcelData)<=0)
                    {
                        // 데이터 검증 결과도 ERROR로 찍는다.
                        if(sizeof($DATAERROR) > 0)
                        {
                            foreach($DATAERROR as $contract_seq => $arr)
                            {
                                $ERROR[] = $contract_seq."번 계약 등록 실패\n".implode("\n", $arr)."\n";
                            }
                        }

                        $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'필터링 후 엑셀처리대상 없음','status'=>'X',], ['division'=>'USR', 'no'=>$result->no]);
                    }
                    else
                    {
                        // 상태 '진행중' 변경
                        $rslt = DB::dataProcess("UPD", "lump_master_log", ['status'=>'P','start_time'=>time()], ['division'=>'USR', 'no'=>$result->no]);
                        
                        // 투자금액 검증
                        foreach($arrayExcelData as $contract_seq => $arraySheet)
                        {
                            if(isset($DATAERROR[$contract_seq]))
                            {
                                $ERROR[] = $contract_seq."번 계약 등록 실패\n".implode("\n", $DATAERROR[$contract_seq])."\n";
                                unset($arrayExcelData[$contract_seq]);
                                continue;
                            }

                            $loanMoney = $totInvMoney = 0;
                            foreach($arraySheet as $sheet => $arrayData)
                            {
                                foreach($arrayData as $seq => $_DATA)
                                {
                                    if($sheet==1) $loanMoney = $_DATA['loan_money'];
                                    if($sheet==2) $totInvMoney+=$_DATA['trade_money'];
                                }
                            }

                            // 대출금과 투자금 불일치
                            if($loanMoney!=$totInvMoney)
                            {
                                $ERROR[] = $contract_seq."번 계약 등록 실패\n대출금과 투자금합계 불일치[대출금 : ".number_format($loanMoney)." <> 투자금합계 : ".number_format($totInvMoney)."]\n";
                                unset($arrayExcelData[$contract_seq]);
                            }

                            $total_cnt++;
                        }
                        

                        // 데이터 입력
                        DB::beginTransaction();
                        foreach($arrayExcelData as $contract_seq => $arraySheet)
                        {
                            foreach($arraySheet as $sheet => $arrayData)
                            {
                                foreach($arrayData as $seq => $_DATA)
                                {
                                    // 고객정보 입력
                                    if($sheet==0)
                                    {

                                        // 기등록 고객정보 확인
                                        $rs = DB::table('cust_info')->select('no')->where('save_status','Y')->where('ssn',Func::encrypt($_DATA['ssn'], 'ENC_KEY_SOL'))->first();
                                        if(!empty($rs->no))
                                        {
                                            $_DATA['cust_info_no'] = $this->conInfo[$_DATA['contract_seq']]['custInfoNo'] = $rs->no;
                                            
                                            $rslt = DB::dataProcess('UPD', 'cust_info', $_DATA, ["no"=>$_DATA['cust_info_no']]);
                                            $rslt = DB::dataProcess('UPD', 'cust_info_extra', $_DATA, ["cust_info_no"=>$_DATA['cust_info_no']]);
                                        }
                                        else
                                        {
                                            $_DATA['reg_date'] = $today;        // 등록일자

                                            $rslt = DB::dataProcess('INS', 'cust_info', $_DATA, null, $cust_info_no);
                                            $_DATA['cust_info_no'] = $this->conInfo[$_DATA['contract_seq']]['custInfoNo'] = $cust_info_no;
                                            
                                            $rslt = DB::dataProcess('INS', 'cust_info_extra', $_DATA);
                                        }
                                    }
                                    // 계약정보 입력
                                    else if($sheet==1)
                                    {
                                        $_DATA['return_method_cd'] = 'M';               // 현재 만기일시 상품만 관리한다. 추후 다른 상환방식을 사용한다면 엑셀로 받는 방식으로 변경 필요.
                                        
                                        $_DATA['app_date'] = $_DATA['contract_date'];
                                        $_DATA['app_money'] = $_DATA['loan_money'];
                                        $_DATA['cust_info_no'] = $this->conInfo[$_DATA['contract_seq']]['custInfoNo'];
                                        $_DATA['save_status'] = 'Y';
                                        
                                        $loan = new ContractExec;
                                        $this->conInfo[$_DATA['contract_seq']]['loanInfoNo'] = $loan->loanAction($_DATA);

                                        $arrAddUpdate = [];
                                        $arrAddUpdate['product_name'] = $_DATA['product_name'];
                                        $arrAddUpdate['add_product_name'] = isset($_DATA['add_product_name']) ? $_DATA['add_product_name'] : "";
                                        $arrAddUpdate['invest_rate'] = $_DATA['invest_rate'];
                                        $arrAddUpdate['dambo_zip'] = isset($_DATA['dambo_zip']) ? $_DATA['dambo_zip'] : "";
                                        $arrAddUpdate['dambo_addr11'] = isset($_DATA['dambo_addr11']) ? $_DATA['dambo_addr11'] : "";
                                        $arrAddUpdate['dambo_addr12'] = isset($_DATA['dambo_addr12']) ? $_DATA['dambo_addr12'] : "";
                                        $arrAddUpdate['platform_fee_rate'] = $_DATA['platform_fee_rate'];
                                        $arrAddUpdate['loan_bank_cd'] = $_DATA['loan_bank_cd'];
                                        $arrAddUpdate['loan_bank_ssn'] = $_DATA['loan_bank_ssn'];
                                        $arrAddUpdate['loan_bank_name'] = $_DATA['loan_bank_name'];
                                        $arrAddUpdate['pay_term'] = (!empty($_DATA['pay_term'])) ? $_DATA['pay_term'] : 1;  // 기본은 1, 입력값이 있을경우는 해당 주기
                                        $rslt = DB::dataProcess('UPD', 'loan_info', $arrAddUpdate, ["no"=>$this->conInfo[$_DATA['contract_seq']]['loanInfoNo']]);

                                        $this->conInfo[$_DATA['contract_seq']]['contract_date'] = $_DATA['contract_date'];
                                        $this->conInfo[$_DATA['contract_seq']]['contract_end_date'] = $_DATA['contract_end_date'];
                                        $this->conInfo[$_DATA['contract_seq']]['contract_day'] = $_DATA['contract_day'];
                                        $this->conInfo[$_DATA['contract_seq']]['loan_money'] = $_DATA['loan_money'];
                                    }
                                    // 투자자정보 입력
                                    else if($sheet==2)
                                    {                   
                                        if(!empty($_DATA['bank_ssn']))
                                        {
                                            $_DATA['bank_ssn'] = str_replace("-", "", $_DATA['bank_ssn']);
                                        }

                                        // 기등록 투자자 정보 확인
                                        $rs = DB::table('loan_usr_info')->select('no')->where('save_status','Y')->where('ssn',Func::encrypt($_DATA['ssn'], 'ENC_KEY_SOL'))->first();
                                        if(!empty($rs->no))
                                        {
                                            $rslt = DB::dataProcess('UPD', 'loan_usr_info', $_DATA, ["no"=>$rs->no]);
                                            $loan_usr_info_no = $rs->no;
                                        }
                                        else
                                        {
                                            $rslt = DB::dataProcess('INS', 'loan_usr_info', $_DATA, null, $loan_usr_info_no);
                                        }

                                        unset($_INV);
                                        $_INV['loan_usr_info_no'] = $loan_usr_info_no;
                                        $_INV['loan_money'] = $this->conInfo[$_DATA['contract_seq']]['loan_money'];
                                        $_INV['trade_money'] = $_DATA['trade_money'];
                                        $_INV['trade_date'] = $this->conInfo[$_DATA['contract_seq']]['contract_date'];
                                        $_INV['platform_fee_rate'] = $_DATA['platform_fee_rate'];
                                        $_INV['inv_tail_money'] = $_DATA['trade_money'];
                                        $_INV['ratio'] = $_DATA['ratio'];
                                        $_INV['tax_free'] = isset($_DATA['tax_free']) ? $_DATA['tax_free'] : "N";
                                        $_INV['contract_date'] = $this->conInfo[$_DATA['contract_seq']]['contract_date'];
                                        $_INV['contract_end_date'] = $_INV['fullpay_date'] = $this->conInfo[$_DATA['contract_seq']]['contract_end_date'];
                                        $_INV['contract_day'] = (!empty($_DATA['contract_day'])) ? $_DATA['contract_day'] : $this->conInfo[$_DATA['contract_seq']]['contract_day'];
                                        $_INV['pay_term'] = (!empty($_DATA['pay_term'])) ? $_DATA['pay_term'] : 1;  // 기본은 1, 입력값이 있을경우는 해당 주기
                                        $_INV['status'] = 'E';              // 투자완료(E)
                                        $_INV['save_id'] = $_DATA['save_id'];
                                        $_INV['save_time'] = $_INV['reg_time'] = $_DATA['save_time'];
                                        $_INV['save_status'] = 'Y';
                                        $_INV['ph_chk'] = 'ph1';
                                        $_INV['addr_chk'] = 'addr1';
                                        $_INV['bank_chk'] = 'bank1';

                                        $rslt = DB::dataProcess('INS', 'loan_info', $_INV, null, $loan_info_no);
                                        $_INV['no'] = $loan_info_no;

                                        unset($arr, $arrAddratio);
                                        $arr['rate_date'] = $_DATA['ratio_date'];
                                        $arr['ratio'] = $_DATA['ratio'];
                                        $arrAddratio[] = $arr;

                                        // 1차 이자율
                                        $_INV['arrayRatio'][$arr['rate_date']]['req_date'] = $arr['rate_date'];
                                        $_INV['arrayRatio'][$arr['rate_date']]['req_value'] = $arr['ratio'];
                                        
                                        // 2차 수익률적용일이 있는 경우
                                        if(isset($_DATA['ratio_date_2th']) && !empty($_DATA['ratio_date_2th']))
                                        {
                                            unset($arr);
                                            $arr['rate_date'] = str_replace("-","",$_DATA['ratio_date_2th']);
                                            $arr['ratio'] = $_DATA['ratio_2th'];
                                            $arrAddratio[] = $arr;

                                            // 2차 이자율
                                            $_INV['arrayRatio'][$arr['rate_date']]['req_date'] = $arr['rate_date'];
                                            $_INV['arrayRatio'][$arr['rate_date']]['req_value'] = $arr['ratio'];
                                        }
                                        // 3차 수익률적용일이 있는 경우
                                        if(isset($_DATA['ratio_date_3th']) && !empty($_DATA['ratio_date_3th']))
                                        {
                                            unset($arr);
                                            $arr['rate_date'] = str_replace("-","",$_DATA['ratio_date_3th']);
                                            $arr['ratio'] = $_DATA['ratio_3th'];
                                            $arrAddratio[] = $arr;

                                            // 3차 이자율
                                            $_INV['arrayRatio'][$arr['rate_date']]['req_date'] = $arr['rate_date'];
                                            $_INV['arrayRatio'][$arr['rate_date']]['req_value'] = $arr['ratio'];
                                        }

                                        // 수익률적용
                                        if(isset($arrAddratio) && sizeof($arrAddratio)>0)
                                        {
                                            for($i=0;$i<sizeof($arrAddratio);$i++)
                                            {
                                                $valratio = [];
                                                $valratio['loan_info_no']          = $loan_info_no;
                                                $valratio['rate_date']       = str_replace("-","",$arrAddratio[$i]['rate_date']);
                                                $valratio['ratio']           = $arrAddratio[$i]['ratio'];
                                                $valratio['save_status']     = 'Y';
                                                $valratio['save_time']       = $_DATA['save_time'];
                                                $valratio['save_id']         = $_DATA['save_id'];
                                                
                                                $rslt = DB::dataProcess('INS', 'INV_RATIO', $valratio);
                                                if( $rslt!="Y" )
                                                {
                                                    DB::rollBack();
                                                    return "실행오류#1";
                                                }
                                            }
                                        }

                                        unset($arr, $arrAddFee);
                                        $arr['rate_date'] = $_DATA['platform_fee_rate_date'];
                                        $arr['platform_fee_rate'] = $_DATA['platform_fee_rate'];
                                        $arrAddFee[] = $arr;

                                        // 1차 수수료
                                        $_INV['arrayPlatformFeeRate'][$arr['rate_date']]['req_date'] = $arr['rate_date'];
                                        $_INV['arrayPlatformFeeRate'][$arr['rate_date']]['req_value'] = $arr['platform_fee_rate'];
                                        
                                        // 2차 플랫폼 수수료율 적용일이 있는 경우
                                        if(isset($_DATA['platform_fee_rate_date_2th']) && !empty($_DATA['platform_fee_rate_date_2th']))
                                        {
                                            unset($arr);
                                            $arr['rate_date'] = str_replace("-","",$_DATA['platform_fee_rate_date_2th']);
                                            $arr['platform_fee_rate'] = $_DATA['platform_fee_rate_2th'];
                                            $arrAddFee[] = $arr;

                                            // 2차 수수료
                                            $_INV['arrayPlatformFeeRate'][$arr['rate_date']]['req_date'] = $arr['rate_date'];
                                            $_INV['arrayPlatformFeeRate'][$arr['rate_date']]['req_value'] = $arr['platform_fee_rate'];
                                        }
                                        // 3차 플랫폼 수수료율 적용일이 있는 경우
                                        if(isset($_DATA['platform_fee_rate_date_3th']) && !empty($_DATA['platform_fee_rate_date_3th']))
                                        {
                                            unset($arr);
                                            $arr['rate_date'] = str_replace("-","",$_DATA['platform_fee_rate_date_3th']);
                                            $arr['platform_fee_rate'] = $_DATA['platform_fee_rate_3th'];
                                            $arrAddFee[] = $arr;

                                            // 3차 수수료
                                            $_INV['arrayPlatformFeeRate'][$arr['rate_date']]['req_date'] = $arr['rate_date'];
                                            $_INV['arrayPlatformFeeRate'][$arr['rate_date']]['req_value'] = $arr['platform_fee_rate'];
                                        }

                                        // 플랫폼 수수료율 적용
                                        if(isset($arrAddFee) && sizeof($arrAddFee)>0)
                                        {
                                            for($i=0;$i<sizeof($arrAddFee);$i++)
                                            {
                                                $valfee = [];
                                                $valfee['loan_info_no']          = $loan_info_no;
                                                $valfee['rate_date']       = str_replace("-","",$arrAddFee[$i]['rate_date']);
                                                $valfee['platform_fee_rate']           = $arrAddFee[$i]['platform_fee_rate'];
                                                $valfee['save_status']     = 'Y';
                                                $valfee['save_time']       = $_DATA['save_time'];
                                                $valfee['save_id']         = $_DATA['save_id'];
                                                
                                                $rslt = DB::dataProcess('INS', 'platform_fee_rate', $valfee);
                                                if( $rslt!="Y" )
                                                {
                                                    DB::rollBack();
                                                    return "실행오류#2";
                                                }
                                            }
                                        }


                                        $valmoney = [];
                                        $valmoney['loan_info_no']          = $loan_info_no;
                                        $valmoney['trade_date']      = $this->conInfo[$_DATA['contract_seq']]['contract_date'];
                                        $valmoney['inv_tail_money']  = $_DATA['trade_money'];
                                        $valmoney['save_status']     = 'Y';
                                        $valmoney['save_time']       = $_DATA['save_time'];
                                        $valmoney['save_id']         = $_DATA['save_id'];
                                        $rslt = DB::dataProcess('INS', 'INV_TAIL_MONEY', $valmoney);
                                        if( $rslt!="Y" )
                                        {
                                            DB::rollBack();
                                            return "실행오류#3";
                                        }

                                        // 분배예정스케줄 생성
                                        $lv = DB::TABLE('loan_info')->SELECT('pro_cd')->WHERE('no',$this->conInfo[$_DATA['contract_seq']]['loanInfoNo'])->FIRST();
                                        $_INV['pro_cd'] = $lv->pro_cd;     // 원천징수 세율을 가져오기 위한 상품코드 설정                                    
                                        $inv = new Invest($_INV); 
                                        $array_plan = $inv->buildPlanData($this->conInfo[$_DATA['contract_seq']]['contract_date']);
                                        $inv->savePlan($array_plan, $this->conInfo[$_DATA['contract_seq']]['contract_date']);
                                    }
                                }
                            }
                        }

                        // 오류건이 있으면 적용하지 않는다.
                        if(count($ERROR) > 0) DB::rollback();
                        else DB::commit();
                        
                        $rslt = DB::dataProcess('UPD', 'lump_master_log', ["status"=>"C","finish_time"=>time(),"total_count"=>$total_cnt, "ok_count"=>((($total_cnt)-count($ERROR) > 0)?($total_cnt)-count($ERROR):0), "fail_count"=>count($ERROR), "remark"=>count($ERROR)."건 실패"], ["no"=>$result->no]);
                    }
                }
                else
                {
                    $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'엑셀파일 읽기오류','status'=>'X',], ['division'=>'USR', 'no'=>$result->no]);
                }
            }
            catch (\Throwable $e)
            {
                $rslt = DB::dataProcess("UPD", "lump_master_log", ['remark'=>'시스템 오류발생-관리자에 문의요망','status'=>'X',], ['division'=>'USR', 'no'=>$result->no]);
    
                Log::channel('batch')->info($e->getFile()." [Line : ".$e->getLine()."] ".$e->getMessage());
                //echo $e;
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
            $file_path = "upload_file/fail_contract_insert";
            $file_name = "lump_fail_contract_insert_".date("YmdHis").".txt";	// 서버 저장파일명

            // 폴더가 없으면 생성
            if(!file_exists(Storage::path($file_path)))
            {
                umask(0);
                mkdir(Storage::path($file_path), "755", true);
            }
            
            Storage::disk('local')->put($file_path."/".$file_name, implode("\n", $ERROR));
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
            if(!isset($value[$chk_key]) || (!is_numeric($value[$chk_key]) && ($value[$chk_key] == '' || empty($value[$chk_key]))))
            {
                $waring = "필수값 체크 : ".$chk_val." 확인필요"; 
                $return["waring"] = $waring;
                return $return;
            }
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
                        $waring = "전화번호 형식[".$val."]을 확인해주세요";
                        $return["waring"] = $waring;
                        return $return;
                    }
                }   
            }

            if(substr($key, -5)=="_date" || in_array($key, ['rate_date_2th','rate_date_3th','ratio_date_2th','ratio_date_3th','platform_fee_rate_date_2th','platform_fee_rate_date_3th']))
            {
                $arrDt = explode("-",$val);
                $dateCheckVal = str_replace("-","",str_replace(".","",$val));

                if(
                    count($arrDt) == 3 && preg_match('/^(\d{4})-?(\d{2})-?(\d{2})$/', $dateCheckVal, $match)
                    && checkdate($match[2],$match[3],$match[1])
                )
                {
                    //return true;
                }
                else
                {
                    $waring = "날짜 형식[".$val."]을 확인해주세요";
                    $return["waring"] = $waring;
                    return $return;
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

    public function getMappingData($str, $catCode)
    {
        if(isset($this->arrConfValue['array_'.$catCode]))
        {
            return (isset($this->arrConfValue['array_'.$catCode][$str])) ? $this->arrConfValue['array_'.$catCode][$str] : '';
        }
        else
        {
            return '';
        }
    }
}
