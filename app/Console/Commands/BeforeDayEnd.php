<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Func;
use Log;
use App\Http\Controllers\Config\BatchController;

class BeforeDayEnd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'before:dayend {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '당일 00시 이전 처리해야할 로직 모음';

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
        
        // echo Func::dateTerm('20211013', date("Ymd"));
        // exit;

        DB::enableQueryLog();

        $note = '';


        // 근저당권 회수대상 완납예정고객 해제. 계약일+29일 dambo_set_fee_target
        $targetDays = 29;
        $up = DB::table('loan_info')
                ->where('dambo_set_fee_target', 'Y')
                ->where('status', '!=', 'E')
                ->where(DB::raw("DAYS(CURRENT date)-DAYS(to_date(loan_date, 'YYYYMMDD'))"), '>=', $targetDays)
                ->update(['dambo_set_fee_target'=>'C']);
        $note.= '근저당:'.$up.'건';
        echo "근저당업데이트 : ".$up."\n";
        

        // 본사로 되어 있는 직원정보에 퇴사일자가 없는경우 퇴사일자 업데이트.
        $branchs = ['001']; // 본사
        $up = DB::table('users')
                ->whereIn('branch_code', $branchs)
                ->where('SAVE_STATUS', 'Y')
                ->where("coalesce(TOESA, '')", '')
                ->update(['TOESA'=>date("Ymd", time()+86400)]);
        $note.= ', 퇴사일:'.$up.'건';
        echo "퇴사일업데이트 : ".$up."\n";
        
        
        $query = DB::getQueryLog();
        print_R($query);
        Log::debug($query);
        Log::debug($note);
        
        // 배치 종료 기록
        if($batchLogNo>0)
        {
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
