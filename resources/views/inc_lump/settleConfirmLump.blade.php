<div id="LUMP_FORM_settleConfirmLump" class="lump-forms" >
    <form name="settle_confirm_form" id="settle_confirm_form" method="post" action="">
        @csrf    

        <div class="card card-outline primary" id="changeManager">
            <div class="card-header flex-column status-border-right-none">
                <h5 class="card-title text-bold pt-1" style='color:black'>
                    일괄처리
                </h5>
            </div>
            
                <div class="card-body">
                    <div class="row p-1">
                        <label for="status" class="col-sm-3 col-form-label" style="color:black">상태</label>
                        <div class="col-md-9">
                            <select class="form-control form-control-sm" name="confirm_status" id="confirm_status">
                            <option value=''>선택</option>
                            </select>
                        </div>
                    </div> 

                    <div class="row p-1 d-none reset a_area">
                        <label for="confirm_memo_1" class="col-sm-3 col-form-label " style="color:black">결재자 의견</label>
                        <div class="col-md-9">
                            <textarea class="form-control form-control-sm" name="confirm_memo_1"></textarea>
                        </div>
                    </div>                    

                    <div class="offset-3 sdate_tip text-grey"></div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer" id="input_footer">
                    <button type="button" class="btn btn-sm btn-info float-right" id="LUMPFORM_BTN_borrowStatus" onclick="batchAction(); return false;">일괄처리실행</button>
                </div>
                <!-- /.card-footer -->
    
        </div>
    </form>
</div>

<script>
    


    // 상태변경
    function batchAction(div)
    {

        if(!settleLumpChk())
        {
            return false;
        }

        var url = '';
        var nowTabs = $('#tabsSelect{{ $result['listName'] ?? '' }}').val();
        if(!$('#confirm_status').val())
        {
            alert("상태를 지정해주세요");
            return false;
        }
        if(nowTabs == "A" &&  !$('#confirm_id_2').val())
        {
            alert("지정된 다음결재자가 없습니다.결재자를 지정해주세요");
            return false;   
        }

        if(ccCheck()) return;

    
        if(!confirm("일괄처리를 실행하시겠습니까?"))
        {
            globalCheck = false;
            return false;
        }
        



        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#settle_confirm_form').serialize();
        var listChk = getArrayCheckbox('listChk[]');
        postdata = postdata + '&' + listChk;
        postdata = postdata + '&nowTab=' + nowTabs;

        $.ajax({
            url  : "/erp/settlelump",
            type : "post",
            data : postdata,
            success : function(result) {
                console.log(result);
                alert(result.msg);  
                listRefresh();
                closeLump();

                globalCheck = false;
            },
            error : function(xhr) {
                globalCheck = false;
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }
    


</script>
