<div id="LUMP_FORM_sms" class="lump-forms" style="display:none">
    <div class="card card-outline primary">
        <div class="card-header flex-column status-border-right-none">
            <h5 class="card-title text-bold" style='color:black'>
                송금 일괄 요청
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
                        <div class="input-group form-control-xs pr-0">
                            <input type="checkbox" class="form-control-sm" name="reserve" id="reserve" value="Y" onclick="clickReserve(this.checked);" />
                            <label class="col-form-label text-xs m-0 ml-1 mr-3 mt-n1" style="color:black" for="reserve">예약</label>

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