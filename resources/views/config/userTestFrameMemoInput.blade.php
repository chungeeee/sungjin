<form class="form-horizontal" role="form" name="form_blanketinfo" id="form_blanketinfo" method="post" action="/loan/blanketinfoaction">
<input type="hidden" name="member_no" value="" />
<input type="hidden" name="no" value="" />
<input type="hidden" name="_token" value="{{ csrf_token() }}" />
<input type="hidden" name="url" value="{{ $_SERVER['REQUEST_URI'] }}" />

<div class="row ml-3 mr-3 mt-2">
<div class="col-12">

<div class="card card-lightblue card-outline mb-0">
    <div class="card-header with-border">
        <h3 class="card-title">
            회원번호 : 
            
			&nbsp; &nbsp;
			신청일 : 
        </h3>
    </div>
	<div class="he5"></div>

    <div class="card-body">

        <div class="form-group row underline he40">
            
            <label class="col-md-1 col-md-1-5 text-center mt5">제휴사</label>
            <div class="col-md-2 col-md-1-5 ">
				<select class="form-control form-control-sm" name="agent_cd" id="blanket_agent_cd">
					<option value="">제휴사선택</option>
				</select>
            </div>

            <label class="col-md-1 col-md-1-5 text-center mt5">파트너ID</label>
            <div class="col-md-2 col-md-1-5">
				<input type="text" class="form-control form-control-sm text-center" id="blanket_partner_cd" name="partner_cd" placeholder="파트너ID" value="">
            </div>

            <label class="col-md-1 col-md-1-5 text-center mt5">제휴사승인여부</label>
			<div class="col-md-2 col-md-1-5">
				<input type="text" class="form-control form-control-sm" name="" id="blanket_agent_yn" value="" readonly>	
			</div>

			<label class="col-md-1 col-md-1-5 text-center mt5">제휴사사유</label>
            <div class="col-md-2 col-md-1-5 mt5">
			<span data-toggle="tooltip" data-placement="top" title=""></span>
            </div>
        </div>

        <div class="form-group row underline he40">
            <label class="col-md-1 col-md-1-5 text-center mt5">계약일</label>
            <div class="col-md-2 col-md-1-5">
				
					<div class="input-group date custom_date" data-provide="datepicker">
						<input type="text" class="form-control form-control-sm" name="s_dt" id="blanket_s_dt" DateOnly="true" value=''>
						<div class="input-group-addon">
						<span class="glyphicon glyphicon-calendar"></span>
						</div>
					</div>
			</div>
			<label class="col-md-1 col-md-1-5 text-center mt5">종료일</label>
			<div class="col-md-2 col-md-1-5">
					<div class="input-group date custom_date" data-provide="datepicker">
						<input type="text" class="form-control form-control-sm" name="e_dt" id="blanket_e_dt" DateOnly="true" value=''>
						<div class="input-group-addon">
						<span class="glyphicon glyphicon-calendar"></span>
						</div>
					</div>
			</div>
            
			<label class="col-md-1 col-md-1-5 text-center mt5">신청요율</label>
            <div class="col-md-2 col-md-1-5 mt5">%
            </div>
			
			<label class="col-md-1 col-md-1-5 text-center mt5">승인요율</label>
            <div class="col-md-2 col-md-1-5">
				<input type="text" class="form-control form-control-sm text-center" id="blanket_ok_ratio" name="ok_ratio" placeholder="승인요율%" value="">
            </div>
        </div>

		<div class="form-group row underline he40">

            <label class="col-md-1 col-md-1-5 text-center mt5">프로모션여부 </label>
            <div class="col-md-2 col-md-1-5">
				<input type="text" class="form-control form-control-sm" name="" id="blanket_promotion" value="" readonly>	
            </div>
			
			<label class="col-md-1 col-md-1-5 text-center mt5">정산은행</label>
            <div class="col-md-2 col-md-1-5">
				<select class="form-control form-control-sm" name="agent_bank_cd" id="blanket_agent_bank_cd">
					<option value="">은행선택</option>
				</select>
            </div>

			<label class="col-md-1 col-md-1-5 text-center mt5">정산계좌</label>
            <div class="col-md-2 col-md-1-5">
				<input type="text" class="form-control form-control-sm text-center" id="blanket_agent_bank_acct" name="agent_bank_acct" maxlength="20" numberOnly="true" style="text-align:center;" placeholder="계좌번호 '-'없이 숫자만 입력" value="">
            </div>

            <label class="col-md-1 col-md-1-5 text-center mt5 text-blue">상태</label> 
            <div class="col-md-2 col-md-1-5 ">
				<select class="form-control form-control-sm" name="sta" id="blanket_sta" onChange='subSelect(this.value);'>
					<option value="">[] 상태변경</option>
				</select>

				{{-- <span id='subSelect' style='display:block;'>
					
                    <select class="form-control form-control-sm" name="reject_cd" id="blanket_reject_cd"  style="max-width: 300px;">
						
                        <option value="">선택</option>
                    </select>
                </span> --}}
            </div>
        </div>
        <div class="form-group row underline he40">
            <label class="col-md-1 col-md-1-5 text-center mt5">연계금융</label> 
            <div class="col-md-2 col-md-1-5 ">
				<select class="form-control form-control-sm" name="financecd" id="financeCd">
					<option value="">연계금융</option>
				</select>
            </div>

            <label class="col-md-1 col-md-1-5 text-center mt5">연계금융여부</label> 
            <div class="col-md-2 col-md-1-5 ">
				<div class="input-group">
				<select class="form-control form-control-sm" name="financeyn" id="financeYn">
				</select>
				</div>
            </div>

            <label class="col-md-1 col-md-1-5 text-center mt5">매출금액 입력</label> 
            <div class="col-md-2 col-md-1-5 ">
				<div class="input-group">
					<button type="button" class="btn btn-sm btn-primary" id="inputAmtBtn" onclick="inputModalForm();">입력하기</button>
				</div>
            </div>
        </div>
    </div>
    <div class="card-footer">
        <div style="float: right;">
            <button type="button" class="btn btn-primary btn-sm" onclick="getCustomerTest();">선정산 회원가입 Test</button>
            <button type="button" class="btn btn-primary btn-sm" onclick="goBlanket()">저장</button>
        </div>
    </div>
