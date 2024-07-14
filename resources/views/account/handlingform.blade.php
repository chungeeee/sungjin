@extends('layouts.masterPop')
@section('content')




<form class="form-horizontal" name="handling_form" id="handling_form">
<div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title">취급수수료 등록</h2>
    </div>

    <div class="card-body mr-3 p-3">


    <div class="form-group row">
        <label for="search_string" class="col-sm-2 col-form-label">검색</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="search_string" placeholder="고객번호,계약번호,이름" value="" />
        </div>
        <div class="col-sm-6 text-left">
            <button type="button" class="btn btn-sm btn-info mr-3" onclick="searchLoanInfo();">검색</button>
        </div>
        </div>

        <div class="form-group row collapse" id="collapseSearch">
        <label class="col-sm-2 col-form-label"></label>
        <div class="col-sm-10" id="collapseSearchResult">
        </div>
        </div>

        <div class="form-group row mt-2">
        <label for="cust_info_no" class="col-sm-2 col-form-label">고객번호</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="cust_info_no" name="cust_info_no" readonly placeholder="" value=""/>
        </div>
        <label for="loan_info_no" class="col-sm-2 col-form-label">계약번호</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="loan_info_no" name="loan_info_no" readonly placeholder="" value=""/>
        </div>
        </div>
        <div class="form-group row">
        <label for="cust_name" class="col-sm-2 col-form-label">이름</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="cust_name" readonly value=""/>
        </div>
        <label for="cust_ssn" class="col-sm-2 col-form-label">생년월일</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="cust_ssn" readonly value=""/>
        </div>
        </div>
        <div class="form-group row">
        <label for="return_date" class="col-sm-2 col-form-label">상환일</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="return_date" readonly value=""/>
        </div>
        <label for="kihan_date" class="col-sm-2 col-form-label">기한이익상실일</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="kihan_date" readonly value=""/>
        </div>
        </div>
        <div class="form-group row">
        <label for="return_method_nm" class="col-sm-2 col-form-label">상환방법</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="return_method_nm" readonly value=""/>
        </div>
        <label for="monthly_return_money" class="col-sm-2 col-form-label">월상환액</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="monthly_return_money" readonly value=""/>
        </div>
        </div>

        <div class="form-group row">
        <label for="div" class="col-sm-2 col-form-label">처리구분</label>
        <div class="col-sm-4">
            <select class="form-control form-control-sm" name="div" id="div">
            <? Func::printOption($array_config['handling_cd'],"01"); ?>
            </select>
        </div>
        </div>

        <div class="form-group row">
        <label for="trade_date" class="col-sm-2 col-form-label">발생일</label>
        <div class="col-sm-4">
            <div class="input-group date datetimepicker" id="div_trade_date" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm" id="trade_date" name="trade_date" DateOnly='true' placeholder="발생일" value="{{ $v->trade_date ?? ''}}"/>
                <div class="input-group-append" data-target="#div_trade_date" data-toggle="datetimepicker">
                    <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                </div>
            </div>
        </div>
        <label for="trade_money" class="col-sm-2 col-form-label">취급수수료</label>
        <div class="col-sm-4">
            <div class="input-group">
                <input type="text" class="form-control form-control-sm text-right moneyformat" id="trade_money" name="trade_money" placeholder="원단위 입력" value="{{ $v->trade_money ?? '' }}">
                <div class="input-group-append">
                    <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fas fa-won-sign"></i></div>
                </div>
            </div>
        </div>
        </div>
        
        <div class="form-group row">
        <label for="lose_money" class="col-sm-2 col-form-label"></label>
        <div class="col-sm-4">
        </div>
        <label for="return_fee_free" class="col-sm-2 col-form-label"></label>
        <div class="col-sm-4">
            <button type="button" class="btn btn-sm btn-primary float-right mr-0" onclick="openLoanInfo();">계약정보창</button>
        </div>
        </div>
        
        <input type='hidden' name='status' id='status' >

    </div>
    <div class="card-footer">
        <button type="button" class="btn btn-sm btn-info float-right" id="cate_btn" onclick="handingAction('');">취급수수료등록</button>
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
    $("#collapseSearchResult").html(loadingStringtxt);
    $('.collapse').collapse('show');
    $.post("/account/handlingsearch", {search_string:search_string}, function(data) {
        $("#collapseSearchResult").html(data);
    });

}

function selectLoanInfo(n)
{
    var cin = $("#cust_info_no_"+n).html();
    var lin = $("#loan_info_no_"+n).html();
    var cnm = $("#cust_name_"+n).html();
    var csn = $("#cust_ssn_"+n).html();
    var rtd = $("#return_date_"+n).html();
    var khd = $("#kihan_date_"+n).html();
    var rmn = $("#return_method_nm_"+n).html();
    var mrm = $("#monthly_return_money_"+n).html();
    var ls  = $("#loan_status_"+n).val();
    
    $("#cust_info_no").val(cin);
    $("#loan_info_no").val(lin);
    $("#cust_name").val(cnm);
    $("#cust_ssn").val(csn);
    $("#return_date").val(rtd);
    $("#kihan_date").val(khd);
    $("#return_method_nm").val(rmn);
    $("#monthly_return_money").val(mrm);
    $("#status").val(ls);

    $('.collapse').collapse('hide');
}


function handingAction(md)
{
    // 중복클릭 방지
    if(ccCheck()) return;

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var formData = new FormData($('#handling_form')[0]);
    formData.append("action_mode", md);

    //$("#tradeInResult").html(loadingString);
    $.ajax({
        url  : "/account/handlingaction",
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
                globalCheck = false;
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
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
changePathCd();

$('input[name="sms_flag"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});

</script>

@endsection
