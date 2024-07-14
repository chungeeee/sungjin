@extends('layouts.masterPop')
@section('content')
<title>양수/양도결재정보</title>

<script>
    window.onload = function()
    {
    }
</script>

<form class="form-horizontal" name="transfer_form" id="transfer_form">
    <input type="hidden" id="no" name="no" value="{{ isset($rs->no) ? $rs->no : '' }}">
    <input type="hidden" id="actMode" name="actMode">
    <input type="hidden" id="loan_info_no" name="loan_info_no" value="{{ isset($rs->loan_info_no) ? $rs->loan_info_no : '' }}">
    <input type="hidden" id="trade_money" name="trade_money" value="{{ isset($rs->trade_money) ? $rs->trade_money : '' }}">
    <div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title">양수/양도결재정보</h2>
    </div>

    <div class="card-body mr-3 p-3">
        <div class="row">
            <div class="col-md-8">
                <div class="form-group row">
                    <label for="search_string" class="col-sm-2 col-form-label">투자내역검색</label>
                    <div class="col-sm-4">
                        <input type="text" class="form-control form-control-sm" id="search_string" placeholder="계약번호.." value="" />
                    </div>
                    <div class="col-sm-6 text-left">
                        <button type="button" class="btn btn-sm btn-info mr-3" onclick="searchInvInfo();">검색</button>
                    </div>
                </div>

                <div class="form-group row collapse" id="collapseSearch">
                    <label class="col-sm-2 col-form-label"></label>
                    <div class="col-sm-10" id="collapseSearchResult">
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                 <div class="btn-group float-right">
                    <label class="mt-1 mr-2">반영기준일 : </label>
                    <input type="text" class="form-control form-control-sm text-center datetimepicker-input dateformat datetimepicker col-md-4" name="trade_date" id="trade_date"
                        placeholder="반영기준일" value="{{ isset($rs->trade_date) ? $rs->trade_date : date('Y-m-d') }}">
                    <div class="input-group-append" data-target="#trade_date" data-toggle="datetimepicker">
                        <div class="input-group-text ml-1"><i class="fa fa-calendar" style="font-size: 0.8rem;"></i></div>
                    </div>
                    @if( $actMode=="INSERT" || ($actMode=="UPDATE" && isset($rs->status) && $rs->status!='E') )
                    @if( $actMode=="UPDATE" )
                    <button type="button" id="btnCONFIRM" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="transferFormAction('CONFIRM');">결재완료</button>
                    <button type="button" id="btnSAVE" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="transferFormAction('SAVE');">저장</button>
                    <button type="button" id="btnCANCEL" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="transferFormAction('CANCEL');">취소</button>
                    @elseif( $actMode=="INSERT" )
                    <button type="button" id="btnINSERT" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="transferFormAction('INSERT');">등록</button>
                    @endif
                    @endif
                </div>

                <div class="form-group row usr_collapse mt-5" id="collapseSearch">
                    <label class="col-sm-2 col-form-label"></label>
                    <div class="col-sm-10" id="usrCollapseSearchResult">
                    </div>
                </div>
            </div>

            <div class="col-md-8">
                <div class="form-group row">
                    <div class="col-sm-5">
                    <b>투자내역</b>
                        <table class="table table-sm card-secondary card-outline table-hover mt-0" id="tblInv">
                        <colgroup>
                            <col width="18%"/>
                            <col width="19%"/>
                            <col width="15%"/>
                            <col width="20%"/>
                            <col width="12%"/>
                            <col width="16%"/>
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="text-center">투자자번호</th>
                                <th class="text-center">투자자명</th>
                                <th class="text-center">투자개시일</th>
                                <th class="text-center">관계</th>
                                <th class="text-center">생년월일</th>
                                <th class="text-center">투자잔액</th>
                            </tr>
                        </thead>
                        <tbody id='tblInvBody'>
                        @if ( isset($jsonData) && isset($jsonData['INV']) )
                        @foreach ( $jsonData['INV'] as $loan_info_no => $v )
                        <tr id="tr_loan_{{ $v['loan_info_no'] }}" role='button' onclick="addOutData({{ $v['loan_info_no'] }}, {{ $v['loan_usr_info_no'] }});">
                            <td id="loan_info_no_{{ $v['loan_info_no'] }}" class='text-center' hidden>{{ $v['loan_info_no'] }}</td>
                            <td id="loan_usr_info_no_{{ $v['loan_info_no'] }}" class='text-center'>{{ $v['loan_usr_info_no'] }}</td>
                            <td id="loan_usr_name_{{ $v['loan_info_no'] }}" class='text-center'>{{ $v['name'] }}</td>
                            <td id="loan_usr_trade_date_{{ $v['loan_info_no'] }}" class='text-center'>{{ $v['trade_date'] }}</td>
                            <td id="loan_usr_relation_{{ $v['loan_info_no'] }}" class='text-center'>{{ $v['relation'] }}</td>
                            <td id="loan_usr_ssn_{{ $v['loan_info_no'] }}" class='text-center'>{{ $v['ssn'] }}</td>
                            <td id="balance_{{ $v['loan_info_no'] }}" class='text-right'>{{ number_format($v['balance']) }}</td>
                        </tr>
                        @endforeach
                        @endif
                        </tbody>
                        </table>
                    </div>
                    <div class="col-sm-7">
                        <b>양도정보</b>
                        <table class="table table-sm card-secondary card-outline table-hover mt-0">
                        <colgroup>
                            <col width="12%"/>
                            <col width="15%"/>
                            <col width="13%"/>
                            <col width="15%"/>
                            <col width="13%"/>
                            <col width="11%"/>
                            <col width="13%"/>
                            <col width="13%"/>
                        </colgroup>
                        <thead>
                            <tr>
                                <th class="text-center">투자자번호</th>
                                <th class="text-center">투자자명</th>
                                <th class="text-center">투자개시일</th>
                                <th class="text-center">관계</th>
                                <th class="text-center">생년월일</th>
                                <th class="text-center">투자잔액</th>
                                <th class="text-center"></th>
                                <th class="text-center"></th>
                            </tr>
                        </thead>
                        <tbody id="trTransOut">
                        @if ( isset($jsonData) && isset($jsonData['OUT']) )
                        @foreach ( $jsonData['OUT'] as $loan_info_no => $v )
                        <tr id="tr_out_{{ $v['loan_info_no'] }}">
                            <td id="out_usr_info_no_{{ $v['loan_info_no'] }}" class="text-center">{{ $v['loan_usr_info_no'] }}</td>
                            <td id="out_usr_name_{{ $v['loan_info_no'] }}" class="text-center">{{ $v['name'] }}</td>
                            <td id="out_usr_trade_date_{{ $v['loan_info_no'] }}" class="text-center">{{ $v['trade_date'] }}</td>
                            <td id="out_usr_relation_{{ $v['loan_info_no'] }}" class="text-center">{{ $v['relation'] }}</td>
                            <td id="out_usr_ssn_{{ $v['loan_info_no'] }}" class="text-center">{{ $v['ssn'] }}</td>
                            <td id="out_balance_{{ $v['loan_info_no'] }}" class="text-right">{{ number_format($v['balance']) }}</td>
                            <td class="text-center"><input type="text" class="form-control form-control-sm text-right moneyformat" id="out_money_{{ $v['loan_info_no'] }}" name="out_money_{{ $v['loan_info_no'] }}" placeholder="원단위 입력" value="{{ number_format($v['trade_money']) }}" onkeyup="calculate('OUT', {{ $v['loan_info_no'] }});" onblur="calculate('OUT', {{ $v['loan_info_no'] }});"></td>
                            <td><button type="button" class="btn btn-sm btn-info" onclick="delOutData({{ $v['loan_info_no'] }}, {{ $v['loan_usr_info_no'] }});">삭제</button></td>
                        </tr>
                        @endforeach
                        @endif
                        </tbody>
                        </table>

                        <br>
                        <table class="table table-sm card-secondary card-outline table-hover mt-0">
                        <colgroup>
                            <col width="16%"/>
                            <col width="20%"/>
                            <col width="27%"/>
                            <col width="27%"/>
                            <col width="10%"/>
                        </colgroup>
                        <tbody>
                            <tr class="bg-secondary">
                                <td class="text-center" colspan="3">양도가능액</td>
                                <td class="text-right" id="target_money" style="font-color:red;font-weight:bold">0</td>
                                <td class="text-center"></td>
                            </tr>
                        </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mt-1">
                <div class="col-md-8 form-group row float-right">
                    <label for="usr_search_string" class="col-sm-3 col-form-label text-righ">양수대상검색</label>
                    <div class="col-sm-6">
                        <input type="text" class="form-control form-control-sm" id="usr_search_string" placeholder="투자자번호, 투자고객명" value="" />
                    </div>
                    <div class="col-sm-3 text-left">
                        <button type="button" class="btn btn-sm btn-info mr-3" onclick="searchUsrInfo();">검색</button>
                    </div>
                </div>
                <b>양수정보</b>
                <table class="table table-sm card-secondary card-outline table-hover mt-0">
                <colgroup>
                    <col width="25%"/>
                    <col width="25%"/>
                    <col width="35%"/>
                    <col width="15%"/>
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-center">투자자번호</th>
                        <th class="text-center">투자자명</th>
                        <th class="text-center">투자잔액</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody id="trTransIn">
                    @if ( isset($jsonData) && isset($jsonData['IN']) )
                    @foreach ( $jsonData['IN'] as $loan_usr_info_no => $v )
                    <tr id="tr_in_{{ $v['loan_usr_info_no'] }}" role="button">
                        <td id="in_usr_info_no_{{ $v['loan_usr_info_no'] }}" class="text-center">{{ $v['loan_usr_info_no'] }}</td>
                        <td id="in_usr_name_{{ $v['loan_usr_info_no'] }}" class="text-center">{{ $v['name'] }}</td>
                        <td class="text-center"><input type="text" class="form-control form-control-sm text-right moneyformat" id="in_money_{{ $v['loan_usr_info_no'] }}" name="in_money_{{ $v['loan_usr_info_no'] }}" placeholder="원단위 입력" value="{{ number_format($v['trade_money']) }}" onkeyup="calculate('IN',{{ $v['loan_usr_info_no'] }});" onblur="calculate('IN',{{ $v['loan_usr_info_no'] }});"></td>
                        <td><button type="button" class="btn btn-sm btn-info" onclick="delRow('tr_in', {{ $v['loan_usr_info_no'] }});">삭제</button></td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
                </table>  
            </div>
        </div>
    </div>
