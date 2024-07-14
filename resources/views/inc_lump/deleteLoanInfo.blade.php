

<div id="LUMP_FORM_deleteLoanInfo" class="lump-forms" style="display:none">

    <div class="row p-1">
        <label for="change_manager_id" class="col-sm-3 col-form-label">삭제자</label>
        <div class="col-md-9">
            <input type='text' id='del_id' name='del_id' value='{{Func::getUserId(Auth::id())->name}}' readonly>
        </div>
    </div> 

    <div class="row p-1">
        <label for="btn" class="col-sm-3 col-form-label"></label>
        <div class="col-md-9">
        <button class="btn btn-sm btn-info" id="LUMPFORM_BTN_changeManager" onclick="lumpDeleteLoan(); return false;">휴지통</button>
        </div>
    </div> 

</div>



<script>

function lumpDeleteLoan()
{
    var cnt = $('input[name="listChk[]"]:checked').length;

    if( !confirm( "선택된 "+cnt+"개의 계약에 대하여 삭제처리를 진행하시겠습니까?" ) )
    {
        return false;
    }

    var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);

    @if (!empty($location) && $location == 'relief')
    {{-- relief는 reliefno를 넘겨서 하는 처리 --}}
    formData.delete('listChk[]');
    var cnt = 0;
    $('input[name="listChk[]"]').each(function (index, item)
    {
        if ($(item).is(':checked'))
        {
            // _ 구분 시 뒤에는 계약번호
            formData.set('listChk['+cnt+']', $(item).val().split('_')[1]);
            cnt++;
        }
    });
    @endif

    $("#LUMP_FORM_deleteLoanInfo").prop("disabled",true);

    $.ajax({
        url  : "/erp/lumpdeleteloaninfo",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result=="Y" )
            {
                alert("처리완료되었습니다.");
                listRefresh();
                closeLump();
            }
            else
            {
                alert(result);
            }
            $("#LUMP_FORM_deleteLoanInfo").prop("disabled",false);
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
            $("#LUMP_FORM_deleteLoanInfo").prop("disabled",false);
        }
    });
}

</script>

@section('javascript')

@endsection
