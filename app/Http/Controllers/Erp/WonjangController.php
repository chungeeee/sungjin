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

class WonjangController extends Controller
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
        $list                  = new DataList(Array("listName"=>"wonjang","listAction"=>'/'.$request->path()));
        $array_div['ALL']      = "전체";
        $array_div['RATE']     = "금리변경";
        $array_div['RATE_ALL'] = "일괄금리인하";
        $array_div['MNY']      = "대출송금액변경";
        $array_div['VIR']      = "가상계좌번호변경";
        $array_div             = array_merge($array_div, Vars::$arrayWonjangHisCd);
        unset($array_div['E'], $array_div['F'], $array_div['J'], $array_div['K'], $array_div['G'], $array_div['H'], $array_div['A'], $array_div['B']);

        $configArr           = Func::getConfigArr();
        $arr_trade_type      = $configArr['trade_in_type'];
        foreach( $configArr['trade_out_type'] as $key => $v )
        {
            $arr_trade_type[$key] = $v;
        }

        // 증명발급비는 거래구분이 없어서 따로 추가
        $arr_trade_type['Z1'] = "증명발급비";


        if(!isset($request->tabs)) $request->tabs = 'ALL';
        $list->setTabs($array_div,$request->tabs);

        $list->setSearchDate('날짜검색',Array('work_time' => '변경일시'),'searchDt','Y');
        $list->setSearchType('manager_code',Func::getBranch(),'관리지점');
        $list->setSearchType('trade_type', $arr_trade_type, '거래취소구분');

        if( Func::funcCheckPermit("L022") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/erp/wonjangexcel', 'form_wonjang')", "btn-success");
        }


        $list->setSearchDetail(Array( 
            'WONJANG_CHG_HIST.LOAN_INFO_NO' => '계약번호',
            'WONJANG_CHG_HIST.CUST_INFO_NO' => '고객번호',
            'ci.NAME'                       => '고객명',
            'worker_nm'                     => '작업자명',
        ));
        
        return $list;
    }
    
    /**
     * 원장변경내역 메인화면
     *
     * @param  request
     * @return view
     */
    public function wonjang(Request $request)
    {
        $list   = $this->setDataList($request);

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬) 
        $list->setlistTitleCommon(Array
        (
            'cust_info_no'     => Array('고객번호', 0, '70px', 'center', '', 'WONJANG_CHG_HIST.cust_info_no'),
            'loan_info_no'     => Array('계약번호', 0, '70px', 'center', '', 'loan_info_no'),
            'name'             => Array('고객명', 0, '70px', 'center', '', 'name'),
            'ssn1'             => Array('생년월일', 0, '70px', 'center', '', 'ci.ssn'),
            'manager_code_nm'  => Array('관리지점', 0, '130px', 'center', '', ''),
            'div_cd'           => Array('작업명', 0, '200px', 'center', '', 'div_cd'),
            'loan_status'      => Array('변경시계약상태', 0, '100px', 'center', '', 'loan_status'),         //  결재당시 상태임
            'content'          => Array('변경내용', 0, '', 'center', '', ''),
            'worker_nm'        => Array('작업자', 0, '70px', 'center', '', 'worker_id'),
            'worker_code_nm'   => Array('작업자소속', 0, '80px', 'center', '', ''),
            'work_time'        => Array('작업일시', 0, '', 'center', '', 'work_time'),
        ));

        $rslt['result'] = $list->getList();

        return view('erp.wonjang')->with($rslt);
    }
    
    /**
     * 원장변경내역 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function wonjangList(Request $request)
    {
        $list  = $this->setDataList($request);
        $param = $request->all();

        $array_user_id       = Func::getUserId();
        $array_user_nm       = array_flip($array_user_id);
        $array_work_div      = Vars::$arrayWonjangHisCd;

        // 기본쿼리
        $HIST = DB::TABLE("WONJANG_CHG_HIST")
                    ->JOIN("CUST_INFO CI", "CUST_INFO_NO", "=", "CI.NO")
                    ->JOIN("LOAN_INFO LI", "LOAN_INFO_NO", "=", "LI.NO")
                    ->SELECT("WONJANG_CHG_HIST.*", "CI.NAME", "CI.SSN", "LI.MANAGER_CODE");           
                    
        if( $request->tabsSelect=="ALL" )
        {
        }
        // 금리인하
        else if( $request->tabsSelect=="RATE" )
        {
            $HIST->WHEREIN("WONJANG_CHG_HIST.DIV_CD",['E', 'F']);
        }
        // 일괄금리인하
        else if( $request->tabsSelect=="RATE_ALL" )
        {
            $HIST->WHEREIN("WONJANG_CHG_HIST.DIV_CD",['J', 'K']);
        }
        // 대출송금액변경
        else if( $request->tabsSelect=="MNY" )
        {
            $HIST->WHEREIN("WONJANG_CHG_HIST.DIV_CD",['G', 'H']);
        }
        // 가상계좌번호변경
        else if( $request->tabsSelect=="VIR" )
        {
            $HIST->WHEREIN("WONJANG_CHG_HIST.DIV_CD",['A', 'B']);
        }
        else if( $request->tabsSelect!="" )
        {
            $HIST->WHERE("WONJANG_CHG_HIST.DIV_CD", $request->tabsSelect);
        }

        if(isset($param['searchDetail']) && isset($param['searchString']) && $param['searchDetail'] == "worker_nm")
        {
            $HIST->WHERE("WONJANG_CHG_HIST.worker_id", Func::nvl($array_user_nm[$param['searchString']], $param['searchString']));

            unset($param['searchDetail'], $param['searchString']);
        }

        unset($param['tabSelectNm'] ,$param['tabsSelect']);

        if(!$param['listOrder'])
        {
            $param['listOrder'] = "WONJANG_CHG_HIST.WORK_TIME";
        }

        if( !$param['listOrderAsc'] ) 
        {
            $param['listOrderAsc'] = 'DESC';
        }

        $HIST = $list->getListQuery('WONJANG_CHG_HIST', 'main', $HIST, $param);

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($HIST, $request->page, $request->listLimit, 10, $request->listName);
        $rslt   = $HIST->GET();
        $rslt = Func::chungDec(["WONJANG_CHG_HIST","CUST_INFO","LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        $configArr           = Func::getConfigArr();
        $array_branch_code   = Func::getBranch();
        $getStatus           = Vars::$arrayContractStaColor;
        $arr_trade_type      = $configArr['trade_in_type'];
        foreach( $configArr['trade_out_type'] as $key => $v )
        {
            $arr_trade_type[$key] = $v;
        }

        // 증명발급비는 거래구분이 없어서 따로 추가
        $arr_trade_type['Z1'] = "증명발급비";

        $cnt = 0;
        foreach( $rslt as $v )
        {
            $v->manager_code_nm   = Func::nvl($array_branch_code[$v->manager_code],$v->manager_code );
            $v->loan_status       = Func::nvl($getStatus[$v->loan_status],$v->loan_status );
            $v->worker_nm         = Func::getArrayName($array_user_id, $v->worker_id);
            $v->work_time         = Func::dateFormat($v->work_time);
            $v->worker_code_nm    = Func::nvl($array_branch_code[$v->worker_code],$v->worker_code );
            $v->ssn1              = substr($v->ssn,0,6);

            $content = "";

            // 약정일변경
            if( $v->div_cd == "I" )
            {
                $content = "|[약정일변경][변경]| [변경전약정일] : {$v->before_data}일, [변경후약정일] : {$v->after_data}일";
            }
            // 금리변경
            else if( $v->div_cd == "E" || $v->div_cd == "F" )
            {
                if( $v->div_cd == "E" )
                {
                    $content = "|[금리변경][변경]| [변경전 정상금리:".number_format($v->before_data,2)."%] [변경후 정상금리 : ".number_format($v->after_data,2)."%]\n[변경사유 : {$v->memo}]";
                }
                elseif( $v->div_cd == "F" )
                {
                    $content = "|[금리변경][변경]| [변경전 연체금리:".number_format($v->before_data,2)."%] [변경후 연체금리 : ".number_format($v->after_data,2)."%]\n[변경사유 : {$v->memo}]";
                }
            }
            // 일괄금리인하
            else if( $v->div_cd == "J" || $v->div_cd == "K" )
            {
                if( $v->div_cd == "J" )
                {
                    $content = "|[일괄금리인하][변경]| [변경전 정상금리:".number_format($v->before_data,2)."%] [변경후 정상금리 : ".number_format($v->after_data,2)."%]";
                }
                elseif( $v->div_cd == "K" )
                {
                    $content = "|[일괄금리인하][변경]| [변경전 연체금리:".number_format($v->before_data,2)."%] [변경후 연체금리 : ".number_format($v->after_data,2)."%]";
                }
            }
            // 무이자변경
            else if( $v->div_cd == "P" )
            {
                $before_data = explode(",",$v->before_data);
                $after_data  = explode(",",$v->after_data);
                $content     = "|[무이자변경등록][변경]| [이전 무이자일수]:".Func::nvl($before_data[0])." [변경된 무이자일수] : ".Func::nvl($after_data[0])." | [이전 무이자종료일자]:".Func::nvl($before_data[1])." [변경된 무이자종료일자]: ".Func::nvl($after_data[1])."\n";
                $content    .= "| [이전 무이자잔액]:".number_format($before_data[2])." [변경된 무이자잔액]:".number_format($after_data[2]);
            }
            // 대출지급계좌변경
            else if( $v->div_cd == "Q" )
            {
                $before_data = explode(",",$v->before_data);
                $after_data  = explode(",",$v->after_data);
                $content     = "|[대출지급계좌변경][변경]| [변경전송금은행]:".Func::nvl($configArr['bank_cd'][$before_data[0]], $before_data[0])." [변경된송금은행] : ".Func::nvl($configArr['bank_cd'][$after_data[0]], $after_data[0]);
                $content    .= " [변경전송금계좌번호]:".Func::nvl($before_data[1])." [변경된송금계좌번호] : ".Func::nvl($after_data[1]);
            }
            // 거래취소
            else if( $v->div_cd == "C" )
            {
                //  원장변경내역에 거래취소 데이터 쌓는 방식이 2021-08-25일에 변경 됨. -> 기존 데이터를 보여주기 위해서 이렇게 함..
                if( strpos($v->before_data,",") === false )
                {
                    $v->before_data = ",".$v->before_data;
                    $v->after_data  = ",".$v->after_data;
                }

                $before_data = explode(",",$v->before_data);
                $after_data  = explode(",",$v->after_data);
                $content     = "|[거래취소][취소]| [취소거래종류]:".Func::nvl($arr_trade_type[$v->trade_type], $v->trade_type)." [SMS발송여부]:$v->sms_yn \n";
                $content    .= "[변경전값]:$before_data[0] [변경후값] : $after_data[0] [기산일자]:$before_data[1] [취소일자] : $after_data[1]";
            }
            // 고객명변경
            else if( $v->div_cd == "D" )
            {
                $content = "|[고객명변경][변경]| [변경전고객명]:".$v->before_data.", [변경후 고객명] : $v->after_data \n";
            }
            // 가상계좌변경
            else if( $v->div_cd == "A" )
            {
                $before_data = explode(",",$v->before_data);
                $after_data  = explode(",",$v->after_data);
                $content     = "|[가상계좌번호변경][변경]| [변경전은행명]:".Func::nvl($configArr['bank_cd'][$before_data[0]], $before_data[0])." [변경된은행명] : ".Func::nvl($configArr['bank_cd'][$after_data[0]], $after_data[0]);
                $content    .= " [변경전가상계좌번호]:".Func::nvl($before_data[1])." [변경된가상계좌번호] : ".Func::nvl($after_data[1]);
            }
            // 납입예정일변경(상환일)
            else if( $v->div_cd == "L" )
            {
                $content     = "|[납입예정일][변경]| [변경전납입예정일]:".$v->before_data." [변경후납입예정일] : ".$v->after_data." ";
                if(isset($v->memo))
                {
                    $content.= "[변경메모 : ".$v->memo."]";
                }
            }
            // 납입예정금액변경
            else if( $v->div_cd == "M" )
            {
                $content     = "|[납입예정금액][변경]| [변경전납입예정금액]:".$v->before_data." [변경후납입예정금액] : ".$v->after_data." ";
                if(isset($v->memo))
                {
                    $content.= "[변경메모 : ".$v->memo."]";
                }
            }
            // 이외 나머지
            else
            {
                $content = "";
            }

            $v->content = $content;
            $v->div_cd  = Func::nvl($array_work_div[$v->div_cd],$v->div_cd );

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
     * 원장변경내역 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function wonjangExcel(Request $request)
    {
        if( !Func::funcCheckPermit("L022") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }
        
        $prcStatus = Vars::$arrayComplainPrcStatus;

        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $list           = $this->setDataList($request);
        $param          = $request->all();
        $down_div       = $request->down_div;
        $array_user_id  = Func::getUserId();
        $array_user_nm  = array_flip($array_user_id);
        $array_work_div = Vars::$arrayWonjangHisCd;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        // 기본쿼리
        $HIST = DB::TABLE("WONJANG_CHG_HIST")
                    ->JOIN("CUST_INFO ci", "cust_info_no", "=", "ci.no")
                    ->JOIN("LOAN_INFO li", "loan_info_no", "=", "li.no")
                    ->SELECT("WONJANG_CHG_HIST.*", "ci.name", "ci.ssn", "li.manager_code");

        if( $request->tabsSelect=="ALL" )
        {
        }
        // 금리인하
        else if( $request->tabsSelect=="RATE" )
        {
            $HIST->WHEREIN("WONJANG_CHG_HIST.DIV_CD",['E', 'F']);
        }
        // 일괄금리인하
        else if( $request->tabsSelect=="RATE_ALL" )
        {
            $HIST->WHEREIN("WONJANG_CHG_HIST.DIV_CD",['J', 'K']);
        }
        // 대출송금액변경
        else if( $request->tabsSelect=="MNY" )
        {
            $HIST->WHEREIN("WONJANG_CHG_HIST.DIV_CD",['G', 'H']);
        }
        // 가상계좌번호변경
        else if( $request->tabsSelect=="VIR" )
        {
            $HIST->WHEREIN("WONJANG_CHG_HIST.DIV_CD",['A', 'B']);
        }
        else if( $request->tabsSelect!="" )
        {
            $HIST->WHERE("WONJANG_CHG_HIST.DIV_CD", $request->tabsSelect);
        }

        if(isset($param['searchDetail']) && isset($param['searchString']) && $param['searchDetail'] == "worker_nm")
        {
            $HIST->WHERE("WONJANG_CHG_HIST.worker_id", Func::nvl($array_user_nm[$param['searchString']], $param['searchString']));

            unset($param['searchDetail'], $param['searchString']);
        }

        unset($param['tabSelectNm'] ,$param['tabsSelect']);
        $HIST = $list->getListQuery('WONJANG_CHG_HIST', 'main', $HIST, $param);

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($HIST, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($HIST);
        $file_name    = "원장변경내역_".date("YmdHis").'_'.Auth::id().'.xlsx';
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

        $rslt = $HIST->GET();
        $rslt = Func::chungDec(["WONJANG_CHG_HIST","CUST_INFO","LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        // 엑셀 헤더
		$excel_header = array('고객번호','계약번호','고객명','생년월일','관리지점','작업명','결재시 계약상태','변경내용','작업자','작업자소속','작업일시',);
        $excel_data = [];
        
        // 뷰단 데이터 정리.
        $configArr           = Func::getConfigArr();
        $array_branch_code   = Func::getBranch();
        $getStatus           = Vars::$arrayContractSta;
        $arr_trade_type      = $configArr['trade_in_type'];
        foreach( $configArr['trade_out_type'] as $key => $v )
        {
            $arr_trade_type[$key] = $v;
        }
        $arr_trade_type['Z1'] = "증명발급비";

        foreach ($rslt as $v)
        {
            $v->manager_code_nm   = Func::nvl($array_branch_code[$v->manager_code],$v->manager_code );
            $v->loan_status       = Func::nvl($getStatus[$v->loan_status],$v->loan_status );
            $v->worker_nm         = Func::getArrayName($array_user_id, $v->worker_id);
            $v->work_time         = Func::dateFormat($v->work_time);
            $v->worker_code_nm    = Func::nvl($array_branch_code[$v->worker_code],$v->worker_code );
            $v->ssn1              = substr($v->ssn,0,6);

            $content = "";

            // 약정일변경
            if( $v->div_cd == "I" )
            {
                $content = "|[약정일변경][변경]| [변경전약정일] : {$v->before_data}일, [변경후약정일] : {$v->after_data}일";
            }
            // 금리변경
            else if( $v->div_cd == "E" || $v->div_cd == "F" )
            {
                if( $v->div_cd == "E" )
                {
                    $content = "|[금리변경][변경]| [변경전 정상금리:".number_format($v->before_data,2)."%] [변경후 정상금리 : ".number_format($v->after_data,2)."%]\n[변경사유 : {$v->memo}]";
                }
                elseif( $v->div_cd == "F" )
                {
                    $content = "|[금리변경][변경]| [변경전 연체금리:".number_format($v->before_data,2)."%] [변경후 연체금리 : ".number_format($v->after_data,2)."%]\n[변경사유 : {$v->memo}]";
                }
            }
            // 일괄금리인하
            else if( $v->div_cd == "J" || $v->div_cd == "K" )
            {
                if( $v->div_cd == "J" )
                {
                    $content = "|[일괄금리인하][변경]| [변경전 정상금리:".number_format($v->before_data,2)."%] [변경후 정상금리 : ".number_format($v->after_data,2)."%]";
                }
                elseif( $v->div_cd == "K" )
                {
                    $content = "|[일괄금리인하][변경]| [변경전 연체금리:".number_format($v->before_data,2)."%] [변경후 연체금리 : ".number_format($v->after_data,2)."%]";
                }
            }
            // 무이자변경
            else if( $v->div_cd == "P" )
            {
                $before_data = explode(",",$v->before_data);
                $after_data  = explode(",",$v->after_data);
                $content     = "|[무이자변경등록][변경]| [이전 무이자일수]:$before_data[0] [변경된 무이자일수] : $after_data[0] | [이전 무이자종료일자]:$before_data[1] [변경된 무이자종료일자]: $after_data[1]\n";
                $content    .= "| [이전 무이자잔액]:".number_format($before_data[2])." [변경된 무이자잔액]:".number_format($after_data[2]);
            }
            // 대출지급계좌변경
            else if( $v->div_cd == "Q" )
            {
                $before_data = explode(",",$v->before_data);
                $after_data  = explode(",",$v->after_data);
                $content     = "|[대출지급계좌변경][변경]| [변경전송금은행]:".Func::nvl($configArr['bank_cd'][$before_data[0]], $before_data[0])." [변경된송금은행] : ".Func::nvl($configArr['bank_cd'][$after_data[0]], $after_data[0]);
                $content    .= " [변경전송금계좌번호]:".Func::nvl($before_data[1], '')." [변경된송금계좌번호] : ".Func::nvl($after_data[1], '');
            }
            // 거래취소
            else if( $v->div_cd == "C" )
            {
                //  원장변경내역에 거래취소 데이터 쌓는 방식이 2021-08-25일에 변경 됨. -> 기존 데이터를 보여주기 위해서 이렇게 함..
                if( strpos($v->before_data,",") === false )
                {
                    $v->before_data = ",".$v->before_data;
                    $v->after_data  = ",".$v->after_data;
                }

                $before_data = explode(",",$v->before_data);
                $after_data  = explode(",",$v->after_data);
                $content     = "|[거래취소][취소]| [취소거래종류]:".Func::nvl($arr_trade_type[$v->trade_type], $v->trade_type)." [SMS발송여부]:$v->sms_yn \n";
                $content    .= "[변경전값]:$before_data[0] [변경후값] : $after_data[0] [기산일자]:$before_data[1] [취소일자] : $after_data[1]";
            }
            // 고객명변경
            else if( $v->div_cd == "D" )
            {
                $content = "|[고객명변경][변경]| [변경전고객명]:".$v->before_data.", [변경후 고객명] : $v->after_data \n";
            }
            // 가상계좌변경
            else if( $v->div_cd == "A" )
            {
                $before_data = explode(",",$v->before_data);
                $after_data  = explode(",",$v->after_data);
                $content     = "|[가상계좌번호변경][변경]| [변경전은행명]:".Func::nvl($configArr['bank_cd'][$before_data[0]], $before_data[0])." [변경된은행명] : ".Func::nvl($configArr['bank_cd'][$after_data[0]], $after_data[0]);
                $content    .= " [변경전가상계좌번호]:$before_data[1] [변경된가상계좌번호] : $after_data[1]";
            }
            // 납입예정일변경(상환일)
            else if( $v->div_cd == "L" )
            {
                $content     = "|[납입예정일][변경]| [변경전납입예정일]:".$v->before_data." [변경후납입예정일] : ".$v->after_data." ";
                if(isset($v->memo))
                {
                    $content.= "[변경메모 : ".$v->memo."]";
                }
            }
            // 납입예정금액변경
            else if( $v->div_cd == "M" )
            {
                $content     = "|[납입예정금액][변경]| [변경전납입예정금액]:".$v->before_data." [변경후납입예정금액] : ".$v->after_data." ";
                if(isset($v->memo))
                {
                    $content.= "[변경메모 : ".$v->memo."]";
                }
            }
            // 이외 나머지
            else
            {
                $content = "";
            }

            $v->content = $content;
            $v->div_cd  = Func::nvl($array_work_div[$v->div_cd],$v->div_cd );

            $array_data = Array(
                Func::addCi($v->cust_info_no),
                $v->loan_info_no,
                $v->name,
                $v->ssn1,
                $v->manager_code_nm,
                $v->div_cd,
                $v->loan_status,
                $v->content,
                $v->worker_nm,
                $v->worker_code_nm,
                $v->work_time,
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
            
            if($excel_down_div == "A"){
                ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div); 
            }
        }
        else
        {
           $array_result['result']    = 'N';
           $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }
        return $array_result;
    }
}




