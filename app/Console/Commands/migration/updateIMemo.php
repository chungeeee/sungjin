<?php

namespace App\Console\Commands\migration;

use Illuminate\Console\Command;
use DB;
use Func;
use Log;
use Arr;

class updateIMemo extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work:updateImportantMemo';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '중요메모 업데이트';

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

        $record = 0;
        $rs = DB::connection('mig_erp')->table('member_list')->select('no, important_memo, imemo_worker_id, imemo_save_time')->where('save_status','Y')->orderby('no')->get();
        foreach($rs as $v)
        {
            unset($arrData);
            $arrData['important_memo'] = $v->important_memo;
            $arrData['imemo_save_id'] = $v->imemo_worker_id;
            $arrData['imemo_save_time'] = date('YmdHis',$v->imemo_save_time);
            $rslt = DB::TABLE('cust_info')->where(['no'=>$v->no])->update($arrData);

            echo ".";
            $record++;
            if($record%100==0) echo $record."\n";
        }
    }
}