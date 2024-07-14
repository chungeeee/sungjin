
<!-- 차량담보 -->
<div class="col-md-12 p-0 m-0 " >
        <div class="card-header p-1" style="border-bottom:none !important;">
            <h6 class="card-title"><i class="fas fa-donate m-2"></i>차량담보</h6>
            <div class="card-tools pr-2">
                <button type="button" class="btn btn-tool m-1 bg-lightblue" onclick="getCustData('car','INPUT');">
                담보추가
                </button>
            </div>
        </div>
        @include('inc/listSimple')
        <div id='damboCarInput' style='display:@if(isset($v->no) || $mode== 'INS') block; @else none; @endif'>
        <form  class="mb-0" name="car_form" id="car_form" method="post" enctype="multipart/form-data">
        <input type="hidden" class="form-control form-control-sm col-md-2" name="no" id="no" value="{{ $v->no ?? '' }}" >
        <input type="hidden" name="loan_info_no" value="{{ $result['customer']['loan_info_no'] ?? '' }}">
        <input type="hidden" name="cust_info_no" value="{{ $result['customer']['cust_info_no'] ?? '' }}">
        <div class="col-md-12 pt-4">
            <table class="table table-sm table-bordered table-input text-xs" id="tblBackground" style="border:none">
                <colgroup>
                <col width="7%"/>
                <col width="10%"/>
                <col width="17%"/>
                <col width="10%"/>
                <col width="17%"/>
                <col width="12%"/>
                <col width="17%"/>
                </colgroup>
                <tbody>
                <tr>
                    <th rowspan="7" class="text-center">담보정보</th>
                    <th><span class="text-danger font-weight-bold h6 mr-1">*</span>최초등록일</th>
                        <td>
                            <div class="input-group date datetimepicker col-md-7 p-0"  id="fst_reg_date_picker" data-target-input="nearest">
                                <input type="text" class="form-control form-control-sm" name="fst_reg_date" id="fst_reg_date" value="{{ $v->fst_reg_date ?? '' }}" dateonly="true" size="6" required>
                                <div class="input-group-append" data-target="#fst_reg_date_picker" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div> 
                        </td>
                        <th><span class="text-danger font-weight-bold h6 mr-1">*</span>차량이전일</th>
                        <td>
                            <div class="input-group date datetimepicker col-md-7 p-0" id="car_reg_date_picker" data-target-input="nearest">
                                <input type="text" class="form-control form-control-sm " name="car_reg_date" id="car_reg_date" value="{{ $v->car_reg_date ?? '' }}" dateonly="true" size="6" required>
                                <div class="input-group-append" data-target="#car_reg_date_picker" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div> 
                        </td>
                        <th><span class="text-danger font-weight-bold h6 mr-1">*</span>운행자</th>
                        <td>
                            <div class="col-md-12 row p-0 m-0">
                                <div class="col-md-7 p-0 m-0">
                                    <input type="text" class="form-control form-control-sm col-md-12" name="user_nm" id="user_nm" value="{{ $v->user_nm ?? '' }}">                                
                                </div>
                                <div class="col-md-5">
                                    <select class="form-control form-control-sm col-md-10"  name="user_rel" id="user_rel">
                                    <option value=''>선택</option>
                                    {{ Func::printOption($arrayRslt['config']['relation_cd'], isset($v->user_rel) ? $v->user_rel : '' ) }}
                                    </select>              
                                </div>
                            </div>
                        </td>
                </tr>
                <tr>                     
                    <th><span class="text-danger font-weight-bold h6 mr-1">*</span>차량번호</th>
                    <td>
                        <input type="text" class="form-control form-control-sm col-md-7" name="car_no" id="car_no" value="{{ $v->car_no ?? '' }}">
                    </td>
                
                    <th><span class="text-danger font-weight-bold h6 mr-1">*</span>차대번호</th>
                    <td>
                        <input type="text" class="form-control form-control-sm col-md-7" name="car_dae_no" id="car_dae_no" value="{{ $v->car_dae_no ?? '' }}">
                    </td>
                    <th><span class="text-danger font-weight-bold h6 mr-1">*</span>차명</th>
                    <td>
                        <input type="text" class="form-control form-control-sm col-md-7" name="model" id="model" value="{{ $v->model ?? '' }}">
                    </td>
                </tr>
                <tr>
                    <th>차량연식</th>
                    <td>
                        <div class="input-group date datetimepicker col-md-7 p-0" id="model_year" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm " name="model_year" id="model_year" value="{{ $v->model_year ?? '' }}" dateonly="true" size="4" required>
                            <div class="input-group-append" data-target="#model_year" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div> 
                    </td>
                    <th>차량색상</th>
                    <td>
                        <input type="text" class="form-control form-control-sm col-md-7" name="model_color" id="model_color" value="{{ $v->model_color ?? '' }}">
                    </td>
                    <th>연료종류</th>
                    <td colspan="3">
                        <select class="form-control form-control-sm col-md-7"  name="fuel_type" id="fuel_type" value="{{ $v->fuel_type ?? '' }}">
                        <option value=''>선택</option>
                        {{ Func::printOption($arrayRslt['config']['mortgage_fuel_type'], isset($v->fuel_type) ? $v->fuel_type : '' ) }}
                        </select>
                    </td>
                </tr> 
                <tr>
                   <th>주행거리(km)</th>
                    <td>
                        <div class="row text-center">
                        <input type="text" class="form-control form-control-sm col-md-4  ml-2 comma" name="drive_distance" id="drive_distance" onkeyup="onlyNumber(this);" value="{{ $v->drive_distance ?? '' }}">
                        <span class="pt-2"> km</span>
                    </div>
                    </td>
                    <th>구동방식</th>
                    <td>
                        <select class="form-control form-control-sm col-md-7"  name="driving_method" id="driving_method" value="{{ $v->driving_method ?? '' }}">
                        <option value=''>선택</option>
                        {{ Func::printOption($arrayRslt['config']['driving_method'], isset($v->driving_method) ? $v->driving_method : '' ) }}
                        </select>
                    </td>
                    <th>출처구분</th>
                    <td>
                        <select class="form-control form-control-sm col-md-7"  name="car_div_cd" id="car_div_cd" value="{{ $v->car_div_cd ?? '' }}">
                        <option value=''>선택</option>
                        {{ Func::printOption($arrayRslt['config']['car_div_cd'], isset($v->car_div_cd) ? $v->car_div_cd: '' ) }}
                        </select>
                    </td>
                </tr>
                <tr>
                   <th>자차보험여부</th>
                    <td colspan="1">
                        <select class="form-control form-control-sm col-md-7"  name="self_insurance_yn" id="self_insurance_yn">
                        <option value=''>선택</option>
                        {{ Func::printOption($arrayRslt['vars']['yn'], isset($v->self_insurance_yn) ? $v->self_insurance_yn : '' ) }}
                        </select>
                    </td>
                </tr>
                <tr>
                    <th>사고내역</th>
                    <td colspan="5">
                        <textarea class="form-control" rows="2" name="accident_etc" id="accident_etc">{{ $v->accident_etc ?? '' }}</textarea>
                    </td>
                </tr>
                <tr>
                    <th>차량정보</th>
                    <td colspan="5">
                        <textarea class="form-control" rows="2" name="car_info_etc" id="car_info_etc" >{{ $v->car_info_etc ?? '' }}</textarea>
                    </td>
                </tr>
                <th rowspan="3" class="text-center">소유현황</th>
                <tr>
                    <th>소유구분</th>
                    <td colspan="3">
                        <select class="form-control form-control-sm col-md-3"  name="together_yn" id="together_yn" onchange="togetherChk();">
                        <option value=''>선택</option>
                        {{ Func::printOption($arrayRslt['vars']['own'], isset($v->together_yn) ? $v->together_yn : '' ) }}
                        </select>
                    </td>
                </tr>
                <tr>
                   <th>소유자현황</th>
                    <td colspan="5">
                        <table class="table table-sm  table-input text-xs m-0 p-0">
                            <colgroup>
                            <col width="11%"/>
                            <col width="27%"/>
                            <col width="11%"/>
                            <col width="27%"/>
                            </colgroup>
                            <tbody>
                                <tr>
                                    <th class="text-center">소유자1</th>
                                    <td>
                                        <div class="col-md-12 row p-0 m-0">
                                            <div class="col-md-4 p-0 m-0">
                                                <input type="text" class="form-control form-control-sm col-md-12" name="owner_nm1" id="owner_nm1" value="{{ $v->owner_nm1 ?? '' }}">                                
                                            </div>
                                            <div class="col-md-4 pr-0">
                                                <select class="form-control form-control-sm col-md-12"  name="owner_rel1" id="owner_rel1">
                                                <option value=''>선택</option>
                                                {{ Func::printOption($arrayRslt['config']['relation_cd'], isset($v->owner_rel1) ? $v->owner_rel1 : '' ) }}
                                                </select>              
                                            </div>
                                            <div class="col-md-4">
                                                <select class="form-control form-control-sm col-md-12"  name="owner_dambo_yn1" id="owner_dambo_yn1" >
                                                <option value=''>담보제공여부</option>
                                                {{ Func::printOption($arrayRslt['vars']['yn'], isset($v->owner_dambo_yn1) ? $v->owner_dambo_yn1 : '' ) }}
                                                </select>              
                                            </div>
                                        </div>
                                    </td>
                                    <th class="text-center">소유자2</th>
                                    <td>
                                        <div class="col-md-12 row p-0 m-0">
                                            <div class="col-md-4 p-0 m-0">
                                                <input type="text" class="form-control form-control-sm col-md-12" name="owner_nm2" id="owner_nm2" value="{{ $v->owner_nm2 ?? '' }}" @if(empty($v->together_yn) || $v->together_yn === 'N') readonly disabled @endif>                                
                                            </div>
                                            <div class="col-md-4 pr-0">
                                                <select class="form-control form-control-sm col-md-12"  name="owner_rel2" id="owner_rel2" @if(empty($v->together_yn) || $v->together_yn === 'N') readonly disabled @endif>
                                                <option value=''>선택</option>
                                                {{ Func::printOption($arrayRslt['config']['relation_cd'], isset($v->owner_rel2) ? $v->owner_rel2 : '' ) }}
                                                </select>              
                                            </div>
                                            <div class="col-md-4 ">
                                                <select class="form-control form-control-sm col-md-12"  name="owner_dambo_yn2" id="owner_dambo_yn2" @if(empty($v->together_yn) || $v->together_yn === 'N') readonly disabled @endif>
                                                <option value=''>담보제공여부</option>
                                                {{ Func::printOption($arrayRslt['vars']['yn'], isset($v->owner_dambo_yn2) ? $v->owner_dambo_yn2 : '' ) }}
                                                </select>              
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
                <tr>
                    <th class="text-center">시세현황</th>
                    <td colspan="6">
                        <table class="table table-sm table-bordered table-input text-xs m-0 p-0" style="border:none">
                            <colgroup>
                            <col width="12%"/>
                            <col width="38%"/>
                            <col width="12%"/>
                            <col width="38%"/>
                            </colgroup> 
                            <tbody>
                                <tr>
                                    <th>차량시세1(원)</th>
                                    <td>
                                        <div class="col-md-12 row p-0 m-0">
                                            <div class="col-md-5 p-0 m-0">
                                                <input type="text" class="form-control form-control-sm col-md-12 comma" name="car_value1" id="car_value1" value="{{ $v->car_value1 ?? '' }}" onkeyup="onlyNumber(this);">                                
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" placeholder="확인처" class="form-control form-control-sm col-md-12" name="car_value_checker1" id="car_value_checker1" value="{{ $v->car_value_checker1 ?? '' }}">               
                                            </div>
                                        </div>
                                    </td>
                                    <th>차량시세2(원)</th>
                                    <td>
                                        <div class="col-md-12 row p-0 m-0">
                                            <div class="col-md-5 p-0 m-0">
                                                <input type="text" class="form-control form-control-sm col-md-12 comma" name="car_value2" id="car_value2" value="{{ $v->car_value2 ?? '' }}" onkeyup="onlyNumber(this);">                                
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" placeholder="확인처" class="form-control form-control-sm col-md-12" name="car_value_checker2" id="car_value_checker2" value="{{ $v->car_value_checker2 ?? '' }}">               
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <th>차량출고가(원)</th>
                                    <td>
                                        <div class="col-md-12 row p-0 m-0">
                                            <div class="col-md-5 p-0 m-0">
                                                <input type="text" class="form-control form-control-sm col-md-12 comma" name="car_release_money" id="car_release_money" value="{{ $v->car_release_money ?? '' }}" onkeyup="onlyNumber(this);" onchange="setDecisionRate(this)">                                
                                            </div>
                                        </div>
                                    </td>
                                    <th>차량결정가(원)/(%)</th>
                                    <td>
                                        <div class="col-md-12 row p-0 m-0">
                                            <div class="col-md-5 p-0 m-0">
                                                <input type="text" class="form-control form-control-sm col-md-12 comma" name="car_decision_money" id="car_decision_money" value="{{ $v->car_decision_money ?? '' }}" onkeyup="onlyNumber(this);"  onchange="setDecisionRate(this)">                                
                                            </div>
                                            <div class="col-md-3">
                                                <input type="text" placeholder="%" class="form-control form-control-sm col-md-12" name="car_decision_rate" id="car_decision_rate"  value="{{ isset($v->car_decision_rate) ? $v->car_decision_rate.'%' : '%' }}"readonly >               
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr> 
                <tr>
                    <th class="text-center"><span class="text-center text-danger font-weight-bold h6 mr-1">*</span>권리관계<br>(선순위)</th>
                    <td colspan="6">
                        <div class="col-md-12 pt-1 pb-0" style="text-align:right;">
                            <button type="button" class="btn btn-default btn-sm text-xxs" onclick="addPrior();"><i class="fas fa-plus-circle p-1" style="color:green;"></i>추가</button>
                            <button type="button" class="btn btn-default btn-sm text-xxs" onclick="deleteTr('prior');"><i class="fas fa-minus-circle p-1" style="color:red;"></i>취소</button>
                        </div>
                        <table class="table table-sm table-bordered table-input text-xs">
                            <colgroup>
                            <col width="10%"/>
                            <col width="10%"/>
                            <col width="10%"/>
                            <col width="14%"/>
                            <col width="11%"/>
                            <col width="11%"/>
                            <col width="9%"/>
                            <col width="8%"/>
                            </colgroup>
                            <tr>
                                    <th style="text-align:center !important;" class="p-0"><span class="text-danger font-weight-bold h6 mr-1">*</span>권리순위</th>
                                    <th style="text-align:center !important;" class="p-0"><span class="text-danger font-weight-bold h6 mr-1">*</span>설정권자구분</th>
                                    <th style="text-align:center !important;" class="p-0"><span class="text-danger font-weight-bold h6 mr-1">*</span>설정권자</th>
                                    <th style="text-align:center !important;" class="p-0"><span class="text-danger font-weight-bold h6 mr-1">*</span>채무자(관계)</th>
                                    <th style="text-align:center !important;" class="p-0"><span class="text-danger font-weight-bold h6 mr-1">*</span>채권가액(원)</th>
                                    <th style="text-align:center !important;" class="p-0"><span class="text-danger font-weight-bold h6 mr-1">*</span>대출원금(원)</th>
                                    <th style="text-align:center !important;" class="p-0"><span class="text-danger font-weight-bold h6 mr-1">*</span>대환여부</th>
                                    <th style="text-align:center !important;" class="p-0">처리</th>
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
                                            <div id="type{{$i}}_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <select class="form-control form-control-sm col-md-10"  name="setter_type[]" id="setter_type{{$i}}">
                                            <option value=''>선택</option>
                                            {{ Func::printOption($arrayRslt['config']['mortgage_setter_type'], $priorList[$i]->setter_type) }}
                                            </select>
                                            <div id="setter_type{{$i}}_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <input type="text" class="form-control form-control-sm col-md-10" name="setter[]" id="setter{{$i}}" value="{{$priorList[$i]->setter ?? ''}}">
                                            <div id="setter{{$i}}_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="row p-0 m-0">
                                            <div class="col-md-6 p-0 m-0">
                                                <input type="text" class="form-control form-control-sm col-md-12" name="cust[]" id="cust{{$i}}" value="{{$priorList[$i]->cust ?? ''}}">
                                                <div id="cust{{$i}}_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                            </div>
                                            <div class="col-md-6 p-0 m-0">
                                                <select class="form-control form-control-sm col-md-12"  name="cust_rel[]" id="cust_rel{{$i}}">
                                                <option value=''>선택</option>
                                                {{ Func::printOption($arrayRslt['config']['relation_cd'], $priorList[$i]->cust_rel) }}
                                                </select>
                                                <div id="cust_rel{{$i}}_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                            </div>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <input type="text" class="form-control form-control-sm col-md-10 comma" name="set_money[]" onkeyup="onlyNumber(this);" id="set_money{{$i}}" value="{{$priorList[$i]->set_money ?? ''}}">
                                            <div id="set_money{{$i}}_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <input type="text" class="form-control form-control-sm col-md-10 comma" name="loan_origin[]" onkeyup="onlyNumber(this);" id="loan_origin{{$i}}" value="{{$priorList[$i]->loan_origin ?? ''}}">
                                            <div id="loan_origin{{$i}}_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <select class="form-control form-control-sm col-md-10"  name="repay_yn[]" id="repay_yn{{$i}}">
                                            <option value=''>선택</option>
                                            {{ Func::printOption($arrayRslt['vars']['yn'], $priorList[$i]->repay_yn) }}
                                            </select>
                                            <div id="repay_yn{{$i}}_error" class="text-danger pt-2 pl-2 error-msg"></div>
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
                    <th class="text-center">압류내역(갑구)</th>
                    <td colspan="6">
                        <div class="col-md-12 pt-1 pb-0" style="text-align:right;">
                            <button type="button" class="btn btn-default btn-sm text-xxs" onclick="addDistraint();"><i class="fas fa-plus-circle p-1" style="color:green;"></i>추가</button>
                            <button type="button" class="btn btn-default btn-sm text-xxs" onclick="deleteTr('distraint');"><i class="fas fa-minus-circle p-1" style="color:red;"></i>취소</button>
                        </div>
                        <table class="table table-sm table-bordered table-input text-xs">
                            <colgroup>
                            <col width="15%"/>
                            <col width="15%"/>
                            <col width="15%"/>
                            <col width="35%"/>
                            <col width="10%"/>
                          
                            </colgroup>
                            <th style="text-align:center !important;" class="p-0">설정액</th>
                            <th style="text-align:center !important;" class="p-0">잔액</th>
                            <th style="text-align:center !important;" class="p-0">대환여부</th>
                            <th style="text-align:center !important;" class="p-0">비고</th>
                            <th style="text-align:center !important;" class="p-0">처리</th>
                        
                            <tbody id="distraintTb">
                                @isset($distraintList)
                                @for ($i = 0; $i < sizeof($distraintList); $i++)
                                <tr>
                                    <input type="hidden" name="distraint_no[]" value="{{$distraintList[$i]->no}}">
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <input type="text" class="form-control form-control-sm col-md-10 comma" name="dis_config_money[]" id="dis_config_money{{$i}}" onkeyup="onlyNumber(this);" value="{{$distraintList[$i]->config_money ?? ''}}">
                                            <div id="config_money{{$i}}_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <input type="text" class="form-control form-control-sm col-md-10 comma" name="dis_origin_money[]" id="dis_origin_money{{$i}}" onkeyup="onlyNumber(this);" value="{{$distraintList[$i]->origin_money ?? ''}}"> 
                                            <div id="dis_origin_money{{$i}}_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <select class="form-control form-control-sm col-md-10"  name="dis_repay_yn[]" id="dis_repay_yn{{$i}}">
                                            <option value=''>선택</option>
                                            {{ Func::printOption($arrayRslt['vars']['yn'] , $distraintList[$i]->repay_yn ?? '' ) }}
                                            </select>
                                            <div id="repay_yn{{$i}}_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                        </div>
                                    </td>
                                    <td align="center">
                                        <div class="col-md-12 p-0 m-0">
                                            <input type="textarea" class="form-control form-control-sm col-md-11" name="etc[]" id="etc{{$i}}" value="{{$distraintList[$i]->etc ?? ''}}">
                                            <div id="etc{{$i}}_error" class="text-danger pt-2 pl-2 error-msg"></div>
                                        </div>
                                    </td>
                                     <td align="center">
                                        <input type="checkbox" name="distraint_del[{{$i}}]" value='{{$distraintList[$i]->no}}'> 삭제
                                    </td>
                                </tr>
                                @endfor
                                @endisset
                            </tbody>
                        </table>
                    </td>
                </tr>  
                <tr>
                    <th class="text-center" >LTV</th>
                    <td colspan="6">
                        <table class="table table-sm table-bordered table-input text-xs m-0 p-0">
                            <colgroup>
                            <col width="15%"/>
                            <col width="30%"/>
                            <col width="15%"/>
                            <col width="30%"/>
                            </colgroup>
                                <tr>
                                    <td colspan="4">
                                        <div class="col-md-12 p-0">
                                            <button type="button" class="btn btn-default btn-sm text-xs" onclick="calLTV();"><i class="fas fa-calculator p-1" style="color:green;"></i>계산</button>
                                        </div>
                                    </td>
                                </tr>
                            <tbody>
                                <tr>
                                    <th style="text-align:center !important;">최종결정가(원)</th>
                                    <td align="left"><input type="text" class="form-control form-control-sm col-md-5 comma" onkeyup="onlyNumber(this);" name="final_value" id="final_value" value="{{ $v->final_value ?? '' }}" readOnly ></td>
                                    <th style="text-align:center !important;">채권가액(원)</th>
                                    <td align="left">
                                        <input type="checkbox"  name="config_money_chk" id="config_money_chk">
                                    </td>
                                </tr>
                                <tr>
                                    <th style="text-align:center !important;" class="p-0">선순위 설정(원)</th>
                                    <td align="left"><input type="text" placeholder="권리순위 채권가액(sum)" onkeyup="onlyNumber(this);" onchange="configMnyChk(this)" class="form-control form-control-sm col-md-5 comma" name="prior_config_money" id="prior_config_money" value="{{ $v->prior_config_money ?? '' }}" readOnly></td>
                                    <th style="text-align:center !important;" class="p-0">선순위대출원금(원)</th>
                                    <td align="left">
                                        <input type="text" class="form-control form-control-sm col-md-5 comma"onkeyup="onlyNumber(this);" name="prior_balance" id="prior_balance" value="{{ $v->prior_balance ?? '' }}"readOnly  >                                
                                    </td>
                                </tr>
                                <tr>
                                    <th style="text-align:center !important;" class="p-0">승인금액(원)</th>
                                    @if ( isset($apr_money))
                                        <td align="left"><input type="text" class="form-control form-control-sm col-md-5 comma" onkeyup="onlyNumber(this);" name="approve_money" id="approve_money" value="{{ $apr_money->approve_money ?? '' }}" readOnly></td>
                                    @else
                                        <td align="left"><input type="text" class="form-control form-control-sm col-md-5 comma" onkeyup="onlyNumber(this);" name="approve_money" id="approve_money" value="{{ $v->approve_money ?? '' }}" readOnly></td>
                                    @endif
                                    <th style="text-align:center !important;" class="p-0">LTV(%)</th>
                                    <td align="left"><input type="text" class="form-control form-control-sm col-md-5" onkeyup="onlyRatio(this);" name="ltv_config_rate" id="ltv_config_rate" readOnly value="{{ $v->ltv_config_rate ?? '' }}"></td>
                                </tr>
                                <tr>
                                    <th style="text-align:center !important;" class="p-0">LTV 비고</th>
                                    <td colspan="3">
                                        <textarea class="form-control" rows="2" name="ltv_etc" id="ltv_etc" >{{ $v->ltv_etc ?? '' }}</textarea>
                                    </td>
                                    <input type="hidden" name ="config_money" id="config_money"/>
                                </tr>
                            </tbody>
                        </table>
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


