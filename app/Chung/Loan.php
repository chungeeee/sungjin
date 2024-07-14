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

class Loan
{

	public $no;
	public $loanInfo, $rateInfo, $cdayInfo;
	public $holiday  = Array();
	public $planInfo = Array();

	public $interestInfo = Array();

	public static $bojungDay = 1;	// PMIS 는 보정일수 사용하지 않음. 2023.09.05 노현정 부장 확인. -> 자유상환의 경우에도 해당 상환방식을 운용하지 않으므로 영향없을것으로 판단됨

	public $set_interest_detail_chk = false;

	public function __construct($param, $isNew=false)
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

		if( is_numeric($param) )
		{
			$no = $param;
			$this->no = $no;
			//log::Debug("LOAN NO = ".$no);
			$rslt = $this->setLoanInfo($no, $isNew);
			
			if( $rslt==false )
			{
				$this->clearLoanInfo();
			}
			return $rslt;
		}
		// 파라미터에 의한 값 셋팅
		else if( is_array($param) )
		{
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
				$param['contract_end_date'] = Loan::addMonth($param['loan_date'], $param['loan_term']);
			}
			
			$param['monthly_return_money'] = 0;
			$param['sanggak_interest']    = 0;
			$param['settle_interest']     = 0;
			$param['lack_interest']       = 0;
			$param['lack_delay_money']    = 0;
			$param['lack_delay_interest'] = 0;
			$param['misu_money']   = 0;
			$param['cost_money']   = 0;
			$param['over_money']   = 0;

			$this->loanInfo = $param;
			$this->rateInfo = Array( $param['loan_date'] => Array( "rate_date" => $param['loan_date'], "loan_rate" => $param['loan_rate'], "loan_delay_rate" => $param['loan_delay_rate'] ) );
			$this->cdayInfo = Array( $param['loan_date'] => Array( "cday_date" => $param['loan_date'], "contract_day" => $param['contract_day'] ) );
			$this->planInfo = $this->buildPlanData($param['take_date'], $param['monthly_return_money']);

