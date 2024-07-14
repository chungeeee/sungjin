@extends('layouts.masterPop')

@section('content')
<style>
    .plan-data {
        background-color:transparent;
    }
    .table-input th {
        font-weight: bold;
        text-align: center;
        padding: 0px;
    }
    .settle_condition_table th {
        padding: 2px;
        text-align: center;
    }
    .settle_condition_table td {
        padding: 2px;
        font-size: 0.8rem;
    }
    </style>
<form  name="settle_form" id="settle_form" method="post"  >
    <div class="content-wrapper needs-validation m-0">
        @csrf

        <input type="hidden" id="action_mode" name="action_mode" value="">
        <input type="hidden" id="settle_no" name="settle_no" value="{{ $settle_no ?? ''}}">
        <input type="hidden" name="cust_info_no" value="{{ $loan['cust_info_no'] ?? '' }}">
        <input type="hidden" name="trade_type" value="{{ $loan['return_method_cd'] ?? '' }}">

        <div class="col-12">
            <section class="content-header pl-3 pb-1">
            <h6 class="font-weight-bold text-sm"><i class="fas fa-user mr-2"></i> 고객번호 {{ $cust->no }} / {{ $cust->name }} 고객 화해결재</h6>
            </section>
            <div class="bg-white text-center col-12" style="cursor:pointer;" onclick="getPopUp('/erp/custpop?cust_info_no={{ $loan['cust_info_no'] ?? '' }}&no={{ $loanInfoNo ?? '' }}','LOANPOP','width=2000, height=1000, scrollbars=yes');">
            @include('inc/loanSimpleLine')
            </div>
        <div>

        <!-- 초입금정보 구분 시작 -->
        <div class="col-md-12">
            <div class="card card-outline card-secondary">
                <div class="card-header p-1">
                    <h3 class="card-title font-weight-bold text-sm"><i class="fas fa-won-sign m-2"></i>초입금정보 등록</h3>
                </div>
                <div class="card-body p-1">
                    <table class="table table-sm table-bordered table-input text-xs">
                        <colgroup>
                        <col width="10%"/>
                        <col width="40%"/>
                        <col width="10%"/>
                        <col width="40%"/>
                        </colgroup>
                        <tbody>
                        <tr>
                            <th>계약번호</th>
                            <td>
                                <div class="col-md-12 row pl-2" >
                                <input type="text" class="form-control form-control-sm col-md-3" name="loan_info_no" id="loan_info_no" onkeyup="onlyNumber(this);" value="{{ $loanInfoNo  }}" readOnly>
                                </div>
                            </td>
                            <th>
                                <span class="text-danger font-weight-bold h6 mr-1">*</span>
                                화해사유
                            </th>
                            <td>
                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="hidden" name="sub_type" id="sub_type" value="{{Func::nvl($loan['sub_type'],'')}}">
                                        <select class="form-control form-control-sm" onchange="getSubInfo(); " disabled>
                                        <option value=''>화해구분 선택</option>
                                        {{ Func::printOption($sub_type,Func::nvl($loan['sub_type'], '')) }} 
                                        </select>
                                    </div>
                                    <div class="col-md-3">
                                        <input type="hidden" name="sub_type_cd" id="sub_type_cd" value="{{Func::nvl($loan['sub_type_cd'],'')}}">
                                        <select class="form-control form-control-sm" onchange="$('#sub_type_cd').val(this.value); setConId(this.value); setSettleReason(this.value); setSettleForm(this.value);" @if(isset($loan['sub_type_cd'])) disabled  @endif>
                                        <option value=''>상세구분</option>
                                        {{ Func::printOption($sub_type_cd,Func::nvl($loan['sub_type_cd'], '')) }} 
                                        </select>
                                    </div> 
                                    <div class="col-md-3">
                                        <!-- <select class="form-control form-control-sm" name="stl_dtl_cd" id="settle_reason_cd"> -->
                                        <select class="form-control form-control-sm" name="settle_reason_cd" id="settle_reason_cd">
                                        <option value=''>화해사유 선택</option>
                                        {{ Func::printOption($settle_reason_cd,Func::nvl($loan['settle_reason_cd'], '')) }} 
                                        </select>
                                    </div>
                                </div>
                            </td>
                        </tr>  

                        <tr>
                            <th>초입금일</th>
                            <td>
                                <div class="col-md-12 row pl-0">
                                    <div class="input-group date datetimepicker col-md-3" id="div_settle_trade_date" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm" id="settle_trade_date" name="settle_trade_date" DateOnly='true'  value="{{ $loan['settle_trade_date'] ?? date('Y-m-d') }}" />
                                        <div class="input-group-append" data-target="#div_settle_trade_date" data-toggle="datetimepicker">
                                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                    <div class="input-group-append" >
                                        <button type="button" class="btn btn-xs btn-info float-right mr-3" id="cate_btn" onclick="getLoanInterest('C');">
                                            <i class="fa fa-calculator"></i>이자계산</button>
                                    </div>
                                    <div class="input-group-append pt-2">
                                        화해이자 계산 기준일입니다. (거래원장 초록색으로 표시)
                                    </div>
                                </div>
                            </td>
                            <th>거래지점</th>
                            <td>
                                <div class="row">   
                                    <div class="col-md-3">
                                        <select class="form-control form-control-sm" name="manager_code" id="manager_code" >
                                        <option value=''>거래지점</option>
                                        {{ Func::printOption($branch_list,Func::nvl($loan['manager_code'],''))  }} 
                                        </select>
                                    </div>
                                </div>
                            </td>
                        </tr>  

                        <tr>
                            <th>화해계약자</th>
                            <td>
                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="text" class="form-control form-control-sm" name="settle_name" id="settle_name" value="{{ Func::nvl($loan['settle_name'],'') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control form-control-sm " name="settle_name_rel_cd" id="settle_name_rel_cd">
                                            <option value=''>계약자와관계 선택</option>
                                            {{ Func::printOption($relation_cd,Func::nvl($loan['settle_name_rel_cd'],'')) }}
                                        </select>
                                    </div>
                                    <div class="col-md-2 pt-2 font-weight-bold text-center">
                                        차주구분
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control form-control-sm " name="target_div" id="target_div">
                                            {{ Func::printOption($stl_target_cd,Func::nvl($loan['target_div'],'')) }} 
                                        </select>
                                    </div>

                                <div>
                                <input type="hidden" class="form-control form-control-sm col-md-3" name="settle_in_money" id="settle_in_money" value="{{ Func::numberFormat(Func::nvl($loan['settle_in_money'],'')) ?? '' }}" onkeyup="onlyNumber(this);CalReturnMoney();inputComma(this);" readonly>
                            </td>
                            <!--
                            <th>초입금액</th>
                            <td>
                                <div class="col-md-12 row pl-2" >
                                    <input type="text" class="form-control form-control-sm col-md-3" name="settle_in_money" id="settle_in_money" value="{{ Func::numberFormat(Func::nvl($loan['settle_in_money'],'')) ?? '' }}" onkeyup="onlyNumber(this);CalReturnMoney();inputComma(this);" readonly>
                                    <span class="pt-2">&nbsp;원</span>
                                </div>
                            </td>
                            -->
                            <th>입금자명</th>
                            <td>
                                <div class="row">
                                    <div class="col-md-3">
                                        <input type="text" class="form-control form-control-sm" name="settle_in_name" id="settle_in_name" value="{{ Func::nvl($loan['settle_in_name'],'') }}">
                                    </div>
                                    <div class="col-md-3">
                                        <select class="form-control form-control-sm " name="settle_in_name_rel_cd" id="settle_in_name_rel_cd">
                                            <option value=''>입금자와관계 선택</option>
                                            {{ Func::printOption($relation_cd,Func::nvl($loan['settle_in_name_rel_cd'],''))  }} 
                                        </select>
                                    </div>
                                <div>
                            </td>
                        </tr>  

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- 개인회생정보 구분 시작 -->
        <div class="col-md-12" id="irl" style="display:none;">
            <div class="card card-outline card-secondary">
                <div class="card-header p-1">
                    <h3 class="card-title font-weight-bold text-sm"><i class="fas fa-table m-2"></i>개인회생</h3>
                </div>
                <div class="card-body p-1">
                    <table class="table table-sm table-bordered table-input text-xs">
                        <colgroup>
                        <col width="6%"/>
                        <col width="10%"/>
                        <col width="7%"/>
                        <col width="10%"/>
                        <col width="7%"/>
                        <col width="10%"/>
                        <col width="8%"/>
                        <col width="17%"/>
                        <col width="8%"/>
                        <col width="17%"/>
                        </colgroup>
                        <tbody>
                        <tr>
                            <th>
                                법원
                            </th>
                            <td>
                                <div class="col-md-12">
                                    <input type="hidden" name="court_cd" id="court_cd" value="{{Func::nvl($loan['court_cd'],'')}}">
                                    <select class="form-control form-control-sm">
                                    <option value=''>법원 선택</option>
                                    {{ Func::printOption($court_cd,Func::nvl($loan['court_cd'],'')) }} 
                                    </select>
                                </div>
                            </td>
                            <th>
                                사건번호
                            </th>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['dd'],'2023개회100999') }}">
                                </div>
                            </td>
                            <th>
                                당사자명
                            </th>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['dd'],'대부') }}">
                                </div>
                            </td>
                            <th>
                                <span class="text-danger font-weight-bold h6 mr-1">*</span>
                                접수일
                            </th>
                            <td>
                                <div class="col-md-11">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['dd'],'') }}">
                                </div>
                            </td>
                            <th>
                                명의변경신고일
                            </th>
                            <td>
                                <div class="input-group date datetimepicker col-md-11" id="div_name_change_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm" id="name_change_date" name="name_change_date" DateOnly='true'  value="{{ $loan['name_change_date'] ?? '' }}" />
                                    <div class="input-group-append" data-target="#div_name_change_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                        </tr>  

                        <tr>
                            <th>
                                변제채권번호
                            </th>
                            <td>
                                <div class="col-md-12" >
                                    <input type="text" class="form-control form-control-sm col-md-12" name="loan_info_no" id="loan_info_no" onkeyup="onlyNumber(this);" value="{{ $loanInfoNo  }}">
                                </div>
                            </td>
                            <th>
                                분납회생코드
                            </th>
                            <td>
                                <div class="col-md-12">
                                    <input type="hidden" name="save_code" id="save_code" value="{{Func::nvl($loan['save_code'],'')}}">
                                    <select class="form-control form-control-sm">
                                    <option value=''>분납회생코드</option>
                                    {{ Func::printOption($save_code, Func::nvl($loan['save_code'],'')) }} 
                                    </select>
                                </div>
                            </td>
                            <th>
                                변제현황일치여부
                            </th>
                            <td>
                                <div class="col-md-12">
                                    <input type="hidden" name="repay_flag" id="repay_flag" value="{{Func::nvl($loan['repay_flag'],'')}}">
                                    <select class="form-control form-control-sm">
                                    <option value=''>분납회생코드</option>
                                    {{ Func::printOption($repay_flag, Func::nvl($loan['repay_flag'],'')) }} 
                                    </select>
                                </div>
                            </td>
                            <th>
                                <span class="text-danger font-weight-bold h6 mr-1">*</span>
                                개시결정일
                            </th>
                            <td>
                                <div class="col-md-11">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['start_date'],'') }}">
                                </div>
                            </td>
                            <th>
                                계좌변경신고일
                            </th>
                            <td>
                                <div class="input-group date datetimepicker col-md-11" id="div_report_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm" id="report_date" name="report_date" DateOnly='true'  value="{{ $loan['report_date'] ?? '' }}" />
                                    <div class="input-group-append" data-target="#div_report_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                입금은행
                            </th>
                            <td>
                                <div class="col-md-12">
                                    <input type="hidden" name="bank_cd" id="bank_cd" value="{{Func::nvl($loan['deposit_bank'],'')}}">
                                    <select class="form-control form-control-sm">
                                    <option value=''>은행코드</option>
                                    {{ Func::printOption($bank_cd,Func::nvl($loan['deposit_bank'],'')) }} 
                                    </select>
                                </div>
                            </td>
                            <th>
                                입금계좌
                            </th>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['deposit_account'],'') }}">
                                </div>
                            </td>
                            <th>
                                입금채권자
                            </th>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['deposit_bond'],'') }}">
                                </div>
                            </td>
                            <th>
                                <span class="text-danger font-weight-bold h6 mr-1">*</span>
                                인가일
                            </th>
                            <td>
                                <div class="col-md-11">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['approve_date'],'') }}">
                                </div>
                            </td>
                            <th>
                                공탁금입금요청발송일
                            </th>
                            <td>
                                <div class="input-group date datetimepicker col-md-11" id="div_deposit_in_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm" id="deposit_in_date" name="deposit_in_date" DateOnly='true'  value="{{ $loan['deposit_in_date'] ?? '' }}" />
                                    <div class="input-group-append" data-target="#div_deposit_in_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                신고은행
                            </th>
                            <td>
                                <div class="col-md-12">
                                    <input type="hidden" name="bank_cd" id="bank_cd" value="{{Func::nvl($loan['report_bank'],'')}}">
                                    <select class="form-control form-control-sm">
                                    <option value=''>은행코드</option>
                                    {{ Func::printOption($bank_cd,Func::nvl($loan['report_bank'],'')) }} 
                                    </select>
                                </div>
                            </td>
                            <th>
                                신고계좌
                            </th>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['report_account'],'') }}">
                                </div>
                            </td>
                            <th>
                                신고채권자
                            </th>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['report_bond'],'') }}">
                                </div>
                            </td>
                            <th>
                                <span class="text-danger font-weight-bold h6 mr-1">*</span>
                                폐지결정일
                            </th>
                            <td>
                                <div class="col-md-11">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['revoke_date_repay'],'') }}">
                                </div>
                            </td>
                            <th>
                                기타채권신고일
                            </th>
                            <td>
                                <div class="row" style="margin-left:1px;">
                                    <div class="input-group date datetimepicker col-md-6" id="div_etc_notify_date" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm" id="etc_notify_date" name="etc_notify_date" DateOnly='true'  value="{{ $loan['etc_notify_date'] ?? '' }}" />
                                        <div class="input-group-append" data-target="#div_etc_notify_date" data-toggle="datetimepicker">
                                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="hidden" name="report_type" id="report_type" value="{{Func::nvl($loan['report_type'],'')}}">
                                        <select class="form-control form-control-sm">
                                        <option value=''>신고종류</option>
                                        {{ Func::printOption($report_type, Func::nvl($loan['report_type'],'')) }} 
                                        </select>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                가상계좌은행
                            </th>
                            <td>
                                <div class="col-md-12">
                                    <input type="hidden" name="bank_cd" id="bank_cd" value="{{Func::nvl($loan['virtual_bank'],'')}}">
                                    <select class="form-control form-control-sm">
                                    <option value=''>은행코드</option>
                                    {{ Func::printOption($bank_cd,Func::nvl($loan['virtual_bank'],'')) }} 
                                    </select>
                                </div>
                            </td>
                            <th>
                                가상계좌
                            </th>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['virtual_account'],'') }}">
                                </div>
                            </td>
                            <th>
                                가상계좌예금자명
                            </th>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['virtual_bond'],'') }}">
                                </div>
                            </td>
                            <th>
                                <span class="text-danger font-weight-bold h6 mr-1">*</span>
                                면책결정일
                            </th>
                            <td>
                                <div class="col-md-11">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['exemption_date'],'') }}">
                                </div>
                            </td>
                            <th>
                                이의신청발송일
                            </th>
                            <td>
                                <div class="row" style="margin-left:1px;">
                                    <div class="input-group date datetimepicker col-md-6" id="div_appeal_send_date" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm" id="appeal_send_date" name="appeal_send_date" DateOnly='true'  value="{{ $loan['appeal_send_date'] ?? '' }}" />
                                        <div class="input-group-append" data-target="#div_appeal_send_date" data-toggle="datetimepicker">
                                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <input type="hidden" name="appeal_send_type" id="appeal_send_type" value="{{Func::nvl($loan['appeal_send_type'],'')}}">
                                        <select class="form-control form-control-sm">
                                        <option value=''>발송종류</option>
                                        {{ Func::printOption($appeal_send_type, Func::nvl($loan['appeal_send_type'],'')) }} 
                                        </select>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                변제은행
                            </th>
                            <td>
                                <div class="col-md-12">

                                </div>
                            </td>
                            <th>
                                변제계좌
                            </th>
                            <td>
                                <div class="col-md-12">
                                    
                                </div>
                            </td>
                            <th>
                                변제채권자
                            </th>
                            <td>
                                <div class="col-md-12">
                                    
                                </div>
                            </td>
                            <th>
                                <span class="text-danger font-weight-bold h6 mr-1">*</span>
                                채권자번호
                            </th>
                            <td>
                                <div class="col-md-11">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['creditor_no'],'') }}">
                                </div>
                            </td>
                            <th>
                                철회신청일
                            </th>
                            <td>
                                <div class="input-group date datetimepicker col-md-11" id="div_retract_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm" id="retract_date" name="retract_date" DateOnly='true'  value="{{ $loan['retract_date'] ?? '' }}" />
                                    <div class="input-group-append" data-target="#div_retract_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th colspan="2" rowspan="2">
                                ① OPB 변동 내역
                            </th>
                            <td colspan="4" rowspan="2">
                                <textarea class="form-control" rows="3" name="app_memo" id="app_memo" style="resize: none;">{{$loan['app_memo'] ?? ''}}</textarea>
                            </td>
                            <th>
                                <span class="text-danger font-weight-bold h6 mr-1">*</span>
                                회생변제 시작일
                            </th>
                            <td>
                                <div class="col-md-11">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['revival_start_date'],'') }}">
                                </div>
                            </td>
                            <th>
                                회생변제 종료일
                            </th>
                            <td>
                                <div class="col-md-11">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['revival_end_date'],'') }}">
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <span class="text-danger font-weight-bold h6 mr-1">*</span>
                                재판부
                            </th>
                            <td colspan="3">
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['court_name'],'') }}">
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th colspan="2" rowspan="2">
                                ② 현재결과 검수 내역
                            </th>
                            <td colspan="4" rowspan="2">
                                <textarea class="form-control" rows="3" name="app_memo" id="app_memo" style="resize: none;">{{$loan['app_memo'] ?? ''}}</textarea>
                            </td>
                            <th>
                                <span class="text-danger font-weight-bold h6 mr-1">*</span>
                                현재결과
                            </th>
                            <td>
                                <div class="col-md-11">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['result_now'],'') }}">
                                </div>
                            </td>
                            <th>
                                대위변제여부
                            </th>
                            <td>
                                <div class="col-md-4">
                                    <input type="hidden" name="subrogation_flag" id="subrogation_flag" value="{{Func::nvl($loan['subrogation_flag'],'')}}">
                                    <select class="form-control form-control-sm">
                                    <option value=''>선택</option>
                                    {{ Func::printOption($subrogation_flag, Func::nvl($loan['subrogation_flag'],'')) }} 
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <span class="text-danger font-weight-bold h6 mr-1">*</span>
                                현재결과일
                            </th>
                            <td>
                                <div class="col-md-11">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['result_now_date'],'') }}">
                                </div>
                            </td>
                            <th>
                                <span class="text-danger font-weight-bold h6 mr-1">*</span>
                                별제권여부
                            </th>
                            <td>
                                <div class="col-md-4">
                                    <input type="hidden" name="separate_flag" id="separate_flag" value="{{Func::nvl($loan['separate_flag'],'')}}">
                                    <select class="form-control form-control-sm">
                                    <option value=''>선택</option>
                                    {{ Func::printOption($separate_flag, Func::nvl($loan['separate_flag'],'')) }} 
                                    </select>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th colspan="2" rowspan="2">
                                ③ 신고 내역
                            </th>
                            <td colspan="4" rowspan="2">
                                <textarea class="form-control" rows="3" name="app_memo" id="app_memo" style="resize: none;">{{$loan['app_memo'] ?? ''}}</textarea>
                            </td>
                            <th>
                                <span class="text-danger font-weight-bold h6 mr-1">*</span>
                                매입전기변제금액
                            </th>
                            <td>
                                <div class="row" style="margin-left:1px;">
                                    <div class="col-md-7">
                                        <input type="text" class="form-control form-control-sm" name="" id="" value="{{ number_format($loan['buy_repay_money'] ?? 0) }}">
                                    </div>
                                    <div>
                                        <span>원</span>
                                    </div>
                                <div>
                            </td>
                            <th>
                                실기변제금
                            </th>
                            <td>
                                <div class="row" style="margin-left:1px;">
                                    <div class="col-md-7">
                                        <input type="text" class="form-control form-control-sm" name="" id="" value="{{ number_format($loan['real_repay_money'] ?? 0) }}">
                                    </div>
                                    <div>
                                        <span>원</span>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                <span class="text-danger font-weight-bold h6 mr-1">*</span>
                                진행일
                            </th>
                            <td>
                                <div class="input-group date datetimepicker col-md-11" id="div_progress_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm" id="progress_date" name="progress_date" DateOnly='true'  value="{{ $loan['progress_date'] ?? '' }}" />
                                    <div class="input-group-append" data-target="#div_progress_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                            <th>
                                <span class="text-danger font-weight-bold h6 mr-1">*</span>
                                우선변제회차
                            </th>
                            <td>
                                <div class="col-md-7">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['first_repay_cnt'],'') }}">
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th colspan="2" rowspan="2">
                                ④ 일시변제,재산처분 내역
                            </th>
                            <td colspan="4" rowspan="2">
                                <textarea class="form-control" rows="3" name="app_memo" id="app_memo" style="resize: none;">{{$loan['app_memo'] ?? ''}}</textarea>
                            </td>
                            <th>
                                <span class="text-danger font-weight-bold h6 mr-1">*</span>
                                중단일
                            </th>
                            <td>
                                <div class="input-group date datetimepicker col-md-11" id="div_revoke_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm" id="revoke_date" name="revoke_date" DateOnly='true'  value="{{ $loan['revoke_date'] ?? '' }}" />
                                    <div class="input-group-append" data-target="#div_revoke_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                            <th>
                                중단사유
                            </th>
                            <td>
                                <div class="col-md-7">
                                    <input type="hidden" name="settle_cancel" id="settle_cancel" value="{{Func::nvl($loan['settle_cancel'],'')}}">
                                    <select class="form-control form-control-sm">
                                    <option value=''>선택</option>
                                    {{ Func::printOption($settle_cancel, Func::nvl($loan['settle_cancel'],'')) }} 
                                    </select>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="4">
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-folder-fill" viewBox="0 0 16 16">
                                <path d="M9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.825a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3zm-8.322.12C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139z"/>
                            </svg>&nbsp;
                            <b style="font-size:medium;">사건조회 변제현황</b>
                            </td>
                        </tr>

                        <tr>
                            <th colspan="2" rowspan="2">
                                ⑤ 단축안 관리
                            </th>
                            <td colspan="4" rowspan="2">
                                <textarea class="form-control" rows="3" name="app_memo" id="app_memo" style="resize: none;">{{$loan['app_memo'] ?? ''}}</textarea>
                            </td>
                            <th>
                                조회일
                            </th>
                            <td>
                                <div class="col-md-12">

                                </div>
                            </td>
                            <th>
                                출금대상기준일
                            </th>
                            <td>
                                <div class="col-md-12">

                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                변제주기
                            </th>
                            <td>
                                <div class="col-md-12">

                                </div>
                            </td>
                            <th>
                                변제기일
                            </th>
                            <td>
                                <div class="col-md-12">

                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th colspan="2" rowspan="2">
                                ⑥ 스케줄 관리
                            </th>
                            <td colspan="4" rowspan="2">
                                <textarea class="form-control" rows="3" name="app_memo" id="app_memo" style="resize: none;">{{$loan['app_memo'] ?? ''}}</textarea>
                            </td>
                            <th>
                                매월변제예정금액
                            </th>
                            <td>
                                <div class="col-md-12">

                                </div>
                            </td>
                            <th>
                                출금대상잔액
                            </th>
                            <td>
                                <div class="col-md-12">

                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                재산처분예정금액
                            </th>
                            <td>
                                <div class="col-md-12">

                                </div>
                            </td>
                            <th>
                                채산처분납입금액
                            </th>
                            <td>
                                <div class="col-md-12">

                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th colspan="2" rowspan="2">
                                ⑦ 기타 관리
                            </th>
                            <td colspan="4" rowspan="2">
                                <textarea class="form-control" rows="3" name="app_memo" id="app_memo" style="resize: none;">{{$loan['app_memo'] ?? ''}}</textarea>
                            </td>
                            <th>
                                전체변제회차
                            </th>
                            <td>
                                <div class="col-md-12">

                                </div>
                            </td>
                            <th>
                                현재변제회차
                            </th>
                            <td>
                                <div class="col-md-12">

                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>
                                잔여변제회차
                            </th>
                            <td>
                                <div class="col-md-12">

                                </div>
                            </td>
                            <th>
                                채무자미납회차
                            </th>
                            <td>
                                <div class="col-md-12">

                                </div>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                    <div class="col-md-10 pt-2">
                        <b style="font-size:medium;"><i class="fas fa-list mr-2"></i>사건조회 분할정보</b>
                    </div>
                    <div class="col-md-12 p-0">
                        <table class="table table-sm text-xs card-outline settle_condition_table">
                            <thead>
                                <tr>
                                    <th width="15%" class="text-center">순번</th>
                                    <th width="15%" class="text-center">채권번호</th>
                                    <th width="15%" class="text-center">변제회차</th>
                                    <th width="15%" class="text-center">변제예정금액</th>
                                    <th width="15%" class="text-center">변제시작일</th>
                                    <th width="15%" class="text-center">변제종료일</th>
                                </tr>
                            </thead>
                            <tbody id="test_tb">
                                @if(isset($test))
                                    @forelse($test as $i => $cnt)
                                    <tr>
                                        <td><div class="col-md-12 row pl-2">
                                            <input type="text" class="form-control form-control-xs border-0 text-center col-6" id="start_cnt_{{ $i+1 }}" name="start_cnt[]" value="{{ $cnt->start_cnt ?? '0' }}" onkeyup="onlyNumber(this);" onblur="calCntMoney();" required> 
                                            <input type="text" class="form-control form-control-xs border-0 text-center col-6" value=" ~ {{ $cnt->end_cnt ?? '0' }}" readonly >  </div></td>
                                        <td><input type="text" class="form-control form-control-xs border-0 text-center" id="balance_cnt_{{ $i+1 }}" name="balance_cnt[]" value="{{ Func::numberFormat($cnt->balance) ?? '0' }}" onkeyup="onlyNumber(this);calCntMoney();inputComma(this);" required></td>
                                        <td><input type="text" class="form-control form-control-xs border-0 text-center" id="interest_cnt_{{ $i+1 }}"  name="interest_cnt[]" value="{{ Func::numberFormat($cnt->interest) ?? '0' }}" onkeyup="onlyNumber(this);calCntMoney();inputComma(this);" required></td>
                                        <td><input type="text" class="form-control form-control-xs border-0 text-center" id="trade_money_cnt_{{ $i+1 }}" name="trade_money_cnt[]" value="{{ Func::numberFormat($cnt->trade_money) ?? '0' }}" onkeyup="onlyNumber(this);calCntMoney();inputComma(this);" required></td>
                                        <td class="text-center" style="margin-top:6px;">
                                            <button type="button" class="btn btn-xs" onclick="delPlanCntRow(this);"><i class="fa fa-trash-alt text-secondary"></i></button>
                                        </td>
                                    </tr>   
                                    @empty
                                    @endforelse
                                @endif
                            </tbody>
                        </table> 
                    </div>
                </div>
            </div>
        </div>



        <!-- 신용회복정보 구분 시작 -->
        <div class="col-md-12" id="ccrs" style="display:none;">
            <div class="card card-outline card-secondary">
                <div class="card-header p-1">
                    <h3 class="card-title font-weight-bold text-sm"><i class="fas fa-table m-2"></i>신용회복</h3>
                </div>
                <div class="card-body p-1">
                    <table class="table table-sm table-bordered table-input text-xs">
                        <colgroup>
                        <col width="12%"/>
                        <col width="12%"/>
                        <col width="12%"/>
                        <col width="12%"/>
                        <col width="20%"/>
                        <col width="20%"/>
                        <col width="12%"/>
                        </colgroup>
                        <tbody>
                        <tr>
                            <th>
                                구분
                            </th>
                            <th>
                                계좌번호
                            </th>
                            <th>
                                변제금수취계좌
                            </th>
                            <td colspan="4" rowspan="2">

                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="col-md-12">
                                    <input type="hidden" name="ccrs_type" id="ccrs_type" value="{{Func::nvl($loan['ccrs_type'],'')}}">
                                    <select class="form-control form-control-sm">
                                    <option value=''>선택</option>
                                    {{ Func::printOption($ccrs_type, Func::nvl($loan['ccrs_type'],'')) }} 
                                    </select>
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['deposit_account'],'') }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['deposit_account'],'') }}">
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                접수번호
                            </th>
                            <th>
                                심의차수
                            </th>
                            <th>
                                신청인상태
                            </th>
                            <th>
                                계좌상태
                            </th>
                            <th colspan="2">
                                계좌상태
                            </th>
                            <th>
                                감면방식
                            </th>
                        </tr>
                        <tr>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['deposit_account'],'') }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['deposit_account'],'') }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['deposit_account'],'') }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['deposit_account'],'') }}">
                                </div>
                            </td>
                            <td colspan="2">
                                <table>
                                    <colgroup>
                                    <col width="9%"/>
                                    <col width="24%"/>
                                    <col width="9%"/>
                                    <col width="24%"/>
                                    <col width="9%"/>
                                    <col width="24%"/>
                                    </colgroup>
                                    <tbody>
                                        <tr>
                                            <th>
                                                실효일자
                                            </th>
                                            <td>
                                                <div class="input-group date datetimepicker col-md-12" id="div_ccrs_cancel_date" data-target-input="nearest">
                                                    <input type="text" class="form-control form-control-sm" id="ccrs_cancel_date" name="ccrs_cancel_date" DateOnly='true'  value="{{ $loan['ccrs_cancel_date'] ?? '' }}" />
                                                    <div class="input-group-append" data-target="#div_ccrs_cancel_date" data-toggle="datetimepicker">
                                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <th>
                                                완제일자
                                            </th>
                                            <td>
                                                <div class="input-group date datetimepicker col-md-12" id="div_ccrs_end_date" data-target-input="nearest">
                                                    <input type="text" class="form-control form-control-sm" id="ccrs_end_date" name="ccrs_end_date" DateOnly='true'  value="{{ $loan['ccrs_end_date'] ?? '' }}" />
                                                    <div class="input-group-append" data-target="#div_ccrs_end_date" data-toggle="datetimepicker">
                                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                </div>
                                            </td>
                                            <th>
                                                포기일자
                                            </th>
                                            <td>
                                                <div class="input-group date datetimepicker col-md-12" id="div_ccrs_lose_date" data-target-input="nearest">
                                                    <input type="text" class="form-control form-control-sm" id="ccrs_lose_date" name="ccrs_lose_date" DateOnly='true'  value="{{ $loan['ccrs_lose_date'] ?? '' }}" />
                                                    <div class="input-group-append" data-target="#div_ccrs_lose_date" data-toggle="datetimepicker">
                                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['deposit_account'],'') }}">
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                접수통지일
                            </th>
                            <th>
                                접수일
                            </th>
                            <th>
                                확정통지일
                            </th>
                            <th>
                                확정일
                            </th>
                            <th>
                                상환개시일
                            </th>
                            <th>
                                재조정여부
                            </th>
                            <th>
                                재조정처리일자
                            </th>
                        </tr>
                        <tr>
                            <td>
                                <div class="input-group date datetimepicker col-md-11" id="div_ccrs_app_alarm_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm" id="ccrs_app_alarm_date" name="ccrs_app_alarm_date" DateOnly='true'  value="{{ $loan['ccrs_app_alarm_date'] ?? '' }}" />
                                    <div class="input-group-append" data-target="#div_ccrs_app_alarm_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="input-group date datetimepicker col-md-11" id="div_ccrs_app_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm" id="ccrs_app_date" name="ccrs_app_date" DateOnly='true'  value="{{ $loan['ccrs_app_date'] ?? '' }}" />
                                    <div class="input-group-append" data-target="#div_ccrs_app_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="input-group date datetimepicker col-md-11" id="div_ccrs_confirm_alarm_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm" id="ccrs_confirm_alarm_date" name="ccrs_confirm_alarm_date" DateOnly='true'  value="{{ $loan['ccrs_confirm_alarm_date'] ?? '' }}" />
                                    <div class="input-group-append" data-target="#div_ccrs_confirm_alarm_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="input-group date datetimepicker col-md-11" id="div_ccrs_confirm_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm" id="ccrs_confirm_date" name="ccrs_confirm_date" DateOnly='true'  value="{{ $loan['ccrs_confirm_date'] ?? '' }}" />
                                    <div class="input-group-append" data-target="#div_ccrs_confirm_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="input-group date datetimepicker col-md-11" id="div_ccrs_start_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm" id="ccrs_start_date" name="ccrs_start_date" DateOnly='true'  value="{{ $loan['ccrs_start_date'] ?? '' }}" />
                                    <div class="input-group-append" data-target="#div_ccrs_start_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['ccrs_readjust_flag'],'') }}">
                                </div>
                            </td>
                            <td>
                                <div class="input-group date datetimepicker col-md-11" id="div_ccrs_readjust_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm" id="ccrs_readjust_date" name="ccrs_readjust_date" DateOnly='true'  value="{{ $loan['ccrs_readjust_date'] ?? '' }}" />
                                    <div class="input-group-append" data-target="#div_ccrs_readjust_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>
                            </th>
                            <th>
                            </th>
                            <th>
                            </th>
                            <th>
                            </th>
                            <th>
                            </th>
                            <th>
                                수정조정여부
                            </th>
                            <th>
                                수정확정일
                            </th>
                        </tr>
                        <tr>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ Func::nvl($loan['deposit_account'],'') }}">
                                </div>
                            </td>
                            <td>
                                <div class="input-group date datetimepicker col-md-11" id="div_ccrs_confirm_alter_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm" id="ccrs_confirm_alter_date" name="ccrs_confirm_alter_date" DateOnly='true'  value="{{ $loan['ccrs_confirm_alter_date'] ?? '' }}" />
                                    <div class="input-group-append" data-target="#div_ccrs_confirm_alter_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                조정전원금
                            </th>
                            <th>
                                조정전이자
                            </th>
                            <th>
                                조정전연체이자
                            </th>
                            <th>
                                조정전비용
                            </th>
                            <th>
                                조정전합계
                            </th>
                            <td colspan="2" rowspan="10">
                                <table class="table table-sm table-bordered table-input text-xs">
                                <colgroup>
                                <col width="20%"/>
                                <col width="80%"/>
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <th>
                                            ① 확정 메모
                                        </th>
                                        <td>
                                            <textarea class="form-control" rows="3" name="" id="" style="resize: none;">{{$loan['app_memo'] ?? ''}}</textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            ② 실효 메모
                                        </th>
                                        <td>
                                            <textarea class="form-control" rows="3" name="" id="" style="resize: none;">{{$loan['app_memo'] ?? ''}}</textarea>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>
                                            ③ 완제 메모
                                        </th>
                                        <td>
                                            <textarea class="form-control" rows="3" name="" id="" style="resize: none;">{{$loan['app_memo'] ?? ''}}</textarea>
                                        </td>
                                    </tr>
                                </tbody>
                                </table>
                            </td>
                        </tr>
                        <tr>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                조정후원금
                            </th>
                            <th>
                                조정후이자
                            </th>
                            <th>
                                조정후연체이자
                            </th>
                            <th>
                                조정후비용
                            </th>
                            <th>
                                조정후합계
                            </th>
                        </tr>
                        <tr>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                원금균등산환기간
                            </th>
                            <th>
                                납입회차
                            </th>
                            <th>
                                원금균등채무액
                            </th>
                            <th>
                                상환후잔액
                            </th>
                            <th>
                                
                            </th>
                        </tr>
                        <tr>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                총납입원금
                            </th>
                            <th>
                                총납입이자
                            </th>
                            <th>
                                총납입기타채무
                            </th>
                            <th>
                                총납입금액
                            </th>
                            <th>
                                
                            </th>
                        </tr>
                        <tr>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                            </td>
                        </tr>

                        <tr>
                            <th>
                                유예기간
                            </th>
                            <th>
                                원금균등시작회차
                            </th>
                            <th>
                                원금균등종료회차
                            </th>
                            <th>
                            </th>
                            <th>
                            </th>
                        </tr>
                        <tr>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                                <div class="col-md-12">
                                    <input type="text" class="text-right form-control form-control-sm" name="" id="" value="{{ number_format($loan['money'] ?? 0) }}">
                                </div>
                            </td>
                            <td>
                            </td>
                            <td>
                            </td>
                        </tr>

                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        
        <!-- 개인회생, 신용회복 추가정보 입력 영역 -->
        <div class="col-md-12" id="settle-sub"></div>


        <!-- 화해조건 구분 시작 -->
        <div class="col-md-12">
            <div class="card card-outline card-secondary">
                <div class="card-header p-1">
                    <h3 class="card-title font-weight-bold text-sm"><i class="fas fa-table m-2"></i>화해조건</h3>
                </div>
                <input type="hidden" name="div" id="div" >
                <div class="card-body p-1">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-sm table-bordered table-input text-xs mb-0">
                                <colgroup>
                                <col width="20%"/>
                                <col width="30%"/>
                                <col width="20%"/>
                                <col width="30%"/>
                                </colgroup>
                                <tbody>
                                    <tr>
                                        <th>화해금액</th>
                                        <td>
                                            <div class="col-md-12 row pl-2">
                                                <input type="text" class="form-control form-control-sm col-md-6" name="settle_money" id="settle_money"  value="{{ Func::numberFormat(Func::nvl($loan['settle_money'],'')) ?? '0' }}" onkeyup="onlyNumber(this);CalReturnMoney();inputComma(this);setExpectSettle();" required>
                                                <span class="pt-2">&nbsp;원</span>
                                                <span class="pt-2 status-text-d" id="ex_plan_mny"></span>
                                            </div>
                                        </td>
                                        <th>조정전금액</th>
                                        <td>
                                            <div class="col-md-12 row pl-2">
                                                <input type="text" class="form-control form-control-sm col-md-8" id="before_money" readonly>
                                                <span class="pt-2">&nbsp;원</span>
                                            </div>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th>분할회차</th>
                                        <td>
                                            <div class="col-md-12 row pl-2">
                                                <input type="text" class="form-control form-control-sm col-md-3" name="settle_cnt" id="settle_cnt"  value="{{ Func::nvl($loan['settle_cnt'],'') }}" onkeyup="onlyNumber(this);" required onchange="setExpectSettle();">
                                                <span class="pt-2">&nbsp;회</span>
                                                <div class="col-md-7">
                                                    <select class="form-control form-control-sm" name="settle_cycle_cd" id="settle_cycle_cd">
                                                        <option value='' >납입주기</option>
                                                        {{ Func::printOption($settle_cycle_cd,Func::nvl($loan['settle_cycle_cd'],''))  }} 
                                                    </select>
                                                </div>
                                            </div>
                                        </td>
                                        <th>감면금액</th>
                                        <td>
                                            <div class="col-md-12 row pl-2">
                                                <input type="text" class="form-control form-control-sm col-md-8" id="after_lose_money" readonly>
                                                <span class="pt-2">&nbsp;원</span>
                                            </div>
                                        </td>
                                    </tr>
{{-- 
                                    <tr>
                                        <th>전산원금</th>
                                        <td>
                                            <div class="col-md-12 row pl-2">
                                                <input type="text" class="form-control form-control-sm col-md-6" name="" id=""  value="{{ Func::numberFormat(Func::nvl($loan['buy_tail_money'],'')) ?? '0' }}" required>
                                                <span class="pt-2">&nbsp;원</span>
                                                <span class="pt-2 status-text-d" id="ex_plan_mny"></span>
                                            </div>
                                        </td>
                                        <th>변제총액</th>
                                        <td>
                                            <div class="col-md-12 row pl-2">
                                                <input type="text" class="form-control form-control-sm col-md-6" name="" id=""  value="{{ Func::numberFormat(Func::nvl($loan['repay_total_money'],'')) ?? '0' }}" required>
                                                <span class="pt-2">&nbsp;원</span>
                                                <span class="pt-2 status-text-d" id="ex_plan_mny"></span>
                                            </div>
                                        </td>
                                    </tr> --}}

                                    <tr>
                                        <th>분납시작일자</th>
                                        <td>
                                            <div class="input-group date datetimepicker " id="div_settle_date" data-target-input="nearest">
                                                <input type="text" class="form-control form-control-sm" id="settle_date" name="settle_date" DateOnly='true'  value="{{ $loan['settle_date'] ?? date('Y-m-d') }}" />
                                                <div class="input-group-append" data-target="#div_settle_date" data-toggle="datetimepicker">
                                                    <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                                </div>
                                            </div>
                                        </td>
                                        <th><!--감면방식/-->약정일</th>
                                        <td class="p-0">
                                            <div class="col-md-12 row pl-2 pr-0">   
                                                <!--
                                                <select class="col-md-6 form-control form-control-sm" name="settle_lose_type" id="settle_lose_type">
                                                {{ Func::printOption(Vars::$arrayCcrsDiv,Func::nvl($loan['settle_lose_type'],'')) }} 
                                                </select>
                                                -->
                                                <input type="hidden" name="settle_lose_type" id="settle_lose_type" value="{{ Func::nvl($loan['settle_lose_type'],'B') }}">

                                                <select class="col-md-8 form-control form-control-sm" name="settle_contract_day" id="settle_contract_day">
                                                    <option value=''>약정일</option>
                                                    {{ Func::printOption($contract_day,Func::nvl($loan['settle_contract_day'],'')) }}
                                                </select>
                                            </div>
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>2회차입금일</th>
                                        <td>
                                            <div class="input-group date datetimepicker " id="div_second_return_date" data-target-input="nearest">
                                                <input type="text" class="form-control form-control-sm" id="second_return_date" name="second_return_date" DateOnly='true'  value="{{ $loan['second_return_date'] ?? '' }}" />
                                                <div class="input-group-append" data-target="#div_second_return_date" data-toggle="datetimepicker">
                                                    <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                                </div>
                                            </div>
                                        </td>
                                        <th colspan=2 class="bg-white text-left pl-3">미입력시 보정일수 없이 자동계산됩니다.</th>
                                        <!--
                                        <td class="p-0">
                                        </td>
                                        -->
                                    </tr>

                                    <tr>
                                        <th>화해조건분석</th>
                                        <td colspan=3>
                                                <table class="table table-bordered table-sm text-xs mb-0 settle_condition_table">
                                                    <colgroup>
                                                    <col width="20%"/>
                                                    <col width="20%"/>
                                                    <col width="20%"/>
                                                    <col width="20%"/>
                                                    <col width="20%"/>
                                                    </colgroup>
                                                    <tbody>
                                                        <tr>
                                                            <th class="text-center">구분</th>
                                                            <th class="text-center">원금</th>
                                                            <th class="text-center">이자</th>
                                                            <th class="text-center">비용</th>
                                                            <th class="text-center">합계</th>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">현재원리금</td>
                                                            <td><input type="text" name="app_origin" id="balance" readonly  class="text-right border-0 w-100 outline-none" value="{{ Func::numberFormat($loan['app_origin']) ?? '0' }}"></td>
                                                            <td><input type="text" name="app_interest" id="interest_sum" readonly  class="text-right border-0 w-100 outline-none" value="{{ Func::numberFormat($loan['app_interest']) ?? '0'}}"></td>
                                                            <td><input type="text" name="app_cost" id="cost_money" readonly  class="text-right border-0 w-100 outline-none" value="{{ Func::numberFormat($loan['app_cost']) ?? '0'}}"></td>
                                                            <td><input type="text" name="total_money" id="total_money" readonly  class="text-right border-0 w-100 outline-none" value=""></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">감면금액</td>
                                                            <td><input type="text" name="lose_origin" id="lose_origin" readonly  class="text-right border-0 w-100 outline-none"></td>
                                                            <td><input type="text" name="lose_interest" id="lose_interest" readonly  class="text-right border-0 w-100 outline-none"></td>
                                                            <td><input type="text" name="lose_pre_money" id="lose_pre_money" readonly  class="text-right border-0 w-100 outline-none"></td>
                                                            <td><input type="text" name="settle_lose_money" id="lose_sum" readonly  class="text-right border-0 w-100 outline-none"></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">초입금액</td>
                                                            <td><input type="text" name="return_origin"    id="return_origin" readonly  class="text-right border-0 w-100 outline-none"></td>
                                                            <td><input type="text" name="return_interest"  id="return_interest" readonly  class="text-right border-0 w-100 outline-none"></td>
                                                            <td><input type="text" name="return_pre_money" id="return_pre_money" readonly  class="text-right border-0 w-100 outline-none"></td>
                                                            <td><input type="text" name="return_sum"       id="return_sum" readonly  class="text-right border-0 w-100 outline-none"></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-center">분할입금액</td>
                                                            <td><input type="text" name="remain_origin" id="remain_origin" readonly  class="text-right border-0 w-100 outline-none"></td>
                                                            <td><input type="text" name="remain_interest" id="remain_interest" readonly  class="text-right border-0 w-100 outline-none"></td>
                                                            <td><input type="text" name="remain_pre_money" id="remain_pre_money" readonly  class="text-right border-0 w-100 outline-none"></td>
                                                            <td><input type="text" name="remain_sum" id="remain_sum" readonly  class="text-right border-0 w-100 outline-none"></td>
                                                        </tr>
                                                    </tbody>
                                                </table>
                                                <table class="table table-sm table-bordered table-input text-xs mb-0 settle_condition_table">
                                                    <colgroup>
                                                    <col width="20%"/>
                                                    <col width="20%"/>
                                                    <col width="20%"/>
                                                    <col width="20%"/>
                                                    <col width="20%"/>
                                                    </colgroup>
                                                    <tbody>
                                                        <tr>
                                                            <th class="text-center">실화해금액</th>
                                                            <td><input type="text" name="real_settle_money" id="real_settle_money" size=12 readonly class="text-right border-0 outline-none w-100"></th>
                                                            <th class="text-center">원금감면율 / 인가율</th>
                                                            <td><input type="text" name="lose_rate" id="lose_rate" size=12 readonly class="text-right text-danger  border-0 outline-none w-100"></th>
                                                            <td><input type="text" name="inga_rate" id="inga_rate" size=12 readonly class="text-right text-primary border-0 outline-none w-100"></th>
                                                        </tr>
                                                    </tbody>
                                                </table>

                                        </td>
                                    </tr>
                                    <tr>
                                        <th>신청메모</th>
                                        <td colspan=3><textarea class="form-control" rows="3" name="app_memo" id="app_memo" style="resize: none;">{{$loan['app_memo'] ?? ''}}</textarea></td>
                                    </tr>
                                    <tr>
                                        <th>승인메모</th>
                                        <td colspan=3><textarea class="form-control" rows="3" name="apr_memo" id="apr_memo" style="resize: none;">{{$loan['apr_memo'] ?? ''}}</textarea></td>
                                    </tr>
                                    <tr>
                                        <th>담당자메모</th>
                                        <td colspan=3><textarea class="form-control" rows="1" name="mng_memo" id="mng_memo" style="resize: none;">{{$loan['mng_memo'] ?? ''}}</textarea></td>
                                    </tr>
                                    <tr>
                                        <th>결재메모</th>
                                        <td colspan=3><textarea class="form-control" rows="3" name="cmp_memo" id="cmp_memo" style="resize: none;">{{$loan['cmp_memo'] ?? ''}}</textarea></td>
                                    </tr>
                                </tbody>
                            </table>
                            
                            <div class="col-md-10"><i class="fas fa-angle-double-right mt-2 mr-2"></i><b>처리로그</b></div>
                            <table class="table table-sm table-bordered table-input text-xs mb-0 settle_condition_table talbe-hover"> 
                                <colgroup>
                                <col width="20%"/>
                                <col width="20%"/>
                                <col width="60%"/>
                                </colgroup>
                                <thead>
                                <tr>
                                    <th>구분</th>
                                    <th>처리자</th>
                                    <th>시간</th>
                                </tr>
                                </thead>
                                <tbody>
                                    @if(isset($settle_log))
                                    @foreach ($settle_log as $stl )
                                    <tr>
                                        <td class="text-center">@if(isset($loan['sub_type']) && $loan['sub_type']!='3'){{ Vars::$arraySettleSta[$stl->status] ?? $stl->status }}
                                            @else {{ Vars::$arrayCcrsStatus[$stl->status] ?? $stl->status }}
                                            @endif
                                        </td>
                                        <td class="text-center">{{ $userList[$stl->save_id]->name ?? '' }}</td>
                                        <td class="text-center">{{ FUNC::dateFormat($stl->save_time) }}</td>
                                    </tr>
                                    @endforeach
                                    @endif
                                </tbody>
                            </table>
                           
                        </div>

                        <div class="col-md-6">
                            <div class="row">
                                <div class="col-md-10 pt-2">
                                    <b><i class="fas fa-list mr-2" ></i>분납정보</b> ( 신용회복을 제외한 화해는 입금예정액만 기재하세요. )
                                </div>
                                <div class="col-md-2 card-tools pr-2 text-right">
                                    <button type="button" class="btn btn-tool m-1" onclick="addCntDiv();" ><i class="fas fa-plus"></i></button>
                                </div>
                            </div>
                            <div class="col-md-12 p-0">
                                <!-- BODY -->

                                <table class="table table-sm text-xs card-outline settle_condition_table">
                                    <thead>
                                        <tr>
                                            <th width="15%" class="text-center">시작회차</th>
                                            <th width="25%" class="text-center">원금</th>
                                            <th width="25%" class="text-center">이자</th>
                                            <th width="25%" class="text-center">입금예정액</th>
                                            <th width="10%"></th>
                                        </tr>
                                    </thead>
                                    <tbody id="cnt_tb">
                                        @forelse($arr_cnt as $i => $cnt)
                                        <tr>
                                            <td><div class="col-md-12 row pl-2">
                                                <input type="text" class="form-control form-control-xs border-0 text-center col-6" id="start_cnt_{{ $i+1 }}" name="start_cnt[]" value="{{ $cnt->start_cnt ?? '0' }}" onkeyup="onlyNumber(this);" onblur="calCntMoney();" required> 
                                                <input type="text" class="form-control form-control-xs border-0 text-center col-6" value=" ~ {{ $cnt->end_cnt ?? '0' }}" readonly >  </div></td>
                                            <td><input type="text" class="form-control form-control-xs border-0 text-center" id="balance_cnt_{{ $i+1 }}" name="balance_cnt[]" value="{{ Func::numberFormat($cnt->balance) ?? '0' }}" onkeyup="onlyNumber(this);calCntMoney();inputComma(this);" required></td>
                                            <td><input type="text" class="form-control form-control-xs border-0 text-center" id="interest_cnt_{{ $i+1 }}"  name="interest_cnt[]" value="{{ Func::numberFormat($cnt->interest) ?? '0' }}" onkeyup="onlyNumber(this);calCntMoney();inputComma(this);" required></td>
                                            <td><input type="text" class="form-control form-control-xs border-0 text-center" id="trade_money_cnt_{{ $i+1 }}" name="trade_money_cnt[]" value="{{ Func::numberFormat($cnt->trade_money) ?? '0' }}" onkeyup="onlyNumber(this);calCntMoney();inputComma(this);" required></td>
                                            <td class="text-center" style="margin-top:6px;">
                                                <button type="button" class="btn btn-xs" onclick="delPlanCntRow(this);"><i class="fa fa-trash-alt text-secondary"></i></button>
                                            </td>
                                        </tr>   
                                        @empty
                                        @endforelse
                                    </tbody>
                                    <tfoot>
                                        <tr style="background-color: #e9ecef">
                                            <th class="text-center pt-1">합계</th>
                                            <th><input type="text" class="form-control form-control-xs border-0 text-center" name="total_blance_cnt" id="total_blance_cnt"  readonly></th>
                                            <th><input type="text" class="form-control form-control-xs border-0 text-center" name="total_interest_cnt" id="total_interest_cnt"  readonly></th>
                                            <th><input type="text" class="form-control form-control-xs border-0 text-center" name="total_trade_cnt" id="total_trade_cnt"  readonly></th>
                                            <th></th>
                                        </tr>
                                    </tfoot>
                                </table> 
                            </div>
                            
                                @if( Func::nvl($loan['status'],'') != 'Y' )
                                    <button type="button" class="btn btn-xs btn-info float-right mr-0"  onclick="getSettlePlanEx();">
                                    <i class="fa fa-table"></i> 스케줄미리보기</button>
                                    <div class="float-right pr-2"> * 정보 변동시 우선 등록 후 실행 </div>
                                @endif
                                @if(Func::nvl($loan['status'],'') == 'Y')
                                    <button type="button" class="btn btn-xs btn-secondary float-right mr-0"  onclick="getPopUp('/erp/settleplanform/{{ $settle_no ?? ''}}','settleplan','');">
                                    <i class="fa fa-edit"></i> 스케줄수정</button>
                                @endif
                                <div class="table-responsive p-0" id="settlePlanDiv" style="height:400px;">
                                <table class="table table-sm table-bordered text-xs mb-0 mt-1 settle_condition_table">
                                <thead>
                                    <tr>
                                        <th width="5%">회차</th>
                                        <th width="15%">입금약속일</th>
                                        <th width="12%">원금</th>
                                        <th width="12%">이자</th>
                                        <th width="15%">입금약속액</th>
                                        <th width="10%">처리일</th>
                                        <th width="12%">처리금액</th>
                                    </tr>
                                </thead>
                                <tbody id="plan_tb">
                                    @if(isset($plan_data))
                                    @forelse ($plan_data as $plan)
                                        <tr @if($plan->status=='N') bgcolor=grey @endif>
                                            <td class="text-center">{{$plan->seq ?? ''}}</td>
                                            <td class="text-center">{{ Func::dateFormat($plan->plan_date) ?? '' }}</td>
                                            <td class="text-center">{{ number_format($plan->plan_origin) ?? 0 }}</td>
                                            <td class="text-center">{{ number_format($plan->plan_interest) ?? 0 }}</td>
                                            <td class="text-center">{{ number_format($plan->plan_money) ?? 0 }}</td>
                                            <td class="text-center">{{ Func::dateFormat($plan->trade_date) ?? ''}}</td>
                                            <td class="text-center">{{ number_format($plan->trade_money) ?? ''}}</td>
                                        </tr>
                                    @empty
                                    @endforelse
                                    @endif
                                    @if(isset($plan_sum))
                                    <tfoot>
                                    <tr>
                                        <th colspan='2'>스케줄 합계</th>
                                        <th class="font-weight-bold text-xs">{{ number_format($plan_sum['plan_origin']) ?? ''}} </th>
                                        <th class="font-weight-bold">{{ number_format($plan_sum['plan_interest']) ?? ''}}</th>
                                        <th class="font-weight-bold">{{ number_format($plan_sum['plan_money']) ?? ''}}</th>
                                        <th></th>
                                        <th class="font-weight-bold">{{ number_format($plan_sum['trade_money']) ?? ''}}</th>
                                    </tr>
                                    </tfoot>
                                    @endif
                                    
                                </tbody>
                                </table>
                                </div>
                            
                        </div>
                    <div>
                </div>
            </div>
        </div>
        {{-- 입력정보 구분 끝 --}}
    </div>

    {{-- <div class="card card-outline card-secondary @if(isset($loan['sub_type']) &&  $loan['sub_type'] != 1) d-none @endif"> --}}
    <div class="card card-outline card-secondary">
        <div class="card-body p-2">
            <div id="con_id_area">
                <table class="table table-sm table-bordered table-input">
                @if(isset($loan['option_str']))
                    @foreach($loan['option_str'] as $col => $option_str)
                    <tr>
                        <th class="w-10"> {{ $loan['confirm_str'][$col] }}</th>
                        <td class="w-10 ">
                            <select class="form-control form-control-sm mr-2 con-id-sel" name="{{ $col }}" id="{{ $col }}" @if(!empty($loan['confirm_date_'.substr($col,-1,1)]) || $loan['status'] == "Y") disabled @endif onchange="setConfirmMemo(this.value,'{{ substr($col,-1,1) }}')">{!! $option_str !!}</select>
                        </td>
                        <th class="w-10"> {{ $loan['confirm_str'][$col]." 의견" }}</th>
                        <td class="w-70"><textarea class="form-control form-control-sm" rows="1" name="{{ 'confirm_memo_'.substr($col,-1,1) }}" @if($loan['confirm_id_'.substr($col,-1,1)] !=Auth::id()  || $loan['status'] == "Y") readonly @endif>{{ $loan['confirm_memo_'.substr($col,-1,1)] }}</textarea></td>
                    </tr>
                    @endforeach
                @endif
                </table>
            </div>
            <input type="hidden" name="confirm_level" id="confirm_level" value="{{ isset($loan['option_str'])?sizeof($loan['option_str']):0 }}">
        </div>
    </div>

    
    <div class="row pt-1 justify-content-center">
        @if(Func::nvl($loan['save_status'],'Y')!='N')
            <input type="hidden" name="oldStatus" id="oldStatus" value="{{ Func::nvl($loan['status'],'') ?? ''}}">
            <select class="form-control form-control-sm col-md-2 mr-2" name="status" id="status">
                @if(isset($loan['status'])) 
                @php
                    $save_yn = "Y";
                @endphp
                <option value="{{ Func::nvl($loan['status'],'') ?? '' }}">화해 {{ Vars::$arraySettleSta[Func::nvl($loan['status'],'')] ?? '상태'}} 저장</option>
                @endif
                @if(isset($arraySta))
                @foreach($arraySta as $sta => $str)
                <option value='{{$sta}}' @if($sta!='X' && $sta!='DEL') selected  @elseif($sta==Func::nvl($loan['status'],'')) selected @endif>@if($sta!='N') 화해 @endif {{ $str }} </option>
                @endforeach
                @endif
            </select>
            @if(isset($arraySta)|| (isset($save_yn) && $save_yn =="Y"))
            <button type="button" class="btn btn-sm bg-lightblue" onclick="settleSearch();">등록</button>
            @endif
        @endif
    </div>

