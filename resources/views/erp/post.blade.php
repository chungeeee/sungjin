@extends('layouts.master')
@section('content')
@include('inc/list')



@endsection

@section('lump')
일괄처리할거 입력
@endsection

@section('javascript')
<script>

function getMemo(no)
{
    var url = "/erp/postgetmemo";
    var formdata = "no="+no;
    var item = '#memo' + '' + no;
    var title = 'DM 메모';

    jsonAction(url, 'POST', formdata, function (data) {
        var memo = '';
        data.forEach(function(v) {
            memo += "<div class='popover-title h6 mt-1'>" + v.save_id + "(" + v.save_time + ") </div>";
            memo += "<div class='popover-content underline mb-2'>" + v.memo + "</div>"; 
        });
        viewPopover(item, title, memo);
    });
}
</script>
@endsection
