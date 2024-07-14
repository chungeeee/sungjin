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

class ContractManagementController extends Controller
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
     * 계약관리명세 조회 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataContractManagementList(Request $request)
    {
        $list   = new DataList(Array("listName"=>"contractManagement","listAction"=>'/'.$request->path()));

        if( Func::funcCheckPermit("C022") )
        {
            //$list->setButtonArray("엑셀다운", "excelDownModal('/account/contractmanagementexcel', 'form_contractManagement')", "btn-success");
        }

        $list->setSearchDate('날짜검색',Array('return_process_date' => '상환처리일', 'deposit_date' => '예치일',),'searchDt','Y');
        $list->setRangeSearchDetail(Array ('return_process_money' => '상환처리금액', 'deposit_money' => '예치금액'),'','','단위(원)');

        $list->setSearchDetail(Array( 
            'cust_info_no' => '차입자번호',
            'loan_info_no'  => '계약번호',
        ));

        return $list;
    }

    /**
     * 계약관리명세 메인
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function contractManagement(Request $request)
    {
        $list   = $this->setDataContractManagementList($request);
        $list->setlistTitleCommon(Array
        (
            'cust_info_no'          => Array('차입자번호', 0, '', 'center', '', 'cust_info_no'),
            'loan_info_no'          => Array('계약번호', 0, '', 'center', '', 'loan_info.no'),
            'return_process_date'   => Array('상환처리일', 0, '', 'center', '', 'return_process_date'),
            'return_process_money'  => Array('상환처리금액', 0, '', 'center', '', 'return_process_money'),
            'deposit_date'          => Array('예치일', 0, '', 'center', '', 'deposit_date'),
            'deposit_money'         => Array('예치금액', 0, '', 'center', '', 'deposit_money'),
            'memo'                  => Array('메모', 0, '', 'center', '', 'memo'),
            'save_id'               => Array('저장자', 0, '', 'center', '', 'save_id'),
            'save_time'             => Array('저장시간', 0, '', 'center', '', 'save_time'),
        ));
        return view('account.contractManagement')->with('result', $list->getList());
    }   
    
    /**
     * 계약관리명세 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function contractManagementList(Request $request)
    {
        $list  = $this->setDataContractManagementList($request);
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
     * 계약관리명세 등록 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function contractManagementForm(Request $request)
    {
        $arrayConfig = Func::getConfigArr();
        $arrayBranch = Func::myPermitBranch();

        return view('account.contractManagementForm')->with("arrayConfig", $arrayConfig)
                                            ->with("arrayBranch", $arrayBranch);
    }

    /**
     * 계약관리명세 등록
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function contractManagementAction(Request $request)
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


    /**
     * 계약관리명세 - 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function contractManagementExcel(Request $request)
    {
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');

        $loan_usr_info_no = $request->loan_usr_info_no;
        $down_filename  = '';
        $excel_down_div = 'E';
        $etc            = '';
        $excelDownCd    = '001';

        $INV = DB::TABLE('loan_info_return_plan')
                ->join("loan_usr_info", function ($join) {
                $join->on('loan_info.loan_usr_info_no', '=', 'loan_usr_info.investor_no')
                    ->on('loan_info.handle_code', '=', 'LOAN_USR_INFO.handle_code');
                })
               ->JOIN('loan_info', 'loan_info_return_plan.LOAN_INFO_NO', '=', 'loan_info.NO')
               ->SELECT('loan_info_return_plan.*', 'loan_info.balance', 'loan_info.loan_money','loan_info.loan_usr_info_no','LOAN_INFO.no as loan_info_no','LOAN_USR_INFO.name')
               ->WHEREIN('loan_info_no', function ($query) use ($loan_usr_info_no) {
                   $query->SELECT('loan_info.no')
                          ->FROM('loan_info')
                          ->WHERE('loan_info.save_status', 'Y')
                          ->WHERE('loan_info.loan_usr_info_no', $loan_usr_info_no);
                   })
               ->WHERE('loan_info.status', '!=', 'N');
        $INV->ORDERBY('loan_info_no','desc');
        $INV->ORDERBY('loan_usr_info_no','asc');
        $INV->ORDERBY('trade_date','asc');
        $INV->ORDERBY('seq','asc');

        $target_sql = urlencode(encrypt(Func::printQuery($INV))); // 페이지 들어가기 전에 쿼리를 저장해야한다.

        $INV = $INV->get();
        $INV = Func::chungDec(["loan_usr_info","loan_info","loan_info_return_plan","cust_info"], $INV);	// CHUNG DATABASE DECRYPT

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "투자내역_전체스케줄_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no))
        {
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
        } 
        else 
        {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$excelDownCd,$file_name, $target_sql, $record_count,$etc,null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
        }
        
        $arrManager = Func::getUserList();

        // 엑셀 헤더
		$excel_header   = array('계약번호','투자자번호','투자자명','회차','상환일','상환일(영업)','이자구간','투자원금', '투자잔액', '이자', '원천징수','이자소득세','주민세','실수령이자');
        $excel_data     = [];

        foreach ($INV as $v)
        {
            $array_data = [
                number_format($v->loan_info_no),
                number_format($v->loan_usr_info_no),
                $v->name,
                number_format($v->seq),
                Func::dateFormat($v->plan_date)." (".Vars::$arrayWeekDay[date('w',Func::dateToUnixtime($v->plan_date))].")",
                Func::dateFormat($v->plan_date_biz),
                Func::dateFormat($v->plan_interest_sdate)." ~ ".Func::dateFormat($v->plan_interest_edate),
                number_format($v->trade_money),
                number_format($v->plan_money),
                number_format($v->plan_interest),
                number_format($v->withholding_tax),
                number_format($v->income_tax),
                number_format($v->local_tax),
                number_format($v->plan_interest-$v->withholding_tax)

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

            if(Storage::disk('excel')->exists('/'.$file_name)) 
            {
                return Storage::disk('excel')->download('/'.$file_name, $file_name);
            }
            else 
            {
                $array_result['result']    = 'N';
                $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
            }
        }
        else
        {
           $array_result['result']    = 'N';
           $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }
    } 
}