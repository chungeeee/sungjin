<div id="LUMP_FORM_borrowBatch" class="lump-forms" >
    <form name="borrowBatchForm" id="borrowBatchForm" method="post" action="">
        @csrf    

        <div class="card card-outline primary" id="changeManager">
            <div class="card-header flex-column status-border-right-none">
                <h5 class="card-title text-bold pt-1" style='color:black'>
                    담보상태변경
                </h5>
            </div>
            
                <div class="card-body">
                    <div class="row p-1">
                        <label for="status" class="col-sm-3 col-form-label" style="color:black">상태</label>
                        <div class="col-md-9">
                            <select class="form-control form-control-sm" name="status" id="status" onchange="setDisplay(this.value)">
                            <option value=''>선택</option>
                                {{ Func::printOption($array_status) }}
                            </select>
                        </div>
                    </div> 
                
                    <div class="status_area" id="status_E" style="display:none">
                        <div class="row p-1">
                            <label for="end_reason_cd" class="col-sm-3 col-form-label" style="color:black">해지사유</label>
                            <div class="col-md-9">
                                <select class="form-control form-control-sm status_E" name="end_reason_cd" id="end_reason_cd" >
                                <option value=''>선택</option>
                                    {{ Func::printOption($array_end_reason) }}
                                </select>
                            </div>
                        </div> 

                        <div class="row p-1">
                            <label for="end_date" class="col-sm-3 col-form-label" style="color:black">해지일자</label>
                            <div class="col-md-9">
                                <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-1 mt-1" id="end_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#end_date" name="end_date" id="end_date" DateOnly="true" size="6">
                                    <div class="input-group-append" data-target="#end_date" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div> 
                    </div>
                    <div class="status_area" id="status_S" style="display:none">
                        <div class="row p-1">
                            <label for="lump_borrow_comp_no" class="col-sm-3 col-form-label" style="color:black">담보제공기관</label>
                            <div class="col-md-9">
                                <select class="form-control form-control-sm" name="borrow_comp_no" id="lump_borrow_comp_no" onchange="setCompSubNo('lump_borrow_comp_sub_no',this.value);">
                                <option value=''>선택</option>
                                    {{ Func::printOption($array_borrow_comp) }}
                                </select>
                            </div>
                        </div> 
                        <div class="row p-1">
                            <label for="lump_borrow_comp_sub_no" class="col-sm-3 col-form-label" style="color:black">차수</label>
                            <div class="col-md-9">
                                <select class="form-control form-control-sm" name="borrow_comp_sub_no" id="lump_borrow_comp_sub_no" onchange="checkSdate($('#lump_borrow_comp_no').val(),this.value)";>
                                <option value=''>선택</option>
                                </select>
                            </div>
                        </div> 
                        <div class="row p-1">
                            <label for="start_date" class="col-sm-3 col-form-label" style="color:black">등록일자</label>
                            <div class="col-md-9">
                                <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-1 mt-1" id="start_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#start_date" name="start_date" id="start_date" DateOnly="true" size="6">
                                    <div class="input-group-append" data-target="#start_date" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="offset-3 sdate_tip text-grey"></div>
                        {{-- <div class="row p-1">
                            <label for="sdate_tail_money" class="col-sm-3 col-form-label" style="color:black">계약시원금</label>
                            <div class="col-md-9">
                                <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-1 mt-1">
                                    <input type="text" class="form-control form-control-sm" name="sdate_tail_money" id="sdate_tail_money" onkeyup="onlyNumber(this);">
                                </div>
                            </div>
                        </div>  --}}
                    </div>
                </div>
                <!-- /.card-body -->
                <div class="card-footer" id="input_footer">
                    <button type="button" class="btn btn-sm btn-info float-right" id="LUMPFORM_BTN_borrowStatus" onclick="batchAction('status'); return false;">담보상태변경 실행</button>
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
            url = 'lumpborrowstatus';

            if(!$('#status').val())
            {
                alert('변경할 상태를 선택해주세요');
                $('#status').focus();
                return false;
            }
            if($('#status').val() == 'S')
            {
                if(!$('#lump_borrow_comp_no').val())
                {
                    alert('담보제공기관을 선택해주세요');
                    $('#lump_borrow_comp_no').focus();
                    return false;
                }
                if(!$('#lump_borrow_comp_sub_no').val())
                {
                    alert('차수를 선택해주세요');
                    $('#lump_borrow_comp_sub_no').focus();
                    return false;
                }
                if(!$('input[name=start_date]').val())
                {
                    alert('담보 등록일자를 선택해주세요');
                    $('input[name=start_date]').focus();
                    return false;
                }
            }

            if($('#status').val() == 'E' && !$('#end_reason_cd').val())
            {
                alert('담보 해지사유를 선택해주세요');
                $('#end_reason_cd').focus();
                return false;
            }
            if($('#status').val() == 'E' && !$('input[name=end_date]').val())
            {
                alert('담보 해지일자를 선택해주세요');
                $('input[name=end_date]').focus();
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

        var postdata = $('#borrowBatchForm').serialize();
        var listChk = getArrayCheckbox('listChk[]');
        postdata = postdata + '&' + listChk;
        postdata = postdata + '&nowTab=' + nowTabs;

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
    


</script>
