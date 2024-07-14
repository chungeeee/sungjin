<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use App\Chung\Loan;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;
use Redirect;
use DataList;
use App\Chung\Paging;
use App\Chung\Vars;
use ExcelFunc;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;

class ConditionController extends Controller
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
        $list   = new DataList(Array("listName"=>"condition","listAction"=>'/'.$request->path()));
 
        if(!isset($request->tabs)) $request->tabs = 'A';
        $arr_status = Vars::$arrayConditionStatus;
        
        $list->setTabs($arr_status, $request->tabs);
        
        $list->setCheckBox("no");

        $date_array = array(
            'lc.app_date'       => '신청일',        'old_return_date'   => '전상환일',      'new_return_date'       => '후상환일',
            'confirm_date'      => '결재일',        'basis_date'        => '반영기준일',
        );
        $list->setSearchDate('날짜검색',$date_array,'searchDt','Y');
        
        $range_array = array(
            'old_rate'                  =>  '전이율',           'old_delay_rate'            =>  '전연체이율',
            'new_rate'                  =>  '후이율',           'new_delay_rate'            =>  '후연체이율',
            'old_contract_day'          =>  '전약정일',         'new_contract_day'          =>  '후약정일',
            'old_monthly_return_money'  =>  '전월상환액',       'new_monthly_return_money'  =>  '후월상환액',
        );
        $list->setRangeSearchDetail($range_array,'','','');

        if( Func::funcCheckPermit("C122") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/erp/conditionexcel', 'form_condition')", "btn-success");
        }

        if( Func::funcCheckPermit("E004") || Func::funcCheckPermit("E031") )
        {
            $list->setSearchType('l-manager_code', Func::myPermitBranch(), '관리지점');
        }

        $list->setSearchDetail(array(
            'lc.loan_info_no' => '계약번호', 
            'lc.cust_info_no' => '차입자번호', 
            'c.name' => '이름'
        ));

        $list->setPlusButton("conditionPopup();");

        return $list;
    }
    

    /**
     * 조건변경 메인화면
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function condition(Request $request)
    {
        $list   = $this->setDataList($request);

        // 결재단계 변경작업으로 인해 일단 주석처리
        // $list->setLumpForm('lumpRateDown',      Array('BTN_NAME'=>'일괄금리인하','BTN_ACTION'=>'lumpRateDownForm();','BTN_ICON'=>'','BTN_COLOR'=>''));

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $list->setlistTitleCommon( Array
        (
            'no'                        =>     array('순번', 0, '', 'center', '', 'no'),
            'cust_info_no'              =>     array('차입자번호', 0, '', 'center', '', 'cust_info_no'),
            'loan_info_no'              =>     array('계약번호', 0, '', 'center', '', 'loan_info_no'),
            'name'                      =>     array('이름', 0, '', 'center', '', 'name'),
            'ssn'                       =>     array('생년월일', 0, '', 'center', '', 'ssn'),
            'manager_name'              =>     Array('관리지점', 0, '', 'center', '', 'l.manager_code'),
            'app_date'                  =>     array('요청일', 0, '', 'center', '', 'lc.app_date'),
            'app_name'                  =>     array('담당자', 0, '', 'center', '', 'app_id'),
            /*
            'old_rate'                  =>     array('전금리', 0, '', 'center', '', 'old_rate', array('old_delay_rate' => array('연체금리', 'old_delay_rate', ' / '))),
            'new_rate'                  =>     array('후금리', 0, '', 'center', '', 'new_rate', array('new_delay_rate' => array('연체금리', 'new_delay_rate', ' / '))),
            'rate_all_chg_yn'           =>     array('다계좌', 0, '', 'center', '', 'rate_all_chg_yn'),

            'old_contract_day'          =>     array('전약정일', 0, '', 'center', '', 'old_contract_day'),
            'new_contract_day'          =>     array('후약정일', 0, '', 'center', '', 'new_contract_day'),
            'cday_all_chg_yn'           =>     array('다계좌', 0, '', 'center', '', 'cday_all_chg_yn'),

            'old_monthly_return_money'  =>     array('전월상환액', 0, '', 'center', '', 'old_monthly_return_money'),
            'new_monthly_return_money'  =>     array('후월상환액', 0, '', 'center', '', 'new_monthly_return_money'),
            'rmoney_all_chg_yn'         =>     array('다계좌', 0, '', 'center', '', 'rmoney_all_chg_yn'),

            'old_return_date'           =>     array('전상환일', 0, '', 'center', '', 'old_return_date'),
            'new_return_date'           =>     array('후상환일', 0, '', 'center', '', 'new_return_date'),
            'rdate_all_chg_yn'          =>     array('다계좌', 0, '', 'center', '', 'rdate_all_chg_yn'),
            */
            'rate_chg'                  =>     array('금리변경', 0, '', 'center', '', 'new_rate'),
            'cday_chg'                  =>     array('약정일변경', 0, '', 'center', '', 'new_contract_day'),
            'rtnd_chg'                  =>     array('상환일변경', 0, '', 'center', '', 'new_return_date'),
            'mrmy_chg'                  =>     array('월상환액변경', 0, '', 'center', '', 'new_monthly_return_money'),

            'status'                    =>     array('결재진행상태', 0, '', 'center', '', 'lc.status'),
            'confirm_date'              =>     array('결재일', 0, '', 'center', '', 'confirm_date'),
            'basis_date'                =>     array('반영기준일', 0, '', 'center', '', 'basis_date'),
        ));

        return view('erp.condition')->with('result', $list->getList());
    }

    /**
     * 조건변경 리스트 출력
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function conditionList(Request $request)
    {
        $request->isDebug = true;
        
        $list   = $this->setDataList($request);

        $param  = $request->all();

            // Tab count 
        if($request->isFirst=='1')
        {
            $BOXC = DB::TABLE("loan_condition")->JOIN("loan_info", "loan_condition.loan_info_no", "loan_info.no");
            $BOXC = $BOXC->SELECT(DB::RAW("
            coalesce(sum(case when loan_condition.status='A' then 1 else 0 end), 0) as a,
            coalesce(sum(case when loan_condition.status='B' then 1 else 0 end), 0) as b, 
            coalesce(sum(case when loan_condition.status='C' then 1 else 0 end), 0) as c, 
            coalesce(sum(case when loan_condition.status='Y' then 1 else 0 end), 0) as y, 
            coalesce(sum(case when loan_condition.status='X' then 1 else 0 end), 0) as x 
                    "));

            // 전지점 조회권한 없으면 자기 지점만
            if( !Func::funcCheckPermit("E004") )
            {
                $BOXC->WHEREIN('loan_info.manager_code', array_keys(Func::myPermitBranch()));
            }
            $count = $BOXC->FIRST();
            $r['tabCount'] = array_change_key_case((Array)$count, CASE_UPPER);
        }

        // 기본쿼리
        $conditions = DB::TABLE("loan_condition lc");
        $conditions->LEFTJOIN("loan_condition_data lcd", [["lc.no", "=", "lcd.loan_condition_no"], ["lc.loan_info_no", "=", "lcd.loan_info_no"]]);
        $conditions->LEFTJOIN("cust_info c", [["lc.cust_info_no", "=", "c.no"]]);
        $conditions->LEFTJOIN("loan_info l", [["lc.loan_info_no", "=", "l.no"]]);

        $conditions->SELECT("lc.no", "lc.cust_info_no", "lc.loan_info_no", "lc.app_date", "lc.app_id", "lc.rate_all_chg_yn", "lc.cday_all_chg_yn", "lc.rmoney_all_chg_yn", "lc.rdate_all_chg_yn", "lc.status", "lc.confirm_date", "lc.basis_date", "lc.condition_bit");
        $conditions->ADDSELECT("lcd.old_rate", "lcd.old_delay_rate", "lcd.new_rate", "lcd.new_delay_rate", "lcd.old_contract_day", "lcd.new_contract_day", "lcd.old_monthly_return_money", "lcd.new_monthly_return_money","lcd.old_return_date","lcd.new_return_date");
        $conditions->ADDSELECT("c.name, l.manager_code, c.ssn");


		if( $request->tabsSelect=="A" || $request->tabsSelect=="R" )
		{
            // if( $request->tabsSelect=="A" )
            // {
            //     $conditions->WHERE('lc.CONDITION_BIT', '!=', 'R');
            // }
            // else
            // {
            //     $conditions->WHERE('lc.CONDITION_BIT', 'R');
            // }
            $param['tabSelectNm'] = "lc.status";
            $param['tabsSelect']  = Array('A');
        }
		else
		{
            $param['tabSelectNm'] = "lc.status";
            $param['tabsSelect']  = $request->tabsSelect;
        }

        // 전지점 조회권한 없으면 자기 지점만
        if( !Func::funcCheckPermit("E004") )
        {
            $conditions->WHEREIN('l.manager_code', array_keys(Func::myPermitBranch()));
        }
        $conditions = $list->getListQuery("lc",'main',$conditions,$param);

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($conditions, $request->page, $request->listLimit, 10, $request->listName);

        $rslt = $conditions->GET();
        $rslt = Func::chungDec(["LOAN_CONDITION","LOAN_CONDITION_DATA","CUST_INFO","LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $arr_status = Vars::$arrayConditionStatus;
        $arrManager = Func::getUserList();
        $arrBranch  = Func::getBranch();
        
        $cnt = 0;
        foreach ($rslt as $v) 
        {
            $url = "'/erp/conditionpop?loan_info_no=" . $v->loan_info_no . "&no=" . $v->no . "'";

            $v->loan_info_no             = '<a onclick="loan_info_pop( '.$v->cust_info_no.', '.$v->loan_info_no.' );" style="cursor: pointer;" class="text-primary">'.$v->loan_info_no.'</a>';
            $v->name                     = '<a onclick="' . 'window.open( ' . $url . ", 'conditionpop', 'left=0, top=0, height=800,width=1300, fullscreen=yes' );" . '"' . " style='cursor: pointer;' class='text-primary'>" . $v->name . "</a>";

            $v->app_date                 = Func::dateformat($v->app_date);
            $v->app_name                 = Func::nvl($arrManager[$v->app_id]->name, $v->app_id);
            $v->manager_name             = Func::nvl($arrBranch[$v->manager_code], $v->manager_code);

            $v->ssn                      = substr($v->ssn,0,6)."-".substr($v->ssn,6,1);
            /*
            $v->old_rate                 = number_format( (float) $v->old_rate, 2);
            $v->old_delay_rate           = number_format( (float) $v->old_delay_rate, 2);
            $v->new_rate                 = number_format( (float) $v->new_rate, 2);
            $v->new_delay_rate           = number_format( (float) $v->new_delay_rate, 2);
            $v->rate_all_chg_yn          = (isset($v->rate_all_chg_yn) && $v->rate_all_chg_yn == "Y") ? "다계좌" : "-";

            $v->old_contract_day         = Func::dateformat($v->old_contract_day);
            $v->new_contract_day         = Func::dateformat($v->new_contract_day);
            $v->cday_all_chg_yn          = (isset($v->cday_all_chg_yn) && $v->cday_all_chg_yn == "Y") ? "다계좌" : "-";

            $v->old_monthly_return_money = !empty($v->old_monthly_return_money) ? number_format((int)$v->old_monthly_return_money) : "-";
            $v->new_monthly_return_money = !empty($v->new_monthly_return_money) ? number_format((int)$v->new_monthly_return_money) : "-";
            $v->rmoney_all_chg_yn        = (isset($v->rmoney_all_chg_yn) && $v->rmoney_all_chg_yn == "Y") ? "다계좌" : "-";

            $v->old_return_date          = Func::dateformat($v->old_return_date);
            $v->new_return_date          = Func::dateformat($v->new_return_date);
            $v->rdate_all_chg_yn         = (isset($v->rdate_all_chg_yn) && $v->rdate_all_chg_yn == "Y") ? "다계좌" : "-";

            if( strpos($v->condition_bit, "R")===false )
            {
                $v->new_rate = "-";
                $v->new_delay_rate = "-";
            }
            if( strpos($v->condition_bit, "C") === false )
            {
                $v->new_contract_day = "-";
            }
            if( strpos($v->condition_bit, "D") === false )
            {
                $v->new_return_date  = "-";
            }
            */



            // ." <i class='fas fa-arrow-right ml-1 mr-1 mt-0 mb-0'></i> "
            // 'rate_chg'                  =>     array('금리변경', 0, '', 'center', '', 'new_rate'),
            // 'cday_chg'                  =>     array('약정일변경', 0, '', 'center', '', 'new_contract_day'),
            // 'rtnd_chg'                  =>     array('상환일변경', 0, '', 'center', '', 'new_return_date'),
            // 'mrmy_chg'                  =>     array('월상환액변경', 0, '', 'center', '', 'new_monthly_return_money'),

            // 금리
            if( substr_count($v->condition_bit, "R")>0 )
            {
                $v->rate_chg  = number_format( (float) $v->old_rate, 2 )."/".number_format( (float) $v->old_delay_rate, 2 )." %";
                $v->rate_chg .= "<i class='fas fa-arrow-right ml-1 mr-1 mt-0 mb-0'></i>";
                $v->rate_chg .= number_format( (float) $v->new_rate, 2 )."/".number_format( (float) $v->new_delay_rate, 2 )." %";
            }
            else
            {
                $v->rate_chg  = "";
            }
            // 약정일
            if( substr_count($v->condition_bit, "C")>0 )
            {
                $v->cday_chg  = $v->old_contract_day."일";
                $v->cday_chg .= "<i class='fas fa-arrow-right ml-1 mr-1 mt-0 mb-0'></i>";
                $v->cday_chg .= $v->new_contract_day."일";
            }
            else
            {
                $v->cday_chg  = "";
            }
            // 상환일
            if( substr_count($v->condition_bit, "D")>0 )
            {
                $v->rtnd_chg  = Func::dateFormat($v->old_return_date);
                $v->rtnd_chg .= "<i class='fas fa-arrow-right ml-1 mr-1 mt-0 mb-0'></i>";
                $v->rtnd_chg .= Func::dateFormat($v->new_return_date);
            }
            else
            {
                $v->rtnd_chg  = "";
            }
            // 월상환액
            if( substr_count($v->condition_bit, "M")>0 )
            {
                $v->mrmy_chg  = number_format($v->old_monthly_return_money)."원";
                $v->mrmy_chg .= "<i class='fas fa-arrow-right ml-1 mr-1 mt-0 mb-0'></i>";
                $v->mrmy_chg .= number_format($v->new_monthly_return_money)."원";
            }
            else
            {
                $v->mrmy_chg  = "";
            }



            $v->confirm_date = Func::dateformat($v->confirm_date);
            $v->basis_date   = Func::dateformat($v->basis_date);
            $v->status       = Func::nvl($arr_status[$v->status], $v->status);

            $r['v'][] = $v;
            $cnt++;
        }

        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path());

        $r['result'] = 1;
        $r['txt'] = $cnt;

        return json_encode($r);
    }

    /**
     * 조건변경 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function conditionExcel(Request $request)
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
        $conditions = DB::TABLE("loan_condition lc");
        $conditions->LEFTJOIN("loan_condition_data lcd", [["lc.no", "=", "lcd.loan_condition_no"], ["lc.loan_info_no", "=", "lcd.loan_info_no"]]);
        $conditions->LEFTJOIN("cust_info c", [["lc.cust_info_no", "=", "c.no"]]);
        $conditions->LEFTJOIN("loan_info l", [["lc.loan_info_no", "=", "l.no"]]);

        $conditions->SELECT("lc.no", "lc.cust_info_no", "lc.loan_info_no", "lc.app_date", "lc.app_id", "lc.rate_all_chg_yn", "lc.cday_all_chg_yn", "lc.rmoney_all_chg_yn", "lc.rdate_all_chg_yn", "lc.status", "lc.confirm_date", "lc.basis_date", "lc.condition_bit");
        $conditions->ADDSELECT("lcd.old_rate", "lcd.old_delay_rate", "lcd.new_rate", "lcd.new_delay_rate", "lcd.old_contract_day", "lcd.new_contract_day", "lcd.old_monthly_return_money", "lcd.new_monthly_return_money","lcd.old_return_date","lcd.new_return_date");
        $conditions->ADDSELECT("c.name, l.manager_code, c.ssn");

		if( $request->tabsSelect=="A" || $request->tabsSelect=="R" )
		{
            if( $request->tabsSelect=="A" )
            {
                $conditions->WHERE('lc.condition_bit', '!=', 'R');
            }
            else
            {
                $conditions->WHERE('lc.condition_bit', 'R');
            }
            $param['tabSelectNm'] = "lc.status";
            $param['tabsSelect']  = Array('A');
        }
		else
		{
            $param['tabSelectNm'] = "lc.status";
            $param['tabsSelect']  = $request->tabsSelect;
        }

        // 전지점 조회권한 없으면 자기 지점만
        if( !Func::funcCheckPermit("E004") && !isset($request->excel_flag) )
        {
            $conditions->WHEREIN('l.manager_code', array_keys(Func::myPermitBranch()));
        }

        $conditions = $list->getListQuery("lc",'main',$conditions,$param);

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($conditions, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($conditions);
        log::info($query);
        $file_name    = "조건변경결재_".date("YmdHis").'_'.Auth::id().'.xlsx';
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

        $rslt = $conditions->GET();
        $rslt = Func::chungDec(["LOAN_CONDITION","LOAN_CONDITION_DATA","CUST_INFO","LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        // 엑셀 헤더
		$excel_header = array('일련번호','차입자번호','계약번호','이름','생년월일','관리지점','요청일','담당자','전금리','전연체금리','후금리','후연체금리','전약정일','후약정일','전월상환액','후월상환액','전상환일','후상환일','결재진행상태','결재일','반영기준일',);
        $excel_data = [];
        
        // 뷰단 데이터 정리.
        $arr_status = Vars::$arrayConditionStatus;
        $arrManager = Func::getUserList();
        $arrBranch  = Func::getBranch();
        
        foreach ($rslt as $v)
        {
            $array_data = Array(
                $v->no,
                Func::addCi($v->cust_info_no),
                $v->loan_info_no,
                $v->name,
                substr($v->ssn,0,6)."-".substr($v->ssn,6,1),
                Func::nvl($arrBranch[$v->manager_code], $v->manager_code),
                Func::dateformat($v->app_date),

                Func::nvl($arrManager[$v->app_id]->name, ''),
                
                !empty($v->old_rate) ?  (float) $v->old_rate : "",
                !empty($v->old_delay_rate) ? (float) $v->old_delay_rate : "",
                !empty($v->new_rate) ?  (float) $v->new_rate : "",
                !empty($v->new_delay_rate) ?  (float) $v->new_delay_rate : "",
                !empty($v->old_contract_day) ? Func::dateformat($v->old_contract_day) : "",
                !empty($v->new_contract_day) ? Func::dateformat($v->new_contract_day) : "",
                !empty($v->old_monthly_return_money) ? (int)$v->old_monthly_return_money : "",
                !empty($v->new_monthly_return_money) ? (int)$v->new_monthly_return_money : "",
                !empty($v->old_return_date) ? Func::dateformat($v->old_return_date) : "",
                !empty($v->new_return_date) ? Func::dateformat($v->new_return_date) : "",
                Func::nvl($arr_status[$v->status], $v->status),
                !empty($v->confirm_date) ? Func::dateformat($v->confirm_date) : "",
                !empty($v->basis_date) ? Func::dateformat($v->basis_date) : "",
            );

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
     * 조건변경 팝업 출력
     *
     * @return view
     */
    public function conditionPop(Request $request)
    {
        $mode = "UPD";
        $setChk = "Y";
        $parent_code = Func::getBranchList()[Auth::user()->branch_code]['parent_code'];
        $confirm_level = 0;


        if(isset($request->loan_info_no))
        {
            $manager_code = DB::table("loan_info")->where("no",$request->loan_info_no)->value("manager_code");
        }


        if( isset($request->no) )
        {
            $condition = DB::TABLE("loan_condition lc")
                            ->LEFTJOIN("loan_condition_data lcd", [["lc.no", "=", "lcd.loan_condition_no"],["lc.loan_info_no", "=", "lcd.loan_info_no"]])
                            ->LEFTJOIN("cust_info c", "lc.cust_info_no", "c.no")
                            ->SELECT("lc.*","lcd.*")
                            ->ADDSELECT("c.name")
                            ->WHERE("lc.no", $request->no)->get()->toArray();
            $condition = Func::chungDec(["loan_condition","loan_condition_data","cust_info"], $condition);	// CHUNG DATABASE DECRYPT

            // 조건변경별 결재단계, 결재자세팅 
            $arr_permit_no  = ['C'=>'01','R1'=>'02','R2'=>'03','M'=>'04','D'=>'05'];
            $condition_bit  = substr($condition[0]->condition_bit,0,1); // 로직변경 전 조건변경 두가지 선택된 경우를 대비해 한글자로 잘라야함
            $new_rate       = $condition[0]->new_rate!=""? $condition[0]->new_rate:0;

            if($condition_bit == "R")
            {
                $condition_bit = $new_rate>=20?"R1":"R2";
            }
            
            $arr_confirm_id = Func::getArrConfirmId($arr_permit_no,$request->loan_info_no)[$condition_bit];
            $len =  sizeof($arr_confirm_id);
            $i = 0;

            foreach($arr_confirm_id as $col => $arr_id)
            {
                $i++;
                if($col =="app_id")
                {
                    continue;
                }
                if($col == "confirm_id_1" && $i != $len)
                {
                    $confirm_str[$col] = "1차결재자";
                }
                else if($col == "confirm_id_2" && $i != $len)
                {
                    $confirm_str[$col] = "2차결재자";
                }
                else
                {
                    $confirm_str[$col]= "최종결재자";
                }
                $option_str[$col] = "<option value=''>".$confirm_str[$col]."</option>".Func::printOption($arr_id,Func::nvl($condition[0]->$col,''),false);
            }

            if(isset($option_str))
            {
                $condition[0]->option_str  = $option_str;
                $condition[0]->confirm_str = $confirm_str;
                $confirm_level = sizeof($option_str);
            }
        }
        else if( isset($request->loan_info_no) )
        {
            // -노현정부장 요청 : 운영데이터 넣는시점만 제외처리
            $condition = DB::TABLE("loan_condition lc")
                            ->LEFTJOIN("loan_condition_data lcd", [["lc.no", "=", "lcd.loan_condition_no"],["lc.loan_info_no", "=", "lcd.loan_info_no"]])
                            ->LEFTJOIN("cust_info c", "lc.cust_info_no", "c.no")
                            ->SELECT("lc.*", "lcd.*", "c.name")
                            ->WHERE("lc.loan_info_no", $request->loan_info_no)
                            // ->WHERE("lc.status","A")
                            ->ORDERBY("lc.no","desc")
                            ->GET()
                            ->toArray();
            $condition = Func::chungDec(["loan_condition","loan_condition_data","cust_info"], $condition);	// CHUNG DATABASE DECRYPT
                            
            if( count($condition) == 0)
            {
                // -노현정부장 요청 : 운영데이터 넣는시점만 제외처리
                $mode = "INS";
                $condition = DB::TABLE('loan_info')
                                ->LEFTJOIN("cust_info", "loan_info.cust_info_no", "cust_info.no")
                                ->SELECT('loan_info.no as loan_info_no', 'loan_info.cust_info_no', 'loan_info.return_method_cd', 'loan_info.loan_rate as old_rate', 'loan_info.loan_delay_rate as old_delay_rate')
                                ->ADDSELECT('loan_info.contract_day as old_contract_day', 'loan_info.monthly_return_money as old_monthly_return_money', 'loan_info.return_date as old_return_date')
                                ->ADDSELECT('cust_info.name')
                                ->WHERE('loan_info.save_status', 'Y')
                                ->WHERE('loan_info.no',          $request->loan_info_no)
                                // ->WHEREIN('loan_info.status',['A'])
                                ->get()
                                ->toArray(); // 2021.10.08 정상건만 가능하도록 요청함 
                $condition = Func::chungDec(["loan_info","cust_info"], $condition);	// CHUNG DATABASE DECRYPT

                if(empty($condition))
                {
                    echo "<script> alert('조건 변경 불가 계약 건 입니다.');</script>";
                    
                    return view('erp.conditionPop');
                }

                $setChk = "N";

            }
            // 조건변경별 결재단계, 결재자세팅 
            $arr_permit_no  = ['C'=>'01','R1'=>'02','R2'=>'03','M'=>'04','D'=>'05'];
            $arr_confirm_id = Func::getArrConfirmId($arr_permit_no,$request->loan_info_no); // 약정일변경
        }
        else
        {
            return view('erp.conditionPop');
        }

        //  데이터 정리
        $condition[0]->old_rate                   = $condition[0]->old_rate!=""? number_format($condition[0]->old_rate, 2, '.', '') : "";
        $condition[0]->old_delay_rate             = $condition[0]->old_delay_rate!=""? number_format($condition[0]->old_delay_rate, 2, '.', '') : "";
        $condition[0]->old_monthly_return_money   = $condition[0]->old_monthly_return_money!=""? number_format($condition[0]->old_monthly_return_money) : "";
        $condition[0]->old_return_date            = Func::dateformat($condition[0]->old_return_date);
        $condition[0]->loan_info_no               = $request->loan_info_no;
        
        if( isset($setChk) && $setChk == "Y" )
        {
            $condition[0]->new_rate                   = $condition[0]->new_rate!=""? number_format($condition[0]->new_rate, 2, '.', '') : "";
            $condition[0]->new_delay_rate             = $condition[0]->new_delay_rate!=""? number_format($condition[0]->new_delay_rate, 2, '.', '') : "";
            $condition[0]->new_monthly_return_money   = $condition[0]->new_monthly_return_money!=""? number_format($condition[0]->new_monthly_return_money) : "";
            $condition[0]->new_return_date            = Func::dateformat($condition[0]->new_return_date);
        }

        if( !empty($condition[0]->basis_date) ) 
        {
            $basis_date = $condition[0]->basis_date;
        }
        
        $arr_memo_div = array_flip(Func::getConfigArr('memo_div'));
        $arr_contract_day = Func::getConfigArr('contract_day');


        //  조건변경 결재상태 셋팅
        $condition[0]->status = Func::nvl($condition[0]->status, '');
        $nowStatus  = ($condition[0]->status!='')? Func::getArrayName(Vars::$arrayConditionStatus, $condition[0]->status)."유지" : null; 
        $arr_status = (isset(Vars::$arrayConditionActionStatus[$condition[0]->status])) ? Vars::$arrayConditionActionStatus[$condition[0]->status]:null;



        //  조건변경 로그
        $memos = DB::TABLE("cust_info_memo")->SELECT("*")->WHERE("save_status","Y")->WHERE("cust_info_no", $condition[0]->cust_info_no)->WHERE("div",$arr_memo_div['조건변경'])->ORDERBY('no', 'desc')->get()->toArray();
        $memos = Func::chungDec(["CUST_INFO_MEMO"], $memos);	// CHUNG DATABASE DECRYPT

        //  계약리스트 
        $loan = (Array) DB::TABLE("loan_info")->SELECT("*")->WHERE("save_status", "Y")->WHERE("cust_info_no", $condition[0]->cust_info_no)->whereIn("status",['A','B'])->get()->toArray();
        $loan = Func::chungDec(["LOAN_INFO"], $loan);	// CHUNG DATABASE DECRYPT
        
        $array_config = Func::getConfigArr();
        $array_return_method = $array_config['return_method_cd'];

        foreach($loan as $idx => $v)
        {
            $loan[$idx]->return_method_nm = $array_return_method[$loan[$idx]->return_method_cd];
            $loan[$idx]->return_fee_nm    = Func::nvl($array_config['return_fee_cd'][$loan[$idx]->return_fee_cd]   , $loan[$idx]->return_fee_cd);
            
            $loan[$idx] = (Array) $loan[$idx];

            if( $v->no == $request->loan_info_no && empty($condition[0]->basis_date) ) $basis_date = $v->take_date;
            if( $v->no == $request->loan_info_no ) $return_method_cd = $v->return_method_cd;
        }



        return view('erp.conditionPop')->with(["mode"             => $mode, 
                                                "condition"        => $condition[0], 
                                                "confirm_level"    => $confirm_level, 
                                                "memos"            => $memos, 
                                                "arr_status"       => $arr_status, 
                                                "nowStatus"        => $nowStatus,
                                                "arr_contract_day" => $arr_contract_day,
                                                "arr_confirm_id"   => $arr_confirm_id,
                                                "basis_date"       => isset($basis_date) ? $basis_date : "",
                                                "return_method_cd" => isset($return_method_cd) ? $return_method_cd : "",
                                                "simple"           => $loan,]);
    }

    /**
     * 조건변경 액션
     *
     * @return view
     */
    public function conditionPopAction(Request $request)
    {
        $_DATA = $request->all();
        
        //  이전 데이터 -> 새로 셋팅 해준다.
        $li = DB::TABLE("loan_info")->SELECT("*")->WHERE("no",$_DATA['loan_info_no'])->first();
        $li = Func::chungDec(["LOAN_INFO"], $li);	// CHUNG DATABASE DECRYPT
        
        $_DATA['old_rate']                 = (float)$li->loan_rate;
        $_DATA['old_delay_rate']           = (float)$li->loan_delay_rate;
        $_DATA['old_contract_day']         = $li->contract_day;
        $_DATA['old_monthly_return_money'] = $li->monthly_return_money;
        $_DATA['old_return_date']          = $li->return_date;
        $next_confirm_id = "";
        if( $_DATA['mode'] != "INS" )
        {
            $condition = DB::TABLE("loan_condition")->SELECT("*")->WHERE("no", $_DATA['no'])->first();
            $condition = Func::chungDec(["LOAN_CONDITION"], $condition);	// CHUNG DATABASE DECRYPT

            $_DATA['old_status'] = $condition->status;
        }
        // 쪽지알림을 위해 필요함 
        if(!empty($condition))
        {
            $arr_confirm_lv   = ["A"=>0,"B"=>1,"C"=>2,"Y"=>3,"X"=>5,"D"=>5]; // 취소나 거절시는 아이디 세팅 필요없음
            $status_div = $request->status==$request->old_status||$request->status=="X"?$request->old_status:$request->status; //  수정,거절일때는 DB결재상태로 다음결재자 지정, 수정아닐때는 현재 지정된 결재상태로 다음결재자 지정해야함
            $next_confirm_lv  = $arr_confirm_lv[$status_div]+1; 
            $next_confirm_str = 'confirm_id_'.$next_confirm_lv; // 다음결재자 컬럼
            
            if($next_confirm_lv<=3 && !empty($request->$next_confirm_str) && $condition->$next_confirm_str != $request->$next_confirm_str) 
            {
                $next_confirm_id = isset($request->$next_confirm_str)?$request->$next_confirm_str:"";
            }
        }
        else
        {
            $next_confirm_id = isset($request->confirm_id_1)?$request->confirm_id_1:"";
        }

        if(isset($_DATA['status']) && $_DATA['status'] == "A" && (((!Func::funcCheckPermit("A102","A") || !Func::funcCheckPermit("A103","A")) && in_array("R", $_DATA['bit'])) || (!Func::funcCheckPermit("A101","A") && in_array("C", $_DATA['bit'])) || (!Func::funcCheckPermit("A105","A") && in_array("D", $_DATA['bit'])) || (!Func::funcCheckPermit("A104","A") && in_array("M", $_DATA['bit']))))
        {
            return Redirect::back()->with('result', '조건 변경 요청 불가. \\n요청 권한이 없습니다.');
        }

        // if( $_DATA['status'] == "Y" || $_DATA['status'] == "X" )
        if( isset($_DATA['status']) && $_DATA['status'] != "A" )
        {
            if( !Func::funcCheckPermit("C010") || (((!Func::funcCheckPermit("A202","A") || !Func::funcCheckPermit("A203","A")) && in_array("R", $_DATA['bit'])) || (!Func::funcCheckPermit("A201","A") && in_array("C", $_DATA['bit'])) || (!Func::funcCheckPermit("A205","A") && in_array("D", $_DATA['bit']))) )
            {
                return Redirect::back()->with('result', '조건 변경 결재 불가. \\n결재 권한이 없습니다.');
            }
            if($request->status != $request->old_status)
            {
                if($request->status == "B")
                {
                    $_DATA['confirm_date_1'] = date("Ymd");
                    $next_confirm_id = $request->$next_confirm_str;
                    // if(!isset($request->confirm_id_1) || $request->confirm_id_1!=Auth::id()) 
                    // {
                    //     return Redirect::back()->with('result', "선택된 1차결재자가 아닙니다");
                    // }
                }
                else if($request->status == "C" )
                {
                    $_DATA['confirm_date_2'] = date("Ymd");
                    $next_confirm_id = $request->$next_confirm_str;
                    // if(!isset($request->confirm_id_2) || $request->confirm_id_2!=Auth::id()) 
                    // {
                    //     return Redirect::back()->with('result', "선택된 2차결재자가 아닙니다");
                    // }
                }
                else if($request->status == "Y") // 결재완료
                {
                    if(!empty($request->confirm_level))
                    {
                        $confirm_id_str = 'confirm_id_'.$request->confirm_level;
                        $_DATA['confirm_date_'.$request->confirm_level] = date("Ymd");
                        // if(!isset($request->$confirm_id_str) || $request->$confirm_id_str!=Auth::id()) 
                        // {
                        //     return Redirect::back()->with('result', "선택된 최종결재자가 아닙니다");
                        // }
                    }

                }
            }
        }


        //  반영완료 후 데이터 수정불가 (거절은 가능)
        //  2021-12-15 취소상태 추가
        if( $_DATA['old_status'] == "Y" &&  !($_DATA['status'] == "X" || $_DATA['status'] == "D") )
        {
            return Redirect::back()->with('result', '반영완료 후 데이터 수정 불가. \\n거절 후 재접수 바랍니다.');
        }

        if( empty($_DATA['old_status']) && empty($_DATA['status']) )
        {
            return Redirect::back()->with('result', '결재 상태를 선택해주세요.');
        }

        //  이미 한 번 거절된 건은 수정 불가.
        //  2021-12-15 이미 한 번 취소된 건은 수정 불가.
        if( $_DATA['old_status'] == "X" || $_DATA['old_status'] == "D" )
        {
            return Redirect::back()->with('result', '거절한 데이터는 수정할 수 없습니다. \\n재접수 바랍니다.');
        }
        
        //  저장시간
        $save_time = date('YmdHis');

        //  ================================================================== 데이터 검사 ================================================================== 

        $result = $this->validCheck($_DATA);

        Log::debug("유효성체크".print_r($result,true));
        
        if( $result['result'] != "Y" && $result['result'] != "YN" )
        {
            return Redirect::back()->with('result', '처리 실패 \\n유효성 검사 진행 후 진행 바랍니다.');
        }
        
        
        //  ================================================================== 데이터 정리 ================================================================== 
        //  거절은 데이터 검사, 처리 하지 않는다.
        if( !($_DATA['status'] == "X" || $_DATA['status'] == "D"))
        {
            if( !in_array("R", $_DATA['bit']) )
            {
                unset($_DATA['new_rate'],$_DATA['new_delay_rate']);
            }

            if( !in_array("C", $_DATA['bit']) )
            {
                unset($_DATA['new_contract_day']);
            }

            if( !in_array("M", $_DATA['bit']) )
            {
                unset($_DATA['new_monthly_return_money']);
            }

            if( !in_array("D", $_DATA['bit']) )
            {
                unset($_DATA['new_return_date']);
            }

            //  다계약 선택
            if( isset($_DATA['all_chg_yn']) )
            {
                foreach($_DATA['all_chg_yn'] as $div)
                {
                    $_DATA[$div."_all_chg_yn"] = "Y";
                }
            }

            foreach ($_DATA as $key => $val) 
            {
                if (strpos($key, "date") !== false) 
                {
                    $_DATA[$key] = str_replace("-", "", $val);
                }

                if (strpos($key, "money") !== false) 
                {
                    $_DATA[$key] = str_replace(",", "", $val);
                }
            }

            // status가 없는 경우 
            if(empty($_DATA['status']))
            {
                $_DATA['status'] = $_DATA['old_status'];
            }
            else
            {
                $_DATA['save_time'] = $save_time;
                $_DATA['save_id']   = Auth::id();

                $_DATA['confirm_date'] = date("Ymd");
            }

            if($_DATA['mode']=="INS")
            {
                unset($_DATA['no']);
                $_DATA['app_date'] = date("Ymd");
                $_DATA['app_id']   = Auth::id();
            }


            $_DATA['condition_bit'] = isset($_DATA['bit']) ? implode('', $_DATA['bit']) : "";
        }

        //  ================================================================== 데이터 입력 ================================================================== 
        // 
        //  1.  LOAN_CONDITION(조건변경), LOAN_CONDITION_DATA 테이블  데이터 입력
        //                                      ->  다계좌 처리라고 하더라도, 결재 진행 중에 계속해서 데이터가 변경 될 수 있기 때문에
        //                                          "Y"(결재완료) 가 되기 전 까지는 대표 계약 번호만 LOAN_CONDITION_DATA에 입력한다.
        //                                      ->  "Y"(결재완료) 시, LOAN_CONDITION_DATA 에 적용되는 모든 계약과 조건을 INSERT 한다.
        //
        //  2.  CUST_INFO_MEMO(변경로그) 테이블 데이터 입력
        //
        //  3.  계약정보 데이터 변경
        //      -   금리변동        : LOAN_INFO_RATE(금리) 테이블 입력          -> LOAN_INFO(계약정보) 테이블 LOAN_RATE, LOAN_DELAY_RATE UPDATE
        //      -   약정일변동      : LOAN_INFO_CDAY(약정일) 테이블 입력        -> LOAN_INFO_TRADE(거래원장) 마지막 거래의 RETURN_DATE, KIHAN_DATE, RETURN_DATE_BIZ, KIHAN_DATE_BIZ 수정
        //                                                                      -> LOAN_INFO(계약정보) 테이블 CONTRACT_DAY, RETURN_DATE, KIHAN_DATE, RETURN_DATE_BIZ, KIHAN_DATE_BIZ UPDATE
        //      -   월상환액변동    : (원금균등, 원리금 균등만 가능) LOAN_INFO(계약정보) 테이블 MONTHLY_RETURN_MONEY UPDATE
        //      -   상환일변동      : 거래원장(LOAN_INFO_TRADE) 테이블 마지막 거래의 RETURN_DATE, KIHAN_DATE, RETURN_DATE_BIZ, KIHAN_DATE_BIZ UPDATE 수정    
        //                                                                      -> LOAN_INFO(계약정보) 테이블 RETURN_DATE, KIHAN_DATE, RETURN_DATE_BIZ, KIHAN_DATE_BIZ UPDATE
        //  
        //  4.  상환스케줄 생성
        //  
        //  5.  계약정보 (이자갱신)
        //
        //
        //  -   반영 완료 결재 건 : 거절 할 때 말곤, 수정 불가
        //  -   취사 된 결재 건   : 수정 불가
        // 
        //  ================================================================================================================================================= 
        DB::beginTransaction();

        //  1.번 LOAN_CONDITION 입력
        if( ($_DATA['status'] == "X" && $_DATA['old_status'] != "X") || ($_DATA['status'] == "D" && $_DATA['old_status'] != "D"))
        {
            // 접수상태 : 접수자,다음결재자 취소가능 , 나머지상태 : 다음결재자 취소가능
            // if(!(isset($request->$next_confirm_str) && $request->$next_confirm_str==Auth::id()) && !($status_div == "A" && $condition->app_id == Auth::id()) && $_DATA['status'] == "X") 
            // {
            //     return Redirect::back()->with('result', '선택된 결재자가 아닙니다.');
            // }

            // 2021-12-17  조건변경 취소에 대한 권한체크. 현 결재건의 결재자들만 가능하도록 요청함
            if( $condition->confirm_id!=Auth::id() && $_DATA['status'] == "D") 
            {
                return Redirect::back()->with('result', '결재완료자만 취소할 수 있습니다.');
            }


            $next_confirm_id = ""; // 취소될경우 다음결재자에게 쪽지보낼필요가 없당

            $_N_DATA = array(
                "confirm_date"  =>date("Ymd"), 
                "status"        =>$_DATA['status'], 
                "save_time"     =>$save_time, 
                "save_id"       =>Auth::id(), 
                "memo"          =>$_DATA['memo'],
            );

            $result_cdt = DB::dataProcess("UPD", "loan_condition", $_N_DATA, ['no'=>$_DATA['no']]);

            if( $result_cdt != "Y" )
            {
                DB::rollBack();

                return Redirect::back()->with('result', '처리 실패\\n데이터 입력 오류#1.');
            }

        }
        else
        {
            //  반영완료로 LOAN_INFO 테이블이 변경될 때 시간 입력
            if( $_DATA['old_status'] != "Y" && $_DATA['status'] == "Y" && $_DATA['mode'] == "UPD" )
            {
                $_DATA['confirm_id'] = Auth::id();
                $_DATA['loan_info_update_time'] = $save_time;
            }

            $lcno = 0;
            if( $_DATA['mode'] == "INS" )
            {
                // $result_cdt = DB::dataProcess("INS", "LOAN_CONDITION", $_DATA, null, $lcno);
                $result_cdt = DB::dataProcess("INS", "loan_condition", $_DATA, null, $lcno);
            }
            else
            {
                $result_cdt = DB::dataProcess("UPD", "loan_condition", $_DATA);
            }
            // LOAN_CONDITION의 no 조회
            // $LOAN_CONDITION = DB::TABLE('loan_condition')->SELECT('no')->WHERE('loan_info_no', $_DATA['loan_info_no'])->orderBy('no', 'desc')->first();
            // $_DATA['no'] = $LOAN_CONDITION->no;

            if( $result_cdt == "Y" )
            {
                $_DATA['loan_condition_no'] = ($_DATA['mode'] == "INS")? $lcno:$_DATA['no'];
                
                if( $_DATA['loan_condition_no'] == 0 )
                {
                    DB::rollBack();

                    return Redirect::back()->with('result', '처리 실패\\n데이터 입력 오류#4.');
                }

                
                $result_lcd = DB::dataProcess("UST", "loan_condition_data", $_DATA, ["loan_condition_no"=>$_DATA['loan_condition_no'], "loan_info_no"=>$_DATA['loan_info_no']]);

                if( $result_lcd != "Y" )
                {
                    DB::rollBack();

                    return Redirect::back()->with('result', '처리 실패\\n데이터 입력 오류#3.');
                }
            }
            else
            {
                DB::rollBack();

                return Redirect::back()->with('result', '처리 실패\\n데이터 입력 오류#2.');
            }
        }

        $arr_memo_div = Func::getConfigArr('memo_div');
        $arr_memo_div = array_flip($arr_memo_div);

        $arr_condition_bit = Vars::$arrayConditionBit;
        $arr_status = Vars::$arrayConditionStatus;

        $_MEMO = [];
        $_MEMO['cust_info_no'] = $_DATA['cust_info_no'];
        $_MEMO['save_time']    = $save_time;
        $_MEMO['save_id']      = Auth::id();
        $_MEMO['save_status']  = "Y";
        $_MEMO['div']          = Func::nvl($arr_memo_div['조건변경'], '조');
        $_MEMO['memo']         = "[조건변경 (".Auth::id()." : ".Func::nvl($arr_status[$_DATA['old_status']],$_DATA['old_status'])." -> ".Func::nvl($arr_status[$_DATA['status']],$_DATA['status']).")] 계약번호 : ".$_DATA['loan_info_no']." ";
        $_MEMO['memo']        .= "\r\n";

        if( !($_DATA['status'] == "X" || $_DATA['status'] == "D") )
        {
            foreach($_DATA['bit'] as $code)
            {
                $_MEMO['memo'] .= $arr_condition_bit[$code];

                if( $code == "R" )
                {
                    if( isset($_DATA['all_chg_yn']) && in_array("rate", $_DATA['all_chg_yn']) )
                    {
                        $_MEMO['memo'] .= "(※ 다계좌)";
                    }

                    // $_MEMO['memo'] .= "(".$_DATA['old_rate']."/".$_DATA['old_delay_rate']."->".$_DATA['new_rate']."/".$_DATA['new_delay_rate'].")";
                    $_MEMO['memo'] .= "(".$_DATA['new_rate']."/".$_DATA['new_delay_rate'].")";
                }
                else if( $code == "C" )
                {
                    if( isset($_DATA['all_chg_yn']) && in_array("cday", $_DATA['all_chg_yn']) )
                    {
                        $_MEMO['memo'] .= "(※ 다계좌)";
                    }

                    // $_MEMO['memo'] .= "(".$_DATA['old_contract_day']."->".$_DATA['new_contract_day'].")";
                    $_MEMO['memo'] .= "(".$_DATA['new_contract_day'].")";
                }
                else if( $code == "M" )
                {
                    if( isset($_DATA['all_chg_yn']) && in_array("rmoney", $_DATA['all_chg_yn']) )
                    {
                        $_MEMO['memo'] .= "(※ 다계좌)";
                    }

                    // $_MEMO['memo'] .= "(".$_DATA['old_monthly_return_money']."->".$_DATA['new_monthly_return_money'].")";
                    $_MEMO['memo'] .= "(".$_DATA['new_monthly_return_money'].")";
                }
                else if( $code == "D" )
                {
                    if( isset($_DATA['all_chg_yn']) && in_array("rdate", $_DATA['all_chg_yn']) )
                    {
                        $_MEMO['memo'] .= "(※ 다계좌)";
                    }

                    // $_MEMO['memo'] .= "(".$_DATA['old_return_date']."->".$_DATA['new_return_date'].")";
                    $_MEMO['memo'] .= "(".$_DATA['new_return_date'].")";
                }
                else if( $code == "S" )
                {
                    //  추가예정
                }
            }
        }

        if( isset($_DATA['memo']) && $_DATA['memo']!='' )
        {
            $_MEMO['memo'] .= "\r\n";
            $_MEMO['memo'] .= "메모 : ".$_DATA['memo'];
        }

        //  2.번 LOAN_INFO_MEMO 입력
        $result_memo = Func::saveMemo($_MEMO);

        if( $result_memo != "Y" )
        {
            DB::rollBack();

            return Redirect::back()->with('result', '처리 실패\\n메모 입력 실패.');
        }

        $i = 0;

        if( $_DATA['status'] == "Y" || $_DATA['status'] == "X" || $_DATA['status'] == "D")
        {
            foreach($result['nos'] as $no)
            {
                $i++;

                //  계약정보 입력 (반영완료일 경우만 입력되어야 한다.)
                if( $_DATA['status'] == "Y" && $_DATA['old_status'] != "Y" )
                {
                    // LOAN
                    $loan = new loan($no);

                    //  LOAN_CONDITION_DATA 에 입력 할 배열
                    $lcd = array(
                        "loan_condition_no"         =>  $_DATA['loan_condition_no'],
                        "loan_info_no"              =>  $no,
                        "old_rate"                  =>  $loan->loanInfo['loan_rate'],
                        "old_delay_rate"            =>  $loan->loanInfo['loan_delay_rate'],
                        "old_contract_day"          =>  $loan->loanInfo['contract_day'],
                        "old_monthly_return_money"  =>  $loan->loanInfo['monthly_return_money'],
                        "old_return_date"           =>  $loan->loanInfo['return_date'],
                    );

                    $_loanInfo = [];
                    $_loanInfo['no']        = $no;
                    $_loanInfo['save_time'] = $save_time;
                    $_loanInfo['save_id']   = Auth::id();

                    //  금리
                    if( in_array("R", $_DATA['bit']) && ( $no == $_DATA['loan_info_no'] || (isset($_DATA['all_chg_yn']) && in_array("rate", $_DATA['all_chg_yn'])) ) )
                    {
                        if( $_DATA['basis_date'] <= $_DATA['confirm_date'] )
                        {
                            $_loanInfo['loan_rate']       = $_DATA['new_rate'];
                            $_loanInfo['loan_delay_rate'] = $_DATA['new_delay_rate'];
                        }

                        //  금리
                        $_rate = [];
                        $_rate['loan_info_no']    = $no;
                        $_rate['rate_date']       = $_DATA['basis_date'];
                        $_rate['loan_rate']       = $_DATA['new_rate'];
                        $_rate['loan_delay_rate'] = $_DATA['new_delay_rate'];
                        $_rate['save_time']       = $save_time;
                        $_rate['save_id']         = Auth::id();
                        $_rate['save_status']     = "Y";

                        // 기준일 이후 금리는 지워
                        $result_rate = DB::dataProcess('UPD', 'loan_info_rate', ['save_status'=>'N', 'del_time'=>$save_time, 'del_id'=>Auth::id()], [['loan_info_no',$no], ['rate_date',">=",$_DATA['basis_date']]]);
                        // 기준일 등록
                        $result_rate = DB::dataProcess('INS', 'loan_info_rate', $_rate);
                        // $result_rate = DB::dataProcess('UST', 'LOAN_INFO_RATE', $_rate, ['loan_info_no'=>$_rate['loan_info_no'], 'rate_date'=>$_rate['rate_date']]);

                        $lcd['new_rate']       = $_DATA['new_rate'];
                        $lcd['new_delay_rate'] = $_DATA['new_delay_rate'];
                        if( $result_rate != "Y" )
                        {
                            DB::rollBack();
                            return Redirect::back()->with('result', '금리 변경 등록 실패\\n계약번호 : '.$no);
                        }
                    
                        //  원장변경내역 입력
                        $_wch = [
                            "cust_info_no"  =>  $_DATA['cust_info_no'],
                            "loan_info_no"  =>  $no,
                            "worker_id"     =>  Auth::id(),
                            "work_time"     =>  $save_time,
                            "worker_code"   =>  Auth::user()->branch_code,
                            "loan_status"   =>  $loan->loanInfo['status'],
                            "manager_code"  =>  $loan->loanInfo['manager_code'],
                            "div_nm"        =>  "금리변경(정상)",
                            "before_data"   =>  number_format($loan->loanInfo['loan_rate'],2),
                            "after_data"    =>  number_format($_DATA['new_rate'],2),
                            "trade_type"    =>  "",
                            "sms_yn"        =>  "",
                            "memo"          =>  $_DATA['memo'] ?? "",
                        ];
    
                        $result_wch = Func::saveWonjangChgHist($_wch);
                        if( $result_wch != "Y" )
                        {
                            DB::rollBack();
                            log::info("금리변경(정상) - 원장변경내역 저장 실패 계약번호 : ".$no);
                            return Redirect::back()->with('result', '원장변경내역 등록 실패\\n계약번호 : '.$no);
                        }

                        $_wch['div_nm']      = "금리변경(연체)";
                        $_wch['before_data'] = number_format($loan->loanInfo['loan_delay_rate'],2);
                        $_wch['after_data']  = number_format($_DATA['new_delay_rate'],2);
    
                        $result_wch = Func::saveWonjangChgHist($_wch);
                        if( $result_wch != "Y" )
                        {
                            DB::rollBack();
                            log::info("금리변경(연체) - 원장변경내역 저장 실패 계약번호 : ".$no);
                            return Redirect::back()->with('result', '원장변경내역 등록 실패\\n계약번호 : '.$no);
                        }
                        
                    }

                    //  약정일
                    if( in_array("C", $_DATA['bit']) && ( $no == $_DATA['loan_info_no'] || (isset($_DATA['all_chg_yn']) && in_array("cday", $_DATA['all_chg_yn'])) ) )
                    {
                        $_loanInfo['contract_day'] = $_DATA['new_contract_day'];
                        
                        //  약정일 -> 약정일 변경은 반영기준일에 상관 없이, 이수일 기준으로 한다.
                        $_cday = [];
                        $_cday['loan_info_no'] = $no;
                        $_cday['cday_date']    = $loan->loanInfo['take_date'];
                        $_cday['contract_day'] = $_DATA['new_contract_day'];
                        $_cday['save_time']    = $save_time;
                        $_cday['save_id']      = Auth::id();
                        $_cday['save_status']  = "Y";

                        // 기준일 이후 금리는 지워
                        $result_cday = DB::dataProcess('UPD', 'loan_info_cday', ['save_status'=>'N', 'del_time'=>$save_time, 'del_id'=>Auth::id()], [['loan_info_no',$no], ['cday_date',">=",$loan->loanInfo['take_date']]]);
                        // 기준일 등록
                        $result_cday = DB::dataProcess('UST', 'loan_info_cday', $_cday, ['loan_info_no'=>$_cday['loan_info_no'], 'cday_date'=>$_cday['cday_date']]);

                        if( $result_cday != "Y" )
                        {
                            DB::rollBack();
                            return Redirect::back()->with('result', '약정일 변경 등록 실패#1\\n계약번호 : '.$no);
                        }


                        //  약정일 변경 -> 상환일 변경
                        $_loanInfo['return_date']     = $loan->getNextReturnDate($loan->loanInfo['take_date'], $_loanInfo['contract_day']);
                        $_loanInfo['kihan_date']      = $loan->getNextKihanDate($_loanInfo['return_date'], $_loanInfo['contract_day']);
                        $_loanInfo['return_date_biz'] = $loan->getBizDay($_loanInfo['return_date']);
                        $_loanInfo['kihan_date_biz']  = $loan->getBizDay($_loanInfo['kihan_date']);


                        //  약정일 변경 -> 상환일 변경 -> 거래원장 UPDATE
                        $_tradeNo = DB::TABLE("loan_info_trade")->SELECT("no")->WHERE('loan_info_no', $no)->WHERE('save_status', 'Y')->ORDERBY('save_time','desc')->ORDERBY('no','desc')->FIRST();
                        
                        $_trade = [];
                        $_trade['return_date']     = $_loanInfo['return_date'];
                        $_trade['return_date_biz'] = $_loanInfo['return_date_biz'];
                        $_trade['kihan_date']      = $_loanInfo['kihan_date'];
                        $_trade['kihan_date_biz']  = $_loanInfo['kihan_date_biz'];
                        
                        $result_trade = DB::dataProcess("UPD", "loan_info_trade", $_trade, ['no'=>$_tradeNo->no]);

                        if( $result_trade != "Y" )
                        {
                            DB::rollBack();
                            return Redirect::back()->with('result', '약정일 변경 등록 실패#2\\n계약번호 : '.$no);
                        }

                        //  LOAN_CONTRACT_DATA 변수셋팅
                        $lcd['new_contract_day'] = $_DATA['new_contract_day'];

                        //  원장변경내역 입력
                        $_wch = [
                            "cust_info_no"  =>  $_DATA['cust_info_no'],
                            "loan_info_no"  =>  $no,
                            "worker_id"     =>  Auth::id(),
                            "work_time"     =>  $save_time,
                            "worker_code"   =>  Auth::user()->branch_code,
                            "loan_status"   =>  $loan->loanInfo['status'],
                            "manager_code"  =>  $loan->loanInfo['manager_code'],
                            "div_nm"        =>  "약정일변경",
                            "before_data"   =>  $loan->loanInfo['contract_day'],
                            "after_data"    =>  $_DATA['new_contract_day'],
                            "trade_type"    =>  "",
                            "sms_yn"        =>  "",
                            "memo"          =>  $_DATA['memo'] ?? "",
                        ];

                        $result_wch = Func::saveWonjangChgHist($_wch);
                        
                        if( $result_wch != "Y" )
                        {
                            DB::rollBack();
                            log::info("약정일변경 - 원장변경내역 저장 실패 계약번호 : ".$no);
                            return Redirect::back()->with('result', '원장변경내역 등록 실패\\n계약번호 : '.$no);
                        }
                    }

                    //  상환일
                    if( in_array("D", $_DATA['bit']) && ( $no == $_DATA['loan_info_no'] || (isset($_DATA['all_chg_yn']) && in_array("rdate", $_DATA['all_chg_yn'])) ) )
                    {
                        if( $loan->loanInfo['return_method_cd'] == "F" )
                        {
                            $_loanInfo['return_date']     = $_DATA['new_return_date'];
                            $_loanInfo['kihan_date']      = $loan->getNextKihanDate( $_loanInfo['return_date'], substr($_loanInfo['return_date'],-2) );
                            $_loanInfo['return_date_biz'] = $loan->getBizDay($_loanInfo['return_date']);
                            $_loanInfo['kihan_date_biz']  = $loan->getBizDay($_loanInfo['kihan_date']);
                            
                            $_tradeNo = DB::TABLE("loan_info_trade")->SELECT("no")->WHERE('loan_info_no', $no)->WHERE('save_status', 'Y')->ORDERBY('no','desc')->FIRST();
                            
                            $_trade = [];
                            $_trade['return_date']     = $_loanInfo['return_date'];
                            $_trade['return_date_biz'] = $_loanInfo['return_date_biz'];
                            $_trade['kihan_date']      = $_loanInfo['kihan_date'];
                            $_trade['kihan_date_biz']  = $_loanInfo['kihan_date_biz'];
                            
                            $result_trade = DB::dataProcess("UPD", "loan_info_trade", $_trade, ['no'=>$_tradeNo->no]);

                            $lcd['new_return_date'] = $_loanInfo['return_date'];
                            
                            if( $result_trade != "Y" )
                            {
                                DB::rollBack();
    
                                return Redirect::back()->with('result', '상환일 변경 등록 실패\\n계약번호 : '.$no);
                            }

                            //  원장변경내역 입력
                            $_wch = [
                                "cust_info_no"  =>  $_DATA['cust_info_no'],
                                "loan_info_no"  =>  $_DATA['loan_info_no'],
                                "worker_id"     =>  Auth::id(),
                                "work_time"     =>  $save_time,
                                "worker_code"   =>  Auth::user()->branch_code,
                                "loan_status"   =>  $loan->loanInfo['status'],
                                "manager_code"  =>  $loan->loanInfo['manager_code'],
                                "div_nm"        =>  "납입예정일변경",
                                "div_cd"        =>  "L",
                                "before_data"   =>  $loan->loanInfo['return_date'],
                                "after_data"    =>  $_loanInfo['return_date'],
                                "trade_type"    =>  "",
                                "sms_yn"        =>  "",
                                "memo"          =>  $_DATA['memo'] ?? "",
                            ];

                            $result_wch = Func::saveWonjangChgHist($_wch);
                            
                            if( $result_wch != "Y" )
                            {
                                DB::rollBack();
                                log::info("납입예정일변경 - 원장변경내역 저장 실패 계약번호 : ".$no);
                                return Redirect::back()->with('result', '원장변경내역 등록 실패\\n계약번호 : '.$no);
                            }
                        }
                        else
                        {
                            DB::rollBack();
        
                            return Redirect::back()->with('result', '상환일 변경 등록 실패 (자유상환만 가능)\\n계약번호 : '.$no);
                        }
                    }

                    //  월상환액
                    if( in_array("M", $_DATA['bit']) && ( $no == $_DATA['loan_info_no'] || (isset($_DATA['all_chg_yn']) && in_array("rmoney", $_DATA['all_chg_yn'])) ) )
                    {
                        if( $loan->loanInfo['return_method_cd'] == "R" || $loan->loanInfo['return_method_cd'] == "B" || $loan->loanInfo['return_method_cd'] == "F" )
                        {
                            $_loanInfo['monthly_return_money'] = Func::strToInt($_DATA['new_monthly_return_money']);

                            $lcd['new_monthly_return_money'] = $_loanInfo['monthly_return_money'];
                        }
                        else
                        {
                            DB::rollBack();

                            return Redirect::back()->with('result', '월상환액 변경 등록 실패 (원리금균등,원금균등,자유상환 만 가능)\\n계약번호 : '.$no);
                        }

                        //  원장변경내역 입력
                        $_wch = [
                            "cust_info_no"  =>  $_DATA['cust_info_no'],
                            "loan_info_no"  =>  $_DATA['loan_info_no'],
                            "worker_id"     =>  Auth::id(),
                            "work_time"     =>  $save_time,
                            "worker_code"   =>  Auth::user()->branch_code,
                            "loan_status"   =>  $loan->loanInfo['status'],
                            "manager_code"  =>  $loan->loanInfo['manager_code'],
                            "div_nm"        =>  "납입예정금액변경",
                            "div_cd"        =>  "M",
                            "before_data"   =>  $loan->loanInfo['monthly_return_money'],
                            "after_data"    =>  $_loanInfo['monthly_return_money'],
                            "trade_type"    =>  "",
                            "sms_yn"        =>  "",
                            "memo"          =>  $_DATA['memo'] ?? "",
                        ];

                        $result_wch = Func::saveWonjangChgHist($_wch);
                    }

                    $result_loan = DB::dataProcess("UPD", "loan_info", $_loanInfo, ["no"=>$_loanInfo['no']]);

                    //  스케줄 생성
                    if( $result_loan=="Y" )
                    {
                        $result_lcd2 = DB::dataProcess("UST", "loan_condition_data", $lcd, ["loan_condition_no" => $lcd['loan_condition_no'], "loan_info_no"=>$lcd['loan_info_no']]);

                        if( $result_lcd2 != "Y" )
                        {
                            DB::rollBack();
        
                            return Redirect::back()->with('result', '조건변경데이터 등록 실패\\n계약번호 : '.$no);
                        }

                        if( $loan->loanInfo['return_method_cd'] != "F" )
                        {
                            $loan_new = new loan($no);
                            // $result_plan = $loan_new->savePlan( $loan_new->buildPlanData("", isset($_loanInfo['monthly_return_money'])? $_loanInfo['monthly_return_money'] : null)  );
                            //  반영기준일을 기점으로 스케줄 갱신
                            $result_plan = $loan_new->savePlan( $loan_new->buildPlanData() );
        
                            if( $result_plan != "Y" )
                            {
                                DB::rollback();
                                return Redirect::back()->with('result', '스케줄 갱신 실패\\n계약번호 : '.$no);
                            }
                        }
                    }
                    else
                    {
                        DB::rollback();
                        return Redirect::back()->with('result', '계약정보 변경 등록 실패\\n계약번호 : '.$no);
                    }

                    
                    
                    if( $result_wch != "Y" )
                    {
                        DB::rollBack();
                        log::info("납입예정금액변경 - 원장변경내역 저장 실패 계약번호 : ".$no);
                        return Redirect::back()->with('result', '원장변경내역 등록 실패\\n계약번호 : '.$no);
                    }

                    $result_interest = Loan::updateLoanInfoInterest($_loanInfo['no'], date("Ymd"));
                    if( $result_interest!="Y" )
                    {
                        DB::rollback();
                        return Redirect::back()->with('result', '계약정보(이자 갱신) 변경 실패\\n계약번호 : '.$_loanInfo['no']);
                    }
                }
                // 반영완료 후 거절 -> 반영완료된 것은 변경할 수 없는 것으로 바뀜
                // 2021-12-15 약정일변경의 경우 반영완료 후 취소가능하도록 요청
                else if( $_DATA['old_status'] == "Y" && $_DATA['status'] == "D" )       //  반영완료 -> 취소
                {
                    // LOAN
                    $loan = new loan($no);

                    //  원복할 데이터
                    $lcd = DB::TABLE("loan_condition_data")->SELECT("*")->WHERE("loan_condition_no", $condition->no)->WHERE("loan_info_no", $no)->first();
                    $lcd = Func::chungDec(["LOAN_CONDITION_DATA"], $lcd);	// CHUNG DATABASE DECRYPT

                    $_loanInfo = [];
                    $_loanInfo['no']        = $no;
                    $_loanInfo['save_time'] = $save_time;
                    $_loanInfo['save_id']   = Auth::id();

                    // //  금리 원복
                    // if( str_contains($condition->condition_bit, "R") && ( $no == $_DATA['loan_info_no'] || $condition->rate_all_chg_yn == "Y" ) )
                    // {
                    //     $_loanInfo['loan_rate']       = $lcd->old_rate;
                    //     $_loanInfo['loan_delay_rate'] = $lcd->old_delay_rate;

                    //     $_rate = [];

                    //     //  금리
                    //     if( count($loan->rateInfo) == 1 )       //  금리가 하나뿐이면 해당 건을 전 약정일 정보로 바꿔야 함 (ex : 계약일에 바로 조건변경)
                    //     {
                    //         $_rate['loan_rate']       = $lcd->old_rate;
                    //         $_rate['loan_delay_rate'] = $lcd->old_delay_rate;
                    //         $_rate['save_time']       = $save_time;
                    //         $_rate['save_id']         = Auth::id();
                    //     }
                    //     else
                    //     {
                    //         $_rate['del_time']        = $save_time;
                    //         $_rate['del_id']          = Auth::id();
                    //         $_rate['save_status']     = "N";
                    //     }

                    //     log::info("금리 ($no)".print_r($_rate,true));
                    //     $result_rate = DB::dataProcess('UPD', 'LOAN_INFO_RATE', $_rate, ['loan_info_no'=>$no, 'save_time'=>$condition->loan_info_update_time]);

                    //     if( $result_rate != "Y" )
                    //     {
                    //         DB::rollBack();
        
                    //         return Redirect::back()->with('result', '금리 원복 실패\\n계약번호 : '.$no);
                    //     }
                    // }

                    //  약정일 원복
                    if( str_contains($condition->condition_bit, "C") && ( $no == $_DATA['loan_info_no'] || $condition->cday_all_chg_yn == "Y"  ) )
                    {
                        // 해당 조건변경건 이후 입금건 있는지 체크
                        $chk_c = DB::TABLE("loan_info_trade")->WHERE('no',$no)->WHERE('save_status',"Y")->WHERERAW("trade_date >= '".$condition->basis_date."'")->EXISTS();
                
                        if(!empty($chk_c))
                        {
                            $rslt = ["rslt"=>"Y","msg"=>""];
                            return Redirect::back()->with('result', '해당 조건변경건 이후 입금건이 존재합니다. \n 입금삭제를 진행해주세요');
                        }


                        $_loanInfo['contract_day'] = $lcd->old_contract_day;

                        $_cday = [];
                        //  약정일
                        if( count($loan->cdayInfo) == 1 )       //  약정일이 하나뿐이면 해당 건을 전 약정일 정보로 바꿔야 함
                        {
                            $_cday['contract_day'] = $lcd->old_contract_day;
                            $_cday['save_time']    = $save_time;
                            $_cday['save_id']      = Auth::id();
                        }
                        else
                        {
                            $_cday['del_time']        = $save_time;
                            $_cday['del_id']          = Auth::id();
                            $_cday['save_status']     = "N";
                        }

                        log::info("약정일 ($no)".print_r($_cday,true));
                        //  take_date 기준 으로 하면 (자유상환)입금 삭제가 발생했을 때 문제가 생김.... (ex)조건번경 이전 입금거래 삭제  ->  save_time만 가지고 찾음...
                        // $result_cday = DB::dataProcess('UPD', 'LOAN_INFO_CDAY', $_cday, ['loan_info_no'=>$no, 'cday_date'=>$loan->loanInfo['take_date'], 'save_time'=>$condition->loan_info_update_time]);
                        $result_cday = DB::dataProcess('UPD', 'loan_info_cday', $_cday, ['loan_info_no'=>$no, 'save_time'=>$condition->loan_info_update_time]);

                        //  약정일 변경 -> 상환일 변경
                        $_loanInfo['return_date']     = $lcd->old_return_date;
                        $_loanInfo['kihan_date']      = $loan->getNextKihanDate($_loanInfo['return_date'], $_loanInfo['contract_day']);
                        $_loanInfo['return_date_biz'] = $loan->getBizDay($_loanInfo['return_date']);
                        $_loanInfo['kihan_date_biz']  = $loan->getBizDay($_loanInfo['kihan_date']);

                        if($result_cday != "Y")
                        {
                            DB::rollBack();
        
                            return Redirect::back()->with('result', '약정일 원복 실패\\n계약번호 : '.$no);
                        }
                        else
                        {
                        
                            //  약정일 변경 -> 상환일 변경 -> 거래원장 UPDATE
                            $_tradeNo = DB::TABLE("loan_info_trade")->SELECT("no")->WHERE('loan_info_no', $no)->WHERE('save_status', 'Y')->ORDERBY('save_time','desc')->ORDERBY('no','desc')->FIRST();
                            
                            $_trade = [];
                            $_trade['return_date']     = $_loanInfo['return_date'];
                            $_trade['return_date_biz'] = $_loanInfo['return_date_biz'];
                            $_trade['kihan_date']      = $_loanInfo['kihan_date'];
                            $_trade['kihan_date_biz']  = $_loanInfo['kihan_date_biz'];
                            
                                    
                            LOG::INFO("TRADE 업데이트내용  ".$_tradeNo->no);
                            LOG::INFO(print_r($_trade,true));

                            $result_trade = DB::dataProcess("UPD", "loan_info_trade", $_trade, ['no'=>$_tradeNo->no]);

                            if( $result_trade != "Y" )
                            {
                                DB::rollBack();

                                return Redirect::back()->with('result', '약정일 원복 실패#2\\n계약번호 : '.$no);
                            }

                        }
                    }

                    // //  상환일 원복
                    // if( str_contains($condition->condition_bit, "D") && ( $no == $_DATA['loan_info_no'] || $condition->rdate_all_chg_yn == "Y"  ) )
                    // {
                    //     if( $loan->loanInfo['return_method_cd'] == "F" )
                    //     {
                    //         $_loanInfo['return_date']     = $lcd->old_return_date;
                    //         $_loanInfo['kihan_date']      = $loan->getNextKihanDate($_loanInfo['return_date'], $lcd->old_contract_day );
                    //         $_loanInfo['return_date_biz'] = $loan->getBizDay($_loanInfo['return_date']);
                    //         $_loanInfo['kihan_date_biz']  = $loan->getBizDay($_loanInfo['kihan_date']);
                            
                    //         $_tradeNo = DB::TABLE("LOAN_INFO_TRADE")->SELECT("no")->WHERE('loan_info_no', $no)->WHERE('save_status', 'Y')->ORDERBY('no','desc')->FIRST();
                            
                    //         $_trade = [];
                    //         $_trade['return_date']     = $_loanInfo['return_date'];
                    //         $_trade['return_date_biz'] = $_loanInfo['return_date_biz'];
                    //         $_trade['kihan_date']      = $_loanInfo['kihan_date'];
                    //         $_trade['kihan_date_biz']  = $_loanInfo['kihan_date_biz'];
                            
                    //         $result_trade = DB::dataProcess("UPD", "LOAN_INFO_TRADE", $_trade, ['no'=>$_tradeNo->no]);

                    //         if( $result_trade != "Y" )
                    //         {
                    //             DB::rollBack();

                    //             return Redirect::back()->with('result', '상환일 원복 실패\\n계약번호 : '.$no);
                    //         }
                    //     }
                    //     else
                    //     {
                    //         DB::rollBack();
        
                    //         return Redirect::back()->with('result', '상환일 원복 실패 (자유상환만 가능)\\n계약번호 : '.$no);
                    //     }
                    // }

                    // //  월상환액
                    // if( str_contains($condition->condition_bit, "M") && ( $no == $_DATA['loan_info_no'] || $condition->rmoney_all_chg_yn == "Y"  ) )
                    // {
                    //     $_loanInfo['monthly_return_money'] = Func::strToInt($lcd->old_monthly_return_money);
                    // }

                    LOG::INFO("LOAN_INFO 업데이트내용");
                    LOG::INFO(print_r($_loanInfo,true));
                    $result_loan = DB::dataProcess("UPD", "loan_info", $_loanInfo, ["no"=>$_loanInfo['no']]);


                    //  스케줄 재등록
                    if( $result_loan=="Y" )
                    {
                        if($loan->loanInfo['return_method_cd'] != "F")
                        {
                            Log::info("스케줄 재등록대상");
            
                            // 조건변경으로 갱신됐던 스케줄을 찾는다
                            $vpl = (Array) DB::TABLE("loan_info_plan_log")->SELECT("max(save_time) as st")->WHERE("loan_info_no", $_loanInfo['no'])->WHERE("seq", 1)->WHERE("save_time", "<", $condition->loan_info_update_time)->FIRST();
                            $last_save_time = $vpl['st'];
                            Log::info("전스케줄 등록시간 = ".$last_save_time);
            
                            // 일단 삭제
                            $rslt = DB::dataProcess("DEL", "loan_info_plan", Array(), [['loan_info_no','=',$_loanInfo['no']]]);
            
            
                            $SQL = "INSERT INTO loan_info_plan ( ";
                            $SQL.= "loan_info_no, seq, plan_date, plan_date_biz, plan_money, plan_origin, plan_interest, plan_balance, plan_interest_term, plan_interest_sdate, plan_interest_edate, save_time, save_id, loan_info_trade_no ";
                            $SQL.= ") ";
                            $SQL.= "SELECT ";
                            $SQL.= "loan_info_no, seq, plan_date, plan_date_biz, plan_money, plan_origin, plan_interest, plan_balance, plan_interest_term, plan_interest_sdate, plan_interest_edate, save_time, save_id, loan_info_trade_no ";
                            $SQL.= "from loan_info_plan_log where loan_info_no=? and save_time=? ";
                            DB::insert($SQL, [$_loanInfo['no'], $last_save_time]);
            
                            $rslt = DB::dataProcess("DEL", "loan_info_plan_log", Array(), Array('loan_info_no'=>$_loanInfo['no'], 'save_time'=>$last_save_time));


                            if( $rslt != "Y" )
                            {
                                DB::rollback();
                                return Redirect::back()->with('result', '스케줄 복원 실패\\n계약번호 : '.$no);
                            }
                        }
                    }
                    else
                    {
                        DB::rollback();
                        return Redirect::back()->with('result', '계약정보 변경 취소 실패\\n계약번호 : '.$no);
                    }


                    $result_interest = Loan::updateLoanInfoInterest($_loanInfo['no'], date("Ymd"));
                    if( $result_interest!="Y" )
                    {
                        DB::rollback();
                        return Redirect::back()->with('result', '계약정보(이자 갱신) 변경 실패\\n계약번호 : '.$_loanInfo['no']);
                    }
                }
                

            }
        }

        DB::commit();

         // 다음 지정된 결재자 있을떄 쪽지보내자
        if(!empty($next_confirm_id))
        {
            $msg = [
                'msg_type' => 'S',
                'msg_level'=> 'info',
                'recv_id'  => $next_confirm_id,
                'send_id'  => 'SYSTEM',
                'title'    => '조건변경 결재요청 - 결재번호['.$_DATA['loan_condition_no'].']',
                'contents' => '조건변경 결재요청건이 존재합니다.',
                'msg_link' => "/erp/conditionpop?loan_info_no=".$_DATA['loan_info_no']."&no=".$_DATA['loan_condition_no'],
            ];  
            Func::sendMessage($msg); 
        }


        // return redirect( "/erp/conditionpop?loan_info_no=".$_DATA['loan_info_no']."&status=".$_DATA['status'] )->with(['result' => '정상적으로 처리되었습니다.', 'flag' => 'Y']);
        return redirect( "/erp/conditionpop")->with(['result' => '정상적으로 처리되었습니다.', 'flag' => 'Y']);

    }

    
    /**
     *  상환스케줄 미리보기
     * 
     * @return view
     */
    public function conditionPlanPreview(Request $request)
    {

        $_DATA = $request->all();

        
        if( $_DATA['mode'] != "INS" )
        {
            $condition = DB::TABLE("loan_condition")->SELECT("*")->WHERE("no", $_DATA['no'])->first();
            $condition = Func::chungDec(["LOAN_CONDITION"], $condition);	// CHUNG DATABASE DECRYPT
            
            $_DATA['old_status'] = $condition->status;

            if( $_DATA['old_status'] == "X" )
            {
                $arrayRslt["main"]["str"] = "※ 거절 건 수정 불가.";
                return $arrayRslt;
            }
            // log::debug((float) $condition->old_rate);
        }

        if( (isset($_DATA['old_status']) && ($_DATA['old_status'] == "Y" && $_DATA['status'] != "X")) )
        {
            $arrayRslt["main"]["str"] = "※ 반영완료 건 입니다.";
            return $arrayRslt;
        }

        //  데이터 검사
        $result = $this->validCheck($_DATA);

        if( $result['result'] != "Y" )
        {
            return $result;
        }
        
        $_DATA['basis_date']      = str_replace("-","",$_DATA['basis_date']);

        foreach( $result["nos"] as $no )
        {
            $loan = new loan($no);

            if( $loan->loanInfo['return_method_cd'] != "F" )
            {
                $plan_old[$no] = $loan->planInfo;
                $loan->planInfo['take_date'] = $_DATA['basis_date'];
        
                //  금리
                if( in_array("R", $_DATA['bit']) && ( (isset($_DATA['all_chg_yn']) && in_array("rate", $_DATA['all_chg_yn'])) || $no == $_DATA['loan_info_no'] ) )
                {
                    $loan->rateInfo[$_DATA['basis_date']] = Array( "rate_date" => $_DATA['basis_date'], "loan_rate" => $_DATA['new_rate'], "loan_delay_rate" => $_DATA['new_delay_rate'] );

                    $loan->loanInfo['loan_rate'] = $_DATA['new_rate'];
                    $loan->loanInfo['loan_delay_rate'] = $_DATA['new_delay_rate'];
                }

                //  약정일
                if( in_array("C", $_DATA['bit']) && ( (isset($_DATA['all_chg_yn']) && in_array("cday", $_DATA['all_chg_yn'])) || $no == $_DATA['loan_info_no'] ) )
                {
                    //  약정일 변경은 반영기준일에 상관 없이, -> 이수일 기준으로 한다.
                    $loan->cdayInfo[$loan->loanInfo['take_date']] = Array( "cday_date" => $loan->loanInfo['take_date'], "contract_day" => $_DATA['new_contract_day'] );

                    //  약정일 변경 -> 상환일 변경
                    $loan->loanInfo['contract_day']    = $_DATA['new_contract_day'];
                    $cday_date = $loan->loanInfo['take_date'];
                    $loan->cdayInfo[$cday_date]['cday_date']    = $cday_date;
                    $loan->cdayInfo[$cday_date]['contract_day'] = $_DATA['new_contract_day'];

                    $loan->loanInfo['return_date']     = $loan->getNextReturnDate( $loan->loanInfo['take_date'] );
                    $loan->loanInfo['kihan_date']      = $loan->getNextKihanDate( $loan->loanInfo['return_date'] );
                    $loan->loanInfo['return_date_biz'] = $loan->getBizDay($loan->loanInfo['return_date']);
                    $loan->loanInfo['kihan_date_biz']  = $loan->getBizDay($loan->loanInfo['kihan_date']);

                    $msg['cday']['str'] = "차기수납일은 최종이수일 기준으로 변경됩니다. ";
                    $msg['cday']['add'] = ( !empty($msg['cday']['add'])?$msg['cday']['add'].", ":"" ) . $no ."번계약 차기수납일 : ".Func::dateFormat($loan->loanInfo['return_date']);
                }

                //  월상환액
                if( in_array("M", $_DATA['bit']) && ( (isset($_DATA['all_chg_yn']) && in_array("rmoney", $_DATA['all_chg_yn'])) || $no == $_DATA['loan_info_no'] ) )
                {
                    // $monthly_return_money = Func::strToInt($_DATA['new_monthly_return_money']);
                    $loan->loanInfo['monthly_return_money'] = Func::strToInt($_DATA['new_monthly_return_money']);
                }
                else
                {
                    // $monthly_return_money = null;
                }

                //  echo "<pre>".print_r($loan->loanInfo)."</pre>";
                $plan_new[$no] = $loan->buildPlanData( );

                $loan_info_nos[] = $no;
            }
            else
            {
                if( in_array("C", $_DATA['bit']) && ( (isset($_DATA['all_chg_yn']) && in_array("cday", $_DATA['all_chg_yn'])) || $no == $_DATA['loan_info_no'] ) )
                {
                    $loan->loanInfo['contract_day']    = $_DATA['new_contract_day'];

                    $cday_date = $loan->loanInfo['take_date'];
                    $loan->cdayInfo[$cday_date]['cday_date']    = $cday_date;
                    $loan->cdayInfo[$cday_date]['contract_day'] = $_DATA['new_contract_day'];
        
                    $loan->loanInfo['return_date']     = $loan->getNextReturnDate( $loan->loanInfo['take_date'] );

                    $msg['cday']['str'] = "차기수납일은 최종이수일 기준으로 변경됩니다. ";
                    $msg['cday']['add'] = ( !empty($msg['cday']['add'])?$msg['cday']['add'].", ":"" ) . $no ."번계약 차기수납일 : ".Func::dateFormat($loan->loanInfo['return_date']);
                }
            }
        }


        return view("inc.loanPlanChg")  -> with("plan_old", $plan_old ?? array())
                                        -> with("plan_new", $plan_new ?? array())
                                        -> with("loan_info_nos", $loan_info_nos ?? array())
                                        -> with("selected_no", $_DATA['loan_info_no'])
                                        -> with("msg", $msg ?? array());
    }

    /*
        $_DATA  : 조건변경데이터 배열

        @return [ key : 에러부분 / value : 'Y' OR '에러 문구' ]
    */
    public function validCheck($_DATA)
    {
        $arrayRslt = [];

        // log::debug(print_r($_DATA,true));

        //  반영완료 -> 거절,취소 시 체크할 사항
        if( $_DATA['old_status'] == "Y" && ($_DATA['status'] == "X" || $_DATA['status'] == "D") )
        {
            $condition = DB::TABLE("loan_condition")->SELECT("*")->WHERE("no", $_DATA['no'])->first();
            $condition = Func::chungDec(["LOAN_CONDITION"], $condition);	// CHUNG DATABASE DECRYPT            
            log::debug(print_r($condition,true));

            
            //  다계약 건일 경우
            if( $condition->rate_all_chg_yn == "Y" || $condition->cday_all_chg_yn == "Y" || $condition->rmoney_all_chg_yn == "Y" || $condition->rdate_all_chg_yn == "Y" )
            {
                //  조건변경 데이터에 입력된 반영됐던데이터들 만 가져온다.
                $loans = DB::TABLE("loan_condition_data")->SELECT("loan_info_no as no")->WHERE("loan_condition_no", $_DATA['no'])->get()->toArray();
                $loans = Func::chungDec(["LOAN_CONDITION_DATA"], $loans);	// CHUNG DATABASE DECRYPT
            }
            else
            {
                $loans = DB::TABLE("loan_info")->SELECT("*")->WHERE("save_status", "Y")->WHERE("no", $_DATA['loan_info_no'])->get()->toArray();
                $loans = Func::chungDec(["LOAN_INFO"], $loans);	// CHUNG DATABASE DECRYPT
            }
            
            $lc_array = DB::TABLE("loan_condition")->SELECT("*")->WHERE("cust_info_no", $condition->cust_info_no)->WHERE("status", "Y")->WHERE("save_time", ">", $condition->save_time)->get()->toArray();
            $lc_array = Func::chungDec(["LOAN_CONDITION"], $lc_array);	// CHUNG DATABASE DECRYPT
            
            $firstCheck = true;
            
            foreach( $loans as $loan )
            {
                $nos[] = $loan->no;
                
                if( !empty($lc_array) )
                {
                    foreach( $lc_array as $lc )
                    {
                        if( $lc->loan_info_no == $loan->no || $lc->rate_all_chg_yn == "Y" || $lc->cday_all_chg_yn == "Y" || $lc->rmoney_all_chg_yn == "Y" || $lc->rdate_all_chg_yn == "Y" )
                        {
                            $arrayRslt["main"]["str"] = "※ 해당 조건변경 반영 이후 조건변경이 존재합니다. 이후 건 거절(취소) 후 시도하세요. (조건변경번호 :$lc->no)";
                            $arrayRslt['result'] = "E";
                            return $arrayRslt;
                        }
                    }
                }

                $_tradeNo = DB::TABLE("loan_info_trade")->SELECT("no")->WHERE('loan_info_no', $loan->no)->WHERE('save_status', 'Y')->WHERE('save_time', ">", $condition->loan_info_update_time)->ORDERBY('save_time','desc')->ORDERBY('no','desc')->FIRST();
    
                if( !empty($_tradeNo->no) )
                {
                    if( $firstCheck )
                    {
                        log::info("여기를 걸려야 함 !");
                        $arrayRslt["main"]["str"] = "※ 조건변경 반영 후 거래가 있습니다. 거래 취소 후 시도하세요.";
                        $arrayRslt["main"]["loan_info_no"] = "$loan->no 번";
                    }
                    else
                    {
                        $arrayRslt["main"]["loan_info_no"] = $arrayRslt["main"]["loan_info_no"].", ".$loan->no." 번";
                    }
                }
            }


            if( empty($arrayRslt) )
            {
                $arrayRslt["main"]["str"] = "※ 거절 가능";
                $arrayRslt["result"] = "YN";
                $arrayRslt["nos"] = $nos;
                
                return $arrayRslt;
            }
            else
            {
                $arrayRslt['result'] = "E";
                return $arrayRslt;
            }
        }

        //  =========================================== 입력값 확인 DE ===========================================

        //  거절일 경우엔 데이터 검사 필요 없음
        if( $_DATA['status'] != "X" )
        {

            if( !isset($_DATA['bit']) )
            {
                $arrayRslt["main"]["str"] = "※ 변경 조건을 입력하세요.";
                $arrayRslt['result'] = "DE";
                return $arrayRslt;
            }

            if( in_array("R", $_DATA['bit']) && ( !$_DATA['new_rate'] || !$_DATA['new_delay_rate'] ) )
            {
                $arrayRslt["rate"]["str"] = ["금리를 정확하게 입력해주세요."];
            }
            
            if( in_array("C", $_DATA['bit']) && ( !$_DATA['new_contract_day'] ) )
            {
                $arrayRslt["cday"]["str"] = ["약정일을 선택해 주세요."];
            }
            
            if( in_array("M", $_DATA['bit']) )
            {
                if( !isset($_DATA['new_monthly_return_money']) || $_DATA['new_monthly_return_money']=="" )
                {
                    $arrayRslt["rmoney"]["str"] = ["월상환액을 입력해 주세요."];
                }
                else
                {
                    $_DATA['new_monthly_return_money'] = str_replace(",","",$_DATA['new_monthly_return_money']);
                }
            }
            
            if( in_array("D", $_DATA['bit']) )
            {
                if( !$_DATA['new_return_date'] )
                {
                    $arrayRslt["rdate"]["str"] = ["상환일을 입력해 주세요."];
                }
                else if( $_DATA['basis_date'] >= $_DATA['new_return_date'] )
                {
                    $arrayRslt["rdate"]["str"] = ["상환일은 반영기준일 이전일 수 없습니다."];
                }
                
                $_DATA['new_return_date'] = str_replace("-","",$_DATA['new_return_date']);
            }

            if( !$_DATA['basis_date'] )
            {
                $arrayRslt["main"]["str"] = "※ 반영기준일을 입력하세요.";
            }
            // else if( $_DATA['basis_date'] > date("Y-m-d") )
            // {
            //     $arrayRslt["main"]["str"] = "※ 반영기준일 금일 이후 불가.";
            // }
            else
            {
                $_DATA['basis_date'] = str_replace("-","",$_DATA['basis_date']);
            }

            if( !empty($arrayRslt) )
            {
                $arrayRslt['result'] = "E";
                return $arrayRslt;
            }
        }

        //  =========================================== 계약 조건 확인 ===========================================

        //  다계약 선택 여부 체크
        if( isset($_DATA['all_chg_yn']) )
        {
            //  정상, 연체 만 확인
            $loans = DB::TABLE("loan_info")->SELECT("*")->WHERE("save_status", "Y")->WHERE("cust_info_no", $_DATA['cust_info_no'])->whereIn("status",['A','B'])->get()->toArray();
            $loans = Func::chungDec(["LOAN_INFO"], $loans);	// CHUNG DATABASE DECRYPT
        }
        else
        {
            $loans = DB::TABLE("loan_info")->SELECT("*")->WHERE("save_status", "Y")->WHERE("no", $_DATA['loan_info_no'])->get()->toArray();
            $loans = Func::chungDec(["LOAN_INFO"], $loans);	// CHUNG DATABASE DECRYPT
        }

        $i = 0;

        $nos = [];

        foreach( $loans as $loan )
        {
            $i++;
            $nos[] = $loan->no;

            $_LOAN = new loan($loan->no);

            if( $_DATA['status'] == "X" ) continue;
            
            // // 2021.10.08 정상건만 가능하도록 요청함 
            // -노현정부장 요청 : 운영데이터 넣는시점만 제외처리
            // if($_LOAN->loanInfo['status'] != "A")
            // {
            //     $str = "※ 조건변경은 정상채권만 가능합니다.";
            //     $arr_bit = ['R'=>"rate",'C'=>"cday",'D'=>"rdate",'M'=>"rmoney",];
            //     $arrayRslt[$arr_bit[$_DATA['bit'][0]]] = ["str" => $str, "loan_info_no" => $_LOAN->loanInfo['no']." 번"];
            //     break;
            // }
            // 2021.10.08 모든 조건변경에 대하여 반영기준일이 상환일에서 보정일수 11일을 뺀 날짜의 이후일수 없도록 수정요청함
            if( Loan::dateTerm($_DATA['basis_date'], $_LOAN->loanInfo['return_date']) < Loan::$bojungDay)
            {
                $str = "※ 반영기준일은 상환일 - 보정일수(".Loan::$bojungDay.") 이후로 불가합니다";
                $arr_bit = ['R'=>"rate",'C'=>"cday",'D'=>"rdate",'M'=>"rmoney",];
                $arrayRslt[$arr_bit[$_DATA['bit'][0]]] = ["str" => $str, "loan_info_no" => $_LOAN->loanInfo['no']." 번"];
                break;
            }

            //  금리, 월상환액, 상환일 변경의 경우엔 BASIS_DATE 기준
            if( in_array("R", $_DATA['bit']) || in_array("M", $_DATA['bit']) || in_array("D", $_DATA['bit']) )
            {
                if( $_DATA['basis_date'] < $_LOAN->loanInfo['take_date'] || ((in_array("M", $_DATA['bit']) || in_array("D", $_DATA['bit'])) && Loan::dateTerm($_DATA['basis_date'], $_LOAN->loanInfo['return_date']) < Loan::$bojungDay) )
                {
                    if(!isset($arrayRslt["main"]))
                    {
                        $str = "※ 반영기준일은 이수일 보다 이전일 수 없습니다.";
                        if( in_array("M", $_DATA['bit']) || in_array("D", $_DATA['bit']) ) $str = "※ 반영기준일은 이수일 보다 이전이거나 상환일 - 보정일수(".Loan::$bojungDay.") 이후로 불가합니다.";
                        $arrayRslt["main"] = ["str" => $str, "loan_info_no" => $_LOAN->loanInfo['no']." 번"];
                    }
                    else
                    {
                        $arrayRslt["main"]["loan_info_no"] = $arrayRslt["main"]["loan_info_no"].", ".$_LOAN->loanInfo['no']." 번";
                    }
                } 
            }
            
            if( in_array("C", $_DATA['bit']) )
            {
                // 이수일~상환일 텀이 보정일수보다 적을때
                if( Loan::dateTerm($_LOAN->loanInfo['take_date'], $_LOAN->loanInfo['return_date']) < Loan::$bojungDay )
                {
                    if(!isset($arrayRslt["cday"]))
                    {
                        $arrayRslt["cday"] = ["str" => "이수일이 상환일 - 보정일수(".Loan::$bojungDay.") 이후로 불가합니다.", "loan_info_no" => $_LOAN->loanInfo['no']." 번"];
                    }
                    else
                    {
                        $arrayRslt["cday"]["loan_info_no"] = $arrayRslt["cday"]["loan_info_no"].", ".$_LOAN->loanInfo['no']." 번";
                    }
                }
                else
                {
                    //  약정일 변경으로 변경될 상환일이 결재일 이전이라면, 변경할 수 없다. (이게 된다면, 계약의 상태가 바뀜!)
                    if( $_LOAN->getNextReturnDate($_LOAN->loanInfo['take_date'], $_DATA['new_contract_day']) < date("Ymd") )
                    {
                        if(!isset($arrayRslt["cday"]))
                        {
                            $arrayRslt["cday"] = ["str" => "변경시 상환일이 금일보다 이전일 수 없습니다.", "loan_info_no" => $_LOAN->loanInfo['no']." 번"];
                        }
                        else
                        {
                            $arrayRslt["cday"]["loan_info_no"] = $arrayRslt["cday"]["loan_info_no"].", ".$_LOAN->loanInfo['no']." 번";
                        }
                        
                    }
                }

            }

            if( in_array("D", $_DATA['bit']) && ( $_DATA['loan_info_no'] == $_LOAN->loanInfo['no'] || (isset($_DATA['all_chg_yn']) && in_array("rdate", $_DATA['all_chg_yn'])) ) )
            {
                //  상환일 변경의 경우, 자유상환만 가능하다.
                if( $_LOAN->loanInfo['return_method_cd'] != "F" )
                {
                    if(!isset($arrayRslt["rdate"]))
                    {
                        $arrayRslt["rdate"] = ["str" => "상환일 변경은 자유상환 외 불가", "loan_info_no" => $_LOAN->loanInfo['no']." 번"];
                    }
                    else
                    {
                        $arrayRslt["rdate"]["loan_info_no"] = $arrayRslt["rdate"]["loan_info_no"].", ".$_LOAN->loanInfo['no']." 번";
                    }
                }
            }
            
            // //  금리변동시, 현 계약 금리보다 높게 변동할 수 없다.
            // if( in_array("R", $_DATA['bit']) && ( $_DATA['loan_info_no'] == $_LOAN->loanInfo['no'] || (isset($_DATA['all_chg_yn']) && in_array("rate", $_DATA['all_chg_yn'])) ) )
            // {
            //     if($_DATA['new_rate'] > $_LOAN->loanInfo['loan_rate'] || $_DATA['new_delay_rate'] > $_LOAN->loanInfo['loan_delay_rate'])
            //     {
            //         if(!isset($arrayRslt["rate"]))
            //         {
            //             $arrayRslt["rate"] = ["str" => "기존 금리 보다 높게 설정 불가", "loan_info_no" => $_LOAN->loanInfo['no']." 번"];
            //         }
            //         else
            //         {
            //             $arrayRslt["rate"]["loan_info_no"] = $arrayRslt["rate"]["loan_info_no"].", ".$_LOAN->loanInfo['no']." 번";
            //         }
            //     }
            // }
            
            //  월상환액변동은 원금균등, 원리금균등 만 가능하다.
            if( in_array("M", $_DATA['bit']) && ( $_DATA['loan_info_no'] == $_LOAN->loanInfo['no'] || (isset($_DATA['all_chg_yn']) && in_array("rmoney", $_DATA['all_chg_yn'])) ) )
            {
                if( !($_LOAN->loanInfo['return_method_cd'] == "R" || $_LOAN->loanInfo['return_method_cd'] == "B" || $_LOAN->loanInfo['return_method_cd'] == "F") )
                {
                    if(!isset($arrayRslt["rmoney"]))
                    {
                        $arrayRslt["rmoney"] = ["str" => "원금균등, 원리금균등 외 불가", "loan_info_no" => $_LOAN->loanInfo['no']." 번"];
                    }
                    else
                    {
                        $arrayRslt["rmoney"]["loan_info_no"] = $arrayRslt["rmoney"]["loan_info_no"].", ".$_LOAN->loanInfo['no']." 번";
                    }
                }
            }


        }


        if( !empty($arrayRslt) && $i == count($loans) )
        {
            $arrayRslt["result"] = "E";
            return $arrayRslt;
        }

        return ["result" => "Y", "nos" => $nos];
    }


    /**
     * 조건변경 액션 - 일괄금리인하 처리
     *
     * @return view
     */
    public function conditionLumpAction(Request $request)
    {
        $request->isDebug = true;

        $param = $request->input();


        if( sizeof($param['listChk'])==0 )
        {
            return "선택된 요청내역이 없습니다.";
        }

        
        if( !Func::funcCheckPermit("C010") )
        {
            return "조건 변경 결재 불가. \n결재 권한이 없습니다.";
        }

        // 일괄처리 구분
        if( $param['lump_action_code']=="LOAN_RATE_DOWN" )
        {
            $confirm_date = date("Ymd");
            $save_time    = date("YmdHis");
            $save_id      = Auth::id();
            $lump_basis_date = str_replace('-','',$param['lump_basis_date']);

            DB::beginTransaction();

            $DATA = DB::TABLE("loan_condition");
            $DATA->LEFTJOIN("loan_info", "loan_condition.loan_info_no", "=", "loan_info.no");
            $DATA->LEFTJOIN("loan_condition_data", [["loan_condition.no", "=", "loan_condition_data.loan_condition_no"], ["loan_condition.loan_info_no", "=", "loan_condition_data.loan_info_no"]]);
            $DATA->SELECT("loan_condition.*", "loan_condition_data.*", "loan_info.take_date", "loan_info.status as l_status", "loan_info.manager_code as l_manager_code", "loan_info.loan_rate as l_loan_rate", "loan_info.loan_delay_rate as l_loan_delay_rate");
            $DATA->WHERE("loan_condition.status","A");
            $DATA->WHERE("loan_condition.condition_bit","R");
            $DATA->WHEREIN("loan_condition.no",$param['listChk']);
            $rslt = $DATA->GET();
            $rslt = Func::chungDec(["LOAN_CONDITION","LOAN_INFO","LOAN_CONDITION_DATA"], $rslt);	// CHUNG DATABASE DECRYPT
            foreach( $rslt as $val )
            {

                $basis_date = max( $lump_basis_date, Loan::addDay($val->take_date) );

                // 조건변경 승인처리
                $up_val = [];
                $up_val['save_time']    = $save_time;
                $up_val['save_id']      = $save_id;
                $up_val['basis_date']   = $basis_date;
                $up_val['confirm_date'] = $confirm_date;
                $up_val['status']       = "Y";
                $up_val['memo']         = $val->memo."\n일괄금리인하 실행";
                $rslt_up = DB::dataProcess("UPD", "loan_condition", $up_val, ['no'=>$val->no]);
                if( $rslt_up!="Y" )
                {
                    DB::rollback();
                    return "처리 중 오류가 발생하였습니다.";
                }


                // 금리등록
                $rt_val = [];
                $rt_val['loan_info_no']    = $val->loan_info_no;
                $rt_val['rate_date']       = $basis_date;
                $rt_val['loan_rate']       = $val->new_rate;
                $rt_val['loan_delay_rate'] = $val->new_delay_rate;
                $rt_val['save_time']       = $save_time;
                $rt_val['save_id']         = $save_id;
                $rt_val['save_status']     = "Y";
                // 기준일 이후 금리는 지워
                $rslt_up = DB::dataProcess('UPD', 'loan_info_rate', ['save_status'=>'N', 'del_time'=>$rt_val['save_time'], 'del_id'=>$rt_val['save_id']], [['loan_info_no',$rt_val['loan_info_no']], ['rate_date',">=",$rt_val['rate_date']]]);
                // 기준일 등록
                $rslt_up = DB::dataProcess('INS', 'loan_info_rate', $rt_val);
                if( $rslt_up!="Y" )
                {
                    DB::rollback();
                    return "처리 중 오류가 발생하였습니다.";
                }

                // 금리적용일이 오늘보다 작거나같으면 계약에도 업데이트
                if( $basis_date<=$confirm_date )
                {
                    $rslt_up = DB::dataProcess('UPD', 'loan_info', ['loan_rate'=>$rt_val['loan_rate'], 'loan_delay_rate'=>$rt_val['loan_delay_rate']], ['no'=>$rt_val['loan_info_no']]);
                    if( $rslt_up!="Y" )
                    {
                        DB::rollback();
                        return "처리 중 오류가 발생하였습니다.";
                    }
                }

                // 메모남기기
                $_MEMO = [];
                $_MEMO['cust_info_no'] = $val->cust_info_no;
                $_MEMO['save_time']    = $save_time;
                $_MEMO['save_id']      = $save_id;
                $_MEMO['save_status']  = "Y";
                $_MEMO['div']          = "CD";
                $_MEMO['memo']         = "[일괄금리인하 실행] 조건변경 완료";
                $result_memo = Func::saveMemo($_MEMO);


                $result_interest = Loan::updateLoanInfoInterest($val->loan_info_no, date("Ymd"));
                if( $result_interest!="Y" )
                {
                    DB::rollback();
                    return "처리 중 오류가 발생하였습니다.";
                }

                //  원장변경내역 입력
                $_wch = [
                    "cust_info_no"  =>  $val->cust_info_no,
                    "loan_info_no"  =>  $val->loan_info_no,
                    "worker_id"     =>  Auth::id(),
                    "work_time"     =>  $save_time,
                    "worker_code"   =>  Auth::user()->branch_code,
                    "loan_status"   =>  $val->l_status,
                    "manager_code"  =>  $val->l_manager_code,
                    "div_nm"        =>  "일괄금리인하(정상)",
                    "before_data"   =>  number_format($val->l_loan_rate,2),
                    "after_data"    =>  number_format($val->new_rate,2),
                    "trade_type"    =>  "",
                    "sms_yn"        =>  "N",
                    "memo"          =>  "",
                ];

                $result_wch = Func::saveWonjangChgHist($_wch);
                if( $result_wch != "Y" )
                {
                    DB::rollBack();
                    log::info("일괄금리인하(정상) - 원장변경내역 저장 실패 계약번호 : ".$val->loan_info_no);
                    return "처리 중 오류가 발생하였습니다.";
                }

                $_wch['div_nm']      = "일괄금리인하(연체)";
                $_wch['before_data'] = number_format($val->l_loan_delay_rate,2);
                $_wch['after_data']  = number_format($val->new_delay_rate,2);

                $result_wch = Func::saveWonjangChgHist($_wch);
                if( $result_wch != "Y" )
                {
                    DB::rollBack();
                    log::info("일괄금리인하(연체) - 원장변경내역 저장 실패 계약번호 : ".$val->loan_info_no);
                    return Redirect::back()->with('result', '원장변경내역 등록 실패\\n계약번호 : '.$val->loan_info_no);
                }


                // 다계좌 일괄처리
                if( $val->rate_all_chg_yn=="Y" )
                {
                    $LOAN = DB::TABLE("loan_info");
                    $LOAN->SELECT("no, loan_rate, loan_delay_rate, take_date, cust_info_no, status, manager_code");
                    $LOAN->WHERE("save_status",  "Y");
                    $LOAN->WHEREIN("status",  ['A','B']);
                    $LOAN->WHERE("cust_info_no", $val->cust_info_no);
                    $LOAN->WHERE("no", "!=", $val->loan_info_no);
                    $lond = $LOAN->GET();
                    $lond = Func::chungDec(["LOAN_INFO"], $lond);	// CHUNG DATABASE DECRYPT
                    foreach( $lond as $vln )
                    {
                        $basis_date = max( $lump_basis_date, Loan::addDay($vln->take_date) );

                        // 금리등록
                        $rt_val = [];
                        $rt_val['loan_info_no']    = $vln->no;
                        $rt_val['rate_date']       = $basis_date;
                        $rt_val['loan_rate']       = $val->new_rate;
                        $rt_val['loan_delay_rate'] = $val->new_delay_rate;
                        $rt_val['save_time']       = $save_time;
                        $rt_val['save_id']         = $save_id;
                        $rt_val['save_status']     = "Y";
                        // 기준일 이후 금리는 지워
                        $rslt_up = DB::dataProcess('UPD', 'loan_info_rate', ['save_status'=>'N', 'del_time'=>$rt_val['save_time'], 'del_id'=>$rt_val['save_id']], [['loan_info_no',$rt_val['loan_info_no']], ['rate_date',">=",$rt_val['rate_date']]]);
                        // 기준일 등록
                        $rslt_up = DB::dataProcess('UST', 'loan_info_rate', $rt_val, ['loan_info_no'=>$rt_val['loan_info_no'], 'rate_date'=>$rt_val['rate_date']]);
                        if( $rslt_up!="Y" )
                        {
                            DB::rollback();
                            return "처리 중 오류가 발생하였습니다.";
                        }

                        // 금리적용일이 오늘보다 작거나같으면 계약에도 업데이트
                        if( $basis_date<=$confirm_date )
                        {
                            $rslt_up = DB::dataProcess('UPD', 'loan_info', ['loan_rate'=>$rt_val['loan_rate'], 'loan_delay_rate'=>$rt_val['loan_delay_rate']], ['no'=>$rt_val['loan_info_no']]);
                            if( $rslt_up!="Y" )
                            {
                                DB::rollback();
                                return "처리 중 오류가 발생하였습니다.";
                            }
                        }

                        // LOAN_CONDITION_DATA
                        $rt_pln = [];
                        $rt_pln['loan_condition_no'] = $val->no;
                        $rt_pln['loan_info_no']      = $vln->no;
                        $rt_pln['old_rate']          = $vln->loan_rate;
                        $rt_pln['old_delay_rate']    = $vln->loan_delay_rate;
                        $rt_pln['new_rate']          = $val->new_rate;
                        $rt_pln['new_delay_rate']    = $val->new_delay_rate;
                        $rslt_up = DB::dataProcess('UST', 'loan_condition_data', $rt_pln, ['loan_condition_no'=>$rt_pln['loan_condition_no'], 'loan_info_no'=>$rt_pln['loan_info_no']]);
                        if( $rslt_up!="Y" )
                        {
                            DB::rollback();
                            return "처리 중 오류가 발생하였습니다.";
                        }

                        $result_interest = Loan::updateLoanInfoInterest($vln->no, date("Ymd"));
                        if( $result_interest!="Y" )
                        {
                            DB::rollback();
                            return "처리 중 오류가 발생하였습니다.";
                        }

                        
                        //  원장변경내역 입력
                        $_wch = [
                            "cust_info_no"  =>  $vln->cust_info_no,
                            "loan_info_no"  =>  $vln->no,
                            "worker_id"     =>  Auth::id(),
                            "work_time"     =>  $save_time,
                            "worker_code"   =>  Auth::user()->branch_code,
                            "loan_status"   =>  $vln->status,
                            "manager_code"  =>  $vln->manager_code,
                            "div_nm"        =>  "일괄금리인하(정상)",
                            "before_data"   =>  number_format($vln->loan_rate,2),
                            "after_data"    =>  number_format($val->new_rate,2),
                            "trade_type"    =>  "",
                            "sms_yn"        =>  "N",
                            "memo"          =>  "",
                        ];

                        $result_wch = Func::saveWonjangChgHist($_wch);
                        if( $result_wch != "Y" )
                        {
                            DB::rollBack();
                            log::info("일괄금리인하(정상) - 원장변경내역 저장 실패 계약번호 : ".$vln->no);
                            return "처리 중 오류가 발생하였습니다.";
                        }

                        $_wch['div_nm']      = "일괄금리인하(연체)";
                        $_wch['before_data'] = number_format($vln->loan_delay_rate,2);
                        $_wch['after_data']  = number_format($val->new_delay_rate,2);
        
                        $result_wch = Func::saveWonjangChgHist($_wch);
                        if( $result_wch != "Y" )
                        {
                            DB::rollBack();
                            log::info("일괄금리인하(연체) - 원장변경내역 저장 실패 계약번호 : ".$vln->no);
                            return Redirect::back()->with('result', '원장변경내역 등록 실패\\n계약번호 : '.$vln->no);
                        }
                    }

                }

            }

            DB::commit();
            return "Y";
        }
        else
        {
            return "가능한 일괄처리가 없습니다.";
        }



    }



    /**
     * 조건변경 취소액션 - 입금삭제시 약정일 조건변경 원복
     *
     * @return String
     */
    public function conditionCancel(Request $request)
    {
        log::info("HI conditionCancel");
        log::info($request);

        $save_id   = Auth::id();
        $save_time = date("YmdHis");

        DB::beginTransaction();
        // LOAN

        $lc = DB::TABLE("loan_condition c")->LEFTJOIN("loan_condition_data cd","c.no","=","cd.loan_condition_no")->LEFTJOIN("loan_info_cday lc","c.loan_info_update_time","=","lc.save_time")
                        ->SELECT("c.*","cd.old_contract_day,cd.old_return_date")
                        ->WHERE("c.loan_info_no",$request->loan_info_no)
                        ->WHERE("c.condition_bit","c")
                        ->WHERE("C.STATUS","Y")
                        ->WHERE("lc.loan_info_no",$request->loan_info_no)
                        ->WHERE("lc.cday_date",$request->trade_in_date)
                        ->GET()->FIRST();
        $lc = Func::chungDec(["LOAN_CONDITION","LOAN_CONDITION_DATA","LOAN_INFO_CDAY"], $lc);	// CHUNG DATABASE DECRYPT
        LOG::INFO(print_r($lc,true));

        //  원복할 데이터
        // $lcd = DB::TABLE("LOAN_CONDITION_DATA")->SELECT("*")->WHERE("loan_condition_no", $condition->no)->WHERE("loan_info_no", $no)->first();
        if(!empty($lc))
        {
            log::info("찾았다");

            $_N_DATA = array(
                "confirm_date"  =>date("Ymd"), 
                "status"        =>"D",  //취소상태
                "save_time"     =>$save_time, 
                "save_id"       =>$save_id, 
            );
        
            $rslt['c'] = DB::dataProcess("UPD", "loan_condition", $_N_DATA, ['no'=>$lc->no]);

            $loan      = new loan($request->loan_info_no);
            $_cday = [];
            //  약정일
            if( count($loan->cdayInfo) == 1 )       //  약정일이 하나뿐이면 해당 건을 전 약정일 정보로 바꿔야 함
            {
                $_cday['contract_day'] = $lc->old_contract_day;
                $_cday['save_time']    = $save_time;
                $_cday['save_id']      = $save_id;
            }
            else
            {
                $_cday['del_time']        = $save_time;
                $_cday['del_id']          = $save_id;
                $_cday['save_status']     = "N";
            }
            
            log::info("약정일 ($request->loan_info_no)".print_r($_cday,true));
            //  take_date 기준 으로 하면 (자유상환)입금 삭제가 발생했을 때 문제가 생김.... (ex)조건번경 이전 입금거래 삭제  ->  save_time만 가지고 찾음...
            // $result_cday = DB::dataProcess('UPD', 'LOAN_INFO_CDAY', $_cday, ['loan_info_no'=>$no, 'cday_date'=>$loan->loanInfo['take_date'], 'save_time'=>$condition->loan_info_update_time]);
            $rslt['cday'] = DB::dataProcess('UPD', 'loan_info_cday', $_cday, ['loan_info_no'=>$request->loan_info_no, 'save_time'=>$lc->loan_info_update_time]);

            //  약정일 변경 -> 상환일 변경
            $_loanInfo = [];
            $_loanInfo['no']              = $request->loan_info_no;
            $_loanInfo['save_time']       = $save_time;
            $_loanInfo['save_id']         = $save_id;
            $_loanInfo['contract_day']    = $lc->old_contract_day;
            $_loanInfo['return_date']     = $lc->old_return_date;
            $_loanInfo['kihan_date']      = $loan->getNextKihanDate($_loanInfo['return_date'], $_loanInfo['contract_day']);
            $_loanInfo['return_date_biz'] = $loan->getBizDay($_loanInfo['return_date']);
            $_loanInfo['kihan_date_biz']  = $loan->getBizDay($_loanInfo['kihan_date']);

            LOG::INFO("LOAN_INFO 업데이트내용");
            LOG::INFO(print_r($_loanInfo,true));
            $rslt['loan'] = DB::dataProcess("UPD", "loan_info", $_loanInfo, ["no"=>$_loanInfo['no']]);

            // 메모입력하자
            $_MEMO = [];
            $_MEMO['cust_info_no'] = $request->cust_info_no;
            $_MEMO['loan_info_no'] = $request->loan_info_no;
            $_MEMO['save_time']    = $save_time;
            $_MEMO['save_id']      = $save_id;
            $_MEMO['save_status']  = "Y";
            $_MEMO['div']          = "CD";
            $_MEMO['memo']         = "입금삭제로 약정일변경 자동취소. 입금삭제 최종결재자 ID : ".$save_id;

            $rslt['m'] = Func::saveMemo($_MEMO);



        }
        else
        {
            log::info("찾을수없다");
            return ["rslt"=>"N","msg"=>"약정일변경건을 찾을수 없습니다."];
        }

        log::info(print_r($rslt,true));
        
        if($rslt['c'] == "Y" && $rslt['cday'] == "Y" && $rslt['m'] == "Y" && $rslt['loan'] == "Y")
        {
            DB::commit();
            return ["rslt"=>"Y","msg"=>"성공~"];
        }
        else
        {
            DB::rollback();
            return ["rslt"=>"N","msg"=>"취소중 오류발생"];
        }
    }

    /**
     * 고객정보창 보증인정보 면탈요청
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function conditionGuarantor(Request $request)
    {
        $gno = $request->gno;
        
        if(!$gno)
        {
        }
        else
        {
            $array_config = Func::getConfigArr();
            $array_return_method = $array_config['return_method_cd'];

            // 보증인 정보
            $gi = DB::TABLE("loan_info_guarantor")->SELECT("*")
                                                    ->WHERE('no', $gno)
                                                    ->WHERE('save_status', 'Y')
                                                    ->get()
                                                    ->toArray();
            $gi = Func::chungDec(["loan_info_guarantor"], $gi);	// CHUNG DATABASE DECRYPT

            $gi[0]->ssn1            = substr($gi[0]->ssn,0,6)?substr($gi[0]->ssn,0,6):"";
            $gi[0]->ssn2            = substr($gi[0]->ssn,6)?substr($gi[0]->ssn,6):"";
            $gi[0]->job_codestr     = $gi[0]->job_cd?Func::getJobCdStr($gi[0]->job_cd):"";
            
            // 계약정보
            $loan = (array)DB::TABLE("loan_info")->join('cust_info', 'cust_info.no', 'loan_info.cust_info_no')
                            ->select("loan_info.*", 'cust_info.name')
                            ->where("loan_info.save_status", "Y")
                            ->where("cust_info.save_status", "Y")
                            ->where("loan_info.no", $gi[0]->loan_info_no)->first();
           
            // $loan = Func::chungDec(["loan_info"], $loan);	// CHUNG DATABASE DECRYPT
            $loan['name'] = Func::chungDecOne($loan['name']);
            $loan['return_method_nm'] = $array_return_method[$loan['return_method_cd']];
            
        }

        return view('erp.conditionGuarantor')
                ->with('v', $gi[0])
                ->with('simple', $loan)
                ;
    }
}
