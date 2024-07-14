<?php
namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use DB;
use Func;
use Log;
use Auth;
use App\Chung\Sms;
use Redirect;
use App\Chung\Ubi;
use App\Chung\Paging;
use App\Chung\Vars;

class PrintController extends Controller
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
    * 고객정보창 양식인쇄
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function openPrint(Request $request)
    {
        $_DATA = $request->all();

        $cinfo = DB::TABLE("cust_info ci")
                    ->JOIN("cust_info_extra cie", "ci.no", "=", "cie.cust_info_no")
                    ->SELECT("ci.no", "cie.zip1", "cie.addr11", "cie.addr12", "cie.zip2", "cie.addr21", "cie.addr22", "cie.zip3", "cie.addr31", "cie.addr32", "cie.zip4", "cie.addr41", "cie.addr42", "cie.post_send_cd" )
                    ->ADDSELECT("cie.addr1_nlive_yn", "cie.addr2_nlive_yn", "cie.addr4_nlive_yn")
                    ->WHERE("ci.no", $_DATA['cust_info_no'])->get();
        $cinfo = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA"], $cinfo);	// CHUNG DATABASE DECRYPT

        $lawno = DB::TABLE('loan_info_law')->SELECT('no')->WHERE('loan_info_no', $_DATA['loan_info_no'])->WHERE('cust_info_no', $_DATA['cust_info_no'])->WHERE('save_status', 'Y')->GET();
        if(!empty($lawno[0]->no))
        {
            $cinfo[0]->loan_info_law_no = $lawno[0]->no;
        }

        $cinfo[0]->post_zip         = Func::nvl($cinfo[0]->{"zip".$cinfo[0]->post_send_cd}, "");
        $cinfo[0]->post_addr11      = Func::nvl($cinfo[0]->{"addr".$cinfo[0]->post_send_cd."1"},"");
        $cinfo[0]->post_addr12      = Func::nvl($cinfo[0]->{"addr".$cinfo[0]->post_send_cd."2"},"");

        return view("erp.loanPrint")->with("loan_info_no", $_DATA['loan_info_no'])->with("cinfo", $cinfo[0]);
    }

    public function printAction(Request $request)
    {
        $_DATA = $request->all();

        foreach( $_DATA as $key => $val )
        {
            if( $key == "listChk" )
            {
                $_DATA['listChk'] = explode(",", $val);
            }

            if( substr($key,0,5) == "lump_" )
            {
                $_DATA[substr($key,5)] = $val;
                unset($_DATA[$key]);
            }
        }

        $loan_info_nos = [];

        $_param = [];
        $_param['post_cd']      = $_DATA['post_cd'];
        $_param['erp_ups']      = "ERP";
        $_param['lno']          = $_DATA['loan_info_no'];
        $_param['post_addr_cd'] = $_DATA['post_addr_cd'];
        $_param['basis_date']   = $_DATA['print_basis_date'];
        if(isset($_DATA['print_no']))
        {
            $_param['trade_no']   = $_DATA['print_no'];
        }
        

        $_UBI[0] = new Ubi($_param);

        if( empty($_UBI[0]->dataSet) )
        {
            return ['msg' => Func::nvl($_UBI[0]->err_msg, "우편 금지 고객입니다.")];
        }

        $loan_info_nos[] = $_DATA['loan_info_no'];
        $addr[$_DATA['loan_info_no']] = ['zip'=>$_UBI[0]->zip, 'addr1'=>$_UBI[0]->addr1, 'addr2'=>$_UBI[0]->addr2];

        $result = ["UBI" => $_UBI, "lnos" => $loan_info_nos, "addr" => $addr];

        return $result;
    }

    public function lumpPrint(Request $request)
    {
        $_DATA = $request->all();
        set_time_limit(0);
        ini_set('memory_limit','-1');

        foreach( $_DATA as $key => $val )
        {
            if( $key == "listChk" )
            {
                $_DATA['listChk'] = explode(",", $val);
            }

            if( substr($key,0,5) == "lump_" )
            {
                $_DATA[substr($key,5)] = $val;
                unset($_DATA[$key]);
            }
        }


        $save_time = date("YmdHis");

        log::info(print_r($_DATA,true));

        $errMsg = [];
        $i = 0;
        foreach($_DATA['listChk'] as $lno)
        {
            $_param = [];

            if (!empty($_DATA['div']))
            {
                if($_DATA['div'] == 'RELIEF')
                {
                    // relief에서는 relief_no를 넘기기 때문에 변환
                    $_param['relief_no'] = $lno;
                    $lno = DB::TABLE('LOAN_RELIEF')->WHERE('NO', $lno)->VALUE('LOAN_INFO_NO');
                }
                else if($_DATA['div'] == 'VISIT')
                {
                    // 집금활동명세표에서는  visit_no를 넘기기 때문에 변환
                    $lno = DB::TABLE('VISIT')->WHERE('NO', $lno)->VALUE('LOAN_INFO_NO');
                }
            }

            $_param['post_cd']      = $_DATA['post_cd'];
            $_param['erp_ups']      = "ERP";
            $_param['lno']          = $lno;
            $_param['post_addr_cd'] = $_DATA['post_addr'];
            $_param['basis_date']   = $_DATA['print_basis_date'];
            $_param['lumpYn']       = "Y";


            $_UBI[$i] = new Ubi($_param);

            $loan_info_nos[] = $lno;
            $i++;
        }

        if( empty($_UBI) )
        {
            $errstr = "";
            foreach( $errMsg as $str => $loanNos )
            {
                $errstr .= "\n".$str."\n(계약번호 : ";
                foreach( $loanNos as $loanNo )
                {
                    $errstr .= $loanNo." ";
                }
                $errstr .= ")";
            }

            return ['msg'=>"출력 가능한 고객이 없습니다.\n".$errstr];
        }

        $arrAddr = [];
        foreach($_UBI as $idx => $ubi)
        {
            $arrAddr[$ubi->loan_info_no] = ['zip'=>$ubi->zip, 'addr1'=>$ubi->addr1, 'addr2'=>$ubi->addr2];
            unset( $_UBI[$idx]->zip, $_UBI[$idx]->addr1, $_UBI[$idx]->addr2 );
            
            if($idx == 0)
            {
                continue;
            }
            
            if( !in_array($_DATA['post_cd'], Ubi::$directPrint) )
            {
                foreach( $ubi->dataSet as $key => $content )
                {
                    $_UBI[0]->dataSet[$key] .= "^n".$content;
                }

                unset($_UBI[$idx]);
            }
        }

        $result = ["UBI" => $_UBI, "lnos" => $loan_info_nos, "addr" => $arrAddr];

        log::info(print_r($result,true));
        return $result;
    }

    public function lumpAfterPrint(Request $request)
    {
        $_DATA = $request->all();

        log::info(print_r($_DATA,true));

        // 메모에 주소 넣을 양식지
        $arrayInsertAddr = [
            "1004033" =>  "독촉장",
            "1004034" =>  "독촉장(서식1-1)",
            "1004001" =>  "독촉장(내용증명)",
            "1001035" =>  "거래내역확인서",
            "1001036" =>  "대출잔고증명",
            "1001037" =>  "대출잔고증명(화해/개회)",
            "1003004" =>  "상속절차이행요청서",
            "1003005" =>  "상속절차협조문",
            "1001008" =>  "기한이익상실 예정통보서",
            "1004003" =>  "기한이익상실예정통지서(계약서미회수)",
            "1004004" =>  "법적절차착수통지서",
            "1001038" =>  "완납확인서",
            "1004047" =>  "고소장",
            "1004048" =>  "대여금청구소장",
            "1001046" =>  "신청서",
            "1004049" =>  "지급명령신청서",
            "1004050" =>  "채권가압류신청서",
            "1004051" =>  "채권압류 및 추심명령 신청",
            "1004056" =>  "강제집행신청서",
            "1001057" =>  "채권양도통지서",
            "1003003" =>  "채무조정안내문",
            "1003039" =>  "채무조정안내문",
            "1003040" =>  "채무조정안내문(화해)",
            "1004002" =>  "담보채권법적절차예정통지",
            "1003006" =>  "고발통지서(신용)",
            "1003007" =>  "고발통지서(담보)",
        ];

        $save_time = date("YmdHis");
        $save_id = Auth::id();

        $memo = '양식 출력';
        if(count($_DATA['lno'])>1)
            $memo = '일괄 양식 출력';

        return "Y";
    }

    public function printViewAction(Request $request)
    {
        $postCd = $request->postCd;

        return $postCd;
    }


}

?>