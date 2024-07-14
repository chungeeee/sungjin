<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Func;
use Loan;
use App\Http\Controllers\Config\BatchController;

class UpdateMisuRevMoney extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'UpdateLoan:MisuRevMoney {batchNo?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '여신계약원장 미수결산이자 업데이트';

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



		$cnt = 1;
        // 기본쿼리
        $LOAN = DB::TABLE("LOAN_INFO");
		$LOAN = $LOAN->SELECT("NO, LOAN_DATE, LOAN_TERM, LOAN_RATE, LOAN_DELAY_RATE, DELAY_TERM, CONTRACT_DAY, RETURN_METHOD_CD, BALANCE, SETTLE_INTEREST, INTEREST_SUM, RETURN_DATE_BIZ, STATUS");
		$LOAN = $LOAN->WHERE("SAVE_STATUS", "Y");
        $LOAN = $LOAN->WHERE("DELAY_TERM", ">=", "90");
		$LOAN = $LOAN->WHEREIN('STATUS', ['A','B']);
		$LOAN = $LOAN->WHERERAW('( COALESCE(BALANCE,0) + COALESCE(SETTLE_INTEREST,0) + COALESCE(INTEREST_SUM,0) ) > 0');
		$LOAN->ORDERBY("NO");

		$RSLT = $LOAN->GET();
        $RSLT = Func::chungDec(["LOAN_INFO"], $RSLT);	// CHUNG DATABASE DECRYPT
        
        foreach( $RSLT as $v )
        {

			$loan = new Loan($v->no);


            $_DATA['STATUS']       = $v->status;
            $_DATA['DELAY_TERM']   = $v->delay_term;
            $_DATA['INTEREST_SUM'] = $v->interest_sum;


			// 미수수익 START ////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////

			// 정상, 연체 90일 이하 = 당일이자
			if( $_DATA['STATUS']=="A" || ( $_DATA['STATUS']=="B" && $_DATA['DELAY_TERM']<=90 ) )
			{
				$_DATA['MISU_REV_MONEY'] = $_DATA['INTEREST_SUM'];
			}
			// 연체 90 이상
			else if( $_DATA['STATUS']=="B" && $_DATA['DELAY_TERM']>90 )
			{
				// 연체 90일차 일
				$d90_date = Loan::addDay($v->return_date_biz, 90);

				// 연체 90일차(상환일+90일) 이후에 이수일이 있는 경우는 부족금만 대상
				if( $d90_date <= $loan->loanInfo['take_date'] )
				{
					$_DATA['MISU_REV_MONEY'] = Func::nvl($loan->loanInfo['lack_interest'],0) + Func::nvl($loan->loanInfo['lack_delay_money'],0) + Func::nvl($loan->loanInfo['lack_delay_interest'],0);
				}
				// 그전 이수일이 있는 경우는 90일자의 이자 계산
				else
				{
					$val = $loan->getInterest($d90_date);
					$_DATA['MISU_REV_MONEY'] = $val['interest_sum'];
				}
			}
			// 그외
			else
			{
				$_DATA['MISU_REV_MONEY'] = 0;
			}
			// 미수수익 END //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////


            echo $v->no." ".$v->status." ".$v->delay_term." ".$v->interest_sum." ".$_DATA['MISU_REV_MONEY']."\n";


			$rslt = DB::dataProcess('UPD', 'LOAN_INFO', ["MISU_REV_MONEY"=>$_DATA['MISU_REV_MONEY']], ["no"=>$v->no]);
            echo $rslt."\n";
            if( $rslt!='Y' )
            {
                exit;
            }

        }

    }

}
