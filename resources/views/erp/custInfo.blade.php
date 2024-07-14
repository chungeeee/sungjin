


<form id="cust_info_form">
@csrf
<input type="hidden" name="cust_info_no" id="cust_info_no" value="{{ $ci->cust_info_no }}">

<style>
    #banSmsDivsSelect .dropdown {
        width:230px !important;
    }
    #cust_info_form table th {
        text-align: center;
        font-weight: bold;
    }
    #cust_info_form table th p {
        margin-top: 3px;
    }
</style>

<!-- 고객정보 -->
<div class="col-md-12 row p-0 m-0" >
    {{-- <div class="col-md-12">
    <b>채권정보</b>
    <table class="table table-sm card-secondary card-outline mt-1 table-bordered" id="shortTable">
        <colgroup>
        <col width="12%"/>
        <col width="13%"/>
        <col width="12%"/>
        <col width="13%"/>
        <col width="12%"/>
        <col width="13%"/>
        <col width="12%"/>
        <col width="13%"/>
        </colgroup>

        <tbody>
        <tr>
            <th>담보제공구분</th>
            <td>{{ $ci->borrow_yn ?? '' }}</td>
            <th>담보설정일</th>
            <td>{{ $ci->borrow_start_date ?? '' }}</td>
            <th>담보설정만료일</th>
            <td>{{ $ci->borrow_end_date ?? '' }}</td>
            <th>담보제공처</th>
            <td>{{ $ci->borrow_comp ?? '' }}</td>
        </tr>
        <tr>
            <th title="민원관리 메뉴에서도 입력가능">민원관리</th>
            <td>
                {{ Func::getArrayName($configArr['person_manage_cd'], isset($ci->person_manage)?$ci->person_manage:'') }}
            </td>
            <th>
            </th>
            <td>
            </td>
            <th>
            </th>
            <td>
            </td>
            <th>
            </th>
            <td>
            </td>            
        </tr>
        </tbody>
    </table>
    </div> --}}

    <div class="col-md-12">
        <b>차입자정보</b>
    </div>

    <div class="col-md-6">

    <table class="table table-sm table-bordered table-input text-xs">
        <colgroup>
        <col width="20%"/>
        <col width="80%"/>
        </colgroup>

        <tbody>
        {{-- <tr height=62>
        <th class="text-blue">고객관리코드</th>
        <td>
            <div class="row pb-1">
            <div class="col-md-6 m-0 pr-0">
            <select class="form-control form-control-sm" name="attribute_manage_cd" id="attribute_manage_cd">
            <option value=''>집금관리</option>
            {{ Func::printOption($configArr['manage_rsn_cd'],$ci->attribute_manage_cd) }}
            </select>
            </div>

            <div class="col-md-6 m-0 pr-3">
            <select class="form-control form-control-sm @if( in_array($ci->attribute_delay_cd,Vars::$arrBanAttDelayCd) ) text-danger font-weight-bold @endif" name="attribute_delay_cd" id="attribute_delay_cd">
            <option value=''>연체사유코드</option>
            {{ Func::printOption($configArr['delay_rsn_cd'],$ci->attribute_delay_cd) }}
            </select>
            </div>
            </div>                
            <div class="row">            
            <div class="col-md-6 m-0 pr-0">
            <select class="form-control form-control-sm" name="attribute_reloan_cd" id="attribute_reloan_cd">
            <option value=''>재대출금지코드</option>
            {{ Func::printOption($configArr['no_reloan_cd'],$ci->attribute_reloan_cd) }}
            </select>
            </div>
            <div class="col-md-6 m-0 pr-3">
            <select class="form-control form-control-sm" name="attribute_addloan_cd" id="attribute_addloan_cd">
            <option value=''>증액금지코드</option>
            {{ Func::printOption($configArr['manage_rsn_cd'],$ci->attribute_addloan_cd) }}
            </select>
            </div>
            </div>
        </td>
        </tr> --}}

        {{-- <tr height=31>
        <th class="bg-secondary p-0">기한이익상실통보일</th>
        <td>
            <div class="row">
                <div class="col-md-3 m-0 row" style="padding-top:1px;">
                    <div class="input-group date datetimepicker" id="kihan_post_date_div" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm dateformat" name="kihan_post_date" id="kihan_post_date" inputmode="text" value="{{ $ci->kihan_post_date }}">
                        <div class="input-group-append" data-target="#kihan_post_date_div" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-5 text-left text-xs text-danger font-weight-bold pt-2">@if($ci->kihan_post_date!="") 기한이익상실통보발송 = Y @endif</div>

                @if( $saly_yn>0 )
                <div class="col-md-3 text-xs text-danger font-weight-bold pt-2">
                [매각요청]
                </div>
                @endif
            </div>
        </td>
        </tr> --}}

        <tr height=31>
        <th>전화번호</th>
        <td>
            <div class="row">
                <div class="col-md-2 m-0 pr-0"><input type="text" class="form-control form-control-sm" name="ph11" id="ph11" onkeyup="onlyNumber(this);" maxlength=3 value="{{ $ci->ph11 }}"></div>
                <div class="col-md-2 m-0 pr-0"><input type="text" class="form-control form-control-sm" name="ph12" id="ph12" onkeyup="onlyNumber(this);" maxlength=4 value="{{ $ci->ph12 }}"></div>
                <div class="col-md-2 m-0 pr-0"><input type="text" class="form-control form-control-sm" name="ph13" id="ph13" onkeyup="onlyNumber(this);" maxlength=4 value="{{ $ci->ph13 }}"></div>
            </div>
        </td>
        </tr> 

        <tr height=31>
            <th>이메일</th>
            <td><input type="text" class="form-control form-control-sm" name="email" id="email" value="{{ $ci->email }}"></td>
        </tr>

        <tr height=31>
        <th>사업자번호</th>
        <td>
            <div class="row">
                <div class="col-md-12 m-0 pr-0 row">
                <input type="text" class="form-control form-control-sm col-md-6" name="com_ssn" id="com_ssn" value="{{ $ci->com_ssn }}"onkeyup="onlyNumber(this);" maxlength=12>
                </div>
            </div>
        </td>
        </tr>
    </tbody>
    </table>


