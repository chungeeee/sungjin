@extends('layouts.master')
@section('content')
@include('inc/list')



<div style="padding-left:10px; padding-top:10px; display:" id='incCheck'>
    <div class="card card-lightblue card-outline" style='width:600px'>
        <div class="card-body p-2">
            <table class="table table-sm table-hover loan-info-table card-secondary card-outline table-bordered">
                <tbody id="miniBoard-table">
                    <tr align="center">
                        <th rowspan="2">NO</th>
                        <th colspan="3" class="rightline">고객원장</th>
                        <th colspan="3">신청원장</th>
                    </tr>
                    <tr align="center">
                        <th>조회직원</th>
                        <th>고객번호</th>
                        <th>조회수</th>                        
                        <th>조회직원</th>
                        <th>신청번호</th>
                        <th>조회수</th>  
                    </tr>
                    @for($i=0; $i<10; $i++)
                    <tr align="center">
                        <td class="bold">{{ $i+1 }}</td>
                        <td class="" id="cust_info_save_id_{{ $i }}"></td>
                        <td class="" id="cust_info_no_{{ $i }}"></td>
                        <td class="text-right" id="cust_info_cnt_{{ $i }}"></td>

                        <td class="" id="loan_app_save_id_{{ $i }}"></td>
                        <td class="" id="loan_app_no_{{ $i }}"></td>
                        <td class="text-right" id="loan_app_cnt_{{ $i }}"></td>                        
                    </tr>
                    @endfor
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection


<!-- 자바스크립트 -->

@section('javascript')
<script>

   
</script>
@endsection