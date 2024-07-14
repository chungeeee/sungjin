<form class="form-horizontal" role="form" name="divide_origin_form" id="divide_origin_form" method="post">
    @csrf
    <input type="hidden" id="loan_info_no" name="loan_info_no" value="{{ $v->no }}">
    <input type="hidden" id="status" value="N">
    
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
                    <div class="col-md-12"><h3 class="card-title"><i class="fas fa-user m-2" size="9px">원금조정</i></div>
                </div>

                <div class="card-body p-1">
                    <table class="table table-sm card-secondary card-outline mt-1 table-bordered">
                    <colgroup>
                    <col width="20%"/>
                    <col width="35%"/>
                    <col width="15%"/>
                    <col width="15%"/>
                    <col width="15%"/>
                    </colgroup>
                    <thead>
                    <tr>
                        <th class="text-center">상환일자</th>
                        <th class="text-center">조정원금</th>
                        <th class="text-center">거치기간이자</th>
                        <th class="text-center">기지급이자</th>
                        <th class="text-center">거치기간</th>
                    </tr>
                    </thead>
                    <tbody id="inputTbody">
                        <tr>
                            <td class="text-center">
                                <div class="row">
                                    <div class="input-group date datetimepicker col-md-12" id="div_trade_date" data-target-input="nearest">
                                        <div class="col-md-8">
                                            <input type="text" class="form-control form-control-sm" data-target="#div_trade_date" id="trade_date" name="trade_date" DateOnly='true' value="{{ date('Y-m-d') }}"  onchange="changeTradeDate()" maxlength ='10'/>
                                        </div>
                                        <div class="col-md-4">
                                            <div class="input-group-append" data-target="#div_trade_date" data-toggle="datetimepicker">
                                                <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="row">
                                    <div class="col-md-8">
                                        <input type="text" class="form-control form-control-sm text-right moneyformat" id="trade_money" name="trade_money" placeholder="원단위 입력" value="{{ number_format($v->balance) }}" onkeyup="calculate();" onblur="calculate();">
                                    </div>
                                    <div class="col-md-4">
                                        <button type="button" class="btn btn-sm btn-info" onclick="setReNew();">이자계산</button>
                                    </div>
                                </div>
                            </td>
                            <td class="text-right" id="return_intarval_interest"></td>
                            <td class="text-right" id="loan_return_interest"></td>
                            <td class="text-right" id="return_intarval"></td>
                        </tr>
                    </tbody>
                    </table>
                </div>                
            </div>
        </div>

        <div class="col-md-12">
            <div class="card card-outline card-lightblue">        
                <div class="card-header p-1 row">
                    <div class="col-md-12"><h3 class="card-title"><i class="fas fa-user m-2" size="9px">원금상환</i></div>
                </div>

                <div class="card-body p-1">
                    <table class="table table-sm card-secondary card-outline mt-1 table-bordered">
                    <colgroup>
                    <col width="20%"/>
                    <col width="15%"/>
                    <col width="15%"/>
                    <col width="15%"/>
                    <col width="35%"/>
                    </colgroup>
                    <thead>
                    <tr>
                        <th class="text-center">원금</th>
                        <th class="text-center">이자</th>
                        <th class="text-center">소득세</th>
                        <th class="text-center">지방소득세</th>
                        <th class="text-center">실지급액</th>
                    </tr>
                    </thead>
                    <tbody id="inputTbody">
                        <tr>
                            <td class="text-right" id="return_origin"></td>
                            <td class="text-right" id="return_interest"></td>
                            <td class="text-right" id="return_income_tax"></td>
                            <td class="text-right" id="return_local_tax"></td>
                            <td class="text-right" id="return_origin_real"></td>
                        </tr>
                    </tbody>
                    </table>
                </div>            
            </div>
            <div class="col-md-12">
                <button type="button" class="btn btn-sm btn-info float-right" style="display:none;" id="divide_origin" onclick="divideOriginAction();">원금상환</button>
            </div>
        </div>
    </div>
    <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
    </div>
</form>

<script>

$(document).ready(function()
{    
    $(".datetimepicker").datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
        useCurrent: false,
    });
});

function calculate()
{
    var balance     = {{ $v->balance ?? 0 }};
    var trade_money = Number($('#trade_money').val().replace(/,/gi,""));

    if(trade_money > balance)
    {
        alert('조정가능액이 투자잔액을 초과하여 투자잔액기준으로 설정합니다.');
        $('#trade_money').val(balance).number(true);
    }
    else
    {
        $('#trade_money').val(trade_money).number(true);
    }

    $('#status').val('N');
    $("#divide_origin").css("display", "none");
}

