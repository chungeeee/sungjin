@extends('layouts.masterPop')
@section('content')

{{-- Action 처리 성공 여부 alert --}}
@if (Session::has('result'))
    <script> 
        alert('{{Session::get("result")}}'); 
        @if( Session::get("flag") == "Y" )
            if( typeof(opener.parent.listRefresh) == 'function' )
            {
                opener.parent.listRefresh();
            }
            window.close();
        @endif
    </script>
@endif

<style>
    .content::-webkit-scrollbar{
        width: 8px;
        height: 10px;
    }
    .content::-webkit-scrollbar-button {
        width: 8px;
    }
    .content::-webkit-scrollbar-thumb {
        background: #999;
        border: thin solid gray;
        border-radius: 10px;
    }
    .content::-webkit-scrollbar-track {
        background: #eee;
        border: thin solid lightgray;
        box-shadow: 0px 0px 3px #dfdfdf inset;
        border-radius: 10px;
    }

    .custom-control-input:checked ~ .custom-control-label::before {
        border-color : #17a2b8;
        background-color : #17a2b8;
    }
</style>


<form class="form-horizontal" role="form" name="form" id="form" method="post" action="/erp/conditionpopaction">
<div class="card card-lightblue">
    @csrf
<input type="hidden" id="mode" name="mode" value="{{ $mode ?? 'INS' }}">
<input type="hidden" id="no" name="no" value="{{ $condition->no ?? '' }}">
<input type="hidden" id="cust_info_no" name="cust_info_no" value="{{ $condition->cust_info_no ?? '' }}">
<input type="hidden" id="loan_info_no" name="loan_info_no" value="{{ $condition->loan_info_no ?? '' }}">
<input type="hidden" id="old_status" name="old_status" value="{{ $condition->status ?? '' }}">
<input type="hidden" name="confirm_level" id="confirm_level" value="{{ $confirm_level ?? 0 }}">

    <div class="card-header">
        <h2 class="card-title" > 
            <i class="fa fa-sm fa-window-restore mr-2"></i> 계약조건변경
        </h2>
    </div>

    <div class="card-body pb-3">

        <div class="form-group row">
            <label for="search_string" class="col-sm-1 col-form-label">검색</label>
            <div class="col-sm-4">
                <input type="text" class="form-control form-control-sm" id="search_string" placeholder="차입자번호,계약번호" value="" />
            </div>
            <div class="col-sm-7 text-left">
                <button type="button" class="btn btn-sm btn-info mr-3" onclick="searchLoanInfo();">검색</button>
            </div>
        </div>

        <div class="form-group row collapse" id="collapseSearch">
            <label class="col-sm-1 col-form-label"></label>
            <div class="col-sm-11" id="collapseSearchResult"></div>
        </div>

        @if( isset($condition) )
        @if( !empty($simple) )
        <div class="row mt-3">
            <b>고객번호 {{ $condition->cust_info_no }} / {{ $condition->name }} 고객 유효계약 리스트 (정상, 연체)</b>
            @include('inc/loanSimpleLine')
        </div>
        @endif

        <div class="row mt-1">
            <div class="col-sm-12 p-0 m-0">
                <b>조건변경 내용등록</b>
                <!-- BODY -->
                <table class="table table-bordered table-input text-xs card-secondary card-outline w-100" id="conditionTable">
                    <colgroup>
                        <col width="10%">
                        <col width="10%">
                        <col width="10%">
                        <col width="10%">
                        <col width="10%">
                        <col width="7%">
                        <col width="43%">
                    </colgroup>
                    <thead>
                        <tr>
                            <th class="p-1 text-center font-weight-bold">조건변경 선택</th>
                            <th class="p-1 text-center font-weight-bold" colspan=2>변경전</th>
                            <th class="p-1 text-center font-weight-bold" colspan=2>변경후</th>
                            <th class="p-1 text-center font-weight-bold">다계좌처리</th>
                            <th class="p-1 text-center font-weight-bold">비고</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr><td colspan=10></td></tr>
                        <tr>
                            <td>
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input chkBit" type="checkbox" id="chkRate" name="bit[]" value="R">
                                    <label for="chkRate" class="custom-control-label"></label>
                                    <label for="chkRate" class="mt-1">금리변경</label>
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="text" name="old_rate" class="form-control form-control-sm text-right floatnum" value="{{$condition->old_rate ?? '0.00'}}" readonly>
                                    <div class="input-group-append">
                                        <div class="input-group-text"><i class="fa fa-percent" style="font-size: 0.65rem;"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="input-group">
                                    <input type="text" name="old_delay_rate" class="form-control form-control-sm text-right floatnum" value="{{$condition->old_delay_rate ?? '0.00'}}" readonly>
                                    <div class="input-group-append">
                                        <div class="input-group-text"><i class="fa fa-percent" style="font-size: 0.65rem;"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td class="new_td">
                                <div class="input-group">
                                    <input @if(!empty($condition->status)) readonly @endif type="text" name="new_rate" onchange="setConId('chkRate');" class="form-control form-control-sm text-right floatnum" value="{{ $condition->new_rate ?? min(Vars::$curMaxRate,$condition->old_rate) }}" placeholder="정상" autocomplete="off" disabled>
                                    <div class="input-group-append">
                                        <div class="input-group-text"><i class="fa fa-percent" style="font-size: 0.65rem;"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td class="new_td">
                                <div class="input-group">
                                    <input @if(!empty($condition->status)) readonly @endif type="text" name="new_delay_rate" class="form-control form-control-sm text-right floatnum" value="{{ $condition->new_delay_rate ?? min(Vars::$curMaxRate,$condition->old_delay_rate) }}" placeholder="연체" autocomplete="off" disabled>
                                    <div class="input-group-append">
                                        <div class="input-group-text"><i class="fa fa-percent" style="font-size: 0.65rem;"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center new_td">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox">
                                        <input class="custom-control-input" type="checkbox" id="rate_all_chg_yn" name="all_chg_yn[]" value="rate" disabled>
                                        <label for="rate_all_chg_yn" class="custom-control-label"></label>
                                        <label for="rate_all_chg_yn" class="mt-1 mb-0">다계좌</label>
                                    </div>
                                </div>
                            </td>
                            <td class="text-left pl-2 bigo" id="bigo_rate">

                            </td>
                        </tr>
                        <tr><td colspan=10></td></tr>
                        <tr>
                            <td>
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input chkBit" type="checkbox" id="chkContractDay" name="bit[]" value="C" >
                                    <label for="chkContractDay" class="custom-control-label"></label>
                                    <label for="chkContractDay" class="mt-1">약정일변경</label>
                                </div>
                            </td>
                            <td colspan=2>
                                <input type="text" name="old_contract_day" class="form-control form-control-sm text-right" value="{{$condition->old_contract_day ?? ''}} 일" readonly>
                            </td>
                            <td colspan=2 class="new_td">
                                <select class="form-control form-control-sm pr-4" name="new_contract_day" style="text-align-last: right;" disabled>
                                    <option value=''>약정일</option>
                                    {{ Func::printOption($arr_contract_day, isset($condition->new_contract_day)? $condition->new_contract_day:"") }}
                                </select>
                            </td>
                            <td class="text-center new_td">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox ">
                                        <input class="custom-control-input" type="checkbox" id="cday_all_chg_yn" name="all_chg_yn[]" value="cday" disabled>
                                        <label for="cday_all_chg_yn" class="custom-control-label"></label>
                                        <label for="cday_all_chg_yn" class="mt-1 mb-0">다계좌</label>
                                    </div>
                                </div>
                            </td>
                            <td class="text-left pl-2 bigo" id="bigo_cday">

                            </td>
                        </tr>
                        <tr><td colspan=10></td></tr>



                        <!-- 월상환액은 원금균등, 원리금균등만 가능 -->
                        @if( isset($return_method_cd) && ( $return_method_cd=="B" || $return_method_cd=="R" || $return_method_cd=="F" ) )
                        <tr>
                            <td>
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input chkBit" type="checkbox" id="chkMonthlyReturnMoney" name="bit[]" value="M" >
                                    <label for="chkMonthlyReturnMoney" class="custom-control-label"></label>
                                    <label for="chkMonthlyReturnMoney" class="mt-1">월상환액변경</label>
                                </div>
                            </td>
                            <td colspan=2>
                                <div class="input-group">
                                    <input type="text" name="old_monthly_return_money" class="form-control form-control-sm text-right" value="{{$condition->old_monthly_return_money ?? ''}}" readonly>
                                    <div class="input-group-append">
                                        <div class="input-group-text" style="width:33px;padding:10px;"><i class="fa fa-won-sign" style="font-size: 0.65rem;"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td colspan=2 class="new_td">
                                <div class="input-group">
                                    <input type="text" class="form-control form-control-sm text-right moneyformat" name="new_monthly_return_money" placeholder="월상환액" autocomplete="off" value="{{$condition->new_monthly_return_money ?? '' }}" disabled>
                                    <div class="input-group-append">
                                        <div class="input-group-text" style="width:33px;padding:10px;"><i class="fa fa-won-sign" style="font-size: 0.65rem;"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center new_td">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox ">
                                        <input class="custom-control-input" type="checkbox" id="rmoney_all_chg_yn" name="all_chg_yn[]" value="rmoney" disabled>
                                        <label for="rmoney_all_chg_yn" class="custom-control-label"></label>
                                        <label for="rmoney_all_chg_yn" class="mt-1 mb-0">다계좌</label>
                                    </div>
                                </div>
                            </td>
                            <td class="text-left pl-2 bigo" id="bigo_rmoney">

                            </td>
                        </tr>
                        <tr><td colspan=10></td></tr>
                        @else
                        <input type="hidden" name="old_monthly_return_money" value="{{$condition->old_monthly_return_money ?? ''}}">
                        @endif

                        
                        @if( isset($return_method_cd) && ($return_method_cd=="F") )
                        <tr>
                            <td>
                                <div class="custom-control custom-checkbox">
                                    <input class="custom-control-input chkBit" type="checkbox" id="chkReturnDate" name="bit[]" value="D" >
                                    <label for="chkReturnDate" class="custom-control-label"></label>
                                    <label for="chkReturnDate" class="mt-1">상환일변경</label>
                                </div>
                            </td>
                            <td colspan=2>
                                <div class="input-group">
                                    <input type="text" name="old_return_date" class="form-control form-control-sm text-right" value="{{$condition->old_return_date ?? ''}}" readonly>
                                    <div class="input-group-append">
                                        <div class="input-group-text" style="width:33px;padding:10px;"><i class="fa fa-calendar" style="font-size: 0.65rem;"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td colspan=2 class="new_td">
                                <div class="input-group date datetimepicker">
                                    <input type="text" class="form-control form-control-sm text-right datetimepicker-input dateformat datetimepicker" name="new_return_date" id="new_return_date"
                                        placeholder="상환일" autocomplete="off" value="{{$condition->new_return_date ?? '' }}" disabled>
                                    <div class="input-group-append" data-target="#new_return_date" data-toggle="datetimepicker">
                                        <div class="input-group-text" style="width:33px;padding:10px;"><i class="fa fa-calendar" style="font-size: 0.65rem;"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td class="text-center new_td">
                                <div class="form-group">
                                    <div class="custom-control custom-checkbox ">
                                        <input class="custom-control-input" type="checkbox" id="rdate_all_chg_yn" name="all_chg_yn[]" value="rdate" disabled>
                                        <label for="rdate_all_chg_yn" class="custom-control-label"></label>
                                        <label for="rdate_all_chg_yn" class="mt-1 mb-0">다계좌</label>
                                    </div>
                                </div>
                            </td>
                            <td class="text-left pl-2 bigo" id="bigo_rdate">

                            </td>
                        </tr>
                        <tr><td colspan=10></td></tr>
                        @else
                        <input type="hidden" name="old_return_date" value="{{$condition->old_return_date ?? ''}}">
                        @endif

                        <tr>
                            <td class="text-left pl-4">
                                <label>조건변경메모</label>
                            </td>
                            <td class="pl-2 pr-2" colspan=5>
                                <textarea class="form-control" rows="3" name="memo" placeholder="메모 ..." style="resize: none;">{{$condition->memo ?? ''}}</textarea>
                            </td>

                            <td class="align-bottom">
                                    <div class="btn-group float-right ">
                                        <label class="mt-1 mr-2">반영기준일 : </label>
                                        <input type="text" class="form-control form-control-sm text-right datetimepicker-input dateformat datetimepicker col-md-3" name="basis_date" id="basis_date"
                                            placeholder="반영기준일" value="{{$basis_date ?? "" }}">
                                        <div class="input-group-append" data-target="#basis_date" data-toggle="datetimepicker">
                                            <div class="input-group-text ml-1"><i class="fa fa-calendar" style="font-size: 0.8rem;"></i></div>
                                        </div>
                                        <button type="button" class="btn btn-info btn-sm float-right ml-1 mb-1" onclick="planPreview();">결과 미리보기</button>
                                    </div>
                            </td>
                        </tr>
                    </tbody>
                    <tfoot>
                        <tr><td colspan=10></td></tr>
                    </tfoot>
                </table>
            </div>
        <!--<div class="col-md-4 content"  style="overflow-y:scroll; max-height:$('#conditionTable').height() + 25;">-->
        </div>

        <div class="card card-outline" id="planView">
        </div>

        <div class="mt-2 content" id="logDiv"  style='max-height:200px; overflow-y:auto'>

            <b>조건변경메모로그</b>

            <table class="table table-sm table-hover loan-info-table card-secondary card-outline">
                <colgroup>
                    <col width="15%">
                    <col width="25%">
                    <col width="60%">
                </colgroup>
                <thead>

                    <tr>
                        <th class="text-center">입력자</th>
                        <th class="text-center">입력시간</th>
                        <th class="text-center">내용</th>
                    </tr>

                </thead>
                <tbody>
                    
                    @forelse( $memos as $idx => $memo )

                        <tr>
                            <td class="text-center">{{$memo->save_id}}</td>
                            <td class="text-center">{{date("Y-m-d H:i:s", strtotime($memo->save_time))}}</td>
                            <td class="text-left">{!! str_replace("\r\n", "<BR>", $memo->memo) !!}</td>
                        </tr>
                        
                    @empty

                        <tr>
                            <td colspan="13" class='text-center p-4'>변경 이력이 없습니다.</td>
                        </tr>

                    @endforelse

                        <tr><td colspan=10></td></tr>

                </tbody>

            </table>

        </div>
        @endif
    </div>

    <div id="con_id_area" class="col-md-12 p-3">
        <table class="table table-sm card-secondary card-outline loan-info-table table-head-fixed">
            @if(isset($condition->option_str))
                <b>결재정보</b>
                <tbody>
                    @foreach($condition->option_str as $col => $option_str)
                    @php
                        if( !empty($condition->$col) && !empty($condition->{'confirm_date_'.substr($col,-1,1)})) // 나중에 권한이 빠지면 selectbox에 표기가 안되므로 ~date 나 ~time 이 있을떄는 수기로 추가해주자 
                        {
                            $option_str .= "<option value='".$condition->$col."' selected >".Func::getArrayName(Func::getUserId(),$condition->$col)."</option>";
                        }
                    @endphp
                    <tr class="">
                        <th class="w-10"> {{ $condition->confirm_str[$col] }}</th>
                        <td class="w-15">
                            <select class="form-control form-control-sm mr-2 con-id-sel" name="{{ $col }}" id="{{ $col }}" @if(!empty($condition->{'confirm_date_'.substr($col,-1,1)}) || $condition->status == "Y") disabled @endif onchange="setConfirmMemo(this.value,'{{ substr($col,-1,1) }}')">{!! $option_str !!}</select>
                        </td>
                        <th class="w-10"> {{ $condition->confirm_str[$col]." 의견" }}</th>
                        <td class=""><textarea class="form-control form-control-sm" rows="1" name="{{ 'confirm_memo_'.substr($col,-1,1) }}" @if($condition->{'confirm_id_'.substr($col,-1,1)} !=Auth::id()  || $condition->status  == "Y") readonly @endif>{{ $condition->{'confirm_memo_'.substr($col,-1,1)} ?? ''}}</textarea></td>
                    </tr>
                    @endforeach
                </tbody>
            @endif
        </table>
    </div>
            

    <div class="card-footer m-0" id="button_area">
        
    </div>
