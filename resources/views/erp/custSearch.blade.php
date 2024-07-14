@extends('layouts.masterPop')

@section('content')

<form  class="mb-0" name="cust_search_form" id="cust_search_form" method="post" enctype="multipart/form-data" >
    <div class="content-wrapper needs-validation m-0" style="overflow:hidden">
        @csrf
        <input type="hidden" id="action_mode" name="action_mode" value="">
        <div class="">
            <div class="d-flex justify-content-between bd-highlight row">
                <div class="p-2 bd-highlight" >
                    <section class=" pl-3">
                    <h5><i class="fas fa-file-invoice-dollar"></i>고객검색</h5>
                    </section>
                </div>
            </div>
            
        </div>
        
        <div class="card-header p-1">
            <div class="card-tools">
                <div class="col-xs-12 row ml-0 " style="text-align:center;">
                    <label for="search_string" class="col-form-label">고객검색</label>
                    <div class="col-xs-7 pl-1 ml-1">
                        <input type="text" class="form-control form-control-sm" id="search_string" placeholder="차입자번호,계약번호,이름.." value="" />
                    </div>
                    <div class="col-xs-3 text-left pl-1 mr-1">
                        <button type="button" class="btn btn-sm btn-info mr-3" onclick="searchLoanInfo();">검색</button>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12" id="collapseSearchResult">
        </div>

</form>


@endsection

@section('javascript')

<script>


// 로드시 스크롤위치 조정
$(document).ready(function(){
    $(window).scrollTop(0);
});


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

// 고객검색
function searchLoanInfo()
{
    var search_string = $("#search_string").val();
    if( search_string=="" )
    {
        alert("검색어를 입력해주세요.");
        $("#search_string").focus();
        window.close;
    }
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
    var url = "/erp/tradeoutsearch";
    if('{{$div}}' == 'take')
    {
        url = "/ups/loanappsearch";
    }
    $("#collapseSearchResult").html(loadingStringtxt);
    $('.collapse').collapse('show');
    $.post(url, {search_string:search_string}, function(data) {
        $("#collapseSearchResult").html(data);
        $(".table").css('font-size', '0.8rem');
    });

}
function selectLoanInfo(no)
{
    var arr = {"give":{"url":"/ups/loanappgetloaninfo","str":"계약번호","param":"loan_info_no"},"take":{"url":"/ups/getloanapp","str":"신청번호","param":"loan_app_no"}};
    var div = "{{$div}}";
    if(confirm(no+'번 '+arr[div]['str']+'로 고객정보를 가져오시겠습니까? '))
    {
        var url = arr[div]['url'];
        var formdata = arr[div]['param']+"="+no;

        jsonAction(url, 'POST', formdata, function (data) {
            if(data!=null)
            {
                //console.log(data);
                var div = '{{$div}}';

                // 가져온 데이터로 폼 채우기
                var arrayIds = $(opener.document).find('#'+div+'_cust_form').serializeArray(); 
                console.log(arrayIds);
                arrayIds.forEach(function(v) {
                        // 입력되어 있는 정보를 지운다.
                        if(v.name!='_token' && v.name!='action_mode' && v.name!='app_type_cd' && v.name!='intr_no') {
                            $(opener.document).find('#'+v.name).val('');
                        }

                        if(v.name!='_token' && v.name!='memo' && v.name!='app_type_cd') {
                            var str = v.name;
                            if(div == 'take')
                            {
                                str = str.substr(5);
                            }
                            console.log(str);
                            if(eval("data."+str))
                            {                         
                                console.log(v.name);
                                $(opener.document).find('#'+v.name).val(eval("data."+str));
                            }
                            
                        }                    
                }); 
                // 주민번호
                $(opener.document).find('#'+div+'_ssn1').val(data.ssn.substring(0, 6));
                $(opener.document).find('#'+div+'_ssn2').val(data.ssn.substring(6));
                $(opener.document).find('#'+div+'_branch_cd').html(data.manager_code_name);
                $(opener.document).find('#'+div+'_manager_code').val(data.manager_code);

                $(opener.document).find(".comma").number(true);

                window.close();
            }
            else
            {
                alert("해당 정보가 존재하지 않습니다.");
            }
        });

    }
    else 
    {

    }    
}
</script>
@endsection