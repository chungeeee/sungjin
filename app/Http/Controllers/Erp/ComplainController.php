<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use App\Chung\ExcelFunc;
use Vars;
use Log;
use Auth;
use DataList;
use Validator;
use App\Chung\Paging;
use Illuminate\Support\Facades\Storage;

class ComplainController extends Controller
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
     private function setDataList(Request $request){

        if(!isset($request->tabs)) $request->tabs = 'A';
        $prcStatus = Vars::$arrayComplainPrcStatus;

        $list   = new DataList(Array("listName"=>"complain","listAction"=>'/'.$request->path()));
        
        // if( Func::funcCheckPermit("H022") )
        // {
        //     $list->setButtonArray("엑셀다운", "excelDownModal('/erp/complainexcel', 'form_complain')", "btn-success");
        // }

        $list->setSearchDate('날짜검색', Array('app_date'=>'접수일', 'prc_date'=>'처리일'),'searchDt','Y');

        $list->setSearchType('complain_office_cd', Func::getConfigArr('complain_app_orgn_cd'), '접수처');

        $list->setSearchType('person_manage', Func::getConfigArr('person_manage_cd'), '민원관리');

        $list->setSearchType('occur_branch', Func::getBranch(), '발생부서');
        
        $list->setSearchType('action_rs', Vars::$arrayComplainResult, '조치결과');

        $list->setTabs(array_merge(Array('ALL'=>'전체'), $prcStatus), $request->tabs);

        $list->setSearchDetail(Array( 
            'complain_title'     => '민원제목', 
            'cust_name'          => '민원인', 
            'birth'              => '생년월일', 
        ));

        return $list;
    }


    /**
     * 민원관리 메인화면
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function complain(Request $request)
    {
        $list   = $this->setDataList($request);
        
        // plusButtonAction : 등록 버튼 onclick 동작
        // if( Func::funcCheckPermit("L031") )
        // {
            $list->setPlusButton("window.open('/erp/complainform','', 'right=0,top=0,height=' + screen.height + ',width=' + screen.width*0.6 + 'fullscreen=yes')");
        // }
        
        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $list->setlistTitleCommon(Array
        (
            'no'                    =>     Array('민원번호', 1, '', 'center', '', 'no'),
            'complain_office_cd'    =>     Array('접수처', 1, '', 'center', '', ''),
            'app_date'              =>     Array('접수일', 1, '', 'center', '', 'app_date'),
            'prc_date'              =>     Array('처리일', 1, '', 'center', '', 'prc_date'),
            'person_manage'         =>     Array('민원관리', 1, '', 'center', '', 'person_manage'),
            'cust_info_no'          =>     Array('차입자번호', 0, '', 'center', '', ''),
            'cust_name'             =>     Array('민원인', 0, '', 'center', '', ''),
            'ssn'                   =>     Array('생년월일', 0, '', 'center', '', ''),
            'complain_title'        =>     Array('민원제목', 0, '', 'center', '', ''),
            'action_rs'             =>     Array('조치결과', 0, '', 'center', '', ''),
            'prc_rs'                =>     Array('처리결과', 0, '', 'center', '', ''),
            'occur_branch'          =>     Array('발생부서', 0, '', 'center', '', ''),
            'occur_id'              =>     Array('발생대상자', 0, '', 'center', '', ''),
            'prc_manager_id'        =>     Array('처리담당자', 0, '', 'center', '', ''),
        ));

        return view('erp.complain')->with('result', $list->getList());
    }

    /**
     * 민원관리 리스트
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function complainList(Request $request)
    {
        $prcStatus = Vars::$arrayComplainPrcStatus;

        $list   = $this->setDataList($request);

        $param  = $request->all();
        $param['tabSelectNm'] = 'PRC_RS';

        if($request->isFirst=='1')
		{
            $countDb = DB::TABLE("complain")->SELECT('prc_rs as item', DB::RAW('count(no) as cnt'))->WHERE('save_status','Y');
            $countAll = DB::TABLE("complain")->SELECT("no")->WHERE("save_status","Y")->COUNT();
            $count = $countDb->GROUPBY('prc_rs')->get();

			$r['tabCount'] = Func::getTabsCnt($count, $prcStatus); 
            $r['tabCount']['ALL'] = $countAll;
		}

        // 메인쿼리
        $complain = DB::TABLE("complain")->SELECT("complain.*")->WHERE('complain.save_status','Y');

        // 생년월일검색
        if(isset( $param['searchDetail']) && $param['searchDetail']=='birth' && !empty($param['searchString']) )
        {
            $searchString = $param['searchString'];

            $complain = Func::encLikeSearch($complain, 'complain.ssn', $searchString, 'all', 9);
            
            unset($param['searchString']);
        }

        $param['listOrder'] = "complain.app_date";
        $param['listOrderAsc'] = "desc";

        $complain = $list->getListQuery("complain", 'main', $complain, $param);

        Log::debug(Func::printQuery($complain));
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($complain, $request->page, $request->listLimit, 10);
        
        // 결과
        $result = $complain->get();
        $result = Func::chungDec(["COMPLAIN"], $result);	// CHUNG DATABASE DECRYPT
        
        // 뷰단 데이터 정리.
        $cnt = 0;
        $configArr = Func::getConfigArr();
        $branchArr = Func::getbranch();
        $arrayUserId  = Func::getUserId();
        
        $masking = 'A';

		foreach ($result as $v)
		{
            $link_c                 = '<a class="hand" onClick="getPopUp(\'/erp/complainform?no='.$v->no.'\',\'complainform\',\'right=0,top=0,height=\'+ screen.height +\',width=\'+ screen.width*0.6 +\'fullscreen=yes, scrollbars=yes\')">';
            $v->no                  = $link_c.$v->no.'</a>';

            $v->cust_info_no        = !empty($v->cust_info_no)?$v->cust_info_no:'';

            $link                   = '<a class="hand" onClick="popUpFull(\'/erp/custpop?cust_info_no='.$v->cust_info_no.'&no='.$v->loan_info_no.'\')">';
            $v->cust_info_no        = $link.$v->cust_info_no.'</a>';

            $v->complain_office_cd  = Func::getArrayName($configArr['complain_app_orgn_cd'], $v->complain_office_cd);
            $v->app_date            = Func::dateFormat($v->app_date);
            $v->prc_date            = Func::dateFormat($v->prc_date);
            $v->person_manage       = Func::getArrayName($configArr['person_manage_cd'], $v->person_manage);

            $v->ssn                 = !empty($v->ssn) ? Func::ssnFormat($v->ssn, $masking) : '';
            $v->action_rs           = Func::getArrayName(Vars::$arrayComplainResult, $v->action_rs);
            $v->prc_rs              = Func::getArrayName(Vars::$arrayComplainPrcStatus, $v->prc_rs);
            $v->occur_branch        = !empty($v->occur_branch) ? $branchArr[$v->occur_branch]:'';
            $v->occur_id            = Func::getArrayName($arrayUserId, $v->occur_id); 
            $v->prc_manager_id      = Func::getArrayName($arrayUserId, $v->prc_manager_id); 
            $r['v'][]               = $v;
			$cnt ++;
        }
        
        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path());

		$r['result'] = 1;
		$r['txt'] = $cnt;

        return json_encode($r);
    }

    /**
     * 신청정보입력화면
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function complainForm(Request $request)
    {
        $branchArr          = Func::getbranch();
        $configArr          = Func::getConfigArr();
        $complainResult     = Vars::$arrayComplainResult;
        $complainStatus     = Vars::$arrayComplainPrcStatus;
        $userName           = Func::getArrayName(Func::getUserId(), Auth::id()); 
        $userArr            = Func::getUserId();
        $gender = '';
        $age = '';
        
        $users = DB::TABLE("users")->SELECT("id, name, branch_code")->WHERE('save_status','Y')->get();
        $users = Func::chungDec(["USERS"], $users);	// CHUNG DATABASE DECRYPT

        foreach( $users as $u )
		{
			$arrayUser['branch'][$u->branch_code][$u->id] = $u;
        }

        if(!$request->no)
        {
            $action_mode = 'INS';
            $v = [];
            
        }
        else
        {
            $action_mode = 'UPD';
            $v = DB::TABLE("complain")->SELECT("complain.*")->WHERE('complain.no', $request->no)->WHERE('complain.save_status','Y')->FIRST();
            $v = Func::chungDec(["COMPLAIN"], $v);	// CHUNG DATABASE DECRYPT

            if(substr($v->ssn, 6, 1) != ''){
                if((substr($v->ssn, 6, 1) == 1 || substr($v->ssn, 6, 1) == 3))
                {
                    $gender = '남';
                }
                else
                {
                    $gender = '여';
                }

                if(substr($v->ssn,6,1) == '1' || substr($v->ssn,6,1) == '2')
                {
                    $year = '19';
                }
                else
                {
                    $year = '20';
                }
            }
            else
            {
                if((int)(substr($v->ssn, 0, 2)) > 20 )
                {
                    $year = '19';
                }
                else
                {
                    $year = '20';
                }
            }

            $age = (int)date("Y") - (int)($year.substr($v->ssn,0,2));

                if(substr(date('Ymd'), 2, 4) >= substr($v->ssn, 2, 4))
                {
                    $age -= 1;
                }

        }
        return view('erp.complainForm')->with('complainResult',$complainResult)
                                        ->with('complainStatus',$complainStatus)
                                        ->with('branchArr',$branchArr)
                                        ->with('configArr',$configArr)
                                        ->with('userArr',$userArr)
                                        ->with('userName',$userName)
                                        ->with('gender',$gender)
                                        ->with('age',$age)
                                        ->with('action_mode',$action_mode)
                                        ->with('getUserId',$arrayUser['branch'])
                                        ->with('v', $v);
    }

    /**
     * 민원관리 고객찾기
     *
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function searchComplainInfo(Request $request)
    {
        if( !isset($request->search_string) )
        {
            return "검색어를 입력해주세요.";
        }
        $search_string = $request->search_string;

        $search_string = Func::stripCi($search_string);

        // 기본쿼리
        $CUST = DB::TABLE("cust_info");
        $CUST->JOIN('cust_info_extra','cust_info.no','=','cust_info_extra.cust_info_no');
        $CUST->JOIN('loan_info','cust_info.no','=','loan_info.cust_info_no');
        $CUST->SELECT("loan_info.*", "cust_info_extra.*", "cust_info.name", "cust_info.ssn");
        $CUST->WHERE('cust_info.save_status','Y');

        // 검색
        $where = "";
        if( is_numeric($search_string) )
        {
            $where.= "cust_info.no=".$search_string." or loan_info.no=".$search_string." or cust_info.ssn like '".$search_string."%' ";
        }
        else
        {
            $where.= "cust_info.name like '".Func::encrypt($search_string, 'ENC_KEY_SOL')."%' ";   // 이름 암호화
        }
        $CUST->WHERERAW($where);
        $CUST->ORDERBY("cust_info.no","desc");

        $rslt = $CUST->get();
        $rslt = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        //Log::debug($CUST->toSql());
        $getStatus  = Vars::$arrayContractStaColor;

        $string = "<table class='table table-sm table-hover card-secondary card-outline mt-1'>";
        $string.= "<tr class='text-center'>";
        $string.= "<td>차입자번호</td>";
        $string.= "<td>계약No</td>";
        $string.= "<td>이름</td>";
        $string.= "<td>생년월일</td>";
        $string.= "<td>대출일</td>";
        $string.= "<td>상태</td>";
        $string.= "<td>대출액</td>";
        $string.= "<td>잔액</td>";
        $string.= "<td>가수금</td>";

        $string.= "<td hidden>상태코드</td>";
        $string.= "<td hidden>신청No</td>";
        $string.= "</tr>";

        foreach( $rslt as $v )
        {
            $string.= "<tr role='button' onclick='selectInfo(".$v->cust_info_no.", ".$v->no.");'>";
            $string.= "<td id='cust_info_no_".$v->no."' class='text-center'>".Func::addCi($v->cust_info_no)."</td>";
            $string.= "<td id='loan_info_no_".$v->no."' class='text-center'>".$v->no."</td>";
            $string.= "<td id='cust_name_".$v->no."'    class='text-center'>".$v->name."</td>";
            $string.= "<td id='cust_ssn_".$v->no."'     class='text-center'>".Func::dateFormat(substr($v->ssn,0,6),"-")."</td>";
            $string.= "<td id='loan_date_".$v->no."'    class='text-center'>".Func::dateFormat($v->loan_date)."</td>";
            $string.= "<td class='text-center'>".(( isset($getStatus[$v->status]) ) ? $getStatus[$v->status] : $v->status)."</td>";
            $string.= "<td id='loan_money_".$v->no."'   class='text-right'>".number_format($v->loan_money)."</td>";
            $string.= "<td id='loan_balance_".$v->no."' class='text-right'>".number_format($v->balance)."</td>";
            $string.= "<td id='over_money_".$v->no."'   class='text-right'>".number_format($v->over_money)."</td>";

            $string.= "<td id='loan_status_".$v->no."'  class='text-center' hidden>".$v->status."</td>";
            $string.= "<td id='loan_info_no_".$v->no."'  class='text-center' hidden>".$v->no."</td>";
            $string.= "</tr>";
        }
        $string.= "</table>";

        return $string;
    }

    /**
     * 민원신청 고객정보 가져오기
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function getCustInfo(Request $request)
    {
        // 기본쿼리
        $CUST = DB::TABLE("cust_info");
        $CUST->JOIN('cust_info_extra','cust_info.no','=','cust_info_extra.cust_info_no');
        $CUST->JOIN('loan_info','cust_info.no','=','loan_info.cust_info_no');
        $CUST->SELECT("loan_info.*", "cust_info_extra.*", "cust_info.name", "cust_info.ssn");
        $CUST->WHERE('cust_info.save_status','Y');
        $CUST->WHERE("cust_info.no", $request->cust_info_no);
        
        $v = $CUST->FIRST();
        $v = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $v);	// CHUNG DATABASE DECRYPT

        Log::debug($CUST->toSql());
        return json_encode($v);
    }

    /**
     * 민원신청 입력폼 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function complainAction(Request $request)
    {
        $no = $request->check_no;

        unset($ARR);
        $ARR = $request->all();

        if($ARR['action_mode'] == "INS" || $ARR['action_mode'] == "UPD")
        {
            
            // ======================================= 유효성테스트 =======================================
            $customMessages = [
                'cust_name.required'            => 'cust_name|민원인 성명을 입력해주세요.',
                'ssn1.required'                 => 'ssn1|주민등록번호 앞자리를 입력해주세요.',
                'ssn1.numeric'                  => 'ssn1|주민등록번호는 숫자로 입력해주세요.',
                'complain_title.required'       => 'complain_title|민원제목을 입력해주세요.',
                'complain_memo.required'        => 'complain_memo|민원내용을 입력해주세요.',
            ];
            
            $validator = Validator::make($request->all(), [
                'cust_name'          => 'bail|required',
                'ssn1'               => 'bail|required|numeric',
                'complain_title'     => 'bail|required',        
                'complain_memo'      => 'bail|required',
            ],$customMessages);

            if($ARR['prc_rs'] == 'Y')
            {
                $customMessages = [
                    'person_manage.required'        => 'person_manage|민원관리 내용을 선택해주세요.(고객정보와 연동됩니다.)',
                    'action_rs.required'            => 'action_rs|조치결과를 입력해주세요.',
                    'prc_memo.required'             => 'prc_memo|처리결과를 입력해주세요.',
                    'prc_date.required'             => 'prc_date|처리일자를 입력해주세요.',
                ];
    
                $validator = Validator::make($request->all(), [
                    'person_manage'     => 'bail|required',
                    'action_rs'         => 'bail|required',
                    'prc_memo'          => 'bail|required',
                    'prc_date'          => 'bail|required',
                ],$customMessages);
            }

            if ($validator->fails()) 
            {
                Log::debug("유효성 검사 실패".response()->json(['error'=>$validator->errors()->all()]));
                return response()->json(['error'=>$validator->errors()->all()]);
            }
            else
            {
                Log::debug("유효성 검사 통과");
            }
            
            // ======================================= 유효성테스트 =======================================


            $ARR['save_status'] = "Y";
            $ARR['save_time']   = date("YmdHis");
            $ARR['save_id']     = Auth::id();
        }
        else
        {
            $ARR['save_status'] = "N";
            $ARR['save_time']   = date("YmdHis");
            $ARR['save_id']     = Auth::id();
        }

        $ARR['app_date']        = str_replace('-','',$ARR['app_date']);
        $ARR['prc_date']        = str_replace('-','',$ARR['prc_date']);
        $ARR['req_date']        = str_replace('-','',$ARR['req_date']);
        $ARR['limit_date']      = str_replace('-','',$ARR['limit_date']);
        $ARR['job_cd']          = $ARR['job_cd']."000";
        $ARR['ssn']             = $ARR['ssn1'].$ARR['ssn2'];
        if(empty($ARR['prc_rs']))
        {
            $ARR['prc_rs'] = 'A';
        }

        // 결과데이터
        $RS['rs_code'] = "N";
        
        if($ARR['action_mode'] == "UPD" || $ARR['action_mode'] == "DEL")
        {
            $rslt = DB::dataProcess('UPD', 'complain', $ARR, ["no"=>$no]);
        }
        else
        {
            $rslt = DB::dataProcess('INS', 'complain', $ARR);
        }

        if(isset($rslt))
        {
            // 고객정보에 민원관리 내용 업데이트
            if(!empty($ARR['cust_info_no']) && $ARR['cust_info_no']>0)
            {
                $up = DB::table('cust_info')
                ->where('no', $ARR['cust_info_no'])
                ->update(['person_manage'=>$ARR['person_manage'], 'save_time'=>date("YmdHis")]);
            }

            $RS['rs_code'] = $rslt;
            if($RS['rs_code'] == "Y")
            {
                $RS['rs_msg'] = "정상적으로 처리되었습니다.";
            }
            else
            {
                $RS['rs_msg'] = "처리에 실패하였습니다.";
            }
        }

        return $RS;
    }

    /**
     * 민원관리 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function complainExcel(Request $request)
    {
        if( !Func::funcCheckPermit("H022") && !isset($request->excel_flag) )
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
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;
        
        $param['tabSelectNm'] = 'PRC_RS';

        // 메인쿼리
        $complain = DB::TABLE("complain")->SELECT("complain.*")->WHERE('complain.save_status','Y')->ORDERBY('complain.app_date','desc');

        $complain = $list->getListQuery("complain", 'main', $complain, $param);

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($complain, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($complain);
        log::info($query);
        $file_name    = "민원관리_".date("YmdHis").'_'.Auth::id().'.xlsx';
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

        $rslt = $complain->GET();
        $rslt = Func::chungDec(["COMPLAIN"], $rslt);	// CHUNG DATABASE DECRYPT

        // 엑셀 헤더
		$excel_header = array('민원번호','접수처','접수일','처리일', '민원관리', '차입자번호','민원인','주민번호','민원제목','조치결과','처리결과','발생부서','발생대상자','처리담당자',);
        
        // 뷰단 데이터 정리.
        $configArr = Func::getConfigArr();
        $branchArr = Func::getbranch();
        $arrayUserId  = Func::getUserId();

        $masking = 'A';

        foreach ($rslt as $v)
        {
            $array_data = Array(
                $v->no,
                Func::getArrayName($configArr['complain_app_orgn_cd'], $v->complain_office_cd),
                Func::dateFormat($v->app_date),
                Func::dateFormat($v->prc_date),
                Func::getArrayName($configArr['person_manage_cd'], $v->person_manage),
                !empty($v->cust_info_no)? Func::addCi($v->cust_info_no):'',
                $v->cust_name,
                !empty($v->ssn) ? Func::ssnFormat($v->ssn, $masking) : '',
                $v->complain_title,
                Func::getArrayName(Vars::$arrayComplainResult, $v->action_rs),
                Func::getArrayName(Vars::$arrayComplainPrcStatus, $v->prc_rs),
                !empty($v->occur_branch)?($branchArr[$v->occur_branch]):'',
                Func::getArrayName($arrayUserId, $v->occur_id),
                Func::getArrayName($arrayUserId, $v->prc_manager_id),
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
     * 민원관리 현황분석 메인화면
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function complainAnalysis(Request $request)
    {
        $cdate = date("Y-m-d");
        $totalOrgn = 0;
        $totalPrcOrgn = 0;
        $rateCount = 0;
        $days = 0;
        $totalDays = 0;
        $managerArr = [];
        $managerRate = [];
        $branch = [];
        $prcRate = [];
        $orgnRate = [];
        $orgnCount = [];
        $orgnDateCount = [];
        $barChartCount = array(array());
        $lineChartCount = array(array());
        $dateCount = array(array());
        $orgnPrcCount = array(array());
        $orgnPrcRate = array(array());
        $totalOrgnPrcRate = array(array());
        $prcCount = array(array());
        $prcActionCount = array(array());
        $orgnActionCount = array(array());
        $orgnArr = array(array());
        $actionCount = array(array());
        $configArr = Func::getConfigArr();
        $branchArr = Func::getbranch();
        $resultArr = Vars::$arrayComplainResult;

        
        if(isset($request->search_sdate) && isset($request->search_edate))
        {
            $sdate = $request->search_sdate;
            $edate = $request->search_edate;
        }
        else
        {
            $edate = date("Y-m-d");
            $sdate = date("Y-m-d", strtotime($edate." -6 months"));
        }

        $dateDiff = abs(strtotime($edate) - strtotime($sdate));
        $days = floor($dateDiff / (60 * 60 * 24));
        $monthDiff = abs(strtotime(substr($edate,0,8)."10") - strtotime(substr($sdate,0,8)."01"));
        $months = floor($monthDiff / (30 * 60 * 60 * 24));

        //차트 x축에 들어갈 날짜
        for($i = 0; $i <= $months; $i++)
        {
            if($i == 0)
            {
                $cdate = substr($sdate,0,8)."01";
            }
            else{
                $cdate = date("Y-m-d", strtotime($cdate." +1 months"));
            }
            
            if(!(substr($cdate, 0, 4) == substr($edate, 0, 4) && substr($cdate, 5, 2) > substr($edate, 5, 2))){
                $chartDates[$i] = substr($cdate, 0, 7);
            }
        }


        /*
         * 직전동기
         */
        $lastCount = DB::TABLE("complain")->SELECT(DB::raw("count(no) as app_count"))->WHERE("save_status","Y")
        ->WHERE("app_date",">=",date("Ymd", strtotime($sdate." -".$days." days")))->WHERE("app_date","<=",date("Ymd", strtotime($sdate." -1 days")))->FIRST();
        /*
         * 전년동기
         */
        $lastYearCount = DB::TABLE("complain")->SELECT(DB::raw("count(no) as app_count"))->WHERE("save_status","Y")
        ->WHERE("app_date",">=",date("Ymd", strtotime($sdate." -1 years")))->WHERE("app_date","<=",date("Ymd", strtotime($edate." -1 years")))->FIRST();
        /*
         * 전전년동기
         */
        $beforeLastCount = DB::TABLE("complain")->SELECT(DB::raw("count(no) as app_count"))->WHERE("save_status","Y")
        ->WHERE("app_date",">=",date("Ymd", strtotime($sdate." -2 years")))->WHERE("app_date","<=",date("Ymd", strtotime($edate." -2 years")))->FIRST();

        
        /*
         *
         * 차트용 쿼리(접수기관별)
         *    
        */
        $lineChartInfo = DB::TABLE("complain")->SELECT(DB::raw("count(no) as chart_count, app_date, complain_office_cd"))
            ->GROUPBY("app_date")->GROUPBY("complain_office_cd")->WHERE("save_status", "Y");

        /*
         *
         * 차트용 쿼리(팀별)
         *    
        */
        $barChartInfo = DB::TABLE("complain")->SELECT(DB::raw("count(no) as chart_count, app_date, prc_branch"))
        ->GROUPBY("app_date")->GROUPBY("prc_branch")->WHERE("save_status", "Y")->WHERENOTNULL("prc_branch")->WHERE("prc_branch", "!=", "");
        
        /*
         *
         * 모든부서, 담당자 정보, 처리 소요 날짜
         *    
        */
        $bInfo = DB::TABLE("complain")->SELECT("prc_branch", "prc_manager_id", "app_date", "prc_date", "complain_office_cd", "prc_rs", "action_rs")
            ->WHERENOTNULL("prc_branch")->WHERE("prc_branch", "!=", "")->WHERE("save_status", "Y");
            
        /*
         *
         * 처리부서, 처리담당자 기준 정보 (부서별 담당자별 현황)
         *    
        */
        $prc = DB::TABLE("complain")->SELECT(DB::raw("count(prc_manager_id) as count, prc_manager_id, complain_office_cd, prc_branch, action_rs"))
            ->GROUPBY("prc_manager_id")->GROUPBY("complain_office_cd")->GROUPBY("prc_branch")->GROUPBY("action_rs")
            ->WHERENOTNULL("prc_manager_id")->WHERE("prc_manager_id", "!=", "")->WHERENOTNULL("complain_office_cd")->WHERE("complain_office_cd", "!=", "")->WHERE("save_status", "Y");

        /*
         *
         * 민원접수기관 기준 정보 (처리현황)
         *    
        */
        $orgn = DB::TABLE("complain")->SELECT(DB::raw("count(complain_office_cd) as orgn_count, complain_office_cd, action_rs, prc_rs"))
        ->GROUPBY("complain_office_cd")->GROUPBY("action_rs")->GROUPBY("prc_rs")
        ->WHERENOTNULL("complain_office_cd")->WHERE("COMPLAIN_OFFICE_CD", "!=", "")->WHERE("save_status", "Y");

        if($request->search_sdate && $request->search_edate)
        {
            $startDate = str_replace('-', '', $request->search_sdate);
            $endDate = str_replace('-', '', $request->search_edate);
            $lineChartInfo = $lineChartInfo->WHERE('app_date', '>=', $startDate)->WHERE('app_date', '<=', $endDate);
            $barChartInfo = $barChartInfo->WHERE('app_date', '>=', $startDate)->WHERE('app_date', '<=', $endDate);
            $bInfo = $bInfo->WHERE('app_date', '>=', $startDate)->WHERE('app_date', '<=', $endDate);
            $prc = $prc->WHERE('app_date', '>=', $startDate)->WHERE('app_date', '<=', $endDate);
            $orgn = $orgn->WHERE('app_date', '>=', $startDate)->WHERE('app_date', '<=', $endDate);
        }
        else
        {
            $startDate = str_replace('-', '', $sdate);
            $endDate = str_replace('-', '', $edate);
            $lineChartInfo = $lineChartInfo->WHERE('app_date', '>=', $startDate)->WHERE('app_date', '<=', $endDate);
            $barChartInfo = $barChartInfo->WHERE('app_date', '>=', $startDate)->WHERE('app_date', '<=', $endDate);
            $bInfo = $bInfo->WHERE('app_date', '>=', $startDate)->WHERE('app_date', '<=', $endDate);
            $prc = $prc->WHERE('app_date', '>=', $startDate)->WHERE('app_date', '<=', $endDate);
            $orgn = $orgn->WHERE('app_date', '>=', $startDate)->WHERE('app_date', '<=', $endDate);
        }

        $lineChartInfo = $lineChartInfo->GET()->TOARRAY();
        $lineChartInfo = Func::chungDec(["COMPLAIN"], $lineChartInfo);	// CHUNG DATABASE DECRYPT

        $barChartInfo = $barChartInfo->GET()->TOARRAY();
        $barChartInfo = Func::chungDec(["COMPLAIN"], $barChartInfo);	// CHUNG DATABASE DECRYPT

        $bInfo = $bInfo->GET()->TOARRAY();
        $bInfo = Func::chungDec(["COMPLAIN"], $bInfo);	// CHUNG DATABASE DECRYPT

        $prc = $prc->GET()->TOARRAY();
        $prc = Func::chungDec(["COMPLAIN"], $prc);	// CHUNG DATABASE DECRYPT
        
        $orgn = $orgn->GET()->TOARRAY();
        $orgn = Func::chungDec(["COMPLAIN"], $orgn);	// CHUNG DATABASE DECRYPT

        

        /*
         * 접수기관별 추이 차트정보
        */
        for($i = 0; $i<sizeof($lineChartInfo); $i++)
        {
            if(isset($lineChartCount[substr($lineChartInfo[$i]->app_date,0,6)][$lineChartInfo[$i]->complain_office_cd]))
            {
                $lineChartCount[substr($lineChartInfo[$i]->app_date,0,6)][$lineChartInfo[$i]->complain_office_cd] += $lineChartInfo[$i]->chart_count;
            }
            else
            {
                $lineChartCount[substr($lineChartInfo[$i]->app_date,0,6)][$lineChartInfo[$i]->complain_office_cd] = $lineChartInfo[$i]->chart_count;
            }
        }

        /*
         * 팀별 추이 차트정보
        */
        for($i = 0; $i<sizeof($barChartInfo); $i++)
        {
            if(isset($barChartCount[substr($barChartInfo[$i]->app_date,0,6)][$barChartInfo[$i]->prc_branch]))
            {
                $barChartCount[substr($barChartInfo[$i]->app_date,0,6)][$barChartInfo[$i]->prc_branch] += $barChartInfo[$i]->chart_count;
            }
            else
            {
                $barChartCount[substr($barChartInfo[$i]->app_date,0,6)][$barChartInfo[$i]->prc_branch] = $barChartInfo[$i]->chart_count;
            }
        }
        
        /*
         * 모든부서, 담당자 정보, 처리 소요 날짜
        */
        for($i=0; $i<sizeof($bInfo); $i++)
        {
            $branch[$bInfo[$i]->prc_branch] = $branchArr[$bInfo[$i]->prc_branch];
            $managerArr[$bInfo[$i]->prc_manager_id] = $bInfo[$i]->prc_branch;

            //처리소요일수
            if($bInfo[$i]->prc_rs == 'Y' && !empty($bInfo[$i]->complain_office_cd))
            {
                $dateDiff = abs(strtotime($bInfo[$i]->prc_date) - strtotime($bInfo[$i]->app_date));
                $days = floor($dateDiff / (60 * 60 * 24));
                $totalDays += $days;
                
                //기관별 조치결과별 처리소요일수
                if(isset($dateCount[$bInfo[$i]->complain_office_cd][$bInfo[$i]->action_rs]))
                {
                    $dateCount[$bInfo[$i]->complain_office_cd][$bInfo[$i]->action_rs] += $days;
                }
                else
                {
                    $dateCount[$bInfo[$i]->complain_office_cd][$bInfo[$i]->action_rs] = $days;
                }

                //기관별 처리소요일수
                if(isset($orgnDateCount[$bInfo[$i]->complain_office_cd]))
                {
                    $orgnDateCount[$bInfo[$i]->complain_office_cd] += $days;
                }
                else
                {
                    $orgnDateCount[$bInfo[$i]->complain_office_cd] = $days;
                }
            }
        }


        /*
         * 처리부서, 처리담당자 기준 정보 (팀별 담당자별 현황)
        */
        for($i = 0; $i<sizeof($prc); $i++)
        {
            //부서별 기관별 발생건수
            if(isset($prcCount[$prc[$i]->prc_branch][$prc[$i]->complain_office_cd]))
            {
                $prcCount[$prc[$i]->prc_branch][$prc[$i]->complain_office_cd] += $prc[$i]->count;
            }
            else
            {
                $prcCount[$prc[$i]->prc_branch][$prc[$i]->complain_office_cd] = $prc[$i]->count;
                
            }

            //구성비 구하기 위한 부서별 발생건수
            if(isset($prcRateCount[$prc[$i]->prc_branch]))
            {
                $prcRateCount[$prc[$i]->prc_branch] += $prc[$i]->count;
            }
            else
            {
                $prcRateCount[$prc[$i]->prc_branch] = $prc[$i]->count;
                
            }

            //부서별 조치결과별 발생건수
            if(isset($prcActionCount[$prc[$i]->prc_branch][$prc[$i]->action_rs]))
            {
                $prcActionCount[$prc[$i]->prc_branch][$prc[$i]->action_rs] += $prc[$i]->count;
            }
            else
            {
                $prcActionCount[$prc[$i]->prc_branch][$prc[$i]->action_rs] = $prc[$i]->count;
            }

            //담당자별 기관별 발생건수
            if(isset($orgnArr[$prc[$i]->prc_manager_id][$prc[$i]->complain_office_cd]))
            {
                $orgnArr[$prc[$i]->prc_manager_id][$prc[$i]->complain_office_cd] += $prc[$i]->count;
            }
            else{
                $orgnArr[$prc[$i]->prc_manager_id][$prc[$i]->complain_office_cd] = $prc[$i]->count;
            }

            //구성비 구하기 위한 담당자별 발생건수
            if(isset($orgnRateArr[$prc[$i]->prc_manager_id]))
            {
                $orgnRateArr[$prc[$i]->prc_manager_id] += $prc[$i]->count;
            }
            else{
                $orgnRateArr[$prc[$i]->prc_manager_id] = $prc[$i]->count;
            }

            //담당자별 조치결과별 발생건수
            if(isset($actionCount[$prc[$i]->prc_manager_id][$prc[$i]->action_rs]))
            {
                $actionCount[$prc[$i]->prc_manager_id][$prc[$i]->action_rs] += $prc[$i]->count;
            }
            else
            {
                $actionCount[$prc[$i]->prc_manager_id][$prc[$i]->action_rs] = $prc[$i]->count;
            }
            
            $rateCount += $prc[$i]->count;
        }
        
        for($i = 0; $i<sizeof($prc); $i++)
        {
            //처리담당자 구성비
            $managerRate[$prc[$i]->prc_manager_id] = round(($orgnRateArr[$prc[$i]->prc_manager_id] / $rateCount) * 100, 2);
            //팀별 구성비
            $prcRate[$prc[$i]->prc_branch] = round(($prcRateCount[$prc[$i]->prc_branch] / $rateCount) * 100, 2);
        }


        /*
         * 민원접수기관 기준 정보 (처리현황)
        */
        for($i = 0; $i<sizeof($orgn); $i++)
        {
            //요청내역 기관별 건수
            if(isset($orgnCount[$orgn[$i]->complain_office_cd]))
            {
                $orgnCount[$orgn[$i]->complain_office_cd] += $orgn[$i]->orgn_count;
            }
            else
            {
                $orgnCount[$orgn[$i]->complain_office_cd] = $orgn[$i]->orgn_count;
            }

            //처리내역 기관별 건수
            if(isset($orgnPrcCount[$orgn[$i]->complain_office_cd][$orgn[$i]->prc_rs]))
            {
                $orgnPrcCount[$orgn[$i]->complain_office_cd][$orgn[$i]->prc_rs] += $orgn[$i]->orgn_count;
            }
            else
            {
                $orgnPrcCount[$orgn[$i]->complain_office_cd][$orgn[$i]->prc_rs] = $orgn[$i]->orgn_count;
            }

            //처리내역 조치결과별 건수
            $orgnActionCount[$orgn[$i]->complain_office_cd][$orgn[$i]->action_rs] = $orgn[$i]->orgn_count;
            
            //처리내역 총계
            if($orgn[$i]->prc_rs == 'Y')
            {
                $totalPrcOrgn += $orgn[$i]->orgn_count;
            }
            //요청내역 총계
            $totalOrgn += $orgn[$i]->orgn_count;
        }
        
        
        for($i = 0; $i<sizeof($orgn); $i++)
        {   
            //요청내역 구성비
            $orgnRate[$orgn[$i]->complain_office_cd] = round(($orgnCount[$orgn[$i]->complain_office_cd] / $totalOrgn) * 100, 2);
            //처리내역 합의취하/합의불성립 구성비
            $orgnPrcRate[$orgn[$i]->complain_office_cd][$orgn[$i]->action_rs] = ($totalPrcOrgn != 0)?(round(($orgnActionCount[$orgn[$i]->complain_office_cd][$orgn[$i]->action_rs] / $totalPrcOrgn) * 100, 2)):0;
            //처리내역 소계 구성비
            $totalOrgnPrcRate[$orgn[$i]->complain_office_cd][$orgn[$i]->prc_rs] = ($totalPrcOrgn != 0)?(round(($orgnPrcCount[$orgn[$i]->complain_office_cd][$orgn[$i]->prc_rs] / $totalPrcOrgn) * 100, 2)):0;
        }
        
        
        return view('erp.complainanalysis')
        ->with('configArr',$configArr)->with('resultArr',$resultArr)->with('orgnArr',$orgnArr)->with('managerArr',$managerArr)
        ->with('managerRate',$managerRate)->with('prcRate',$prcRate)->with('orgnRate',$orgnRate)->with('orgnPrcRate',$orgnPrcRate)->with('totalOrgnPrcRate',$totalOrgnPrcRate)
        ->with('prcCount',$prcCount)->with('actionCount',$actionCount)->with('prcActionCount',$prcActionCount)->with('orgnActionCount',$orgnActionCount)
        ->with('orgnPrcCount',$orgnPrcCount)->with('orgnCount',$orgnCount)->with('dateCount',$dateCount)->with('orgnDateCount',$orgnDateCount)
        ->with('lineChartCount',$lineChartCount)->with('barChartCount',$barChartCount)
        ->with('totalDays',$totalDays)->with('branch',$branch)->with('totalOrgn',$totalOrgn)->with('totalPrcOrgn',$totalPrcOrgn)
        ->with('lastCount',$lastCount->app_count)->with('lastYearCount',$lastYearCount->app_count)->with('beforeLastCount',$beforeLastCount->app_count)
        ->with('sdate',$sdate)->with('edate',$edate)->with('chartDates',$chartDates);
    }
    
}
