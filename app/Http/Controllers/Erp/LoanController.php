<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Loan;
use Vars;
use Auth;
use Log;
use DataList;
use App\Chung\Paging;
use ExcelFunc;
use Invest;
use Trade;
use Cache;
use WooriBank;
use Illuminate\Support\Facades\Storage;
use FastExcel;
use Artisan;
use Carbon;

class LoanController extends Controller
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
     * 채권현황조회 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataLoanList(Request $request){

        $configArr          = Func::getConfigArr();

        $list   = new DataList(Array("listName"=>"loan","listAction"=>'/'.$request->path()));
        
        if(!isset($request->tabs)) $request->tabs = 'YU';
        $list->setTabs(Array(
            'ALL'=>'전체', 'YU'=>'유효', 'A'=>'정상', 'B'=>'연체', 'E'=>'종료',
            'BR'=>'지점', 'MY'=>'업무', 'OM'=>'가수금',
            ), $request->tabs);
        $list->setCheckBox("no");

        $list->setHidden(["target_sql"=>""]);

        $list->setButtonArray("엑셀다운","excelDownModal('/erp/loanexcel','form_loan')","btn-success");

        $list->setSearchDate('날짜검색',Array('loan_date'=>'계약일', 'promise_date'=>'약속일', 'lost_date'=>'소멸시효일', 'take_date'=>'최종이수일', 'LOAN_INFO.return_date'=>'차기수익지급일자', 'kihan_date'=>'기한이익상실일'),'searchDt','Y');
        
        $list->setRangeSearchDetail(Array('loan_money'=>'투자금액', 'LOAN_INFO.balance'=>'잔액', 'delay_term'=>'연체일'),'','','단위(원/일)');

        $list->setSearchType('d-pro_cd',Func::getConfigArr('pro_cd'),'상품코드', '', '', '', '', 'Y', '', true);
        $list->setSearchType('d-pro_sub_div', $configArr['pro_sub_div'], '상품구분', '', '', '', '', 'Y', '');
        $list->setSearchType('handle_code',$configArr['handle_cd'],'관리점');
        if( Func::funcCheckPermit("E004") || Func::funcCheckPermit("E031") )
        {
            // $list->setSearchType('manager_code', Func::myPermitBranch(), '관리지점', '', '', '', '', 'Y', '', true);
            $branchs = Func::myPermitBranchManager();
            $list->setSearchTypeMultiChain('manager_code', 'manager_id', $branchs, '관리지점', '', '', '', '', '담당자선택');
        }
        // 담당자 선택할 수 있게 한다.
        else
        {
            $list->setSearchType('manager_id', Func::getBranchUsers(Auth::user()->branch_code), '담당자', '', '', '', '', 'Y', '', true);
        }

                

        // $list->setSearchType('attribute_delay_cd',Func::getConfigArr('delay_rsn_cd'),'연체사유', '', '', '', '', 'Y', '', true);
        //$list->setSearchType('return_method_cd',Func::getConfigArr('return_method_cd'),'수익지급방법');
        //$list->setSearchType('return_fee_cd',Func::getConfigArr('return_fee_rate'),'조기수익지급');
        $list->setSearchType('loan_info-status',Vars::$arrayContractStaSearch,'상태', '', '', '', '', 'Y', '');
        $list->setSearchType('contract_day', Func::getConfigArr('contract_day'),'약정일', '', '', '', '', 'Y', '');

        $list->setSearchType('chng_handle_flag', ['Y'=>'Y'],'영업부채권');

        $list->setSearchDetail(Array(
            'loan_info.no' => '계약번호',
            'loan_info.cust_info_no' => '차입자번호',
            'name' => '이름',
            'ph23' => '휴대폰뒤',
            'find_manager_id' => '담당자명',
            'ssn' => '주민번호',
            'guarantor-name' => '보증인명',
            'guarantor-ssn' => '보증인주민번호',
            'birth' => '생년월일'
        ));

        return $list;
    }
    

    /**
     * 채권현황조회 메인화면
     *
     * @param  Void
     * @return view
     */
	public function loan(Request $request)
    {
        $list   = $this->setDataLoanList($request);

        $configArr = Func::getConfigArr();
        $array_cat_name = Func::getConfigArrList(['loan_cat_1_cd','loan_cat_2_cd','loan_cat_3_cd','loan_cat_4_cd','loan_cat_5_cd']);

        //$list->setLumpForm('STATUS', Array('BTN_NAME'=>'일괄처리','BTN_ACTION'=>'','BTN_ICON'=>'','BTN_COLOR'=>''));
        //$list->setLumpForm('EMPTY',  Array('BTN_NAME'=>'폼없는거','BTN_ACTION'=>'','BTN_ICON'=>'','BTN_COLOR'=>''));
        //$list->setLumpForm('TST',    Array('BTN_NAME'=>'폼없이바로실행','BTN_ACTION'=>'alert("실행");','BTN_ICON'=>'','BTN_COLOR'=>''));
        if( Func::funcCheckPermit("E003"))
        {
            $list->setLumpForm('changeManager', Array('BTN_NAME'=>'지점·담당자변경','BTN_ACTION'=>'','BTN_ICON'=>'','BTN_COLOR'=>''));
        }
        $list->setLumpForm('sms',           Array('BTN_NAME'=>'문자발송','BTN_ACTION'=>'','BTN_ICON'=>'','BTN_COLOR'=>'','param'=>['div'=>'erp', 'readonly'=>true]));
        $list->setLumpForm('loanLump',      Array('BTN_NAME'=>'일괄처리','BTN_ACTION'=>'','BTN_ICON'=>'','BTN_COLOR'=>''));
        $list->setLumpForm('print',         Array('BTN_NAME'=>'일괄인쇄','BTN_ACTION'=>'','BTN_ICON'=>'','BTN_COLOR'=>'', 'param'=>['div'=>'ERP']));

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $list->setlistTitleCommon(Array
        (
            'cust_info_no'     => Array('차입자번호', 0, '', 'center', '', 'cust_info_no', ['no'=>['계약번호', 'no', '<br>']]),
           'name'              => Array('이름', 0, '', 'center', '', 'ENC-name', ['loan_type'=>['최근투자', 'loan_type', '<br>']]),
           'ssn'               => Array('생년월일', 0, '', 'center', '', 'ENC-ssn', ['ph1'=>['전화번호1', 'ENC-ph11,ph12,ph13', '<br>']]),
           'addr11'            => Array('지역', 0, '', 'center', '', ''),
           'manager_name'       => Array('관리지점', 0, '', 'center', '', 'manager_code', ['manager'=>['담당', 'manager_id', '<br>']]),
            // 'name'           => Array('이름', 0, '60px', 'center', '', 'name', ['ssn'=>['생년월일', 'ssn', '<br>']]),
            // 'ph2'              => Array('휴대폰', 0, '', 'center', '', 'ph21,ph22,ph23'),
            // 'delay_rsn_nm'     => Array('연체사유', 0, '', 'center', '', 'attribute_delay_cd'),
            //'manager_name'     => Array('관리지점', 0, '', 'center', '', 'manager_code'),  
            //'manager'          => Array('담당자명', 0, '', 'center',  '','manager_id'),
            // 'manager_name'     => Array('관리지점', 0, '', 'center', '', 'manager_code', ['manager'=>['담당자명', 'manager_id', '<br>']]),
            // 'promise_info'     => Array('약속정보', 0, '', 'left', '', 'promise_date||promise_hour'),
            'pro_cd'           => Array('상품구분', 0, '', 'center', '', 'pro_cd'),
            'status_nm'        => Array('상태', 0, '', 'center', '', 'status||settle_div_cd', ['day2'=>['약 / 연', '', '<br>']]),
            'loan_rate'        => Array('금리', 0, '', 'center', '', 'loan_rate'),
            'delay_term_max'   => Array('최대(연)', 0, '', 'center', '', 'delay_term_max', ['delay_div'=>['<br>누적(연) / 횟수', 'delay_term_sum', '']]),
            // 'return_method_cd' => Array('수익지급방식', 0, '', 'center', '', 'return_method_cd'),
            'loan_date'        => Array('계약일', 0, '', 'center', '', 'loan_date', ['contract_end_date'=>['만기일', 'contract_end_date', '<br>']]),
            // 'max_ltv'          => Array('최고액 LTV', 0, '', 'center', '', 'max_ltv', ['ltv'=>['원금 LTV', 'ltv', '<br>']]),
            // 'recent_sise'     => Array('월시세', 0, '', 'center', '', 'recent_sise'),
            // 'highest_ltv'         => Array('최고액 LTV', 0, '', 'center', '', 'highest_ltv', ['origin_ltv'=>['원금 LTV', 'origin_ltv', '<br>']]),
            // 'chg_sise'     => Array('시세증감', 0, '', 'center', '', 'chg_sise'),
            // 'limit_money'      => Array('한도', 0, '', 'center', '', 'limit_money', ['monthly_return_money'=>['월수익지급', 'monthly_return_money', '<br>']]),
            // 'first_loan_money' => Array('최초투자', 0, '', 'center', '', 'first_loan_money', ['total_loan_money'=>['총투자', 'total_loan_money', '<br>']]),
            // 'contract_day'     => Array('약정일', 0, '', 'center', '', 'contract_day'),

            
            // 'delay_term'       => Array('연체일', 0, '', 'center', '', 'delay_term'),
            // 'return_date'      => Array('차기수익지급일', 0, '', 'center', '', 'return_date', ['return_date_interest'=>['수익지급일이자', 'return_date_interest', '<br>']]),
            'return_date'      => Array('차기수익지급일', 0, '', 'center', '', 'return_date', ['return_date_interest'=>['차기수익지급일 원리금', 'return_date_interest', '<br>']]),
            'last_in_date'  => Array('최근입금일', 0, '', 'center', '', 'last_in_date', ['last_in_money'=>['최근입금액', '', '<br>']]),
            'interest_sum'     => Array('총이자', 0, '', 'center', '', 'interest_sum', ['balance'=>['잔액', 'balance', '<br>']]),
            
            'promise_info'     => Array('약속일시', 0, '', 'center', '', 'promise_date', ['promise_money'=>['약속금액', 'promise_money', '<br>']]),
            // 'tenant_yn'        => Array('임차유무', 0, '', 'center', '', 'tenant_yn', ['rental_deposit'=>['임차금액', 'rental_deposit', '<br>']]),

            // 'interest_sum'     => Array('이자합계', 0, '', 'right', '', 'interest_sum'),
            // 'balance'          => Array('잔액', 0, '', 'right', '', 'balance'),
            // 'monthly_return_money' => Array('월수익지급액', 0, '', 'right', '', 'monthly_return_money'),
            // 'charge_money'     => Array('청구금액', 0, '', 'right', '', 'charge_money'),
            // 'irl_ccrs_status'  => Array('회파복진행', 0, '', 'center', '', ''),
            
            //'fullpay_money'    => Array('완납금액', 0, '', 'right', '', 'fullpay_money'),
        ));

        $list->setlistTitleTabs('OM',Array
        (
            'over_money'           => Array('가수금액', 0, '', 'center', '', 'over_money'),
        ));

		// KB 스크래핑 배치 마지막 실행시간
		$lastScrapTime = DB::table('conf_batch_log')
			->select('s_time', 'e_time')
			->where('save_status', 'Y')
			->where('conf_batch_no', 13)
			->orderBy('no', 'desc')
			->first();
		
		$scrapHTML = '';
		if (!empty($lastScrapTime->s_time)) {
			// e_time 없으면 붉은색
			if (empty($lastScrapTime->e_time))	$scrapHTML .= '<span class="status-text-b">';
			else								$scrapHTML .= '<span class="status-text-a">';
			$scrapHTML .= '마지막 KB 업데이트 : ';
			$scrapHTML .= date('Y-m-d H:i:s', strtotime($lastScrapTime->s_time));
			$scrapHTML .= '</span>';
		}

        return view('erp.loan')->with('result', $list->getList())
                               ->with('array_branch',       Func::getBranchList())      // 담당자일괄변경에서 사용
                               ->with('array_branch_users', Func::getBranchUserList())
                               ->with('arrayBranchUsers', Func::getBranch())            // PDS에서 사용
                               ->with('configArr', $configArr)
                               ->with('array_cat_name', $array_cat_name)
							   ->with('scrapHTML', $scrapHTML);
    }

    /**
     * 채권현황조회 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function loanList(Request $request)
    {
        // $request->isDebug = true;
        
        $list  = $this->setDataLoanList($request);
        $param = $request->all();
        
         // Tab count 
		if($request->isFirst=='1')
		{
            $BOXC = DB::table("loan_info")->join("cust_info", "loan_info.cust_info_no", "=", "cust_info.no");
            $BOXC->join("cust_info_extra", "cust_info.no", "=", "cust_info_extra.cust_info_no");
            $BOXC->leftJoin("product_manage d", "loan_info.pro_cd", "=", "cast(d.pro_cd as text)");

            // 부족금, 전체정상, 전체연체 삭제
            // , coalesce(sum(case when loan_info.status in ('A','C') and (( loan_info.lack_interest + loan_info.lack_delay_money + loan_info.lack_delay_interest ) > 10000) then 1 else 0 end),0) as LK
            // , coalesce(sum(case when loan_info.status in ('A','C') then 1 else 0 end),0) as AC
            // , coalesce(sum(case when loan_info.status in ('B','D') then 1 else 0 end),0) as BD
            $BOXC->SELECT(DB::RAW("coalesce(sum(case when loan_info.manager_id='".Auth::id()."' then 1 else 0 end),0) as MY
                                , coalesce(sum(case when loan_info.manager_code='".Auth::user()->branch_code."' and loan_info.status in ('A','B','C','D') then 1 else 0 end),0) as BR
                                , count(loan_info.no) as ALL
                                , coalesce(sum(case when loan_info.status in ('A','B','C','D') then 1 else 0 end),0) as YU
                                , coalesce(sum(case when loan_info.status in ('E') and (loan_info.over_money > 0) then 1 else 0 end),0) as OM
                                , coalesce(sum(case when loan_info.status in ('A') then 1 else 0 end),0) as A
                                , coalesce(sum(case when loan_info.status in ('B') then 1 else 0 end),0) as B
                                , coalesce(sum(case when loan_info.status in ('C') and loan_info.settle_div_cd='1' then 1 else 0 end),0) as C1
                                , coalesce(sum(case when loan_info.status in ('D') and loan_info.settle_div_cd='1' then 1 else 0 end),0) as D1
                                -- , coalesce(sum(case when loan_info.status in ('C') and loan_info.settle_div_cd='2' then 1 else 0 end),0) as C2
                                -- , coalesce(sum(case when loan_info.status in ('D') and loan_info.settle_div_cd='2' then 1 else 0 end),0) as D2
                                -- , coalesce(sum(case when loan_info.status in ('C') and loan_info.settle_div_cd='3' then 1 else 0 end),0) as C3
                                -- , coalesce(sum(case when loan_info.status in ('D') and loan_info.settle_div_cd='3' then 1 else 0 end),0) as D3
                                , coalesce(sum(case when loan_info.status in ('E') then 1 else 0 end),0) as E
                                , coalesce(sum(case when loan_info.status in ('E') and loan_info.contract_end_date > loan_info.last_in_date then 1 else 0 end),0) as EL
                                , coalesce(sum(case when loan_info.status in ('P') then 1 else 0 end),0) as P
                                , coalesce(sum(case when loan_info.status in ('M') then 1 else 0 end),0) as M
                                , coalesce(sum(case when loan_info.status in ('N') then 1 else 0 end),0) as N"));
            $BOXC->where('cust_info.save_status','Y');
            $BOXC->where('loan_info.save_status','Y');
            // 전지점 조회권한 없으면 자기 지점만
            if( !Func::funcCheckPermit("E004") )
            {
                $BOXC->where('loan_info.manager_code',Auth::user()->branch_code);
            }
            $BOXC->whereIn("loan_info.status", ['A','B','C','D','E','P','M','N','S','Y','H']);
            $BOXC->whereraw("(d.del_time is null or d.del_time = '')");
            
            $vcnt = $BOXC->FIRST();
            Log::info("#########쿼리 확인 :".Func::printQuery($BOXC));
			$r['tabCount'] = array_change_key_case((Array) $vcnt, CASE_UPPER);
		}

		// 배치 가장 마지막으로 돈 시간
		// $latestTime = DB::table('conf_batch_log')->where('conf_batch_no', 13)->where('end_yn', 'Y')->max('save_time');

		// $clcltData = DB::table('kb_clclt_data')
		// 	->select('loan_info_no','recent_sise', 'chg_sise', 'origin_ltv', 'highest_ltv')
		// 	->where('save_status', 'Y')
		// 	->where('save_time', $latestTime);

        // 기본쿼리
        $LOAN = DB::table("loan_info");
        $LOAN->join("cust_info", "loan_info.cust_info_no", "=", "cust_info.no");
        $LOAN->join("cust_info_extra", "cust_info.no", "=", "cust_info_extra.cust_info_no");
        $LOAN->leftJoin("product_manage d", "loan_info.pro_cd", "=", "cast(d.pro_cd as text)");

        $LOAN->select("loan_info.no", "loan_info.cust_info_no", "loan_date", "loan_info.pro_cd", "return_method_cd", "loan_rate", "loan_delay_rate", "contract_day", "delay_term", "loan_info.promise_date", "promise_hour", "promise_min", "loan_info.loan_type", "loan_info.delay_term_max", "convert_c_no", "convert_l_no", 
        "loan_info.loan_money", "loan_info.total_loan_money", "loan_info.base_cost", "loan_info.chng_handle_flag", "loan_info.buy_date" );
        $LOAN->addselect("loan_info.status", "loan_info.return_date", "kihan_date", "interest_sum", "loan_info.balance", "fullpay_money", "charge_money", "manager_code", "manager_id", "loan_info.monthly_return_money", "loan_info.delay_term_sum", "loan_info.delay_cnt", "loan_info.limit_money", "loan_info.return_date_interest", "loan_info.last_trade_date", "loan_info.last_in_date", "loan_info.last_in_money", "loan_info.contract_date", "loan_info.contract_end_date", "loan_info.lack_interest", "loan_info.lack_delay_money", "loan_info.lack_delay_interest", "loan_info.promise_money", "loan_info.over_money");
        $LOAN->addselect("cust_info.name", "cust_info.ssn", "cust_info_extra.ph11", "cust_info_extra.ph12", "cust_info_extra.ph13", "cust_info_extra.ph21", "cust_info_extra.ph22", "cust_info_extra.ph23", "cust_info_extra.addr11", "cust_info_extra.year_income", "loan_info.first_loan_money", "cust_info.attribute_delay_cd, loan_info.settle_div_cd", "job_cd", "cust_info.nice_info_date", "cust_info_extra.addr11", "cust_info_extra.zip1");
        
        $LOAN->addselect(DB::raw("( select coalesce(law_proc_status_cd, '')||'|'||coalesce(law_div, '')||'|'||coalesce(law_type, '') from loan_info_law where save_status='Y' and cust_info_no=cust_info.no order by save_time desc fetch first 1 rows only ) as law_info"));
                        
        $LOAN->where('cust_info.save_status','Y');
        $LOAN->where('loan_info.save_status','Y');
        $LOAN->whereRaw("(d.del_time is null or d.del_time = '')");

        // 'MY'=>'업무', 'BR'=>'지점', 'ALL'=>'전체', 'YU'=>'유효', 'LK'=>'부족금', 'BD'=>'연체', 'S'=>'상각', 'M'=>'매각', 'E'=>'완제'
		if( $request->tabsSelect=="MY" )
		{
            $LOAN->whereIn('loan_info.status',['A','B','C','D']);
            $param['tabSelectNm'] = "loan_info.manager_id";
            $param['tabsSelect']  = Auth::id();
        }
		else if( $request->tabsSelect=="BR" )
		{
            $LOAN->whereIn('loan_info.status',['A','B','C','D']);
            $param['tabSelectNm'] = "loan_info.manager_code";
            $param['tabsSelect']  = Auth::user()->branch_code;
        }
		else if( $request->tabsSelect=="ALL" )
		{
            $param['tabsSelect']  = "ALL";
        }
		else if( $request->tabsSelect=="YU" )
		{
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('A','B','C','D');
        }
		else if( $request->tabsSelect=="LK" )
		{
            $LOAN->WHERE('( loan_info.lack_interest + loan_info.lack_delay_money + loan_info.lack_delay_interest )', '>', 10000);
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('A','C');
        }
        // 가수금
        else if( $request->tabsSelect=="OM" )
		{
            $LOAN->WHERE('loan_info.over_money', '>', 0);
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('E');
        }
        // 화해
        else if(in_array(substr($request->tabsSelect, 0, 1), ['C', 'D']))
        {
            $sta = substr($request->tabsSelect, 0, 1);
            $settleDiv = substr($request->tabsSelect, 1, 1);
            $LOAN->WHERE('loan_info.settle_div_cd', $settleDiv);
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array($sta);
        }
		else if( $request->tabsSelect=="AC" )
		{
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('A','C');
        }
		else if( $request->tabsSelect=="BD" )
		{
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('B','D');
        }
        else if( $request->tabsSelect=="E" )
		{
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('E');
        }
        else if( $request->tabsSelect=="M" )
		{
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('M');
        }
        else if( $request->tabsSelect=="EL" )
		{
            $LOAN->WHERERAW('loan_info.contract_end_date > loan_info.last_in_date');

            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('E');
        }
		else
		{
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = $request->tabsSelect;
        }

        // 전지점 조회권한 없으면 자기 지점만
        if( !Func::funcCheckPermit("E004") )
        {
            $LOAN->WHEREIN('loan_info.manager_code', array_keys(Func::myPermitBranch()));
        }

        // 담당자명 검색 - ID로도 검색하게 한다.
        if(isset( $param['searchDetail']) && $param['searchDetail']=='find_manager_id' && !empty($param['searchString']) )
        {
            $searchString = $param['searchString'];
            $LOAN->whereIn('loan_info.manager_id', function($query) use ($searchString) {
                $query->select('id')
                        ->from('users')
                        ->where('save_status', 'Y')
                        ->whereRaw("(name='".Func::encrypt($searchString, 'ENC_KEY_SOL')."' or id='".$searchString."')");
                        ;
            });
            unset($param['searchString']);
        }

        // 생년월일검색
        if(isset( $param['searchDetail']) && $param['searchDetail']=='birth' && !empty($param['searchString']) )
        {
            $searchString = $param['searchString'];

            $LOAN = Func::encLikeSearch($LOAN, 'cust_info.ssn', $searchString, 'all', 9);
            
            unset($param['searchString']);
        }

        //Log::info("#########메인 쿼리 확인 :".Func::printQuery($LOAN));
        $LOAN = $list->getListQuery('loan_info', 'main', $LOAN, $param);
        $LOAN->orderBy("loan_info.no", "desc");

        $target_sql = urlencode(encrypt(Func::printQuery($LOAN))); // 페이지 들어가기 전에 쿼리를 저장해야한다.

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $sum_data = Array
        (
            ["coalesce(sum(loan_info.balance),0)", '잔액', '원'],
            ["coalesce(sum(loan_info.interest_sum),0)", '총이자', '원'],
            // ["ROUND( COALESCE(SUM(CASE WHEN STATUS IN ('B','D') THEN LOAN_INFO.BALANCE ELSE 0 END),0)::numeric / COALESCE(SUM(CASE WHEN LOAN_INFO.BALANCE>0 THEN LOAN_INFO.BALANCE ELSE NULL END),100)::numeric * 100, 2)", '연체율', '%'],
        );
        $paging = new Paging($LOAN, $request->page, $request->listLimit, 10, $request->listName, '', $sum_data);
        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["loan_info","cust_info","cust_info_extra","product_manage"], $rslt);	// CHUNG DATABASE DECRYPT

        //log::info("쿼리 ".Func::printQuery($LOAN));
        // 뷰단 데이터 정리.
        $getProCode        = Func::getConfigArr('pro_cd');
        $getReturnMethodCd = Func::getConfigArr('return_method_cd');
        $getDelayRsnCd     = Func::getConfigArr('delay_rsn_cd');
        $lawProcStatus     = Func::getConfigArr('law_proc_status_cd');
        $getSettleCd       = Func::getConfigArr('stl_div_cd');
        $arrBranch         = Func::getBranch();
        $arrManager        = Func::getUserList();
        $configArr         = Func::getConfigArr();
        $getIrlStatus      = Func::getConfigArr('relief_irl_status');
        $arrayCcrsStatus   = Vars::$arrayReliefCcrsStatus;

        // 리스트 배열을 담기위해 한번 돌리고 돌린다.
        $array_rslt = [];
        foreach ($rslt as $v)
        {
            $array_rslt[$v->no] = $v;
        }

        $nos = ( sizeof($array_rslt)>0 && sizeof($array_rslt)<=1000 ) ? urlencode(encrypt(gzcompress(implode("|",array_keys($array_rslt)), 9))) : "" ;
        $cnt = 0;
        $total = ($request->listLimit>1000) ? 1000:$request->listLimit;

        foreach ($array_rslt as $v)
        {
            // $curr_idx = ( ($request->page-1) * $request->listLimit ) + $cnt + 1;

            $v->onclick          = "javascript:loan_info_pop(".$v->cust_info_no.", ".$v->no.", '".$nos."', '".$cnt."', '".$total."');";
            $v->line_style       = 'cursor: pointer;';

            $v->loan_info_no     = $v->no;
            $v->name             = Func::nameMasking($v->name, 'Y');
            $v->ssn              = Func::ssnFormat($v->ssn, 'Y');
            $v->ph1              = Func::phMasking($v->ph11,$v->ph12,$v->ph13,'Y') ;
            $v->loan_date        = Func::dateFormat($v->loan_date);
            $v->pro_cd           = Func::getArrayName($getProCode, $v->pro_cd);
            $v->return_method_cd = Func::getArrayName($getReturnMethodCd, $v->return_method_cd);
            $v->loan_rate        = sprintf('%0.2f',$v->loan_rate)."%";
            $v->status_nm        = Func::getInvStatus($v->status, true);

            $v->return_date             = Func::dateFormat($v->return_date);
            $v->kihan_date              = Func::dateFormat($v->kihan_date);
            $v->interest_sum            = '<span style="color:#0353a8;font-weight: 800">'.number_format($v->interest_sum).'</span>';
            $v->balance                 = number_format($v->balance);
            $v->fullpay_money           = number_format($v->fullpay_money);
            $v->charge_money            = number_format($v->charge_money);
            $v->return_date_interest    = ($v->monthly_return_money>0) ? number_format($v->monthly_return_money):number_format($v->return_date_interest);
            $v->monthly_return_money    = number_format($v->monthly_return_money);
            $v->over_money              = number_format($v->over_money);

            if( isset($v->addr11) && !empty($v->addr11))
            {
                $tmp = explode(" ", $v->addr11);
                $v->addr11       = str_replace("광역시","",str_replace("특별시","",(isset($tmp[0]) ? $tmp[0]:'')))." ".(isset($tmp[1]) ? $tmp[1]:'');   
            }
            if( isset($v->delay_term) && $v->delay_term<0 )
            {
                $v->delay_term = 0;
            }
            $v->day2             = $v->contract_day." / ".$v->delay_term;
            $v->delay_div        = " / ".$v->delay_term_sum." / ".number_format($v->delay_cnt);
            $v->ltv              = isset($v->ltv) ? sprintf('%0.2f',$v->ltv).'%' : '';
			$v->max_ltv          = isset($v->max_ltv) ? sprintf('%0.2f',$v->max_ltv).'%' : '';
            $v->loan_type        = Func::getArrayName($configArr['app_type_cd'], $v->loan_type);
            if( isset($v->tenant_yn) )
            {
                if( $v->tenant_yn =='Y' )
                {
                    $v->tenant_yn = '임차';
                    if($v->rental_deposit == 0)
                    {
                        $v->rental_deposit = '';
                    }
                    else
                    {
                        $v->rental_deposit = number_format($v->rental_deposit);
                    }
                }
                else
                {
                    $v->tenant_yn = '';
                }
            }
            else
            {
                $v->tenant_yn = '';
            }
            if( isset($v->delay_term_max) && $v->delay_term_max<=0 )
            {
                $v->delay_term_max=0;
            }
            $v->limit_money             = number_format($v->limit_money);
            // $v->final_value             = number_format($v->final_value/10000);
            $v->loan_money              = number_format($v->loan_money);
            $v->first_loan_money        = number_format($v->first_loan_money);
            $v->total_loan_money        = number_format($v->total_loan_money);
            $v->last_trade_date         = Func::dateFormat($v->last_trade_date);
            $v->last_in_date            = Func::dateFormat($v->last_in_date);
            $v->last_in_money           = number_format($v->last_in_money);
            $v->contract_end_date       = Func::dateFormat($v->contract_end_date);
            $v->lack_div                = number_format($v->lack_delay_interest+$v->lack_delay_money+$v->lack_interest);
            $v->promise_date            = isset($v->promise_date) ? Func::dateFormat($v->promise_date) : '';
            // $v->yudong_money           = number_format($v->yudong_money);
            $v->job_cd                 = isset($v->job_cd) ? Func::getArrayName($array_job_cd,(substr($v->job_cd, 0, 1))) : '';
            //log::debug("여기야!!!==>".print_r($v, true));
            
            $v->manager_name     = Func::nvl($arrBranch[$v->manager_code], $v->manager_code);
           // $v->manager_id     =  isset($arrManager[$v->manager_id]) ? Func::nvl($arrManager[$v->manager_id]->name, $v->manager_id) : $v->manager_id ;
            $v->manager       = isset($arrManager[$v->manager_id]) ? Func::nvl($arrManager[$v->manager_id]->name, $v->manager_id) : $v->manager_id ;
            
            // text-color
            $textColor = 'indigo';
            if( $v->promise_date!="" )
            {
                $v->promise_info = Func::dateFormat(substr($v->promise_date, 2), '/');

                if($v->promise_date<date("Ymd"))
                {
                    $textColor = 'red';
                }
            }
            if( $v->promise_hour!="" )
            {
                if(!isset($v->promise_info))
                    $v->promise_info = '';
                    
                $v->promise_info.= " ".sprintf("%02d",$v->promise_hour).":";
                $v->promise_info.= ( $v->promise_min!="" ) ? sprintf("%02d",$v->promise_min) : "00" ;

                if($v->promise_date.sprintf("%02d", $v->promise_hour).sprintf("%02d", $v->promise_min)<date("YmdHi"))
                {
                    $textColor = 'red';
                }
            }

            // 색상 변경
            if(isset($v->promise_info) && $v->promise_info!='')
            {
                $v->promise_info = "<span class='text-".$textColor."'>".$v->promise_info."</span>";
            }

            $v->delay_rsn_nm  = Func::getArrayName($getDelayRsnCd, $v->attribute_delay_cd);
            if( in_array($v->attribute_delay_cd,Vars::$arrBanAttDelayCd) )
            {
                $v->delay_rsn_nm = "<span class='text-danger'>".$v->delay_rsn_nm."</span>"; // font-weight-bold
            }

            if( $v->law_info )
            {
                $law_tmp = explode("|", $v->law_info);    //law_proc_status_cd||'|'||law_div||'|'||law_type
                $v->law_status_nm = Func::getArrayName($lawProcStatus, $law_tmp[0]);
                $v->law_div_nm    = Func::getArrayName(Vars::$arrayLawDiv, $law_tmp[1]);
                $v->law_type_nm   = Func::getArrayName(Func::nvl(Vars::$arrayLawType[$law_tmp[1]],[]), $law_tmp[2]);
            }
			

			$v -> recent_sise = (isset($v -> recent_sise)) ? number_format($v->recent_sise/10000) : '';

			$v -> origin_ltv = (isset($v -> origin_ltv)) ?"<font color='#000080'><b>".sprintf('%0.2f',$v->origin_ltv).'%</b></font>': '';

			$v -> highest_ltv = (isset($v -> highest_ltv)) ?sprintf('%0.2f',$v->highest_ltv).'%': '';


			if(isset($v -> chg_sise)){
				$color = $v->chg_sise >= 0 ? 'blue' : 'red';
				$v->chg_sise = "<font color='$color'>".number_format($v->chg_sise/10000).'</font>';
			}
			else{
				$v->chg_sise = '';
			}

            if(!empty($v->dambo_offer))
            {
                $v->dambo_offer = Func::chungDecOne($v->dambo_offer);
            }
			
			//Log::debug("데이터  ".print_r($v, true));


            $r['v'][] = $v;
            $cnt ++;

        }
		
        // 페이징
        $r['pageList']  = $paging->getPagingHtml($request->path());
        $r['result']    = 1;
        $r['txt']       = $cnt;
        $r['targetSql'] = $target_sql;
        $r['totalCnt']  = $paging->getTotalCnt();
		// $r['clcltData'] = $clcltData;

        return json_encode($r);
    }





    /**
     * 엑셀다운로드 (채권현황조회)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function loanInfoInterestCal(Request $request)
    {
        $no = $request->no;

        if( $request->mode=="UP" )
        {
            $dambo_set_fee_target = ( isset($request->dambo_set_fee_target) && $request->dambo_set_fee_target=="Y" ) ? "Y" : "N" ;
            $rslt = DB::dataProcess("UPD", 'loan_info', ["dambo_set_fee_target"=>$dambo_set_fee_target], ['no'=>$no]);
        }
        else if( $request->mode=="MONTHLY_RETURN" )
        {
            if(!Func::funcCheckPermit("A104","A")) // 월수익지급액 변경권한
            {
                return "X";
            }
            $monthly_return_gubun = $request->monthly_return_gubun;
            $monthly_return_money = str_replace(",","",$request->monthly_return_money);

            $rslt = DB::dataProcess("UPD", 'loan_info', ["monthly_return_gubun"=>$monthly_return_gubun, "monthly_return_money"=>$monthly_return_money], ['NO'=>$no, 'RETURN_METHOD_CD'=>'F']);
        }

        $rslt = Loan::updateLoanInfoInterest($no, date("Ymd"));
        return $rslt;
    }


    /**
     * 엑셀다운로드 (채권현황조회)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function loanExcel(Request $request)
    {
        $getDelayRsnCd     = Func::getConfigArr('delay_rsn_cd');

        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $list     = $this->setDataLoanList($request);
        $param    = $request->all();
        $down_div = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;
        $arrayHeader = ExcelFunc::getExcelHeader("/erp/loan", $param['tabsSelect']);
        $selectHeaders = array_flip(json_decode($param['excelHeaders']));       // 선택된 항목값을 Key로 설정. in_array를 안쓰고 배열에 Key로 존재하는지 식별하도록 함. in_array는 반복실행시 느려지는 원인이 됨.

        // 엑셀 select 기능
        $excel_header = array();
        // 엑셀 - 헤더
        foreach($arrayHeader as $headerIdx => $headerTitle)
        {            
            // 선택한 항목값이 있는경우만
            if(isset($selectHeaders[$headerIdx])) $excel_header[] = $headerTitle;
        }

        // 기본쿼리
        $LOAN = DB::TABLE("loan_info");
        $LOAN->JOIN("cust_info", "loan_info.cust_info_no", "=", "cust_info.no");
        $LOAN->JOIN("cust_info_extra", "cust_info.no", "=", "cust_info_extra.cust_info_no");

        $LOAN->LEFTJOIN("product_manage d", "loan_info.pro_cd", "=", "cast(d.pro_cd as text)");

        $LOAN->SELECT("loan_info.no", "loan_info.cust_info_no", "loan_date", "loan_info.pro_cd", "loan_info.vir_acct_ssn", "return_method_cd", "loan_rate", "loan_delay_rate", "contract_day", "delay_term", "settle_div_cd", "delay_term_max", "first_path_cd", "cost_money", "convert_c_no", "convert_l_no", "return_interest_sum", "return_origin_sum", "return_cost_money_sum");
        $LOAN->ADDSELECT("loan_info.status", "loan_info.return_date", "kihan_date", "interest_sum", "loan_info.balance", "fullpay_money", "monthly_return_money", "charge_money", "manager_id" , "manager_code","loan_money", "contract_end_date", "over_money","return_fee_cd","loan_info.first_loan_money","loan_info.base_cost","attribute_delay_cd", "loan_info.chng_handle_flag", "loan_info.buy_corp", "loan_info.buy_date");
        $LOAN->ADDSELECT("loan_info.loan_type", "loan_info.loan_money","loan_info.delay_term_sum","loan_info.delay_cnt","loan_info.limit_money","loan_info.return_date_interest","loan_info.last_trade_date", "loan_info.last_in_date", "loan_info.last_in_money","loan_info.contract_date","loan_info.lack_interest","loan_info.lack_delay_money","loan_info.lack_delay_interest","loan_info.promise_money","loan_info.doc_status_cd", "loan_info.over_money" , "loan_info.misu_money");
        $LOAN->ADDSELECT("cust_info.name", "cust_info.ssn", "cust_info_extra.ph21", "cust_info_extra.ph22", "cust_info_extra.ph23","cust_info_extra.ph11","cust_info_extra.ph12","cust_info_extra.ph13","cust_info_extra.ph41","cust_info_extra.ph42","cust_info_extra.ph43","cust_info_extra.zip1","cust_info_extra.com_name","first_loan_use_cd", "cust_info.nice_info_date");
        $LOAN->ADDSELECT("cust_info_extra.addr11","cust_info_extra.addr12");
        $LOAN->ADDSELECT("cust_info_extra.email", "cust_info_extra.job_cd", "cust_info_extra.ph31", "cust_info_extra.ph32", "cust_info_extra.ph33", "cust_info_extra.addr31", "cust_info_extra.zip1", "cust_info_extra.com_year", "cust_info_extra.com_months", "cust_info_extra.year_income", "cust_info_extra.com_ssn", "cust_info.attribute_manage_cd", "cust_info.person_manage" );
        $LOAN->ADDSELECT("loan_info.promise_date, loan_info.promise_hour, loan_info.promise_min, cust_info.attribute_delay_cd " );
        $LOAN->ADDSELECT(DB::RAW("( select law_proc_status_cd||'|'||law_div||'|'||law_type from loan_info_law where save_status='Y' and cust_info_no=cust_info.no order by save_time desc fetch first 1 rows only ) as law_info"));

        $LOAN->WHERE('cust_info.save_status','Y');
        $LOAN->WHERE('loan_info.save_status','Y');
        $LOAN->WHERERAW("(d.del_time is null or d.del_time = '')");
        
        // 'MY'=>'업무', 'BR'=>'지점', 'ALL'=>'전체', 'YU'=>'유효', 'LK'=>'부족금', 'BD'=>'연체', 'S'=>'상각', 'M'=>'매각', 'E'=>'완제'
		if( $request->tabsSelect=="MY" )
		{
            $LOAN->WHEREIN('loan_info.status',['A','B','C','D']);
            $param['tabSelectNm'] = "loan_info.manager_id";
            $param['tabsSelect']  = Auth::id();
        }
		else if( $request->tabsSelect=="BR" )
		{
            $LOAN->WHEREIN('loan_info.status',['A','B','C','D']);
            $param['tabSelectNm'] = "loan_info.manager_code";
            $param['tabsSelect']  = Auth::user()->branch_code;
        }
		else if( $request->tabsSelect=="ALL" )
		{
            $param['tabsSelect']  = "ALL";
        }
		else if( $request->tabsSelect=="YU" )
		{
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('A','B','C','D');
        }
		else if( $request->tabsSelect=="LK" )
		{
            $LOAN->WHERE('( loan_info.lack_interest + loan_info.lack_delay_money + loan_info.lack_delay_interest )', '>', 10000);
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('A','C');
        }
        // 가수금
        else if( $request->tabsSelect=="OM" )
		{
            $LOAN->WHERE('loan_info.over_money', '>', 0);
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('E');
        }
        // 화해
        else if(in_array(substr($request->tabsSelect, 0, 1), ['C', 'D']))
		{
            $sta = substr($request->tabsSelect, 0, 1);
            $settleDiv = substr($request->tabsSelect, 1, 1);
            $LOAN->WHERE('loan_info.settle_div_cd', $settleDiv);
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array($sta);
        }
		else if( $request->tabsSelect=="AC" )
		{
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('A','C');
        }
		else if( $request->tabsSelect=="BD" )
		{
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('B','D');
        }
        else if( $request->tabsSelect=="EL" )
		{
            $LOAN->WHERERAW('loan_info.contract_end_date > loan_info.last_in_date');

            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = Array('E');
        }
		else
		{
            $param['tabSelectNm'] = "loan_info.status";
            $param['tabsSelect']  = $request->tabsSelect;
        }

        // 전지점 조회권한 없으면 자기 지점만
        if( !Func::funcCheckPermit("E004") && !isset($request->excel_flag) )
        {
            $LOAN->WHEREIN('loan_info.manager_code', array_keys(Func::myPermitBranch()));
        }

        // 담당자명 검색 - ID로도 검색하게 한다.
        if(isset( $param['searchDetail']) && $param['searchDetail']=='find_manager_id' && !empty($param['searchString']) )
        {
            $searchString = $param['searchString'];
            $LOAN->whereIn('loan_info.manager_id', function($query) use ($searchString) {
                $query->select('id')
                        ->from('users')
                        ->where('save_status', 'Y')
                        ->whereRaw("(name='".Func::encrypt($searchString, 'ENC_KEY_SOL')."' or id='".$searchString."')");
                        ;
            });
            unset($param['searchString']);
        }

        // 생년월일검색
        if(isset( $param['searchDetail']) && $param['searchDetail']=='birth' && !empty($param['searchString']) )
        {
            $searchString = $param['searchString'];

            $LOAN = Func::encLikeSearch($LOAN, 'cust_info.ssn', $searchString, 'all', 9);
            
            unset($param['searchString']);
        }

        $LOAN = $list->getListQuery("LOAN_INFO",'main',$LOAN,$param);
        $LOAN->ORDERBY("loan_info.no", "desc");

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($LOAN);
        $file_name    = "채권현황조회_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no)){
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
        } else {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $query, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
        }

        $arrayNsf = array();
        // 선택엑셀 데이터 추가
        if(isset($addJoinTable) && sizeof($addJoinTable) > 0)
        {
            foreach($addJoinTable as $table)
            {
                // loan_info, cust_info, cust_info_extra, loan_info_trade 테이블의 경우 이미 Join되어있으므로 제외
                if($table!="loan_info" && $table!="cust_info" && $table!="cust_info_extra" && $table!="loan_info_trade")
                {
                    // cust_info_img
                    if($table=="cust_info_img")
                    {
                        $LOAN->LEFTJOIN(DB::RAW("(select count(1) as img_cnt, cust_info_no from cust_info_img where save_status = 'Y' group by cust_info_no ) as cust_info_img"),'loan_info.cust_info_no','=','cust_info_img.cust_info_no');
                    }
                }

                // loan_settle, loan_settle_plan, loan_irl, loan_ccrs 테이블의 column 은 Subquery 실행시 이미 검색대상 컬럼 추가됨.
                if($table!="loan_settle" && $table!="loan_settle_plan" && $table!="loan_irl" && $table!="loan_ccrs")
                {
                    // 검색대상 컬럼 추가
                    if(isset($addSelect[$table]) && sizeof($addSelect[$table]) > 0)
                    {
                        for($columnIdx=0;$columnIdx<sizeof($addSelect[$table]);$columnIdx++)
                        {
                            $LOAN->addSelect($addSelect[$table][$columnIdx]);
                        }
                    }
                }
            }

            // 화해관련 정보는 Subquery에서 지정된 정보모두 join한다.
            if(isset($settle))
            {
                $LOAN->leftjoinSub($settle, 'loan_settle', function($join) {
                    $join->on('LOAN_INFO.no', '=', 'loan_settle.settle_loan_info_no');
                });
                $LOAN->addSelect('loan_settle.*');
            }
        }

        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","CUST_INFO_EXTRA","PRODUCT_MANAGE"], $rslt);	// CHUNG DATABASE DECRYPT

        log::debug("QUERY END");

        $excel_data = array();
        // 뷰단 데이터 정리.
        $getProCode        = Func::getConfigArr('pro_cd');
        $configArr         = Func::getConfigArr();
        $arrManager        = Func::getUserList();
        $getReturnMethodCd = Func::getConfigArr('return_method_cd');
        $getHouseTypeCd    = Func::getConfigArr('house_type_cd');
        $getHouseOwnCd     = Func::getConfigArr('house_own_cd');
        $getSettleCd       = Func::getConfigArr('stl_div_cd');
        $getProDivCd       = Func::getConfigArr('pro_div');
        $lawProcStatus     = Func::getConfigArr('law_proc_status_cd');
        
        $getIrlStatus      = Func::getConfigArr('relief_irl_status');
        $arrayCcrsStatus   = Vars::$arrayReliefCcrsStatus;
        $arrayBranch   = Func::getBranch();
        $record_count  = 0;
		
        foreach ($rslt as $v)
        {
            
            $v->return_date_interest    = ($v->monthly_return_money>0) ? $v->monthly_return_money:$v->return_date_interest;

            if(!empty($v->dambo_offer))
            {
                $v->dambo_offer = Func::chungDecOne($v->dambo_offer);
            }
            
            $v->promise_info = "";
            if( $v->promise_date!="" )
            {
                $v->promise_info = Func::dateFormat(substr($v->promise_date, 2), '/');
            }
            if( $v->promise_hour!="" )
            {
                if(!isset($v->promise_info))
                    $v->promise_info = '';
                    
                $v->promise_info.= " ".sprintf("%02d",$v->promise_hour).":";
                $v->promise_info.= ( $v->promise_min!="" ) ? sprintf("%02d",$v->promise_min) : "00" ;
            }

            $v->delay_rsn_nm  = Func::getArrayName($getDelayRsnCd, $v->attribute_delay_cd);

            if( $v->law_info )
            {
                $law_tmp = explode("|", $v->law_info);    //law_proc_status_cd||'|'||law_div||'|'||law_type
                $v->law_status_nm = Func::getArrayName($lawProcStatus, $law_tmp[0]);
                $v->law_div_nm    = Func::getArrayName(Vars::$arrayLawDiv, $law_tmp[1]);
                $v->law_type_nm   = Func::getArrayName(Func::nvl(Vars::$arrayLawType[$law_tmp[1]],[]), $law_tmp[2]);
            }
            if( isset($v->addr11) )
            {
                $tmp = explode(" ", $v->addr11);
                $local           = str_replace("광역시","",str_replace("특별시","",(isset($tmp[0]) ? $tmp[0]:'')))." ".(isset($tmp[1]) ? $tmp[1]:'');   
            }
            else
            {
                $local = '';
            }
            if( isset($v->delay_term) && $v->delay_term<0 )
            {
                $v->delay_term = 0;
            }
            if( isset($v->year_income) )
            {
                $v->year_income = (int)$v->year_income*10000;
            }
            $v->return_method_nm = Func::getArrayName($configArr['return_method_cd'], $v->return_method_cd);
            
            $v->monthly_return_money = number_format($v->monthly_return_money);
            if( isset($v->first_loan_use_cd) )
            {
                $v->first_loan_use_cd = Func::getArrayName($configArr['loan_use_cd'], $v->first_loan_use_cd);
            }
            if( isset($v->doc_status_cd) )
            {
                if( $v->doc_status_cd == 'Y' )
                {
                    $v->doc_status_cd = '징구완료';
                }
                else if( $v->doc_status_cd == 'A' )
                {
                    $v->doc_status_cd = '일부징구';
                }
                else
                {
                    $v->doc_status_cd = '미징구';
                }
            }
            else
            {
                $v->doc_status_cd = '미징구';
            }
            // 1229데이터없는거
            if( !isset($v->final_value3) )
            {
                $v->final_value3 = '';
            }
            // $KB = (Array) DB::TABLE('kb_clclt_data')->SELECT("recent_sise","origin_ltv","highest_ltv","chg_sise")->WHERE("SAVE_STATUS","Y")->WHERE("LOAN_INFO_NO",$v->no)->ORDERBY('SAVE_TIME','DESC')->FIRST();
            // $v->recent_sise = !empty($KB['recent_sise']) ? $KB['recent_sise'] : 0;
            // $v->origin_ltv  = !empty($KB['origin_ltv']) ? $KB['origin_ltv'] : 0;
            // $v->highest_ltv = !empty($KB['highest_ltv']) ? $KB['highest_ltv'] : 0;
            // $v->chg_sise    = !empty($KB['chg_sise']) ? $KB['chg_sise'] : 0;
			//log::debug("arrManager".print_r($arrManager,true));
            unset($array_data);
            
            if(isset($selectHeaders[0])) $array_data[] = Func::addCi($v->cust_info_no);
            if(isset($selectHeaders[1])) $array_data[] = $v->no;
            if(isset($selectHeaders[2])) $array_data[] = '';
            if(isset($selectHeaders[3])) $array_data[] = $v->name;
            if(isset($selectHeaders[4])) $array_data[] = (substr($v->ssn, 0, 6)."-".substr($v->ssn, 6,7));
            if(isset($selectHeaders[5])) $array_data[] = $v->relation;
            if(isset($selectHeaders[6])) $array_data[] = Func::getGender($v->ssn);
            if(isset($selectHeaders[7])) $array_data[] = Func::getAge($v->ssn);
            if(isset($selectHeaders[8])) $array_data[] = Func::getArrayName($array_job_cd,(substr($v->job_cd, 0, 1)));
            if(isset($selectHeaders[9])) $array_data[] = Func::getArrayName($arrayBranch, $v->manager_code);
            if(isset($selectHeaders[10])) $array_data[] = isset($arrManager[$v->manager_id]) ? Func::nvl($arrManager[$v->manager_id]->name, $v->manager_id) : $v->manager_id;
            if(isset($selectHeaders[11])) $array_data[] = Func::getInvStatus($v->status);
            if(isset($selectHeaders[12])) $array_data[] = $v->contract_day;
            if(isset($selectHeaders[13])) $array_data[] = Func::getArrayName($configArr['app_type_cd'], $v->loan_type);
            if(isset($selectHeaders[14])) $array_data[] = Func::getArrayName($getProCode, $v->pro_cd);
            if(isset($selectHeaders[15])) $array_data[] = $v->return_method_nm;                                                           // 수익지급방법
            if(isset($selectHeaders[16])) $array_data[] = Func::getArrayName($configArr['app_type_cd'], $v->loan_type);                   // 최근투자
            if(isset($selectHeaders[17])) $array_data[] = Func::dateFormat($v->contract_date);
            if(isset($selectHeaders[18])) $array_data[] = Func::dateFormat($v->contract_end_date);
            if(isset($selectHeaders[19])) $array_data[] = (sprintf('%0.2f',$v->loan_rate)."%");
            if(isset($selectHeaders[20])) $array_data[] = (sprintf('%0.2f',$v->loan_delay_rate)."%");
            if(isset($selectHeaders[21])) $array_data[] = number_format($v->limit_money);                                                 // 한도
            if(isset($selectHeaders[22])) $array_data[] = $v->monthly_return_money;                                                       // 월수익지급
            if(isset($selectHeaders[23])) $array_data[] = number_format($v->first_loan_money);                                            // 최초투자
            if(isset($selectHeaders[24])) $array_data[] = number_format($v-> loan_money);                                                 // 총투자
            if(isset($selectHeaders[25])) $array_data[] = Func::dateFormat($v->return_date);                                              // 수익지급일
            if(isset($selectHeaders[26])) $array_data[] = number_format($v->return_date_interest);                                        // 수익지급일이자
            if(isset($selectHeaders[27])) $array_data[] = Func::dateFormat($v->last_in_date);                                             // 최근입금일
            if(isset($selectHeaders[28])) $array_data[] = number_format($v->last_in_money);                                               // 최근입금액
            if(isset($selectHeaders[29])) $array_data[] = number_format($v->misu_money);                                                  // 미수비용
            if(isset($selectHeaders[30])) $array_data[] = number_format($v->interest_sum);                                                // 총이자
            if(isset($selectHeaders[31])) $array_data[] = number_format($v->return_origin_sum);                                           // 총 수익지급원금
            if(isset($selectHeaders[32])) $array_data[] = number_format($v->return_interest_sum);                                         // 총 수익지급이자
            if(isset($selectHeaders[33])) $array_data[] = number_format($v->return_cost_money_sum);                                       // 총 수익지급비용
            if(isset($selectHeaders[34])) $array_data[] = number_format(Func::dateTerm($v->contract_date, $v->last_trade_date));          // 사용기간
            if(isset($selectHeaders[35])) $array_data[] = number_format($v->balance);                                                     // 잔액
            if(isset($selectHeaders[36])) $array_data[] = number_format($v->year_income);                                                 // 소득금액
            if(isset($selectHeaders[37])) $array_data[] = $v->first_loan_use_cd;                                                          // 용도
            if(isset($selectHeaders[38])) $array_data[] = $local;
            if(isset($selectHeaders[39])) $array_data[] = Func::getArrayName($configArr['path_cd'], $v->first_path_cd);                   // 신청경로
            if(isset($selectHeaders[40])) $array_data[] = $v->delay_term;
            if(isset($selectHeaders[41])) $array_data[] = $v->delay_term_max;                                                             // 최대연체일
            if(isset($selectHeaders[42])) $array_data[] = $v->delay_term_sum;                                                             // 누적연체일
            if(isset($selectHeaders[43])) $array_data[] = number_format($v->delay_cnt);
            if(isset($selectHeaders[44])) $array_data[] = $v->doc_status_cd;                                                              // 징구서류상태
            if(isset($selectHeaders[45])) $array_data[] = $v->zip1;                                                                       // 실거주지 우편번호
            if(isset($selectHeaders[46])) $array_data[] = $v->addr11.' '.$v->addr12;                                                      // 실거주지 주소
            if(isset($selectHeaders[47])) $array_data[] = Func::phFormat($v->ph21,$v->ph22,$v->ph23);
            if(isset($selectHeaders[48])) $array_data[] = number_format($v->fullpay_money - ($v->charge_money + $v->balance));            // 중도상환수수료
            if(isset($selectHeaders[49])) $array_data[] = $v->return_fee_cd;                                                              // 중도상환수수료율
            if(isset($selectHeaders[50])) $array_data[] = Func::dateFormat($v->buy_date);
            if(isset($selectHeaders[51])) $array_data[] = $v->chng_handle_flag;
            if(isset($selectHeaders[52])) $array_data[] = $v->promise_info;                                                               // 약속일                
            if(isset($selectHeaders[53])) $array_data[] = $v->buy_corp;                                                               // 원채권사           

            if(Func::funcCheckPermit("R006"))
            {
                if(isset($selectHeaders[54])) $array_data[] = number_format($v->base_cost);                                                 // 매입가
            }

            if(isset($selectHeaders[55])) $array_data[] = number_format($v->over_money);                                                   // 가수금액

            $record_count++;
            $excel_data[] = $array_data;
        }
		//log::debug("array_data".print_r($array_data,true));
        log::debug("DATA END");
        
        // 엑셀 익스포트
        ExcelFunc::fastexcelExport($excel_data,$excel_header,$file_name);
        // 파일 저장 여부 확인
        $exists = Storage::disk('excel')->exists($file_name);

        if( isset($exists) )
        {
            $array_result['etc']             = $etc;
            $array_result['result']          = 'Y';
            $array_result['filename']        = $file_name;
            $array_result['excel_no']        = $excel_no;
            $array_result['record_count']    = $record_count;
            $array_result['down_filename']   = $down_filename;
            $array_result['excel_down_div']  = $excel_down_div;
            
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
        }
        else
        {
            $array_result['result']    = 'N';
            $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }
        return $array_result;
    }





    /**
     * 계약관리 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataLoanMngList(Request $request)
    {
        $list   = new DataList(Array("listName"=>"loanmng","listAction"=>'/'.$request->path()));

        if(!isset($request->tabs)) $request->tabs = 'ALL';
        $list->setTabs(Array('ALL'=>'전체', 'N'=>'지급대기', 'A'=>'지급중', 'E'=>'지급종료'),$request->tabs);

        // if( Func::funcCheckPermit("R022") )
        // {
        //     $list->setButtonArray("엑셀다운", "excelDownModal('/erp/loanmngexcel', 'form_loanmng')", "btn-success");
        // }
        $list->setButtonArray("엑셀다운", "excelDownModal('/erp/loanmngexcel', 'form_loanmng')", "btn-success");
        $list->setCheckBox("no");
        $list->setSearchDate('날짜검색',Array('loan_date' => '투자일', 'contract_end_date' => '만기일', 'return_date' => '수익지급일', 'fullpay_date' => '완제일'),'searchDt','Y', 'NONE', '', '');
        $list->setSearchType('pro_cd',Func::getConfigArr('pro_cd'),'상품구분', '', '', '', '', 'Y', '', true);
        $list->setSearchType('loan_info-handle_code',Func::getConfigArr('mo_acct_div'),'법인 구분', '', '', '', '', 'Y', '', true);
        
        $list->setSearchType('status',Vars::$arrayContractSta,'상태');
        $list->setRangeSearchDetail(Array ('contract_day' => '약정일'),'','','단위(일)');
        $list->setSearchDetail(Array(
            'NAME'  => '차입자이름',
            'LOAN_INFO.CUST_INFO_NO'  => '차입자번호',
            'investor_no-inv_seq' => '채권번호',
        ));
        
        $list->setPlusButton("loanMngForm('');");

        return $list;
    }
    
    /**
     * 계약명세 메인화면
     *
     * @param  Void
     * @return view
     */
	public function loanMng(Request $request)
    {
        $list   = $this->setDataLoanMngList($request);

        $list->setLumpForm('DEL', Array('BTN_NAME'=>'삭제','BTN_ACTION'=>'lump_del(this)','BTN_ICON'=>'','BTN_COLOR'=>''));

        $list->setlistTitleCommon(Array
        (
            'investor_no_inv_seq'      => Array('채권번호', 0, '', 'center', '', ''),
            'name'                     => Array('차입자명', 0, '', 'center', '', 'name'),
            'pro_cd'                   => Array('상품구분', 0, '', 'center', '', 'pro_cd'),
            'status'                   => Array('상태', 0, '', 'center', '', 'status'),
            'loan_date'                => Array('투자일자', 0, '', 'center', '', 'loan_date'),
            'contract_end_date'        => Array('만기일자', 0, '', 'center', '', 'contract_end_date'),
            'loan_money'               => Array('투자금액', 0, '', 'center', '', 'loan_money'),
            'balance'                  => Array('투자잔액', 0, '', 'right', '', 'balance'),
            'loan_rate'                => Array('금리', 0, '', 'center', '', 'loan_rate'),
            'loan_pay_term'            => Array('수익지급주기(월)', 0, '', 'center', '', 'loan_pay_term'),
            'contract_day'             => Array('약정일', 0, '', 'center', '', 'contract_day'),
            'return_date'              => Array('차기수익지급일자', 0, '', 'center', '', 'return_date'),
            'return_money'             => Array('차기수익지급일이자', 0, '', 'right', '', 'return_money'),
        ));
        return view('erp.loanMng')->with('result', $list->getList());
    }

    /**
     * 계약명세 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function loanMngList(Request $request)
    {
        $list   = $this->setDataLoanMngList($request);
        $param  = $request->all();

        // Tab count 
		if($request->isFirst=='1')
		{
            $BOXC = DB::table("loan_info")->join("cust_info", "loan_info.cust_info_no", "=", "cust_info.no");
            $BOXC->SELECT(DB::RAW("
                                coalesce(sum(case when loan_info.save_status='Y' then 1 else 0 end),0) as ALL
                                , coalesce(sum(case when loan_info.save_status='Y' and loan_info.status in ('A') then 1 else 0 end),0) as A
                                , coalesce(sum(case when loan_info.save_status='Y' and loan_info.status in ('E') then 1 else 0 end),0) as E
                                , coalesce(sum(case when loan_info.save_status='Y' and loan_info.status in ('N') then 1 else 0 end),0) as N"
                        )
            );
            $BOXC->WHERE('loan_info.save_status','Y');            
            $vcnt = $BOXC->FIRST();
			$r['tabCount'] = array_change_key_case((Array) $vcnt, CASE_UPPER);
		}

        // 기본쿼리
        $LOAN = DB::TABLE("loan_info")->JOIN("cust_info", "loan_info.cust_info_no", "=", "cust_info.no")->JOIN("cust_info_extra", "cust_info.no", "=", "cust_info_extra.cust_info_no");
        $LOAN->SELECT("loan_info.*", "cust_info.name", "cust_info.ssn", "cust_info_extra.ph21", "cust_info_extra.ph22", "cust_info_extra.ph23");
        $LOAN->WHERE('cust_info.save_status','Y');
        $LOAN->WHERE('loan_info.save_status','Y');

        // 'ALL'=>'전체', 'A'=>'정상', 'B'=>'연체', 'E'=>'완제', 'M'=>'매각', 'OV'=>'가지급금', 'N'=>'휴지통'
		if( $request->tabsSelect=="ALL" )
		{
            $param['tabsSelect']    = "ALL";
        }
		else if( $request->tabsSelect=="A" )
		{
            $param['tabSelectNm']   = "loan_info.status";
            $param['tabsSelect']    = Array('A');
        }
        else if( $request->tabsSelect=="E" )
		{
            $param['tabSelectNm']   = "loan_info.status";
            $param['tabsSelect']    = Array('E');
        }
		else if( $request->tabsSelect=="OV" )
		{
            $param['tabSelectNm']   = "loan_info.status";
            $param['tabsSelect']    = Array('E');
            $LOAN->WHERE('loan_info.over_money', ">", '0');
        }
        else if( $request->tabsSelect=="N" )
		{
            $param['tabSelectNm']   = "loan_info.status";
            $param['tabsSelect']    = Array('N');
        }
		else
		{
            $param['tabSelectNm']   = "loan_info.status";
            $param['tabsSelect']    = $request->tabsSelect;
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
                            $LOAN = $LOAN->WHERE('loan_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $LOAN = $LOAN->WHERE('loan_info.investor_no',$searchString[0])
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
                        $LOAN = $LOAN->WHERE('loan_info.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $LOAN = $LOAN->WHERE('loan_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
                    }
                }
            }

            unset($param['searchString']);
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='LOAN_INFO.CUST_INFO_NO' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
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

        $LOAN = $list->getListQuery("LOAN_INFO",'main',$LOAN,$param);
                
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN, $request->page, $request->listLimit, 10, $request->listName);

        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","CUST_INFO_EXTRA"], $rslt);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $getProCode        = Func::getConfigArr('pro_cd');
        $configArr         = Func::getConfigArr();
        $getReturnMethodCd = Func::getConfigArr('return_method_cd');
        $arrBranch         = Func::getBranch();

        $cnt = 0;
        foreach ($rslt as $v)
        {
            $v->onclick                  = 'javascript:loan_info_pop('.$v->cust_info_no.', '.$v->no.');';
            $v->line_style               = 'cursor: pointer;';

            $v->investor_no_inv_seq      = $v->investor_type.$v->investor_no.'-'.$v->inv_seq;

            $v->name                     = Func::nameMasking($v->name, 'N');
            $v->pro_cd                   = Func::getArrayName($getProCode, $v->pro_cd);
            $v->ssn                      = substr($v->ssn, 0, 6);
            $v->loan_date                = Func::dateFormat($v->loan_date);
            $v->ph2                      = Func::phFormat($v->ph21,$v->ph22,$v->ph23);
            $v->loan_rate                = sprintf('%0.2f',$v->loan_rate)."%";
            $v->return_method_cd         = Func::getArrayName($getReturnMethodCd, $v->return_method_cd);
            $v->status                   = Func::getInvStatus($v->status, true);
            $v->contract_end_date        = Func::dateFormat($v->contract_end_date);
            $v->fullpay_date             = Func::dateFormat($v->fullpay_date);
            $v->return_date              = Func::dateFormat($v->return_date);
            $v->kihan_date               = Func::dateFormat($v->kihan_date);
            $v->return_money             = number_format($v->return_money);
            $v->interest_sum             = number_format($v->interest_sum);
            $v->loan_money               = number_format($v->loan_money);
            $v->balance                  = number_format($v->balance);
            $v->fullpay_money            = number_format($v->fullpay_money);
            $v->charge_money             = number_format($v->charge_money);
            $v->manager_name             = Func::nvl($arrBranch[$v->manager_code], $v->manager_code);

            $r['v'][] = $v;
            $cnt ++;

        }
        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path());

        $r['result'] = 1;
        $r['txt'] = $cnt;
        
        return json_encode($r);
    }


    public function loanMngExcel(Request $request)
    {
        if( !Func::funcCheckPermit("E001") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }
        
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $list           = $this->setDataLoanMngList($request);
        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        // 기본쿼리
        $LOAN = DB::TABLE("loan_info")->JOIN("cust_info", "loan_info.cust_info_no", "=", "cust_info.no")->JOIN("cust_info_extra", "cust_info.no", "=", "cust_info_extra.cust_info_no");
        $LOAN->SELECT("loan_info.*", "cust_info.name", "cust_info.ssn", "cust_info_extra.com_ssn", "cust_info_extra.ph21", "cust_info_extra.ph22", "cust_info_extra.ph23");
        $LOAN->WHERE('cust_info.save_status','Y');
        $LOAN->WHERE('loan_info.save_status','Y');

        // 'ALL'=>'전체', 'A'=>'정상', 'B'=>'연체', 'E'=>'완제', 'M'=>'매각', 'OV'=>'가수금', 'N'=>'휴지통'
		if( $request->tabsSelect=="ALL" )
		{
            $param['tabsSelect']    = "ALL";
        }
		else if( $request->tabsSelect=="A" )
		{
            $param['tabSelectNm']   = "loan_info.status";
            $param['tabsSelect']    = Array('A');
        }
		else if( $request->tabsSelect=="B" )
		{
            $param['tabSelectNm']   = "loan_info.status";
            $param['tabsSelect']    = Array('B');
        }
        else if( $request->tabsSelect=="E" )
		{
            $param['tabSelectNm']   = "loan_info.status";
            $param['tabsSelect']    = Array('E');
        }
        else if( $request->tabsSelect=="M" )
		{
            $param['tabSelectNm']   = "loan_info.status";
            $param['tabsSelect']    = Array('M');
        }
		else if( $request->tabsSelect=="OV" )
		{
            $param['tabSelectNm']   = "loan_info.status";
            $param['tabsSelect']    = Array('E');
            $LOAN->WHERE('loan_info.over_money', ">", '0');
        }
        else if( $request->tabsSelect=="N" )
		{
            $param['tabSelectNm']   = "loan_info.status";
            $param['tabsSelect']    = Array('N');
        }
		else
		{
            $param['tabSelectNm']   = "loan_info.status";
            $param['tabsSelect']    = $request->tabsSelect;
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
                            $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])
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
                        $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
                    }
                }
            }
            
            unset($param['searchString']);
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='LOAN_INFO.CUST_INFO_NO' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
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

        $LOAN = $list->getListQuery("LOAN_INFO",'main',$LOAN,$param);

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN, $request->nowPage, $request->listLimit, 10, $request->listName);
        }
        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($LOAN);
        // log::info($query);
        $file_name    = "상품계약관리_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $request_all['work_id'] = Auth::id();
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no)){
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
            $origin_filename = $excel_no.'_'.$request->work_id.'_'.date("YmdHis").'.xlsx'; 
        } else {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $query, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
            $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
        }

        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","CUST_INFO_EXTRA"], $rslt);	// CHUNG DATABASE DECRYPT
        
        // 엑셀
		$excel_header = array('NO','채권번호','차입자명','상품구분','상태','투자일자','만기일자','투자금액','투자잔액','금리','수익지급주기(월)','약정일','차기수익지급일자','차기수익지급일이자');

        $excel_data = array();
    
        // 뷰단 데이터 정리.
        $arrBranch         = Func::getBranch();
        $configArr         = Func::getConfigArr();
        $getProCode        = Func::getConfigArr('pro_cd');
        $board_count       = 1;

        foreach ($rslt as $v)
        {
            $array_data = [
                $board_count,
                $v->investor_type.$v->investor_no.'-'.$v->inv_seq,   //채권번호
                $v->name,
                Func::getArrayName($getProCode, $v->pro_cd),
                Func::getInvStatus($v->status),
                Func::dateFormat($v->loan_date),
                Func::dateFormat($v->contract_end_date),
                number_format($v->loan_money),
                number_format($v->balance),
                sprintf('%0.2f',$v->loan_rate)."%",
                $v->loan_pay_term,
                $v->contract_day,
                Func::dateFormat($v->return_date),
                number_format($v->return_money),
            ];
            $record_count++;
            $board_count++;
            $excel_data[] = $array_data;
        }
        
        // 엑셀 익스포트
        // ExcelFunc::fastexcelExport($excel_data,$excel_header,$file_name);
        ExcelFunc::fastexcelExport($excel_data, $excel_header, $origin_filename);
     
        // 파일 저장 여부 확인
        // $exists = Storage::disk('excel')->exists($file_name);
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
            
            // ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
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
     * 계약 등록 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function loanMngForm(Request $request)
    {
        $arrayConfig  = Func::getConfigArr();
        $arrayBranch  = Func::myPermitBranch();
        $branches     = DB::TABLE("branch")->SELECT("code", "branch_name")->WHERE("parent_code", "T2")->WHERE("save_status", "Y")->get();
        $chargeBranch = array();
        foreach($branches as $branch){
            $chargeBranch[$branch->code] = $branch->branch_name;
        }
        $getProCode   = Func::getConfigArr('pro_cd');

        return view('erp.loanMngForm')
        ->with("arrayConfig", $arrayConfig)
        ->with("arrayBranch", $arrayBranch)
        ->with("chargeBranch", $chargeBranch)
        ->with("arrayProCd", $getProCode);
    }
    
    

    /**
     * 계약등록 입력폼에서 찾기를 할 때, 결과 테이블 HTML 응답한다.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function loanMngCustSearch(Request $request)
    {
        if( !isset($request->cust_search_string) )
        {
            return "검색어를 입력하세요.";
        }
        
        $cust_search_string = $request->cust_search_string;

        // 기본쿼리
        $LOAN = DB::table("cust_info")
                ->join("cust_info_extra", "cust_info.no", "=", "cust_info_extra.cust_info_no")
                ->select("cust_info.no", "cust_info.name", "cust_info.relation", "cust_info.ssn", "cust_info_extra.bank_cd", "cust_info_extra.bank_ssn", "cust_info_extra.bank_cd2", "cust_info_extra.bank_ssn2", "cust_info_extra.bank_cd3", "cust_info_extra.bank_ssn3", "cust_info.vir_acct_no")
                ->where('cust_info.save_status','Y');

        // 검색
        $where = "";
        if( is_numeric($cust_search_string) )
        {
            $where.= "CUST_INFO.NO=".$cust_search_string." ";
            // 6자리 이상인 경우만 검색
            if( strlen($cust_search_string)>=6 )
            {
                $where.= 'or '.Func::encLikeSearchString('cust_info.ssn', $cust_search_string, 'after');
            }
        }
        else
        {
            $where.= Func::encLikeSearchString('cust_info.name', $cust_search_string, 'after');
        }

        if($where!='')
        {
            $where = '('.$where.')';
        }

        $LOAN->whereRaw($where);
        $LOAN->orderBy("cust_info.ssn","ASC");

        $rslt = $LOAN->get();
        $rslt = Func::chungDec(["cust_info","cust_info_extra"], $rslt);	// CHUNG DATABASE DECRYPT
        
        $string = "<table class='table table-sm table-hover card-secondary card-outline mt-1'>";
        $string.= "<tr class='text-center'>";
        $string.= "<td>차입자No</td>";
        $string.= "<td>이름</td>";
        $string.= "<td>생년월일</td>";
        $string.= "<td>은행</td>";
        $string.= "<td>계좌번호</td>";

        $string.= "<td hidden>은행코드</td>";
        $string.= "<td hidden>법인계좌번호</td>";
        $string.= "</tr>";

        foreach( $rslt as $v )
        {
            $string.= "<tr role='button' onclick='selectLoanInfo(".$v->no.");'>";
            $string.= "<td id='cust_info_no_".$v->no."' class='text-center'>".$v->no."</td>";
            $string.= "<td id='cust_name_".$v->no."' class='text-center'>".$v->name."</td>";
            $string.= "<td id='cust_ssn_".$v->no."' class='text-center'>".Func::ssnFormat($v->ssn, 'A')."</td>";
            $string.= "<td id='cust_bank_name_".$v->no."' class='text-center'>".Func::getArrayName(Func::getConfigArr('bank_cd'), $v->bank_cd)."</td>";
            $string.= "<td id='cust_bank_ssn_".$v->no."' class='text-center'>".$v->bank_ssn."</td>";
            
            $string.= "<td id='cust_bank_cd_".$v->no."'  class='text-center' hidden>".$v->bank_cd."</td>";
            $string.= "<td id='vir_acct_no_".$v->no."'  class='text-center' hidden>".$v->vir_acct_no."</td>";
            $string.= "</tr>";
        }
        $string.= "</table>";

        return $string;
    }

    /**
     * 계약등록 입력폼에서 찾기를 할 때, 결과 테이블 HTML 응답한다.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function loanMngUsrSearch(Request $request)
    {
        if( !isset($request->usr_search_string) )
        {
            return "검색어를 입력하세요.";
        }
        
        $usr_search_string = $request->usr_search_string;

        // 기본쿼리
        $LOAN = DB::table("loan_usr_info")
                ->select("*")
                ->where('save_status','Y');

        // 검색
        $where = "";
        if( is_numeric($usr_search_string) )
        {
            $where.= "investor_no=".$usr_search_string." ";
            
            // 6자리 이상인 경우만 검색
            if( strlen($usr_search_string)>=6 )
            {
                $where.= 'or '.Func::encLikeSearchString('ssn', $usr_search_string, 'after');
            }
        }
        else
        {
            $where.= Func::encLikeSearchString('name', $usr_search_string, 'after');
        }

        if($where!='')
        {
            $where = '('.$where.')';
        }

        $LOAN->whereRaw($where);
        $LOAN->orderBy("ssn","ASC");

        $rslt = $LOAN->get();
        $rslt = Func::chungDec(["loan_usr_info"], $rslt);	// CHUNG DATABASE DECRYPT
        
        $string = "<table class='table table-sm table-hover card-secondary card-outline mt-1'>";
        $string.= "<tr class='text-center'>";
        $string.= "<td hidden>투자자No</td>";
        $string.= "<td>투자자No</td>";
        $string.= "<td>이름</td>";
        $string.= "<td>관계</td>";
        $string.= "<td>생년월일</td>";
        $string.= "<td>은행</td>";
        $string.= "<td>계좌번호</td>";

        $string.= "<td hidden>은행코드</td>";
        $string.= "</tr>";

        foreach( $rslt as $v )
        {
            $string.= "<tr role='button' onclick='selectUsrInfo(".$v->no.");'>";
            $string.= "<td id='loan_usr_info_no_".$v->no."' class='text-center' hidden>".$v->no."</td>";
            $string.= "<td id='loan_usr_info_investor_no_".$v->no."' class='text-center'>".$v->investor_no."</td>";
            $string.= "<td id='loan_usr_info_name_".$v->no."'    class='text-center'>".$v->name."</td>";
            $string.= "<td id='loan_usr_info_relation_".$v->no."'    class='text-center'>".$v->relation."</td>";
            $string.= "<td id='loan_usr_info_ssn_".$v->no."'     class='text-center'>".Func::ssnFormat($v->ssn, 'A')."</td>";
            $string.= "<td id='loan_usr_info_bank_name_".$v->no."' class='text-center'>".Func::getArrayName(Func::getConfigArr('bank_cd'), $v->bank_cd)."</td>";
            $string.= "<td id='loan_usr_info_bank_ssn_".$v->no."' class='text-center'>".$v->bank_ssn."</td>";
            
            $string.= "<td id='loan_usr_info_bank_cd_".$v->no."'  class='text-center' hidden>".$v->bank_cd."</td>";
            $string.= "</tr>";
        }
        $string.= "</table>";

        return $string;
    }

    /**
     * 계약 등록
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function loanMngAction(Request $request)
    {
        // 권한체크추가
        // if(!Func::funcCheckPermit("R001"))
        // {
        //     return "계약등록 권한이 없습니다.";
        // }

        $v = $request->input();

        if( !isset($v['cust_info_no']) || !isset($v['loan_usr_info_no']))
        {
            return "파라미터 오류";
        }

        // 차입자 정보
        $cust = DB::table("cust_info")->select("*")->where("no", $v['cust_info_no'])->where("save_status", "Y")->first();
        $cust = Func::chungDec(["cust_info"], $cust);	// CHUNG DATABASE DECRYPT

        if( !$cust )
        {
            return "차입자 정보를 찾을 수 없습니다.";
        }

        // 투자자 정보
        $loan_usr = DB::table("loan_usr_info")->select("*")->where("no", $v['loan_usr_info_no'])->where("save_status", "Y")->first();
        $loan_usr = Func::chungDec(["loan_usr_info"], $loan_usr);	// CHUNG DATABASE DECRYPT

        if( !$loan_usr )
        {
            return "투자자 정보를 찾을 수 없습니다.";
        }

        // 계약등록
        $LOAN = $v;

        if(isset($LOAN['handle_code']) && isset($LOAN['pro_cd']) && $LOAN['pro_cd'] == '03' )
        {
            $LOAN['investor_type']    =  $LOAN['handle_code']==1 ? 'TM' : ($LOAN['handle_code']==2 ? 'HM' : 'YM');
        }
        else if(isset($loan_usr->handle_code) && $loan_usr->handle_code != $LOAN['handle_code'] )
        {
            $LOAN['investor_type']    =  $loan_usr->handle_code==1 ? 'T' : ($loan_usr->handle_code==2 ? 'H' : 'Y');
        }

        $LOAN['contract_date']        = $LOAN['loan_date'] = $LOAN['take_date'] = $LOAN['app_date'] = Func::delChar($LOAN['contract_date'], '-');
        $LOAN['contract_end_date']    = Func::delChar($LOAN['contract_end_date'], '-');
        $LOAN['invest_rate']          = $LOAN['loan_rate'] = $LOAN['loan_delay_rate'] = sprintf('%0.2f', $LOAN['invest_rate']);
        $LOAN['income_rate']          = sprintf('%0.2f', $LOAN['income_rate']);
        $LOAN['local_rate']           = sprintf('%0.2f', $LOAN['local_rate']);
        $LOAN['balance']              = $LOAN['platform_fee_rate'] = 0;
        $LOAN['legal_rate']           = Vars::$curMaxRate;
        $LOAN['loan_money']           = $LOAN['app_money'] = $LOAN['total_loan_money'] = $LOAN['first_loan_money'] = Func::delChar($LOAN['loan_money'], ',');
        $LOAN['monthly_return_money'] = 0;
        $LOAN['loan_type']            = '01';
        $LOAN['pay_term']             = $LOAN['loan_pay_term'];

        $date1 = Carbon::parse($LOAN['contract_date']);
        $date2 = Carbon::parse($LOAN['contract_end_date'])->addDay();

        if($LOAN['loan_term'] != $date1->diffInMonths($date2))
        {
            // 투자일의 일자가 만기일의 일자보다 클경우 개월수 매칭이 안맞음
            // ex. 투자기간 18개월 : 2024-05-31 - 2025-11-30 -> 개월수 계산시에 17개월로 측정됨
            // 추후에 해당 내용에 대해서는 로직수정을 하거나, 예외처리를 해야함
            // addDay 추가해서 수정함
            
            return "투자기간을 확인해주세요.";
        }

        DB::beginTransaction();

        $loanInfoNo = Loan::insertLoanInfo($LOAN);

        // 오류 업데이트 후 쪽지 발송
        if(!is_numeric($loanInfoNo))
        {
            DB::rollBack();

            Log::debug($loanInfoNo);
            return '계약등록시 에러가 발생했습니다.('.$loanInfoNo.')';
        }
        
        Log::info('계약등록 > 차입자 번호 : '.$LOAN['cust_info_no'].', 투자자 번호 : '.$LOAN['loan_usr_info_no'].', 계약번호 : '.$loanInfoNo);

        // 기관차입이 아닐때만 스케줄 자동생성
        if($LOAN['pro_cd'] != '03')
        {
            $save_status = 'Y';
            $save_time   = date("YmdHis");
            $save_id     = Auth::id();

            $valcday = [];
            $valcday['loan_info_no'] = $loanInfoNo;
            $valcday['cday_date']    = $LOAN['loan_date'];
            $valcday['contract_day'] = $LOAN['contract_day'];
            $valcday['save_status']  = $save_status;
            $valcday['save_time']    = $save_time;
            $valcday['save_id']      = $save_id;
            $rslt = DB::dataProcess('UST', 'loan_info_cday', $valcday, ['loan_info_no'=>$valcday['loan_info_no'], 'cday_date'=>$valcday['cday_date']]);
            if( $rslt!="Y" )
            {
                DB::rollBack();
                return "실행오류#1";
            }
    
            $valrate = [];
            $valrate['loan_info_no']    = $loanInfoNo;
            $valrate['rate_date']       = $LOAN['loan_date'];
            $valrate['loan_rate']       = $LOAN['loan_rate'];
            $valrate['loan_delay_rate'] = $LOAN['loan_delay_rate'];
            $valrate['save_status']     = $save_status;
            $valrate['save_time']       = $save_time;
            $valrate['save_id']         = $save_id;
            $rslt = DB::dataProcess('UST', 'loan_info_rate', $valrate, ['loan_info_no'=>$valrate['loan_info_no'], 'rate_date'=>$valrate['rate_date']]);
            if( $rslt!="Y" )
            {
                DB::rollBack();
                return "실행오류#2";
            }

            $valinvrate = [];
            $valinvrate['loan_info_no']    = $loanInfoNo;
            $valinvrate['rate_date']       = $LOAN['loan_date'];
            $valinvrate['invest_rate']     = $LOAN['invest_rate'];
            $valinvrate['save_status']     = $save_status;
            $valinvrate['save_time']       = $save_time;
            $valinvrate['save_id']         = $save_id;
            $rslt = DB::dataProcess('UST', 'loan_info_invest_rate', $valinvrate, ['loan_info_no'=>$valinvrate['loan_info_no'], 'rate_date'=>$valinvrate['rate_date']]);
            if( $rslt!="Y" )
            {
                DB::rollBack();
    
                return "실행오류#3";
            }
            
            // 분배예정스케줄 생성
            $inv = new Invest($loanInfoNo);
            $array_inv_plan = $inv->buildPlanData($LOAN['contract_date'], $LOAN['contract_end_date']);
            $rslt = $inv->savePlan($array_inv_plan);
            if(!isset($rslt) || $rslt != "Y")
            {
                DB::rollBack();
                return "실행오류#4";
            }
    
            // 거래내역 등록
            $_IN['trade_type']       = '11';
            $_IN['trade_date']       = $LOAN['contract_date'];
            $_IN['trade_money']      = $LOAN['loan_money'];
            $_IN['loan_usr_info_no'] = $LOAN['loan_usr_info_no'];
            $_IN['cust_info_no']     = $LOAN['cust_info_no'];
            $_IN['loan_info_no']     = $loanInfoNo;
            $_IN['trade_fee']        = 0;
    
            $t = new Trade($loanInfoNo);
            $loan_info_trade_no = $t->tradeOutInsert($_IN, Auth::id());
    
            // 오류 업데이트 후 쪽지 발송
            if(!is_numeric($loan_info_trade_no))
            {
                DB::rollBack();
    
                Log::debug($loanInfoNo);
                return '차입금 등록시 에러가 발생했습니다.('.$loanInfoNo.')';
            }
            
            Log::info('거래내역등록 > 계약번호 : '.$loanInfoNo.', 거래내역번호 : '.$loan_info_trade_no);
        }

        DB::commit();
        
		return "Y";     
    }




















    /**
     * 고객정보창 계약정보
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function loanMain(Request $request)
    {
        // log::info('loanmain'.print_r($request->all(),true));
        if(isset($request->no))
        {
            $rslt = DB::TABLE("LOAN_INFO")->SELECT("*")->WHERE('NO',$request->no)->FIRST();
            $rslt = Func::chungDec(["LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

            return view('erp.loanMain')->with("no", $request->no)
                                       ->with("loantab", $request->loantab)
                                       ->with("rslt", $rslt);
        }
    }
 
     /**
      * 고객정보창 계약정보상세
      *
      * @param  \Illuminate\Http\Request  $request
      * @return view
      */
    public function loanInfo(Request $request)
    {
        // 환경설정
        $configArr = Func::getConfigArr();
        $array_member  = Func::getUserList('');
        $array_branch  = Func::getBranchList();

        $v = null;
        if( isset($request->no) && is_numeric($request->no) )
        {
            //$v = DB::TABLE("LOAN_INFO")->SELECT("*")->WHERE("SAVE_STATUS","Y")->->FIRST();
            $LOAN = DB::TABLE("LOAN_INFO")->JOIN("CUST_INFO", "LOAN_INFO.CUST_INFO_NO", "=", "CUST_INFO.NO")
            ->JOIN("CUST_INFO_EXTRA", "CUST_INFO.NO", "=", "CUST_INFO_EXTRA.CUST_INFO_NO")
            ->JOIN("loan_usr_info", "loan_usr_info.no", "=", "loan_info.loan_usr_info_no");

            $LOAN->SELECT("LOAN_INFO.*", "CUST_INFO.NAME", "CUST_INFO.SSN", "CUST_INFO_EXTRA.PH21", "CUST_INFO_EXTRA.PH22", "CUST_INFO_EXTRA.PH23", "loan_usr_info.name as loan_usr_info_name");
            $LOAN->WHERE('CUST_INFO.SAVE_STATUS','Y');
            $LOAN->WHERE('loan_usr_info.save_status','Y');
            $LOAN->WHERE('LOAN_INFO.SAVE_STATUS','Y');
            $LOAN->WHERE("LOAN_INFO.NO", $request->no);
            $v = $LOAN->FIRST();
            $v = Func::chungDec(["LOAN_INFO","CUST_INFO","CUST_INFO_EXTRA"], $v);	// CHUNG DATABASE DECRYPT

            // 값 정리
            $v->return_fee_cd           = Func::getArrayName($configArr['return_fee_rate'], $v->return_fee_cd);
            $v->return_method_nm        = Func::getArrayName($configArr['return_method_cd'], $v->return_method_cd);
            $v->path_cd                 = Func::getArrayName($configArr['path_cd'], $v->path_cd);
            $v->pro_cd                  = Func::getArrayName($configArr['pro_cd'], $v->pro_cd);

            $v->loan_usr_info_name      = Func::decrypt($v->loan_usr_info_name, 'ENC_KEY_SOL') ?? '';

            $v->fullpay_cd_nm           = Func::getArrayName($configArr['flpay_cd'], $v->fullpay_cd);
            $v->opb                     = Func::getOpb($v);

            // 계약철회 가능여부 - 계약일 20161219 이후, 계약일 17일이내(6일 투자시 23일까지 가능)
            $contract_cancel = '';            
            if($v->contract_date>='20161219')
            {
                $cancelDt = Loan::addDay($v->contract_date, 17);
                
                if(date("Ymd")<=$cancelDt)
                    $contract_cancel = '(철회가능대상 : '.Func::dateFormat($cancelDt).')';
            }

            // 최초 잔액, 미수금
            $firstTrade['sum'] = $firstTrade['loan_money'] = (isset($v->loan_money)) ? $v->loan_money : 0;

            $return_money['return_origin_sum']    = $v->return_origin_sum ?? 0;
            $return_money['return_interest_sum']  = $v->return_interest_sum ?? 0;
            $return_money['sum'] = $return_money['return_origin_sum'] + $return_money['return_interest_sum'];
        }

        return view('erp.loanInfo')->with("no", $request->no)->with("v", $v)->with("contract_cancel", $contract_cancel)->with("result", $v)->with("return_money", $return_money)->with("firstTrade", $firstTrade);
    }
 
     /**
      * 고객정보창 소멸시효 이력 팝업페이지
      *
      * @param  \Illuminate\Http\Request  $request
      * @return view
      */
    public function loanLostDate(Request $request)
    {
        $request->isDebug = true;
        $no = $request->loan_info_no;

        $LOAN = DB::TABLE("LOAN_INFO")->JOIN("CUST_INFO", "LOAN_INFO.CUST_INFO_NO", "=", "CUST_INFO.NO");
        $LOAN->SELECT("CUST_INFO.NAME, LOAN_INFO.NO, LOAN_INFO.CUST_INFO_NO, LOAN_INFO.LOST_DATE");
        $LOAN->WHERE('CUST_INFO.SAVE_STATUS','Y');
        $LOAN->WHERE('LOAN_INFO.SAVE_STATUS','Y');
        $LOAN->WHERE("LOAN_INFO.NO", $no);
        $v = $LOAN->FIRST();
        $v = Func::chungDec(["LOAN_INFO","CUST_INFO"], $v);	// CHUNG DATABASE DECRYPT

        return view('erp.loanLostDatePop')->with("no", $no)->with("v", $v);
    }

    /**
     * 고객정보창 계약정보 내 거래원장
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function loanTrade(Request $request)
    {
        $no = $request->no;
        $loan = new Loan($no);

        if( $loan->no==NULL )
        {
            return "<div class='p-5 text-center'>계약정보에 오류가 있습니다.</div>";
        }

        $arrayUserId  = Func::getUserId();
        $array_config = Func::getConfigArr();
        $array_pro_cd = Func::getConfigArr('pro_cd');
        $array_return_method = $array_config['return_method_cd'];

        $loan->loanInfo['return_method_nm'] = Func::getArrayName($array_return_method, $loan->loanInfo['return_method_cd']);
        $loan->loanInfo['return_fee_nm']    = Func::getArrayName($array_config['return_fee_rate'], $loan->loanInfo['return_fee_cd']);

        // 조건변경내역
        $condition_arr = [];
        
        // 약정일로그
        $bf_cday        = "0";
        $condition_cday = DB::TABLE("LOAN_INFO_CDAY")
                            ->SELECT("CDAY_DATE AS TRADE_DATE", "CONTRACT_DAY AS AFTER", "SAVE_ID", "SAVE_TIME", "DEL_ID", "DEL_TIME", "SAVE_STATUS")
                            ->WHERE("LOAN_INFO_NO", $no)
                            ->ORDERBY("TRADE_DATE")
                            ->GET();
        $condition_cday = Func::chungDec(["LOAN_INFO_CDAY"], $condition_cday);	// CHUNG DATABASE DECRYPT
        
        foreach($condition_cday as $cday)
        {
            $cday->div         = "약정일변경";
            if($cday->save_status=="Y")
            {
                $cday->memo_before = "[변경전] ".$bf_cday."일";
            }
            else 
            {
                $cday->memo_before = "";
            }
            $cday->memo_after  = "[변경후] ".$cday->after."일";

            $condition_arr[$cday->save_time][] = $cday;
            if($cday->save_status=="Y") $bf_cday = $cday->after;
        }

        // 이율변경로그
        $bf_rate        = "";
        $bf_delay_rate  = "";
        $condition_rate = DB::TABLE("LOAN_INFO_RATE")
                            ->SELECT("RATE_DATE AS TRADE_DATE", "LOAN_RATE AS AFTER", "LOAN_DELAY_RATE AS AFTER_DELAY", "SAVE_ID", "SAVE_TIME", "SAVE_STATUS")
                            ->WHERE("SAVE_STATUS", "Y")
                            ->WHERE("LOAN_INFO_NO", $no)
                            ->ORDERBY("TRADE_DATE")
                            ->GET();
        $condition_rate = Func::chungDec(["LOAN_INFO_RATE"], $condition_rate);	// CHUNG DATABASE DECRYPT

        foreach($condition_rate as $crate)
        {
            $crate->div         = "이율변경";
            $crate->memo_before = "[변경전] ".sprintf("%.03f", $bf_rate)."%";
            $crate->memo_after  = "[변경후] ".sprintf("%.03f", $crate->after)."%";

            $condition_arr[$crate->save_time][] = $crate;
            $bf_rate       = $crate->after;
            $bf_delay_rate = $crate->after_delay;
        }
        
        // 기본쿼리
        $trade = DB::TABLE("LOAN_INFO_TRADE")->SELECT("*")->WHERE('LOAN_INFO_NO',$no)->ORDERBY("SEQ", "DESC")->GET();
        $trade = Func::chungDec(["LOAN_INFO_TRADE"], $trade);	// CHUNG DATABASE DECRYPT
        
        $result = [];
        foreach( $trade as $v )
        {
            if( $v->trade_div=="O" )
            {
                $v->trade_type_name = ( isset($array_config['trade_out_type'][$v->trade_type]) ) ? $array_config['trade_out_type'][$v->trade_type] : $v->trade_type ;
                $v->trade_path_name = ( isset($array_config['trade_out_path'][$v->trade_path_cd]) ) ? $array_config['trade_out_path'][$v->trade_path_cd] : $v->trade_path_cd ;
                if( $v->trade_type=="91" || $v->trade_type=="99" )
                {
                    $v->trade_color = "status-bg-n";
                }
                else
                {
                    $v->trade_color = "status-bg-a";
                }
            }
            else if( $v->trade_div=="I" )
            {
                $v->trade_type_name = ( isset($array_config['trade_in_type'][$v->trade_type]) ) ? $array_config['trade_in_type'][$v->trade_type] : $v->trade_type ;
                $v->trade_path_name = ( isset($array_config['trade_in_path'][$v->trade_path_cd]) ) ? $array_config['trade_in_path'][$v->trade_path_cd] : $v->trade_path_cd ;
                if( $v->trade_type=="06" || $v->trade_type=="07" )
                {
                    $v->trade_color = "status-bg-b";
                }
                else
                {
                    $v->trade_color = "";
                }
            }
            else if( $v->trade_div=="C" )
            {
                $v->trade_type_name = ( isset($array_config['trade_cost_type'][$v->trade_type]) ) ? $array_config['trade_cost_type'][$v->trade_type] : $v->trade_type ;
                $v->trade_path_name = "";
                $v->trade_color     = "status-bg-b";
            }
            else if( $v->trade_div=="F" )
            {
                $v->trade_type_name = ( isset($array_config['trade_fee_type'][$v->trade_type]) ) ? $array_config['trade_fee_type'][$v->trade_type] : $v->trade_type ;
                $v->trade_path_name = "";
                $v->trade_color     = "status-bg-d";
            }

            if( $v->save_status=="N" )
            {
                $v->trade_type_name = $v->trade_type_name."(삭)";
                $v->trade_color     = "bg-secondary";
            }

            $result[] = $v;
        }
        // Log::Debug((array)$result);
        return view('erp.loanTrade')->with("result", $result)
                                    ->with('simple', $loan->loanInfo)
                                    ->with('condition', $condition_arr);

    }

    /**
      * 고객정보창 계약정보 내 계약통합정보
      *
      * @param  \Illuminate\Http\Request  $request
      * @return view
      */
    public function loanTotal(Request $request)
    {
        // 차입자번호
        $cust_info_no = $request->no;
        $array_config = Func::getConfigArr();
        $array_pro_cd = Func::getConfigArr('pro_cd');

        $array_contracts = array();
        $rs = DB::table("LOAN_INFO")->WHERE("SAVE_STATUS", "Y")->WHERE("CUST_INFO_NO", $cust_info_no)->WHEREIN("status", ['A','B','C','D','M','E'])->ORDERBY("NO","DESC")->GET();
        $rs = Func::chungDec(["LOAN_INFO"], $rs);	// CHUNG DATABASE DECRYPT
        
        foreach($rs as $v)
        {
            // $v->loan_rate           = number_format($v->loan_rate, 2);
            // $v->loan_delay_rate     = number_format($v->loan_delay_rate, 2);
            $array_contracts[] = $v;
        }

        return view('erp.loanTotal')->with("cust_info_no", $cust_info_no)->with("array_contracts", $array_contracts)->with("array_pro_cd", $array_pro_cd)->with("array_config", $array_config);
    }



    /**
     * 예상이자조회
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function loanInterest(Request $request)
    {
        //$request->isDebug = true;
        $param = $request->input();
        if( !isset($param['no']) || !is_numeric($param['no']) )
        {
            return "계약번호가 등록되지 않았습니다.";
        }

        $no = $param['no'];
        if( !isset($param['today']) || !$param['today'] )
        {
            $today = date("Ymd");
        }
        else
        {
            $today = str_replace("-","",$param['today']);
        }

        if( isset($param['days']) && $param['days']>0 )
        {
            $days = $param['days'];
        }
        else
        {
            $days = 30;
        }

        $loan = new Loan($no);
        $array_config = Func::getConfigArr();
        $array_pro_cd = Func::getConfigArr('pro_cd');
        $loan->loanInfo['return_method_nm'] = Func::getArrayName($array_config['return_method_cd'], $loan->loanInfo['return_method_cd']);
        $loan->loanInfo['return_fee_nm']    = Func::getArrayName($array_config['return_fee_rate'], $loan->loanInfo['return_fee_cd']);

        if( !$loan->loanInfo['balance'] || $loan->loanInfo['status']=='S' && $loan->loanInfo['fullpay_date']!='')
        {
            return view('erp.loanInterest')->with('no', $no)->with('today', $today)->with('loan', $loan)->with('result', [])->with('simple', $loan->loanInfo);
        }

        // 입력받은 날로 7일치 이자 계산
        $sdate = $today;
        $edate = Loan::addDay($today,$days);
        // log::debug($sdate." ~ ".$edate);
        $array_interest = Array();
        for( $d=$sdate; $d<=$edate; $d=Loan::addDay($d) )
        {
            $val = $loan->getInterest($d);
            $val['holiday'] = ( isset($loan->holiday[$d]) && $loan->holiday[$d] );
            $val['weekday'] = date('w',Loan::dateToUnixtime($d));
            
            $array_interest[$d] = $val;
        }
        $rslt['loan']       = $loan;
        $rslt['today']      = $today;
        $rslt['result']     = $array_interest;
        $rslt['no']         = $no;
        $rslt['simple']     = $loan->loanInfo;
        
        if( isset($request->returnType) && $request->returnType=="json" )
        {
            return json_encode($rslt);
        }
        else
        {
            return view('erp.loanInterest')->with($rslt);
        }
    }

    /**
     * 수익지급처리 미리보기
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function loanReturnPreview(Request $request)
    {
        $param = $request->input();
        if( !isset($param['loan_info_no']) || !is_numeric($param['loan_info_no']) )
        {
            return "계약번호가 등록되지 않았습니다.";
        }

        $no = $param['loan_info_no'];
        if( !isset($param['trade_date']) || !$param['trade_date'] )
        {
            $trade_date = date("Ymd");
        }
        else
        {
            $trade_date = str_replace("-","",$param['trade_date']);
        }

        $loan = new Loan($no);

        $array_config = Func::getConfigArr();
        $array_pro_cd = Func::getConfigArr('pro_cd');
        $array_return_method = $array_config['return_method_cd'];

        $rslt = DB::TABLE("CUST_INFO")->SELECT("*")->WHERE('SAVE_STATUS','Y')->WHERE("NO", $loan->loanInfo['cust_info_no'])->FIRST();
        $rslt = Func::chungDec(["CUST_INFO"], $rslt);	// CHUNG DATABASE DECRYPT
        $cust = (Array) $rslt;

        return view('erp.loanReturnPreview')->with('trade_date', $trade_date)->with('cust', $cust)->with('loan', $loan->loanInfo)->with('array_config',$array_config);
    }

    


    /**
     * 수익지급스케줄
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function loanPlan(Request $request)
    {
        $array_config = Func::getConfigArr('');
        $array_pro_cd = Func::getConfigArr('pro_cd');

		// 휴일
		$holiday = Cache::remember('Func_getBizDay', 86400, function()
		{
			$rslt = DB::TABLE("DAY_CONF")->SELECT("*")->GET();
			foreach( $rslt as $v )
			{
				$day           = str_replace("-","",$v->day);
				$holiday[$day] = $day;
			}
			return $holiday;
		});

        $rslt = DB::TABLE("LOAN_INFO")->SELECT("*")->WHERE('SAVE_STATUS','Y')->WHERE("LOAN_INFO.NO", $request->no)->FIRST();
        $rslt = Func::chungDec(["LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT
        $loan = (Array) $rslt;

        $loan['return_method_nm'] = Func::getArrayName($array_config['return_method_cd'], $loan['return_method_cd']);
        $loan['return_fee_nm']    = Func::getArrayName($array_config['return_fee_rate'], $loan['return_fee_cd']);

        return view('erp.loanPlan')->with('no', $request->no)->with('simple', $loan)->with('holiday', $holiday);
    }


    /**
     * 화해상환스케줄
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function loanSettlePlan(Request $request)
    {
        $array_config = Func::getConfigArr('');
        $array_pro_cd = Func::getConfigArr('pro_cd');

		// 휴일
		$holiday = Cache::remember('Func_getBizDay', 86400, function()
		{
			$rslt = DB::TABLE("DAY_CONF")->SELECT("*")->GET();
			foreach( $rslt as $v )
			{
				$day           = str_replace("-","",$v->day);
				$holiday[$day] = $day;
			}
			return $holiday;
		});

        $rslt = DB::TABLE("LOAN_INFO")->SELECT("*")->WHERE('SAVE_STATUS','Y')->WHERE("LOAN_INFO.NO", $request->no)->FIRST();
        $rslt = Func::chungDec(["LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT
        $loan = (Array) $rslt;

        $loan['return_method_nm'] = Func::getArrayName($array_config['return_method_cd'], $loan['return_method_cd']);
        $loan['return_fee_nm']    = Func::getArrayName($array_config['return_fee_rate'], $loan['return_fee_cd']);


        // 화해정보
        $settle = DB::TABLE("LOAN_SETTLE")->SELECT("*")->WHERE('SAVE_STATUS','Y')->WHERE('STATUS','Y')->WHERE("LOAN_INFO_NO", $request->no)->ORDERBY('NO','DESC')->FIRST();
        $settle = Func::chungDec(["LOAN_SETTLE"], $settle);	// CHUNG DATABASE DECRYPT

        if( !$settle )
        {
            return "<div class='p-5 text-center'>화해 결재정보를 찾을 수 없습니다.</div>";
        }

        $settle->settle_reason_nm = Func::nvl($array_config['stl_rsn_cd'][$settle->settle_reason_cd], $settle->settle_reason_cd);     //settle_reason_cd
    
        // 화해스케줄
        $plans = DB::TABLE("LOAN_SETTLE_PLAN")->SELECT("*")->WHERE("LOAN_SETTLE_NO", $settle->no)->ORDERBY("SEQ","ASC")->GET();
        $plans = Func::chungDec(["LOAN_SETTLE_PLAN"], $plans);	// CHUNG DATABASE DECRYPT



        $view_opt = "";
        if( isset($request->view_opt) )
        {
            $view_opt = $request->view_opt;
        }
        
        return view('erp.loanSettlePlan')->with('no', $request->no)->with('simple', $loan)->with('settle', $settle)->with('plans', $plans)->with('holiday', $holiday)->with('holiday', $holiday)->with('view_opt', $view_opt);
    }


    /**
     * 계약정보 변경내역
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function loanLog(Request $request)
    {
        $no = $request->no;

        $array_user_id = Func::getUserId();
        $array_branch  = Func::getBranch();
        $array_config  = Func::getConfigArr();
        $array_pro_cd  = Func::getConfigArr('pro_cd');

        $rslt = DB::TABLE("LOAN_INFO")->SELECT("*")->WHERE('SAVE_STATUS','Y')->WHERE("LOAN_INFO.NO", $no)->FIRST();
        $rslt = Func::chungDec(["LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT
        $loan = (Array) $rslt;

        $loan['return_method_nm'] = Func::getArrayName($array_config['return_method_cd'], $loan['return_method_cd']);
        $loan['return_fee_nm']    = Func::getArrayName($array_config['return_fee_rate'], $loan['return_fee_cd']);

        // 금리
        $rate = [];
        $trade = DB::TABLE("LOAN_INFO_RATE")->SELECT("*")->WHERE('LOAN_INFO_NO',$no)->WHERE('SAVE_STATUS','Y')->ORDERBY("RATE_DATE", "DESC")->ORDERBY("SAVE_TIME", "DESC")->GET();
        $trade = Func::chungDec(["LOAN_INFO_RATE"], $trade);	// CHUNG DATABASE DECRYPT

        foreach( $trade as $v )
        {
            $v->save_nm = Func::getArrayName($array_user_id, $v->save_id);
            $rate[] = $v;
        }
        // 약정일
        $cday = [];
        $trade = DB::TABLE("LOAN_INFO_CDAY")->SELECT("*")->WHERE('LOAN_INFO_NO',$no)->WHERE('SAVE_STATUS','Y')->ORDERBY("CDAY_DATE", "DESC")->ORDERBY("SAVE_TIME", "DESC")->GET();
        $trade = Func::chungDec(["LOAN_INFO_CDAY"], $trade);	// CHUNG DATABASE DECRYPT

        foreach( $trade as $v )
        {
            $v->save_nm = Func::getArrayName($array_user_id, $v->save_id);
            $cday[] = $v;
        }
        // 계약정보
        $cont = [];
        $trade = DB::TABLE("LOAN_INFO_LOG")->SELECT("*")->WHERE('LOAN_INFO_NO',$no)->ORDERBY("SAVE_TIME", "ASC")->GET();
        $trade = Func::chungDec(["LOAN_INFO_LOG"], $trade);	// CHUNG DATABASE DECRYPT

        foreach( $trade as $v )
        {
            $v->loan_rate_color            = ( isset($p) && $v->loan_rate!=$p->loan_rate )                       ? "FFDDDD" : "FFFFFF" ;
            $v->contract_date_color        = ( isset($p) && $v->contract_date!=$p->contract_date )               ? "FFDDDD" : "FFFFFF" ;
            $v->contract_end_date_color    = ( isset($p) && $v->contract_end_date!=$p->contract_end_date )       ? "FFDDDD" : "FFFFFF" ;
            $v->contract_day_color         = ( isset($p) && $v->contract_day!=$p->contract_day )                 ? "FFDDDD" : "FFFFFF" ;
            $v->monthly_return_money_color = ( isset($p) && $v->monthly_return_money!=$p->monthly_return_money ) ? "FFDDDD" : "FFFFFF" ;
            $v->balance_color              = ( isset($p) && $v->balance!=$p->balance )                           ? "FFDDDD" : "FFFFFF" ;
            $v->take_date_color            = ( isset($p) && $v->take_date!=$p->take_date )                       ? "FFDDDD" : "FFFFFF" ;
            $v->return_date_color          = ( isset($p) && $v->return_date!=$p->return_date )                   ? "FFDDDD" : "FFFFFF" ;
            $v->kihan_date_color           = ( isset($p) && $v->kihan_date!=$p->kihan_date )                     ? "FFDDDD" : "FFFFFF" ;
            $v->status_color               = ( isset($p) && $v->status!=$p->status )                             ? "FFDDDD" : "FFFFFF" ;

            $cont[] = $v;
            $p = $v;
        }
        krsort($cont);

        return view('erp.loanLog')->with("rate", $rate)->with("cday", $cday)->with("cont", $cont)->with('simple', $loan);
    }


    /**
     *  보증인정보
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function loanGuarantor(Request $request)
    {
        $configArr      = Func::getConfigArr();
        $no             = $request->no?$request->no:"";
        $gi             = DB::table("loan_info_guarantor")
                            ->select("loan_info_no,cust_info_no,no,name,ssn,status,live_together,
                            relation_cd,ph11,ph12,ph13,ph21,ph22,ph23,ph31,ph32,ph33,ph34,com_name, g_loan_cat_1_cd, g_loan_cat_2_cd ")
                            ->where('save_status','Y');

        if(isset($request->loan_info_no))
        {
            $loan_info_no = $request->loan_info_no;
            $gi = $gi->WHERE('LOAN_INFO_NO',$loan_info_no)
                    ->get()->toArray();
        }
        else if(isset($request->cust_info_no))
        {
            $cust_info_no = $request->cust_info_no;
            $gi = $gi->WHERE('CUST_INFO_NO',$cust_info_no)
                    ->get()->toArray();
        }
        $gi = Func::chungDec(["LOAN_INFO_GUARANTOR"], $gi);	// CHUNG DATABASE DECRYPT
                            
        return view('erp.loanGuarantor')->with('loan_info_no',$loan_info_no)
        ->with('no',$no)
        ->with('configArr',$configArr)
        ->with('gi',$gi);


    }

    /**
     * 고객정보창 보증인정보
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function loanGuarantorInfo(Request $request)
    {
        // Log::debug($request);
        $configArr      = Func::getConfigArr();
        $no             = $request->no?$request->no:0;
        $gi[0] = "";
        $court_gi_v = Array();
        
        if($no)
        {
            $gi = DB::TABLE("loan_info_guarantor")->SELECT("*")
                                                    ->WHERE('no', $no)
                                                    ->WHERE('save_status', 'Y')
                                                    ->get()
                                                    ->toArray();
            $gi = Func::chungDec(["loan_info_guarantor"], $gi);	// CHUNG DATABASE DECRYPT

            $gi[0]->ssn1            = substr($gi[0]->ssn,0,6)?substr($gi[0]->ssn,0,6):"";
            $gi[0]->ssn2            = substr($gi[0]->ssn,6)?substr($gi[0]->ssn,6):"";
            $gi[0]->mode            = "UPD";

            if( Func::funcCheckPermit("E025") ){
                $gi[0]->auth        = "Y";
            }
        }

        return view('erp.loanGuarantorInfo')
                ->with('v', $gi[0])
                ->with('configArr', $configArr);
    }
    
    /**
     *  보증인정보 ACTION
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function loanGuarantorAction(Request $request)
    {
        $guaArray                = Array();
        $DATA                    = $request->all();
        $DATA['save_status']     = "Y";
        $DATA['save_time']       = date("YmdHis");
        $DATA['save_id']         = Auth::id();
        $DATA['ssn']             = $request->ssn1.$request->ssn2?$request->ssn1.$request->ssn2:"";
        $DATA['live_together']   = $request->live_together?"Y":"N";
        $DATA['status']          = $request->status?$request->status:"Y";
        $DATA['cust_info_no']    = DB::table('loan_info')->select('cust_info_no')->where('no', '=', $request->loan_info_no)->value('cust_info_no');
        
        foreach($DATA as $key => $val)
        {
            if(isset($val) && substr($key,-5,5)=="_date")
            {
                $DATA[$key] = Func::delChar($val,['-']);
            }
        }   

        if($request->mode == "UPD")
        {
            $rslt = DB::dataProcess($request->mode, 'LOAN_INFO_GUARANTOR', $DATA, ['NO'=>$DATA['no']]);
            $array_result['no'] = $DATA['no'];
        }
        else if($request->mode == "INS")
        {
            $g_no = "";
            unset($DATA['no']);
            $rslt = DB::dataProcess($request->mode, 'LOAN_INFO_GUARANTOR', $DATA, null, $g_no);
            $array_result['no'] = $g_no;
        }

        $array_result['loan_info_no'] = $DATA['loan_info_no'];

        if(isset($rslt) && $rslt == "Y")
        {
            $array_result['result_msg'] = "정상처리 되었습니다.";
        }
        else
        {
            $array_result['result_msg'] = "처리에 실패하였습니다.";
        }
        return $array_result;
    }

    /**
     *  보증인정보 삭제 ACTION
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function loanGuarantorRemoveAction(Request $request)
    {
        if( !Func::funcCheckPermit("E025") )
        {
            $array_result['result_msg'] = "보증인 삭제 권한이 없습니다.";
            return $array_result;
        }

        log::Debug("보증인정보 삭제 ACTION");
        if($request->mode == "UPD")
        {
            $DATA                    = $request->all();
            $UPD['save_status']      = "N";
            $UPD['save_time']        = date("YmdHis");
            $UPD['save_id']          = Auth::id();

            $rslt = DB::dataProcess($request->mode, 'loan_info_guarantor', $UPD, ['no'=>$DATA['no']]);
            log::Debug("보증인번호 ".$DATA['no']." 삭제 ".$rslt);
        }

        if(isset($rslt) && $rslt == "Y")
        {
            $array_result['result_msg'] = "정상처리 되었습니다.";
        }
        else
        {
            $array_result['result_msg'] = "처리에 실패하였습니다.";
        }
        return $array_result;
    }

    /**
     * 계약명세 일괄처리
     * mode 마다 분기처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function loanLump(Request $request)
    {
        $param = $request->all();
        $mode = $request->mode;
        $arrMode = Array(
            "badInfo"       => Array("bad_post_date","bad_reg_date"),
            "creditInquiry" => Array("niceCb","niceCredit", "niceCfs", "niceShort"),
            "loanDiv"       => Array("loan_cat_1_cd","loan_cat_2_cd", "loan_cat_3_cd", "loan_cat_4_cd", "loan_cat_5_cd")
        );
        Log::debug("====================== 일괄처리 시작 =====================");
        if(isset($mode))
        {
            $updateVal = array();
            foreach($request->listChk as $no)
            {
                if($mode == 'badInfo')
                {
                    foreach($arrMode[$mode] as $v)
                    {
                        $updateVal[$v] = $request->$v;
                    }
                    $rslt = DB::dataProcess('UPD', 'loan_info', $updateVal, ['no'=>$no]);

                    if($rslt)
                    {
                        $result = 'Y';
                    }
                }

                if($mode == 'creditInquiry')
                {
                    $result = 'Y';
                }

                if($mode == 'loanDiv')
                {
                    foreach($arrMode[$mode] as $v)
                    {
                        if($request->lumpLoanDiv == $v)
                        {
                            $updateVal[$v] = $request->lumpLoanDivValue;
                        }
                    }
                    $rslt = DB::dataProcess('UPD', 'loan_info', $updateVal, ['no'=>$no]);

                    if($rslt)
                    {
                        $result = 'Y';
                    }

                }
            }
        }

        Log::debug("chk:".print_r($request->listChk,true)."\n");
        Log::debug("====================== 일괄처리 끝 =====================");
        if($result == 'Y')
        {
            $r['msg'] = "complete"; 
        }
        
        return json_encode($r);
    }



    /**
     *  징구서류
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function loanDoc(Request $request)
    {
        $configArr      = Func::getConfigArr();
        $array_user     = Func::getUserId();
        $loan_info_no   = $request->loan_info_no;
        $necessary_doc  = Func::getLoanNecessaryDoc($loan_info_no);
        //log::debug(print_r($necessary_doc,true));

        $di = DB::TABLE("LOAN_INFO_DOC")->SELECT("*")->WHERE('LOAN_INFO_NO',$loan_info_no)->WHERE('SAVE_STATUS','Y')->ORDERBY("app_document_cd")->get()->toArray();
        $di = Func::chungDec(["LOAN_INFO_DOC"], $di);	// CHUNG DATABASE DECRYPT

        // 필수서류중 이미 등록되어있는 서류 제외
        foreach($di as $v)
        {
            if( array_search($v->app_document_cd, $necessary_doc)!==false )
            {
                unset($necessary_doc[(integer)array_search($v->app_document_cd,$necessary_doc)]);
            }
        }

        return view('erp.loanDoc')->with('loan_info_no',$loan_info_no)
                                    ->with('array_user',$array_user)
                                    ->with('configArr',$configArr)
                                    ->with('necessary_doc',$necessary_doc)
                                    ->with('di',$di);
    }
    
    /**
     *  징구서류 ACTION
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function loanDocAction(Request $request)
    {
        Log::debug($request);
        $DATA = $request->all();
        $DATA['loan_info_no']      = $request->loan_info_no;
        $DATA['cust_info_no']      = DB::table('loan_info')->select('cust_info_no')->where('no', '=', $request->loan_info_no)->value('cust_info_no');
        $DATA['save_status']       = "Y";
        $DATA['save_time']         = date("YmdHis");
        $DATA['save_id']           = Auth::id();

        /*
        $DATA['scan_chk'] = isset($DATA['scan_chk']) ? "Y" : "N" ;
        $DATA['keep_chk'] = isset($DATA['keep_chk']) ? "Y" : "N" ;
        */
        
        foreach($DATA AS $col => $v)
        {
            if(isset($v))
            {
                $DATA[$col] = str_replace('-','',$v);
            }
            else
            {
                unset($DATA[$col]);
            }
        }

        DB::beginTransaction();

        if($request->mode == "UPD")
        {
            unset($DATA['no']);
            foreach($request->no as $doc_no)
            {
                Log::debug($doc_no);
                $tmp = explode("_",$doc_no);
                $DATA['app_document_cd'] = $tmp[0];
                $no = $tmp[1];
                if($no)
                {
                    $rslt = DB::dataProcess("UPD", 'LOAN_INFO_DOC', $DATA,['NO'=>$no]);
                }
                else
                {
                    $check_v = DB::table('LOAN_INFO_DOC')->where(ARRAY('LOAN_INFO_NO'=>$DATA['loan_info_no'],"APP_DOCUMENT_CD"=>isset($DATA['app_document_cd'])?$DATA['app_document_cd']:"","SAVE_STATUS"=>"Y"))->exists();
                    if(empty($check_v))
                    {
                        $DATA['necessary_chk'] = 'Y';
                        $rslt = DB::dataProcess("INS", 'LOAN_INFO_DOC', $DATA);
                    }
                }
            }
        }
        else if($request->mode == "INS")
        {
            //중복서류검사
            $check_v = DB::table('LOAN_INFO_DOC')->where(ARRAY('LOAN_INFO_NO'=>$DATA['loan_info_no'],"APP_DOCUMENT_CD"=>$DATA['app_document_cd'],"SAVE_STATUS"=>"Y"))->exists();
            if(empty($check_v))
            {
                $DATA['necessary_chk'] = isset($DATA['necessary_chk'])?"Y":"N";
                $rslt = DB::dataProcess($request->mode, 'LOAN_INFO_DOC', $DATA);
            }
        }
        else if($request->mode == "DEL")
        {
            foreach($request->no as $doc_no)
            {
                $tmp = explode("_",$doc_no);
                $nos[] = $tmp[1];
            }

            $rslt = DB::table('LOAN_INFO_DOC')->whereIn('NO',isset($nos)?$nos:Array())->update(Array("SAVE_STATUS"=>"N","DEL_TIME"=>date("YmdHis"),"DEL_ID"=>Auth::id()));
            if($rslt>0)
            {
                $rslt = "Y";
            }
        }
        


        $loan_info_no = $DATA['loan_info_no'];

        // 조건) 필수서류가 모두 있어야 하고, 도착일이 있어야 함
        $necessary_doc = Func::getLoanNecessaryDoc($loan_info_no);

        $vcnt = DB::table('LOAN_INFO_DOC')->SELECT(DB::raw('count(distinct app_document_cd) as cnt'))
                                          ->WHERE('LOAN_INFO_NO',$loan_info_no)
                                          ->WHERE('SAVE_STATUS','Y')
                                          ->WHEREIN('APP_DOCUMENT_CD',$necessary_doc)
                                          ->WHERERAW("( KEEP_CHK='Y' OR SCAN_CHK='Y' )")->FIRST();
        log::debug("necessary_doc = ".$vcnt->cnt." / ".sizeof($necessary_doc));

        // 필수서류와 징구한 서류갯수가 같다면 완료
        if( sizeof($necessary_doc)==$vcnt->cnt )
        {
            $rslt2 = DB::dataProcess("UPD","LOAN_INFO",["DOC_STATUS_CD"=>"Y"],["NO"=>$loan_info_no]);   //징구완료
        }
        // 징구한 서류가 있으면 일부징구
        else if( $vcnt->cnt>0 )
        {
            $rslt2 = DB::dataProcess("UPD","LOAN_INFO",["DOC_STATUS_CD"=>"A"],["NO"=>$loan_info_no]);   //일부징구
        }
        // 하나도 안오면 미징구
        else
        {
            $rslt2 = DB::dataProcess("UPD","LOAN_INFO",["DOC_STATUS_CD"=>"N"],["NO"=>$loan_info_no]);   //미징구
        }




        $array_result['loan_info_no']   = $DATA['loan_info_no'];

        if( isset($rslt) && $rslt=="Y" && $rslt2=="Y" )
        {
            $array_result['result_msg'] = "정상처리 되었습니다.";
            DB::commit();
        }
        else
        {
            $array_result['result_msg'] = !empty($check_v) ? "처리실패. \n중복된 서류종류를 확인해주세요." : "처리실패. 데이터를 확인해주세요.";
            DB::rollback();
        }

        return $array_result;
    }

    /**
     *  가상계좌 정보
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function loanVirAccount(Request $request)
    {
        $array_user     = Func::getUserId();
        $loan_info_no   = $request->loan_info_no;
        $cust_info_no   = $request->cust_info_no;
        // $loan_info_no = 13502;
        // $ACCT = DB::SELECT(
        //         DB::RAW("SELECT 
        //                         CUST.*,V.BANK_CD,V.VIR_ACCT_SSN,V.NO AS VNO
        //                 FROM 
        //                     (SELECT 
        //                         G.CUST_INFO_NO,G.LOAN_INFO_NO,G.NAME ,G.NO AS GNO 
        //                     FROM 
        //                         LOAN_INFO_GUARANTOR G where SAVE_STATUS = 'Y' AND STATUS = 'Y'
        //                     UNION
        //                     SELECT 
        //                         L.CUST_INFO_NO,L.NO AS LOAN_INFO_NO,(SELECT NAME FROM CUST_INFO WHERE NO = CUST_INFO_NO ) AS NAME,0 AS GNO 
        //                     FROM LOAN_INFO L  where SAVE_STATUS = 'Y') CUST  
        //                     LEFT JOIN VIR_ACCT V 
        //                     ON 
        //                         V.LOAN_INFO_NO = CUST.LOAN_INFO_NO AND 
        //                         ( (V.LOAN_INFO_GUARANTOR_NO IS NULL AND CUST.GNO = 0) OR 
        //                         (CUST.GNO = V.LOAN_INFO_GUARANTOR_NO AND V.LOAN_INFO_GUARANTOR_NO IS NOT NULL )) 
        //                 WHERE CUST.LOAN_INFO_NO =".$loan_info_no." AND (V.SAVE_STATUS = 'Y' OR V.SAVE_STATUS IS NULL) ORDER BY CUST.GNO "));

        // 고객별로 뽑기
        $ACCT = DB::SELECT(" SELECT CUST.*,V.BANK_CD,V.VIR_ACCT_SSN,V.NO AS VNO
                        FROM (
                            SELECT  G.CUST_INFO_NO,G.LOAN_INFO_NO,G.NAME ,G.NO AS GNO FROM  LOAN_INFO_GUARANTOR G where SAVE_STATUS = 'Y' AND STATUS = 'Y' AND CUST_INFO_NO = ".$cust_info_no."
                            UNION 
                            SELECT C.CUST_INFO_NO ,0 AS LOAN_INFO_NO,(SELECT NAME FROM CUST_INFO WHERE NO = CUST_INFO_NO ) AS NAME ,0 AS GNO FROM LOAN_INFO C  where SAVE_STATUS = 'Y'  AND 
                            CUST_INFO_NO = ".$cust_info_no." 
                            ) CUST 
                        LEFT JOIN VIR_ACCT V 
                                    ON 
                        V.CUST_INFO_NO = CUST.CUST_INFO_NO AND 
                        COALESCE(CUST.GNO,0) = COALESCE(V.LOAN_INFO_GUARANTOR_NO,0) AND SAVE_STATUS ='Y'
                                WHERE CUST.CUST_INFO_NO=".$cust_info_no." ORDER BY CUST.GNO ");

        return view('erp.loanVirAccount')->with('ACCT',Func::chungDec(["VIR_ACCT", "LOAN_INFO_GUARANTOR", "LOAN_INFO", "CUST_INFO"], $ACCT))
                                        ->with('loan_info_no',$loan_info_no)
                                        ->with('array_bank_cd',Func::getConfigArr('bank_cd'))
                                        ->with('array_user',$array_user);
    }

     /**
     *  가상계좌 action
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Array
     */
	public function loanVirAccountAction(Request $request)
    {
        log::info($request);
        DB::beginTransaction();

        // 발급
        if($request->mode == "Y")
        {   
            // 보증인일 경우는 계약번호 세팅해주자
            if(!empty($request->gno))
            {
                $DATA['loan_info_guarantor_no']  = $request->gno;
                $DATA['loan_info_no']  = $request->v_loan_info_no;
            }
            $DATA['cust_info_no']  = $request->cust_info_no;
            $DATA['reg_date']      = date("YmdHis");

            // CUST_INFO_NO, LOAN_INFO_NO 없는것도 확인하자
            // $VIR_NO = DB::TABLE("VIR_ACCT")->WHERERAW("COALESCE(REG_DATE,'')='' AND COALESCE(LOAN_INFO_NO,0)=0 AND COALESCE(CUST_INFO_NO,0)=0  AND COALESCE(LOAN_APP_NO,0)=0")->WHERE("SAVE_STATUS","Y")->MIN("NO");
            $VIR_NO = DB::TABLE("VIR_ACCT")->WHERERAW("COALESCE(CUST_INFO_NO,0)=0 ")->WHERE("SAVE_STATUS","Y")->MIN("NO");
            log::info($VIR_NO);
            if(empty($VIR_NO))
            {
                $array_result['msg'] = "현재 발급가능한 가상계좌가 없습니다.";
            }
            else
            {
                $VIR = DB::TABLE('VIR_ACCT')->SELECT('MO_SSN, BANK_CD, VIR_ACCT_SSN')->WHERE('NO',$VIR_NO)->FIRST();
                $VIR = Func::chungDec(["VIR_ACCT"], $VIR);	// CHUNG DATABASE DECRYPT

                // 차주,보증인에 맞게 예금주명 세팅
                if(!empty($request->gno))
                {
                    $guarantor = DB::TABLE("LOAN_INFO_GUARANTOR")->select("NAME")->WHERE("SAVE_STATUS","Y")->WHERE("NO",$request->gno)->ORDERBY("NO")->FIRST();
                    $guarantor = Func::chungDec(["LOAN_INFO_GUARANTOR"], $guarantor);	// CHUNG DATABASE DECRYPT
                    $name = $guarantor->name;
                }
                else
                {
                    $cust = DB::TABLE("CUST_INFO")->select("NAME")->WHERE("SAVE_STATUS","Y")->WHERE("NO",$request->cust_info_no)->ORDERBY("NO")->FIRST();
                    $cust = Func::chungDec(["CUST_INFO"], $cust);	// CHUNG DATABASE DECRYPT
                    $name = $cust->name;
                }
            }
        }
        // 해지
        else
        {
            //  원장변경내역 등록
            $VIR = DB::TABLE("VIR_ACCT")->SELECT("BANK_CD, VIR_ACCT_SSN")->WHERE("NO", $request->vno)->FIRST();
            $VIR = Func::chungDec(["VIR_ACCT"], $VIR);	// CHUNG DATABASE DECRYPT
            $_wch = [
                "cust_info_no"  =>  $request->cust_info_no,
                "loan_info_no"  =>  $request->loan_info_no,
                "worker_id"     =>  Auth::id(),
                "work_time"     =>  date("Ymd"),
                "worker_code"   =>  Auth::user()->branch_code,
                "loan_status"   =>  "",
                "manager_code"  =>  "",
                "div_nm"        =>  "가상계좌번호변경(해지)",
                "before_data"   =>  $VIR->bank_cd.",".$VIR->vir_acct_ssn,
                "after_data"    =>  "",
                "trade_type"    =>  "",
                "sms_yn"        =>  "N",
                "memo"          =>  "",
            ];

            Log::debug("###### 해지 vno  : ".$VIR->vir_acct_ssn);
            // 2021.12.29 yjlee
            // 해당 가상계좌번호의 유효채권 존재 여부 체크 or 해당 계약번호의 유효 여부 체크
            $check_status = DB::TABLE('LOAN_INFO')->SELECT('COUNT(1) AS CNT')->WHERE("VIR_ACCT_SSN",$VIR->vir_acct_ssn)->WHERE("STATUS",'<>','E')->FIRST();
            Log::info($check_status->cnt);

            if(isset($check_status->cnt) && $check_status->cnt>= 1 ){ //(완제가 아닌건이 한건이라도 존재한다면 )
                Log::debug("###### 해당 가상계좌번호의 완제가 아닌건 존재 o");
                $rslt = "R";
            }else{
                Log::debug("###### 해당 가상계좌번호의 완제가 아닌건 존재 X");
            }

        }

        $array_result['loan_info_no'] = $request->loan_info_no;
        $array_result['cust_info_no'] = $request->cust_info_no;
        if(isset($rslt) && $rslt == "Y")
        {
            DB::commit();
            $array_result['msg'] = "정상처리 되었습니다.";

        }else if(isset($rslt) && $rslt == "R")
        {
            DB::rollback();
            $array_result['msg'] = Func::nvl($array_result['msg'],"해당 가상계좌번호로 완제가 아닌 건이 존재합니다. 확인 바랍니다.");

        }else
        {
            DB::rollback();
            $array_result['msg'] = Func::nvl($array_result['msg'],"처리에 실패하였습니다.");
            
        }

        return $array_result;
    }
    
    /**
     * 추가투자 / 재투자 검색
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function loanTmList(Request $request)
    {
        $request->isDebug = true;
        
        $list   = $this->setDataLoanList($request);
        $param  = $request->all();

         // Tab count 
		if($request->isFirst=='1')
		{
            $BOXC = DB::TABLE("LOAN_INFO")->JOIN("CUST_INFO", "LOAN_INFO.CUST_INFO_NO", "=", "CUST_INFO.NO");
            $BOXC = $BOXC->JOIN("CUST_INFO_EXTRA", "CUST_INFO.NO", "=", "CUST_INFO_EXTRA.CUST_INFO_NO");
            $BOXC = $BOXC->SELECT(DB::RAW("SUM(CASE WHEN LOAN_INFO.MANAGER_ID='".Auth::id()."' THEN 1 ELSE 0 END) AS MY, 
                    SUM(CASE WHEN LOAN_INFO.MANAGER_CODE='".Auth::user()->branch_code."' THEN 1 ELSE 0 END) AS BR, 
                    --COUNT(LOAN_INFO.NO) AS ALL,
                    --SUM(CASE WHEN LOAN_INFO.STATUS IN ('A','B','C','D') THEN 1 ELSE 0 END) AS YU,
                    --SUM(CASE WHEN LOAN_INFO.STATUS IN ('A','B','C','D') AND (LOAN_INFO.LACK_INTEREST + LOAN_INFO.LACK_DELAY_MONEY + LOAN_INFO.LACK_DELAY_INTEREST) > 10000 THEN 1 ELSE 0 END) AS LK,
                    SUM(CASE WHEN LOAN_INFO.STATUS IN ('B','D') THEN 1 ELSE 0 END) AS B
                    --SUM(CASE WHEN LOAN_INFO.STATUS IN ('E') THEN 1 ELSE 0 END) AS E,
                    --SUM(CASE WHEN LOAN_INFO.STATUS IN ('M') THEN 1 ELSE 0 END) AS M,
                    --SUM(CASE WHEN LOAN_INFO.STATUS IN ('S') THEN 1 ELSE 0 END) AS S
                    "));
            $BOXC = $BOXC->WHERE('LOAN_INFO.SAVE_STATUS','Y');
            $BOXC = $BOXC->WHERE('CUST_INFO.SAVE_STATUS','Y');
            $BOXC = $BOXC->WHERERAW("LOAN_INFO.STATUS IN ('A','B','C','D','S') AND ( LOAN_INFO.MANAGER_CODE= ? OR LOAN_INFO.MANAGER_ID= ? )", [ Auth::user()->branch_code, Auth::id() ]);
            $count = $BOXC->FIRST();
			$r['tabCount'] = array_change_key_case((Array)$count, CASE_UPPER);
		}

        // 기본쿼리
        $LOAN = DB::TABLE("LOAN_INFO");
        $LOAN = $LOAN->JOIN("CUST_INFO", "LOAN_INFO.CUST_INFO_NO", "=", "CUST_INFO.NO");
        $LOAN = $LOAN->JOIN("CUST_INFO_EXTRA", "CUST_INFO.NO", "=", "CUST_INFO_EXTRA.CUST_INFO_NO");
        $LOAN->SELECT("LOAN_INFO.NO", "LOAN_INFO.CUST_INFO_NO", "LOAN_DATE", "LOAN_INFO.PRO_CD", "RETURN_METHOD_CD", "LOAN_RATE", "LOAN_DELAY_RATE");
        $LOAN->ADDSELECT("LOAN_INFO.STATUS", "LOAN_INFO.RETURN_DATE", "KIHAN_DATE", "INTEREST_SUM", "LOAN_INFO.BALANCE", "FULLPAY_MONEY", "CHARGE_MONEY", "MANAGER_ID", "settle_div_cd");
        $LOAN->ADDSELECT("CUST_INFO.NAME", "CUST_INFO.SSN", "CUST_INFO_EXTRA.PH21", "CUST_INFO_EXTRA.PH22", "CUST_INFO_EXTRA.PH23");
        $LOAN->WHERE('CUST_INFO.SAVE_STATUS','Y');
        $LOAN->WHERE('LOAN_INFO.SAVE_STATUS','Y');

        // 조건쿼리
        $SUB = DB::TABLE("LOAN_INFO AS L")->JOIN("CUST_INFO AS C", "L.CUST_INFO_NO", "=", "C.NO");
        $SUB->SELECT("MAX(L.NO)");
        $SUB->WHERE('C.SAVE_STATUS','Y');
        $SUB->WHERE('L.SAVE_STATUS','Y');
        $SUB->WHEREIN('L.STATUS',['A','E']);
        $SUB->GROUPBY('L.CUST_INFO_NO','L.NO');

        $LOAN = $LOAN->WHEREIN("LOAN_INFO.NO",$SUB);

        if( $request->tm_div == "ADD" ) // TM추가투자
		{
            $LOAN = $LOAN->WHERE('LOAN_INFO.LIMIT_MONEY - LOAN_INFO.BALANCE','>', '0');
        } 
        if( $request->tm_div == "RE" ) // TM재투자
		{
            $LOAN = $LOAN->WHERE('LOAN_INFO.STATUS','=', 'E');
            //$TM = $TM->WHERE('LOAN_INFO.LIMIT_MONEY - LOAN_INFO.BALANCE','=', '0');
        } 

        // 'MY'=>'업무', 'BR'=>'지점', 'ALL'=>'전체', 'YU'=>'유효', 'LK'=>'부족금', 'BD'=>'연체', 'S'=>'상각', 'M'=>'매각', 'E'=>'완제'
		if( $request->tabsSelect=="MY" )
		{
            $param['tabSelectNm'] = "LOAN_INFO.MANAGER_ID";
            $param['tabsSelect']  = Auth::id();
        }
		else if( $request->tabsSelect=="BR" )
		{
            $param['tabSelectNm'] = "LOAN_INFO.MANAGER_CODE";
            $param['tabsSelect']  = Auth::user()->branch_code;
        }
		else if( $request->tabsSelect=="ALL" )
		{
            $param['tabsSelect']  = "ALL";
        }
		else if( $request->tabsSelect=="YU" )
		{
            $param['tabSelectNm'] = "LOAN_INFO.STATUS";
            $param['tabsSelect']  = Array('A','B','C','D');
        }
		else if( $request->tabsSelect=="LK" )
		{
            $LOAN->WHERE('( LOAN_INFO.LACK_INTEREST + LOAN_INFO.LACK_DELAY_MONEY + LOAN_INFO.LACK_DELAY_INTEREST )', '>', 10000);

            $param['tabSelectNm'] = "LOAN_INFO.STATUS";
            $param['tabsSelect']  = Array('A','B','C','D');
        }
		else if( $request->tabsSelect=="BD" )
		{
            $param['tabSelectNm'] = "LOAN_INFO.STATUS";
            $param['tabsSelect']  = Array('B','D');
        }

        $LOAN = $list->getListQuery('LOAN_INFO', 'main', $LOAN, $param);

        $r['totalCnt'] = $LOAN->count();

        // target_sql 저장
        $sql            = Func::printQuery($LOAN);
        $r['targetSql'] = base64_encode($sql);
                
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN, $request->page, $request->listLimit, 10, $request->listName);
        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","CUST_INFO_EXTRA"], $rslt);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $getProCode           = Func::getConfigArr('pro_cd');
        $getReturnMethodCd    = Func::getConfigArr('return_method_cd');
        $arrManager           = Func::getUserList();


        $cnt = 0;
        foreach ($rslt as $v)
        {
            $v->onclick          = 'javascript:window.open("/erp/custpop?cust_info_no='.$v->cust_info_no.'&no='.$v->no.'","msgpop","width=2000, height=1000, scrollbars=yes")';
            $v->line_style       = 'cursor: pointer;';

            $v->loan_info_no     = $v->no;
            $v->ssn              = substr($v->ssn, 0, 6);
            $v->ph2              = Func::phFormat($v->ph21,$v->ph22,$v->ph23) ;
            $v->loan_date        = Func::dateFormat($v->loan_date);
            $v->pro_cd           = Func::getArrayName($getProCode, $v->pro_cd);
            $v->return_method_cd = Func::getArrayName($getReturnMethodCd, $v->return_method_cd);
            $v->loan_rate        = sprintf('%0.2f',$v->loan_rate)."%";
            $v->status           = Func::getInvStatus($v->status, true);
            $v->return_date      = Func::dateFormat($v->return_date);
            $v->kihan_date       = Func::dateFormat($v->kihan_date);
            $v->interest_sum     = number_format($v->interest_sum);
            $v->balance          = number_format($v->balance);
            $v->fullpay_money    = number_format($v->fullpay_money);
            $v->charge_money     = number_format($v->charge_money);
            $v->manager_name     = isset($arrManager[$v->manager_id]) ? Func::nvl($arrManager[$v->manager_id]->name, $v->manager_id) : $v->manager_id ;

            $r['v'][] = $v;
            $cnt ++;

        }

        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path());
        $r['result']   = 1;
        $r['txt']      = $cnt;

        return json_encode($r);
    }

    public function loanCcrsAccount(Request $request)
    {
        $_DATA = $request->all();

        $linfo = DB::TABLE("loan_info")->SELECT('no','cust_info_no', 'ccrs_account')
                                        ->WHERE("save_status", 'Y')
                                        ->WHERE("no", $request->loan_info_no)
                                        ->WHERE("cust_info_no", $request->cust_info_no)
                                        ->first();

        $linfo = Func::chungDec(["loan_info"], $linfo);	// CHUNG DATABASE DECRYPT

        return view("erp.loanCcrsAccount")->with("v", $linfo);
    }

    public function loanCcrsAccountAction(Request $request)
    {
        $_UPD = Array();
        $_DATA = $request->all();

        if(!empty($request->cust_info_no) && !empty($request->loan_info_no))
        {
            $_UPD['ccrs_account'] = $request->ccrs_account;

            $rslt = DB::dataProcess("UPD", 'LOAN_INFO', $_UPD, ['no' => $request->loan_info_no, 'cust_info_no' => $request->cust_info_no]);
        }

        if(isset($rslt) && $rslt == "Y")
        {
            $array_result['result_msg'] = "정상처리 되었습니다.";
        }
        else
        {
            $array_result['result_msg'] = "처리에 실패하였습니다.";
        }

        return $array_result;
    }

    /**
     * 만기일 계산
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function getAddMonth(Request $request)
    {
        if(empty($request->today) || empty($request->term))
        {
            return '';
        }

        $endDate = Loan::addMonth(str_replace('-', '', $request->today), $request->term);

        return Func::dateFormat($endDate);
    }

    /**
     * 사모사채계약리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataLoanPrivatelyList(Request $request)
    {
        $list   = new DataList(Array("listName"=>"loanprivately","listAction"=>'/'.$request->path()));

        if( Func::funcCheckPermit("R022") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/erp/loanprivatelyexcel', 'form_loanprivately')", "btn-success");
        }
        $list->setCheckBox("no");
        $list->setSearchDate('날짜검색',Array('app_date' => '신청일', 'loan_date' => '투자일', 'contract_end_date' => '만기일', 'return_date' => '수익지급일'),'searchDt','Y');
        $list->setRangeSearchDetail(Array ('app_money' => '신청금액', 'loan_money' => '투자금액', 'balance'=>'잔액'),'','','단위(원)');
        if( Func::funcCheckPermit("E004") || Func::funcCheckPermit("E031") )
        {
            $list->setSearchType('manager_code', Func::myPermitBranch(), '관리지점');
        }
        
        $list->setSearchType('return_method_cd',Func::getConfigArr('return_method_cd'),'수익지급방법');
        $list->setSearchType('status',Vars::$arrayContractSta,'상태');
        $list->setSearchDetail(Array(
            'LOAN_INFO.NO'  => '계약번호',
            'LOAN_INFO.CUST_INFO_NO'  => '차입자번호',
            'NAME'  => '이름',
            'SSN'   => '주민번호',
            'PH23'  => '휴대폰뒤',
        ));

        return $list;
    }

    /**
     * 사모사채계약 리스트 메인화면
     *
     * @param  Void
     * @return view
     */
	public function loanPrivately(Request $request)
    {
        $list   = $this->setDataLoanPrivatelyList($request);
        $list->setlistTitleCommon(Array
        (
            'cust_info_no'          => Array('차입자번호', 0, '', 'center', '', 'cust_info_no'),
            'loan_info_no'          => Array('계약번호', 0, '', 'center', '', 'loan_info.no'),
            'name'                  => Array('이름', 0, '', 'center', '', 'name'),
            'ssn'                   => Array('생년월일', 0, '', 'center', '', 'ssn'),
            'return_method_cd'      => Array('수익지급방식', 0, '', 'center', '', 'return_method_cd'),
            'loan_date'             => Array('계약일', 0, '', 'center', '', 'loan_datreturn_method_cde'),
            'loan_money'            => Array('투자금액', 0, '', 'center', '', 'loan_money'),
            'loan_rate'             => Array('금리', 0, '', 'center', '', 'loan_rate'),
            'contract_day'          => Array('약정일', 0, '', 'center', '', 'contract_day'),
            'status'                => Array('상태', 0, '', 'center', '', 'status'),
            'delay_term'            => Array('연체일', 0, '', 'center', '', 'delay_term'),
            'return_date'           => Array('수익지급일', 0, '', 'center', '', 'return_date'),
            'kihan_date'            => Array('상실일', 0, '', 'center', '', 'kihan_date'),
            'interest_sum'          => Array('이자합계', 0, '', 'right', '', 'interest_sum'),
            'balance'               => Array('잔액', 0, '', 'right', '', 'balance'),
        ));

        return view('erp.loanPrivately')->with('result', $list->getList());
    }

    /**
     * 사모사채계약 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function loanPrivatelyList(Request $request)
    {
        $list   = $this->setDataLoanPrivatelyList($request);
        $param  = $request->all();

        // 기본쿼리
        $LOAN = DB::TABLE("loan_info")->JOIN("cust_info", "loan_info.cust_info_no", "=", "cust_info.no")->JOIN("cust_info_extra", "cust_info.no", "=", "cust_info_extra.cust_info_no");
        $LOAN->SELECT("loan_info.*", "cust_info.name", "cust_info.ssn", "cust_info_extra.ph21", "cust_info_extra.ph22", "cust_info_extra.ph23");
        $LOAN->WHERE('cust_info.save_status','Y');
        $LOAN->WHERE('loan_info.save_status','Y');
        $LOAN->WHERE('loan_info.pro_cd','02');

        // 전지점 조회권한 없으면 자기 지점만
        if( !Func::funcCheckPermit("E004") )
        {
            $LOAN->WHEREIN('loan_info.manager_code', array_keys(Func::myPermitBranch()));
        }

        $LOAN = $list->getListQuery("LOAN_INFO",'main',$LOAN,$param);
                
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN, $request->page, $request->listLimit, 10, $request->listName);

        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","CUST_INFO_EXTRA"], $rslt);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $getProCode = Func::getConfigArr('pro_cd');
        $getReturnMethodCd = Func::getConfigArr('return_method_cd');
        $arrBranch         = Func::getBranch();

        $cnt = 0;
        foreach ($rslt as $v)
        {
            $v->onclick          = 'javascript:loan_info_pop('.$v->cust_info_no.', '.$v->no.');';
            $v->line_style       = 'cursor: pointer;';

            $v->loan_info_no     = $v->no;
            $v->ssn              = Func::ssnFormat($v->ssn, 'Y');
            $v->loan_date        = Func::dateFormat($v->loan_date);
            $v->name             = Func::nameMasking($v->name, 'Y');
            $v->ph2              = Func::phMasking($v->ph21,$v->ph22,$v->ph23,'Y');
            $v->loan_rate        = sprintf('%0.2f',$v->loan_rate)."%";
            $v->return_method_cd = Func::getArrayName($getReturnMethodCd, $v->return_method_cd);
            $v->status           = Func::getInvStatus($v->status, true);
            $v->return_date      = Func::dateFormat($v->return_date);
            $v->kihan_date       = Func::dateFormat($v->kihan_date);
            $v->interest_sum     = number_format($v->interest_sum);
            $v->loan_money       = number_format($v->loan_money);
            $v->balance          = number_format($v->balance);
            $v->fullpay_money    = number_format($v->fullpay_money);
            $v->charge_money     = number_format($v->charge_money);

            $v->manager_name     = Func::nvl($arrBranch[$v->manager_code], $v->manager_code);

            $r['v'][] = $v;
            $cnt ++;

        }
        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path());

        $r['result'] = 1;
        $r['txt'] = $cnt;
        
        return json_encode($r);
    }

    public function loanPrivatelyExcel(Request $request)
    {
        if( !Func::funcCheckPermit("R022") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }
        
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $list           = $this->setDataLoanPrivatelyList($request);
        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        // 기본쿼리
        $LOAN = DB::TABLE("loan_info")->JOIN("cust_info", "loan_info.cust_info_no", "=", "cust_info.no")->JOIN("cust_info_extra", "cust_info.no", "=", "cust_info_extra.cust_info_no");
        $LOAN->SELECT("loan_info.*", "cust_info.name", "cust_info.relation", "cust_info.ssn", "cust_info_extra.ph21", "cust_info_extra.ph22", "cust_info_extra.ph23");
        $LOAN->WHERE('cust_info.save_status','Y');
        $LOAN->WHERE('loan_info.save_status','Y');
        $LOAN->WHERE('loan_info.pro_cd','02');


        // 전지점 조회권한 없으면 자기 지점만
        if( !Func::funcCheckPermit("E004") && !isset($request->excel_flag) )
        {
            $LOAN->WHEREIN('loan_info.manager_code', array_keys(Func::myPermitBranch()));
        }
                       
        $LOAN = $list->getListQuery("LOAN_INFO",'main',$LOAN,$param);

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN, $request->nowPage, $request->listLimit, 10, $request->listName);
        }
        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($LOAN);
        // log::info($query);
        $file_name    = "사모사채리스트_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no)){
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
        } else {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $query, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
        }

        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","CUST_INFO_EXTRA"], $rslt);	// CHUNG DATABASE DECRYPT

        // 엑셀
		$excel_header = array('차입자번호','계약번호','이름','생년월일','수익지급방식','계약일','투자금액','금리','약정일','상태','연체일','수익지급일','상실일','이자합계','잔액',);

        $excel_data = array();
    
        // 뷰단 데이터 정리.
        $getProCode = Func::getConfigArr('pro_cd');
        $getReturnMethodCd = Func::getConfigArr('return_method_cd');
        $arrBranch  = Func::getBranch();

        foreach ($rslt as $v)
        {
            $array_data = [
                Func::addCi($v->cust_info_no),
                $v->no,
                $v->name,
                substr($v->ssn, 0, 6),
                Func::getArrayName($getReturnMethodCd, $v->return_method_cd),
                Func::dateFormat($v->loan_date),
                (int)($v->loan_money),
                sprintf('%0.2f',$v->loan_rate)."%",
                $v->contract_day,
                Func::getInvStatus($v->status),
                $v->delay_term,
                Func::dateFormat($v->return_date),
                Func::dateFormat($v->kihan_date),
                (int)($v->interest_sum),
                (int)($v->balance),
            ];
            $record_count++;
            $excel_data[] = $array_data;
        }
        
        // 엑셀 익스포트
        ExcelFunc::fastexcelExport($excel_data,$excel_header,$file_name);
     
        // 파일 저장 여부 확인
        $exists = Storage::disk('excel')->exists($file_name);

        if( isset($exists) )
        {
            $array_result['etc']             = $etc;
            $array_result['result']          = 'Y';
            $array_result['filename']        = $file_name;
            $array_result['excel_no']        = $excel_no;
            $array_result['record_count']    = $record_count;
            $array_result['down_filename']   = $down_filename;
            $array_result['excel_down_div']  = $excel_down_div;
            
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
        }
        else
        {
           $array_result['result']    = 'N';
           $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }
        return $array_result;
    }

    /**
     * 상품계약관리 삭제 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function loanMngLumpDelete(Request $request)
    {
        $val = $request->input();
        $s_cnt = 0;
        $arr_fail = Array();

        $del_id   = Auth::id();
        $del_time = date("YmdHis");

        if( $val['action_mode']=="loan_DELETE" && is_array($val['listChk']) && sizeof($val['listChk'])>0 )
        {
            for( $i=0; $i<sizeof($val['listChk']); $i++ )
            {
                $loan_info_no = $val['listChk'][$i];

                DB::beginTransaction();

                $account_transfer = DB::table('account_transfer')->join("loan_info","loan_info.no","=","account_transfer.loan_info_no")
                                                    ->join("cust_info","cust_info.no","=","loan_info.cust_info_no")
                                                    ->join("loan_usr_info", 'loan_info.loan_usr_info_no', '=', 'loan_usr_info.no')
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

                    $arr_fail[$loan_info_no] = "이체실행결재에서 현재 요청중인 송금이 있습니다.";
                    continue;
                }

                $tradeLoanInfo = DB::table("loan_info_trade")->where("TRADE_DIV", 'I')->where("loan_info_no", $loan_info_no)->where("save_status", 'Y')->first();
                $tradeLoanInfo = Func::chungDec(["loan_info_trade"], $tradeLoanInfo);	// CHUNG DATABASE DECRYPT
                if(!empty($tradeLoanInfo->no))
                {
                    DB::rollback();

                    $arr_fail[$loan_info_no] = "수입지급내역이 있는 계약이 있습니다.";
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
                    
                    $rslt = DB::dataProcess('UPD', 'loan_info_return_plan', $_END, ["loan_info_no"=>$loan_info_no]);
                    // 오류 업데이트 후 쪽지 발송
                    if( $rslt!="Y" )
                    {
                        DB::rollBack();
            
                        $arr_fail[$loan_info_no] = "신규 투자자 스케줄 업데이트를 실패했습니다.";
                        continue;
                    }

                    $rslt = DB::dataProcess("UPD", "loan_info_rate", $_END, ["loan_info_no"=>$loan_info_no]);
                    // 오류 업데이트 후 쪽지 발송
                    if( $rslt!="Y" )
                    {
                        DB::rollBack();
            
                        $arr_fail[$loan_info_no] = "계약이율 업데이트를 실패했습니다.";
                        continue;
                    }
    
                    $rslt = DB::dataProcess("UPD", "loan_info_cday", $_END, ["loan_info_no"=>$loan_info_no]);
                    // 오류 업데이트 후 쪽지 발송
                    if( $rslt!="Y" )
                    {
                        DB::rollBack();
            
                        $arr_fail[$loan_info_no] = "계약약정일 업데이트를 실패했습니다.";
                        continue;
                    }

                    $rslt = DB::dataProcess("UPD", "loan_info_invest_rate", $_END, ["loan_info_no"=>$loan_info_no]);
                    // 오류 업데이트 후 쪽지 발송
                    if( $rslt!="Y" )
                    {
                        DB::rollBack();
            
                        $arr_fail[$loan_info_no] = "투자이율 업데이트를 실패했습니다.";
                        continue;
                    }
                }

                $rslt = DB::dataProcess("UPD", "loan_info", $_END, ["no"=>$loan_info_no]);
                // 오류 업데이트 후 쪽지 발송
                if( $rslt!="Y" )
                {
                    DB::rollBack();

                    $arr_fail[$loan_info_no] = "계약삭제시 에러가 발생했습니다.";
                    continue;
                }

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

            foreach($arr_fail as $l_no => $msg)
            {
                $error_msg .= "[".$l_no."] => ".$msg."\n";
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