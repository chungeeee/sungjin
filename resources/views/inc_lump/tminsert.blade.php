

<div id="LUMP_FORM_tminsert" class="lump-forms" style="display:none">
    <div class="card card-outline primary">
        <div class="card-header flex-column status-border-right-none">
            <h5 class="card-title text-bold" style='color:black'>
                TM등록
            </h5>
        </div>
        <form name="tminsertBatchForm" id="tminsertBatchForm" method="post" action="">
            @csrf
            <div class="card-body">
            {{-- <input type="hidden" name="batchPdsDiv" id="batchPdsDiv" value="{{ $lumpv['param']['div'] }}"> --}}
                 <div class="row">
                    <div class="col-md-12 text-black">
                        <span class="form-control-xs col-2" style="float:left">분배 월 : </span> 
                        <div class="input-group date datetimepicker-wol col-md-5 p-0" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm datetimepicker-wol " data-target="#wol" name="wol" id="wol" dateonly="true" size="6" required>
                            <div class="input-group-append" data-target="#wol" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div> 
                    </div>
                </div>
            </div>
            <div class="card-footer " id="input_footer">
                <button type="button" class="btn btn-sm btn-default float-right ml-1" onclick="tmInsertAction('chk');">TM등록</button>
                <button type="button" class="btn btn-sm btn-default float-right" onclick="tmInsertAction('all');">결과전체등록</button>
            </div>
            <!-- /.card-footer -->
        </form>
    </div>
</div>
@section('javascript')
@parent
<script>
// TM 등록
function tmInsertAction(div)
{
    var chk_cnt = 0;
    var listdata = '';
    var lump_url = 'lumptminsert';
    var lump_div = '';
    var lump_name = 'loan';

    // 체크박스
    if(div == 'chk')
    {
        if(!isCheckboxChecked('listChk[]'))
        {
            alert('선택한 고객이 없습니다. 고객을 선택 후 이용해 주세요.');
            return;
        }

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

    if(isEmpty($('#wol').val()))
    {
        alert('해당 월을 선택해주세요.');
        $("#wol").focus();
        return;
    }
    if(isEmpty($("#tm_div").val()))
    {
        alert("재대출 또는 추가대출대상을 먼저 검색해주시기 바랍니다.");
        return;
    }

    if(ccCheck()) return; 

    if( !confirm("정말로 등록하시겠습니까?") )
    {
        globalCheck = false;
        return false;
    }

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata  = $('#tminsertBatchForm').serialize();
    var listChk   = getArrayCheckbox('listChk[]');
    var targetSql = $("#target_sql"+lump_name).val();
    var tmDiv     = $("#tm_div").val();
    postdata = listdata + postdata + '&' + listChk + '&div=' +div + '&tmDiv=' +tmDiv + '&targetSql=' +targetSql;

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
                $('#pds_name').val('');
                $('.selectpicker').selectpicker('val', '');
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