</div>
<div class="col-md-6">
<table class="table table-sm table-bordered table-input text-xs">
        <colgroup>
        <col width="20%"/>
        <col width="80%"/>
        </colgroup>

        <tbody>

        <tr height=93>
        <th>주소
            <p>
            <span class="map-n" title="네이버맵 조회"; onClick="showMapNaver($('#addr11').val() + ' ' + $('#addr12').val());">&nbsp;N&nbsp;</span>
            <span class="map-k" title="카카오맵 조회"; onClick="showMapKakao($('#addr11').val() + ' ' + $('#addr12').val());">&nbsp;K&nbsp;</span>
        </th>
        <td>
            <div class="row">
                <div class="input-group col-sm-4 pb-1">
                    <input type="text" class="form-control" name="zip1" id="zip1" numberOnly="true" value="{{ $ci->zip1 }}" readOnly>
                    <span class="input-group-btn input-group-append">
                    <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('zip1', 'addr11', 'addr12', $('#addr11').val())">검색</button>
                    </span>
                </div>
                <div class="col-sm-4 p-1">
                </div>
                <div class="col-sm-2 p-1">
                </div>
            </div>

            <div class="row">
                <input type="text" class="form-control form-control-sm col-md-10 ml-2 mb-1" name="addr11" id="addr11" value="{{ $ci->addr11 }}" readOnly>
                <input type="text" class="form-control form-control-sm col-md-10 ml-2 mb-1" name="old_addr11" id="old_addr11" value="{{ $ci->old_addr11 }}" readOnly title="지번주소" placeholder="지번주소" hidden>
                <div class="col-sm-1 p-1">
                </div>
            </div>
            <div class="row">
                <input type="text" class="form-control form-control-sm col-md-10 ml-2" name="addr12" id="addr12" value="{{ $ci->addr12 }}" maxlength="100">
            </div>

        </td>
        </tr>
        </tbody>
    </table>
