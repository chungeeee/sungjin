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


<form class="form-horizontal" role="form" name="form" id="form" method="post" action="/erp/loanguarantorstatusaction">
<div class="card card-lightblue">
    @csrf
<input type="hidden" id="mode" name="mode" value="{{ $mode ?? 'INS' }}">
<input type="hidden" id="no" name="no" value="{{ $v->no ?? '' }}">

    <div class="card-header">
        <h2 class="card-title" > 
            <i class="fa fa-sm fa-window-restore mr-2"></i> 보증인면탈신청
        </h2>
    </div>

    <div class="card-body pb-3">        

        <div class="form-group row collapse" id="collapseSearch">
            <label class="col-sm-1 col-form-label"></label>
            <div class="col-sm-11" id="collapseSearchResult"></div>
        </div>

        
        @if( !empty($simple) )
        <div class="row mt-3">
            <b>{{ $v->name ?? '' }} 보증인 면탈신청 (차입자번호 {{ $simple['cust_info_no'] }} / {{ $simple['name'] }} 고객)</b>
            @include('inc/loanSimpleLine')
        </div>
        @endif
    </div>

    <div class="card-body pb-3">     
        <table class="table -input text-xs card-secondary card-outline" id="conditionTable">
        <tr>
            <td class="text-left pl-2" width="15%">
                <label>보증인면탈 신청메모</label>
            </td>
            <td class="pl-2 pr-2" width="50%">
                <textarea class="form-control" rows="5" name="memo" placeholder="메모 ..." style="resize: none;">{{$condition->memo ?? ''}}</textarea>
            </td>

            <td class="align-bottom">
                
                <button type='button' class='btn btn-default btn-sm ml-1 mr-1 mb-1 float-left' onclick="loan_info_pop('{{ $simple['cust_info_no'] }}', '{{ $simple['no'] }}')">계약정보보기</button>
                <span id="button_area">
                <button type='button' class='btn btn-info btn-sm ml-1 mr-1 mb-1 float-right' onclick='sendit();'>결재요청 등록</button>
                </span>
            </td>
        </tr>
        </table>
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
        if(!$('#confirm_id_3').val())
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
            else
            {
                var col_str = '결재자';
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
        // @if( isset($condition) )
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
                    button += "<button type='button' class='btn btn-info btn-sm ml-1 mr-1 mb-1 float-right' onclick='sendit(\""+mode+"\", \""+status+"\");'>수정</button>";
                }
               
                if(status == "X")
                {
                    button += "<button type='button' class='btn btn-default btn-sm float-right ml-1 mb-1' onclick='window.close();''>닫기</button>";
                }

            @endif
        // @endif

        console.log(button);
        $('#button_area').append(button);
    }
</script>
@endsection