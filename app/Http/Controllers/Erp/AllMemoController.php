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

class AllMemoController extends Controller
{

    //sub_type_cd =>stl_dtl_cd
    //settle_reason_cd=>delay_rsn_cd
    //$sub_type=>stl_div_cd

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
        $configArr          = Func::getConfigArr();

        $list = new DataList(Array("listName"=>"allmemo","listAction"=>'/'.$request->path()));

        $list->setSearchDate('날짜검색',Array('cim.save_time'=>'등록일', 'cim.promise_date'=>'약속일'),'searchDt','Y');
        // $list->setSearchType('div',Func::getConfigArr('memo_div'),'메모구분');
        //$list->setSearchType('important_check', ['Y'=>'중요메모', 'N'=>'삭제포함'],'기타');

        $list->setSearchType('handle_code',$configArr['handle_cd'],'관리점');
        if( Func::funcCheckPermit("E004") || Func::funcCheckPermit("E031") )
        {
            // $list->setSearchType('manager_code', Func::myPermitBranch(), '관리지점', '', '', '', '', 'Y', '', true);
            $branchs = Func::myPermitBranchManager();
            $list->setSearchTypeMultiChain('manager_code', 'manager_id', $branchs, '관리지점', '', '', '', '', '담당자선택');
        }
        // 담당자 선택할 수 있게 한다.
        else
        {
            $list->setSearchType('manager_id', Func::getBranchUsers(Auth::user()->branch_code), '담당자', '', '', '', '', 'Y', '', true);
        }

        $list->setSearchTypeChain('div', 'sub_div', Func::getConfigChain('memo_div'), '메모');

        $list->setSearchDetail(Array( 
            'cim.cust_info_no'  => '차입자번호',
            'name'  => '이름',
            'ph_no'  => '연락처',
            'memo'  =>  '메모내용',
        ));    

        if( Func::funcCheckPermit("E022") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/erp/allmemoexcel', 'form_allmemo')", "btn-success");
        }

