<?
$array_branch = Func::getBranchList();
$configArr = Func::getConfigArr();
?>

<form class="form-horizontal" role="form" name="user_form" id="user_form" method="post" enctype="multipart/form-data">
<input type="hidden" id="mode" name="mode" value="{{ $mode }}">
<input type="hidden" id="save_status" name="save_status" value="{{ $v->save_status ?? 'Y' }}">
<input type="hidden" id="imgDeleteFlag" name="imgDeleteFlag" value="N">
@if (isset($v->profile_img_src))
<input type="hidden" id="img_src" id="default_img" value="data:image;base64,{{ $v->profile_img_src ?? '' }}" />
@else
<input type="hidden" id="img_src" id="default_img" value="/img/blank_profile.png" />
@endif
<div class="row m-3">
<div class="col-6">
    <div class="card card-lightblue card-outline">
        <div class="card-header">
            고객정보
        </div>
        <div class="card-body">
            <div class="form-group row mb-0">
            <div class="col-sm-6">
                <!-- row -->
                <div class="form-group row">
                <label for="id" class="col-sm-4 col-form-label text-center">사번<span class="text-danger align-middle">*</span></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm" id="id" name="id" placeholder="" @if($mode != 'INS') readonly @endif value="{{ $v->id ?? '' }}" maxlength="10">
                </div>
                
                </div>
                <!-- row -->
                <div class="form-group row">
                <label for="name" class="col-sm-4 col-form-label text-center">이름<span class="text-danger align-middle">*</span></label>
                <div class="col-sm-8">
                    <input type="text" class="form-control form-control-sm" id="name" name="name" placeholder="한글등록" value="{{ $v->name ?? '' }}" maxlength="50" {{ $readonly }}>
                </div>
                </div>
            
                <!-- row -->
                <div class="form-group row">
                <label for="branch_code" class="col-sm-4 col-form-label text-center">부서</label>
                <div class="col-sm-8">
                    <select class="form-control select2 form-control-sm w-100" name="branch_code" @if(!empty($readonly)) onFocus='this.initialSelect = this.selectedIndex;' onChange='this.selectedIndex = this.initialSelect;' @endif {{ $readonly }}>
                    {{ Func::printOptionArray($array_branch, 'branch_name', $v->branch_code ?? '') }}
                    </select>
                </div>
                </div>


                <!-- row -->
                <div class="form-group row">
                <label for="passwd" class="col-sm-4 col-form-label text-center">패스워드@if($mode == 'INS')<span class="text-danger align-middle">*</span>@endif</label>
                <div class="col-sm-8">
                    <input type="password" class="form-control form-control-sm" id="passwd" name="passwd"@if($mode != 'INS') placeholder="변경 시에만 입력해주세요"@endif value="">
                </div>
                </div>


                <!-- row -->
                <div class="form-group row">
                <label for="user_rank_cd" class="col-sm-4 col-form-label text-center">직급</label>
                <div class="col-sm-8">
                    <select class="form-control select2 form-control-sm" id="user_rank_cd" name="user_rank_cd" @if(!empty($readonly)) onFocus='this.initialSelect = this.selectedIndex;' onChange='this.selectedIndex = this.initialSelect;' @endif {{ $readonly }}>
                    <option class="bg-gray" value=''>직급</option>
                    {{ Func::printOption($configArr['user_rank_cd'], $v->user_rank_cd ?? '') }}
                    </select>
                </div>
                </div>


                <!-- row -->
                <div class="form-group row">
                <label for="user_position_cd" class="col-sm-4 col-form-label text-center">직책</label>
                <div class="col-sm-8">
                    <select class="form-control select2 form-control-sm" id="user_position_cd" name="user_position_cd" @if(!empty($readonly)) onFocus='this.initialSelect = this.selectedIndex;' onChange='this.selectedIndex = this.initialSelect;' @endif {{ $readonly }}>
                    <option class="bg-gray" value=''>직책</option>
                    {{ Func::printOption($configArr['user_position_cd'], $v->user_position_cd ?? '') }}
                    </select>
                </div>
                </div>


            </div>
            <div class="col">
                <canvas id="img_canvas" style="width: 190px; height: 250px; margin: 0px auto; border: 1px solid #CCC; display: block">
                </canvas>
                <div class="w-100 text-center pt-1">
                <input type="file" name="profile_img_origin" id="fileUploadBtn" onchange="setThumbnail(event);" hidden/>
                <button type="button" class="btn btn-sm btn-primary" onclick="$('#fileUploadBtn').click();">업로드</button>
                <button type="button" class="btn btn-sm btn-info" onclick="basicImg();">기본 이미지</button>
                </div>
            </div>
            
            </div>



            <!-- row -->
            <div class="form-group row">
            <label for="birthday" class="col-sm-2 col-form-label text-center">생년월일</label>
            <div class="col-sm-5 col-lg-3">
                <div class="input-group date" id="birthdayDiv" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#birthdayDiv" name="birthday" id="birthday" value='{{ $v->birthday ?? '' }}' maxlength="10" size="6"/>
                <div class="input-group-append" data-target="#birthdayDiv" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
                </div>
            </div>
            <div class="col-sm-4">
                <div class="icheck-primary d-inline align-sub mr-1">
                <input type="radio" id="radioPrimary1" name="birth_type" value="Y" {{ $mode == 'UPD' && $v->birth_type == 'Y' ? 'checked' : '' }} checked>
                <label for="radioPrimary1">양력</label>
                </div>
                <div class="icheck-primary d-inline align-sub">
                <input type="radio" id="radioPrimary2" name="birth_type" value="N" {{ $mode == 'UPD' && $v->birth_type == 'N' ? 'checked' : '' }}>
                <label for="radioPrimary2">음력</label>
                </div>
            </div>
            </div>

            <!-- row -->
            <div class="form-group row">
            <label for="ssn11" class="col-sm-2 col-form-label text-center">주민번호</label>
            <div class="col-sm-2">
                <input type="text" class="form-control form-control-sm" id="ssn11" name="ssn11" placeholder="" value="{{ $v->ssn11 ?? '' }}" maxlength="6" onkeyup="onlyNumber(this);">
            </div>
            <div class="col-sm-2">
                <input type="text" class="form-control form-control-sm" id="ssn12" name="ssn12" placeholder="" value="{{ $v->ssn12 ?? '' }}" maxlength="7" onkeyup="onlyNumber(this);">
            </div>
            </div>


            <!-- row -->
            <div class="form-group row">
            <label for="zip" class="col-sm-2 col-form-label text-center">집주소</label>
            <div class="col-sm-8">

                <div class="input-group col-sm-3 pl-0 pb-1">
                <input type="text" class="form-control form-control-sm" id="zip" name="zip" numberOnly="true" value="{{ $v->zip ?? '' }}" readOnly>
                <span class="input-group-btn">
                <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('zip', 'addr11', 'addr12', '')">검색</button>
                </span>
                </div>
                <input type="text" class="form-control form-control-sm mb-1" id="addr11" name="addr11" value="{{ $v->addr11 ?? '' }}" readOnly>
                <input type="text" class="form-control form-control-sm" id="addr12" name="addr12" value="{{ $v->addr12 ?? '' }}" maxlength="100">

            </div>
            </div>
            
            <!-- row -->
            <div class="form-group row">
            <label for="ph31" class="col-sm-2 col-form-label text-center">회사전화</label>
            <div class="col-sm-2 col-md-1">
                <input type="text" class="form-control form-control-sm" id="ph31" name="ph31" placeholder="" value="{{ $v->ph31 ?? '' }}" maxlength="3" onkeyup="onlyNumber(this);">
            </div>
            <div class="col-sm-2 col-md-1">
                <input type="text" class="form-control form-control-sm" id="ph32" name="ph32" placeholder="" value="{{ $v->ph32 ?? '' }}" maxlength="4" onkeyup="onlyNumber(this);">
            </div>
            <div class="col-sm-2 col-md-1">
                <input type="text" class="form-control form-control-sm" id="ph33" name="ph33" placeholder="" value="{{ $v->ph33 ?? '' }}" maxlength="4" onkeyup="onlyNumber(this);">
            </div>
            <label for="ph34" class="col-sm-2 col-form-label text-center">내선번호</label>
            <div class="col-sm-2 col-md-1 p-0">
                <input type="text" class="form-control form-control-sm" id="ph34" name="ph34" placeholder="" value="{{ $v->ph34 ?? '' }}" maxlength="4" onkeyup="onlyNumber(this);">
            </div>
            </div>

            <!-- row -->
            <div class="form-group row">
            <label for="ph21" class="col-sm-2 col-form-label text-center">핸드폰</label>
            <div class="col-sm-2 col-md-1">
                <input type="text" class="form-control form-control-sm" id="ph21" name="ph21" placeholder="" value="{{ $v->ph21 ?? '' }}" maxlength="3" onkeyup="onlyNumber(this);">
            </div>
            <div class="col-sm-2 col-md-1">
                <input type="text" class="form-control form-control-sm" id="ph22" name="ph22" placeholder="" value="{{ $v->ph22 ?? '' }}" maxlength="4" onkeyup="onlyNumber(this);">
            </div>
            <div class="col-sm-2 col-md-1">
                <input type="text" class="form-control form-control-sm" id="ph23" name="ph23" placeholder="" value="{{ $v->ph23 ?? '' }}" maxlength="4" onkeyup="onlyNumber(this);">
            </div>
            </div>

            <!-- row -->
            <div class="form-group row">
            <label for="ph11" class="col-sm-2 col-form-label text-center">집전화</label>
            <div class="col-sm-2 col-md-1">
                <input type="text" class="form-control form-control-sm" id="ph11" name="ph11" placeholder="" value="{{ $v->ph11 ?? '' }}" maxlength="3" onkeyup="onlyNumber(this);">
            </div>
            <div class="col-sm-2 col-md-1">
                <input type="text" class="form-control form-control-sm" id="ph12" name="ph12" placeholder="" value="{{ $v->ph12 ?? '' }}" maxlength="4" onkeyup="onlyNumber(this);">
            </div>
            <div class="col-sm-2 col-md-1">
                <input type="text" class="form-control form-control-sm" id="ph13" name="ph13" placeholder="" value="{{ $v->ph13 ?? '' }}" maxlength="4" onkeyup="onlyNumber(this);">
            </div>
            </div>

            <!-- row -->
            <div class="form-group row">
            <label for="email" class="col-sm-2 col-form-label text-center">이메일</label>
            <div class="col-sm-3">
                <input type="text" class="form-control form-control-sm" id="email" name="email" placeholder="" value="{{ $v->email ?? '' }}" maxlength="50">
            </div>
            </div>


            <!-- row -->
            <div class="form-group row">
            <label for="ipsa" class="col-sm-2 col-form-label text-center">입사일</label>
            <div class="col-sm-5 col-lg-3">
                <div class="input-group date" id="ipsaDiv" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#ipsaDiv" name="ipsa" id="ipsa" value='{{ $v->ipsa ?? '' }}' maxlength="10" size="6" {{ $readonly ? 'disabled' : '' }}/>
                <div class="input-group-append" data-target="#ipsaDiv" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
                </div>
            </div>
            </div>

            <!-- row -->
            <div class="form-group row">
            <label for="toesa" class="col-sm-2 col-form-label text-center">퇴사일</label>
            <div class="col-sm-5 col-lg-3">
                <div class="input-group date" id="toesaDiv" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#toesaDiv" name="toesa" id="toesa" value='{{ $v->toesa ?? '' }}' maxlength="10" size="6" {{ $readonly ? 'disabled' : '' }}/>
                <div class="input-group-append" data-target="#toesaDiv" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
                </div>
            </div>
            </div>
            <!-- row -->
            <div class="form-group row">
            <label for="ip" class="col-sm-2 col-form-label text-center">IP</label>
            <div class="col-sm-10">
                <input type="text" class="form-control form-control-sm " name="access_ip" id="access_ip" placeholder="000.000.000.000, 000.000.000.000, 000.000.000.000" value="{{ $v->access_ip ?? '' }}" maxlength="50" {{ $readonly }}>
            </div>
            </div>
        </div>
    </div>
