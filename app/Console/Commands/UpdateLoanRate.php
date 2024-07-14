<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Func;
use Loan;
use App\Http\Controllers\Config\BatchController;

class UpdateLoanRate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateLoan:Rate {dt? : 기준일} {no? : 계약번호} {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '여신계약원장 금리 업데이트 / 당일자기본값';

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
        // 배치 기록
        $stime = time();
        $batchLogNo = $this->startBatchLog($stime);

        $dt = $this->argument('dt');
        $no = $this->argument('no');

        if( !isset($dt) || strlen($dt)!=8 )
        {
            $dt = date("Ymd");
        }
        if( !isset($no) || !is_numeric($no) )
        {
            $no = 0;
        }

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);

        echo "[START] LOAN_INFO_RATE\n";
        
        // 법착관리 자동조회 등록
        $LOAN = DB::TABLE("LOAN_INFO")->JOIN("LOAN_INFO_RATE", "LOAN_INFO.NO", "=", "LOAN_INFO_RATE.LOAN_INFO_NO");
        $LOAN->SELECT("LOAN_INFO_RATE.*");
        $LOAN->WHERE('LOAN_INFO.SAVE_STATUS',      'Y');
        $LOAN->WHERE('LOAN_INFO_RATE.SAVE_STATUS', 'Y');
        $LOAN->WHERE("LOAN_INFO_RATE.RATE_DATE",   $dt);
        if( $no>0 )
        {
            $LOAN->WHERE('LOAN_INFO.NO', $no);
        }
        $LOAN->ORDERBY("LOAN_INFO.NO");
        $LOAN->ORDERBY("LOAN_INFO_RATE.SAVE_TIME");

        // 비교할 필요없이 무조건 업데이트 한다. 로그 셋트가 하루에 2개 등록된 경우도 있을 수 있음.
        $cnt = 0;
        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["LOAN_INFO","LOAN_INFO_RATE"], $rslt);	// CHUNG DATABASE DECRYPT

        foreach( $rslt as $val )
        {
            $cnt++;
            $val->loan_rate       = (float) $val->loan_rate;
            $val->loan_delay_rate = (float) $val->loan_delay_rate;

            // 거래원장도 업데이트 해준다.
            $rslt = DB::dataProcess("UPD", "LOAN_INFO", ['loan_rate'=>$val->loan_rate, 'loan_delay_rate'=>$val->loan_delay_rate], ['NO'=>$val->loan_info_no]);
            echo $val->loan_info_no." ".$val->loan_rate." ".$val->loan_delay_rate."\n";
        }


        echo "[END] LOAN_INFO_RATE\n";
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
