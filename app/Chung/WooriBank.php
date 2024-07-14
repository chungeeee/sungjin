<?php

namespace App\Chung;

use Func;
use DB;
use ErrorException;
use Log;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\File;
use Illuminate\Support\Facades\Storage;


class WooriBank
{
    /**                         
	 * 가상계좌 셋팅 
	 * 
	 * @param String $cust_info_no,$loan_info_no
	 * @return String $receive_msg
	 */
    function setVirtualAccount($cust_info_no, $loan_info_no, $name)
    {
        $vir = DB::TABLE('VIR_ACCT')->SELECT("*")->WHERE("SAVE_STATUS","Y")->WHERE("CUST_INFO_NO",$cust_info_no)->ORDERBY("NO")->FIRST(); 
        Log::debug((array)$vir);
        // 해당 고객번호로 등록된 가상계좌가 있음.
        if(isset($vir))
        {
            return 'duple';
        }
        else 
        {
            $v = DB::TABLE("VIR_ACCT")->SELECT("*")->WHERERAW("COALESCE(CUST_INFO_NO,0)=0 ")->WHERE("SAVE_STATUS","Y")->ORDERBY("NO")->FIRST();

            // VIR_ACCT 테이블에 관련내용 업데이트
            if(!isset($v))
            {
                return 'N';
            }
            // 가상계좌 등록
            else
            {
                // 가상계좌 업데이트
                $UP['loan_info_no'] = $loan_info_no;
                $UP['cust_info_no'] = $cust_info_no;
                $UP['save_time']    = date("YmdHis"); 
                $UP['reg_date']     = date("Ymd");
                $UP['save_id']      = Auth::id();
                $upd                = DB::dataProcess("UPD", 'vir_acct', $UP, ["no" => $v->no]);

                // 세팅정보 넣기 VIR_ACCT_SET
                $_IN['cust_info_no']    = $cust_info_no;
                $_IN['loan_info_no']    = $loan_info_no;
                $_IN['bank_ssn']        = Func::chungDecOne($v->vir_acct_ssn);
                $_IN['name']            = $name;
                $_IN['adddate']         = date("Ymd");
                $_IN['addtime']         = date("His");
                $_IN['hb']              = 'HB';
                $_IN['company_id']      = 'CO_KICO';                // env로 빼자.
                $_IN['bukrs']           = '1';
                $_IN['tr_key']          = '';
                $_IN['trcode']          = 'WBV01';
                $_IN['header1']         = '';
                $_IN['header2']         = '';
                $_IN['money_set']       = '0';
                $_IN['s_date']          = date("Ymd");
                $_IN['e_date']          = '99991231';
                $_IN['s_time']          = '000001';
                $_IN['e_time']          = '235959';
                $_IN['type_code']       = 'D';
                $_IN['money_code']      = '2';
                $_IN['result_code']     = '';
                $_IN['result_memo']     = '';
                $_IN['worker_id']       = Auth::id();
                $_IN['save_time']       = date("YmdHis");
                $_IN['save_status']     = 'Y';
                $no = DB::table('vir_acct_set')->insertGetId($_IN, 'no');
                Log::debug("INSERT NO :".$no);
                
                if(empty($no))
                {
                    return 'N';
                }
                else 
                {
                    // WBV01 DT테이블 입력 (실시간)
                    $_DT['COMPANY_ID']          = $_IN['company_id'];     // 업체코드(은행부여 업체코드)
                    $_DT['REQUEST_DATE']        = $_IN['adddate'];        // 요청일자(현재일)
                    $_DT['TR_CODE']             = $_IN['trcode'];         // 거래코드(WBV01)
                    $_DT['TR_KEY']              = sprintf("%05d", $no);   // 거래키(일별/거래별 UNIQUE 키)
                    $_DT['SEQ']                 = '1';                    // 거래순번(반복부내 거래순번) 현재 1로 고정
                    $_DT['BUKRS']               = $_IN['bukrs'];          // 회사코드(고객지정 여분 키 필드
                    $_DT['REQ_RES']             = 'R';                    // 요청/응답구분 요청(R) 응답(S)
                    $_DT['STATUS']              = '11';                   // 상태(최초'11')
                    $_DT['STATUS_DESC']         = '처리대기중';           // 상태메세지(최초'처리대기중')
                    $_DT['FIELD1']              = $_IN['bank_ssn'];       // 가상계좌번호
                    $_DT['FIELD2']              = '09122202';             // 업체고객번호(업체에서 부여하는 구분 Key)
                    $_DT['FIELD3']              = '0';                    // 납부금액
                    $_DT['FIELD4']              = $_IN['s_date'];         // 납부시작일자 (ex: 20060601)
                    $_DT['FIELD5']              = $_IN['e_date'];         // 납부종료일자 (ex: 20061231)
                    $_DT['FIELD6']              = $_IN['s_time'];         // 납부시작시간 (ex: 000001)
                    $_DT['FIELD7']              = $_IN['e_time'];         // 납부종료시간 (ex: 235959)
                    $_DT['FIELD8']              = 'D';                    // 등록/해지 구분코드 (D-등록, C-해지)
                    $_DT['FIELD9']              = '2';                    // 금액체크 구분 코드 기존에 2번 사용
                    $_DT['FIELD10']             = $_IN['name'];           // 고객이름

                    // 운영만 등록
                    if(config('app.env')=='prod')
                    {
                        DB::connection('mybank')->table('WCMS_GW_TR_DT')->insert($_DT);
                    }
                    
                    // WBV01 헤더테이블 입력 (실시간)
                    $_HDR['COMPANY_ID']         = $_IN['company_id'];     // 업체코드(은행부여 업체코드)
                    $_HDR['REQUEST_DATE']       = $_IN['adddate'];        // 요청일자(현재일)
                    $_HDR['TR_CODE']            = $_IN['trcode'];         // 거래코드(WBV01)
                    $_HDR['TR_KEY']             = $_DT['TR_KEY'];         // 거래키(일별/거래별 UNIQUE 키)
                    $_HDR['BUKRS']              = $_IN['bukrs'];          // 회사코드(고객지정 여분 키 필드)
                    $_HDR['TARGET']             = 'WBANK';                // 전송대상(WBANK)
                    $_HDR['TR_FLAG']            = 'B';                    // 거래구분('B' 배치)
                    $_HDR['REQUEST_TIME']       = $_IN['addtime'];        // 요청시간(현재시간)
                    $_HDR['EXEC_DATE']          = $_IN['adddate'];        // 실행일자(실행예정일)
                    $_HDR['IBANKING_ID']        = 'KICORES';              // banking ID env로 빼자. 
                    $_HDR['IBANKING_MST_ID']    = $_HDR['IBANKING_ID'];
                    $_HDR['BANK_EXEC_DATE']     = $_IN['adddate'];        // 은행실행일자(실행일자(EXEC_DATE)와 동일일로 세팅
                    $_HDR['STATUS']             = '11';                   // 상태(최초'11')
                    $_HDR['STATUS_DESC']        = '처리대기중';           // 상태메세지(최초'처리대기중')
                    $_HDR['TOTAL_CNT']          = '1';                    // 총건수(반복부 총건수)	

                    // 운영만 등록
                    if(config('app.env')=='prod')
                    {
                        DB::connection('mybank')->table('WCMS_GW_TR_HDR')->insert($_HDR);
                    }

                    // WBV02 헤더테이블 입력 (실시간)WCMS_GW_TR_HDR
                    $_HDR['TR_CODE']            = 'WBV02';                // 거래코드(WBV02)    
                    $_HDR['FIELD1']            = $_IN['adddate'];         // 원거래일자(원거래일자)
                    $_HDR['FIELD2']            = $_DT['TR_KEY'];          // 원거래키(원거래키) -- 원거래키가 뭘 말하는지 파악할것                    
                    
                    // 운영만 등록
                    if(config('app.env')=='prod')
                    {
                        DB::connection('mybank')->table('WCMS_GW_TR_HDR')->insert($_HDR);
                    }

                    return 'Y';
                }
            }

            
        }
    }

    /**
	 * 가상계좌 해지
	 * 
	 * @param String $vir_acct_no
	 * @return String $receive_msg
	 */
    function delVirtualAccount($vir_acct_no)
    {
        // 해지할 내용 필요할시 작업할 것.
        return 'Y';
    }

}