</div>
<div class="col-6">
    <div class="card card-lightblue card-outline">
        <div class="card-header">
            약관동의
        </div>
        <div class="card-body">
            <!-- row -->
            <div class="form-group row">
            <label for="toesa" class="col-sm-2 col-form-label text-center">퇴사일</label>
            <div class="col-sm-5 col-lg-3">
                <div class="input-group date" id="toesaDiv" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#toesaDiv" name="toesa" id="toesa" value='{{ $v->toesa ?? '' }}' maxlength="10" size="6" {{ $readonly ? 'disabled' : '' }}/>
                <div class="input-group-append" data-target="#toesaDiv" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
                </div>
            </div>
            </div>
            <!-- row -->
            <div class="form-group row">
            <label for="ip" class="col-sm-2 col-form-label text-center">IP</label>
            <div class="col-sm-10">
                <input type="text" class="form-control form-control-sm " name="access_ip" id="access_ip" placeholder="000.000.000.000, 000.000.000.000, 000.000.000.000" value="{{ $v->access_ip ?? '' }}" maxlength="50" {{ $readonly }}>
            </div>
            </div>
        </div>
    </div>
</div>
</div>
</form>

<form id="ph_cert_form" name="ph_cert_form">
	<input type="hidden" id="cert_name" name="cert_name" value=""/>
	<input type="hidden" id="cert_ph" name="cert_ph" value=""/>
	<input type="hidden" id="cert_member_no" name="member_no" value=""/>
	@csrf
