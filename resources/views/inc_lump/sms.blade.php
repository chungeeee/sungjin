<div id="LUMP_FORM_sms" class="lump-forms" style="display:none">
    <div class="card card-outline primary">
        <div class="card-header flex-column status-border-right-none">
            <h5 class="card-title text-bold" style='color:black'>
                문자 일괄 발송
            </h5>
        </div>
        <form name="smsBatchForm" id="smsBatchForm" method="post" action="">
            @csrf
            <div class="card-body">
                <input type="hidden" name="batchSmsDiv" id="batchSmsDiv" value="{{ $lumpv['param']['div'] }}">

                <div class="row">
                    <div class="col-md-5">
                        <textarea class="form-control" name="message" id="message" rows="10" style="resize:none; font-size:12px;" @if(isset($lumpv['param']['readonly']) && $lumpv['param']['readonly']==true) readonly @endif></textarea>
                    </div>
                    <div class="col-md-7 text-black">

                        <span class="form-control-xs mt-1 col-4" style="float:left">구&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;분 : </span> 
                        <select class="form-control form-control-xs col-8 mt-1" onchange="changeDiv(this, '{{$lumpv['param']['div']}}');" id="batch_sms_div" name="batch_sms_div">
                            <option value=''>발송구분</option>
                            @if( isset($batchSmsDiv) )
                            {{ Func::printOption($batchSmsDiv, '') }}
                            {{ Log::debug($batchSmsDiv)}}
                            @else
                            {{ Func::printOption($configArr['sms_'.$lumpv['param']['div'].'_cd'], '') }}
                            @endif
                        </select>

                        <span class="form-control-xs mt-1 col-4" style="float:left">문&nbsp; &nbsp; &nbsp; &nbsp; &nbsp;구 : </span> 
                        <select class="form-control form-control-xs col-8 mt-1" id="sms_msg" onchange="set_msg()">
                            <option value=''>직접입력</option>
                        </select>
                        <span class="form-control-xs mt-1 col-4" style="float:left">수신번호 : </span> 
                        <input type="text" name="receiver" id="receiver" class="form-control form-control-xs mt-1 col-8" maxlength="11" placeholder="체크선택" disabled />
                        <span class="form-control-xs mt-1 col-4" style="float:left">발신번호 : </span> 
                        <input type="text" name="sender" class="form-control form-control-xs mt-1 col-8" maxlength="11" placeholder="보내는이" onkeyup="onlyNumber(this);" value='{{ Func::getBranchPh(Auth::user()->branch_code) }}'/>
                        

                        @if( $lumpv['param']['div']=="erp" )

                        <span class="form-control-xs mt-1 col-4" style="float:left; margin-top:3px;">기준일자 : </span> 
                        <div class="input-group form-control-xs col-8 date datetimepicker mt-1 p-0" id="sms_basis_date" data-target-input="nearest">
                            <input type="text" class="form-control form-control-xs datetimepicker-input" style="height:25px;" data-target="#sms_basis_date" name="sms_basis_date" id="sms_basis_date" DateOnly="true" size="6" data-toggle="datetimepicker" value="{{ date('Ymd') }}">
                            <div class="input-group-append" data-target="#sms_basis_date" data-toggle="datetimepicker">
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

                        <span class="form-control-xs mt-1 col-4 visit-input-form" style="float:left; margin-top:3px;"></span> 
                        <div class="input-group form-control-xs pr-0 col-8 visit-input-form">
                            <input type="checkbox" class="form-control-sm" name="sms_with_59" id="sms_with_59" value="Y" />
                            <label class="form-control-sm text-xs" style="color:black" for="sms_with_59">추심착수안내</label>
                        </div> 

                        @endif

                        
                        <div class="input-group form-control-xs pr-0">

                            <input type="checkbox" class="form-control-sm" name="reserve" id="reserve" value="Y" onclick="clickReserve(this.checked);" />
                            <label class="col-form-label text-xs m-0 ml-1 mr-3 mt-n1" style="color:black" for="reserve">예약</label>
                            @if($lumpv['param']['div']=='erp')
                                @php 
                                    if(isset($result['listName']) && $result['listName']=='doc')
                                    {
                                        $duplChk = "";
                                    }
                                    else 
                                    {
                                        $duplChk = "checked";
                                    }

                                @endphp
                            <input type="checkbox" class="form-control-sm" name="no_div_dup" id="no_div_dup" value="Y" {{ $duplChk }} />
                            <label class="col-form-label text-xs m-0 ml-1 mr-3 mt-n1" style="color:black" for="no_div_dup">중복제외</label>

                            <input type="checkbox" class="form-control-sm" name="sms_customer" id="sms_customer" value="Y" {{ $duplChk }} />
                            <label class="col-form-label text-xs m-0 ml-1 mr-0 mt-n1" style="color:black" for="sms_customer">고객통합</label>
                            @else
                            <input type="hidden" name="no_div_dup" id="no_div_dup" value=''>
                            <input type="hidden" name="sms_customer" id="sms_customer" value=''>
                            @endif

                            <div class="input-group date mr-0" id="rDateDiv" data-target-input="nearest">
                                <input type="text" class="form-control form-control-sm datetimeformat datetimepicker-input" data-target="#rDateDiv" name="rDate" id="rDate" size="15" disabled />
                                <div class="input-group-append" data-target="#rDateDiv" data-toggle="datetimepicker">
                                    <div class="input-group-text text-xs"><i class="fa fa-xs fa-calendar"></i></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.card-body -->
            <div class="card-footer" id="input_footer">
                <button type="button" class="btn btn-sm btn-info" onclick="smsPreview();">미리보기</button>
                <button type="button" class="btn btn-sm btn-info float-right" onclick="smsAction();">발송</button>
            </div>
            <!-- /.card-footer -->
        
        </form>
    </div>
    <div id='viewPreview' style="display:none">
     <h5>미리보기</h5>
        <div style="height:400px; overflow-y:auto" id="viewMsg">
                

        </div>
    </div>
