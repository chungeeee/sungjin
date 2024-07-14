<form class="form-horizontal" role="form" name="divide_plus_form" id="divide_plus_form" method="post">
    @csrf
    <input type="hidden" id="loan_info_no" name="loan_info_no" value="{{ $v->no }}">
    <input type="hidden" id="info_date" value="{{ date('Ymd') }}">
    <input type="hidden" id="term" name="term" value="0">
    
    <br>

    <div class="content-wrapper needs-validation m-0">
        <div class="col-md-12">
            <div class="card card-outline card-lightblue">        
                <div class="card-header p-1">
                    <h3 class="card-title"><i class="fas fa-user m-2" size="9px">계약정보</i>
                    <div class="card-tools pr-2">
                    </div>
                </div>

                <div class="card-body p-1">
                    <table class="table table-sm table-bordered table-input text-xs">
                    <colgroup>
                    <col width="20%"/>
                    <col width="20%"/>
                    <col width="15%"/>
                    <col width="15%"/>
                    <col width="30%"/>
                    </colgroup>

                    <thead>
                    <tr>
                        <th class="text-center">투자자번호</th>
                        <th class="text-center">투자자명</th>
                        <th class="text-center">투자일자</th>
                        <th class="text-center">만기일자</th>
                        <th class="text-center">투자잔액</th>
                    </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td class="text-center">{{ $v->investor_no ?? '' }}</td>
                            <td class="text-center">{{ $v->name ?? '' }}</td>
                            <td class="text-center">{{ Func::dateFormat($v->contract_date) ?? '' }}</td>
                            <td class="text-center">{{ Func::dateFormat($v->contract_end_date) ?? '' }}</td>
                            <td class="text-center">{{ number_format($v->balance) ?? '' }}</td>
                        </tr>  
                    </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card card-outline card-lightblue">        
                <div class="card-header p-1 row">
                    <div class="col-md-12"><h3 class="card-title"><i class="fas fa-user m-2" size="9px">만기갱신</i></div>
                </div>

                <div class="card-body p-1">
                    <table class="table table-sm card-secondary card-outline mt-1 table-bordered">
                    <colgroup>
                    <col width="30%"/>
                    <col width="20%"/>
                    <col width="30%"/>
                    <col width="20%"/>
                    </colgroup>
                    <thead>
                    <tr>
                        <th class="text-center">현재만기일자</th>
                        <th class="text-center">연장기간</th>
                        <th class="text-center">연장만기일자</th>
                        <th class="text-center"></th>
                    </tr>
                    </thead>
                    <tbody id="inputTbody">
                        <tr>
                            <td class="text-center">
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="text" class="form-control form-control-sm" id="contract_end_date" value="{{ Func::dateFormat($v->contract_end_date) ?? '' }}" disabled/>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-sm btn-info" onclick="setReNew('1')">1년</button>
                                    <button type="button" class="btn btn-sm btn-info" onclick="setReNew('2')">2년</button>
                                    <button type="button" class="btn btn-sm btn-info" onclick="setReNew('3')">3년</button>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="row" id="divide_plus">
                                    <div class="col-md-12">
                                        <input type="text" class="form-control form-control-sm" id="trade_date" name="trade_date" value="" readonly>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="col-md-12">
                                    <button type="button" class="btn btn-sm btn-info" onclick="dividePlusAction();">만기연장</button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                    </table>
                </div>            
            </div>
        </div>
    </div>
    <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
    </div>
</form>

<script>

function setReNew(term)
{
    var loan_info_no        = $('#loan_info_no').val();
    var info_date           = $('#info_date').val();
    var contract_date       = {{ $v->contract_date }};
    var contract_end        = {{ $v->contract_end_date }};  // '-' 없는 형식필요
    var contract_end_date   = $('#contract_end_date').val();
    
    var balance       = {{ $v->balance }};
    //var sum_interest  = {{ $v->sum_interest }};

    if(contract_date != '')
    {
        if(info_date < contract_date)
        {
            alert('투자일자가 미래인경우 만기연장이 불가합니다.');
            return false;
        }
    }
    else
    {
        alert('투자일자를 존재하지않습니다.');
        return false;
    }

    if(info_date < contract_end)
    {
        alert('만기일이 지난 계약만 만기연장이 가능합니다.');
        return false;
    }

    if(!(balance > 0))
    {
        alert('잔액을 확인해주세요.');
        return false;
    }

    //if(sum_interest > 0)
    //{
    //    alert('잔여수익지급을 확인해주세요.');
    //    return false;
    //}

    var date = new Date(contract_end_date);

    var year = date.getFullYear() + parseInt(term);
    var month = new String(date.getMonth()+1); 
    var day = new String(date.getDate()); 

    if(month.length == 1)
    { 
        month = "0" + month; 
    }
    if(day.length == 1)
    { 
        day = "0" + day; 
    }

    $('#trade_date').val(year +'-'+ month +'-'+ day);

    $('#term').val(term);
}

// 만기연장 등록 Action
function dividePlusAction() 
{
    var term                = parseInt($('#term').val());
    var loan_info_no        = $('#loan_info_no').val();
    var contract_date       = {{ $v->contract_date }};
    var contract_end        = {{ $v->contract_end_date }};  // '-' 없는 형식필요
    var contract_end_date   = $('#contract_end_date').val();
    var balance             = {{ $v->balance }};
    //var sum_interest  = {{ $v->sum_interest }};
    var info_date     = $('#info_date').val();

    if(contract_date != '')
    {
        if(info_date < contract_date)
        {
            alert('투자일자가 미래인경우 만기연장이 불가합니다.');
            return false;
        }
    }
    else
    {
        alert('투자일자를 확인해주세요.');
        return false;
    }
    
    if(info_date < contract_end)
    {
        alert('만기일이 지난 계약만 만기연장이 가능합니다.');
        return false;
    }

    if(!(term > 0))
    {
        alert('연장기간버튼을 눌러주세요.');
        return false;
    }
    
    if(!(balance > 0))
    {
        alert('잔액을 확인해주세요.');
        return false;
    }

    //if(sum_interest > 0)
    //{
    //    alert('잔여수익지급을 확인해주세요.');
    //    return false;
    //}

    var date = new Date(contract_end_date);

    var year = date.getFullYear() + term;
    var month = new String(date.getMonth()+1); 
    var day = new String(date.getDate()); 

    if(month.length == 1)
    { 
        month = "0" + month; 
    }
    if(day.length == 1)
    { 
        day = "0" + day; 
    }

    var trade_date = year +'-'+ month +'-'+ day;

    // 중복클릭 방지
    if(ccCheck()) return;

    $.ajax({
        url  : "/account/divideplusformaction",
        type : "post",
        data : { loan_info_no:loan_info_no, trade_date:trade_date, term:term, balance:balance },
        success : function(data)
        {
            // 성공알림 
            if(data['rs_code'] == "Y") 
            {
                globalCheck = false;
                alert(data['result_msg']);
                $(".modal-backdrop").remove();
                $("#dividePlusModal").modal('hide');
                getInvestmentData('investmentinfo');
            }
            // 실패알림
            else 
            {
                globalCheck = false;
                alert(data['result_msg']);
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다. 관리자에게 문의해주세요.");
            globalCheck = false;
        }
    });
}

</script>