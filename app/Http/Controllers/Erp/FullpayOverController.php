<?php
namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataList;
use Log;
use Func;
use Vars;
use DB;
use App\Chung\Paging;
use ExcelFunc;
use Auth;
use Illuminate\Support\Facades\Storage;


class FullpayOverController extends Controller
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
        $list   = new DataList(Array("listName"=>"fullpayover","listAction"=>'/'.$request->path()));

        /*
        $list->setCheckBox("no");
        if( Func::funcCheckPermit("E022") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/erp/fullpaymentexcel', 'form_fullpayment')", "btn-success");
        }
        */

        $list->setSearchDate('날짜검색',Array('contract_date'=>'계약일', 'contract_end_date'=>'만기일','fullpay_date'=>'완제일' ),'searchDt','Y');
        $list->setRangeSearchDetail(Array ('limit_money'=>'한도액','loan_money'=>'대출금액','over_money'=>'가수금액'),'','','');
        if( Func::funcCheckPermit("E004") || Func::funcCheckPermit("E031") )
        {
            $list->setSearchType('manager_code', Func::myPermitBranch(), '관리지점');
        }
        $list->setSearchType('pro_cd',Func::getConfigArr('pro_cd'),'상품');
        $list->setSearchType('fullpay_cd',Func::getConfigArr('flpay_cd'),'완제사유');

        //$list->setSearchType('status',Vars::$arrayContractSta,'상태');
        $list->setSearchDetail(Array( 
            'LOAN_INFO.NO'  => '계약번호',
            'LOAN_INFO.CUST_INFO_NO'  => '고객번호',
            'NAME'  => '이름',
            'SSN'   => '주민번호',
        ));
        return $list;
    }
    
    /**
     * 완제자명세 메인화면
     *
     * @param  request
     * @return view
     */
	public function over(Request $request)
    {
        $list   = $this->setDataList($request);

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬) 
        $list->setlistTitleCommon(Array
        (
            'cust_info_no'     => Array('고객번호', 0, '5', 'center', '', 'cust_info_no'),
            'loan_info_no'     => Array('계약번호', 0, '5', 'center', '', 'loan_info.no'),
            'name'             => Array('이름', 0, '', 'center', '', 'name'),
            'ssn'              => Array('생년월일', 0, '', 'center', '', 'ssn'),
            'manager_name'     => Array('관리지점', 0, '', 'center', '', 'manager_code'),
            'loan_type'        => Array('대출종류', 0, '', 'center', '', 'loan_type'),
            'pro_cd'           => Array('상품명', 0, '', 'center', '', 'pro_cd'),
            'loan_date'        => Array('계약일', 0, '', 'center', '', 'loan_date'),
            'contract_end_date'=> Array('만기일', 0, '', 'center', '', 'contract_end_date'),

            'trade_days'       => Array('거래일수', 0, '', 'center', '', 'trade_days'),
            'fullpay_date'     => Array('완제일', 0, '', 'center', '', 'fullpay_date'),
            'fullpay_nm'       => Array('완제사유', 0, '', 'center', '', 'fullpay_cd'),
            'fullpay_origin'   => Array('최종완납원금', 0, '', 'center', '', 'fullpay_origin'),
            'over_money'       => Array('가수금', 0, '', 'center', '', 'over_money'),
            // 'trade_term'       => Array('거래기간', 0, '', 'center', '', 'trade_term'),
            //'limit_money'      => Array('한도', 0, '', 'right comma', '', 'limit_money'),
        ));

        $rslt['result'] = $list->getList();

        return view('erp.mortgage')->with($rslt);
    }


    /**
     * 완제자명세 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function overList(Request $request)
    {
        $request->isDebug = true;
        
        $list  = $this->setDataList($request);
        $param = $request->all();

        // 기본쿼리
        $LOAN = DB::TABLE("LOAN_INFO");
        $LOAN->JOIN("CUST_INFO", "LOAN_INFO.CUST_INFO_NO", "=", "CUST_INFO.NO");
        $LOAN->JOIN("CUST_INFO_EXTRA", "CUST_INFO.NO", "=", "CUST_INFO_EXTRA.CUST_INFO_NO");
        $LOAN->SELECT("LOAN_INFO.CUST_INFO_NO, LOAN_INFO.NO, CUST_INFO.NAME, CUST_INFO.SSN, LOAN_INFO.LOAN_TYPE, LOAN_INFO.PRO_CD, LOAN_INFO.LOAN_DATE, LOAN_INFO.CONTRACT_END_DATE, LOAN_INFO.FULLPAY_DATE, LOAN_INFO.LIMIT_MONEY, LOAN_INFO.FULLPAY_CD, LOAN_INFO.FULLPAY_ORIGIN, LOAN_INFO.OVER_MONEY, LOAN_INFO.MANAGER_CODE");
        $LOAN->WHERE('CUST_INFO.SAVE_STATUS','Y');
        $LOAN->WHERE('LOAN_INFO.SAVE_STATUS','Y');
        $LOAN->WHERE('LOAN_INFO.STATUS','E');
        $LOAN->WHERE('LOAN_INFO.OVER_MONEY','>',"0");

        // 전지점 조회권한 없으면 자기 지점만
        if( !Func::funcCheckPermit("E004") )
        {
            $LOAN->WHEREIN('LOAN_INFO.MANAGER_CODE', array_keys(Func::myPermitBranch()));
        }

        if( !isset($param['listOrder']) && !isset($param['listOrderAsc']) )
        {
            $param['listOrder']    = "LOAN_INFO.FULLPAY_DATE";
            $param['listOrderAsc'] = "DESC";
        }

        $LOAN = $list->getListQuery("LOAN_INFO", "main", $LOAN,$param);
        $LOAN->ORDERBY("LOAN_INFO.NO", "DESC");

        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN, $request->page, $request->listLimit, 10, $request->listName);
        $rslt   = $LOAN->GET();
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","CUST_INFO_EXTRA"], $rslt);	// CHUNG DATABASE DECRYPT

        $array_pro_cd   = Func::getConfigArr('pro_cd');
        $array_config   = Func::getConfigArr();
        $getStatus      = Vars::$arrayContractStaColor;
        $arrBranch      = Func::getBranch();

        $cnt = 0;
        foreach ($rslt as $v)
        {
            $v->onclick           = 'window.open("/erp/tradeoutform?loan_info_no_auto=Y&loan_info_no='.$v->no.'", \'tradeoutpop\', \'width=900, height=800, scrollbars=yes\')';
            $v->line_style        = 'cursor: pointer;';

            $v->loan_info_no      = $v->no;
            $v->ssn               = substr($v->ssn, 0, 6);
            $v->loan_date         = Func::dateFormat($v->loan_date);
            $v->contract_end_date = Func::dateFormat($v->contract_end_date);
            $v->loan_type         = Func::getArrayName($array_config['app_type_cd'], $v->loan_type);
            $v->fullpay_date      = Func::dateFormat($v->fullpay_date);
            $v->pro_cd            = Func::getArrayName($array_pro_cd, $v->pro_cd);
            //$v->agent_cd          = Func::getArrayName($array_agent_cd, $v->agent_cd);
            //$v->path_cd           = Func::getArrayName($array_config['path_cd'], $v->path_cd);
            $v->trade_days        = Func::dateTerm($v->loan_date, $v->fullpay_date);

            $v->fullpay_nm        = Func::nvl($array_config['flpay_cd'][$v->fullpay_cd], $v->fullpay_cd);
            $v->fullpay_origin    = number_format($v->fullpay_origin);
            $v->over_money        = number_format($v->over_money);
            $v->manager_name      = Func::nvl($arrBranch[$v->manager_code], $v->manager_code);

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
     * 완제자명세 완제사유코드 일괄처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function fullPaymentLumpAction(Request $request)
    {
        $val = $request->input();
        log::debug(print_r($val,true));
        $request->isDebug = true;

        if( !is_array($val['listChk']) || sizeof($val['listChk'])==0 )
        {
            return "선택된 계약이 없습니다.";
        }

        // 일괄처리 구분
        if( $val['lump_action_code']=="CHANGE_FULLPAY_CD" )
        {
            foreach( $val['listChk'] as $no )
            {
                $rslt = DB::dataProcess("UPD", "LOAN_INFO", ['fullpay_cd'=>$val['lump_fullpay_cd']],[['NO',$no],['STATUS','E']]);
            }
        }
        else
        {
            return "선택된 일괄처리가 없습니다.";
        }

        if( $rslt=="Y" )
        {
            return "Y";
        }
        else
        {
            return "처리에 실패하였습니다.";
        }

    }


    /**
     * 완제자명세 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function fullPaymentExcel(Request $request)
    {
        if( !Func::funcCheckPermit("E022") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }

        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $list   = $this->setDataList($request);
        $param  = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        // 기본쿼리
        $LOAN = DB::TABLE("LOAN_INFO");
        $LOAN->JOIN("CUST_INFO", "LOAN_INFO.CUST_INFO_NO", "=", "CUST_INFO.NO");
        $LOAN->JOIN("CUST_INFO_EXTRA", "CUST_INFO.NO", "=", "CUST_INFO_EXTRA.CUST_INFO_NO");
        $LOAN->SELECT("LOAN_INFO.CUST_INFO_NO, LOAN_INFO.NO, CUST_INFO.NAME, CUST_INFO.SSN, LOAN_INFO.LOAN_TYPE, LOAN_INFO.PATH_CD, LOAN_INFO.AGENT_CD, LOAN_INFO.PRO_CD, LOAN_INFO.LOAN_DATE, LOAN_INFO.CONTRACT_END_DATE, LOAN_INFO.FULLPAY_DATE, LOAN_INFO.LIMIT_MONEY, LOAN_INFO.FULLPAY_CD, LOAN_INFO.FULLPAY_ORIGIN");
        $LOAN->WHERE('CUST_INFO.SAVE_STATUS','Y');
        $LOAN->WHERE('LOAN_INFO.SAVE_STATUS','Y');
        $LOAN->WHERE('LOAN_INFO.STATUS','E');

        // 전지점 조회권한 없으면 자기 지점만
        if( !Func::funcCheckPermit("E004") && !isset($request->excel_flag) )
        {
            $LOAN->WHEREIN('LOAN_INFO.MANAGER_CODE', array_keys(Func::myPermitBranch()));
        }
                
        $list   = $this->setDataList($request);
        $param  = $request->all();
        $LOAN   = $list->getListQuery("LOAN_INFO","main",$LOAN,$param);

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN, $request->nowPage, $request->listLimit, 10, $request->listName);
        }
        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($LOAN);
        $file_name    = "완제계약명세_".date("YmdHis").'_'.Auth::id().'.xlsx';
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
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","CUST_INFO_EXTRA"], $rslt);	// CHUNG DATABASE DECRYPT
        
        // 엑셀 헤더
		$excel_header = array('고객번호','계약번호','이름','생년월일','대출종류','신청경로','중개사','상품명','계약일','만기일','완제일','거래일수','완제사유','최종완납원금',);


        $array_pro_cd   = Func::getConfigArr('pro_cd');
        $array_config   = Func::getConfigArr();
        $getStatus      = Vars::$arrayContractStaColor;

        foreach ($rslt as $v)
        {
            $array_data = [
                Func::addCi($v->cust_info_no),
                $v->no,
                $v->name,
                substr($v->ssn, 0, 6),
                Func::getArrayName($array_config['app_type_cd'], $v->loan_type),
                Func::getArrayName($array_config['path_cd'], $v->path_cd),
                Func::getArrayName($array_agent_cd, $v->agent_cd),
                Func::getArrayName($array_pro_cd, $v->pro_cd),
                Func::dateFormat($v->loan_date),
                Func::dateFormat($v->contract_end_date),
                Func::dateFormat($v->fullpay_date),
                Func::dateTerm($v->loan_date, $v->fullpay_date),
                Func::nvl($array_config['flpay_cd'][$v->fullpay_cd],$v->fullpay_cd),
                number_format($v->fullpay_origin),
            ];
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
}
