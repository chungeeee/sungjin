@extends('layouts.masterPop')

{{-- 내선번호 1228만 로그인가능 --}}
@php
if($user->id)
{
    $userid = env('EICN_LOGIN_ID');
    $exten  = $user->ph34;
    $passwd = env('EICN_LOGIN_PW');
}
@endphp

@section('content')
<body oncontextmenu="return false">
<form class='form-horizontal' name=input_form id=input_form method="post">
    <input type='hidden' name='option' id='option' value='0'>
    <input type='hidden' name='usertype' id='usertype' value='M'>
    <input type='hidden' name='nodejs_connector_url' id='nodejs_connector_url' value='{{env('EICN_CTI_HOST')}}:8087'>
    <input type='hidden' name='server_ip' id='server_ip' value='{{env('EICN_SERVER_IP')}}'>
    <input type='hidden' name='company_id' id='company_id' value='{{strtolower(env('COM_NAME'))}}'>
    <input type='hidden' name='userid' id='userid' value='{{$userid}}'>
    <input type='hidden' name='exten' id='exten' value='{{$exten}}'>
    <input type='hidden' name='passwd' id='passwd' value='{{$passwd}}'>

    <table class='table table-sm text-center card-secondary card-outline table-bordered' >
        <thead>
        <tr>
            <td class="pt-2" colspan=2 style='font-size: 1.0rem; height:46px;'>
                <div id='LOGIN_DIV' class='text-left' style='float:left;'>
                    <b>CTI &nbsp;</b>
                </div>

                <div id='STATUS_DIV' class='form-inline' style='display:none; float:right;'>
                    <div id='phonestatus'>
                        전화기상태
                    </div>
                    <div id='forwardstatus'>
                        착신전환상태
                    </div>
                    <div id='record_type'>
                        녹취형태
                    </div>
                </div>
            </td>
        </tr>
        </thead>
        <tr>
            <td class='pt-3'>
                <b>고객연동</b>
            </td>
            <td class='text-left'>
                <input class="ml-1 mt-1" type="checkbox" name='inbound_chk' id='inbound_chk' value='Y' onClick="setCookie('in_chk', '')"> 인바운드 &nbsp;&nbsp;&nbsp;
                <input class="ml-1 mt-1" type="radio" name='inbound_radio' id='inbound_radio' value='A1' onClick="setCookie('inbound', 'A1')"> 벨울릴 때&nbsp;
                <input class="ml-1 mt-1" type="radio" name='inbound_radio' id='inbound_radio' value='A2' onClick="setCookie('inbound', 'A2')"> 통화 되었을 때
                <br>
                <input class="ml-1 mt-1" type="checkbox" name='outbound_chk' id='outbound_chk' value='Y' onClick="setCookie('out_chk', '')"> 아웃바운드
                <input class="ml-1 mt-1" type="radio" name='outbound_radio' id='outbound_radio' value='B1' onClick="setCookie('outbound', 'B1')"> 벨울리 때&nbsp;
                <input class="ml-1 mt-1" type="radio" name='outbound_radio' id='outbound_radio' value='B2' onClick="setCookie('outbound', 'B2')"> 통화 되었을 때
            </td>
        </tr>
        <tr>
            <td class='pt-2' style='width:190px;'>
                <b>상담원상태</b>
            </td>
            <td class='text-left'>
                <input type=button class='btn btn-sm btn-primary ml-1' name='memberstatus0' id='memberstatus0' value='대기(0)'>
                <input type=button class='btn btn-sm btn-primary ml-1' name='memberstatus1' id='memberstatus1' value='상담중(1)'>
                <input type=button class='btn btn-sm btn-primary ml-1' name='memberstatus2' id='memberstatus2' value='후처리(2)'>
                <input type=button class='btn btn-sm btn-primary ml-1' name='memberstatus3' id='memberstatus3' value='휴식(3)'>
                <input type=button class='btn btn-sm btn-primary ml-1' name='memberstatus4' id='memberstatus4' value='식사(4)'>
                <input type=button class='btn btn-sm btn-primary ml-1' name='memberstatus7' id='memberstatus7' value='아웃바운드(7)'>
                <input type=button class='btn btn-sm btn-primary ml-1' name='memberstatus8' id='memberstatus8' value='PDS(8)'>
            </td>
        </tr>
        <tr>
            <td class='pt-2'>
                <b>PDS상태</b>
            </td>
            <td class='text-left'>
                <input type=button name='pdsstatus0' class='btn btn-sm btn-primary ml-1' id='pdsstatus0' value='PDS대기(0)'>
                <input type=button name='pdsstatus1' class='btn btn-sm btn-primary ml-1' id='pdsstatus1' value='PDS상담중(1)'>
                <input type=button name='pdsstatus2' class='btn btn-sm btn-primary ml-1' id='pdsstatus2' value='PDS후처리(2)'>
                <input type=button name='pdsstatus3' class='btn btn-sm btn-primary ml-1' id='pdsstatus3' value='PDS타업무(3)'>
            </td>
        </tr>
        <tr>
            <td class='pt-2'>
                <b>전화걸기</b>
            </td>
            <td class='text-left'>
                <span class='ml-1'>고객번호 : </span><input type=text size=15 name=number id=number value=''>&nbsp;
                RID : <input type=text size=15 name=cid id=cid value='15442525'>
                <input name="dial_btn" type="button" id='dial_btn' class='btn btn-sm btn-info ml-1' value='전화걸기' ><br>
            </td>
        </tr>
        <tr>
            <td class='pt-2'>
                <b>받기,끊기,당겨받기</b>
            <td class='text-left'>
                <input class='btn btn-sm btn-info ml-1' name="receive_btn" id='receive_btn' type="button" value='전화받기'>
                <input class='btn btn-sm btn-info ml-1' name="hangup_btn" id='hangup_btn' type="button" value='전화끊기'>
                <input class='btn btn-sm btn-info ml-1' name="pickup_btn" id='pickup_btn' type="button" value='당겨받기'>
                <!--input name="pickup_btn1" id='pickup_btn1' type="button" value='당겨받기1'-->
            </td>
        </tr>
        <tr>
            <td class='pt-2'>
                <b>돌려주기(내선)</b>
            </td>
            <td class='text-left'>
                <input class='ml-1' type=text size=15 name='transfer_num' id='transfer_num' value=''>
                <input class='btn btn-sm btn-info ml-1 ml-1' name="redirect_btn" type="button" id="redirect_btn" value='돌려주기(BLIND)'>
                <input class='btn btn-sm btn-info ml-1 ml-1' name="attended_btn" type="button" id='attended_btn' value='돌려주기(ATTENDED)'>
                <input class='btn btn-sm btn-info ml-1 ml-1' name="attended_hangup_btn" id='attended_hangup_btn' type="button" value='돌려준전화끊기'>
            </td>
        </tr>
        <tr>
            <td class='pt-2'>
                <b>돌려주기(외부로)</b>
            </td>
            <td class='text-left'>
                <input class='ml-1' type=text size=15 name='transferout_num' id='transferout_num' value=''>
                <input class='btn btn-sm btn-info ml-1' name="redirectout_btn" type="button" id='redirectout_btn' value='돌려주기(BLIND)'>
                <input class='btn btn-sm btn-info ml-1' name="attendedout_btn" type="button" id='attendedout_btn' value='돌려주기(ATTENDED)'>
            </td>
        </tr>
        {{-- <tr>
            <td class='pt-2'> 
                <b>돌려주기(헌트번호, 대표번호)</b>
            </td>
            <td class='text-left'>
                <input class='ml-1' type=text size=15 name='redirecthunt_num' id='redirecthunt_num' value=''>
                <input class='btn btn-sm btn-info  ml-1' name="redirecthunt_btn" type="button" id="redirecthunt_btn" value='돌려주기(BLIND)'>
            </td>
        </tr>
        <tr>
            <td class='pt-2'>
                <b>착신전환</b>
            </td>
            <td class='text-left'>
                <input class="ml-1" type="text" name='forwarding' id='forwarding' value='' size=15>
                <input class="ml-1" type="radio" size=15 name='forward_when' id='forward_when' value='N' onClick="selectForward('N')"> 착신전화안함
                <input class="ml-1" type="radio" name='forward_when' id='forward_when' value='A' onClick="selectForward('A')"> 항상
                <input class="ml-1" type="radio" name='forward_when' id='forward_when' value='B' onClick="selectForward('B')"> 통화중
                <input class="ml-1" type="radio" name='forward_when' id='forward_when' value='C' onClick="selectForward('C')"> 부재중
                <input class="ml-1" type="radio" name='forward_when' id='forward_when' value='T' onClick="selectForward('T')"> 부재중+통화중
                <input class='btn btn-sm btn-info' name="forward_btn" type="button" id='forward_btn' value='착신전환'>
            </td>
        </tr>
        <tr>
            <td class='pt-2'>
                <b>마지막콜이벤트다시받기</b>
            </td>
            <td class='text-left'>
                <input class='btn btn-sm btn-info ml-1' name="lastevent_btn" type="button" id='lastevent_btn' value='다시받기'>
            </td>
        </tr>
        <tr>
            <td class='pt-2'>
                <b>보류</b>
            </td>
            <td class='text-left'>
                <input class='btn btn-sm btn-info ml-1' name="shold_btn" type="button" id='shold_btn' value='시작'>
                <input class='btn btn-sm btn-info ml-1' name="ehold_btn" type="button" id='ehold_btn' value='종료' >
            </td>
        </tr>
        <tr>
            <td class='pt-2'>
                <b>부분녹취</b>
            </td>
            <td class='text-left'>
                <input class='btn btn-sm btn-info ml-1' name="srec_btn" type="button" id='srec_btn' value='시작'>
                <input class='btn btn-sm btn-info ml-1' name="erec_btn" type="button" id='erec_btn' value='종료' >
            </td>
        </tr>
        <tr>
            <td class='pt-2'>
                <b>성희롱/언어폭력 안내멘트</b>
            </td>
            <td class='text-left'>
                <input class='btn btn-sm btn-info ml-1' name="sexual_btn" type="button" id='sexual_btn' value='성희롱'>
                <input class='btn btn-sm btn-info ml-1' name="violence_btn" type="button" id='violence_btn' value='언어폭력'>
            </td>
        </tr>
        <tr>
            <td class='pt-2'>         
                <b>안내멘트ARS 연결</b>
            </td>
            <td class='text-left'>
                <input class='btn btn-sm btn-info ml-1 agree_btn' name="agree_btn" type="button" data='1'  id='agree_btn_1' value='컷오프 동의'>
                <input class='btn btn-sm btn-info ml-1 agree_btn' name="agree_btn" type="button" data='4'  id='agree_btn_4' value='주민번호 입력'>
                <input class='btn btn-sm btn-info ml-1 agree_btn' name="agree_btn" type="button" data='10' id='agree_btn_10' value='개인정보 수입이용 동의'>
                <input class='btn btn-sm btn-info ml-1 agree_btn' name="agree_btn" type="button" data='11' id='agree_btn_11' value='개인정보 제공 동의'>
                <input class='btn btn-sm btn-info ml-1 agree_btn' name="agree_btn" type="button" data='12' id='agree_btn_12' value='상품이용권유 동의'>
                <input class='btn btn-sm btn-info ml-1 agree_btn' name="agree_btn" type="button" data='13' id='agree_btn_13' value='개인정보 필수 동의'>
                <input class='btn btn-sm btn-info ml-1 agree_btn' name="agree_btn" type="button" data='14' id='agree_btn_14' value='연계대출 동의'>
            </td>
        </tr> --}}
        <tr>
            <td class='pt-2'>         
                <b>로그</b>
            </td>
            <td class='text-left'>
                <input class="ml-1" name="remove_btn" type="button" id="remove_btn" value='비우기' style="width:60px; height:20px; border-top-left-radius: 0.2rem; border-top-right-radius: 0.2rem; color:#FFFFFF; background-color:#1E9FB3; border:0 solid #51881A" ><br>
                <textarea class="ml-1" name="snd_text" id='snd_text' style='width:100%;height:190px;'></textarea>
            </td>
        </tr>
    </table>
