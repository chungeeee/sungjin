<?php
namespace App\Chung;

//use App\Models\User;
//use DBD;
//use Storage;
use DB;
use Auth;
use Loan;
use Log;
use Sum;
use Invest;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
//use Illuminate\Pagination\Paginator;

class Trade
{ 

	public $no;
	public $loan;
	public $checkMsg;
	public $interest = Array();

	public function __construct($no, $today="")
	{
        $this->no = $no;
		$this->loan = new Loan($no);
		$this->invest = new Invest($no);
    }

	public function tradeCheck($v)
	{
		// 필수값 검사, 데이터 적합성 검사

		// 신규,증액 시 - 당일 승인내역 등 심사상태 검사

		// 신규,증액 시 - 스케줄 검사 - 입금 기준일 이후 스케줄 원금 합계가 

		// 가수금 거래 시 가수금 금액 검사

		$trade_date      = str_replace("-","",$v['trade_date']);
		$last_trade_date = $this->loan->loanInfo['last_trade_date'] ?? 0;

		if(isset($last_trade_date) && $trade_date < $last_trade_date )
		{
			$this->checkMsg = "거래일이 최종거래일보다 과거일 수 없습니다.";
			log::debug($this->checkMsg);
			return false;
		}

		$this->checkMsg = "정상";
		return true;
	}


	/**
	* 기준일의 이자정보 셋팅
	*
	* @param  
	* @return 
	*/
	public function setInterest($today)
	{
        $this->interest = $this->loan->getInterest($today);
		return $this->interest;
	}




