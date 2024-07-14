<style>
    .dropdown-menu {
        min-width: 100%;
    }
</style>

<div class="p-2">
    <b>신복계좌번호</b>
    <form id="ccrsaccount_form" method="post" name="ccrsaccount_form">
        @csrf
        <input type="hidden" name="loan_info_no" id="loan_info_no" value="{{ $v->no ?? '' }}">
        <input type="hidden" name="cust_info_no" id="cust_info_no" value="{{ $v->cust_info_no ?? '' }}">
        <table class="table table-sm table-bordered table-input text-xs">
            <colgroup>
                <col width="20%" />
                <col width="30%" />
                <col width="50%" />
            </colgroup>
            <tbody>
            <tr>
                <th>신복계좌번호</th>
                <td colspan='2'>
                    <div class="row">
                        <div class="col-md-3 m-0 pr-0">
                            <input type="text" class="form-control" name="ccrs_account" id="ccrs_account" value="{{$v->ccrs_account ?? ''}}">
                        </div>
                        <div class="col-md-3 m-0">
                            <button class="btn btn-sm bg-lightblue" onclick="ccrsAccountAction();">저장</div>
                        </div>
                    </div>
                </td>
            </tr>
        </table>
    
    </form>
</div>


<script>

function ccrsAccountAction()
{

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#ccrsaccount_form').serialize();

    $("#loan-tabs-home").html(loadingString);
    $.post(
        "/erp/ccrsaccountaction", 
        postdata, 
        function(data) {
            alert(data.result_msg);
            getLoanData('ccrsaccount', data.no);
            // getLoanGuarantor(data.loan_info_no,data.no);
    });
}

</script>