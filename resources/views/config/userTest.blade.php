@extends('layouts.master')
@section('content')
@include('inc/list')
@endsection








@section('lump')
일괄처리할거 입력
@endsection








@section('javascript')
<script>
function goPopup(no, cno, blanketNo)
{
    window.open('/config/userframe/info/' + no + '/' + cno + '?blanket=' + blanketNo, "loaninfo" + no, "top=0,left=0,height=950,width=1600,scrollbars=no");
}
</script>
@endsection