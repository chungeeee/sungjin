@extends('layouts.master')


@section('content')


<?
    if(!isset($_COOKIE[$result['listName']]))
    {
        $_COOKIE[$result['listName']] = 10;
    }

    if(!isset($result['tabsSelect']))
    {
        $result['tabsSelect'] = '';
    }
?>

<script language='javascript'>

function getMarkup{{ $result['listName'] ?? '' }}() 
{
    var tabs = $("#tabsSelect{{ $result['listName'] ?? '' }}").val();
    
    var markup = "";
    markup = "<tr id='no_${no}' style='${line_style}' onclick='${onclick}'>";
    // checkbox 
    @if(Func::getArrayName($result,'checkbox'))
        markup+= "<td class='text-center'><input type='checkbox' name='listChk[]' id='listChk${"+"{{ $result['listName'] ?? ''}}"+"}' class='list-check' value='${"+"{{ $result['checkbox'] ?? ''}}"+"}'></td>";
    @endif
    @foreach($result['listTitle'] as $tabs=>$listTitle)
        @if($tabs!='common') if(tabs=="{{$tabs}}"){ @endif
            @foreach($listTitle as $key => $val)
                @if(isset($val[6]) && is_array($val[6]))
                markup+= "<td class='text-{{ $val[3] }} @if(isset($val[4]) && $val[4]!='') rightline @endif'>";
                        markup+= "{{html <?=$key?>}}";
                    @foreach ($val[6] as $k => $arr)
                        markup+= "<?=$arr[2]?>{{html <?=$k?>}}";
                    @endforeach
                markup+= "</td>";
                @else
                markup+= "<td class='text-{{ $val[3] }} comma @if(isset($val[4]) && $val[4]!='') rightline @endif'>{{html <?=$key?>}}</td>";
                @endif
            @endforeach
        @if($tabs!='common') } @endif
    @endforeach
    markup+= "</tr>";

    console.log(markup);
    return markup;
}

// 팝업창에서 호출해서 사용(submit)
function listRefresh()
{
    $('#isFirst{{ $result['listName'] }}').val('1');
    getDataList('{{ $result['listName'] }}', $("#nowPage{{ $result['listName'] }}").val(), '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());
}
// 날짜 버튼 지정
function setDateUser(mode, startDt, endDt)
{
    // 종료일이 있으면 오늘.
    if(endDt!='')
        $('#' + endDt).val('{{ date("Y-m-d") }}');
    switch(mode)
    {
        case 'today' :
            $('#' + startDt).val('{{ date("Y-m-d") }}');
            break;
        case 'yesterday' :
                $('#' + startDt).val('{{ date("Y-m-d", time()-86400) }}');
                $('#' + endDt).val('');
                break;
        case 'week' :
            $('#' + startDt).val('{{ date("Y-m-d", strtotime("last Monday", time()+86400)) }}');
            break;
        case 'month' :
            $('#' + startDt).val('{{ date("Y-m") }}-01');
            break;
    }
    return false;
}

function nameorder(order, element)
{
    $('.orderIcon').removeClass('fas fa-arrow-down');
    $('.orderIcon').removeClass('fas fa-arrow-up');
    if($('#listOrder{{ $result['listName'] }}').val() != order) {
            $('#listOrderAsc{{ $result['listName'] }}').val('asc');
            $(element).children('i').addClass('fas fa-arrow-up');
    } else {
        if($('#listOrderAsc{{ $result['listName'] }}').val() == 'asc')
        {
            $('#listOrderAsc{{ $result['listName'] }}').val('desc');
            $(element).children('i').addClass('fas fa-arrow-down');
        }
        else
        {
            $('#listOrderAsc{{ $result['listName'] }}').val('asc');
            $(element).children('i').addClass('fas fa-arrow-up');
        }
    }
	$('#listOrder{{ $result['listName'] }}').val(order);
	getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());
}
function select_cal_type(ob)
{
	var type = ob.value;
}

