<?php
namespace App\Chung;

use DB;
use Vars;
use Log;
use Auth;
use Func;
use Loan;
use Carbon\Carbon;

class PaperPrint
{

   /**
	* 문자파싱열 가져오기
	* @param $no = 차입자번호
	* @param $msg = 변환할 메세지
    * @param $ups_erp = 대출 / 계약 구분
	* @param $arrayKeys = key : 데이터 가져올 테이블, val : 테이블번호
	* @return $msg = 변환된 메세지
    * 
    * Sms::msgParser($v->no, $msg, "ERP", null, $sms_erp_div);
	*/
	public static function msgParser($no, $loan_info_law_no='', $msg, $ups_erp, $arrayKeys=null, $sms_div=null, $input_params=Array(), $post_cd)
	{	
		if(!empty($arrayKeys))
		{
			$exp = explode('-',$arrayKeys);
			$trade_no 	= $exp[0];	//거래원장 시퀀스
			$table_type = $exp[1]; 	//구거래원장,  거래원장 테이블 구분
			
			//이전거래 번호 가져오기
			$trade_prv = DB::table('loan_info_trade')->select('no')->where('loan_info_no', '=', $no)->WHERE('no', '<',$trade_no)->WHERE('trade_div', '=','I') ->orderBy('SEQ', 'desc')->FIRST(); 
			$trade_prv = Func::chungDec("loan_info_trade", $trade_prv);

			$no_prv =  isset($trade_prv) ? $trade_prv->no : "NN";

			// 영수증 겸 잔금 확인서
			$arrayParser['[연체일수]'] = 'LOAN_INFO__delay_term';
			$arrayParser['[사용금액]'] = 'LOAN_INFO__use_money'; 
			$arrayParser['[보관금]'] = 'LOAN_INFO_LAW_COST__lawdeposit_am'; 

			if($table_type == 'new')
			{
				//거래원장
				$arrayParser['[이번회수일]'] = 'LOAN_INFO_TRADE__trade_date'; 
				$arrayParser['[회수내용]'] = 'LOAN_INFO_TRADE__trade_type'; 
				$arrayParser['[회수금액]'] = 'LOAN_INFO_TRADE__trade_money'; 
				$arrayParser['[수령이자]'] = 'LOAN_INFO_TRADE__return_interest_sum'; 
				$arrayParser['[원금충당액]'] = 'LOAN_INFO_TRADE__return_origin';
				$arrayParser['[다음지불일]'] = 'LOAN_INFO_TRADE__return_date';	

				$arrayParser['[원장이율]'] = 'LOAN_INFO__interest'; 
				$arrayParser['[원장연체이율]'] = 'LOAN_INFO__delay_interest'; 
			}
			
			else
			{
				//구 거래원장
				$arrayParser['[이번회수일]'] = 'LOAN_INFO_TRADE_OLD__trade_date'; 
				$arrayParser['[회수내용]'] = 'LOAN_INFO_TRADE_OLD__trade_type'; 
				$arrayParser['[회수금액]'] = 'LOAN_INFO_TRADE_OLD__trade_money'; 
				$arrayParser['[수령이자]'] = 'LOAN_INFO_TRADE_OLD__return_interest_sum'; 
				$arrayParser['[원금충당액]'] = 'LOAN_INFO_TRADE_OLD__return_origin';	
				$arrayParser['[다음지불일]'] = 'LOAN_INFO_TRADE__return_date';	
			}

			//전회회수
			if(isset($trade_prv))
			{
				$arrayParser['[전회회수일]'] 	= 'LOAN_INFO_TRADE_PREVIOUS__trade_date'; 
				$arrayParser['[전회미납금]'] 	= 'LOAN_INFO_TRADE_PREVIOUS__tail_rack_money'; 
				$arrayParser['[전회잔고]'] 		= 'LOAN_INFO_TRADE_PREVIOUS__balance'; 
			}
		}
		
		// 사모사채 양도양수 계약서 양식일 경우
		if($post_cd=="SM003")
		{
			$loan_info_no = $loan_info_law_no ?? '';
			$loan_info_law_no = '';

			$in_usr_info_no = $input_params['in_usr_info_no'];
			$transfer_date = $input_params['transfer_date'];
			$transfer_trade_money = $input_params['transfer_trade_money'];

			$arrayParser['[계약 발행일]'] = 'COMMON__transfer_date';
			$arrayParser['[양도 금액]'] = 'COMMON__transfer_trade_money';
			$arrayParser['[양도 금액 한글]'] = 'COMMON__transfer_trade_money_kor';
			$arrayParser['[양도인 이름]'] = "LOAN_INFO__name";
			$arrayParser['[양도인 사업자/주민번호]'] = "LOAN_INFO__ssn";
			$arrayParser['[양수인 이름]'] = "LOAN_USR_INFO__name";
			$arrayParser['[양수인 사업자/주민번호]'] = "LOAN_USR_INFO__ssn";
			$arrayParser['[양수인 번호]'] = "LOAN_USR_INFO__ph1";

			$arrayResult['COMMON']['transfer_date'] = substr($transfer_date,0,4)."년 ".substr($transfer_date,4,2)."월 ".substr($transfer_date,-2)."일";
			$arrayResult['COMMON']['transfer_trade_money'] = Func::numberFormat($transfer_trade_money);
			$arrayResult['COMMON']['transfer_trade_money_kor'] = PaperPrint::getHanMoney($transfer_trade_money);

			// 양도인 주소 직접 입력시
			if(!empty($input_params['out_addr1']) && !empty($input_params['out_addr2']))
			{
				$arrayResult['COMMON']['out_addr1'] = $input_params['out_addr1']."<br>&nbsp;&nbsp;&nbsp;".$input_params['out_addr2'];
				$arrayParser['[양도인 주소]'] = 'COMMON__out_addr1';
			}
			else
			{
				$arrayParser['[양도인 주소]'] = "LOAN_INFO__addr1";
			}
			// 양수인 주소 직접 입력시
			if(!empty($input_params['in_addr1']) && !empty($input_params['in_addr2']))
			{
				$arrayResult['COMMON']['in_addr1'] = $input_params['in_addr1']."<br>&nbsp;&nbsp;&nbsp;".$input_params['in_addr2'];
				$arrayParser['[양수인 주소]'] = 'COMMON__in_addr1';
			}
			else
			{
				$arrayParser['[양수인 주소]'] = "LOAN_USR_INFO__addr1";
			}
		}
		// 투자자양식(금전소비대차계약서,확약서)은 파일명이 숫자형태가 아니다..
		else if(!is_numeric($post_cd))
		{
			$loan_info_no = $loan_info_law_no ?? '';
			$loan_info_law_no = '';
		}

		$arrayParser['[(주)상호]'] = 'COMMON__corp_name';
		$arrayParser['[상호]'] = 'COMMON__corp_name_only';
		$arrayParser['[사업자등록번호]'] = 'COMMON__corp_company';
		$arrayParser['[대표명]'] = 'COMMON__corp_ceo_name';
		$arrayParser['[대부주소]'] = 'COMMON__corp_address';
		$arrayParser['[대부주소온리]'] = 'COMMON__corp_address_only';
		$arrayParser['[대부전화]'] = 'COMMON__corp_ph';
		$arrayParser['[대부담당자]'] = 'COMMON__corp_mid';
		$arrayParser['[대부표시]'] = 'COMMON__corp_mark';
		$arrayParser['[도장]'] = 'COMMON__stamp';
		$arrayParser['[오늘년월일]'] = 'COMMON__today';
		$arrayParser['[오늘년월일dot]'] = 'COMMON__dot_today';
		$arrayParser['[완납금액]'] = 'LOAN_INFO__fullpay_money';
		$arrayParser['[완납거래금]'] = 'LOAN_LAST_TRADE__complete_money';
		$arrayParser['[완납거래금한글]'] = 'LOAN_LAST_TRADE__complete_money_kor';
		// $arrayParser['[완납일]'] = 'LOAN_INFO__fullpay_date';
		$arrayParser['[완납일]'] = 'LOAN_LAST_TRADE__complete_date';
		$arrayParser['[원금]'] = 'LOAN_INFO__balance';
		$arrayParser['[이율]'] = 'LOAN_INFO__loan_rate';
		$arrayParser['[연체이율]'] = 'LOAN_INFO__loan_delay_rate';
		$arrayParser['[소멸시효일]'] = 'LOAN_INFO__lost_date';
		// $arrayParser['[이자]'] = 'LOAN_INFO__interest_sum';

		$arrayParser['[고객명]'] = 'LOAN_INFO__name';
		$arrayParser['[계약번호]'] = 'LOAN_INFO__loan_info_no';
		$arrayParser['[차입자번호]'] = 'LOAN_INFO__cust_info_no';
		$arrayParser['[주민번호]'] = 'LOAN_INFO__ssn';
		$arrayParser['[마스킹주민번호]'] = 'LOAN_INFO__mask_ssn';
		$arrayParser['[민번별3개]'] = 'LOAN_INFO__mask_ssn2';
		$arrayParser['[생년월일]'] = 'LOAN_INFO__birth';
		$arrayParser['[성별]'] = 'LOAN_INFO__sex';
		$arrayParser['[우편번호]'] = 'LOAN_INFO__zip';
		$arrayParser['[계좌번호]'] = 'LOAN_INFO__loan_bank_ssn';
		$arrayParser['[대출금액]'] = 'LOAN_INFO__first_loan_info_money';
		$arrayParser['[대출금액한글]'] = 'LOAN_INFO__first_loan_info_money_kor';
		// $arrayParser['[완납금액]'] = 'LOAN_INFO__fullpay_money';	// 중복으로 임시적으로 제거
		$arrayParser['[잔액]'] = 'LOAN_INFO__balance';
		$arrayParser['[이자]'] = 'LOAN_INFO__interest_sum';
		$arrayParser['[정상이자]'] = 'LOAN_INFO__interest';
		$arrayParser['[연체이자]'] = 'LOAN_INFO__delay_interest';
		$arrayParser['[현재미수이자]'] = 'LOAN_INFO__m_interest';
		$arrayParser['[현재합계]'] = 'LOAN_INFO__total_money';
		$arrayParser['[비용]'] = 'LOAN_INFO__cost_money';
		$arrayParser['[주소]'] = 'LOAN_INFO__address';
		//$arrayParser['[담당자]'] = 'LOAN_INFO__app_manager_id';
		$arrayParser['[개인/법인]'] = 'LOAN_INFO__com_div';
		$arrayParser['[최초매입처]'] = 'LOAN_INFO__buy_corp';
		$arrayParser['[연체시작일]'] = 'DELAY_INFO__delay_sdate';
		$arrayParser['[/연체시작일/]'] = 'DELAY_INFO__delay_sdate2';
		$arrayParser['[신용등록일]'] = 'DELAY_INFO__credit_order';
		$arrayParser['[신용등록액]'] = 'DELAY_INFO__credit_order_money';
		$arrayParser['[납입기한]'] = 'LOAN_INFO__limit_day';
		$arrayParser['[담보물주소]'] = 'LOAN_INFO__dambo_address';
		$arrayParser['[담보물주소1]'] = 'LOAN_INFO__dambo_address1';
		$arrayParser['[담보물주소2]'] = 'LOAN_INFO__dambo_address2';
		
	


		// 20230420 양식지 작업 추가내용(이세형)
		$arrayParser['[대출일년월일]'] = 'LOAN_INFO__contract_date';
		$arrayParser['[대출일년월일한글]'] = 'LOAN_INFO__contract_date_ko';
		$arrayParser['[상환년월일]'] = 'LOAN_INFO__return_date_ymd';
		$arrayParser['[약정일]'] = 'LOAN_INFO__contract_day';
		$arrayParser['[만기일]'] = 'LOAN_INFO__contract_end_date';
		$arrayParser['[한도액]'] = 'LOAN_INFO__limit_money'; 
		$arrayParser['[상환액]'] = 'LOAN_INFO__charge_money'; 
		$arrayParser['[최초대출액]'] = 'LOAN_INFO__first_loan_info_money';
		$arrayParser['[최근대출일]'] = 'LOAN_INFO__last_loan_date';
		$arrayParser['[가상계좌]'] = 'VIR_ACCT__vir_acct_ssn';
		$arrayParser['[가상계좌번호]'] = 'LOAN_INFO__vir_acct_ssn';
		$arrayParser['[가상계좌은행명]'] = 'LOAN_INFO__vir_acct_ssn_nm';
		$arrayParser['[신대부주소앞]'] = 'COMMON__CORP_ADDR1';
		$arrayParser['[신대부주소뒤]'] = 'COMMON__CORP_ADDR2';
		$arrayParser['[신대부우편번호]'] = 'COMMON__CORP_POST_ZIP';
		$arrayParser['[대부팩스]'] = 'BRANCH__fax';
		$arrayParser['[오늘년]'] = 'COMMON__year_today';
		$arrayParser['[오늘월]'] = 'COMMON__month_today';
		$arrayParser['[오늘일]'] = 'COMMON__day_today';
		$arrayParser['[요청일]'] = 'COMMON__request_ymd';
		$arrayParser['[오늘요일]'] = 'COMMON__yoil_today';
		$arrayParser['[기준일자년]'] = 'COMMON__print_basis_date_y';
		$arrayParser['[기준일자월]'] = 'COMMON__print_basis_date_m';
		$arrayParser['[기준일자일]'] = 'COMMON__print_basis_date_d';
		$arrayParser['[보증인이름2]'] = 'LOAN_INFO_GUARANTOR__name';
		$arrayParser['[보증인민번]'] = 'LOAN_INFO_GUARANTOR__ssn';
		$arrayParser['[보증인주민번호마스킹]'] = 'LOAN_INFO_GUARANTOR__mask_ssn';
		$arrayParser['[보증인등본주소앞]'] = 'LOAN_INFO_GUARANTOR__addr11';
		$arrayParser['[보증인등본주소뒤]'] = 'LOAN_INFO_GUARANTOR__addr12';
		$arrayParser['[보증인등본우편번호]'] = 'LOAN_INFO_GUARANTOR__zip1';
		$arrayParser['[보증인사건번호]'] = 'LOAN_INFO_GUARANTOR__case_number';
		$arrayParser['[보증인채권번호]'] = 'LOAN_INFO_GUARANTOR__guarantor_no';
		$arrayParser['[보증인법원명]'] = 'LOAN_INFO_GUARANTOR__court';
		// $arrayParser['[보증인회생위원]'] = 'LOAN_INFO_GUARANTOR__member';
		$arrayParser['[담당자]'] = 'USERS__name';
		$arrayParser['[직원주민]'] = 'USERS__ssn';
		$arrayParser['[직원직급]'] = 'CONF_CODE__name';
		$arrayParser['[등본주소앞]'] = 'CUST_INFO_EXTRA__addr21';
		$arrayParser['[등본주소뒤]'] = 'CUST_INFO_EXTRA__addr22';
		$arrayParser['[등본우편번호]'] = 'CUST_INFO_EXTRA__zip2';

		$arrayParser['[별지내용]'] = 'LOAN_INFO__document_memo';
		$arrayParser['[법조치사유]'] = 'LOAN_INFO__law_reason_memo';
		$arrayParser['[사건번호]'] = 'LOAN_IRL__law_event_no';
		$arrayParser['[채권번호]'] = 'LOAN_IRL__payment_bond_no';
		$arrayParser['[법원명]'] = 'LOAN_IRL__law_justice';
		$arrayParser['[법조치구분]'] = 'LOAN_INFO__law_div';
		$arrayParser['[법조치세부]'] = 'LOAN_INFO__law_type';
		$arrayParser['[법조치상태]'] = 'LOAN_INFO__law_proc_status';
		$arrayParser['[관할법원]'] = 'LOAN_INFO__court_cd';
		$arrayParser['[대상자명]'] = 'LOAN_INFO__target_name';
		$arrayParser['[대상자개인법인]'] = 'LOAN_INFO__target_com_div';
		$arrayParser['[대표자명]'] = 'LOAN_INFO__target_owner_name';
		$arrayParser['[송달주소]'] = 'LOAN_INFO__target_address';
		$arrayParser['[청구금액]'] = 'LOAN_INFO__law_app_money';
		$arrayParser['[법비용합계]'] = 'LOAN_INFO__cost_money';
		$arrayParser['[결재상태]'] = 'LOAN_INFO__confirm_status';
		// $arrayParser['[회생위원]'] = 'LOAN_IRL__member';

		$arrayParser['[중개성별]'] = 'LOAN_INFO__sex_checkbox';
		$arrayParser['[중개최초연월일]'] = 'AGENT_LEVEL__agent_app_date1';
		$arrayParser['[중개최초상호]'] = 'AGENT_LEVEL__agent_name1';
		$arrayParser['[중개최초대부]'] = 'AGENT_LEVEL__agent_ssn1';
		$arrayParser['[중개최초협회]'] = 'AGENT_LEVEL__agent_assn1';
		$arrayParser['[중개최초전화]'] = 'AGENT_LEVEL__agent_ph1';		
		$arrayParser['[중개1차연월일]'] = 'AGENT_LEVEL__agent_app_date2';
		$arrayParser['[중개1차상호]'] = 'AGENT_LEVEL__agent_name2';
		$arrayParser['[중개1차대부]'] = 'AGENT_LEVEL__agent_ssn2';
		$arrayParser['[중개1차협회]'] = 'AGENT_LEVEL__agent_assn2';
		$arrayParser['[중개1차전화]'] = 'AGENT_LEVEL__agent_ph2';
		$arrayParser['[중개2차연월일]'] = 'AGENT_LEVEL__agent_app_date3';
		$arrayParser['[중개2차상호]'] = 'AGENT_LEVEL__agent_name3';
		$arrayParser['[중개2차대부]'] = 'AGENT_LEVEL__agent_ssn3';
		$arrayParser['[중개2차협회]'] = 'AGENT_LEVEL__agent_assn3';
		$arrayParser['[중개2차전화]'] = 'AGENT_LEVEL__agent_ph3';
		$arrayParser['[중개3차연월일]'] = 'AGENT_LEVEL__agent_app_date4';
		$arrayParser['[중개3차상호]'] = 'AGENT_LEVEL__agent_name4';
		$arrayParser['[중개3차대부]'] = 'AGENT_LEVEL__agent_ssn4';
		$arrayParser['[중개3차협회]'] = 'AGENT_LEVEL__agent_assn4';
		$arrayParser['[중개3차전화]'] = 'AGENT_LEVEL__agent_ph4';
		$arrayParser['[중개4차연월일]'] = 'AGENT_LEVEL__agent_app_date5';
		$arrayParser['[중개4차상호]'] = 'AGENT_LEVEL__agent_name5';
		$arrayParser['[중개4차대부]'] = 'AGENT_LEVEL__agent_ssn5';
		$arrayParser['[중개4차협회]'] = 'AGENT_LEVEL__agent_assn5';
		$arrayParser['[중개4차전화]'] = 'AGENT_LEVEL__agent_ph5';
		$arrayParser['[중개5차연월일]'] = 'AGENT_LEVEL__agent_app_date6';
		$arrayParser['[중개5차상호]'] = 'AGENT_LEVEL__agent_name6';
		$arrayParser['[중개5차대부]'] = 'AGENT_LEVEL__agent_ssn6';
		$arrayParser['[중개5차협회]'] = 'AGENT_LEVEL__agent_assn6';
		$arrayParser['[중개5차전화]'] = 'AGENT_LEVEL__agent_ph6';
		$arrayParser['[중개6차연월일]'] = 'AGENT_LEVEL__agent_app_date7';
		$arrayParser['[중개6차상호]'] = 'AGENT_LEVEL__agent_name7';
		$arrayParser['[중개6차대부]'] = 'AGENT_LEVEL__agent_ssn7';
		$arrayParser['[중개6차협회]'] = 'AGENT_LEVEL__agent_assn7';
		$arrayParser['[중개6차전화]'] = 'AGENT_LEVEL__agent_ph7';
		$arrayParser['[중개7차연월일]'] = 'AGENT_LEVEL__agent_app_date8';
		$arrayParser['[중개7차상호]'] = 'AGENT_LEVEL__agent_name8';
		$arrayParser['[중개7차대부]'] = 'AGENT_LEVEL__agent_ssn8';
		$arrayParser['[중개7차협회]'] = 'AGENT_LEVEL__agent_assn8';
		$arrayParser['[중개7차전화]'] = 'AGENT_LEVEL__agent_ph8';
		$arrayParser['[중개8차연월일]'] = 'AGENT_LEVEL__agent_app_date9';
		$arrayParser['[중개8차상호]'] = 'AGENT_LEVEL__agent_name9';
		$arrayParser['[중개8차대부]'] = 'AGENT_LEVEL__agent_ssn9';
		$arrayParser['[중개8차협회]'] = 'AGENT_LEVEL__agent_assn9';
		$arrayParser['[중개8차전화]'] = 'AGENT_LEVEL__agent_ph9';

		$arrayParser['[체크-고객연락]'] = 'LOAN_INFO__collection_route01';
		$arrayParser['[체크-중개인연락처고객]'] = 'LOAN_INFO__agent_route05';
		$arrayParser['[체크-중개인연락처홈페이지]'] = 'LOAN_INFO__agent_route01';
		$arrayParser['[체크-중개인연락처신문]'] = 'LOAN_INFO__agent_route02';
		$arrayParser['[체크-중개인연락처전단]'] = 'LOAN_INFO__agent_route03';
		$arrayParser['[체크-중개인연락처기타]'] = 'LOAN_INFO__agent_route04';
		$arrayParser['[체크-중개인연락처상세]'] = 'LOAN_INFO__agent_route_memo';
		$arrayParser['[체크-중개인연락]'] = 'LOAN_INFO__collection_route02';
		$arrayParser['[체크-고객연락처제휴]'] = 'LOAN_INFO__member_route04';
		$arrayParser['[체크-고객연락처기타]'] = 'LOAN_INFO__member_route05';
		$arrayParser['[체크-고객연락처상세]'] = 'LOAN_INFO__member_route_memo';
		$arrayParser['[체크-고객연락전화]'] = 'LOAN_INFO__member_contact02';
		$arrayParser['[체크-고객연락SMS]'] = 'LOAN_INFO__member_contact03';
		$arrayParser['[체크-고객연락EMAIL]'] = 'LOAN_INFO__member_contact04';
		$arrayParser['[체크-고객연락우편물]'] = 'LOAN_INFO__member_contact05';
		$arrayParser['[체크-고객연락방문]'] = 'LOAN_INFO__member_contact01';
		$arrayParser['[체크-고객연락기타]'] = 'LOAN_INFO__member_contact06';
		$arrayParser['[체크-고객연락상세]'] = 'LOAN_INFO__member_contact_memo';

		$arrayParser['[img]'] = 'COP__img';
		$arrayParser['[img2]'] = 'COP__img2';

		// 20230420 양식지 작업 추가내용(이세형)
		// env, app 값이 안불러와짐. 일단 강제등록.
		// $arrayResult['COMMON']['CORP_ADDR1'] = config('app.corp_addr1');
		// $arrayResult['COMMON']['CORP_ADDR2'] = env('CORP_ADDR2');
		// $arrayResult['COMMON']['CORP_POST_ZIP'] =  env('CORP_POST_ZIP');
		// $arrayResult['COMMON']['corp_name'] = '(주)'.env('CORP_NAME');
		// $arrayResult['COMMON']['corp_name_only'] = env('CORP_NAME');
		// $arrayResult['COMMON']['corp_company'] = env('CORP_COMPANY_NUM1')."-".env('CORP_COMPANY_NUM2')."-".env('CORP_COMPANY_NUM3');
		// $arrayResult['COMMON']['corp_ceo_name'] = env('CORP_CEO_NAME');
		// $arrayResult['COMMON']['corp_address'] = env('CORP_ZIP').")".env('CORP_ADDR1').env('CORP_ADDR2');
		// $arrayResult['COMMON']['corp_address_only'] = env('CORP_ADDR1').env('CORP_ADDR2');

		$arrayResult['COMMON']['CORP_ADDR1'] = '경기도 파주시 문산읍 방촌로 1719-10';
		$arrayResult['COMMON']['CORP_ADDR2'] = '209호(와우시티에이동)';
		$arrayResult['COMMON']['CORP_POST_ZIP'] =  '10816';
		$arrayResult['COMMON']['corp_name'] = '(주)청';
		$arrayResult['COMMON']['corp_name_only'] = '(주)청';
		$arrayResult['COMMON']['corp_company'] = "895-86-02415";
		$arrayResult['COMMON']['corp_ceo_name'] = '정영란';
		$arrayResult['COMMON']['corp_address'] = "10816)경기도 파주시 문산읍 방촌로 1719-10 209호(와우시티에이동)";
		$arrayResult['COMMON']['corp_address_only'] = '경기도 파주시 문산읍 방촌로 1719-10 209호(와우시티에이동)';

		$arrayResult['COMMON']['corp_ph'] = PaperPrint::strReplace('corp_ph');
		$arrayResult['COMMON']['corp_mid'] = PaperPrint::strReplace('name');
		$arrayResult['COMMON']['today'] = date("Y년 m월 d일");
		$arrayResult['COMMON']['dot_today'] = date("Y.m.d");
		$arrayResult['COMMON']['space_today'] = date("Y 년 m 월 d 일");
		$arrayResult['COMMON']['today_dot'] = date("Y . m . d . ");

		// 20230420 양식지 작업 추가내용(이세형)
		$arrayResult['COMMON']['year_today'] = date('Y');
		$arrayResult['COMMON']['month_today'] = date('m');
		$arrayResult['COMMON']['day_today'] = date('d');
		$arrayResult['COMMON']['yoil_today'] = vars::$arrayWeekDay[date('w')];
		$arrayResult['COMMON']['request_ymd'] = date("Y년 m월 d일", strtotime("+6 day"));
		$arrayResult['COMMON']['corp_mark'] = "KICORES";
		$arrayResult['COMMON']['stamp'] = "K1_stamp";

		// 선택주소
		if(!empty($input_params['addr1']) && !empty($input_params['addr2']))
		{
			$arrayResult['COMMON']['sel_addr1'] = $input_params['addr1']." ".$input_params['addr2'];
			$arrayResult['COMMON']['sel_addr11'] = $input_params['addr1'];
			$arrayResult['COMMON']['sel_addr12'] = $input_params['addr2'];
			$arrayParser['[채권자주소]'] = 'COMMON__sel_addr1';
			$arrayParser['[채권자주소1]'] = 'COMMON__sel_addr11';
			$arrayParser['[채권자주소2]'] = 'COMMON__sel_addr12';
		}
		else
		{
			$arrayParser['[채권자주소]'] = "LOAN_INFO__addr1";
			$arrayParser['[채권자주소1]'] = "LOAN_INFO__addr11";
			$arrayParser['[채권자주소2]'] = "LOAN_INFO__addr12";
		}

		if(!empty($input_params['zip']))
		{
			$arrayResult['COMMON']['sel_zip'] = $input_params['zip'];
		}

		if(!empty($input_params['print_basis_date']))
		{
			$basis_date_array = explode('-', $input_params['print_basis_date']);

			$arrayResult['COMMON']['print_basis_date_y'] = $basis_date_array[0];
			$arrayResult['COMMON']['print_basis_date_m'] = preg_replace('/(0)(\d)/','$2', $basis_date_array[1]);
			$arrayResult['COMMON']['print_basis_date_d'] = preg_replace('/(0)(\d)/','$2', $basis_date_array[2]);
		}

		// 20230808 금전소비대차 내용
		$arrayParser['[채권자]'] = "LOAN_INFO__name";
		$arrayParser['[채권자전화번호]'] = "LOAN_INFO__ph1";
		$arrayParser['[채권자주민번호]'] = "LOAN_INFO__ssn";
		$arrayParser['[대여금액한글]'] = "LOAN_INFO__loan_money_han";
		$arrayParser['[대여금액숫자]'] = "LOAN_INFO__loan_money";
		$arrayParser['[최초투자금액한글]'] = "LOAN_INFO__trade_money_han";
		$arrayParser['[최초투자금액숫자]'] = "LOAN_INFO__trade_money";
		$arrayParser['[금리]'] = "LOAN_INFO__ratio";
		$arrayParser['[연체금리]'] = "LOAN_INFO__delay_ratio";
		$arrayParser['[오늘날짜]'] = "COMMON__space_today";
		$arrayParser['[오늘날짜점]'] = "COMMON__today_dot";

		// 대출실행일을 직접 입력받았을 경우
		if(!empty($input_params['print_trade_date']))
		{
			$arrayResult['COMMON']['print_trade_date'] = substr($input_params['print_trade_date'],0,4)."년  ".substr($input_params['print_trade_date'],5,2)."월  ".substr($input_params['print_trade_date'],-2)."일";
			$arrayParser['[시작일]'] = 'COMMON__print_trade_date';
		}
		else
		{
			$arrayParser['[시작일]'] = "LOAN_INFO__trade_date";
		}
		$arrayParser['[시작년]'] = "LOAN_INFO__trade_date_y";
		$arrayParser['[시작월일]'] = "LOAN_INFO__trade_date_md";
		$arrayParser['[만료일]'] = "LOAN_INFO__contract_end_date";
		$arrayParser['[이수일]'] = "LOAN_INFO__contract_day";
		$arrayParser['[기간]'] = "LOAN_INFO__term";
		// $arrayParser['[채무자]'] = "K1_stamp";

		#$arrayResult['COP']['img'] = "/img/main2.jpg";
		#$arrayResult['COP']['img2'] = "/img/k.jpg";

		if($ups_erp=="UPS")
		{
			$arrayResult['LOAN_INFO'] = PaperPrint::getLoanAppInfo($no);
			$arrayResult['AGENT_LEVEL'] = PaperPrint::getAgentLevel($no);
		}
		else
		{
			$arrayResult['LOAN_INFO'] = PaperPrint::getLoanInfo($no, $loan_info_law_no, "", "", "");
			$arrayResult['LOAN_INFO_GUARANTOR'] = PaperPrint::getLoanInfoGuarantor($no);
			$arrayResult['USERS'] = PaperPrint::getUsers($no, $post_cd, $loan_info_law_no);
			$arrayResult['CUST_INFO_EXTRA'] = PaperPrint::getCustInfoExtra($no);
			$arrayResult['BRANCH'] = PaperPrint::getBranch();
			$arrayResult['CONF_CODE'] = PaperPrint::getUserRankCd($no);
			$arrayResult['LOAN_IRL'] = PaperPrint::getLoanIrl($no);
			//$arrayResult['VIR_ACCT'] = PaperPrint::getVirtualAccount($no);
			$arrayResult['LOAN_LAST_TRADE'] = PaperPrint::getLoanLastTrade($no);
			$arrayResult['DELAY_INFO'] = PaperPrint::getDelayInfo($no);
			$arrayResult['LOAN_INFO_LAW_COST'] = PaperPrint::getLoanInfoLaw($no);
			$arrayResult['LOAN_INFO'] = PaperPrint::getInvList($no,$loan_info_no,$input_params,$post_cd);
			
			if($post_cd=="SM003")
			{
				$arrayResult['LOAN_USR_INFO'] = PaperPrint::getLoanUsrInfo($in_usr_info_no, $input_params, $post_cd);
			}
			if(!empty($arrayKeys))
			{
				if($table_type=='new')
				{
					$arrayResult['LOAN_INFO_TRADE'] = PaperPrint::getLoanInfoTrade($no, $trade_no, $table_type);
				}
				else
				{
					$arrayResult['LOAN_INFO_TRADE_OLD'] = PaperPrint::getLoanInfoTrade($no, $trade_no, $table_type);
				}
			}

			if(isset($trade_prv))
			{
				$arrayResult['LOAN_INFO_TRADE_PREVIOUS'] = PaperPrint::getLoanInfoTrade($no, $no_prv, $table_type);
			}
			
		}
		
        if(isset($arrayResult))
		{
			foreach($arrayResult as $table=>$result)
			{
                if(empty($result)) continue;
				foreach($result as $column => $value )
	            {
					// 파싱열에 포함된 컬럼만 배열로 저장한다.
					$pullColumn = $table.'__'.$column;
					if(in_array($pullColumn, $arrayParser))
                    {
						$arrayMsg[$pullColumn] = $value;
                    }
					
	            }
			}
		}

		foreach($arrayParser as $parser => $column)
		{
			if(!isset($arrayMsg[$column]))
			{
				// continue;
				$value = '';		// 데이터 없으면 파싱 문자열 미출력
			}
			else
			{
				$value = $arrayMsg[$column];

				if($column == "reg_date")
				{
					$value = date("Y-m-d", strtotime($value));
				}
				
			}            
			
			$msg = str_replace($parser, $value, $msg);
		}
        return $msg;
    }

