<?php
namespace App\Chung;

//use App\Models\User;
//use DBD;
//use Storage;
use DB;
use Auth;
use Log;
use Sum;
use Cache;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
//use Illuminate\Pagination\Paginator;

class Invest
{
    public $holiday  = Array();
    public $investInfo, $moneyInfo, $ratioInfo, $feeInfo;
    public $no;

    public function __construct($param)
	{
        // 휴일
		$this->holiday = Cache::remember('Loan_Holiday', 86400, function()
		{
			$rslt = DB::table("day_conf")->select("*")->get();
			foreach( $rslt as $v )
			{
				$day           = str_replace("-","",$v->day);
				$holiday[$day] = $day;
			}
			return $holiday;
		});
		
        if( is_numeric($param) )
		{
			$no = $param;
			$this->no = $no;

			$rslt = $this->setInvestInfo($no);
			
			if( $rslt==false )
			{
				$this->clearInvestInfo();
			}

			return $rslt;
        }
        // 파라미터에 의한 값 셋팅
		else if( is_array($param) )
		{
			// 필수값
			if( !$param['loan_money'] || !$param['contract_date'] || !$param['contract_end_date'] || !$param['contract_day'] || $param['invest_rate']=='' || $param['income_rate']=='' || $param['local_rate']=='' || $param['platform_fee_rate']=='' )
			{
				Log::debug("Argument Error : [".$param['loan_money']."][".$param['contract_date']."][".$param['contract_end_date']."][".$param['contract_day']."][".$param['invest_rate']."][".$param['income_rate']."][".$param['local_rate']."][".$param['platform_fee_rate']."]");
				return false;
			}
            
			$this->investInfo = $param;
			$this->ratioInfo = isset($param['arrayRatio']) ? $param['arrayRatio'] : Array( $param['contract_date'] => Array( "req_date" => $param['contract_date'], "req_value" => $param['invest_rate'] ) );
			$this->feeInfo = isset($param['arrayPlatformFeeRate']) ? $param['arrayPlatformFeeRate'] : Array( $param['contract_date'] => Array( "req_date" => $param['contract_date'], "req_value" => $param['platform_fee_rate'] ) );
            $this->moneyInfo = isset($param['arrayMoneyInfo']) ? $param['arrayMoneyInfo'] : Array( $param['contract_date'] => Array( "req_date" => $param['contract_date'], "req_value" => $param['loan_money'] ) );
		}
		else
		{
			return false;
		}
    }

    /**
	* 투자일련번호에 의한 기본정보 셋팅 - 투자번호,수익률,수수료율
	*
	* @param  $no 
	* @return boolean
	*/
	private function setInvestInfo($no)
	{
		$this->clearInvestInfo();
		$this->no = $no;

		// LOAN
		$rslt = DB::table("loan_info")->select("*")->where('no',$no)->where('save_status','Y')->first();
		$rslt = Func::chungDec(["loan_info"], $rslt);	// CHUNG DATABASE DECRYPT
		if( !$rslt )
		{
			log::Debug("LOAN(".$no.") 생성에러 : ".$no." = 투자계약을 찾을 수 없습니다.");
			return false;
		}
		
		// 필수값
		if( !$rslt->contract_date || !$rslt->contract_end_date || !$rslt->contract_day || $rslt->invest_rate==''  || $rslt->income_rate==''  || $rslt->local_rate==''  || $rslt->platform_fee_rate=='' )
		{
			log::Debug("LOAN(".$no.") 생성에러 : 필수정보 부족 #1");
			log::Debug("CONTRACT_DATE       = ".$rslt->contract_date);
			log::Debug("CONTRACT_END_DATE   = ".$rslt->contract_end_date);
			log::Debug("CONTRACT_DAY        = ".$rslt->contract_day);
			log::Debug("INVEST_RATE         = ".$rslt->invest_rate);
			log::Debug("INCOME_RATE         = ".$rslt->income_rate);
			log::Debug("LOCAL_RATE          = ".$rslt->local_rate);
            log::Debug("PLATFORM_FEE_RATE   = ".$rslt->platform_fee_rate);

			return false;
		}

		$this->investInfo = (Array) $rslt;
		$this->investInfo['invest_rate']       = (float) $this->investInfo['invest_rate'];
		$this->investInfo['income_rate']       = (float) $this->investInfo['income_rate'];
		$this->investInfo['local_rate']        = (float) $this->investInfo['local_rate'];
		$this->investInfo['platform_fee_rate'] = (float) $this->investInfo['platform_fee_rate'];

		// RATE
		$rslt = DB::TABLE("loan_info_invest_rate")->SELECT("rate_date, invest_rate")->WHERE('loan_info_no',$no)->WHERE('SAVE_STATUS','Y')->ORDERBY('RATE_DATE')->ORDERBY('SAVE_TIME')->GET();
		$rslt = Func::chungDec(["loan_info_invest_rate"], $rslt);	// CHUNG DATABASE DECRYPT

		foreach( $rslt as $v )
		{
			$this->ratioInfo[$v->rate_date]['req_date'] = $v->rate_date;
			$this->ratioInfo[$v->rate_date]['req_value'] = (float) $v->invest_rate;
		}
		if( sizeof($this->ratioInfo)==0 )
		{
			log::Debug("Invest(".$no.") 생성에러 : ".$no." = 수익률정보를 찾을 수 없습니다.");
			return false;
		}
		
		$this->feeInfo[$this->investInfo['contract_date']] = Array( "req_date" => $this->investInfo['contract_date'], "req_value" => $this->investInfo['platform_fee_rate'] );
		$this->moneyInfo[$this->investInfo['contract_date']] = Array( "req_date" => $this->investInfo['contract_date'], "req_value" => $this->investInfo['loan_money'] );

		return true;
	}