</form>
<script>

function phoneCertify() {
	var name = $('#form_customerinfo input[name=ceo_nm]').val();
	var ph = $('#form_customerinfo input[name=ceo_ph]').val();

	$("#cert_name").val(name);
	$("#cert_ph").val(ph);

	var width = '480';
	var height = '600';
	var left = Math.ceil(( window.screen.width - width ) / 2);
	var top = Math.ceil(( window.screen.height - height ) / 2);

	var pop_title = "popupOpener" ;
	var popup = window.open("", pop_title, "height=" + height + "px, width=" + width + "px, left=" + left + "px, top=" + top + "px") ;
	 
	var formObj = document.ph_cert_form;
	formObj.target = pop_title;
	formObj.action = "/customer/phcertwindow";
	formObj.method = "post";

	formObj.submit();

	return false;
}

function customerDelete(member_no, div)
{
	if(!confirm('탈퇴 진행을 하시겠습니까?')) return false;

	$.ajaxSetup({
		headers: {
		'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$.ajax({
		url: '/customer/customerdelete',
		type: 'post',
		data: { member_no : member_no, div : div,  withdraw_div : "info"},
		success : function(result){
			if(result.rs_code=="Y" && div=="N")
			{
				alert('고객데이터 삭제성공');
				window.close();
				location.reload();
			}
			else if(result.rs_code=="Y" && div=="D")
			{
				alert('탈퇴신청 성공');
				window.close();
				location.reload();
			}
			else
			{
				alert('고객데이터 삭제실패');
			}
		},
		error : function(xhr) {
			//console.log(xhr.responseText);
			alert("통신오류입니다. 관리자에게 문의해주세요.");
			location.reload();
		}
	});
}

function snedTerms(member_no)
{
    if(confirm('로 약관내용을 전송하시겠습니까?'))
    {
        setLoading2('start','loading');
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    	$.ajax({
    		url: '/customer/customersendterms',
    		type: 'post',
    		data: { member_no : member_no },
    		success : function(result){
                if( result == 'OK' )
                {
                    alert('약관전송을 성공했습니다.');
                    location.reload();
                }
                else
                {
                    alert('약관전송을 실패했습니다. 관리자에게 문의해주세요.');
                    location.reload();
                }
    		},
    		error : function(xhr) {
    			//console.log(xhr.responseText);
    			alert("통신오류입니다. 관리자에게 문의해주세요.");
                location.reload();
    		}
    	});
    }
    else return false;
}

function resetPwfail(div)
{
	if(div=="passwd")
	{
		var keyword = "비밀번호";
	}
	else if(div=="pin_code")
	{
		var keyword = "핀코드";
	}

    if(confirm(keyword+' 실패횟수를 초기화 하시겠습니까?'))
    {
        var memberNo = '';

        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

    	$.ajax({
    		url: '/customer/customerresetfail',
    		type: 'post',
    		data: { memberNo : memberNo, div : div },
    		success : function(result){
                if( result == 'Y' )
                {
                    alert(keyword+' 실패횟수 초기화를 완료했습니다.');
                    getInfo("/customer/customerinfo", "conLeftTop");
                }
                else
                {
                    alert(keyword+' 실패횟수 초기화를 실패했습니다. 관리자에게 문의해주세요.');
                    return ;
                }
    		},
    		error : function(xhr) {
    			//console.log(xhr.responseText);
    			alert("통신오류입니다. 관리자에게 문의해주세요.");
                location.reload();
    		}
    	});
    }
    else return ;
}
function getBankOwner(){
	

	$('#bank_owner').attr('readonly', false);
	return false;


	var bank_cd =$('#bank_cd').val();
	var bank_acct =$('#bank_acct').val();

	$.ajaxSetup({
		headers: {
		'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$.ajax({
		url: '/bsfund/bsfundbankowner',
		type: 'post',
		data: { bank_cd : bank_cd,bank_acct:bank_acct },
		dataType: 'json',
		success : function(res){
			console.log(res);
			if(res.result=="Y"){
				alert('정상적으로 조회되었습니다. 수정을 눌러서 반영해주세요.');
				$('#bank_owner').val(res.bank_owner);
			}else{
				alert(res.result_msg);
			}
		},
		error : function(xhr) {
			console.log(xhr);
			alert("통신오류입니다. 관리자에게 문의해주세요.");
		}
	});
}
function customerImageStatus()
{
	var memberNo = '';

	$.ajaxSetup({
		headers: {
		'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$.ajax({
		url: '/customer/customerupdatecusdiv',
		type: 'post',
		data: { memberNo : memberNo },
		success : function(result){
			
		},
		error : function(xhr) {
			alert("통신오류입니다. 관리자에게 문의해주세요.");
			location.reload();
		}
	});
}

function customerPop(no)
{
    if ( no == '0' )
    {
        alert('등록된 고객이 아닙니다.');
        return false;
    }
    //var print = window.open('/customer/customerinfoprint?no='+no,'print','left=0,top=0,scrollbars=yes');
    var print = window.open('/customer/customerinfoprint?no='+no,'print','left='+(screen.availWidth-650)/2+',top='+(screen.availHeight-1000)/2+', width=650,height=1000,resizable=no');
}

function setChildValue(type,zip,roadaddr){
      if(type == 1){
          document.getElementById("zip1").value = zip;
          document.getElementById("addr11").value = roadaddr;
          document.getElementById('addr12').focus();
      }
      if(type == 2){
          document.getElementById("zip3").value = zip;
          document.getElementById("addr31").value = roadaddr;
          document.getElementById('addr32').focus();
      }

}

{{-- $(".view").iCheck({
    checkcardClass: 'icheckcard_minimal-blue',
    radioClass   : 'iradio_minimal-blue',
    handle: 'radio'
});

$(".viewCheck").iCheck({
    checkcardClass: 'icheckcard_minimal-blue',
    radioClass   : 'iradio_minimal-blue',
    handle: 'checkcard'
}); --}}

function sectorselect(val){
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

		var selectStr = "<option value=''>업종 미선택</option>";

		if( val == '')
        {
			$("#sector").html(selectStr);
		}
		else{

			$.post("/api/member/selectsector", { val : val },
					function(data) {
						//var parsedJson = JSON.parse(data);
						//var selectStr = "<option>업종선택</option>";

						//console.log(Object.keys(parsedJson).length);
						//$(parsedJson).each(function(){
						for(var i = 0;i<Object.keys(data).length;i++){
							selectStr += "<option value="+data[i].code+">" + data[i].code_name + "</option>";
							//$("#sector").append(selectStr);
						}
						//});
						$("#sector").html(selectStr);
		});
	}
}


$("#password").blur(function(){
    var pw = $("#password").val();
    if(pw != ""){
        if(pw.length > 20 || pw.length < 8) {
            alert('패스워드 길이를 확인해주세요.');
            setTimeout(function(){
                $("#password").val("");
                $("#password").focus();
            }, 10);
            return false;
        }
        var checkNumber = pw.search(/[0-9]/g);
        var checkEnglish = pw.search(/[a-zA-Z]/ig);
        var checkSpecial = pw.search(/[~!@#$%^=&*()_+-]/ig);
        if(checkNumber < 0 || checkEnglish < 0 || checkSpecial < 0){
            alert('패스워드에 영문, 숫자, 특수문자를 혼용해주세요.');
            $("#password").val("");
            $("#password").focus();
            return false;
        }
    }
});

function goEdit()
{

	if($('#mem_type').val()=='')
    {
        alert('회원구분을 선택해주세요');
        $('#mem_type').focus();
        return false;
    }

    if($("#name").val() == "")
    {
        alert('회사명을 입력해주세요');
        $('#name').focus();
        return false;
    }

    if($("#estDt").val() == "")
    {
        alert('설립일을 입력해주세요');
        $('#estDt').focus();
        return false;
    }
	
	if($("#biz_ssn").val() == "")
    {
        alert('사업자등록번호를 입력해주세요');
        $('#biz_ssn').focus();
        return false;
    }
	
	if($("#mem_type").val() == "3")
    {
		if($("#ssn11").val().length != 6)
		{
			alert('법인번호 앞자리 길이를 확인해주세요.');
			$('#ssn11').focus();
			return false;
		}

		if($("#ssn12").val().length != 7)
		{
			alert('법인번호 뒷자리 길이를 확인해주세요.');
			$('#ssn12').focus();
			return false;
		}

    }

	if($("#addr11").val() == "")
    {
        alert('주소를 선택해서 입력해주세요');
        $('#addr11').focus();
        return false;
    }

	if($("#addr12").val() == "")
    {
        alert('상세주소를 입력해주세요');
        $('#addr12').focus();
        return false;
    }

	if($("#ph11").val() == "")
    {
        alert('회사대표번호를 입력해주세요');
        $('#ph11').focus();
        return false;
    }

	if($("#ceo_nm").val() == "")
    {
        alert('대표자명을 입력해주세요');
        $('#ceo_nm').focus();
        return false;
    }

	if($("#ceo_ph").val() == "")
    {
        alert('대표자 연락처를 입력해주세요');
        $('#ceo_ph').focus();
        return false;
    }

	if($("#ceo_ssn11").val().length != 6)
	{
		alert('대표자 주민번호 앞자리 길이를 확인해주세요.');
		$('#ceo_ssn11').focus();
		return false;
	}

	if($("#ceo_ssn12").val().length != 7)
	{
		alert('대표자 주민번호 뒷자리 길이를 확인해주세요.');
		$('#ceo_ssn12').focus();
		return false;
	}

	if($("#exclBnkCd").val() == "")
    {
        alert('정산계좌 은행을 선택해주세요');
        $('#exclBnkCd').focus();
        return false;
    }
	
	if($("#exclAcntNo").val() == "")
    {
        alert('정산계좌번호를 입력해주세요');
        $('#exclAcntNo').focus();
        return false;
    }

	if($("#bank_cd").val() == "")
    {
        alert('상환계좌 은행을 선택해주세요');
        $('#bank_cd').focus();
        return false;
    }

	if($("#bank_acct").val() == "")
    {
        alert('상환계좌번호를 입력해주세요');
        $('#bank_acct').focus();
        return false;
    }

	if($("#dperBrddDt").val() == "")
    {
        alert('예금주 생년월일을 입력해주세요');
        $('#dperBrddDt').focus();
        return false;
    }

	if($("#crifInqrAgreModCd").val() == "")
    {
        alert('신용조회동의 방법을 선택해주세요');
        $('#crifInqrAgreModCd').focus();
        return false;
    }

	if($("#prestlModCd").val() == "")
    {
        alert('선정산 방식을 선택해주세요');
        $('#prestlModCd').focus();
        return false;
    }

	if($("#prestlMrketGrdCd").val() == "")
    {
        alert('셀러 등급을 선택해주세요');
        $('#prestlMrketGrdCd').focus();
        return false;
    }
	
	if($('#cert_id').val()=='')
    {
        alert('인증상태를 선택해주세요');
        $('#cert_id').focus();
        return false;
    }

	getFrame('form_customerinfo','/customer/customerinfoaction');
}


// 담당자정보 
$("#same_up").click(function() {
		
	if($("#same_up").is(":checked"))
	{
		$("#manager_nm").val($("#ceo_nm").val());
		$("#ph21").val($("#ceo_ph").val());
	}
	else
	{
		$("#manager_nm").val("");
		$("#ph21").val("");
	}
});

// 본인인증 정보 수정 시 동작
function checkPhCert(tag) {
//	$("#phone_certify").val('N');
}


</script>