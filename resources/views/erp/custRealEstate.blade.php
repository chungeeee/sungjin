<!-- 부동산담보 -->
<div class="col-md-12 p-0 m-0 " >
    <div class="card-header p-1" style="border-bottom:none !important;">
        <h6 class="card-title"><i class="fas fa-donate m-2"></i>부동산담보</h6>
        <div class="card-tools pr-2">
            
            <button type="button" class="btn btn-xs btn-outline-info float-right mb-1" onclick="getCustData('realestate', 'INPUT');">
                <i class="fa fa-plus-square text-info mr-1"></i>담보추가
            </button>
            
        </div>
    </div>
    @include('inc/listSimple')

    <div id="realEstateInput" style='display:@if(isset($v->no) || $mode== 'INS') block; @else none; @endif'>
    <form  class="mb-0" name="real_estate_form" id="real_estate_form" method="post" enctype="multipart/form-data">
        <input type="hidden" name="loan_info_no" value="{{ $result['customer']['loan_info_no'] ?? '' }}">
        <input type="hidden" name="cust_info_no" value="{{ $result['customer']['cust_info_no'] ?? '' }}">
        <div class="col-md-12 pt-4">
            <table class="table table-sm table-bordered table-input text-xs" id="tblBackground">
                <colgroup>
                    <col width="6%"/>
                    <col width="10%"/>
                    <col width="22%"/>
                    <col width="10%"/>
                    <col width="22%"/>
                    <col width="10%"/>
                    <col width="21%"/>
                </colgroup>
                <tbody>
                <tr>
                    <th rowspan="5">기본사항</th>
                    <th>담보번호</th>
                    <td>
                        <input type="text" class="form-control form-control-sm col-md-8" name="no" id="no" value="{{ $v->no ?? '' }}" readOnly>
                    </td>
                    <th style="background-color: #FEAFA1;">대출종류</th>
                    <td>
                        <select class="form-control form-control-sm selectpicker selectwidth100 mr-2 col-md-12" name="dambo_loan_type[]" id="dambo_loan_type" multiple data-selected-text-format="count > 20" data-live-search="true" title="대출종류">
                            {{ Func::printOptionMulti($arrayRslt['config']['dambo_loan_type'], isset($v->dambo_loan_type) ? $v->dambo_loan_type : '' ) }}
                        </select>
                    </td>
                    <th>대출종류 기타</th>
                    <td>
                        
                        <input type="text" class="form-control form-control-sm col-md-5" name="dambo_loan_type_etc" id="dambo_loan_type_etc" value="{{ $v->dambo_loan_type_etc ?? '' }}" maxlength="10">
                        
                    </td>
                </tr>
                <tr>
                    <th style="background-color: #FEAFA1;">물건소재지</th>
                    <td colspan="5">
                        <div class="col-md-6 pl-0">
                            <div class="row">
                                <div class="input-group col-sm-6 pb-1">
                                <input type="text" class="form-control" name="zip11" id="zip11" numberOnly="true" value="{{ $v->zip11 ?? '' }}" readOnly>
                                <span class="input-group-btn">
                                <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('zip11', 'addr11', 'addr12', $('#addr11').val())">검색</button>
                                </span>
                                <span class="input-group-btn">
								{{-- 2022.12.07 robert 스크래핑 라우팅 수정
                                <a href="#" onclick="window.open('/ups/realestateinfo?loan_app_no={{$result['customer']['loan_app_no'] ?? ''}}&loan_real_estate_no={{$v->no ?? ''}}','', 'right=0,top=0,height=800,width=1300,fullscreen=yes')"> --}}
								<a href="#" onclick="window.open('/erp/kbscrap/{{ $v->no ?? ''  }}','', 'right=0,top=0,height=800,width=1300,fullscreen=yes')">
								<img src="/img/kb.png" align=absmiddle width="25" style="max-height:100%;margin-right:3px;" border=0>부동산스크래핑</a>
                                </span>
                                </div>
                            </div>
                            <input type="text" class="form-control mb-1 col-md-12" name="addr11" id="addr11" value="{{ $v->addr11 ?? '' }}" readOnly style="background-color: #FFE5DD">
                            <input type="text" class="form-control mb-1 col-md-12" name="old_addr11" id="old_addr11" value="{{ $v->old_addr11 ?? '' }}" readOnly title="지번주소" placeholder="지번주소">
                            <input type="text" class="form-control col-md-12" name="addr12" id="addr12" value="{{ $v->addr12 ?? '' }}" maxlength="100" style="background-color: #FFE5DD">
                        </div>
                    </td>
                </tr> 
                <tr>
                    <th style="background-color: #FEAFA1;">담보주소(등기부등본)</th>
                    <td colspan="5">
                        <div class="col-md-10 pl-0">
                            <input type="text" class="form-control"name="ccr_addr" id="ccr_addr" value="{{ $v->ccr_addr ?? '' }}" readOnly style="background-color: #FFE5DD">
                        </div>
                    </td>
                </tr> 
                <tr>
                    <th style="background-color: #FEAFA1;">등기고유번호</th>
                    <td>
                        <div class="col-md-12 row p-0 m-0">
                            <div class="col-md-5 p-0 m-0">
                                <input type="text" class="form-control form-control-sm col-md-12" onkeyup="onlyNumber(this);" name="unique_code" id="unique_code" value="{{ $v->unique_code ?? '' }}">
                            </div>
                            <div class="col-md-2 p-0 pl-1 m-0">
                                <button type="button" class="btn btn-default btn-sm text-xs" onclick="duplChk();"><i class="fas fa-search p-1"></i></button>
                            </div>
                        </div>
                    </td>  

                    <th style="background-color: #FEAFA1;">임차인여부</th>
                    <td>
                        <div class="col-md-12 row" >
                            <select class="form-control form-control-sm col-md-8"  name="tenant_yn" id="tenant_yn">
                            <option value=''>선택</option>
                            {{ Func::printOption($arrayRslt['vars']['yn'], isset($v->tenant_yn) ? $v->tenant_yn : '' ) }}
                            </select>
                        </div>
                    </td>
                    <th style="background-color: #FEAFA1;">담보구분</th>
                    <td>
                        <div class="row">
                        <select class="form-control form-control-sm col-md-6 ml-2"  name="house_type" id="house_type" onChange="chSetMemo(this.value, '90', 'house_type_etc')">
                        <option value=''>선택</option>
                        {{ Func::printOption($arrayRslt['config']['dambo_mortgage_type'], isset($v->house_type) ? $v->house_type : '' ) }}
                        </select>
                        <input type="text" class="form-control form-control-sm col-md-5 ml-1" name="house_type_etc" id="house_type_etc" value="{{ $v->house_type_etc ?? '' }}" placeholder="메모" @if(!isset($v->house_type) || $v->house_type!='90') disabled @endif>
                        </div>
                    </td>
                </tr>

                <tr>
                    
                    <th style="background-color: #FEAFA1;">전입세대열람<br>(세대주)</th>
                    <td>
                        <div class="col-md-12 row p-0 m-0">
                            <div class="col-md-6 p-0 m-0">
                                <input type="text" class="form-control form-control-sm col-md-12" name="move_in_name" id="move_in_name" value="{{ $v->move_in_name ?? '' }}" maxlength="10">
                            </div>
                            <div class="col-md-6">
                                <select class="form-control form-control-sm col-md-10"  name="move_in_type" id="move_in_type" onChange="chSetMemo(this.value, '90', 'move_in_type_etc', 'move_in_name')">
                                <option value=''>관계선택</option>
                                {{ Func::printOption($arrayRslt['config']['cust_rel_cd'], isset($v->move_in_type) ? $v->move_in_type : '' ) }}
                                </select>
                            </div>
                        </div>
                    </td>
                    
                    <th>전입세대열람 기타</th>
                    <td>

                        <input type="text" class="form-control form-control-sm col-md-5" name="move_in_type_etc" id="move_in_type_etc" value="{{ $v->move_in_type_etc ?? '' }}" maxlength="10"  @if(!isset($v->move_in_type) || $v->move_in_type!='90') disabled @endif>
                    </td>

                    <th>최초전입자명/관계</th>
                    <td>
                        <div class="col-md-12 row p-0 m-0">
                            <div class="col-md-5 p-0 m-0">
                                <input type="text" class="form-control form-control-sm col-md-12" name="fst_move_name" id="fst_move_name" value="{{ $v->fst_move_name ?? '' }}" maxlength="10">
                            </div>
                            <div class="col-md-6">
                                <select class="form-control form-control-sm col-md-10"  name="fst_move_name_rel" id="fst_move_name_rel" onChange="setMyName('fst_move_name', this.value)">
                                <option value=''>관계선택</option>
                                {{ Func::printOption($arrayRslt['config']['cust_rel_cd'], isset($v->fst_move_name_rel) ? $v->fst_move_name_rel : '' ) }}
                                </select>
                            </div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>유동화금액</th>
                    <td>
                        <div class="col-md-12 row p-0 m-0">
                            <div class="col-md-10 p-0 m-0">
                                <input type="text" class="form-control form-control-sm col-md-12" name="yudong_money" id="yudong_money" value="{{ $v->yudong_money ?? '' }}" maxlength="10">
                            </div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th rowspan="3">물건내역</th>
                    <th>공급면적(m<sup>2</sup>)</th>
                    <td>
                        <input type="text" class="form-control form-control-sm col-md-4" name="supply_m" id="supply_m" value="{{ $v->supply_m ?? '' }}">
                    </td>

                    <th style="background-color: #FEAFA1;">전용면적(m<sup>2</sup>)</th>
                    <td>
                        <div class="col-md-12 row pl-2" >
                            <input type="text" class="form-control form-control-sm col-md-4" name="jeonyong_m" id="jeonyong_m" value="{{ $v->jeonyong_m ?? '' }}" style="background-color: #FFE5DD">
                        </div>
                    </td>
                    <th>준공연도</th>
                    <td>
                        <div class="input-group date datetimepicker col-md-4 p-0" id="completion_date_div" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm datetimepicker " name="completion_date" id="completion_date" value="{{ $v->completion_date ?? '' }}" dateonly="true" required maxlength=4>
                            <div class="input-group-append" data-target="#completion_date" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div> 
                    </td>

                </tr> 
                <tr>                    
                    <th>해당 층</th>
                    <td>
                        <input type="text" class="form-control form-control-sm col-md-8" name="item_floor" id="item_floor" value="{{ $v->item_floor ?? '' }}" maxlength="10">
                    </td>
                    <th>총 층수</th>
                    <td>
                        <input type="text" class="form-control form-control-sm col-md-8" name="item_total_floor" id="item_total_floor" value="{{ $v->item_total_floor ?? '' }}" maxlength="10">
                    </td>
                    <td></td>
                    <td></td>
                </tr>

                <tr>
                    <th>총 동수</th>
                    <td>
                        <input type="text" class="form-control form-control-sm col-md-8" name="item_total_dong" id="item_total_dong" value="{{ $v->item_total_dong ?? '' }}" maxlength="10">
                    </td>
                    <th>총 세대수</th>
                    <td>
                        <input type="text" class="form-control form-control-sm col-md-8" name="item_households" id="item_households" value="{{ $v->item_households ?? '' }}" maxlength="10">
                    </td>
                    <td></td>
                    <td></td>
                </tr>

                {{-- <tr>
                    <th>임차인비고</th>
                    <td colspan="5">
                        <textarea class="form-control" rows="2" name="tenant_etc" id="tenant_etc" value="{{ $v->tenant_etc ?? '' }}"></textarea>
                    </td>
                </tr> --}}
                <tr>
                    <th rowspan="4">시세현황</th>

                    <th>대법원 매각가율</th>
                    <td>
                        <div class="row text-center">
                        <input type="text" class="form-control form-control-sm col-md-3 ml-2" name="court_sale_rate" id="court_sale_rate" value="{{ $v->court_sale_rate ?? '' }}" maxlength="6" onKeyUp="onlyRatio(this)">
                        <span class="pt-2"> %</span>
                        </div>
                    </td>
                    <th>인포케어 낙찰가율</th>
                    <td>
                        <div class="row text-center">
                        <input type="text" class="form-control form-control-sm col-md-3 ml-2" name="infocare_rate" id="infocare_rate" value="{{ $v->infocare_rate ?? '' }}" maxlength="8" onkeyup="onlyRatio(this)">
                        <span class="pt-2"> %</span>

                        <input type="text" class="form-control form-control-sm col-md-5 ml-2" name="infocare_rate_etc" id="infocare_rate_etc" value="{{ $v->infocare_rate_etc ?? '' }}" maxlength="10">
                        </div>
                    </td>
                    
                    <td></td>
                    <td></td>
                </tr>
                <tr>
                    <th>감정기관</th>
                    <td colspan="3">
                        <div class="row">
                            <select class="form-control form-control-sm col-md-2 ml-2"  name="app_agency" id="app_agency" onChange="chSetMemo(this.value, '90', 'app_agency_etc')">
                            <option value=''>선택</option>
                            {{ Func::printOption($arrayRslt['config']['app_agency'], isset($v->app_agency) ? $v->app_agency : '' ) }}
                            </select>
                            <input type="text" class="form-control form-control-sm col-md-4 ml-1" name="app_agency_etc" id="app_agency_etc" value="{{ $v->app_agency_etc ?? '' }}" placeholder="메모" @if(!isset($v->app_agency) || $v->app_agency!='90') disabled @endif>
                        </div>
                    </td>
                    <th>kb기준</th>
                    <td>
                        <div class="row">
                            <select class="form-control form-control-sm col-md-5 ml-2"  name="kb_gijun" id="kb_gijun">
                            <option value=''>선택</option>
                            {{ Func::printOption($arrayRslt['vars']['value'], isset($v->kb_gijun) ? $v->kb_gijun : '' ) }}
                            </select>
                            <span class="input-group-btn">
                                <a href="#" onclick="window.open('/erp/rekbscrap/{{ $v->no ?? ''  }}','', 'right=0,top=0,height=800,width=1300,fullscreen=yes')">
                                <img src="/img/kb.png" align=absmiddle width="25" style="max-height:100%;margin-right:3px;" border=0>시세 갱신</a>
                            </span>
                        </div>
                    </td>
                </tr> 
                <tr>
                    <th style="background-color: #FEAFA1;">매매일반가</th>
                    <td>
                        <input type="text" class="form-control form-control-sm col-md-5 comma" name="value_middle" id="value_middle" value="{{ $v->value_middle ?? '' }}" onclick="valCopy('value_middle')">
                    </td>
                    <th>매매하한가</th>
                    <td>
                        <input type="text" class="form-control form-control-sm col-md-5 comma" name="value_low" id="value_low" value="{{ $v->value_low ?? '' }}" onclick="valCopy('value_low')">
                    </td>
                    <th>매매상한가</th>
                    <td>
                        <input type="text" class="form-control form-control-sm col-md-5 comma" name="value_high" id="value_high" value="{{ $v->value_high ?? '' }}" onclick="valCopy('value_high')">
                    </td>
                </tr>
                <tr>
                    
                    <th style="background-color: #FEAFA1;">결정가(원)</th>
                    <td>
                        <div class="row">                                            
                        <input type="text" class="form-control form-control-sm col-md-5 comma ml-2" name="final_value" id="final_value" value="{{ $v->final_value ?? '' }}"  onkeyup="onlyNumber(this);">

                        
                            <span class="ml-2 mr-1 pt-2">시세</span>
                            <input type="text" class="form-control form-control-sm col-md-3" onkeyup="onlyRatio(this);" name="market_rate" id="market_rate" value="{{$v->market_rate ?? ''}}" maxlength="6">
                            <span class="ml-1 pt-2">% 적용</span>
                        </div> 
                    </td>
                    <th style="background-color: #FEAFA1;">기준가</th>
                    <td>
                        <input type="text" class="form-control form-control-sm col-md-5 comma" name="basic_value" id="basic_value" value="{{ $v->basic_value ?? '' }}"  onkeyup="onlyNumber(this);">
                    </td>
                    <th>공시지가</th>
                    <td>
                        <input type="text" class="form-control form-control-sm col-md-5 comma" name="official_value" id="official_value" value="{{ $v->official_value ?? '' }}"  onkeyup="onlyNumber(this);">
                    </td>
                    
                </tr> 
                {{-- <tr>
                    <th>실거래가(원)</th>
                    <td>
                        <input type="text" class="form-control form-control-sm col-md-8 comma" name="real_trade_value" id="real_trade_value" value="{{ $v->real_trade_value ?? '' }}">
                    </td>
                    <th>특이사항</th>
                    <td colspan="3">
                        <textarea class="form-control" rows="2" name="etc" id="etc" value="{{ $v->etc ?? '' }}"></textarea>
                    </td>
                </tr>   --}}
                <tr>
                    <th>소유현황</th>
                    <td colspan="6">
                        <div class="col-md-12 pt-0 pb-0" style="text-align:right;">
                            <button type="button" class="btn btn-default btn-sm text-xxs" onclick="addOwner();"><i class="fas fa-plus-circle p-1" style="color:green;"></i>추가</button>
                            <button type="button" class="btn btn-default btn-sm text-xxs" onclick="deleteTr('owner');"><i class="fas fa-minus-circle p-1" style="color:red;"></i>취소</button>
                        </div>
                        <table class="table table-sm table-bordered table-input text-xs">
                            <colgroup>
                            <col width="15%"/>
                            <col width="15%"/>
                            <col width="15%"/>
                            <col width="15%"/>
                            <col width="15%"/>
                            <col width="15%"/>
                            <col width="6%"/>
                            </colgroup>
                            <tr>
                                <th style="text-align:center !important;" class="pl-0">소유자명</th>
                                <th style="text-align:center !important;" class="pl-0">관계</th>
                                <th style="text-align:center !important;" class="pl-0">소유구분</th>
                                <th style="text-align:center !important;" class="pl-0">지분율</th>
                                <th style="text-align:center !important;" class="pl-0">담보제공여부</th>
                                <th style="text-align:center !important;" class="pl-0">소유권등기일</th>
                                <th style="text-align:center !important;" class="pl-0">삭제</th>
                            </tr>
                            <tbody id="ownerTb">
                                @isset($ownerList)
                                @for ($i = 0; $i < sizeof($ownerList); $i++)
                                <tr>
                                    <input type="hidden" name="owner_no[]" value="{{$ownerList[$i]->no}}">
                                    <td align="center">
                                        <div class="col-md-12">
                                            <input type="text" class="form-control form-control-sm col-md-10" name="owner_name[]" id="owner_name{{$i}}" value="{{$ownerList[$i]->owner_name ?? ''}}">
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12" >
                                            <select class="form-control form-control-sm col-md-10"  name="owner_relation[]" id="owner_relation{{$i}}" onChange="setMyName('owner_name{{$i}}', this.value)">
                                            <option value=''>선택</option>
                                            {{ Func::printOption($arrayRslt['config']['relation_cd'], $ownerList[$i]->owner_relation ) }}
                                            </select>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12" >
                                            <select class="form-control form-control-sm col-md-10"  name="owner_type[]" id="owner_type{{$i}}" onChange="chOwnType(this.value, 'owner_ratio{{$i}}')">
                                            <option value=''>선택</option>
                                            {{ Func::printOption($arrayRslt['config']['mortgage_own_type'], $ownerList[$i]->owner_type ) }}
                                            </select>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12">
                                            <div class="col-md-6 row">
                                            <input type="text" class="form-control form-control-sm col-md-8" onkeyup="onlyNumber(this);" name="owner_ratio[]" id="owner_ratio{{$i}}" value="{{$ownerList[$i]->owner_ratio ?? ''}}" maxlength="3" @if($ownerList[$i]->owner_type=='M') readonly @endif>
                                            <span class="ml-1 pt-2">%</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12" >
                                            <select class="form-control form-control-sm col-md-10"  name="dambo_offer_yn[]" id="dambo_offer_yn{{$i}}">
                                            <option value=''>선택</option>
                                            {{ Func::printOption($arrayRslt['vars']['yn'], $ownerList[$i]->dambo_offer_yn ) }}
                                            </select>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="input-group date datetimepicker col-md-10 mt-0" id="own_reg_date{{$i}}" data-target-input="nearest">
                                            <input type="text" class="form-control form-control-sm datetimepicker " name="own_reg_date[]" id="own_reg_date{{$i}}" value="{{$ownerList[$i]->own_reg_date ?? ''}}" dateonly="true" size="6" required>
                                            <div class="input-group-append" data-target="#own_reg_date{{$i}}" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div> 
                                    </td>
                                    <td align="center">
                                        <input type="checkbox" name="own_del[{{$i}}]" value='{{$ownerList[$i]->no}}'> 삭제
                                    </td>
                                </tr>                                    
                                @endfor
                                @endisset
                            </tbody>
                        </table>
                    </td>
                </tr>
{{--     삭제요청. 추후 실삭제 예정            
                <tr>
                    <th style="background-color:#cff0cc">설정등록</th>
                    <td colspan="6">
                        <table class="table table-sm table-bordered table-input text-xs m-0">
                            <colgroup>
                            <col width="12%"/>
                            <col width="12%"/>
                            <col width="12%"/>
                            <col width="12%"/>
                            <col width="12%"/>
                            <col width="12%"/>
                            <col width="12%"/>
                            <col width="12%"/>
                            </colgroup>
                            <tr>
                                <th align="center" style="text-align:center;">설정번호</th>
                                <td align="center"><input type="text" class="form-control form-control-sm col-md-12" name="config_no" id="config_no" value="{{ $v->config_no ?? '' }}"></td>
                                <th align="center" style="text-align:center;">설정상태</th>
                                <td align="center">
                                    <select class="form-control form-control-sm col-md-12"  name="config_type" id="config_type" value="{{ $v->config_type ?? '' }}">
                                    <option value=''>선택</option>
                                    </select>
                                </td>
                                <th align="center" style="text-align:center;">설정일자</th>
                                <td align="center">
                                    <div class="input-group date datetimepicker col-md-12 p-0" id="config_date" data-target-input="nearest">
                                        <input type="text" class="form-control form-control-sm datetimepicker-input " name="config_date" id="config_date" dateonly="true" size="6" value="{{ $v->config_date ?? '' }}" required>
                                        <div class="input-group-append" data-target="#config_date" data-toggle="datetimepicker">
                                            <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                        </div>
                                    </div> 
                                </td>
                                <th align="center" style="text-align:center;">설정금액</th>
                                <td align="center"><input type="text" class="form-control form-control-sm col-md-12 comma" name="config_money" id="config_money" value="{{ $v->config_money ?? '' }}"></td>
                            </tr>
                            <tbody>
                            </tbody>
                        </table>
                    </td>
                </tr>  --}}
                <tr>
                    <th>임대현황</th>
                    <td colspan="6">
                        <div class="col-md-12 pt-1 pb-0" style="text-align:right;">
                            <button type="button" class="btn btn-default btn-sm text-xxs" id="btnAddRental" onclick="addRental();"><i class="fas fa-plus-circle p-1" style="color:green;"></i>추가</button>
                            <button type="button" class="btn btn-default btn-sm text-xxs" onclick="deleteTr('rental');"><i class="fas fa-minus-circle p-1" style="color:red;"></i>취소</button>
                        </div>
                        <table class="table table-sm table-bordered table-input text-xs">
                            <colgroup>
                            <col width="9%"/>
                            <col width="8%"/>
                            <col width="12%"/>
                            <col width="12%"/>
                            <col width="9%"/>
                            <col width="9%"/>
                            <col width="12%"/>
                            <col width="12%"/>
                            <col width="11%"/>

                            <col width="6%"/>
                            </colgroup>
                                <tr>
                                    <th style="text-align:center !important;" class="p-0">임차인</th>
                                    <th style="text-align:center !important;" class="p-0">임차구분</th>
                                    <th style="text-align:center !important;" class="p-0">임차시작일</th>
                                    <th style="text-align:center !important;" class="p-0">임차만료일</th>
                                    <th style="text-align:center !important;" class="p-0">임차보증금(원)</th>
                                    <th style="text-align:center !important;" class="p-0">월세금(원)</th>
                                    <th style="text-align:center !important;" class="p-0">전세권설정일</th>
                                    <th style="text-align:center !important;" class="p-0">확정일자</th>
                                    <th style="text-align:center !important;" class="p-0">임대차계약확정</th>
                                    <th style="text-align:center !important;" class="p-0">삭제</th>
                                </tr>
                            <tbody id="rentalTb">
                                @isset($rentalList)
                                @for ($i = 0; $i < sizeof($rentalList); $i++)
                                <tr>
                                    <input type="hidden" name="rental_no[]" value="{{$rentalList[$i]->no}}">
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <input type="text" class="form-control form-control-sm col-md-10" name="rental_name[]" id="rental_name{{$i}}" value="{{$rentalList[$i]->rental_name ?? ''}}">
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <select class="form-control form-control-sm col-md-10"  name="rental_type[]" id="rental_type{{$i}}">
                                            <option value=''>선택</option>
                                            {{ Func::printOption($arrayRslt['config']['dambo_rental_type'], $rentalList[$i]->rental_type) }}
                                            </select>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="input-group date datetimepicker col-md-12 mt-0" id="target_rental_sdate{{$i}}" data-target-input="nearest">
                                            <input type="text" class="form-control form-control-sm datetimepicker " name="rental_sdate[]" value="{{$rentalList[$i]->rental_sdate ?? ''}}" dateonly="true" size="6" data-target="#rental_sdate{{$i}}" required>
                                            <div class="input-group-append" data-target="#target_rental_sdate{{$i}}" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div> 
                                    </td>
                                    <td align="center">
                                        <div class="input-group date datetimepicker col-md-12 mt-0" id="target_rental_edate{{$i}}" data-target-input="nearest">
                                            <input type="text" class="form-control form-control-sm datetimepicker " name="rental_edate[]" value="{{$rentalList[$i]->rental_edate ?? ''}}" dateonly="true" size="6" required>
                                            <div class="input-group-append" data-target="#target_rental_edate{{$i}}" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div> 
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <input type="text" class="form-control form-control-sm col-md-10 comma" name="rental_deposit[]" onkeyup="onlyNumber(this);" id="rental_deposit{{$i}}" value="{{$rentalList[$i]->rental_deposit ?? ''}}">
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <input type="text" class="form-control form-control-sm col-md-10 comma" name="rental_month_money[]" onkeyup="onlyNumber(this);" id="rental_month_money{{$i}}" value="{{$rentalList[$i]->rental_month_money ?? ''}}">
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="input-group date datetimepicker col-md-12 mt-0" id="target_rental_config_date{{$i}}" data-target-input="nearest">
                                            <input type="text" class="form-control form-control-sm datetimepicker" name="rental_config_date[]" id="rental_config_date{{$i}}" value="{{$rentalList[$i]->rental_config_date ?? ''}}" dateonly="true" size="6" required>
                                            <div class="input-group-append" data-target="#target_rental_config_date{{$i}}" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div> 
                                    </td>
                                    <td align="center">
                                        <div class="input-group date datetimepicker col-md-12 mt-0" id="target_rental_confirm_date{{$i}}" data-target-input="nearest">
                                            <input type="text" class="form-control form-control-sm datetimepicker" name="rental_confirm_date[]" id="rental_confirm_date{{$i}}" value="{{$rentalList[$i]->rental_confirm_date ?? ''}}" dateonly="true" size="6" required>
                                            <div class="input-group-append" data-target="#target_rental_confirm_date{{$i}}" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div> 
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <select class="form-control form-control-sm col-md-10"  name="rental_contract_yn[]" id="rental_contract_yn{{$i}}">
                                            <option value=''>선택</option>
                                            {{ Func::printOption($arrayRslt['vars']['yn'], $rentalList[$i]->rental_contract_yn) }}
                                            </select>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <input type="checkbox" name="rental_del[{{$i}}]" value='{{$rentalList[$i]->no}}'> 삭제
                                    </td>
                                </tr>
                                @endfor
                                @endisset
                            </tbody>
                        </table>                       
                    </td>
                </tr> 

                <tr>
                    <th>선순위내역</th>
                    <td colspan="6">
                        <div class="col-md-12 pt-1 pb-0" style="text-align:right;">
                            <button type="button" class="btn btn-default btn-sm text-xxs" id="btnAddPrior" onclick="addPrior();"><i class="fas fa-plus-circle p-1" style="color:green;"></i>추가</button>
                            <button type="button" class="btn btn-default btn-sm text-xxs" onclick="deleteTr('prior');"><i class="fas fa-minus-circle p-1" style="color:red;"></i>취소</button>
                        </div>
                        <table class="table table-sm table-bordered table-input text-xs">
                            <colgroup>
                            <col width="10%"/>
                            <col width="10%"/>
                            <col width="10%"/>
                            <col width="15%"/>
                            <col width="12%"/>
                            <col width="12%"/>

                            <col width="13%"/>
                            <col width="12%"/>
                            <col width="6%"/>
                            </colgroup>
                                <tr>
                                    <th style="text-align:center !important;" class="p-0">설정순위</th>
                                    <th style="text-align:center !important;" class="p-0">설정권자구분</th>
                                    <th style="text-align:center !important;" class="p-0">설정권자</th>
                                    <th style="text-align:center !important;" class="p-0">채무자(관계)</th>
                                    <th style="text-align:center !important;" class="p-0">설정금액(원)</th>
                                    <th style="text-align:center !important;" class="p-0">대출원금(원)</th>
                                    <th style="text-align:center !important;" class="p-0">설정일자</th>
                                    <th style="text-align:center !important;" class="p-0">담보제공</th>
                                    <th style="text-align:center !important;" class="p-0">삭제</th>
                                </tr>
                            <tbody id="priorTb">
                                @isset($priorList)
                                @for ($i = 0; $i < sizeof($priorList); $i++)
                                <tr>
                                    <input type="hidden" name="prior_no[]" value="{{$priorList[$i]->no}}">
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <select class="form-control form-control-sm col-md-10"  name="type[]" id="type{{$i}}">
                                            <option value=''>선택</option>
                                            {{ Func::printOption($arrayRslt['config']['mortgage_prior_type'], $priorList[$i]->type ) }}
                                            </select>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <select class="form-control form-control-sm col-md-10"  name="setter_type[]" id="setter_type{{$i}}">
                                            <option value=''>선택</option>
                                            {{ Func::printOption($arrayRslt['config']['mortgage_setter_type'], $priorList[$i]->setter_type) }}
                                            </select>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <input type="text" class="form-control form-control-sm col-md-10" name="setter[]" id="setter{{$i}}" value="{{$priorList[$i]->setter ?? ''}}">
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="row p-0 m-0">
                                            <div class="col-md-6 p-0 m-0">
                                                <input type="text" class="form-control form-control-sm col-md-12" name="cust[]" id="cust{{$i}}" value="{{$priorList[$i]->cust ?? ''}}">
                                            </div>
                                            <div class="col-md-6 p-0 m-0">
                                                <select class="form-control form-control-sm col-md-12"  name="cust_rel[]" id="cust_rel{{$i}}" onChange="setMyName('cust{{$i}}', this.value)">
                                                <option value=''>선택</option>
                                                {{ Func::printOption($arrayRslt['config']['relation_cd'], $priorList[$i]->cust_rel) }}
                                                </select>
                                            </div>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <input type="text" class="form-control form-control-sm col-md-10 comma" name="set_money[]" onkeyup="onlyNumber(this);" id="set_money{{$i}}" value="{{$priorList[$i]->set_money ?? ''}}">
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <input type="text" class="form-control form-control-sm col-md-10 comma" name="loan_origin[]" onkeyup="onlyNumber(this);" id="loan_origin{{$i}}" value="{{$priorList[$i]->loan_origin ?? ''}}">
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="input-group date datetimepicker col-md-11 mt-0" id="target_set_date{{$i}}" data-target-input="nearest">
                                            <input type="text" class="form-control form-control-sm datetimepicker " name="set_date[]" id="set_date{{$i}}" value="{{$priorList[$i]->set_date ?? ''}}" dateonly="true" size="6" required>
                                            <div class="input-group-append" data-target="#target_set_date{{$i}}" data-toggle="datetimepicker">
                                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                            </div>
                                        </div> 
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            {{-- <select class="form-control form-control-sm col-md-10"  name="dambo_offer[]" id="dambo_offer{{$i}}">
                                            {{ Func::printOption($arrayRslt['vars']['yn'], $priorList[$i]->dambo_offer) }}
                                            </select> --}}
                                            <input type="text" class="form-control form-control-sm col-md-10" name="dambo_offer[]" id="dambo_offer{{$i}}" value="{{$priorList[$i]->dambo_offer ?? ''}}">
                                        </div>
                                    </td>
                                    <td align="center">
                                        <input type="checkbox" name="prior_del[{{$i}}]" value='{{$priorList[$i]->no}}'> 삭제
                                    </td>
                                </tr>
                                @endfor
                                @endisset
                            </tbody>
                        </table>                       
                    </td>
                </tr>  

                <tr>
                    <th>압류내역</th>
                    <td colspan="6">
                        <table class="table table-sm table-bordered table-input text-xs mb-0">
                            <colgroup>
                            <col width="15%"/>
                            <col width="15%"/>
                            <col width="15%"/>
                            <col />
                            </colgroup>
                                <tr>
                                    <th style="text-align:center !important;" class="p-0">설정액</th>
                                    <th style="text-align:center !important;" class="p-0">잔액</th>
                                    <th style="text-align:center !important;" class="p-0">대환</th>
                                    <th style="text-align:center !important;" class="p-0">비고</th>
                                </tr>
                            <tbody>
                                <tr>
                                    <td align="center"><input type="text" class="form-control form-control-sm col-md-10 comma" onkeyup="onlyNumber(this);" name="one_config_money" id="one_config_money" value="{{ $v->one_config_money ?? '' }}"></td>
                                    <td align="center"><input type="text" class="form-control form-control-sm col-md-10 comma" onkeyup="onlyNumber(this);" name="one_balance" id="one_balance" value="{{ $v->one_balance ?? '' }}"></td>
                                    <td align="center"><input type="text" class="form-control form-control-sm col-md-10 comma" onkeyup="onlyNumber(this);" name="one_exchange_money" id="one_exchange_money" value="{{ $v->one_exchange_money ?? '' }}"></td>
                                    <td align="center"><input type="text" class="form-control form-control-sm col-md-10" name="one_etc" id="one_etc" value="{{ $v->one_etc ?? '' }}"></td>
                                </tr>
                                {{-- <tr>
                                    <td align="center">을구</td>
                                    <td align="center"><input type="text" class="form-control form-control-sm col-md-10 comma" onkeyup="onlyNumber(this);" name="two_config_money" id="two_config_money" value="{{ $v->two_config_money ?? '' }}"></td>
                                    <td align="center"><input type="text" class="form-control form-control-sm col-md-10 comma" onkeyup="onlyNumber(this);" name="two_balance" id="two_balance" value="{{ $v->two_balance ?? '' }}"></td>
                                    <td align="center"><input type="text" class="form-control form-control-sm col-md-10" name="two_etc" id="two_etc" value="{{ $v->two_etc ?? '' }}"></td>
                                </tr> --}}
                            </tbody>
                        </table>                       
                    </td>
                </tr>
                <tr>
                    <th>LTV


                        <button type="button" class="btn btn-default btn-sm text-xs" onclick="calLTV();"><i class="fas fa-calculator p-1" style="color:green;"></i>계산</button>
                    </th>
                    <td colspan="6">
                        <table class="table table-sm table-bordered table-input text-xs m-0 p-0">
                            <colgroup>
								<col width="10%"/>
								<col width="10%"/>
								<col width="15%"/>
								<col width="10%"/>
								<col width="10%"/>
								<col width="15%"/>
								<col width="10%"/>
								<col width="10%"/>
                            </colgroup>

                            <tbody>
                                <tr>
                                    <th class="text-center">최종결정가(원)</th>
                                    <td>
                                        <input type="text" class="form-control form-control-sm col-md-11 comma" onkeyup="onlyNumber(this);" name="last_final_value" id="last_final_value" value="{{ $v->last_final_value ?? '' }}" >
                                    </td>
                                    <td></td>
                                    <th class="text-center">임대차보증금(원)</th>
                                    <td>
                                        <input type="text" class="form-control form-control-sm col-md-11 comma" onkeyup="onlyNumber(this);" name="rental_deposit_sum" id="rental_deposit_sum" value="{{ $v->rental_deposit_sum ?? '' }}">
                                    </td>
                                    <td></td>
                                    <th class="text-center">승인금액(원)</th>
                                    <td>
                                        <input type="text" class="form-control form-control-sm col-md-11 comma" onkeyup="onlyNumber(this);" name="approve_money" id="approve_money" value="{{ $v->approve_money ?? '' }}" >
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="text-center">선순위 설정(원)</th>
                                    <td>
                                        <input type="text" placeholder="선순위 설정(sum)" onkeyup="onlyNumber(this);" class="form-control form-control-sm col-md-11 comma" name="prior_config_money" id="prior_config_money" value="{{ $v->prior_config_money ?? '' }}" >
                                    </td>
                                    <td></td>
                                    <th class="text-center">선순위대출원금(원)</th>
                                    <td>
                                        <input type="hidden" id="loan_money" value="{{ $v->loan_money ?? '' }}" >
                                        <input type="text" class="form-control form-control-sm col-md-11 comma" onkeyup="onlyNumber(this);" name="prior_balance" id="prior_balance" value="{{ $v->prior_balance ?? '' }}" >
                                    </td>
                                    <td></td>
                                </tr>
                                <tr>
                                    <th class="text-center bold">최고액 LTV(%)</th>
                                    <td>
                                        <input type="text" class="form-control form-control-sm col-md-11 bold" onkeyup="onlyRatio(this);" name="max_ltv" id="max_ltv" value="{{ $v->max_ltv ?? '' }}" >
                                    </td>
                                    <td></td>
                                    <th class="text-center bold">원금 LTV(%)</th>
                                    <td>
                                        <input type="text" class="form-control form-control-sm col-md-11 bold" onkeyup="onlyRatio(this);" name="ltv" id="ltv" value="{{ $v->ltv ?? '' }}" >
                                    </td>
                                    <td></td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr> 
                <tr>
                    <th>비고</th>
                    <td colspan="7">
                        <textarea class="form-control" rows="2" name="ltv_etc" id="ltv_etc">{{ $v->ltv_etc ?? '' }}</textarea>
                    </td>
                </tr>

                <tr>
                    <td colspan="7" align="right" class="pt-4">
                        <button class="btn btn-sm bg-lightblue" type="button" onclick="modeChk('{{ $mode }}');">저장</button>
                        @if($mode !== 'INS')
                        <button type="button" class="btn btn-sm btn-danger" onclick="modeChk('DEL');">삭제</button>
                        @endif
                    </td>
                </tr>
                </tbody>
            </table>
        </div>
        </form>
    </div>
