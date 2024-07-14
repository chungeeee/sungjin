<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Func;
use Loan;
use App\Http\Controllers\Config\BatchController;

class UpdateLoanData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateLoan:Data {no? : 계약번호} {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '계약정보 업데이트';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    // 계약종료할때 거래원장 감면 금액도 업데이트 쳐야되나

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // 배치 기록
        $stime = time();
        $batchLogNo = $this->startBatchLog($stime);

        $no = $this->argument('no');

        if( !isset($no) || !is_numeric($no) )
        {
            $no = 0;
        }

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);

        echo "[START] LOAN_INFO\n";
        
        $LOAN = DB::table("loan_info")->select("*")->where('save_status','Y');
        if( $no>0 )
        {
            echo "[START] 2\n";
            $LOAN->where('no', $no);
        }

        $cnt = 0;
        $rslt = $LOAN->get();
        $rslt = Func::chungDec(["loan_info"], $rslt);	// CHUNG DATABASE DECRYPT

        foreach( $rslt as $v )
        {
            $rs = DB::table('loan_info_return_plan')->select(DB::RAW('coalesce(sum(plan_interest),0) as sum_interest, coalesce(sum(withholding_tax),0) as sum_withholding_tax, coalesce(sum(income_tax),0) as sum_income_tax, coalesce(sum(local_tax),0) as sum_local_tax'))->where('loan_info_no',$v->no)->where('save_status','Y')->first();

            $arrInfoData = array();
            $arrInfoData['sum_interest']        = $rs->sum_interest;
            $arrInfoData['sum_withholding_tax'] = $rs->sum_withholding_tax;
            $arrInfoData['sum_income_tax']      = $rs->sum_income_tax;
            $arrInfoData['sum_local_tax']       = $rs->sum_local_tax;

            $rslt = DB::dataProcess("UPD", "loan_info", $arrInfoData, ['no'=>$v->no]);

            $cnt++;
            if($cnt%100==0) echo $cnt."\n";
            echo ".";
        }


        echo "[END] LOAN_INFO\n";
        echo "[CNT] ".$cnt."\n";

        // 배치 종료 기록
        if($batchLogNo>0)
        {
            $note = '';
            BatchController::setBatchLog($this->argument('batchNo'), $batchLogNo, $note, $stime);
        }
    }

    // 배치로그 시작
    public function startBatchLog($stime)
    {
        $batchNo = $this->argument('batchNo');
        $batchLogNo =  0;
        if(!empty($batchNo))
        {
            $batchLogNo = BatchController::setBatchLog($batchNo, 0, '', $stime);
        }

        return $batchLogNo;
    }
}
