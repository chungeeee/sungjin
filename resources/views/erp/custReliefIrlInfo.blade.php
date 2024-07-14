<form id="irl_form" method="post" enctype="multipart/form-data" >
    <b class="pl-1">회생/파산 상세내용</b>
    <table class="table table-sm table-bordered table-input">
        <input type="hidden" name="cust_info_no" id="cust_info_no" value="{{ $cust_info_no ?? '' }}">
        <input type="hidden" name="no" id="no" value="{{ $v->no ?? '' }}">
        <input type="hidden" name="div" id="div" value="A">
        <colgroup>
            <col width="10%" class="text-center">
            <col width="10%">
            <col width="10%" class="text-center">
            <col width="10%">
            <col width="10%" class="text-center">
            <col width="10%">
            <col width="10%" class="text-center">
            <col width="10%">
            <col width="10%" class="text-center">
            <col width="10%">
        </colgroup>
        <tbody>
            <tr>
                <th>계약번호</th>
                <td>
                    <select class="form-control form-control-sm" name="loan_info_no" id="loan_info_no" {{ isset($v->loan_info_no) && isset($v->no)? 'disabled':''}} required>
                    <option value=''>선택</option>
                    {{ Func::printOption($array_loan_info_no,isset($v->loan_info_no)?$v->loan_info_no:'') }}   
                    </select>
                </td>

                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>구분</th>
                <td>
                    <select class="form-control form-control-sm" data-size="10" name="sub_div" id="sub_div" title="선택" onchange="changeSubdiv('irlSubDiv', this.value)">
                        <option value=''>선택</option>
                        {{ Func::printOption($arrayReliefSubDiv, isset($v->sub_div)?$v->sub_div:'') }}   
                    </select>
                </td>
                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>유형</th>
                <td>
                    <select class="form-control form-control-sm p-0" data-size="10" name="status_cd" id="status_cd" title="선택">
                        <option value=''>선택</option>
                        {{ Func::printOption($arrayReliefIrlStatus, isset($v->status_cd)?$v->status_cd:'') }}
                    </select>
                </td>
                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>채무구분</th>
                <td>
                    <select class="form-control form-control-sm" data-size="10" name="target_div" id="target_div" title="선택">
                        <option value=''>선택</option>
                        {{ Func::printOption($configArr['stl_target_cd'],isset($v->target_div)?$v->target_div:'') }}   
                    </select>
                </td>
                <th>당사자명</th>
                <td><input type="text" class="form-control form-control-sm" name="target_name" id="target_name" value="{{ $v->target_name ?? '' }}" placeholder="당사자명"></td>
            </tr>
            <tr>
                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>법원</th>
                <td>
                    <select class="form-control form-control-sm selectpicker" data-size="10" name="court_cd" id="court_cd" title="선택" data-live-search="true">
                        <option value=''>선택</option>
                        {{ Func::printOption($configArr['court_cd'],isset($v->court_cd)?$v->court_cd:'') }}   
                    </select>
                </td>
                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>사건번호</th>
                <td>
                    <input type="text" class="form-control form-control-sm mr-1" name="event_year" id="event_year" onkeyup="onlyNumber(this);" maxlength="4" value="{{ $v->event_year ?? '' }}" placeholder="년도">
                </td>
                <td>
                    <select class="form-control form-control-sm" data-size="10" name="event_cd" id="event_cd" title="선택">
                        <option value=''>선택</option>
                        {{ Func::printOption($arrayReliefEventCd, isset($v->event_cd)?$v->event_cd:'') }}
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="event_no" id="event_no" value="{{ $v->event_no ?? '' }}" placeholder="사건번호" onkeyup="onlyNumber(this);">
                </td>
                <td colspan="4">
                    <div class="ml-1 mr-0 pr-0 row align-self-center form-check">
                        <input type='checkbox' class='form-check form-check-input' id='auto_flag' name='auto_flag' value='Y' {{ is_object($v) ? Func::echoChecked('Y',Func::nvl($v->auto_flag,'')) : '' }}  @if(!isset($v->no)) {{ 'checked' }} @endif >
                        <label class="form-check-label font-weight-bold text-xs mr-2" for="auto_flag">자동조회</label>

                        @if(isset($nsf))
                            <button type="button" class="btn btn-default btn-xs text-xs {{ $nsf->text_class }}" onclick="getPopUp('/erp/nsfform?regdt={{ $nsf->bm_regdt ?? ''}}&seq={{ $nsf->bm_seq ?? ''}}','nsf','');">자동조회 : {{ Vars::$arrayNsfBmStatus[$nsf->bm_status] }}</button>
                        @endif
                    </div>
                </td>
            </tr>
            <tr>
                <th>접수일</th>
                <td>
                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0" id="app_date_id" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input c_readonly" data-target="#app_date_id" name="app_date" id="app_date" value="{{ isset($v->app_date) ? Func::dateFormat($v->app_date) : '' }}" DateOnly="true" size="6" disabled>
                        <div class="input-group-append" data-target="#app_date_id" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <th>금지결정일</th>
                <td>
                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0" id="ban_date_id" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input c_readonly" data-target="#ban_date_id" name="ban_date" id="ban_date" value="{{ isset($v->ban_date) ? Func::dateFormat($v->ban_date) : '' }}" DateOnly="true" size="6" disabled>
                        <div class="input-group-append" data-target="#ban_date_id" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <th>금지결정도달일</th>
                <td>
                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0 " id="ban_arrive_date_id" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input c_readonly" data-target="#ban_arrive_date_id" name="ban_arrive_date" id="ban_arrive_date" value="{{ isset($v->ban_arrive_date) ? Func::dateFormat($v->ban_arrive_date) : '' }}" DateOnly="true" size="6" disabled>
                        <div class="input-group-append" data-target="#ban_arrive_date_id" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <th>개시(파산선고)<br>결정일</th>
                <td>
                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0 " id="start_date_id" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input c_readonly" data-target="#start_date_id" name="start_date" id="start_date" value="{{ isset($v->start_date) ? Func::dateFormat($v->start_date) : '' }}" DateOnly="true" size="6" disabled>
                        <div class="input-group-append" data-target="#start_date_id" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <th>변제인가일</th>
                <td>
                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0 " id="auth_date_id" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input c_readonly" data-target="#auth_date_id" name="auth_date" id="auth_date" value="{{ isset($v->auth_date) ? Func::dateFormat($v->auth_date) : '' }}" DateOnly="true" size="6" disabled>
                        <div class="input-group-append" data-target="#auth_date_id" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>종국</th>
                <td><input type="text" class="form-control form-control-sm c_readonly" name="end_result" id="end_result" value="{{ $v->end_result ?? '' }}"  disabled></td>
                <th>채권번호</th>
                <td><input type="text" class="form-control form-control-sm c_readonly" name="creditor_no" id="creditor_no" value="{{ $v->creditor_no ?? '' }}" disabled onkeyup="onlyNumber(this);"></td>
                <th>인가일 전 잔액</th>
                <td><input type="text" class="form-control form-control-sm c_readonly" name="before_balance" id="before_balance" value="{{ isset($v->before_balance) ? number_format($v->before_balance) : 0 }}" onkeyup="onlyNumber(this);" disabled></td>
                <th>변제예정금액</th>
                <td><input type="text" class="form-control form-control-sm c_readonly" name="maybe_money" id="maybe_money" value="{{ isset($v->maybe_money) ? number_format($v->maybe_money) : 0 }}" onkeyup="onlyNumber(this);" disabled></td>
                <th>회생위원</th>
                <td><input type="text" class="form-control form-control-sm" name="court_branch_no" id="court_branch_no" value="{{ $v->court_branch_no ?? '' }}" onkeyup="onlyNumber(this);"></td>
            </tr>
            <tr>
                <th>비고</th>
                <td colspan="3"><textarea class="form-control form-control-sm" name="memo" id="memo" style="height:88px;font-size:0.8rem;">{{$v->memo ?? ''}}</textarea></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
        <tr class="underline">
            <td colspan="10" class="text-right pt-3">
                <button class="btn btn-sm bg-danger" onclick="irlAction('DEL');" type="button">삭제</button>
                <button class="btn btn-sm bg-lightblue" onclick="irlAction('{{ $mode }}');" type="button">저장</button>
            </td>
        </tr>
    </table>
