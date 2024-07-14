<div id="LUMP_FORM_loanAppBatch" class="lump-forms" style="display:none">
    <form name="loanAppBatchForm" id="loanAppBatchForm" method="post" action="">
        @csrf    

        <div class="card card-outline primary" id="changeManager">
            <div class="card-header flex-column status-border-right-none">
                <h5 class="card-title text-bold" style='color:black'>
                    심사자변경
                </h5>
            </div>
            
                <div class="card-body">
                    <div class="row p-1">
                        <label for="change_manager_code" class="col-sm-3 col-form-label" style="color:black">지점</label>
                        <div class="col-md-9">
                            <select class="form-control form-control-sm selectpicker" name="change_manager_code" id="change_manager_code" onchange="changeLumpManagerCode(this.value,'change_manager_id');">
                            <option value=''>부서</option>
                                {{ Func::printOption($arrayBranch) }}
                            </select>
                        </div>
                    </div> 
                
                    <div class="row p-1">
                        <label for="change_manager_id" class="col-sm-3 col-form-label" style="color:black">담당자</label>
                        <div class="col-md-9">
                            <select class="form-control form-control-sm selectpicker" name="change_manager_id" id="change_manager_id">
                            <option value=''>담당자선택</option>
                                
                            </select>
                        </div>
                    </div> 
                
                    ※ 접수에서 심사자 변경시 신청상태가 심사상태로 변경됩니다.
                </div>
                <!-- /.card-body -->
                <div class="card-footer" id="input_footer">
                    <button class="btn btn-sm btn-info float-right" id="LUMPFORM_BTN_loanAppManager" onclick="batchAction('appmanager'); return false;">심사자변경 실행</button>
                </div>
                <!-- /.card-footer -->
    
        </div>

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
                            <select class="form-control form-control-sm selectpicker" name="ch_status" id="ch_status" onChange="setRejectCd();">
                            <option value=''>상태</option>
                                {{ Func::printOption($arrayChangeStatus) }}
                            </select>
                        </div>

                        <label for="label_ch_reject" class="col-sm-3 col-form-label" style="color:black">거절사유</label>
                        <div class="col-md-9">
                            <select class="form-control form-control-sm selectpicker mr-2 col-md-8" name="ch_reject_cd[]" id="ch_reject_cd" multiple data-selected-text-format="count > 2" data-live-search="true" title="거절사유" disabled>
                                {{ Func::printOptionMulti($configArr['app_reject_cd']) }}
                            </select> 
                        </div>
                    </div> 
                    
                </div>
                <!-- /.card-body -->
                <div class="card-footer" id="input_footer">
                    <button class="btn btn-sm btn-info float-right" id="LUMPFORM_BTN_loanAppStatus" onclick="batchAction('status'); return false;">상태변경 실행</button>
                </div>
                <!-- /.card-footer -->

        </div> 

    </form>
</div>

<script>
    
    // 상태변경
    function batchAction(div)
    {

        if(!isCheckboxChecked('listChk[]'))
        {
            alert('선택한 고객이 없습니다. 고객을 선택 후 이용해 주세요.');
            return;
        }
        
        var url = '';
        var nowTabs = $('#tabsSelect{{ $result['listName'] ?? '' }}').val();
        if(div=='status')
        {
            url = 'lumploanappstatus';

            // 가접수, 접수 상태에서만 거절이나 보류로 보낸다.
            if(nowTabs!='Z' && nowTabs!='A' && nowTabs!='B')
            {
                alert('일괄상태변경은 가접수와, 접수, 심사 상태에서만 가능합니다.');
                return;
            }

            if(!$('#ch_status').val())
            {
                alert('변경할 상태를 선택해주세요');
                $('#ch_status').focus();
                return false;
            }
        }
        else if(div=='appmanager')
        {
            url = 'lumploanappmanager';

            // 접수, 심사 상태에서만 심사자를 변경한다.
            if(nowTabs!='A' && nowTabs!='B')
            {
                alert('일괄 심사자변경은 접수와, 심사 상태에서만 가능합니다.');
                return;
            }

            if(!$('#change_manager_id').val())
            {
                alert('변경할 심사자를 선택해주세요');
                $('#change_manager_id').focus();
                return false;
            }
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

        var postdata = $('#loanAppBatchForm').serialize();
        var listChk = getArrayCheckbox('listChk[]');
        postdata = postdata + '&' + listChk;

        $.ajax({
            url  : "/lump/"+url,
            type : "post",
            data : postdata,
            success : function(result) {
                alert(result);  
                listRefresh();

                globalCheck = false;
            },
            error : function(xhr) {
                globalCheck = false;
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }

    
    function changeLumpManagerCode(val, toid)
    {
        $("#"+toid).empty();
        var option_string = "<option value=''>선택</option>";
        $("#"+toid).append(option_string);

        @foreach( $arrayUserLoan as $bcd => $vus)
        if( val=='{{ $bcd }}' )
        {
            @foreach( $vus as $vtmp )

            var option = $("<option value='{{ $vtmp->id }}'>{{ $vtmp->name }}</option>");
            $("#"+toid).append(option);

            @endforeach
        }
        @endforeach

        // 화면갱신
        $("#"+toid).selectpicker({
            width: '100%',
            style: 'btn-default form-control-sm bg-white',
        });   
        $("#"+toid).selectpicker('refresh');

    }

    // 거절상태일때만 거절사유 활성화
    function setRejectCd()
    {
        var status = $('#ch_status').val();
        
        if(status=='X')
        {
            $('#ch_reject_cd').attr('disabled', false);
        }
        else
        {
            $('#ch_reject_cd').attr("disabled", true);
        }
        $('#ch_reject_cd').selectpicker('refresh');
    }
</script>
