<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Func;
use Log;
use DB;
use Loan;
use Trade;
use Invest;
use Vars;
use Auth;
use Ksnet;
use Redirect;
use DataList;
use Validator;
use Carbon;
use Artisan;
use Storage;
use Excel;
use DateTime;
use ExcelFunc;
use FastExcel;
use App\Chung\Sms;
use App\Chung\Paging;

class QuickAction extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Quick:action {--flag=}{--opt=}{--opt2=}';
    //                      php artisan Quick:action --flag=devTest --opt=e --opt2=value
    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '1회성 처리';

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
     * @return mixed
     */
	public function handle()
	{
		$flag = $this->option('flag');
		$opt = $this->option('opt');
		$opt2 = $this->option('opt2');

		if($flag == 'encryptDecrypt') {
			self::encryptDecrypt($opt, $opt2);
		} else if($flag == 'unixtime'){
			self::unixtime($opt);
		} else if($flag == 'timediff'){
			self::timediff($opt, $opt2);
		} else if($flag == 'printQuery'){
			self::printQuery();
		} else if($flag == 'devTest'){
			self::devTest($opt);
		} else if($flag == 'devTest2'){
			self::devTest2($opt);
		} else if($flag == 'devTest3'){
			self::devTest3();
		} else if($flag == 'devTest4'){
			self::devTest4();
		}
	}

    // 암복호화 변환: php artisan Quick:action --flag=encryptDecrypt --opt='e' --opt2='1255964400'
	public function encryptDecrypt($mode, $value)
	{
		if($mode == 'e')
		{
			echo Func::encrypt($value, 'ENC_KEY_SOL');
		}
		else
		{
			echo Func::decrypt($value, 'ENC_KEY_SOL');
		}
	}

    // unix 시간 변환: php artisan Quick:action --flag=unixtime --opt='1255964400'
    public function unixtime($value)
    {
        $time = Carbon::createFromTimestamp($value);
        echo $time;
    }

    // 시간계산: php artisan Quick:action --flag=timediff --opt=20180725 --opt2=20200101
    public function timediff($value, $value2)
	{
        $from = new DateTime($value);
        $to = new DateTime($value2);

        echo $from->diff($to)->days;

        //echo date_diff($from, $to)->days;
	}
        
    // 쿼리찍어보기 get 이나 first 전에 써야됨
    // php artisan Quick:action --flag=printQuery
    public function printQuery()
	{
        $_query = DB::TABLE("");
        echo Func::printQuery($_query);
	}
        
    // 테스트용
    // php artisan Quick:action --flag=devTest --opt=2001 
    public function devTest($loan_info_no)
	{
        $loanInfo = DB::table("loan_info")->where("no", $loan_info_no)->where("save_status", 'Y')->first();
        $loanInfo = Func::chungDec(["loan_info"], $loanInfo);	// CHUNG DATABASE DECRYPT

        // 입금배열
        $array_insert = Array();
        $array_insert['action_mode']      = "INSERT";
        $array_insert['trade_type']       = "01";
        $array_insert['trade_path_cd']    = "1";
        $array_insert['cust_info_no']     = $loanInfo->cust_info_no;
        $array_insert['loan_usr_info_no'] = $loanInfo->loan_usr_info_no;
        $array_insert['loan_info_no']     = $loanInfo->no;
        $array_insert['trade_date']       = date("Ymd");
        
        // 이율
        $array_insert['invest_rate']      = $loanInfo->invest_rate;
        $array_insert['income_rate']      = $loanInfo->income_rate;
        $array_insert['local_rate']       = $loanInfo->local_rate;

        // 입금액
        $array_insert['trade_money']      = $loanInfo->return_origin + $loanInfo->return_interest;
        $array_insert['lose_money']       = 0;

        // 원천징수
        $array_insert['return_money']     = $loanInfo->return_money;
        $array_insert['withholding_tax']  = $loanInfo->withholding_tax;
        $array_insert['income_tax']       = $loanInfo->income_tax;
        $array_insert['local_tax']        = $loanInfo->local_tax;
        $array_insert['memo']             = "수익지급처리";
        
        // 계좌
        $array_insert['loan_bank_cd']     = $loanInfo->loan_bank_cd;
        $array_insert['loan_bank_ssn']    = $loanInfo->loan_bank_ssn;
        $array_insert['loan_bank_name']   = $loanInfo->loan_bank_name;
        $array_insert['cust_bank_cd']     = $loanInfo->cust_bank_cd;
        $array_insert['cust_bank_ssn']    = $loanInfo->cust_bank_ssn;
        $array_insert['cust_bank_name']   = $loanInfo->cust_bank_name;

        $trade = new Trade($loan_info_no);
        $loan_info_trade_no = $trade->tradeInInsert($array_insert, 'SYSTEM');

        // 정상 처리될 경우, loan_info_trade의 no가 응답, 오류인경우 오류 메세지 응답
        if( !is_numeric($loan_info_trade_no) )
        {
            DB::rollBack();
            $array_result['rs']  = "N";
            $array_result['msg'] = "송금금액 입금거래등록에 실패하였습니다.";

            echo print_r($array_result,1);
        }

        echo $loan_info_trade_no;
	}

    // 테스트용2
    // php artisan Quick:action --flag=devTest2 --opt=2001 
    public function devTest2($loan_info_no)
    {
        Invest::updateSavePlan($loan_info_no, date("Ymd"));
    }
        
    // 테스트용3
    // php artisan Quick:action --flag=devTest3
    public function devTest3()
	{
	}
        
    // 테스트용4
    // php artisan Quick:action --flag=devTest4
    public function devTest4()
	{
	}
}