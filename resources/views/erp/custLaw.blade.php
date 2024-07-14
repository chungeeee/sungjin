<div class="p-2">

<!-- BODY -->
<b>법착리스트</b>
<button type="button" class="btn btn-xs btn-outline-info float-right mb-1" onclick="setLawInfo('');"><i class="fa fa-plus-square text-info mr-1"></i>법착추가</button>
<table class="table table-sm table-hover loan-info-table card-secondary card-outline">
    <thead>
        <tr>
            <th class="text-center">법착번호</th>
            <th class="text-center">계약번호</th>
            <th class="text-center">신청일</th>
            <th class="text-center">법착구분</th>
            <th class="text-center">법착세부</th>
            <th class="text-center">법착상태</th>
            <th class="text-center">결재상태</th>
            <th class="text-center">법원</th>
            <th class="text-center">사건번호</th>
            <th class="text-center">자동조회</th>
            <th class="text-center">청구원금</th>
            <th class="text-center">청구금액</th>
            <th class="text-center">법비용합계</th>
        </tr>
    </thead>
    <tbody id="law_list">
        @forelse( $li as $idx => $v )
            <tr onclick="setLawInfo({{ $v->no }},{{ $v->loan_info_no }})" id="law_row{{ $v->no }}"> 
                <td class="text-center">{{ $v->no }}</td>
                <td class="text-center">{{ $v->loan_info_no }}</td>
                <td class="text-center">{{ Func::dateFormat($v->law_app_date) }}</td>
                <td class="text-center">{{ Func::getArrayName($configArr['law_div_cd'],$v->law_div) }}</td>
                <td class="text-center">{{ Func::getArrayName($configArr['law_type_cd'],$v->law_type) }}</td>
                <td class="text-center">{{ Func::getArrayName($configArr['law_status_cd'],$v->law_proc_status_cd) }}</td>
                <td class="text-center">{{ Func::getArrayName($configArr['confirm_cd'],$v->law_confirm_status) }}</td>
                <td class="text-center">{{ Func::getArrayName($configArr['court_cd'],$v->court_cd) }}</td>
                <td class="text-center">{{ $v->event_year.$v->event_cd.$v->event_no }}</td>
                <td class="text-center">{!! ( $v->auto_nsf=='Y' ) ? "<i class='fas fa-check text-green'>" : "" !!}</td>

                <td class="text-right comma">{{ $v->law_won_mny }}</td>
                <td class="text-right comma">{{ $v->law_app_mny }}</td>
                <td class="text-right comma">{{ $v->law_cost_money }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="14" class='text-center p-4'><i class="fas fa-balance-scale m-2"></i>등록된 법조치 내역이 없습니다.</td>
            </tr>
        @endforelse
        <tr><td colspan="14"></td></tr>
    </tbody>
</table>

<div class="needs-validation" id="law_contents"></div>


<script>

function setLawInfo(no,loan_info_no,tab='',ino=0)
{
    $(".was-validated").removeClass("was-validated");
    $("#law_list >tr").attr('style','background-color:');
    $("#law_row"+no).attr('style','background-color:#FFDDDD');


    $("#law_contents").html(loadingString);
    $.post("/erp/custlawinfo","no="+no+"&cust_info_no={{ $cust_info_no }}&loan_info_no="+loan_info_no+"&selected_tab="+tab+"&img_no="+ino, 
        function(data) {
        $("#law_contents").html(data);
        afterAjax();
        setInputMask('class', 'moneyformat', 'money');
    });
}

function chkEventCd(cd)
{
    @foreach( $configArr['law_event_cd'] as $cd )
    if( cd == "{{ $cd }}" )
    {
        return true;
    }
    @endforeach

    return false;
}

function lawAction(mode, div_no)
{
    if(checkValue())
    {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        //if( $('#status').length>0 && $('#status').val()=="" )
        //{
        //    alert("승인여부를 선택해주세요.");
        //    return false;
        //}
        if( mode=="INS" && $('#law_app_memo').val()=="" )
        {
            alert("신청자의견을 입력해주세요.");
            return false;
        }

        if( mode=="UPD" )
        {
            for(var $i=0; $i<Number($('#cost_row_cnt').val()); $i++)
            {
                if($('#trade_cost_path_'+$i).val()=="")
                {
                    alert("결제방법을 선택해주세요.");
                    return false;
                }
            }
        }

        // 사건번호가 없으면 자동 조회를 없앤다.
        if($('#court_cd').val()=='' || $('#event_year').val()=='' || $('#event_cd').val()=='' || $('#event_no').val()=='')
        {
            $('#auto_nsf').prop('checked', false);
        }

        // var ecdhan = $('#event_cd').val();
        // var acdhan = $('#add_event_cd').val();
        
        // if( ecdhan!="" && !chkEventCd(ecdhan) )
        // {
        //     alert("사건번호를 정확히 입력해주세요.");
        //     $("#event_cd").focus();
        //     return false;
        // }
        // if( acdhan!="" && !chkEventCd(acdhan) )
        // {
        //     alert("추가 사건번호를 정확히 입력해주세요.");
        //     $("#add_event_cd").focus();
        //     return false;
        // }

        $('#loan_info_no').attr("disabled",false);
        $('#law_div').attr("disabled",false);
        $('#law_type').attr("disabled",false);

        var ldiv = $('#law_div').val();
        var ltyp = $('#law_type').val();

        if( ldiv=="E" || ldiv=="F" || ldiv=="G" || ldiv=="H" || ldiv=="I" || ltyp=="C13" || ltyp=="C14" )
        {
            if( $('input:checkbox[name="return_target[]"]:checked').length > 0 )
            {
                alert("선택하신 법조치구분은 회수대상 비용을 등록하실 수 없습니다.");
                return false;
            }
        }

        var postdata = $('#law_form').serialize();
        postdata += '&mode='+mode;

        if( mode=="DEL_COST" )
        {
            postdata += '&div_no='+div_no[0];
            postdata += '&loan_info_trade_no='+div_no[1];
            if(!confirm('해당 법비용을 삭제하시겠습니까?'))
            {
                return false;
            }
        }
        if( mode=="DEL" )
        {
            if($('#have_cost').length>0)
            {
                alert("법비용이 존재하여 삭제할수 없습니다.");
                return false;
            }
            if(!confirm('해당 법착내용을 삭제하시겠습니까?\n삭제된 법착정보는 복구할 수 없으며 필요시 재등록하셔야합니다.'))
            {
                return false;
            }
        }

        $("#customer-contents").html(loadingString);   
        $.post(
            "/erp/custlawaction", 
            postdata, 
            function(data) {
                alert(data.result_msg);
                getCustData('law',data.loan_info_no,'',data.no);
        });
    }
    else
    {
        alert("필수입력값을 확인해주세요");
    }
}

// 제3채무자 추가 action
function debtorAction(mode)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var debtor_last_row = $('.debtor').length;

    var postdata = $('#debtor_form').serialize();
    postdata += '&mode='+mode;
    postdata += '&debtor_last_row='+debtor_last_row;

    /*
    if( mode=="DEL" )
    {
    }
    */

    $("#customer-contents").html(loadingString);
    $.post(
        "/erp/custdebtoraction", 
        postdata, 
        function(data) {
            alert(data.result_msg);
            getCustData('law',data.loan_info_no,'',data.no,2);
    });
}


