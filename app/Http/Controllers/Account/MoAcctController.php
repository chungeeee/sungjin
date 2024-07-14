<?php

namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use App\Chung\Func as ChungFunc;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;
use DataList;
use Validator;
use Vars;
use ExcelFunc;
use Illuminate\Support\Facades\Storage;
use App\Chung\Paging;

class MoAcctController extends Controller
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

        if(!isset($request->tabs)) $request->tabs = 'S';
        
        $list   = new DataList(Array("listName"=>"moAcct", "listAction"=>'/'.$request->path()));

        if( Func::funcCheckPermit("A022") )
        {
            $list->setButtonArray("엑셀다운", "excelDownModal('/account/moacctexcel', 'form_moAcct')", "btn-success");
        }
        $list->setButtonArray("엑셀다운", "excelDownModal('/account/moacctexcel', 'form_moAcct')", "btn-success");

        $list->setSearchType('mo_acct_div', Func::getConfigArr('mo_acct_div'), '법인구분');
        
        $list->setSearchType('pro_cd',Func::getConfigArr('pro_cd'),'상품구분', '', '', '', '', 'Y', '', true);
        
        $list->setSearchType('mo_acct_cd', Func::getConfigArr('mo_acct_cd'), '대분류');
        $list->setSearchType('mo_acct_sub_cd', Func::getConfigArr('mo_acct_sub_cd'), '중분류');

        $list->setSearchDate('기준일선택', Array('info_date'=>'기준일'), 'searchDt', 'N', 'Y', date('Y-m-d'), date('Y-m-d'), 'info_date');

        $list->setTabs(Vars::$arrayUseBank, $request->tabs);

        $list->setSearchDetail(Array(
            'mo_bank_ssn'     => '계좌번호',
            'mo_bank_name'    => '계좌명',
        ));
        
        return $list;
    }


    /**
     * 법인통장관리 메인화면
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function moAcct(Request $request)
    {
        $list   = $this->setDataList($request);
        
        // plusButtonAction : 등록 버튼 onclick 동작
        $list->setPlusButton("moAcctForm('');");
        
        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $list->setlistTitleCommon(Array
        (
            'mo_acct_div'        =>     Array('법인', 0, '', 'center', '', 'mo_acct_div'),
            'mo_bank_cd'         =>     Array('은행', 0, '', 'center', '', 'mo_bank_cd'),
            'status'             =>     Array('사용/미사용', 1, '', 'center', '', 'status'),
            'mo_bank_ssn'        =>     Array('계좌번호', 0, '', 'center', '', 'mo_bank_ssn'),
            'mo_bank_name'       =>     Array('계좌명', 0, '', 'center', '', 'mo_bank_name'),
            'old_money'          =>     Array('전일 잔액', 0, '', 'center', '', ''),
            'in_money'           =>     Array('입금액', 0, '', 'center', '', ''),
            'out_money'          =>     Array('출금액', 0, '', 'center', '', ''),
            'now_money'          =>     Array('기준일 잔액', 0, '', 'center', '', ''),
            'mo_acct_cd'         =>     Array('대분류', 0, '', 'center', '', 'mo_acct_cd'),
            'mo_acct_sub_cd'     =>     Array('중분류', 0, '', 'center', '', 'mo_acct_sub_cd'),
            'memo'               =>     Array('메모', 0, '', 'center', '', 'memo')
        ));

        return view('account.moAcct')->with('result', $list->getList());
    }

    /**
     * 법인통장관리 리스트
     *
     * @param  \Illuminate\Http\Request  $request
     * @return JSON
     */
    public function moAcctList(Request $request)
    {
        $list   = $this->setDataList($request);

        $param  = $request->all();

        if( $param['searchDt'] == "")
        {
            $param['searchDt'] = "info_date";
        }

        if( $param['searchDtString'] == "" )
        {
            $param['searchDtString'] = date("Y-m-d");
        }

        if( $param['tabsSelect'] == "" )
        {
            $tabsSelect = 'S';
        }

        $tabsSelect = $param['tabsSelect'];

        // 날짜형식변환
        $search_date = str_replace("-", "", $param['searchDtString']);

        $moneyArray = Array();
        $info_date = $search_date;
        $tableName = 'vir_acct_mo';

        // 메인쿼리
        $motherAccount = DB::TABLE($tableName)->SELECT("*")->WHERE('save_status','Y');

        if(!isset($param['listOrder']))
        {
            $param['listOrder']    = "no";
            $param['listOrderAsc'] = "asc";
        }

        // 신한은행
        if($tabsSelect == 'S')
        {
            $motherAccount = $motherAccount->where("mo_bank_cd", "088");
        }
        // 우리은행
        elseif($tabsSelect == 'W')
        {
            $motherAccount = $motherAccount->where("mo_bank_cd", "020");
        }
        // 기업은행
        else
        {
            $motherAccount = $motherAccount->where("mo_bank_cd", "003");
        }

        unset($oldTradeData, $tradeData, $param['searchDt'], $param['searchDtString'], $param['tabsSelect']);

        // 신한은행(하이픈)
        if($tabsSelect == 'S')
        {
            // 기준일
            $tradeData = DB::connection('band')->table('TRADE_DATA_TBL')->select("BANK_CODE_3", "CORP_ACC_NO", "BALANCE", "DEAL_SELE", "TOTAL_AMT")
                                                                        ->where("DEAL_DATE", $info_date)
                                                                        ->orderBy("DEAL_TIME")
                                                                        ->get();
            foreach( $tradeData as $val )
            {
                if(!empty($val))
                {
                    $moneyArray[$val->CORP_ACC_NO]['now_money']    = ($val->BALANCE ?? 0)*1;
    
                    if($val->DEAL_SELE == '20' || $val->DEAL_SELE == '40')
                    {                   
                        $moneyArray[$val->CORP_ACC_NO]['in_money'] = isset($moneyArray[$val->CORP_ACC_NO]['in_money']) ? $moneyArray[$val->CORP_ACC_NO]['in_money'] + ($val->TOTAL_AMT)*1 : ($val->TOTAL_AMT)*1;
                    }
                    else if($val->DEAL_SELE == '30')
                    {
                        $moneyArray[$val->CORP_ACC_NO]['out_money'] = isset($moneyArray[$val->CORP_ACC_NO]['out_money']) ? $moneyArray[$val->CORP_ACC_NO]['out_money'] + ($val->TOTAL_AMT)*1 : ($val->TOTAL_AMT)*1;
                    }
                }
            }
    
            $motherAccount = $list->getListQuery($tableName, 'main', $motherAccount, $param);
    
            // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
            $paging = new Paging($motherAccount, $request->page, $request->listLimit, 10);
            
            // 결과
            $result = $motherAccount->get();
            $result = Func::chungDec([$tableName], $result);	// CHUNG DATABASE DECRYPT
    
            // 뷰단 데이터 정리.
            $cnt = 0;
            $configArr = Func::getConfigArr();
            foreach ($result as $v)
            {
                unset($mo_bank_ssn);
    
                $mo_bank_ssn = preg_replace('/[^0-9]/', '', $v->mo_bank_ssn);
    
                $v->mo_acct_div = isset($configArr['mo_acct_div'][$v->mo_acct_div])?$configArr['mo_acct_div'][$v->mo_acct_div]:'';
                $v->mo_acct_div = '<a onclick="moAcctForm(\''.$v->no.'\');" style="cursor: pointer;" class="text-primary">'.$v->mo_acct_div.'</a>';
                
                // 해당일에 데이터가 있으면
                if(!empty($moneyArray[$mo_bank_ssn]))
                {
                    $v->now_money = isset($moneyArray[$mo_bank_ssn]['now_money']) ? $moneyArray[$mo_bank_ssn]['now_money']:0;                    
                    $v->in_money  = isset($moneyArray[$mo_bank_ssn]['in_money']) ? $moneyArray[$mo_bank_ssn]['in_money']:0;
                    $v->out_money = isset($moneyArray[$mo_bank_ssn]['out_money']) ? $moneyArray[$mo_bank_ssn]['out_money']:0;                    
                    $v->old_money = ($v->now_money+$v->out_money)-$v->in_money;
    
                    $v->now_money   = number_format($v->now_money);
                    $v->in_money    = number_format($v->in_money);
                    $v->out_money   = number_format($v->out_money);
                    $v->old_money   = number_format($v->old_money);
                }
                else
                {
                    $lastTrade = DB::connection('band')->table('TRADE_DATA_TBL')->select("BALANCE")
                                                    ->where("DEAL_DATE", "<=", $info_date)
                                                    ->where("CORP_ACC_NO", $mo_bank_ssn)
                                                    ->orderBy("DEAL_DATE", "DESC")
                                                    ->orderBy("DEAL_TIME", "DESC")
                                                    ->first();
    
                    $v->old_money = number_format($lastTrade->BALANCE);
                    $v->now_money = number_format($lastTrade->BALANCE);
                    $v->in_money  = 0;
                    $v->out_money = 0;
                }
    
    
                $v->mo_bank_cd        = isset($configArr['bank_cd'][$v->mo_bank_cd])?$configArr['bank_cd'][$v->mo_bank_cd]:'';
                $v->mo_acct_cd        = isset($configArr['mo_acct_cd'][$v->mo_acct_cd])?$configArr['mo_acct_cd'][$v->mo_acct_cd]:'';
                $v->mo_acct_sub_cd    = isset($configArr['mo_acct_sub_cd'][$v->mo_acct_sub_cd])?$configArr['mo_acct_sub_cd'][$v->mo_acct_sub_cd]:'';
    
                $v->status            = $v->status == 'Y'?'사용':'미사용';
    
                $r['v'][] = $v;
                $cnt ++;
            }
        }
        // 우리은행(WINCMS)
        elseif($tabsSelect == 'W')
        {
            // 기준일
            $tradeData = DB::connection('wincms')->table('WCMS_GW_ACCOUNT_TRNX_LOG')->select("BANK_CODE", "ACCOUNT_NO", "BALANCE", "TRNX_TYPE", "AMOUNT")
                                                                        ->where("TRNX_DATE", $info_date)
                                                                        ->orderBy("TRNX_TIME")
                                                                        ->get();
            foreach( $tradeData as $val )
            {
                if(!empty($val))
                {
                    $moneyArray[$val->ACCOUNT_NO]['now_money']    = ($val->BALANCE ?? 0)*1;
    
                    if($val->TRNX_TYPE == '16' || $val->TRNX_TYPE == '18' || $val->TRNX_TYPE == '19' || $val->TRNX_TYPE == '50')
                    {                   
                        $moneyArray[$val->ACCOUNT_NO]['in_money'] = isset($moneyArray[$val->ACCOUNT_NO]['in_money']) ? $moneyArray[$val->ACCOUNT_NO]['in_money'] + ($val->AMOUNT)*1 : ($val->AMOUNT)*1;
                    }
                    else if($val->TRNX_TYPE == '28' || $val->TRNX_TYPE == '29')
                    {
                        $moneyArray[$val->ACCOUNT_NO]['out_money'] = isset($moneyArray[$val->ACCOUNT_NO]['out_money']) ? $moneyArray[$val->ACCOUNT_NO]['out_money'] + ($val->AMOUNT)*1 : ($val->AMOUNT)*1;
                    }
                }
            }
    
            $motherAccount = $list->getListQuery($tableName, 'main', $motherAccount, $param);
    
            // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
            $paging = new Paging($motherAccount, $request->page, $request->listLimit, 10);
            
            // 결과
            $result = $motherAccount->get();
            $result = Func::chungDec([$tableName], $result);	// CHUNG DATABASE DECRYPT
    
            // 뷰단 데이터 정리.
            $cnt = 0;
            $configArr = Func::getConfigArr();
            foreach ($result as $v)
            {
                unset($mo_bank_ssn);
    
                $mo_bank_ssn = preg_replace('/[^0-9]/', '', $v->mo_bank_ssn);
    
                $v->mo_acct_div = isset($configArr['mo_acct_div'][$v->mo_acct_div])?$configArr['mo_acct_div'][$v->mo_acct_div]:'';
                $v->mo_acct_div = '<a onclick="moAcctForm(\''.$v->no.'\');" style="cursor: pointer;" class="text-primary">'.$v->mo_acct_div.'</a>';
                
                // 해당일에 데이터가 있으면
                if(!empty($moneyArray[$mo_bank_ssn]))
                {
                    $v->now_money = isset($moneyArray[$mo_bank_ssn]['now_money']) ? $moneyArray[$mo_bank_ssn]['now_money']:0;                    
                    $v->in_money  = isset($moneyArray[$mo_bank_ssn]['in_money']) ? $moneyArray[$mo_bank_ssn]['in_money']:0;
                    $v->out_money = isset($moneyArray[$mo_bank_ssn]['out_money']) ? $moneyArray[$mo_bank_ssn]['out_money']:0;                    
                    $v->old_money = ($v->now_money+$v->out_money)-$v->in_money;
    
                    $v->now_money   = number_format($v->now_money);
                    $v->in_money    = number_format($v->in_money);
                    $v->out_money   = number_format($v->out_money);
                    $v->old_money   = number_format($v->old_money);
                }
                // 기준일에 데이터가 없으면, 기준일과 가장 가까운 마지막데이터 추출
                else
                {
                    $lastTrade = DB::connection('wincms')->table('WCMS_GW_ACCOUNT_TRNX_LOG')->select("BALANCE")
                                                    ->where("TRNX_DATE", "<=", $info_date)
                                                    ->where("ACCOUNT_NO", $mo_bank_ssn)
                                                    ->orderBy("TRNX_DATE", "DESC")
                                                    ->orderBy("TRNX_TIME", "DESC")
                                                    ->first();
    
                    if(!empty($lastTrade->BALANCE))
                    {
                        $v->old_money = number_format($lastTrade->BALANCE);
                        $v->now_money = number_format($lastTrade->BALANCE);
                        $v->in_money  = 0;
                        $v->out_money = 0;
                    }
                    else
                    {
                        $v->old_money = 0;
                        $v->now_money = 0;
                        $v->in_money  = 0;
                        $v->out_money = 0;
                    }
                }
    
    
                $v->mo_bank_cd        = isset($configArr['bank_cd'][$v->mo_bank_cd])?$configArr['bank_cd'][$v->mo_bank_cd]:'';
                $v->mo_acct_cd        = isset($configArr['mo_acct_cd'][$v->mo_acct_cd])?$configArr['mo_acct_cd'][$v->mo_acct_cd]:'';
                $v->mo_acct_sub_cd    = isset($configArr['mo_acct_sub_cd'][$v->mo_acct_sub_cd])?$configArr['mo_acct_sub_cd'][$v->mo_acct_sub_cd]:'';
    
                $v->status            = $v->status == 'Y'?'사용':'미사용';
    
                $r['v'][] = $v;
                $cnt ++;
            }
        }
        // 기업은행(유미 - 거래내역 X)
        else
        {
            $motherAccount = $list->getListQuery($tableName, 'main', $motherAccount, $param);
    
            // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
            $paging = new Paging($motherAccount, $request->page, $request->listLimit, 10);
            
            // 결과
            $result = $motherAccount->get();
            $result = Func::chungDec([$tableName], $result);	// CHUNG DATABASE DECRYPT
    
            // 뷰단 데이터 정리.
            $cnt = 0;
            $configArr = Func::getConfigArr();
            foreach ($result as $v)
            {
                unset($mo_bank_ssn);
    
                $mo_bank_ssn = preg_replace('/[^0-9]/', '', $v->mo_bank_ssn);
    
                $v->mo_acct_div = isset($configArr['mo_acct_div'][$v->mo_acct_div])?$configArr['mo_acct_div'][$v->mo_acct_div]:'';
                $v->mo_acct_div = '<a onclick="moAcctForm(\''.$v->no.'\');" style="cursor: pointer;" class="text-primary">'.$v->mo_acct_div.'</a>';
                
                $v->old_money = 0;
                $v->now_money = 0;
                $v->in_money  = 0;
                $v->out_money = 0;
    
                $v->mo_bank_cd        = isset($configArr['bank_cd'][$v->mo_bank_cd])?$configArr['bank_cd'][$v->mo_bank_cd]:'';
                $v->mo_acct_cd        = isset($configArr['mo_acct_cd'][$v->mo_acct_cd])?$configArr['mo_acct_cd'][$v->mo_acct_cd]:'';
                $v->mo_acct_sub_cd    = isset($configArr['mo_acct_sub_cd'][$v->mo_acct_sub_cd])?$configArr['mo_acct_sub_cd'][$v->mo_acct_sub_cd]:'';
    
                $v->status            = $v->status == 'Y'?'사용':'미사용';
    
                $r['v'][] = $v;
                $cnt ++;
            }
        }
        
        // 페이징
        $r['pageList'] = $paging->getPagingHtml($request->path());

		$r['result'] = 1;
		$r['txt'] = $cnt;

        return json_encode($r);
    }

    /**
     * 계좌상세 입력폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function moAcctForm(Request $request)
    {
        $arrayConfig  = Func::getConfigArr();
        
        $v = DB::table("vir_acct_mo")->select("*")->where('no', $request->no)->where('save_status','Y')->first();
        $v = Func::chungDec(["vir_acct_mo"], $v);	// CHUNG DATABASE DECRYPT

        return view('account.moAcctForm')->with(["v"=>$v, "arrayConfig"=>$arrayConfig]);
    }

    
    /**
     * 법인통장관리 입력폼 처리
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function moAcctAction(Request $request)
    {
        $ARR = $request->all();

        if(!isset($request->status))
        {
            $ARR['status'] = 'N';
        }

        if(isset($request->check_no))
        {
            $no = $request->check_no;
        }

        if($ARR['mo_acct_status'] == "INS" || $ARR['mo_acct_status'] == "UPD")
        {
            $ARR['save_status'] = "Y";
            $ARR['save_time']   = date("YmdHis");
            $ARR['save_id']     = Auth::id();
        }
        else
        {
            $ARR['save_status'] = "N";
            $ARR['del_time']    = date("YmdHis");
            $ARR['del_id']      = Auth::id();
        }
        
        // 결과데이터
        $RS['rs_code'] = "N";
        
        if($ARR['mo_acct_status'] == "DEL")
        {
            $chk = DB::table("cust_info")->select('cust_info.no')
                                        ->join("cust_info_extra", "cust_info_extra.cust_info_no", "=", "cust_info.no")
                                        ->where('cust_info.vir_acct_no',$ARR['mo_acct_div'])
                                        ->where('cust_info_extra.bank_cd',$ARR['mo_bank_cd'])
                                        ->where('cust_info_extra.bank_ssn',Func::encrypt($ARR['mo_bank_ssn'], 'ENC_KEY_SOL'))
                                        ->where('cust_info.save_status','Y')
                                        ->first();
            if( !empty($chk->no) )
            {
                $RS['rs_msg']   = "해당 계좌를 사용중인 차입자를 확인해주세요.";
                $RS['rs_code']  = "N";
            }
            else
            {
                $rslt = DB::dataProcess('UPD', 'vir_acct_mo', $ARR, ["no"=>$no]);
            }
        }
        else if($ARR['mo_acct_status'] == "UPD")
        {
            $chk2 = DB::table("vir_acct_mo")->where('mo_bank_cd',$ARR['mo_bank_cd'])->where('mo_bank_ssn',Func::encrypt($ARR['mo_bank_ssn'], 'ENC_KEY_SOL'))->where('save_status','Y')->count();
            if( $chk2 > 1 )
            {
                $RS['rs_msg']   = "계좌번호가 중복입니다. 계좌번호를 확인해주세요.";
                $RS['rs_code']  = "N";
            }
            else
            {
                $rslt = DB::dataProcess('UPD', 'vir_acct_mo', $ARR, ["no"=>$no]);
            }
        }
        else
        {
            $chk = DB::table("vir_acct_mo")->where('mo_bank_cd',$ARR['mo_bank_cd'])->where('mo_bank_ssn',Func::encrypt($ARR['mo_bank_ssn'], 'ENC_KEY_SOL'))->where('save_status','Y')->count();
            if( $chk > 0 )
            {
                $RS['rs_msg']   = "계좌번호가 중복입니다. 계좌번호를 확인해주세요.";
                $RS['rs_code']  = "N";
            }
            else
            {
                $rslt = DB::dataProcess('INS', 'vir_acct_mo', $ARR);
            }
        }

        if(isset($rslt))
        {
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
     * 법인통장관리 리스트 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function moAcctExcel(Request $request)
    {
        if( !Func::funcCheckPermit("C001") && !isset($request->excel_flag) )
        {
            return ['result'=>'N', 'error_msg'=>"엑셀 다운로드 권한이 없습니다.\n관리자에게 문의해 주세요."];
        }

        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');

        $list           = $this->setDataList($request);

        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        if( $param['searchDt'] == "")
        {
            $param['searchDt'] = "info_date";
        }

        if( $param['searchDtString'] == "" )
        {
            $param['searchDtString'] = date("Y-m-d");
        }

        if( $param['tabsSelect'] == "" )
        {
            $tabsSelect = 'S';
        }

        $tabsSelect = $param['tabsSelect'];

        // 날짜형식변환
        $search_date = str_replace("-", "", $param['searchDtString']);

        $moneyArray = Array();
        $info_date = $search_date;
        $tableName = 'vir_acct_mo';

        // 메인쿼리
        $motherAccount = DB::TABLE($tableName)->SELECT("*")->WHERE('save_status','Y');

        if(!isset($param['listOrder']))
        {
            $param['listOrder']    = "no";
            $param['listOrderAsc'] = "asc";
        }

        // 신한은행
        if($tabsSelect == 'S')
        {
            $motherAccount = $motherAccount->where("mo_bank_cd", "088");
        }
        // 우리은행
        elseif($tabsSelect == 'W')
        {
            $motherAccount = $motherAccount->where("mo_bank_cd", "020");
        }
        // 기업은행
        else
        {
            $motherAccount = $motherAccount->where("mo_bank_cd", "003");
        }

        unset($oldTradeData, $tradeData, $param['searchDt'], $param['searchDtString'], $param['tabsSelect']);

        // 신한은행(하이픈)
        if($tabsSelect == 'S')
        {
            // 기준일
            $tradeData = DB::connection('band')->table('TRADE_DATA_TBL')->select("BANK_CODE_3", "CORP_ACC_NO", "BALANCE", "DEAL_SELE", "TOTAL_AMT")
                                                                        ->where("DEAL_DATE", $info_date)
                                                                        ->orderBy("DEAL_TIME")
                                                                        ->get();
            foreach( $tradeData as $val )
            {
                if(!empty($val))
                {
                    $moneyArray[$val->CORP_ACC_NO]['now_money']    = ($val->BALANCE ?? 0)*1;
    
                    if($val->DEAL_SELE == '20' || $val->DEAL_SELE == '40')
                    {                   
                        $moneyArray[$val->CORP_ACC_NO]['in_money'] = isset($moneyArray[$val->CORP_ACC_NO]['in_money']) ? $moneyArray[$val->CORP_ACC_NO]['in_money'] + ($val->TOTAL_AMT)*1 : ($val->TOTAL_AMT)*1;
                    }
                    else if($val->DEAL_SELE == '30')
                    {
                        $moneyArray[$val->CORP_ACC_NO]['out_money'] = isset($moneyArray[$val->CORP_ACC_NO]['out_money']) ? $moneyArray[$val->CORP_ACC_NO]['out_money'] + ($val->TOTAL_AMT)*1 : ($val->TOTAL_AMT)*1;
                    }
                }
            }

            $motherAccount = $list->getListQuery("VIR_ACCT_MO", 'main', $motherAccount, $param);

            // 현재 페이지 출력 
            if( $down_div=='now' )
            {
                // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
                $paging = new Paging($motherAccount, $request->nowPage, $request->listLimit, 10, $request->listName);
            }

            // 엑셀다운 로그 시작
            $record_count = 0;
            $query        = Func::printQuery($motherAccount);
            log::info($query);
            $file_name    = "법인통장 관리_".date("YmdHis").'_'.Auth::id().'.xlsx';
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
                $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $query, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
                if($excel_down_div == 'S')
                {
                    $yet['result']  = 'Y';
                    return $yet;
                }
                $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
            }

            $rslt = $motherAccount->GET();
            $rslt = Func::chungDec(["VIR_ACCT_MO"], $rslt);	// CHUNG DATABASE DECRYPT

            // 엑셀 헤더
            $excel_header = array('NO','법인','은행','사용/미사용','계좌번호','계좌명','전일 잔액','입금액','출금액','금일 잔액', '대분류', '중분류', '메모');
            $excel_data = [];
            $configArr = Func::getConfigArr();
            foreach ($rslt as $v)
            {
                unset($mo_bank_ssn);
    
                $mo_bank_ssn = preg_replace('/[^0-9]/', '', $v->mo_bank_ssn);

                // 해당일에 데이터가 있으면
                if(!empty($moneyArray[$mo_bank_ssn]))
                {
                    $v->now_money = isset($moneyArray[$mo_bank_ssn]['now_money']) ? $moneyArray[$mo_bank_ssn]['now_money']:0;                    
                    $v->in_money  = isset($moneyArray[$mo_bank_ssn]['in_money']) ? $moneyArray[$mo_bank_ssn]['in_money']:0;
                    $v->out_money = isset($moneyArray[$mo_bank_ssn]['out_money']) ? $moneyArray[$mo_bank_ssn]['out_money']:0;                    
                    $v->old_money = ($v->now_money+$v->out_money)-$v->in_money;
                }
                // 기준일에 데이터가 없으면, 기준일과 가장 가까운 마지막데이터 추출
                else
                {
                    $lastTrade = DB::connection('band')->table('TRADE_DATA_TBL')->select("BALANCE")
                                                    ->where("DEAL_DATE", "<=", $info_date)
                                                    ->where("CORP_ACC_NO", $mo_bank_ssn)
                                                    ->orderBy("DEAL_DATE", "DESC")
                                                    ->orderBy("DEAL_TIME", "DESC")
                                                    ->first();
    
                    if(!empty($lastTrade->BALANCE))
                    {
                        $v->old_money = $lastTrade->BALANCE;
                        $v->now_money = $lastTrade->BALANCE;
                        $v->in_money  = 0;
                        $v->out_money = 0;
                    }
                    else
                    {
                        $v->old_money = 0;
                        $v->now_money = 0;
                        $v->in_money  = 0;
                        $v->out_money = 0;
                    }
                }

                $array_data = [
                    $v->no,
                    isset($configArr['mo_acct_div'][$v->mo_acct_div])?$configArr['mo_acct_div'][$v->mo_acct_div]:'',
                    isset($configArr['bank_cd'][$v->mo_bank_cd])?$configArr['bank_cd'][$v->mo_bank_cd]:'',
                    $v->status == 'Y'?'사용':'미사용',
                    $v->mo_bank_ssn,
                    $v->mo_bank_name,
                    isset($v->old_money)?number_format($v->old_money):0,
                    isset($v->in_money)?number_format($v->in_money):0,
                    isset($v->out_money)?number_format($v->out_money):0,
                    isset($v->now_money)?number_format($v->now_money):0,
                    isset($configArr['mo_acct_cd'][$v->mo_acct_cd])?$configArr['mo_acct_cd'][$v->mo_acct_cd]:'',
                    isset($configArr['mo_acct_sub_cd'][$v->mo_acct_sub_cd])?$configArr['mo_acct_sub_cd'][$v->mo_acct_sub_cd]:'',
                    $v->memo,
                ];
                $record_count++;
                $excel_data[] = $array_data;
            }
        }
        // 우리은행(WINCMS)
        elseif($tabsSelect == 'W')
        {
            // 기준일
            $tradeData = DB::connection('wincms')->table('WCMS_GW_ACCOUNT_TRNX_LOG')->select("BANK_CODE", "ACCOUNT_NO", "BALANCE", "TRNX_TYPE", "AMOUNT")
                                                                        ->where("TRNX_DATE", $info_date)
                                                                        // ->where("ACCOUNT_NO", "100031500271")
                                                                        ->orderBy("TRNX_TIME")
                                                                        ->get();
            foreach( $tradeData as $val )
            {
                if(!empty($val))
                {
                    $moneyArray[$val->ACCOUNT_NO]['now_money']    = ($val->BALANCE ?? 0)*1;
    
                    if($val->TRNX_TYPE == '16' || $val->TRNX_TYPE == '18' || $val->TRNX_TYPE == '19' || $val->TRNX_TYPE == '50')
                    {                   
                        $moneyArray[$val->ACCOUNT_NO]['in_money'] = isset($moneyArray[$val->ACCOUNT_NO]['in_money']) ? $moneyArray[$val->ACCOUNT_NO]['in_money'] + ($val->AMOUNT)*1 : ($val->AMOUNT)*1;
                    }
                    else if($val->TRNX_TYPE == '28' || $val->TRNX_TYPE == '29')
                    {
                        $moneyArray[$val->ACCOUNT_NO]['out_money'] = isset($moneyArray[$val->ACCOUNT_NO]['out_money']) ? $moneyArray[$val->ACCOUNT_NO]['out_money'] + ($val->AMOUNT)*1 : ($val->AMOUNT)*1;
                    }
                }
            }

            $motherAccount = $list->getListQuery("VIR_ACCT_MO", 'main', $motherAccount, $param);

            // 현재 페이지 출력 
            if( $down_div=='now' )
            {
                // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
                $paging = new Paging($motherAccount, $request->nowPage, $request->listLimit, 10, $request->listName);
            }

            // 엑셀다운 로그 시작
            $record_count = 0;
            $query        = Func::printQuery($motherAccount);
            log::info($query);
            $file_name    = "법인통장 관리_".date("YmdHis").'_'.Auth::id().'.xlsx';
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
                $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $query, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
                if($excel_down_div == 'S')
                {
                    $yet['result']  = 'Y';
                    return $yet;
                }
                $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
            }

            $rslt = $motherAccount->GET();
            $rslt = Func::chungDec(["VIR_ACCT_MO"], $rslt);	// CHUNG DATABASE DECRYPT

            // 엑셀 헤더
            $excel_header = array('NO','법인','은행','사용/미사용','계좌번호','계좌명','전일 잔액','입금액','출금액','금일 잔액', '대분류', '중분류', '메모');
            $excel_data = [];
            $configArr = Func::getConfigArr();
            foreach ($rslt as $v)
            {
                unset($mo_bank_ssn);
    
                $mo_bank_ssn = preg_replace('/[^0-9]/', '', $v->mo_bank_ssn);

                // 해당일에 데이터가 있으면
                if(!empty($moneyArray[$mo_bank_ssn]))
                {
                    $v->now_money = isset($moneyArray[$mo_bank_ssn]['now_money']) ? $moneyArray[$mo_bank_ssn]['now_money']:0;                    
                    $v->in_money  = isset($moneyArray[$mo_bank_ssn]['in_money']) ? $moneyArray[$mo_bank_ssn]['in_money']:0;
                    $v->out_money = isset($moneyArray[$mo_bank_ssn]['out_money']) ? $moneyArray[$mo_bank_ssn]['out_money']:0;                    
                    $v->old_money = ($v->now_money+$v->out_money)-$v->in_money;
                }
                // 기준일에 데이터가 없으면, 기준일과 가장 가까운 마지막데이터 추출
                else
                {
                    $lastTrade = DB::connection('wincms')->table('WCMS_GW_ACCOUNT_TRNX_LOG')->select("BALANCE")
                                                    ->where("TRNX_DATE", "<=", $info_date)
                                                    ->where("ACCOUNT_NO", $mo_bank_ssn)
                                                    ->orderBy("TRNX_DATE", "DESC")
                                                    ->orderBy("TRNX_TIME", "DESC")
                                                    ->first();
    
                    if(!empty($lastTrade->BALANCE))
                    {
                        $v->old_money = $lastTrade->BALANCE;
                        $v->now_money = $lastTrade->BALANCE;
                        $v->in_money  = 0;
                        $v->out_money = 0;
                    }
                    else
                    {
                        $v->old_money = 0;
                        $v->now_money = 0;
                        $v->in_money  = 0;
                        $v->out_money = 0;
                    }
                }

                $array_data = [
                    $v->no,
                    isset($configArr['mo_acct_div'][$v->mo_acct_div])?$configArr['mo_acct_div'][$v->mo_acct_div]:'',
                    isset($configArr['bank_cd'][$v->mo_bank_cd])?$configArr['bank_cd'][$v->mo_bank_cd]:'',
                    $v->status == 'Y'?'사용':'미사용',
                    $v->mo_bank_ssn,
                    $v->mo_bank_name,
                    isset($v->old_money)?number_format($v->old_money):0,
                    isset($v->in_money)?number_format($v->in_money):0,
                    isset($v->out_money)?number_format($v->out_money):0,
                    isset($v->now_money)?number_format($v->now_money):0,
                    isset($configArr['mo_acct_cd'][$v->mo_acct_cd])?$configArr['mo_acct_cd'][$v->mo_acct_cd]:'',
                    isset($configArr['mo_acct_sub_cd'][$v->mo_acct_sub_cd])?$configArr['mo_acct_sub_cd'][$v->mo_acct_sub_cd]:'',
                    $v->memo,
                ];
                $record_count++;
                $excel_data[] = $array_data;
            }
        }
        // 기업은행(유미 - 거래내역 X)
        else
        {
            $motherAccount = $list->getListQuery($tableName, 'main', $motherAccount, $param);

            // 현재 페이지 출력 
            if( $down_div=='now' )
            {
                // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
                $paging = new Paging($motherAccount, $request->nowPage, $request->listLimit, 10, $request->listName);
            }

            // 엑셀다운 로그 시작
            $record_count = 0;
            $query        = Func::printQuery($motherAccount);
            log::info($query);
            $file_name    = "법인통장 관리_".date("YmdHis").'_'.Auth::id().'.xlsx';
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
                $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $query, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
                if($excel_down_div == 'S')
                {
                    $yet['result']  = 'Y';
                    return $yet;
                }
                $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
            }

            $rslt = $motherAccount->GET();
            $rslt = Func::chungDec(["VIR_ACCT_MO"], $rslt);	// CHUNG DATABASE DECRYPT

            // 엑셀 헤더
            $excel_header = array('NO','법인','은행','사용/미사용','계좌번호','계좌명','전일 잔액','입금액','출금액','금일 잔액', '대분류', '중분류', '메모');
            $excel_data = [];
            $configArr = Func::getConfigArr();
            foreach ($rslt as $v)
            {
                unset($mo_bank_ssn);
    
                $mo_bank_ssn = preg_replace('/[^0-9]/', '', $v->mo_bank_ssn);

                $v->old_money = 0;
                $v->now_money = 0;
                $v->in_money  = 0;
                $v->out_money = 0;

                $array_data = [
                    $v->no,
                    isset($configArr['mo_acct_div'][$v->mo_acct_div])?$configArr['mo_acct_div'][$v->mo_acct_div]:'',
                    isset($configArr['bank_cd'][$v->mo_bank_cd])?$configArr['bank_cd'][$v->mo_bank_cd]:'',
                    $v->status == 'Y'?'사용':'미사용',
                    $v->mo_bank_ssn,
                    $v->mo_bank_name,
                    isset($v->old_money)?number_format($v->old_money):0,
                    isset($v->in_money)?number_format($v->in_money):0,
                    isset($v->out_money)?number_format($v->out_money):0,
                    isset($v->now_money)?number_format($v->now_money):0,
                    isset($configArr['mo_acct_cd'][$v->mo_acct_cd])?$configArr['mo_acct_cd'][$v->mo_acct_cd]:'',
                    isset($configArr['mo_acct_sub_cd'][$v->mo_acct_sub_cd])?$configArr['mo_acct_sub_cd'][$v->mo_acct_sub_cd]:'',
                    $v->memo,
                ];
                $record_count++;
                $excel_data[] = $array_data;
            }
        
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
}