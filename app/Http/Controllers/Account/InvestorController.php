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

class InvestorController extends Controller
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
     * 투자자명세 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataInvestorList(Request $request){

        $configArr = Func::getConfigArr();

        $list   = new DataList(Array("listName"=>"investor","listAction"=>'/'.$request->path()));
        
        if(!isset($request->tabs)) $request->tabs = 'Y';
        $list->setTabs(Array(
            'Y'=>'유효', 'N'=>'비유효'), $request->tabs);

        $list->setCheckBox("no");

        $list->setPlusButton("setInvestorInputForm('')");
        
        $list->setLumpForm('DEL', Array('BTN_NAME'=>'삭제','BTN_ACTION'=>'lump_del(this);','BTN_ICON'=>'','BTN_COLOR'=>''));

        $list->setButtonArray("엑셀다운","excelDownModal('/account/investorexcel','form_investor')","btn-success");
        
        $list->setSearchDetail(Array( 
            'nick_name'   => '이름',
            'investor_no' => '투자자번호',
            'ssn'         => '주민번호',
        ));

        return $list;
    }

    /**
     * 투자자명세창 메인
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investor(Request $request)
    {
        $list   = $this->setDataInvestorList($request);

        $list->setLumpForm('DEL', Array('BTN_NAME'=>'삭제','BTN_ACTION'=>'lump_del(this)','BTN_ICON'=>'','BTN_COLOR'=>''));

        $list->setlistTitleCommon(Array
        (
            'investor_no'       => Array('투자자번호', 0, '', 'center', '', 'investor_no'), 
            'name'              => Array('이름', 0, '', 'center', '', 'name'),
            'ssn'               => Array('주민/법인번호', 0, '', 'center', '', 'ssn'),
            'ph1'               => Array('휴대폰', 0, '', 'center', '', ''),
            'company_yn'        => Array('개인/기업 구분', 0, '', 'center', '', ''),
            'save_id'           => Array('작업자', 0, '', 'center', '', 'save_id', ['save_time'=>['저장시간', 'save_time', '<br>']]),
        )); 
        return view('account.investor')->with('result', $list->getList());
    }   
    
    /**
     * 투자자현황조회 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function investorList(Request $request)
    {         
        $list  = $this->setDataInvestorList($request);
        $param = $request->all();

        // Tab count 
		if($request->isFirst=='1')
		{
            $BOXC = DB::table("loan_usr_info");
            $BOXC->SELECT(DB::RAW("coalesce(sum(case when loan_usr_info.save_status='Y' then 1 else 0 end),0) as Y
                                , coalesce(sum(case when loan_usr_info.save_status='N' then 1 else 0 end),0) as N"));            
            $vcnt = $BOXC->FIRST();
            Log::info("#########쿼리 확인 :".Func::printQuery($BOXC));
			$r['tabCount'] = array_change_key_case((Array) $vcnt, CASE_UPPER);
		}

        // 기본쿼리
        $USR = DB::table("loan_usr_info");

        if( $request->tabsSelect=="Y" )
		{
            $param['tabSelectNm'] = "loan_usr_info.save_status";
            $param['tabsSelect']  = Array('Y');
        }
        else if( $request->tabsSelect=="N" )
		{
            $param['tabSelectNm'] = "loan_usr_info.save_status";
            $param['tabsSelect']  = Array('N');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='investor_no' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='nick_name' && !empty($param['searchString']) )
        {
            $USR = $USR->where('nick_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        if(empty($param['listOrder']) && empty($param['listOrderAsc']))
        {
            $param['listOrder']    = 'investor_no';
            $param['listOrderAsc'] = 'desc';
        }

        $USR = $list->getListQuery('loan_usr_info', 'main', $USR, $param);

        $target_sql = urlencode(encrypt(Func::printQuery($USR))); // 페이지 들어가기 전에 쿼리를 저장해야한다.
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($USR, $request->page, $request->listLimit, 10, $request->listName);

        //$paging = new Paging($USR, $request->page, $request->listLimit, 10, $request->listName, '', $sum_data);
        $rslt = $USR->GET();
        $rslt = Func::chungDec(["loan_usr_info"], $rslt);	// CHUNG DATABASE DECRYPT

        $arrManager        = Func::getUserList();
        $configArr         = Func::getConfigArr();

        // 리스트 배열을 담기위해 한번 돌리고 돌린다.
        $array_rslt = [];
        foreach ($rslt as $v)
        {
            $array_rslt[$v->no] = $v;
        }

        $nos = ( sizeof($array_rslt)>0 && sizeof($array_rslt)<=1000 ) ? urlencode(encrypt(gzcompress(implode("|",array_keys($array_rslt)), 9))) : "" ;
        $cnt = 0;
        $total = ($request->listLimit>1000) ? 1000:$request->listLimit;

        foreach ($array_rslt as $v)
        {
            if($v->save_status == 'Y')
            {
                $v->onclick          = 'popUpFull(\'/account/investorpop?no='.$v->no.'\', \'investor'.$v->no.'\')';
                $v->line_style       = 'cursor: pointer;';
            }
            
            $v->ssn              = Func::ssnFormat($v->ssn, 'A');
            $v->name             = $v->name;
            $v->ph1              = Func::phMasking($v->ph11,$v->ph12,$v->ph13);
            $v->company_yn       = $v->company_yn=='Y'?"기업" : "개인";          
            $v->save_id          = isset($arrManager[$v->save_id]) ? Func::nvl($arrManager[$v->save_id]->name, $v->save_id) : $v->save_id;
            $v->save_time        = Func::dateFormat($v->save_time);

            $r['v'][] = $v;
            $cnt ++;
        }
        // 페이징
        $r['pageList']  = $paging->getPagingHtml($request->path());
        $r['result']    = 1;
        $r['txt']       = $cnt;
        $r['targetSql'] = $target_sql;
        $r['totalCnt']  = $paging->getTotalCnt();
		// $r['clcltData'] = $clcltData;
        return json_encode($r);
    }

    /**
     * 투자자정보 - 팝업창
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investorPop(Request $request)
    {
        $status_color = "#6c757d";
        $no = $request->no;
        $lui = DB::TABLE("loan_usr_info")->SELECT("*")->WHERE('save_status','Y')->WHERE("no", $no)->first();
        $lui = Func::chungDec(["loan_usr_info"], $lui);	// CHUNG DATABASE DECRYPT

        Func::setMemberAccessLog('투자자정보 조회', $request->ip(), $request->path(), $request->userAgent(), null, null, null, $no);

        return view('account.investorPop')->with("lui", $lui)->with("status_color", $status_color);
    }

    /**
     * 투자자정보 팝업창 - 상세정보
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investorInfo(Request $request)
    {
        $array_user         = Func::getUserId();
        $configArr          = Func::getConfigArr();

        $no = $request->loan_usr_info_no;

        $lui = DB::table("loan_usr_info")
                        ->select("*")
                        ->where('save_status', 'Y')
                        ->where('no', $no)
                        ->first();

        $lui = Func::chungDec(["loan_usr_info"], $lui); // CHUNG DATABASE DECRYPT

        if(empty($lui->tax_free)) $lui->tax_free = "N";                             // 면세여부 변경체크를 위해 데이터가 빈값일 경우 N으로 기본 설정

        $lui->reg_time = Func::dateFormat2($lui->reg_time);
        $lui->ssn      = Func::ssnFormat($lui->ssn,'A');

        $mode = "UPD";

        return view('account.investorInfo')->with('lui',$lui)
                                    ->with('array_user',$array_user)
                                    ->with('mode',$mode)
                                    ->with('configArr',$configArr);
    }

    /**
     * 엑셀다운로드 (투자자명세)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investorExcel(Request $request)
    {
        if( !Func::funcCheckPermit("U002") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }

        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');

        $list           = $this->setDataInvestorList($request);
        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        // 기본쿼리
        $USR = DB::table("loan_usr_info");

        if( $request->tabsSelect=="Y" )
		{
            $param['tabSelectNm'] = "loan_usr_info.save_status";
            $param['tabsSelect']  = Array('Y');
        }
        else if( $request->tabsSelect=="N" )
		{
            $param['tabSelectNm'] = "loan_usr_info.save_status";
            $param['tabsSelect']  = Array('N');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='investor_no' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='nick_name' && !empty($param['searchString']) )
        {
            $USR = $USR->where('nick_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $USR = $list->getListQuery('loan_usr_info', 'main', $USR, $param);
        $USR->orderBy("loan_usr_info.investor_no", "desc");

        $target_sql = urlencode(encrypt(Func::printQuery($USR))); // 페이지 들어가기 전에 쿼리를 저장해야한다.                
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($USR, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "투자자명세_".date("YmdHis").'_'.Auth::id().'.xlsx';
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

        $rslt = $USR->get();
        $rslt = Func::chungDec(["loan_usr_info"], $rslt);	// CHUNG DATABASE DECRYPT
        
        // 엑셀 헤더
		$excel_header   = array('No','투자자번호','이름','주민/법인번호','휴대폰','개인/기업 구분','작업자','저장시간');
        $excel_data     = [];

        $array_config   = Func::getConfigArr();
        $getStatus      = Vars::$arrayContractSta;
        $arrBranch      = Func::getBranch();
        $arrManager     = Func::getUserList();

        $board_count = 1;
        foreach ($rslt as $v)
        {
            $array_data = [
                $board_count,
                $v->investor_no,
                $v->name,
                Func::ssnFormat($v->ssn,'A'),
                Func::phFormat($v->ph11,$v->ph12,$v->ph13),
                $v->company_yn=='Y'?"기업" : "개인",
                isset($arrManager[$v->save_id]) ? Func::nvl($arrManager[$v->save_id]->name, $v->save_id) : $v->save_id,
                Func::dateFormat($v->save_time),
            ];

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
     * 투자자정보 저장
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function investorAction(Request $request)
    {
        $ARR                = $request->all();
        $ARR['save_status'] = "Y";
        $ARR['save_time']   = date("YmdHis");
        $ARR['save_id']     = Auth::id();
        $ARR['ssn']         = str_replace("-" ,"", $ARR['ssn']);
        $ARR['tax_free']    = (isset($ARR['tax_free']) && !empty($ARR['tax_free'])) ? $ARR['tax_free'] : "N";   // 면세여부

        // 트랜잭션
        DB::beginTransaction();

        if($request->mode == "UPD")
        {
            $no = $request->no;
            $usrInfo = DB::TABLE('loan_usr_info')->SELECT('tax_free')->WHERE('no',$no)->FIRST();
            $old_tax_free = $usrInfo->tax_free;

            $rslt = DB::dataProcess('UPD', 'loan_usr_info', $ARR, ["no"=>$no]);
            if($rslt != "Y")
            {
                DB::rollback();

                $array_result['result_msg'] = "투자자정보 업데이트 처리에 실패하였습니다.";
                $array_result['rs_code'] = "N";

                return $array_result;
            }
        }
        else if($request->mode == "INS")
        {
            
            $cnt = DB::table('loan_usr_info')->where('ssn', Func::encrypt($ARR['ssn'], 'ENC_KEY_SOL'))->count();
            if($cnt > 0 )
            {
                $array_result['result_msg'] = "기등록된 주민번호입니다.";
                $array_result['rs_code'] = "N";
                DB::rollback();
                return $array_result;
            }

            // 비유효건도 확인해야하므로 save_status 조건제외
            $max_ino = DB::table('loan_usr_info')->select('max(investor_no) as inv_no')->first();
            $ARR['investor_no'] = ( $max_ino->inv_no ) ? $max_ino->inv_no + 1 : 1 ;
            $ARR['in_name']     = $ARR['nick_name'] = $ARR['name'];
            
            $rslt = DB::dataProcess('INS', 'loan_usr_info', $ARR);
        }
        
        $array_result['mode'] = $request->mode;

        if(isset($rslt) && $rslt == "Y")
        {
            DB::commit();

            $array_result['result_msg'] = "정상처리 되었습니다.";
            $array_result['rs_code'] = "Y";
        }
        else
        {
            DB::rollback();

            $array_result['result_msg'] = "처리에 실패하였습니다.";
            $array_result['rs_code'] = "N";
        }
        
        return $array_result;
    }

    /**
     * 고객정보창 메인
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investorInputForm(Request $request)
    {
        $configArr = Func::getConfigArr();     

        return view('account.investorInputForm')->with(['mode'=>'INS', 'configArr' => $configArr]);
    }

    /**
     * 투자자정보창 변경내역
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function investorInfoChange(Request $request)
    {
        $array_bank_cd       = Func::getConfigArr('bank_cd');
        $array_user_id       = Func::getUserId();
        $loan_usr_info_no = $request->loan_usr_info_no;
        
        // 기본쿼리
        $HIST = DB::TABLE("loan_usr_info_log l")
                    ->JOIN("loan_usr_info ui", "l.loan_usr_info_no", "=", "ui.no")
                    ->SELECT("l.*", "ui.name")
                    ->WHERE('ui.no', $loan_usr_info_no);   
        
        if(empty($request->selected))
        {
            $selected = 'addr11';
        }
        else
        {
            $selected = $request->selected;
        }
        
        if( $selected=="relation" )
        {
            $where_raw = "((l.relation!= '' and l.relation is not null))";
            $pre_select = "relation";
        }
        else if( $selected=="email" )
        {
            $where_raw = "((l.email!= '' and l.email is not null))";
            $pre_select = "l.email";
        }
        else if( $selected=="com_ssn" )
        {
            $where_raw = "((l.com_ssn!= '' and l.com_ssn is not null))";
            $pre_select = "com_ssn";
        }
        else if( $selected=="ph11" )
        {
            $where_raw = "((l.ph11!= '' and l.ph11 is not null) and (l.ph12!= '' and l.ph12 is not null) and (l.ph13!= '' and l.ph13 is not null))";
            $pre_select = "ph11, ph12, ph13";
        }
        else if( $selected=="ph21" )
        {
            $where_raw = "((l.ph21!= '' and l.ph21 is not null) and (l.ph22!= '' and l.ph22 is not null) and (l.ph23!= '' and l.ph23 is not null))";
            $pre_select = "ph21, ph22, ph23";
        }
        else if( $selected=="ph41" )
        {
            $where_raw = "((l.ph41!= '' and l.ph41 is not null) and (l.ph42!= '' and l.ph42 is not null) and (l.ph43!= '' and l.ph43 is not null))";
            $pre_select = "ph41, ph42, ph43";
        }
        else if($selected=="addr11")
        {
            $where_raw = " ((l.zip1!= '' and l.zip1 is not null) and (l.addr11!= '' and l.addr11 is not null) and (l.addr12!= '' and l.addr12 is not null)) ";
            $pre_select = "zip1 as zip11, addr11, addr12";
        }
        else if( $selected=="addr21" )
        {
            $where_raw = "((l.zip2!= '' and l.zip2 is not null) and (l.addr21!= '' and l.addr21 is not null) and (l.addr22!= '' and l.addr22 is not null))";
            $pre_select = "zip2 as zip21, addr21, addr22";
        }
        else if( $selected=="bank11" )
        {
            $where_raw = "((l.bank_cd!= '' and l.bank_cd is not null) or (l.bank_ssn!= '' and l.bank_ssn is not null)) ";
            $pre_select = "bank_cd as bank11, bank_ssn as bank12";
        }
        else if( $selected=="bank21" )
        {
            $where_raw = "((l.bank_cd2!= '' and l.bank_cd2 is not null) or (l.bank_ssn2!= '' and l.bank_ssn2 is not null)) ";
            $pre_select = "bank_cd2 as bank21 , bank_ssn2 as bank22";
        }
        else if( $selected=="bank31" )
        {
            $where_raw = "((l.bank_cd3!= '' and l.bank_cd3 is not null) or (l.bank_ssn3!= '' and l.bank_ssn3 is not null)) ";
            $pre_select = "bank_cd3 as bank31, bank_ssn3 as bank32";
        }
        else if( $selected=="memo" )
        {
            $where_raw = "((l.memo!= '' and l.memo is not null))";
            $pre_select = "memo";
        }
        else if( $selected=="name" )
        {
            $where_raw = "((l.name!= '' and l.name is not null))";
            $pre_select = "name";
        }
        else if( $selected=="ssn" )
        {
            $where_raw = "((l.ssn!= '' and l.ssn is not null))";
            $pre_select = "ssn";
        }
        
        $HIST->WHERERAW($where_raw);
        $HIST->ORDERBY('seq', 'desc');

        $rslt   = $HIST->GET();
        $rslt = Func::chungDec(["LOAN_USR_INFO_LOG","LOAN_USR_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        $configArr     = Func::getConfigArr();
        $array_key_chk = Array('relation', 'memo', 'bank11', 'bank21', 'bank31', 'zip11', 'zip21', 'addr11', 'addr21');

        $cnt = 0;
        foreach( $rslt as $v )
        {
            $v->ph11              = $v->ph11."-".$v->ph12."-".$v->ph13;
            $v->ph21              = $v->ph21."-".$v->ph22."-".$v->ph23;
            $v->ph41              = $v->ph41."-".$v->ph42."-".$v->ph43;
            $v->addr11            = $v->zip1." ".$v->addr11." ".$v->addr12;
            $v->addr21            = $v->zip2." ".$v->addr21." ".$v->addr22;
            $v->bank11             = Func::nvl($array_bank_cd[$v->bank_cd], $v->bank_cd)." / ".$v->bank_ssn;
            $v->bank21             = Func::nvl($array_bank_cd[$v->bank_cd2], $v->bank_cd2)." / ".$v->bank_ssn2;
            $v->bank31             = Func::nvl($array_bank_cd[$v->bank_cd3], $v->bank_cd3)." / ".$v->bank_ssn3;
            $v->save_id           = $v->save_id ? Func::getArrayName($array_user_id, $v->save_id) : '';
            $v->save_time         = Func::dateFormat($v->save_time);
            $v->ssn               = Func::ssnFormat($v->ssn,'A');

            $HIST_PRE = DB::TABLE("loan_usr_info_log l")
                        ->SELECTRAW($pre_select)
                        ->WHERERAW($where_raw)
                        ->WHERERAW("loan_usr_info_no = '".$v->loan_usr_info_no."' and seq < ".$v->seq)
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

        if(empty($r))
        {
            $r['v'][] = '';
        }

        $array_select = Array(
            'addr11'        =>"주소1",
            'addr21'        =>"주소2",
            'ph11'          =>"전화번호1",
            'ph21'          =>"전화번호2",
            'ph41'          =>"전화번호3", 
            'bank11'       =>"은행/계좌번호1",
            'bank21'      =>"은행/계좌번호2",
            'bank31'      =>"은행/계좌번호3",      
            'name'          =>"이름",       
            'ssn'           =>"주민등록번호",  
            'relation'     =>"관계",
            'memo'          =>"메모",
        );
        


        return view('account.investorInfoChange')
                                    ->with('array_select',$array_select)
                                    ->with('array_user', Func::getUserId())
                                    ->with('array_post_send_cd',Func::getConfigArr('addr_cd'))
                                    ->with('selected', $selected)
                                    ->with('r', $r);
    }

    /**
     * 투자자정보창 이미지
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function investorInfoImage(Request $request)
    {
        $arr_lon_div = Vars::$arrayLonDoc;
        $arr_cot_div = Vars::$arrayCotDoc;
        $arr_etc_div = Vars::$arrayEtcDoc;
        $arr_img_div = $arr_lon_div + $arr_cot_div + $arr_etc_div;

        $arr_task_name        = Vars::$arrayTaskName;
        $arr_image_div        = Vars::$arrayImageUploadDivision;
        $arr_image_div_select = Vars::$arrayImageUploadDivisionSelect;
        $arrManager           = Func::getUserList();
        $arrPdName            = array();
        $arrPdInvNoList       = "";

        $img = DB::TABLE("loan_usr_info_img")->leftjoin('loan_info', 'loan_usr_info_img.loan_info_no', '=', 'loan_info.no')
                                            ->SELECT("loan_usr_info_img.*", "loan_info.inv_seq", "loan_info.investor_no", "loan_info.investor_type")
                                            ->WHERE('loan_usr_info_img.save_status','Y')
                                            ->ORDERBY('loan_usr_info_img.save_time', 'desc');

        if(isset($request->loan_usr_info_no))
        {
            $loan_usr_info_no = $request->loan_usr_info_no;
            $img = $img->WHERE('loan_usr_info_img.loan_usr_info_no', $loan_usr_info_no)->get()->toArray();
            $img = Func::chungDec(["loan_usr_info_img"], $img);	// CHUNG DATABASE DECRYPT
            $invNo = DB::table('loan_info')->select('no')
                                            ->where('save_status', 'Y')
                                            ->where('loan_usr_info_no', $loan_usr_info_no)
                                            ->where('save_status', 'Y')
                                            ->where('status', '!=', 'N')
                                            ->orderby('no', 'desc')
                                            ->get();
            $arrInvNo = Array();
            foreach($invNo as $inv){
                $arrInvNo[$inv->no] = $inv->no;
            }
            
            $arrInvNo['0'] = 0;
        }

        $selected_img = array();
        if(isset($request->selected))
        {
            $mode = "UPD";
            foreach( $img as $key=>$value )
            {
                if($value->loan_info_no > 0)
                {
                    $loan_sql = DB::TABLE("loan_info")->SELECT("no")->WHERE('no',$value->loan_info_no)->WHERE('save_status','Y')->FIRST();

                    $value->loan_info_no = $loan_sql->no;
                }
                else
                {
                    $value->loan_info_no = '0';
                }

                $value->save_time   = date("Y-m-d H:i:s", strtotime($value->save_time));
                $value->save_id   = isset($arrManager[$value->save_id]) ? Func::nvl($arrManager[$value->save_id]->name, $value->save_id) : $value->save_id;
                // $value->trade_date  = Func::dateFormat($value->trade_date);
                // $value->trade_money = number_format($value->trade_money);

                if( $value->no == $request->selected )
                {
                    $selected_img[0] = $img[$key];
                }
            }
        }
        else
        {
            $mode = "INS";
            foreach( $img as $key=>$value )
            {
                if($value->loan_info_no > 0)
                {
                    $loan_sql = DB::TABLE("loan_info")->SELECT("no")->WHERE('no',$value->loan_info_no)->WHERE('save_status','Y')->FIRST();

                    $value->loan_info_no = $loan_sql->no;
                }
                else
                {
                    $value->loan_info_no = '0';
                }

                $value->save_id   = isset($arrManager[$value->save_id]) ? Func::nvl($arrManager[$value->save_id]->name, $value->save_id) : $value->save_id ;
                $value->save_time   = date("Y-m-d H:i:s", strtotime($value->save_time));
                // $value->trade_date  = Func::dateFormat($value->trade_date);
                // $value->trade_money = number_format($value->trade_money);
            }
        }

        $arrPdInvNoList = json_encode($arrPdName, JSON_UNESCAPED_UNICODE);

        return view('account.investorInfoImage')->with('arr_lon_div',$arr_lon_div)
                                            ->with('arr_cot_div',$arr_cot_div)
                                            ->with('arr_img_div',$arr_img_div)
                                            ->with('arr_image_div_select',$arr_image_div_select)
                                            ->with('arr_etc_div',$arr_etc_div)
                                            ->with('arr_pd_name',$arrPdName)
                                            ->with('arr_pd_loan_no_list',$arrPdInvNoList)
                                            ->with('arr_loan_no',$arrInvNo)
                                            ->with('arr_task_name',$arr_task_name)
                                            ->with('arr_image_div',$arr_image_div)
                                            ->with('img',$img)
                                            ->with('loan_usr_info_no', $loan_usr_info_no)
                                            ->with('mode', $mode)
                                            ->with('selected_img', $selected_img);
    }

    /**
    * 고객정보창 이미지 액션
    *
    * @param  \Illuminate\Http\Request  $request
    * @return String
    */
    public function investorInfoImageAction(Request $request)
    {
        $_DATA = $request->input();
        $arrayTaskId        = Vars::getTaskId();
        $arrayEcmResultCode = Vars::$arrayEcmResultCode;
        $arraySaveFolder    = Vars::getSaveFolder();
        $arr_lon_div        = Vars::$arrayLonDoc;
        $arr_cot_div        = Vars::$arrayCotDoc;
        $arr_etc_div        = Vars::$arrayEtcDoc;
        $arr_img_div        = $arr_lon_div + $arr_cot_div + $arr_etc_div;
        $param              = $request->all();

        $_DATA['save_id'] = Auth::id();
        $_DATA['save_time'] = date("YmdHis");

        // 폴더 생성
        $folder = date("Ymd");
        $fileName = date("YmdHis")."_".sprintf("%07d", $request->loan_usr_info_no);

        // 투자자번호 조회
        if(!empty($request->loan_usr_info_no))
        {
            $usr = DB::TABLE("loan_usr_info")->SELECT('no')->WHERE('no', $param['loan_usr_info_no'])->FIRST();
            $loan_usr_info_no = $usr->no;
        }
        else 
        {
            $loan_usr_info_no = "0";
        }

        // 계약번호 조회
        if(!empty($request->loan_info_no))
        {
            $loan = DB::TABLE("loan_info")->SELECT('no')->WHERE('no', $param['loan_info_no'])->FIRST();
            $loan_info_no = $loan->no;
        }
        else
        {
            $loan_info_no = '0';
        }
        
        if( $_DATA['mode'] == "INS" )
        {
            $_DATA['save_status'] = "Y";
            
            // $no = 0;
            DB::beginTransaction();
            unset($_DATA['no']);

            // 업로드할 파일이 존재하는 경우
            if( $request->file('customFile') )
            {
                $filePath = $request->file('customFile')->storeAs($folder, $fileName,'erp_data_usr_img');
                
                $_DATA['origin_filename'] = $request->file('customFile')->getClientOriginalName();
                $_DATA['filename']        = $fileName;
                $_DATA['folder_name']     = $folder;
                $_DATA['file_path']       = $filePath;
                $_DATA['extension']       = $request->file('customFile')->guessExtension();
                $_DATA['save_id']       = Auth::id();
                $_DATA['save_time']       = date("YmdHis");
                $_DATA['save_status']     = "Y";
                $_DATA['loan_usr_info_no']     = $loan_usr_info_no;
                $_DATA['loan_info_no']    = $loan_info_no;
                if(isset($param['taskname']))$_DATA['taskname']        = $param['taskname'];

                $result = DB::dataProcess( "INS", "loan_usr_info_img", $_DATA );
                
                if( $result=="Y" )
                {
                    $msg = "정상적으로 처리되었습니다.";
                    DB::commit();
                }
                else
                {
                    $msg = "이미지 저장 실패.";
                    DB::rollBack();
                }
            }
            else
            {
                $msg = "파일을 선택해주세요.";
                DB::rollBack();
            }
        }
        else if( $_DATA['mode'] == "UPD" )
        {
            $_DATA['loan_usr_info_no']    = $loan_usr_info_no;
            if(isset($param['taskname']))$_DATA['taskname'] = $param['taskname'];

            //  파일 수정 시, 파일 삭제는 하지 않는다.
            if( $request->file('customFile') )
            {
                DB::beginTransaction();

                $rslt = DB::TABLE('loan_usr_info_img')->SELECT('filename', 'taskname', 'img_div_cd', 'file_path','save_id')->WHERE('save_status', 'Y')->WHERE('no', $_DATA['no'])->FIRST();
                $rslt = Func::chungDec(["loan_usr_info_img"], $rslt);	// CHUNG DATABASE DECRYPT

                // 기존 파일명
                if(isset($rslt->filename))
                {
                    $filePath = $request->file('customFile')->storeAs($folder, $fileName,'erp_data_usr_img');

                    $_DATA['save_status'] = "Y";
                    $_DATA['origin_filename'] = $request->file('customFile')->getClientOriginalName();
                    $_DATA['filename']        = $fileName;
                    $_DATA['folder_name']     = $folder;
                    $_DATA['file_path']       = $filePath;
                    $_DATA['extension']       = $request->file('customFile')->guessExtension();

                    $result = DB::dataProcess("UPD", "loan_usr_info_img", $_DATA);
    
                    if( $result=="Y" )
                    {
                        $msg = "정상적으로 처리되었습니다.";
                        DB::commit();
                    }
                    else
                    {
                        $msg = "이미지 저장 실패.";
                        DB::rollBack();
                    }
                }
                else
                {
                    $msg = "기존 데이터 수정 실패.";
                    DB::rollback();
                }
            }
            else
            {
                $result = DB::dataProcess("UPD", "loan_usr_info_img", $_DATA);

                if( $result == "Y" )
                {
                    $msg = "정상적으로 처리되었습니다.";
                    DB::commit();
                }
                else
                {
                    $msg = "데이터 수정 실패";
                    DB::rollback();
                }
            }
        }
        else if( $_DATA['mode'] == "DEL" )
        {                
            $_DEL_DATA['no']          = $_DATA['no'];
            $_DEL_DATA['del_id']      = Auth::id();
            $_DEL_DATA['del_time']    = date("YmdHis");
            $_DEL_DATA['save_status'] = "N";

            DB::beginTransaction();
            $_IMG = DB::TABLE('loan_usr_info_img')->SELECT('filename', 'file_path')->WHERE('no', $_DATA['no'])->first();
            $_IMG = Func::chungDec(["loan_usr_info_img"], $_IMG);	// CHUNG DATABASE DECRYPT

            if(isset($_IMG->filename))
            {
                // 기존파일 삭제
                $exists = Storage::disk('erp_data_usr_img')->exists($_IMG->file_path);
                if( $exists )
                {
                    Storage::disk('erp_data_usr_img')->delete($_IMG->file_path);
                }

                $result = DB::dataProcess("UPD", "loan_usr_info_img", $_DEL_DATA);

                if( $result == "Y" )
                {
                    $rs_code = 'Y';
                    $msg = "정상적으로 처리되었습니다.";
                    DB::commit();
                }
                else
                {
                    $msg = "데이터 삭제 실패";
                    DB::rollback();
                }
            }
            else
            {
                $msg = "파일 미존재";
                DB::rollback();
            }
        }
        else
        {
            $msg = "데이터 오류";
        }

        return $msg;
    } 
    
    /**
     * loan_usr_info_img Image 파일 경로 가져오기
     *
     * @param  \Illuminate\Http\Request  $request
     * @return 
     */
    public function getInvestorInfoImg(Request $request)
    {
        //  no값이나 고객번호가 없으면 ERROR
        if( !(isset($request->no) && isset($request->loan_usr_info_no)) )
        {
            return "E";
        }
        
        $_IMG = DB::TABLE("loan_usr_info_img")->SELECT("*")->WHERE("save_status", "Y")->WHERE("no", $request->no)->WHERE("loan_usr_info_no", $request->loan_usr_info_no)->first();
        // Log::info($_IMG);
        // $_IMG = Func::chungDec(["CUST_INFO_IMG"], $_IMG);	// CHUNG DATABASE DECRYPT
        
        $exists = Storage::disk('erp_data_usr_img')->exists($_IMG->file_path);
        //  파일 존재 유무 확인
        if( !$exists )
        {
            return "E";
        }

        Log::debug($_IMG->file_path);

        return Storage::disk('erp_data_usr_img')->get($_IMG->file_path);
    }

    /**
     * loan_usr_info_img Image 파일 다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return 
     */
    public function downInvestorImg($dataNo)
    {
        Log::debug("다운로드 시작 : [".$dataNo."]");

        //  NO 값이 없으면 ERROR
        if( !(isset($dataNo) ) )
        {
            Log::debug("No 값 없음 : [".$dataNo."]");
            return;
        }

        $_IMG = DB::TABLE("loan_usr_info_img")->SELECT("origin_filename, filename, file_path")->WHERE("save_status", "Y")->WHERE("no", $dataNo)->first();
        $_IMG = Func::chungDec(["loan_usr_info_img"], $_IMG);	// CHUNG DATABASE DECRYPT

        if(isset($_IMG->filename))
        {
            $exists = Storage::disk('erp_data_usr_img')->exists($_IMG->file_path);
            if( !$exists )
            {
                Log::debug("파일 없음 : [".print_r($_IMG, true)."]");
                return;
            }

            $response = Response::make(Storage::disk('erp_data_usr_img')->get($_IMG->file_path), 200);
            $response->header('Content-Type', Storage::disk('erp_data_usr_img')->mimeType($_IMG->file_path));
            return $response;
        }
        else
        {
            Log::debug("파일 데이터 없음 : [".print_r($_IMG, true)."]");
            return;
        }
    }

    /**
     * 고객정보창 이미지 TASK 구분값
     */
    public function usrImagePriview(Request $request)
    {
        return view('account/investorImgPreview')
                ->with('no', $request->no)
                ->with('loan_usr_info_no', $request->loan_usr_info_no)
                ->with('ext', $request->ext);
    }

    /**
     * 투자자정보 팝업창 - 투자내역
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investorInfoDetail(Request $request)
    {
        $array_user         = Func::getUserId();
        $configArr          = Func::getConfigArr();

        $no = $request->loan_usr_info_no;
        $investor_no = $request->loan_usr_info_investor_no;
        $loan_info_no = $request->selected;

        $v = $plan = [];
        
        if(is_numeric($loan_info_no))
        {
            // 메인쿼리
            $v = DB::TABLE("loan_info")->join('loan_usr_info', 'loan_info.loan_usr_info_no', '=', 'loan_usr_info.no')
                                            ->SELECT("LOAN_USR_INFO.relation, LOAN_USR_INFO.PH11, LOAN_USR_INFO.PH12, LOAN_USR_INFO.PH13, LOAN_USR_INFO.BANK_CD, LOAN_USR_INFO.BANK_SSN, LOAN_USR_INFO.BANK_CD2, LOAN_USR_INFO.BANK_SSN2, LOAN_USR_INFO.BANK_CD3, LOAN_USR_INFO.BANK_SSN3, LOAN_INFO.STATUS LOAN_STATUS, LOAN_USR_INFO.NAME, loan_info.*")
                                            ->WHERE('LOAN_USR_INFO.SAVE_STATUS','Y')
                                            ->WHERE('loan_info.SAVE_STATUS','Y')
                                            ->WHERE('loan_info.no', $loan_info_no)
                                            ->FIRST();

            $v = Func::chungDec(["loan_info", "LOAN_USR_INFO"], $v);	// CHUNG DATABASE DECRYPT

            $plan = DB::TABLE("loan_info_trade")->join("loan_info","loan_info.no","=","loan_info_trade.loan_info_no")
                                        ->SELECT("loan_info_trade.*, loan_info.loan_usr_info_no, loan_info.no", "loan_info.inv_seq", "loan_info.investor_no", "loan_info.loan_money")
                                        ->WHERE('loan_info.save_status','Y')
                                        ->WHERE('loan_info_trade.save_status','Y')
                                        ->WHERE('loan_info.no',$loan_info_no)
                                        ->ORDERBY('loan_info_trade.no')
                                        ->get();

            $plan = Func::chungDec(["loan_info_trade","loan_info"], $plan);	// CHUNG DATABASE DECRYPT
        }

        // listName : 리스트 이름 (표시 x)
        $result['listName'] = 'investorinfodetail';
        // listAction : 리스트 url - ajax 요청주소 ( '/'.$request->path() )
        $result['listAction'] = '/'.$request->path();

        // tabs : 탭 사용 여부 (Y, N)
        $result['tabs'] = 'N';

        // button : 버튼 추가 여부 (Y, N)
        $result['button'] = 'N';

        // searchDate : 일자검색 여부 (Y, N)
        $result['searchDate'] = 'N';
        // searchType : select 검색 여부 (Y, N) [searchDetail과 다른 점은 input 입력하는 부분이 없다.]
        $result['searchType'] = 'N';

        // searchDetail : 검색 사용 여부 (Y, N)
        $result['searchDetail'] = 'X';
        // searchButton : 검색버튼 사용 여부 (Y, N)
        // isModal : 모달창 사용여부 (Y, N)
        $result['isModal'] = 'N';

        // plusButton : 등록 버튼 추가 여부 (Y, N)
        $result['plusButton'] = 'N';
        
        // customer : 번호
        $result['customer']['loan_usr_info_no'] = $no;

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $result['listTitle'] = Array
        (
            'investor_no_inv_seq'       => Array('채권번호', 0, '', 'center', '', ''),
            'status'                    => Array('투자상태', 1, '', 'center', '', 'status'),
            'pro_cd'                    => Array('상품구분', 1, '', 'center', '', 'pro_cd'),
            'contract_date'             => Array('투자일자', 1, '', 'center', '', 'contract_date'),
            'contract_end_date'         => Array('만기일자', 1, '', 'center', '', 'contract_end_date'),
            'loan_money'                => Array('최초투자금액', 1, '', 'center', '', 'loan_money'),
            'return_origin_sum'         => Array('투자원금상환액', 1, '', 'center', '', 'return_origin_sum'),
            'balance'                   => Array('투자잔액', 1, '', 'center', '', 'balance'),
            'invest_rate'               => Array('수익률', 1, '', 'center', '', 'invest_rate'),
            'pay_term'                  => Array('수익지급주기(월)', 1, '', 'center', '', 'pay_term'),
            'contract_day'              => Array('약정일', 1, '', 'center', '', 'contract_day'),
            'return_date'               => Array('차기수익지급일자', 1, '', 'center', '', 'return_date'),
            'return_money'              => Array('차기수익지급금액', 1, '', 'center', '', 'return_money'),
        );

        $result['page'] = $request->page ?? 1;

        return view('account.investorInfoDetail')->with('v', $v ?? [])
                                            ->with('plan', $plan ?? [])
                                            ->with('rates', $rates ?? [])
                                            ->with('array_user',$array_user ?? [])
                                            ->with("result",    $result ?? [])
                                            ->with('configArr',$configArr ?? [])
                                            ->with("userVar",   $no ?? '');
    }

    /**
     * 투자자정보 팝업창 - 투자내역
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investorInfoDetailList(Request $request)
    {
        $request_all = $request->all();

        // 메인쿼리
        $LOAN_LIST = DB::TABLE("loan_info")->join('loan_usr_info', 'loan_info.loan_usr_info_no', '=', 'loan_usr_info.no')
                                            ->SELECT("LOAN_USR_INFO.relation, LOAN_USR_INFO.PH11, LOAN_USR_INFO.PH12, LOAN_USR_INFO.PH13, LOAN_USR_INFO.BANK_CD, LOAN_USR_INFO.BANK_SSN, LOAN_USR_INFO.BANK_CD2, LOAN_USR_INFO.BANK_SSN2, LOAN_USR_INFO.BANK_CD3, LOAN_USR_INFO.BANK_SSN3, LOAN_INFO.STATUS LOAN_STATUS, LOAN_USR_INFO.NAME, LOAN_USR_INFO.INVESTOR_NO, loan_info.*", 
                                            DB::raw("(select count(1) from loan_usr_info_doc where loan_usr_info_doc.loan_info_no = loan_info.no and app_document_cd = '01' and save_id is not null and save_status = 'Y') as conp_cnt"), 
                                            DB::raw("(select count(1) from loan_usr_info_doc where loan_usr_info_doc.loan_info_no = loan_info.no and app_document_cd = '04' and save_id is not null and save_status = 'Y') as conep_cnt"), 
                                            DB::raw("(select count(1) from loan_usr_info_doc where loan_usr_info_doc.loan_info_no = loan_info.no and app_document_cd = '02' and save_id is not null and save_status = 'Y') as comp_cnt")
                                            )
                                            ->WHERE('LOAN_USR_INFO.SAVE_STATUS','Y')
                                            ->WHERE('loan_info.SAVE_STATUS','Y')
                                            ->WHERE('loan_info.loan_usr_info_no', $request_all['loan_usr_info_no']);

        // 정렬
        if($request->listOrder)
        {
            $LOAN_LIST = $LOAN_LIST->ORDERBY($request->listOrder, $request->listOrderAsc);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->ORDERBY('loan_info.no', 'desc');
        }
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN_LIST, $request->page, 20, 10);

        $rslt = $LOAN_LIST->GET();
        $rslt = Func::chungDec(["loan_info","LOAN_USR_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        $configArr         = Func::getConfigArr();
        $array_bank_cd     = Func::getConfigArr('bank_cd');
        $arrayUserId       = Func::getUserId();

        $cnt = 0;
        foreach ($rslt as $v)
        {
            $link_b                 = '<a class="hand" onClick="popUpFull(\'/account/investmentpop?no='.$v->no.'\', \'investment'.$v->no.'\')">';
            $v->investor_no_inv_seq = $link_b.$v->investor_type.$v->investor_no.'-'.$v->inv_seq;

            $link_c                 = '<a class="hand" onClick="getInvestorData(\'investorinfodetail\',\'\',\''.$v->no.'\',\'\',\'\',\'\',\''.$request->page.'\')">';
            $v->status              = $link_c.Func::getInvStatus($v->status, true);

            $v->name                = Func::nameMasking($v->name, 'Y');
            $v->loan_money          = number_format($v->loan_money);
            if($v->status != 'E')
            {
                $v->balance              = number_format($v->balance);
                $v->return_date          = Func::dateFormat($v->return_date);
                $v->return_money         = number_format($v->return_money);
            }
            else
            {
                $v->balance              = 0;
                $v->return_date          = '';
                $v->return_money         = 0;
            }
            $v->return_origin_sum   = number_format($v->return_origin_sum);
            $v->fullpay_money       = number_format($v->fullpay_money);
            $v->fullpay_date        = Func::dateFormat($v->fullpay_date);
            
            $v->contract_date       = Func::dateFormat($v->contract_date);
            $v->fullpay_date        = Func::dateFormat($v->fullpay_date);
            $v->ph1                 = Func::phMasking($v->ph11, $v->ph12, $v->ph13, 'Y');
            $v->contract_end_date   = Func::dateFormat($v->contract_end_date);
            $v->save_time           = Func::dateFormat($v->save_time);
            $v->save_id             = Func::getArrayName($arrayUserId, $v->save_id);
            $v->pro_cd              = Func::getArrayName($configArr['pro_cd'], $v->pro_cd);
            $v->invest_rate         = sprintf('%0.2f',$v->invest_rate);

            $r['v'][] = $v;
            $cnt ++;
        }
		
        // 페이징
        $r['pageList']  = $paging->getPagingHtml($request->path(), $request->listName);
        $r['result']    = 1;
        $r['txt']    = $cnt;

        return json_encode($r);
    }

    /**
     * 투자내역 수익분배전체 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investorInfoDetailExcel(Request $request)
    {
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');

        $param = $request->all();

        $down_filename  = '';
        $excel_down_div = 'E';
        $etc            = '';
        $excelDownCd    = '001';
        $loan_usr_info_no    = $param['loan_usr_info_no'];

        $plan = DB::TABLE("loan_info")->join("loan_info_return_plan","loan_info.no","=","loan_info_return_plan.loan_info_no")
                                        ->join("loan_usr_info","loan_usr_info.no","=","loan_info.loan_usr_info_no")
                                        ->SELECT("loan_info.no as loan_info_no, loan_info.loan_usr_info_no, loan_info.no,loan_info.sum_interest, loan_usr_info.name, loan_info_return_plan.*")
                                        ->WHERE('loan_usr_info.save_status','Y')
                                        ->WHERE('loan_info.save_status','Y')
                                        ->WHERE('loan_info.loan_usr_info_no',$loan_usr_info_no)
                                        ->ORDERBY('loan_info.no')
                                        ->ORDERBY('loan_info_return_plan.seq')
                                        ->ORDERBY('loan_info_return_plan.plan_date');


        $target_sql = urlencode(encrypt(Func::printQuery($plan))); // 페이지 들어가기 전에 쿼리를 저장해야한다.
        
        // Log::debug(print_r($param, true));

        $plan = $plan->GET();
        $plan = Func::chungDec(["loan_usr_info","loan_info","loan_info_return_plan"], $plan);	// CHUNG DATABASE DECRYPT

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "수익분배전체_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no))
        {
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
        } 
        else 
        {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$excelDownCd,$file_name, $target_sql, $record_count,$etc,null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
        }

        $arrManager = Func::getUserList();

        // 엑셀 헤더
		$excel_header   = array('순번','계약번호','분배일자','투자금액','전체이자','당월이자','원천징수','소득세','주민세','실수령이자', '지급적용일자', '지급처리자', '지급처리시간');
        $excel_data     = [];

        foreach ($plan as $v)
        {
            $array_data = [
                $v->seq,
                $v->loan_info_no,
                Func::dateFormat($v->plan_date),
                number_format($v->plan_money),
                number_format($v->sum_interest),
                number_format($v->plan_interest),
                number_format($v->withholding_tax),
                number_format($v->income_tax),
                number_format($v->local_tax),
                number_format($v->plan_interest-$v->withholding_tax),
                Func::dateFormat($v->divide_date),
                isset($arrManager[$v->divide_id]->name) ? $arrManager[$v->divide_id]->name : $v->divide_id,
                Func::dateFormat($v->divide_time)
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

            if(Storage::disk('excel')->exists('/'.$file_name)) 
            {
                return Storage::disk('excel')->download('/'.$file_name, $file_name);
            }
            else 
            {
                $array_result['result']    = 'N';
                $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
            }
        }
        else
        {
           $array_result['result']    = 'N';
           $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }

        Log::debug(print_r($array_result, true));
    }

    /**
     * 투자자정보 팝업창 - 투자내역 - 지급내역엑셀다운
     * 
     * TODO
     * loan_info 테이블의 loan_usr_info_no 와 매칭되는 loan_usr_info 테이블의 no를 investor_no로 수정해야 함
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investorPaymentExcel(Request $request)
    {
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');

        $loan_usr_info_no = $request->loan_usr_info_no;
        $down_filename  = '';
        $excel_down_div = 'E';
        $etc            = '';
        $excelDownCd    = '001';

        $INV = DB::TABLE("loan_info")->join("loan_info_return_plan","loan_info.no","=","loan_info_return_plan.loan_info_no")->join("loan_usr_info","loan_usr_info.investor_no","=","loan_info.loan_usr_info_no");
        $INV->SELECT("loan_info.no as loan_info_no, loan_info.sum_interest, loan_info.no, loan_info.invest_rate, loan_usr_info.relation, loan_info.balance, loan_usr_info.name, loan_info_return_plan.*");
        $INV->WHERE('loan_info.save_status','Y');
        $INV->WHERE('loan_usr_info.save_status','Y');
        $INV->WHERE("LOAN_USR_INFO.no",$loan_usr_info_no);
        $INV->WHERE('loan_info_return_plan.divide_flag','Y');
        $INV->WHERE('LOAN_INFO.status', '!=', 'N');
        $INV->ORDERBY('loan_info.no', 'desc');
        $INV->ORDERBY('loan_usr_info_no','asc');
        $INV->ORDERBY('trade_date','asc');
        $INV->ORDERBY('seq','asc');

        $target_sql = urlencode(encrypt(Func::printQuery($INV))); // 페이지 들어가기 전에 쿼리를 저장해야한다.

        $INV = $INV->get();
        $INV = Func::chungDec(["loan_usr_info","loan_info","loan_info_return_plan"], $INV);	// CHUNG DATABASE DECRYPT

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "투자내역_지급내역전체_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no))
        {
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
        } 
        else 
        {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$excelDownCd,$file_name, $target_sql, $record_count,$etc,null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
        }
        
        $arrManager = Func::getUserList();

        // 엑셀 헤더
		$excel_header   = array('순번','계약번호','투자자명','분배일자','투자금액','투자잔액','전체이자','당월이자', '이자지급시작일자','이자지급종료일자','일수','이율', '원천징수','소득세','주민세','실수령이자', '지급적용일자', '지급처리자', '지급처리시간');
        $excel_data     = [];

        foreach ($INV as $v)
        {
            $array_data = [
                $v->seq,
                $v->loan_info_no,
                $v->name,
                Func::dateFormat($v->plan_date),
                number_format($v->plan_money),
                number_format($v->balance),
                number_format($v->sum_interest),
                number_format($v->plan_interest),
                Func::dateFormat($v->plan_interest_sdate),
                Func::dateFormat($v->plan_interest_edate),
                Loan::dateTerm($v->plan_interest_sdate, $v->plan_interest_edate, 1),
                $v->invest_rate,
                number_format($v->withholding_tax),
                number_format($v->income_tax),
                number_format($v->local_tax),
                number_format($v->plan_interest-$v->withholding_tax),
                Func::dateFormat($v->divide_date),
                isset($arrManager[$v->divide_id]->name) ? $arrManager[$v->divide_id]->name : $v->divide_id,
                Func::dateFormat($v->divide_time)
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

            if(Storage::disk('excel')->exists('/'.$file_name)) 
            {
                return Storage::disk('excel')->download('/'.$file_name, $file_name);
            }
            else 
            {
                $array_result['result']    = 'N';
                $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
            }
        }
        else
        {
           $array_result['result']    = 'N';
           $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }

        Log::debug(print_r($array_result, true));
    }

    /**
     * 투자내역 팝업 - 투자정보 투자리스트 전체스케줄 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investorTotalScheduleExcel(Request $request)
    {
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');

        $loan_usr_info_no = $request->loan_usr_info_no;
        $down_filename  = '';
        $excel_down_div = 'E';
        $etc            = '';
        $excelDownCd    = '001';

        $INV = DB::TABLE('loan_info_return_plan')
               ->JOIN('LOAN_USR_INFO', 'loan_info.loan_usr_info_no', '=', 'LOAN_USR_INFO.investor_no')
               ->JOIN('loan_info', 'loan_info_return_plan.LOAN_INFO_NO', '=', 'LOAN_INFO.NO')
               ->SELECT('loan_info_return_plan.*', 'loan_info.balance', 'loan_info.trade_money','LOAN_USR_INFO.no as loan_usr_info_no','LOAN_INFO.no as loan_info_no','LOAN_USR_INFO.name')
               ->WHEREIN('loan_info_no', function ($query) use ($loan_usr_info_no) {
                   $query->SELECT('loan_info.no')
                          ->FROM('loan_info')
                          ->WHERE('loan_info.save_status', 'Y')
                          ->WHERE('loan_info.loan_usr_info_no', $loan_usr_info_no);
                   });
        $INV->ORDERBY('loan_info_no','desc');
        $INV->ORDERBY('loan_usr_info_no','asc');
        $INV->ORDERBY('trade_date','asc');
        $INV->ORDERBY('seq','asc');

        $target_sql = urlencode(encrypt(Func::printQuery($INV))); // 페이지 들어가기 전에 쿼리를 저장해야한다.

        $INV = $INV->get();
        $INV = Func::chungDec(["loan_usr_info","loan_info","loan_info_return_plan","cust_info"], $INV);	// CHUNG DATABASE DECRYPT

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "투자내역_전체스케줄_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no))
        {
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
        } 
        else 
        {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$excelDownCd,$file_name, $target_sql, $record_count,$etc,null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }
        }
        
        $arrManager = Func::getUserList();

        // 엑셀 헤더
		$excel_header   = array('계약번호','투자자번호','투자자명','회차','상환일','상환일(영업)','이자구간','투자원금', '투자잔액', '이자', '원천징수','이자소득세','주민세','실수령이자');
        $excel_data     = [];

        foreach ($INV as $v)
        {
            $array_data = [
                number_format($v->loan_info_no),
                number_format($v->loan_usr_info_no),
                $v->name,
                number_format($v->seq),
                Func::dateFormat($v->plan_date)." (".Vars::$arrayWeekDay[date('w',Func::dateToUnixtime($v->plan_date))].")",
                Func::dateFormat($v->plan_date_biz),
                Func::dateFormat($v->plan_interest_sdate)." ~ ".Func::dateFormat($v->plan_interest_edate),
                number_format($v->trade_money),
                number_format($v->plan_money),
                number_format($v->plan_interest),
                number_format($v->withholding_tax),
                number_format($v->income_tax),
                number_format($v->local_tax),
                number_format($v->plan_interest-$v->withholding_tax)

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

            if(Storage::disk('excel')->exists('/'.$file_name)) 
            {
                return Storage::disk('excel')->download('/'.$file_name, $file_name);
            }
            else 
            {
                $array_result['result']    = 'N';
                $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
            }
        }
        else
        {
           $array_result['result']    = 'N';
           $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }

        Log::debug(print_r($array_result, true));
    } 
    
    /**
     * 투자자정보창 이미지
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function investorInfoSms(Request $request)
    {
        //Log::debug(print_r($request->all(), true));

        $arr_lon_div = Vars::$arrayLonDoc;
        $arr_cot_div = Vars::$arrayCotDoc;
        $arr_etc_div = Vars::$arrayEtcDoc;
        $arr_img_div = $arr_lon_div + $arr_cot_div + $arr_etc_div;

        $arr_task_name        = Vars::$arrayTaskName;
        $arr_image_div        = Vars::$arrayImageUploadDivision;
        $arr_image_div_select = Vars::$arrayImageUploadDivisionSelect;
        $arrManager           = Func::getUserList();
        $arrPdName            = array();
        $arrPdInvNoList       = "";

        $img = DB::TABLE("loan_usr_info_img")->leftjoin('loan_info', 'loan_usr_info_img.loan_info_no', '=', 'loan_info.no')
                                            ->SELECT("loan_usr_info_img.*")
                                            ->WHERE('loan_usr_info_img.save_status','Y')
                                            ->ORDERBY('loan_usr_info_img.save_time', 'desc');

        if(isset($request->loan_usr_info_no))
        {
            $loan_usr_info_no = $request->loan_usr_info_no;
            $img = $img->WHERE('loan_usr_info_img.loan_usr_info_no', $loan_usr_info_no)->get()->toArray();
            $img = Func::chungDec(["loan_usr_info_img"], $img);	// CHUNG DATABASE DECRYPT

            $invNo = DB::table('loan_info')->select('no')
                                            ->where('save_status', 'Y')
                                            ->where('loan_usr_info_no', $loan_usr_info_no)
                                            ->orderby('no', 'desc')
                                            ->get();
            $arrInvNo = Array();
            foreach($invNo as $inv){
                $arrInvNo[$inv->no] = $inv->no;
            }
            
            $arrInvNo['0'] = 0;
        }

        $selected_img = array();
        if(isset($request->selected))
        {
            $mode = "UPD";
            foreach( $img as $key=>$value )
            {
                if($value->loan_info_no > 0)
                {
                    $loan_sql = DB::TABLE("loan_info")->SELECT("no")->WHERE('no',$value->loan_info_no)->WHERE('save_status','Y')->FIRST();

                    $value->loan_info_no = $loan_sql->no;
                }
                else
                {
                    $value->loan_info_no = '0';
                }

                $value->save_time   = date("Y-m-d H:i:s", strtotime($value->save_time));
                $value->save_id   = isset($arrManager[$value->save_id]) ? Func::nvl($arrManager[$value->save_id]->name, $value->save_id) : $value->save_id;
                $value->trade_date  = Func::dateFormat($value->trade_date);
                $value->trade_money = number_format($value->trade_money);

                if( $value->no == $request->selected )
                {
                    $selected_img[0] = $img[$key];
                }
            }
        }
        else
        {
            $mode = "INS";
            foreach( $img as $key=>$value )
            {
                if($value->loan_info_no > 0)
                {
                    $loan_sql = DB::TABLE("loan_info")->SELECT("no")->WHERE('no',$value->loan_info_no)->WHERE('save_status','Y')->FIRST();

                    $value->loan_info_no = $loan_sql->no;
                }
                else
                {
                    $value->loan_info_no = '0';
                }

                $value->save_id   = isset($arrManager[$value->save_id]) ? Func::nvl($arrManager[$value->save_id]->name, $value->save_id) : $value->save_id ;
                $value->save_time   = date("Y-m-d H:i:s", strtotime($value->save_time));
                $value->trade_date  = Func::dateFormat($value->trade_date);
                $value->trade_money = number_format($value->trade_money);
            }
        }

        $arrPdInvNoList = json_encode($arrPdName, JSON_UNESCAPED_UNICODE);

        return view('account.investorInfoSms')->with('arr_lon_div',$arr_lon_div)
                                            ->with('arr_cot_div',$arr_cot_div)
                                            ->with('arr_img_div',$arr_img_div)
                                            ->with('arr_image_div_select',$arr_image_div_select)
                                            ->with('arr_etc_div',$arr_etc_div)
                                            ->with('arr_pd_name',$arrPdName)
                                            ->with('arr_pd_loan_no_list',$arrPdInvNoList)
                                            ->with('arr_loan_no',$arrInvNo)
                                            ->with('arr_task_name',$arr_task_name)
                                            ->with('arr_image_div',$arr_image_div)
                                            ->with('img',$img)
                                            ->with('loan_usr_info_no', $loan_usr_info_no)
                                            ->with('mode', $mode)
                                            ->with('selected_img', $selected_img);
    }

    /**
    * 고객정보창 이미지 액션
    *
    * @param  \Illuminate\Http\Request  $request
    * @return String
    */
    public function investorInfoSmsAction(Request $request)
    {
        $_DATA = $request->input();
        $arrayTaskId        = Vars::getTaskId();
        $arrayEcmResultCode = Vars::$arrayEcmResultCode;
        $arraySaveFolder    = Vars::getSaveFolder();
        $arr_lon_div        = Vars::$arrayLonDoc;
        $arr_cot_div        = Vars::$arrayCotDoc;
        $arr_etc_div        = Vars::$arrayEtcDoc;
        $arr_img_div        = $arr_lon_div + $arr_cot_div + $arr_etc_div;
        $param              = $request->all();

        $_DATA['save_id'] = Auth::id();
        $_DATA['save_time'] = date("YmdHis");

        // 폴더 생성
        $folder = date("Ymd");
        $fileName = date("YmdHis")."_".sprintf("%07d", $request->loan_usr_info_no);

        // 투자자번호 조회
        if(!empty($request->loan_usr_info_no))
        {
            $usr = DB::TABLE("loan_usr_info")->SELECT('no')->WHERE('no', $param['loan_usr_info_no'])->FIRST();
            $loan_usr_info_no = $usr->no;
        }
        else 
        {
            $loan_usr_info_no = "0";
        }

        // 계약번호 조회
        if(!empty($request->loan_info_no))
        {
            $loan = DB::TABLE("loan_info")->SELECT('no')->WHERE('no', $param['loan_info_no'])->FIRST();
            $loan_info_no = $loan->no;
        }
        else
        {
            $loan_info_no = '0';
        }
        
        if( $_DATA['mode'] == "INS" )
        {
            $_DATA['save_status'] = "Y";
            
            // $no = 0;
            DB::beginTransaction();
            unset($_DATA['no']);

            // 업로드할 파일이 존재하는 경우
            if( $request->file('customFile') )
            {
                $filePath = $request->file('customFile')->storeAs($folder, $fileName,'erp_data_usr_img');
                
                $_DATA['origin_filename'] = $request->file('customFile')->getClientOriginalName();
                $_DATA['filename']        = $fileName;
                $_DATA['folder_name']     = $folder;
                $_DATA['file_path']       = $filePath;
                $_DATA['extension']       = $request->file('customFile')->guessExtension();
                $_DATA['save_id']       = Auth::id();
                $_DATA['save_time']       = date("YmdHis");
                $_DATA['save_status']     = "Y";
                $_DATA['loan_usr_info_no']     = $loan_usr_info_no;
                $_DATA['loan_info_no']    = $loan_info_no;
                if(isset($param['taskname']))$_DATA['taskname']        = $param['taskname'];

                $result = DB::dataProcess( "INS", "loan_usr_info_img", $_DATA );
                
                if( $result=="Y" )
                {
                    $msg = "정상적으로 처리되었습니다.";
                    DB::commit();
                }
                else
                {
                    $msg = "이미지 저장 실패.";
                    DB::rollBack();
                }
            }
            else
            {
                $msg = "파일을 선택해주세요.";
                DB::rollBack();
            }
        }
        else if( $_DATA['mode'] == "UPD" )
        {
            $_DATA['loan_usr_info_no']    = $loan_usr_info_no;
            if(isset($param['taskname']))$_DATA['taskname'] = $param['taskname'];

            //  파일 수정 시, 파일 삭제는 하지 않는다.
            if( $request->file('customFile') )
            {
                DB::beginTransaction();

                $rslt = DB::TABLE('loan_usr_info_img')->SELECT('filename', 'taskname', 'img_div_cd', 'file_path','save_id')->WHERE('save_status', 'Y')->WHERE('no', $_DATA['no'])->FIRST();
                $rslt = Func::chungDec(["loan_usr_info_img"], $rslt);	// CHUNG DATABASE DECRYPT

                // 기존 파일명
                if(isset($rslt->filename))
                {
                    $filePath = $request->file('customFile')->storeAs($folder, $fileName,'erp_data_usr_img');

                    $_DATA['save_status'] = "Y";
                    $_DATA['origin_filename'] = $request->file('customFile')->getClientOriginalName();
                    $_DATA['filename']        = $fileName;
                    $_DATA['folder_name']     = $folder;
                    $_DATA['file_path']       = $filePath;
                    $_DATA['extension']       = $request->file('customFile')->guessExtension();

                    $result = DB::dataProcess("UPD", "loan_usr_info_img", $_DATA);
    
                    if( $result=="Y" )
                    {
                        $msg = "정상적으로 처리되었습니다.";
                        DB::commit();
                    }
                    else
                    {
                        $msg = "이미지 저장 실패.";
                        DB::rollBack();
                    }
                }
                else
                {
                    $msg = "기존 데이터 수정 실패.";
                    DB::rollback();
                }
            }
            else
            {
                $result = DB::dataProcess("UPD", "loan_usr_info_img", $_DATA);

                if( $result == "Y" )
                {
                    $msg = "정상적으로 처리되었습니다.";
                    DB::commit();
                }
                else
                {
                    $msg = "데이터 수정 실패";
                    DB::rollback();
                }
            }
        }
        else if( $_DATA['mode'] == "DEL" )
        {                
            $_DEL_DATA['no']          = $_DATA['no'];
            $_DEL_DATA['del_id']      = Auth::id();
            $_DEL_DATA['del_time']    = date("YmdHis");
            $_DEL_DATA['save_status'] = "N";

            DB::beginTransaction();
            $_IMG = DB::TABLE('loan_usr_info_img')->SELECT('filename', 'file_path')->WHERE('no', $_DATA['no'])->first();
            $_IMG = Func::chungDec(["loan_usr_info_img"], $_IMG);	// CHUNG DATABASE DECRYPT

            if(isset($_IMG->filename))
            {
                // 기존파일 삭제
                $exists = Storage::disk('erp_data_usr_img')->exists($_IMG->file_path);
                if( $exists )
                {
                    Storage::disk('erp_data_usr_img')->delete($_IMG->file_path);
                }

                $result = DB::dataProcess("UPD", "loan_usr_info_img", $_DEL_DATA);

                if( $result == "Y" )
                {
                    $rs_code = 'Y';
                    $msg = "정상적으로 처리되었습니다.";
                    DB::commit();
                }
                else
                {
                    $msg = "데이터 삭제 실패";
                    DB::rollback();
                }
            }
            else
            {
                $msg = "파일 미존재";
                DB::rollback();
            }
        }
        else
        {
            $msg = "데이터 오류";
        }

        return $msg;
    } 
    
    /**
     * 투자자정보창 메모
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function investorInfoMemo(Request $request)
    {
        //Log::debug(print_r($request->all(), true));

        Log::info($request);
        $arr_lon_div = Vars::$arrayLonDoc;
        $arr_cot_div = Vars::$arrayCotDoc;
        $arr_etc_div = Vars::$arrayEtcDoc;
        $arr_img_div = $arr_lon_div + $arr_cot_div + $arr_etc_div;

        $arr_task_name        = Vars::$arrayTaskName;
        $arr_image_div        = Vars::$arrayImageUploadDivision;
        $arr_image_div_select = Vars::$arrayImageUploadDivisionSelect;
        $arrManager           = Func::getUserList();
        $arrPdName            = array();
        $arrPdInvNoList       = "";

        $img = DB::TABLE("loan_usr_info_memo")->leftjoin('loan_info', 'loan_usr_info_memo.loan_info_no', '=', 'loan_info.no')
                                            ->SELECT("loan_usr_info_memo.*","loan_info.inv_seq", "loan_info.investor_no", "loan_info.investor_type")
                                            ->WHERE('loan_usr_info_memo.save_status','Y')
                                            ->ORDERBY('loan_usr_info_memo.save_time', 'desc');

        if(isset($request->loan_usr_info_no))
        {
            $loan_usr_info_no = $request->loan_usr_info_no;
            $img = $img->WHERE('loan_usr_info_memo.loan_usr_info_no', $loan_usr_info_no)->get()->toArray();
            $img = Func::chungDec(["loan_usr_info_memo"], $img);	// CHUNG DATABASE DECRYPT
            $loanNo = DB::table('loan_info')->select('loan_info.no')
                                            ->where('loan_info.save_status', 'Y')
                                            ->where('loan_info.loan_usr_info_no', $loan_usr_info_no)
                                            ->orderby('no', 'desc')
                                            ->get();
            $arrInvNo = Array();
            foreach($loanNo as $inv){
                $arrInvNo[$inv->no] = $inv->no;
            }
            
            $arrInvNo['0'] = 0;
        }

        $selected_img = array();
        if(isset($request->selected))
        {
            $mode = "UPD";
            foreach( $img as $key=>$value )
            {
                if($value->loan_info_no > 0)
                {
                    $loan_sql = DB::TABLE("loan_info")->SELECT("no")->WHERE('no',$value->loan_info_no)->WHERE('save_status','Y')->FIRST();

                    $value->loan_info_no = $loan_sql->no;
                }
                else
                {
                    $value->loan_info_no = '0';
                }

                $value->save_time   = date("Y-m-d H:i:s", strtotime($value->save_time));
                $value->save_id     = isset($arrManager[$value->save_id]) ? Func::nvl($arrManager[$value->save_id]->name, $value->save_id) : $value->save_id;

                if( $value->no == $request->selected )
                {
                    $selected_img[0] = $img[$key];
                }
            }
        }
        else
        {
            $mode = "INS";
            foreach( $img as $key=>$value )
            {
                if($value->loan_info_no > 0)
                {
                    $loan_sql = DB::TABLE("loan_info")->SELECT("no")->WHERE('no',$value->loan_info_no)->WHERE('save_status','Y')->FIRST();

                    $value->loan_info_no = $loan_sql->no;
                }
                else
                {
                    $value->loan_info_no = '0';
                }

                $value->save_id     = isset($arrManager[$value->save_id]) ? Func::nvl($arrManager[$value->save_id]->name, $value->save_id) : $value->save_id ;
                $value->save_time   = date("Y-m-d H:i:s", strtotime($value->save_time));
            }
        }

        $arrPdInvNoList = json_encode($arrPdName, JSON_UNESCAPED_UNICODE);

        return view('account.investorInfoMemo')->with('arr_lon_div',$arr_lon_div)
                                            ->with('arr_cot_div',$arr_cot_div)
                                            ->with('arr_img_div',$arr_img_div)
                                            ->with('arr_image_div_select',$arr_image_div_select)
                                            ->with('arr_etc_div',$arr_etc_div)
                                            ->with('arr_pd_name',$arrPdName)
                                            ->with('arr_pd_loan_no_list',$arrPdInvNoList)
                                            ->with('arr_loan_no',$arrInvNo)
                                            ->with('arr_task_name',$arr_task_name)
                                            ->with('arr_image_div',$arr_image_div)
                                            ->with('img',$img)
                                            ->with('loan_usr_info_no', $loan_usr_info_no)
                                            ->with('mode', $mode)
                                            ->with('selected_img', $selected_img);
    }

    /**
    * 투자자정보창 메모 액션
    *
    * @param  \Illuminate\Http\Request  $request
    * @return String
    */
    public function investorInfoMemoAction(Request $request)
    {
        $_DATA = $request->input();
        $arrayTaskId        = Vars::getTaskId();
        $arrayEcmResultCode = Vars::$arrayEcmResultCode;
        $arraySaveFolder    = Vars::getSaveFolder();
        $arr_lon_div        = Vars::$arrayLonDoc;
        $arr_cot_div        = Vars::$arrayCotDoc;
        $arr_etc_div        = Vars::$arrayEtcDoc;
        $arr_img_div        = $arr_lon_div + $arr_cot_div + $arr_etc_div;
        $param              = $request->all();

        $_DATA['save_id'] = Auth::id();
        $_DATA['save_time'] = date("YmdHis");

        // 폴더 생성
        $folder = date("Ymd");
        $fileName = date("YmdHis")."_".sprintf("%07d", $request->loan_usr_info_no);

        // 투자자번호 조회
        if(!empty($request->loan_usr_info_no))
        {
            $usr = DB::TABLE("loan_usr_info")->SELECT('no')->WHERE('no', $param['loan_usr_info_no'])->FIRST();
            $loan_usr_info_no = $usr->no;
        }
        else 
        {
            $loan_usr_info_no = "0";
        }

        // 계약번호 조회
        if(!empty($request->loan_info_no))
        {
            $loan = DB::TABLE("loan_info")->SELECT('no')->WHERE('no', $param['loan_info_no'])->FIRST();
            $loan_info_no = $loan->no;
        }
        else
        {
            $loan_info_no = '0';
        }
        
        if( $_DATA['mode'] == "INS" )
        {
            $_DATA['save_status'] = "Y";
            
            // $no = 0;
            DB::beginTransaction();
            unset($_DATA['no']);

            // 업로드할 파일이 존재하는 경우
            if( $request->file('customFile') )
            {
                $filePath = $request->file('customFile')->storeAs($folder, $fileName,'erp_data_usr_img');
                
                $_DATA['origin_filename'] = $request->file('customFile')->getClientOriginalName();
                $_DATA['filename']        = $fileName;
                $_DATA['folder_name']     = $folder;
                $_DATA['file_path']       = $filePath;
                $_DATA['extension']       = $request->file('customFile')->guessExtension();
                $_DATA['save_id']         = Auth::id();
                $_DATA['save_time']       = date("YmdHis");
                $_DATA['save_status']     = "Y";
                $_DATA['loan_usr_info_no']= $loan_usr_info_no;
                $_DATA['loan_info_no']    = $loan_info_no;
                if(isset($param['taskname']))$_DATA['taskname']        = $param['taskname'];

                $result = DB::dataProcess( "INS", "loan_usr_info_img", $_DATA );
                
                if( $result=="Y" )
                {
                    $msg = "정상적으로 처리되었습니다.";
                    DB::commit();
                }
                else
                {
                    $msg = "이미지 저장 실패.";
                    DB::rollBack();
                }
            }
            else
            {
                $msg = "파일을 선택해주세요.";
                DB::rollBack();
            }
        }
        else if( $_DATA['mode'] == "UPD" )
        {
            $_DATA['loan_usr_info_no']    = $loan_usr_info_no;
            if(isset($param['taskname']))$_DATA['taskname'] = $param['taskname'];

            //  파일 수정 시, 파일 삭제는 하지 않는다.
            if( $request->file('customFile') )
            {
                DB::beginTransaction();

                $rslt = DB::TABLE('loan_usr_info_img')->SELECT('filename', 'taskname', 'img_div_cd', 'file_path','save_id')->WHERE('save_status', 'Y')->WHERE('no', $_DATA['no'])->FIRST();
                $rslt = Func::chungDec(["loan_usr_info_img"], $rslt);	// CHUNG DATABASE DECRYPT

                // 기존 파일명
                if(isset($rslt->filename))
                {
                    $filePath = $request->file('customFile')->storeAs($folder, $fileName,'erp_data_usr_img');

                    $_DATA['save_status'] = "Y";
                    $_DATA['origin_filename'] = $request->file('customFile')->getClientOriginalName();
                    $_DATA['filename']        = $fileName;
                    $_DATA['folder_name']     = $folder;
                    $_DATA['file_path']       = $filePath;
                    $_DATA['extension']       = $request->file('customFile')->guessExtension();

                    $result = DB::dataProcess("UPD", "loan_usr_info_img", $_DATA);
    
                    if( $result=="Y" )
                    {
                        $msg = "정상적으로 처리되었습니다.";
                        DB::commit();
                    }
                    else
                    {
                        $msg = "이미지 저장 실패.";
                        DB::rollBack();
                    }
                }
                else
                {
                    $msg = "기존 데이터 수정 실패.";
                    DB::rollback();
                }
            }
            else
            {
                $result = DB::dataProcess("UPD", "loan_usr_info_img", $_DATA);

                if( $result == "Y" )
                {
                    $msg = "정상적으로 처리되었습니다.";
                    DB::commit();
                }
                else
                {
                    $msg = "데이터 수정 실패";
                    DB::rollback();
                }
            }
        }
        else if( $_DATA['mode'] == "DEL" )
        {                
            $_DEL_DATA['no']          = $_DATA['no'];
            $_DEL_DATA['del_id']      = Auth::id();
            $_DEL_DATA['del_time']    = date("YmdHis");
            $_DEL_DATA['save_status'] = "N";

            DB::beginTransaction();
            $_IMG = DB::TABLE('loan_usr_info_img')->SELECT('filename', 'file_path')->WHERE('no', $_DATA['no'])->first();
            $_IMG = Func::chungDec(["loan_usr_info_img"], $_IMG);	// CHUNG DATABASE DECRYPT

            if(isset($_IMG->filename))
            {
                // 기존파일 삭제
                $exists = Storage::disk('erp_data_usr_img')->exists($_IMG->file_path);
                if( $exists )
                {
                    Storage::disk('erp_data_usr_img')->delete($_IMG->file_path);
                }

                $result = DB::dataProcess("UPD", "loan_usr_info_img", $_DEL_DATA);

                if( $result == "Y" )
                {
                    $rs_code = 'Y';
                    $msg = "정상적으로 처리되었습니다.";
                    DB::commit();
                }
                else
                {
                    $msg = "데이터 삭제 실패";
                    DB::rollback();
                }
            }
            else
            {
                $msg = "파일 미존재";
                DB::rollback();
            }
        }
        else
        {
            $msg = "데이터 오류";
        }

        return $msg;
    } 

    /**
     * 투자자명세 삭제 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function investorDelete(Request $request)
    {
        $val = $request->input();
        $s_cnt = 0;
        $arr_fail = Array();

        $del_id   = Auth::id();
        $del_time = date("YmdHis");

        if( $val['action_mode']=="investor_DELETE" && is_array($val['listChk']) && sizeof($val['listChk'])>0 )
        {
            for( $i=0; $i<sizeof($val['listChk']); $i++ )
            {
                $loan_usr_info_no = $val['listChk'][$i];

                DB::beginTransaction();

                $loan_info = DB::table("loan_info")
                                    ->join("loan_usr_info", 'loan_info.loan_usr_info_no', '=', 'loan_usr_info.no')
                                    ->where('loan_info.loan_usr_info_no', $loan_usr_info_no)
                                    ->where('loan_info.save_status', 'Y')
                                    ->where('loan_usr_info.save_status', 'Y')
                                    ->first();
                $loan_info = Func::chungDec(["loan_info"], $loan_info);	// CHUNG DATABASE DECRYPT

                if(!empty($loan_info->no))
                {
                    DB::rollback();

                    $arr_fail[$loan_usr_info_no] = "연결되어있는 투자계약이 있습니다.";
                    continue;
                }

                $rslt = DB::dataProcess('UPD', 'loan_usr_info', ["SAVE_STATUS"=>"N", "DEL_ID"=>$del_id, "DEL_TIME"=>$del_time], ["no"=>$loan_usr_info_no]);
                if( $rslt!="Y" )
                {
                    DB::rollback();

                    $arr_fail[$loan_usr_info_no] = "삭제처리에 실패하였습니다.";
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