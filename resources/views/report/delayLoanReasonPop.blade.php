@extends('layouts.masterPop')
@section('content')
<div class="card card-lightblue">
    <div class="card-header-no-round">
    <h2 class="card-title">계약검색 - {{ Func::getConfigArr('delay_rsn_cd')[$v['pop_delay_rsn_cd']] }} </h2>
    </div>
</div>

<div class="row pt-0 p-2 ">
    {{-- 리스트 --}}
    @include('inc.list')
</div>

@endsection

@section('javascript')

<style>

</style>
<script type="text/javascript" src="/js/tiff.min.js"></script>

<script>
    $("<input>").attr({name: "pop_status", type: "hidden",value:"{{$v['pop_status']}}" }).appendTo("#form_delayloanleasonpop");
    $("<input>").attr({name: "pop_manager_code",type: "hidden",value:"{{$v['pop_manager_code']}}" }).appendTo("#form_delayloanleasonpop");
    $("<input>").attr({name: "info_date",type: "hidden",value:"{{$v['info_date']}}" }).appendTo("#form_delayloanleasonpop");
    $("<input>").attr({name: "pop_delay_rsn_cd",type: "hidden",value:"{{$v['pop_delay_rsn_cd']}}" }).appendTo("#form_delayloanleasonpop");

//var postdata = $('#form_{{ $result['listName'] }}').serialize();
//postdata = postdata+"&pop_manager_code={{$v['pop_manager_code']}}&pop_status={{$v['pop_status']}}&info_date={{$v['info_date']}}&pop_delay_rsn_cd={{$v['pop_delay_rsn_cd']}}";
getDataList('{{ $result['listName'] }}', 1, 'delayloanreasonpoplist', $('#form_{{ $result['listName'] }}').serialize());



</script>

@endsection

