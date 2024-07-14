<div class="modal fade" id="divideOriginModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content" id="divideOriginModalContent">
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div class="modal fade" id="dividePlusModal">
    <div class="modal-dialog modal-lg">
      <div class="modal-content" id="dividePlusModalContent">
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<div class="modal fade" id="excelUploadModal">
    <div class="modal-dialog modal-sl">
      <div class="modal-content" id="excelUploadModalContent">
      </div>
      <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>

<!-- 투자내역 -->
<div class="col-md-12 p-0 m-0 " >
    <div class="card-header p-1" style="border-bottom:none !important;">
        <h6 class="card-title">투자리스트</h6>
        <div class="card-tools pr-2">
        </div>
    </div>
    @include('inc/listSimple')

    <br>
    <div class="card-body" id="investmentinfoInput">
    <form  class="mb-0" name="investment_form" id="investment_form" method="post" enctype="multipart/form-data">
    <input type="hidden" id="loan_info_no" name="loan_info_no" value="{{ $result['customer']['loan_info_no'] ?? '' }}">
    <input type="hidden" id="cust_info_no" name="cust_info_no" value="{{ $result['customer']['cust_info_no'] ?? '' }}">
    <input type="hidden" id="loan_usr_info_no" name="loan_usr_info_no" value="{{ $result['customer']['loan_usr_info_no'] ?? '' }}">
    <input type="hidden" id="platform_fee_rate" name="platform_fee_rate" value="0">
    <input type="hidden" id="pro_cd" name="pro_cd" value="{{ $v->pro_cd ?? '' }}">
    <input type="hidden" id="return_method_cd" name="return_method_cd" value="{{ $v->return_method_cd ?? '' }}">
    <input type="hidden" id="status" value="{{ $v->status ?? '' }}">
    <input type="hidden" id="loan_bank_status" name="loan_bank_status" value="{{ $v->loan_bank_status ?? 'N' }}">
    <input type="hidden" id="actMode" name="actMode">
        <div class="row">
            <div class="col-md-5">
                <div class="form-group row usr_collapse" id="collapseSearch">
                    <label class="col-sm-2 col-form-label"></label>
                    <div class="col-sm-10" id="usrCollapseSearchResult">
                    </div>
                </div>

                <div class="row" id="invest_input">
                    <div class="col-md-12">
                        <h3 class="card-title"><i class="fas fa-user m-2" size="9px"></i>투자 정보</h3>
                    </div>
                    <div class="card-body p-1">
                        <table class="table table-sm table-bordered table-input text-xs">
                            <colgroup>
                            <col width="17%"/>
                            <col width="33%"/>
                            <col width="15%"/>
                            <col width="35%"/>
                            </colgroup>
                            <tbody>
                            <tr height="34">
                                <th>채권번호</th>
                                <td>{{ $v->investor_type.$v->investor_no.'-'.$v->inv_seq}}</td>
                                <th>관계</th>
                                <td>{{ $v->relation ?? ''}}</td>
                            </tr>
                            <tr height="34">
                                <th>투자자명</th>
                                <td>{{ $v->name ?? ''}}</td>
                                <th>생년월일</th>
                                <td>{{ $v->ssn ?? ''}}</td>
                            </tr>
                            <tr height="34">
                                <th>상품명</th>
                                <td>{{ Func::getConfigArr('pro_cd')[$v->pro_cd] ?? ''}}</td>
                                <th>상환방식</th>
                                @if($v->pro_cd == '03')
                                    <td>
                                        <div class="col-sm-12 ml-lg-n2">
                                            <div class="row">
                                                <select class="form-control form-control-sm col-md-6 mt-1 ml-2" name="viewing_return_method" id="viewing_return_method">
                                                    <option value=''>상환방식</option>
                                                        {{ Func::printOption($configArr['viewing_return_method'], $v->viewing_return_method ?? '') }}
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                @else
                                    <td>{{ Func::getConfigArr('return_method_cd')[$v->return_method_cd] ?? ''}}</td>
                                @endif
                            </tr>
                            <tr height="34">
                                <th>전화번호</th>
                                <td>
                                    <div class="row mt-1">
                                        <input type="text" class="form-control form-control-sm col-md-3 ml-2" name="ph1" id="ph1" value="{{ $v->ph21 ?? '' }}" readonly>
                                        <input type="text" class="form-control form-control-sm col-md-3 ml-1" name="ph2" id="ph2" value="{{ $v->ph22 ?? '' }}" readonly>
                                        <input type="text" class="form-control form-control-sm col-md-3 ml-1" name="ph3" id="ph3" value="{{ $v->ph23 ?? '' }}" readonly>
                                    </div>
                                </td>
                                <th>지급적요</th>
                                <td>{{ $v->loan_bank_nick ?? ''}}</td>
                            </tr>
                            <tr height="34">
                                <th>주소</th>
                                <td>
                                    <div class="row mt-1">
                                        <input type="text" class="form-control form-control-sm col-md-4 mt-1 ml-2" id="zip1" name="zip1" value="{{ $v->zip ?? '' }}" readonly>
                                        <input type="text" class="form-control form-control-sm col-md-12 mt-1 ml-2" id="addr1" name="addr1" value="{{ $v->addr1 ?? '' }}" readonly>
                                        <input type="text" class="form-control form-control-sm col-md-12 mt-1 ml-2" id="addr2" name="addr2" value="{{ $v->addr2 ?? '' }}" readonly>
                                    </div>
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr height="34">
                                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>투자일자</th>
                                <td>
                                    @if($v->status == 'N')
                                        <div class="row">
                                            <div class="col-md-9 m-0 pr-0">
                                                <div class="input-group date datetimepicker" id="contract_date_div" data-target-input="nearest">
                                                    <input type="text" class="form-control form-control-sm dateformat" name="contract_date" id="contract_date" inputmode="text" value="{{ Func::dateFormat($v->contract_date) }}">
                                                    <div class="input-group-append" data-target="#contract_date_div" data-toggle="datetimepicker">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-sm-12 ml-lg-n2">
                                            <input type="text" class="form-control form-control-sm col-md-6 mt-1" id="contract_date" value="{{ $v->contract_date ?? '' }}" readonly>
                                        </div>
                                    @endif
                                </td>
                                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>만기일자</th>
                                <td colspan='4'>
                                    @if($v->status == 'N')
                                        <div class="row">
                                            <div class="col-md-9 m-0 pr-0">
                                                <div class="input-group date datetimepicker" id="contract_end_date_div" data-target-input="nearest">
                                                    <input type="text" class="form-control form-control-sm dateformat" name="contract_end_date" id="contract_end_date" inputmode="text" value="{{ Func::dateFormat($v->contract_end_date) }}">
                                                    <div class="input-group-append" data-target="#contract_end_date_div" data-toggle="datetimepicker">
                                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="col-sm-12 ml-lg-n2">
                                            <div class="row">
                                                @if($v->pro_cd != '03')
                                                    <input type="text" class="form-control form-control-sm col-md-5 mt-1 ml-2" id="contract_end_date" value="{{ $v->contract_end_date ?? '' }}" readonly>
                                                @else
                                                    <input type="text" class="form-control form-control-sm col-md-5 mt-1 ml-2" id="contract_end_date" name="contract_end_date" value="{{ $v->contract_end_date ?? '' }}">
                                                @endif
                                                @if($v->pro_cd != '03' && $v->status == 'A')
                                                    <button type="button" class="btn btn-sm btn-info float-right mr-4 ml-1" id="contract_end_plus" onclick="endPlus('{{$result['customer']['loan_info_no']}}');">만기갱신</button>
                                                @endif
                                            </div>
                                        </div>
                                    @endif
                                </td>
                            </tr>
                            <tr height="34">
                                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>이자지급일(일)</th>
                                <td>
                                    @if($v->status == 'N')
                                        <input type="text" class="form-control form-control-sm col-md-3 text-right" id="contract_day" name="contract_day" placeholder="일" value="{{ $v->contract_day }}">
                                    @else
                                        <input type="text" class="form-control form-control-sm col-md-3 text-right" id="contract_day" value="{{ $v->contract_day }}" readonly>
                                    @endif
                                </td>
                                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>상환주기(월)</th>
                                <td>
                                    @if($v->status == 'N')
                                        <input type="text" class="form-control form-control-sm col-md-3 text-right" id="pay_term" name="pay_term" placeholder="월" value="{{ $v->pay_term }}">
                                    @else
                                        <input type="text" class="form-control form-control-sm col-md-3 text-right" id="pay_term" value="{{ $v->pay_term }}" readonly>
                                    @endif
                                </td>
                            </tr>
                            <tr height="34">
                                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>투자금액</th>
                                <td colspan='4'>
                                    <div class="col-sm-12 ml-lg-n2">
                                        <div class="row">
                                            @if($v->status == 'N')
                                                <input type="text" class="form-control form-control-sm col-md-3 mt-1 ml-2 text-right moneyformat" id="loan_money" name="loan_money" placeholder="원단위 입력" onkeyup="setInput(0);" value="{{ number_format($v->loan_money ?? 0) }}">
                                            @else
                                                <input type="text" class="form-control form-control-sm col-md-3 mt-1 ml-2" id="loan_money" value="{{ number_format($v->loan_money ?? 0) }}" readonly>
                                            @endif

                                            @if($v->pro_cd != '03' && $v->status == 'A')
                                                <button type="button" class="btn btn-sm btn-info float-right mr-4 ml-1" id="divide_origin" onclick="divideOriginForm('{{$result['customer']['loan_info_no']}}');">원금조정</button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            <tr height="34">
                                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>이자율(%)</th>
                                <td>
                                    @if($v->status == 'N')
                                        <input type="text" class="form-control form-control-sm col-md-6 text-right floatnum" id="invest_rate" name="invest_rate" onkeyup="setInput(0);" value="{{ $v->invest_rate }}">
                                    @else
                                        <input type="text" class="form-control form-control-sm col-md-6 text-right floatnum" id="invest_rate" value="{{ $v->invest_rate }}" readonly>
                                    @endif
                                </td>
                                <td></td>
                                <td></td>
                            </tr>
                            <tr height="34">
                                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>소득세율(%)</th>
                                <td>
                                    @if($v->status == 'N')
                                        <input type="text" class="form-control form-control-sm col-md-6 text-right floatnum" id="income_rate" name="income_rate" onkeyup="setInput(0);" value="{{ $v->income_rate }}">
                                    @else
                                        <input type="text" class="form-control form-control-sm col-md-6 text-right floatnum" id="income_rate" value="{{ $v->income_rate }}" readonly>
                                    @endif
                                </td>
                                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>지방소득세율(%)</th>
                                <td>
                                    @if($v->status == 'N')
                                        <input type="text" class="form-control form-control-sm col-md-6 text-right floatnum" id="local_rate" name="local_rate" onkeyup="setInput(0);" value="{{ $v->local_rate }}">
                                    @else
                                        <input type="text" class="form-control form-control-sm col-md-6 text-right floatnum" id="local_rate" value="{{ $v->local_rate }}" readonly>
                                    @endif
                                </td>
                            </tr>
                            <tr>
                                <th><span class="text-danger font-weight-bold h6 mr-1">*</span>은행</th>
                                <td colspan='4'>
                                    <div class="col-sm-12 ml-lg-n2">
                                        <div class="row">
                                            <select class="form-control form-control-sm col-md-3 mt-1 ml-2" name="loan_bank_cd" id="loan_bank_cd" onchange="changeLoanBank()">
                                                <option value=''>투자자은행</option>
                                                    {{ Func::printOption($configArr['bank_cd'], $v->loan_bank_cd ?? '') }}
                                            </select>
                                            <input type="text" class="form-control form-control-sm col-md-3 mt-1 ml-1" name="loan_bank_ssn" id="loan_bank_ssn" value="{{ $v->loan_bank_ssn ?? '' }}" onkeyup="changeLoanBank()">
                                            <input type="text" class="form-control form-control-sm col-md-3 mt-1 ml-2" name="loan_bank_name" id="loan_bank_name" value="{{ $v->loan_bank_name ?? '' }}" onkeyup="changeLoanBank()" readonly>
                                            
                                            @if($v->status != 'E')
                                                <button type="button" class="btn btn-sm btn-danger float-right mr-4 ml-1" id="loan_bank_btn" onclick="bankCheck('UPD', '{{$result['customer']['loan_info_no']}}');" @if(isset($v->loan_bank_status) && $v->loan_bank_status == 'Y') disabled @endif>계좌실명조회</button>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="col-md-12">
                        <button type="button" class="btn btn-sm btn-success float-right mr-1" id="loan_confirm" onclick="loan_info_pop('{{$result['customer']['cust_info_no']}}', '{{$result['customer']['loan_info_no']}}');">계약정보창</button>
                        <button type="button" class="btn btn-sm btn-secondary float-right mr-2" id="investor_confirm" onclick="popUpFull('/account/investorpop?no={{$v->loan_usr_info_no}}', 'investor{{$v->loan_usr_info_no}}');">투자자정보창</button>
                        <button type="button" class="btn btn-sm btn-secondary float-right mr-1" id="customer_confirm" onclick="popUpFull('/erp/customerpop?cust_info_no={{$v->cust_info_no}}', 'cust_info{{$v->cust_info_no}}');">차입자정보창</button>
                    </div>

                    <br/><br/>

                    <div class="col-md-12" >
                        <h3 class="card-title"><i class="fas fa-user m-2" size="9px"></i>메모</h3>
                    </div>
                    <div class="card-body p-1">
                        <table class="table table-sm table-bordered table-input text-xs" id='memo_title'>
                            <colgroup>
                            <col width="17%"/>
                            <col width="83%"/>
                            </colgroup>
                            <tbody>
                                <input type="hidden" name="mode" value="" >
                                <input type="hidden" name="no" value="" >
                                <tr>
                                    <th class="text-center bold">메&nbsp;&nbsp;&nbsp;&nbsp;모</th>
                                    <td>
                                        <textarea class="form-control form-control-xs" name="loan_memo" id="loan_memo" placeholder=" 메모입력...." rows="4" style="resize:none;">{{ $v->loan_memo ?? '' }}</textarea>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <div class="pt-1 pb-1" id="input_footer">
                            @if($v->pro_cd != '03' && $v->status == 'N')
                                <button type="button" class="btn btn-sm btn-info float-right mr-3" id="insert_invest" onclick="confirmInvest('INS', 'A');">투자등록</button>
                                <button type="button" class="btn btn-sm btn-info float-right mr-3" id="review_schedule" onclick="reviewSchedule();">스케줄미리보기</button>
                            @elseif($v->pro_cd != '03')
                                <button type="button" class="btn btn-sm btn-info float-right" id="memo_confirm" onclick="confirmInvest('UPD' ,'A');">수정</button>
                            @endif

                            @if($v->pro_cd == '03' && $v->status == 'N')
                                <button type="button" class="btn btn-sm btn-info float-right mr-3" id="insert_invest" onclick="confirmInvest('INS', 'B');">투자등록</button>
                            @elseif($v->pro_cd == '03')
                                <button type="button" class="btn btn-sm btn-info float-right" id="memo_confirm" onclick="confirmInvest('UPD', 'B');">수정</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-7">
                <div class="row">
                    <div class="col-md-12">
                        @if($v->status == 'N' && $v->pro_cd == '03')
                            <button type="button" class="btn btn-xs btn-success float-right mb-1 mr-1" onclick="investmentScheduleExcel('{{$result['customer']['loan_info_no']}}');">스케줄 업로드</button>
                        @endif
                        <table class="table table-sm card-secondary card-outline table-hover mt-0">
                        <colgroup>
                            <col width="4%">
                            @if( $v->pro_cd == "03" )
                                <col width="7%">
                                <col width="6%">
                                <col width="8%">
                            @else
                                <col width="10%">
                                <col width="10%">
                            @endif
                            @if( $v->return_method_cd == "F" && $v->pro_cd != "03" )
                                <col width="8.5%">
                            @endif
                            <col width="8%">
                            <col width="8%">
                            <col width="7%">
                            <col width="7%">
                            <col width="7%">
                            <col width="7%">
                            <col width="6%">
                            <col width="6%">
                        <colgroup>
                        <thead>
                            <tr>
                                <th class="text-center">회차</th>
                                @if( $v->pro_cd != "03" )
                                    <th class="text-center">수익지급일</th>
                                    <th class="text-center">수익지급일<br>(영업)</th>
                                @else
                                    <th class="text-center" colspan="2">수익지급일</th>
                                    <th class="text-center">수익지급일<br>(영업)</th>
                                @endif
                                @if( $v->return_method_cd == "F" && $v->pro_cd != "03" )
                                    <th class="text-center">이자구간</th>
                                @endif
                                <th class="text-center">투자잔액</th>
                                <th class="text-center">상환원금</th>
                                <th class="text-center">상환이자</th>
                                <th class="text-center">원천징수</th>
                                <th class="text-center">소득세</th>
                                <th class="text-center">지방소득세</th>
                                <th class="text-center">실지급액</th>
                                <th class="text-center">상태</th>
                            </tr>
                        </thead>
                        <tbody id="inputTbody">
                            @php ( $scheduleCnt = $sum_money_return = $sum_origin_return = $sum_plan_origin = $sum_plan_interest = $sum_withholding_tax = $sum_income_tax = $sum_local_tax = $sum_interest_return = $sum_withholding_return = $sum_income_return = $sum_local_return = $sum_plan_money = 0 )
                            @php ( $save_time = "" )
                            @forelse( $plans as $val )
                                @if( $val->divide_flag == "Y" )
                                    @php ( $sum_origin_return += $val->plan_origin )   
                                    @php ( $sum_interest_return += $val->plan_interest )
                                    @php ( $sum_withholding_return += $val->withholding_tax )
                                    @php ( $sum_income_return += $val->income_tax )
                                    @php ( $sum_local_return += $val->local_tax )
                                    @php ( $sum_money_return += $val->plan_money )
                                @endif
                                
                                <tr>
                                    <td class="text-center {{ $val->divide_flag=='Y' ? 'bg-secondary' : '' }}"><input type="hidden" name="divide_flag[]" value="{{ $val->divide_flag }}">{{ number_format($val->seq) }}</td>
                                    @if($v->pro_cd != "03")
                                        <td class="text-center {{ isset($holiday[$val->plan_date]) ? 'text-red' : '' }}"><input type="hidden" name="plan_date[]" value="{{ Func::dateFormat($val->plan_date) }}">{{ Func::dateFormat($val->plan_date) }} ({{ Vars::$arrayWeekDay[date('w',Func::dateToUnixtime($val->plan_date))] }})</td>
                                    @elseif($val->divide_flag == "Y")
                                        <td class="text-center {{ isset($holiday[$val->plan_date]) ? 'text-red' : '' }}" colspan="2"><input type="hidden" name="plan_date[]" value="{{ Func::dateFormat($val->plan_date) }}">{{ Func::dateFormat($val->plan_date) }} ({{ Vars::$arrayWeekDay[date('w',Func::dateToUnixtime($val->plan_date))] }})</td>
                                    @else
                                        <td class="text-center" colspan="2">
                                            <div class="row">
                                                <div class="col-md-10 m-0 pr-0">
                                                    <div class="input-group date datetimepicker" id="plan_date_div{{$scheduleCnt}}" data-target-input="nearest">
                                                        <input type="text" class="form-control form-control-sm dateformat" name="plan_date[]" id="plan_date[]" inputmode="text" value="{{ Func::dateFormat($val->plan_date) }}">
                                                        <div class="input-group-append" data-target="#plan_date_div{{$scheduleCnt}}" data-toggle="datetimepicker">
                                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    @endif

                                    <td class="text-center">{{ Func::dateFormat($val->plan_date_biz) }}({{ Vars::$arrayWeekDay[date('w',Func::dateToUnixtime($val->plan_date_biz))] }})</td>

                                    @if( $v->return_method_cd == "F" && $v->pro_cd != "03" )
                                        <td class="text-center">{{ Func::dateFormat($val->plan_interest_sdate) }} ~<br> {{ Func::dateFormat($val->plan_interest_edate) }}</td>
                                    @endif

                                    <td class="text-right" id="td_plan_balance{{ $val->seq }}">
                                        <input type="hidden" id="plan_balance{{ $val->seq }}" name="plan_balance[]" value="{{ $val->plan_balance }}">
                                        {{ number_format($val->plan_balance) }}
                                    </td>

                                    @if( $v->pro_cd == "03" && $val->divide_flag != "Y")
                                        <td class="text-right"><input type="text" class="form-control form-control-sm text-right moneyformat" id="plan_origin{{ $val->seq }}" name="plan_origin[]" placeholder="원단위 입력" onkeyup="setInput({{ $val->seq }});" value="{{ number_format($val->plan_origin) }}"></td>
                                        <td class="text-right"><input type="text" class="form-control form-control-sm text-right moneyformat" id="plan_interest{{ $val->seq }}" name="plan_interest[]" placeholder="원단위 입력" onkeyup="setInput({{ $val->seq }});" value="{{ number_format($val->plan_interest) }}"></td>
                                    @else
                                        <td class="text-right">
                                            <input type="hidden" id="plan_origin{{ $val->seq }}" name="plan_origin[]" value="{{ $val->plan_origin }}">
                                            {{ number_format($val->plan_origin) }}
                                        </td>
                                        <td class="text-right">
                                            <input type="hidden" id="plan_interest{{ $val->seq }}" name="plan_interest[]" value="{{ $val->plan_interest }}">
                                            {{ number_format($val->plan_interest) }}
                                        </td>
                                    @endif

                                    <td class="text-right" id="td_withholding_tax{{ $val->seq }}">
                                        <input type="hidden" id="withholding_tax{{ $val->seq }}" name="withholding_tax[]" value="{{ $val->withholding_tax }}">
                                        {{ number_format($val->withholding_tax) }}
                                    </td>
                                    <td class="text-right" id="td_income_tax{{ $val->seq }}">
                                        <input type="hidden" id="income_tax{{ $val->seq }}" name="income_tax[]" value="{{ $val->income_tax }}">
                                        {{ number_format($val->income_tax) }}
                                    </td>
                                    <td class="text-right" id="td_local_tax{{ $val->seq }}">
                                        <input type="hidden" id="local_tax{{ $val->seq }}" name="local_tax[]" value="{{ $val->local_tax }}">
                                        {{ number_format($val->local_tax) }}
                                    </td>
                                    <td class="text-right" id="td_plan_money{{ $val->seq }}">
                                        <input type="hidden" id="plan_money{{ $val->seq }}" name="plan_money[]" value="{{ $val->plan_money }}">
                                        {{number_format($val->plan_money)}}
                                    </td>

                                    @if( $v->pro_cd == "03" && $val->divide_flag != "Y")
                                        <td class="text-center">
                                            <div class="row">
                                                <div class="col-sm-5 m-0 pr-0">
                                                    <button type="button" class="btn btn-default btn-sm float-center mr-2 addbtn" onclick="addRow(this);"><i class="fa fa-xs fa-plus-square text-info"></i></button>
                                                </div>
                                                <div class="col-sm-5 m-0 pr-0">
                                                    <button type="button" class="btn btn-default btn-sm float-center mr-2 delbtn" onclick="delRow(this);"><i class="fa fa-xs fa-minus-square text-danger"></i></button>
                                                </div>
                                            </div>
                                        </td>
                                    @else
                                        <td class="text-center">{{ isset($val->divide_flag) ? Vars::$arraySendYn[$val->divide_flag] : ''}}</td>
                                    @endif
                                </tr>
                            
                                @php ( $sum_plan_origin+= $val->plan_origin )
                                @php ( $sum_plan_interest+= $val->plan_interest )
                                @php ( $sum_withholding_tax+= $val->withholding_tax )
                                @php ( $sum_income_tax+= $val->income_tax )
                                @php ( $sum_local_tax+= $val->local_tax )
                                @php ( $sum_plan_money += $val->plan_money )
                                @php ( $save_time = $val->save_time )
                                @php ( $scheduleCnt++ )
                                @empty
                                <tr>
                                    <td colspan="15" class='text-center p-4'>등록된 상환스케줄이 없습니다.</td>
                                </tr>
                            @endforelse

                            @if( $plans )
                            <tr class="bg-secondary">
                                <td class="text-center" id="td_sum"></td>
                                @if( $v->return_method_cd == "F" || $v->pro_cd == "03" )
                                    <td class="text-center" colspan="4" >합계 [ 최종갱신 : {{ Func::dateFormat($v->save_time) }} ]</td>
                                @else
                                    <td class="text-center" colspan="3" >합계 [ 최종갱신 : {{ Func::dateFormat($v->save_time) }} ]</td>
                                @endif
                                <td class="text-right" id="td_tot_plan_origin">{{ number_format($sum_plan_origin) }}</td>
                                <td class="text-right" id="td_tot_plan_interest">{{ number_format($sum_plan_interest) }}</td>
                                <td class="text-right" id="td_tot_withholding_tax">{{ number_format($sum_withholding_tax) }}</td>
                                <td class="text-right" id="td_tot_income_tax">{{ number_format($sum_income_tax) }}</td>
                                <td class="text-right" id="td_tot_local_tax">{{ number_format($sum_local_tax) }}</td>
                                <td class="text-right" id="td_tot_plan_money">{{ number_format($sum_plan_money) }}</td>
                                <td class="text-center"></td>
                            </tr>
                            <tr class="bg-secondary">

                                <td class="text-center" id="td_money_sum"></td>
                                @if( $v->return_method_cd == "F" || $v->pro_cd == "03" )
                                    <td class="text-center" colspan="4">수익지급 합계</td>
                                @else
                                    <td class="text-center" colspan="3">수익지급 합계</td>
                                @endif
                                <td class="text-right">{{ number_format($sum_origin_return) }}</td>
                                <td class="text-right">{{ number_format($sum_interest_return) }}</td>
                                <td class="text-right">{{ number_format($sum_withholding_return) }}</td>
                                <td class="text-right">{{ number_format($sum_income_return) }}</td>
                                <td class="text-right">{{ number_format($sum_local_return) }}</td>
                                <td class="text-right">{{ number_format($sum_money_return) }}</td>
                                <td class="text-center"></td>
                            </tr>
                            @endif

                        </tbody>
                        </table>
                    </div>
                </div>
            </div>            
        </div>
    </form>
    </div>
