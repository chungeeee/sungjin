<?php
namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;
use Redirect;
use App\Chung\Paging;
use App\Chung\Vars;


class CustomerMemoController extends Controller
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
    * 고객정보창 메모 리스트
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function custMemo(Request $request)
    {
	    $configArr             = Func::getConfigArr();
        $arr_memo_div       = $configArr['memo_div'];
        $arr_relation_cd    = $configArr['relation_cd'];
        $arr_ph_cd          = $configArr['phone_cd'];
        $chain_memo_div     = Func::getConfigChain('memo_div');
        $arrayUserId        = Func::getUserId();


        // listName : 리스트 이름 (표시 x)
        $result['listName'] = 'custmemo';
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
        $result['searchDateNm'][] = 'searchDt';
        // searchDatePair : 일자검색 시작날짜, 종료날짜 검색 여부 - 두번째 날짜 input은 name에 End가 붙는다.
        $result['searchDatePair'][] = 'Y';
        // searchDateNoBtn : 오늘, 이번주, 한달 버튼 여부 (N == 표시, Y == 미표시, YESTERDAY == 전날도 사용)
        $result['searchDateNoBtn'][] = 'Y';

        // searchDetail : 검색 사용 여부 (Y, N)
        $result['searchDetail'] = 'N';

        //  searchType
        $result['searchType'] = 'Y';
        $result['searchTypeNm'][] = 'memo_div';
        $result['searchTypeTitle'][] = '메모구분';
        $result['searchTypeAction'][] = "onChange='getMemoList()'";
        $result['searchTypeArray'][] = $arr_memo_div;

        // isModal : 모달창 사용여부 (Y, N)
        $result['isModal'] = 'N';

        // plusButton : 등록 버튼 추가 여부 (Y, N)
        $result['plusButton'] = 'Y';
        // plusButtonAction : 등록 버튼 onclick 동작
        $result['plusButtonAction'] = "setMemo('new');";

        // listTitle : 표시할 컬럼 및 설정 [ Title, width, align, colum, orderby사용여부 ]
        $result['listTitle'][] = Array
        (
            'div_name'          => Array('구분', '34%', 'center', 'div', 'Y'),
            'promise_date'      => Array('약속일', '22%', 'center', 'promise_date', 'Y'),
            'promise_time'      => Array('약속시간', '22%', 'center', 'promise_hour', 'Y'),
            'promise_money'     => Array('약속금액', '22%', 'center', 'promise_money', 'Y'),
        );
        $result['listTitle'][] = Array
        (
            'save_time'         => Array('등록시간', '34%', 'center', 'save_time', 'Y'),
            'save_name'         => Array('등록직원', '22%', 'center', 'save_id', 'Y'),
            'ph_cd_name'        => Array('연락처', '22%', 'center', 'relation_cd', 'Y'),            
            'relation_cd_name'  => Array('대상', '22%', 'center', 'relation_cd', 'Y'),
        );
        $result['listTitle'][] = Array
        (
            'memo'              => Array('', '100%', 'left', 'memo', 'N'),
        );


        // listlimit : 한페이지 출력 건수
        $result['listlimit'] = "30";

        if(isset($request->loan_info_no))
        {
            $result['customer']['loan_info_no'] = $request->loan_info_no;
        }

        if(isset($request->cust_info_no))
        {
            $result['customer']['cust_info_no'] = $request->cust_info_no;
        }

        // 중요메모 불러오기
        $cust = DB::table('cust_info')->select('important_memo', 'imemo_save_id', 'imemo_save_time')
                ->where('save_status', 'Y')
                ->where('no', $request->cust_info_no)
                ->first();
        $imemo['memo'] = isset($cust->important_memo) ? $cust->important_memo:'';
        $imemo['save_id'] = Func::getArrayName($arrayUserId, $cust->imemo_save_id);
        $imemo['save_time'] = Func::dateFormat($cust->imemo_save_time);
        $imemo['save_time'] = str_replace(" ", "<br>", substr($imemo['save_time'], 2));

        // log::debug($result);
        return view('erp.custMemo') ->with("cust_info_no", $request->cust_info_no)
                                    ->with("loan_info_no", $request->loan_info_no)
                                    ->with("result", $result)
                                    ->with("arr_memo_div", $arr_memo_div)
                                    ->with("arr_relation_cd", $arr_relation_cd)
                                    ->with("chain_memo_div", $chain_memo_div)
                                    ->with("arr_ph_cd", $arr_ph_cd)
                                    ->with("imemo", $imemo);
    }

    /**
    * 고객정보창 메모 리스트
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function custMemoList(Request $request)
    {
        // $request->isDebug = true;

        // DB::enableQueryLog();
        // 메인쿼리
        $memos = DB::table("cust_info_memo as cim");
        $memos->select("COALESCE(important_check, '') AS ic", "cim.no", "cim.div", "cim.relation_cd", "cim.save_id", "cim.save_time", "cim.promise_date", "cim.promise_hour", "cim.promise_min", "cim.promise_money", "cim.memo", "cim.memo_color", "cim.ph_cd", "cim.ph_no", "cim.up_id", "cim.up_time", "cim.sub_div", "cim.is_batch");
        $memos->where("save_status","Y");
        $memos->where("cust_info_no", $request->cust_info_no);
        // $memos->ORDERBY("COALESCE(trim(IMPORTANT_CHECK), '')",'desc');
		// $memos->orderBy("cim.no",'desc');
        
        // // 상세검색
        // if($request->searchDetail && $request->searchString)
        // {
        //     $memos = $memos->WHERE($request->searchDetail, 'like', $request->searchString.'%');
        // }
        
        if( $request->memo_div )
        {
            $memos = $memos->where('div', $request->memo_div);
        }
        
        // 날짜 검색
        if($request->searchDtString)
        {
            $sDate = str_replace('-', '', $request->searchDtString)."000000";
            $memos = $memos->where('save_time', '>=', $sDate);
        }
        if($request->searchDtStringEnd)
        {
            $eDate = str_replace('-', '', $request->searchDtStringEnd)."235959";
            $memos = $memos->where('save_time', '<=', $eDate);
        }

        // 정렬
        if($request->listOrder)
        {
            if($request->listOrder=='primise_date' || $request->listOrder=='promise_time')
            {
                $memos = $memos->orderBy("coalesce(".$request->listOrder.", '')", $request->listOrderAsc);
            }
            else
            {
                $memos = $memos->orderBy($request->listOrder, $request->listOrderAsc);
            }
        }
        else
        {
            $memos = $memos->orderBy('save_time', 'desc');
        }
        $memos = $memos->orderBy('no', 'desc');

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($memos, $request->page, $request->listLimit, 10);
        
        // 결과
        $memos = $memos->get();
        $memos = Func::chungDec(["cust_info_memo"], $memos);	// CHUNG DATABASE DECRYPT
	
	    $configArr = Func::getConfigArr();
        $arr_memo_div = $configArr['memo_div'];
        $arr_relation_cd = $configArr['relation_cd'];
        $arr_ph_cd       = $configArr['phone_cd'];
        $arrayUserId = Func::getUserId();
        $chain_memo_div = Func::getConfigChain('memo_div');

        // 뷰단 데이터 정리.
        $cnt = 0;
        foreach ($memos as $v)
        {
            // $v->div_name         = Func::getArrayName($arr_memo_div, $v->div);
            $v->div_name         = Func::getChainName($chain_memo_div, $v->div, $v->sub_div);
            $v->relation_cd_name = Func::getArrayName($arr_relation_cd, $v->relation_cd);
            $v->ph_cd_name       = Func::getArrayName($arr_ph_cd, $v->ph_cd);
            $v->save_time        = Func::dateFormat($v->save_time);
            $v->promise_date     = isset( $v->promise_date ) ? Func::dateFormat($v->promise_date) : "";
            if( trim($v->promise_hour)!="" || trim($v->promise_min)!="" )
            {
                $v->promise_hour = sprintf("%02d",$v->promise_hour);
                $v->promise_min = sprintf("%02d",$v->promise_min);
            }
            $v->promise_time     = $v->promise_hour.":".$v->promise_min;
            $v->promise_money    = number_format($v->promise_money);

            $v->memo             = str_replace("\r","",str_replace("\n", "<br>", $v->memo));
            if( $v->ph_no!='' )
            {
                $v->memo = "[연락번호] ".$v->ph_no."<br>".$v->memo;
            }
            $v->onclick          = "setMemo('".$v->no."');";
            $v->memo_color       = isset($v->memo_color)? "color:".$v->memo_color.";" : "";
            $v->important_color  = ($v->ic == "Y")? "background-color:"."#f4ffb6;" : "";

            $v->save_name        = Func::getArrayName($arrayUserId, $v->save_id);

            // 배치로 등록한 건 진하게 표시
            if($v->is_batch=='Y')
            {
                $v->memo = '<b class="bold">'.$v->memo.'</b>';
            }
            
            // 수정내역 확인
            if($v->up_time!='')
            {
                $v->save_name.= '<br>(수정:'.Func::getArrayName($arrayUserId, $v->up_id).')';
                $v->save_time.= '<br>('.Func::dateFormat($v->up_time).')';
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
    * 고객정보창 메모 상세
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function custMemoInput(Request $request)
    {
        if($request->no!='')
        {  
            $r['mode'] = "UPD";
            $r['data'] = DB::table("cust_info_memo")->select("*")->where("save_status","Y")->where("no", $request->no)->first();
            $r['data'] = Func::chungDec(["cust_info_memo"], $r['data']);	// CHUNG DATABASE DECRYPT
        }
        else
        {
            $r['mode'] = "INS";
        }

        $r['branch_memo'] = DB::table("branch_memo")->select("*")->where("save_status", 'Y')->where("branch_code", Auth::user()->branch_code)->orderby('memo_order','asc')->orderBy('no','desc')->get()->toArray();
        $r['branch_memo'] = Func::chungDec(["branch_memo"], $r['branch_memo']);	// CHUNG DATABASE DECRYPT
        
        // return view('erp.custMemoInput')->with("mode", $mode)->with("result", $v);
        return json_encode($r);
    }


    
    /*
        고객정보창 메모 등록, 수정, 삭제

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function custMemoAction(Request $request)
    {
        $_DATA = $request->input();

        $_DATA['important_check'] = ( isset( $_DATA['important_check'] ) && $_DATA['important_check']=="Y" ) ? "Y" : "" ;

        DB::beginTransaction();

        // 기한이익상실통보
        if( isset($_DATA['div']) && $_DATA['div']=="G" )
        {
            $rslt = DB::dataProcess('UPD', 'CUST_INFO', ['kihan_post_date'=>date("Ymd"), 'kihan_post_id'=>Auth::id()], ['no'=>$_DATA['cust_info_no']]);
        }

        $result = Func::saveMemo($_DATA, $_DATA['mode'], "CUST_INFO_MEMO");
        
        if( $result == "Y" )
        {
            DB::commit();

            //return "정상적으로 처리되었습니다.";
            return 'Y';
        }
        else 
        {
            DB::rollback();
            
            if( $result == "AN" )
            {
                return "알람 등록 오류.";
                
            }
            else if( $result == "MN" )
            {
                return "등록 오류.";
            }
            else
            {
                return "데이터 오류.";
            }
        }
    }

    /*
        고객정보창 메모 등록, 수정, 삭제

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function custImportMemoAction(Request $request)
    {
        $arrayUserId        = Func::getUserId();

        $_DATA['important_memo']    = $request->important_memo;
        $_DATA['imemo_save_id']     = Auth::id();
        $_DATA['imemo_save_time']   = date('YmdHis');

        $rs = DB::table('cust_info')->where('no', $request->cust_info_no)
            ->update($_DATA);
        if( $rs )
        {
            $imemo['save_id'] = Func::getArrayName($arrayUserId, $_DATA['imemo_save_id']);
            $imemo['save_time'] = Func::dateFormat($_DATA['imemo_save_time']);
            $imemo['save_time'] = str_replace(" ", "<br>", substr($imemo['save_time'], 2));

            return $imemo;
        }
        else 
        {
            $imemo['save_id']='';
            return $imemo;
        }
    }
    

    /**
    * 부서별 주요 메모 화면 오픈
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function coMemo(Request $request)
    {
        $array_branch = Func::getBranchList();

        return view('erp.comemo') -> with("branch", $array_branch);
    }

    /**
    * 부서별 주요 메모 리스트
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function coMemoList(Request $request)
    {
        $selected_branch_memos = DB::TABLE("BRANCH_MEMO")->SELECT("*")->WHERE("save_status", 'Y')->WHERE("branch_code", $request->code)->ORDERBY('MEMO_ORDER','ASC')->ORDERBY('NO','DESC')->get()->toArray();
        $selected_branch_memos = Func::chungDec(["BRANCH_MEMO"], $selected_branch_memos);	// CHUNG DATABASE DECRYPT

        $mode = "INS";

        if( isset($request->no) )
        {
            foreach($selected_branch_memos as $idx => $v)
            {
                if( $request->no == $v->no )
                {
                    $selected_memo = $v;
                    $mode = "UPD";
                    break;
                }
            }
        }

        $array_branch = Func::getBranchList();

        return view('erp.comemolist')->with('branch_memo', $selected_branch_memos)
                                     ->with('branch_code', $request->code)
                                     ->with('selected_memo', isset($selected_memo)? $selected_memo : '')
                                     ->with('mode', $mode)
                                     ->with('array_branch', $array_branch);
    }

    /**
    * 부서별 주요 메모 action
    *
    * @param  \Illuminate\Http\Request  $request
    * @return json
    */
    public function coMemoAction(Request $request)
    {
        $_DATA = $request->input();

        if( $_DATA['mode'] != "DEL" && !empty($_DATA['no']) )
        {
            $_DATA['mode'] = "UPD";
        }
        elseif( $_DATA['mode'] != "DEL" && empty($_DATA['no']) )
        {
            $_DATA['mode'] = "INS";
        }

        if( $_DATA['memo_order'] < 0 || $_DATA['memo_order'] > 99 )
        {
            $result['msg'] = "순서는 0~99 까지만 가능합니다.";
            $result['code'] = $_DATA['branch_code'];

            return json_encode($result);
        }

        if($_DATA['mode'] == "INS")
        {
            //memo_order 세팅 
            unset($mOrder);
            $MAIN= DB::TABLE("branch_memo")->SELECT("memo_order")->WHERE("branch_code",$_DATA['branch_code'])->WHERE("save_status","Y")->ORDERBY("memo_order", "desc")->first();
            $mOrder = isset($MAIN->memo_order) ? $MAIN->memo_order : 0;
            $mOrder += 1;

            unset($_DATA['no']);
            $_DATA['save_id']     = Auth::id();
            $_DATA['save_time']   = date('YmdHis');
            $_DATA['save_status'] = "Y";
            $_DATA['memo_order'] = $mOrder;

            $rslt = DB::dataProcess($_DATA['mode'], "BRANCH_MEMO", $_DATA);
        }
        else if( $_DATA['mode'] == "UPD" )
        {
            $_DATA['save_id']     = Auth::id();
            $_DATA['save_time']   = date('YmdHis');
            $_DATA['save_status'] = "Y";

            $rslt = DB::dataProcess($_DATA['mode'], "BRANCH_MEMO", $_DATA);
        }
        else
        {
            $_DEL_DATA['mode']        = "UPD";
            $_DEL_DATA['no']          = $_DATA['no'];
            $_DEL_DATA['del_id']      = Auth::id();
            $_DEL_DATA['del_time']    = date('YmdHis');
            $_DEL_DATA['save_status'] = "N";

            $rslt = DB::dataProcess($_DEL_DATA['mode'], "BRANCH_MEMO", $_DEL_DATA);
        }
        

        if( $rslt == "Y" )
        {
            $result['msg'] = "정상적으로 처리되었습니다.";
            $result['code'] = $_DATA['branch_code'];
        }
        else
        {
            $result['msg'] = "데이터 오류";
            $result['code'] = $_DATA['branch_code'];
        }
        
        return json_encode($result);

    }

    
}




?>