<script id="prior_tmpl" type="text/tmpl">
    <tr>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <select class="form-control form-control-sm col-md-10"  name="type[]" id="type${prior_cnt}">
                <option value=''>선택</option>
                {{ Func::printOption($arrayRslt['config']['mortgage_prior_type']) }}
                </select>
                <div id="type${prior_cnt}_error" class="text-danger pt-2 pl-2 error-msg"></div>
            </div>
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <select class="form-control form-control-sm col-md-10"  name="setter_type[]" id="setter_type${prior_cnt}">
                <option value=''>선택</option>
                {{ Func::printOption($arrayRslt['config']['mortgage_setter_type']) }}
                </select>
                <div id="setter_type${prior_cnt}_error" class="text-danger pt-2 pl-2 error-msg"></div>
            </div>
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <input type="text" class="form-control form-control-sm col-md-10" name="setter[]" id="setter${prior_cnt}">
                <div id="setter${prior_cnt}_error" class="text-danger pt-2 pl-2 error-msg"></div>
            </div>
        </td>
        <td align="center">
            <div class="row p-0 m-0">
                <div class="col-md-6 p-0 m-0">
                    <input type="text" class="form-control form-control-sm col-md-12" name="cust[]" id="cust${prior_cnt}}">
                    <div id="cust${prior_cnt}_error" class="text-danger pt-2 pl-2 error-msg"></div>
                </div>
                <div class="col-md-6 p-0 m-0">
                    <select class="form-control form-control-sm col-md-12"  name="cust_rel[]" id="cust_rel${prior_cnt}">
                    <option value=''>선택</option>
                    {{ Func::printOption($arrayRslt['config']['relation_cd']) }}
                    </select>
                    <div id="cust_rel${prior_cnt}_error" class="text-danger pt-2 pl-2 error-msg"></div>
                </div>
            </div>
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <input type="text" class="form-control form-control-sm col-md-10 comma" onkeyup="onlyNumber(this);" name="set_money[]" id="set_money${prior_cnt}">
                <div id="set_money${prior_cnt}_error" class="text-danger pt-2 pl-2 error-msg"></div>
            </div>
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <input type="text" class="form-control form-control-sm col-md-10 comma" onkeyup="onlyNumber(this);" name="loan_origin[]" id="loan_origin${prior_cnt}">
                <div id="loan_origin${prior_cnt}_error" class="text-danger pt-2 pl-2 error-msg"></div>
            </div>
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <select class="form-control form-control-sm col-md-10"  name="repay_yn[]" id="repay_yn${prior_cnt}">
                <option value=''>선택</option>
                {{ Func::printOption($arrayRslt['vars']['yn']) }}
                </select>
                <div id="repay_yn${prior_cnt}_error" class="text-danger pt-2 pl-2 error-msg"></div>
            </div>
        </td>
        <td align="center">
        </td>
    </tr>