// 양식 action
function documentAction(mode)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#document_form').serialize();
    postdata += '&mode='+mode;

    $("#customer-contents").html(loadingString);
    $.post(
        "/erp/custdocumentaction", 
        postdata, 
        function(data) {
            alert(data.result_msg);
            getCustData('law',data.loan_info_no,'',data.no,5);
    });
}



// 등기부등본에서 제3채무자 추가
// function addDebtorAction()
// {
//     $.ajaxSetup({
//         headers: {
//             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//         }
//     });

//     var postdata = $('#searchRegistForm').serialize();

//     $.post(
//         "/erp/adddebtoraction", 
//         postdata, 
//         function(data) {
//             if(data.rslt == 'Y')
//             {
//                 alert("제3채무자 등록에 성공했습니다.");
//                 getCustData('law',data.loan_info_no,'',data.no,2);
//             }
//             else if(data.rslt == 'C')
//             {
//                 alert("체크박스를 선택해주세요.");
//                 return false;
//             }
//             else
//             {
//                 alert("제3채무자 등록에 실패했습니다. 관리자에게 문의해주세요.");
//                 return false;
//             }
//     });

// }

// 제3채무자 삭제
function deleteDebtor(seq, no, loan_info_no)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    if(!confirm('해당 제3채무자를 삭제하시겠습니까?'))
    {
        return false;
    }

    $.ajax({
        url  : "/erp/deletedebtor",
        type : "post",
        data : {seq:seq, loan_info_law_no:no, loan_info_no:loan_info_no},
        success : function(data)
        {
            if(data.rslt == 'Y')
            {
                alert("제3채무자 삭제에 성공했습니다.");
                getCustData('law',data.loan_info_no,'',data.no,2);
            }
            else
            {
                alert("제3채무자 삭제에 실패했습니다. 관리자에게 문의해주세요.");
                return false;
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다. 관리자에게 문의해주세요.");
            
        }
    });
}


