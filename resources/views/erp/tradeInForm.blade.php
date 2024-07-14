@extends('layouts.masterPop')
@section('content')




<form class="form-horizontal" name="trade_out_form" id="trade_out_form">

<div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title">입금 등록</h2>
    </div>

    <div class="card-body mr-3 p-3">


    <div class="form-group row">
        <label for="search_string" class="col-sm-2 col-form-label">검색</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="search_string" placeholder="고객번호,계약번호,이름,상품명" value="" />
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
            
        </div>

        <div class="form-group row">
            <label for="return_method_nm" class="col-sm-2 col-form-label">상환방법</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" id="return_method_nm" readonly value=""/>
            </div>
            <label for="kihan_date" class="col-sm-2 col-form-label">기한이익상실일</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" id="kihan_date" readonly value=""/>
            </div>
           
        </div>
        
        <div class="form-group row">
            <label for="trade_type" class="col-sm-2 col-form-label">입금구분</label>
            <div class="col-sm-4">
                <select class="form-control form-control-sm" name="trade_type" id="trade_type">
                <? Func::printOption($array_config['trade_in_type'],"01"); ?>
                </select>
            </div>
            <label for="monthly_return_money" class="col-sm-2 col-form-label">월상환액</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" id="monthly_return_money" readonly value=""/>
            </div>
            {{-- <label for="trade_path_cd" class="col-sm-2 col-form-label">입금경로</label>
            <div class="col-sm-4">
                <select class="form-control form-control-sm" name="trade_path_cd" id="trade_path_cd" onchange="changePathCd(this.value);">
                <? Func::printOption($array_config['trade_in_path'],Func::nvl2($v->trade_path_cd,'03')); ?>
                </select>
            </div>--}}
        </div>

        <div class="form-group row">
            <label for="trade_date" class="col-sm-2 col-form-label">입금기준일</label>
            <div class="col-sm-4">
                <div class="input-group date datetimepicker" id="div_trade_date" data-target-input="nearest">
                    <input type="text" class="form-control form-control-sm" id="trade_date" name="trade_date" DateOnly='true' placeholder="입금기준일" value="{{ $v->trade_date ?? ''}}"/>
                    <div class="input-group-append" data-target="#div_trade_date" data-toggle="datetimepicker">
                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>
            <label for="transaction_date" class="col-sm-2 col-form-label">실입금일</label>
            <div class="col-sm-4">
                <div class="input-group date datetimepicker" id="div_transaction_date" data-target-input="nearest">
                    <input type="text" class="form-control form-control-sm" id="transaction_date" name="transaction_date" DateOnly='true' placeholder="실거래일" value="{{ date('Y-m-d') }}"/>
                    <div class="input-group-append" data-target="#div_transaction_date" data-toggle="datetimepicker">
                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="trade_money" class="col-sm-2 col-form-label">입금액</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm text-right moneyformat" id="trade_money" name="trade_money" placeholder="원단위 입력" value="{{ $v->trade_money ?? '' }}">
                    <div class="input-group-append">
                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fas fa-won-sign"></i></div>
                    </div>
                </div>
            </div>

            <label for="loan_balance" class="col-sm-2 col-form-label">대출잔액</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm text-right moneyformat" id="loan_balance" name="loan_balance" placeholder="원단위 입력" value="" readonly>
                    <div class="input-group-append">
                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fas fa-won-sign"></i></div>
                    </div>
                </div>
            </div>

            <label for="request_return_origin_money" class="col-sm-2 col-form-label" id="tit_origin" style="display:none;">원금상환액</label>
            <div class="col-sm-4" id="input_origin" style="display:none;">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm text-right moneyformat" id="request_return_origin_money" name="request_return_origin_money" placeholder="원단위 입력" value="{{ $v->request_return_origin_money ?? '' }}">
                    <div class="input-group-append">
                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fas fa-won-sign"></i></div>
                    </div>
                </div>
            </div>
            {{-- <label for="in_name" class="col-sm-2 col-form-label">입금자명</label>
            <div class="col-sm-4">
                <div class="input-group date" id="loan_date" data-target-input="nearest">
                    <input type="text" class="form-control form-control-sm" id="in_name" name="in_name" placeholder="미입력시 고객명 등록" value="{{ $v->in_name ?? '' }}" />
                </div>
            </div>--}}
        </div>
        
        <div class="form-group row">
            <label for="lose_money" class="col-sm-2 col-form-label">감면액</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm text-right moneyformat" id="lose_money" name="lose_money" placeholder="원단위 입력" value="" readonly>
                    <div class="input-group-append">
                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fas fa-won-sign"></i></div>
                    </div>
                </div>
            </div>

            <label for="loan_rate" class="col-sm-2 col-form-label">차주이율</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" name="loan_rate" id="loan_rate" class="form-control form-control-sm text-right floatnum" value="" placeholder="정상이율" autocomplete="off" readonly>
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fa fa-percent" style="font-size: 0.65rem;"></i></div>
                    </div>
                </div>
            </div>
            <label for="memo" class="col-sm-2 col-form-label">메모</label>
            <div class="col-sm-10">
                <div class="input-group date" id="loan_date" data-target-input="nearest">
                    <textarea class="form-control form-control-sm" name="memo" id= "memo" cols="40" rows="2" style="width:58%" placeholder="메모"  >{{ $v->memo ?? '' }}</textarea>
                    
                </div>
            </div>
        </div>

        <div class="form-group row mt-3">
            <label for="lose_money" class="col-sm-2 col-form-label"></label>
            <div class="col-sm-4 text-right">
                {{-- <input type='checkbox' name='sms_flag' id='sms_flag' class='list-check pr-0' value='A'> --}}
                {{-- <label class="form-check-label ml-1 mr-0" for="sms_flag">SMS발송</label> --}}
            </div>
            <label class="col-sm-2 col-form-label">
            </label>
            <div class="col-sm-4">
            {{-- <input type='checkbox' name='return_fee_free' id='return_fee_free' class='list-check pr-0' value='Y'>
                <label class="form-check-label ml-1 mr-0" for="return_fee_free">조기상환수수료 면제</label>
                --}}
                <button type="button" class="btn btn-sm btn-primary float-right mr-0" onclick="openLoanInfo();">계약정보창</button>
            </div>
        </div>

        <div class="form-group row mt-2">
        <div class="col-sm-12" id="LoanInfoInterest">
        </div>
        </div>
        
        <input type='hidden' name='status' id='status' >
        <input type='hidden' name='bank_statement_no' id='bank_statement_no' value="{{ $v->bank_statement_no ?? '' }}">

    </div>
    <div class="card-footer">
        <button type="button" class="btn btn-sm btn-info float-right" id="cate_btn" onclick="tradeInAction('');">입금등록</button>
        <button type="button" class="btn btn-sm btn-secondary float-right mr-1" onclick="setLoanInfoInterest();">입금처리 미리보기</button>
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
    $.post("/erp/tradeinsearch", {search_string:search_string}, function(data) {
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
    var lb  = $("#loan_balance_"+n).html();
    var lr  = $("#loan_rate_"+n).val();
    
    $("#cust_info_no").val(cin);
    $("#loan_info_no").val(lin);
    $("#cust_name").val(cnm);
    $("#cust_ssn").val(csn);
    $("#return_date").val(rtd);
    $("#kihan_date").val(khd);
    $("#return_method_nm").val(rmn);
    $("#monthly_return_money").val(mrm);
    $("#status").val(ls);
    $("#loan_balance").val(lb);
    $("#loan_rate").val(lr);

    $("#trade_type").val('01');
    {{-- $("#trade_money").val('0'); --}}
    {{-- $("#trade_date").val('{{ date("Y-m-d") }}'); --}}
    $("#lose_money").val('0');
    setLoanInfoInterest();

    $('.collapse').collapse('hide');
}


function setLoanInfoInterest()
{
    var cin = $("#cust_info_no").val();
    var lin = $("#loan_info_no").val();
    var tty = $("#trade_type").val();
    var tpc = $("#trade_path_cd").val();
    var trd = $("#trade_date").val();
    var trm = $("#trade_money").val();
    var lsm = $("#lose_money").val();
    var rom = $("#request_return_origin_money").val();
    var rff = ( $("#return_fee_free").is(":checked") ) ? "Y" : "N" ;

    // 입력값 정합성 체크
    if(!validCheck())
    {
        return false;
    }
    
    console.log(cin+' / '+lin+' / '+tty+' / '+tpc+' / '+trd+' / '+trm+' / '+lsm+' / '+rff);

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
    $("#LoanInfoInterest").html(loadingString);
    $.post("/erp/tradeininterest", {cust_info_no:cin, loan_info_no:lin, trade_type:tty, trade_path_cd:tpc, trade_date:trd, trade_money:trm, lose_money:lsm, return_fee_free:rff, request_return_origin_money:rom}, function(data) {
        $("#LoanInfoInterest").html(data);
    });
}


function changePathCd(val)
{
    if( val=="01" )
    {
        $("#trade_bank_info").attr("disabled", true);
    }
    else
    {
        $("#trade_bank_info").attr("disabled", false);
    }
}

// 입금구분 선택시
$('#trade_type').focus(function(){
    prev_val = $(this).val();
}).change(function(){
    var val = $(this).val();
    if( val=="03" )
    {
        $("#lose_money").prop('readonly', false);
    }
    else if( val=="11" )
    {
        if( $("#trade_date").val()=="" )
        {
            alert("입금기준일을 먼저 입력해주세요.");
            $("#trade_type").val(prev_val);
            return false;            
        }

        if(( $("#status").val()=="B" || $("#status").val()=="D" ) && ( $("#return_date").val() < $("#trade_date").val() ))
        {
            alert("원금우선상환은 정상채권만 실행가능합니다.\n([입금일 < 상환일] 일 경우 가능)");
            $("#trade_type").val(prev_val);
            return false;            
        }

        $("#tit_origin").css('display', 'block');
        $("#input_origin").css('display', 'block');
    }
    else
    {
        $("#lose_money").val("");
        $("#lose_money").prop('readonly', true);

        $("#tit_origin").css('display', 'none');
        $("#input_origin").css('display', 'none');
        $("#request_return_origin_money").val('');
    }

    $("#trade_type").blur();
});

function tradeInAction(md)
{
    // 중복클릭 방지
    if(ccCheck()) return;

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    if( $("#in_name").val()=="" )
    {
        $("#in_name").val($("#cust_name").val());
    }

    // 입력값 정합성 체크
    if(!validCheck())
    {
        return false;
    }
    
    var formData = new FormData($('#trade_out_form')[0]);
    formData.append("action_mode", md);

    //$("#tradeInResult").html(loadingString);
    $.ajax({
        url  : "/erp/tradeinaction",
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

function validCheck()
{
    var tradeMoney = Number($("#trade_money").val().replace(/,/gi,""));
    var requestMoney = Number($("#request_return_origin_money").val().replace(/,/gi,""));

    // 원금상환액이 입금액보다 크지않게 확인
    if( $("#request_return_origin_money").length )
    {
        // 원금우선상환
        if( $("#trade_type").val()=="11" )
        {
            if(requestMoney > tradeMoney)
            {
                alert("원금상환액은 입금액보다 클 수 없습니다.");
                $("#request_return_origin_money").focus();
                return false;
            }
        }
    }

    return true;
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


$('input[name="return_fee_free"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});
$('input[name="sms_flag"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});

</script>

@endsection
