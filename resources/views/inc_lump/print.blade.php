    <div id="LUMP_FORM_print" class="lump-forms" style="display:none">
        <form name="printLumpForm" id="printLumpForm" method="post" action="">
        @csrf
        <div class="row">
            <label class="col-md-3 col-form-label">인쇄 양식</label>
            <div class="col-md-9">
                <select class="form-control form-control-sm" name="post_cd" id="post_cd">
                    <option value=''>선택</option>
                    @if( $lumpv['param']['div'] == "ERP" || $lumpv['param']['div'] == "POSTCR" || $lumpv['param']['div'] == "VISIT" || $lumpv['param']['div'] == "DOC")

                        @foreach( Vars::$arrayPaperForm as $key => $v )
                            <option value='{{$key}}' title="{{$v}}">{!! mb_strlen($v) > 25 ? mb_substr($v,0,22)."..." : $v !!}</option>
                        @endforeach

                    @elseif( $lumpv['param']['div'] == "UPS" )
                        @foreach( Vars::$arrayUPSPaperForm as $key => $v )
                            <option value='{{$key}}' title="{{$v}}">{!! mb_strlen($v) > 25 ? mb_substr($v,0,22)."..." : $v !!}</option>
                        @endforeach
                    @endif

                </select>
            </div>
            <label class="col-md-3 col-form-label">받을 주소</label>
            <div class="col-md-9 p-1 pl-2">
                <input type="hidden" id="post_addr" name="post_addr" value=""/>
                <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_1" onclick="chgPostAddr('1');">실거주</button>
                <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_2" onclick="chgPostAddr('2');" @if( $lumpv['param']['div'] == "POSTCR" ) disabled @endif >등본</button>
                <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_3" onclick="chgPostAddr('3');" @if( $lumpv['param']['div'] == "POSTCR" ) disabled @endif >직장</button>
                <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_4" onclick="chgPostAddr('4');" @if( $lumpv['param']['div'] == "POSTCR" ) disabled @endif >기타</button>
                <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_P" onclick="chgPostAddr('P');" @if( $lumpv['param']['div'] == "POSTCR" ) disabled @endif >우편물주소</button>
            </div>
            <label class="col-md-3 col-form-label">기준일자</label>
            <div class="col-md-9 p-1 pl-2">
                <div class="input-group date datetimepicker" id="basis_date" data-target-input="nearest">
                    <input type="text" class="form-control form-control-sm dateformat datetimepicker" name="print_basis_date" id="print_basis_date" inputmode="text" value="{{ date('Y-m-d') }}">
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
        
        @if( $lumpv['param']['div'] == "POSTCR" )
            chgPostAddr("1");
        @else 
            chgPostAddr("P");
        @endif

    }




function chgPostAddr(v)
{
    $("#post_addr").val(v);

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
    var formDiv = erp_ups;
    if( erp_ups == "POSTCR" || erp_ups == "DOC" || erp_ups == "VISIT" )
    {
        erp_ups = "ERP";
    }

    var cnt = $('input[name="listChk[]"]:checked').length;
    if( cnt==0 )
    {
        alert("인쇄할 계약을 선택해주세요.");
        return false;
    }
    if( $('#post_cd').val() == "" )
    {
        alert("인쇄 양식을 선택해주세요.");
        return false;
    }
    if( !confirm( "선택된 "+cnt+"개의 계약의 대하여 인쇄 하시겠습니까?" ) )
    {
        return false;
    }

    var formdata = $('#printLumpForm').serializeArray();
    
    var listChk = [];
    $("input:checkbox[name='listChk[]']:checked").each(function(i){
        listChk.push($(this).val());
	});
    formdata.push({name : "listChk" , value : listChk});
    formdata.push({name : "print_mode" , value : mode});
    formdata.push({name : "action_code" , value : "LUMP_PRINT"});
    formdata.push({name : "div" , value : formDiv});
    
    /*
    var msg = "";
    for(var i=0; i<formdata.length; i++)
    {
        var raky = Object.keys(formdata[i]);
        msg+="\n"+raky[0]+":"+eval("formdata[i]."+raky[0])+""+raky[1]+":"+eval("formdata[i]."+raky[1]);
    }
    alert(msg);    
    */

    var url = "/lump/printview?fData="+JSON.stringify(formdata);
    var wnd = window.open(url, "printview","width=900, height=1000, scrollbars=yes");
    wnd.focus();
}


</script>