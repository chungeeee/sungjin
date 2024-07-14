@extends('layouts.masterPop')



<title>고객등록</title>
@section('content')

<form  class="mb-0" name="loan_app_form" id="loan_app_form" method="post" enctype="multipart/form-data" >
    <div class="content-wrapper needs-validation m-0">
        @csrf
        <input type="hidden" id="action_mode" name="action_mode" value="INS">

   {{-- 입력정보별 구분 시작--}}
        <div class="col-md-12 pt-4">
            <div class="card card-outline card-lightblue">

                <div class="card-header p-1">
                    <h3 class="card-title"><i class="fas fa-donate m-2"></i>고객등록</h3>
                    <div class="card-tools pr-2">
                        <button type="button" class="btn btn-tool m-1" data-card-widget="collapse">
                        <i class="fas fa-minus"></i>
                        </button>
                    </div>
                </div>

                <div class="card-body p-1">
                    <div class="fade show active p-2" id="customer-contents"></div>
                </div>
            </div>
        </div>
        {{-- 입력정보 구분 끝 --}}
    </div>

</form>
@endsection

@section('javascript')

<script>
// 로드시 스크롤위치 조정
$(document).ready(function(){
    $(window).scrollTop(0);
});

// 고객정보 (md:탭, div_no:탭에서 사용할 no)
function getCustData(md,div_no,select)
{
    // CORS 방지
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 현재 선택된 메뉴가 무엇인지 저장
    $("#cust_select").val(md);

    // 고객정보 받아오기
    var url = "/erp/customer"+md;
    $("#customer-contents").html(loadingString);     

    $.post(url, { mode:md, selected:select }, function(data) {
        $("#customer-contents").html(data);
        afterAjax();
    });
}


getCustData('info');

/**
*   (공통) 직업코드 검색 팝업
*   jobId : 최종코드저장 ID 
*   전달된 파라미터 기준 ID+1~4 있으면 세팅
*   전달된 파라미터 기준 ID+name 1~4 있으면 세팅
*   전달된 파라미터 기준 ID+str 전체 name text 세팅 
*/
function getJobCode(jobId)
{
    window.open("/config/jobcodepop?jobId="+jobId, "msgInfo", "width=800, height=350, scrollbars=no");
}


</script>
@endsection