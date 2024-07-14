@extends('layouts.masterPop')
@section('content')
<?
$u_agent = $_SERVER['HTTP_USER_AGENT'];  //  들어온사람의 브라우저를 변수 $u_agent 에 담아서
$zoom_ratio = "85";

?>
<object id="factory" viewastext style="display:none" classid="clsid:1663ed61-23eb-11d2-b92f-008048fdd814" codebase="smsx.cab#Version=7.3.0.22"></object>
<form class="form-horizontal" name="trade_out_form" id="trade_out_form">
<table width="100%" border="0" cellspacing="1" cellpadding="1" id='header'>
<tr>
<td height="30" align=right style="padding-right:5px;filter:alpha(opacity=70)" bgcolor="#AAAAAA">
	<a href="javascript:printOk(<?=$zoom_ratio?>);" style="color:black"><i class="fas fa-print p-1 text text-xs"></i>인쇄하기</a>
</td>
</tr>
</table>
<br> 
<div style="zoom:80%">
<table cellSpacing=0 cellPadding=0  border=0 width=99% align=center>
    <tr>
        <td>
            <br><br>
            
            <table width="100%" cellspacing=0 cellpadding=0 bgcolor="FFFFFF" align=center border=0>
                <tr>
                    <td><font size=3><b><?=$data['cust_info_no']?>. <?=$data['name']?> <?=substr($data['ssn'], 0, 6)?>-<?=substr($data['ssn'], 7)?> 보유 계약리스트</b></font></td>
                </tr>
            </table>

            <table cellpadding=0 cellspacing=0 bgcolor="000000" width="100%">
                <tr>
                    <td>
                        <table border="1" cellpadding=0 cellspacing=1 bgcolor="000000" width="100%">
                            <tr bgcolor=EEEEEE height=25 align=center>
                                <td>계약No</td>
                                <td>부서</td>
                                <td>담당</td>
                                <td>상태</td>
                                <td>약정</td>
                                <td>이율</td>
                                <td>유형</td>
                                <td>상환방법</td>
                                <td>계약일</td>
                                <td>최초대출</td>
                                <td>한도액</td>
                                <td>월상환</td>
                                <td>최근거래일</td>
                                <td>상환일</td>
                                <td>미수비용</td>
                                <td>총이자</td>
                                <td>당일청구금액</td>
                                <td>잔액</td>
                                <td>매입원가</td>
                                <td>잔여원가</td>
                            </tr>
                        
                            <tr <?=Func::trColor($data['bgcolor2'],$data['bgcolor'])?> height=25 align=center bgcolor="FFFFFF">
                                <td><?=$data['no']?></td>
                                <td><?=isset(Func::myPermitBranch()[$data['manager_code']]) ? Func::myPermitBranch()[$data['manager_code']] : ''?></td>
                                <td><?=isset(Func::getUserList($data['manager_id'])->name) ? Func::getUserList($data['manager_id'])->name : ''?></td>
                                <td><?=Vars::$arrayContractSta[$data['status']]?></td>
                                <td><?=$data['contract_day']?></td>
                                <td><?=$data['loan_rate']?>/<?=$data['loan_delay_rate']?></td>
                                <td><?=$data['loan_type']?></td>
                                <td><?=Func::nvl($array_return_method[$data['return_method_cd']], '')?></td>
                                <td><?=Func::dateFormat(substr($data['contract_date'],2), '-')?></td>
                                <td align=right style="padding-right:3px"><?=Func::numberFormat($data['first_loan_money'])?></td>
                                <td align=right style="padding-right:3px"><?=Func::numberFormat($data['limit_money'])?></td>
                                <td align=right style="padding-right:3px"><?=Func::numberFormat($data['monthly_return_money'])?></td>
                                <td><?=Func::dateFormat(substr($data['last_trade_date'],2), '-')?></td>
                                <td><?=Func::dateFormat(substr($data['return_date'],2), '-')?></td>
                                <td align=right style="padding-right:3px"><?=Func::numberFormat($data['misu_money'])?></td>
                                <td align=right style="padding-right:3px" title="<?=Func::getInterestTitle($data)?>"><?=Func::numberFormat($data['interest_sum'])?></td>
                                <td align=right style="padding-right:3px"><?=Func::numberFormat($data['charge_money'])?></td>
                                <td align=right style="padding-right:3px"><?=Func::numberFormat($data['balance'])?></td>
                                <td align=right style="padding-right:3px"><?=Func::numberFormat($data['base_cost'])?></td>
                                <td align=right style="padding-right:3px"><?//Func::numberFormat($data['now_cost'])?></td>
                            </tr>
                        </table>
                    </td>
                </tr>
            </table>
            </br>
        </td>
    <tr valign=top>
        <td>
            <table width="100%" cellspacing=1 cellpadding=1 bgcolor="FFFFFF">
                <tr valign=bottom>
                    <td><?=$data['no']?>번 계약 거래원장</td>
                </tr>
            </table>

            <table border="1" width="100%" cellspacing=1 cellpadding=1 bgcolor="666666" style="margin-top:3px;margin-bottom:10px;" id="T<?=$data['no']?>">
                <tr bgcolor="EEEEEE" height=20 align=center>
                    <td></td>
                    <td>형식<br>경로</td>
                    <td>거래일</td>
                    <td>이율</td>
                    <td>경과</td>
                    <td>정상이자<br>연체이자</td>
                    <td>입출금액</td>
                    <td>비용상환<br>비용감면</td>
                    <td>이자상환<br>이자감면</td>
                    <td>원금상환<br>원금감면</td>
                    <td>잔액<br>화해이자</td>
                    <td>부족금<br>미수금</td>
                    <td>비용<br>가수금</td>
                    <td>원가상환<br>원가수익</td>
                    <td>잔여원가</td>
                    <td>다음상환일<br>월상환부족</td>
                    <td>작업자<br>저장일</td>
                    <td>삭제자<br>삭제일</td>
                    <td>관리점<br>담당자</td>
                </tr>
                <? 
                    if(!empty($data['trade'])) { 
                        foreach($data['trade'] as $num =>$vt){
                ?>
                <!-- <tr <? //Func::trColor($data['bgcolor2'], $data['bgcolor1'])?> height=20 align=center style="display:<?//$data['tr_display']?>" bgcolor="FFFFFF" status="<?//$data['save_status']?>"> -->
                <? if($vt->trade_div == 'O'){ ?>
                <tr <?=Func::trColor($data['out']['bgcolor2'], $data['out']['bgcolor1'])?> height=20 align=center style="display:<?=$data['tr_display']?>" status="<?=$data['save_status']?>">
                <? } else { ?>
                <tr <?=Func::trColor($data['in']['bgcolor2'], $data['in']['bgcolor1'])?> height=20 align=center style="display:<?=$data['tr_display']?>" status="<?=$data['save_status']?>">
                <?}?>
                    <td align=center><?=isset(Vars::$arrayTradeDiv[$vt->trade_div]) ? Vars::$arrayTradeDiv[$vt->trade_div] : ''?><?=$data['del_title']?></td>
                    <? if($vt->trade_div == "I") {?>
                    <td align=center><?=$tradeInType[$vt->trade_type] ?? ''?><br><?=$tradeInPath[$vt->trade_path_cd] ?? ''?></td>
                    <?} else if($vt->trade_div == "O") {?>
                    <td align=center><?=$tradeOutType[$vt->trade_type] ?? ''?><br><?$tradeOutPath[$vt->trade_path_cd] ?? ''?></td>
                    <?}?>
                    <td align=center><?$data['strike']?><?=Func::dateFormat(substr($vt->trade_date, 2), '-')?><br><?=$data['delay_text']?></td>
                    <td align=center><?=$data['loan_rate']?><br><?=$data['loan_delay_rate']?></td>
                    <td align=center><?=$vt->interest_term?>/<?=$vt->delay_interest_term?><br><?=$vt->delay_term?></td>
                    <td align=right><?=$data['strike']?><?=Func::numberFormat($vt->interest)?><br><?=Func::numberFormat($vt->delay_interest)?></td>
                    <td align=right><?=$data['strike']?><?=Func::numberFormat($vt->trade_money)?></td>
                    <td align=right><?=$data['strike']?><?=Func::numberFormat($vt->return_cost_money)?><br><?=Func::numberFormat($vt->lose_cost_money)?></td>
                    <td align=right><?=$data['strike']?><a href="#" title="<?=$return_interest_title?>"><?=Func::numberFormat($vt->return_interest_sum)?></a><br><?=Func::numberFormat($vt->lose_interest)?></td>
                    <td align=right><?=$data['strike']?><?=Func::numberFormat($vt->return_origin)?><br><?=Func::numberFormat($vt->lose_origin)?></td>
                    <td align=right><?=$data['strike']?><?=Func::numberFormat($vt->balance)?><br><?=Func::numberFormat($vt->settle_interest)?></td>
                    <td align=right><?=$data['strike']?><?=Func::numberFormat($vt->lack_delay_money)?><br><?=Func::numberFormat($vt->misu_money)?></td>
                    <td align=right><?=$data['strike']?><?//Func::numberFormat($data['pre_money'])?><br><?=Func::numberFormat($vt->over_money)?></td>
                    <td align=right><?=$data['strike']?><?=Func::numberFormat($vt->cost_money)?><br><?//Func::numberFormat($data['profit_money'])?></td>
                    <td align=right><?=$data['strike']?><?//Func::numberFormat($data['now_cost'])?></td>
                    <td><?=$data['strike']?><?=Func::dateFormat(substr($vt->return_date, 2), '-')?><br><?=Func::numberFormat($vt->lack_delay_money)?></td>
                    <td>
                        <?=isset(Func::getUserList($vt->save_id)->name) ? Func::getUserList($vt->save_id)->name : ''; ?>
                        <br>
                        <?=substr($vt->save_time, 4, 2).'-'.substr($vt->save_time, 6,2).' '.substr($vt->save_time, 8,2).':'.substr($vt->save_time, 10,2).':'.substr($vt->save_time, 12,2)?>
                    </td>
                    <td><?=$vt->del_id?><br><?=$vt->del_time ? date("m-d H:i", $vt->del_time) : "" ; ?>	</td>
                    <td><?=isset(Func::myPermitBranch()[$vt->manager_code]) ? Func::myPermitBranch()[$vt->manager_code] : ''?><br>
                    <?=isset(Func::getUserList($vt->manager_id)->name) ? Func::getUserList($vt->manager_id)->name : ''?></td>
                </tr>
                    <?}
                }?>
                <? if( $vt->trade_type == "I" && $data['settle_no']) { ?>
                <tr align=center style="display:<?=$data['tr_display']?>" bgcolor="FFFFFF" status="<?=$data['save_status']?>">
                    <td colspan=20><?=$data['strike']?>총 <?=Func::numberFormat($data['settle_money'])?>원, <?=$data['settle_cnt']?> 분할 화해 시작</td>
                </tr>
                <?}?>
                <tr bgcolor="EEEEEE" height=20 align=center>
                    <td colspan=2>상환합계</td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <!-- <td align=right><?//Func::numberFormat($sum['return_pre_money']+$sum['return_interest_sum']+$sum['return_origin'])?></td> -->
                    <td align=right><?=Func::numberFormat(+$sum['return_interest_sum']+$sum['return_origin'])?></td>
                    <td align=right><?//Func::numberFormat($sum['return_pre_money'])?><br><?=Func::numberFormat($sum['lose_pre_money'])?></td>
                    <td align=right><?=Func::numberFormat($sum['return_interest_sum'])?><br><?=Func::numberFormat($sum['lose_interest'])?></td>
                    <td align=right><?=Func::numberFormat($sum['return_origin'])?><br><?=Func::numberFormat($sum['lose_origin'])?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td align=right><?//Func::numberFormat($sum[cost_money])?><br><?//Func::numberFormat($sum[profit_money])?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
                <? if(!empty($array_settle_plan)) { 
                    $c = 0;
                    foreach( $array_settle_plan as $s_trade_date => $vs )
                    {
                        if($c)
                        {
                            $td_class = "";
                            $rtd_class = "class='rtd'";
                        }
                        else
                        {
                            $td_class = "class='ttd'";
                            $rtd_class = "class='rttd'";
                        }   
                ?>
                <tr bgcolor="CCCCCC" height=25>
                    <? if(!$c) { ?>
                    <td class="ltbtd" colspan=2 align=center>화해스케줄</td>
                    <?}?>
                    <td <?=$td_class?> align=center><?=$s_trade_date?></td>
                    <td <?=$td_class?> align=center></td>
                    <td <?=$td_class?> align=center><?=$vs['seq']?>회</td>
                    <td <?=$td_class?> align=right><?=Func::numberFormat($vs['money'])?></td>
                    <td <?=$td_class?> align=right><?=Func::numberFormat($vs['in_money'])?></td>
                    <td <?=$td_class?> align=center colspan=3><?=($vs['money']==$vs['in_money']) ? "<font color=''>完</font>" : "" ; ?></td>
                    <td <?=$td_class?> align=right><?=Func::numberFormat($vs['money']-$vs['in_money'])?></td>
                    <td <?=$rtd_class?> align=center colspan=9></td>
                    <?
                        $c++;
                        $remain_settle_money+= $vs['money']-$vs['in_money'];
                        }
                    ?>
                    <td class="ltbtd" rowspan=2 colspan=2 align=center>화해스케줄</td>
                    <td align=center></td>
                    <td align=center></td>
                    <td align=center></td>
                    <td align=center></td>
                    <td align=center></td>
                    <td align=center></td>
                    <td align=center></td>
                    <td colspan=10 align=center></td>
                </tr>
                <tr bgcolor="666666" height=25 style="color:white">
                    <td align=center>합계</td>
                    <td align=center></td>
                    <td align=center></td>
                    <td align=right><?=Func::numberFormat($data['total_settle_money'])?></td>
                    <td align=right><?=Func::numberFormat($data['return_settle_money'])?></td>
                    <td align=center colspan=3></td>
                    <td align=right><?=Func::numberFormat($data['remain_settle_money'])?></td>
                    <td align=center colspan=9>실행율 : <?=percentReport($data['return_settle_money'], $data['total_settle_money'])?></td>
                </tr>
                <?}?>
            </table>
        </td>
    <tr>
        <br><br>
        <td align=right>
        </td>
        </tr>
