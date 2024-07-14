<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mig;

class Migration extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'command:migration {divType? : 개별실행구분}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Data Migration';

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
     * @param  $divType 개별실행구분 CONTRACT:고객&계약, GUARANTOR:보증인, TRADE:거래원장, MORTGAGE:부동산담보, CAR:차량담보, INTEREST:미수이자, MEMO:관리메모
     * @return int
     */

    public function handle()
    {
        ini_set('memory_limit', '-1');
        $migration = new Mig($this->argument('divType'));
        $migration->MigRun();

        // UpdateLoan:Data
        exit;
    }
}
