@extends('layouts.masterPop')
@section('content')
<style>

    /* 상단고정 */
    #fixed_top {    
    position: fixed;
    width: calc(75% - 10px);
    height: 38px;
    z-index:101;
    margin-left:0px;
    margin-bottom:0px;
    padding-left:0px;
    }

    /* 컨텐츠 높이 조정 */
    #splitParent {
        top: 42.5px;
        z-index:100;
    }

    .pagination {
        margin-bottom:0px;
    }
    .content::-webkit-scrollbar{
        width: 8px;
        height: 10px;
    }
    .content::-webkit-scrollbar-button {
        width: 8px;
    }
    .content::-webkit-scrollbar-thumb {
        background: #999;
        border: thin solid gray;
        border-radius: 10px;
    }
    .content::-webkit-scrollbar-track {
        background: #eee;
        border: thin solid lightgray;
        box-shadow: 0px 0px 3px #dfdfdf inset;
        border-radius: 10px;
    }


    .user-nav-link {
    display: block;
    padding: 0.3rem 0.2rem;
    border: 1px solid transparent;
    border-top-left-radius: 0.25rem;
    border-bottom-left-radius: 0.25rem;
    }

    .form-inline {
        justify-content: flex-end;
        margin-top:7px;
    }

</style>
<form  class="m-2" name="form_undivideschedules" id="form_undivideschedules" method="post" enctype="multipart/form-data" >
    <div class="content-wrapper needs-validation m-0">
        @csrf

        <div class="content-wrapper m-0" >
            <div class="row m-0">

        {{-- 수익분배대상 --}}
        <div class="col-md-12">
            <div class="card card-outline card-lightblue">
                <div class="col-md-12">
                    <h6 class="card-title" style='margin:12px;'><i class="fas fa-user m-2" size="9px"></i>잔여스케줄 정보조회</h6>
                    @include('inc/listSimple')
                </div>
            </div>
        </div>

    </div>
</form>

@endsection

@section('javascript')

<script>
// 로드시 실행
$(document).ready(function(){
    // 스크롤위치 조정
    $(window).scrollTop(0);
    
    // 팝업창에서 바로 실행
    getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());
    
    // listsimple.blade에 초기화 버튼 생성
    var resetButtonHTML = `<button type="button" title="새로고침" class="btn btn-sm btn-default float-center mr-2" onclick="resetPage()" style="height:31px; width:33px; margin-left:5px;"><i class="fas fa-sync"></i></button>`;
    $('#searchBox').append(resetButtonHTML);

    // listsimple.blade에 엑셀다운로드 버튼 생성
    var undivideExcelButtonHTML = `<button type="button" class="btn btn-sm btn-success mr-2" onclick="undivideSchedulesExcel()">엑셀다운</button>`;
    $('#searchBox').prepend(undivideExcelButtonHTML);

    // 모든 input태그 자동완성 기능 비활성화
    $('input').attr('autocomplete', 'off');
    
});

// 엔터막기
function enterClear()
{
    $('input[type="text"]').keydown(function() {
        if (event.keyCode === 13)
        {
            event.preventDefault();
            getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());
        };
    });

    $("input[data-bootstrap-switch]").each(function() {
        $(this).bootstrapSwitch('state', $(this).prop('checked'));
    });
}

enterClear();

// 초기화 버튼 클릭시
function resetPage()
{
    // 정렬 버튼 초기화
    $('.orderIcon').removeClass('fas fa-arrow-down');
    $('.orderIcon').removeClass('fas fa-arrow-up');
    $('#listOrder{{ $result['listName'] }}').val(null);
    $('#listOrderAsc{{ $result['listName'] }}').val(null);

    // 모든 input태그 초기화
    $('input[type="text"]').val(null);

    getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());
}

// 잔여스케줄 엑셀다운로드
function undivideSchedulesExcel()
{
    $('#form_undivideschedules').attr('action', '/account/undivideschedulesexcel');
    $('#form_undivideschedules').submit();
}


</script>
@endsection