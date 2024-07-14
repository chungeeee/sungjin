<?php

namespace App\Console\Commands\migration;

use Illuminate\Console\Command;
use DB;
use Func;
use Log;
use Arr;

class updateContractClose extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work:updateContractClose';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '마감데이터 일괄업데이트';

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
        ini_set('memory_limit', '-1');

        $fp = fopen("/home/laravel/storage/logs/migration/updateContractClose.log", "w");
        
        // 1차로 2023년5월부터 현재까지만 우선 돌림.
        $sdate = "20230501";
        $edate = "20230601";
        for($date=$sdate; $date<=$edate; $date=date("Ymd", strtotime(substr($date,0,4)."-".substr($date,4,2)."-".substr($date,6)."+1 months")))
        {
            echo $date."기준 마감 업데이트 실행 시작\n";
            $dateVal = substr($date,0,6);

            $record = 0;
            $rs = DB::connection('mig_erp')->table('contract_info_close_'.$dateVal)->select('today, contract_info_no, status, rack_money')->where('today','<=','2023-06-08')->orderby('today')->orderby('contract_info_no')->get();
            foreach($rs as $v)
            {
                $v->today = str_replace("-","",$v->today);

                unset($_LOG);
                $_UP['settle_div_cd'] = "";
                if($v->status=="C" || $v->status=="D" || $v->status=="I" || $v->status=="J" || $v->status=="F" || $v->status=="G")
                {
                    if($v->status=="C" || $v->status=="D") $settle_div_cd = "1";                // 일반화해
                    else if($v->status=="I" || $v->status=="J") $settle_div_cd = "2";           // 개인회생
                    else if($v->status=="F" || $v->status=="G") $settle_div_cd = "3";           // 신용회복
                    else $settle_div_cd = "";
                    $_UP['settle_div_cd'] = $settle_div_cd;
                }

                $_UP['lack_interest'] = $v->rack_money;                                  // 부족금은 구분할 수 없어서 그냥 다 이자쪽으로 넣음

                $_LOG[] = $v->today;
                $_LOG[] = $v->contract_info_no;
                $_LOG[] = $v->status;
                $_LOG[] = $_UP['settle_div_cd'];
                $_LOG[] = $v->rack_money;
                $_LOG[] = "close_data_".substr($v->today,0,6);

                DB::table("close_data_".substr($v->today,0,6))->where(['info_date'=>$v->today, 'loan_info_no'=>$v->contract_info_no])->update($_UP);
                fwrite($fp, implode("\t", $_LOG)."\n");

                echo ".";
                $record++;
                if($record%100==0) echo $record."\n";
            }

            echo $date."기준 마감 업데이트 실행 완료\n\n";
        }
    }
}