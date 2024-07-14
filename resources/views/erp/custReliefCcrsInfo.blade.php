<form id="ccrs_form" method="post" enctype="multipart/form-data" >
    <b class="pl-1">신용회복 상세내용</b>
    <table class="table table-sm table-bordered table-input">
        <input type="hidden" name="cust_info_no" id="cust_info_no" value="{{ $cust_info_no ?? '' }}">
        <input type="hidden" name="no" id="no" value="{{ $v->no ?? '' }}">
        <input type="hidden" name="div" id="div" value="B">
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
                <th>고객번호</th>
                <td><input type="text" class="form-control form-control-sm" name="target_name" id="target_name" value="{{ $cust_info_no ?? '' }}" readonly></td>
                <th>계약번호</th>
                <td>
                    <select class="form-control form-control-sm" name="loan_info_no" id="loan_info_no" {{ isset($v->loan_info_no) && isset($v->no) ? 'disabled':''}} required>
                    <option value=''>선택</option>
                    {{ Func::printOption($array_loan_info_no,isset($v->loan_info_no)?$v->loan_info_no:'') }}   
                    </select>
                </td>
                <th>상품명</th>
                <td><input type="text" class="form-control form-control-sm" name="pro_cd" id="pro_cd" value="{{ isset($v->pro_cd) ? Func::getArrayName($arrayProduct, $v->pro_cd):'' }}" readonly></td>
                <th>채무구분</th>
                <td><input type="text" class="form-control form-control-sm" name="" id="loan_type" value="{{ $v->loan_type ?? '' }}" readonly></td>
                <th>신청구분</th>
                <td><input type="text" class="form-control form-control-sm" name="" id="app_type" value="{{ $v->app_type ?? '' }}" readonly></td>
            </tr>
            <tr>
                <th>신청인상태</th>
                <td><input type="text" class="form-control form-control-sm" name="" id="applicant_status" value="{{ $v->applicant_status ?? '' }}" readonly></td>
                <th>계좌상태</th>
                <td><input type="text" class="form-control form-control-sm" name="" id="account_status" value="{{ $v->account_status ?? '' }}" readonly></td>
                <th>진행상태</th>
                <td><input type="text" class="form-control form-control-sm" name="" id="status_cd" value="{{ isset($v->status_cd) ? Func::getArrayName(Vars::$arrayReliefCcrsStatus, $v->status_cd):'' }}" readonly></td>
                <th>접수번호</th>
                <td>
                    <input type="text" class="form-control form-control-sm" name="event_no" id="event_no" value="{{ $v->event_no ?? '' }}" @if(isset($v->event_no) && $v->event_no != '') readonly @endif>
                </td>
                <td colspan="2">
                    <div class="ml-1 mr-0 pr-0 row align-self-center form-check">
                        <input type='checkbox' class='form-check form-check-input' id='auto_flag' name='auto_flag' value='Y' {{ is_object($v) ? Func::echoChecked('Y',Func::nvl($v->auto_flag,'')) : '' }}>
                        <label class="form-check-label font-weight-bold text-xs" for="auto_flag">자동조회</label>
                    </div>
                </td>
            </tr>
            <tr>
                <th>접수통지일</th>
                <td><input type="text" class="form-control form-control-sm" name="" id="app_arrive_date" value="{{ isset($v->app_arrive_date) ? Func::dateFormat($v->app_arrive_date):'' }}" readonly></td>
                <th>확정일<br>(합의서체결일)</th>
                <td><input type="text" class="form-control form-control-sm" name="" id="auth_date" value="{{ isset($v->auth_date) ? Func::dateFormat($v->auth_date):'' }}" readonly></td>
                <th>수정조정<br>접수통지일</th>
                <td>
                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0" id="edit_arrive_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#edit_arrive_date" name="edit_arrive_date" id="edit_arrive_date" value="{{ $v->edit_arrive_date ?? '' }}" DateOnly="true" size="6">
                        <div class="input-group-append" data-target="#edit_arrive_date" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <th>수정조정<br>확정일</th>
                <td>
                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0" id="edit_auth_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#edit_auth_date" name="edit_auth_date" id="edit_auth_date" value="{{ $v->edit_auth_date ?? '' }}" DateOnly="true" size="6">
                        <div class="input-group-append" data-target="#edit_auth_date" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th>확정자통지일</th>
                <td><input type="text" class="form-control form-control-sm" name="" id="auth_arrive_date" value="{{ isset($v->auth_arrive_date) ? Func::dateFormat($v->auth_arrive_date):'' }}" readonly></td>
                <th>반송일</th>
                <td><input type="text" class="form-control form-control-sm" name="" id="return_date" value="{{ isset($v->return_date) ? Func::dateFormat($v->return_date):'' }}" readonly></td>
                <th>실효/포기일</th>
                <td><input type="text" class="form-control form-control-sm" name="" id="cancel_date" value="{{ isset($v->cancel_date) ? Func::dateFormat($v->cancel_date):'' }}" readonly></td>
                <th>실효원상회복일</th>
                <td><input type="text" class="form-control form-control-sm" name="" id="restore_date" value="{{ isset($v->restore_date) ? Func::dateFormat($v->restore_date):'' }}" readonly></td>
                <th>상환방식</th>
                <td><input type="text" class="form-control form-control-sm" name="" id="return_method" value="{{ $v->return_method ?? '' }}" readonly></td>
            </tr>
            <tr>
                <th>조정전합계</th>
                <td><input type="text" class="form-control form-control-sm" name="" id="origin_balance" value="{{ isset($v->origin_balance) ? number_format($v->origin_balance) : '' }}" readonly></td>
                <th>조정후합계</th>
                <td><input type="text" class="form-control form-control-sm" name="" id="balance" value="{{ isset($v->balance) ? number_format($v->origin_balance) : '' }}" readonly></td>
                <th>인가율</th>
                <td><input type="text" class="form-control form-control-sm" name="" id="auth_ratio" value="{{ $v->auth_ratio ?? '' }} %" readonly></td>
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

            {{-- @if (Auth::user()->branch_code=="607" || Auth::user()->branch_code=="638") --}}
            <tr class="underline">
                <td colspan="10" class="text-right pt-3">
                    <button class="btn btn-sm bg-danger" onclick="ccrsAction('DEL');" type="button">삭제</button>
                    <button class="btn btn-sm bg-lightblue" onclick="ccrsAction('{{ $mode }}');" type="button">저장</button>
                </td>
            </tr>
            {{-- @endif --}}
    </table>
</form>