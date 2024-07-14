
<div class="col-md-12 row p-2">
    <div class="col-md-12">
    <b>통합정보</b>
    <table class="table table-sm card-secondary card-outline mt-1 table-bordered" id="shortTable">
    <col width="14%"/>
    <col width="14%"/>
    <col width="14%"/>
    <col width="14%"/>
    <col width="16%"/>     

    <tr>
        <th>구분</th>
        <th>원금</th>
        <th>이자</th>
        <th>가지급금</th>
        <th>합계</th>
    </tr>

    <tr>
        <th>최초 </th>
        <td>{{ number_format($firstTrade['loan_money']) }}</td>
        <td>0</td>
        <td>0</td>
        <td class="bold">{{ number_format($firstTrade['sum']) }}</td>
    </tr>
    <tr>
        <th>지급 </th>
        <td>{{ number_format($return_money['return_origin_sum']) }}</td>
        <td>{{ number_format($return_money['return_interest_sum']) }}</td>
        <td>0</td>
        <td class="bold">{{ number_format($return_money['sum']) }}</td>
    </tr>
    <tr>
        <th>현재 </th>
        <td>{{ number_format($v->balance) }}</td>
        <td>{{ number_format($v->sum_interest) }}</td>
        <td>{{ number_format($v->over_money) }}</td>
        <td class="bold">{{ number_format($v->balance + $v->sum_interest) }}</td>
    </tr>

    </table>
    </div>
</div>

            

