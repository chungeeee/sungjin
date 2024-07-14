<div class="modal-header">
  <h4 class="modal-title">투자자입력</h4>
  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
    <span aria-hidden="true">&times;</span>
  </button>
</div>
<div class="modal-body">

  <form class="form-horizontal" role="form" name="investor_info_form" id="investor_info_form" method="post">
    <input type="hidden" id="mode" name="mode" value="{{ $mode }}">
    <div class="form-group row">
      <div class="col-sm-12">
        <!-- row -->
        <div class="form-group row">
          <label for="id" class="col-sm-2 col-form-label text-center"><span class="text-danger align-middle">*</span>투자자명</label>
          <div class="col-auto">
            <input type="text" class="form-control form-control-sm col-md-12 input-red" name="name" id="name" maxlength="50">
          </div>
          <div class="col-auto">
            <input type="checkbox" class="icheckbox_square-blue-sm tax_free" id="tax_free" name="tax_free" value="Y">면세대상</div>
        </div>

        <!-- row -->
        <div class="form-group row">
          <label for="company_yn" class="col-sm-2 col-form-label text-center"><span class="text-danger align-middle">*</span>개인/기업</label>
          <div class="col-sm-3">
            <select class="form-control select2 form-control-sm input-red" id="company_yn" name="company_yn">
                    <option value=''>선택</option>
                      {{ Func::printOption(['N'=>'개인', 'Y'=>'기업']) }} 
            </select>
          </div>
        </div>
        
        <!-- row -->
        <div class="form-group row">
          <label for="ssn" class="col-sm-2 col-form-label text-center"><span class="text-danger align-middle">*</span>주민번호<br>(법인번호)</label>
          <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm input-red" id="ssn" name="ssn"  maxlength="13">
          </div>
        </div>

        <!-- row -->
        <div class="form-group row">
          <label for="ssn" class="col-sm-2 col-form-label text-center"><span class="text-danger align-middle"></span>관계</label>
          <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="relation" name="relation">
          </div>
        </div>

        <!-- row -->
        <div class="form-group row">
          <label for="ph11" class="col-sm-2 col-form-label text-center"><span class="text-danger align-middle">*</span>전화번호1</label>
          <div class="col-auto">
            <input type="text" class="form-control form-control-sm input-red" id="ph11" name="ph11" maxlength="3" onkeyup="onlyNumber(this);" size="3">
          </div>
          <div class="col-auto">
            <input type="text" class="form-control form-control-sm input-red" id="ph12" name="ph12" maxlength="4" onkeyup="onlyNumber(this);" size="4">
          </div>
          <div class="col-auto">
            <input type="text" class="form-control form-control-sm input-red" id="ph13" name="ph13" maxlength="4" onkeyup="onlyNumber(this);" size="4">
          </div>
        </div>

        <!-- row -->
        <div class="form-group row">
          <label for="ph21" class="col-sm-2 col-form-label text-center">전화번호2</label>
          <div class="col-auto">
            <input type="text" class="form-control form-control-sm" id="ph21" name="ph21" maxlength="3" onkeyup="onlyNumber(this);" size="3">
          </div>
          <div class="col-auto">
            <input type="text" class="form-control form-control-sm" id="ph22" name="ph22" maxlength="4" onkeyup="onlyNumber(this);" size="4">
          </div>
          <div class="col-auto">
            <input type="text" class="form-control form-control-sm" id="ph23" name="ph23" maxlength="4" onkeyup="onlyNumber(this);" size="4">
          </div>
        </div>

        <!-- row -->
        <div class="form-group row">
          <label for="ph41" class="col-sm-2 col-form-label text-center">전화번호3</label>
          <div class="col-auto">
            <input type="text" class="form-control form-control-sm" id="ph41" name="ph41" maxlength="3" onkeyup="onlyNumber(this);" size="3">
          </div>
          <div class="col-auto">
            <input type="text" class="form-control form-control-sm" id="ph42" name="ph42" maxlength="4" onkeyup="onlyNumber(this);" size="4">
          </div>
          <div class="col-auto">
            <input type="text" class="form-control form-control-sm" id="ph43" name="ph43" maxlength="4" onkeyup="onlyNumber(this);" size="4">
          </div>
        </div>

        <!-- row -->
        <div class="form-group row">
          <label for="email" class="col-sm-2 col-form-label text-center">이메일</label>
          <div class="col-sm-6">
            <input type="text" class="form-control form-control-sm" id="email" name="email" placeholder="" value="{{ $v->email ?? '' }}" maxlength="50">
          </div>
        </div>

        <!-- row -->
        <div class="form-group row">
          <label for="com_ssn" class="col-sm-2 col-form-label text-center">사업자번호</label>
          <div class="col-sm-6">
            <input type="text" class="form-control form-control-sm" id="com_ssn" name="com_ssn" placeholder="" value="" maxlength="50">
          </div>
        </div>
        
        <!-- row -->
        <div class="form-group row">
          <label for="bank_cd" class="col-sm-2 col-form-label text-center"><span class="text-danger align-middle">*</span>은행1</label>
          <div class="col-sm-4">
            <select class="form-control select2 form-control-sm input-red" id="bank_cd" name="bank_cd">
                    <option value=''>선택</option>
                      {{ Func::printOption($configArr['bank_cd']) }}
            </select>
          </div>
          <label for="bank_ssn" class="col-sm-1.5 col-form-label text-center"><span class="text-danger align-middle">*</span>계좌번호1</label>
          <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm input-red" id="bank_ssn" name="bank_ssn" maxlength="50" onkeyup="onlyAccount(this);">
          </div>
        </div>

        <!-- row -->
        <div class="form-group row">
          <label for="bank_cd2" class="col-sm-2 col-form-label text-center">은행2</label>
          <div class="col-sm-4">
            <select class="form-control select2 form-control-sm" id="bank_cd2" name="bank_cd2">
                    <option value=''>선택</option>
                      {{ Func::printOption($configArr['bank_cd']) }}
            </select>
          </div>
          <label for="bank_ssn2" class="col-sm-1.5 col-form-label text-center">계좌번호2</label>
          <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="bank_ssn2" name="bank_ssn2" maxlength="50" onkeyup="onlyAccount(this);">
          </div>
        </div>

        <!-- row -->
        <div class="form-group row">
          <label for="bank_cd3" class="col-sm-2 col-form-label text-center">은행3</label>
          <div class="col-sm-4">
            <select class="form-control select2 form-control-sm" id="bank_cd3" name="bank_cd3">
                    <option value=''>선택</option>
                      {{ Func::printOption($configArr['bank_cd']) }}
            </select>
          </div>
          <label for="bank_ssn3" class="col-sm-1.5 col-form-label text-center">계좌번호3</label>
          <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="bank_ssn3" name="bank_ssn3" maxlength="50" onkeyup="onlyAccount(this);">
          </div>
        </div>

        <!-- row -->
        <div class="form-group row">
          <label for="bank_cd3" class="col-sm-2 col-form-label text-center">은행4</label>
          <div class="col-sm-4">
            <select class="form-control select2 form-control-sm" id="bank_cd4" name="bank_cd4">
                    <option value=''>선택</option>
                      {{ Func::printOption($configArr['bank_cd']) }}
            </select>
          </div>
          <label for="bank_ssn3" class="col-sm-1.5 col-form-label text-center">계좌번호4</label>
          <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="bank_ssn4" name="bank_ssn4" maxlength="50" onkeyup="onlyAccount(this);">
          </div>
        </div>

        <!-- row -->
        <div class="form-group row">
          <label for="zip1" class="col-sm-2 col-form-label text-center">주소1</label>
          <div class="col-sm-9">
            <div class="input-group col-sm-5 pl-0 pb-1">
              <input type="text" class="form-control form-control-sm" id="zip1" name="zip1" numberOnly="true" readOnly>
              <span class="input-group-append">
                <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('zip1', 'addr11', 'addr12', '')">검색</button>
              </span>
            </div>
            <input type="text" class="form-control form-control-sm mb-1" id="addr11" name="addr11" value="{{ $v->addr11 ?? '' }}" readOnly>
            <input type="text" class="form-control form-control-sm" id="addr12" name="addr12" value="{{ $v->addr12 ?? '' }}" maxlength="100">
          </div>
        </div>
        <!-- row -->
        <div class="form-group row">
          <label for="zip2" class="col-sm-2 col-form-label text-center">주소2</label>
          <div class="col-sm-9">
            <div class="input-group col-sm-5 pl-0 pb-1">
              <input type="text" class="form-control form-control-sm" id="zip2" name="zip2" numberOnly="true" readOnly>
              <span class="input-group-append">
                <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('zip2', 'addr21', 'addr22', '')">검색</button>
              </span>
            </div>
            <input type="text" class="form-control form-control-sm mb-1" id="addr21" name="addr21" value="{{ $v->addr21 ?? '' }}" readOnly>
            <input type="text" class="form-control form-control-sm" id="addr22" name="addr22" value="{{ $v->addr22 ?? '' }}" maxlength="100">
          </div>
        </div>
        
        <!-- row -->
        <div class="form-group row">
          <label for="memo" class="col-sm-2 col-form-label text-center">메모</label>
          <div class="col-sm-10">
            <textarea class="form-control form-control-sm" rows="3" name="memo" id="memo"></textarea>
          </div>
        </div>
        </span>
    </div>
  </form>
