<?php

$trade_type_name = "";

?>
<div class="p-2">


@include('inc/loanSimpleLine')



<div class="form-group row">
    <div class="pt-0 pl-2 col-md-4">
    </div>
    <div class="pt-0 pl-2 col-md-8">
    </div>
</div>

<div class="row">
    <div class="col-12">
    <!-- Custom Tabs -->
        <div class="p-0">
            <div class="d-flex p-0">
            <ul class="nav nav-pills gray pb-0">
                <li class="nav-item "><a class="nav-link nav-loan-detail text-xs active pt-2 pb-2" href="#tab_1" data-toggle="tab">금리이력</a></li>
                <li class="nav-item "><a class="nav-link nav-loan-detail text-xs pt-2 pb-2" href="#tab_2" data-toggle="tab">약정일이력</a></li>
                <li class="nav-item "><a class="nav-link nav-loan-detail text-xs pt-2 pb-2" href="#tab_3" data-toggle="tab">계약정보변경</a></li>
            </ul>
            </div><!-- /.card-header -->
            <div class="p-0">
            <div class="tab-content">
            {{-- 금리  --}}
                <div class="tab-pane p-0 active" id="tab_1">
                    <table class="table table-hover table-sm card-secondary card-outline table-bordered mt-0 mb-0" id="loanInfoLogTable">
                        <thead>
                            <tr bgcolor="EEEEEE">
                                <th class="text-center" width="25%">등록일시</th>
                                <th class="text-center" width="25%">등록사번</th>
                                <th class="text-center" width="25%">적용일</th>
                                <th class="text-center" width="25%">금리</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse( $rate as $v )
                            <tr>
                                <td class="text-center">{{ Func::dateFormat($v->save_time) }}</td>
                                <td class="text-center">{{ $v->save_id }}</td>
                                <td class="text-center">{{ Func::dateFormat($v->rate_date) }}</td>
                                <td class="text-center">{{ number_format($v->loan_rate,2) }}%</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="13" class='text-center p-4'>변경내역이 없습니다.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 약정일  --}}
                <div class="tab-pane p-0" id="tab_2">
                    <table class="table table-hover table-sm card-secondary card-outline table-bordered mt-0 mb-0" id="loanInfoLogTable">
                    <thead>
                            <tr bgcolor="EEEEEE">
                                <th class="text-center" width="20%">등록일시</th>
                                <th class="text-center" width="20%">등록사번</th>
                                <th class="text-center" width="20%">적용일</th>
                                <th class="text-center" width="40%">약정일</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse( $cday as $v )
                            <tr>
                                <td class="text-center">{{ Func::dateFormat($v->save_time) }}</td>
                                <td class="text-center">{{ $v->save_id }}</td>
                                <td class="text-center">{{ Func::dateFormat($v->cday_date) }}</td>
                                <td class="text-center">{{ $v->contract_day }}일</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="13" class='text-center p-4'>변경내역이 없습니다.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- 계약정보변경  --}}
                <div class="tab-pane p-0" id="tab_3">
                    <table class="table table-sm card-secondary card-outline table-bordered mt-0 mb-0" id="loanInfoLogTable">
                        <thead>
                            <tr bgcolor="EEEEEE">
                                <th class="text-center">변경일시</th>
                                <th class="text-center" width="10%">계약일</th>
                                <th class="text-center" width="10%">만기일</th>
                                <th class="text-center" width="10%">약정일</th>
                                <th class="text-center" width="10%">금리</th>
                                <th class="text-center" width="10%">잔액</th>
                                <th class="text-center" width="10%">송금일</th>
                                <th class="text-center" width="10%">지급일</th>
                                <th class="text-center" width="10%">상태</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse( $cont as $v )
                            <tr>
                                <td class="text-center" bgcolor="FFFFFF">{{ Func::dateFormat($v->save_time) }}</td>
                                <td class="text-center" bgcolor="{{ $v->contract_date_color        }}">{{ Func::dateFormat($v->contract_date) }}</td>
                                <td class="text-center" bgcolor="{{ $v->contract_end_date_color    }}">{{ Func::dateFormat($v->contract_end_date) }}</td>
                                <td class="text-center" bgcolor="{{ $v->contract_day_color         }}">{{ $v->contract_day }}</td>
                                <td class="text-center" bgcolor="{{ $v->loan_rate_color            }}">{{ (float) $v->loan_rate }}%</td>
                                <td class="text-center" bgcolor="{{ $v->balance_color              }}">{{ number_format($v->balance) }}</td>
                                <td class="text-center" bgcolor="{{ $v->take_date_color            }}">{{ Func::dateFormat($v->take_date) }}</td>
                                <td class="text-center" bgcolor="{{ $v->return_date_color          }}">{{ Func::dateFormat($v->return_date) }}</td>
                                <td class="text-center" bgcolor="{{ $v->status_color               }}">{!! Vars::$arrayContractStaColor[$v->status] !!}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="13" class='text-center p-4'>변경내역이 없습니다.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            </div>

        </div>
    </div>
</div>


</div>


<script>
    function viewDeleteTrade(b)
    {
        if( b.checked )
        {
            $('.collapse').show();
        }
        else
        {
            $('.collapse').hide();
        }
    }

    $(function(){
        // Enables popover
        $("[data-toggle=popover]").popover();
    });    
</script>