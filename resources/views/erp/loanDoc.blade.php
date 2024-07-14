<div class="p-2">
<!-- BODY -->
<b>징구서류관리</b>
<form id="doc_form">
<input type="hidden" name="loan_info_no" value="{{ $loan_info_no }}">
<table class="table table-sm table-hover loan-info-table card-secondary card-outline">
    <thead>
        <tr>
            <th class="text-center"><input id="check_all" type="checkbox" class="icheckbox_square-blue-sm"></th>
            <th class="text-center"><span class="text-danger font-weight-bold h6 mr-1">*</span>서류</th>
            <th class="text-center">필수</th>
            <th class="text-center w-10">발송방법</th>
            <th class="text-center w-10">발송일</th>
            <th class="text-center w-10">도착일</th>
            <th class="text-center">스캔</th>
            <th class="text-center">보관</th>
            <th class="text-center w-20">메모</th>
            <th class="text-center">작업자</th>
            <th class="text-center">작업시간</th>
        </tr>
    </thead>
    <tbody>
        @foreach($necessary_doc as $ad)
            <tr>
            <td class="text-center"><input type="checkbox" class="icheckbox_square-blue-sm no" name="no[]" value="{{ $ad }}_0"></td>
            <td class="text-center">{{ Func::getArrayName($configArr['app_document'],$ad) }}</td>
            <td class="text-center"><i class='fas fa-check text-green'></td>
            <td class="text-center"></td>
            <td class="text-center"></td>
            <td class="text-center"></td>
            <td class="text-center"></td>
            <td class="text-center"></td>
            <td class="text-center"></td>
            <td class="text-center"></td>
            <td class="text-center"></td>
        </tr>
        @endforeach
        @foreach( $di as $idx => $v )
        <tr>
            <td class="text-center"><input type="checkbox" class="icheckbox_square-blue-sm no" name="no[]" value="{{ $v->app_document_cd."_".$v->no }}"></td>
            <td class="text-center" title="{{ $v->app_document_cd }}">{{ Func::getArrayName($configArr['app_document'],$v->app_document_cd) }}</td>
            <td class="text-center">@if($v->necessary_chk=="Y")<i class='fas fa-check text-green'></i>@endif</td>
            <td class="text-center">{{ Func::getArrayName($configArr['send_type_cd'],$v->send_type_cd) }}</td>
            <td class="text-center">{{ Func::dateFormat($v->send_date) }}</td>
            <td class="text-center">{{ Func::dateFormat($v->arrival_date) }}</td>
            <td class="text-center">@if($v->scan_chk=="Y")<i class='fas fa-check text-green'></i>@endif</td>
            <td class="text-center">@if($v->keep_chk=="Y")<i class='fas fa-check text-green'></i>@endif</td>
            <td class="text-center">{{ $v->memo }}</td>
            <td class="text-center">{{ Func::getArrayName($array_user,$v->save_id) }}</td>
            <td class="text-center">{{ Func::dateFormat($v->save_time) }}</td>
        </tr>
        @endforeach
        <tr>
            <th class="text-center"></th>
            <th class="text-center">                
                <select class="form-control form-control-sm" name="app_document_cd" id="app_document_cd" required>
                <option value=''>서류 추가시 선택</option>
                {{ Func::printOption($configArr['app_document']) }}   
                </select>
            </th>
            <th class="text-center"><input type="checkbox" class="icheckbox_square-blue-sm" name="necessary_chk" id="necessary_chk" value="Y"></th>
            <th class="text-center">
                <select class="form-control form-control-sm" name="send_type_cd" id="send_type_cd" >
                <option value=''>선택</option>
                {{ Func::printOption($configArr['send_type_cd']) }}   
                </select>
            </th>
            <th class="text-center">
                <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-1" id="send_date" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#send_date" name="send_date" id="send_date" DateOnly="true" size="6">
                <div class="input-group-append" data-target="#send_date" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                </div>
                </div>
            </th>
            <th class="text-center">
                <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-1" id="arrival_date" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#arrival_date" name="arrival_date" id="arrival_date"  DateOnly="true" size="6">
                <div class="input-group-append" data-target="#arrival_date" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                </div>
                </div>
            </th>
            <th class="text-center"><input type="checkbox" class="icheckbox_square-blue-sm" name="scan_chk" id="scan_chk" value="Y"></th>
            <th class="text-center"><input type="checkbox" class="icheckbox_square-blue-sm" name="keep_chk" id="keep_chk" value="Y"></th>
            <th class="text-center"><input class="form-control form-control-sm" type="text" name="memo"></th>
            <th class="text-center" colspan=2><button onclick="docAction('INS');" type="button" class="btn btn-default btn-sm text-xs"><i class="fas fa-plus-circle p-1 text-green"></i>서류추가</button></th>
        </tr>
    </tbody>
</table>
<button class="btn btn-sm bg-lightblue float-right" type="button" onclick="docAction('UPD')">선택수정</button>
<button class="btn btn-sm bg-red float-right mr-1" type="button" onclick="docAction('DEL');">선택삭제</button>
</form>
<script>

function docAction(mode)
{
    if(checkValue(mode) == false)
    {
        return false;
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#doc_form').serialize();
    postdata += '&mode='+mode;

    $("#loan-tabs-home").html(loadingString);
    $.post(
        "/erp/loandocaction", 
        postdata, 
        function(data) {
            alert(data.result_msg);
            getLoanData('loandoc');            
            // getLoanDoc(data.loan_info_no);
    });
}


// 유효성검사
function checkValue(mode) 
{
    $(".was-validated").removeClass("was-validated");
    var result = false;

    if(mode == "INS")
    {
        if(!$('#app_document_cd').val())
        {
            $('#app_document_cd').parent().addClass("was-validated");
        }
        else
        {
            result = true;
        }
    }
    else if(mode == "UPD" && $('#app_document_cd').val())
    {
        alert("수정시 서류종류를 변경할수 없습니다.");
        $('#app_document_cd').val(null) ;
    }
    else
    {
        $('input[name="no[]"]:checked').each(function() {
            result = true;
        });

        if(result == false)
        {
            alert("체크박스를 선택해주세요");
        }
    }

    return result;
}

$(".datetimepicker").datetimepicker({
    format: 'YYYY-MM-DD',
    locale: 'ko',
    widgetPositioning: {
        horizontal: 'left',
        vertical: 'bottom'
    }
});

$('#check_all').click(function(){
    if($('#check_all').is(":checked"))
    {
        $(".no").prop("checked",true);
    }
    else
    {
        $(".no").prop("checked",false);
    }
});


$('input[id="scan_chk"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});
$('input[id="keep_chk"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});
$('input[id="necessary_chk"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});


</script>