<!-- 투자내역 -->
<div class="col-md-12 p-0 m-0 " >
    <div class="card-header p-1" style="border-bottom:none !important;">
        <b>투자내역</b>
        {{-- <button type="button" class="btn btn-xs btn-success float-right mb-1 mr-1" onclick="investorInfoDetailExcel('invPlan','{{ $userVar ?? '' }}');">수익분배전체엑셀</button>
        <button type="button" class="btn btn-xs btn-success float-right mb-1 mr-1" onclick="investorInfoDetailExcel('investorPayment','{{ $userVar ?? '' }}');">지급내역엑셀다운</button>
        <button type="button" class="btn btn-xs btn-success float-right mb-1 mr-1" onclick="investorInfoDetailExcel('investorTotalSchedule','{{ $userVar ?? '' }}');">전체스케줄엑셀다운</button> --}}
    </div>
    @include('inc/listSimple')
    <br>
    <br>
    <div id="investmentinfoDetail" style='display:@if(isset($v->loan_usr_info_no)) block; @else none; @endif'>
        <form class="mb-0" name="form_datailaction" id="form_datailaction" method="post" enctype="multipart/form-data">
        <input type="hidden" id="loan_usr_info_no" name="loan_usr_info_no" value="{{ $userVar ?? '' }}">
        <input type="hidden" id="loan_info_no" name="loan_info_no" value="{{ $v->loan_info_no ?? '' }}">
            <div class="form-goup row">
                <b>수익지급내역</b>
                <table class="table table-sm table-hover loan-info-table card-secondary card-outline">
                    <thead>
                        <tr>
                            <th class="text-center">No</th>
                            <th class="text-center w-2">채권번호</th>
                            <th class="text-center">투자원금</th>
                            <th class="text-center">투자잔액</th>
                            <th class="text-center">기준일자</th>
                            <th class="text-center">이자</th>
                            <th class="text-center">원천징수</th>
                            <th class="text-center">소득세</th>
                            <th class="text-center">주민세</th>
                            <th class="text-center">실수령이자</th>
                            <th class="text-center">지급적용일자</th>
                            <th class="text-center">지급처리자</th>
                            <th class="text-center">지급처리시간</th>
                        </tr>
                    </thead>
                    <tbody id="loan_document">
                        @php ( $sum_return_interest = $sum_interest = $sum_withholding_tax = $sum_income_tax = $sum_local_tax = 0 )
                        @foreach( $plan as $key => $val)
                        <tr>
                            <td class="text-center">{{ $val->no ?? ''}}</td>
                            <td class="text-center">{{ $val->loan_info_no ?? ''}}</td>
                            <td class="text-center">{{ number_format($val->loan_money) ?? '' }}</td>
                            <td class="text-center">{{ number_format($val->balance) }}</td>
                            <td class="text-center">{{ Func::dateFormat($val->trade_date) ?? ''}}</td>
                            <td class="text-center">{{ Func::dateFormat($val->interest) ?? '' }}</td>
                            <td class="text-center">{{ number_format($val->withholding_tax) ?? '' }}</td>
                            <td class="text-center">{{ number_format($val->income_tax) ?? ''}}</td>
                            <td class="text-center">{{ number_format($val->local_tax) ?? '' }}</td>
                            <td class="text-center">{{ number_format($val->return_money) }}</td>
                            <td class="text-center">{{ Func::dateFormat($val->return_date) ?? ''}}</td>
                            <td class="text-center">{{ (isset($array_user[$val->save_id])) ? $array_user[$val->save_id] : $val->save_id }}</td>
                            <td class="text-center">{{ Func::dateFormat($val->save_time) ?? ''}}</td>
                        </tr>

                        @php ( $sum_interest+= $val->interest )
                        @php ( $sum_return_interest+= $val->return_money )
                        @php ( $sum_withholding_tax+= $val->withholding_tax )
                        @php ( $sum_income_tax+= $val->income_tax )
                        @php ( $sum_local_tax+= $val->local_tax )

                        @endforeach
                    </tbody>
                    <tbody>
                    <tr>
                        <th class="text-center" colspan='6'>합계</th>                    
                        <th class="text-center">{{ number_format($sum_interest ?? 0) }}</th>
                        <th class="text-center">{{ number_format($sum_withholding_tax ?? 0) }}</th>
                        <th class="text-center">{{ number_format($sum_income_tax ?? 0) }}</th>
                        <th class="text-center">{{ number_format($sum_local_tax ?? 0) }}</th>
                        <th class="text-center">{{ number_format($sum_return_interest ?? 0) }}</th>
                        <th class="text-center" colspan=3></th>
                    </tr>
                </tbody>
                </table>
            </div>
        </form>
    </div>
</div>


<form id="fileForm" method="POST">
    @csrf
    <input type="hidden" id="loan_usr_info_no" name="loan_usr_info_no" value="">
</form>


<script>
getDataList('{{ $result['listName'] }}', '{{ $result['page'] ?? 1 }}', '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());

// function invPlanExcel(no)
// {
//     $("#loan_usr_info_no").val(no);
//     $('#fileForm').submit();
// }

function investorInfoDetailExcel(mode, loan_usr_info_no)
{
    $("#loan_usr_info_no").val(loan_usr_info_no);

    if(mode=="invPlan")
    {
        $('#fileForm').attr('action', '/account/investorinfodetailexcel');
    }
    if(mode=="investorPayment")
    {
        $('#fileForm').attr('action', '/account/investorpaymentexcel');
    }else if(mode=="investorTotalSchedule")
    {
        $('#fileForm').attr('action', '/account/investortotalscheduleexcel');
    }
    $('#fileForm').submit();
}
</script>