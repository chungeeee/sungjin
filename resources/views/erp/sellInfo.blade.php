@extends('layouts.masterPop')
@section('content')
<title>매각결재정보</title>


<div class="card card-lightblue">
    <div class="card-header-no-round">
    <h2 class="card-title font-weight-bold">매각결재정보</h2>
    </div>
</div>
<form id="sellForm">
    <div class="row p-2">
{{-- <table class="table table-sm table-hover loan-info-table card-secondary card-outline"> --}}


    <div class="col-sm-6">
        <table class="table table-sm card-secondary card-outline table-bordered loan-info-table " height="280px">
        <input type="hidden" name="sell_no" value="{{ $v->no ?? '' }}">
        <input type="hidden" name="status" id="status">
            <colgroup>
                <col width="15%"/>
                <col width="10%"/>
                <col width="15%"/>
                <col width="10%"/>
                <col width="15%"/>
                <col width="10%"/>
                <col width="15%"/>
                <col width="10%"/>
            </colgroup>
            <tbody>
                <tr>
                    <th class="text-center">계약번호/<br>매각대금</th> 
                    <td colspan=7>
                    <input type="hidden" name="old_loan_info_nos" @if(isset($v->loan_info_nos)) value="{{ isset($v->loan_info_nos)?$v->loan_info_nos:"" }}" @endif>
                    <input type="hidden" id="old_lin_sm" @if(isset($v->lin_sm)) value="{{ isset($v->lin_sm)?$v->lin_sm:"" }}" @endif>
                    <textarea class="form-control form-control-sm" name="lin_sm" id="lin_sm" rows="9" @if(isset($v->status) && $v->status !="A") readonly @endif onkeyup="$('#status_check').val(null);">{{ isset($v->lin_sm)?$v->lin_sm:"" }}</textarea>
                    <a type="button" class='pt-2' data-container="body" data-toggle="popover" data-html="true" data-placement="right" data-content="<img src='/img/sample_sellinfo.jpg'>">
                    엑셀파일 계약번호와 매각금액을 복사해서 붙여넣기 해주세요. (예시)
                    </a>

                </td>
                </tr>
                <tr>
                    <th class="align-middle text-center">입력 계약건수</th> 
                    <td class="text-center align-middle comma" id="loan_info_nos_cnt"></td>
                    <th class="align-middle text-center">입력계약 잔액 합계</th> 
                    <td class="text-right align-middle comma" id="total_balance"></td>
                    <th class="align-middle text-center">입력계약 이자 합계</th> 
                    <td class="text-right align-middle comma" id="total_interest_sum"></td>
                    <th class="align-middle text-center">입력계약 비용 합계</th> 
                    <td class="text-right align-middle comma" id="total_cost_money"></td>
                </tr> 
                @if(!isset($v->status) || $v->status != "Y") 
                <tr>
                    <th class="align-middle text-center">가능 계약건수</th> 
                    <td class="text-center align-middle comma" id="ok_loan_info_nos_cnt"></td>
                    <th class="align-middle text-center">가능계약 잔액 합계</th> 
                    <td class="text-right align-middle comma" id="ok_total_balance"></td>
                    <th class="align-middle text-center">가능계약 이자 합계</th> 
                    <td class="text-right align-middle comma" id="ok_total_interest_sum"></td>
                    <th class="align-middle text-center">가능계약 비용 합계</th> 
                    <td class="text-right align-middle comma" id="ok_total_cost_money"></td>
                </tr>
                @endif
                @if(isset($v->status) && $v->status == "Y")
                <tr> 
                    <th class="align-middle">결재 계약건수</th> 
                    <td class="text-center align-middle comma" >{{  isset($v->confirm_cnt)?$v->confirm_cnt:"" }}</td>
                    <th class="align-middle">결재시 잔액 합</th> 
                    <td class="text-right align-middle comma" >{{  isset($v->confirm_balance)?$v->confirm_balance:"" }}</td>
                    <th class="align-middle">결재시 이자 합</th> 
                    <td class="text-right align-middle comma" >{{  isset($v->confirm_interest_sum)?$v->confirm_interest_sum:"" }}</td>
                    <th class="align-middle">결재시 비용 합</th> 
                    <td class="text-right align-middle comma" >{{  isset($v->confirm_cost_money)?$v->confirm_cost_money:"" }}</td>
                </tr> 
                @endif
            </tbody>
        </table>
    </div>

    <div class="col-sm-6">
        <table class="table table-sm card-secondary card-outline table-bordered loan-info-table " height="280px">
        <colgroup>
            <col width="10%"/>
            <col width="23%"/>
            <col width="10%"/>
            <col width="23%"/>
        </colgroup>
        <tbody>

            <tr>
                <th class="align-middle text-center">매각사</th> 
                <td class="align-middle pt-0 pb-0">
                    <input class="form-control from-control-sm p-2" name="sell_corp" value="{{ $v->sell_corp ?? '' }}">
                </td>
                <th class="p-2 text-center" rowspan=8>메모</th> 
                <td rowspan=8>
                <textarea class="form-control form-control-sm text-xs p-2" name="memo" rows="15">{{  isset($v->memo)?$v->memo:"" }}</textarea>
                </td>
            </tr>
            <tr>
                <th class="align-middle text-center">매각사유</th> 
                <td class="align-middle pt-0 pb-0">
                    <select class="form-control from-control-sm" name="sell_reason_cd" id="sell_reason_cd" >
                    <option value=''>선택</option>
                    {{ Func::printOption($array_sell_reason,isset($v->sell_reason_cd)?$v->sell_reason_cd:'') }}   
                    </select>
                </td>   
            </tr>
            <tr>
                <th class="align-middle text-center">매각금액처리</th> 
                <td class="align-middle pt-0 pb-0">
                    <select class="form-control from-control-sm" name="sell_money_proc" id="sell_money_proc" >
                    <option value=''>선택</option>
                    {{ Func::printOption(Vars::$arraySellMoneyProc,isset($v->sell_money_proc)?$v->sell_money_proc:'') }}   
                    </select>
                </td>   
            </tr>
            <tr>
                <th class="align-middle text-center">자산확정일</th> 
                <td class="align-middle pt-0 pb-0">
                    <input type="hidden" name="old_asset_decision_date" id="old_asset_decision_date" value="{{ $v->asset_decision_date ?? '' }}" DateOnly="true" size="6">
                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1" id="asset_decision_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#asset_decision_date" name="asset_decision_date" id="now_asset_decision_date" value="{{ $v->asset_decision_date ?? '' }}" DateOnly="true" size="6">
                        <div class="input-group-append" data-target="#asset_decision_date" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="align-middle text-center">매각예정일</th> 
                <td class="align-middle pt-0 pb-0">
                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1" id="sell_expected_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#sell_expected_date" name="sell_expected_date" id="sell_expected_date" value="{{ $v->sell_expected_date ?? '' }}" DateOnly="true" size="6">
                        <div class="input-group-append" data-target="#sell_expected_date" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="align-middle text-center">환매종료일</th> 
                <td class="align-middle pt-0 pb-0">
                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1" id="rebuy_end_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#rebuy_end_date" name="rebuy_end_date" id="rebuy_end_date" value="{{ $v->rebuy_end_date ?? '' }}" DateOnly="true" size="6">
                        <div class="input-group-append" data-target="#rebuy_end_date" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="align-middle text-center">결재요청일</th> 
                <td class="align-middle pl-2">
                    {{ isset($v->app_time)?Func::dateFormat($v->app_time):"" }}{{ isset($v->app_id)?" - ".Func::getArrayName($array_user_id,$v->app_id):"" }}
                </td>
            </tr>

            <tr>
            @if(isset($v->status) && $v->status == "N")
                <th class="align-middle text-center">결재취소일</th> 
                <td class="align-middle pl-2">{{ isset($v->cancel_time)?Func::dateFormat($v->cancel_time):"" }}{{ isset($v->cancel_id)?" - ".Func::getArrayName($array_user_id,$v->cancel_id):"" }}</th>
            @else
                <th class="align-middle text-center">결재완료일</th> 
                <td class="align-middle pl-2">{{ isset($v->confirm_time)?Func::dateFormat($v->confirm_time):"" }}{{ isset($v->confirm_id)?" - ".Func::getArrayName($array_user_id,$v->confirm_id):"" }}</th>
            @endif
            </tr>

        </tbody>
        </table>
        </div>
        @if(isset($v->status))
        <b class="pl-2 pb-1">매각대상 계약정보</b>
        <div class="col-sm-12 table-responsive" style="height:250px;">
            <table class="table table-sm card-secondary card-outline  table-head-fixed text-nowrap loan-info-table table-hover" id="loan_sell_table">
                <colgroup>
                    <col width="16%"> 
                    <col width="16%"> 
                    <col width="16%"> 
                    <col width="16%"> 
                    <col width="16%"> 
                    <col width="16%"> 
                </colgroup>
                <thead>
                    <tr>
                    <th class="text-center">계약번호</th> 
                    <th class="text-center">상태</th> 
                    <th class="text-center">잔액</th> 
                    <th class="text-center">이자합계</th> 
                    <th class="text-center">비용</th> 
                    <th class="text-center">매각대금</th> 
                    </tr>
                </thead>
                <tbody>
                @forelse($l_v as $idx => $lv)
                    @if($idx != 0 )
                        <tr>
                        <td class="text-center">{{ $lv->loan_info_no }}</td>
                        <td class="text-center">{!! Vars::$arrayContractStaColor[$lv->status] !!}</td>
                        <td class="text-right comma">{{ $lv->balance }}</td>
                        <td class="text-right comma">{{ $lv->interest_sum }}</td>
                        <td class="text-right comma">{{ $lv->cost_money }}</td>
                        <td class="text-right comma pr-4">{{ $lv->sell_money }}</td>
                        </tr>
                    @endif
                @empty
                @endforelse
                </tbody>
                <tfoot>
                @if(!empty($l_v[0]))
                    <tr class="text-bold">
                        <th class="text-center"></th>
                        <th class="text-center"></th>
                        <th class="text-right comma">{{ $l_v[0]->balance }}</th>
                        <th class="text-right comma">{{ $l_v[0]->interest_sum }}</th>
                        <th class="text-right comma">{{ $l_v[0]->cost_money }}</th>
                        <th class="text-right comma pr-4">{{ $l_v[0]->sell_money }}</th>
                    </tr>
                @endif
                </tfoot>
            </table>
        </div>
        @endif

        <b class='pt-3 pl-2 pb-1'>등록불가 계약정보</b>
        <div class="col-sm-12 table-responsive" style="height:250px">
            <table class='table table-sm card-secondary card-outline loan-info-table table-head-fixed text-nowrap table-hover' id='loan_info_table'>
            <colgroup>
            <col width='16%'/>
            <col width='16%'/>
            <col width='16%'/>
            <col width='16%'/>
            <col width='16%'/>
            <col width='16%'/>
            </colgroup>
            <thead>
            <th class='text-center'>계약번호</th>
            <th class='text-center'>상태</th>
            <th class='text-center'>연체일</th>
            <th class='text-center'>잔액</th>
            <th class='text-center'>이자합계</th>
            <th class='text-center'>구분</th>
            </thead>
            <tbody id="loan_info_check" >
            </tbody>
            <tfoot>
            <tr><td colspan='6'></td></tr>
            </tfoot>
            </table>
        </div>
    </div>
    <div class="card-footer" style="min-height:50px" >
        {{-- 매각결재처리 권한 --}}
        @if(empty($v->status))
            <button type="button" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="sellAction('A')">매각요청 등록</button>
        @elseif($v->status =="A")
            @if( Func::funcCheckPermit("C080") )
            <button type="button" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="sellAction('Y')">결재</button>
            @endif
            <button type="button" class="btn btn-danger btn-sm float-right ml-1 mb-1" onclick="sellAction('N')">취소</button>
            <button type="button" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="sellAction('AA')">수정</button>
        @elseif($v->status =="Y")
            @if( Func::funcCheckPermit("C080") )
            <button type="button" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="sellSmsAction({{$v->no}})">매각안내문자발송</button>
            @endif
        @endif
        @if(empty($v->status) || (isset($v->status) && $v->status == 'A'))
        <button type="button" class="btn btn-sm bg-green float-right ml-1" onclick="sellPreview('{{ isset($v->status)?$v->status:'0' }}')">미리보기</button>
        @endif
        <input type="hidden" id="status_check">
        <div id="preview_msg_N"></div>
    </div>
