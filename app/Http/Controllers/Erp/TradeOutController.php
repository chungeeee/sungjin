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
use App\Chung\Paging;
use DataList;
use SettleBank;
use Illuminate\Support\Facades\Storage;
use ExcelFunc;

class TradeOutController extends Controller
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
        $list = new DataList(Array("listName"=>"tradeout","listAction"=>'/'.$request->path()));

        if( Func::funcCheckPermit("E001") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/erp/tradeoutexcel', 'form_tradeout')", "btn-success");
        }

        $list->setSearchDate('날짜검색', Array('T.trade_date'=>'차입금일', 'T.save_time'=>'등록일', 'T.del_time'=>'삭제일'), 'searchDt', 'Y', 'N', '', '', '');

        $list->setRangeSearchDetail(Array('trade_money'=>'차입금액'),'','','단위(원)');

        $list->setSearchType('handle_code', Func::getConfigArr('mo_acct_div'), '법인구분', '', '', '', '', 'Y', '', true);
        
        $list->setSearchType('trade_type', $array_conf_code['trade_out_type'], '차입금구분');

        $list->setSearchDetail(Array( 'C.name'=>'차입자이름', 'T.cust_info_no'=>'차입자번호', 'investor_no-inv_seq'=>'채권번호'));

        return $list;
    }
    /**
      * 차입금리스트
      *
      * @param  \Illuminate\Http\Request  $request
      * @return view
      */
    public function tradeOut(Request $request)
    {
        $list   = $this->setDataList($request);
        
        $list->setlistTitleCommon(Array
        (
            'investor_no_inv_seq'      => Array('채권번호', 0, '', 'center', '', ''),
            'name'                     => Array('차입자명', 0, '', 'center', '', 'name'),
            'contract_date'            => Array('계약일', 0, '', 'center', '', 'contract_date'),
            'contract_end_date'        => Array('만기일', 0, '', 'center', '', 'contract_end_date'),

            'trade_type'               => Array('차입금구분', 0, '', 'center', '', 'trade_type'),
            'trade_date'               => Array('차입금일', 0, '', 'center', '', 'trade_date'),
            'trade_money'              => Array('차입금액', 0, '', 'center', '', 'trade_money'),

            'save_id'                  => Array('등록자', 0, '', 'center', '', 'T.save_id'),
            'save_time'                => Array('등록시간', 0, '', 'center', '', 'T.save_time'),
        ));

        return view('erp.tradeOut')->with('result', $list->getList());
    }
  


    /**
     * 차입금리스트 데이터
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function tradeOutList(Request $request)
    {
        $list   = $this->setDataList($request);
        $param  = $request->all();
    
         // Tab count 
         if($request->isFirst=='1')
         {
            $BOXC = DB::TABLE("LOAN_INFO AS L")
                            ->JOIN("CUST_INFO AS C", "L.CUST_INFO_NO", "=", "C.NO")
                            ->JOIN("LOAN_INFO_TRADE AS T", "L.NO", "=", "T.LOAN_INFO_NO")
                            ;
            $BOXC = $BOXC->SELECT(DB::RAW("
            COALESCE(SUM(CASE WHEN T.SAVE_STATUS = 'Y' THEN 1 ELSE 0 END),0) AS Y, 
            COALESCE(SUM(CASE WHEN T.SAVE_STATUS = 'N' THEN 1 ELSE 0 END),0) AS N"));
            $BOXC->WHERE('C.SAVE_STATUS','Y');
            $BOXC->WHERE('L.SAVE_STATUS','Y');
            $BOXC->WHERE('T.TRADE_DIV','O');

            // 전지점 조회권한 없으면 자기 지점만
            if( !Func::funcCheckPermit("E004") )
            { 
                $BOXC->WHERE( FUNCTION ($subquery){
                    $subquery -> WHEREIN('L.MANAGER_CODE', array_keys(Func::myPermitBranch()));
                });  
            }
            $count = $BOXC->FIRST();
            $r['tabCount'] = array_change_key_case((Array)$count, CASE_UPPER);
         }

            
        // 기본쿼리
        $LOAN = DB::TABLE("LOAN_INFO AS L")
                    ->JOIN("CUST_INFO AS C", "L.CUST_INFO_NO", "=", "C.NO")
                    ->JOIN("LOAN_INFO_TRADE AS T", "L.NO", "=", "T.LOAN_INFO_NO")
                    ;
        $LOAN->SELECT("T.*", "C.NAME", "C.SSN", "L.contract_date", "L.contract_end_date", "L.PRO_CD", "L.RETURN_METHOD_CD", "L.LOAN_RATE", "L.STATUS", "L.CONTRACT_DAY", "L.MANAGER_CODE as L_MANAGER_CODE", "L.loan_usr_info_no", "L.inv_seq", "L.investor_no", "L.investor_type");
        $LOAN->WHERE('C.SAVE_STATUS','Y');
        $LOAN->WHERE('L.SAVE_STATUS','Y');
        $LOAN->WHERE('T.TRADE_DIV','O');

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
        $paging = new Paging($LOAN, $request->page, $request->listLimit, 10, $request->listName);

        // 뷰단 데이터 정리.
        $getProCode = Func::getConfigArr('pro_cd');
        $array_conf_code = Func::getConfigArr();
        $arrBranch       = Func::getBranch();
        $arrayUserId     = Func::getUserId();

        $cnt = 0;
        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","LOAN_INFO_TRADE","LOAN_APP"], $rslt);	// CHUNG DATABASE DECRYPT
        
        foreach( $rslt as $v )
        {
            $v->loan_info_no_lnk = "<a href='javascript:loan_info_pop(".$v->cust_info_no.", ".$v->loan_info_no.");'>".$v->loan_info_no."</a>";

            $v->investor_no_inv_seq = $v->investor_type.$v->investor_no.'-'.$v->inv_seq;

            $v->name             = Func::nameMasking($v->name, 'N');
            $v->ssn              = Func::ssnFormat($v->ssn, 'A');
            $v->contract_date    = Func::dateFormat($v->contract_date);
            $v->contract_end_date= Func::dateFormat($v->contract_end_date);
            $v->trade_type       = $array_conf_code['trade_out_type'][$v->trade_type];
            $v->trade_path_cd    = Func::getArrayName($array_conf_code['trade_out_path'], $v->trade_path_cd);

            $v->trade_date       = Func::dateFormat($v->trade_date);
            $v->trade_money      = number_format($v->trade_money);
            $v->save_time        = Func::dateFormat($v->save_time);
            $v->del_time         = Func::dateFormat($v->del_time);
            $v->save_id          = Func::getArrayName($arrayUserId, $v->save_id);
            $v->del_id           = Func::getArrayName($arrayUserId, $v->del_id);
            $v->manager_name     = Func::nvl($arrBranch[$v->l_manager_code], $v->l_manager_code);

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
    public function tradeOutExcel(Request $request)
    {
        if( !Func::funcCheckPermit("E001") && !isset($request->excel_flag) )
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
        $LOAN = DB::TABLE("LOAN_INFO AS L")
                    ->JOIN("CUST_INFO AS C", "L.CUST_INFO_NO", "=", "C.NO")
                    ->JOIN("LOAN_INFO_TRADE AS T", "L.NO", "=", "T.LOAN_INFO_NO")
                    ;
        $LOAN->SELECT("T.*", "C.NAME", "C.SSN", "L.contract_date", "L.contract_end_date", "L.PRO_CD", "L.RETURN_METHOD_CD", "L.LOAN_RATE", "L.STATUS", "L.CONTRACT_DAY", "L.MANAGER_CODE as L_MANAGER_CODE", "L.loan_usr_info_no", "L.inv_seq", "L.investor_no", "L.investor_type");
        $LOAN->WHERE('C.SAVE_STATUS','Y');
        $LOAN->WHERE('L.SAVE_STATUS','Y');
        $LOAN->WHERE('T.TRADE_DIV','O');

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

        $LOAN   = $list->getListQuery("T","main",$LOAN,$param);
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($LOAN);
        $file_name    = "차입금리스트_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $request_all['work_id'] = Auth::id();
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no))
        {
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
            $origin_filename = $excel_no.'_'.$request->work_id.'_'.date("YmdHis").'.xlsx'; 
        }
        else
        {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $query, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
            $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
        }

        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","LOAN_INFO_TRADE","LOAN_APP"], $rslt);	// CHUNG DATABASE DECRYPT

        // 엑셀 헤더
		$excel_header    = array('NO','채권번호','차입자명','계약일','만기일','차입금구분','차입금일','차입금액','등록자','등록시간');
        $excel_data      = [];
        $getProCode      = Func::getConfigArr('pro_cd');
        $array_conf_code = Func::getConfigArr();
        $arrBranch       = Func::getBranch();
        $arrayUserId     = Func::getUserId();

        $board_count     = 1;
        foreach ($rslt as $v)
        {
            $array_data = [
                $board_count,
                $v->investor_type.$v->investor_no.'-'.$v->inv_seq,
                Func::nameMasking($v->name, 'N'),
                Func::ssnFormat($v->ssn, 'A'),
                Func::dateFormat($v->contract_date),
                Func::dateFormat($v->contract_end_date),
                $array_conf_code['trade_out_type'][$v->trade_type],
                Func::dateFormat($v->trade_date),
                Func::numberFormat((int)($v->trade_money)),
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
}
