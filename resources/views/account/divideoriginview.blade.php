<br>
<div class="content-wrapper needs-validation m-0">
    <div class="col-md-12">
        <div class="card card-outline card-lightblue">        
            <div class="card-header p-1">
                <h3 class="card-title"><i class="fas fa-user m-2" size="9px">원금조정대상금액</i>
                <div class="card-tools pr-2">
                </div>
            </div>

            <div class="card-body p-1">
                <table class="table table-sm table-bordered table-input text-xs">
                <colgroup>
                <col width="30%"/>
                <col width="70%"/>
                </colgroup>

                <tbody>
                <tr>
                    <th class="col-form-label-sm">원금조정대상금액</th>
                    <td>{{ number_format($v->return_origin) ?? '' }}</td>
                </tr>                        
                </table>
            </div>
            
        </div>
    </div>

    <div class="col-md-12">
        <div class="card card-outline card-lightblue">        
            <div class="card-header p-1">
                <h3 class="card-title"><i class="fas fa-user m-2" size="9px">원금조정처리내역</i>
                <div class="card-tools pr-2">
                </div>
            </div>

            <div class="card-body p-1">
                <table class="table table-sm card-secondary card-outline mt-1 table-bordered">
                <colgroup>
                <col width="20%"/>
                <col width="20%"/>
                <col width="20%"/>
                <col width="20%"/>
                <col width="20%"/>
                </colgroup>
                <thead>
                <tr>
                    <th class="text-center">투자자번호</th>
                    <th class="text-center">투자자명</th>
                    <th class="text-center">투자잔액</th>
                    <th class="text-center">차감액</th>
                    <th class="text-center">차감 후 잔액</th>
                </tr>
                </thead>
                <tbody>
                @if ( sizeof($inv) > 0)
                @foreach ( $inv as $v )
                <tr>
                    <td class="text-center">{{ $v['loan_usr_info_no'] }}</td>
                    <td class="text-center">{{ $v['name'] }}</td>
                    <td class="text-right">{{ number_format($v['origin_balance']) }}</td>
                    <td class="text-right">{{ number_format($v['trade_money']) }}</td>
                    <td class="text-right">{{ number_format($v['balance']) }}</td>
                </tr>
                @endforeach
                @else
                <tr height="40">
                    <td colspan="5" align="center">투자원금조정처리이력이 없습니다.</td>
                </tr>
                @endif
                </tbody>
                </table>
            </div>                
        </div>
    </div>
</div>
<div class="modal-footer justify-content-between">
    <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
    <div class="p-0">            
    </div>
</div>
<script>
setInputMask('class', 'moneyformat', 'money');
</script>