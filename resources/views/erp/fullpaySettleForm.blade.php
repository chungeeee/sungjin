@extends('layouts.masterPop')
@section('content')

<style>

.outline-none::-webkit-scrollbar{
    width: 8px;
    height: 10px;
}
.outline-none::-webkit-scrollbar-button {
    width: 8px;
}
.outline-none::-webkit-scrollbar-thumb {
    background: #999;
    border: thin solid gray;
    border-radius: 10px;
}
.outline-none::-webkit-scrollbar-track {
    background: #eee;
    border: thin solid lightgray;
    box-shadow: 0px 0px 3px #dfdfdf inset;
    border-radius: 10px;
}

</style>


<form class="form-horizontal" name="fullpay_settle_form" id="fullpay_settle_form">
<input type="hidden" name="loan_settle_no" value="{{ $loan_settle_no }}">

<div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title">화해완제 등록</h2>
    </div>

    <div class="card-body mr-3 p-3">

        <div class="form-group row mt-2">
        <label for="cust_info_no" class="col-sm-2 col-form-label">고객번호</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="cust_info_no" name="cust_info_no" placeholder="고객번호" value="{{ $rslt['cust_info_no'] ?? '' }}" readonly>
        </div>
        <label for="cust_name" class="col-sm-2 col-form-label">고객명</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="cust_name" name="cust_name" placeholder="이름" value="{{ $rslt['name'] ?? '' }}" readonly>
        </div>
        </div>
        
        <div class="form-group row">
        <label for="loan_info_no" class="col-sm-2 col-form-label">계약번호</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="loan_info_no" name="loan_info_no" placeholder="계약번호" value="{{ $rslt['loan_info_no'] ?? '' }}" readonly>
        </div>
        <label for="contract_date" class="col-sm-2 col-form-label">대출일</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="contract_date" name="contract_date" placeholder="대출일" value="{{ $rslt['contract_date'] ?? '' }}" readonly>
        </div>
        </div>

        <div class="form-group row mb-2">
        <label for="settle_info" class="col-sm-2 col-form-label">화해정보</label>
        <div id="loanInfoTradeDiv" class="col-sm-10 p-0 outline-none" style="height:400px;overflow-y:scroll;border:0px;">
            <div id="settle_info">
            </div>
        </div>
        </div>

        <div class="form-group row mt-4">
        <label for="balance" class="col-sm-2 col-form-label">잔여원금</label>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" id="balance" name="balance" placeholder="금리" value="{{ number_format($rslt['balance']) ?? '0' }}" readonly>
        </div>
        <label for="interest_sum" class="col-sm-2 col-form-label">잔여화해이자</label>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" id="interest_sum" name="interest_sum" placeholder="금리" value="{{ number_format($rslt['interest_sum']) ?? '0' }}" readonly>
        </div>
        <label for="trade_date" class="col-sm-2 col-form-label">완제일</label>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" id="trade_date" name="trade_date" placeholder="금리" value="{{ date('Y-m-d') }}" readonly>
        </div>
        </div>



        <div class="form-group row mt-2">
        <div class="col-sm-1"></div>
        <div class="col-sm-2 text-right"><button type="button" class="btn btn-sm btn-primary" onclick="openLoanInfo();">계약정보창</button></div>
        </div>













    </div>
    <div class="card-footer">

        @if( $rslt['lstatus']!="E" && Func::funcCheckPermit("C021") &&  Func::funcCheckPermit("A120","A") )
        <button type="button" class="btn btn-sm btn-info float-right mr-3" id="btn_confirm" onclick="fullpaySettleAction('CONFIRM');">화해완제 실행</button>
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



// 화해상환스케줄
function getLoanSettlePlan(cno)
{
    // CORS 예외처리
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $("#settle_info").html(loadingString);
    $.post("/erp/loansettleplan", { no:cno, view_opt:'NO_SIMPLE_LINE' }, function(data) {
        $("#settle_info").html(data);
    });
}
getLoanSettlePlan('{{ $rslt['loan_info_no'] }}');



function fullpaySettleAction(md)
{
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    if( md=="CONFIRM" && !confirm("화해계약을 완제처리하시겠습니까?\n잔여원리금을 모두 감면처리하고 계약상태가 완제로 변경됩니다.") )
    {
        return false;
    }

    var formData = new FormData($('#fullpay_settle_form')[0]);
    formData.append("action_mode", md);

    if( md=="CONFIRM" )
    {
        $("#btn_confirm").prop("disabled",true);
    }

    $.ajax({
        url  : "/erp/fullpaysettleformaction",
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
                $("#btn_confirm").prop("disabled",false);
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
            $("#btn_confirm").prop("disabled",false);
        }
    });
}


function openLoanInfo()
{
    var cin = $("#cust_info_no").val();
    var lin = $("#loan_info_no").val();

    if( cin!="" && lin!="" )
    {
        loan_info_pop( cin, lin );
    }
    else
    {
        alert("검색으로 계약을 선택해주세요.");
    }
}



// 엔터막기
function enterClear()
{
    $('#search_string').keydown(function() {
      if (event.keyCode === 13)
      {
        event.preventDefault();
      };
    });
}
enterClear();
</script>

@endsection
