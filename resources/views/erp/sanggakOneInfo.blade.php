@extends('layouts.masterPop')
@section('content')
<title>상각결재정보</title>


<div class="card card-lightblue">
    <div class="card-header-no-round">
    <h2 class="card-title" > 
        <i class="fa fa-sm fa-window-restore mr-2"></i> 상각결재정보
    </h2>
</div>
<form id="sanggakForm">
    <input type="hidden" name="sanggak_no" value="{{ $v->no ?? '' }}">
    <input type="hidden" name="old_status" id="old_status" value="{{ $v->status ?? '' }}">
    <input type="hidden" name="loan_info_no" id="loan_info_no" value="{{ $simple['no'] }}">
    <input type="hidden" name="cust_info_no" id="cust_info_no" value="{{ $simple['cust_info_no'] }}">
<div >
{{-- <table class="table table-sm table-hover loan-info-table card-secondary card-outline"> --}}

    @if( !empty($simple) )
    <div class="col-sm-12">
        <section class="content-header pl-2 pb-0">
            <h6 class="font-weight-bold text-sm"><i class="fas fa-user mr-2"></i> 고객번호 {{ $simple['cust_info_no'] }} / {{ $simple['name'] }} 고객 상각결재</h6>
        </section>
        <div class="bg-white text-center col-12" style="cursor:pointer;" onclick="getPopUp('/erp/custpop?cust_info_no={{ $simple['cust_info_no'] ?? '' }}&no={{ $simple['no'] ?? '' }}','LOANPOP','width=2000, height=1000, scrollbars=yes');">
        @include('inc/loanSimpleLine')
    </div>
    @endif
        
    
    <div class="card-body p-2">
        <div id="con_id_area">
            <b class='pl-1 pb-1'>대손상각 내용등록</b>
            <table class="table table-sm card-secondary card-outline loan-info-table table-head-fixed">
    <!--<div class="card card-outline card-secondary">
        <div class="card-header p-1">
            <h3 class="card-title font-weight-bold text-sm"><i class="fas fa-table m-2"></i>대손상각정보</h3>
        </div> --}}
        <div class="card-body p-1">
            <table class="table table-sm table-bordered table-input text-xs">-->

                <colgroup>
                <col width="11%"/>
                <col width="14%"/>
                <col width="10%"/>
                <col width="14%"/>
                <col width="10%"/>
                <col width="14%"/>
                <col width="10%"/>
                <col width="14%"/>
                </colgroup>
                <tbody>
                <tr>
                    <th class="pt-2">대손상태</th>
                    <td class="pt-2 bold">
                        {{empty($v->status)?"결재요청":Vars::$arrayConfirmStatus[$v->status]}}                     </td>
                        <input type="hidden" name="status" id="status">
                    <th class="pt-2">
                        대출상태
                    </th>
                    <td class="pt-2 bold">
                        {!! $v->loan_status ?? '' !!}
                    </td>
                    <th class="pt-2">
                        연체일수
                    </th>
                    <td class="pt-2 bold">
                        {{ $v->loan_delay_term ?? '' }} 일
                    </td>
                    <th class="pt-2">
                        대손증빙
                    </th>
                    <td>
                        <select class="form-control from-control-sm col-md-6" name="cert_yn" id="cert_yn" >
                        <option value=''>선택</option>
                        {{ Func::printOption(['Y'=>'Y', 'N'=>'N'],isset($v->cert_yn)?$v->cert_yn:'') }}   
                        </select>
                    </td>
                </tr>

                <tr>
                    <th class="pt-2">대손사유 <span class="text-danger font-weight-bold h6 mr-1">*</span></th>
                    <td>
                        
                        <select class="form-control from-control-sm" name="sg_reason_cd" id="sg_reason_cd" >
                        <option value=''>선택</option>
                        {{ Func::printOption($array_sg_reason,isset($v->sg_reason_cd)?$v->sg_reason_cd:'') }}   
                        </select>
                        
                    </td>
                    <th class="pt-2">
                        법원
                    </th>
                    <td>
                        <select class="form-control form-control-sm selectpicker" data-live-search="true" name="court_cd" id="court_cd" title="선택">
                        {{ Func::printOption($array_court_cd,isset($v->court_cd)?$v->court_cd:'') }}   
                        </select>
                        
                    </td>
                    <th class="pt-2">
                        사건번호
                    </th>
                    <td>
                        <input type="text" class="form-control form-control-sm" name="event_no" id="event_no" maxlength=12 value="{{ $v->event_no ?? '' }}" placeholder="사건번호">
                    </td>
                    <th class="pt-2">
                        면책일
                    </th>
                    <td>
                        <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0" id="exemption_date" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm datetimepicker-input branch-disabled" data-target="#exemption_date" name="exemption_date" id="exemption_date" value="{{ $v->exemption_date ?? '' }}" DateOnly="true" size="6">
                            <div class="input-group-append" data-target="#exemption_date" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                            </div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th class="pt-2">대손상각<br>신청의견 <span class="text-danger font-weight-bold h6 mr-1">*</span></th>
                    <td colspan="3">
                        
                        <textarea class="form-control form-control-sm" name="memo" id="memo" rows="6">{{  isset($v->memo)?$v->memo:"" }}</textarea>
                        
                    </td>
                    <td colspan="4">
                        <table class="table table-sm card-secondary card-outline table-bordered loan-info-table ">
                            <colgroup>
                                <col width="10%"/>
                                <col width="23%"/>
                                <col width="10%"/>
                                <col width="23%"/>
                            </colgroup>
                            <tbody>
                                @if(isset($v->status))
                                <tr>
                                    <th class="align-middle">요청일</th>
                                    <td class="align-middle">{{ isset($v->app_time)?Func::dateFormat($v->app_time):"" }}</td>
                                    <th class="align-middle">요청자</th>
                                    <td class="align-middle" >{{ isset($v->app_id)?Func::getArrayName($array_user_id,$v->app_id):"" }}</td>
                                </tr> 
                                @endif
                                @if(isset($v->status) && $v->status == "Y")
                    
                                <tr>
                                    <th class="align-middle">최종결재일</th>
                                    <td class="align-middle">{{ isset($v->confirm_time)?Func::dateFormat($v->confirm_time):"" }}</td>
                                    <th>결재자</th>
                                    <td class="align-middle">{{ isset($v->confirm_id)?Func::getArrayName($array_user_id,$v->confirm_id):"" }}</td>
                                </tr>
                                @elseif(isset($v->status) && $v->status == "N")
                                <tr>
                                    <th class="align-middle">취소일</th>
                                    <td class="align-middle">{{ isset($v->cancel_time)?Func::dateFormat($v->cancel_time):"" }}</td>
                                    <th>취소자</th>
                                    <td class="align-middle">{{ isset($v->cancel_id)?Func::getArrayName($array_user_id,$v->cancel_id):"" }}</td>
                                </tr>
                                @endif
                            </tbody>
                            </table>
                    </td>
                </tr>
            </table>
        </div>
    </div>


    

    <div class="card-body p-2">
        <div id="con_id_area">
            <b class='pl-1 pb-1'>결재정보</b>
            <table class="table table-sm card-secondary card-outline loan-info-table table-head-fixed">
                <tbody>
                    @foreach($arr_confirm_id as $col => $option_arr)
                        @if($col!="app_id") 
                            <tr class="col-md-12">
                                @php
                                    $lv = substr($col,-1,1);
                                    $confirm_str = $lv!=3?$lv."차결재자":"최종결재자";
                                    $confirm_str = "결재자";
                                    $disabled = isset($v->no) && $v->{"confirm_time"}?"disabled":"";
                                    $readonly = isset($v->no) && $v->{'confirm_id'} != Auth::id()?"readonly":"";
                                    $selected_id = "";
                                    $col = 'confirm_id';
                                    if(!empty($v->$col) && !empty($v->{"confirm_date_".$lv})) // 나중에 권한이 빠지면 selectbox에 표기가 안되므로 ~date가 있을떄는 수기로 추가해주자 
                                    {
                                        $option_arr[$v->$col] = Func::getArrayName($array_user_id,$v->$col);
                                    }
                                @endphp
                                <th class="align-middle col-md-1">{{ $confirm_str }}</th>
                                <td class="col-md-1 ">
                                    <select class="form-control form-control-sm mr-2 con-id-sel" name="{{ $col }}" id="{{ $col }}" {{ $disabled }} onchange="setConfirmMemo(this.value,'{{ $lv }}')">
                                    <option value="">{{ $confirm_str }}</option>
                                    {{ Func::printOption($option_arr,!empty($v->$col)?$v->$col:$selected_id) }}
                                    </select>
                                </td>
                                <th class="align-middle col-md-1">{{ $confirm_str." 의견" }}</th>
                                <td class="col-md-6">
                                <textarea class="form-control form-control-sm" rows="1" name="{{ 'confirm_memo_'.$lv }}" {{ $readonly }}>{{ isset($v->{'confirm_memo_'.$lv})?$v->{'confirm_memo_'.$lv}:"" }}</textarea>
                                </td>
                            </tr>
                        @endif
                    @endforeach 
                </tbody>
            </table>
        </div>
        <input type="hidden" name="confirm_level" id="confirm_level" value="{{ isset($loan['option_str'])?sizeof($loan['option_str']):0 }}">
    </div>


    <div class="card-footer ">
        {{-- 상각결재처리 권한 --}}
        @if(empty($v->status))
            <button type="button" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="sanggakAction('A')">상각요청 등록</button>
        @elseif(Func::funcCheckPermit("C060"))
            @if( $v->status =="A" )
            <button type="button" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="sanggakAction('Y')">결재</button>
            <!-- <button type="button" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="sanggakAction('B')">1차결재</button> -->
            @elseif($v->status =="B")
            <button type="button" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="sanggakAction('C')">2차결재</button>
            @elseif($v->status =="C")
            <button type="button" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="sanggakAction('Y')">최종결재</button>
            @endif
        @endif
        
        @if(!empty($v->status) && $v->status != 'Y' &&  $v->status != 'N')
        <button type="button" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="sanggakAction('AA')">수정</button>
        <button type="button" class="btn btn-danger btn-sm float-right ml-1 mb-1" onclick="sanggakAction('N')">취소</button>
        @endif

                
    </div>

    
