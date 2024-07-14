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
<?
if(isset($data['loan_info_no']))
{
  if(is_array($data['loan_info_no']))
  {
    foreach($data['loan_info_no'] as $no)
    { 
      if($data['post_cd']=="100120" && isset($data['trade_no']))
      {
        echo PaperPrint::msgParser($no, ($data['loan_info_law_no'] ?? ''), $data['printForm'], $data['div'], $data['trade_no']."-".$data['table_type'], "59", array(), $data['post_cd']);
      }
      // 사모사채 양도양수 계약서일 경우
      else if($data['post_cd']=="SM003")
      {
        echo PaperPrint::msgParser($no, ($data['loan_info_no'] ?? ''), $data['printForm'], $data['div'], null, "59", $data, $data['post_cd']);
      }
      // 투자자양식은 파일명이 숫자형태가 아니다..
      else if(!is_numeric($data['post_cd']))
      {
        echo PaperPrint::msgParser($no, ($data['loan_info_no'] ?? ''), $data['printForm'], $data['div'], null, "59", $data, $data['post_cd']);
      }
      else
      {
        echo PaperPrint::msgParser($no, ($data['loan_info_law_no'] ?? ''), $data['printForm'], $data['div'], null, "59", array(), $data['post_cd']);
      }
    }
  }
  else
  {
    echo PaperPrint::msgParser($data['loan_info_no'], ($data['loan_info_law_no'] ?? ''), $data['printForm'], $data['div'], null, "59", array()), $data['post_cd'];   
  }
}
else if(isset($data['loan_app_no']))
{
  echo PaperPrint::msgParser($data['loan_app_no'], '', $data['printForm'], $data['div'], null, "59", array());   
}
?>
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
  // document.all.header.style.pixelTop = document.body.scrollTop;
}

$(document).ready(function (){
    var creditorName       = $('.paper-form-creditor');
    var creditorNameBottom = $('.paper-form-creditor-bottom');
    var maxFontSize        = 16;

    creditorName.each(function(){
        var nameLength = $(this).text().length;

        if(nameLength > 25){
          var fontSize = 10;

          $(this).css('font-size', fontSize + 'pt');
        }
    });

    creditorNameBottom.each(function(){
        var nameLength = $(this).text().length;

        if(nameLength > 19){
          var fontSize = 10;

          $(this).css('font-size', fontSize + 'pt');
        }
    });

    var collateralAddress1 = $('.paper-form-collateral-address1');
    var collateralAddress2 = $('.paper-form-collateral-address2');

    if(collateralAddress1.text().length < 1){
      var address = collateralAddress2.text();
      collateralAddress1.text(address);
      collateralAddress2.parent().parent().remove();
    }
});

