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

class TransferController extends Controller
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
     * 양수/양도결재 조회 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataTransferList(Request $request){

        $configArr = Func::getConfigArr();

        $list   = new DataList(Array("listName"=>"transferList","listAction"=>'/'.$request->path()));
        
        if(!isset($request->tabs)) $request->tabs = 'A';
        
        $list->setTabs(Array(
            'A'=>'접수', 'E'=>'완료', 'X'=>'취소'), $request->tabs);

        $list->setHidden(["target_sql"=>""]);
        $list->setPlusButton("transferForm('')");
        $list->setSearchDate('날짜검색',Array('trade_date'=>'적용기준일'),'searchDt','Y');
        
        $list->setSearchDetail(Array(
            'L.no' => '계약번호'

        ));

        return $list;
    }

    /**
     * 양수/양도결재 메인
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function transfer(Request $request)
    {
        $list   = $this->setDataTransferList($request);
        $list->setlistTitleCommon(Array
        (
            'loan_info_no'     => Array('계약번호', 0, '', 'center', '', 'loan_info_no'), 
            'name'             => Array('이름', 0, '', 'center', '', 'ENC-name'),
            'loan_date'        => Array('계약일', 0, '', 'center', '', 'loan_date'),
            'contract_day'     => Array('약정일', 0, '', 'center', '', 'contract_day'),
            'loan_money'       => Array('대출액', 0, '', 'center', '', 'loan_money'),
            'trade_date'       => Array('적용기준일', 0, '', 'center', '', 'trade_date'),
            'trade_money'       => Array('적용금액', 0, '', 'center', '', 'trade_money'),

            'transfer_out_info'=> Array('양도정보', 0, '', 'center', '', 'transfer_out_info'),
            'transfer_in_info' => Array('양수정보', 0, '', 'center', '', 'transfer_in_info'),
            'a_id'             => Array('접수자', 0, '', 'center', '', 'a_id', ['a_time'=>['접수시간', 'a_time', '<br>']]),
            'e_id'             => Array('결재자', 0, '', 'center', '', 'e_id', ['e_time'=>['결재시간', 'e_time', '<br>']]),
        )); 
        return view('account.transfer')->with('result', $list->getList());
    }   
    
    /**
     * 양수/양도결재 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function transferList(Request $request)
    {
        $list   = $this->setDataTransferList($request);
        foreach($request->all() as $key => $val)
        {
            if($key=="tabsSelect") continue;
            $param[$key] = $val;
        }

        // 카운트 쿼리 
        $BOXC = DB::TABLE("LOAN_INFO AS L")->JOIN("CUST_INFO AS C", "L.CUST_INFO_NO", "=", "C.NO")->JOIN("INV_TRANSFER_INFO T","L.NO","=","T.LOAN_INFO_NO");
        $BOXC->SELECT(DB::RAW("
        COALESCE(SUM(CASE WHEN T.status = 'A' THEN 1 ELSE 0 END),0) AS A, 
        COALESCE(SUM(CASE WHEN T.status = 'E' THEN 1 ELSE 0 END),0) AS E, 
        COALESCE(SUM(CASE WHEN T.status = 'X' THEN 1 ELSE 0 END),0) AS X"));
        $BOXC->WHERE('C.SAVE_STATUS','Y');
        $BOXC->WHERE('L.SAVE_STATUS','Y');
        $BOXC->WHERE('L.STATUS', '!=', 'N');
        $BOXC->WHERE('T.SAVE_STATUS', 'Y');
        
        // 전지점 조회권한 없으면 자기 지점만
        if( !Func::funcCheckPermit("E004") )
        {
            $BOXC->WHEREIN('L.MANAGER_CODE', array_keys(Func::myPermitBranch()));
        }

        $count = $BOXC->FIRST();
        $r['tabCount'] = array_change_key_case((Array)$count, CASE_UPPER);


        // 기본쿼리
        $LOAN_LIST = DB::TABLE("LOAN_INFO AS L")->JOIN("CUST_INFO AS C", "L.CUST_INFO_NO", "=", "C.NO")->JOIN("INV_TRANSFER_INFO T","L.NO","=","T.LOAN_INFO_NO");
        $LOAN_LIST->SELECT("T.*", "C.NAME", "C.SSN", "L.NO AS LOAN_INFO_NO", "L.LOAN_DATE", "L.STATUS as contract_status", "L.CONTRACT_DAY", "L.LOAN_MONEY");
        $LOAN_LIST->WHERE('C.SAVE_STATUS','Y');
        $LOAN_LIST->WHERE('L.SAVE_STATUS','Y');
        $LOAN_LIST->WHERE('L.STATUS', '!=', 'N');
        $LOAN_LIST->WHERE('T.SAVE_STATUS', 'Y');

        // 결재상태
        if(!empty($request->tabsSelect))
        {
            $LOAN_LIST->WHERE('T.status', $request->tabsSelect);
        }
        
        $LOAN_LIST = $list->getListQuery('T', 'main', $LOAN_LIST, $param);

        $paging = new Paging($LOAN_LIST, $request->page, $request->listLimit, 10, $request->listName);


        // 뷰단 데이터 정리.
        $array_conf_code    = Func::getConfigArr();
        $arrBranch          = Func::getBranch();
        $arrManager         = Func::getUserList();

        $cnt = 0;
        $rslt = $LOAN_LIST->GET();
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO","INV_TRANSFER_LIST"], $rslt);	// CHUNG DATABASE DECRYPT

        $rs = DB::TABLE('LOAN_USR_INFO')->ORDERBY('NO')->get();
        $rs = Func::chungDec(["LOAN_USR_INFO"], $rs);	// CHUNG DATABASE DECRYPT
        $arrayUsr = [];
        foreach( $rs as $v) $arrayUsr[$v->no] = $v->name;

        foreach( $rslt as $v )
        {
            $transfer_info = json_decode($v->transfer_info, true);
            $link_c              = '<a class="hand" onClick="popUpFull(\'/account/transferform?no='.$v->no.'\',\'transferform\')">';

            $v->loan_date        = Func::dateFormat($v->loan_date);
            $v->name             = Func::nameMasking($v->name, 'Y');
            $v->loan_money       = number_format($v->loan_money);

            $v->trade_date       = Func::dateFormat($v->trade_date);
            $v->trade_money      = number_format($v->trade_money);

            $arrayInInfo=$arrayOutInfo=[];
            foreach($transfer_info['IN'] as $arr) $arrayInInfo[] = $arr['loan_usr_info_no']."_".$arrayUsr[$arr['loan_usr_info_no']]."[".number_format($arr['trade_money'])."]";
            foreach($transfer_info['OUT'] as $arr) $arrayOutInfo[] = $arr['loan_usr_info_no']."_".$arrayUsr[$arr['loan_usr_info_no']]."[".number_format($arr['trade_money'])."]";

            $v->transfer_in_info = implode(" , ", $arrayInInfo);
            $v->transfer_out_info= implode(" , ", $arrayOutInfo);
            $v->a_id             = isset($arrManager[$v->a_id]) ? Func::nvl($arrManager[$v->a_id]->name, $v->a_id) : $v->a_id ;
            $v->e_id             = isset($arrManager[$v->e_id]) ? Func::nvl($arrManager[$v->e_id]->name, $v->e_id) : $v->e_id ;

            $v->a_time           = Func::dateFormat($v->a_time);
            $v->e_time           = Func::dateFormat($v->e_time);

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
     * 수익분배 - 입력창
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function transferForm(Request $request)
    {
        $status_color = "#6c757d";

        $arrayForm = $INV = [];
        if(isset($request->no))
        {
            $rs = DB::TABLE('LOAN_USR_INFO')->ORDERBY('NO')->get();
            $rs = Func::chungDec(["LOAN_USR_INFO"], $rs);	// CHUNG DATABASE DECRYPT
            $arrayUsr = [];
            foreach( $rs as $v)
            {
                $arrayUsr[$v->no] = $v->name;
                $arrayUsrSsn[$v->no] = $v->ssn;
            }
            
            $INV = DB::TABLE("inv_transfer_info")->JOIN('loan_info','loan_info.no','=','inv_transfer_info.loan_info_no')->SELECT('inv_transfer_info.*')->WHERE('inv_transfer_info.no',$request->no)->first();
            $transInfo = json_decode($INV->transfer_info, true);

            //Log::debug(print_r($transInfo, true));

            // 투자내역 기준으로 양도/양수정보 결재내역을 Setting 한다.
            $rs = DB::TABLE("loan_info")->WHERE("SAVE_STATUS","Y")->WHERE("NO",$INV->loan_info_no)->ORDERBY('loan_usr_info_no')->get();
            $rs = Func::chungDec(["loan_info"], $rs);	// CHUNG DATABASE DECRYPT
            
            foreach($rs as $v)
            {
                // dd($v);
                if($v->balance > 0)
                {
                    $arrayForm['INV'][$v->no]['loan_info_no'] = $v->no;
                    $arrayForm['INV'][$v->no]['loan_usr_info_no'] = $v->loan_usr_info_no;
                    $arrayForm['INV'][$v->no]['trade_date'] = Func::dateFormat($v->trade_date);
                    $arrayForm['INV'][$v->no]['name'] = Func::nameMasking($arrayUsr[$v->loan_usr_info_no],'Y');
                    $arrayForm['INV'][$v->no]['ssn'] = Func::ssnFormat($arrayUsrSsn[$v->loan_usr_info_no], 'N');
                    $arrayForm['INV'][$v->no]['balance'] = $v->balance;
                }
                
                //Log::debug(print_r($arrayForm, true));

                if(isset($transInfo['OUT']))
                {
                    foreach($transInfo['OUT'] as $arr)
                    {
                        unset($inData);
                        if($v->no==$arr['loan_info_no'])
                        {
                            $inData['loan_info_no'] = $arr['loan_info_no'];
                            $inData['loan_usr_info_no'] = $arr['loan_usr_info_no'];
                            $inData['trade_date'] = Func::dateFormat($v->trade_date);
                            $inData['balance'] = $v->balance;                            
                            $inData['name'] = Func::nameMasking($arrayUsr[$arr['loan_usr_info_no']],'Y');
                            $inData['ssn'] = Func::ssnFormat($arrayUsrSsn[$arr['loan_usr_info_no']], 'N');
                            $inData['trade_money'] = $arr['trade_money'];     // 양도금액
                            $arrayForm['OUT'][] = $inData;

                            // 양도대상으로 존재하는건은 투자내역에서 삭제
                            unset($arrayForm['INV'][$arr['loan_info_no']]);                            
                        }
                    }
                }
            }

            if(isset($transInfo['IN']))
            {
                foreach($transInfo['IN'] as $arr)
                {
                    unset($inData);
                    $inData['loan_usr_info_no'] = $arr['loan_usr_info_no'];
                    $inData['name'] = Func::nameMasking($arrayUsr[$arr['loan_usr_info_no']], 'Y');
                    $inData['ssn'] = Func::ssnFormat($arrayUsrSsn[$arr['loan_usr_info_no']], 'N');
                    $inData['trade_money'] = $arr['trade_money'];     // 양수금액

                    $arrayForm['IN'][] = $inData;
                }
            }

            $actMode = "UPDATE";
        }
        else
        {
            $actMode = "INSERT";
        }

        //Log::debug(print_r($arrayForm, true));
        return view('account.transferform')->with("actMode",$actMode)
                                        ->with("rs", $INV)
                                        ->with("jsonData",$arrayForm)
                                        ->with("status_color", $status_color);
    }    

    public function transferAction(Request $request)
    {
        $param = $request->all();
        Log::debug(print_r($param, true));

        $JSON_DATA = [];
        foreach($param as $key => $val)
        {
            unset($arr);
            // 양도정보
            if(substr_count($key, "out_money_"))
            {
                $arr['loan_info_no'] = substr($key, strlen("out_money_"));

                $iv = DB::TABLE('loan_info')->SELECT('loan_usr_info_no')->WHERE('no', $arr['loan_info_no'])->FIRST();
                $arr['loan_usr_info_no'] = $iv->loan_usr_info_no;
                $arr['trade_money'] = str_replace(",","",$val);
                $JSON_DATA['OUT'][] = $arr;
            }
            // 양수정보
            if(substr_count($key, "in_money_"))
            {
                $arr['loan_usr_info_no'] = substr($key, strlen("in_money_"));
                $arr['trade_money'] = str_replace(",","",$val);
                $JSON_DATA['IN'][] = $arr;
            }
        }

        $_DATA['trade_date'] = str_replace("-","",$param['trade_date']);
        $_DATA['trade_money'] = $param['trade_money'];
        $_DATA['transfer_info'] = json_encode($JSON_DATA, JSON_UNESCAPED_UNICODE);        
        $_DATA['save_id'] = Auth::id();
        $_DATA['save_time'] = date("YmdHis");
        
        DB::beginTransaction();
        if($request->actMode == "INSERT")
        {
            $_DATA['save_status'] = "Y";
            $_DATA['status'] = "A";
            $_DATA['loan_info_no'] = $param['loan_info_no'];
            $_DATA['a_id'] = $_DATA['save_id'];
            $_DATA['a_time'] = $_DATA['save_time'];

            $no = "";
            $rslt = DB::dataProcess('INS', 'inv_transfer_info', $_DATA, null,$no);

            if(isset($rslt) && $rslt == "Y")
            {
                $array_result['result_msg'] = "정상처리 되었습니다.";
            }
            else
            {
                $array_result['result_msg'] = "처리에 실패하였습니다.";
                $array_result['rs_code'] = "N";
                Log::debug('INSERT INV_TRANSFER_INFO 에러');
                DB::rollback();
            }
        }
        else
        {            
            if($request->actMode == "CANCEL")
            {
                // 취소적용
                $rslt = DB::dataProcess('UPD', 'inv_transfer_info', ['status'=>'X','del_id'=>Auth::id(), 'del_time'=>date("YmdHis")], ["no"=>$request->no]);

                if(isset($rslt) && $rslt == "Y")
                {
                    $array_result['result_msg'] = "정상처리 되었습니다.";
                }
                else
                {
                    $array_result['result_msg'] = "처리에 실패하였습니다.";
                    $array_result['rs_code'] = "N";
                    Log::debug('CANCEL INV_TRANSFER_INFO 에러');
                    DB::rollback();
                }
            }
            else if($request->actMode == "SAVE")
            {
                $rslt = DB::dataProcess('UPD', 'inv_transfer_info', $_DATA, ["no"=>$request->no]);

                if(isset($rslt) && $rslt == "Y")
                {
                    $array_result['result_msg'] = "정상처리 되었습니다.";
                }
                else
                {
                    $array_result['result_msg'] = "처리에 실패하였습니다.";
                    $array_result['rs_code'] = "N";
                    Log::debug('SAVE INV_TRANSFER_INFO 에러');
                    DB::rollback();
                }
            }
            else if($request->actMode == "CONFIRM")
            {
                $arrInvData = [];
                
                $chk = DB::TABLE('inv_transfer_info')->SELECT('status')->WHERE('no', $request->no)->FIRST();
                if($chk->status=="E")
                {
                    $array_result['result_msg'] = "기처리결재건. 중복실행으로 감지되어 중지.";
                    $array_result['rs_code'] = "N";
                    Log::debug('양수/양도 중복실행 #'.$request->no);
                    DB::rollback();
                    return $array_result;
                }

                $_DATA['status'] = "E";                 // 완료
                $_DATA['e_id'] = $_DATA['save_id'];
                $_DATA['e_time'] = $_DATA['save_time'];
                $rslt = DB::dataProcess('UPD', 'inv_transfer_info', $_DATA, ["no"=>$request->no]);
                
                if(!isset($rslt) || $rslt != "Y")
                {
                    $array_result['result_msg'] = "처리에 실패하였습니다.";
                    $array_result['rs_code'] = "N";
                    Log::debug('CONFIRM INV_TRANSFER_INFO 에러');
                    DB::rollback();
                    return $array_result;
                }
                
                // 양도 투자건 UPDATE
                if(isset($JSON_DATA['OUT']) && sizeof($JSON_DATA['OUT']))
                {
                    for($i=0; $i<sizeof($JSON_DATA['OUT']); $i++)
                    {
                        // 양도금액이 있는 경우
                        if($JSON_DATA['OUT'][$i]['trade_money'] > 0)
                        {
                            $rs = DB::TABLE("loan_info")->WHERE("SAVE_STATUS","Y")->WHERE("NO", $param['loan_info_no'])->WHERE("loan_usr_info_no",$JSON_DATA['OUT'][$i]['loan_usr_info_no'])->WHERE("NO",$JSON_DATA['OUT'][$i]['loan_info_no'])->FIRST();
                            $arrInvData = get_object_vars($rs);

                            unset($_UP);
                            $_UP['balance'] = ($rs->balance - $JSON_DATA['OUT'][$i]['trade_money']>0) ? $rs->balance - $JSON_DATA['OUT'][$i]['trade_money'] : 0;
                            if($_UP['balance']<=0)
                            {
                                $_UP['fullpay_date'] = $_DATA['trade_date'];  // 양도로 인한 종결일경우 종결일자 UPDATE
                                $_UP['fullpay_money'] = $rs->fullpay_money+$rs->balance;      // 양도금액입력
                            }
                            else
                            {
                                $_UP['fullpay_date'] = $_DATA['trade_date'];                // 일부양도 양도날짜입력
                                $_UP['fullpay_money'] = $rs->fullpay_money+$JSON_DATA['OUT'][$i]['trade_money'];    // 양도금액입력
                            }
                            $_UP['save_id'] = $_DATA['save_id'];
                            $_UP['save_time'] = $_DATA['save_time'];
                            $_UP['toss_flag'] = "Y";
                            Log::debug(print_r($_UP, true));
                            $rslt = DB::dataProcess('UPD', 'loan_info', $_UP, ["no"=>$rs->no]);

                            if(!isset($rslt) || $rslt != "Y")
                            {
                                $array_result['result_msg'] = "처리에 실패하였습니다.";
                                $array_result['rs_code'] = "N";
                                Log::debug('UPDATE loan_info 에러');
                                DB::rollback();
                                return $array_result;
                            }
                            
                            // 금액변동이 있는경우 입력
                            if($rs->balance != $_UP['balance'])
                            {
                                // 양도 후 잔여액이 경우 금액 지정
                                $valmoney = [];
                                $valmoney['loan_info_no']          = $rs->no;
                                $valmoney['trade_date']      = $_DATA['trade_date'];
                                $valmoney['balance']  = $_UP['balance'];
                                $valmoney['save_status']     = 'Y';
                                $valmoney['save_time']       = $_DATA['save_time'];
                                $valmoney['save_id']         = $_DATA['save_id'];
                                $rslt = DB::dataProcess('INS', 'INV_TAIL_MONEY', $valmoney);
                            }

                            Log::debug("[".$arrInvData['trade_date']."][".$_DATA['trade_date']."]");
                            // 투자시작일 기준 양도의 경우
                            if($arrInvData['trade_date']==$_DATA['trade_date'])
                            {
                                if($_UP['balance'] > 0)
                                {
                                    // 분배예정스케줄 생성 - 변경 기준일 이후 스케줄만 반영
                                    $inv = new Invest($rs->no); 
                                    $array_plan = $inv->buildPlanData($arrInvData['trade_date']);
                                    $inv->savePlan($array_plan, $arrInvData['trade_date']);
                                }
                                else
                                {
                                    // 양도하고 종결일경우 스케줄을 다 지운다.
                                    $rs_p = DB::TABLE('loan_info_return_plan')->WHERE('loan_info_no', $rs->no)->ORDERBY('seq')->get();
                                    foreach($rs_p as $vp)
                                    {
                                        $vp = get_object_vars($vp);
                                        $rslt = DB::dataProcess("INS", "loan_info_return_plan_log", $vp);
                                    }
                                    $rslt = DB::dataProcess("DEL", "loan_info_return_plan", Array(), [['loan_info_no','=',$rs->no]]);
                                    $rslt = DB::dataProcess('UPD', 'loan_info', ['sum_interest'=>0,'sum_withholding_tax'=>0,'sum_income_tax'=>0,'sum_local_tax'=>0], ["no"=>$rs->no]);
                                }
                            }
                            else
                            {
                                /*
                                $sch = DB::TABLE("loan_info_return_plan")->SELECT("SEQ, PLAN_INTEREST_SDATE")->WHERE('loan_info_no', $rs->no)->WHERE('PLAN_INTEREST_SDATE','<',$_DATA['trade_date'])->WHERE('PLAN_INTEREST_EDATE','>',$_DATA['trade_date'])->WHERERAW("(divide_flag is null or divide_flag = 'N')")->FIRST();

                                // 조정대상 스케줄이 있는경우만 스케줄 갱신 - 이미 지급기준일까지
                                if(!empty($sch->plan_interest_sdate))                                
                                {
                                    // 분배예정스케줄 생성 - 변경 기준일 이후 스케줄만 반영
                                    $inv = new Invest($rs->no); 
                                    $array_plan = $inv->buildPlanData($sch->plan_interest_sdate, $sch->seq);
                                    $inv->savePlan($array_plan, $sch->plan_interest_sdate);
                                }
                                */

                                $inv = new Invest($rs->no); 
                                $rebuildInfo = $inv->rebuildInfo($rs->no, $_DATA['trade_date']);    // 거래일 기준으로 스케줄 갱신
                                $array_plan = $inv->buildPlanData($rebuildInfo['start_date'], $rebuildInfo['start_seq']);
                                $rslt = $inv->savePlan($array_plan, $rebuildInfo['start_date']);

                                if( $rslt!="Y" )
                                {
                                    $array_result['result_msg'] = "분배예정스케줄 생성 Error";
                                    $array_result['rs_code'] = "N";
                                    Log::debug('분배예정스케줄 생성 에러');
                                    DB::rollback();
                                    return $array_result;
                                }
                            }
                            
                            // 미지급된 이자내역 모두 지급처리대상으로 지정
                            $sch = DB::TABLE("loan_info_return_plan")->SELECTRAW("SUM(plan_interest) as plan_interest, SUM(coalesce(withholding_tax,0)) as withholding_tax, SUM(coalesce(platform_fee,0)) as platform_fee")->WHERE('loan_info_no', $rs->no)->WHERERAW("(divide_flag is null or divide_flag = 'N')")->FIRST();
                            $totInterest = $sch->plan_interest - $sch->withholding_tax - $sch->platform_fee;    // 스케줄상 지급되지 않은 이자지급액
                            $originMoney = $JSON_DATA['OUT'][$i]['trade_money'];                                // 양도금액

                            $_PAY = [];
                            $_PAY['loan_info_no'] = $param['loan_info_no'];
                            $_PAY['loan_info_no'] = $rs->no;
                            $_PAY['loan_usr_info_no'] = $JSON_DATA['OUT'][$i]['loan_usr_info_no'];
                            $_PAY['div'] = '4';                 // 양수/양도(4)
                            $_PAY['rel_no'] = $request->no;

                            $_PAY['trade_date'] = $_DATA['trade_date'];
                            $_PAY['trade_money'] = ($_UP['balance'] <= 0) ? $originMoney+$totInterest : $originMoney;   // 총지급대상금액 : 원금+이자, 일부양도시에는 발생이자는 차감된 금액으로 스케줄대로 생성된다. 전체양도시에만 이자까지 포함.
                            $_PAY['interest'] = ($_UP['balance'] <= 0) ? $totInterest : 0;   // 실지급액
                            $_PAY['origin'] = $originMoney;                     // 투자금 감소분
                            $_PAY['status'] = 'A';                              // 접수
                            $_PAY['save_status'] = 'Y';
                            $_PAY['save_id'] = $_DATA['save_id'];
                            $_PAY['save_time'] = $_DATA['save_time'];
                            $rslt = DB::dataProcess("INS", "interest_pay_info", $_PAY);
                            if( $rslt!="Y" )
                            {
                                $array_result['result_msg'] = "투자자 지급내역 등록 Error";
                                $array_result['rs_code'] = "N";
                                Log::debug('ISNERT interest_pay_info 에러');
                                DB::rollback();
                                return $array_result;
                            }
                        }
                    }
                }

                // 양수 투자건 신규 입력
                if(isset($JSON_DATA['IN']) && sizeof($JSON_DATA['IN']))
                {
                    for($i=0; $i<sizeof($JSON_DATA['IN']); $i++)
                    {
                        // 양수금액이 있는 경우
                        if($JSON_DATA['IN'][$i]['trade_money'] > 0)
                        {
                            $loan_usr_info_no =  $JSON_DATA['IN'][$i]['loan_usr_info_no'];
                            $trade_money =  $JSON_DATA['IN'][$i]['trade_money'];

                            $lv = DB::TABLE('loan_info')->SELECT('loan_money, contract_date, contract_end_date, pro_cd, pay_term, invest_rate, platform_fee_rate')->WHERE('no',$param['loan_info_no'])->FIRST();
                            $uv = DB::TABLE('loan_usr_info')->SELECT('tax_free')->WHERE('no', $loan_usr_info_no)->FIRST();

                            $inv_rs = DB::TABLE('loan_info')->SELECT('*')->WHERE('save_status','Y')->WHERE('loan_info_no', $param['loan_info_no'])->WHERE('loan_usr_info_no', $loan_usr_info_no)->FIRST();
                            
                            $_INV = [];
                            $_INV['loan_usr_info_no'] = $loan_usr_info_no;
                            $_INV['loan_info_no'] = $param['loan_info_no'];
                            $_INV['loan_money'] = $lv->loan_money;
                            $_INV['trade_money'] = $_INV['balance'] = $trade_money;
                            $_INV['trade_date'] = $_DATA['trade_date'];
                            $_INV['contract_date'] = $lv->contract_date;
                            $_INV['contract_end_date'] = $lv->contract_end_date;
                            $_INV['status'] = 'E';
                            $_INV['save_status'] = "Y";
                            $_INV['save_id'] = $_DATA['save_id'];
                            $_INV['save_time'] = $_DATA['save_time'];
                            $_INV['toss_flag'] = "Y";
                            $_INV['fullpay_date'] = $_INV['contract_end_date'];
                            $_INV['pro_cd'] = $lv->pro_cd;
                            $_INV['ph_chk'] = 'ph1';
                            $_INV['addr_chk'] = 'addr1';
                            $_INV['bank_chk'] = 'bank1';
                            
                            // 기투자 내역이 있는 투자자의 경우 - 기존 투자내역정보를 기준으로 투자계약 생성
                            if( isset($inv_rs) )
                            {
                                $_INV['platform_fee_rate'] = $inv_rs->platform_fee_rate;
                                $_INV['invest_rate'] = $inv_rs->invest_rate;
                                $_INV['contract_day'] = $inv_rs->contract_day;
                                $_INV['pay_term'] = $inv_rs->pay_term;
                                $_INV['tax_free'] = $inv_rs->tax_free;
                            }
                            else
                            {
                                $_INV['platform_fee_rate'] = $lv->platform_fee_rate;
                                $_INV['invest_rate'] = $lv->invest_rate;
                                $_INV['contract_day'] = $arrInvData['contract_day'];                // 양도건 중 마지막내역.. 
                                $_INV['pay_term'] = $lv->pay_term;
                                $_INV['tax_free'] = !empty($uv->tax_free) ? $uv->tax_free : "N";
                            }
                                                            
                            $rslt = DB::dataProcess('INS', 'loan_info', $_INV, null, $loan_info_no);
                            $_INV['no'] = $loan_info_no;

                            if(!isset($rslt) || $rslt != "Y")
                            {
                                $array_result['result_msg'] = "처리에 실패하였습니다.";
                                $array_result['rs_code'] = "N";
                                Log::debug('INSERT loan_info 에러');
                                DB::rollback();
                                return $array_result;
                            }

                            $valratio = [];
                            $valratio['loan_info_no']          = $loan_info_no;
                            $valratio['rate_date']       = $_DATA['trade_date'];
                            $valratio['ratio']           = $lv->invest_rate;
                            $valratio['save_status']     = 'Y';
                            $valratio['save_time']       = $_DATA['save_time'];
                            $valratio['save_id']         = $_DATA['save_id'];
                            $rslt = DB::dataProcess('INS', 'INV_RATIO', $valratio);

                            if(!isset($rslt) || $rslt != "Y")
                            {
                                $array_result['result_msg'] = "처리에 실패하였습니다.";
                                $array_result['rs_code'] = "N";
                                Log::debug('INSERT INV_RATIO 에러');
                                DB::rollback();
                                return $array_result;
                            }

                            $valfee = [];
                            $valfee['loan_info_no']          = $loan_info_no;
                            $valfee['rate_date']       = $_DATA['trade_date'];
                            $valfee['platform_fee_rate'] = 0;
                            $valfee['save_status']     = 'Y';
                            $valfee['save_time']       = $_DATA['save_time'];
                            $valfee['save_id']         = $_DATA['save_id'];                            
                            $rslt = DB::dataProcess('INS', 'platform_fee_rate', $valfee);

                            if(!isset($rslt) || $rslt != "Y")
                            {
                                $array_result['result_msg'] = "처리에 실패하였습니다.";
                                $array_result['rs_code'] = "N";
                                Log::debug('INSERT platform_fee_rate 에러');
                                DB::rollback();
                                return $array_result;
                            }

                            $valmoney = [];
                            $valmoney['loan_info_no']          = $loan_info_no;
                            $valmoney['trade_date']      = $_DATA['trade_date'];
                            $valmoney['balance']  = $trade_money;
                            $valmoney['save_status']     = 'Y';
                            $valmoney['save_time']       = $_DATA['save_time'];
                            $valmoney['save_id']         = $_DATA['save_id'];
                            $rslt = DB::dataProcess('INS', 'INV_TAIL_MONEY', $valmoney);
                            
                            if(!isset($rslt) || $rslt != "Y")
                            {
                                $array_result['result_msg'] = "처리에 실패하였습니다.";
                                $array_result['rs_code'] = "N";
                                Log::debug('INSERT BALANCE 에러');
                                DB::rollback();
                                return $array_result;
                            }

                            // 분배예정스케줄 생성
                            $inv = new Invest($_INV); 
                            $array_plan = $inv->buildPlanData($_DATA['trade_date']);
                            $inv->savePlan($array_plan, $_DATA['trade_date']);
                        }
                    }
                }

                $array_result['result_msg'] = "정상처리 되었습니다.";
                $array_result['rs_code'] = "Y";
            }
        }

        DB::commit();
        return $array_result;
    }

    /**
     * 양수/양도 입력폼에서 찾기를 할 때, 결과 테이블 HTML 응답한다.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function transInvSearch(Request $request)
    {
        if( !isset($request->search_string) )
        {
            return "검색어를 입력하세요.";
        }
        $loan_info_no_auto = $request->loan_info_no_auto;
        $search_string     = $request->search_string;

        // 기본쿼리
        $INV = DB::table("LOAN_INFO")->JOIN("CUST_INFO","CUST_INFO.NO","=","LOAN_INFO.CUST_INFO_NO")
                ->select("LOAN_INFO.NO, CUST_INFO.NAME, LOAN_INFO.STATUS, LOAN_INFO.CONTRACT_DATE, LOAN_INFO.CONTRACT_END_DATE, LOAN_INFO.LOAN_MONEY, LOAN_INFO.BALANCE")
                ->WHERE("CUST_INFO.SAVE_STATUS", "Y")
                ->WHERE("LOAN_INFO.SAVE_STATUS", "Y")
                ->WHERE("LOAN_INFO.STATUS", "!=", "N")
                ->ORDERBY("LOAN_INFO.NO");

        // 검색
        $where = "";
        if( is_numeric($search_string) )
        {
            $where.= "LOAN_INFO.NO=".$search_string." ";
        }

        if($where!='')
        {
            $where = '('.$where.')';
        }

        $INV->whereRaw($where);
        $INV->orderBy("LOAN_INFO.NO","ASC");

        $rslt = $INV->get();
        $rslt = Func::chungDec(["LOAN_INFO","CUST_INFO"], $rslt);	// CHUNG DATABASE DECRYPT
        
        $string = "<table class='table table-sm table-hover card-secondary card-outline mt-1'>";
        $string.= "<tr class='text-center'>";
        $string.= "<td>계약No</td>";
        $string.= "<td>이름</td>";
        $string.= "<td>대출일</td>";
        $string.= "<td>만기일</td>";
        $string.= "<td>상태</td>";
        $string.= "<td>대출액</td>";
        $string.= "<td>잔액</td>";
        $string.= "</tr>";

        foreach( $rslt as $v )
        {
            $string.= "<tr role='button' onclick='selectInvInfo(".$v->no.");'>";
            $string.= "<td id='loan_info_no_".$v->no."' class='text-center'>".$v->no."</td>";
            $string.= "<td id='cust_name_".$v->no."'    class='text-center'>".$v->name."</td>";
            $string.= "<td id='contract_date_".$v->no."'    class='text-center'>".Func::dateFormat($v->contract_date)."</td>";
            $string.= "<td id='contract_end_date_".$v->no."'    class='text-center'>".Func::dateFormat($v->contract_end_date)."</td>";
            $string.= "<td class='text-center'>".Func::getInvStatus($v->status, true)."</td>";
            $string.= "<td id='loan_money_".$v->no."'   class='text-right'>".number_format($v->loan_money)."</td>";
            $string.= "<td id='loan_balance_".$v->no."' class='text-right'>".number_format($v->balance)."</td>";
            $string.= "</tr>";
        }
        $string.= "</table>";

        return $string;
    }

    /**
     * 양수/양도 입력폼에서 찾은 투자내역 선택시 대상 투자건의 투자내역 HTML 리턴한다
     *
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function transInvList(Request $request)
    {
        if( !isset($request->no) )
        {
            return "대상을 선택하세요.";
        }

        $loan_info_no     = $request->no;

        // 기본쿼리
        $INV = DB::table("loan_info")->JOIN("LOAN_USR_INFO","loan_info.loan_usr_info_no","=","LOAN_USR_INFO.investor_no")
                ->select("loan_info.*, LOAN_USR_INFO.NAME, LOAN_USR_INFO.relation, LOAN_USR_INFO.SSN")
                ->WHERE("loan_info.SAVE_STATUS", "Y")
                ->WHERE("LOAN_USR_INFO.SAVE_STATUS", "Y")
                ->WHERE("loan_info.NO",$loan_info_no)
                ->WHERE("loan_info.balance",">",0)
                ->ORDERBY("loan_info.loan_usr_info_no")
                ->ORDERBY("loan_info.balance","DESC");
                
        $rslt = $INV->get();
        $rslt = Func::chungDec(["loan_info","LOAN_USR_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        $string = "";
        foreach( $rslt as $v )
        {
            $string.= "<tr id='tr_inv_".$v->no."' role='button' onclick='addOutData(".$v->no.", ".$v->loan_usr_info_no.");'>";
            $string.= "<td id='inv_inv_no_".$v->no."' class='text-center' hidden>".$v->no."</td>";
            $string.= "<td id='inv_usr_info_no_".$v->no."' class='text-center'>".$v->loan_usr_info_no."</td>";
            $string.= "<td id='inv_usr_name_".$v->no."'    class='text-center'>".Func::nameMasking($v->name,'Y')."</td>";
            $string.= "<td id='inv_usr_trade_date_".$v->no."'    class='text-center'>".Func::dateFormat($v->trade_date)."</td>";
            $string.= "<td id='inv_usr_relation_".$v->no."'    class='text-center'>".$v->relation."</td>";
            $string.= "<td id='inv_usr_ssn_".$v->no."'    class='text-center'>".Func::ssnFormat($v->ssn, 'N')."</td>";
            $string.= "<td id='balance_".$v->no."'    class='text-right'>".number_format($v->balance)."</td>";
            $string.= "</tr>";
        }

        return $string;
    }

    /**
     * 양수/양도 입력폼에서 찾기를 할 때, 결과 테이블 HTML 응답한다.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function transUsrSearch(Request $request)
    {
        Log::debug(print_r($request->all(), true));
        if( !isset($request->search_string) )
        {
            return "검색어를 입력하세요.";
        }

        if( !isset($request->loan_info_no) )
        {
            return "양도대상을 검색하세요.";
        }

        $loan_info_no_auto = $request->loan_info_no_auto;
        $search_string     = $request->search_string;

        // 기본쿼리
        $INV = DB::table("LOAN_USR_INFO")->select("*")->WHERE("SAVE_STATUS", "Y");
        // 증액 or 재투자 가능하도록 요청 - 전산요청 81번
        //$INV->WHERERAW("NO NOT IN (SELECT loan_usr_info_no FROM loan_info WHERE SAVE_STATUS = 'Y' AND LOAN_INFO_NO = ".$request->loan_info_no.")");

        // 검색
        $where = "";
        if( is_numeric($search_string) )
        {
            $where.= "LOAN_USR_INFO.NO=".$search_string." ";
            // 6자리 이상인 경우만 검색
            if( strlen($search_string)>=6 )
            {
                $where.= 'or '.Func::encLikeSearchString('LOAN_USR_INFO.SSN', $search_string, 'after');
            }
        }
        else
        {
            $where.= Func::encLikeSearchString('LOAN_USR_INFO.NAME', $search_string, 'after');
        }

        if($where!='')
        {
            $where = '('.$where.')';
        }

        $INV->whereRaw($where);
        $INV->orderBy("LOAN_USR_INFO.NO","ASC");
        //Log::debug(Func::printQuery($INV));

        $rslt = $INV->get();
        $rslt = Func::chungDec(["LOAN_USR_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        $getConfigArr = Func::getConfigArr();
        
        $string = "<table class='table table-sm table-hover card-secondary card-outline mt-1'>";
        $string.= "<tr class='text-center'>";
        $string.= "<td>회원No</td>";
        $string.= "<td>투자자명</td>";
        $string.= "<td>생년월일</td>";
        $string.= "</tr>";

        foreach( $rslt as $v )
        {
            $string.= "<tr role='button' onclick='addInData(".$v->no.");'>";
            $string.= "<input type='hidden' id='ph1_".$v->no."' value='".$v->ph11."'>";
            $string.= "<input type='hidden' id='ph2_".$v->no."' value='".$v->ph12."'>";
            $string.= "<input type='hidden' id='ph3_".$v->no."' value='".$v->ph13."'>";
            $string.= "<input type='hidden' id='zip1_".$v->no."' value='".$v->zip1."'>";
            $string.= "<input type='hidden' id='addr1_".$v->no."' value='".$v->addr11."'>";
            $string.= "<input type='hidden' id='addr2_".$v->no."' value='".$v->addr12."'>";
            $string.= "<input type='hidden' id='bank_name_".$v->no."' value='".$getConfigArr['bank_cd'][$v->bank_cd]."'>";
            $string.= "<input type='hidden' id='bank_ssn_".$v->no."' value='".$v->bank_ssn."'>";
            $string.= "<td id='usr_info_no_".$v->no."' class='text-center'>".$v->no."</td>";
            $string.= "<td id='usr_name_".$v->no."'    class='text-center'>".$v->name."</td>";
            $string.= "<td id='usr_ssn_".$v->no."'    class='text-center'>".substr($v->ssn,0,6)."</td>";
            $string.= "</tr>";
        }
        $string.= "</table>";

        return $string;
    }
}