<input type="hidden" name="direct_div" value="{{ $direct_div ?? '' }}">
</form>
<br>
@endsection



@section('javascript')
 {{--분납정보--}}
 <script id="cnt_tmpl" type="text/tmpl">
    <tr>
        <td><input type="text" class="form-control form-control-xs border-0 text-center " id="start_cnt_${cnt}" name="start_cnt[]" value="${cnt}" onkeyup="onlyNumber(this);calCntMoney();" ></td>
        <td><input type="text" class="form-control form-control-xs border-0 text-center " id="balance_cnt_${cnt}" name="balance_cnt[]" value="" onkeyup="onlyNumber(this);inputComma(this);calCntMoney();" ></td>
        <td><input type="text" class="form-control form-control-xs border-0 text-center " id="interest_cnt_${cnt}" name="interest_cnt[]" value="" onkeyup="onlyNumber(this);calCntMoney();inputComma(this);" ></td>
        <td><input type="text" class="form-control form-control-xs border-0 text-center " id="trade_money_cnt_${cnt}" name="trade_money_cnt[]"value="" onkeyup="onlyNumber(this);calCntMoney();inputComma(this);" ></td>
        <td class="text-center" style="margin-top:6px;">
            <button type="button" class="btn btn-xs" onclick="delPlanCntRow(this);"><i class="fa fa-trash-alt text-secondary"></i></button>
        </td>
    </tr> 
 </script>
 <script id="plan_tmpl" type="text/tmpl">
    <tr>
        <td class="text-center">${seq}</td>
        <td class="text-center">${plan_date}<!-- (${plan_date_biz}) --></td>
        <td class="text-center">${plan_origin}</td>
        <td class="text-center">${plan_interest}</td>
        <td class="text-center">${plan_money}</td>
        <td class="text-center"></td>
        <td class="text-center"></td>
    </tr>
 </script>
