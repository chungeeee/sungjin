@extends('layouts.masterPop')



<title>민원등록</title>
@section('content')

<form  class="m-2" name="complain_form" id="complain_form" method="post" enctype="multipart/form-data" >
    <div class="content-wrapper needs-validation m-0">
        @csrf
        <input type="hidden" id="action_mode" name="action_mode" value="{{ $action_mode ?? '' }}">
        <input type="hidden" id="check_no" name="check_no" value="{{ $v->no ?? '' }}">
        <input type="hidden" id="cust_info_no" name="cust_info_no" value="{{ $v->cust_info_no ?? '' }}">
        <input type="hidden" id="loan_info_no" name="loan_info_no" value="{{ $v->loan_info_no ?? '' }}">
        <div class="">
            <div class="d-flex justify-content-between bd-highlight">
                <div class="p-2 bd-highlight" @if(isset($result['isPopup']) && $result['isPopup']==='Y') @endif >
                    <section class=" pl-3 pb-1">
                    <h5><i class="fas fa-file-invoice-dollar"></i> 민원등록</h5>
                    </section>
                </div>
            </div>

            <div class="p-2 bd-highlight col-md-5">
                <div class="form-group row">
                    <label for="search_string" class="col-sm-2 col-form-label">고객검색</label>
                    <div class="">
                        <input type="text" class="form-control form-control-sm" id="search_string" placeholder="차입자번호,이름,주민등록번호.." value="" />
                    </div>
                    <div class="col-sm-3 text-left">
                        <button type="button" class="btn btn-sm btn-info mr-3" onclick="searchComplainInfo();">검색</button>
                    </div>
                </div>
            </div>
            
        </div>
        

        {{-- 민원인 정보--}}
        <div class="col-md-12">
            <div class="form-group row collapse" id="collapseSearch">
                <div class="col-sm-5"></div>
                <label class="col-sm-1 col-form-label">회원검색</label>
                <div class="col-sm-6 " id="collapseSearchResult">
                </div>
            </div>

            <div class="card card-outline card-lightblue">

                <div class="card-header p-1">
                    <h3 class="card-title"><i class="fas fa-user m-2" size="9px"></i>민원인 정보 
                        <span style="cursor: pointer; color:#007bff;" onclick="openCustInfo({{$v->cust_info_no ?? ''}}, {{$v->loan_info_no ?? ''}});"><span>{{ isset($v->cust_info_no) && $v->cust_info_no != 0 ? "(".$v->cust_info_no.")" : '' }}</h3>
                    <div class="card-tools pr-2">
                    </div>
                </div>

                <div class="card-body p-1">
                    <table class="table table-sm table-bordered table-input text-xs">

                        <colgroup>
                        <col width="12%"/>
                        <col width="38%"/>
                        <col width="12%"/>
                        <col width="38%"/>
                        </colgroup>

                        <tbody>
                        <tr>
                            <th>접수처/접수일</th>
                            <td>
                                <div class="row">
                                    <div class="col-md-4 m-0 pr-0">
                                        
                                        <select class="form-control" name="complain_office_cd" id="complain_office_cd">
                                            <option value=''>접수처</option>
                                            {{ Func::printOption($configArr['complain_app_orgn_cd'],$v->complain_office_cd ?? '') }}
                                        </select>
                                        
                                    </div>
                                    <div class="col-md-4 m-0 pr-0">
                                        <div class="input-group date datetimepicker" id="div_app_date" data-target-input="nearest">
                                            <input type="text" class="form-control form-control-sm col-sm-10" DateOnly='true' id="app_date" name="app_date" placeholder="접수일" data-target="#div_app_date" value="{{ $v->app_date ?? '' }}"/>
                                            <div class="input-group-append" data-target="#div_app_date" data-toggle="datetimepicker">
                                                <div class="input-group-text text-xs text-center"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <th>민원관리</th>
                            <td>
                                <div class="row">
                                    <div class="col-md-4">
                                    <select class="form-control" name="person_manage" id="person_manage">
                                        <option value=''>민원관리</option>
                                        {{ Func::printOption($configArr['person_manage_cd'], $v->person_manage ?? '') }}   
                                    </select>
                                    </div>
                                    <div class="text-red">
                                     * 고객정보와 연동
                                    </div>
                                </div>
                            </td>
                        </tr> 

                        <tr>
                            <th><span class="text-danger font-weight-bold h6 mr-1">*</span>민원인성명</th>
                                <td>
                                    <div >
                                        <input type="text" class="form-control col-md-4" id="cust_name" name="cust_name" placeholder="민원인성명" value="{{ $v->cust_name ?? '' }}" >
                                    </div>
                                </td>
                            <th><span class="text-danger font-weight-bold h6 mr-1">*</span>주민등록번호</th>
                            <td>
                                <div class="col-md-10 row pl-2">
                                    <input type="text" class="form-control col-md-4 mr-1" name="ssn1" id="ssn1" value="{{ substr($v->ssn ?? '',0, 6) }}" onkeyup="onlyNumber(this);" maxlength=6 minlength=6 required>
                                    <input type="text" class="form-control col-md-4" name="ssn2" id="ssn2" value="{{ isset($v->ssn)?substr($v->ssn ?? '', 6, 7):'' }}" onkeyup="onlyNumber(this);" maxlength=7 minlength=7 required>
                                    <div>
                                    <div id="ssn1_error" class="text-danger pl-2 error-msg"></div>
                                    <div id="ssn2_error" class="text-danger pl-2 error-msg"></div>
                                    </div>
                                </div>
                            </td>
                        </tr> 
                        <tr>
                            <th>성별</th>
                                <td>
                                    <div >
                                        <input type="text" style="background: #FFFF;border:0px;" class="form-control col-md-4" id="gender" name="gender" value="{{ !empty($v->ssn)?($gender ?? ''):'' }}" readonly>
                                    </div>
                                </td>
                            <th>나이</th>
                                <td>
                                    <div >
                                        <input type="text" style="background: #FFFF;border:0px;" class="form-control col-md-4" id="age" name="age" value="{{ !empty($v->ssn)?($age ?? ''):'' }}" readonly>
                                    </div>
                                </td>
                        </tr> 
                        <tr>
                            <th>전화번호</th>
                            <td>
                                <div class="row">
                                    
                                    <div class="col-md-2 m-0 pr-0">
                                        <input type="text" class="form-control" name="ph11" id="ph11" value="{{ $v->ph11 ?? '' }}" onkeyup="onlyNumber(this);" maxlength=3 required>
                                        <div id="ph11_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                    </div>
                                    <div class="col-md-2 m-0 pr-0">
                                        <input type="text" class="form-control" name="ph12" id="ph12" value="{{ $v->ph12 ?? '' }}" onkeyup="onlyNumber(this);" maxlength=4 required>
                                        <div id="ph12_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                    </div>
                                    <div class="col-md-2 m-0 pr-0">
                                        <input type="text" class="form-control" name="ph13" id="ph13" value="{{ $v->ph13 ?? '' }}" onkeyup="onlyNumber(this);" maxlength=4 required>
                                        <div id="ph13_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                    </div>
                                </div>
                            </td>
                            <th>휴대폰번호</th>
                            <td>
                                <div class="row">
                                    
                                    <div class="col-md-2 m-0 pr-0">
                                        <input type="text" class="form-control" name="ph21" id="ph21" value="{{ $v->ph21 ?? '' }}" onkeyup="onlyNumber(this);" maxlength=3 required>
                                        <div id="ph21_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                    </div>
                                    <div class="col-md-2 m-0 pr-0">
                                        <input type="text" class="form-control" name="ph22" id="ph22" value="{{ $v->ph22 ?? '' }}" onkeyup="onlyNumber(this);" maxlength=4 required>
                                        <div id="ph22_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                    </div>
                                    <div class="col-md-2 m-0 pr-0">
                                        <input type="text" class="form-control" name="ph23" id="ph23" value="{{ $v->ph23 ?? '' }}" onkeyup="onlyNumber(this);" maxlength=4 required>
                                        <div id="ph23_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>직업</th>
                            <td>
                                <div>
                                    <select class="form-control col-md-4" name="job_cd" id="job_cd">
                                        <option value=''>직업</option>
                                        {{ Func::printOption(Func::getJobCd(), substr($v->job_cd ?? '' , 0, 1)) }}
                                    </select>
                                </div>
                            </td>
                            <th>지역</th>
                            <td>
                                <div >
                                    <input type="text" class="form-control col-md-4" id="local" name="local" value="{{ $v->local ?? '' }}" placeholder="지역" value="" >
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>주소</th>
                            <td colspan="3">
                                <div class="row">
                                    <div class="input-group col-sm-5 pb-1">
                                    <input type="text" class="form-control" name="zip1" id="zip1" numberOnly="true" value="{{ $v->zip1 ?? '' }}" readOnly>
                                    <span class="input-group-btn input-group-append">
                                    <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('zip1', 'addr11', 'addr12', '')">검색</button>
                                    </span>
                                    </div>
                                </div>
                                <input type="text" class="form-control mb-1 col-md-9" name="addr11" id="addr11" value="{{ $v->addr11 ?? '' }}" readOnly>
                                <input type="text" class="form-control col-md-9" name="addr12" id="addr12" value="{{ $v->addr12 ?? '' }}" maxlength="100">
                            </td>
                        </tr>
                        <tr>
                            <th>등록시간</th>
                            <td>
                                <input type="text" style="background: #FFFF;border:0px;" class="form-control col-md-4" name="save_time" id="save_time" value="{{ $action_mode == 'UPD'?Func::dateFormat($v->save_time):'' }}" readonly>
                            </td>
                            <th>이메일</th>
                            <td><input type="text" class="form-control col-md-4" name="cust_email" id="cust_email" value="{{ $v->cust_email ?? '' }}" placeholder="이메일"></td>
                        </tr>
                        <tr>
                            <th>조치결과</th>
                            <td>
                                <div>
                                    <select class="form-control col-md-4" name="action_rs" id="action_rs">
                                        <option value=''>조치결과</option>
                                        {{ Func::printOption($complainResult, $v->action_rs ?? '') }}   
                                    </select>
                                </div>
                            </td>
                            <th>처리상태</th>
                            <td>
                                <div>
                                    <select class="form-control col-md-4" name="prc_rs" id="prc_rs">
                                        <option value=''>처리상태</option>
                                        {{ Func::printOption($complainStatus, $v->prc_rs ?? '') }}   
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>발생부서</th>
                            <td>
                                <div>
                                    <select class="form-control col-md-4" name="occur_branch" id="occur_branch" onchange="changeOccurCode(this.value,'occur_id');">
                                        <option value=''>발생부서</option>
                                        {{ Func::printOption($branchArr, $v->occur_branch ?? '') }}
                                    </select>
                                </div>
                            </td>
                            <th>발생대상자</th>
                            <td>
                                <div>
                                    <select class="form-control col-md-4" name="occur_id" id="occur_id">
                                        @if ($action_mode == 'INS')
                                            <option value=''>발생대상자</option> 
                                        @else
                                            @if (isset($v->occur_id))
                                                <option value=''>발생대상자</option>
                                                {{ Func::printOption($userArr, $v->occur_id ?? '') }}
                                            @else
                                                <option value=''>발생대상자</option>
                                            @endif
                                            
                                        @endif
                                    </select>
                                </div>
                            </td>
                        </tr>
                        
                    </table>
                </div>
            </div>
        </div>
        {{-- 민원인 정보 끝 --}}

        {{-- 민원 내용--}}
        <div class="col-md-12">
            <div class="card card-outline card-lightblue">

                <div class="card-header p-1">
                    <h3 class="card-title"><i class="fas fa-donate m-2"></i>민원내용</h3>
                    <div class="card-tools pr-2">
                    </div>
                </div>

                <div class="card-body p-1">
                    <table class="table table-sm table-bordered table-input text-xs">

                        <colgroup>
                        <col width="12%"/>
                        <col width="78%"/>
                        </colgroup>

                        <tbody>
                        <tr>
                            <th><span class="text-danger font-weight-bold h6 mr-1">*</span>민원제목</th>
                            <td>
                                <div >
                                    <input type="text" class="form-control col-md" id="complain_title" name="complain_title" placeholder="민원제목" value="{{ $v->complain_title ?? '' }}" >
                                </div>
                            </td>
                        </tr>  

                        <tr>
                            <th><span class="text-danger font-weight-bold h6 mr-1">*</span>민원내용</th>
                            <td><textarea class="form-control" rows="8" name="complain_memo" id="complain_memo">{{ $v->complain_memo ?? '' }}</textarea></td>
                        </tr> 

                        @if ($action_mode != 'INS')
                            <tr>
                                <th>등록자</th>
                                <td>
                                    <div >
                                        <input type="text" style="background: #FFFF; border:0px;" class="form-control col-md-2" id="save_id" name="save_id"  value="{{ Func::getArrayName($userArr, $v->save_id) }}" readonly>
                                    </div>
                                </td>
                            </tr> 
                        @endif
                        
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- 민원 내용 끝 --}}

        {{-- 민원 진행상황--}}
        <div class="col-md-12">
            <div class="card card-outline card-lightblue">

                <div class="card-header p-1">
                    <h3 class="card-title"><i class="fas fa-donate m-2"></i>민원 진행상황</h3>
                    <div class="card-tools pr-2">
                    </div>
                </div>

                <div class="card-body p-1">
                    <table class="table table-sm table-bordered table-input text-xs">

                        <colgroup>
                        <col width="12%"/>
                        <col width="78%"/>
                        </colgroup>

                        <tbody>
                        <tr>
                            <th>요청날짜</th>
                            <td>
                                <div class="input-group date datetimepicker" id="div_req_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm col-sm-2" DateOnly='true' id="req_date" name="req_date" placeholder="요청날짜" data-target="#div_req_date" value="{{ $v->req_date ?? '' }}"/>
                                    <div class="input-group-append" data-target="#div_req_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                        </tr>  

                        <tr>
                            <th>제출기한</th>
                            <td>
                                <div class="input-group date datetimepicker" id="div_limit_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm col-sm-2" DateOnly='true' id="limit_date" name="limit_date" placeholder="제출기한" data-target="#div_limit_date" value="{{ $v->limit_date ?? '' }}"/>
                                    <div class="input-group-append" data-target="#div_limit_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                        </tr> 

                        <tr>
                            <th>요청사항</th>
                            <td><textarea class="form-control" rows="8" name="req_memo" id="req_memo">{{ $v->req_memo ?? '' }}</textarea></td>
                        </tr>
                        <tr>
                            <th>처리결과</th>
                            <td><textarea class="form-control" rows="8" name="prc_memo" id="prc_memo">{{ $v->prc_memo ?? '' }}</textarea></td>
                        </tr>
                        <tr>
                            <th>처리일자</th>
                            <td>
                                <div class="input-group date datetimepicker" id="div_prc_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm col-sm-2" DateOnly='true' id="prc_date" name="prc_date" placeholder="처리일자" data-target="#div_prc_date" value="{{ $v->prc_date ?? '' }}"/>
                                    <div class="input-group-append" data-target="#div_prc_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>처리결과회신방법</th>
                            <td>
                                <select class="form-control  col-md-2" name="reply_method" id="reply_method" >
                                    <option value=''>처리결과회신방법</option>
                                    {{ Func::printOption($configArr['reply_method_cd'], $v->reply_method ?? '') }} 
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>처리담당자</th>
                            <td>
                                <select class="form-control col-md-2" style="float: left;margin-right:10px;" name="prc_branch" id="prc_branch" onchange="changeOccurCode(this.value,'prc_manager_id');">
                                    <option value=''>처리부서</option>
                                    {{ Func::printOption($branchArr, $v->prc_branch ?? '') }}
                                </select>
                                <select class="form-control col-md-2" style="float: left;" name="prc_manager_id" id="prc_manager_id">
                                    @if ($action_mode == 'INS')
                                        <option value=''>처리담당자</option>
                                    @else
                                        @if (isset($v->prc_manager_id))
                                            <option value=''>처리담당자</option>
                                            {{ Func::printOption($userArr, $v->prc_manager_id ?? '') }}
                                        @else
                                            <option value=''>처리담당자</option>
                                        @endif
                                        
                                    @endif                      
                                </select>
                            </td>
                        </tr>
                        <!-- <tr>
                            <th>첨부파일</th>
                            <td>
                                <div class="input-group custom-file" style="width: 215px;" >
                                    <input type="file" class="custom-file-input form-control-xs text-xs" id="customFile"  style="cursor:pointer;">
                                    <label class="custom-file-label mb-0 text-xs form-control-xs" for="customFile" style="text-align: left">Choose file</label>
                                </div>
                            </td>
                        </tr> -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
        {{-- 민원 진행상황 끝 --}}



        
        {{-- <div class="card-footer text-center col-md-12"> --}}
            <div class="row justify-content-center">
                {{-- @if( Func::funcCheckPermit("L031") ) --}}
                @if ($action_mode == 'INS')
                    <button type="button"style="margin-bottom: 10px;" class="btn btn-sm bg-lightblue" onclick="complainFormAction('INS');">등록</button>
                @else
                    <button type="button" style="margin-right: 10px;margin-bottom: 10px;" class="btn btn-sm bg-lightblue" onclick="complainFormAction('UPD');">수정</button>
                    <button type="button" style="margin-left:10px;margin-bottom: 10px;" class="btn btn-sm bg-lightblue" onclick="complainFormAction('DEL');">삭제</button>
                @endif
                {{-- @endif --}}
            </div>
        {{-- </div> --}}

          
    </div>

