<div id="LUMP_FORM_lawXml" class="lump-forms" >
    <form name="lawXmlForm" id="lawXmlForm" method="post" action="">
        @csrf    
        <div class="row p-1">
            <label for="xml_app_date" class="col-sm-3 col-form-label text-white" style="color:black">등록일자</label>
            <div class="col-md-9">
                <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-1 mt-1" id="xml_app_date" data-target-input="nearest">
                    <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#xml_app_date" name="xml_app_date" id="xml_app_date_id" DateOnly="true" size="6">
                    <div class="input-group-append" data-target="#xml_app_date" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                    </div>
                </div>
            </div>
        </div> 
        <button type="button" class="btn btn-sm btn-info float-right" id="LUMPFORM_BTN_borrowStatus" onclick="lawXmlDown('lawxml'); return false;">XML다운</button>
    </form>
</div>

<script>
    


    // 상태변경
    function lawXmlDown(url)
    {
        if(!isCheckboxChecked('listChk[]'))
        {
            alert('선택한 고객이 없습니다. 고객을 선택 후 이용해 주세요.');
            return;
        }
        
        if(!$('#xml_app_date_id').val())
        {
            alert('등록일자를 입력해주세요');
            $('#lump_borrow_comp_sub_no').focus();
            return false;
        }

        if(ccCheck()) return;

        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#lawXmlForm').serialize();
        var listChk = getArrayCheckbox('listChk[]');
        postdata = postdata + '&' + listChk;
console.log(postdata);
        $.ajax({
            url     : "/erp/"+url,
            type    : "post",
            data    : postdata,
            success : function(data) {
                console.log(data);
                globalCheck = false;
                alert(data.msg);

                if(data.result == 'Y')
                {
                    window.location.href = '/erp/lawxmldown?filename='+data.filename;
                }
            },
            error : function(xhr) {
                globalCheck = false;
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }




</script>
