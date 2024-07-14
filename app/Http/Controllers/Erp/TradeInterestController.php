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

class TradeInterestController extends Controller
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
     * 결산명세서 공통 세팅 내용
     *
     * @param  request
     * @return dataList
     */
    private function setDataListInterest(Request $request)
    {
        $array_conf_code    = Func::getConfigArr();

        $list = new DataList(Array("listName"=>"tradeinterest","listAction"=>'/'.$request->path()));
        if(!isset($request->tabs))
        {
            $request->tabs = "ALL";
        }
        
        if( Func::funcCheckPermit("E001") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/erp/tradeinterestexcel', 'form_tradeinterest')", "btn-success");
        }
        
        $list->setSearchDate('기준일선택', Array('info_date'=>'기준일'), 'searchDt', 'N', 'Y', date("Y-m-d"), date("Y-m-d"), 'info_date');
        $list->setSearchDate('날짜검색', Array('cm.contract_date'=>'계약일', 'cm.contract_end_date'=>'만기일', 'cm.last_trade_date'=>'최근거래일', 'cm.return_date'=>'차기수익지급일'), 'searchDt2', 'Y', 'N');
        $list->setRangeSearchDetail(Array('cm.balance'=>'투자잔액'),'','','단위(원)');

        $list->setSearchDetail(Array( 
            'lu.nick_name'          =>'투자자명', 
            'cm.investor_no'        => '투자자번호',
            'c.name'                =>'차입자명',
            'investor_no-inv_seq'   => '채권번호', 
        ));

        return $list;
    }

    /**
      * 결산명세서
      *
      * @param  \Illuminate\Http\Request  $request
      * @return view
      */
    public function tradeInterest(Request $request)
    {
        $list   = $this->setDataListInterest($request);

        $list->setlistTitleCommon(Array
        (
            'investor_no_inv_seq'       => Array('채권번호', 0, '', 'center', '', ''),
            'cust_name'                 => Array('차입자명', 1, '', 'center', '', 'c.name'),
            'name'                      => Array('투자자명', 0, '', 'center', '', 'lu.name'),
            'ssn'                       => Array('투자자주민등록번호', 0, '', 'center', '', 'lu.ssn'),
            'status'                    => Array('상태', 0, '', 'center', '', 'cm.status'),
            'pro_cd'                    => Array('상품명', 0, '', 'center', '', 'cm.pro_cd'),
            'contract_date'             => Array('계약일', 0, '', 'center', '', 'cm.contract_date'),
            'contract_end_date'         => Array('만기일', 0, '', 'center', '', 'cm.contract_end_date'),
            'balance'                   => Array('투자잔액', 1, '', 'center', '', 'cm.balance'),
            'return_date'               => Array('차기수익지급일', 0, '', 'center', '', 'cm.return_date'),
            'return_money'              => Array('차기수익지급금', 0, '', 'center', '', 'cm.return_money'),
        ));

        return view('erp.tradeInterest')->with('result', $list->getList());
    }

    /**
     * 결산명세서 데이터리스트
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function tradeInterestList(Request $request)
    {
        $request->isDebug = true;

        if( $request->searchDt=="" || $request->searchDtString=="")
        {
            return json_encode(['result'=>'0','msg'=>'필수값 미입력 - 기준일을 선택해 주세요.']);
        }

        $list   = $this->setDataListInterest($request);
        $param  = $request->all();

        if( !$param['listOrder'] || !$param['listOrderAsc'] )
        {
            $param['listOrder'] = "cm.cust_info_no";
            $param['listOrderAsc'] = "desc";
        }

        if( $request->searchDtStringEnd=="" )
        {
            $param['searchDtStringEnd'] = $request->searchDtString;
        }

        // 날짜형식변환
        $search_date = str_replace("-", "", $request->searchDtString);

        // 검색 숫자 유효성 검사
        if (isset( $param['searchDetail']) && !empty($param['searchString']) && !is_numeric($param['searchString'])
        && (
            $param['searchDetail']=='cm.loan_info_no'  // 계약번호
            )
        ) 
        {
            $r["result"] = "0";
            $r["msg"] = "숫자만 입력 가능합니다.";
            return $r;
        } 
    
        // 당일
        if($param['searchDtString'] == date("Y-m-d"))
        {
            unset($param['searchDt'], $param['searchDtString'], $param['searchDtStringEnd']);
            // 기본쿼리
            $LOAN = DB::TABLE("loan_info as cm");
            $LOAN->JOIN("cust_info as c", "c.no", "=", "cm.cust_info_no");
            $LOAN->JOIN("loan_usr_info as lu", "cm.loan_usr_info_no", "=", "lu.no");
            $LOAN->SELECT("cm.*");
            $LOAN->ADDSELECT("c.name cust_name", "c.ssn", "lu.name", "lu.ssn");
            $LOAN->WHERENOTIN("cm.status", ['N','E']);
            $LOAN->WHERERAW("(fullpay_date = '".$search_date."' or fullpay_date is null or fullpay_date = '')");

            // 당일자 계약번호 때문에 오류 방지
            if($param['listOrder']=='cm.loan_info_no')
            {
                $param['listOrder'] = 'cm.no';
            }
            if($param['searchDetail']=='cm.loan_info_no')
            {
                $param['searchDetail'] = 'cm.no';
            }

            // 생년월일검색
            if(isset( $param['searchDetail']) && $param['searchDetail']=='birth' && !empty($param['searchString']) )
            {
                $searchString = $param['searchString'];

                $LOAN = Func::encLikeSearch($LOAN, 'c.ssn', $searchString, 'all', 7);
                
                unset($param['searchString']);
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
                                $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                            }
                            // 문자열있는 채권번호로 검색(ex. H5-1)
                            else 
                            {
                                $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])
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
                            $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0]);          
                        }
                        // 채권번호로 검색(ex. 5-1)
                        else 
                        {
                            $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
                        }
                    }
                }

                unset($param['searchString']);
            }

            if(isset( $param['searchDetail']) && $param['searchDetail']=='cm.investor_no' && !empty($param['searchString']) )
            {
                $pattern = '/\d+/';
                // 패턴과 일치하면 테이터 제거
                if(!preg_match($pattern, $param['searchString'])) {
                    unset($param['searchString']);
                }
            }

            if(isset( $param['searchDetail']) && $param['searchDetail']=='lu.nick_name' && !empty($param['searchString']) )
            {
                $LOAN = $LOAN->where('lu.nick_name', 'like','%'.$param['searchString'].'%');
                unset($param['searchString']);
            }

            //구간 검색 시 숫자 아닌 값 입력되면 데이터 제거
            if(isset($param['rangeSearchDetail']) && (!empty($param['sRangeSearchString']) || !empty($param['eRangeSearchString']) )) {
                $pattern = '/\d+/';
                if(!preg_match($pattern, $param['sRangeSearchString']) || !preg_match($pattern, $param['eRangeSearchString'])) {
                    unset($param['rangeSearchDetail']);
                }
            }

            // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
            if(empty( $param['searchDetail']) && !empty($param['searchString']) )
            {
                unset($param['searchString']);
            }

            $LOAN = $list->getListQuery("loan_info", 'main', $LOAN, $param);
        }
        else
        {
            // 기본쿼리
            $LOAN = DB::table("close_data as cm");
            $LOAN->join("cust_info as c", "c.no", "=", "cm.cust_info_no");
            $LOAN->join("loan_info as l", "l.no", "=", "cm.loan_info_no");
            $LOAN->JOIN("loan_usr_info as lu", "cm.loan_usr_info_no", "=", "lu.no");
            $LOAN->select("cm.*", "l.no", "l.investor_type");
            $LOAN->ADDSELECT("c.name cust_name", "c.ssn", "lu.name", "lu.ssn", "l.manager_code");
            $LOAN->WHERENOTIN("cm.status", ['N','E']);
            $LOAN->WHERERAW("(cm.fullpay_date = '".$search_date."' or cm.fullpay_date is null or cm.fullpay_date = '')");

            // 생년월일검색
            if(isset( $param['searchDetail']) && $param['searchDetail']=='birth' && !empty($param['searchString']) )
            {
                $searchString = $param['searchString'];

                $LOAN = Func::encLikeSearch($LOAN, 'c.ssn', $searchString, 'all', 7);
                
                unset($param['searchString']);
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
                                $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                            }
                            // 문자열있는 채권번호로 검색(ex. H5-1)
                            else 
                            {
                                $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])
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
                            $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0]);          
                        }
                        // 채권번호로 검색(ex. 5-1)
                        else 
                        {
                            $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
                        }
                    }
                }

                unset($param['searchString']);
            }

            if(isset( $param['searchDetail']) && $param['searchDetail']=='cm.investor_no' && !empty($param['searchString']) )
            {
                if(strstr($param['searchString'], '-')) 
                {
                    unset($param['searchString']);
                }
            }

            // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
            if(empty( $param['searchDetail']) && !empty($param['searchString']) )
            {
                unset($param['searchString']);
            }


            $LOAN = $list->getListQuery("close_data", 'main', $LOAN, $param);
        }

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN, $request->page, $request->listLimit, 10, $request->listName);

        // 뷰단 데이터 정리.
        $getStatus          = Vars::$arrayContractSta;
        $array_conf_code    = Func::getConfigArr();
        $arrBranch          = Func::getBranch();
        $arrManager         = Func::getUserList();

        $query              = Func::printQuery($LOAN);
        // log::info($query);

        $cnt = 0;
        $rslt = $LOAN->GET();
        // $rslt = Func::chungDec(["CLOSE_DATA","CUST_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        foreach( $rslt as $v )
        {
            // $v->interest_sum        = $v->interest + $v->delay_interest + $v->lack_delay_money + $v->misu_money + $v->settle_interest;
            $v->investor_no_inv_seq = $v->investor_type.$v->investor_no.'-'.$v->inv_seq;
            $v->onclick             = 'javascript:window.open("/erp/custpop?cust_info_no='.$v->cust_info_no.'&no='.$v->no.'","msgpop","width=2000, height=1000, scrollbars=yes")';
            $v->line_style          = 'cursor: pointer;';

            $v->cust_name           = Func::chungDecOne($v->cust_name);
            $v->name                = Func::chungDecOne($v->name);
            $v->ssn                 = Func::ssnFormat(Func::chungDecOne($v->ssn));
            $v->status              = Func::getInvStatus($v->status, true);
            $v->pro_cd              = Func::getArrayName($array_conf_code['pro_cd'], $v->pro_cd);
            
            $v->contract_date       = Func::dateFormat($v->contract_date);
            $v->contract_end_date   = Func::dateFormat($v->contract_end_date);
            $v->balance             = Func::numberformat($v->balance);
            $v->interest_sum        = Func::numberformat($v->interest_sum);
            $v->last_trade_date     = $v->last_trade_date ?? $v->last_in_date ?? '';
            $v->last_trade_date     = Func::dateFormat($v->last_trade_date);
            $v->return_date         = Func::dateFormat($v->return_date);
            $v->return_money        = number_format($v->return_money);
            
            $r['v'][] = $v;
            $cnt ++;
        }

        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path());
        $r['result'] = 1;
        $r['txt'] = $cnt;

        return json_encode($r);
    }

    // 결산명세서 엑셀출력
    public function tradeinterestExcel(Request $request)
    {
        if( !Func::funcCheckPermit("E001") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }

        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $list           = $this->setDataListInterest($request);
        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        $arrayHeader = ExcelFunc::getExcelHeader("/erp/tradeinterest");
        $selectHeaders = array_flip(json_decode($param['excelHeaders']));

         // 엑셀 select 기능
         // 엑셀 항목별 분리 2024.01.30
         $excel_header = $addJoinTable = $addSelect = $selectDivHeaders = array();
         $custArray = $loanArray = $tradeArray = [];

         // 엑셀 마스터 검색(E036) 권한자
        if( Func::funcCheckPermit("E036"))
        {
            $seq = $select_seq = $divIndex = 0;
            // 엑셀 - 헤더
            foreach($arrayHeader as $div => $arrInfo)
            {
                foreach( $arrInfo['info'] as $idx => $detailInfo )
                {
                    // 선택한 항목값이 있는경우만
                    if(isset($selectHeaders[$seq]))
                    {
                        $selectDivHeaders[$divIndex][$idx] = $seq;
                        
                        $excel_header[] = $detailInfo['header'];
                        
                        if (empty($detailInfo['style'])) {
                            $excel_style[]=null;
                        } else {
                            $excel_style[] = array($detailInfo['style']=>ExcelFunc::getColumnLetter($select_seq));
                        }

                        // join 대상테이블에 포함되어있는지 여부 체크
                        if(!in_array($detailInfo['table'], $addJoinTable)) $addJoinTable[] = $detailInfo['table'];
                        // 추출대상 Column 값이 있는경우 추가
                        if(isset($detailInfo['column']) && $detailInfo['column']) $addSelect[$detailInfo['table']][] = $detailInfo['column'];
                        $select_seq++;
                    }
                    $seq++;
                }
                $divIndex++;
            }
            // 날짜/숫자 서식 분리
            foreach ($excel_style as $key => $value) 
            {
                if(!empty($value['datetime'])){
                    $excel_style_datetime[] = $value['datetime'];
                }
                if(!empty($value['number'])){
                    $excel_style_number[] = $value['number'];
                }
            }
        }
        // 기본
        else
        {
            // 엑셀 - 헤더
            foreach($arrayHeader as $headerIdx => $headerTitle)
            {            
                // 선택한 항목값이 있는경우만
                if(isset($selectHeaders[$headerIdx])) $excel_header[] = $headerTitle;
            }
        }

        if( !$param['listOrder'] || !$param['listOrderAsc'] )
        {
            $param['listOrder'] = "cm.cust_info_no";
            $param['listOrderAsc'] = "desc";
        }

        if( $request->searchDtStringEnd=="" )
        {
            $param['searchDtStringEnd'] = $request->searchDtString;
        }

        // 날짜형식변환
        $search_date = str_replace("-", "", $request->searchDtString);

        $table_Select = '';
        
        if($param['searchDtString'] == date("Y-m-d"))
        {   
            $table_Select = 'loan_info';
            unset($param['searchDt'], $param['searchDtString'], $param['searchDtStringEnd']);
            // 기본쿼리-당일
            $LOAN = DB::TABLE("loan_info as cm");
            $LOAN->JOIN("cust_info as c", "c.no", "=", "cm.cust_info_no");
            $LOAN->JOIN("loan_usr_info as lu", "cm.loan_usr_info_no", "=", "lu.no");
            $LOAN->SELECT("cm.*");
            $LOAN->ADDSELECT("c.name cust_name", "c.ssn", "lu.name", "lu.ssn");
            $LOAN->WHERENOTIN("cm.status", ['N','E']);
            $LOAN->WHERERAW("(fullpay_date = '".$search_date."' or fullpay_date is null or fullpay_date = '')");
            
            // 당일자 계약번호 때문에 오류 방지
            if($param['listOrder']=='cm.loan_info_no')
            {
                $param['listOrder'] = 'cm.no';
            }
            if($param['searchDetail']=='cm.loan_info_no')
            {
                $param['searchDetail'] = 'cm.no';
            }
        }
        else 
        {
            $table_Select = 'close_data';

            // 기본쿼리-마감
            $LOAN = DB::table("close_data as cm");
            $LOAN->join("cust_info as c", "c.no", "=", "cm.cust_info_no");
            $LOAN->join("loan_info as l", "l.no", "=", "cm.loan_info_no");
            $LOAN->JOIN("loan_usr_info as lu", "cm.loan_usr_info_no", "=", "lu.no");
            $LOAN->select("cm.*", "l.no", "l.investor_type");
            $LOAN->ADDSELECT("c.name cust_name", "c.ssn", "lu.name", "lu.ssn", "l.manager_code");
            $LOAN->WHERENOTIN("cm.status", ['N','E']);
            $LOAN->WHERERAW("(cm.fullpay_date = '".$search_date."' or cm.fullpay_date is null or cm.fullpay_date = '')");
        }

        // 생년월일검색
        if(isset( $param['searchDetail']) && $param['searchDetail']=='birth' && !empty($param['searchString']) )
        {
            $searchString = $param['searchString'];

            $LOAN = Func::encLikeSearch($LOAN, 'c.ssn', $searchString, 'all', 7);
            
            unset($param['searchString']);
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
                                $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                            }
                            // 문자열있는 채권번호로 검색(ex. H5-1)
                            else 
                            {
                                $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])
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
                            $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0]);          
                        }
                        // 채권번호로 검색(ex. 5-1)
                        else 
                        {
                            $LOAN = $LOAN->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
                        }
                    }
                }
                
                unset($param['searchString']);
            }

            if(isset( $param['searchDetail']) && $param['searchDetail']=='cm.investor_no' && !empty($param['searchString']) )
            {
                $pattern = '/\d+/';
                // 패턴과 일치하면 테이터 제거
                if(!preg_match($pattern, $param['searchString'])) {
                    unset($param['searchString']);
                }
            }

            if(isset( $param['searchDetail']) && $param['searchDetail']=='lu.nick_name' && !empty($param['searchString']) )
            {
                $LOAN = $LOAN->where('lu.nick_name', 'like','%'.$param['searchString'].'%');
                unset($param['searchString']);
            }

            //구간 검색 시 숫자 아닌 값 입력되면 데이터 제거
            if(isset($param['rangeSearchDetail']) && (!empty($param['sRangeSearchString']) || !empty($param['eRangeSearchString']) )) {
                $pattern = '/\d+/';
                if(!preg_match($pattern, $param['sRangeSearchString']) || !preg_match($pattern, $param['eRangeSearchString'])) {
                    unset($param['rangeSearchDetail']);
                }
            }

            // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
            if(empty( $param['searchDetail']) && !empty($param['searchString']) )
            {
                unset($param['searchString']);
            }

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($LOAN);
        
        $file_name    = "결산명세서(기준일".$search_date.")_".date("YmdHis").'_'.Auth::id().".xlsx";
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

        // 선택엑셀 데이터 추가
        if(isset($addJoinTable) && sizeof($addJoinTable) > 0)
        {
            foreach($addJoinTable as $table)
            { 
                if($table!="loan_info" && $table!="cust_info" && $table!="cust_info_extra")
                {
                    // 보증인
                    if($table=="loan_info_guarantor")
                    {
                        $guarantor = DB::table(DB::Raw("(select *, rank() over(partition by loan_info_no order by (case when status = 'Y' then 0 else 1 end) asc, no) from loan_info_guarantor where save_status = 'Y' order by loan_info_no, no) as foo"))
                            ->select('*')
                            ->where('rank',1);
                        $LOAN->leftjoinSub($guarantor, 'loan_info_guarantor', function($join) {
                            $join->on('l.no', '=', 'loan_info_guarantor.loan_info_no');
                        });
    
                    }
    
                    // borrow_comp
                    if($table=="borrow")
                    {
                        $results = DB::table('borrow')
                                    ->select('borrow.loan_info_no', 'borrow_comp.bank_name')
                                    ->leftJoin('borrow_comp', 'borrow.borrow_comp_no', '=', 'borrow_comp.no')
                                    ->whereIn('borrow.no', function($query) {
                                        $query->select(DB::raw('max(no)'))
                                            ->from('borrow')
                                            ->where('save_status', '=', 'Y')
                                            ->groupBy('loan_info_no');
                                    })
                                    ->where('borrow.status', '=', 'S')
                                    ->get();
                        $results = Func::chungDec(["borrow","borrow_comp"], $results);	// CHUNG DATABASE DECRYPT
    
                        foreach ($results as $result) 
                        {
                            $loanArray[$result->loan_info_no] = $result->bank_name;
                        }
                    }
                    
                    // vir_acct
                    if($table=="vir_acct")
                    {
                        $results = DB::table('vir_acct')
                                    ->select('vir_acct_ssn', 'cust_info_no')
                                    ->whereRaw("no in (select max(no) from vir_acct where save_status = 'Y' group by cust_info_no)")
                                    ->get();
                        $results = Func::chungDec(["VIR_ACCT"], $results);	// CHUNG DATABASE DECRYPT
    
                        foreach ($results as $result) 
                        {
                            $custArray[$result->cust_info_no] = $result->vir_acct_ssn ?? '';
                        }
                    }
                        
                    // loan_info_trade
                    if($table=="loan_info_trade")
                    {
                        $results = DB::table('loan_info_trade')
                                    ->select('loan_info_no',
                                        DB::raw("sum(case when trade_div = 'I' and trade_money > 0 then 1 else 0 end) as total_return_cnt"),
                                        DB::raw("sum(case when trade_div = 'I' and trade_money > 0 then coalesce(return_interest_sum,0) else 0 end) as total_return_interest")
                                    )
                                    ->where('save_status', 'Y')
                                    ->groupBy('loan_info_no')
                                    ->get();
                        $results = Func::chungDec(["loan_info_trade"], $results);	// CHUNG DATABASE DECRYPT
    
                        foreach ($results as $result) 
                        {
                            $tradeArray[$result->loan_info_no]['total_return_cnt']      = $result->total_return_cnt ?? 0;
                            $tradeArray[$result->loan_info_no]['total_return_interest'] = $result->total_return_interest ?? 0;
                        }
                    }
                }

                // loan_settle, loan_settle_plan, loan_irl, loan_ccrs 테이블의 column 은 Subquery 실행시 이미 검색대상 컬럼 추가됨.
                if($table!="loan_info" && $table!="cust_info" && $table!="cust_info_extra" && $table!="borrow" && $table!="vir_acct" && $table!="loan_info_trade")
                {   
                    // 검색대상 컬럼 추가
                    if(isset($addSelect[$table]) && sizeof($addSelect[$table]) > 0)
                    {
                        for($columnIdx=0;$columnIdx<sizeof($addSelect[$table]);$columnIdx++)
                        {
                            $LOAN->addSelect($addSelect[$table][$columnIdx]);
                        }
                    }
                }
            }
        }
         //쿼리 만드는 부분;
        $LOAN = $list->getListQuery($table_Select, 'main', $LOAN, $param);

        Log::info("#########쿼리 확인 :".Func::printQuery($LOAN));

        //쿼리 날리는 부분;
        $rslt = $LOAN->GET();
        
        
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","CUST_INFO_EXTRA","PRODUCT_MANAGE"], $rslt);	// CHUNG DATABASE DECRYPT


        // 뷰단 데이터 정리.
        $getStatus       = Vars::$arrayContractSta;
        $configArr         = Func::getConfigArr();
        $array_conf_code = Func::getConfigArr();
        $arrBranch       = Func::getBranch();
        $arrManager      = Func::getUserList();
        $getSaleStatus = array('A'=>'매각요청', 'Y'=>'매각완료', 'N'=>'매각취소', 'X'=>'비매각대상');
        $arrayBranch   = Func::getBranch();
        $board_count = 1;

       

        foreach ($rslt as $v)
        {   
            unset($array_data);

            $v->loan_info_no    = $v->no ?? $v->loan_info_no;
            $v->last_trade_date = $v->last_trade_date ?? $v->last_in_date;
            $v->manager_code    = Func::nvl($arrBranch[$v->manager_code], $v->manager_code) ?? 'SYSTEM';
            $v->lack_money      = $v->lack_interest + $v->lack_delay_interest + $v->lack_delay_money;

            // 엑셀 마스터 검색(E036) 권한자
            if( Func::funcCheckPermit("E036"))
            {
                // 엑셀 항목별 분리 2024.01.30
                // 1. 기본정보
                if(isset($selectDivHeaders[0][0])) $array_data[] = $v->convert_c_no;              //구회원
                if(isset($selectDivHeaders[0][1])) $array_data[] = $v->convert_l_no;              //구계약
                if(isset($selectDivHeaders[0][2])) $array_data[] = Func::addCi($v->cust_info_no); //회원no
                if(isset($selectDivHeaders[0][3])) $array_data[] = $v->loan_info_no;              //계약no
                if(isset($selectDivHeaders[0][4])) $array_data[] = $v->name;                      //성명
                if(isset($selectDivHeaders[0][5])) $array_data[] = Func::ssnFormat($v->ssn);      //주민등록번호
                if(isset($selectDivHeaders[0][6])) $array_data[] = func::getAge($v->ssn);         //연령
                if(isset($selectDivHeaders[0][7])) $array_data[] = Func::getArrayName(Func::getJobCd(),(substr($v->job_cd, 0, 1)));   //자격구분
                if( $v->addr11 )
                {
                    $tmp = explode(" ", $v->addr11);
                    $local           = str_replace("광역시","",str_replace("특별시","",(isset($tmp[0]) ? $tmp[0]:'')))." ".(isset($tmp[1]) ? $tmp[1]:'');   
                }
                else
                {
                    $local = '';
                }
                if(isset($selectDivHeaders[0][8])) $array_data[] = $local;                          //지역
                if(isset($selectDivHeaders[0][9])) $array_data[] = $v->zip1;                        //실거주주소 우편번호
                if(isset($selectDivHeaders[0][10])) $array_data[] = $v->addr11;                     //실거주주소
                if(isset($selectDivHeaders[0][11])) $array_data[] = $v->zip2;                       //등본주소 우편번호
                if(isset($selectDivHeaders[0][12])) $array_data[] = $v->addr21;                     //등본주소
                if(isset($selectDivHeaders[0][13])) $array_data[] = ($v->post_send_cd) ? $v->{"zip".$v->post_send_cd} : "";   //우편물수령주소 우편번호
                if(isset($selectDivHeaders[0][14])) $array_data[] = ($v->post_send_cd) ? $v->{"addr".$v->post_send_cd."1"}." ".$v->{"addr".$v->post_send_cd."2"} : "";        //우편물수령주소
                if(isset($selectDivHeaders[0][15])) $array_data[] = $v->com_name;                     //직장명
                if(isset($selectDivHeaders[0][16])) $array_data[] = $v->retire;                       //퇴사여부
                if(isset($selectDivHeaders[0][17])) $array_data[] = $v->addr31;                       //직장주소
                if(isset($selectDivHeaders[0][18])) $array_data[] = $v->addr32;                       //직장상세주소
                if(isset($selectDivHeaders[0][19])) $array_data[] = ($v->ph31) ? $v->ph31."-".$v->ph32."-".$v->ph33.( ($v->ph34)?"(".$v->ph34.")" : "" ) : "";        //직장전화번호
                if(isset($selectDivHeaders[0][20])) $array_data[] = Func::getArrayName($configArr['call_status_cd'], $v->ph2_status); //핸드폰가능여부
                if(isset($selectDivHeaders[0][21])) $array_data[] = ($v->fax11) ? $v->fax11."-".$v->fax12."-".$v->fax13 : "";         //팩스번호
                if(isset($selectDivHeaders[0][22])) $array_data[] = Func::getArrayName($configArr['employ_cd'], $v->com_employ_cd);   //고용형태
                if(isset($selectDivHeaders[0][23])) $array_data[] = Func::getArrayName($configArr['jikmu_cd'], $v->com_jikmu);        //직무형태
                if(isset($selectDivHeaders[0][24])) $array_data[] = $v->stay_year;                                                    //근속년수
                if(isset($selectDivHeaders[0][25])) $array_data[] = Func::getArrayName($configArr['marry_status_cd'], $v->marry_status_cd);//결혼
                if(isset($selectDivHeaders[0][26])) $array_data[] = Func::getArrayName($configArr['house_type_cd'], $v->house_type_cd);    //주거형태
                                         

                // 2. 보증인정보
                if(isset($selectDivHeaders[1][0])) $array_data[] = Func::decrypt($v->g_name, 'ENC_KEY_SOL');         //보증인이름
                if(isset($selectDivHeaders[1][1])) $array_data[] = Func::decrypt($v->g_ssn, 'ENC_KEY_SOL');          //보증인생년월일
                if(!empty($v->g_addr11))                                                                        
                {
                    $g_addr11 = Func::decrypt($v->g_addr11, 'ENC_KEY_SOL');
                    if( $g_addr11 )
                    {
                        $tmp = explode(" ", $g_addr11);
                        $g_local           = str_replace("광역시","",str_replace("특별시","",(isset($tmp[0]) ? $tmp[0]:'')))." ".(isset($tmp[1]) ? $tmp[1]:'');   
                    }
                    else
                    {
                        $g_local = '';
                    }
                }
                else
                {
                    $g_local = '';
                }         
                if(isset($selectDivHeaders[1][2])) $array_data[] = $g_local;                                         //보증인지역
                if(isset($selectDivHeaders[1][3])) $array_data[] = Func::decrypt($v->g_zip1, 'ENC_KEY_SOL');         //보증인실거주주소우편번호
                if(isset($selectDivHeaders[1][4])) $array_data[] = Func::decrypt($v->g_addr11, 'ENC_KEY_SOL')." ".Func::decrypt($v->g_addr12, 'ENC_KEY_SOL');    //보증인실거주주소
                if(isset($selectDivHeaders[1][5])) $array_data[] = Func::decrypt($v->g_zip2, 'ENC_KEY_SOL');         //보증인등본주소우편번호
                if(isset($selectDivHeaders[1][6])) $array_data[] = Func::decrypt($v->g_addr21, 'ENC_KEY_SOL')." ".Func::decrypt($v->g_addr22, 'ENC_KEY_SOL');        //보증인등본주소
                if(isset($selectDivHeaders[1][7])) $array_data[] = ($v->g_post_send_cd) ? Func::decrypt($v->{"g_zip".$v->g_post_send_cd}, 'ENC_KEY_SOL') : "";   //보증인우편물수령주소 우편번호
                if(isset($selectDivHeaders[1][8])) $array_data[] = ($v->g_post_send_cd) ? Func::decrypt($v->{"g_addr".$v->g_post_send_cd."1"}, 'ENC_KEY_SOL')." ".Func::decrypt($v->{"g_addr".$v->g_post_send_cd."2"}, 'ENC_KEY_SOL') : "";   //보증인우편물수령주소
                if(isset($selectDivHeaders[1][9])) $array_data[] = Func::decrypt($v->g_com_name, 'ENC_KEY_SOL');   //보증인직장명
                if(isset($selectDivHeaders[1][10])) $array_data[] = Func::decrypt($v->g_addr31, 'ENC_KEY_SOL');     //보증인직장주소
                if(isset($selectDivHeaders[1][11])) $array_data[] = Func::decrypt($v->g_addr32, 'ENC_KEY_SOL');     //보증인직장상세주소
                
                //보증인직장전화번호
                if(isset($selectDivHeaders[1][12])) $array_data[] = ($v->ph31) ? Func::decrypt($v->g_ph31, 'ENC_KEY_SOL')."-".Func::decrypt($v->g_ph32, 'ENC_KEY_SOL')."-".Func::decrypt($v->g_ph33, 'ENC_KEY_SOL')."(".Func::decrypt($v->g_ph34, 'ENC_KEY_SOL').")" : "";
                
                if(isset($selectDivHeaders[1][13])) $array_data[] = Func::getArrayName($configArr['call_status_cd'], $v->g_ph2_status);   //보증인핸드폰가능여부

                // 3. 계약정보
                if(isset($selectDivHeaders[2][0])) $array_data[] = (sprintf('%0.3f',$v->loan_rate)."%");                                 //이율        
                if(isset($selectDivHeaders[2][1])) $array_data[] = (sprintf('%0.3f',$v->loan_delay_rate)."%");                           //연체이율
                if(isset($selectDivHeaders[2][2])) $array_data[] = Func::dateFormat($v->contract_date);                                  //계약일
                if(isset($selectDivHeaders[2][3])) $array_data[] = Func::dateFormat($v->contract_end_date);                              //만기일
                if(isset($selectDivHeaders[2][4])) $array_data[] = Func::dateFormat($v->buy_date);                                       //매입일
                if(isset($selectDivHeaders[2][5])) $array_data[] = Func::dateFormat($v->first_loan_date);                                //최초계약일자
                if(isset($selectDivHeaders[2][6])) $array_data[] = ($v->first_loan_money) ? number_format($v->first_loan_money) : 0;     //최초대출
                if(isset($selectDivHeaders[2][7])) $array_data[] = 0;
                if(isset($selectDivHeaders[2][8])) $array_data[] = $v->pro_cd;                                                        //상품
                if(isset($selectDivHeaders[2][9])) $array_data[] = Func::getArrayName($configArr['return_method'], $v->viewing_return_method);//상환방법
                if(isset($selectDivHeaders[2][10])) $array_data[] = Func::dateFormat($v->last_loan_date);                                 //최근대출일

                // 4. 채권정보
                if(isset($selectDivHeaders[3][0])) $array_data[] = Func::getArrayName($configArr['handle_cd'], $v->handle_code);             //취급점
                if(isset($selectDivHeaders[3][1])) $array_data[] = Func::getArrayName($arrayBranch, $v->manager_code);                       //부서
                if(isset($selectDivHeaders[3][2])) $array_data[] = isset($arrManager[$v->manager_id]) ? $arrManager[$v->manager_id]->name : $v->manager_id;  //담당
                if(isset($selectDivHeaders[3][3])) $array_data[] = $v->contract_day; //약정일
                if(isset($selectDivHeaders[3][4])) $array_data[] = Func::getInvStatus($v->status, $v->settle_div_cd);   //상태
                if(isset($selectDivHeaders[3][5])) $array_data[] = Func::getArrayName($configArr['app_type_cd'], $v->loan_type); //유형
                if(isset($selectDivHeaders[3][6])) $array_data[] = 0;
                if(isset($selectDivHeaders[3][7])) $array_data[] = ($v->loan_money) ? number_format($v->loan_money) : 0;
                if(isset($selectDivHeaders[3][8])) $array_data[] = Func::dateFormat($v->return_date);
                if(isset($selectDivHeaders[3][9])) $array_data[] = ($v->return_date_interest) ? number_format($v->return_date_interest) : 0;
                if(isset($selectDivHeaders[3][10])) $array_data[] = Func::dateFormat($v->last_trade_date);
                if(isset($selectDivHeaders[3][11])) $array_data[] = ($v->last_in_money) ? number_format($v->last_in_money) : 0;
                if(isset($selectDivHeaders[3][12])) $array_data[] = ($v->misu_money) ? number_format($v->misu_money) : 0;
                if(isset($selectDivHeaders[3][13])) $array_data[] = ($v->interest_sum) ? number_format($v->interest_sum) : 0;
                if(isset($selectDivHeaders[3][14])) $array_data[] = ($v->balance) ? number_format($v->balance) : 0;               //잔액
                if(isset($selectDivHeaders[3][15])) $array_data[] = ($v->over_money) ? number_format($v->over_money) : 0;         //가수금
                if(isset($selectDivHeaders[3][16])) $array_data[] = isset($custArray[$v->cust_info_no]) ? $custArray[$v->cust_info_no] : '';
                if(isset($selectDivHeaders[3][17])) $array_data[] = date("Y-m-d", strtotime(substr($v->return_date,0,4)."-".substr($v->return_date,4,2)."-".substr($v->return_date,6)."+91 days"));
                if(isset($selectDivHeaders[3][18])) $array_data[] = Func::dateFormat($v->promise_date, 'kor').(($v->promise_hour) ? " ".$v->promise_hour."시".$v->promise_min."분":"");
                if(isset($selectDivHeaders[3][19])) $array_data[] = Func::getArrayName($configArr['manage_rsn_cd'], $v->attribute_manage_cd);
                if(isset($selectDivHeaders[3][20])) $array_data[] = Func::getArrayName($configArr['loan_cat_1_cd'], $v->loan_cat_1_cd);   //채권구분1
                if(isset($selectDivHeaders[3][21])) $array_data[] = Func::getArrayName($configArr['loan_cat_2_cd'], $v->loan_cat_2_cd);   //채권구분2
                if(isset($selectDivHeaders[3][22])) $array_data[] = Func::getArrayName($configArr['loan_cat_1_cd'], $v->g_loan_cat_1_cd); //채권구분1
                if(isset($selectDivHeaders[3][23])) $array_data[] = Func::getArrayName($configArr['loan_cat_2_cd'], $v->g_loan_cat_2_cd); //채권구분2
                if(isset($selectDivHeaders[3][24])) $array_data[] = Func::getArrayName($configArr['person_manage_cd'], $v->person_manage);   //민원관리
                if(isset($selectDivHeaders[3][25])) $array_data[] = '';   //연체일
                if(isset($selectDivHeaders[3][26])) $array_data[] = $v->delay_interest_term;  //연체일2
                if(isset($selectDivHeaders[3][27])) $array_data[] = '';   //최대연체일수
                if(isset($selectDivHeaders[3][28])) $array_data[] = '';   //누적연체일수
                if(isset($selectDivHeaders[3][29])) $array_data[] = ($v->contract_end_date && $v->contract_end_date != '10191014') ? Func::dateTerm($v->contract_end_date, date("Ymd")) : "";             //만기연체일수
                if(isset($selectDivHeaders[3][30])) $array_data[] = isset($tradeArray[$v->loan_info_no]['total_return_cnt']) ? $tradeArray[$v->loan_info_no]['total_return_cnt'] : 0;     //총납입횟수
                if(isset($selectDivHeaders[3][31])) $array_data[] = isset($tradeArray[$v->loan_info_no]['total_return_interest']) ? number_format($tradeArray[$v->loan_info_no]['total_return_interest']) : 0;  //총납입이자
                if(isset($selectDivHeaders[3][32])) $array_data[] = ($v->fullpay_money) ? number_format($v->fullpay_money) : 0;      //총완납금
                if(isset($selectDivHeaders[3][33])) $array_data[] = '';
                if(isset($selectDivHeaders[3][34])) $array_data[] = "우리은행";                // 가상계좌 (은행명)      
                if(isset($selectDivHeaders[3][35])) $array_data[] = 0;    
                
                // 5. 권한부여자
                if(isset($selectDivHeaders[4][0])) $array_data[] = ($v->base_cost) ? number_format($v->base_cost) : 0;
                if(isset($selectDivHeaders[4][1])) $array_data[] = isset($loanArray[$v->loan_info_no]) ? $loanArray[$v->loan_info_no] : '';
                if(isset($selectDivHeaders[4][2])) $array_data[] = '';
                if(isset($selectDivHeaders[4][3])) $array_data[] = $v->buy_corp;
                if(isset($selectDivHeaders[4][4])) $array_data[] = $v->ssn;
                if(isset($selectDivHeaders[4][5])) $array_data[] = $v->ph21."-".$v->ph22."-".$v->ph23;
                if(isset($selectDivHeaders[4][6])) $array_data[] = Func::dateFormat($v->lost_date);
                if(isset($selectDivHeaders[4][7])) $array_data[] = $v->lost_date_memo;
                if(isset($selectDivHeaders[4][8])) $array_data[] = '';
                if(isset($selectDivHeaders[4][9])) $array_data[] = '';
                if(isset($selectDivHeaders[4][10])) $array_data[] = $v->kfb_yn;
            }
            else
            {
                if(isset($selectHeaders[0])) $array_data[] = $board_count;
                if(isset($selectHeaders[1])) $array_data[] = $v->investor_type.$v->investor_no.'-'.$v->inv_seq;
                if(isset($selectHeaders[2])) $array_data[] = Func::chungDecOne($v->cust_name);
                if(isset($selectHeaders[3])) $array_data[] = $v->name;
                if(isset($selectHeaders[4])) $array_data[] = Func::ssnFormat($v->ssn);
                if(isset($selectHeaders[5])) $array_data[] = Func::getInvStatus($v->status);
                if(isset($selectHeaders[6])) $array_data[] = Func::getArrayName($array_conf_code['pro_cd'], $v->pro_cd);
                if(isset($selectHeaders[7])) $array_data[] = Func::dateFormat($v->contract_date);
                if(isset($selectHeaders[8])) $array_data[] = Func::dateFormat($v->contract_end_date);
                if(isset($selectHeaders[9])) $array_data[] = Func::numberformat($v->balance);
                if(isset($selectHeaders[10])) $array_data[] = Func::numberformat($v->interest_sum);
                if(isset($selectHeaders[11])) $array_data[] = Func::dateFormat($v->return_date);
                if(isset($selectHeaders[12])) $array_data[] = number_format($v->return_money);
            }
            
            $record_count++;
            $board_count++;
            $excel_data[] = $array_data;
        }
        
        // 엑셀 익스포트
        $excel_style = ['datetime' => $excel_style_datetime ?? [], 'number' => $excel_style_number ?? []];// 서식지정
        // ExcelFunc::fastexcelExport($excel_data,$excel_header,$origin_filename);
        $_EXCEL = Array();
        $_EXCEL[] = Array(
            "header"    =>  $excel_header ?? [],
            "excel_data"=>  $excel_data ?? [],
            "title"     =>  $file_name ?? [],
            "style"     =>  $excel_style ?? [],
        );
        
        ExcelFunc::downExcelSheet($_EXCEL, $origin_filename);

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
            
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div,null,$origin_filename);
        }
        else
        {
            $array_result['result']    = 'N';
            $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }
        return $array_result;
    }
}