</div>


<script id="owner_tmpl" type="text/tmpl">
    <tr id="ownerTr${owner_cnt}">
        <td align="center">
            <div class="col-md-12">
                <input type="text" class="form-control form-control-sm col-md-10" name="owner_name[]" id="owner_name${owner_cnt}">
            </div>
        </td>
        <td align="center">
            <div class="col-md-12" >
                <select class="form-control form-control-sm col-md-10"  name="owner_relation[]" id="owner_relation${owner_cnt}" onChange="setMyName('owner_name${owner_cnt}', this.value)">
                <option value=''>선택</option>
                {{ Func::printOption($arrayRslt['config']['relation_cd']) }}
                </select>
            </div>
        </td>
        <td align="center">
            <div class="col-md-12" >
                <select class="form-control form-control-sm col-md-10"  name="owner_type[]" id="owner_type${owner_cnt}" onChange="chOwnType(this.value, 'owner_ratio${owner_cnt}')">
                <option value=''>선택</option>
                {{ Func::printOption($arrayRslt['config']['mortgage_own_type']) }}
                </select>
            </div>
        </td>
        <td align="center">
            <div class="col-md-12">
                <div class="col-md-6 row">
                <input type="text" class="form-control form-control-sm col-md-8" onkeyup="onlyNumber(this);" name="owner_ratio[]" id="owner_ratio${owner_cnt}" maxlength="3">
                <span class="ml-1 pt-2">%</span>
                </div>
            </div>
        </td>
        <td align="center">
            <div class="col-md-12" >
                <select class="form-control form-control-sm col-md-10"  name="dambo_offer_yn[]" id="dambo_offer_yn${owner_cnt}">
                <option value=''>선택</option>
                {{ Func::printOption($arrayRslt['vars']['yn']) }}
                </select>
            </div>
        </td>
        <td align="center">
            <div class="input-group date datetimepicker col-md-10 mt-0" id="target_own_reg_date${owner_cnt}" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm datetimepicker-input " name="own_reg_date[]" id="own_reg_date${owner_cnt}" dateonly="true" size="6" data-target="#own_reg_date${owner_cnt}" required>
                <div class="input-group-append" data-target="#own_reg_date${owner_cnt}" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
            </div> 
        </td>
        <td align="center">    
        </td>
    </tr> 