function printOk(p)
{
  var form_zoom = p;
  var agt = navigator.userAgent.toLowerCase();
  if( agt.indexOf("chrome") != -1 ) {
      //var popupwindow = window.open("","PRINT","height=1500, width=1000");
      printFrm.document.write("<style type='text/css' media='print'>"+
                                  "@media print {" 	+
                                    "html, body {"+
                                          "height:100vh; "+
                                        "}"+
                                    "foot_table {"+
                                        "background-color: #F3EBD4 !important;"+
                                        "-webkit-print-color-adjust:exact;"+
                                      "}"+
                                  "}"+
                                  "@page {"+
                                      "size:21cm 29.7cm; /* A4 사이즈 */"+
                                      "margin : 4.3mm;"+
                                      "page-break-before: avoid;"+
                                  "}"+
                                  "body { "+
                                    "zoom: "+form_zoom+"%; "+		//	배율 조정
                                    "}"+
                                  "div.wrapper { "+
                                    "page-break-before: avoid;"+
                                    "}"+
                                "</style>");
      var $printarea = $('form');

      var cnt = 0;
      while(eval("document.getElementsByName('hpinupt')["+cnt+"]"))	//inputbox 테두리 스타일 없애기
      {
        eval("document.getElementsByName('hpval')["+cnt+"].innerText = document.getElementsByName('hpinupt')["+cnt+"].value");
        // #####
        eval("document.getElementsByName('hpval')["+cnt+"].style.display = ''");
        // #####
        eval("document.getElementsByName('hpinupt')["+cnt+"].style.display = 'none'");
        cnt++;
      }

		  cnt = 0;
      while(eval("document.getElementsByName('txt')["+cnt+"]"))	//inputbox 테두리 스타일 없애기
      {
        eval("document.getElementsByName('txt')["+cnt+"].className = 'blur_txt'");
        cnt++;
      }
      
      cnt=0;
      while(eval("document.getElementsByName('txt1')["+cnt+"]"))
      {              
        eval("document.getElementsByName('val1')["+cnt+"].innerText = document.getElementsByName('txt1')["+cnt+"].value");
        eval("document.getElementsByName('val2')["+cnt+"].innerText = document.getElementsByName('txt2')["+cnt+"].value");
        eval("document.getElementsByName('val3')["+cnt+"].innerText = document.getElementsByName('txt3')["+cnt+"].value");
        eval("document.getElementsByName('val4')["+cnt+"].innerText = document.getElementsByName('txt4')["+cnt+"].value");
        eval("document.getElementsByName('val5')["+cnt+"].innerText = document.getElementsByName('txt5')["+cnt+"].value");
        eval("document.getElementsByName('val6')["+cnt+"].innerText = document.getElementsByName('txt6')["+cnt+"].value");
        // #####
        eval("document.getElementsByName('val1')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('val2')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('val3')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('val4')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('val5')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('val6')["+cnt+"].style.display = ''");
        // #####
        eval("document.getElementsByName('txt1')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('txt2')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('txt3')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('txt4')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('txt5')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('txt6')["+cnt+"].style.display = 'none'");

        cnt++;
      }
      
      <?
        for( $i=0; $i<sizeof($data['loan_info_no']); $i++)
        {
          $cno = $data['loan_info_no'][$i];?>

          if(document.getElementById("ymdset_<?=$cno?>"))
          {
            document.getElementById("ymdset_<?=$cno?>").style.paddingLeft = '100px';
          }

          cnt=0;
          while(eval("document.getElementsByName('sdate1_<?=$cno?>')["+cnt+"]"))
          {              
            eval("document.getElementsByName('kdate1_<?=$cno?>')["+cnt+"].innerText = document.getElementsByName('sdate1_<?=$cno?>')["+cnt+"].value+'년'");
            eval("document.getElementsByName('kdate2_<?=$cno?>')["+cnt+"].innerText = document.getElementsByName('sdate2_<?=$cno?>')["+cnt+"].value+'월'");
            eval("document.getElementsByName('kdate3_<?=$cno?>')["+cnt+"].innerText = document.getElementsByName('sdate3_<?=$cno?>')["+cnt+"].value+'일'");

            eval("document.getElementsByName('kdate1_<?=$cno?>')["+cnt+"].style.width = '80px '");
            eval("document.getElementsByName('kdate2_<?=$cno?>')["+cnt+"].style.width = '52px '");
            eval("document.getElementsByName('kdate3_<?=$cno?>')["+cnt+"].style.width = '52px '");

            eval("document.getElementsByName('kdate1_<?=$cno?>')["+cnt+"].style.height = '50px '");
            eval("document.getElementsByName('kdate2_<?=$cno?>')["+cnt+"].style.height = '50px '");
            eval("document.getElementsByName('kdate3_<?=$cno?>')["+cnt+"].style.height = '50px '");
            // #####
            eval("document.getElementsByName('sdate1_<?=$cno?>')["+cnt+"].style.display = 'none'");
            eval("document.getElementsByName('sdate2_<?=$cno?>')["+cnt+"].style.display = 'none'");
            eval("document.getElementsByName('sdate3_<?=$cno?>')["+cnt+"].style.display = 'none'");

            cnt++;
          }

          cnt=0;
          while(eval("document.getElementsByName('edate1_<?=$cno?>')["+cnt+"]"))
          {
            // Sdate
            eval("document.getElementsByName('vdate1_<?=$cno?>')["+cnt+"].innerText = document.getElementsByName('edate1_<?=$cno?>')["+cnt+"].value+'년'");
            eval("document.getElementsByName('vdate2_<?=$cno?>')["+cnt+"].innerText = document.getElementsByName('edate2_<?=$cno?>')["+cnt+"].value+'월'");
            eval("document.getElementsByName('vdate3_<?=$cno?>')["+cnt+"].innerText = document.getElementsByName('edate3_<?=$cno?>')["+cnt+"].value+'일'");

            eval("document.getElementsByName('vdate1_<?=$cno?>')["+cnt+"].style.width = '80px '");
            eval("document.getElementsByName('vdate2_<?=$cno?>')["+cnt+"].style.width = '52px '");
            eval("document.getElementsByName('vdate3_<?=$cno?>')["+cnt+"].style.width = '52px '");

            eval("document.getElementsByName('vdate1_<?=$cno?>')["+cnt+"].style.height = '50px '");
            eval("document.getElementsByName('vdate2_<?=$cno?>')["+cnt+"].style.height = '50px '");
            eval("document.getElementsByName('vdate3_<?=$cno?>')["+cnt+"].style.height = '50px '");
            // #####
            eval("document.getElementsByName('edate1_<?=$cno?>')["+cnt+"].style.display = 'none'");
            eval("document.getElementsByName('edate2_<?=$cno?>')["+cnt+"].style.display = 'none'");
            eval("document.getElementsByName('edate3_<?=$cno?>')["+cnt+"].style.display = 'none'");

            cnt++;
          }
		  <?}
      ?>

      cnt=0;
      while(eval("document.getElementsByName('date_txt1')["+cnt+"]"))
      {              
        eval("document.getElementsByName('date_val1')["+cnt+"].innerText = document.getElementsByName('date_txt1')["+cnt+"].value");
        eval("document.getElementsByName('date_val2')["+cnt+"].innerText = document.getElementsByName('date_txt2')["+cnt+"].value");
        eval("document.getElementsByName('date_val3')["+cnt+"].innerText = document.getElementsByName('date_txt3')["+cnt+"].value");
        // #####
        eval("document.getElementsByName('date_val1')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('date_val2')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('date_val3')["+cnt+"].style.display = ''");
        // #####
        eval("document.getElementsByName('date_txt1')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('date_txt2')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('date_txt3')["+cnt+"].style.display = 'none'");

        cnt++;
      }

      cnt=0;
      while(eval("document.getElementsByName('date_line1')["+cnt+"]"))
      {              
        eval("document.getElementsByName('date_val1')["+cnt+"].innerText = document.getElementsByName('date_line1')["+cnt+"].value");
        eval("document.getElementsByName('date_val2')["+cnt+"].innerText = document.getElementsByName('date_line2')["+cnt+"].value");
        eval("document.getElementsByName('date_val3')["+cnt+"].innerText = document.getElementsByName('date_line3')["+cnt+"].value");
        
        eval("document.getElementsByName('date_val1')["+cnt+"].style.textDecoration  = 'underline'");
        eval("document.getElementsByName('date_val2')["+cnt+"].style.textDecoration  = 'underline'");
        eval("document.getElementsByName('date_val3')["+cnt+"].style.textDecoration  = 'underline'");
        // #####
        eval("document.getElementsByName('date_val1')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('date_val2')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('date_val3')["+cnt+"].style.display = ''");
        // #####
        eval("document.getElementsByName('date_line1')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('date_line2')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('date_line3')["+cnt+"].style.display = 'none'");

        cnt++;
      }

      cnt = 0;
      while(eval("document.getElementsByName('sb')["+cnt+"]"))	//select박스 display:none
      {
        eval("document.getElementsByName('sb')["+cnt+"].style.display = 'none'");
        cnt++;
      }

      if(cnt>1)	//select박스의 값 iuput 박스에 입력.
      {
        cnt = 0;
        while(eval("document.getElementsByName('sb')["+cnt+"]"))
        {
          eval("document.getElementsByName('sb_txt')["+cnt+"].innerText = document.getElementsByName('sb')["+cnt+"].options[document.getElementsByName('sb')["+cnt+"].selectedIndex].text");
          cnt++;
        }
      }
      else if(cnt==1)
      {
        document.getElementById('sb_txt').innerText = document.getElementById('sb').options[document.getElementById('sb').selectedIndex].text;
      }

      cnt = 0;
      while(eval("document.getElementsByName('sb_txt')["+cnt+"]"))
      {
        eval("document.getElementsByName('sb_txt')["+cnt+"].style.display = 'inline-block'");
        cnt++;
      }
      
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

      var cnt = 0;
      while(eval("document.getElementsByName('hpinupt')["+cnt+"]"))	//inputbox 테두리 스타일 없애기
      {
        eval("document.getElementsByName('hpinupt')["+cnt+"].style.border = ''");
        cnt++;
      }

      cnt = 0;
      while(eval("document.getElementsByName('txt')["+cnt+"]"))
      {
        eval("document.getElementsByName('txt')["+cnt+"].className = 'input_txt'");
        cnt++;
      }
      
      cnt=0;
      while(eval("document.getElementsByName('txt1')["+cnt+"]"))
      {              
        eval("document.getElementsByName('val1')["+cnt+"].innerText = ''");
        eval("document.getElementsByName('val2')["+cnt+"].innerText = ''");
        eval("document.getElementsByName('val3')["+cnt+"].innerText = ''");
        eval("document.getElementsByName('val4')["+cnt+"].innerText = ''");
        eval("document.getElementsByName('val5')["+cnt+"].innerText = ''");
        eval("document.getElementsByName('val6')["+cnt+"].innerText = ''");
        // #####
        eval("document.getElementsByName('val1')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('val2')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('val3')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('val4')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('val5')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('val6')["+cnt+"].style.display = 'none'");
        // #####
        eval("document.getElementsByName('txt1')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('txt2')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('txt3')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('txt4')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('txt5')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('txt6')["+cnt+"].style.display = ''");

        cnt++;
      }

      cnt = 0;
      while(eval("document.getElementsByName('sb')["+cnt+"]"))
      {
        eval("document.getElementsByName('sb')["+cnt+"].style.display = ''");
        cnt++;
      }

      cnt = 0;
      while(eval("document.getElementsByName('sb_txt')["+cnt+"]"))
      {
        eval("document.getElementsByName('sb_txt')["+cnt+"].style.display = 'none'");
        cnt++;
      }
      
      <?
        for( $i=0; $i<sizeof($data['loan_info_no']); $i++)
        {
          $cno = $data['loan_info_no'][$i];?>

          if(document.getElementById("ymdset_<?=$cno?>"))
          {
            document.getElementById("ymdset_<?=$cno?>").style.paddingLeft = '20px';
          }

          cnt=0;
          while(eval("document.getElementsByName('sdate1_<?=$cno?>')["+cnt+"]"))
          {
            eval("document.getElementsByName('kdate1_<?=$cno?>')["+cnt+"].innerText = '년'");
            eval("document.getElementsByName('kdate2_<?=$cno?>')["+cnt+"].innerText = '월'");
            eval("document.getElementsByName('kdate3_<?=$cno?>')["+cnt+"].innerText = '일'");

            eval("document.getElementsByName('kdate1_<?=$cno?>')["+cnt+"].style.width = ''");
            eval("document.getElementsByName('kdate2_<?=$cno?>')["+cnt+"].style.width = ''");
            eval("document.getElementsByName('kdate3_<?=$cno?>')["+cnt+"].style.width = ''");

            eval("document.getElementsByName('kdate1_<?=$cno?>')["+cnt+"].style.height = ''");
            eval("document.getElementsByName('kdate2_<?=$cno?>')["+cnt+"].style.height = ''");
            eval("document.getElementsByName('kdate3_<?=$cno?>')["+cnt+"].style.height = ''");
            // #####
            eval("document.getElementsByName('sdate1_<?=$cno?>')["+cnt+"].style.display = ''");
            eval("document.getElementsByName('sdate2_<?=$cno?>')["+cnt+"].style.display = ''");
            eval("document.getElementsByName('sdate3_<?=$cno?>')["+cnt+"].style.display = ''");

            cnt++;
          }

          cnt=0;
          while(eval("document.getElementsByName('edate1_<?=$cno?>')["+cnt+"]"))
          {
            eval("document.getElementsByName('vdate1_<?=$cno?>')["+cnt+"].innerText = '년'");
            eval("document.getElementsByName('vdate2_<?=$cno?>')["+cnt+"].innerText = '월'");
            eval("document.getElementsByName('vdate3_<?=$cno?>')["+cnt+"].innerText = '일'");

            eval("document.getElementsByName('vdate1_<?=$cno?>')["+cnt+"].style.width = ''");
            eval("document.getElementsByName('vdate2_<?=$cno?>')["+cnt+"].style.width = ''");
            eval("document.getElementsByName('vdate3_<?=$cno?>')["+cnt+"].style.width = ''");

            eval("document.getElementsByName('vdate1_<?=$cno?>')["+cnt+"].style.height = ''");
            eval("document.getElementsByName('vdate2_<?=$cno?>')["+cnt+"].style.height = ''");
            eval("document.getElementsByName('vdate3_<?=$cno?>')["+cnt+"].style.height = ''");
            // #####
            eval("document.getElementsByName('edate1_<?=$cno?>')["+cnt+"].style.display = ''");
            eval("document.getElementsByName('edate2_<?=$cno?>')["+cnt+"].style.display = ''");
            eval("document.getElementsByName('edate3_<?=$cno?>')["+cnt+"].style.display = ''");

            cnt++;
          }
		  <?}
      ?>

      cnt=0;
      while(eval("document.getElementsByName('date_txt1')["+cnt+"]"))
      {              
        eval("document.getElementsByName('date_val1')["+cnt+"].innerText = ''");
        eval("document.getElementsByName('date_val2')["+cnt+"].innerText = ''");
        eval("document.getElementsByName('date_val3')["+cnt+"].innerText = ''");
        // #####
        eval("document.getElementsByName('date_val1')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('date_val2')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('date_val3')["+cnt+"].style.display = 'none'");
        // #####
        eval("document.getElementsByName('date_txt1')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('date_txt2')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('date_txt3')["+cnt+"].style.display = ''");

        cnt++;
      }

      cnt=0;
      while(eval("document.getElementsByName('date_line1')["+cnt+"]"))
      {              
        eval("document.getElementsByName('date_val1')["+cnt+"].innerText = ''");
        eval("document.getElementsByName('date_val2')["+cnt+"].innerText = ''");
        eval("document.getElementsByName('date_val3')["+cnt+"].innerText = ''");
        
        eval("document.getElementsByName('date_val1')["+cnt+"].style.textDecoration  = ''");
        eval("document.getElementsByName('date_val2')["+cnt+"].style.textDecoration  = ''");
        eval("document.getElementsByName('date_val3')["+cnt+"].style.textDecoration  = ''");
        // #####
        eval("document.getElementsByName('date_val1')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('date_val2')["+cnt+"].style.display = 'none'");
        eval("document.getElementsByName('date_val3')["+cnt+"].style.display = 'none'");
        // #####
        eval("document.getElementsByName('date_line1')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('date_line2')["+cnt+"].style.display = ''");
        eval("document.getElementsByName('date_line3')["+cnt+"].style.display = ''");

        cnt++;
      }

      var cnt = 0;
      while(eval("document.getElementsByName('hpinupt')["+cnt+"]"))	//inputbox 테두리 스타일 없애기
      {
        eval("document.getElementsByName('hpval')["+cnt+"].innerText = ''");
        // #####
        eval("document.getElementsByName('hpval')["+cnt+"].style.display = 'none'");
        // #####
        eval("document.getElementsByName('hpinupt')["+cnt+"].style.display = ''");
        cnt++;
      }
  }
  else
  {
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

$('#datetimepicker').datetimepicker({
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

<script>  //window.setInterval('scrollPage()',1); /*printSet(); */</script>

@endsection