	/**
	* 계약 기본정보 초기화 - 계약정보, 금리, 약정일
	*/
	private function clearInvestInfo()
	{
		$this->no = NULL;
		$this->investInfo = Array();
		$this->ratioInfo = Array();
		$this->feeInfo = Array();
	}

    /**
	* 스케줄저장 - DB에 저장
	*
	* @param  Array - 상환스케줄
	* @return 
	*/
	public function savePlan($plans)
	{
		$no = $this->investInfo['no'];

		$save_id   = 'SYSTEM';
		$save_time = date("YmdHis");

		$_DEL_PLAN['save_status']      = "N";
		$_DEL_PLAN['del_time']         = $save_time;
		$_DEL_PLAN['del_id']           = $save_id;

		$_DEL_WHERE['save_status']     = "Y";
		$_DEL_WHERE['loan_info_no']    = $no;
		$_DEL_WHERE['divide_flag']     = 'N';

		$rslt = DB::dataProcess("UPD", "loan_info_return_plan", $_DEL_PLAN, $_DEL_WHERE);
		if( $rslt !="Y" )
		{
			return "스케줄등록오류";
		}
		
		if( is_array($plans) && sizeof($plans)>0 )
		{
			foreach( $plans as $v )
			{
				if( $v['plan_money']==0 )
				{
					continue;
				}

				$v['loan_info_no']   = $no;
				$v['loan_usr_info_no'] = $this->investInfo['loan_usr_info_no'];
				$v['inv_seq']        = $this->investInfo['inv_seq'];
				$v['investor_no']    = $this->investInfo['investor_no'];

				$v['handle_code']    = $this->investInfo['handle_code'];
				$v['pro_cd']         = $this->investInfo['pro_cd'];

				$v['cust_bank_ssn']  = $this->investInfo['cust_bank_ssn'];

				$v['loan_bank_cd']   = $this->investInfo['loan_bank_cd'];
				$v['loan_bank_ssn']  = $this->investInfo['loan_bank_ssn'];
				$v['loan_bank_name'] = $v['loan_bank_owner'] = $this->investInfo['loan_bank_name'];

				$v['save_time']      = $save_time;
				$v['save_id']        = $save_id;
				$v['save_status']    = "Y";
				
				$rslt = DB::dataProcess("INS", "loan_info_return_plan", $v);
				if( $rslt!="Y" )
				{
					return "스케줄등록오류";
				}
			}
		}

		$rs = DB::table('loan_info_return_plan')->select(DB::RAW('coalesce(sum(plan_interest),0) as sum_interest, coalesce(sum(withholding_tax),0) as sum_withholding_tax, coalesce(sum(income_tax),0) as sum_income_tax, coalesce(sum(local_tax),0) as sum_local_tax'))->where('loan_info_no',$no)->where('save_status','Y')->first();
        $rslt = DB::dataProcess("UPD", "loan_info", ['sum_interest'=>$rs->sum_interest,'sum_withholding_tax'=>$rs->sum_withholding_tax,'sum_income_tax'=>$rs->sum_income_tax,'sum_local_tax'=>$rs->sum_local_tax], ['no'=>$no]);
		if( $rslt!="Y" )
		{
			return "투자정보업데이트오류";
		}
		
		return $rslt;
	}
	
	// /**
	// * 스케줄저장 - DB에 저장
	// *
	// * @param  Array - 상환스케줄
	// * @return 
	// */
	// public function savePlan($plans, $loan_info_trade_no=0)
	// {
	// 	$no = $this->loanInfo['no'];
		
	// 	if( $loan_info_trade_no>0 )
	// 	{
	// 		$rslt = DB::TABLE("LOAN_INFO_TRADE")->SELECT("*")->WHERE("NO", $loan_info_trade_no)->FIRST();
	// 		$rslt = Func::chungDec(["LOAN_INFO_TRADE"], $rslt);	// CHUNG DATABASE DECRYPT
	// 		$vt = (Array) $rslt;
	// 		$save_time = $vt['save_time'];
	// 	}
	// 	else
	// 	{
	// 		$save_time = date("YmdHis");
	// 	}

	// 	if( is_array($plans) && sizeof($plans)>0 )
	// 	{
	// 		$first_plan_date = min(array_keys($plans));

	// 		// 그냥 다 지운다.
	// 		$rslt = DB::dataProcess("DEL", "LOAN_INFO_PLAN", Array(), [['loan_info_no','=',$no]]);
	// 		if( $rslt!="Y" )
	// 		{
	// 			return "스케줄등록오류";
	// 		}

	// 		// 해당 save_time으로 로그에 기록된 이력이 있으면 save_time+1 하자.. 대출생성시 이슈 발생
	// 		$logCnt = DB::TABLE('LOAN_INFO_PLAN_LOG')->WHERE('LOAN_INFO_NO',$no)->WHERE('SAVE_TIME',$save_time)->COUNT();
	// 		if($logCnt > 0)
	// 		{
	// 			$save_time = date("YmdHis", strtotime($save_time."+1 seconds"));
	// 		}
			
	// 		foreach( $plans as $v )
	// 		{
	// 			if( $v['plan_money']==0 )
	// 			{
	// 				continue;
	// 			}

	// 			$v['loan_info_no'] = $no;
	// 			$v['save_time']    = $save_time;
	// 			$v['save_id']      = "SYSTEM";