</div>

<script>
    // 발송사유 선택 시 해당 문자코드에 작성된 SMS문장 출력
    function changeDiv(div, ups_erp)
    {     
        var batch_sms_div = div.options[div.selectedIndex].value;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url  : "/"+ups_erp+"/custsmsdiv",
            type : "post",
            data : {sms_div:batch_sms_div},
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
        if( ups_erp=="erp" && ( batch_sms_div=="77" || batch_sms_div=="58" || batch_sms_div=="71" || batch_sms_div=="72" ) )
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

    function clickReserve(chk)
    {
        if(chk==true)
        {
            $('#rDateDiv').data("datetimepicker").enable();
        }
        else
        {
            $('#rDateDiv').data("datetimepicker").disable();
        }
    }

    // 문자 미리보기
    function smsPreview()
    {
        if(!isCheckboxChecked('listChk[]'))
        {
            alert('선택한 고객이 없습니다. 고객을 선택 후 이용해 주세요.');
            return;
        }

        if( $('#batch_sms_div').val() == "" )
        {
            alert('발송구분을 선택해주세요.');
            $('#batch_sms_div').focus();
            return false;
        }

        if( $("#message").val() == "" )
        {
            alert('메세지 내용을 입력해주세요.');
            return false;
        }


        var formdata = $('#smsBatchForm').serialize();
        var listChk = getArrayCheckbox('listChk[]');
        formdata = formdata + '&' + listChk;

        // 채권현황조회에서만 사용
        if( $("#target_sqlloan").length>0 )
        {
            formdata = formdata + '&condition=' + $("#target_sqlloan").val();
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        if(ccCheck()) return;

        $.post("/lump/lumpsmspreview", formdata, function(data) {
            
            if(data.rs_code=='Y')
            {
                var viewMsg = '';       
                $.each(Object.values(data.msg).reverse(), function(idx, msg){
                    viewMsg += '<div class="underline mr-2 ml-2 mt-1 mb-2" style="width:98%">' + msg + '</div>';
                });

                $('#viewMsg').html(viewMsg);
                $('#viewPreview').css('display', 'block');
            }
            else
            {
                alert(data.rs_msg);
            }
        });
        globalCheck = false;
    }

    // 문자 발송
    function smsAction()
    {
        if(!isCheckboxChecked('listChk[]'))
        {
            alert('선택한 고객이 없습니다. 고객을 선택 후 이용해 주세요.');
            return;
        }

        if( $('#batch_sms_div').val() == "" )
        {
            alert('발송구분을 선택해주세요.');
            $('#batch_sms_div').focus();
            return false;
        }
       
        if( $("#message").val() == "" )
        {
            alert('메세지 내용을 입력해주세요.');
            return false;
        }

        if( smsBatchForm.reserve.checked )
        {
            var datetime_pattern = /^(19|20)\d{2}-(0[1-9]|1[012])-(0[1-9]|[12][0-9]|3[0-1]) (0[0-9]|1[0-9]|2[0-3]):([0-5][0-9])$/;

            if(!datetime_pattern.test(smsBatchForm.rDate.value))
            {
                alert("예약시간 형식을 올바르게 입력해주세요.");
                smsBatchForm.rDate.focus();
                return false;
            }

            if( smsBatchForm.rDate.value )

            var d = new Date();
            var s =
                leadingZeros(d.getFullYear(), 4) + '-' +
                leadingZeros(d.getMonth() + 1, 2) + '-' +
                leadingZeros(d.getDate(), 2) + ' ' +

                leadingZeros(d.getHours(), 2) + ':' +
                leadingZeros(d.getMinutes(), 2);
                // + ':' + leadingZeros(d.getSeconds(), 2);
            
            if( smsBatchForm.rDate.value <= s )
            {
                alert('예약시간은 과거시간 및 현재시간으로 설정할 수 없습니다.');
                smsBatchForm.rDate.focus();
                return false;
            }
        }

        if(ccCheck()) return;

        if( !confirm("정말로 문자를 발송하시겠습니까?") )
        {
            globalCheck = false;
            return false;
        }

        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#smsBatchForm').serialize();
        var listChk = getArrayCheckbox('listChk[]');
        postdata = postdata + '&' + listChk;

        $.ajax({
            url  : "/lump/lumpsmsaction",
            type : "post",
            data : postdata,
            success : function(result) {
                alert(result);
                $('#viewMsg').html("");
                $('#viewPreview').css('display', 'none');    
                $('.check-all').iCheck('uncheck');            
                $('.list-check').iCheck('uncheck');     

                globalCheck = false;
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

@section('javascriptSms')

<script>
    // 일괄처리 - 문자발송
    setInputMask('class', 'datetimeformat', 'datetime');

    $('#rDateDiv').datetimepicker({ format: 'YYYY-MM-DD HH:mm', locale: 'ko', timePicker: true });

    $('.visit-input-form').hide();
    console.log('{{$lumpv['param']['div']}}');
</script>
@endsection