</div>

</form>


@endsection


@section('javascript')
<!-- INPUT MASK 설정 -->
<script>
    window.resizeTo(1216 ,window.screen.availHeight);

    $(".datetimepicker").datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
        useCurrent: false,
    });

    // 엔터막기
    $('input[type="text"]').keydown(function() {
        if (event.keyCode === 13)
        {
            event.preventDefault();

            if($(this).attr('id') == "search_string")
            {
                searchLoanInfo();
            }
        };
    });

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

    function selectLoanInfo(n)
    {
        var lin = $("#loan_info_no_"+n).html();

        location.href = "/erp/conditionpop?loan_info_no="+lin;
    }

    $(function () {

        setInputMask('class', 'dateformat', 'date');
        setInputMask('class', 'moneyformat', 'money');
        setInputMask('class', 'ratioformat', 'ratio');

        @if( isset($condition->condition_bit) )
            @if( strpos($condition->condition_bit, 'R')!==false )
                $('#chkRate').siblings('.custom-control-label').click();
                
                @if( isset($condition->rate_all_chg_yn) && $condition->rate_all_chg_yn == "Y" )
                    $('#rate_all_chg_yn').siblings('.custom-control-label').click();
                @endif

            @endif
            
            @if( strpos($condition->condition_bit, 'C')!==false )
                $('#chkContractDay').siblings('.custom-control-label').click();
                
                @if( isset($condition->cday_all_chg_yn) && $condition->cday_all_chg_yn == "Y" )
                    $('#cday_all_chg_yn').siblings('.custom-control-label').click();
                @endif
            @endif
            
            @if( strpos($condition->condition_bit, 'M')!==false )
                $('#chkMonthlyReturnMoney').siblings('.custom-control-label').click();
                
                @if( isset($condition->rmoney_all_chg_yn) && $condition->rmoney_all_chg_yn == "Y" )
                    $('#rmoney_all_chg_yn').siblings('.custom-control-label').click();
                @endif
            @endif
            
            @if( strpos($condition->condition_bit, 'D')!==false )
                $('#chkReturnDate').siblings('.custom-control-label').click();
                
                @if( isset($condition->rdate_all_chg_yn) && $condition->rdate_all_chg_yn == "Y" )
                    $('#rdate_all_chg_yn').siblings('.custom-control-label').click();
                @endif
            @endif
        @endif

        @if( isset($condition->no) )
            planPreview();
        @endif

    });

    $('.chkBit').on("change",function(){
        var id = this.id;

        @if( !empty($condition->status) )
            $('.chkBit').prop("disabled",true);
        @else
            setConId(id);
            var bit = $('#'+id).val();

            // 금리변경시 반영기준일 = 등록일+1일 나머지는 등록일로 세팅되도록 요청함 
            if(bit == "R")
            {
                $('#basis_date').val("{{ date('Ymd' , strtotime('+1 days')) }}");
            }
            else
            {
                $('#basis_date').val("{{ date('Ymd') }}");
            }
        @endif

        $('.chkBit').prop("checked",false);
        $('.new_td').find('input').prop('disabled', true);
        $('.new_td').find('select').prop('disabled', true);

        $('#'+id).prop("checked",true);
        $('#'+id).prop("disabled",false);
        $('#'+id).parent().parent().parent().find('.new_td').find('input').prop('disabled', false);
        $('#'+id).parent().parent().parent().find('.new_td').find('select').prop('disabled', false);
        

        //$item = $('#'+this.id);
        //if( this.checked )
        //{
        //    $id = this.id.replace("chk","div");
        //    console.log($id);
        //    id.parent().parent().parent().find('.new_td').find('input').prop('disabled', false);
        //    id.parent().parent().parent().find('.new_td').find('select').prop('disabled', false);
        //}
        //else
        //{
        //    $id = this.id.replace("chk","div");
        //    $item.parent().parent().parent().find('.new_td').find('input').prop('disabled', true);
        //    $item.parent().parent().parent().find('.new_td').find('select').prop('disabled', true);
        //}
    });

    // 결과 미리보기
    function planPreview()
    {
        var postArr = $('#form').serialize();

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#planView").html(loadingString);


        $.ajax({
            url  : "/erp/conditionplanPreview",
            type : "post",
            data : postArr,
            success : function(result) {

                $('.bigo').html('');

                if( typeof(result) == "object" )
                {
                    var mainDiv    = "<div class='text-center' style='height:200px; transform: translateY(40%);'><b>";
                    var mainDivEnd = "</b></div>";

                    var mainStr = "";

                    $.each(result, function(key, v){


                        var erStr   = "<span class='right badge badge-danger mr-1'>ER</span>";
                        var tdflag  = false;

                        if( key != "result" )
                        {
                            if( key == "main" )
                            {
                                $.each(v, function(div, val){
                                    
                                    if( div == "loan_info_no" )
                                    {
                                        mainStr += "("+val+") ";
                                    }
                                    else if( div == "str" )
                                    {
                                        mainStr += val;
                                    }

                                    mainStr += "<BR>";
                                    
                                });
                            }
                            else
                            {
                                $.each(v, function(div, val){
                                    
                                    if( div == "loan_info_no" )
                                    {
                                        erStr += "("+val+") ";
                                    }
                                    else if( div == "str" )
                                    {
                                        erStr += val;

                                        if( key != "main") tdflag=true;
                                    }
                                    
                                });
                            }
                        }
                        
                        if(tdflag)
                        {
                            $('#bigo_'+key).html(erStr);
                        }
                    });

                    var okStr = "<span class='right badge badge-info mr-1'>OK</span>정상 처리 가능";
                    if(!Object.keys(result).includes('main'))
                    {
                        $.each($(".chkBit").filter(":checked"), function(idx, item){
                            
                            if( item.value == "R" && !Object.keys(result).includes('rate') )      $("#bigo_rate").html(okStr);
                            else if(item.value == "C" && !Object.keys(result).includes('cday'))   $("#bigo_cday").html(okStr);
                            else if(item.value == "M" && !Object.keys(result).includes('rmoney')) $("#bigo_rmoney").html(okStr);
                            else if(item.value == "D" && !Object.keys(result).includes('rdate'))  $("#bigo_rdate").html(okStr);

                        });
                    }

                    if( mainStr == "" )
                    {
                        $("#planView").html(mainDiv + "※ 조건 변경 불가" + mainDivEnd);
                    }
                    else
                    {
                        $("#planView").html(mainDiv + mainStr + mainDivEnd);
                    }
                }
                else
                {
                    $("#planView").html(result);

                    var okStr = "<span class='right badge badge-info mr-1'>OK</span>정상 처리 가능";
                    var okStrCday = "<span class='right badge badge-info mr-1'>OK</span><font class='text-danger'>약정일은 각 계약의 이수일 기준으로 변경됩니다.</font>";

                    $.each($(".chkBit").filter(":checked"), function(idx, item){
                        if(item.value == "R")       $("#bigo_rate").html(okStr);
                        else if(item.value == "M")   $("#bigo_rmoney").html(okStr);
                        else if(item.value == "D")   $("#bigo_rdate").html(okStr);
                    });

                }
            },
            error : function(xhr) {
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });

    }

    function sendit(mode, status)
    {

        // 결재단계 변경. 하나의 조건변경씩 가능하게 해야함
        var i = $('input[name="bit[]"]:checked').length;
        if(i==0)
        {
            alert("조건변경을 선택해주세요");
            return false;
        }
        else if(i>1)
        {
            alert("조건변경을 하나만 선택해주세요");
            return false;
        }

        if( $('#chkRate').prop('checked') && (!form.new_rate.value || !form.new_delay_rate.value) )
        {
            alert('변경할 금리 값을 입력하세요.');
            form.new_rate.focus();
            return false;
        }
        if( $('#chkContractDay').prop('checked') && !form.new_contract_day.value )
        {
            alert('변경할 약정일을 선택하세요.');
            form.new_contract_day.focus();
            return false;
        }
        if( $('#chkMonthlyReturnMoney').prop('checked') && !form.new_monthly_return_money.value )
        {
            alert('변경할 월상환액을 입력하세요.');
            form.new_monthly_return_money.focus();
            return false;
        }
        if( $('#chkReturnDate').prop('checked') && !form.new_return_date.value )
        {
            alert('변경할 상환일을 입력하세요.');
            form.new_return_date.focus();
            return false;
        }

        /*
        if( $('input[name="bit[]"]:checked').val()=="R" && $('input[name="new_rate"]').val()<20 && $('#confirm_level').val() < 3)
        {
            alert("결재정보를 확인해주세요.");
            return false;
        }
        */
        if( status=='A' && !$('#confirm_id_1').val() && $('#confirm_level').val()>0)
        {
            alert("다음결재자를 지정해주세요.");
            return false;
        }
        if( status=='B'&& (!$('#confirm_id_1').val() || !$('#confirm_id_2').val()))
        {
            alert("결재자를 모두 지정해주세요.");
            return false;
        }
        if( status=='C'&& !$('#confirm_id_3').val())
        {
            alert("다음결재자를 지정해주세요.");
            return false;
        }

        if(status == "D")
        {
            if(!confirm("약정일 변경건을 취소하시겠습니까?") )
            {
                return false;
            }
        }
        else
        {
            if(!confirm("등록 하시겠습니까?") )
            {
                return false;
            }
        }

        

        if(mode)
        {
            form.mode.value = mode;
        }

        if(status)
        {
            form.status.value = status;
        }

        if(ccCheck()) return;

        form.submit();
    }

    // 조건변경별 결재단계가 달라지면서 다중 조건변경 사용불가. checkbox를 radio처럼 사용할수있도록 변경
    //function conditionCheck(id)
    //{
    //    $('.chkBit').prop("checked",false);
    //    $('#'+id).prop("checked",true);
    //    setConId(id);
    //}

    function setConId(id)
    {
        @if(isset($arr_confirm_id))
            var arrConId = @json($arr_confirm_id);
        @else
            return;
        @endif

        var bit = $('#'+id).val();

        if(bit == "R")
        {
            var new_rate       = $('input[name=new_rate]').val();

            if(new_rate>=20)
            {
                bit = "R1";
            }
            else
            {
                bit = "R2";
            }
        }

        var len = Object.keys(arrConId[bit]).length;
        var i = 0;
        var option = "";
        var tr_str = "";
        $('#con_id_area').empty('');
        $('#confirm_level').val(len-1);

        $.each(arrConId[bit], function (conLevel,obj) {
            var l = conLevel.substr(-1,1);
            i++;

            if(conLevel == "app_id") // 요청자 SELECTBOX 는 출력해줄 필요없을듯
            {
                return ;
            }
            
            if(conLevel == "confirm_id_1" && i != len)
            {
                var col_str = '1차결재자';
            }
            else if(conLevel == "confirm_id_2" && i != len)
            {
                var col_str = '2차결재자';
            }
            else
            {
                var col_str = '최종결재자';
            }

            var option = "<select class='form-control form-control-sm mr-2 con-id-sel' name='"+conLevel+"' id='"+conLevel+"' onchange='setConfirmMemo(this.value,"+l+");'>";
            option    += "<option value=''>"+col_str+"</option>";

            if(typeof obj != 'string')
            {
                $.each(obj, function (id, name) {
                    option += "<option value='"+id+"'>"+name+"</option>";
                });

            }
            option += "</select>";

            tr_str += "<tr class='col-md-12'><th class='col-md-1'>"+col_str+"</th><td class='col-md-1'>"+option+"</td>";
            tr_str += "<th class='col-md-1'>"+col_str+" 의견</th><td class='col-md-6'><textarea class='form-control form-control-sm' rows='1' readonly  name='confirm_memo_"+l+"'></textarea></td></tr>";
        });

        if(tr_str)
        {
            $("#con_id_area").append("<table class='table table-sm loan-info-table card-secondary card-outline'> <b>결재정보</b>"+tr_str+"</table>");
        }
    }   

    function setConfirmMemo(id,lv)
    {
        console.log(id,lv);
        if("{{ Auth::id() }}" == id)
        {
            $("textarea[name='confirm_memo_"+lv+"']").attr("readonly",false);
        }
        else
        {
            $("textarea[name='confirm_memo_"+lv+"']").attr("readonly",true);
        }
    }

    setConfirmButton();

    function setConfirmButton()
    {
        @if( isset($condition) )
            var button = "<input type='hidden' name='status' />";
            @if( empty($condition->status) )
                mode   = "INS";
                status = 'A';
                buttonName = "결재요청 등록";
                button += "<button type='button' class='btn btn-info btn-sm ml-1 mr-1 mb-1 float-right' onclick='sendit(\""+mode+"\", \""+status+"\");'>"+buttonName+"</button>";
            @else
                mode = "UPD";
                status = "{{ $condition->status }}";
                permit = "{{ Func::funcCheckPermit('C010') }}";
                confirmLv = "{{ $confirm_level ?? 0 }}";
                conditionBit = "{{ $condition->condition_bit ?? ''}}";

                if(status == "A" || status == "B" || status == "C")
                {
                    if(permit)
                    {
                        if(( status =="A" && confirmLv>1 ))
                        {
                            console.log("here");
                            newStatus = "B";
                            buttonName = '1차결재';
                        }
                        else if(( status =="B" && confirmLv>2 ))
                        {
                            newStatus = "C";
                            buttonName = '2차결재';
                        }
                        else
                        {
                            console.log("here???????????");

                            newStatus = "Y";
                            buttonName = '최종결재';
                        }
                        button += "<button type='button' class='btn btn-info btn-sm ml-1 mr-1 mb-1 float-right' onclick='sendit(\""+mode+"\", \""+newStatus+"\");'>"+buttonName+"</button>";
                        button += "<button type='button' class='btn btn-danger btn-sm ml-1 mr-1 mb-1 float-right' onclick='sendit(\""+mode+"\", \"X\")'>거절</button>";
                    }
                    button += "<button type='button' class='btn btn-info btn-sm ml-1 mr-1 mb-1 float-right' onclick='sendit(\""+mode+"\", \""+status+"\");'>수정</button>";
                }
                if(status == "Y" && permit && conditionBit == "C") // 약정일 변경시 취소가능
                {
                    button += "<button type='button' class='btn btn-danger btn-sm float-right ml-1 mb-1' onclick='sendit(\""+mode+"\", \"D\");'>취소</button>";
                }
                if(status == "X")
                {
                    button += "<button type='button' class='btn btn-default btn-sm float-right ml-1 mb-1' onclick='window.close();''>닫기</button>";
                }

            @endif
        @endif

        console.log(button);
        $('#button_area').append(button);
    }
</script>
@endsection