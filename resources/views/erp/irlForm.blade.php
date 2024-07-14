<div class="card card-outline card-info">
    <div class="card-header p-1">
        <h3 class="card-title"><i class="fas fa-university m-2"></i>개인회생</h3>
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
                <th class="status-bg-n">법원</th>
                <td>
                    <div class="col-md-12">
                        <select class="form-control form-control-sm " data-size="10" data-live-search="true" name="law_justice" id="law_justice" title="선택">
                        {{ Func::printOption($law_justice,($irl['law_justice'] ?? '')) }}   
                        </select>
                    </div>
                </td>
                <th class="status-bg-n">사건번호</th>
                <td>
                    <div class="input-group  col-md-12" id="law_event_no" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm -input " name="law_event_no" id="law_event_no" value="{{ $irl['law_event_no'] ?? '' }}"  size="6" required>
                        <div class="input-group-append" data-target="#law_event_no">
                            {{-- <div class="input-group-text"  onclick="window.open('https://safind.scourt.go.kr/sf/mysafind.jsp','', 'width=1000, height=1000, scrollbars = yes, top=10, left=10');" style="cursor: pointer;"><i class="fa fa-search"></i></div> --}}
                        </div>
                    </div>
                </td>
                <th class="status-bg-n">당사자명</th>
                <td>
                    <div class="col-md-12">
                        <input type="text" class="form-control form-control-sm " name="law_name" id="law_name" value="{{ $irl['law_name'] ?? '' }}">
                    </div>
                </td>
                <th class="status-bg-d">
                    <span class="text-danger font-weight-bold h6 mr-1">*</span>
                    접수일
                </th>
                <td>
                    <div class="input-group date datetimepicker col-md-11" id="div_irl_reg_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="irl_reg_date" name="irl_reg_date" DateOnly='true'  value="{{ Func::dateFormat(Func::nvl($irl['irl_reg_date'],'')) ?? '' }}" />
                        <div class="input-group-append" data-target="#div_irl_reg_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <th class="status-bg-d">명의변경신고일</th>
                <td>
                    <div class="input-group date datetimepicker col-md-11" id="div_name_change_dt" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="name_change_date" name="name_change_date" DateOnly='true'  value="{{ Func::dateFormat(Func::nvl($irl['name_change_date'],'')) ?? '' }}" />
                        <div class="input-group-append" data-target="#div_name_change_dt" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
            </tr>  

            <tr>
                <th class="status-bg-n">변제채권번호</th>
                <td>
                    <div class="col-md-12">
                        <input type="text" class="form-control form-control-sm " name="payment_bond_no" id="payment_bond_no" value="{{ $irl['payment_bond_no'] ?? '' }}">
                    </div>                                    
                </td>
                <th class="status-bg-n">분납회생코드</th>
                <td>
                    <div class="col-md-12">
                        <select name="save_code" id="save_code" class="form-control form-control-sm">
                        <option value=''>분납회생코드</option>
                        {{ Func::printOption($save_code, Func::nvl($irl['save_code'],'')) }} 
                        </select>
                    </div>
                </td>
                <th class="status-bg-n">변제현황일치여부</th>
                <td>
                    <div class="col-md-12">
                        <select class="form-control form-control-sm " name="repay_flag" id="repay_flag">
                        <option value=''>여부</option>
                        {{ Func::printOption(Vars::$arrayRepayFlag,Func::nvl($irl['repay_flag'],'')) }} 
                        </select>
                    </div>
                </td>
                <th class="status-bg-d">
                    <span class="text-danger font-weight-bold h6 mr-1">*</span>
                    개시결정일
                </th>
                <td>
                    <div class="input-group date datetimepicker col-md-11" id="div_irl_start_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="irl_start_date" name="irl_start_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($irl['irl_start_date'],'')) ?? ''}}"/>
                        <div class="input-group-append" data-target="#div_irl_start_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <th class="status-bg-d">계좌변경신고일</th>
                <td>
                    <div class="input-group date datetimepicker col-md-11" id="div_report_dt" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="report_date" name="report_date" DateOnly='true'  value="{{ $irl['report_date'] ?? '' }}" />
                        <div class="input-group-append" data-target="#div_report_dt" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <th class="status-bg-n">입금은행</th>
                <td>
                    <div class="col-md-12">
                        <select name="deposit_bank" id="deposit_bank" class="form-control form-control-sm">
                        <option value=''>은행코드</option>
                        {{ Func::printOption($bank_cd,Func::nvl($irl['deposit_bank'],'')) }} 
                        </select>
                    </div>
                </td>
                <th class="status-bg-n">입금계좌</th>
                <td>
                    <div class="col-md-12">
                        <input type="text" class="form-control form-control-sm" name="deposit_account" id="deposit_account" value="{{ Func::nvl($irl['deposit_account'],'') }}">
                    </div>
                </td>
                <th class="status-bg-n">입금채권자</th>
                <td>
                    <div class="col-md-12">
                        <input type="text" class="form-control form-control-sm" name="deposit_bond" id="deposit_bond" value="{{ Func::nvl($irl['deposit_bond'],'') }}">
                    </div>
                </td>
                <th class="status-bg-d">
                    <span class="text-danger font-weight-bold h6 mr-1">*</span>
                    인가일
                </th>
                <td>
                    <div class="input-group date datetimepicker col-md-11" id="div_irl_auth_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="irl_auth_date" name="irl_auth_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($irl['irl_auth_date'],'')) ?? ''}}"/>
                        <div class="input-group-append" data-target="#div_irl_auth_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <th class="status-bg-d">공탁금입금요청발송일</th>
                <td>
                    <div class="input-group date datetimepicker col-md-11" id="div_deposit_in_dt" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="deposit_in_date" name="deposit_in_date" DateOnly='true'  value="{{ $irl['deposit_in_date'] ?? '' }}" />
                        <div class="input-group-append" data-target="#div_deposit_in_dt" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="status-bg-n">신고은행</th>
                <td>
                    <div class="col-md-12">
                        <select name="report_bank" id="report_bank" class="form-control form-control-sm">
                        <option value=''>은행코드</option>
                        {{ Func::printOption($bank_cd,Func::nvl($irl['report_bank'],'')) }} 
                        </select>
                    </div>                                        
                </td> 
                <th class="status-bg-n">신고계좌</th>
                <td>
                    <div class="col-md-12">
                        <input type="text" class="form-control form-control-sm " name="report_account" id="report_account" value="{{ $irl['report_account'] ?? '' }}">
                    </div>    
                </td>
                <th class="status-bg-n">신고채권자</th>
                <td>
                    <div class="col-md-12">
                        <input type="text" class="form-control form-control-sm " name="report_bond" id="report_bond" value="{{ $irl['report_bond'] ?? '' }}">
                    </div>      
                </td>
                <th class="status-bg-d">
                    <span class="text-danger font-weight-bold h6 mr-1">*</span>
                    폐지결정일
                </th>
                <td>
                    <div class="input-group date datetimepicker col-md-11" id="div_irl_revoke_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="irl_revoke_date" name="irl_revoke_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($irl['irl_revoke_date'],'')) ?? ''}}"/>
                        <div class="input-group-append" data-target="#div_irl_revoke_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <th class="status-bg-d">기타채권신고일</th>
                <td>
                    <div class="row" style="margin-left:1px;">
                        <div class="input-group date datetimepicker col-md-6" id="div_etc_notify_dt" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm" id="etc_notify_date" name="etc_notify_date" DateOnly='true'  value="{{ $irl['etc_notify_date'] ?? '' }}" />
                            <div class="input-group-append" data-target="#div_etc_notify_dt" data-toggle="datetimepicker">
                                <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <select name="report_type" id="report_type" class="form-control form-control-sm">
                            <option value=''>신고종류</option>
                            {{ Func::printOption($report_type, Func::nvl($irl['report_type'],'')) }} 
                            </select>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <th class="status-bg-n">가상계좌은행</th>
                <td>
                    <div class="col-md-12">
                        <select name="virtual_bank" id="virtual_bank" class="form-control form-control-sm " >
                        <option value=''>은행코드</option>
                        {{ Func::printOption($bank_cd,Func::nvl($irl['virtual_bank'],'')) }} 
                        </select>
                    </div>
                </td>
                <th class="status-bg-n">가상계좌</th>
                <td>
                    <div class="col-md-12">
                        <input type="text" class="form-control form-control-sm" name="virtual_account" id="virtual_account" value="{{ Func::nvl($irl['virtual_account'],'') }}">
                    </div>
                </td>
                <th class="status-bg-n">가상계좌예금자명</th>
                <td>
                    <div class="col-md-12">
                        <input type="text" class="form-control form-control-sm" name="virtual_bond" id="virtual_bond" value="{{ Func::nvl($irl['virtual_bond'],'') }}">
                    </div>
                </td>
                <th class="status-bg-d">
                    <span class="text-danger font-weight-bold h6 mr-1">*</span>
                    면책결정일
                </th>
                <td>
                    <div class="input-group date datetimepicker col-md-11" id="div_exemption_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="exemption_date" name="exemption_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($irl['exemption_date'],'')) ?? ''}}"/>
                        <div class="input-group-append" data-target="#div_exemption_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <th class="status-bg-d">이의신청발송일</th>
                <td>
                    <div class="row" style="margin-left:1px;">
                        <div class="input-group date datetimepicker col-md-6" id="div_appeal_send_dt" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm" id="appeal_send_date" name="appeal_send_date" DateOnly='true'  value="{{ $irl['appeal_send_date'] ?? '' }}" />
                            <div class="input-group-append" data-target="#div_appeal_send_dt" data-toggle="datetimepicker">
                                <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <select name="appeal_send_type" id="appeal_send_type" class="form-control form-control-sm">
                            <option value=''>발송종류</option>
                            {{ Func::printOption($appeal_send_type, Func::nvl($irl['appeal_send_type'],'')) }} 
                            </select>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <th class="status-bg-n">변제은행</th>
                <td>
                    <div class="col-md-12">
                        <input type="text" class="form-control form-control-sm " name="law_bank_cd" id="law_bank_cd" value="{{ $irl['law_bank_cd'] ?? '' }}">
                    </div>
                </td>
                <th class="status-bg-n">변제계좌</th>
                <td>
                    <div class="col-md-12">
                        <input type="text" class="form-control form-control-sm " name="law_bank_acno" id="law_bank_acno" value="{{ $irl['law_bank_acno'] ?? '' }}">
                    </div>
                </td>
                <th class="status-bg-n">변제채권자</th>
                <td>
                    <div class="col-md-12">
                        <input type="text" class="form-control form-control-sm " name="law_bank_name" id="law_bank_name" value="{{ $irl['law_bank_name'] ?? '' }}">
                    </div>
                </td>
                <th class="status-bg-d">
                    <span class="text-danger font-weight-bold h6 mr-1">*</span>
                    채권자번호
                </th>
                <td>
                    <div class="col-md-11">
                        <input type="text" class="form-control form-control-sm " name="receiv_no" id="receiv_no" value="{{ $irl['receiv_no'] ?? '' }}" onkeyup="onlyNumber(this);">
                    </div>
                </td>
                <th class="status-bg-d">철회신청일</th>
                <td>
                    <div class="input-group date datetimepicker col-md-11" id="div_retract_dt" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm col-md-11" id="retract_date" name="retract_date" DateOnly='true'  value="{{ $irl['retract_date'] ?? '' }}" />
                        <div class="input-group-append" data-target="#div_retract_dt" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
            </tr>

            <tr>
                <th colspan="2" rowspan="2" class="status-bg-n">① OPB 변동 내역</th>
                <td colspan="4" rowspan="2">
                    <textarea class="form-control" rows="3" name="opb_change_history" id="opb_change_history" style="resize: none;">{{$irl['opb_change_history'] ?? ''}}</textarea>
                </td>
                <th class="status-bg-d">
                    <span class="text-danger font-weight-bold h6 mr-1">*</span>
                    회생변제 시작일
                </th>
                <td>
                    <div class="input-group date datetimepicker col-md-11" id="div_revival_start_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="revival_start_date" name="revival_start_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($irl['revival_start_date'],'')) ?? ''}}"/>
                        <div class="input-group-append" data-target="#div_revival_start_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <th class="status-bg-d">회생변제 종료일</th>
                <td>
                    <div class="input-group date datetimepicker col-md-11" id="div_revival_end_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="revival_end_date" name="revival_end_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($irl['revival_end_date'],'')) ?? ''}}"/>
                        <div class="input-group-append" data-target="#div_revival_end_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="status-bg-d">
                    <span class="text-danger font-weight-bold h6 mr-1">*</span>
                    재판부
                </th>
                <td colspan="3">
                    <div class="col-md-12">
                        <input type="text" class="form-control form-control-sm " name="court_name" id="court_name" value="{{ $irl['court_name'] ?? '' }}" >
                    </div>
                </td>
            </tr>

            <tr>
                <th colspan="2" rowspan="2" class="status-bg-n">② 현재결과 검수 내역</th>
                <td colspan="4" rowspan="2">
                    <textarea class="form-control" rows="3" name="result_inspection_history" id="result_inspection_history" style="resize: none;">{{$irl['result_inspection_history'] ?? ''}}</textarea>
                </td>
                <th class="status-bg-d">
                    <span class="text-danger font-weight-bold h6 mr-1">*</span>
                    현재결과
                </th>
                <td>
                    <div class="col-md-11">
                        <input type="text" class="form-control form-control-sm" name="result_now" id="result_now" value="{{ Func::nvl($irl['result_now'],'') }}">
                    </div>
                </td>
                <th class="status-bg-d">대위변제여부</th>
                <td>
                    <div class="col-md-7">
                        <select name="subrogation_flag" id="subrogation_flag" class="form-control form-control-sm">
                        <option value=''>선택</option>
                        {{ Func::printOption($subrogation_flag, Func::nvl($irl['subrogation_flag'],'')) }} 
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="status-bg-d">
                    <span class="text-danger font-weight-bold h6 mr-1">*</span>
                    현재결과일
                </th>
                <td>
                    <div class="col-md-11">
                        <div class="input-group date datetimepicker " id="div_result_now_date" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm" id="result_now_date" name="result_now_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($irl['result_now_date'],'')) ?? ''}}"/>
                            <div class="input-group-append" data-target="#div_result_now_date" data-toggle="datetimepicker">
                                <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </td>
                <th class="status-bg-d">
                    <span class="text-danger font-weight-bold h6 mr-1">*</span>
                    별제권여부
                </th>
                <td>
                    <div class="col-md-7">
                        <select name="separate_flag" id="separate_flag" class="form-control form-control-sm">
                        <option value=''>선택</option>
                        {{ Func::printOption($separate_flag, Func::nvl($irl['separate_flag'],'')) }} 
                        </select>
                    </div>
                </td>
            </tr>

            <tr>
                <th colspan="2" rowspan="2" class="status-bg-n">③ 신고 내역</th>
                <td colspan="4" rowspan="2">
                    <textarea class="form-control" rows="3" name="report_history" id="report_history" style="resize: none;">{{$irl['report_history'] ?? ''}}</textarea>
                </td>
                <th class="status-bg-d">
                    <span class="text-danger font-weight-bold h6 mr-1">*</span>
                    매입전기변제금액
                </th>
                <td>
                    <div class="row" style="margin-left:1px;">
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm" name="buy_repay_money" id="buy_repay_money" value="{{ number_format($irl['buy_repay_money'] ?? 0) }}">
                        </div>
                        <div>
                            <span>원</span>
                        </div>
                    <div>
                </td>
                <th class="status-bg-d">실기변제금</th>
                <td>
                    <div class="row" style="margin-left:1px;">
                        <div class="col-md-7">
                            <input type="text" class="form-control form-control-sm" name="real_repay_money" id="real_repay_money" value="{{ number_format($irl['real_repay_money'] ?? 0) }}">
                        </div>
                        <div>
                            <span>원</span>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th class="status-bg-d">
                    <span class="text-danger font-weight-bold h6 mr-1">*</span>
                    진행일
                </th>
                <td>
                    <div class="input-group date datetimepicker col-md-11" id="div_progress_dt" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="progress_date" name="progress_date" DateOnly='true'  value="{{ $irl['progress_date'] ?? '' }}" />
                        <div class="input-group-append" data-target="#div_progress_dt" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <th class="status-bg-d">
                    <span class="text-danger font-weight-bold h6 mr-1">*</span>
                    우선변제회차
                </th>
                <td>
                    <div class="col-md-7">
                        <input type="text" class="form-control form-control-sm" name="first_repay_cnt" id="first_repay_cnt" value="{{ Func::nvl($irl['first_repay_cnt'],'') }}">
                    </div>
                </td>
            </tr>

            <tr>
                <th colspan="2" rowspan="2" class="status-bg-n">④ 일시변제,재산처분 내역</th>
                <td colspan="4" rowspan="2">
                    <textarea class="form-control" rows="3" name="temporary_payment_history" id="temporary_payment_history" style="resize: none;">{{$irl['temporary_payment_history'] ?? ''}}</textarea>
                </td>
                <th class="status-bg-d">
                    <span class="text-danger font-weight-bold h6 mr-1">*</span>
                    중단일
                </th>
                <td>
                    <div class="input-group date datetimepicker col-md-11" id="div_suspend_dt" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="suspend_date" name="suspend_date" DateOnly='true'  value="{{ $irl['suspend_date'] ?? '' }}" />
                        <div class="input-group-append" data-target="#div_suspend_dt" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <th class="status-bg-d">중단사유</th>
                <td>
                    <div class="col-md-7">
                        <select name="settle_cancel" id="settle_cancel" class="form-control form-control-sm">
                        <option value=''>선택</option>
                        {{ Func::printOption($settle_cancel, Func::nvl($irl['settle_cancel'],'')) }} 
                        </select>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="4">
                {{-- <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-folder-fill" viewBox="0 0 16 16">
                    <path d="M9.828 3h3.982a2 2 0 0 1 1.992 2.181l-.637 7A2 2 0 0 1 13.174 14H2.825a2 2 0 0 1-1.991-1.819l-.637-7a1.99 1.99 0 0 1 .342-1.31L.5 3a2 2 0 0 1 2-2h3.672a2 2 0 0 1 1.414.586l.828.828A2 2 0 0 0 9.828 3zm-8.322.12C1.72 3.042 1.95 3 2.19 3h5.396l-.707-.707A1 1 0 0 0 6.172 2H2.5a1 1 0 0 0-1 .981l.006.139z"/>
                </svg>&nbsp; --}}
                <b>사건조회 변제현황</b>
                </td>
            </tr>

            <tr>
                <th colspan="2" rowspan="2" class="status-bg-n">
                    ⑤ 단축안 관리
                </th>
                <td colspan="4" rowspan="2">
                    <textarea class="form-control" rows="3" name="shorten_manage" id="shorten_manage" style="resize: none;">{{$irl['shorten_manage'] ?? ''}}</textarea>
                </td>
                <th class="bg-info"> 조회일</th>
                <td>
                    <div class="col-md-12">
                        {{ Func::dateFormat(Func::nvl($irl['repay_trade_date'],'')) ?? '' }}
                    </div>
                </td>
                <th></th>
                <td>
                    <div class="col-md-12">

                    </div>
                </td>
            </tr>
            <tr>
                <th class="bg-info">변제주기</th>
                <td>
                    <div class="col-md-12 text-center">
                        {{ Func::nvl($irl['repay_cycle'],'') ?? '-' }} 개월
                    </div>
                </td>
                <th class="bg-info">변제기일</th>
                <td>
                    <div class="col-md-12 text-center">
                        {{ Func::nvl($irl['repay_date'],'') ?? '-' }} 일
                    </div>
                </td>
            </tr>

            <tr>
                <th colspan="2" rowspan="2" class="status-bg-n">⑥ 스케줄 관리</th>
                <td colspan="4" rowspan="2">
                    <textarea class="form-control" rows="3" name="schedule_manage" id="schedule_manage" style="resize: none;">{{$irl['schedule_manage'] ?? ''}}</textarea>
                </td>
                <th class="bg-info">매월변제예정금액</th>
                <td>
                    <div class="col-md-12 text-center">
                        {{ Func::numberFormat(Func::nvl($irl['repay_expact_money'],'')) ?? '' }}&nbsp;원
                    </div>
                </td>
                <th class="bg-info">출금대상잔액</th>
                <td>
                    <div class="col-md-12 text-center">
                        {{ Func::nvl($irl['repay_balance'],'') ?? '-' }}&nbsp;원
                    </div>
                </td>
            </tr>
            <tr>
                <th class="bg-info">재산처분예정금액</th>
                <td>
                    <div class="col-md-12 text-center">
                        {{ Func::nvl($irl['repay_pre_money'],'') ?? '-' }}&nbsp;원
                    </div>
                </td>
                <th class="bg-info">재산처분납입금액</th>
                <td>
                    <div class="col-md-12 text-center">
                        {{ Func::nvl($irl['repay_pay_money'],'') ?? '-' }}&nbsp;원
                    </div>
                </td>
            </tr>

            <tr>
                <th colspan="2" rowspan="2" class="status-bg-n">⑦ 기타 관리</th>
                <td colspan="4" rowspan="2">
                    <textarea class="form-control" rows="3" name="etc_manage" id="etc_manage" style="resize: none;">{{$irl['etc_manage'] ?? ''}}</textarea>
                </td>
                <th class="bg-info">전체변제회차</th>
                <td>
                    <div class="col-md-12 text-center">
                        {{ Func::nvl($irl['repay_cnt_all'],'') ?? '-' }} 회
                    </div>
                </td>
                <th class="bg-info">현재변제회차</th>
                <td>
                    <div class="col-md-12 text-center">
                        {{ Func::nvl($irl['repay_cnt_now'],'') ?? '-' }} 회
                    </div>
                </td>
            </tr>
            <tr>
                <th class="bg-info">잔여변제회차</th>
                <td>
                    <div class="col-md-12 text-center">
                        {{ Func::nvl($irl['repay_cnt_remain'],'') ?? '-' }} 회
                    </div>
                </td>
                <th class="bg-info">채무자미납회차</th>
                <td>
                    <div class="col-md-12 text-center">
                        {{ Func::nvl($irl['repay_no_cnt'],'') ?? '-' }} 회
                    </div>
                </td>
            </tr>

            </tbody>
        </table>
        <div class="col-md-10 pt-2">
            <b>사건조회 분할정보</b>
        </div>
        <div class="col-md-12 p-0">
            <table class="table table-sm text-xs card-outline settle_condition_table text-center">
                <thead>
                    <tr>
                        <th width="15%" class="status-bg-c">순번</th>
                        <th width="15%" class="status-bg-c">채권번호</th>
                        <th width="15%" class="status-bg-c">변제회차</th>
                        <th width="15%" class="status-bg-c">변제예정금액</th>
                        <th width="15%" class="status-bg-c">변제시작일</th>
                        <th width="15%" class="status-bg-c">변제종료일</th>
                    </tr>
                </thead>

                <tbody>
                    @if(isset($nsf_credt))
                    @foreach ($nsf_credt as $cr )
                    <tr>
                        <td>{{ $cr->bt_c_no ?? ''}}</td>
                        <td>{{ $cr->bt_c_creditno ?? ''}}</td>
                        <td>{{ $cr->bt_c_hoi ?? ''}}</td>
                        <td>{{ $cr->bt_c_hoimny ?? ''}}</td>
                        <td>{{ $cr->bt_c_opndt ?? ''}}</td>
                        <td>{{ $cr->bt_c_clsdt ?? ''}}</td>
                    </tr>
                    @endforeach
                    @endif
                </tbody>
            </table> 
        </div>
    </div>
    {{-- <div class="card-body p-1">
        <table class="table table-sm table-bordered table-input text-xs  text-center">
            <tbody>
            <tr>
                <th class="status-bg-n"  width="12%">*법원</th>
                <th class="status-bg-n"  width="12%">*사건번호</th>
                <th class="status-bg-n"  width="12%">*당사자명</th>
                <th class="status-bg-n"  width="12%">*변제채권번호입력</th>
                <th class="status-bg-n"  width="12%">조회일자</th>
                <th class="status-bg-n"  width="12%">사건조회결과</th>
                <th class="status-bg-n"  width="12%">변제조회결과</th>
            </tr>
            <tr>
                <td>
                    <select class="form-control form-control-sm selectpicker" data-size="10" data-live-search="true" name="law_justice" id="law_justice" title="선택">
                    {{ Func::printOption($law_justice,($irl['law_justice'] ?? '')) }}   
                    </select>
                </td>
                <td>
                    <div class="input-group  col-md-12" id="law_event_no" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm -input " name="law_event_no" id="law_event_no" value="{{ $irl['law_event_no'] ?? '' }}"  size="6" required>
                        <div class="input-group-append" data-target="#law_event_no">
                            <div class="input-group-text"  onclick="window.open('https://safind.scourt.go.kr/sf/mysafind.jsp','', 'width=1000, height=1000, scrollbars = yes, top=10, left=10');" style="cursor: pointer;"><i class="fa fa-search"></i></div>
                        </div>
                    </div>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm " name="law_name" id="law_name" value="{{ $irl['law_name'] ?? '' }}">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm " name="payment_bond_no" id="payment_bond_no" value="{{ $irl['payment_bond_no'] ?? '' }}">
                </td>
                <td>{{ $irl['nsf_date'] ?? '' }} @if(isset($irl['law_event_no'])) <button type="button" class="btn btn-default btn-sm text-xxs" onclick="getNsfInfo()">새로조회</button>@endif
                    @if(isset($irl['bm_seq'])) <button type="button" class="btn btn-default btn-sm text-xxs" onclick="getPopUp('/erp/nsfform?regdt={{ $irl['bm_regdt'] ?? ''}}&seq={{ $irl['bm_seq'] ?? ''}}','nsf','');">내용</button> @endif
                </td>
                <td>
                    {{ Func::nvl($irl['nsf_list_status'], '') }} |  {{ Func::dateFormat( Func::nvl($irl['nsf_list_status_time'], '')) ?? '' }}
                </td>
                <td>
                    {{ Func::nvl($irl['repay_info_status'], '') }} {{ Func::dateFormat(Func::nvl($irl['repay_info_status_time'],'')) ?? '' }}
                </td>
            </tr>  
            <tr>
                <th class="status-bg-n">신고계좌은행</th>
                <th class="status-bg-n">신고계좌</th>
                <th class="status-bg-n">신고예금주명</th>
                <th class="status-bg-n">변제은행</th>
                <th class="status-bg-n">변제계좌</th>
                <th class="status-bg-n">변제채권자</th>
                <th class="status-bg-n">변제현황일치</th>
            </tr>   
            <tr>
                <td>
                    <select class="form-control form-control-sm  selectpicker" name="virtual_bank" id="virtual_bank">
                    <option value=''>은행</option>
                    {{ Func::printOption($bank_cd,Func::nvl($irl['virtual_bank'],'')) }} 
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm " name="virtual_account" id="virtual_account" value="{{ $irl['virtual_account'] ?? '' }}">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm " name="virtual_bank_name" id="virtual_bank_name" value="{{ $irl['virtual_bank_name'] ?? '' }}">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm " name="law_bank_cd" id="law_bank_cd" value="{{ $irl['law_bank_cd'] ?? '' }}">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm " name="law_bank_acno" id="law_bank_acno" value="{{ $irl['law_bank_acno'] ?? '' }}">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm " name="law_bank_name" id="law_bank_name" value="{{ $irl['law_bank_name'] ?? '' }}">
                </td>
                <td>
                    <select class="form-control form-control-sm " name="repay_flag" id="repay_flag">
                    <option value=''>여부</option>
                    {{ Func::printOption(Vars::$arrayRepayFlag,Func::nvl($irl['repay_flag'],'')) }} 
                    </select>
                </td>
            </tr>
            <tr>
                <th class="status-bg-n">분납회생코드</th>
                <th class="status-bg-n">입금은행</th>
                <th class="status-bg-n">입금계좌</th>
                <th class="status-bg-n">입금채권자</th>
                <th class="status-bg-n">가상계좌은행</th>
                <th class="status-bg-n">가상계좌</th>
                <th class="status-bg-n">가상계좌예금주명</th>
            </tr> 
            <tr>
                <td>
                    <input type="hidden" name="save_code" id="save_code" value="{{Func::nvl($irl['save_code'],'')}}">
                    <select class="form-control form-control-sm selectpicker">
                    <option value=''>분납회생코드</option>
                    {{ Func::printOption($save_code, Func::nvl($irl['save_code'],'')) }}  
                    </select>
                </td>
                <td>
                    <select class="form-control form-control-sm  selectpicker" name="virtual_bank" id="virtual_bank">
                    <option value=''>은행</option>
                    {{ Func::printOption($bank_cd,Func::nvl($irl['virtual_bank'],'')) }} 
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="" id="" value="">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm " name="" id="" value="">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm " name="" id="" value="">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm " name="" id="" value="">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm " name="" id="" value="">
                </td>
            </tr>   
            </tbody>
        </table>
        <table class="table table-sm table-bordered table-input text-xs  text-center">
            <tbody>
            <tr>
                <th width="12%" class="status-bg-d">채권자번호</th>
                <th width="12%" class="status-bg-d">접수일</th>
                <th width="12%" class="status-bg-d">개시결정일</th>
                <th width="12%" class="status-bg-d">인가일</th>
                <th width="12%" class="status-bg-d">폐지결정일</th>
                <th width="12%" class="status-bg-d">면책결정일</th>
                <th colspan="2" class="status-bg-d">회생변제 시작일~종료일</th>
            </tr>
            <tr>
                <td>
                    <input type="text" class="form-control form-control-sm " name="receiv_no" id="receiv_no" value="{{ $irl['receiv_no'] ?? '' }}" onkeyup="onlyNumber(this);">
                </td>
                <td>
                    <div class="input-group date datetimepicker " id="div_irl_reg_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="irl_reg_date" name="irl_reg_date" DateOnly='true'  value="{{ Func::dateFormat(Func::nvl($irl['irl_reg_date'],'')) ?? '' }}" />
                        <div class="input-group-append" data-target="#div_irl_reg_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group date datetimepicker " id="div_irl_start_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="irl_start_date" name="irl_start_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($irl['irl_start_date'],'')) ?? ''}}"/>
                        <div class="input-group-append" data-target="#div_irl_start_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group date datetimepicker " id="div_irl_auth_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="irl_auth_date" name="irl_auth_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($irl['irl_auth_date'],'')) ?? ''}}"/>
                        <div class="input-group-append" data-target="#div_irl_auth_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group date datetimepicker " id="div_irl_revoke_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="irl_revoke_date" name="irl_revoke_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($irl['irl_revoke_date'],'')) ?? ''}}"/>
                        <div class="input-group-append" data-target="#div_irl_revoke_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td> 
                <td>
                    <div class="input-group date datetimepicker " id="div_exemption_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="exemption_date" name="exemption_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($irl['exemption_date'],'')) ?? ''}}"/>
                        <div class="input-group-append" data-target="#div_exemption_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group date datetimepicker " id="div_revival_start_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="revival_start_date" name="revival_start_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($irl['revival_start_date'],'')) ?? ''}}"/>
                        <div class="input-group-append" data-target="#div_revival_start_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group date datetimepicker " id="div_revival_end_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="revival_end_date" name="revival_end_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($irl['revival_end_date'],'')) ?? ''}}"/>
                        <div class="input-group-append" data-target="#div_revival_end_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
            </tr>
            <tr>
                <th width="12%" class="status-bg-d">명의변경신고일</th>
                <th width="12%" class="status-bg-d">계좌변경신고일</th>
                <th width="12%" class="status-bg-d">공탁금입금요청발송일</th>
                <th width="12%" class="status-bg-d">기타채권신고일</th>
                <th width="12%" class="status-bg-d">이의신청발송일</th>
                <th width="12%" class="status-bg-d">철회신청일</th>
                <th width="12%" class="status-bg-d">대위변제여부</th>
                <th width="12%" class="status-bg-d">실기변제금</th>
            </tr> 
            <tr>
                <td>
                    <div class="input-group date datetimepicker" id="div_name_change_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="name_change_date" name="name_change_date" DateOnly='true'  value="{{ Func::dateFormat(Func::nvl($irl['name_change_date'],'')) ?? ''}}" />
                        <div class="input-group-append" data-target="#div_name_change_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group date datetimepicker" id="div_report_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="report_date" name="report_date" DateOnly='true'  value="{{ $irl['report_date'] ?? '' }}" />
                        <div class="input-group-append" data-target="#div_report_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group date datetimepicker" id="div_deposit_in_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="deposit_in_date" name="deposit_in_date" DateOnly='true'  value="{{ $irl['deposit_in_date'] ?? '' }}" />
                        <div class="input-group-append" data-target="#div_deposit_in_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group date datetimepicker" id="div_etc_notify_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="etc_notify_date" name="etc_notify_date" DateOnly='true'  value="{{ $irl['etc_notify_date'] ?? '' }}" />
                        <div class="input-group-append" data-target="#div_etc_notify_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group date datetimepicker" id="div_appeal_send_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="appeal_send_date" name="appeal_send_date" DateOnly='true'  value="{{ $irl['appeal_send_date'] ?? '' }}" />
                        <div class="input-group-append" data-target="#div_appeal_send_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group date datetimepicker" id="div_retract_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="retract_date" name="retract_date" DateOnly='true'  value="{{ $irl['retract_date'] ?? '' }}" />
                        <div class="input-group-append" data-target="#div_retract_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <td>
                    <input type="hidden" name="subrogation_flag" id="subrogation_flag" value="{{Func::nvl($irl['subrogation_flag'],'')}}">
                    <select class="form-control form-control-sm">
                    <option value=''>선택</option>
                    {{ Func::printOption($subrogation_flag, Func::nvl($irl['subrogation_flag'],'')) }} 
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="" id="" value="{{ number_format($irl['real_repay_money'] ?? 0) }}">
                </td>
            </tr>
            <tr>
                <th class="status-bg-d">진행일</th>
                <th class="status-bg-d">중단일</th>
                <th class="status-bg-d">중단사유</th>
                <th class="status-bg-d">별제권여부</th>
                <th class="status-bg-d">우선변제회차</th>
            </tr>
            <tr>
                <td>
                    <div class="input-group date datetimepicker " id="div_progress_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="progress_date" name="progress_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($irl['progress_date'],'')) ?? ''}}"/>
                        <div class="input-group-append" data-target="#div_progress_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="input-group date datetimepicker" id="div_revoke_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm" id="irl_revoke_date" name="irl_revoke_date" DateOnly='true'  value="{{ $irl['irl_revoke_date'] ?? '' }}" />
                        <div class="input-group-append" data-target="#div_revoke_date" data-toggle="datetimepicker">
                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <td >
                    <input type="hidden" name="settle_cancel" id="settle_cancel" value="{{Func::nvl($irl['settle_cancel'],'')}}">
                    <select class="form-control form-control-sm">
                    <option value=''>선택</option>
                    {{ Func::printOption($settle_cancel, Func::nvl($irl['settle_cancel'],'')) }} 
                    </select>
                </td>
                <td>
                    <input type="hidden" name="separate_flag" id="separate_flag" value="{{Func::nvl($irl['separate_flag'],'')}}">
                    <select class="form-control form-control-sm">
                    <option value=''>선택</option>
                    {{ Func::printOption($separate_flag, Func::nvl($irl['separate_flag'],'')) }} 
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="" id="" value="">
                </td>   
            </tr>
                
            <tr >
                <th class="status-bg-d">현재결과</th>
                <td colspan="3">
                    <div class="col-md-12  pl-0">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="input-group date datetimepicker " id="div_result_now_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm" id="result_now_date" name="result_now_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($irl['result_now_date'],'')) ?? ''}}"/>
                                    <div class="input-group-append" data-target="#div_result_now_date" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-8">
                                <input type="text" class="form-control form-control-sm text-blue" value=" {{ $irl['result_now'] ?? '' }}  ">
                            </div>
                        </div>
                    </div>
                </td>
                <th class="status-bg-d">재판부</th>
                <td colspan="3">
                    <input type="text" class="form-control form-control-sm " name="court_name" id="court_name" value="{{ $irl['court_name'] ?? '' }}" >
                </td>
            </tr> 
            </tbody>
        </table>
        <div class="row">
            <div class="col-md-6">
                <b>사건조회 변제현황</b>
                <table class="table table-sm table-bordered table-input text-xs  text-center">
                    <tbody>
                        <tr>
                            <th class="bg-info" width="13%">조회일</th>
                            <td  width="10%">{{ Func::dateFormat(Func::nvl($irl['repay_trade_date'],'')) ?? '' }}</td>
                            <th class="bg-info"  width="13%">변제주기</th>
                            <td  width="10%">{{ Func::nvl($irl['repay_cycle'],'') ?? '-' }} 개월</td>
                            <th class="bg-info"  width="13%">변제기일</th>
                            <td  width="10%">{{ Func::nvl($irl['repay_date'],'') ?? '-' }} 일</td>
                        </tr> 
                        <tr>
                            <th class="bg-info"  width="13%">변제예정금액</th>
                            <td  width="10%">{{ Func::numberFormat(Func::nvl($irl['repay_expact_money'],'')) ?? '' }}&nbsp;원</td>
                            <th class="bg-info">출금대상잔액</th>
                            <td>{{ Func::nvl($irl['repay_balance'],'') ?? '-' }}&nbsp;원</td>
                        </tr>  
                        <tr>
                            <th class="bg-info">재산처분예정금액</th>
                            <td>{{ Func::nvl($irl['repay_pre_money'],'') ?? '-' }}&nbsp;원</td>
                            <th class="bg-info">재산처분납입금액</th>
                            <td>{{ Func::nvl($irl['repay_pay_money'],'') ?? '-' }}&nbsp;원</td>
                            <th class="bg-info">전체변제회차</th>
                            <td>{{ Func::nvl($irl['repay_cnt_all'],'') ?? '-' }} 회</td>
                        </tr>
                        <tr>
                            <th class="bg-info">현재변제회차</th>
                            <td>{{ Func::nvl($irl['repay_cnt_now'],'') ?? '-' }} 회</td>
                            <th class="bg-info">잔여변제회차</th>
                            <td>{{ Func::nvl($irl['repay_cnt_remain'],'') ?? '-' }} 회</td>
                            <th class="bg-info">채무자미납회차</th>
                            <td>{{ Func::nvl($irl['repay_no_cnt'],'') ?? '-' }} 회</td>
                        </tr>
                        <tr>
                            <th class="status-bg-n" colspan='2'>개인회생메모</th>
                            <td colspan="4"><textarea class="form-control" rows="2" style="resize:none;" name="law_memo" id="law_memo">{{ Func::numberFormat(Func::nvl($irl['law_memo'],'')) ?? '' }}</textarea></td>
                        </tr>
                        <tr>
                            <th class="status-bg-n" colspan='2'>OPB 변동 내역</th>
                            <td colspan="4"><textarea class="form-control" rows="2" style="resize:none;" name="law_memo" id="law_memo">{{ Func::numberFormat(Func::nvl($irl['law_memo'],'')) ?? '' }}</textarea></td>
                        </tr>
                        <tr>
                            <th class="status-bg-n" colspan='2'>현재결과 검수 내역</th>
                            <td colspan="4"><textarea class="form-control" rows="2" style="resize:none;" name="law_memo" id="law_memo">{{ Func::numberFormat(Func::nvl($irl['law_memo'],'')) ?? '' }}</textarea></td>
                        </tr>
                        <tr>
                            <th class="status-bg-n" colspan='2'>신고 내역</th>
                            <td colspan="4"><textarea class="form-control" rows="2" style="resize:none;" name="law_memo" id="law_memo">{{ Func::numberFormat(Func::nvl($irl['law_memo'],'')) ?? '' }}</textarea></td>
                        </tr>
                        <tr>
                            <th class="status-bg-n" colspan='2'>일시변제,재산처분 내역</th>
                            <td colspan="4"><textarea class="form-control" rows="2" style="resize:none;" name="law_memo" id="law_memo">{{ Func::numberFormat(Func::nvl($irl['law_memo'],'')) ?? '' }}</textarea></td>
                        </tr>
                        <tr>
                            <th class="status-bg-n" colspan='2'>단축안 관리</th>
                            <td colspan="4"><textarea class="form-control" rows="2" style="resize:none;" name="law_memo" id="law_memo">{{ Func::numberFormat(Func::nvl($irl['law_memo'],'')) ?? '' }}</textarea></td>
                        </tr>
                        <tr>
                            <th class="status-bg-n" colspan='2'>스케줄 관리</th>
                            <td colspan="4"><textarea class="form-control" rows="2" style="resize:none;" name="law_memo" id="law_memo">{{ Func::numberFormat(Func::nvl($irl['law_memo'],'')) ?? '' }}</textarea></td>
                        </tr>
                        <tr>
                            <th class="status-bg-n" colspan='2'>기타 관리</th>
                            <td colspan="4"><textarea class="form-control" rows="2" style="resize:none;" name="law_memo" id="law_memo">{{ Func::numberFormat(Func::nvl($irl['law_memo'],'')) ?? '' }}</textarea></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-6">
                <b>사건조회 분할정보</b>
                <table class="table table-sm table-bordered table-input text-xs  text-center">
                    <tbody>
                    <tr >
                        <th class="status-bg-c" width="5%">순번</th>
                        <th class="status-bg-c" width="12%">채권번호</th>
                        <th class="status-bg-c" width="12%">변제회차</th>
                        <th class="status-bg-c" width="12%">변제예정금액</th>
                        <th class="status-bg-c" width="12%">변제시작일</th>
                        <th class="status-bg-c" width="12%">변제종료일</th>
                    </tr>
                    
                    @if(isset($nsf_credt))
                    @foreach ($nsf_credt as $cr )
                    <tr>
                        <td>{{ $cr->bt_c_no ?? ''}}</td>
                        <td>{{ $cr->bt_c_creditno ?? ''}}</td>
                        <td>{{ $cr->bt_c_hoi ?? ''}}</td>
                        <td>{{ $cr->bt_c_hoimny ?? ''}}</td>
                        <td>{{ $cr->bt_c_opndt ?? ''}}</td>
                        <td>{{ $cr->bt_c_clsdt ?? ''}}</td>
                    </tr>
                    @endforeach
                    @endif
                    </tbody>
                </table>
            </div>
        </div>
    </div> --}}
</div>
<script>
    $('.selectpicker').selectpicker();
    $(".datetimepicker").datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
        useCurrent: false,
    });
    // 나의사건조회 등록
    function getNsfInfo() 
    {
        if(!confirm("나의사건조회를 새로진행하시겠습니까?")) return;
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var postdata = $('#settle_form').serialize();
        $.ajax({
            url  : "/erp/irlnsfinfo",
            type : "post",
            data : postdata,
            dataType: "json",
            success : function(data)
            {
                if(data.rs_code!='Y') alert(data.rs_msg);
                else  alert("요청 완료되었습니다.");
            },
            error : function(xhr)
            {
                console.log(xhr);
            }
        });
    }

</script>