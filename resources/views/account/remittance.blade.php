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

function lumpit()
{
    if( !confirm("일괄처리를 진행하시겠습니까?") )
    {
        return false;
    }
    if( checkOneMore()===false )
    {
        alert('체크박스를 선택해주세요');
        return false;
    }
}

function searchAccount(no)
{
    var div = 'UPD';
    
    // 중복클릭 방지
    if(ccCheck()) return;

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url  : "/account/loanbanksearch",
        type : "post",
        data : { div : div, loan_info_no : no },
        success : function(result)
        {
            if( result['rs_code'] == "Y" )
            {
                globalCheck = false;
                alert(result['result_msg']);
                $('#btnSearchAccount_' + no).removeClass('btn-primary').addClass('btn-default').text('인증완료');
            }
            else
            {
                globalCheck = false;
                alert(result['result_msg']);
            }
        },
        error : function(xhr)
        {
            globalCheck = false;
            alert("통신오류입니다.");
        }
    });
}

function sendAccount(no)
{
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 중복클릭 방지
    if(ccCheck()) return;
    
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url  : "/account/remittanceaction",
        type : "post",
        data : { loan_info_no : no},
        success : function(result)
        {
            if(result['rs_code'] == "Y")
            {
                globalCheck = false;
                alert("송금요청이 완료되었습니다.");
                $('#btnSendAccount_' + no).removeClass('btn-primary').addClass('btn-default').text('요청중');
            }
            else
            {
                globalCheck = false;
                alert(result['result_msg']);
            }
        },
        error : function(xhr)
        {
            globalCheck = false;
            alert("통신오류입니다.");
        }
    });
}

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

function lump_ins(btn_obj)
{
    if(!isCheckboxChecked('listChk[]'))
    {
        alert('체크박스를 선택해주세요');
        return false;
    }
    if(!confirm("송금요청하시겠습니까?."))
    {
        return false;
    }

    var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
    formData.append("action_mode", "remittance_INSERT");

    btn_obj.disabled = true;
    $("#"+btn_obj.id).html(loadingStringtxt);

    $.ajax({
        url  : "/account/remittancelumpinsert",
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
            $("#"+btn_obj.id).html("일괄송금요청");
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