@extends('layouts.masterPop')
@section('content')




<form class="form-horizontal" name="trade_unknown_form" id="trade_unknown_form">
<input type="hidden" name="unknown_trade_no" value="{{ $unknown_trade_no }}">


<div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title">미처리입금 등록</h2>
    </div>

    <div class="card-body mr-3 p-3">

        <div class="form-group row mt-2">
        <label for="in_name" class="col-sm-2 col-form-label">입금자명</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="in_name" name="in_name" placeholder="이름" value="{{ $rslt['in_name'] ?? '이름없음' }}">
        </div>
        <label for="reg_div" class="col-sm-2 col-form-label">등록구분</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="reg_div" name="reg_div" value="{{ Func::nvl(Vars::$arrayUnknownTradeRegDiv[$rslt['reg_div']],'') }}" readonly>
        </div>        
        </div>

        <div class="form-group row">
        <label for="trade_money" class="col-sm-2 col-form-label">입금액</label>
        <div class="col-sm-4">
            @if( $action_mode=="INSERT" )
            <div class="input-group">
                <input type="text" class="form-control form-control-sm text-right moneyformat" id="trade_money" name="trade_money" placeholder="원단위 입력" value="{{ number_format($rslt['trade_money']) }}">
                <div class="input-group-append">
                    <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fas fa-won-sign"></i></div>
                </div>
            </div>
            @else
            <input type="text" class="form-control form-control-sm text-right" id="trade_money" name="trade_money" value="{{ number_format($rslt['trade_money']) }}" readonly>
            @endif
        </div>
        <label for="trade_path_cd" class="col-sm-2 col-form-label">입금경로</label>
        @if( $action_mode=="INSERT" )
        <div class="col-sm-2">
            <select class="form-control form-control-sm" name="trade_path_cd" id="trade_path_cd">
            <? Func::printOption($array_config['trade_in_path'], '1'); ?>
            </select>
        </div>
        @else
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" value="{{ Func::nvl($array_config['trade_in_path'][$rslt['trade_path_cd']], $rslt['trade_path_cd']) }} {{ $rslt['vir_acct_ssn'] }}" readonly>
            <input type="hidden" name="trade_path_cd" id="trade_path_cd" value="{{ $rslt['trade_path_cd'] }}">
        </div>
        @endif
        </div>

        <div class="form-group row">
        <label for="trade_date" class="col-sm-2 col-form-label">입금일</label>
        <div class="col-sm-4">
            @if( $action_mode=="INSERT" )
            <div class="input-group date datetimepicker" id="div_trade_date" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm" id="trade_date" name="trade_date" DateOnly='true' placeholder="입금일" value="{{ Func::dateFormat($rslt['trade_date']) }}">
                <div class="input-group-append" data-target="#div_trade_date" data-toggle="datetimepicker">
                    <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                </div>
            </div>
            @else
            <input type="text" class="form-control form-control-sm" id="trade_date" name="trade_date" value="{{ Func::dateFormat($rslt['trade_date']) }}" readonly>
            @endif            
        </div>
        </div>

        <div class="form-group row">
        <label for="memo" class="col-sm-2 col-form-label">메모</label>
        <div class="col-sm-10">
            <textarea class="form-control form-control-sm" rows=5 id="memo" name="memo" placeholder="메모등록">{{ $rslt['memo'] }}</textarea>
        </div>
        </div>


        
        @if( $action_mode=="INSERT" || $action_mode=="UPDATE" )

        <div class="form-group row mt-2">
        <label for="search_string" class="col-sm-2 col-form-label">고객연결</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="search_string" placeholder="차입자번호,계약번호" value="" />
        </div>
        <div class="col-sm-2">
            <button type="button" class="btn btn-sm btn-info mr-3" id="btn_search_string" onclick="searchLoanInfo();">검색</button>
        </div>
        <label class="col-sm-4 text-left">
            <button type="button" class="btn btn-sm btn-info mr-3" id="customer_not_found" onclick="customerNotFound();">고객연결해제 저장</button>
        </label>
        </div>
        <div class="form-group row collapse" id="collapseSearch">
        <label class="col-sm-2 col-form-label"></label>
        <div class="col-sm-10" id="collapseSearchResult">
        </div>
        </div>

        @else
            <br>
        @endif



        <div class="form-group row">
        <label for="trade_date" class="col-sm-2 col-form-label">고객번호</label>
        <div class="col-sm-3">
            <input type="text" class="form-control form-control-sm" id="cust_info_no" name="cust_info_no" value="{{ $rslt['cust_info_no'] ?? '' }}" readonly>
        </div>
        <div class="col-sm-1">
            <button type="button" class="btn btn-sm btn-primary float-right mr-0" onclick="openLoanInfo();">계약정보창</button>
        </div>
        <label for="cust_name" class="col-sm-2 col-form-label">고객이름/생년월일</label>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" id="cust_name" name="cust_name" value="{{ $rslt['cust_name'] ?? '' }}" readonly>
        </div>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" id="cust_ssn" name="cust_ssn" value="{{ $rslt['cust_ssn'] ?? '' }}" readonly>
        </div>
        </div>






        @if( $action_mode=="UPDATE" )

        <div class="form-group row mt-2 mb-0 pb-0">
        <label class="col-sm-2 bg-secondary pt-2 col-form-label">미처리입금 정리등록</label>
        <label class="col-sm-10 pt-2 col-form-label text-right" id="trade_money_compare_txt"></label>
        </div>

        <div class="form-group row mt-0 card-secondary card-outline"></div>
        <div class="form-group row mt-2">
        <label for="find_t_money" class="col-sm-2 col-form-label pt-0">
            <input type='checkbox' name='find_div_t' id='find_div_t' class='list-check' value='T' onclick="check_find_div();">
            <label style="vertical-align:middle;" class="form-check-label ml-1" for="find_div_t">입금거래등록</label>
        </label>
        <div class="col-sm-4">
            <div class="input-group">
                <input type="text" class="form-control form-control-sm text-right moneyformat" id="find_t_money" name="find_t_money" onkeyup="calSumMoney('t');" value="{{ number_format($rslt['find_t_money']) }}" disabled>
                <div class="input-group-append">
                    <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fas fa-won-sign"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-4">
            <button type="button" class="btn btn-sm btn-info mr-3" id="btn_find_div_t" onclick="setLoanForms();" disabled>입금정보확인</button>
            <span id="t_money_balance" class="text-bold text-red mr-2"></span>
        </div>
        <div class="col-sm-2">
        </div>
        </div>
        <div class="form-group row">
            <label for="search_string" class="col-sm-2 col-form-label">입금계약정보</label>
            <div class="col-sm-10 pt-2" id="LoanInfoList">
                입금정보확인을 실행해주세요.
            </div>
        </div>

        <div class="form-group row mt-2 card-secondary card-outline"></div>
        <div class="form-group row mt-2">
        <label for="find_b_money" class="col-sm-2 col-form-label pt-0">
            <input type='checkbox' name='find_div_b' id='find_div_b' class='list-check' value='B' onclick="check_find_div();">
            <label style="vertical-align:middle;" class="form-check-label ml-1" for="find_div_b">계좌송금(반환)</label>
        </label>
        <div class="col-sm-4">
            <div class="input-group">
                <input type="text" class="form-control form-control-sm text-right moneyformat" id="find_b_money" name="find_b_money" onkeyup="calSumMoney('b');" value="{{ number_format($rslt['find_b_money']) }}" disabled>
                <div class="input-group-append">
                    <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fas fa-won-sign"></i></div>
                </div>
            </div>
        </div>
        </div>

        <div class="form-group row">
            <label for="search_string" class="col-sm-2 col-form-label">송금계좌정보</label>
            <div class="col-sm-7">
            <table class="table table-sm card-secondary card-outline mt-1 mb-1 w-100">
                <thead>
                    <tr>
                        <th class="text-center w-20">은행</th>
                        <th class="text-center w-20">계좌번호</th>
                        <th class="text-center w-20">예금주명</th>
                    </tr>
                </thead>
                <tbody id="loanInfoBankList">
                <tr>
                <td class='text-center'>
                    <input type='hidden' name='sub_bank_chk_yn'   id='sub_bank_chk_yn'   value='{{ $bank_chk_yn   ?? "" }}'>
                    <input type='hidden' name='sub_bank_chk_time' id='sub_bank_chk_time' value='{{ $bank_chk_time ?? "" }}'>
                    <input type='hidden' name='sub_bank_chk_id'   id='sub_bank_chk_id'   value='{{ $bank_chk_id   ?? "" }}'>
                    <select class='form-control form-control-sm' id='sub_bank_code' name='sub_bank_code'>
                    <option value=''>선택</option>
                    @php Func::printOption($array_config['bank_cd'], ( $bank_code ?? '' )) @endphp
                    </select>
                </td>
                <td class='text-center'><input type='text' class='form-control form-control-sm text-center' id='sub_bank_ssn'   name='sub_bank_ssn' placeholder='계좌번호' value='{{ $bank_ssn ?? "" }}'></td>
                <td class='text-center'><input type='text' class='form-control form-control-sm text-center' id='sub_bank_owner' name='sub_bank_owner' placeholder='예금주명' value='{{ $bank_owner ?? "" }}'></td>
                </tr>
                </tbody>
                </table>
            </div>
            <div class="col-sm-2 pt-2 pl-3">
                {{-- <input type='checkbox' name='firmbanking_yn' id='firmbanking_yn' class='list-check' value='Y' disabled>
                <label style="vertical-align:middle;" class="form-check-label ml-1 text-xs font-weight-bold" for="firmbanking_yn">펌뱅킹 송금</label>                 --}}
            </div>
        </div>



        <div class="form-group row mt-0 card-secondary card-outline"></div>
        <div class="form-group row mt-2">
        <label for="find_p_money" class="col-sm-2 col-form-label pt-0">
            <input type='checkbox' name='find_div_p' id='find_div_p' class='list-check' value='P' onclick="check_find_div();">
            <label style="vertical-align:middle;" class="form-check-label ml-1" for="find_div_p">잡이익등록</label>
        </label>
        <div class="col-sm-4">
            <div class="input-group">
                <input type="text" class="form-control form-control-sm text-right moneyformat" id="find_p_money" name="find_p_money" onkeyup="calSumMoney('p');" value="{{ number_format(Func::nvlEmpty($rslt['find_p_money'], 0)) }}" disabled>
                <div class="input-group-append">
                    <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fas fa-won-sign"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-6">
        </div>
        </div>



        @elseif( $action_mode=="NONE" )

        @if( $rslt['status']=="Y" )

        <div class="form-group row mt-2">
            <label for="search_string" class="col-sm-2 col-form-label">미처리입금 정리방법</label>
            <div class="col-sm-4 col-form-label">
                <div class="row">
                @if( substr_count( $rslt['find_div'], "T")>0 ) <div class="col-md-4 m-0 pr-1">입금거래등록</div> @endif
                @if( substr_count( $rslt['find_div'], "B")>0 ) <div class="col-md-4 m-0 pr-1">계좌송금(반환)</div> @endif
                @if( substr_count( $rslt['find_div'], "P")>0 ) <div class="col-md-4 m-0 pr-1">잡이익</div> @endif
                </div>
            </div>
        </div>



        @if( substr_count( $rslt['find_div'], "T")>0 )
        <div class="form-group row mt-2">
            <label for="search_string" class="col-sm-2 col-form-label">입금거래등록<br>계약번호</label>
            <div class="col-sm-10 col-form-label">
                <table class="table table-sm table-hover card-secondary card-outline">
                <thead>
                <tr>
                <th class="text-center">계약번호</th>
                <th class="text-center">입금일</th>
                <th class="text-right">입금액</th>
                <th class="text-right">비용입금</th>
                <th class="text-right">이자입금</th>
                <th class="text-right">원금입금</th>
                <th class="text-right">이자부족금</th>
                <th class="text-right">잔액</th>
                <th class="text-right">가수금</th>
                <th class="text-center">다음상환일</th>
                </tr>
                </thead>
                <tbody>
                @foreach( $rslt['trade_infos'] as $vtinfo )
                <tr role="button" onclick="loan_info_pop({{ $vtinfo->cust_info_no }}, {{ $vtinfo->loan_info_no }});">
                <td class="text-center">{{ $vtinfo->loan_info_no }}</td>
                <td class="text-center">{{ Func::dateFormat($vtinfo->trade_date) }} {{ ($vtinfo->save_status=="N") ? " (삭제)" : "" }}</td>
                <td class="text-right">{{ number_format($vtinfo->trade_money) }}</td>
                <td class="text-right">{{ number_format($vtinfo->return_cost_money) }}</td>
                <td class="text-right">{{ number_format($vtinfo->return_interest_sum) }}</td>
                <td class="text-right">{{ number_format($vtinfo->return_origin) }}</td>
                <td class="text-right">{{ number_format($vtinfo->lack_interest+$vtinfo->lack_delay_money+$vtinfo->lack_delay_interest) }}</td>
                <td class="text-right">{{ number_format($vtinfo->balance) }}</td>
                <td class="text-right">{{ number_format($vtinfo->over_money) }}</td>
                <td class="text-center">{{ Func::dateFormat($vtinfo->return_date) }}</td>
                </tr>
                @endforeach
                <tr>
                <td></td>
                <td class="text-center">입금 합계</td>
                <td class="text-right">{{ number_format($rslt['find_t_money']) }}</td>
                <td colspan=7></td>
                </tr>
                </tbody>
                </table>
            </div>
            <div class="col-sm-3 col-form-label">
            </div>
        </div>
        @endif

        @if( substr_count( $rslt['find_div'], "B")>0 )
        <div class="form-group row mt-1">
            <label for="search_string" class="col-sm-2 col-form-label">계좌송금(반환) 금액</label>
            <div class="col-sm-10 col-form-label">
            
                <table class="table table-sm table-hover card-secondary card-outline">
                <thead>
                <tr>
                <th class="text-center">반환금액</th>
                <th class="text-center">은행</th>
                <th class="text-center">계좌번호</th>
                <th class="text-center">예금주명</th>
                <th class="text-center">예금주명확인</th>
                <th class="text-center">확인사번</th>
                <th class="text-center">확인시간</th>
                <th class="text-center">펌뱅킹</th>
                <th class="text-center">처리상태</th>
                <th class="text-center">처리시간</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                <td class="text-center">{{ number_format($rslt['find_b_money']) }}원</td>
                <td class="text-center">{{ Func::nvl($array_config['bank_cd'][$rslt['bank_code']],$rslt['bank_code']) }}</td>
                <td class="text-center">{{ $rslt['bank_ssn'] }}</td>
                <td class="text-center">{{ $rslt['bank_owner'] }}</td>
                <td class="text-center">{!! ( $rslt['bank_chk_yn']=="Y" ) ? "<i class='fas fa-check text-success'></i>" : "" !!}</td>
                <td class="text-center">{{ $rslt['bank_chk_id'] }}</td>
                <td class="text-center">{{ Func::dateFormat($rslt['bank_chk_time']) }}</td>
                <td class="text-center">{!! $rslt['banking_flag'] !!}</td>
                <td class="text-center">
                    {{ Vars::$arrayFirmbankStatus[$rslt['firmbank_status']] ?? '' }}
                    @if( $rslt['firmbank_status']=="N" )
                    <a type="button" data-container="body" data-toggle="popover" data-html="true" data-placement="right" data-content="{{ $rslt['firmbank_status_code'] }} {{ Func::nvl(Vars::$arrayStebnkResultcode[$rslt['firmbank_status_code']], Func::nvl(Vars::$arrayStebnkFirmcode[$rslt['firmbank_status_code']],'')) }}">
                    <i class='fas fa-info-circle text-gray'></i>
                    </a>
                    @endif
                </td>
                <td class="text-center">{{ Func::dateFormat($rslt['firmbank_status_time']) }}</td>
                </tr>
                </tbody>
                </table>
                <div class="text-right pr-2">

                    @if( $rslt['firmbank_status']=="Z" )
                        @if( Func::funcCheckPermit("R005") )
                        <button type='button' class='ml-2 mt-2 btn btn-sm btn-success' id="bank_trans_cert_check_btn1" onclick="bankTransCertcheck('{{ $rslt['no'] }}','Y');">미처리입금 반환승인</button>
                        <button type='button' class='ml-2 mt-2 btn btn-sm btn-danger' id="bank_trans_cert_check_btn2" onclick="bankTransCertcheck('{{ $rslt['no'] }}','N');">미처리입금 반환거절</button>
                        @endif
                    @elseif( $rslt['firmbank_status']!="" )
                    <div class="mt-2 font-weight-bold">
                    <i class='fas fa-check text-success mr-2'></i> {{ "미처리입금반환승인" }} = {{ $rslt['cert_id'] }} / {{ Func::dateFormat($rslt['cert_time']) }}
                    </div> 
                    @endif

                </div>
            </div>
            
        </div>
        @endif

        @if( substr_count( $rslt['find_div'], "P")>0 )
        <div class="form-group row mt-1">
            <label for="search_string" class="col-sm-2 col-form-label">잡이익등록</label>
            <div class="col-sm-10 col-form-label">{{ number_format($rslt['find_p_money']) }}원
            </div>
        </div>
        @endif


        @endif



        @endif



        @if( $action_mode!="INSERT" )
        <div class="form-group row mt-3">
            <label for="search_string" class="col-sm-2 col-form-label">미처리입금 등록시간</label>
            <div class="col-sm-4 col-form-label">
                {{ $rslt['save_id'] }}
                ( {{ Func::dateFormat($rslt['save_time']) }} )
            </div>
        </div>
        @endif
        @if( $action_mode=="NONE" )
        <div class="form-group row mt-1">
            <label for="search_string" class="col-sm-2 col-form-label">미처리입금 {{ $rslt['status_nm'] }}</label>
            <div class="col-sm-4 col-form-label">
                @if( $rslt['status']=="Y" )
                    {{ $rslt['find_id'] }}
                    ( {{ Func::dateFormat($rslt['find_time']) }} )
                @elseif( $rslt['status']=="N" )
                    {{ $rslt['del_id'] }}
                    ( {{ Func::dateFormat($rslt['del_time']) }} )
                @endif
            </div>
        </div>
        @endif



    </div>
    <div class="card-footer">
        @if( $action_mode=="UPDATE" && Func::funcCheckPermit("A144","A") )
        <button type="button" class="btn btn-sm btn-info   float-right mr-3" id="btn_find" onclick="tradeUnknownAction('FIND');">정리</button>
        <button type="button" class="btn btn-sm btn-danger float-right mr-2" id="btn_delete" onclick="tradeUnknownAction('DELETE');">삭제</button>
        @elseif( $action_mode=="INSERT" )
        <button type="button" class="btn btn-sm btn-info float-right mr-3" id="cate_btn" onclick="tradeUnknownAction('INSERT');">미처리입금 등록</button>
        @endif
    </div>
    