// 멀티검색
function multi_view()
{
    var view_tr = document.getElementById("row_select_multi").style.display;

    if(view_tr=="none")
    {
        document.getElementById("row_select_multi").style.display = "";
    }
    else
    {
        document.getElementById("row_select_multi").style.display = "none";
    }
}
</script>


{{-- Content Wrapper. Contains page content --}}
<div class="col-12" >

    {{-- Main content--}} 
    <section class="content">		
		
		    {{-- 서류함           --}} 
        @if(Func::getArrayName($result,'Tabs'))
            <nav id="tabsBox">
                <div class="nav nav-pills border-bottom-0" role="tablist">
                @foreach($result['Tabs']['tabsArray'] as $key=>$val)
                    <a class="nav-link @if ($result['Tabs']['tabsSelect']===$key) active @endif" id="tab{{ $key }}" style='margin-right:0px; cursor: pointer;' onClick='goTab("{{ $result['listAction'] }}", "{{ $key }}", "{{ $result['listName'] }}");'><span id='tab{{ $key }}'>{{ $val }}</span></a>
                @endforeach
                </div>
            </nav>
        @endif
        {{-- 서류함     끝      --}} 

        <div class="col-md-12 pl-0">
            <div class="card card-lightblue card-outline">
                <div class="box box-{{ config('view.box') }}">
                    <form class="form-horizontal" onsubmit="getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize()); return false;" method="post" name="form_{{ $result['listName'] }}" id="form_{{ $result['listName'] }}">
                    <input type="hidden" name="listName" id="listName{{ $result['listName'] }}" value="{{ $result['listName'] }}">
                    <input type="hidden" name="listOrder" id="listOrder{{ $result['listName'] }}">
                    <input type="hidden" name="listOrderAsc" id="listOrderAsc{{ $result['listName'] }}">
                    <input type="hidden" name="isFirst" id="isFirst{{ $result['listName'] }}" value="1">
                    @if(Func::getArrayName($result,'Tabs'))
                    <input type="hidden" name="tabsSelect" id="tabsSelect{{ $result['listName'] }}" value="{{ $result['Tabs']['tabsSelect'] }}">
                    <input type="hidden" name="tabsChange" id="tabsChange{{ $result['listName'] }}">
                    @endif
                    <input type="hidden" name="mode" id="GET_LIST{{ $result['listName'] }}">
                    <input type="hidden" name="nowPage" id="nowPage{{ $result['listName'] }}">
                    <input type="hidden" name="searchCnt" id="searchCnt{{ $result['listName'] }}">
                    <input type="hidden" name="customSearch" id="customSearch{{ $result['listName'] }}">
                    <input type="hidden" name="excelDownCd" id="excelDownCd{{ $result['listName'] }}">
                    <input type="hidden" name="excelUrl" id="excelUrl{{ $result['listName'] }}">
                    <input type="hidden" name="etc" id="etc{{ $result['listName'] }}">
                    <input type="hidden" name="down_div" id="down_div{{ $result['listName'] }}">
                    <input type="hidden" name="excel_down_div" id="excel_down_div{{ $result['listName'] }}">
                    <input type="hidden" name="down_filename" id="down_filename{{ $result['listName'] }}">
                    
                    {{ csrf_field() }}

                    @if(isset($result['hidden']))
                        @foreach($result['hidden'] as $key=>$val)
                            <input type="hidden" name='{{ $key }}' id='{{ $key }}{{ $result['listName'] }}' value='{{ $val }}'>
                        @endforeach
                    @endif
                    
                    {{--  box-header searchBox   --}} 
                    <div class="card-header pt-2">
                        {{-- 체크박스 클릭 시 리스트 출력 컬럼 추가 -----------------}}
                        @if(isset($result['checkboxListAdd']))
                            <div class="pt-2 ml-2" style="float:left;">
                                @foreach($result['checkboxListAdd'] as $key=>$val)
                                    <input type="checkbox" class="form-check-input" name="lists[{{$key}}]" id="lists[{{$key}}]" onclick="listAdd();" value="{{$val}}">
                                    <label class="form-check-label mr-4" for="lists[{{$key}}]">{{$val}} </label>
                                @endforeach
                            </div>
                        @endif

                        <div class="card-tools form-inline" id="searchBox" style=" justify-content: flex-end;">
                            {{-- 버튼 Array 추가 -------------------------------}}
                            @if(isset($result['buttonArray']))
                                <div class="mr-1 mb-1 mt-1">
                                @foreach($result['buttonArray'] as $i =>$btn)
                                    <button type="button" class="btn btn-sm {{ $btn['buttonArrayClass'] ?? 'btn-primary' }}" onclick="{!! $btn['buttonArrayAction'] !!}">{{ $btn['buttonArrayNm'] }}</button>
                                @endforeach
                                </div>
                            @endif
                            {{-- 버튼 Array 추가 끝 -------------------------------}}
                            {{-- 일자 검색 ------------------------------}}
                            @if(isset($result['searchDate']))
                                @foreach($result['searchDate'] as $i => $search)
                                    <select class="form-control form-control-sm selectpicker mr-1 mb-1 mt-1" 
                                    name="{{ $search['searchDateNm'] }}" id="{{ $search['searchDateNm'] }}">
                                        <option value="">{{ $search['searchDateTitle'] }}</option>
                                        {{ Func::printOption($search['searchDateArray'],Func::getArrayName($result,'searchDateSelect'))  }}
                                    </select>
                                        
                                    <div class="input-group date mt-0 mb-0 datetimepicker" id="searchDateStringStart" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#searchDateStringStart" name="{{ $search['searchDateNm'] }}String" id="{{ $search['searchDateNm'] }}String" DateOnly="true" @if ( isset($search['searchDateTxt']) ) value='{{ $search['searchDateTxt'] }}' @endif size="6">
                                        <div class="input-group-append" data-target="#searchDateStringStart" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                    
                                    @if ($search['searchDatePair'] === 'Y')
                                    <span class="ml-1 mr-1">-</span>
                                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-1 mt-1" id="searchDateStringEnd" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#searchDateStringEnd" name="{{ $search['searchDateNm'] }}StringEnd" id="{{ $search['searchDateNm'] }}StringEnd" DateOnly="true" @if ( isset($search['searchDateTxt']) ) value='{{ $search['searchDateTxt'] }}' @endif size="6">
                                        <div class="input-group-append" data-target="#searchDateStringEnd" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                        @if ($search['searchDateNoBtn'] != 'Y')
                                        <div class="btn-group mr-1 mb-1 mt-1">
                                            @if(isset($search['searchDateNoBtn']) && $search['searchDateNoBtn']=='YESTERDAY')
                                                <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("yesterday", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd")'>전날</button>
                                            @endif
                                        <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("today", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd")'>금일</button>
                                        <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("week", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd")'>금주</button>
                                        <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("month", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd")'>금월</button>
                                        </div>
                                        @endif
                                    @else
                                        @if($search['searchDateNoBtn'] != 'Y')
                                            @if(isset($search['searchDateNoBtn']) && $search['searchDateNoBtn']=='YESTERDAY')
                                            <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("yesterday", "{{ $search['searchDateNm'] }}String")'>{{ $search['searchDateNoBtnNm'] }}</button>
                                            @else
                                            <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("today", "{{ $search['searchDateNm'] }}String")'>금일</button>
                                            @endif
                                        @endif
                                    @endif
                                @endforeach
                            @endif
                            {{-- 일자 검색 끝 ---------------------------}}
                            {{-- select 검색 ------------------------------}}
                            @if(isset($result['searchType']))
                                @foreach ($result['searchType'] as $select)
                                {{ $select['searchTypeSubject'] ?? '' }}
                                <select class="
                                    form-control form-control-sm selectpicker mr-1 mb-1 mt-1
                                    " name="{{ $select['searchTypeNm'] }}" id="{{ $select['searchTypeNm'] }}" {!! $select['searchTypeAction'] ?? '' !!}>
                                    @if(isset($select['searchTypeTitle']))
                                        <option value="">{{ $select['searchTypeTitle'] }}</option>
                                    @endif
                                    @if(isset($select['searchTypeArray']))
                                        {{ Func::printOption($select['searchTypeArray'],Func::getArrayName($select,'searchTypeVal'))  }}
                                    @endif
                                </select>
                                @endforeach
                            @endif
                            {{-- select 검색 끝 ---------------------------}}
                            {{-- 구간 상세 검색 -------------------------}}
                            @if(isset($result['rangeSearchDetail']))
                                <div class="input-group align-center">
                                    <select class=" form-control form-control-sm mr-1 selectpicker" name="rangeSearchDetail" id="rangeSearchDetail">
                                        <option value="">구간상세검색</option>
                                        {{ Func::printOption($result['rangeSearchDetail']['rangeSearchDetailArray'],Func::getArrayName($result,'rangeSearchDetailSet'))  }}
                                    </select>
                                    <div class="input-group" style="width:100px">
                                        <input type="text" name="sRangeSearchString" class="form-control form-control-sm" placeholder="{{ $result['rangeSearchDetail']['rangePlHolder'] ?? '' }}" id="sRangeSearchString" value="{{ $result['rangeSearchDetail']['sRangeSearchStringSet'] ?? '' }}" onkeyup="chkNumber();">
                                    </div>
                                    <span class="ml-1 mr-1">-</span>
                                    <div class="input-group mr-1" style="width:100px">
                                        <input type="text" name="eRangeSearchString" class="form-control form-control-sm" placeholder="{{ $result['rangeSearchDetail']['rangePlHolder'] ?? '' }}" id="eRangeSearchString" value="{{ $result['rangeSearchDetail']['eRangeSearchStringSet'] ?? '' }}" onkeyup="chkNumber();">
                                    </div>
                                </div>
                            @endif
                            {{-- 구간 상세 검색 끝 ----------------------}}
                            {{-- 상세 검색 ------------------------------}}
                            @if(isset($result['searchDetail']))
                                <div class="input-group">
                                    <select class="@if(0)form-control input-sm @else form-control form-control-sm @endif mr-1 mb-1 mt-1 selectpicker" name="searchDetail" id="searchDetail">
                                        <option value=""> 상세검색</option>
                                        {{ Func::printOption($result['searchDetail'],Func::getArrayName($result,'searchDetailSet'))  }}
                                    </select>
                                    <div class="input-group mt-0 mb-0 mr-1 mb-1 mt-1" style="width:120px;">
                                    <input type="text" name="searchString" class="form-control form-control-sm" placeholder="Search" id="searchString" value="{{ $result['searchStringSet'] ?? '' }}">
                                    </div>
                                </div>
                            @endif
                            {{-- 상세 검색 끝 ------------------------------}}
                            @if(!isset($result['Tabs'])|| (isset($result['Tabs']) && $result['Tabs']['tabsSelect'] !== 'X' && $result['Tabs']['tabsSelect'] !== 'E'))
                                
                                <div class="input-group mt-1 mb-1  input-group-sm ml-1 align-center">
                                    한페이지
                                    <input type="text" name="listLimit" class="form-control ml-1" placeholder="한페이지" id="listLimit{{ $result['listName'] }}" value="{{ $_COOKIE[$result['listName']] }}"  style="width: 50px;">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-default" onclick="getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize()); return false;"><i class="fa fa-search"></i></button>
                                        <button type="button" class="btn btn-default" onclick="@if(isset($result['isPopup']) && $result['isPopup']==='Y' && isset($result['popupListAction'])) {{ $result['popupListAction'] }} @else location.href='{{ $result['listAction'] }}';@endif"><i class="fas fa-sync"></i></button>
                                        @if(isset($result['plusButton']))
                                            <button type="button" class="btn btn-default" onclick="{!! $result['plusButton'] !!}"><i class="fa fa-plus-square text-primary"></i></button>
                                        @endif

                                        @if(isset($result['multiButton']))
                                            <button type="button" class="btn btn-default" onclick="{!! $result['multiButton'] !!}" title="멀티검색" style="height:29px;"><i class="fa fa-cog"></i></button>
                                        @endif
                                    </div>
                                </div>
                            @endif
                            @if (isset($result['Tabs']) && ($result['Tabs']['tabsSelect'] === 'X' || $result['Tabs']['tabsSelect'] === 'E'))
                                <div class="input-group mt-1 mb-1 input-group-sm">
                                    <div class="input-group-append">
                                        <button type="submit" class="btn btn-default" onclick="getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize()); return false;">실행</button>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if(isset($result['multiButton']))
                            <div class="input-group mt-1 mb-1 input-group-sm align-right" style="display:none; margin-left:540px;" id="row_select_multi">
                                <select class="form-control form-control-sm text-xs col-md-1" name="multi_detail" id="multi_detail">
                                    <option value=''>멀티검색</option>
                                        {{ Func::printOption($result['multiArray'])  }}
                                </select>
                                &nbsp;&nbsp;
                                <textarea name="multi_content" cols="40" rows="10" style="width:58%"></textarea>
                            </div>
                        @endif
                    </div>

                    {{-- box-header searchBox --}}
                    <div class="card-body p-0" id="dataTable">
                        <table class="table table-hover table-striped table-sm mb-1" id="loanInfoTradeTable" style="font-size:12px;">

                        <thead>
                            <tr>
                                <th rowspan=2 class='text-center' style="width:200px;">등록일시<br>삭제일시</th>
                                <th rowspan=2 class='text-center' style="width:140px;">입금구분<br>입금경로</th>
                                <th rowspan=2 class='text-center' style="width:140px;">차입자번호<br>계약번호</th>
                                <th rowspan=2 class='text-center' style="width:140px;">이름<br>생일</th>
                                <th rowspan=2 class='text-center' style="width:140px;">상품명</th>
                                <th rowspan=2 class='text-center' style="width:140px;">입금일<br>관리지점</th>
                                <th rowspan=2 class='text-center' style="width:140px;">입금액</th>
                                <th rowspan=2 class='text-center' style="width:140px;">정상이자<br>연체이자</th>
                                <th colspan=3 class='text-center' style="width:140px;">상환 / 감면</th>
                                <th rowspan=2 class='text-center' style="width:140px;">잔액</th>
                                <th rowspan=2 class='text-center' style="width:140px;">잔여이자</th>
                                <th rowspan=2 class='text-center' style="width:200px;">차기상환일<br>기한이익상실</th>
                            </tr>

                            <tr>
                                <th class='text-center' style="width:140px;">법비용</th>
                                <th class='text-center' style="width:140px;">이자</th>
                                <th class='text-center' style="width:140px;">원금</th>
                            </tr>
                        </thead>

                        <tbody id="listData_{{ $result['listName'] }}">
                        </tbody>

                        </table>
                        <div id="listError_{{ $result['listName'] }}"></div>
                    </div>

                    <!-- 일괄처리 & 페이지 버튼 -->
                    <div class="card-footer mb-0 p-1">
                        <div class="col-md-12 p-0 pt-2 m-0 row">
                            <div class="col-md-8" id="listDataSum_{{ $result['listName'] }}"></div>                            
                            <div class="col-md-4 text-right" id="pageList_{{ $result['listName'] }}"></div>
                        </div>
                    </div>

                    </form>
                </div>


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
                        <input class="form-control form-control-sm text-xs col-md-6"type="text" id="down_filename" style="display:none;margin-left:120px;"placeholder="다운받을 파일명을 입력해주세요">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <span class="form-control-sm col-8 text-red" id='excelMsg' style="display:none;">* 다운로드 중 입니다. </span> 
                <button type="button" class="btn btn-sm btn-secondary" id="closeBtn" data-dismiss="modal" aria-hidden="true">닫기</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="excelDown('form_'+'{{$result['listName']}}');">다운로드</button>
            </div>
        </div>
    </div>
</div>




<script language='javascript'>

    window.onload = function() {
        // 체크박스모양
        $('input[name="listChk[]"]').iCheck({
            checkboxClass: 'icheckbox_square-blue',
        });
        $('input[name="check-all"]').iCheck({
            checkboxClass: 'icheckbox_square-blue',
        });
        // 리스트 전체 선택/해제
        $('#{{ $result['listName'] }}ListHeader').on('ifChecked', '.check-all', function(event) {
            $('.list-check').iCheck('check');
        });
        $('#{{ $result['listName'] }}ListHeader').on('ifUnchecked', '.check-all', function(event) {
            $('.list-check').iCheck('uncheck');
        });
        
        $(".datetimepicker").datetimepicker({
            format: 'YYYY-MM-DD',
            locale: 'ko',
            useCurrent: false,
        });

        // 예약 다운 시 파일명 입력칸 보이기
        if($('input[name="excel_down_div"]:checked').val() == "S")
        {
            $('#down_filename').css('display', 'block');
        }
    }

</script>






@endsection





@section('javascript')
<script>



function getDataList(nm, page, action, postdata, oldTab)
{

  postdata = postdata + '&page=' + page;
  var queryParam = $.getQueryParameters(postdata);

  //파라미터로 받는다.	
  var nm = queryParam['listName'];
  setLoading('start', nm);

  $.ajaxSetup({
    headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    }
  });

  $.ajax({
    url: action,
    type: "POST",
    data: postdata,
    dataType: "json",
    success: function (data)
    {
      if( data.result=="1" )
      {

        $("#listData_" + nm).empty();
        $("#listError_" + nm).empty();

        // 데이터 없음.
        if (data.txt == "0") 
        {
          setLoading('stop', nm);
        }
        else
        {
          $("#listData_{{ $result['listName'] }}").html(data.v);
          $("#listDataSum_{{ $result['listName'] }}").html(data.sum);
        }

        // 페이징 처리
        $("#pageList_" + nm).html(data.pageList);
        $("#searchCnt").val(data.totalCnt);

        // tab
        if (data.tabCount) {
          $.each(data.tabCount, function (key, val) {
            var tmp = $("#tab" + key).text().split('(');
            $("#tab" + key).text(tmp[0] + '(' + val + ')');
          });
        }

        // 배치 항목 처리 - 처음 진입이나 탭이 변경됐을 경우만 변경한다.
        // 현재탭과 선택한 탭이 같은지 확인한다.
        if (typeof oldTab == 'string' && $('#tabsSelect' + nm).val() != oldTab) {
          if (data.batchArray) {
            $('#batchSta').find("option").remove();
            $('#batchSta').append('<option value="">선택</option>');

            $.each(data.batchArray, function (key, val) {
              $('#batchSta').append('<option value="' + key + '">' + val + '</option>');
            });
            $('#batchDiv').show();
          }
          else {
            $('#batchDiv').hide();
          }
        }
        if (data.incSum) {
          $.each(data.incSum, function (key, val) {
            $('#' + key).html(val);
          });
        }

        // 상단 카운트 불러오는 설정값 초기화
        $("#isFirst" + nm).val('0');

        // 현재 페이지번호
        $("#nowPage" + nm).val(page);

        // 전체선택 없앰
        if (data.listTitle) {
          $('.check-all').iCheck({
            checkboxClass: 'icheckbox_square-blue',
          });
        }
        $('.check-all').iCheck('uncheck');

        // 체크박스 icheck
        $('.list-check').iCheck({
          checkboxClass: 'icheckbox_square-blue',
          radioClass: 'iradio_square-blue',
          handle: 'checkbox'
        });

      }
      else if (data.result == "0" && data.msg != "")
      {
        alert(data.msg);
        setLoading('stop', nm);
      }
      else
      {
        alert('데이터를 불러오지 못했습니다.');
        setLoading('stop', nm);
      }

    },
    error: function (xhr) {
      console.log(xhr.responseText);
      alert("통신오류입니다. 관리자에게 문의해주세요.");
      setLoading('stop', nm);
    }
  });

}


// 엔터막기
function enterClear()
{
    $('input[type="text"]').keydown(function() {
      if( event.keyCode === 13 )
      {
        event.preventDefault();
        listRefresh();
      };
    });

    $("input[data-bootstrap-switch]").each(function() {
    $(this).bootstrapSwitch('state', $(this).prop('checked'));
  });
}


enterClear();

</script>
@endsection