</script>
<script id="distraint_tmpl" type="text/tmpl">
    <tr>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <input type="text" class="form-control form-control-sm col-md-10 comma"onkeyup="onlyNumber(this);" name="dis_config_money[]"  id="dis_config_money${distraint_cnt}">
                <div id="dis_origin_money${distraint_cnt}_error" class="text-danger pt-2 pl-2 error-msg"></div>
            </div>
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <input type="text" class="form-control form-control-sm col-md-10 comma" onkeyup="onlyNumber(this);" name="dis_origin_money[]" id="dis_origin_money${distraint_cnt}" > 
                <div id="dis_origin_money${distraint_cnt}_error" class="text-danger pt-2 pl-2 error-msg"></div>
            </div>
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <select class="form-control form-control-sm col-md-10"  name="dis_repay_yn[]" id="dis_repay_yn${distraint_cnt}">
                <option value=''>선택</option>
                {{ Func::printOption($arrayRslt['vars']['yn']) }}
                </select>
                <div id="dis_repay_yn${distraint_cnt}_error" class="text-danger pt-2 pl-2 error-msg"></div>
            </div>
        </td>
        <td align="center">
            <div class="col-md-12 p-0 m-0">
                <input type="textarea" class="form-control form-control-sm col-md-11" name="etc[]" id="etc${distraint_cnt}" >
                <div id="etc${distraint_cnt}_error" class="text-danger pt-2 pl-2 error-msg"></div>
            </div>
        </td>
            <td align="center">
        </td>
    </tr>