	/**
	* 입금등록
	*
	* @param  Array	  입금정보 배열 ( cust_info_no, loan_info_no, trade_type, trade_path_cd, trade_date, trade_money, lose_money )
	* @return Array   [action_mode=PREVIEW] LOAN_INFO_TRADE에 등록할 정보 배열
	* @return Integer [action_mode=INSERT]  LOAN_INFO_TRADE의 NO
	*/
	public function tradeInInsert($v, $save_id="")
	{
        $v['trade_div']        = "I";
		$v['transaction_date'] = isset($v['transaction_date']) ? str_replace("-","",$v['transaction_date']) : date("Ymd");

		if( !$this->tradeCheck($v) )
		{
			$v['PROC_FLAG'] = "N";
			$v['REPLAN_YN'] = "N";
			$v['PROC_MSG']  = $this->checkMsg;
			return $v;
		}

		// 기준일 이자정보를 생성
		$val = $this->setInterest($v['trade_date']);

		if(!is_array($this->interest) || sizeof($this->interest)==0 )
		{
			$v['PROC_FLAG'] = "N";
			$v['REPLAN_YN'] = "N";
			$v['PROC_MSG']  = "이자정보가 조회되지 않았습니다.";
			return $v;
		}

		// 만기연장일 경우 스케줄측정 X
		if($v['trade_type'] != '07')
		{
			$return_plan      = $val['return_plan'];
		}
		else
		{
			$return_plan = Array();
		}

		$take_date 		  = $v['trade_date'];

		$loan_status      = $this->loan->loanInfo['status'];
		$return_date      = $this->loan->loanInfo['return_date'];
		$return_date_biz  = $this->loan->loanInfo['return_date_biz'];
		$kihan_date       = $this->loan->loanInfo['kihan_date'];
		$kihan_date_biz   = $this->loan->loanInfo['kihan_date_biz'];

		// $v['interest_detail'] = json_encode($val, JSON_PRETTY_PRINT);
		$v['PROC_FLAG'] = "Y";
		$v['PROC_MSG']  = "수익지급처리";
		$v['REPLAN_YN'] = "N";

		// 입금회차 구간에 따라 보여줘야하는 계산이자가 다르다.
		$v['interest']             = 0;
		$v['delay_term']           = 0;
		$v['delay_money']          = 0;
		$v['delay_interest']       = 0;
		$v['interest_sdate']       = "";
		$v['interest_edate']       = "";
		$v['delay_money_sdate']    = "";
		$v['delay_money_edate']    = "";
		$v['delay_interest_sdate'] = "";
		$v['delay_interest_edate'] = "";

		$v['interest_term']        = 0;
		$v['delay_money_term']     = 0;
		$v['delay_interest_term']  = 0;

		foreach( $return_plan as $pd => $vtmp )
		{
			$v['interest'] += Func::nvl($return_plan[$pd]['interest'], 0);

			if( $return_plan[$pd]['interest_sdate'] ) $v['interest_sdate'] = min( ( $v['interest_sdate'] ? $v['interest_sdate'] : "99991231" ), $return_plan[$pd]['interest_sdate'] );
			if( $return_plan[$pd]['interest_edate'] ) $v['interest_edate'] = max( ( $v['interest_edate'] ? $v['interest_edate'] : "00000000" ), $return_plan[$pd]['interest_edate'] );
		}

		// 감면처리 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$lose_money = $v['lose_money'];
		$v['lose_cost_money']          = Trade::divMoney( $lose_money, $val['cost_money'] );
		$v['lose_misu_money']          = Trade::divMoney( $lose_money, $val['misu_money'] );
		$v['lose_lack_delay_interest'] = Trade::divMoney( $lose_money, $val['lack_delay_interest'] );
		$v['lose_lack_delay_money']    = Trade::divMoney( $lose_money, $val['lack_delay_money'] );
		$v['lose_lack_interest']       = Trade::divMoney( $lose_money, $val['lack_interest'] );

		// 계산된 이자는 회차별로 뺀다.
		$v['lose_delay_interest']  = 0;
		$v['lose_delay_money']     = 0;
		$v['lose_interest']        = 0;
		$v['lose_settle_interest'] = 0;
		$v['lose_origin']          = 0;

		foreach( $return_plan as $pd => $vtmp )
		{
			if( is_numeric($pd) )
			{
				$v['lose_delay_interest'] 	+= Trade::divMoney( $lose_money, $return_plan[$pd]['delay_interest'], 	$val['charge_delay_interest'] );
				$v['lose_delay_money']    	+= Trade::divMoney( $lose_money, $return_plan[$pd]['delay_money'],    	$val['charge_delay_money'] );
				$v['lose_interest']         += Trade::divMoney( $lose_money, $return_plan[$pd]['interest'],    $val['charge_interest'] );
				$v['lose_origin']           += Trade::divMoney( $lose_money, $return_plan[$pd]['plan_origin'], $val['charge_origin'] );
			}
		}
		
		$v['lose_sanggak_interest'] = Trade::divMoney( $lose_money, $val['sanggak_interest'] );
		$v['lose_interest']        += Trade::divMoney( $lose_money, $val['no_charge_interest'] );
		$v['lose_origin']          += Trade::divMoney( $lose_money, $val['no_charge_origin'] );

		// 이자감면합계
		$v['lose_interest_sum'] = $v['lose_misu_money'];
		$v['lose_interest_sum']+= $v['lose_lack_delay_interest'] + $v['lose_lack_delay_money'] + $v['lose_lack_interest'];
		$v['lose_interest_sum']+= $v['lose_delay_interest'] + $v['lose_delay_money'] + $v['lose_interest'];
		$v['lose_interest_sum']+= $v['lose_settle_interest'];
		$v['lose_interest_sum']+= $v['lose_sanggak_interest'];

		// 감면액이 남았다면, 가수금이 발생할 수는 없기 때문에, 감면금액을 실감면금액으로 조정한다.
		if( $lose_money>0 )
		{
			$lose_money = 0;
			$v['lose_money'] = $v['lose_interest_sum'] + $v['lose_origin'];
		}
		// 남은이자금액 - 부족금이랑은 다르다. 1회차만 입금해서 부족금이 아닌데 남는 이자가 있다.

		// 입금처리 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
		$trade_money = $v['trade_money'];
		
		// 근저당권설정비용 수취 - 얘는 감면은 없다.
		$v['return_dambo_set_fee']       = Trade::divMoney( $trade_money, $val['dambo_set_fee'] );

		$v['return_cost_money']          = Trade::divMoney( $trade_money, $val['cost_money'] );
		$v['return_cost_origin']         = Trade::divMoney( $trade_money, $val['cost_origin'] );
		$v['return_misu_money']          = Trade::divMoney( $trade_money, $val['misu_money'] );
		$v['return_lack_delay_interest'] = Trade::divMoney( $trade_money, $val['lack_delay_interest'] );
		$v['return_lack_delay_money']    = Trade::divMoney( $trade_money, $val['lack_delay_money'] );
		$v['return_lack_interest']       = Trade::divMoney( $trade_money, $val['lack_interest'] );

		// 계산된 이자는 회차별로 뺀다.
		$v['return_delay_interest']      = 0;
		$v['return_delay_money']         = 0;
		$v['return_interest']            = 0;
		$v['return_settle_interest']     = 0;
		$v['return_origin']              = 0;

		foreach( $return_plan as $pd => $vtmp )
		{
			// 미청구(NOCHARGE)는 밑에서 합산해서 뺀다.
			if( is_numeric($pd) )
			{
				$v['return_delay_interest']   += Trade::divMoney( $trade_money, $return_plan[$pd]['delay_interest'], $val['charge_delay_interest'] );
				$v['return_delay_money']      += Trade::divMoney( $trade_money, $return_plan[$pd]['delay_money'],    $val['charge_delay_money'] );
				$v['return_interest']         += Trade::divMoney( $trade_money, $return_plan[$pd]['interest'],       $val['charge_interest'] );
				$v['return_settle_interest']  += Trade::divMoney( $trade_money, $return_plan[$pd]['settle_interest'] );
				$v['return_origin']           += Trade::divMoney( $trade_money, $return_plan[$pd]['plan_origin'],    $val['charge_origin'] );
			}
		}

		$v['return_sanggak_interest']    = Trade::divMoney( $trade_money, $val['sanggak_interest'] );
		$v['return_interest']           += Trade::divMoney( $trade_money, $val['no_charge_interest'] );

		// 이자입금합계
		$v['return_interest_sum'] = $v['return_misu_money'];
		$v['return_interest_sum']+= $v['return_lack_delay_interest'] + $v['return_lack_delay_money'] + $v['return_lack_interest'];
		$v['return_interest_sum']+= $v['return_delay_interest'] + $v['return_delay_money'] + $v['return_interest'];
		$v['return_interest_sum']+= $v['return_settle_interest'];
		$v['return_interest_sum']+= $v['return_sanggak_interest'];

		// 잔여 이자
		$v['refund_interest'] = $v['interest'] - $v['return_interest_sum'];

		// 입금하고 남은금액을 보여줘야한다.
		$v['remain_charge_delay_interest'] = $val['charge_delay_interest'];
		$v['remain_charge_delay_money']    = $val['charge_delay_money'];
		$v['remain_charge_interest']       = $val['charge_interest'];
		$v['remain_charge_origin']         = $val['charge_origin'];

		// 원금상환액과 중도상환수수료 계산 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		$v['return_fee']      = 0;
		$v['return_fee_rate'] = 0;

		// 남아있는 입금액이 없으면 할필요가 없으니 그냥 패스
		if( $trade_money>0 )
		{
			$v['return_origin'] += Trade::divMoney( $trade_money, $val['no_charge_origin'] );
			$v['return_fee']     = 0;
		}
		// 원금상환액과 중도상환수수료 계산 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

		// 입금후
		$v['cost_money']          = $val['cost_money'];
		$v['misu_money']          = $val['misu_money'];

		$v['lack_delay_interest'] = 0;
		$v['lack_delay_money']    = 0;
		$v['lack_interest']       = 0;

		$v['sanggak_interest']    = $val['sanggak_interest'];

		$v['balance']             = $val['balance'] - ( $v['lose_origin'] + $v['return_origin'] );
		$v['over_money']          = $trade_money + $this->loan->loanInfo['over_money'];				// 가수금은 잔액 개념이라 더해줘야 한다. 발생가수금 = (거래금액>가수금) ? 가수금 : 거래금액 ;
		$v['take_date']           = $take_date;

		// 상환일 기본값
		$v['return_date']         = $return_date;
		$v['return_date_biz']     = $return_date_biz;
		$v['kihan_date']          = $kihan_date;
		$v['kihan_date_biz']      = $kihan_date_biz;

		// 차입금 데이터 이후입금 합계액
		$vtmny = DB::table("loan_info_trade")->select(DB::RAW("coalesce(sum(TRADE_MONEY),0) as sum_trade_money"))->where("loan_info_no", $v['loan_info_no'])->where("trade_div", "I")->where("save_status", "Y")->first();

		$origin_trade_money_sum = $trade_money_sum = $vtmny->sum_trade_money + $v['trade_money'];

		// 거래원장에 없는애들까지.
		if(!empty($v['count']))
		{
			// PLAN
			$p_sum = DB::table("loan_info_return_plan")->select("plan_interest", "plan_origin")
												->where("loan_info_no", $v['loan_info_no'])
												->where("save_status", 'Y')
												->where('seq', '<', $v['count'])
												->orderBy('seq')
												->get();
			$p_sum = Func::chungDec(["loan_info_return_plan"], $p_sum);	// CHUNG DATABASE DECRYPT

			foreach($p_sum as $p_v)
			{						
				$trade_money_sum+= $p_v->plan_origin;
				$trade_money_sum+= $p_v->plan_interest;
			}
		}

		// PLAN_max
		$ps = DB::table("loan_info_return_plan")->select(DB::RAW("coalesce(sum(plan_origin),0) as sum_plan_origin"), DB::RAW("coalesce(sum(plan_interest),0) as sum_plan_interest"))->where("loan_info_no", $v['loan_info_no'])->where("save_status", 'Y')->first();

		// PLAN
		$rtmp = DB::table("loan_info_return_plan")->select("*")->where("loan_info_no", $v['loan_info_no'])->where("save_status", 'Y')->orderBy('seq')->get();
		$rtmp = Func::chungDec(["loan_info_return_plan"], $rtmp);	// CHUNG DATABASE DECRYPT
		
		foreach( $rtmp as $vtmp )
		{
			$trade_money_sum     -= $vtmp->plan_origin;
			$trade_money_sum     -= $vtmp->plan_interest;

			$v['return_date']     = $vtmp->plan_date;
			$v['return_date_biz'] = $vtmp->plan_date_biz;
			$v['kihan_date']      = $this->loan->loanInfo['contract_end_date'];
			$v['kihan_date_biz']  = $this->loan->getBizDay($v['kihan_date']);

			if($origin_trade_money_sum != ($ps->sum_plan_origin + $ps->sum_plan_interest))
			{
				$v['plan_return_money']    = $vtmp->plan_money ?? 0;
				$v['plan_return_origin']   = $vtmp->plan_origin ?? 0;
				$v['plan_return_interest'] = $vtmp->plan_interest ?? 0;
				$v['plan_withholding_tax'] = $vtmp->withholding_tax ?? 0;
				$v['plan_income_tax']      = $vtmp->income_tax ?? 0;
				$v['plan_local_tax']       = $vtmp->local_tax ?? 0;
			}

			if( $trade_money_sum < 0 )
			{
				break;
			}
		}

		// 거래계약의 상각여부
		$v['loan_settle_yn']  = "N";
		$v['loan_sanggak_yn'] = "N" ;

		// 관리지점, 관리자 기본값 설정
		if( !isset($v['manager_code']) || $v['manager_code']=="" )
		{
			$v['manager_code'] = $this->loan->loanInfo['manager_code'];
		}
		if( !isset($v['manager_id']) || $v['manager_id']=="" )
		{
			$v['manager_id'] = $this->loan->loanInfo['manager_id'];
		}

		// 미청구이자 잔여분
		$v['no_charge_interest']  = $val['no_charge_interest'];

		// LOAN_INFO_TRADE에 등록 /////////////////////////////////////////////////////////////////////////////
		if( $v['action_mode']=="INSERT" )
		{
			$v['save_status'] = "Y";
			$v['save_time']   = date("YmdHis");
			$v['save_id']     = ( $save_id=="" ) ? Auth::id() : $save_id ;
			$v['sms_flag']    = ( isset($v['sms_flag']) && $v['sms_flag']=="A" ) ? "A" : "N" ;

			// 거래순번
			$vt = DB::TABLE("LOAN_INFO_TRADE")->SELECT("MAX(SEQ) as seq")->where("LOAN_INFO_NO", $v['loan_info_no'])->FIRST();
			$v['seq'] = ( $vt->seq ) ? $vt->seq + 1 : 1 ;

			$vi = $v;

			// 거래원장 등록
			$rslt = DB::dataProcess("INS", "loan_info_trade", $vi, null, $loan_info_trade_no);
			if( $rslt!="Y" )
			{
				return "실행오류";
			}

			$loan_info_sum = DB::table("loan_info")->select('sum_interest', 'return_origin_sum', 'return_interest_sum', 'return_withholding_tax_sum', 'return_income_tax_sum', 'return_local_tax_sum')->where('no', $v['loan_info_no'])->where('save_status', 'Y')->first();

			// 계약정보 업데이트
			$vl = [];
			$vl['cost_money']           = $vi['cost_money'];
			$vl['misu_money']           = $vi['misu_money'];
			$vl['lack_delay_interest']  = $vi['lack_delay_interest'];
			$vl['lack_delay_money']     = $vi['lack_delay_money'];
			$vl['lack_interest']        = $vi['lack_interest'];
			$vl['sanggak_interest']     = $vi['sanggak_interest'];
			$vl['balance']              = $vi['balance'];
			$vl['over_money']           = $vi['over_money'];
			$vl['take_date']            = $vi['take_date'];
			$vl['return_date']          = $vi['return_date'];
			$vl['return_date_biz']      = $vi['return_date_biz'];
			$vl['kihan_date']           = $vi['kihan_date'];
			$vl['kihan_date_biz']       = $vi['kihan_date_biz'];
			$vl['last_trade_date']      = $vi['trade_date'];
			$vl['last_in_date']         = $vi['trade_date'];
			$vl['last_in_money']        = $vi['trade_money'];

			$vl['return_money']         = $vi['plan_return_money'] ?? 0;
			$vl['return_origin']        = $vi['plan_return_origin'] ?? 0;
			$vl['return_interest']      = $vi['plan_return_interest'] ?? 0;
			$vl['withholding_tax']      = $vi['plan_withholding_tax'] ?? 0;
			$vl['income_tax']           = $vi['plan_income_tax'] ?? 0;
			$vl['local_tax']            = $vi['plan_local_tax'] ?? 0;

			$vl['return_intarval']      = $vi['return_intarval'] ?? 0;
			$vl['return_withholding_tax_sum'] = ($loan_info_sum->return_withholding_tax_sum ?? 0) + $vi['withholding_tax'];
			$vl['return_income_tax_sum']= ($loan_info_sum->return_income_tax_sum ?? 0) + $vi['income_tax'];
			$vl['return_local_tax_sum'] = ($loan_info_sum->return_local_tax_sum ?? 0) + $vi['local_tax'];
			$vl['return_origin_sum']    = ($loan_info_sum->return_origin_sum ?? 0) + $vi['return_origin'];
			$vl['return_last_interest'] = $vi['return_interest_sum'];
			$vl['return_interest_sum']  = ($loan_info_sum->return_interest_sum ?? 0) + $vi['return_interest_sum'];
			$vl['loan_info_trade_no']   = $loan_info_trade_no;
			$vl['now_cost']             = 0;

			$vl['refund_interest']      = ($loan_info_sum->sum_interest ?? 0) - $vl['return_interest_sum'];

			$vl['save_id']              = $vi['save_id'];
			$vl['save_time']            = $vi['save_time'];

			// 완제
			if( $vl['balance']==0 && $vl['sanggak_interest']==0 && ($vl['misu_money'] + $vl['cost_money'] + $vl['lack_delay_interest'] + $vl['lack_delay_money'] + $vl['lack_interest'] ) == 0)
			{
				$vl['status'] = 'E';

				$vl['delay_interest']       = 0;
				$vl['delay_money']          = 0;
				$vl['interest']             = 0;
				$vl['interest_sum']         = $vl['misu_money'] + $vl['lack_delay_interest'] + $vl['lack_delay_money'] + $vl['lack_interest'] + $vl['sanggak_interest'];
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
				$vl['charge_money']         = $vl['interest_sum'];
				$vl['fullpay_money']        = $vl['interest_sum'];	// 혹시 이자만 남는 경우가...
				$vl['fullpay_date']         = $vi['trade_date'];
				$vl['fullpay_cd']           = $vi['fullpay_cd'] ?? '';
				$vl['fullpay_origin'] 	    = $vl['return_origin_sum'];

				$vl['return_money']         = 0;
				$vl['return_origin']        = 0;
				$vl['return_interest']      = 0;
				$vl['withholding_tax']      = 0;
				$vl['income_tax']           = 0;
				$vl['local_tax']            = 0;
			}
			
			$rslt = DB::dataProcess('UPD', 'loan_info', $vl, ["no"=>$v['loan_info_no']]);
			if( $rslt!="Y" )
			{
				return "계약정보업데이트오류";
			}

			$rslt = Invest::updateSavePlan($v['loan_info_no'], date("Ymd"));
			if( $rslt!="Y" )
			{
				return $rslt;
			}

			// if( !isset($vl['status']) || $vl['status']!="E" ) 
			// {
			// 	$rslt = Loan::updateLoanInfoInterest($v['loan_info_no'], date("Ymd"));
			// 	if( $rslt!="Y" )
			// 	{
			// 		return "이자계산오류";
			// 	}
			// }

			return $loan_info_trade_no;
		}
		else
		{
			// 미리보기인경우는 정보만 넘김
			return $v;
		}
	}

