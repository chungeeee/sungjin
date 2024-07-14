<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Config\BatchController;
use DB;
use Func;
use Log;

class InsertVirAcctMoEnd extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Insert:VirAcctMoEnd {batchNo?} {targetDt?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '법인통장 일마감 데이터 생성';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        ini_set('memory_limit','-1');
        ini_set('max_execution_time', 0);

        // 배치시작기록
        $start      = time();
        $batchLogNo = $this->startBatchLog($start);

        // 기준일
        $info_date = date("Ymd", time()-86400);
        if( is_numeric($this->argument('targetDt')) && strlen($this->argument('targetDt'))==8 )
        {
            $info_date = $this->argument('targetDt');
        }

        // 기준일에서 전날
        $yesterday = date('Ymd', strtotime($info_date . '-1 Days'));

        // 기준월
        $info_month = substr($info_date,0,6);

        // 마감데이터
        // -------------------------------------------------------------------------------------
        try
        {
            $count = DB::TABLE('pg_tables')->WHERE('tablename', 'vir_acct_mo_end_'.$info_month)->count();
            echo $count."\n";
            if( $count==0 )
            {
                echo "CREATE TABLE\n";
                DB::STATEMENT("CREATE TABLE vir_acct_mo_end_".$info_month." PARTITION OF vir_acct_mo_end FOR VALUES FROM ('".$info_month."01') TO ('".date('Ymd',strtotime("+1 month", strtotime($info_month."01")))."')");
            }
        }
        catch(QueryException $e)
        {
        }

        // 기준일자 데이터 존재할경우
        $old_master = DB::TABLE("vir_acct_mo_end")->SELECT("vir_acct_mo_no")->WHERE("info_date", $info_date)->COUNT();
        if($old_master > 0)
        {
            echo "DELETE\n";
            DB::TABLE('vir_acct_mo_end')->WHERE("info_date", $info_date)->DELETE();
        }

        // 성공, 실패 건수 카운트
        $xcnt = $cnt = 0;

        $pgsqlTable = 'vir_acct_mo';
        $moacct = DB::table($pgsqlTable)->select("*")->where("save_status", 'Y')->get();
        $moacct = Func::chungDec([$pgsqlTable], $moacct);	// CHUNG DATABASE DECRYPT

        $mysqlTable = 'TRADE_DATA_TBL';
        foreach( $moacct as $v )
        {
            unset($IN, $oldTradeData, $tradeData);

            // 전일
            $oldTradeData = DB::connection('band')->table($mysqlTable)->select("BALANCE")
                                                                    ->where("DEAL_DATE", $yesterday)
                                                                    ->where("BANK_CODE_3", preg_replace('/[^0-9]/', '', $v->mo_bank_cd))
                                                                    ->where("CORP_ACC_NO", preg_replace('/[^0-9]/', '', $v->mo_bank_ssn))
                                                                    ->orderBy("DEAL_TIME", 'DESC')
                                                                    ->first();
            $old_money = ($oldTradeData->BALANCE ?? 0)*1;

            // 기준일
            $tradeData = DB::connection('band')->table($mysqlTable)->select("DEAL_DATE", "BALANCE", "DEAL_SELE", "TOTAL_AMT")
                                                                    ->where("DEAL_DATE", $info_date)
                                                                    ->where("BANK_CODE_3", $v->mo_bank_cd)
                                                                    ->where("CORP_ACC_NO", preg_replace('/[^0-9]/', '', $v->mo_bank_ssn))
                                                                    ->orderBy("DEAL_TIME")
                                                                    ->get();

            $last_trade_date = '';

            $now_money = $in_money = $out_money = 0;

            foreach( $tradeData as $val )
            {
                $last_trade_date = $val->DEAL_DATE;
                $now_money       = ($val->BALANCE ?? 0)*1;

                if($val->DEAL_SELE == '20')
                {
                    $in_money    += ($val->TOTAL_AMT ?? 0)*1;
                }
                else if($val->DEAL_SELE == '30')
                {
                    $out_money   += ($val->TOTAL_AMT ?? 0)*1;
                }
            }

            $IN['last_trade_date']   = $last_trade_date;        // 마지막 거래일

            $IN['old_money']         = $old_money;              // 전일 잔액
            $IN['now_money']         = $now_money;              // 기준일 잔액
            $IN['in_money']          = $in_money;               // 기준일 입금액
            $IN['out_money']         = $out_money;              // 기준일 출금액

            $IN['info_date']         = $info_date;			    // 기준일자
            $IN['vir_acct_mo_no']    = $v->no;		            // seq
            $IN['mo_bank_cd']        = $v->mo_bank_cd;		    // 계좌은행(코드관리)
            $IN['mo_bank_ssn']       = $v->mo_bank_ssn;			// 계좌번호
            $IN['mo_acct_div']       = $v->mo_acct_div;			// 법인구분
            $IN['status']            = $v->status;              // 사용여부(ON,OFF)
            $IN['save_time']         = $v->save_time;		    // 저장시간
            $IN['save_status']       = $v->save_status;		    // 저장상태
            $IN['save_id']           = $v->save_id;				// 저장자
            $IN['mo_bank_name']      = $v->mo_bank_name;		// 계좌명
            $IN['reg_time']          = $v->reg_time;		    // 저장시간
            $IN['mo_acct_cd']        = $v->mo_acct_cd;	        // 대분류
            $IN['mo_acct_sub_cd']    = $v->mo_acct_sub_cd;		// 중분류
            $IN['firm_banking']      = $v->firm_banking;		// 펌뱅킹 등록여부 Y/N
            $IN['cost_firm_banking'] = $v->cost_firm_banking;	// 비용 펌뱅킹 출금계좌 여부
            $IN['bank_firm_banking'] = $v->bank_firm_banking;	// 사채/우선주 펌뱅킹 출금계좌여부
            $IN['comp_code']         = $v->comp_code;			// KSNET 기관코드
            $IN['memo']              = $v->memo;		        // 메모
            
            $result = DB::dataProcess('INS', 'vir_acct_mo_end', $IN);
            
            // 성공
            if($result=='Y')
            {
                $cnt ++;
            }
            else
            {
                $xcnt ++;

                // 실패로그 남긴다.
                echo $info_date." ".$IN['vir_acct_mo_no']."\n";
                Log::debug('vir_acct_mo_end 입력 에러');
                Log::debug($IN);
            }
        }
        // -------------------------------------------------------------------------------------;;

        echo "END\n";

        $note = '성공:'.$cnt.'건, 실패:'.$xcnt.'건';
        echo $note."\n";

        // 배치종료기록
        if($batchLogNo > 0)
        {
            BatchController::setBatchLog($this->argument('batchNo'), $batchLogNo, $note, $start);
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