</form>
@endsection

@section('javascript')
<script>
$('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
	    useCurrent: false,
});
setInputMask('class', 'moneyformat', 'money');


function searchInvInfo()
{
    var search_string = $("#search_string").val();
    
    if( search_string=="" )
    {
        alert("검색어를 입력해주세요.");
        $("#search_string").focus();
        return false;
    }
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
    $("#collapseSearchResult").html(loadingStringtxt);
    $('.collapse').collapse('show');
    $.post("/account/transinvsearch", {search_string:search_string}, function(data) {
        $("#collapseSearchResult").html(data);
    });
}

function searchUsrInfo()
{
    var search_string = $("#usr_search_string").val();
    var loan_info_no = $("#loan_info_no").val();
    
    if( search_string=="" )
    {
        alert("검색어를 입력해주세요.");
        $("#usr_search_string").focus();
        return false;
    }
    if( loan_info_no=="" )
    {
        alert("양수대상을 검색하세요.");
        $("#search_string").focus();
        return false;
    }
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
    $("#usrCollapseSearchResult").html(loadingStringtxt);
    $('.collapse').collapse('show');
    $.post("/account/transusrsearch", {search_string:search_string,loan_info_no:loan_info_no}, function(data) {
        $("#usrCollapseSearchResult").html(data);
    });
}

