<?php
namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;
use App\Chung\Sms;
use Redirect;
use App\Chung\Paging;
use App\Chung\Vars;

class CustomerSmsController extends Controller
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
    * 고객정보창 SMS
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function custSms(Request $request)
    {
        $cust_info_no = $request->cust_info_no;
        $loan_info_no = $request->loan_info_no;

        $arrayConf = Func::getConfigArr();
        $arraySms = Func::getSmsMessage();

        // listName : 리스트 이름 (표시 x)
        $result['listName'] = 'custsms';
        // listAction : 리스트 url - ajax 요청주소 ( '/'.$request->path() )
        $result['listAction'] = '/'.$request->path();

        // 서류함(탭) 설정
        if(!$request->tabs) $request->tabs = 'A';	// 기본값 세팅

        // tabs : 탭 사용 여부 (Y, N)
        $result['tabs'] = 'N';

        // button : 버튼 추가 여부 (Y, N)
        $result['button'] = 'N';

        // searchDate : 일자검색 여부 (Y, N)
        $result['searchDate'] = 'Y';
        // searchDateNm : 검색 input name 값 - select 태그 name, text는 자동으로 뒤에 String이 붙음.
        $result['searchDateNm'][] = 'save_time';             
        // searchDatePair : 일자검색 시작날짜, 종료날짜 검색 여부 - 두번째 날짜 input은 name에 End가 붙는다.
        $result['searchDatePair'][] = 'Y';                  
        // searchDateNoBtn : 오늘, 이번주, 한달 버튼 여부 (N == 표시, Y == 미표시, YESTERDAY == 전날도 사용)
        $result['searchDateNoBtn'][] = 'Y';

        // searchType : select 검색 여부 (Y, N) [searchDetail과 다른 점은 input 입력하는 부분이 없다.]
        $result['searchType'] = 'Y';
        // searchTypeNm : select 태그 name 속성 값
        $result['searchTypeNm'][] = 'sms_div';
        $result['searchTypeTitle'][] = '발송구분';
        $result['searchTypeArray'][] = Func::getConfigArr('sms_erp_cd');

        // searchDetail : 검색 사용 여부 (Y, N)
        $result['searchDetail'] = 'N';

        // isModal : 모달창 사용여부 (Y, N)
        $result['isModal'] = 'N';

        // plusButton : 등록 버튼 추가 여부 (Y, N)
        $result['plusButton'] = 'N';

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $result['listTitle'] = Array
        (
            'save_time'           => Array('작업일시', 0, '15%', 'center', '', 'save_time'),
            'save_name'           => Array('작업자', 0, '12%', 'center', '', 'save_id'),
            'sms_div_name'        => Array('발송구분', 0, '20%', 'center', '', 'sms_div'),
            'message'             => Array('내용', 0, '53%', 'center', '', 'message'),
        );

        // listlimit : 한페이지 출력 건수
        $result['listlimit'] = "10";

        if(isset($request->loan_app_no))
        {
            $result['customer']['loan_app_no'] = $request->loan_app_no;
        }

        if(isset($request->loan_info_no))
        {
            $result['customer']['loan_info_no'] = $request->loan_info_no;
        }

        if(isset($request->cust_info_no))
        {
            $result['customer']['cust_info_no'] = $request->cust_info_no;
        }

        $cust_info = DB::TABLE("CUST_INFO")->leftJoin('CUST_INFO_EXTRA', 'CUST_INFO.NO', '=', 'CUST_INFO_EXTRA.CUST_INFO_NO')
                                    ->SELECT("PH21, PH22, PH23")
                                    ->WHERE('CUST_INFO.NO', $cust_info_no)
                                    ->WHERE('CUST_INFO.SAVE_STATUS', 'Y')
                                    ->FIRST();
        $cust_info = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA"], $cust_info);	// CHUNG DATABASE DECRYPT

        // 고객 휴대전화
        $ph_num = $cust_info->ph21.$cust_info->ph22.$cust_info->ph23;

        return view('erp.custSms')->with("cust_info_no", $cust_info_no)
                                  ->with("loan_info_no", $loan_info_no)
                                  ->with("arrayConf", $arrayConf)
                                  ->with("arraySms", $arraySms)
                                  ->with("result", $result)
                                  ->with("ph_num", $ph_num );
    }


     /**
    * 고객정보창 SMS 리스트
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function custSmsList(Request $request)
    {
        //$request->isDebug = true;

        // 메인쿼리
        $sms = DB::TABLE("SUBMIT_SMS_LOG")->SELECT("*")->WHERE("SAVE_STATUS","Y")->WHERE("UPS_ERP", "ERP")->WHERE("CUST_INFO_NO", $request->cust_info_no);
        
        // // 상세검색
        // if($request->searchDetail && $request->searchString)
        // {
        //     $sms = $sms->WHERE($request->searchDetail, 'like', $request->searchString.'%');
        // }

        if( $request->sms_div )
        {
            $sms = $sms->WHERE('sms_div', $request->sms_div);
        }

        // 날짜 검색
        if($request->searchDt)
        {
            if($request->searchDtString)
            {
                $sDate = str_replace('-', '', $request->searchDtString);
                $sms = $sms->WHERE($request->searchDt, '>=', $sDate);
            }
            if($request->searchDtStringEnd)
            {
                $eDate = str_replace('-', '', $request->searchDtStringEnd);
                $sms = $sms->WHERE($request->searchDt, '<=', $eDate);
            }
        }

        // 정렬
        if($request->listOrder)
        {
            $sms = $sms->ORDERBY($request->listOrder, $request->listOrderAsc);
        }
        else
        {
            $sms = $sms->ORDERBY('SUBMIT_SMS_LOG.SAVE_TIME', 'DESC');
        }
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($sms, $request->page, $request->listLimit, 10);

        // 결과
        $sms = $sms->get();
        $sms = Func::chungDec(["SUBMIT_SMS_LOG"], $sms);	// CHUNG DATABASE DECRYPT

        $arr_sms_div = Func::getConfigArr('sms_erp_cd');
        $arrManager  = Func::getUserList();
        $arrayRslt   = Func::getConfigArr('sms_result_cd');

        // 뷰단 데이터 정리.
        $cnt = 0;
        foreach ($sms as $v)
        {
            if(substr($v->sms_div, 0, 1) == 0)
            {
                $v->sms_div = (string)$v->sms_div;
            }

            $v->save_name     = isset($arrManager[$v->save_id]) ? Func::nvl($arrManager[$v->save_id]->name, $v->save_id) : $v->save_id ;
            $v->save_time     = date("y-m-d H:i:s", strtotime($v->save_time));
            $v->sms_div_name  = Func::getArrayName($arr_sms_div, $v->sms_div)."<br>".$v->receiver;

            // 이름밑에 결과 표시
            if( $v->send_result=="0" )
            {
                $v->save_name.= "<br><span class='text-success'>전송성공</span>";
            }
            else if( $v->send_result=="" )
            {
                $v->save_name.= "<br><span class='text-info'>결과대기</span>";
            }
            else
            {
                $v->save_name.= "<br><span class='text-danger'>".Func::nvl($arrayRslt[$v->send_result],'')."</span>";
            }

            // 예약문자표시
            if( $v->reserve_time!="" )
            {
                $v->message = "<i class='fas fa-clock text-info mr-2'></i>".Func::dateFormat($v->reserve_time)."<br>".$v->message;
            }

            $r['v'][] = $v;
            $cnt ++;
        }
        
        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path());

        $r['result'] = 1;
        $r['txt'] = $cnt;

        return json_encode($r);
    }

    /**
    * 고객정보창 SMS 구분값 별 문장
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function custSmsDiv(Request $request)
    {
        $msg = DB::TABLE("SMS_MSG")->SELECT("*")->WHERE("SAVE_STATUS","Y")->WHERE("SMS_DIV", $request->sms_div)->WHERE("CODE_DIV", 'ERP')->GET();
        $msg = Func::chungDec(["SMS_MSG"], $msg);	// CHUNG DATABASE DECRYPT

        $str = "<option value=''>직접입력</option>";

        foreach($msg as $v)
        {
            $str.= "<option value='".str_replace("'", "＇", $v->message)."' >".$v->message."</option>";
        }
        
        return $str;
    }

    /**
    * 고객정보창 SMS 미리보기
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function custSmsPreview(Request $request)
    {
        $input_params = $request->input();
        $cust_info_no = $request->cust_info_no;
        $loan_info_no = $request->loan_info_no;
        $msg          = $request->message;
        $sms_erp_div  = $request->sms_erp_div;

        // 만기일도래알림은 고객통합을 사용하지 않음.
        if($input_params['sms_erp_div']=='66')
        {
            $input_params['sms_customer'] = 'N';
        }

        $v = DB::TABLE("LOAN_INFO")->SELECT("no")->WHERE("SAVE_STATUS","Y")->WHERE("NO", $loan_info_no)->WHERE("CUST_INFO_NO", $cust_info_no)->FIRST();

        // 개별문자 구분
        $input_params['lumpYn'] = 'N';

        if(isset($v))
        {
            $parser_msg = Sms::msgParser($v->no, $msg, "ERP", null, $sms_erp_div, $input_params);

            if( $sms_erp_div=="77" || $sms_erp_div=="58" || $sms_erp_div=="71" || $sms_erp_div=="72" )
            {
                if( isset($request->sms_with_59) && $request->sms_with_59=="Y" )
                {

                    $vmsg = DB::TABLE("SMS_MSG")->SELECT("MESSAGE")->WHERE("CODE_DIV","ERP")->WHERE("SMS_DIV","59")->WHERE("SAVE_STATUS","Y")->ORDERBY("NO", "DESC")->FIRST();
                    $msg2 = $vmsg->message;

                    $parser_msg2 = Sms::msgParser($v->no, $msg2, "ERP", null, "59", $input_params);
                    $parser_msg.= "\n\n".$parser_msg2;
                }
            }
        }
        else
        {
            $parser_msg = "문자열 변환에 실패하였습니다.";
        }
           

        return view('erp.custSmsPreview')->with("msg", $parser_msg);
    }

    /**
	* 고객 SMS 발송
	*
	* @param  int $member_no = 고객번호
	* @return view
	*/
	public function custSmsAction(Request $request)
    {
        $_DATA = $request->all();
        $input_params = $request->input();

        // 만기일도래알림은 고객통합을 사용하지 않음.
        if($input_params['sms_erp_div']=='66')
        {
            $input_params['sms_customer'] = 'N';
        }

        // 개별문자 구분
        $input_params['lumpYn'] = 'N';

        // 기본쿼리
        $v = DB::TABLE("LOAN_INFO")->JOIN("CUST_INFO", "LOAN_INFO.CUST_INFO_NO", "=", "CUST_INFO.NO")
                                    ->SELECT("LOAN_INFO.NO, LOAN_INFO.CUST_INFO_NO", "CUST_INFO.SSN", "BAN_SMS", "BAN_SMS_DIV")
                                    ->WHERE('CUST_INFO.SAVE_STATUS','Y')
                                    ->WHERE('LOAN_INFO.SAVE_STATUS','Y')
                                    ->WHERE('LOAN_INFO.SAVE_STATUS','Y')
                                    ->WHERE("LOAN_INFO.NO", $_DATA['loan_info_no'])
                                    ->WHERE("LOAN_INFO.CUST_INFO_NO", $_DATA['cust_info_no'])
                                    ->FIRST();
        $v = Func::chungDec(["LOAN_INFO","CUST_INFO"], $v);	// CHUNG DATABASE DECRYPT

        if(isset($v))
        {
            
            $banInfo = Func::getBanInfo($v->cust_info_no, "Y", "N");
            if( $banInfo['ban_sms'] == 'Y' || ( $banInfo['ban_anne'] == 'Y' && $_DATA['sms_erp_div']!="26" ) )
            {
                return '문자금지 대상입니다.';
            }
            /*
            else if (isset($v->ban_sms_div) && !empty($v->ban_sms_div)) {
                $banDiv = explode(',', $v->ban_sms_div);
                if (in_array($_DATA['sms_erp_div'], $banDiv)) {
                    return '문자금지항목이 포함되어 있습니다.';
                }
            }
            */

            $parser_msg = Sms::msgParser($v->no, $_DATA['message'], "ERP", null, $_DATA['sms_erp_div'], $input_params);

            $arrayMsg['ups_erp']          = "ERP";
            $arrayMsg['cust_info_no']     = $v->cust_info_no;
            $arrayMsg['loan_info_no']     = $v->no;
            $arrayMsg['sms_div']          = $_DATA['sms_erp_div'];
            $arrayMsg['message']          = $parser_msg;
            $arrayMsg['sender']           = $_DATA['sender'];
            $arrayMsg['receiver']         = $_DATA['receiver'];
            $arrayMsg['ssn']              = $v->ssn;
            $arrayMsg['lumpYn']           = 'N';                        // 일괄발송여부

            // 예약시간
            if( isset($_DATA['reserve']) && $_DATA['reserve']=="Y" )
            {
                if( isset($_DATA['rDate']) )
                {
                    $_DATA['rDate'] = date("YmdHis", strtotime(($_DATA['rDate'])));
                }

                $arrayMsg['reserve_time'] = $_DATA['rDate'];
            }

            //문자발송
            $smsReturn = Sms::smsSend($arrayMsg);

            if($smsReturn == 'Y')
            {
                // 추심착수안내 동시발송 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                if( $_DATA['sms_erp_div']=="77" || $_DATA['sms_erp_div']=="58" || $_DATA['sms_erp_div']=="71" || $_DATA['sms_erp_div']=="72" )
                {
                    if( isset($request->sms_with_59) && $request->sms_with_59=="Y" )
                    {

                        $vmsg = DB::TABLE("SMS_MSG")->SELECT("MESSAGE")->WHERE("CODE_DIV","ERP")->WHERE("SMS_DIV","59")->WHERE("SAVE_STATUS","Y")->ORDERBY("NO", "DESC")->FIRST();
                        $vmsg = Func::chungDec(["SMS_MSG"], $vmsg);	// CHUNG DATABASE DECRYPT
                        
                        $msg2 = $vmsg->message;
                        $parser_msg = Sms::msgParser($v->no, $msg2, "ERP", null, "59", $input_params);
                    
                        if( $parser_msg!="" )
                        {
                            $arrayMsg['sms_div']          = "59";
                            $arrayMsg['message']          = $parser_msg;                // 메세지
                
                            //문자발송
                            $smsReturn2 = Sms::smsSend($arrayMsg);
                            
                            if($smsReturn2!='Y')
                            {
                                return "방문안내문자는 정상적으로 발송되었으나 추심안내문자는 오류가 발생했습니다.";
                            }
                        }
                    }
                }
                // 추심착수안내 동시발송 //////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////////
                
                $result = '문자가 정상적으로 발송되었습니다.';
            }
            else
            {
                $result = '문자발송에 실패하였습니다.';
            }
        }
        else
        {
            $result = '문자열 변환에 실패하였습니다.';
        }
        
        return $result;
    }




}

?>