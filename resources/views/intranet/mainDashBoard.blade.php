@extends('layouts.master')
@section('content')


<!-- Main content -->
<section class="content">
<div class="container-fluid">

    <div class="row">

        <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-0">
                <div class="d-flex justify-content-between">
                <h3 class="card-title font-weight-bold text-gray-dark text-sm">신용,담보대출 기표금액</h3>
                </div>
            </div>
            <div class="card-body">
                <div class="d-flex">
                    <p class="d-flex flex-column">
                        <span class="text-bold text-lg text-gray-dark">￦ {{ number_format($array_loan_graph['total_money']) }} <span class='text-sm font-weight-bold'>백만원</span></span>
                        <span>이번 달 대출액</span>
                    </p>

                    {{-- <p class="ml-auto d-flex flex-column text-right">
                    @if( $array_graph['tot_mny'] >= $array_graph['pre_mny'] )
                        <span class="text-success">
                        <i class="fas fa-arrow-up"></i> {{ ( $array_graph['pre_mny']>0 ) ? number_format(($array_graph['tot_mny']-$array_graph['pre_mny'])/$array_graph['pre_mny']*100, 2) : 0 }}%
                        </span>
                    @else
                    <span class="text-danger">
                        <i class="fas fa-arrow-down"></i> {{ ( $array_graph['pre_mny']>0 ) ? number_format(($array_graph['pre_mny']-$array_graph['tot_mny'])/$array_graph['pre_mny']*100, 2) : 0 }}%
                    </span>
                    @endif
                    <span class="text-muted">지난 달</span>
                    </p> --}}
                </div>
                <!-- /.d-flex -->

                <div class="position-relative mb-4">
                    <canvas id="loan-chart" height="200"></canvas>
                </div>

                <div class="d-flex flex-row justify-content-end">
                <span class="mr-2">
                    <i class="fas fa-square text-primary text-xs"></i> 신용대출
                </span>
                <span>
                    <i class="fas fa-square text-xs" style="color:#ced4da"></i> 담보대출
                </span>
                </div>
                
            </div>
            </div>
            <!-- /.card -->
        </div>

        <div class="col-lg-6">
        <div class="card" style="height:382.750px;">
            <div class="card-header border-0">
            <div class="d-flex justify-content-between">
                <h3 class="card-title font-weight-bold text-gray-dark text-sm">당월 예상 수입 현황</h3>
            </div>
        </div>
        <br>
        @if( Func::funcCheckPermit("I006") )
        <div class="card-body">
            <div class="d-flex">
                <p class="d-flex flex-column">
                    <table class="table table-xs table-bordered text-xs vertical m-0 p-0">
                        <colgroup>
                            <col width="20%"/>
                            <col width="20%"/>
                            <col width="20%"/>
                            <col width="20%"/>
                            <col width="20%"/>
                        </colgroup> 
                        <thead>
                            <tr class="text-center">
                                <th rowspan=2>당월 총 예상이자</th>
                                <th colspan=2>전체 이자현황</th>
                                <th rowspan=2>연체 미수금</th>
                                <th rowspan=2>미도래 미수금</th>
                            </tr>
                            <tr class="text-center">
                                <th>수금 이자</th>
                                <th>미수/미도래 이자</th>
                            </tr>
                        </thead>
                        <tr style="text-align:right;">
                            <td>51,242,500</td>
                            <td>51,242,500</td>
                            <td>51,242,500</td>
                            <td>51,242,500</td>
                            <td>51,242,500</td>
                        </tr>
                    </table>
                </p>
            </div>
            <br><br>
            <div class="d-flex">
                <p class="d-flex flex-column">
                    <table class="table table-xs table-bordered text-xs vertical m-0 p-0">
                        <colgroup>
                            <col width="12.5%"/>
                            <col width="12.5%"/>
                            <col width="12.5%"/>
                            <col width="12.5%"/>
                            <col width="12.5%"/>
                            <col width="12.5%"/>
                            <col width="12.5%"/>
                            <col width="12.5%"/>
                            <col width="12.5%"/>
                        </colgroup> 
                        <thead class="text-center">
                            <tr style="text-align:center;">
                                <th>약정일</th>
                                @isset($arr_contract_day)
                                @foreach ( $arr_contract_day as $contract_day )
                                <th>{{$contract_day}}일</th>
                                @endforeach
                                @endisset
                            </tr>
                        </thead>
                        <tr>
                            <td class="text-center">미수건수</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                        </tr>
                        <tr>
                            <td class="text-center">미수이자</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                        </tr>
                        <tr>
                            <td class="text-center">미도래이자</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                            <td class="text-right">1</td>
                        </tr>
                    </table>
                </p>
            </div>
            @endif
            <!-- /.d-flex -->
            </div>
        </div>
        <!-- /.card -->
    </div> 
