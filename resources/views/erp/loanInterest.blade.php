
<div class="p-2">



@include('inc/loanSimpleLine')



<div class="col-12 row mr-0 mt-1">
    <div class="col-6 row">
    <div class="form-group pl-2">
        <div class="input-group mt-0 mb-0 date datetimepicker" id="interestDateStringStart" data-target-input="nearest">
            <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#interestDateStringStart" name="interest_today" id="interest_today" DateOnly="true" value="{{ Func::dateFormat($today) }}" size="8">
            <div class="input-group-append" data-target="#interestDateStringStart" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
            </div>
        </div>
    </div>
    <div class="pl-1">
    <button type="button" class="btn btn-sm btn-info float-right mr-2" id="pop_int_btn" onclick="getLoanData('loaninterest', $('#interest_today').val());">이자조회</button>
    </div>
    </div>

    <div class='text-right col-6 pr-0 m-0'>
        <span class="mr-3 pt-1"><i class="fa fa-xs fa-won-sign text-gray"></i> 하루이자 : {{ number_format(floor( $simple['balance'] * $simple['loan_rate'] / 100 / ( ( (substr($today,0,4)%4)==0 && substr($today,0,4)>="2012" ) ? 366 : 365 ) )) }}원</span>
        <button type="button" class="btn btn-sm btn-primary" id="cate_btn" onclick="loanReturnPreview('{{ $no }}', $('#interest_today').val());">예상입금조회</button>
    </div>
</div>



