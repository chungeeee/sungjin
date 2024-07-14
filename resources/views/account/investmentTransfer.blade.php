@extends('layouts.masterPop')
@section('content')
<title>양도/양수결재정보</title>

<script>
    window.onload = function()
    {
    }
</script>

<!-- <form class="form-horizontal" name="transfer_form" id="transfer_form"> -->
<form class="mb-0" name="investmentTransfer_form" id="investmentTransfer_form" method="post" enctype="multipart/form-data">
    <input type="hidden" id="no" name="no" value="{{ isset($rs->no) ? $rs->no : '' }}">
    <input type="hidden" id="actMode" name="actMode">
    <input type="hidden" id="loan_info_no" name="loan_info_no" value="{{ isset($rs->loan_info_no) ? $rs->loan_info_no : '' }}">
    <input type="hidden" id="trade_money" name="trade_money" value="{{ isset($rs->trade_money) ? $rs->trade_money : '' }}">
    <input type="hidden" id="out_usr_info_no" name="out_usr_info_no" value="{{ $out_usr_info_no ?? '' }}">
    <input type="hidden" id="in_usr_info_no" name="in_usr_info_no" value="{{ $in_usr_info_no ?? '' }}">
    <input type="hidden" id="loan_usr_info_no" name="loan_usr_info_no" value="{{ $v->loan_usr_info_no ?? '' }}">
    <input type="hidden" id="transfer_date" name="transfer_date" value="{{ $transfer_date ?? '' }}">
    <input type="hidden" id="transfer_trade_money" name="transfer_trade_money" value="{{ $transfer_trade_money ?? '' }}">
    <div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title">양도/양수결재정보</h2>
    </div>

    <div class="card-body mr-3 p-3">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group row">
                    <div class="col-sm-8">
                        <b>양도정보</b>
                        <table class="table table-sm card-secondary card-outline table-hover mt-0">
                        <colgroup>
                            <col width="15%"/>
                            <col width="15%"/>
                            <col width="15%"/>
                            <col width="15%"/>
                            <col width="22%"/>
                            <col width="23%"/>
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="text-center">투자자번호</th>
                                <th class="text-center">투자자명</th>
                                <th class="text-center">관계</th>
                                <th class="text-center">생년월일</th>
                                <th class="text-center">투자잔액</th>
                                <th class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="trTransOut">
                        @if ( isset($jsonData) && isset($jsonData['OUT']) )
                        @foreach ( $jsonData['OUT'] as $loan_info_no => $v )
                        <tr id="tr_out_{{ $v['loan_info_no'] }}" onclick="addInList({{ $v['loan_info_no'] }}, {{ $v['loan_usr_info_no'] }}, {{ $v['transfer_date'] }});" role="button" @if($v['loan_usr_info_no']==$out_usr_info_no) style="background-color:#d9e9f9;" @endif>
                            <td id="out_usr_info_no_{{ $v['loan_info_no'] }}" class="text-center">{{ $v['loan_usr_info_no'] }}</td>
                            <td id="out_usr_name_{{ $v['loan_info_no'] }}" class="text-center">{{ $v['name'] }}</td>
                            <td id="out_usr_relation_{{ $v['loan_info_no'] }}" class="text-center">{{ $v['relation'] }}</td>
                            <td id="out_usr_ssn_{{ $v['loan_info_no'] }}" class="text-center">{{ $v['ssn'] }}</td>
                            <td id="out_balance_{{ $v['loan_info_no'] }}" class="text-center">{{ number_format($v['balance']) }}</td>
                            <td class="text-center"><input type="text" class="form-control form-control-sm text-right moneyformat" id="out_money_{{ $v['loan_info_no'] }}" name="out_money_{{ $v['loan_info_no'] }}" value="{{ number_format($v['trade_money']) }}" readonly></td>
                        </tr>
                        @endforeach
                        @else
                        <tr class="text-center">
                            <td colspan='6'>양도/양수 이력이 없습니다.</td>
                        </tr>
                        @endif
                        </tbody>
                        </table>

                        <table class="table table-sm card-secondary card-outline table-hover mt-0">
                        <colgroup>
                            <col width="16%"/>
                            <col width="20%"/>
                            <col width="27%"/>
                            <col width="27%"/>
                            <col width="10%"/>
                        </colgroup>
                        </table>
                    </div>
                    @if ( isset($jsonData) && isset($jsonData['IN']) )
                    <div class="col-sm-4">
                        <b>양수정보</b>
                        <table class="table table-sm card-secondary card-outline table-hover mt-0">
                        <colgroup>
                            <col width="30%"/>
                            <col width="30%"/>
                            <col width="40%"/>
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="text-center">투자자번호</th>
                                <th class="text-center">투자자명</th>
                                <th class="text-center">투자잔액</th>
                            </tr>
                        </thead>
                        <tbody id="trTransIn">
                            @foreach ( $jsonData['IN'] as $loan_usr_info_no => $v )
                            <tr id="tr_in_{{ $v['loan_usr_info_no'] }}" onClick="addPrintForm({{ $out_loan_no}}, {{ $v['loan_usr_info_no'] }}, {{ $v['trade_money'] }})" role="button" @if($v['loan_usr_info_no']==$in_usr_info_no) style="background-color:#d9e9f9;" @endif>
                                <td id="in_usr_info_no_{{ $v['loan_usr_info_no'] }}" class="text-center">{{ $v['loan_usr_info_no'] }}</td>
                                <td id="in_usr_name_{{ $v['loan_usr_info_no'] }}" class="text-center">{{ $v['name'] }}</td>
                                <td class="text-center"><input type="text" class="form-control form-control-sm text-right moneyformat" id="in_money_{{ $v['loan_usr_info_no'] }}" name="in_money_{{ $v['loan_usr_info_no'] }}" value="{{ number_format($v['trade_money']) }}" readonly></td>
                            </tr>
                            @endforeach
                        </tbody>
                        </table>  
                    </div>
                    @endif
                </div>
                <div class="col-md-12 p-0 m-0 " @if($printAction!='true') style="display:none" @endif>
                    <div class="form-goup row">
                        <b>징구서류관리</b>
                        <table class="table table-sm table-hover loan-info-table card-secondary card-outline">
                            <thead>
                                <tr>
                                    <th class="text-center"><input id="check_all" type="checkbox" class="icheckbox_square-blue-sm"></th>
                                    <th class="text-center"><span class="text-danger font-weight-bold h6 mr-1">*</span>서류</th>
                                    <th class="text-center w-10">최초인쇄일</th>
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
                            <tbody id="loan_document">
                                @foreach( Vars::$arrayInvestPaper as $key => $val )
                                <tr>
                                    <td class="text-center"><input type="checkbox" class="icheckbox_square-blue-sm docs_cd" id="docs_cd[]" name="docs_cd[]" value="{{ $key }}"></td>
                                    <td class="text-center">{{ $val }}</td>
                                    <td class="text-center">{{ isset($arrayDocData[$key])?Func::dateFormat($arrayDocData[$key]['print_date']):"" }}</td>
                                    <td class="text-center">{{ Func::getArrayName($configArr['send_type_cd'], $arrayDocData[$key]['send_type_cd'] ?? '') }}</td>
                                    <td class="text-center">{{ isset($arrayDocData[$key])?Func::dateFormat($arrayDocData[$key]['send_date']):"" }}</td>
                                    <td class="text-center">{{ isset($arrayDocData[$key])?Func::dateFormat($arrayDocData[$key]['arrival_date']):"" }}</td>
                                    <td class="text-center">@if(isset($arrayDocData[$key]) && $arrayDocData[$key]['scan_chk']=="Y")<i class='fas fa-check text-green'></i>@endif</td>
                                    <td class="text-center">@if(isset($arrayDocData[$key]) && $arrayDocData[$key]['keep_chk']=="Y")<i class='fas fa-check text-green'></i>@endif</td>
                                    <td class="text-center">{{ isset($arrayDocData[$key])?$arrayDocData[$key]['memo']:"" }}</td>
                                    <td class="text-center">{{ Func::getArrayName($array_user,$arrayDocData[$key]['save_id'] ?? '') }}</td>
                                    <td class="text-center">{{ isset($arrayDocData[$key])?Func::dateFormat($arrayDocData[$key]['save_time']):"" }}</td>
                                </tr>
                                @endforeach
                            </tbody>
                            <tbody>
                                <tr>
                                    <th class="text-center"></th>
                                    <th class="text-center"></th>
                                    <th class="text-center"></th>
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
                                    <th class="text-center" colspan=2><button onclick="docAction('UPDATE');" type="button" class="btn btn-sm bg-lightblue">선택적용</button></th>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="form-group row col-sm-6">
                        <b style="margin-bottom:4px;">사모사채 양도양수 계약서 인쇄</b>
                        <table class="table table-sm table-bordered table-input text-xs">
                            <colgroup>
                                <col width="20%" />
                                <col width="80%" />
                            </colgroup>
                            <tbody>
                            <input type="hidden" id="post_cd" name="post_cd" value="SM003">
                            <tr>
                                <th>기준일자</th>
                                <td>
                                    <div class="input-group date datetimepicker" id="basis_date" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm col-sm-3 dateformat datetimepicker" name="print_basis_date" id="print_basis_date" inputmode="text" value="{{ date('Y-m-d') }}" DateOnly="true" size="6">
                                        <div class="input-group-append" data-target="#print_basis_date" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th>마스킹여부</th>
                                <td>
                                    <div class="input-group">
                                        <input type="checkbox" class="icheckbox_square-blue-sm masking" id="masking" name="masking" value="Y"></div>
                                    </div>
                                </td>
                            </tr>

                            <tr>
                                <th>양도인 주소</th>
                                <td>
                                    <div class="row">
                                        <div class="input-group col-sm-4 pb-1">
                                            <input type="hidden" id="post_addr_cd" name="post_addr_cd" value=""/>
                                            <input type="text" class="form-control" name="out_zip" id="out_zip" numberonly="true" value="">
                                        </div>
                                        <div class="pl-0 p-1">
                                            <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_1" onclick="setAddr('out_zip', 'out_addr1', 'out_addr2', '{{$out_print_info->zip1 ?? ''}}', '{{$out_print_info->addr11 ?? ''}}', '{{$out_print_info->addr12 ?? ''}}');">주소1</button>
                                            <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_2" onclick="setAddr('out_zip', 'out_addr1', 'out_addr2', '{{$out_print_info->zip2 ?? ''}}', '{{$out_print_info->addr21 ?? ''}}', '{{$out_print_info->addr22 ?? ''}}');">주소2</button>
                                            <button type="button" class="btn btn-secondary btn-xs" onclick="setAddr('out_zip', 'out_addr1', 'out_addr2', '', '', ''); ">지우기</button>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control mb-1 col-md-10" name="out_addr1" id="out_addr1" value="" >
                                    <input type="text" class="form-control col-md-10" name="out_addr2" id="out_addr2" value="">
                                </td>
                            </tr>
                            <tr>
                                <th>양수인 주소</th>
                                <td>
                                    <div class="row">
                                        <div class="input-group col-sm-4 pb-1">
                                            <input type="hidden" id="post_addr_cd" name="post_addr_cd" value=""/>
                                            <input type="text" class="form-control" name="in_zip" id="in_zip" numberonly="true" value="">
                                        </div>
                                        <div class="pl-0 p-1">
                                            <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_1" onclick="setAddr('in_zip', 'in_addr1', 'in_addr2', '{{$in_print_info->zip1 ?? ''}}', '{{$in_print_info->addr11 ?? ''}}', '{{$in_print_info->addr12 ?? ''}}');">주소1</button>
                                            <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_2" onclick="setAddr('in_zip', 'in_addr1', 'in_addr2', '{{$in_print_info->zip2 ?? ''}}', '{{$in_print_info->addr21 ?? ''}}', '{{$in_print_info->addr22 ?? ''}}');">주소2</button>
                                            <button type="button" class="btn btn-secondary btn-xs" onclick="setAddr('in_zip', 'in_addr1', 'in_addr2', '', '', ''); ">지우기</button>
                                        </div>
                                    </div>
                                    <input type="text" class="form-control mb-1 col-md-10" name="in_addr1" id="in_addr1" value="" >
                                    <input type="text" class="form-control col-md-10" name="in_addr2" id="in_addr2" value="">
                                </td>
                            </tr>
                            
                            <tr>
                                <td colspan=2 class="">
                                    <button type="button" class="btn btn-sm btn-secondary  mb-1" onclick="printAction();">
                                        <i class="fas fa-print"></i> 인쇄
                                    </button>
                                </td>
                            </tr>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</form>
