<?php
namespace App\Chung;

use DB;
use Vars;
use Log;
use Auth;
use Func;
use Loan;

class Sms
{

   /**
	* 문자파싱열 가져오기
	* @param $no = 회원번호
	* @param $msg = 변환할 메세지
    * @param $ups_erp = 대출 / 계약 구분
	* @param $arrayKeys = key : 데이터 가져올 테이블, val : 테이블번호
	* @return $msg = 변환된 메세지
    * 
    * Sms::msgParser($v->no, $msg, "ERP", null, $sms_erp_div);
	*/
	public static function msgParser($no, $msg, $ups_erp, $arrayKeys=null, $input_params=Array(), $array_config=Array(), $param)
	{
        $input_params = json_decode($input_params, true);
        
        //공통정보
        $arrayResult['common'] = Array
        (
            //'auth_id'       => Func::chungDecOne(Auth::user()->name),                //  [발송직원]
            'today_ymd'     => date("Y년 m월 d일"),
            'today_md'      => date("m월 d일"),
            'today_d'       => date("d"),
            'max_rate_date' => Vars::$curMaxRateDate,
            'max_rate'      => Vars::$curMaxRate,
            //'fax_number'    => '000-0000-0000'
        );

        $today = date("Ymd");
        if( sizeof($input_params)>0 )
        {
            foreach( $input_params as $ipk => $ipv )
            {
                $arrayResult['common'][$ipk] = $ipv;
            }

            // 기준일이 넘어오면.
            if( isset($arrayResult['common']['sms_basis_date']) )
            {
                $today = str_replace("-","",$arrayResult['common']['sms_basis_date']);

                $arrayResult['common']['visit_date'] = substr($today,4,2)."월 ".substr($today,6,2)."일";
                $arrayResult['common']['today_ymd']  = substr($today,0,4)."년 ".substr($today,4,2)."월 ".substr($today,6,2)."일";
                $arrayResult['common']['today_md']   = substr($today,4,2)."월 ".substr($today,6,2)."일";
                $arrayResult['common']['today_d']    = substr($today,6,2);

            }
        }

        // 계약기본정보
        if($ups_erp == "ERP")
        {
            $arrayParser = Vars::$arraySmsErpParser;
            if( $no > 0 )
            {
                $arrayResult['LOAN_INFO'] = Sms::getLoanInfo($no, $input_params['no'], $array_config);
                // $arrayResult['USERS'] = PaperPrint::getUsers($no, $post_cd, $loan_info_law_no);
                // $arrayResult['CUST_INFO_EXTRA'] = PaperPrint::getCustInfoExtra($no);
                // $arrayResult['BRANCH'] = PaperPrint::getBranch();
                // $arrayResult['CONF_CODE'] = PaperPrint::getUserRankCd($no);
                $arrayResult['loan_info'] = Sms::getInvList($no, $input_params['no'], $input_params);
                $arrayResult['loan_info_return_plan'] = Sms::getReturnList($input_params['no'], $input_params, $param);


                $arrayParser['[채권자]']            = "LOAN_INFO___name";                
                $arrayParser['[투자잔액백만단위]']   = "LOAN_INFO__balance_han";
                $arrayParser['[투자금리]']          = "LOAN_INFO___ratio";
                $arrayParser['[만기일전]']          = "LOAN_INFO___prev_end_date";
                $arrayParser['[만기일후]']          = "LOAN_INFO___next_end_date";
                $arrayParser['[금리전]']            = "LOAN_INFO___prev_ratio";
                $arrayParser['[금리후]']            = "LOAN_INFO___next_ratio";
                $arrayParser['[이자지급일수]']       = "LOAN_INFO_RETURN_PLAN__loan_term";
                $arrayParser['[세전]']              = "LOAN_INFO_RETURN_PLAN__plan_interest";
                $arrayParser['[세금]']              = "LOAN_INFO_RETURN_PLAN__withholding_tax";
                $arrayParser['[세후]']              = "LOAN_INFO_RETURN_PLAN__return_interest";
                $arrayParser['[투자금액]']          = "LOAN_INFO_RETURN_PLAN__plan_money_tail";
                $arrayParser['[상환금액]']          = "LOAN_INFO_RETURN_PLAN__origin_trade_money";
                $arrayParser['[투자잔액]']          = "LOAN_INFO_RETURN_PLAN__balance";
            }
        }

        $arrayParser = array_merge($arrayParser,Vars::$arraySmsCommonParser);
		
        // 대출,계약 이외에 필요한 테이블이 있는지 확인한다. - 아마도 이제는 불필요할듯
		if( $arrayKeys!=null && is_array($arrayKeys) )
		{
			foreach($arrayKeys as $table => $tno)
			{
				$arrayResult[strtolower($table)] = call_user_func(__NAMESPACE__ .'\SMS::get_'.strtolower($table),$no,$tno);
			}
		}
        //Log::debug(print_r($arrayResult, true));

		if(isset($arrayResult))
		{
			foreach($arrayResult as $table=>$result)
			{
                if(empty($result)) continue;
				foreach($result as $column => $value )
	            {
					// 파싱열에 포함된 컬럼만 배열로 저장한다.
					$pullColumn = $table.'__'.$column;
					if(in_array($pullColumn, $arrayParser))
                    {
						$arrayMsg[$pullColumn] = $value;
                    }
	            }
			}

            //Log::debug(print_r($arrayParser, true));
            foreach($arrayParser as $parser => $column)
            {
                if(!isset($arrayMsg[$column]))
                {
                    continue;
                }
                else
                {
                    $value = $arrayMsg[$column];

                    if($column == "reg_date")
                    {
                        $value = date("Y-m-d", strtotime($value));
                    }
					else if($column == "return_date2"){
						$value = date("m월 d일", strtotime($value));
					}
					else if($column == "return_date2"){

					}
                }            
                $msg = str_replace($parser, $value, $msg);
            }
		}
        return $msg;
	}

