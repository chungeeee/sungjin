<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use DataList;
use App\Chung\Paging;
use Log;
use Auth;
use Loan;
use Trade;
use Ksnet;
use Vars;
use App\Chung\Sms;
use ExcelFunc;
use Illuminate\Support\Facades\Storage;

class AccountController extends Controller
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
     * 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataList(Request $request){

        $list   = new DataList(Array("listName"=>"account","listAction"=>'/'.$request->path()));
        
        if(!isset($request->tabs)) $request->tabs = 'S';
        $list->setTabs(Vars::$arrayAccountStatus,$request->tabs);
        
        $list->setCheckBox("no");

        $list->setSearchType('loan_info-pro_cd',Func::getConfigArr('pro_cd'),'상품구분', '', '', '', '', 'Y', '', true);

        $list->setSearchType('loan_info-handle_code',Func::getConfigArr('mo_acct_div'),'법인 구분', '', '', '', '', 'Y', '', true);

        $list->setSearchDate('날짜검색',Array('loan_info.return_date_biz' => '지급일','app_time' => '결재요청일','confirm_time' => '결재완료일','cancel_time' => '결재취소일'),'searchDt','Y');

        if( Func::funcCheckPermit("Z001") )
        {
            $list->setLumpForm('UPD', Array('BTN_NAME'=>'요청','BTN_ACTION'=>'lump_action(this)','BTN_ICON'=>'','BTN_COLOR'=>''));
            $list->setLumpForm('DEL', Array('BTN_NAME'=>'취소','BTN_ACTION'=>'lump_del(this)','BTN_ICON'=>'','BTN_COLOR'=>''));
        }
        $list->setSearchDetail(Array( 
            'loan_usr_info.nick_name' => '투자자명',
            'loan_info.investor_no'   => '투자자번호',
            'investor_no-inv_seq'     => '채권번호',
        ));    
        $list->setSearchDetailLikeOption("%%");
        return $list;
    }
    
    /**
     * 이체실행결재 메인화면
     *
     * @param  request
     * @return view
     */
	public function account(Request $request)
    {
        $list   = $this->setDataList($request);
        
        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬) 
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
        ));
        $list->setlistTitleTabs('S',Array
        (
            'app_time'                 => Array('요청일시', 0, '', 'center', '', 'app_time'),
            'app_id'                   => Array('요청자', 0, '', 'center', '', 'app_id'),
        ));
        $list->setlistTitleTabs('W',Array
        (
            'confirm_time'             => Array('결재일시', 0, '', 'center', '', 'confirm_time'),
            'confirm_id'               => Array('결재자', 0, '', 'center', '', 'confirm_id'),
        ));
        $list->setlistTitleTabs('A',Array
        (
            'firm_banking_status'      => Array('진행상태', 0, '', 'center', '', 'firm_banking_status'),
            'firm_banking_msg'         => Array('결과메세지', 0, '', 'center', '', 'firm_banking_msg'),
        ));
        $list->setlistTitleTabs('Y',Array
        (
            'send_time'                => Array('송금일시', 0, '', 'center', '', 'send_time'),
            'firm_banking_status'      => Array('진행상태', 0, '', 'center', '', 'firm_banking_status'),
        ));
        $list->setlistTitleTabs('F',Array
        (
            'fail_time'                => Array('실패일시', 0, '', 'center', '', 'fail_time'),
            'firm_banking_status'      => Array('진행상태', 0, '', 'center', '', 'firm_banking_status'),
            'firm_banking_msg'         => Array('결과메세지', 0, '', 'center', '', 'firm_banking_msg'),
        ));
        $list->setlistTitleTabs('N',Array
        (
            'cancel_time'              => Array('취소일시', 0, '', 'center', '', 'cancel_time'),
            'cancel_id'                => Array('취소자', 0, '', 'center', '', 'cancel_id'),
        ));

        $rslt['result'] = $list->getList();

        return view('account.account')->with($rslt);
    }
    
    /**
     * 이체실행결재 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function accountList(Request $request)
    {
        $list   = $this->setDataList($request);
        $param  = $request->all();
        
        // Tab count 
		if($request->isFirst=='1')
		{
            $COUNT_S = DB::table("account_transfer")->join("loan_info", "loan_info.no", "=", "account_transfer.loan_info_no")
                                                    ->join("loan_usr_info", "loan_usr_info.no", "=", "account_transfer.loan_usr_info_no")
                                                    ->SELECT(DB::raw("account_transfer.STATUS AS item, count(1) as cnt"))
                                                    ->where('account_transfer.save_status','Y')
                                                    ->where('loan_usr_info.save_status', 'Y')
                                                    ->where('loan_info.save_status','Y')
                                                    ->GROUPBY("account_transfer.status")->get(); 
			$r['tabCount'] = Func::getTabsCnt($COUNT_S,Vars::$arrayAccountStatus);
		}

        // 기본쿼리
        $SG = DB::table("account_transfer")->select("account_transfer.*", "loan_info.investor_type", "loan_usr_info.name")
                                            ->join("loan_info", "loan_info.no", "=", "account_transfer.loan_info_no")
                                            ->join("loan_usr_info", "loan_usr_info.no", "=", "account_transfer.loan_usr_info_no")
                                            ->where('loan_usr_info.save_status','Y')
                                            ->where('loan_info.save_status','Y')
                                            ->where('account_transfer.save_status','Y');

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
                            $SG = $SG->WHERE('loan_info.investor_no',$searchString[0])->WHERE('loan_info.investor_type',$string);
                        }
                        // 문자열있는 채권번호로 검색(ex. H5-1)
                        else 
                        {
                            $SG = $SG->WHERE('loan_info.investor_no',$searchString[0])
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
                        $SG = $SG->WHERE('loan_info.investor_no',$searchString[0]);          
                    }
                    // 채권번호로 검색(ex. 5-1)
                    else 
                    {
                        $SG = $SG->WHERE('loan_info.investor_no',$searchString[0])->WHERE('loan_info.inv_seq',$searchString[1]) ;          
                    }
                }
            }

            unset($param['searchString']);
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='loan_usr_info.nick_name' && !empty($param['searchString']) )
        {
            $SG = $SG->where('loan_usr_info.nick_name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        $SG = $list->getListQuery("account_transfer",'main',$SG,$param);
        $SG->orderBy("account_transfer.no", "desc");
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        //총 지급 금액 
        $sum_data = Array
        (
            ["coalesce(sum(account_transfer.return_money),0)", '총지급금액', '원'],
        );
        $paging = new Paging($SG, $request->page, $request->listLimit, 10, $request->listName,'',$sum_data);
        
        $rslt   = $SG->GET();
        $rslt = Func::chungDec(["account_transfer", "loan_usr_info"], $rslt);	// CHUNG DATABASE DECRYPT

        $cnt    = 0;
        $configArr     = Func::getConfigArr();
        $array_user_id = Func::getUserId();
        $getProCd      = Func::getConfigArr('pro_cd');
        $bank_cd       = Func::getConfigArr('bank_cd');
        $firm_status   = Vars::$arrayFirmBankingStatus;

        foreach ($rslt as $v)
        {
            $v->onclick                  = 'popUpFull(\'/account/investmentpop?no='.$v->loan_info_no.'\', \'account'.$v->loan_info_no.'\')';
            $v->line_style               = 'cursor: pointer;';
            
            $v->loan_usr_info_name       = $v->name ?? '';

            $v->pro_cd                   = Func::getArrayName($getProCd, $v->pro_cd);

            $v->cust_bank_cd             = $v->cust_bank_cd ? $bank_cd[$v->cust_bank_cd] : '';                         // 차입자 은행명
            $v->loan_bank_cd             = $v->loan_bank_cd ? $bank_cd[$v->loan_bank_cd] : '';                         // 투자자 은행명

            $v->investor_no_inv_seq      = $v->investor_type.$v->investor_no.'-'.$v->inv_seq;

            $v->app_time                 = Func::dateFormat($v->app_time);
            $v->app_id                   = Func::getArrayName($array_user_id,$v->app_id);
            
            $v->return_date_biz          = Func::dateFormat($v->return_date_biz);
            $v->return_money             = number_format($v->return_money);
            
            $v->fail_time                = Func::dateFormat($v->fail_time);
            $v->send_time                = Func::dateFormat($v->send_time);

            $v->confirm_time             = Func::dateFormat($v->confirm_time);
            $v->confirm_id               = Func::getArrayName($array_user_id,$v->confirm_id);
            $v->cancel_time              = Func::dateFormat($v->cancel_time);
            $v->cancel_id                = Func::getArrayName($array_user_id,$v->cancel_id);
            
            $v->firm_banking_status      = isset($firm_status[$v->firm_banking_status]) ? $firm_status[$v->firm_banking_status] : '';
            
            $r['v'][] = $v;
            $cnt ++;
        }

        $r['pageList']  = $paging->getPagingHtml($request->path());
        $r['result']    = 1;
        $r['txt']       = $cnt;
        
        return json_encode($r);
    }

    /**
     * 송금상세정보
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function accountInfo(Request $request)
    {
        $account_transfer[0] = $account_transfer[0] = 0;

        if($request->sno)
        {
            $account_transfer = DB::TABLE("account_transfer AS S")->SELECT("S.*")->WHERE('S.NO', $request->sno)->GET();
            $account_transfer = Func::chungDec(["account_transfer"], $account_transfer);	// CHUNG DATABASE DECRYPT

            // 계약번호/송금대금 데이터세팅
            $account_transfer[0]->lin_sm = null;
            $array_loan_info_nos = explode(",",$account_transfer[0]->loan_info_nos);
            $array_account_moneys   = explode(",",$account_transfer[0]->account_moneys);
            foreach($array_loan_info_nos as $key => $v)
            {
                $account_transfer[0]->lin_sm .= $v."\t".$array_account_moneys[$key]."\r\n";
            }
        }

        return view('account.accountInfo')->with('v',$account_transfer[0])
                                    ->with('array_user_id',Func::getUserId())
                                    ->with('array_account_corp',Func::getConfigArr('account_corp_cd'));
    }

    /**
    * 송금 결재 완료 SMS 보내기
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function accountSmsAction(Request $request)
    {
        Log::debug($request->all());
        $accountNo = $request->account_no;

        // 기본값 세팅
        $vmsg = DB::TABLE("SMS_MSG")->SELECT("MESSAGE")->WHERE("CODE_DIV","SYS")->WHERE("SMS_DIV","01")->WHERE("SAVE_STATUS","Y")->ORDERBY("NO", "DESC")->FIRST();
        $vmsg = Func::chungDec(["SMS_MSG"], $vmsg);	// CHUNG DATABASE DECRYPT
        
        $msg = $vmsg->message;
        $sender = '15442525';

        $rslt = DB::table("CUST_INFO")
                ->select('CUST_INFO.NAME, CUST_INFO_EXTRA.PH21, CUST_INFO_EXTRA.PH22, CUST_INFO_EXTRA.PH23, CUST_INFO_EXTRA.CUST_INFO_NO')
                ->join('CUST_INFO_EXTRA', 'CUST_INFO.NO', '=', 'CUST_INFO_EXTRA.CUST_INFO_NO')
                ->where('CUST_INFO.SAVE_STATUS', "Y")
                ->whereIn('CUST_INFO.NO', function($query) use ($accountNo){
                    $query->SELECT('cust_info_no')
                            ->FROM('account_transfer')
                            ->join('loan_info', 'loan_info.no', '=', 'account_transfer.loan_info_no')
                            ->WHERE('account_transfer.SAVE_STATUS', 'Y')
                            ->WHERE('account_transfer.account_transfer_STATUS', 'Y')
                            ->WHERE('account_transfer_NO', $accountNo);
                })
                ->orderBy('CUST_INFO.no')
                ->get();
        $rslt = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA"], $rslt);	// CHUNG DATABASE DECRYPT
        $okCnt = 0;
        $failCnt = 0;
        foreach($rslt as $v)
        {
            $message = str_replace("[고객명]", $v->name, $msg);
            $receiver = trim($v->ph21).trim($v->ph22).trim($v->ph23);
            if($receiver=="")
            {
                $failCnt ++;
                continue;
            }
            Log::debug($v->name.'>'.$v->ph21.'-'.$v->ph22.'-'.$v->ph23.':'.$message);

            $arrayMsg = [];
            $arrayMsg['ups_erp']          = "account";
            $arrayMsg['cust_info_no']     = $v->cust_info_no;
            $arrayMsg['loan_info_no']     = "";
            $arrayMsg['sms_div']          = "SYS";
            $arrayMsg['message']          = $message;
            $arrayMsg['sender']           = $sender;
            $arrayMsg['receiver']         = $receiver;
                
            //문자발송
            //$smsReturn = 'Y';
            $smsReturn = Sms::smsSend($arrayMsg, "PASS");
            if( $smsReturn=='Y' )
            {
                $okCnt ++;
            }
            else
            {
                $failCnt ++;
            }
            
        }

        $array_result['rs']  = "Y";
        if($okCnt>0)
            $array_result['msg'] = "다음과 같이 SMS가 발송되었습니다. (성공 : ".$okCnt.", 실패 : ".$failCnt.")";
        else
            $array_result['msg'] = "SMS 발송이 실패했습니다. 관리자에게 문의해주세요.";

        return $array_result;
    }

    /**
     * 이체실행결재 요청 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function accountLumpAction(Request $request)
    {
        $val = $request->input();
        
        $s_cnt = 0;
        $arr_fail = Array();

        $confirm_id   = Auth::id();
        $confirm_time = date("YmdHis");

        if( $val['action_mode']=="account_transfer_ACTION" && is_array($val['listChk']) && sizeof($val['listChk'])>0 )
        {
            for( $i=0; $i<sizeof($val['listChk']); $i++ )
            {
                $account_transfer_no = $val['listChk'][$i];

                DB::beginTransaction();

                $rslt = DB::dataProcess('UPD', 'account_transfer', ["STATUS"=>"W", "confirm_id"=>$confirm_id, "confirm_time"=>$confirm_time], ["no"=>$account_transfer_no]);
                if( $rslt!="Y" )
                {
                    DB::rollBack();

                    $arr_fail[$account_transfer_no] = "처리에 실패하였습니다..(".$account_transfer_no.")";
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

    /**
     * 이체실행결재 삭제 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function accountLumpDelete(Request $request)
    {
        $val = $request->input();
        
        $s_cnt = 0;
        $arr_fail = Array();

        $del_id   = Auth::id();
        $del_time = date("YmdHis");

        if( $val['action_mode']=="account_transfer_DELETE" && is_array($val['listChk']) && sizeof($val['listChk'])>0 )
        {
            for( $i=0; $i<sizeof($val['listChk']); $i++ )
            {
                $account_transfer_no = $val['listChk'][$i];

                DB::beginTransaction();

                $rslt = DB::dataProcess('UPD', 'account_transfer', ["status"=>"N", "del_id"=>$del_id, "del_time"=>$del_time], ["no"=>$account_transfer_no]);
                if( $rslt!="Y" )
                {
                    DB::rollBack();

                    $arr_fail[$account_transfer_no] = "처리에 실패하였습니다..(".$account_transfer_no.")";
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

    /**
    * 투자자 계좌실명조회
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function loanBankSearch(Request $request)
    {
        $param = $request->all();

        $_DATA = array();
        
        if(isset($param['loan_info_no'])) 
        {
            $loanInfo = DB::table("loan_info")->select("*")
                                                ->where("no", $param["loan_info_no"])
                                                ->where("save_status", "Y")
                                                ->first();
            $loanInfo = Func::chungDec(["loan_info"], $loanInfo);	// CHUNG DATABASE DECRYPT

            if(empty($loanInfo->no))
            {
                $rslt['rs_code']    = 'N';
                $rslt['result_msg'] = "계약이 존재하지 않습니다.";

                return $rslt;
            }

            $_DATA['loan_bank_cd']   = isset($param['loan_bank_cd']) ? $param['loan_bank_cd'] : $loanInfo->loan_bank_cd;
            $_DATA['loan_bank_ssn']  = isset($param['loan_bank_ssn']) ? $param['loan_bank_ssn'] : $loanInfo->loan_bank_ssn;

            $_DATA['handle_code']    = $loanInfo->handle_code;
        }
        else
        {
            $_DATA['loan_bank_cd']   = $param['loan_bank_cd'];
            $_DATA['loan_bank_ssn']  = $param['loan_bank_ssn'];
            
            $_DATA['handle_code']    = $param['handle_code'];
        }

        if(env('APP_ENV') == 'prod')
        {
            $ksnet = new Ksnet(date('Ymd'));
            
            if(!empty($ksnet))
            {
                $ksnet->setOutBank();
                $ksnet->startCheck();
                $rslt = $ksnet->realNameCheck($_DATA);
            }
        }
        else
        {
            $ksnet = new Ksnet(date('Ymd'));
            
            if(!empty($ksnet))
            {
                $ksnet->setOutBank();
                $ksnet->startCheck();
                $rslt = $ksnet->realNameCheck($_DATA);
            }
        }

        if($rslt['rs_code'] == 'Y' && $param['div'] == 'UPD')
        {
            $_UPD = array();
            $_UPD['loan_bank_cd']     = $_DATA['loan_bank_cd'];
            $_UPD['loan_bank_ssn']    = $_DATA['loan_bank_ssn'];
            $_UPD['loan_bank_name']   = $rslt['result_data'];
            $_UPD['loan_bank_status'] = 'Y';

            $_UPD['save_id']          = Auth::id();
            $_UPD['save_time']        = date('YmdHis');

            DB::dataProcess('UPD', 'loan_info', $_UPD, ["no"=>$param['loan_info_no']]);
        }

        return $rslt;
    }
}
