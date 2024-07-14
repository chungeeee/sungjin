

<div id="LUMP_FORM_changeDocManager" class="lump-forms" style="display:none">

    <div class="row p-1">
        <label for="change_doc_manager_id" class="col-sm-3 col-form-label">계약서담당자</label>
        <div class="col-md-9">
            <select class="form-control form-control-sm selectpicker" name="change_doc_manager_id" id="change_doc_manager_id">
            <option value=''>담당</option>
            {{ Func::printOption(Func::getDocManagerList()) }}
            </select>
        </div>
    </div> 

    <div class="row p-1">
        <label for="btn" class="col-sm-3 col-form-label"></label>
        <div class="col-md-9">
        <button class="btn btn-sm btn-info" id="LUMPFORM_BTN_changeDocManager" onclick="lumpChangeDocManager(); return false;">담당자변경 실행</button>
        </div>
    </div> 

</div>



<script>

function lumpChangeDocManager()
{
    var cnt = $('input[name="listChk[]"]:checked').length;
    if( cnt==0 )
    {
        alert("담당자 변경할 계약을 선택해주세요.");
        return false;
    }
    if( !confirm( "선택된 "+cnt+"개의 계약에 대하여 담당자를 변경하시겠습니까?" ) )
    {
        return false;
    }

    var change_doc_manager_id = $("#change_doc_manager_id").val();

    var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
    formData.append("lump_action_code",      "CHANGE_DOC_MANAGER");
    formData.append("change_doc_manager_id", change_doc_manager_id);


    $("#LUMPFORM_BTN_changeDocManager").prop("disabled",true);

    $.ajax({
        url  : "/erp/lumpchangemanager",
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
            $("#LUMPFORM_BTN_changeDocManager").prop("disabled",false);
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
            $("#LUMPFORM_BTN_changeDocManager").prop("disabled",false);
        }
    });

}

</script>