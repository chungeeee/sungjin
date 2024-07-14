<?php

namespace App\Console\Commands;

use App\Models\ExcelImport;
use Illuminate\Console\Command;
use DB;
use Func;
use Loan;
use Log;
use ExcelFunc;
use FastExcel;
use Excel;
use Vars;
use Carbon;
use Invest;
use App\Chung\ExcelCustomExport;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Config\BatchController;

class PlWorkAction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'PlWork:action';
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '1회성 처리';

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
        //$loanInfoNo = 355;
        // $loan = new Loan($loanInfoNo, true);
        // $array_plan = $loan->buildPlanData();
        // $loan->savePlan($array_plan);
        $loan_info_no = Array('438');

        $LOAN = DB::TABLE("loan_info")->SELECT("loan_info_no", "no")
                                    ->WHERE('SAVE_STATUS','Y')
                                    ->GET();

        foreach($LOAN as $val)
        {
            //echo print_r($val, true);
            // 기본쿼리
            $LOAN_LIST = DB::TABLE("LOAN_INFO AS L");
            $LOAN_LIST->JOIN(DB::RAW("(select 
                                    loan_info_no, COALESCE(sum(case when divide_flag is null or divide_flag = 'N' then 1 else 0 end),0) as n_cnt
    
                                from 
                                (
                                    select
                                        loan_info.no, loan_info_return_plan.divide_flag, loan_info_return_plan.plan_interest, loan_info_return_plan.seq
                                    from 
                                        loan_info,loan_info_return_plan
                                    where
                                        loan_info.save_status = 'Y' and loan_info.no=loan_info_return_plan.loan_info_no
                                        and loan_info_return_plan.loan_info_no = ".$val->no." 
                                ) as foo
                                group by loan_info_no ) as i"), "l.no", "=", "i.loan_info_no");
            $LOAN_LIST->SELECT("i.*");
            $LOAN_LIST->WHERE('L.SAVE_STATUS','Y');
            $LOAN_LIST->WHERE('L.NO',$val->no);
            $LOAN_LIST->WHERE('L.STATUS','!=', 'N');
            $LOAN_LIST = $LOAN_LIST->FIRST();
            
            unset($_LOAN);
    
            if(!empty($LOAN_LIST))
            {
                // 상환완료
                if($LOAN_LIST->n_cnt <= 0)
                {
                    $DIVIDE = DB::TABLE("loan_info")->JOIN("loan_info_return_plan", "loan_info.NO", "=", "loan_info_return_plan.loan_info_no")
                                                ->SELECT("loan_info_return_plan.*", "loan_info.balance")
                                                ->WHERE('loan_info.SAVE_STATUS','Y')
                                                ->WHERE('loan_info.NO', $val->no)
                                                ->ORDERBY('loan_info_return_plan.PLAN_DATE', 'DESC')
                                                ->ORDERBY('loan_info.NO', 'DESC')
                                                ->FIRST();
    
                    // 상환완료인 애들중에 완제일자 구하기
                    // 수익분배 완료시 마지막 지급일자
                    if($DIVIDE->divide_flag == 'Y')
                    {
                        $_LOAN['status']       = 'A';
                        $_LOAN['return_date']  = null;
                        $_LOAN['fullpay_date'] = $DIVIDE->divide_date;
                        if($DIVIDE->balance > 0)
                        {
                            $_LOAN['fullpay_money']  = $DIVIDE->balance;
                        }
                        $_LOAN['balance']     = 0;
                    }
                    else
                    {
                        // 현재 상환완료건 중에서 앞으로 남은 지급일자 구하기(차기상환일)
                        $DIVIDE = DB::TABLE("loan_info")->JOIN("loan_info_return_plan", "loan_info.NO", "=", "loan_info_return_plan.loan_info_no")
                                                        ->SELECT("loan_info_return_plan.DIVIDE_FLAG", "loan_info_return_plan.DIVIDE_DATE", "loan_info_return_plan.PLAN_DATE", "loan_info.FULLPAY_MONEY", "loan_info.CONTRACT_END_DATE")
                                                        ->WHERE('loan_info.SAVE_STATUS','Y')
                                                        ->WHERE('loan_info.NO', $val->no)
                                                        ->WHERENULL('loan_info_return_plan.DIVIDE_FLAG')
                                                        ->ORDERBY('loan_info_return_plan.PLAN_DATE', 'ASC')
                                                        ->ORDERBY('loan_info.NO', 'ASC')
                                                        ->FIRST();
    
                        if(!empty($DIVIDE))
                        {
                            $_LOAN['status'] = 'E';
                            $_LOAN['return_date']  = $DIVIDE->plan_date;
                            $_LOAN['closing_date'] = $DIVIDE->contract_end_date;
                            $_LOAN['fullpay_money']  = 0;
                            $_LOAN['fullpay_date'] = null;
                        }
                    }
                }
                // 상환중
                else
                {
                    // 상환중인 애들중에 차기상환일자 구하기
                    // 수익분배 미지급건중 최초일자
                    $DIVIDE = DB::TABLE("loan_info")->JOIN("loan_info_return_plan", "loan_info.NO", "=", "loan_info_return_plan.loan_info_no")
                                                    ->SELECT("loan_info_return_plan.DIVIDE_FLAG", "loan_info_return_plan.DIVIDE_DATE", "loan_info_return_plan.PLAN_DATE", "loan_info.FULLPAY_MONEY", "loan_info.CONTRACT_END_DATE")
                                                    ->WHERE('loan_info.SAVE_STATUS','Y')
                                                    ->WHERE('loan_info.NO', $val->no)
                                                    ->WHERERAW("( loan_info_return_plan.DIVIDE_FLAG IS NULL OR loan_info_return_plan.DIVIDE_FLAG = 'N' )")
                                                    ->ORDERBY('loan_info_return_plan.PLAN_DATE', 'ASC')
                                                    ->ORDERBY('loan_info.NO', 'ASC')
                                                    ->FIRST();
    
                    if(!empty($DIVIDE))
                    {
                        $_LOAN['status'] = 'E';
                        $_LOAN['return_date'] = $DIVIDE->plan_date;
                        $_LOAN['closing_date'] = $DIVIDE->contract_end_date;
                        $_LOAN['fullpay_money']  = 0;
                        $_LOAN['fullpay_date'] = null;
                        if($DIVIDE->fullpay_money > 0)
                        {
                            $_LOAN['balance']  = $DIVIDE->fullpay_money;
                        }
                    }
                }
                
                echo print_r($val->no, true)."\n";
                $rslt = DB::dataProcess("UPD", "loan_info", $_LOAN, ['no'=>$val->no]);
            }
            else
            {
                $_LOAN['return_date'] = null;
                echo "미존재 : ".print_r($val->no, true)."\n";
                $rslt = DB::dataProcess("UPD", "loan_info", $_LOAN, ['no'=>$val->no]);
            }
        }
	}
}