</div>

</div>
</div>


</form>

<div class="modal fade" id="inputAmtModal">
	<div class="modal-dialog">
		<div class="modal-content" id="inputAmtContent">
			
		</div>
	</div>
</div>

<script>


function getCustomerTest() {
	var member_no = $("input[name='member_no']").val();
	var blanket_no = $("input[name='no']").val();

	console.log(member_no);
	console.log(blanket_no);

	$.ajaxSetup({
		headers: {
		'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$.ajax({
		url: '/customer/getCustomerTest',
		type: 'get',
		data: { "member_no" : member_no, "blanket_no" : blanket_no },
		success : function(result){
			console.log(result);
			if (result == 'Y') {
				alert('완료');	
			} else {
				alert(result.msg);	
			}
		},
		error : function(xhr) {
			//console.log(xhr.responseText);
//			alert("통신오류입니다. 관리자에게 문의해주세요.");
//			location.reload();
		}
	});
}

function subSelect(val)
{
    if(val=='R')
    {
        $('#subSelect').show();

        // 환경설정값 가져와서 세팅하기
        setSubSelect('blanket_reject_cd', 'blanketRejectCd', '거절사유');
    }
    else
    {
        $('#subSelect').hide();
    }
}


function goBlanket()
{	
	var idAdd = '#blanket_';

	if($(idAdd + "agent_cd").val() == "")
    {
        alert('제휴사를 선택해주세요.');
		$(idAdd + "agent_cd").focus();
        return false;
    }

	if($(idAdd + "partner_cd").val() == "")
    {
        alert('파트너ID를 입력해주세요.');
		$(idAdd + "partner_cd").focus();
        return false;
    }
	
//	if($(idAdd + "agent_yn").val() == "")
//    {
//        alert('제휴사 승인 여부를 선택해주세요');
//        $(idAdd + "agent_yn").focus();
//        return false;
//    }

	if($(idAdd + "s_dt").val() == "")
    {
        alert('시작일을 선택해주세요');
        $(idAdd + "s_dt").focus();
        return false;
    }

	// 계약만료로 선택할때만 만료일을 넣어준다.
	if($(idAdd + "sta").val() == "F")
	{
		if($(idAdd + "e_dt").val() == "")
		{
			alert('종료일을 선택해주세요');
			$(idAdd + "e_dt").focus();
			return false;
		}
	}

	// 내부승인시에는 승인요율을 입력받는다.
	if($(idAdd + "sta").val() == "C")
	{

		if($(idAdd + "ok_ratio").val() == "")
		{
			alert('승인요율을 입력해주세요');
			$(idAdd + "ok_ratio").focus();
			return false;
		}

		if($(idAdd + "agent_bank_cd").val() == "")
		{
			alert('입금은행을 선택해주세요');
			$(idAdd + "agent_bank_cd").focus();
			return false;
		}

		if($(idAdd + "agent_bank_acct").val() == "")
		{
			alert('입금계좌번호를 입력해주세요');
			$(idAdd + "agent_bank_acct").focus();
			return false;
		}
	}
	if($(idAdd + "sta").val() == "R")
	{
		if($(idAdd + "reject_cd").val() == "")
		{
			alert('거절사유를 선택해주세요.');
			return false;	
		}
	}

//	if($(idAdd + "promotion").val() == "")
//    {
//        alert('프로모션 여부를 선택해주세요');
//        $(idAdd + "promotion").focus();
//        return false;
//    }

	getFrame('form_blanketinfo','/loan/blanketinfoaction');
}


function inputModalForm() {
	var postdata = $('#form_blanketinfo').serialize();

	$.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

	$.ajax({
		url: '/loan/blanketamtmodal',
		type: 'post',
		data: postdata,
		success : function(data){
			$("#inputAmtContent").html(data);
			$("#inputAmtModal").modal();
		},
		error : function(xhr) {
			console.log(xhr);
			alert("통신오류입니다. 관리자에게 문의해주세요.");
		}
	});
}



</script>