			if( !isset($param['return_date']) || !$param['return_date'] )
			{
				$this->loanInfo['return_date'] = $this->getNextReturnDate($param['take_date'],   $param['contract_day']);
			}
			if( !isset($param['kihan_date']) || !$param['kihan_date'] )
			{
				$this->loanInfo['kihan_date']  = $this->getNextKihanDate($param['return_date'],   $param['contract_day']);
			}
			$this->loanInfo['return_date_biz'] = $this->getBizDay($param['return_date']);
			$this->loanInfo['kihan_date_biz']  = $this->getBizDay($param['kihan_date']);
		}
		else
		{
			return false;
		}
	}

	/**
	* 기준일까지의 이자 정보 ( 기본값 적용 후, 상환방법별 메소드 분기처리 )
	*
	* @param  
	* @return 
	*/
	public function getInterest($today)
	{
		$this->interestInfo = Array();
		if( sizeof($this->loanInfo)==0 )
		{
			Log::debug("Loan Object의 계약정보가 등록되지 않았습니다.");
			return false;
		}

		$today = str_replace("-","",$today);

		$val['return_method_cd']     = $this->loanInfo['return_method_cd'];

		$val['today']                = $today;
		$val['balance']              = $this->loanInfo['balance'] * 1;
		$val['misu_money']           = $this->loanInfo['misu_money'] * 1;
		$val['cost_money']           = $this->loanInfo['cost_money'] * 1;
		$val['over_money']           = $this->loanInfo['over_money'] * 1;

		$val['sanggak_interest']     = $this->loanInfo['sanggak_interest'] * 1;
		$val['settle_interest']      = $this->loanInfo['settle_interest'] * 1;
		$val['lack_interest']        = $this->loanInfo['lack_interest'] * 1;
		$val['lack_delay_money']     = $this->loanInfo['lack_delay_money'] * 1;
		$val['lack_delay_interest']  = $this->loanInfo['lack_delay_interest'] * 1;
	
		$val['take_date']            = $this->loanInfo['take_date'];
		$val['return_date']          = $this->loanInfo['return_date'];
		$val['return_date_biz']      = $this->loanInfo['return_date_biz'];
		$val['kihan_date']           = $this->loanInfo['kihan_date'];
		$val['kihan_date_biz']       = $this->loanInfo['kihan_date_biz'];

		$val['contract_end_date']    = $this->loanInfo['contract_end_date'];
		$val['monthly_return_money'] = $this->loanInfo['monthly_return_money'];
		$val['legal_rate']           = ( isset($this->loanInfo['legal_rate']) && $this->loanInfo['legal_rate']>0 ) ? $this->loanInfo['legal_rate'] : Vars::$curMaxRate ;
		$val['loan_date']	         = $this->loanInfo['loan_date'];

		if( !$val['balance'] && !$this->loanInfo['interest_sum'])
		{
			Log::debug("투자잔액이 없습니다.");
			return false;
		}

		$val = $this->getInterestPlan($val);

		// 상환일이자 - 이자만계산
		$valr = $val;
		$valr['today'] = $val['return_date'];
		$valr = $this->getInterestFree($valr);

		$val['return_date_interest'] = isset($val['settle_interest']) ? $val['settle_interest'] : 0 ;
		$val['return_date_interest']+= $val['lack_interest'] + $val['lack_delay_money'] + $val['lack_delay_interest'];
		$val['return_date_interest']+= $val['misu_money'];
		$val['return_date_interest']+= $valr['interest'] + $valr['delay_money'] + $valr['delay_interest'];

		log::debug("RETURN_DATE     = ".$val['return_date']);
		log::debug("RETURN_DATE_INT = ".$val['return_date_interest']);

		// 연체구간이자 - 나이스에 등록하는 회차별 이자 ++++++++++++++++++++++++++++++++++++++++
		$val['gugan_interest_sum']  = 0;
		$val['gugan_interest_date'] = "";

		// 이자합계
		$val['interest_sum'] = 0;
		$val['interest_sum'] += isset($val['sanggak_interest']) ? $val['sanggak_interest'] : 0 ;
		$val['interest_sum'] += isset($val['settle_interest'])  ? $val['settle_interest']  : 0 ;
		$val['interest_sum'] += $val['lack_interest'] + $val['lack_delay_money'] + $val['lack_delay_interest'];
		$val['interest_sum'] += $val['misu_money'];
		$val['interest_sum'] += $val['interest'];

		// 완납시의 조기상환수수료 계산
		$val['return_fee_rate'] = $this->getReturnFeeRate($today);
		$val['return_fee_div']  = "";
		$val['return_fee_max']  = 0;
		$val['return_fee']      = 0;

		$val['dambo_set_fee']   = 0;

		// 완납금액
		$val['fullpay_money']   = $val['balance'] + $val['interest_sum'];

		$this->interestInfo     = $val;

		return $val;
	}

	/**
	* 스케줄에의한 이자 구하기
	*
	* @param  Array - 계약정보배열
	* @return Array - 계약정보배열에 이자정보 추가 응답
	*/
	public function getInterestPlan($val)
	{
		if( sizeof($this->planInfo)==0 )
		{
			return false;
		}

		// 정상이자
		$val['interest']             = 0;
		$val['interest_term']        = 0;
		$val['interest_sdate']       = "";
		$val['interest_edate']       = "";

		// 연체이자(원금분)
		$val['delay_money']          = 0;
		$val['delay_money_term']     = 0;
		$val['delay_money_sdate']    = "";
		$val['delay_money_edate']    = "";

		// 연체이자(이자분)
		$val['delay_interest']       = 0;
		$val['delay_interest_term']  = 0;
		$val['delay_interest_sdate'] = "";
		$val['delay_interest_edate'] = "";

		log::debug(print_r($val,1));
		// 계산할 스케줄만 추출 - 상환일 이후
		$array_plans = Array();
		foreach( $this->planInfo as $v )
		{
			if( $v['plan_date'] >= $val['return_date'])
			{
				$array_plans[$v['plan_date']] = $v;
			}
		}
		log::debug(print_r($array_plans,1));

		$balance   = $val['balance'];

		// 추출된 스케줄로 계산시작
		if( sizeof($array_plans)>0 )
		{
			foreach( $array_plans as $v )
			{
				// 정상이자
				$v['interest']             = $v['plan_interest'];
				$v['interest_term']        = $v['plan_interest_term'];
				$v['interest_sdate']       = $v['plan_interest_sdate'];
				$v['interest_edate']       = $v['plan_interest_edate'];

				// 연체이자(원금분)
				$v['delay_money']          = 0;
				$v['delay_money_term']     = 0;
				$v['delay_money_sdate']    = "";
				$v['delay_money_edate']    = "";
				// 연체이자(이자분)
				$v['delay_interest']       = 0;
				$v['delay_interest_term']  = 0;
				$v['delay_interest_sdate'] = "";
				$v['delay_interest_edate'] = "";

				$array_plans[$v['plan_date']] = $v;
			}
		}

		$val['plan_interest']         = 0;
		$val['plan_origin']           = 0;
		$val['plan_money']            = 0;
		$val['charge_money']          = 0;
		$val['charge_origin']         = 0;
		$val['charge_interest']       = 0;
		$val['charge_delay_money']    = 0;
		$val['charge_delay_interest'] = 0;
		$val['no_charge_interest']    = 0;
		$val['no_charge_origin']      = 0;

		// 합계 처리
		foreach( $array_plans as $key => $v )
		{
			if( $v['interest']>0 )
			{
				$val['interest']       	  += $v['interest'];
				$val['interest_sdate'] 	  = min( ( $val['interest_sdate'] ? $val['interest_sdate'] : "99991231" ), $v['interest_sdate'] );
				$val['interest_edate'] 	  = max( ( $val['interest_edate'] ? $val['interest_edate'] : "00000000" ), $v['interest_edate'] );
				$val['interest_term']  	  = Loan::dateTerm($val['interest_sdate'], $val['interest_edate'], 1);
			}
			
			$val['plan_money']    		  += (isset($v['plan_money']))    ? $v['plan_money']    : 0 ;
			$val['plan_interest'] 		  += (isset($v['plan_interest'])) ? $v['plan_interest'] : 0 ;
			$val['plan_origin']   		  += (isset($v['plan_origin']))   ? $v['plan_origin']   : 0 ;
			
			$val['charge_origin']         += (isset($v['plan_origin']))    ? $v['plan_origin']    : 0 ;
			$val['charge_interest']       += (isset($v['interest']))       ? $v['interest']       : 0 ;
			$val['charge_delay_money']    += (isset($v['delay_money']))    ? $v['delay_money']    : 0 ;
			$val['charge_delay_interest'] += (isset($v['delay_interest'])) ? $v['delay_interest'] : 0 ;

			$val['plan_date'] = $key;

			// 회차별청구금액 더해
			$plan_charge_money = (isset($v['plan_origin']))    ? $v['plan_origin']    : 0 ;
			$plan_charge_money+= (isset($v['interest']))       ? $v['interest']       : 0 ;
			$plan_charge_money+= (isset($v['delay_money']))    ? $v['delay_money']    : 0 ;
			$plan_charge_money+= (isset($v['delay_interest'])) ? $v['delay_interest'] : 0 ;
			
			$array_plans[$key]['plan_charge_money'] = $plan_charge_money;
		}

		$val['return_plan']      = $array_plans;

		$val['charge_money']     = (isset($val['misu_money']) ? $val['misu_money']:0) + ( $val['charge_origin'] + $val['charge_interest'] + $val['charge_delay_money'] + $val['charge_delay_interest'] );	//비용안더해짐
		$val['no_charge_origin'] = $val['balance'] - $val['charge_origin'];

		log::debug(print_r($val,1));
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

		// 정상구간 이자,금리셋
		//$sdate = $this->addDay($val['take_date']);
		// 초일산입으로 변경 - 2023.09.05 노현정 부장 요청
		$sdate = $val['take_date'];
		$edate = min($val['today'], $delay_basis_date);
		//for( $d = $sdate; $d <= $edate; $d = $this->addDay($d) )
		// 초일산입으로 변경 - 2023.09.05 노현정 부장 요청
		for( $d = $sdate; $d < $edate; $d = $this->addDay($d) )
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

			// 상세정보등록 - 검증용
			if( $this->set_interest_detail_chk )
			{
				$key = "A_".(string) $rate;
				$val['interest_detail_chk'][$key]['status'] = "A";
				$val['interest_detail_chk'][$key]['yunRate'] = ( $rate==(string)$loan_rate ) ? "N" : "Y" ;
				$val['interest_detail_chk'][$key]['loan_rate'] = $loan_rate;
				$val['interest_detail_chk'][$key]['rate_term'] = $days;
				if( !isset($val['interest_detail_chk'][$key]['rate_sdate']) )
				{
					$val['interest_detail_chk'][$key]['rate_sdate'] = $d;
				}
				$val['interest_detail_chk'][$key]['rate_edate'] = $d;
			}
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


		// 연체구간 이자,금리셋
		//$sdate = $this->addDay($delay_basis_date);
		// 초일산입으로 변경 - 2023.09.05 노현정 부장 요청
		$sdate = $delay_basis_date;
		$edate = $val['today'];
		//for( $d = $sdate; $d <= $edate; $d = $this->addDay($d) )
		// 초일산입으로 변경 - 2023.09.05 노현정 부장 요청
		for( $d = $sdate; $d < $edate; $d = $this->addDay($d) )
		{
			$loan_delay_rate = $this->getCurrRate($d)['loan_rate'];		// 연체중이더라도 기한이익상실전까지는 정상금리를 적용
			$rate = (string) $this->yunRate( $loan_delay_rate, $d );
			
			$days = $this->countUp($array_rate_set['B'][$rate]);

			$val['delay_interest_term']++;
			if( !$val['delay_interest_sdate'] )
			{
				$val['delay_interest_sdate'] = $d;
			}
			$val['delay_interest_edate'] = $d;

			// 상세정보등록 - 검증용
			if( $this->set_interest_detail_chk )
			{
				$key = "B_".(string) $rate;
				$val['interest_detail_chk'][$key]['status'] = "B";
				$val['interest_detail_chk'][$key]['yunRate'] = ( $rate==(string)$loan_delay_rate ) ? "N" : "Y" ;
				$val['interest_detail_chk'][$key]['loan_rate'] = $loan_delay_rate;
				$val['interest_detail_chk'][$key]['rate_term'] = $days;
				if( !isset($val['interest_detail_chk'][$key]['rate_sdate']) )
				{
					$val['interest_detail_chk'][$key]['rate_sdate'] = $d;
				}
				$val['interest_detail_chk'][$key]['rate_edate'] = $d;
			}
		}

		$val['interest']       = $this->getInterestTerm($array_rate_set['A'], $base_money);
		$val['delay_interest'] = $this->getInterestTerm($array_rate_set['B'], $base_money);

		return $val;
	}

	/**
	* 다음상환일 구하기 ( cdayInfo 셋팅전제 )
	*
	* @param  Date   - 기준일
	* @param  String - 약정일 (기본값은 cdayInfo에서 기준일로 참조 )
	* @return Date   - 다음상환일
	*/
	public function getNextReturnDate($today, $contractDay="", $scheduleApply="Y")
	{
		if( !isset($this->planInfo) || sizeof($this->planInfo)==0 )
		{
			return false;
		}

		$today = Loan::addDay($today, static::$bojungDay);

		$regDt = "";
		$plan = $this->planInfo;
		krsort($plan);
		// 스케줄 납입일자 기준 역순으로 필터링 시작
		foreach($plan as $dt => $v)
		{
			// 회차별 스케줄 납입일자가 기준일보다 작아지면 종료.
			if($dt < $today) break;
			$regDt = $v['plan_date'];
		}

		// 스케줄 기반이라 리턴되는 스케줄이 없는경우는 최종회차 스케줄 납입일 이후일 것이므로 상환일을 만기일로 지정한다.
		if(empty($regDt)) $regDt = $this->loanInfo['contract_end_date'];
		$today = $regDt;

		// 상환일이 만기일을 넘을 수 없다.
		if( isset($this->loanInfo['contract_end_date']) && $this->loanInfo['contract_end_date'] && $today > $this->loanInfo['contract_end_date'] )
		{
			$today = $this->loanInfo['contract_end_date'];
		}
		
		return $today;
	}

	/**
	* 기한이익상실일 구하기 ( cdayInfo 셋팅전제 )
	*
	* @param  Date   - 기준일
	* @param  String - 약정일 (기본값은 cdayInfo에서 기준일로 참조 )
	* @return Date   - 다음상환일
	*/
	public function getNextKihanDate($return_date, $contractDay="")
	{
		// 사모사채(02) 상품은 기한이익상실일을 만기일로 지정한다. 만기일 이전은 그냥 정상상환 - 연체처리하지 않는다 함.
		$kihan_date = $this->loanInfo['contract_end_date'];

		return $kihan_date;
	}


	/**
	* 기준일의 조기상환수수료율을 가져온다. (기준일별로 변경될 수 있어서)
	*
	* @param  Date   - 기준일
	* @param  Float  - 조기상환수수료
	*/
	public function getReturnFeeRate( $today="" )
	{
		// 하드코딩 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
		$return_fee_rate = 0;
		// 하드코딩 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

		// 소수점 자리 조정
		$return_fee_rate = floor( $return_fee_rate * 100 ) / 100;
		
		return $return_fee_rate;
	}

	/**
	 * 두 날짜 사이의 단순 이자 계산 (일수 양편넣기 계산)
	 *
	 * @param   Date    이자시작일
	 * @param   Date    이자종료일
	 * @param   Float   연금리
	 * @param   Number  잔액
	 * @param   Number  분모일수 ( 기본값 0 인경우는 365(366)으로 계산한다. )
	 * @return  Number  이자
	 */	
	private static function getInterestBetweenDate($sdate, $edate, $rate, $balance, $base_days=0)
	{
		$cal_int = 0;

		for( $y=substr($sdate,0,4); $y<=substr($edate,0,4); $y++ )
		{
			$sd = max($sdate, $y."0101");
			$ed = min($edate, $y."1231");
			if( $base_days>0 )
			{
				$bd = $base_days;
			}
			else
			{
				$bd = ( (substr($sd,0,4)%4)==0 && substr($sd,0,4)>="2012" ) ? 366 : 365 ;
			}
			$dd = Loan::dateTerm($sd, $ed, 1);
			if($dd<0) $dd = 0;

			$cal_int+= floor( $balance * $rate / 100 * $dd / $bd );
		}

		return $cal_int;
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
				$interest+= floor( ( $basemoney * $cnt * ($rate/100)) / 365 );
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
	* 기준일의 약정일
	*
	* @param  Date  - 기준일
	* @return char  - 약정일 2자리
	*/	
	public function getCurrCday($today)
	{
		$val = Array('contract_day'=>'');
	
		foreach( $this->cdayInfo as $v )
		{
			if( $v['cday_date']>$today )
			{
				break;
			}
			$val = $v;
		}
		// 계약일보다 이전인 경우는 첫번째 배열을 넣어준다.
		if($val['contract_day']=='')
		{
			foreach( $this->cdayInfo as $v )
			{
				$val = $v;
				break;
			}
		}

		return $val['contract_day'];
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

	public function getBizDay($today)
	{
		$info_month = substr($today,4,2);
		
		while( in_array($today, $this->holiday) )
		{
			$today = $this->addDay($today);
		}

		// 기관차입이 아닐때만
		if($this->loanInfo['pro_cd'] != '03')
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
	* 계약번호에 의한 기본정보 셋팅 - 계약정보, 금리, 약정일
	*
	* @param  $no 
	* @return boolean
	*/
	private function setLoanInfo($no, $isNew=false)
	{
		$this->clearLoanInfo();
		$this->no = $no;

		// LOAN
		$rslt = DB::TABLE("loan_info")->SELECT("*")->WHERE('no',$no)->WHERE('save_status','Y')->FIRST();
		$rslt = Func::chungDec(["LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT
		if( !$rslt )
		{
			log::Debug("Loan(".$no.") 생성에러 : ".$no." = 계약정보를 찾을 수 없습니다.");
			return false;
		}

		// 필수값
		if( !$rslt->loan_date || !$rslt->loan_term || $rslt->loan_rate=='' || !$rslt->contract_day || !$rslt->return_method_cd )	//|| !$rslt->balance 
		{
			log::Debug("Loan(".$no.") 생성에러 : 필수정보 부족 #1");
			log::Debug("LOAN_DATE        = ".$rslt->loan_date);
			log::Debug("LOAN_TERM        = ".$rslt->loan_term);
			log::Debug("LOAN_RATE        = ".$rslt->loan_rate);
			log::Debug("CONTRACT_DAY     = ".$rslt->contract_day);
			log::Debug("RETURN_METHOD_CD = ".$rslt->return_method_cd);
			return false;
		}
		if( $rslt->status!="N" && ( !$rslt->take_date || !$rslt->return_date || !$rslt->return_date_biz ) )
		{
			log::Debug("Loan(".$no.") 생성에러 : 필수정보 부족 #2");
			log::Debug("TAKE_DATE        = ".$rslt->take_date);
			log::Debug("RETURN_DATE      = ".$rslt->return_date);
			log::Debug("RETURN_DATE_BIZ  = ".$rslt->return_date_biz);
			return false;
		}
		//log::Debug("loanInfo Set OK");

		$this->loanInfo = (Array) $rslt;
		$this->loanInfo['loan_rate']       = (float) $this->loanInfo['loan_rate'];
		$this->loanInfo['loan_delay_rate'] = (float) $this->loanInfo['loan_delay_rate'];
		$this->loanInfo['legal_rate']      = (float) $this->loanInfo['legal_rate'];

		// RATE
		$rslt = DB::TABLE("LOAN_INFO_RATE")->SELECT("rate_date, loan_rate, loan_delay_rate")->WHERE('LOAN_INFO_NO',$no)->WHERE('SAVE_STATUS','Y')->ORDERBY('RATE_DATE')->ORDERBY('SAVE_TIME')->GET();
		$rslt = Func::chungDec(["LOAN_INFO_RATE"], $rslt);	// CHUNG DATABASE DECRYPT

		foreach( $rslt as $v )
		{
			$this->rateInfo[$v->rate_date]['rate_date'] = $v->rate_date;
			$this->rateInfo[$v->rate_date]['loan_rate'] = (float) $v->loan_rate;
			$this->rateInfo[$v->rate_date]['loan_delay_rate'] = (float) $v->loan_delay_rate;
		}
		if( sizeof($this->rateInfo)==0 )
		{
			log::Debug("Loan(".$no.") 생성에러 : ".$no." = 금리정보를 찾을 수 없습니다.");
			return false;
		}
		//log::Debug("loanRate Set OK");

		// CDAY
		$rslt = DB::TABLE("LOAN_INFO_CDAY")->SELECT("cday_date, contract_day")->WHERE('LOAN_INFO_NO',$no)->WHERE('SAVE_STATUS','Y')->ORDERBY('CDAY_DATE')->ORDERBY('SAVE_TIME')->GET();
		$rslt = Func::chungDec(["LOAN_INFO_CDAY"], $rslt);	// CHUNG DATABASE DECRYPT

		foreach( $rslt as $v )
		{
			$this->cdayInfo[$v->cday_date]['cday_date'] = $v->cday_date;
			$this->cdayInfo[$v->cday_date]['contract_day'] = $v->contract_day;
		}
		if( sizeof($this->cdayInfo)==0 )
		{
			log::Debug("Loan(".$no.") 생성에러 : ".$no." = 약정일정보를 찾을 수 없습니다.");
			return false;
		}

		// PLAN
		$rslt = DB::TABLE("loan_info_return_plan")->SELECT("*")->WHERE('loan_info_no',$no)->WHERE('save_status','Y')->ORDERBY('SEQ')->ORDERBY('plan_date')->get();
		$rslt = Func::chungDec(["loan_info_return_plan"], $rslt);	// CHUNG DATABASE DECRYPT

		foreach( $rslt as $v )
		{
			$this->planInfo[$v->plan_date] = (Array) $v;
		}
		if( sizeof($this->planInfo)==0 )
		{
			log::Debug("Loan(".$no.") 생성에러 : ".$no." = 스케줄정보를 찾을 수 없습니다.");
			return false;
		}

		return true;
	}


	/**
	* 계약 기본정보 초기화 - 계약정보, 금리, 약정일
	*/
	private function clearLoanInfo()
	{
		$this->no = NULL;
		$this->loanInfo = Array();
		$this->rateInfo = Array();
		$this->cdayInfo = Array();
		//$this->holiday  = Array();
		$this->planInfo = Array();
		$this->interestInfo = Array();
	}



	/**
	* 월상환금액 구하기
	*
	* @param  Float - 이율(연)
	* @param  Int   - 상환회차(월)
	* @param  Int   - 기준잔액
	* @return Int   - 월상환금액
	*/
	public static function PMT($rate, $cnt, $money)
	{
		if( !$money || !$rate || !$cnt )
		{
			return 0;
		}
		$rate = $rate / 1200;
		$monthly_return_money = ( $money * $rate * pow( 1+$rate, $cnt ) ) / ( pow( 1+$rate, $cnt )-1 );
		return ceil( $monthly_return_money / 1000 ) * 1000;
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
     * 계약정보 업데이트, 배치 및 입금 처리 후 사용
     *
     * @param  Integer $no
     * @param  Date    $dt
     * @return view
     */
	public static function updateLoanInfoInterest($no, $dt)
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
		$LOAN = DB::TABLE("LOAN_INFO");
		$LOAN = $LOAN->SELECT("NO, LOAN_DATE, LOAN_TERM, LOAN_RATE, LOAN_DELAY_RATE, CONTRACT_DAY, RETURN_METHOD_CD, BALANCE, SETTLE_INTEREST, SANGGAK_INTEREST, COST_MONEY, INTEREST_SUM, RETURN_DATE_BIZ, STATUS, FIRST_DELAY_DATE, FULLPAY_DATE ");
		$LOAN = $LOAN->WHERE("SAVE_STATUS", "Y");
		if( $no>0 )
		{
			$LOAN = $LOAN->WHERERAW('( COALESCE(BALANCE,0) + COALESCE(SETTLE_INTEREST,0) + COALESCE(SANGGAK_INTEREST,0) + COALESCE(INTEREST_SUM,0) + COALESCE(COST_MONEY,0) + COALESCE(OVER_MONEY,0) ) > 0');
			$LOAN = $LOAN->WHEREIN('STATUS', ['A','E']);
			$LOAN->WHERE('NO',$no);
		}
		// 임시
		else 
		{
			$LOAN = $LOAN->WHERERAW('( COALESCE(BALANCE,0) + COALESCE(SETTLE_INTEREST,0) + COALESCE(SANGGAK_INTEREST,0) + COALESCE(INTEREST_SUM,0) + COALESCE(COST_MONEY,0) ) > 0');
			$LOAN = $LOAN->WHEREIN('STATUS', ['A']);
			// $LOAN->WHERE('NO', '>=', 98450);
		}
		$LOAN->ORDERBY("NO");

		$RSLT = $LOAN->GET();
		$RSLT = Func::chungDec(["LOAN_INFO"], $RSLT);	// CHUNG DATABASE DECRYPT

		foreach( $RSLT as $v )
		{
			// 기존완제건
			if($v->status=='E' && $no>0)
			{
				log::debug("일반완제 : ");
				return 'Y';
			}

			// 필수값
			if( !$v->loan_date || !$v->loan_term || $v->loan_rate=='' || $v->loan_delay_rate=='' || !$v->contract_day || !$v->return_method_cd || ( !$v->balance && !$v->settle_interest && !$v->sanggak_interest && !$v->interest_sum && !$v->cost_money ) )
			{
				Log::debug($v->no." 필수값 미등록");
				log::debug("loanInfo : ".print_r((array)$v, true));

				if( $no>0 )
				{
					return "N";
				}
				echo $v->no." 필수값 미등록\n";
				continue;
			}
			if( $no==0 )
			{
				log::debug("no=0 : ");
				echo $v->no."\n";
			}

			$loan = new Loan($v->no);
			$val = $loan->getInterest($dt);
			if( !is_array($val) )
			{
				log::debug("getinterest애러 : ");
				if( $no>0 )
				{
					Log::debug($v->no." Loan 객체 생성에러");
					return "N";
				}
				Log::debug($v->no." 이자계산 실패");
				continue;
			}

			$_DATA = [];
			$_DATA['DELAY_INTEREST']       = $val['delay_interest'];
			$_DATA['DELAY_MONEY']          = $val['delay_money'];
			$_DATA['INTEREST']             = $val['interest'];
			$_DATA['INTEREST_SUM']         = $val['interest_sum'];
			$_DATA['DELAY_TERM']           = Func::dateTerm($v->return_date_biz, $dt);
			$_DATA['DELAY_INTEREST_TERM']  = $val['delay_interest_term'];
			$_DATA['DELAY_INTEREST_SDATE'] = $val['delay_interest_sdate'];
			$_DATA['DELAY_INTEREST_EDATE'] = $val['delay_interest_edate'];
			$_DATA['DELAY_MONEY_TERM']     = $val['delay_money_term'];
			$_DATA['DELAY_MONEY_SDATE']    = $val['delay_money_sdate'];
			$_DATA['DELAY_MONEY_EDATE']    = $val['delay_money_edate'];
			$_DATA['INTEREST_TERM']        = $val['interest_term'];
			$_DATA['INTEREST_SDATE']       = $val['interest_sdate'];
			$_DATA['INTEREST_EDATE']       = $val['interest_edate'];
			$_DATA['CHARGE_MONEY']         = $val['charge_money'];
			$_DATA['FULLPAY_MONEY']        = $val['fullpay_money'];
			$_DATA['calc_date']            = $dt;

			if( $v->status=="A")
			{
				$_DATA['STATUS'] = "A" ;
			}

			$_DATA['RETURN_DATE_INTEREST'] = $val['return_date_interest'];		// 상환일이자 내부에서 계산하는것으로 바꿈
			$_DATA['GUGAN_INTEREST_SUM']   = $val['gugan_interest_sum'];		// 회차구간 연체이자 - 나이스에 등록한다. +++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++
			$_DATA['GUGAN_INTEREST_DATE']  = $val['gugan_interest_date'];		// 회차구간 연체이자 기준일 ++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++++

			// 미수수익 START ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			// 정상 = 당일이자
			if( $_DATA['STATUS']=="A" )
			{
				$_DATA['MISU_REV_MONEY'] = $_DATA['INTEREST_SUM'];
			}
			// 그외
			else
			{
				$_DATA['MISU_REV_MONEY'] = 0;
			}
			// 미수수익 END //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
			
			log::debug("2 : ");

			$rslt = DB::dataProcess('UPD', 'LOAN_INFO', $_DATA, ["no"=>$v->no]);
			if( $rslt!="Y" && $no>0 )
			{
				log::debug("실행오류 : ");
				return "실행오류";
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
			return ( isset($rslt) ) ? $rslt : "실행오류" ;
		}
	}



    /**
     * 계약정보 등록
     *
     * @param  Integer $no
     * @param  Date    $dt
     * @return view
     */
	public static function insertLoanInfo($val)
	{
		if( !is_array($val) )
		{
			$val = ( Array ) $val;
		}

		if( !$val['cust_info_no'] )
		{
			return "차입자번호는 필수입니다.";
		}
		if( !$val['loan_usr_info_no'] )
		{
			return "투자자번호는 필수입니다.";
		}
		if( !$val['pro_cd'] )
		{
			return "상품코드는 필수입니다.";
		}
		if( !$val['loan_date'] )
		{
			return "투자일은 필수입니다.";
		}
		if( !$val['loan_term'] )
		{
			return "투자기간은 필수입니다.";
		}
		if( !$val['loan_money'] )
		{
			return "투자금액은 필수입니다.";
		}
		if( !$val['loan_rate'] || !$val['loan_delay_rate'] || !$val['invest_rate'] )
		{
			return "금리는 필수입니다.";
		}
		if( !$val['contract_date'] )
		{
			return "계약일은 필수입니다.";
		}
		if( !$val['contract_end_date'] )
		{
			return "계약만기일은 필수입니다.";
		}
		if( !$val['contract_day'] )
		{
			return "약정일은 필수입니다.";
		}
		if($val['pro_cd'] != '03')
		{
			if( !$val['return_method_cd'] )
			{
				return "상환방법은 필수입니다.";
			}

			$val['viewing_return_method'] = $val['return_method_cd'];
		}
		else
		{
			if( !$val['viewing_return_method'] )
			{
				return "상환방법은 필수입니다.";
			}

			$val['return_method_cd']  = 'F';
		}
		
		$val['loan_bank_nick'] 		  = isset($val['loan_bank_nick']) ? $val['loan_bank_nick'] : $val['loan_bank_name'];
		$val['monthly_return_gubun']  = "";
        $val['status']      		  = "N";
        $val['save_status'] 		  = "Y";
        $val['save_id']     		  = Auth::id();
        $val['save_time']   		  = date("YmdHis");
		$val['take_date']			  = $val['loan_date'];
		$val['loan_pay_term'] 		  = $val['pay_term'] = isset($val['loan_pay_term']) ? $val['loan_pay_term'] : '1';			// 차주 이자납입주기

		DB::beginTransaction();

		if(empty($val['save_id']))
		{
			$val['save_id'] = 'SYSTEM';
		}
		
		$uv = DB::TABLE('loan_usr_info')->select('tax_free')->where('no',$val['loan_usr_info_no'])->where("save_status", 'Y')->FIRST();
		$val['tax_free'] = !empty($uv->tax_free) ? $uv->tax_free : "N";

        $vl = DB::TABLE("LOAN_INFO")->select("MAX(LOAN_SEQ) as seq")->where("cust_info_no", $val['cust_info_no'])->where("save_status", 'Y')->FIRST();
        $val['loan_seq'] = ( $vl->seq ) ? $vl->seq + 1 : 1 ;

        $vu = DB::TABLE("LOAN_INFO")->select("MAX(INV_SEQ) as seq")->where("investor_no", $val['investor_no'])->where("cust_info_no", $val['cust_info_no'])->where("save_status", 'Y')->FIRST();
        $val['inv_seq'] = ( $vu->seq ) ? $vu->seq + 1 : 1 ;

		$rslt = DB::dataProcess('INS', 'LOAN_INFO', $val, null, $loan_info_no);

		if( $rslt!="Y" )
		{
			DB::rollBack();
			return "실행오류#0";
		}

		DB::commit();

		return $loan_info_no;
	}
}