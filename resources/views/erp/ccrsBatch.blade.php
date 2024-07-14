@extends('layouts.masterPop')
@section('content')
<div class="col-12">
    <div class="card">
        <form id="deposit_file" name="deposit_file" onSubmit="return false;">
        <input type="hidden" name="action" id="action" value="">
        @csrf
        <div class="card-header">
            <div class="card-title row col-8">
                <h3 class="col-4"><i class="far fa-list-alt"></i> 신용회복일괄처리</h3>
                 <div  class="col-8">
                    <label for="file_tot_cnt" class="col-sm-3 col-form-label text-sm pr-0">전체건수 : <span id="file_tot_cnt">0</span>건</label>
                    <input class="col-2" type="text" name="file_suc_cnt" id="file_suc_cnt" value="0"><input class="col-2" type="hidden" name="before_file_cnt" id="before_file_cnt" value="0">
                    <label for="file_tot_cnt" class="col-sm-1 col-form-label text-xs pr-0">부터</label>
                    <input class="col-2" type="text" name="file_cnt" id="file_cnt" value="500">
                    <label for="file_cnt" class="col-sm-1 col-form-label text-xs pr-0">건씩</label>
                    <input type="button" class="btn-xs btn-secondary" id="batch_next_btn" value="다음" onclick="batchAction('next')" disabled />
                </div>
            </div>
            <div class="card-tools">
                <div class="input-group input-group-sm ">
                    <select class="form-control form-control-sm " name="div" id="div" >
                        <option value=''>엑셀 선택</option>
                        <option value='APP'>신청인현황</option>
                        <option value='RETURN'>심사반송</option>
                        <option value='SHORT'>계좌별진행상황(약식)</option>
                        <option value='BATCH'>계좌별진행상황</option>
                    </select>
                    <div class="btn-xs btn-default btn-file ">
                        <i class="fas fa-paperclip"></i>
                        <input type="file" name="ccrs_data" id="ccrs_data" value="" onclick="btnUpdate(true);" >
                    </div>
                    <div class="input-group-append">
                        <button class="btn-xs btn-primary" onclick="batchAction('read')"  >
                            <i class="fas fa-file-excel"></i>파일업로드
                        </button>
                    </div>
                </div>
                <div>
                    <br>
                    * 상환스케줄 등록은 약식조회로 진행할수 없습니다.
                </div>
            </div>
        </div>
        </form>
        <!-- /.card-header -->
        <div class="card-body table-responsive p-0" style="height: 500px;">
            <table class="table table-head-fixed text-nowrap table-bordered table-sm">
                <thead>
                    <tr align=center>
                        <th >NO</th>
                        <th>성명</th>
                        <th>주민번호</th>
                        <th>채무구분</th>
                        <th>접수번호</th>
                        <th>계좌번호</th>
                        <th>신청인진행상태</th>
                        <th>계좌진행상태</th>
                        <th>현재상태</th>
                        <th>변경상태</th>
                        <th>처리내용<span id="rsText"></span></th>
                        <th>회파복 현재상태</th>
                        <th>회파복 변경상태</th>
                        <th>처리내용<span id="rsText"></span></th>
                    </tr>
                </thead>
                <tbody id="ccrs_body">
                    
                </tbody>
            </table>
        </div>
        <div class="card-footer text-right">
            <button type="button" class="btn btn-sm bg-lightblue" id="batch_exec_btn" disabled onclick="batchAction('exec');">실행</button>
        </div>
    </div>
    <!-- /.card -->
</div>
@endsection


@section('javascript')
<script id="ccrs_tmpl" type="text/tmpl">
<tr align=center>
    <td>${excel_no}</td>
    <td>${name}</td>
    <td>${ssn}</td>
    <td>${ccrs_target}</td>
    <td>${ccrs_app_no}</td>
    <td>${ccrs_account}</td>
    <td>${ccrs_stat}</td>
    <td>${ccrs_acct_stat}</td>
    <td>${status_str}</td>
    <td>${process}</td>
    <td><span class="text-${result}">${message}</span></td>
    <td>${relief_bef_status}</td>
    <td>${relief_aft_status}</td>
    <td><span class="text-${relief_result}">${relief_message}</span></td>
</tr>
 </script>

<!-- Summernote -->
<script>
    $(document).ready(function(){
        window.resizeTo( 1500, 710 );
    });
    //일괄입금내역 가져오기
    function batchAction(action)
    {
        if(action == 'read')
        {
            $('#batch_next_btn').attr('disabled',false);
            $("#file_suc_cnt").val('0');
        }
        else if(action=='next')
        {
            action = 'read';
            var suc_cnt =  $("#file_suc_cnt").val();
            $("#file_suc_cnt").val(Number(suc_cnt)+Number($("#file_cnt").val()));
        }
        
        $('#action').val(action);
        if($('#div').val()=='')
        {
            alert("엑셀 구분을 선택해주세요.");
            return false;
        }
        
        if(!$('#ccrs_data').val())
        {
            alert("파일을 첨부해주세요.");
            return false;
        }
        
        $('#ccrs_body').html(loadingString);
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        btnUpdate(true);
        var form        = $('#deposit_file')[0];
        var postdata    = new FormData(form);
        $.ajax({
            url  : "/erp/ccrsbatchaction",
            type : "post",
            data : postdata,
            processData: false,
            contentType: false,
            dataType : 'json',
            success : function(data)
            {
                if(data.file_tot_cnt>0)
                {
                    $("#file_tot_cnt").html(data.file_tot_cnt);
                    $('#ccrs_body').empty();
                    if(data.rs_msg!='') alert(data.rs_msg);

                    if(data.rs_code!='X')
                    {
                        $("#ccrs_tmpl").template("ccrs_tmpl");
                        if( data.v != '') $.tmpl("ccrs_tmpl", data.v).appendTo("#ccrs_body");
                    }

                    if(action=='exec')
                    {
                        $('#rsText').text("(실행결과)");
                    }
                    else
                    {
                        $('#rsText').text("(미리보기)");
                        if(data.rs_code=='Y') btnUpdate(false);
                    }
                }
                else
                {
                    alert("데이터가 없습니다.");
                }
            },
            error : function(xhr)
            {
                alert(xhr);
                $('#ccrs_body').empty();
            }
        });
    }

    function btnUpdate(sta)
    {
        $('#batch_exec_btn').attr('disabled',sta);
    }

</script>
 @endsection
