<div class="card-header p-0 mt-2 ml-2">
	<ul class="nav nav-tabs"  role="tablist" style="border-bottom:none; background-color:#ffffff">
		<li class="nav-item">
			<button class="nav-link active"  id="tab-1" data-toggle="pill" role="tab" aria-selected="true" onclick="ClickDirectDivClass(1)">법착 기본정보</button>
		</li>
		<li class="nav-item">
			<button class="nav-link active"  id="tab-2" data-toggle="pill" role="tab" aria-selected="false" onclick="ClickDirectDivClass(2)">제3채무자</button>
		</li>
        <li class="nav-item">
			<button class="nav-link active"  id="tab-3" data-toggle="pill" role="tab" aria-selected="false" onclick="ClickDirectDivClass(3)">사건정보</button>
		</li>
        <li class="nav-item">
			<button class="nav-link active"  id="tab-4" data-toggle="pill" role="tab" aria-selected="false" onclick="ClickDirectDivClass(4)">이미지</button>
		</li>
        <li class="nav-item">
			<button class="nav-link active"  id="tab-5" data-toggle="pill" role="tab" aria-selected="false" onclick="ClickDirectDivClass(5)">양식</button>
		</li>
	</ul>
</div>


<!-- {{ $v->app_delay_term ?? '' }} {{ $v->confirm_time ?? '' }} {{ $v->confirm_id ?? '' }} -->
<div id="law_info" style="display:none;">
    <form id="law_form" method="post" enctype="multipart/form-data" >
        <table class="table table-sm table-bordered table-input">
            <input type="hidden" name="cust_info_no" id="cust_info_no" value="{{ $cust_info_no ?? '' }}">
            <input type="hidden" name="last_trade_date" id="last_trade_date" value="{{ $last_trade_date ?? '' }}">
            <input type="hidden" name="no" id="no" value="{{ $v->no ?? '' }}">
            <input type="hidden" name="cost_row_cnt" id="cost_row_cnt" value="">

            <colgroup>
            <col width="6%" class="text-center">
            <col width="12%">
            <col width="6%" class="text-center">
            <col width="12%">
            <col width="6%" class="text-center">
            <col width="15%">
            <col width="22.5%" class="text-center">
            <col width="22.5%" class="text-center">
            </colgroup>

            <tbody>
            <tr style="height:40px;">
                <td colspan="8" style="vertical-align:bottom;">
                    <b class="pl-1">법착상세내용</b>
                    <!-- <b class="pl-1">법착상세내용</b> -->

                    @if( $manager_code!="" )
                    <b class="pl-1">- 관리지점:{{ Func::nvl($arrBranch[$manager_code]) }}</b>
                    @endif

                    @if( isset($v->confirm_time) && $v->confirm_time!="" )
                    <div class="float-right font-weight-bold ml-2">
                    ※ 결재일시 ( {{ Func::dateFormat($v->confirm_time) }} / {{ $v->confirm_nm }} )
                    </div>
                    @endif

                    @if( isset($v->branch_app_time) && $v->branch_app_time!="" )
                    <div class="float-right font-weight-bold ml-2">
                    ※ 접수대기일시 ( {{ Func::dateFormat($v->branch_app_time) }} / {{ $v->branch_app_nm }} )
                    </div>
                    @endif
                </td>
            </tr>
            <tr>
                <th>계약번호</th>
                <td>
                    <select class="form-control form-control-sm" name="loan_info_no" id="loan_info_no" {{ $action_mode!="INSERT" && isset($v->loan_info_no) ? 'disabled':''}} required>
                    <option value=''>선택</option>
                    {{ Func::printOption($array_loan_info_no,isset($v->loan_info_no)?$v->loan_info_no:'') }}   
                    </select>
                </td>
                <th>법착구분</th>
                <td>
                    <select class="form-control form-control-sm" name="law_div" id="law_div" onchange="changeDiv(this.value);"  required>
                        {{-- 기존에 없는게 많아서 뺌 {{ isset($v->law_div)?'disabled':''}} --}}
                    <option value=''>선택</option>
                    {{ Func::printOption($configArr['law_div_cd'], isset($v->law_div)?$v->law_div:'') }}
                    </select>
                </td>
                <th>법착세부</th>
                <td>
                    {{-- 기존에 없는게 많아서 뺌 {{ isset($v->law_type)?'disabled':''}} --}}
                    <select class="form-control form-control-sm" name="law_type" id="law_type" required>
                    <option value=''>선택</option>
                    {{ Func::printOption($configArr['law_type_cd'], isset($v->law_type)?$v->law_type:'') }}
                    {{-- {{ Func::printOption(isset($v->law_div) ? $arrayLawType[$v->law_div]:array(),isset($v->law_type) ? $v->law_type:'') }} --}}
                    </select>
                </td>
                {{-- <th class="text-center">신청자의견</th> --}}
                <th class="text-center" colspan="2">메모</th>
            </tr>

            {{--
            <tr>
                <th>승인여부</th>
                <td>
                    @if( $action_mode=="INSERT" || $v->status=="" || Func::nvl($my_permit_status[$v->status],'')!="" )
                    <select class="form-control form-control-sm" name="status" id="status" required>
                    <option value=''>선택</option>
                    {{ Func::printOption($my_permit_status, Func::nvl($v->status,'')) }}
                    </select>
                    @else
                    <input type="text" class="form-control form-control-sm" name="status_nm" id="status_nm" value="{{ Func::nvl($configArr['law_status_cd'][$v->status],$v->status) }}" readonly>
                    @endif
                </td>
                <th>진행상태</th>
                <td>
                    <select class="form-control form-control-sm " name="law_proc_status_cd" id="law_proc_status_cd">
                    <option value=''>선택</option>
                    {{ Func::printOption($arr_law_proc_status_cd,isset($v->law_proc_status_cd)?$v->law_proc_status_cd:'') }}   
                    </select>
                    <input type="hidden" name="old_law_proc_status_cd" value="{{ $v->law_proc_status_cd ?? '' }}">
                </td>
                <th>당사자명</th>
                <td><input type="text" class="form-control form-control-sm text-center " name="nsf_name" id="nsf_name"  value="{{ $v->nsf_name ?? '' }}" placeholder="미기재시회사명으로자동세팅"></td>
                
                <td rowspan=3><textarea class="form-control form-control-sm" name="law_app_memo" id="law_app_memo" style="height:88px;font-size:0.8rem;">{{$v->law_app_memo ?? ''}}</textarea></td>
                <td rowspan=3><textarea class="form-control form-control-sm" name="law_memo" id="law_memo" style="height:88px;font-size:0.8rem;">{{$v->law_memo ?? ''}}</textarea></td>

            </tr> 
            --}}

            <tr>
                <th>신청일</th>
                <td>
                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0" id="law_app_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input branch-disabled" data-target="#law_app_date" name="law_app_date" id="law_app_date" value="{{ $v->law_app_date ?? '' }}" DateOnly="true" size="6">
                        <div class="input-group-append" data-target="#law_app_date" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                        </div>
                    </div>
                </td>
                <th>확정일</th>
                <td>
                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0" id="law_confirm_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input branch-disabled" data-target="#law_confirm_date" name="law_confirm_date" id="law_confirm_date" value="{{ $v->law_confirm_date ?? '' }}" DateOnly="true" size="6">
                        <div class="input-group-append" data-target="#law_confirm_date" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                        </div>
                    </div>
                    <input type="hidden" name="old_law_confirm_date" id="old_law_confirm_date" value="{{ $v->law_confirm_date ?? '' }}">
                </td>
                <th>취하일</th>
                <td>
                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0" id="law_cancel_date" data-target-input="nearest">
                        <input type="text" class="form-control form-control-sm datetimepicker-input branch-disabled" data-target="#law_cancel_date" name="law_cancel_date" id="law_cancel_date" value="{{ $v->law_cancel_date ?? '' }}" DateOnly="true" size="6">
                        <div class="input-group-append" data-target="#law_cancel_date" data-toggle="datetimepicker">
                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                        </div>
                    </div>
                </td>

                {{-- <td rowspan=3><textarea class="form-control form-control-sm" name="law_app_memo" id="law_app_memo" style="height:88px;font-size:0.8rem;">{{$v->law_app_memo ?? ''}}</textarea></td> --}}
                <td rowspan="3" colspan="2"><textarea class="form-control form-control-sm" name="law_memo" id="law_memo" style="height:88px;font-size:0.8rem;">{{$v->law_memo ?? ''}}</textarea></td>
            </tr>

            <tr>
                <th>법착상태</th>
                <td>
                    <select class="form-control form-control-sm selectpicker" data-size="5" name="law_proc_status_cd" data-live-search="true" id="law_proc_status_cd" title="선택">
                        {{ Func::printOption($configArr['law_status_cd'], isset($v->law_proc_status_cd)?$v->law_proc_status_cd:'') }}
                    </select>    
                </td>
                <th>결재상태</th>
                <td>
                    <select class="form-control form-control-sm selectpicker" data-size="5" name="law_confirm_status" data-live-search="true" id="law_confirm_status" title="선택">
                        {{ Func::printOption($configArr['confirm_cd'], isset($v->law_confirm_status)?$v->law_confirm_status:'') }}
                    </select>
                </td>
                <th>법착결과</th>
                <td>
                    <select class="form-control form-control-sm selectpicker" data-size="5" name="law_final" data-live-search="true" id="law_final" title="선택">
                        {{ Func::printOption($configArr['law_final_cd'], isset($v->law_final)?$v->law_final:'') }}
                    </select>
                </td>
            </tr>

            <tr>
                <th>청구원금</th>
                <td><input type="text" class="form-control form-control-sm text-right moneyformat branch-disabled" name="law_won_mny" id="law_won_mny" onkeyup="onlyNumber(this);" value="{{ $v->law_won_mny ?? '' }}"></td>
                <th>청구금액</th>
                <td><input type="text" class="form-control form-control-sm text-right moneyformat branch-disabled" name="law_app_mny" id="law_app_mny" onkeyup="onlyNumber(this);" value="{{ $v->law_app_mny ?? '' }}"></td>
                <th>법무법인</th>
                <td><input type="text" class="form-control form-control-sm branch-disabled" name="law_firm" id="law_firm"  value="{{ $v->law_firm ?? '' }}"></td>
            </tr>

            <tr style="height:40px;">
                <td colspan="8" style="vertical-align:bottom;">
                    <b class="pl-1">사건정보</b>
                </td>
            </tr>
            <tr>
            <td colspan="8">
                    <div class="underline">
                        <table class="table table-sm table-input mb-0">

                            <colgroup>
                                <col width="6%" class="text-center">
                                <col width="12%">
                                <col width="6%" class="text-center">
                                <col width="12%">
                                <col width="6%" class="text-center">
                                <col width="10%">
                                <col width="10%" class="text-center">
                                <col width="13%" class="text-center">
                            </colgroup>

                            <tbody>
                            <tr>
                                <th>당사자명</th>
                                <td>
                                    <input type="text" class="form-control form-control-sm text-center " name="target_name" id="target_name" value="{{ $v->target_name ?? '' }}">
                                </td>
                                <th>관할법원</th>
                                <td>
                                    <select class="form-control form-control-sm selectpicker" data-size="10" name="court_cd" data-live-search="true" id="court_cd" title="선택">{{-- branch-disabled --}}
                                        <option value="">법원선택</option>
                                    {{ Func::printOption($configArr['court_cd'],isset($v->court_cd)?$v->court_cd:'') }}   
                                    </select>
                                </td>
                                <th>사건번호</th>
                                <td colspan=3>
                                    <div class="row m-0 p-0">
                                    <div class="row col-md-12 m-0 p-0">
                                    
                                        <input type="text" class="form-control form-control-sm col-md-2 mr-1" name="event_year" id="event_year" onkeyup="onlyNumber(this);" maxlength="4" value="{{ $v->event_year ?? '' }}" placeholder="년도">{{-- branch-disabled --}}
                                        <input type="text" class="form-control form-control-sm col-md-2 mr-1" name="event_cd" id="event_cd" value="{{ $v->event_cd ?? '' }}" placeholder="구분">
                                        <input type="text" class="form-control form-control-sm col-md-2 mr-1" name="event_no" id="event_no" maxlength=7 onkeyup="onlyNumber(this);" value="{{ $v->event_no ?? '' }}" placeholder="사건번호">

                                        {{-- <div class="col-md-2 ml-2 mr-0 pr-0 row align-self-center form-check">
                                            <input type='checkbox' class='form-check form-check-input' id='auto_nsf' name='auto_nsf' value='Y' {{ isset($v->auto_nsf) ? Func::echoChecked('Y',Func::nvl($v->auto_nsf,'')) : '' }}>
                                            <label class="form-check-label font-weight-bold text-xs" for="auto_nsf">자동조회</label>
                                        </div> --}}

                                    </div>
                                    </div>

                                </td>
                                <td></td><td></td>
                                <td class="text-right">
                                    {{-- 
                                    <button type="button" class="btn btn-default btn-xs text-xs mr-1" onclick="window.open('https://safind.scourt.go.kr/sf/mysafind.jsp','','width=1000,height=1000,scrollbars=yes,top=10,left=10')">나의사건조회</button>
                                    <button type="button" class="btn btn-default btn-xs text-xs mr-1" onclick="window.open('https://www.courtauction.go.kr','','width=1000,height=1000,scrollbars=yes,top=10,left=10')">법원경매</button>
                                    --}}
                                </td>
                            </tr>  

                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>  

            {{--
            <tr>
                <th>주소</th>
                <td colspan="7">
                    <div class="input-group">
                        <div class="input-group col-md-2 pl-0">
                            <input type="text" class="form-control" name="zip" id="zip" numberOnly="true" value="{{ $v->zip ?? '' }}" readOnly>
                            <span class="input-group-btn input-group-append">
                                <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('zip', 'addr1', 'addr2', $('#addr1').val())">검색</button>
                            </span>
                        </div>
                        <input type="hidden" name="addr_cd" id="addr_cd" value="">
                        <input type="text" class="form-control form-control-sm col-md-3 ml-1" name="addr1" id="addr1" value="{{ $v->addr1 ?? '' }}" readonly>
                        <input type="text" class="form-control form-control-sm col-md-3 ml-1" name="addr2" id="addr2" value="{{ $v->addr2 ?? '' }}" maxlength="100">
                        
                        <button type="button" class="btn btn-secondary btn-xs lawBtn ml-1" id="lawBtn_1" onclick="setAddr('zip', 'addr1', 'addr2', '{{$addr->zip1 ?? ''}}', '{{$addr->addr11 ?? ''}}', '{{$addr->addr12 ?? ''}}'); setAddrInput('1');">실거주</button>
                        <button type="button" class="btn btn-secondary btn-xs lawBtn ml-1" id="lawBtn_2" onclick="setAddr('zip', 'addr1', 'addr2', '{{$addr->zip2 ?? ''}}', '{{$addr->addr21 ?? ''}}', '{{$addr->addr22 ?? ''}}'); setAddrInput('2');">등본</button>
                        <button type="button" class="btn btn-secondary btn-xs lawBtn ml-1" id="lawBtn_3" onclick="setAddr('zip', 'addr1', 'addr2', '{{$addr->zip3 ?? ''}}', '{{$addr->addr31 ?? ''}}', '{{$addr->addr32 ?? ''}}'); setAddrInput('3');">직장</button>
                        <button type="button" class="btn btn-secondary btn-xs lawBtn ml-1" id="lawBtn_4" onclick="setAddr('zip', 'addr1', 'addr2', '{{$addr->zip4 ?? ''}}', '{{$addr->addr41 ?? ''}}', '{{$addr->addr42 ?? ''}}'); setAddrInput('4');">기타</button>
                    </div>
                </td>
            </tr>
            --}}



            <tr style="height:40px;">
                <td colspan="8" style="vertical-align:bottom;">
                    <b class="pl-1">당사자정보</b>
                </td>
            </tr>
            <tr>
                <td colspan="8">
                    <div class="underline">
                        <table class="table table-sm table-input mb-0">
                            <colgroup>
                                <col width="6%" class="text-center">
                                <col width="12%">
                                <col width="6%" class="text-center">
                                <col width="12%">
                                <col width="6%" class="text-center">
                                <col width="6%">
                                <col width="6%" class="text-center">
                                <col width="15%">
                                <col width="22%" class="text-center">
                            </colgroup>

                            <tbody>
                            <tr>
                                <th>대상자명</th>
                                <td>
                                    <input type="text" class="form-control form-control-sm text-center " name="target_name" id="target_name"  value="{{ $v->target_name ?? '' }}">
                                </td>
                                <th>주민번호</th>
                                <td class="text">
                                    <input type="text" class="form-control form-control-sm text-center" onkeyup="onlyNumber(this);" name="target_ssn" id="target_ssn"  value="{{ $v->target_ssn ?? '' }}">
                                </td>
                                <th>개인법인</th>
                                <td class="text">
                                    <select class="form-control form-control-sm selectpicker" data-size="5" name="target_com_div" data-live-search="true" id="target_com_div" title="선택">
                                    {{ Func::printOption($configArr['com_div'],isset($v->target_com_div)?$v->target_com_div:'') }}
                                    </select>
                                </td>
                                <th>대표자명</th>
                                <td class="text">
                                    <input type="text" class="form-control form-control-sm text-center " name="target_owner_name" id="target_owner_name"  value="{{ $v->target_owner_name ?? '' }}">
                                </td>
                                <td class="text-right" colspan="2">
                                    <!-- <button type="button" class="btn btn-default btn-xs text-xs branch-disabled" id="law_cost_add_btn"><i class="fas fa-plus-circle p-1 text-green text-xs"></i>대상자선택</button> -->
                                </td>
                            </tr>  

                            <tr>
                                <th>송달주소</th>
                                <td colspan="9">
                                    {{-- 우편번호 --}}
                                    <div class="input-group">
                                        <div class="input-group col-md-2 pl-0">
                                            <input type="text" class="form-control" name="target_zip" id="target_zip" numberOnly="true" value="{{ $v->target_zip ?? '' }}" readOnly>
                                            <span class="input-group-btn input-group-append">
                                                <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('target_zip', 'target_addr1', 'target_addr2', $('#target_addr1').val())">검색</button>
                                            </span>
                                        </div>
                                        <input type="hidden" name="addr_cd" id="addr_cd" value="">
                                        <input type="text" class="form-control form-control-sm col-md-3 ml-1" name="target_addr1" id="target_addr1" value="{{ $v->target_addr1 ?? '' }}" readonly>
                                        <input type="text" class="form-control form-control-sm col-md-3 ml-1" name="target_addr2" id="target_addr2" value="{{ $v->target_addr2 ?? '' }}" maxlength="100">
                                        
                                        <button type="button" class="btn btn-secondary btn-xs lawBtn ml-1" id="lawBtn_1" onclick="setAddr('target_zip', 'target_addr1', 'target_addr2', '{{$addr->zip1 ?? ''}}', '{{$addr->addr11 ?? ''}}', '{{$addr->addr12 ?? ''}}'); setAddrInput('1');">실거주</button>
                                        <button type="button" class="btn btn-secondary btn-xs lawBtn ml-1" id="lawBtn_2" onclick="setAddr('target_zip', 'target_addr1', 'target_addr2', '{{$addr->zip2 ?? ''}}', '{{$addr->addr21 ?? ''}}', '{{$addr->addr22 ?? ''}}'); setAddrInput('2');">등본</button>
                                        <button type="button" class="btn btn-secondary btn-xs lawBtn ml-1" id="lawBtn_3" onclick="setAddr('target_zip', 'target_addr1', 'target_addr2', '{{$addr->zip3 ?? ''}}', '{{$addr->addr31 ?? ''}}', '{{$addr->addr32 ?? ''}}'); setAddrInput('3');">직장</button>
                                        <button type="button" class="btn btn-secondary btn-xs lawBtn ml-1" id="lawBtn_4" onclick="setAddr('target_zip', 'target_addr1', 'target_addr2', '{{$addr->zip4 ?? ''}}', '{{$addr->addr41 ?? ''}}', '{{$addr->addr42 ?? ''}}'); setAddrInput('4');">기타</button>
                                    </div>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>


            <tr style="height:40px;">
                <td colspan="2" style="vertical-align:bottom;">
                    <b class="pl-1">법비용</b>
                </td>
                <td colspan="8" class="text-right" style="vertical-align:bottom;">
                    <button type="button" class="btn btn-default btn-xs text-xs branch-disabled" onclick="addRow();" id="law_cost_add_btn"><i class="fas fa-plus-circle p-1 text-green text-xs"></i>법비용 추가</button>
                </td>
            </tr>
            <tr>
                <th style="background-color:#e8f3ea">법비용</th>
                <td colspan="12">
                    <div class="underline">
                        <table class="table table-sm table-input mb-0 table-hover" id="law_cost_table">

                            <colgroup>
                            <col width="4.6%"></col>
                            <col width="3.1%"></col>
                            <col width="5.8%"></col>
                            <col width="9.2%"></col>
                            <col width="5.5%"></col>
                            <col width="5.5%"></col>
                            <col width="5.5%"></col>
                            <col width="5.5%"></col>
                            <col width="5.5%"></col>
                            <col width="5.5%"></col>
                            <col width="5.5%"></col>
                            <col width="5.5%"></col>
                            <col width="5.5%"></col>
                            <col width="5.5%"></col>
                            <col width="5.5%"></col>
                            <col width="5.5%"></col>
                            <col width="4%"></col>
                            <col width="4%"></col>
                            <col width="6.5%"></col>
                            </colgroup>

                            <thead>
                                <tr>
                                    <th class="text-center">법비용키</th>
                                    <th class="text-center">구분</th>
                                    <th class="text-center">결제<br>방법</th>
                                    <th class="text-center">발생일</th>
                                    <th class="text-center">송달료</th>
                                    <th class="text-center">인지대</th>
                                    <th class="text-center">증지대</th>
                                    <th class="text-center">등본대</th>

                                    <th class="text-center">집행관</th>
                                    <th class="text-center">등록<br>면허세</th>
                                    <th class="text-center">교육세</th>

                                    <th class="text-center">공증료</th>
                                    <th class="text-center">보증<br>보험</th>
                                    <th class="text-center">공탁금</th>

                                    <th class="text-center">해지<br>비용</th>
                                    <th class="text-center">보관금</th>
                                    <th class="text-center">기타</th>
                                    <th class="text-center">회수</th>
                                    <th class="text-center">처리</th>
                                   
                                </tr>
                            </thead>
                            <tbody id="law_cost_row">
                            @if(!empty($v2))
                                @foreach( $v2 as $idx => $c )
                                <tr id="have_cost">
                                    <td class="text-center">{{ $c->no }}</td>
                                    <td class="text-center" id="law_cost_type_{{ $c->cost_type }}">{{ Func::getArrayName($configArr['law_cost_type'],$c->cost_type) }}</td>
                                    <td class="text-center">{{ Func::nvl($configArr['trade_cost_path'][$c->trade_cost_path]) }}</td>
                                    <td class="text-center">{{ Func::dateFormat($c->trade_date) }}</td>

                                    <td class="text-right">{{ Func::numberReport($c->postage_am) }}</td>
                                    <td class="text-right">{{ Func::numberReport($c->stamptax_am) }}</td>
                                    <td class="text-right">{{ Func::numberReport($c->certitax_am) }}</td>
                                    <td class="text-right">{{ Func::numberReport($c->certissu_am) }}</td>

                                    <td class="text-right">{{ Func::numberReport($c->enforce_am) }}</td>
                                    <td class="text-right">{{ Func::numberReport($c->registtax_am) }}</td>
                                    <td class="text-right">{{ Func::numberReport($c->edu_am) }}</td>

                                    <td class="text-right">{{ Func::numberReport($c->notarial_am) }}</td>
                                    <td class="text-right">{{ Func::numberReport($c->insurance_am) }}</td>
                                    <td class="text-right">{{ Func::numberReport($c->deposit_am) }}</td>

                                    <td class="text-right">{{ Func::numberReport($c->cancel_am) }}</td>
                                    <td class="text-right">{{ Func::numberReport($c->lawdeposit_am) }}</td>
                                    <td class="text-right">{{ Func::numberReport($c->etc_am) }}</td>

                                    <td class="text-center">@if($c->return_target=="Y")<i class='fas fa-check text-green'></i>@endif</td>

                                    <td class="text-center" title="{{ Func::dateFormat($c->save_time) }} {{ Func::getArrayName($array_user,$c->save_id) }}">
                                        <button onclick="lawAction('DEL_COST',['{{ $c->no }}','{{ $c->loan_info_trade_no }}']);" type="button" class="btn btn-default btn-xs branch-disabled"><i class="fas fa-minus-circle p-1 text-red text-xs"></i>삭제</button>
                                    </td>
                                </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>

            
            <tr style="height:40px;">
                <td colspan="8" style="vertical-align:bottom;">
                    <b class="pl-1">법취하정보</b>
                </td>
            </tr>
            <tr>
            <th style="background-color:#fff0c7">취하정보</th>
            <td colspan="9">
                    <div class="underline">
                        <table class="table table-sm table-input mb-0" id="law_cost_table">
                            <thead>
                                <tr>
                                    <th class="text-center w-10">상태</th>
                                    <th class="text-center w-10">처리자</th>
                                    <th class="text-center w-10">처리일시</th>
                                    <th class="text-center w-70">메모</th>
                                </tr>
                            </thead>
                            <tbody>
                                @if(!empty($v->cancel_app_time))
                                    <tr>
                                        <td class="text-center">요청</td>
                                        <td class="text-center">{{ Func::getArrayName($array_user,$v->cancel_app_id) ?? ''}}</td>
                                        <td class="text-center">{{ Func::dateFormat($v->cancel_app_time) ?? ''}}</td>
                                        <td class="text-center">{{ $v->cancel_app_memo ?? ''}}</td>
                                    </tr>
                                @endif
                                @if(!empty($v->cancel_confirm_time) && $v->cancel_status!="A")
                                <tr>
                                    <td class="text-center">@if($v->cancel_status == "Y")결재@elseif($v->cancel_status == "N")요청취소/부결@endif</td>
                                    <td class="text-center">{{ Func::getArrayName($array_user,$v->cancel_confirm_id) ?? ''}}</td>
                                    <td class="text-center">{{ Func::dateFormat($v->cancel_confirm_time) ?? ''}}</td>
                                    <td class="text-center">{{ $v->cancel_confirm_memo ?? ''}}</td>
                                </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </td>
            </tr>


            <tr class="underline">
            <td colspan="10" class="text-right pt-3">
                @if(isset($v->no) &&  Func::funcCheckPermit("A136","A") && (empty($v->cancel_status) ||  $v->cancel_status == 'N') )
                    <button class="btn btn-sm bg-info mr-1" onclick="getPopUp('/erp/lawcancelform?law_no={{ $v->no ?? '' }}','name','get');" type="button">취하/해지 요청</button>
                @endif
                
                @if(isset($v->no) &&  Func::funcCheckPermit("A236","A") && $v->cancel_status == 'A' )
                    <button class="btn btn-sm bg-info mr-1" onclick="getPopUp('/erp/lawcancelform?law_no={{ $v->no ?? '' }}','name','get');" type="button">취하/해지 결재</button>
                @endif

                @if( $action_mode!="NONE" || ($v->law_div=='I' && $v->law_type!='I2'))
                    <button class="btn btn-sm bg-danger mr-1" onclick="lawAction('DEL');" type="button">삭제</button>
                    <button class="btn btn-sm bg-lightblue mr-1" onclick="lawAction('{{ $mode }}');" type="button">저장</button>
                @endif
            </td>
            </tr>
        </table>
    </form>