</form>


@endsection

@section('javascript')

<script>
@if(isset($search_no)&&!empty($search_no))
selectLoanInfo('{{$search_no}}');
@endif


// 로드시 스크롤위치 조정
$(document).ready(function(){
    $(window).scrollTop(0);
});


// 디스플레이상태 변경하기(변경할div id,비교flag)
function setDisplay(divId,flag)
{
    var arrayDisplayFlag = new Array();
    arrayDisplayFlag['agent_cd_div'] = '03';

    if(arrayDisplayFlag[divId] == flag)
    {
        $('#'+ divId).show();
    }
    else
    {
        $('#'+ divId +' option:eq(0)').prop("selected", true);
        $('#'+ divId).hide();
    }
}


// 민원 등록 Action
function complainFormAction(mode) 
{
    if(mode == 'DEL')
    {
        $('#action_mode').val(mode);
    }

    var postdata = $('#complain_form').serialize();
    $.ajax({
        url  : "/erp/complainaction",
        type : "post",
        data : postdata,
        success : function(data)
        {
            // 유효성검사 실패시 에러메세지 표시
            if(data.error) 
            {
                alertErrorMsg(data.error);
            }
            // 성공알림 
            else if(data.rs_code=="Y") 
            {
                alert(data.rs_msg);
                //location.reload();
                //opener.goTab("/erp/complain", "N", "complain");
                opener.listRefresh();
                window.close();
            }
            // 실패알림
            else 
            {
                alert(data.rs_msg);
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
}


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

function openCustInfo(cust_info_no, loan_info_no)
{
    popUpFull("/erp/custpop?cust_info_no="+cust_info_no+"&no="+loan_info_no);
}

// 고객검색
function searchComplainInfo()
{
    var search_string = $("#search_string").val();
    if( search_string=="" )
    {
        alert("검색어를 입력해주세요.");
        $("#search_string").focus();
        return false;
    }
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
    $("#collapseSearchResult").html(loadingStringtxt);
    $('.collapse').collapse('show');
    $.post("/erp/searchcomplaininfo", {search_string:search_string}, function(data) {
        $("#collapseSearchResult").html(data);
        $(".table").css('font-size', '0.8rem');
    });

}

function selectInfo(cust_info_no, loan_info_no)
{
    if(confirm(cust_info_no+'번 고객님의 고객정보를 가져오시겠습니까? '))
    {
        var url = "/erp/getcustinfo";
        var formdata = "cust_info_no="+cust_info_no;
        
        jsonAction(url, 'POST', formdata, function (data) {
            var memo = '';
            if(data!=null)
            {
                //console.log(data);

                // 가져온 데이터로 폼 채우기
                var arrayIds = $('#complain_form').serializeArray();                

                /*arrayIds.forEach(function(v) {
                        // 입력되어 있는 정보를 지운다.
                        if(v.name!='_token' && v.name!='action_mode' && v.name!='app_type_cd' && v.name!='save_id' && v.name!='save_time') {
                            $('#'+v.name).val('');
                        }

                        if(v.name!='_token' && v.name!='memo' && v.name!='app_type_cd' && v.name!='save_id' && v.name!='save_time') {
                            
                            if(eval("data."+v.name))
                            {                                
                                console.log(v.name);
                                $('#'+v.name).val(eval("data."+v.name));
                            }
                            
                        }                        
                }); 
                */

                // 계약번호
                $('#cust_name').val(data.name);

                $("#ssn1").val(data.ssn.substring(0, 6));
                $("#ssn2").val(data.ssn.substring(6, 7));

                if(data.ssn.substring(6,7) == '1' || data.ssn.substring(6,7) == '3')
                {
                    $('#gender').val('남');
                }
                else
                {
                    $('#gender').val('여');
                }
                
                if($('#cust_email').val() == '')
                {
                    $('#cust_email').val(data.email);
                }

                $('#job_cd').val(data.job_cd.substring(0,1));
                $('#local').val(data.local);

                $('#zip1').val(data.zip1);
                $('#addr11').val(data.addr11);
                $('#addr12').val(data.addr12);

                if($('#ph11').val() == '' && $('#ph12').val() == '' && $('#ph13').val() =='')
                {
                    $('#ph11').val(data.ph11);
                    $('#ph12').val(data.ph12);
                    $('#ph13').val(data.ph13);
                }

                if($('#ph21').val() == '' && $('#ph22').val() == '' && $('#ph23').val() == '')
                {
                    $('#ph21').val(data.ph21);
                    $('#ph22').val(data.ph22);
                    $('#ph23').val(data.ph23);
                }

                $('#cust_info_no').val(cust_info_no);
                $('#loan_info_no').val(loan_info_no);

                var date = new Date();
                var year = date.getFullYear(); 
                var month = new String(date.getMonth()+1); 
                var day = new String(date.getDate()); 

                if(month.length == 1){ 
                month = "0" + month; 
                } 
                if(day.length == 1){ 
                day = "0" + day; 
                }

                var today = year + month + day;
                var age = 0;
                var y = '';
                if(data.ssn.substring(6,7) == '1' || data.ssn.substring(6,7) == '2')
                {
                    y = '19';
                }
                else
                {
                    y = '20';
                }
                
                age = Number(today.substring(0,4)) - Number(y + data.ssn.substring(0,2));

                if(today.substring(4,8) < data.ssn.substring(2,6))
                {
                    $('#age').val(age);
                }
                else
                {
                    $('#age').val(age-1);
                }

            }
            else
            {
                memo = '결재로그 없음';
            }
        });

        $('.collapse').collapse('hide');
    }
    else 
    {

    }    
}

// 엔터막기
function enterClear()
{
    $('#search_string').keydown(function() {
      if (event.keyCode === 13)
      {
        event.preventDefault();
        searchComplainInfo();
      };
    });
}
enterClear();

function changeOccurCode(val, toid)
{
    $("#"+toid).empty();
    if(toid == 'occur_id')
    {
        var option_string = "<option value=''>발생대상자</option>";
    }
    else
    {
        var option_string = "<option value=''>처리담당자</option>";
    }
    $("#"+toid).append(option_string);

    @foreach( $getUserId as $bcd => $vus)
    if( val=='{{ $bcd }}' )
    {
        @foreach( $vus as $vtmp )

        var option = $("<option value='{{ $vtmp->id }}'>{{ $vtmp->name }}</option>");
        $("#"+toid).append(option);

        @endforeach
    }
    @endforeach

}

</script>
@endsection