    /**
 	* 문자발송 Npro
 	* @param $arrayMsg 메세지 정보
 	* @param $type 기본값 SMS, 알림톡 KAKAO
 	* @return $msg = 변환된 메세지
 	*/
 	public static function smsSend($arrayMsg, $ban_check="")
 	{		
        // 비어있는 문자 제외
        if (!isset($arrayMsg['message']) || empty(trim($arrayMsg['message']))) {
            return 'N';
        }

        // 문자금지체크 두번째 파라미터로 PASS가 오면 안한다.
        if( !empty($arrayMsg['cust_info_no']) && $ban_check!="PASS" )
        {
            // 일괄문자 여부
            if(!isset($arrayMsg['lumpYn']) || $arrayMsg['lumpYn']=='Y')
            {
                $lumpYn = 'Y';
            }
            else 
            {
                $lumpYn = 'N';
            }

            $banInfo = Func::getBanInfo($arrayMsg['cust_info_no'], "Y", $lumpYn);
            if( $banInfo['ban_sms'] == 'Y' || ( $banInfo['ban_anne'] == 'Y' && Func::nvl($arrayMsg['sms_div']) != '26' ) )
            {
                return 'N';
            }
        }

        $cmp_msg_id = 0;
        DB::beginTransaction();

        // 임시.
        // $arrayMsg['receiver'] = '01092158919';
        $arrayMsg['receiver'] = str_replace("-","",$arrayMsg['receiver']);
        $arrayMsg['receiver'] = str_replace(" ","",$arrayMsg['receiver']);

        
        // 예약시간
        $reserve_time = isset($arrayMsg['reserve_time']) ? $arrayMsg['reserve_time'] : '';

        // 문자발송 로그 데이터 세팅
        $_DATA = [
            "loan_app_no"	    =>	$arrayMsg['loan_app_no'] ?? null,
            "div"		        =>	'S',
            "ups_erp"		    =>	$arrayMsg['ups_erp'],
            "sms_div"	        =>	isset($arrayMsg['sms_div']) ? $arrayMsg['sms_div'] : NULL,
            "sender"	        =>	str_replace("-","",$arrayMsg['sender']),
            "receiver"	        =>	str_replace("-","",$arrayMsg['receiver']),
            "message"	        =>	$arrayMsg['message'],
            "save_id"	        =>	isset(Auth::user()->id) ? Auth::user()->id : 'SYSTEM',
            "save_time"	        =>	date("YmdHis"),
            "save_status"	    =>	"Y",
            "reserve_time"	    =>	$reserve_time,
            "cust_info_no"      =>  $arrayMsg['cust_info_no'] ?? null,
        ];
        
        $rslt = DB::dataProcess("INS", "SUBMIT_SMS_LOG", $_DATA, null, $cmp_msg_id);

        if($rslt == 'Y')
        {
            unset($_DATA);
            
            $msgByte = 0;
            if( mb_detect_encoding($arrayMsg["message"], ['UTF-8','EUC-KR'], true)=="UTF-8" )
            {
                $msgByte = mb_strwidth(iconv("UTF-8","EUC-KR", $arrayMsg["message"]), "EUC-KR");
                //Log::debug('UTF-8 : SMS_CHECK '.$msgByte.':'.$arrayMsg["message"]);
            }
            else 
            {
                $msgByte = mb_strwidth($arrayMsg["message"], "EUC-KR");
                // Log::debug('EUC-KR : SMS_CHECK '.$msgByte.':'.$arrayMsg["message"]);
            }

            $encMsg = SMS::npro_encode($arrayMsg['message']);

            // LMS 발송
            if( $msgByte > 90)
            {
                // 6. MMS(LMS)
                $MSG_TYPE = '6';
                $sms_lms_div = 'L';

                // 문자 컨텐츠 세팅
                $_DATA = [
                    "MMS_REQ_DATE"	=>	($reserve_time != '') ? date("Y-m-d H:i:s", strtotime($reserve_time)) : DB::raw("NOW()"),
                    "FILE_CNT"		=>	1,
                    "MMS_BODY"		=>	$encMsg,
                    "MMS_SUBJECT"	=> 	SMS::npro_encode('[청대부]'),
                ];

                // 운영환경만
                if(config('app.env')=='prod')
                {
                    $CONT_SEQ = DB::connection("sms")->table("MMS_CONTENTS_INFO")->insertGetId($_DATA, "CONT_SEQ");
                }
                else 
                {
                    $CONT_SEQ = rand(100000, 999999);
                }
            }
            else 
            {
                $MSG_TYPE = '4';
                $sms_lms_div = 'S';
            }
            
            unset($_DATA);

            // 문자 메인 세팅
            $_DATA = [
                "CUR_STATE"	=>	0,
                "REQ_DATE"	=>	isset($arrayMsg["reserve_time"]) ? date("Y-m-d H:i:s", strtotime($arrayMsg["reserve_time"])) : DB::raw("NOW()"),
                "CALL_TO"	=>	SMS::npro_encode($arrayMsg['receiver']),
                "CALL_FROM"	=>	SMS::npro_encode($arrayMsg["sender"]),
                "MSG_TYPE"	=>	$MSG_TYPE,
                "CONT_SEQ"	=>	isset($CONT_SEQ) ? $CONT_SEQ : NULL,
                "TRAN_ETC1"	=>	'K',	// 예전에 사용하던지점인데 필요시 세팅할 것.
                "TRAN_ETC2"	=>	isset($cmp_msg_id) ? $cmp_msg_id : NULL,
                "TRAN_ETC3"	=>	'N',
            ];

            if($MSG_TYPE == "4")
            {
                $_DATA["SMS_TXT"] = $encMsg;
            }
            
            // 운영환경만
            if(config('app.env')=='prod')
            {
                $msg_no = DB::connection('sms')->table("MSG_DATA")->insertGetId($_DATA, "MSG_SEQ");
            }
            else 
            {
                $msg_no = rand(100000, 999999);
            }
                        
            // SMS/LMS 테이블 no 값 문자로그테이블에 저장
            if( $rslt=="Y" && $msg_no>0 )
            {
                unset($_DATA);
                $_DATA["send_msg_no"] = $msg_no;
                $_DATA['sms_lms_div'] = $sms_lms_div;
                DB::dataProcess('UPD','SUBMIT_SMS_LOG', $_DATA, ['no'=>$cmp_msg_id]);

                DB::commit();
                return 'Y';
            }
            else
            {
                DB::rollback();
                return 'N';
            }
        }
        else
        {
            DB::rollback();
            return 'N';
        }          
 	}

