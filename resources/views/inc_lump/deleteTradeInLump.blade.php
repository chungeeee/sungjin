<div id="LUMP_FORM_deleteTradeInLump" class="lump-forms" >
    <form name="delete_form" id="delete_form" method="post" action="">
        @csrf    

        <div class="card card-outline primary" id="changeManager">
            <div class="card-header flex-column status-border-right-none">
                <h5 class="card-title text-bold pt-1" style='color:black'>
                    입금삭제
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
                
                    <div class="row p-1 d-none reset a_area" >
                        <label for="app_memo" class="col-sm-3 col-form-label " style="color:black">요청자 의견</label>
                        <div class="col-md-9">
                            <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-1 mt-1" id="app_memo" data-target-input="nearest">
                                <textarea class="form-control form-control-sm" name="app_memo"></textarea>
                            </div>
                        </div>
                    </div> 
                    <div class="row p-1 d-none reset b_area">
                        <label for="confirm_memo_1" class="col-sm-3 col-form-label " style="color:black">결재자 의견</label>
                        <div class="col-md-9">
                            <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-1 mt-1" id="confirm_memo_1" data-target-input="nearest">
                                <textarea class="form-control form-control-sm" name="confirm_memo_1"></textarea>
                            </div>
                        </div>
                    </div>

                    <div class="status_area">

                        <div class="row p-1 d-none reset a_area">
                            <label for="end_reason_cd" class="col-sm-3 col-form-label" style="color:black">결재자<br>지정</label>
                            <div class="col-md-9">
                                <select class="form-control form-control-sm status_A" name="confirm_id_1" id="confirm_id_1" >
                                <option value=''>선택</option>
                                </select>
                            </div>
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
        <input type="hidden" name="trade_day_div" id="trade_day_div">
        <input type="hidden" name="lump_trade_date" id="lump_trade_date">
        <input type="hidden" name="sms_send_flag" id="sms_send_flag" value="">
    </form>
</div>

<script>
    


    // 상태변경
    function batchAction()
    {
        if(!isCheckboxChecked('listChk[]'))
        {
            alert('선택한 고객이 없습니다. 고객을 선택 후 이용해 주세요.');
            return;
        }
        
        var url = '';
        var nowTabs = $('#tabsSelect{{ $result['listName'] ?? '' }}').val();

        if(!$('#confirm_status').val())
        {
            alert("상태를 지정해주세요");
            return false;
        }
        if(($('#confirm_status').val() == "A" && !$('#confirm_id_1').val()))
        {
            alert("결재자를 지정해주세요");
            return false;   
        }
       

        if(ccCheck()) return;

        if($('#confirm_status').val() == "N" )
        {
            if(!confirm("삭제결재를 취소하시겠습니까? \n취소시 결재관련 내용은 모두 초기화됩니다."))
            {
                globalCheck = false;
                return false;
            }
        }
        else
        {
            if($('#confirm_status').val() == "Y")
            {
                // if( confirm("고객에게 입금 취소 문자를 발송하시겠습니까?\n\n[확인]을 선택하시면 고객에게 취소 문자가 발송됩니다.\n[취소]를 선택하시면 발송되지 않습니다.") )
                // {
                //     console.log("test");
                //     $('#sms_send_flag').val("Y");
                // }
                // else
                // {
                //     $('#sms_send_flag').val("N");
                // }
                $('#sms_send_flag').val("N");
            }


            if(!confirm("일괄처리를 실행하시겠습니까?"))
            {
                globalCheck = false;
                return false;
            }
        }



        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#delete_form').serialize();
        var listChk = getArrayCheckbox('listChk[]');
        postdata = postdata + '&' + listChk;
        postdata = postdata + '&nowTab=' + nowTabs;

        $.ajax({
            url  : "/erp/tradeindeletelump",
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