</div>

<script>
$('.datetimepicker').datetimepicker({
    format: 'YYYY-MM-DD',
    locale: 'ko',
    useCurrent: false,
});
setInputMask('class', 'moneyformat', 'money');
getDataList('{{ $result['listName'] }}', '{{ $result['page'] ?? 1 }}', '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());

var scheduleCnt = 0;

// 원금조정 modal show 동작
function divideOriginForm(loan_info_no)
{
    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#divideOriginModal").modal('show');
	$("#divideOriginModalContent").html(loadingString);
	$.post("/account/divideoriginform", { loan_info_no: loan_info_no }, function (data) {
		$("#divideOriginModalContent").html(data);
	});
}

// 만기갱신
function endPlus(loan_info_no)
{
    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#dividePlusModal").modal('show');
	$("#dividePlusModalContent").html(loadingString);
	$.post("/account/divideplusform", { loan_info_no: loan_info_no }, function (data) {
		$("#dividePlusModalContent").html(data);
	});
}

// 기관차입 엑셀업로드
function investmentScheduleExcel(loan_info_no)
{
    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#excelUploadModal").modal('show');
	$("#excelUploadModalContent").html(loadingString);
	$.post("/account/exceluploadform", { loan_info_no: loan_info_no }, function (data) {
		$("#excelUploadModalContent").html(data);
	});
}

function setInput(cnt)
{
    if(cnt>0)
    {
        var get_targetOriginMoney = $("#inputTbody input[name^='plan_origin[]']");
        var get_targetInterestMoney = $("#inputTbody input[name^='plan_interest[]']");

        //세금 적용
        var invest_rate = $('#invest_rate').val();      //이자율
        var income_rate = $('#income_rate').val();     //소득세율
        var local_rate = $('#local_rate').val();      //지방소득세율

        //소득세
        var plan_interest = Number(get_targetInterestMoney.eq(cnt-1).val().replace(/,/gi,""));
        var income_tax = Math.floor(plan_interest * (income_rate / 100) / 10) * 10;
        $('#income_tax' + cnt).val(income_tax);
        var hiddenValue = $('#td_income_tax' + cnt + ' input[type="hidden"]').val(income_tax).clone(); 
        $('#td_income_tax' + cnt).html(income_tax.toLocaleString()).append(hiddenValue); 

        //지방소득세
        var local_tax = Math.floor(income_tax * (local_rate / 100) / 10) * 10;
        $('#local_tax' + cnt).val(local_tax);
        var hiddenValue = $('#td_local_tax' + cnt + ' input[type="hidden"]').val(local_tax).clone(); 
        $('#td_local_tax' + cnt).html(local_tax.toLocaleString()).append(hiddenValue);
        
        //원천징수
        var withholding_tax = income_tax + local_tax;
        $('#withholding_tax' + cnt).val(withholding_tax);
        var hiddenValue = $('#td_withholding_tax' + cnt + ' input[type="hidden"]').val(withholding_tax).clone(); 
        $('#td_withholding_tax' + cnt).html(withholding_tax.toLocaleString()).append(hiddenValue);

        //실지급액 조정    
        var plan_interest = Number(get_targetInterestMoney.eq(cnt-1).val().replace(/,/gi,""));
        var plan_origin = Number(get_targetOriginMoney.eq(cnt-1).val().replace(/,/gi,""));
        var plan_money = plan_origin + plan_interest - withholding_tax;

        $('#plan_money' + cnt).val(plan_money);
        var hiddenValue = $('#td_plan_money' + cnt + ' input[type="hidden"]').val(plan_money).clone(); 
        $('#td_plan_money' + cnt).html(plan_money.toLocaleString()).append(hiddenValue); 
    }

    var cal_plan_origin = cal_plan_interest = cal_withholding_tax = cal_income_tax = cal_local_tax = cal_plan_money = 0;
    
    // 원금
    var get_targetMoney = $("#inputTbody input[name^='plan_origin[]']");
    $.each(get_targetMoney, function(index, value){
        cal_plan_origin+=Number($(value).val().replace(/,/gi,""));
    });

    // 이자
    var get_targetMoney = $("#inputTbody input[name^='plan_interest[]']");
    $.each(get_targetMoney, function(index, value){
        cal_plan_interest+=Number($(value).val().replace(/,/gi,""));
    });

    // 원천징수
    var get_targetMoney = $("#inputTbody input[name^='withholding_tax[]']");
    $.each(get_targetMoney, function(index, value){
        cal_withholding_tax+=Number($(value).val().replace(/,/gi,""));
    });
    
    // 이자소득세
    var get_targetMoney = $("#inputTbody input[name^='income_tax[]']");
    $.each(get_targetMoney, function(index, value){
        cal_income_tax+=Number($(value).val().replace(/,/gi,""));
    });
    
    // 주민세
    var get_targetMoney = $("#inputTbody input[name^='local_tax[]']");
    $.each(get_targetMoney, function(index, value){
        cal_local_tax+=Number($(value).val().replace(/,/gi,""));
    });
    
    // 실지급액
    var get_targetMoney = $("#inputTbody input[name^='plan_money[]']");
    $.each(get_targetMoney, function(index, value){
        cal_plan_money+=Number($(value).val().replace(/,/gi,""));
    });

    //투자잔액 조정
    var loan_money = Number($('#loan_money').val().replace(/,/gi,""));
    var get_targetBalanceMoney = $("#inputTbody input[name^='plan_balance[]']");
    
    $.each(get_targetBalanceMoney, function(index, value) {
        var plan_origin_value = $('#plan_origin' + (index + 1)).val();
        var new_plan_balance = loan_money - Number(plan_origin_value.replace(/,/gi,""));
        $('#plan_balance' + (index + 1)).val(new_plan_balance);
        var hiddenValue = $('#td_plan_balance' + (index + 1) + ' input[type="hidden"]').val(new_plan_balance).clone(); 
        $('#td_plan_balance' + (index + 1)).html(new_plan_balance.toLocaleString()).append(hiddenValue);
        loan_money = new_plan_balance;       
    });

    //세금 적용
    var get_targetInterestMoney = $("#inputTbody input[name^='plan_interest[]']");
    var income_rate = $('#income_rate').val();     //소득세율
    var local_rate = $('#local_rate').val();      //지방소득세율

    $.each(get_targetInterestMoney, function(index, value) {
        //소득세
        var plan_interest = Number($('#plan_interest' + (index + 1)).val().replace(/,/gi,""));
        var income_tax = Math.floor(plan_interest * (income_rate / 100) / 10) * 10;
        $('#income_tax' + cnt).val(income_tax);
        var hiddenValue = $('#td_income_tax' +  (index + 1) + ' input[type="hidden"]').val(income_tax).clone(); 
        $('#td_income_tax' +  (index + 1)).html(income_tax.toLocaleString()).append(hiddenValue); 

        //지방소득세
        var local_tax = Math.floor(income_tax * (local_rate / 100) / 10) * 10;
        $('#local_tax' + (index + 1)).val(local_tax);
        var hiddenValue = $('#td_local_tax' + (index + 1) + ' input[type="hidden"]').val(local_tax).clone(); 
        $('#td_local_tax' + (index + 1)).html(local_tax.toLocaleString()).append(hiddenValue);

        //원천징수
        var withholding_tax = income_tax + local_tax;
        $('#withholding_tax' + (index + 1)).val(withholding_tax);
        var hiddenValue = $('#td_withholding_tax' + (index + 1) + ' input[type="hidden"]').val(withholding_tax).clone(); 
        $('#td_withholding_tax' + (index + 1)).html(withholding_tax.toLocaleString()).append(hiddenValue);

        //실지급액 조정    
        var plan_origin = Number($('#plan_origin' + (index + 1)).val().replace(/,/gi,""));
        var plan_money = plan_origin + plan_interest - withholding_tax;

        $('#plan_money' + (index + 1)).val(plan_money);
        var hiddenValue = $('#td_plan_money' + (index + 1) + ' input[type="hidden"]').val(plan_money).clone(); 
        $('#td_plan_money' + (index + 1)).html(plan_money.toLocaleString()).append(hiddenValue); 
    });
    
    // 합계 변경
    $('#td_tot_plan_origin').html(cal_plan_origin).number(true);
    $('#td_tot_plan_interest').html(cal_plan_interest).number(true);
    $('#td_tot_withholding_tax').html(cal_withholding_tax).number(true);
    $('#td_tot_income_tax').html(cal_income_tax).number(true);
    $('#td_tot_local_tax').html(cal_local_tax).number(true);
    $('#td_tot_plan_money').html(cal_plan_money).number(true);
}

// 스케줄미리보기
function reviewSchedule()
{
    // 입력값 확인
    if(!validCheck('INS', 'C')) return false;

    // 스케줄 테이블 비우기
    $('#inputTbody').empty();

    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

    var postdata = $('#investment_form').serialize();

    $.ajax({
        url  : "/account/reviewinvestschedule",
        type : "post",
        data : postdata,
        success : function(data)
        {
            $("#inputTbody").html(data);
        },
        error : function(xhr)
        {
            alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
}

// 투자등록
function confirmInvest(div, code)
{   
    // 입력값 확인
    if(!validCheck(div, code)) return false;

    $('#actMode').val(div);

    var postdata = $('#investment_form').serialize();

    $.ajax({
        url  : "/account/investmentinfoaction",
        type : "post",
        data : postdata,
        success : function(data)
        {
            if(data.rs_code=="Y") 
            {
                alert(data.result_msg);

                document.location.href = "/account/investmentpop?no="+$('#loan_info_no').val();
                
                return false;
            }
            // 실패알림
            else 
            {
                alert(data.result_msg);
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
}

function addRow(f)
{
    scheduleCnt++;
    let num = $(".addbtn").index(f);
    let tr = '<tr>';
        tr+= '<td class="text-center"><input type="hidden" name="divide_flag[]" value="">'+scheduleCnt+'</td>';
        tr+= '<td class="text-center" colspan="2">';
        tr+= '  <div class="row">';
        tr+= '      <div class="col-md-10 m-0 pr-0">';
        tr+= '          <div class="input-group date datetimepicker" id="plan_date_div'+scheduleCnt+'" data-target-input="nearest">';
        tr+= '              <input type="text" class="form-control form-control-sm dateformat" name="plan_date[]" id="plan_date[]" inputmode="text" value="">';
        tr+= '              <div class="input-group-append" data-target="#plan_date_div'+scheduleCnt+'" data-toggle="datetimepicker">';
        tr+= '                  <div class="input-group-text"><i class="fa fa-calendar"></i></div>';
        tr+= '              </div>';
        tr+= '          </div>';
        tr+= '      </div>';
        tr+= '  </div>';
        tr+= '</td>';
        tr+= '<td class="text-right"></td>';
        tr+= '<td class="text-right" id="td_plan_balance'+scheduleCnt+'"><input type="hidden" id="plan_balance'+scheduleCnt+'" name="plan_balance[]" value="">0</td>';
        tr+= '<td class="text-right">';
        tr+= '<input type="text" class="form-control form-control-sm text-right moneyformat" id="plan_origin'+scheduleCnt+'" name="plan_origin[]" placeholder="원단위 입력" onkeyup="setInput('+scheduleCnt+');">';
        tr+= '</td>';
        tr+= '<td class="text-right">';
        tr+= '<input type="text" class="form-control form-control-sm text-right moneyformat" id="plan_interest'+scheduleCnt+'" name="plan_interest[]" placeholder="원단위 입력" onkeyup="setInput('+scheduleCnt+');">';
        tr+= '</td>';
        tr+= '<td class="text-right" id="td_withholding_tax'+scheduleCnt+'"><input type="hidden" id="withholding_tax'+scheduleCnt+'" name="withholding_tax[]" value="">0</td>';
        tr+= '<td class="text-right" id="td_income_tax'+scheduleCnt+'"><input type="hidden" id="income_tax'+scheduleCnt+'" name="income_tax[]" value="">0</td>';
        tr+= '<td class="text-right" id="td_local_tax'+scheduleCnt+'"><input type="hidden" id="local_tax'+scheduleCnt+'" name="local_tax[]" value="">0</td>';
        tr+= '<td class="text-right" id="td_plan_money'+scheduleCnt+'"><input type="hidden" id="plan_money'+scheduleCnt+'" name="plan_money[]" value="">0</td>';
        tr+= '<td class="text-center">';
        tr+= '<div class="row">';
        tr+= '<div class="col-sm-5 m-0 pr-0">';
        tr+= '<button type="button" class="btn btn-default btn-sm float-center mr-2 addbtn" onclick="addRow(this);"><i class="fa fa-xs fa-plus-square text-info"></i></button>';
        tr+= '</div>';
        tr+= '<div class="col-sm-5 m-0 pr-0">';
        tr+= '<button type="button" class="btn btn-default btn-sm float-center mr-2 delbtn" onclick="delRow(this);"><i class="fa fa-xs fa-minus-square text-danger"></i></button>';
        tr+= '</div>';
        tr+= '</div>';
        tr+= '</td>';
        tr+= '</tr>';
    $("#inputTbody tr:eq("+num+")").after(tr);

    $("#inputTbody tr").each(function(index)
    {
        let newIndex = index + 1;

        if ($(this).find("td[id^='td_sum']").length > 0) {
            return true; 
        }
        
        if ($(this).find("td[id='td_money_sum']").length > 0) {
            return true; 
        }
        
        $(this).find("td:eq(0)").html(newIndex);
        $(this).find("input[name='plan_date[]']").attr("id", "plan_date" + newIndex);
        $(this).find("input[name='plan_origin[]']").attr("id", "plan_origin" + newIndex);
        $(this).find("input[name='plan_origin[]']").attr("onkeyup", "setInput(" + newIndex+")");
        $(this).find("input[name='plan_interest[]']").attr("id", "plan_interest" + newIndex);
        $(this).find("input[name='plan_interest[]']").attr("onkeyup", "setInput(" + newIndex+")");
        $(this).find("td[id^='td_plan_balance']").attr("id", "td_plan_balance" + newIndex);
        $(this).find("input[name='plan_balance[]']").attr("id", "plan_balance" + newIndex);
        $(this).find("td[id^='td_withholding_tax']").attr("id", "td_withholding_tax" + newIndex);
        $(this).find("input[name='withholding_tax[]']").attr("id", "withholding_tax" + newIndex);
        $(this).find("td[id^='td_income_tax']").attr("id", "td_income_tax" + newIndex);
        $(this).find("input[name='income_tax[]']").attr("id", "income_tax" + newIndex);
        $(this).find("td[id^='td_local_tax']").attr("id", "td_local_tax" + newIndex);
        $(this).find("input[name='local_tax[]']").attr("id", "local_tax" + newIndex);
        $(this).find("td[id^='td_plan_money']").attr("id", "td_plan_money" + newIndex);
        $(this).find("input[name='plan_money[]']").attr("id", "plan_money" + newIndex);

        $(".addbtn").index();
        $("#plan_date_div"+newIndex).datetimepicker({
            format: 'YYYY-MM-DD',
            locale: 'ko',
            useCurrent: false,
        });
    });
    setInputMask('class', 'moneyformat', 'money');
    setInput(0);
}

function delRow(f)
{
    scheduleCnt--;
    let num = $(".delbtn").index(f);
    $("#inputTbody tr:eq("+num+")").remove();
    $(".delbtn").index();
    
    $("#inputTbody tr").each(function(index) {
        let newIndex = index + 1;

        if ($(this).find("td[id^='td_sum']").length > 0) {
            return true;
        }
        
        if ($(this).find("td[id='td_money_sum']").length > 0) {
            return true; 
        }

        $(this).find("td:eq(0)").html(newIndex);
        $(this).find("input[name='plan_date[]']").attr("id", "plan_date" + newIndex);
        $(this).find("input[name='plan_origin[]']").attr("id", "plan_origin" + newIndex);
        $(this).find("input[name='plan_origin[]']").attr("onkeyup", "setInput(" + newIndex+")");
        $(this).find("input[name='plan_interest[]']").attr("id", "plan_interest" + newIndex);
        $(this).find("input[name='plan_interest[]']").attr("onkeyup", "setInput(" + newIndex+")");
        $(this).find("td[id^='td_plan_balance']").attr("id", "td_plan_balance" + newIndex);
        $(this).find("input[name='plan_balance[]']").attr("id", "plan_balance" + newIndex);
        $(this).find("td[id^='td_withholding_tax']").attr("id", "td_withholding_tax" + newIndex);
        $(this).find("input[name='withholding_tax[]']").attr("id", "withholding_tax" + newIndex);
        $(this).find("td[id^='td_income_tax']").attr("id", "td_income_tax" + newIndex);
        $(this).find("input[name='income_tax[]']").attr("id", "income_tax" + newIndex);
        $(this).find("td[id^='td_local_tax']").attr("id", "td_local_tax" + newIndex);
        $(this).find("input[name='local_tax[]']").attr("id", "local_tax" + newIndex);
        $(this).find("td[id^='td_plan_money']").attr("id", "td_plan_money" + newIndex);
        $(this).find("input[name='plan_money[]']").attr("id", "plan_money" + newIndex);
    });
    
    setInput(0);
}

// 계좌실명조회
function changeLoanBank()
{
    $("#loan_bank_status").val('N');
    $("#loan_bank_btn").attr("disabled", false);
}

function bankCheck(div, loan_info_no)
{
    var loan_bank_cd        = $('#loan_bank_cd').val();
    var loan_bank_ssn       = $('#loan_bank_ssn').val();

    if( $('#loan_bank_cd').val() =="" )
    {
        alert("은행을 선택해주세요.");
        $('#loan_bank_cd').focus();
        return false;
    }
    if( $('#loan_bank_ssn').val() =="" )
    {
        alert("계좌번호를 입력해주세요.");
        $('#loan_bank_ssn').focus();
        return false;
    }
    if( $('#loan_bank_status').val() =="Y" )
    {
        alert("실명인증이 되어있습니다.");
        $("#loan_bank_btn").attr("disabled", true);

        return false;
    }

    // 중복클릭 방지
    if(ccCheck()) return;
    
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url  : "/account/loanbanksearch",
        type : "post",
        data : { div : div, loan_info_no : loan_info_no, loan_bank_cd : loan_bank_cd, loan_bank_ssn : loan_bank_ssn },
        success : function(result)
        {
            if( result['rs_code'] == "Y" )
            {
                globalCheck = false;
                alert(result['result_msg']);
                
                $("#loan_bank_status").val('Y');
                $("#loan_bank_btn").attr("disabled", true);
            }
            else
            {
                globalCheck = false;
                alert(result['result_msg']);
            }
        },
        error : function(xhr)
        {
            globalCheck = false;
            alert("통신오류입니다.");
        }
    });
}

function validCheck(div, code)
{
    if(div == 'INS')
    {
        if( $('#contract_date').val() =="" )
        {
            alert("투자일자를 입력해주세요.");
            $('#contract_date').focus();
            return false;
        }
        if( $('#contract_end_date').val() =="" )
        {
            alert("만기일자를 입력해주세요.");
            $('#contract_end_date').focus();
            return false;
        }
        if( $('#contract_day').val() == "일" || $('#contract_day').val() == "")
        {
            alert("이자지급일을 입력해주세요.");
            $('#contract_day').focus();
            return false;
        }
        if( $('#pay_term').val() == "월" || $('#pay_term').val() == "" )
        {
            alert("상환주기를 입력해주세요.");
            $('#pay_term').focus();
            return false;
        }
        if( Number($('#loan_money').val().replace(/,/gi,"")) <= 0 )
        {
            alert("투자금액을 입력해주세요.");
            $('#loan_money').focus();
            return false;
        }
        if( $('#invest_rate').val() =="" )
        {
            alert("이자율을 입력해주세요.");
            $('#invest_rate').focus();
            return false;
        }
        if( $('#income_rate').val() =="" )
        {
            alert("소득세율을 입력해주세요.");
            $('#income_rate').focus();
            return false;
        }
        if( $('#local_rate').val() =="" )
        {
            alert("지방소득세율을 입력해주세요.");
            $('#local_rate').focus();
            return false;
        }
    }

    if(code == 'B')
    {
        if( $('#viewing_return_method').val() =="" )
        {
            alert("상환방식을 선택해주세요.");
            $('#viewing_return_method').focus();
            return false;
        }
    }

    if( $('#loan_bank_cd').val() =="" )
    {
        alert("은행을 선택해주세요.");
        $('#loan_bank_cd').focus();
        return false;
    }
    if( $('#loan_bank_ssn').val() =="" )
    {
        alert("계좌번호를 입력해주세요.");
        $('#loan_bank_ssn').focus();
        return false;
    }
    if( $('#loan_bank_status').val() != "Y" )
    {
        alert('계좌실명조회 버튼을 눌러주세요');
        return false;
    }
    if( $('#loan_bank_name').val() =="" )
    {
        alert("예금주명을 입력해주세요.");
        $('#loan_bank_name').focus();
        return false;
    }

    if(code != 'C')
    {
        if(div == 'INS')
        {
            if(!confirm("투자등록을 하시겠습니까?")) return false;
        }
        else
        {
            if(!confirm("수정을 하시겠습니까?")) return false;
        }
    }
    
    return true;
}

$(document).ready(function()
{
    scheduleCnt = {{ $scheduleCnt }};
});

</script>