<div class="col-md-12 row">
    <input type="hidden" id="loan_info_no" value="{{ $no }}">
    <div class="col-md-3">
        <b>계약기본정보 
            {{-- {{ $contract_cancel ?? '' }} --}}
            @php
                //print_r($v);
            @endphp
        
        </b>
        <table class="table table-sm table-hover loan-info-table card-secondary card-outline">
        <col width="35%"></col>
        <col width="65%"></col>
            <tr>
                <th class="text-center">채권번호</th>
                <td class="text-center">{{ $v->investor_type.$v->investor_no }}-{{ $v->inv_seq }}</td>
            </tr>
            <tr>
                <th class="text-center">투자자명</th>
                <td class="text-center">{{ $v->loan_usr_info_name }}</td>
            </tr>
            <tr>
                <th class="text-center">상태</th>
                <td class="text-center">
                    {!! Func::getInvStatus($v->status, true) !!}
                </td>
            </tr>
            <tr>
                <th class="text-center">상품구분</th>
                <td class="text-center">{{ $v->pro_cd }}</td>
            </tr>
            <tr>
                <th class="text-center">상환방법</th>
                <td class="text-center">{{ $v->return_method_nm}}</td>
            </tr>
            <tr>
                <th class="text-center">수익률</th>
                <td class="text-center">{{ number_format($v->loan_rate,2) }}%</td>
            </tr>
            {{-- <tr>
                <th class="text-center">조기상환<br>수수료율</th>
                <td class="text-center">{{ $v->return_fee_cd ?? "-" }}</td>
            </tr> --}}
            <tr>
                <th class="text-center">약정일</th>
                <td class="text-center">{{ $v->contract_day }}일</td>
            </tr>
            <!-- OPTION -->
            {{-- <tr>
                <th class="text-center">투자수수료</th>
                <td class="text-right">{{ number_format($v->handling_fee) ?? '' }}</td>
            </tr> --}}

        </table>
    </div>

    <div class="col-md-3">
        <b>회차/수익지급일 정보</b>
        <table class="table table-sm table-hover loan-info-table card-secondary card-outline">
        <col width="50%"></col>
        <col width="50%"></col>
            <tr>
                <th class="text-center">투자일</th>
                <td class="text-center">{{ Func::dateFormat($v->contract_date) }}</td>
            </tr>
            <tr>
                <th class="text-center">만기일</th>
                <td class="text-center">{{ Func::dateFormat($v->contract_end_date) }}</td>
            </tr>
            <tr>
                <th class="text-center">수익지급주기</th>
                <td class="text-center">{{ $v->loan_pay_term }}개월</td>
            </tr>
            <tr>
                <th class="text-center">총회차</th>
                <td class="text-center">{{ $v->loan_term }}회</td>
            </tr>
            <tr>
                <th class="text-center">최근거래일</th>
                <td class="text-center">{{ Func::dateFormat($v->take_date) }}</td>
            </tr>
            <tr>
                <th class="text-center">최근수익지급일</th>
                <td class="text-center">{{ Func::dateFormat($v->last_in_date) }}</td>
            </tr>
            <tr>
                <th class="text-center bg-secondary">차기수익지급일자</th>
                <td class="text-center {{ $v->return_date<date('Ymd') ? 'text-red' : '' }}">{{ Func::dateFormat($v->return_date) }}</td>
            </tr>
            <tr>
                <th class="text-center bg-secondary">차기수익지급액</th>
                <td class="text-right {{ $v->return_date<date('Ymd') ? 'text-red' : '' }}">{{ number_format($v->return_interest) }}</td>
            </tr>

        </table>
    </div>

    <div class="col-md-3">
        <b>원리금 정보</b>
        <table class="table table-sm table-hover loan-info-table card-secondary card-outline">
        <col width="50%"></col>
        <col width="50%"></col>
            <tr>
                <th class="text-center">투자액</th>
                <td class="text-right">{{ number_format($v->loan_money) }}</td>
            </tr>
            <tr>
                <th class="text-center">잔액</th>
                <td class="text-right">{{ number_format($v->balance) }}</td>
            </tr>
            {{-- <tr>
                <th class="text-center">비용</th>
                <td class="text-right">{{ number_format($v->cost_money) }}</td>
            </tr>
            <tr>
                <th class="text-center">미수이자</th>
                <td class="text-right">{{ number_format($v->misu_money) }}</td>
            </tr>
            <tr>
                <th class="text-center">이자부족금</th>
                <td class="text-right" role="button" data-container="body" data-toggle="popover" data-html="true" data-placement="right" data-content='정상이자부족 : {{ number_format($v->lack_interest) }}원<br>지연배상부족 : {{ number_format($v->lack_delay_money) }}원<br>연체이자부족 : {{ number_format($v->lack_delay_interest) }}원'>{{ number_format($v->lack_interest+$v->lack_delay_money+$v->lack_delay_interest) }}</td>
            </tr> --}}

        </table>

    </div>

    <div class="col-md-3">
        <b>기타 정보</b>
        <table class="table table-sm table-hover loan-info-table card-secondary card-outline">
        <col width="50%"></col>
        <col width="50%"></col>
            @if( $v->over_money>0 )
                <tr>
                    <th class="text-center">가지급금</th>
                    <td class="text-right">{{ number_format($v->over_money) }}</td>
                </tr>
            @endif

            <tr>
                <th class="text-center">지급예정 금액</th>
                <td class="text-right">{{ number_format($v->return_money) }}</td>
            </tr>
            <tr>
                <th class="text-center">총투자원금상환액</th>
                <td class="text-right">{{ number_format($return_money['return_origin_sum']) ?? '' }}</td>
            </tr>
            <tr>
                <th class="text-center">총수입지급액</th>
                <td class="text-right">{{ number_format($return_money['return_interest_sum']) ?? '' }}</td>
            </tr>

            @if( $v->status=="E" )
            <tr>
                <th class="text-center">완료일</th>
                <td class="text-center">{{ isset($v->fullpay_date) ? Func::dateFormat($v->fullpay_date) : "" }}</td>
            </tr>
            <tr>
                <th class="text-center">완료사유</th>
                <td class="text-center">{{ isset($v->fullpay_cd_nm) ? Func::dateFormat($v->fullpay_cd_nm) : "" }}</td>
            </tr>
            @endif
            {{-- @if( $v->status=="S" || $v->sanggak_date!="" )
            <tr>
                <th class="text-center">상각일</th>
                <td class="text-center">{{ isset($v->sanggak_date) ? Func::dateFormat($v->sanggak_date) : "" }}</td>
            </tr>
            <tr>
                <th class="text-center">상각사유</th>
                <td class="text-center">{{ isset($v->sg_reason_nm) ? $v->sg_reason_nm : "" }}</td>
            </tr>
            <tr>
                <th class="text-center">상각원금</th>
                <td class="text-right">{{ isset($v->sanggak_balance) ? number_format($v->sanggak_balance) : "" }}</td>
            </tr>
            <tr>
                <th class="text-center">상각이자합계</th>
                <td class="text-right">{{ isset($v->sanggak_interest) ? number_format($v->sanggak_interest) : "" }}</td>
            </tr>
                @if( $v->fullpay_date!="" && $v->sg_fullpay_cd!='')
                <tr>
                    <th class="text-center">대손완제사유</th>
                    <td class="text-center">{{ $v->sg_fullpay_cd }}</td>
                </tr>
                <tr>
                    <th class="text-center">대손완제일</th>
                    <td class="text-center">{{ $v->fullpay_date }}</td>
                </tr>
                @endif
            @endif
            @if( $v->status=="M" )
            <tr>
                <th class="text-center">매각일</th>
                <td class="text-center">{{ isset($v->sell_date) ? Func::dateFormat($v->sell_date) : "" }}</td>
            </tr>
            <tr>
                <th class="text-center">매각사</th>
                <td class="text-center">{{ $v->sell_corp ?? "" }}</td>
            </tr>
            <tr>
                <th class="text-center">매각사유</th>
                <td class="text-center">{{ $v->sell_reason_nm ?? "" }}</td>
            </tr>
            <tr>
                <th class="text-center">매각원금</th>
                <td class="text-right">{{ isset($v->sell_balance) ? number_format($v->sell_balance) : "" }}</td>
            </tr>
            <tr>
                <th class="text-center">매각이자합계</th>
                <td class="text-right">{{ isset($v->sell_interest) ? number_format($v->sell_interest) : "" }}</td>
            </tr>
            @endif


            @if( isset($v->dambo_set_fee) && Func::dateTerm($v->loan_date, date("Ymd"))<=29 )
            <tr>
                <th class="text-center text-xs">근저당권 설정비용</th>
                <td class="text-right" role="button" data-container="body" data-toggle="popover" data-html="true" data-placement="right" data-content='등록면허세 : {{ number_format($v->reg_liense_tax) }}원<br>지방교육세 : {{ number_format($v->loal_edu_tax) }}원'>
                    {{ isset($v->dambo_set_fee) ? number_format($v->dambo_set_fee) : "" }}
                </td>
            </tr>
            <tr>
                <th class="text-center text-xs">근저당권 회수대상</th>
                <td class="text-center text-xs">
                    <input type='checkbox' name='dambo_set_fee_target' id='dambo_set_fee_target' class='list-check pr-0' value='Y' {{ Func::echoChecked('Y',$v->dambo_set_fee_target ) }}>
                    <label style="vertical-align:middle;" class="form-check-label ml-1" for="dambo_set_fee_target">완납예정고객</label>
                </td>
            </tr>
            
            @endif --}}


        </table>
    </div>