</div>

<div id="debtor_info" style="display:none;">
    <form id="debtor_form" method="post" enctype="multipart/form-data" >
        <table class="table table-sm table-bordered table-input">
            <input type="hidden" name="cust_info_no" id="cust_info_no" value="{{ $cust_info_no ?? '' }}">
            <input type="hidden" name="loan_info_no" id="loan_info_no" value="{{ $v->loan_info_no ?? '' }}">
            <input type="hidden" name="loan_info_law_no" id="loan_info_law_no" value="{{ $v->no ?? '' }}">

            <tbody>
                <tr style="height:40px;">
                    <td colspan="1" style="vertical-align:bottom;">
                        <b class="pl-1">제3채무자</b>
                    </td>
                    <td colspan="8" class="text-right" style="vertical-align:bottom;">
                        <button type="button" class="btn btn-default btn-xs text-xs branch-disabled" onclick="$('#searchRegistModal').modal({ backdrop:'', keyboard:false});" id="law_cost_add_btn"><i class="fas fa-search p-1 text text-xs"></i>등기부등본 검색</button>
                        <button type="button" class="btn btn-default btn-xs text-xs ml-2 branch-disabled" onclick="addDebtor();" id="law_cost_add_btn"><i class="fas fa-plus-circle p-1 text-green text-xs"></i>제3채무자 추가</button>
                    </td>
                </tr>
                <tr>
                    <td colspan="8" id="debtor_first">
                        @foreach($debtor as $key => $val)
                            <ul class="debtor" id="debtor_{{ $key ?? 0 }}" style="list-style:none; padding-left: 0px;">
                                <li>
                                    <div class="underline">
                                        <table class="table table-sm table-input mb-0">

                                            <colgroup>
                                                <col width="8%" class="text-center">
                                                <col width="21%">
                                                <col width="8%" class="text-center">
                                                <col width="33%">
                                                <col width="8%" class="text-center">
                                                <col width="12%">
                                                <col width="6%">
                                            </colgroup>
                                            <input type="hidden" name="dseq_{{ $key ?? 0 }}" value="{{ $val->seq ?? 1 }}">
                                            <input type="hidden" name="debtor_key_{{ $key ?? 0 }}" value="{{ $val->debtor_key ?? 1 }}">
                                            <tbody>
                                            <tr>
                                                <th>제3채무자명</th>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm col-md-8 branch-disabled" name="debtor_name_{{ $key ?? 0 }}" id="debtor_name_{{ $key ?? 0 }}"  value="{{ $val->debtor_name ?? '' }}">
                                                </td>
                                                <th>안분비율</th>
                                                <td class="text">
                                                    <input type="text" class="form-control form-control-sm col-md-4 floatnum branch-disabled" name="distribute_ratio_{{ $key ?? 0 }}" id="distribute_ratio_{{ $key ?? 0 }}"  value="{{ $val->distribute_ratio ?? '' }}">
                                                </td>
                                                <th>안분금액</th>
                                                <td class="text">
                                                    <input type="text" class="form-control form-control-sm moneyformat col-md-6 branch-disabled" name="distribute_money_{{ $key ?? 0 }}" id="distribute_money_{{ $key ?? 0 }}" onkeyup="onlyNumber(this);"  value="{{ $val->distribute_money ?? '' }}">
                                                </td>
                                                <td rowspan="2" class="text-right">
                                                    @if(isset($val->seq))
                                                    <button class="btn btn-sm bg-danger mr-1" onclick="deleteDebtor('{{ $val->seq ?? '' }}', '{{ $v->no ?? '' }}', '{{ $v->loan_info_no ?? '' }}');" type="button">삭제</button>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>대표명</th>
                                                <td>
                                                    <input type="text" class="form-control form-control-sm col-md-8 branch-disabled" name="owner_name_{{ $key ?? 0 }}" id="owner_name_{{ $key ?? 0 }}"  value="{{ $val->owner_name ?? '' }}">
                                                </td>
                                                <th>주소</th>
                                                <td >
                                                    <div class="input-group">
                                                        <input type="text" class="form-control form-control-sm col-md-12" name="addr1_{{ $key ?? 0 }}" id="addr1_{{ $key ?? 0 }}" value="{{ $val->addr1 ?? '' }}">
                                                    </div>
                                                </td>
                                                <th>송달일</th>
                                                <td class="text">
                                                    <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0" id="delivery_date_{{ $key ?? 0 }}" data-target-input="nearest">
                                                        <input type="text" class="form-control form-control-sm datetimepicker-input branch-disabled" data-target="#delivery_date_{{ $key ?? 0 }}" name="delivery_date_{{ $key ?? 0 }}" id="delivery_date_{{ $key ?? 0 }}" value="{{ $val->delivery_date ?? '' }}" DateOnly="true" size="6">
                                                        <div class="input-group-append" data-target="#delivery_date_{{ $key ?? 0 }}" data-toggle="datetimepicker">
                                                            <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </div>
                                </li>
                            </ul>
                        @endforeach
                    </td>
                </tr>
                <tr class="underline">
                    <td colspan="10" class="text-right pt-3">
                        <button class="btn btn-sm bg-lightblue mr-1" onclick="debtorAction('{{ $mode }}');" type="button">저장</button>
                    </td>
                </tr>
            </tbody>

        </table>
    </form>
