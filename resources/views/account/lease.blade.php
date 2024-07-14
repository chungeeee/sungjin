@extends('layouts.master')


@section('content')
@include('inc/list')
<!-- 계약명세 모달 -->
@endsection


@section('lump')
일괄처리할거 입력
@endsection

@section('javascript')
<script>

// 엔터막기
function enterClear()
{
    $('input[type="text"]').keydown(function() {
      if (event.keyCode === 13)
      {
        event.preventDefault();
        listRefresh();
      };
    });

    $("input[data-bootstrap-switch]").each(function() {
    $(this).bootstrapSwitch('state', $(this).prop('checked'));
  });
}

// 유효성 체크
function validateChk(mode)
{
  if($('#code').val() == 0)
  {
    alert('코드를 입력해주세요.');
    $('#code').focus();
    return false;
  }
  if($('#manager').val() == 0)
  {
    alert('담당자를 입력해주세요.');
    $('#manager').focus();
    return false;
  }
  if($('#id').val() == 0)
  {
    alert('아이디를 입력해주세요.');
    $('#id').focus();
    return false;
  }
  if(mode == 'INS')
  {
    if($('#passwd').val() == 0)
    {
      alert('패스워드를 입력해주세요.');
      $('#passwd').focus();
      return false;
    }
  }
  if($('#email').val() != 0)
  {
    var reg_email = /^([0-9a-zA-Z_\.-]+)@([0-9a-zA-Z_-]+)(\.[0-9a-zA-Z_-]+){1,2}$/;
    if (!reg_email.test($('#email').val())) 
    {
      alert('이메일 형식이 올바르지 않습니다.');
      return false;
    } 
  }
  if($('#ip').val() != 0)
  {
    var repl_ip = $('#ip').val().replaceAll(' ', '');
    var chk_ip = repl_ip.split(',');

    if( chk_ip ) 
    {
      for (var key in chk_ip) 
      {  
        var reg_ip = /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/g;

        if (!reg_ip.test(chk_ip[key])) 
        {
          alert('아이피 형식이 올바르지 않습니다.');
          return false;
        }
      }
    }
  }

  return true;
}



function lumpit()
{

}

function checkOneMore()
{
    var checked = $('input[name="listChk[]"]:checked').length > 0;
    if(checked !== true)
    {
        return false;
    }
    else
    {
        return true;
    }
}

function advanceDepositForm(n)
{
    //중앙위치 구해오기
    width  = 900;
    height = 900;

    LeftPosition =(screen.width-width)/2;
    TopPosition  =(screen.height-height)/2;

    var wnd = window.open("/account/investmentform?no="+n, "investmentpop","width="+width+", height="+height+",top="+TopPosition+",left="+LeftPosition+", scrollbars=yes");
    wnd.focus();
}

enterClear();

</script>
@endsection