	/*
	*	데이터 select box 로 선택 
	*/
	public static function strReplace($str, $postcd='', $loan_info_law_no='')
	{
		$array_conf = Func::getConfigArr();

		if($str == 'corp_ph')
		{
			$cnt1 = 0;
			$cnt2 = 0;
			$paperPh = '';
			$replace = "<select id=\"sb\" name=\"sb\" style=\"width:110;\">".Func::selectStrAdd($paperPh, 'print_tel')."</select>";
		}
		elseif($str == 'name')
		{
			$users = DB::TABLE('branch as b')->JOIN('users as u', 'b.code', '=', 'u.branch_code')->WHERE('u.save_status', 'Y')
						->WHERERAW("(u.permit like '%,E003,%' or permit like '%,E004,%') ")
						->ORDERBY('b.branch_name', 'asc')->ORDERBY('u.name', 'asc')->GET();

			$users = Func::chungDec(["BRANCH", "USERS"], $users);

			foreach($users as $user => $v)
			{
				$array_branch_member[$v->id] = $v->name;
			}

			$replace = "<select id=\"sb\" name=\"sb\" style=\"width:65\">".Func::selectStrAdd($array_branch_member, 'print_manager')."</select>";

			if($postcd == '53')
			{
				$vmid = DB::TABLE('loan_info')->SELECT('*')->WEHRE('no', $loan_info_law_no)->FIRST();
				$replace = "<select id=\"sb\" name=\"sb\" style=\"width:65\">".Func::selectStrAdd($array_branch_member, $vmid['manager_id'])."</select>";
			}
		}
		elseif($str == 'court_cd')
		{
			$replace = "<select id=\"sb\" name=\"sb\" style=\"width:200;\">".Func::selectStrAdd($array_conf['court_cd'], 'sb')."</select>";
		}

		return $replace;

	}