    /**
    * 문자발송 Npro API
    * @param $arrayMsg 메세지 정보
    * @param $type 기본값 SMS, 알림톡 KAKAO
    * @return $msg = 변환된 메세지
    */
    public static function smsSendNproApi($arrayMsg, $ban_check="")
    {		
        // 비어있는 문자 제외
        if (!isset($arrayMsg['message']) || empty(trim($arrayMsg['message']))) {
            return 'N';
        }

        // 문자금지체크 두번째 파라미터로 PASS가 오면 안한다.
        if( !empty($arrayMsg['cust_info_no']) && $ban_check!="PASS" )
        {
            // 일괄문자 여부
            if(!isset($arrayMsg['lumpYn']) || $arrayMsg['lumpYn']=='Y')
            {
                $lumpYn = 'Y';
            }
            else 
            {
                $lumpYn = 'N';
            }

            $banInfo = Func::getBanInfo($arrayMsg['cust_info_no'], "Y", $lumpYn);
            if( $banInfo['ban_sms'] == 'Y' || ( $banInfo['ban_anne'] == 'Y' && Func::nvl($arrayMsg['sms_div']) != '26' ) )
            {
                return 'N';
            }
        }

        $cmp_msg_id = 0;
        DB::beginTransaction();

        
		$arrayMsg["sender"] = env('SENDER_PH');
        //$arrayMsg['receiver'] = '01089377858';
        $arrayMsg['receiver'] = str_replace("-","",$arrayMsg['receiver']);
        $arrayMsg['receiver'] = str_replace(" ","",$arrayMsg['receiver']);

        // 예약시간
        $reserve_time = isset($arrayMsg['reserve_time']) ? $arrayMsg['reserve_time'] : '';

        // 문자발송 로그 데이터 세팅
        $_DATA = [
            "loan_app_no"	    =>	$arrayMsg['loan_app_no'] ?? null,
            "div"		        =>	'S',
            "ups_erp"		    =>	$arrayMsg['ups_erp'],
            "sms_div"	        =>	isset($arrayMsg['sms_div']) ? $arrayMsg['sms_div'] : NULL,
            "sender"	        =>	str_replace("-","",$arrayMsg['sender']),
            "receiver"	        =>	str_replace("-","",$arrayMsg['receiver']),
            "message"	        =>	$arrayMsg['message'],
            "save_id"	        =>	isset(Auth::user()->id) ? Auth::user()->id : 'SYSTEM',
            "save_time"	        =>	date("YmdHis"),
            "save_status"	    =>	"Y",
            "reserve_time"	    =>	$reserve_time,
            "cust_info_no"      =>  $arrayMsg['cust_info_no'] ?? null,
        ];
        
        $rslt = DB::dataProcess("INS", "SUBMIT_SMS_LOG", $_DATA, null, $cmp_msg_id);

        if($rslt != 'N')
        {
            unset($_DATA);

            $msgByte = 0;
            // if( mb_detect_encoding($arrayMsg["message"], ['UTF-8','EUC-KR'], true)=="UTF-8" )
            // {
            //     $msgByte = mb_strwidth(iconv("UTF-8","EUC-KR", $arrayMsg["message"]), "EUC-KR");
            //     Log::debug('UTF-8 : SMS_CHECK '.$msgByte.':'.$arrayMsg["message"]);
            // }
            // else 
            // {
            //     $msgByte = mb_strwidth($arrayMsg["message"], "EUC-KR");
            //     Log::debug('EUC-KR : SMS_CHECK '.$msgByte.':'.$arrayMsg["message"]);
            // }

            if($reserve_time == '')
                $reserve_time = date("Y-m-d H:i:s");
            else
                $reserve_time = date("Y-m-d H:i:s", strtotime($reserve_time));

            $arrayMsg["message"] = str_replace("\r", "", $arrayMsg["message"]);
            $arrayMsg["message"] = str_replace("\n", "", $arrayMsg["message"]);

            // LMS 발송
            if(strlen($arrayMsg["message"]) > 90)
            // if( $msgByte > 90 )
            {
                $seq_table = 'eumgp_msg_queue_seq_seq';
                $sms_lms_div = 'L';
                $lms = (DB::select("SELECT nextval('eumgp_msg_queue_seq_seq') AS no"))[0];
                $msg_no = $lms->no;
                $arrayMsg['subject'] = '제목없음';

                // 문자 컨텐츠 세팅
                $_DATA = [
                    "SEQ"           =>  $msg_no,
                    "STATUS"        =>  "0",
                    "REQ_DATE"      =>  $reserve_time,
                    "RECEIVERNUM"   =>  SMS::npro_encode($arrayMsg['receiver']),
                    "CALLBACK"	    =>	SMS::npro_encode($arrayMsg["sender"]),
                    "MSG_TYPE"      => "M",
                    "MSG"           =>  SMS::npro_encode($arrayMsg['message']),
                    "SUBJECT"       =>  SMS::npro_encode($arrayMsg['subject']),
                    "CONTENTS_TYPE" =>  "TXT",
                ];
                // $rslt = DB::table('eumgp_msg_queue')->insert($_DATA);
                $rslt = DB::dataProcess("INS", "eumgp_msg_queue", $_DATA);
            }
            // SMS 발송
            else
            {
                $seq_table = 'eumgp_msg_queue_seq_seq';
                $sms_lms_div = 'S';
                $sms = (DB::select("SELECT nextval('eumgp_msg_queue_seq_seq') AS no"))[0];
                $msg_no = $sms->no;

                // SMS 데이터 세팅
                $_DATA = [
                    "SEQ"           =>  $msg_no,
                    "STATUS"        =>  "0",
                    "REQ_DATE"      =>  $reserve_time,
                    "RECEIVERNUM"   =>  SMS::npro_encode($arrayMsg['receiver']),
                    "CALLBACK"	    =>	SMS::npro_encode($arrayMsg["sender"]),
                    "MSG_TYPE"      => "S",
                    "MSG"           =>  SMS::npro_encode($arrayMsg['message']),
                ];
                // $rslt = DB::table('eumgp_msg_queue')->insert($_DATA);
                $rslt = DB::dataProcess("INS", "eumgp_msg_queue", $_DATA);
            }

            // SMS/LMS 테이블 no 값 문자로그테이블에 저장
            if( $rslt=="Y" && $msg_no>0 )
            {
                unset($_DATA);
                //$msg_no = Func::getSeqPrevval($seq_table);
                $_DATA["send_msg_no"] = $msg_no;
                $_DATA['sms_lms_div'] = $sms_lms_div;
                DB::dataProcess('UPD','SUBMIT_SMS_LOG', $_DATA, ['no'=>$cmp_msg_id]);

                DB::commit();
                return 'Y';
            }
            else
            {
                DB::rollback();
                return 'N';
            }
        }
        else
        {
            DB::rollback();
            return 'N';
        }          
    }