</script>
<script>
getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());

// 준공일은 년도만 출력
$("#model_year").datetimepicker({
    format: 'YYYY',
    locale: 'ko',
    useCurrent: false,
}); 

function modeChk(mode)
{
    var modeArr = [];
    modeArr['INS'] = "등록";
    modeArr['UPD'] = "저장";
    modeArr['DEL'] = "삭제";

    if(mode == 'DEL')
    {
        custCarAction(mode);
        return;
    }
    
    if(!validChk(''))
    {
        return false;
    } 

    {{--
    if(!validChk('priorChk'))
    {
        alert('권리관계 설정 등록 오류입니다.');
        return false;
    } --}}
    
    if(!confirm("정말 "+modeArr[mode]+"하시겠습니까?"))
    {
        return false;
    }
    custCarAction(mode);
}

// 권리순위 count
var prior_cnt = {{ isset($priorList) ? sizeof($priorList) : 0 }};

// 압류내역 count
var distraint_cnt = {{ isset($distraintList) ? sizeof($distraintList) : 0 }};

function custCarAction(mode) {

    // 담보설정금액 세팅
    var config_money =  $("#prior_config_money").val();
    $("#config_money").val(config_money);

    var postdata = $('#car_form').serialize();
    postdata = postdata + "&mode=" + mode + "&priorCnt=" + prior_cnt + "&distraintCnt=" + distraint_cnt;;

    console.log(postdata);

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url  : "/erp/caraction",
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
                    getCustData('car');
                }
                else
                {
                    getCustData('car','',data.no);
                }
            }
            // 실패알림
            else 
            {
                alert(data.rs_msg);
                getCustData('car','',data.no);
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다. 관리자에게 문의해주세요.");
            getCustData('car');
        }
    });
}

