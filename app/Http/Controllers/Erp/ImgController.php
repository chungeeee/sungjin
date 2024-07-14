<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Chung\Func as ChungFunc;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;
use DataList;
use Validator;
use Vars;
use ExcelFunc;
use App\Chung\Paging;
use Illuminate\Support\Facades\Storage;

class ImgController extends Controller
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
        $list   = new DataList(Array("listName"=>"img", "listAction"=>'/'.$request->path()));

        if(!isset($request->tabs))
        {
            $request->tabs = "ALL";
        }

        $list->setTabs(Array('ALL'=>'전체',), $request->tabs);

        if( Func::funcCheckPermit("R022") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/erp/imgexcel', 'form_img')", "btn-success");
        }

        $list->setSearchDate('날짜검색',Array('CUST_INFO_IMG.save_time' => '이미지파일등록일'),'searchDt','Y');

        // $list->setCheckBox("no");

        $list->setSearchDetail(Array( 
            'cust_info_img.cust_info_no'    => '차입자번호',
            'cust_info_img.loan_info_no'    => '계약번호',
            'birth'    => '생년월일',
        ));

        $array_multi = Array('cust_info_img.cust_info_no' => '차입자번호', 'cust_info_img.loan_info_no' => '계약번호');
        $list->setMultiButton($array_multi);
        
        return $list;
    }


   /**
    * 이미지파일명세 메인화면
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
   public function img(Request $request)
   {
       $list   = $this->setDataList($request);
       
       // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
       $list->setlistTitleCommon(Array
       (
            'cust_info_no'               =>     Array('차입자번호', 1, '', 'center', '', 'cust_info_no'),
            'loan_info_no'               =>     Array('계약번호', 1, '', 'center', '', 'loan_info_no'),
            'name'                       =>     Array('이름', 1, '', 'center', '', 'name'),
            'ssn'                        =>     Array('생년월일', 1, '', 'center', '', 'ssn'),
            'taskname'                   =>     Array('파일구분', 1, '', 'center', '', 'taskname'),
            'origin_filename'            =>     Array('파일명', 1, '', 'center', '', 'origin_filename'),
            'memo'                       =>     Array('메모', 1, '', 'center', '', 'memo'),
            'save_time'                  =>     Array('등록일시', 1, '', 'center', '', 'save_time'),
            'worker_id'                  =>     Array('등록자', 1, '', 'center', '', 'worker_id'),
       ));

       return view('erp.img')->with('result', $list->getList());
   }

    /**
    * 이미지파일명세 리스트
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function imgList(Request $request)
    {
        //$request->isDebug = true;

        $list   = $this->setDataList($request);
        $param  = $request->all();

        // 메인쿼리
        $motherAccount = DB::TABLE("cust_info_img");
        $motherAccount->LEFTJOIN("cust_info", "cust_info_img.cust_info_no", "cust_info.no");
        $motherAccount->LEFTJOIN('loan_info', [['cust_info.no', '=', 'loan_info.cust_info_no'],['cust_info.last_loan_info_no', '=', 'loan_info.no']]);
        $motherAccount->SELECT("cust_info_img.no as img_no", "cust_info_img.*", "cust_info.name", "cust_info.ssn");
        $motherAccount->WHERE('cust_info_img.save_status','Y');

        if(!isset($param['listOrder']))
        {
            $param['listOrder']    = "cust_info_img.save_time";
            $param['listOrderAsc'] = "desc";
        }

        // 생년월일검색
        if(isset( $param['searchDetail']) && $param['searchDetail']=='birth' && !empty($param['searchString']) )
        {
            $searchString = $param['searchString'];

            $motherAccount = Func::encLikeSearch($motherAccount, 'cust_info.ssn', $searchString, 'all', 9);
            
            unset($param['searchString']);
        }

        $motherAccount = $list->getListQuery("cust_info_img", 'main', $motherAccount, $param);

        //Log::debug(Func::printQuery($motherAccount));
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($motherAccount, $request->page, $request->listLimit, 10);
        
        // 결과
        $result = $motherAccount->get();
        $result = Func::chungDec(["CUST_INFO_IMG","CUST_INFO","LOAN_INFO"], $result);	// CHUNG DATABASE DECRYPT
        
        // 뷰단 데이터 정리.
        $cnt = 0;
        $arr_task_name = Vars::$arrayTaskName;
        $arrManager = Func::getUserList();

        foreach ($result as $v)
        {
            $v->onclick          = 'javascript:window.open("/erp/custpop?cust_info_no='.$v->cust_info_no.'&no='.$v->loan_info_no.'&page_div=image","msgpop","width=2000, height=1000, scrollbars=yes")';
            $v->line_style       = 'cursor: pointer;';

            $v->ssn = Func::ssnFormat($v->ssn, 'A');
            
            $v->cust_name       = $v->name;
            $v->taskname        = Func::getArrayName($arr_task_name, $v->taskname);
            $v->save_time       = date("Y-m-d H:i:s", strtotime($v->save_time));
            $v->worker_id       = isset($arrManager[$v->worker_id]) ? Func::nvl($arrManager[$v->worker_id]->name, $v->worker_id) : $v->worker_id ;
            
            $r['v'][]           = $v;
            $cnt ++;
        }

        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path());
        $r['result'] = 1;
        $r['txt'] = $cnt;

        return json_encode($r);
   }

   public function imgExcel(Request $request)
   {
        if( !Func::funcCheckPermit("R022") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }

        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $list   = $this->setDataList($request);

        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        $motherAccount = DB::TABLE("cust_info_img");
        $motherAccount->LEFTJOIN("cust_info", "cust_info_img.cust_info_no", "cust_info.no");
        $motherAccount->LEFTJOIN('loan_info', [['cust_info.no', '=', 'loan_info.cust_info_no'],['cust_info.last_loan_info_no', '=', 'loan_info.no']]);
        $motherAccount->SELECT("cust_info_img.*", "cust_info.name");
        $motherAccount->WHERE('cust_info_img.save_status','Y');

        // 생년월일검색
        if(isset( $param['searchDetail']) && $param['searchDetail']=='birth' && !empty($param['searchString']) )
        {
            $searchString = $param['searchString'];

            $motherAccount = Func::encLikeSearch($motherAccount, 'cust_info.ssn', $searchString, 'all', 9);
            
            unset($param['searchString']);
        }

        $param['listOrder'] = "cust_info_img.save_time";
        $param['listOrderAsc'] = "desc";
        $motherAccount = $list->getListQuery("cust_info_img", 'main', $motherAccount, $param);

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($motherAccount, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($motherAccount);
        $file_name    = "이미지파일명세로그조회_".date("YmdHis").'_'.Auth::id().'.xlsx';
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

        // 결과
        $rslt = $motherAccount->get();
        $rslt = Func::chungDec(["CUST_INFO_IMG","CUST_INFO","LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        // 엑셀 헤더
        $excel_header = array('차입자번호','계약번호','이름','파일구분','파일명','메모','등록일시','등록자');
        $excel_data = [];

        $arr_task_name = Vars::$arrayTaskName;
        $arrManager = Func::getUserList();

        foreach ($rslt as $v)
        {
            $array_data = [
                $v->cust_info_no     = Func::addCi($v->cust_info_no),
                $v->loan_info_no     = $v->loan_info_no,
                $v->cust_name        = $v->name,
                $v->taskname         = Func::getArrayName($arr_task_name, $v->taskname),
                $v->origin_filename  = $v->origin_filename,
                $v->memo             = $v->memo,
                $v->save_time       = date("Y-m-d H:i:s", strtotime($v->save_time)),
                $v->worker_id       = isset($arrManager[$v->worker_id]) ? Func::nvl($arrManager[$v->worker_id]->name, $v->worker_id) : $v->worker_id ,
            ];
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
}