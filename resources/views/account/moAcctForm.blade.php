@extends('layouts.masterPop')
@section('content')

<form class="form-horizontal" name="mo_form" id="mo_form">
<input type="hidden" id="check_no" name="check_no" value="{{ $v->no ?? '' }}">
<input type="hidden" id="mo_acct_status" name="mo_acct_status" value="">

<div class="card card-lightblue">
    <div class="card-header">
    <h2 class="card-title">법인계좌 관리</h2>
    </div>
    
    <div class="card-body mr-3 p-3">
        <div class="form-group row">
            <label for="mo_acct_div" class="col-sm-2 col-form-label"><font class='text-red'>*</font>법인구분</label>
            <div class="col-sm-4">
                <select class="form-control form-control-sm col-sm-7" id="mo_acct_div" name="mo_acct_div">
                    <option value=''>법인구분</option>
                    {{ Func::printOption($arrayConfig['mo_acct_div'], $v->mo_acct_div ?? '') }}
                </select>
            </div>

            <label for="status" class="col-sm-2 col-form-label"><font class='text-red'>*</font>사용여부</label>
            <div class="col-sm-4">
                <input type="checkbox" name="status" id="status" data-bootstrap-switch value="Y" {{ ( isset($v->status) && $v->status=='Y' ) ? 'checked' : '' }}>
            </div>
        </div>

        <div class="form-group row">
            <label for="mo_bank_cd" class="col-sm-2 col-form-label"><font class='text-red'>*</font>법인통장 은행</label>
            <div class="col-sm-4">
                <select class="form-control form-control-sm col-sm-15" id="mo_bank_cd" name="mo_bank_cd" >
                    <option value=''>법인통장 은행</option>
                    {{ Func::printOption($arrayConfig['bank_cd'], $v->mo_bank_cd ?? '') }}
                </select>
            </div>

            <label for="mo_bank_ssn" class="col-sm-2 col-form-label"><font class='text-red'>*</font>법인통장 계좌번호</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" id="mo_bank_ssn" name="mo_bank_ssn" placeholder="계좌번호" value="{{ $v->mo_bank_ssn ?? '' }}">
            </div>
        </div>

        <div class="form-group row">
            <label for="mo_bank_name" class="col-sm-2 col-form-label">법인통장 계좌명</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" id="mo_bank_name" name="mo_bank_name" placeholder="계좌명" value="{{ $v->mo_bank_name ?? '' }}" >
            </div>
            <label for="mo_acct_cd" class="col-sm-2 col-form-label">분류</label>
            <div class="col-sm-2">
                <select class="form-control form-control-sm col-sm-12" id="mo_acct_cd" name="mo_acct_cd">
                    <option value=''>대분류</option>
                    {{ Func::printOption($arrayConfig['mo_acct_cd'], $v->mo_acct_cd ?? '') }}
                </select>
            </div>
            <div class="col-sm-2">
                <select class="form-control form-control-sm col-sm-12" id="mo_acct_sub_cd" name="mo_acct_sub_cd">
                    <option value=''>중분류</option>
                    {{ Func::printOption($arrayConfig['mo_acct_sub_cd'], $v->mo_acct_sub_cd ?? '') }}
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label for="memo" class="col-sm-2 col-form-label"></font>메모</label>
            <div class="col-sm-10">
                <input type="text" class="form-control form-control-sm" id="memo" name="memo" placeholder="메모" value="{{ $v->memo ?? '' }}" >
            </div>
        </div>
    </div>
    <div class="card-footer">
        @if(isset($v->no))
            <button type="button" class="btn btn-sm btn-danger" onclick="moAcctAction('DEL');">삭제</button>
            <button type="button" class="btn btn-sm btn-info" onclick="moAcctAction('UPD');">수정</button>
        @else
            <button type="button" class="btn btn-sm btn-info" onclick="moAcctAction('INS');">저장</button>
        @endif
    </div>
</div>

</form>

@endsection

@section('javascript')

<script>

/** 법인통장 상세정보 저장 */
function moAcctAction(md)
{
    if(md=="DEL")
    {
        if(!confirm('정말 삭제하시겠습니까?'))
        {
            return;
        }
    }
    else
    {
        // 유효성 체크
        if(!$('#mo_acct_div').val())
        {
            alert('법인구분을 선택해주세요.');
            $('#mo_acct_div').focus();
            return false;
        }
        if(!$('#mo_bank_cd').val())
        {
            alert('은행을 선택해주세요.');
            $('#mo_bank_cd').focus();
            return false;
        }
        if(!$('#mo_bank_ssn').val())
        {
            alert('계좌번호를 입력해주세요.');
            $('#mo_bank_ssn').focus();
            return false;
        }
    }
    
    $("#mo_acct_status").val(md);
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#mo_form').serialize();

    // 중복클릭 방지
    if(ccCheck()) return;

    $.ajax({
        url  : "/account/moacctaction",
        type : "post",
        data : postdata,
        success : function(result)
        {
            if(result.rs_code=='Y')
            {
                alert(result.rs_msg);
                window.opener.listRefresh();
                self.close();
            }
            else
            {
                alert(result.rs_msg);
                globalCheck = false;
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
}

</script>

@endsection