	// 			$v['loan_info_trade_no'] = $loan_info_trade_no;
			
	// 			$rslt = DB::dataProcess("INS", "LOAN_INFO_PLAN", $v);
	// 			if( $rslt!="Y" )
	// 			{
	// 				return "스케줄등록오류";
	// 			}
	// 			// 로그에도 넣어주자
	// 			$rslt = DB::dataProcess("INS", "LOAN_INFO_PLAN_LOG", $v);
	// 			if( $rslt!="Y" )
	// 			{
	// 				return "스케줄등록오류";
	// 			}
	// 		}
	// 	}

	// 	return "Y";
	// }

    /**
	* 스케줄생성 ( 미래금리의 변동은 고려하지 않음 / 현재기준 금리로 스케줄생성 ) - 전제 = take_date|loan_date, rateInfo, cdayInfo, balance, contract_end_date
	*
	* @param  Date  - 기준일 (스케줄 생성일자, 첫회차 아님, 기본값은 대출일 또는 이수일)
	* @param  Int   - 월상환금액 (기본값은 PMT를 통하여 계산)
	* @return Array - 상환스케줄
	*/
	public function buildPlanData($today, $lastday)
	{
		$array_plan = $this->getPlanDateArray($today, $lastday);

		if(sizeof($array_plan) > 0)
		{
			$currSeq     = 1;
			foreach( $array_plan as $rd => $v )
			{
				// 기준일 이전이면 PASS - 무조건 계약일부터 뽑는형태로 변경하면서 추가됨.
				// 투자개시일 당일 스케줄은 제외 - 지급하지 않는다.
				if($rd < $today || $rd == $this->investInfo['contract_date'])
				{
					unset($array_plan[$rd]);
					if($rd!=$this->investInfo['contract_date']) $currSeq++;
					continue;
				}

				if($this->investInfo['return_method_cd'] == 'M')
				{
					if(substr($v['plan_interest_sdate'],0,6) == substr($v['plan_interest_edate'],0,6))
					{
						unset($array_plan[$rd]);
						continue;
					}
					
					// 이자 정보
					unset($interest_info);
					$interest_info = $this->getMonthInterest( Array("sdate"=>$v['plan_interest_sdate'],"edate"=>$v['plan_interest_edate']) );
					$v['plan_interest'] = $interest_info['interest'];
				}
				else
				{
					// 이자 정보
					unset($interest_info);
					$interest_info = $this->getDayInterest( Array("sdate"=>$v['plan_interest_sdate'],"edate"=>$v['plan_interest_edate']) );
					$v['plan_interest'] = $interest_info['interest'];
				}

				$v['seq'] 			= $currSeq;
				$v['loan_money']    = $this->investInfo['loan_money'];
				$v['plan_balance']  = $interest_info['balance'];

				if($this->investInfo['tax_free'] == "Y")
				{
					$v['income_tax'] = 0;													 // 소득세 : 면세자는 제외
					$v['local_tax']  = 0;												     // 지방소득세 : 면세자는 제외
				}
				else
				{
					// - 이자 = Math.round(발행금액 * 금리 / 100 / 12 / 10) * 10
					// - 소득세 = Math.floor(이자 * (소득세율 / 100) / 10) * 10
					// - 지방소득세 = Math.floor(소득세 * (지방소득세율 / 100) / 10) * 10
					
					$v['income_tax']     = floor( $v['plan_interest'] * ($this->investInfo['income_rate'] / 100) / 10 ) * 10;	        // 소득세 : TRUNC(세전이자금액*원천징수세율/100/10,0)*10
					$v['local_tax']      = floor( $v['income_tax'] * ($this->investInfo['local_rate'] / 100) / 10 ) * 10;				// 지방소득세 : TRUNC( 소득세*0.01,0)*10
				}

				$v['plan_interest_term']  	= $interest_info['interest_term'];

				$v['plan_interest_sdate']	= $interest_info['interest_sdate'];
				$v['plan_interest_edate'] 	= $interest_info['interest_edate'];

				$v['withholding_tax']    	= $v['income_tax'] + $v['local_tax'];			      // 원천징수 : 소득세+지방소득세

				$v['plan_interest_real'] 	= floor(($v['plan_interest'] - $v['withholding_tax'])/10)*10;

				$invest_rate             	= $this->getCurrRate('ratioInfo', $rd)['req_value'];  // 수익률
				$v['invest_rate']        	= $invest_rate;

				$fee_ratio               	= $this->getCurrRate('feeInfo', $rd)['req_value'];    // 플랫폼 수수료
				$v['platform_fee_rate']  	= $fee_ratio;
				$v['platform_fee']       	= $interest_info['platform_fee'];
				
				$v['income_rate']	 	 	= $this->investInfo['income_rate'];
				$v['local_rate']	 	 	= $this->investInfo['local_rate'];

				$v['plan_origin']        	= $interest_info['origin'];

				$v['plan_money']  	     	= $v['plan_origin'] + $v['plan_interest_real'];
				
				$array_plan[$rd] = $v;
				$currSeq++;
			}
		}

		return $array_plan;
	}
	
