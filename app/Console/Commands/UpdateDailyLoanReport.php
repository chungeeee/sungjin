<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Log;
use Auth;
use DB;
use Func;
use App\Http\Controllers\Config\BatchController;

class UpdateDailyLoanReport extends Command
{
    /**
     * The name and signature of the console command.
     * 
     * 영업일보 배치번호(49)
     * 
     * @var string
     */
    protected $signature = 'UpdateReport:DailyLoan {batchNo?} {infoDate? : 기준일}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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

        /*
            신청정보쪽 업데이트 필요하면  지점별신청정보 업데이트 후 실행해야함!!!!!
            php artisan UpdateReport:loanApp 47 "해당날짜"
        */
        
        // 배치 기록
        $stime = time();
        $batchLogNo = $this->startBatchLog($stime);
        $save_id    = Auth::id()?Auth::id():"SYSTEM";
        $save_time  = date("YmdHis");

        // 인자값이 없다
        if(empty($this->argument('infoDate')))
        {
            // 새벽한시 이전이라면 1일전 . 아니라면 당일 기준으로 돌린다 
            $info_date = date("H")<"01"?date("Ymd" ,strtotime("-1 days")):date("Ymd");
        }
        else
        {
            $info_date = $this->argument('infoDate');
        }

        // $info_date = empty($this->argument('infoDate'))?date("Ymd" ,strtotime("-1 days")):$this->argument('infoDate');
        // $info_date = empty($this->argument('infoDate'))?date("Ymd"):$this->argument('infoDate');
        $info_date_01 = date("Ym01" ,strtotime($info_date));
        $info_date_bf1 = $info_date!=$info_date_01?(date("Ymd" ,strtotime($info_date." -1 days"))):$info_date_01;

        DB::beginTransaction();
        // 기존데이터 삭제처리
        $CHECK = DB::TABLE("REPORT_DAILY")->WHERE("INFO_DATE",$info_date)->WHERE("save_time","<",$save_time)->exists();
        if(!empty($CHECK))
        {
            log::channel('report_daily')->info("기존데이터 존재");
            try
            {
                $rslt = DB::table('REPORT_DAILY')->WHERE("INFO_DATE",$info_date)->WHERE("save_time","<",$save_time)->delete();
                log::channel('report_daily')->info("기존데이터 ".$rslt."건 삭제성공");
                $rslt = "Y";
            }
            catch (exception $ex)
            {
                $rslt = "N";
                log::channel('report_daily')->info('기존데이터 삭제실패 ');
                log::channel('report_daily')->info("EXCEPTION = ".$ex->getMessage());
                log::channel('report_daily')->info("==================================================================================");
                DB::rollback();
            }
        }
        else
        {
            $rslt = "Y";
            log::channel('report_daily')->info("기존데이터 미존재");
        }