</script>

<script id="rental_tmpl" type="text/tmpl">
    <tr>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <input type="text" class="form-control form-control-sm col-md-10" name="rental_name[]" id="rental_name${rental_cnt}">
            </div>
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <select class="form-control form-control-sm col-md-10"  name="rental_type[]" id="rental_type${rental_cnt}">
                <option value=''>선택</option>
                {{ Func::printOption($arrayRslt['config']['dambo_rental_type'], '') }}
                </select>
            </div>
        </td>
        <td align="center">
            <div class="input-group date datetimepicker col-md-12 mt-0" id="target_rental_sdate${rental_cnt}" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm datetimepicker-input " name="rental_sdate[]" id="rental_sdate${rental_cnt}" dateonly="true" size="6" required>
                <div class="input-group-append" data-target="#rental_sdate${rental_cnt}" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
            </div> 
        </td>
        <td align="center">
            <div class="input-group date datetimepicker col-md-12 mt-0" id="target_rental_edate${rental_cnt}" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm datetimepicker-input " name="rental_edate[]" id="rental_edate${rental_cnt}" dateonly="true" size="6" required>
                <div class="input-group-append" data-target="#rental_edate${rental_cnt}" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
            </div> 
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <input type="text" class="form-control form-control-sm col-md-10 comma" name="rental_deposit[]" onkeyup="onlyNumber(this);" id="rental_deposit${rental_cnt}">
            </div>
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <input type="text" class="form-control form-control-sm col-md-10 comma" name="rental_month_money[]" onkeyup="onlyNumber(this);" id="rental_month_money${rental_cnt}" >
            </div>
        </td>
        <td align="center">
            <div class="input-group date datetimepicker col-md-12 mt-0" id="target_rental_config_date${rental_cnt}" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm datetimepicker-input " name="rental_config_date[]" id="rental_config_date${rental_cnt}" dateonly="true" size="6" required>
                <div class="input-group-append" data-target="#rental_config_date${rental_cnt}" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
            </div> 
        </td>
        <td align="center">
            <div class="input-group date datetimepicker col-md-12 mt-0" id="target_rental_confirm_date${rental_cnt}" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm datetimepicker-input " name="rental_confirm_date[]" id="rental_confirm_date${rental_cnt}" value="{{$rentalList[$i]->rental_confirm_date ?? ''}}" dateonly="true" size="6" required>
                <div class="input-group-append" data-target="#rental_confirm_date${rental_cnt}" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
            </div> 
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <select class="form-control form-control-sm col-md-10"  name="rental_contract_yn[]" id="rental_contract_yn${rental_cnt}">
                <option value=''>선택</option>
                {{ Func::printOption($arrayRslt['vars']['yn'], '') }}
                </select>
            </div>
        </td>
        <td align="center">
            
        </td>
    </tr>