	/**
	* 스케줄재생성 ( 미래금리의 변동은 고려하지 않음 / 현재기준 금리로 스케줄생성 ) - 전제 = take_date|loan_date, rateInfo, cdayInfo, balance, contract_end_date
	*
	* @param  Date  - 기준일 (스케줄 생성일자, 첫회차 아님, 기본값은 대출일 또는 이수일)
	* @param  Int   - 월상환금액 (기본값은 PMT를 통하여 계산)
	* @return Array - 상환스케줄
	*/
	public function reBuildPlanData($today, $lastday, $tradeMoney, $returnInterest)
	{
		$array_plan = $this->getPlanDateArray($today, $lastday);

		if(sizeof($array_plan) > 0)
		{
			$currSeq = 1;
			$maxSeq  = sizeof($array_plan);

			foreach( $array_plan as $rd => $v )
			{
				// 기준일 이전이면 PASS - 무조건 계약일부터 뽑는형태로 변경하면서 추가됨.
				// 투자개시일 당일 스케줄은 제외 - 지급하지 않는다.
				if($rd < $today || $rd == $this->investInfo['contract_date'])
				{
					unset($array_plan[$rd]);
					if($rd!=$this->investInfo['contract_date']) $currSeq++;
					continue;
				}
				
				// 마지막 회차만 수정하는 방향으로.
				if($currSeq < $maxSeq)
				{
					$v['plan_money'] = 0;

					$array_plan[$rd] = $v;
					$currSeq++;

					continue;
				}

				// 이자 정보
				unset($interest_info);
				$interest_info 		= $this->getDivideInterest(Array("sdate"=>$today,"edate"=>$lastday));

				$v['seq'] 			= $currSeq;
				$v['loan_money']    = $this->investInfo['loan_money'];

				$v['plan_balance']  = 0;
				$v['plan_interest'] = $interest_info['interest'] - $returnInterest;

				if($this->investInfo['tax_free'] == "Y")
				{
					$v['income_tax'] = 0;													 // 소득세 : 면세자는 제외
					$v['local_tax']  = 0;												     // 지방소득세 : 면세자는 제외
				}
				else
				{
					// - 이자 = Math.floor(발행금액 * 금리 / 100 / 12 / 10) * 10
					// - 소득세 = Math.ceil(이자 * (소득세율 / 100) / 10) * 10
					// - 지방소득세 = Math.floor(소득세 * (지방소득세율 / 100) / 10) * 10
					
					$v['income_tax'] = ceil( $v['plan_interest'] * ($this->investInfo['income_rate'] / 100) / 10 ) * 10;	// 소득세 : TRUNC(세전이자금액*원천징수세율/100/10,0)*10
					$v['local_tax']  = floor( $v['income_tax'] * ($this->investInfo['local_rate'] / 100) / 10 ) * 10;		// 지방소득세 : TRUNC( 소득세*0.01,0)*10
				}

				$v['plan_interest_term']  	= $interest_info['interest_term'];

				$v['plan_interest_sdate']	= $interest_info['interest_sdate'];
				$v['plan_interest_edate'] 	= $interest_info['interest_edate'];

				$v['withholding_tax']       = $v['income_tax'] + $v['local_tax'];			      // 원천징수 : 소득세+지방소득세

				$v['plan_interest_real']    = $v['plan_interest'] - $v['withholding_tax'];

				$invest_rate                = $this->getCurrRate('ratioInfo', $rd)['req_value'];  // 수익률
				$v['invest_rate']           = $invest_rate;

				$fee_ratio               	= $this->getCurrRate('feeInfo', $rd)['req_value'];    // 플랫폼 수수료
				$v['platform_fee_rate']  	= $fee_ratio;
				$v['platform_fee']       	= $interest_info['platform_fee'];
				
				$v['income_rate']	 	 	= $this->investInfo['income_rate'];
				$v['local_rate']	 	 	= $this->investInfo['local_rate'];

				$v['plan_origin']        	= $tradeMoney;

				$v['plan_money']  	     	= $v['plan_origin'] + $v['plan_interest_real'];
				
				$array_plan[$rd] = $v;
				$currSeq++;
			}
		}

		return $array_plan;
	}

    /**
	* 상환일 스케줄 생성 ( 상환일 회차만 응답 )
	*
	* @param  Date   - 기준일 (스케줄 생성일자, 첫회차 아님 - 대출일 또는 이수일)
	* @return Array  - 스케줄배열 (seq, plan_date)
	*/
	private function getPlanDateArray($sdate, $lastday)
	{
		// 이자상환주기 설정
		if(empty($this->investInfo['pay_term'])) $pay_term = 1;
		else $pay_term = $this->investInfo['pay_term'];
		
		$arrayPlan  = $arrayRealPlan = [];

		$cnt = 1;
		$start_date = max($sdate, $this->investInfo['contract_date']);
		$end_date   = min($lastday, $this->investInfo['contract_end_date']);
		
		// 기본 상환주기를 통한 납입스케줄 설정
		for( $d = $this->getNextReturnDate($start_date, $end_date); $d<=$end_date; $d = $this->getNextReturnDate($d, $end_date) )
		{
			// 상환주기회차만큼 돌지않으면 PASS, 1개월주기가 아니라 사모사채의 경우 2개월 이상 주기(pay_term)로 스케줄 잡는경우가 있다.
			if($pay_term > 1 && $cnt < $pay_term)
			{
				$cnt++;
				continue;
			}
            
			$arrayPlan[$d][] = "SCHEDULE";

            // 스케줄이 종결일에 도래하면 종료
            if($d>=$end_date) break;

			$cnt = 1;
		}

		// 지급처리된 스케줄도 포함 - 수기처리된 스케줄도 반영하기 위함
		$returnedPlan = [];
		$returnedPlan = $this->getReturnPlan($this->no);
		
		if(sizeof($returnedPlan) > 0)
		{
			foreach($returnedPlan as $reqDt => $v)
			{
				$arrayPlan[$reqDt][] = "RETURNEDPLAN";
			}
		}

		ksort($arrayPlan);
		foreach($arrayPlan as $d => $v)
		{
			$arrayRealPlan[$d]['plan_date'] = $d;
			$arrayRealPlan[$d]['plan_date_biz'] = $this->getBizDay($d);
			$arrayRealPlan[$d]['plan_money'] = 0;

			$arrayRealPlan[$d]['withholding_tax'] = 0;
			$arrayRealPlan[$d]['income_tax'] = 0;
            $arrayRealPlan[$d]['local_tax'] = 0;
			$arrayRealPlan[$d]['plan_interest_sdate'] = $start_date;
            $arrayRealPlan[$d]['plan_interest_edate'] = $d;

			$start_date = $d;
		}

		return $arrayRealPlan;
	}

