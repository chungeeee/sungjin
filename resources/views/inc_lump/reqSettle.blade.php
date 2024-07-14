

<div id="LUMP_FORM_reqSettle" class="lump-forms" style="display:none">

    <div class="row p-1">
        <div class="col-md-4">
            <select class="form-control form-control-sm" name="sub_type" id="sub_type">
                {{ Func::printOption($sub_type, '') }} 
            </select>
        </div>
        <div class="col-md-4">
            <select class="form-control form-control-sm" id="sub_type_cd" name="sub_type_cd" onchange="setSettleReason(this.value)">
                <option value=''>상세구분</option>
                {{ Func::printOption($sub_type_cd, '') }} 
            </select>
        </div>
        <div class="col-md-4">
            <select class="form-control form-control-sm" name="settle_reason_cd" id="settle_reason_cd">
                <option value=''>화해사유 선택</option>
                {{ Func::printOption($settle_reason_cd,'') }} 
            </select>
        </div>
    </div>

    <div class="row p-1">
        <div class="col-md-12">
            <button class="btn btn-sm btn-info" id="LUMPFORM_BTN_reqSettle" onclick="lumpReqSettle(); return false;">접수</button>
        </div>
    </div> 

</div>



<script>

function getSubTypeCd(sub_type)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.post("/erp/settlesubtypecd", {sub_type: sub_type}, function(rs) {
        if (rs.result == 'Y')
        {
            $('#sub_type_cd').val('');
            $('#sub_type_cd').html('');
            $('#sub_type_cd').append($('<option value="">상세구분</option>'));
            for(idx in rs.code)
            {
                $('#sub_type_cd').append($('<option value="'+ idx +'">'+ rs.code[idx] +'</option>'))
            }
        }
        else
        {
            alert('다시 선택해주세요.');
        }
    }, 'json')
    .fail(function(err){
        alert('오류가 발생하였습니다.');
        console.log(err);
    });
}

function setSettleReason(cd)
{
    var sub_type = $('#sub_type').val();
    if(sub_type == '1' && cd)
    {
        if(cd == '04')
        {
            $('#settle_reason_cd').val('28');
        }
        if(cd == '03')
        {
            $('#settle_reason_cd').val('18');
        }
    }
}

function lumpReqSettle()
{
    var sub_type         = $("#sub_type").val();
    var sub_type_cd      = $("#sub_type_cd").val();
    var settle_reason_cd = $("#settle_reason_cd").val();

    if (sub_type == '' || sub_type_cd == '' || settle_reason_cd == '')
    {
        alert('모두 필수 선택값입니다.');
        return false;
    }

    var cnt = $('input[name="listChk[]"]:checked').length;
    if( cnt==0 )
    {
        alert("일괄 처리할 계약을 선택해주세요.");
        return false;
    }

    if( !confirm( "선택된 "+cnt+"개의 계약에 대하여 일괄처리를 하시겠습니까?" ) )
    {
        return false;
    }

    var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
    // formData.append("lump_action_code", "CHANGE_LOAN_MANAGER");
    formData.append("sub_type",         sub_type);
    formData.append("sub_type_cd",      sub_type_cd);
    formData.append("settle_reason_cd", settle_reason_cd);

    formData.delete('listChk[]');
    var cnt = 0;
    $('input[name="listChk[]"]').each(function (index, item)
    {
        if ($(item).is(':checked'))
        {
            // _ 구분 시 뒤에는 계약번호
            formData.set('listChk['+cnt+']', $(item).val().split('_')[0]);
            cnt++;
        }
    });

    $("#LUMPFORM_BTN_reqSettle").prop("disabled",true);

    $.ajax({
        url  : "/erp/relieflump",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result.result=="Y" )
            {
                alert("일괄처리 완료");
                listRefresh();
                closeLump();
            }
            else
            {
                alert(result.msg);
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
            console.log(xhr);
        }
    }).always(function() {
        $("#LUMPFORM_BTN_reqSettle").prop("disabled",false);
    });

}

</script>

@section('javascript')
<script>
</script>

@endsection
