@extends('layouts.masterPop')
@section('content')




<form class="form-horizontal" name="rebuy_form" id="rebuy_form">
<input type="hidden" name="loan_sell_seq" value="{{ $loan_sell_seq }}">

<div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title">환매결재</h2>
    </div>

    <div class="card-body mr-3 p-3">
        <div class="form-group row">
            <label for="search_string" class="col-sm-2 col-form-label">고객검색</label>
            <div class="col-sm-4 ">
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

        <label for="sell_date" class="col-sm-2 col-form-label">매각일</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" placeholder="매각일" value="{{ isset($rslt['sell_date'])?Func::dateFormat($rslt['sell_date']):'' }}" readonly>
        </div>
        </div>

        <div class="form-group row">
            <label for="rebuy_reason_cd" class="col-sm-2 col-form-label">환매사유</label>
            <div class="col-sm-4">
                <select class="form-control form-control-sm" name="rebuy_reason_cd" id="rebuy_reason_cd">
                {{ Func::printOption($array_config['rebuy_reason_cd'], $rslt['rebuy_reason_cd']) }}
                </select>
            </div>

            <label for="sell_date" class="col-sm-2 col-form-label">메모</label>
            <div class="col-sm-4">
                <textarea class="form-control form-control-sm" name="rebuy_memo">{{ $rslt['rebuy_memo'] ?? '' }}</textarea>
            </div>

        </div>
        <div class="form-group row">

        </div>


        @if( $action_mode!="INSERT" )
        <div class="form-group row mt-2">
            <label for="search_string" class="col-sm-2 col-form-label">요청 등록일시</label>
            <div class="col-sm-4 col-form-label">
                {{ Func::getArrayName($array_user_id,$rslt['rebuy_app_id']) }}
                ( {{ Func::dateFormat($rslt['rebuy_app_time']) }} )
            </div>
        </div>
        <input type="hidden" name="rebuy_app_id" value="{{ $rslt['rebuy_app_id'] ?? '' }}" >
        @endif


        @if( $action_mode=="UPDATE" )


        @elseif( $action_mode=="NONE" )

        @if( $rslt['rebuy_status']=="Y" )
        <div class="form-group row mt-2">
            <label for="search_string" class="col-sm-2 col-form-label">결재 일시</label>
            <div class="col-sm-4 col-form-label">
                {{  Func::getArrayName($array_user_id,$rslt['rebuy_confirm_id']) }}
                ( {{ Func::dateFormat($rslt['rebuy_confirm_time']) }} )
            </div>
        </div>
        @elseif( $rslt['rebuy_status']=="N" )
        <div class="form-group row mt-2">
            <label for="search_string" class="col-sm-2 col-form-label">취소 일시</label>
            <div class="col-sm-4 col-form-label">
                {{  Func::getArrayName($array_user_id,$rslt['save_id']) }}
                ( {{ Func::dateFormat($rslt['save_time']) }} )
            </div>
        </div>
        @endif


        @endif





    </div>
    <div class="card-footer">
        @if( $action_mode=="UPDATE" )
        @if( Func::funcCheckPermit("C090") )
        <button type="button" class="btn btn-sm btn-info   float-right mr-1" id="btn_confirm" onclick="rebuyAction('CONFIRM');">결재</button>
        @endif
        <button type="button" class="btn btn-sm btn-danger float-right mr-1" id="btn_delete"  onclick="rebuyAction('DELETE');" >취소</button>
        <button type="button" class="btn btn-sm btn-info float-right mr-1" id="btn_update"  onclick="rebuyAction('UPDATE');" >수정</button>
        @elseif( $action_mode=="INSERT" )
        <button type="button" class="btn btn-sm btn-info float-right mr-3" id="cate_btn" onclick="rebuyAction('INSERT');">환매요청 등록</button>
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
    var lst = $("#loan_status_"+n).val();

    if(lst != "M")
    {
        alert("매각상태의 계약을 선택해주세요");
        return false;
    }

    $("#cust_info_no").val(cin);
    $("#loan_info_no").val(lin);
    $("#cust_name").val(cnm);

    $('.collapse').collapse('hide');
}






function rebuyAction(md)
{
    if(!$("#loan_info_no").val())
    {
        alert("선택된 계약이 없습니다.");
        return false;
    }
    if( md=="INSERT" && !confirm("환매요청을 등록하시겠습니까?") )
    {
        return false;
    }
    if( md=="DELETE" && !confirm("환매요청을 취소하시겠습니까?") )
    {
        return false;
    }
    if( md=="CONFIRM" && !confirm("환매요청을 결재하시겠습니까?") )
    {
        return false;
    }

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var formData = new FormData($('#rebuy_form')[0]);
    formData.append("action_mode", md);


    if( md=="CONFIRM" )
    {
        $("#btn_confirm").prop("disabled",true);
        $("#btn_delete").prop("disabled",true);
    }
    

    $.ajax({
        url  : "/erp/rebuyformaction",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            console.log(result);
            if( result.rs=="Y" )
            {
                alert(result.rs_msg);  
                opener.document.location.reload();
                self.close();
            }
            else
            {
                alert(result.rs_msg);  
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