    /**
	* 일수 이자 구하기
	*
	* @param  Array - 계약정보배열 [take_date] [today] [return_date] [balance] / 기생성 getCurrRate, getCurrCday
	* @param  String - 연체계산기준일 - 기본값은 상환일이나 자유상환의 경우는 특이하게 기한이익상실로 계산하는 경우가 있어서 파라미터로 받음
	* @return Array - 계약정보배열에 이자정보 추가 응답
	*/
    public function getDayInterest($val)
	{
		// 정상이자
        $val['interest_term']   = 0;       // 분배일수
		$val['balance']         = 0;       // 잔액
		$val['origin']          = 0;       // 분배원금
		$val['interest']        = 0;       // 분배이자
        $val['withholding_tax'] = 0;       // 원천징수
		$val['income_tax']      = 0;       // 이자소득세
		$val['local_tax']       = 0;       // 주민세
		$val['interest_sdate']  = "";      // 분배시작일
		$val['interest_edate']  = "";      // 분배종료일

		// 이자계산 상세정보 (검증데이터)
		$val['interest_detail_chk']  = Array();

		// 구간 -> 후일산입 산출 -> 선취로 변경. 2023.09.05 노현정 부장 요청
		//$sdate = $this->addDay($val['sdate']);
		$sdate = $val['sdate'];
		$edate = $val['edate'];

		for( $d = $sdate; $d < $edate; $d = $this->addDay($d) )
		{
			$balance = $this->getCurrRate('moneyInfo', $d)['req_value'];    	// 투자금액
			$invest_rate = $this->getCurrRate('ratioInfo', $d)['req_value'];    // 수익률
			$invest_rate = (string) $this->yunRate( $invest_rate, $d );
			$fee_ratio = $this->getCurrRate('feeInfo', $d)['req_value'];    	// 플랫폼 수수료
			$fee_ratio = (string) $this->yunRate( $fee_ratio, $d );
			
			$val['interest_term']++;
			if( !$val['interest_sdate'] )
			{
				$val['interest_sdate'] = $d;
			}
			$val['interest_edate'] = $d;

			if(isset($array_rate_set[$balance][$invest_rate])) $array_rate_set[$balance][$invest_rate]++;
			else $array_rate_set[$balance][$invest_rate] = 1;
			if(isset($array_fee_set[$balance][$fee_ratio])) $array_fee_set[$balance][$fee_ratio]++;
			else $array_fee_set[$balance][$fee_ratio] = 1;
		}

		$val['balance']        = $balance;
		$val['interest']       = $this->getDayInterestTerm($array_rate_set);
		$val['platform_fee']   = $this->getDayInterestTerm($array_fee_set);
        
		return $val;
	}

    /**
	* 월수 이자 구하기
	*
	* @param  Array - 계약정보배열 [take_date] [today] [return_date] [balance] / 기생성 getCurrRate, getCurrCday
	* @param  String - 연체계산기준일 - 기본값은 상환일이나 자유상환의 경우는 특이하게 기한이익상실로 계산하는 경우가 있어서 파라미터로 받음
	* @return Array - 계약정보배열에 이자정보 추가 응답
	*/
    public function getMonthInterest($val)
	{
		// 정상이자
        $val['interest_term']   = 0;       // 분배일수
		$val['balance']         = 0;       // 잔액
		$val['origin']          = 0;       // 분배원금
		$val['interest']        = 0;       // 분배이자
        $val['withholding_tax'] = 0;       // 원천징수
		$val['income_tax']      = 0;       // 이자소득세
		$val['local_tax']       = 0;       // 주민세
		$val['interest_sdate']  = "";      // 분배시작일
		$val['interest_edate']  = "";      // 분배종료일

		// 이자계산 상세정보 (검증데이터)
		$val['interest_detail_chk']  = Array();

		$sdate = $val['sdate'];
		$edate = $val['edate'];

		for( $d = $sdate; substr($d,0,6) < substr($edate,0,6); $d = $this->addMonth($d) )
		{
			$balance = $this->getCurrRate('moneyInfo', $d)['req_value'];    							// 투자금액
			$invest_rate = (string) $this->getCurrRate('ratioInfo', $d)['req_value'];    				// 수익률
			$fee_ratio = (string) $this->getCurrRate('feeInfo', $d)['req_value'];    					// 플랫폼 수수료
			
			$val['interest_term']++;
			if( !$val['interest_sdate'] )
			{
				$val['interest_sdate'] = $d;
			}
			$val['interest_edate'] = $d;

			if(isset($array_rate_set[$balance][$invest_rate])) $array_rate_set[$balance][$invest_rate]++;
			else $array_rate_set[$balance][$invest_rate] = 1;
			if(isset($array_fee_set[$balance][$fee_ratio])) $array_fee_set[$balance][$fee_ratio]++;
			else $array_fee_set[$balance][$fee_ratio] = 1;
		}

		$val['balance']        = $balance;
		$val['interest']       = $this->getMonthInterestTerm($array_rate_set);
		$val['platform_fee']   = $this->getMonthInterestTerm($array_fee_set);
		
		return $val;
	}