</div>
</div>
<div class="col-md-12 row p-0 m-0 pt-2">
    <div class="col-md-12 border-top pt-3">
        <b>계약정보</b>
    </div>

    <div class="col-md-6 mt-1">
        <table class="table table-sm table-bordered table-input text-xs">
            <colgroup>
            <col width="20%"/>
            <col width="80%"/>
            </colgroup>
            <tbody>
                <tr height=31>
                    <th>차입자계좌</th>
                    <td>
                        <div class="row">
                            <div class="col-md-4 m-0 pr-0">            
                            <select class="form-control form-control-sm" name="cust_bank_cd" id="cust_bank_cd">
                                <option value=''>선택</option>
                                {{ Func::printOption($configArr['bank_cd'],isset($ci->cust_bank_cd)?$ci->cust_bank_cd:'') }} 
                            </select>
                            </div>
                            <div class="col-md-4 m-0 pr-0">
                                <input type="text" class="form-control form-control-sm" name="cust_bank_ssn" id="cust_bank_ssn" value="{{ $ci->cust_bank_ssn }}" onkeyup="onlyNumber(this);">
                            </div>
                            <div class="col-md-4 m-0">
                                <input type="text" class="form-control form-control-sm" name="cust_bank_name" id="cust_bank_name" value="{{ $ci->cust_bank_name }}">
                            </div>
                        </div>
                    </td>
                </tr>

                <tr height=31>
                    <th>담당</th>
                    <td>
                        <div class="row">
                            <div class="col-sm-4">
                                <select class="form-control form-control-sm" name="branch_cd" id="branch_cd" >
                                    <option value=''>담당</option>
                                        {{ Func::printOption($chargeBranch, $ci->branch_cd) }}
                                </select>
                            </div>
                        </div>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td>
                        <button type="button" class="btn btn-sm btn-success float-right mr-1" id="btn_confirm" onclick="popUpFull('/account/investmentpop?no={{$ci->loan_info_no}}','account{{$ci->loan_info_no}}');">투자계약창</button>
                    </td>
                    <td>
                        <div class="row">
                            <div class="col-sm-12">
                                <button type="button" class="btn btn-sm btn-secondary float-left mr-2" id="btn_confirm" onclick="popUpFull('/account/investorpop?no={{$ci->loan_usr_info_no}}', 'investor{{$ci->loan_usr_info_no}}');">투자자정보창</button>
                                <button type="button" class="btn btn-sm btn-secondary float-left mr-1" id="btn_confirm" onclick="popUpFull('/erp/customerpop?cust_info_no={{$ci->cust_info_no}}', 'cust_info{{$ci->cust_info_no}}');">차입자정보창</button>
                            </div>
                        </div>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>

    <div class="col-md-6">
        <table class="table table-sm table-bordered table-input text-xs">
            <colgroup>
            <col width="20%"/>
            <col width="80%"/>
            </colgroup>
            <tbody>
                <tr height=62>
                    <th>관리메모
                    </th>
                    <td>
                        <textarea class="form-control form-control-xs" name="loan_memo" id="loan_memo" rows="3" style="resize:none;">{{ $ci->loan_memo ?? "" }}</textarea>
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td></td>
                    <td>
                        <button class="btn btn-sm bg-lightblue float-right" onclick="custInfoAction();">저장</button>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
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
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });


    //$('#cust_info_form').append('loan_info_no',currLoanNo)
    var postdata = $('#cust_info_form').serialize();
    postdata+= "&loan_info_no=" + currLoanNo;


    $("#customer-contents").html(loadingString);
    $.post(
        "/erp/custinfoaction", 
        postdata, 
        function(data) {
        alert(data.result_msg);
        getCustData('info');
    });
}

function changeManagerCode(val, toid)
{
    $("#"+toid).empty();
    var option = $("<option value=''>담당자 미배분</option>");
    $("#"+toid).append(option);

    @foreach( $array_manager as $bcd => $vus)
    if( val=='{{ $bcd }}' )
    {
        @if( isset($vus) && sizeof($vus)>0 )
        @foreach( $vus as $vtmp )
        var option = $("<option value='{{ $vtmp->id }}'>{{ $vtmp->name }}</option>");
        $("#"+toid).append(option);
        @endforeach
        @endif
    }
    @endforeach
}


// 문자금지설정 - 상세
function banSmsErpDiv()
{
    alert();
}

$('input[name="ban_sms"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});
$('input[name="ban_post"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});
$('input[name="ban_call"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});
$('input[name="addr1_nlive_yn"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});
$('input[name="addr2_nlive_yn"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});
$('input[name="addr4_nlive_yn"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});
$('input[name="prhb_yn"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});



$(document).on('click', '#banSmsDiv .dropdown-menu', function (e) {
  e.stopPropagation();
});

function edmsSearch(div, loan_info_no, cust_info_no, filekey)
{
    // EDMS 웹소켓 연결
    P_onLoad();

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var data = 'div='+div+'&loan_info_no='+loan_info_no+'&cust_info_no='+cust_info_no+'&focusfilekey='+filekey;

    $.ajax({
        url  : '/config/socket',
        data : data,
        type : 'post',
        success : function(result)
        {
            P_SendMSG(result);
        },
        error : function(xhr)
        {
            alert('에러가 발생하였습니다.');
        }
    })
}


</script>