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

class RemittanceController extends Controller
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
     * 이자지급스케줄명세 조회 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataRemittanceList(Request $request)
    {
        $list   = new DataList(Array("listName"=>"remittance","listAction"=>'/'.$request->path()));

        if(!isset($request->tabs)) $request->tabs = 'ALL';
        
        $list->setTabs(Vars::$arrayRemittanceDays, $request->tabs);
        
        $list->setSearchDate('날짜검색',Array('loan_info.contract_date'=>'계약일', 'loan_info.contract_end_date'=>'만기일', 'loan_info.return_date_biz'=>'차기지급일'),'searchDt', 'Y', 'N', '', '', 'return_date_biz');

        $list->setSearchType('loan_info-pro_cd',Func::getConfigArr('pro_cd'),'상품구분', '', '', '', '', 'Y', '', true);

        $list->setSearchType('loan_info-handle_code',Func::getConfigArr('mo_acct_div'),'법인 구분', '', '', '', '', 'Y', '', true);

        $list->setCheckBox('no');

        if( Func::funcCheckPermit("U002") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/account/remittanceexcel', 'form_remittance')", "btn-success");
        }

        $list->setSearchDetail(Array(
            'loan_usr_info.nick_name' => '투자자명',
            'loan_info.investor_no'   => '투자자번호',
            'investor_no-inv_seq'     => '채권번호',
        ));

        return $list;
    }

    /**
     * 이자지급스케줄명세 메인
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function remittance(Request $request)
    {
        $list = $this->setDataRemittanceList($request);

        $list->setLumpForm('INS', Array('BTN_NAME'=>'일괄송금요청','BTN_ACTION'=>'lump_ins(this)','BTN_ICON'=>'','BTN_COLOR'=>''));
        
        $list->setlistTitleCommon(Array
        (
            'investor_no_inv_seq'      => Array('채권번호', 0, '', 'center', '', ''),
            'pro_cd'                   => Array('상품구분', 1, '', 'center', '', 'pro_cd'),

            'cust_bank_name'           => Array('차입자명', 1, '', 'center', '', 'cust_bank_name'),
            'cust_bank_cd'             => Array('차입자은행', 0, '', 'center', '', 'cust_bank_cd'),
            'cust_bank_ssn'            => Array('차입자계좌번호', 0, '', 'center', '', 'cust_bank_ssn'),

            'loan_usr_info_name'       => Array('투자자명', 1, '', 'center', '', ''),

            'loan_bank_cd'             => Array('투자자은행', 0, '', 'center', '', 'loan_bank_cd'),
            'loan_bank_ssn'            => Array('투자자계좌번호', 0, '', 'center', '', 'loan_bank_ssn'),
            'loan_bank_name'           => Array('투자자예금주명', 1, '', 'center', '', 'loan_bank_name'),

            'return_date_biz'          => Array('지급일', 0, '', 'center', '', 'return_date_biz'),
            'return_money'             => Array('지급금액', 0, '', 'center', '', 'return_money'),
            
            'search'                   => Array('실명인증요청', 0, '', 'center', '', ''),
            'save'                     => Array('송금요청', 0, '', 'center', '', ''),
        ));

        return view('account.remittance')->with('result', $list->getList());
    }   
    
    /**
     * 이자지급스케줄명세 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function remittanceList(Request $request)
    {
        $list  = $this->setDataRemittanceList($request);
        $param  = $request->all();

        $today     = date('Ymd');
        $nextDay   = Func::getBizDay(date('Ymd', strtotime($today." +1 days")));
        $DoubleDay = Func::getBizDay(date('Ymd', strtotime($nextDay." +1 days")));
        $TripleDay = Func::getBizDay(date('Ymd', strtotime($DoubleDay." +1 days")));

        // 기본쿼리
        $plans = DB::TABLE("loan_info")
                ->join("loan_usr_info", 'loan_info.loan_usr_info_no', '=', 'loan_usr_info.no')
                ->JOIN("cust_info", "cust_info.NO", "=", "loan_info.cust_info_no")
                ->JOIN("cust_info_extra", "cust_info_extra.cust_info_no", "=", "cust_info.no")
                ->SELECT("loan_info.*", "loan_usr_info.name")
                
                ->WHERE("loan_info.return_money", '>', "0")
                ->WHERE("loan_info.return_date_biz", '>=', $today)

                ->WHERE("loan_info.status", '!=','E')
                ->WHERE("loan_info.save_status", 'Y')

                ->WHERE("loan_usr_info.save_status", 'Y')
                ->WHERE("loan_info.save_status", 'Y')
                ->WHERE("cust_info.save_status", 'Y');

        // 당일
        if( $request->tabsSelect=="0" )
        {
            $plans->WHERE("loan_info.return_date_biz", '>=', $today);
            $plans->WHERE("loan_info.return_date_biz", '<=', $today);
        }
        // 1일차
        else if( $request->tabsSelect=="1" )
        {
            $plans->WHERE("loan_info.return_date_biz", '>=', $nextDay);
            $plans->WHERE("loan_info.return_date_biz", '<=', $nextDay);
        }
        // 2일차
        else if( $request->tabsSelect=="2" )
        {

            $plans->WHERE("loan_info.return_date_biz", '>=', $DoubleDay);
            $plans->WHERE("loan_info.return_date_biz", '<=', $DoubleDay);
        }
        // 3일차
        else if( $request->tabsSelect=="3" )
        {

            $plans->WHERE("loan_info.return_date_biz", '>=', $TripleDay);
            $plans->WHERE("loan_info.return_date_biz", '<=', $TripleDay);
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
                            $plans = $plans->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $plans = $plans->WHERE('loan_usr_info.investor_no',$searchString[0])
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
                        $plans = $plans->WHERE('loan_usr_info.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $plans = $plans->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
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
            $plans = $plans->where('loan_usr_info.nick_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        unset($param['tabSelectNm']);
        unset($param['tabsSelect']);

        $plans = $list->getListQuery("loan_info",'main',$plans,$param);
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $sum_data = Array
        (
            ["coalesce(sum(loan_info.return_money),0)", '총지급금액', '원'],
        );

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($plans, $request->page, $request->listLimit, 10, $request->listName, '', $sum_data);

        $rslt = $plans->GET();
        $rslt = Func::chungDec(["loan_usr_info", "loan_info", "cust_info"], $rslt);	// CHUNG DATABASE DECRYPT
        
        $loanArray = [];
        
        // 뷰단 데이터 정리.
        $getProCd          = Func::getConfigArr('pro_cd');
        $getReturnMethodCd = Func::getConfigArr('return_method_cd');
        $getBankCd         = Func::getConfigArr('bank_cd');
        $arrBranch         = Func::getBranch();
        $arrayUserId       = Func::getUserId();

        $results = DB::table('account_transfer')
                    ->select('loan_info_no')
                    ->whereIn('status', ['S','W','A'])
                    ->where('save_status', 'Y')
                    ->groupBy('loan_info_no')
                    ->get();
        $results = Func::chungDec(["account_transfer"], $results);	// CHUNG DATABASE DECRYPT

        foreach ($results as $result)
        {
            $loanArray[$result->loan_info_no] = 'Y';
        }

        $cnt = 0;
        foreach ($rslt as $v)
        {
            $link_b                      = '<a class="hand" onClick="popUpFull(\'/account/investmentpop?no='.$v->no.'\', \'investment'.$v->no.'\')">';
            $v->investor_no_inv_seq      = $link_b.$v->investor_type.$v->investor_no.'-'.$v->inv_seq;
            
            $v->loan_usr_info_name       = $v->name ?? '';
            
            $v->cust_bank_cd             = Func::getArrayName($getBankCd, $v->cust_bank_cd);
            $v->loan_bank_cd             = Func::getArrayName($getBankCd, $v->loan_bank_cd);

            $v->status                   = Func::getInvStatus($v->status, true);
            $v->return_date_biz          = Func::dateFormat($v->return_date_biz);
            $v->return_money             = number_format($v->return_money);

            $v->save_id                  = Func::getArrayName($arrayUserId, $v->save_id);
            $v->save_time                = Func::dateFormat($v->save_time);

            $v->pro_cd                   = Func::getArrayName($getProCd, $v->pro_cd);

            if(!empty($v->loan_bank_status) && $v->loan_bank_status == 'Y')
            {
                $v->search               = '<button type="button" class="btn btn-xs btn-default float-center ml-2">인증완료</button>';
            }
            else
            {
                $v->search               = '<button type="button" class="btn btn-xs btn-primary float-center ml-2" id="btnSearchAccount_'.$v->no.'" onclick="searchAccount(\''.$v->no.'\');">실명인증요청</button>';
            }

            if(!empty($loanArray[$v->no]))
            {
                $v->save                 = '<button type="button" class="btn btn-xs btn-default float-center ml-2">요청중</button>';
            }
            else
            {
                $v->save                 = '<button type="button" class="btn btn-xs btn-primary float-center ml-2" id="btnSendAccount_'.$v->no.'" onclick="sendAccount(\''.$v->no.'\');">송금요청</button>';
            }

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
     * 이자지급스케줄명세 - 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function remittanceExcel(Request $request)
    {
        if( !Func::funcCheckPermit("U002") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }
        
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');

        $list             = $this->setDataRemittanceList($request);
        $param            = $request->all();
        $down_div         = $request->down_div;
        $down_filename    = $request->down_filename;
        $excel_down_div   = $request->excel_down_div;
        $etc              = $request->etc;

        $loan_usr_info_no = $request->loan_usr_info_no;

        $INV = DB::TABLE("loan_info")
                ->join("loan_usr_info", 'loan_info.loan_usr_info_no', '=', 'loan_usr_info.no')
                ->JOIN("cust_info", "cust_info.NO", "=", "loan_info.cust_info_no")
                ->JOIN("cust_info_extra", "cust_info_extra.cust_info_no", "=", "cust_info.no")
                ->SELECT("loan_info.*", "loan_usr_info.name")
                
                ->WHERE("loan_info.return_money", '>', "0")
                ->WHERE("loan_info.return_date_biz", '>=', date('Ymd'))

                ->WHERE("loan_info.status", '!=','E')
                ->WHERE("loan_info.save_status", 'Y')

                ->WHERE("loan_usr_info.save_status", 'Y')
                ->WHERE("loan_info.save_status", 'Y')
                ->WHERE("cust_info.save_status", 'Y');

        // 당일
        if( $request->tabsSelect=="0" )
        {
            $INV->WHERE("loan_info.return_date_biz", '>=', date('Ymd'));
            $INV->WHERE("loan_info.return_date_biz", '<=', date('Ymd'));
        }
        // 1일차
        else if( $request->tabsSelect=="1" )
        {
            $INV->WHERE("loan_info.return_date_biz", '>=', date('Ymd', strtotime(date('Ymd')." +1 days")));
            $INV->WHERE("loan_info.return_date_biz", '<=', date('Ymd', strtotime(date('Ymd')." +1 days")));
        }
        // 2일차
        else if( $request->tabsSelect=="2" )
        {
            $INV->WHERE("loan_info.return_date_biz", '>=', date('Ymd', strtotime(date('Ymd')." +2 days")));
            $INV->WHERE("loan_info.return_date_biz", '<=', date('Ymd', strtotime(date('Ymd')." +2 days")));
        }
        // 3일차
        else if( $request->tabsSelect=="3" )
        {
            $INV->WHERE("loan_info.return_date_biz", '>=', date('Ymd', strtotime(date('Ymd')." +3 days")));
            $INV->WHERE("loan_info.return_date_biz", '<=', date('Ymd', strtotime(date('Ymd')." +3 days")));
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
                            $INV = $INV->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $INV = $INV->WHERE('loan_usr_info.investor_no',$searchString[0])
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
                        $INV = $INV->WHERE('loan_usr_info.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $INV = $INV->WHERE('loan_usr_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
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
            $plans = $plans->where('loan_usr_info.nick_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        unset($param['tabSelectNm']);
        unset($param['tabsSelect']);

        $INV = $list->getListQuery('loan_info', 'main', $INV, $param);
        Log::info(Func::printQuery($INV));

        $target_sql = urlencode(encrypt(Func::printQuery($INV))); // 페이지 들어가기 전에 쿼리를 저장해야한다.

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($INV, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "이자지급스케줄_명세_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $request_all['work_id'] = Auth::id();
        $all_data     = json_encode($request_all, true);;

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

        $rslt = $INV->GET();
        $rslt = Func::chungDec(["loan_usr_info", "loan_info", "cust_info"], $rslt);	// CHUNG DATABASE DECRYPT

        $getProCd       = Func::getConfigArr('pro_cd');
        $getBankCd      = Func::getConfigArr('bank_cd');

        // 엑셀 헤더
        $excel_header   = array('NO','채권번호','상품구분','차입자명','차입자은행','차입자계좌번호', '투자자명', '투자자은행', '투자자계좌번호','투자자예금주명', '지급일','지급금액','실명인증요청','송금요청');

        $results = DB::table('account_transfer')
                    ->select('loan_info_no')
                    ->whereIn('status', ['S','W','A'])
                    ->where('save_status', 'Y')
                    ->groupBy('loan_info_no')
                    ->get();
        $results = Func::chungDec(["account_transfer"], $results);	// CHUNG DATABASE DECRYPT

        foreach ($results as $result)
        {
            $loanArray[$result->loan_info_no] = 'Y';
        }

        $excel_data     = [];
        $board_count    = 1;
        foreach ($rslt as $v)
        {
            $array_data = [
                $board_count,
                $v->investor_type.$v->investor_no.'-'.$v->inv_seq,
                Func::getArrayName($getProCd, $v->pro_cd),
                $v->cust_bank_name,
                Func::getArrayName($getBankCd, $v->cust_bank_cd),
                $v->cust_bank_ssn,
                $v->name ?? '',
                Func::getArrayName($getBankCd, $v->loan_bank_cd),
                $v->loan_bank_ssn,
                $v->loan_bank_name,
                Func::dateFormat($v->return_date_biz),
                number_format($v->return_money),
            ];
            if(!empty($v->loan_bank_status) && $v->loan_bank_status == 'Y')
            {
                $array_data[] = $v->save = '인증완료';          
            }
            else
            {
                $array_data[] = $v->save = '실명인증요청';      
            }        
            if(!empty($loanArray[$v->no]))
            {
                $array_data[] = $v->save = '요청중';          
            }
            else
            {
                $array_data[] = $v->save = '송금요청';      
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
     * 이자지급스케줄명세 - 송금요청
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function remittanceAction(Request $request)
    {
        $param = $request->all();

        $account_transfer = DB::table('account_transfer')->join("loan_info","loan_info.no","=","account_transfer.loan_info_no")
                                            ->join("cust_info","cust_info.no","=","loan_info.cust_info_no")
                                            ->join("loan_usr_info", 'loan_info.loan_usr_info_no', '=', 'loan_usr_info.no')
                                            ->where('account_transfer.loan_info_no', $param['loan_info_no'])
                                            ->whereIn('account_transfer.status', ['S', 'W', 'A'])
                                            ->where('loan_info.save_status', 'Y')
                                            ->where('cust_info.save_status', 'Y')
                                            ->where('loan_usr_info.save_status', 'Y')
                                            ->where('account_transfer.save_status', 'Y')
                                            ->orderby('account_transfer.no')
                                            ->first();
                                            
        $account_transfer = Func::chungDec(["account_transfer"], $account_transfer);	// CHUNG DATABASE DECRYPT

        DB::beginTransaction();

        if(!empty($account_transfer->no))
        {
            DB::rollback();

            $array_result['rs_code']    = "N";
            $array_result['result_msg'] = "이체실행결재에서 현재 요청중인 송금이 있습니다.";
            return $array_result;
        }

        $loan_info = DB::table("loan_info")->where('no', $param['loan_info_no'])->where('save_status', 'Y')->first();
        $loan_info = Func::chungDec(["account_transfer"], $loan_info);	// CHUNG DATABASE DECRYPT

        if(empty($loan_info->loan_bank_cd) || empty($loan_info->loan_bank_ssn) || empty($loan_info->loan_bank_name))
        {
            DB::rollback();

            $array_result['rs_code']    = "N";
            $array_result['result_msg'] = "송금 받을 계좌정보가 없는 고객입니다.";
            return $array_result;
        }

        if(empty($loan_info->loan_bank_status) || (!empty($loan_info->loan_bank_status) && $loan_info->loan_bank_status != 'Y'))
        {
            DB::rollback();

            $array_result['rs_code']    = "N";
            $array_result['result_msg'] = "계좌실명조회가 되어있지않은 고객입니다.";
            return $array_result;
        }
                
        $schedule = DB::table('loan_info_return_plan')->join("loan_info","loan_info.no","=","loan_info_return_plan.loan_info_no")
                                            ->where('loan_info_return_plan.loan_info_no', $param['loan_info_no'])
                                            ->where('loan_info_return_plan.save_status', 'Y')
                                            ->where('loan_info.save_status', 'Y')
                                            ->first();
                                            
        $schedule = Func::chungDec(["schedule"], $schedule);	// CHUNG DATABASE DECRYPT

        if(empty($schedule->loan_info_no))
        {
            DB::rollBack();

            $array_result['rs_code']    = "N";
            $array_result['result_msg'] = "스케줄정보가 없는 고객입니다.";
            return $array_result;
        }

        $_INS = [];
        $_INS['cust_info_no']        = $loan_info->cust_info_no;
        $_INS['loan_usr_info_no']    = $loan_info->loan_usr_info_no;
        $_INS['loan_info_no']        = $loan_info->no;
        $_INS['investor_no']         = $loan_info->investor_no;
        $_INS['inv_seq']             = $loan_info->inv_seq;
        $_INS['pro_cd']              = $loan_info->pro_cd;
        $_INS['handle_code']         = $loan_info->handle_code;
        
        $_INS['cust_bank_cd']        = $loan_info->cust_bank_cd;
        $_INS['cust_bank_ssn']       = $loan_info->cust_bank_ssn;
        $_INS['cust_bank_name']      = $loan_info->cust_bank_name;
        $_INS['loan_bank_cd']        = $loan_info->loan_bank_cd;
        $_INS['loan_bank_ssn']       = $loan_info->loan_bank_ssn;
        $_INS['loan_bank_name']      = $loan_info->loan_bank_name;
        $_INS['loan_bank_nick']      = $loan_info->loan_bank_nick;
        
        $_INS['invest_rate']         = $loan_info->invest_rate;
        $_INS['income_rate']         = $loan_info->income_rate;
        $_INS['local_rate']          = $loan_info->local_rate;

        $_INS['loan_money']          = $loan_info->loan_money;
        $_INS['balance']             = $loan_info->balance;
        $_INS['return_date']         = $loan_info->return_date;
        $_INS['return_date_biz']     = $loan_info->return_date_biz;
        $_INS['return_money']        = $loan_info->return_money;
        $_INS['return_origin']       = $loan_info->return_origin;
        $_INS['return_interest']     = $loan_info->return_interest;
        $_INS['withholding_tax']     = $loan_info->withholding_tax;
        $_INS['income_tax']          = $loan_info->income_tax;
        $_INS['local_tax']           = $loan_info->local_tax;
        
        $_INS['firm_banking_status'] = $_INS['status'] = $_INS['out_type'] = 'S';
        $_INS['name_check_cnt']      = 0;

        $_INS['app_id']              = Auth::id();
        $_INS['app_time']            = date("YmdHis");

        $_INS['save_status']         = 'Y';
        $_INS['save_id']             = Auth::id();
        $_INS['save_time']           = date("YmdHis");

        $rslt = DB::dataProcess("INS", "account_transfer", $_INS);

        if($rslt != "Y")
        {
            DB::rollback();

            $array_result['rs_code']    = "N";
            $array_result['result_msg'] = "처리에 실패하였습니다.";
            return $array_result;
        }

        DB::commit();

        $array_result['rs_code']    = "Y";
        $array_result['result_msg'] = "정상처리 되었습니다.";

        return $array_result;
    }

    /**
     * 이자지급스케줄명세 일괄 송금 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function remittanceLumpInsert(Request $request)
    {
        $val = $request->input();
        
        $s_cnt = 0;
        $arr_fail = Array();

        $save_id   = Auth::id();
        $save_time = date("YmdHis");

        if( $val['action_mode']=="remittance_INSERT" && is_array($val['listChk']) && sizeof($val['listChk'])>0 )
        {
            for( $i=0; $i<sizeof($val['listChk']); $i++ )
            {
                $loan_info_no = $val['listChk'][$i];

                $loan_info = DB::table("loan_info")->where('no', $loan_info_no)->where('save_status', 'Y')->first();
                $loan_info = Func::chungDec(["account_transfer"], $loan_info);	// CHUNG DATABASE DECRYPT

                DB::beginTransaction();

                $account_transfer = DB::table('account_transfer')->join("loan_info","loan_info.no","=","account_transfer.loan_info_no")
                                                    ->join("cust_info","cust_info.no","=","loan_info.cust_info_no")
                                                    ->join("loan_usr_info", 'loan_info.loan_usr_info_no', '=', 'loan_usr_info.no')
                                                    ->where('account_transfer.loan_info_no', $loan_info_no)
                                                    ->whereIn('account_transfer.status', ['S', 'W', 'A'])
                                                    ->where('loan_info.save_status', 'Y')
                                                    ->where('cust_info.save_status', 'Y')
                                                    ->where('loan_usr_info.save_status', 'Y')
                                                    ->where('account_transfer.save_status', 'Y')
                                                    ->orderby('account_transfer.no')
                                                    ->first();
                                                    
                $account_transfer = Func::chungDec(["account_transfer"], $account_transfer);	// CHUNG DATABASE DECRYPT

                if(!empty($account_transfer->no))
                {
                    DB::rollBack();

                    $arr_fail[$loan_info->investor_no."-".$loan_info->inv_seq] = "이미 요청된 계약이 있습니다.";
                    continue;
                }

                if(empty($loan_info->loan_bank_cd) || empty($loan_info->loan_bank_ssn) || empty($loan_info->loan_bank_name))
                {
                    DB::rollback();
        
                    $arr_fail[$loan_info->investor_no."-".$loan_info->inv_seq] = "송금 받을 계좌정보가 없는 고객입니다.";
                    return $array_result;
                }

                if(empty($loan_info->loan_bank_status) || (!empty($loan_info->loan_bank_status) && $loan_info->loan_bank_status != 'Y'))
                {
                    DB::rollBack();

                    $arr_fail[$loan_info->investor_no."-".$loan_info->inv_seq] = "실명인증이 되지 않은 계약이 있습니다.";
                    continue;
                }
                
                $schedule = DB::table('loan_info_return_plan')->join("loan_info","loan_info.no","=","loan_info_return_plan.loan_info_no")
                                                    ->where('loan_info_return_plan.loan_info_no', $loan_info_no)
                                                    ->where('loan_info_return_plan.save_status', 'Y')
                                                    ->where('loan_info.save_status', 'Y')
                                                    ->first();
                                                    
                $schedule = Func::chungDec(["schedule"], $schedule);	// CHUNG DATABASE DECRYPT

                if(empty($schedule->loan_info_no))
                {
                    DB::rollBack();

                    $arr_fail[$loan_info->investor_no."-".$loan_info->inv_seq] = "스케줄정보가 없습니다.";
                    continue;
                }

                $_INS = [];
                $_INS['cust_info_no']        = $loan_info->cust_info_no;
                $_INS['loan_usr_info_no']    = $loan_info->loan_usr_info_no;
                $_INS['loan_info_no']        = $loan_info->no;
                $_INS['investor_no']         = $loan_info->investor_no;
                $_INS['inv_seq']             = $loan_info->inv_seq;
                $_INS['pro_cd']              = $loan_info->pro_cd;
                $_INS['handle_code']         = $loan_info->handle_code;
                
                $_INS['cust_bank_cd']        = $loan_info->cust_bank_cd;
                $_INS['cust_bank_ssn']       = $loan_info->cust_bank_ssn;
                $_INS['cust_bank_name']      = $loan_info->cust_bank_name;
                $_INS['loan_bank_cd']        = $loan_info->loan_bank_cd;
                $_INS['loan_bank_ssn']       = $loan_info->loan_bank_ssn;
                $_INS['loan_bank_name']      = $loan_info->loan_bank_name;
                $_INS['loan_bank_nick']      = $loan_info->loan_bank_nick;
                
                $_INS['invest_rate']         = $loan_info->invest_rate;
                $_INS['income_rate']         = $loan_info->income_rate;
                $_INS['local_rate']          = $loan_info->local_rate;
        
                $_INS['loan_money']          = $loan_info->loan_money;
                $_INS['balance']             = $loan_info->balance;
                $_INS['return_date']         = $loan_info->return_date;
                $_INS['return_date_biz']     = $loan_info->return_date_biz;
                $_INS['return_money']        = $loan_info->return_money;
                $_INS['return_origin']       = $loan_info->return_origin;
                $_INS['return_interest']     = $loan_info->return_interest;
                $_INS['withholding_tax']     = $loan_info->withholding_tax;
                $_INS['income_tax']          = $loan_info->income_tax;
                $_INS['local_tax']           = $loan_info->local_tax;
                
                $_INS['firm_banking_status'] = $_INS['status'] = $_INS['out_type'] = 'S';
                $_INS['name_check_cnt']      = 0;
                
                $_INS['app_id']             = $save_id;
                $_INS['app_time']           = $save_time;

                $_INS['save_status']        = 'Y';
                $_INS['save_id']            = $save_id;
                $_INS['save_time']          = $save_time;

                $rslt = DB::dataProcess("INS", "account_transfer", $_INS);

                if($rslt != "Y")
                {
                    DB::rollBack();

                    $arr_fail[$loan_info->investor_no."-".$loan_info->inv_seq] = "입력을 실패하였습니다.";
                    continue;
                }

                $s_cnt++;

                DB::commit();
            }
        }
        else
        {
            $_RESULT['rslt'] = 'N';
            $_RESULT['msg']  = "파라미터 에러";
            return $_RESULT;
        }

        if(isset($arr_fail) && sizeof($arr_fail)>0)
        {
            $error_msg = "실패건이 존재합니다. \n";

            foreach($arr_fail as $t_no => $msg)
            {
                $error_msg .= "[".$t_no."] => ".$msg."\n";
            }

            $return_msg = sizeof($val['listChk'])."건 중 ".$s_cnt."건 성공 ".sizeof($arr_fail)."건 실패\n";
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