    /**
	* 원금상환시 이자 구하기
	*
	* @param  Array - 계약정보배열 [take_date] [today] [return_date] [balance] / 기생성 getCurrRate, getCurrCday
	* @param  String - 연체계산기준일 - 기본값은 상환일이나 자유상환의 경우는 특이하게 기한이익상실로 계산하는 경우가 있어서 파라미터로 받음
	* @return Array - 계약정보배열에 이자정보 추가 응답
	*/
    public function getDivideInterest($val)
	{
		// 정상이자
        $val['interest_term']   = 0;       // 분배일수
		$val['balance']         = 0;       // 잔액
		$val['origin']          = 0;       // 분배원금
		$val['interest']        = 0;       // 분배이자
        $val['withholding_tax'] = 0;       // 원천징수
		$val['income_tax']      = 0;       // 이자소득세
		$val['local_tax']       = 0;       // 주민세
		$val['interest_sdate']  = "";      // 분배시작일
		$val['interest_edate']  = "";      // 분배종료일

		// 이자계산 상세정보 (검증데이터)
		$val['interest_detail_chk']  = Array();

		// 구간 -> 후일산입 산출 -> 선취로 변경. 2023.09.05 노현정 부장 요청
		//$sdate = $this->addDay($val['sdate']);
		$sdate = $val['sdate'];
		$edate = $val['edate'];

		// 투자개시일 당일 종결건 - 일단 당일이자 발생시키자. 발생시키면 안된다고하면 0으로 처리하자.
		if($sdate==$edate && $sdate==$this->investInfo['trade_date'])
		{
			$balance = $this->getCurrRate('moneyInfo', $this->investInfo['trade_date'])['req_value'];	// 투자잔액
			$invest_rate = $this->getCurrRate('ratioInfo', $this->investInfo['trade_date'])['req_value'];     // 수익률
			$invest_rate = (string) $this->yunRate( $invest_rate, $this->investInfo['trade_date'] );
			$fee_ratio = $this->getCurrRate('feeInfo', $this->investInfo['trade_date'])['req_value'];   // 플랫폼 수수료
			$fee_ratio = (string) $this->yunRate( $fee_ratio, $this->investInfo['trade_date'] );
			
			$array_rate_set[$balance][$invest_rate] = 1;
			$array_fee_set[$balance][$fee_ratio] = 1;
			
			$val['balance']		    = $balance;
			$val['interest']		= $this->getDivideInterestTerm($array_rate_set);
			$val['platform_fee']	= $this->getDivideInterestTerm($array_fee_set);
			$val['interest_term']	= 1;
			$val['interest_sdate'] = $val['interest_edate'] = $this->investInfo['trade_date'];
		}
		else
		{
			for( $d = $sdate; $d < $edate; $d = $this->addDay($d) )
			{
				$balance = $this->getCurrRate('moneyInfo', $d)['req_value'];    	// 투자금액
				$invest_rate = $this->getCurrRate('ratioInfo', $d)['req_value'];    // 수익률
				$invest_rate = (string) $this->yunRate( $invest_rate, $d );
				$fee_ratio = $this->getCurrRate('feeInfo', $d)['req_value'];    	// 플랫폼 수수료
				$fee_ratio = (string) $this->yunRate( $fee_ratio, $d );
				
				$val['interest_term']++;
				if( !$val['interest_sdate'] )
				{
					$val['interest_sdate'] = $d;
				}
				$val['interest_edate'] = $d;

				if(isset($array_rate_set[$balance][$invest_rate])) $array_rate_set[$balance][$invest_rate]++;
				else $array_rate_set[$balance][$invest_rate] = 1;
				if(isset($array_fee_set[$balance][$fee_ratio])) $array_fee_set[$balance][$fee_ratio]++;
				else $array_fee_set[$balance][$fee_ratio] = 1;
			}

			$val['balance']        = $balance;
			$val['interest']       = $this->getDivideInterestTerm($array_rate_set);
			$val['platform_fee']   = $this->getDivideInterestTerm($array_fee_set);
		}
        
		return $val;
	}

