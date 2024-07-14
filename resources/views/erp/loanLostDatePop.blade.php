@extends('layouts.masterPop')
@section('content')




<form class="form-horizontal" name="trade_out_form" id="trade_out_form">

<div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title">소멸시효 변경이력</h2>
    </div>

    <div class="card-body mr-3 p-3">


        <table class="table table-sm card-secondary card-outline mt-1 mb-1 table-bordered">
        <tr height=30>
        <th class="text-center" bgcolor="EEEEEE">고객명</th>
        <td class="text-center">{{ $v->name }}</td>
        <th class="text-center" bgcolor="EEEEEE">고객번호</th>
        <td class="text-center">{{ $v->cust_info_no }}</td>
        <th class="text-center" bgcolor="EEEEEE">계약번호</th>
        <td class="text-center">{{ $v->no }}</td>
        </tr>
        </table>


        <table class="table table-sm card-secondary card-outline mt-2 table-bordered table-hover">
        <thead>
            <tr bgcolor="EEEEEE">
                <th class="text-center">처리일시</th>
                <th class="text-center">처리사번</th>
                <th class="text-center">구분</th>
                <th class="text-center">기준일자</th>
                <th class="text-center">소멸시효일</th>
                <th class="text-center"></th>
            </tr>
        </thead>
        <tbody>

            @forelse( $rslt as $v2 )

            <tr>
                <td class="text-center">{{ Func::dateFormat($v2->save_time) }}</td>
                <td class="text-center">{{ $v2->save_id }}</td>
                <td class="text-center">{{ Vars::$arrayLoanLostDiv[$v2->trade_div] }}</td>
                <td class="text-center">{{ Func::dateFormat($v2->trade_date) }}</td>
                <td class="text-center">{{ Func::dateFormat($v2->lost_date) }}</td>
                <td class="text-center">@if( $v->lost_date==$v2->lost_date ) <i class='fas fa-check text-green'></i> @endif</td>
            </tr>

            @empty
            <tr>
                <td colspan="20" class='text-center p-4'>변경이력이 없습니다.</td>
            </tr>
            @endforelse
            
        </tbody>
        </table>



    </div>
    <div class="card-footer">
        <button type="button" class="btn btn-sm btn-secondary float-right mr-3" id="cate_btn" onclick="window.close();">닫기</button>
    </div>
    
</div>

</form>

@endsection



@section('javascript')

@endsection
