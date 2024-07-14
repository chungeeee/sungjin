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
        };
    });

    $("input[data-bootstrap-switch]").each(function() {
        $(this).bootstrapSwitch('state', $(this).prop('checked'));
    });
}

</script>
@endsection



