<!-- 투자내역 -->
<div class="col-md-12 p-0 m-0 " >
    <div class="card-header p-1" style="border-bottom:none !important;">
        <h6 class="card-title">수수료리스트</h6>
    </div>
    @include('inc/listSimple')
    <div id="investmentinfoInput" style='display:@if(isset($v->no)) block; @else none; @endif'>
        <form class="mb-0" name="form_ratioaction" id="form_ratioaction" method="post" enctype="multipart/form-data">
            <input type="hidden" name="loan_info_no" id="loan_info_no" value="{{ $result['customer']['loan_info_no'] ?? '' }}">
            <input type="hidden" name="cust_info_no" id="cust_info_no" value="{{ $result['customer']['cust_info_no'] ?? '' }}">
            <input type="hidden" name="rate_mode" id="rate_mode" value="">
            <div class="col-md-6">
                <div class="row">
                    @forelse($rates as $t_name => $r)
                    <div class="col-md-6">
                        <div class="card-header p-1" style="border-bottom:none !important;">
                            <h6 class="card-title">@if($t_name == 'loan_ratio') 수익률 @elseif($t_name == 'platform_fee_rate') 수수료율 @endif</h6>
                        </div>
                        <table class="table table-sm card-secondary card-outline table-hover mt-0">
                            <thead>
                                <tr>
                                    <th class="text-center">적용일</th>
                                    <th class="text-center">@if($t_name == 'loan_ratio') 수익률 @elseif($t_name == 'platform_fee_rate') 수수료율 @endif</th>
                                    <th class="text-center">삭제</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse( $r as $v )
                                @php if($t_name == 'loan_ratio') {
                                        $ratio = $v->ratio;
                                    } elseif($t_name == 'platform_fee_rate') {
                                        $ratio = $v->platform_fee_rate;
                                    }
                                @endphp
                                <tr>
                                    <td class="text-center">{{ Func::dateFormat($v->rate_date) }}</td>
                                    <td class="text-center">{{ $ratio }} %</td>
                                    <td class="text-center">
                                    <button class="btn btn-sm btn-danger py-0" type="button" onclick="rateAction('DEL','{{ $t_name }}','{{ $v->rate_date }}','{{ $v->save_time }}',$(this).val());" value="{{ $ratio }}">
                                            &times;
                                        </button>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="13" class='text-center p-4'>등록된 데이터가 없습니다.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @empty
                    @endforelse
                </div>
                <div class="row">
                    <div class="col-md-3 row">
                        <label class="col-md-6 text-center mt5">요율선택<b style='color: red; font-size:15px;'>*</b></label>
                        <select class="form-control selectpicker col-md-6" name="rate_type" id="rate_type">
                            <option value="loan_ratio">수익률</option>
                            <option value="platform_fee_rate">수수료율</option>
                        </select>
                    </div>
                    <div class="col-md-4 row">
                        <label class="col-md-4 text-center mt5">적용일<b style='color: red; font-size:15px;'>*</b></label>
                        <div class="input-group date mt-0 mb-0 datetimepicker col-md-7" id="rate_date" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#rate_date" id="rate_date_id" name="rate_date" DateOnly="true" onchange="checkDate(this)" value="{{ date('Y-m-d') }}" size="6">
                            <div class="input-group-append" data-target="#rate_date" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-3 row">
                        <label class="col-md-4 text-center mt5">요율<b style='color: red; font-size:15px;'>*</b></label>
                        <div class="col-md-6">
                            <input type="text" class="form-control form-control-sm" id="ratio_val" name="ratio_val" value="" placeholder="%" onkeyup="onlyRatio(this)">
                        </div>
                    </div>
                    <div class="col-md-2 row">
                        <button class="btn btn-sm btn-info" type="button" onclick="rateAction('INS');">저장</button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
getDataList('{{ $result['listName'] }}', '{{ $result['page'] ?? 1 }}', '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());

function rateAction(mode, rate_type, rate_date, rate_save_time, rate_val)
{
    if( mode == 'INS')
    {
        if(!$('#rate_date_id').val())
        {
            alert('적용일을 입력해주세요.');
            $('#rate_date_id').focus()
            return false;
        }
        if(!$('#ratio_val').val())
        {
            alert('요율을 입력해주세요.');
            $('#ratio_val').focus()
            return false;
        }
    }

    $("#rate_mode").val(mode);
    var mode_msg = '저장';
    var r_st  = "";
    var page   = "{{ $result['page'] ?? 1 }}";
    var lon_no = $("#loan_info_no").val();
    var cus_no = $("#cust_info_no").val();
    var r_mode = $("#rate_mode").val();
    var r_type = $("#rate_type").val();
    var r_date = $("#rate_date_id").val();
    var r_val  = $("#ratio_val").val();

    if(mode == 'DEL')
    {
        mode_msg = '삭제';
        r_type   = rate_type;
        r_date   = rate_date;
        r_val    = rate_val;
        r_st     = rate_save_time;
    }

    if(!confirm(mode_msg+'하시겠습니까?'))
    {
        return false;
    }

    if(ccCheck()) return;

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url  : '/account/investmentchgratioaction',
        data : {loan_info_no:lon_no, cust_info_no:cus_no, rate_mode:r_mode, rate_type:r_type, rate_date:r_date, save_time:r_st, ratio_val:r_val},
        type : 'POST',
        success : function(r){
            if(r.rslt == 'Y')
            {
                alert('정상 처리되었습니다.');
            }
            else
            {
                alert(r.msg);
            }
            getInvestmentData('investmentchgratio','',lon_no,'','','',page);
            globalCheck = false;
        },
        error : function(xhr){
            alert("통신오류입니다. 관리자에게 문의해주세요.");
            globalCheck = false;
        }
    });
}

function setReschedule(loan_info_no)
{
    if(!confirm("스케줄갱신하시겠습니까?")) return false;
    var page   = "{{ $result['page'] ?? 1 }}";
    var postdata = $('#investment_form').serialize();
    postdata = postdata + '&loan_info_no=' + loan_info_no;
    $.ajax({
        url  : "/account/investmentrescheduleaction",
        type : "post",
        data : postdata,
        success : function(data)
        {
            alert(data.result_msg);
            getInvestmentData('investmentchgratio','','','','','',page);
        }
    });
}
</script>