</div>

<div class="row">
    <div class="col-lg-6">

        <div class="card">
        <div class="card-header border-0">
            <h3 class="card-title font-weight-bold text-gray-dark text-sm">공지사항 게시판</h3>
            <div class="card-tools">
            <a href="#" class="btn btn-tool btn-sm">
                <i class="fas fa-bars"></i>
            </a>
            </div>
        </div>
        <div class="card-body table-responsive p-0">
            <table class="table table-striped table-valign-middle table-hover table-condensed">
            <col width="10%"></col>
            <col width="7%"></col>
            <col width=""></col>
            <col width="30%"></col>
            <thead>
            <tr>
                <th class='p-2 pl-2 text-center'>번호</th>
                <th></th>
                <th class='p-2 pl-2'>제목</th>
                <th class='p-2 mr-2 text-center'>작성일시</th>
            </tr>
            </thead>
            <tbody>

            @forelse( $notice as $val )

            <tr role="button" onclick="location.href='/intranet/board/notice?no={{ $val->no }}'">
                <td class='p-2 pl-2 text-center'>{{ $val->no }}</td>
                <td class='p-0 pl-2 text-center'>{!! Func::dateTerm( substr($val->save_time,0,8), date("Ymd") )<=2 ? "<i class='fas fa-bullhorn m-0 text-awesome'></i>" : "" !!}</td>
                <td class='p-2 pl-2'>{{ ( $val->title=="" ) ? "제목없음" : $val->title }}</td>
                <td class='p-2 pr-2 text-center'>{{ Func::dateFormat($val->save_time) }}</td>
            </tr>

            @empty

            <tr>
                <td colspan=4 class="text-center p-4 text-muted bg-white">
                등록된 공지가 없습니다.
                </td>
            </tr>

            @endforelse

            </tbody>
            </table>
        </div>
        </div>
        <!-- /.card -->
    </div>

    <div class="col-lg-6">

        <div class="card">
            <div class="card-header border-0">
                <h3 class="card-title font-weight-bold text-gray-dark text-sm">읽지않은 메세지</h3>
                <div class="card-tools">
                <!--
                <a href="#" class="btn btn-tool btn-sm">
                    <i class="fas fa-download"></i>
                </a>
                -->
                <a href="#" class="btn btn-tool btn-sm">
                    <i class="fas fa-bars"></i>
                </a>
                </div>
            </div>
            <div class="card-body table-responsive p-0">

            <table class="table table-striped table-valign-middle table-hover table-condensed">
                <col width="15%"></col>
                <col width=""></col>
                <col width="15%"></col>
                <col width="25%"></col>
                <thead>
                <tr>
                    <th class='p-2 pl-2 text-center'>번호</th>
                    <th class='p-2 pl-2'>제목</th>
                    <th class='p-2 pl-2 text-center'>보낸이</th>
                    <th class='p-2 mr-2 text-center'>보낸시간</th>
                </tr>
                </thead>
                <tbody>

                @forelse( $message as $val )

                <tr role="button" onclick="setMsgForm({{$val->no}});">
                    <td class='p-2 pl-2 text-center'>

                    @if( $val->msg_type=="M" )
                        <i class="fas fa-envelope text-{{ $val->msg_level ?? 'gray' }}"></i>
                    @elseif ( $val->msg_type=="N" )
                        <i class="fas fa-bullhorn text-{{ $val->msg_level ?? 'gray' }}"></i>
                    @elseif ( $val->msg_type=="S" )
                        <i class="fas fa-bell text-{{ $val->msg_level ?? 'gray' }}"></i>
                    @endif

                    </td>
                    <td class='p-2'>{{ $val->title }}</td>
                    <td class='p-2 text-center'>{{ $val->send_id }}</td>
                    <td class='p-2 pr-2 text-center'>{{ Func::dateFormat($val->send_time) }}</td>
                    <!--
                    <small class="text-success mr-1">
                        <i class="fas fa-arrow-up"></i>
                        12%
                    </small>
                    12,000 Sold
                    -->

                </tr>

                @empty

                <tr>
                    <td colspan=4 class="text-center p-4 text-muted bg-white">
                    읽지 않은 메세지가 없습니다.
                    </td>
                </tr>

                @endforelse

                </tbody>
                </table>

            </div>
            </div>
            <!-- /.card -->

        </div>
    </div>