    /*
	*	계약번호로 LOAN_INFO  가져오기
	*/
	public static function getLoanInfo($no, $today="", $array_config)
	{
        if( $today=="" )
        {
            $today = date("Ymd");
        }

        // if( $list_query!="" )
        // {
        //     $list_query = decrypt(urldecode($list_query));
        //     $list_query = "LOAN_INFO.NO IN ( SELECT NO FROM ( ".$list_query." ) AS tmp_table_sms )";
        //     log::debug($list_query);
        // }

        // 고객별 통합할 변수
        $cus_over_money     = 0;
        $cus_balance        = 0;
        $cus_interest       = 0;
        $cus_delay_money    = 0;
        $cus_delay_interest = 0;
        $cus_lack_money     = 0;
        $cus_interest_sum   = 0;
        $cus_charge_money   = 0;
        $cus_fullpay_money  = 0;
        $cus_fullpay_moneye = 0;

		$DATA = DB::table('LOAN_INFO');
        $DATA->JOIN("CUST_INFO", "LOAN_INFO.CUST_INFO_NO", "=", "CUST_INFO.NO");
        $DATA->JOIN("CUST_INFO_EXTRA", "CUST_INFO.NO", "=", "CUST_INFO_EXTRA.CUST_INFO_NO");
        $DATA->LEFTJOIN(DB::RAW("(SELECT LOAN_INFO_NO, SSN as G_SSN, NAME as G_NAME FROM LOAN_INFO_GUARANTOR WHERE NO IN (SELECT MAX(NO) FROM LOAN_INFO_GUARANTOR WHERE SAVE_STATUS = 'Y' GROUP BY LOAN_INFO_NO)) as LOAN_INFO_GUARANTOR"), "LOAN_INFO.NO", "=", "LOAN_INFO_GUARANTOR.LOAN_INFO_NO");
        $DATA->SELECT("LOAN_INFO.*", "CUST_INFO.*", "CUST_INFO_EXTRA.*", "LOAN_INFO.NO AS LOAN_INFO_NO", "LOAN_INFO_GUARANTOR.G_NAME");
        $DATA->WHERE('CUST_INFO.SAVE_STATUS','Y');
        $DATA->WHERE('LOAN_INFO.SAVE_STATUS','Y');
        $DATA->WHERE('LOAN_INFO.STATUS', '!=', 'X');
        $DATA->WHERERAW("COALESCE(ban_sms,'')!='Y'");
        $DATA->WHERE("LOAN_INFO.NO", $no);
        
		$RSLT = $DATA->ORDERBY("LOAN_INFO.NO", "DESC")->GET();
        $RSLT = Func::chungDec(["LOAN_INFO","CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO_GUARANTOR"], $RSLT);	// CHUNG DATABASE DECRYPT

        $isSettleYn = 'N';
        $isMonthMoneyYn = 'N';

        $cnt = 0;
        foreach( $RSLT as $result )
        {
            $cnt++;

            // 다계좌중 한건이라도 화해가 있는지 체크
            if( $isSettleYn=='N' && ($result->status=='C' || $result->status=='D'))
            {
                $isSettleYn = 'Y';
            }

            // 월상환액 無
            if( $result->status=='C' || $result->status=='D' || ( $result->return_method_cd=="F" && $result->monthly_return_money==0 ) )
            {
            }
            // 월상환액 有
            else
            {
                $isMonthMoneyYn = 'Y';
            }
            
            $result->contract_date              = Func::dateFormat($result->contract_date);                 //  [계약일]
            $result->contract_end_date          = Func::dateFormat($result->contract_end_date);             //  [만기일]
            $result->contract_end_date2         = Func::dateFormat($result->contract_end_date, ".");        //  [만기일(.)]
            $result->loan_date                  = Func::dateFormat($result->loan_date);                     //  [대출일]
            $result->return_date                = Func::dateFormat($result->return_date);                     //  [상환일]
            $result->loan_money                 = number_format($result->loan_money);                       //  [대출액]
            $result->ph2                        = $result->ph21.'-'.$result->ph22.'-'.$result->ph23;        //  [핸드폰번호]
            $result->return_date_biz            = date("m월 d일", strtotime($result->return_date_biz)+86400);     //  [연체발생일]
			$result->return_date2            	= date("m월 d일", strtotime($result->return_date));    		 //  [상환일월일]

            // 금액
            $result->over_money     = $result->over_money;                       //  [가수금]
            $result->balance        = $result->balance;                          //  [잔액]
            $result->interest       = $result->interest;                         //  [당일정상이자]
            $result->delay_money    = $result->delay_money;                      //  [당일지연배상금]
            $result->delay_interest = $result->delay_interest;                   //  [당일연체이자]
            $result->lack_money     = $result->lack_interest + $result->lack_delay_money + $result->lack_delay_interest;                   //  [부족금]
            $result->interest_sum   = $result->interest_sum;                     //  [이자합계]
            $result->charge_money   = $result->charge_money;                     //  [당일청구금액]
            $result->fullpay_money  = $result->fullpay_money;                    //  [완납금액]
            $result->monthly_return_money  = $result->monthly_return_money;      //  [월상환액]
            $result->last_in_date  = Func::dateFormat($result->last_in_date);                      //  [최근납입일]
            $result->last_in_money  = $result->last_in_money;                    //  [최근납입금]
			$result->return_date_interest  = $result->return_date_interest;                    //  [상환일이자]
            $result->fullpay_moneye = 0;
            
            $result->g_name = Func::decrypt($result->g_name, 'ENC_KEY_SOL');     // [보증인이름]

            // 오늘이자가 아니면 계산 = 2021-08-19 과거완제고객 문자발송을 위한 예외처리. 완제고객은 이자를 계산할 필요가 없음.
            if( $today != $result->calc_date && $result->status!='E' )
            {
                $loan = new Loan($result->loan_info_no);
                $val_int = $loan->getInterest($today);

                $result->interest       = $val_int['interest'];                         //  [당일정상이자]
                $result->delay_money    = $val_int['delay_money'];                      //  [당일지연배상금]
                $result->delay_interest = $val_int['delay_interest'];                   //  [당일연체이자]
                $result->interest_sum   = $val_int['interest_sum'];                     //  [이자합계]
                $result->charge_money   = $val_int['charge_money'];                     //  [당일청구금액]
                $result->fullpay_money  = $val_int['fullpay_money'];                    //  [완납금액]
            }
            if( $result->status!='E' && $result->status!='M' )
            {
                $loan = new Loan($result->loan_info_no);
                $val_int = $loan->getInterest($loan->loanInfo['contract_end_date']);
                $result->fullpay_moneye  = $val_int['fullpay_money'];                    //  [만기일완납금액]
            }

            // 금액 = 고객별 금액 합산 ////////////////////////////////////////////////////////////////////////////////////////////////////
            $cus_over_money     += $result->over_money;
            $cus_balance        += $result->balance;
            $cus_interest       += $result->interest;
            $cus_delay_money    += $result->delay_money;
            $cus_delay_interest += $result->delay_interest;
            $cus_lack_money     += $result->lack_money;
            $cus_interest_sum   += $result->interest_sum;
            $cus_charge_money   += $result->charge_money;
            $cus_fullpay_money  += $result->fullpay_money;
            $cus_fullpay_moneye += $result->fullpay_moneye;
            // 금액 = 고객별 금액 합산 ////////////////////////////////////////////////////////////////////////////////////////////////////


            // 34 개인회생기각, 35 개인회생폐지, 36 신복위실효
            if( $result->attribute_delay_cd=="34" || $result->attribute_delay_cd=="35" || $result->attribute_delay_cd=="36" )
            {
                $arry_cnf = Func::getConfigArr('delay_rsn_cd');
                $result->cancel_reason = $arry_cnf[$result->attribute_delay_cd];
            }
            else
            {
                $result->cancel_reason = "";
            }

            $result->blank = "";        //  파싱 애매한 것 공백으로 일단 나오도록
        }


        if( $cnt>=1 )
        {
            $result->over_money     = number_format($cus_over_money);
            $result->balance        = number_format($cus_balance);
            $result->interest       = number_format($cus_interest);
            $result->delay_money    = number_format($cus_delay_money);
            $result->delay_interest = number_format($cus_delay_interest);
            $result->lack_money     = number_format($cus_lack_money);
            $result->interest_sum   = number_format($cus_interest_sum);
            $result->charge_money   = number_format($cus_charge_money);
            $result->fullpay_money  = number_format($cus_fullpay_money);
            $result->fullpay_moneye = number_format($cus_fullpay_moneye);
			$result->return_date_interest = number_format($result->return_date_interest);
        }

        if( $cnt==0 )
        {
            return 0;
        }
        else if( $cnt==1 )
        {
            // '[당일청구금액75]'    => 'loan_info__charge_money_75',
            // '[당일청구금액74]'    => 'loan_info__charge_money_74',
            // '[당일청구금액01]'    => 'loan_info__charge_money_01',

            // 월 상환액 無
            if( $result->status=='C' || $result->status=='D' || ( $result->return_method_cd=="F" && $result->monthly_return_money==0 ) )
            {
                $result->charge_money_75 = "안내입니다. ".$result->charge_money."원";
                $result->charge_money_74 = substr($today,6,2)."일 기준 입금액 ".$result->charge_money."원";
                $result->charge_money_01 = substr($today,6,2)."일 기준 입금금액 ".$result->charge_money."원";
            }
            // 월 상환액 有
            else
            {
                $result->charge_money_75 = $result->charge_money."원(이자".$result->interest_sum.")";
                $result->charge_money_74 = substr($today,6,2)."일입금액".$result->charge_money."원(이자".$result->interest_sum.")";
                $result->charge_money_01 = "당월입금액".$result->charge_money."(".substr($today,6,2)."일기준이자".$result->interest_sum.")";
            }

        }
        else
        {
            $charge_money = $result->charge_money;
            $interest_sum = $result->interest_sum;

            // 월 상환액 無
            if($isMonthMoneyYn=='N')
            {
                $result->charge_money_01 = substr($today,6,2)."일 기준 입금금액 ".$charge_money."원";
                $result->charge_money_74 = substr($today,6,2)."일 기준 입금액 ".$charge_money."원";
                $result->charge_money_75 = "안내입니다. ".$charge_money."원";
            }
            // 월 상환액 有
            else
            {
                $result->charge_money_75 = $result->charge_money."원(이자".$result->interest_sum.")";
                $result->charge_money_74 = substr($today,6,2)."일입금액".$result->charge_money."원(이자".$result->interest_sum.")";
                $result->charge_money_01 = "당월입금액".$result->charge_money."(".substr($today,6,2)."일기준이자".$result->interest_sum.")";

                $result->charge_money_01 = "당월입금액".$charge_money."(".substr($today,6,2)."일기준이자".$interest_sum.")";
                $result->charge_money_74 = substr($today,6,2)."일입금액".$charge_money."원(이자".$interest_sum.")";
                $result->charge_money_75 = $charge_money."원(이자".$interest_sum.")";
            }

            //$result->charge_money = "입금액 합계 ".$charge_money;
            $result->charge_money = $charge_money;
            
        }

        // [추심착수안내금액]
        if( $isSettleYn=='Y')
        {
            $result->charge_money_59 = "원금:지점문의/이자:지점문의";
        }
        else
        {   
            $result->charge_money_59 = "원금:".$result->balance."원/이자:".$result->interest_sum."원";
        }

        $result->corp_addr = env('CORP_ADDR1')." ".env('CORP_ADDR2');           //  [계약서발송주소]

		return $result;
	}

