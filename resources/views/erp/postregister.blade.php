@extends('layouts.master')


@section('content')

<form id="add_ban_form" name="add_ban_form" method="post" enctype="multipart/form-data" action="" onSubmit="return false;">
    @csrf
    <div class="modal fade" id="excelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">등기번호등록</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="row mt-1">
                        <div class="col-md-12 text-black">
                            <label class="col-3 text-left" for="customFile">엑셀 파일 : </label> 
                            <div class="input-group custom-file col-8" id="fileDiv">
                                <input type="file" class="custom-file-input form-control-sm text-sm align-middle" id="customFile">
                                <label id="customFileLabel" class="custom-file-label mb-0 text-sm form-control-sm" for="customFile">파일을 선택해주세요.</label>
                            </div> 
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="col-md-12 text-black">
                            <span class="form-control-sm col-3" style="float:left; font-weight:bold;">우편물종류 : </span> 
                            <div class="input-group date datetimepicker-wol col-md-8 p-0" data-target-input="nearest">
                                <select class="form-control" name="post_code">
                                    <option value="0000000" selected="selected">선택</option>    
                                    <option value="1001006">자필기재약정</option>
                                    <option value="1001010">기한연장계약서</option>
                                    <option value="1001008">기한이익상실예정통지서</option>
                                </select>
                            </div> 
                        </div>
                    </div>
                    <div class="row mt-2">
                        <div class="col-md-12" style="text-align:center;">
                            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">닫기</button>
                            <button type="button" class="btn btn-sm btn-default ml-1" onclick="regPostAction();">등록</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
</form>
@include('inc/list')

@endsection

@section('javascript')
<script>
 bsCustomFileInput.init();


function setSelectBox(nowTabs)
{
    var tabsSelect = nowTabs;

    $("#lump_value").empty();
    $("#lump_value").append("<option value=''>선택</option>");

    for(var i in arrayYN[tabsSelect]){
        $("#lump_value").append('<option value="'+i+'">'+arrayYN[tabsSelect][i]+'</option>');
    }

    $("#lump_value").selectpicker('refresh');
}

function regPost()
{
    $("#excelModal").modal('show');
}
// 등기번호등록
function regPostAction()
{
    if( $("#post_code").val()=="" || $("#post_code").val()=="0000000" )
    {
        $("#post_code").focus();
        alert("등록하실 우편물종류를 선택해주세요");
        return false;
    }
    if( $("#customFile").val()=="" )
    {
        $("#customFile").focus();
        alert("엑셀 파일을 선택해주세요");
        return false;
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = new FormData($('#add_ban_form')[0]);

    if( $('#customFile')[0].files[0] )
    {
        postdata.append('fileObj', $('#customFile')[0].files[0]);
    }

    // target_sql 붙이기
    postdata.append('target_sql', $('#target_sql').val());

    if(ccCheck()) return;

    if( !confirm("정말로 등록하시겠습니까?") )
    {
        globalCheck = false;
        return false;
    }

    $.ajax({
        url  : "/erp/postregisteraction",
        type : "post",
        data : postdata,
        dataType : 'json',
        processData : false,
        contentType : false,
        success : function(result) {
            globalCheck = false;
            alert(result.rs_msg);
            if (result.rs_code == 'Y') {
                listRefresh();
                $("#excelModal").modal('hide');
                $('#customFile').val('');
                $('#suc_cnt').text(result.suc_cnt);
                $('#fail_cnt').text(result.fail_cnt);
                $('#customFileLabel').text('파일을 선택해주세요.');
            }else{
                $('#suc_cnt').text(result.suc_cnt);
                $('#fail_cnt').text(result.fail_cnt);
            }
        },
        error : function(xhr) {
            globalCheck = false;
            alert("통신오류입니다. 관리자에게 문의해주세요.");			
        }
    });

}

function checkOneMore()
{
    var checked = $('input[name="listChk[]"]:checked').length > 0;
    if(checked !== true)
    {
        return false;
    }
    else
    {
        return true;
    }
}

function lump_del(btn_obj){
    if( checkOneMore()===false )
    {
        alert("체크박스를 선택해주세요");
        return false;
    }
    if(!confirm("선택하신 내역을 삭제하시겠습니까?\n삭제하시면 복구할 수 없으며 필요시 재등록해야합니다."))
    {
        return false;
    }

    var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
    formData.append("action_mode", "LUMP_POSTREGISTER_DELETE");

    btn_obj.disabled = true;
    $("#"+btn_obj.id).html(loadingStringtxt);

    $.ajax({
        url  : "/erp/postregisterdelete",
        type : "post",
        data : formData,
        dataType : 'json',
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result.rs_code == 'Y' )
            {
                alert(result.rs_msg);
                listRefresh();
            }
            else
            {
                alert(result.rs_msg);
            }

            btn_obj.disabled = false;
            $("#"+btn_obj.id).html("삭제");
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
        }
    });
}
</script>

@endsection
