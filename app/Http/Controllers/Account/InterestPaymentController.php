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

class InterestPaymentController extends Controller
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
        $list   = new DataList(Array("listName"=>"interestPayment","listAction"=>'/'.$request->path()));
        
        if(!isset($request->tabs)) $request->tabs = 'Y';
        $list->setTabs(Array('Y'=>'정상', 'N'=>'삭제'),$request->tabs);

        $list->setCheckBox("no");
        
        $list->setButtonArray("엑셀다운","excelDownModal('/account/interestpaymentexcel','form_interestPayment')","btn-success");
        $list->setSearchType('loan_info-handle_code',Func::getConfigArr('mo_acct_div'),'법인 구분', '', '', '', '', 'Y', '', true);
        
        //검색항목 
        $list->setSearchDate('일자',Array('loan_info.save_time'=>'등록일자','loan_info.del_time'=>'삭제일자'),'searchDt','Y');  //일자
        
        $list->setSearchDetail(Array( 
            'loan_usr_info.nick_name'  => '투자자명',
            'loan_info.investor_no'    => '투자자번호',
            'investor_no-inv_seq'      => '채권번호',
        ));
        return $list;
    }

    /**
     * 이자지급내역 메인화면
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function interestPayment(Request $request)
    {
        $list = $this->setDataList($request);

        $list->setLumpForm('DEL', Array('BTN_NAME'=>'삭제처리','BTN_ACTION'=>'lump_del(this);','BTN_ICON'=>'','BTN_COLOR'=>''));

        $list->setlistTitleCommon(Array
        (
            'investor_no_inv_seq'      => Array('채권번호', 0, '', 'center', '', ''),
            
            'cust_bank_name'           => Array('차입자명', 1, '', 'center', '', 'cust_bank_name'),
            'cust_bank_cd'             => Array('차입자은행', 0, '', 'center', '', ''),
            'cust_bank_ssn'            => Array('차입자계좌번호', 0, '', 'center', '', 'cust_bank_ssn'),

            'loan_usr_info_name'       => Array('투자자명', 1, '', 'center', '', ''),

            'loan_bank_cd'             => Array('투자자은행', 0, '', 'center', '', ''),
            'loan_bank_ssn'            => Array('투자자계좌번호', 0, '', 'center', '', 'loan_bank_ssn'),
            'loan_bank_name'           => Array('투자자예금주명', 1, '', 'center', '', 'loan_bank_name'),

            'trade_date'               => Array('수익지급일', 0, '', 'center', '', 'trade_date'),
            'return_interest'          => Array('수익지급금액', 0, '', 'center', '', 'return_interest'),
            
            'save_id'                  => Array('저장자', 0, '', 'center', '', 'save_id', ['save_time'=>['저장시간', 'save_time', '<br>']]),
        ));
        
        $list->setlistTitleTabs('N',Array
        (
            'del_id'                   => Array('삭제자', 0, '', 'center', '', 'del_id'),
            'del_time'                 => Array('삭제시간', 0, '', 'center', '', 'del_time'),
        ));
        return view('account.interestPayment')->with("result", $list->getList());
    }

    /**
     * 이자지급내역 리스트
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function interestPaymentList(Request $request)
    {
        $list   = $this->setDataList($request);
        $param = $request->all();
        
        // Tab count 
		if($request->isFirst=='1')
		{
            $BOXC = DB::table("loan_info_trade")->join("loan_info", "loan_info.no", "=", "loan_info_trade.loan_info_no")
                                                ->join("cust_info", "cust_info.no", "=", "loan_info_trade.cust_info_no")
                                                ->join("loan_usr_info", "loan_usr_info.no", "=", "loan_info_trade.loan_usr_info_no");
            $BOXC->SELECT(DB::RAW("coalesce(sum(case when loan_info_trade.return_interest > 0 and loan_info_trade.save_status='Y' and loan_usr_info.save_status='Y' and cust_info.save_status='Y' and loan_info.save_status='Y' then 1 else 0 end),0) as Y
                                , coalesce(sum(case when loan_info_trade.return_interest > 0 and loan_info_trade.save_status='N' and loan_usr_info.save_status='Y' and cust_info.save_status='Y' and loan_info.save_status='Y' then 1 else 0 end),0) as N"));            
            $vcnt = $BOXC->FIRST();
            
			$r['tabCount'] = array_change_key_case((Array) $vcnt, CASE_UPPER);
		}

        // 기본쿼리
        $IPL = DB::table("loan_info_trade")->select("loan_info_trade.*", "loan_info.inv_seq", "loan_info.investor_no", "loan_info.investor_type", "loan_usr_info.name")
                                            ->join("loan_info", "loan_info.no", "=", "loan_info_trade.loan_info_no")
                                            ->join("cust_info", "cust_info.no", "=", "loan_info_trade.cust_info_no")
                                            ->join("loan_usr_info", "loan_usr_info.no", "=", "loan_info_trade.loan_usr_info_no")
                                            ->where('loan_info_trade.return_interest', '>','0');
                                                                    
        if( $request->tabsSelect=="Y" )
		{
            $param['tabSelectNm'] = "loan_info_trade.save_status";
        }
        else if( $request->tabsSelect=="N" )
		{
            $param['tabSelectNm'] = "loan_info_trade.save_status";
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
                            $IPL = $IPL->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $IPL = $IPL->WHERE('loan_usr_info.investor_no',$searchString[0])
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
                        $IPL = $IPL->WHERE('loan_usr_info.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $IPL = $IPL->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
                    }
                }
            }

            unset($param['searchString']);
        }  
        
        if(isset( $param['searchDetail']) && $param['searchDetail']=='loan_info.investor_no' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='loan_usr_info.nick_name' && !empty($param['searchString']) )
        {
            $IPL = $IPL->where('loan_usr_info.nick_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        if(isset($param['listOrder']) && $param['listOrder'] == 'loan_bank_cd') {
            if(isset($param['listOrderAsc'])  && $param['listOrderAsc']=='desc') {
                $IPL = $IPL->orderBy('loan_info.loan_bank_cd', 'desc');
            }
            else if(isset($param['listOrderAsc'])  && $param['listOrderAsc']=='asc') {
                $IPL = $IPL->orderBy('loan_info.loan_bank_cd', 'asc');
            }
            unset($param['listOrder']);
            unset($param['listOrderAsc']);
        }

        $IPL = $list->getListQuery('loan_info_trade', 'main', $IPL, $param);
        $IPL->orderBy("loan_info_trade.save_time", "desc");
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($IPL, $request->page, $request->listLimit, 10, $request->listName);

        $rslt   = $IPL->GET();
        $rslt   = Func::chungDec(["loan_info_trade", "loan_usr_info"], $rslt);	// CHUNG DATABASE DECRYPT
        
        // 뷰단 데이터 정리.
        $cnt       = 0;
        $getUserId = Func::getUserId(); // 작업자 이름 리스트
        $bank_cd   = Func::getConfigArr('bank_cd');
		
        foreach ($rslt as $v)
		{
            $v->onclick                  = 'popUpFull(\'/account/investmentpop?no='.$v->loan_info_no.'\', \'investment'.$v->loan_info_no.'\')';
            $v->line_style               = 'cursor: pointer;';
            
            $v->loan_usr_info_name       = $v->name ?? '';

            $v->investor_no_inv_seq      = $v->investor_type.$v->investor_no.'-'.$v->inv_seq;

            $v->trade_date               = (isset($v->trade_date) ? Func::dateFormat($v->trade_date) : '');

            $v->cust_bank_cd             = $v->cust_bank_cd ? $bank_cd[$v->cust_bank_cd] : '';                         // 차입자 은행명
            $v->loan_bank_cd             = $v->loan_bank_cd ? $bank_cd[$v->loan_bank_cd] : '';                         // 투자자 은행명
            $v->return_interest          = Func::numberFormat($v->return_interest);                                    // 금액
            $v->save_id                  = (isset($v->save_id) ? Func::getArrayName($getUserId, $v->save_id) : '');
            $v->save_time                = (isset($v->save_time) ? Func::dateFormat($v->save_time) : '');
            $v->del_id                   = (isset($v->del_id) ? Func::getArrayName($getUserId, $v->del_id) : '');
            $v->del_time                 = (isset($v->del_time) ? Func::dateFormat($v->del_time) : '');

            $r['v'][]                    = $v;
            $cnt ++;
        }
        
        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path());
		$r['result'] = 1;
		$r['txt'] = $cnt;
        $r['totalCnt']  = $paging->getTotalCnt();

		return json_encode($r);
    }

    /**
     * 엑셀다운로드 (투자자명세)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function interestPaymentExcel(Request $request)
    {
        if( !Func::funcCheckPermit("U002") && !isset($request->excel_flag) )
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
        $IPL = DB::table("loan_info_trade")->select("loan_info_trade.*", "loan_info.inv_seq", "loan_info.investor_no", "loan_info.investor_type", "loan_usr_info.name")
                    ->join("loan_info", "loan_info.no", "=", "loan_info_trade.loan_info_no")
                    ->join("cust_info", "cust_info.no", "=", "loan_info_trade.cust_info_no")
                    ->join("loan_usr_info", "loan_usr_info.no", "=", "loan_info_trade.loan_usr_info_no")
                    ->where('loan_info_trade.return_interest', '>','0');

        if( $request->tabsSelect=="Y" )
		{
            $param['tabSelectNm'] = "loan_info_trade.save_status";
            $param['tabsSelect']  = Array('Y');
        }
        else if( $request->tabsSelect=="N" )
		{
            $param['tabSelectNm'] = "loan_info_trade.save_status";
            $param['tabsSelect']  = Array('N');
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
                            $IPL = $IPL->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $IPL = $IPL->WHERE('loan_usr_info.investor_no',$searchString[0])
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
                        $IPL = $IPL->WHERE('loan_usr_info.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $IPL = $IPL->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
                    }
                }
            }
            
            unset($param['searchString']);
        }  
        
        if(isset( $param['searchDetail']) && $param['searchDetail']=='loan_info.investor_no' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='loan_usr_info.nick_name' && !empty($param['searchString']) )
        {
            $IPL = $IPL->where('loan_usr_info.nick_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        if(isset($param['listOrder']) && $param['listOrder'] == 'loan_bank_cd') {
            if(isset($param['listOrderAsc'])  && $param['listOrderAsc']=='desc') {
                $IPL = $IPL->orderBy('loan_info.loan_bank_cd', 'desc');
            }
            else if(isset($param['listOrderAsc'])  && $param['listOrderAsc']=='asc') {
                $IPL = $IPL->orderBy('loan_info.loan_bank_cd', 'asc');
            }
            unset($param['listOrder']);
            unset($param['listOrderAsc']);
        }
        

        $IPL = $list->getListQuery('loan_info_trade', 'main', $IPL, $param);
        $IPL->orderBy("loan_info_trade.save_time", "desc");

        $target_sql = urlencode(encrypt(Func::printQuery($IPL))); // 페이지 들어가기 전에 쿼리를 저장해야한다.                
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($IPL, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "이자지급리스트_".date("YmdHis").'_'.Auth::id().'.xlsx';
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
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $target_sql, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
            $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
        }

        $rslt = $IPL->GET();
        $rslt = Func::chungDec(["loan_info_trade", "loan_usr_info"], $rslt);	// CHUNG DATABASE DECRYPT
        
        // 엑셀 헤더
		$excel_header   = array('No','채권번호','차입자명','차입자은행','차입자계좌번호','투자자명','투자자은행','투자자계좌번호','투자자예금주명','수익지급일','수익지급금액','저장자', '저장시간');
        if( $request->tabsSelect=="N" )
		{
            array_push($excel_header,'삭제자', '삭제시간');
        }
        $excel_data     = [];

        $array_config   = Func::getConfigArr();
        $getStatus      = Vars::$arrayContractSta;
        $arrBranch      = Func::getBranch();
        $bank_cd   = Func::getConfigArr('bank_cd');
        $getUserId = Func::getUserId(); //작업자 이름 리스트 
        $board_count = 1;

        foreach ($rslt as $v)
        {
            $array_data = [
                $board_count,      //No
                $v->investor_no.'-'.$v->inv_seq,   //채권번호
                $v->cust_bank_name,  //차입자명
                $v->cust_bank_cd ? $bank_cd[$v->cust_bank_cd] : '',   //차입자 은행
                $v->cust_bank_ssn,  //차입자 계좌번호    
                $v->name, //투자자명                            
                $v->loan_bank_cd ? $bank_cd[$v->loan_bank_cd] : '',   //투자자 은행
                $v->loan_bank_ssn,  //투자자계좌번호   
                $v->loan_bank_name, //투자자예금주명
                Func::dateFormat($v->trade_date),     //수익지급일  
                Func::numberFormat($v->return_interest),  //수익지급금액     
                (isset($v->save_id) ? Func::getArrayName($getUserId, $v->save_id) : ''),        //저장자
                Func::dateFormat($v->save_time),    //저장시간
            ];
            if ($request->tabsSelect == "N")
            {
                $array_data[] = isset($v->del_id) ? Func::getArrayName($getUserId, $v->del_id) : '';
                $array_data[] = Func::dateFormat($v->del_time);
            }
            $record_count++;
            $board_count++;
            $excel_data[] = $array_data;
        }
        
        // 엑셀 익스포트
        ExcelFunc::fastexcelExport($excel_data, $excel_header, $origin_filename);
    
        // 파일 저장 여부 확인
        $exists = Storage::disk('excel')->exists($origin_filename);
        
        if( isset($exists) )
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
     * 이자지급내역 삭제 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function interestPaymentDelete(Request $request)
    {
        $s_cnt = 0;
        $arr_fail = Array();

        // 처리할 내역 배열로 변환
        $arrayListChk = (array) $request->listChk;
        rsort($arrayListChk);

        if( $request->action_mode=="interest_payment_DELETE" && is_array($arrayListChk) && sizeof($arrayListChk)>0 )
        {
            for($i=0; $i<sizeof($arrayListChk); $i++)
            {
                $trade_no = $arrayListChk[$i];

                $loan_info_no = DB::table('loan_info_trade')->where("no",$trade_no)->value('loan_info_no');
                            
                DB::beginTransaction();

                // 수익지급정보 SELECT
                $rslt = DB::Table("loan_info_trade")->select("*")->where("no", $trade_no)->where("save_status", "Y")->where("trade_div", "I")->first();
                $rslt = Func::chungDec(["LOAN_INFO_TRADE"], $rslt);	// CHUNG DATABASE DECRYPT
                $vt = (Array) $rslt;
                if( !$vt )
                {
                    DB::rollBack();
                    $arr_fail[$loan_info_no] = "거래내역의 정보를 찾을 수 없습니다.";
                    continue;
                }

                // 계약정보 SELECT
                $rslt = DB::Table("loan_info")->select("*")->where("no", $loan_info_no)->where("save_status", "Y")->first();
                $rslt = Func::chungDec(["LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT
                $vl = (Array) $rslt;
                if( !$vl )
                {
                    DB::rollBack();
                    $arr_fail[$loan_info_no] = "계약정보를 찾을 수 없습니다.";
                    continue;
                }

                // 현재 사모사채면서 원금상환 건은 삭제가 안됨
                if( !empty($vl['pro_cd']) && $vl['pro_cd'] != '03' && !empty($vt['return_origin']) && $vt['return_origin'] > 0 )
                {
                    DB::rollBack();
                    $arr_fail[$loan_info_no] = "삭제가 불가능한 건입니다.";
                    continue;
                }
                
                // 수익지급취소처리
                $trade = new Trade($loan_info_no);
                $rslt = $trade->tradeInDelete($trade_no);
                if( is_string($rslt) )
                {
                    DB::rollBack();

                    $arr_fail[$loan_info_no] = $rslt;
                    continue;
                }
                
                //  원장변경내역 입력
                $_wch = [
                    "cust_info_no"     =>  $vt['cust_info_no'],
                    "loan_info_no"     =>  $vt['loan_info_no'],
                    "loan_usr_info_no" =>  $vt['loan_usr_info_no'],
                    "worker_id"        =>  Auth::id(),
                    "work_time"        =>  date("YmdHis"),
                    "worker_code"      =>  Auth::user()->branch_code ?? '',
                    "loan_status"      =>  "",
                    "manager_code"     =>  "",
                    "div_nm"           =>  "거래취소",
                    "before_data"      =>  "null,".$vt['trade_date'],       //  변경전값(null 셋팅인듯?),기산일자
                    "after_data"       =>  "null,".date("Ymd"),             //  변경후값(null 셋팅인듯?),취소일자
                    "trade_type"       =>  $vt['trade_type'],
                    "sms_yn"           =>  'N',
                    "memo"             =>  "",
                ];

                $result_wch = Func::saveWonjangChgHist($_wch);
                if( $result_wch != "Y" )
                {
                    DB::rollBack();

                    $arr_fail[$loan_info_no] = "원장변경내역 등록 실패.";
                    continue;
                }

                $s_cnt++;
                DB::commit();
            }
        }
    
        if(isset($arr_fail) && sizeof($arr_fail)>0)
        {
            $error_msg = "실패건이 존재합니다. \n";

            foreach($arr_fail as $t_no => $msg)
            {
                $error_msg .= "[".$t_no."] => ".$msg."\n";
            }

            $return_msg = sizeof($request->listChk)."건 중 ".$s_cnt."건 성공 ".sizeof($arr_fail)."건 실패\n";
            $return_msg .= Func::nvl($error_msg,"");
        }
        else
        {
            $return_msg = "정상처리 되었습니다.";
        }

        $_RESULT['rslt'] = 'Y';
        $_RESULT['msg']  = $return_msg;

        return $_RESULT;
    }
}