</div>

<div id="status_info" style="display:none;">
    <form id="status_form" method="post" enctype="multipart/form-data" >
        <table class="table table-sm table-bordered table-input">
            <input type="hidden" name="cust_info_no" id="cust_info_no" value="{{ $cust_info_no ?? '' }}">
            <input type="hidden" name="loan_info_no" id="loan_info_no" value="{{ $v->loan_info_no ?? '' }}">
            <input type="hidden" name="loan_info_law_no" id="loan_info_law_no" value="{{ $v->no ?? '' }}">

            <tbody>
                <tr style="height:40px;">
                    <td colspan="8" style="vertical-align:bottom;">
                        <b class="pl-1">사건정보</b>
                    </td>
                </tr>
                <tr>
                    <td colspan="8">
                        <div class="underline">
                            <table class="table table-sm table-input mb-0">

                                <colgroup>
                                <col width="10%" class="text-center">
                                <col width="15%">
                                <col width="10%" class="text-center">
                                <col width="15%">
                                <col width="10%" class="text-center">
                                <col width="15%">
                                <col width="10%" class="text-center">
                                <col width="15%">
                                </colgroup>

                                <tbody>
                                <tr>
                                    <th>당사자명</th>
                                    <td class="text">
                                        <input type="text" class="form-control form-control-sm text-center " value="{{ $v->target_name ?? '' }}" readonly>
                                    </td>
                                    <th>관할법원</th>
                                    <td class="text">
                                        <input type="text" class="form-control form-control-sm col-md-12 branch-disabled" value="{{ $configArr['court_cd'][$v->court_cd ?? ''] ?? '' }}" readonly>
                                    </td>
                                    <th>사건번호</th>
                                    <td class="text" colspan="3">
                                        <div class="row m-0 p-0">
                                            <div class="row col-md-12 m-0 p-0">
                                                <input type="text" class="form-control form-control-sm col-md-3 mr-1" value="{{ $v->event_year ?? '' }}" readonly>
                                                <input type="text" class="form-control form-control-sm col-md-3 mr-1" value="{{ $v->event_cd ?? '' }}" readonly>
                                                <input type="text" class="form-control form-control-sm col-md-5 mr-1" value="{{ $v->event_no ?? '' }}" readonly>
                                            </div>
                                        </div>
                                    </td>
                                    
                                </tr>  
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>

                <tr style="height:40px;">
                    <td colspan="8" style="vertical-align:bottom;">
                        <b class="pl-1">사건최신화정보</b>
                    </td>
                </tr>
                <tr>
                    <td colspan="8">
                        <div class="underline">
                            <table class="table table-sm table-input mb-0">

                                <colgroup>
                                <col width="10%" class="text-center">
                                <col width="15%">
                                <col width="10%" class="text-center">
                                <col width="15%">
                                <col width="10%" class="text-center">
                                <col width="15%">
                                <col width="10%" class="text-center">
                                <col width="15%">
                                </colgroup>

                                <tbody>
                                <tr>
                                    <th>접수일</th>
                                    <td class="text">
                                        <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0" id="event_app_date" data-target-input="nearest">
                                            <input type="text" class="form-control form-control-sm datetimepicker-input branch-disabled" data-target="#event_app_date" name="event_app_date" id="event_app_date" value="{{ $v->event_app_date ?? '' }}" DateOnly="true" size="6">
                                            <div class="input-group-append" data-target="#event_app_date" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </td>
                                    <th>조회사유</th>
                                    <td class="text">
                                        
                                    </td>
                                    {{-- <th>최신화결과</th>
                                    <td class="text">
                                        <select class="form-control form-control-sm selectpicker" data-size="5" name="event_renew_result" data-live-search="true" id="event_renew_result" title="선택">
                                        {{ Func::printOption($configArr['final_cd'],isset($v->event_renew_result)?$v->event_renew_result:'') }}
                                        </select>
                                    </td> --}}
                                </tr>  

                                <tr>
                                    <th>종료일</th>
                                    <td>
                                        <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0" id="event_end_date" data-target-input="nearest">
                                            <input type="text" class="form-control form-control-sm datetimepicker-input branch-disabled" data-target="#event_end_date" name="event_end_date" id="event_end_date" value="{{ $v->event_end_date ?? '' }}" DateOnly="true" size="6">
                                            <div class="input-group-append" data-target="#event_end_date" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </td>
                                    <th>종국결과</th>
                                    <td class="text">
                                        <select class="form-control form-control-sm selectpicker" data-size="5" name="event_final_result" data-live-search="true" id="event_final_result" title="선택">
                                        {{ Func::printOption($configArr['event_final_cd'], isset($v->event_final_result)?$v->event_final_result:'') }}
                                        </select>
                                    </td>
                                    <th>종국결과일</th>
                                    <td class="text">
                                        <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-0 mt-0" id="event_final_date" data-target-input="nearest">
                                            <input type="text" class="form-control form-control-sm datetimepicker-input branch-disabled" data-target="#event_final_date" name="event_final_date" id="event_final_date" value="{{ $v->event_final_date ?? '' }}" DateOnly="true" size="6">
                                            <div class="input-group-append" data-target="#event_final_date" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>

                <tr style="height:40px;">
                    <td colspan="1" style="vertical-align:bottom;">
                        <b class="pl-1">관련사건</b>
                    </td>
                    <td colspan="8" class="text-right" style="vertical-align:bottom;">
                        <!-- 아직 기능 없음 -->
                        <!-- <button type="button" class="btn btn-default btn-xs text-xs branch-disabled" onclick="" id="law_cost_add_btn"><i class="fas fa-plus-circle p-1 text-green text-xs"></i>관련법조치 찾기</button> -->
                    </td>
                </tr>
                <tr>
                    <td colspan="8">
                        <div class="underline">
                            <table class="table table-sm table-input mb-0">

                                <colgroup>
                                <col width="10%" class="text-center">
                                <col width="15%">
                                <col width="10%" class="text-center">
                                <col width="15%">
                                <col width="10%" class="text-center">
                                <col width="15%">
                                <col width="10%" class="text-center">
                                <col width="15%">
                                </colgroup>

                                <tbody>
                                <tr>
                                    <th>관련법착번호</th>
                                    <td>
                                        <input type="text" class="form-control form-control-sm branch-disabled" name="law_rel_key" id="law_rel_key"  value="{{ $v->law_rel_key ?? '' }}">
                                    </td>
                                    <th>관련법착구분</th>
                                    <td class="text">
                                        <select class="form-control form-control-sm selectpicker" data-size="12" name="law_rel_cd" id="law_rel_cd" data-live-search="true" title="선택">
                                            {{ Func::printOption($configArr['law_rel_cd'], isset($v->law_rel_cd)?$v->law_rel_cd:'') }}
                                        </select>
                                    </td>
                                    <th>집행권원서류명</th>
                                    <td class="text">
                                        <select class="form-control form-control-sm selectpicker" data-size="12" name="law_doc_cd" id="law_doc_cd" data-live-search="true" title="선택">
                                            {{ Func::printOption($configArr['law_doc_cd'], isset($v->law_doc_cd)?$v->law_doc_cd:'') }}
                                        </select>
                                    </td>
                                    <th>집행권원표시</th>
                                    <td class="text">
                                        <input type="text" class="form-control form-control-sm branch-disabled" name="law_exec_div" id="law_exec_div"  value="{{ $v->law_exec_div ?? ''}}">
                                    </td>
                                </tr>  
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>

                <tr class="underline">
                    <td colspan="10" class="text-right pt-3">
                        <button class="btn btn-sm bg-lightblue mr-1" onclick="eventAction('{{ $mode }}');" type="button">저장</button>
                    </td>
                </tr>
            </tbody>

        </table>
    </form>
