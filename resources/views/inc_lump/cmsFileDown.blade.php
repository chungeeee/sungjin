<div id="LUMP_FORM_cmsFileDown" class="lump-forms" >
    <form name="cmsFileDownForm" id="cmsFileDownForm" method="post" action="">
        <input type="hidden" name="cms_code" id="cms_code" value="" >
        <input type="hidden" name="cms_file_no" id="cms_file_no" value="" >
        @csrf    
        <div class="card card-outline primary" id="changeManager">
            <div class="card-header flex-column status-border-right-none">
                <h5 class="card-title text-bold pt-1" style='color:black'>
                    CMS 출금의뢰 파일 대상 등록
                </h5>
            </div>
            <div class="card-body">
                <div class="row p-1">
                    <label for="div_cms_send_date" class="col-sm-3 col-form-label" style="color:black">출금요청일자</label>
                    <div class="col-md-9">
                        <div class="input-group date datetimepicker " id="div_cms_send_date" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm" id="cms_send_date" name="cms_send_date" DateOnly='true'  value="{{ date('Y-m-d') }}" />
                            <div class="input-group-append" data-target="#div_cms_send_date" data-toggle="datetimepicker">
                                <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </div> 
                <div class="col-12"  id="cmsfileres"></div>
                <div class="offset-3 sdate_tip text-grey"></div>
            </div>
            <!-- /.card-body -->
            <div class="card-footer" id="input_footer">
                <button type="button" class="btn btn-sm btn-success " id="LUMPFORM_BTN_CMS_EB21" onclick="setCmsUse('EB21'); return false;">출금파일등록(익일-EB)</button>
                {{-- <button type="button" class="btn btn-sm btn-warning float-right" id="LUMPFORM_BTN_CMS_EC21" onclick="setCmsUse('EC21'); return false;">출금파일(당일-EC) </button> --}}
            </div>
            <!-- /.card-footer -->
        </div>
    </form>
</div>

<script>
    
function setCmsUse(div) {

    if(isEmpty($('#cms_send_date').val()))
    {
        alert('날짜를 입력해주세요.');
        return false;
    }
    
	// 중복 클릭 방지
	if(ccCheck()) return;
    $("input[id=cms_code]").val(div);
    var postdata = $('#cmsFileDownForm').serialize();
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $("#cmsfileres").html(loadingString);
    $.ajax({
        url  : "/erp/cmsusefile",
        type : "post",
        data : postdata,
        dataType: "json",
        success : function(data)
        {
            globalCheck = false;
            $("#cmsfileres").empty();
            console.log(data);
            
            if(data.rs_code=="Y"){
                alert("파일대상 등록 완료.");
            }else{
                alert(data.rs_msg);
            }
        },
        error : function(xhr)
        {
            globalCheck = false;
            $("#cmsfileres").empty();
            console.log(xhr);
        }
    });

}

</script>
