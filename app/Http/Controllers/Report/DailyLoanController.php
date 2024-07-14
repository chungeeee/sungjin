<?php

namespace App\Http\Controllers\Report;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use ExcelFunc;
use PHPUnit\Util\Json;
use Excel;
use App\Chung\ExcelCustomExport;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Auth;

class DailyLoanController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

    }


    /**
     * 연체현황표 - 요약
     *
     * @param  Void
     * @return json
     */
	public function dailyLoan(Request $request)
    {
        // log::info($request);
        $arr =  $request->all();
        $arr['listName'] = "dailyloan";
        $array_branch  = Func::getBranch();
        return view('report.dailyLoan')->with("result",$arr)->with("array_branch",$array_branch);
    }

    /**
     * 연체현황표 - 요약
     *
     * @param  Void
     * @return json
     */
	public function dailyLoanList(Request $request)
    {
        $info_date     = str_replace("-","",$request->info_date);
        $info_date_bf1d = date("Ymd" ,strtotime($info_date." -1 days"));
        $info_date_bf1m = date("Ymt" ,strtotime(substr($info_date, 0, 6)."01"." -1 months"));
        $where = !empty($request->manager_code)?" AND MANAGER_CODE = '".$request->manager_code."'":"";

        $sql =  "
        SELECT * FROM 
        (
            SELECT 
                CASE
                    WHEN (COALESCE(COALESCE(REPORT.PRO_CD,BF_REPORT.PRO_CD),COALESCE(REPORT.PRO_MIDDLE_DIV,BF_REPORT.PRO_MIDDLE_DIV),COALESCE(REPORT.MANAGER_CODE,BF_REPORT.MANAGER_CODE),'') = '' ) THEN '총합계'
                    WHEN (COALESCE(COALESCE(REPORT.MANAGER_CODE,BF_REPORT.MANAGER_CODE),'') = '' ) THEN'상품별 합계'
                ELSE COALESCE(REPORT.MANAGER_CODE,BF_REPORT.MANAGER_CODE) 
                END MANAGER_CODE,
                CASE
                WHEN COALESCE(COALESCE(REPORT.PRO_CD,BF_REPORT.PRO_CD),COALESCE(REPORT.PRO_MIDDLE_DIV,BF_REPORT.PRO_MIDDLE_DIV),'') = ''  AND 
                    COALESCE(COALESCE(REPORT.PRO_CD,BF_REPORT.PRO_CD),COALESCE(REPORT.PRO_MIDDLE_DIV,BF_REPORT.PRO_MIDDLE_DIV),COALESCE(REPORT.MANAGER_CODE,BF_REPORT.MANAGER_CODE),'') != ''  THEN '지점별 합계'
                ELSE COALESCE(REPORT.PRO_MIDDLE_DIV,BF_REPORT.PRO_MIDDLE_DIV) 
                END PRO_MIDDLE_DIV,
                COALESCE(REPORT.PRO_CD,BF_REPORT.PRO_CD)  AS PRO_CD,

                SUM(APP_01_D_CNT) AS APP_01_D_CNT,
                SUM(APP_01_M_CNT) AS APP_01_M_CNT,
                SUM(APP_REAPP_D_CNT) AS APP_REAPP_D_CNT,
                SUM(APP_REAPP_M_CNT) AS APP_REAPP_M_CNT,
                SUM(APP_02_D_CNT) AS APP_02_D_CNT,
                SUM(APP_02_M_CNT) AS APP_02_M_CNT,
                SUM(APP_04_D_CNT) AS APP_04_D_CNT,
                SUM(APP_04_M_CNT) AS APP_04_M_CNT,
                SUM(APP_REAPP_TOTAL_D_CNT) AS APP_REAPP_TOTAL_D_CNT,
                SUM(APP_REAPP_TOTAL_M_CNT) AS APP_REAPP_TOTAL_M_CNT,
                SUM(APP_TOTAL_D_CNT) AS APP_TOTAL_D_CNT,
                SUM(APP_TOTAL_M_CNT) AS APP_TOTAL_M_CNT,
                SUM(LOAN_01_D_CNT) AS LOAN_01_D_CNT,
                SUM(LOAN_01_D_MONEY) AS LOAN_01_D_MONEY,
                SUM(LOAN_01_D_EXE_RATE) AS LOAN_01_D_EXE_RATE,
                SUM(LOAN_01_D_AVG_MONEY) AS LOAN_01_D_AVG_MONEY,
                SUM(LOAN_01_M_CNT) AS LOAN_01_M_CNT,
                SUM(LOAN_01_M_MONEY) AS LOAN_01_M_MONEY,
                SUM(LOAN_01_M_EXE_RATE) AS LOAN_01_M_EXE_RATE,
                SUM(LOAN_01_M_AVG_MONEY) AS LOAN_01_M_AVG_MONEY,
                SUM(LOAN_02_D_CNT) AS LOAN_02_D_CNT,
                SUM(LOAN_02_D_MONEY) AS LOAN_02_D_MONEY,
                SUM(LOAN_02_D_EXE_RATE) AS LOAN_02_D_EXE_RATE,
                SUM(LOAN_02_M_CNT) AS LOAN_02_M_CNT,
                SUM(LOAN_02_M_MONEY) AS LOAN_02_M_MONEY,
                SUM(LOAN_02_M_EXE_RATE) AS LOAN_02_M_EXE_RATE,
                SUM(LOAN_04_D_CNT) AS LOAN_04_D_CNT,
                SUM(LOAN_04_D_MONEY) AS LOAN_04_D_MONEY,
                SUM(LOAN_04_M_CNT) AS LOAN_04_M_CNT,
                SUM(LOAN_04_M_MONEY) AS LOAN_04_M_MONEY,
                SUM(LOAN_TOTAL_D_CNT) AS LOAN_TOTAL_D_CNT,
                SUM(LOAN_TOTAL_D_MONEY) AS LOAN_TOTAL_D_MONEY,
                SUM(LOAN_TOTAL_M_CNT) AS LOAN_TOTAL_M_CNT,
                SUM(LOAN_TOTAL_M_MONEY) AS LOAN_TOTAL_M_MONEY,
                SUM(LOAN_FULLPAY_D_CNT) AS LOAN_FULLPAY_D_CNT,
                SUM(LOAN_RETURN_ORIGIN_D_MONEY) AS LOAN_RETURN_ORIGIN_D_MONEY,
                SUM(LOAN_FULLPAY_M_CNT) AS LOAN_FULLPAY_M_CNT,
                SUM(LOAN_RETURN_ORIGIN_M_MONEY) AS LOAN_RETURN_ORIGIN_M_MONEY,
                SUM(CHANGE_MNG_D_CNT) AS CHANGE_MNG_D_CNT,
                SUM(CHANGE_MNG_D_MONEY) AS CHANGE_MNG_D_MONEY,
                SUM(CHANGE_MNG_M_CNT) AS CHANGE_MNG_M_CNT,
                SUM(CHANGE_MNG_M_MONEY) AS CHANGE_MNG_M_MONEY,
                SUM(SANGGAK_D_CNT) AS SANGGAK_D_CNT,
                SUM(SANGGAK_D_MONEY) AS SANGGAK_D_MONEY,
                SUM(SANGGAK_M_CNT) AS SANGGAK_M_CNT,
                SUM(SANGGAK_M_MONEY) AS SANGGAK_M_MONEY,
                SUM(LOAN_D_CNT) AS LOAN_D_CNT,
                SUM(LOAN_D_BALANCE) AS LOAN_D_BALANCE,
                SUM(COALESCE(LOAN_D_CNT,0))-SUM(COALESCE(BF1D_LOAN_D_CNT,0)) AS BF1D_LOAN_D_CNT,
                SUM(COALESCE(LOAN_D_BALANCE,0))-SUM(COALESCE(BF1D_LOAN_D_BALANCE,0)) AS BF1D_LOAN_D_BALANCE,
                SUM(COALESCE(LOAN_D_CNT,0))-SUM(COALESCE(BF1M_LOAN_D_CNT,0)) AS BF1M_LOAN_D_CNT,
                SUM(COALESCE(LOAN_D_BALANCE,0))-SUM(COALESCE(BF1M_LOAN_D_BALANCE,0)) AS BF1M_LOAN_D_BALANCE,
                MAX(SAVE_TIME),
        1 AS GUB
            FROM 
                (SELECT * FROM REPORT_DAILY WHERE INFO_DATE ='".$info_date."' ".$where.") REPORT
                FULL OUTER JOIN 
                (SELECT MANAGER_CODE , PRO_MIDDLE_DIV, PRO_CD,
                SUM(CASE WHEN INFO_DATE = '".$info_date_bf1d."'  THEN LOAN_D_CNT ELSE 0 END ) AS BF1D_LOAN_D_CNT,
                SUM(CASE WHEN INFO_DATE = '".$info_date_bf1d."'  THEN LOAN_D_BALANCE ELSE 0 END) AS BF1D_LOAN_D_BALANCE,
                SUM(CASE WHEN INFO_DATE = '".$info_date_bf1m."'  THEN LOAN_D_CNT ELSE 0 END ) AS BF1M_LOAN_D_CNT,
                SUM(CASE WHEN INFO_DATE = '".$info_date_bf1m."'  THEN LOAN_D_BALANCE ELSE 0 END) AS BF1M_LOAN_D_BALANCE
                FROM REPORT_DAILY WHERE (INFO_DATE = '".$info_date_bf1d."' OR INFO_DATE = '".$info_date_bf1m."') AND ( LOAN_D_CNT>0 OR LOAN_D_BALANCE>0 ) ".$where."
                GROUP BY  MANAGER_CODE,PRO_MIDDLE_DIV ,PRO_CD ) BF_REPORT
                ON BF_REPORT.MANAGER_CODE = REPORT.MANAGER_CODE AND  BF_REPORT.PRO_MIDDLE_DIV = REPORT.PRO_MIDDLE_DIV AND  BF_REPORT.PRO_CD = REPORT.PRO_CD 
            GROUP BY GROUPING SETS(  (COALESCE(REPORT.MANAGER_CODE,BF_REPORT.MANAGER_CODE),COALESCE(REPORT.PRO_MIDDLE_DIV,BF_REPORT.PRO_MIDDLE_DIV),COALESCE(REPORT.PRO_CD,BF_REPORT.PRO_CD))
            ,(COALESCE(REPORT.PRO_MIDDLE_DIV,BF_REPORT.PRO_MIDDLE_DIV),COALESCE(REPORT.PRO_CD,BF_REPORT.PRO_CD)),(COALESCE(REPORT.MANAGER_CODE,BF_REPORT.MANAGER_CODE)),())
            ORDER BY MANAGER_CODE,PRO_MIDDLE_DIV ,PRO_CD
                    ) u1
            union all 
            SELECT * FROM (
                    SELECT 
                        '상품별 요약'                       AS 상품별_요약
                        , M1.PRO_SUB_DIV                   AS 상품구분
                        , M1.PRO_MIDDLE_DIV                AS 상품명_중분류
                        , M1.APP_01_D_CNT                  AS 신규_당일
                        , M1.APP_01_M_CNT                  AS 신규_당월
                        , M1.APP_REAPP_D_CNT               AS 신규재신청_당일
                        , M1.APP_REAPP_M_CNT               AS 신규재신청_당월
                        , M1.APP_02_D_CNT                  AS 재대출_당일
                        , M1.APP_02_M_CNT                  AS 재대출_당월
                        , M1.APP_04_D_CNT                  AS 증액_당일
                        , M1.APP_04_M_CNT                  AS 증액_당월
                        , M1.APP_REAPP_TOTAL_D_CNT         AS 재신청계_당일
                        , M1.APP_REAPP_TOTAL_M_CNT         AS 재신청계_당월
                        , M1.APP_TOTAL_D_CNT               AS 신청총계_당일
                        , M1.APP_TOTAL_M_CNT               AS 신청총계_당월
                        , M1.LOAN_01_D_CNT                 AS 신규대출_당일_건수
                        , M1.LOAN_01_D_MONEY               AS 신규대출_당일_금액
                        , M1.LOAN_01_D_EXE_RATE            AS 신규대출_당일_실행율
                        , M1.LOAN_01_D_AVG_MONEY           AS 신규대출_당일_평균
                        , M1.LOAN_01_M_CNT                 AS 신규대출_당월_건수
                        , M1.LOAN_01_M_MONEY               AS 신규대출_당월_금액
                        , M1.LOAN_01_M_EXE_RATE            AS 신규대출_당월_실행율
                        , M1.LOAN_01_M_AVG_MONEY           AS 신규대출_당월_평균
                        , M1.LOAN_02_D_CNT                 AS 재대출_당일_건수
                        , M1.LOAN_02_D_MONEY               AS 재대출_당일_금액
                        , M1.LOAN_02_D_EXE_RATE            AS 재대출_당일_실행율
                        , M1.LOAN_02_M_CNT                 AS 재대출_당월_건수
                        , M1.LOAN_02_M_MONEY               AS 재대출_당월_금액
                        , M1.LOAN_02_M_EXE_RATE            AS 재대출_당월_실행율
                        , M1.LOAN_04_D_CNT                 AS 증액_당일_건수
                        , M1.LOAN_04_D_MONEY               AS 증액_당일_금액
                        , M1.LOAN_04_M_CNT                 AS 증액_당월_건수
                        , M1.LOAN_04_M_MONEY               AS 증액_당월_금액
                        , M1.LOAN_TOTAL_D_CNT              AS 합계_당일_건수
                        , M1.LOAN_TOTAL_D_MONEY            AS 합계_당일_금액
                        , M1.LOAN_TOTAL_M_CNT              AS 합계_당월_건수
                        , M1.LOAN_TOTAL_M_MONEY            AS 합계_당월_금액
                        , M1.LOAN_FULLPAY_D_CNT            AS 대출상환_당일_건수
                        , M1.LOAN_RETURN_ORIGIN_D_MONEY    AS 대출상환_당일_금액  
                        , M1.LOAN_FULLPAY_M_CNT            AS 대출상환_당월_건수 
                        , M1.LOAN_RETURN_ORIGIN_M_MONEY    AS 대출상환_당월_금액
                        , M1.CHANGE_MNG_D_CNT              AS 이수관_당일_건수
                        , M1.CHANGE_MNG_D_MONEY            AS 이수관_당일_금액
                        , M1.CHANGE_MNG_M_CNT              AS 이수관_당월_건수
                        , M1.CHANGE_MNG_M_MONEY            AS 이수관_당월_금액
                        , M1.SANGGAK_D_CNT                 AS 대손상각_당일_건수
                        , M1.SANGGAK_D_MONEY               AS 대손상각_당일_금액
                        , M1.SANGGAK_M_CNT                 AS 대손상각_당월_건수
                        , M1.SANGGAK_M_MONEY               AS 대손상각_당월_금액
                        , M1.LOAN_D_CNT                    AS 당일_대출_좌수
                        , M1.LOAN_D_BALANCE                AS 당일_대출_잔액
                        , M1.LOAN_D_CNT - M2.LOAN_D_CNT            AS 증감_전일대비_좌수
                        , M1.LOAN_D_BALANCE - M2.LOAN_D_BALANCE    AS 증감_전일대비_금액
                        , M1.LOAN_D_CNT - M3.LOAN_D_CNT            AS 증감_전월대비_좌수
                        , M1.LOAN_D_BALANCE - M3.LOAN_D_BALANCE    AS 증감_전월대비_금액
                        , M1.SAVE_TIME    
        , 2 AS GUB                         
                    FROM (SELECT CASE WHEN T2.PRO_SUB_DIV IS NULL THEN '총합계' ELSE T2.PRO_SUB_DIV END AS PRO_SUB_DIV
                                , CASE WHEN T2.PRO_SUB_DIV IS NULL AND T2.PRO_MIDDLE_DIV IS NULL THEN '총합계' 
                                        WHEN T2.PRO_SUB_DIV IS NOT NULL AND T2.PRO_MIDDLE_DIV IS NULL THEN T2.PRO_SUB_DIV||'-소계'
                                ELSE T2.PRO_MIDDLE_DIV END                      AS PRO_MIDDLE_DIV
                    --             , MAX(T1.INFO_DATE)                    AS INFODATE   
                                , SUM(T1.APP_01_D_CNT)                 AS APP_01_D_CNT
                                , SUM(T1.APP_01_M_CNT)                 AS APP_01_M_CNT
                                , SUM(T1.APP_REAPP_D_CNT)              AS APP_REAPP_D_CNT
                                , SUM(T1.APP_REAPP_M_CNT)              AS APP_REAPP_M_CNT
                                , SUM(T1.APP_02_D_CNT)                 AS APP_02_D_CNT
                                , SUM(T1.APP_02_M_CNT)                 AS APP_02_M_CNT
                                , SUM(T1.APP_04_D_CNT)                 AS APP_04_D_CNT
                                , SUM(T1.APP_04_M_CNT)                 AS APP_04_M_CNT
                                , SUM(T1.APP_REAPP_TOTAL_D_CNT)        AS APP_REAPP_TOTAL_D_CNT
                                , SUM(T1.APP_REAPP_TOTAL_M_CNT)        AS APP_REAPP_TOTAL_M_CNT
                                , SUM(T1.APP_TOTAL_D_CNT)              AS APP_TOTAL_D_CNT
                                , SUM(T1.APP_TOTAL_M_CNT)              AS APP_TOTAL_M_CNT
                                , SUM(T1.LOAN_01_D_CNT)                AS LOAN_01_D_CNT
                                , SUM(T1.LOAN_01_D_MONEY)              AS LOAN_01_D_MONEY
                                , ROUND(CASE WHEN (SUM(T1.APP_01_D_CNT)=0 OR SUM(T1.LOAN_01_D_CNT)=0) THEN 0
                                        ELSE (SUM(T1.LOAN_01_D_CNT)/SUM(T1.APP_01_D_CNT)*100)::DECIMAL END,2)      AS LOAN_01_D_EXE_RATE 
                                , CASE WHEN SUM(T1.LOAN_01_D_CNT)=0 OR SUM(T1.LOAN_01_D_MONEY)=0 THEN 0
                                        ELSE SUM(T1.LOAN_01_D_MONEY)/SUM(T1.LOAN_01_D_CNT) END                                     AS LOAN_01_D_AVG_MONEY
                                , SUM(T1.LOAN_01_M_CNT)                AS LOAN_01_M_CNT
                                , SUM(T1.LOAN_01_M_MONEY)              AS LOAN_01_M_MONEY
                                , ROUND(CASE WHEN (SUM(T1.APP_01_M_CNT)=0 OR SUM(T1.LOAN_01_M_CNT)=0) THEN 0
                                        ELSE (SUM(T1.LOAN_01_M_CNT)/SUM(T1.APP_01_M_CNT)*100)::DECIMAL END,2)      AS LOAN_01_M_EXE_RATE 
                                , CASE WHEN SUM(T1.LOAN_01_M_CNT)=0 OR SUM(T1.LOAN_01_M_MONEY)=0 THEN 0
                                        ELSE SUM(T1.LOAN_01_M_MONEY)/SUM(T1.LOAN_01_M_CNT) END                                     AS LOAN_01_M_AVG_MONEY
                                , SUM(T1.LOAN_02_D_CNT)                AS LOAN_02_D_CNT
                                , SUM(T1.LOAN_02_D_MONEY)              AS LOAN_02_D_MONEY
                                , ROUND(CASE WHEN (SUM(T1.APP_02_D_CNT)=0 OR SUM(T1.LOAN_02_D_CNT)=0) THEN 0
                                        ELSE (SUM(T1.LOAN_02_D_CNT)/SUM(T1.APP_02_D_CNT)*100)::DECIMAL END,2)      AS LOAN_02_D_EXE_RATE 
                                , SUM(T1.LOAN_02_M_CNT)                AS LOAN_02_M_CNT
                                , SUM(T1.LOAN_02_M_MONEY)              AS LOAN_02_M_MONEY
                                , ROUND(CASE WHEN (SUM(T1.APP_02_M_CNT)=0 OR SUM(T1.LOAN_02_M_CNT)=0) THEN 0
                                        ELSE (SUM(T1.LOAN_02_M_CNT)/SUM(T1.APP_02_M_CNT)*100)::DECIMAL END,2)      AS LOAN_02_M_EXE_RATE 
                                , SUM(T1.LOAN_04_D_CNT)                AS LOAN_04_D_CNT
                                , SUM(T1.LOAN_04_D_MONEY)              AS LOAN_04_D_MONEY
                                , SUM(T1.LOAN_04_M_CNT)                AS LOAN_04_M_CNT
                                , SUM(T1.LOAN_04_M_MONEY)              AS LOAN_04_M_MONEY
                                , SUM(T1.LOAN_TOTAL_D_CNT)             AS LOAN_TOTAL_D_CNT
                                , SUM(T1.LOAN_TOTAL_D_MONEY)           AS LOAN_TOTAL_D_MONEY
                                , SUM(T1.LOAN_TOTAL_M_CNT)             AS LOAN_TOTAL_M_CNT
                                , SUM(T1.LOAN_TOTAL_M_MONEY)           AS LOAN_TOTAL_M_MONEY
                                , SUM(T1.LOAN_FULLPAY_D_CNT)           AS LOAN_FULLPAY_D_CNT
                                , SUM(T1.LOAN_RETURN_ORIGIN_D_MONEY)   AS LOAN_RETURN_ORIGIN_D_MONEY    
                                , SUM(T1.LOAN_FULLPAY_M_CNT)           AS LOAN_FULLPAY_M_CNT
                                , SUM(T1.LOAN_RETURN_ORIGIN_M_MONEY)   AS LOAN_RETURN_ORIGIN_M_MONEY
                                , SUM(T1.CHANGE_MNG_D_CNT)             AS CHANGE_MNG_D_CNT
                                , SUM(T1.CHANGE_MNG_D_MONEY)           AS CHANGE_MNG_D_MONEY
                                , SUM(T1.CHANGE_MNG_M_CNT)             AS CHANGE_MNG_M_CNT
                                , SUM(T1.CHANGE_MNG_M_MONEY)           AS CHANGE_MNG_M_MONEY
                                , SUM(T1.SANGGAK_D_CNT)                AS SANGGAK_D_CNT
                                , SUM(T1.SANGGAK_D_MONEY)              AS SANGGAK_D_MONEY
                                , SUM(T1.SANGGAK_M_CNT)                AS SANGGAK_M_CNT
                                , SUM(T1.SANGGAK_M_MONEY)              AS SANGGAK_M_MONEY
                                , SUM(T1.LOAN_D_CNT)                   AS LOAN_D_CNT
                                , SUM(T1.LOAN_D_BALANCE)               AS LOAN_D_BALANCE
                                , MAX(T1.SAVE_TIME)                    AS SAVE_TIME
                            FROM REPORT_DAILY T1
                            LEFT OUTER JOIN PRODUCT_MANAGE T2 ON T1.PRO_CD=T2.PRO_CD
                            WHERE T1.INFO_DATE='".$info_date."'          -- 당일기준일자
                            GROUP BY ROLLUP(T2.PRO_SUB_DIV, T2.PRO_MIDDLE_DIV)
                            ) M1
                    LEFT OUTER JOIN (SELECT CASE WHEN T2.PRO_SUB_DIV IS NULL THEN '총합계' ELSE T2.PRO_SUB_DIV END AS PRO_SUB_DIV
                                            , CASE WHEN T2.PRO_SUB_DIV IS NULL AND T2.PRO_MIDDLE_DIV IS NULL THEN '총합계' 
                                                WHEN T2.PRO_SUB_DIV IS NOT NULL AND T2.PRO_MIDDLE_DIV IS NULL THEN T2.PRO_SUB_DIV||'-소계'
                                                ELSE T2.PRO_MIDDLE_DIV END      AS PRO_MIDDLE_DIV
                                            , SUM(T1.LOAN_D_CNT)                   AS LOAN_D_CNT
                                            , SUM(T1.LOAN_D_BALANCE)               AS LOAN_D_BALANCE
                                        FROM REPORT_DAILY T1
                                        LEFT OUTER JOIN PRODUCT_MANAGE T2 ON T1.PRO_CD=T2.PRO_CD
                                        WHERE T1.INFO_DATE='".$info_date_bf1d."'  -- 전일기준일자
                                        GROUP BY ROLLUP(T2.PRO_SUB_DIV, T2.PRO_MIDDLE_DIV)
                                    ) M2 ON M1.PRO_SUB_DIV=M2.PRO_SUB_DIV AND M1.PRO_MIDDLE_DIV=M2.PRO_MIDDLE_DIV
                    LEFT OUTER JOIN (SELECT CASE WHEN T2.PRO_SUB_DIV IS NULL THEN '총합계' ELSE T2.PRO_SUB_DIV END AS PRO_SUB_DIV
                                            , CASE WHEN T2.PRO_SUB_DIV IS NULL AND T2.PRO_MIDDLE_DIV IS NULL THEN '총합계' 
                                                WHEN T2.PRO_SUB_DIV IS NOT NULL AND T2.PRO_MIDDLE_DIV IS NULL THEN T2.PRO_SUB_DIV||'-소계'
                                                ELSE T2.PRO_MIDDLE_DIV END      AS PRO_MIDDLE_DIV
                                            , SUM(T1.LOAN_D_CNT)                   AS LOAN_D_CNT
                                            , SUM(T1.LOAN_D_BALANCE)               AS LOAN_D_BALANCE
                                        FROM REPORT_DAILY T1
                                        LEFT OUTER JOIN PRODUCT_MANAGE T2 ON T1.PRO_CD=T2.PRO_CD
                                        WHERE T1.INFO_DATE='".$info_date_bf1m."'   -- 전월말기준일자
                                        GROUP BY ROLLUP(T2.PRO_SUB_DIV, T2.PRO_MIDDLE_DIV)
                                    ) M3 ON M1.PRO_SUB_DIV=M3.PRO_SUB_DIV AND M1.PRO_MIDDLE_DIV=M3.PRO_MIDDLE_DIV
                    ORDER BY 2 ASC, 3 DESC  
        ) u2 ORDER BY GUB ASC
        ";

        $DELAY     = DB::select($sql);

        $array_pro_cd         = Func::getConfigArr('pro_cd');
        $array_pro_middle_div = Func::getConfigArr('pro_middle_div');
        $array_pro_report_div = Func::getConfigArr('pro_report_div');
        $array_pro_mid_div    = Func::getConfigArr('pro_report_mid_div');
        $array_branch         = Func::getBranch();
        $array_branch["N"] = "미지정";
        $setting = Array();



        foreach($DELAY AS $idx => $obj)
        {
            $arr = (Array)$obj;

            $arr['manager_code']    = Func::getArrayName($array_branch,$obj->manager_code);
            
            if($obj->manager_code =="상품별 요약")
            {
                $arr['pro_middle_div']    = Func::getArrayName($array_pro_report_div,$obj->pro_middle_div);
                $pro_arr = explode("-",$obj->pro_cd);
                $arr['pro_cd']            = sizeof($pro_arr)>1?Func::getArrayName($array_pro_report_div,$obj->pro_middle_div)." 소계":Func::getArrayName($array_pro_mid_div,$pro_arr[0]);


            }
            else
            {
                $arr['pro_middle_div']  = Func::getArrayName($array_pro_middle_div,$obj->pro_middle_div);
                $arr['pro_cd']          = Func::getArrayName($array_pro_cd,$obj->pro_cd);
            }


            foreach($arr as $col =>$v)
            {
                if(str_ends_with($col,"_rate")== true)
                {
                    // DB에 값이 있지만 합계 ROW의 실행율 산출을 위해 추가
                    $loan_cnt_str = 'loan_'.substr($col,5,4).'_cnt';
                    $app_cnt_str = 'app_'.substr($col,5,4).'_cnt';
                    $arr['loan_'.substr($col,5,4).'_exe_rate']   = ($obj->$loan_cnt_str>0&&$obj->$app_cnt_str>0)?@sprintf('%0.2f',($obj->$loan_cnt_str/$obj->$app_cnt_str*100)):0;
                }

                if(str_ends_with($col,"_cnt")== true || str_ends_with($col,"_balance") == true || str_ends_with($col,"_money") == true)
                {
                    $arr[$col] = number_format($v);
                }

                if(str_ends_with($col,"_avg_money")== true)
                {
                    // DB에 값이 있지만 합계 ROW의 평균산출을 위해 추가
                    $loan_money_str = 'loan_'.substr($col,5,4).'_money';
                    $loan_cnt_str = 'loan_'.substr($col,5,4).'_cnt';
                    $arr['loan_'.substr($col,5,4).'_avg_money'] = ($obj->$loan_money_str>0&&$obj->$loan_cnt_str>0)?number_format(round($obj->$loan_money_str/$obj->$loan_cnt_str)):0;
                }
            }

            $setting[] = array_values($arr);
        }

        $json_string = '{
            "data":'.json_encode($setting, JSON_UNESCAPED_UNICODE).',
            "sql":'.json_encode($sql, JSON_UNESCAPED_UNICODE).'
            }';
    
        return $json_string;
    }

    /**
     * 지역별 채권 현황 엑셀 데이터 셋팅 
     *
     * @param  Request $request
     * @return Array ['header', 'excel_data''title', 'style']
     */
    public function dailyLoanExcel(Request $request)
    {
        if( !Func::funcCheckPermit("H022") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }
        
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $info_date      = str_replace("-","",$request->info_date);
        $rs             = $this->dailyLoanList($request);
        $data_v         = json_decode($rs,true);
        $file_name      = "영업일보_".$info_date."_".date("YmdHis").'.xlsx';
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;
        
        // 헤더
		$excel_header = Array(
			1=>Array("지점명","상품명","","신청건수","","","","","","","","","","","","신규대출(A)","","","","","","","","재대출(B)","","","","","","증액대출(C)","","","","합계(E=A+B+C)","","","","대출상환","","","","이수관","","","","대손상각","","","","당일대출잔액","","증감","","","",""),
            2=>Array("","중분류","소분류","신규","","신규재신청","","재대출","","증액","","재신청계","재신청계","신청총계","","당일","","","","당월","","","","당일","","","당월","","","당일","","당월","","당일","","당월","","당일","","당월","","당일","","당월","","당일","","당월","","좌수","금액","전일대비","","전월말대비",""),
            3=>Array("","","","당일","당월","당일","당월","당일","당월","당일","당월","당일","당월","당일","당월","당일","금액","실행율","평균","당월","금액","실행율","평균","건수","금액","실행율","건수","금액","실행율","건수","금액","건수","금액","건수","금액","건수","금액","건수","금액","건수","금액","건수","금액","건수","금액","건수","금액","건수","금액","","","좌수","금액","좌수","금액",),
        );  
        $excel_data = [];

        $last_header_col_idx = "";
        // 헤더 지정 행 COLSPAN
        foreach($excel_header as $header_rownum => $header_row)
        {
            foreach($header_row as $idx => $header_name)
            {
                $idx = $idx+1;
                // 둘째행까지만하자~~
                if(($header_rownum==1 ||($header_rownum==2 && $idx>10)  )&& $header_name == "" && $idx<sizeof($header_row) )
                {
                    $last_header_col_idx = Func::nvl($last_header_col_idx,Coordinate::stringFromColumnIndex($idx-1));
                    $curr_header_col_idx = Coordinate::stringFromColumnIndex($idx);
                }
                else
                {
                    if(isset($last_header_col_idx) && isset($curr_header_col_idx))
                    {
                        $style['merge'][] = $last_header_col_idx.$header_rownum.":".$curr_header_col_idx.$header_rownum;
                    }
                    unset($last_header_col_idx,$curr_header_col_idx);
                }
            }
        }
        $first_idx0 = $first_idx1 = $first_idx2 = 4; // 엑셀상 실 데이터 시작 행 번호 (헤더3줄 이후)
        $cnt0 = $cnt1= $cnt2    = 0;
        $last_row_idx = sizeof($data_v['data'])+3;   // 헤더 세줄 추가해줌

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query = $data_v['sql'];
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no)){
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, null, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
        } else {
            $excel_no       = ExcelFunc::setExcelDownLog("INS", $request->excelDownCd,$file_name, $query, null, $request->etc,null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
        }
        
        foreach($data_v['data'] as $idx => $v)
        {
            $j = sizeof($v);
            unset($v[$j-1],$v[$j-2]); // sql상 마지막 두열은 출력할필요 없으니 빼자

            // 첫밴째 열 지점명($v[0]),중분류($v[1]),소분류($v[2])에 대해 다음 row의 값이 중복될경우 rowspan 처리
            $old_v0[$idx] = $v[0];
            $old_v1[$idx] = $v[1];
            $old_v2[$idx] = $v[2];

            if($idx>0)
            {
                for($i=0;$i<3;$i++)
                {
                    if(!empty(${"old_v".$i}[$idx-1]) && ${"old_v".$i}[$idx-1] == $v[$i] )
                    {
                        ${"cnt".$i}++;
                        ${"first_idx".$i} = Func::nvl(${"first_idx".$i},$idx+3);
                        ${"last_idx".$i}  = ${"first_idx".$i}+ ${"cnt".$i};

                        if(${"last_idx".$i} == $last_row_idx) // 마지막열까지 rowspan 확인해주자
                        {
                            $col_idx = Coordinate::stringFromColumnIndex($i+1);
                            $style['merge'][] = $col_idx.${"first_idx".$i}.":".$col_idx.${"last_idx".$i};
                        }
                    }
                    else
                    {
                        $col_idx = Coordinate::stringFromColumnIndex($i+1);
                        // rowspan 처리
                        if(isset(${"first_idx".$i}) && isset(${"last_idx".$i}))
                        {
                            $style['merge'][] = $col_idx.${"first_idx".$i}.":".$col_idx.${"last_idx".$i};
                        }
                        unset(${"first_idx".$i},${"last_idx".$i});
                        ${"cnt".$i} = 0;
                    }
                }
            }
            else
            {
                // 각 열마다 오른쪽 border 추가
                foreach($v as $v_idx => $vv)
                {
                    $col_idx_r = Coordinate::stringFromColumnIndex($v_idx+1);
                    $style['border'][$col_idx_r."1:".$col_idx_r.$last_row_idx] = 'right';
                }
            }
            // 각 행마다 아래쪽 border 추가
            if(Auth::id()!='jasmine')
            {
                $style['border'][$idx+4]='bottom';
            }
            $excel_data[] = $v;
        }

        // 헤더 ROWSPAN 수기처리
        $style['merge'][] = "A1:A3";
        $style['merge'][] = "B2:B3";
        $style['merge'][] = "C2:C3";
        $style['merge'][] = "AX2:AX3";
        $style['merge'][] = "AY2:AY3";

        // 스타일
        $style['custom'] = [
            // // 헤더
            'A1:'.$col_idx_r.'1'=> [
                'borders' => [
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                ]
            ],
            // 헤더
            'A2:'.$col_idx_r.'2'=> [
                'borders' => [
                    'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                ]
            ],
            // 헤더전체 BORDER,스타일
            'A1:'.$col_idx_r.'3'=> [
                'font' => ['bold'=>true], 
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'ebebec']],
                'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                                'top'=>['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                                'bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                                'right' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                            ],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,
                    // 'wrapText' => true,
                ],
            ],
            // 고정열부분
            'A1:C'.$last_row_idx  => [
                'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'ebebec']],
                'alignment' => [
                    'vertical' => Alignment::VERTICAL_CENTER,

                    'wrapText' => true,
                ],
                'borders' => ['bottom' => ['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
                'right'=>['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN]],
            ],
            // 데이터부분 오른쪽정렬
            'D4:'.$col_idx_r.$last_row_idx => [
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_RIGHT,
                ],
            ],
        ];

        // 엑셀 저장 
        Excel::store(new ExcelCustomExport($excel_header,$excel_data,'영업일보',$style), './excel/'.$file_name);
        // 엑셀 익스포트
        // ExcelFunc::fastexcelExport($excel_data,$excel_header,$file_name);

        // 파일 저장 여부 확인
        $exists = Storage::disk('excel')->exists($file_name);

        if( isset($exists))
        {
            $array_result['etc']             = $etc;
            $array_result['result']          = 'Y';
            $array_result['filename']        = $file_name;
            $array_result['excel_no']        = $excel_no;
            $array_result['record_count']    = $last_row_idx;
            $array_result['down_filename']   = $down_filename;
            $array_result['excel_down_div']  = $excel_down_div;
            
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $last_row_idx, null, $excel_no, $down_filename, $excel_down_div); 
        }
        else
        {
            $array_result['result']    = 'N';
            $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }

        return $array_result;
    }

}