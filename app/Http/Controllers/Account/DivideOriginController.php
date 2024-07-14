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
use Loan;
use Trade;
use Invest;
use Carbon;

class DivideOriginController extends Controller
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
     * 투자원금조정 - 입력창
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function divideOriginForm(Request $request)
    {
        $status_color = "#6c757d";
        
        $v = DB::table("loan_info")->join("loan_usr_info", "loan_usr_info.no", "=", "loan_info.loan_usr_info_no")
                                    ->select("loan_info.*, loan_usr_info.name")
                                    ->where("loan_info.no",$request->loan_info_no)
                                    ->where("loan_usr_info.save_status","Y")
                                    ->first();

        $v = Func::chungDec(["loan_info","loan_usr_info"], $v);	// CHUNG DATABASE DECRYPT

        return view('account.divideoriginform')->with("v", $v)->with("status_color", $status_color);
    }

    /**
     * 투자원금조정 - 입력창 액션
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function divideOriginFormAction(Request $request)
    {
        $param     = $request->all();
        
        $save_id   = Auth::id();
        $save_time = date("YmdHis");

        $_DIVIDE = [];
        $_DIVIDE['loan_info_no'] = $param['loan_info_no'];
        $_DIVIDE['trade_date']   = preg_replace('/[^0-9]/', '', $param['trade_date']);
        $_DIVIDE['trade_money']  = preg_replace('/[^0-9]/', '', $param['trade_money']);

        $loanInfo = DB::table("loan_info")->where("no", $param['loan_info_no'])->where("save_status", 'Y')->first();
        $loanInfo = Func::chungDec(["loan_info"], $loanInfo);	// CHUNG DATABASE DECRYPT

        // 조정 후 남는 잔액이 0원 미만일 경우 break; 누르는 1초 순간에 발생할 수도 있잖아요
        if(empty($loanInfo->balance))
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "해당 계약의 잔액은 0원입니다.";
            return $array_result;
        }
        if(empty($_DIVIDE['trade_money']))
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "해당 계약의 잔액조정금액이 0원입니다.";
            return $array_result;
        }

        # 제이핀 FMS 사채 인수인계 자료에 있는 세팅조건이지만 BAND 전산 요청으로 인한 주석
        // if( empty($loanInfo->return_interest_sum) && empty($loanInfo->return_origin_sum) )
        // {
        //     $array_result['rs_code'] = "N";
        //     $array_result['result_msg'] = "실지급이력이 존재하지 않아서 원금상환이 불가합니다.";
        //     return $array_result;
        // }
        
        if( ($loanInfo->balance - $_DIVIDE['trade_money']) < 0 )
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "해당 계약의 잔액보다 잔액조정금액이 클 수 없습니다.";
            return $array_result;
        }

        $planInfo = DB::table('account_transfer')->select('account_transfer.no')
                                                ->join("loan_info","loan_info.no","=","account_transfer.loan_info_no")
                                                ->join("cust_info","cust_info.no","=","loan_info.cust_info_no")
                                                ->join("loan_usr_info", "loan_info.loan_usr_info_no", "=", "loan_usr_info.no")
                                                ->where('account_transfer.loan_info_no', $param['loan_info_no'])
                                                ->whereIn('account_transfer.status', ['S', 'W', 'A'])
                                                ->where('loan_info.save_status', 'Y')
                                                ->where('cust_info.save_status', 'Y')
                                                ->where('loan_usr_info.save_status', 'Y')
                                                ->where('account_transfer.save_status', 'Y')
                                                ->orderby('account_transfer.no')
                                                ->first();
        $planInfo = Func::chungDec(["account_transfer"], $planInfo);	// CHUNG DATABASE DECRYPT

        // 송금대기상태에 있으면 안됩니다.
        if(!empty($planInfo->balance))
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "해당 계약에서 송금대기상태인 항목이 있습니다.";
            return $array_result;
        }

        DB::beginTransaction();

        $invest     = new Invest($loanInfo->no);
        $divideInterest = $invest->getDivideInterest(Array("sdate"=>$loanInfo->contract_date,"edate"=>$_DIVIDE['trade_date']));
        $array_plan = $invest->reBuildPlanData($loanInfo->contract_date, $_DIVIDE['trade_date'], $_DIVIDE['trade_money'], ($loanInfo->return_interest_sum ?? 0));
        $rslt       = $invest->savePlan($array_plan);
        if( $rslt != "Y" )
        {
            DB::rollBack();
            $array_result['rs_code']    = "N";
            $array_result['result_msg'] = "분배예정스케줄 수정 Error";
            return $array_result;
        }

        // PLAN_max
        $ps = DB::table("loan_info_return_plan")->select(DB::RAW("coalesce(sum(plan_origin),0) as sum_plan_origin"), DB::RAW("coalesce(sum(plan_interest),0) as sum_plan_interest"))->where("loan_info_no", $param['loan_info_no'])->where("save_status", 'Y')->first();

        // PLAN
        $rtmp = DB::table("loan_info_return_plan")->select("*")->where("loan_info_no", $param['loan_info_no'])->where("save_status", 'Y')->orderBy('seq')->get();
        $rtmp = Func::chungDec(["loan_info_return_plan"], $rtmp);	// CHUNG DATABASE DECRYPT

        // 차입금 데이터 이후입금 합계액
        $vtmny = DB::table("loan_info_trade")->select(DB::RAW("coalesce(sum(TRADE_MONEY),0) as sum_trade_money"))->where("save_status", "Y")->where("trade_div", "I")->where("loan_info_no", $param['loan_info_no'])->first();

        $origin_trade_money_sum = $trade_money_sum = $vtmny->sum_trade_money;

        $_PLAN = array();
        foreach( $rtmp as $vtmp )
        {
            $trade_money_sum            -= $vtmp->plan_interest;
            $trade_money_sum            -= $vtmp->plan_origin;

            $_PLAN['return_date']         = $vtmp->plan_date;
            $_PLAN['return_date_biz']     = $vtmp->plan_date_biz;
            
            if($origin_trade_money_sum != ($ps->sum_plan_origin + $ps->sum_plan_interest))
            {
                $_PLAN['return_money']    = $vtmp->plan_money ?? 0;
                $_PLAN['return_origin']   = $vtmp->plan_origin ?? 0;
                $_PLAN['return_interest'] = $vtmp->plan_interest ?? 0;
                $_PLAN['withholding_tax'] = $vtmp->withholding_tax ?? 0;
                $_PLAN['income_tax']      = $vtmp->income_tax ?? 0;
                $_PLAN['local_tax']       = $vtmp->local_tax ?? 0;
            }

            if( $trade_money_sum < 0 )
            {
                break;
            }
        }

        $rslt = DB::dataProcess("UPD", "loan_info", $_PLAN, ["no" => $param['loan_info_no']]);
        // 오류 업데이트 후 쪽지 발송
        if( $rslt!="Y" )
        {
            DB::rollBack();
            
            return '계약정보 업데이트를 실패했습니다.('.$param['loan_info_no'].')';
        }

        $loanInfo = DB::table("loan_info")->where("no", $param['loan_info_no'])->where("save_status", 'Y')->first();
        $loanInfo = Func::chungDec(["loan_info"], $loanInfo);	// CHUNG DATABASE DECRYPT
        
        // 선계산 이자
        $interest = [];
        $interest['return_income_tax']    = ceil( $divideInterest['interest'] * ($loanInfo->income_rate / 100) / 10 ) * 10;	    // 소득세 : TRUNC(세전이자금액*원천징수세율/100/10,0)*10
        $interest['return_local_tax']     = floor( $interest['return_income_tax'] * ($loanInfo->local_rate / 100) / 10 ) * 10;	// 지방소득세 : TRUNC( 소득세*0.01,0)*10  

        // 입금배열
        $array_insert = Array();
        $array_insert['action_mode']      = "INSERT";
        $array_insert['trade_type']       = "06";
        $array_insert['trade_path_cd']    = "1";
        $array_insert['investor_no']      = $loanInfo->investor_no;
        $array_insert['cust_info_no']     = $loanInfo->cust_info_no;
        $array_insert['loan_usr_info_no'] = $loanInfo->loan_usr_info_no;
        $array_insert['loan_info_no']     = $loanInfo->no;
        $array_insert['trade_money']      = $_DIVIDE['trade_money'] + $divideInterest['interest'] - ($loanInfo->return_interest_sum ?? 0);
        $array_insert['trade_date']       = $_DIVIDE['trade_date'];
        $array_insert['lose_money']       = $_DIVIDE['lose_money'] = $loanInfo->balance - $_DIVIDE['trade_money'];
        
        $array_insert['interest_sdate']   = $loanInfo->contract_date;
        $array_insert['interest_edate']   = $_DIVIDE['trade_date'];

        $date1 = Carbon::parse($loanInfo->contract_date);
        $date2 = Carbon::parse($_DIVIDE['trade_date']);
        $array_insert['return_intarval']  = $date1->diffInDays($date2);                                              // 거치기간
        
        // 이율
        $array_insert['invest_rate']      = $loanInfo->invest_rate;
        $array_insert['income_rate']      = $loanInfo->income_rate;
        $array_insert['local_rate']       = $loanInfo->local_rate;

        // 원천징수
        $array_insert['withholding_tax']  = $interest['return_income_tax'] + $interest['return_local_tax'];
        $array_insert['income_tax']       = $interest['return_income_tax'];
        $array_insert['local_tax']        = $interest['return_local_tax'];
        $array_insert['fullpay_cd']       = "1";
        $array_insert['memo']             = "원금상환 처리";
        
        // 계좌
        $array_insert['loan_bank_cd']     = $loanInfo->loan_bank_cd;
        $array_insert['loan_bank_ssn']    = $loanInfo->loan_bank_ssn;
        $array_insert['loan_bank_name']   = $loanInfo->loan_bank_name;
        $array_insert['cust_bank_cd']     = $loanInfo->cust_bank_cd;
        $array_insert['cust_bank_ssn']    = $loanInfo->cust_bank_ssn;
        $array_insert['cust_bank_name']   = $loanInfo->cust_bank_name;

        $trade              = new Trade($array_insert['loan_info_no']);
        $loan_info_trade_no = $trade->tradeInInsert($array_insert);
        if( !is_numeric($loan_info_trade_no) )
        {
            DB::rollBack();
            $array_result['rs_code']    = "N";
            $array_result['result_msg'] = "거래원장 등록 Error";
            return $array_result;
        }
        
        // 부분 중도 상환시 추가계약생성
        if($_DIVIDE['lose_money'] > 0)
        {
            // 계약등록
            $LOAN['cust_info_no']         = $loanInfo->cust_info_no;
            $LOAN['cust_bank_name']       = $loanInfo->cust_bank_name;
            $LOAN['cust_bank_cd']         = $loanInfo->cust_bank_cd;
            $LOAN['cust_bank_ssn']        = $loanInfo->cust_bank_ssn;
            $LOAN['loan_usr_info_no']     = $loanInfo->loan_usr_info_no;
            $LOAN['investor_no']          = $loanInfo->investor_no;
            $LOAN['investor_type']        = $loanInfo->investor_type;
            $LOAN['loan_bank_nick']       = $loanInfo->loan_bank_nick;
            $LOAN['loan_bank_name']       = $loanInfo->loan_bank_name;
            $LOAN['loan_bank_cd']         = $loanInfo->loan_bank_cd;
            $LOAN['loan_bank_ssn']        = $loanInfo->loan_bank_ssn;
            $LOAN['loan_bank_status']     = 'N';
            $LOAN['handle_code']          = $loanInfo->handle_code;
            $LOAN['contract_date']        = $_DIVIDE['trade_date'];
            $LOAN['contract_end_date']    = date('Ymd', strtotime(date('Ymd', strtotime($_DIVIDE['trade_date'].' +1 years'))));
            $LOAN['contract_day']         = $loanInfo->contract_day;
            $LOAN['loan_money']           = $_DIVIDE['lose_money'];
            $LOAN['pro_cd']               = $loanInfo->pro_cd;
            $LOAN['return_method_cd']     = $loanInfo->return_method_cd;
            $LOAN['viewing_return_method'] = $loanInfo->viewing_return_method;
            $LOAN['loan_pay_term']        = $loanInfo->loan_pay_term;
            $LOAN['invest_rate']          = $loanInfo->invest_rate;
            $LOAN['branch_cd']            = $loanInfo->branch_cd;
            $LOAN['income_rate']          = $loanInfo->income_rate;
            $LOAN['local_rate']           = $loanInfo->local_rate;
            $LOAN['loan_memo']            = $loanInfo->loan_memo;

            $LOAN['contract_date']        = $LOAN['loan_date'] = $LOAN['take_date'] = $LOAN['app_date'] = Func::delChar($LOAN['contract_date'], '-');
            $LOAN['contract_end_date']    = Func::delChar($LOAN['contract_end_date'], '-');
            $LOAN['invest_rate']          = $LOAN['loan_rate'] = $LOAN['loan_delay_rate'] = sprintf('%0.2f', $LOAN['invest_rate']);
            $LOAN['balance']              = $LOAN['platform_fee_rate'] = 0;
            $LOAN['legal_rate']           = Vars::$curMaxRate;
            $LOAN['loan_money']           = $LOAN['app_money'] = $LOAN['total_loan_money'] = $LOAN['first_loan_money'] = Func::delChar($LOAN['loan_money'], ',');
            $LOAN['monthly_return_money'] = 0;
            $LOAN['loan_type']            = '01';
            $LOAN['pay_term']             = $LOAN['loan_pay_term'];

            $date1 = Carbon::parse($LOAN['contract_date']);
            $date2 = Carbon::parse($LOAN['contract_end_date']);
            $LOAN['loan_term'] = $date1->diffInMonths($date2);

            $loanInfoNo = Loan::insertLoanInfo($LOAN);

            // 오류 업데이트 후 쪽지 발송
            if(!is_numeric($loanInfoNo))
            {
                DB::rollBack();

                Log::debug($loanInfoNo);
                return '계약등록시 에러가 발생했습니다.('.$loanInfoNo.')';
            }
            
            Log::info('계약등록 > 차입자 번호 : '.$LOAN['cust_info_no'].', 투자자 번호 : '.$LOAN['loan_usr_info_no'].', 계약번호 : '.$loanInfoNo);

            $newLoanInfo = DB::table("loan_info")->where("no", $loanInfoNo)->where("save_status", 'Y')->first();
            $newLoanInfo = Func::chungDec(["loan_info"], $newLoanInfo);	// CHUNG DATABASE DECRYPT

            $_END = Array();
            $_END['handle_code']          = $loanInfo->handle_code;
            $_END['loan_usr_info_no']     = $LOAN['loan_usr_info_no'];
            $_END['loan_info_no']         = $loanInfoNo;
            $_END['inv_seq']              = $newLoanInfo->inv_seq;
            $_END['old_loan_usr_info_no'] = $loanInfo->loan_usr_info_no;
            $_END['old_loan_info_no']     = $loanInfo->no;
            $_END['old_inv_seq']          = $loanInfo->inv_seq;
            $_END['type']                 = '1';
            $_END['save_status']          = 'Y';
            $_END['save_id']              = $save_id;
            $_END['save_time']            = $save_time;            

            $rslt = DB::dataProcess("INS", "loan_info_end_log", $_END);
            // 오류 업데이트 후 쪽지 발송
            if( $rslt!="Y" )
            {
                DB::rollBack();

                Log::debug($loanInfoNo);
                return '구계약 로그 등록을 실패했습니다.('.$loanInfoNo.')';
            }
        }

        DB::commit();

        $array_result['rs_code']    = "Y";
        $array_result['result_msg'] = "정상처리 되었습니다.";
        return $array_result;
    }

    /**
     * 만기일자조정 - 입력창
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function dividePlusForm(Request $request)
    {
        $status_color = "#6c757d";
        
        $v = DB::table("loan_info")->join("loan_usr_info", "loan_usr_info.no", "=", "loan_info.loan_usr_info_no")
                                    ->select("loan_info.*, loan_usr_info.name")
                                    ->where("loan_info.no",$request->loan_info_no)
                                    ->where("loan_usr_info.save_status","Y")
                                    ->first();

        $v = Func::chungDec(["loan_info","loan_usr_info"], $v);	// CHUNG DATABASE DECRYPT

        return view('account.divideplusform')->with("v", $v)->with("status_color", $status_color);
    }

    /**
     * 만기일자조정 - 입력창 액션
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function dividePlusFormAction(Request $request)
    {
        $param     = $request->all();
        
        $save_id   = Auth::id();
        $save_time = date("YmdHis");

        $_DIVIDE = [];
        $_DIVIDE['loan_info_no'] = $param['loan_info_no'];
        $_DIVIDE['trade_date']   = preg_replace('/[^0-9]/', '', $param['trade_date']);
        $_DIVIDE['term']         = $param['term'] * 1;

        $loanInfo = DB::table("loan_info")->where("no", $param['loan_info_no'])->where("save_status", 'Y')->first();
        $loanInfo = Func::chungDec(["loan_info"], $loanInfo);	// CHUNG DATABASE DECRYPT

        // 조정 후 남는 잔액이 0원 미만일 경우 break; 누르는 1초 순간에 발생할 수도 있잖아요
        if(empty($loanInfo->balance))
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "해당 계약의 잔액은 0원입니다.";
            return $array_result;
        }
        if( $loanInfo->balance < 0 )
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "해당 계약의 잔액이 0보다 작을 수 없습니다.";
            return $array_result;
        }
        if( $_DIVIDE['term'] <= 0 )
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "해당 조정기간이 0보다 작을 수 없습니다.";
            return $array_result;
        }
        
        if( $_DIVIDE['trade_date'] != date('Ymd', strtotime(date('Ymd', strtotime($loanInfo->contract_end_date.' +'.$_DIVIDE['term'].' years')))) )
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "연장만기일자와 연장기간이 다릅니다.";
            return $array_result;
        }

        // 스케줄 이자지급 확인
        $loanPlanCnt = DB::table("loan_info_return_plan")->select("count(1) as cnt")
                                                        ->where("loan_info_no", $param['loan_info_no'])
                                                        ->where("save_status", 'Y')
                                                        ->where("divide_flag", 'N')
                                                        ->first();
        
        if($loanPlanCnt->cnt > 0)                                          
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "스케줄에 따른 이자지급 미처리된건이 존재합니다. \n이자지급을 모두 완료해주세요.";
            return $array_result;
        }

        $planInfo = DB::table('account_transfer')->join("loan_info","loan_info.no","=","account_transfer.loan_info_no")
                                            ->join("cust_info","cust_info.no","=","loan_info.cust_info_no")
                                            ->join("loan_usr_info", "loan_info.loan_usr_info_no", "=", "loan_usr_info.no")
                                            ->where('account_transfer.loan_info_no', $param['loan_info_no'])
                                            ->whereIn('account_transfer.status', ['S', 'W', 'A'])
                                            ->where('loan_info.save_status', 'Y')
                                            ->where('cust_info.save_status', 'Y')
                                            ->where('loan_usr_info.save_status', 'Y')
                                            ->where('account_transfer.save_status', 'Y')
                                            ->orderby('account_transfer.no')
                                            ->first();

        $planInfo = Func::chungDec(["account_transfer"], $planInfo);	// CHUNG DATABASE DECRYPT

        // 송금대기상태에 있으면 안됩니다.
        if(!empty($planInfo->balance))
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "해당 계약에서 송금대기상태인 항목이 있습니다.";
            return $array_result;
        }

        DB::beginTransaction();

        $invest     = new Invest($loanInfo->no);
        $divideInterest = $invest->getDivideInterest(Array("sdate"=>$loanInfo->contract_date,"edate"=>$_DIVIDE['trade_date']));

        // 선계산 이자
        $interest = [];
        $interest['return_income_tax']    = floor( $divideInterest['interest'] * ($loanInfo->income_rate / 100) / 10 ) * 10;	    // 소득세 : TRUNC(세전이자금액*원천징수세율/100/10,0)*10
        $interest['return_local_tax']     = floor( $interest['return_income_tax'] * ($loanInfo->local_rate / 100) / 10 ) * 10;	// 지방소득세 : TRUNC( 소득세*0.01,0)*10  

        // 입금배열
        $array_insert = Array();
        $array_insert['action_mode']      = "INSERT";
        $array_insert['trade_type']       = "07";
        $array_insert['trade_path_cd']    = "1";
        $array_insert['cust_info_no']     = $loanInfo->cust_info_no;
        $array_insert['loan_usr_info_no'] = $loanInfo->loan_usr_info_no;
        $array_insert['loan_info_no']     = $loanInfo->no;
        $array_insert['trade_money']      = 0;
        $array_insert['trade_date']       = $_DIVIDE['trade_date'];
        // $array_insert['lose_origin']      = $loanInfo->balance;
        $array_insert['lose_money']       = $loanInfo->balance;
        $array_insert['return_origin']    = 0;        

        $array_insert['interest_sdate']   = $loanInfo->contract_date;
        $array_insert['interest_edate']   = $_DIVIDE['trade_date'];

        $date1 = Carbon::parse($loanInfo->contract_date);
        $date2 = Carbon::parse($_DIVIDE['trade_date']);
        $array_insert['return_intarval']  = $date1->diffInDays($date2);   
        
        // 이율
        $array_insert['invest_rate']      = $loanInfo->invest_rate;
        $array_insert['income_rate']      = $loanInfo->income_rate;
        $array_insert['local_rate']       = $loanInfo->local_rate;

        // 원천징수
        $array_insert['withholding_tax']  = $interest['return_income_tax'] + $interest['return_local_tax'];
        $array_insert['income_tax']       = $interest['return_income_tax'];
        $array_insert['local_tax']        = $interest['return_local_tax'];
        $array_insert['fullpay_cd']       = "2";
        $array_insert['memo']             = "만기연장으로 인한 완료처리";
        
        // 계좌
        $array_insert['loan_bank_cd']     = $loanInfo->loan_bank_cd;
        $array_insert['loan_bank_ssn']    = $loanInfo->loan_bank_ssn;
        $array_insert['loan_bank_name']   = $loanInfo->loan_bank_name;
        $array_insert['cust_bank_cd']     = $loanInfo->cust_bank_cd;
        $array_insert['cust_bank_ssn']    = $loanInfo->cust_bank_ssn;
        $array_insert['cust_bank_name']   = $loanInfo->cust_bank_name;

        $trade              = new Trade($array_insert['loan_info_no']);
        $loan_info_trade_no = $trade->tradeInInsert($array_insert);
        if( !is_numeric($loan_info_trade_no) )
        {
            DB::rollBack();
            $array_result['rs_code']    = "N";
            $array_result['result_msg'] = "거래원장 등록 Error";
            return $array_result;
        }

        // 계약등록
        $LOAN['cust_info_no']           = $loanInfo->cust_info_no;
        $LOAN['cust_bank_name']         = $loanInfo->cust_bank_name;
        $LOAN['cust_bank_cd']           = $loanInfo->cust_bank_cd;
        $LOAN['cust_bank_ssn']          = $loanInfo->cust_bank_ssn;
        $LOAN['loan_usr_info_no']       = $loanInfo->loan_usr_info_no;
        $LOAN['investor_no']            = $loanInfo->investor_no;
        $LOAN['investor_type']          = $loanInfo->investor_type;
        $LOAN['loan_bank_nick']         = $loanInfo->loan_bank_nick;
        $LOAN['loan_bank_name']         = $loanInfo->loan_bank_name;
        $LOAN['loan_bank_cd']           = $loanInfo->loan_bank_cd;
        $LOAN['loan_bank_ssn']          = $loanInfo->loan_bank_ssn;
        $LOAN['loan_bank_status']       = 'N';
        $LOAN['handle_code']            = $loanInfo->handle_code;
        $LOAN['contract_date']          = $loanInfo->contract_end_date;
        $LOAN['contract_end_date']      = $_DIVIDE['trade_date'];
        $LOAN['contract_day']           = $loanInfo->contract_day;
        $LOAN['loan_money']             = $loanInfo->balance;
        $LOAN['pro_cd']                 = $loanInfo->pro_cd;
        $LOAN['return_method_cd']       = $loanInfo->return_method_cd;
        $LOAN['viewing_return_method']  = $loanInfo->viewing_return_method;
        $LOAN['loan_pay_term']          = $LOAN['pay_term'] = $loanInfo->loan_pay_term;
        $LOAN['invest_rate']            = $loanInfo->invest_rate;
        $LOAN['branch_cd']              = $loanInfo->branch_cd;
        $LOAN['income_rate']            = $loanInfo->income_rate;
        $LOAN['local_rate']             = $loanInfo->local_rate;
        $LOAN['loan_memo']              = $loanInfo->loan_memo;

        $LOAN['contract_date']          = $LOAN['loan_date'] = $LOAN['take_date'] = $LOAN['app_date'] = Func::delChar($LOAN['contract_date'], '-');
        $LOAN['contract_end_date']      = Func::delChar($LOAN['contract_end_date'], '-');
        $LOAN['invest_rate']            = $LOAN['loan_rate'] = $LOAN['loan_delay_rate'] = sprintf('%0.2f', $LOAN['invest_rate']);
        $LOAN['balance']                = $LOAN['platform_fee_rate'] = 0;
        $LOAN['legal_rate']             = Vars::$curMaxRate;
        $LOAN['loan_money']             = $LOAN['app_money'] = $LOAN['total_loan_money'] = $LOAN['first_loan_money'] = Func::delChar($LOAN['loan_money'], ',');
        $LOAN['monthly_return_money']   = 0;
        $LOAN['loan_type']              = '01';

        $date1 = Carbon::parse($LOAN['contract_date']);
        $date2 = Carbon::parse($LOAN['contract_end_date']);
        $LOAN['loan_term'] = $date1->diffInMonths($date2);

        $loanInfoNo = Loan::insertLoanInfo($LOAN);

        // 오류 업데이트 후 쪽지 발송
        if(!is_numeric($loanInfoNo))
        {
            DB::rollBack();

            Log::debug($loanInfoNo);
            return '계약등록시 에러가 발생했습니다.('.$loanInfoNo.')';
        }
        
        Log::info('계약등록 > 차입자 번호 : '.$LOAN['cust_info_no'].', 투자자 번호 : '.$LOAN['loan_usr_info_no'].', 계약번호 : '.$loanInfoNo);

        $newLoanInfo = DB::table("loan_info")->where("no", $loanInfoNo)->where("save_status", 'Y')->first();
        $newLoanInfo = Func::chungDec(["loan_info"], $newLoanInfo);	// CHUNG DATABASE DECRYPT

        $_END = Array();
        $_END['handle_code']          = $loanInfo->handle_code;
        $_END['loan_usr_info_no']     = $LOAN['loan_usr_info_no'];
        $_END['loan_info_no']         = $loanInfoNo;
        $_END['inv_seq']              = $newLoanInfo->inv_seq;
        $_END['old_loan_usr_info_no'] = $loanInfo->loan_usr_info_no;
        $_END['old_loan_info_no']     = $loanInfo->no;
        $_END['old_inv_seq']          = $loanInfo->inv_seq;
        $_END['type']                 = '2';
        $_END['save_status']          = 'Y';
        $_END['save_id']              = $save_id;
        $_END['save_time']            = $save_time;            

        $rslt = DB::dataProcess("INS", "loan_info_end_log", $_END);
        // 오류 업데이트 후 쪽지 발송
        if( $rslt!="Y" )
        {
            DB::rollBack();

            Log::debug($loanInfoNo);
            return '구계약 로그 등록을 실패했습니다.('.$loanInfoNo.')';
        }

        DB::commit();

        $array_result['rs_code']    = "Y";
        $array_result['result_msg'] = "정상처리 되었습니다.";
        return $array_result;
    }
    
    /**
     * 투자원금조정 - 입력창 이자계산버튼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function divideOriginInterest(Request $request)
    {
        $loanInfo = DB::table("loan_info")->select('*')->where("no", $request->loan_info_no)->where("save_status", 'Y')->first();

        $invest = new Invest($request->loan_info_no);
        $val    = $invest->getDivideInterest(Array("sdate"=>$loanInfo->contract_date,"edate"=>$request->trade_date));

        $interest = [];
        $interest['return_intarval_interest'] = $val['interest'];

        $interest['loan_return_interest']   = $loanInfo->return_interest_sum ?? 0;                                          // 기지급 이자
        $interest['loan_return_income_tax'] = $loanInfo->return_income_tax_sum ?? 0;                                        // 기지급 소득세
        $interest['loan_return_local_tax']  = $loanInfo->return_local_tax_sum ?? 0;                                         // 기지급 지방소득세

        $date1 = Carbon::parse($request->contract_date);
        $date2 = Carbon::parse($request->trade_date);
        $interest['return_intarval']        = $date1->diffInDays($date2);                                                   // 거치기간

        $interest['return_origin']          = $request->trade_money ?? 0;                                                   // 원금
        $interest['return_interest']        = $interest['return_intarval_interest'] - $interest['loan_return_interest'];    // 이자
        
        $interest['return_income_tax']      = ceil( $interest['return_interest'] * ($loanInfo->income_rate / 100) / 10 ) * 10;	    // 소득세 : TRUNC(세전이자금액*원천징수세율/100/10,0)*10
        $interest['return_local_tax']       = floor( $interest['return_income_tax'] * ($loanInfo->local_rate / 100) / 10 ) * 10;	// 지방소득세 : TRUNC( 소득세*0.01,0)*10

        $interest['return_intarval_interest'] = number_format($interest['return_intarval_interest']);               // 거치기간이자
        $interest['loan_return_interest']     = number_format($interest['loan_return_interest']);                   // 기지급이자
        $interest['loan_return_income_tax']   = number_format($interest['loan_return_income_tax']);                 // 기지급소득세
        $interest['loan_return_local_tax']    = number_format($interest['loan_return_local_tax']);                  // 기지급지방소득세

        $interest['return_interest_real']     = $interest['return_interest'] - $interest['return_income_tax'] - $interest['return_local_tax'];
        $interest['return_origin_real']       = number_format($interest['return_origin'] + $interest['return_interest'] - $interest['return_income_tax'] - $interest['return_local_tax']); // 실지급액
        
        $interest['return_interest']          = number_format($interest['return_interest']);                        // 이자
        $interest['return_income_tax']        = number_format($interest['return_income_tax']);                      // 소득세
        $interest['return_local_tax']         = number_format($interest['return_local_tax']);                       // 지방소득세
        
        $interest['return_origin']            = number_format($interest['return_origin']);                          // 원금

        return $interest;
    }
}