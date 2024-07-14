@include('inc/listSimple')

<!-- Main content -->
<div class="toasts-bottom-right fixed  pr-2  pl-1 col-md-3" id="custRightInput" style="bottom:2px;">
    <div class="card card-outline primary">
        <div class="card-header flex-column status-border-right-none">
            <h5 class="card-title text-bold" >
                문자 발송
            </h5>
        </div>
        <form name="smsForm" id="smsForm" method="post" action="">
            @csrf
            <div class="card-body">
                <input type="hidden" name="cust_info_no" id="sms_cust_info_no" value="{{ $cust_info_no }}">
                <input type="hidden" name="loan_info_no" id="sms_loan_info_no" value="{{ $loan_info_no }}">

                <div class="row">
                    <div class="col-md-5">
                        <textarea class="form-control" name="message" id="message" rows="10" style="resize:none;"></textarea>
                    </div>
                    <div class="col-md-7">

                        <span class="form-control-xs mt-1 col-4" style="float:left">구&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;분 : </span> 
                        <select class="form-control form-control-xs col-8 mt-1" onchange="change_div(this);" id="sms_erp_div" name="sms_erp_div">
                            <option value=''>발송사유</option>
                            {{ Func::printOption($arrayConf['sms_erp_cd'], '') }}
                        </select>

                        <span class="form-control-xs mt-1 col-4" style="float:left">문&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;구 : </span> 
                        <select class="form-control form-control-xs col-8 mt-1" id="sms_msg" onchange="set_msg()">
                            <option value=''>직접입력</option>
                        </select>
                        <span class="form-control-xs mt-1 col-4" style="float:left">수신번호 : </span> 
                        <input type="text" name="receiver" id="receiver" class="form-control form-control-xs mt-1 col-8" maxlength="11" placeholder="받는이" onkeyup="onlyNumber(this);" value="{{ $ph_num }}"/>
                        <span class="form-control-xs mt-1 col-4" style="float:left">발신번호 : </span> 
                        <input type="text" name="sender" class="form-control form-control-xs mt-1 col-8" maxlength="11" placeholder="보내는이" onkeyup="onlyNumber(this);" value='{{ Func::getBranchPh(Auth::user()->branch_code) }}'/>

                        <span class="form-control-xs mt-1 col-4" style="float:left; margin-top:3px;">기준일자 : </span> 
                        <div class="input-group form-control-xs col-8 date  mt-1 p-0" id="sms_basis_date" data-target-input="nearest">
                            <input type="text" class="form-control form-control-xs -input" style="height:25px;" data-target="#sms_basis_date" name="sms_basis_date" id="sms_basis_date" DateOnly="true" size="6" data-toggle="" value="{{ date('Ymd') }}">
                            <div class="input-group-append" data-target="#sms_basis_date" data-toggle="">
                                <div class="input-group-text text-xs" style="height:25px;"><i class="fa fa-xs fa-calendar p-0"></i></div>
                            </div>
                        </div>

                        <span class="form-control-xs mt-1 col-4 visit-input-form" style="float:left; margin-top:3px;">방문장소 : </span> 
                        <select class="form-control form-control-xs col-8 mt-1 visit-input-form" id="visit_local" name="visit_local">
                            <option value=''>방문장소</option>
                            {{ Func::printOption(['자택'=>'자택','직장'=>'직장','자택 및 직장'=>'자택 및 직장']) }}
                        </select>

                        <span class="form-control-xs mt-1 col-4 visit-input-form" style="float:left; margin-top:3px;">방문시간 : </span> 
                        <select class="form-control form-control-xs col-8 mt-1 visit-input-form" id="visit_time" name="visit_time">
                            <option value=''>방문시간</option>
                            {{ Func::printOption(['08시~12시'=>'08시~12시','10시~14시'=>'10시~14시','12시~16시'=>'12시~16시','14시~18시'=>'14시~18시','16시~20시30분'=>'16시~20시30분']) }}
                        </select>
                        
                        <div class="visit-input-form">
                        <span class="form-control-xs mt-1 col-4 visit-input-form" style="float:left; margin-top:3px;"></span> 
                        <div class="input-group form-control-xs pr-0 col-8 visit-input-form">
                            <input type="checkbox" class="form-control-sm" name="sms_with_59" id="sms_with_59" value="Y" />
                            <label class="form-control-sm text-xs" style="color:black" for="sms_with_59">추심착수안내</label>
                        </div>
                        </div>
                        

                        <div class="form-control-xs col-12" style="">
                            <input type="checkbox" class="form-control-sm" name="reserve" id="reserve" value="Y" onclick="clickReserve();">
                            <label class="text-xs ml-1" for="reserve" style="vertical-align:middle;padding-bottom:10px;">예약</label>

                            <input type="checkbox" class="form-control-sm ml-3" name="no_div_dup" id="no_div_dup" value="Y" checked />
                            <label class="text-xs ml-1" style="vertical-align:middle;padding-bottom:10px;color:black;" for="no_div_dup">중복제외</label>

                            <input type="checkbox" class="form-control-sm ml-3" name="sms_customer" id="sms_customer" value="Y"  checked />
                            <label class="text-xs ml-1" style="vertical-align:middle;padding-bottom:10px;color:black" for="sms_customer">고객통합</label>

                        </div> 
                        <div class="input-group form-control-xs col-8 mt-1 ml-2 p-0">
                            <div class="input-group date" id="rDateDiv" data-target-input="nearest">
                                <input type="text" class="form-control form-control-sm datetimeformat -input" data-target="#rDateDiv" name="rDate" id="rDate" size="15" disabled/>
                                <div class="input-group-append" data-target="#rDateDiv" data-toggle="">
                                    <div class="input-group-text text-xs"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>


                    </div>
                </div>
            </div>
            <!-- /.card-body -->
            <div class="card-footer" id="input_footer">
                <button type="button" class="btn-sm btn-default" onclick="smsPreview();">미리보기</button>
                <button type="button" class="btn-sm btn-default float-right" onclick="smsAction();">발송</button>
            </div>
            <!-- /.card-footer -->
        </form>
    </div>