</div>

</form>

@endsection

@section('javascript')

<!--
<script src="/plugins/moment/moment.min.js"></script>
<script src="/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.js"></script>
-->
<script>

$('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
	    useCurrent: false,
});
setInputMask('class', 'moneyformat', 'money');

$(function () {
    $("[data-toggle=popover]").popover();
});


// 고객검색
function searchLoanInfo()
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
    $("#collapseSearchResult").html(loadingString);
    $('.collapse').collapse('show');
    $.post("/erp/tradeinsearch", {search_string:search_string}, function(data) {
        $("#collapseSearchResult").html(data);
    });
}

// 고객검색에서 선택한 고객의 계약정보 호출
function selectLoanInfo(n)
{
    var cin = $("#cust_info_no_"+n).html();
    var cnm = $("#cust_name_"+n).html();
    var ssn = $("#cust_ssn_"+n).html();

    $("#cust_info_no").val(cin);
    $("#cust_name").val(cnm);
    $("#cust_ssn").val(clearNumber(ssn));

    $("#find_div_t").iCheck('uncheck');
    $("#find_div_b").iCheck('uncheck');
    $("#find_div_p").iCheck('uncheck');

    check_find_div();
    $('.collapse').collapse('hide');

}





@if( Func::funcCheckPermit("R005") )