	/*
	*	계약번호로 LOAN_INFO_TRADE  가져오기
	*/
	public static function getLoanInfoTrade($no, $trade_no, $table_type)
	{
		$array_config = Func::getConfigArr();

		if($table_type == 'new')
		{
			$result = DB::table('loan_info_trade');
		}
		else if(($table_type == 'old'))
		{
			$result = DB::table('loan_info_trade_old');
		}

		$result->SELECT('*');
		$result->WHERE('no', $trade_no);
		$result->WHERE('loan_info_no', $no);
		$result = $result->FIRST();
		
		if($table_type == 'new')
		{
			$result = Func::chungDec(["loan_info_trade"], $result);	// CHUNG DATABASE DECRYPT
		}
		else 
		{
			$result = Func::chungDec(["loan_info_trade_old"], $result);	// CHUNG DATABASE DECRYPT
		}
		
		
		if(isset($result))
		{
			$result->trade_date = substr($result->trade_date, 0, 4)."/".preg_replace('/(0)(\d)/','$2', substr($result->trade_date, 4, 2))."/".preg_replace('/(0)(\d)/','$2', substr($result->trade_date, -2));
			$result->return_origin = Func::numberFormat($result->return_origin);			//	원금충당액
			$result->trade_money = Func::numberFormat($result->trade_money);				// 	회수 금액
			$result->return_interest_sum = Func::numberFormat($result->return_interest_sum);//	수령 이자
			$result->trade_type =  $array_config['trade_in_type'][$result->trade_type] ?? '';		//	회수 내용
			$result->return_date =  substr($result->return_date,0,4)."/".substr($result->return_date,4,2)."/".substr($result->return_date,6,2);		//	회수 내용

			$result->balance =  Func::numberFormat($result->balance); 							//전회잔고
			$result->tail_rack_money  =  Func::numberFormat($result->tail_rack_money ); 		//전회미납금
		}

		return $result;
	}

