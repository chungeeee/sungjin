<?php

namespace App\Console\Commands\migration;

use Illuminate\Console\Command;
use DB;
use Func;
use Log;
use Arr;

class updateFax extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work:updateFax';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '팩스번호 업데이트';

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
        $rs = DB::connection('mig_erp')->table('member_list_extra')
            ->select('member_list_no, fax11, fax12, fax13')
            ->whereRaw("(fax11 is not null and fax11 != '') or (fax12 is not null and fax12 != '') or (fax13 is not null and fax13 != '')")
            ->orderby('member_list_no')->get();
        foreach($rs as $v)
        {
            unset($arrData);
            $arrData['fax11'] = $this->cubeone_encode($v->fax11, 'ENC_KEY_SOL');
            $arrData['fax12'] = $this->cubeone_encode($v->fax12, 'ENC_KEY_SOL');
            $arrData['fax13'] = $this->cubeone_encode($v->fax13, 'ENC_KEY_SOL');

            $rslt = DB::TABLE('cust_info_extra')->where(['cust_info_no'=>$v->member_list_no])->update($arrData);

            echo ".";
            $record++;
            if($record%100==0) echo $record."\n";
        }
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
}