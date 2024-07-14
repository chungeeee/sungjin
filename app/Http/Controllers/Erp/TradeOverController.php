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
use Illuminate\Support\Facades\Storage;
use ExcelFunc;

class TradeOverController extends Controller
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
     * 가지급금 명세 공통 세팅 내용
     *
     * @param  request
     * @return dataList
     */
    private function setDataListOver(Request $request)
    {

        $list = new DataList(Array("listName"=>"tradeover","listAction"=>'/'.$request->path()));

        if(!isset($request->tabs)) $request->tabs = 'W';

        $list->setTabs(Array('W'=>'미처리', 'C'=>'처리완료'), $request->tabs);

        if( Func::funcCheckPermit("E001") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/erp/tradeoverexcel', 'form_tradeover')", "btn-success");
        }
        $list->setSearchType('loan_info-handle_code',Func::getConfigArr('mo_acct_div'),'법인 구분', '', '', '', '', 'Y', '', true);

        $list->setSearchDetail(Array(
            'cust_bank_name'          => '차입자명',
            'loan_usr_info.nick_name' => '투자자명',
            'investor_no'             => '투자자번호',
            'investor_no-inv_seq'     => '채권번호',
        ));

        $list->setCheckBox("no");

        return $list;
    }

    /**
      * 가수금명세
      *
      * @param  \Illuminate\Http\Request  $request
      * @return view
      */
    public function tradeOver(Request $request)
    {
        $list   = $this->setDataListOver($request);

        $list->setlistTitleCommon(Array
        (
            'investor_no_inv_seq'      => Array('채권번호', 0, '', 'center', '', ''),
            'cust_bank_name'           => Array('차입자명', 0, '', 'center', '', 'cust_bank_name'),
            'loan_usr_info_name'       => Array('투자자명', 0, '', 'center', '', ''),
            'trade_date'               => Array('가지급금발생일', 0, '', 'center', '', ''),
            'over_money'               => Array('가지급금', 0, '', 'center', '', 'over_money'),
        ));

        $list->setlistTitleTabs('W',Array
        (
            'e_status'                 => Array('회수처리', 0, '', 'center', '', ''),
            's_status'                 => Array('손실처리', 0, '', 'center', '', ''),
        ));
        
        $list->setlistTitleTabs('C',Array
        (
            'reg_time'                 => Array('처리일시', 0, '', 'center', '', 'reg_time'),
        ));

        return view('erp.tradeOver')->with('result', $list->getList());
    }
  
    /**
     * 가수금명세 데이터리스트
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function tradeOverList(Request $request)
    {
        $list   = $this->setDataListOver($request);
        $param  = $request->all();

        // 메인쿼리
        $tradeOver = DB::TABLE("loan_info")->JOIN('loan_usr_info', 'loan_usr_info.no', '=', 'loan_info.loan_usr_info_no')
                        ->SELECT("loan_info.cust_info_no",
                                    DB::raw("(select trade_date from loan_info_trade where loan_info_no = loan_info.no and trade_div = 'I' and over_money > 0 order by no desc limit 1) as trade_date"),
                                    "loan_info.cust_bank_name",
                                    "loan_usr_info.name as loan_usr_info_name",
                                    "loan_info.no as loan_info_no",
                                    "loan_info.loan_usr_info_no",
                                    "loan_info.inv_seq",
                                    "loan_info.investor_no",
                                    "loan_info.investor_type"
                                )
                        ->WHERE('loan_info.status', 'E')
                        ->WHERE('loan_usr_info.save_status', 'Y')
                        ->WHERE('loan_info.save_status', 'Y');

		// 탭 - 미처리
        if( $request->tabsSelect=="W" )
		{
            $tradeOver->WHERE('loan_info.over_money','>', 0)
                    ->ADDSELECT("loan_info.over_money");
            unset($param['tabSelectNm'], $param['tabsSelect']);
        }
        // 탭 - 처리완료
		else if( $request->tabsSelect=="C" )
		{
            $tradeOver->JOIN('loan_info_trade', 'loan_info_trade.loan_info_no', '=', 'loan_info.no')
                    ->ADDSELECT("loan_info_trade.trade_money as over_money", "loan_info_trade.reg_time")
                    ->WHERE('loan_info_trade.trade_div', 'O')
                    ->whereIn('loan_info_trade.trade_type', ['91','99'])
                    ->ORDERBY("loan_info_trade.reg_time", "desc");
            unset($param['tabSelectNm'], $param['tabsSelect']);
        }

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
                            $tradeOver = $tradeOver->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $tradeOver = $tradeOver->WHERE('loan_usr_info.investor_no',$searchString[0])
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
                        $tradeOver = $tradeOver->WHERE('loan_usr_info.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $tradeOver = $tradeOver->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
                    }
                }
            }

            unset($param['searchString']);
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='investor_no' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='loan_usr_info.nick_name' && !empty($param['searchString']) )
        {
            $tradeOver = $tradeOver->where('loan_usr_info.nick_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $tradeOver = $list->getListQuery("loan_info",'main', $tradeOver, $param);

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($tradeOver, $request->page, $request->listLimit, 10);

        // 결과
        $result = $tradeOver->get();
        $result = Func::chungDec(["loan_info"], $result);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $cnt = 0;
        $nos = 0;
        $total = 0;

        $arrManager = Func::getUserList();

        foreach( $result as $v )
        {
            $v->investor_no_inv_seq = $v->investor_type.$v->investor_no.'-'.$v->inv_seq;
            
            $v->e_status      = '<button type="button" class="btn btn-primary btn-xs m-0 mr-1 float-center" onclick="overMoney(\''.$v->loan_info_no.'\', \'R\');">회수</button>';
            $v->s_status      = '<button type="button" class="btn btn-outline-dark btn-xs m-0 mr-1 float-center" onclick="overMoney(\''.$v->loan_info_no.'\', \'S\');">손실</button>';

            $v->trade_date    = Func::dateFormat($v->trade_date);
            $v->over_money    = number_format($v->over_money);
            
            $v->loan_usr_info_name = Func::decrypt($v->loan_usr_info_name, 'ENC_KEY_SOL');

            $v->reg_time      = Func::dateFormat($v->reg_time ?? '') ;
            
            $r['v'][]         = $v;

            $cnt ++;
        }

         // 페이징
         $r['pageList'] = $paging->getPagingHtml($request->path());
         $r['result'] = 1;
         $r['txt'] = $cnt;

        return json_encode($r);
    }

    public function tradeOverExcel(Request $request)
    {
        if( !Func::funcCheckPermit("E001") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }
        
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $list           = $this->setDataListOver($request);
        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;
        
        // 메인쿼리
        $tradeOver = DB::TABLE("loan_info")->JOIN('loan_usr_info', 'loan_usr_info.no', '=', 'loan_info.loan_usr_info_no')
                        ->SELECT("loan_info.cust_info_no",
                                    DB::raw("(select trade_date from loan_info_trade where loan_info_no = loan_info.no and trade_div = 'I' and over_money > 0 order by no desc limit 1) as trade_date"),
                                    "loan_info.cust_bank_name",
                                    "loan_usr_info.name as loan_usr_info_name",
                                    "loan_info.no as loan_info_no", 
                                    "loan_info.status as e_status",
                                    "loan_info.loan_usr_info_no", 
                                    "loan_info.inv_seq",
                                    "loan_info.investor_no",
                                    "loan_info.investor_type"
                                )
                        ->WHERE('loan_info.status', 'E')
                        ->WHERE('loan_usr_info.save_status', 'Y');

		// 탭 - 미처리
        if( $request->tabsSelect=="W" )
		{
            $tradeOver->WHERE('loan_info.over_money','>', 0)
                    ->ADDSELECT("loan_info.over_money");
            unset($param['tabSelectNm'], $param['tabsSelect']);
        }
        // 탭 - 처리완료
		else if( $request->tabsSelect=="C" )
		{
            $tradeOver->join('loan_info_trade', 'loan_info_trade.loan_info_no', '=', 'loan_info.no')
                    ->ADDSELECT("loan_info_trade.trade_money as over_money", "loan_info_trade.reg_time")
                    ->where('loan_info_trade.trade_div', 'O')
                    ->whereIn('loan_info_trade.trade_type', ['91','99'])
                    ->orderBy("loan_info_trade.reg_time", "desc");
            unset($param['tabSelectNm'], $param['tabsSelect']);
        }

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
                            $tradeOver = $tradeOver->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $tradeOver = $tradeOver->WHERE('loan_usr_info.investor_no',$searchString[0])
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
                        $tradeOver = $tradeOver->WHERE('loan_usr_info.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $tradeOver = $tradeOver->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
                    }
                }
            }
            
            unset($param['searchString']);
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='investor_no' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='loan_usr_info.nick_name' && !empty($param['searchString']) )
        {
            $tradeOver = $tradeOver->where('loan_usr_info.nick_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $tradeOver = $list->getListQuery("loan_info",'main', $tradeOver, $param);

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($tradeOver, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($tradeOver);
        
        $file_name    = "가지급금 명세_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $request_all['work_id'] = Auth::id();
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no)){
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
            $origin_filename = $excel_no.'_'.$request->work_id.'_'.date("YmdHis").'.xlsx'; 
        } else {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $query, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
            $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
        }

        $rslt = $tradeOver->GET();
        $rslt = Func::chungDec(["LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        // 엑셀
		$excel_header = array('NO','채권번호','차입자명','투자자명','가지급금발생일','가지급금');
        if($request->tabsSelect=="C")
        {
            array_push($excel_header, '처리일시');
        }

        $excel_data = [];
        $board_count=1;

        foreach ($rslt as $v)
        {
            $array_data = [
                $board_count,
                $v->investor_type.$v->investor_no.'-'.$v->inv_seq,
                $v->cust_bank_name,
                Func::decrypt($v->loan_usr_info_name, 'ENC_KEY_SOL'),
                Func::dateFormat($v->trade_date),
                number_format($v->over_money),
            ];
            if($request->tabsSelect=="C")
            {
                array_push($array_data, Func::dateFormat($v->reg_time));
            }
            $record_count++;
            $board_count++;
            $excel_data[] = $array_data;
        }
        
        // 엑셀 익스포트
        ExcelFunc::fastexcelExport($excel_data, $excel_header, $origin_filename);
    
        // 파일 저장 여부 확인
        $exists = Storage::disk('excel')->exists($origin_filename);

        if( $exists )
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
    
    /**
    * 가지급금 정리
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function tradeOverAction(Request $request)
    {
        $_DATA = $request->all();

        $_IN = array();

        if($_DATA['div'] == 'R')
        {
            $_IN['trade_type']       = '91';
        }
        else if($_DATA['div'] == 'S')
        {
            $_IN['trade_type']       = '99';
        }
        else
        {
            return '구분이 없습니다. 관리자에게 문의하시기 바랍니다.';
        }

        $tradeOver = DB::TABLE("loan_info")
                        ->SELECT("loan_info.cust_info_no",
                                    DB::raw("(select trade_date from loan_info_trade where loan_info_no = loan_info.no and trade_div = 'I' and over_money > 0 order by no desc limit 1) as trade_date"),
                                    "loan_info.loan_usr_info_no", 
                                    "loan_info.investor_no",
                                    "loan_info.inv_seq",
                                    "loan_info.over_money"
                                )
                        ->WHERE('loan_info.no', $_DATA['loan_info_no'])
                        ->WHERE('loan_info.status', 'E')
                        ->WHERE('loan_info.save_status', 'Y')
                        ->WHERE('loan_info.over_money','>', 0)
                        ->first();

        // 거래내역 등록
        $_IN['trade_date']       = $tradeOver->trade_date;
        $_IN['trade_money']      = $tradeOver->over_money;
        $_IN['investor_no']      = $tradeOver->investor_no;
        $_IN['loan_usr_info_no'] = $tradeOver->loan_usr_info_no;
        $_IN['cust_info_no']     = $tradeOver->cust_info_no;
        $_IN['loan_info_no']     = $_DATA['loan_info_no'];
        $_IN['trade_fee']        = 0;
        $_IN['memo']             = "가지급금 정리";

        $t = new Trade($_DATA['loan_info_no']);
        $loan_info_trade_no = $t->tradeOutInsert($_IN, Auth::id());

        // 오류 업데이트 후 쪽지 발송
        if(!is_numeric($loan_info_trade_no))
        {
            DB::rollBack();

            Log::debug($_DATA['loan_info_no']);
            return '가지급금 정리시 에러가 발생했습니다.('.$_DATA['loan_info_no'].')';
        }

        return 'Y';
    }
}