<script>

setSettleForm();

var cnt = {{ count($arr_cnt) ?? 1}};
// 로드시 스크롤위치 조정
$(document).ready(function(){
    
    $(".datetimepicker").datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
        useCurrent: false,
    });

    window.resizeTo(1500, 1000 );
    $(window).scrollTop(0);
    sumSchedule('N');
    if($('#settle_no').val()!=''&&$('#settle_no').val()>0){
        if($('#status').val()!='N'|| filterNum($("#total_money").val())>0) CalReturnMoney();
        calCntMoney();
    }
    getSubInfo();
    if(cnt<=0){
        addCntDiv();
    }

    @if( Func::nvl($loan['status'],'') != 'Y' && Func::nvl($loan['status'],'') != '' )
    getSettlePlanEx();
    @endif

    // sub_type 별 결재담당자 지정
    if($('#sub_type').val() == '1')
    {
        if($('#settle_no').val()=='')
        {
            setConId('01');
        }
    }
    if($('#sub_type').val() == '2')
    {
        setConId('05');
    }
    if($('#sub_type').val() == '3')
    {
        setConId('06');
    }
});


function delPlanCntRow(obj)
{
    obj.parentNode.parentNode.remove();
    calCntMoney();
}

// 대출금셋팅
function CalReturnMoney()
{
    var trade_money = filterNum($("#settle_in_money").val()) * 1;
    var settle_money = filterNum($("#settle_money").val()) * 1;
    var lose_origin = 0;
    var lose_interest = 0;
    var lose_pre_money = 0;
    var return_origin = 0;
    var return_interest = 0;
    var return_pre_money = 0;
    var remain_origin = filterNum($("#balance").val()) * 1;
    var now_balance = remain_origin;
    var remain_interest = filterNum($("#interest_sum").val()) * 1;
    var remain_pre_money = filterNum($("#cost_money").val()) * 1;

    var total_money = remain_origin+remain_interest+remain_pre_money;
    $("#total_money").val(commaSplitAndNumberOnly(total_money));
    $("#before_money").val($("#total_money").val());
    var real_settle_money = trade_money + settle_money;			// 실화해금액

    if( trade_money>total_money)
    {
        alert("초입금액이 원리금의 합계보다 커서 초입금액을 재설정 합니다.");
        $("#settle_in_money").val(0);
    }

    // 감면금액을 구한다.
    var lose_money = total_money - ( settle_money + trade_money );
    if(lose_money<0)
    {
        lose_money = 0;
    }

    // 감면순서 - 이자, 비용, 원금
    if(lose_money>0)
    {
        var diff = lose_money-remain_interest;
        lose_interest = diff>0? remain_interest : lose_money;
        remain_interest = diff>0? 0 : (diff*-1);
        lose_money = diff>0? diff : 0;
        // console.log("diff-이자:"+diff+"lose_interest:"+lose_interest+"remain_interest:"+remain_interest);

        diff = lose_money-remain_pre_money;
        lose_pre_money = diff>0? remain_pre_money : lose_money;
        remain_pre_money = diff>0? 0 : (diff*-1);
        lose_money = diff>0? diff : 0;
        // console.log("diff-비용:"+diff+"lose_pre_money:"+lose_pre_money+"remain_pre_money:"+remain_pre_money);

        diff = lose_money-remain_origin;
        lose_origin = diff>0? remain_origin : lose_money;
        remain_origin = diff>0? 0 : (diff*-1);
        lose_money = diff>0? diff : 0;
        // console.log("diff-원금:"+diff+"lose_origin:"+lose_origin+"remain_origin:"+remain_origin);
    }
    // 상환순서 - 비용, 이자, 원금
    if(trade_money>0)
    {
        diff = trade_money-remain_pre_money;
        return_pre_money = diff>0? remain_pre_money : trade_money;
        remain_pre_money = diff>0? 0 : (diff*-1);
        trade_money = diff>0? diff : 0;

        diff = trade_money-remain_interest;
        return_interest = diff>0? remain_interest : trade_money;
        remain_interest = diff>0? 0 : (diff*-1);
        trade_money = diff>0? diff : 0;

        diff = trade_money-remain_origin;
        return_origin = diff>0? remain_origin : trade_money;
        remain_origin = diff>0? 0 : (diff*-1);
        trade_money = diff>0? diff : 0;
    }

    // 감면과 상환이 끝난후 잔여원리금을 잔여화해금액과 비교 화해금액이 더 크면 화해이자를 추가한다.
    var add_money = settle_money - ( remain_origin+remain_interest+remain_pre_money) ;
    if( add_money>0 )
    {
        var pre_lose_origin = filterNum($("#pre_lose_origin").val()) * 1;

        if( pre_lose_origin>0 )
        {
            if( pre_lose_origin>add_money )
            {
                remain_origin+= add_money;
            }
            else
            {
                remain_origin+= pre_lose_origin;
                remain_interest+= ( add_money - pre_lose_origin ) ;
            }
        }
        else
        {
            remain_interest+= add_money;
        }
    }

    $("#lose_origin").val(commaSplitAndNumberOnly(lose_origin));
    $("#lose_interest").val(commaSplitAndNumberOnly(lose_interest));
    $("#lose_pre_money").val(commaSplitAndNumberOnly(lose_pre_money));
    $("#lose_sum").val(commaSplitAndNumberOnly((lose_origin+lose_interest+lose_pre_money)));
    $("#after_lose_money").val($("#lose_sum").val());
    
    $("#return_origin").val(commaSplitAndNumberOnly(return_origin));
    $("#return_interest").val(commaSplitAndNumberOnly(return_interest));
    $("#return_pre_money").val(commaSplitAndNumberOnly(return_pre_money));
    $("#return_sum").val(commaSplitAndNumberOnly((return_origin+return_interest+return_pre_money)));

    $("#remain_origin").val(commaSplitAndNumberOnly(remain_origin));
    $("#remain_interest").val(commaSplitAndNumberOnly(remain_interest));
    $("#remain_pre_money").val(commaSplitAndNumberOnly(remain_pre_money));
    $("#remain_sum").val(commaSplitAndNumberOnly((remain_origin+remain_interest+remain_pre_money)));

    $("#real_settle_money").val(commaSplitAndNumberOnly(real_settle_money));

    // 현재원금기준으로 계산하게 변경.    
    //var lose_rate = (total_money>0) ? Math.round((lose_origin+lose_interest+lose_pre_money)/total_money*10000)/100 : "-";
    var lose_rate = (now_balance>0) ? Math.round(lose_origin/now_balance*10000)/100 : "-";
    $("#lose_rate").val(lose_rate+"%");

    //var inga_rate = (total_money>0) ? Math.round((settle_money+trade_money)/total_money*10000)/100 : "-";
    var inga_rate = (now_balance>0) ? Math.round((settle_money+trade_money)/now_balance*10000)/100 : "-";
    console.log(settle_money+trade_money);
    console.log(now_balance);
    $("#inga_rate").val(inga_rate+"%");
    
}

