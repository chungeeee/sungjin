@extends('layouts.masterPop')
@section('content')
<div class="col-12">
    <div class="card">
        <form id="deposit_file" name="deposit_file" onSubmit="return false;">
        @csrf
        <div class="card-header">
            <h3 class="card-title"><i class="far fa-list-alt"></i> 신용회복입금</h3>
            <div class="card-tools">
                <div class="input-group input-group-sm ">
                    <div class="btn-xs btn-default btn-file ">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" name="ccrs_data" value="" onclick="$('#deposit_btn').attr('disabled',true);"   >
                    </div>
                    <div class="input-group-append">
                        <button class="btn-xs btn-primary" onclick="getDepositList('read')" >
                            <i class="fas fa-file-excel"></i>입금업로드
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <input type="hidden" name="deposit_action" id="deposit_action" vlaue="">
        </form>
            
        <!-- /.card-header -->
        <div class="card-body table-responsive p-0" style="height: 500px;">
            <table class="table table-head-fixed text-nowrap table-bordered table-sm">
                <thead>
                    <tr align=center>
                        <th width="5%">구분</th>
                        <th width="7%">처리일자</th>
                        <th width="7%">성명</th>
                        <th width="12%">주민번호</th>
                        <th width="5%">회차</th>
                        <th width="12%">계좌번호</th>
                        <th width="10%">원금</th>
                        <th  width="10%">이자</th>
                        <th  width="10%">기타채무</th>
                        <th  width="10%">납입액계</th>
                        <th>입금가능여부</th>
                    </tr>
                </thead>
                <tbody id="ccrs_body">
                    
                </tbody>
            </table>
        </div>
        <!-- /.card-body -->
        <div class="card-footer">
            <table class="table table-sm table-borderless">
                <tbody>
                    <tr>
                        <th class="text-right">총건수 :</th>
                        <th><input type="text" class="form-control form-control-sm border-0" id="total_cnt" name="total_cnt" value="" readonly></th>
                        <th class="text-right">입금가능건수 :</th>
                        <th><input type="text" class="form-control form-control-sm border-0" id="suc_cnt" name="suc_cnt" value="" readonly></th>
                        <th class="text-right">입금불가능건수 :</th>
                        <th><input type="text" class="form-control form-control-sm border-0" id="fail_cnt" name="fail_cnt" value="" readonly></th>
                        <th class="text-right"><button id="deposit_btn" class="btn btn-success btn-xs" disabled onclick="getDepositList('exec')">입금처리</button></th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
    <!-- /.card -->
</div>
@endsection


@section('javascript')
<script id="ccrs_tmpl" type="text/tmpl">
<tr align=center>
    <td>${ccrs_target}</td>
    <td>${trade_date}</td>
    <td>${name}</td>
    <td>${ssn}</td>
    <td>${seq}</td>
    <td><input type="text" readonly class="form-control form-control-sm" name="account_${no}" value="${ccrs_account}"></td>
    <td><input type="text" readonly class="form-control form-control-border" name="balance_${no}" value="${ccrs_origin}"></td>
    <td><input type="text" readonly class="form-control form-control-border" name="interest_${no}" value="${ccrs_interest}"></td>
    <td><input type="text" readonly class="form-control form-control-border" name="etc_money_${no}" value="${ccrs_etc_money}"></td>
    <td><input type="text" readonly class="form-control form-control-border" name="total_money_${no}" value="${ccrs_total_money}"></td>
    <td>${ccrs_status_str}<input type="hidden" readonly class="form-control form-control-border" name="ccrs_status_${no}" value="${ccrs_status}"></td>
</tr>
 </script>

<!-- Summernote -->
<script>
    $(document).ready(function(){
        window.resizeTo( 1500, 710 );
    });
    //일괄입금내역 가져오기
    function getDepositList(action)
    {
        $('#deposit_action').val(action);
        $('#ccrs_body').html("<tr><td colspan=11>"+loadingString+"</td></tr>");
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        var form        = $('#deposit_file')[0];
        var postdata    = new FormData(form);
        $.ajax({
        url  : "/erp/ccrsdepositexcel",
        type : "post",
        data : postdata,
        processData: false,
        contentType: false,
        dataType : 'json',
        success : function(data)
        {
            $('#ccrs_body').empty();
            $("#ccrs_tmpl").template("ccrs_tmpl");
            if(data.total_cnt>0) $.tmpl("ccrs_tmpl", data.v).appendTo("#ccrs_body");    
            $("#total_cnt").val(data.total_cnt);
            $("#suc_cnt").val(data.suc_cnt);
            $("#fail_cnt").val(data.fail_cnt);
            if(data.rs_code =="Y") $("#deposit_btn").attr('disabled',false);
            else alert(data.rs_msg);
        },
        error : function(xhr)
        {
            alert(result);
        }
        });
    }

</script>
 @endsection
