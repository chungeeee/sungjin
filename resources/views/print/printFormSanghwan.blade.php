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
	<a href="javascript:printOk(<?//$zoom_ratio?>);" style="color:black"><i class="fas fa-print p-1 text text-xs"></i>인쇄하기</a>
</td>
</tr>
</table>
<br> 
<table width="100%" cellspacing=10 cellpadding=0 bgcolor="FFFFFF" align=center border=0>
    <tr>
        <td>
            <table width="100%" cellspacing=1 cellpadding=1 bgcolor="000000" style="margin-top:3px;margin-bottom:3px;">
                <tr bgcolor="FFFFFF" align=center>
                    <td bgcolor="C0C0C0" width="25%">고객명</td>
                    <td width="25%"><?=$data['name']?></td>
                    <td bgcolor="C0C0C0" width="25%">차입자번호</td>
                    <td width="25%"><?=$data['cust_info_no']?></td>
                </tr>
            </table>
            
            <table width="100%" cellspacing=1 cellpadding=1 bgcolor="000000" style="margin-top:3px;margin-bottom:3px;">
                <tr bgcolor="C0C0C0" align=center>
                    <td colspan=7><b><?=$data['name']?></b> 님의 상환스케줄표</td>
                </tr>
                <tr bgcolor=C0C0C0 align=center>
                    <td>회차</td>
                    <td>날짜</td>
                    <td>금액</td>
                    <td>발생이자</td>
                    <td>이자상환</td>
                    <td>원금상환</td>
                    <td>대출잔액</td>
                </tr>
                <tr bgcolor="FFFFFF" height=19 align=center>
                    <td bgcolor="C0C0C0">대출</td>
                    <td><?=$data['last_trade_date']?></td>
                    <td align=right><?=number_format($data['balance'])?></td>
                    <td></td>
                    <td></td>
                    <td></td>
                    <td align=right><?=number_format($data['balance'])?></td>
                </tr>

                <tr bgcolor="FFFFFF" height=19 align=center>
                    <td><?=$cnt?></td>
                    <td><?=isset($data['trade_date']) ? $data['trade_date'] : 0 ?></td>
                    <td align=right><?=isset($data['trade_money']) ? number_format($data['trade_money']) : 0?></td>
                    <td align=right><?=isset($data['interest_sum']) ? number_format($data['interest_sum']) : 0?></td>
                    <td align=right><?=isset($data['return_interest']) ? number_format($data['return_interest']) : 0?></td>
                    <td align=right><?=isset($data['return_origin']) ? number_format($data['return_origin']) : 0?></td>
                    <td align=right><?=isset($data['balance']) ? number_format($data['balance']) : 0?></td>
                </tr>

                <tr bgcolor="FFFFFF" height=19 align=center>
                    <td colspan=2 bgcolor="C0C0C0">합계</td>
                    <td align=right><?number_format($sum['trade_money'])?></td>
                    <td align=right><?number_format($sum['interest_sum'])?></td>
                    <td align=right><?number_format($sum['return_interest'])?></td>
                    <td align=right><?number_format($sum['return_origin'])?></td>
                    <td align=right>-</td>
                </tr>
                <tr bgcolor="FFFFFF" height=19 align=center>
                    <td colspan=7><br/>	* 본 상환계획은 실제 납입금액 및 납입일에 따라서 달라질 수 있으며, 단, 거래가 종료되는 시점에 잔여 원금과 이자를 정산합니다.<br/><br/></td>
                </tr>
            </table>
        </td>
    </tr>
</table>
</form>
<iframe name="printFrm" frameborder=0 style="display:none;"></iframe>
@endsection

@section('javascript')

<!--
<script src="/plugins/moment/moment.min.js"></script>
<script src="/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.js"></script>
-->
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
