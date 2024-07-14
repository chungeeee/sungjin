@extends('layouts.masterPop')
@section('content')




<form class="form-horizontal" name="loan_cancel_form" id="loan_cancel_form">
<input type="hidden" name="loan_cancel_no" value="{{ $loan_cancel_no }}">

<div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title">철회 등록</h2>
    </div>

    <div class="card-body mr-3 p-3">



        <div class="form-group row">
            <label for="search_string" class="col-sm-2 col-form-label">고객검색</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" id="search_string" placeholder="차입자번호,계약번호" value="" />
            </div>
            <div class="col-sm-6 text-left">
                <button type="button" class="btn btn-sm btn-info mr-3" id="btn_search_string" onclick="searchLoanInfo();">검색</button>
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
            <input type="text" class="form-control form-control-sm" id="cust_info_no" name="cust_info_no" placeholder="고객번호" value="{{ $rslt['cust_info_no'] ?? '' }}" readonly>
        </div>
        <label for="cust_name" class="col-sm-2 col-form-label">고객명</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="cust_name" name="cust_name" placeholder="이름" value="{{ $rslt['cust_name'] ?? '' }}" readonly>
        </div>
        </div>
        
        <div class="form-group row">
        <label for="loan_info_no" class="col-sm-2 col-form-label">계약번호</label>
        <div class="input-group col-sm-4 pb-1">
            <input type="text" class="form-control" name="loan_info_no" id="loan_info_no" placeholder="계약번호" value="{{ $rslt['loan_info_no'] ?? '' }}"readOnly>
            <span class="input-group-btn input-group-append">
            <button class="btn btn-sm btn-primary" type="button" onclick="openLoanInfo()">계약정보창</button>
            </span>
        </div>

        <label for="loan_date" class="col-sm-2 col-form-label">대출일</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="loan_date" name="loan_date" placeholder="대출일" value="{{ $rslt['loan_date'] ?? '' }}" readonly>
        </div>
        </div>


        @if( $action_mode=="UPDATE" )

        <div class="form-group row">
        <label for="cancel_reason_cd" class="col-sm-2 col-form-label">계약상태</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" value="{{ Func::nvl(Vars::$arrayContractSta[$rslt['status']], $rslt['status']) }}" readonly>
        </div>
        <label for="cancel_reason_cd" class="col-sm-2 col-form-label">완제일자</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" value="{{ $rslt['fullpay_date'] ?? '' }}" readonly>
        </div>
        </div>

        @endif

        
        <div class="form-group row">
        <label for="cancel_reason_cd" class="col-sm-2 col-form-label">철회사유</label>
        <div class="col-sm-4">
            <select class="form-control form-control-sm" name="cancel_reason_cd" id="cancel_reason_cd">
            <? Func::printOption($array_config['cancel_reason_cd'], $rslt['cancel_reason_cd']); ?>
            </select>
        </div>
        <label for="trade_money" class="col-sm-2 col-form-label">철회비용</label>
        <div class="col-sm-4">
            <div class="input-group">
                <input type="text" class="form-control form-control-sm text-right moneyformat" id="cancel_cost_money" name="cancel_cost_money" placeholder="원단위 입력" value="{{ number_format($rslt['cancel_cost_money']) }}">
                <div class="input-group-append">
                    <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fas fa-won-sign"></i></div>
                </div>
            </div>
        </div>
        </div>
        <div class="form-group row">
            <label for="memo" class="offset-6  col-sm-2 col-form-label">메모</label>
            <div class="col-sm-4">
            <textarea class="form-control form-control-sm" name="memo">{{ $rslt['memo'] ?? '' }}</textarea>
            </div>
        </div>



        @if( $action_mode!="INSERT" )
        <div class="form-group row mt-2">
            <label for="search_string" class="col-sm-2 col-form-label">요청 등록일시</label>
            <div class="col-sm-4 col-form-label">
                {{ $rslt['app_id'] }}
                ( {{ Func::dateFormat($rslt['app_time']) }} )
            </div>
        </div>
        <input type="hidden" name="app_id" value="{{ $rslt['app_id'] ?? '' }}">
        @endif




        @if( $action_mode=="UPDATE" )


        @elseif( $action_mode=="NONE" )

        @if( $rslt['status']=="Y" )
        <div class="form-group row mt-2">
            <label for="search_string" class="col-sm-2 col-form-label">결재 일시</label>
            <div class="col-sm-4 col-form-label">
                {{ $rslt['confirm_id'] }}
                ( {{ Func::dateFormat($rslt['confirm_time']) }} )
            </div>
        </div>
        @elseif( $rslt['status']=="N" )
        <div class="form-group row mt-2">
            <label for="search_string" class="col-sm-2 col-form-label">취소 일시</label>
            <div class="col-sm-4 col-form-label">
                {{ $rslt['save_id'] }}
                ( {{ Func::dateFormat($rslt['save_time']) }} )
            </div>
        </div>
        @endif


        @endif





    </div>
    <div class="card-footer">
        @if( $action_mode=="UPDATE" )
        @if( Func::funcCheckPermit("C050") && Func::funcCheckPermit("A223","A") )
        <button type="button" class="btn btn-sm btn-info   float-right mr-3" id="btn_confirm" onclick="loanCancelAction('CONFIRM');">결재</button>
        @endif
        <button type="button" class="btn btn-sm btn-danger float-right mr-2" id="btn_delete"  onclick="loanCancelAction('DELETE');" >취소</button>
        @elseif( $action_mode=="INSERT" )
        <button type="button" class="btn btn-sm btn-info float-right mr-3" id="cate_btn" onclick="loanCancelAction('INSERT');">철회요청 등록</button>
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
    $("#collapseSearchResult").html(loadingStringtxt);
    $('.collapse').collapse('show');
    $.post("/erp/tradeinsearch", {search_string:search_string}, function(data) {
        $("#collapseSearchResult").html(data);
    });
}



// 고객검색에서 선택한 고객의 계약정보 호출
function selectLoanInfo(n)
{
    var cin = $("#cust_info_no_"+n).html();
    var lin = $("#loan_info_no_"+n).html();
    var cnm = $("#cust_name_"+n).html();
    var ldt = $("#loan_date_"+n).html();

    $("#cust_info_no").val(cin);
    $("#loan_info_no").val(lin);
    $("#cust_name").val(cnm);
    $("#loan_date").val(ldt);

    $('.collapse').collapse('hide');

}






function loanCancelAction(md)
{
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    if( md=="INSERT" && !confirm("철회요청을 등록하시겠습니까?") )
    {
        return false;
    }
    if( md=="DELETE" && !confirm("철회요청을 취소하시겠습니까?") )
    {
        return false;
    }
    if( md=="CONFIRM" && !confirm("철회요청을 결재하시겠습니까?") )
    {
        return false;
    }

    var formData = new FormData($('#loan_cancel_form')[0]);
    formData.append("action_mode", md);


    if( md=="CONFIRM" )
    {
        $("#btn_confirm").prop("disabled",true);
        $("#btn_delete").prop("disabled",true);
    }

    $.ajax({
        url  : "/erp/cancelformaction",
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
                $("#btn_delete").prop("disabled",false);
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
            $("#btn_confirm").prop("disabled",false);
            $("#btn_delete").prop("disabled",false);
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
        searchLoanInfo();
      };
    });
}
enterClear();
</script>

@endsection
