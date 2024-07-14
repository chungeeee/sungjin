<div id="LUMP_FORM_nsicLump" class="lump-forms" style="display:none">
    <div class="card card-outline primary">
        <div class="card-header flex-column status-border-right-none">
            <h5 class="card-title text-bold" style='color:black'>
                일괄처리
            </h5>
        </div>
        <form name="nsicLumpBatchForm" id="nsicLumpBatchForm" method="post" action="">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 text-black">
                        <div class="row">
                            <span class="form-control-xs col-4" style="float:left">처리선택 : </span> 
                            <select class="form-control form-control-sm mr-1 col-md-6 mt-1" name="lump_status" id="lump_status" title="선택" onchange="setSelectDiv(this.value)">
                                {{ Func::printOption($arrayNsicStatusTran[$result['Tabs']['tabsSelect']], '') }}
                            </select> 
                        </div>
                        <div class="row" style="display:none;" id="div_cancel_reason">
                            <span class="form-control-xs col-4" style="float:left">취소사유 : </span> 
                            <select class="form-control form-control-sm mr-1 col-md-6 mt-1" name="cont_cancel_reason" id="cont_cancel_reason">
                                <option value=''>취소사유선택</option>
                                {{ Func::printOption($configArr['cont_cancel_reason']) }}
                            </select> 
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.card-footer -->
            <div class="card-footer " id="input_footer">
                <button type="button" class="btn btn-sm btn-info float-right ml-1" onclick="nsicLumpAction('chk');">처리</button>
                {{-- <button type="button" class="btn btn-sm btn-info float-right" onclick="nsicLumpAction('all');">결과전체처리</button> --}}
            </div>
        </form>
    </div>
</div>
@section('javascript')
@parent
<script>
function setSelectBox(nowTabs)
{
    var tabsSelect = nowTabs;
    var arrayNsicStatusTran = @json($arrayNsicStatusTran);

    $("#lump_status").empty();
    $("#lump_status").append("<option value=''>선택</option>");

    for(var i in arrayNsicStatusTran[tabsSelect]){
        $("#lump_status").append('<option value="'+i+'">'+arrayNsicStatusTran[tabsSelect][i]+'</option>');
    }

    //$("#lump_status").selectpicker('refresh');
}
function setSelectDiv(val)
{
    if(val == 'N')
    {
        $("#div_cancel_reason").show();
    }
    else
    {
        $("#div_cancel_reason").hide();
    }

}
// 일괄처리
function nsicLumpAction(div)
{
    var chk_cnt = 0;
    var listdata = '';
    var lump_url = 'nsiclump';
    var lump_div = '';
    var lump_name = 'nsic';

    // 체크박스
    if(div == 'chk')
    {
        if(!isCheckboxChecked('listChk[]'))
        {
            alert('선택한 고객이 없습니다. 고객을 선택 후 이용해 주세요.');
            return;
        }

        listdata = 'tabsSelect=' + $("#tabsSelect"+lump_name).val() + '&';
        $cust_cnt = $("input:checkbox[name='listChk[]']:checked").length;
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
            $cust_cnt =  $("#searchCnt"+lump_name).val();
        }
    }

    if(isEmpty($("#lump_status").val()))
    {
        alert("처리할 값을 선택해주세요!");
        $("#lump_status").focus();
        return;
    }

    if($("#lump_status").val() == 'N' && isEmpty($("#cont_cancel_reason").val()))
    {
        alert("취소사유를 선택해주세요!");
        $("#cont_cancel_reason").focus();
        return;
    }

    if(ccCheck()) return; 

    if( !confirm("정말로 처리하시겠습니까?") )
    {
        globalCheck = false;
        return false;
    }

    var postdata  = $('#nsicLumpBatchForm').serialize();
    postdata = listdata + postdata;
    console.log(postdata);
    

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata  = $('#nsicLumpBatchForm').serialize();
    var listChk   = getArrayCheckbox('listChk[]');
    var targetSql = $("#target_sql"+lump_name).val();
    postdata = listdata + postdata + '&' + listChk + '&div=' +div + '&targetSql=' +targetSql;

    $.ajax({
        url  : "/lump/"+lump_url,
        type : "post",
        data : postdata,
        success : function(result) {
            alert(result.msg);
            if(result.code == 'Y')
            {
                $('.check-all').iCheck('uncheck');
                $('.list-check').iCheck('uncheck');
                $('.selectpicker').selectpicker('val', '');

                listRefresh();
            }

            globalCheck = false;
        },
        error : function(xhr) {
            globalCheck = false;
            alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
}
  
</script>
@endsection