</form>

@endsection

@section('javascript')

<script>
function sellAction(status)
{
    if(checkValue(status) == true)
    {
        if( status=="A" && !confirm("매각요청을 등록하시겠습니까?") )
        {
            return false;
        }
        if( status=="AA" && !confirm("매각요청정보를 수정하시겠습니까?") )
        {
            return false;
        }
        if( status=="N" && !confirm("매각요청을 취소하시겠습니까?") )
        {
            return false;
        }
        if( status=="Y" && !confirm("매각요청을 결재하시겠습니까?") )
        {
            return false;
        }


        $('#status').val(status);
        if(ccCheck()) return;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#sellForm').serialize();

        $.post(
            "/erp/sellaction", 
            postdata, 
            function(result) {

                alert(result.msg);  
                globalCheck = false;

                if(result.rs =="Y" && status == "AA")
                {
                    location.reload();
                }
                else if(result.rs =="Y" )
                {
                    opener.document.location.reload();
                    self.close();
                }
        });
    }
}

function sellSmsAction(sell_no)
{

    if(!confirm("SMS발송은 취소하실 수 없습니다.\n\nSMS 발송을 하시겠습니까?") )
    {
        return false;
    }

    if(ccCheck()) return;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = "sell_no=" + sell_no;

    $.post(
        "/erp/sellsmsaction", 
        postdata, 
        function(result) {

            alert(result.msg);  
            globalCheck = false;

            if(result.rs =="Y" && status == "AA")
            {
                location.reload();
            }
            else if(result.rs =="Y" )
            {
                opener.document.location.reload();
                self.close();
            }
    });
    
}