        if($rslt == "Y")
        {
            if($info_date == date("Ymd"))
            {
                $table_name   = "LOAN_INFO";
                $loan_info_no = "NO";
                $where = " AND L.SAVE_STATUS = 'Y' ";
            }
            else
            {
                $table_name = "CB_MASTER ";
                $loan_info_no = "LOAN_INFO_NO";
                $where = " AND L.INFO_DATE = '".$info_date."' ";
            }
    
            $sql = "
            SELECT 
                E.MANAGER_CODE,E.PRO_MIDDLE_DIV,E.PRO_CD,

                -- 신청
                SUM(APP_01_D_CNT)               AS APP_01_D_CNT ,               -- 신청 신규 건수 당일
                SUM(APP_01_M_CNT)               AS APP_01_M_CNT,                -- 신청 신규 건수 당월 
                SUM(APP_REAPP_D_CNT)            AS APP_REAPP_D_CNT,             -- 재신청 신규 건수 당일
                SUM(APP_REAPP_M_CNT)            AS APP_REAPP_M_CNT,             -- 재신청 신규 건수 당월
                SUM(APP_02_D_CNT)               AS APP_02_D_CNT,                -- 재신청 재대출 건수 당일 
                SUM(APP_02_M_CNT)               AS APP_02_M_CNT,                -- 재신청 재대출 건수 당월 
                SUM(APP_04_D_CNT)               AS APP_04_D_CNT,                -- 재신청 증액 건수 당일
                SUM(APP_04_M_CNT)               AS APP_04_M_CNT,                -- 재신청 증액 건수 당월 
                SUM(APP_REAPP_TOTAL_D_CNT)      AS APP_REAPP_TOTAL_D_CNT ,      -- 당일 재신청 합계
                SUM(APP_REAPP_TOTAL_M_CNT)      AS APP_REAPP_TOTAL_M_CNT ,      -- 당월 재신청 합계 
                SUM(APP_TOTAL_D_CNT)            AS APP_TOTAL_D_CNT,             -- 당일 신청 + 재신청 합계 
                SUM(APP_TOTAL_M_CNT)            AS APP_TOTAL_M_CNT,             -- 당월 신청 + 재신청
                -- 대출
                SUM(LOAN_01_D_CNT)              AS LOAN_01_D_CNT,               -- 신규대출 건수 당일
                SUM(LOAN_01_D_MONEY)            AS LOAN_01_D_MONEY,             -- 신규대출 금액 당일
                SUM(LOAN_01_M_CNT)              AS LOAN_01_M_CNT,               -- 신규대출 건수 당일
                SUM(LOAN_01_M_MONEY)            AS LOAN_01_M_MONEY,             -- 신규대출 금액 당일
                SUM(LOAN_02_D_CNT)              AS LOAN_02_D_CNT,               -- 재대출 건수 당일
                SUM(LOAN_02_D_MONEY)            AS LOAN_02_D_MONEY,             -- 재대출 금액 당일
                SUM(LOAN_02_M_CNT)              AS LOAN_02_M_CNT,               -- 재대출 건수 당일
                SUM(LOAN_02_M_MONEY)            AS LOAN_02_M_MONEY,             -- 재대출 금액 당일
                SUM(LOAN_04_D_CNT)              AS LOAN_04_D_CNT,               -- 증액 건수 당일
                SUM(LOAN_04_D_MONEY)            AS LOAN_04_D_MONEY,             -- 증액 금액 당일
                SUM(LOAN_04_M_CNT)              AS LOAN_04_M_CNT,               -- 증액 건수 당일
                SUM(LOAN_04_M_MONEY)            AS LOAN_04_M_MONEY,             -- 증액 금액 
                SUM(LOAN_TOTAL_D_CNT)           AS LOAN_TOTAL_D_CNT,            -- 계약건수 당일
                SUM(LOAN_TOTAL_D_MONEY)         AS LOAN_TOTAL_D_MONEY,          -- 계약잔액 당일
                SUM(LOAN_TOTAL_M_CNT)           AS LOAN_TOTAL_M_CNT,            -- 계약건수 당월
                SUM(LOAN_TOTAL_M_MONEY)         AS LOAN_TOTAL_M_MONEY,          -- 계약건수 당월
                -- 원금상환
                SUM(LOAN_FULLPAY_D_CNT)         AS LOAN_FULLPAY_D_CNT,
                SUM(LOAN_RETURN_ORIGIN_D_MONEY) AS LOAN_RETURN_ORIGIN_D_MONEY,
                SUM(LOAN_FULLPAY_M_CNT)         AS LOAN_FULLPAY_M_CNT,
                SUM(LOAN_RETURN_ORIGIN_M_MONEY) AS LOAN_RETURN_ORIGIN_M_MONEY,
                --이수관
                SUM(CHANGE_MNG_D_CNT)           AS CHANGE_MNG_D_CNT,
                SUM(CHANGE_MNG_D_MONEY)         AS CHANGE_MNG_D_MONEY,
                SUM(CHANGE_MNG_M_CNT)           AS CHANGE_MNG_M_CNT,
                SUM(CHANGE_MNG_M_MONEY)         AS CHANGE_MNG_M_MONEY,
                --상각(+화해완제)
                SUM(SANGGAK_D_CNT)              AS SANGGAK_D_CNT,
                SUM(SANGGAK_D_MONEY)            AS SANGGAK_D_MONEY,
                SUM(SANGGAK_M_CNT)              AS SANGGAK_M_CNT,
                SUM(SANGGAK_M_MONEY)            AS SANGGAK_M_MONEY,
                -- 해당일 잔액
                SUM(LOAN_D_CNT)                 AS LOAN_D_CNT,
                SUM(LOAN_D_BALANCE)             AS LOAN_D_BALANCE
            FROM 
        (
        SELECT 
            COALESCE(T2.MANAGER_CODE,T1.MANAGER_CODE,T3.MANAGER_CODE,T4.MANAGER_CODE,T5.MANAGER_CODE,T6.MANAGER_CODE) AS MANAGER_CODE,
            (SELECT PRO_MIDDLE_DIV FROM PRODUCT_MANAGE WHERE PRO_CD = COALESCE(T2.PRO_CD,T1.PRO_CD,T3.PRO_CD,T4.PRO_CD,T5.PRO_CD,T6.PRO_CD) ) AS PRO_MIDDLE_DIV,
            COALESCE(T2.PRO_CD,T1.PRO_CD,T3.PRO_CD,T4.PRO_CD,T5.PRO_CD,T6.PRO_CD) AS PRO_CD,
            
            COALESCE(T1.APP_01_D,0)                                                                                             AS APP_01_D_CNT , -- 신청 신규 건수 당일
            COALESCE(T1.APP_01_M,0)                                                                                             AS APP_01_M_CNT, -- 신청 신규 건수 당월 
            COALESCE(T1.RE_APP_02_D,0)                                                                                          AS APP_REAPP_D_CNT, -- 재신청 신규 건수 당일
            COALESCE(T1.RE_APP_02_M,0)                                                                                          AS APP_REAPP_M_CNT, -- 재신청 신규 건수 당월
            COALESCE(T1.APP_02_D,0)                                                                                             AS APP_02_D_CNT, -- 재신청 재대출 건수 당일 
            COALESCE(T1.APP_02_M,0)                                                                                             AS APP_02_M_CNT, -- 재신청 재대출 건수 당월 
            COALESCE(T1.APP_04_D,0)                                                                                             AS APP_04_D_CNT, -- 재신청 증액 건수 당일
            COALESCE(T1.APP_04_M,0)                                                                                             AS APP_04_M_CNT, -- 재신청 증액 건수 당월 
            COALESCE(T1.RE_APP_02_D,0)+COALESCE(T1.APP_02_D,0)+COALESCE(T1.APP_04_D,0)                                          AS APP_REAPP_TOTAL_D_CNT , -- 당일 재신청 합계
            COALESCE(T1.RE_APP_02_M,0)+COALESCE(T1.APP_02_M,0)+COALESCE(T1.APP_04_M,0)                                          AS APP_REAPP_TOTAL_M_CNT , -- 당월 재신청 합계 
            COALESCE(T1.APP_01_D,0)+COALESCE(T1.RE_APP_02_D,0)+COALESCE(T1.APP_02_D,0)+COALESCE(T1.APP_04_D,0)                  AS APP_TOTAL_D_CNT,        -- 당일 신청 + 재신청 합계 
            COALESCE(T1.APP_01_M,0)+COALESCE(T1.RE_APP_02_M,0)+COALESCE(T1.APP_02_M,0)+COALESCE(T1.APP_04_M,0)                  AS APP_TOTAL_M_CNT,        -- 당월 신청 + 재신청 합계 
            COALESCE(T2.LOAN_01_CNT,0)                                                                                          AS LOAN_01_D_CNT, -- 신규대출 당일 건수
            COALESCE(T2.LOAN_01_MONEY,0)                                                                                        AS LOAN_01_D_MONEY,-- 신규대출 당일 금액
            COALESCE(T2.LOAN_01_CNT,0)+COALESCE(T6.LOAN_01_M_CNT,0)                                                             AS LOAN_01_M_CNT,
            COALESCE(T2.LOAN_01_MONEY,0)+COALESCE(T6.LOAN_01_M_MONEY,0)                                                         AS LOAN_01_M_MONEY,
            COALESCE(T2.LOAN_02_CNT,0) 						                                                                    AS LOAN_02_D_CNT, -- 재대출 당일 건수
            COALESCE(T2.LOAN_02_MONEY,0) 								                                                        AS LOAN_02_D_MONEY, --재대출 당일 금액 
            COALESCE(T2.LOAN_02_CNT,0)+COALESCE(T6.LOAN_02_M_CNT,0)                                                             AS LOAN_02_M_CNT,
            COALESCE(T2.LOAN_02_MONEY,0)+COALESCE(T6.LOAN_02_M_MONEY,0)                                                         AS LOAN_02_M_MONEY,
            COALESCE(T2.LOAN_04_CNT,0) 								                                                            AS LOAN_04_D_CNT, 								
            COALESCE(T2.LOAN_04_MONEY,0) 								                                                        AS LOAN_04_D_MONEY, 							
            COALESCE(T2.LOAN_04_CNT,0)+COALESCE(T6.LOAN_04_M_CNT,0)                                                             AS LOAN_04_M_CNT,
            COALESCE(T2.LOAN_04_MONEY,0)+COALESCE(T6.LOAN_04_M_MONEY,0)                                                         AS LOAN_04_M_MONEY,
            COALESCE(T2.LOAN_01_CNT,0)+COALESCE(T2.LOAN_02_CNT,0)+COALESCE(T2.LOAN_04_CNT,0) 								    AS LOAN_TOTAL_D_CNT,    -- 당일대출 총 건수 
            COALESCE(T2.LOAN_01_MONEY,0)+COALESCE(T2.LOAN_02_MONEY,0)+COALESCE(T2.LOAN_04_MONEY,0) 								AS LOAN_TOTAL_D_MONEY,  -- 당일대출 총 금액
            COALESCE(T2.LOAN_01_CNT,0)+COALESCE(T2.LOAN_02_CNT,0)+COALESCE(T2.LOAN_04_CNT,0)+COALESCE(T6.LOAN_TOTAL_M_CNT,0) 		    AS LOAN_TOTAL_M_CNT,    -- 당월대출 총 건수 
            COALESCE(T2.LOAN_01_MONEY,0)+COALESCE(T2.LOAN_02_MONEY,0)+COALESCE(T2.LOAN_04_MONEY,0)+COALESCE(T6.LOAN_TOTAL_M_MONEY,0)    AS LOAN_TOTAL_M_MONEY,  -- 당일대출 총 금액 	
            COALESCE(T4.LOAN_FULLPAY_D_CNT,0) 										                                            AS LOAN_FULLPAY_D_CNT,
            COALESCE(T2.RETURN_ORIGIN_D,0) 										                                                AS LOAN_RETURN_ORIGIN_D_MONEY, -- 대출상환 당월 금액
            COALESCE(T4.LOAN_FULLPAY_D_CNT,0)+COALESCE(T6.LOAN_FULLPAY_M_CNT,0) 		                                        AS LOAN_FULLPAY_M_CNT, 
            COALESCE(T2.RETURN_ORIGIN_D,0)+COALESCE(T6.LOAN_RETURN_ORIGIN_M_MONEY,0) 	                                        AS LOAN_RETURN_ORIGIN_M_MONEY,
            COALESCE(T3.LOG_CNT_D,0)                                                                                            AS CHANGE_MNG_D_CNT,
            COALESCE(T3.LOG_BALANCE_D,0)                                                                                        AS CHANGE_MNG_D_MONEY,
            COALESCE(T3.LOG_CNT_M,0)                                                                                            AS CHANGE_MNG_M_CNT,
            COALESCE(T3.LOG_BALANCE_M,0)                                                                                        AS CHANGE_MNG_M_MONEY,
            COALESCE(T5.SANGGAK_D_CNT,0) 								                                                        AS SANGGAK_D_CNT,
            COALESCE(T5.SANGGAK_D_MONEY,0)                                                                                      AS SANGGAK_D_MONEY,
            COALESCE(T5.SANGGAK_D_CNT,0)+COALESCE(T6.SANGGAK_M_CNT,0) 		                                                    AS SANGGAK_M_CNT,
            COALESCE(T5.SANGGAK_D_MONEY,0)+COALESCE(T6.SANGGAK_M_MONEY,0) 	                                                    AS SANGGAK_M_MONEY,	
            COALESCE(T4.LOAN_D_CNT,0) 		                                                                                    AS LOAN_D_CNT,
            COALESCE(T4.LOAN_D_BALANCE,0) 	                                                                                    AS LOAN_D_BALANCE
        
        FROM
            (
                SELECT 
                    MANAGER_CODE,PRO_CD
                    ,SUM(CASE WHEN INFO_DATE='".$info_date."' AND APP_TYPE_CD = '01' AND RE_APP_YN = 'N' THEN APP_CNT ELSE 0 END ) AS APP_01_D
                    ,SUM(CASE WHEN APP_TYPE_CD = '01' AND RE_APP_YN = 'N' THEN APP_CNT ELSE 0 END ) AS APP_01_M
                    ,SUM(CASE WHEN INFO_DATE='".$info_date."' AND APP_TYPE_CD = '01' AND RE_APP_YN = 'Y' THEN APP_CNT ELSE 0 END ) AS RE_APP_02_D
                    ,SUM(CASE WHEN APP_TYPE_CD = '01' AND RE_APP_YN = 'Y' THEN APP_CNT ELSE 0 END ) AS RE_APP_02_M
                    ,SUM(CASE WHEN INFO_DATE='".$info_date."' AND APP_TYPE_CD = '02'  THEN APP_CNT ELSE 0 END ) AS APP_02_D
                    ,SUM(CASE WHEN APP_TYPE_CD = '02' THEN APP_CNT ELSE 0 END ) AS APP_02_M
                    ,SUM(CASE WHEN INFO_DATE='".$info_date."' AND APP_TYPE_CD = '04'  THEN APP_CNT ELSE 0 END ) AS APP_04_D
                    ,SUM(CASE WHEN APP_TYPE_CD = '04' THEN APP_CNT ELSE 0 END ) AS APP_04_M
                    FROM 
                    REPORT_LOAN_APP 
                    WHERE
                    INFO_DATE >= '".$info_date_01."'
                    AND INFO_DATE<='".$info_date."'
                    AND MANAGER_CODE != '001' -- ASIS에서 제외하고있어 그대로 적용
                    AND APP_CNT > 0
                    GROUP BY 
                    MANAGER_CODE,PRO_CD
            ) T1 -- 신청건 테이블 
            FULL OUTER JOIN
            ( 
                SELECT 
                LOAN.MANAGER_CODE,PRO_CD,
                SUM(LOAN_01_CNT) AS LOAN_01_CNT,SUM(LOAN_01_MONEY) AS LOAN_01_MONEY, SUM(LOAN_02_CNT) AS LOAN_02_CNT,SUM(LOAN_02_MONEY) AS LOAN_02_MONEY,
                SUM(LOAN_04_CNT) AS LOAN_04_CNT,SUM(LOAN_04_MONEY) AS LOAN_04_MONEY,SUM(RETURN_ORIGIN_D) AS RETURN_ORIGIN_D 
                FROM
                    (
                    -- 해당일자 거래 
                    SELECT  
                        L.MANAGER_CODE
                        ,L.PRO_CD
                        ,T.LOAN_INFO_NO
                        ,SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '11'  THEN 1 ELSE 0 END) AS LOAN_01_CNT
                        ,SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '11'  THEN T.TRADE_MONEY ELSE 0 END) AS LOAN_01_MONEY
                        ,SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '13'  THEN 1 ELSE 0 END) AS LOAN_02_CNT 
                        ,SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '13'  THEN T.TRADE_MONEY ELSE 0 END ) AS LOAN_02_MONEY                     
                        ,SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '12'  THEN 1 ELSE 0 END) AS LOAN_04_CNT 
                        ,SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '12'  THEN T.TRADE_MONEY ELSE 0 END ) AS LOAN_04_MONEY 
                        ,SUM(CASE WHEN T.TRADE_DIV='I' AND LOAN_SANGGAK_YN = 'N'THEN T.RETURN_ORIGIN ELSE 0 END) AS RETURN_ORIGIN_D
                    FROM 
                        LOAN_INFO_TRADE T , ".$table_name."  L 
                    WHERE 
                        L.".$loan_info_no." = T.LOAN_INFO_NO 
                        AND L.MANAGER_CODE != '001'
                        AND L.STATUS IN ('A','B','C','D','E','S','X','M')
                        AND T.SAVE_STATUS = 'Y' AND SUBSTR(T.SAVE_TIME,1,8) = '".$info_date."'
                        ".$where." 
                    GROUP BY 
                        L.MANAGER_CODE,L.PRO_CD,T.LOAN_INFO_NO
                    
                    UNION ALL
                    -- 해당일자 거래는 아니지만 해당일자에 취소된 거래
                    SELECT  
                        L.MANAGER_CODE
                        ,L.PRO_CD
                        ,T.LOAN_INFO_NO
                        ,-SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '11' THEN 1 ELSE 0 END) AS LOAN_01_CNT
                        ,-SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '11' THEN T.TRADE_MONEY ELSE 0 END) AS LOAN_01_MONEY
                        ,-SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '13' THEN 1 ELSE 0 END) AS LOAN_02_CNT 
                        ,-SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '13' THEN T.TRADE_MONEY ELSE 0 END ) AS LOAN_02_MONEY 
                        ,-SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '12' THEN 1 ELSE 0 END) AS LOAN_04_CNT 
                        ,-SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '12' THEN T.TRADE_MONEY ELSE 0 END ) AS LOAN_04_MONEY 
                        ,-SUM(CASE WHEN T.TRADE_DIV='I' AND LOAN_SANGGAK_YN = 'N' THEN T.RETURN_ORIGIN ELSE 0 END) AS RETURN_ORIGIN_D
                    FROM 
                        LOAN_INFO_TRADE T , ".$table_name."  L 
                    WHERE 
                        L.".$loan_info_no." = T.LOAN_INFO_NO 
                        AND L.MANAGER_CODE != '001'
                        AND L.STATUS IN ('A','B','C','D','E','S','X','M')
                        AND T.SAVE_STATUS = 'N' AND SUBSTR(T.SAVE_TIME,1,8) != SUBSTR(T.DEL_TIME,1,8)  AND SUBSTR(T.DEL_TIME,1,8) = '".$info_date."' 
                        ".$where."
                    GROUP BY 
                        L.MANAGER_CODE,L.PRO_CD,T.LOAN_INFO_NO
                    UNION ALL
                    -- 해당일자 거래이면서 해당일자 이후에 취소된 거래
                    SELECT  
                        L.MANAGER_CODE
                        ,L.PRO_CD
                        ,T.LOAN_INFO_NO
                        ,+SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '11'  THEN 1 ELSE 0 END) AS LOAN_01_CNT
                        ,+SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '11'  THEN T.TRADE_MONEY ELSE 0 END) AS LOAN_01_MONEY
                        ,+SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '13'  THEN 1 ELSE 0 END) AS LOAN_02_CNT 
                        ,+SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '13'  THEN T.TRADE_MONEY ELSE 0 END ) AS LOAN_02_MONEY
                        ,+SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '12' THEN 1 ELSE 0 END) AS LOAN_04_CNT 
                        ,+SUM(CASE WHEN T.TRADE_DIV='O' AND T.TRADE_TYPE = '12'  THEN T.TRADE_MONEY ELSE 0 END ) AS LOAN_04_MONEY      
                        ,+SUM(CASE WHEN T.TRADE_DIV='I' AND LOAN_SANGGAK_YN = 'N'  THEN T.RETURN_ORIGIN  ELSE 0 END) AS RETURN_ORIGIN_D
                    FROM 
                        LOAN_INFO_TRADE T , ".$table_name."  L 
                    WHERE 
                        L.".$loan_info_no." = T.LOAN_INFO_NO 
                        AND L.MANAGER_CODE != '001'
                        AND L.STATUS IN ('A','B','C','D','E','S','X','M')
                        AND T.SAVE_STATUS = 'N' AND SUBSTR(T.SAVE_TIME,1,8) = '".$info_date."' AND  SUBSTR(T.DEL_TIME,1,8) > '".$info_date."'
                        ".$where."  
                    GROUP BY 
                        L.MANAGER_CODE,L.PRO_CD,T.LOAN_INFO_NO
                    ) LOAN 
                GROUP BY LOAN.MANAGER_CODE,LOAN.PRO_CD
            ) T2 -- 해당일자 대출테이블 
            ON T1.MANAGER_CODE = T2.MANAGER_CODE
            AND T1.PRO_CD = T2.PRO_CD
            FULL OUTER JOIN 
            (
                SELECT 
                    MANAGER_CODE, PRO_CD
                    , SUM((CASE WHEN DIV = 'D' THEN CNT ELSE 0 END)) AS LOG_CNT_D , SUM((CASE WHEN DIV = 'D' THEN BALANCE ELSE 0 END)) AS LOG_BALANCE_D
                    , SUM((CASE WHEN DIV = 'M' THEN CNT ELSE 0 END)) AS LOG_CNT_M , SUM((CASE WHEN DIV = 'M' THEN BALANCE ELSE 0 END)) AS LOG_BALANCE_M		            
                FROM 
                (
                    SELECT 'D' AS DIV, OLD_MANAGER_CODE AS MANAGER_CODE,-1 AS CNT, (SELECT PRO_CD FROM LOAN_INFO WHERE NO = LOAN_INFO_NO) AS PRO_CD , -BALANCE AS BALANCE 
                    FROM LOAN_INFO_LOG_MANAGER  
                    WHERE SUBSTR(SAVE_TIME,1,8) ='".$info_date."'  AND COALESCE(OLD_MANAGER_CODE,'')!='' AND COALESCE(NEW_MANAGER_CODE,'')!=''
                    UNION ALL
                    SELECT 'D' AS DIV, NEW_MANAGER_CODE  AS MANAGER_CODE, 1 AS CNT, (SELECT PRO_CD FROM LOAN_INFO WHERE NO = LOAN_INFO_NO) AS PRO_CD , +BALANCE  AS BALANCE 
                    FROM LOAN_INFO_LOG_MANAGER  
                    WHERE SUBSTR(SAVE_TIME,1,8) ='".$info_date."' AND  COALESCE(OLD_MANAGER_CODE,'')!='' AND COALESCE(NEW_MANAGER_CODE,'')!=''
                    UNION ALL
                    SELECT 'M' AS DIV, OLD_MANAGER_CODE AS MANAGER_CODE, -1 AS CNT,(SELECT PRO_CD FROM LOAN_INFO WHERE NO = LOAN_INFO_NO) AS PRO_CD , -BALANCE AS BALANCE 
                    FROM LOAN_INFO_LOG_MANAGER  
                    WHERE SUBSTR(SAVE_TIME,1,8) >= '".$info_date_01."' AND SUBSTR(SAVE_TIME,1,8) <= '".$info_date."' AND COALESCE(OLD_MANAGER_CODE,'')!='' AND COALESCE(NEW_MANAGER_CODE,'')!=''
                    UNION ALL
                    SELECT 'M' AS DIV, NEW_MANAGER_CODE  AS MANAGER_CODE, +1 AS CNT, (SELECT PRO_CD FROM LOAN_INFO WHERE NO = LOAN_INFO_NO) AS PRO_CD , +BALANCE  AS BALANCE 
                    FROM LOAN_INFO_LOG_MANAGER  
                    WHERE SUBSTR(SAVE_TIME,1,8) >='".$info_date_01."'AND SUBSTR(SAVE_TIME,1,8) <= '".$info_date."' AND COALESCE(OLD_MANAGER_CODE,'')!='' AND COALESCE(NEW_MANAGER_CODE,'')!=''
                ) AS FOO
                GROUP BY MANAGER_CODE, PRO_CD
            ) T3 -- 이수관테이블
            ON (T3.MANAGER_CODE = COALESCE(T2.MANAGER_CODE,T1.MANAGER_CODE) AND T3.PRO_CD =  COALESCE(T2.PRO_CD,T1.PRO_CD))
            FULL OUTER JOIN 
            (
                SELECT 
                CASE WHEN COALESCE(MANAGER_CODE,'') = '' THEN 'N' ELSE COALESCE(MANAGER_CODE,'') END MANAGER_CODE, 
                PRO_CD ,
                COUNT(CASE WHEN STATUS IN ('A','B','C','D') THEN 1 END) AS LOAN_D_CNT, 
                SUM(CASE WHEN STATUS IN ('A','B','C','D') THEN BALANCE END) AS LOAN_D_BALANCE, 
                COUNT(CASE WHEN STATUS NOT IN ('A','B','C','D') THEN 1 END) AS LOAN_FULLPAY_D_CNT
                FROM ".$table_name." L  WHERE 
                (STATUS IN ('A','B','C','D') OR 
                (STATUS IN ('E','X') AND FULLPAY_DATE = '".$info_date."' AND (COALESCE(SANGGAK_DATE,'') = '' or SANGGAK_DATE = '".$info_date."' ))  OR
                (STATUS = 'S' AND SANGGAK_DATE = '".$info_date."') OR
                (STATUS = 'M' AND SELL_DATE = '".$info_date."' AND (COALESCE(SANGGAK_DATE,'') = '' or SANGGAK_DATE = '".$info_date."' ) ) -- 매각건들 중 상각이였던 계약은 이미 유효건에서 제외됐을테니 상각일 없는 매각건들만 찾자
                )
                ".$where." GROUP BY COALESCE(MANAGER_CODE,'') , PRO_CD 
            ) T4 -- 완제건수, 당일채권 건수,잔액
            ON T4.MANAGER_CODE = COALESCE(T2.MANAGER_CODE,T1.MANAGER_CODE,T3.MANAGER_CODE) AND T4.PRO_CD =  COALESCE(T2.PRO_CD,T1.PRO_CD,T3.PRO_CD)
            FULL OUTER JOIN 
            (
                SELECT 
                    S.SANGGAK_MANAGER_CODE AS MANAGER_CODE,S.PRO_CD,COUNT(1) AS SANGGAK_D_CNT, SUM(SANGGAK_BALANCE) AS SANGGAK_D_MONEY 
                FROM
                    (
                        SELECT SANGGAK_MANAGER_CODE, (SELECT PRO_CD FROM LOAN_INFO WHERE NO = LOAN_INFO_NO) AS PRO_CD ,SANGGAK_BALANCE
                        FROM LOAN_SANGGAK 
                        WHERE  SANGGAK_DATE = '".$info_date."' AND SAVE_STATUS ='Y' AND SANGGAK_BALANCE>0
                    )S 
                GROUP BY 
                    S.SANGGAK_MANAGER_CODE,S.PRO_CD
            ) T5 -- 상각테이블
            ON T5.MANAGER_CODE = COALESCE(T2.MANAGER_CODE,T1.MANAGER_CODE,T3.MANAGER_CODE,T4.MANAGER_CODE) AND T5.PRO_CD =  COALESCE(T2.PRO_CD,T1.PRO_CD,T3.PRO_CD,T4.PRO_CD)
            FULL OUTER JOIN 
            (
                SELECT 
                    MANAGER_CODE, PRO_CD ,
                    LOAN_01_M_CNT, LOAN_01_M_MONEY,
                    LOAN_02_M_CNT, LOAN_02_M_MONEY,
                    LOAN_04_M_CNT, LOAN_04_M_MONEY,
                    LOAN_TOTAL_M_CNT, LOAN_TOTAL_M_MONEY,
                    SANGGAK_M_CNT,SANGGAK_M_MONEY,
                    LOAN_FULLPAY_M_CNT,LOAN_RETURN_ORIGIN_M_MONEY
                FROM REPORT_DAILY WHERE INFO_DATE = '".$info_date_bf1."' 
            ) T6  -- 전일자 영업일보 당월데이터들 
            ON T6.MANAGER_CODE = COALESCE(T2.MANAGER_CODE,T1.MANAGER_CODE,T3.MANAGER_CODE,T4.MANAGER_CODE,T5.MANAGER_CODE) AND T6.PRO_CD =  COALESCE(T2.PRO_CD,T1.PRO_CD,T3.PRO_CD,T4.PRO_CD,T5.PRO_CD)
            ) E -- 모두묶자~~
            GROUP BY E.MANAGER_CODE,E.PRO_MIDDLE_DIV,E.PRO_CD
            ORDER BY MANAGER_CODE,PRO_MIDDLE_DIV,PRO_CD  ";
            
            log::channel('report_daily')->info("[ TABLE ] : ".$table_name.", [ INFO_DATE ] : ".$info_date.", [ SAVE_ID ] : ".$save_id);
            LOG::INFO($sql);
            $APP = DB::SELECT(DB::RAW($sql));
            $APP = json_decode(json_encode($APP, JSON_UNESCAPED_UNICODE),TRUE);
    
            $insert_cnt = 0;
            foreach($APP AS $idx => $v)
            {
                $v['info_date'] = $info_date;
                $v['save_time'] = $save_time;
                $v['save_id']   = $save_id;
                $v['loan_01_d_exe_rate']   = @sprintf('%0.3f',(isset($v['app_01_d_cnt']) && $v['app_01_d_cnt'] > 0)?($v['loan_01_d_cnt']/$v['app_01_d_cnt']*100):0);
                $v['loan_02_d_exe_rate']   = @sprintf('%0.3f',(isset($v['app_02_d_cnt']) && $v['app_02_d_cnt'] > 0)?($v['loan_02_d_cnt']/$v['app_02_d_cnt']*100):0);
                $v['loan_04_d_exe_rate']   = @sprintf('%0.3f',(isset($v['app_04_d_cnt']) && $v['app_04_d_cnt'] > 0)?($v['loan_04_d_cnt']/$v['app_04_d_cnt']*100):0);
                $v['loan_01_m_exe_rate']   = @sprintf('%0.3f',(isset($v['app_01_m_cnt']) && $v['app_01_m_cnt'] > 0)?($v['loan_01_m_cnt']/$v['app_01_m_cnt']*100):0);
                $v['loan_02_m_exe_rate']   = @sprintf('%0.3f',(isset($v['app_02_m_cnt']) && $v['app_02_m_cnt'] > 0)?($v['loan_02_m_cnt']/$v['app_02_m_cnt']*100):0);
                $v['loan_04_m_exe_rate']   = @sprintf('%0.3f',(isset($v['app_04_m_cnt']) && $v['app_04_m_cnt'] > 0)?($v['loan_04_m_cnt']/$v['app_04_m_cnt']*100):0);
                $v['loan_01_d_avg_money']  = ($v['loan_01_d_cnt']>0)?$v['loan_01_d_money']/$v['loan_01_d_cnt']:0;
                $v['loan_02_d_avg_money']  = ($v['loan_02_d_cnt']>0)?$v['loan_02_d_money']/$v['loan_02_d_cnt']:0;
                $v['loan_04_d_avg_money']  = ($v['loan_04_d_cnt']>0)?$v['loan_04_d_money']/$v['loan_04_d_cnt']:0;
                $v['loan_01_m_avg_money']  = ($v['loan_01_m_money']>0)?$v['loan_01_m_money']/$v['loan_01_m_cnt']:0;
                $v['loan_02_m_avg_money']  = ($v['loan_02_m_money']>0)?$v['loan_02_m_money']/$v['loan_02_m_cnt']:0;
                $v['loan_04_m_avg_money']  = ($v['loan_04_m_money']>0)?$v['loan_04_m_money']/$v['loan_04_m_cnt']:0;
                
                $result = DB::dataProcess("INS", 'REPORT_DAILY', $v);
                if($result != "Y")
                {
                    log::channel('report_daily')->info("새 데이터 입력 에러 ".print_r($v,true));
                    DB::rollback();
                    break;
                }
                $insert_cnt++;
            }
    
            log::channel('report_daily')->info("새 데이터 ".$insert_cnt."건 INSERT");
            log::channel('report_daily')->info("==================================================================================");
            
            DB::commit();
        }
        

        // 배치 종료 기록
        if($batchLogNo>0)
        {
            $note = '';
            BatchController::setBatchLog($this->argument('batchNo'), $batchLogNo, $note, $stime);
        }
    }

    // 배치로그 시작
    public function startBatchLog($stime)
    {
        $batchNo = $this->argument('batchNo');
        $batchLogNo =  0;
        if(!empty($batchNo))
        {
            $batchLogNo = BatchController::setBatchLog($batchNo, 0, '', $stime);
        }

        return $batchLogNo;
    }
}
