<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Func;
use Loan;
use App\Http\Controllers\Config\BatchController;

class UpdateLoanInterest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateLoan:Interest {batchNo?} {no? : 계약번호} {dt? : 기준일} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '여신계약원장 이자계산 업데이트 / 당일자기본값';

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

        $no = $this->argument('no');
        $dt = $this->argument('dt');

        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);

        Loan::updateLoanInfoInterest($no, $dt);
        
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
