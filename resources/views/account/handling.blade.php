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



function handlingForm(n)
{
    //중앙위치 구해오기
    width  = 900;
    height = 800;

    LeftPosition =(screen.width-width)/2;
    TopPosition  =(screen.height-height)/2;

    var wnd = window.open("/account/handlingform?no="+n, "handlingpop","width="+width+", height="+height+",top="+TopPosition+",left="+LeftPosition+", scrollbars=yes");
    wnd.focus();
}


// 엔터막기
function enterClear()
{
    $('input[type="text"]').keydown(function() {
        if( event.keyCode === 13 )
        {
            event.preventDefault();
            listRefresh();
        };
    });

    $("input[data-bootstrap-switch]").each(function() {
    $(this).bootstrapSwitch('state', $(this).prop('checked'));
  });
}


function lump_del(btn_obj)
{
    if( checkOneMore()===false )
    {
        alert('체크박스를 선택해주세요');
        return false;
    }

    var sms_send_flag = "N";
    if(!confirm("선택하신 수수료 거래내역을 삭제하시겠습니까?\n삭제하시면 복구할 수 없으며 필요시 수기로 재등록해야합니다."))
    {
        return false;
    }

    var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
    formData.append("action_mode", "LUMP_HANDLING_DELETE");
    formData.append("sms_send_flag", sms_send_flag);

    btn_obj.disabled = true;
    $("#"+btn_obj.id).html(loadingStringtxt);

    $.ajax({
        url  : "/account/handlingdelete",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result=="Y" )
            {
                alert("취급수수료 삭제처리 완료");
                listRefresh();
            }
            else
            {
                alert(result);
            }
            btn_obj.disabled = false;
            $("#"+btn_obj.id).html("삭제처리");
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
        }
    });

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

enterClear();

</script>
@endsection