@extends('layouts.master')

@section('content')
@include('inc/list')
@endsection

@section('javascript')
<script>

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

function overMoney(loan_info_no, div)
{
    // 중복클릭 방지
    if(ccCheck()) return;

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url  : "/erp/tradeoveraction",
        type : "post",
        data : { div : div, loan_info_no : loan_info_no },
        success : function(result)
        {
            if( result=="Y" )
            {
                alert("가지급금 정리가 완료됐습니다.");
                location.reload();
            }
            else
            {
                alert(result);
                globalCheck = false;
            }
        },
        error : function(xhr)
        {
            globalCheck = false;
            alert("통신오류입니다.");
        }
  });
}

enterClear();

</script>
@endsection