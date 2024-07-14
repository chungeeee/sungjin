@extends('layouts.masterPop')
@section('content')

<? $star = "<font class='text-red'>*</font>"; ?>

<script>
    window.onload = function()
    {
        
    }
</script>





<form class="form-horizontal" name="advance_deposit_form" id="advance_deposit_form">
<input type="hidden" name="loan_info_no" id="loan_info_no">
<div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title">사택 등록 </h2>
    </div>
    
    <div class="card-body mr-3 p-3">


    <div class="form-group row">
        <label for="search_string" class="col-sm-2 col-form-label">검색</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="search_string" placeholder="차입자번호, 이름, 생년월일.." value="" />
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
            <label for="cust_info_no" class="col-sm-2 col-form-label">{!! $star !!}차입자번호</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" id="cust_info_no" name="cust_info_no" readonly placeholder="" value="@if(isset($result->cust_info_no)) {{ $result->cust_info_no }} @endif"/>
            </div>
             <label for="div_loan_date" class="col-sm-2 col-form-label">이름</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" id="cust_name" readonly placeholder="" data-target="#div_loan_date" value="@if(isset($result->name)) {{ $result->name }} @endif"/>
            </div>
        </div>
        <div class="form-group row">
            <label for="div_loan_date" class="col-sm-2 col-form-label">생년월일</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" id="cust_ssn" readonly placeholder="생년월일" data-target="#div_loan_date" value="@if(isset($result->ssn) && $result->ssn != '') {{ $result->ssn }} @endif"/>
            </div>
        </div>

        <div class="form-group row">
            <label for="return_process_date" class="col-sm-2 col-form-label">{!! $star !!}상환처리일</label>
            <div class="col-sm-4">        
                <div class="input-group date datetimepicker" id="div_return_process_date" data-target-input="nearest">
                    <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#div_return_process_date" name="return_process_date" id="return_process_date" DateOnly="true"  value=''  size="6">
                    <div class="input-group-append" data-target="#div_return_process_date" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
    
            </div>
        
            <label for="return_process_money" class="col-sm-2 col-form-label">{!! $star !!}상환처리금액</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm text-right moneyformat" id="return_process_money" name="return_process_money" placeholder="원단위 입력" value="0">
                    <div class="input-group-append">
                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fas fa-won-sign"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label for="deposit_date" class="col-sm-2 col-form-label">{!! $star !!}예치일</label>
            <div class="col-sm-4">        
                <div class="input-group date datetimepicker" id="div_deposit_date" data-target-input="nearest">
                    <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#div_deposit_date" name="deposit_date" id="deposit_date" DateOnly="true"  value=''  size="6">
                    <div class="input-group-append" data-target="#div_deposit_date" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
    
            </div>
        
            <label for="deposit_money" class="col-sm-2 col-form-label">{!! $star !!}예치금액</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm text-right moneyformat" id="deposit_money" name="deposit_money" placeholder="원단위 입력" value="0">
                    <div class="input-group-append">
                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fas fa-won-sign"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label for="memo" class="col-sm-2 col-form-label">메모</label>
            <div class="col-sm-10">
                <textarea class="form-control form-control-xs" name="memo" id="memo" rows="4" style="resize:none;"></textarea>
            </div>
        </div>

    </div>
    <div class="card-footer">
        <button type="button" class="btn btn-sm btn-info float-right mr-3" id="cate_btn" onclick="advanceDepositAction('');">선입금등록</button>
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
    $.post("/account/advancedepositformsearch", {search_string:search_string}, function(data) {
        $("#collapseSearchResult").html(data);
    });
}

function selectLoanInfo(n)
{

    var cin = $("#cust_info_no_"+n).html();
    var lin = $("#loan_info_no_"+n).html();
    var cnm = $("#cust_name_"+n).html();
    var csn = $("#cust_ssn_"+n).html();
    var bcn = $("#bank_info_"+n).html();
    
    // 화면에 표시
    $("#cust_info_no").val(cin);
    $("#loan_info_no").val(lin);
    $("#cust_name").val(cnm);
    $("#cust_ssn").val(csn);
    
    $('#array_bank_cd').find("option").remove();
    $('#array_bank_cd').append('<option value="">선택</option>');

    $.each(JSON.parse(bcn), function (key, val) {
        $('#array_bank_cd').append('<option value="' + key + '">' + val + '</option>');
    });

    $('.collapse').collapse('hide');
}

function setDelayRatio(ratio)
{
    var maxRatio = {{ Vars::$curMaxRate }};
    ratio = ratio * 1;
    
    if(ratio>maxRatio)
    {
        alert('법정최고이율을 초과 했습니다. 다시 정확히 입력해 주세요.');
        $('#loan_ratio').focus();
        return false;
    }
    else 
    {
        var delayRatio = ratio+3;
        if(delayRatio>maxRatio)
        {
            delayRatio = maxRatio;
        }
    }

    $('#loan_delay_rate').val(delayRatio);
}


function advanceDepositAction(md)
{
    // 유효성 체크
    if(!$('#cust_info_no').val())
    {
        alert('회원을 검색하여 선택 후 진행해 주세요.');
        $('#search_string').focus();
        return false;
    }

    if(!$('#return_process_date').val())
    {
        alert('상환처리일을 선택해 주세요.');
        $('#return_process_date').focus();
        return false;
    }

    if(!$('#return_process_money').val())
    {
        alert('상환처리금액 입력해 주세요.');
        $('#return_process_money').focus();
        return false;
    }

    if(!$('#deposit_date').val())
    {
        alert('예치일을 선택해 주세요.');
        $('#deposit_date').focus();
        return false;
    }

    if(!$('#deposit_money').val())
    {
        alert('예치금액을 입력해 주세요.');
        $('#deposit_money').focus();
        return false;
    }


    // 중복클릭 방지
    if(ccCheck()) return;
    
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var formData = new FormData($('#advance_deposit_form')[0]);
    formData.append("action_mode", md);

    $.ajax({
        url  : "/account/advancedepositaction",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result=="Y" )
            {
                alert("선입금등록이 완료됐습니다.");
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
            globalCheck = false;
        }
    });
}




// 월상환금 계산기 연동
function getMonthMoney()
{
    var url = "/ups/calculator?is_ups=Y";
    url += '&return_method_cd=' + $('#return_method_cd').val();  
    url += '&loan_term=' + $('#loan_term').val();
    url += '&loan_rate=' + $('#loan_rate').val();
    url += '&loan_delay_rate=' + $('#loan_delay_rate').val();
    url += '&contract_day=' + $('#contract_day').val();
    url += '&monthly_return_money=' + $('#monthly_return_money').val();
    // url += '&loan_app_no=' + '{{$no ?? ''}}';
    url += '&balance=' + $('#loan_money').val();
        
    window.open(url,'calculator','left=0,top=0,width=700,height=800,scrollbars=yes');
}

// 월상환금 받아서 입력
function setMonthMoney(money)
{
    $('#monthly_return_money').val(money);
    alert('월상환액이 ' + money + '원으로 적용되었습니다.');
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
