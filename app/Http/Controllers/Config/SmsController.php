<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Log;
use Func;
use Auth;
use DataList;
use Excel;
use ExcelFunc;
use FastExcel;
use App\Chung\Paging;
use Illuminate\Support\Facades\Storage;

class SmsController extends Controller
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
	public function sms()
    {
        $arrayConf = Func::getConfigArr();
        $result = [];
        $rslt = DB::table('sms_msg')->SELECT('no', 'sms_div','sms_type', 'code_div', 'message')->WHERE('save_status', 'Y')->ORDERBY('sms_div')->ORDERBY('no')->GET();
        $rslt = Func::chungDec(["SMS_MSG"], $rslt);	// CHUNG DATABASE DECRYPT

        foreach($rslt as $v){

            $result[$v->sms_type][] = $v;
        }
        
        return view("config.sms")->with("arrayConf", $arrayConf)->with("result", $result);
    }

    /**
     * SMS관리 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function smsAction(Request $request)
    {
        $param = $request->input();
        $rslt = true;

        // 1. NO 값으로 DB message 랑 비교하여 다를 경우 UPDATE
        // 2. 입력받은 message 값이 없을경우 해당 메세지 save_status = 'N' 으로 수정
        // 3. + 버튼으로 추가된 내용들은 INSERT
        foreach( $param as $key => $arr)
        {
            if( substr($key, 0, 8)=="message_" && is_array($arr) )
            {
                // sms_div 값 추출
                $sms_type = substr($key, 8);
               
                foreach( $arr as $no => $msg)
                {
                    $sms_div  = $param['sms_div'][$no];
                    // 기존배열 index가 no 값인지 체크
                    if(is_int($no))
                    {
                        $SMS = DB::table('sms_msg')->SELECT('*')->WHERE('save_status', 'Y')->WHERE('no', $no);

                        $list_chk = $SMS->exists();

                        if($list_chk)
                        {
                            $sel = $SMS->FIRST();
                            $sel = Func::chungDec(["SMS_MSG"], $sel);	// CHUNG DATABASE DECRYPT

                            unset($UP);
                            if($msg != $sel->message)
                            {
                                // 문장이 공백일 경우 해당 문자설정 삭제
                                if($msg === '' || $msg == NULL)
                                {
                                    $UP['save_status'] = 'N';
                                    $UP['del_id'] = Auth::user()->id;
                                    $UP['del_time'] = date('YmdHis');
                                }
                                // 공백이 아닐경우 변경된 문장으로 설정
                                else
                                {
                                    $UP['message'] = $msg; 
                                }
                            }
                            if($sms_div != $sel->sms_div) $UP['sms_div']     = $sms_div;
                            if(!isset($UP))
                            {   
                                continue;
                            }

                            $rslt = DB::dataProcess('UPD', 'sms_msg', $UP, ['no'=>$no]);  
                        }  
                    }
                    // 배열 index 가 'add' 인 경우 '+' 버튼으로 추가된 문장
                    elseif(substr($no, 0, 3) === 'add')
                    {
                        if(isset($msg))
                        {
                            $params['code_div']    = $param['code_div'];
                            $params['sms_div']     = $sms_div;
                            $params['sms_type']    = $sms_type;
                            $params['message']     = $msg;
                            $params['worker_id']   = Auth::user()->id;
                            $params['save_status'] = 'Y';
                            $params['save_time']   = date('YmdHis');
                            
                            $rslt = DB::dataProcess('INS', 'sms_msg', $params);  
                        }
                    }
                }
            }
        }
        
        if($rslt)
        {
            $rs_msg = '저장이 완료되었습니다.';
        }
        else
        {
            $rs_msg = '처리에 실패하였습니다.';
        }

        return $rs_msg;
    }

    /**
     * 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataList(Request $request){

        $list   = new DataList(Array("listName"=>"sms","listAction"=>'/'.$request->path()));

        $list->setCheckBox("no");

        if(!isset($request->tabs)) $request->tabs = 'ALL';
 
        $list->setTabs(Array('ALL'=>'전체', 'ERP'=>'회수'),$request->tabs);

        if( Func::funcCheckPermit("E004") || Func::funcCheckPermit("E031") )
        {
            $list->setSearchType('manager_code', Func::myPermitBranch(), '관리지점');
        }
        $list->setSearchDate('날짜검색',Array('save_time' => '등록일', 'reserve_time' => '예약일'),'searchDt','Y', 'N', date("Y-m-d"), date("Y-m-d"), 'save_time');

        if(Func::funcCheckPermit("S011"))
        {
            $list->setLumpForm('DEL', Array('BTN_NAME'=>'삭제처리','BTN_ACTION'=>'lump_del(this)','BTN_ICON'=>'','BTN_COLOR'=>''));
        }

        $list->setSearchDetail(Array( 
            'sender'   => '발신번호',
            'receiver' => '수신번호',
            //'save_id'  => '등록사번',
        ));

        if( Func::funcCheckPermit("C122") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/config/smshistoryexcel', 'form_sms')", "btn-success");
        }

        return $list;
    }

     /**
     * 문자발송내역 리스트 메인화면
     *
     * @param  Void
     * @return view
     */
	public function smsHistory(Request $request)
    {
        $array_branch = Func::getBranchList();
        $list         = $this->setDataList($request);
        
        $list->setButtonArray("발송제한", "getPopUp('/config/smslimit','smslimit','width=460, height=690')", "btn-secondary");

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $list->setlistTitleTabs('ALL',Array
        (
            'ups_erp'          => Array('구분', 0, '6%', 'center', '', 'ups_erp'),
            'save_id'          => Array('등록직원', 0, '6%', 'center', '', 'save_id'),
            'sender'           => Array('발신번호', 0, '8%', 'center', '', 'sender'),
            'receiver'         => Array('수신번호', 0, '8%', 'center', '', 'receiver'),
            'message'          => Array('발송내용', 0, '45%', 'center', '', 'message'),
            'save_time'        => Array('등록시간', 0, '10%', 'center', '', 'save_time'),
            'reserve_time'     => Array('예약시간', 0, '10%; padding-right:24px', 'center', '', 'reserve_time'),
            'sms_result'       => Array('발송결과', 0, '8%', 'center', '', 'send_result'),
        ));

        $list->setlistTitleTabs('UPS',Array
        (
            'save_id'          => Array('등록직원', 0, '5%', 'center', '', 'save_id'),          
            'sender'           => Array('발신번호', 0, '7%', 'center', '', 'sender'),
            'loan_app_no'      => Array('신청번호', 0, '5%', 'center', '', 'sender'),
            'name'             => Array('이름', 0, '5%', 'center', '', 'sender'),
            'receiver'         => Array('수신번호', 0, '7%', 'center', '', 'receiver'),
            'message'          => Array('발송내용', 0, '45%', 'center', '', 'message'),
            'save_time'        => Array('등록시간', 0, '10%', 'center', '', 'save_time'),
            'reserve_time'     => Array('예약시간', 0, '10%; padding-right:24px', 'center', '', 'reserve_time'),
            'sms_result'       => Array('발송결과', 0, '8%', 'center', '', 'send_result'),
        ));

        $list->setlistTitleTabs('ERP',Array
        (
            'save_id'          => Array('등록직원', 0, '5%', 'center', '', 'save_id'),
            'sender'           => Array('발신번호', 0, '7%', 'center', '', 'sender'),
            'cust_info_no'     => Array('고객번호', 0, '5%', 'center', '', 'sender'),
            'name'             => Array('이름', 0, '5%', 'center', '', 'sender'),
            'receiver'         => Array('수신번호', 0, '7%', 'center', '', 'receiver'),
            'message'          => Array('발송내용', 0, '45%', 'center', '', 'message'),
            'save_time'        => Array('등록시간', 0, '10%', 'center', '', 'save_time'),
            'reserve_time'     => Array('예약시간', 0, '10%', 'center', '', 'reserve_time'),
            'sms_result'       => Array('발송결과', 0, '8%', 'center', '', 'send_result'),
        ));

        return view('config.smsHistory')->with('result', $list->getList())->with("array_branch", $array_branch);;
        
    }

    /**
    * 문자발송내역 리스트 (ajax부분화면)
    *
    * @param  \Illuminate\Http\Request  $request
    * @return Json $r
    */
    public function smsHistoryList(Request $request)
    {
        $list   = $this->setDataList($request);
        $param  = $request->all();
        //$request->isDebug = true;

        // 기본쿼리
        $SMS = DB::TABLE("submit_sms_log")->SELECT("*")->WHERE("save_status", "Y");

        // 'UPS'=>'대출', 'ERP'=>'회수'
		if( $request->tabsSelect=="ERP" )
		{
            $param['tabSelectNm']   = "UPS_ERP";
            $param['tabsSelect']    = $request->tabsSelect;
        }


        // 전지점 조회권한 없으면 자기 지점만
        if( !Func::funcCheckPermit("E004") )
        {
            $SMS->WHERERAW("cust_info_no in ( select cust_info_no from loan_info where manager_code in ('".implode("','", array_keys(Func::myPermitBranch()))."') )");
        }
        if( isset($param['manager_code']) && $param['manager_code']!="" )
        {
            $SMS->WHERERAW("cust_info_no in ( select cust_info_no from loan_info where manager_code='".$param['manager_code']."' )");
            unset($param['manager_code']);
        }


        $SMS = $list->getListQuery("submit_sms_log",'main',$SMS,$param);


        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($SMS, $request->page, $request->listLimit, 10, $request->listName);
        $rslt   = $SMS->GET();
        $rslt = Func::chungDec(["SUBMIT_SMS_LOG"], $rslt);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리
        $arrayUpsErp = Array('ERP'=>'회수');
        $arrayName   = Func::getUserId('');
        $arrayRslt   = Func::getConfigArr('sms_result_cd');

        $cnt = 0;
        foreach( $rslt as $v )
        {
            if( $v->ups_erp == 'UPS' )
            {
                $ups = DB::TABLE("loan_app")->SELECT("name")->WHERE("no", $v->loan_app_no)->FIRST();
                $ups = Func::chungDec(["LOAN_APP"], $ups);	// CHUNG DATABASE DECRYPT

                if(isset($ups->name))
                {
                    $v->name = $ups->name;
                }
            }
            else if( $v->ups_erp == 'ERP' )
            {
                if( isset($v->cust_info_no) && $v->cust_info_no>0 )
                {
                    $erp = DB::TABLE("cust_info")->SELECT("name")->WHERE("no", $v->cust_info_no)->FIRST();
                    $erp = Func::chungDec(["CUST_INFO"], $erp);	// CHUNG DATABASE DECRYPT
                    
                    $v->name = $erp->name;
                    $v->name = '<a onclick="loan_info_pop( '.$v->cust_info_no.', 0 );" style="cursor: pointer;" class="text-primary">'.$v->name.'</a>';
                }
            }

            $v->ups_erp           = Func::getArrayName($arrayUpsErp, $v->ups_erp);
            $v->save_id           = Func::getArrayName($arrayName, $v->save_id);
            $v->save_time         = Func::dateFormat($v->save_time);
            $v->reserve_time      = Func::dateFormat($v->reserve_time) ? Func::dateFormat($v->reserve_time) : "<i class='fas fa-times'></i>";
            $v->sms_result        = Func::nvl($arrayRslt[$v->send_result], $v->send_result);
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
    * 문자발송내역 엑셀 다운로드
    *
    * @param  \Illuminate\Http\Request  $request
    * @return Json $r
    */
    public function smsHistoryExcel(Request $request)
    {
        if( !Func::funcCheckPermit("C122") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }

        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $list           = $this->setDataList($request);
        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;


        // 기본쿼리
        $SMS = DB::TABLE("submit_sms_log")->SELECT("*")->WHERE("save_status", "Y");

        // 'UPS'=>'대출', 'ERP'=>'회수'
		if( $request->tabsSelect=="ERP" )
		{
            $param['tabSelectNm']   = "UPS_ERP";
            $param['tabsSelect']    = $request->tabsSelect;
        }


        // 전지점 조회권한 없으면 자기 지점만
        if( !Func::funcCheckPermit("E004") )
        {
            $SMS->WHERERAW("cust_info_no in ( select cust_info_no from loan_info where manager_code in ('".implode("','", array_keys(Func::myPermitBranch()))."') )");
        }
        if( isset($param['manager_code']) && $param['manager_code']!="" )
        {
            $SMS->WHERERAW("cust_info_no in ( select cust_info_no from loan_info where manager_code='".$param['manager_code']."' )");
            unset($param['manager_code']);
        }

        $SMS = $list->getListQuery("submit_sms_log",'main',$SMS,$param);

       // 현재 페이지 출력 
       if( $down_div=='now' )
       {
           // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
           $paging = new Paging($SMS, $request->nowPage, $request->listLimit, 10, $request->listName);
       }

       // 엑셀다운 로그 시작
       $record_count = 0;
       $query        = Func::printQuery($SMS);
       
       $file_name    = "문자발송내역_".date("YmdHis").'_'.Auth::id().'.xlsx';
       $request_all  = $request->all();
       $request_all['class'] = __CLASS__;
       $all_data     = json_encode($request_all, true);

       if(!empty($request->excel_no)){
           $file_name = $request->file_name;
           $excel_no = $request->excel_no;
           ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
           $excel_down_div = 'A';
       } else {
           $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $query, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
           if($excel_down_div == 'S')
           {
               $yet['result']  = 'Y';
               return $yet;
           }
       }

        $rslt   = $SMS->GET();
        $rslt = Func::chungDec(["SUBMIT_SMS_LOG"], $rslt);	// CHUNG DATABASE DECRYPT

        // 엑셀헤더
        if( $request->tabsSelect=="ERP" )
        {
            $excel_header = array('구분', '등록직원', '발신번호', '고객번호', '이름', '수신번호', '발송내용', '등록시간', '예약시간', '발송결과');
        }
        else
        {
            $excel_header = array('구분', '등록직원', '발신번호', '수신번호', '발송내용', '등록시간', '예약시간', '발송결과');
        }
        $excel_data   = [];

        // 뷰단 데이터 정리
        $arrayUpsErp = Array('ERP'=>'회수');
        $arrayName   = Func::getUserId('');
        $arrayRslt   = Func::getConfigArr('sms_result_cd');

        foreach( $rslt as $v )
        {
            if( $v->ups_erp == 'UPS' )
            {
                $ups = DB::TABLE("loan_app")->SELECT("name")->WHERE("no", $v->loan_app_no)->FIRST();
                $ups = Func::chungDec(["LOAN_APP"], $ups);	// CHUNG DATABASE DECRYPT

                if(isset($ups->name))
                {
                    $v->name = $ups->name;
                }
            }
            else if( $v->ups_erp == 'ERP' )
            {
                if( isset($v->cust_info_no) && $v->cust_info_no>0 )
                {
                    $erp = DB::TABLE("cust_info")->SELECT("name")->WHERE("no", $v->cust_info_no)->FIRST();
                    $erp = Func::chungDec(["CUST_INFO"], $erp);	// CHUNG DATABASE DECRYPT
                    
                    $v->name = $erp->name;
                }
            }

            if( $request->tabsSelect=="ERP" )
            {
                $array_data = Array(
                    Func::getArrayName($arrayUpsErp, $v->ups_erp),
                    Func::getArrayName($arrayName, $v->save_id),
                    $v->sender,
                    Func::addCi($v->cust_info_no), 
                    $v->name, 
                    $v->receiver,
                    $v->message,
                    Func::dateFormat($v->save_time),
                    !empty($v->reserve_time) ? Func::dateFormat($v->reserve_time) : "X",
                    Func::nvl($arrayRslt[$v->send_result], $v->send_result)
                );
            }
            else 
            {
                $array_data = Array(
                    Func::getArrayName($arrayUpsErp, $v->ups_erp),
                    Func::getArrayName($arrayName, $v->save_id),
                    $v->sender,
                    $v->receiver,
                    $v->message,
                    Func::dateFormat($v->save_time),
                    !empty($v->reserve_time) ? Func::dateFormat($v->reserve_time) : "X",
                    Func::nvl($arrayRslt[$v->send_result], $v->send_result)
                );
            }

            $record_count++;
            $excel_data[] = $array_data;
        }

        // 엑셀 익스포트
        ExcelFunc::fastexcelExport($excel_data,$excel_header,$file_name);

        // 파일 저장 여부 확인
        $exists = Storage::disk('excel')->exists($file_name);   

        if( isset($exists) )
        {
            $array_result['etc']             = $etc;
            $array_result['result']          = 'Y';
            $array_result['filename']        = $file_name;
            $array_result['excel_no']        = $excel_no;
            $array_result['record_count']    = $record_count;
            $array_result['down_filename']   = $down_filename;
            $array_result['excel_down_div']  = $excel_down_div;
            
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
        }
        else
        {
            $array_result['result']    = 'N';
            $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }
        return $array_result;
    }
   
    /**
    * 문자발송내역 삭제
    *
    * @param  \Illuminate\Http\Request  $request
    * @return Json $r
    */
    public function smsHistoryAction(Request $request)
    {
        if(!Func::funcCheckPermit("S011"))
        {
            return "삭제처리 권한이 없습니다.";
        }

        $listChk = $request->all()['listChk'];

        // 데이터 체크(예약시간 5분 이상이여야 삭제가능)
        $chkTime = date("YmdHis", time()+60*5);
        $vcntSql = DB::TABLE("submit_sms_log")->WHERE('save_status', 'Y')
                    ->whereIn('no', $listChk)
                    ->where('save_status', 'Y')
                    ->WHERE(function($query) use ($chkTime)  {
                        $query->WHERE("coalesce(reserve_time, '')", '')
                              ->orWhere("reserve_time", '<=', $chkTime);
                    });
        //Log::debug(Func::printQuery($vcntSql));
        $vcnt = $vcntSql->count();

        if($vcnt>0)
        {
            return "삭제는 예약건 및 예약발송시간 5분전까지 가능합니다. (취소불가건 : ".$vcnt.")";
        }

        $smsSeq = $vcntSql->get();
        $smsSeqList = array();
        foreach($smsSeq as $sms)
        {
            foreach($sms as $key => $val)
            {
                if($key == 'send_msg_no')
                {
                    array_push($smsSeqList, $val);
                }
            }
        }
        DB::beginTransaction();

        // SMS 삭제 
        $rslt = DB::table("eumgp_msg_queue")->whereIn("seq", $smsSeqList)->delete();
        Log::debug("SMS 삭제 : ".$rslt);

        // 발송내역 업데이트
        $rslt = DB::table("submit_sms_log")->whereIn("no", $listChk)->where('save_status', 'Y')
                    ->update(['save_status' => "N",'del_time'=>date("YmdHis"),'del_id'=>Auth::id()]);
        Log::debug("업데이트 : ".$rslt);

        if($rslt==0)
        {
            DB::rollback();
            return "발송내역 취소 처리시 에러가 발생했습니다. 관리자에게 문의해주세요";
        }
        else 
        {
            DB::commit();
            return 'Y';
        }
    }
    
    /**
     * 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setSmsLimitList(Request $request){

        $list   = new DataList(Array("listName"=>"smsLimit","listAction"=>'/'.$request->path()));

        if(!isset($request->tabs)) $request->tabs = 'SMS';
        $list->setTabs(Array(),$request->tabs);

        $list->setViewNum(false);

        return $list;
    }
    

    /**
     * 배치관리 메인
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function smsLimit(Request $request)
    {
        $list = $this->setSmsLimitList($request);

        $list->setrightLumpForm('smsLimit',Array('BTN_NAME'=>'저장','BTN_ACTION'=>'smsSaveClick();','BTN_ICON'=>'','BTN_COLOR'=>''));

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $list->setlistTitleCommon(Array
        (
            'code'           =>     Array('발송사유', 1, '', 'center', '', ''),
            'cnt'            =>     Array('일 제한건수', 1, '', 'center', '', ''),
            'month_cnt'      =>     Array('월 제한건수', 0, '', 'center', '', ''),
            'except_yn'      =>     Array('제한유무', 0, '', 'center', '', ''),
        ));

        return view('config.smslimit')->with("result", $list->getList());
    }

    /**
     * 배치 리스트
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function smsLimitList(Request $request)
    {
        $list   = $this->setSmsLimitList($request);
        $param  = $request->all();
        $param['listOrderAsc'] = "asc";
        $param['listOrder']    = "code_order";

        // 메인쿼리
        $config = DB::table("conf_code")->select("*")->where("cat_code", 'sms_erp_cd')->where("save_status", 'Y');

        // 전체 데이터
        $all = DB::table("sms_cnt")->select("*")->where("code", 'tot')->first();

        // 기존 데이터
        $sms = DB::table("sms_cnt")->select("*")->get();

        // 비교 데이터
        foreach ($sms as $key => $val) {
            $configArr[$val->code] = $val->except_yn;
        }

        // 코드 불러오기
        $getConfigArr = Func::getConfigArr('sms_erp_cd');
                
        // 결과
        $result = $config->get();
        
        // 뷰단 데이터 정리.
        $cnt = 0;

        $all->cnt = '<input type="text" name="tot_cnt" value="'.$all->cnt.'" class="text-right">';
        $all->month_cnt = '<input type="text" name="tot_month_cnt" value="'.$all->month_cnt.'" class="text-right">';
        $all->except_yn = '<input type="checkbox" name="tot_except_yn" value="'.$all->code.'" onclick="checkall();">';
        $all->code = '전체';
        $r['v'][] = $all;
        $cnt ++;

		foreach ($result as $v)
		{
            if(isset($configArr[$v->code])){
                $v->cnt = ($configArr[$v->code] == 'Y') ? "무제한" : "";
                $v->month_cnt = ($configArr[$v->code] == 'Y') ? "무제한" : "";
                $v->except_yn = '<input type="checkbox" name="except[]" '.(($configArr[$v->code]=='Y') ? 'checked':'').' value="'.$v->code.'" onclick="">';
            } else {
                $v->cnt = "";
                $v->month_cnt = "";
                $v->except_yn = '<input type="checkbox"  name="except[]" value="'.$v->code.'" onclick="">';
            
            }
            $v->code = $getConfigArr[$v->code];

            $r['v'][] = $v;
			$cnt ++;
        }

		$r['result'] = 1;
		$r['txt'] = $cnt;

		return json_encode($r);
    }

    /**
     * 문자발송건수 저장
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function smsLimitAction(Request $request)
    {
        log::debug(__method__);
        $_DATA = $request->all();

        $reset['except_yn'] = 'N';
        $value['except_yn'] = 'Y';
        $dataProcess = DB::dataProcess('UPD', 'sms_cnt', $reset, $value);
        
        $where['code'] = 'tot';
        $cnt['cnt'] = $_DATA['tot_cnt'] ?? '';
        $cnt['month_cnt'] = $_DATA['tot_month_cnt'] ?? '';
        $dataProcess = DB::dataProcess('UPD', 'sms_cnt', $cnt, $where);

        foreach( $_DATA['except'] as $key => $val )
        {
            $dataProcess = DB::dataProcess('UPD', 'sms_cnt', $value, ["code"=>$val]);
        }

        return $dataProcess;
    }
}