<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;
use Func;
use Loan;
use Trade;
use Vars;
use Auth;
use Log;
use Invest;
use App\Chung\Paging;
use DataList;
use Illuminate\Support\Facades\Storage;
use ExcelFunc;

class TradeInController extends Controller
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
     * 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataList(Request $request)
    {
        $array_conf_code = Func::getConfigArr();
        
        $list = new DataList(Array("listName"=>"tradein","listAction"=>'/'.$request->path()));
        if(!isset($request->tabs))
        {
            $request->tabs = "Y";
        }
        $list->setTabs(Array('Y'=>'정상','N'=>'삭제'),$request->tabs);

        $list->setCheckBox("no");

        if( Func::funcCheckPermit("E001") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/erp/tradeinexcel', 'form_tradein')", "btn-success");
        }

        $list->setSearchDate('날짜검색', Array('T.trade_date'=>'수익지급일','T.transaction_date'=>'실수익지급일', 'T.save_time'=>'등록일', 'T.del_time'=>'삭제일'), 'searchDt', 'Y');
        
        $list->setRangeSearchDetail(Array('T.trade_money'=>'수익지급액'),'','','단위(원)');

        $list->setSearchType('handle_code',Func::getConfigArr('mo_acct_div'),'법인 구분', '', '', '', '', 'Y', '', true);

        $list->setSearchType('trade_type',    $array_conf_code['trade_in_type'], '수익지급구분');

        $list->setSearchDetail(Array('C.name'=>'차입자이름', 'T.cust_info_no'=>'차입자번호', 'investor_no-inv_seq'=>'채권번호'));

        return $list;
    }

    /**
      * 수익지급리스트
      *
      * @param  \Illuminate\Http\Request  $request
      * @return view
      */
    public function tradeIn(Request $request)
    {
        $list   = $this->setDataList($request);

        $list->setLumpForm('DEL', Array('BTN_NAME'=>'삭제처리','BTN_ACTION'=>'lump_del(this);','BTN_ICON'=>'','BTN_COLOR'=>''));

        $list->setlistTitleCommon(Array
        (
            'investor_no_inv_seq'      => Array('채권번호', 0, '', 'center', '', ''),

            'name'                     => Array('차입자명', 0, '', 'center', '', 'name'),
            'l_status'                 => Array('계약상태', 0, '', 'center', '', 'status'),
            'contract_date'            => Array('계약일', 0, '', 'center', '', 'contract_date'),
            'contract_end_date'        => Array('만기일', 0, '', 'center', '', 'contract_end_date'),

            'trade_type'               => Array('수익지급구분', 0, '', 'center', '', 'trade_type'),
            'trade_date'               => Array('수익지급일', 0, '', 'center', '', 'trade_date'),
            'trade_money'              => Array('수익지급액', 0, '', 'center', '', 'trade_money'),
            
            'balance'                  => Array('수익지급후잔액', 0, '', 'center', '', 't.balance'),
            'save_id'                  => Array('등록자', 0, '', 'center', '', 't.save_id', ['save_time'=>['등록시간', 't.save_time', '<br>']])
        ));

        $list->setlistTitleTabs('N',Array
        (
            'del_id'                   => Array('삭제자', 0, '', 'center', '', 't.del_id'),
            'del_time'                 => Array('삭제시간', 0, '', 'center', '', 't.del_time'),
        ));

        return view('erp.tradeIn')->with('result', $list->getList())->with("arr_confirm_id",Func::getArrConfirmId(['D'=>'30','L'=>'31']));

    }
  


    /**
     * 거래처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function tradeInList(Request $request)
    {
        $list   = $this->setDataList($request);
        $param  = $request->all();

        // 기본쿼리
        $LOAN = DB::TABLE("LOAN_INFO AS L")->JOIN("CUST_INFO AS C", "L.CUST_INFO_NO", "=", "C.NO")->JOIN("LOAN_INFO_TRADE AS T", "L.NO", "=", "T.LOAN_INFO_NO");
        $LOAN->SELECT("T.*", "C.NAME", "C.SSN", "L.contract_date", "L.contract_end_date", "L.PRO_CD", "L.RETURN_METHOD_CD", "L.LOAN_RATE", "L.STATUS", "L.LOAN_PAY_TERM","L.DELAY_TERM", "L.CONTRACT_DAY", "L.MANAGER_CODE AS L_MANAGER_CODE", "L.loan_usr_info_no", "L.inv_seq", "L.investor_no", "L.investor_type");
        $LOAN->WHERE('C.SAVE_STATUS','Y');
        $LOAN->WHERE('L.SAVE_STATUS','Y');
        $LOAN->WHERE('T.TRADE_DIV','I');
        
        // 탭 검색
        $param['tabSelectNm'] = "T.SAVE_STATUS";
        $param['tabsSelect']  = $request->tabsSelect;

        if(!isset($param['listOrder']))
        {
            $param['listOrderAsc'] = "DESC";
            $param['listOrder']    = "T.SAVE_TIME";
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='investor_no-inv_seq' && !empty($param['searchString']) )
        {
            if(strstr($param['searchString'], '-'))
            {
                $searchString = explode("-", $param['searchString']);

                // 이관법인타입 문자열 확인
                $pattern = '/([\xEA-\xED][\x80-\xBF]{2}|[a-zA-Z])+/';
                preg_match_all($pattern, $searchString[0], $match);
                $string = implode('', $match[0]);

                // 채권번호 앞에 문자열이 있을경우
                if(!empty($string))
                {
                    $searchString[0] = str_replace($string, "", $searchString[0]);

                    if(!empty($searchString[0]))
                    {
                        // 문자열있는 투자자번호 검색(ex. H5-?)
                        if($searchString[1] == null || $searchString[1] == 0 || $searchString[1] == '')
                        {
                            $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])
                                                    ->WHERE('loan_info.inv_seq',$searchString[1])
                                                    ->WHERE('loan_info.investor_type',$string);          
                        }
                    }
                }
                // 기존 채권번호 형태인경우
                else
                {
                    // 투자자번호로만 검색(ex. 5-?)
                    if($searchString[1] == null || $searchString[1] == 0 || $searchString[1] == '')
                    {
                        $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
                    }
                }
            }

            unset($param['searchString']);
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='T.cust_info_no' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
        }

        //구간 검색 시 숫자 아닌 값 입력되면 데이터 제거
        if(isset($param['rangeSearchDetail']) && (!empty($param['sRangeSearchString']) || !empty($param['eRangeSearchString']) )) {
            $pattern = '/\d+/';
            if(!preg_match($pattern, $param['sRangeSearchString']) || !preg_match($pattern, $param['eRangeSearchString'])) {
                unset($param['rangeSearchDetail']);
            }
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $LOAN = $list->getListQuery("T", 'main', $LOAN, $param);

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $sum_data = Array
        (
            ["COALESCE(SUM(T.TRADE_MONEY),0)", '수익지급액', '원']
        );

        $paging = new Paging($LOAN, $request->page, $request->listLimit, 10, $request->listName, '', $sum_data);

        // 뷰단 데이터 정리.
        $getProCode         = Func::getConfigArr('pro_cd');
        $array_conf_code    = Func::getConfigArr();
        $arrBranch          = Func::getBranch();
        $arrayUserId        = Func::getUserId();

        $cnt = 0;
        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","LOAN_INFO_TRADE"], $rslt);	// CHUNG DATABASE DECRYPT

        foreach( $rslt as $v )
        {
            $v->investor_no_inv_seq = $v->investor_type.$v->investor_no.'-'.$v->inv_seq;
            
            $link = 'javascript:window.open("/erp/custpop?cust_info_no='.$v->cust_info_no.'&no='.$v->loan_info_no.'","msgpop","width=2000, height=1000, scrollbars=yes")';

            $v->loan_info_no = "<a href='".$link.";'>".$v->loan_info_no."</a>";

            if ($v->delay_term > 0) 
            {
                $v->delay_term = '<span class="text-red">' . $v->delay_term . '</span>';
            }
            else
            {
                $v->delay_term = 0;
            }

            $v->name             = Func::nameMasking($v->name, 'N');
            $v->ssn              = Func::ssnFormat($v->ssn, 'A');
            $v->contract_date    = Func::dateFormat($v->contract_date);
            $v->contract_end_date= Func::dateFormat($v->contract_end_date);
            $v->trade_type       = Func::getArrayName($array_conf_code['trade_in_type'], $v->trade_type);
            $v->trade_date       = Func::dateFormat($v->trade_date);
            $v->transaction_date = Func::dateFormat($v->transaction_date);
            $v->balance          = number_format($v->balance);
            $v->trade_money      = number_format($v->trade_money);
            
            $v->save_time        = Func::dateFormat($v->save_time);
            $v->del_time         = Func::dateFormat($v->del_time);
            $v->save_id          = Func::getArrayName($arrayUserId, $v->save_id);
            $v->del_id           = Func::getArrayName($arrayUserId, $v->del_id);

            $v->l_status         = Func::getInvStatus($v->status, true);

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
     * 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Array
     */
    public function tradeInExcel(Request $request)
    {
        if( !Func::funcCheckPermit("E001") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }

        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $list     = $this->setDataList($request);
        $param    = $request->all();
        $down_div = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        // 기본쿼리
        $LOAN = DB::TABLE("LOAN_INFO AS L")->JOIN("CUST_INFO AS C", "L.CUST_INFO_NO", "=", "C.NO")->JOIN("LOAN_INFO_TRADE AS T", "L.NO", "=", "T.LOAN_INFO_NO");
        $LOAN->SELECT("T.*", "C.NAME", "C.SSN", "L.contract_date", "L.contract_end_date", "L.PRO_CD", "L.RETURN_METHOD_CD", "L.LOAN_RATE", "L.STATUS", "L.LOAN_PAY_TERM","L.DELAY_TERM", "L.CONTRACT_DAY", "L.MANAGER_CODE AS L_MANAGER_CODE", "L.loan_usr_info_no", "L.inv_seq", "L.investor_no", "L.investor_type");
        $LOAN->WHERE('C.SAVE_STATUS','Y');
        $LOAN->WHERE('L.SAVE_STATUS','Y');
        $LOAN->WHERE('T.TRADE_DIV','I');
        
        // 탭 검색
        $param['tabSelectNm'] = "T.SAVE_STATUS";
        $param['tabsSelect']  = $request->tabsSelect;

        if(!isset($param['listOrder']))
        {
            $param['listOrderAsc'] = "DESC";
            $param['listOrder']    = "T.SAVE_TIME";
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='investor_no-inv_seq' && !empty($param['searchString']) )
        {
            if(strstr($param['searchString'], '-'))
            {
                $searchString = explode("-", $param['searchString']);

                // 이관법인타입 문자열 확인
                $pattern = '/([\xEA-\xED][\x80-\xBF]{2}|[a-zA-Z])+/';
                preg_match_all($pattern, $searchString[0], $match);
                $string = implode('', $match[0]);

                // 채권번호 앞에 문자열이 있을경우
                if(!empty($string))
                {
                    $searchString[0] = str_replace($string, "", $searchString[0]);

                    if(!empty($searchString[0]))
                    {
                        // 문자열있는 투자자번호 검색(ex. H5-?)
                        if($searchString[1] == null || $searchString[1] == 0 || $searchString[1] == '')
                        {
                            $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])
                                                    ->WHERE('loan_info.inv_seq',$searchString[1])
                                                    ->WHERE('loan_info.investor_type',$string);          
                        }
                    }
                }
                // 기존 채권번호 형태인경우
                else
                {
                    // 투자자번호로만 검색(ex. 5-?)
                    if($searchString[1] == null || $searchString[1] == 0 || $searchString[1] == '')
                    {
                        $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
                    }
                }
            }
            
            unset($param['searchString']);
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='T.cust_info_no' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
        }

        //구간 검색 시 숫자 아닌 값 입력되면 데이터 제거
        if(isset($param['rangeSearchDetail']) && (!empty($param['sRangeSearchString']) || !empty($param['eRangeSearchString']) )) {
            $pattern = '/\d+/';
            if(!preg_match($pattern, $param['sRangeSearchString']) || !preg_match($pattern, $param['eRangeSearchString'])) {
                unset($param['rangeSearchDetail']);
            }
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }
                
        $LOAN = $list->getListQuery("T", 'main', $LOAN, $param);

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($LOAN);
        log::info($query);
        $file_name    = "수익지급리스트_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $request_all['work_id'] = Auth::id();
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no)){
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
            $origin_filename = $excel_no.'_'.$request->work_id.'_'.date("YmdHis").'.xlsx'; 
        } else {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $query, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
            $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
        }

        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","LOAN_INFO_TRADE"], $rslt);	// CHUNG DATABASE DECRYPT

        // 엑셀 헤더
		$excel_header = array('NO','채권번호','차입자명','계약상태','계약일','만기일','수익지급구분','수익지급일','수익지급액','수익지급후잔액','등록자','등록시간');
        $excel_data = [];
        // 뷰단 데이터 정리.
        $getProCode         = Func::getConfigArr('pro_cd');
        $array_conf_code    = Func::getConfigArr();
        $arrBranch          = Func::getBranch();
        $arrayUserId        = Func::getUserId();

        $board_count=1;
        if( Func::funcCheckPermit('R022') )
        {
            $masking = 'A';
        }
        else
        {
            $masking = 'N';
        }

        foreach ($rslt as $v)
        {
            // 펌뱅킹 진행상태 (A대상,B처리중,Y송금성공,N송금실패)
            if( $v->firmbank_status=="" )
            {
                $v->banking_flag = "";
            }
            else
            {
                $v->banking_flag = Vars::$arrayFirmbankStatus[$v->firmbank_status];
            }

            if ($v->delay_term > 0) 
            {
                $v->delay_term = $v->delay_term;
            }
            else
            {
                $v->delay_term = 0;
            }

            $array_data = [
                $board_count,
                $v->investor_type.$v->investor_no.'-'.$v->inv_seq,
                $v->name,
                Func::getInvStatus($v->status),
                Func::dateFormat($v->contract_date),
                Func::dateFormat($v->contract_end_date),
                Func::getArrayName($array_conf_code['trade_in_type'], $v->trade_type),
                Func::dateFormat($v->trade_date),
                number_Format($v->trade_money),
                number_Format($v->balance),
                Func::getArrayName($arrayUserId, $v->save_id),
                Func::dateFormat($v->save_time),
            ];
            $record_count++;
            $board_count++;
            $excel_data[] = $array_data;
        }
        
        // 엑셀 익스포트
        ExcelFunc::fastexcelExport($excel_data, $excel_header, $origin_filename);
    
        // 파일 저장 여부 확인
        $exists = Storage::disk('excel')->exists($origin_filename);

        if( isset($exists) )
        {
            $array_result['etc']             = $etc;
            $array_result['result']          = 'Y';
            $array_result['filename']        = $file_name;
            $array_result['excel_no']        = $excel_no;
            $array_result['record_count']    = $record_count;
            $array_result['down_filename']   = $down_filename;
            $array_result['excel_down_div']  = $excel_down_div;
            $array_result['origin_filename'] = $origin_filename;
            
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div,null, $origin_filename);
        }
        else
        {
           $array_result['result']    = 'N';
           $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }
        return $array_result;
    }
    
    /**
     * 수익지급거래 일괄삭제 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return String 처리결과 Y 또는 오류메세지
     */
    public function tradeInDelete(Request $request)
    {
        $s_cnt = 0;
        $arr_fail = Array();

        // 처리할 내역 배열로 변환
        $arrayListChk = (array) $request->listChk;
        rsort($arrayListChk);

        if( $request->action_mode=="trade_in_DELETE" && is_array($arrayListChk) && sizeof($arrayListChk)>0 )
        {
            for($i=0; $i<sizeof($arrayListChk); $i++)
            {
                $trade_no = $arrayListChk[$i];

                $loan_info_no = DB::table('loan_info_trade')->where("no",$trade_no)->value('loan_info_no');
                            
                DB::beginTransaction();

                // 수익지급정보 SELECT
                $rslt = DB::Table("loan_info_trade")->select("*")->where("no", $trade_no)->where("save_status", "Y")->where("trade_div", "I")->first();
                $rslt = Func::chungDec(["LOAN_INFO_TRADE"], $rslt);	// CHUNG DATABASE DECRYPT
                $vt = (Array) $rslt;
                if( !$vt )
                {
                    DB::rollBack();
                    $arr_fail[$loan_info_no] = "거래내역의 정보를 찾을 수 없습니다.";
                    continue;
                }
                
                // 수익지급취소처리
                $trade = new Trade($loan_info_no);
                $rslt = $trade->tradeInDelete($trade_no);
                if( is_string($rslt) )
                {
                    DB::rollBack();

                    $arr_fail[$loan_info_no] = $rslt;
                    continue;
                }
                
                //  원장변경내역 입력
                $_wch = [
                    "cust_info_no"     =>  $vt['cust_info_no'],
                    "loan_info_no"     =>  $vt['loan_info_no'],
                    "loan_usr_info_no" =>  $vt['loan_usr_info_no'],
                    "worker_id"        =>  Auth::id(),
                    "work_time"        =>  date("YmdHis"),
                    "worker_code"      =>  Auth::user()->branch_code ?? '',
                    "loan_status"      =>  "",
                    "manager_code"     =>  "",
                    "div_nm"           =>  "거래취소",
                    "before_data"      =>  "null,".$vt['trade_date'],       //  변경전값(null 셋팅인듯?),기산일자
                    "after_data"       =>  "null,".date("Ymd"),             //  변경후값(null 셋팅인듯?),취소일자
                    "trade_type"       =>  $vt['trade_type'],
                    "sms_yn"           =>  'N',
                    "memo"             =>  "",
                ];

                $result_wch = Func::saveWonjangChgHist($_wch);
                if( $result_wch != "Y" )
                {
                    DB::rollBack();

                    $arr_fail[$loan_info_no] = "원장변경내역 등록 실패.";
                    continue;
                }

                $s_cnt++;
                DB::commit();
            }
        }
    
        if(isset($arr_fail) && sizeof($arr_fail)>0)
        {
            $error_msg = "실패건이 존재합니다. \n";

            foreach($arr_fail as $t_no => $msg)
            {
                $error_msg .= "[".$t_no."] => ".$msg."\n";
            }

            $return_msg = sizeof($request->listChk)."건 중 ".$s_cnt."건 성공 ".sizeof($arr_fail)."건 실패\n";
            $return_msg .= Func::nvl($error_msg,"");
        }
        else
        {
            $return_msg = "정상처리 되었습니다.";
        }

        $_RESULT['rslt'] = 'Y';
        $_RESULT['msg']  = $return_msg;

        return $_RESULT;
    }
}

