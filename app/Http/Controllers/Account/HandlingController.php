<?php
namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;
use App\Chung\Paging;
use App\Chung\Vars;
use Illuminate\Support\Facades\Storage;
use DataList;
use Illuminate\Support\Facades\Response;
use ExcelFunc;
use Loan;
use Trade;

class HandlingController extends Controller
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
     * 양수/양도결재 조회 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataHandlingList(Request $request){

        $array_conf_code = Func::getConfigArr();

        $list   = new DataList(Array("listName"=>"handlingList","listAction"=>'/'.$request->path()));
        
        if(!isset($request->tabs))
        {
            $request->tabs = "Y";
        }
        $list->setTabs(Array('Y'=>'정상', 'N'=>'삭제'),$request->tabs);   // ,'D2'=>'삭제 1차결재'
        $list->setCheckBox("no");

        $list->setSearchDate('날짜검색', Array('T.trade_date'=>'발생일', 'T.save_time'=>'등록일', 'T.del_time'=>'삭제일'), 'searchDt', 'Y', 'N', date("Y-m-d"), date("Y-m-d"), 'T.trade_date');
        $list->setRangeSearchDetail(Array('T.trade_money'=>'취급수수료'),'','','단위(원)');

        if( Func::funcCheckPermit("E004") || Func::funcCheckPermit("E031") )
        {
            $list->setSearchType('l-manager_code', Func::myPermitBranch(), '관리지점');
        }
   
        $list->setSearchDetail(Array('C.name'=>'이름', 'T.cust_info_no'=>'차입자번호', 'T.loan_info_no'=>'계약번호', 'ssn'=>'주민번호' ));

        if( Func::funcCheckPermit("R023") && Func::funcCheckPermit("A132","A") )
        {
            $list->setPlusButton("handlingForm('');");
        }

        return $list;
    }

    /**
     * 양수/양도결재 메인
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function handling(Request $request)
    {
        $list   = $this->setDataHandlingList($request);
        $list->setLumpForm('DEL', Array('BTN_NAME'=>'삭제처리','BTN_ACTION'=>'lump_del(this);','BTN_ICON'=>'','BTN_COLOR'=>''));

        $list->setlistTitleCommon(Array
        (
            'no'               => Array('일련번호', 0, '', 'center', '', 'no'),
            'cust_info_no'     => Array('차입자번호', 0, '', 'center', '', 'cust_info_no', ['loan_info_no'=>['계약번호', 'loan_info_no', '<br>']]),
            'seq'              => Array('순번', 0, '', 'center', '', 'seq'),
            'name'             => Array('이름', 0, '', 'center', '', 'name'),
            'ssn'              => Array('생년월일', 0, '', 'center', '', 'ssn'),
            'manager_name'     => Array('관리지점', 0, '', 'center', '', 'l.manager_code'),
            'loan_date'        => Array('계약일', 0, '', 'center', '', 'loan_date'),
            'contract_day'     => Array('약정일', 0, '', 'center', '', 'contract_day'),

            'trade_type'       => Array('처리구분', 0, '', 'center', '', 'trade_type'),
            'trade_date'       => Array('발생일', 0, '', 'center', '', 'trade_date'),
            'trade_money'      => Array('취급수수료', 0, '', 'center', '', 'trade_money'),
            'l_status'         => Array('현상태', 0, '', 'center', '', 'status'),

            'balance'          => Array('잔액', 0, '', 'center', '', 't.balance'),
            'save_id'          => Array('등록자', 0, '', 'center', '', 't.save_id', ['save_time'=>['등록시간', 't.save_time', '<br>']]),
        ));
        $list->setlistTitleTabs('N',Array
        (
            'del_id'           => Array('삭제자<br>삭제시간', 0, '', 'center', '', 'del_id'),
        ));

        //Log::debug(print_r($result, true));
        return view('account.handling')->with('result', $list->getList())->with("arr_confirm_id",Func::getArrConfirmId(['D'=>'30','L'=>'31']));
    }   
    
    /**
     * 양수/양도결재 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function handlingList(Request $request)
    {
        $list   = $this->setDataHandlingList($request);
        $param  = $request->all();

        // 삭제요청, 삭제1차결재 탭을 위한 카운트 쿼리 
        $BOXC = DB::TABLE("LOAN_INFO AS L")->JOIN("CUST_INFO AS C", "L.CUST_INFO_NO", "=", "C.NO")->JOIN("LOAN_INFO_TRADE AS T", "L.NO", "=", "T.LOAN_INFO_NO");
        $BOXC = $BOXC->SELECT(DB::RAW("
        COALESCE(SUM(CASE WHEN T.SAVE_STATUS = 'Y' THEN 1 ELSE 0 END),0) AS Y, 
        COALESCE(SUM(CASE WHEN T.SAVE_STATUS = 'N' THEN 1 ELSE 0 END),0) AS N"));
        $BOXC->WHERE('C.SAVE_STATUS','Y');
        $BOXC->WHERE('L.SAVE_STATUS','Y');
        $BOXC->WHERE('T.TRADE_DIV','F');
        // 전지점 조회권한 없으면 자기 지점만
        if( !Func::funcCheckPermit("E004") )
        {
            $BOXC->WHEREIN('L.MANAGER_CODE', array_keys(Func::myPermitBranch()));
        }
        if(!empty($request->searchDt))
        {
            if(!empty($request->searchDtString))
            {
                $BOXC->WHERE($request->searchDt, ">=" , str_replace("-",'',$request->searchDtString));
            }
            if(!empty($request->searchDtStringEnd))
            {
                $BOXC->WHERE($request->searchDt, "<=" ,  str_replace("-",'',$request->searchDtStringEnd));
            }
        }
        $count = $BOXC->FIRST();
        $r['tabCount'] = array_change_key_case((Array)$count, CASE_UPPER);


        // 기본쿼리
        $LOAN = DB::TABLE("LOAN_INFO AS L")->JOIN("CUST_INFO AS C", "L.CUST_INFO_NO", "=", "C.NO")->JOIN("LOAN_INFO_TRADE AS T", "L.NO", "=", "T.LOAN_INFO_NO");
        $LOAN->SELECT("T.*", "C.NAME", "C.SSN", "L.LOAN_DATE", "L.PRO_CD", "L.RETURN_METHOD_CD", "L.LOAN_RATE", "L.STATUS", "L.CONTRACT_DAY", "L.MANAGER_CODE AS L_MANAGER_CODE");
        $LOAN->WHERE('C.SAVE_STATUS','Y');
        $LOAN->WHERE('L.SAVE_STATUS','Y');
        $LOAN->WHERE('T.TRADE_DIV','F');
        
        // 탭 검색
        $param['tabSelectNm'] = "T.SAVE_STATUS";
        $param['tabsSelect']  = $request->tabsSelect;

        if(!isset($param['listOrder']))
        {
            $param['listOrderAsc'] = "DESC";
            $param['listOrder']    = "T.SAVE_TIME";
        }

        // 전지점 조회권한 없으면 자기 지점만
        if( !Func::funcCheckPermit("E004") )
        {
            $LOAN->WHEREIN('L.MANAGER_CODE', array_keys(Func::myPermitBranch()));
        }

        $LOAN = $list->getListQuery("T", 'main', $LOAN, $param);

        Log::debug(Func::printQuery($LOAN));

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $sum_data = Array
        (
            ["COALESCE(SUM(T.TRADE_MONEY),0)", '발생금액', '원'],
            // ["COALESCE(SUM(T.RETURN_ORIGIN),0)", '원금', '원'],
            // ["COALESCE(SUM(T.RETURN_INTEREST_SUM),0)", '이자', '원'],
            // ["COALESCE(SUM(T.RETURN_DAMBO_SET_FEE+T.RETURN_COST_MONEY),0)", '비용', '원'],
        );
        $paging = new Paging($LOAN, $request->page, $request->listLimit, 10, $request->listName, '', $sum_data);


        // 뷰단 데이터 정리.
        $array_conf_code    = Func::getConfigArr();
        $arrBranch          = Func::getBranch();
        $arrayUserId        = Func::getUserId();

        $cnt = 0;
        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","LOAN_INFO_TRADE"], $rslt);	// CHUNG DATABASE DECRYPT

        foreach( $rslt as $v )
        {
            $v->trade_money = number_format($v->trade_money);
            $link          = 'javascript:window.open("/account/custpop?cust_info_no='.$v->cust_info_no.'&no='.$v->loan_info_no.'","msgpop","width=2000, height=1000, scrollbars=yes")';
            $v->loan_info_no = "<a href='".$link.";'>".$v->loan_info_no."</a>";

            if ($v->delay_term > 0) {
                $v->delay_term       = '<span class="text-red">' . $v->delay_term . '</span>';
            }
            $v->name             = Func::nameMasking($v->name, 'Y');
            $v->ssn              = Func::ssnFormat($v->ssn, 'Y');
            $v->pro_cd           = Func::getArrayName($array_conf_code['pro_cd'], $v->pro_cd);
            $v->loan_date        = Func::dateFormat($v->loan_date);
            $v->trade_type       = Func::getArrayName($array_conf_code['trade_fee_type'], $v->trade_type);
            $v->name             = $v->name;    // name 추가
            $v->trade_date       = Func::dateFormat($v->trade_date);
            $v->balance          = number_format($v->balance);
            $v->save_time        = "<input type='hidden' id='td_".$v->no."' value='".substr($v->save_time,0,8)."'>".Func::dateFormat($v->save_time);

            $v->del_time         = Func::dateFormat($v->del_time);
            $v->save_id          = Func::getArrayName($arrayUserId, $v->save_id);
            $v->del_id           = Func::getArrayName($arrayUserId, $v->del_id)."<br>".Func::dateFormat($v->del_time);

            $v->l_status         = Vars::$arrayContractStaColor[$v->status];
            if( $v->status=="C" || $v->status=="D" )
            {
                $v->status_nm2 = Func::getArrayName($array_conf_code['stl_div_cd'],$v->settle_div_cd);
                if( $v->status_nm2 )
                {
                    $v->l_status.= "(".substr($v->status_nm2,0,3).")";
                }
            }
            $v->manager_name     = "<input type='hidden' id='mc_".$v->no."' value='". $v->l_manager_code."'>".Func::nvl($arrBranch[$v->l_manager_code], $v->l_manager_code);

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
     * 취급수수료등록 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function handlingForm(Request $request)
    {        
        $request->isDebug = true;
        
        // 아직은 아니지만 나중에 파라미터로 계약번호가 넘어올 수 있다. 그렇게 되면 미리 폼에 값을 로딩해놔야함
        if( isset($request->loan_info_no) && $request->loan_info_no )
        {
            $loan_info_no = $request->loan_info_no;
        }
        else
        {
            $loan_info_no = 0;
        }

        $array_config = Func::getConfigArr();
         return view('account.handlingform')->with("loan_info_no", $loan_info_no)->with("array_config", $array_config)->with("v", $request);
    }
 
    public function handlingAction(Request $request)
    {
        $param = $request->all();
        Log::debug(print_r($param, true));

        $loanv = DB::TABLE('loan_info')->SELECT('last_trade_date, manager_code, manager_id')->WHERE('no', '=', $param['loan_info_no'])->FIRST();
        $loanv = Func::chungDec(["loan_info"], $loanv);	// CHUNG DATABASE DECRYPT
        $last_trade_date = $loanv->last_trade_date;
        $manager_code    = $loanv->manager_code;
        $manager_id      = $loanv->manager_id;
        
        DB::beginTransaction();
        unset($_DATA);
        $_DATA['cust_info_no'] = $param['cust_info_no'];
        $_DATA['loan_info_no'] = $param['loan_info_no'];
        $_DATA['save_status'] = "Y";
        $_DATA['save_id'] = Auth::id();
        $_DATA['save_time'] = date("YmdHis");
        $_DATA['trade_date'] = str_replace("-","",$param['trade_date']);
        $_DATA['trade_money'] = str_replace(",","",$param['trade_money']);
        $_DATA['div'] = $param['div'];
        $_DATA['manager_code'] = $manager_code;
        $_DATA['manager_id'] = $manager_id;

        unset($_DATA['loan_info_trade_no']);
                
        // 마지막 거래일자와 비교 - 거래원장 등록
        if(date($last_trade_date) <= date($_DATA['trade_date']))
        {
            $trade = new Trade($param['loan_info_no']);
            // 거래구분(trade_type)은 대출취급수수료(01)로 고정
            $loan_info_trade_no = $trade->handlingFeeInsert(['cust_info_no'=>$param['cust_info_no'], 'loan_info_no'=>$param['loan_info_no'], 'trade_type'=>"01", "trade_date"=>$param['trade_date'], 'trade_money'=>$param['trade_money']]);
            if(is_numeric($loan_info_trade_no))
            {
                $_DATA['loan_info_trade_no'] = $loan_info_trade_no;
            }
            else
            {
                DB::rollBack();
                Log::debug("취급수수료 거래원장 등록 실패 : ".$loan_info_trade_no);
                return "취급수수료 거래원장 등록 실패 : ".$loan_info_trade_no;
            }
        }
        else
        {
            DB::rollBack();
            Log::debug("취급수수료는 마지막 거래원장날짜의 이후 날짜만 등록 가능합니다.");
            return "취급수수료는 마지막 거래원장날짜의 이후 날짜만 등록 가능합니다.";
        }
        
        // 처리구분이 상환등록일 경우 취급수수료액 기준으로 입금반영
        if($_DATA['div']=="02")
        {
            $_IN = $_DATA;
            $_IN['trade_type'] = "10";  // 입금구분 - 취급수수료상환(10)
            $_IN['lose_money'] = 0;
            $_IN['bank_cd']  = "";
            $_IN['bank_ssn'] = "";
            
            // 입금데이터 생성
            $_IN['action_mode'] = "INSERT";
            $trade = new Trade($_IN['loan_info_no']);
            $rslt = $trade->tradeInInsert($_IN);

            // 정상 처리될 경우, loan_info_trade의 no가 응답, 오류인경우 오류 메세지 응답
            if( !is_numeric($rslt) )
            {
                DB::rollBack();
                Log::debug($rslt['PROC_MSG'] ?? $rslt);
                return $rslt['PROC_MSG'] ?? $rslt;
            }
        }

        DB::commit();
        return "Y";
    }

    /**
     * 대출취급수수료 일괄삭제 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return String 처리결과 Y 또는 오류메세지
     */
    public function handlingDelete(Request $request)
    {
        $val = $request->input();
        DB::beginTransaction();

        if( $val['action_mode']=="LUMP_HANDLING_DELETE" && is_array($val['listChk']) && sizeof($val['listChk'])>0 )
        {
            for( $i=0; $i<sizeof($val['listChk']); $i++ )
            {
                $loan_info_trade_no = $val['listChk'][$i];

                // 입금정보 SELECT
                $rslt = DB::TABLE("LOAN_INFO_TRADE")->SELECT("*")->WHERE("NO", $loan_info_trade_no)->WHERE("SAVE_STATUS", "Y")->WHERE("TRADE_DIV", "F")->FIRST();
                $rslt = Func::chungDec(["LOAN_INFO_TRADE"], $rslt);	// CHUNG DATABASE DECRYPT
                $vt = (Array) $rslt;

                if( !$vt )
                {
                    DB::rollBack();
                    Log::debug("선택한 거래내역의 정보를 찾을 수 없습니다.");
                    return "선택한 거래내역의 정보를 찾을 수 없습니다.";
                }
                $loan_info_no = $vt['loan_info_no'];

                // 입금취소처리
                $trade = new Trade($loan_info_no);
                $rslt = $trade->handlingFeeDelete($loan_info_trade_no);
                if( is_string($rslt) )
                {
                    DB::rollBack();
                    Log::debug($rslt);
                    return $rslt;
                }
                
                //  원장변경내역 입력
                $_wch = [
                    "cust_info_no"  =>  $vt['cust_info_no'],
                    "loan_info_no"  =>  $vt['loan_info_no'],
                    "worker_id"     =>  Auth::id(),
                    "work_time"     =>  date("YmdHis"),
                    "worker_code"   =>  Auth::user()->branch_code,
                    "loan_status"   =>  "",
                    "manager_code"  =>  "",
                    "div_nm"        =>  "거래취소",
                    "before_data"   =>  "null,".$vt['trade_date'],       //  변경전값(null 셋팅인듯?),기산일자
                    "after_data"    =>  "null,".date("Ymd"),             //  변경후값(null 셋팅인듯?),취소일자
                    "trade_type"    =>  $vt['trade_type'],
                    "memo"          =>  "",
                ];

                $result_wch = Func::saveWonjangChgHist($_wch);
                if( $result_wch != "Y" )
                {
                    DB::rollBack();
                    return "원장변경내역 등록 실패.";
                }
            }
        }
        else
        {
            Log::debug("파라미터 에러");
            return "파라미터 에러";
        }

        DB::commit();
        return "Y";        
    }
    
    /**
     * 입출금 입력폼에서 찾기를 할 때, 결과 테이블 HTML 응답한다.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function searchLoanInfo(Request $request)
    {
        if( !isset($request->search_string) )
        {
            return "검색어를 입력하세요.";
        }
        $search_string = $request->search_string;

        // 기본쿼리
        $LOAN = DB::TABLE("LOAN_INFO")->JOIN("CUST_INFO", "LOAN_INFO.CUST_INFO_NO", "=", "CUST_INFO.NO");
        $LOAN->SELECT("LOAN_INFO.*", "CUST_INFO.NAME", "CUST_INFO.SSN");
        $LOAN->WHERE('CUST_INFO.SAVE_STATUS','Y');
        $LOAN->WHERE('LOAN_INFO.SAVE_STATUS','Y');

        // 검색
        $where = "";
        if( is_numeric($search_string) )
        {
            $where.= "CUST_INFO.NO=".$search_string." OR LOAN_INFO.NO=".$search_string." ";
            // 6자리 이상인 경우만 검색
            if( strlen($search_string)>=6 )
            {
                // $where.= "OR CUST_INFO.SSN like '".$search_string."%' ";
                $where.= 'or '.Func::encLikeSearchString('cust_info.ssn', $search_string, 'after');
            }            
        }
        else
        {
			$name = DB::table('loan_info as l')
				->join('cust_info as c', 'l.cust_info_no', '=', 'c.no')
				->select('l.no, c.name')
				->where('c.save_status', 'Y')
				->where('l.save_status', 'Y')
				->orderBy('l.no')
				->get();
			$name = Func::chungDec(['cust_info'], $name);
			
			$loanInfoList = [];  // 동명이인 처리
			foreach ($name as $val) {
				if ($val->name === trim($search_string))	$loanInfoList[] = $val->no;
			}

			// 유효성 검사
			if (empty($loanInfoList))	$loanInfoList = 'null';
			else						$loanInfoList = implode(',', $loanInfoList);

            $where .= "loan_info.no in ($loanInfoList)";
        }

        if($where!='')
        {
            $where = '('.$where.')';
        }
        
        $LOAN->WHERERAW($where);
        //$LOAN->ORDERBY("LOAN_INFO.NO","DESC");
        $LOAN->ORDERBY("CUST_INFO.SSN");

        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO"], $rslt);	// CHUNG DATABASE DECRYPT       

        $string = "<table class='table table-sm table-hover card-secondary card-outline mt-1'>";
        $string.= "<tr class='text-center'>";
        $string.= "<td>고객No</td>";
        $string.= "<td>계약No</td>";
        $string.= "<td>이름</td>";
        $string.= "<td>생년월일</td>";
        $string.= "<td>상태</td>";
        $string.= "<td>상환방법</td>";
        $string.= "<td>상환일</td>";
        $string.= "<td>잔액</td>";
        $string.= "<td>청구금액</td>";
        $string.= "<td>완납금액</td>";

        $string.= "<td hidden>상환방법</td>";
        $string.= "<td hidden>월상환액</td>";
        $string.= "<td hidden>상실일</td>";
        $string.= "<td hidden>대출일</td>";
        $string.= "</tr>";

        $array_conf_code   = Func::getConfigArr();
        if( sizeof($rslt)>0 )
        {
            foreach( $rslt as $v )
            {
                $string.= "<tr role='button' onclick='selectLoanInfo(".$v->no.");'>";
                $string.= "<td id='cust_info_no_".$v->no."' class='text-center'>".$v->cust_info_no."</td>";
                $string.= "<td id='loan_info_no_".$v->no."' class='text-center'>".$v->no."</td>";
                $string.= "<td id='cust_name_".$v->no."'    class='text-center'>".$v->name."</td>";
                $string.= "<td id='cust_ssn_".$v->no."'     class='text-center'>".Func::dateFormat(substr($v->ssn,0,6),"-")."</td>";
                $string.= "<td class='text-center'>".Func::getInvStatus($v->status, true)."</td>";
                $string.= "<input type='hidden' id='loan_status_".$v->no."' value='".$v->status."'>";
                $string.= "<input type='hidden' id='loan_rate_".$v->no."' value='".number_format($v->loan_rate,2)."'>";
                $string.= "<input type='hidden' id='loan_delay_rate_".$v->no."' value='".number_format($v->loan_delay_rate,2)."'>";
                $string.= "<td id='return_method_nm_".$v->no."' class='text-center'>".Func::nvl($array_conf_code['return_method_cd'][$v->return_method_cd],$v->return_method_cd)."</td>";
                $string.= "<td id='return_date_".$v->no."'  class='text-center'>".Func::dateFormat($v->return_date)."</td>";
                $string.= "<td id='loan_balance_".$v->no."' class='text-right'>".number_format($v->balance)."</td>";
                $string.= "<td id='loan_money_".$v->no."'   class='text-right'>".number_format($v->charge_money)."</td>";
                $string.= "<td id='over_money_".$v->no."'   class='text-right'>".number_format($v->fullpay_money)."</td>";

                $string.= "<td id='return_method_cd_".$v->no."'     hidden>".$v->return_method_cd."</td>";
                $string.= "<td id='monthly_return_money_".$v->no."' hidden>".number_format($v->monthly_return_money)."</td>";
                $string.= "<td id='kihan_date_".$v->no."'           hidden>".Func::dateFormat($v->kihan_date)."</td>";
                $string.= "<td id='loan_date_".$v->no."'            hidden>".Func::dateFormat($v->loan_date)."</td>";
                $string.= "</tr>";
            }
        }
        else
        {
            $string.= "<tr><td colspan=20 class='text-center p-4'>검색된 고객이 없습니다.</td></tr>";
        }
        $string.= "</table>";

        return $string;
    }
}