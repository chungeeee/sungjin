<?php
namespace App\Http\Controllers\Account;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use App\Chung\Paging;
use App\Chung\Vars;
use DataList;
class MoAcctTradeController extends Controller
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
     * 법인통장 거래내역 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setMoAcctTradeList(Request $request){

        $list   = new DataList(Array("listName"=>"moAcctTrade","listAction"=>'/'.$request->path()));
        
        $list->setSearchDate('날짜검색',Array('trade_date' => '거래일자'),'searchDt','Y', '', '', '','trade_date');

        $list->setSearchType('status', Array('I' => '입금', 'O'  => '출금'), '입출금');
        $list->setSearchDetail(Array(
            'name' => '내용',
        ));

        return $list;
    }

     /**
     * 법인통장 거래내역 메인화면
     *
     * @param  Void
     * @return view
     */
	public function moAcctTrade(Request $request)
    {
        $list = $this->setMoAcctTradeList($request);

        $configArr = Func::getConfigArr();

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬)
        $list->setlistTitleCommon(Array
        (
            'trade_date'    => Array('거래일자', 0, '', 'center', '', ''),
            'trade_time'    => Array('거래시간', 0, '', 'center', '', ''),
            'out_money'     =>  Array('출금(원)', 0, '', 'center', '', ''),
            'in_money'      =>  Array('입금(원)', 0, '', 'center', '', ''),
            'memo'          =>  Array('내용', 0, '', 'center', '', ''),
            'balance'       =>  Array('잔액(원)', 0, '', 'center', '', ''),
        ));

        $moAcctArray1 = [];
        $moAcctArray2 = [];

        // 메인쿼리(신한은행)
        $moAcct1 = DB::table("vir_acct_mo")->select("*")
                                            ->where('mo_bank_cd','088')
                                            ->where('firm_banking','Y')
                                            ->where('save_status','Y')
                                            ->orderBy("mo_acct_cd")
                                            ->orderBy("mo_acct_sub_cd")
                                            ->get();
        $moAcct1 = Func::chungDec(["vir_acct_mo"], $moAcct1);	// CHUNG DATABASE DECRYPT
        foreach ($moAcct1 as $key => $val)
        {
            // 기준일
            $tradeData = DB::connection('band')->table('tran_balance')->select("BALANCE", "DEAL_DATE")
                                                                    ->where("CORP_ACC_NO", preg_replace('/[^0-9]/', '', $val->mo_bank_ssn))
                                                                    ->orderBy("DEAL_DATE", "DESC")
                                                                    ->orderBy("DEAL_TIME", "DESC")
                                                                    ->first();

            $val->last_trade_date = isset($tradeData->DEAL_DATE) ? Func::dateFormat($tradeData->DEAL_DATE) : '';
            $val->now_money       = isset($tradeData->BALANCE) ? number_format(($tradeData->BALANCE ?? 0)*1) : '-';

            $moAcctArray1[] = $val;
        }

        // 메인쿼리2(우리은행)
        $moAcct2 = DB::table("vir_acct_mo")->select("*")
                                            ->where('mo_bank_cd','020')
                                            ->where('mo_acct_div','1')
                                            ->where('save_status','Y')
                                            ->orderBy("no")
                                            ->get();
        $moAcct2 = Func::chungDec(["vir_acct_mo"], $moAcct2);	// CHUNG DATABASE DECRYPT
        foreach ($moAcct2 as $key => $val)
        {
            // 기준일
            $tradeData = DB::connection('wincms')->table('WCMS_GW_ACCOUNT_TRNX_LOG')->select("TRNX_DATE", "BALANCE")
                                                                    ->where("ACCOUNT_NO", preg_replace('/[^0-9]/', '', $val->mo_bank_ssn))
                                                                    ->orderBy("TRNX_DATE", "DESC")
                                                                    ->orderBy("TRNX_TIME", "DESC")
                                                                    ->first();

            $val->last_trade_date = isset($tradeData->TRNX_DATE) ? Func::dateFormat($tradeData->TRNX_DATE) : '';
            $val->now_money       = isset($tradeData->BALANCE) ? number_format(($tradeData->BALANCE ?? 0)*1) : '-';

            $moAcctArray2[] = $val;
        }

        return view('account.moAcctTrade')->with(['moAcct1'=>$moAcctArray1])
                                          ->with(['moAcct2'=>$moAcctArray2])
                                          ->with(['configArr'=>$configArr])
                                          ->with('result', $list->getList());
    }

     /**
     * 법인통장 거래내역 리스트
     *
     * @param  Void
     * @return Json $r
     */
	public function moAcctTradeList(Request $request)
    {
        $list   = $this->setMoAcctTradeList($request);

        $param  = $request->all();

        Log::debug(print_r($param, true));

        if(isset($request->customSearch) && isset($request->etc))
        {
            // 신한은행 - 하이픈
            if($request->etc == 'S')
            {
                // 메인쿼리
                $tradeAccount = DB::connection("band")->table("TRADE_DATA_TBL")->select("*");

                $moAcct = DB::table("vir_acct_mo")->select("*")->where('no',$request->customSearch)->where('save_status','Y')->first();
                $moAcct = Func::chungDec(["vir_acct_mo"], $moAcct);	// CHUNG DATABASE DECRYPT
    
                $tradeAccount = $tradeAccount->where("BANK_CODE_3", $moAcct->mo_bank_cd);
                $tradeAccount = $tradeAccount->where("CORP_ACC_NO", preg_replace('/[^0-9]/', '', $moAcct->mo_bank_ssn));

                // 거래일자 시작일
                if(isset( $param['searchDt']) && $param['searchDt']=='trade_date' && !empty($param['searchDtString']) )
                {
                    $param['searchDt'] = 'deal_date';
                    $param['searchDtString'] = str_replace("-", "", $param['searchDtString']);
                }

                // 거래일자 종료일
                if(isset( $param['searchDt']) && $param['searchDt']=='trade_date' && !empty($param['searchDtStringEnd']) )
                {
                    $param['searchDt'] = 'deal_date';
                    $param['searchDtStringEnd'] = str_replace("-", "", $param['searchDtStringEnd']);
                }

                 // 입출금상태
                 if(isset( $param['status']) )
                 {
                     // 입금내역
                     if($param['status'] == 'I')
                     {
                         $tradeAccount = $tradeAccount->whereIN("DEAL_SELE", ["20", "40"]);
                     }
                     // 출금내역
                     else
                     {
                         $tradeAccount = $tradeAccount->whereIN("DEAL_SELE", ["30"]);
                     }
                     unset($param['status']);
                 }

                // 내용
                if(isset( $param['searchString']) )
                {
                    $tradeAccount = $tradeAccount->where("CUST_NAME", $param['searchString']);

                    unset($param['searchString']);
                }

                if(!isset($param['listOrder']))
                {
                    $param['listOrder']    = "deal_date";
                    $param['listOrderAsc'] = "desc";
                }

                $tradeAccount = $list->getListQuery("TRADE_DATA_TBL", 'main', $tradeAccount, $param);
            }
            // 우리은행 - wincms
            elseif($request->etc == 'W')
            {
                $tradeAccount = DB::connection('wincms')->table('WCMS_GW_ACCOUNT_TRNX_LOG')->select("*");

                $moAcct = DB::table("vir_acct_mo")->select("*")->where('no',$request->customSearch)->where('save_status','Y')->first();
                $moAcct = Func::chungDec(["vir_acct_mo"], $moAcct);	// CHUNG DATABASE DECRYPT
    
                $tradeAccount = $tradeAccount->where("BANK_CODE", $moAcct->mo_bank_cd);
                $tradeAccount = $tradeAccount->where("ACCOUNT_NO", preg_replace('/[^0-9]/', '', $moAcct->mo_bank_ssn));

                // 거래일자 시작일
                if(isset( $param['searchDt']) && $param['searchDt']=='trade_date' && !empty($param['searchDtString']) )
                {
                    $param['searchDt'] = 'TRNX_DATE';
                    $param['searchDtString'] = str_replace("-", "", $param['searchDtString']);
                }

                // 거래일자 종료일
                if(isset( $param['searchDt']) && $param['searchDt']=='trade_date' && !empty($param['searchDtStringEnd']) )
                {
                    $param['searchDt'] = 'TRNX_DATE';
                    $param['searchDtStringEnd'] = str_replace("-", "", $param['searchDtStringEnd']);
                }

                // 입출금상태
                if(isset( $param['status']) )
                {
                    // 입금내역
                    if($param['status'] == 'I')
                    {
                        $tradeAccount = $tradeAccount->whereIN("TRNX_TYPE", ["16", "18", "19", "50"]);
                    }
                    // 출금내역
                    else
                    {
                        $tradeAccount = $tradeAccount->whereIN("TRNX_TYPE", ["28", "29"]);
                    }
                    unset($param['status']);
                }

                // 내용
                if(isset( $param['searchString']) )
                {
                    $tradeAccount = $tradeAccount->where("REQUESTER_NAME", $param['searchString']);

                    unset($param['searchString']);
                }

                if(!isset($param['listOrder']))
                {
                    $param['listOrder']    = "TRNX_DATE";
                    $param['listOrderAsc'] = "DESC";
                }

                $tradeAccount = $list->getListQuery("WCMS_GW_ACCOUNT_TRNX_LOG", 'main', $tradeAccount, $param);
            }

            // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
            $paging = new Paging($tradeAccount, $request->page, $request->listLimit, 10);
            
            Log::debug(Func::printQuery($tradeAccount));

            // 결과
            $result = $tradeAccount->get();

            // 뷰단 데이터 정리.
            $cnt = 0;
            $configArr = Func::getConfigArr();

            foreach ($result as $v)
            {
                // 신한은행 - 하이픈
                if($request->etc == 'S')
                {
                    $v->trade_date = ($v->DEAL_DATE) ? Func::dateFormat($v->DEAL_DATE) : '';
                    $v->trade_time = ($v->DEAL_TIME) ? Func::dateFormat($v->DEAL_TIME) : '';
                    
                    // 입금
                    if($v->DEAL_SELE == '20' || $v->DEAL_SELE == '40')
                    {
                        $v->in_money   = number_format(($v->TOTAL_AMT ?? 0)*1);
                        $v->out_money  = '';
                    }
                    // 출금
                    else if($v->DEAL_SELE == '30')
                    {
                        $v->in_money   = '';
                        $v->out_money  = number_format(($v->TOTAL_AMT ?? 0)*1);
                    }
                    else
                    {
                        $v->in_money   = '';
                        $v->out_money  = '';
                    }
    
                    $v->memo       = $v->CUST_NAME ?? '';
                    $v->balance    = number_format(($v->BALANCE ?? 0)*1);
                }
                // 우리은행 - wincms
                elseif($request->etc == 'W')
                {
                    $v->trade_date = ($v->TRNX_DATE) ? Func::dateFormat($v->TRNX_DATE) : '';
                    $v->trade_time = ($v->TRNX_TIME) ? Func::dateFormat($v->TRNX_TIME) : '';

                    // 입금
                    if($v->TRNX_TYPE == '18' || $v->TRNX_TYPE == '50' || $v->TRNX_TYPE == '16' || $v->TRNX_TYPE == '19')
                    {
                        $v->in_money   = number_format(($v->AMOUNT ?? 0)*1);
                        $v->out_money  = '';
                    }
                    // 출금
                    elseif($v->TRNX_TYPE == '28' || $v->TRNX_TYPE == '29')
                    {
                        $v->in_money   = '';
                        $v->out_money  = number_format(($v->AMOUNT ?? 0)*1);
                    }
                    else
                    {
                        $v->in_money   = '';
                        $v->out_money  = '';
                    }

                    $v->memo       = $v->REQUESTER_NAME ?? '';
                    $v->balance    = number_format(($v->BALANCE ?? 0)*1);
                }
                else
                {
                    $v = Array();
                }


                $r['v'][] = $v;

                $cnt ++;
            }
            
            // 페이징
            $r['pageList'] = $paging->getPagingHtml($request->path());

            $r['result'] = 1;
            $r['txt'] = $cnt;

        }
        else
        {
            // 안나오도록
            $r['pageList'] = '';
            $r['result'] = 1;
            $r['txt'] = 0;
        }

        return json_encode($r);
    }
}
