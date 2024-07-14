<script language='javascript'>

function getMarkup{{ $list['listName'] ?? '' }}() 
{
    var tabs = $("#tabsSelect{{ $list['listName'] ?? '' }}").val();
    console.log(tabs);
    var markup = "";
    markup = "<tr id='no_${no}' style='${line_style}' onclick='${onclick}'>";
    markup+= "</tr>";

    return markup;
}

</script>

@extends('layouts.masterPop2')
<input type="hidden" name="excelDownCd" id="excelDownCd{{ $list['listName'] }}">
<input type="hidden" name="excelUrl" id="excelUrl{{ $list['listName'] }}">
<input type="hidden" name="etc" id="etc{{ $list['listName'] }}">
<input type="hidden" name="down_div" id="down_div{{ $list['listName'] }}">
<input type="hidden" name="excel_down_div" id="excel_down_div{{ $list['listName'] }}">
<input type="hidden" name="down_filename" id="down_filename{{ $list['listName'] }}">
{{ csrf_field() }}
<div class="form-group row">
	<div class="input-group col-md-1">
            <select class="form-control form-control-sm col-md-12 mr-1 mb-1 mt-1" name="searchBank" id="searchBank">
                <option value=""> 은행명</option>
                {{ Func::printOption($bank_cd, $data['searchBank'] ?? '')  }}
            </select>
        </div>
	<div class="input-group col-md-3">
            <select class="form-control form-control-sm col-md-4 mr-1 mb-1 mt-1" name="searchDate" id="searchDate">
                <option value=""> 일자검색</option>
                {{ Func::printOption($dateSearchArr, $data['searchDate'] ?? '')  }}
            </select>
            <div class="input-group col-md-4 mt-0 mb-0 date datetimepicker mr-1 mb-1 mt-1" id="searchDateString" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm col-md-8" name="sdate" id="sdate" DateOnly="true" value="{{ $data['sdate'] ?? '' }}" size="4">
                <div class="input-group-append" data-target="#searchDateString" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
            </div>
	    <span style="margin-left:-15px;margin-top:8px;margin-right:3px;">~</span>
	    <div class="input-group col-md-4 mt-0 mb-0 date datetimepicker mr-1 mb-1 mt-1" id="searchDateEndString" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm col-md-8" name="edate" id="edate" DateOnly="true" value="{{ $data['edate'] ?? '' }}" size="4">
                <div class="input-group-append" data-target="#searchDateEndString" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
            </div>
        </div>
	<div class="input-group col-md-2">
            <select class="form-control form-control-sm col-md-6 mr-1 mb-1 mt-1" name="searchDetail" id="searchDetail">
                <option value=""> 상세검색</option>
                {{ Func::printOption($detailSearchArr, $data['searchDetail'] ?? '')  }}
            </select>
            <div class="input-group col-md-6 mt-0 mb-0 mr-1 mb-1 mt-1">
            <input type="text" name="searchString" class="form-control form-control-sm" placeholder="Search" id="searchString" value="{{ $data['searchString'] ?? '' }}">
            </div>
        </div>
	<div class="input-group col-md-2">
		<button type="button" class="btn btn-sm btn-info float-right ml-2" style="height:28px;margin-top:4px;" id="code_btn" onclick="listSearch();">검색</button>
		<button type="button" class="btn btn-sm btn-success" style="height:28px; margin-top:4px; margin-left:8px;" id="excel_btn" onclick="excelDownModal('/config/registexcel', 'form_{{ $list['listName'] }}');">엑셀다운</button>
	</div>
</div>
<br>
        <div class="card-body table-responsive p-0" style="height: 450px;">
        <table class="table table-sm table-hover table-head-fixed text-nowrap text-center">
			<colgroup>
				<col width="4%">
				<col width="9%">
				<col width="9%">
				<col width="9%">
                                <col width="9%">
				<col width="9%">
				<col width="24%">
				<col width="9%">
                                <col width="9%">
                                <col width="9%">
			</colgroup>
			<thead>
			<tr>
                                <th>No</th>
				<th>은행명<br>나이스은행코드</th>
                                <th>업체명</th>
				<th>등록번호</th>
                                <th>업체구분</th>
				<th>타입</th>
				<th>대표자명<br>주소</th>
                                <th>원본파일명</th>
                                <th>저장일시</th>
                                <th>작업자</th>
			</tr>
			</thead>
			<tbody>

            @php $cnt = 0; @endphp
			@forelse( $result as $value )
            @php $cnt++; @endphp
			<tr onClick="javascript:setCodeForm('{{ $value->no }}'); setImgView('{{ $value->no }}')" class="hand">
				<td>{{ $cnt }}</td>
				<td>{{ $bank_cd[$value->bank_cd ?? ''] ?? '' }}<br>{{ $value->nice_cd ?? '' }}</td>
				<td>{{ $value->bank_name ?? '' }}</td>
				<td>{{ $value->regist_no ?? '' }}</td>
				{{--<td><img src="data:image/{{ $value->extension }};base64, {{ $value->product_img }}" style="height:16px;"></td>--}}
                                <td>{{ $bank_div[$value->bank_type ?? ''] ?? '' }}</td>
                                <td>{{ $value->bank_div ?? '' }}</td>
                                <td>{{ $value->owner_name ?? '' }}<br>{{ $value->addr ?? '' }}</td>
                                @if(isset($value->origin_filename))
                                <td><button type="button" class="btn btn-outline-dark btn-xs m-0 mr-1" onclick="filePreview('{{ $value->no }}');"><i class="fa fa-search mr-1"></i>{{ $value->origin_filename ?? '' }}</button></td>
				@else
                                <td></td>
                                @endif
                                <td>
					@if(isset($value->save_time))
						{{ Str::substr(date("Y-m-d H:i:s", strtotime($value->save_time)), 0, 10) }}
					@else
						&nbsp;
					@endif
				</td>
                                <td>{{ Func::getArrayName($arrayUserId, $value->save_id) }}</td>
			</tr>

			@empty

			<tr>
			<td colspan=10 class='text-center p-4'>등록된 등기부등본 정보가 없습니다.</td>
			</tr>

			@endforelse

        </table>
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
                            <label class="radio-block" style="width:74px; padding-left: 5px!important;">
                                <input type="radio" name="excel_down_div" id="realtime" value="E" onchange="input_filename()"> 바로실행 &nbsp;
                            </label>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <span class="form-control-sm col-8 text-red" id='excelMsg' style="display:none;">* 다운로드 중 입니다. </span> 
                <button type="button" class="btn btn-sm btn-secondary" id="closeBtn" data-dismiss="modal" aria-hidden="true">닫기</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="excelDown('form_regist');">다운로드</button>
            </div>
        </div>
    </div>
</div>