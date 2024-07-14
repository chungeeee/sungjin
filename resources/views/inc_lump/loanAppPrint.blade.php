<div id="LUMP_FORM_loanAppPrint" class="lump-forms" style="display:none">
    <form name="loanAppPrintForm" id="loanAppPrintForm" method="post" action="">
        @csrf    
        <input type="hidden" name="params" id="params">
        <div class="col-md-12 row">
            <button class="btn btn-sm btn-default float-right" id="LUMPFORM_BTN_agentLevel" onclick="printAction('agentLevel'); return false;">제휴사이력제</button>
        </div>
    </form>
</div>

<script>
    
    // 상태변경
    function printAction(div)
    {

        if(!isCheckboxChecked('listChk[]'))
        {
            alert('선택한 고객이 없습니다. 고객을 선택 후 이용해 주세요.');
            return;
        }
        
        var url = '';
        var nowTabs = $('#tabsSelect{{ $result['listName'] ?? '' }}').val();
        if(div=='agentLevel')
        {
            url = 'printagentlevel'; 
        }

        if(ccCheck()) return;

        if( !confirm("정말로 인쇄하시겠습니까?") )
        {
            globalCheck = false;
            return false;
        }

        //history.replaceState({}, null, location.pathname);
        var postdata = $('#loanAppPrintForm').serialize();
        var listChk = getArrayCheckbox('listChk[]');
        postdata = postdata + '&' + listChk + "&form=" + div;

        var frm = $('#loanAppPrintForm');
        $("#params").val(postdata);

        $('#loanAppPrintForm').attr("action", '/lump/'+url);
        $('#loanAppPrintForm').attr("method", "post");
        $('#loanAppPrintForm').attr("target", "popOpen");

        window.open('','popOpen','right=0,top=0,height=' + screen.height + ',width=900,scrollbars=yes');
       
        $('#loanAppPrintForm').submit();

    }

</script>
