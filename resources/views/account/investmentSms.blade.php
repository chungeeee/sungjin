<!-- 투자내역 -->
<div class="col-md-12 p-0 m-0 " >
    <div class="card-header p-1" style="border-bottom:none !important;">
        <h6 class="card-title">문자내용</h6>
        <div class="card-tools pr-2">
        </div>
    </div>
    <div class="card-body" id="investmentinfoMemo">
        <form  class="mb-0" name="investment_memo_form" id="investment_memo_form" method="post" enctype="multipart/form-data">
        <input type="hidden" id="loan_info_no" name="loan_info_no" value="{{ $result['customer']['loan_info_no'] ?? '' }}">
        <input type="hidden" id="cust_info_no" name="cust_info_no" value="{{ $result['customer']['cust_info_no'] ?? '' }}">
        <input type="hidden" id="loan_usr_info_no" name="loan_usr_info_no" value="{{ $result['customer']['loan_usr_info_no'] ?? '' }}">
            <div class="row">
                <div class="col-md-12">
                    <table class="table table-sm table-bordered table-input text-xs col-md-5">
                    <colgroup>
                    <col width="20%"/>
                    <col width="80%"/>
                    </colgroup>
                    <tr>
                        <th><span class="text-danger font-weight-bold h6 mr-1"></span>기준일자</th>
                        <td>
                            <div class="ml-lg-n2">
                                <div class="row">
                                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1" id="target_divide_date" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm col-md-2 ml-2 datetimepicker-input" data-target="#target_divide_date" name="target_divide_date" id="target_divide_date" DateOnly="true" size="6" autocomplete="off">
                                        <div class="input-group-append" data-target="#target_divide_date" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th><span class="text-danger font-weight-bold h6 mr-1"></span>문자내용</th>
                        <td>
                            <div class="ml-lg-n2">
                                <div class="row">
                                    <select class="form-control form-control-sm col-md-3 ml-2" name="sms_div" id="sms_div">
                                    <option value=''>선택</option>
                                        {{ Func::printOption($array_sms_cd, '') }} 
                                    </select>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <td></td>
                        <td>
                            <div style="margin-left:105px;">
                                <button type="button" class="btn btn-sm btn-info" id="btn_confirm" onclick="confirmSms();">검색</button>
                            </div>
                        </td>
                    </tr>
                    </table>
                    <div class="row pl-2" id='add_sms'>
                    </div>
                </div>            
            </div>
        </form>
    </div>
</div>

<script>
$('.datetimepicker').datetimepicker({
    format: 'YYYY-MM-DD',
    locale: 'ko',
    useCurrent: false,
});

function confirmSms()
{    
    // CORS 방지
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#investment_memo_form').serializeArray();

    $.ajax({
        url  : "/account/investmentmemoview",
        type : "post",
        data : postdata,
        success : function(data)
        {
            if(data=="false")
            {
                alert("검색된 결과가 없습니다.\n검색조건을 다시 확인해주세요.")
            }
            else
            {
                $('#add_sms').html(data);
            }
        }
    });
}

// 복사기능
function Lbcopy(id)
{
    console.log(id);
    var obj = document.getElementById(id);
    console.log(obj);
    obj.select(); //인풋 컨트롤의 내용 전체 선택
    document.execCommand("copy"); //복사
    obj.setSelectionRange(0, 0); //커서 위치 초기화
    document.getElementById(id).style.backgroundColor = '#337ab7b3'; // 내용 출력 부분 컬러 변경
}

</script>