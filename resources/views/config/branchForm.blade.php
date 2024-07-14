<form class="form-horizontal" role="form" name="br_form" id="br_form" method="post">
<input type="hidden" id="mode" name="mode" value="{{ $mode }}">
<input type="hidden" id="save_status" name="save_status" value="{{ $v->save_status ?? 'Y' }}">

<div class="form-group row">
  <label for="code" class="col-sm-2 col-form-label">부서코드</label>
  <div class="col-sm-4">
    <input type="text" class="form-control form-control-sm" id="code" name="code" placeholder="숫자코드" {{ $readonly }} value="{{ $v->code ?? '' }}">
  </div>
</div>

<div class="form-group row">
  <label for="parent_code" class="col-sm-2 col-form-label">상위부서</label>
  <div class="col-sm-4">
    @if( $mode=='UPD' && $v->parent_code == 'TOP' )
    <label class="col-form-label">최상위</label>
    <input type="hidden" id="parent_code" name="parent_code" value="TOP">
    @else
    <select class="form-control select2 form-control-sm" style="width: 100%;" id="parent_code" name="parent_code">
    {{ Func::printOptionArray($array_branch, 'branch_name', $v->parent_code ?? '001') }}
    </select>
    @endif
  </div>
  <label for="branch_name" class="col-sm-2 col-form-label">부서명</label>
  <div class="col-sm-4">
    <input type="text" class="form-control form-control-sm" id="branch_name" name="branch_name" placeholder="한글등록" value="{{ $v->branch_name ?? '' }}">
  </div>
</div>

<div class="form-group row">
  <label for="open_date" class="col-sm-2 col-form-label">영업개시일</label>
  <div class="col-sm-4">
    <div class="input-group date" id="div_open_date" data-target-input="nearest">
        <input type="text" class="form-control form-control-sm datetimepicker-input dateformat" id="open_date" name="open_date" placeholder="영업개시일" data-target="#div_open_date" value="{{ $v->open_date ?? '' }}"/>
        <div class="input-group-append" data-target="#div_open_date" data-toggle="datetimepicker">
            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
        </div>
    </div>
  </div>
  <label for="close_date" class="col-sm-2 col-form-label">지점폐쇄일</label>
  <div class="col-sm-4">
    <div class="input-group date" id="div_close_date" data-target-input="nearest">
        <input type="text" class="form-control form-control-sm datetimepicker-input dateformat" id="close_date" name="close_date" placeholder="지점폐쇄일" data-target="#div_close_date" value="{{ $v->close_date ?? '' }}"/>
        <div class="input-group-append" data-target="#div_close_date" data-toggle="datetimepicker">
            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
        </div>
    </div>
  </div>
</div>

<div class="form-group row">
  <label for="ceo_name" class="col-sm-2 col-form-label">지점장</label>
  <div class="col-sm-4">
    <select class="form-control select2 form-control-sm" style="width: 100%;" id="ceo_name" name="ceo_name">
      <option value=''>지점장</option>
      {{ Func::printOption($arrayUsers, $v->ceo_name ?? '') }}
    </select>
  </div>

</div>

<div class="form-group row">
  <label for="zip" class="col-sm-2 col-form-label">우편 주소</label>
  <div class="col-sm-10">
    <div class="input-group col-sm-3 pl-0">
      <input type="text" class="form-control form-control-sm" id="zip" name="zip" numberOnly="true" value="{{ $v->zip ?? '' }}" readOnly>
      <span class="input-group-btn">
      <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('zip', 'addr11', 'addr12', '')">검색</button>
      </span>
    </div>
    <input type="text" class="form-control form-control-sm mt-1" id="addr11" name="addr11" value="{{ $v->addr11 ?? '' }}" readOnly>
    <input type="text" class="form-control form-control-sm mt-1" id="addr12" name="addr12" value="{{ $v->addr12 ?? '' }}">
  </div>
</div>

<div class="form-group row">
  <label for="phone" class="col-sm-2 col-form-label">전화번호</label>
  <div class="col-sm-4">
    <input type="text" class="form-control form-control-sm phformat" id="phone" name="phone" placeholder="숫자만입력" value="{{ $v->phone ?? '' }}">
  </div>

  <label for="phone" class="col-sm-2 col-form-label">예비번호</label>
  <div class="col-sm-4">
    <input type="text" class="form-control form-control-sm phformat" id="phone_extra" name="phone_extra" placeholder="숫자만입력" value="{{ $v->phone_extra ?? '' }}">
  </div>
</div>

</form>
