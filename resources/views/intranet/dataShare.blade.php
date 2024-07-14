@extends('layouts.master')
@section('content')

<form class="form-horizontal" name="datashare" id="datashare_form" method="post" >
    @csrf
    <input type="hidden" name="sel_date" id="sel_date">
    <input type="hidden" name="sel_file" id="sel_file">
    <div class="p-2">
        <h6>● {{ $branch->branch_name }}</h6>
        <table class="table table-sm table-hover loan-info-table card-secondary card-outline table-bordered" style='width:650px'>
        <colgroup>     
            <col width="150"/>
            <col width="500"/>
        </colgroup>
        <thead>
            <tr class="text-center">
                
                <th>구분</th>
                <th>데이터선택</th>

                
            </tr>
        </thead>
        <tbody>   
            @if($arrayFilesMon!=null)
            <tr>
                <td align="center" class="p-2">월별자료</td>
                <td class="p-2">                        
                    <div class="input-group date mt-0 mb-0 datetimepicker-wol mr-1 mb-1 mt-1 " id="searchWolString" data-target-input="nearest" style="width:120px">
                        <input type="text" class="form-control form-control-sm datetimepicker-wol" data-target="#searchWolString" name="search_mon" id="search_mon" DateOnly="true" value="{{ date("Y-m", strtotime("-1 month")) }}" size="6">
                        <div class="input-group-append" data-target="#searchWolString" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                    
                    <select class="form-control form-control-sm selectpicker" name="file_mon" id="file_mon">
                    <option value=''>파일선택</option>
                    {{ Func::printOption($arrayFilesMon, '') }}
                    </select>
                    
                    <button class="btn btn-sm bg-lightblue" type="button" onclick="goDownload('mon');" >다운로드</button>
                </td>
            </tr>
            @endif

            @if($arrayFilesDay!=null)
            <tr>
                <td align="center" class="p-2">일별자료</td>
                <td class="p-2">                        
                    <div class="input-group date mt-0 mb-0 datetimepicker mr-1 mb-1 mt-1 " id="searchDayString" data-target-input="nearest" style="width:120px">
                        <input type="text" class="form-control form-control-sm datetimepicker" data-target="#searchDayString" name="search_day" id="search_day" DateOnly="true" value="{{ date("Y-m-d", time()-86400) }}" size="6">
                        <div class="input-group-append" data-target="#searchDayString" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                    
                    <select class="form-control form-control-sm selectpicker" name="file_day" id="file_day">
                    <option value=''>파일선택</option>
                    {{ Func::printOption($arrayFilesDay, '') }}
                    </select>
                    
                    <button class="btn btn-sm bg-lightblue" type="button" onclick="goDownload('day');" >다운로드</button>
                </td>
            </tr>
            @endif

        </tbody>
        </table>
    </div>
</form>    

@endsection

@section('javascript')
<script>
 
function goDownload(tp)
{
    var selDate = $('#search_'+tp);
    var selFile = $('#file_'+tp);
    
    if(!selDate.val())
    {
        alert('다운로드 받을 자료의 날짜를 선택해 주세요');
        selDate.focus();
        return false;
    }
    if(!selFile.val())
    {
        alert('다운로드 받을 자료를 선택해 주세요');
        selFile.focus();
        return false;
    }
    
    if(ccCheck()) return;

            
    $('#sel_date').val(selDate.val());
    $('#sel_file').val(selFile.val());

    var f = document.getElementById('datashare_form');

    f.action = "/intranet/datasharedownload";
    f.method = 'POST';
    f.submit();
    globalCheck = false;
}

</script>
@endsection