	/*
	*	계약번호로 LOAN_INFO_LAW_COST  가져오기
	*/
	public static function getLoanInfoLaw($no)
	{
		$result = DB::table('loan_info_law_cost');
		$result->SELECT("lawdeposit_am");
		$result->WHERE('loan_info_trade_no', $no);

		$result = $result->FIRST();
		$result = Func::chungDec(["loan_info_law_cost"], $result);	// CHUNG DATABASE DECRYPT
		
		if(isset($result))
		{
			$result->lawdeposit_am = Func::numberFormat($result->lawdeposit_am);
		}
		else
		{
			//$result = new stdClass();
			$result = (object) ['lawdeposit_am' => 0] ;
		}
		return $result;
	}
	public static function getLoanInfo($no, $loan_info_law_no="", $today="", $sms_customer="N", $list_query="")
	{
		$array_conf = Func::getConfigArr();

		if( $today=="" )
		{
			$today = date("Ymd");
		}

		// if( $list_query!="" )
		// {
		//     $list_query = decrypt(urldecode($list_query));
		//     $list_query = "LOAN_INFO.NO IN ( SELECT NO FROM ( ".$list_query." ) AS tmp_table_sms )";
		//     log::debug($list_query);
		// }

		// 고객별 통합할 변수
		$cus_over_money     = 0;
		$cus_balance        = 0;
		$cus_interest       = 0;
		$cus_delay_money    = 0;
		$cus_delay_interest = 0;
		$cus_lack_money     = 0;
		$cus_interest_sum   = 0;
		$cus_charge_money   = 0;
		$cus_fullpay_money  = 0;
		$cus_fullpay_moneye = 0;

		$hasBanSmsColumn = DB::getSchemaBuilder()->hasColumn('loan_info', 'ban_sms');

		$DATA = DB::table('loan_info');
		$DATA->JOIN("cust_info", "loan_info.cust_info_no", "=", "cust_info.no");
		$DATA->JOIN("cust_info_extra", "cust_info.no", "=", "cust_info_extra.cust_info_no");
		$DATA->SELECT("loan_info.*", "cust_info.*", "cust_info_extra.*", "loan_info.no as loan_info_no", "loan_info.first_loan_money as first_loan_info_money");
		//$DATA->selectRaw("(select vir_acct_ssn from vir_acct where cust_info_extra.cust_info_no = vir_acct.cust_info_no and vir_acct.save_status='Y' order by no desc limit 1)");
		$DATA->WHERE('cust_info.save_status','Y');
		$DATA->WHERE('loan_info.save_status','Y');
		$DATA->WHERE('loan_info.status', '!=', 'X');

		if($hasBanSmsColumn)
		{
			$DATA->WHERERAW("coalesce(ban_sms,'')!='Y'");
		}
		

		// 검색된 조건내에서만 대상을 추출한다. - 특히 고객별.... 
		// if( $list_query!="" )
		// {
		//     $DATA->WHERERAW($list_query);
		// }
		
	

		// 고객통합이면 고객번호 기준으로 계약들 가져옴
		if( $sms_customer=="Y" )
		{
			$DATA->WHERERAW("cust_info.no=( select cust_info_no from loan_info where no = ? )", [$no]);
		}
		else
		{
			$DATA->WHERE("loan_info.no", $no);
		}
		
		
		$RSLT = $DATA->ORDERBY("loan_info.no", "desc")->GET();
		$RSLT = Func::chungDec(["LOAN_INFO","CUST_INFO","CUST_INFO_EXTRA"], $RSLT);	// CHUNG DATABASE DECRYPT
		
		foreach($RSLT as $result)
		{	
			$balance_format = $result->balance;
			$result->m_interest = Func::numberFormat(($result->interest_sum ?? 0) - ($result->delay_interest ?? 0)); // [현재미수이자]
			$result->total_money = Func::numberFormat(($result->balance ?? 0) + ($result->interest_sum ?? 0) + ($result->cost_money ?? 0)); // [현재합계]
			$result->first_loan_info_money_kor  = Func::numberToKor($result->first_loan_info_money);  // [대출금액한글]
			$result->first_loan_info_money  = Func::numberFormat($result->first_loan_info_money);    	// [대출금액]
			$result->balance  = Func::numberFormat($result->balance);    					// [원금]
			$result->interest_sum  = Func::numberFormat($result->interest_sum);    			// [이자]
			$result->interest  = Func::numberFormat($result->interest);    					// [정상이자]
			$result->delay_interest  = Func::numberFormat($result->delay_interest);    					// [연체이자]
			$result->delay_term  = Func::numberFormat($result->delay_term);    					// [연체이자]
			$result->fullpay_money  = Func::numberFormat($result->fullpay_money);    		// [완납금액]
			$result->ssn = substr($result->ssn, 0, 6)."-".substr($result->ssn, 6, 7);		// [주민등록번호]
			$result->mask_ssn = substr($result->ssn, 0, 6)."-".substr($result->ssn, 7, 1)."******";	// [주민등록번호마스킹1]
			$result->mask_ssn2 = substr($result->ssn, 0, 6)."-".substr($result->ssn, 7, 4)."***";	// [주민등록번호마스킹2]
			$result->zip = $result->zip1;							// [우편번호]
			$result->address = $result->addr11." ".$result->addr12;				// [주소]
			$result->dambo_address = $result->dambo_addr11." ".$result->dambo_addr12;				// [담보물주소]
			$result->dambo_address1 = $result->dambo_addr11;				// [담보물주소1]
			$result->dambo_address2 = $result->dambo_addr12;				// [담보물주소2]
			$result->loan_rate = sprintf('%0.2f', ($result->loan_rate ?? 0));		// [이율]
			$result->loan_delay_rate = sprintf('%0.2f', ($result->loan_delay_rate ?? 0));	// [연체이율]
			$result->com_div = $array_conf['com_div'][$result->com_div ?? 'A'] ?? '';	// [개인/법인]
			$result->lost_date = date('Y-m-d', strtotime($result->lost_date));		// [소멸시효일]
			//$result->vir_acct_ssn_nm = ($result->vir_acct_ssn) ? '우리은행' : '';
			$result->return_date_ymd = substr($result->return_date,0,4)."년".substr($result->return_date,4,2)."월".substr($result->return_date,-2)."일";
			$result->charge_money = Func::numberFormat($result->charge_money) ?? 0;
			$result->contract_date = Func::dateFormat($result->contract_date);
			$result->contract_date_ko = substr($result->contract_date,0,4)."년  ".substr($result->contract_date,5,2)."월  ".substr($result->contract_date,-2)."일";
			$result->contract_end_date = Func::dateFormat($result->contract_end_date);
			$limit_format = $result->limit_money;
			
			$result->limit_money = Func::numberFormat($result->limit_money) ?? 0;			//한도액

			
			$result->use_money = Func::numberFormat($limit_format  - $balance_format);

			if($result->balance < $result->monthly_return_money && $result->status == 'A')
			{
				$result->contract_day = substr($result->return_date,8,2);
			}
			
			// 법착 없어도 보여주기
			$result->court_cd = PaperPrint::strReplace('court_cd');

			// 법착정보
			if(is_numeric($loan_info_law_no))
			{
				$law = DB::TABLE("loan_info_law")
				->SELECT("*")
				->WHERE("no", $loan_info_law_no)
				->WHERE("save_status", 'Y')
				->FIRST();
				
				$result->law_reason_memo = nl2br($law->law_reason_memo ?? '');
				$result->document_memo = nl2br($law->document_memo ?? '');
				log::debug('testttt : '.print_r($result->law_reason_memo, true));
				$result->eventNo = ($law->event_year ?? '').($law->event_cd ?? '').($law->event_no ?? '');
				$result->law_div = Vars::$arrayLawDiv[$law->law_div] ?? '';
				$result->law_type = Vars::$arrayLawType[$law->law_div][$law->law_type] ?? '';
				$result->law_proc_status = $array_conf['law_status_cd'][$law->law_proc_status_cd] ?? '';
				$result->target_name = $law->target_name ?? '';
				$result->target_com_div = $array_conf['com_div'][$law->target_com_div] ?? '';
				$result->target_owner_name = $law->target_owner_name ?? '';
				$result->target_address = ($law->target_addr1 ?? '')." ".($law->target_addr2 ?? '');
				$result->law_app_money = Func::numberFormat($law->law_app_mny ?? 0);
				$result->confirm_status = $array_conf['confirm_cd'][$law->law_confirm_status] ?? '';
				
				// 법비용
				if(DB::TABLE("loan_info_law_cost")->WHERE("loan_info_law_no", $loan_info_law_no)->WHERE("save_status", 'Y')->EXISTS())
				{
					$result->cost_money = Func::numberFormat(
						DB::TABLE("loan_info_law_cost")
						->WHERE("loan_info_law_no", $loan_info_law_no)
						->WHERE("save_status", 'Y')
						->SUM('trade_money')
					);
				}
				else
				{
					$result->cost_money = 0;
				}
			}

			// 납입기한
			$result->limit_day = Func::dateFormat(date('Y-m-d', (strtotime('+7 days'))), 'kor');

		}
		return $result;
	}

