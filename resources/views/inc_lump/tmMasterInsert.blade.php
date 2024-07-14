

<div id="LUMP_FORM_tmMasterInsert" class="lump-forms" style="display:none">
    <div class="card card-outline primary">
        <div class="card-header flex-column status-border-right-none">
            <h5 class="card-title text-bold" style='color:black'>
                TM분배
            </h5>
        </div>
        <form name="tmMasterInsertBatchForm" id="tmMasterInsertBatchForm" method="post" action="">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 text-black">
                        {{-- <span class="form-control-xs col-3" style="float:left">분배 월 : </span> 
                        <div class="input-group date datetimepicker-wol col-md-5 p-0" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm datetimepicker-wol " data-target="#assign_wol" name="assign_wol" id="assign_wol" dateonly="true" size="6" required>
                            <div class="input-group-append" data-target="#assign_wol" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        <span class="form-control-xs mt-2 col-3" style="float:left">TM분배구분 : </span> 
                        <select class="form-control form-control-sm selectpicker mr-2 col-md-6 mt-2" name="tm_loan_div" id="tm_loan_div" title="구분 선택">
                            {{ Func::printOption($arrayLoanDiv, '') }}
                        </select>   --}}
                        <div class="row">
                            <span class="form-control-xs mt-2 col-3" style="float:left">지 &nbsp; &nbsp; &nbsp; &nbsp; &nbsp;점 : </span> 
                            <select class="form-control form-control-sm selectpicker mr-2 col-md-6 mt-2" onchange="change_branch(this);" name="branch_code" id="branch_code" title="지점 선택">
                                {{ Func::printOption($arrayBranchUsers, '') }}
                            </select>  
                        </div>
                        <div class="row">
                            <span class="form-control-xs mt-2 col-3" style="float:left">상 &nbsp; 담 &nbsp;원 : </span> 
                            <select class="form-control form-control-sm selectpicker mr-2 col-md-6 mt-2" name="tm_manager[]" id="tm_manager" multiple data-selected-text-format="count > 30" data-live-search="true" title="상담사 선택">
                            </select>  
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.card-footer -->
            <div class="card-footer " id="input_footer">
                <button type="button" class="btn btn-sm btn-default float-right ml-1" onclick="assignAction('chk');">분배</button>
                <button type="button" class="btn btn-sm btn-default float-right" onclick="assignAction('all');">검색결과전체분배</button>
            </div>
        </form>
    </div>
</div>
<style>
	/* 로딩*/
	#loading {
		height: 100%;
		left: 0px;
		position: fixed;
		_position:absolute; 

		top: 0px;

		width: 100%;
		filter:alpha(opacity=50);
		-moz-opacity:0.5;
		opacity : 0.5;
	}
	.loading {
		background-color: white;
		z-index: 199;
	}
	#loading_img{
		position:absolute; 
		top:50%;
		left:55%;
		height:100px;
		margin-top:-75px;	
		margin-left:-75px;	
		z-index: 200;
	}
</style>


@section('javascript')
@parent
<script>
var loading = $('<div id="loading" class="loading"></div><img id="loading_img" alt="loading" src="/img/Spinner.gif" />').appendTo(document.body).hide();

// TM 분배
function assignAction(div)
{
    var chk_cnt = 0;
    var listdata = '';
    var lump_url = 'tmmasterinsert';
    var lump_name = 'tmAssign';

    if($('#tabsSelect'+lump_name).val() != 'Y')
    {
        alert("분배대상 탭에서만 분배가 가능합니다.");
        return;
    }
    
    if($('#ASSIGN_ID').val() != 'N')
    {
        alert("[분배상태]를 미분배로 검색해서 진행해주세요.");
        return;
    }

    // 체크박스
    if(div == 'chk')
    {
        if(!isCheckboxChecked('listChk[]'))
        {
            alert('선택한 고객이 없습니다. 고객을 선택 후 이용해 주세요.');
            return;
        }

        cust_cnt = $("input:checkbox[name='listChk[]']:checked").length;
    }
    // 전체등록
    else if(div == 'all')
    {
        if($("#searchCnt"+lump_name).val() == 0)
        {
            alert('검색된 고객이 없습니다. 검색을 다시 진행해주세요.');
            return;
        }
        else
        {
            listdata = $('#form_'+lump_name).serialize() + '&';
            cust_cnt =  $("#searchCnt"+lump_name).val();
        }
    }

    {{-- if(isEmpty($('#assign_wol').val()))
    {
        alert('분배 월을 선택해주세요.');
        $("#wol").focus();
        return;
    } --}}
    {{-- if(isEmpty($("#tm_loan_div").val()))
    {
        alert("분배구분을 선택해주세요.");
        $("#tm_loan_div").focus();
        return;
    } --}}
    if(isEmpty($('#tm_manager').val()))
    { 
        alert('상담사를 선택해주세요.');
        $("#tm_manager").focus();
        return;
    }

    if(ccCheck()) return; 

    var manager_name = $('[data-id="tm_manager"]').attr('title');
    
    var confirmMsg = '';
    if(div=='all')
    {
        confirmMsg = "검색결과 전체를 정말로 등록하시겠습니까?\n상 담 원  : "+manager_name;
    }
    else
    {
        confirmMsg = "정말로 등록하시겠습니까?\n상 담 원  : "+manager_name+"\n고 객 수  : "+cust_cnt+"명";
    }

    if( !confirm(confirmMsg) )
    {
        globalCheck = false;
        return false;
    }

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata  = $('#tmMasterInsertBatchForm').serialize();
    var targetSql = $("#target_sql"+lump_name).val();
    var listChk   = getArrayCheckbox('listChk[]');
    
    postdata = postdata + '&' + listChk + '&div='+ div + '&targetSql=' +targetSql;
    
    $.ajax({
        url  : "/lump/"+lump_url,
        type : "post",
        data : postdata,
        success : function(result) {
            alert(result.msg);
            loading.hide();
            globalCheck = false;
        },
        beforeSend: function () {
            loading.show();
        },
        complete: function () {
            //loading.hide();
        },
        error : function(xhr) {
            globalCheck = false;
            alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });

}
 // 부서 선택 시 상담원 출력
function change_branch(div)
{   
    var branch_div = div.options[div.selectedIndex].value;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url  : "/lump/lumptmbranch",
        type : "post",
        data : { branch_div:branch_div },
            success : function(result)
            {
                $('#tm_manager').html(result);
                $('#tm_manager').selectpicker('refresh');       
            },
            error : function(xhr)
            {
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
        
} 

$('#branch_code').selectpicker({
        dropupAuto: false
    });
</script>
@endsection