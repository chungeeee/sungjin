

<div id="LUMP_FORM_lumpRateDown" class="lump-forms" style="display:none">

    <div class="row p-1">
        <label for="change_manager_code" class="col-sm-3 col-form-label">금리적용일</label>
        <div class="col-md-9">
            <div class="input-group date datetimepicker" id="div_lump_basis_date" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm" id="lump_basis_date" name="lump_basis_date" DateOnly='true' placeholder="금리적용일" value="{{ date('Y-m-d') }}"/>
                <div class="input-group-append" data-target="#div_lump_basis_date" data-toggle="datetimepicker">
                    <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                </div>
            </div>
        </div>
    </div> 

    <div class="row p-1">
        <label for="btn" class="col-sm-3 col-form-label"></label>
        <div class="col-md-9">
        <button class="btn btn-sm btn-info" id="LUMPFORM_BTN_lumpRateDown" onclick="lumpRateDownFormExec(); return false;">일괄금리인하 실행</button>
        </div>
    </div>

</div>




<script>

function lumpRateDownFormExec()
{
    var cnt = $('input[name="listChk[]"]:checked').length;
    if( cnt==0 )
    {
        alert("일괄금리인하를 실행할 요청내역을 선택해주세요.");
        return false;
    }
    if( !confirm( "선택된 "+cnt+"개의 계약에 대하여 일괄금리인하를 실행하시겠습니까?" ) )
    {
        return false;
    }


    var lump_basis_date = $("#lump_basis_date").val();

    var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
    formData.append("lump_action_code", "LOAN_RATE_DOWN");
    formData.append("lump_basis_date",  lump_basis_date);


    $("#LUMPFORM_BTN_lumpRateDown").prop("disabled",true);

    $.ajax({
        url  : "/erp/conditionlumpaction",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result=="Y" )
            {
                alert("일괄처리 완료");
                listRefresh();
                closeLump();
            }
            else
            {
                alert(result);
            }
            $("#LUMPFORM_BTN_lumpRateDown").prop("disabled",false);
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
            $("#LUMPFORM_BTN_lumpRateDown").prop("disabled",false);
        }
    });

}

</script>