	/**
	* 입금삭제
	*
	* @param   Integer $no		삭제할 loan_info_trade 의 NO
	* @param   String  $del_id	삭제한 직원사번 빈값인 경우 로그인 아이디 셋팅
	* @return  Boolean 
	*/
	public function tradeInDelete($no, $del_id="", $fromForm="", $sms_send_flag="N")
	{
		$rslt = DB::table("loan_info_trade")->select("*")->where("no", $no)->where("save_status", "Y")->where("trade_div", "I")->ORDERBY("no","desc")->first();
		$rslt = Func::chungDec(["LOAN_INFO_TRADE"], $rslt);	// CHUNG DATABASE DECRYPT
        $vt = (Array) $rslt;

		if( !$vt )
		{
			Log::debug("삭제가능한 거래원장이 없습니다.");
            return "삭제가능한 거래원장이 없습니다.";
		}

		$rslt = DB::table("loan_info")->select("*")->where("no", $vt['loan_info_no'])->where("save_status", "Y")->first();
		$rslt = Func::chungDec(["LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT
		$vl = (Array) $rslt;

		if( !$vl )
		{
			Log::debug("선택하신 거래의 여신원장이없습니다.");
            return "선택하신 거래의 여신원장이없습니다.";
		}
		if( $no!=$vl['loan_info_trade_no'] )
		{
			Log::debug("마지막 거래원장부터 삭제 가능합니다.#1 (".$vt['loan_info_no'].")");
			return "마지막 거래원장부터 삭제 가능합니다.#1 (".$vt['loan_info_no'].")";
		}

		$vc = (Array) DB::TABLE("LOAN_INFO_TRADE")->SELECT("MAX(NO) as NO")->WHERE("LOAN_INFO_NO", $vt['loan_info_no'])->WHERE("SAVE_STATUS", "Y")->FIRST();
		if( $vc['no']!=$no )
		{
			Log::debug("마지막 거래원장부터 삭제 가능합니다.#2 (".$vt['loan_info_no'].")");
			return "마지막 거래원장부터 삭제 가능합니다.#2 (".$vt['loan_info_no'].")";
		}

		$loan_info_no = $vl['no'];

		if( $del_id=="" )
		{
			$del_id = Auth::id();
		}

		$upd_vals = Array( 'save_status'=>'N', 'del_time'=>date("YmdHis"), 'del_id'=>$del_id );

		// 거래원장 업데이트
		$rslt = DB::dataProcess("UPD", "loan_info_trade", $upd_vals, ['no'=>$no]);
        if( $rslt!="Y" )
        {
            return "실행오류";
        }

		// PLAN_max
		$ps = DB::table("loan_info_return_plan")->select(DB::RAW("coalesce(sum(plan_origin),0) as sum_plan_origin"), DB::RAW("coalesce(sum(plan_interest),0) as sum_plan_interest"))->where("loan_info_no", $loan_info_no)->where("save_status", 'Y')->first();

		// PLAN
		$rtmp = DB::table("loan_info_return_plan")->select("*")->where("loan_info_no", $loan_info_no)->where("save_status", 'Y')->orderBy('seq')->get();
		$rtmp = Func::chungDec(["loan_info_return_plan"], $rtmp);	// CHUNG DATABASE DECRYPT
		
		// 차입금 데이터 이후입금 합계액
		$vtmny = DB::table("loan_info_trade")->select(DB::RAW("coalesce(sum(TRADE_MONEY),0) as sum_trade_money"))->where("save_status", "Y")->where("trade_div", "I")->where("loan_info_no", $loan_info_no)->first();
		
		$origin_trade_money_sum = $trade_money_sum = $vtmny->sum_trade_money;

		foreach( $rtmp as $vtmp )
		{
			$trade_money_sum     -= $vtmp->plan_interest;
			$trade_money_sum     -= $vtmp->plan_origin;

			$plan['return_date']     = $vtmp->plan_date;
			$plan['return_date_biz'] = $vtmp->plan_date_biz;
			$plan['kihan_date']      = $this->loan->loanInfo['contract_end_date'];
			$plan['kihan_date_biz']  = $this->loan->getBizDay($plan['kihan_date']);
			
			if($origin_trade_money_sum != ($ps->sum_plan_origin + $ps->sum_plan_interest))
			{
				$plan['return_money']    = $vtmp->plan_money ?? 0;
				$plan['return_origin']   = $vtmp->plan_origin ?? 0;
				$plan['return_interest'] = $vtmp->plan_interest ?? 0;
				$plan['withholding_tax'] = $vtmp->withholding_tax ?? 0;
				$plan['income_tax']      = $vtmp->income_tax ?? 0;
				$plan['local_tax']       = $vtmp->local_tax ?? 0;
			}

			if( $trade_money_sum < 0 )
			{
				break;
			}
		}

		$loan_info_sum = DB::table("loan_info")->select('return_origin_sum', 'return_interest_sum', 'return_withholding_tax_sum', 'return_income_tax_sum', 'return_local_tax_sum', 'return_last_interest')->where('no', $loan_info_no)->where('save_status', 'Y')->first();

		// 여신원장 업데이트
		$rslt = DB::table("loan_info_trade")->select("*")->where("loan_info_no", $loan_info_no)->where("save_status", "Y")->ORDERBY("NO","DESC")->first();
		$rslt = Func::chungDec(["LOAN_INFO_TRADE"], $rslt);	// CHUNG DATABASE DECRYPT
		$vo = (Array) $rslt;
		if( $vo )
		{
			$vi = [];
			$vi['cost_money']          = $vo['cost_money'];
			$vi['misu_money']          = $vo['misu_money'];
			$vi['lack_delay_interest'] = $vo['lack_delay_interest'];
			$vi['lack_delay_money']    = $vo['lack_delay_money'];
			$vi['lack_interest']       = $vo['lack_interest'];
			$vi['sanggak_interest']    = $vo['sanggak_interest'];
			$vi['balance']             = $vo['balance'];
			$vi['over_money']          = $vo['over_money'];
			$vi['take_date']           = $vo['take_date'];
			$vi['return_date']         = $vo['return_date'];
			$vi['return_date_biz']     = $vo['return_date_biz'];
			$vi['kihan_date']          = $vo['kihan_date'];
			$vi['kihan_date_biz']      = $vo['kihan_date_biz'];
			$vi['now_cost']	           = $vo['now_cost'];
			$vi['loan_info_trade_no']  = $vo['no'];

			$vi['return_intarval']     = $vo['return_intarval'];
			$vi['return_money']        = $vo['return_money'];
			$vi['return_origin']       = $vo['return_origin'];
			$vi['return_interest']     = $vo['return_interest'];
			$vi['withholding_tax']     = $vo['withholding_tax'];
			$vi['income_tax']          = $vo['income_tax'];
			$vi['local_tax']           = $vo['local_tax'];

			$vi['return_withholding_tax_sum'] = $loan_info_sum->return_withholding_tax_sum - $vo['withholding_tax'];
			$vi['return_income_tax_sum'] = $loan_info_sum->return_income_tax_sum - $vo['income_tax'];
			$vi['return_local_tax_sum']  = $loan_info_sum->return_local_tax_sum - $vo['local_tax'];
			$vi['return_origin_sum']     = $loan_info_sum->return_origin_sum - $vo['return_origin'];
			
			$vi['return_last_interest'] = $vo['return_last_interest'] ?? 0;
			$vi['return_interest_sum']  = $vo['return_interest_sum'];
			
			// SELECT
			$vtmp = (Array) DB::TABLE("LOAN_INFO_TRADE")->SELECT(DB::raw("max(trade_date) as last_trade_date, max(CASE WHEN trade_div='O' and trade_type in ('11','12','13') THEN trade_date ELSE '' END ) AS last_loan_date, max(CASE WHEN trade_div='I' THEN trade_date ELSE '' END ) AS last_in_date, max(CASE WHEN trade_div='I' THEN no ELSE 0 END ) AS last_in_no"))->WHERE("LOAN_INFO_NO", $loan_info_no)->WHERE("SAVE_STATUS", "Y")->FIRST();
			$vi['last_trade_date']      = $vtmp['last_trade_date'];
			$vi['last_loan_date']       = $vtmp['last_loan_date'];
			$vi['last_in_date']         = $vtmp['last_in_date'];

			// 마지막 입금액 가져오기
			$vi['last_in_money']   = 0;
			if(isset($vtmp['last_in_no']) && $vtmp['last_in_no']>0)
			{
				$vtmp2 = (Array) DB::table("loan_info_trade")->select('trade_money')->WHERE('no', $vtmp['last_in_no'])->FIRST();
				$vi['last_in_money']   = $vtmp2['trade_money'];
			}

			if( $vo['balance']==0 && $vo['sanggak_interest']==0 && ($vo['misu_money'] + $vo['cost_money'] + $vo['lack_delay_interest'] + $vo['lack_delay_money'] + $vo['lack_interest']) == 0)
			{
				$vi['status'] = "E";
			}
			else
			{
				$vi['status'] = "A";

				// 완제일자를 지워준다.
				$vi['fullpay_money'] = 0;
				$vi['fullpay_date']  = '';
				$vi['fullpay_cd']    = '';
			}
		}
		else
		{
			$vi = [];			
			$vi['cost_money']          = 0;
			$vi['misu_money']          = 0;
			$vi['lack_delay_interest'] = 0;
			$vi['lack_delay_money']    = 0;
			$vi['lack_interest']       = 0;
			$vi['sanggak_interest']    = 0;
			$vi['balance']             = 0;
			$vi['over_money']          = 0;
			$vi['take_date']           = $vl['loan_date'];
			$vi['return_date']         = '';
			$vi['return_date_biz']     = '';
			$vi['kihan_date']          = '';
			$vi['kihan_date_biz']      = '';
			$vi['last_trade_date']     = "";
			$vi['last_loan_date']      = "";
			$vi['last_in_date']        = "";
			$vi['last_in_money']       = 0;
			$vi['loan_info_trade_no']  = 0;
			
			$vi['return_intarval']     = 0;
			$vi['return_money']        = 0;
			$vi['return_origin']       = 0;
			$vi['return_interest']     = 0;
			$vi['withholding_tax']     = 0;
			$vi['income_tax']          = 0;
			$vi['local_tax']           = 0;

			$vi['return_withholding_tax_sum'] = 0;
			$vi['return_income_tax_sum'] = 0;
			$vi['return_local_tax_sum']  = 0;
			$vi['return_origin_sum']     = 0;
			
			$vi['return_interest_sum']  = 0;
			$vi['return_last_interest'] = 0;

			// 이자가 안돌기 때문에, 상태등도 업데이트 해줘야 한다.
			$vi['status'] 			   = "N";
			$vi['interest_sum']        = 0;
			$vi['charge_money']        = 0;
			$vi['fullpay_money']       = 0;
			$vi['delay_term']          = 0;
			$vi['interest_term']       = 0;
			$vi['delay_money_term']    = 0;
			$vi['delay_interest_term'] = 0;
		}
		
        $rslt = DB::dataProcess('UPD', 'LOAN_INFO', $vi, ["no"=>$loan_info_no]);
        if( $rslt!="Y" )
        {
            return "실행오류";
        }

		$rslt = Invest::updateSavePlan($loan_info_no, date("Ymd"));
		if( $rslt!="Y" )
		{
			return $rslt;
		}

		// 이전거래가 있으면서 완제일경우는 이자계산을 하지 않는다.
		// if( $vo && $vi['status']!='E' )
		// {
        // 	$rslt = Loan::updateLoanInfoInterest($loan_info_no, date("Ymd"));
		// 	if( $rslt!="Y" )
		// 	{
		// 		return "이자계산오류(".$loan_info_no.")";
		// 	}
		// }

		return true;
	}

	/**
	* 출금등록
	*
	* @param  Array	  출금정보 배열 ( cust_info_no, loan_info_no, trade_type, trade_path_cd, trade_date, trade_money )
	* @return Integer loan_info_trade의 NO
	*/
	public function tradeOutInsert($v, $save_id="")
	{
        $v['trade_div'] = "O";

		if( !$this->tradeCheck($v) )
		{
			return $this->checkMsg;
		}

        $v['save_status']          = "Y";
        $v['save_time']            = date("YmdHis");
        $v['save_id']              = ( $save_id=="" ) ? Auth::id() : $save_id ;

		// 거래순번
        $vt = DB::table("loan_info_trade")->select("MAX(SEQ) as seq")->where("loan_info_no", $v['loan_info_no'])->first();
        $v['seq']                  = ( $vt->seq ) ? $vt->seq + 1 : 1 ;

		$v['trade_date']           = str_replace("-", "", $v['trade_date']);
		$v['trade_money']          = str_replace(",", "", $v['trade_money']) * 1;
		$v['trade_fee']            = str_replace(",", "", $v['trade_fee']) * 1;
		$v['lose_money']           = 0;

		// 계산
		$v['interest']             = 0;
		$v['delay_money']          = 0;
		$v['delay_interest']       = 0;
		$v['interest_term']        = 0;
		$v['interest_sdate']       = "";
		$v['interest_edate']       = "";
		$v['delay_money_term']     = 0;
		$v['delay_money_sdate']    = "";
		$v['delay_money_edate']    = "";
		$v['delay_interest_term']  = 0;
		$v['delay_interest_sdate'] = "";
		$v['delay_interest_edate'] = "";
		
		// 거래(금액)
		$v['cost_money']           = $this->loan->loanInfo['cost_money'] ?? 0;
		$v['misu_money']           = $this->loan->loanInfo['misu_money'] ?? 0;
		$v['lack_delay_interest']  = $this->loan->loanInfo['lack_delay_interest'] ?? 0;
		$v['lack_delay_money']     = $this->loan->loanInfo['lack_delay_money'] ?? 0;
		$v['lack_interest']        = $this->loan->loanInfo['lack_interest'] ?? 0;
		$v['balance']              = $this->loan->loanInfo['balance'] ?? 0;
		$v['over_money']           = $this->loan->loanInfo['over_money'] ?? 0;

		// 거래(일자)
		$v['take_date']            = $this->loan->loanInfo['take_date'];
		$v['return_date']          = $this->loan->loanInfo['return_date'];
		$v['return_date_biz']      = $this->loan->loanInfo['return_date_biz'];
		$v['kihan_date']           = $this->loan->loanInfo['kihan_date'];
		$v['kihan_date_biz']       = $this->loan->loanInfo['kihan_date_biz'];

		$v['manager_code']         = $this->loan->loanInfo['manager_code'];
		$v['manager_id']           = $this->loan->loanInfo['manager_id'];
		$v['loan_sanggak_yn']      = "N";

		$v['del_id']               = "";
		$v['del_time']             = "";
		
		// 신규
		if( $v['trade_type']=="11")
		{
			// PLAN_max
			$ps = DB::table("loan_info_return_plan")->select(DB::RAW("coalesce(sum(plan_origin),0) as sum_plan_origin"), DB::RAW("coalesce(sum(plan_interest),0) as sum_plan_interest"))->where("loan_info_no", $v['loan_info_no'])->where("save_status", 'Y')->first();
			
			// PLAN
			$rtmp = DB::table("loan_info_return_plan")->select("*")->where("loan_info_no", $v['loan_info_no'])->where("save_status", 'Y')->orderBy('seq')->get();
			$rtmp = Func::chungDec(["loan_info_return_plan"], $rtmp);	// CHUNG DATABASE DECRYPT
			
			$origin_trade_money_sum = $trade_money_sum = 0;

			foreach( $rtmp as $vtmp )
			{
				$trade_money_sum     -= $vtmp->plan_interest;
				$trade_money_sum     -= $vtmp->plan_origin;

				$v['return_date']     = $vtmp->plan_date;
				$v['return_date_biz'] = $vtmp->plan_date_biz;
				$v['kihan_date']      = $this->loan->loanInfo['contract_end_date'];
				$v['kihan_date_biz']  = $this->loan->getBizDay($v['kihan_date']);
				
				if($origin_trade_money_sum != ($ps->sum_plan_origin + $ps->sum_plan_interest))
				{
					$v['plan_return_money']    = $vtmp->plan_money ?? 0;
					$v['plan_return_origin']   = $vtmp->plan_origin ?? 0;
					$v['plan_return_interest'] = $vtmp->plan_interest ?? 0;
					$v['plan_withholding_tax'] = $vtmp->withholding_tax ?? 0;
					$v['plan_income_tax']      = $vtmp->income_tax ?? 0;
					$v['plan_local_tax']       = $vtmp->local_tax ?? 0;
				}

				if( $trade_money_sum < 0 )
				{
					break;
				}
			}

			// 스케줄 합계액
			$vtmny = DB::table("loan_info_return_plan")->select(DB::RAW("coalesce(sum(plan_interest),0) as sum_plan_interest"))->where("loan_info_no", $v['loan_info_no'])->where("save_status", "Y")->first();

			// 입금 후 정보
			// 거래원장에 넣을 값을 여기서 다 정리해야 한다.
			$v['lack_delay_interest'] = 0;
			$v['lack_delay_money']    = 0;
			$v['lack_interest']       = 0;
			$v['misu_money']   		  = 0;
			$v['balance']      		  = $v['trade_money'];
			$v['refund_interest']     = $vtmny->sum_plan_interest;

			// 날짜
			$v['take_date']       	  = $v['trade_date'];
		}
		// 가수금송금, 가수금잡이익
		else if( $v['trade_type']=="91" || $v['trade_type']=="99" )
		{
			if( $v['over_money']>=$v['trade_money'] )
			{
				$v['over_money'] = $v['over_money'] - $v['trade_money'];
			}
			else
			{
				$v['over_money'] = 0;
			}
		}

		$vo = $v;

		// 거래원장 등록
		$rslt = DB::dataProcess("INS", "loan_info_trade", $vo, null, $loan_info_trade_no);
        if( $rslt!="Y" )
        {
            return "실행오류";
        }

        // 계약정보 업데이트
        $vl = [];
        $vl['cost_money']          = $vo['cost_money'];
        $vl['misu_money']          = $vo['misu_money'];
        $vl['lack_delay_interest'] = $vo['lack_delay_interest'];
        $vl['lack_delay_money']    = $vo['lack_delay_money'];
        $vl['lack_interest']       = $vo['lack_interest'];
        $vl['balance']             = $vo['balance'];
        $vl['over_money']          = $vo['over_money'];
        $vl['take_date']           = $vo['take_date'];
        $vl['return_date']         = $vo['return_date'];
        $vl['return_date_biz']     = $vo['return_date_biz'];
        $vl['kihan_date']          = $vo['kihan_date'];
        $vl['kihan_date_biz']      = $vo['kihan_date_biz'];
		$vl['last_trade_date']     = $vo['trade_date'];

		$vl['return_money']        = $vo['plan_return_money'] ?? 0;
		$vl['return_origin']       = $vo['plan_return_origin'] ?? 0;
		$vl['return_interest']     = $vo['plan_return_interest'] ?? 0;
		$vl['withholding_tax']     = $vo['plan_withholding_tax'] ?? 0;
		$vl['income_tax']          = $vo['plan_income_tax'] ?? 0;
		$vl['local_tax']           = $vo['plan_local_tax'] ?? 0;

		// 신규,증액시에만 업데이트
		if( $vo['trade_type']=="11" || $vo['trade_type']=="12" || $vo['trade_type']=="13" )
		{
			$vl['last_loan_date']  = $vo['trade_date'];
		}
		
        $vl['loan_info_trade_no']  = $loan_info_trade_no;
		$vl['status']              = ( $vl['balance']>0 ) ? "A" : "E" ;
		
        $rslt = DB::dataProcess('UPD', 'LOAN_INFO', $vl, ["no"=>$v['loan_info_no']]);
        if( $rslt!="Y" )
        {
            return "실행오류";
        }

		// if( $vl['status']!="E" )
		// {
		// 	$rslt = Loan::updateLoanInfoInterest($v['loan_info_no'], date("Ymd"));
		// 	if( $rslt!="Y" )
		// 	{
		// 		return "이자계산오류";
		// 	}			
		// }

		return $loan_info_trade_no;
	}

	/**
	* 출금삭제
	*
	* @param   Integer $no		삭제할 loan_info_trade 의 NO
	* @param   String  $del_id	삭제한 직원사번 빈값인 경우 로그인 아이디 셋팅
	* @return  Boolean 
	*/
	public function tradeOutDelete($no, $del_id="")
	{
		$rslt = DB::table("loan_info_trade")->select("*")->WHERE("NO", $no)->WHERE("SAVE_STATUS", "Y")->WHERE("TRADE_DIV", "O")->FIRST();
		$rslt = Func::chungDec(["LOAN_INFO_TRADE"], $rslt);	// CHUNG DATABASE DECRYPT
        $vt = (Array) $rslt;
		
		if( !$vt )
		{
            return "거래원장없음";
		}
		$rslt = DB::table("loan_info")->select("*")->where("NO", $vt['loan_info_no'])->WHERE("SAVE_STATUS", "Y")->FIRST();
		$rslt = Func::chungDec(["LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT
		$vl = (Array) $rslt;

		if( !$vl )
		{
            return "여신원장없음";
		}
		if( $no!=$vl['loan_info_trade_no'] )
		{
			return "마지막거래원장아님(".$no." ".$vl['loan_info_trade_no'].")";
		}

		$vc = (Array) DB::TABLE("LOAN_INFO_TRADE")->SELECT("MAX(NO) as NO")->WHERE("LOAN_INFO_NO", $vt['loan_info_no'])->WHERE("SAVE_STATUS", "Y")->FIRST();
		if( $vc['no']!=$no )
		{
			return "마지막거래원장아님";
		}

		$loan_info_no = $vl['no'];

		if( $del_id=="" )
		{
			$del_id = Auth::id();
		}

		// 거래원장 업데이트
		$rslt = DB::dataProcess("UPD", "LOAN_INFO_TRADE", ['save_status'=>'N','del_time'=>date('YmdHis'),'del_id'=>$del_id], ['no'=>$no]);
        if( $rslt!="Y" )
        {
            return "실행오류";
        }

		// 여신원장 업데이트
		$rslt = DB::TABLE("LOAN_INFO_TRADE")->SELECT("*")->WHERE("LOAN_INFO_NO", $loan_info_no)->WHERE("SAVE_STATUS", "Y")->ORDERBY("NO","DESC")->FIRST();
		$rslt = Func::chungDec(["LOAN_INFO_TRADE"], $rslt);	// CHUNG DATABASE DECRYPT
		$vo = (Array) $rslt;

		if( $vo )
		{// PLAN_max
			$ps = DB::table("loan_info_return_plan")->select(DB::RAW("coalesce(sum(plan_origin),0) as sum_plan_origin"), DB::RAW("coalesce(sum(plan_interest),0) as sum_plan_interest"))->where("loan_info_no", $loan_info_no)->where("save_status", 'Y')->first();

			// PLAN
			$rtmp = DB::table("loan_info_return_plan")->select("*")->where("loan_info_no", $loan_info_no)->where("save_status", 'Y')->orderBy('seq')->get();
			$rtmp = Func::chungDec(["loan_info_return_plan"], $rtmp);	// CHUNG DATABASE DECRYPT
			
			$origin_trade_money_sum = $trade_money_sum = 0;

			foreach( $rtmp as $vtmp )
			{
				$trade_money_sum     -= $vtmp->plan_interest;
				$trade_money_sum     -= $vtmp->plan_origin;

				$v['return_date']     = $vtmp->plan_date;
				$v['return_date_biz'] = $vtmp->plan_date_biz;
				$v['kihan_date']      = $this->loan->loanInfo['contract_end_date'];
				$v['kihan_date_biz']  = $this->loan->getBizDay($v['kihan_date']);
				
				if($origin_trade_money_sum != ($ps->sum_plan_origin + $ps->sum_plan_interest))
				{
					$v['plan_return_money']    = $vtmp->plan_money ?? 0;
					$v['plan_return_origin']   = $vtmp->plan_origin ?? 0;
					$v['plan_return_interest'] = $vtmp->plan_interest ?? 0;
					$v['plan_withholding_tax'] = $vtmp->withholding_tax ?? 0;
					$v['plan_income_tax']      = $vtmp->income_tax ?? 0;
					$v['plan_local_tax']       = $vtmp->local_tax ?? 0;
				}

				if( $trade_money_sum < 0 )
				{
					break;
				}
			}

			$vi = [];
			$vi['cost_money']          = $vo['cost_money'];
			$vi['misu_money']          = $vo['misu_money'];
			$vi['lack_delay_interest'] = $vo['lack_delay_interest'];
			$vi['lack_delay_money']    = $vo['lack_delay_money'];
			$vi['lack_interest']       = $vo['lack_interest'];
			$vi['balance']             = $vo['balance'];
			$vi['over_money']          = $vo['over_money'];
			$vi['take_date']           = $vo['take_date'];
			$vi['loan_info_trade_no']  = $vo['no'];

			$vl['return_date']         = $v['return_date'];
			$vl['return_date_biz']     = $v['return_date_biz'];
			$vl['kihan_date']          = $v['kihan_date'];
			$vl['kihan_date_biz']      = $v['kihan_date_biz'];
	
			$vl['return_money']        = $v['plan_return_money'] ?? 0;
			$vl['return_origin']       = $v['plan_return_origin'] ?? 0;
			$vl['return_interest']     = $v['plan_return_interest'] ?? 0;
			$vl['withholding_tax']     = $v['plan_withholding_tax'] ?? 0;
			$vl['income_tax']          = $v['plan_income_tax'] ?? 0;
			$vl['local_tax']           = $v['plan_local_tax'] ?? 0;

			// SELECT
			$vtmp = (Array) DB::TABLE("LOAN_INFO_TRADE")->SELECT(DB::raw("max(trade_date) as last_trade_date, max(CASE WHEN trade_div='O' and trade_type in ('11','12','13') THEN trade_date ELSE '' END ) AS last_loan_date, max(CASE WHEN trade_div='I' THEN trade_date ELSE '' END ) AS last_in_date, max(CASE WHEN trade_div='I' THEN no ELSE 0 END ) AS last_in_no"))->WHERE("LOAN_INFO_NO", $loan_info_no)->WHERE("SAVE_STATUS", "Y")->FIRST();
			$vi['last_trade_date']     = $vtmp['last_trade_date'];
			$vi['last_loan_date']      = $vtmp['last_loan_date'];
			$vi['last_in_date']        = $vtmp['last_in_date'];

			// 마지막 입금액 가져오기
			$vi['last_in_money']   = 0;
			if(isset($vtmp['last_in_no']) && $vtmp['last_in_no']>0)
			{
				$vtmp2 = (Array) DB::table("loan_info_trade")->select('trade_money')->WHERE('no', $vtmp['last_in_no'])->FIRST();
				$vi['last_in_money']   = $vtmp2['trade_money'];
			}
			

			if( $vo['balance']==0)
			{
				$vi['status'] = "E";
			}
			else
			{
				$vi['status'] = "A";
			}
		}
		else
		{
			$vi = [];			
			$vi['cost_money']          = 0;
			$vi['misu_money']          = 0;
			$vi['lack_delay_interest'] = 0;
			$vi['lack_delay_money']    = 0;
			$vi['lack_interest']       = 0;
			$vi['balance']             = 0;
			$vi['refund_interest']     = 0;
			$vi['over_money']          = 0;
			$vi['take_date']           = $vl['loan_date'];
			$vi['return_date']         = "";
			$vi['return_date_biz']     = "";
			$vi['kihan_date']          = "";
			$vi['kihan_date_biz']      = "";
			$vi['last_trade_date']     = "";
			$vi['last_loan_date']      = "";
			$vi['last_in_date']        = "";
			$vi['last_in_money']       = 0;
			$vi['loan_info_trade_no']  = 0;

			// 이자가 안돌기 때문에, 상태등도 업데이트 해줘야 한다.
			$vi['interest_sum']        = 0;
			$vi['charge_money']        = 0;
			$vi['fullpay_money']       = 0;
			$vi['status'] 			   = "N";
			$vi['delay_term']          = 0;
			$vi['interest_term']       = 0;
			$vi['delay_money_term']    = 0;
			$vi['delay_interest_term'] = 0;

			$vi['return_money']         = 0;
			$vi['return_origin']        = 0;
			$vi['return_interest']      = 0;
			$vi['withholding_tax']      = 0;
			$vi['income_tax']           = 0;
			$vi['local_tax']            = 0;
		}
		
        $rslt = DB::dataProcess('UPD', 'LOAN_INFO', $vi, ["no"=>$loan_info_no]);
        if( $rslt!="Y" )
        {
            return "실행오류";
        }

		// if( $vo && $vi['status']!="E" )
		// {
        // 	$rslt = Loan::updateLoanInfoInterest($loan_info_no, date("Ymd"));
		// 	if( $rslt!="Y" )
		// 	{
		// 		return "이자계산오류";
		// 	}
		// }
		return true;
	}

	/**
	* 금액배분
	*
	* @param  Integer  입금 또는 감면금액
	* @param  Integer  대상금액
	* @return Integer  처리한 금액 (감면처리, 입금처리)
	*/
	public static function divMoney( &$money, &$target_money, &$sum_money=null )
	{
		$result_money = ( $target_money>$money ) ? $money : $target_money ;
		$money       -= $result_money;
		$target_money-= $result_money;

		if( isset($sum_money) )
		{
			$sum_money-= $result_money;
		}
		return $result_money;
	}
}