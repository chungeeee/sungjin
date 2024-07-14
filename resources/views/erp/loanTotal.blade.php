

<div class="col-md-12 row p-2 pl-4 pt-3">


<div class="pb-2">
<b>고객({{ $cust_info_no }}) 유효 계약정보</b>
</div>

<table class="table table-sm card-secondary table-hover card-outline mb-1">
    <thead>
        <tr>
            <th class="text-center">계약</th>
            <th class="text-center">상품구분</th>
            <th class="text-center">계약일</th>
            <th class="text-center">만기일</th>
            <!--<th class="text-center">대출기간</th>-->
            <th class="text-center">상환방법</th>
            <th class="text-center">월상환액</th>
            <th class="text-center">금리</th>
            <th class="text-center">조기상환</th>
            <th class="text-center">약정일</th>
            <th class="text-center">이수일</th>
            <th class="text-center">최근거래일</th>
            <th class="text-center">차기상환일</th>
            <th class="text-center">기한이익상실일</th>
            <th class="text-center">상태</th>
            <th class="text-right">잔액</th>
        </tr>
    </thead>
    <tbody>
        @php ( $balance       = 0 )
        @php ( $charge_money  = 0 )
        @forelse($array_contracts as $v)
        <tr>
            <td class="text-center">{{ $v->no }}</td>
            <td class="text-center">{{ str_replace("대출채권/","",Func::nvl($array_pro_cd[$v->pro_cd],$v->pro_cd)) }}</td>
            <td class="text-center">{{ Func::dateFormat($v->contract_date) }}</td>
            <td class="text-center">{{ Func::dateFormat($v->contract_end_date) }}</td>
            <!--<td class="text-center">{{ $v->loan_term }}</td>-->
            <td class="text-center">{{ Func::nvl($array_config['return_method_cd'][$v->return_method_cd],$v->return_method_cd) }}</td>
            <td class="text-center">{{ number_format($v->monthly_return_money) }}</td>
            <td class="text-center">{{ number_format($v->loan_rate, 2) }}% / {{ number_format($v->loan_delay_rate, 2) }}%</td>
            <td class="text-center">{{ $v->return_fee_nm ?? '-' }}</td>
            <td class="text-center">{{ $v->contract_day }}일</td>
            <td class="text-center">{{ Func::dateFormat($v->take_date) }}</td>
            <td class="text-center">{{ Func::dateFormat($v->last_trade_date) }}</td>
            <td class="text-center {{ $v->return_date<date('Ymd') ? 'text-red' : '' }}">{{ Func::dateFormat($v->return_date) }}</td>
            <td class="text-center {{ $v->kihan_date <date('Ymd') ? 'text-red' : '' }}">{{ Func::dateFormat($v->kihan_date)  }}</td>
            <td class="text-center">{!! Func::getInvStatus($v->status, true) !!}</td>
            <td class="text-right">{{ number_format($v->balance) }}</td>
        </tr>

        @php ( $balance       += $v->balance       )
        @php ( $charge_money  += $v->charge_money  )

        @empty
        <tr>
            <td colspan="16" class='text-center p-4'>계약정보가 없습니다.</td>
        </tr>
        @endforelse

        <tr class="bg-secondary">
            <td class="text-center" colspan=15>합계</td>
            <td class="text-right">{{ number_format($balance) }}</td>
            <td class="text-right">{{ number_format($charge_money) }}</td>
        </tr>        
        <tr><td colspan=20></td></tr>
    </tbody>
    </table>


</div>