	/*
	*	계약번호로 LOAN_INFO  가져오기
	*/
	public static function getLoanAppInfo($no)
	{
		$rs = DB::table('LOAN_APP')
		->select('LOAN_APP.name','LOAN_APP.ssn','LOAN_APP.collection_route','LOAN_APP.agent_route','LOAN_APP.agent_route_memo','LOAN_APP.member_route','LOAN_APP.member_route_memo','LOAN_APP.member_contact','LOAN_APP.member_contact_memo','LOAN_APP.app_manager_id')
		->JOIN("LOAN_APP_EXTRA", "LOAN_APP.NO", "=", "LOAN_APP_EXTRA.LOAN_APP_NO")
		->where('LOAN_APP.no',$no)->first();
		$rs = Func::chungDec(["LOAN_APP","LOAN_APPEXTRA"], $rs);	// CHUNG DATABASE DECRYPT
		$arrayUserId = Func::getUserId();
		$rs->app_manager_id = Func::getArrayName($arrayUserId, $rs->app_manager_id);
		$ssnDiv = substr($rs->ssn, 6, 1);
		if($ssnDiv=="1"||$ssnDiv=="2"||$ssnDiv=="3"||$ssnDiv=="4") $rs->birth = Func::dateFormat('19'.substr($rs->ssn, 0, 6), 'kor');
		else $rs->birth = Func::dateFormat('20'.substr($rs->ssn, 0, 6));
		if($ssnDiv=="1"||$ssnDiv=="3"||$ssnDiv=="5"||$ssnDiv=="7")
		{
			$rs->sex = "남";
			$rs->sex_checkbox = "<input type='checkbox' checked disabled>&nbsp;남자&nbsp;&nbsp;/&nbsp;&nbsp;<input type='checkbox' disabled>&nbsp;여자";
		}
		else
		{
			$rs->sex = "여";
			$rs->sex_checkbox = "<input type='checkbox' disabled>&nbsp;남자&nbsp;&nbsp;/&nbsp;&nbsp;<input type='checkbox' checked disabled>&nbsp;여자";
		}
		
		// 적법수집 확인서 체크여부
		if($rs->collection_route=="01") $rs->collection_route01 = "checked";	// [적법수집확인서] 고객, 중개인중 먼저 연락한 사람 - 고객(고객->중개인)
		if($rs->agent_route=="01") $rs->agent_route01 = "checked";		// [적법수집확인서] 중개인 연락처를 알게 된 경로 - 인터넷 광고(홈페이지 등 연락처 남김 )
		if($rs->agent_route=="02") $rs->agent_route02 = "checked";		// [적법수집확인서] 중개인 연락처를 알게 된 경로 - 신문 광고
		if($rs->agent_route=="03") $rs->agent_route03 = "checked";		// [적법수집확인서] 중개인 연락처를 알게 된 경로 - 전단지 광고
		if($rs->agent_route=="04") $rs->agent_route04 = "checked";		// [적법수집확인서] 중개인 연락처를 알게 된 경로 - 기타(메모 입력)
		if($rs->agent_route=="05") $rs->agent_route05 = "checked";		// [적법수집확인서] 중개인 연락처를 알게 된 경로 - 인터넷 광고(고객이직접전화)
		if($rs->collection_route=="02") $rs->collection_route02 = "checked";	// [적법수집확인서] 고객, 중개인중 먼저 연락한 사람 - 중개인(중개인->고객)
		if($rs->member_route=="04") $rs->member_route04 = "checked";		// [적법수집확인서] 고객 연락처를 알게 된 경로 - 제휴처 제공
		if($rs->member_route=="05") $rs->member_route05 = "checked";		// [적법수집확인서] 고객 연락처를 알게 된 경로 - 기타(메모 입력)
		if($rs->member_contact=="01") $rs->member_contact01 = "checked";	// [적법수집확인서] 고객 연락 방법 - 방문
		if($rs->member_contact=="02") $rs->member_contact02 = "checked";	// [적법수집확인서] 고객 연락 방법 - 전화
		if($rs->member_contact=="03") $rs->member_contact03 = "checked";	// [적법수집확인서] 고객 연락 방법 - SMS
		if($rs->member_contact=="04") $rs->member_contact04 = "checked";	// [적법수집확인서] 고객 연락 방법 - E-MAIL
		if($rs->member_contact=="05") $rs->member_contact05 = "checked";	// [적법수집확인서] 고객 연락 방법 - 우편물
		if($rs->member_contact=="06") $rs->member_contact06 = "checked";	// [적법수집확인서] 고객 연락 방법 - 기타(메모 입력)
		if(!isset($rs->agent_route_memo)) $rs->agent_route_memo = "";
		if(!isset($rs->member_route_memo)) $rs->member_route_memo = "";
		if(!isset($rs->member_contact_memo)) $rs->member_contact_memo = "";
		
		return $rs;
	}

