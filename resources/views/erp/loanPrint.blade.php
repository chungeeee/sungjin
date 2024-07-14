<style>
    .dropdown-menu {
        min-width: 100%;
    }
</style>

<div class="p-2">
    <b>양식인쇄</b>
    <form id="print_form" method="post" name="print_form">
        @csrf
        <input type="hidden" name="loan_info_no" id="loan_info_no" value="{{ $loan_info_no ?? '' }}">
        <input type="hidden" name="cust_info_no" id="cust_info_no" value="{{ $cinfo->no ?? '' }}">
        <input type="hidden" name="lump_yn" id="lump_yn" value="N">
        <input type="hidden" name="loan_info_law_no" id="loan_info_law_no" value="{{ $cinfo->loan_info_law_no ?? '' }}">
        <table class="table table-sm table-bordered table-input text-xs">
            <colgroup>
                <col width="20%" />
                <col width="30%" />
                <col width="50%" />
            </colgroup>
            <tbody>
            <tr>
                <th>인쇄양식</th>
                <td colspan="2">
                    <div class="form-group row">
                        <select class="form-control form-control-sm col-sm-6 ml-2 mr-2" id="post_cd" name="post_cd" title="선택" >
                            <option value=''>선택</option>
                            {!! Func::printOption(Vars::$arrayPaperForm); !!}
                        </select>
                    </div>
                </td>
                <td colspan=2>
                </td>
            </tr> 

            <tr>
                <th>기준일자</th>
                <td>
                    <div class="input-group date datetimepicker" id="basis_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm dateformat datetimepicker" name="print_basis_date" id="print_basis_date" inputmode="text" value="{{ date('Y-m-d') }}" DateOnly="true" size="6">
                        <div class="input-group-append" data-target="#print_basis_date" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <th>받을 주소</th>
                <td colspan="2">
                    <div class="row">
                        <div class="input-group col-sm-4 pb-1">
                            <input type="hidden" id="post_addr_cd" name="post_addr_cd" value=""/>
                            <input type="text" class="form-control" name="zip" id="zip" numberonly="true" value="" readonly="">
                        </div>
                        <div class="pl-0 p-1">
                            <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_1" onclick="setAddr('zip', 'addr', 'addr2', '{{$cinfo->zip1 ?? ''}}', '{{$cinfo->addr11 ?? ''}}', '{{$cinfo->addr12 ?? ''}}'); setAddrInput('1');">실거주</button>
                            <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_2" onclick="setAddr('zip', 'addr', 'addr2', '{{$cinfo->zip2 ?? ''}}', '{{$cinfo->addr21 ?? ''}}', '{{$cinfo->addr22 ?? ''}}'); setAddrInput('2');">등본</button>
                            <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_3" onclick="setAddr('zip', 'addr', 'addr2', '{{$cinfo->zip3 ?? ''}}', '{{$cinfo->addr31 ?? ''}}', '{{$cinfo->addr32 ?? ''}}'); setAddrInput('3');">직장</button>
                            <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_4" onclick="setAddr('zip', 'addr', 'addr2', '{{$cinfo->zip4 ?? ''}}', '{{$cinfo->addr41 ?? ''}}', '{{$cinfo->addr42 ?? ''}}'); setAddrInput('4');">기타</button>
                            <button type="button" class="btn btn-secondary btn-xs postBtn" id="post_addr" onclick="setAddr('zip', 'addr', 'addr2', '{{$cinfo->post_zip ?? ''}}', '{{$cinfo->post_addr11 ?? ''}}', '{{$cinfo->post_addr12 ?? ''}}');  setAddrInput('{{$cinfo->post_send_cd}}');">우편물주소</button>
                            <!-- <button type="button" class="btn btn-secondary btn-xs" onclick="setAddr('zip', 'addr', 'addr2', '', '', ''); setAddrInput('');">주소지우기</button> -->
                        </div>
                    </div>
                    <input type="text" class="form-control mb-1 col-md-10" name="addr" id="addr" value="" readonly="">
                    <input type="text" class="form-control col-md-10" name="addr2" id="addr2" value="" readonly="">
                </td>
            </tr>
            
            <tr>
                <td colspan=4 class="">
                    <button type="button" class="btn btn-sm btn-secondary  mb-1" onclick="printAction();">
                        <i class="fas fa-print"></i> 인쇄
                    </button>
                </td>
            </tr>
        </table>
    </form>
</div>


<script>
    
    $('#print_basis_date').datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
        useCurrent: false,
        widgetPositioning:{
            horizontal : 'auto',
            vertical: 'bottom'
        }
    });
    $(function(){

        $("#post_addr").click();
    })

    function setAddrInput(code)
    {
        if(code == '9')
        {
            code = '4';
        }
        $('#post_addr_cd').val(code);

        if( code!="1" && code!="2" && code!="3" && code!="4" )
        {
            $.each($(".postBtn"), function(idx,item){

                if( item.getAttribute("id") == "post_addr" )
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
        else
        {
            $.each($(".postBtn"), function(idx,item){

                if( item.getAttribute("id") == "postBtn_"+code )
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
    }

    function printAction()
    {
        
        var postCd = $('#post_cd').val();
        
        if(postCd=="transaction" || postCd=="collect_money" | postCd=="sanghwan")
        {
            var urld  = "/lump/printviewloan";
            var title = "printviewloan";
        }
        else
        {
            var urld  = "/lump/printview";
            var title = "printview";
        }
        
        
        var formdata = $('#print_form').serializeArray();
        var url = urld+"?fData="+JSON.stringify(formdata);
        var wnd = window.open(url, title,"width=900, height=800, scrollbars=yes");
        wnd.focus();

    }



</script>