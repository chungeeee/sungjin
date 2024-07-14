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
              <h3 class="card-title font-weight-bold text-gray-dark text-sm">주요현장</h3>
            </div>
          </div>
          <div class="card-body">
            <div class="d-flex">
              <p class="d-flex flex-column">
                <span class="text-bold text-lg text-gray-dark">{{ number_format($array_graph['tot_cnt']) }}<span class='text-sm font-weight-bold ml-1'>건</span></span>
                <span>이번 달 현장</span>          
              </p>
              <p class="ml-auto d-flex flex-column text-right">

                @if( $array_graph['tot_cnt'] >= $array_graph['pre_cnt'] )
                  <span class="text-success">
                    <i class="fas fa-arrow-up"></i> {{ ( $array_graph['pre_cnt']>0 ) ? number_format(($array_graph['tot_cnt']-$array_graph['pre_cnt'])/$array_graph['pre_cnt']*100, 2) : 0 }}%
                  </span>
                @else
                  <span class="text-danger">
                    <i class="fas fa-arrow-down"></i> {{ ($array_graph['pre_cnt'] >0 ) ? number_format(($array_graph['pre_cnt']-$array_graph['tot_cnt'])/$array_graph['pre_cnt']*100, 2) : 0 }}%
                  </span>
                @endif

                <span class="text-muted">지난 달</span>
              </p>
            </div>
            <!-- /.d-flex -->

            <div class="position-relative mb-4">
              <canvas id="visitors-chart" height="200"></canvas>
            </div>

            <div class="d-flex flex-row justify-content-end">
                <span class="mr-2">
                  <i class="fas fa-square text-primary text-xs"></i>
                </span>
                <span class="mr-2">
                  <i class="fas fa-square text-xs" style="color:#e0507b"></i>
                </span>
                <span class="mr-2">
                  <i class="fas fa-square text-xs" style="color:#ffca2b"></i>
                </span>
            </div>      
          </div>
        </div>
        <!-- /.card -->
      </div>

      <div class="col-lg-6">
        <div class="card">
            <div class="card-header border-0">
              <div class="d-flex justify-content-between">
                <h3 class="card-title font-weight-bold text-gray-dark text-sm">정기발주</h3>
              </div>
            </div>
            <div class="card-body">
              <div class="d-flex">
                <p class="d-flex flex-column">
                  <span class="text-bold text-lg text-gray-dark">￦ {{ number_format($array_graph['tot_mny']) }} <span class='text-sm font-weight-bold'>만원</span></span>
                  <span>이번 달 발주액</span>
                </p>
                <p class="ml-auto d-flex flex-column text-right">

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

                </p>
              </div>
              <!-- /.d-flex -->

            <div class="position-relative mb-4">
              <canvas id="inv-chart" height="200"></canvas>
            </div>


            <div class="d-flex flex-row justify-content-end">
              <span class="mr-2">
                <i class="fas fa-square text-primary text-xs"></i> 
              </span>
              <span class="mr-2">
                <i class="fas fa-square text-gray text-xs"></i> 
              </span>
              <span class="mr-2">
                <i class="fas fa-square text-xs" style="color:#ced4da"></i> 
              </span>
            </div>
            
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
                    <td class='p-2 text-center'>{{ Func::getUserId($val->send_id)->name ?? '' }}</td>
                    <td class='p-2 pr-2 text-center'>{{ Func::dateFormat($val->send_time) }}</td>

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
<script src="/plugins/chart.js/Chart.min.js"></script>
<script>

  $(function () {
    'use strict'

    var ticksStyle = {
      fontColor: '#495057',
      fontStyle: 'bold'
    }

    var mode = 'index'
    var intersect = true


    // 주요자금
    var $visitorsChart = $('#visitors-chart');

    // eslint-disable-next-line no-unused-vars
    var visitorsChart = new Chart($visitorsChart, {
      data: {
        labels: ['{!! implode("','",$array_graph['labels']) !!}'],
        datasets: [{
          type: 'line',
          data: [{!! implode(", ",$array_graph['NR']['01']) !!}],
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
          data: [{!! implode(", ",$array_graph['NR']['02']) !!}],
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
        },
        {
          type: 'line',
          data: [{!! implode(", ",$array_graph['NR']['03']) !!}],
          backgroundColor: 'tansparent',
          borderColor: '#ffca2b',
          pointBorderColor: '#ffca2b',
          pointBackgroundColor: '#ffca2b',
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
    });

    var $invChart = $('#inv-chart');

    // eslint-disable-next-line no-unused-vars
    var invChart = new Chart($invChart, {
      type: 'bar',
      data: {
        labels: ['{!! implode("','",$array_graph['labels']) !!}'],
        datasets: [
          {
            backgroundColor: '#007bff',
            borderColor: '#007bff',
            data: [{!! implode(", ",$array_graph['SD']['01']) !!}],
          },
          {
            backgroundColor: '#e0507b',
            borderColor: '#e0507b',
            data: [{!! implode(", ",$array_graph['SD']['02']) !!}],
          },
          {
            backgroundColor: '#ced4da',
            borderColor: '#ced4da',
            data: [{!! implode(", ",$array_graph['SD']['03']) !!}],
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
                if (value >= 1000) {
                  value /= 1000
                  value += 'K'
                }

                return '￦' + value
              }
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
    });
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

<script>

@if(session('warning'))
  alert("{{ session('warning') ?? '' }}");
  location.href = "/intranet/myinfo?tab=pwd";
@endif

</script>

@endsection