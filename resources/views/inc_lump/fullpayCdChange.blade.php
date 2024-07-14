

<div id="LUMP_FORM_fullpayCdChange" class="lump-forms" style="display:none">
    <div class="row">
        <div class="col-md-12">
            <select class="form-control form-control-sm selectpicker pr-0" name="lump_fullpay_cd" id="lump_fullpay_cd">
            <option value=''>완제사유 선택</option>
                @php Func::printOption(Func::getConfigArr('flpay_cd')); @endphp
            </select>
        </div>
    </div> 
    <div class="row p-1">
        <div class="col-md-12 text-right pt-2">
            완제상태가 "완제" 인 경우만 수정됩니다.
        <button class="btn btn-sm btn-info ml-4" id="LUMPFORM_BTN_fullpayCdChange" onclick="lumpChangeFullpayCd(); return false;">완제사유코드 수정</button>
        </div>
    </div> 

</div>

<script>

function lumpChangeFullpayCd()
{

    var cnt = $('input[name="listChk[]"]:checked').length;
    if( cnt==0 )
    {
        alert("완제사유코드를 변경할 계약을 선택해주세요.");
        return false;
    }
    if( !confirm( "선택된 "+cnt+"개의 계약에 대하여 담당자를 변경하시겠습니까?" ) )
    {
        return false;
    }

    var lump_fullpay_cd = $("#lump_fullpay_cd").val();
    

    var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
    formData.append("lump_action_code",  "CHANGE_FULLPAY_CD");
    formData.append("lump_fullpay_cd",   lump_fullpay_cd);

    $("#LUMPFORM_BTN_fullpayCdChange").prop("disabled",true);

    $.ajax({
        url  : "/erp/fullpaymentlumpaction",
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
            $("#LUMPFORM_BTN_fullpayCdChange").prop("disabled",false);
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
            $("#LUMPFORM_BTN_fullpayCdChange").prop("disabled",false);
        }
    });

}

</script>