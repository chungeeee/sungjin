<?php

namespace App\Console\Commands;

use App\Models\ExcelImport;
use Illuminate\Console\Command;
use DB;
use Func;
use Log;
use ExcelFunc;
use FastExcel;
use Excel;
use Vars;
use Carbon;
use Invest;
use App\Chung\ExcelCustomExport;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Config\BatchController;

class ImgTaskToDivision extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ImgTaskToDivision:action {loan_info_no? : 투자일련번호}';
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

        if ($this->argument('loan_info_no')) {
            $LoanInfoNo = $this->argument('loan_info_no');
        }
        else{
            echo "no LoanInfoNo";
        }

        $docInfoCon = DB::table('loan_usr_info_img')->select('no')
                                                ->where('save_status', 'Y')
                                                ->where('loan_info_no', $LoanInfoNo)
                                                ->get();

        log::info(print_r($docInfoCon, true));

        foreach($docInfoCon as $dic){
            $no = $dic->no;
            $rsltCon = DB::dataProcess('UPD', 'loan_usr_info_img', $con, ["no"=>$no]);
            if($rsltCon != "Y")
            {
                log::info("fail con : " . $no);
                DB::rollback();
            }
        }

        $docInfoCom = DB::table('loan_usr_info_img')->select('no')
                                                ->where('save_status', 'Y')
                                                ->where('loan_info_no', $LoanInfoNo)
                                                ->get();

        foreach($docInfoCom as $dicm){
            $no = $dicm->no;
            $rsltCom = DB::dataProcess('UPD', 'loan_usr_info_img', $com, ["no"=>$no]);
            if($rsltCom != "Y")
            {
                log::info("fail com : " . $no);
                DB::rollback();
            }
        }
	}
}