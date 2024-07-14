<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use DB;
use Log;
use Auth;
use Func;
use Hash;
use Vars;
use DataList;
use App\Chung\Paging;
use Illuminate\Support\Facades\Storage;

class VisitController extends Controller
{

/**
     * 고객정보 조회기록 공용 리스트 함수
     *
     * @param Request $request
     * @return DataList
     */
    private function setLogDataList($request) {
        $list = new DataList(Array("listName"=>"visitLog","listAction"=>'/'.$request->path()));

        $list->setTabs(Array(),$request->tabs);

        $list->setSearchDate('날짜검색', Array('save_time' => '조회 날짜'), 'searchDt', 'Y', 'N',date("Ymd"),date("Ymd"), 'save_time');

        $list->setSearchDetail(Array( 
            'loan_app_no'  => '신청번호',
            'cust_info_no'  => '회원번호',
            'work_type'  => '업무구분',
        ));

        return $list;
    }

    /**
     * 조회 기록
     *
     * @param Request $request
     * @return View visitLog
     */
    public function visitLog(Request $request) {
        $list = $this->setLogDataList($request);

        $list->setlistTitleCommon(Array
        (
            'no'              =>     Array('No', 0, '', 'center', '', 'no'),
            'save_time'       =>     Array('조회시간', 0, '', 'center', '', 'save_time'),
            'save_id'         =>     Array('조회직원', 0, '', 'center', '', 'save_id'),
            'loan_app_no'     =>     Array('신청번호', 0, '', 'center', '', 'loan_app_no'),
            'cust_info_no'    =>     Array('회원번호', 0, '', 'center', '', 'cust_info_no'),
            'work_type'       =>     Array('업무구분', 0, '', 'center', '', 'work_type'),
            'location'        =>     Array('접근위치', 0, '', 'center', '', 'location'),
        ));

        $tempArr = $list->getList();
        $list = new DataList($tempArr);

        return view('config.visitLog')->with("result", $list->getList());
    }

    /**
     * 조회 기록 데이터
     *
     * @param Request $request
     * @return Json 조회 기록 데이터
     */
    public function visitLogList(Request $request) {
        $list = $this->setLogDataList($request);
        $param = $request->all();

        // 기본쿼리
        $visitLogs = DB::TABLE("USERS_ACCESS_LOG");

        // 날짜검색.
        if(isset($param['searchDt']) && $param['searchDt']!='')
        {
            if(isset($param['searchDtString']) && $param['searchDtString']!='')
            {
                $visitLogs->where('LEFT('.$param['searchDt'].', 8)', '>=', str_replace('-', '', $param['searchDtString']));
                unset($param['searchDtString']);
            }   
            if(isset($param['searchDtStringEnd']) && $param['searchDtStringEnd']!='')
            {
                $visitLogs->where('LEFT('.$param['searchDt'].', 8)', '<=', str_replace('-', '', $param['searchDtStringEnd']));
                unset($param['searchDtStringEnd']);
            }   
        }
        
        $arrayName = Func::getUserId('');

        // 합계용 복사
        $r['incCheck'] = 'N';
        $totalCnt = 10;
        $SUM1 = clone $visitLogs;
        $SUM2 = clone $visitLogs;

        $sumParam = $param;
        $sumParam['listOrder'] = 'count(1)';
        $sumParam['listOrderAsc'] = 'desc';        

        $SUM1 = $list->getListQuery("users_access_log", 'main', $SUM1, $sumParam);
        $SUM2 = $list->getListQuery("users_access_log", 'main', $SUM2, $sumParam);

        $groupByCol = 'save_id, loan_app_no';
        $sum1 = $SUM1->groupBy($groupByCol)->select($groupByCol, 'count(1) as cnt')
                ->where('coalesce(loan_app_no, 0)', '>', 0)
                ->limit($totalCnt)->offset(0)->get();
        $cnt = 0;

        foreach($sum1 as $s)
        {
            $l_link = '<a class="hand" onClick="popUpFull(\'/ups/custpop?tabs=&no='.$s->loan_app_no.'\', \'loan_app'.$s->loan_app_no.'\')">';

            $r['incSum']['loan_app_save_id_'.$cnt] = Func::getArrayName($arrayName, $s->save_id);
            $r['incSum']['loan_app_no_'.$cnt] = $l_link.$s->loan_app_no.'</a>';
            $r['incSum']['loan_app_cnt_'.$cnt] = $s->cnt;
            $cnt ++;
        }
        for($cnt; $cnt<$totalCnt; $cnt++)
        {
            $r['incSum']['loan_app_save_id_'.$cnt] = $r['incSum']['loan_app_no_'.$cnt] = $r['incSum']['loan_app_cnt_'.$cnt] = '';
        }

        $groupByCol = 'save_id, cust_info_no ';
        $sum2 = $SUM2->groupBy($groupByCol)->select($groupByCol, 'count(1) as cnt', 'max(loan_info_no) as lno')
                ->where('coalesce(cust_info_no, 0)', '>', 0)
                ->limit(10)->offset(0)->get();
        $cnt = 0;
        foreach($sum2 as $s)
        {
            $c_link = '<a class="hand" onClick="popUpFull(\'/erp/custpop?cust_info_no='.$s->cust_info_no.'&no='.$s->lno.'\')">';

            $r['incSum']['cust_info_save_id_'.$cnt] = Func::getArrayName($arrayName, $s->save_id);
            $r['incSum']['cust_info_no_'.$cnt] = $c_link.$s->cust_info_no.'</a>';
            $r['incSum']['cust_info_cnt_'.$cnt] = $s->cnt;
            $cnt ++;
        }
        for($cnt; $cnt<$totalCnt; $cnt++)
        {
            $r['incSum']['cust_info_save_id_'.$cnt] = $r['incSum']['cust_info_no_'.$cnt] = $r['incSum']['cust_info_cnt_'.$cnt] = '';
        }


        $visitLogs->SELECT("*");

        if (!isset($param['listOrder'])) {
            $param['listOrder']     = 'no';
            $param['listOrderAsc']  = 'desc';
        }
        
        $visitLogs = $list->getListQuery("users_access_log",'main',$visitLogs,$param);
                
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($visitLogs, $request->page, $request->listLimit, 10);
		$result = $visitLogs->get();
        $result = Func::chungDec(["USERS_ACCESS_LOG"], $result);	// CHUNG DATABASE DECRYPT

		// 뷰단 데이터 정리.
        $cnt = 0;
		foreach ($result as $v)
		{

            $v->save_time = Func::dateFormat($v->save_time);
            $v->save_id   = Func::getArrayName($arrayName, $v->save_id);

            if($v->loan_app_no == 0){
                $v->loan_app_no = '';
            }
            if($v->cust_info_no == 0){
                $v->cust_info_no = '';
            }

            $l_link = '<a class="hand" onClick="popUpFull(\'/ups/custpop?tabs=&no='.$v->loan_app_no.'\', \'loan_app'.$v->loan_app_no.'\')">';
            $v->loan_app_no = $l_link.$v->loan_app_no.'</a>';

            $c_link = '<a class="hand" onClick="popUpFull(\'/erp/custpop?cust_info_no='.$v->cust_info_no.'&no='.$v->loan_info_no.'\')">';
            $v->cust_info_no = $c_link.$v->cust_info_no.'</a>';

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