// 송금승인
function bankTransCertcheck(un,st)
{


    @if( isset($rslt['find_div']) && substr_count( $rslt['find_div'], "T")>0 )
    if( st=="N" && !confirm("함께 입금정리된 거래는 자동으로 취소처리됩니다.\n계속 하시겠습니까?") )
    {
        return false;
    }
    @endif

    
    $("[id^=bank_trans_cert_check_btn]").attr("disabled", true);
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url  : "/erp/tradeunknowncertchk",
        type : "post",
        data : { unknown_trade_no:un, confirm_status:st },
        success : function(rslt)
        {
            if( rslt=="Y" )
            {
                alert("정상적으로 처리되었습니다.");
                location.reload();
            }
            else
            {
                alert(rslt);
                $("[id^=bank_trans_cert_check_btn]").attr("disabled", false);
            }
        },
        error : function(xhr)
        {
            alert("처리에 실패하였습니다.");
            $("[id^=bank_trans_cert_check_btn]").attr("disabled", false);
            console.log(xhr);
        }
    });
}

@endif











function check_find_div()
{
    var find_div_t = $("#find_div_t").is(":checked");   // 입금거래등록
    var find_div_b = $("#find_div_b").is(":checked");   // 계좌송금(반환)
    var find_div_p = $("#find_div_p").is(":checked");   // 잡이익

    // 입금거래등록
    if( find_div_t )
    {
        $("#find_t_money").prop("disabled",false);
        $("#btn_find_div_t").prop("disabled",false);
        if( !find_div_b && !find_div_p )
        {
            $("#find_t_money").val($("#trade_money").val());
        }
    }
    else
    {
        $("#LoanInfoList").html('입금정보확인을 실행해주세요.');
        $("#find_t_money").val(0);
        $("#find_t_money").prop("disabled",true);
        $("#btn_find_div_t").prop("disabled",true);
    }

    //계좌송금반환
    if( find_div_b )
    {
        $("#btn_find_div_b").prop("disabled",false);
        $("#find_b_money").prop("disabled",false);
        $("#firmbanking_yn").prop("disabled",false);
        $("[id^='sub_bank_']").prop("disabled",false);
        $("#firmbanking_yn").prop("checked",true);
        if( !find_div_t && !find_div_p )
        {
            $("#find_b_money").val($("#trade_money").val());
        }
        setBankForms();
    }
    else
    {
        $("#btn_find_div_b").prop("disabled",true);
        $("#find_b_money").val(0);
        $("#find_b_money").prop("disabled",true);
        $("#firmbanking_yn").prop("disabled",true);
        $("[id^='sub_bank_']").prop("disabled",true);
        $("#firmbanking_yn").prop("checked",false);
    }

    //잡이익
    if( find_div_p )
    {
        $("#find_p_money").prop("disabled",false);
        if( !find_div_t && !find_div_b )
        {
            $("#find_p_money").val($("#trade_money").val());
        }
    }
    else
    {
        $("#find_p_money").val(0);
        $("#find_p_money").prop("disabled",true);
    }
    calBalance();

    $('input[name="firmbanking_yn"]').iCheck({
        checkboxClass: 'icheckbox_square-blue',
    });
}
check_find_div();




