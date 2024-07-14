@extends('layouts.masterPop')
@section('content')




<form class="form-horizontal" name="trade_out_form" id="trade_out_form">

<div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title">이전 대출결재정보</h2>
    </div>

    <div class="card-body mr-3 p-3">


        <table class="table table-sm card-secondary card-outline mt-1 mb-1 table-bordered">
        <tr height=30>
        <th class="w-20 text-center" bgcolor="EEEEEE">고객명</th>
        <td class="w-30 text-center">{{ $ci->name }}</td>
        <th class="w-20 text-center" bgcolor="EEEEEE">고객번호</th>
        <td class="w-30 text-center">{{ $ci->no }}</td>
        </tr>
        </table>

        @forelse( $va as $v )


        <table class="table table-sm card-secondary card-outline mt-2 table-bordered">
        <tbody>

        <tr>
        <th class="w-20 text-center" bgcolor="EEEEEE">대출일시</th>
        <td class="w-30 text-center">{{ Func::dateFormat($v->loan_date) }}</td>
        <th class="w-20 text-center" bgcolor="EEEEEE">대출금액</th>
        <td class="w-30 text-center">{{ number_format($v->loan_money) }}원</td>
        </tr>
        <tr>
        <th class="w-20 text-center" bgcolor="EEEEEE">
            상담메모<br>
            (심사자 : {{ Func::nvl($array_user[$v->app_manager_id],$v->app_manager_id) }})
        </th>
        <td colspan=3>
            {!! str_replace("\n","<br>",$v->memo) !!}
        </td>
        </tr>
        <tr>
        <th class="w-20 text-center" bgcolor="EEEEEE">
            결재자의견<br>
            (결재자 : {{ Func::nvl($array_user[$v->app_confirm_id],$v->app_confirm_id) }})
        </th>
        <td colspan=3>
            {!! str_replace("\n","<br>",$v->app_confirm_note) !!}
        </td>
        </tr>
        @if( trim($v->app_confirm_mnote)!="" )
        <tr>
        <th class="w-20 text-center" bgcolor="EEEEEE">
            섹터장결재의견<br>
            (섹터장 : {{ Func::nvl($array_user[$v->app_confirm_m],$v->app_confirm_m) }})            
        </th>
        <td colspan=3>
            {!! str_replace("\n","<br>",$v->app_confirm_mnote) !!}
        </td>
        </tr>
        @endif
        @if( trim($v->app_confirm_nnote)!="" )
        <tr>
        <th class="w-20 text-center" bgcolor="EEEEEE">
            본부장결재의견<br>
            (본부장 : {{ Func::nvl($array_user[$v->app_confirm_n],$v->app_confirm_n) }})                        
        </th>
        <td colspan=3>
            {!! str_replace("\n","<br>",$v->app_confirm_nnote) !!}
        </td>
        </tr>
        @endif

        </tbody>
        </table>


        @empty


        <table class="table table-sm card-secondary card-outline mt-2 table-bordered">
        <tbody>
        <tr>
            <td colspan="20" class='text-center p-4'>이전 대출결재정보가 없습니다.</td>
        </tr>
        </tbody>
        </table>      


        @endforelse
            



    </div>
    <div class="card-footer">
        <button type="button" class="btn btn-sm btn-secondary float-right mr-3" id="cate_btn" onclick="window.close();">닫기</button>
    </div>
    
</div>

</form>

@endsection



@section('javascript')

@endsection
