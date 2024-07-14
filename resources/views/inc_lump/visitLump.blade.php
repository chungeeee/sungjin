    <div id="LUMP_FORM_visitLump" class="lump-forms" style="display:none">

        <form name="visitLumpForm" id="visitLumpForm" method="post" action="">
            @csrf    
            <div class="card card-outline primary" id="changeStatus">
                <div class="card-header flex-column status-border-right-none">
                    <h5 class="card-title text-bold" style='color:black'>
                        방문 일괄 취소
                    </h5>
                </div>
                <!-- /.card-body -->
                <div class="card-footer" id="input_footer">
                    <button class="btn btn-sm btn-danger float-right" id="LUMPFORM_BTN_visitSta" onclick="visitLumpAction(); return false;" >취소</button>
                </div>
                <!-- /.card-footer -->
            </div> 
        </form>
    </div>

    
<script>
    
    // 상태변경
    function visitLumpAction()
    {

        if(!isCheckboxChecked('listChk[]'))
        {
            alert('선택한 고객이 없습니다. 고객을 선택 후 이용해 주세요.');
            return;
        }
        
        if( !confirm("방문취소 하시겠습니까?") )
        {
            globalCheck = false;
            return false;
        }

        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#visitLumpForm').serialize();
        var listChk = getArrayCheckbox('listChk[]');
        postdata = postdata + '&' + listChk;

        $.ajax({
            url  : "/erp/visitcancellump",
            type : "post",
            data : postdata,
            success : function(result) {
                alert(result);  
                listRefresh();
                globalCheck = false;
            },
            error : function(xhr) {
                globalCheck = false;
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }


</script>