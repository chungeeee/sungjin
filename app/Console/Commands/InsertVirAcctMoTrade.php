<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Config\BatchController;
use DB;
use Func;
use Log;

class InsertVirAcctMoTrade extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Insert:VirAcctMoTrade {batchNo?} {info_date?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '법인통장 거래내역 데이터 생성';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // 배치시작기록
        $start      = time();
        $batchLogNo = $this->startBatchLog($start);

        $table = 'TRADE_DATA_TBL';

        // 안가져온것 같져오기
        $wcms = DB::connection('band')->table($table)->select("*")
                                                        ->whereRaw(" COALESCE(TRADE_LIST_FLAG, '')!='Y' ")
                                                        ->whereNotIn('TRNX_TYPE', ['23', '28', '29'])
                                                        ->orderBy('TRCOM_SEND_DATE')
                                                        ->orderBy('TRCOM_SEND_TIME')
                                                        ->get();
        
        $xcnt = $cnt = 0;
        foreach( $wcms as $v )
        {
            echo $v->REQUESTER_NAME."\n";

            // 가상계좌번호 암호화
            $enc_cms_no = Func::encrypt($v->CMS_NO, 'ENC_KEY_SOL');
            
            // 회원번호, 계약번호 가져오기. 다계좌 처리를 안하므로 미리 넣고 바로 입금처리 예정
            $vir = DB::table('vir_acct')
                    ->select('cust_info_no', 'loan_info_no')
                    ->where('vir_acct_ssn', $enc_cms_no)
                    ->where('save_status', 'Y')
                    ->first();

            unset($IN);
            $IN['trade_code']         = $v->FB_TR_CODE;			    // 펌뱅킹 거래코드
            $IN['trade_date']         = $v->TRCOM_SEND_DATE;		// 처리일자 - 전송일자
            $IN['trade_time']         = $v->TRCOM_SEND_TIME;		// 처리시간 - 전송시간
            $IN['in_date']            = $v->TRNX_DATE;				// 입금일자 - 거래일자
            $IN['in_time']            = $v->TRNX_TIME;				// 입금시간 - 거래시간
            $IN['name']               = trim($v->REQUESTER_NAME);   // 입금인성명
            $IN['c_date']             = $v->CANCEL_ORG_DATE;		// 취소원거래일자
            $IN['c_time']             = $v->CANCEL_ORG_TR_NO;		// 취소원거래전문번호
            $IN['str_no']             = $v->TR_NO;					// 전문번호
            $IN['bank_code']          = trim($v->BANK_CODE);		// 은행코드
            $IN['trade_money']        = intval($v->AMOUNT);		    // 거래금액
            $IN['trade_branch']       = $v->TRNX_BANK_BRANCH;	    // 거래지점코드
            $IN['tail_money']         = intval($v->BALANCE);		// 거래후잔액
            $IN['trade_type_code']    = $v->TRNX_TYPE;		        // 거래구분    - 입/출금 여부등
            $IN['vir_acct_ssn']       = $v->CMS_NO;					// 가상계좌번호
            $IN['mo_ssn']             = $v->ACCOUNT_NO;				// 모계좌번호
            $IN['cust_info_no']       = (isset($vir->cust_info_no)) ? $vir->cust_info_no:null;	// 회원번호
            $IN['loan_info_no']       = (isset($vir->loan_info_no)) ? $vir->loan_info_no:null;	// 계약번호
            $IN['manager_code']       = 'K';				        // 관리지점           

            DB::beginTransaction();
            
            $result = DB::dataProcess('INS', 'vir_acct_mo_trade', $IN);
                        
            // 성공
            if($result=='Y')
            {
                $cnt ++;
                DB::commit();
            }
            else
            {
                $xcnt ++;

                // 실패로그 남긴다.
                echo $IN['in_date']." ".$IN['str_no']."\n";
                Log::debug('vir_acct_mo_trade 입력 에러');
                Log::debug($IN);
            }
        }

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