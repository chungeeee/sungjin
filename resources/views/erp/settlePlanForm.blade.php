@extends('layouts.masterPop')
@section('content')
<form id="settle_form" name="settle_form" onSubmit="return false;" method="POST">
<div class="col-12">
    <div class="card">
        @csrf
        <div class="card-header">
            <h3 class="card-title"><i class="far fa-edit"></i>스케줄수정</h3>
        </div>
        <input type="hidden" name="action_mode" id="action_mode" vlaue="">
        <input type="hidden" name="loan_info_no" id="loan_info_no" vlaue="{{ $settle->loan_info_no ?? '' }}">
        <!-- /.card-header -->
        <div class="card-body table-responsive p-0" style="height: 500px;">
            <table class="table table-head-fixed text-nowrap table-bordered table-sm">
                <thead>
                    <tr class="text-center " >
                        <th width="10%">회차</th>
                        <th width="20%">입금약속일</th>
                        <th width="12%">원금</th>
                        <th width="12%">이자</th>
                        <th width="15%">입금약속액</th>
                        <th width="15%">처리</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($plan_data as $p)
                        @if($p->status =='Y')
                        <tr class="text-center">
                            <td><input class="text-center" type="text" name="seq[]" size=5 value="{{ $p->seq ?? '' }}" readonly></td>
                            <td><div class="col-md-12  pl-0">
                                    <div class="input-group date datetimepicker " id="div_plan_date_{{$p->seq}}" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm" id="plan_date_{{$p->seq}}" name="plan_date[{{$p->seq}}]" DateOnly='true' value="{{ Func::dateFormat($p->plan_date) ?? ''}}"/>
                                        <div class="input-group-append" data-target="#div_plan_date_{{$p->seq}}" data-toggle="datetimepicker">
                                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td><input class="text-right border-0 plan_origin" type="text" id="plan_origin_{{$p->seq}}" size=12 name="plan_origin[{{$p->seq}}]"  value="{{ number_format($p->plan_origin) ?? '' }}" onchange="onlyNumber(this);inputComma(this);">원</td>
                            <td><input class="text-right border-0 plan_interest" type="text" id="plan_interest_{{$p->seq}}" name="plan_interest[{{$p->seq}}]" size=12  value="{{ number_format($p->plan_interest) ?? ''}}" onchange="onlyNumber(this);inputComma(this);" >원</td>
                            <td><input class="text-right border-0 plan_money" type="text" id="plan_money_{{$p->seq}}" name="plan_money[{{$p->seq}}]"  size=12 value="{{ number_format($p->plan_money) ?? '' }}" onchange="onlyNumber(this);inputComma(this);sumSchedule({{$p->seq}});" >원</td>
                            <td><input class="text-right border-0 trade_money" type="text" id="trade_money_{{$p->seq}}" name="trade_money[{{$p->seq}}]"  size=12 value="{{ number_format($p->trade_money) ?? '' }}" readonly >원</td>
                        @else
                        <tr class="text-center " style="background-color: #bbbbbb;" >
                            <td>{{ $p->seq ?? '' }}</td>
                            <td>{{ Func::dateFormat($p->plan_date) ?? ''}}</td>
                            <td>{{ number_format($p->plan_origin) ?? '' }} 원</td>
                            <td>{{ number_format($p->plan_interest) ?? ''}} 원</td>
                            <td><input class="form-control form-control-xs text-right border-0 plan_money" type="text" id="plan_money_{{$p->seq}}" name="plan_money[{{$p->seq}}]"  size=12 value="{{ number_format($p->plan_money) ?? '' }}" readonly ></td>
                            <td>{{ number_format($p->trade_money) ?? '' }} 원</td>
                        @endif
                        </tr>    
                    @endforeach
                </tbody>
            </table>
        </div>
        <!-- /.card-body -->
        <div class="card-footer">
            <table class="table table-sm table-borderless">
                <tbody>
                    <tr>
                        <th class="text-right">분납회차</th>
                        <th><input type="text" class="form-control form-control-sm border-0" id="settle_cnt" name="settle_cnt" size=3 value="{{ $settle->settle_cnt ?? ''}}" readonly></th>
                        <th class="text-right">화해잔액금액</th>
                        <th class="text-right"><input type="text" class="form-control form-control-sm border-0" id="settle_money" name="settle_money" value="{{ number_format($settle->settle_money) ?? '' }}" readonly></th>
                        <th class="text-right">총액 :</th>
                        <th><input type="text" class="form-control form-control-sm border-0" id="total_mny" name="total_mny" value="" readonly></th>
                        <th class="text-right"><button id="deposit_btn" class="btn btn-success btn-xs"  onclick="settlePlanAction('exec')">스케줄수정</button></th>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
</form>
@endsection

@section('javascript')
<!-- Summernote -->
<script>
    $(document).ready(function(){
        window.resizeTo( 800, 710 );
        sumSchedule(0);
    });
    //일괄입금내역 가져오기
    function settlePlanAction(action)
    {
        var totalMny = filterNum($('#total_mny').val())*1;
        var settleMny = filterNum($('#settle_money').val())*1;
        if(totalMny!=settleMny){
            alert("화해금액과 맞지 않습니다.");
            return false;
        }
        $('#action_mode').val(action);
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        var form        = $('#settle_form')[0];
        var postdata    = new FormData(form);
        $.ajax({
        url  : "/erp/settleplanform/{{$settle->no ?? ''}}",
        type : "post",
        data : postdata,
        processData: false,
        contentType: false,
        dataType: "json",
        success : function(data)
        {   
            console.log(data);
            alert(data.rs_msg);
        },
        error : function(xhr)
        {
            alert(result);
        }
        });
    }
    
    //스케줄합계 구하기
    function sumSchedule(seq){
        
        if(seq>0){
            var tradeMny = filterNum($('#trade_money_'+seq).val())*1;
            var planMny = filterNum($('#plan_money_'+seq).val())*1;
            if(tradeMny>planMny) alert("입금약속액은 처리금액보다 높게 설정해주세요.");
        }
        var total = 0;
        $('.plan_money').each(function(){ //클래스가 money인 항목의 갯수만큼 진행
            total += Number(filterNum($(this).val())); 
        });
        $('#total_mny').val(commaSplitAndNumberOnly(total));
    }

    // $ 와 콤마 제거 함수
    function inputComma(obj){
        obj.value   = commaSplitAndNumberOnly(filterNum(obj.value));
    }

    function filterNum(str)
    {
        str = String(str);
        re = /^\$|,/g;
        return str.replace(re, "");
    }

    function commaSplitAndNumberOnly(str)
    {
        str = String(str*1);
        return  str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
    }
</script>
 @endsection
