@extends('layouts.masterPop')
@section('content')




<form class="form-horizontal" name="rebuy_form" id="rebuy_form">
<input type="hidden" name="sms_check_no" value="{{ $result->no ?? '' }}">

<div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title"><i class="fas fa-mobile-alt"></i> SMS/LMS 발송 결재 </h2>
    </div>
    <div class="card-body mr-3 p-3">
        <div class="form-group row">
            <label for="search_string" class="col-sm-2 col-form-label">SMS/LMS 내용</label>
            <textarea class="col-sm-8 " rows="7" @if($edit)  name="message"  @else disabled="" @endif style="resize:none;">{{ $result->message ?? '' }}</textarea>
        </div>
        <div class="form-group row">
            <label for="cust_info_no" class="col-sm-2 col-form-label"></label>
            <div class="col-sm-2 custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" name="multpl_yn" id="multpl_yn" value="Y" @if(isset($result->multpl_yn) && $result->multpl_yn == "Y" ) checked @endif >
                <label for="multpl_yn" class="custom-control-label">채권통합</label>
            </div>
            <div class="col-sm-5 custom-control custom-checkbox">
                <input class="custom-control-input" type="checkbox" id="coll_yn" name="coll_yn" value="Y" @if(isset($result->coll_yn) && $result->coll_yn == "Y" ) checked @endif >
                <label for="coll_yn" class="custom-control-label">추심여부</label>
            </div>
            <div  class="input-group col-sm-2">
               [{{ strlen($result->message) ?? '0' }} Byte]
            </div>
        </div>
        <div class="form-group row mt-2">
            <label for="cust_info_no" class="col-sm-2 col-form-label">발송대상<br>({{ $result->ups_erp_str ?? '' }})</label>
            <div class="col-sm-8">
                <div class="card-body table-responsive p-0" style="height: 300px;">
                    <table class="table table-head-fixed text-nowrap table-sm">
                    <thead>
                        <tr>
                            <th>계약(접수)번호</th>
                            <th>이름</th>
                            <th>생년월일</th>
                            <th>상태</th>
                            <th>발신번호</th>
                            <th>미리보기</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loan_li as $li)
                            <tr>
                                <td>
                                    @if($result->status=='A')<input type='checkbox' name='listChk[]' class='list-check' value='{{ $li->no ?? ''}}' checked>@endif
                                    {{ $li->no ?? ''}}</td>
                                <td>{{ $li->name ?? ''}}</td>
                                <td>{{ $li->ssn ?? ''}}</td>
                                <td>{!! $li->status ?? '' !!}</td>
                                <td>{{ $li->ph2 ?? ''}}</td>
                                <td>{{ $li->message ?? ''}}</td>
                            </tr>
                        @empty
                        <tr>
                            <td colspan="4">발송대상이 없습니다.</td>
                        </tr>
                        @endforelse
                    </tbody>
                    </table>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="loan_info_no" class="col-sm-2 col-form-label">발신번호</label>
            <div class="input-group col-sm-4 pb-1">
                <input type="text" class="form-control" @if($edit) name="sender"  @else  disabled="" @endif id="sender" placeholder="계약번호" value="{{ $result->sender ?? '' }}" >
            </div>
        </div>
        <div class="form-group row">
            <label for="send_date" class="col-sm-2 col-form-label">발송일</label>
            <div class="row col-sm-6">
                <div class="input-group date datetimepicker col-sm-4">
                    <input type="text" class="form-control form-control-sm text-right datetimepicker-input dateformat datetimepicker readonlys" name="send_date" id="send_date"
                        placeholder="방문요청일" autocomplete="off" value="{{ $result->send_date ?? '' }}">
                    <div class="input-group-append" data-target="#send_date" data-toggle="datetimepicker">
                        <div class="input-group-text ml-1"><i class="fa fa-calendar" style="font-size: 0.8rem;"></i></div>
                    </div>
                </div>
                <div class="input-group col-sm-2">
                    <input type="text" class="form-control form-control-sm text-right hourformat readonlys" name="send_hour" id="send_hour"
                        placeholder="24시" autocomplete="off" value="{{$result->send_hour ?? '' }}" maxlength="2">
                    <label for="" class="mt-1 ml-2 mr-1"> 시</label>
                </div>
            </div>
        </div>
        <div class="form-group row">
            <label for="sell_date" class="col-sm-2 col-form-label">요청메모</label>
            <div class="col-sm-4">
                <textarea class="form-control form-control-sm" name="rebuy_memo">{{ $result->req_memo ?? '' }}</textarea>
            </div>

        </div>
        <div class="form-group row">

        </div>
        <div class="form-group row mt-2">
            <label for="search_string" class="col-sm-2 col-form-label">요청 등록일시</label>
            <div class="col-sm-4 col-form-label">
                {{ Func::getArrayName($array_user_id,$result->req_id) }}
                ( {{ Func::dateFormat( $result->req_date ) }} )
            </div>
        </div>
        @if($result->status !='A')
        <div class="form-group row mt-2">
            <label for="search_string" class="col-sm-2 col-form-label">@if( $result->status=="Y" )결재 일시 @else 취소 @endif</label>
            <div class="col-sm-4 col-form-label">
                {{ Func::getArrayName($array_user_id,$result->confirm_id) }}
                ( {{ Func::dateFormat( $result->confirm_date ) }} )
            </div>
        </div>
        @endif

    </div>
    <div class="card-footer">
        @if($result->status =='A')
            @if( Func::funcCheckPermit("C090") )
                <button type="button" class="btn btn-sm btn-info   float-right mr-1" id="btn_confirm" onclick="rebuyAction('CONFIRM');">결재</button>
                <button type="button" class="btn btn-sm btn-danger float-right mr-1" id="btn_delete"  onclick="rebuyAction('DELETE');" >취소</button>
            @endif
            @if( $result->req_id== Auth::id() )
                <button type="button" class="btn btn-sm btn-info float-right mr-1" id="btn_update"  onclick="rebuyAction('UPDATE');" >수정</button>
            @endif
        @endif
    </div>
    
</div>
</form>
@endsection
@section('javascript')
<script>

function rebuyAction(md)
{
    if( md=="CONFIRM" && !confirm("SMS발송을 진행하시겠습니까?") )
    {
        return false;
    }
    if( md=="DELETE" && !confirm("SMS발송을 취소하시겠습니까?") )
    {
        return false;
    }


    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var formData = new FormData($('#rebuy_form')[0]);
    formData.append("action_mode", md);


    if( md=="CONFIRM" )
    {
        $("#btn_confirm").prop("disabled",true);
        $("#btn_delete").prop("disabled",true);
    }
    

    $.ajax({
        url  : "/erp/smscheckaction",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            console.log(result);
            if( result.rs_code=="Y" )
            {
                alert(result.rs_msg);  
                opener.document.location.reload();
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
enterClear();
</script>

@endsection