// $ 와 콤마 제거 함수
function inputComma(obj){
    obj.value   = commaSplitAndNumberOnly(filterNum(obj.value));
}

function filterNum(str)
{
    str = String(str);
	re = /^\$|,/g;
	return str.replace(re, "");
}

function commaSplitAndNumberOnly(str)
{
    
    str = String(str*1);
    return  str.replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
}

// 화해내역검색
function settleSearch() 
{
    if( $('#oldStatus').val()!='Y' && $('#status').val()=='Y' )
    {
        console.log('test');
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        $.ajax({
            url  : "/erp/settlesearch",
            type : "get",
            data : {
                loan_info_no : $('#loan_info_no').val()
            },
            dataType: "json",
            success : function(data)
            {
                if(data.result=="Y"){
                    if(confirm(" 완료되어 진행중인 "+data.v.title+"["+data.v.no+"]"+"번을 중단시키고 새로 진행하시겠습니까?")){
                        settleAction();
                    }
                }else{
                    settleAction();
                }
            },
            error : function(xhr)
            {
                console.log(xhr);
                console.log("fail!!");
            }
        });
    }else{
        console.log('test2');
        settleAction();
    }
}

function setExpectSettle(){
    if( $("#settle_money").val() != '' && $('#settle_cnt').val() != ''){
        var settle_money = Number(filterNum($('#settle_money').val())); 
        var settle_cnt = Number(filterNum($('#settle_cnt').val())); 
        console.log("settle_money/settle_cnnt : "+ settle_money+"/"+settle_cnt);
        if(settle_money>0&&settle_cnt>0){
            var str = Math.round(settle_money/settle_cnt);
            $("#ex_plan_mny").text("(예상)회당 "+commaSplitAndNumberOnly(str)+"원");
        }
    }
}

