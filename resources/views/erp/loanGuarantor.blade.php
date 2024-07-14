

<script>

// 선택한 보증인 form 에 세팅 
function setGuarantorInfo(no)
{
    $(".was-validated").removeClass("was-validated");
    $("#guarantor_list >tr").attr('style','background-color:');
    $("#guarantor_row"+no).attr('style','background-color:#FFDDDD');

    $("#guarantor_contents").html(loadingString);
    $.post("/erp/loanguarantorinfo","no="+no, 
        function(data) {
        $("#guarantor_contents").html(data);
        afterAjax();
    });
}

if('{{ $no ?? '' }}')
{
    setGuarantorInfo('{{ $no ?? '' }}');
}


// 신용정보 상세보기 팝업창
function creditForm(no, list_no, code) {
    var width = 1000;
    var height = 1000;
    if (list_no == undefined) {
        list_no = '';
    }

    // 조기경보 팝업
    if (code == 'ews') {
        width = 1200;
    }

    window.open("/erp/cust" + code + "pop?guarantor_no=" + no + "&list_no=" + list_no, "erp_" + code + "pop_" + no, "left=0, top=0, width=" + width + ", height=" + height + ", scrollbars=yes");
}

function guarantorAction()
{
    if(checkValue())
    {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#guarantor_form').serialize();
        if($('#mode').val()!="UPD")
        {
            postdata += '&loan_info_no={{ $loan_info_no ?? '' }}';
            postdata += '&mode=INS';
        }


        $("#loan-tabs-home").html(loadingString);
        $.post(
            "/erp/loanguarantoraction", 
            postdata, 
            function(data) {
                alert(data.result_msg);
                getLoanData('loanguarantor', data.no);
                // getLoanGuarantor(data.loan_info_no,data.no);
        });
    }
}

function removeAction()
{
    if(confirm('보증인을 삭제하시겠습니까?'))
    {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#guarantor_form').serialize();

        $("#loan-tabs-home").html(loadingString);
        $.post(
            "/erp/loanguarantorremoveaction", 
            postdata, 
            function(data) {
                alert(data.result_msg);
                getLoanData('loanguarantor');
                // getLoanGuarantor(data.loan_info_no,data.no);
        });
    }
}

// 유효성검사
function checkValue(msg) 
{
    var result = true;
    var checkId = ['name','ssn1','ssn2'];

    $(".was-validated").removeClass("was-validated");
    checkId.forEach(function(id){
        if(!$('#'+id).val())
        {
            result = false;
            $('#'+id).parent().addClass("was-validated");
        }
    });

    return result;
}

function alertGuarantor(sta, gno)
{
    // if(sta=='Y')
    // {
    //     if(confirm('보증인을 유효에서 비유효 변경시 조건변경으로 면탈 결재를 받아야 됩니다. 면탈요청을 하시겠습니까?'))
    //     {
    //         guarantorStatusChange(gno);
    //     }
    //     $('#status').val(sta);
    // }
}

function guarantorStatusChange(gno)
{
    window.open("/erp/conditionguarantor?gno="+gno, "conditionguarantor", "width=800, height=350, scrollbars=no");
}



</script>

<div class="p-2 needs-validation">

<!-- BODY -->
<b>보증인명세</b>
<button type="button" class="btn btn-xs btn-outline-info float-right mb-1" onclick="setGuarantorInfo('');"><i class="fa fa-plus-square text-info mr-1"></i>보증인추가</button>
{{-- <button type="button" class="btn btn-default btn-sm text-xxs float-right mb-1" onclick="setGuarantorInfo('');" ><i class="fas fa-plus-square p-1" style="color:green;"></i>보증인추가</button> --}}
<table class="table table-sm table-hover loan-info-table card-secondary card-outline">
    <thead>
        <tr>
            <th class="text-center w-5">계약번호</th>
            <th class="text-center w-5">이름</th>
            <th class="text-center w-10">주민등록</th>
            <th class="text-center w-10">채권구분1</th>
            <th class="text-center w-10">채권구분2</th>
            <th class="text-center w-5">유효</th>
            <th class="text-center w-5">동거</th>
            <th class="text-center w-5">관계</th>
            <th class="text-center w-10">집전화</th>
            <th class="text-center w-10">휴대전화</th>
            <th class="text-center w-10">직장전화</th>
            <th class="text-center w-10">직장명</th>
        </tr>
    </thead>
    <tbody id="guarantor_list">
        @forelse( $gi as $idx => $v )
            <tr onclick="setGuarantorInfo({{ $v->no ?? '' }})" id="guarantor_row{{ $v->no }}">
                <td class="text-center">{{ $v->loan_info_no ?? '' }}</td>
                <td class="text-center">{{ $v->name ?? '' }}</td>
                <td class="text-center">{{ Func::ssnFormat($v->ssn,'N') ?? '' }}</td>
                <td class="text-center">{{ Func::getArrayName($configArr['loan_cat_1_cd'],$v->g_loan_cat_1_cd) ?? '' }}</td>
                <td class="text-center">{{ Func::getArrayName($configArr['loan_cat_2_cd'],$v->g_loan_cat_2_cd) ?? '' }}</td>

                <td class="text-center">{{ $v->status ?? '' }}</td>
                <td class="text-center">{{ $v->live_together ?? '' }}</td>
                <td class="text-center">{{ Func::getArrayName($configArr['relation_cd'],$v->relation_cd) ?? '' }}</td>
                <td class="text-center">{{ Func::phFormat($v->ph11,$v->ph12,$v->ph13) ?? '' }}</td>
                <td class="text-center">{{ Func::phFormat($v->ph21,$v->ph22,$v->ph23) ?? '' }}</td>
                <td class="text-center">{{ Func::phFormat($v->ph31,$v->ph32,$v->ph33)." ".$v->ph34 ?? '' }}</td>
                <td class="text-center">{{ $v->com_name ?? '' }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="13" class='text-center p-4'><i class="fas fa-user m-2"></i>등록된 보증인이 없습니다.</td>
            </tr>
        @endforelse
        <tr><td colspan="13"></td></tr>
    </tbody>
</table>

<div id="guarantor_contents"></div>
