<?php

$trade_type_name = "";

?>
<div class="p-2">



@include('inc/loanSimpleLine')

<div class="form-group row">
    <div class="pt-0 pl-2 col-md-4">
    </div>
    <div class="pt-0 pl-2 col-md-8">
        <div class="form-check text-right pr-3">
            <input style="vertical-align:middle;" class="form-check-input" type="checkbox" id="chk_loan_trade_delete_view" onclick="viewDeleteTrade(this);">
            <label style="vertical-align:middle;" class="form-check-label" for="chk_loan_trade_delete_view">삭제거래보기</label>
        </div>
    </div>
</div>

<style>
#loanInfoTradeTable tbody:hover,
tr.hover,
th.hover,
td.hover,
tr.hoverable:hover {
    background-color: #EEEEEE;
}
#loanInfoTradeTable tbody {
    border-bottom: 2px solid #C3C3C3;
}

/*
.table { border: 1px solid #2980B9; }
.table thead > tr > th { border-bottom: none; }
.table thead > tr > th, .table tbody > tr > th, .table tfoot > tr > th, .table thead > tr > td, .table tbody > tr > td, .table tfoot > tr > td { border: 1px solid #2980B9; }
*/
/*
#loanInfoTradeDiv {
    overflow-y : scroll;
    height: calc(100% - 60px);
}
*/

.outline-none::-webkit-scrollbar{
    width: 8px;
    height: 10px;
}
.outline-none::-webkit-scrollbar-button {
    width: 8px;
}
.outline-none::-webkit-scrollbar-thumb {
    background: #999;
    border: thin solid gray;
    border-radius: 10px;
}
.outline-none::-webkit-scrollbar-track {
    background: #eee;
    border: thin solid lightgray;
    box-shadow: 0px 0px 3px #dfdfdf inset;
    border-radius: 10px;
}

</style>
<form id="print_form" method="post" name="print_form">
    @csrf
    <input type="hidden" id="loan_info_no" name="loan_info_no" value= "{{$simple['no']}}">
    <input type="hidden" id="cust_info_no" name="cust_info_no" value= "{{$simple['cust_info_no']}}">
    <input type="hidden" id="post_cd" name="post_cd" value= "100120">
    <input type="hidden" id="print_basis_date" name="print_basis_date" value="{{ date('Y-m-d') }}">
    <input type="hidden" id="post_addr_cd" name="post_addr_cd" value="P">
    <input type="hidden" id="zip" name="zip" value="">
    <input type="hidden" id="addr" name="addr" value="">
    <input type="hidden" id="addr2" name="addr2" value="">
    <input type="hidden" id="print_no" name="print_no" value="">
    <input type="hidden" id="table_type" name="table_type" value="">
    <input type="hidden" id="trade_no" name="trade_no" value="">
</form>

