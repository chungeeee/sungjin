@extends('layouts.masterPop')
@section('content')
<title>상각결재정보</title>


<div class="card card-lightblue">
    <div class="card-header-no-round">
    <h2 class="card-title font-weight-bold">상각결재정보</h2>
    </div>
</div>
<form id="sanggakForm">
<div class="row p-2">
{{-- <table class="table table-sm table-hover loan-info-table card-secondary card-outline"> --}}

    <div class="col-sm-6">
        <table class="table table-sm card-secondary card-outline table-bordered loan-info-table " height="240px">
        <input type="hidden" name="sanggak_no" value="{{ $v->no ?? '' }}">
        <input type="hidden" name="old_status" id="old_status" value="{{ $v->status ?? '' }}">
            <colgroup>
                <col width="16%"/>
                <col width="15%"/>
                <col width="16%"/>
                <col width="17%"/>        
                <col width="16%"/>
                <col width="17%"/>    
            </colgroup>
            <tbody>
                <tr>
                    <th>계약번호</th>
                    <td colspan=5>
                    <input type="hidden" id="old_loan_info_nos" @if(isset($v->loan_info_nos)) value="{{ isset($v->loan_info_nos)?str_replace(",","\n",$v->loan_info_nos):"" }}" @endif>
                    <textarea class="form-control form-control-sm" name="loan_info_nos" id="loan_info_nos" rows="8" @if(isset($v->status) && $v->status !="A") readonly @endif>{{ isset($v->loan_info_nos)?str_replace(",","\n",$v->loan_info_nos):"" }}</textarea>
                    <a type="button" class='pt-2' data-container="body" data-toggle="popover" data-html="true" data-placement="right" data-content="<img src='/img/sample_sanggakinfo.jpg'>">
                    엑셀파일 계약번호만을 복사해서 붙여넣기 해주세요. (예시)
                    </a>                    
                </td>
                </tr> 
                <tr>
                    <th class="align-middle">입력 계약건수</th><td class="text-center align-middle comma" id="loan_info_nos_cnt"></td>
                    <th class="align-middle">입력계약 잔액 합</th><td class="text-right align-middle comma" id="total_balance"></td>
                    <th class="align-middle">입력계약 이자 합</th><td class="text-right align-middle comma" id="total_interest_sum"></td>
                </tr> 
            @if(!isset($v->status) || $v->status != "Y") 
            <tr>
                <th class="align-middle">가능 계약건수</th> 
                <td class="text-center align-middle comma" id="ok_loan_info_nos_cnt"></td>
                <th class="align-middle">가능계약 잔액 합</th> 
                <td class="text-right align-middle comma" id="ok_total_balance"></td>
                <th class="align-middle">가능계약 이자 합</th> 
                <td class="text-right align-middle comma" id="ok_total_interest_sum"></td>
            </tr>
            @endif
            @if(isset($v->status) && $v->status == "Y")
            <tr> 
                <th class="align-middle">결재 계약건수</th> 
                <td class="text-center align-middle comma" >{{  isset($v->confirm_cnt)?$v->confirm_cnt:"" }}</td>
                <th class="align-middle">결재시 잔액 합</th> 
                <td class="text-right align-middle comma" >{{  isset($v->confirm_balance)?$v->confirm_balance:"" }}</td>
                <th class="align-middle">결재시 이자 합</th> 
                <td class="text-right align-middle comma" >{{  isset($v->confirm_interest_sum)?$v->confirm_interest_sum:"" }}</td>
            </tr> 
            @endif
            </tbody>
        </table>
    </div>

    <div class="col-sm-6">
        <table class="table table-sm card-secondary card-outline table-bordered loan-info-table " height="240px">
        <colgroup>
            <col width="10%"/>
            <col width="23%"/>
            <col width="10%"/>
            <col width="23%"/>
        </colgroup>
        <tbody>
            <tr>
                <th class="align-middle">상태</th><td  class="align-middle">
                    {{empty($v->status)?"결재요청":Vars::$arrayConfirmStatus[$v->status]}}
                    <input type="hidden" name="status" id="status">
                </td>
                <th class="align-middle">상각사유</th>
                <td>
                    <select class="form-control from-control-sm" name="sg_reason_cd" id="sg_reason_cd" >
                    <option value=''>선택</option>
                    {{ Func::printOption($array_sg_reason,isset($v->sg_reason_cd)?$v->sg_reason_cd:'') }}   
                    </select>
                </td>
            </tr>
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
                <th class="align-middle">결재일</th>
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
            <tr>
                <th>메모</th>
                <td colspan=5>
                <textarea class="form-control form-control-sm" name="memo" id="memo" rows="8">{{  isset($v->memo)?$v->memo:"" }}</textarea>
                </td>
            </tr>
        </tbody>
        </table>
    </div>

    <div class="col-sm-12 table-responsive" style="max-height: 300px;">
        <table class='table table-sm card-secondary card-outline loan-info-table table-head-fixed text-nowrap' id='loan_info_table'>
        <b class='pl-1 pb-1'>입력 계약정보</b>
        <colgroup>
        <col width='10%'/>
        <col width='15%'/>
        <col width='15%'/>
        <col width='15%'/>
        <col width='15%'/>
        <col width='15%'/>
        <col width='15%'/>
        </colgroup>
        <thead>
        <th class='text-center'>계약번호</th>
        <th class='text-center'>상태</th>
        <th class='text-center'>연체일</th>
        <th class='text-center'>잔액</th>
        <th class='text-center'>이자합계</th>
        <th class='text-center'>관리지점</th>
        <th class='text-center'>구분</th>
        </thead>
        <tbody id="loan_info_check" >
        </tbody>
        </table>
    </div>
