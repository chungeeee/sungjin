@extends('layouts.masterPop')
@section('content')




<form class="form-horizontal" name="nonintr_form" id="nonintr_form">
<input type="hidden" name="nonintr_no" value="{{ $nonintr_no }}">
<input type="hidden" name="introduction_no" value="{{ $rslt['introduction_no'] ?? '' }}">
<input type="hidden" name="nonintr_status" id="nonintr_status" value="{{ $rslt['status'] ?? 'A' }}">
<input type="hidden" id="change_flag" value="Y">

<div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title">무이자결재</h2>
    </div>

    
    <div class="card-body mr-3 p-3">
        <div class="form-group row">
            <label for="search_string" class="col-sm-2 col-form-label">고객검색</label>
            <div class="col-sm-4 ">
                <input type="text" class="form-control form-control-sm" id="search_string" placeholder="차입자번호,계약번호" value="" />
            </div>
            <div class="col-sm-6 text-left">
                <button type="button" class="btn btn-sm btn-info mr-3" id="btn_search_string" onclick="searchLoanInfo();">검색</button>
            </div>
        </div>
        <div class="form-group row collapse" id="collapseSearch">
        <label class="col-sm-2 col-form-label"></label>
        <div class="col-sm-10" id="collapseSearchResult">
        </div>
        </div>


        <div class="form-group row mt-2">
        <label for="cust_info_no" class="col-sm-2 col-form-label">고객번호</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="cust_info_no" name="cust_info_no" placeholder="고객번호" value="{{ $rslt['cust_info_no'] ?? '' }}" readonly>
        </div>
        <label for="cust_name" class="col-sm-2 col-form-label">고객명</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" id="cust_name" name="cust_name" placeholder="이름" value="{{ $rslt['cust_name'] ?? '' }}" readonly>
        </div>
        </div>
        
        <div class="form-group row">
        <label for="loan_info_no" class="col-sm-2 col-form-label">계약번호</label>
        <div class="input-group col-sm-4 pb-1">
            <input type="text" class="form-control pl-2" name="loan_info_no" id="loan_info_no" placeholder="계약번호" value="{{ $rslt['loan_info_no'] ?? '' }}"readOnly>
            <span class="input-group-btn input-group-append">
            <button class="btn btn-sm btn-primary" type="button" onclick="openLoanInfo()">계약정보창</button>
            </span>
        </div>

        <label for="return_date" class="col-sm-2 col-form-label">상환일</label>
        <div class="col-sm-4">
            <input type="text" class="form-control form-control-sm" placeholder="상환일"  name="return_date_chk" id="return_date"  value="{{ isset($rslt['return_date'])?Func::dateFormat($rslt['return_date']):'' }}" readonly>
        </div>
        </div>

        <div class="form-group row">
            <label for="balance" class="col-sm-2 col-form-label">기준잔액</label>
                <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm text-right comma" id="loan_balance" name="loan_balance" readonly 
                    @if(isset($rslt['status']) && $rslt['status'] == 'Y') value="{{ $rslt['balance'] }}" @else
                    value="{{ isset($rslt['loan_balance'])?number_format($rslt['loan_balance']):'' }}" @endif>
                    <div class="input-group-append">
                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fas fa-won-sign"></i></div>
                    </div>
                </div>
            </div>     

        <label for="loan_rate" class="col-sm-2 col-form-label">금리 / 연체금리</label>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" id="loan_rate" name="loan_rate" placeholder="금리" value="{{ isset($rslt['loan_rate'])?number_format($rslt['loan_rate'],2):'' }}" readonly>
        </div>
        <div class="col-sm-2">
            <input type="text" class="form-control form-control-sm" id="loan_delay_rate" name="loan_delay_rate" placeholder="연체금리" value="{{ isset($rslt['loan_delay_rate'])?number_format($rslt['loan_delay_rate'],2):'' }}" readonly>
        </div>
        </div>


        <div class="form-group row">
            <label for="new_loan_rate" class="col-sm-2 col-form-label">무이자기간</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" placeholder="무이자기간 일수입력" name="nonintr_term" id="nonintr_term"  value="{{ isset($rslt['nonintr_term'])?$rslt['nonintr_term']:'' }}" onkeyup="onlyNumber(this);resetValue();" maxlength="2">
            </div>        

            <label for="nonintr_cd" class="col-sm-2 col-form-label">무이자사유</label>
            <div class="col-sm-4">
                <select class="form-control form-control-sm pl-1" name="nonintr_cd" id="nonintr_cd">
                {{ Func::printOption($array_config['no_int_div_cd'], $rslt['nonintr_cd']) }}
                </select>
            </div>   
        </div>

        <div class="form-group row">
            <label for="nonintr_sdate" class="col-sm-2 col-form-label"> 무이자 시작일 / 종료일</label>
            <div class="col-sm-2" >
                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0" id="nonintr_sdate" data-target-input="nearest">
                        <input onchange="openLoanInfo()" type="text" class="form-control form-control-sm datetimepicker-input"  data-target="#nonintr_sdate" name="nonintr_sdate" id="nonintr_sdate_id" placeholder="시작일" DateOnly="true" size="6" value="{{ isset($rslt['nonintr_sdate'])?Func::dateFormat($rslt['nonintr_sdate']):'' }}">
                        <div class="input-group-append" data-target="#nonintr_sdate" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                        </div>
                    </div>
            </div>
            <div class="col-sm-2">
                <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0" id="nonintr_edate" data-target-input="nearest">
                    <input type="text" class="form-control form-control-sm datetimepicker-input change_disable" data-target="#nonintr_edate" name="nonintr_edate" id="nonintr_edate_id"  disabled placeholder="종료일" DateOnly="true" size="6" value="{{ isset($rslt['nonintr_edate'])?Func::dateFormat($rslt['nonintr_edate']):'' }}">
                    <div class="input-group-append" data-target="#nonintr_edate" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                    </div>
                </div>
            </div>

            <label for="reset_rate_date" class="col-sm-2 col-form-label"> 기존이자 적용일</label>
            <div class="col-sm-4">
                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0" id="reset_rate_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input change_disable" data-target="#reset_rate_date" name="reset_rate_date" id="reset_rate_date_id" v  disabled placeholder="기존이자적용일" DateOnly="true" size="6" value="{{ isset($rslt['reset_rate_date'])?Func::dateFormat($rslt['reset_rate_date']):'' }}">
                        <div class="input-group-append" data-target="#reset_rate_date" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                        </div>
                    </div>
            </div>
        </div>

        <div class="form-group row">
            <label for="nonintr_mny" class="col-sm-2 col-form-label">예상 무이자금액</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm text-right comma" id="nonintr_mny" name="nonintr_mny" @if(isset($rslt['status']) && $rslt['status'] == 'Y') value="{{ $rslt['nonintr_mny'] }}" @endif readonly >
                    <div class="input-group-append">
                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fas fa-won-sign"></i></div>
                    </div>
                </div>
            </div>
            <label for="sell_date" class="col-sm-2 col-form-label">메모</label>
            <div class="col-sm-4">
                <textarea class="form-control form-control-sm" name="memo">{{ $rslt['memo'] ?? '' }}</textarea>
            </div>
        </div>



        @if( isset($rslt['app_id']) )
        <div class="form-group row mt-2">
            <label for="search_string" class="col-sm-2 col-form-label">요청 등록일시</label>
            <div class="col-sm-4 col-form-label">
                {{ Func::getArrayName($array_user_id,$rslt['app_id']) }}
                ( {{ Func::dateFormat($rslt['app_time']) }} )
            </div>
        </div>
        <input type="hidden" name="app_id" value="{{ $rslt['app_id'] ?? '' }}" >
        @endif
        @if( isset($rslt['confirm_time_1']))
            <div class="form-group row">
                <label for="search_string" class="col-sm-2 col-form-label">1차결재 일시</label>
                <div class="col-sm-4 col-form-label">
                    {{  Func::getArrayName($array_user_id,$rslt['confirm_id_1']) }}
                    ( {{ Func::dateFormat($rslt['confirm_time_1']) }} )
                </div>
            </div>
        @endif
        @if( isset($rslt['confirm_time']))
            <div class="form-group row">
                <label for="search_string" class="col-sm-2 col-form-label">최종결재 일시</label>
                <div class="col-sm-4 col-form-label">
                    {{  Func::getArrayName($array_user_id,$rslt['confirm_id']) }}
                    ( {{ Func::dateFormat($rslt['confirm_time']) }} )
                </div>
            </div>
        @endif
        @if( isset($rslt['save_time']) && $rslt['status'] == "N")
            <div class="form-group row">
                <label for="search_string" class="col-sm-2 col-form-label">취소 일시</label>
                <div class="col-sm-4 col-form-label">
                    {{  Func::getArrayName($array_user_id,$rslt['save_id']) }}
                    ( {{ Func::dateFormat($rslt['save_time']) }} )
                </div>
            </div>
        @endif



    @if( !empty($l_v) )
        <div class="form-group row">
            <label for="nonintr_mny" class="col-sm-2 col-form-label">이전 결재내역</label>
            <div class="col-sm-4 col-sm-4 col-form-label">
                @foreach($l_v as $idx => $lv)
                    [{{ Vars::$arrayConfirmStatus[$lv->status] }}]
                    @if($lv->status == "N")
                        {{ Func::getArrayName($array_user_id,$lv->save_id) }} ({{ Func::dateFormat($lv->save_time) }})
                    @else
                        {{  Func::getArrayName($array_user_id,$lv->confirm_id) }} ({{ Func::dateFormat($lv->confirm_time) }})
                    @endif
                    <br>
                @endforeach
            </div>
        </div>
    @endif

    </div>

        <div class="p-4">
            <div class="card card-body" id="con_id_area" >
                <b class='pl-1 pb-1'>결재정보</b>
                <table class="table table-sm card-secondary card-outline table-head-fixed">
                    <tbody>
                        @foreach($array_confirm_id as $col => $option_arr)
                            @if($col!="app_id") 
                                <tr>
                                    @php
                                        $lv = substr($col,-1,1);
                                        $confirm_str = $lv!=2?$lv."차결재자":"최종결재자";
                                        $disabled = !empty($nonintr_no) && $rslt["confirm_time_".$lv]?"disabled":"";
                                        $readonly = !empty($nonintr_no) && $rslt['confirm_id_'.$lv] != Auth::id()?"readonly":"";
                                        $selected_id = "";                                        
                                        if(!empty($rslt[$col]) && !empty($rslt["confirm_time_".$lv])) // 나중에 권한이 빠지면 selectbox에 표기가 안되므로 ~date가 있을떄는 수기로 추가해주자 
                                        {
                                            $option_arr[$rslt[$col]] = Func::getArrayName($array_user_id,$rslt[$col]);
                                        }
                                    @endphp
                                    <th class="align-middle col-md-1">{{ $confirm_str }}</th>
                                    <td class="col-md-1">
                                        <select class="form-control form-control-sm mr-2 con-id-sel" name="{{ $col }}" id="{{ $col }}" {{ $disabled }} onchange="setConfirmMemo(this.value,'{{ $lv }}')">
                                        <option value="">{{ $confirm_str }}</option>
                                        {{ Func::printOption($option_arr,!empty($rslt[$col])?$rslt[$col]:$selected_id ) }}
                                        </select>
                                    </td>
                                    <th class="align-middle col-md-1">{{ $confirm_str." 의견" }}</th>
                                    <td class="col-md-6">
                                    <textarea class="form-control form-control-sm" rows="1" name="{{ 'confirm_memo_'.$lv }}" {{ $readonly }}>{{ isset($rslt['confirm_memo_'.$lv])?$rslt['confirm_memo_'.$lv]:"" }}</textarea>
                                    </td>
                                </tr>
                            @endif
                        @endforeach 
                    </tbody>
                </table>
            </div>
            <input type="hidden" name="confirm_level" id="confirm_level" value="{{ isset($loan['option_str'])?sizeof($loan['option_str']):0 }}">
        </div>


    <div class="card-footer">
        @if( $action_mode=="UPDATE" )
            @if( Func::funcCheckPermit("C040") )
                @if(isset($rslt['status']) && $rslt['status'] == "A")
                    <button type="button" class="btn btn-sm btn-info   float-right mr-1" id="btn_confirm" onclick="nonintrAction('CONFIRM_1');">1차결재</button>
                @elseif(isset($rslt['status']) && $rslt['status'] == "B")
                    <button type="button" class="btn btn-sm btn-info   float-right mr-1" id="btn_confirm" onclick="nonintrAction('CONFIRM');">최종결재</button>
                @endif
            @endif
            <button type="button" class="btn btn-sm btn-danger float-right mr-1" id="btn_delete"  onclick="nonintrAction('DELETE');" >취소</button>
            <button type="button" class="btn btn-sm btn-info float-right mr-1" id="btn_update"  onclick="nonintrAction('UPDATE');" >수정</button>
        @elseif( $action_mode=="INSERT" )
            <button type="button" class="btn btn-sm btn-info float-right mr-1" id="cate_btn" onclick="nonintrAction('INSERT');">무이자요청 등록</button>
        @endif
        @if( $action_mode=="UPDATE" ||  $action_mode=="INSERT")
            <button type="button" class="btn btn-sm bg-green float-right mr-1" onclick="nonintrPreview()">미리보기</button>
        @endif

    </div>
    


