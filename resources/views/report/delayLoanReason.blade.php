@extends('layouts.master')


@section('content')
{{-- Content Wrapper. Contains page content --}}
<div class="col-12" >
    {{-- Main content--}} 
    <section class="content">	

        <div class="col-md-12 pl-0">
            <div class="card card-lightblue card-outline">
                <div class="box box-{{ config('view.box') }}">
                <form id="form_{{ $result['listName'] }}">
                    @csrf
                    <input type="hidden" name="excelDownCd" id="excelDownCd{{ $result['listName'] }}">
                    <input type="hidden" name="excelUrl" id="excelUrl{{ $result['listName'] }}">
                    <input type="hidden" name="etc" id="etc{{ $result['listName'] }}">
                    <input type="hidden" name="pop_manager_code" id="pop_manager_code">
                    <input type="hidden" name="pop_status" id="pop_status">
                    <input type="hidden" name="pop_delay_rsn_cd" id="pop_delay_rsn_cd">

                    <div class="card-header pt-2">
                        <div class="card-tools form-inline" id="searchBox" style=" justify-content: flex-end;">
                        <div id="button-area">
                        <span class="pr-2" id="update_time"></span>
                        @if(Func::funcCheckPermit("H022"))
                        <button type="button" class="btn btn-sm btn-success" onclick="excelDownModal('/report/{{ $result['listName'] }}excel','form_{{ $result['listName'] }}')">엑셀다운</button>
                        @endif
                        </div>
                            <div class="mr-1 mb-1 mt-1" ></div> 
                                <select class="form-control form-control-sm selectpicker mr-1 mb-1 mt-1" id="manager_code" name="manager_code" >
                                    <option value="">지점</option>
                                    {{ Func::printOption($array_branch,Func::nvl($result['manager_code'],""))  }}
                                </select>
                                <select class="form-control form-control-sm selectpicker mr-1 mb-1 mt-1" >
                                    <option value="info_date">기준일</option>
                                </select>
                                <div class="input-group date mt-0 mb-0 datetimepicker" id="info_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#info_date" id="info_date_id" name="info_date" DateOnly="true"  value="{{ $result['info_date'] ?? date("Y-m-d") }}" size="6">
                                    <div class="input-group-append" data-target="#info_date" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                                <div class="input-group mt-1 mb-1  input-group-sm ml-1 align-center">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-default" onclick="getDelayLoanList()" ><i class="fa fa-search"></i></button>
                                        <button type="button" class="btn btn-default" onclick="delayLoanReasonBatch()"><i class="fas fa-sync"></i></button>
                                    </div>
                                </div>
                        </div>
                    </div>

                        <div style="height:700px;">
                            <div id="loading-area"></div>
                            <div id="data-area" class="card-body p-0 m-0" >
                                <table id="reason_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
                                <thead class="text-center">
                                    <tr>
                                        <th class="pr-1 pl-1" rowspan="2">지점명</th>
                                        <th class="pr-2 pl-2" rowspan="2">채권상태</th>
                                        @foreach($array_delay_cd as $cd => $name)
                                            <th class="pr-1 pl-1" colspan="2">{{"(".$cd.")".$name}}</th>
                                        @endforeach
                                        <th class="pr-1 pl-1" colspan="2">합계</th>
                                        <th class="pr-1 pl-1" rowspan="2">연체율</th>
                                        <th class="pr-1 pl-1" rowspan="2">대출잔액</th>
                                    </tr>
                                    <tr>
                                        @foreach($array_delay_cd as $cd => $name)
                                            <th class="{{$cd}}_delay_cd">건수</th>
                                            <th class="{{$cd}}_delay_cd">연체금액</th>
                                        @endforeach
                                        <th>건수</th>
                                        <th>연체금액</th>
                                    </tr>
                                </thead>
                                </table>

                            </div>
                        </div>

                        <div class="card-body p-0" id="footTable" style="max-height:450px; overflow-y: auto;">
                        </div>

                        <!-- 일괄처리 & 페이지 버튼 -->
                        <div class="card-footer mb-0 p-3">
                        </div>

                    </div>
                </div>
                </form>
            </div>
        </div>

    </section>
</div>


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
                </form>
            </div>
            <div class="modal-footer">
                <span class="form-control-sm col-8 text-red" id='excelMsg' style="display:none;">* 다운로드 중 입니다. </span> 
                <button type="button" class="btn btn-sm btn-secondary" id="closeBtn" data-dismiss="modal" aria-hidden="true">닫기</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="excelDown('form_{{$result['listName']}}');">다운로드</button>
            </div>
        </div>
    </div>
</div>