function selectInvInfo(n)
{
    $('#loan_info_no').val(n);

    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
    $.post("/account/transinvlist", {no:n}, function(data) {
        $("#tblInvBody").html(data);
    });

    // 투자상품을 지정시 양도/양수정보 초기화
    $('#trTransOut > tr').remove();
    $('#trTransIn > tr').remove();
}

// 계산식
function calculate(div='', n=0)
{
    var chkMoney1 = chkMoney2 = chkMoney3 = 0;
    var out_money = in_money = balance = target_money = 0;
    
    var get_loan_money =  $("td[id^='out_balance_']");
    $.each(get_loan_money, function(index, value){
        balance+=Number($(value).html().replace(/,/gi,""));
    });    
    var get_outData = $("#trTransOut input[type=text]");
    $.each(get_outData, function(index, value){
        out_money+=Number($(value).val().replace(/,/gi,""));
    });
    var get_inData = $("#trTransIn input[type=text]");
    $.each(get_inData, function(index, value){
        in_money+=Number($(value).val().replace(/,/gi,""));
    });    

    // keyup으로 처리시 입력값 확인
    if(div!='' && n>0)
    {
        // 양도정보 입력시
        if(div=="OUT")
        {
            // 양도금액은 투자잔액을 초과할 수 없다.
            chkMoney1 = Number($('#out_balance_'+n).html().replace(/,/gi,""));
            chkMoney2 = Number($('#out_money_'+n).val().replace(/,/gi,""));
            if(chkMoney2 > chkMoney1)
            {
                $('#out_money_'+n).val($('#out_balance_'+n).html());
                $('#out_money_'+n).focus();
                alert('양도금액은 투자잔액을 초과할 수 없습니다.');
            }
        }
        // 양수정보 입력시
        else if(div=="IN")
        {
            if(in_money > out_money)
            {
                $minusVal = Number($('#in_money_'+n).val().replace(/,/gi,""))-(in_money-out_money);
                $minusVal = ($minusVal<0) ? 0 : $minusVal;
                $('#in_money_'+n).val($minusVal.toLocaleString('en-US'));   // Locale en-US나 ko-KR을 인자로 넣으면 3자리마다 콤마 들어간 문자열을 리턴할 수 있음.
                $('#in_money_'+n).focus();
                alert('양수금액은 양도가능액을 초과할 수 없습니다.');
            }
        }
    }

    // 최대액으로 설정된 기준으로 다시 계산
    var out_money = in_money = balance = target_money = 0;
    
    var get_loan_money =  $("td[id^='out_balance_']");
    $.each(get_loan_money, function(index, value){
        balance+=Number($(value).html().replace(/,/gi,""));
    });    
    var get_outData = $("#trTransOut input[type=text]");
    $.each(get_outData, function(index, value){
        out_money+=Number($(value).val().replace(/,/gi,""));
    });
    var get_inData = $("#trTransIn input[type=text]");
    $.each(get_inData, function(index, value){
        in_money+=Number($(value).val().replace(/,/gi,""));
    });

    $('#target_money').html(out_money-in_money).number(true);
    // 대상금액 Set
    $('#trade_money').val(out_money);
}

