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
use Invest;

class AdvanceDepositController extends Controller
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
     * 상품선입금내역 조회 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataAdvanceDepositList(Request $request)
    {
        $list   = new DataList(Array("listName"=>"advancedeposit","listAction"=>'/'.$request->path()));

        if( Func::funcCheckPermit("R022") )
        {
            //$list->setButtonArray("엑셀다운", "excelDownModal('/account/advancedepositexcel', 'form_advancedeposit')", "btn-success");
        }

        $list->setSearchDate('날짜검색',Array('return_process_date' => '상환처리일', 'deposit_date' => '예치일',),'searchDt','Y');
        $list->setRangeSearchDetail(Array ('return_process_money' => '상환처리금액', 'deposit_money' => '예치금액'),'','','단위(원)');

        $list->setSearchDetail(Array( 
            'cust_info_no' => '차입자번호',
            'loan_info_no'  => '계약번호',
            'loan_info_no'  => '투자번호',
        ));

        $list->setPlusButton("advanceDepositForm('');");

        return $list;
    }

    /**
     * 상품선입금내역 메인
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function advanceDeposit(Request $request)
    {
        $list   = $this->setDataAdvanceDepositList($request);
        $list->setlistTitleCommon(Array
        (
            'cust_info_no'          => Array('차입자번호', 0, '', 'center', '', 'cust_info_no'),
            'loan_info_no'          => Array('계약번호', 0, '', 'center', '', 'loan_info.no'),
            'loan_info_no'                => Array('투자번호', 0, '', 'center', '', 'loan_info_no'),
            'return_process_date'   => Array('상환처리일', 0, '', 'center', '', 'return_process_date'),
            'return_process_money'  => Array('상환처리금액', 0, '', 'center', '', 'return_process_money'),
            'deposit_date'          => Array('예치일', 0, '', 'center', '', 'deposit_date'),
            'deposit_money'         => Array('예치금액', 0, '', 'center', '', 'deposit_money'),
            'memo'                  => Array('메모', 0, '', 'center', '', 'memo'),
            'save_id'               => Array('저장자', 0, '', 'center', '', 'save_id'),
            'save_time'             => Array('저장시간', 0, '', 'center', '', 'save_time'),
        ));
        return view('account.advancedeposit')->with('result', $list->getList());
    }   
    
    /**
     * 상품선입금내역 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function advanceDepositList(Request $request)
    {
        $list  = $this->setDataAdvanceDepositList($request);
        $param  = $request->all();

        // 기본쿼리
        $DEPOSIT = DB::TABLE("advance_deposit")->SELECT("*");
        $DEPOSIT->WHERE('advance_deposit.save_status','Y');

        $LOAN = $list->getListQuery("advance_deposit",'main',$DEPOSIT,$param);
                
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($DEPOSIT, $request->page, $request->listLimit, 10, $request->listName);

        $rslt = $DEPOSIT->GET();
        $rslt = Func::chungDec(["advance_deposit"], $rslt);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $getReturnMethodCd = Func::getConfigArr('return_method_cd');
        $arrBranch         = Func::getBranch();

        $cnt = 0;
        foreach ($rslt as $v)
        {
            $v->return_process_date     = Func::dateFormat($v->return_process_date);
            $v->return_process_money    = number_format($v->return_process_money);
            $v->deposit_date            = Func::dateFormat($v->deposit_date);
            $v->deposit_money           = number_format($v->deposit_money);
            $v->save_id                 = Func::nvl(Func::getUserId($v->save_id)->name, $v->save_id);
            $v->save_time               = Func::dateFormat($v->save_time);

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
     * 계약 등록 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function advanceDepositForm(Request $request)
    {
        $arrayConfig = Func::getConfigArr();
        $arrayBranch = Func::myPermitBranch();

        return view('account.advanceDepositForm')->with("arrayConfig", $arrayConfig)
                                            ->with("arrayBranch", $arrayBranch);
    }

    /**
     * 계약등록 입력폼에서 찾기를 할 때, 결과 테이블 HTML 응답한다.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function advanceDepositSearch(Request $request)
    {
        if( !isset($request->search_string) )
        {
            return "검색어를 입력하세요.";
        }
        $loan_info_no_auto = $request->loan_info_no_auto;
        $search_string     = $request->search_string;

        // 기본쿼리
        $LOAN = DB::table("cust_info")->join("cust_info_extra", "cust_info.no", "=", "cust_info_extra.cust_info_no")
                                        ->leftJoin("loan_info", [["cust_info.no", "=", "loan_info.cust_info_no"], ["loan_info.save_status","=","'Y'"]])
                                        ->select("loan_info.*", "cust_info.no as cno", "cust_info.name", "cust_info.ssn", "cust_info_extra.bank_cd", "cust_info_extra.bank_ssn", "cust_info_extra.bank_cd2", "cust_info_extra.bank_ssn2", "cust_info_extra.bank_cd3", "cust_info_extra.bank_ssn3")
                                        ->where('cust_info.save_status','Y');

        // 검색
        $where = "";
        if( is_numeric($search_string) )
        {
            $where.= "CUST_INFO.NO=".$search_string." ";
            // 6자리 이상인 경우만 검색
            if( strlen($search_string)>=6 )
            {
                $where.= 'or '.Func::encLikeSearchString('cust_info.ssn', $search_string, 'after');
            }
        }
        else
        {
            $where.= Func::encLikeSearchString('cust_info.name', $search_string, 'after');
        }

        if($where!='')
        {
            $where = '('.$where.')';
        }

        $LOAN->whereRaw($where);
        $LOAN->orderBy("cust_info.ssn","ASC");

        $rslt = $LOAN->get();
        $rslt = Func::chungDec(["loan_info","cust_info","cust_info_extra"], $rslt);	// CHUNG DATABASE DECRYPT

        $arrayBankCd = Func::getConfigArr('bank_cd');
        
        $string = "<table class='table table-sm table-hover card-secondary card-outline mt-1'>";
        $string.= "<tr class='text-center'>";
        $string.= "<td>고객No</td>";
        $string.= "<td>계약No</td>";
        $string.= "<td>이름</td>";
        $string.= "<td>생년월일</td>";
        $string.= "<td>대출일</td>";
        $string.= "<td>상태</td>";
        $string.= "<td>대출액</td>";
        $string.= "<td>잔액</td>";

        $string.= "<td hidden>계약구분코드</td>";
        $string.= "<td hidden>상태코드</td>";
        $string.= "<td hidden>은행</td>";
        $string.= "</tr>";

        foreach( $rslt as $v )
        {
            $arrayBank = [];
            if(!empty($v->bank_ssn)) $arrayBank[$v->bank_cd."||".$v->bank_ssn] = "(".Func::getArrayName($arrayBankCd, $v->bank_cd).") ".$v->bank_ssn;
            if(!empty($v->bank_ssn2)) $arrayBank[$v->bank_cd2."||".$v->bank_ssn2] = "(".Func::getArrayName($arrayBankCd, $v->bank_cd2).") ".$v->bank_ssn2;
            if(!empty($v->bank_ssn3)) $arrayBank[$v->bank_cd3."||".$v->bank_ssn3] = "(".Func::getArrayName($arrayBankCd, $v->bank_cd3).") ".$v->bank_ssn3;

            $string.= "<tr role='button' onclick='selectLoanInfo(".$v->cno.");'>";
            $string.= "<td id='cust_info_no_".$v->cno."' class='text-center'>".$v->cno."</td>";
            $string.= "<td id='loan_usr_info_no".$v->cno."'   class='text-center' hidden>".$v->loan_usr_info_no."</td>";
            $string.= "<td id='loan_info_no_".$v->cno."' class='text-center'>".$v->no."</td>";
            $string.= "<td id='cust_name_".$v->cno."'    class='text-center'>".$v->name."</td>";
            $string.= "<td id='cust_ssn_".$v->cno."'     class='text-center'>".Func::dateFormat(substr($v->ssn,0,6),"-")."</td>";
            $string.= "<td id='loan_date_".$v->cno."'    class='text-center'>".Func::dateFormat($v->loan_date)."</td>";
            $string.= "<td class='text-center'>".Func::getInvStatus($v->status, true)."</td>";
            $string.= "<td id='loan_money_".$v->cno."'   class='text-right'>".number_format($v->loan_money)."</td>";
            $string.= "<td id='loan_balance_".$v->cno."' class='text-right'>".number_format($v->balance)."</td>";

            $string.= "<td id='loan_type_".$v->cno."'    class='text-center' hidden>".$v->loan_type."</td>";
            $string.= "<td id='loan_status_".$v->cno."'  class='text-center' hidden>".$v->status."</td>";
            $string.= "<td id='bank_info_".$v->cno."'    class='text-center' hidden>".json_encode($arrayBank, JSON_UNESCAPED_UNICODE)."</td>";
            $string.= "</tr>";
        }
        $string.= "</table>";

        return $string;
    }

    /**
     * 계약 등록
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function advanceDepositAction(Request $request)
    {
        $v = $request->input();

        if( !isset($v['loan_info_no']))
        {
            return "파라미터 오류";
        }
       
        // 회원정보
        $loan = DB::table("loan_info")->select("*")->where("no", $v['loan_info_no'])->where("save_status", "Y")->first();
        $loan = Func::chungDec(["loan_info"], $loan);	// CHUNG DATABASE DECRYPT

        if( !$loan )
        {
            return "계약정보를 찾을 수 없습니다.";
        }

        // 계약등록
        $_DEPOSIT = $v;
        $_DEPOSIT['return_process_date']    = str_replace("-", "", $v['return_process_date']);
        $_DEPOSIT['deposit_date']           = str_replace("-", "", $v['deposit_date']);
        $_DEPOSIT['save_status']            = 'Y';
        $_DEPOSIT['save_id']                = Auth::id();
        $_DEPOSIT['save_time']              = date('YmdHis');

        DB::beginTransaction();

        Log::debug($_DEPOSIT);

        $rslt = DB::dataProcess('INS', 'advance_deposit', $_DEPOSIT);

        if( $rslt!="Y" )
        {
            DB::rollBack();
            return '선입금등록시 에러가 발생했습니다.';
        }

        DB::commit();
		return "Y";     
    }

}