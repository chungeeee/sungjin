


<form id="investor_info_form">
@csrf
<input type="hidden" name="no" id="investor_info_no" value="{{ $lui->no ?? '' }}">
<input type="hidden" name="name" id="name" value="{{ $lui->name ?? '' }}">
<input type="hidden" name="mode" id="mode" value="{{ $mode ?? '' }}">
<input type="hidden" name="old_tax_free" id="old_tax_free" value="{{ $lui->tax_free ?? '' }}">

<h3 class="card-title">
    <i class="fas fa-user m-2" size="9px"></i>투자자 정보
</h3>
<div class="card-body p-1">
    <table class="table table-sm table-bordered table-input text-xs">
        <colgroup>
        <col width="7%"/>
        <col width="18%"/>
        <col width="7%"/>
        <col width="18%"/>
        <col width="7%"/>
        <col width="18%"/>
        <col width="7%"/>
        <col width="18%"/>
        </colgroup>
        <tbody>
        <tr>
            <th><span class="text-danger font-weight-bold h6 mr-1">*</span>고객명</th>
            <td>
                <div class="row">
                    <div class="col-md-8">
                        <input type="text" class="form-control form-control-sm col-md-12 input-red" name="name" id="name" value="{{ $lui->name ??  '' }}" readonly>
                    </div>
                    <div class="col-md-4">
                        <input type="checkbox" class="icheckbox_square-blue-sm tax_free" id="tax_free" name="tax_free" value="Y" {{ Func::echoChecked('Y',$lui->tax_free) }}>
                        <span>면세대상</span>
                    </div>
                </div>
            </td>
            <th><span class="text-danger font-weight-bold h6 mr-1">*</span>주민/법인번호</th>
            <td>
                <div class="col-md-10 row pl-2">
                    <input type="text" class="form-control form-control-sm col-md-8 mr-1 input-red" name="ssn" id="ssn" onkeyup="onlyNumber(this);" maxlength=13 value="{{ $lui->ssn ??  '' }}" readonly>
                </div>
            </td>
            <th>개인/기업 구분</th>
            <td>
                <select class="form-control form-control-sm col-md-5" name="company_yn" id="company_yn" @if((Func::funcCheckPermit('U004'))) onchange="groupView(this.value);" @endif>
                    {{ Func::printOption(['N'=>'개인', 'Y'=>'기업'],isset($lui->company_yn)?$lui->company_yn:'')  }} 
                </select>
            </td>
            <th>고객등록일</th>
            <td>
                <input type="text" class="form-control form-control-sm col-md-5" name="reg_time" id="reg_time" value="{{ $lui->reg_time ??  '' }}" readonly>
            </td> 
        </tr>
        <tr>
            <th>관계</th>
            <td>
                <input type="text" class="form-control form-control-sm col-md-8" name="relation" id="relation" value="{{ $lui->relation ??  '' }}">
            </td>
            <th>이메일</th>
            <td><input type="text" class="form-control form-control-sm col-md-8" name="email" id="email" value="{{ $lui->email ??  '' }}"></td>
            <th>사업자번호</th>
            <td><input type="text" class="form-control form-control-sm col-md-8" name="com_ssn" id="com_ssn" value="{{ $lui->com_ssn }}"onkeyup="onlyNumber(this);" maxlength=12></td>
        </tr> 
        <tr>
            <th><span class="text-danger font-weight-bold h6 mr-1">*</span>전화번호1</th>
            <td>
                <div class="row">
                    <input type="text" class="form-control form-control-sm col-md-2 ml-2 input-red" name="ph11" id="ph11" onkeyup="onlyNumber(this);" maxlength=3 value="{{ $lui->ph11 ??  '' }}">
                    <input type="text" class="form-control form-control-sm col-md-2 ml-1 input-red" name="ph12" id="ph12" onkeyup="onlyNumber(this);" maxlength=4 value="{{ $lui->ph12 ??  '' }}">
                    <input type="text" class="form-control form-control-sm col-md-2 ml-1 input-red" name="ph13" id="ph13" onkeyup="onlyNumber(this);" maxlength=4 value="{{ $lui->ph13 ??  '' }}">
                </div>
            </td>
            <th>전화번호2</th>
            <td>
                <div class="row">
                    <input type="text" class="form-control form-control-sm col-md-2 ml-2" name="ph21" id="ph21" onkeyup="onlyNumber(this);" maxlength=3 value="{{ $lui->ph21 ??  '' }}">
                    <input type="text" class="form-control form-control-sm col-md-2 ml-1" name="ph22" id="ph22" onkeyup="onlyNumber(this);" maxlength=4 value="{{ $lui->ph22 ??  '' }}">
                    <input type="text" class="form-control form-control-sm col-md-2 ml-1" name="ph23" id="ph23" onkeyup="onlyNumber(this);" maxlength=4 value="{{ $lui->ph23 ??  '' }}">
                </div>
            </td>
            <th>전화번호3</th>
            <td>
                <div class="row">
                    <input type="text" class="form-control form-control-sm col-md-2 ml-2" name="ph41" id="ph41" onkeyup="onlyNumber(this);" maxlength=3 value="{{ $lui->ph41 ??  '' }}">
                    <input type="text" class="form-control form-control-sm col-md-2 ml-1" name="ph42" id="ph42" onkeyup="onlyNumber(this);" maxlength=4 value="{{ $lui->ph42 ??  '' }}">
                    <input type="text" class="form-control form-control-sm col-md-2 ml-1" name="ph43" id="ph43" onkeyup="onlyNumber(this);" maxlength=4 value="{{ $lui->ph43 ??  '' }}">
                </div>
            </td>
            <td></td>
            <td></td>
        </tr>
        <tr>
            <th><span class="text-danger font-weight-bold h6 mr-1">*</span>은행/계좌번호1</th>
            <td>
                <div class="row">
                    <select class="form-control form-control-sm col-md-5 ml-1 input-red" style='margin-left:8px !important;' name="bank_cd" id="bank_cd">
                    <option value=''>선택</option>
                        {{ Func::printOption($configArr['bank_cd'],isset($lui->bank_cd)?$lui->bank_cd:'') }} 
                    </select>
                    <input type="text" class="form-control form-control-sm col-md-5 ml-1 input-red" name="bank_ssn" id="bank_ssn" value="{{ $lui->bank_ssn ??  '' }}" onkeyup="onlyAccount(this);">
                </div>
            </td>
            <th>은행/계좌번호2</th>
            <td>
                <div class="row">
                    <select class="form-control form-control-sm col-md-5 ml-1" style='margin-left:8px !important;' name="bank_cd2" id="bank_cd2">
                    <option value=''>선택</option>
                        {{ Func::printOption($configArr['bank_cd'],isset($lui->bank_cd2)?$lui->bank_cd2:'') }} 
                    </select>
                    <input type="text" class="form-control form-control-sm col-md-5 ml-1" name="bank_ssn2" id="bank_ssn2" value="{{ $lui->bank_ssn2 ??  '' }}" onkeyup="onlyAccount(this);">
                </div>
            </td>
            <th>은행/계좌번호3</th>
            <td>
                <div class="row">
                    <select class="form-control form-control-sm col-md-5 ml-1" style='margin-left:8px !important;' name="bank_cd3" id="bank_cd3">
                    <option value=''>선택</option>
                        {{ Func::printOption($configArr['bank_cd'],isset($lui->bank_cd3)?$lui->bank_cd3:'') }} 
                    </select>
                    <input type="text" class="form-control form-control-sm col-md-5 ml-1" name="bank_ssn3" id="bank_ssn3" value="{{ $lui->bank_ssn3 ??  '' }}" onkeyup="onlyAccount(this);">
                </div>
            </td>
            <th>은행/계좌번호4</th>
            <td>
                <div class="row">
                    <select class="form-control form-control-sm col-md-5 ml-1" style='margin-left:8px !important;' name="bank_cd4" id="bank_cd4">
                    <option value=''>선택</option>
                        {{ Func::printOption($configArr['bank_cd'],isset($lui->bank_cd4)?$lui->bank_cd4:'') }} 
                    </select>
                    <input type="text" class="form-control form-control-sm col-md-5 ml-1" name="bank_ssn4" id="bank_ssn4" value="{{ $lui->bank_ssn4 ??  '' }}" onkeyup="onlyAccount(this);">
                </div>
            </td>
            <td></td>
        </tr>
        <tr>
            <th><span class="text-danger font-weight-bold h6 mr-1">*</span>예금주명1</th>
            <td>
                <input type="text" class="form-control form-control-sm col-md-8 input-red" name="in_name" id="in_name" value="{{ $lui->in_name ??  '' }}">
            </td>
            <th>예금주명2</th>
            <td>
                <input type="text" class="form-control form-control-sm col-md-8" name="in_name2" id="in_name2" value="{{ $lui->in_name2 ??  '' }}">
            </td>
            <th>예금주명3</th>
            <td>
                <input type="text" class="form-control form-control-sm col-md-8" name="in_name3" id="in_name3" value="{{ $lui->in_name3 ??  '' }}">
            </td>
            <th>예금주명4</th>
            <td>
                <input type="text" class="form-control form-control-sm col-md-8" name="in_name4" id="in_name4" value="{{ $lui->in_name4 ??  '' }}">
            </td>
        </tr> 
        <tr>
            <th>주소1</th>
            <td colspan=3>
                <div class="row">
                    <div class="input-group col-sm-3 pb-1">
                    <input type="text" class="form-control form-control-sm col-md-12" name="zip1" id="zip1" numberOnly="true" value="{{ $lui->zip1 ?? ''}}" readOnly>
                    <span class="input-group-btn input-group-append">
                    <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('zip1', 'addr11', 'addr12', '')">검색</button>
                    </span>
                    </div>
                </div>
                <input type="text" class="form-control form-control-sm mb-1 col-md-10" name="addr11" id="addr11" value="{{ $lui->addr11  ?? ''}}" readOnly>
                <input type="text" class="form-control form-control-sm col-md-10" name="addr12" id="addr12" value="{{ $lui->addr12  ?? ''}}" maxlength="100" required>
                <div id="addr12_error" class="text-danger pt-2 pl-1 error-msg"></div>
            </td>
            <th>주소2</th>
            <td colspan=3>
                <div class="row">
                    <div class="input-group col-sm-3 pb-1">
                    <input type="text" class="form-control form-control-sm col-md-12" name="zip2" id="zip2" numberOnly="true" value="{{ $lui->zip2 ?? ''}}" readOnly>
                    <span class="input-group-btn input-group-append">
                    <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('zip2', 'addr21', 'addr22', '')">검색</button>
                    </span>
                    </div>
                </div>
                <input type="text" class="form-control form-control-sm mb-1 col-md-10" name="addr21" id="addr21" value="{{ $lui->addr21  ?? ''}}" readOnly>
                <input type="text" class="form-control form-control-sm col-md-10" name="addr22" id="addr22" value="{{ $lui->addr22  ?? ''}}" maxlength="100" required>
                <div id="addr22_error" class="text-danger pt-2 pl-1 error-msg"></div>
            </td>
        </tr>
        <tr>
            <th>메모</th>
            <td colspan=3><textarea class="form-control form-control-sm" rows="6" name="memo" id="memo">{{ $lui->memo ??  ''}}</textarea></td>
            <td colspan="4"></td>
        </tr>
        </tbody>
    </table>
