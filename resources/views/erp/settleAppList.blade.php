@extends('layouts.masterPop')
@section('content')
<div class="col-12">
    <div class="card card-outline card-info">
        <form id="deposit_file" name="deposit_file" onSubmit="return false;">
        @csrf
        <div class="card-header">
            <h3 class="card-title"><i class="far fa-list-alt"></i> &nbsp;화해신청List</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm p-1">
                    @foreach ($sub_type as $key => $val)
                        <? 
                        if(!Func::funcCheckPermit("R041") && ($key=='2'  || $key=='3') )
                        {
                            continue;
                        }
                        ?>
                        <div class="custom-control custom-radio pr-3 pt-1">
                            <input class="custom-control-input" type="radio" id="settleRadio{{$key ?? ''}}" name="settleRadio" value="{{ $key ?? ''}}" checked>
                            <label for="settleRadio{{$key ?? ''}}" class="custom-control-label" style="padding-top:3px;">{{ $val ?? ''}}</label>
                        </div>
                    @endforeach
                    <div class="input-group-append btn-xs btn-default">
                        <button class="btn-xs btn-primary" onclick="goSettle('N','')" >
                            <i class="fas fa-edit"></i>화해신청
                        </button>
                    </div>
                </div>
            </div>
        </div>
        </form>
        <!-- /.card-header -->
        <div class="card-body table-responsive p-0" style="height: 500px;">
            <table class="table table-head-fixed text-nowrap table-bordered table-sm table-hover">
                <thead>
                    <tr align=center>
                        <th >NO</th>
                        <th>구분</th>
                        <th>화해사유</th>
                        <th>접수일</th>
                        <th >화해일</th>
                        <th >화해완료일</th>
                        <th>상태</th>
                        <th >진행회차/총회차</th>
                        <th>최종저장시간</th>
                    </tr>
                </thead>
                <tbody>
                    @if(isset($list))
                    @foreach ( $list as $v )
                        <tr align=center onclick="goSettle('{{$v->no ?? ''}}','{{$v->sub_type ?? ''}}')">
                            <td>{{ $v->no ?? ''}}</td>
                            <td>{{ $v->sub_type_str ?? ''}}</td>
                            <td>{{ $v->settle_reason_cd ?? ''}}</td>
                            <td>{{ $v->app_date ?? ''}}</td>
                            <td >{{ $v->settle_date ?? ''}}</td>
                            <td >{{ $v->settle_end_date ?? ''}}</td>
                            <td>{{ $v->status ?? ''}}</td>
                            <td >{{ $v->trade_cnt ?? '0'}}/{{ $v->settle_cnt ?? ''}}</td>
                            <td>{{ $v->save_time ?? ''}}</td>
                        </tr>
                    @endforeach
                    @endif
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.card -->
</div>
@endsection


@section('javascript')
<script id="ccrs_tmpl" type="text/tmpl">

 </script>

<!-- Summernote -->
<script>

    //일괄입금내역 가져오기
    function goSettle(settle_no,div)
    {
        if(div=='') var div = $(":input:radio[name=settleRadio]:checked").val();
        

        if(isEmpty(div)) return 


        getPopUp('/erp/settleform/'+settle_no+'?loan_info_no={{$loan_info_no ?? ''}}&sub_type='+div+'&direct_div=Y',"settle","width=2000, height=1000, scrollbars=yes")
    }

</script>
 @endsection
