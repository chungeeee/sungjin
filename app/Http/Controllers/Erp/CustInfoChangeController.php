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

class CustInfoChangeController extends Controller
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
        $list                  = new DataList(Array("listName"=>"custinfochange","listAction"=>'/'.$request->path()));

        if(!isset($request->tabs)) $request->tabs = 'name';
        $list->setTabs(Vars::$arrayCustInfoChangeList,$request->tabs);

        $list->setSearchDate('날짜검색',Array('l.save_time' => '입력일'),'searchDt','Y');
        $list->setSearchType('manager_code',Func::getBranch(),'관리지점');

        if( Func::funcCheckPermit("E001") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/erp/custinfochangeexcel', 'form_custinfochange')", "btn-success");
        }

        $list->setSearchDetail(Array( 
            'L.CUST_INFO_NO'            => '차입자번호',
            'LI.NO'                     => '계약번호',
            'CI.NAME'                   => '이름',
        ));
        
        return $list;
    }
    
    /**
     * 고객정보변경내역 메인화면
     *
     * @param  request
     * @return view
     */
    public function custInfoChange(Request $request)
    {
        $list   = $this->setDataList($request);

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬) 
        $list->setlistTitleCommon(Array
        (
            'cust_info_no'     => Array('차입자번호', 0, '70px', 'center', '', 'cust_info_no'),
            'name'             => Array('차입자명', 0, '70px', 'center', '', 'ci.name'),
        ));
        $list->setlistTitleTabs('name',Array
        (
            'pre_name'          => Array('이름(변경전)', 0, '70px', 'center', '', ''),
            'name'              => Array('이름', 0, '70px', 'center', '', 'l.name'),
        ));
        $list->setlistTitleTabs('ssn',Array
        (
            'pre_ssn'          => Array('주민등록번호(변경전)', 0, '70px', 'center', '', ''),
            'ssn'              => Array('주민등록번호', 0, '70px', 'center', '', 'l.ssn'),
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
            'pre_ph11'         => Array('전화번호1(변경전)', 0, '70px', 'center', '', ''),
            'ph11'             => Array('전화번호1', 0, '70px', 'center', '', 'l.ph11'),
        ));

        $list->setlistTitleTabs('bank11',Array
        (
            'pre_bank11'      => Array('은행/계좌번호1(변경전)', 0, '70px', 'center', '', ''),
            'bank11'          => Array('은행/계좌번호1', 0, '70px', 'center', '', ''),
        ));
        $list->setlistTitleTabs('addr11',Array
        (
            'pre_addr11'         => Array('주소1(변경전)', 0, '80px', 'center', '', ''),
            'addr11'             => Array('주소1', 0, '80px', 'center', '', ''),
        ));
        $list->setlistTitleTabs('memo',Array
        (
            'pre_memo'         => Array('고객메모(변경전)', 0, '80px', 'center', '', ''),
            'memo'             => Array('고객메모', 0, '80px', 'center', '', 'l.memo'),
        ));
        $list->setlistTitleCommonEnd(Array
        (
            'save_id'          => Array('작업자', 0, '80px', 'center', '', 'l.save_id'),
            'save_time'        => Array('작업일시', 0, '200px', 'center', '', 'l.save_time'),
        ));

        $rslt['result'] = $list->getList();

        return view('erp.custInfoChange')->with($rslt);
    }
    
    /**
     * 고객정보변경내역 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function custInfoChangeList(Request $request)
    {
        $list                = $this->setDataList($request);
        $array_user_id       = Func::getUserId();
        $array_bank_cd       = Func::getConfigArr('bank_cd');

        foreach($request->all() as $key => $val)
        {
            if($key=="tabsSelect") continue;
            $param[$key] = $val;
        }
        
        // 기본쿼리
        $HIST = DB::TABLE("cust_info_log l")
                    ->JOIN("cust_info ci", "l.cust_info_no", "=", "ci.no")
                    ->JOIN("loan_info li", "ci.no", "=", "li.cust_info_no")
                    ->SELECT("l.*", "ci.name", "li.no as loan_info_no", "li.manager_code");   
        
        if( $request->tabsSelect=="name" )
        {
            $where_raw = "((l.name!= '' and l.name is not null))";
            $pre_select = "name";
        }
        else if( $request->tabsSelect=="ssn" )
        {
            $where_raw = "((l.ssn!= '' and l.ssn is not null))";
            $pre_select = "ssn";
        }
        else if( $request->tabsSelect=="relation" )
        {
            $where_raw = "((l.relation!= '' and l.relation is not null))";
            $pre_select = "relation";
        }
        else if( $request->tabsSelect=="email" )
        {
            $where_raw = "((email!= '' and email is not null))";
            $pre_select = "email";
        }
        else if( $request->tabsSelect=="com_ssn" )
        {
            $where_raw = "((com_ssn!= '' and com_ssn is not null))";
            $pre_select = "com_ssn";
        }
        else if( $request->tabsSelect=="ph11" )
        {
            $where_raw = "((ph11!= '' and ph11 is not null) and (ph12!= '' and ph12 is not null) and (ph13!= '' and ph13 is not null))";
            $pre_select = "ph11, ph12, ph13";
        }
        else if($request->tabsSelect=="addr11")
        {
            $where_raw = " ((zip1!= '' and zip1 is not null) and (addr11!= '' and addr11 is not null) and (addr12!= '' and addr12 is not null)) ";
            $pre_select = "zip1 as zip11, addr11, addr12";
        }
        else if( $request->tabsSelect=="bank11" )
        {
            $where_raw = "((bank_cd!= '' and bank_cd is not null) or (bank_ssn!= '' and bank_ssn is not null)) ";
            $pre_select = "bank_cd as bank11, bank_ssn as bank12";
        }
        else if( $request->tabsSelect=="memo" )
        {
            $where_raw = "((memo!= '' and memo is not null))";
            $pre_select = "memo";
        }

        if(!$param['listOrder'])
        {
            $param['listOrder'] = "l.save_time, l.cust_info_no";
        }

        if( !$param['listOrderAsc'] ) 
        {
            $param['listOrderAsc'] = 'desc';
        }

        $HIST->WHERERAW($where_raw);
        $HIST = $list->getListQuery('CUST_INFO_LOG', 'main', $HIST, $param);

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($HIST, $request->page, $request->listLimit, 10, $request->listName);
        $rslt   = $HIST->GET();
        $rslt = Func::chungDec(["CUST_INFO_LOG","CUST_INFO","LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        $configArr     = Func::getConfigArr();
        $array_key_chk = Array('relation', 'memo', 'bank11', 'bank21', 'bank31', 'zip11', 'zip21', 'addr11', 'addr21');

        $cnt = 0;
        foreach( $rslt as $v )
        {                                      
            $v->onclick           = 'popUpFull(\'/erp/customerpop?cust_info_no='.$v->cust_info_no.'\', \'cust_info'.$v->cust_info_no.'\')';                                                                   
            $v->line_style        = 'cursor: pointer;';
            
            $v->name              = Func::nameMasking($v->name, 'N');
            $v->post_send_cd      = Func::nvl($configArr['addr_cd'][$v->post_send_cd]);
            $v->ph11              = $v->ph11."-".$v->ph12."-".$v->ph13;
            $v->addr11            = $v->zip1." ".$v->addr11." ".$v->addr12;
            $v->bank11            = Func::nvl($array_bank_cd[$v->bank_cd], $v->bank_cd)." / ".$v->bank_ssn;
            $v->save_id           = $v->save_id ? Func::getArrayName($array_user_id, $v->save_id) : '';
            $v->save_time         = Func::dateFormat($v->save_time);
            $v->ssn               = Func::ssnFormat($v->ssn,'A');

            $HIST_PRE = DB::TABLE("cust_info_log l")
                        ->SELECTRAW($pre_select)
                        ->WHERERAW($where_raw)
                        ->WHERERAW(" cust_info_no = '".$v->cust_info_no."' and seq < ".$v->seq)
                        ->ORDERBY("seq", "desc")
                        ->FIRST();

            if(is_object($HIST_PRE))
            {
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
                        $v->pre_ssn = Func::ssnFormat($v->pre_ssn,'A');
                    }
                    
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
     * 고객정보변경내역 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function custInfoChangeExcel(Request $request)
    {
        if( !Func::funcCheckPermit("E001") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }
        
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');

        $list           = $this->setDataList($request);
        $down_div       = $request->down_div;
        $array_user_id  = Func::getUserId();
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        foreach($request->all() as $key => $val)
        {
            if($key=="tabsSelect") continue;
            $param[$key] = $val;
        }

        // 기본쿼리
        $HIST = DB::TABLE("cust_info_log l")
                    ->JOIN("cust_info ci", "l.cust_info_no", "=", "ci.no")
                    ->JOIN("loan_info li", "ci.no", "=", "li.cust_info_no")
                    ->SELECT("l.*", "ci.name", "li.no as loan_info_no", "li.manager_code");    

        // 엑셀 헤더 공통
		$excel_header = array('NO','회원번호','차입자명');

        // 엑셀 헤더 추가
        if( $request->tabsSelect=="name" )
        {
            $where_raw = "((l.name!= '' and l.name is not null))";
            $pre_select = "name";
            array_push($excel_header, '이름(변경전)', '이름');
        }
        else if( $request->tabsSelect=="ssn" )
        {
            $where_raw = "((l.ssn!= '' and l.ssn is not null))";
            $pre_select = "ssn";
            array_push($excel_header, '주민등록번호(변경전)', '주민등록번호');
        }
        else if( $request->tabsSelect=="relation" )
        {
            $where_raw = "((l.relation!= '' and l.relation is not null))";
            $pre_select = "relation";
            array_push($excel_header, '관계(변경전)', '관계');
        }
        else if( $request->tabsSelect=="email" )
        {
            $where_raw = "((l.email!= '' and l.email is not null))";
            $pre_select = "email";
            array_push($excel_header, '이메일(변경전)' ,'이메일');
        }
        else if( $request->tabsSelect=="com_ssn" )
        {
            $where_raw = "((l.com_ssn!= '' and l.com_ssn is not null))";
            $pre_select = "com_ssn";
            array_push($excel_header, '사업자번호(변경전)' ,'사업자번호');
        }
        else if( $request->tabsSelect=="ph11" )
        {
            $where_raw = "((l.ph11!= '' and l.ph11 is not null) and (l.ph12!= '' and l.ph12 is not null) and (l.ph13!= '' and l.ph13 is not null))";
            $pre_select = "ph11, ph12, ph13";
            array_push($excel_header, '전화번호1(변경전)', '전화번호1');
        }
        else if( $request->tabsSelect=="bank11" )
        {
            $where_raw = "((l.bank_cd!= '' and l.bank_cd is not null) or (l.bank_ssn!= '' and l.bank_ssn is not null)) ";
            $pre_select = "bank_cd as bank11, bank_ssn as bank12";
            array_push($excel_header, '은행/계좌번호1(변경전)', '은행/계좌번호1');
        }
        else if($request->tabsSelect=="addr11")
        {
            $where_raw = " ((l.zip1!= '' and l.zip1 is not null) and (l.addr11!= '' and l.addr11 is not null) and (l.addr12!= '' and l.addr12 is not null)) ";
            $pre_select = "zip1 as zip11, addr11, addr12";
            array_push($excel_header, '주소1(변경전)','주소1');
        }
        else if( $request->tabsSelect=="memo" )
        {
            $where_raw = "((l.memo!= '' and l.memo is not null))";
            $pre_select = "memo";
            array_push($excel_header, '메모(변경전)','메모');
        }

        if(!$param['listOrder'])
        {
            $param['listOrder'] = "l.save_time, l.cust_info_no";
        }

        if( !$param['listOrderAsc'] ) 
        {
            $param['listOrderAsc'] = 'desc';
        }

        array_push($excel_header, '작업자','작업일시');
        $HIST->WHERERAW($where_raw);
        $HIST = $list->getListQuery('CUST_INFO_LOG', 'main', $HIST, $param);

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($HIST, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($HIST);
        $file_name    = "차입자정보변경내역_".date("YmdHis").'_'.Auth::id().'.xlsx';
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

        $rslt = $HIST->GET();
        $rslt = Func::chungDec(["CUST_INFO_LOG","CUST_INFO","LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        $excel_data = [];
        $board_count=1;
        // 뷰단 데이터 정리.
        $configArr           = Func::getConfigArr();
        $array_bank_cd       = Func::getConfigArr('bank_cd');
        $board_count=1;
        $array_key_chk = Array('relation', 'memo', 'bank11', 'zip11', 'addr11');

        foreach ($rslt as $v)
        {
            $v->ph11              = Func::phFormat($v->ph11,$v->ph12,$v->ph13);
            $v->save_id           = $v->save_id ? Func::getArrayName($array_user_id, $v->save_id) : '';
            $v->save_time         = Func::dateFormat($v->save_time);

            $array_data = Array(
                $board_count,
                Func::addCi($v->cust_info_no),
                $v->name,
            );

            $HIST_PRE = DB::TABLE("cust_info_log l")
                        ->SELECTRAW($pre_select)
                        ->WHERERAW($where_raw)
                        ->WHERERAW(" cust_info_no = '".$v->cust_info_no."' and seq < ".$v->seq)
                        ->ORDERBY("seq", "desc")
                        ->FIRST();

            if(is_object($HIST_PRE))
            {
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
                        $v->pre_ssn = Func::ssnFormat($v->pre_ssn,'A');
                    }                   
                }  
            }
            
            if($request->tabsSelect=="name")
            {
                isset($v->pre_name) ? null : $v->pre_name = '';
                array_push($array_data, $v->pre_name, $v->name,);
            }
            else if($request->tabsSelect=="ssn")
            {
                isset($v->pre_ssn) ? null : $v->pre_ssn = '';
                $v->ssn = Func::ssnFormat($v->ssn,'A');
                array_push($array_data,$v->pre_ssn, $v->ssn,);
            }
            else if($request->tabsSelect=="relation")
            {
                isset($v->pre_relation) ? null : $v->pre_relation = '';
                array_push($array_data, $v->pre_relation, $v->relation,);
            }
            else if($request->tabsSelect=="email")
            {
                isset($v->pre_email) ? null : $v->pre_email = '';
                array_push($array_data, $v->pre_email, $v->email,);
            }
            else if($request->tabsSelect=="com_ssn")
            {
                isset($v->pre_com_ssn) ? null : $v->pre_com_ssn = '';
                array_push($array_data, $v->pre_com_ssn,$v->com_ssn,);
            }
            else if($request->tabsSelect=="ph11")
            {
                isset($v->pre_ph11) ? null : $v->pre_ph11 = '';
                isset($v->pre_ph12) ? null : $v->pre_ph12 = '';
                isset($v->pre_ph13) ? null : $v->pre_ph13 = '';

                if(($v->pre_ph11 != '') && ($v->pre_ph12 != '') && ($v->pre_ph13 != ''))
                {
                    $v->pre_ph11 = $v->pre_ph11.'-'.$v->pre_ph12.'-'.$v->pre_ph13;
                }

                array_push($array_data, $v->pre_ph11, $v->ph11,);
            }
            else if($request->tabsSelect=="bank11")
            {
                isset($v->pre_bank11) ? null : $v->pre_bank11 = '';

                $v->pre_bank1 = $v->pre_bank11;
                $v->bank1 = Func::nvl($array_bank_cd[$v->bank_cd], $v->bank_cd)." / ".$v->bank_ssn;
                array_push($array_data, $v->pre_bank1, $v->bank1);
            }
            else if($request->tabsSelect=="addr11")
            {
                isset($v->pre_zip1) ? null : $v->pre_zip1 = '';
                isset($v->pre_addr11) ? null : $v->pre_addr11 = '';
                isset($v->pre_addr12) ? null : $v->pre_addr12 = '';

                $v->pre_addr1 = $v->pre_zip1." ".$v->pre_addr11;
                $v->addr1 = $v->zip1." ".$v->addr11." ".$v->addr12;
                array_push($array_data, $v->pre_addr1, $v->addr1);
            }
            else if($request->tabsSelect=="memo")
            {
                isset($v->pre_memo) ? null : $v->pre_memo = '';
                
                array_push($array_data, $v->pre_memo, $v->memo,);
            }

            array_push($array_data, $v->save_id, $v->save_time,);

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




