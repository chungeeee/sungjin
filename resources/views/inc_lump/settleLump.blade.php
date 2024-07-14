<div id="LUMP_FORM_settleLump" class="lump-forms" style="display:none">
    <form name="settleLumpForm" id="settleLumpForm" method="post" action="">
        <input type="hidden" name="beforeStatus" id="beforeStatus" value="">
        @csrf    
        <div class="card card-outline primary" id="changeStatus">
            <div class="card-header flex-column status-border-right-none">
                <h5 class="card-title text-bold" style='color:black'>
                    상태변경
                </h5>
            </div>
            
                <div class="card-body">
                    <div class="row p-1">
                        <label for="label_ch_status" class="col-sm-3 col-form-label" style="color:black">변경상태</label>
                        <div class="col-md-9">
                            <select class="form-control form-control-sm selectpicker" name="ch_status" id="ch_status">
                            <option value=''>상태</option>
                                {{ Func::printOption($arrayStatus) }}
                            </select>
                        </div>
                    </div> 
                </div>
                <!-- /.card-body -->
                <div class="card-footer" id="input_footer">
                    @if(Func::funcCheckPermit("C020")||Func::funcCheckPermit("C022"))
                    <button class="btn btn-sm btn-info float-right" id="LUMPFORM_BTN_loanAppStatus" onclick="settleBatchAction(); return false;">상태변경 실행</button>
                    @endif
                </div>
                <!-- /.card-footer -->

        </div> 

    </form>
</div>

<script>
    
    // 상태변경
    function settleBatchAction()
    {

        if(!isCheckboxChecked('listChk[]'))
        {
            alert('선택한 고객이 없습니다. 고객을 선택 후 이용해 주세요.');
            return;
        }
        
        if(!$('#ch_status').val())
        {
            alert('변경할 상태를 선택해주세요');
            $('#ch_status').focus();
            return false;
        }
       
        var url = '';
        var nowTabs = $('#tabsSelect{{ $result['listName'] ?? '' }}').val();
        var upStatus = $('#ch_status').val();
        $("#beforeStatus").val(nowTabs);
        url = 'lumploanappstatus';
       
        // 가접수, 접수 상태에서만 거절이나 보류로 보낸다.
        if(nowTabs!='A' && nowTabs!='B')
        {
            alert('일괄상태변경은 접수, 결제요청 상태에서만 가능합니다.');
            return;
        }
        if(nowTabs =="A" && upStatus !='B' && upStatus !='X' ){
            alert('접수상태에서는  결제요청, 부결 상태로만 가능합니다.');
            return
            ;
        }
        if(nowTabs =="B" && upStatus !='Y' && upStatus !='X' ){
            alert(' 결제요청 상태에서는  결제완료, 부결 상태로만 가능합니다.');
            return;
        }

        if(ccCheck()) return;

        if( !confirm("정말로 변경하시겠습니까?") )
        {
            globalCheck = false;
            return false;
        }

        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#settleLumpForm').serialize();
        var listChk = getArrayCheckbox('listChk[]');
        postdata = postdata + '&' + listChk;

        $.ajax({
            url  : "/erp/settlelump",
            type : "post",
            data : postdata,
            dataType:"json",
            success : function(result) {
                var str = "[화해일괄처리결과]\n";
                str+="성공 ("+result.suc_cnt+") 건:"+result.suc_no+"\n";
                str+="실패 ("+result.fail_cnt+") 건:"+result.fail_no+"";
                
                alert(str);  
                listRefresh();

                globalCheck = false;
            },
            error : function(xhr) {
                globalCheck = false;
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }

    
</script>
