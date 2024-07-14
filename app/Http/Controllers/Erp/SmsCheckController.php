<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Log;
use Func;
use Vars;
use Auth;
use DataList;
use App\Chung\Paging;
use App\Chung\Sms;

class SmsCheckController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //
    }

    /**
     * SMS관리 메인화면
     *
     * @param  Void
     * @return view
     */
	public function smsCheckForm(Request $request,$check_no)
    {
        $array_user_id = Func::getUserId();
        $edit = false;
        $check         = DB::table('SMS_CHECK')->SELECT('*')->WHERE('SAVE_STATUS', 'Y')->WHERE("NO",$check_no)->FIRST();
        $check = Func::chungDec(["SMS_CHECK"], $check);	// CHUNG DATABASE DECRYPT

        if(isset($check)){
            $check->send_date = substr($check->send_time,0,6);
            $check->send_hour = substr($check->send_time,6,2);
            $loan_li          = Array();
            if($check->ups_erp=="ERP"){
                $getStatus = Vars::$arrayContractStaColor;
                $rslt = DB::table('LOAN_INFO')->JOIN("CUST_INFO", "LOAN_INFO.CUST_INFO_NO", "=", "CUST_INFO.NO")->JOIN("CUST_INFO_EXTRA", "CUST_INFO.NO", "=", "CUST_INFO_EXTRA.CUST_INFO_NO")
                ->SELECT("LOAN_INFO.NO,LOAN_INFO.STATUS","CUST_INFO.NAME", "CUST_INFO.SSN", "CUST_INFO_EXTRA.PH21", "CUST_INFO_EXTRA.PH22", "CUST_INFO_EXTRA.PH23")
                ->WHERE('CUST_INFO.SAVE_STATUS','Y')
                ->WHERE('LOAN_INFO.SAVE_STATUS','Y')
                ->WHEREIN("LOAN_INFO.NO",explode(",",$check->loan_nos))
                ->GET();
                $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","CUST_INFO_EXTRA",], $rslt);	// CHUNG DATABASE DECRYPT
                $check->ups_erp_str = "회수"; 
            } 
            else{
                $getStatus = Vars::$arrayLoanAppStatusColor;
                $rslt = DB::TABLE('LOAN_APP')->Join('LOAN_APP_EXTRA', 'LOAN_APP.NO', '=', 'LOAN_APP_EXTRA.LOAN_APP_NO')
                ->SELECT("LOAN_APP.NO,LOAN_APP.STATUS","LOAN_APP.NAME", "LOAN_APP.SSN", "LOAN_APP_EXTRA.PH21", "LOAN_APP_EXTRA.PH22", "LOAN_APP_EXTRA.PH23")
                ->WHERE('LOAN_APP.SAVE_STATUS','Y')
                ->WHEREIN("LOAN_APP.NO",explode(",",$check->loan_nos))
                ->GET();
                $rslt = Func::chungDec(["LOAN_APP","LOAN_APP_EXTRA"], $rslt);	// CHUNG DATABASE DECRYPT
                $check->ups_erp_str = "대출";
            }  
            
            foreach($rslt as $v){
                $v->ph2 = Func::phFormat($v->ph21,$v->ph22,$v->ph23);
                $v->ssn = substr($v->ssn, 0, 6);
                $v->status = Func::getArrayName($getStatus, $v->status);
                $v->message  = Sms::msgParser($v->no, $check->message, $check->ups_erp);
                $loan_li[] = $v;
            }
            if( $check->status=='A' && $check->req_id==Auth::id() ) //권한
            {
                $edit = true;
            }
        }
        return view("erp.smsCheckForm")->with("loan_li", $loan_li)->with("result", $check)->with("array_user_id", $array_user_id)->with("edit", $edit);
    }

    /**
     * SMS관리 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function smsCheckAction(Request $request)
    {
        $param = $request->input();
        $_DATA['save_time']   = date("YmdHis");
        $_DATA['save_id']     = Auth::id();
        Log::alert($param);
        if($param['action_mode']=="CONFIRM"){
            $check                    = DB::table('SMS_CHECK')->SELECT('*')->WHERE('SAVE_STATUS', 'Y')->WHERE("NO",$request->sms_check_no)->FIRST();
            $check = Func::chungDec(["SMS_CHECK"], $check);	// CHUNG DATABASE DECRYPT
            
            $_DATA['confirm_date']    = date("Ymd");
            $_DATA['confirm_id']      = Auth::id();
            $_DATA['status']     = "Y";
            
            $sendSucess = null;
            $sendFail = null;   
            //SMS 발송
            foreach($check->loan_nos as $no)
            {
                unset($arrayMsg);
                // 고객번호와 전화번호를 가져온다.
                if($check->ups_erp=='ERP')
                {
                    $loan_info      = Sms::getLoanInfo($no);
                    $receiver       = $loan_info->ph2;
                    $ssn            = $loan_info->ssn;
                    $keyColNo       = $loan_info->cust_info_no;
                }
                else
                {
                    $loan_app       = Sms::getLoanAppInfo($no);
                    $receiver       = $loan_app->ph2;
                    $ssn            = $loan_app->ssn;
                    $keyColNo       = $no;
                }
        
                $parser_msg = Sms::msgParser($no, $check->msg, $check->ups_erp);
            
                $arrayMsg['ups_erp']          = $check->ups_erp;                   // 대출 / 회수 구분
                $arrayMsg[$keyCol]            = $keyColNo;                  // 신청 / 고객원장 번호
                $arrayMsg['message']          = $parser_msg;                // 메세지
                $arrayMsg['sender']           = $check->sender;           // 보내는이번호
                $arrayMsg['receiver']         = $receiver;                  // 받는이번호
                $arrayMsg['ssn']              = $ssn;                       // 주민번호
                $arrayMsg['reserve_time']     = $check->send_time;
                $arrayMsg['lumpYn']           = 'Y';                        // 일괄발송여부
                //문자발송
                $smsReturn = Sms::smsSend($arrayMsg);

                if($smsReturn == 'Y')
                {
                    $sendSucess[] = $no;
                }
            }
            $_DATA['suc_nos']  = implode(",",$sendSucess);
        }else if($param['action_mode']=="DELETE"){
            $_DATA['confirm_date']    = date("Ymd");
            $_DATA['confirm_id']      = Auth::id();
            $_DATA['status']     = "N";
        }else{
            $_DATA['message']   = $param['message'];
            $_DATA['sender']    = $param['sender'];
            $_DATA['loan_nos']  = implode(",",$param['listChk']);
            $_DATA['multpl_yn'] = isset($param['multpl_yn']) ? $param['multpl_yn']:"N";
            $_DATA['coll_yn']   = isset($param['coll_yn']) ? $param['coll_yn']:"N";
            $_DATA['send_time'] = str_replace("-","",$param['send_date']).$param['send_hour'].'0000';
        }
       
        $RS['rs_code'] = DB::dataProcess('UPD', 'SMS_CHECK', $_DATA, ["NO"=>$param['sms_check_no']]);
        Log::alert($RS);
        if( $RS['rs_code']=="Y" )
        {
            $RS['rs_msg'] = "처리가 완료되었습니다.";
        }
        else
        {
            $RS['rs_msg'] = "처리에 실패하였습니다.";
        }
        return json_encode($RS);
    }

    
    
    /**
     * sms 결재``` 등록
     *
     * @param  \Illuminate\Http\Request  $request
     * @return json
     */
	public static function setSmsCheck(Request $request)
    {
        $_DATA                = $request->all();
        $_DATA['save_status'] = "Y";
        $_DATA['save_time']   = date("YmdHis");
        $_DATA['save_id']     = Auth::id();
        $_DATA['req_date']    = date("Ymd");
        $_DATA['req_id']      = Auth::id();
        $_DATA['status']      = "A";
        // 신청번호
        if($request->batchSmsDiv=='ups')
        {
            $_DATA['ups_erp'] = 'UPS';
        }
        // 계약번호로 넘어온다. cust_info_no를 가져와야함.
        else if($request->batchSmsDiv=='erp')
        {
            $_DATA['ups_erp'] = 'ERP';
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

            $_DATA['send_time'] = $_DATA['rDate'];
        }else{
            $_DATA['send_time'] = date("Ymd");
        }
        $_DATA['loan_nos'] = implode(",",$request->listChk);
       
        $rslt =  DB::dataProcess('INS', 'SMS_CHECK',$_DATA); 

        if(isset($rslt) && $rslt=="Y")
        {
            return count($request->listChk);
        }
        else
        {
            return null;
        }
    }


    /**
     * 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataList(Request $request){

        $list   = new DataList(Array("listName"=>"smscheck","listAction"=>'/'.$request->path()));

        if(!isset($request->tabs)) $request->tabs = 'A';
 
        $list->setTabs(Array('A'=>'접수', 'Y'=>'결재', 'N'=>'취소'),$request->tabs); 
        $list->setSearchDate('날짜검색',Array('req_date' => '요청일','send_date'=>'발송일','confirm_date'=>'결재일'),'searchDt','Y');
        $list->setSearchDetail(Array( 
            'loan_nos.loan_info_no'  => '계약번호',
            'sender'  => '발신번호',
        ));    
        return $list;
    }

     /**
     * 문자발송내역 리스트 메인화면
     *
     * @param  Void
     * @return view
     */
	public function smsCheck(Request $request)
    {
        $list         = $this->setDataList($request);

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $list->setlistTitleCommon(Array
        (
            'req_date'          => Array('요청일', 0, '6%', 'center', '', 'req_date'),
            'req_id'            => Array('요청직원', 0, '6%', 'center', '', 'req_id'),
            'ups_erp'           => Array('구분', 0, '6%', 'center', '', 'ups_erp'),
            'message'           => Array('메세지', 0, '', 'center', '', 'message'),
            'send_time'         => Array('발송일', 0, '8%', 'center', '', 'send_time'),
            'loan_nos'          => Array('발송대상', 0, '8%', 'center', '', ''),
            'sender'            => Array('발신번호', 0, '10%', 'center', '', 'sender'),
        ));
        $list->setlistTitleTabs('Y',Array
        (
            'confirm_date'      => Array('결재일', 0, '6%', 'center', '', 'confirm_date'),
            'confirm_id'        => Array('결재자', 0, '6%', 'center', '', 'confirm_id'),
        ));
        $list->setlistTitleTabs('N',Array
        (
            'confirm_date'      => Array('취소일', 0, '6%', 'center', '', 'confirm_date'),
            'confirm_id'        => Array('취소결재자', 0, '6%', 'center', '', 'confirm_id'),
        ));

        return view('erp.smsCheck')->with('result', $list->getList());
        
    }

    /**
    * 문자발송내역 리스트 (ajax부분화면)
    *
    * @param  \Illuminate\Http\Request  $request
    * @return Json $r
    */
    public function smsCheckList(Request $request)
    {
        $list   = $this->setDataList($request);
        $param  = $request->all();
        $arrayName = Func::getUserId('');
        $arrayUpsErp = Array('UPS' => '대출', 'ERP' => '회수');
        // 기본쿼리
        $SMS = DB::TABLE("SMS_CHECK")->SELECT("*")->WHERE("SAVE_STATUS", "Y");

        $param['tabSelectNm']   = "STATUS";

        $SMS = $list->getListQuery("SMS_CHECK",'main',$SMS,$param);

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($SMS, $request->page, $request->listLimit, 10, $request->listName);

        $rslt = $SMS->GET();
        $rslt = Func::chungDec(["SMS_CHECK"], $rslt);	// CHUNG DATABASE DECRYPT

        $cnt = 0;
        foreach ($rslt as $v)
        {
            $v->onclick              =  'getPopUp("/erp/smscheckform/'.$v->no.'","smscheck","width=1000, height=800, scrollbars=yes")';
            $v->line_style           = 'cursor: pointer;';
            $v->ups_erp             = Func::getArrayName($arrayUpsErp, $v->ups_erp);
            $v->req_id              = Func::getArrayName($arrayName, $v->req_id);
            $v->confirm_id          = Func::getArrayName($arrayName, $v->confirm_id);
            $v->req_date            = Func::dateFormat($v->req_date);
            $v->send_time           = Func::dateFormat($v->send_time);
            $v->confirm_date        = Func::dateFormat($v->confirm_date);
            $tmp                    = explode(",",trim($v->loan_nos));
            if($tmp>1){
                $v->loan_nos            = $tmp[0]." 외 ".count($tmp)."개";
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
   

}
 