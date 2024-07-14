<?php
namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;
use App\Chung\Paging;
use App\Chung\Vars;
use Illuminate\Support\Facades\Storage;
use DataList;
use Illuminate\Support\Facades\Response;
use ExcelFunc;
use Carbon;
use Loan;
use Trade;
use Invest;
use App\Chung\Sms;

// php Spreadsheet 라이브러리
##################################################
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
##################################################

class InvestmentController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

    }

    /**
     * 직원관리 입력폼 (ajax부분화면)
     * 투자명세 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataInvestmentList(Request $request)
    {
        $list   = new DataList(Array("listName"=>"investment","listAction"=>'/'.$request->path()));

        if(!isset($request->tabs)) $request->tabs = 'ALL';

        $list->setTabs(Vars::$arrayInvStaTab, $request->tabs);

        $list->setSearchDate('날짜검색',Array('contract_date' => '투자개시일', 'contract_end_date' => '만기일자', 'fullpay_date' => '완제일자','return_date' => '차기수익지급일자',),'searchDt','Y');

        $list->setButtonArray("엑셀다운","excelDownModal('/account/investmentexcel','form_investment')","btn-success");

        $list->setCheckBox("no");
        $list->setSearchType('loan_info-handle_code',Func::getConfigArr('mo_acct_div'),'법인 구분', '', '', '', '', 'Y', '', true);
        $list->setSearchType('pro_cd',Func::getConfigArr('pro_cd'),'상품구분', '', '', '', '', 'Y', '', true);
        $list->setSearchType('pay_term', Func::getConfigArr('pay_term'),'수익지급주기', '', '', '', '', 'Y', '');
        $list->setSearchType('contract_day', Func::getConfigArr('contract_day'),'약정일', '', '', '', '', 'Y', '');
        $list->setRangeSearchDetail(Array ('loan_money'=>'투자금액', 'balance'=>'투자잔액'),'','','단위(일)');
        
        $list->setPlusButton("investmentForm('');");

        $list->setSearchDetail(Array(
            'loan_usr_info.nick_name'   => '투자자명',
            'loan_usr_info.investor_no' => '투자자번호',
            'investor_no-inv_seq'       => '채권번호',
        ));

        return $list;
    }

    public function investment(Request $request)
    {
        $list   = $this->setDataInvestmentList($request);

        $list->setLumpForm('DEL', Array('BTN_NAME'=>'삭제','BTN_ACTION'=>'lump_del(this)','BTN_ICON'=>'','BTN_COLOR'=>''));

        $list->setlistTitleCommon(Array
        (
            'investor_no_inv_seq'       => Array('채권번호', 0, '', 'center', '', ''),
            'cust_bank_name'            => Array('차입자명', 1, '', 'center', '', 'cust_bank_name'),
            'name'                      => Array('투자자명', 1, '', 'center', '', 'ENC-name'),
            'pro_cd'                    => Array('상품구분', 1, '', 'center', '', 'pro_cd'),
            'status'                    => Array('진행상태', 1, '', 'center', '', 'status'),
            'contract_date'             => Array('투자일자', 1, '', 'center', '', 'contract_date'),
            'contract_end_date'         => Array('만기일자', 1, '', 'center', '', 'contract_end_date'),
            'loan_term'                 => Array('투자개월', 0, '', 'center', '', 'loan_term'),
            'loan_money'                => Array('투자금액', 1, '', 'center', '', 'loan_money'),
            'return_origin_sum'         => Array('투자원금상환액', 1, '4%', 'center', '', 'return_origin_sum'),
            'balance'                   => Array('투자잔액', 1, '', 'center', '', 'balance'),
            'invest_rate'               => Array('수익률(%)', 1, '', 'center', '', 'invest_rate'),
            'pay_term'                  => Array('수익지급주기', 1, '', 'center', '', 'pay_term'),
            'contract_day'              => Array('약정일', 1, '4%', 'center', '', 'contract_day'),
            'return_date'               => Array('차기수익지급<br>일자', 1, '', 'center', '', 'return_date'),
            'return_money'              => Array('차기수익지급<br>금액', 1, '', 'center', '', 'return_money'),
            'save_id'                   => Array('작업자', 0, '', 'center', '', 'save_id', ['save_time'=>['저장시간', 'save_time', '<br>']]),
        ));
        
        return view('account.investment')->with('result', $list->getList());
    }   
    
    /**
     * 투자계약관리 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investmentList(Request $request)
    { 
        $list  = $this->setDataInvestmentList($request);

        $param = $request->all();

        // Tab count 
		if($request->isFirst=='1')
		{
            $BOXC = DB::table("loan_info")->join("loan_usr_info", "loan_info.loan_usr_info_no", "=", "loan_usr_info.no")
                                          ->join("cust_info", "loan_info.cust_info_no", "=", "cust_info.no")
                                          ->join("cust_info_extra", "cust_info.no", "=", "cust_info_extra.cust_info_no");
            $BOXC->SELECT(DB::RAW("
                                coalesce(sum(case when loan_usr_info.save_status='Y' and cust_info.save_status='Y' and loan_info.save_status='Y' and loan_info.status in ('N', 'A', 'E') then 1 else 0 end),0) as ALL
                                , coalesce(sum(case when loan_usr_info.save_status='Y' and cust_info.save_status='Y' and loan_info.save_status='Y' and loan_info.status in ('N') then 1 else 0 end),0) as N
                                , coalesce(sum(case when loan_usr_info.save_status='Y' and cust_info.save_status='Y' and loan_info.save_status='Y' and loan_info.status in ('A') then 1 else 0 end),0) as A
                                , coalesce(sum(case when loan_usr_info.save_status='Y' and cust_info.save_status='Y' and loan_info.save_status='Y' and loan_info.status in ('E') then 1 else 0 end),0) as E"
                        )
            );
            $vcnt = $BOXC->FIRST();

			$r['tabCount'] = array_change_key_case((Array) $vcnt, CASE_UPPER);
		}

        
        if( $request->tabsSelect=="N" )
		{
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('N');
        }
        else if( $request->tabsSelect=="A" )
		{
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('A');
        }
        else if( $request->tabsSelect=="E" )
		{
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('E');
        }

        // 메인쿼리
        $LOAN_LIST = DB::TABLE("loan_info")->join("loan_usr_info", "loan_info.loan_usr_info_no", "=", "loan_usr_info.no")
                                           ->join("CUST_INFO", "loan_info.cust_info_no", "=", "CUST_INFO.no")
                                           ->join("CUST_INFO_EXTRA", "CUST_INFO.no", "=", "CUST_INFO_EXTRA.cust_info_no")
                                           ->SELECT("CUST_INFO.NAME cust_name, LOAN_USR_INFO.PH11, LOAN_USR_INFO.PH12, LOAN_USR_INFO.PH13, LOAN_USR_INFO.BANK_CD, LOAN_USR_INFO.BANK_SSN, LOAN_USR_INFO.BANK_CD2, LOAN_USR_INFO.BANK_SSN2, LOAN_USR_INFO.BANK_CD3, LOAN_USR_INFO.BANK_SSN3, LOAN_USR_INFO.NAME, loan_info.*")
                                           ->WHERE('LOAN_USR_INFO.SAVE_STATUS','Y')
                                           ->WHERE('CUST_INFO.SAVE_STATUS','Y')
                                           ->WHERE('loan_info.SAVE_STATUS','Y');


        // 완제일자 null 제외 후 강제출력
        if($param['listOrder'] == 'fullpay_date' && !empty($param['listOrder']) && !empty($param['listOrderAsc']))
        {
            $LOAN_LIST->orderByRaw("status asc nulls last, fullpay_date ".$param['listOrderAsc']." nulls last, return_date ".$param['listOrderAsc']." nulls last");
            unset($param['listOrder'], $param['listOrderAsc']);
        }

        if(empty($param['listOrder']) && empty($param['listOrderAsc'])) {
            $param['listOrder'] = 'contract_date,no';
            $param['listOrderAsc'] = 'desc,desc';
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='investor_no-inv_seq' && !empty($param['searchString']) )
        {
            if(strstr($param['searchString'], '-'))
            {
                $searchString = explode("-", $param['searchString']);

                // 이관법인타입 문자열 확인
                $pattern = '/([\xEA-\xED][\x80-\xBF]{2}|[a-zA-Z])+/';
                preg_match_all($pattern, $searchString[0], $match);
                $string = implode('', $match[0]);

                // 채권번호 앞에 문자열이 있을경우
                if(!empty($string))
                {
                    $searchString[0] = str_replace($string, "", $searchString[0]);

                    if(!empty($searchString[0]))
                    {
                        // 문자열있는 투자자번호 검색(ex. H5-?)
                        if($searchString[1] == null || $searchString[1] == 0 || $searchString[1] == '')
                        {
                            $LOAN_LIST = $LOAN_LIST->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $LOAN_LIST = $LOAN_LIST->WHERE('loan_usr_info.investor_no',$searchString[0])
                                                    ->WHERE('loan_info.inv_seq',$searchString[1])
                                                    ->WHERE('loan_info.investor_type',$string);          
                        }
                    }
                }
                // 기존 채권번호 형태인경우
                else
                {
                    // 투자자번호로만 검색(ex. 5-?)
                    if($searchString[1] == null || $searchString[1] == 0 || $searchString[1] == '')
                    {
                        $LOAN_LIST = $LOAN_LIST->WHERE('loan_usr_info.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $LOAN_LIST = $LOAN_LIST->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
                    }
                }
            }

            unset($param['searchString']);
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='loan_usr_info.investor_no' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='loan_usr_info.nick_name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('loan_usr_info.nick_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        //구간 검색 시 숫자 아닌 값 입력되면 데이터 제거
        if(isset($param['rangeSearchDetail']) && (!empty($param['sRangeSearchString']) || !empty($param['eRangeSearchString']) )) {
            $pattern = '/\d+/';
            if(!preg_match($pattern, $param['sRangeSearchString']) || !preg_match($pattern, $param['eRangeSearchString'])) {
                unset($param['rangeSearchDetail']);
            }
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $LOAN_LIST = $list->getListQuery('loan_info', 'main', $LOAN_LIST, $param);

        $target_sql = urlencode(encrypt(Func::printQuery($LOAN_LIST))); // 페이지 들어가기 전에 쿼리를 저장해야한다.
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN_LIST, $request->page, $request->listLimit, 10, $request->listName);

        $rslt = $LOAN_LIST->GET();
        $rslt = Func::chungDec(["LOAN_USR_INFO","loan_info","cust_info"], $rslt);	// CHUNG DATABASE DECRYPT

        $configArr         = Func::getConfigArr();
        $array_bank_cd     = Func::getConfigArr('bank_cd');
        $arrayUserId       = Func::getUserId();
        $arrayBranch       = Func::getBranch();

        $cnt = 0;

        foreach ($rslt as $v)
        {
            $v->onclick                  = 'popUpFull(\'/account/investmentpop?no='.$v->no.'\', \'investment'.$v->no.'\')';
            $v->line_style               = 'cursor: pointer;';
            
            $v->investor_no_inv_seq      = $v->investor_type.$v->investor_no.'-'.$v->inv_seq;
            $v->name                     = Func::nameMasking($v->name, 'N'); 
            $v->contract_date            = Func::dateFormat($v->contract_date);
            $v->loan_money               = number_format($v->loan_money);
            if($v->status != 'E')
            {
                $v->balance              = number_format($v->balance);
                $v->return_date          = Func::dateFormat($v->return_date);
                $v->return_money         = number_format($v->return_money);
            }
            else
            {
                $v->balance              = 0;
                $v->return_date          = '';
                $v->return_money         = 0;
            }
            $v->fullpay_money            = number_format($v->fullpay_money);
            $v->fullpay_date             = Func::dateFormat($v->fullpay_date);
            $v->status                   = Func::getInvStatus($v->status, true);
            $v->ph1                      = Func::phMasking($v->ph11, $v->ph12, $v->ph13, 'Y');
            $v->contract_end_date        = Func::dateFormat($v->contract_end_date);
            $v->save_time                = Func::dateFormat($v->save_time);
            $v->save_id                  = Func::getArrayName($arrayUserId, $v->save_id);
            $v->pro_cd                   = Func::getArrayName($configArr['pro_cd'], $v->pro_cd);
            $v->invest_rate              = sprintf('%0.2f',$v->invest_rate);
            $v->return_origin_sum        = number_format($v->return_origin_sum ?? 0);
            $v->pay_term                 = $v->pay_term." 개월";
            $v->contract_day             = $v->contract_day." 일";
 
            $v->status                   = Func::getInvStatus($v->status, true);

            $r['v'][] = $v;
            $cnt ++;
        }
		
        // 페이징
        $r['pageList']  = $paging->getPagingHtml($request->path());
        $r['result']    = 1;
        $r['txt']       = $cnt;
        $r['targetSql'] = $target_sql;
        $r['totalCnt']  = $paging->getTotalCnt();

        return json_encode($r);
    }

    /**
     * 투자계약 등록 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investmentForm(Request $request)
    {
        $arrayConfig  = Func::getConfigArr();
        $arrayBranch  = Func::myPermitBranch();
        $branches     = DB::TABLE("branch")->SELECT("code", "branch_name")->WHERE("parent_code", "T2")->WHERE("save_status", "Y")->get();
        $chargeBranch = array();
        foreach($branches as $branch){
            $chargeBranch[$branch->code] = $branch->branch_name;
        }
        $getProCode   = Func::getConfigArr('pro_cd');

        return view('account.investmentForm')->with("arrayConfig", $arrayConfig)
                                        ->with("arrayBranch", $arrayBranch)
                                        ->with("chargeBranch", $chargeBranch)
                                        ->with("arrayProCd", $getProCode);
    }

    /**
     * 투자자정보 - 팝업창
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investmentPop(Request $request)
    {
        $status_color = "#6c757d";
        $no = $request->no;
        
        $inv = DB::table("loan_info");
        $inv->join("cust_info", "cust_info.no", "=", "loan_info.cust_info_no");
        $inv->join("loan_usr_info", "loan_usr_info.no", "=", "loan_info.loan_usr_info_no");
        $inv->select("loan_info.*", "cust_info.name");
        $inv->where("loan_info.save_status", "Y");
        $inv->where("cust_info.save_status", "Y");
        $inv->where("loan_info.no",$no);
        $inv = $inv->first();

        $inv = Func::chungDec(["loan_info","cust_info"], $inv);	// CHUNG DATABASE DECRYPT

        return view('account.investmentPop')->with("inv", $inv)->with("status_color", $status_color);
    }

    /**
     * 투자자정보 팝업창 - 상세정보
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investmentInfo(Request $request)
    {
        $array_user         = Func::getUserId();
        $array_config       = Func::getConfigArr();

        $v = null;
        $plans = [];
        
        $loan_usr_info_no = $request->loan_usr_info_no;
        $loan_info_no     = $request->loan_info_no;
        $cust_info_no     = $request->cust_info_no;
        
        if(is_numeric($loan_info_no))
        {
            $v = DB::TABLE("loan_info")->JOIN("loan_usr_info", "loan_info.loan_usr_info_no", "=", "loan_usr_info.no")
            ->SELECT("name, ssn, ph11, ph12, ph13, zip1, addr11, addr12, relation, loan_info.*, loan_usr_info.no as loan_usr_info_no, loan_usr_info.investor_no as investor_no")
            ->WHERE('loan_info.save_status','Y')
            ->WHERE("loan_info.no", $loan_info_no)->first();
            $v = Func::chungDec(["loan_info", "loan_usr_info"], $v);	// CHUNG DATABASE DECRYPT

            $v->ssn = Func::ssnFormat($v->ssn, 'A');
            
            $v->ph21 = $v->ph11;
            $v->ph22 = $v->ph12;
            $v->ph23 = $v->ph13;
            
            $v->zip  = $v->zip1;
            $v->addr1 = $v->addr11;
            $v->addr2 = $v->addr12;
            
            $v->bank_name = Func::getArrayName(Func::getConfigArr('bank_cd'), $v->loan_bank_cd);
            
            $plans = DB::TABLE("loan_info_return_plan")
                                ->JOIN("loan_info", "loan_info.no", "=", "loan_info_return_plan.loan_info_no")
                                ->JOIN("loan_usr_info", "loan_usr_info.no", "=", "loan_info.loan_usr_info_no")
                                ->SELECT("loan_info_return_plan.*")
                                ->WHERE("loan_info_return_plan.loan_info_no", $loan_info_no)
                                ->WHERE("loan_info_return_plan.save_status", "Y")
                                ->WHERE("loan_info.save_status", "Y")
                                ->ORDERBY('seq')
                                ->GET();
            $plans = Func::chungDec(["loan_info_return_plan"], $plans);	// CHUNG DATABASE DECRYPT
        }

        // 뷰 데이터 정리
        if(isset($v) && !empty($v))
        {
            $no = $v->no;
            $v->invest_rate       = sprintf('%0.2f',$v->invest_rate);
            $v->contract_date     = Func::dateFormat($v->contract_date);
            $v->contract_end_date = Func::dateFormat($v->contract_end_date);
            $v->platform_fee_rate = sprintf('%0.2f',$v->platform_fee_rate);
        }

        // listName : 리스트 이름 (표시 x)
        $result['listName'] = 'investmentinfo';

        // listAction : 리스트 url - ajax 요청주소 ( '/'.$request->path() )
        $result['listAction'] = '/'.$request->path();

        // tabs : 탭 사용 여부 (Y, N)
        $result['tabs'] = 'N';

        // button : 버튼 추가 여부 (Y, N)
        $result['button'] = 'N';

        // searchDate : 일자검색 여부 (Y, N)
        $result['searchDate'] = 'N';

        // searchType : select 검색 여부 (Y, N) [searchDetail과 다른 점은 input 입력하는 부분이 없다.]
        $result['searchType'] = 'N';

        // searchDetail : 검색 사용 여부 (Y, N)
        $result['searchDetail'] = 'X';

        // searchButton : 검색버튼 사용 여부 (Y, N)
        // isModal : 모달창 사용여부 (Y, N)
        $result['isModal'] = 'N';

        // plusButton : 등록 버튼 추가 여부 (Y, N)
        $result['plusButton'] = 'N';

        $result['inv_seq'] = $v->inv_seq;
        
        if(isset($loan_info_no) && isset($cust_info_no))
        {
            $result['customer']['loan_usr_info_no'] = $loan_usr_info_no;
            $result['customer']['loan_info_no']     = $loan_info_no;
            $result['customer']['cust_info_no']     = $cust_info_no;
        }

        $result['page'] = $request->page ?? 1;
        
        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $result['listTitle'] = Array
        (
            'count'                     => Array('No', 1, '', 'center', '', ''),
            'investor_no_inv_seq'       => Array('채권번호', 0, '', 'center', '', ''),
            'name'                      => Array('투자자명', 1, '', 'center', '', 'name'),
            'contract_date'             => Array('투자일자', 1, '', 'center', '', 'contract_date'),
            'contract_end_date'         => Array('만기일자', 1, '', 'center', '', 'contract_end_date'),
            'contract_day'              => Array('이수일(일)', 1, '', 'center', '', 'contract_day'),
            'pay_term'                  => Array('수익지급주기(개월)', 1, '', 'center', '', 'pay_term'),
            'loan_money'                => Array('투자금액', 1, '', 'center', '', 'loan_money'),
            'return_origin_sum'         => Array('투자원금상환액', 0, '', 'center', '', 'return_origin_sum'),
            'balance'                   => Array('투자잔액', 1, '', 'center', '', 'balance'),
            'invest_rate'               => Array('이자율(%)', 1, '', 'center', '', 'invest_rate')
        );

        // listlimit : 한페이지 출력 건수
        $result['listlimit'] = "5";

        return view('account.investmentInfo')->with('v', $v)
                                    ->with('plans', $plans)
                                    ->with('array_user',$array_user)
                                    ->with("result",    $result)
                                    ->with("userVar",   $loan_info_no)
                                    ->with("configArr", $array_config);
    }

    /**
    * 투자내역 투자 리스트
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function investmentinfoList(Request $request)
    {
        $array_config = Func::getConfigArr();
        $LOAN_LIST = DB::TABLE("loan_info")
                            ->join("loan_usr_info", "loan_info.loan_usr_info_no", "=", "loan_usr_info.no")
                            ->select("loan_usr_info.name, loan_info.*")
                            ->where('loan_info.save_status','Y')
                            ->where("loan_info.loan_usr_info_no",$request->loan_usr_info_no);

        $info = DB::table("loan_info")->select('status')->where('no',$request->loan_info_no)->first();
        
        if ($info->status != 'E')
        {
            $LOAN_LIST->WHERE('loan_info.status', '!=','E');
        }
        else
        {
            $LOAN_LIST->WHERE('loan_info.no', $request->loan_info_no);
        }

        // 정렬
        if($request->listOrder)
        {
            $LOAN_LIST = $LOAN_LIST->ORDERBY($request->listOrder, $request->listOrderAsc);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->ORDERBY('contract_date','asc');
            $LOAN_LIST = $LOAN_LIST->ORDERBY('investor_no','asc');
        }
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN_LIST, $request->page, 50, 10);
        
        // 결과
        $LOAN_LIST = $LOAN_LIST->get();
        $LOAN_LIST = Func::chungDec(["loan_info", "LOAN_USR_INFO"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $cnt = 0;
        foreach ($LOAN_LIST as $v)
        {              
            if(isset($request->loan_info_no) && $request->loan_info_no==$v->no)
            {
                $v->line_style = 'background-color:#ffdddd;';
            }

            $link_c                 = '<a class="hand" onClick="clickInvestmentInfo(\''.$v->no.'\')">';
            $v->count               = $cnt+1;
            $v->investor_no_inv_seq = $v->investor_type.$v->investor_no.'-'.$v->inv_seq;
            $v->name                = $link_c.Func::nameMasking($v->name, 'N');
            $v->contract_date       = Func::dateFormat($v->contract_date);
            $v->contract_end_date   = Func::dateFormat($v->contract_end_date);
            $v->fullpay_date        = Func::dateFormat($v->fullpay_date);
            $v->loan_money          = number_format($v->loan_money);
            $v->fullpay_money       = number_format($v->fullpay_money);
            if($v->status != 'E')
            {
                $v->balance         = number_format($v->balance);
                $v->return_date     = Func::dateFormat($v->return_date);
                $v->return_money    = number_format($v->return_money);
            }
            else
            {
                $v->balance         = 0;
                $v->return_date     = '';
                $v->return_money    = 0;
            }
            $v->invest_rate         = sprintf('%0.2f',$v->invest_rate);
            $v->reschedule          = '<button type="button" title="새로고침" class="btn btn-sm btn-info float-center mr-2" onclick="setReschedule('.$v->no.');return false;" style="height:29px;"><i class="fas fa-sync"></i></button>';
            $v->return_origin_sum   = number_format($v->return_origin_sum);

            $r['v'][] = $v;
            $cnt ++;
        }
        
        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path(), 'investmentinfo');

        $r['result'] = 1;
        $r['txt'] = $cnt;

        return json_encode($r);
    }

    /**
     * 엑셀다운로드 (채권현황조회)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investmentExcel(Request $request)
    {
        if( !Func::funcCheckPermit("U002") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }
        
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $list           = $this->setDataInvestmentList($request);
        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        $USR = DB::TABLE("loan_info")->join("loan_usr_info", "loan_info.loan_usr_info_no", "=", "loan_usr_info.no")
                                     ->join("cust_info", "loan_info.cust_info_no", "=", "cust_info.no")
                                     ->join("cust_info_extra", "cust_info.no", "=", "cust_info_extra.cust_info_no")
                                     ->select("cust_info.NAME cust_name, loan_usr_info.PH11, loan_usr_info.PH12, loan_usr_info.PH13, loan_usr_info.BANK_CD, loan_usr_info.BANK_SSN, LOAN_USR_INFO.BANK_CD2, LOAN_USR_INFO.BANK_SSN2, LOAN_USR_INFO.BANK_CD3, LOAN_USR_INFO.BANK_SSN3, LOAN_USR_INFO.NAME, loan_info.*")
                                     ->where('loan_usr_info.save_status','Y')
                                     ->where('cust_info.save_status','Y')
                                     ->where('loan_info.save_status','Y')
                                     ->orderby('contract_date', 'desc');

        if( $request->tabsSelect=="N" )
		{
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('N');
        }
        else if( $request->tabsSelect=="A" )
		{
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('A');
        }
        else if( $request->tabsSelect=="E" )
		{
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('E');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='investor_no-inv_seq' && !empty($param['searchString']) )
        {
            if(strstr($param['searchString'], '-'))
            {
                $searchString = explode("-", $param['searchString']);

                // 이관법인타입 문자열 확인
                $pattern = '/([\xEA-\xED][\x80-\xBF]{2}|[a-zA-Z])+/';
                preg_match_all($pattern, $searchString[0], $match);
                $string = implode('', $match[0]);

                // 채권번호 앞에 문자열이 있을경우
                if(!empty($string))
                {
                    $searchString[0] = str_replace($string, "", $searchString[0]);

                    if(!empty($searchString[0]))
                    {
                        // 문자열있는 투자자번호 검색(ex. H5-?)
                        if($searchString[1] == null || $searchString[1] == 0 || $searchString[1] == '')
                        {
                            $USR = $USR->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $USR = $USR->WHERE('loan_usr_info.investor_no',$searchString[0])
                                                    ->WHERE('loan_info.inv_seq',$searchString[1])
                                                    ->WHERE('loan_info.investor_type',$string);          
                        }
                    }
                }
                // 기존 채권번호 형태인경우
                else
                {
                    // 투자자번호로만 검색(ex. 5-?)
                    if($searchString[1] == null || $searchString[1] == 0 || $searchString[1] == '')
                    {
                        $USR = $USR->WHERE('loan_usr_info.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $USR = $USR->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
                    }
                }
            }
            
            unset($param['searchString']);
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='loan_usr_info.investor_no' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='loan_usr_info.nick_name' && !empty($param['searchString']) )
        {
            $USR = $USR->where('loan_usr_info.nick_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        //구간 검색 시 숫자 아닌 값 입력되면 데이터 제거
        if(isset($param['rangeSearchDetail']) && (!empty($param['sRangeSearchString']) || !empty($param['eRangeSearchString']) )) {
            $pattern = '/\d+/';
            if(!preg_match($pattern, $param['sRangeSearchString']) || !preg_match($pattern, $param['eRangeSearchString'])) {
                unset($param['rangeSearchDetail']);
            }
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }


        $USR = $list->getListQuery('loan_info', 'main', $USR, $param);
        $USR->orderBy("loan_info.no", "desc");

        $target_sql = urlencode(encrypt(Func::printQuery($USR))); // 페이지 들어가기 전에 쿼리를 저장해야한다.                
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($USR, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "투자계약관리_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $request_all['work_id'] = Auth::id();
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no))
        {
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
            $origin_filename = $excel_no.'_'.$request->work_id.'_'.date("YmdHis").'.xlsx'; 
        }
        else
        {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $target_sql, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
            $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
        }

        $rslt = $USR->GET();
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","CUST_INFO_EXTRA"], $rslt);	// CHUNG DATABASE DECRYPT
        
        // 엑셀 헤더
        $excel_header   = array('No','채권번호', '차입자명','투자자명', '상품구분', '진행상태','투자일자', '만기일자', '투자개월','투자금액','투자원금상환액', '투자잔액','수익률(%)','수익지급주기','약정일', '차기수익지급일자', '차기수익지급금액','저장자','저장시간');
        $excel_data     = [];

        $array_config   = Func::getConfigArr();
        $arrManager     = Func::getUserList();
        $arrayBranch    = Func::getBranch();

        $board_count = 1;
        foreach ($rslt as $v)
        {
            $array_data = [
                $board_count,                                                           //No
                $v->investor_type.$v->investor_no.'-'.$v->inv_seq,                      //채권번호
                Func::nameMasking(Func::decrypt($v->cust_name, 'ENC_KEY_SOL'), 'N'),    //차입자명
                $v->name,                                                               //투자자명
                Func::getArrayName($array_config['pro_cd'], $v->pro_cd),                //상품구분
                Func::getInvStatus($v->status),                                         //진행상태
                Func::dateFormat($v->contract_date),                                    //투자일자
                Func::dateFormat($v->contract_end_date),                                //만기일자
                $v->loan_term,                                                          //투자개월
                number_format($v->loan_money),                                          //투자금액
                number_format($v->return_origin_sum ?? 0),                              //투자원금상환액
                number_format($v->balance),                                             //투자잔액
                sprintf('%0.2f',$v->invest_rate),                                       //수익률
                $v->pay_term." 개월",                                                   //수익지급주기
                $v->contract_day." 일",                                                 //약정일
                Func::dateFormat($v->return_date),                                      //차기수익지급일자
                number_format($v->return_money),                                        //차기수익지급금액
                isset($arrManager[$v->save_id]) ? Func::nvl($arrManager[$v->save_id]->name, $v->save_id) : $v->save_id,
                Func::dateFormat($v->save_time),                                        //저장일자
            ];
            $record_count++;
            $board_count++;
            $excel_data[] = $array_data;
        }
        
        // 엑셀 익스포트
        ExcelFunc::fastexcelExport($excel_data, $excel_header, $origin_filename);
    
        // 파일 저장 여부 확인
        $exists = Storage::disk('excel')->exists($origin_filename);
        
        if( isset($exists) )
        {
            $array_result['etc']             = $etc;
            $array_result['result']          = 'Y';
            $array_result['filename']        = $file_name;
            $array_result['excel_no']        = $excel_no;
            $array_result['record_count']    = $record_count;
            $array_result['down_filename']   = $down_filename;
            $array_result['excel_down_div']  = $excel_down_div;
            $array_result['origin_filename'] = $origin_filename;
            
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div,null, $origin_filename);  
        }
        else
        {
           $array_result['result']    = 'N';
           $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }
        return $array_result;
    }

    /**
     * 투자원금조정 - 입력창
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function excelUploadForm(Request $request)
    {
        $status_color = "#6c757d";
        
        $v = DB::table("loan_info")->join("loan_usr_info","loan_usr_info.no","=","loan_info.loan_usr_info_no")
                                    ->select("loan_info.*, loan_usr_info.name")
                                    ->where("loan_info.no",$request->loan_info_no)
                                    ->where("loan_usr_info.save_status","Y")
                                    ->first();

        $v = Func::chungDec(["loan_info","loan_usr_info"], $v);	// CHUNG DATABASE DECRYPT

        return view('account.exceluploadform')->with("v", $v)->with("status_color", $status_color);
    }

    /**
     * 투자등록 - 스케줄 미리보기
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function reviewInvestSchedule(Request $request)
    {
        $param = $request->all();

        $string = "";
        $sum_plan_origin = $sum_plan_interest = $sum_withholding_tax = $sum_income_tax = $sum_local_tax = $sum_plan_money = 0;

        $_INV = [];
        $_INV['no']                  = $param['loan_info_no'];
        $_INV['loan_usr_info_no']    = $param['loan_usr_info_no'];
        $_INV['pro_cd']              = $param['pro_cd'];
        $_INV['return_method_cd']    = $param['return_method_cd'];
        $_INV['loan_money']          = preg_replace('/[^0-9]/', '', $param['loan_money']);
        $_INV['contract_date']       = preg_replace('/[^0-9]/', '', $param['contract_date']);
        $_INV['contract_end_date']   = preg_replace('/[^0-9]/', '', $param['contract_end_date']);
        $_INV['contract_day']        = preg_replace('/[^0-9]/', '', $param['contract_day']);

        $date1 = Carbon::parse($_INV['contract_date']);
        $date2 = Carbon::parse($_INV['contract_end_date']);
        $_INV['loan_term']           = $date1->diffInMonths($date2);

        $uv = DB::table('loan_usr_info')->select('tax_free')->where('no',$param['loan_usr_info_no'])->where('save_status','Y')->first();
        $_INV['tax_free']            = !empty($uv->tax_free) ? $uv->tax_free : "N";

        $_INV['invest_rate']         = $param['invest_rate'];
        $_INV['income_rate']         = $param['income_rate'];
        $_INV['local_rate']          = $param['local_rate'];
        $_INV['platform_fee_rate']   = $param['platform_fee_rate'];
        
        $_INV['arrayRatio'][$_INV['contract_date']]['req_date']  = $_INV['contract_date'];
        $_INV['arrayRatio'][$_INV['contract_date']]['req_value'] = $_INV['invest_rate'];
        $_INV['feeInfo'][$_INV['contract_date']]['req_date']     = $_INV['contract_date'];
        $_INV['feeInfo'][$_INV['contract_date']]['req_value']    = $_INV['platform_fee_rate'];
        $_INV['moneyInfo'][$_INV['contract_date']]['req_date']   = $_INV['contract_date'];
        $_INV['moneyInfo'][$_INV['contract_date']]['req_value']  = $_INV['loan_money'];

        $inv = new Invest($_INV);

        $array_plan = $inv->buildPlanData($_INV['contract_date'], $_INV['contract_end_date']);
        foreach($array_plan as $plan_date => $v)
        {
            $string.= '<tr>';
            $string.= '<td class="text-center bg-secondary">'.$v['seq'].'</td>';
            $string.= '<td class="text-center">'.Func::dateFormat($v['plan_date']).'('.Vars::$arrayWeekDay[date('w',Func::dateToUnixtime($v['plan_date']))].')</td>';
            $string.= '<td class="text-center">'.Func::dateFormat($v['plan_date_biz']).'</td>';
            if($_INV['return_method_cd'] == 'F')
            {
                $string.= '<td class="text-center">'.Func::dateFormat($v['plan_interest_sdate']).' ~ '.Func::dateFormat($v['plan_interest_edate']).'</td>';
            }
            $string.= '<td class="text-right">'.number_format($v['plan_balance']).'</td>';
            $string.= '<td class="text-right">'.number_format($v['plan_origin']).'</td>';
            $string.= '<td class="text-right">'.number_format($v['plan_interest']).'</td>';
            $string.= '<td class="text-right">'.number_format($v['withholding_tax']).'</td>';
            $string.= '<td class="text-right">'.number_format($v['income_tax']).'</td>';
            $string.= '<td class="text-right">'.number_format($v['local_tax']).'</td>';
            $string.= '<td class="text-right">'.number_format($v['plan_money']).'</td>';
            $string.= '<td class="text-center">-</td>';
            $string.= "</tr>";

            $sum_plan_origin+= $v['plan_origin'];
            $sum_plan_interest+= $v['plan_interest'];
            $sum_withholding_tax+= $v['withholding_tax'];
            $sum_income_tax+= $v['income_tax'];
            $sum_local_tax+= $v['local_tax'];
            $sum_plan_money+= $v['local_tax'];
        }

        $string.= '<tr class="bg-secondary">';
        $string.= '<td class="text-center"></td>';
        if($_INV['return_method_cd'] == 'F')
        {
            $string.= '<td class="text-center" colspan="4">합계</td>';
        }
        else
        {
            $string.= '<td class="text-center" colspan="3">합계</td>';
        }
        $string.= '<td class="text-right">'.number_format($sum_plan_origin).'</td>';
        $string.= '<td class="text-right">'.number_format($sum_plan_interest).'</td>';
        $string.= '<td class="text-right" id="td_tot_withholding_tax">'.number_format($sum_withholding_tax).'</td>';
        $string.= '<td class="text-right" id="td_tot_income_tax">'.number_format($sum_income_tax).'</td>';
        $string.= '<td class="text-right" id="td_tot_local_tax">'.number_format($sum_local_tax).'</td>';
        $string.= '<td class="text-right">'.number_format($sum_plan_money).'</td>';
        $string.= '<td class="text-center"></td>';

        $string.= '</tr>';
        
        return $string;
    }

    /**
     * 투자등록
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function investmentInfoAction(Request $request)
    {
        $_INV = $request->all();

        if($_INV['pro_cd'] == '03')
        {
            if(empty($_INV['plan_date']) || empty($_INV['plan_origin']) || empty($_INV['plan_interest']))
            {
                Log::debug("투자등록 실행오류#-1 ".$_INV['loan_info_no']);
    
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "스케줄을 업로드하시길 바랍니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }
        }

        $LOAN_INFO = DB::table("loan_info")->select("*")->where("no",$_INV['loan_info_no'])->where('save_status','Y')->first();
        $LOAN_INFO = Func::chungDec(["loan_info"], $LOAN_INFO);	// CHUNG DATABASE DECRYPT

        if(empty($LOAN_INFO))
        {
            Log::debug("투자등록 실행오류#-2 ".$_INV['loan_info_no']);

            $array_result['rs_code']    = "N";
            $array_result['result_msg'] = "해당 계약이 존재하지 않습니다.(".$_INV['loan_info_no'].")";
            
            return $array_result;
        }

        $save_status = 'Y';
        $save_id     = Auth::id();
        $save_time   = date("YmdHis");

        DB::beginTransaction();

        // 기관차입 제외 계약등록
        if($_INV['actMode'] == 'INS' && $_INV['pro_cd'] != '03')
        {
            $_INV['contract_date']        = $_INV['loan_date'] = $_INV['take_date'] = $_INV['app_date'] = preg_replace('/[^0-9]/', '', $_INV['contract_date']);
            $_INV['contract_end_date']    = preg_replace('/[^0-9]/', '', $_INV['contract_end_date']);
            $_INV['contract_day']         = preg_replace('/[^0-9]/', '', $_INV['contract_day']);
            $_INV['take_date']			  = $_INV['loan_date'];
    
            $date1 = Carbon::parse($_INV['contract_date']);
            $date2 = Carbon::parse($_INV['contract_end_date']);
    
            $_INV['loan_term']             = $date1->diffInMonths($date2);
            $_INV['loan_pay_term'] 		   = $_INV['pay_term'] = isset($_INV['pay_term']) ? $_INV['pay_term'] : '1';			// 차주 이자납입주기
    
            $_INV['invest_rate']           = $_INV['loan_rate'] = $_INV['loan_delay_rate'] = sprintf('%0.2f', $_INV['invest_rate']);
            $_INV['income_rate']           = sprintf('%0.2f', $_INV['income_rate']);
            $_INV['local_rate']            = sprintf('%0.2f', $_INV['local_rate']);
            $_INV['legal_rate']            = Vars::$curMaxRate;
            
            $_INV['loan_money']            = $_INV['app_money'] = $_INV['total_loan_money'] = $_INV['first_loan_money'] = preg_replace('/[^0-9]/', '', $_INV['loan_money']);
            $_INV['balance']               = $_INV['platform_fee_rate'] = 0;
            $_INV['monthly_return_money']  = 0;
    
            $_INV['viewing_return_method'] = $_INV['return_method_cd'];
            $_INV['monthly_return_gubun']  = '';
    
            $_INV['loan_type']             = '01';

            $_INV['save_id']               = $save_id;
            $_INV['save_time']             = $save_time;
            
            unset($_INV['no']);
            $rslt = DB::dataProcess('UPD', 'loan_info', $_INV, ['no'=>$_INV['loan_info_no']]);
            // 오류 업데이트 후 쪽지 발송
            if( $rslt!="Y" )
            {
                DB::rollBack();
    
                Log::debug("투자등록 실행오류#0 ".$_INV['loan_info_no']);
    
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "투자등록시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }
    
            $valcday = [];
            $valcday['loan_info_no'] = $_INV['loan_info_no'];
            $valcday['cday_date']    = $_INV['loan_date'];
            $valcday['contract_day'] = $_INV['contract_day'];
            $valcday['save_status']  = $save_status;
            $valcday['save_time']    = $save_time;
            $valcday['save_id']      = $save_id;
            $rslt = DB::dataProcess('UST', 'loan_info_cday', $valcday, ['loan_info_no'=>$valcday['loan_info_no'], 'cday_date'=>$valcday['cday_date']]);
            if( $rslt!="Y" )
            {
                DB::rollBack();
    
                Log::debug("투자등록 실행오류#1 ".$_INV['loan_info_no']);
    
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "투자등록시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }
    
            $valrate = [];
            $valrate['loan_info_no']    = $_INV['loan_info_no'];
            $valrate['rate_date']       = $_INV['loan_date'];
            $valrate['loan_rate']       = $_INV['loan_rate'];
            $valrate['loan_delay_rate'] = $_INV['loan_delay_rate'];
            $valrate['save_status']     = $save_status;
            $valrate['save_time']       = $save_time;
            $valrate['save_id']         = $save_id;
            $rslt = DB::dataProcess('UST', 'loan_info_rate', $valrate, ['loan_info_no'=>$valrate['loan_info_no'], 'rate_date'=>$valrate['rate_date']]);
            if( $rslt!="Y" )
            {
                DB::rollBack();
    
                Log::debug("투자등록 실행오류#2 ".$_INV['loan_info_no']);
    
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "투자등록시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }
    
            $valinvrate = [];
            $valinvrate['loan_info_no']    = $_INV['loan_info_no'];
            $valinvrate['rate_date']       = $_INV['loan_date'];
            $valinvrate['invest_rate']     = $_INV['invest_rate'];
            $valinvrate['save_status']     = $save_status;
            $valinvrate['save_time']       = $save_time;
            $valinvrate['save_id']         = $save_id;
            $rslt = DB::dataProcess('UST', 'loan_info_invest_rate', $valinvrate, ['loan_info_no'=>$valinvrate['loan_info_no'], 'rate_date'=>$valinvrate['rate_date']]);
            if( $rslt!="Y" )
            {
                DB::rollBack();
    
                Log::debug("투자등록 실행오류#3 ".$_INV['loan_info_no']);
    
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "투자등록시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }
            
            // 분배예정스케줄 생성
            $inv = new Invest($_INV['loan_info_no']);
            $array_inv_plan = $inv->buildPlanData($_INV['contract_date'], $_INV['contract_end_date']);
            $rslt = $inv->savePlan($array_inv_plan);
            if(!isset($rslt) || $rslt != "Y")
            {
                DB::rollBack();
    
                Log::debug("투자등록 실행오류#4 ".$_INV['loan_info_no']);
    
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "투자등록시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }
    
            // 거래내역 등록
            $_IN['trade_type']       = '11';
            $_IN['trade_date']       = $_INV['contract_date'];
            $_IN['trade_money']      = $_INV['loan_money'];
            $_IN['loan_usr_info_no'] = $_INV['loan_usr_info_no'];
            $_IN['cust_info_no']     = $_INV['cust_info_no'];
            $_IN['loan_info_no']     = $_INV['loan_info_no'];
            $_IN['trade_fee']        = 0;
    
            $t = new Trade($_INV['loan_info_no']);
            $loan_info_trade_no = $t->tradeOutInsert($_IN, Auth::id());
    
            // 오류 업데이트 후 쪽지 발송
            if(!is_numeric($loan_info_trade_no))
            {
                DB::rollBack();
    
                Log::debug("투자등록 실행오류#5 ".$_INV['loan_info_no']);
    
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "투자등록시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }
            
            Log::info('거래내역등록 > 계약번호 : '.$_INV['loan_info_no'].', 거래내역번호 : '.$loan_info_trade_no);
        }

        // 기관차입 계약등록
        else if($_INV['actMode'] == 'INS' && $_INV['pro_cd'] == '03')
        {
            $_INV['contract_date']         = $_INV['loan_date'] = $_INV['take_date'] = $_INV['app_date'] = preg_replace('/[^0-9]/', '', $_INV['contract_date']);
            $_INV['contract_end_date']     = preg_replace('/[^0-9]/', '', $_INV['contract_end_date']);
            $_INV['contract_day']          = preg_replace('/[^0-9]/', '', $_INV['contract_day']);
            $_INV['take_date']			   = $_INV['loan_date'];
            
            $date1 = Carbon::parse($_INV['contract_date']);
            $date2 = Carbon::parse($_INV['contract_end_date']);
    
            $_INV['loan_term']             = $date1->diffInMonths($date2);
            $_INV['loan_pay_term'] 		   = $_INV['pay_term'] = isset($_INV['pay_term']) ? $_INV['pay_term'] : '1';			// 차주 이자납입주기
    
            $_INV['invest_rate']           = $_INV['loan_rate'] = $_INV['loan_delay_rate'] = sprintf('%0.2f', $_INV['invest_rate']);
            $_INV['income_rate']           = sprintf('%0.2f', $_INV['income_rate']);
            $_INV['local_rate']            = sprintf('%0.2f', $_INV['local_rate']);
            $_INV['legal_rate']            = Vars::$curMaxRate;
            
            $_INV['loan_money']            = $_INV['app_money'] = $_INV['total_loan_money'] = $_INV['first_loan_money'] = preg_replace('/[^0-9]/', '', $_INV['loan_money']);
            $_INV['balance']               = $_INV['platform_fee_rate'] = 0;
            $_INV['monthly_return_money']  = 0;
    
            $_INV['viewing_return_method'] = $_INV['return_method_cd'];
            $_INV['monthly_return_gubun']  = '';
    
            $_INV['loan_type']             = '01';
    
            $_INV['loan_memo']             = $_INV['loan_memo'] ?? '';

            $_INV['save_id']               = $save_id;
            $_INV['save_time']             = $save_time;
            
            unset($_INV['no']);
            $rslt = DB::dataProcess('UPD', 'loan_info', $_INV, ['no'=>$_INV['loan_info_no']]);
            // 오류 업데이트 후 쪽지 발송
            if( $rslt!="Y" )
            {
                DB::rollBack();
    
                Log::debug("투자등록 실행오류#0 ".$_INV['loan_info_no']);
    
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "투자등록시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }
    
            $valcday = [];
            $valcday['loan_info_no'] = $_INV['loan_info_no'];
            $valcday['cday_date']    = $_INV['loan_date'];
            $valcday['contract_day'] = $_INV['contract_day'];
            $valcday['save_status']  = $save_status;
            $valcday['save_time']    = $save_time;
            $valcday['save_id']      = $save_id;
            $rslt = DB::dataProcess('UST', 'loan_info_cday', $valcday, ['loan_info_no'=>$valcday['loan_info_no'], 'cday_date'=>$valcday['cday_date']]);
            if( $rslt!="Y" )
            {
                DB::rollBack();
    
                Log::debug("투자등록 실행오류#1 ".$_INV['loan_info_no']);
    
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "투자등록시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }
    
            $valrate = [];
            $valrate['loan_info_no']    = $_INV['loan_info_no'];
            $valrate['rate_date']       = $_INV['loan_date'];
            $valrate['loan_rate']       = $_INV['loan_rate'];
            $valrate['loan_delay_rate'] = $_INV['loan_delay_rate'];
            $valrate['save_status']     = $save_status;
            $valrate['save_time']       = $save_time;
            $valrate['save_id']         = $save_id;
            $rslt = DB::dataProcess('UST', 'loan_info_rate', $valrate, ['loan_info_no'=>$valrate['loan_info_no'], 'rate_date'=>$valrate['rate_date']]);
            if( $rslt!="Y" )
            {
                DB::rollBack();
    
                Log::debug("투자등록 실행오류#2 ".$_INV['loan_info_no']);
    
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "투자등록시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }
    
            $valinvrate = [];
            $valinvrate['loan_info_no']    = $_INV['loan_info_no'];
            $valinvrate['rate_date']       = $_INV['loan_date'];
            $valinvrate['invest_rate']     = $_INV['invest_rate'];
            $valinvrate['save_status']     = $save_status;
            $valinvrate['save_time']       = $save_time;
            $valinvrate['save_id']         = $save_id;
            $rslt = DB::dataProcess('UST', 'loan_info_invest_rate', $valinvrate, ['loan_info_no'=>$valinvrate['loan_info_no'], 'rate_date'=>$valinvrate['rate_date']]);
            if( $rslt!="Y" )
            {
                DB::rollBack();
    
                Log::debug("투자등록 실행오류#3 ".$_INV['loan_info_no']);
    
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "투자등록시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }

            $seq = 1;
            $main_money = $_INV['loan_money'];
            foreach ($_INV['plan_date'] as $key => $value)
            {
                if(empty($value))
                {
                    continue;
                }

                $_PLAN = array();
                $_PLAN['seq']              = $seq;
                $_PLAN['plan_date']        = preg_replace('/[^0-9]/', '', $value);
                $_PLAN['plan_date_biz']    = Func::getBizDay($_PLAN['plan_date']);

				$_PLAN['plan_origin']      = isset($_INV['plan_origin'][$key]) ? preg_replace('/[^0-9]/', '', $_INV['plan_origin'][$key]) : 0;
				$_PLAN['plan_interest']    = isset($_INV['plan_interest'][$key]) ? preg_replace('/[^0-9]/', '', $_INV['plan_interest'][$key]) : 0;
				
				$_PLAN['invest_rate']      = $_INV['invest_rate'];
				$_PLAN['income_rate']	   = $_INV['income_rate'];
				$_PLAN['local_rate']	   = $_INV['local_rate'];

                // 기관차입은 원천징수가 얼마인지를 모르겠음 일단 0
                $_PLAN['income_tax']       = floor( $_PLAN['plan_interest'] * ($_PLAN['income_rate'] / 100) / 10 ) * 10;	        // 소득세 : TRUNC(세전이자금액*원천징수세율/100/10,0)*10
                $_PLAN['local_tax']        = floor( $_PLAN['income_tax'] * ($_PLAN['local_rate'] / 100) / 10 ) * 10;				// 지방소득세 : TRUNC( 소득세*0.01,0)*10
                $_PLAN['withholding_tax']  = $_PLAN['income_tax'] + $_PLAN['local_tax'];
                
                $_PLAN['plan_interest_real']    = $_PLAN['plan_interest'] - $_PLAN['withholding_tax'];
                $_PLAN['plan_money']            = $_PLAN['plan_origin'] + $_PLAN['plan_interest_real'];

                $_PLAN['loan_money']            = $_INV['loan_money'];
                $main_money                     -= $_PLAN['plan_origin'];
				$_PLAN['plan_balance']          = $main_money;
                
				$_PLAN['plan_interest_term']  	= 0;

				$_PLAN['plan_interest_sdate']	= $_INV['contract_date'];
				$_PLAN['plan_interest_edate'] 	= $_INV['contract_end_date'];
                
				$_PLAN['platform_fee_rate']     = 0;
				$_PLAN['platform_fee']          = 0;
                
                $_PLAN['loan_info_no']     = $_INV['loan_info_no'];
                $_PLAN['loan_usr_info_no'] = $_INV['loan_usr_info_no'];
                $_PLAN['inv_seq']          = $LOAN_INFO->inv_seq;

                $_PLAN['handle_code']      = $LOAN_INFO->handle_code;
                $_PLAN['pro_cd']           = $_INV['pro_cd'];
    
                $_PLAN['cust_bank_ssn']    = $LOAN_INFO->cust_bank_ssn;
    
                $_PLAN['loan_bank_cd']     = $_INV['loan_bank_cd'];
                $_PLAN['loan_bank_ssn']    = $_INV['loan_bank_ssn'];
                $_PLAN['loan_bank_name']   = $_PLAN['loan_bank_owner'] = $_INV['loan_bank_name'];

                $_PLAN['save_status']      = $save_status;
                $_PLAN['save_time']        = $save_time;
                $_PLAN['save_id']          = $save_id;
                
                $rslt = DB::dataProcess("INS", "loan_info_return_plan", $_PLAN);
                if( $rslt!="Y" )
                {
                    DB::rollBack();
        
                    Log::debug("투자등록 실행오류#4 ".$_INV['loan_info_no']);
        
                    $array_result['rs_code']    = "N";
                    $array_result['result_msg'] = "투자등록시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                    
                    return $array_result;
                }
                
                $seq++;
            }

            if(($_PLAN['plan_balance'] < 0 || $_PLAN['plan_balance'] > 0) || empty($_PLAN))
            {
                DB::rollBack();
    
                Log::debug("투자등록 실행오류#4-1 ".$_INV['loan_info_no']);
    
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "투자등록시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }
    
            // 거래내역 등록
            $_IN['trade_type']       = '11';
            $_IN['trade_date']       = $_INV['contract_date'];
            $_IN['trade_money']      = $_INV['loan_money'];
            $_IN['loan_usr_info_no'] = $_INV['loan_usr_info_no'];
            $_IN['cust_info_no']     = $_INV['cust_info_no'];
            $_IN['loan_info_no']     = $_INV['loan_info_no'];
            $_IN['trade_fee']        = 0;
    
            $t = new Trade($_INV['loan_info_no']);
            $loan_info_trade_no = $t->tradeOutInsert($_IN, Auth::id());
    
            // 오류 업데이트 후 쪽지 발송
            if(!is_numeric($loan_info_trade_no))
            {
                DB::rollBack();
    
                Log::debug("투자등록 실행오류#5 ".$_INV['loan_info_no']);
    
                $array_result['rs_code'] = "N";
                $array_result['result_msg'] = "투자등록시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }
            
            Log::info('거래내역등록 > 계약번호 : '.$_INV['loan_info_no'].', 거래내역번호 : '.$loan_info_trade_no);
        }
        else if($_INV['actMode'] == 'UPD' && $_INV['pro_cd'] != '03')
        {
            $_LOAN = [];
            $_LOAN['loan_memo'] = $_INV['loan_memo'];
            $_LOAN['save_id']   = Auth::id();
            $_LOAN['save_time'] = date("YmdHis");
            
            $rslt = DB::dataProcess('UPD', 'loan_info', $_LOAN, ['no'=>$_INV['loan_info_no']]);
            // 오류 업데이트 후 쪽지 발송
            if( $rslt!="Y" )
            {
                DB::rollBack();
    
                Log::debug("투자수정 실행오류#1 ".$_INV['loan_info_no']);
    
                $array_result['rs_code'] = "N";
                $array_result['result_msg'] = "투자수정시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }
        }
        else if($_INV['actMode'] == 'UPD' && $_INV['pro_cd'] == '03')
        {
            $ACCOUNT_INFO = DB::table("account_transfer")->select("*")->whereIn('status', ['S', 'W', 'A'])->where("loan_info_no",$_INV['loan_info_no'])->where('save_status','Y')->first();
            $ACCOUNT_INFO = Func::chungDec(["account_transfer"], $ACCOUNT_INFO);	// CHUNG DATABASE DECRYPT
    
            if(!empty($ACCOUNT_INFO->no))
            {
                Log::debug("투자수정 실행오류#0 ".$_INV['loan_info_no']);
    
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "이체실행결재에서 요청중인 송금이 존재합니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }
            
            $RETURN_PLAN = DB::TABLE("loan_info_return_plan")->SELECT("*")->WHERE("loan_info_no",$_INV['loan_info_no'])->WHERE('divide_flag','Y')->WHERE('save_status','Y')->get();// 만기연장 결재 이력 
            $RETURN_PLAN = Func::chungDec(["loan_info_return_plan"], $RETURN_PLAN);	// CHUNG DATABASE DECRYPT
            
            $return_arr = [];
            foreach($RETURN_PLAN as $v)
            {   
                $return_arr[$v->plan_date][] = $v->no;
            }
            
            $_DEL_PLAN = array();
            $_DEL_PLAN['save_status']      = "N";
            $_DEL_PLAN['del_time']         = $save_time;
            $_DEL_PLAN['del_id']           = $save_id;
    
            $_DEL_WHERE = array();
            $_DEL_WHERE['save_status']     = $save_status;
            $_DEL_WHERE['loan_info_no']    = $_INV['loan_info_no'];
            $_DEL_WHERE['divide_flag']     = 'N';
    
            $rslt = DB::dataProcess("UPD", "loan_info_return_plan", $_DEL_PLAN, $_DEL_WHERE);
            if( $rslt !="Y" )
            {
                DB::rollBack();
    
                Log::debug("투자수정 실행오류#1 ".$_INV['loan_info_no']);
    
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "투자수정시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }

            $_UPDATE = array();
            
            $seq = 1;
            $one = 'Y';
            $main_money = $LOAN_INFO->loan_money;

            foreach ($_INV['plan_date'] as $key => $value)
            {
                if(empty($value))
                {
                    continue;
                }

                $_PLAN = array();
                $_PLAN['seq']                  = $seq;
                $_PLAN['plan_date']            = preg_replace('/[^0-9]/', '', $value);

				$_PLAN['plan_origin']          = isset($_INV['plan_origin'][$key]) ? preg_replace('/[^0-9]/', '', $_INV['plan_origin'][$key]) : 0;
				$_PLAN['plan_interest']        = isset($_INV['plan_interest'][$key]) ? preg_replace('/[^0-9]/', '', $_INV['plan_interest'][$key]) : 0;

                $main_money                    -= $_PLAN['plan_origin'];
				$_PLAN['plan_balance']         = $main_money;

                if(!empty($return_arr[$_PLAN['plan_date']]))
                {
                    unset($_PLAN['plan_date']);
                    $seq++;
                    continue;
                }

                $_PLAN['plan_date_biz']        = Func::getBizDay($_PLAN['plan_date']);
				
				$_PLAN['invest_rate']          = $LOAN_INFO->invest_rate;
				$_PLAN['income_rate']	       = $LOAN_INFO->income_rate;
				$_PLAN['local_rate']	       = $LOAN_INFO->local_rate;

                // 기관차입은 원천징수가 얼마인지를 모르겠음 일단 0
                $_PLAN['income_tax']            = floor( $_PLAN['plan_interest'] * ($_PLAN['income_rate'] / 100) / 10 ) * 10;	        // 소득세 : TRUNC(세전이자금액*원천징수세율/100/10,0)*10
                $_PLAN['local_tax']             = floor( $_PLAN['income_tax'] * ($_PLAN['local_rate'] / 100) / 10 ) * 10;				// 지방소득세 : TRUNC( 소득세*0.01,0)*10
                $_PLAN['withholding_tax']       = $_PLAN['income_tax'] + $_PLAN['local_tax'];
                
                $_PLAN['plan_interest_real']    = $_PLAN['plan_interest'] - $_PLAN['withholding_tax'];
                $_PLAN['plan_money']            = $_PLAN['plan_origin'] + $_PLAN['plan_interest_real'];

                $_PLAN['loan_money']            = $LOAN_INFO->loan_money;
                
				$_PLAN['plan_interest_term']  	= 0;

				$_PLAN['plan_interest_sdate']	= $LOAN_INFO->contract_date;
				$_PLAN['plan_interest_edate'] 	= $LOAN_INFO->contract_end_date;
                
				$_PLAN['platform_fee_rate']     = 0;
				$_PLAN['platform_fee']          = 0;
                
                $_PLAN['loan_info_no']          = $LOAN_INFO->no;
                $_PLAN['loan_usr_info_no']      = $LOAN_INFO->loan_usr_info_no;
                $_PLAN['inv_seq']               = $LOAN_INFO->inv_seq;

                $_PLAN['handle_code']           = $LOAN_INFO->handle_code;
                $_PLAN['pro_cd']                = $LOAN_INFO->pro_cd;
    
                $_PLAN['cust_bank_ssn']         = $LOAN_INFO->cust_bank_ssn;
    
                $_PLAN['loan_bank_cd']          = $_INV['loan_bank_cd'] ?? $LOAN_INFO->loan_bank_cd;
                $_PLAN['loan_bank_ssn']         = $_INV['loan_bank_ssn'] ?? $LOAN_INFO->loan_bank_ssn;
                $_PLAN['loan_bank_name']        = $_PLAN['loan_bank_owner'] = $_INV['loan_bank_name'] ?? $LOAN_INFO->loan_bank_name;

                $_PLAN['save_status']           = $save_status;
                $_PLAN['save_time']             = $save_time;
                $_PLAN['save_id']               = $save_id;
                
                $rslt = DB::dataProcess("INS", "loan_info_return_plan", $_PLAN);
                if( $rslt!="Y" )
                {
                    DB::rollBack();
        
                    Log::debug("투자수정 실행오류#2 ".$_INV['loan_info_no']);
        
                    $array_result['rs_code']    = "N";
                    $array_result['result_msg'] = "투자수정시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                    
                    return $array_result;
                }

                if($one == 'Y')
                {
                    $_UPDATE['return_date']     = $_PLAN['plan_date'];
                    $_UPDATE['return_date_biz'] = $_PLAN['plan_date_biz'];
                    $_UPDATE['kihan_date']      = $LOAN_INFO->contract_end_date;
                    $_UPDATE['kihan_date_biz']  = $_UPDATE['kihan_date'];
                    $_UPDATE['return_money']    = $_PLAN['plan_money'];
                    $_UPDATE['return_origin']   = $_PLAN['plan_origin'];
                    $_UPDATE['return_interest'] = $_PLAN['plan_interest'];
                    $_UPDATE['withholding_tax'] = $_PLAN['withholding_tax'];
                    $_UPDATE['income_tax']      = $_PLAN['income_tax'];
                    $_UPDATE['local_tax']       = $_PLAN['local_tax'];
                    
                    $one = 'N';
                }
                
                $seq++;
            }

            if(($_PLAN['plan_balance'] < 0 || $_PLAN['plan_balance'] > 0) || empty($_PLAN))
            {
                DB::rollBack();
    
                Log::debug("투자수정 실행오류#2-1 ".$_INV['loan_info_no']);
    
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "투자수정시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }

            $_INV['contract_end_date'] = isset($_INV['contract_end_date']) ? preg_replace('/[^0-9]/', '', $_INV['contract_end_date']) : $LOAN_INFO->contract_end_date;
            
            // 만기일변경시?
            if(!empty($_INV['contract_end_date']) && $_INV['contract_end_date'] != $LOAN_INFO->contract_end_date)
            {
                $_UPDATE['contract_end_date'] = $_INV['contract_end_date'];
                
                if(!empty($_PLAN['plan_date']) && $_PLAN['plan_date'] > $_UPDATE['contract_end_date'])
                {
                    DB::rollBack();
        
                    Log::debug("투자수정 실행오류#2-2 ".$_PLAN['plan_date'].$_INV['loan_info_no']);
        
                    $array_result['rs_code']    = "N";
                    $array_result['result_msg'] = "투자수정시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                    
                    return $array_result;
                }
            }
            else
            {
                if(!empty($_PLAN['plan_date']) && $_PLAN['plan_date'] > $LOAN_INFO->contract_end_date)
                {
                    DB::rollBack();
        
                    Log::debug("투자수정 실행오류#2-2 ".$_INV['loan_info_no'].$_PLAN['plan_date']);
        
                    $array_result['rs_code']    = "N";
                    $array_result['result_msg'] = "투자수정시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                    
                    return $array_result;
                }
            }
            
            $_UPDATE['viewing_return_method'] = $_INV['viewing_return_method'] ?? 'F';

            $_UPDATE['loan_memo']             = $_INV['loan_memo'] ?? '';

            $rslt = DB::dataProcess('UPD', 'loan_info', $_UPDATE, ['no'=>$_INV['loan_info_no']]);
            // 오류 업데이트 후 쪽지 발송
            if( $rslt!="Y" )
            {
                DB::rollBack();
    
                Log::debug("투자수정 실행오류#3 ".$_INV['loan_info_no']);
    
                $array_result['rs_code'] = "N";
                $array_result['result_msg'] = "투자수정시 에러가 발생했습니다.(".$_INV['loan_info_no'].")";
                
                return $array_result;
            }

            $rslt = Invest::updateSavePlan($_INV['loan_info_no'], date("Ymd"));
            if( $rslt!="Y" )
			{
				return $rslt;
			}
        }
        else
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "일치하는 타입이 없습니다.(".$_INV['loan_info_no'].")";
            
            return $array_result;
        }

        $array_result['result_msg'] = "정상처리 되었습니다.";
        $array_result['rs_code'] = "Y";
        
        DB::commit();
        
        return $array_result;
    }

    /**
    * 투자내역 투자 리스트
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function investmentPaperList(Request $request)
    {
        // 메인쿼리
        $LOAN_LIST = DB::TABLE("loan_info")->join("loan_usr_info", "loan_info.loan_usr_info_no", "=", "loan_usr_info.no")
                                            ->select("loan_usr_info.name, loan_info.*")
                                            ->where('loan_info.save_status','Y')
                                            ->where("loan_info.no",$request->loan_info_no);
        
        // 정렬
        if($request->listOrder)
        {
            $LOAN_LIST = $LOAN_LIST->ORDERBY($request->listOrder, $request->listOrderAsc);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->ORDERBY('no', 'desc');
        }
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN_LIST, $request->page, $request->listLimit, 10);
        
        $LOAN_LIST = $LOAN_LIST->get();
        $LOAN_LIST = Func::chungDec(["loan_info", "LOAN_USR_INFO"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $cnt = 0;
        foreach ($LOAN_LIST as $v)
        {
            $v->investor_no_inv_seq = $v->investor_no.'-'.$v->inv_seq;

            if(isset($request->user_var) && $request->user_var==$v->no)
            {
                $v->line_style = 'background-color:#ffdddd;';
            }

            $link_c               = '<a class="hand" onClick="getInvestmentData(\'investmentpaper\',\'\',\''.$v->no.'\',\'\',\'\',\'\',\''.$request->page.'\')">';
            $v->name              = $link_c.Func::nameMasking($v->name, 'N');
            $v->contract_date     = Func::dateFormat($v->contract_date);
            $v->contract_end_date = Func::dateFormat($v->contract_end_date);
            $v->loan_money        = number_format($v->loan_money);
            $v->balance           = number_format($v->balance);
            $v->invest_rate       = sprintf('%0.2f',$v->invest_rate);
            $v->reschedule = '<button type="button" title="새로고침" class="btn btn-sm btn-info float-center mr-2" onclick="setReschedule('.$v->no.');return false;" style="height:29px;"><i class="fas fa-sync"></i></button>';

            $r['v'][] = $v;
            $cnt ++;
        }
        
        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path(), 'investmentinfo');

        $r['result'] = 1;
        $r['txt'] = $cnt;

        return json_encode($r);
    }

    public function investmentPaperAction(Request $request)
    {
        foreach($request->all() as $key => $val)
        {
            if(substr($key,-5)=="_date") $param[$key] = str_replace("-","",$val);
            else $param[$key] = $val;
        }
        
        if($param['mode']=="PAPER")
        {
            if(substr($param['post_cd'],-3)=="001") $param['docs_cd'][] = "01";         // 계약서
            else if(substr($param['post_cd'],-3)=="002") $param['docs_cd'][] = "02";    // 확약서
            else if(substr($param['post_cd'],-3)=="009") $param['docs_cd'][] = "02";    // 확약서(cnk때문에 만듬)
            else if(substr($param['post_cd'],-3)=="003") $param['docs_cd'][] = "03";    // 사모사채 양도양수 계약서
            else if(substr($param['post_cd'],-3)=="004") $param['docs_cd'][] = "04";    // 계약서 - 연장
        }
        
        DB::beginTransaction();
        foreach(Vars::$arrayInvestPaper as $doc_cd => $val)
        {
            unset($_DATA);
            
            // 처리대상일 경우 입력값을 셋팅한다.
            if(in_array($doc_cd, $param['docs_cd']))
            {
                if($param['mode']=="PAPER")
                {
                    // 이미 인쇄일이 있으면 PASS, 인쇄일이 없는 첫번째 인쇄일 경우만 데이터를 Setting한다.
                    $cnt = DB::TABLE("loan_usr_info_doc")->WHERE('loan_info_no', $param['loan_info_no'])->WHERE('app_document_cd', $doc_cd)->WHERERAW("(PRINT_DATE is not null and PRINT_DATE != '')")->COUNT();
                    if($cnt>0) continue;

                    $_DATA['loan_info_no'] = $param['loan_info_no'];
                    $_DATA['loan_info_no'] = $param['loan_info_no'];
                    $_DATA['app_document_cd'] = $doc_cd;
                    $_DATA['print_date'] = date("Ymd");
                    $_DATA['save_id'] = Auth::id();
                    $_DATA['save_status'] = 'Y';
                    $_DATA['save_time'] = date("YmdHis");
                }
                else
                {
                    $_DATA = $param;
                    $_DATA['app_document_cd'] = $doc_cd;
                    $_DATA['scan_chk'] = isset($param['scan_chk']) ? $param['scan_chk'] : "N";
                    $_DATA['keep_chk'] = isset($param['keep_chk']) ? $param['keep_chk'] : "N";
                    $_DATA['save_id'] = Auth::id();
                    $_DATA['save_status'] = 'Y';
                    $_DATA['save_time'] = date("YmdHis");
                }
            }
            // 처리대상이 아닌경우는 기본값만 입력처리
            else
            {
                $_DATA['app_document_cd'] = $doc_cd;
                $_DATA['loan_info_no'] = $param['loan_info_no'];
                $_DATA['loan_info_no'] = $param['loan_info_no'];
                $_DATA['save_status'] = 'Y';
            }

            $rslt = DB::dataProcess("UST", "loan_usr_info_doc", $_DATA, ['loan_info_no'=>$param['loan_info_no'], 'app_document_cd'=>$_DATA['app_document_cd']]);
            if($rslt!="Y")
            {
                $array_result['result_msg'] = "처리에 실패하였습니다.";
                $array_result['rs_code'] = "N";
                Log::debug('입력 에러');
                DB::rollback();
                return $array_result;
            }
        }
        DB::commit();

        $configArr      = Func::getConfigArr();
        $array_user     = Func::getUserId();
        $p = DB::TABLE('loan_usr_info_doc')->WHERE('SAVE_STATUS','Y')->WHERE("loan_info_no", $param['loan_info_no'])->ORDERBY('APP_DOCUMENT_CD')->get();
        $p = Func::chungDec(["loan_info", "LOAN_USR_INFO"], $p);	// CHUNG DATABASE DECRYPT

        $array_result['inv_document_html'] = "";
        $array_result['result_msg'] = "정상처리 되었습니다.";
        $array_result['rs_code'] = "Y";
        
        return $array_result;
    }

    /**
    * 투자계약정보창 메모 상세
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function investMemoInput(Request $request)
    {
        if($request->no!='')
        {  
            $r['mode'] = "UPD";
            $r['data'] = DB::table("loan_usr_info_memo")->select("*")->where("save_status","Y")->where("no", $request->no)->first();
            $r['data'] = Func::chungDec(["loan_usr_info_memo"], $r['data']);	// CHUNG DATABASE DECRYPT
        }
        else
        {
            $r['mode'] = "INS";
        }

        return json_encode($r);
    }

    /*
        투자계약정보창 메모 등록, 수정, 삭제

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function investMemoAction(Request $request)
    {
        $_DATA = $request->input();

        DB::beginTransaction();

        $_INS['no']               = $_DATA['no'];
        $_INS['mode']             = $_DATA['mode'];
        $_INS['loan_usr_info_no'] = $_DATA['loan_usr_info_no'];
        $_INS['loan_info_no']     = $_DATA['loan_info_no'];
        $_INS['cust_info_no']     = $_DATA['cust_info_no'];
        $_INS['memo']             = $_DATA['memo'];

        $result = Func::saveMemo($_INS, $_INS['mode'], "loan_usr_info_memo");
        
        if( $result == "Y" )
        {
            DB::commit();

            $array_result['result_msg'] = "정상적으로 등록되었습니다.";
            $array_result['rs_code'] = "Y";

            return $array_result;
        }
        else
        {
            DB::rollback();
            
            if( $result == "MN" )
            {
                $array_result['result_msg'] = "등록 오류";
                $array_result['rs_code'] = "N";

                return $array_result;
                
            }
            else
            {
                $array_result['result_msg'] = "데이터 오류";
                $array_result['rs_code'] = "N";

                return $array_result;
            }
        }
    }

    

    /**
    * 상품내역 팝업 - 첨부파일 탭
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function investmentImage(Request $request)
    {
        $request_all = $request->all();

        $configArr            = Func::getConfigArr();
        $array_user           = Func::getUserId();
        $arr_task_name        = Vars::$arrayTaskName;
        $v                    = null;
        $plans                = [];
        $img                  = [];
        $arrayInvestPaperData = [];
        $arrayPaperForm       = [];

        $loan_info_no     = $request->loan_info_no;
        $cust_info_no     = $request->cust_info_no;
        $loan_usr_info_no = $request->loan_usr_info_no;

        if(is_numeric($loan_info_no))
        {
            $v = DB::TABLE("loan_info")->join("loan_usr_info", 'loan_info.loan_usr_info_no', '=', 'loan_usr_info.no')
                                       ->select("name, zip1, addr11, addr12, zip2, addr21, addr22, loan_info.*")
                                       ->where('loan_info.save_status','Y')
                                       ->where("loan_info.no", $loan_info_no)->first();
            $v = Func::chungDec(["loan_info", "LOAN_USR_INFO"], $v);	// CHUNG DATABASE DECRYPT

            $p = DB::TABLE('loan_usr_info_doc')->WHERE('SAVE_STATUS','Y')->WHERE("loan_info_no", $loan_info_no)->ORDERBY('APP_DOCUMENT_CD')->get();
            $p = Func::chungDec(["loan_info", "LOAN_USR_INFO"], $p);	// CHUNG DATABASE DECRYPT
            foreach($p as $dv)
            {
                $arrayInvestPaperData[$dv->app_document_cd] = get_object_vars($dv);
            }

            $arrayPaperForm = Vars::$arrayPaperForm;
        }

        // 뷰 데이터 정리
        if(isset($v) && !empty($v))
        {
            $no = $v->no;
            $v->invest_rate       = sprintf('%0.2f',$v->invest_rate);
            $v->contract_date     = Func::dateFormat($v->contract_date);
            $v->contract_end_date = Func::dateFormat($v->contract_end_date);
            $v->platform_fee_rate = sprintf('%0.2f',$v->platform_fee_rate);                
            $v->balance           = isset($v->balance) ? number_format($v->balance) : 0;
        }
        
        // 이미지출력
        $arr_lon_div = Vars::$arrayLonDoc;
        $arr_cot_div = Vars::$arrayCotDoc;
        $arr_etc_div = Vars::$arrayEtcDoc;
        $arr_img_div = $arr_lon_div + $arr_cot_div + $arr_etc_div;

        $arr_task_name        = Vars::$arrayTaskName;
        $arr_image_div        = Vars::$arrayImageUploadDivision;
        $arr_image_div_select = Vars::$arrayImageUploadDivisionSelect;
        $arrManager           = Func::getUserList();

        if(!empty($v->loan_usr_info_no) || !empty($request->loan_usr_info_no))
        {
            $img = DB::TABLE("loan_usr_info_img")->JOIN('loan_info', 'loan_usr_info_img.loan_info_no', '=', 'loan_info.no')
                                                ->SELECT("loan_usr_info_img.*", "loan_info.inv_seq", "loan_info.investor_no", "loan_info.investor_type")
                                                ->WHERE('loan_usr_info_img.save_status','Y')
                                                ->WHERE('loan_info.save_status','Y')
                                                ->ORDERBY('loan_usr_info_img.save_time', 'desc');

            if(!empty($v->loan_usr_info_no))
            {
                $loan_usr_info_no = $v->loan_usr_info_no;
                $loan_info_no     = $v->no;
            }
            else
            {
                $loan_usr_info_no = $request->loan_usr_info_no;
                $loan_info_no     = $v->no;
            }

            $img = $img->WHERE('loan_usr_info_img.loan_usr_info_no', $loan_usr_info_no)->WHERE('loan_usr_info_img.loan_info_no', $loan_info_no)->get()->toArray();
            $img = Func::chungDec(["loan_usr_info_img"], $img);	// CHUNG DATABASE DECRYPT
        }

        $selected_img = array();
        if(isset($request->no))
        {
            $mode = "UPD";
            foreach( $img as $key=>$value )
            {
                if( $value->no == $request->no )
                {
                    $selected_img[0] = $img[$key];
                }
            }
        }
        else
        {
            $mode = "INS";
        }

        // listName : 리스트 이름 (표시 x)
        $result['listName'] = 'investmentimage';
        // listAction : 리스트 url - ajax 요청주소 ( '/'.$request->path() )
        $result['listAction'] = '/'.$request->path();

        // tabs : 탭 사용 여부 (Y, N)
        $result['tabs'] = 'N';

        // button : 버튼 추가 여부 (Y, N)
        $result['button'] = 'N';

        // searchDate : 일자검색 여부 (Y, N)
        $result['searchDate'] = 'N';
        // searchType : select 검색 여부 (Y, N) [searchDetail과 다른 점은 input 입력하는 부분이 없다.]
        $result['searchType'] = 'N';

        // searchDetail : 검색 사용 여부 (Y, N)
        $result['searchDetail'] = 'X';
        // searchButton : 검색버튼 사용 여부 (Y, N)
        // isModal : 모달창 사용여부 (Y, N)
        $result['isModal'] = 'N';

        // plusButton : 등록 버튼 추가 여부 (Y, N)
        $result['plusButton'] = 'N';        
        
        if(isset($loan_info_no) && isset($cust_info_no) && isset($loan_usr_info_no))
        {
            $result['customer']['loan_info_no']     = $loan_info_no;
            $result['customer']['cust_info_no']     = $cust_info_no;
            $result['customer']['loan_usr_info_no'] = $loan_usr_info_no;
        }

        $result['page'] = $request->page ?? 1;

        Log::debug(print_r($selected_img, true));
        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $result['listTitle'] = Array
        (
            // 'investor_no_inv_seq'      => Array('채권번호', 1, '', 'center', '', ''),
            // 'name'                     => Array('투자자명', 1, '', 'center', '', 'name'),
            // 'contract_date'            => Array('시작일자', 1, '', 'center', '', 'contract_date'),
            // 'contract_end_date'        => Array('만기일자', 1, '', 'center', '', 'contract_end_date'),
            // 'loan_money'               => Array('투자금', 1, '', 'center', '', 'loan_money'),
            // 'balance'                  => Array('투자잔액', 1, '', 'center', '', 'balance'),
            // 'invest_rate'              => Array('수익률', 1, '', 'center', '', '')
        );

        // listlimit : 한페이지 출력 건수
        $result['listlimit'] = "5";

        return view('account.investmentImage')->with('v', $v)
                                        ->with('arr_task_name', $arr_task_name)
                                        ->with('arr_image_div', $arr_image_div)
                                        ->with('arr_image_div_select', $arr_image_div_select)
                                        ->with('arrayDocData',$arrayInvestPaperData)
                                        ->with('array_user',$array_user)
                                        ->with('configArr',$configArr)
                                        ->with("result",    $result)
                                        ->with("userVar",   $no)
                                        ->with("paperForm", $arrayPaperForm)
                                        ->with('selected_img', $selected_img)
                                        ->with('mode', $mode)
                                        ->with('img', $img);
    }


    public function investmentImageAction(Request $request)
    {
        $_DATA = $request->input();
        $_FILE_DATA = $request->file('customFile');

        DB::beginTransaction();

        if($_DATA['mode'] == 'DEL')
        {
            $_INS['no']               = $_DATA['no'];
            $_INS['mode']             = $_DATA['mode'];
            $_INS['loan_usr_info_no'] = $_DATA['loan_usr_info_no'];
            $_INS['loan_info_no']     = $_DATA['loan_info_no'];
            $_INS['cust_info_no']     = $_DATA['cust_info_no'];
            $_INS['memo']             = $_DATA['memo'];
            $_INS['img_div_cd']       = $_DATA['img_div_cd'];          
        }
        else {
            if($_FILE_DATA == null) {
                $img = DB::table("loan_usr_info_img")->select("*")->where("save_status","Y")->where("no", $request->no)->first();
                $file_name = $img->origin_filename;
                $file_path = $img->file_path;
                $current_date = date('Ymd');
                $extension = $img->extension;
            }
            else {
                $file_name = $_FILE_DATA->getClientOriginalName();
                $current_date = date('Ymd');
                $file_path = Storage::disk('erp_data_usr_img')->put($current_date, $_FILE_DATA);
                $extension = $_FILE_DATA->getClientOriginalExtension();
            }
            $_INS['no']               = $_DATA['no'];
            $_INS['mode']             = $_DATA['mode'];
            $_INS['loan_usr_info_no'] = $_DATA['loan_usr_info_no'];
            $_INS['loan_info_no']     = $_DATA['loan_info_no'];
            $_INS['cust_info_no']     = $_DATA['cust_info_no'];
            $_INS['memo']             = $_DATA['memo'];
            $_INS['img_div_cd']       = $_DATA['img_div_cd'];
            $_INS['origin_filename']  = $file_name;
            $_INS['file_path']        = $file_path;
            $_INS['filename']         = $file_name;
            $_INS['folder_name']      = $current_date;
            $_INS['extension']        = $extension;
        }

        $result = Func::saveFile($_INS, $_INS['mode'], "loan_usr_info_img");
        
        if( $result == "Y" )
        {
            DB::commit();

            $array_result['result_msg'] = "정상적으로 등록되었습니다.";
            $array_result['rs_code'] = "Y";

            return $array_result;
        }
        else
        {
            DB::rollback();
            
            if( $result == "MN" )
            {
                $array_result['result_msg'] = "등록 오류";
                $array_result['rs_code'] = "N";

                return $array_result;
                
            }
            else
            {
                $array_result['result_msg'] = "데이터 오류";
                $array_result['rs_code'] = "N";

                return $array_result;
            }
        }
    }


    /*
        투자계약정보창 메모 등록, 수정, 삭제

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function investmentImageList(Request $request)
    {
        // 메인쿼리
        $LOAN_LIST = DB::TABLE("loan_info")->join("loan_usr_info", "loan_info.loan_usr_info_no", "=", "loan_usr_info.no")
                                            ->select("loan_usr_info.name, loan_info.*", 
                                            DB::raw("(select count(1) from loan_usr_info_doc where loan_usr_info_doc.loan_info_no = loan_info.no and app_document_cd = '01' and save_id is not null and save_status = 'Y') as conp_cnt"), 
                                            DB::raw("(select count(1) from loan_usr_info_doc where loan_usr_info_doc.loan_info_no = loan_info.no and app_document_cd = '04' and save_id is not null and save_status = 'Y') as conep_cnt"), 
                                            DB::raw("(select count(1) from loan_usr_info_doc where loan_usr_info_doc.loan_info_no = loan_info.no and app_document_cd = '02' and save_id is not null and save_status = 'Y') as comp_cnt")
                                            )
                                            ->where('loan_info.save_status','Y')
                                            ->where("loan_info.no",$request->loan_info_no);
        
        // 정렬
        if($request->listOrder)
        {
            $LOAN_LIST = $LOAN_LIST->ORDERBY($request->listOrder, $request->listOrderAsc);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->ORDERBY('no', 'desc');
        }
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN_LIST, $request->page, $request->listLimit, 10);
        
        // 결과
        Log::info("#########쿼리 확인 :".Func::printQuery($LOAN_LIST));
        $LOAN_LIST = $LOAN_LIST->get();
        $LOAN_LIST = Func::chungDec(["loan_info", "LOAN_USR_INFO"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $cnt = 0;
        foreach ($LOAN_LIST as $v)
        {
            $v->investor_no_inv_seq = $v->investor_no.'-'.$v->inv_seq;

            $link_c               = '<a class="hand" onClick="getInvestmentData(\'investmentimage\',\'\',\''.$v->no.'\',\'\',\'\',\'\',\''.$request->page.'\')">';
            $v->name              = $link_c.Func::nameMasking($v->name, 'N');
            $v->contract_date     = Func::dateFormat($v->contract_date);
            $v->contract_end_date = Func::dateFormat($v->contract_end_date);
            $v->loan_money        = number_format($v->loan_money);
            $v->balance           = number_format($v->balance);
            $v->invest_rate       = sprintf('%0.2f',$v->invest_rate);
            $v->reschedule = '<button type="button" title="새로고침" class="btn btn-sm btn-info float-center mr-2" onclick="setReschedule('.$v->no.');return false;" style="height:29px;"><i class="fas fa-sync"></i></button>';

            $r['v'][] = $v;
            $cnt ++;
        }
        
        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path(), 'investmentimage');

        $r['result'] = 1;
        $r['txt']    = $cnt;

        return json_encode($r);
    }

    /**
    * 투자계약정보창 메모 상세
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function investmentImageInput(Request $request)
    {
        if($request->no!='')
        {  
            $r['mode'] = "UPD";
            $r['data'] = DB::table("loan_usr_info_img")->select("*")->where("save_status","Y")->where("no", $request->no)->first();
            $r['data'] = Func::chungDec(["loan_usr_info_img"], $r['data']);	// CHUNG DATABASE DECRYPT
        }
        else
        {
            $r['mode'] = "INS";
        }

        return json_encode($r);
    }

    /**
    * 양식인쇄
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function investmentPaper(Request $request)
    {
        $configArr            = Func::getConfigArr();
        $array_user           = Func::getUserId();
        $v                    = null;
        $plans                = [];
        $arrayInvestPaperData = [];
        $arrayPaperForm       = [];
        $arrayPaper           = [];

        $loan_info_no     = $request->loan_info_no;
        $cust_info_no     = $request->cust_info_no;
        $loan_usr_info_no = $request->loan_usr_info_no;

        $i                = 0;

        log::info("계약번호 : ".$request->loan_info_no.", 투자일련번호 : ".$request->loan_info_no.", 구분 : ".$request->div."");


        if(is_numeric($loan_info_no))
        {
            $v = DB::TABLE("loan_info")->join("loan_usr_info", "loan_info.loan_usr_info_no", "=", "loan_usr_info.no")->SELECT("NAME, ZIP1, ADDR11, ADDR12, ZIP2, ADDR21, ADDR22, LOAN_INFO.*")->WHERE('loan_info.save_status','Y')->WHERE("loan_info.no", $loan_info_no)->first();
            $v = Func::chungDec(["loan_info", "LOAN_USR_INFO"], $v);	// CHUNG DATABASE DECRYPT

            $p = DB::TABLE('loan_usr_info_doc')->WHERE('SAVE_STATUS','Y')->WHERE("loan_info_no", $loan_info_no)->ORDERBY('APP_DOCUMENT_CD')->get();
            $p = Func::chungDec(["loan_info", "LOAN_USR_INFO"], $p);	// CHUNG DATABASE DECRYPT
            foreach($p as $dv)
            {
                $arrayInvestPaperData[$dv->app_document_cd] = get_object_vars($dv);
                if($dv->app_document_cd == 01){
                    $arrayPaper['ContractPrint_' . $i] = get_object_vars($dv);
                }
                if($dv->app_document_cd == 02){
                    $arrayPaper['CommitmentPrint_' . $i] = get_object_vars($dv);
                }
                if($dv->app_document_cd == 04){
                    $arrayPaper['ContractPrintExtension_' . $i] = get_object_vars($dv);
                }
                $i++;
            }

            $arrayPaperForm = Vars::$arrayPaperForm;
        }

        // 뷰 데이터 정리
        if(isset($v) && !empty($v))
        {
            $no = $v->no;
            $v->invest_rate       = sprintf('%0.2f',$v->invest_rate);
            $v->contract_date     = Func::dateFormat($v->contract_date);
            $v->contract_end_date = Func::dateFormat($v->contract_end_date);
            $v->platform_fee_rate = sprintf('%0.2f',$v->platform_fee_rate);                
            $v->balance           = isset($v->balance) ? number_format($v->balance) : 0;
        }

        // listName : 리스트 이름 (표시 x)
        $result['listName'] = 'investmentinfo';
        // listAction : 리스트 url - ajax 요청주소 ( '/'.$request->path() )
        $result['listAction'] = '/'.$request->path();

        // tabs : 탭 사용 여부 (Y, N)
        $result['tabs'] = 'N';

        // button : 버튼 추가 여부 (Y, N)
        $result['button'] = 'N';

        // searchDate : 일자검색 여부 (Y, N)
        $result['searchDate'] = 'N';
        // searchType : select 검색 여부 (Y, N) [searchDetail과 다른 점은 input 입력하는 부분이 없다.]
        $result['searchType'] = 'N';

        // searchDetail : 검색 사용 여부 (Y, N)
        $result['searchDetail'] = 'X';
        // searchButton : 검색버튼 사용 여부 (Y, N)
        // isModal : 모달창 사용여부 (Y, N)
        $result['isModal'] = 'N';

        // plusButton : 등록 버튼 추가 여부 (Y, N)
        $result['plusButton'] = 'N';        
        
        if(isset($loan_info_no) && isset($cust_info_no) && isset($loan_usr_info_no))
        {
            $result['customer']['loan_info_no']     = $loan_info_no;
            $result['customer']['cust_info_no']     = $cust_info_no;
            $result['customer']['loan_usr_info_no'] = $loan_usr_info_no;
        }

        $result['page'] = $request->page ?? 1;

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $result['listTitle'] = Array
        (
            'loan_info_usr_no_inv_seq'  => Array('채권번호', 1, '', 'center', '', ''),
            'name'                      => Array('투자자명', 1, '', 'center', '', 'name'),
            'contract_date'             => Array('시작일자', 1, '', 'center', '', 'contract_date'),
            'contract_end_date'         => Array('만기일자', 1, '', 'center', '', 'contract_end_date'),
            'loan_money'                => Array('투자금액', 1, '', 'center', '', 'loan_money'),
            'balance'                   => Array('투자잔액', 1, '', 'center', '', 'balance'),
            'invest_rate'               => Array('수익률', 1, '', 'center', '', 'invest_rate'),
        );

        // listlimit : 한페이지 출력 건수
        $result['listlimit'] = "5";
        
        return view('account.investmentPaper')->with('v', $v)
                                    ->with('arrayDocData',$arrayInvestPaperData)
                                    ->with('arrayPaper',$arrayPaper)
                                    ->with('array_user',$array_user)
                                    ->with('configArr',$configArr)
                                    ->with("result",    $result)
                                    ->with("userVar",   $no)
                                    ->with("paperForm", $arrayPaperForm);
    }

    /**
     * 상품내역 팝업창 - 문자
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investmentSms(Request $request)
    {
        $array_user         = Func::getUserId();
        $array_config       = Func::getConfigArr();
        $array_ph_chk       = Array('ph1' => '전화번호1', 'ph2' =>'전화번호2', 'ph4' =>'전화번호3');
        $array_addr_chk     = Array('addr1' => '주소1', 'addr2' =>'주소2');
        $array_bank_chk     = Array('bank1' => '은행/계좌번호1', 'bank2' =>'은행/계좌번호2', 'bank3' =>'은행/계좌번호3');
        $chain_memo_div     = Func::getConfigChain('memo_div');
        $arr_memo_div       = $array_config['memo_div'];
        $arr_relation_cd    = $array_config['relation_cd'];
        $arr_ph_cd          = $array_config['phone_cd'];
        $array_sms_cd       = $array_config['sms_erp_cd'];

        $mode = '';
        $v = null;
        $memos = [];
        $maxSeq = 0;

        $loan_info_no     = $request->loan_info_no;
        $cust_info_no     = $request->cust_info_no;
        $loan_usr_info_no = $request->loan_usr_info_no;
        
        if(isset($loan_info_no) && isset($cust_info_no))
        {
            $result['customer']['loan_info_no']     = $loan_info_no;
            $result['customer']['cust_info_no']     = $cust_info_no;
            $result['customer']['loan_usr_info_no'] = $loan_usr_info_no;
        }

        $INV = DB::TABLE("loan_info")->SELECT("*")
                                        ->WHERE('save_status','Y')
                                        ->WHERE("no",$loan_info_no)
                                        ->GET();

        $INV = Func::chungDec(["loan_info"], $INV);	// CHUNG DATABASE DECRYPT
        return view('account.investmentSms')->with('memos', $memos)
                                    ->with('array_user',$array_user)
                                    ->with('array_sms_cd', $array_sms_cd)
                                    ->with("result",    $result)
                                    ->with("mode",      $mode)
                                    ->with("userVar",   $loan_info_no)
                                    ->with("configArr", $array_config)
                                    ->with("ph_chk",    $array_ph_chk)
                                    ->with("addr_chk",  $array_addr_chk)
                                    ->with("bank_chk", $array_bank_chk)
                                    ->with("arr_memo_div", $arr_memo_div)
                                    ->with("arr_relation_cd", $arr_relation_cd)
                                    ->with("chain_memo_div", $chain_memo_div)
                                    ->with("arr_ph_cd", $arr_ph_cd);
    }

    /**
     * 상품내역 팝업창 - 문자리스트
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investmentSmsView(Request $request)
    {
        $param = $request->all();
        $str = '';
        
        $array_config = Func::getConfigArr();
        $target_divide_date = str_replace("-","",$param['target_divide_date']);
        
        $rslt = DB::table('sms_msg')->SELECT('no', 'sms_div','sms_type', 'code_div', 'message')
                                    ->WHERE('save_status', 'Y')
                                    ->WHERE('sms_div', $param['sms_div'])
                                    ->ORDERBY('sms_div')
                                    ->ORDERBY('no')
                                    ->FIRST();
        $rslt = Func::chungDec(["SMS_MSG"], $rslt);	// CHUNG DATABASE DECRYPT


        $INV = DB::TABLE("loan_info")->JOIN("loan_usr_info", "loan_info.loan_usr_info_no", "=", "loan_usr_info.no")
                                    ->SELECT("loan_info.*, LOAN_USR_INFO.NAME, LOAN_USR_INFO.ZIP1, LOAN_USR_INFO.ADDR11, LOAN_USR_INFO.ADDR12")
                                    ->WHERE('loan_info.save_status','Y')
                                    ->WHERE("loan_info.no",$param['loan_info_no'])
                                    ->get();

        $INV = Func::chungDec(["LOAN_USR_INFO", "LOAN_INFO"], $INV);	// CHUNG DATABASE DECRYPT

        $cnt=0;
        foreach( $INV as $v)
        {
            $loan_usr_info_no = $v->loan_usr_info_no;
            $loan_info_no     = $v->loan_info_no;
            $name             = $v->name;

            // 선택된 기준일자에서 수익분배 처리된 내용만 문자내용이 출력되도록 체크
            $sql_ck = DB::TABLE("loan_info_return_plan")->JOIN("loan_info", "loan_info.NO", "=", "loan_info_return_plan.loan_info_no")
                                                    ->SELECT("loan_info_return_plan.divide_flag")
                                                    ->WHERE("loan_info_no", $v->no)
                                                    ->WHERE("divide_date", $target_divide_date)
                                                    ->ORDERBY('divide_date')
                                                    ->FIRST();

            $v = json_encode($v);

            if(isset($sql_ck->divide_flag) && $sql_ck->divide_flag=="Y")
            {
                $msg = Sms::msgParser($loan_info_no, $rslt->message, 'account', "", $v, $array_config, $param);
    
                $str.= '<div class="card card-secondary mt-2 ml-4" style="width:210px;">';
                $str.= '<input type="button" value="'.$name.'" onclick=Lbcopy(\'message_'.$cnt.'\')>';
                $str.= '<textarea id=message_'.$cnt.' name=message_'.$cnt.' rows="8" style="border:0px; background-color:#fff6db;">'.$msg.'</textarea>';
                $str.= '</div>';
                
                $cnt++;
            }
        }
        if($str=="")
        {
            $str="false";
        }

        return $str;
    }

    /**
     * 상품내역 팝업창 - 문자
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investmentMemo(Request $request)
    {
        $array_user         = Func::getUserId();
        $array_config       = Func::getConfigArr();
        $array_ph_chk       = Array('ph1' => '전화번호1', 'ph2' =>'전화번호2', 'ph4' =>'전화번호3');
        $array_addr_chk     = Array('addr1' => '주소1', 'addr2' =>'주소2');
        $array_bank_chk     = Array('bank1' => '은행/계좌번호1', 'bank2' =>'은행/계좌번호2', 'bank3' =>'은행/계좌번호3');
        $chain_memo_div     = Func::getConfigChain('memo_div');
        $arr_memo_div       = $array_config['memo_div'];
        $arr_relation_cd    = $array_config['relation_cd'];
        $arr_ph_cd          = $array_config['phone_cd'];
        $array_sms_cd       = $array_config['sms_erp_cd'];

        $mode = '';
        $v = null;
        $memos = [];
        $maxSeq = 0;

        $loan_info_no = $request->loan_info_no;
        $cust_info_no = $request->cust_info_no;
        $loan_usr_info_no = $request->loan_usr_info_no;
        
        if(isset($loan_info_no) && isset($loan_info_no))
        {
            $result['customer']['loan_info_no']     = $loan_info_no;
            $result['customer']['cust_info_no']     = $cust_info_no;
            $result['customer']['loan_usr_info_no'] = $loan_usr_info_no;
        }

        $memos = DB::TABLE("loan_usr_info_memo")->JOIN("loan_info", "loan_info.no", "=", "loan_usr_info_memo.loan_info_no")
                            ->SELECT("loan_usr_info_memo.*", "loan_info.inv_seq", "loan_info.investor_no", "loan_info.investor_type")
                            ->WHERE("loan_usr_info_memo.loan_info_no", $loan_info_no)
                            ->WHERE("loan_usr_info_memo.save_status", "Y")
                            ->ORDERBY("no", "desc")
                            ->GET();

        $memos = Func::chungDec(["loan_usr_info_memo"], $memos);	        // CHUNG DATABASE DECRYPT

        $INV = DB::TABLE("loan_info")->SELECT("*")
                                        ->WHERE('save_status','Y')
                                        ->WHERE("no",$loan_info_no)
                                        ->GET();
        $INV = Func::chungDec(["loan_info"], $INV);	                    // CHUNG DATABASE DECRYPT

        return view('account.investmentMemo')->with('memos', $memos)
                                    ->with('array_user',$array_user)
                                    ->with('array_sms_cd', $array_sms_cd)
                                    ->with("result",    $result)
                                    ->with("mode",      $mode)
                                    ->with("userVar",   $loan_info_no)
                                    ->with("configArr", $array_config)
                                    ->with("ph_chk",    $array_ph_chk)
                                    ->with("addr_chk",  $array_addr_chk)
                                    ->with("bank_chk", $array_bank_chk)
                                    ->with("arr_memo_div", $arr_memo_div)
                                    ->with("arr_relation_cd", $arr_relation_cd)
                                    ->with("chain_memo_div", $chain_memo_div)
                                    ->with("arr_ph_cd", $arr_ph_cd);
    }

    /**
     * 투자스케줄 엑셀업로드 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function excelUploadSample(Request $request)
    {
        if(Storage::disk('investment')->exists('sample.xlsx'))
        {
            return Storage::disk('investment')->download('sample.xlsx', '투자스케줄예시파일.xlsx');
        }
        else
        {
            log::debug("샘플파일 없음");
        }
    }

    /**
     * 투자스케줄 엑셀업로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function excelUploadFormAction(Request $request)
    {
        $string = "";
        $count  = 0;
        $loan_money = isset($request->loan_money) ? preg_replace('/[^0-9]/', '', $request->loan_money) : 0;
        $sum_plan_origin = $sum_plan_interest = $sum_withholding_tax = $sum_income_tax = $sum_local_tax = $sum_plan_money = 0;

        if( $request->file('excel_data') )
        {
            // 저장
            $file_path = $request->file('excel_data')->store("upload/".date("Ymd"), 'investment');
            
            // 경로세팅 
            if(Storage::disk('investment')->exists($file_path))
            {
                $colHeader  = array("회차",
                                    "수익지급일",
                                    "상환원금",
                                    "상환이자"
                                    );
                $colNm      = array(
                                    "seq"	          => "0",	      // 회차
                                    "plan_date"	      => "1",	      // 수익지급일
                                    "plan_origin"     => "2",         // 상환원금
                                    "plan_interest"   => "3",         // 상환이자
                                    );

                $file = Storage::path('/investment/'.$file_path);
                
                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    // 파일삭제
                    Storage::disk('investment')->delete($file_path);

                    $r['rs_code'] = "N";
                    $r['rs_msg']  = "엑셀 유효성 검사를 실패하였습니다.";

                    return $r;
                }
                else
                {
                    foreach($excelData as $_DATA) 
                    {
                        unset($_UPD);

                        // 데이터 정리
                        foreach($_DATA as $key => $val) 
                        {
                            $val = trim($val);
                            $_UPD[$key] = $val;
                        }

                        // 데이터 추출 및 정리
                        foreach($_UPD as $key => $val)
                        {
                            // 값이 없으면 unset
                            if($val == "")
                            {
                                unset($_UPD[$key]);
                                continue;
                            }

                            // 값이 null 이면 빈값으로 업데이트
                            if($val == "null" || $val == "NULL")
                            {
                                $_UPD[$key] = '';
                                continue;
                            }

                            $_UPD[$key] = preg_replace('/[^0-9]/', '', $val);
                        }

                        if(!empty($_UPD['seq']))
                        {
                            $plan_balance = $loan_money - $_UPD['plan_origin'];
                            $income_rate = $request->income_rate ?? 0;     //소득세율
                            $local_rate = $request->local_rate ?? 0;       //지방소득세율

                            // 기관차입은 원천징수가 얼마인지를 모르겠음 일단 0
                            $_UPD['income_tax']      = floor( $_UPD['plan_interest'] * ($income_rate / 100) / 10 ) * 10;	        // 소득세 : TRUNC(세전이자금액*원천징수세율/100/10,0)*10
                            $_UPD['local_tax']       = floor( $_UPD['income_tax'] * ($local_rate / 100) / 10 ) * 10;				// 지방소득세 : TRUNC( 소득세*0.01,0)*10
                            $_UPD['withholding_tax'] = $_UPD['income_tax'] + $_UPD['local_tax'];
                            $_UPD['plan_interest_real'] = $_UPD['plan_interest'] - $_UPD['withholding_tax'];
                            $_UPD['plan_money']      = $_UPD['plan_origin'] + $_UPD['plan_interest_real'];
                            
                            $string.= '<tr>';
                            $string.= '<td class="text-center">'.$_UPD['seq'].'</td>';
                            $string.= '<td class="text-center" colspan="2"><div class="row"><div class="col-md-10 m-0 pr-0">';
                            $string.= '<div class="input-group date datetimepicker" id="plan_date_div'.$_UPD['seq'].'" data-target-input="nearest">';
                            $string.= '<input type="text" class="form-control form-control-sm dateformat" name="plan_date[]" id="plan_date[]" inputmode="text" value="'.Func::dateFormat($_UPD['plan_date']).'">';
                            $string.= '<div class="input-group-append" data-target="#plan_date_div'.$_UPD['seq'].'" data-toggle="datetimepicker">';
                            $string.= '<div class="input-group-text"><i class="fa fa-calendar"></i></div></div></div></div></div></td>';
                            $string.= '<td class="text-center">'.Func::dateFormat(Func::getBizDay($_UPD['plan_date'])).'('.Vars::$arrayWeekDay[date('w',Func::dateToUnixtime(Func::getBizDay($_UPD['plan_date'])))].')</td>';
                            $string.= '<td class="text-right" id="td_plan_balance'.$_UPD['seq'].'">
                                            <input type="hidden" id="plan_balance'.$_UPD['seq'].'" name="plan_balance[]" value="'.number_format($plan_balance).'">'.number_format($plan_balance).'
                                       </td>';
                            $string.= '<td class="text-right"><input type="text" class="form-control form-control-sm text-right moneyformat" id="plan_origin'.$_UPD['seq'].'" name="plan_origin[]" placeholder="원단위 입력" onkeyup="setInput('.$_UPD['seq'].');" value="'.number_format($_UPD['plan_origin']).'"></td>';
                            $string.= '<td class="text-right"><input type="text" class="form-control form-control-sm text-right moneyformat" id="plan_interest'.$_UPD['seq'].'" name="plan_interest[]" placeholder="원단위 입력" onkeyup="setInput('.$_UPD['seq'].');" value="'.number_format($_UPD['plan_interest']).'"></td>';
                            $string.= '<td class="text-right" id="td_withholding_tax'.$_UPD['seq'].'">
                                            <input type="hidden" id="withholding_tax'.$_UPD['seq'].'" name="withholding_tax[]" value="'.number_format($_UPD['withholding_tax']).'">'.number_format($_UPD['withholding_tax']).'
                                       </td>';
                            $string.= '<td class="text-right" id="td_income_tax'.$_UPD['seq'].'">
                                        <input type="hidden" id="income_tax'.$_UPD['seq'].'" name="income_tax[]" value="'.number_format($_UPD['income_tax']).'">'.number_format($_UPD['income_tax']).'
                                       </td>';
                            $string.= '<td class="text-right" id="td_local_tax'.$_UPD['seq'].'">
                                        <input type="hidden" id="local_tax'.$_UPD['seq'].'" name="local_tax[]" value="'.number_format($_UPD['local_tax']).'">'.number_format($_UPD['local_tax']).'
                                       </td>';
                            $string.= '<td class="text-right" id="td_plan_money'.$_UPD['seq'].'">
                                            <input type="hidden" id="plan_money'.$_UPD['seq'].'" name="plan_money[]" value="'.number_format($_UPD['plan_money']).'">'.number_format($_UPD['plan_money']).'
                                       </td>';
                            $string.= '<td class="text-center"><div class="row"><div class="col-sm-5 m-0 pr-0">';
                            $string.= '<button type="button" class="btn btn-default btn-sm float-center mr-2 addbtn" onclick="addRow(this);"><i class="fa fa-xs fa-plus-square text-info"></i></button></div><div class="col-sm-5 m-0 pr-0">';
                            $string.= '<button type="button" class="btn btn-default btn-sm float-center mr-2 delbtn" onclick="delRow(this);"><i class="fa fa-xs fa-minus-square text-danger"></i></button></div>';
                            $string.= '</div></td></tr>';
                            
                            $sum_plan_origin+= $_UPD['plan_origin'];
                            $sum_plan_interest+= $_UPD['plan_interest'];
                            $sum_withholding_tax+= $_UPD['withholding_tax'];
                            $sum_income_tax+= $_UPD['income_tax'];
                            $sum_local_tax+= $_UPD['local_tax'];
                            $sum_plan_money+= $_UPD['plan_money'];

                            $count ++;
                            $loan_money = $plan_balance;
                        }
                    }
    
                    $string.= '<tr class="bg-secondary">';
                    $string.= '<td class="text-center" id="td_sum" ></td>';
                    $string.= '<td class="text-center" colspan="4">합계</td>';
                    $string.= '<td class="text-right" id="td_tot_plan_origin">'.number_format($sum_plan_origin).'</td>';
                    $string.= '<td class="text-right" id="td_tot_plan_interest">'.number_format($sum_plan_interest).'</td>';
                    $string.= '<td class="text-right" id="td_tot_withholding_tax">'.number_format($sum_withholding_tax).'</td>';
                    $string.= '<td class="text-right" id="td_tot_income_tax">'.number_format($sum_income_tax).'</td>';
                    $string.= '<td class="text-right" id="td_tot_local_tax">'.number_format($sum_local_tax).'</td>';
                    $string.= '<td class="text-right" id="td_tot_plan_money">'.number_format($sum_plan_money).'</td>';
                    $string.= '<td class="text-center"></td>';

                    $string.= '</tr>';
                }

                // 파일삭제
                Storage::disk('investment')->delete($file_path);

                $r['rs_code'] = "Y";
                $r['rs_data'] = $string;
                $r['rs_cnt']  = $count;
                $r['rs_msg']  = "파일 저장을 성공하였습니다.";

                return $r;
            }
            else 
            {
                $r['rs_code'] = "N";
                $r['rs_msg']  = "파일 저장을 실패했습니다.";

                return $r;
            }
        }
        else
        {
            $r['rs_code'] = "N";
            $r['rs_msg']  = "엑셀을 등록해주세요.";

            return $r;
        }
    }

    /**
     * 투자계약관리 삭제 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investmentLumpDelete(Request $request)
    {
        $val = $request->input();
        
        $s_cnt = 0;
        $arr_fail = Array();

        $del_id   = Auth::id();
        $del_time = date("YmdHis");

        if( $val['action_mode']=="investment_DELETE" && is_array($val['listChk']) && sizeof($val['listChk'])>0 )
        {
            for( $i=0; $i<sizeof($val['listChk']); $i++ )
            {
                $loan_info_no = $val['listChk'][$i];

                DB::beginTransaction();

                $account_transfer = DB::table('account_transfer')->join("loan_info","loan_info.no","=","account_transfer.loan_info_no")
                                                    ->join("cust_info","cust_info.no","=","loan_info.cust_info_no")
                                                    ->join("loan_usr_info", "loan_info.loan_usr_info_no", "=", "loan_usr_info.no")
                                                    ->where('account_transfer.loan_info_no', $loan_info_no)
                                                    ->whereIn('account_transfer.status', ['S', 'W', 'A'])
                                                    ->where('loan_info.save_status', 'Y')
                                                    ->where('cust_info.save_status', 'Y')
                                                    ->where('loan_usr_info.save_status', 'Y')
                                                    ->where('account_transfer.save_status', 'Y')
                                                    ->orderby('account_transfer.no')
                                                    ->first();
                $account_transfer = Func::chungDec(["account_transfer"], $account_transfer);	// CHUNG DATABASE DECRYPT

                if(!empty($account_transfer->no))
                {
                    DB::rollback();

                    $arr_fail[$loan_info_no] = "이체실행결재에서 현재 요청중인 송금이 있습니다.(".$account_transfer->no.")";
                    continue;
                }

                $tradeLoanInfo = DB::table("loan_info_trade")->where("TRADE_DIV", 'I')->where("loan_info_no", $loan_info_no)->where("save_status", 'Y')->first();
                $tradeLoanInfo = Func::chungDec(["loan_info_trade"], $tradeLoanInfo);	// CHUNG DATABASE DECRYPT

                if(!empty($tradeLoanInfo->no))
                {
                    DB::rollback();

                    $arr_fail[$loan_info_no] = "수입지급내역이 있습니다.(".$tradeLoanInfo->no.")";
                    continue;
                }

                $_END['save_status'] = 'N';
                $_END['del_id']      = $del_id;
                $_END['del_time']    = $del_time;

                $newLoanInfo = DB::table("loan_info")->where("no", $loan_info_no)->where("save_status", 'Y')->first();
                $newLoanInfo = Func::chungDec(["loan_info"], $newLoanInfo);	// CHUNG DATABASE DECRYPT
                
                if(!empty($newLoanInfo->no) && ($newLoanInfo->status != 'N'))
                {
                    $t = new Trade($newLoanInfo->no);
                    $rslt = $t->tradeOutDelete($newLoanInfo->loan_info_trade_no);
                    // 오류 업데이트 후 쪽지 발송
                    if( is_string($rslt) )
                    {
                        DB::rollBack();
    
                        $arr_fail[$loan_info_no] = $rslt;
                        continue;
                    }
                    
                    Log::info('거래내역삭제 > 계약번호 : '.$newLoanInfo->no.', 거래내역번호 : '.$newLoanInfo->loan_info_trade_no);
    
                    $rslt = DB::dataProcess('UPD', 'loan_info_return_plan', $_END, ["loan_info_no"=>$loan_info_no]);
                    // 오류 업데이트 후 쪽지 발송
                    if( $rslt!="Y" )
                    {
                        DB::rollBack();
            
                        $arr_fail[$loan_info_no] = '신규 투자자 스케줄 업데이트를 실패했습니다.';
                        continue;
                    }

                    $rslt = DB::dataProcess("UPD", "loan_info_rate", $_END, ["loan_info_no"=>$loan_info_no]);
                    // 오류 업데이트 후 쪽지 발송
                    if( $rslt!="Y" )
                    {
                        DB::rollBack();
                        
                        $arr_fail[$loan_info_no] = '계약이율 업데이트를 실패했습니다.';
                        continue;
                    }
    
                    $rslt = DB::dataProcess("UPD", "loan_info_cday", $_END, ["loan_info_no"=>$loan_info_no]);
                    // 오류 업데이트 후 쪽지 발송
                    if( $rslt!="Y" )
                    {
                        DB::rollBack();
                        
                        $arr_fail[$loan_info_no] = '계약약정일 업데이트를 실패했습니다.';
                        continue;
                    }

                    $rslt = DB::dataProcess("UPD", "loan_info_invest_rate", $_END, ["loan_info_no"=>$loan_info_no]);
                    // 오류 업데이트 후 쪽지 발송
                    if( $rslt!="Y" )
                    {
                        DB::rollBack();
                        
                        $arr_fail[$loan_info_no] = '투자이율 업데이트를 실패했습니다.';
                        continue;
                    }
                }

                $rslt = DB::dataProcess("UPD", "loan_info", $_END, ["no"=>$loan_info_no]);
                // 오류 업데이트 후 쪽지 발송
                if( $rslt!="Y" )
                {
                    DB::rollBack();
                        
                    $arr_fail[$loan_info_no] = '계약삭제시 에러가 발생했습니다.';
                    continue;
                }
                
                Log::info('계약삭제 > 차입자 번호 : '.$newLoanInfo->cust_info_no.', 투자자 번호 : '.$newLoanInfo->loan_usr_info_no.', 계약번호 : '.$loan_info_no);
                
                $s_cnt++;

                DB::commit();
            }
        }
        else
        {
            $_RESULT['rslt'] = 'N';
            $_RESULT['msg']  = "파라미터 에러";
            return $_RESULT;
        }

        if(isset($arr_fail) && sizeof($arr_fail)>0)
        {
            $error_msg = "실패건이 존재합니다. \n";

            foreach($arr_fail as $t_no => $msg)
            {
                $error_msg .= "[".$t_no."] => ".$msg."\n";
            }

            $return_msg = sizeof($val['listChk'])."건 중 ".$s_cnt."건 성공 ".sizeof($arr_fail)."건 실패\n";
            $return_msg .= Func::nvl($error_msg,"");
        }
        else
        {
            $return_msg = "정상처리 되었습니다.";
        }
        
        $_RESULT['rslt'] = 'Y';
        $_RESULT['msg']  = $return_msg;

        return $_RESULT;
    }
}