    /*
	*	신청번호로 LOAN_APP 가져오기
	*/
	public static function getLoanAppInfo($no)
	{
		$result = DB::TABLE('LOAN_APP')->Join('LOAN_APP_EXTRA', 'LOAN_APP.NO', '=', 'LOAN_APP_EXTRA.LOAN_APP_NO')
                    ->SELECT("LOAN_APP.name, LOAN_APP.app_money, LOAN_APP.app_date, LOAN_APP_EXTRA.ph21, LOAN_APP_EXTRA.ph22, LOAN_APP_EXTRA.ph23, LOAN_APP.ssn, LOAN_APP.NO AS LOAN_APP_NO, LOAN_APP.vir_acct_ssn ")
                    ->WHERE('LOAN_APP.SAVE_STATUS','Y')
					->where('LOAN_APP.NO', $no)
					->FIRST();
        $result = Func::chungDec(["LOAN_APP","LOAN_APP_EXTRA"], $result);	// CHUNG DATABASE DECRYPT

        if(isset($result))
        {
            $result->ph2       = $result->ph21.'-'.$result->ph22.'-'.$result->ph23; //  [핸드폰번호]
            $result->app_date  = Func::dateFormat($result->app_date);               //  [신청일]
            $result->app_money = number_format($result->app_money);                 //  [신청액]
            $result->online_app_no = Func::getOnlineAppNo($result->loan_app_no);    //  [온라인신청번호]
            $result->corp_addr = env('CORP_ADDR1')." ".env('CORP_ADDR2');           //  [계약서발송주소]
            $result->blank     = "";        //  파싱 애매한 것 공백으로 일단 나오도록
        }
        else
        {
            return 0;
        }

		return $result;
	}
    