<div id="loanInfoTradeDiv" class="outline-none" style="overflow-y:scroll;border:0px;">
<table class="table table-sm card-secondary card-outline mt-1 table-bordered" id="loanInfoTradeTable">

    <col width="8%" />
    <col width="9%" />
    <col width="9%" />

    <col width="9%" />
    <col width="9%" />

    <col width="8%" />
    <col width="8%" />

    <col width="10%" />
    <col width="10%" />

    <thead>
        <tr bgcolor="EEEEEE">
            <th class="text-center" rowspan=2 style="vertical-align:middle;">입출금No</th>
            <th class="text-center" rowspan=2 >구분</th>
            <th class="text-center">거래일자</th>
            
            <th class="text-center">이자(출)</th>
            <th class="text-center">원금(출)</th>
            <th class="text-center">잔여이자</th>
            <th class="text-center">가지급금</th>

            <th class="text-center">등록일시</th>
            <th class="text-center" rowspan=2 style="vertical-align:middle;">메모</th>
        </tr>
        <tr bgcolor="EEEEEE">
            <th class="text-center">거래금액</th>
            
            <th class="text-center">이자(감)</th>
            <th class="text-center">원금(감)</th>
            <th class="text-center">잔여원금</th>
            <th class="text-center">차기수익지급일</th>
            
            <th class="text-center">삭제일시</th>
        </tr>

    </thead>

    @php ($cnt = 0)
    @php ($bf_basis_date = date("YmdHis"))
    
    @forelse( $result as $v )
    @foreach ($condition as $basis_date => $c_data) 

        @if( $bf_basis_date >= $basis_date && $basis_date > $v->save_time )
            @foreach ($c_data as $c_seq => $c_detail)
            <tbody>
                <tr @if($c_detail->save_status=='N') class="collapse view-del bg-pastel-red" @else class="bg-pastel-red" @endif>
                    <td class="text-center"></td>
                    <td class="text-center">@if($c_detail->save_status=='N') (삭) @endif {{ $c_detail->div }}</td>
                    <td class="text-center">{{ Func::dateFormat(substr($c_detail->trade_date, 0, 8)) }}</td>
                    <td class="text-center" colspan="2">{{ $c_detail->memo_before }}</td>
                    <td class="text-center" colspan="2">{{ $c_detail->memo_after }}</td>
                    <td class="text-center">{{ substr(Func::dateFormat($c_detail->save_time),2) }}@if($c_detail->save_status=='N') <br>{{ substr(Func::dateFormat($c_detail->del_time),2) }} @endif</td>
                    <td class="text-center"></td>
                </tr>
            </tbody>
            @endforeach
        @endif

    @endforeach
    @php ($bf_basis_date = $v->save_time)

    <!-- 거래원장 -->
    <tbody id="trade_seq_{{ $v->seq }}">
        <tr @if($v->save_status=='N') class="collapse view-del" @endif>
            <td class="text-center {{ $v->trade_color ? $v->trade_color : '' }}" rowspan=2 style="vertical-align:middle;" title="{{ $v->no }}">
                {{ $v->no }}
            </td>
            <td class="text-center" rowspan="2">{{ $v->trade_type_name }}</td>              <!--구분-->    
            <td class="text-center">{{ Func::dateFormat($v->trade_date) }}</td>             <!--거래일자--> 

            <td class="text-right">         <!--이자-->
                {{ Func::numberReport($v->return_misu_money+$v->return_lack_delay_interest+$v->return_lack_delay_money+$v->return_lack_interest+$v->return_delay_interest+$v->return_delay_money+$v->return_interest+$v->return_settle_interest) }}<br>
            </td>
            <!-- <td class="text-right">{{-- $v->return_origin --}}</td> 이자(출) -->
            <td class="text-right">{{ Func::numberReport($v->return_origin) }}</td>  <!-- 원금(출) -->

            <td class="text-right">{{ Func::numberReport($v->refund_interest) }}</td>   <!-- 잔여이자 -->

            <td class="text-right">{{ Func::numberReport($v->over_money) }}</td> <!--가수금-->

            <td class="text-center">        <!--등록일시-->
                {{ substr(Func::dateFormat($v->save_time),2) }}
            </td>
            <td class="text-center" rowspan=2 style="vertical-align:middle;">
                <a type="button" data-container="body" data-toggle="popover" data-html="true" data-placement="left" data-content='{{ $v->memo }}'>
                    {{ $v->memo }}
                </a>
            </td> <!--메모-->
        </tr>
        <tr @if($v->save_status=='N') class="collapse view-del" @endif>
            <td class="text-right">{{ Func::numberReport($v->trade_money) }}</td>             <!--거래금액--> 
            <td class="text-right"> <!--이자감면-->
                {{ Func::numberReport($v->lose_interest+$v->lose_settle_interest+$v->lose_sanggak_interest) }}
            </td>
            <td class="text-right">{{ Func::numberReport($v->lose_origin) }}</td>   <!--원금감면-->

            <td class="text-right">{{ Func::numberReport($v->balance) }}</td>       <!--잔여원금-->

            <td class="text-right">{{ Func::dateFormat($v->return_date) }}</td>  <!--상환일-->
            
            <td class="text-center {{ ( $v->trade_date>=$v->return_date ) ? 'text-red' : '' }}"> <!--삭제일시-->
                {{ Func::dateFormat($v->del_time) }}
            </td>
        </tr>
    </tbody>

    @php ($cnt++)

    @empty
    <tbody>
        <tr>
            <td colspan="20" class='text-center p-4'>거래내역이 없습니다.</td>
        </tr>
    </tbody>
    @endforelse
</table>



<br>

</div>



</div>


<script>
    $(function () {
        // Enables popover
        $("[data-toggle=popover]").popover();

        var lbheight = $("#splitContentB").outerHeight();
        $("#loanInfoTradeDiv").css("height", lbheight-150);
    })

    // 다른 클래스와 충돌되서 클래스명 변경함.
    function viewDeleteTrade(b)
    {
        if( b.checked )
        {
            $('.view-del').show();
        }
        else
        {
            $('.view-del').hide();
        }
    }

    $(document).on("click","#loanInfoTradeTable tbody", function() {
    $(this).closest('table').find('tbody').removeClass('bg-click');
    $(this).addClass('bg-click');
    });

    function printAction(no, table){

        if( !confirm("영수증(겸)잔고확인증을 출력하시겠습니까?") )
        {
            alert(no + table);
            return false;
        }

        $('#table_type').val(table);
        $('#trade_no').val(no);

        var urld  = "/lump/printview";
        var title = "printReceipt";
        
        var formdata = $('#print_form').serializeArray();
        var url = urld+"?fData="+JSON.stringify(formdata);
        var wnd = window.open(url, title,"width=900, height=800, scrollbars=yes");
        wnd.focus();
    }
    
</script>
