<div class="card card-outline card-warning">
    <div class="card-header p-1">
        <h3 class="card-title"><i class="fas fa-university m-2"></i>신용회복</h3>
        <div class="card-tools col-md-3">
            <div class="mr-1 mb-1 mt-1 text-right">
                {{-- @if(isset($ccrs)) <button type="button" class="btn btn-sm btn-outline-warning"  onclick="saveOnlyCcrs()">신용회복정보저장</button> @endif  --}}
                @if(isset($ccrs['no'])) <button type="button" class="btn btn-sm btn-outline-warning"  onclick="saveOnlyCcrs()">신용회복정보저장</button> @endif 
            </div>
        </div>    
    </div> 
    <div class="card-body p-1 ">
        <table class="table table-sm table-bordered table-input text-sm  text-center col-sm-12">
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
                <th class="status-bg-n">구분</th>
                <th class="status-bg-n">계좌번호</th>
                <th class="status-bg-n">변제금수취계좌</th>
                <td colspan="4" rowspan="2"></td>
            </tr>
            <tr>
                <td>
                    <select class="form-control form-control-sm " name="ccrs_div" id="ccrs_div">
                    <option value=''>신용회복구분(자동)</option>
                    {{ Func::printOption($ccrs_div_cd,Func::nvl($ccrs['div'],''))  }} 
                    </select>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm " name="ccrs_account" id="ccrs_account" value="{{ $ccrs['ccrs_account'] ?? '' }}">
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm " name="ccrs_recv_acno" id="ccrs_recv_acno" value="{{ $ccrs['ccrs_recv_acno'] ?? '' }}"  onkeyup="onlyNumber(this);">
                </td>
            </tr>
            <tr>
                <th class="status-bg-n">접수번호</th>
                <th class="status-bg-n">심의차수</th>
                <th class="status-bg-n">신청인상태</th>
                <th class="status-bg-n">계좌상태</th>
                <th colspan="2" class="status-bg-n">실효일자 / 완제일자 / 포기일자</th>
                <th class="status-bg-n">감면방식</th>
            </tr>
            <td>
                <input type="text" class="form-control form-control-sm " name="ccrs_app_no" id="ccrs_app_no" value="{{ $ccrs['ccrs_app_no'] ?? '' }}" >
            </td>
            <td>
                <input type="text" class="form-control form-control-sm " name="ccrs_app_cnt" id="ccrs_app_cnt" value="{{ $ccrs['ccrs_app_cnt'] ?? '' }}" >
            </td>
            <td>
                <input type="text" class="form-control form-control-sm " name="ccrs_stat" id="ccrs_stat"  value="{{ $ccrs['ccrs_stat'] ?? '' }}" >
            </td>
            <td>
                <input type="text" class="form-control form-control-sm " name="ccrs_acct_stat" id="ccrs_acct_stat"  value="{{ $ccrs['ccrs_acct_stat'] ?? '' }}" >
            </td>
            <td colspan="2">
                <table>
                    <colgroup>
                    <col width="33%"/>
                    <col width="33%"/>
                    <col width="33%"/>
                    </colgroup>
                    <tbody>
                        <tr>
                            <td>
                                <div class="col-md-12  pl-0">
                                    <div class="input-group date datetimepicker " id="div_ccrs_cancel_dt" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm" id="ccrs_cancel_date" name="ccrs_cancel_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($ccrs['ccrs_cancel_date'],'')) ?? ''}}"/>
                                        <div class="input-group-append" data-target="#div_ccrs_cancel_dt" data-toggle="datetimepicker">
                                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </td>    
                            <td>
                                <div class="input-group date datetimepicker col-md-12" id="div_ccrs_end_dt" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm" id="ccrs_end_date" name="ccrs_end_date" DateOnly='true'  value="{{ $ccrs['ccrs_end_date'] ?? '' }}" />
                                    <div class="input-group-append" data-target="#div_ccrs_end_dt" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <div class="input-group date datetimepicker col-md-12" id="div_ccrs_lose_dt" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm" id="ccrs_lose_date" name="ccrs_lose_date" DateOnly='true'  value="{{ $ccrs['ccrs_lose_date'] ?? '' }}" />
                                    <div class="input-group-append" data-target="#div_ccrs_lose_dt" data-toggle="datetimepicker">
                                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </td>
            <td>
                <input type="text" class="form-control form-control-sm " name="ccrs_lose_type" id="ccrs_lose_type" value="{{ $ccrs['ccrs_lose_type'] ?? '' }}" >
            </td>
            <tr>
                <th class="status-bg-n">접수통지일</th>
                <th class="status-bg-n">접수일</th>
                <th class="status-bg-n">확정통지일</th>
                <th class="status-bg-n">확정일</th>
                <th class="status-bg-n">상환개시일</th>
                <th class="status-bg-n">재조정여부</th>
                <th class="status-bg-n">재조정처리일자</th>
            </tr>   
            <tr>
                <td>
                    <div class="col-md-12  pl-0">
                        <div class="input-group date datetimepicker " id="div_ccrs_app_alarm_dt" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm" id="ccrs_app_alarm_date" name="ccrs_app_alarm_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($ccrs['ccrs_app_alarm_date'],'')) ?? ''}}"/>
                            <div class="input-group-append" data-target="#div_ccrs_app_alarm_dt" data-toggle="datetimepicker">
                                <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="col-md-12  pl-0">
                        <div class="input-group date datetimepicker " id="div_ccrs_app_dt" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm" id="ccrs_app_date" name="ccrs_app_date" DateOnly='true'   value="{{ Func::dateFormat($ccrs['ccrs_app_date'] ?? '') ?? ''}}"/>
                            <div class="input-group-append" data-target="#div_ccrs_app_dt" data-toggle="datetimepicker">
                                <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="col-md-12  pl-0">
                        <div class="input-group date datetimepicker " id="div_ccrs_confirm_alarm_dt" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm" id="ccrs_confirm_alarm_date" name="ccrs_confirm_alarm_date" DateOnly='true'   value="{{ Func::dateFormat($ccrs['ccrs_confirm_alarm_date'] ?? '') ?? ''}}"/>
                            <div class="input-group-append" data-target="#div_ccrs_confirm_alarm_dt" data-toggle="datetimepicker">
                                <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="col-md-12  pl-0">
                        <div class="input-group date datetimepicker " id="div_ccrs_confirm_dt" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm" id="ccrs_confirm_date" name="ccrs_confirm_date" DateOnly='true'   value="{{ Func::dateFormat($ccrs['ccrs_confirm_date'] ?? '') ?? ''}}"/>
                            <div class="input-group-append" data-target="#div_ccrs_confirm_dt" data-toggle="datetimepicker">
                                <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <div class="col-md-12  pl-0">
                        <div class="input-group date datetimepicker " id="div_ccrs_start_dt" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm" id="ccrs_start_date" name="ccrs_start_date" DateOnly='true'   value="{{ Func::dateFormat($ccrs['ccrs_start_date'] ?? '') ?? ''}}"/>
                            <div class="input-group-append" data-target="#div_ccrs_start_dt" data-toggle="datetimepicker">
                                <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </td>
                <td>
                    <input type="text" class="form-control form-control-sm" name="ccrs_readjust_flag" id="ccrs_readjust_flag" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_readjust_flag'],'')) ?? '' }}" >
                </td>
                <td>
                    <div class="col-md-12  pl-0">
                        <div class="input-group date datetimepicker" id="div_ccrs_readjust_dt" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm datetimepicker-input " name="ccrs_readjust_date" id="ccrs_readjust_date" value="{{ Func::dateFormat(Func::nvl($ccrs['ccrs_readjust_date'],'')) ?? ''}}" dateonly="true" >
                            <div class="input-group-append" data-target="#div_ccrs_readjust_dt" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </td>
            </tr>  
            <tr>
                <th class="status-bg-c">수정조정접수일자</th>
                <th class="status-bg-c">실효취소동의요청일</th>
                <th class="status-bg-n">실효원상회복일자</th>
                <th class="status-bg-n"></th>
                <th class="status-bg-n"></th>
                <th class="status-bg-n">수정조정여부</th>
                <th class="status-bg-n">수정확정일자</th>
            </tr>
            <tr>
                <td>
                    <div class="col-md-12  pl-0">
                        <div class="input-group date datetimepicker " id="div_ccrs_alter_app_date" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm" id="ccrs_alter_app_date" name="ccrs_alter_app_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($ccrs['ccrs_alter_app_date'],'')) ?? ''}}"/>
                            <div class="input-group-append" data-target="#div_ccrs_alter_app_date" data-toggle="datetimepicker">
                                <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </td>    
                <td>
                    <div class="col-md-12  pl-0">
                        <div class="input-group date datetimepicker " id="div_cancel_agree_date" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm" id="cancel_agree_date" name="cancel_agree_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($ccrs['cancel_agree_date'],'')) ?? ''}}"/>
                            <div class="input-group-append" data-target="#div_cancel_agree_date" data-toggle="datetimepicker">
                                <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </td>    
                <td>
                    <div class="col-md-12  pl-0">
                        <div class="input-group date datetimepicker " id="div_cancel_return_date" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm" id="cancel_return_date" name="cancel_return_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($ccrs['cancel_return_date'],'')) ?? ''}}"/>
                            <div class="input-group-append" data-target="#div_cancel_return_date" data-toggle="datetimepicker">
                                <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </td>
                <td></td> 
                <td></td> 
                <td>
                    <input type="text" class="form-control form-control-sm" name="ccrs_alter_flag" id="ccrs_alter_flag" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_alter_flag'],'')) ?? '' }}" >
                </td>
                <td>
                    <div class="col-md-12  pl-0">
                        <div class="input-group date datetimepicker" id="div_confirm_alter_date" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm datetimepicker-input " name="confirm_alter_date" id="confirm_alter_date" value="{{ Func::dateFormat(Func::nvl($ccrs['confirm_alter_date'],'')) ?? ''}}" dateonly="true" >
                            <div class="input-group-append" data-target="#div_confirm_alter_date" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </td>   
            </tr>  
        </table>
        <table class="col-sm-12">
            <tr>
                <td width="60%">
                    <table class="table table-sm table-bordered table-input text-sm  text-center col-sm-12">
                        <tr >
                            <th width="20%" class="status-bg-d">조정전원금</th>
                            <th width="20%" class="status-bg-d">조정전이자</th>
                            <th width="20%" class="status-bg-d">조정전연체이자</th>
                            <th width="20%" class="status-bg-d">조정전비용</th>
                            <th width="20%" class="status-bg-d">조정전합계</th>
                        </tr> 
                        <tr>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_before_origin_money" id="ccrs_before_origin_money" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_before_origin_money'],'')) ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_before_interest" id="ccrs_before_interest" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_before_interest'],'')) ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_before_delay_interest" id="ccrs_before_delay_interest" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_before_delay_interest'],'')) ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_before_pre_money" id="ccrs_before_pre_money" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_before_pre_money'],'')) ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_before_total_money" id="ccrs_before_total_money" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_before_total_money'],'')) ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                        </tr>
                        <tr>
                            <th class="status-bg-d">조정후원금</th>
                            <th class="status-bg-d">조정후이자</th>
                            <th class="status-bg-d">조정후연체이자</th>
                            <th class="status-bg-d">조정후비용</th>
                            <th class="status-bg-d">조정후합계</th>
                        </tr> 
                        <tr> 
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_after_origin_money" id="ccrs_after_origin_money" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_after_origin_money'],'')) ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_after_interest" id="ccrs_after_interest" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_after_interest'],'')) ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_after_delay_interest" id="ccrs_after_delay_interest" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_after_delay_interest'],'')) ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_after_pre_money" id="ccrs_after_pre_money" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_after_pre_money'],'')) ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_after_total_money" id="ccrs_after_total_money" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_after_total_money'],'')) ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-info">원금균등상환기간</th>
                            <th class="bg-info">납입회차</th>
                            <th class="bg-info">원금균등채무액</th>
                            <th class="bg-info">원금균등시작회차~종료회차</th>
                            <th class="bg-info">유예기간</th>
                        </tr> 
                        <tr>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_repay_total_cnt" id="ccrs_repay_total_cnt" value="{{ $ccrs['ccrs_repay_total_cnt'] ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_repay_now_cnt" id="ccrs_repay_now_cnt" value="{{ $ccrs['ccrs_repay_now_cnt'] ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_monthly_return_money" id="ccrs_monthly_return_money" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_monthly_return_money'],'')) ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                            <td>
                                <div class="input-group ">
                                    <input type="text" class="form-control form-control-sm col-md-5" name="ccrs_repay_start_cnt" id="ccrs_repay_start_cnt"  value="{{ $ccrs['ccrs_repay_start_cnt'] ?? '' }}" onkeyup="onlyNumber(this);">
                                    <span class="col-md-2">~</span>
                                    <input type="text" class="form-control form-control-sm col-md-5" name="ccrs_repay_end_cnt" id="ccrs_repay_end_cnt" value="{{ $ccrs['ccrs_repay_end_cnt'] ?? '' }}" onkeyup="onlyNumber(this);">
                                </div>
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_respite_term" id="ccrs_respite_term" value="{{ $ccrs['ccrs_respite_term'] ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                        </tr>
                        <tr>
                            <th class="bg-info">총납입원금</th>
                            <th class="bg-info">총납입이자</th>
                            <th class="bg-info">총납입기타채무</th>
                            <th class="bg-info">총납입금액</th>
                            <th class="bg-info">상환후잔액</th>
                        </tr> 
                        <tr>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_total" id="ccrs_total" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_total'],'')) ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_balance" id="ccrs_balance" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_balance'],'')) ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_interest" id="ccrs_interest" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_interest'],'')) ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_cost" id="ccrs_cost" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_cost'],'')) ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                            <td>
                                <input type="text" class="form-control form-control-sm " name="ccrs_repay_end_balance" id="ccrs_repay_end_balance" value="{{ Func::numberFormat(Func::nvl($ccrs['ccrs_repay_end_balance'],'')) ?? '' }}" onkeyup="onlyNumber(this);">
                            </td>
                        </tr>
                        <tr>
                            <th class="status-bg-n">반송일자</th>
                            <th class="status-bg-n" colspan=2>반송사유</th>
                            <th class="status-bg-n"></th>
                            <th class="status-bg-n"></th>
                        </tr>
                        <tr>
                            <td>
                                <div class="col-md-12  pl-0">
                                    <div class="input-group date datetimepicker " id="div_ccrs_return_date" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm" id="ccrs_return_date" name="ccrs_return_date" DateOnly='true'   value="{{ Func::dateFormat(Func::nvl($ccrs['ccrs_return_date'],'')) ?? ''}}"/>
                                        <div class="input-group-append" data-target="#div_ccrs_return_date" data-toggle="datetimepicker">
                                            <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div>
                                </div>
                            </td>    
                            <td colspan=2>
                                <input type="text" class="form-control form-control-sm " name="ccrs_return_memo" id="ccrs_return_memo" value="{{ $ccrs['ccrs_return_memo'] ?? '' }}" >
                            </td>
                            <td></td>
                            <td></td>
                        </tr>
                        </tbody>
                    </table>    
                </td>
                <td width="40%">
                    <table class="table table-sm table-bordered table-input text-sm  text-center col-sm-12">
                        
                        <tr>
                            <th class="status-bg-n" colspan="2">신복메모</th>
                        </tr> 
                        <tr>
                            <td colspan=2><textarea class="form-control" rows="2" style="resize:none;" name="settle_memo1" id="settle_memo1">{{ $ccrs['settle_memo1'] ?? '' }}</textarea></td>
                        </tr>
                        <tr>
                            <th class="status-bg-n" colspan="2">확정메모</th>
                        </tr> 
                        <tr>
                            <td colspan=2><textarea class="form-control" rows="2" style="resize:none;" name="settle_memo1" id="settle_memo1">{{ $ccrs['settle_memo1'] ?? '' }}</textarea></td>
                        </tr>
                        <tr>
                            <th class="status-bg-n" colspan="2">실효메모</th>
                        </tr> 
                        <tr>
                            <td colspan=2><textarea class="form-control" rows="2" style="resize:none;" name="settle_memo1" id="settle_memo1">{{ $ccrs['settle_memo1'] ?? '' }}</textarea></td>
                        </tr>
                        <tr>
                            <th class="status-bg-n" colspan="2">완제메모</th>
                        </tr> 
                        <tr>
                            <td colspan=2><textarea class="form-control" rows="2" style="resize:none;" name="settle_memo1" id="settle_memo1">{{ $ccrs['settle_memo1'] ?? '' }}</textarea></td>
                        </tr>
                        </tbody>
                    </table>    
                </td>    
            </tr>
        </table>        
    </div>
</div>
<script>
$(".datetimepicker").datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
        useCurrent: false,
    });

    
// 신용회복정보저장
function saveOnlyCcrs() 
{
    console.log("DDD");
    if(!confirm("신용회복 정보만 저장하시겠습니까?")) return;
    var postdata = $('#settle_form').serialize();
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url  : "/erp/ccrsaction",
        type : "post",
        data : postdata,
        dataType: "json",
        success : function(data)
        {
            alert(data.rs_msg);
            location.reload();
        },
        error : function(xhr)
        {
            alert("[데이터 처리 오류] 전산팀에 문의하세요");
            console.log(xhr);
        }
    });

}
</script>