    /*
	*	계약번호로 LOAN_INFO_TRADE  가져오기
	*/
	public static function get_loan_info_trade($no,$tno=null)
	{
		$result = DB::table('LOAN_INFO_TRADE')
                    ->SELECT("LOAN_INFO_TRADE.*")
                    ->WHERE('TRADE_DIV','I')
                    ->WHERE('SAVE_STATUS','Y')
					->WHERE("LOAN_INFO_NO", $no);
        if($tno) $result->WHERE("NO",$tno);
        $result  = $result->ORDERBY('NO','DESC')->FIRST();
        $result = Func::chungDec(["LOAN_INFO_TRADE"], $result);	// CHUNG DATABASE DECRYPT

        if(isset($result))
        {
            $result->trade_date          = Func::dateFormat($result->trade_date);           //  [입금일]
            $result->trade_money         = number_format($result->trade_money);             //  [입금금액]
            $result->return_interest_sum = number_format($result->return_interest_sum);     //  [입금이자]
        }
        else
        {
            $result = (object) null;
            $result->trade_date          = "";
            $result->trade_money         = "";
            $result->return_interest_sum = "";
        }

		$result2 = DB::table('LOAN_INFO_TRADE')->SELECT("LOAN_INFO_TRADE.*")->WHERE('TRADE_DIV','I')->WHERE('SAVE_STATUS','N')->WHERE("LOAN_INFO_NO", $no);
        if($tno) $result2->WHERE("NO",$tno);
        $result2 = $result2->ORDERBY('NO','DESC')->FIRST();
        $result2 = Func::chungDec(["LOAN_INFO_TRADE"], $result2);	// CHUNG DATABASE DECRYPT

        if(isset($result2))
        {
            $result->cancel_date  = Func::dateFormat($result2->trade_date);           //  [입금일]
            $result->cancel_money = number_format($result2->trade_money);             //  [입금금액]
        }
        else
        {
            $result->cancel_date = "";
            $result->cancel_money = "";
        }


		return $result;
	}

    /*
	*	계약번호로 VIR_ACCT  가져오기
	*/
	public static function get_vir_acct($cust_info_no, $tno=null)
	{
        $vir = DB::TABLE("VIR_ACCT")->SELECT("*")->WHERE("CUST_INFO_NO",$cust_info_no)->WHERE("SAVE_STATUS","Y")->ORDERBY("NO","DESC")->FIRST();
        $vir = Func::chungDec(["VIR_ACCT"], $vir);	// CHUNG DATABASE DECRYPT

        if( isset($vir) )
        {
            $vir->vir_bank_nm  = str_replace("은행","",Func::nvl(Func::getConfigArr('bank_cd')[$vir->bank_cd],""));     //  [가상계좌은행]
            $vir->vir_bank_nm_full = Func::nvl(Func::getConfigArr('bank_cd')[$vir->bank_cd],"");                        //  [가상계좌은행]
            $vir->vir_acct_ssn = Func::bankSsnSmsFormat($vir->bank_cd, $vir->vir_acct_ssn);                                //  [가상계좌번호]
            $vir->vir_acct_full = $vir->vir_bank_nm." ".$vir->vir_acct_ssn;
        }
        else
        {
            return 0;
        }

		return $vir;
	}
    
    /*
	*	계약번호로 VISIT  가져오기
	*/
	public static function get_visit($no,$vno=null)
	{
        $vis = DB::TABLE("VISIT")->SELECT("*")->WHERE("LOAN_INFO_NO",$no)->WHERE("STATUS",'!=',"C");
        if($vno) $vis->WHERE("NO",$vno); 
        $vis = $vis->ORDERBY("NO","DESC")->FIRST();
        $vis = Func::chungDec(["VISIT"], $vis);	// CHUNG DATABASE DECRYPT

        if( isset($vis) )
        {
            $vis->visit_req_date          = Func::dateFormat($vis->visit_req_date);             //  [방문예정월일]
            $vis->visit_req_hour          = Func::nvl($vis->visit_req_hour,'--')."시";          //  [방문예정시간]
            $vis->visit_addr              = $vis->visit_addr11.$vis->visit_addr12;              //  [방문예정주소]
        }
        else
        {
            return 0;
        }

		return $vis;
	}
    /**
        * 고객 전화번호 가져오기
        * yjlee
        * @param  String
        * @return phone 
        */
        public static function getCustomerPh($cust_info_no)
        {
            $phone = null;
            $v = DB::TABLE("CUST_INFO")->JOIN('CUST_INFO_EXTRA','CUST_INFO.NO','=','CUST_INFO_EXTRA.CUST_INFO_NO')
                        ->SELECT("CUST_INFO_EXTRA.PH21" , "CUST_INFO_EXTRA.PH22", "CUST_INFO_EXTRA.PH23")
                        ->WHERE("CUST_INFO.SAVE_STATUS",'Y')
                        ->WHERE("CUST_INFO.NO",$cust_info_no)
                        ->FIRST();
            $v = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA"], $v);	// CHUNG DATABASE DECRYPT

            if( !isset($v) || trim($v->ph21)=="" || trim($v->ph22)=="" || trim($v->ph23)=="" )
            {
                return "N";
            }

            if( isset($v) )
            {
                $phone = trim($v->ph21).trim($v->ph22).trim($v->ph23);
            }
            else
            {
                return 0;
            }

            return $phone;
        }
        
