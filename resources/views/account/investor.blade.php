@extends('layouts.master')


@section('content')
@include('inc/list')
<!-- 계약명세 모달 -->
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

// 투자자 입력 modal show 동작
function setInvestorInputForm(id) {
  $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#investorModal").modal('show');
	$("#investorModalContent").html(loadingString);
	$.post("/account/investorinputform", { id: id }, function (data) {
		$("#investorModalContent").html(data);
	});
}

function lump_del(btn_obj)
{
  if( checkOneMore()===false )
  {
    alert('체크박스를 선택해주세요');
    return false;
  }
  if(!confirm("선택하신 투자자를 삭제하시겠습니까?\n삭제하시면 복구할 수 없으며 필요시 투자자를 재등록해야합니다."))
  {
    return false;
  }

  var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
  formData.append("action_mode", "investor_DELETE");

  btn_obj.disabled = true;
  $("#"+btn_obj.id).html(loadingStringtxt);

  $.ajax({
    url  : "/account/investordelete",
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
        $("#"+btn_obj.id).html("삭제");
    },
    error : function(xhr)
    {
        alert("통신오류입니다.");
    }
  });
}

</script>
@endsection