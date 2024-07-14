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

class WithholdingController extends Controller
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
     * 원천징수 조회 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataWithholdingList(Request $request){

        $configArr = Func::getConfigArr();

        $list   = new DataList(Array("listName"=>"withholding","listAction"=>'/'.$request->path()));
        
        if(!isset($request->tabs)) $request->tabs = 'A';
        
        $list->setTabs(Array(
            'A'=>'접수', 'E'=>'완료', 'X'=>'취소'), $request->tabs);

        $list->setHidden(["target_sql"=>""]);
        
        $list->setSearchDate('날짜검색',Array('trade_date'=>'기준일자', 'trade_date'=>'지급적용일자'),'searchDt','Y');
        $list->setButtonArray("엑셀다운","excelDownModal('/account/withholdingexcel','form_withholding')","btn-success");
        $list->setSearchType('i-handle_code',Func::getConfigArr('mo_acct_div'),'법인 구분', '', '', '', '', 'Y', '', true);
        
        $list->setSearchDetail(Array(
            'u.nick_name'             => '투자자명',
            'i.investor_no'           => '투자자번호',
            'i.investor_no-i.inv_seq' => '채권번호'
        ));

        return $list;
    }

    /**
     * 원천징수 메인
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function withholding(Request $request)
    {
        $list   = $this->setDataWithholdingList($request);
        $list->setlistTitleCommon(Array
        (
            'investor_no_inv_seq' => Array('채권번호', 0, '', 'center', '', ''),
            'name'                     => Array('투자자명', 0, '', 'center', '', 'ENC-name'),
            'invest_rate'              => Array('수익률', 0, '', 'center', '', 'invest_rate'),
            'balance'                  => Array('투자잔액', 0, '', 'center', '', 'balance'),
            'return_date'              => Array('기준일자', 0, '', 'center', '', 'return_date'),
            'trade_date'               => Array('지급적용일자', 0, '', 'center', '', 'trade_date'),
            'return_interest'          => Array('이자금액', 0, '', 'center', '', 'return_interest'),
            'withholding_tax'          => Array('원천징수', 0, '', 'center', '', 'withholding_tax'),
            'income_tax'               => Array('소득세', 0, '', 'center', '', 'income_tax'),
            'local_tax'                => Array('주민세', 0, '', 'center', '', 'local_tax'),
            'return_money'             => Array('실지급금액', 0, '', 'center', '', 'return_money'),
        ));

        $rslt['result'] = $list->getList();
        unset($rslt['result']['Tabs']);
        return view('account.withholding')->with($rslt);
    }   
    
    /**
     * 원천징수 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function withholdingList(Request $request)
    {
        $list  = $this->setDataWithholdingList($request);
        foreach($request->all() as $key => $val)
        {
            $param[$key] = $val;
        }

        // 기본쿼리
        $LOAN_LIST = DB::table("loan_info_trade as ip")
                                ->join("loan_info i","i.no","=","ip.loan_info_no")
                                ->join("loan_usr_info u","u.no","=","ip.loan_usr_info_no")
                                ->select("ip.*", "i.inv_seq", "u.name", "i.investor_no", "i.investor_type")
                                ->where('ip.withholding_tax', '>', '0')
                                ->where('ip.save_status','Y')
                                ->where('i.save_status','Y')
                                ->where('u.save_status','Y');

        if(isset( $param['searchDetail']) && $param['searchDetail']=='i.investor_no-i.inv_seq' && !empty($param['searchString']) )
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
                            $LOAN_LIST = $LOAN_LIST->WHERE('i.investor_no',$searchString[0])->WHERE('i.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $LOAN_LIST = $LOAN_LIST->WHERE('i.investor_no',$searchString[0])
                                                    ->WHERE('i.inv_seq',$searchString[1])
                                                    ->WHERE('i.investor_type',$string);          
                        }
                    }
                }
                // 기존 채권번호 형태인경우
                else
                {
                    // 투자자번호로만 검색(ex. 5-?)
                    if($searchString[1] == null || $searchString[1] == 0 || $searchString[1] == '')
                    {
                        $LOAN_LIST = $LOAN_LIST->WHERE('i.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $LOAN_LIST = $LOAN_LIST->WHERE('i.investor_no',$searchString[0])->WHERE('i.inv_seq',$searchString[1]) ;          
                    }
                }
            }
            unset($param['searchString']);
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='i.investor_no' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='u.nick_name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('u.nick_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $LOAN_LIST = $list->getListQuery('ip', 'main', $LOAN_LIST, $param);
        $LOAN_LIST->orderBy("ip.save_time", "desc");

        $paging = new Paging($LOAN_LIST, $request->page, $request->listLimit, 10, $request->listName);

        $cnt = 0;
        $rslt = $LOAN_LIST->GET();
        $rslt = Func::chungDec(["loan_info_trade","LOAN_USR_INFO", "loan_info"], $rslt);	// CHUNG DATABASE DECRYPT

        foreach( $rslt as $v )
        {
            $v->onclick                  = 'popUpFull(\'/account/investmentpop?no='.$v->loan_info_no.'\', \'investment'.$v->loan_info_no.'\')';
            $v->line_style               = 'cursor: pointer;';

            $v->investor_no_inv_seq     = $v->investor_type.$v->investor_no.'-'.$v->inv_seq;

            $v->invest_rate              = number_format($v->invest_rate, 2)." %";

            $v->balance                  = number_format($v->balance);
            $v->trade_date               = Func::dateFormat($v->trade_date);
            $v->return_date              = Func::dateFormat($v->return_date);
            
            $v->return_money             = number_format($v->return_money);

            $v->return_interest          = number_format($v->return_interest);
            $v->withholding_tax          = number_format($v->withholding_tax);
            $v->income_tax               = number_format($v->income_tax);
            $v->local_tax                = number_format($v->local_tax);

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
     * 엑셀다운로드 (수익분배)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function withholdingListExcel(Request $request)
    {
        if( !Func::funcCheckPermit("U002") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }
        
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');

        $list  = $this->setDataWithholdingList($request);
        
        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;
        
        $LOAN_LIST = DB::table("loan_info_trade as ip")
                        ->join("loan_info i","i.no","=","ip.loan_info_no")
                        ->join("loan_usr_info u","u.no","=","ip.loan_usr_info_no")
                        ->select("ip.*", "i.inv_seq", "u.name")
                        ->where('ip.withholding_tax', '>', '0')
                        ->where('ip.save_status','Y')
                        ->where('i.save_status','Y')
                        ->where('u.save_status','Y');

        if(isset( $param['searchDetail']) && $param['searchDetail']=='i.loan_usr_info_no-i.inv_seq' && !empty($param['searchString']) )
        {
            if(strstr($param['searchString'], '-'))
            {
                $searchString = explode("-", $param['searchString']);
                if($searchString[1] == null || $searchString[1] == 0)
                {
                    $LOAN_LIST = $LOAN_LIST->WHERE('i.loan_usr_info_no',$searchString[0]);          
                }
                else {
                    $LOAN_LIST = $LOAN_LIST->WHERE('i.loan_usr_info_no',$searchString[0])->WHERE('i.inv_seq',$searchString[1]) ;          
                }
            }
            unset($param['searchString']);
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='i.loan_usr_info_no' && !empty($param['searchString']) )
        {
            if(strstr($param['searchString'], '-')) 
            {
                unset($param['searchString']);
            }
        }
        
        $LOAN_LIST = $list->getListQuery('ip', 'main', $LOAN_LIST, $param);
        $LOAN_LIST->orderBy("ip.save_time", "desc");
        
        $target_sql = urlencode(encrypt(Func::printQuery($LOAN_LIST))); // 페이지 들어가기 전에 쿼리를 저장해야한다.                
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN_LIST, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "원천징수리스트_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $request_all['work_id'] = Auth::id();
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no))
        {
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
            $origin_filename = $excel_no.'_'.$request->work_id.'_'.date("YmdHis").'.xlsx'; 
        }
        else
        {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $target_sql, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
            $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
        }

        $rslt = $LOAN_LIST->GET();
        $rslt = Func::chungDec(["loan_info","loan_usr_info"], $rslt);	// CHUNG DATABASE DECRYPT
        
        // 엑셀 헤더
        $excel_header   = array('No','투자자번호', '투자계약번호','투자자명','수익률','투자잔액','기준일자','지급적용일자','이자금액','원천징수','소득세','주민세','실지급금액');
        $excel_data     = [];

        $board_count = 1;

        foreach ($rslt as $v)
        {
            $array_data = [
                $board_count,
                $v->loan_usr_info_no,
                $v->loan_info_no,
                $v->name,
                number_format($v->invest_rate, 2)." %",
                number_format($v->balance),
                Func::dateFormat($v->return_date),
                Func::dateFormat($v->trade_date),
                number_format($v->return_interest),
                number_format($v->withholding_tax),
                number_format($v->income_tax),
                number_format($v->local_tax),
                $v->return_money = Func::numberFormat($v->return_interest - $v->withholding_tax),
            ];
            $record_count++;
            $board_count++;
            $excel_data[] = $array_data;
        }
        
        // 엑셀 익스포트
        // ExcelFunc::fastexcelExport($excel_data,$excel_header,$file_name);
        ExcelFunc::fastexcelExport($excel_data, $excel_header, $origin_filename);
    
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

    /**
     * 수익분배 - 입력창
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function withholdingPop(Request $request)
    {
        $rs = DB::TABLE("loan_info")->JOIN("LOAN_USR_INFO","LOAN_USR_INFO.no","=","loan_info.loan_usr_info_no")->JOIN("loan_info_return_plan","loan_info.NO","=","loan_info_return_plan.loan_info_no")->SELECT("loan_info_return_plan.*, LOAN_INFO.STATUS, loan_info.loan_usr_info_no, loan_info.SUM_INTEREST, LOAN_USR_INFO.NAME")->WHERE("LOAN_INFO.NO", $request->loan_info_no)->WHERE("loan_info.SAVE_STATUS","Y")->WHERE("DIVIDE_FLAG","Y")->ORDERBY("loan_info_return_plan.SEQ")->ORDERBY("loan_info.TRADE_DATE")->ORDERBY("loan_info.loan_usr_info_no")->GET();
        $rs = Func::chungDec(["LOAN_USR_INFO","LOAN_INFO","loan_info_return_plan"], $rs);	// CHUNG DATABASE DECRYPT

        $status = "";
        $arrayInfo = [];
        foreach($rs as $v)
        {
            $arrayInfo[$v->plan_date][] = $v;
            $status = $v->status;
        }

        return view('account.withholdingpop')->with("loan_info_no",$request->loan_info_no)->with("status",$status)->with("v", $arrayInfo);
    }

    /**
     * 엑셀다운로드 (수익분배)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function withholdingExcel(Request $request)
    {
        if( !Func::funcCheckPermit("U002") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }
        
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');

        $list  = $this->setDataWithholdingList($request);
        
        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;
        
        $LOAN_LIST = DB::table("loan_info_trade as ip")
                        ->join("loan_info i","i.no","=","ip.loan_info_no")
                        ->join("loan_usr_info u","u.no","=","ip.loan_usr_info_no")
                        ->select("ip.*", "i.inv_seq", "u.name", "i.investor_no", "i.investor_type")
                        ->where('ip.withholding_tax', '>', '0')
                        ->where('ip.save_status','Y')
                        ->where('i.save_status','Y')
                        ->where('u.save_status','Y');

        if(isset( $param['searchDetail']) && $param['searchDetail']=='i.investor_no-i.inv_seq' && !empty($param['searchString']) )
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
                            $LOAN_LIST = $LOAN_LIST->WHERE('i.investor_no',$searchString[0])->WHERE('i.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $LOAN_LIST = $LOAN_LIST->WHERE('i.investor_no',$searchString[0])
                                                    ->WHERE('i.inv_seq',$searchString[1])
                                                    ->WHERE('i.investor_type',$string);          
                        }
                    }
                }
                // 기존 채권번호 형태인경우
                else
                {
                    // 투자자번호로만 검색(ex. 5-?)
                    if($searchString[1] == null || $searchString[1] == 0 || $searchString[1] == '')
                    {
                        $LOAN_LIST = $LOAN_LIST->WHERE('i.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $LOAN_LIST = $LOAN_LIST->WHERE('i.investor_no',$searchString[0])->WHERE('i.inv_seq',$searchString[1]) ;          
                    }
                }
            }
            unset($param['searchString']);
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='i.investor_no' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='u.nick_name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('u.nick_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }
        
        $LOAN_LIST = $list->getListQuery('ip', 'main', $LOAN_LIST, $param);
        $LOAN_LIST->orderBy("ip.save_time", "desc");

        $target_sql = urlencode(encrypt(Func::printQuery($LOAN_LIST))); // 페이지 들어가기 전에 쿼리를 저장해야한다.                
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN_LIST, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "원천징수 리스트_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $request_all['work_id'] = Auth::id();
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no))
        {
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
            $origin_filename = $excel_no.'_'.$request->work_id.'_'.date("YmdHis").'.xlsx'; 
        }
        else
        {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $target_sql, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
            $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
        }

        $rslt = $LOAN_LIST->GET();
        $rslt = Func::chungDec(["loan_info","loan_usr_info"], $rslt);	// CHUNG DATABASE DECRYPT
        
        // 엑셀 헤더
        $excel_header   = array('No','채권번호','투자자명','수익률','투자잔액','기준일자','지급적용일자','이자금액','원천징수','소득세','주민세','실지급금액');
        $excel_data     = [];

        $board_count = 1;

        foreach ($rslt as $v)
        {
            $array_data = [
                $board_count,
                $v->investor_type.$v->investor_no.'-'.$v->inv_seq,
                $v->name,
                number_format($v->invest_rate, 2)." %",
                number_format($v->balance),
                Func::dateFormat($v->return_date),
                Func::dateFormat($v->trade_date),
                number_format($v->return_interest),
                number_format($v->withholding_tax),
                number_format($v->income_tax),
                number_format($v->local_tax),
                $v->return_money = Func::numberFormat($v->return_interest - $v->withholding_tax),
            ];
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