// 사건정보 action
function eventAction(mode)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#status_form').serialize();

    $("#customer-contents").html(loadingString);
    $.post(
        "/erp/custeventaction", 
        postdata, 
        function(data) {
            alert(data.result_msg);
            getCustData('law',data.loan_info_no,'',data.no,3);
    });
}


// 유효성검사
function checkValue() 
{
    $(".was-validated").removeClass("was-validated");
    var result = true;
    var length = $('input[name="trade_date[]"]').length;
    //var checkName = ['select[name="cost_type[]"]']; // 필수값배열

    // 법비용 발생일이 마지막 거래일보다 이전일경우 처리실패
    $('input[name="trade_date[]"]').each(function(index, item){
            
            if(item.value && item.value.replaceAll('-','') < $("#last_trade_date").val())
            {
                console.log(item.value);
                $('input[name="trade_date[]"]').eq(index).val("");
                $('input[name="trade_date[]"]').eq(index).parent().addClass("was-validated");
                alert("법비용 발생일은 마지막거래일보다 이후로 입력해주세요");
                result = false;
                return false;
            }
    });

    if(!$('#loan_info_no').val())
    {
        $('#loan_info_no').parent().addClass("was-validated");
        result = false;
    }
    if(!$('#law_div').val())
    {
        // $('#law_div').parent().addClass("was-validated");
        // result = false;
    }
    if(!$('#law_type').val())
    {
        // $('#law_type').parent().addClass("was-validated");
        // result = false;
    }
    
    // 필수값체크
    /*
    checkName.forEach(function(name){
        $(name).each(function(index, item){
            if(!item.value)
            {
                $(name).eq(index).parent().addClass("was-validated");
                result = false;
            }

        });
    });
    */

    return result;
}

