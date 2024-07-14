<?php
namespace App\Http\Controllers\Lump;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;
use Redirect;
use App\Chung\Sms;
use PhpParser\Node\Stmt\Else_;

class LumpBorrowController extends Controller
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
    * 일괄 담보채권상태 변경
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function lumpBorrowStatus(Request $request)
    {

        $_DATA                = $request->all();
        unset($_DATA['listChk']);
        
        $_DATA                = Func::arrayDelChar($_DATA,['-']) ;
        $_DATA['save_id']     = Auth::id();
        $_DATA['save_time']   = date("YmdHis");
        $_DATA['save_status'] = "Y";

        DB::enableQueryLog();

        $sendSucess = null;
        $sendFail = null;

        if(isset($_DATA['borrow_comp_no']) && isset($_DATA['borrow_comp_sub_no']))
        {
            $sub_trade_sdate = DB::table('borrow_comp_sub')->select('sub_trade_sdate')
            ->where('save_status','Y')
            ->where('borrow_comp_no',$_DATA['borrow_comp_no'])
            ->where('sub_no',$_DATA['borrow_comp_sub_no'])->value('sub_trade_sdate');
        }

        foreach($request->listChk as $nos)
        {
            $array_no = explode('_',$nos);
            $_DATA['loan_info_no'] = $array_no[0];
            $_DATA['cust_info_no'] = DB::table('loan_info')->select('cust_info_no')->where('no', '=', $_DATA['loan_info_no'])->value('cust_info_no');

            // 담보미등록,담보검토,담보불가로 넘길때 등록정보 해지정보값 초기화
            if($request->status == 'N' || $request->status == 'R' || $request->status == 'X')
            {
                $_DATA['borrow_comp_no'] = $_DATA['borrow_comp_sub_no'] = $_DATA['mng_no'] = $_DATA['start_date'] = $_DATA['end_date'] = $_DATA['end_reason_cd'] = null;
            }

            if($request->status == 'S')
            {
                // 채권계약일 select
                $contract_date = DB::table('loan_info')->select('contract_date')
                        ->where('save_status','Y')
                        ->where('no',$_DATA['loan_info_no'] )->value('contract_date');
                        
                // 채권계약일이 담보제공계약일보다 이전일경우 등록불가
                if($contract_date < $sub_trade_sdate )
                {
                    $sendFail[] = $_DATA['loan_info_no']." : 담보제공계약일 이전 계약";
                    continue;
                }
                // 해당회원의 채권들 중 타차입처 담보등록건 확인 
                $other_comp =  DB::table('borrow')->where('cust_info_no',$_DATA['cust_info_no'])->where('borrow_comp_no','!=',$request->borrow_comp_no)->WHERE('STATUS','S')->WHERE('SAVE_STATUS','Y')->EXISTS();
                if($other_comp)
                {
                    $sendFail[] = $_DATA['loan_info_no']." : 타차입처 담보등록 회원";
                    continue;
                }
                // 해당 담보채권 서류스캔 상태 확인
                // $scan_status =  DB::table('borrow')->where('loan_info_no', $_DATA['loan_info_no'])->WHERE('SCAN_STATUS', '!=', 'Y')->WHERE('SAVE_STATUS','Y')->EXISTS();
                // if($scan_status)
                // {
                //     $sendFail[] = $_DATA['loan_info_no']." : 서류 미등록";
                //     continue;
                // }
                
                // 담보등록으로 넘길때 해지정보 초기화
                $_DATA['end_date'] = $_DATA['end_reason_cd'] = null;  
                
                // 관리번호 부여
                $_DATA['mng_no']   = DB::TABLE('BORROW')->SELECT(DB::raw('COALESCE(max(mng_no),0)+1 as mng_no'))
                ->where('save_status','Y')
                ->where('borrow_comp_no',$request->borrow_comp_no)
                ->where('borrow_comp_sub_no',$request->borrow_comp_sub_no)->value('mng_no');

            }

            if($request->status == 'E')
            {
                // 계약상태 select
                $contract_status = DB::table('loan_info')->select('status')
                        ->where('save_status','Y')
                        ->where('no',$_DATA['loan_info_no'] )->value('status');

                if(isset($contract_status) && $contract_status == 'E' && (!Func::funcCheckPermit("A145","A") || !Func::funcCheckPermit("A245","A")))
                {
                    $sendFail[] = $_DATA['loan_info_no']." : 완제건 설정 해지 권한 없음";
                    continue;
                }
                else if(isset($contract_status) && $contract_status != 'E' && (!Func::funcCheckPermit("A146","A") || !Func::funcCheckPermit("A246","A")))
                {
                    $sendFail[] = $_DATA['loan_info_no']." : 완제 이전 설정 해지 권한 없음";
                    continue;
                }
            }

            DB::beginTransaction();
            // borrow에 미등록 : INSERT필요
            if($array_no[1])
            {
                $_DATA['no'] = $array_no[1];
                $rslt = DB::dataProcess('UPD', 'BORROW', $_DATA, ["NO"=>$_DATA['no']]);
            }
            else
            {
                unset($_DATA['no']);
                $rslt = DB::dataProcess('INS', 'BORROW', $_DATA);
            }

            // 담보채권상태 = 담보중일경우 계약 borrow_yn update
            if($request->status == "S")
            {
                $loan_rslt = DB::dataProcess('UPD', 'LOAN_INFO', ['BORROW_YN'=>"Y","SAVE_ID"=>$_DATA['save_id'],"SAVE_TIME"=>$_DATA['save_time']], ["NO"=>$_DATA['loan_info_no']]);
            }
            else
            {
                $loan_rslt = DB::dataProcess('UPD', 'LOAN_INFO', ['BORROW_YN'=>"N","SAVE_ID"=>$_DATA['save_id'],"SAVE_TIME"=>$_DATA['save_time']], ["NO"=>$_DATA['loan_info_no']]);
            }

            if($rslt=='Y' && $loan_rslt=='Y')
            {
                DB::commit();
                $sendSucess[] = $_DATA['loan_info_no'];
            }
            else
            {
                DB::rollback();
                $sendFail[] = $_DATA['loan_info_no'];
            }
        }

        // DB::rollback();
        $rs_msg = "아래와 같이 담보채권 상태가 변경되었습니다.\n실패건이 있는 경우에는 관리자에게 문의해주세요.\n\n";
        if($sendSucess!=null)
        {
            $rs_msg.= "[성공] ".count($sendSucess)."건\n";
        }

        if($sendFail!=null)
        {
            $rs_msg.= "[실패] ".count($sendFail)."건\n";
            $rs_msg.= "[실패계약번호]\n".implode("\n", $sendFail)."\n";
            Log::debug("담보채권 상태변경실패 : ".$request->ch_status."\n변경자 : ".Auth::id()."\n번호 : ".implode(', ', $sendFail));
        }
        
        return $rs_msg;
    }


}