</form>

@endsection

@section('javascript')

<script>

// 로드시 화면크기조정
$(document).ready(function(){
    window.resizeTo(1200, 800 );
});



function sanggakAction(status)
{
    
    if(!$('#sg_reason_cd').val())
    {
        alert("대손사유를 선택해 주세요.");
        $('#sg_reason_cd').focus();
        return false;
    }
    if(!$('#memo').val())
    {
        alert("대손상각 신청의견을 입력해 주세요.");
        $('#memo').focus();
        return false;
    }
    
    if( status=='AA' || status=='N')
    {
        var chk_status = $('#old_status').val();
    }
    else
    {
        var chk_status = status;
    }

    if( chk_status=='A' && !$('#confirm_id').val())
    {
        alert("결재자를 지정해주세요.");
        return false;
    }
    // if( chk_status=='A' && !$('#confirm_id_1').val())
    // {
    //     alert("1차결재자를 지정해주세요.");
    //     return false;
    // }
    // if( chk_status=='B'&& !$('#confirm_id_2').val())
    // {
    //     alert("2차결재자를 지정해주세요.");
    //     return false;
    // }
    // if( chk_status=='C'&& !$('#confirm_id_3').val())
    // {
    //     alert("최종결재자를 지정해주세요.");
    //     return false;
    // }


    if( status=="A" && !confirm("상각요청을 등록하시겠습니까?") )
    {
        return false;
    }
    if( status=="AA" && !confirm("상각요청정보를 수정하시겠습니까?") )
    {
        return false;
    }
    if( status=="N" && !confirm("상각요청을 취소하시겠습니까?") )
    {
        return false;
    }
    if( status=="Y" && !confirm("상각요청을 결재하시겠습니까?") )
    {
        return false;
    }

    $('#status').val(status);

    if(ccCheck()) return;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#sanggakForm').serialize();

    $.post(
        "/erp/sanggakoneaction", 
        postdata, 
        function(data) {
            alert(data.rs_msg);
            if(data.rslt == "Y")
            {
                if( status!="A")
                {
                    window.opener.listRefresh();                
                }
                self.close();
            }
            globalCheck = false;
    });
}

$(function () {
        // Enables popover
        $("[data-toggle=popover]").popover();
    });


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
</script>

@endsection