var i = 0;
function addRow()
{
    var ldiv = $('#law_div').val();
    var lctp = $('#law_cost_type_01').length;

    if( ldiv=="A" )
    {
        var disabled1 = "disabled";
        var disabled2 = "";
    }
    else
    {
        var disabled1 = "";
        var disabled2 = "disabled";
    }

    var rowString = "";
    rowString+= "<tr id='addRow"+i+"'>";

    rowString+= "<td class='text-center' id=''><input type='hidden' value=''>  </td>";

    if( lctp==0 )
    {
        rowString+= "<td class='text-center' id='law_cost_type_01'><input type='hidden' name='cost_type[]' id='cost_type_"+i+"' value='01'>필수</td>";
    }
    else
    {
        rowString+= "<td class='text-center' id='law_cost_type_02'><input type='hidden' name='cost_type[]' id='cost_type_"+i+"' value='02'>추가</td>";
    }
    rowString+= "<td class='text-center'><select class='form-control form-control-xs' data-size='10' name='trade_cost_path[]' id='trade_cost_path_"+i+"' title='선택' required><option value=''>선택</option>{{ Func::printOption($configArr['trade_cost_path'], '31') }}</select></td>";


    rowString+= "<td class='text-center'><div class='input-group mt-0 mb-0 date datetimepicker mb-0 mt-0' id='trade_date"+i+"' data-target-input='nearest'><input type='text' class='form-control form-control-xs datetimepicker-input' data-target='#trade_date"+i+"' name='trade_date[]' id='trade_date_"+i+"' DateOnly='true' size='6' required value='{{ date("Ymd") }}'><div class='input-group-append' data-target='#trade_date"+i+"' data-toggle='datetimepicker'><div class='form-control-xs input-group-text text-xs'><i class='fa fa-xs fa-calendar'></i></div></div></div></td>";

    rowString+= "<td class='text-center'><input class='form-control form-control-xs text-xs text-right moneyformat' name='postage_am[]'  id='postage_am_"+i+"'  onkeyup='onlyNumber(this);' required></td>";
    rowString+= "<td class='text-center'><input class='form-control form-control-xs text-xs text-right moneyformat' name='stamptax_am[]'  id='stamptax_am_"+i+"'  onkeyup='onlyNumber(this);' required ></td>";
    rowString+= "<td class='text-center'><input class='form-control form-control-xs text-xs text-right moneyformat' name='certitax_am[]' id='certitax_am_"+i+"' onkeyup='onlyNumber(this);' required ></td>";
    rowString+= "<td class='text-center'><input class='form-control form-control-xs text-xs text-right moneyformat' name='certissu_am[]' id='certissu_am_"+i+"' onkeyup='onlyNumber(this);' required ></td>";

    rowString+= "<td class='text-center'><input class='form-control form-control-xs text-xs text-right moneyformat' name='enforce_am[]' id='enforce_am_"+i+"' onkeyup='onlyNumber(this);' required ></td>";
    rowString+= "<td class='text-center'><input class='form-control form-control-xs text-xs text-right moneyformat' name='registtax_am[]' id='registtax_am_"+i+"' onkeyup='onlyNumber(this);' required ></td>";
    rowString+= "<td class='text-center'><input class='form-control form-control-xs text-xs text-right moneyformat' name='edu_am[]' id='edu_am_"+i+"' onkeyup='onlyNumber(this);' required ></td>";
    rowString+= "<td class='text-center'><input class='form-control form-control-xs text-xs text-right moneyformat' name='notarial_am[]' id='notarial_am_"+i+"' onkeyup='onlyNumber(this);' required ></td>";

    //rowString+= "<td class='text-center'><div class='form-inline'><span style='cursor: pointer;' class='ml-1' onclick='selectPostage(" + i + ");'><i class='fas fa-search'></i></span><input class='ml-2 w-75 form-control form-control-xs text-xs text-right moneyformat' name='postage_am[]' id='postage_am_"+i+"' onkeyup='onlyNumber(this);' required></div><input type='hidden' name='postage_use_no[]' id='postage_use_no_"+i+"' ></td>";
    rowString+= "<td class='text-center'><input class='form-control form-control-xs text-xs text-right moneyformat' name='insurance_am[]' id='insurance_am_"+i+"' onkeyup='onlyNumber(this);' required></td>";
    rowString+= "<td class='text-center'><input class='form-control form-control-xs text-xs text-right moneyformat' name='deposit_am[]'   id='deposit_am_"+i+"'   onkeyup='onlyNumber(this);' required></td>";
    rowString+= "<td class='text-center'><input class='form-control form-control-xs text-xs text-right moneyformat' name='cancel_am[]'   id='cancel_am_"+i+"'   onkeyup='onlyNumber(this);' required></td>";
    rowString+= "<td class='text-center'><input class='form-control form-control-xs text-xs text-right moneyformat' name='lawdeposit_am[]'    id='lawdeposit_am_"+i+"'    onkeyup='onlyNumber(this);' required></td>";
    rowString+= "<td class='text-center'><input class='form-control form-control-xs text-xs text-right moneyformat' name='etc_am[]'       id='etc_am_"+i+"'       onkeyup='onlyNumber(this);' required ></td>";

    // 2021-10-28 법비용 회수 여부 결정되면 원상복구 예정
    // 2023-01-31 법비용 회수 여부 임시 주석 해제
    rowString+= "<td class='text-center'><input type='checkbox' class='input-checkbox' id='return_target"+i+"' name='return_target[]' value='Y' onclick=\"$('#return_target"+i+"').is(':checked')==true?$('#return_target_hidden_"+i+"').attr('disabled',true):$('#return_target_hidden_"+i+"').attr('disabled',false)\"><input type='hidden' class='input-checkbox' id='return_target_hidden_"+i+"' name='return_target[]' value='N'></td>";
    //rowString+= "<td class='text-center'></td>";
    rowString+= "<td class='text-center'><button onclick=\"delRow('addRow"+i+"');\" type='button' class='btn btn-default btn-xs'><i class='fas fa-minus-circle p-1 text-red text-xs'></i>삭제</button></td>";
    rowString+= "</tr>";

    $('#law_cost_row').append(rowString);
    setInputMask('class', 'moneyformat', 'money');
    afterAjax();

    i++;
    var costCnt = Number($('#cost_row_cnt').val());
    $('#cost_row_cnt').val(costCnt+1);
}

