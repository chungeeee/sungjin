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


function loan_doc_pop( cin, lin, cnd, cnt )
{
	if( cnd==undefined )
	{
		cnd = "";
	}
	if( cnt==undefined )
	{
		cnt = "";
	}
	var wnd = window.open("/erp/docinfo?cust_info_no="+cin+"&loan_info_no="+lin+"&condition="+cnd+"&cnt="+cnt,"loan_doc_pop","width=2000, height=1000, scrollbars=yes");
	wnd.focus();
}



enterClear();

</script>
@endsection