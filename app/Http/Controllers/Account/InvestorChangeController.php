<?php

namespace App\Http\Controllers\Account;

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

class InvestorChangeController extends Controller
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
        $list = new DataList(Array("listName"=>"investorchange","listAction"=>'/'.$request->path()));

        if(!isset($request->tabs)) $request->tabs = 'relation';
        $list->setTabs(Vars::$arrayInvestorChangeList,$request->tabs);

        $list->setSearchDate('날짜검색',Array('l.save_time' => '입력일'),'searchDt','Y');
        $list->setSearchType('pro_cd', Func::getConfigArr('pro_cd'), '상품코드');

        if( Func::funcCheckPermit("U002") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/account/investorchangeexcel', 'form_investorchange')", "btn-success");
        }

        $list->setSearchDetail(Array( 
            'ui.nick_name'   => '투자자명',
            'ui.investor_no' => '투자자번호',
        ));
        
        return $list;
    }

    /**
     * 고객정보변경내역 메인화면
     *
     * @param  request
     * @return view
     */
    public function investorChange(Request $request)
    {
        $list   = $this->setDataList($request);

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬) 
        $list->setlistTitleCommon(Array
        (
            'investor_no'      => Array('투자자번호', 0, '70px', 'center', '', 'ui.investor_no'),
            'name'             => Array('고객명', 0, '70px', 'center', '', 'ui.name'),
        ));
        $list->setlistTitleTabs('relation',Array
        (
            'pre_relation'    => Array('관계(변경전)', 0, '70px', 'center', '', ''),
            'relation'        => Array('관계', 0, '70px', 'center', '', 'l.relation'),
        ));
        $list->setlistTitleTabs('email',Array
        (
            'pre_email'        => Array('이메일(변경전)', 0, '70px', 'center', '', ''),
            'email'            => Array('이메일', 0, '70px', 'center', '', 'l.email'),
        ));
        $list->setlistTitleTabs('com_ssn',Array
        (
            'pre_com_ssn'        => Array('사업자번호(변경전)', 0, '70px', 'center', '', ''),
            'com_ssn'            => Array('사업자번호', 0, '70px', 'center', '', 'l.com_ssn'),
        ));
        $list->setlistTitleTabs('ph11',Array
        (
            'pre_ph11'           => Array('전화번호1(변경전)', 0, '70px', 'center', '', ''),
            'ph11'               => Array('전화번호1', 0, '70px', 'center', '', 'l.ph11'),
        ));
        $list->setlistTitleTabs('ph21',Array
        (
            'pre_ph21'           => Array('전화번호2(변경전)', 0, '70px', 'center', '', ''),
            'ph21'               => Array('전화번호2', 0, '70px', 'center', '', 'l.ph21'),
        ));
        $list->setlistTitleTabs('ph41',Array
        (
            'pre_ph41'           => Array('전화번호3(변경전)', 0, '70px', 'center', '', ''),
            'ph41'               => Array('전화번호3', 0, '70px', 'center', '', 'l.ph41'),
        ));

        $list->setlistTitleTabs('bank11',Array
        (
            'pre_bank11'         => Array('은행/계좌번호1(변경전)', 0, '70px', 'center', '', ''),
            'bank11'             => Array('은행/계좌번호1', 0, '70px', 'center', '', ''),
        ));
        $list->setlistTitleTabs('bank21',Array
        (
            'pre_bank21'         => Array('은행/계좌번호2(변경전)', 0, '70px', 'center', '', ''),
            'bank21'             => Array('은행/계좌번호2', 0, '70px', 'center', '', ''),
        ));
        $list->setlistTitleTabs('bank31',Array
        (
            'pre_bank31'         => Array('은행/계좌번호3(변경전)', 0, '70px', 'center', '', ''),
            'bank31'             => Array('은행/계좌번호3', 0, '70px', 'center', '', ''),
        ));
        $list->setlistTitleTabs('addr11',Array
        (
            'pre_addr11'         => Array('주소1(변경전)', 0, '80px', 'center', '', ''),
            'addr11'             => Array('주소1', 0, '80px', 'center', '', ''),
        ));
        $list->setlistTitleTabs('addr21',Array
        (
            'pre_addr21'         => Array('주소2(변경전)', 0, '80px', 'center', '', ''),
            'addr21'             => Array('주소2', 0, '80px', 'center', '', ''),
        ));
        $list->setlistTitleTabs('memo',Array
        (
            'pre_memo'           => Array('고객메모(변경전)', 0, '80px', 'center', '', ''),
            'memo'               => Array('고객메모', 0, '80px', 'center', '', 'l.memo'),
        ));
        $list->setlistTitleCommonEnd(Array
        (
            'save_id'            => Array('작업자', 0, '80px', 'center', '', 'l.save_id'),
            'save_time'          => Array('작업일시', 0, '200px', 'center', '', 'l.save_time'),
        ));

        $rslt['result'] = $list->getList();

        return view('account.investorChange')->with($rslt);
    }

    /**
     * 고객정보변경내역 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investorChangeList(Request $request)
    {
        // Log::info('tabsSelect value: ' . $request->tabsSelect);

        $list                = $this->setDataList($request);
        $array_user_id       = Func::getUserId();
        $array_bank_cd       = Func::getConfigArr('bank_cd');

        foreach($request->all() as $key => $val)
        {
            if($key=="tabsSelect") continue;
            $param[$key] = $val;
        }
        
        // 기본쿼리
        $HIST = DB::TABLE("loan_usr_info_log as l")
                    ->JOIN("loan_usr_info as ui", "l.loan_usr_info_no", "=", "ui.no")
                    ->JOIN("loan_info as li", "ui.no", "=", "li.loan_usr_info_no")
                    ->SELECT("l.*", "ui.name", "li.pro_cd", "ui.investor_no");   
        
        if( $request->tabsSelect=="relation" )
        {
            $where_raw = "((l.relation!= '' and l.relation is not null))";
            $pre_select = "relation";
        }
        else if( $request->tabsSelect=="email" )
        {
            $where_raw = "((l.email!= '' and l.email is not null))";
            $pre_select = "l.email";
        }
        else if( $request->tabsSelect=="com_ssn" )
        {
            $where_raw = "((l.com_ssn!= '' and l.com_ssn is not null))";
            $pre_select = "com_ssn";
        }
        else if( $request->tabsSelect=="ph11" )
        {
            $where_raw = "((l.ph11!= '' and l.ph11 is not null) and (l.ph12!= '' and l.ph12 is not null) and (l.ph13!= '' and l.ph13 is not null))";
            $pre_select = "ph11, ph12, ph13";
        }
        else if( $request->tabsSelect=="ph21" )
        {
            $where_raw = "((l.ph21!= '' and l.ph21 is not null) and (l.ph22!= '' and l.ph22 is not null) and (l.ph23!= '' and l.ph23 is not null))";
            $pre_select = "ph21, ph22, ph23";
        }
        else if( $request->tabsSelect=="ph41" )
        {
            $where_raw = "((l.ph41!= '' and l.ph41 is not null) and (l.ph42!= '' and l.ph42 is not null) and (l.ph43!= '' and l.ph43 is not null))";
            $pre_select = "ph41, ph42, ph43";
        }
        else if($request->tabsSelect=="addr11")
        {
            $where_raw = " ((l.zip1!= '' and l.zip1 is not null) and (l.addr11!= '' and l.addr11 is not null) and (l.addr12!= '' and l.addr12 is not null)) ";
            $pre_select = "zip1 as zip11, addr11, addr12";
        }
        else if( $request->tabsSelect=="addr21" )
        {
            $where_raw = "((l.zip2!= '' and l.zip2 is not null) and (l.addr21!= '' and l.addr21 is not null) and (l.addr22!= '' and l.addr22 is not null))";
            $pre_select = "zip2 as zip21, addr21, addr22";
        }
        else if( $request->tabsSelect=="bank11" )
        {
            $where_raw = "((l.bank_cd!= '' and l.bank_cd is not null) or (l.bank_ssn!= '' and l.bank_ssn is not null)) ";
            $pre_select = "bank_cd as bank11, bank_ssn as bank12";
        }
        else if( $request->tabsSelect=="bank21" )
        {
            $where_raw = "((l.bank_cd2!= '' and l.bank_cd2 is not null) or (l.bank_ssn2!= '' and l.bank_ssn2 is not null)) ";
            $pre_select = "bank_cd2 as bank21 , bank_ssn2 as bank22";
        }
        else if( $request->tabsSelect=="bank31" )
        {
            $where_raw = "((l.bank_cd3!= '' and l.bank_cd3 is not null) or (l.bank_ssn3!= '' and l.bank_ssn3 is not null)) ";
            $pre_select = "bank_cd3 as bank31, bank_ssn3 as bank32";
        }
        else if( $request->tabsSelect=="memo" )
        {
            $where_raw = "((l.memo!= '' and l.memo is not null))";
            $pre_select = "memo";
        }
        
        if(!$param['listOrder'])
        {
            $param['listOrder'] = "l.save_time, l.loan_usr_info_no";
        }

        if( !$param['listOrderAsc'] ) 
        {
            $param['listOrderAsc'] = 'desc';
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='ui.investor_no' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='ui.nick_name' && !empty($param['searchString']) )
        {
            $HIST = $HIST->where('ui.nick_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        
        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $HIST->WHERERAW($where_raw);
        $HIST = $list->getListQuery('LOAN_USR_INFO_LOG', 'main', $HIST, $param);

        //Log::debug($HIST->toSql());

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($HIST, $request->page, $request->listLimit, 10, $request->listName);
        $rslt   = $HIST->GET();
        $rslt = Func::chungDec(["LOAN_USR_INFO_LOG","LOAN_USR_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        $configArr     = Func::getConfigArr();
        $array_key_chk = Array('relation', 'memo', 'bank11', 'bank21', 'bank31', 'zip11', 'zip21', 'addr11', 'addr21');

        $cnt = 0;
        foreach( $rslt as $v )
        {       
            $v->onclick          = 'popUpFull(\'/account/investorpop?no='.$v->loan_usr_info_no.'\', \'investor'.$v->loan_usr_info_no.'\')';
            $v->line_style       = 'cursor: pointer;';                     

            $v->name             = Func::nameMasking($v->name, 'N');
            $v->ph11              = Func::phFormat($v->ph11,$v->ph12,$v->ph13);
            $v->ph21              = Func::phFormat($v->ph21,$v->ph22,$v->ph23);
            $v->ph41              = Func::phFormat($v->ph41,$v->ph42,$v->ph43);
            $v->addr11            = $v->zip1." ".$v->addr11." ".$v->addr12;
            $v->addr21            = $v->zip2." ".$v->addr21." ".$v->addr22;
            $v->bank11             = Func::nvl($array_bank_cd[$v->bank_cd], $v->bank_cd)." / ".$v->bank_ssn;
            $v->bank21             = Func::nvl($array_bank_cd[$v->bank_cd2], $v->bank_cd2)." / ".$v->bank_ssn2;
            $v->bank31             = Func::nvl($array_bank_cd[$v->bank_cd3], $v->bank_cd3)." / ".$v->bank_ssn3;
            $v->save_id           = $v->save_id ? Func::getArrayName($array_user_id, $v->save_id) : '';
            $v->save_time         = Func::dateFormat($v->save_time);
            $v->ssn               = Func::ssnFormat($v->ssn,'Y');

            $HIST_PRE = DB::TABLE("loan_usr_info_log l")
                        ->SELECTRAW($pre_select)
                        ->WHERERAW($where_raw)
                        ->WHERERAW("loan_usr_info_no = '".$v->loan_usr_info_no."' and seq < ".$v->seq)
                        ->ORDERBY("seq", "desc")
                        ->FIRST();

            if(is_object($HIST_PRE))
            {
                Log::debug(print_r($HIST_PRE, true));
                foreach($HIST_PRE as $key => $val)
                {
                    if(in_array($key, $array_key_chk))
                    {
                        if(substr($key, 0, 4)==='bank' && substr($key, -1) == '1')
                        {
                            $v->{'pre_'.substr($key, 0, 5).'1'} = Func::nvl($array_bank_cd[$val], $val);
                        }
                        else if((substr($key, 0, 3)==='zip' || substr($key, 0, 4)==='addr')  && substr($key, -1) == '1')
                        {
                            if(substr($key, 0, 3)==='zip')
                            {
                                $key = str_replace('zip', 'addr', $key);
                                
                                $v->{'pre_'.substr($key, 0, 5).'1'} = Func::chungDecOne($val);
                            }
                            elseif(substr($key, 0, 4)==='addr')
                            {
                                $v->{'pre_'.substr($key, 0, 5).'1'}.= " ".Func::chungDecOne($val);
                            }
                        }
                        else
                        {
                            $v->{'pre_'.$key} = $val;
                        }
                    }
                    else
                    {
                        $v->{'pre_'.$key} = Func::chungDecOne($val);
                    }
                    
                    if(substr($key, 0, 2)==='ph' && substr($key, -1)!=='1')
                    {
                        $v->{'pre_'.substr($key, 0, 3).'1'}.= "-".Func::chungDecOne($val);
                    }
                    if(substr($key, 0, 4)==='bank' && substr($key, -1)!=='1')
                    {
                        $v->{'pre_'.substr($key, 0, 5).'1'} .= " / ".Func::chungDecOne($val);
                    }
                    if(substr($key, 0, 4)==='addr' && substr($key, -1)!=='1')
                    {       
                        $v->{'pre_'.substr($key, 0, 5).'1'} .= " ".Func::chungDecOne($val);   
                    }
                    if($key==='ssn')
                    {
                        $v->pre_ssn = Func::ssnFormat($v->pre_ssn,'Y');
                    }
                    //Log::debug('pre_'.substr($key, 0, 5).'1'."-----".Func::chungDecOne($val));
                    
                }
            }
            
            $r['v'][] = $v;
            $cnt ++;            
        }


        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path());
        $r['result']   = 1;
        $r['txt']      = $cnt;


        return json_encode($r);
    }

    /**
     * 엑셀다운로드 (투자자정보변경 내역)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investorChangeExcel(Request $request)
    {
        if( !Func::funcCheckPermit("U002") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }
        
        foreach($request->all() as $key => $val)
        {
            if($key=="tabsSelect") continue;
            $param[$key] = $val;
        }

        $array_user_id       = Func::getUserId();
        $array_bank_cd       = Func::getConfigArr('bank_cd');

        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $list           = $this->setDataList($request);
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        // 기본쿼리
        $HIST = DB::TABLE("loan_usr_info_log as l")
                    ->JOIN("loan_usr_info as ui", "l.loan_usr_info_no", "=", "ui.no")
                    ->JOIN("loan_info as li", "ui.no", "=", "li.loan_usr_info_no")
                    ->SELECT("l.*", "ui.name", "li.pro_cd", "ui.investor_no"); 
        
        if( $request->tabsSelect=="relation" )
        {
            $where_raw = "((l.relation!= '' and l.relation is not null))";
            $pre_select = "relation";
            $excel_header   = array('No','투자자번호','고객명','관계(변경전)', '관계','작업자','작업일시');
        }
        else if( $request->tabsSelect=="email" )
        {
            $where_raw = "((l.email!= '' and l.email is not null))";
            $pre_select = "email";
            $excel_header   = array('No','투자자번호','고객명','이메일(변경전)', '이메일','작업자','작업일시');
        }
        else if( $request->tabsSelect=="com_ssn" )
        {
            $where_raw = "((l.com_ssn!= '' and l.com_ssn is not null))";
            $pre_select = "com_ssn";
            $excel_header   = array('No','투자자번호','고객명','사업자번호(변경전)', '사업자번호','작업자','작업일시');
        }
        else if( $request->tabsSelect=="ph11" )
        {
            $where_raw = "((l.ph11!= '' and l.ph11 is not null) and (l.ph12!= '' and l.ph12 is not null) and (l.ph13!= '' and l.ph13 is not null))";
            $pre_select = "ph11, ph12, ph13";
            $excel_header   = array('No','투자자번호','고객명','전화번호1(변경전)', '전화번호1','작업자','작업일시');
        }
        else if( $request->tabsSelect=="ph21" )
        {
            $where_raw = "((l.ph21!= '' and l.ph21 is not null) and (l.ph22!= '' and l.ph22 is not null) and (l.ph23!= '' and l.ph23 is not null))";
            $pre_select = "ph21, ph22, ph23";
            $excel_header   = array('No','투자자번호','고객명','전화번호2(변경전)', '전화번호2','작업자','작업일시');
        }
        else if( $request->tabsSelect=="ph41" )
        {
            $where_raw = "((l.ph41!= '' and l.ph41 is not null) and (l.ph42!= '' and l.ph42 is not null) and (l.ph43!= '' and l.ph43 is not null))";
            $pre_select = "ph41, ph42, ph43";
            $excel_header   = array('No','투자자번호','고객명','전화번호3(변경전)', '전화번호3','작업자','작업일시');
        }
        else if( $request->tabsSelect=="bank11" )
        {
            $where_raw = "((l.bank_cd!= '' and l.bank_cd is not null) or (l.bank_ssn!= '' and l.bank_ssn is not null)) ";
            $pre_select = "bank_cd as bank11, bank_ssn as bank12";
            $excel_header   = array('No','투자자번호','고객명','은행/계좌번호1(변경전)', '은행/계좌번호1','작업자','작업일시');
        }
        else if( $request->tabsSelect=="bank21" )
        {
            $where_raw = "((l.bank_cd2!= '' and l.bank_cd2 is not null) or (l.bank_ssn2!= '' and l.bank_ssn2 is not null)) ";
            $pre_select = "bank_cd2 as bank21 , bank_ssn2 as bank22";
            $excel_header   = array('No','투자자번호','고객명','은행/계좌번호2(변경전)', '은행/계좌번호2','작업자','작업일시');
            
        }
        else if( $request->tabsSelect=="bank31" )
        {
            $where_raw = "((l.bank_cd3!= '' and l.bank_cd3 is not null) or (l.bank_ssn3!= '' and l.bank_ssn3 is not null)) ";
            $pre_select = "bank_cd3 as bank31, bank_ssn3 as bank32";
            $excel_header   = array('No','투자자번호','고객명','은행/계좌번호3(변경전)', '은행/계좌번호3','작업자','작업일시');
        }
        else if($request->tabsSelect=="addr11")
        {
            $where_raw = " ((l.zip1!= '' and l.zip1 is not null) and (l.addr11!= '' and l.addr11 is not null) and (l.addr12!= '' and l.addr12 is not null)) ";
            $pre_select = "zip1 as zip11, addr11, addr12";
            $excel_header   = array('No','투자자번호','고객명','주소1(변경전)', '주소1','작업자','작업일시');
        }
        else if( $request->tabsSelect=="addr21" )
        {
            $where_raw = "((l.zip2!= '' and l.zip2 is not null) and (l.addr21!= '' and l.addr21 is not null) and (l.addr22!= '' and l.addr22 is not null))";
            $pre_select = "zip2 as zip21, addr21, addr22";
            $excel_header   = array('No','투자자번호','고객명','주소2(변경전)', '주소2','작업자','작업일시');
        }
        else if( $request->tabsSelect=="memo" )
        {
            $where_raw = "((l.memo!= '' and l.memo is not null))";
            $pre_select = "memo";
            $excel_header   = array('No','투자자번호','고객명','고객메모(변경전)', '고객메모','작업자','작업일시');
        }
        
        if(!$param['listOrder'])
        {
            $param['listOrder'] = "l.save_time, l.loan_usr_info_no";
        }

        if( !$param['listOrderAsc'] ) 
        {
            $param['listOrderAsc'] = 'desc';
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='ui.investor_no' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='ui.nick_name' && !empty($param['searchString']) )
        {
            $HIST = $HIST->where('ui.nick_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $HIST->WHERERAW($where_raw);
        $HIST = $list->getListQuery('l', 'main', $HIST, $param);

        $target_sql = urlencode(encrypt(Func::printQuery($HIST))); // 페이지 들어가기 전에 쿼리를 저장해야한다. 
              
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($HIST, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "투자자정보변경내역".date("YmdHis").'_'.Auth::id().'.xlsx';
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

        $rslt   = $HIST->GET();
        $rslt = Func::chungDec(["LOAN_USR_INFO_LOG","LOAN_USR_INFO"], $rslt);	// CHUNG DATABASE DECRYP

        $array_key_chk = Array('relation', 'memo', 'bank11', 'bank21', 'bank31', 'zip11', 'zip21', 'addr11', 'addr21');


        // 엑셀 헤더
        $excel_data     = [];
        $array_data = [];
        $board_count = 1;
        foreach ($rslt as $v)
        {
            $array_data = [
                $board_count,
                $v->investor_no,
                Func::nameMasking($v->name, 'N'),
            ];

            $HIST_PRE = DB::TABLE("loan_usr_info_log l")
                        ->SELECTRAW($pre_select)
                        ->WHERERAW($where_raw)
                        ->WHERERAW("loan_usr_info_no = '".$v->loan_usr_info_no."' and seq < ".$v->seq)
                        ->ORDERBY("seq", "desc")
                        ->FIRST();

            if(is_object($HIST_PRE))
            {
                Log::debug(print_r($HIST_PRE, true));
                foreach($HIST_PRE as $key => $val)
                {
                    if(in_array($key, $array_key_chk))
                    {
                        if(substr($key, 0, 4)==='bank' && substr($key, -1) == '1')
                        {
                            $array_data[] = $v->{'pre_'.substr($key, 0, 5).'1'} = Func::nvl($array_bank_cd[$val], $val);
                        }
                        else if((substr($key, 0, 3)==='zip' || substr($key, 0, 4)==='addr')  && substr($key, -1) == '1')
                        {
                            if(substr($key, 0, 3)==='zip')
                            {
                                $key = str_replace('zip', 'addr', $key);
                                
                                $array_data[] = $v->{'pre_'.substr($key, 0, 5).'1'} = Func::chungDecOne($val);
                            }
                            elseif(substr($key, 0, 4)==='addr')
                            {
                                $array_data[] = $v->{'pre_'.substr($key, 0, 5).'1'}.= " ".Func::chungDecOne($val);
                            }
                        }
                        else
                        {
                            $array_data[] = $v->{'pre_'.$key} = $val;
                        }
                    }
                    else
                    {
                        $array_data[] = $v->{'pre_'.$key} = Func::chungDecOne($val);
                    }
                    
                    if(substr($key, 0, 2)==='ph' && substr($key, -1)!=='1')
                    {
                        $array_data[] = $v->{'pre_'.substr($key, 0, 3).'1'}.= "-".Func::chungDecOne($val);
                    }
                    if(substr($key, 0, 4)==='bank' && substr($key, -1)!=='1')
                    {
                        $array_data[] = $v->{'pre_'.substr($key, 0, 5).'1'} .= " / ".Func::chungDecOne($val);
                    }
                    if(substr($key, 0, 4)==='addr' && substr($key, -1)!=='1')
                    {       
                        $array_data[] = $v->{'pre_'.substr($key, 0, 5).'1'} .= " ".Func::chungDecOne($val);   
                    }
                    if($key==='ssn')
                    {
                        $array_data[] = $v->pre_ssn = Func::ssnFormat($v->pre_ssn,'Y');
                    }
                    //Log::debug('pre_'.substr($key, 0, 5).'1'."-----".Func::chungDecOne($val));
                    
                }
            }
            else {
                $array_data[] = $HIST_PRE;
            }

            if ($request->tabsSelect == "relation") {
                $array_data[] = $v->relation;
            }
            else if( $request->tabsSelect=="email" ) {
                $array_data[] = $v->email;
            }
            else if( $request->tabsSelect=="com_ssn" ) {
                $array_data[] = $v->com_ssn;
            }
            else if( $request->tabsSelect=="ph11" ) {
                $array_data[] = Func::phFormat($v->ph11,$v->ph12,$v->ph13);
            }
            else if( $request->tabsSelect=="ph21" ) {
                $array_data[] = Func::phFormat($v->ph21,$v->ph22,$v->ph23);
            }
            else if( $request->tabsSelect=="ph41" ) {
                $array_data[] = Func::phFormat($v->ph41,$v->ph42,$v->ph43);
            }
            else if( $request->tabsSelect=="bank11" ) {
                $array_data[] = Func::nvl($array_bank_cd[$v->bank_cd], $v->bank_cd)." / ".$v->bank_ssn;
            }
            else if( $request->tabsSelect=="bank21" ) {
                $array_data[] = Func::nvl($array_bank_cd[$v->bank_cd2], $v->bank_cd2)." / ".$v->bank_ssn2;
            }
            else if( $request->tabsSelect=="bank31" ) {
                $array_data[] = Func::nvl($array_bank_cd[$v->bank_cd3], $v->bank_cd3)." / ".$v->bank_ssn3;
            }
            else if( $request->tabsSelect=="addr11" ) {
                $array_data[] = $v->zip1." ".$v->addr11." ".$v->addr12;
            }
            else if( $request->tabsSelect=="addr21" ) {
                $array_data[] = $v->zip2." ".$v->addr21." ".$v->addr22;
            }
            else if( $request->tabsSelect=="memo" ) {
                $array_data[] = $v->memo;
            }
            $array_data[] = $v->save_id ? Func::getArrayName($array_user_id, $v->save_id) : '';
            $array_data[] = Func::dateFormat($v->save_time);

            $record_count++;
            $board_count++;
            $excel_data[] = $array_data;
        }
        
        // 엑셀 익스포트
        // ExcelFunc::fastexcelExport($excel_data,$excel_header,$file_name);
        ExcelFunc::fastexcelExport($excel_data, $excel_header, $origin_filename);
    
        // 파일 저장 여부 확인
        // $exists = Storage::disk('excel')->exists($file_name);
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
            
            // ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div,null, $origin_filename);
        }
        else
        {
           $array_result['result']    = 'N';
           $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }
        return $array_result;
    }
}