</div>


    <div id="law_img" style="display:none;">

        <b>파일 ( 기타파일 업로드시 해당 파일은 다운로드만 가능합니다 )</b>
        <!-- BODY -->
        <button type="button" class="btn btn-xs btn-outline-info float-right mb-1" onclick="getCustData('law',{{ $loan_info_no ?? 0 }},'',{{ $v->no ?? 0 }}, 4);"><i class="fa fa-plus-square text-info mr-1"></i>파일추가</button>
        <table class="table table-sm table-hover loan-info-table card-secondary card-outline">
            <colgroup>
                <col width="15%"/>
                <col width="15%"/>
                <col width="15%"/>
                <col width="20%"/>
                <col width="15%"/>
            </colgroup>
            <thead>
                <tr>
                    <th class="text-center">파일구분</th>
                    <th class="text-center">계약번호</th>
                    <th class="text-center">등록자</th>
                    <th class="text-center">등록일시</th>
                    <th class="text-center">파일</th>
                </tr>
            </thead>
            <tbody>
                @if(isset($img))
                    @forelse( $img as $idx => $val )
                        <tr onclick="getCustData('law',{{ $loan_info_no ?? 0 }},'',{{ $v->no ?? 0 }}, 4, {{ $val->no ?? 0 }});" @if( isset($selected_img[0]->no) && $selected_img[0]->no == $val->no ) bgcolor="FFDDDD" @endif >
                            <td class="text-center">{{ Func::getArrayName($arr_task_name, $val->taskname) }}</td>
                            <td class="text-center">{{ $val->loan_info_no }}</td>
                            <td class="text-center">{{ $val->worker_id }}</td>
                            <td class="text-center">{{ $val->save_time }}</td>
                            <td class="text-center" onClick="event.cancelBubble=true;">{{$val->origin_filename}}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class='text-center p-4'>등록된 파일이 없습니다.</td>
                        </tr>
                    @endforelse
                @endif
                <tr><td colspan="13"></td></tr>
            </tbody>
        </table>

        <div class="row">
            <div class="col-md-6">
                <form id="img_form" name="img_form" method="post" enctype="multipart/form-data" action="">
                @csrf
                    <input type="hidden" name="cust_info_no" value="{{ $cust_info_no ?? '' }}">
                    <input type="hidden" name="mode" value="{{ $img_mode?? '' }}">
                    <input type="hidden" name="img_no" value="{{ $selected_img[0]->no ?? '' }}">
                    <input type="hidden" name="loan_info_no" value="{{ $loan_info_no ?? '' }}">
                    <table class="table table-sm table-bordered table-input text-xs">
                        <colgroup>
                            <col width="25%"/>
                            <col width="75%"/>
                        </colgroup>

                        <tbody>
                            <tr>
                                <th>구분</th>
                                <td>
                                    <select class="form-control form-control-sm text-xs col-md-4" onchange='change_div(this)' name="taskname" id="taskname">
                                    <option value=''>구분선택</option>
                                        {{ Func::printOption($arr_task_name, 'LAW') }}
                                    </select>
                                </td>
                            </tr>
                            @if(isset($selected_img[0]) && isset($selected_img[0]->filename))
                            <tr>
                                <th>파일다운로드</th>
                                <td>
                                <a href="/erp/downcustimg/{{$selected_img[0]->no}}" download="{{$selected_img[0]->origin_filename}}"><span class="hand text-blue"><i class="fas fa-file-download pr-1"></i>{{$selected_img[0]->origin_filename ?? ''}}</span></a>
                                </td>
                            </tr>
                            @endif
                            <tr>
                                <th>파일첨부</th>
                                <td>
                                    <div class="input-group custom-file">
                                        <input type="file" class="custom-file-input form-control-xs text-xs" id="customFile" name="customFile" style="cursor:pointer;">
                                        <label class="custom-file-label mb-0 text-xs form-control-xs" for="customFile">Choose file</label>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <th>메모</th>
                                <td>
                                    <textarea class="form-control form-control-xs" name="memo" id="memo" placeholder=" 메모입력...." rows="4" style="resize:none;" >{{$selected_img[0]->memo ??"" }}</textarea>
                                </td>
                            </tr>
                            <tr>
                                <td class="text-right" colspan=2>
                                    @if( !isset($img_mode) || (isset($img_mode) && $img_mode == "INS") )
                                    <button type="button" class="btn btn-sm btn-info" onclick="imgAction('INS');">저장</button>
                                    @elseif( isset($img_mode) && $img_mode == "UPD" )
                                    <button type="button" class="btn btn-sm btn-info" onclick="imgAction('DEL');">삭제</button>
                                    <button type="button" class="btn btn-sm btn-info" onclick="imgAction('UPD');">수정</button>
                                    @endif
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </form>
            </div>
            <div class="col-md-6 text-center" >
                @if( isset($selected_img[0]->extension) )
                    @if( ($selected_img[0]->extension != "tif" && $selected_img[0]->extension != "tiff") )
                        <img style="width:100%;height:100%;" src='/erp/getcustimg?no={{$selected_img[0]->no}}&cust_info_no={{$selected_img[0]->cust_info_no}}'>
                    @endif
                @endif
            </div>
        </div>

    </div>

