@extends('layouts.masterPop')
@section('content')

<!-- 현장내역 -->
<div class="col-md-12 p-0 m-0 " >
    <div class="card-body" id="managementinfoInput">
    <form  class="mb-0" name="management_form" id="management_form" method="post" enctype="multipart/form-data">
    <input type="hidden" id="contract_info_no" name="contract_info_no" value="{{ $v->no ?? '' }}">
    <input type="hidden" id="status" value="{{ $v->status ?? '' }}">
        <div class="row">
            <div class="col-md-12">
                <div class="form-group row usr_collapse" id="collapseSearch">
                    <label class="col-sm-2 col-form-label"></label>
                    <div class="col-sm-10" id="usrCollapseSearchResult">
                    </div>
                </div>

                <div class="row" id="invest_input">
                    <div class="col-md-12">
                        <h3 class="card-title"><i class="fas fa-user m-2" size="9px"></i>일위대가 상세</h3>
                        <div class="card-tools text-right">
                            <button type="button" class="btn btn-sm btn-primary text-right" onclick="plusCode();">항목 추가</button>
                            <button type="button" class="btn btn-sm btn-danger text-right" onclick="removeCode();">항목 삭제</button>
                        </div>
                    </div>
                    <div class="card-body p-1">
                        <table class="table table-sm table-bordered table-input text-xs">
                            <colgroup>
                                <col width="7%"/>
                                <col width="10%"/>
                                <col width="10%"/>
                                <col width="10%"/>
                                <col width="5%"/>
                                <col width="10%"/>
                                <col width="10%"/>
                                <col width="10%"/>
                                <col width="10%"/>
                                <col width="5%"/>
                                <col width="3%"/>
                            </colgroup>
                            <thead>
                                <tr align='center'>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>CODE</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>품명</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>규격(1)</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>규격(2)</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>단위</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>수량</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>단가</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>금액</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>자재총소요량</td>
                                    <td colspan ='2' style="border-bottom-color: #000000;" bgcolor='lightblue'>비고</td>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <input type="text" class="form-control" id="code" name="code" value="{{ $v->code ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" id="name" name="name" value="{{ $v->name ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" id="standard1" name="standard1" value="{{ $v->standard1 ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" id="standard2" name="standard2" value="{{ $v->standard2 ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" id="type" name="type" value="{{ $v->type ?? '' }}">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" id="volume" name="volume" value="{{ $v->volume ?? 0 }}" onkeyup="countCheck();">
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" id="price" name="price" value="{{ $v->price ?? 0 }}" readonly>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" id="amount" name="amount" value="{{ $v->amount ?? 0 }}" readonly>
                                    </td>
                                    <td>
                                        <input type="text" class="form-control" id="sum_price" name="sum_price" value="{{ $v->material ?? '' }}">
                                    </td>
                                    <td colspan ='2'>
                                        <input type="text" class="form-control" id="etc" name="etc" value="{{ $v->etc ?? '' }}">
                                    </td>
                                </tr>
                            </tbody>
                            <thead>
                                <tr align='center'>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>CODE</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>품명</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>규격(1)</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>규격(2)</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>단위</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>수량</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>단가</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>금액</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>자재총소요량</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'>비고</td>
                                    <td style="border-bottom-color: #000000;" bgcolor='lightblue'></td>
                                </tr>
                            </thead>

                            <tbody id="inputTbody">
                                <!-- 추가된 항목들 -->
                                @php ( $scheduleCnt = $sum_money_return = $sum_origin_return = $sum_plan_origin = $sum_plan_interest = $sum_withholding_tax = $sum_income_tax = $sum_local_tax = $sum_interest_return = $sum_withholding_return = $sum_income_return = $sum_local_return = $sum_plan_money = 0 )
                                @foreach($cost_extra as $key => $val)
                                    @php ( $sum_origin_return += $val->plan_origin ?? 0 )   
                                    @php ( $sum_interest_return += $val->plan_interest ?? 0 )
                                    @php ( $sum_withholding_return += $val->withholding_tax ?? 0 )
                                    @php ( $sum_income_return += $val->income_tax ?? 0 )
                                    @php ( $sum_local_return += $val->local_tax ?? 0 )
                                    @php ( $sum_money_return += $val->plan_money ?? 0 )
                                    <tr>
                                        <td>
                                            <input type="text" class="form-control" id="code{{$key}}" name="code{{$key}}" value="{{ $val->code ?? '' }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="name{{$key}}" name="name{{$key}}" value="{{ $val->name ?? '' }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="standard1{{$key}}" name="standard1{{$key}}" value="{{ $val->standard1 ?? '' }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="standard2{{$key}}" name="standard2{{$key}}" value="{{ $val->standard2 ?? '' }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="type{{$key}}" name="type{{$key}}" value="{{ $val->type ?? '' }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="volume{{$key}}" name="volume{{$key}}" value="{{ $val->volume }}" onkeyup="countCheck({{$key}});">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="price{{$key}}" name="price{{$key}}" value="{{ $val->price }}" readonly>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="amount{{$key}}" name="amount{{$key}}" value="{{ $val->amount }}" readonly>
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="sum_price{{$key}}" name="sum_price{{$key}}" value="{{ $val->sum_price ?? '' }}">
                                        </td>
                                        <td>
                                            <input type="text" class="form-control" id="etc{{$key}}" name="etc{{$key}}" value="{{ $val->etc ?? '' }}">
                                        </td>
                                        <td>
                                            <div class="col-sm-5 m-0 pr-0">
                                                <button type="button" class="btn btn-default btn-sm float-center mr-2 addbtn" onclick="addRow(this);"><i class="fa fa-xs fa-plus-square text-info"></i></button>
                                            </div>
                                            <div class="col-sm-5 m-0 pr-0">
                                                <button type="button" class="btn btn-default btn-sm float-center mr-2 delbtn" onclick="delRow(this);"><i class="fa fa-xs fa-minus-square text-danger"></i></button>
                                            </div>
                                        </td>
                                    </tr>
                                    
                                    @php ( $scheduleCnt++ )
                                @endforeach

                                @if( $cost_extra )
                                <tr class="bg-secondary">
                                    <td class="text-center" id="td_sum"></td>
                                    @if( $v->return_method_cd == "F" || $v->pro_cd == "03" )
                                        <td class="text-center" colspan="4" >합계 [ 최종갱신 : {{ Func::dateFormat($v->save_time) }} ]</td>
                                    @else
                                        <td class="text-center" colspan="3" >합계 [ 최종갱신 : {{ Func::dateFormat($v->save_time) }} ]</td>
                                    @endif
                                    <td class="text-right" id="td_tot_plan_origin">{{ number_format($sum_plan_origin) }}</td>
                                    <td class="text-right" id="td_tot_plan_interest">{{ number_format($sum_plan_interest) }}</td>
                                    <td class="text-right" id="td_tot_withholding_tax">{{ number_format($sum_withholding_tax) }}</td>
                                    <td class="text-right" id="td_tot_income_tax">{{ number_format($sum_income_tax) }}</td>
                                    <td class="text-right" id="td_tot_local_tax">{{ number_format($sum_local_tax) }}</td>
                                    <td class="text-right" id="td_tot_plan_money">{{ number_format($sum_plan_money) }}</td>
                                    <td class="text-center"></td>
                                </tr>
                                <tr class="bg-secondary">

                                    <td class="text-center" id="td_money_sum"></td>
                                    @if( $v->return_method_cd == "F" || $v->pro_cd == "03" )
                                        <td class="text-center" colspan="4">수익지급 합계</td>
                                    @else
                                        <td class="text-center" colspan="3">수익지급 합계</td>
                                    @endif
                                    <td class="text-right">{{ number_format($sum_origin_return) }}</td>
                                    <td class="text-right">{{ number_format($sum_interest_return) }}</td>
                                    <td class="text-right">{{ number_format($sum_withholding_return) }}</td>
                                    <td class="text-right">{{ number_format($sum_income_return) }}</td>
                                    <td class="text-right">{{ number_format($sum_local_return) }}</td>
                                    <td class="text-right">{{ number_format($sum_money_return) }}</td>
                                    <td class="text-center"></td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>          
        </div>

        <!-- 검색 모달 -->
        <div class="modal fade" id="modalS" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="myModalLabel">자재단가표 코드</h4>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <form id="searchForm">
                            <div class="form-group">
                                <input type="text" class="form-control" name="codeSearch" id="codeSearch" placeholder="코드를 입력하세요">
                            </div>
                            <button type="button" class="btn btn-primary btn-sm" onclick="search();">검색</button>
                        </form>
                        <div id="list" class="mt-3" style="overflow-y:auto; max-height:300px;"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">닫기</button>
                    </div>
                </div>
            </div>
        </div>
    </form>
    </div>
</div>

@endsection


@section('javascript')
<script>

var scheduleCnt = 0;

$(document).ready(function()
{
    scheduleCnt = {{ $scheduleCnt ?? 0 }};
});

function setInput(cnt)
{
    if(cnt>0)
    {
        var get_targetOriginMoney = $("#inputTbody input[name^='plan_origin[]']");
        var get_targetInterestMoney = $("#inputTbody input[name^='plan_interest[]']");

        //세금 적용
        var invest_rate = $('#invest_rate').val();      //이자율
        var income_rate = $('#income_rate').val();     //소득세율
        var local_rate = $('#local_rate').val();      //지방소득세율

        //소득세
        var plan_interest = Number(get_targetInterestMoney.eq(cnt-1).val().replace(/,/gi,""));
        var income_tax = Math.floor(plan_interest * (income_rate / 100) / 10) * 10;
        $('#income_tax' + cnt).val(income_tax);
        var hiddenValue = $('#td_income_tax' + cnt + ' input[type="hidden"]').val(income_tax).clone(); 
        $('#td_income_tax' + cnt).html(income_tax.toLocaleString()).append(hiddenValue); 

        //지방소득세
        var local_tax = Math.floor(income_tax * (local_rate / 100) / 10) * 10;
        $('#local_tax' + cnt).val(local_tax);
        var hiddenValue = $('#td_local_tax' + cnt + ' input[type="hidden"]').val(local_tax).clone(); 
        $('#td_local_tax' + cnt).html(local_tax.toLocaleString()).append(hiddenValue);
        
        //원천징수
        var withholding_tax = income_tax + local_tax;
        $('#withholding_tax' + cnt).val(withholding_tax);
        var hiddenValue = $('#td_withholding_tax' + cnt + ' input[type="hidden"]').val(withholding_tax).clone(); 
        $('#td_withholding_tax' + cnt).html(withholding_tax.toLocaleString()).append(hiddenValue);

        //실지급액 조정    
        var plan_interest = Number(get_targetInterestMoney.eq(cnt-1).val().replace(/,/gi,""));
        var plan_origin = Number(get_targetOriginMoney.eq(cnt-1).val().replace(/,/gi,""));
        var plan_money = plan_origin + plan_interest - withholding_tax;

        $('#plan_money' + cnt).val(plan_money);
        var hiddenValue = $('#td_plan_money' + cnt + ' input[type="hidden"]').val(plan_money).clone(); 
        $('#td_plan_money' + cnt).html(plan_money.toLocaleString()).append(hiddenValue); 
    }

    var cal_plan_origin = cal_plan_interest = cal_withholding_tax = cal_income_tax = cal_local_tax = cal_plan_money = 0;
    
    // 원금
    var get_targetMoney = $("#inputTbody input[name^='plan_origin[]']");
    $.each(get_targetMoney, function(index, value){
        cal_plan_origin+=Number($(value).val().replace(/,/gi,""));
    });

    // 이자
    var get_targetMoney = $("#inputTbody input[name^='plan_interest[]']");
    $.each(get_targetMoney, function(index, value){
        cal_plan_interest+=Number($(value).val().replace(/,/gi,""));
    });

    // 원천징수
    var get_targetMoney = $("#inputTbody input[name^='withholding_tax[]']");
    $.each(get_targetMoney, function(index, value){
        cal_withholding_tax+=Number($(value).val().replace(/,/gi,""));
    });
    
    // 이자소득세
    var get_targetMoney = $("#inputTbody input[name^='income_tax[]']");
    $.each(get_targetMoney, function(index, value){
        cal_income_tax+=Number($(value).val().replace(/,/gi,""));
    });
    
    // 주민세
    var get_targetMoney = $("#inputTbody input[name^='local_tax[]']");
    $.each(get_targetMoney, function(index, value){
        cal_local_tax+=Number($(value).val().replace(/,/gi,""));
    });
    
    // 실지급액
    var get_targetMoney = $("#inputTbody input[name^='plan_money[]']");
    $.each(get_targetMoney, function(index, value){
        cal_plan_money+=Number($(value).val().replace(/,/gi,""));
    });

    //투자잔액 조정
    var loan_money = Number($('#loan_money').val().replace(/,/gi,""));
    var get_targetBalanceMoney = $("#inputTbody input[name^='plan_balance[]']");
    
    $.each(get_targetBalanceMoney, function(index, value) {
        var plan_origin_value = $('#plan_origin' + (index + 1)).val();
        var new_plan_balance = loan_money - Number(plan_origin_value.replace(/,/gi,""));
        $('#plan_balance' + (index + 1)).val(new_plan_balance);
        var hiddenValue = $('#td_plan_balance' + (index + 1) + ' input[type="hidden"]').val(new_plan_balance).clone(); 
        $('#td_plan_balance' + (index + 1)).html(new_plan_balance.toLocaleString()).append(hiddenValue);
        loan_money = new_plan_balance;       
    });

    //세금 적용
    var get_targetInterestMoney = $("#inputTbody input[name^='plan_interest[]']");
    var income_rate = $('#income_rate').val();     //소득세율
    var local_rate = $('#local_rate').val();      //지방소득세율

    $.each(get_targetInterestMoney, function(index, value) {
        //소득세
        var plan_interest = Number($('#plan_interest' + (index + 1)).val().replace(/,/gi,""));
        var income_tax = Math.floor(plan_interest * (income_rate / 100) / 10) * 10;
        $('#income_tax' + cnt).val(income_tax);
        var hiddenValue = $('#td_income_tax' +  (index + 1) + ' input[type="hidden"]').val(income_tax).clone(); 
        $('#td_income_tax' +  (index + 1)).html(income_tax.toLocaleString()).append(hiddenValue); 

        //지방소득세
        var local_tax = Math.floor(income_tax * (local_rate / 100) / 10) * 10;
        $('#local_tax' + (index + 1)).val(local_tax);
        var hiddenValue = $('#td_local_tax' + (index + 1) + ' input[type="hidden"]').val(local_tax).clone(); 
        $('#td_local_tax' + (index + 1)).html(local_tax.toLocaleString()).append(hiddenValue);

        //원천징수
        var withholding_tax = income_tax + local_tax;
        $('#withholding_tax' + (index + 1)).val(withholding_tax);
        var hiddenValue = $('#td_withholding_tax' + (index + 1) + ' input[type="hidden"]').val(withholding_tax).clone(); 
        $('#td_withholding_tax' + (index + 1)).html(withholding_tax.toLocaleString()).append(hiddenValue);

        //실지급액 조정    
        var plan_origin = Number($('#plan_origin' + (index + 1)).val().replace(/,/gi,""));
        var plan_money = plan_origin + plan_interest - withholding_tax;

        $('#plan_money' + (index + 1)).val(plan_money);
        var hiddenValue = $('#td_plan_money' + (index + 1) + ' input[type="hidden"]').val(plan_money).clone(); 
        $('#td_plan_money' + (index + 1)).html(plan_money.toLocaleString()).append(hiddenValue); 
    });
    
    // 합계 변경
    $('#td_tot_plan_origin').html(cal_plan_origin).number(true);
    $('#td_tot_plan_interest').html(cal_plan_interest).number(true);
    $('#td_tot_withholding_tax').html(cal_withholding_tax).number(true);
    $('#td_tot_income_tax').html(cal_income_tax).number(true);
    $('#td_tot_local_tax').html(cal_local_tax).number(true);
    $('#td_tot_plan_money').html(cal_plan_money).number(true);
}

// 행추가
function addRow(f)
{
    scheduleCnt++;

    let num = $(".addbtn").index(f);
    let tr = '<tr>';
        tr+= '<td class="text-center">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id="code'+scheduleCnt+'" name="code[]" value="">';
        tr+= '</td>';
        tr+= '<td class="text-center">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id="name'+scheduleCnt+'" name="name[]" value="">';
        tr+= '</td>';
        tr+= '<td class="text-center">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id="standard1'+scheduleCnt+'" name="standard1[]" value="">';
        tr+= '</td>';
        tr+= '<td class="text-center">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id="standard2'+scheduleCnt+'" name="standard2[]" value="">';
        tr+= '</td>';
        tr+= '<td class="text-center">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id="type'+scheduleCnt+'" name="type[]" value="">';
        tr+= '</td>';
        tr+= '<td class="text-right">';
        tr+= '<input type="text" class="form-control form-control-sm text-right moneyformat" id="volume'+scheduleCnt+'" name="volume[]" value="" onkeyup="setInput('+scheduleCnt+');">';
        tr+= '</td>';
        tr+= '<td class="text-right" id="td_price'+scheduleCnt+'">';
        tr+= '<input type="hidden" id="price'+scheduleCnt+'" name="price[]" value="">0';
        tr+= '</td>';
        tr+= '<td class="text-right" id="td_balance'+scheduleCnt+'">';
        tr+= '<input type="hidden" id="balance'+scheduleCnt+'" name="balance[]" value="">0';
        tr+= '</td>';
        tr+= '<td class="text-center">';
        tr+= '<input type="text" class="form-control form-control-sm text-center" id="etc'+scheduleCnt+'" name="etc[]" value="">';
        tr+= '</td>';
        tr+= '<div class="row">';
        tr+= '<div class="col-sm-5 m-0 pr-0">';
        tr+= '<button type="button" class="btn btn-default btn-sm float-center mr-2 addbtn" onclick="addRow(this);"><i class="fa fa-xs fa-plus-square text-info"></i></button>';
        tr+= '</div>';
        tr+= '<div class="col-sm-5 m-0 pr-0">';
        tr+= '<button type="button" class="btn btn-default btn-sm float-center mr-2 delbtn" onclick="delRow(this);"><i class="fa fa-xs fa-minus-square text-danger"></i></button>';
        tr+= '</div>';
        tr+= '</div>';
        tr+= '</td>';
        tr+= '</tr>';
    $("#inputTbody tr:eq("+num+")").after(tr);

    $("#inputTbody tr").each(function(index)
    {
        let newIndex = index + 1;

        if ($(this).find("td[id^='td_sum']").length > 0) {
            return true; 
        }
        
        if ($(this).find("td[id='td_money_sum']").length > 0) {
            return true; 
        }
        
        $(this).find("td:eq(0)").html(newIndex);
        $(this).find("input[name='code[]']").attr("id", "code" + newIndex);
        $(this).find("input[name='name[]']").attr("id", "name" + newIndex);
        $(this).find("input[name='standard1[]']").attr("id", "standard1" + newIndex);
        $(this).find("input[name='standard2[]']").attr("id", "standard2" + newIndex);
        $(this).find("input[name='type[]']").attr("id", "type" + newIndex);
        $(this).find("input[name='volume[]']").attr("id", "volume" + newIndex);
        $(this).find("input[name='volume[]']").attr("onkeyup", "setInput(" + newIndex+")");
        $(this).find("td[id^='td_price']").attr("id", "td_price" + newIndex);
        $(this).find("input[name='price[]']").attr("id", "price" + newIndex);
        $(this).find("td[id^='td_balance']").attr("id", "td_balance" + newIndex);
        $(this).find("input[name='balance[]']").attr("id", "balance" + newIndex);

        $(".addbtn").index();
    });
    
    setInputMask('class', 'moneyformat', 'money');
    setInput(0);
}

// 행삭제
function delRow(f)
{
    scheduleCnt--;
    let num = $(".delbtn").index(f);
    $("#inputTbody tr:eq("+num+")").remove();
    $(".delbtn").index();
    
    $("#inputTbody tr").each(function(index) {
        let newIndex = index + 1;

        if ($(this).find("td[id^='td_sum']").length > 0) {
            return true;
        }
        
        if ($(this).find("td[id='td_money_sum']").length > 0) {
            return true; 
        }

        $(this).find("td:eq(0)").html(newIndex);
        $(this).find("input[name='plan_date[]']").attr("id", "plan_date" + newIndex);
        $(this).find("input[name='plan_origin[]']").attr("id", "plan_origin" + newIndex);
        $(this).find("input[name='plan_origin[]']").attr("onkeyup", "setInput(" + newIndex+")");
        $(this).find("input[name='plan_interest[]']").attr("id", "plan_interest" + newIndex);
        $(this).find("input[name='plan_interest[]']").attr("onkeyup", "setInput(" + newIndex+")");
        $(this).find("td[id^='td_plan_balance']").attr("id", "td_plan_balance" + newIndex);
        $(this).find("input[name='plan_balance[]']").attr("id", "plan_balance" + newIndex);
        $(this).find("td[id^='td_withholding_tax']").attr("id", "td_withholding_tax" + newIndex);
        $(this).find("input[name='withholding_tax[]']").attr("id", "withholding_tax" + newIndex);
        $(this).find("td[id^='td_income_tax']").attr("id", "td_income_tax" + newIndex);
        $(this).find("input[name='income_tax[]']").attr("id", "income_tax" + newIndex);
        $(this).find("td[id^='td_local_tax']").attr("id", "td_local_tax" + newIndex);
        $(this).find("input[name='local_tax[]']").attr("id", "local_tax" + newIndex);
        $(this).find("td[id^='td_plan_money']").attr("id", "td_plan_money" + newIndex);
        $(this).find("input[name='plan_money[]']").attr("id", "plan_money" + newIndex);
    });
    
    setInput(0);
}

function plusCode() {
    var row = '<tr>' +
                '<td><input type="text" class="form-control form-control-sm text-center" id="code' + total + '" name="code' + total + '" readonly></td>' +
                '<td><input type="text" class="form-control form-control-sm" id="name' + total + '" name="name' + total + '"></td>' +
                '<td><input type="text" class="form-control form-control-sm" id="standard1' + total + '" name="standard1' + total + '"></td>' +
                '<td><input type="text" class="form-control form-control-sm" id="standard2' + total + '" name="standard2' + total + '"></td>' +
                '<td><input type="text" class="form-control form-control-sm" id="type' + total + '" name="type' + total + '"></td>' +
                '<td><input type="text" class="form-control form-control-sm text-right" id="volume' + total + '" name="volume' + total + '" onkeyup="countCheck(' + total + ');"></td>' +
                '<td><input type="text" class="form-control form-control-sm text-right" id="price' + total + '" name="price' + total + '" readonly></td>' +
                '<td><input type="text" class="form-control form-control-sm text-right" id="amount' + total + '" name="amount' + total + '" readonly></td>' +
                '<td><input type="text" class="form-control form-control-sm text-right" id="sum_price' + total + '" name="sum_price' + total + '"></td>' +
                '<td><input type="text" class="form-control form-control-sm" id="etc' + total + '" name="etc' + total + '"></td>' +
            '</tr>';

    $('#trCheck').append(row);
    total++;
}

function removeCode() {
    if (total > 0) {
        total--;
        $('#trCheck tr:last').remove();
    }
}

function countCheck(index) {
    var volume = parseFloat($('#volume' + index).val());
    var price = parseFloat($('#price' + index).val());
    var amount = volume * price;

    $('#amount' + index).val(amount.toFixed(2));
}

function code_search(index) {
    $('#modalS').modal('show');
    $('#codeSearch').val($('#code' + index).val());
}

function search() {
    var codeSearch = $('#codeSearch').val();

    $.ajax({
        type: 'POST',
        url: '/management/unitcostactionplus',
        data: { action: 'search', codeSearch: codeSearch },
        success: function(data) {
            makeList(data);
        }
    });
}

function makeList(data) {
    var html = '';

    if (data.length > 0) {
        for (var i = 0; i < data.length; i++) {
            html += '<a href="#" class="list-group-item list-group-item-action" onclick="selectCode(' + data[i].code + ');">' + data[i].name + '</a>';
        }
    } else {
        html += '<div class="alert alert-warning">검색 결과가 없습니다.</div>';
    }

    $('#list').html(html);
}

function selectCode(code) {
    $('#codeSearch').val(code);
    $('#modalS').modal('hide');
}

setInputMask('class', 'moneyformat', 'money');

// 검색모달
function searchCustInfo()
{
    var cust_search_string = $("#cust_search_string").val();
    if( cust_search_string=="" )
    {
        alert("검색어를 입력해주세요.");
        $("#cust_search_string").focus();
        return false;
    }
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
    $("#custSearchResult").html(loadingStringtxt);
    $("#custSearch").collapse('show');
    $.post("/erp/loanmngcustsearch", {cust_search_string:cust_search_string}, function(data) {
        $("#custSearchResult").html(data);
    });
}

// 현장등록
function confirmSave(div)
{
    if(div == 'UPD')
    {
        // 입력값 확인
        var code = $('#code').val();
        var name = $('#name').val();

        if(code == '')
        {
            alert('코드를 입력해주세요.');
            return false;
        }
        if(name == '')
        {
            alert('현장명을 입력해주세요.');
            return false;
        }
    }
    else
    {
        if(!confirm('정말 삭제하시겠습니까?'))
        {
            return false;
        }
    }

    var postdata = $('#management_form').serialize();
    postdata = postdata + '&mode=' + div;

    // 중복클릭 방지
    if(ccCheck()) return;

    $.ajax({
        url  : "/field/managementinfoaction",
        type : "post",
        data : postdata,
        success : function(data)
        {
            if(data['rs_code'] == "Y") 
            {
                globalCheck = false;
                alert(data['result_msg']);

                if(div == 'UPD')
                {
                    document.location.href = "/field/managementpop?no="+$('#contract_info_no').val();
                }
                else
                {
                    window.opener.listRefresh();
                    self.close();
                }
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
        }
    });
}

</script>
@endsection