</script>

<script id="prior_tmpl" type="text/tmpl">
    <tr>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <select class="form-control form-control-sm col-md-10"  name="type[]" id="type${prior_cnt}">
                <option value=''>선택</option>
                {{ Func::printOption($arrayRslt['config']['mortgage_prior_type']) }}
                </select>
            </div>
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <select class="form-control form-control-sm col-md-10"  name="setter_type[]" id="setter_type${prior_cnt}">
                <option value=''>선택</option>
                {{ Func::printOption($arrayRslt['config']['mortgage_setter_type']) }}
                </select>
            </div>
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <input type="text" class="form-control form-control-sm col-md-10" name="setter[]" id="setter${prior_cnt}">
            </div>
        </td>
        <td align="center">
            <div class="row p-0 m-0">
                <div class="col-md-6 p-0 m-0">
                    <input type="text" class="form-control form-control-sm col-md-12" name="cust[]" id="cust${prior_cnt}">
                </div>
                <div class="col-md-6 p-0 m-0">
                    <select class="form-control form-control-sm col-md-12"  name="cust_rel[]" id="cust_rel${prior_cnt}" onChange="setMyName('cust${prior_cnt}', this.value)">
                    <option value=''>선택</option>
                    {{ Func::printOption($arrayRslt['config']['relation_cd']) }}
                    </select>
                </div>
            </div>
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <input type="text" class="form-control form-control-sm col-md-10 comma" onkeyup="onlyNumber(this);" name="set_money[]" id="set_money${prior_cnt}">
            </div>
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <input type="text" class="form-control form-control-sm col-md-10 comma" onkeyup="onlyNumber(this);" name="loan_origin[]" id="loan_origin${prior_cnt}">
            </div>
        </td>
        <td align="center">
            <div class="input-group date datetimepicker col-md-11 mt-0" id="target_set_date${prior_cnt}" data-target-input="nearest">
                <input type="text" class="form-control form-control-sm datetimepicker-input " name="set_date[]" id="set_date${prior_cnt}" dateonly="true" size="6" data-target="#set_date${prior_cnt}" required>
                <div class="input-group-append" data-target="#set_date${prior_cnt}" data-toggle="datetimepicker">
                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                </div>
            </div> 
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <input type="text" class="form-control form-control-sm col-md-10" name="dambo_offer[]" id="dambo_offer${prior_cnt}">
            </div>
        </td>
        <td align="center">
        </td>
    </tr>
