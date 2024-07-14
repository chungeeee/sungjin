@extends('layouts.masterPop')
@section('content')




<form class="form-horizontal" name="trade_out_form" id="trade_out_form">

<div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title">예상입금조회</h2>
    </div>

    <div class="card-body mr-3 p-3">

        <div class="form-group row mt-2">
        <label for="cust_info_no" class="col-sm-2 col-form-label">차입자번호</label>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" id="cust_info_no" name="cust_info_no" readonly placeholder="" value="{{ $loan['cust_info_no'] }}"/>
        </div>
        <label for="loan_info_no" class="col-sm-2 col-form-label">계약번호</label>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" id="loan_info_no" name="loan_info_no" readonly placeholder="" value="{{ $loan['no'] }}"/>
        </div>
        <label for="cust_name" class="col-sm-2 col-form-label">이름</label>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" id="cust_name" readonly value="{{ $cust['name'] }}"/>
        </div>
        </div>

        <div class="form-group row">
        <label for="return_date" class="col-sm-2 col-form-label">이수일</label>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" id="take_date" readonly value="{{ Func::dateFormat($loan['take_date']) }}"/>
        </div>
        <label for="return_date" class="col-sm-2 col-form-label">상환일</label>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" id="return_date" readonly value="{{ Func::dateFormat($loan['return_date']) }}"/>
        </div>
        <label for="kihan_date" class="col-sm-2 col-form-label">기한이익상실일</label>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" id="kihan_date" readonly value="{{ Func::dateFormat($loan['kihan_date']) }}"/>
        </div>
        </div>

        <div class="form-group row">
        <label for="return_fee_nm" class="col-sm-2 col-form-label">조기상환수수료</label>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" id="return_fee_nm" readonly value="{{ Func::nvl($array_config['return_fee_rate'][$loan['return_fee_cd']],$loan['return_fee_cd']) }}"/>
        </div>
        <label for="return_method_nm" class="col-sm-2 col-form-label">상환방법</label>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" id="return_method_nm" readonly value="{{ Func::nvl($array_config['return_method_cd'][$loan['return_method_cd']],$loan['return_method_cd']) }}"/>
        </div>
        <label for="monthly_return_money" class="col-sm-2 col-form-label">월상환액</label>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm text-right" id="monthly_return_money" readonly value="{{ number_format($loan['monthly_return_money']) }}"/>
        </div>
        </div>


        <div class="form-group row">
        <label for="balance" class="col-sm-2 col-form-label">잔액</label>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm text-right" id="balance" readonly value="{{ number_format($loan['balance']) }}"/>
        </div>
        <label for="trade_date" class="col-sm-2 col-form-label">입금일</label>
        <div class="col-sm-2">
            <div class="input-group date datetimepicker" id="div_trade_date" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm" id="trade_date" name="trade_date" DateOnly='true' placeholder="입금일" value="{{ $trade_date }}"/>
                <div class="input-group-append" data-target="#div_trade_date" data-toggle="datetimepicker">
                    <div class="input-group-text text-xs text-center" style="width:31px;"><i class="fa fa-calendar"></i></div>
                </div>
            </div>
        </div>
        <div class="col-sm-2">
            <select class="form-control form-control-sm" name="trade_money_type" id="trade_money_type">
            <option value="trade_money">입금액</option>
            <option value="return_origin">원금입금</option>
            </select>
        </div>
        <div class="col-sm-2">
            <div class="input-group">
                <input type="text" class="form-control form-control-sm text-right moneyformat" id="trade_money" name="trade_money" placeholder="원단위 입력" value="">
                <div class="input-group-append">
                    <div class="input-group-text text-xs text-center" style="width:31px;"><i class="fas fa-won-sign"></i></div>
                </div>
            </div>
        </div>
        </div>

        <div class="form-group row">
        <label for="lose_money" class="col-sm-2 col-form-label"></label>
        <div class="col-sm-6"></div>
        <div class="col-sm-2">
            <div class="form-check text-center">
                <input style="vertical-align:middle;" class="form-check-input" type="checkbox" id="return_fee_free">
                <label style="vertical-align:middle;" class="form-check-label" for="return_fee_free">조기상환수수료면제</label>
            </div>
        </div>
        <div class="col-sm-2 text-right">
        <button type="button" class="btn btn-sm btn-info" onclick="setLoanInfoInterest();">예상입금조회</button>
        </div>
        </div>

        <div class="form-group row mt-2">
        <div class="col-sm-12" id="LoanInfoInterest">
        </div>
        </div>

    </div>
    <div class="card-footer">
        <!--
        <button type="button" class="btn btn-sm btn-info float-right mr-3" id="cate_btn" onclick="tradeInAction('');">입금등록</button>
        -->
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


function setLoanInfoInterest()
{
    var cin = $("#cust_info_no").val();
    var lin = $("#loan_info_no").val();
    var trd = $("#trade_date").val();
    var trm = $("#trade_money").val();
    var tmt = $("#trade_money_type").val();
    var rff = ( $("#return_fee_free").is(":checked") ) ? "Y" : "N" ;

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
    $("#LoanInfoInterest").html(loadingString);
    $.post("/erp/tradeininterest", {cust_info_no:cin, loan_info_no:lin, trade_type:'01', trade_path_cd:'01', trade_date:trd, trade_money:trm, lose_money:0, trade_money_type:tmt, return_fee_free:rff}, function(data) {
        $("#LoanInfoInterest").html(data);
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
</script>

@endsection