</div>
@if( $lui->company_yn=="Y" && Func::funcCheckPermit('U004')) 
@php ( $readonly = "" )
@else
@php ( $readonly = "display:none" )
@endif
<span id="groupInput" style="{{ $readonly }}">
<h3 class="card-title">
    <i class="fas fa-user m-2" size="9px"></i>기관 그룹 정보
</h3>
<div class="card-body p-1">
    <table class="table table-sm table-bordered table-input text-xs">
    <colgroup>
    <col width="3%"/>
    <col width="18%"/>
    <col width="3%"/>
    <col width="18%"/>
    </colgroup>
    <tbody>
    <tr>
        <th rowspan=3>주소</th>
        <td rowspan=3>
            <div class="row">
                <div class="input-group col-sm-2 pb-1">
                <input type="text" class="form-control form-control-sm col-md-12" name="com_zip" id="com_zip" numberOnly="true" value="{{ $lui->com_zip ?? ''}}" readOnly>
                <span class="input-group-btn input-group-append">
                <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('com_zip', 'com_addr11', 'com_addr12', '')">검색</button>
                </span>
                </div>
            </div>
            <input type="text" class="form-control form-control-sm mb-1 col-md-6" name="com_addr11" id="com_addr11" value="{{ $lui->com_addr11  ?? ''}}" readOnly>
            <input type="text" class="form-control form-control-sm col-md-6" name="com_addr12" id="com_addr12" value="{{ $lui->com_addr12  ?? ''}}" maxlength="100" required>
            <div id="com_addr12_error" class="text-danger pt-2 pl-1 error-msg"></div>
        </td>
    </tr>
    <tr> 
        <th>대표번호</th>
        <td>
            <div class="row">
                <input type="text" class="form-control form-control-sm col-md-2 ml-2" name="com_tel1" id="com_tel1" onkeyup="onlyNumber(this);" maxlength=3 value="{{ $lui->com_tel1 ??  '' }}">
                <input type="text" class="form-control form-control-sm col-md-2 ml-1" name="com_tel2" id="com_tel2" onkeyup="onlyNumber(this);" maxlength=4 value="{{ $lui->com_tel2 ??  '' }}">
                <input type="text" class="form-control form-control-sm col-md-2 ml-1" name="com_tel3" id="com_tel3" onkeyup="onlyNumber(this);" maxlength=4 value="{{ $lui->com_tel3 ??  '' }}">
            </div>
        </td>
    </tr>
    </tbody>
    </table>