</script>

<script>
getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());

function checkDocument(loan_app_no)
{   
    window.open("/ups/custrealestatedoccheck?loan_app_no=" + loan_app_no, "documentCheck" + loan_app_no, "left=100, top=100, width=1000, height=1000, scrollbars=yes");
}

// 준공일은 년도만 출력
$("#completion_date").datetimepicker({
    format: 'YYYY',
    locale: 'ko',
    useCurrent: false,
}); 

// 소유자 현황 count
var owner_cnt = {{ isset($ownerList) ? sizeof($ownerList) : 0 }};

// 권리순위 count
var prior_cnt = {{ isset($priorList) ? sizeof($priorList) : 0 }};

// 임대현황
var rental_cnt = {{ isset($rentalList) ? sizeof($rentalList) : 0 }};

function modeChk(mode)
{
    var modeArr = [];
    modeArr['INS'] = "등록";
    modeArr['UPD'] = "저장";
    modeArr['DEL'] = "삭제";
    
    if(mode == 'DEL')
    {
        if(confirm("정말 삭제 하시겠습니까?"))
        {
            custRealEstateAction(mode);
        }
        return;
    }

    if(!validChk(''))
    {
        //alert('필수 입력 사항을 모두 입력해주세요.');
        return false;
    }

    // if(!validChk('ownRateChk'))
    // {
    //     alert('소유자 지분율 합계가 100이 아닙니다. 소유현황을 확인해주세요');
    //     return false;
    // }

    if(!validChk('priorChk'))
    {
        return false;
    }

    if(!confirm("정말 "+modeArr[mode]+"하시겠습니까?"))
    {
        return false;
    }

    custRealEstateAction(mode);
}

