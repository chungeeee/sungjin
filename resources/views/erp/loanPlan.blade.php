
<div class="p-2">


@include('inc/loanSimpleLine')



<div class="form-group row col-md-12 p-0 m-0 mt-2">
    <label for="plan_trade_no" class="col-sm-10 col-form-label text-right">이전 상환스케줄 보기</label>
    <div class="col-sm-2 p-0 m-0">
        <select class="form-control form-control-sm" name="plan_trade_no" id="plan_trade_no" onchange="getLoanPlan({{ $no }});">
        <? Func::printOption($replans, $plan_trade_no); ?>
        </select>
    </div>
</div>



<table class="table table-sm card-secondary card-outline table-hover mt-0">
<thead>

    <tr>
        <th class="text-center">회차</th>
        <th class="text-center">납입일</th>
        <th class="text-center">납입일(영업)</th>
        <th class="text-center">이자일수</th>
        <th class="text-center">이자구간</th>
        <th class="text-right">납입액</th>
        <th class="text-right">원금</th>
        <th class="text-right">이자</th>
        <th class="text-right">납입 후 잔액</th>
        <th class="text-center">등록/갱신일시</th>
    </tr>

</thead>
<tbody>

    @php ( $sum_plan_money    = 0 )
    @php ( $sum_plan_origin   = 0 )
    @php ( $sum_plan_interest = 0 )
    @forelse( $plans as $v )
    <tr>

        <td class="text-center {{ $v->plan_date<=date('Ymd') ? 'bg-secondary' : '' }}">{{ number_format($v->seq) }}</td>
        <td class="text-center {{ isset($holiday[$v->plan_date]) ? 'text-red' : '' }}">{{ Func::dateFormat($v->plan_date) }} ({{ Vars::$arrayWeekDay[date('w',Func::dateToUnixtime($v->plan_date))] }})</td>
        <td class="text-center">{{ Func::dateFormat($v->plan_date_biz) }}</td>
        <td class="text-center">{{ number_format($v->plan_interest_term) }}</td>
        <td class="text-center">{{ Func::dateFormat($v->plan_interest_sdate) }} ~ {{ Func::dateFormat($v->plan_interest_edate) }}</td>
        <td class="text-right">{{ number_format($v->plan_money) }}</td>
        <td class="text-right">{{ number_format($v->plan_origin) }}</td>
        <td class="text-right">{{ number_format($v->plan_interest) }}</td>
        <td class="text-right">{{ number_format($v->plan_balance) }}</td>
        <td class="text-center">{{ Func::dateFormat($v->save_time) }}</td>

    </tr>

    @php ( $sum_plan_money   += $v->plan_money    )
    @php ( $sum_plan_origin  += $v->plan_origin   )
    @php ( $sum_plan_interest+= $v->plan_interest )

    
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
        <td class="text-center"></td>
        <td class="text-center"></td>
        <td class="text-right">{{ number_format($sum_plan_money) }}</td>
        <td class="text-right">{{ number_format($sum_plan_origin) }}</td>
        <td class="text-right">{{ number_format($sum_plan_interest) }}</td>
        <td class="text-right">-</td>
        <td class="text-center">-</td>
        
    </tr>
    @endif

</tbody>
</table>