@endsection
@section('javascript')
{{-- <script src="/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script> --}}



<script src="/plugins/datatables/jquery.dataTables.js"></script>
<script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script> 
<script src="/plugins/datatables-rowgroup/js/dataTables.rowGroup.js"></script> 
<script src="/plugins/datatables-fixedcolumns/js/dataTables.fixedColumns.js"></script> 
<script src="/plugins/datatables-responsive/js/dataTables.responsive.js"></script> 
<script src="/plugins/datatables-responsive/js/responsive.bootstrap4.js"></script> 

<script src="/plugins/datatables-buttons/js/dataTables.buttons.js"></script> 
<script src="/plugins/datatables-buttons/js/buttons.bootstrap4.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.html5.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.print.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.colVis.js"></script>

<link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.css">
<link rel="stylesheet" href="/plugins/datatables-rowgroup/css/rowGroup.bootstrap4.css">
<link rel="stylesheet" href="/plugins/datatables-fixedcolumns/css/fixedColumns.bootstrap4.css">
<link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.css">
<link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.css">


<script src="/plugins/jszip/jszip.min.js"></script>
<script src="/plugins/pdfmake/pdfmake.min.js"></script>
<script src="/plugins/pdfmake/vfs_fonts.js"></script>
<style>

</style>
<script>


function delayLoanReasonBatch()
{
    if(!confirm('기준일자 데이터를 재생성 하시겠습니까?'))
    {
        return; 
    }

    if(ccCheck()) return;
    // 기준일자 데이터 재생성 범위 수정(전일자까지만 재생성 가능)
    var info_date = $('#info_date_id').val();
    var bas_de = info_date.replace(/-/gi,"");
    var enable_date = "{{ date('Ymd', strtotime( '-1 days') ) }}";
    //alert(enable_date);
    
    if(enable_date > bas_de)
    {
        getDelayLoanList();
        return;
    }
    $('#data-area').hide();
    $("#loading-area").append(loadingString);   

    $.get(
        "/report/delayloanreasonbatch/"+info_date, 
        "", 
        function(data) {
            globalCheck = false;
            $('#loading-area').empty();
            $('#data-area').show();
            getDelayLoanList();
    });
}

getDelayLoanList();

function getDelayLoanList()
{
    $('#update_time').empty();
    var info_date    = $('#info_date_id').val();
    var manager_code = $('#manager_code').val();
    var table = $('#reason_table').DataTable( {
        destroy: true,
        scrollY:        "600px",
        scrollX:        true,
        scrollCollapse: true,
        lengthChange:   false,          // 표시 건수기능 숨기기
        searching:      false,          // 검색 기능 숨기기
        ordering:       false,          // 정렬 기능 숨기기
        info:           false,          // 정보 표시 숨기기
        paging:         false,          // 페이징 기능 숨기기
        fixedColumns:   {
            leftColumns: 2,
        },
        ajax:   {
                "type" : "get",
                "url" : "/report/{{$result['listName']}}list?info_date="+info_date+"&manager_code="+manager_code,
                "dataType": "JSON"
                },

        rowsGroup: [0,1],
        createdRow: function(row, data, dataIndex) {
            // 마지막컬럼에 있는 save_time으로 생성시간 세팅해줌 
            if(dataIndex == 0)
            {
                var update_time = getTimestamp(data[data.length-1]);
                if(update_time)
                {
                    $('#update_time').html("생성시간 : "+update_time);
                }
            }
            if(data[0].indexOf('총합계') != -1 || data[1].indexOf('합계') != -1)
            {
                $(row).css('background-color', '#f4f6f9');
            }
        },

        columnDefs: [
                { targets: [0,1], createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css('background-color', '#f4f6f9');
                        $(td).addClass('text-center');
                        $(td).addClass('pr-2');
                        $(td).addClass('pl-2');
                    }
                },
                { targets: '_all',createdCell: function (td, cellData, rowData, row,col ) {
                        if(col >= 2 && col < (rowData.length-5) && rowData[1] != "합계" )
                        {
                            $(td).addClass('hand');  
                        }
                        $(td).addClass('text-right');
                    }
                }
        ],
    });

    $('#reason_table').off('click');
    $('#reason_table').on('click', 'td', function() {
        var arrValue  = table.row(this).data();
        console.log(arrValue);
        var col_idx   = $(this).index();
        var col_value = $(this).text().replaceAll(",","");
        var delay_rsn_cd =  $(table.column( col_idx ).header()).attr('class').substring(0,2);

        if(arrValue[1].indexOf('총합계') == -1 && arrValue[1].indexOf('합계') == -1 && col_value > 0 &&  col_idx < (arrValue.length-5))
        {
            $('#pop_manager_code').val(arrValue[0]);
            $('#pop_status').val(arrValue[1]);
            $('#pop_delay_rsn_cd').val(delay_rsn_cd);
            getLoanList();
        }
    });
}

function getLoanList()
{
    $('#form_delayloanreason').attr("action", '/report/delayloanreasonpop');
    $('#form_delayloanreason').attr("method", "post");
    $('#form_delayloanreason').attr("target", "popOpen");
    window.open('계약검색','popOpen','right=0,top=0,height=680,width=900');
    $('#form_delayloanreason').submit();
}

</script>


<style>
.DTFC_LeftBodyLiner { 
    overflow-x: hidden; 
}
table.dataTable.no-footer {
    border-bottom: 0px;
}
</style>




@endsection