function custRealEstateAction(mode) {
    var postdata = $('#real_estate_form').serialize();
    postdata = postdata + "&mode=" + mode + "&ownerCnt=" + owner_cnt + "&priorCnt=" + prior_cnt + "&rentalCnt=" + rental_cnt;

    console.log(postdata);

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url  : "/erp/custrealestateaction",
        type : "post",
        data : postdata,
        success : function(data)
        {
            // 유효성검사 실패시 에러메세지 표시
            if(data.error) 
            {
                console.log(data.error);
                printErrorMsg(data.error);
            }
            // 성공알림 
            else if(data.rs_code=="Y") 
            {
                alert(data.rs_msg);

                if(data.mode === 'DEL')
                {
                    getCustData('realestate');
                }
                else
                {
                    getCustData('realestate',data.no);
                }
            }
            // 실패알림
            else 
            {
                alert(data.rs_msg);
                //getCustData('realestate',data.no);
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다. 관리자에게 문의해주세요.");
            //getCustData('realestate');
        }
    });
}

// 기본정보
var arrMust  = {
            //"zip11"           : "물건소재지를 선택해주세요",
            "addr12"            : "물건소재지 상세주소를 입력해주세요",
            "unique_code"       : "등기고유번호를 입력해주세요",
            "tenant_yn"         : "임차인여부를 선택해주세요",
            "house_type"        : "담보구분을 선택해주세요",
            "move_in_name"      : "전입세대열람을 입력해주세요",
            "move_in_type"      : "전입세대열람 구분을 선택해주세요",
            "jeonyong_m"        : "전용면적을 입력해주세요",
			"value_middle"      : "매매일반가를 입력해주세요",
			"final_value"       : "결정가를 입력해주세요",
			"basic_value"       : "기준가를 입력해주세요",
            /*
            "completion_date"   : "준공연도를 입력해주세요",
            "item_floor"        : "해당 층수를 입력해주세요",
            "item_total_floor"  : "총 층수를 입력해주세요",
            "item_households"   : "총 세대수를 입력해주세요",
            "court_sale_rate"   : "대법원 매각가율을 입력해주세요",
            "infocare_rate"     : "인포케어 낙찰가율을 입력해주세요",
            "app_agency"        : "감정기관을 입력해주세요",
            "value_low"         : "매매하한가를 입력해주세요",
            "value_high"        : "매매상한가를 입력해주세요",
            */
        };