//스케줄합계 구하기
function sumSchedule(seq){
    if(seq!='N'){
        var plan_mny = (filterNum($('#plan_origin_'+seq).val())*1)+(filterNum($('#plan_interest_'+seq).val())*1);
        $('#plan_money_'+seq).val(commaSplitAndNumberOnly(plan_mny));
    }
    var total = 0;
    $('.plan_origin').each(function(){ //클래스가 money인 항목의 갯수만큼 진행
		total += Number(filterNum($(this).val())); 
	});
    $('#plan_total_origin').val(commaSplitAndNumberOnly(total));
    total = 0;
    $('.plan_interest').each(function(){ //클래스가 money인 항목의 갯수만큼 진행
		total += Number(filterNum($(this).val())); 
	});
    $('#plan_total_int').val(commaSplitAndNumberOnly(total));
    total = 0;
    $('.plan_money').each(function(){ //클래스가 money인 항목의 갯수만큼 진행
		total += Number(filterNum($(this).val())); 
	});
    $('#plan_total_mny').val(commaSplitAndNumberOnly(total));
}

// 화해ACTION
function settleAction() 
{
    var status = $('#status').val();
    if(isEmpty(status)){
        alert("화해 상태를 선택해주세요.");
        return false;
    }
    if( status=='DEL' )
    {
        if( !confirm('화해 취소 하시면 초입금일에 처리된 감면입금분도 함께 취소됩니다. 취소 진행하시겠습니까?') )
        {
            return false;
        }
        var url = "/erp/settledel";        
    }
    else
    {
        if( $('#oldStatus').val()!='Y' && status!='X' )
        {
            getLoanInterest();
            if(isEmpty($('#sub_type').val()))
            {
                alert("화해 구분을 선택해주세요.");
                return false;
            }
            if(isEmpty($('#sub_type_cd').val()))
            {
                alert("화해 상세구분을 선택해주세요.");
                return false;
            }
            if(isEmpty($('#settle_reason_cd').val()))
            {
                alert("화해사유를 선택해주세요.");
                $('#settle_reason_cd').focus();
                return false;
            }
            if($('#settle_money').val()==''|| filterNum($('#settle_money').val())<=0)
            {
                alert("잔여화해금액을 입력해주세요.");
                $('#settle_money').focus();
                return false;
            }
            if( $('#total_trade_cnt').val()==''||filterNum($('#total_trade_cnt').val())<=0) 
            {
                alert("스케줄 금액을 확인해주세요");
                return false;
            }
            sumSchedule(1);
            if($('#settle_money').val() != $('#total_trade_cnt').val())
            {
                alert("화해금액과 분납 합계금액이 일치하지 않습니다.");
                return false;
            }
            // 일반화해시 결재단계에 맞게 결재자세팅 확인
            if($('#sub_type').val()=="1")
            {
                if( status=='A' && !$('#confirm_id_1').val())
                {
                    alert("1차결재자를 지정해주세요.");
                    return false;
                }
                if( status=='B'&& !$('#confirm_id_2').val())
                {
                    var confirm_id_str = "2차결재자";
                    if($('#confirm_level').val() == 2) // confirm_level 이 2인경우 2차결재자가 최종결재자임
                    {
                        confirm_id_str = "최종결재자";
                    }
                    alert(confirm_id_str+"를 지정해주세요.");
                    return false;
                }
                if( status=='C'&& !$('#confirm_id_3').val())
                {
                    alert("최종결재자를 지정해주세요.");
                    return false;
                }
            }
            //신용회복 조건
            if($('#sub_type').val()=="3")
            {
                if(isEmpty($('#ccrs_account').val())||isEmpty($('#ccrs_app_no').val())){
                    alert("신용회복 계좌번호와 접수번호를 입력해주세요.");
                    return false;
                }
                if(status=='C' && !confirm("조정시 새로운 스케줄이 시작됩니다. 진행하시겠습니까?")) return false;
            }
        }
        var url = "/erp/settleaction";
    }
    
    // 중복 클릭 방지
	if(ccCheck()) return;  

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var postdata = $('#settle_form').serialize();
    $.ajax({
        url  : url,
        type : "post",
        data : postdata,
        dataType: "json",
        success : function(data)
        {
            alert(data.rs_msg);
            if( status != 'DEL' && !isEmpty(data.settle_no) ){
                var url = "/erp/settleform/"+data.settle_no+"?loan_info_no={{ $loanInfoNo ?? '' }}&sub_type="+$('#sub_type').val();
                console.log(url);
                location.href=url;
                window.opener.location.reload();
            }else{
            }
            globalCheck = false;
        },
        error : function(xhr)
        {
            console.log(xhr);
            globalCheck = false;
        }
    });
}

