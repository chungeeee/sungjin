<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Log;
use Func;
use Vars;
use App\Chung\Pds;
use Auth;
use Invest;

class RebuildSchedules extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work:rebuildSchedules {invNo? : 투자일련번호} {infoDate? : 기준일}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '투자 스케줄 갱신';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $rs = DB::TABLE('loan_info');
        $rs->SELECT('loan_info.no as loan_info_no, loan_info.loan_usr_info_no');
        $rs->WHERE('loan_info.save_status','Y');
        $rs->WHERE('loan_info.save_status','Y');
        $rs->WHEREIN('loan_info.status', ['A','B']);
        if($this->argument('invNo')) $rs->WHERE('loan_info.no', $this->argument('invNo'));
        $rs->ORDERBY('loan_info.no');
        $rs = $rs->GET();
        
        foreach($rs as $v)
        {
            $arrayReturn = [];
            $sch = DB::TABLE("loan_info")->LEFTJOIN(DB::RAW("(
                select s1.*, s2.remain_seq, s2.remain_plan_date, s2.remain_sdate 
                from 
                (select loan_info_no, seq as max_seq, plan_date as max_plan_date, plan_interest_sdate as max_sdate from loan_info_return_plan where (loan_info_no, seq) in (select loan_info_no, max(seq) from loan_info_return_plan where loan_info_no = ".$v->loan_info_no." group by loan_info_no)) s1
                left outer join 
                (select loan_info_no, seq as remain_seq, plan_date as remain_plan_date, plan_interest_sdate as remain_sdate from loan_info_return_plan where (loan_info_no, seq) in (select loan_info_no, min(seq) from loan_info_return_plan where loan_info_no = ".$v->loan_info_no." ".(($this->argument('infoDate')) ? "and plan_date >= '".$this->argument('infoDate')."'":"")." and (divide_flag is null or divide_flag = 'N')  group by loan_info_no)) s2
                on s1.loan_info_no=s2.loan_info_no
            ) loan_info_return_plan"),'loan_info.no','=','loan_info_return_plan.loan_info_no')->SELECT('loan_info.trade_date, loan_info_return_plan.*')->WHERE('loan_info.no',$v->loan_info_no)->FIRST();
            
            // 기준일 이후 잔여 스케줄이 있는 경우 - 잔여 스케줄 기점으로 갱신
            if(!empty($sch->remain_seq))
            {
                $checkDt = DB::TABLE('inv_tail_money')->WHERE('save_status','Y')->WHERE('loan_info_no',$v->loan_info_no)->WHERE('trade_date', '>=', $sch->remain_sdate)->min('trade_date');
                $arrayReturn['start_date'] = (!empty($checkDt)) ? min($sch->remain_plan_date, $checkDt) : $sch->remain_plan_date;
                $arrayReturn['start_seq'] = $sch->remain_seq;
            }
            // 기준일 이후 잔여 스케줄이 없는 경우 - 현재 종료 스케줄 이후로 갱신, 종결일이 바뀌거나 종결거래가 해지되지 않으면 변동사항 없을듯.
            else
            {
                if(empty($sch->max_plan_date))
                {
                    $arrayReturn['start_date'] = $sch->trade_date;
                    $arrayReturn['start_seq'] = 1;
                }
                else
                {
                    $arrayReturn['start_date'] = Func::addDay($sch->max_plan_date);
                    $arrayReturn['start_seq'] = $sch->max_seq+1;
                }
            }

            $inv = new Invest($v->loan_info_no); 
            echo "계약번호 : ".$v->loan_info_no.", 차입자번호 : ".$v->cust_info_no.", 투자자번호 : ".$v->loan_usr_info_no.", 갱신기준 = (".$arrayReturn['start_date']." , ".$arrayReturn['start_seq']."회차)\n";
            $array_plan = $inv->buildPlanData($arrayReturn['start_date'], $arrayReturn['start_seq']);
            $rslt = $inv->savePlan($array_plan, $arrayReturn['start_date']);
        }
    }
}
