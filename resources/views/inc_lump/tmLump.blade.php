

<div id="LUMP_FORM_tmLump" class="lump-forms" style="display:none">
    <div class="card card-outline primary">
        <div class="card-header flex-column status-border-right-none">
            <h5 class="card-title text-bold" style='color:black'>
                일괄처리
            </h5>
        </div>
        <form name="tmLumpBatchForm" id="tmLumpBatchForm" method="post" action="">
            @csrf
            <div class="card-body">
                <div class="row">
                    <div class="col-md-12 text-black">
                        {{-- <span class="form-control-xs col-3" style="float:left">분배 월 : </span> 
                        <div class="input-group date datetimepicker col-md-5 p-0" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm datetimepicker-input " data-target="#assign_wol" name="assign_wol" id="assign_wol" dateonly="true" size="6" required>
                            <div class="input-group-append" data-target="#assign_wol" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div> --}}
                        <div class="row">
                            <span class="form-control-xs col-4" style="float:left">일괄처리구분 : </span> 
                            <select class="form-control form-control-sm selectpicker mr-1 col-md-6 mt-1" name="lump_div" id="lump_div" title="구분 선택">
                                {{ Func::printOption($arrayTmLumpDiv, '') }}
                            </select>  
                        </div>
                        <div class="row">
                            <span class="form-control-xs col-4" style="float:left">처리선택 : </span> 
                            <select class="form-control form-control-sm selectpicker mr-1 col-md-6 mt-1" name="lump_value" id="lump_value" title="선택">
                                {{ Func::printOption($arrayYN[$result['Tabs']['tabsSelect']], '') }}
                            </select> 
                        </div>
                    </div>
                </div>
            </div>
            <!-- /.card-footer -->
            <div class="card-footer " id="input_footer">
                <button type="button" class="btn btn-sm btn-info float-right ml-1" onclick="tmLumpAction('chk');">처리</button>
                <button type="button" class="btn btn-sm btn-info float-right" onclick="tmLumpAction('all');">결과전체처리</button>
            </div>
        </form>
    </div>
</div>
@section('javascript')
@parent
<script>
// TM 등록
function tmLumpAction(div)
{
    var chk_cnt = 0;
    var listdata = '';
    var lump_url = 'tmlump';
    var lump_div = '';
    var lump_name = 'tmAssign';

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

    if(isEmpty($("#lump_div").val()))
    {
        alert("일괄처리구분을 선택해주세요!");
        $("#lump_div").focus();
        return;
    }
    else if(isEmpty($("#lump_value").val()))
    {
        alert("처리할 값을 선택해주세요!");
        $("#lump_value").focus();
        return;
    }
    if(ccCheck()) return; 

    if( !confirm("정말로 처리하시겠습니까?") )
    {
        globalCheck = false;
        return false;
    }

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata  = $('#tmLumpBatchForm').serialize();
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

                $('#searchWol').val('wol');                

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