</form>

<script>
    function changeSubdiv(md,mdKey)
    {
        $.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
	
		$.ajax({
			url  : "/erp/reliefselect",
			type : "post",
			data : {mode:md, mode_key:mdKey},
				success : function(result)
				{   
					$("#status_cd").selectpicker({
						noneSelectedText : '선택'
					});
					$("#status_cd").html(result);
					$('#status_cd').selectpicker('refresh');    
				},
				error : function(xhr)
				{
					alert("통신오류입니다. 관리자에게 문의해주세요.");
				}
		});
        
        // 사건부호도 처리해주자..
        $.ajax({
			url  : "/erp/reliefselect",
			type : "post",
			data : {mode:'irlEventCd', mode_key:mdKey},
				success : function(result)
				{   
					$("#event_cd").selectpicker({
						noneSelectedText : '사건부호'
					});
					$("#event_cd").html(result);
					$('#event_cd').selectpicker('refresh');    
				},
				error : function(xhr)
				{
					alert("통신오류입니다. 관리자에게 문의해주세요.");
				}
		});


        changeReadOnly(mdKey);
    }

changeReadOnly($("#sub_div").val());

// 항고일경우 수정가능해야하는 항목
function changeReadOnly(mdKey)
{
    if(mdKey == "A4")
    {
        $(".c_readonly").attr("disabled",false);
    }
    else
    {
        $(".c_readonly").attr("disabled",true);
    }
}
</script>