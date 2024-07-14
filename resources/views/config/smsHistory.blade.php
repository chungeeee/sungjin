@extends('layouts.master')
@section('content')
@include('inc/list')

@endsection

@section('javascript')
<script>

function lump_del(btn_obj)
{   
    if(!isCheckboxChecked('listChk[]'))
    {
        alert('선택한 내역이 없습니다. 삭제할 내역을 선택 후 이용해 주세요.');
        return;
    }
    
    if(!confirm("선택하신 문자발송내역을 삭제하시겠습니까?"))
    {
        return false;
    }
    //var formData = getArrayCheckbox('listChk[]');
    var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
        btn_obj.disabled = true;
    $("#"+btn_obj.id).html(loadingStringtxt);

    $.ajax({
        url  : "/config/smshistoryaction",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result=="Y" )
            {
                alert("삭제가 완료됐습니다.");
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
</script>
@endsection