function setLoanForms()
{
    var find_div_t = $("#find_div_t").is(":checked");   // 계좌송금(반환)

    if( find_div_t )
    {
        var cin = $("#cust_info_no").val();
        var tdt = $("#trade_date").val();
        var trm = $("#find_t_money").val();

        if( cin=="" || cin==0 )
        {
            alert("고객연결을 확인해주세요.");
            return false;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $("#LoanInfoList").html(loadingString);
        $.post("/erp/tradeunknownsetloan", {cust_info_no:cin, trade_date:tdt, trade_money:trm}, function(data) {
            $("#LoanInfoList").html(data);
            setInputMask('class', 'moneyformat', 'money');
            $('input[name="sms_flag"]').iCheck({
                checkboxClass: 'icheckbox_square-blue',
            });
            if($("#yu_cnt").val()=="0")
            {
                alert("선택하신 고객은 입금가능한 유효계약이 없습니다.");
            }
            else
            {
                calBalance();
            }
        });
    }
}




function tradeUnknownPreview()
{
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var formData = new FormData($('#trade_unknown_form')[0]);

    $(".divbigo").empty();
    $("#btn_preview").prop("disabled",true);

    $.ajax({
        url  : "/erp/tradeunknownpreview",
        type : "post",
        data : formData,
        dataType: "json",
        processData: false,
        contentType: false,
        success : function(data)
        {
            if( data.result=="1" )
            {
                var tdt = $("#trade_date").val();
                var lis = data.v;
                for( var i=0; i<lis.length; i++ )
                {
                    var val = lis[i];
                    var str = "";

                    if( val.PROC_FLAG=="Y" )
                    {
                        str+= "<span class='right badge badge-info mr-1'>OK</span>";
                        if( val.REPLAN_YN=="Y" )
                        {
                            str+= "<span class='right badge badge-primary mr-1'>RE</span>";
                        }
                        if( val.return_date<tdt )
                        {
                            str+= "(상환) <span class='weekend'>"+val.return_date+"</span>, ";
                        }
                        else
                        {
                            str+= "(상환) "+val.return_date+", ";
                        }
                        str+= "(잔액) "+val.balance+"원";
                    }
                    else
                    {
                        str+= "<span class='right badge badge-danger mr-1'>ER</span>";
                        str+= val.PROC_MSG;
                    }
                    $("#div_bigo_"+val.loan_info_no).html(str);
                }
            }
            else
            {
                alert(data.txt);
            }
            $("#btn_preview").prop("disabled",false);

        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
        }
    });
}






function setBankForms()
{
    var cin = $("#cust_info_no").val();

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
    $("#loanInfoBankList").html("<tr><td colspan=7>"+loadingString+"</td></tr>");
    $.post("/erp/tradeunknownbankset", {cust_info_no:cin}, function(data) {
        $("#loanInfoBankList").html(data);
    });
}



function saveForm()
{
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var formData = new FormData($('#trade_unknown_form')[0]);
    formData.append("action_mode", "SAVE");

    $.ajax({
        url  : "/erp/tradeunknownaction",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result=="Y" )
            {
                alert("정상처리 완료");
                window.opener.listRefresh();
                self.close();
            }
            else
            {
                alert(result);
                $("#btn_find").prop("disabled",false);
                $("#btn_delete").prop("disabled",false);
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
            $("#btn_find").prop("disabled",false);
            $("#btn_delete").prop("disabled",false);
        }
    });
}