</div>

</form>

@endsection

@section('javascript')

<script>


// 로드시 화면크기조정
$(document).ready(function(){
    window.resizeTo(1300, 750 );
});


// 고객검색
function searchLoanInfo()
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
    $.post("/erp/tradeinsearch", {search_string:search_string}, function(data) {
        $("#collapseSearchResult").html(data);
    });
}



// 고객검색에서 선택한 고객의 계약정보 호출
function selectLoanInfo(n)
{
    if("{{$action_mode}}" != "INSERT" && $("#loan_info_no").val())
    {
        alert("계약번호 변경불가");
        return false;
    }
    var cin = $("#cust_info_no_"+n).html();
    var lin = $("#loan_info_no_"+n).html();
    var cnm = $("#cust_name_"+n).html();
    var rd = $("#return_date_"+n).html();
    var lr  = $("#loan_rate_"+n).val();
    var ldr = $("#loan_delay_rate_"+n).val();
    var lb = $("#loan_balance_"+n).html();
    var rmc = $("#return_method_cd_"+n).html();

    if(rmc != "F")
    {
        alert("만기일시 계약만 등록 가능합니다."); // 구 자유상환임.
        return false;
    }

    $("#cust_info_no").val(cin);
    $("#loan_info_no").val(lin);
    $("#cust_name").val(cnm);
    $("#loan_rate").val(lr);
    $("#loan_delay_rate").val(ldr);
    $("#return_date").val(rd);
    $("#loan_balance").val(lb);

    $('.collapse').collapse('hide');
}