// 소유현황
var arrOwner  = {
            "owner_name"        : "소유현황 소유자명을 입력해주세요",
            "owner_relation"    : "소유현황 소유관계를 선택해주세요",
            "owner_type"        : "소유현황 소유구분을 선택해주세요",
            "owner_rate"        : "소유현황 지분율을 입력해주세요",
            "dambo_offer_yn"    : "소유현황 담보제공여부를 선택해주세요",
            "own_reg_date"      : "소유현황 쇼유권등기일을 입력해주세요",
        };

// 임대현황
var arrRental = {
            "rental_name"       : "임대현황 임차인을 입력해주세요",
            "rental_type"       : "임대현황 임차구분을 선택해주세요",
            "rental_sdate"      : "임대현황 임차시작일을 입력해주세요",
            "rental_edate"      : "임대현황 임차만료일을 입력해주세요",
            "rental_deposit"    : "임대현황 임차보증금을 입력해주세요",
            "rental_contract_yn": "임대현황 임대차계약확정여부을 입력해주세요",
        };

// 선순위내역
var arrPrior = {
            "type"              : "선순위내역 설정순위를 선택해주세요",
            "setter_type"       : "선순위내역 설정권자구분을 선택해주세요",
            "setter"            : "선순위내역 설정권자를 입력해주세요",
            "cust"              : "선순위내역 채무자를 입력해주세요",
            "cust_rel"          : "선순위내역 채무자관계를 선택해주세요",
            "set_money"         : "선순위내역 설정금액을 입력해주세요",
            "loan_origin"       : "선순위내역 대출원금을 입력해주세요",
            "set_date"          : "선순위내역 설정일자를 입력해주세요",
            "dambo_offer"       : "선순위내역 담보제공을 입력해주세요",
        };

function validChk(div)
{
    var rs = 1;
    if(div ==='')
    {   
        $.each(arrMust, function (key, val) 
        {
            if($("#"+key).val() == '' || $("#"+key).val() == 0)
            {
                alert(val);
                $("#"+key).focus();
                rs = 0;
                return false;
            }
        });
    
        if(!rs)
            return false;

        /*
        // 소유자현황
        for(var i=0; i<owner_cnt;i++)
        {
            $.each(arrOwner, function (key, val) 
            {
                if($("#"+key+i).val() == '' || $("#"+key+i).val() == 0)
                {
                    alert(val);
                    $("#"+key+i).focus();
                    rs = 0;
                    return false;
                }
            });

            if(!rs)
                return false;
        }

        if($("#tenant_yn").val()=='Y')
        {
            if(rental_cnt <= 0)
            {
                alert("임차인이 있을 경우 임대현황을 입력해주세요.");
                $("#btnAddRental").focus();
                return false;
            }
        }
        // 임대현황
        for(var i=0; i<rental_cnt;i++)
        {
            $.each(arrRental, function (key, val) 
            {
                console.log(key+i);
                console.log($("#"+key+i).val());

                if($("#"+key+i).val() == '' || $("#"+key+i).val() == 0)
                {
                    alert(val);
                    $("#"+key+i).focus();
                    rs = 0;
                    return false;
                }
            });

            if(!rs)
                return false;
        }

        // 후순위선택시 권리관계 필수입력
        var isSecond = 0;
        $.each($("#dambo_loan_type").val(), function (key, val){
            if(val=='02')
            {
                isSecond = 1;
                return true;
            }
        });
        if(isSecond)
        {
            if(prior_cnt <= 0)
            {
                alert("후순위 선택시 선순위내역은 필수로 입력해주세요.");
                $("#btnAddPrior").focus();
                return false;
            }
        }

        // 권리관계
        for(var i=0; i<prior_cnt;i++)
        {
            $.each(arrPrior, function (key, val) 
            {
                if($("#"+key+i).val() == '' || $("#"+key+i).val() == 0)
                {
                    alert(val);
                    $("#"+key+i).focus();
                    rs = 0;
                    return false;
                }
            });

            if(!rs)
                return false;
        }
        */

        return true;

    }

    // 소유자현황 확인 (합계 100 아닐 시 오류)
    if(div === 'ownRateChk')
    {
        var ownRateSum = 0;
        for(var i=0; i<owner_cnt;i++)
        {
            if(!isNaN($("#owner_ratio"+i).val()))
            {
                ownRateSum = ownRateSum + $("#owner_ratio"+i).val()*1;
            }
        }
        if(ownRateSum !== 100)
        {
            return false;
        }

    }

    // 선순위내역 설정확인 (임차인여부 Y : 전세권설정 필수 / 임차인여부 N : 전세권설정 등록시 오류)
    if(div === 'priorChk')
    {
        var tenantYn = $("#tenant_yn").val();
        var flag     = false;
        for(var i=0; i<prior_cnt;i++)
        {
            var type = $("#type"+i).val();

            // 임차인 없고 전세권 설정이 되어 있는경우.
            if(tenantYn === 'N' && type === 'A')
            {
                alert('임차인이 없을경우 전세권 설정을 할 수 없습니다.');
                $("#type"+i).focus();
                return false;
            }
            // 임차인이 있고 전세권설정이 되어 있으면 true
            else if(tenantYn === 'Y' && type === 'A')
            {
                flag = true;
            }
        }

		/*
        if(tenantYn === 'Y' && flag !== true)
        {
            alert("임차인 존재시 전세권설정은 필수입니다.\n\n선순위내역을 추가해서 입력해주세요");
            return false;
        }
		*/
    }

    return true;
    
}

