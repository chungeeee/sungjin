@extends('layouts.master')


@section('content')
@include('inc/list')
<!-- 계약명세 모달 -->
@endsection


@section('lump')
일괄처리할거 입력
@endsection

@section('javascript')
<script>

function withholdingPopup()
{
    //중앙위치 구해오기
    width  = 900;
    height = 800;

    LeftPosition =(screen.width-width)/2;
    TopPosition  =(screen.height-height)/2;

    var wnd = window.open("/account/withholdingpop", "withholdingpop","width="+width+", height="+height+",top="+TopPosition+",left="+LeftPosition+", scrollbars=yes");
    wnd.focus();
}

// 엔터막기
function enterClear()
{
    $('input[type="text"]').keydown(function() {
      if (event.keyCode === 13)
      {
        event.preventDefault();
        listRefresh();
      };
    });

    $("input[data-bootstrap-switch]").each(function() {
    $(this).bootstrapSwitch('state', $(this).prop('checked'));
  });
}


enterClear();
</script>
@endsection