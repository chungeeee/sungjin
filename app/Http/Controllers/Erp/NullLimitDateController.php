<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;
use Redirect;
use Vars;
use Loan;
use Trade;
use DataList;
use Validator;
use App\Chung\Paging;
use App\Http\Controllers\Erp\CcrsController;
use App\Http\Controllers\Erp\IrlController;
use ExcelFunc;
use FastExcel;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NullLimitDateController extends Controller
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
     * 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataList(Request $request)
    {
        $param   = $request->all();
        $list = new DataList(Array("listName"=>"nulllimitdate","listAction"=>'/'.$request->path()));

        // 상세검색 시 조건컬럼 추가되서 기본 조건으로 세팅
        $param['searchDetail'] = 'save_status';
        $param['searchString'] = 'Y';
        $param['listOrder']    = 'NONE';

        $list->setSearchDate('기준일자','','searchDt','Y','N', '2023-01-01', date("Y-m-d"));
        // $list->setSearchDate('기준일자','','searchDt','Y','N', date("Y-m-d"), date("Y-m-d"));
        $list->setSearchDetail(Array('cust_info.name'=>'성명','cust_info.ssn'=>'주민번호','loan_info.no'=>'계약번호','cust_info.no'=>'차입자번호'));
        $list->setButtonArray("엑셀다운","excelDownModal('/erp/nulllimitdateexcel','form_nulllimitdate')","btn-success");


        if(!isset($request->tabs))
        {
            $request->tabs = "Y";
        }
        $list->setTabs(Array('Y'=>'전체'), $request->tabs);

        return $list;
    }
    
    /**
     * 소멸시효명세 메인화면
     *
     * @param  request
     * @return view
     */
	public function nullLimitDate(Request $request)
    {
        $list   = $this->setDataList($request);
        $param  = $request->all();

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 
        //             리스트 세팅([0] key=>타이틀,[1]사용X ,[2] 넓이 - % 또는 px,[3] text 정렬,[4] rightline 여부,[5] data정렬,
        //                        [6] 한칸에 여러데이터 중첩표시 array([컬럼]=>array(text,data 정렬,txet앞에 표시될html( / , <br> ..) )))
 
        $list->setlistTitleCommon(Array
        (
            'cno'                         => Array('차입자번호', 0, '', 'center', '', 'no'),
            'lno'                         => Array('계약번호', 0, '', 'center', '', 'no'),
            'name'                        => Array('성명', 0, '', 'center', '', 'name'),
            'ssn'                         => Array('생년월일', 0, '', 'center', '', 'ssn'),
            'null_limit_date'             => Array('시효완성일', 0, '', 'center', '', 'null_limit_date'),
            'null_limit_date_memo'        => Array('시효완성일기준', 0, '', 'center', '', 'null_limit_date_memo'),
            'trade_date'                  => Array('기준일자', 0, '', 'center', '', 'trade_date'),
            'worker_id'                   => Array('작업자', 0, '', 'center', '', 'worker_id'),
        ));
        
        return view('erp.nullLimitDate')->with('result', $list->getList());
    }
    
    /**
     * 소멸시효명세 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function nullLimitDateList(Request $request)
    {
        $list           = $this->setDataList($request);
        $param          = $request->all();
        $param_seller   = $request->all();
        $reg_sdate      = $request->searchDtString;
        $reg_edate      = $request->searchDtStringEnd;
        
        
        // 기본쿼리
        $NL = DB::TABLE("loan_info_log_null_limit_date");
        $NL ->JOIN("loan_info", "loan_info_log_null_limit_date.loan_info_no", "=", "loan_info.no");
        $NL ->JOIN("cust_info", "cust_info.no", "=", "loan_info.cust_info_no");
        $NL ->SELECT("cust_info.no as cno", "cust_info.name", "cust_info.ssn", "loan_info.no as lno", "loan_info_log_null_limit_date.*");
        $NL ->WHERE('loan_info.save_status', 'Y')->WHERE('loan_info_log_null_limit_date.save_status', 'Y');
        
        
        // 기준일자 검색
        if($reg_sdate && $reg_edate)
        {
            $NL->WHEREBETWEEN("loan_info_log_null_limit_date.trade_date", [$reg_sdate, $reg_edate]);
        }
        elseif($reg_sdate)
        {
            $NL->WHERE("loan_info_log_null_limit_date.trade_date", ">=", $reg_sdate);
        }
        elseif($reg_edate)
        {
            $NL->WHERE("loan_info_log_null_limit_date.trade_date", "<=", $reg_edate);
        }
        
        Log::debug(Func::printQuery($NL));

        $NL = $list->getListQuery("loan_info_log_null_limit_date", "main", $NL, $param);
        $NL->ORDERBY('loan_info_log_null_limit_date.null_limit_date', 'desc');
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($NL, $request->page, $request->listLimit, 10);
        $rslt   = $NL->GET();
        $rslt   = Func::chungDec(["CUST_INFO","LOAN_INFO","LOAN_INFO_LOG_NULL_LIMIT_DATE"], $rslt);	// 복호화
        
        
        if(!isset($param['listOrder']) || ($param['searchDetail'] && $param['searchString']))
        {
            $param_seller['searchDetail'] = "save_status";
            $param_seller['searchString'] = 'Y';
            $param_seller['listOrder'] = 'NONE';
        }
                

        // 뷰단 데이터 정리
        $arrayBranch = Func::getBranchList();
        $arrayUsers  = Func::getBranchUserList();
        $arrayNullMemo =  Func::getConfigArr('null_limit_date_memo');
        
        $cnt = 0;
        foreach($rslt as $n)
        {
            $worker_id        = Func::getBranchById($n->worker_id);
            $n->name          = '<a onclick="javascript:loan_info_pop('.$n->cno.','.$n->lno.');" class="hand text-primary">'.$n->name.'</a>';
            $n->worker_id     = !empty($worker_id->name) ? $worker_id->name : $n->worker_id;

            // 권한 있으면 다 보여주고(A), 없으면 마스킹처리(Y) 
            $n->ssn = Func::ssnFormat($n->ssn, 'A');
            if($n->null_limit_date_memo)
            {
                $n->null_limit_date_memo  = $arrayNullMemo[$n->null_limit_date_memo];
            }

            $r['v'][] = $n;
            
            $cnt++;
        }
        
        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path());
        $r['result'] = 1;
        $r['txt'] = $cnt;
        
        return json_encode($r);
    }

        /**
     * 엑셀다운로드 (소멸시효명세)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return Array ['header', 'excel_data''title', 'style']
     */
    public function nullLimitDateExcel(Request $request)
    {
        if( !Func::funcCheckPermit("R022") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }
        
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');

        $list           = $this->setDataList($request);
        $param          = $request->all();
        $file_name      = "소멸시효명세_".date("YmdHis").".xlsx";
        $mode           = "INS";
        $down_div       = $request->down_div;
        $reg_sdate      = $request->searchDtString;
        $reg_edate      = $request->searchDtStringEnd;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;
        
        // 기본쿼리
        $NL = DB::TABLE("loan_info_log_null_limit_date");
        $NL ->JOIN("loan_info", "loan_info_log_null_limit_date.loan_info_no", "=", "loan_info.no");
        $NL ->JOIN("cust_info", "cust_info.no", "=", "loan_info.cust_info_no");
        $NL ->SELECT("cust_info.no as cno", "cust_info.name", "cust_info.ssn", "loan_info.no as lno", "loan_info_log_null_limit_date.*");
        $NL ->WHERE('loan_info.save_status', 'Y')->WHERE('loan_info_log_null_limit_date.save_status', 'Y');

        // 기준일자 검색
        if($reg_sdate && $reg_edate)
        {
            $NL->WHEREBETWEEN("loan_info_log_null_limit_date.trade_date", [$reg_sdate, $reg_edate]);
        }
        elseif($reg_sdate)
        {
            $NL->WHERE("loan_info_log_null_limit_date.trade_date", ">=", $reg_sdate);
        }
        elseif($reg_edate)
        {
            $NL->WHERE("loan_info_log_null_limit_date.trade_date", "<=", $reg_edate);
        }

        $NL = $list->getListQuery("loan_info_log_null_limit_date", 'main', $NL, $param);
        $NL->ORDERBY('loan_info_log_null_limit_date.null_limit_date', 'desc');

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($NL, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($NL);
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

        $rslt = $NL->GET();
        $rslt = Func::chungDec(["CUST_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        // 엑셀 헤더
		$excel_header = array('No','차입자번호','계약번호','성명','주민등록번호','시효완성일','시효완성일기준','기준일자','작업자');
        $excel_data   = [];
        // 데이터 추출
        if(!isset($param['listOrder']) || ($param['searchDetail'] && $param['searchString']))
        {
            $param['searchDetail'] = "save_status";
            $param['searchString'] = 'Y';
            $param['listOrder'] = 'NONE';
        }

        // 뷰단 데이터 정리
        $arrayNullMemo =  Func::getConfigArr('null_limit_date_memo');

        $record_count = 1;
        foreach($rslt as $n)
        {
            $n->ssn               = Str::substr($n->ssn, 0, 6)."-".Str::substr($n->ssn, 6);
            $worker_id            = Func::getBranchById($n->worker_id) ?? '';         

            $array_data = Array(
                                    $record_count,
                                    Func::addCi($n->cno),
                                    $n->lno,
                                    $n->name,
                                    $n->ssn,            
                                    $n->null_limit_date ?? '',
                                    $n->null_limit_date_memo  = isset($n->null_limit_date_memo) ? $arrayNullMemo[$n->null_limit_date_memo] : '',
                                    $n->trade_date ?? '',
                                    !empty($worker_id->name) ? $worker_id->name : $n->worker_id
                                );

            // 데이터
            $excel_data[] = $array_data;
            
            $record_count++;
        }


        // 엑셀 익스포트
        ExcelFunc::fastexcelExport($excel_data,$excel_header,$file_name);

        // 파일 저장 여부 확인
        $exists = Storage::disk('excel')->exists($file_name);

        if( isset($exists))
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



 
} 