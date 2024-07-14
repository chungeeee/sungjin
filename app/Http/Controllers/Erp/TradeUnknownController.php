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

class TradeUnknownController extends Controller
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
        $array_conf_code = Func::getConfigArr();
        $list = new DataList(Array("listName"=>"tradeunknown","listAction"=>'/'.$request->path()));
        if(!isset($request->tabs))
        {
            $request->tabs = "A";
        }

        $list->setTabs(Array('A'=>'등록', 'Y'=>'정리', 'N'=>'삭제'),$request->tabs);
        $list->setCheckBox("no");

        if( Func::funcCheckPermit("R022") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/erp/tradeunknownexcel', 'form_tradeunknown')", "btn-success");
        }

        $list->setSearchDate('날짜검색', Array('U.trade_date'=>'입금일', 'U.save_time'=>'등록일', 'U.del_time'=>'삭제일', 'U.find_time'=>'정리일'), 'searchDt', 'Y', 'N', date("Ymd"), date("Ymd"), 'U.trade_date');
        $list->setRangeSearchDetail(Array('U.trade_money'=>'입금액'),'','','단위(원)');
        if( Func::funcCheckPermit("E004") || Func::funcCheckPermit("E031") )
        {
            $list->setSearchType('manager_code', Func::myPermitBranch(), '관리지점');
        }
        $list->setSearchType('reg_div', Vars::$arrayUnknownTradeRegDiv, '등록구분');
        $list->setSearchType('trade_path_cd', $array_conf_code['trade_in_path'], '입금경로');
        
        $list->setSearchDetail(Array( 'C.name'=>'이름', 'C.no'=>'고객번호', 'U.in_name'=>'입금자명', 'ssn' => '주민번호', ));

        // 권한추가
        if( Func::funcCheckPermit("A143","A") )
        {
            $list->setPlusButton("tradeUnknownForm('');");
        }

        return $list;
    }
    /**
      * 불명금리스트
      *
      * @param  \Illuminate\Http\Request  $request
      * @return view
      */
    public function trade(Request $request)
    {
        $list   = $this->setDataList($request);

        if( Func::funcCheckPermit("R025") )
        {
            $list->setLumpForm('DEL', Array('BTN_NAME'=>'삭제처리','BTN_ACTION'=>'lump_del(this);','BTN_ICON'=>'','BTN_COLOR'=>''));
        }

        $list->setlistTitleCommon(Array
        (
            'no'            => Array('미처리번호', 0, '', 'center', '', 'no'),
            'trade_date'    => Array('입금일', 0, '', 'center', '', 'trade_date'),
            'reg_div'       => Array('등록구분', 0, '', 'center', '', 'reg_div'),
            'trade_path_cd' => Array('입금경로', 0, '', 'center', '', 'trade_path_cd'),
            'manager_name'  => Array('관리지점', 0, '', 'center', '', 'manager_code'),
            'name'          => Array('고객이름', 0, '', 'center', '', 'name'),
            'ssn'           => Array('주민번호', 0, '', 'center', '', 'ssn'),
            'mo_bank_nm'    => Array('입금은행', 0, '', 'center', '', 'u.mo_bank_cd'),
            'in_name'       => Array('입금자명', 0, '', 'center', '', 'u.in_name'),
            'trade_money'   => Array('입금액', 0, '', 'center', '', 'u.trade_money'),
            'status'        => Array('상태', 0, '', 'center', '', 'u.status'),

            'save_time'     => Array('등록일시', 0, '', 'center', '', 'u.save_time'),
            'find_div'      => Array('정리구분', 0, '', 'center', '', 'find_div'),
//            'cust_info_no'  => Array('고객번호', 0, '', 'center', '', 'cust_info_no'),
        ));
        $list->setlistTitleTabs('Y',Array
        (
//            'loan_info_nos' => Array('정리계약번호', 0, '', 'center', '', 'loan_info_nos'),
            'find_id'       => Array('정리사번', 0, '', 'center', '', 'find_id'),
            'find_time'     => Array('정리일시', 0, '', 'center', '', 'find_time'),
        ));
        $list->setlistTitleTabs('N',Array
        (
            'del_time'      => Array('삭제일시', 0, '', 'center', '', 'del_time'),
        ));
        $list->setlistTitleTabs('B',Array
        (
            'find_time'     => Array('정리일시', 0, '', 'center', '', 'find_time'),
            'find_b_money'  => Array('반환금액', 0, '', 'center', '', 'find_b_money'),
        ));        
        //Log::debug(print_r($result, true));
        return view('erp.tradeUnknown')->with('result', $list->getList());

    }
  
    /**
     * 불명금 리스트처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function tradeList(Request $request)
    {
        //$request->isDebug = true;

        $list  = $this->setDataList($request);
        $param = $request->all();

        // Tab count 
        if($request->isFirst=='1')
        {
            $BOXC = DB::TABLE("UNKNOWN_TRADE AS U");
            $BOXC->LEFTJOIN("CUST_INFO AS C", "U.CUST_INFO_NO", "=", "C.NO");
            $BOXC->LEFTJOIN('LOAN_INFO AS L', [['C.NO', '=', 'L.CUST_INFO_NO'],['C.LAST_LOAN_INFO_NO', '=', 'L.NO']]);
            $BOXC->SELECT(DB::RAW("COUNT(*) AS B"));
            $BOXC->WHERE('U.STATUS', 'Y');
            $BOXC->WHERE('U.FIND_DIV', 'LIKE', '%B%');

            if( !Func::funcCheckPermit("E004") )
            {
                $BOXC->WHERERAW("( ( COALESCE(U.CUST_INFO_NO,0)=0 ) OR ( L.MANAGER_CODE IN ('".implode("','", array_keys(Func::myPermitBranch()))."') ) )");
            }

            $count = $BOXC->FIRST();
            $r['tabCount'] = array_change_key_case((Array)$count, CASE_UPPER);
        }
        
        // 기본쿼리
        $LOAN = DB::TABLE("UNKNOWN_TRADE AS U");
        $LOAN->LEFTJOIN("CUST_INFO AS C", "U.CUST_INFO_NO", "=", "C.NO");
        $LOAN->LEFTJOIN('LOAN_INFO AS L', [['C.NO', '=', 'L.CUST_INFO_NO'],['C.LAST_LOAN_INFO_NO', '=', 'L.NO']]);

        $LOAN->SELECT("U.*", "C.NAME", "C.SSN", "L.MANAGER_CODE");


        // 탭 검색
        $param['tabSelectNm'] = "U.STATUS";
        $param['tabsSelect']  = $request->tabsSelect;

        if( !isset($param['listOrder']) && !isset($param['listOrderAsc']) )
        {
            if( $request->tabsSelect=="A" )
            {
                $param['listOrder']    = "U.REG_TIME";
                $param['listOrderAsc'] = "DESC";
            }
            else if( $request->tabsSelect=="Y" )
            {
                $param['listOrder']    = "U.FIND_TIME";
                $param['listOrderAsc'] = "DESC";
            }
            else if( $request->tabsSelect=="N" )
            {
                $param['listOrder']    = "U.DEL_TIME";
                $param['listOrderAsc'] = "DESC";
            }
            else if( $request->tabsSelect=="B" )
            {
                $param['listOrder']    = "U.FIND_TIME";
                $param['listOrderAsc'] = "DESC";
            }
        }


        // 탭 검색
        if( $request->tabsSelect=="B" )
        {
            $LOAN->WHERE('U.STATUS', 'Y');
            $LOAN->WHERE('U.FIND_DIV', 'LIKE', '%B%');

            unset($param['tabSelectNm']);
            unset($param['tabsSelect']);
        }


        // 전지점 조회권한 없으면 자기 지점 또는 고객번호 없는 아이들만
        if( !Func::funcCheckPermit("E004") )
        {
            $LOAN->WHERERAW("( ( COALESCE(U.CUST_INFO_NO,0)=0 ) OR ( L.MANAGER_CODE IN ('".implode("','", array_keys(Func::myPermitBranch()))."') ) )");
        }

        $LOAN = $list->getListQuery("U", 'main', $LOAN, $param);

        //Log::debug(Func::printQuery($LOAN));

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $sum_data = Array
        (
            ["COALESCE(SUM(U.TRADE_MONEY),0)", '금액', '원'],
            // ["COALESCE(SUM(T.RETURN_ORIGIN),0)", '원금', '원'],
            // ["COALESCE(SUM(T.RETURN_INTEREST_SUM),0)", '이자', '원'],
            // ["COALESCE(SUM(T.RETURN_DAMBO_SET_FEE+T.RETURN_COST_MONEY),0)", '비용', '원'],
        );
        $paging = new Paging($LOAN, $request->page, $request->listLimit, 10, $request->listName, '', $sum_data);


        // 뷰단 데이터 정리.
        $getProCode = Func::getConfigArr('pro_cd');
        $getStatus = Vars::$arrayContractSta;
        $array_conf_code = Func::getConfigArr();
        $arrBranch       = Func::getBranch();
        $arrayUserId     = Func::getUserId();

        $cnt = 0;
        log::debug(Func::printQuery($LOAN));
        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["UNKNOWN_TRADE","CUST_INFO","LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        foreach( $rslt as $v )
        {

            $v->onclick          = 'javascript:window.open("/erp/tradeunknownform?no='.$v->no.'","unknownpop","width=1200, height=800, scrollbars=yes")';
            $v->line_style       = 'cursor: pointer;';

            $v->name             = Func::nameMasking($v->name, 'Y');
            $v->ssn              = Func::ssnFormat($v->ssn, 'Y');
            $v->status           = Vars::$arrayUnknownTradeStatus[$v->status];
            $v->reg_div          = Func::nvl(Vars::$arrayUnknownTradeRegDiv[$v->reg_div], $v->reg_div);

            $find_div = "";
            if( substr_count( $v->find_div, "T")>0 )    //입금거래등록
            {
                $find_div = "입금거래등록";
            }
            if( substr_count( $v->find_div, "B")>0 )    //계좌송금(반환)
            {
                $find_div.= ($find_div=="") ? "계좌송금(반환)" : ", 계좌송금(반환)";
            }
            if( substr_count( $v->find_div, "P")>0 )    //잡이익
            {
                $find_div.= ($find_div=="") ? "잡이익" : ", 잡이익";
            }
            $v->find_div = $find_div;


            $v->trade_path_cd    = Func::nvl($array_conf_code['trade_in_path'][$v->trade_path_cd],$v->trade_path_cd);

            $v->trade_date       = Func::dateFormat($v->trade_date);
            $v->trade_money      = number_format($v->trade_money);
            //$v->balance          = number_format($v->balance);
            $v->save_time        = Func::dateFormat($v->save_time);
            $v->find_time        = Func::dateFormat($v->find_time);
            $v->del_time         = Func::dateFormat($v->del_time);
            //$v->trade_count      = ( $v->loan_info_trade_nos!="" ) ? sizeof(explode(",", $v->loan_info_trade_nos)) : 0 ;

            $v->find_id          = Func::getArrayName($arrayUserId, $v->find_id);
            $v->find_b_money     = number_format($v->find_b_money);
            $v->manager_name     = Func::nvl($arrBranch[$v->manager_code], $v->manager_code);
            
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
     * 미처리입금 반환
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function chkLoanInfoCert(Request $request)
    {
        $request->isDebug = true;
        $unknown_trade_no = $request->unknown_trade_no;
        $confirm_status   = $request->confirm_status;

        if( !Func::funcCheckPermit("R005") )
        {
            return "권한이 없습니다.";
        }

        $v = DB::TABLE("UNKNOWN_TRADE")->WHERE("SAVE_STATUS", "Y")->WHERE("NO", $unknown_trade_no)->FIRST();
        $v = Func::chungDec(["UNKNOWN_TRADE"], $v);	// CHUNG DATABASE DECRYPT

        if( !$v || $v->firmbank_status!="Z" )
        {
            return "대상 반환정보를 찾을 수 없습니다.";
        }

        $save_time = date("YmdHis");
        $save_id   = Auth::id();

        DB::beginTransaction();
        
        // 승인처리
        if( $confirm_status=="Y" )
        {
            $rslt = DB::dataProcess('UPD', 'UNKNOWN_TRADE', ["FIRMBANK_STATUS"=>"A", "CERT_ID"=>$save_id, "CERT_TIME"=>$save_time], [["no",$unknown_trade_no],["FIRMBANK_STATUS","Z"]]);
            if( $rslt!="Y" )
            {
                DB::rollBack();
                return "승인 처리에 실패하였습니다.";
            }
        }
        // 거절처리
        else
        {
            if( substr_count( $v->find_div, "T")>0 )
            {
                $trades = explode(",",$v->loan_info_trade_nos);
                foreach( $trades as $loan_info_trade_no )
                {

                    // 입금정보 SELECT
                    $rslt = DB::TABLE("LOAN_INFO_TRADE")->SELECT("*")->WHERE("NO", $loan_info_trade_no)->WHERE("SAVE_STATUS", "Y")->WHERE("TRADE_DIV", "I")->FIRST();
                    $rslt = Func::chungDec(["LOAN_INFO_TRADE"], $rslt);	// CHUNG DATABASE DECRYPT
                    $vt = (Array) $rslt;
                    if( !$vt )
                    {
                        DB::rollBack();
                        Log::debug("삭제할 입금거래 정보를 찾을 수 없습니다.");
                        return "삭제할 입금거래 정보를 찾을 수 없습니다.";
                    }
                    $loan_info_no = $vt['loan_info_no'];

                    // 입금취소처리
                    $trade = new Trade($loan_info_no);
                    $rslt = $trade->tradeInDelete($loan_info_trade_no);
                    if( is_string($rslt) )
                    {
                        DB::rollBack();
                        Log::debug($rslt);
                        return $rslt;
                    }
                }
            }

            $rslt = DB::dataProcess('UPD', 'UNKNOWN_TRADE', ["STATUS"=>"A", "FIRMBANK_YN"=>"", "FIRMBANK_STATUS"=>""], [["no",$unknown_trade_no]]);
            if( $rslt!="Y" )
            {
                DB::rollBack();
                return "승인 처리에 실패하였습니다.";
            }

       }

        DB::commit();
        
        return "Y";

    }



    public function tradeunknownExcel(Request $request)
    {        
        if( !Func::funcCheckPermit("R022") && !isset($request->excel_flag) )
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
        $LOAN = DB::TABLE("UNKNOWN_TRADE AS U");
        $LOAN->LEFTJOIN("CUST_INFO AS C", "U.CUST_INFO_NO", "=", "C.NO");
        $LOAN->LEFTJOIN('LOAN_INFO AS L', [['C.NO', '=', 'L.CUST_INFO_NO'],['C.LAST_LOAN_INFO_NO', '=', 'L.NO']]);

        $LOAN->SELECT("U.*", "C.NAME", "C.SSN", "L.MANAGER_CODE");


        // 전지점 조회권한 없으면 자기 지점 또는 고객번호 없는 아이들만
        if( !Func::funcCheckPermit("E004") && !isset($request->excel_flag) )
        {
            $LOAN->WHERERAW("( ( COALESCE(U.CUST_INFO_NO,0)=0 ) OR ( L.MANAGER_CODE IN ('".implode("','", array_keys(Func::myPermitBranch()))."') ) )");
        }

        // 매각대상확인
        if(isset($param['sale_yn']) && $param['sale_yn']!='')
        {
            $sale_yn = $param['sale_yn'];

            // 비매각대상
            if($param['sale_yn']=='X')            
            {
                $LOAN->whereNotIn('L.NO', function($query) {
                    $query->SELECT('LOAN_INFO_NO')
                            ->FROM('LOAN_SELL')
                            ->WHERE('SAVE_STATUS', 'Y')
                            ->WHERE('SELL_STATUS', 'A')
                            ->WHERE('SELL_NO', '>', 0);
                });

                // 가상계좌로도 제외 시킴.
                $LOAN->whereNotIn('U.vir_acct_ssn', function($query) {
                    $query->SELECT("(select vir_acct_ssn from loan_info where no=LOAN_SELL.loan_info_no) as vir_acct_ssn")
                            ->FROM('LOAN_SELL')
                            ->WHERE('SAVE_STATUS', 'Y')
                            ->WHERE('SELL_STATUS', 'A')
                            ->WHERE('SELL_NO', '>', 0);
                });
            }
            else
            {

                // $LOAN->WHEREIN('L.NO', function($query) use ($sale_yn){
                //     $query->SELECT('LOAN_INFO_NO')
                //             ->FROM('LOAN_SELL')
                //             ->WHERE('SAVE_STATUS', 'Y')
                //             ->WHERE('SELL_STATUS', $sale_yn)
                //             ->WHERE('SELL_NO', '>', 0);
                // });

                $LOAN->WHEREIN('U.vir_acct_ssn', function($query) use ($sale_yn){
                    $query->SELECT("(select vir_acct_ssn from loan_info where no=LOAN_SELL.loan_info_no) as vir_acct_ssn")
                            ->FROM('LOAN_SELL')
                            ->WHERE('SAVE_STATUS', 'Y')
                            ->WHERE('SELL_STATUS', $sale_yn)
                            ->WHERE('SELL_NO', '>', 0);
                });
            }

            unset($param['sale_yn']);
        }

        // 탭 검색
        $param['tabSelectNm'] = "U.STATUS";
        $param['tabsSelect']  = $request->tabsSelect;

        $LOAN = $list->getListQuery("U", 'main', $LOAN, $param);
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($LOAN);
        $file_name    = "가상계좌정리등록_".date("YmdHis").'_'.Auth::id().'.xlsx';
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

        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["UNKNOWN_TRADE","CUST_INFO","LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        // 엑셀 헤더
		$excel_header = array('미처리번호','입금일','등록구분','입금경로','관리지점','고객이름','주민번호','입금은행','입금자명','모계좌','가상계좌','입금액','상태','등록일시','정리구분');
        $excel_data   = [];
        // 뷰단 데이터 정리.
        $getProCode = Func::getConfigArr('pro_cd');
        $getStatus  = Vars::$arrayContractSta;
        $arrBranch  = Func::getBranch();
        $array_conf_code = Func::getConfigArr();

        if( Func::funcCheckPermit('R022') )
        {
            $masking = 'A';
        }
        else
        {
            $masking = 'N';
        }

        foreach ($rslt as $v)
        {
            $find_div = "";
            if( substr_count( $v->find_div, "T")>0 )    //입금거래등록
            {
                $find_div = "입금거래등록";
            }
            if( substr_count( $v->find_div, "B")>0 )    //계좌송금(반환)
            {
                $find_div.= ($find_div=="") ? "계좌송금(반환)" : ", 계좌송금(반환)";
            }
            $v->find_div = $find_div;
            $array_data = Array(
                $v->no,
                Func::dateFormat($v->trade_date),
                Func::nvl(Vars::$arrayUnknownTradeRegDiv[$v->reg_div], $v->reg_div),
                Func::nvl($array_conf_code['trade_in_path'][$v->trade_path_cd],$v->trade_path_cd),
                Func::nvl($arrBranch[$v->manager_code], $v->manager_code),
                $v->name,
                Func::ssnFormat($v->ssn, $masking),
                Func::nvl($array_conf_code['bank_cd'][$v->mo_bank_cd],$v->mo_bank_cd),
                $v->in_name,
                $v->mo_ssn,
                $v->vir_acct_ssn,
                Func::numberFormat((int)($v->trade_money)),
                Vars::$arrayUnknownTradeStatus[$v->status],
                Func::dateFormat($v->save_time),
                $v->find_div,

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
     * 불명금등록 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function tradeForm(Request $request)
    {
        $array_config = Func::getConfigArr();

        // 불명금일련번호
        if( isset($request->no) && $request->no )
        {
            $unknown_trade_no = $request->no;
            $rslt = DB::TABLE("UNKNOWN_TRADE")->SELECT("*")->WHERE('NO',$unknown_trade_no)->FIRST();
            $rslt = Func::chungDec(["UNKNOWN_TRADE"], $rslt);	// CHUNG DATABASE DECRYPT
            $v = (Array) $rslt;
            
            $v['status_nm']     = Vars::$arrayUnknownTradeStatus[$v['status']];
            $v['loan_info_arr'] = explode(",",$v['loan_info_nos']);
            if( trim($v['in_name'])=="" )
            {
                $v['in_name'] = "이름없음";
            }
            // 입금처리한 거래 정보
            if( $v['status']=='Y' && $v['loan_info_trade_nos']!="" )
            {
                $rslt = DB::TABLE("LOAN_INFO_TRADE")->SELECT("*")->WHEREIN('NO',explode(",",$v['loan_info_trade_nos']))->GET();
                $rslt = Func::chungDec(["LOAN_INFO_TRADE"], $rslt);	// CHUNG DATABASE DECRYPT
                
                $v['trade_infos'] = $rslt;
            }
            // 고객이 연결된 경우 이름
            if( $v['cust_info_no'] && $v['cust_info_no']>0 )
            {
                $vcst = DB::TABLE("CUST_INFO")->SELECT("NAME, SSN")->WHERE('NO',$v['cust_info_no'])->FIRST();
                $vcst = Func::chungDec(["CUST_INFO"], $vcst);	// CHUNG DATABASE DECRYPT

                if($vcst != NULL)
                {
                    $v['cust_name']  = $vcst->name;
                    $v['cust_ssn']   = substr($vcst->ssn,0,6);
                }else
                {
                    $v['cust_name']  = "";
                    $v['cust_ssn']   = "";
                }
            }
            else
            {
                $v['cust_name']  = "";
                $v['cust_ssn']   = "";
            }

            $arrayUserId     = Func::getUserId();
            if(isset($v['save_id'])) $v['save_id'] = Func::getArrayName($arrayUserId, $v['save_id']);
            if(isset($v['find_id'])) $v['find_id'] = Func::getArrayName($arrayUserId, $v['find_id']);

            if( $v && $v['status']=="A" )
            {
                $action_mode = "UPDATE";

                if( $v['find_t_money']==0 )
                {
                    $v['find_t_money'] = $v['trade_money'];
                }
            }
            else
            {
                $action_mode = "NONE";
            }
        }
        else
        {
            $v = Array();
            $v['cust_info_no']  = "";
            $v['in_name']       = "";
            $v['trade_path_cd'] = "";
            $v['trade_money']   = 0;
            $v['trade_date']    = date("Y-m-d");
            $v['memo']          = "";
            $v['reg_div']       = "U";
            $unknown_trade_no   = 0;
            $action_mode = "INSERT";
        }

        return view('erp.tradeUnknownForm')->with("unknown_trade_no", $unknown_trade_no)->with("action_mode", $action_mode)->with("array_config", $array_config)->with("rslt", $v);
    }
 

    /**
     * 고객검색 후 선택된 고객의 송금계좌정보를 표시한다.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return String html
     */
    public function setBankInfo(Request $request)
    {

        $cust_info_no = $request->cust_info_no;

        $array_bank = Func::getConfigArr('bank_cd');

        // 기본쿼리
        $LOAN = DB::TABLE("LOAN_INFO");
        $LOAN->SELECT("STATUS, OVER_MONEY, LOAN_MONEY, BALANCE, LOAN_APP_NO, LOAN_BANK_CD, LOAN_BANK_SSN, LOAN_BANK_NAME");
        $LOAN->WHERE('LOAN_INFO.SAVE_STATUS','Y');
        $LOAN->WHERE('CUST_INFO_NO', $cust_info_no);
        $LOAN->ORDERBY('LOAN_INFO.NO', 'DESC');
        $rslt = Func::chungDec(["LOAN_INFO"], $LOAN);	// CHUNG DATABASE DECRYPT
        $vl = (Array) $rslt;

        $bank_code  = "";
        $bank_ssn   = "";
        $bank_owner = "";

        $return_string = $this->getBankInfoString($bank_code, $bank_ssn, $bank_owner, $array_bank);
        return $return_string;
    }

    // 송금은행정보 TR 출력
    private function getBankInfoString($bank_code, $bank_ssn, $bank_owner, $array_bank)
    {
        $return_string = "<tr>";


        $return_string.= "<td class='text-center'>";
        $return_string.= "  <input type='hidden' name='sub_bank_chk_yn'   id='sub_bank_chk_yn'   value=''>";
        $return_string.= "  <input type='hidden' name='sub_bank_chk_time' id='sub_bank_chk_time' value=''>";
        $return_string.= "  <input type='hidden' name='sub_bank_chk_id'   id='sub_bank_chk_id'   value=''>";
        $return_string.= "  <select class='form-control form-control-sm' id='sub_bank_code' name='sub_bank_code'>";
        $return_string.= "  <option value=''>선택</option>".Func::printOption($array_bank, $bank_code, false)."</select>";
        $return_string.= "</td>";
        $return_string.= "<td class='text-center'><input type='text' class='form-control form-control-sm text-center' id='sub_bank_ssn'   name='sub_bank_ssn' placeholder='계좌번호' value='".$bank_ssn."'></td>";
        $return_string.= "<td class='text-center'><input type='text' class='form-control form-control-sm text-center' id='sub_bank_owner' name='sub_bank_owner' placeholder='예금주명' value='".$bank_owner."'></td>";
        
        $return_string.= "</tr>";

        return $return_string;
    }




    /**
     * 고객검색 후 선택된 고객의 계약정보를 표시한다.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return String html
     */
    public function setLoanInfo(Request $request)
    {
        if( !isset($request->cust_info_no) )
        {
            return "파라미터 오류";
        }
        $cust_info_no = $request->cust_info_no;
        $trade_date   = str_replace("-","",$request->trade_date);
        $trade_money  = str_replace(",","",$request->trade_money);

        // 고객정보
        $cust = DB::TABLE("CUST_INFO")->SELECT("*")->WHERE("NO", $cust_info_no)->WHERE('SAVE_STATUS','Y')->FIRST();
        $cust = Func::chungDec(["CUST_INFO"], $cust);	// CHUNG DATABASE DECRYPT
        if( !$cust )
        {
            return "고객번호 오류";
        }

        // 계좌정보
        $LOAN = DB::TABLE("LOAN_INFO")->SELECT("*")->WHERE('SAVE_STATUS','Y')->WHERE('CUST_INFO_NO', $cust_info_no)->WHEREIN('STATUS', Array('A','B','C','D','S'))->ORDERBY("NO","ASC");
        $rslt = $LOAN->GET();
        $rslt = Func::chungDec(["LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        $getProCode = Func::getConfigArr('pro_cd');
        $getStatus  = Vars::$arrayContractStaColor;

        //$string = "고객번호 = ".$cust_info_no.", 고객명:".$cust->name;
        $string = "<table class='table table-sm card-secondary card-outline mt-1'>";
        $string.= "<tr class='text-center'>";
        $string.= "<td width='12%' bgcolor='EEEEEE'>고객번호</td>";
        $string.= "<td width='13%' bgcolor='FFFFFF'>".$cust_info_no."</td>";
        $string.= "<td width='12%' bgcolor='EEEEEE'>고객명</td>";
        $string.= "<td width='13%' bgcolor='FFFFFF'>".$cust->name."</td>";
        $string.= "<td width='12%' bgcolor='EEEEEE'>생년월일</td>";
        $string.= "<td width='13%' bgcolor='FFFFFF'>".substr($cust->ssn,0,6)."</td>";
        $string.= "<td width='12%' bgcolor='EEEEEE'>메세지발송</td>";
        $string.= "<td width='13%' bgcolor='FFFFFF'><input type='checkbox' name='sms_flag' id='sms_flag' class='list-check pr-0' value='A'><label class='form-check-label ml-1' for='sms_flag'>SMS발송</label></td>";
        $string.= "</tr>";
        $string.= "</table>";

        $string.= "<table class='table table-sm table-hover card-secondary card-outline mt-0'>";
        $string.= "<tr>";
        $string.= "<td width='40'  class='text-center'>계약</td>";
        //$string.= "<td width='14%' class='text-center'>상품</td>";
        $string.= "<td width='10%' class='text-center'>상환방법</td>";
        $string.= "<td width='6%' class='text-center'>금리</td>";
        $string.= "<td width='8%' class='text-center'>상태</td>";
        $string.= "<td width='7%' class='text-center'>이전거래</td>";
        $string.= "<td width='7%' class='text-center'>상환일</td>";
        $string.= "<td width='8%' class='text-right'>잔액</td>";
        $string.= "<td width='7%' class='text-right'>이자합계</td>";
        $string.= "<td width='7%' class='text-right'>청구금액</td>";
        $string.= "<td width='8%' class='text-right'>처리금액</td>";
        $string.= "<td class='text-center'>비고</td>";
        $string.= "</tr>";

        $getReturnMethodCd  = Func::getConfigArr('return_method_cd');
        $array_interest_val = Array();
        foreach( $rslt as $v )
        {
            // 원리금 배분을 위해서 계산한번 하고 따로 돌린다.
            $loan = new Loan($v->no);
            $loan->getInterest($trade_date);
            
            $array_interest_val[$v->no] = $loan;
        }
/*
            if( $trade_date < $loan->loanInfo['last_trade_date'] )
            {
                $bigo = "거래일(".$loan->loanInfo['last_trade_date'].") 이후 거래존재";
            }
            else
            {
                $bigo = "";
            }
*/
        $yu_cnt = 0;
        // 처리금액 분할
        $array_div_money = Trade::divTradeMoney($array_interest_val, $trade_money);
        foreach( $rslt as $v )
        {
            $val = $array_interest_val[$v->no]->interestInfo;
            $v->div_money = $array_div_money[$v->no];

            $string.= "<tr style='vertical-align:middle;'>";
            $string.= "<td class='text-center' title='".Func::nvl($getProCode[$v->pro_cd], $v->pro_cd)."'><a href='#' onclick='loan_info_pop(".$v->cust_info_no.",".$v->no.");'>".$v->no."</a></td>";
            //$string.= "<td class='text-center'>".Func::nvl($getProCode[$v->pro_cd], $v->pro_cd)."</td>";
            $string.= "<td class='text-center'>".Func::nvl($getReturnMethodCd[$v->return_method_cd], $v->return_method_cd)."</td>";
            $string.= "<td class='text-center' >".number_format($v->loan_rate,2)."%</td>";
            $string.= "<td class='text-center'>".Func::nvl($getStatus[$v->status], $v->status)."</td>";
            $string.= "<td class='text-center'>".substr(Func::dateFormat($v->last_trade_date),2)."</td>";
            $string.= "<td class='text-center'>".substr(Func::dateFormat($val['return_date']),2)."</td>";
            $string.= "<td class='text-right' >".number_format($val['balance'])."</td>";
            $string.= "<td class='text-right' >".number_format($val['interest_sum'])."</td>";
            $string.= "<td class='text-right' >".number_format($val['charge_money'])."</td>";
            $string.= "<td class='text-right p-0'><input type='text' class='form-control form-control-xs moneyformat' onKeyUp='calBalance()' id='div_money_".$v->no."' name='div_money_".$v->no."' placeholder='금액' value='".number_format($v->div_money)."' autocomplete='off' /></td>";
            $string.= "<td class='text-left pl-2 divbigo' id='div_bigo_".$v->no."'></td>";
            $string.= "</tr>";

            $yu_cnt++;
        }

        $string.= "</table>";
        $string.= "<input type='hidden' name='cust_info_no' value='".$cust_info_no."'>";
        $string.= "<input type='hidden' name='yu_cnt' id='yu_cnt' value='".$yu_cnt."'>";
        $string.= "<button type='button' class='btn btn-sm btn-info float-right mr-3' id='btn_preview' onclick='tradeUnknownPreview();'>입금처리 미리보기</button>";

        return $string;        
    }



    /**
     * 입금처리 미리보기
     *
     * @param  \Illuminate\Http\Request  $request
     * @return String 처리결과 등록된 loan_info_trade의 NO 또는 오류메세지
     */
    public function findPreview(Request $request)
    {
        $v = $request->input();

        $trade_date        = str_replace("-","",str_replace(" ","",$v['trade_date']));
        $trade_money_total = str_replace(",","",str_replace(" ","",$v['trade_money']));

        $find_t_money      = isset($v['find_t_money']) ? str_replace(",","",str_replace(" ","",$v['find_t_money'])) : 0 ;   // 입금거래
        $find_b_money      = isset($v['find_b_money']) ? str_replace(",","",str_replace(" ","",$v['find_b_money'])) : 0 ;   // 계좌송금
        $find_p_money      = isset($v['find_p_money']) ? str_replace(",","",str_replace(" ","",$v['find_p_money'])) : 0 ;   // 계좌송금
        
        if( $trade_money_total!=($find_t_money+$find_b_money+$find_p_money) )
        {
            $array_result['result'] = "0";
            $array_result['txt']    = "정리방법 합계금액이 일치하지 않습니다.";
            return json_encode($array_result);
        }

        $trade_path_cd     = $v['trade_path_cd'];
        $cust_info_no      = $v['cust_info_no'];

        $trade_money_sum = 0;
        $array_loans = Array();
        foreach( $v as $key => $val )
        {
            if( substr($key,0,10)=="div_money_" )
            {
                $loan_info_no = substr($key,10);
                $trade_money  = str_replace(",","",str_replace(" ","",$val));
                if( $trade_money>0 )
                {
                    $array_loans[$loan_info_no]['trade_type']    = "01";
                    $array_loans[$loan_info_no]['cust_info_no']  = $cust_info_no;
                    $array_loans[$loan_info_no]['loan_info_no']  = $loan_info_no;
                    $array_loans[$loan_info_no]['trade_money']   = $trade_money;
                    $array_loans[$loan_info_no]['lose_money']    = "0";
                    $array_loans[$loan_info_no]['trade_date']    = $trade_date;
                    $array_loans[$loan_info_no]['trade_path_cd'] = $trade_path_cd;
                    $trade_money_sum+= $trade_money;
                }
            }
        }
        if( $trade_money_sum!=$find_t_money )
        {
            $array_result['result'] = "0";
            $array_result['txt']    = "입금거래등록 처리금액 합계가 일치하지 않습니다.";
            return json_encode($array_result);
        }

        $array_result = Array();
        foreach( $array_loans as $loan_info_no => $v )
        {
            $trade = new Trade($v['loan_info_no']);
            //$val = $trade->setInterest($v['trade_date']);
            // 입금데이터 생성
            $v['action_mode'] = "PREVIEW";      //INSERT
            $vin = $trade->tradeInInsert($v);
    
            $arrTmp = [];
            $arrTmp['loan_info_no'] = $loan_info_no;
            $arrTmp['PROC_FLAG']    = $vin['PROC_FLAG'];
            $arrTmp['PROC_MSG']     = $vin['PROC_MSG'];
            $arrTmp['REPLAN_YN']    = $vin['REPLAN_YN'];
            $arrTmp['balance']      = isset($vin['balance'])     ? number_format($vin['balance'])        : 0 ;
            $arrTmp['return_date']  = isset($vin['return_date']) ? Func::dateFormat($vin['return_date']) : "" ;
            $array_result['v'][] = $arrTmp;
        }
        $array_result['result'] = "1";
        $array_result['txt']    = "성공";

        return json_encode($array_result);
    }


    /**
     * 불명금 등록처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return String 처리결과 등록된 loan_info_trade의 NO 또는 오류메세지
     */
    public function tradeAction(Request $request)
    {
        
        if( !isset($request->action_mode) || !isset($request->in_name) || !isset($request->trade_path_cd) || !isset($request->trade_date) || !isset($request->trade_money) )
        {
            return "입력값 오류";
        }

        // 권한체크 추가
        if($request->action_mode == "INSERT" && !Func::funcCheckPermit("A143","A"))
        {
            return "미처리입금등록 권한이 없습니다.";
        }
        if($request->action_mode != "INSERT" && !Func::funcCheckPermit("A144","A"))
        {
            return "미처리입금 삭제/정리 권한이 없습니다.";
        }

        $v = $request->input();
        $v['trade_date']  = str_replace("-","",str_replace(" ","",$v['trade_date']));
        $v['trade_money'] = str_replace(",","",str_replace(" ","",$v['trade_money']));

        $v['find_t_money'] = isset($v['find_t_money']) ? str_replace(",","",str_replace(" ","",$v['find_t_money'])) : 0 ;   // 입금거래
        $v['find_b_money'] = isset($v['find_b_money']) ? str_replace(",","",str_replace(" ","",$v['find_b_money'])) : 0 ;   // 계좌송금
        $v['find_p_money'] = isset($v['find_p_money']) ? str_replace(",","",str_replace(" ","",$v['find_p_money'])) : 0 ;   // 잡이익

        if( !is_numeric($v['trade_date']) || !is_numeric($v['trade_money']) || strlen($v['trade_date'])!=8 )
        {
            return "입력 형식 오류";
        }

        if( $v['action_mode']=="INSERT" )
        {
            if( $v['cust_info_no']=="" || $v['cust_info_no']==0 )
            {
                unset($v['cust_info_no']);
            }
            
            $v['save_status'] = "Y";
            $v['save_id']     = Auth::id();
            $v['save_time']   = date("YmdHis");
            $v['status']      = "A";
            $v['reg_div']     = "U";
            // $rslt = DB::dataProcess("INS", "UNKNOWN_TRADE", $v, null, $unknown_trade_no);
            $rslt = DB::dataProcess("INS", "UNKNOWN_TRADE", $v);
            if( $rslt!="Y" )
            {
                return "실행오류";
            }
            return "Y";
        }
        else if( $v['action_mode']=="DELETE" )
        {
            $unknown_trade_no = $v['unknown_trade_no'];

            $vu = [];
            $vu['memo']     = $v['memo'];
            $vu['del_id']   = Auth::id();
            $vu['del_time'] = date("YmdHis");
            $vu['save_status'] = "N";
            $vu['status']      = "N";

            // 거래원장 업데이트
            $rslt = DB::dataProcess("UPD", "UNKNOWN_TRADE", $vu, ['no'=>$unknown_trade_no]);
            if( $rslt!="Y" )
            {
                return "실행오류";
            }
            return "Y";  
        }
        // 고객연결해제 - 저장
        else if( $v['action_mode']=="SAVE" )
        {
            $request->isDebug = true;
            $unknown_trade_no = $v['unknown_trade_no'];

            $vu = [];
            $vu['memo']         = $v['memo'];
            $vu['cust_info_no']  = "";
            $vu['loan_info_nos'] = "";
            $vu['loan_info_trade_nos'] = "";

            // 거래원장 업데이트
            $rslt = DB::dataProcess("UPD", "UNKNOWN_TRADE", $vu, ['no'=>$unknown_trade_no]);
            if( $rslt!="Y" )
            {
                return "실행오류";
            }
            return "Y";  
        }        
        // 불명금 해결
        else if( $v['action_mode']=="FIND" )
        {

            $unknown_trade_no  = $v['unknown_trade_no'];

            $trade_date        = $v['trade_date'];
            $trade_money_total = $v['trade_money'];
            $trade_path_cd     = $v['trade_path_cd'];
            $in_name           = $v['in_name'];
            $sms_flag          = ( isset($v['sms_flag']) && $v['sms_flag']=="A" ) ? "A" : "N" ;

            $find_div_b        = isset($v['find_div_b']) ? $v['find_div_b'] : "";
            $find_div_t        = isset($v['find_div_t']) ? $v['find_div_t'] : "";
            $find_div_p        = isset($v['find_div_p']) ? $v['find_div_p'] : "";
            $find_div          = $find_div_b.$find_div_t.$find_div_p;

            $find_b_money      = ( $find_div_b=="B" && $v['find_b_money']>0 ) ? $v['find_b_money'] : 0 ;
            $find_t_money      = ( $find_div_t=="T" && $v['find_t_money']>0 ) ? $v['find_t_money'] : 0 ;
            $find_p_money      = ( $find_div_p=="P" && $v['find_p_money']>0 ) ? $v['find_p_money'] : 0 ;

            if( $trade_money_total!=( $find_t_money + $find_b_money + $find_p_money ) )
            {
                log::debug("UNKNOWN_TRADE_NO = ".$unknown_trade_no);
                log::debug("TRADE_MONEY      = ".$trade_money_total);
                log::debug("FIND_DIV_B       = ".$find_div_b);
                log::debug("FIND_DIV_T       = ".$find_div_t);
                log::debug("FIND_DIV_P       = ".$find_div_p);
                log::debug("FIND_B_MONEY     = ".$find_b_money);
                log::debug("FIND_T_MONEY     = ".$find_t_money);
                log::debug("FIND_P_MONEY     = ".$find_p_money);
                return "정리금액 합계가 일치하지 않습니다.";
            }


            DB::beginTransaction();

            // 결과업데이트
            $vu = [];
            $vu['find_id']      = Auth::id();
            $vu['find_time']    = date("YmdHis");
            $vu['save_status']  = "Y";
            $vu['status']       = "Y";
            $vu['find_div']     = $find_div;
            $vu['find_t_money'] = 0;
            $vu['find_b_money'] = 0;
            $vu['find_p_money'] = 0;

            $vu['bank_chk_yn']   = ( $v['sub_bank_chk_yn']!="Y" ) ? "N" : "Y" ;
            $vu['bank_chk_time'] = $v['sub_bank_chk_time'];
            $vu['bank_chk_id']   = $v['sub_bank_chk_id'];
            $vu['bank_code']     = $v['sub_bank_code'];
            $vu['bank_ssn']      = $v['sub_bank_ssn'];
            $vu['bank_owner']    = $v['sub_bank_owner'];

            $vu['firmbank_yn']          = "N";
            $vu['firmbank_status']      = "";
            $vu['firmbank_status_time'] = "";

            // 정리방법 - 계좌송금
            if( $find_div_b=="B" && $find_b_money>0 )
            {
                $vu['find_b_money'] = $find_b_money;

                if( $vu['bank_chk_yn']!="Y" )
                {
                    // return "송금계좌정보의 예금주명 조회는 필수입니다.";
                }
            }

            // 정리방법 - 거래등록
            if( $find_div_t=="T" && $find_t_money>0 )
            {
                $cust_info_no    = $v['cust_info_no'];
                $trade_money_sum = 0;
                $array_loans = Array();
                foreach( $v as $key => $val )
                {
                    if( substr($key,0,10)=="div_money_" )
                    {
                        $loan_info_no = substr($key,10);
                        $trade_money  = str_replace(",","",str_replace(" ","",$val));
                        if( $trade_money>0 )
                        {
                            $array_loans[$loan_info_no]['trade_type']    = "01";
                            $array_loans[$loan_info_no]['cust_info_no']  = $cust_info_no;
                            $array_loans[$loan_info_no]['loan_info_no']  = $loan_info_no;
                            $array_loans[$loan_info_no]['trade_money']   = $trade_money;
                            $array_loans[$loan_info_no]['lose_money']    = "0";
                            $array_loans[$loan_info_no]['trade_date']    = $trade_date;
                            $array_loans[$loan_info_no]['trade_path_cd'] = $trade_path_cd;

                            //$array_loans[$loan_info_no]['bank_cd']       = $bank_cd;
                            //$array_loans[$loan_info_no]['bank_ssn']      = $bank_ssn;
                            //$array_loans[$loan_info_no]['vir_acct_ssn']  = $vir_acct_ssn;

                            $array_loans[$loan_info_no]['in_name']  = $in_name;
                            $array_loans[$loan_info_no]['memo']     = "미처리입금 정리 (NO=".$unknown_trade_no.")";

                            $trade_money_sum+= $trade_money;
                        }
                    }
                }
                if( $trade_money_sum!=$find_t_money )
                {
                    return "입금처리 합계금액이 일치하지 않습니다.";
                }

                $loan_info_nos       = Array();
                $loan_info_trade_nos = Array();
                foreach( $array_loans as $loan_info_no => $v2 )
                {
                    $trade = new Trade($v2['loan_info_no']);

                    $v2['sms_flag']     = $sms_flag;
                    $v2['action_mode']  = "INSERT";
                    $loan_info_trade_no = $trade->tradeInInsert($v2);
                    if( !is_numeric($loan_info_trade_no) )
                    {
                        DB::rollBack();
                        if( is_array($loan_info_trade_no) )
                        {
                            return $loan_info_trade_no['PROC_MSG'];
                        }
                        else
                        {
                            return $loan_info_trade_no;
                        }
                    }

                    $loan_info_nos[]       = $loan_info_no;
                    $loan_info_trade_nos[] = $loan_info_trade_no;
                }

                // 결과업데이트
                $vu['find_t_money'] = $find_t_money;
                $vu['cust_info_no'] = $cust_info_no;
                $vu['loan_info_nos']       = implode(",",$loan_info_nos);
                $vu['loan_info_trade_nos'] = implode(",",$loan_info_trade_nos);


            }

            // 정리방법 - 잡이익
            if( $find_div_p=="P" && $find_p_money>0 )
            {
                $vu['find_p_money'] = $find_p_money;
            }

            // log::debug(print_r($vu,true));
            // DB::rollback();
            // return "";

            // 거래원장 업데이트
            $rslt = DB::dataProcess("UPD", "UNKNOWN_TRADE", $vu, ['no'=>$unknown_trade_no]);
            if( $rslt!="Y" )
            {
                return "실행오류";
            }

            DB::commit();


            $msg_contents = "";
            if( $vu['find_t_money']>0 )
            {
                $msg_contents.= "<br>입금거래 ".number_format($vu['find_t_money'])."원, 입금계약번호:".$vu['loan_info_nos'];
            }
            if( $vu['find_b_money']>0 )
            {
                $msg_contents.= "<br>계좌송금반환 ".number_format($vu['find_b_money'])."원";
            }
            if( $vu['find_p_money']>0 )
            {
                $msg_contents.= "<br>잡이익등록 ".number_format($vu['find_p_money'])."원";
            }
            $msg = Array(
                'recv_id'  => $vu['find_id'],
                'title'    => '미처리입금 정리', 
                'contents' => '처리자사번:'.$vu['find_id'].$msg_contents, 
                'msg_type' => 'S',
                'msg_level'=> 'info');
            Func::sendMessage($msg);


            return "Y";
        }



    }




    /**
     * 입금거래 일괄삭제 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return String 처리결과 Y 또는 오류메세지
     */
    public function tradeDelete(Request $request)
    {
        $val = $request->input();

        DB::beginTransaction();

        if( $val['action_mode']=="LUMP_TRADEUNKNOWN_DELETE" && is_array($val['listChk']) && sizeof($val['listChk'])>0 )
        {
            for( $i=0; $i<sizeof($val['listChk']); $i++ )
            {
                $unknown_trade_no = $val['listChk'][$i];

                // 미처리입금내역 SELECT
                $rslt = DB::TABLE("UNKNOWN_TRADE")->SELECT("no, memo")->WHERE("NO", $unknown_trade_no)->WHERE("SAVE_STATUS", "Y")->WHERE("STATUS", "A")->FIRST();
                $rslt = Func::chungDec(["UNKNOWN_TRADE"], $rslt);	// CHUNG DATABASE DECRYPT
                $vt = (Array) $rslt;
                
                if( !$vt )
                {
                    DB::rollBack();
                    Log::debug("선택한 미처리입금내역 정보를 찾을 수 없습니다.");
                    return "선택한 거래내역의 정보를 찾을 수 없습니다.";
                }

                $vu = [];
                $vu['memo']     = $vt['memo']."\n\n일괄삭제";
                $vu['del_id']   = Auth::id();
                $vu['del_time'] = date("YmdHis");
                $vu['save_status'] = "N";
                $vu['status']      = "N";

                // 거래원장 업데이트
                $rslt = DB::dataProcess("UPD", "UNKNOWN_TRADE", $vu, ['no'=>$unknown_trade_no]);
                if( $rslt!="Y" )
                {
                    DB::rollBack();
                    return "실행 중 오류가 발생했습니다.";
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





}

