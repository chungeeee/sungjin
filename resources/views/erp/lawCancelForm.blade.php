@extends('layouts.masterPop')
@section('content')


<form class="form-horizontal" name="law_cancel_form" id="law_cancel_form">

<div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title">법착해지 결재요청</h2>
    </div>

    <div class="card-body mr-3 p-3">
        <div class="pt-3" id="con_id_area">
                <table class="table table-sm card-secondary card-outline loan-info-table table-head-fixed">
                <b class='pl-1 pb-1'>결재정보</b>
                    <tbody>
                        <tr>
                            <th class="w-10 align-middle">요청자</th>
                                <td class="w-10 pl-3 align-middle">
                                {{ isset($v->cancel_app_id)?$v->cancel_app_id:Func::getArrayName(Func::getUserId(),Auth::id())}}
                                </td>
                                <th class="w-10 align-middle">요청자 의견</th>
                                <td>
                                <textarea class="form-control form-control-sm" rows="1" name="cancel_app_memo" @if(isset($v->cancel_app_id) && $v->cancel_app_id != Auth::id()) readonly @endif>{{ $v->cancel_app_memo ?? ''}}</textarea>
                            </td>
                        </tr>
                        <tr class=" ">
                            <th class="align-middle">결재자</th>
                            <td class="align-middle">
                                <select class="form-control form-control-sm" name="cancel_confirm_id" id="cancel_confirm_id" onchange="setConfirmMemo(this.value,'')">
                                <option value="">선택</option>
                                {{ Func::printOption($arr_confirm_id['confirm_id_1'],empty($v->cancel_status)?'':$v->cancel_confirm_id) }}
                                </select>
                            </td>
                            <th class="align-middle">결재자 의견</th>
                            <td>
                            <textarea class="form-control form-control-sm" rows="1" name="cancel_confirm_memo" @if($v->cancel_confirm_id != Auth::id()) readonly @endif>{{ isset($v->cancel_confirm_memo)?$v->cancel_confirm_memo:"" }}</textarea>
                            </td>
                        </tr>
                    </tbody>
            </table>
        </div>  

    </div>
    <div class="card-footer">
        @if( (empty($v->cancel_status) || $v->cancel_status == "N") && Func::funcCheckPermit("A136","A") )
            <button type="button" class="btn btn-sm btn-info float-right ml-1"  onclick="lawCancelAction('A');">취하/해지 요청</button>
        @endif

        @if($v->cancel_status == 'A' && Func::funcCheckPermit("A236","A"))
            <button type="button" class="btn btn-sm btn-info float-right ml-1" onclick="lawCancelAction('Y');">취하/해지 결재</button>
            <button type="button" class="btn btn-danger btn-sm float-right ml-1" onclick="lawCancelAction('N');">취소</button>
        @endif
    </div>
    <input type="hidden" name="cancel_status" id="cancel_status">
    <input type="hidden" name="law_no" id="law_no" value="{{ $v->no }}">
    <input type="hidden" name="cust_info_no" id="cust_info_no" value="{{ $v->cust_info_no }}">
    <input type="hidden" name="loan_info_no" id="loan_info_no" value="{{ $v->loan_info_no }}">


    
</div>

</form>

@endsection

@section('javascript')


<!--
<script src="/plugins/moment/moment.min.js"></script>
<script src="/plugins/tempusdominus-bootstrap-4/js/tempusdominus-bootstrap-4.js"></script>
-->
<script>


// 로드시 스크롤위치 조정
$(document).ready(function(){
    window.resizeTo(1300, 450 );
});


function lawCancelAction(st)
{
    if( (st=='A' || st=='Y') && !$('#cancel_confirm_id').val())
    {
        alert("결재자를 지정해주세요.");
        return false;
    }
    if( st=="A" && !confirm("해당 법착에 대하여 취하/해지 요청을 하시겠습니까?") )
    {
        return false;
    }  
    if( st=="Y" && !confirm("해당 법착에 대하여 취하/해지 결재를 하시겠습니까?") )
    {
        return false;
    }  
    if( st=="N" && !confirm("해당 법착에 대하여 취하/해지 요청을 취소하시겠습니까?") )
    {
        return false;
    }  

    $('#cancel_status').val(st);

    if(ccCheck()) return;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#law_cancel_form').serialize();

    $.post("/erp/lawcancelaction", postdata, function(data) {
        alert(data.msg);
        if(data.rslt == "Y")
        {
            window.opener.getCustData('law','','',data.law_no);
            self.close();
        }
        globalCheck = false;
    }).fail(function(e) {
        console.log(e);
        globalCheck = false;
    });


}

function setConfirmMemo(id,lv)
{
    if("{{ Auth::id() }}" == id)
    {
        $("textarea[name='cancel_confirm_memo']").attr("readonly",false);
    }
    else
    {
        $("textarea[name='cancel_confirm_memo']").attr("readonly",true);
    }
}

</script>

@endsection
