<?php
namespace App\Http\Controllers\Lump;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;
use Redirect;
use App\Chung\Sms;

class LumpSmsController extends Controller
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
    * SMS 미리보기
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function lumpSmsPreview(Request $request)
    {
        $msg = $request->message;
        // 신청번호
        if($request->batchSmsDiv=='ups')
        {
            $ups_erp = 'UPS';
        }
        // 계약번호로 넘어온다. cust_info_no를 가져와야함.
        else if($request->batchSmsDiv=='erp')
        {
            $ups_erp = 'ERP';
        }
        else
        {
            $RS['rs_code'] = "N";
            $RS['rs_msg'] = "처리할 내역이 없습니다.";
            return $RS;
        }

        $input_params = $request->input();
        $curr_send_cust_info_no = [];
        foreach($request->listChk as $no)
        {
            if($ups_erp=='ERP')
            {
                // 만기일도래알림은 고객통합을 사용하지 않음.
                if($request->batch_sms_div=='66')
                {
                    $input_params['sms_customer'] = 'N';
                }

                $loan_info = Sms::getLoanInfo($no);
                if( !is_object($loan_info) )
                {
                    continue;
                }
                
                // 03.연체대상, 11.완납 : 정상채권은 발송을 할 수 없게한다.
                if($request->batch_sms_div=='03' || $request->batch_sms_div=='11')
                {
                    // 정상
                    if($loan_info->status=='A')
                    {
                        continue;
                    }
                }

                // 중복제외 되어 있으면, 밑에 메세지 가져올때도 점검하지만 미리보기에 보이는게 찝찝하니 여기서도 빼주자
                // 고객통합으로 다계약 고객이 한번에 발송처리할때 중복될수도 있으니 빼주자. 다시 보낼수도 있으니까 no_div_dup 으로 강제전환은 오바인듯. 담당자가 체크하고 하도록 설명
                if( Func::nvl($input_params['sms_customer'])=="Y" || Func::nvl($input_params['no_div_dup'])=="Y" )
                {
                    if( in_array($loan_info->cust_info_no, $curr_send_cust_info_no) )
                    {
                        continue;
                    }
                    $curr_send_cust_info_no[] = $loan_info->cust_info_no;
                }
            }

            $parser_msg = Sms::msgParser($no, $msg, $ups_erp, null, $request->batch_sms_div, $input_params);

            if($ups_erp == 'UPS' && $parser_msg=='')
            {
                $RS['msg'][$no] = str_replace("\n", "<br>", "발송불가");
            }
            else
            {
                $RS['msg'][$no] = str_replace("\n", "<br>", $parser_msg);
            }


            // 추심착수안내 동시발송
            if( $ups_erp=="ERP" )
            {
                if( $request->batch_sms_div=="77" || $request->batch_sms_div=="58" || $request->batch_sms_div=="71" || $request->batch_sms_div=="72" )
                {
                    if( isset($request->sms_with_59) && $request->sms_with_59=="Y" )
                    {

                        $vmsg = DB::TABLE("SMS_MSG")->SELECT("MESSAGE")->WHERE("CODE_DIV","ERP")->WHERE("SMS_DIV","59")->WHERE("SAVE_STATUS","Y")->ORDERBY("NO", "DESC")->FIRST();
                        $vmsg = Func::chungDec(["SMS_MSG"], $vmsg);	// CHUNG DATABASE DECRYPT
                        $msg2 = $vmsg->message;

                        $parser_msg = Sms::msgParser($no, $msg2, $ups_erp, null, "59", $input_params);
                        $RS['msg'][$no].= "<br><br>".str_replace("\n", "<br>", $parser_msg);
                    }
                }
            }

        }
        

        if(isset($RS['msg']))
        {
            $RS['rs_code'] = "Y";
            $RS['rs_msg'] = "정상 처리 되었습니다.";
        }
        else
        {
            $RS['rs_code'] = "N";
            $RS['rs_msg'] = "처리중 오류가 발생했습니다.";
        }

        return $RS;
    }

    /**
	* SMS 발송
	*
	* @param  int $member_no = 회원번호
	* @return view
	*/
	public function lumpSmsAction(Request $request)
    {
        $_DATA = $request->all();
        $msg = $request->message;
        
        // 신청번호
        if($request->batchSmsDiv=='ups')
        {
            $keyCol = 'loan_app_no';
            $ups_erp = 'UPS';
        }
        // 계약번호로 넘어온다. cust_info_no를 가져와야함.
        else if($request->batchSmsDiv=='erp')
        {
            $keyCol = 'cust_info_no';
            $ups_erp = 'ERP';
        }
        else
        {
            $RS['rs_code'] = "N";
            $RS['rs_msg'] = "처리할 내역이 없습니다.";
            return $RS;
        }

        // 예약시간
        if( isset($_DATA['reserve']) && $_DATA['reserve']=="Y" )
        {
            if( isset($_DATA['rDate']) )
            {
                $_DATA['rDate'] = date("YmdHis", strtotime(($_DATA['rDate'])));
            }

            $arrayMsg['reserve_time'] = $_DATA['rDate'];
        }

        $input_params = $request->input();
        $curr_send_cust_info_no = [];

        $sendSucess = null;
        $sendFail = null;
        $banFail = null;
        foreach($request->listChk as $no)
        {
            // 고객번호와 전화번호를 가져온다.
            if($ups_erp=='ERP')
            {
                // 만기일도래알림은 고객통합을 사용하지 않음.
                if($request->batch_sms_div=='66')
                {
                    $input_params['sms_customer'] = 'N';
                }

                $loan_info      = Sms::getLoanInfo($no);
                if( !is_object($loan_info) )
                {
                    continue;
                }

                // 03.연체대상, 11.완납 : 정상채권은 발송을 할 수 없게한다.
                if($request->batch_sms_div=='03' || $request->batch_sms_div=='11')
                {
                    // 정상
                    if($loan_info->status=='A')
                    {
                        continue;
                    }
                }

                $receiver       = $loan_info->ph2;
                $ssn            = $loan_info->ssn;
                $keyColNo       = $loan_info->cust_info_no;


                // 중복제외 되어 있으면, 밑에 메세지 가져올때도 점검하지만 미리보기에 보이는게 찝찝하니 여기서도 빼주자
                // 고객통합으로 다계약 고객이 한번에 발송처리할때 중복될수도 있으니 빼주자. 다시 보낼수도 있으니까 no_div_dup 으로 강제전환은 오바인듯. 담당자가 체크하고 하도록 설명
                if( Func::nvl($input_params['sms_customer'])=="Y" || Func::nvl($input_params['no_div_dup'])=="Y" )
                {
                    if( in_array($loan_info->cust_info_no, $curr_send_cust_info_no) )
                    {
                        continue;
                    }
                    $curr_send_cust_info_no[] = $loan_info->cust_info_no;
                }


                // 문자금지항목 체크
                $banInfo = Func::getBanInfo($loan_info->cust_info_no, "Y", "Y");
                if( $banInfo['ban_sms'] == 'Y' || ( $banInfo['ban_anne'] == 'Y' && Func::nvl2($request->batch_sms_div)!="26" ) )  // 완납확인 문자는 안내금지에서 제외
                {
                    $banFail[] = $no;
                    continue;
                }
                /*
                else if (isset($loan_info->ban_sms_div) && !empty($loan_info->ban_sms_div))
                {
                    $banDiv = explode(',', $loan_info->ban_sms_div);
                    if (in_array($_DATA['batch_sms_div'], $banDiv)) {
                        $banFail[] = $no;
                        continue;
                    }
                }
                */
            }
            else
            {
                $loan_app       = Sms::getLoanAppInfo($no);
                $receiver       = $loan_app->ph2;
                $ssn            = $loan_app->ssn;
                $keyColNo       = $no;
            }

            // 개별문자 구분
            $input_params['lumpYn'] = 'Y';

            $parser_msg = Sms::msgParser($no, $msg, $ups_erp, null, $request->batch_sms_div, $input_params);

            // 메시지가 없으면 보내지 않는다. 
            if($ups_erp == 'UPS' && $parser_msg=='')
            {
                $sendFail[] = $no;
                continue;
            }

            $arrayMsg['ups_erp']          = $ups_erp;                   // 대출 / 회수 구분
            $arrayMsg[$keyCol]            = $keyColNo;                  // 신청 / 고객원장 번호
            $arrayMsg['sms_div']          = $_DATA['batch_sms_div'];    // 문자 발송 구분
            $arrayMsg['message']          = $parser_msg;                // 메세지
            $arrayMsg['sender']           = $_DATA['sender'];           // 보내는이번호
            $arrayMsg['receiver']         = $receiver;                  // 받는이번호
            $arrayMsg['ssn']              = $ssn;                       // 주민번호
            $arrayMsg['lumpYn']           = 'Y';                        // 일괄발송여부

            //문자발송
            $smsReturn = Sms::smsSend($arrayMsg);





            // 추심착수안내 동시발송 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
            if( $ups_erp=="ERP" )
            {
                if( $request->batch_sms_div=="77" || $request->batch_sms_div=="58" || $request->batch_sms_div=="71" || $request->batch_sms_div=="72" )
                {
                    if( isset($request->sms_with_59) && $request->sms_with_59=="Y" )
                    {

                        $vmsg = DB::TABLE("SMS_MSG")->SELECT("MESSAGE")->WHERE("CODE_DIV","ERP")->WHERE("SMS_DIV","59")->WHERE("SAVE_STATUS","Y")->ORDERBY("NO", "DESC")->FIRST();
                        $vmsg = Func::chungDec(["SMS_MSG"], $vmsg);	// CHUNG DATABASE DECRYPT
                        $msg2 = $vmsg->message;

                        $parser_msg = Sms::msgParser($no, $msg2, $ups_erp, null, "59", $input_params);
                        if( $parser_msg!="" )
                        {
                            $arrayMsg['sms_div']          = "59";
                            $arrayMsg['message']          = $parser_msg;                // 메세지
                
                            //문자발송
                            $smsReturn2 = Sms::smsSend($arrayMsg);
                        }
                    }
                }
            }
            // 추심착수안내 동시발송 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////




            if($smsReturn == 'Y')
            {
                $sendSucess[] = $no;

                // 개인정보 활용동의 대상 업데이트
                if($ups_erp=='UPS' && (strstr($msg, '[온라인신청번호]') || strstr($msg, '[신청번호]')))
                {
                    $rslt = DB::dataProcess('UPD', 'LOAN_APP', ['lms_agree_yn'=>'A'],['NO'=>$keyColNo]);
                }
            }
            else
            {
                $sendFail[] = $no;
            }
        }

        $rs_msg = "아래와 같이 문자가 발송되었습니다.\n실패건이 있는 경우에는 관리자에게 문의해주세요.\n\n";
        if($sendSucess!=null)
        {
            $rs_msg.= "[성공] ".count($sendSucess)."건\n";
        }

        if($sendFail!=null)
        {
            $rs_msg.= "[실패] ".count($sendFail)."건\n";
            $rs_msg.= "실패번호(".implode(', ', $sendFail).")\n";
            Log::debug("SMS발송실패 : ".$ups_erp."\n발송자 : ".Auth::id()."\n번호 : ".implode(', ', $sendFail)."\n메시지내용 : ".$msg);
        }

        if($banFail != null)
        {
            $rs_msg.= "[문자금지] ".count($banFail)."건\n";
            $rs_msg.= "금지번호(".implode(', ', $banFail).")\n";
        } 

        return $rs_msg;
    }
}