<!-- BODY -->
<table class="table table-sm table-hover card-secondary card-outline mt-1">
    <thead>

        <tr>
            <th class="text-center">일자</th>
            <th class="text-center">경과</th>

            @if ( $loan->loanInfo['return_method_cd']!='F' )
            <th class="text-center">스케줄입금</th>
            <th class="text-center">스케줄이자</th>
            <th class="text-center">스케줄원금</th>
            @endif

            <th class="text-center">연체이자</th>
            <th class="text-center">정상이자</th>
            <th class="text-center">이자합계</th>
            <th class="text-center">잔액</th>

            <th class="text-center">조기상환</th>
            <th class="text-center">총금액</th>
        </tr>

    </thead>
    <tbody>
    @if( $today >= $loan->loanInfo['take_date'] )

        @forelse( $result as $v )
        <tr @if ( $loan->loanInfo['return_method_cd']!='F' ) onclick="viewInterestDetail('{{ $v['today'] }}');" role="button" @endif {{ $v['today']==date('Ymd') ? "class=bg-secondary" : '' }}>

            <td class="text-center {{ $v['holiday'] ? 'text-red' : '' }}">{{ Func::dateFormat($v['today']) }} ({{ Vars::$arrayWeekDay[$v['weekday']] }})</td>
            <td class="text-center">{{ Loan::dateTerm($v['take_date'], $v['today']) }}</td>

            @if ( $loan->loanInfo['return_method_cd']!='F' )
            <td class="text-right">{{ number_format($v['plan_money']) }}</td>
            <td class="text-right">{{ number_format($v['plan_interest']) }}</td>
            <td class="text-right">{{ number_format($v['plan_origin']) }}</td>
            @endif

            <td class="text-right">{{ number_format($v['delay_interest']) }}</td>
            <td class="text-right">{{ number_format($v['interest']) }}</td>

            <td class="text-right">{{ number_format($v['interest_sum']) }}</td>
            <td class="text-right">{{ number_format($v['balance']) }}</td>

            <td class="text-right {{ ( $v['return_fee']>0 && $v['return_fee']==$v['return_fee_max'] ) ? 'text-red' : '' }}">{{ number_format($v['return_fee']) }}</td>
            <td class="text-right">{{ number_format($v['fullpay_money']) }}</td>
        </tr>

        @if ( isset($v['return_plan']) && sizeof($v['return_plan'])>0 )
        <tr class="collapse" id="loanInterestDetail{{ $v['today'] }}">
            <td class="text-center p-3" colspan=20 style="background:#FFFFFF;">
                <table class="table table-xs card-primary card-outline table-bordered">
                    <thead>
                    <tr>
                        <th class="text-center">스케줄일자</th>
                        <th class="text-center">스케줄입금</th>
                        <th class="text-center">스케줄이자</th>
                        <th class="text-center">스케줄원금</th>
                        <th class="text-center">스케줄잔액</th>
                        <th class="text-center">정상일수</th>
                        <th class="text-center">정상이자</th>
                        <th class="text-center">연체일수</th>
                        <th class="text-center">지연배상금</th>
                        <th class="text-center">연체이자</th>
                        <th class="text-center">이자합계</th>
                        <th class="text-center">청구금액</th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach( $v['return_plan'] as $dt => $v2 )      
                    <tr>
                        <td class="text-center">{{ ( is_numeric($dt) ) ? Func::dateFormat($dt)  : '= 미청구 =' }}</td>
                        <td class="text-right text-blue">{{ isset($v2['plan_money'])    ? number_format($v2['plan_money'])    : '' }}</td>
                        <td class="text-right">{{ isset($v2['plan_interest']) ? number_format($v2['plan_interest']) : '' }}</td>
                        <td class="text-right">{{ isset($v2['plan_origin'])   ? number_format($v2['plan_origin'])   : '' }}</td>
                        <td class="text-right">{{ isset($v2['plan_balance']) ? number_format($v2['plan_balance']) : '' }}</td>

                        <td class="text-center">{{ isset($v2['interest_term']) ? number_format($v2['interest_term'])." ( ".Func::dateFormat($v2['interest_sdate'])." ~ ".Func::dateFormat($v2['interest_edate'])." )" : '' }}</td>
                        <td class="text-right" >{{ isset($v2['interest'])      ? number_format($v2['interest']) : '' }}</td>

                        @if ( $v2['delay_money']>0 )
                        <td class="text-center">{{ isset($v2['delay_money_term']) ? number_format($v2['delay_money_term'])." ( ".Func::dateFormat($v2['delay_money_sdate'])." ~ ".Func::dateFormat($v2['delay_money_edate'])." )" : '' }}</td>
                        @else
                        <td class="text-center">{{ isset($v2['delay_interest_term']) ? number_format($v2['delay_interest_term'])." ( ".Func::dateFormat($v2['delay_interest_sdate'])." ~ ".Func::dateFormat($v2['delay_interest_edate'])." )" : '' }}</td>
                        @endif
                        <td class="text-right text-red">{{ isset($v2['delay_money'])    ? number_format($v2['delay_money']) : '' }}</td>
                        <td class="text-right text-red">{{ isset($v2['delay_interest']) ? number_format($v2['delay_interest']) : '' }}</td>

                        <td class="text-right">{{ isset($v2['interest']) ? number_format($v2['interest']+$v2['delay_money']+$v2['delay_interest']) : '' }}</td>
                        <td class="text-right">{{ number_format($v2['plan_charge_money']) }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
            </td>
        </tr>
        @endif

        @empty
        <tr>
            <td colspan="17" class='text-center p-4'>예상이자조회 내역이 없습니다.</td>
        </tr>
        @endforelse


    @else
        <tr>
            <td colspan="17" class='text-center p-4'>이자조회일은 이수일 이후로 입력하셔야합니다.</td>
        </tr>
    @endif

    </tbody>
</table>

</div>



<script>

    $(".datetimepicker").datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
        widgetPositioning: {
            horizontal: 'left',
            vertical: 'bottom'
        }
    });
    
    function viewInterestDetail( d )
    {
        if( $('#loanInterestDetail'+d).css('display')=="none" )
        {
            $('.collapse').hide();
        }
        $('#loanInterestDetail'+d).toggle();
    }

    function loanReturnPreview(no, dt)
    {
        var url = "/erp/loanreturnpreview?loan_info_no="+no+"&trade_date="+dt;
        var wnd = window.open(url, "loanreturnpreview","width=900, height=800, scrollbars=yes");
        wnd.focus();
    }

    $(function () {
    $('[data-toggle="tooltip"]').tooltip()
    });

</script>
<style>
.tooltip {
    font-size: 9px;
}
</style>


