    <div id="LUMP_FORM_printAccountReg" class="lump-forms" style="display:none">
        <form name="printLumpForm" id="printLumpForm" method="post" action="">
        @csrf
        <div class="row">
            <label class="col-md-3 col-form-label">인쇄 양식</label>
            <div class="col-md-9">
                <select class="form-control form-control-sm" name="lump_post_cd" id="lump_post_cd">
                    <option value='1001041' title="{{App\Chung\Ubi::$erpTitle['1001041']}}">{!! mb_strlen(App\Chung\Ubi::$erpTitle['1001041']) > 25 ? mb_substr(App\Chung\Ubi::$erpTitle['1001041'],0,22)."..." : App\Chung\Ubi::$erpTitle['1001041'] !!}</option>
                </select>
            </div>
            <label class="col-md-3 col-form-label">받을 주소</label>
            <div class="col-md-9 p-1 pl-2">
                <input type="hidden" id="lump_post_addr" name="lump_post_addr" value=""/>
                <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_1" onclick="chgPostAddr('1');">실거주</button>
                <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_2" onclick="chgPostAddr('2');">등본</button>
                <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_3" onclick="chgPostAddr('3');">직장</button>
                <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_4" onclick="chgPostAddr('4');">기타</button>
                <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_P" onclick="chgPostAddr('P');">우편물주소</button>
            </div>
            <label class="col-md-3 col-form-label">기준일자</label>
            <div class="col-md-9 p-1 pl-2">
                <div class="input-group date datetimepicker" id="basis_date" data-target-input="nearest">
                    <input type="text" class="form-control form-control-sm dateformat datetimepicker" name="lump_print_basis_date" id="lump_print_basis_date" inputmode="text" value="{{ date('Y-m-d') }}">
                    <div class="input-group-append" data-target="#print_basis_date" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>

        </div> 


        <div class="row p-1">
            <label for="btn" class="col-sm-3 col-form-label"></label>
            <div class="col-md-9">
                <button class="btn btn-sm btn-info float-right mt-1" id="LUMPFORM_BTN_PRINT" onclick="lumpprint('A', '{{$lumpv['param']['div'] ?? ''}}'); return false;">일괄인쇄</button>
            </div>
        </div> 

        </form>
    </div>



<script>

    window.onload = function(){
        // 체크박스모양
        $('input[name="listChk[]"]').iCheck({
            checkboxClass: 'icheckbox_square-blue',
        });
        $('input[name="check-all"]').iCheck({
            checkboxClass: 'icheckbox_square-blue',
        });
        // 리스트 전체 선택/해제
        $('#{{ $result['listName'] }}ListHeader').on('ifChecked', '.check-all', function(event) {
            $('.list-check').iCheck('check');
        });
        $('#{{ $result['listName'] }}ListHeader').on('ifUnchecked', '.check-all', function(event) {
            $('.list-check').iCheck('uncheck');
        });
        
        $(".datetimepicker").datetimepicker({
            format: 'YYYY-MM-DD',
            locale: 'ko',
            useCurrent: false,
        });
        
        chgPostAddr("P");

    }




function chgPostAddr(v)
{
    $("#lump_post_addr").val(v);

    $.each($(".postBtn"), function(idx,item){

        if( item.getAttribute("id") == "postBtn_"+v )
        {
            item.classList.add("btn-danger");
            item.classList.remove("btn-secondary");
        }
        else
        {
            item.classList.add("btn-secondary");
            item.classList.remove("btn-danger");
        }
    });
}


function lumpprint(mode, erp_ups)
{
    if( erp_ups == "POSTCR" || erp_ups == "DOC" )
    {
        erp_ups = "ERP";
    }

    // 인쇄 양식별로 검색을 했는지 확인한다.
    @if( $lumpv['param']['div'] == "POSTCR" )
        if($('#rangeSearchDetail').val()!='li.delay_term')
        {
            alert('연체일수를 검색 후 이용해 주세요.');
            $('#rangeSearchDetail').focus();
            return false;
        }

        // 독촉장(서식1-1) : 연체일수 31일 이상
        if($('#lump_post_cd').val()=='1004034' && $('#sRangeSearchString').val()<31)
        {
            alert('연체일수를 31일 이상 검색 후 이용가능합니다.');
            $('#rangeSearchDetail').val('li.delay_term');
            $('#sRangeSearchString').val('31');
            getDataList('postCr', 1, '/erp/postcrlist', $('#form_postCr').serialize()); return false;
            return false;
        }
        // 기한이익상실 예정통보서 : 연체일수 51일 이상
        else if($('#lump_post_cd').val()=='1001008' && $('#sRangeSearchString').val()<51)
        {
            alert('연체일수를 51일 이상 검색 후 이용가능합니다.');
            $('#rangeSearchDetail').val('li.delay_term');
            $('#sRangeSearchString').val('51');
            getDataList('postCr', 1, '/erp/postcrlist', $('#form_postCr').serialize()); return false;
            return false;
        }
        // 채무조정안내면 : 연체일수 181일 이상
        else if($('#lump_post_cd').val()=='1003003' && $('#sRangeSearchString').val()<181)
        {
            alert('연체일수를 181일 이상 검색 후 이용가능합니다.');
            $('#rangeSearchDetail').val('li.delay_term');
            $('#sRangeSearchString').val('181');
            getDataList('postCr', 1, '/erp/postcrlist', $('#form_postCr').serialize()); return false;
            return false;
        }
    @endif

    pJrfDir = '/home/laravel/'+"{{ strtolower(config("app.comp")) }}"+'/public/ubi4/files/';

    var cnt = $('input[name="listChk[]"]:checked').length;
    if( cnt==0 )
    {
        alert("인쇄할 계약을 선택해주세요.");
        return false;
    }
    if( $('#lump_post_cd').val() == "" )
    {
        alert("인쇄 양식을 선택해주세요.");
        return false;
    }
    var directPrint = $.parseJSON('{!! json_encode(App\Chung\Ubi::$directPrint) !!}');
    if( directPrint.includes($('#lump_post_cd').val()) )
    {
        if( !confirm("해당 양식은 미리보기 없이 바로 출력됩니다.") )
        {
            return false;
        }
    }
    if( !confirm( "선택된 "+cnt+"개의 계약의 대하여 인쇄 하시겠습니까?" ) )
    {
        return false;
    }

    var formdata = $('#printLumpForm').serializeArray();

    var listChk = [];
    $("input:checkbox[name='listChk[]']:checked").each(function(i){  
        listChk.push($(this).val().split('_')[0]);
	});
    formdata.push({name : "listChk" , value : listChk});
    formdata.push({name : "lump_print_mode" , value : mode});
    formdata.push({name : "lump_action_code" , value : "LUMP_PRINT"});
    formdata.push({name : "lump_div" , value : "RELIEF"});



    var result = ubiPrint(formdata, erp_ups, "LUMP", directPrint);



}


</script>