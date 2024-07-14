<html>
<head>
<title>대출중개 경로 표시서</title>
<meta http-equiv="Content-Type" content="text/html; charset=euc-kr">
<style>
    body { font-size:10pt }

	#bodyTbl td {
		font-family:바탕;
		font-size:10pt;
		font-weight:bold;
		border-top-width: 0px !important;
		border-right-width: 0px !important;
		border-bottom-width: 0px !important;
		border-left-width: 0px !important;

		border-bottom-style: solid !important;
		border-top-style: solid !important;
		border-right-style: solid !important;
		border-left-style: solid !important;

		border-top-color: #444444 !important;
		border-right-color: #444444 !important;
		border-bottom-color: #444444 !important;
		border-left-color: #444444 !important;
	}

	.Content {
		font-size:8pt;
		text-align:center;
	}

    #agentLevelTbl td {
        border:1px solid black !important;
    }

    #custTbl td {
        border:1px solid black !important;
    }
</style>
</head>
<body topmargin="0" leftmargin="0">
<table id="bodyTbl" style="border-style:double;border-color:black;margin-top:5px;" width="96%" height="95%" cellpadding="5" align=center>
	<tr height="20">
		<td>&nbsp;</td>
	</tr>
	<tr width="100%" align="center">
		<td style="font-size:25pt;">대출중개 경로 표시서</td>
	</tr>
	<tr height="35">
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><font size="3">&nbsp;&nbsp;당 사가 귀 사에 제공하는 본 대출신청 고객정보는 다음의 대부중개업자를 통하여 생산 및 제공받았음을 확인 합니다.</font></td>
	</tr>
	<tr height="25">
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;&nbsp;&nbsp;○ 제공할 고객정보</td>
	</tr>
	<tr>
		<td>
			<table id="custTbl" cellspacing="1" cellpadding="5" bgcolor="#000000" width="100%">
				<tr bgcolor="#ffffff" width="100%">
					<td width="15%" align="center">고&nbsp;&nbsp;객&nbsp;&nbsp;명</td>
					<td colspan=3>[이름]</td>
				</tr>
				<tr bgcolor="#ffffff" width="100%">
					<td width="15%" align="center">생&nbsp;년&nbsp;월&nbsp;일</td>
					<td width="35%">[생년월일]</td>
					<td width="15%" align="center">성&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;별</td>
					<td align="center">[성별체크]</td>
				</tr>
			</table>
		</td>
	</tr>
	<tr height="25">
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;&nbsp;&nbsp;○ 고객정보의 제공 경로</td>
	</tr>
	<tr>
		<td>
            <div style="min-height:400px">
			<table id="agentLevelTbl" cellspacing="1" cellpadding="5" bgcolor="#000000" width="100%">
				
				<thead>
					<tr bgcolor="#ffffff" width="100%"  align=center style="height:20px;">
						<td width="15%">구&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;분</td>
						<td width="15%">해당<br>연월일</td>
						<td width="15%">상&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;호</td>
						<td width="23%">대부중개업<br>등록번호</td>
						<td width="17%">협회 등록번호</td>
						<td width="15%">전화번호</td>
					</tr>
				</thead>
				<tbody>
                    @foreach($v as $a)
                        <tr bgcolor="#ffffff" width="100%" style="height:20px;">
                            <td align=center>@if($a->agent_level == 1) 최초 @else {{ ($a->agent_level-1) }}차 @endif 제공받은자</td>
                            <td class="Content">{{date("Y년m월d일",strtotime($a->agent_app_date))}}</td>
                            <td class="Content">{{$a->agent_name}}</td>
                            <td class="Content">{{$a->agent_ssn}}</td>
                            <td class="Content">{{$a->agent_assn}}</td>
                            <td class="Content">{{$a->agent_ph}}</td>
                        </tr>
                    @endforeach
				</tbody>
			</table>
            </div>
		</td>
	</tr>
	<tr height="5">
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>*동 표시서는 제공하려는 고객의 대출신청 구비서류 맨 앞면에 부착하여 사용 바랍니다.
		</td>
	</tr>
</table>
</body>
<html>