//분납정보추가
function addCntDiv(){
    cnt = cnt+1;
    $("#cnt_tmpl").template("cnt_tmpl");
    $.tmpl("cnt_tmpl",cnt).appendTo("#cnt_tb");   
}

//분납정보 금액 계산
function calCntMoney(){

    var total_balance  = 0;
    var total_interest = 0;
    var total_trade    = 0;
    var settle_end = $('#settle_cnt').val()*1;
    if(settle_end==''||settle_end<1) return;
    var arr_cnt = [];
    for(var i =1; i<=cnt; i++)
    {
        if(!isEmpty($('#start_cnt_'+i).val())){
            // console.log(i+$('#start_cnt_'+i).val()+"<="+$('#start_cnt_'+(i-1)).val());
            if(i>2 && ($('#start_cnt_'+i).val()*1) <= ($('#start_cnt_'+(i-1)).val()*1) ){
                // alert("이전회차보다 큰 숫자로 입력해주세요.");
                // $('#start_cnt_'+i).focus();
                return false;
            }
            if($('#start_cnt_'+i).val()>0) arr_cnt[$('#start_cnt_'+i).val()] = filterNum($('#trade_money_cnt_'+i).val());
        }
    }
    var mny = 0;
    var target = 0;
    for( var i=1 ; i<=settle_end; i++ )
    {
        if( !isEmpty(arr_cnt[i]) )
        {
            mny    = arr_cnt[i] * 1;
            target = i;
        } 
        
        if( mny>=0 )
        {
            if(!isEmpty($('#balance_cnt_'+target).val())) total_balance   += (filterNum($('#balance_cnt_'+target).val())*1);
            if(!isEmpty($('#interest_cnt_'+target).val())) total_interest += (filterNum($('#interest_cnt_'+target).val())*1);
            total_trade += mny;
        }
        // console.log(mny+":"+total_trade+"<<"+i);
    }
    //total_trade = total_balance +
    $('#total_blance_cnt').val(commaSplitAndNumberOnly(total_balance));
    $('#total_interest_cnt').val(commaSplitAndNumberOnly(total_interest));
    $('#total_trade_cnt').val(commaSplitAndNumberOnly(total_trade));

}

