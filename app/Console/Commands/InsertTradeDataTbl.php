<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Config\BatchController;
use DB;
use Func;
use Log;
use Loan;
use Trade;
use Vars;
use App\Chung\Sms;

class InsertTradeDataTbl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Insert:tradeDataTbl {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'KSNET 신한은행 입금거래내역 동기화(TECH555)';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        # 배치시작기록
        $start      = time();
        $batchLogNo = $this->startBatchLog($start);

        $xcnt = $cnt = 0;

        # 메인쿼리
        # 최초이관날짜 : 2024.05.27 15:17:35 이후 데이터 동기화 시작
        # wincos 아바타 서버 ksnet DB
        $trade = DB::connection("ksnet")->table("TRADE_DATA_TBL")->select("*")
                                                                ->whereRaw(" COALESCE(BAND_FLAG, '') != 'Y' ")
                                                                ->where('DEAL_DATE', '>=', '20240527')
                                                                ->orderby('BAND_FLAG')
                                                                ->orderby('TRAN_TIME')
                                                                ->get();

        foreach( $trade as $key)
        {
            unset($_INS);

            foreach($key as $k => $v)
            {
                $_INS[$k] = $v;
            }

            # BAND 운영 서버 band DB
            $result = DB::connection("band")->table("TRADE_DATA_TBL")->insert($_INS);

            # 성공
            if($result)
            {
                # 업데이트
                # 이관 완료후 ksnet DB BAND_FLAG = 'Y' update
                $upResult = DB::connection('ksnet')->table("TRADE_DATA_TBL")
                                        ->whereRaw(" COALESCE(BAND_FLAG, '') != 'Y' ")
                                        ->where('TRAN_DATE', $_INS['TRAN_DATE'])
                                        ->where('TRAN_TIME', $_INS['TRAN_TIME'])
                                        ->where('SEQ_NO', $_INS['SEQ_NO'])
                                        ->where('CORP_ACC_NO', $_INS['CORP_ACC_NO'])
                                        ->update(['BAND_FLAG' => "Y"]);

                if($upResult!=0)
                {
                    $cnt ++;
                }
                else 
                {
                    $xcnt ++;

                    # 실패로그
                    $note = $_INS['TRNX_DATE']." ".$_INS['SEQ_NO']." ".$_INS['CORP_ACC_NO'];
                    echo $note."\n";
                    Log::error('TRADE_DATA_TBL 입력 에러');
                    Log::error($note);
                }
            }
            else
            {
                $xcnt ++;

                # 실패로그
                echo $_INS['TRNX_DATE']." ".$_INS['SEQ_NO'].$_INS['CORP_ACC_NO']."\n";
                Log::error('trade_list 입력 에러');
                Log::error($_INS);
            }
        }

        $note = '성공:'.$cnt.'건, 실패:'.$xcnt.'건';
        echo $note."\n";

        # 배치종료기록
        if($batchLogNo > 0)
        {
            BatchController::setBatchLog($this->argument('batchNo'), $batchLogNo, $note, $start);
        }
    }


    # 배치로그 시작
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
