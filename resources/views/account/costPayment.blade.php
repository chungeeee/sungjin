@extends('layouts.master')
@section('content')
@include('inc/list')

<!-- Main content -->


@endsection

@section('lump')
일괄처리할거 입력
@endsection

@section('javascript')
<div class="modal fade" id="investorModal">
    <div class="modal-dialog" style="width:500px;">
      <div class="modal-content" id="investorModalContent">
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

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

function lump_del(btn_obj)
{
    if( checkOneMore()===false )
    {
        alert('체크박스를 선택해주세요');
        return false;
    }
    if(!confirm("선택하신 투자원금상환을 삭제하시겠습니까?"))
    {
        return false;
    }

    var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
    formData.append("action_mode", "cost_payment_DELETE");

    btn_obj.disabled = true;
    $("#"+btn_obj.id).html(loadingStringtxt);

    $.ajax({
        url  : "/account/costpaymentdelete",
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

</script>
@endsection