//화해 구분에 따른 화면
function getSubInfo()
{
    var settle_no = $('#settle_no').val();
    var sub_type  = $('#sub_type').val();
    if(sub_type=='2')  var url = 'irl';
    else if(sub_type=='3') var url = 'ccrs';
    else{
        $("#settle-sub").empty();  
        return ;
    } 
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.post("/erp/"+url+"form", { settle_no:settle_no }, function(data) {
            $("#settle-sub").html(data);
        });
}
    
// 이자조회
function getLoanInterest(div)
{
    // CORS 예외처리
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var today = $('#settle_trade_date').val();
    var loanNo = $('#loan_info_no').val();
    $.ajax({
        url  : "/erp/loaninterest",
        type : "post",
        data : { no:loanNo, today:today ,returnType:'json', days:1 },
        dataType: "json",
        success : function(data)
        {
            today = today.split("-").join("");
            // console.log(today+"<"+data.simple['last_trade_date']);
            if(today<data.simple['last_trade_date'] && $('#status').val()!='N'){
                alert("초입금일 이전에 거래내역이 있습니다. 확인해주세요.");
            }else{
                $('#balance').val(commaSplitAndNumberOnly(data.result[today]['balance']));
                $('#interest_sum').val(commaSplitAndNumberOnly(data.result[today]['interest_sum']));
                $('#cost_money').val(commaSplitAndNumberOnly(data.result[today]['cost_money']));
                CalReturnMoney();
                if(div=="C") alert("이자가 계산되어 아래표에 적용되었습니다.");
            }
        },
        error : function(xhr)
        {
            console.log(xhr);
        }
    });
}

