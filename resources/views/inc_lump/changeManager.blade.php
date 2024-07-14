

<div id="LUMP_FORM_changeManager" class="lump-forms" style="display:none">

    <div class="row p-1">
        <label for="label_change_manager_code" class="col-sm-3 col-form-label">지점</label>
        <div class="col-md-9">
            <select class="form-control form-control-sm selectpicker" name="change_manager_code" id="change_manager_code" onchange="changeLumpManagerCode(this.value,'change_manager_id');" @if( !Func::funcCheckPermit("E003") ) disabled @endif >
            <option value=''>지점선택</option>
                {{ Func::printOptionArray($array_branch, 'branch_name', Auth::user()->branch_code ?? '') }}
            </select>
        </div>
    </div> 

    <div class="row p-1">
        <label for="change_manager_id" class="col-sm-3 col-form-label">담당자</label>
        <div class="col-md-9">
            <select class="form-control form-control-sm selectpicker" name="change_manager_id" id="change_manager_id">
            <option value=''>담당</option>
            </select>
        </div>
    </div> 

    <div class="row p-1">
        <label for="btn" class="col-sm-3 col-form-label"></label>
        <div class="col-md-9">
        <button class="btn btn-sm btn-info" id="LUMPFORM_BTN_changeManager" onclick="lumpChangeManager(); return false;">담당자변경 실행</button>
        </div>
    </div> 

</div>



<script>

function changeLumpManagerCode(val, toid)
{
    $("#"+toid).empty();
    var option = $("<option value=''>담당자 미배분</option>");
    $("#"+toid).append(option);

    @foreach( $array_branch_users as $bcd => $vus)
    if( val=='{{ $bcd }}' )
    {
        @if( isset($vus) && sizeof($vus)>0 )
        @foreach( $vus as $vtmp )
        var option = $("<option value='{{ $vtmp->id }}'>{{ $vtmp->name }}</option>");
        $("#"+toid).append(option);
        @endforeach
        @endif
    }
    @endforeach

    // 화면갱신
    $("#"+toid).selectpicker({
		width: '100%',
		style: 'btn-default form-control-sm bg-white',
	});   
    $("#"+toid).selectpicker('refresh');

}

function lumpChangeManager()
{
    if($('#change_manager_code').val()=='')
    {
        alert("변경할 지점을 선택해주세요.");
        $('#change_manager_code').focus();
        return false;
    }

    var cnt = $('input[name="listChk[]"]:checked').length;
    if( cnt==0 )
    {
        alert("담당자 변경할 계약을 선택해주세요.");
        return false;
    }
    if( !confirm( "선택된 "+cnt+"개의 계약에 대하여 담당자를 변경하시겠습니까?" ) )
    {
        return false;
    }

    var change_manager_code = $("#change_manager_code").val();
    var change_manager_id   = $("#change_manager_id").val();

    var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
    formData.append("lump_action_code",    "CHANGE_LOAN_MANAGER");
    formData.append("change_manager_code", change_manager_code);
    formData.append("change_manager_id",   change_manager_id);

    @if (!empty($location) && $location == 'relief')
    {{-- relief는 reliefno를 넘겨서 하는 처리 --}}
    formData.delete('listChk[]');
    var cnt = 0;
    $('input[name="listChk[]"]').each(function (index, item)
    {
        if ($(item).is(':checked'))
        {
            // _ 구분 시 뒤에는 계약번호
            formData.set('listChk['+cnt+']', $(item).val().split('_')[1]);
            cnt++;
        }
    });
    @endif

    $("#LUMPFORM_BTN_changeManager").prop("disabled",true);

    $.ajax({
        url  : "/erp/lumpchangemanager",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result=="Y" )
            {
                alert("일괄처리 완료");
                listRefresh();
                closeLump();
            }
            else
            {
                alert(result);
            }
            $("#LUMPFORM_BTN_changeManager").prop("disabled",false);
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
            $("#LUMPFORM_BTN_changeManager").prop("disabled",false);
        }
    });

}

</script>

@section('javascript')
<script>
changeLumpManagerCode($('#change_manager_code').val(),'change_manager_id');

$('#change_manager_code').selectpicker({
        dropupAuto: false
});

</script>

@endsection