function checkValue(status)
{
    if($('#status_check').val()=="N")
    {
        alert("등록불가 계약이 존재합니다.");
        return false;
    }
    if( $("#lin_sm").val()=="" )
    {
        alert("계약번호를 입력해주세요");
        $("#lin_sm").focus();
        return false;
    }
    if( status!="N" && !$('#status_check').val() )
    {
        alert("미리보기를 실행하여 입력건수,입력금액을 확인해주세요.");
        return false;
    }
    if(!$('#now_asset_decision_date').val())
    {
        alert("자산확정일을 입력해주세요");
        return false;
    }
    if((status=="N" ||  status=="Y") && $('#old_lin_sm').val() != $('#lin_sm').val()) 
    {
        alert("계약번호/매각대금 내용이 변경되었습니다. \n수정버튼을 통해 저장 후 이용해주세요");
        return false;
    }
    if((status=="N" ||  status=="Y") && $('#old_asset_decision_date').val() != $('#now_asset_decision_date').val().replaceAll("-","")) 
    {
        alert("자산확정일이 변경되었습니다. \n수정버튼을 통해 저장 후 이용해주세요");
        return false;
    }
    if(status=="AA" && $('#sell_moneys').val())
    {
        var lis_length = $('#loan_info_nos').val().split("\n").length
        var sm_length = $('#sell_moneys').val().split("\n").length
        if(lis_length != sm_length)
        {
            alert("계약번호와 매각대금의 데이터가 일치하지 않습니다. \n계약번호에 맞게 매각대금 값을 입력해주세요");
            return false;
        }
    }
    return true;
}