<div id="document_info" style="display:none;">
    <form id="document_form" method="post" enctype="multipart/form-data" >
        <table class="table table-sm table-bordered table-input">
            <input type="hidden" name="cust_info_no" id="cust_info_no" value="{{ $cust_info_no ?? '' }}">
            <input type="hidden" name="loan_info_no" id="loan_info_no" value="{{ $v->loan_info_no ?? '' }}">
            <input type="hidden" name="loan_info_law_no" id="loan_info_law_no" value="{{ $v->no ?? '' }}">

            <tbody>
                <tr style="height:40px;">
                    <td class="text" style="vertical-align:bottom;">
                        <button type="button" class="btn btn-default btn-xs text-xs ml-1 branch-disabled" onclick="printDoc('erp', 'doc');" id="law_cost_add_btn"><i class="fas fa-print p-1 text text-xs"></i>별지 인쇄</button>
                        <button type="button" class="btn btn-default btn-xs text-xs ml-2 branch-disabled" onclick="printDoc('erp', 'doc_law');" id="law_cost_add_btn"><i class="fas fa-print p-1 text text-xs"></i>법조치의뢰 인쇄</button>
                        <button type="button" class="btn btn-default btn-xs text-xs ml-2 branch-disabled" onclick="printDoc('erp', 'loan_form');" id="law_cost_add_btn"><i class="fas fa-print p-1 text text-xs"></i>채권계산서 인쇄</button>
                    </td>
                </tr>
                <tr style="height:40px;">
                    <td colspan="1" style="vertical-align:bottom;">
                        <b class="pl-1">신청취지 및 이유</b>
                    </td>
                </tr>
                <tr>
                    <td colspan="8">
                        <div class="underline">
                            <table class="table table-sm table-input mb-0">

                                <colgroup>
                                <col width="10%" class="text-center">
                                <col width="40%">
                                <col width="10%" class="text-center">
                                <col width="40%">
                                </colgroup>

                                <tbody>
                                <tr>
                                    <th>신청이유<br><button class="btn btn-default btn-xs text-xs branch-disabled" onclick="lawAppStr();" type="button"><i class="fas fa-receipt p-1 text text-xs"></i>신청이유</button></th>
                                    <td><textarea class="form-control form-control-sm" name="law_app_reason_memo" id="law_app_reason_memo" style="height:150px;font-size:0.8rem;">{{$v->law_app_reason_memo ?? ''}}</textarea></td>
                                    <th>별지내용<br><button class="btn btn-default btn-xs text-xs branch-disabled" onclick="documentStr();" type="button"><i class="fas fa-receipt p-1 text text-xs"></i>별지내용</button></th>
                                    <td class="text"><textarea class="form-control form-control-sm" name="document_memo" id="document_memo" style="height:150px;font-size:0.8rem;">{{$v->document_memo ?? ''}}</textarea></td>
                                </tr>
                                <tr>
                                    <th>별지내용2</th>
                                    <td><textarea class="form-control form-control-sm" name="document_memo2" id="document_memo2" style="height:150px;font-size:0.8rem;">{{$v->document_memo2 ?? ''}}</textarea></td>
                                    <th>법조치사유</th>
                                    <td class="text"><textarea class="form-control form-control-sm" name="law_reason_memo" id="law_reason_memo" style="height:150px;font-size:0.8rem;">{{$v->law_reason_memo ?? ''}}</textarea></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </td>
                </tr>
                <tr class="underline">
                    <td colspan="10" class="text-right pt-3">
                        <button class="btn btn-sm bg-lightblue mr-1" onclick="documentAction('{{ $mode }}');" type="button">저장</button>
                    </td>
                </tr>
            </tbody>
        </table>
    </form>
