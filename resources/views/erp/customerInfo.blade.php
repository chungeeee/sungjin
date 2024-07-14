


<form id="cust_info_form">
@csrf
<input type="hidden" name="cust_info_no" id="cust_info_no" value="{{ $ci->no ?? '' }}">
<input type="hidden" name="mode" id="mode" value="{{ $mode ?? '' }}">
<input type="hidden" name="vir_acct_no" id="vir_acct_no" value="{{ $ci->vir_acct_no ?? '' }}">

<h3 class="card-title"><i class="fas fa-user m-2" size="9px"></i>개인 및 직장정보</h3>
    <div class="card-body p-1">
        <table class="table table-sm table-bordered table-input text-xs">

            <colgroup>
            <col width="7%"/>
            <col width="18%"/>
            <col width="8%"/>
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
                    <input type="text" class="form-control form-control-sm col-md-4 input-red" name="name" id="name" value="{{ $ci->name ??  '' }}">
                </td>
                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>주민/법인번호</th>
                <td>
                    <div class="col-md-10 row pl-2">
                        <input type="text" class="form-control form-control-sm col-md-4 mr-1 input-red" name="ssn1" id="ssn1" onkeyup="onlyNumber(this);" maxlength=6 minlength=6 value="{{ $ci->ssn1 ??  '' }}" >
                        <input type="text" class="form-control form-control-sm col-md-5 input-red" name="ssn2" id="ssn2" onkeyup="onlyNumber(this);" maxlength=7 minlength=7 value="{{ $ci->ssn2 ??  '' }}" >
                    </div>
                </td>
                <th>사업자번호</th>
                <td><input type="text" class="form-control form-control-sm col-md-8" name="com_ssn" id="com_ssn" value="{{ $ci->com_ssn ??  '' }}"></td>
                <td></td>
                <td></td>
            </tr> 
            
            <tr>
                <th>관계</th>
                <td>
                    <input type="text" class="form-control form-control-sm col-md-8" name="relation" id="relation" value="{{ $ci->relation ??  '' }}">
                </td>
                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>전화번호</th>
                <td>
                    <div class="row">
                        <input type="text" class="form-control form-control-sm col-md-2 ml-2 input-red" name="ph11" id="ph11" onkeyup="onlyNumber(this);" maxlength=3 value="{{ $ci->ph11 ??  '' }}">
                        <input type="text" class="form-control form-control-sm col-md-2 ml-1 input-red" name="ph12" id="ph12" onkeyup="onlyNumber(this);" maxlength=4 value="{{ $ci->ph12 ??  '' }}">
                        <input type="text" class="form-control form-control-sm col-md-2 ml-1 input-red" name="ph13" id="ph13" onkeyup="onlyNumber(this);" maxlength=4 value="{{ $ci->ph13 ??  '' }}">
                        {{-- <select class="form-control form-control-sm col-md-4 ml-1" name="ph1_status" id="ph1_status">
                        <option value=''>상태선택</option>
                        {{ Func::printOption($configArr['call_status_cd'],isset($ci->ph1_status)?$ci->ph1_status:'') }} 
                        </select> --}}
                    </div>
                </td>
                <th>이메일</th>
                <td><input type="text" class="form-control form-control-sm col-md-8" name="email" id="email" value="{{ $ci->email ??  '' }}"></td>
            </tr>

            <tr>
                <th><span class="text-danger font-weight-bold h6 mr-1"></span>계좌 선택</th>
                <td>
                    <div class="form-group row">
                        <div class="col-sm-6">
                            <input type="text" class="form-control form-control-sm" id="usr_search_string" placeholder="법인 이름, 계좌번호" value="" />
                        </div>
                        <div class="col-sm-6 text-left">
                            <button class="btn btn-default btn-sm" type="button" onclick="searchUsrInfo();">검색</button>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <td> </td>
                <td colspan="3">
                    <div class="form-group row" id="usrSearch">
                        <div class="col-sm-12" id="usrSearchResult">
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <th><span class="text-danger font-weight-bold h6 mr-1" >*</span>은행/계좌번호</th>
                <td>
                    <div class="form-group row">
                        <div class="col-sm-4">
                            <input type="text" class="form-control form-control-sm" id="bank_cd" name="bank_cd" readonly placeholder="" value="{{ isset($ci->bank_cd) ? $configArr['bank_cd'][$ci->bank_cd] : '' }}"/>
                        </div>
                        <div class="col-sm-8 text-left">
                            <input type="text" class="form-control form-control-sm" id="bank_ssn" name="bank_ssn" readonly placeholder="" value="{{ $ci->bank_ssn ?? ''}}"/>
                        </div>
                    </div>
                </td>
                <td></td>
                <td></td>
                <td></td>
            </tr>

            <tr>
                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>주소</th>
                <td colspan="3">
                    <div class="row">
                        <div class="input-group col-sm-5 pb-1">
                        <input type="text" class="form-control form-control-sm input-red" name="zip1" id="zip1" numberOnly="true" value="{{ $ci->zip1 ?? ''}}" readOnly>
                        <span class="input-group-btn input-group-append">
                        <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('zip1', 'addr11', 'addr12', '')">검색</button>
                        </span>
                        </div>
                        {{-- 
                        <div class="pl-0 pt-1">
                        <button type="button" class="btn btn-secondary btn-xs" onclick="setAddr('zip1', 'addr11', 'addr12',$('#zip2').val(), $('#addr21').val(), $('#addr22').val());">등본</button>
                        <button type="button" class="btn btn-secondary btn-xs" onclick="setAddr('zip1', 'addr11', 'addr12',$('#zip4').val(), $('#addr41').val(), $('#addr42').val());">기타</button>
                        </div>
                        --}}
                    </div>
                    
                    <input type="text" class="form-control form-control-sm mb-1 col-md-12 input-red" name="addr11" id="addr11" value="{{ $ci->addr11  ?? ''}}" readOnly>
                    <input type="text" class="form-control form-control-sm col-md-12 input-red" name="addr12" id="addr12" value="{{ $ci->addr12  ?? ''}}" maxlength="100" required>
                    <div id="addr12_error" class="text-danger pt-2 pl-1 error-msg"></div>                    
                </td>
            </tr>
            <tr>
                <th>메모</th>
                <td colspan=3><textarea class="form-control form-control-sm" rows="6" name="memo" id="memo">{{ $ci->memo ??  ''}}</textarea></td>
                <td colspan="4"></td>
            </tr>
            <tr>
                <td colspan="8" class="text-right">
                    <button class="btn btn-sm bg-lightblue" type="button" onclick="custInfoAction();">{{$button_name}}</button>
                </td>
            </tr>
        </table>
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