</form>
@endsection

@section('javascript')
<script>

// 페이지 로드시 자동로그인
window.onload = function(){

    login();

    if('{{$cust_num}}' != 'NoN')
    {
        $('#number').val('0'+{{$cust_num}});
        setTimeout(() => click2call(), 1500);
    }

    setTimeout(() => getCookie(), 500);
}

function setCookie(cookie_name, value)
{
    var exdate = new Date();
    exdate.setDate(exdate.getDate() + 30);
    // 설정 일수만큼 현재시간에 만료값으로 지정 ( 3일 )

    var cookie_value = escape(value) + ((30 == null) ? '' : '; expires=' + exdate.toUTCString());

    if(cookie_name == 'in_chk')
    {
        document.cookie = cookie_name + '=' + $('input[name=inbound_chk]:checked').val();
    }
    else if(cookie_name == 'out_chk')
    {
        document.cookie = cookie_name + '=' + $('input[name=outbound_chk]:checked').val();
    }
    else
    {
        document.cookie = cookie_name + '=' + cookie_value;
    }

    console.log(document.cookie);
}

function getCookie() 
{    
    var x, y;
    var val = document.cookie.split(';');

    for (var i = 0; i < val.length; i++) 
    {
        x = val[i].substr(0, val[i].indexOf('='));
        y = val[i].substr(val[i].indexOf('=') + 1);
        x = x.replace(/^\s+|\s+$/g, ''); // 앞과 뒤의 공백 제거하기
        if(x == 'in_chk' && unescape(y) == 'Y')
        {
            $("input:checkbox[name='inbound_chk']").prop('checked', true);                                  // 선택하기
        }
        if(x == 'out_chk' && unescape(y) == 'Y')
        {
            $("input:checkbox[name='outbound_chk']").prop('checked', true);                                 // 선택하기
        }
        if(x == 'inbound')
        {
            $("input:radio[name='inbound_radio']:radio[value='"+unescape(y)+"']").prop('checked', true);    // 선택하기
        }
        if(x == 'outbound')
        {
            $("input:radio[name='outbound_radio']:radio[value='"+unescape(y)+"']").prop('checked', true);   // 선택하기
        }
 
    }
}


// 새로고침 막기
{{-- document.onkeydown = noEvent;
function noEvent() {
    if (event.keyCode == 116) {
        event.keyCode= 2;
        return false;
    }
    else if(event.ctrlKey && (event.keyCode==78 || event.keyCode == 82))
    {
        return false;
    }
} --}}

</script>
@endsection
