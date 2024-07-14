@extends('layouts.masterPop')
@section('content')




<form class="form-horizontal" name="search_form" id="search_form" onSubmit="searchBond(); return false;">

<div class="card card-lightblue">

    <div class="card-header-no-round">
    <h2 class="card-title">채권번호 검색</h2>
    </div>

    <div class="card-body pr-1 pl-1">


        <div class="input-group row">
            <label for="search_bond" class="col-sm-1 col-form-label">검색</label>
            <div class="col-sm-6 p-0">

                <input type="text" class="form-control form-control-sm col-sm-12" id="search_bond" placeholder="채권번호" value="{{ $searchStr }}" />
                <input type="hidden" id="order_colm" name="order_colm" value="">
                <input type="hidden" id="order_type" name="order_type" value="">

            </div>
            <div class="col-sm-1 text-left ml-1">
                <button type="submit" class="btn btn-sm btn-info">검색</button>
            </div>

            <!-- <div class="col-sm-3 text-left ml-1">
                <div class="icheck-primary ml-4 ">
                    <input id="isMyBranch" name="isMyBranch" value='Y' type="checkbox" onClick="myBranch(this.checked)" @if (!isset($_COOKIE['only_my_branch']) || $_COOKIE['only_my_branch'] == 'Y') checked @endif >
                    <label for="isMyBranch">
                    &nbsp; {{ Func::getArrayName($arrayBranch, $myBranch) }} 지점만
                    </label>
                </div>
            </div> -->
        </div>

        <div class="col-sm-12 collapse" id='loading'>
            <script>document.write(loadingString);</script>
        </div>
        <div class="col-sm-12 collapse" id='viewSearch'>

            
        </div>
    </div>
    
</div>

</form>

@endsection

@section('javascript')



<script src="/plugins/moment/moment.min.js"></script>
<script src="/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.js"></script>
<script>

function setOd(oc, ot)
{
    $("#order_colm").val(oc);
    $("#order_type").val(ot);
    searchCust();
}
function searchBond(loanAppNo)
{
    var oc = $("#order_colm").val();
    var ot = $("#order_type").val();

    var search_bond = $("#search_bond").val();
    if( search_bond=="" )
    {
        alert("검색어를 입력해주세요.");
        $("#search_bond").focus();
        return false;
    }

	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

    $("#loading").show();
    $("#viewSearch").hide();
    
    $.post("/{{ $requestUri }}/searchNo", {searchStr:search_bond, loanAppNo:loanAppNo, order_colm:oc, order_type:ot}, function(data) {

        $('#viewSearch').html(data);

        $("#loading").hide();
        $("#viewSearch").show();

    });

}

function searchLoanAppNo(loanAppNo, custInfoNo)
{
    var oc = $("#order_colm").val();
    var ot = $("#order_type").val();


	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

    $("#loading").show();
    $("#viewSearch").hide();
    
    $.post("/erp/searchaction2", {loanAppNo:loanAppNo, custInfoNo:custInfoNo, order_colm:oc, order_type:ot}, function(data) {

        $('#viewSearch').html(data);

        $("#loading").hide();
        $("#viewSearch").show();     

    });

}

$("#loading").html(loadingString);

@if (!empty($searchStr))
searchBond();
@elseif( !empty($loanAppNo) )
searchLoanAppNo('{{$loanAppNo}}', '');
@elseif( !empty($custInfoNo) )
searchLoanAppNo('', '{{$custInfoNo}}');
@endif


function myBranch(chk)
{
    if(chk==true)
	{
		setCookie('only_my_branch', 'Y', 365);
	}
	else
	{
		setCookie('only_my_branch', 'N', 365);
	}

    branchSearch(chk);
}



// 메뉴자동숨김 체크 이벤트
function chkSidebar(chk)
{
	if(chk==true)
	{
		setCookie('hide_sidebar', 'Y', 365);
		$('#sidebar').addClass("sidebar-collapse");
	}
	else
	{
		setCookie('hide_sidebar', 'N', 365);
		$('#sidebar').removeClass("sidebar-collapse");
		$('#sidebar').removeClass("sidebar-expanded-on-hover");
	}
}	

</script>

@endsection
