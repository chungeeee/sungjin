@extends('layouts.master')


@section('content')
@include('inc/list')
<!-- 계약명세 모달 -->
@endsection


@section('lump')
일괄처리할거 입력
@endsection

@section('javascript')
<div class="modal fade" id="divideModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content" id="divideModalContent">
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
<script>

// 투자자 입력 modal show 동작
function divideoriginform(no, loan_info_no) {
  $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#divideModal").modal('show');
	$("#divideModalContent").html(loadingString);
	$.post("/account/divideoriginform", { no: no, loan_info_no: loan_info_no }, function (data) {
		$("#divideModalContent").html(data);
	});
}

// 투자자 입력 modal show 동작
function divideoriginview(no, loan_info_no) {
  $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#divideModal").modal('show');
	$("#divideModalContent").html(loadingString);
	$.post("/account/divideoriginview", { no: no, loan_info_no: loan_info_no }, function (data) {
		$("#divideModalContent").html(data);
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
    if(!confirm("선택하신 투자원금조정 거래내역을 삭제하시겠습니까?\n삭제하시면 복구할 수 없으며 필요시 수기로 재등록해야합니다."))
    {
        return false;
    }

    var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
    formData.append("action_mode", "LUMP_DIVIDEORIGIN_DELETE");
    formData.append("sms_send_flag", sms_send_flag);

    btn_obj.disabled = true;
    $("#"+btn_obj.id).html(loadingStringtxt);

    $.ajax({
        url  : "/account/divideorigindelete",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result=="Y" )
            {
                alert("투자원금 조정 거래내역 삭제처리 완료");
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


enterClear();
</script>
@endsection