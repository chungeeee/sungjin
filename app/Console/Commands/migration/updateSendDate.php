<?php

namespace App\Console\Commands\migration;

use Illuminate\Console\Command;
use DB;
use Func;
use Log;
use Arr;

class updateSendDate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work:updateSendDate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '기한의이익상실&채불등록예정 통지서 발송일, 등기번호 일괄 업데이트';

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
        $fp = fopen("/home/laravel/storage/logs/migration/updateSendDate.log", "w");

        echo "계약 UPDATE 시작\n";
        $record = 0;
        $rs = DB::connection('mig_erp')->table('contract_info')->select('no, trigger_send_date, trigger_reg_no, bad_send_date')->where('save_status','Y')->orderby('no')->get();
        foreach($rs as $v)
        {
            unset($arrData);
            $arrData['trigger_send_date'] = ($v->trigger_send_date) ? $this->convDateTime($v->trigger_send_date) : "";
            $arrData['trigger_reg_no'] = $v->trigger_reg_no;
            $arrData['bad_send_date'] = ($v->bad_send_date) ? $this->convDateTime($v->bad_send_date) : "";

            $rslt = DB::TABLE('loan_info')->where(['no'=>$v->no])->update($arrData);

            echo ".";
            $record++;
            if($record%100==0) echo $record."\n";
        }
        echo "계약 UPDATE 완료\n\n";


        echo "보증인 UPDATE 시작\n";
        $record = 0;
        $rs = DB::connection('mig_erp')->table('guarantor')
            ->select('contract_info_no, name, ssn11, ssn12, save_status, status, trigger_send_date, trigger_reg_no, bad_send_date, bad_reg_no')
            ->where('save_status','Y')
            ->whereRaw("(trigger_send_date is not null or (trigger_reg_no is not null and trigger_reg_no != '')) or (bad_send_date is not null or (bad_reg_no is not null and bad_reg_no != ''))")
            ->orderby('no')->get();
        foreach($rs as $v)
        {
            unset($arrData);
            $arrData['trigger_send_date'] = ($v->trigger_send_date) ? $this->convDateTime($v->trigger_send_date) : "";
            $arrData['trigger_reg_no'] = $v->trigger_reg_no;
            $arrData['bad_send_date'] = ($v->bad_send_date) ? $this->convDateTime($v->bad_send_date) : "";
            $arrData['bad_reg_no'] = $v->bad_reg_no;

            unset($arrSearch);
            $arrSearch['loan_info_no'] = $v->contract_info_no;
            $arrSearch['name'] = $this->cubeone_convert($v->name);
            $arrSearch['ssn'] = $this->cubeone_encode($this->cubeone_euc_decode($v->ssn11).$this->cubeone_euc_decode($v->ssn12), 'ENC_KEY_SOL');
            $arrSearch['status'] = $v->status;
            $arrSearch['save_status'] = $v->save_status;

            unset($_LOG);
            $_LOG[] = $arrSearch['loan_info_no'];
            $_LOG[] = $arrSearch['name'];
            $_LOG[] = $arrSearch['ssn'];
            $_LOG[] = $arrSearch['status'];
            $_LOG[] = $arrSearch['save_status'];

            $cnt = DB::TABLE('loan_info_guarantor')->where($arrSearch)->count();
            if($cnt>=1)
            {
                if($cnt>1) $_LOG[] = "복수건 - 업데이트 대상";
                else $_LOG[] = "단건 - 업데이트 대상";

                $rslt = DB::TABLE('loan_info_guarantor')->where($arrSearch)->update($arrData);
            }
            else
            {
                $_LOG[] = "매칭정보 없음";
            }

            fwrite($fp, implode("\t", $_LOG)."\n");


            echo ".";
            $record++;
            if($record%100==0) echo $record."\n";
        }
        echo "보증인 UPDATE 완료\n";
    }


    function cubeone_decode($str)
    {
        $rs='';
        if(trim($str) && trim($str)!="")
        {
            $errCode="00000";
            $rs=php_co_dec_char($str,"KI_AES256", 10, "tbl", "col", $errCode);
            if($errCode=='20014')
            {
                $rs=$str;
            }
        }
        else
        {
            $rs='';
        }

        return $rs;
    }

    function cubeone_encode($str, $enKey='')
    {
		$rs='';
		// 운영환경
		if(config('app.env')=='prod')
		{
            $errCode="00000";
			$rs = php_co_enc_char($str, "KI_AES256", 11, "tbl", "col", $errCode);
		}
		// 개발환경
		else
		{
			if($enKey=='ENC_KEY_SOL')
			{
				$key = config('app.enKey');
			}
			else
			{
				$key = env($enKey);
			}
			$str = trim($str);

			if(!$str || !$key)
				return $str;

			$rs = base64_encode(openssl_encrypt($str, config('app.cipher'), $key, true, str_repeat(chr(0), 16)));
		}
		return $rs;
	}

    function cubeone_euc_decode($str)
    {
        return mb_convert_encoding($this->cubeone_decode($str), "UTF-8", "EUC-KR");
    }

    function cubeone_convert($str)
    {
        return $this->cubeone_encode($this->cubeone_euc_decode($str), 'ENC_KEY_SOL');
    }

    //날짜변경
    public function convDateTime($date)
    {
        $date = preg_replace('/\D/', '', $date); // 문자열에서 숫자만 추출

        if(empty($date)) return null;

        if (strlen($date) == 10) { // Unix Timestamp 값인 경우
            $date = date('YmdHis',$date);
        }
        
        return $date;
    }
}