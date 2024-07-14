@extends('layouts.master')
@section('content')
    @include('inc/list')
@endsection

@section('lump')
일괄처리할거 입력
@endsection




@section('javascript')
<script>

function conditionPopup()
{
    //중앙위치 구해오기
    width  = 900;
    height = 800;

    LeftPosition =(screen.width-width)/2;
    TopPosition  =(screen.height-height)/2;

    var wnd = window.open("/erp/conditionpop", "conditionpopup","width="+width+", height="+height+",top="+TopPosition+",left="+LeftPosition+", scrollbars=yes");
    wnd.focus();
}

// 엔터막기
function enterClear()
{
    $('input[type="text"]').keydown(function() {
        if (event.keyCode === 13)
        {
            event.preventDefault();
        };
    });

    $("input[data-bootstrap-switch]").each(function() {
        $(this).bootstrapSwitch('state', $(this).prop('checked'));
    });
}

// 일괄금리인하
function lumpRateDownForm()
{
    if( $("#tabsSelectcondition").val()!='R' )
    {
        alert("일괄금리인하요청 탭에서만 실행 가능합니다.");
        return false;
    }
    lump_btn_click('lumpRateDown', '일괄금리인하');
}



</script>
@endsection