//소유자추가
function addOwner()
{
    $("#owner_tmpl").template("owner_tmpl");
    $.tmpl("owner_tmpl",owner_cnt).appendTo("#ownerTb"); 
    afterAjaxId("own_reg_date"+owner_cnt);
    owner_cnt = owner_cnt+1;
}

// 선순위 추가
function addPrior()
{
    $("#prior_tmpl").template("prior_tmpl");
    $.tmpl("prior_tmpl",prior_cnt).appendTo("#priorTb"); 
    $("#set_money"+prior_cnt).number(true);
    $("#loan_origin"+prior_cnt).number(true);
    afterAjaxId("set_date"+prior_cnt);
    prior_cnt = prior_cnt+1;
}

// 임대현황 추가
function addRental()
{
    $("#rental_tmpl").template("rental_tmpl");
    $.tmpl("rental_tmpl",rental_cnt).appendTo("#rentalTb"); 
    $("#rental_deposit"+rental_cnt).number(true);
    $("#rental_month_money"+rental_cnt).number(true);
    afterAjaxId("rental_sdate"+rental_cnt);
    afterAjaxId("rental_edate"+rental_cnt);
    afterAjaxId("rental_config_date"+rental_cnt);
    afterAjaxId("rental_confirm_date"+rental_cnt);
    afterAjaxId("rental_edate"+rental_cnt);    
    rental_cnt = rental_cnt+1;
}

function afterAjaxId(id)
{
    $("#"+id).datetimepicker({
        format: 'YYYY-MM-DD',
		locale: 'ko',
		useCurrent: false,
    }); 
}

function deleteTr(div) {

    if(div=="owner")
    {
        // 기존 소유자는 취소안됨
        if(owner_cnt == {{ sizeof($ownerList) ?? 0 }})
        {
            return false;
        }
        owner_cnt = owner_cnt - 1;
    }
    else if(div=="prior")
    {
        // 기존 권리관계는 취소안됨
        if(prior_cnt == {{ sizeof($priorList) ?? 0 }})
        {
            return false;
        }
        prior_cnt = prior_cnt - 1;
    }
    else if(div=="rental")
    {
        // 기존 취소안됨
        if(rental_cnt == {{ sizeof($rentalList) ?? 0 }})
        {
            return false;
        }
        rental_cnt = rental_cnt - 1;
    }
    
    var tb = document.getElementById(div+"Tb");
    tb.lastChild.remove();
}

function calLTV()
{
    // 최종결정가
    var lastFinalValue = $("#basic_value").val().replace(/,/gi, "");  // 기준가
    var marketRate = $("#market_rate").val();  // 시세 N% 적용

    if(!lastFinalValue || lastFinalValue==0)
    {
        alert('기준가를 입력해주세요');
        $("#basic_value").focus();
        return false;
    }

    // 시세적용
    // if(marketRate!='' && marketRate>0)
    //     lastFinalValue = lastFinalValue*marketRate/100;


    $("#last_final_value").val($('#final_value').val()).number(true);
    
    // 임대차 보증금
    var rentalDepositSum = 0;
    for(var i=0; i<rental_cnt;i++)
    {
        var rentalDeposit = $("#rental_deposit"+i).val().replace(/,/gi, "");
        rentalDepositSum += rentalDeposit*1;
    }

    // 선순위 설정금액
    var priorConfigMoneySum = 0;

    // 선순위 대출원금
    var priorBalanceSum = 0;

    for(var i=0; i<prior_cnt;i++)
    {
        var type       = $("#type"+i).val();
        var setMoney   = $("#set_money"+i).val().replace(/,/gi, "");
        var loanOrigin = $("#loan_origin"+i).val().replace(/,/gi, "");

        // 전세권제외
        if(type ==='A')
        {    
            // 임대차보증금 -> 권리관계 전세권 설정금액
            $("#rental_deposit").val(setMoney*1).number(true);   
            continue;
        }
       
        // 선순위 대출원금
        priorBalanceSum     += loanOrigin*1;
        
        // 선순위 설정금액
        priorConfigMoneySum += setMoney*1;
    }

    // 임대차보증금
    $("#rental_deposit_sum").val(rentalDepositSum).number(true);

    // 선순위 설정금액
    $("#prior_config_money").val(priorConfigMoneySum).number(true);
    // 선순위 대출원금
    $("#prior_balance").val(priorBalanceSum).number(true);

    // 승인금액 가져오기
    $("#approve_money").val($('#loan_money').val()).number(true);

    // ltv 산출 - (선순위대출잔액+당사승인금액+압류내역(갑구,을구) 잔액/ 최종결정가) *** 확인필요
    // 압류내역(갑구) 잔액 : + $("#one_balance").val().replace(/,/gi, "")*1
    // 압류내역(을구) 잔액 : + $("#two_balance").val().replace(/,/gi, "")*1
    var ltv = (rentalDepositSum*1 + priorBalanceSum*1 + $("#approve_money").val().replace(/,/gi, "")*1 ) / lastFinalValue * 100;
    var max_ltv = (rentalDepositSum*1 + priorConfigMoneySum*1 + $("#approve_money").val().replace(/,/gi, "")*1 ) / lastFinalValue * 100;
    
    if(isNaN(max_ltv))
    {
        max_ltv = 0;
    }
    // 소수점 2자리 자름
    else
    {
        max_ltv = Math.floor(max_ltv*100)/100;
    }

    if(isNaN(ltv))
    {
        ltv = 0;
    }
    // 소수점 2자리 자름
    else
    {
        ltv = Math.floor(ltv*100)/100;
    }

    $("#max_ltv").val(max_ltv).number(true, 2);
    $("#ltv").val(ltv).number(true, 2);
}

// 중복검사
function duplChk()
{
    var code = $("#unique_code").val();

    if(code == '')
    {
        alert('등기고유번호를 입력해주세요');
        $("#unique_code").focus();
        return;
    }
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url  : "/ups/custrealestateduplchk",
        type : "post",
        data : {code:code},
        success : function(data)
        {
            alert(data.msg);
        },
        error : function(xhr)
        {
            alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
}

// 소유 단독, 공동
function chOwnType(ownType, id)
{
    // 단독소유
    if(ownType=='M')
    {
        $('#'+id).val('100');
        $('#'+id).attr("readonly", true);
    }
    else
    {
        $('#'+id).attr("readonly", false);
        $('#'+id).focus();
    }
    
}

function setMyName(colId, val)
{
    if(val=='01')
    {
        $('#'+colId).val('{{ $custInfo->name }}');
    }
}

// select 특정값으로 변경시 메모 활성화
function chSetMemo(thisValue, targetVal, colId, mySel)
{
    // 본인 선택시 이름넣어주기.
    if(mySel!='')
    {
        setMyName(mySel, thisValue);
    }

    if(thisValue==targetVal)
    {
        $('#'+colId).attr("disabled", false);
        $('#'+colId).focus();
    }
    else
    {
        $('#'+colId).attr("disabled", true);
    }
}

function valCopy(val)
{
    var chk = $("#"+val).val();
    $("#basic_value").val(chk);
    $("#final_value").val(chk);
}
// selectpicker
$('.selectwidth100').selectpicker({
    width: '100%',
    style: 'btn-default form-control-sm bg-white',
});

</script>
