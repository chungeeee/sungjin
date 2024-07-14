@extends('layouts.master')
@section('content')
@include('inc/list')

<div style="padding-left:10px; padding-top:10px; display:" id='incCheck'>
    <i class="fas fa-align-justify"></i> 전체 건수 합계
    <div class="card card-lightblue card-outline" style='width:600px'>
        <div class="card-body p-2">
            <table class="table table-sum text-center">
                <tbody>
                    <tr class='text-bold' style='background-color:rgba(0, 0, 0, 0.05);'>
                        <td>전체건수 : <span id="total_cnt"></span>건</td>
                        <td id='all_cnt'>접촉시도건수</td>
                        <td id='search_cnt'>접촉건수</td>
                    </tr>
                    <tr>
                        <td >규정 준수 검수<br>(접촉 : 일일2회 / 접촉시도 : 일일 8회)</td>
                        <td id='try1'></td>
                        <td id='con1'></td>
                    </tr>
                    <tr>
                        <td>모니터링대상</td>
                        <td id='try2'></td>
                        <td id='con2'></td>
                    </tr>
                    <tr>
                        <td>중요모니터링대상<br>(접촉 : 일일7회 / 접촉시도 : 일일 15회)</td>
                        <td id='try3'></td>
                        <td id='con3'></td>
                    </tr>
                    <tr>
                        <td>일일 최고횟수</td>
                        <td id='max_try'></td>
                        <td id='max_con'></td>
                    </tr>
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