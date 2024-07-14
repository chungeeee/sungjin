@extends('layouts.master')


@section('content')

@include('inc/list')
<form id="add_ban_form" name="add_ban_form" method="post" enctype="multipart/form-data" action="" onSubmit="return false;">
    @csrf
    <div class="modal fade" id="excelModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">개인정보노출자등록</h5>
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
                    <div class="row mt-2">
                        <div class="col-md-12" style="text-align:center;">
                            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">닫기</button>
                            <button type="button" class="btn btn-sm btn-default ml-1" onclick="regExposureAction();">등록</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.modal-content -->
        </div>
        <!-- /.modal-dialog -->
    </div>
</form>
@endsection

@section('lump')
일괄처리할거 입력
@endsection

@section('javascript')
<script>
     bsCustomFileInput.init();

function regExposure()
{
    $("#excelModal").modal('show');
}
// 등기번호등록
function regExposureAction()
{
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
        url  : "/erp/infoexposureaction",
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
                $('#customFileLabel').text('파일을 선택해주세요.');
            }else{
                
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
    formData.append("action_mode", "LUMP_INFOEXPOSURE_DELETE");

    btn_obj.disabled = true;
    $("#"+btn_obj.id).html(loadingStringtxt);

    $.ajax({
        url  : "/erp/infoexposuredelete",
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