// 양도대상으로 지정 - 투자내역에서는 삭제처리
function addOutData(loan_info_no, loan_usr_info_no)
{
    var varAddTable = $('#trTransOut');            // 양도정보 table
    var rowItem = '<tr id="tr_out_'+loan_info_no+'">';
    rowItem += '    <td id="out_loan_info_no_'+loan_info_no+'" class="text-center" hidden>'+$('#loan_info_no_'+loan_info_no).html()+'</td>';
    rowItem += '    <td id="out_usr_info_no_'+loan_info_no+'" class="text-center">'+$('#loan_usr_info_no_'+loan_info_no).html()+'</td>';
    rowItem += '    <td id="out_usr_name_'+loan_info_no+'" class="text-center">'+$('#loan_usr_name_'+loan_info_no).html()+'</td>';
    rowItem += '    <td id="out_usr_trade_date_'+loan_info_no+'" class="text-center">'+$('#loan_usr_trade_date_'+loan_info_no).html()+'</td>';
    rowItem += '    <td id="out_usr_relation_'+loan_info_no+'" class="text-center">'+$('#loan_usr_relation_'+loan_info_no).html()+'</td>';
    rowItem += '    <td id="out_usr_ssn_'+loan_info_no+'" class="text-center">'+$('#loan_usr_ssn_'+loan_info_no).html()+'</td>';
    rowItem += '    <td id="out_balance_'+loan_info_no+'" class="text-right">'+$('#balance_'+loan_info_no).html()+'</td>';
    rowItem += '    <td class="text-center"><input type="text" class="form-control form-control-sm text-right moneyformat" id="out_money_'+loan_info_no+'" name="out_money_'+loan_info_no+'" placeholder="원단위 입력" value="'+$('#balance_'+loan_info_no).html()+'" onkeyup="checkOnlyNumber();calculate(\'OUT\', '+loan_info_no+');" onblur="calculate(\'OUT\', '+loan_info_no+');"></td>';
    rowItem += '    <td><button type="button" class="btn btn-sm btn-info" onclick="delOutData('+loan_info_no+', '+loan_usr_info_no+');">삭제</button></td>';
    rowItem += "</tr>";

    // 행추가 맨 마지막에 append
    varAddTable.append(rowItem);

    // 투자내역에서는 삭제한다.
    delRow('tr_inv', loan_info_no);
};

