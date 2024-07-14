
<div class="p-2">


@if( !isset($view_opt) || $view_opt!="NO_SIMPLE_LINE" )
@include('inc/loanSimpleLine')
@endif


<table class="table table-sm card-secondary card-outline table-bordered mt-2">
<tr class="text-center">
<th width="7%">화해일자</th><td width="7%" title="화해원장번호:{{ $settle->no }}">{{ Func::dateFormat($settle->settle_date) }}</td>
<th width="7%">화해금액</th><td width="7%">{{ number_format($settle->settle_money) }}원</td>
<th width="7%">분납시작</th><td width="7%">{{ $settle->settle_year }}년{{ $settle->settle_month }}월</td>
<!--
<th width="7%">감면금액</th><td width="7%">{{ number_format($settle->settle_lose_money) }}원</td>
<th width="7%">감면구분</th><td width="7%">{{ Func::nvl(Vars::$arrayCcrsDiv[$settle->settle_lose_type], $settle->settle_lose_type) }}</td>
-->
<th width="7%">화해사유</th><td width="7%">{{ $settle->settle_reason_nm }}</td>
<th width="7%">다음납입회차</th><td width="7%">{{ ( $settle->trade_cnt > $settle->settle_cnt ) ? "완료" : $settle->trade_cnt."회" }}</td>
</tr>
</table>



<table class="table table-sm card-secondary card-outline table-hover mt-0">
<thead>

    <tr>
        <th class="text-center">회차</th>
        <th class="text-center">납입예정일</th>
        <th class="text-center">납입예정일(영업)</th>
        <th class="text-right">납입예정액</th>
        <th class="text-right">화해원금</th>
        <th class="text-right">화해이자</th>
        <th class="text-right">잔여화해금액</th>

        <th class="text-center">납입여부</th>
        <th class="text-center">납입일</th>
        <th class="text-right">납입액</th>
        <th class="text-right">잔여액</th>
    </tr>

</thead>
<tbody>

    @php ( $sum_plan_money    = 0 )
    @php ( $sum_plan_origin   = 0 )
    @php ( $sum_plan_interest = 0 )
    @php ( $sum_trade_money   = 0 )
    @php ( $sum_remain_money  = 0 )

    @forelse( $plans as $v )
    <tr>

        <td class="text-center {{ $v->plan_date<=date('Ymd') ? 'bg-secondary' : '' }}">{{ number_format($v->seq) }}</td>
        <td class="text-center {{ isset($holiday[$v->plan_date]) ? 'text-red' : '' }}">{{ Func::dateFormat($v->plan_date) }} ({{ Vars::$arrayWeekDay[date('w',Func::dateToUnixtime($v->plan_date))] }})</td>
        <td class="text-center">{{ Func::dateFormat($v->plan_date_biz) }}</td>

        <td class="text-right">{{ number_format($v->plan_money) }}</td>
        <td class="text-right">{{ number_format($v->plan_origin) }}</td>
        <td class="text-right">{{ number_format($v->plan_interest) }}</td>
        <td class="text-right">{{ number_format($v->plan_balance) }}</td>

        <td class="text-center">{{ $v->status=="N" ? "완료" : "" }}</td>
        <td class="text-center">{{ ( $v->trade_money>0 ) ? Func::dateFormat($v->trade_date) : "" }}</td>
        <td class="text-right">{{ number_format($v->trade_money) }}</td>
        <td class="text-right">{{ number_format($v->plan_money - $v->trade_money) }}</td>


    </tr>

        @php ( $sum_plan_money   += $v->plan_money    )
        @php ( $sum_plan_origin  += $v->plan_origin   )
        @php ( $sum_plan_interest+= $v->plan_interest )
        @php ( $sum_trade_money  += $v->trade_money   )
        @php ( $sum_remain_money += ( $v->plan_money - $v->trade_money ) )

    
    @empty
    <tr>
        <td colspan="13" class='text-center p-4'>등록된 상환스케줄이 없습니다.</td>
    </tr>

    @endforelse


    @if( $sum_plan_money>0 )
    <tr class="bg-secondary">

        <td class="text-center"></td>
        <td class="text-center">합계</td>
        <td class="text-center"></td>
        <td class="text-right">{{ number_format($sum_plan_money) }}</td>
        <td class="text-right">{{ number_format($sum_plan_origin) }}</td>
        <td class="text-right">{{ number_format($sum_plan_interest) }}</td>

        <td class="text-center" colspan=3>진행율 = {{ number_format($sum_trade_money/$sum_plan_money*100,2) }}%</td>

        <td class="text-right">{{ number_format($sum_trade_money) }}</td>
        <td class="text-right">{{ number_format($sum_remain_money) }}</td>

    </tr>
    @endif

</tbody>
</table>