</table>
</div>

</form>
<iframe name="printFrm" frameborder=0 style="display:none;"></iframe>
@endsection

@section('javascript')


<style>
  #header { position:absolute; top:0px; z-index:99; }
</style>
<script>
// 메뉴 고정 스크립트
function scrollPage()
{
  document.all.header.style.pixelTop = document.body.scrollTop;
}

function printOk(p)
{
  var form_zoom = p;
  var agt = navigator.userAgent.toLowerCase();
  if( agt.indexOf("chrome") != -1 ) {
      //var popupwindow = window.open("","PRINT","height=1500, width=1000");
      printFrm.document.write("<style type='text/css' media='print'>"+
                                  "@page {"+
                                      "size: auto;  /* auto is the initial value */"+
                                      "margin: 0mm 0mm 0mm 0mm;  /* this affects the margin in the printer settings */"+
                                  "}"+
                                  "body { "+
                                    "zoom: "+form_zoom+"%; "+		//	배율 조정
                                    "background-color: red; "+
                                  "}"+
                                "</style>");
      var $printarea = $('form');
      
      if( $printarea.html().indexOf("<html>") != -1 ){
        printFrm.document.write( $printarea.html() );
        printFrm.document.getElementById('header').display="none";
        printFrm.document.all.header.style.display = "none";
      }else {
        printFrm.document.write('<body>');
        printFrm.document.write( $printarea.html() );
        printFrm.document.write('</body>');
        printFrm.document.getElementById('header').display="none";
        printFrm.document.all.header.style.display = "none";
      }

      printFrm.document.close();
      printFrm.focus();
      printFrm.print();
      printFrm.close();
  }else{
    document.getElementById('header').display="none";
    document.all.header.style.display = "none";
    factory.printing.Print(false, window);
    document.getElementById('header').display="";
    document.all.header.style.display = "";
  }
}
/*
function printSet()
{
  factory.printing.header = "";
  factory.printing.footer = "";
  factory.printing.leftMargin = 0;
  factory.printing.rightMargin = 0;
  factory.printing.topMargin = 0;
  factory.printing.bottomMargin = 0;
}
*/
$('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
	    useCurrent: false,
});
setInputMask('class', 'moneyformat', 'money');


// 엔터막기
function enterClear()
{
    $('#search_string').keydown(function() {
      if (event.keyCode === 13)
      {
        event.preventDefault();
        searchLoanInfo();
      };
    });
}
enterClear();
</script>

<script>  window.setInterval('scrollPage()',1); /*printSet(); */</script>
@endsection
