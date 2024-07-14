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

class InsertTradeIn extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Insert:tradeIn {batchNo?} {no?} ';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '가상계좌 입금 처리';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // 배치시작기록
        $start      = time();
        $batchLogNo = $this->startBatchLog($start);


        ##############################################################
        # 입금처리

        
        // 매각대상
        $array_sell_target = [];
        $SQL = "SELECT loan_info_no from loan_sell where sell_no>'0' and save_status='Y' and sell_status='A' order by loan_info_no";
        $DAT = DB::select($SQL);
        foreach( $DAT as $val )
        {
            $array_sell_target[$val->loan_info_no] = $val->loan_info_no;
        }




        // 처리 안된 것 같져오기
        $trade = DB::table('trade_list')
                    ->select("*")
                    ->whereNotIn('trade_type_code', ['13', '23', '28', '29'])
                    ->where('in_date', '>=', '20100101')
                    ->whereRaw(" COALESCE(inserted, '')='' ");

        // 특정건만 돌릴때.
        if(!empty($this->argument('no')))
        {
            $trade->where('no', $this->argument('no'));
        }

        $in = $trade->orderBy('no')->get();
        $in = Func::chungDec(["trade_list"], $in);	// CHUNG DATABASE DECRYPT
        
        $xcnt = $ncnt = $cnt = 0;
        foreach( $in as $val )
        {
            DB::beginTransaction();

            // SELECT FOR UPDATE LOCKS
            // $SQL = "SELECT COALESCE(inserted, 'N') AS chk_flag FROM trade_list WHERE mo_ssn=:mo_ssn AND trade_date=:trade_date AND trade_time=:trade_time AND trade_type_code=:trade_type_code AND str_no=:str_no ";
            // $SQL.= " for update ";
            // $vchk = DB::select($SQL, Array(":mo_ssn"=>Func::chungEncOne($val->mo_ssn), ":trade_date"=>$val->trade_date, ":trade_time"=>$val->trade_time, ":trade_type_code"=>$val->trade_type_code, ":str_no"=>$val->str_no))[0];

            echo $val->no." ".$val->trade_date.$val->trade_time." ".$val->name." ".$val->trade_type_code." ".$val->str_no." ".$val->vir_acct_ssn." ".$val->trade_money."\n";

            // 중복처리 방지
            // if( $vchk->chk_flag!='N' && trim($vchk->chk_flag)!='' )
            // {
            //     DB::commit();
            //     echo "'".$vchk->chk_flag."'"." 처리중 에러";
            //     continue;
            // }

            $array_loans     = Array();
            $no              = $val->no;
            $save_time       = $val->trade_date.$val->trade_time;
            $cust_info_no    = $val->cust_info_no;
            $loan_info_no    = $val->loan_info_no;
            $trade_money     = $val->trade_money;
            $trade_date      = $val->trade_date;
            $vacs_vact_tr_cd = $val->no;

            $in_name      = trim($val->name);

            $vir_acct_ssn = trim($val->vir_acct_ssn);         // 가상계좌 - 예만 있는경우 많다.
            $bank_cd      = $val->bank_code;
            $bank_ssn     = trim($val->mo_ssn);               // 모계좌 계좌번호

            if( $bank_cd=="" || $bank_cd=="000" || $bank_ssn=="" )
            {
                $tmp = $this->getMoSsn($vir_acct_ssn);

                $val->bank_cd = trim($tmp['bank_cd']);
                $val->mo_ssn  = trim($tmp['mo_ssn']);
                $bank_cd      = $val->bank_cd;
                $bank_ssn     = $val->mo_ssn;
            }

            // 미처리입금 - 고객번호 없음 //////////////////////////////////////////////////////////////////////////////////////////
            if( !$val->cust_info_no )
            {
                $rslt = $this->insertUnknownTrade($val, "가상계좌입금 : 입금된 가상계좌번호로 부여된 고객을 찾을 수 없습니다.\n가상계좌번호 = ".$vir_acct_ssn);
                if( $rslt!="Y" )
                {
                    echo " >> CUST_INFO NOT FOUND => UNKNOWN_TRADE INSERT ERROR\n";
                    DB::rollback();
                }
                else
                {
                    echo " >> CUST_INFO NOT FOUND => UNKNOWN_TRADE INSERT SUCCESS\n";
                    DB::commit();
                }
                Func::pushSystemErrorMessage("가상계좌 입금 처리실패", "부여되지 않은 가상계좌로 입금<br>가상계좌 : ".$vir_acct_ssn."<br>입금금액 : ".number_format($trade_money));

                $ncnt ++;
                continue;
            }

            /*
            'A'=>'정상',
            'B'=>'연체',
            'C'=>'화해정상',
            'D'=>'화해연체',
            'S'=>'상각',

            'E'=>'완제',
            'M'=>'매각',
            'N'=>'미송금',
            'X'=>'철회',
            */          
            $last_loan_status = "";  
            $loan_sell_target = false;

            // 계약정보 
            $rslt = DB::table("loan_info")->select("*")->where('save_status','Y')->where('cust_info_no', $cust_info_no)
                    ->orderby("no", "asc")
                    ->orderby("no", "asc")
                    ->get();
            foreach( $rslt as $v )
            {
                // 입금처리는 유효건만
                if( $v->status=="A" )
                {
                    // 원리금 배분을 위해서 계산한번 하고 따로 돌린다.
                    $loan = new Loan($v->no);
                    if( $loan->no>0 )
                    {
                        $loan->getInterest($trade_date);
                        $array_loans[$v->no] = $loan;
                    }
                }
                $last_loan_status = $v->status;

                // 매각대상인지 확인 Flag
                if( in_array($v->no, $array_sell_target) )
                {
                    $loan_sell_target = true;
                }
            }


            // 미처리입금 - 유효계약 없음 //////////////////////////////////////////////////////////////////////////////////////////////////////////////
            if( sizeof($array_loans)==0 )
            {
                // $msg = "고객의 유효계약을 찾을 수 없습니다.\n최종계약상태";      // 다계좌 처리시 변경할 것.
                $msg = "입금계좌가 유효계약이 아닙니다.\n계약상태";
                $rslt = $this->insertUnknownTrade($val, "가상계좌입금 : ".$msg." : ".Func::nvl(Vars::$arrayContractSta[$last_loan_status],$last_loan_status)."\n가상계좌번호 = ".$vir_acct_ssn);
                if( $rslt!="Y" )
                {
                    echo " >> LOAN_INFO NOT FOUND => UNKNOWN_TRADE INSERT ERROR\n";
                    DB::rollback();
                }
                else
                {
                    echo " >> LOAN_INFO NOT FOUND => UNKNOWN_TRADE INSERT SUCCESS\n";
                    DB::commit();
                }
                $ncnt ++;
                continue;
            }


            // 매각 대상자 /////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            if( $loan_sell_target )
            {
                $rslt = $this->insertUnknownTrade($val, "가상계좌입금 : 매각결재에 매각예정계약으로 등록된 고객입니다.\n가상계좌번호 = ".$vir_acct_ssn);
                if( $rslt!="Y" )
                {
                    echo " >> SELL TARGET LOAN => UNKNOWN_TRADE INSERT ERROR\n";
                    DB::rollback();
                }
                else
                {
                    echo " >> SELL TARGET LOAN => UNKNOWN_TRADE INSERT SUCCESS\n";
                    DB::commit();
                }
                $ncnt ++;
                continue;
            }








            $loan_info_nos       = Array();
            $loan_info_trade_nos = Array();
            $loan_info_trade_no  = 0;

            // 입금액 분할
            $array_div_money = Trade::divTradeMoney($array_loans, $trade_money);
            if( sizeof($array_div_money)==0 )
            {
                $rslt = $this->insertUnknownTrade($val, "가상계좌입금 : 입금처리가 가능한 계약을 찾을 수 없습니다.\n가상계좌번호 = ".$vir_acct_ssn);
                if( $rslt!="Y" )
                {
                    echo " >> LOAN_INFO IMPOSSIBLE => UNKNOWN_TRADE INSERT ERROR\n";
                    DB::rollback();
                }
                else
                {
                    echo " >> LOAN_INFO IMPOSSIBLE => UNKNOWN_TRADE INSERT SUCCESS\n";
                    DB::commit();
                }
                $ncnt ++;
                continue;
            }


            // 분할 입금처리
            $rslt = "Y";
            foreach( $array_div_money as $loan_info_no => $sub_trade_money )
            {
                Log::debug("VIR_SSN=".$vir_acct_ssn." CUST_NO=".$cust_info_no." TRADE_MONEY=".$trade_money." / LOAN_INFO_NO=".$loan_info_no." SUB_TRADE_MONEY=".$sub_trade_money);
                if( $sub_trade_money==0 )
                {
                    continue;
                }

                $vin = Array();

                $vin['save_time']     = $save_time;
                $vin['save_id']       = "SYSTEM";
                $vin['trade_type']    = "01";
                $vin['cust_info_no']  = $cust_info_no;
                $vin['loan_info_no']  = $loan_info_no;
                $vin['trade_money']   = $sub_trade_money;
                $vin['lose_money']    = "0";
                $vin['trade_date']    = $trade_date;
                $vin['trade_path_cd'] = "1";               // 1 가상계좌
                $vin['vacs_vact_tr_cd'] = $vacs_vact_tr_cd;

                $vin['bank_cd']       = $bank_cd;
                $vin['bank_ssn']      = $bank_ssn;
                $vin['vir_acct_ssn']  = $vir_acct_ssn;
                $vin['in_name']       = $in_name;
                $vin['memo']          = "가상계좌 자동입금처리";
                $vin['sms_flag']      = "A";
                $vin['action_mode']   = "INSERT";

                $trade = new Trade($loan_info_no);
                $loan_info_trade_no = $trade->tradeInInsert($vin, "SYSTEM");

                // 입금처리 실패
                if( !is_numeric($loan_info_trade_no) )
                {
                    echo " >> TRADE INSERT ERROR\n";
                    $rslt = "N";
                    break;
                }
                else
                {
                    echo " >> TRADE INSERT SUCCESS ".$loan_info_no." ".$loan_info_trade_no."\n";
                }

                $loan_info_nos[]       = $loan_info_no;
                $loan_info_trade_nos[] = $loan_info_trade_no;

            }


            // 결과 업데이트
            if( $rslt=="Y" && sizeof($loan_info_trade_nos)>0 )
            {
                // 결과업데이트
                $rslt = DB::dataProcess("UPD", "trade_list", ['inserted'=>'I', 'insert_no'=>$loan_info_trade_no, 'convert_time'=>date("YmdHis")], ["no"=>$no]);

                // $vu = [];
                // $vu['convert_flag']  = "I";
                // $vu['cust_info_no']  = $cust_info_no;
                // $vu['loan_info_nos'] = implode(",",$loan_info_nos);
                // $vu['trade_nos']     = implode(",",$loan_info_trade_nos);
    
                // $rslt = DB::dataProcess("UPD", "VACS_AHST", $vu, ["ORG_CD"=>$val->org_cd, "TR_IL"=>$val->tr_il, "TR_SI"=>$val->tr_si, "TR_CD"=>$val->tr_cd, "TR_NO"=>$val->tr_no]);

                if( $rslt!="Y" )
                {
                    $xcnt ++;
                    echo " >> RESULT UPDATE ERROR\n";
                    DB::rollBack();
                }
                else
                {
                    $cnt ++;
                    DB::commit();
                }

            }
            // 입금실패
            else if( $rslt=="N" )
            {

                // 분할 입금 중 앞에서 성공한 건이 있을 수 있으니, 우선 롤백을 한다.
                echo " >> TRADE INSERT ERROR - ROLLBACK\n";
                DB::rollBack();

                // 트랜젝션 재시작
                DB::beginTransaction();
                $rslt = $this->insertUnknownTrade($val, "가상계좌입금 : 입금처리가 불가능합니다.\n가상계좌번호 = ".$vir_acct_ssn);
                if( $rslt!="Y" )
                {
                    echo " >> LOAN_INFO IMPOSSIBLE => UNKNOWN_TRADE INSERT ERROR\n";
                    DB::rollback();
                }
                else
                {
                    echo " >> LOAN_INFO IMPOSSIBLE => UNKNOWN_TRADE INSERT SUCCESS\n";
                    DB::commit();
                }
                Func::pushSystemErrorMessage("가상계좌 입금 처리실패", "입금처리 중 오류가 발생하여 미처리입금 등록되었습니다.<br>가상계좌 : ".$vir_acct_ssn."<br>고객번호 : ".$cust_info_no."<br>입금금액 : ".number_format($trade_money));

                $ncnt ++;

            }
            // 입금실패도 아닌데, 등록된 거래도 없는 경우 - 이럴수는 없는데... 혹시몰라 롤백처리
            else
            {
                $xcnt ++;
                DB::rollBack();
            }

            
        }

        $note = '성공:'.$cnt.'건, 실패:'.$xcnt.'건, 불명금:'.$ncnt.'건';
        echo $note."\n";

        // 배치종료기록
        if($batchLogNo > 0)
        {
            BatchController::setBatchLog($this->argument('batchNo'), $batchLogNo, $note, $start);
        }
    }

    
    // 불명금 처리
    public function insertUnknownTrade($val, $memo)
    {

        $cust_info_no = $val->cust_info_no;
        $loan_info_no = $val->loan_info_no;
        $trade_money  = $val->trade_money;
        $trade_date   = $val->trade_date;

        $bank_cd         = $val->bank_code;            
        $bank_ssn        = trim($val->mo_ssn);          // 모계좌 계좌번호
        $vir_acct_ssn    = trim($val->vir_acct_ssn);
        $in_name         = trim($val->name);
        $vacs_vact_tr_cd = $val->no;

        $v = [];
        if( $cust_info_no>0 )
        {
            $v['cust_info_no']  = $cust_info_no;
        }
        if( $loan_info_no>0 )
        {
            $v['loan_info_nos']  = $loan_info_no;
        }
        $v['trade_date']    = $trade_date;
        $v['trade_money']   = $trade_money;
        $v['trade_path_cd'] = "1";
        
        $v['in_name']       = $in_name;
        $v['mo_bank_cd']    = $bank_cd;
        // $v['bank_ssn']      = $bank_ssn;
        $v['mo_ssn']        = $bank_ssn;
        $v['vir_acct_ssn']  = $vir_acct_ssn;
        $v['memo']          = $memo;

        $v['save_status'] = "Y";
        $v['save_id']     = "SYSTEM";
        $v['save_time']   = date("YmdHis");
        $v['status']      = "A";
        $v['reg_div']     = "U";
        $v['vacs_vact_tr_cd'] = $vacs_vact_tr_cd;

        $unknown_trade_no = 0;
        $rslt = DB::dataProcess("INS", "unknown_trade", $v, null, $unknown_trade_no);
        if( $rslt!="Y" || $unknown_trade_no==0 )
        {
            return "N";
        }

        // 결과업데이트
        $rslt = DB::dataProcess("UPD", "trade_list", ['inserted'=>'U', 'insert_no'=>$unknown_trade_no, 'convert_time'=>date("YmdHis")], ["no"=>$val->no]);
        if( $rslt!="Y" )
        {
            return $rslt;
        }


        return "Y";
    }

    // 모계좌번호
    public function getMoSsn($act)
    {
        $acct = DB::table("vir_acct")->select("bank_cd, mo_ssn")->where('vir_acct_ssn',$act)->orderBy("no", "desc")->first();
        if( $acct )
        {
            return [ 'bank_cd'=>$acct->bank_cd, 'mo_ssn'=>$acct->mo_ssn ];
        }
        else
        {
            return [ 'bank_cd'=>'', 'mo_ssn'=>'' ];
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