</div>
</span>
<div class="row justify-content-center">
    <button class="btn btn-sm bg-lightblue" type="button" onclick="investorAction();">저장</button>
</div>
</form>
<script>

/**
*   (공통) 직업코드 검색 팝업
*   jobId : 최종코드저장 ID 
*   전달된 파라미터 기준 ID+1~4 있으면 세팅
*   전달된 파라미터 기준 ID+name 1~4 있으면 세팅
*   전달된 파라미터 기준 ID+str 전체 name text 세팅 
*/
function getJobCode(jobId)
{
    window.open("/config/jobcodepop?jobId="+jobId, "msgInfo", "width=800, height=350, scrollbars=no");
}

var change_flag = "false";

function investorAction()
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 입력값 확인
    if(!validCheck()) return false;

    // 면세여부 변경 체크
    var arrayFaxFree = {Y:'대상',N:'비대상'};
    var old_val = $('#old_tax_free').val();
    var new_val = ( $("#tax_free").is(":checked") ) ? "Y" : "N" ;
    // 면세여부가 변경될 경우 - 스케줄도 변경된다. 실행여부 한번더 체크한다.
    if(old_val != new_val)
    {
        if(!confirm('면세대상여부가 \''+arrayFaxFree[old_val]+'\'에서 \''+arrayFaxFree[new_val]+'\'으로 변경되어\n대상 투자자의 유효한 투자건의 스케줄이 조정됩니다.\n\n진행하시겠습니까?')) return false;
    }

    var postdata = $('#investor_info_form').serialize();

    $("#investor-contents").html(loadingString);       
    $.post(
        "/account/investoraction", 
        postdata, 
        function(data) {
            alert(data.result_msg);
            // 정상처리시에만..
            if(data.rs_code=="Y") getInvestorData('investorinfo');
            else location.reload();
    });
}

function validCheck()
{
    if( $('#name').val() =="" )
    {
        alert("투자자명을 입력해주세요.");
        $('#name').focus();
        return false;
    }

    if( $('#ssn').val() =="" )
    {
        if($('#company_yn').val()=="Y") alert("법인번호를 입력해주세요.");
        else alert("주민번호를 입력해주세요.");
        $('#ssn').focus();
        return false;
    }

    if( $('#ph11').val() =="" )
    {
        alert("전화번호를 입력해주세요.");
        $('#ph11').focus();
        return false;
    }

    if( $('#ph12').val() =="" )
    {
        alert("전화번호를 입력해주세요.");
        $('#ph12').focus();
        return false;
    }

    if( $('#ph13').val() =="" )
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

    if( $('#bank_ssn').val() =="" )
    {
        alert("계좌번호를 입력해주세요.");
        $('#bank_ssn').focus();
        return false;
    }
    return true;
}

function groupView(div)
{
    // 기관일경우
    if(div=="Y")
    {
        $('#groupInput').css('display', 'block');
    }
    else
    {
        $('#groupInput').css('display', 'none');
    }
}

</script>