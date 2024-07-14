@extends('layouts.master')
@section('content')
@include('inc/list')

@endsection

@section('javascript')
<script>

function checkall()
{
    for(var i=0; i<document.form_smsLimit.elements.length; i++)
    {
        if(document.form_smsLimit.elements[i].name=="except[]")
        {
            document.form_smsLimit.elements[i].checked = true;
        }
    }
}

function changeDisplay(cd)
{
    var cnt = document.getElementById("cnt_"+cd);
    if(cnt.innerText=="")
    {
        document.getElementById("cnt_"+cd).innerText = "무제한";
        document.getElementById("month_cnt_"+cd).innerText = "무제한";
    }
    else
    {
        document.getElementById("cnt_"+cd).innerText = "";
        document.getElementById("month_cnt_"+cd).innerText = "";
    }
}

function smsSaveClick(form)
{
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#form_smsLimit').serialize();
    
    $.ajax({
            url  : "/config/smslimitaction",
            type : "post",
            data : postdata,
        success : function(result)
        {
            if(result == 'Y'){
                alert('저장 되었습니다.');  
                location.reload();
            } else {
                alert('관리자에게 문의해주세요.');  
            }
        },
        error : function(xhr)
        {
            alert('관리자에게 문의해주세요.');  
        }
    });
}
</script>
@endsection