</div>
<div class="modal-footer justify-content-between">
  <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
  <div class="p-0">
    <button type="button" class="btn btn-sm btn-info" onclick="investorAction();">저장</button>
  </div>
</div>

<script> 
/**
*   (공통) 직업코드 검색 팝업
*   jobId : 최종코드저장 ID 
*   전달된 파라미터 기준 ID+1~4 있으면 세팅
*   전달된 파라미터 기준 ID+name 1~4 있으면 세팅
*   전달된 파라미터 기준 ID+str 전체 name text 세팅 
*/

// 투자자입력 Action
function investorAction()
{
    if(!investorDataValidation()) 
    {
      return false;
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    
    var postdata = $('#investor_info_form').serialize();
    
    if($("#band_no").val()!="")
    {
      postdata += "&band="+$("#band_no").val();
    }

    $("#investor-contents").html(loadingString);       
    $.post(
        "/account/investoraction", 
        postdata, 
        function(data) {
            alert(data.result_msg);
            // 정상처리시에만..
            if(data.rs_code=="Y") location.reload();
    });
}

// 투자자입력 form 데이터 유효성 검사
function investorDataValidation() 
{
  if( $('#name').val() =="" )
  {
      alert("투자자명을 입력해주세요.");
      $('#name').focus();
      return false;
  }

  if( $('#company_yn').val() =="" )
  {
      alert("개인/기업을 선택해주세요.");
      $('#company_yn').focus();
      return false;
  }

  if( $('#ssn').val() =="" )
  {
      if($('#company_yn').val()=="Y") alert("법인번호를 입력해주세요.");
      else alert("주민번호를 입력해주세요.");
      $('#ssn').focus();
      return false;
  }

  if (!validateSsn($('#ssn').val()))
  {
    alert("올바른 주민등록번호 형식이 아닙니다. 다시 확인해주세요.");
    return false;
  }

  if( $('#ph11').val() == "" )
  {
      alert("전화번호를 입력해주세요.");
      $('#ph11').focus();
      return false;
  }

  if( $('#ph12').val() == "" )
  {
      alert("전화번호를 입력해주세요.");
      $('#ph12').focus();
      return false;
  }

  if( $('#ph13').val() == "" )
  {
      alert("전화번호를 입력해주세요.");
      $('#ph13').focus();
      return false;
  }

  if( $('#bank_cd').val() =="" )
  {
      alert("은행을 입력해주세요.");
      $('#bank_cd').focus();
      return false;
  }

  if( $('#bank_ssn').val() == "" )
  {
      alert("계좌번호를 입력해주세요.");
      $('#bank_ssn').focus();
      return false;
  }

  if($("#email").val() != "")
  {
    var reg_email = /^([0-9a-zA-Z_\.-]+)@([0-9a-zA-Z_-]+)(\.[0-9a-zA-Z_-]+){1,2}$/;

    if (!reg_email.test($('#email').val())) 
    {
      alert('이메일 형식이 올바르지 않습니다.');
      return false;
    } 
  }

  return true;
}

// 주민등록번호 유효성 검사
function validateSsn(ssn) {
    // "-" 제거
    const cleanNumber = ssn.replace(/-/g, "");

    // 자릿수 확인
    const length = cleanNumber.length;
    if (length !== 10 && length !== 13) 
    {
        return false;
    }

    // 숫자로만 구성되었는지 확인
    if (!/^\d+$/.test(cleanNumber)) 
    {
        return false;
    }

    // 유효한 주민등록번호 혹은 사업자번호
    return true;
}

</script>