// 양도정보 삭제
function delOutData(loan_info_no, loan_usr_info_no)
{
    var varAddTable = $('#tblInv');                 // 투자내역정보 table
    var rowItem = '<tr id="tr_loan_'+loan_info_no+'" role="button" onclick="addOutData('+loan_info_no+', '+loan_usr_info_no+');">';
    rowItem += '    <td id="loan_info_no_'+loan_info_no+'" class="text-center" hidden>'+$('#out_loan_no_'+loan_info_no).html()+'</td>';
    rowItem += '    <td id="loan_usr_info_no_'+loan_info_no+'" class="text-center">'+$('#out_usr_info_no_'+loan_info_no).html()+'</td>';
    rowItem += '    <td id="loan_usr_name_'+loan_info_no+'" class="text-center">'+$('#out_usr_name_'+loan_info_no).html()+'</td>';
    rowItem += '    <td id="loan_usr_trade_date_'+loan_info_no+'" class="text-center">'+$('#out_usr_trade_date_'+loan_info_no).html()+'</td>';
    rowItem += '    <td id="loan_usr_relation_'+loan_info_no+'" class="text-center">'+$('#out_usr_relation_'+loan_info_no).html()+'</td>';
    rowItem += '    <td id="loan_usr_ssn_'+loan_info_no+'" class="text-center">'+$('#out_usr_ssn_'+loan_info_no).html()+'</td>';
    rowItem += '    <td id="balance_'+loan_info_no+'" class="text-right">'+$('#out_balance_'+loan_info_no).html()+'</td>';
    rowItem += "</tr>";
    // 행추가 맨 마지막에 append
    varAddTable.append(rowItem);

    // 양도정보에서는 삭제한다.
    delRow('tr_out', loan_info_no);
};

// 양수대상으로 지정
function addInData(loan_usr_info_no)
{
    var varAddTable = $('#trTransIn');            // 양수정보 table
    var rowItem = '<tr id="tr_in_'+loan_usr_info_no+'" role="button">';
    rowItem += '    <td id="in_usr_info_no_'+loan_usr_info_no+'" class="text-center">'+$('#usr_info_no_'+loan_usr_info_no).html()+'</td>';
    rowItem += '    <td id="in_usr_name_'+loan_usr_info_no+'" class="text-center">'+$('#usr_name_'+loan_usr_info_no).html()+'</td>';
    rowItem += '    <td class="text-center"><input type="text" class="form-control form-control-sm text-right moneyformat" id="in_money_'+loan_usr_info_no+'" name="in_money_'+loan_usr_info_no+'" placeholder="원단위 입력" value="" onkeyup="checkOnlyNumber();calculate(\'IN\','+loan_usr_info_no+');" onblur="calculate(\'IN\','+loan_usr_info_no+');"></td>';
    rowItem += '    <td><button type="button" class="btn btn-sm btn-info" onclick="delRow(\'tr_in\', '+loan_usr_info_no+');">삭제</button></td>';
    rowItem += "</tr>";
    // 행추가 맨 마지막에 append
    varAddTable.append(rowItem);
    calculate();
};

// 행 삭제
function delRow(target, idx)
{
    var tr = $('#'+target+"_"+idx);
    tr.remove();
    calculate();
};

// 분배등록 Action
function transferFormAction(mode) 
{
    var out_money = in_money = 0;
    var get_outData = $("#trTransOut input[type=text]");
    $.each(get_outData, function(index, value){
        out_money+=Number($(value).val().replace(/,/gi,""));
    });
    var get_inData = $("#trTransIn input[type=text]");
    $.each(get_inData, function(index, value){
        in_money+=Number($(value).val().replace(/,/gi,""));
    });

    // 양도정보 미입력 or 양수정보 미입력 or 양도가능액이 남아있는경우 등록 불가
    if(out_money <= 0 || in_money <= 0 || Number($('#target_money').html().replace(/,/gi,"")) > 0)
    {
        alert("양도정보를 확인하세요.");
        return false;
    }

    if(mode=="CANCEL")
    {
        if(!confirm("취소처리하시겠습니까?"))
        {
            return false;
        }
    }
    
    $('#btn'+mode).css('display', 'none');
    $('#actMode').val(mode);
    var postdata = $('#transfer_form').serialize();
    $.ajax({
        url  : "/account/transferaction",
        type : "post",
        data : postdata,
        success : function(data)
        {
            if(data.rs_code=="N")
            {
                alert(data.result_msg);            
                $('#btn'+mode).css('display', 'block');
            }
            else
            {
                alert(data.result_msg);            
                opener.listRefresh();
                window.close();
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다. 관리자에게 문의해주세요.");
            $('#btn'+mode).css('display', 'block');
        }
    });
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

</script>

@endsection