//권리순위 설정권자구분 설정권자 채무자 채무자관계 설정금액 대출원금 대환여부
var arrPrior = ['type','setter_type','setter','cust','cust_rel','set_money','loan_origin','repay_yn'];

var arrAlert = {"zip11":"물건소재지","unique_code" : "등기고유번호","jeonyong_m":"전용면적","tenant_yn":"임차인여부","move_in_name":"전입세대열람","move_in_type":"전입세대열람구분","value_high":"kb상한가","value_middle":"kb일반가","value_low":"kb하한가","owner_name":"소유자명","own_rel":"소유관계","own_type":"소유구분","owner_rate":"지분율","dambo_offer_yn":"담보제공여부","type":"권리순위","setter_type":"설정권자구분", "setter":"설정권자","cust":"채무자","cust_rel":"채무자구분","set_money":"채권가액","loan_origin":"대출원금","repay_yn":"대환여부"};

// 기본정보
var arrMust  = {
            "fst_reg_date"  : "최초등록일",
            "car_reg_date"  : "차량이전일",
            "user_nm"       : "운행자",
            "user_rel"      : "운행자와의 관계",
            "car_no"        : "차량번호",
            "car_dae_no"    : "차대번호",
            "model"         : "차명",
        };

function validChk(div)
{

    if( div==='')
    {
        var rs = 1;
        $.each(arrMust, function (key, val) 
            {
                if($("#"+key).val() == '' || $("#"+key).val() == 0)
                {
                    alert(val+"을(를) 입력해주세요.");
                    $("#"+key).focus();
                    rs = 0;
                    return false;
                }
            });  

        if(!rs)
            return false;
    }

    // 권리관계
    for(var i=0; i<prior_cnt;i++)
    {
        for(var ip in arrPrior)
        {
            if($("#"+arrPrior[ip]+i).val() == ''|| $("#"+arrPrior[ip]+i).val() == 0)
            {
                alert(arrAlert[arrPrior[ip]] + "을(를) 입력해주세요.");
                $("#"+arrPrior[ip]+i).focus();
                return false;
            }
        }
    }

    return true;
    
}