function tradeUnknownAction(md)
{
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    if( md=="INSERT" && !confirm("미처리입금을 등록하시겠습니까?") )
    {
        return false;
    }
    if( md=="DELETE" && !confirm("미처리입금을 삭제하시겠습니까?") )
    {
        return false;
    }
    if( md=="FIND" && !confirm("미처리입금을 정리하시겠습니까?") )
    {
        return false;
    }
    if( $("#find_div_t").is(":checked") )
    {
        var tmny = $("#find_t_money").val();
        tmny = clearNumber(tmny) * 1;
        if( tmny==0 )
        {
            alert("입금금액이 없는경우, 입금거래등록 체크를 해제 해주세요.");
            return false;
        }
    }
    if( $("#find_div_b").is(":checked") )
    {
        var bmny = $("#find_b_money").val();
        bmny = clearNumber(bmny) * 1;
        if( bmny==0 )
        {
            alert("반환금액이 없는경우, 계좌송금(반환) 체크를 해제 해주세요.");
            return false;
        }

        // 계좌송금인데, 펌뱅킹 송금이 체크되지 않았을 경우 다시한번 확인한다.
        if(bmny > 0 && !$("#firmbanking_yn").is(":checked"))
        {
            // if(!confirm("펌뱅킹 송금이 체크되지 않았습니다. 이대로 진행하시겠습니까? "))
            // {
            //     return false;
            // }
        }
    }
    if( $("#find_div_p").is(":checked") )
    {
        var pmny = $("#find_p_money").val();
        pmny = clearNumber(pmny) * 1;
        if( pmny==0 )
        {
            alert("잡이익 금액이 없는경우, 잡이익등록 체크를 해제 해주세요.");
            return false;
        }
    }


    $("[id^='sub_bank_']").prop("disabled",false);

    var formData = new FormData($('#trade_unknown_form')[0]);
    formData.append("action_mode", md);


    if( md=="FIND" )
    {
        $("#btn_find").prop("disabled",true);
        $("#btn_delete").prop("disabled",true);
    }

    //$("#tradeInResult").html(loadingString);
    $.ajax({
        url  : "/erp/tradeunknownaction",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result=="Y" )
            {
                alert("정상처리 완료");
                window.opener.listRefresh();
                self.close();
            }
            else
            {
                alert(result);    
                $("#btn_find").prop("disabled",false);
                $("#btn_delete").prop("disabled",false);
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
            $("#btn_find").prop("disabled",false);
            $("#btn_delete").prop("disabled",false);
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
        searchLoanInfo();
      };
    });
}
enterClear();