    /*
	*	계약번호로 VISIT  가져오기
	*/
	public static function get_branch($no,$type)
	{
        if($type=="ERP"){
            $br = DB::table('LOAN_INFO')->JOIN("BRANCH", "BRANCH.CODE", "=", "LOAN_INFO.MANAGER_CODE")->LEFTJOIN("USERS", "USERS.ID", "=", "LOAN_INFO.MANAGER_ID")
                    ->SELECT("BRANCH.*, USERS.NAME")->WHERE("BRANCH.SAVE_STATUS",'Y')
                    ->WHERE('LOAN_INFO.SAVE_STATUS','Y')
					->WHERE("LOAN_INFO.NO", $no)
					->FIRST();
            $br = Func::chungDec(["LOAN_INFO","BRANCH","USERS"], $br);	// CHUNG DATABASE DECRYPT
        }else{
            $br = DB::TABLE('LOAN_APP')->Join('BRANCH', 'LOAN_APP.BRANCH_CD', '=', 'BRANCH.CODE')
                    ->SELECT("BRANCH.*")->WHERE("BRANCH.SAVE_STATUS",'Y')
                    ->WHERE('LOAN_APP.SAVE_STATUS','Y')
					->where('LOAN_APP.NO', $no)
					->FIRST();
            $br = Func::chungDec(["LOAN_APP","BRANCH"], $br);	// CHUNG DATABASE DECRYPT
        }

        if( isset($br) )
        {
            $br->addr = $br->addr11.' '.$br->addr12;                                        //  ERP_[담당지점주소]
            $br->ceo_name = !empty(Func::getUserList($br->ceo_name)->name) ?? '';           //  ERP_[담당자명]
            $br->manager_name = $br->name ?? '';                                            //  ERP_[담당자]
            $br->phone = !empty($br->phone) ? Func::fullPhFormat($br->phone) : '';          //  UPS_[심사지점전화번호], ERP_[담당지점전화번호]
            $br->fax = !empty($br->fax) ? Func::fullPhFormat($br->fax) : '';                //  UPS_[심사지점팩스번호], ERP_[담당지점팩스번호]
            $br->phone_extra = !empty($br->phone_extra) ? Func::fullPhFormat($br->phone_extra) : '';    //  UPS_[심사지점전화번호]
        }
        else
        {
            return 0;
        }

		return $br;
	}

    public static function get_manager($no, $type)
    {
        if( $type=="ERP" )
        {

        }
        else
        {
            $m = DB::TABLE('LOAN_APP')->Join('BRANCH', 'LOAN_APP.manager_code', '=', 'BRANCH.CODE')
                    ->SELECT("BRANCH.*")->WHERE("BRANCH.SAVE_STATUS",'Y')
                    ->WHERE('LOAN_APP.SAVE_STATUS','Y')
                    ->where('LOAN_APP.NO', $no)
                    ->FIRST();
            $m = Func::chungDec(["LOAN_APP","BRANCH"], $m);	// CHUNG DATABASE DECRYPT
        }
        if( isset($m) )
        {
            $m->phone = !empty($m->phone) ?Func::fullPhFormat($m->phone) : '';              //  [담당부서연락처]
            $m->fax = !empty($m->fax) ?Func::fullPhFormat($m->fax) : '';                    //  [담당부서팩스번호]
            $m->manager_code_nm = $m->branch_name;                                          //  [담당지점]
        }
        else
        {
            return 0;
        }

        return $m;
    }

    public static function get_settle($no,$tno=null)
    {
        $settle = DB::TABLE("LOAN_SETTLE ls")->SELECT("*")->WHERE("loan_info_no", $no)->WHERE("save_status", "Y")->ORDERBY("no","DESC")->first();
        $settle = Func::chungDec(["LOAN_SETTLE"], $settle);	// CHUNG DATABASE DECRYPT

        return $settle;
    }

    public static function npro_encode($str)	
    {
        $key		= config('app.smsKey');		//복호화키값
        $iconv_str	= iconv('UTF-8','EUC-KR',$str);
        $rs_e		= base64_encode(openssl_encrypt($iconv_str, "aes-256-cbc", $key, true, str_repeat(chr(0), 16)));
        return $rs_e;
    }

    public static function npro_decode($str)	
    {
        $key		= config('app.smsKey');		//복호화키값
        $iconv_str	= iconv('EUC-KR','UTF-8',$str);
        $rs_d		= openssl_decrypt(base64_decode($iconv_str), 'aes-256-cbc', $key, true, str_repeat(chr(0), 16));
        $rs_d2		= iconv('EUC-KR','UTF-8', $rs_d);
        return $rs_d2;
    }