// 선순위 추가
function addPrior()
{
    $("#prior_tmpl").template("prior_tmpl");
    $.tmpl("prior_tmpl",prior_cnt).appendTo("#priorTb"); 
    $("#set_money"+prior_cnt).number(true);
    $("#loan_origin"+prior_cnt).number(true);
    prior_cnt = prior_cnt+1;
}
// 압류내역 추가
function addDistraint()
{
    $("#distraint_tmpl").template("distraint_tmpl");
    $.tmpl("distraint_tmpl",distraint_cnt).appendTo("#distraintTb"); 
    $("#dis_config_money"+distraint_cnt).number(true);
    $("#dis_origin_money"+distraint_cnt).number(true);
    distraint_cnt = distraint_cnt+1;
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
    
    if(div=="prior")
    {
        // 기존 권리관계는 취소안됨
        if(prior_cnt == {{ sizeof($priorList) ?? 0 }})
        {
            return false;
        }
        prior_cnt = prior_cnt - 1;
    }
    else if(div=="distraint")
    {
        // 기존 등록된 압류내역은 취소안됨
        if(distraint_cnt == {{ sizeof($distraintList) ?? 0 }})
        {
            return false;
        }
        distraint_cnt = distraint_cnt - 1;
    }
    
    var tb = document.getElementById(div+"Tb");
    tb.lastChild.remove();
}

