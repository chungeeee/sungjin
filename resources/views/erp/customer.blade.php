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

function lump_del(btn_obj)
{
  if(!isCheckboxChecked('listChk[]'))
  {
      alert('선택한 내역이 없습니다. 삭제할 내역을 선택 후 이용해 주세요.');
      return;
  }
  if(!confirm("선택하신 차입자를 삭제하시겠습니까?\n삭제하시면 복구할 수 없으며 필요시 차입자를 재등록해야합니다."))
  {
      return false;
  }

  var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
  formData.append("action_mode", "customer_DELETE");

  btn_obj.disabled = true;

  $("#"+btn_obj.id).html(loadingStringtxt);

  $.ajax({
      url  : "/erp/customerdelete",
      type : "post",
      data : formData,
      processData: false,
      contentType: false,
      success : function(result)
      {
          if( result=="Y" )
          {
              alert("해당 차입자가 삭제처리되었습니다.");
              listRefresh();
          }
          else
          {
              alert(result);
          }

          btn_obj.disabled = false;
          $("#"+btn_obj.id).html("휴지통");
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