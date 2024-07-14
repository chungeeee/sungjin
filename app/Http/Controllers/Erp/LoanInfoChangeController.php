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

class LoanInfoChangeController extends Controller
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
        $list = new DataList(Array("listName"=>"loaninfochange","listAction"=>'/'.$request->path()));

        if(!isset($request->tabs)) $request->tabs = 'loan_rate';
        $list->setTabs(Vars::$arrayLoanInfoChangeList,$request->tabs);

        $list->setSearchDate('날짜검색',Array('l.save_time' => '입력일'),'searchDt','Y');
        $list->setSearchType('manager_code',Func::getBranch(),'관리지점');
        $list->setSearchType('pro_cd', Func::getConfigArr('pro_cd'), '상품코드');

        if( Func::funcCheckPermit("E001") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/erp/loaninfochangeexcel', 'form_loaninfochange')", "btn-success");
        }

        $list->setSearchType('li-handle_code',Func::getConfigArr('mo_acct_div'),'법인 구분', '', '', '', '', 'Y', '', true);

        $list->setSearchDetail(Array( 
            'LI.CUST_INFO_NO'           => '차입자번호',
            'CI.NAME'                   => '차입자이름',
            'investor_no-inv_seq'       =>'채권번호',
        ));
        
        return $list;
    }
    
    /**
     * 채권정보변경내역 메인화면
     *
     * @param  request
     * @return view
     */
    public function loanInfoChange(Request $request)
    {
        $list   = $this->setDataList($request);

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬) 
        $list->setlistTitleCommon(Array
        (
            'investor_no_inv_seq'      => Array('채권번호', 0, '70px', 'center', '', ''),
            'name'                     => Array('차입자명', 0, '70px', 'center', '', 'ci.name'),
        ));
        $list->setlistTitleTabs('loan_rate',Array
        (
            //'pre_loan_rate'         => Array('정상금리(변경전)', 0, '70px', 'center', '', ''),
            //'pre_loan_delay_rate'   => Array('연체금리(변경전)', 0, '70px', 'center', '', ''),
            'loan_rate'             => Array('금리', 0, '70px', 'center', '', 'loan_rate'),
        ));
        $list->setlistTitleTabs('contract_date',Array
        (
            //'pre_contract_date'     => Array('계약일(변경전)', 0, '70px', 'center', '', ''),
            'contract_date'         => Array('계약일', 0, '70px', 'center', '', 'contract_date'),
        ));
        $list->setlistTitleTabs('contract_end_date',Array
        (
            //'pre_contract_end_date' => Array('만기일(변경전)', 0, '70px', 'center', '', ''),
            'contract_end_date'     => Array('만기일', 0, '70px', 'center', '', 'contract_end_date'),
        ));
        $list->setlistTitleTabs('contract_day',Array
        (
            //'pre_contract_day'      => Array('약정일(변경전)', 0, '70px', 'center', '', ''),
            'contract_day'          => Array('약정일', 0, '70px', 'center', '', 'contract_day'),
        ));
        $list->setlistTitleTabs('kihan_date',Array
        (
            //'pre_kihan_date'        => Array('기한이익상실일(변경전)', 0, '80px', 'center', '', ''),
            'kihan_date'            => Array('기한이익상실일', 0, '80px', 'center', '', 'kihan_date'),
        ));
        $list->setlistTitleTabs('status',Array
        (
            //'pre_status'            => Array('상태(변경전)', 0, '70px', 'center', '', ''),
            'status'                => Array('상태', 0, '70px', 'center', '', 'status'),
        ));
        $list->setlistTitleCommonEnd(Array
        (
            'save_id'             => Array('작업자', 0, '100px', 'center', '', 'l.save_id'),
            'save_time'           => Array('작업일시', 0, '100px', 'center', '', 'l.save_time'),
        ));

        $rslt['result'] = $list->getList();

        return view('erp.loanInfoChange')->with($rslt);
    }
    
    /**
     * 채권정보변경내역 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function loanInfoChangeList(Request $request)
    {
        $list  = $this->setDataList($request);
        $array_user_id = Func::getUserId();
        $array_branch  = Func::getBranch();

        foreach($request->all() as $key => $val)
        {
            if($key=="tabsSelect") continue;
            $param[$key] = $val;
        }

        // 기본쿼리
        if($request->tabsSelect=="loan_rate")
        {
            $HIST = DB::TABLE("loan_info_rate l")
                        ->JOIN("loan_info li", "l.loan_info_no", "=", "li.no")
                        ->JOIN("cust_info ci", "li.cust_info_no", "=", "ci.no")
                        ->SELECT("l.*", "ci.no as cust_info_no", "ci.name", "li.no as loan_info_no", "li.manager_code", "li.loan_usr_info_no", "li.inv_seq", "li.investor_no", "li.investor_type")
                        ->WHERE('l.save_status','Y');
            
            $table = "LOAN_INFO_RATE";

            $HIST->WHERERAW("(l.loan_rate is not null)");
            $pre_select = "loan_rate";
        }
        else if( $request->tabsSelect=="contract_day" )
        {
            $HIST = DB::TABLE("loan_info_cday l")
                        ->JOIN("loan_info li", "l.loan_info_no", "=", "li.no")
                        ->JOIN("cust_info ci", "li.cust_info_no", "=", "ci.no")
                        ->SELECT("l.*", "ci.no as cust_info_no", "ci.name", "li.no as loan_info_no", "li.manager_code", "li.loan_usr_info_no", "li.inv_seq", "li.investor_no", "li.investor_type")
                        ->WHERE('l.save_status','Y'); 

           $table = "LOAN_INFO_CDAY";

           $HIST->WHERERAW("((l.contract_day != '' and l.contract_day is not null))");
           $pre_select = "contract_day";
        }
        else
        {
            $HIST = DB::TABLE("loan_info_log l")
                        ->JOIN("loan_info li", "l.loan_info_no", "=", "li.no")
                        ->JOIN("cust_info ci", "li.cust_info_no", "=", "ci.no")
                        ->SELECT("l.*", "ci.no as cust_info_no", "ci.name", "li.no as loan_info_no", "li.manager_code", "li.loan_usr_info_no", "li.inv_seq", "li.investor_no", "li.investor_type");

            if( $request->tabsSelect=="kihan_date" )
            {
                $HIST->WHERERAW("((l.kihan_date!= '' and l.kihan_date is not null))");
                $pre_select = "kihan_date";
            }
            if( $request->tabsSelect=="contract_date" )
            {
                $HIST->WHERERAW("((l.contract_date!= '' and l.contract_date is not null))");
                $pre_select = "contract_date";
            }
            if( $request->tabsSelect=="contract_end_date" )
            {
                $HIST->WHERERAW("((l.contract_end_date!= '' and l.contract_end_date is not null))");
                $pre_select = "contract_end_date";
            }
            if( $request->tabsSelect=="status" )
            {
                $HIST->WHERERAW("((l.status!= '' and l.status is not null))");
                $pre_select = "status";
            }

            $table = "LOAN_INFO_LOG";   
            
            if(isset($param['manager_code'])) {
                $HIST->WHERERAW("((li.manager_code='".$param['manager_code']."'))");
                
                unset($param['manager_code']);
            }
        }

        if(!$param['listOrder'])
        {
            $param['listOrder'] = "l.save_time";
        }

        if( !$param['listOrderAsc'] ) 
        {
            $param['listOrderAsc'] = 'desc';
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
                            $HIST = $HIST->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $HIST = $HIST->WHERE('loan_usr_info.investor_no',$searchString[0])
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
                        $HIST = $HIST->WHERE('loan_usr_info.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $HIST = $HIST->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
                    }
                }
            }

            unset($param['searchString']);
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='LI.CUST_INFO_NO' && !empty($param['searchString']) )
        {
            if(strstr($param['searchString'], '-')) 
            {
                unset($param['searchString']);
            }
        }



        $HIST = $list->getListQuery($table, 'main', $HIST, $param);

        // Log::debug($HIST->toSql());

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($HIST, $request->page, $request->listLimit, 10, $request->listName);
        $rslt   = $HIST->GET();
        $rslt = Func::chungDec([$table,"CUST_INFO","LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        $configArr  = Func::getConfigArr();
        $loanLogArr = Array('loan_info','kihan_date','contract_end_date','contract_date','status');
        $array_key_chk = Array('loan_rate', 'contract_date', 'contract_end_date', 'contract_day', 'kihan_date', 'status');

        $cnt = 0;
        foreach( $rslt as $v )
        {
            $v->onclick                  = 'javascript:window.open("/erp/custpop?cust_info_no='.$v->cust_info_no.'&no='.$v->loan_info_no.'&loanMainTab=getLoanLog","msgpop","width=2000, height=1000, scrollbars=yes")';
            $v->line_style               = 'cursor: pointer;';
            
            $v->name                     = Func::nameMasking($v->name, 'N');
            
            $v->investor_no_inv_seq      = $v->investor_type.$v->investor_no.'-'.$v->inv_seq;

            if( in_array($request->tabsSelect,$loanLogArr) )
            {
                $v->loan_rate            = (float) $v->loan_rate.'%';
                $v->contract_date        = Func::dateFormat($v->contract_date);
                $v->contract_end_date    = Func::dateFormat($v->contract_end_date);
                $v->balance              = number_format($v->balance);
                $v->take_date            = Func::dateFormat($v->take_date);
                $v->return_date          = Func::dateFormat($v->return_date);
                $v->kihan_date           = Func::dateFormat($v->kihan_date);
                $v->status               = Vars::$arrayContractStaColor[$v->status];
            }
            else if( $request->tabsSelect=="loan_rate" )
            {
                $v->loan_rate            = (float) $v->loan_rate.'%';
            }
            else if( $request->tabsSelect=="manager" )
            {
                $v->old_manager_id       = Func::getArrayName($array_user_id, $v->old_manager_id);
                $v->old_manager_code     = Func::getArrayName($array_branch,  $v->old_manager_code);
                $v->new_manager_id       = Func::getArrayName($array_user_id, $v->new_manager_id);
                $v->new_manager_code     = Func::getArrayName($array_branch,  $v->new_manager_code);

                if( $v->old_manager_code==$v->new_manager_code && $v->old_manager_id==$v->new_manager_id )
                {
                    continue;
                }
            }

            if( !in_array($request->tabsSelect,$loanLogArr) )
            {
                $v->save_id              = $v->save_id ? Func::getArrayName($array_user_id, $v->save_id) : '';
            }
     
            $v->save_time                = Func::dateFormat($v->save_time);

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
     * 채권정보변경내역 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function loanInfoChangeExcel(Request $request)
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
        $array_branch   = Func::getBranch();
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        foreach($request->all() as $key => $val)
        {
            if($key=="tabsSelect") continue;
            $param[$key] = $val;
        }
        
        // 기본쿼리
        if($request->tabsSelect=="loan_rate")
        {
            $HIST = DB::TABLE("loan_info_rate l")
                        ->JOIN("loan_info li", "l.loan_info_no", "=", "li.no")
                        ->JOIN("cust_info ci", "li.cust_info_no", "=", "ci.no")
                        ->SELECT("l.*", "ci.no as cust_info_no", "ci.name", "li.no as loan_info_no", "li.manager_code", "li.loan_usr_info_no", "li.inv_seq", "li.investor_no", "li.investor_no", "li.investor_type")
                        ->WHERE('l.save_status','Y');
            
            $table = "LOAN_INFO_RATE";

            $HIST->WHERERAW("(l.loan_rate is not null)");
            $pre_select = "loan_rate";
        }
        else if( $request->tabsSelect=="contract_day" )
        {
            $HIST = DB::TABLE("loan_info_cday l")
                        ->JOIN("loan_info li", "l.loan_info_no", "=", "li.no")
                        ->JOIN("cust_info ci", "li.cust_info_no", "=", "ci.no")
                        ->SELECT("l.*", "ci.no as cust_info_no", "ci.name", "li.no as loan_info_no", "li.manager_code", "li.loan_usr_info_no", "li.inv_seq", "li.investor_no", "li.investor_type")
                        ->WHERE('l.save_status','Y'); 

           $table = "LOAN_INFO_CDAY";

           $HIST->WHERERAW("((l.contract_day != '' and l.contract_day is not null))");
           $pre_select = "contract_day";
        }
        else
        {
            $HIST = DB::TABLE("loan_info_log l")
                        ->JOIN("loan_info li", "l.loan_info_no", "=", "li.no")
                        ->JOIN("cust_info ci", "li.cust_info_no", "=", "ci.no")
                        ->SELECT("l.*", "ci.no as cust_info_no", "ci.name", "li.no as loan_info_no", "li.manager_code", "li.loan_usr_info_no", "li.inv_seq", "li.investor_no", "li.investor_type");

            if( $request->tabsSelect=="kihan_date" )
            {
                $HIST->WHERERAW("((l.kihan_date!= '' and l.kihan_date is not null))");
                $pre_select = "kihan_date";
            }
            if( $request->tabsSelect=="contract_date" )
            {
                $HIST->WHERERAW("((l.contract_date!= '' and l.contract_date is not null))");
                $pre_select = "contract_date";
            }
            if( $request->tabsSelect=="contract_end_date" )
            {
                $HIST->WHERERAW("((l.contract_end_date!= '' and l.contract_end_date is not null))");
                $pre_select = "contract_end_date";
            }
            if( $request->tabsSelect=="status" )
            {
                $HIST->WHERERAW("((l.status!= '' and l.status is not null))");
                $pre_select = "status";
            }

            $table = "LOAN_INFO_LOG";   
            
            if(isset($param['manager_code'])) {
                $HIST->WHERERAW("((li.manager_code='".$param['manager_code']."'))");                
                unset($param['manager_code']);
            }
        }

        if(!$param['listOrder'])
        {
            $param['listOrder'] = "l.save_time";
        }

        if( !$param['listOrderAsc'] ) 
        {
            $param['listOrderAsc'] = 'desc';
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
                            $HIST = $HIST->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $HIST = $HIST->WHERE('loan_usr_info.investor_no',$searchString[0])
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
                        $HIST = $HIST->WHERE('loan_usr_info.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $HIST = $HIST->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
                    }
                }
            }
            
            unset($param['searchString']);
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='LI.CUST_INFO_NO' && !empty($param['searchString']) )
        {
            if(strstr($param['searchString'], '-')) 
            {
                unset($param['searchString']);
            }
        }

        $HIST = $list->getListQuery($table, 'main', $HIST, $param);

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($HIST, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count   = 0;
        $file_name      = "채권정보변경내역_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $query          = Func::printQuery($HIST);
        $request_all    = $request->all();
        $request_all['class'] = __CLASS__;
        $request_all['work_id'] = Auth::id();
        $all_data       = json_encode($request_all, true);

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
        $rslt = Func::chungDec([$table,"CUST_INFO","LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        // 엑셀 헤더 공통
		$excel_header = array('NO','채권번호','차입자명');

        // 엑셀 헤더 추가
        if($request->tabsSelect=="loan_rate")
        {
            array_push($excel_header, '금리','작업자');
        }
        else if($request->tabsSelect=="contract_date")
        {
            array_push($excel_header, '계약일', '작업자');
        }
        else if($request->tabsSelect=="contract_end_date")
        {
            array_push($excel_header, '만기일', '작업자');
        }
        else if($request->tabsSelect=="contract_day")
        {
            array_push($excel_header, '약정일','작업자');
        }
        else if($request->tabsSelect=="kihan_date")
        {
            array_push($excel_header, '기한이익상실일');
        }
        else if($request->tabsSelect=="status")
        {
            array_push($excel_header, '상태', '작업자');
        }
        else if($request->tabsSelect=="manager")
        {
            array_push($excel_header, '이관관리지점','이관담당자','수관관리지점','수관담당자','작업자');
        }

        array_push($excel_header, '작업일시');

        $excel_data = [];
        
        // 뷰단 데이터 정리.
        $configArr  = Func::getConfigArr();
        $loanLogArr = Array('loan_info','kihan_date','contract_end_date','contract_date','status');
        $board_count=1;

        foreach ($rslt as $v)
        {
            $v->save_time         = Func::dateFormat($v->save_time);

            if( in_array($request->tabsSelect,$loanLogArr) )
            {
                $v->loan_rate            = (float) $v->loan_rate.'%';
                $v->contract_date        = Func::dateFormat($v->contract_date);
                $v->contract_end_date    = Func::dateFormat($v->contract_end_date);
                $v->balance              = number_format($v->balance);
                $v->take_date            = Func::dateFormat($v->take_date);
                $v->return_date          = Func::dateFormat($v->return_date);
                $v->kihan_date           = Func::dateFormat($v->kihan_date);
                $v->status               = Vars::$arrayContractSta[$v->status];
            }
            else if( $request->tabsSelect=="loan_rate" )
            {
                $v->loan_rate            = (float) $v->loan_rate.'%';
            }
            else if( $request->tabsSelect=="manager")
            {
                $v->old_manager_id       = Func::getArrayName($array_user_id, $v->old_manager_id);
                $v->old_manager_code     = Func::getArrayName($array_branch,  $v->old_manager_code);
                $v->new_manager_id       = Func::getArrayName($array_user_id, $v->new_manager_id);
                $v->new_manager_code     = Func::getArrayName($array_branch,  $v->new_manager_code);

                if( $v->old_manager_code==$v->new_manager_code && $v->old_manager_id==$v->new_manager_id )
                {
                    continue;
                }
            }

            if( !in_array($request->tabsSelect,$loanLogArr) )
            {
                $v->save_id              = $v->save_id ? Func::getArrayName($array_user_id, $v->save_id) : '';
            }

            $array_data = Array(
                $board_count,
                $v->investor_type.$v->investor_no.'-'.$v->inv_seq,
                $v->name,
            );

            if($request->tabsSelect=="loan_rate")
            {
                array_push($array_data, $v->loan_rate, $v->save_id);
            }
            if($request->tabsSelect=="contract_date")
            {
                array_push($array_data, $v->contract_date, '');
            }
            if($request->tabsSelect=="contract_end_date")
            {
                array_push($array_data, $v->contract_end_date, '');
            }
            if($request->tabsSelect=="contract_day")
            {
                array_push($array_data, $v->contract_day,$v->save_id,);
            }
            else if($request->tabsSelect=="kihan_date")
            {
                array_push($array_data, $v->kihan_date,);
            }
            else if($request->tabsSelect=="status")
            {
                array_push($array_data, $v->status, '');
            }
            else if($request->tabsSelect=="manager")
            {
                array_push($array_data, $v->old_manager_id, $v->old_manager_code, $v->new_manager_id, $v->new_manager_code,);
            }

            array_push($array_data, $v->save_time,);

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




