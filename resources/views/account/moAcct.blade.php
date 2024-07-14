@extends('layouts.master')
@section('content')
@include('inc/list')
@endsection

@section('javascript')
<script>

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

function moAcctForm(n)
{
    //중앙위치 구해오기
    width  = 800;
    height = 300;

    LeftPosition =(screen.width-width)/2;
    TopPosition  =(screen.height-height)/2;

    var wnd = window.open("/account/moacctform?no="+n, "moacctpop","width="+width+", height="+height+",top="+TopPosition+",left="+LeftPosition+", scrollbars=yes");
    wnd.focus();
}

</script>
@endsection