function delRow(id)
{
    var costCnt = Number($('#cost_row_cnt').val());
    $('#'+id).remove();
    $('#cost_row_cnt').val(costCnt-1);
}

// 법조치에따라 법조치 구분배열 세팅
function changeDiv(div)
{

    // 법조치구분
    // $('#law_type').empty();
    // $('#law_type').append("<option value=''>선택</option>");
    // var arrayLawType = @json($configArr['law_type_cd']);
    // $.each(arrayLawType,function(key,value) {
    //    var option = $("<option value='"+key+"'>"+value+"</option>");
    //    $('#law_type').append(option);
    //})

    // 진행상태
    $('#law_status_cd').empty();
    $('#law_status_cd').append("<option value=''>선택</option>");
    var array_law_status = @json($configArr['law_status_cd']);
    $.each(array_law_status,function(key,value) {
        if( ( div=="A" && key.substring(0,1)=="A" ) || ( div!="A" && key.substring(0,1)!="A" ) || ( key.substring(0,1)=="Z" ) )
        {
            var option = $("<option value='"+key+"'>"+value+"</option>");
            $('#law_status_cd').append(option);
        }
    });


    // 소송인경우 남부지방법원
    if( $("#law_div").val()=="A" )
    {
        $("#court_cd").val("000212");
    }


    // 버튼이 활성화 되어 있을때만,,,,,
    if( $('#law_cost_add_btn').attr("disabled")=="disabled" )
    {
        return false;
    }

    $('#law_cost_row').empty();
    addRow();

}

// 송달료 선택 팝업창
function selectPostage(no)
{
    window.open('/erp/lawpostage?type=Pop&rowno=' + no, '', 'right=0,top=0,height=500,width=' + screen.width*0.5 + 'fullscreen=yes');
}

// 별지인쇄
function printDoc(div, file_name)
{
    var cust_info_no = $('#cust_info_no').val();
    var loan_info_no = $('#loan_info_no').val();
    var loan_info_law_no = $('#loan_info_law_no').val();

    var url = "/erp/printview?div="+div+"&file_name="+file_name+"&cust_info_no="+cust_info_no+"&loan_info_no="+loan_info_no+"&loan_info_law_no="+loan_info_law_no;
    var wnd = window.open(url, "printview","width=900, height=800, scrollbars=yes");
    wnd.focus();
}


</script>