	/*
	*	대출접수번호로 제휴사 이력제 정보 가져오기
	*/
	public static function getAgentLevel($no)
	{
		$rs = DB::table('AGENT_LEVEL')
		->select('agent_level', 'agent_app_date', 'agent_name', 'agent_ssn', 'agent_assn', 'agent_ph')
		->where('save_status','Y')->where('loan_app_no',$no)->orderBy('agent_level')->get();
		$data = array();
		foreach($rs as $v)
		{
			$data['agent_app_date'.$v->agent_level] = Func::dateFormat($v->agent_app_date);
			$data['agent_name'.$v->agent_level] = $v->agent_name;
			$data['agent_ssn'.$v->agent_level] = $v->agent_ssn;
			$data['agent_assn'.$v->agent_level] = $v->agent_assn;
			$data['agent_ph'.$v->agent_level] = $v->agent_ph;			
		}
		return (object)$data;
	}

	/*
	*   계약번호로 LOAN_INFO_GUARANTOR(보증인 정보) 가져오기 [20230420 작업자 이세형]
	*/
	public static function getLoanInfoGuarantor($no)
	{
		$array_conf = Func::getConfigArr();

		$g_rs = DB::TABLE('loan_info_guarantor')->SELECT('no', 'name', 'ssn')
												->WHERE('save_status', 'Y')
												->WHERE('status', 'Y')
												->WHERE('loan_info_no', $no)
												->ORDERBY('no','desc')
												->FIRST();

		$g_rs = Func::chungDec(["loan_info_guarantor"], $g_rs);	// CHUNG DATABASE DECRYPT
		if(!empty($g_rs))
		{
			$g_rs->ssn 	    = substr($g_rs->ssn, 0, 6)."-".substr($g_rs->ssn, 6, 7);			//[보증인주민번호]
			$g_rs->mask_ssn = substr($g_rs->ssn, 0, 6)."-".substr($g_rs->ssn, 7, 1)."******";	//[보증인주민번호마스킹]
		}
		else
		{
			$g_rs = [];
		}

		return $g_rs;
	}

