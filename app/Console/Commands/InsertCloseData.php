<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Config\BatchController;
use Illuminate\Database\QueryException;
use Carbon\Carbon;
use DB;
use Func;

class InsertCloseData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Insert:CloseData {batchNo?} {targetDt?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '일마감 데이터 생성';

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

        // 배치시작기록
        $start      = time();
        $batchLogNo = $this->startBatchLog($start);

        // 기준일
        $info_date = date("Ymd", time()-86400);
        if( is_numeric($this->argument('targetDt')) && strlen($this->argument('targetDt'))==8 )
        {
            $info_date = $this->argument('targetDt');
        }

        // 기준월
        $info_month = substr($info_date,0,6);

        // 마감데이터
        // -------------------------------------------------------------------------------------
        try
        {
            $count = DB::TABLE('pg_tables')->WHERE('tablename', 'close_data_'.$info_month)->count();
            echo $count."\n";
            if( $count==0 )
            {
                echo "CREATE TABLE\n";
                DB::STATEMENT("CREATE TABLE close_data_".$info_month." PARTITION OF close_data FOR VALUES FROM ('".$info_month."01') TO ('".date('Ymd',strtotime("+1 month", strtotime($info_month."01")))."')");
            }  

        }
        catch(QueryException $e)
        {

        }
        // 기준일자 데이터 존재할경우 백업처리
        $old_master = DB::TABLE("close_data")->SELECT("loan_info_no")->WHERE("info_date", $info_date)->COUNT();
        if($old_master > 0)
        {
            echo "DELETE\n";
            DB::TABLE('close_data')->WHERE("info_date", $info_date)->DELETE();
        }
        
        echo "INSERT\n";
        $SQL = "
        INSERT INTO close_data ( 
            cust_info_no,loan_usr_info_no,loan_info_no,info_date,cust_name,cust_ssn,name,ssn,usr_save_time,save_time,
            com_ssn,com_name,job_cd,email,app_date,app_money,loan_date,loan_money,loan_term,loan_rate,loan_delay_rate,
            contract_date,contract_end_date,contract_day,return_method_cd,cost_money,misu_money,lack_interest,interest,
            interest_sum,balance,over_money,take_date,return_date,return_date_biz,kihan_date,kihan_date_biz,loan_info_trade_no,status,
            interest_term,interest_sdate,interest_edate,charge_money,return_date_interest,fullpay_money,
            calc_date,fullpay_date,fullpay_cd,
            return_fee_rate,return_fee_cd,legal_rate,reg_time,cust_type,com_div,pro_cd,last_trade_date,
            cust_bank_cd,cust_bank_ssn,cust_bank_name,loan_bank_cd,loan_bank_ssn,loan_bank_name,misu_rev_money,
            loan_type,branch_cd,last_loan_date,last_in_date,
            
            loan_seq, inv_seq, lack_basis_money, fullpay_origin, first_loan_money,total_loan_money,
            first_loan_date, last_in_money,cust_save_time,cust_extra_save_time,handle_code,

            return_origin, return_local_tax_sum, return_income_tax_sum, return_withholding_tax_sum, return_money_sum,
            return_last_interest, return_intarval, return_interest,loan_bank_check_no,loan_bank_nick,
            loan_bank_status, local_tax, income_tax, withholding_tax, sum_interest, sum_local_tax,
            sum_income_tax, sum_withholding_tax, tax_free, platform_fee_rate, local_rate, income_rate,
            return_cost_money, return_origin_sum, return_interest_sum, return_money, investor_no, investor_type
        ) 
        SELECT l.cust_info_no, l.loan_usr_info_no, l.no, '".$info_date."' AS info_date, c.name, c.ssn, u.name, u.ssn, u.save_time, l.save_time
             , ce.com_ssn, ce.com_name, ce.job_cd, ce.email, app_date, app_money, loan_date, loan_money, loan_term, loan_rate, loan_delay_rate
             , contract_date, contract_end_date, contract_day, return_method_cd, cost_money, misu_money, lack_interest, interest
             , interest_sum, balance, over_money, take_date, return_date, return_date_biz, kihan_date, kihan_date_biz, loan_info_trade_no, status
             , interest_term, interest_sdate, interest_edate, charge_money, return_date_interest, fullpay_money
             , calc_date, fullpay_date, fullpay_cd
             , return_fee_rate, return_fee_cd, legal_rate, l.reg_time, cust_type, com_div, l.pro_cd, last_trade_date
             , l.cust_bank_cd, l.cust_bank_ssn, l.cust_bank_name, l.loan_bank_cd, l.loan_bank_ssn, l.loan_bank_name, l.misu_rev_money
             , l.loan_type, l.branch_cd, l.last_loan_date, l.last_in_date
             
             , l.loan_seq, l.inv_seq, l.lack_basis_money, l.fullpay_origin, l.first_loan_money, l.total_loan_money
             , l.first_loan_date, l.last_in_money, c.save_time, ce.save_time, l.handle_code

             , l.return_origin, l.return_local_tax_sum, l.return_income_tax_sum, l.return_withholding_tax_sum, l.return_money_sum
             , l.return_last_interest, l.return_intarval, l.return_interest, l.loan_bank_check_no, l.loan_bank_nick
             , l.loan_bank_status, l.local_tax, l.income_tax, l.withholding_tax, l.sum_interest, l.sum_local_tax
             , l.sum_income_tax, l.sum_withholding_tax, l.tax_free, l.platform_fee_rate, l.local_rate double, l.income_rate
             , l.return_cost_money, l.return_origin_sum, l.return_interest_sum, l.return_money, l.investor_no, l.investor_type

          FROM cust_info c, cust_info_extra ce, loan_info l, loan_usr_info u
         WHERE c.no=ce.cust_info_no 
           AND l.cust_info_no=c.no
           AND l.loan_usr_info_no=u.no
           AND c.save_status='Y'
           AND l.save_status='Y'
           AND u.save_status='Y'
        ";
        DB::STATEMENT($SQL);
        // -------------------------------------------------------------------------------------;;

        echo "END\n";

        // 배치종료기록
        if($batchLogNo > 0)
        {
            BatchController::setBatchLog($this->argument('batchNo'), $batchLogNo, '', $start);
        }
    }

    // 배치로그 시작
    public function startBatchLog($start)
    {
        $batchNo    = $this->argument('batchNo');
        $batchLogNo =  0;
        if(!empty($batchNo))
        {
            $batchLogNo = BatchController::setBatchLog($batchNo, 0, '', $start);
        }

        return $batchLogNo;
    }
}