$('input[name="find_div_t"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});
$('input[name="find_div_b"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});
$('input[name="find_div_p"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});
$('input[name="firmbanking_yn"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});



$('input[name="find_div_t"]').on('ifToggled', function(event){ check_find_div(); });
$('input[name="find_div_b"]').on('ifToggled', function(event){ check_find_div(); });
$('input[name="find_div_p"]').on('ifToggled', function(event){ check_find_div(); });



@if( $action_mode=="UPDATE" && ( $rslt['cust_info_no']==0 || $rslt['cust_info_no']=="") )

$("#search_string").val("{{ $rslt['in_name'] }}");
searchLoanInfo();

@endif





function calSumMoney(md)
{
    var smny = $("#trade_money").val();
    var tmny = $("#find_t_money").val();
    var bmny = $("#find_b_money").val();
    var pmny = $("#find_p_money").val();

    smny = clearNumber(smny) * 1;
    tmny = clearNumber(tmny) * 1;
    bmny = clearNumber(bmny) * 1;
    pmny = clearNumber(pmny) * 1;

    if( tmny > smny )
    {
        alert("입금거래등록액이 입금액보다 클 수 없습니다.");
        $("#find_t_money").val(smny);
        $("#find_b_money").val(0);
        $("#find_p_money").val(0);
    }
    if( bmny > smny )
    {
        alert("계좌송금반환액이 입금액보다 클 수 없습니다.");
        $("#find_t_money").val(0);
        $("#find_p_money").val(0);
        $("#find_b_money").val(smny);
    }
    if( pmny > smny )
    {
        alert("잡이익등록금액이 입금액보다 클 수 없습니다.");
        $("#find_t_money").val(0);
        $("#find_b_money").val(0);
        $("#find_p_money").val(smny);
    }

    // 합계비교
    if( smny < ( tmny + bmny + pmny ) )
    {
        alert("정리금액 합계가 입금액을 초과합니다.");
        if( md=="t" )
        {
            $("#find_t_money").val(0);
        }
        if( md=="b" )
        {
            $("#find_b_money").val(0);
        }
        if( md=="p" )
        {
            $("#find_p_money").val(0);
        }
    }

    calBalance();
    return true;

}


function customerNotFound()
{
    if(!confirm("고객연결을 해제하시겠습니까?"))
    {
        return false;
    }

    $("#cust_info_no").val("");
    $("#cust_name").val("");
    $("#cust_ssn").val("");

    $("#find_div_t").iCheck('uncheck');
    $("#find_div_b").iCheck('uncheck');

    check_find_div();
    $('.collapse').collapse('hide');

    saveForm();
}
    
function openLoanInfo()
{
    var cin = $("#cust_info_no").val();
    var lin = 0;

    if( cin!="" )
    {
        loan_info_pop( cin, lin );
    }
    else
    {
        alert("검색으로 계약을 선택해주세요.");
    }
}

// 처리금액 표시
function calBalance()
{
    var smny = $("#trade_money").val();
    var tmny = $("#find_t_money").val();
    var bmny = $("#find_b_money").val();
    var pmny = $("#find_p_money").val();

    smny = clearNumber(smny) * 1;
    tmny = clearNumber(tmny) * 1;
    bmny = clearNumber(bmny) * 1;
    pmny = clearNumber(pmny) * 1;

    // 정리등록 금액의 금액 비교
    $("#trade_money_compare_txt").text("정리대상금액 : " + $.number(smny) + "원 = 정리등록금액 : " + $.number(tmny+bmny+pmny) + "원");
    if( smny!=(tmny+bmny+pmny) )
    {
        $("#trade_money_compare_txt").addClass("text-danger");        
    }
    else
    {
        $("#trade_money_compare_txt").removeClass("text-danger");
    }

    // 입금거래등록의 금액비교
    var sumMoney = 0;
    var bal = tmny;
    
    $("input").each(function(idx){

        if(this.name.substr(0, 10)=='div_money_')
        {
            sumMoney += clearNumber($(this).val())*1;
        }        
    });
    bal = bal-sumMoney;

    $("#t_money_balance").text("입금처리잔액 : " + $.number(bal) + "원");

}

</script>

@endsection