function nonintrAction(md)
{
    if(checkValue(md) === false)
    {
        return false;
    }

    if( md=="INSERT" && !confirm("무이자요청을 등록하시겠습니까?") )
    {
        return false;
    }
    if( md=="DELETE" && !confirm("무이자요청을 취소하시겠습니까?") )
    {
        return false;
    }
    if( md=="CONFIRM_1" && !confirm("1차결재처리를 하시겠습니까?") )
    {
        return false;
    }
    if( md=="CONFIRM" && !confirm("최종결재처리를 하시겠습니까?") )
    {
        return false;
    }

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $(".change_disable").prop("disabled",false);

    var formData = new FormData($('#nonintr_form')[0]);
    formData.append("action_mode", md);


    if( md=="CONFIRM" )
    {
        $("#btn_confirm").prop("disabled",true);
        $("#btn_delete").prop("disabled",true);
    }
    

    $.ajax({
        url  : "/erp/nonintrformaction",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            console.log(result);
            if( result.rs=="Y" )
            {
                alert(result.rs_msg); 
                window.opener.listRefresh();
                self.close();
            }
            else
            {
                alert(result.rs_msg);  
                $("#btn_confirm").prop("disabled",false);
                $("#btn_delete").prop("disabled",false);
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
            $("#btn_confirm").prop("disabled",false);
            $("#btn_delete").prop("disabled",false);
        }
    });
}



