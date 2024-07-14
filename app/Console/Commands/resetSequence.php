<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use DB;
use Func;
use Log;
use Arr;

class resetSequence extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work:resetSequence';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '시퀀스 리셋';

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
        $arrayTable = array(
            "account_transfer"          => array("seq_key"=>"no", "idx"=>"account_transfer_no_seq", "minValue"=>"1"),
            "advance_deposit"           => array("seq_key"=>"no", "idx"=>"advance_deposit_no_seq", "minValue"=>"1"),
            "board"                     => array("seq_key"=>"no", "idx"=>"board_seq", "minValue"=>"1"),
            "board_cmt"                 => array("seq_key"=>"no", "idx"=>"board_cmt_seq", "minValue"=>"1"),
            "board_file"                => array("seq_key"=>"no", "idx"=>"board_file_seq", "minValue"=>"1"),
            "branch"                    => array("seq_key"=>"", "idx"=>""),
            "branch_memo"               => array("seq_key"=>"no", "idx"=>"branch_memo_seq", "minValue"=>"1"),
            "close_data"                => array("seq_key"=>"", "idx"=>""),
            "complain"                  => array("seq_key"=>"no", "idx"=>"complain_seq", "minValue"=>"1"),
            "conf_batch"                => array("seq_key"=>"no", "idx"=>"conf_batch_seq", "minValue"=>"1"),
            "conf_batch_log"            => array("seq_key"=>"no", "idx"=>"conf_batch_log_seq", "minValue"=>"1"),
            "conf_cate"                 => array("seq_key"=>"", "idx"=>""),
            "conf_code"                 => array("seq_key"=>"", "idx"=>""),
            "conf_menu"                 => array("seq_key"=>"", "idx"=>""),
            "conf_menu_branch"          => array("seq_key"=>"", "idx"=>""),
            "conf_menu_head"            => array("seq_key"=>"", "idx"=>""),
            "conf_menu_user"            => array("seq_key"=>"", "idx"=>""),
            "conf_sub_code"             => array("seq_key"=>"", "idx"=>""),
            "cust_info"                 => array("seq_key"=>"no", "idx"=>"cust_info_seq", "minValue"=>"1"),
            "cust_info_extra"           => array("seq_key"=>"", "idx"=>""),
            "cust_info_img"             => array("seq_key"=>"no", "idx"=>"cust_info_img_seq", "minValue"=>"1"),
            "cust_info_log"             => array("seq_key"=>"", "idx"=>""),
            "cust_info_memo"            => array("seq_key"=>"no", "idx"=>"cust_info_memo_seq", "minValue"=>"1"),
            "day_conf"                  => array("seq_key"=>"", "idx"=>""),
            "excel_down_log"            => array("seq_key"=>"no", "idx"=>"excel_down_log_seq", "minValue"=>"1"),
            "loan_info"                 => array("seq_key"=>"no", "idx"=>"loan_info_seq", "minValue"=>"1"),
            "loan_info_cday"            => array("seq_key"=>"", "idx"=>""),
            "loan_info_end_log"         => array("seq_key"=>"no", "idx"=>"loan_info_end_log_no_seq", "minValue"=>"1"),
            "loan_info_img"             => array("seq_key"=>"no", "idx"=>"loan_info_img_seq", "minValue"=>"1"),
            "loan_info_invest_rate"     => array("seq_key"=>"", "idx"=>""),
            "loan_info_log"             => array("seq_key"=>"no", "idx"=>"loan_info_log_seq", "minValue"=>"1"),
            "loan_info_rate"            => array("seq_key"=>"", "idx"=>""),
            "loan_info_return_plan"     => array("seq_key"=>"no", "idx"=>"loan_info_return_plan_log_seq", "minValue"=>"1"),
            "loan_info_trade"           => array("seq_key"=>"no", "idx"=>"loan_info_trade_seq", "minValue"=>"1"),
            "loan_usr_info"             => array("seq_key"=>"no", "idx"=>"loan_usr_info_no_seq", "minValue"=>"1"),
            "loan_usr_info_doc"         => array("seq_key"=>"", "idx"=>""),
            "loan_usr_info_img"         => array("seq_key"=>"no", "idx"=>"loan_usr_info_img_seq", "minValue"=>"1"),
            "loan_usr_info_log"         => array("seq_key"=>"", "idx"=>""),
            "loan_usr_info_memo"        => array("seq_key"=>"no", "idx"=>"loan_usr_info_memo_seq", "minValue"=>"1"),
            "lump_master_log"           => array("seq_key"=>"no", "idx"=>"lump_master_log_no_seq", "minValue"=>"1"),
            "messages"                  => array("seq_key"=>"no", "idx"=>"messages_seq", "minValue"=>"1"),
            "migrations"                => array("seq_key"=>"id", "idx"=>"migrations_id_seq", "minValue"=>"1"),
            "paper_form"                => array("seq_key"=>"", "idx"=>""),
            "platform_fee_rate"         => array("seq_key"=>"", "idx"=>""),
            "report_daily"              => array("seq_key"=>"", "idx"=>""),
            "report_loan_info"          => array("seq_key"=>"", "idx"=>""),
            "sms_cnt"                   => array("seq_key"=>"", "idx"=>""),
            "sms_msg"                   => array("seq_key"=>"no", "idx"=>"sms_msg_seq", "minValue"=>"1"),
            "submit_sms_log"            => array("seq_key"=>"no", "idx"=>"submit_sms_log_seq", "minValue"=>"1"),
            "users"                     => array("seq_key"=>"", "idx"=>""),
            "users_access_log"          => array("seq_key"=>"no", "idx"=>"users_access_log_seq", "minValue"=>"1"),
            "users_log"                 => array("seq_key"=>"", "idx"=>""),
            "users_login_history"       => array("seq_key"=>"", "idx"=>""),
            "vir_acct_mo"               => array("seq_key"=>"no", "idx"=>"vir_acct_mo_no_seq", "minValue"=>"1"),
            "vir_acct_mo_end"           => array("seq_key"=>"", "idx"=>""),
            "vir_acct_mo_trade"         => array("seq_key"=>"no", "idx"=>"vir_acct_mo_trade_no_seq", "minValue"=>"1"),                
            "wonjang_chg_hist"          => array("seq_key"=>"no", "idx"=>"wonjang_chg_hist_seq", "minValue"=>"1")
        );

        foreach($arrayTable as $tbl => $arrInfo)
        {
            if($arrInfo['idx'] && $arrInfo['seq_key'])
            {
                echo "[".date("Y-m-d H:i:s")."] ".strtoupper($tbl)." 시퀀스 초기화 시작\n";
                $max_no = DB::TABLE($tbl)->max($arrInfo['seq_key']);
                if($max_no > 0)
                {
                    $nextVal = $max_no+1;
                }
                else
                {
                    $nextVal = $arrInfo['minValue'];
                }

                $sql = "ALTER SEQUENCE ".$arrInfo['idx']." RESTART WITH ".$nextVal.";";
                DB::statement($sql, []);
                echo "[".date("Y-m-d H:i:s")."] ".strtoupper($tbl)." 시퀀스 초기화 완료\n";
            }
        }
    }
}