        return $list;
    }
    
    /**
     * 전체메모명세 메인화면
     *
     * @param  request
     * @return view
     */
	public function allmemo(Request $request)
    {
        $list   = $this->setDataList($request);
        $list->setTabs([], $request->Tabs);
        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬) 
        $list->setlistTitleCommon(Array
        (
            'cust_info_no'        => Array('차입자번호', 0, '', 'center', '', 'cust_info_no'),
            'loan_info_no'        => Array('계약번호', 0, '', 'center', '', 'loan_info_no'),
            'name'                => Array('성명', 0, '', 'center', '', 'name'),
            'div'                 => Array('구분', 0, '', 'center', '', 'div'),
            'sub_div'             => Array('메모구분', 0, '', 'center', '', 'sub_div'),
            'relation_cd'         => Array('연락대상', 0, '', 'center', '', 'relation_cd'),
            'ph_no'               => Array('연락처', 0, '', 'center', '', 'ph_no'),
            'memo'                => Array('메모', 0, '', 'center', '', 'memo'),
            'promise_date'        => Array('약속일', 0, '', 'center', '', 'promise_date'),
            'promise_time'        => Array('약속시간', 0, '', 'center', '', 'promise_time'),
            'promise_money'       => Array('약속금액', 0, '', 'center', '', 'promise_money'),
            'save_name'           => Array('등록직원', 0, '', 'center', '', 'save_id'),
            'save_time'           => Array('등록시간', 0, '', 'center', '', 'cim.save_time'),
        ));

        $rslt['result'] = $list->getList();

        return view('erp.allmemo')->with($rslt);
    }
    
    /**
     * 전체메모명세 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function allmemoList(Request $request)
    {

        $list  = $this->setDataList($request);
        $param = $request->all();

        // 기본쿼리
        $ALLMEMO = DB::table("cust_info as ci")->leftJoin("cust_info_memo as cim",'ci.no','=','cim.cust_info_no')
                                                ->leftJoin("loan_info as li",'cim.loan_info_no','=','li.no')
                                                ->select('cim.*', 'ci.name')
                                                ->where('ci.save_status','=','Y')
                                                ->where('li.save_status','=','Y')
                                                ->wherenotnull('cim.no');
        
        if(empty($param['listOrder']))
        {
            $param['listOrder']       = 'cim.no';
            $param['listOrderAsc']    = 'desc';
        }
        $ALLMEMO = $list->getListQuery("ci",'main',$ALLMEMO,$param);

        Log::debug(Func::printQuery($ALLMEMO));
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($ALLMEMO, $request->page, $request->listLimit, 10, $request->listName);
        $rslt   = $ALLMEMO->get();
        $rslt   = Func::chungDec(["cust_info","cust_info_memo"], $rslt);	// CHUNG DATABASE DECRYPT
        $cnt    = 0;

        // $arr_memo_div = Func::getConfigArr('memo_div');
        $arrayUserId  = Func::getUserId();
        $relation_cd_name = Func::getConfigArr('relation_cd');
        $chain_memo_div = Func::getConfigChain('memo_div');

        foreach ($rslt as $v)
        {
            $v->onclick             = 'javascript:window.open("/erp/custpop?cust_info_no='.$v->cust_info_no.'&no='.$v->loan_info_no.'","msgpop","width=2000, height=1000, scrollbars=yes")';
            $v->line_style          = 'cursor: pointer;';

            // $v->div           = Func::getArrayName($arr_memo_div, $v->div);
            $tempDiv                = Func::getChainName($chain_memo_div, $v->div, $v->sub_div, '', 'array');
            
            $v->div                 = $tempDiv[0];
            $v->sub_div             = $tempDiv[1];
            $v->promise_date        = isset( $v->promise_date ) ? Func::dateFormat($v->promise_date) : "";
            $v->promise_hour        = sprintf("%02d",$v->promise_hour);
            if( trim($v->promise_hour)!="" || trim($v->promise_min)!="" )
            {
                $v->promise_hour    = sprintf("%02d",$v->promise_hour);
                $v->promise_min     = sprintf("%02d",$v->promise_min);
            }
            $v->promise_time        = $v->promise_hour.":".$v->promise_min;
            $v->promise_money       = number_format($v->promise_money);
            $v->save_name           = Func::getArrayName($arrayUserId, $v->save_id);
            $v->relation_cd         = Func::getArrayName($relation_cd_name, $v->relation_cd);
            $v->save_time           = Func::dateFormat($v->save_time);

            $r['v'][] = $v;
            $cnt ++;
        }
        // 페이징
        $r['pageList']  = $paging->getPagingHtml($request->path());
        if(isset($r['incSum']['loan_money']))
        {
            $r['pageList'] = Func::addTextPage($r['pageList'], '[원금합계 : <span class="text-blue bold">'.number_format($r['incSum']['loan_money']).'원</span>]');
        }
        $r['result']    = 1;
        $r['txt']       = $cnt;
        
        return json_encode($r);
    }


    /**
     * 전체메모명세 엑셀
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function allmemoExcel(Request $request)
    {
        if( !Func::funcCheckPermit("E022") && !isset($request->excel_flag) )
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
        $ALLMEMO = DB::table("cust_info as ci")->leftJoin("cust_info_memo as cim",'ci.no','=','cim.cust_info_no')->where('ci.save_status','=','Y')->wherenotnull('cim.no');
        
        if(empty($param['listOrder']))
        {
            $param['listOrder']       = 'cim.no';
            $param['listOrderAsc']    = 'desc';
        }
        $ALLMEMO = $list->getListQuery("ci",'main',$ALLMEMO,$param);
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($ALLMEMO, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($ALLMEMO);
        
        $file_name    = "전체메모명세_".date("YmdHis").'_'.Auth::id().'.xlsx';
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

        $rslt   = $ALLMEMO->GET();
        $rslt   = Func::chungDec(["cust_info","cust_info_memo"], $rslt);	// CHUNG DATABASE DECRYPT
        
        // 엑셀헤더
        $excel_header = array('No', '차입자번호', '계약번호', '성명', '구분', '메모구분', '연락대상', '연락처', '메모', '약속일', '약속시간', '약속금액', '등록직원', '등록시간');
        $excel_data   = [];
        
        // 뷰단 데이터 정리
        $arrayUserId      = Func::getUserId();
        $relation_cd_name = Func::getConfigArr('relation_cd');
        $chain_memo_div   = Func::getConfigChain('memo_div');
        $record_count     = 0;
        $cnt              = 1;
        
        foreach ($rslt as $v)
        {
            if( trim($v->promise_hour)!="" || trim($v->promise_min)!="" )
            {
                $v->promise_hour    = sprintf("%02d",$v->promise_hour);
                $v->promise_min     = sprintf("%02d",$v->promise_min);
            }
            $tempDiv                = Func::getChainName($chain_memo_div, $v->div, $v->sub_div, '', 'array');

            $array_data = Array(
                $cnt,
                Func::addCi($v->cust_info_no),
                $v->loan_info_no,
                $v->name,
                $tempDiv[0],
                $tempDiv[1],
                Func::getArrayName($relation_cd_name, $v->relation_cd),
                $v->ph_no,
                $v->memo,
                !empty( $v->promise_date ) ? Func::dateFormat($v->promise_date) : '',
                !empty( $v->promise_hour ) ? $v->promise_hour.":".$v->promise_min : '',
                number_format($v->promise_money),
                Func::getArrayName($arrayUserId, $v->save_id),
                substr(Func::dateFormat($v->save_time), 0, 10)
            );

            $cnt++;
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