	/*
	*   계약번호로 USERS(담당자 정보) 가져오기 [20230421 작업자 이세형]
	*/
	public static function getUsers($no, $post_cd='', $loan_info_law_no='')
	{
		
		$getId = DB::table('loan_info') ->select('manager_id')-> where('no', $no)->FIRST();

		$result = DB::table('USERS')->select('id', 'name', 'user_rank_cd', 'ssn11', 'ssn12') // 담당자 아이디, 이름, 직급(번호), 담당자 주민번호앞, 담당자 주민번호뒤
									->where('id', $getId->manager_id)
									->first();

		$result = Func::chungDec(["USERS"], $result);	// CHUNG DATABASE DECRYPT
		 
		if(isset($result->ssn11)&& isset($result->ssn12)) // 등록된 직원주민번호가 있을때
		{
			$result->ssn = substr($result->ssn11, 0, 6)."-".substr($result->ssn12, 0, 1)."******";	// 직원주민번호마스킹하여 처리
			$result->name = PaperPrint::strReplace('name', $post_cd, $loan_info_law_no);
		}
		else
		{
			$result = [];
		}

		return $result;
	}

	/*
	*   계약번호와 담당자 정보(user_rank_cd)로 직원직급 가져오기 [20230424 작업자 이세형]
	*/
	public static function getUserRankCd($no)
	{
		$user_rank_cd = PaperPrint::getUsers($no);

		if(!empty($user_rank_cd->user_rank_cd))
		{
			$result = DB::table('CONF_CODE')
										->select('name')
										->where('cat_code','user_rank_cd')
										->where('code', $user_rank_cd->user_rank_cd)
										->first();
		}
		else
		{
			$result = [];
		}
		return $result;
	}

	/*
	*   계약번호로 CUST_INFO_EXTRA(고객 정보) 가져오기 [20230421 작업자 이세형]
	*/
	public static function getCustInfoExtra($no)
	{
		$result = DB::table('CUST_INFO_EXTRA')
		->select('*')
        ->whereIn('cust_info_no', function ($getCustInfoNo) use ($no) {
            $getCustInfoNo->select('cust_info_no')
            ->from('loan_info')
            ->where('no', $no);
         })
        ->first();
		$result = Func::chungDec(["CUST_INFO_EXTRA"], $result);	// CHUNG DATABASE DECRYPT
		return $result;
	}

	/*
	*   부서정보(팩스번호 등) 가져오기
	*/
	public static function getBranch()
	{
		$result = DB::table('BRANCH')
		->select('*')
		->first();
		return $result;
	}

	/*
	*  loan_irl 화해
	*/
	public static function getLoanIrl($no)
	{
		$law_justice= Func::getConfigArr('court_cd');
		$result = DB::table('loan_irl')
		->select('*')
        ->whereIn('cust_info_no', function ($getCustInfoNo) use ($no) {
            $getCustInfoNo->select('cust_info_no')
            ->from('loan_info')
            ->where('no', $no);
         })
		 ->first();
		if(isset($result)){
			$result->law_justice = $law_justice[$result->law_justice] ?? '';
		}
		return $result;
	}

	/*
	*  loan_trade_xx
	*/
	public static function getLoanLastTrade($no)
	{
		$result = PaperPrint::getLoanInfo($no);
		
		// 완제
		if($result->balance==0 && $result->status=="E")
		{
			$complete = DB::TABLE('loan_info_trade')->SELECT('balance, trade_date, trade_money, return_origin')
						->WHERE('save_status', 'Y')->WHERE('balance', '0')->WHERE('return_origin', '>', '0')->WHERE('loan_info_no', $no)
						->ORDERBY('trade_date', 'desc')->FIRST();


			$result->complete_date   	= Func::dateFormat($complete->trade_date, 'kor');    	// [완납일]
			$result->complete_money  	= Func::numberFormat($complete->trade_money);			// [완납거래금]
			$result->complete_money_kor = Func::numberToKor($complete->trade_money);			// [완납거래금한글]
		}

		return $result;
	}

	/*
	*	계약번호로 가상계좌명 가져오기
	*/
	public static function getVirtualAccount($no)
	{
		// 담보여부 체크
		if($no)
		{	
			$dv = DB::TABLE('borrow')->WHERE('no', '4')->WHERE('status', 'Y')->WHERE('loan_info_no', $no)->COUNT();
		}

		
		$cno  	 = DB::TABLE('loan_info')->SELECT('cust_info_no')->WHERE('no', $no)->FIRST();
		$virtual = DB::TABLE('vir_acct')->SELECT('vir_acct_ssn')->WHERE('cust_info_no', $cno->cust_info_no)->ORDERBY('no', 'desc')->FIRST();
		$virtual = Func::chungDec(["VIR_ACCT"], $virtual);

		if($dv > 0)
		{
			$vir_acct_ssn_nm = "상호저축 ";
		}
		else
		{
			$dv2 = DB::TABLE('loan_info')->WHERE('convert_flag', 'like', 'lead%')->WHERE('save_status', 'Y' )->WHERE('no', $no)->COUNT();
			if($dv2 > 0)
			{
				if($virtual)
				{
					$vir_acct_ssn_nm = "신한 ";
				}
				else
				{
					$vir_acct_ssn_nm = "우리 ";
				}
			}
			else
			{
				$vir_acct_ssn_nm = "우리 ";
			}
		}

		if($virtual)
		{
			$vir_acct_ssn = $virtual->vir_acct_ssn;
		}
		else
		{
			$vir_acct_ssn = '';
		}

		$vir['vir_acct_ssn'] = $vir_acct_ssn_nm.$vir_acct_ssn;
		
		return $vir;
	}

