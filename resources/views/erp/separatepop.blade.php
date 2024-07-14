@extends('layouts.masterPop')

@section('content')
<form id="request_separate">
    <input type="hidden" name="request_id" value="{{ $result['request_id'] ?? '' }}" >
    <div class="card" id="container">
        <div class="card-header">
            <h3 class="card-title">
            <i class="fas fa-envelope mr-1"></i>
            분리보관 요청사유
            </h3>
            <button type="button" class="close" onclick="window.close()">
                <span aria-hidden="true">×</span>
            </button>
        </div>
        <div class="card-body">
            <table class="table table-sm">
            <tr>
                <th>요청자</th>
                <td>{{ $arrayConf[$result['request_id']] }}</td>
            </tr>
            <tr>
                <th>요청사유</th>
                <td>
                    <textarea style='height:120px;' class="form-control form-control-sm" name="approval_reason" id="approval_reason"></textarea>
                </td>
            </tr>
            <tr>
                <td colspan=3>
                    <button type="button" class="btn btn-default float-right btn-xs" onclick="requestAction();"><i class="fas fa-user mr-1 text-gray"></i>요청등록</button>
                </td>
            </tr>
            </table>
        </div>
    </div>
</form>
@endsection

@section('javascript')
<script>
// 로드시 스크롤위치 조정
$(document).ready(function(){
    $(window).scrollTop(0);
});

function requestAction()
{
    if( $("#approval_reason").val()=="" )
    {
        alert("요청사유를 입력해주세요");
        $("#approval_reason").focus();
        return false;
    }
    
    if(ccCheck()) return;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#request_separate').serialize();

    $.post(
        "/erp/separaterequest", 
        postdata, 
        function(result) {
            if(result == 'Y'){
                alert('정상처리 되었습니다.');  
                opener.document.location.reload();
                self.close();
            } else {
                alert('관리자에게 문의해주세요.');  
            }
            globalCheck = false;
        }
    );
}
</script>
@endsection