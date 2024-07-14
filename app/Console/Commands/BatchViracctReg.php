<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\Config\BatchController;
use DB;
use Func;
use Log;
use Vars;
use WooriBank;

class BatchViracctReg extends Command
{
    /**
     * The name and signature of the console command.
     *
     * no:직전매입처코드, limit:한번에 처리할 수량
     * @var string
     */
    protected $signature = 'Batch:viracctReg {no?} {limit?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '가상계좌 일괄 발급 등록';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        
        if(empty($this->argument('no')))
        {
            echo "직전매입처 코드를 입력해주세요.(예:솔림=31)\n";
            exit;
        }

        $no = $this->argument('no');

        echo $no."\n";

        // 고객 가져오기
        $cust = DB::table('loan_info')
                    ->join("cust_info", "loan_info.cust_info_no", "=", "cust_info.no")
                    ->select("cust_info.no", DB::raw("max(loan_info.no) as loan_info_no"), "cust_info.name")
                    ->where('cust_info.save_status', 'Y')
                    ->where('loan_info.save_status', 'Y')
                    ->whereIn('loan_info.status', ['A', 'B', 'C', 'D'])
                    ->whereRaw("(select no from vir_acct where save_status='Y' and CUST_INFO_NO=cust_info.no) is null")
                    ->where('loan_info.seller_no', $no)
                    ->groupBy('cust_info.no')
                    ->orderBy('cust_info.no');

        if(!empty($this->argument('limit')))
        {
            $cust->limit($this->argument('limit'));
        }
        $cust = $cust->get();

        $cnt = 0;
        foreach( $cust as $v )
        {
            DB::beginTransaction();
            $cnt ++;
            echo $cnt.'.'.$v->no." ".$v->loan_info_no." ".$v->name."\n";   
            
            $VIR_NO = DB::TABLE("VIR_ACCT")->WHERERAW("COALESCE(CUST_INFO_NO,0)=0 ")->WHERE("SAVE_STATUS","Y")->MIN("NO");
            
            if(empty($VIR_NO))
            {
                echo "현재 발급가능한 가상계좌가 없습니다.\n";
                exit;
            }
            else
            {
                $VIR = DB::TABLE('VIR_ACCT')->SELECT('MO_SSN, BANK_CD, VIR_ACCT_SSN')->WHERE('NO',$VIR_NO)->FIRST();
                $VIR = Func::chungDec(["VIR_ACCT"], $VIR);	// CHUNG DATABASE DECRYPT

                // 가상계좌 발급
                $stb = new WooriBank();
                $stb_rslt = $stb->setVirtualAccount($v->no, $v->loan_info_no, Func::chungDecOne($v->name));

                if($stb_rslt=='duple')
                {
                    echo "이미 동일한 회원번호로 발급된 가상계좌가 있습니다.\n";
                }
                else if($stb_rslt=='N')
                {
                    echo "가상계좌 발급중 오류가 발생했습니다.\n";
                }
                else 
                {
                    // 계약정보 업데이트
                    $rslt = DB::dataProcess("UPD", 'LOAN_INFO', ['VIR_ACCT_MO_BANK_CD'=>$VIR->bank_cd,'VIR_ACCT_MO_SSN'=>$VIR->mo_ssn, 'SAVE_ID'=>'SYSTEM', 'SAVE_TIME'=>date("YmdHis")], ["CUST_INFO_NO"=>$v->no]);
                    if($rslt != 'Y')
                    {
                        echo "계약정보 업데이트 실패.\n";
                    }
                    else 
                    {
                        //  원장변경내역 등록
                        $_wch = [
                            "cust_info_no"  =>  $v->no,
                            "loan_info_no"  =>  $v->loan_info_no,
                            "worker_id"     =>  'SYSTEM',
                            "work_time"     =>  date("Ymd"),
                            "worker_code"   =>  '',
                            "loan_status"   =>  "",
                            "manager_code"  =>  "",
                            "div_nm"        =>  "가상계좌번호변경(발급)",
                            "before_data"   =>  "",
                            "after_data"    =>  $VIR->bank_cd.",".$VIR->vir_acct_ssn,
                            "trade_type"    =>  "",
                            "sms_yn"        =>  "N",
                            "memo"          =>  "",
                        ];
                
                        $result_wch = Func::saveWonjangChgHist($_wch);
                        if( $result_wch != "Y" )
                        {
                            echo "가상계좌번호변경(발급) - 원장변경내역 저장 실패 계약번호 : ".$v->loan_info_no."\n";
                        }
                    }
                }
            }

            DB::commit();
        }
    }
}
