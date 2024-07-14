@extends('layouts.master' . $type)

@section('content')

{{-- 팝업으로 여는 경우 --}}
@if($type == 'Pop')
<div class="card card-lightblue">
    <div class="card-header-no-round">
    <h2 class="card-title">송달료</h2>
    </div>
</div>
@endif

@include('inc.list')

<div class="modal fade" id="postageModal">
    <div class="modal-dialog @if($type == 'Pop') modal-lg @else modal-md @endif">
        <div class="modal-content" id="postageModalBody">

        </div>
    </div>
</div>
@endsection

@section('javascript')
<script>
// 함수 재정의
function afterAjax()
{
	// selectpicker
	$('.selectpicker').selectpicker({
		width: 'auto',
		style: 'btn-default form-control-sm bg-white',
	});

	// 달력
	$(".datetimepicker").datetimepicker({
		format: 'YYYY-MM-DD',
		locale: 'ko',
		useCurrent: false,
	});

	// 숫자만 입력, 콤마 자동 생성
	$(".comma").number(true);

	// 소숫점 2자리 자동 입력
    $(".floatnum").number(true, 2);

	// On Off 버튼
	$("input[data-bootstrap-switch]").each(function() {
		$(this).bootstrapSwitch('state', $(this).prop('checked'));
	});
	//
	$(".datetimepicker-wol").datetimepicker({
		format: 'yyyy-MM',
		locale: 'ko',
		useCurrent: false,
	});
	
	checkPostage();
}

@if($type == 'Pop')
listRefresh();
@endif

// 선택되어있는 항목 표시
function checkPostage()
{
    for(var i = 0; i < $("input[name='postage_use_no[]']", opener.document).length; i++)
    {
        if ($('#no_' + $("input[name='postage_use_no[]']", opener.document).eq(i).val()).attr('id') == 'no_' + $("input[name='postage_use_no[]']", opener.document).eq(i).val())
        {
            $('#no_' + $("input[name='postage_use_no[]']", opener.document).eq(i).val()).css('background-color', '#86e29b');
        }
    }
}

function postageForm(no)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#postageModal").modal();
    $("#postageModalBody").html(loadingString);
    $.post("/erp/lawpostageform", { no: no, type: '{{ $type }}' }, function(data) {
        $('#postageModalBody').html(data);
        setInputMask('class', 'moneyformat', 'money');
        afterAjax();
    }).fail(function(jqXHR) {
        alert("오류가 발생했습니다." );
        console.log(jqXHR);
    });
}

function usePostage(pay_no, pay_money)
{
    // 중복선택 확인하기
    for(var i = 0; i < $("input[name='postage_use_no[]']", opener.document).length; i++)
    {
        if (pay_no == $("input[name='postage_use_no[]']", opener.document).eq(i).val())
        {
            alert('이미 선택 되어있는 송달료입니다.');
            return false;
        }
    }

    
    // 법비용창 hidden 값 넣어주기
    $('#trade_cost_path_' + $('#rownolawPostage').val(), opener.document).val('32');
    $('#postage_am_' + $('#rownolawPostage').val(), opener.document).val(pay_money);
    $('#postage_am_' + $('#rownolawPostage').val(), opener.document).attr('readonly', true);
    $('#postage_use_no_' + $('#rownolawPostage').val(), opener.document).val(pay_no);

    self.close();
    return false;
}

function postageAction(action)
{
    if (action == 'DEL')
    {
        if (!confirm('정말 삭제하시겠습니까?'))
        {
            return false;
        }
    }
    else if (action == 'UPD')
    {
        if (!confirm('저장하시겠습니까?'))
        {
            return false;
        }
    }
    else
    {
        return false;
    }
    
    $('#postageAction').val(action);

    var formData = $("#lawPostageForm").serialize();

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.post("/erp/lawpostageaction", formData, function(data) {
        alert(data.msg);

        if (data.result == 'Y')
        {
            $("#postageModal").modal('hide');
            listRefresh();
        }
    }).fail(function(jqXHR) {
        alert("오류가 발생했습니다." );
        console.log(jqXHR);
    });
}
</script>
@endsection