</div>
</section>
<!-- /.content -->

<form id="myMsgForm">
  @csrf
  <input type="hidden" id="mdiv" name="mdiv" value="recv">
  <input type="hidden" id="mtype" name="mtype" value="">
  <input type="hidden" id="msgNo" name="msgNo" value="">
</form>


@endsection






@section('javascript')
{{-- <script src="/plugins/chart.js/Chart.min.js"></script> --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- <script src="/dist/js/demo.js"></script> -->
<script>

$(function () {
  'use strict'

  var ticksStyle = {
    fontColor: '#495057',
    fontStyle: 'bold'
  }

  var mode = 'index'
  var intersect = true


  // 신규, 재대출+증액 기표건

  {{-- var $visitorsChart = $('#visitors-chart')
  // eslint-disable-next-line no-unused-vars
  var visitorsChart = new Chart($visitorsChart, {
    data: {
      //labels: ['21/01', '21/02', '21/03', '21/04', '21/05', '21/06', '21/07'],
      labels: ['{!! implode("','",$array_graph['labels']) !!}'],
      datasets: [{
        type: 'line',
        data: [{!! implode(", ",$array_graph['NR']['N']) !!}],
        backgroundColor: 'transparent',
        borderColor: '#007bff',
        pointBorderColor: '#007bff',
        pointBackgroundColor: '#007bff',
        fill: false
        // pointHoverBackgroundColor: '#007bff',
        // pointHoverBorderColor    : '#007bff'
      },
      {
        type: 'line',
        data: [{!! implode(", ",$array_graph['NR']['R']) !!}],
        backgroundColor: 'tansparent',
        borderColor: '#e0507b',
        pointBorderColor: '#e0507b',
        pointBackgroundColor: '#e0507b',
        //borderColor: '#ced4da',
        //pointBorderColor: '#ced4da',
        //pointBackgroundColor: '#ced4da',
        fill: false
        // pointHoverBackgroundColor: '#ced4da',
        // pointHoverBorderColor    : '#ced4da'
      }]
    },
    options: {
      maintainAspectRatio: false,
      tooltips: {
        mode: mode,
        intersect: intersect
      },
      hover: {
        mode: mode,
        intersect: intersect
      },
      legend: {
        display: false
      },
      scales: {
        yAxes: [{
          // display: false,
          gridLines: {
            display: true,
            lineWidth: '4px',
            color: 'rgba(0, 0, 0, .2)',
            zeroLineColor: 'transparent'
          },
          ticks: $.extend({
            beginAtZero: true,
            suggestedMax: 200
          }, ticksStyle)
        }],
        xAxes: [{
          display: true,
          gridLines: {
            display: false
          },
          ticks: ticksStyle
        }]
      }
    }
  }); --}}
    
    const ctx = $('#loan-chart');
    const dChart = {
            backgroundColor: '#ced4da',
            borderColor: '#ced4da',
            data: [{!! implode(", ",$array_loan_graph['d_money']) !!}],
            label: '담보대출'
    }
    const cChart = {
            backgroundColor: '#007bff',
            borderColor: '#007bff',
            data: [{!! implode(", ",$array_loan_graph['c_money']) !!}],
            label: '신용대출',
    }
    {{-- const dCnt  = {
            type: 'line',
            backgroundColor: '#cd9ca0',
            borderColor: '#cd9ca0',
            data: [{!! implode(", ",$array_loan_graph['d_cnt']) !!}],
            label: '담보대출 건수',
    }
    const cCnt = {
            type: 'line',
            backgroundColor: '#bd5786',
            borderColor: '#bd5786',
            data: [{!! implode(", ",$array_loan_graph['c_cnt']) !!}],
            label: '신용대출 건수',
    } --}}
    const data = {
        labels:['{!! implode("','",$array_loan_graph['labels']) !!}'],
        datasets:[
            cChart,
            dChart,
            {{-- dCnt,
            cCnt --}}
        ]
    }
    const options = {
        maintainAspectRatio :false,//그래프의 비율 유지
        tooltips: {
            mode: mode,
            intersect: intersect
        },
        hover: {
            mode: mode,
            intersect: intersect
        },
        legend: {
            labels: {
                //fontColor: 'black'
                display: false,
            }
        },
        scales:{
            x:{ //x축값 누적
                stacked:true
            },
            y:{ //y축값 누적
                stacked:true,
                ticks: $.extend({
                    beginAtZero: true,

                    // Include a dollar sign in the ticks
                    callback: function (value) {
                    if (value >= 10000) {
                        value /= 10000
                        value += 'k'
                    }

                    return '￦' + value
                    }
                }, ticksStyle)
            }
        }
    }

    const myChart = new Chart(ctx, {
        type:'bar',
        data:data,
        options:options
    });

  // 신용,담보대출 기표금액
   /*var $loanChart = $('#loan-chart')
    // eslint-disable-next-line no-unused-vars
    var loanChart = new Chart($loanChart, {
        type: 'bar',
        data: {
        labels: ['{!! implode("','",$array_loan_graph['labels']) !!}'],
        datasets: [
            {
            backgroundColor: '#ced4da',
            borderColor: '#ced4da',
            data: [{!! implode(", ",$array_loan_graph['d_money']) !!}],
            label: '담보대출',
            },
            {
            backgroundColor: '#007bff',
            borderColor: '#007bff',
            data: [{!! implode(", ",$array_loan_graph['c_money']) !!}],
            label: '신용대출',
            },
            {
            type: 'line',
            backgroundColor: '#cd9ca0',
            borderColor: '#cd9ca0',
            data: [{!! implode(", ",$array_loan_graph['d_cnt']) !!}],
            label: '담보대출 건수',
            },
            {
            type: 'line',
            backgroundColor: '#bd5786',
            borderColor: '#bd5786',
            data: [{!! implode(", ",$array_loan_graph['c_cnt']) !!}],
            label: '신용대출 건수',
            }    
        ]
        },
        options: {
        maintainAspectRatio: false,
        tooltips: {
            mode: mode,
            intersect: intersect
        },
        hover: {
            mode: mode,
            intersect: intersect
        },
        legend: {
            display: false
        },
        scales: {
            yAxes: [{
            // display: false,
            stacked:true,
            gridLines: {
                display: true,
                lineWidth: '4px',
                color: 'rgba(0, 0, 0, .2)',
                zeroLineColor: 'transparent'
            },
            ticks: $.extend({
                beginAtZero: true,

                // Include a dollar sign in the ticks
                callback: function (value) {
                if (value >= 10000) {
                    value /= 10000
                    value += 'k'
                }

                return '￦' + value
                }
            }, ticksStyle)
            },],
            xAxes: [{
            display: true,
            gridLines: {
                display: false
            },
            ticks: ticksStyle
            }]
        }
        }
    }); */
    


});




function setMsgForm(no)
  {
    $('#msgNo').val(no);
    $('#myMsgForm').attr("action", "/intranet/msgpop");
    $('#myMsgForm').attr("method", "post");
    $('#myMsgForm').attr("target", "msgInfo");
    
    window.open("", "msgInfo", "width=600, height=800, scrollbars=no");
    $("#myMsgForm").submit();

  }



</script>

<style>
.text-right {
    vertical-align: right;
}
</style>


<script>
@if(session('warning'))
alert("{{ session('warning') ?? '' }}");
location.href = "/intranet/myinfo?tab=pwd";
@endif
</script>
@endsection