	/*
	*	계약번호로 연체일수 가져오기
	*/
	public static function getDelayInfo($no)
	{
		// $now = substr(\Carbon\Carbon::now(), 0, 10);
		$delay = DB::TABLE('loan_info')->SELECT('delay_term, balance')->WHERE('no', $no)->GET();
		
		if(!empty($delay[0]))
		{
			$delay_sdate = date('Y-m-d', strtotime('+'.$delay[0]->delay_term.' days'));
			
			$delay[0]->delay_sdate  	  = Func::dateFormat($delay_sdate);
			$delay[0]->delay_sdate2 	  = Func::dateFormat($delay_sdate, '/');
			$delay[0]->credit_order 	  = Func::dateFormat(date('Y-m-d', strtotime($delay_sdate."+90 day")), '/');
			$delay[0]->credit_order_money = Func::numberFormat(substr($delay[0]->balance, 0 , strlen($delay[0]->balance) - 3));
		}

		return $delay[0];
	}

	/*
	*	계약번호로 투자내역 가져오기
	*/
	public static function getInvList($loan_info_no, $no, $params, $post_cd)
	{
		$inv = DB::TABLE('LOAN_INFO as i')->JOIN('loan_usr_info as u','i.loan_usr_info_no','=','u.no')
										->WHERE(['i.no'=>$loan_info_no,'i.save_status'=>'Y','u.save_status'=>'Y'])
										->FIRST();
		
		if(!empty($inv))
		{
			Func::chungDec(['LOAN_INFO','loan_usr_info'],$inv);
			$inv->term = Round(Func::dateTerm($inv->trade_date, $inv->contract_end_date)/30);
			foreach($inv as $col => $val)
			{
				if(strstr($col,'ph') && strlen($col)>3)
				{
					if(!empty($params['masking']) && $params['masking'] == 'Y')
					{
						// 가운데 전화번호 * 처리
						if($col == 'ph12')
						{
							$val = '****';
						}
					}

					$ph_div = substr($col,'2','1');
					$ph_nm = 'ph'.$ph_div;
					$inv->$ph_nm = isset($inv->$ph_nm) ? $inv->$ph_nm.'-'.$val : $val;
				}
				if(strstr($col,'addr') && strlen($col)>5)
				{
					$addr_div = substr($col,'4','1');
					$addr_nm = 'addr'.$addr_div;

					if($post_cd=="SM003")
					{
						$inv->$addr_nm = isset($inv->$addr_nm) ? $inv->$addr_nm.'<br>&nbsp;&nbsp;&nbsp;'.$val : $val;
					}
					else
					{
						$inv->$addr_nm = isset($inv->$addr_nm) ? $inv->$addr_nm.' '.$val : $val;
					}
				}
				if($col == 'ratio')
				{
					$inv->delay_ratio = $val+3;
				}
				if(strstr($col,'money') && !strstr($col,'_han'))
				{
					$han = $col.'_han';
					$inv->$han = PaperPrint::getHanMoney($val).'원';
					$inv->$col = number_format($val ?? 0);
				}
				if(strstr($col,'_date') && (strlen($inv->$col) == 8))
				{
					$col_y = $col.'_y';
					$col_md = $col.'_md';
					$inv->$col_y = date('Y',strtotime($inv->$col));
					$inv->$col_md = preg_replace('/(0)(\d)/','$2', date('m월 d일',strtotime($inv->$col)));
					$inv->$col = date('Y년',strtotime($inv->$col))." ".preg_replace('/(0)(\d)/','$2', date('m월 d일',strtotime($inv->$col)));
				}
				if($col == 'ssn')
				{
					if(!empty($inv->com_ssn))
					{
						$inv->com_ssn = str_replace('-', '', $inv->com_ssn);
						if(!empty($params['masking']) && $params['masking'] == 'Y')
						{
							$inv->$col = substr($inv->com_ssn, 0, 3)."-".substr($inv->com_ssn, 3, 2)."-*****";
						}
						else
						{
							$inv->$col = substr($inv->com_ssn, 0, 3)."-".substr($inv->com_ssn, 3, 2)."-".substr($inv->com_ssn, 5, 5);
						}	
					}
					else
					{
						if(!empty($params['masking']) && $params['masking'] == 'Y')
						{
							$inv->$col = Func::ssnFormat($val,'Y');
						}
						else
						{
							$inv->$col = Func::ssnFormat($val,'A');
						}
						
					}
				}
			}
		}
		// log::debug('inv - '.print_R($inv,1));
		return $inv;
	}

	/**
	 * 금액 한글표기
	 *
	 * @param  Integer
	 * @return array 
	*/
	public static function getHanMoney($money)
	{
		if(!isset($money) || empty($money))
		{
			return 0;
		}
		if(!is_numeric($money))
		{
			$money = preg_replace('/[^0-9]/s','',$money);
		}

		$numb  = ['','일','이','삼','사','오','육','칠','팔','구'];
		$unit1 = ['','십','백','천'];
		$unit4 = ['','만','억','조','경'];

		$split4 = str_split(strrev((string)$money), 4);

		$res = [];
		for($i=0; $i<count($split4); $i++)
		{
			$tmp = [];
			$split1 = str_split((string)$split4[$i], 1);
			for($j=0; $j<count($split1); $j++)
			{
				$v = (int)$split1[$j];
				if($v > 0)
				{
					$tmp[] = $numb[$v].$unit1[$j];
				}
			}
			if(count($tmp) > 0)
			{
				$res[] = implode('',array_reverse($tmp)).$unit4[$i];
			}
		}
		return implode('',array_reverse($res));
	}

	/*
	*	계약번호로 투자자 정보 가져오기
	*/
	public static function getLoanUsrInfo($loan_info_no, $params, $post_cd)
	{
		$inv = DB::TABLE('LOAN_USR_INFO')->WHERE(['no'=>$loan_info_no,'save_status'=>'Y'])->FIRST();
		
		if(!empty($inv))
		{
			Func::chungDec(['LOAN_USR_INFO'],$inv);
			//$inv->term = Round(Func::dateTerm($inv->trade_date, $inv->contract_end_date)/30);
			foreach($inv as $col => $val)
			{
				if(strstr($col,'ph') && strlen($col)>3)
				{
					if(!empty($params['masking']) && $params['masking'] == 'Y')
					{
						// 가운데 전화번호 * 처리
						if($col == 'ph12')
						{
							$val = '****';
						}
					}

					$ph_div = substr($col,'2','1');
					$ph_nm = 'ph'.$ph_div;
					$inv->$ph_nm = isset($inv->$ph_nm) ? $inv->$ph_nm.'-'.$val : $val;
				}
				if(strstr($col,'addr') && strlen($col)>5)
				{
					$addr_div = substr($col,'4','1');
					$addr_nm = 'addr'.$addr_div;

					if($post_cd=="SM003")
					{
						$inv->$addr_nm = isset($inv->$addr_nm) ? $inv->$addr_nm.'<br>&nbsp;&nbsp;&nbsp;'.$val : $val;
					}
					else
					{
						$inv->$addr_nm = isset($inv->$addr_nm) ? $inv->$addr_nm.' '.$val : $val;
					}
				}
				if($col == 'ratio')
				{
					$inv->delay_ratio = $val+3;
				}
				if(strstr($col,'money') && !strstr($col,'_han'))
				{
					$han = $col.'_han';
					$inv->$han = PaperPrint::getHanMoney($val).'원';
					$inv->$col = number_format($val ?? 0);
				}
				if(strstr($col,'_date') && (strlen($inv->$col) == 8))
				{
					$col_y = $col.'_y';
					$col_md = $col.'_md';
					$inv->$col_y = date('Y',strtotime($inv->$col));
					$inv->$col_md = preg_replace('/(0)(\d)/','$2', date('m월 d일',strtotime($inv->$col)));
					$inv->$col = date('Y 년',strtotime($inv->$col))." ".preg_replace('/(0)(\d)/','$2', date('m월 d일',strtotime($inv->$col)));
				}
				if($col == 'ssn')
				{
					if(!empty($inv->com_ssn))
					{
						$inv->com_ssn = str_replace('-', '', $inv->com_ssn);
						if(!empty($params['masking']) && $params['masking'] == 'Y')
						{
							$inv->$col = substr($inv->com_ssn, 0, 3)."-".substr($inv->com_ssn, 3, 2)."-*****";
						}
						else
						{
							$inv->$col = substr($inv->com_ssn, 0, 3)."-".substr($inv->com_ssn, 3, 2)."-".substr($inv->com_ssn, 5, 5);
						}	
					}
					else
					{
						if(!empty($params['masking']) && $params['masking'] == 'Y')
						{
							$inv->$col = Func::ssnFormat($val,'Y');
						}
						else
						{
							$inv->$col = Func::ssnFormat($val,'A');
						}
						
					}
				}
			}
		}
		// log::debug('inv - '.print_R($inv,1));
		return $inv;
	}
}


?>