function openLoanInfo()
{
    var cin = $("#cust_info_no").val();
    var lin = $("#loan_info_no").val();

    if( cin!="" && lin!="" )
    {
        loan_info_pop( cin, lin );
    }
    else
    {
        alert("검색으로 계약을 선택해주세요.");
    }
}
if("{{ isset($rslt['status'])?$rslt['status']:'' }}"=="A" || "{{ isset($rslt['status'])?$rslt['status']:'' }}"=="B")
{
    nonintrPreview();
}

function nonintrPreview()
{

    var lin = $("#loan_info_no").val();
    var sdate = $("#nonintr_sdate_id").val();
    var nt = $("#nonintr_term").val();
    var nc = $("#nonintr_cd").val();

    if(!lin)
    {
        alert("선택된 계약이 없습니다.");
        return false;
    }
    if(!nt || nt == 0)
    {
        alert("무이자기간 1일 이상으로 입력해주세요");
        return false;
    }
    if( nc == "005" && nt > 30)
    {
        alert("소개고객 이자면제일 경우 30일이상 등록할수 없습니다.");
        return false;
    }
    if(!sdate)
    {
        alert("무이자 시작일을 입력해주세요");
        return false;
    }


    var formData = new FormData($('#nonintr_form')[0]);

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
    url     : "/erp/nonintrpreview",
    type    : "post",
    data    : formData,
    processData: false,
    contentType: false,
    success : function(result)
    {
        console.log(result);
        $("#nonintr_edate_id").val(result.nonintr_edate);
        $("#nonintr_mny").val(result.nonintr_mny);
        $("#reset_rate_date_id").val(result.reset_rate_date);
        afterAjax();

        // 상환일변경 필요 플래그 세팅 
        var return_date   = new Date($('#return_date').val());
        var nonintr_edate = new Date($('#nonintr_edate_id').val());
        if(return_date<=nonintr_edate)
        {
            alert("무이자 종료일이 상환일과 같거나 이후입니다. \n결재완료될 경우 상환일이 변경됩니다.");
        }
    },
    error : function(xhr)
    {
        alert("통신오류입니다.");
        $("#btn_confirm").prop("disabled",false);
        $("#btn_delete").prop("disabled",false);
    }
});


}

