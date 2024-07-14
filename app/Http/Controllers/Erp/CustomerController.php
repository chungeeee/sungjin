<?php
namespace App\Http\Controllers\Erp;

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

class CustomerController extends Controller
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
     * 고객정보창 메인
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function custPop(Request $request)
    {
        $param = $request->all();
        
        // 변수정리
        $no           = $request->no;
        $cust_info_no = $request->cust_info_no;
        $page_div     = $request->page_div;          // 이미지, 녹취 페이지 바로가기 위한 div값

        // 앞,뒤 구현을 위한 쿼리 생성 - 파라미터가 안넘어올수도 있다.
        $qik_btn = Array();
        $userTitle = '';
        if( isset($request->condition) && isset($request->cnt) )
        {
            /*
            $sql = decrypt(urldecode($request->condition));
            $cnt = $request->cnt;

            $sql = "SELECT * FROM ( SELECT ROW_NUMBER() OVER() AS RRR, NO, CUST_INFO_NO FROM ( ".$sql." ) AS tmp_table1 ) AS tmp_table2 WHERE RRR BETWEEN ".min(1,$cnt-1)." AND ".($cnt+1);
            $qik = (Array) DB::select($sql);
            foreach( $qik as $vaq )
            {
                if( $vaq->rrr == ($cnt-1) )
                {
                    $qik_btn["PREV"] = $vaq;
                }
                if( $vaq->rrr == ($cnt+1) )
                {
                    $qik_btn["NEXT"] = $vaq;
                }
            }
            */
            $nos = gzuncompress(decrypt(urldecode($request->condition)));
            $cnt = $request->cnt;
            $qik_btn["CNT"] = $cnt;
            $qik_btn["TOTAL"] = $request->total;
            $userTitle = '('.($qik_btn["CNT"]+1).'/'.$qik_btn["TOTAL"].')';

            $array_nos = explode("|", $nos);
            $pno = Func::nvl($array_nos[$cnt-1],0);
            $nno = Func::nvl($array_nos[$cnt+1],0);

            $sql = "SELECT NO, CUST_INFO_NO FROM LOAN_INFO WHERE SAVE_STATUS='Y' and NO IN (".$pno.", ".$nno.")";
            $qik = (Array) DB::select($sql);
            foreach( $qik as $vaq )
            {
                if( $vaq->no == $pno )
                {
                    $vaq->rrr = $cnt-1;
                    $qik_btn["PREV"] = $vaq;
                }
                if( $vaq->no == $nno )
                {
                    $vaq->rrr = $cnt+1;
                    $qik_btn["NEXT"] = $vaq;
                }
            }


        }

        if( isset($request->opentab) )
        {
            $opentab = $request->opentab;
        }
        else if( isset($page_div) )
        {
            $opentab = $page_div;
        }
        else
        {
            $opentab = "info";
        }
        $law_no = isset($request->law_no) ? $request->law_no : "" ;

        //채권정보변경내역에서 팝업창 오픈시 하단탭 변경내역으로 세팅하기 
        if( isset($request->loanMainTab)) 
        {
            $loanMainTab = $request->loanMainTab;
        }
        else {
            $loanMainTab = '';
        }

        // 데이터
        $ci = DB::table("cust_info")->WHERE("no", $cust_info_no)->first();
        $ci = Func::chungDec(["CUST_INFO"], $ci);	// CHUNG DATABASE DECRYPT

        $li = DB::table("loan_info")->WHERE("cust_info_no", $cust_info_no)->WHERE("save_status", "Y")->ORDERBY("no","desc");
        $li_data = $li->GET();
        $li_data = Func::chungDec(["LOAN_INFO"], $li_data);	// CHUNG DATABASE DECRYPT

        if( $no==0 )
        {
            $no = $ci->last_loan_info_no;
        }

        $loanInfo = DB::table("loan_info")->WHERE("no", $no)->WHERE("save_status", "Y")->ORDERBY("no","desc")->first();

        $userTitle = $ci->name.$userTitle;

        $array_contracts_status = array();
        $arrayStaColor = Vars::$arrayStaColor;

        $li_cnt = 0;
        $li_cnt_yu = 0;
        foreach($li_data as $v)
        {
            $array_contracts[$v->no] = $v;
            $array_contracts_status[$v->no] = ( isset($arrayStaColor[$v->status]) ) ? $arrayStaColor[$v->status] : "";

            if( $v->status=="A" )
            {
                $li_cnt_yu++;
            }
            $li_cnt++;
        }

        Func::setMemberAccessLog('차입자정보 조회', $request->ip(), $request->path(), $request->userAgent(), null, $ci->no, $no, null);

        $array_customer = array(
            "cust_info_no"  => $cust_info_no,
            "investor_no_inv_seq"  => $loanInfo->investor_type.$loanInfo->investor_no.'-'.$loanInfo->inv_seq,
            "name"          => $ci->name,
            "age"           => "20",
            "tot_cont_cnt"  => $li_cnt,
            "yu_cont_cnt"   => $li_cnt_yu,
            "ban_sms"       => "Y",
            "ban_dm"        => "N",
            "prhb_yn"       => $ci->prhb_yn,
        );
        $array_customer["ssn"] = substr($ci->ssn, 0, 6)."-".substr($ci->ssn, 6);
        
        return view('erp.custPop')
                    ->with("cust_info_no", $cust_info_no)
                    ->with("qik_btn", $qik_btn)
                    ->with("condition", ( isset($request->condition) ? $request->condition : '' ))
                    ->with("no", $no)
                    ->with("opentab", $opentab)
                    ->with("loanMainTab", $loanMainTab)
                    ->with("law_no",  $law_no)
                    ->with("array_customer", $array_customer)
                    ->with("array_contracts_status", $array_contracts_status)
                    ->with("userTitle", $userTitle)
                    ->with("page_div", $page_div)
                    ;
    }

    /**
     * 고객정보창 고객정보
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function custInfo(Request $request)
    {
        $configArr      = Func::getConfigArr();
        $cust_info_no   = $request->cust_info_no;
        $loan_info_no   = $request->loan_info_no;

        $ci = DB::TABLE("cust_info")->leftJoin('cust_info_extra', 'cust_info.no', '=', 'cust_info_extra.cust_info_no')
                        ->SELECT("*")
                        ->WHERE('cust_info.no', $cust_info_no)
                        ->WHERE('cust_info.save_status', 'Y')
                        ->first();
        $ci = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA"], $ci);	// CHUNG DATABASE DECRYPT

        // 직업구분 string 표기
        if(isset($ci->job_cd))
        {
            $ci->job_codestr = "";
        }

        $li = DB::TABLE("loan_info")->SELECT("manager_code, manager_id, loan_date, first_prescription_date, first_prescription_date_memo, loan_memo, loan_pay_term, branch_cd, cust_bank_name, cust_bank_ssn,cust_bank_cd, loan_usr_info_no,cust_info_no")
                                    ->WHERE('no', $loan_info_no)
                                    ->FIRST();
        $li = Func::chungDec(["LOAN_INFO"], $li);	// CHUNG DATABASE DECRYPT
        
        $ci->loan_info_no                   = $loan_info_no;
        $ci->loan_usr_info_no               = $li->loan_usr_info_no;
        $ci->cust_info_no                   = $li->cust_info_no;
        
        $ci->cust_bank_name                 = $li->cust_bank_name;
        $ci->cust_bank_ssn                  = $li->cust_bank_ssn;
        $ci->cust_bank_cd                   = $li->cust_bank_cd;
        
        $ci->loan_date                      = $li->loan_date;
        
        $ci->loan_memo                      = $li->loan_memo;
        $ci->first_prescription_date        = $li->first_prescription_date;
        $ci->first_prescription_date_memo   = $li->first_prescription_date_memo;

        $ci->loan_pay_term                  = $li->loan_pay_term;

        $ci->branch_cd                      = $li->branch_cd;

        $ci->ban_sms_divs = json_encode(explode(",",$ci->ban_sms_div));

        $branches     = DB::TABLE("branch")->SELECT("code", "branch_name")->WHERE("parent_code", "T2")->WHERE("save_status", "Y")->get();
        $chargeBranch = array();
        foreach($branches as $branch){
            $chargeBranch[$branch->code] = $branch->branch_name;
        }

        return view('erp.custInfo')->with('ci',$ci)
                                    ->with('configArr',$configArr)
                                    ->with("array_branch", Func::getBranchList())
                                    ->with("chargeBranch", $chargeBranch)
                                    ->with("array_manager", Func::getBranchUserList())
                                    ->with("array_user", Func::getUserId());
    }

    /**
      * 고객정보창 이미지
      *
      * @param  \Illuminate\Http\Request  $request
      * @return view
      */
    public function custImage(Request $request)
    {
        $arr_lon_div = Vars::$arrayLonDoc;
        $arr_cot_div = Vars::$arrayCotDoc;
        $arr_etc_div = Vars::$arrayEtcDoc;
        $arr_img_div = $arr_lon_div + $arr_cot_div + $arr_etc_div;

        $arr_task_name = Vars::$arrayTaskName;
        $arrManager = Func::getUserList();

        $img = DB::TABLE("cust_info_img")->SELECT("*")->WHERE('save_status','Y')->ORDERBY('save_time', 'desc');

        if(isset($request->cust_info_no))
        {
            $cust_info_no = $request->cust_info_no;
            $img = $img->WHERE('cust_info_no',$cust_info_no)->get()->toArray();
            $img = Func::chungDec(["CUST_INFO_IMG"], $img);	// CHUNG DATABASE DECRYPT
        }

        $selected_img = array();
        if(isset($request->no))
        {
            $mode = "UPD";
            foreach( $img as $key=>$value )
            {
                $value->save_time = date("Y-m-d H:i:s", strtotime($value->save_time));
                $value->worker_id = isset($arrManager[$value->worker_id]) ? Func::nvl($arrManager[$value->worker_id]->name, $value->worker_id) : $value->worker_id ;

                log::debug($value->no);
                log::debug($request->no);
                if( $value->no == $request->no )
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
                $value->worker_id = isset($arrManager[$value->worker_id]) ? Func::nvl($arrManager[$value->worker_id]->name, $value->worker_id) : $value->worker_id ;
                $value->save_time = date("Y-m-d H:i:s", strtotime($value->save_time));
            }
        }

        return view('erp.custImage')->with('arr_lon_div',$arr_lon_div)
                                    ->with('arr_cot_div',$arr_cot_div)
                                    ->with('arr_img_div',$arr_img_div)
                                    ->with('arr_etc_div',$arr_etc_div)
                                    ->with('arr_task_name',$arr_task_name)
                                    ->with('img',$img)
                                    ->with('cust_info_no', $cust_info_no)
                                    ->with('loan_info_no', $request->loan_info_no)
                                    ->with('mode', $mode)
                                    ->with('selected_img', $selected_img);
    }

    /**
      * 고객정보창 변경내역
      *
      * @param  \Illuminate\Http\Request  $request
      * @return view
      */
    public function customerChange(Request $request)
    {
        $array_bank_cd       = Func::getConfigArr('bank_cd');
        $array_user_id       = Func::getUserId();
        $cust_info_no        = $request->cust_info_no;

        if(empty($request->selected))
        {
            $selected = 'addr11';
        }
        else
        {
            $selected = $request->selected;
        }

         // 기본쿼리
        $HIST = DB::TABLE("cust_info_log l")
                        ->JOIN("cust_info ci", "l.cust_info_no", "=", "ci.no")
                        ->SELECT("l.*", "ci.name")
                        ->WHERE("ci.no", $cust_info_no);

        if( $selected=="name" )
        {
            $where_raw = "((l.name!= '' and l.name is not null))";
            $pre_select = "name";
        }
        else if( $selected=="ssn" )
        {
            $where_raw = "((l.ssn!= '' and l.ssn is not null))";
            $pre_select = "ssn";
        }
        else if( $selected=="relation" )
        {
            $where_raw = "((l.relation!= '' and l.relation is not null))";
            $pre_select = "relation";
        }
        else if( $selected=="email" )
        {
            $where_raw = "((l.email!= '' and l.email is not null))";
            $pre_select = "email";
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
        else if($selected=="addr11")
        {
            $where_raw = " ((l.zip1!= '' and l.zip1 is not null) and (l.addr11!= '' and l.addr11 is not null) and (l.addr12!= '' and l.addr12 is not null)) ";
            $pre_select = "zip1 as zip11, addr11, addr12";
        }
        else if( $selected=="bank11" )
        {
            $where_raw = "((l.bank_cd!= '' and l.bank_cd is not null) or (l.bank_ssn!= '' and l.bank_ssn is not null)) ";
            $pre_select = "bank_cd as bank11, bank_ssn as bank12";
        }
        else if( $selected=="memo" )
        {
            $where_raw = "((l.memo!= '' and l.memo is not null))";
            $pre_select = "memo";
        }

        $HIST->WHERERAW($where_raw);
        $HIST->ORDERBY('seq', 'desc');

        $rslt   = $HIST->GET();
        $rslt = Func::chungDec(["CUST_INFO_LOG","CUST_INFO","LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        $configArr     = Func::getConfigArr();
        $array_key_chk = Array('relation', 'memo', 'bank11', 'bank21', 'bank31', 'zip11', 'zip21', 'addr11', 'addr21');

        $cnt = 0;
        foreach( $rslt as $v )
        {                                                                                                         
            $v->post_send_cd      = Func::nvl($configArr['addr_cd'][$v->post_send_cd]);
            $v->ph11              = $v->ph11."-".$v->ph12."-".$v->ph13;
            $v->addr11            = $v->zip1." ".$v->addr11." ".$v->addr12;
            $v->bank11             = Func::nvl($array_bank_cd[$v->bank_cd], $v->bank_cd)." / ".$v->bank_ssn;
            $v->save_id           = $v->save_id ? Func::getArrayName($array_user_id, $v->save_id) : '';
            $v->save_time         = Func::dateFormat($v->save_time);
            $v->ssn               = Func::ssnFormat($v->ssn,'Y');

            $HIST_PRE = DB::TABLE("cust_info_log l")
                        ->SELECTRAW($pre_select)
                        ->WHERERAW($where_raw)
                        ->WHERERAW(" cust_info_no = '".$v->cust_info_no."' and seq < ".$v->seq)
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
                        $v->pre_ssn = Func::ssnFormat($v->pre_ssn,'Y');
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
            'addr11'        =>"주소",
            'ph11'          =>"전화번호",
            'bank11'       =>"은행/계좌번호",     
            'name'          =>"이름",       
            'ssn'           =>"주민등록번호",
            'memo'          =>"메모",
        );

        return view('erp.customerChange')
                                    ->with('array_select',$array_select)
                                    ->with('array_user',Func::getUserId())
                                    ->with('array_post_send_cd',Func::getConfigArr('addr_cd'))
                                    ->with('selected', $selected)
                                    ->with('r', $r);
    }

    public function custInfoAction(Request $request)
    {
        $cust_info_no       = $request->cust_info_no;
        $loan_info_no       = $request->loan_info_no;

        $ARR                = $request->all();

        $ARR['SAVE_ID']     = Auth::id();
        $ARR['SAVE_TIME']   = date("YmdHis");        
        $ARR['LOCAL']       = mb_substr($request->addr11, 0, 2, 'utf-8');  

        if(!isset($ARR['ban_call']))    $ARR['ban_call'] = "N";
        if(!isset($ARR['ban_sms'] ))    $ARR['ban_sms']  = "N";
        if(!isset($ARR['ban_post']))    $ARR['ban_post'] = "N";
        
        if(!isset($ARR['addr1_nlive_yn'])) $ARR['addr1_nlive_yn'] = "N";
        if(!isset($ARR['addr2_nlive_yn'])) $ARR['addr2_nlive_yn'] = "N";
        if(!isset($ARR['addr4_nlive_yn'])) $ARR['addr4_nlive_yn'] = "N";

        if(!isset($ARR['branch_cd']))      $ARR['branch_cd'] = "";

        // 채무대리인
        if(!isset($ARR['prhb_yn'])) $ARR['prhb_yn'] = "N";

        $ARR['ban_sms_div'] = ( isset($ARR['ban_sms_divs']) && sizeof($ARR['ban_sms_divs'])>0 ) ? implode(",",$ARR['ban_sms_divs']) : "" ;

        

        DB::beginTransaction();
        // 고객속성 업데이트 - 어차피 없는 컬럼은 무시, 있는 컬럼은 업데이트 하니까 그냥 하자.
        $rslt = DB::dataProcess('UPD', 'cust_info', $ARR, ["NO"=>$cust_info_no]);
        if( $rslt!="Y" )
        {
            DB::rollBack();
            return ['result_msg'=>"처리에 실패하였습니다.#1", 'rs_code'=>'N'];
        }
        // 고객정보 업데이트
        $rslt = DB::dataProcess('UPD', 'cust_info_extra', $ARR, ["CUST_INFO_NO"=>$cust_info_no]);
        if( $rslt!="Y" )
        {
            DB::rollBack();
            return ['result_msg'=>"처리에 실패하였습니다.#2", 'rs_code'=>'N'];
        }

        // 계약정보 업데이트 
        $LOAN_ARR['cust_bank_cd']       = $ARR['cust_bank_cd'] ?? "";
        $LOAN_ARR['cust_bank_name']     = $ARR['cust_bank_name'] ?? "";
        $LOAN_ARR['cust_bank_ssn']      = $ARR['cust_bank_ssn'] ?? "";
        $LOAN_ARR['loan_memo']	        = $ARR['loan_memo'] ?? "";
        $LOAN_ARR['branch_cd']	        = $ARR['branch_cd'] ?? "";
        $LOAN_ARR['save_id']	        = Auth::id();
        $LOAN_ARR['save_time']	        = date("Ymd");

        $rslt = DB::dataProcess('UPD', 'loan_info', $LOAN_ARR, ["no"=>$loan_info_no]);
        if( $rslt!="Y" )
        {
            DB::rollBack();
            return ['result_msg'=>"처리에 실패하였습니다.#3", 'rs_code'=>'N'];
        }


        // 계약정보 수정 후 한번 더 업데이트 해준다. - Y인 경우만
        $ban_info = Func::getBanInfo($cust_info_no, "Y", "N");
        $ARR2 = [];
        if( $ban_info['ban_post']=="Y" )
        {
            $ARR2['ban_post'] = "Y";
        }
        if( $ban_info['ban_sms']=="Y" )
        {
            $ARR2['ban_sms'] = "Y";
        }
        if( $ban_info['ban_call']=="Y" )
        {
            $ARR2['ban_call'] = "Y";
        }
        if( sizeof($ARR2)>0 )
        {
            $rslt = DB::dataProcess('UPD', 'cust_info', $ARR2, ["no"=>$cust_info_no]);
        }


        DB::commit();

        $array_result['result_msg'] = "정상처리 되었습니다.";
        $array_result['rs_code'] = 'Y';
        return $array_result;
    }

    /**
      * 고객정보창 이미지 액션
      *
      * @param  \Illuminate\Http\Request  $request
      * @return String
      */
    public function custImgAction(Request $request)
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

        $_DATA['worker_id'] = Auth::id();
        $_DATA['save_time'] = date("YmdHis");

        // 폴더 생성
        $folder = date("Ymd");
        $fileName = date("YmdHis")."_".sprintf("%07d", $request->cust_info_no);

        // 계약번호 조회
        if(!empty($request->loan_info_no))
        {
            $loan = DB::TABLE("loan_info")->SELECT('no')->WHERE('cust_info_no', $param['cust_info_no'])->FIRST();
            $loan_info_no = $loan->no;
        }
        else 
        {
            $loan_info_no = $request->loan_info_no;
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
                $filePath = $request->file('customFile')->storeAs($folder, $fileName,'erp_data_img');
                
                $_DATA['origin_filename'] = $request->file('customFile')->getClientOriginalName();
                $_DATA['filename']        = $fileName;
                $_DATA['folder_name']     = $folder;
                $_DATA['file_path']       = $filePath;
                $_DATA['extension']       = $request->file('customFile')->guessExtension();
                $_DATA['taskname']        = $param['taskname'];
                $_DATA['worker_id']       = Auth::id();
                $_DATA['save_time']       = date("YmdHis");
                $_DATA['save_status']     = "Y";
                $_DATA['cust_info_no']    = $param['cust_info_no'];
                $_DATA['loan_info_no']    = $loan_info_no;

                $result = DB::dataProcess( "INS", "cust_info_img", $_DATA );
                
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
            $_DATA['taskname'] = $param['taskname'];
            $_DATA['loan_info_no'] = $loan_info_no;

            //  파일 수정 시, 파일 삭제는 하지 않는다.
            if( $request->file('customFile') )
            {
                DB::beginTransaction();

                $rslt = DB::TABLE('cust_info_img')->SELECT('filename', 'taskname', 'img_div_cd', 'file_path','worker_id')->WHERE('save_status', 'Y')->WHERE('no', $_DATA['no'])->FIRST();
                $rslt = Func::chungDec(["CUST_INFO_IMG"], $rslt);	// CHUNG DATABASE DECRYPT

                // 기존 파일명
                if(isset($rslt->filename))
                {
                    // 기존파일 삭제
                    // $exists = Storage::disk('erp_data_img')->exists($rslt->file_path);
                    // if( $exists )
                    // {
                    //     Storage::disk('erp_data_img')->delete($rslt->file_path);
                    // }

                
                    $filePath = $request->file('customFile')->storeAs($folder, $fileName,'erp_data_img');

                    $_DATA['save_status'] = "Y";
                    $_DATA['origin_filename'] = $request->file('customFile')->getClientOriginalName();
                    $_DATA['filename']        = $fileName;
                    $_DATA['folder_name']     = $folder;
                    $_DATA['file_path']       = $filePath;
                    $_DATA['extension']       = $request->file('customFile')->guessExtension();

                    $result = DB::dataProcess("UPD", "cust_info_img", $_DATA);
    
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
                $result = DB::dataProcess("UPD", "cust_info_img", $_DATA);

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
            $_IMG = DB::TABLE('cust_info_img')->SELECT('filename', 'file_path')->WHERE('no', $_DATA['no'])->first();
            $_IMG = Func::chungDec(["CUST_INFO_IMG"], $_IMG);	// CHUNG DATABASE DECRYPT

            if(isset($_IMG->filename))
            {
                // 기존파일 삭제
                $exists = Storage::disk('erp_data_img')->exists($_IMG->file_path);
                if( $exists )
                {
                    Storage::disk('erp_data_img')->delete($_IMG->file_path);
                }

                $result = DB::dataProcess("UPD", "cust_info_img", $_DEL_DATA);

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
     * 고객관리 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setCustomerList(Request $request)
    {
        $list   = new DataList(Array("listName"=>"customer","listAction"=>'/'.$request->path()));

        $list->setLumpForm('DEL', Array('BTN_NAME'=>'삭제','BTN_ACTION'=>'lump_del(this)','BTN_ICON'=>'','BTN_COLOR'=>''));

        if(!isset($request->tabs)) $request->tabs = 'Y';
        $list->setTabs(Array("Y"=>"유효","N"=>"삭제"),$request->tabs);
        
        // if( Func::funcCheckPermit("E001") )
        // {
        //     $list->setButtonArray("엑셀다운", "excelDownModal('/erp/customerexcel', 'form_customer')", "btn-success");
        // }
        $list->setButtonArray("엑셀다운", "excelDownModal('/erp/customerexcel', 'form_customer')", "btn-success");
        
        $list->setCheckBox("no");

        $list->setPlusButton('popUpFull(\'/erp/custinputpop\',\'\' )');

        $list->setSearchDetail(Array( 
            'NAME'  => '이름',
            'e.cust_info_no'    => '차입자번호',
            'SSN'   => '주민번호',
        ));

        return $list;
    }
    
    /**
     * 고객관리 메인화면
     *
     * @param  Void
     * @return view
     */
	public function customer(Request $request)
    {
        $list   = $this->setCustomerList($request);

        $list->setLumpForm('DEL', Array('BTN_NAME'=>'삭제','BTN_ACTION'=>'lump_del(this)','BTN_ICON'=>'','BTN_COLOR'=>''));

        $list->setlistTitleCommon(Array
        (
            'cust_info_no'              => Array('차입자번호', 0, '', 'center', '', 'no'),
            'name'                      => Array('이름', 0, '', 'center', '', 'name'),
            'ssn'                       => Array('주민등록번호(법인번호)', 0, '', 'center', '', 'ssn'),
            'com_ssn'                   => Array('사업자번호', 0, '', 'center', '', 'com_ssn'),
            'email'                     => Array('이메일', 0, '', 'center', '', 'email'),
            'ph11'                      => Array('전화번호', 0, '', 'center', '', 'ph11'),
        ));

        return view('erp.customer')->with('result', $list->getList());
    }

    /**
     * 고객관리 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function customerList(Request $request)
    {
        $list   = $this->setCustomerList($request);
        $request->isDebug = true;
        $param  = $request->all();

        // 탭 검색
        $param['tabSelectNm'] = "c.save_status";
        $param['tabsSelect']  = $request->tabsSelect;

        // 기본쿼리
        $CUST = DB::TABLE("cust_info c");
        $CUST->JOIN('cust_info_extra e', 'c.no', '=', 'e.cust_info_no');
        $CUST->LEFTJOIN(DB::RAW("(select cust_info_no, sum(loan_money) as total_loan_money, sum(balance) as total_balance from loan_info where save_status = 'Y' group by cust_info_no) as l"), 'c.no', '=', 'l.cust_info_no');
        $CUST->SELECT("c.name,c.ssn,c.no,e.ph11,e.ph12,e.ph13,e.ph21,e.ph22,e.ph23,e.com_ssn,e.email,l.total_loan_money,l.total_balance");

        if( substr($param['searchDetail'],0,2) == "PH" )
        {
            $param['searchString'] = str_replace("-", "", $param['searchString']);
        }
        
        // 고객번호검색 예외처리
        if($param['searchDetail'] == "no")
        {
            $param['searchDetail'] = "c.no";
        }

        if(isset( $param['searchDetail']) && !empty($param['searchString']) )
        {
            $searchString = $param['searchString'];
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='e.cust_info_no' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $CUST = $list->getListQuery("c",'main',$CUST, $param);
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($CUST, $request->page, $request->listLimit, 10, $request->listName);
        $rslt = $CUST->GET();
        $rslt = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $array_user_id      = Func::getUserId();
        $configArr          = Func::getConfigArr();
        $arrBranch          = Func::getBranch();

        // Log::debug(print_r($configArr,true));
        $cnt = 0;
        foreach ($rslt as $v)
        {
            $v->onclick                 = 'popUpFull(\'/erp/customerpop?cust_info_no='.$v->no.'\', \'cust_info'.$v->no.'\')';
            $v->line_style              = 'cursor: pointer;';
            $v->cust_info_no            = $v->no;
            $v->name                    = Func::nameMasking($v->name, 'N');
            $v->ssn                     = Func::ssnFormat($v->ssn, 'A');
            $v->ph11                    = Func::phFormat($v->ph11,$v->ph12,$v->ph13);
            $v->ph21                    = Func::phFormat($v->ph21,$v->ph22,$v->ph23);
            $v->total_loan_money        = number_format($v->total_loan_money);            
            $v->total_balance           = number_format($v->total_balance);
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
     * 고객관리-고객정보창 메인
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function customerPop(Request $request)
    {
        $status_color = "#6c757d";
        $no = $request->cust_info_no;
        $ci = DB::TABLE("cust_info")->SELECT("no,name,ssn")->WHERE('save_status','Y')->WHERE("no", $no)->first();
        $ci = Func::chungDec(["CUST_INFO"], $ci);	// CHUNG DATABASE DECRYPT

        return view('erp.customerPop')->with("ci", $ci)->with("status_color", $status_color);
    }

    /**
     * 고객관리-고객정보창 고객정보
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function customerInfo(Request $request)
    {
        $array_user         = Func::getUserId();
        $configArr          = Func::getConfigArr();

        $no = $request->cust_info_no;
        if($no)
        {
            $ci = DB::TABLE("cust_info")->LEFTJOIN('cust_info_extra', 'cust_info.no', '=', 'cust_info_extra.cust_info_no')
                            ->SELECT("cust_info.*,cust_info_extra.*")
                            ->WHERE('cust_info.no', $no)
                            ->WHERE('cust_info.save_status', 'Y')
                            ->FIRST();
            $ci = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA"], $ci);	// CHUNG DATABASE DECRYPT

            $ci->ssn1         = substr($ci->ssn,0,6);
            $ci->ssn2         = substr($ci->ssn,6);
            $ci->job_codestr  = "";
            $ci->reg_date     = Func::dateFormat($ci->reg_date);
            $button_name      = "저장";
            $mode             = "UPD";
        }
        else
        {
            $ci[0] = "";
            $button_name      = "등록";
            $mode             = "INS";
        }


        return view('erp.customerInfo')->with('ci',$ci)
                                    ->with('array_user',$array_user)
                                    ->with('button_name',$button_name)
                                    ->with('mode',$mode)
                                    ->with('configArr',$configArr);
    }

    /**
     * 고객관리-고객정보창 고객정보
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function customerInfoAction(Request $request)
    {
        $ARR                = $request->all();
        $ARR['SAVE_ID']     = Auth::id();
        $ARR['SAVE_STATUS'] = "Y";
        $ARR['SAVE_TIME']   = date("YmdHis");
        $ARR['SSN']         = $request->ssn1.$request->ssn2;
        $ARR['local']       = substr($request->addr11, 0, 6);  

        $code=null;
        $codeResult = DB::table("conf_code")->select("*")->where('name', $request->bank_cd)->first();
        if ($codeResult) {
            $code = $codeResult->code;
            $ARR['bank_cd'] = $code;   
        }

        foreach($ARR as $col => $value)
        {
            if(str_ends_with($col,'_date'))
            {
                $ARR[$col] = str_replace("-","",$value);
            }
        }

        if($request->mode == "UPD")
        {
            $cust_info_no = $request->cust_info_no;

            $rslt = DB::dataProcess('UPD', 'cust_info', $ARR, ["no"=>$cust_info_no]);
            if($rslt=="Y")
            {
                $rslt = DB::dataProcess('UPD', 'cust_info_extra', $ARR, ["CUST_INFO_NO"=>$cust_info_no]);
            }
        }
        else if($request->mode == "INS")
        {
            $cnt = DB::TABLE('cust_info')->WHERE('ssn', Func::encrypt($ARR['SSN'], 'ENC_KEY_SOL'))->COUNT();
            if($cnt>0)
            {
                $array_result['mode'] = $request->mode;
                $array_result['result_msg'] = "기등록된 주민번호입니다.";
                $array_result['rs_code'] = "N";
                return $array_result;
            }
            
            $cust_info_no = "";
            $rslt = DB::dataProcess('INS', 'cust_info', $ARR, null,$cust_info_no);
            if($rslt=="Y")
            {
                $ARR['CUST_INFO_NO'] = $cust_info_no;
                $rslt = DB::dataProcess('INS', 'cust_info_extra', $ARR);
            } 
        }
        
        $array_result['mode'] = $request->mode;
        if(isset($rslt) && $rslt == "Y")
        {
            $array_result['result_msg'] = "정상처리 되었습니다.";
            $array_result['rs_code'] = "Y";
        }
        else
        {
            $array_result['result_msg'] = "처리에 실패하였습니다.";
            $array_result['rs_code'] = "N";
        }

        return $array_result;
    }

    
    /**
     * 고객관리 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function customerExcel(Request $request)
    {
        if( !Func::funcCheckPermit("E001") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }
        
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        $list           = $this->setCustomerList($request);
        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;
        
        // 탭 검색
        $param['tabSelectNm'] = "c.save_status";
        $param['tabsSelect']  = $request->tabsSelect;

        // 기본쿼리
        $CUST = DB::TABLE("cust_info c");
        $CUST->JOIN('cust_info_extra e', 'c.no', '=', 'e.cust_info_no');
        $CUST->LEFTJOIN(DB::RAW("(select cust_info_no, sum(loan_money) as total_loan_money, sum(balance) as total_balance from loan_info where save_status = 'Y' group by cust_info_no) as l"), 'c.no', '=', 'l.cust_info_no');
        $CUST->SELECT("c.name,c.ssn,c.no,e.ph11,e.ph12,e.ph13,e.ph21,e.ph22,e.ph23,e.com_ssn,e.email, l.total_loan_money,l.total_balance");

        // if(isset( $param['searchDetail']) && !empty($param['searchString']) )
        // {
        //     $searchString = $param['searchString'];
        //     unset($param['searchString']);
        // }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='e.cust_info_no' && !empty($param['searchString']) )
        {
            $pattern = '/\d+/';
            // 패턴과 일치하면 테이터 제거
            if(!preg_match($pattern, $param['searchString'])) {
                unset($param['searchString']);
            }
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }
        
        $CUST = $list->getListQuery("c",'main',$CUST,$param);

        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($CUST, $request->nowPage, $request->listLimit, 10, $request->listName);
        }


        // 엑셀다운 로그 시작
        $record_count = 0;
        $query        = Func::printQuery($CUST);
        log::info($query);
        $file_name    = "차입자_명세_".date("YmdHis").'_'.Auth::id().'.xlsx';
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

        $rslt = $CUST->GET();
        $rslt = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $rslt);	// CHUNG DATABASE DECRYPT

        // 엑셀
		$excel_header = array('NO','차입자번호','이름','주민등록번호(법인번호)','사업자번호','이메일','전화번호');

        $excel_data = [];

        $array_user_id      = Func::getUserId();
        $configArr          = Func::getConfigArr();
        $arrBranch          = Func::getBranch();
        $board_count=1;

        foreach ($rslt as $v)
        {
            $array_data = [
                $board_count,
                $v->cust_info_no = $v->no,
                Func::nameMasking($v->name, 'N'),
                Func::ssnFormat($v->ssn, 'A'),
                $v->com_ssn,
                $v->email,
                Func::phFormat($v->ph11,$v->ph12,$v->ph13)
            ];
            $record_count++;
            $board_count++;
            $excel_data[] = $array_data;
        }
        
        // 엑셀 익스포트
        ExcelFunc::fastexcelExport($excel_data, $excel_header, $origin_filename);
    
        // 파일 저장 여부 확인
        $exists = Storage::disk('excel')->exists($origin_filename);

        if( $exists )
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
     * CUST_INFO_IMG Image 파일 경로 가져오기
     *
     * @param  \Illuminate\Http\Request  $request
     * @return 
     */
    public function getCustImg(Request $request)
    {
        //  no값이나 고객번호가 없으면 ERROR
        if( !(isset($request->no) && isset($request->cust_info_no)) )
        {
            return "E";
        }

        $_IMG = DB::TABLE("cust_info_img")->SELECT("*")->WHERE("save_status", "Y")->WHERE("no", $request->no)->WHERE("cust_info_no", $request->cust_info_no)->first();
        // $_IMG = Func::chungDec(["CUST_INFO_IMG"], $_IMG);	// CHUNG DATABASE DECRYPT
        
        $exists = Storage::disk('erp_data_img')->exists($_IMG->file_path);
        //  파일 존재 유무 확인
        if( !$exists )
        {
            return "E";
        }

        Log::debug($_IMG->file_path);
        return Storage::disk('erp_data_img')->get($_IMG->file_path);
    }

    /**
     * CUST_INFO_IMG Image 파일 다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return 
     */
    public function downCustImg($dataNo)
    {
        Log::debug("다운로드 시작 : [".$dataNo."]");

        //  NO 값이 없으면 ERROR
        if( !(isset($dataNo) ) )
        {
            Log::debug("No 값 없음 : [".$dataNo."]");
            return;
        }

        $_IMG = DB::TABLE("cust_info_img")->SELECT("origin_filename, filename, file_path")->WHERE("save_status", "Y")->WHERE("no", $dataNo)->first();
        $_IMG = Func::chungDec(["CUST_INFO_IMG"], $_IMG);	// CHUNG DATABASE DECRYPT

        if(isset($_IMG->filename))
        {
            $exists = Storage::disk('erp_data_img')->exists($_IMG->file_path);
            if( !$exists )
            {
                Log::debug("파일 없음 : [".print_r($_IMG, true)."]");
                return;
            }

            $response = Response::make(Storage::disk('erp_data_img')->get($_IMG->file_path), 200);
            $response->header('Content-Type', Storage::disk('erp_data_img')->mimeType($_IMG->file_path));
            return $response;
        }
        else
        {
            Log::debug("파일 데이터 없음 : [".print_r($_IMG, true)."]");
            return;
        }        
    }

    /**
     * 고객검색
     */
    public function custSearch(Request $request)
    {
        return view('erp.custSearch')->with('div',$request->div)
                                     ->with('searchString',$request->search_string);
    }

    /**
     * 고객정보창 이미지 TASK 구분값
     */
    public function custTaskDiv(Request $request)
    {
        if($request->task_div == 'COURT')
        {
            $div = Vars::$arrayCotDoc;
        }
        elseif($request->task_div == 'LOAN')
        {
            $div = Vars::$arrayLonDoc;
            
        }
        elseif($request->task_div == 'ETC')
        {
            $div =Vars::$arrayEtcDoc;
        }
        else
        {
            $div = Array();
        }

        $str = "<option value=''>파일서식선택</option>";

        foreach($div as $k => $v)
        {
            $str.= "<option value='".$k."' >".$v."</option>";
        }
        
        return $str;
    }

    /**
     * 고객정보창 이미지 TASK 구분값
     */
    public function custImagePriview(Request $request)
    {
        return view('erp/custImgPreview')
                ->with('no', $request->no)
                ->with('cust_info_no', $request->cust_info_no)
                ->with('ext', $request->ext);
    }
    
    /**
     * 고객정보 메뉴 새창
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function custPopNew(Request $request)
    {        
        // 변수정리
        $no             = $request->no;
        $cust_info_no   = $request->cust_info_no;
        $zone           = $request->zone;
        $opentab        = $request->opentab;
        $menuTitle      = $request->menutitle;
        

        // 데이터
        $loan = DB::table("loan_info")->join("cust_info", "loan_info.cust_info_no", "=", "cust_info.no")
                    ->select('cust_info.name', 'cust_info.ssn', 'loan_info.cust_info_no', 'loan_info.status')
                    ->where('cust_info.save_status','Y')
                    ->where('loan_info.save_status','Y')
                    ->where("cust_info.no", $cust_info_no)
                    ->where("loan_info.no", $no)
                    ->first();
        $loan = Func::chungDec(['cust_info'], $loan);	// CHUNG DATABASE DECRYPT
        $loan->ssn = substr($loan->ssn, 0, 6)."-".substr($loan->ssn, 6);

        $arrayStaColor = Vars::$arrayStaColor;
        $statusColor = ( isset($arrayStaColor[$loan->status]) ) ? $arrayStaColor[$loan->status] : "";


        return view('erp.custPopNew')
                    ->with("cust_info_no", $cust_info_no)
                    ->with("no", $no)
                    ->with("zone", $zone)
                    ->with("opentab", $opentab)
                    ->with("menuTitle", $menuTitle)
                    ->with("statusColor", $statusColor)
                    ->with("arrayCustomer", (array)$loan)
                    ;
    }  

    /**
     * 계약등록 입력폼에서 찾기를 할 때, 결과 테이블 HTML 응답한다.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function corporateSearch(Request $request)
    {
        if( !isset($request->usr_search_string) )
        {
            return "검색어를 입력하세요.";
        }
        
        $usr_search_string = $request->usr_search_string;

        // 기본쿼리
        $LOAN = DB::TABLE("vir_acct_mo")->SELECT("*")->WHERE('save_status','Y');

        // 검색
        $where = "";
        if( is_numeric($usr_search_string) )
        {
            $where.= "no=".$usr_search_string." ";
            
            // 6자리 이상인 경우만 검색
            if( strlen($usr_search_string)>=6 )
            {
                $where.= 'or '.Func::encLikeSearchString('mo_bank_ssn', $usr_search_string, 'after');
            }
        }
        else
        {
            $code = null;
            $codeResult = DB::table("conf_code")->select("*")->where('name', $usr_search_string)->first();

            if ($codeResult) {
                $code = $codeResult->code;
                $where .= "mo_acct_div='".$code."'";
            }
            else {
                return "일치하는 결과가 없습니다";
            }
        }

        if($where!='')
        {
            $where = '('.$where.')';
        }

        $LOAN->whereRaw($where);
        $LOAN->orderBy("no","ASC");

        $rslt = $LOAN->get();
        $rslt = Func::chungDec(["vir_acct_mo"], $rslt);	// CHUNG DATABASE DECRYPT
        
        $string = "<table class='table table-sm table-hover card-secondary card-outline mt-1'>";
        $string.= "<tr class='text-center'>";
        $string.= "<td>No</td>";
        $string.= "<td>법인</td>";
        $string.= "<td>은행</td>";
        $string.= "<td>계좌번호</td>";

        $string.= "<td hidden>은행코드</td>";
        $string.= "<td hidden>법인코드</td>";
        $string.= "</tr>";

        $configArr = Func::getConfigArr();
        foreach( $rslt as $v )
        {
            $string.= "<tr role='button' onclick='selectUsrInfo(".$v->no.");'>";
            $string.= "<td id='vir_acct_mo_no_".$v->no."' class='text-center'>".$v->no."</td>";
            $string.= "<td id='vir_acct_mo_div_".$v->no."'    class='text-center'>".$configArr['mo_acct_div'][$v->mo_acct_div]."</td>";
            $string.= "<td id='vir_acct_mo_bank_cd_".$v->no."'     class='text-center'>".$configArr['bank_cd'][$v->mo_bank_cd]."</td>";
            $string.= "<td id='vir_acct_mo_bank_ssn_".$v->no."' class='text-center'>".$v->mo_bank_ssn."</td>";
            $string.= "<td id='vir_acct_mo_acct_div_".$v->no."' class='text-center'>".$v->mo_acct_div."</td>";
            $string.= "</tr>";
        }
        $string.= "</table>";

        return $string;
    }

    /**
     * 차입자명세 삭제 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function customerDelete(Request $request)
    {
        $val = $request->input();
        $s_cnt = 0;
        $arr_fail = Array();

        $del_id   = Auth::id();
        $del_time = date("YmdHis");

        if( $val['action_mode']=="customer_DELETE" && is_array($val['listChk']) && sizeof($val['listChk'])>0 )
        {
            for( $i=0; $i<sizeof($val['listChk']); $i++ )
            {
                $cust_info_no = $val['listChk'][$i];

                DB::beginTransaction();

                $loan_info = DB::table("loan_info")
                                    ->join("cust_info","cust_info.no","=","loan_info.cust_info_no")
                                    ->where('loan_info.cust_info_no', $cust_info_no)
                                    ->where('loan_info.save_status', 'Y')
                                    ->where('cust_info.save_status', 'Y')
                                    ->first();
                $loan_info = Func::chungDec(["loan_info"], $loan_info);	// CHUNG DATABASE DECRYPT

                if(!empty($loan_info->no))
                {
                    DB::rollback();

                    $arr_fail[$cust_info_no] = "연결되어있는 계약이 있습니다.";
                    continue;
                }

                $rslt = DB::dataProcess('UPD', 'cust_info', ["SAVE_STATUS"=>"N", "DEL_ID"=>$del_id, "DEL_TIME"=>$del_time], ["no"=>$cust_info_no]);
                if( $rslt!="Y" )
                {
                    DB::rollBack();
 
                    $arr_fail[$cust_info_no] = "삭제처리에 실패하였습니다.";
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

            foreach($arr_fail as $c_no => $msg)
            {
                $error_msg .= "[".$c_no."] => ".$msg."\n";
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


