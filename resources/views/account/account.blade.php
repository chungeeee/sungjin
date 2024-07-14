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


// 영어, 숫자입력
function specialCharRemove(obj) 
{
  var val = obj.value;
  var pattern = /[^(a-zA-Z0-9)]/gi;
  if(pattern.test(val)){
      obj.value = val.replace(pattern,"");
  }
}

// 한글입력
function onlyKorean(obj) 
{
  var val = obj.value;
  var pattern = /[^(가-힣ㄱ-ㅎㅏ)]/gi;
  if(pattern.test(val)){
      obj.value = val.replace(pattern,"");
  }
}

// 숫자입력
function onlyNumber(obj) 
{
  var val = obj.value;
  var pattern = /[^0-9]/g;  
  if(pattern.test(val)){
      obj.value = val.replace(pattern,"");
  }
}

// 이율입력 ( '숫자', '.') 
function onlyRatio(obj) 
{
  var val = obj.value;
  var pattern = /[^(0-9_.)]/gi;
  if(pattern.test(val)){
      obj.value = val.replace(pattern,"");
  }
}

function lump_action(btn_obj)
{
    if(!isCheckboxChecked('listChk[]'))
    {
        alert('체크박스를 선택해주세요');
        return false;
    }
    if(!confirm("선택하신 항목을 대기탭으로 옮기시겠습니까?"))
    {
        return false;
    }

    var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
    formData.append("action_mode", "account_transfer_ACTION");

    btn_obj.disabled = true;
    $("#"+btn_obj.id).html(loadingStringtxt);

    $.ajax({
        url  : "/account/accountlumpaction",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result['rslt'] == "Y" )
            {
                alert(result['msg']);
                listRefresh();
            }
            else
            {
                alert(result['msg']);
            }

            btn_obj.disabled = false;
            $("#"+btn_obj.id).html("요청");
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
        }
    });
}

function lump_del(btn_obj)
{
    if(!isCheckboxChecked('listChk[]'))
    {
        alert('체크박스를 선택해주세요');
        return false;
    }
    if(!confirm("선택하신 항목을 취소하시겠습니까?"))
    {
        return false;
    }

    var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
    formData.append("action_mode", "account_transfer_DELETE");

    btn_obj.disabled = true;
    $("#"+btn_obj.id).html(loadingStringtxt);

    $.ajax({
        url  : "/account/accountlumpdelete",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result['rslt'] == "Y" )
            {
                alert(result['msg']);
                listRefresh();
            }
            else
            {
                alert(result['msg']);
            }

            btn_obj.disabled = false;
            $("#"+btn_obj.id).html("취소");
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
        }
    });
}


enterClear();

</script>
@endsection