// 무이자시작일 변경시 무이자종료일,기존이자적용일 초기화
$("#nonintr_sdate").on("change.datetimepicker", ({date, oldDate}) => {     
    if(oldDate)  
    {
        resetValue();
    }
})



// 엔터막기
function enterClear()
{
    $('#search_string').keydown(function() {
    if (event.keyCode === 13)
    {
        event.preventDefault();
        searchLoanInfo();
    };
    });
}

// 초기화
function resetValue()
{
    $('#nonintr_edate_id').val(null);
    $('#reset_rate_date_id').val(null);
    $('#nonintr_mny').val(0);
    $('#change_flag').val("N");
}

function checkValue(status)
{
    var nm = $('#nonintr_mny').val();
    var ns = $('#nonintr_status').val();
    if(!$("#loan_info_no").val())
    {
        alert("선택된 계약이 없습니다.");
        return false;
    }
    if($("#nonintr_cd").val() == "005" && nm.replaceAll(",","")>100000)
    {
        alert("소개고객 이자면제일 경우 무이자금액이 10만원 이상일수 없습니다.");
        return false;
    }

    if(status!="DELETE" && $('#nonintr_mny').val()==0)
    {
        alert("미리보기를 실행하여 입력값을 확인해주세요");
        return false;
    }
    if((status=="DELETE" ||  status=="CONFIRM") && $('#change_flag').val() != "Y") 
    {
        alert("입력값이 변경되었습니다. \n수정버튼을 통해 저장 후 이용해주세요");
        return false;
    }
    if( (status!="DELETE" ) && !$('#confirm_id_1').val())
    {
        alert("1차결재자를 지정해주세요.");
        return false;
    }
    if((status=="CONFIRM_1" || (status =="UPDATE" && ns =="B")) && !$('#confirm_id_2').val())
    {
        alert("최종결재자를 지정해주세요.");
        return false;
    }

    return true;
}


function setConfirmMemo(id,lv)
{
    if("{{ Auth::id() }}" == id)
    {
        $("textarea[name='confirm_memo_"+lv+"']").attr("readonly",false);
    }
    else
    {
        $("textarea[name='confirm_memo_"+lv+"']").attr("readonly",true);
    }
}

enterClear();
</script>

@endsection
