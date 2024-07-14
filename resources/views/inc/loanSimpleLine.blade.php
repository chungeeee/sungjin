<table class="table table-sm card-secondary card-outline mb-1">
    <thead>
        <tr>
            <th class="text-center">채권번호</th>
            <th class="text-center">계약일</th>
            <th class="text-center">만기일</th>
            <th class="text-center">투자기간(개월)</th>
            <th class="text-center">상환방법</th>
            {{-- <th class="text-center">월상환액</th> --}}
            <th class="text-center">금리</th>
            {{-- <th class="text-center">조기상환</th> --}}
            <th class="text-center">약정일</th>
            {{-- <th class="text-center">이수일</th> --}}
            <th class="text-center">수익지급일</th>
            {{-- <th class="text-center">상실일</th> --}}
            <th class="text-center">상태</th>
            <th class="text-center">잔액</th>
        </tr>
    </thead>
    <tbody>

        @if( is_array($simple) && isset($simple[0]) )
            @foreach($simple as $idx => $v)
            <tr @if($v['no'] == $condition->loan_info_no) bgcolor="FFDDDD" @endif >
                <td class="text-center">{{ $v['investor_type'].$v['investor_no'] }}-{{ $v['inv_seq'] ?? 1 }}</td>
                <td class="text-center">{{ Func::dateFormat($v['contract_date']) }}</td>
                <td class="text-center">{{ Func::dateFormat($v['contract_end_date']) }}</td>
                <td class="text-center">{{ number_format($v['loan_term']) }}</td>
                <td class="text-center">{{ $v['return_method_nm'] }}</td>
                {{-- <td class="text-center">{{ number_format($v['monthly_return_money']) }}</td> --}}
                <td class="text-center">{{ number_format($v['loan_rate'], 3) }}%</td>
                {{-- <td class="text-center">{{ $v['return_fee_nm'] ?? '-' }}</td> --}}
                <td class="text-center">{{ $v['contract_day'] }}일</td>
                {{-- <td class="text-center">{{ Func::dateFormat($v['take_date']) }}</td> --}}
                <td class="text-center {{ $v['return_date']<date('Ymd') ? 'text-red' : '' }}">{{ Func::dateFormat($v['return_date']) }}</td>
                {{-- <td class="text-center {{ $v['kihan_date'] <date('Ymd') ? 'text-red' : '' }}">{{ Func::dateFormat($v['kihan_date']) }}</td> --}}
                <td class="text-center">{!!Func::nvl(Vars::$arrayContractStaColor[$v['status']], $v['status']) !!}
                    {!! ( $v['status']=='S' && $v['fullpay_date']!='' ) ? " <span class='status-text-e'>(완제)</span>" : "" !!}
                </td>
                <td class="text-center">{{ number_format($v['balance']) }}</td>
            </tr>
            @endforeach
        @else
        <tr>
            <td class="text-center">{{ $simple['investor_type'].$simple['investor_no'] }}-{{ ($simple['inv_seq'] ?? 1) }}</td>
            <td class="text-center">{{ Func::dateFormat($simple['contract_date']) }}</td>
            <td class="text-center">{{ Func::dateFormat($simple['contract_end_date']) }}</td>
            <td class="text-center">{{ number_format($simple['loan_term']) }}</td>
            <td class="text-center">{{ $simple['return_method_nm'] }}</td>
            {{-- <td class="text-center">{{ number_format($simple['monthly_return_money']) }}</td> --}}
            <td class="text-center">{{ number_format($simple['loan_rate'], 3) }}%</td>
            {{-- <td class="text-center">{{ $simple['return_fee_nm'] ?? '-' }}</td> --}}
            <td class="text-center">{{ $simple['contract_day'] }}일</td>
            {{-- <td class="text-center">{{ Func::dateFormat($simple['take_date']) }}</td> --}}
            <td class="text-center {{ $simple['return_date']<date('Ymd') ? 'text-red' : '' }}">{{ Func::dateFormat($simple['return_date']) }}</td>
            {{-- <td class="text-center {{ $simple['kihan_date'] <date('Ymd') ? 'text-red' : '' }}">{{ Func::dateFormat($simple['kihan_date']) }}</td> --}}
            <td class="text-center">{!! Func::getInvStatus($simple['status'], true) !!}</td>
            <td class="text-center">{{ number_format($simple['balance']) }}</td>
        </tr>
        @endif
        <tr><td colspan=20></td></tr>
    </tbody>
    </table>