    /*
	*	계약번호로 투자내역 가져오기
	*/
	public static function getInvList($loan_info_no, $loan_info_no, $params)
	{
        // Log::debug(print_r($params, true));
		$inv = DB::TABLE('LOAN_INFO as i')->JOIN('loan_usr_info as u','i.loan_usr_info_no','=','u.no')
										->WHERE(['i.no'=>$loan_info_no,'i.save_status'=>'Y','u.save_status'=>'Y'])
										->FIRST();
		
		if(!empty($inv))
		{
			Func::chungDec(['LOAN_INFO','loan_usr_info'],$inv);
			$inv->term = Round(Func::dateTerm($inv->trade_date, $inv->contract_end_date)/30);
            //$inv->balance_han    = $inv->balance."백만원";
            //20000000

            if($inv->fullpay_money > 0)
            {
                $inv->balance_han    = substr($inv->fullpay_money, 0, -6)."백만원";
            }
            else
            {
                $inv->balance_han    = substr($inv->balance, 0, -6)."백만원";
            }           
            $inv->return_pay            = number_format($inv->trade_money-$inv->balance);
            $inv->trade_money           = number_format($inv->trade_money);
            $inv->balance        = number_format($inv->balance);
            $inv->name = $inv->name."님";
            
            // if($inv->company_yn != 'Y')
            // {
            //     $inv->name = $inv->name."님";
            // }

			foreach($inv as $col => $val)
			{
				if(strstr($col,'ph') && strlen($col)>3)
				{
					if(!empty($params['masking']) && $params['masking'] == 'Y')
					{
						// 가운데 전화번호 * 처리
						if($col == 'ph12')
						{
							$val = '****';
						}
					}

					$ph_div = substr($col,'2','1');
					$ph_nm = 'ph'.$ph_div;
					$inv->$ph_nm = isset($inv->$ph_nm) ? $inv->$ph_nm.'-'.$val : $val;
				}
				if(strstr($col,'addr') && strlen($col)>5)
				{
					$addr_div = substr($col,'4','1');
					$addr_nm = 'addr'.$addr_div;

					$inv->$addr_nm = isset($inv->$addr_nm) ? $inv->$addr_nm.' '.$val : $val;
				}
				if($col == 'ratio')
				{
					$inv->delay_ratio = $val+3;
				}
				// if(strstr($col,'money') && !strstr($col,'_han'))
				// {
                //     if(!empty($val))
                //     {
                //         $han = $col.'_han';
                //         $inv->$han = PaperPrint::getHanMoney($val).'원';
                //         $inv->$col = number_format($val ?? 0);
                //     }
				// }
				if(strstr($col,'_date') && (strlen($inv->$col) == 8))
				{
					$col_y = $col.'_y';
					$col_md = $col.'_md';
					$inv->$col_y = date('Y',strtotime($inv->$col));
					$inv->$col_md = preg_replace('/(0)(\d)/','$2', date('m월 d일',strtotime($inv->$col)));
					$inv->$col = date('Y 년',strtotime($inv->$col))." ".preg_replace('/(0)(\d)/','$2', date('m월 d일',strtotime($inv->$col)));
				}
				if($col == 'ssn')
				{
					if(!empty($inv->com_ssn))
					{
						$inv->com_ssn = str_replace('-', '', $inv->com_ssn);
						if(!empty($params['masking']) && $params['masking'] == 'Y')
						{
							$inv->$col = substr($inv->com_ssn, 0, 3)."-".substr($inv->com_ssn, 3, 2)."-*****";
						}
						else
						{
							$inv->$col = substr($inv->com_ssn, 0, 3)."-".substr($inv->com_ssn, 3, 2)."-".substr($inv->com_ssn, 5, 5);
						}	
					}
					else
					{
						if(!empty($params['masking']) && $params['masking'] == 'Y')
						{
							$inv->$col = Func::ssnFormat($val,'Y');
						}
						else
						{
							$inv->$col = Func::ssnFormat($val,'A');
						}
						
					}
				}
			}
		}

        if(!empty($loan_next))
        {
            $loan_prev = DB::TABLE('LOAN_INFO as i')->JOIN('loan_usr_info as u','i.loan_usr_info_no','=','u.no')
                                        ->SELECT('i.no', 'i.contract_end_date', 'i.ratio')
                                        ->WHERE('i.loan_info_no', $params['loan_info_no'])
                                        ->WHERE('i.loan_usr_info_no', $params['loan_usr_info_no'])
                                        ->WHERE('i.no', '<', $loan_next->no)
                                        ->WHERE('i.save_status', 'Y')
                                        ->WHERE('u.save_status', 'Y')
                                        ->ORDERBY('i.no', 'desc')
                                        ->FIRST();
    
                                        
            if(!empty($loan_prev))
            {
                $inv->next_end_date = date('Y년',strtotime($loan_next->contract_end_date))." ".preg_replace('/(0)(\d)/','$2', date('m월 d일',strtotime($loan_next->contract_end_date)));
                $inv->prev_end_date = date('Y년',strtotime($loan_prev->contract_end_date))." ".preg_replace('/(0)(\d)/','$2', date('m월 d일',strtotime($loan_prev->contract_end_date)));
                $inv->next_ratio    = $loan_next->ratio;
                $inv->prev_ratio    = $loan_prev->ratio;
            }
        }
        
		// log::debug('inv - '.print_R($inv,1));
		return $inv;
	}

        /*
	*	계약번호로 투자내역 가져오기
	*/
	public static function getReturnList($loan_info_no, $input_params, $param)
	{
        Log::debug("====================>");
        Log::debug($loan_info_no);
        Log::debug(print_r($param, true));

        if(!empty($param['target_divide_date']))
        {
            $param['target_divide_date'] = str_replace("-", "", $param['target_divide_date']);
        }

        $invTarget= [];        

        // 조회할 투자자의 스케줄에 투자원금상환액 표기 목적으로 투자원금조정이력에서 계약번호와 trade_date 기준으로 조회된 투자원금조정금액을 가져온다.
        $logVs = DB::TABLE('loan_divide_origin_log')->SELECT('divide_info','trade_date')->WHERE('save_status','Y')->WHERE('loan_info_no',$param['loan_info_no'])->GET();
        
        if(!empty($logVs))
        {
            foreach($logVs as $logV)
            { 
                $arrJson = json_decode($logV->divide_info, true);
                foreach($arrJson as $arrInv)
                {
                    // 잔액변동이 있었던 건만 배열에 담는다.
                    if($arrInv['trade_money'] > 0)
                    {
                        $INV[$arrInv['loan_info_no']][$logV->trade_date]['trade_date'] = $logV->trade_date;
                        $INV[$arrInv['loan_info_no']][$logV->trade_date]['trade_money'] = $arrInv['trade_money'];
                        $invTarget[] = $arrInv['loan_info_no'];
                    }
                }
            }
        }

		$plans = DB::TABLE("loan_info_return_plan")->JOIN("LOAN_INFO", "LOAN_INFO.NO", "=", "loan_info_return_plan.loan_info_no")
                                                     ->SELECT("loan_info_return_plan.*, LOAN_INFO.balance, LOAN_INFO.trade_money")
                                                     ->WHERE("loan_info_no", $loan_info_no)
                                                     ->WHERE('divide_date', $param['target_divide_date'])
                                                     ->ORDERBY('plan_date')
                                                     ->FIRST();
        $plans = Func::chungDec(["loan_info_return_plan"], $plans);	// CHUNG DATABASE DECRYPT

        if(!empty($plans))
        {
            // 선택된 loan_no의 투자원금조정이력이 있고 스케줄의 상환일과 일치하는 투자원금조정일자가 있으면 투자원금상환액 입력
            if(in_array($loan_info_no, $invTarget))
            {
                if(isset($INV[$loan_info_no][$plans->plan_date]['trade_date']) && $INV[$loan_info_no][$plans->plan_date]['trade_date']==$plans->plan_date)
                {
                    $origin_trade_money = $INV[$loan_info_no][$plans->plan_date]['trade_money'];
                }
                else
                {
                    $origin_trade_money = 0;
                }
            }
            else
            {
                $origin_trade_money = 0;
            }

            $plans->origin_trade_money  = number_format($origin_trade_money);
            $plans->plan_money_tail     = number_format($plans->plan_money);
            $plans->balance      = number_format($plans->plan_money-$origin_trade_money);
            $plans->loan_term           = Loan::dateTerm($plans->plan_interest_sdate, $plans->plan_interest_edate, 1);
            $plans->return_interest     = number_format($plans->plan_interest-$plans->withholding_tax);
            $plans->plan_interest       = number_format($plans->plan_interest);
            $plans->withholding_tax     = number_format($plans->withholding_tax);
        }

		return $plans;
	}
}
