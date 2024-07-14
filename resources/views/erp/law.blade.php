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




// 일괄처리 클릭
function lawXmlClick(lumpcd, btnName)
{
    // 탭 상태에 따라 보여줄 내용 결정.
    var nowTabs = $("#tabsSelect{{ $result['listName'] ?? '' }}").val();
    var none = true;    

    lump_btn_click(lumpcd, btnName);
    afterAjax();

}





enterClear();

</script>
@endsection