// LTV 계산 
function calLTV(configMoney)
{
    //차량결정가
    if( !$("#car_decision_money").val() )
    {
        alert("차량결정가를 입력해주세요");
        $("#car_decision_money").focus();
        return false;
    }
    // 권리관계
    if(prior_cnt == 0)
    {
        alert("권리관계를 입력해주세요");
        return false;
    }
    for(var i=0; i<prior_cnt;i++)
    {
        for(var ip in arrPrior)
        {
            if($("#"+arrPrior[ip]+i).val() == ''|| $("#"+arrPrior[ip]+i).val() == 0)
            {
                alert(arrAlert[arrPrior[ip]] + "을(를) 입력해주세요.");
                $("#"+arrPrior[ip]+i).focus();
                return false;
            }
        }
    }
 
    // 선순위 대출원금
    var priorBalanceSum = 0;
    // 선순위 설정금액 => 선순위 채권가액 sum
    var priorConfigMoneySum = 0;

    for(var i=0; i<prior_cnt;i++)
    {
        var type       = $("#type"+i).val();
        var setYN      = $("#set_yn"+i).val();
        var repayYN    = $("#repay_yn"+i).val();

        var setMoney   = $("#set_money"+i).val().replace(/,/gi, "");
        var loanOrigin = $("#loan_origin"+i).val().replace(/,/gi, "");

        // 대환여부 Y 제외
        if(repayYN === 'Y')
        {
            continue;
        }
        // 선순위 대출원금
        priorBalanceSum     = priorBalanceSum + loanOrigin*1;
        // 설정여부 N 제외 - 선순위설정금액만
        if(setYN === 'N')
        {
            continue;
        }
        // 기존에 설정금액이 존재하는 경우 
        if(!configMoney)
        {
            // 선순위 설정금액
            priorConfigMoneySum = priorConfigMoneySum + setMoney*1;
        }
        else
        {
            priorConfigMoneySum = configMoney*1;
        }
    }

    // 선순위 설정금액
    $("#prior_config_money").val(priorConfigMoneySum).number(true);
    // 선순위 대출원금
    $("#prior_balance").val(priorBalanceSum).number(true);
    // 승인금액 
    var apr_money = $("#approve_money").val().replace(/,/gi, "");

    // 최종결정가
    var car_decision_money = $("#car_decision_money").val().replace(/,/gi, "");
    $("#final_value").val(car_decision_money).number(true);

    // LTV 설정 % 산출 - ((선순위설정 + 승인금액) /최종결정가)
    var ltv_rate = ( (priorConfigMoneySum*1 + apr_money*1) / car_decision_money*100 );
    $("#ltv_config_rate").val(ltv_rate).number(true, 2);
    
}
function configMnyChk(obj)
{
    var config_money_sum = obj.value.replace(/,/gi, "");
    calLTV(config_money_sum);
} 
function togetherChk()
{
    var together_yn = $("#together_yn").val();

    if(together_yn == 'Y')
    {
        $("#owner_nm2").attr({
            "readonly": false,
            "disabled": false,
        });
        $("#owner_rel2").attr({
            "disabled": false,
            "readonly": false,
        });
        $("#owner_dambo_yn2").attr({
            "disabled": false,
            "readonly": false,
        });
    }
    else
    {
        $("#owner_nm2").empty().attr({
            "readonly": true,
            "disabled": true,
        });
        $("#owner_rel2").attr({
            "disabled": true,
            "readonly": true,
        });
        $("#owner_dambo_yn2").attr({
            "disabled": true,
            "readonly": true,
        });
    }
}

function setConfigRate(obj)
{
    $("#config_rate").val(obj.value);
}
function setDecisionRate(obj)
{
    if(obj.name == "car_decision_money")
    {   
        var decision_money = obj.value.replace(/,/g, '');
        var release_money = $("#car_release_money").val().replace(/,/g, '') || 0;
    }
    else
    {
        var release_money = obj.value.replace(/,/g, '');
        var decision_money = $("#car_decision_money").val().replace(/,/g, '') || 0;
    }
    // 차량결정가액 %
    var rate = decision_money/release_money*100;

    if(!isFinite(rate))
    {
        rate = 0;
    }

    $("#car_decision_rate").val(rate).number(true,2);
}
$('input[name="config_money_chk"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});
$('input[name="config_money_chk"]').on('ifChecked',function(event){
    $("input[name='prior_config_money']").attr("readonly",false);
});
$('input[name="config_money_chk"]').on('ifUnchecked',function(event){
    $("input[name='prior_config_money']").attr("readonly",true);
    calLTV();
});
</script>