function changeTradeDate()
{
    $('#status').val('N');
    $("#divide_origin").css("display", "none");
}

function setReNew()
{
    calculate();

    var loan_info_no  = $('#loan_info_no').val();
    var contract_date = {{ $v->contract_date }};
    var contract_end_date = {{ $v->contract_end_date }};
    var balance       = {{ $v->balance }};
    var trade_date    = $('#trade_date').val();
    var trade_money   = Number($('#trade_money').val().replace(/,/gi,""));

    if(trade_date != '')
    {   
        trade_date = trade_date.replace(/[^0-9]/g, "");
        if((trade_date.length) != '8')
        {
            alert('상환일자의 날짜형식을 확인해주세요.');
            return false;
        }
        else if(trade_date <= contract_date)
        {
            alert('상환일자의 날짜를 확인해주세요.');
            return false;
        }
        else if(trade_date > contract_end_date)
        {
            alert('만기일자와 상환일자를 확인해주세요.');
            return false;
        }
    }
    else
    {
        alert('상환일자를 입력해주세요.');
        return false;
    }

    if(trade_money > balance)
    {
        alert('상환원금과 잔액을 확인해주세요.');
        return false;
    }
    else if(trade_money == 0)
    {
        alert('상환원금을 0보다 크게 입력해주세요.');
        return false;
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url  : "/account/divideorigininterest",
        type : "post",
        data : { loan_info_no:loan_info_no, contract_date:contract_date, trade_date:trade_date, trade_money:trade_money },
        success : function(data)
        {
            $("#return_intarval_interest").html(data['return_intarval_interest']+" 원");
            $("#loan_return_interest").html(data['loan_return_interest']+" 원");
            $("#return_intarval").html(data['return_intarval']+" 일");
            
            $("#return_origin").html(data['return_origin']+" 원");
            $("#return_interest").html(data['return_interest']+" 원");
            $("#return_income_tax").html(data['return_income_tax']+" 원");
            $("#return_local_tax").html(data['return_local_tax']+" 원");
            $("#return_origin_real").html(data['return_origin_real']+" 원");

            $('#status').val('Y');
            $("#divide_origin").css("display", "");

            alert("이자가 계산되어 아래표에 적용되었습니다.");
        },
        error : function(xhr)
        {
            console.log(xhr);
        }
    });
}

// 원금조정 등록 Action
function divideOriginAction() 
{
    var status        = $('#status').val();
    var loan_info_no  = $('#loan_info_no').val();
    var contract_date = {{ $v->contract_date }};
    var contract_end_date = {{ $v->contract_end_date }};
    var balance       = {{ $v->balance }};
    var trade_date    = $('#trade_date').val();
    var trade_money   = Number($('#trade_money').val().replace(/,/gi,""));

    if(status != 'Y')
    {
        alert('이자계산버튼을 눌러주세요.');
        return false;
    }

    if(trade_date != '')
    {   
        trade_date = trade_date.replace(/[^0-9]/g, "");
        if((trade_date.length) != '8')
        {
            alert('상환일자의 날짜형식을 확인해주세요.');
            return false;
        }
        else if(trade_date <= contract_date)
        {
            alert('투자일자와 상환일자를 확인해주세요.');
            return false;
        }
        else if(trade_date > contract_end_date)
        {
            alert('만기일자와 상환일자를 확인해주세요.');
            return false;
        }
    }
    else
    {
        alert('상환일자를 입력해주세요.');
        return false;
    }
    
    // 조정가능액이 남아있는 경우
    if(trade_money < balance)
    {
        if( !confirm("원금 조정금액이 남아있습니다. 진행하시겠습니까?") )
        {
            return false;
        }
    }
    else if(trade_money > balance)
    {
        alert('상환원금과 잔액을 확인해주세요.');
        return false;
    }
    else if(trade_money == 0)
    {
        alert('상환원금을 0보다 크게 입력해주세요.');
        return false;
    }

    // 중복클릭 방지
    if(ccCheck()) return;

    $.ajax({
        url  : "/account/divideoriginformaction",
        type : "post",
        data : { loan_info_no:loan_info_no, trade_date:trade_date, trade_money:trade_money },
        success : function(data)
        {
            // 성공알림 
            if(data['rs_code'] == "Y") 
            {
                globalCheck = false;
                alert(data['result_msg']);
                location.href='/account/investmentpop?no='+loan_info_no;
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