</div>


<!-- 등기부등본 검색 모달 -->
<div class="modal fade" id="searchRegistModal" style="margin-top:20px;">
    <div class="modal-dialog modal-xxl">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">제3채무자 선택</h5>
                <button type="button" class="close" id="excelClose"data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="searchRegistForm" name="searchRegistForm" method="post" enctype="multipart/form-data" action="" onSubmit="return false;">
                <input type="hidden" name="cust_info_no" id="cust_info_no" value="{{ $cust_info_no ?? '' }}">
                <input type="hidden" name="loan_info_no" id="loan_info_no" value="{{ $v->loan_info_no ?? '' }}">
                <input type="hidden" name="loan_info_law_no" id="loan_info_law_no" value="{{ $v->no ?? '' }}">
                    <table class="table table-hover table-sm text-center card-secondary card-outline table-bordered">
                        <tbody>
                            <colgroup>
                                <col width="3%"/>
                                <col width="13%"/>
                                <col width="10%"/>
                                <col width="5%"/>
                                <col width="16%"/>
                                <col width="37%"/>
                                <col width="17%"/>
                            </colgroup>
                            <tr align="center">
                                <th>선택</th>
                                <th>제3채무자명</th>
                                <th>압류구분</th>
                                <th>구분</th>
                                <th>대표자명</th>
                                <th>주소</th>
                                <th>소관</th>
                            </tr>
                            @if(isset($regist))
                                @foreach($regist as $v)
                                    <tr align="center">
                                        <td><input type='checkbox' name='listChk[]' class='list-check pr-0' value='{{ $v->no }}'></td>
                                        <td>{{ $v->bank_name ?? '' }}</td>
                                        <td>{{ $configArr['bank_div'][$v->bank_type] ?? '' }}</td>
                                        <td>{{ $v->bank_div ?? '' }}</td>
                                        <td>{{ $v->owner_name ?? '' }}</td>
                                        <td>{{ $v->addr ?? '' }}</td>
                                        <td></td>
                                    </tr>
                                @endforeach
                            @endif
                        </tbody>
                    </table>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" id="closeBtn" data-dismiss="modal" aria-hidden="true">닫기</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="addDebtorAction();">제3채무자 추가</button>
            </div>
        </div>
    </div>
