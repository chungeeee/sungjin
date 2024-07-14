
<input type="hidden" id="mode"  name="mode"  value="{{ $mode ?? 'INS' }}">
<input type="hidden" id="no"  name="no"  value="{{ $result->no ?? '' }}">

<div class="form-group row">
  <label for="name" class="col-sm-2 col-form-label">업체코드</label>
  <div class="col-sm-3">
        <select class="form-control form-control-xs  col-md-10" name="bank_cd" id="bank_cd">
                <option value=''>구분</option>
                {{ Func::printOption($bank_cd, $result->bank_cd ?? '') ?? '' }}
        </select>
  </div>
  <label for="bank_name" class="col-sm-2 col-form-label">업체명</label>
  <div class="col-sm-3">
      <input type="text" class="form-control form-control-sm" id="bank_name" name="bank_name" placeholder="" value="{{ $result->bank_name ?? '' }}">
  </div>
</div>

<div class="form-group row">
  <label for="regist_no" class="col-sm-2 col-form-label">등록번호</label>
  <div class="col-sm-3">
      <input type="text" class="form-control form-control-sm" id="regist_no" name="regist_no" placeholder=""  value="{{ $result->regist_no ?? '' }}">
  </div>
  <label for="bank_type" class="col-sm-2 col-form-label">타입</label>
  <div class="col-sm-3">
        <select class="form-control form-control-xs  col-md-10" name="bank_type" id="bank_type">
                <option value=''>업체타입</option>
                {{ Func::printOption($bank_div, $result->bank_type ?? '') ?? '' }}
        </select>
  </div>
</div>

<div class="form-group row">
  <label for="owner_name" class="col-sm-2 col-form-label">대표자명</label>
  <div class="col-sm-3">
      <input type="text" class="form-control form-control-sm" id="owner_name" name="owner_name" placeholder=""  value="{{ $result->owner_name ?? '' }}">
  </div>
  <label for="addr" class="col-sm-2 col-form-label">주소</label>
  <div class="col-sm-3">
      <input type="text" class="form-control form-control-sm" id="addr" name="addr" placeholder="" value="{{ $result->addr ?? '' }}">
  </div>
</div>

<div class="form-group row">
  <label for="bank_div" class="col-sm-2 col-form-label">업체구분</label>
  <div class="col-sm-3">
      <input type="text" class="form-control form-control-sm" id="bank_div" name="bank_div" placeholder=""  value="{{ $result->bank_div ?? '' }}">
  </div>
  <label for="nice_cd" class="col-sm-2 col-form-label">나이스은행코드</label>
  <div class="col-sm-3">
      <input type="text" class="form-control form-control-sm" id="nice_cd" name="nice_cd" placeholder="" value="{{ $result->nice_cd ?? '' }}">
  </div>
</div>

<div class="form-group row">
  <label for="customFile" class="col-sm-2 col-form-label">파일첨부</label>
  <div class="input-group custom-file col-sm-8" style="margin-left:8px;">
		<input type="file" class="custom-file-input form-control-xs text-xs" id="customFile" name="customFile" style="cursor:pointer;" accept=".pdf">
		<label class="custom-file-label mb-0 text-xs form-control-xs" style="text-align:left;" for="customFile">{{ $result->filename ?? '파일 첨부' }}</label>
	</div>
</div>
<div class="form-group row">
  <label for="customFileDown" class="col-sm-2 col-form-label">파일다운로드</label>
  <div class="input-group custom-file col-sm-8" style="margin-left:8px;">
  @if(!empty($result->origin_filename))
    <!-- <a href="#" onclick="fileDownload()"><h6 class="m-0"><i class="fas fa-file-download pr-1"></i>{{ $result->origin_filename ?? '' }}</h6></a> -->
    <a href="/config/file/{{$result->file_path}}" download="{{$result->filename.'.'.$result->extension}}"><h6 class="m-0"><i class="fas fa-file-download pr-1"></i>{{ $result->origin_filename ?? '' }}</h6></a>
  @else
  @endif
	</div>
</div>
<script>
    bsCustomFileInput.init();
</script>