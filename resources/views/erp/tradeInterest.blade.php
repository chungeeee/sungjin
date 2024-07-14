@extends('layouts.master')

@section('content')

@include('inc/list')
<!-- 계약명세 모달 -->



@endsection

@section('javascript')
<script>




// 엔터막기
function enterClear()
{
    $('input[type="text"]').keydown(function() {
      if( event.keyCode === 13 )
      {
        event.preventDefault();
        listRefresh();
      };
    });

    $("input[data-bootstrap-switch]").each(function() {
    $(this).bootstrapSwitch('state', $(this).prop('checked'));
  });
}


function checkOneMore()
{
    var checked = $('input[name="listChk[]"]:checked').length > 0;
    if(checked !== true)
    {
        return false;
    }
    else
    {
        return true;
    }
}

enterClear();

</script>
@endsection