//스케줄 미리보기
function getSettlePlanEx()
{
    if($('#settle_money').val()==''|| filterNum($('#settle_money').val())<=0)
    {
        alert("잔여화해금액을 입력해주세요.");
        $('#settle_money').focus();
        return false;
    }
    if( $('#total_trade_cnt').val()==''||filterNum($('#total_trade_cnt').val())<=0) 
    {
        console.log($('#total_trade_cnt').val());
        alert("스케줄 금액을 확인해주세요");
        return false;
    }
    sumSchedule(1);
    if($('#settle_money').val() != $('#total_trade_cnt').val())
    {
        alert("화해금액과 분납 합계금액이 일치하지 않습니다.");
        return false;
    }
    // CORS 예외처리
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $('#plan_tb').html("<tr><td colspan=10>"+loadingString+"</td></tr>");
    var postdata = $('#settle_form').serialize();
    $.ajax({
        url  : "/erp/settleplanexpl",
        type : "post",
        data : postdata,
        dataType: "json",
        success : function(data)
        {
            if(data.rs_code =='Y'){
                $('#plan_tb').empty();
                $("#plan_tmpl").template("plan_tmpl");
                $.tmpl("plan_tmpl",data.rs_data).appendTo("#plan_tb");     
            }else{
                alert(data.rs_msg);
            }
        },
        error : function(xhr)
        {
            console.log(xhr);
        }
    });
}

function setConId(subType)
{
    /*if($('#sub_type').val() != '1')
    {
        return;
    }*/

    var arrConId = @json($arr_confirm_id);
    var len = Object.keys(arrConId[subType]).length;
    var i = 0;
    var option = "";
    var tr_str = "";
    $('#con_id_area').empty('');
    $('#confirm_level').val(len-1);

    $.each(arrConId[subType], function (conLevel,obj) {
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

        var selected_id= "";

        var option = "<select class='form-control form-control-sm mr-2 con-id-sel' name='"+conLevel+"' id='"+conLevel+"' onchange='setConfirmMemo(this.value,"+l+");'>";
        if(typeof obj != 'string')
        {
            option += "<option value=''>"+col_str+"</option>";

            $.each(obj, function (id, name) {

                if(name)
                {
                    var selected = "";
                    if(selected_id == id)
                    {
                        selected = "selected";
                    }
                    option += "<option value='"+id+"' "+selected+">"+name+"</option>";
                }
                
            });

        }
        option += "</select>";

        tr_str += "<tr class='col-md-12'><th class='col-md-1'>"+col_str+"</th><td class='col-md-1'>"+option+"</td>";
        tr_str += "<th class='col-md-1'>"+col_str+" 의견</th><td class='col-md-6'><textarea class='form-control form-control-sm' rows='1' readonly  name='confirm_memo_"+l+"'></textarea></td></tr>";
    });

    $("#con_id_area").append("<table class='table table-sm table-bordered table-input'>"+tr_str+"</table>");
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

function setSettleReason(cd)
{
    var sub_type = $('#sub_type').val();
    if(sub_type == '1' && cd)
    {
        if(cd == '04')
        {
            $('#settle_reason_cd').val('28');
        }
        if(cd == '03')
        {
            $('#settle_reason_cd').val('18');
        }
    }
}

function setSettleForm(cd)
{
    if(typeof cd == 'undefined' || cd == null || cd == '')
    {
        cd = @json($loan['sub_type_cd'] ?? '');
    }
    
    var irl = document.getElementById("irl");
    var ccrs = document.getElementById("ccrs");

    console.log(cd);

    if(cd == '03')
    {
        irl.style.display = "none";
        ccrs.style.display = "block";
    }
    else if(cd == '04')
    {
        irl.style.display = "none";
        ccrs.style.display = "block";
    }
    else
    {
        irl.style.display = "none";
        ccrs.style.display = "none";
    }
}

</script>
@endsection