<script>


function docAction(mode)
{
    if(mode=='UPDATE')
    {
        if(checkValue() == false)
        {
            return false;
        }
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var rs_code;
    var postdata = $('#investmentTransfer_form').serialize();
    postdata += '&mode='+mode;
    $("#loan_document").html(loadingString);
    $.post(
        "/account/investmentpaperaction", 
        postdata, 
        function(data) {
            rs_code = data.rs_code;
            if(data.rs_code!="Y")
            {
                alert(data.result_msg);
            }
            else
            {
                $("#loan_document").html(data.loan_ducument_html);
            }

            return rs_code;
    });
}


// 유효성검사
function checkValue() 
{
    $(".was-validated").removeClass("was-validated");
    var result = false;

    $('input[name="docs_cd[]"]:checked').each(function() {
        result = true;
    });

    if(result == false)
    {
        alert("체크박스를 선택해주세요");
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
        $(".docs_cd").prop("checked",true);
    }
    else
    {
        $(".docs_cd").prop("checked",false);
    }
});


$('input[id="scan_chk"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});
$('input[id="keep_chk"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});

// $('#print_basis_date').datetimepicker({
//     format: 'YYYY-MM-DD',
//     locale: 'ko',
//     useCurrent: false,
//     widgetPositioning:{
//         horizontal : 'auto',
//         vertical: 'bottom'
//     }
// });

function printAction()
{
    // 최초인쇄일 UPDATE
    docAction('PAPER');

    var urld  = "/lump/printview";
    var title = "printview";

    var formdata = $('#investmentTransfer_form').serializeArray();
    var url = urld+"?fData="+JSON.stringify(formdata);
    var wnd = window.open(url, title,"width=900, height=800, scrollbars=yes");
    wnd.focus();
}


</script>
@endsection

@section('javascript')
<script>

function addInList(out_loan_no, out_usr_info_no, transfer_date)
{
    $('#transfer_date').val(transfer_date);
    recallTransferForm('investmenttransfer', out_loan_no, false, out_usr_info_no, '', transfer_date);
}

function addPrintForm(out_loan_no, in_usr_info_no, transfer_trade_money)
{
    var out_usr_info_no = $('#out_usr_info_no').val();
    var transfer_date = $('#transfer_date').val();
    recallTransferForm('investmenttransfer', out_loan_no, true, out_usr_info_no, in_usr_info_no, transfer_date, transfer_trade_money);
}

// 엔터막기
function enterClear()
{
    $('#search_string').keydown(function() {
      if (event.keyCode === 13)
      {
        event.preventDefault();
        searchInvInfo();
      };
    });

    $('#usr_search_string').keydown(function() {
      if (event.keyCode === 13)
      {
        event.preventDefault();
        searchUsrInfo();
      };
    });
}
enterClear();

// 유효성검사
function checkValue() 
{
    $(".was-validated").removeClass("was-validated");
    var result = false;

    $('input[name="docs_cd[]"]:checked').each(function() {
        result = true;
    });

    if(result == false)
    {
        alert("체크박스를 선택해주세요");
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
        $(".docs_cd").prop("checked",true);
    }
    else
    {
        $(".docs_cd").prop("checked",false);
    }
});


$('input[id="scan_chk"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});
$('input[id="keep_chk"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});

// $('#print_basis_date').datetimepicker({
//     format: 'YYYY-MM-DD',
//     locale: 'ko',
//     useCurrent: false,
//     widgetPositioning:{
//         horizontal : 'auto',
//         vertical: 'bottom'
//     }
// });

function printAction()
{
    var postCd = $('#post_cd').val();
    if(!postCd)
    {
        alert('인쇄양식을 선택해주세요.');
        return false;
    }
    
    // 최초인쇄일 UPDATE
    docAction('PAPER');

    var urld  = "/lump/printview";
    var title = "printview";

    var formdata = $('#investmentTransfer_form').serializeArray();
    var url = urld+"?fData="+JSON.stringify(formdata);
    var wnd = window.open(url, title,"width=900, height=800, scrollbars=yes");
    wnd.focus();
}



</script>

@endsection