<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Illuminate\Support\Facades\Log;
use Func;
use App\Http\Controllers\Config\BatchController;
class SmsResultUpdate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SMS:ResultUpdate {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'SMS 전송 결과 업데이트';

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

        $fail_cnt = 0;
        $lms_cnt = 0;
        $sms_cnt = 0;

        $tablename = "MSG_LOG_".date("Ym");
        
		$npro_log = DB::connection("sms")->table($tablename)
										  ->select("MSG_SEQ","CUR_STATE","TRAN_ETC2", "RSLT_CODE", "RSLT_CODE2", "RSLT_CODE_PRE", "RSLT_CODE2_PRE", "RSLT_DATE", "SENT_DATE","REPORT_DATE","MSG_TYPE")
										  ->where("TRAN_ETC3", 'N')
										  ->orderBy("TRAN_ETC2","asc")
										  ->get();
		foreach($npro_log as $v)
		{
            print_r((array)$v);
            $DATA = Array();
            $DATA['SEND_STATUS'] = 'Y';
            $DATA['SEND_RESULT'] = $v->RSLT_CODE2;

            $rslt = DB::dataProcess('UPD', 'SUBMIT_SMS_LOG', $DATA, ['SEND_MSG_NO'=>$v->MSG_SEQ, 'SEND_STATUS'=>'S']);
            
            if($rslt=='Y')
            {
				$MSG_SEQ = DB::connection("sms")->table($tablename)
							->where('MSG_SEQ',$v->MSG_SEQ)
							->update(['TRAN_ETC3' => 'Y']);
                if($v->MSG_TYPE == '4')
                {
                    $sms_cnt++;
                }
                else if($v->MSG_TYPE == '6')
                {
                    $lms_cnt++;
                }
                
			}
            else 
            {
                $fail_cnt ++;
            }
		}


        // 배치 종료 기록
        if($batchLogNo>0)
        {
            $note = 'LMS:'.$lms_cnt.'건, SMS:'.$sms_cnt.'건 업데이트. ';
            if($fail_cnt>0)
            {
                $note.= '실패:'.$fail_cnt.'건';
            }
            echo $note."\n";
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