</div>

<div class="modal fade" id="smsModal" aria-modal="true" role="dialog">
    <div class="modal-dialog modal-normal">
        <div class="modal-content-sms">
            <div class="modal-header">
                <h4 class="modal-title pl-4">미리보기</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body text-sm" id="smsModalBody">
                
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>



<script>
    $(document).ready(function() {
        // 리스트
        @if (isset($result) && gettype($result) == 'array' && isset($result['listAction']))
            // 진입시 데이터 가져오기
            getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize(), 'FIRST');
        @endif

        listSizeSet("A");
    });
    
    $('#rDateDiv').({
        format: 'YYYY-MM-DD HH:mm',
        locale: 'ko',
    });

    
    $(function(){
        setInputMask('class', 'datetimeformat', 'datetime');

        $('#custRightInput > .card').css('border-radius','0.5rem');
        $('#custRightInput > .card > .card-header').css('background-color',$("#status_color").val());
        $('#custRightInput > .card > .card-header > .card-title').css('color', '#FFFFFF');
        
        $('.visit-input-form').hide();
    });



    function clickReserve()
    {
        if(smsForm.reserve.checked)
        {
            $('#rDateDiv').data("").enable();
        }
        else
        {
            $('#rDateDiv').data("").disable();
        }
    }

    // 발송사유 선택 시 해당 문자코드에 작성된 SMS문장 출력
    function change_div(div)
    {   
        var sms_div = div.options[div.selectedIndex].value;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url  : "/erp/custsmsdiv",
            type : "post",
            data : {sms_div:sms_div},
                success : function(result)
                {
                    $("#sms_msg").html(result);
                },
                error : function(xhr)
                {
                    alert("통신오류입니다. 관리자에게 문의해주세요.");
                }
            });

        // 추가입력화면 필요
        if( ( sms_div=="77" || sms_div=="58" || sms_div=="71" || sms_div=="72" ) )
        {
            $('.visit-input-form').show();
            $('#sms_with_59').prop("checked", true);
        }
        else
        {
            $('.visit-input-form').hide();
            $('#sms_with_59').prop("checked", false);
        }
    }
    

    // 선택한 SMS문장 텍스트박스에 출력
    function set_msg()
    {
        var text = $('#sms_msg').val();
        $('#message').val(text);
    }

    // 문자 미리보기
    function smsPreview()
    {
        if( smsForm.sms_erp_div.value == "" )
        {
            alert('발송사유를 선택해주세요.');
            smsForm.sms_erp_div.focus();
            return false;
        }

        if( $("#message").val() == "" )
        {
            alert('메세지 내용을 입력해주세요.');
            return false;
        }

        $("#sms_loan_info_no").val(currLoanNo);

        var formdata = $('#smsForm').serialize();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#smsModal").modal('show');
        $("#smsModalBody").html(loadingString);
        
        $.post("/erp/custsmspreview", formdata, function(data) {

            $("#smsModalBody").html(data);
        });
    }

    // 문자 발송
    function smsAction()
    {
        if( smsForm.sms_erp_div.value == "" )
        {
            alert('구분을 선택해주세요.');
            smsForm.sms_erp_div.focus();
            return false;
        }
        
        if( $("#receiver").val() == "" )
        {
            alert('수신번호를 입력해주세요.');
            return false;
        }
        if( $("#message").val() == "" )
        {
            alert('메세지 내용을 입력해주세요.');
            return false;
        }

        if( smsForm.reserve.checked )
        {
            var datetime_pattern = /^(19|20)\d{2}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[0-1]) (0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])$/;

            if(!datetime_pattern.test(smsForm.rDate.value))
            {
                alert("예약시간 형식을 올바르게 입력해주세요.");
                smsForm.rDate.focus();
                return false;
            }

            if( smsForm.rDate.value )

            var d = new Date();
            var s =
                leadingZeros(d.getFullYear(), 4) + '-' +
                leadingZeros(d.getMonth() + 1, 2) + '-' +
                leadingZeros(d.getDate(), 2) + ' ' +

                leadingZeros(d.getHours(), 2) + ':' +
                leadingZeros(d.getMinutes(), 2);
                // + ':' + leadingZeros(d.getSeconds(), 2);
            
            if( smsForm.rDate.value <= s )
            {
                alert('예약시간은 과거시간 및 현재시간으로 설정할 수 없습니다.');
                smsForm.rDate.focus();
                return false;
            }
        }

        if( !confirm("정말로 문자를 발송하시겠습니까?") )
        {
            return false;
        }

        $("#sms_loan_info_no").val(currLoanNo);


        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#smsForm').serialize();

        if(ccCheck()) return;

        $.ajax({
            url  : "/erp/custsmsaction",
            type : "post",
            data : postdata,
            success : function(result) {
                globalCheck = false;
                alert(result);
                getRightList('sms');
            },
            error : function(xhr) {
                globalCheck = false;
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });

    }

    function leadingZeros(n, digits) 
    {
        var zero = '';
        n = n.toString();

        if (n.length < digits) 
        {
            for (i = 0; i < digits - n.length; i++)
            {
                zero += '0';
            }
        }
        return zero + n;
    }

</script>