@extends('layouts.masterPop')

@section('content')
<form  class="mb-0" name="withholding_form" id="withholding_form" method="post" enctype="multipart/form-data">
@csrf
<input type="hidden" id="loan_info_no" name="loan_info_no" value="{{ $loan_info_no }}">
<input type="hidden" id="target_date" name="target_date">
<input type="hidden" id="excelDownCd" name="excelDownCd" value="001">
<input type="hidden" id="excel_down_div" name="excel_down_div" value="E">
<input type="hidden" id="down_filename" name="down_filename" value="">
<input type="hidden" id="etc" name="etc" value="">

<div class="content-wrapper needs-validation m-0">
    <div class="col-md-12 text-center" style="padding-top:20px;">
        <section class="pl-3 pb-1">
        <h5>원천징수 - {{ Vars::$arrayInvestmentStatus[$status] }}</h5>
        </section>
    </div>
    <div class="col-md-12">
        @foreach($v as $plan_date =>$arrayIdx)
        <div class="card card-outline card-lightblue">
            <div class="card-header p-1">
                <div class="row">
                    <div class="col-md-8">
                        <h3 class="card-title"><i class="fas fa-user m-2" size="9px"></i>{{ Func::dateFormat($plan_date) }}</h3>
                    </div>
                    <div class="col-md-4 text-right">
                        <button type="button" class="btn btn-sm btn-success" onclick="excel('{{ $plan_date }}');">엑셀다운</button>
                    </div>
                </div>
            </div>

            <div class="card-body p-1">
                <table class="table table-sm card-secondary card-outline table-hover mt-0">
                    <colgroup>
                    <col width="5%"/>
                    <col width="8%"/>
                    <col width="15%"/>
                    <col width="12%"/>
                    <col width="12%"/>
                    <col width="12%"/>
                    <col width="12%"/>
                    <col width="12%"/>
                    <col width="12%"/>
                    </colgroup>
                    <thead>
                    <tr>
                        <th class="text-center" colspan="3">투자자</th>
                        <th class="text-center" colspan="3">예상이자</th>
                        <th class="text-center" colspan="3">당월</th>
                    </tr>
                    <tr>
                        <th class="text-center">NO</th>
                        <th class="text-center">투자자번호</th>
                        <th class="text-center">이름</th>
                        <th class="text-center">투자금</th>
                        <th class="text-center">전체</th>
                        <th class="text-center">당월</th>
                        <th class="text-center">실수령이자</th>
                        <th class="text-center">플랫폼이용료</th>
                        <th class="text-center">원천징수</th>
                    </tr>
                    </thead>
                    @php ( $record = 1 )
                    @foreach($arrayIdx as $idx => $v)                    
                    <tbody>
                    <tr>
                        <td class="text-center">{{ $record }}</td>
                        <td class="text-center">{{ $v->loan_usr_info_no }}</td>
                        <td class="text-center">{{ $v->name }}</td>
                        <td class="text-right">{{ number_format($v->plan_money) }}</td>
                        <td class="text-right">{{ number_format($v->sum_interest) }}</td>
                        <td class="text-right">{{ number_format($v->plan_interest) }}</td>
                        <td class="text-right">{{ number_format($v->plan_interest - $v->platform_fee - $v->withholding_tax) }}</td>
                        <td class="text-right">{{ number_format($v->platform_fee) }}</td>
                        <td class="text-right">{{ number_format($v->withholding_tax) }}</td>
                    </tr>
                    @php ( $record++ )
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endforeach
    </div>
</div>
</form>
@endsection

@section('javascript')

<script>
    function excel(dt)
    {
        $('#target_date').val(dt);

        var postdata = $('#withholding_form').serialize();
        $.ajax({
            url  : "/account/withholdingexcel",
            type : "post",
            data : postdata,
            success : function(data)
            {
                if(data.result == "Y")
                {
                    var f = document.getElementById('withholding_form');
                    $( "<input>",{type:'hidden',id:'filename',name:'filename',value:data.filename}).appendTo(f);
                    $( "<input>",{type:'hidden',id:'excel_no',name:'excel_no',value:data.excel_no}).appendTo(f);
                    $( "<input>",{type:'hidden',id:'record_count',name:'record_count',value:data.record_count}).appendTo(f);
                    f.action = "/account/exceldown";
                    f.method = 'POST';
                    f.submit();

                    // 초기화
                    f.filename.value = '';
                    f.excel_no.value = '';
                    f.record_count.value = '';
                }
                else
                {
                    alert(data.error_msg);
                }
            },
            error : function(xhr)
            {
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }
</script>
@endsection