</div>

        <div class="card-body p-2">
            <div id="con_id_area">
                <table class="table table-sm card-secondary card-outline loan-info-table table-head-fixed">
                <b class='pl-1 pb-1'>결재정보</b>
                    <tbody>
                        @foreach($arr_confirm_id as $col => $option_arr)
                            @if($col!="app_id") 
                                <tr class="col-md-12">
                                    @php
                                        $lv = substr($col,-1,1);
                                        $confirm_str = $lv!=3?$lv."차결재자":"최종결재자";
                                        $disabled = isset($v->no) && $v->{"confirm_date_".$lv}?"disabled":"";
                                        $readonly = isset($v->no) && $v->{'confirm_id_'.$lv} != Auth::id()?"readonly":"";
                                        $selected_id = "";
                                        
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
            <button type="button" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="sanggakAction('B')">1차결재</button>
            @elseif($v->status =="B")
            <button type="button" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="sanggakAction('C')">2차결재</button>
            @elseif($v->status =="C")
            <button type="button" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="sanggakAction('Y')">최종결재</button>
            @endif
        @endif
        @if(empty($v->status) || ($v->status != 'Y' &&  $v->status != 'N'))
        <button type="button" class="btn btn-sm bg-green float-right ml-1" onclick="sanggakPreview('{{ isset($v->status)?'Y':'A' }}')">미리보기</button>
        @endif
        @if(!empty($v->status) && $v->status != 'Y' &&  $v->status != 'N')
        <button type="button" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="sanggakAction('AA')">수정</button>
        <button type="button" class="btn btn-danger btn-sm float-right ml-1 mb-1" onclick="sanggakAction('N')">취소</button>
        @endif

        <input type="hidden" id="status_check">
        <div id="preview_msg_N"></div>
        
    </div>

    
</form>

@endsection

@section('javascript')

<script>

// 로드시 화면크기조정
$(document).ready(function(){
    window.resizeTo(1400, 900 );
});



function sanggakAction(status)
{
    if( status!="N" && !$('#status_check').val() )
    {
        alert("미리보기를 실행하여 입력건수,입력금액을 확인해주세요.");
        return false;
    }
    if($('#status_check').val()=="N" && status != "N")
    {
        alert("등록불가 계약이 존재합니다.");
        return false;
    }

    if((status=="N" ||  status=="Y") && $('#old_loan_info_nos').val() != $('#loan_info_nos').val()) 
    {
        alert("계약번호 내용이 변경되었습니다. \n수정버튼을 통해 저장 후 이용해주세요");
        return false;
    }
    
    if(!$('#sg_reason_cd').val())
    {
        alert("상각사유를 선택해 주세요.");
        $('#sg_reason_cd').focus();
        return false;
    }
    if(!$('#memo').val())
    {
        alert("메모를 입력해 주세요.");
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

    if( chk_status=='A' && !$('#confirm_id_1').val())
    {
        alert("1차결재자를 지정해주세요.");
        return false;
    }
    if( chk_status=='B'&& !$('#confirm_id_2').val())
    {
        alert("2차결재자를 지정해주세요.");
        return false;
    }
    if( chk_status=='C'&& !$('#confirm_id_3').val())
    {
        alert("최종결재자를 지정해주세요.");
        return false;
    }


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
        "/erp/sanggakaction", 
        postdata, 
        function(data) {
            alert(data.rs_msg);
            if(data.rslt == "Y")
            {
                window.opener.listRefresh();                
                self.close();
            }
            globalCheck = false;
    });
}
if("{{ isset($v->no) }}" && "{{ isset($v->no)?$v->status:'' }}" !="Y"  && "{{  isset($v->no)?$v->status:'' }}"!="N") // 결재완료나 결재취소때는 할필요 없음
{
    sanggakPreview();
}
else
{
    $('#loan_info_check').html("<tr><td colspan='6' class='text-center p-4'><span class='text-bold pt-1 pr-1'><i class='fas fa-user m-2'></i>등록불가 계약이 없습니다!</span></td></tr>");
}
function sanggakPreview(status)
{

    if($('#loan_info_nos').val()=='')
    {
        alert('계약번호를 입력해주세요');
        $('#loan_info_nos').focus();
        return false;
    }
    if(ccCheck()) return;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#sanggakForm').serialize();

    $('#loan_info_check').empty();
    $('#preview_msg_N').empty();
    $.post(
        "/erp/sanggakpreview", 
        postdata, 
        function(result) {
            $('#status_check').val(result.status_check);
            //if($('#status_check').val()=="N")
            if(result.loan_info_table!='')
            {
                $('#loan_info_check').html(result.loan_info_table);
                $('#preview_msg_N').html("<span class='float-right text-bold pt-1 pr-1 text-red'>* "+result.loan_info_nos_cnt+"건 중 "+result.n_cnt+"건 등록불가 </span>");
            }
            else
            {
                $('#loan_info_check').html("<tr><td colspan='6' class='text-center p-4'><span class='text-bold pt-1 pr-1'><i class='fas fa-user m-2'></i>등록불가 계약이 없습니다.</span></td></tr><tr><td colspan='6'></td></tr>");
            }
            $('#loan_info_nos_cnt').html(result.loan_info_nos_cnt);
            $('#total_balance').html(result.total_balance);
            $('#total_balance').html(result.total_balance);
            $('#total_interest_sum').html(result.total_interest_sum);
            $('#ok_loan_info_nos_cnt').html(result.ok_loan_info_nos_cnt);
            $('#ok_total_balance').html(result.ok_total_balance);
            $('#ok_total_interest_sum').html(result.ok_total_interest_sum);
            $('#total_cost_money').html(result.ok_total_cost_money);
            afterAjax();
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