function custInfoAction()
{
    if(checkValue())
    {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#cust_info_form').serialize();

        // 입력 오류시 화면을 유지하고 싶어함.. loading 화면 제외함.
        //$("#customer-contents").html(loadingString);       
        $.post(
            "/erp/customerinfoaction", 
            postdata, 
            function(data) {
                alert(data.result_msg);
                if(data.mode == 'INS')
                {
                    // 정상처리시에만..
                    if(data.rs_code=="Y")
                    {
                        opener.document.location.reload();
                        self.close();
                    }
                }
                else
                {
                    getCustData('info');
                }
                
        });
    }
}


// 유효성검사
function checkValue() 
{
    var result = true;
    var checkId = ['name','ssn1','ssn2'];

    $(".was-validated").removeClass("was-validated");
    checkId.forEach(function(id){
        if(!$('#'+id).val())
        {
            result = false;
            $('#'+id).parent().addClass("was-validated");
        }
    });

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

    if( $('#zip1').val() =="" )
    {
        alert("주소를 입력해주세요.");
        $('#zip1').focus();
        return false;
    }

    if( $('#addr11').val() =="" )
    {
        alert("주소를 입력해주세요.");
        $('#addr11').focus();
        return false;
    }

    if( $('#addr12').val() =="" )
    {
        alert("주소를 입력해주세요.");
        $('#addr12').focus();
        return false;
    }

    return result;
}

function searchUsrInfo()
{
    var usr_search_string = $("#usr_search_string").val();
    if( usr_search_string=="" )
    {
        alert("검색어를 입력해주세요.");
        $("#usr_search_string").focus();
        return false;
    }
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
    $("#usrSearchResult").html(loadingStringtxt);
    $("#usrSearch").collapse('show');
    $.post("/erp/corporatesearch", {usr_search_string:usr_search_string}, function(data) {
        $("#usrSearchResult").html(data);
    });
}

function selectUsrInfo(n)
{
    var cbn = $("#vir_acct_mo_acct_div_"+n).html();

    var cbc = $("#vir_acct_mo_bank_cd_"+n).html();
    var cbs = $("#vir_acct_mo_bank_ssn_"+n).html();
    
    // 화면에 표시
    $("#vir_acct_no").val(cbn);
    
    $("#bank_cd").val(cbc);
    $("#bank_ssn").val(cbs);

    $("#usrSearch").collapse('hide');
}

// 엔터막기
function enterClear()
{
    $('#usr_search_string').keydown(function() {
        if (event.keyCode === 13)
        {
            event.preventDefault();
            searchUsrInfo();
        };
    });
}

enterClear();

</script>
