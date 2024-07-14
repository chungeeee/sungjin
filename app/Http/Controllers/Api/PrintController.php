<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;

class PrintController extends Controller
{
	/**
    * 분리보관 프린트 처리
    * 
    */
    public function print(Request $request)
    {
        return false;
        #
        #	분리보관 - 종결확인서 인쇄
        #	작성일 : 2021-08-09
        #	작성자 : boromir
        #
        //헤더
        include 'search.php';

        $_SERVER['DOCUMENT_ROOT'] = '/home/mdamc/public_html';
        include $_SERVER['DOCUMENT_ROOT'].'/importantShare/db.inc.php';
        include $_SERVER['DOCUMENT_ROOT'].'/importantShare/function.inc.php';
        include $_SERVER['DOCUMENT_ROOT'].'/importantShare/function.inc.enc.php';
        include $_SERVER['DOCUMENT_ROOT'].'/importantShare/class.record.php';
        include "/home/mdamc/code.inc/print.set.php";

        $fp = fopen('/home/mdamc_log/separate/API_PRINT_'.date('Ymd').'.log', 'a');
        fwrite($fp, '['.date('H:i:s').'][START]'."\n");
        fwrite($fp, '['.date('H:i:s').']'.print_r($_POST, true)."\n");

        $name = $_POST['NAME'];
        $ssn = $_POST['SSN'];
        $mno = $_POST['MNO'];
        $cno = $_POST['CNO'];
        $mkey = $_POST['MKEY'];
        $info_id = $_POST['INFO_ID'];
        $dept_etc = $_POST['ETC_TEXT'];
        $dept_addr = $_POST['ADDR_DIV']	;

        $v_mem = sendMemberInfo($info_id);

        unset($trade_money, $sum['trade_money'], $sum_total_money, $sum_tt_money);

        ############가장 최근 거래############
        // 입금액은 일반입금,화해입금,개인회생,신용회복,유체동산,배당금 만 반영해주세요. type in ('1','7','8','9','10','11')
        $sql_tb = "select in_name, trade_date, trade_money from trade_book where contract_info_no in (select no from contract_info where save_status='Y' and status='E' and member_key='".$mkey."') and save_status='Y' and trade_type='I' and COALESCE(over_money,0)<trade_money and type in ('1','7','8','9','10','11') order by save_time desc, trade_date desc, no desc limit 1";
        $v_tb = db_fetch_array(db_query($pg_erp, $sql_tb));
        $in_name = $v_tb['in_name'];			//입금자
        $trade_date = $v_tb['trade_date'];		//입금일
        $sql_tot="select trade_money from trade_book where contract_info_no in (select no from contract_info where save_status='Y' and status='E' and member_key='".$mkey."')  and save_status='Y' and trade_type='I' and COALESCE(over_money,0)<trade_money and type in ('1','7','8','9','10','11') and trade_date='".$v_tb['trade_date']."' ";
        $rs_tot = db_query($pg_erp, $sql_tot);
        while($v_tot = db_fetch_array($rs_tot))
        {
            $sum['trade_money']+=$v_tot['trade_money'];
        }
        $trade_money = $sum['trade_money'];//입금액
        ############가장 최근 거래############

        ############합계금액(채무총액)############
        $sql_con = "select no from contract_info where save_status='Y' and status='E' and member_key='".$mkey."'";
        $rs_con = db_query($pg_erp, $sql_con);
        while($v_con = db_fetch_array($rs_con))
        {
            $cno_arr[]=$v_con['no'];
        }
        $sum_total_money = array();
        foreach($cno_arr as $cno)
        {
            $sql_tt="select * from trade_book where contract_info_no ='".$cno."' and save_status='Y' and trade_type='I' and trade_date='".$trade_date."' ";
            $rs_tt = db_query($pg_erp, $sql_tt);
            while($v_tt = db_fetch_array($rs_tt))
            {
                $v_tt['return_pre_money']		= ($v_tt['return_pre_money'] < 0)		? 0 : $v_tt['return_pre_money'];
                $v_tt['return_miss_money']		= ($v_tt['return_miss_money'] < 0)		? 0 : $v_tt['return_miss_money'];
                $v_tt['return_rack_money']		= ($v_tt['return_rack_money'] < 0)		? 0 : $v_tt['return_rack_money'];
                $v_tt['return_delay_interest']	= ($v_tt['return_delay_interest'] < 0)	? 0 : $v_tt['return_delay_interest'];
                $v_tt['return_interest']		= ($v_tt['return_interest'] < 0)		? 0 : $v_tt['return_interest'];
                $v_tt['return_settle_interest']	= ($v_tt['return_settle_interest'] < 0) ? 0 : $v_tt['return_settle_interest'];
                $v_tt['return_origin']			= ($v_tt['return_origin'] < 0)			? 0 : $v_tt['return_origin'];
                $v_tt['lose_pre_money']			= ($v_tt['lose_pre_money'] < 0)			? 0 : $v_tt['lose_pre_money'];
                $v_tt['lose_interest']			= ($v_tt['lose_interest'] < 0)			? 0 : $v_tt['lose_interest'];
                $v_tt['lose_origin']			= ($v_tt['lose_origin'] < 0)			? 0 : $v_tt['lose_origin'];

                $sum_total_money[$v_tt['contract_info_no']]+=$v_tt['return_pre_money'];			//법비용상환
                $sum_total_money[$v_tt['contract_info_no']]+=$v_tt['return_miss_money'];		//이자
                $sum_total_money[$v_tt['contract_info_no']]+=$v_tt['return_rack_money'];		//이자
                $sum_total_money[$v_tt['contract_info_no']]+=$v_tt['return_delay_interest'];	//이자
                $sum_total_money[$v_tt['contract_info_no']]+=$v_tt['return_interest'];			//이자
                $sum_total_money[$v_tt['contract_info_no']]+=$v_tt['return_settle_interest'];	//이자
                $sum_total_money[$v_tt['contract_info_no']]+=$v_tt['return_origin'];			//원금상환
                $sum_total_money[$v_tt['contract_info_no']]+=$v_tt['lose_pre_money'];			//법비용감면
                $sum_total_money[$v_tt['contract_info_no']]+=$v_tt['lose_interest'];			//이자감면
                $sum_total_money[$v_tt['contract_info_no']]+=$v_tt['lose_origin'];				//원금감면
            }

            $res_tt = db_query($pg_erp, $sql_tt." order by no desc limit 1");	//최근납입일 기준 마지막거래
            while($v_tot = db_fetch_array($res_tt))
            {
                $v_tot['pre_money']			= ($v_tot['pre_money'] < 0)			? 0 : $v_tot['pre_money'];
                $v_tot['rack_money']		= ($v_tot['rack_money'] < 0)		? 0 : $v_tot['rack_money'];
                $v_tot['miss_money']		= ($v_tot['miss_money'] < 0)		? 0 : $v_tot['miss_money'];
                $v_tot['settle_interest']	= ($v_tot['settle_interest'] < 0)	? 0 : $v_tot['settle_interest'];
                $v_tot['tail_money']		= ($v_tot['tail_money'] < 0)		? 0 : $v_tot['tail_money'];

                $sum_total_money[$v_tot['contract_info_no']]+=$v_tot['pre_money'];				//법비용
                $sum_total_money[$v_tot['contract_info_no']]+=$v_tot['rack_money'];				//잔여이자
                $sum_total_money[$v_tot['contract_info_no']]+=$v_tot['miss_money'];				//잔여이자
                $sum_total_money[$v_tot['contract_info_no']]+=$v_tot['settle_interest'];		//잔여이자
                $sum_total_money[$v_tot['contract_info_no']]+=$v_tot['tail_money'];				//잔액
            }
        }
        ############합계금액(채무총액)############

        ############계약정보 가져오기############
        $contract_cnt = 0;
        $contract_str = "";
        $sql = "select * from contract_info where save_status='Y' and status='E' and member_key='".$mkey."' order by no";
        $rs = db_query($pg_erp, $sql);
        while($v = db_fetch_array($rs))
        {
            $contract_cnt++;
            $contract_str.="<tr>";
            $contract_str.="<td align='center' style='border:1px solid black; padding:5px; font-size:15px;'>".$v['first_agent']."</td>";
            $contract_str.="<td align='center' style='border:1px solid black; padding:5px; font-size:15px;'>".$v['convert_pro_name']."</td>";
            $contract_str.="<td align='center' style='border:1px solid black; padding:5px; font-size:15px;'>".$v['convert_no']."</td>";
            $contract_str.="<td align='center' style='border:1px solid black; padding:5px; font-size:15px;'>".$v['contract_date']."</td>";
            $contract_str.="<td align ='right' style='border:1px solid black; padding:5px; font-size:15px;'>".number_format($sum_total_money[$v['no']])."</td>";

            $contract_str.="</tr>";

            $sum_tt_money+= $sum_total_money[$v['no']];
        }
        $remain_con_cnt = (7-$contract_cnt);
        if($contract_cnt < 7)
        {
            for($t=0; $t<$remain_con_cnt; $t=$t+1)
            {
                $contract_str.="<tr>";
                $contract_str.="<td style='border:1px solid black;'>&nbsp;</td>";
                $contract_str.="<td style='border:1px solid black;'>&nbsp;</td>";
                $contract_str.="<td style='border:1px solid black;'>&nbsp;</td>";
                $contract_str.="<td style='border:1px solid black;'>&nbsp;</td>";
                $contract_str.="<td style='border:1px solid black;'>&nbsp;</td>";
                $contract_str.="</tr>";
            }
        }
        ############계약정보 가져오기############

        ############불명금 가져오기############
        if($_POST['not_flag'] == 'Y')
        {
            $sql_con = "SELECT * FROM not_found_money WHERE save_status = 'Y' AND reg_cno = ".$cno." and status = 'A' and trade_date = '".$trade_date."' order by trade_date desc";
            $v = db_query($pg_erp, $sql_con);
            while($v = db_fetch_array($v))
            {
                $trade_money = $v['trade_money'] + $trade_money;
            }
        }
        ############불명금 가져오기############

        ############불명금 가져오기############
        if($v_mem['sender_flag']== $accept_code_s || $v_mem['sender_flag']== $accept_code_m || $v_mem['sender_flag']== $accept_code_k)	// SM
        {
            $first_bc_string =
            '
            <table align=center style="margin-top:40px;">
                <tr align=center >
                    <td>
                        <table width="395px">
                            <tr>
                                <td style="font-size:13pt;padding-left:30px; font-weight:900; letter-spacing:0.1px;">채권자 : '.$v_mem['origin_company'].'</td>
                                <td width="0"><img src="'.$v_mem['origin_company_img_01'].'" style="width:50px; margin-left:0px;" border="0"></td>
                            </tr>
                        </table>
                        <table width="320px">
                            <tr>	
                                <td style="font-size:13pt; font-weight:900; letter-spacing:0.1px;">수임사 : '.$v_mem['accept_company'].'</td>
                                <td width="0"><img src="'.$v_mem['accept_company_img_01'].'" style="width:50px; margin-left:0px;" border="0"></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            ';
        }
        else if($v_mem['sender_flag']== $origin_code)	// 모두론
        {
            $first_bc_string =
            '
            <table align=center style="margin-top:50px;">
                <tr align=center >
                    <td>
                        <table width="300px">
                            <tr>
                                <td style="font-size:13pt;padding-left:30px; font-weight:900; letter-spacing:0.1px;">'.$v_mem['origin_company'].'</td>
                                <td width="0"><img src="'.$v_mem['origin_company_img_01'].'" style="width:60px; margin-left:0px;" border="0"></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            ';
        }
        unset($_POST['trade_money']);
        $_POST['trade_date'] = $trade_date;
        $_POST['trade_money'] = number_format($trade_money);
        $_POST['in_name'] = $in_name;
        $_POST['sender_ph'] = $v_mem['sender_branch_ph'];
        $_POST['sender_branch_fax'] = $v_mem['sender_branch_fax'];
        $_POST['sender_branch_addr'] = $v_mem['sender_branch_addr'];

        if($_POST['INPUT_MONEY']!="") $_POST['trade_money'] = number_format($_POST['INPUT_MONEY']);
        if($_POST['INPUT_DATE']!="") $_POST['trade_date'] = $_POST['INPUT_DATE'];

        $string = '
        <html>
        <head>
            <meta http-equiv=Content-Type content="text/html; charset=UTF-8">
            <META NAME="Description" CONTENT="채무종결확인서">
            <style>
                table, figure, .sector {
                page-break-inside: avoid;
                }
                body { font-family: "Nanum Gothic", sans-serif; }
            </style>
        </head>

        <body>

        <div class="sector">
            <table style="BORDER-COLLAPSE:collapse; margin-top:15px;" cellSpacing=0 cellPadding=0 width="100%">
                <tr>
                    <td style="font-size:9pt;">(문서번호 : '.$mno.')</td>
                </tr>
                <tr>
                    <td align="center">
                        <table align="center" style="margin-top:5px; width:450px; border:1px solid black;">
                            <tr>
                                <td align="center" style=" font-size:22pt; padding:5px;"><b>채&nbsp;&nbsp;무&nbsp;&nbsp;종&nbsp;&nbsp;결&nbsp;&nbsp;확&nbsp;&nbsp;인&nbsp;&nbsp;서</b></td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding-top:20px; font-size:9pt;">□ 고객사항</td>
                </tr>
                <tr>
                    <td>
                        <table width=100% style="BORDER-COLLAPSE: collapse" cellSpacing=0 cellPadding=0 >
                            <tr>
                                <td width="14%" bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">성 명</td>
                                <td width="30%" style="text-align:left;padding:3px;padding-left:8px;border:1px solid black;font-size:9pt;">'.$name.'</td>
                                <td width="20%" bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">주민등록번호</td>
                                <td style="text-align:left;padding:3px;padding-left:8px;border:1px solid black;font-size:9pt;">'.$ssn.'</td>
                            </tr>
                            <tr>
                                <td width="14%" bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">주 소</td>
                                <td colspan="3" style="padding:3px;padding-left:8px;text-align:left;border:1px solid black;font-size:9pt;">'.$dept_addr.'</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding-top:20px;">
                        <table width=100% style="BORDER-COLLAPSE: collapse" cellSpacing=0 cellPadding=0 >
                            <tr>
                                <td width="12%" style="font-size:9pt;">□ 채무내역	</td>
                                <td width="76%" style="font-size:9pt;letter-spacing:-1px;">[&nbsp기준일 : '.date('Y년 m월 d일').'('.$weekday.')&nbsp]</td>
                                <td width="12%" align="right" style="font-size:9pt;">(단위 : 원)</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td>
                        <table width=100% style="BORDER-COLLAPSE: collapse" cellSpacing=0 cellPadding=0 >
                            <tr>
                                <td width="25%" bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">최초대출기관</td>
                                <td width="25%" bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">계 정 과 목</td>
                                <td width="20%" bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">계 좌 번 호</td>
                                <td width="15%" bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">대출실행일</td>
                                <td width="15%" bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">채 무 총 액</td>
                            </tr>
                            '.$contract_str.'
                            <tr>
                                <td colspan="4" align="right" style="border:1px solid black; padding:5px;">전&nbsp;체&nbsp;합&nbsp;계</td>
                                <td align="right" style="border:1px solid black; padding:5px;">'.number_format($sum_tt_money).'</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding-top:20px;font-size:9pt;"><u>상기 채무에 대하여 아래와 같이 변제하여 채권, 채무관계가 종결되었음을 증명합니다.</u></td>
                </tr>
                <tr>
                    <td style="padding-top:20px; font-size:9pt;">□ 변제내역</td>
                </tr>
                <tr>
                    <td>
                        <table width=100% style="BORDER-COLLAPSE: collapse" cellSpacing=0 cellPadding=0 >
                            <tr>
                                <td width="30%" bgcolor="#E6E6E6" align="center" style="background:#E6E6E6 !important; padding:3px;border:1px solid black;font-size:9pt;">입&nbsp;금&nbsp;구&nbsp;분</td>
                                <td bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">내&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;용</td>
                            </tr>
                            <tr>
                                <td width="30%" bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">입&nbsp;&nbsp;&nbsp;&nbsp;금&nbsp;&nbsp;&nbsp;&nbsp;일</td>
                                <td align="left" style="padding:3px;border:1px solid black;font-size:9pt;padding-left:10px;">'.$_POST['trade_date'].'</td>
                            </tr>
                            <tr>
                                <td width="30%" bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">입&nbsp;&nbsp;&nbsp;&nbsp;금&nbsp;&nbsp;&nbsp;&nbsp;액</td>
                                <td align="left" style="padding:3px;border:1px solid black;font-size:9pt;padding-left:10px;">'.$_POST['trade_money'].'</td>
                            </tr>
                            <tr>
                                <td width="30%" bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">입&nbsp;&nbsp;&nbsp;&nbsp;금&nbsp;&nbsp;&nbsp;&nbsp;자</td>
                                <td align="left" style="padding:3px;border:1px solid black;font-size:9pt;padding-left:10px;">'.$_POST['in_name'].'</td>
                            </tr>
                            <tr>
                                <td width="30%" bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">입&nbsp;금&nbsp;은&nbsp;행</td>
                                <td align="left" style="padding:3px;border:1px solid black;font-size:9pt;padding-left:10px;">'.get_virtual_account($mno, $cno).'</td>
                            </tr>
                            <tr>
                                <td width="30%" bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">기&nbsp;타&nbsp;내&nbsp;용</td>
                                <td align="left" style="padding:3px;border:1px solid black;font-size:9pt;padding-left:10px;">'.$dept_etc.'</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td style="padding-top:70px; font-size:10pt;">□ 담당사항</td>
                </tr>
                <tr>
                    <td>
                        <table width=100% style="BORDER-COLLAPSE: collapse" cellSpacing=0 cellPadding=0 >
                            <tr>						
                                <td width="20%" bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">전&nbsp;&nbsp;화</td>
                                <td align="left" style="padding:3px;border:1px solid black;font-size:9pt;padding-left:5px;">'.$_POST['sender_ph'].'</td>
                                <td width="20%" bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">팩&nbsp;&nbsp;스</td>
                                <td align="left" style="padding:3px;border:1px solid black;font-size:9pt;padding-left:5px;">'.$_POST['sender_branch_fax'].'</td>
                            </tr>
                            <tr>
                                <td width="20%" bgcolor="#E6E6E6" align="center" style="padding:3px;border:1px solid black;font-size:9pt;">주&nbsp;&nbsp;소</td>
                                <td align="left" colspan="3" style="padding:3px;border:1px solid black;font-size:9pt;padding-left:5px;">'.$_POST['sender_branch_addr'].'</td>
                            </tr>
                        </table>
                    </td>
                </tr>
                <tr>
                    <td align="center" style="padding-top:20px;font-size:10pt;"><u>'.date('Y년 m월 d일').'</u></td>
                </tr>
                <tr>
                    <td>
                        '.$first_bc_string.'
                    </td>
                </tr>
                <tr>
                    <td>
                        <table width="100%" style="border:none; border-top:2px solid #a4a4a4; margin-top:20px;">
                            <tr>
                                <td width="30%" style="font-size:7pt; border:none; text-align:left">'.$v_mem['sender_company'].'</td>
                                <td width="30%" style="font-size:7pt; border:none; text-align:center">1 of 1</td>
                                <td width="30%" style="font-size:7pt; border:none; text-align:right">인쇄자 : '.$v_mem['sender'].'&nbsp&nbsp&nbsp&nbsp인쇄일 :'.date('Y년 m월 d일').'</td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
        </div>

        </body>
        </html>
        ';

        echo json_encode($string);

        fwrite($fp, '['.date('H:i:s').'][END]'."\n");
        fclose($fp);
    }
}
?>