</div>

<script>
    $(function(){
        // Enables popover
        $("[data-toggle=popover]").popover();
        $('input[name="dambo_set_fee_target"]').iCheck({
            checkboxClass: 'icheckbox_square-blue',
        });
    });

    function viewLostDateHistory(no)
    {
        var url = "/erp/loanlostdate?loan_info_no="+no;
        var wnd = window.open(url, "loanlostdate","width=900, height=800, scrollbars=yes");
        wnd.focus();
    }

    function interestReCal(md)
    {
        var no = $("#loan_info_no").val();
        var dambo_set_fee_target = $("#dambo_set_fee_target").is(":checked");
        if( dambo_set_fee_target )
        {
            var target_val = "Y";
        }
        else
        {
            var target_val = "N";
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.post("/erp/loaninfointerestcal", { no:no, mode:md, dambo_set_fee_target:target_val }, function(data) {
            getContData(no);
        });
    }

    $('input[name="dambo_set_fee_target"]').on('ifToggled', function(event){ interestReCal('UP'); });


    setInputMask('class', 'moneyformat', 'money');

    function viewInterestDetail( id )
    {
        $('#'+id).toggle();
    }

    function changeMonthlyReturnMoney(md)
    {
        var no = $("#loan_info_no").val();

        var md = "MONTHLY_RETURN";
        var new_monthly_return_gubun = $("#new_monthly_return_gubun").val();
        var new_monthly_return_money = $("#new_monthly_return_money").val();
        if( new_monthly_return_money!="" && new_monthly_return_money!="0" && new_monthly_return_gubun=="" )
        {
            alert("안내금액구분을 선택해주세요.");
            $("#new_monthly_return_gubun").focus();
            return false;
        }
        if( new_monthly_return_gubun!="" && ( new_monthly_return_money=="" || new_monthly_return_money=="0" ) )
        {
            alert("안내금액을 입력해주세요.");
            $("#new_monthly_return_money").focus();
            return false;
        }

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.post("/erp/loaninfointerestcal", { no:no, mode:md, monthly_return_gubun:new_monthly_return_gubun, monthly_return_money:new_monthly_return_money }, function(data) {
            if(data == "X")
            {
                alert("월상환액 변경 권한이 없습니다.");
            }
            else
            {
                getContData(no);
            }
        });
    }

</script>
