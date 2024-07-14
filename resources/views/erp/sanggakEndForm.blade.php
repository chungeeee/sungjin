@extends('layouts.masterPop')
@section('content')




<form class="form-horizontal" name="sanggak_end_form" id="sanggak_end_form">
<input type="hidden" name="no" value="{{ $no }}">

<div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title">상각완제결재</h2>
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

        <label for="sanggak_date" class="col-sm-2 col-form-label">상각일</label>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" placeholder="상각일" value="{{ isset($rslt['sanggak_date'])?Func::dateFormat($rslt['sanggak_date']):'' }}" readonly>
        </div>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" placeholder="상각일" value="{{ $rslt['sg_reason_nm'] ?? '' }}" readonly>
        </div>
        </div>

        <div class="form-group row">
            <label for="sg_end_reason_cd" class="col-sm-2 col-form-label">상각완제사유</label>
            <div class="col-sm-4">
                <select class="form-control form-control-sm" name="sg_end_reason_cd" id="sg_end_reason_cd">
                {{ Func::printOption($array_config['sg_end_reason_cd'], $rslt['sg_end_reason_cd'] ?? '' ) }}
                </select>
            </div>
            <label for="memo" class="col-sm-2 col-form-label">메모</label>
            <div class="col-sm-4">
                <textarea class="form-control form-control-sm" name="memo" >{{ $rslt['memo'] ?? '' }}</textarea>
            </div>

        </div>
        <div class="form-group row">

        </div>


        @if( $action_mode!="INSERT" )
        <div class="form-group row mt-2">
            <label for="search_string" class="col-sm-2 col-form-label">요청 등록일시</label>
            <div class="col-sm-4 col-form-label">
                {{ Func::getArrayName($array_user_id,$rslt['app_id']) }}
                ( {{ Func::dateFormat($rslt['app_time']) }} )
            </div>
        </div>
        <input type="hidden" name="app_id" value="{{ $rslt['app_id'] ?? '' }}" >
        @endif




        @if( $action_mode=="UPDATE" )


        @elseif( $action_mode=="NONE" )

        @if( $rslt['status']=="Y" )
        <div class="form-group row mt-2">
            <label for="search_string" class="col-sm-2 col-form-label">결재 일시</label>
            <div class="col-sm-4 col-form-label">
                {{  Func::getArrayName($array_user_id,$rslt['confirm_id']) }}
                ( {{ Func::dateFormat($rslt['confirm_time']) }} )
            </div>
        </div>
        @elseif( $rslt['status']=="N" )
        <div class="form-group row mt-2">
            <label for="search_string" class="col-sm-2 col-form-label">취소 일시</label>
            <div class="col-sm-4 col-form-label">
                {{  Func::getArrayName($array_user_id,$rslt['cancel_id']) }}
                ( {{ Func::dateFormat($rslt['cancel_time']) }} )
            </div>
        </div>
        @endif


        @endif





    </div>
    <div class="card-footer">
        @if( $action_mode=="UPDATE" )
        @if( Func::funcCheckPermit("C070") )
        <button type="button" class="btn btn-sm btn-info   float-right mr-1" id="btn_confirm" onclick="sanggakEndAction('CONFIRM');">결재</button>
        @endif
        <button type="button" class="btn btn-sm btn-danger float-right mr-1" id="btn_delete"  onclick="sanggakEndAction('DELETE');" >취소</button>
        <button type="button" class="btn btn-sm btn-info float-right mr-1" id="btn_update"  onclick="sanggakEndAction('UPDATE');" >수정</button>
        @elseif( $action_mode=="INSERT" )
        <button type="button" class="btn btn-sm btn-info float-right mr-3" id="cate_btn" onclick="sanggakEndAction('INSERT');">상각완제요청 등록</button>
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

    if(lst != "S")
    {
        alert("상각상태의 계약을 선택해주세요");
        return false;
    }

    location.href = "/erp/sanggakendform?loan_info_no=" + lin;
    $('.collapse').collapse('hide');
}






function sanggakEndAction(md)
{
    if(!$("#loan_info_no").val())
    {
        alert("선택된 계약이 없습니다.");
        return false;
    }
    if( md=="INSERT" && !confirm("상각완제요청을 등록하시겠습니까?") )
    {
        return false;
    }
    if( md=="DELETE" && !confirm("상각완제요청을 취소하시겠습니까?") )
    {
        return false;
    }
    if( md=="CONFIRM" && !confirm("상각완제요청을 결재하시겠습니까?") )
    {
        return false;
    }

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var formData = new FormData($('#sanggak_end_form')[0]);
    formData.append("action_mode", md);


    if( md=="CONFIRM" )
    {
        $("#btn_confirm").prop("disabled",true);
        $("#btn_delete").prop("disabled",true);
    }
    

    $.ajax({
        url  : "/erp/sanggakendformaction",
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
