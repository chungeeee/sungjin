<div id="LUMP_FORM_loanLump" class="lump-forms" style="display:none">
    <form name="loanLumpForm" id="loanLumpForm" method="post" action="/erp/loanlump">
        @csrf    
        <input type="hidden" name="mode" id="mode" value="">
        <div class="card card-outline primary" id="badInfo">
            <div class="card-header flex-column status-border-right-none">
                <h5 class="card-title text-bold" style='color:black'>
                    채불등록
                </h5>
            </div>
            <!-- /.card-body -->
            <div class="card-body">
                <div class="col-md-12 input-group date datetimepicker" data-target-input="nearest">
                    <span style="color:black;font-weigh:bold;" class="pr-2">통지일</span>
                    <input type="text" class="form-control form-control-sm dateformat datetimepicker" name="bad_post_date" id="bad_post_date" inputmode="text">
                    <div class="input-group-append" data-target="#bad_post_date" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                
                    <span style="color:black;" class="pr-2 pl-3">등록일</span>
                    <input type="text" class="form-control form-control-sm dateformat datetimepicker" name="bad_reg_date" id="bad_reg_date" inputmode="text">
                    <div class="input-group-append" data-target="#bad_reg_date" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <button class="btn btn-sm btn-info float-right" id="LUMPFORM_BTN_badInfo" onclick="batchAction('badInfo'); return false;" >등록</button>   
            </div>
            <!-- /.card-footer -->
        </div> 

        <div class="card card-outline primary" id="loanDiv">
            <div class="card-header flex-column status-border-right-none">
                <h5 class="card-title text-bold" style='color:black'>
                    채권구분변경
                </h5>
            </div>

            <div class="card-body">
                <table>
                    <tr>
                        <td class="col-sm-6">
                            <select class="form-control form-control-sm" id="lumpLoanDiv" name="lumpLoanDiv" onchange="changeAttribute(this.value);">
                                <option>채권구분선택</option>
                                <option value="loan_cat_1_cd">채권구분1</option>
                                <option value="loan_cat_2_cd">NPL채권구분</option>
                                <option value="loan_cat_3_cd">채권구분2</option>
                                <option value="loan_cat_4_cd">성향등급</option>
                                <option value="loan_cat_5_cd">채권등급</option>
                            </select>
                        </td>
                        <td>
                            <select class="form-control form-control-sm" name="lumpLoanDivValue" id="lumpLoanDivValue">
                                <option value="">채권구분</option>
                            </select>
                        </td>
                    </tr>
                </table>
            </div>

            <!-- /.card-body -->
            <div class="card-footer" id="input_footer">
                <button class="btn btn-sm btn-info float-right" id="lumpButtonLoanDiv" onclick="batchAction('loanDiv'); return false;" >변경</button>
            </div>
            <!-- /.card-footer -->
        </div> 
    </form>
</div>
<script>
    
    // 
    function batchAction(val)
    {
        if(val == 'badInfo')
        {
            var target = "채불등록을";
        }
        if(val == 'loanDiv')
        {
            var target = "채권구분변경을";
        }
        
        if(!isCheckboxChecked('listChk[]'))
        {
            alert('선택한 고객이 없습니다. 고객을 선택 후 이용해 주세요.');
            return;
        }
        
        if( !confirm(target+" 진행하시겠습니까?") )
        {
            globalCheck = false;
            return false;
        }

        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#mode').val(val);
        var postdata = $('#loanLumpForm').serialize();
        var listChk = getArrayCheckbox('listChk[]');
        postdata = postdata + '&' + listChk;

        $.ajax({
            url  : "{{url('/erp/loanlump')}}",
            type : "post",
            data : postdata,
            dataType : 'json',
            success : function(result) {
                console.log(result);
                if(result.msg == "complete")
                {
                    alert(target + " 완료했습니다.");
                }
                listRefresh();
                globalCheck = false;
            },
            error : function(xhr) {
                globalCheck = false;
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }



    function changeAttribute(val)
    {
        $("#lumpLoanDivValue").empty();
        var option = $("<option value=''>채권구분</option>");
        $("#lumpLoanDivValue").append(option);
        @foreach( $array_cat_name as $bcd => $vus)
        if(val == '{{$bcd}}')
        {
            @if( isset($vus) )
            
            @foreach( $vus as $num => $vtmp)
                var option = $("<option value='{{$num}}'>{{$vtmp}}</option>");
                $("#lumpLoanDivValue").append(option);
                @endforeach
            
            @endif
        }
        @endforeach
    }

</script>