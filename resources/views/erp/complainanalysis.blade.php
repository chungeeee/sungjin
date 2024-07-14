@extends('layouts.master')
@section('content')

<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.2.0/chart.js" integrity="sha512-opXrgVcTHsEVdBUZqTPlW9S8+99hNbaHmXtAdXXc61OUU6gOII5ku/PzZFqexHXc3hnK8IrJKHo+T7O4GRIJcw==" crossorigin="anonymous"></script>

<!-- Main content -->
<section class="content">
<form id="complainanalysis_form">
@csrf
<input type="hidden" name="excelDownCd" id="excelDownCdcomplainanalysis">
<input type="hidden" name="excelUrl" id="excelUrlcomplainanalysis">
<input type="hidden" name="etc" id="etccomplainanalysis">
<input type="hidden" name="down_div" id="down_divcomplainanalysis">
<input type="hidden" name="excel_down_div" id="excel_down_divcomplainanalysis">
<input type="hidden" name="down_filename" id="down_filenamecomplainanalysis">

<div class="container-fluid">
    <div style="display:none" id="headerArea">
        <h1 id="headerTitle"></h1>
    </div>

    <div class="card-tols form-inline" style="justify-content: flex-end" id="searchArea">
        <div class="input-group date datetimepicker" id="div_search_sdate" data-target-input="nearest" style="margin-bottom: 8px; margin-left:8px; margin-right:-55px;">
            <input type="text" class="form-control form-control-sm col-sm-6" DateOnly='true' id="search_sdate" name="search_sdate" data-target="#div_search_sdate" value="{{ $sdate }}"/>
            <div class="input-group-append" data-target="#div_search_sdate" data-toggle="datetimepicker">
                <div class="input-group-text text-xs text-center"><i class="fa fa-calendar"></i></div>
            </div>
        </div>
        ~
        <div class="input-group date datetimepicker" id="div_search_edate" data-target-input="nearest" style="margin-bottom: 8px; margin-left:8px; margin-right:-55px;">
            <input type="text" class="form-control form-control-sm col-sm-6" DateOnly='true' id="search_edate" name="search_edate" data-target="#div_search_edate" value="{{ $edate }}"/>
            <div class="input-group-append" data-target="#div_search_edate" data-toggle="datetimepicker">
                <div class="input-group-text text-xs text-center"><i class="fa fa-calendar"></i></div>
            </div>
        </div>
        <div style="z-index: 99">
            <button type="button" class="btn btn-sm btn-info float-right ml-2" style="margin-bottom:7px;" onclick="searchComplain();">기간검색</button>
        </div>
        <div style="z-index: 99">
            <button type="button" class="btn btn-sm btn-success" style="margin-bottom:7px; margin-left:7px" onclick="excelDownModal('/erp/complainexcel', 'complainanalysis_form')" id="">엑셀다운</button>
        </div>
        <div style="z-index: 99">
            <button type="button" class="btn btn-sm btn-info float-right ml-2" style="margin-bottom:7px;" onclick="doPrint()">인쇄</button>
        </div>
    </div>
    
    <div id="printArea">
        <div class="row">
            <h5><i class="fas fa-file-alt m-2"></i>총괄</h5>
            <div class="col-md-12">
                <div class="card card-lightblue">
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover loan-info-table card-secondary card-outline table-bordered" >
                            <colgroup>
                                <col width="11%"/>
                                <col width="11%"/>
                                <col width="11%"/>
                                <col width="11%"/>
                                <col width="11%"/>
                                <col width="11%"/>
                                <col width="11%"/>
                                <col width="11%"/>
                                <col width="11%"/>
                            </colgroup>
                            <thead>
                                <tr class="text-center">
                                    <th class="pl-1" rowspan=2>구분</th>
                                    <th colspan=4>건수</th>
                                    <th colspan=3>증감률(소수 둘째자리 반올림)</th>
                                </tr>
                                <tr class="text-center">
                                    <th>기준기간</th>
                                    <th>직전동기</th>
                                    <th>전년동기</th>
                                    <th>전전년동기</th>
                                    <th>직전동기</th>
                                    <th>전년동기</th>
                                    <th>전전년동기</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="bg-pastel-red text-bold">
                                    <td class="text-center pl-1">합계</td>
                                    <td class="text-center">{{ $totalOrgn."건" }}</td>
                                    <td class="text-center">{{ $lastCount."건" }}</td>
                                    <td class="text-center">{{ $lastYearCount."건" }}</td>
                                    <td class="text-center">{{ $beforeLastCount."건" }}</td>
                                    <td class="text-center">{{ ($lastCount != 0)?(round((($totalOrgn-$lastCount) / $lastCount) * 100, 2))."%":'' }}</td>
                                    <td class="text-center">{{ ($lastYearCount != 0)?(round((($totalOrgn-$lastYearCount) / $lastYearCount) * 100, 2))."%":'' }}</td>
                                    <td class="text-center">{{ ($beforeLastCount != 0)?(round((($totalOrgn-$beforeLastCount) / $beforeLastCount) * 100, 2))."%":'' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" style="margin-top: 30px;">
            <h5><i class="fas fa-file-alt m-2"></i>처리현황</h5>
            <div class="col-md-12">
                <div class="card card-lightblue">
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover loan-info-table card-secondary card-outline table-bordered">
                            <colgroup>
                                <col width="17%"/>
                                <col width="13%"/>
                                <col width="13%"/>
                                <col width="18%"/>
                                <col width="13%"/>
                                <col width="13%"/>
                                <col width="13%"/>
                            </colgroup>
                            <thead>
                                <tr class="text-center">
                                    <th colspan=3>요청내역</th>
                                    <th colspan=4>처리내역</th>
                                </tr>
                                <tr class="text-center">
                                    <th>구분</th>
                                    <th>건수</th>
                                    <th>구성비</th>
                                    <th>구분</th>
                                    <th>건수</th>
                                    <th>구성비</th>
                                    <th>평균소요일수</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach( $configArr['complain_app_orgn_cd'] as $key => $val )
                                    <tr>
                                        <td class="text-center pl-1" rowspan="3" style="vertical-align: middle">{{ $val }}</td>
                                        <td class="text-center" rowspan="3" style="vertical-align: middle">
                                            {{ Func::nvl($orgnCount[$key], 0) }}
                                            @if (isset($orgnPrcCount[$key]['N']))
                                                <br>{{ $orgnPrcCount[$key]['N']."건 처리중" }}
                                            @endif
                                            @if (isset($orgnPrcCount[$key]['A']))
                                                <br>{{ $orgnPrcCount[$key]['A']."건 접수상태" }}
                                            @endif
                                        </td>
                                        <td class="text-center" rowspan="3" style="vertical-align: middle">{{ Func::nvl($orgnRate[$key], 0)."%" }}</td>
                                        <td class="text-center">{{ Func::getArrayName($resultArr, 'Y') }}</td>
                                        <td class="text-center">{{ Func::nvl($orgnActionCount[$key]['Y'], 0) }}</td>
                                        <td class="text-center">{{ Func::nvl($orgnPrcRate[$key]['Y'],0)."%" }}</td>
                                        <td class="text-center">{{ isset($dateCount[$key]['Y'])?round($dateCount[$key]['Y'] / $orgnActionCount[$key]['Y'], 0)."일":'0일' }}</td>
                                    </tr>
                                    <tr>
                                        <td class="text-center">{{ Func::getArrayName($resultArr, 'N') }}</td>
                                        <td class="text-center">{{ Func::nvl($orgnActionCount[$key]['N'], 0) }}</td>
                                        <td class="text-center">{{ Func::nvl($orgnPrcRate[$key]['N'],0)."%" }}</td>
                                        <td class="text-center">{{ isset($dateCount[$key]['N'])?round($dateCount[$key]['N'] / $orgnActionCount[$key]['N'], 0)."일":'0일' }}</td>
                                    </tr>
                                    <tr class="bg-pastel-red text-bold">
                                        <td class="text-center">계</td>
                                        <td class="text-center">{{ Func::nvl($orgnPrcCount[$key]['Y'],0)."건" }}</td>
                                        <td class="text-center">{{ Func::nvl($totalOrgnPrcRate[$key]['Y'],0)."%" }}</td>
                                        <td class="text-center">{{ isset($orgnDateCount[$key])?round($orgnDateCount[$key] / $orgnPrcCount[$key]['Y'], 0)."일":'0일' }}</td>
                                    </tr>
                                @endforeach
                                <tr class="bg-pastel-red text-bold">
                                    <td class="text-center">총계</td>
                                    <td class="text-center">{{ $totalOrgn."건" }}</td>
                                    <td class="text-center">100%</td>
                                    <td class="text-center">총계</td>
                                    <td class="text-center">{{ $totalPrcOrgn."건" }}</td>
                                    <td class="text-center">100%</td>
                                    <td class="text-center">{{ ($totalPrcOrgn != 0)?round(($totalDays / $totalPrcOrgn), 0)."일":'0일' }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="row" style="margin-top: 30px; page-break-before:always;">
            <h5><i class="fas fa-file-alt m-2"></i>부서별 담당자별 현황</h5>
            <div class="col-md-12">
                <div class="card card-lightblue">
                    <div class="card-body p-0">
                        <table class="table table-sm table-hover loan-info-table card-secondary card-outline table-bordered">
                            <thead>
                                <tr class="text-center">
                                    <th rowspan="2">팀</th>
                                    <th rowspan="2">처리담당자</th>
                                    <th rowspan="2">구성비</th>
                                    <th colspan="{{ sizeof($configArr['complain_app_orgn_cd']) }}">발생건수</th>
                                    <th colspan="{{ sizeof($resultArr) }}">조치건수</th>
                                </tr>
                                <tr class="text-center">
                                    @foreach ( $configArr['complain_app_orgn_cd'] as $key => $val)
                                        <th>{{ $val }}</th>
                                    @endforeach
                                    @foreach ( $resultArr as $key => $val)
                                        <th>{{ $val }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach( $branch as $branchCode => $branchName )
                                    @foreach ($managerArr as $managerId => $managerBranch)
                                        @if ($managerBranch == $branchCode)
                                            <tr>
                                                <td class="text-center pl-1">{{ $branchName }}</td>
                                                <td class="text-center">{{ Func::getArrayName(Func::getUserId(), $managerId) }}</td>
                                                <td class="text-center">{{ $managerRate[$managerId] ?? '0' }}%</td>
                                                @foreach ( $configArr['complain_app_orgn_cd'] as $key => $val)
                                                    <td class="text-center">{{ Func::nvl($orgnArr[$managerId][$key], 0) }}</td>
                                                @endforeach
                                                @foreach ( $resultArr as $key => $val)
                                                    <td class="text-center">{{ Func::nvl($actionCount[$managerId][$key],0) }}</td>
                                                @endforeach
                                            </tr>
                                        @endif
                                    @endforeach
                                    <tr class="bg-pastel-red text-bold">
                                        <td class="text-center pl-1" colspan="2">소계</td>
                                        <td class="text-center">{{ $prcRate[$branchCode]."%" }}</td>
                                        @foreach ( $configArr['complain_app_orgn_cd'] as $key => $val)
                                            <td class="text-center">{{ Func::nvl($prcCount[$branchCode][$key], 0) }}</td>
                                        @endforeach
                                        @foreach ( $resultArr as $key => $val)
                                            <td class="text-center">{{ Func::nvl($prcActionCount[$branchCode][$key],0) }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div style="margin-top:30px; page-break-before: always;">
            <h5><i class="fas fa-file-alt m-2"></i>접수기관별 추이</h5>
            <div class="row" style=" display:flex; justify-content:center;">
                <canvas id="orgnChart" style="width:90%; height:500px;"></canvas>
            </div>
        </div>

        <div style="margin-top:30px">
            <h5><i class="fas fa-file-alt m-2"></i>팀별 추이</h5>
            <div class="row" style=" display:flex; justify-content:center;">
                <canvas id="branchChart" style="width:90%; height:500px; "></canvas>
            </div>
        </div>
    </div>
</form>

</section>

<!-- 엑셀 다운 모달 -->
<div class="modal fade" id="excelDownModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">엑셀 다운로드</h5>
                <button type="button" class="close" id="excelClose"data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="excelForm" name="excelForm" method="post" enctype="multipart/form-data" action="" onSubmit="return false;">
                    @csrf
                    <div class="row mt-1">
                        <span class="form-control-sm col-3" for="reason" style="font-weight:700; padding-top:3px; width:30px;">다운로드 사유 : </span> 
                        <select class="form-control form-control-sm text-xs col-md-6" name="excel_down_cd" id="excel_down_cd" onchange="etc_check();">
                            <option value=''>선택</option>
                                {{ Func::printOption(Func::getConfigArr("excel_down_cd")) }} 
                        </select>
                        <input class="form-control form-control-sm text-xs col-md-6"type="text" id="etc" style="display:none;margin-left:120px;"placeholder="사유를 입력해주세요">
                    </div>
                    <div class="row mt-1">
                        <div class="icheck-success d-inline">
                            <span class="form-control-sm col-3" for="reason" style="font-weight:700; margin-top:10px;">다운로드 구분 : </span> 
                            <label class="radio-block">
                            <input type="radio" name="radio_div" value="now" checked > 현재 페이지 &nbsp;
                            </label>
                        </div>
                        <div class="icheck-success d-inline">
                            <label class="radio-block">
                            <input type="radio" name="radio_div" value="all" > 전체 페이지 &nbsp;
                            </label>
                        </div>
                    </div>
                    <div class="row mt-1">
                        <div class="icheck-success d-inline">
                            <span class="form-control-sm col-3" for="execution" style="font-weight:700; margin-top:10px;">다운로드 실행구분 : </span> 
                            <label class="radio-block" style="width:110px; padding-left: 5px!important;">
                                <input type="radio" name="excel_down_div" id="reservation" value="S" checked onchange="input_filename()"> 예약실행 &nbsp;
                            </label>
                        </div>
                        <div class="icheck-success d-inline">
                            <label class="radio-block" style="padding-left: 5px!important;">
                                <input type="radio" name="excel_down_div" id="realtime" value="E" onchange="input_filename()"> 바로실행 &nbsp;
                            </label>
                        </div>
                        <input class="form-control form-control-sm text-xs col-md-6"type="text" id="down_filename" style="margin-left:120px;"placeholder="다운받을 파일명을 입력해주세요">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <span class="form-control-sm col-8 text-red" id='excelMsg' style="display:none;">* 다운로드 중 입니다. </span> 
                <button type="button" class="btn btn-sm btn-secondary" id="closeBtn" data-dismiss="modal" aria-hidden="true">닫기</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="excelDown('complainanalysis_form');">다운로드</button>
            </div>
        </div>
    </div>
</div>

<!-- /.content -->
@endsection

@section('javascript')
<script>

//차트 1 (접수기관별 추이)
var no = 0;
var officeArr = [];
var lineDataCount = [];
var barDataCount = [];
var branch = @JSON($branch);
var configArr = @JSON($configArr);
var chartDates = @JSON($chartDates);
var lineChartCount = @JSON($lineChartCount);
var barChartCount = @JSON($barChartCount);
officeArr = configArr['complain_app_orgn_cd'];


var ctx = document.getElementById("orgnChart");
var orgnChart = new Chart(ctx, {
    type : "line",
    data : {
          
    }
    , options : {
        responsive: false,
        spanGaps : true,
        scales : {
            yAxes : [{
                ticks : {
                    beginAtZero: true,
                    scaleOverride : true,
                    scaleStartValue : 0 
                    }
            }]
        }
    }
});

//dataset에 들어갈 data 배열
for(i in chartDates){
    for(code in officeArr)
    {
        if (lineChartCount[chartDates[i].replace('-','')] === undefined) {
            lineChartCount[chartDates[i].replace('-','')] = {}
        }
        if (lineDataCount[code] === undefined) {
            lineDataCount[code] = {}
        }
        if(lineChartCount[chartDates[i].replace('-','')][code]){
            lineDataCount[code][chartDates[i]] = Number(lineChartCount[chartDates[i].replace('-','')][code]);
        }
        else{
            lineDataCount[code][chartDates[i]] = 0;
        }
    }
}

//datasets 설정
for(code in officeArr)
{
    color = no*100
    color2 = no * 150
    if(color > 255){
        color = color % 255;
    }
    if(color2 > 255){
        color2 = color2 % 255;
    }
    orgnChart.data.datasets.push({
            label: officeArr[code],
            fill: true,
            lineTension: 0,
            backgroundColor: "transparent",
            borderColor: "rgba(100, "+color+", "+color2+", 1)",
            data: lineDataCount[code]
        });
    no++;
 }

 window.orgnChart.update();
//////////////////////////////////////////////////////////


//차트 2 (팀별 추이)
no = 0;
var ctx2 = document.getElementById("branchChart");
var branchChart = new Chart(ctx2, {
    type : "bar",
    data : {
          
    }
    , options : {
        responsive: false,
        scales : {
            yAxes : [{
                ticks : {
                    beginAtZero: true,
                    scaleOverride : true,
                    scaleStartValue : 0 
                    }
            }]
        }
    }
});

//dataset에 들어갈 data 배열
for(i in chartDates){
    for(code in branch)
    {
        if (barChartCount[chartDates[i].replace('-','')] === undefined) {
            barChartCount[chartDates[i].replace('-','')] = {}
        }
        if (barDataCount[code] === undefined) {
            barDataCount[code] = {}
        }
        if(barChartCount[chartDates[i].replace('-','')][code]){
            barDataCount[code][chartDates[i]] = Number(barChartCount[chartDates[i].replace('-','')][code]);
        }
        else{
            barDataCount[code][chartDates[i]] = 0;
        }
    }
}

//datasets 설정
for(code in branch)
{
    color = no*100
    color2 = no * 150
    if(color > 255){
        color = color % 255;
    }
    if(color2 > 255){
        color2 = color2 % 255;
    }
    branchChart.data.datasets.push({
            label: branch[code],
            fill: true,
            backgroundColor: "rgba(100, "+color+", "+color2+", 1)",
            borderColor: "rgba(100, "+color+", "+color2+", 1)",
            data: barDataCount[code]
        });
    no++;
 }

 window.branchChart.update();
//////////////////////////////////////////////////////////




function searchComplain()
{
    var sdate = $('#search_sdate').val();
    var edate = $('#search_edate').val();

    var sdt = new Date(sdate);
    var edt = new Date(edate);
    var dateDiff = Math.ceil((edt.getTime()-sdt.getTime())/(1000*3600*24));

    if(dateDiff > 365){
        alert("1년 이상의 기간은 검색이 불가능합니다.");
        return false;
    }

    location.replace("/erp/complainanalysis?search_sdate="+sdate+"&search_edate="+edate);
}


</script>
@endsection