if("{{ isset($v->status)?$v->status:'' }}"=="A")
{
    sellPreview("A");
}
else
{
    $('#loan_info_check').html("<tr><td colspan='6' class='text-center p-4'><span class='text-bold pt-1 pr-1'><i class='fas fa-user m-2'></i>등록불가 계약이 없습니다.</span></td></tr>");
}


function sellPreview(status)
{
    if( $("#lin_sm").val()=="" )
    {
        alert("계약번호를 입력해주세요");
        $("#lin_sm").focus();
        return false;
    }
    
    if(ccCheck()) return;

    $("#loan_info_check").html(loadingString);   

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $('#status').val(status);
    var postdata = $('#sellForm').serialize();

    $("#loan_info_check").html("<tr><td colspan='6'><div style='text-align:center;padding:40px;'><i class='fas fa-asterisk fa-spin text-orange mr-1'></i> Loading...</div></td></tr>");   
    $('#preview_msg_N').empty();
    $.post(
        "/erp/sellpreview", 
        postdata, 
        function(result) {
            $('#status_check').val(result.status_check);
            if($('#status_check').val()=="N")
            {
                $('#loan_info_check').html(result.loan_info_table);
                $('#preview_msg_N').html("<span class='float-right text-bold pt-1 pr-1 text-red'>* "+result.loan_info_nos_cnt+"건 중 "+result.n_cnt+"건 등록불가 </span>");
            }
            else
            {
                $('#loan_info_check').html("<tr><td colspan='6' class='text-center p-4'><span class='text-bold pt-1 pr-1'><i class='fas fa-user m-2'></i>등록불가 계약이 없습니다.</span></td></tr>");
            }

            $('#loan_info_nos_cnt').html(result.loan_info_nos_cnt);
            $('#total_balance').html(result.total_balance);
            $('#total_interest_sum').html(result.total_interest_sum);
            $('#total_cost_money').html(result.total_cost_money);
            $('#ok_loan_info_nos_cnt').html(result.ok_loan_info_nos_cnt);
            $('#ok_total_balance').html(result.ok_total_balance);
            $('#ok_total_interest_sum').html(result.ok_total_interest_sum);
            $('#total_cost_money').html(result.ok_total_cost_money);
            afterAjax();
            globalCheck = false;
    });
}
setInputMask('class', 'moneyformat', 'money');


$(function () {
        // Enables popover
        $("[data-toggle=popover]").popover();
    });
</script>

@endsection