    /**
	* 다음상환일 구하기
	*
	* @param  Date   - 기준일
	* @param  String - 약정일
	* @return Date   - 다음상환일
	*/
	public function getNextReturnDate($today, $lastday)
	{
		$contractDay = $this->investInfo['contract_day'];
		
        $today = $this->addDay($today);
		
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

		// 상환일이 종결일을 넘을 수 없다.
		$end_date = min($lastday, $this->investInfo['contract_end_date']);

		if( !empty($end_date) && $today > $end_date )
		{
			$today = $end_date;
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
	private function getDayInterestTerm(&$array_rate_set)
	{
		$interest = 0;
		if( isset($array_rate_set) && sizeof($array_rate_set) > 0)
		{
            foreach($array_rate_set as $balance => $arrRatio)
            {
                foreach($arrRatio as $invest_rate => $day)
                {
                    $invest_rate = (float) $invest_rate;
			        $interest += (($balance * $day * ($invest_rate/100)) / 365);
                }
            }
		}

		return $interest;
	}

    /**
	* 금리셋트로 이자 계산 ( 같은 이자구간(정상,연체)내에서 금리변동이 발생할 수 있어서 사용 )
	*
	* @param  Array - 금리셋트 [금리] = 계산일수
	* @param  Int   - 이자계산의 기준금액 (잔액, 월상환금액 등등)
	* @return Int   - 이자응답
	*/
	private function getMonthInterestTerm(&$array_rate_set)
	{
		$interest = 0;
		if( isset($array_rate_set) && sizeof($array_rate_set) > 0)
		{
            foreach($array_rate_set as $balance => $arrRatio)
            {
                foreach($arrRatio as $invest_rate => $month)
                {
                    $invest_rate = (float) $invest_rate;
					$interest += round(($balance * $month * ($invest_rate/100)) / 12 / 10 ) * 10;
                }
            }
		}

		return $interest;
	}

    /**
	* 원금상환시 금리셋트로 이자 계산 ( 같은 이자구간(정상,연체)내에서 금리변동이 발생할 수 있어서 사용 )
	*
	* @param  Array - 금리셋트 [금리] = 계산일수
	* @param  Int   - 이자계산의 기준금액 (잔액, 월상환금액 등등)
	* @return Int   - 이자응답
	*/
	private function getDivideInterestTerm(&$array_rate_set)
	{
		$interest = 0;
		if( isset($array_rate_set) && sizeof($array_rate_set) > 0)
		{
            foreach($array_rate_set as $balance => $arrRatio)
            {
                foreach($arrRatio as $invest_rate => $day)
                {
                    $invest_rate = (float) $invest_rate;
					$interest+= floor( ( $balance * $day * ($invest_rate/100)) / 365 / 10 ) * 10;
                }
            }
		}

		return $interest;
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
	public function getCurrRate($div, $today)
	{
        $val = array();
        if(isset($this->{$div}))
        {
            foreach( $this->{$div} as $v )
            {
                if( $v['req_date']>$today )
                {
                    break;
                }
                $val = $v;
            }
        }

		return $val;
	}

	public function getBizDay($today)
	{
		$info_month = substr($today,4,2);

		while( in_array($today, $this->holiday) )
		{
			$today = $this->addDay($today);
		}

		// 기관차입이 아닐때만
		if($this->investInfo['pro_cd'] != '03')
		{
			// 차기상환일의 영업일이 달을 넘어갈 경우, 해당하는 달(info_month)의 마지막 영업일을 구함
			if(substr($today,4,2) != $info_month)
			{
				$today = $this->addDay($today,-1); 
	
				while( in_array($today, $this->holiday) ) 
				{
					$today = $this->addDay($today,-1); 
				}
			}
		}

		return $today;
	}

    /**
	* 일자사이 일수 구하기
	*
	* @param  Date - 시작일
	* @param  Date - 종료일
	* @param  0|1  - 0=한편넣기, 1=양편넣기
	* @return Int  - 일수
	*/
	public static function dateTerm($s, $e, $mode=0)
	{
		$day = ( $mode!=0 ) ? 1 : 0 ;
		if( $s && $e )
		{
			$st = Loan::dateToUnixtime($s);
			$et = Loan::dateToUnixtime($e);
			return intval( ($et-$st)/(86400) ) + $day;
		}
		else
		{
			return 0;
		}
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
		return date("Ymd", (Loan::dateToUnixtime($today) + (86400 * $cnt)));
	}

	// 월수 증가
	public static function addMonth($today, $t=1)
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

	/**
	 * 기준일자로 스케줄 갱신
	 * 
	 * @param Int - 투자일련번호
	 * @return Array - 스케줄정보 배열
	 */
	public static function getReturnPlan($loan_info_no)
	{
		// 지급완료된 스케줄만(divide_flag = 'Y') 추출
		$sch = DB::TABLE("loan_info_return_plan")->WHERE('loan_info_no',$loan_info_no)->WHERE('divide_flag','Y')->WHERE('save_status','Y')->ORDERBY('seq')->GET();

		$arrayReturn = [];
		foreach($sch as $v)
		{
			$arrayReturn[$v->plan_date]['seq'] = $v->seq;
			$arrayReturn[$v->plan_date]['plan_date'] = $v->plan_date;
			$arrayReturn[$v->plan_date]['plan_date_biz'] = $v->plan_date_biz;
			$arrayReturn[$v->plan_date]['plan_money'] = $v->plan_money;
			$arrayReturn[$v->plan_date]['plan_interest_sdate'] = $v->plan_interest_sdate;
			$arrayReturn[$v->plan_date]['plan_interest_edate'] = $v->plan_interest_edate;
		}

		return $arrayReturn;
	}

	/**
     * 투자자스케줄 업데이트, 배치 및 입금 처리 후 사용
     *
     * @param  Integer $no
     * @param  Date    $dt
     * @return view
     */
	public static function updateSavePlan($no, $dt)
	{
		if( empty($dt) )
		{
			$dt = date("Ymd");
		}

		// Query 시간 체크를 위한 
		$sTime = microtime(1);

		$no = ( $no>0 ) ? $no : 0;

		$cnt = 1;
		// 기본쿼리
		$LOAN = DB::table("loan_info");
		$LOAN = $LOAN->select("no, loan_date, loan_term, loan_rate, loan_delay_rate, contract_day, contract_end_date, return_method_cd, balance, settle_interest, sanggak_interest, cost_money, interest_sum, return_date, return_date_biz, status, first_delay_date, fullpay_date, last_in_money, return_origin_sum");
		$LOAN = $LOAN->where("save_status", "Y");
		if( $no>0 )
		{
			$LOAN = $LOAN->whereIn('status', ['A','E']);
			$LOAN->where('no',$no);
		}
		// 임시
		else 
		{
			$LOAN = $LOAN->whereIn('status', ['A']);
			// $LOAN->WHERE('NO', '>=', 98450);
		}
		$LOAN->orderBy("NO");

		$RSLT = $LOAN->get();
		$RSLT = Func::chungDec(["LOAN_INFO"], $RSLT);	// CHUNG DATABASE DECRYPT
			
		log::debug("1 : ");

		foreach( $RSLT as $v )
		{
			if( $no==0 )
			{
				log::debug("no=0 : ");
				echo $v->no."\n";
			}

			// 기존완제건
			if($v->status=='E' && $no>0)
			{
				$_PLAN = Array();
				$_PLAN['divide_flag']   = 'Y';
				$_PLAN['divide_status'] = '1';
				$_PLAN['divide_time']   = date('YmdHis');
				$_PLAN['save_id']       = 'SYSTEM';
				$_PLAN['save_time']     = date('YmdHis');
				$_PLAN['memo']          = '원금상환 처리';

				$rslt = DB::dataProcess('UPD', 'loan_info_return_plan', $_PLAN, ["loan_info_no"=>$v->no, 'divide_flag'=>'N', 'save_status'=>'Y']);

				log::debug("일반완제 계약번호 : ".$v->no);

				return 'Y';
			}
			
			log::debug("2 : ");

			$plan_data = array();

			// 차입금 데이터 이후입금 합계액
			$vtmny = DB::table("loan_info_trade")->select(DB::RAW("coalesce(sum(TRADE_MONEY),0) as sum_trade_money"))->where("loan_info_no", $v->no)->where("trade_div", "I")->where("save_status", "Y")->first();

			$trade_money_sum = $vtmny->sum_trade_money;

			$RETURN = DB::table("loan_info_return_plan")
										->where("loan_info_no", $v->no)
										->where("save_status", "Y")
										->orderBy("seq")
										->get();
			$RETURN = Func::chungDec(["loan_info_return_plan"], $RETURN);	// CHUNG DATABASE DECRYPT

			foreach($RETURN as $value)
			{
				$plan_data = $value;
				
				$trade_money_sum -= $value->plan_origin;
				$trade_money_sum -= $value->plan_interest;
				
				if( $trade_money_sum < 0 )
				{
					break;
				}
			}

			if(empty($plan_data))
			{
				log::debug("실행오류0 : ");
				return "실행오류" ;
			}
			
			if(isset($trade_money_sum) && $trade_money_sum == 0)
			{
				$_PLAN = Array();
				$_PLAN['divide_flag']   = 'Y';
				$_PLAN['divide_status'] = '1';
				$_PLAN['divide_time']   = date('YmdHis');
				$_PLAN['save_id']       = 'SYSTEM';
				$_PLAN['save_time']     = date('YmdHis');
				$_PLAN['memo']          = '마지막 이자지급 처리';

				$rslt = DB::dataProcess('UPD', 'loan_info_return_plan', $_PLAN, ["loan_info_no"=>$v->no, 'divide_flag'=>'N', 'save_status'=>'Y']);
			}
			else
			{
				$CHECK = DB::table("loan_info_return_plan")
											->where("loan_info_no", $v->no)
											->where("seq", ">=",$plan_data->seq)
											->where("save_status", "Y")
											->orderBy("seq")
											->first();
	
				if(!empty($CHECK->no))
				{
					$_PLAN = Array();
					$_PLAN['divide_flag']   = 'N';
					$_PLAN['divide_status'] = '';
					$_PLAN['divide_time']   = '';
					$_PLAN['save_id']       = 'SYSTEM';
					$_PLAN['save_time']     = date('YmdHis');
					$_PLAN['memo']          = '수익지급 처리취소';
		
					$rslt = DB::dataProcess('UPD', 'loan_info_return_plan', $_PLAN, [['loan_info_no','=',$v->no], ['seq','>=',$plan_data->seq], ['divide_flag','=','Y'],['save_status','=','Y']]);
					if( $rslt!="Y" && $no>0 )
					{
						log::debug("실행오류1 : ");
						return "실행오류";
					}
				}
				
				$_PLAN = Array();
				$_PLAN['divide_flag']   = 'Y';
				$_PLAN['divide_status'] = '1';
				$_PLAN['divide_time']   = date('YmdHis');
				$_PLAN['save_id']       = 'SYSTEM';
				$_PLAN['save_time']     = date('YmdHis');
				$_PLAN['memo']          = '수익지급 처리';
	
				$rslt = DB::dataProcess('UPD', 'loan_info_return_plan', $_PLAN, [['loan_info_no','=',$v->no], ['seq','<',$plan_data->seq], ['divide_flag','=','N'],['save_status','=','Y']]);
				if( $rslt!="Y" && $no>0 )
				{
					log::debug("실행오류2 : ");
					return "실행오류";
				}
			}
			
			log::debug("3 : ");

			if( $cnt==1000 )
			{
				$cnt = 0;
				$eTime = microtime(1);
				$sTime = $eTime;
			}
			$cnt++;
        }

		log::debug("4 : ");

		if( $no>0 )
		{
			log::debug("no>0 : ".$no);
			log::debug("실행오류4? : ");
			return ( isset($rslt) ) ? $rslt : "실행오류" ;
		}
	}
}

?>