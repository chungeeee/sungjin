@extends('layouts.master')
@section('content')
@include('inc/list')

@endsection
@section('javascript')
<script>
function requestActionY(form)
{
    if( !confirm("승인 하시겠습니까?") )
    {
        return false;
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var postdata = $('#'+form).serialize();
    
    if(ccCheck()) return;

    $.ajax({
        url  : "/erp/separaterequesty",
        type : "post",
        data : postdata,
        success : function(result) {
            globalCheck = false;
            if(result == 'Y'){
                alert('승인되었습니다.');
                location.reload();
            } else if(result == 'A'){
                alert('결재할 대상이 없습니다.');
            } else {
                alert('에러가 발생하였습니다.');
            }
        },
        error : function(xhr) {
            globalCheck = false;
            alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
}

function requestActionN(form)
{
    if( !confirm("거절 하시겠습니까?") )
    {
        return false;
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var postdata = $('#'+form).serialize();

    if(ccCheck()) return;

    $.ajax({
        url  : "/erp/separaterequestn",
        type : "post",
        data : postdata,
        success : function(result) {
            globalCheck = false;
            if(result == 'Y'){
                alert('거절되었습니다.');
                location.reload();
            } else if(result == 'A'){
                alert('결재할 대상이 없습니다.');
            } else {
                alert('에러가 발생하였습니다.');
            }
        },
        error : function(xhr) {
            globalCheck = false;
            alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
}
</script>
@endsection