</div>
<script>
    bsCustomFileInput.init();
</script>
<script>

    $(document).ready(function () {
        // 체크박스모양
        $('input[name="listChk[]"]').iCheck({
            checkboxClass: 'icheckbox_square-blue',
        });
        
        if(typeof @json($selected_tab) == 'undefined' || @json($selected_tab) == null || @json($selected_tab) == '')
        {
            ClickDirectDivClass(1);
        }
        else
        {
            ClickDirectDivClass(@json($selected_tab));
        }
    });

    @if( $branch_disabled=="disabled" || $action_mode=="NONE" )

    $(".branch-disabled").attr("disabled", true);

    @endif

    @if( Func::nvl($v->status)=="B" )
    $("#court_cd").attr("disabled", true);
    $("#event_year").attr("disabled", true);
    $("#event_cd").attr("disabled", true);
    $("#event_no").attr("disabled", true);
    @endif


    if( $('#law_cost_type_01').length==0 && $('#law_type').val()!="" && $('#law_cost_add_btn').attr("disabled")!="disabled" )
    {
        addRow();
    }

    function setAddrInput(code)
    {
        $('#addr_cd').val(code);

        $.each($(".lawBtn"), function(idx,item){
            if( item.getAttribute("id") == "lawBtn_"+code )
            {
                item.classList.add("btn-danger");
                item.classList.remove("btn-secondary");
            }
            else
            {
                item.classList.add("btn-secondary");
                item.classList.remove("btn-danger");
            }
        });
    }

    function addDebtor(div)
	{
		var last_row  = $('.debtor').length;
        var loan_info_law_no  = $('#loan_info_law_no').val();

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});

		$.ajax({
			url: '/erp/adddebtor',
			type: "post",
			data: {'last_row':last_row, 'loan_info_law_no':loan_info_law_no},
			success: function(result) {
				$("#" + "debtor_"+(last_row - 1)).after(result.debtorStr);
				afterAjax();
			},
			error : function(xhr) {
			}
		})
	}

    // 등기부등본에서 제3채무자 추가
    function addDebtorAction()
    {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        var last_row  = $('.debtor').length;

        var postdata = $('#searchRegistForm').serialize();
        if((typeof $('#debtor_name_0').val() == 'undefined' || $('#debtor_name_0').val() == null || $('#debtor_name_0').val() == '') && 
         ( typeof $('#owner_name_0').val() == 'undefined' || $('#owner_name_0').val() == null || $('#owner_name_0').val() == '') && last_row == 1)
        {
            postdata += "&last_row="+0;
        }
        else
        {
            postdata += "&last_row="+last_row;
        }

        $.post(
            "/erp/adddebtor", 
            postdata, 
            function(result) {
                if((typeof $('#debtor_name_0').val() == 'undefined' || $('#debtor_name_0').val() == null || $('#debtor_name_0').val() == '') && 
                 ( typeof $('#owner_name_0').val() == 'undefined' || $('#owner_name_0').val() == null || $('#owner_name_0').val() == '') && last_row == 1)
                {
                    $("#debtor_first").html(result.debtorStr);
                }
                else
                {
                    $("#" + "debtor_"+(last_row - 1)).after(result.debtorStr);
                }
				afterAjax();
             
		});

        $("#searchRegistModal").modal('hide');

       
    }

    function imgAction(mode)
    {
        if( !confirm("정말로 작업 하시겠습니까?") )
        {
            return false;
        }

        img_form.mode.value = mode;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = new FormData($('#img_form')[0]);

        if( $('#customFile')[0].files[0] )
        {
            postdata.append('fileObj', $('#customFile')[0].files[0]);
        }

        if(ccCheck()) return;

        $.ajax({
            url  : "/erp/custimgaction",
            type : "post",
            data : postdata,
            processData : false,
            contentType : false,
            success : function(result) {
                globalCheck = false;
                alert(result);
                getCustData('law',{{ $loan_info_no }},'',{{ $v->no ?? 0 }}, 4);
            },
            error : function(xhr) {
                globalCheck = false;
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }

    function documentStr()
    {
        var str = '집행권원의 표시 : \n\n채무자가 아래 제 3채무자에 대하여 현재 또는 장래에 가지는 보험금청구채권(해약환급금 포함)중 다음에서 기재한 순서에 따라 아래 각 제 3채무자별 청구금액에 이를 때까지의 금액 ';
        str += '(단, 해지되지 않은 보험계약에 대해 증권발행 후 청구합니다)(단, 자동차손해보험보장법에 의한 피해자의 제 3채무자에 대한 보험금 청구채권 및 민사집행법 제 246조 제 1항 제 7호, 제 8호 및 동법 시행령 제 6조의 ';
        str += '규정에 따라 압류가 금지되는 보험금 및 예금을 제외한다)\n\n';
        str += '다    음\n\n';
        str += '1. 채무자가 보험계약에 기하여 의무를 다하던 중 보험약관 상 사유로 인하여 제 3채무자로부터 지급받을 보험금 지급 청구 채권 및 배당금2. 보험계약 만료로 인하여 채무자가 제 3채무자로부터 지급받을 (만기)';
        str += '보험금 지급청구채권3. 중도해지 및 실효로 인하여 채무자가 제 3채무자로부터 지급받을 해약환급금 청구채권4. 기타 채무자가 제 3채무자로부터 지급받을 각종 지급청구채권 및 배당금';
        str += '5. 압류되지 않은 보험과 압류된 보험이 있는 경우에는 다음의 순서에 의하여 압류한다.가. 질권설정 및 압류, 가압류가 없는 것나. 압류, 가압류가 있으나 질권설정이 없는 것다. 질권설정이 있으나 가압류가 없는 것라. ';
        str += '질권설정 및 압류, 가압류가 있는 것6. 여러종류의 보험이 있는 경우에는 다음의 순서에 의하여 압류한다가. 저축보험, 나. 연금저축보험, 다. 연금보험, 라. 변액보험, 마. 암보험바. 보장보험, 사. 건강보험, 아. 상해보험';
        str += ', 자. 실버보험7. 같은 종류의 보험이 2개 이상 있을 경우, 만기가 빠른 순서부터 압류 및 추심한다\n\n';
        str += '아    래\n\n';
        str += '제3채무자\n\n\n\n';
        str += '채무자 : '+(@json($custInfo->name ?? ''))+' '+(@json($custInfo->ssn ?? '').substr(0, 6))+'-'+(@json($custInfo->ssn ?? '').substr(6, 7))+' \n\n';

        document.getElementById('document_memo').innerHTML = str;
    }

    function lawAppStr()
    {
        str = '채권자는 채무자로부터   에 따라 청구금액과 같은 금원을 지급받을 집행력있는 채권이 있습니다. \n';
        str += '그런데 채무자는 위 채무를 임의 변제치 아니하므로 채권자의 채무자에 대한 위 채권의 변제에 충당하기 위하여\n';
        str += '채무자가 제3채무자에 대하여 가지는 별지목록 기재 채권에 대하여 신청취지와 같은 결정을 구하고자 본 건 신청에 이른 것입니다.';

        document.getElementById('law_app_reason_memo').innerHTML = str;
    }

    function ClickDirectDivClass(div)
	{
        for(var i = 1; i <= 5; i++)
        {
            document.all["tab-"+i].style.color="black";
            document.all["tab-"+i].style.background="white";
        }
		
		if(div == 1)
		{
			document.all["tab-1"].style.color="white";
			document.all["tab-1"].style.background="#3C8DBC";

			document.all["law_info"].style.display = "block";

            document.all["debtor_info"].style.display = "none";
            document.all["status_info"].style.display = "none";
            document.all["law_img"].style.display = "none";
            document.all["document_info"].style.display = "none";
		}
		else if(div == 2)
		{
			document.all["tab-2"].style.color="white";
			document.all["tab-2"].style.background="#3C8DBC";

			document.all["debtor_info"].style.display = "block";

            document.all["law_info"].style.display = "none";
            document.all["status_info"].style.display = "none";
            document.all["law_img"].style.display = "none";
            document.all["document_info"].style.display = "none";
		}
		else if(div == 3)
		{
			document.all["tab-3"].style.color="white";
			document.all["tab-3"].style.background="#3C8DBC";

			document.all["status_info"].style.display = "block";

            document.all["law_info"].style.display = "none";
            document.all["law_img"].style.display = "none";
            document.all["debtor_info"].style.display = "none";
            document.all["document_info"].style.display = "none";
		}
        else if(div == 4)
		{
			document.all["tab-4"].style.color="white";
			document.all["tab-4"].style.background="#3C8DBC";

			document.all["law_img"].style.display = "block";

            document.all["law_info"].style.display = "none";
            document.all["status_info"].style.display = "none";
            document.all["debtor_info"].style.display = "none";
            document.all["document_info"].style.display = "none";
		}
        else if(div == 5)
		{
			document.all["tab-5"].style.color="white";
			document.all["tab-5"].style.background="#3C8DBC";

			document.all["document_info"].style.display = "block";

            document.all["law_info"].style.display = "none";
            document.all["status_info"].style.display = "none";
            document.all["debtor_info"].style.display = "none";
            document.all["law_img"].style.display = "none";
		}

//		iframeResize();
	}


</script>