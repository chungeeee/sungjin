@extends('layouts.master')
@section('content')
@include('inc/list')

@endsection
@section('javascript')
<script>

function requestAction(form)
{
    // if( !confirm("복원 하시겠습니까?") )
    // {
    //     return false;
    // }
    
    alert('데모버전에서는 사용할 수 없는 기능입니다.');
    return false;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var postdata = $('#'+form).serialize();

    if(ccCheck()) return;

    $.ajax({
        url  : "/config/separaterestore",
        type : "post",
        data : postdata,
        success : function(result) {
            globalCheck = false;
            if(result == 'Y'){
                alert('복원되었습니다.');
                location.reload();
            } else if(result == 'A'){
                alert('복원할 대상이 없습니다.');
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