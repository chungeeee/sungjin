<script>

    $(function(){
        $("#custom-tabs-four-{{$selected_no}}-tab").click();

        @if( !empty($msg) )
        @foreach( $msg as $type => $v )
        var str = "<span class='right badge badge-info mr-1'>OK</span><font>{{$v['str'].'('.$v['add'].')'}}</font>";        // class='text-danger'
        $("#bigo_"+"{{$type}}").html(str);
        @endforeach
        @endif

    });
</script>

<style>
    .nav-tabs .nav-link.active, .nav-tabs .nav-item.show .nav-link {
        color: snow;
        background-color: #17a2b8;
        border-color: #dee2e6 #dee2e6 #fff;
    }
</style>


<div class="card-header p-0 border-bottom-0">
    <ul class="nav nav-tabs" id="custom-tabs-tab" role="tablist">
        @forelse( $loan_info_nos as $idx => $no )
        <li class="nav-item">
            <a class="nav-link text-black pt-1 pb-1" id="custom-tabs-four-{{$no}}-tab" data-toggle="pill" href="#custom-tabs-four-{{$no}}" 
                    role="tab" aria-controls="custom-tabs-four-{{$no}}" aria-selected="false"><b> {{$no}} 번 계약</b></a>
        </li>
        @empty

        @endforelse
    </ul>
</div>


<div class='content plans card-body p-2' style='max-height:200px; overflow-y:auto; overflow-x:hidden;'>
<div class="tab-content" id="custom-tabs-four-tabContent">

    @forelse( $loan_info_nos as $idx => $no )
    <div class="tab-pane fade" id="custom-tabs-four-{{$no}}" role="tabpanel" aria-labelledby="custom-tabs-four-{{$no}}-tab">
    <table class='table-sm table-hover mt-1 w-100'>
        <tr class='text-center'>
            <td class='pt-0' colspan=15 bgcolor='FFFFFF'>
                <div class='form-group row p-0' >
                <div class='col-sm-6 p-0 pr-1'>

                <b class="">스케줄 변경전</b>

                    <table class='table table-sm table-hover card-secondary card-outline'>
                        <tr class='text-right'>
                            <td class='text-center'></td>
                            <td class='text-center'>납입일</td>
                            <td class='text-center'>이자구간</td>
                            <td>납입액</td>
                            <td>원금</td>
                            <td>이자</td>
                            <td>잔액</td>        
                        </tr>

                        @php 
                        $i = 1;    
                        $plan_sum = Array('plan_money'=>0, 'plan_origin'=>0, 'plan_interest'=>0);
                        @endphp

                        @forelse( $plan_old[$no] as $vp )
                            {{-- @php
                                if( $vp['plan_date']<min(array_keys($plan_new[$no])) ) continue;
                            @endphp --}}
                            

                            <tr class='text-right'>
                            <td class='text-center'>{{ $i }}</td>
                            <td class='text-center'>{!! Func::dateFormat($vp['plan_date']) !!}</td>
                            <td class='text-center'>
                            {!! Func::dateFormat($vp['plan_interest_sdate']) !!}~{!! Func::dateFormat($vp['plan_interest_edate']) !!}
                            ({!! Func::dateFormat($vp['plan_interest_term']) !!})
                            </td>                            
                            <td>{{ number_format($vp['plan_money']) }}</td>
                            <td>{{ number_format($vp['plan_origin']) }}</td>
                            <td>{{ number_format($vp['plan_interest']) }}</td>
                            <td>{{ number_format($vp['plan_balance']) }}</td>
                            </tr>

                            @php
                            $plan_sum['plan_money']   += $vp['plan_money'];
                            $plan_sum['plan_origin']  += $vp['plan_origin'];
                            $plan_sum['plan_interest']+= $vp['plan_interest'];
                            $i++;
                            @endphp
                        @empty
                        
                        @endforelse

                        <tr class='text-right bg-secondary'>
                            <td class='text-center' colspan=3>합계</td>
                            <td>{{ number_format($plan_sum['plan_money']) }}</td> 
                            <td>{{ number_format($plan_sum['plan_origin']) }}</td>
                            <td>{{ number_format($plan_sum['plan_interest']) }}</td>
                            <td>-</td>
                        </tr>
                    </table>


                </div>


                <div class='col-sm-6 p-0 pl-1'>
                
                <b>스케줄 변경후</b>

                    <table class='table table-sm table-hover card-secondary card-outline'>
                    <tr class='text-right'>
                    <td class='text-center'></td>
                    <td class='text-center'>납입일</td>
                    <td class='text-center'>이자구간</td>
                    <td>납입액</td>
                    <td>원금</td>
                    <td>이자</td>
                    <td>잔액</td>        
                    </tr>

                    @php
                    $i = 1;
                    $plan_sum = Array('plan_money'=>0, 'plan_origin'=>0, 'plan_interest'=>0);
                    @endphp

                    @forelse( $plan_new[$no] as $vp )
                        <tr class='text-right'>
                        <td class='text-center'>{{ $i }}</td>
                        <td class='text-center'>{!! Func::dateFormat($vp['plan_date']) !!}</td>
                        <td class='text-center'>
                        {!! Func::dateFormat($vp['plan_interest_sdate']) !!}~{!! Func::dateFormat($vp['plan_interest_edate']) !!}
                        ({!! Func::dateFormat($vp['plan_interest_term']) !!})
                        </td>
                        <td>{{ number_format($vp['plan_money']) }}</td>
                        <td>{{ number_format($vp['plan_origin']) }}</td>
                        <td>{{ number_format($vp['plan_interest']) }}</td>
                        <td>{{ number_format($vp['plan_balance']) }}</td>
                        </tr>

                        @php
                        $plan_sum['plan_money']   += $vp['plan_money'];
                        $plan_sum['plan_origin']  += $vp['plan_origin'];
                        $plan_sum['plan_interest']+= $vp['plan_interest'];
                        $i++;
                        @endphp
                    @empty

                    @endforelse

                    <tr class='text-right bg-secondary'>
                    <td class='text-center' colspan=3>합계</td>
                    <td>{{ number_format($plan_sum['plan_money']) }}</td>
                    <td>{{ number_format($plan_sum['plan_origin']) }}</td>
                    <td>{{ number_format($plan_sum['plan_interest']) }}</td>
                    <td>-</td>
                    </tr>                
                    </table>


                </div>
            </td>
        </tr>
    </table>
    </div>
    @empty
        <div class='text-center' style='height:100px; transform: translateY(40%);'><b>※ 스케줄 없음</b></div>
    @endforelse

</div>
</div>