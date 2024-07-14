@extends('layouts.master')
@section('content')
<?
    if(!isset($_COOKIE[$result['listName']]))
    {
        $_COOKIE[$result['listName']] = 10;
    }
?>

<script language='javascript'>
    // 리스트 표현 태그 
    function getMarkup{{ $result['listName'] ?? '' }}() 
    {
        var tabs = $("#tabsSelect{{ $result['listName'] ?? '' }}").val();
        var markup = "";
        markup = "<tr id='no_${no}' style='${line_style}' onclick='${onclick}'>";
        
        // checkbox 
        @if(isset($result['checkbox']))
            markup+= "<td class='text-center'><input type='checkbox' name='listChk[]' id='listChk${"+"{{ $result['listName'] ?? ''}}"+"}' class='list-check' value='${"+"{{ $result['checkbox'] ?? ''}}"+"}'></td>";
        @endif
        @foreach($result['listTitle'] as $tabs=>$listTitle)
            @if($tabs!='common') if(tabs=="{{$tabs}}"){ @endif
                @foreach($listTitle as $key => $val)
                    @if(isset($val[6]) && is_array($val[6]))
                    markup+= "<td class='text-{{ $val[3] }} @if(isset($val[4]) && $val[4]!='') rightline @endif one'>";
                            markup+= "{{html <?=$key?>}}";
                        @foreach ($val[6] as $k => $arr)
                            markup+= "<?=$arr[2]?>{{html <?=$k?>}}";
                        @endforeach
                    markup+= "</td>";
                    @else
                    markup+= "<td class='text-{{ $val[3] }} @if(isset($val[4]) && $val[4]!='') rightline @endif one'>{{html <?=$key?>}}</td>";
                    @endif
                @endforeach
            @if($tabs!='common') } @endif
        @endforeach
        markup+= "</tr>";
    
        return markup;
    }
    
    // 팝업창에서 호출해서 사용(submit)
    function listRefresh()
    {
        $('#isFirst{{ $result['listName'] }}').val('1');
        getDataList('{{ $result['listName'] }}', $("#nowPage{{ $result['listName'] }}").val(), '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());
    }
    // 날짜 버튼 지정
    function setDateUser(mode, startDt, endDt, id)
    {
        // 종료일이 있으면 오늘.
        if(endDt!='')
            $('#'+id + endDt).val('{{ date("Y-m-d") }}');
        switch(mode)
        {
            case 'today' :
                $('#'+id + startDt).val('{{ date("Y-m-d") }}');
                break;
            case 'yesterday' :
                $('#'+id + startDt).val('{{ date("Y-m-d", time()-86400) }}');
                $('#'+id + endDt).val('{{ date("Y-m-d", time()-86400) }}');
                break;
            case 'week' :
                $('#'+id + startDt).val('{{ date("Y-m-d", strtotime("last Monday", time()+86400)) }}');
                $('#'+id + endDt).val('{{ date("Y-m-d", strtotime("next Sunday", time()+86400)) }}');
                break;
            case 'month' :
                $('#'+id + startDt).val('{{ date("Y-m") }}-01');
                $('#'+id + endDt).val('{{ date("Y-m-t") }}');
                break;
            case 'year' :
                $('#'+id + startDt).val('{{ date("Y") }}-01-01');
                $('#'+id + endDt).val('{{ date("Y") }}-12-31');
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
<div class="col-12">
    {{-- Main content--}} 
    <section class="content">		
		
		{{-- 서류함 --}} 
        @if(isset($result['Tabs']))
            <nav id="tabsBox" style="display: inline-block;">
                <div class="nav nav-pills border-bottom-0" role="tablist">
                @foreach($result['Tabs']['tabsArray'] as $key=>$val)
                    <a class="nav-link @if ($result['Tabs']['tabsSelect']===$key) active @endif" id="tab{{ $key }}" style='margin-right:0px; cursor: pointer;' onClick='goTab("{{ $result['listAction'] }}", "{{ $key }}", "{{ $result['listName'] }}");'><span id='tab{{ $key }}'>{{ $val }}</span></a>
                @endforeach
				</div>
            </nav>
			<div style="display: inline-block; float: right; margin: 10px 10px 0 0">{!! $scrapHTML ?? '' !!}</div>
        @endif
        {{-- 서류함 끝 --}} 

        <div class="col-md-12 pl-0">
            <div class="card card-lightblue card-outline">
                <div class="box box-{{ config('view.box') }}">
                    <form class="form-horizontal" onsubmit="getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize()); return false;" method="post" name="form_{{ $result['listName'] }}" id="form_{{ $result['listName'] }}">
                    <input type="hidden" name="listName" id="listName{{ $result['listName'] }}" value="{{ $result['listName'] }}">
                    <input type="hidden" name="listOrder" id="listOrder{{ $result['listName'] }}">
                    <input type="hidden" name="listOrderAsc" id="listOrderAsc{{ $result['listName'] }}">
                    <input type="hidden" name="isFirst" id="isFirst{{ $result['listName'] }}" value="1">
                    @if(isset($result['Tabs']))
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
                    <input type="hidden" name="search_div" id="search_div{{ $result['listName'] }}">
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
                    
                        <div class="card-tools form-inline" id="searchBox" style="justify-content: flex-end; padding-right:4px;">
                            {{-- 버튼 Array 추가 -------------------------------}}
                            @if(isset($result['buttonArray']))
                                <div class="mr-1 mb-1 mt-1">
                                @foreach($result['buttonArray'] as $i =>$btn)
                                    <button type="button" class="btn btn-sm {{ $btn['buttonArrayClass'] ?? 'btn-primary' }}" onclick="{!! $btn['buttonArrayAction'] !!}" id="{{ $btn['buttonArrayId'] ?? '' }}">{{ $btn['buttonArrayNm'] }}</button>
                                @endforeach
                                </div>
                            @endif
                            {{-- 버튼 Array 추가 끝 -------------------------------}}
                            {{-- 일자 검색 ------------------------------}}
                            @if(isset($result['searchDate']))
                                @foreach($result['searchDate'] as $i => $search)
                                    <select class="form-control form-control-sm selectpicker mr-1 mb-1 mt-1" 
                                    name="{{ $search['searchDateNm'] }}" id="{{ $search['searchDateNm'] }}" onChange="{{ $search['searchDateFunc'] }}">
                                        <option value="">{{ $search['searchDateTitle'] }}</option>
                                        {{ Func::printOption($search['searchDateArray'],Func::getArrayName($search,'searchDateSelect'))  }}
                                    </select>
                                        
                                    <div class="input-group date mr-1 mt-0 mb-0 datetimepicker" id="{{ $result['listName'] }}{{ $search['searchDateNm'] }}searchDateStringStart" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#{{ $result['listName'] }}{{ $search['searchDateNm'] }}searchDateStringStart" name="{{ $search['searchDateNm'] }}String" id="{{  $result['listName']  }}{{ $search['searchDateNm'] }}String" DateOnly="true" @if ( isset($search['searchDateTxt']) ) value='{{ $search['searchDateTxt'] }}' @endif size="6">
                                        <div class="input-group-append" data-target="#{{ $result['listName'] }}{{ $search['searchDateNm'] }}searchDateStringStart" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                    
                                    @if ($search['searchDatePair'] === 'Y')
                                    <span class="mr-1">-</span>
                                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-1 mt-1" id="{{ $result['listName'] }}{{ $search['searchDateNm'] }}searchDateStringEnd" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#{{ $result['listName'] }}{{ $search['searchDateNm'] }}searchDateStringEnd" name="{{ $search['searchDateNm'] }}StringEnd" id="{{ $result['listName'] }}{{ $search['searchDateNm'] }}StringEnd" DateOnly="true" @if ( isset($search['searchDateTxtEnd']) ) value='{{ $search['searchDateTxtEnd'] }}' @endif size="6">
                                        <div class="input-group-append" data-target="#{{ $result['listName'] }}{{ $search['searchDateNm'] }}searchDateStringEnd" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                        @if ($search['searchDateNoBtn'] === 'YEAR')
                                        <div class="btn-group mr-1 mb-1 mt-1">
                                          <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("yesterday", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd","{{ $result['listName'] }}")'>전일</button>                                            
                                          <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("today", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd","{{ $result['listName'] }}")'>금일</button>
                                          <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("month", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd","{{ $result['listName'] }}")'>금월</button>
                                          <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("year", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd","{{ $result['listName'] }}")'>금해</button>
                                        </div>
                                        @elseif ($search['searchDateNoBtn'] != 'Y')
                                        <div class="btn-group mr-1 mb-1 mt-1">
                                          <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("yesterday", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd","{{ $result['listName'] }}")'>전일</button>                                            
                                          <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("today", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd","{{ $result['listName'] }}")'>금일</button>
                                          <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("week", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd","{{ $result['listName'] }}")'>금주</button>
                                          <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("month", "{{ $search['searchDateNm'] }}String", "{{ $search['searchDateNm'] }}StringEnd","{{ $result['listName'] }}")'>금월</button>
                                        </div>
                                        @endif
                                    @else
                                        @if($search['searchDateNoBtn'] != 'Y')
                                            @if(isset($search['searchDateNoBtn']) && $search['searchDateNoBtn']=='YESTERDAY')
                                            <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("yesterday", "{{ $search['searchDateNm'] }}String")'>{{ $search['searchDateNoBtnNm'] }}</button>
                                            @else
                                            {{-- <button type="button" class="btn btn-sm btn-default" onClick='setDateUser("today", "{{ $search['searchDateNm'] }}String")'>금일</button> --}}
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
                                <select class="form-control form-control-sm selectpicker mr-1 mb-1 mt-1" name="{{ $select['searchTypeNm'] }}" 
                                  id="{{ $select['searchTypeNm'] }}" {!! $select['searchTypeAction'] ?? '' !!}>
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
                            {{-- 상세 검색 ------------------------------}}
                            @if(isset($result['searchDetail']))
                                <div class="input-group">
                                    <select class="@if(0)form-control input-sm @else form-control form-control-sm @endif mr-1 mb-1 mt-1 selectpicker" name="searchDetail" id="searchDetail">
                                        <option value=""> 상세검색</option>
                                        {{ Func::printOption($result['searchDetail'],Func::getArrayName($result,'searchDetailSet'))  }}
                                    </select>
                                    <div class="input-group mt-0 mb-0 mr-1 mb-1 mt-1" style="width:120px;">
                                    <input type="text" name="searchString" class="form-control form-control-sm" placeholder="Search" id="searchString" value="{{ $result['searchStringSet'] ?? '' }}" {{ $result['searchStringReadOnly'] }}
                                    @if(isset($result['searchStringReadOnly']) && $result['searchStringReadOnly'] == 'Y') readonly @endif>
                                    </div>
                                </div>
                            @endif
                            {{-- 상세 검색 끝 ------------------------------}}
                            @if(isset($result['statusCheckBox']))
                            <div class="input-group">
                                <input type="checkbox" name="status_e_yn" id="status_e_yn" class="list-check" value="Y" style="opacity: 0;" onchange="{!! $result['statusCheckBox'] !!}">
                                <label class="form-check-label ml-1 font-weight-bold" for="status_e_yn">완제포함</label>
                            </div>
                            @endif
                        
                                <div class="input-group mt-1 mb-1  input-group-sm ml-1 align-center">
                                    한페이지
                                    <input type="text" name="listLimit" class="form-control form-control-sm ml-1" placeholder="한페이지" id="listLimit{{ $result['listName'] }}" value="{{ $_COOKIE[$result['listName']] }}" onkeyup="NotZero(this);" style="width:50px;height:29px;">
                                    <div class="input-group-append">
                                        <button type="submit" title="조회" class="btn btn-default" 
                                        onclick="@if(isset($searchDiv) && $searchDiv=='Y') goTab('{{ $result['listAction'] }}', '{{ $key }}', '{{ $result['listName'] }}'); @endif getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize()); return false;"
                                        
                                        style="height:29px;background-color:#3C8DBC;"><i class="fa fa-search"></i>조회</button>

                                        <button type="button" title="새로고침" class="btn btn-default" onclick="@if(isset($result['isPopup']) && $result['isPopup']==='Y' && isset($result['popupListAction'])) {{ $result['popupListAction'] }} @elseif($result['refresh']!='') {{ $result['refresh'] }} @else location.href='{{ $result['listAction'] }}'; @endif ;return false;" style="height:29px;"><i class="fas fa-sync"></i></button>

                                        @if(isset($result['multiButton']))
                                            <button type="button" class="btn btn-default" onclick="{!! $result['multiButton'] !!}" title="멀티검색" style="height:29px;"><i class="fa fa-cog"></i></button>
                                        @endif

                                    </div>
                                </div>
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
                        <table class="table table-hover table-striped table-sm mb-1" id="reportCostTable" style="font-size:12px;">
                        <thead>
                        <tr>
                            {{-- <th rowspan="2" class='text-center' style="background-color: lightgray;">No</th> --}}
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">회원<br>번호</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">구분</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">성명<br>생년월일</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">담당자<br>매입처수</th>
                            <th colspan="10" class='text-center' style="background-color: rgb(229, 206, 228);">채무자대리인위임관련정보</th>
                            <th colspan="6" class='text-center' style="background-color: rgb(202, 223, 229);">개인회생(파산)접수 정보</th>
                            <th rowspan="2" colspan="2" class='text-center' style="background-color: lightgray;">작업자<br>저장일</th>
                        </tr>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">통지서도착일</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">통지서</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">위임장</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">신분증</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">위임기간</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">위임기간<br>종료일 기준</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">채무자 대리인명</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">주소</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">연락처</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">진행/종료여부</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">회생접수여부</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">관할법원</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">사건번호</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">접수일(파산신청)</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">개시결정일(파산선고일)</th>
                            <th rowspan="2" class='text-center' style="background-color: lightgray;">변제계획인가일(파산면책일)</th>
                        </tr>
                        </thead>
                        <tbody id="listData_{{ $result['listName'] }}">
                        </tbody>
                        </table>
                        <div id="listError_{{ $result['listName'] }}"></div>
                    </div>

                    {{-- 일괄처리 & 페이지 버튼 --}}
                    <div class="card-footer mb-0">
                        <div class="col-md-12 p-0 pt-2 m-0 row">
                            <div class="col-md-6">
                                @if(isset($result['lumpForm']))
                                    @foreach( $result['lumpForm'] as $lumpcd => $lumpv )
                                    <button class="btn btn-sm @if (isset($lumpv['BTN_COLOR']) && $lumpv['BTN_COLOR'] != '') {{ $lumpv['BTN_COLOR'] }} @else btn-info @endif" id="LUMP_BTN_{{ $lumpcd }}" onclick="{{ ( isset($lumpv['BTN_ACTION']) && $lumpv['BTN_ACTION'] ) ? $lumpv['BTN_ACTION'].';' : "lump_btn_click('".$lumpcd."', '".$lumpv['BTN_NAME']."');" }} return false;">{{ $lumpv['BTN_NAME'] }}</button>
                                    @endforeach
                                @endif
                            </div>
                            <div class="col-md-6 text-right" id="pageList_{{ $result['listName'] }}"></div>
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



@if(isset($result['isModal']))
<div class="modal fade" id="modal01" @if (isset($result['isModal']['modalOption']) ) {{ $result['isModal']['modalOption'] }} @endif>
    <div class="modal-dialog @if (isset($result['isModal']['modalSize'])) {{ $result['isModal']['modalSize'] }} @endif">
        <div class="modal-content" id="modalContents">
            <div class="modal-header">
                
                <h4 class="modal-title" style="font-weight:bold;" id="modalTitle">{{ $result['isModal']['modalTitle'] }}</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div id="modalBody">
            </div>
        </div>
    </div>
</div>
@endif


<!-- Main content -->


{{-- 
     --}}

@endsection


<?
    // if(!isset($_COOKIE[$result['listName']]))
    // {
    //     $_COOKIE[$result['listName']] = 10;
    // }

    if(!isset($result['tabsSelect']))
    {
        $result['tabsSelect'] = 'T';
    }
    
?>


{{-- @section('lump')
일괄처리할거 입력
@endsection --}}

@section('javascript')
<script>

    window.onload = function() {
        // 예약 다운 시 파일명 입력칸 보이기
        if($('input[name="excel_down_div"]:checked').val() == "S")
        {
            $('#down_filename').css('display', 'block');
        }
    }


// // 엔터막기
// function enterClear()
// {
//     $('input[type="text"]').keydown(function() {
//       if (event.keyCode === 13)
//       {
//         event.preventDefault();
//         listRefresh();
//       };
//     });

//     $("input[data-bootstrap-switch]").each(function() {
//     $(this).bootstrapSwitch('state', $(this).prop('checked'));
//   });
  
// }




// // 일괄처리 클릭
// function lawXmlClick(lumpcd, btnName)
// {
//     // 탭 상태에 따라 보여줄 내용 결정.
//     var nowTabs = $("#tabsSelect{{ $result['listName'] ?? '' }}").val();
//     var none = true;    

//     lump_btn_click(lumpcd, btnName);
//     afterAjax();

// }





// enterClear();

</script>
@endsection