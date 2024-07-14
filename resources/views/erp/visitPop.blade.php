@extends('layouts.masterPop')
@section('content')

{{-- Action 처리 성공 여부 alert --}}
@if (Session::has('result'))
    <script> 
        alert('{{Session::get("result")}}'); 

        //@if( Session::get("del_yn") == "Y" )
        //    window.close();
        //@endif
    </script>
@endif

<form class="form-horizontal" role="form" name="form" id="form" method="post" action="">
<div class="card card-lightblue m-2">
    @csrf
<input type="hidden" id="mode" name="mode" value="{{ $mode ?? 'INS' }}">
<input type="hidden" id="no" name="no" value="{{ $visit->no ?? '' }}">

    <div class="card-header">
        <h2 class="card-title" > 
            <i class="fa fa-sm fa-house-user mr-2"></i> 방문 요청
        </h2>
    </div>

    <div class="card-body pb-3">
        <div class="row">
            <div class="col-sm-8"  style="">
                <table class="table table-bordered table-input text-xs" id="reqTable" >
                    <colgroup>
                        <col width="15%">
                        <col width="35%">
                        <col width="15%">
                        <col width="10%">
                        <col width="25%">
                    </colgroup>
                    <tbody>
                        <tr>
                            <th class="">고객번호</th>
                            <td><input type="text" class="form-control form-control-sm" name="cust_info_no" value="{{$visit->cust_info_no ?? ''}}" readonly/></td>
                            <th class="">여신원장번호</th>
                            <td colspan=2><input type="text" class="form-control form-control-sm" name="loan_info_no" value="{{$visit->loan_info_no ?? ''}}" readonly/></td>
                        </tr>
                        <tr>
                            <th class="">방문희망일시</th>
                            <td>
                                <div class="row">
                                    <div class="input-group date datetimepicker col-md-8">
                                        <input type="text" class="form-control form-control-sm text-right datetimepicker-input dateformat datetimepicker readonlys" name="visit_req_date" id="visit_req_date"
                                            placeholder="방문요청일" autocomplete="off" value="{{$visit->visit_req_date ?? '' }}">
                                        <div class="input-group-append" data-target="#visit_req_date" data-toggle="datetimepicker">
                                            <div class="input-group-text ml-1"><i class="fa fa-calendar" style="font-size: 0.8rem;"></i></div>
                                        </div>
                                    </div>
                                    <div class="input-group col-md-4">
                                        <input type="text" class="form-control form-control-sm text-right hourformat readonlys" name="visit_req_hour" id="visit_req_hour"
                                            placeholder="시간" autocomplete="off" value="{{$visit->visit_req_hour ?? '' }}">
                                        <label for="" class="mt-1 ml-2 mr-1"> 시</label>
                                    </div>
                                </div>
                            </td>
                            <th class="">처리등급</th>
                            <td class="">
                                <div class="custom-control custom-radio m-0">
                                    <input class="custom-control-input custom-control-input-danger" type="radio" id="important_degree1" name="important_degree" value="A">
                                    <label for="important_degree1" id="label_important_degree1" class="custom-control-label"></label>
                                    <label for="important_degree1" class="mt-1">긴급</label>
                                </div>
                            </td>
                            <td>
                                <div class="custom-control custom-radio m-0">
                                    <input class="custom-control-input custom-control-input-primary" type="radio" id="important_degree2" name="important_degree" value="B">
                                    <label for="important_degree2" id="label_important_degree2" class="custom-control-label"></label>
                                    <label for="important_degree2" class="mt-1">보통</label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th class="">주소 ①</th>
                            <td colspan="10">
                                <div class="row">
                                    <div class="input-group col-sm-4 pb-1">
                                        <input type="text" class="form-control" name="visit_zip1" id="visit_zip1" numberonly="true" value="{{$visit->visit_zip1 ?? ''}}" autocomplete="off" >
                                        <span class="input-group-btn">
                                            <button class="btn btn-default btn-sm ml-1" type="button" onclick="blankCheck(1) !== false ? DaumPost('visit_zip1', 'visit_addr11', 'visit_addr12', $('#visit_addr11').val()) : ''">검색</button>
                                        </span>
                                    </div>
                                    <div class="custom-control custom-radio m-0">
                                        <input class="custom-control-input custom-control-input-navy" type="radio" id="select_addr_a" name="visit_addr1_div" value="1">
                                        <label for="select_addr_a" id="label_addr_a" class="custom-control-label"></label>
                                        <label for="select_addr_a" class="mt-1" style="margin-left: -0.5rem;">실거주</label>
                                    </div>
                                    <div class="custom-control custom-radio m-0">
                                        <input class="custom-control-input custom-control-input-navy" type="radio" id="select_addr_b" name="visit_addr1_div" value="2">
                                        <label for="select_addr_b" id="label_addr_b" class="custom-control-label ml-1"></label>
                                        <label for="select_addr_b" class="mt-1" style="margin-left: -0.5rem;">등본</label>
                                    </div>
                                    <div class="custom-control custom-radio m-0">
                                        <input class="custom-control-input custom-control-input-navy" type="radio" id="select_addr_c" name="visit_addr1_div" value="3">
                                        <label for="select_addr_c" id="label_addr_c" class="custom-control-label ml-1"></label>
                                        <label for="select_addr_c" class="mt-1" style="margin-left: -0.5rem;">직장</label>
                                    </div>
                                    <div class="custom-control custom-radio m-0">    
                                        <input class="custom-control-input custom-control-input-navy" type="radio" id="select_addr_d" name="visit_addr1_div" value="9" checked>
                                        <label for="select_addr_d" id="label_addr_d" class="custom-control-label ml-1"></label>
                                        <label for="select_addr_d" class="mt-1" style="margin-left: -0.5rem;">직접입력</label>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <input type="text" class="form-control mb-1 col-md-12 mr-1" name="visit_addr11" id="visit_addr11" value="{{$visit->visit_addr11 ?? ''}}" autocomplete="off" >
                                    <input type="text" class="form-control col-md-12 ml-1" name="visit_addr12" id="visit_addr12" value="{{$visit->visit_addr12 ?? ''}}" maxlength="100" autocomplete="off" >
                                </div>
                                <div class="input-group">
                                    <input type="text" class="form-control mb-1 col-md-6" name="old_visit_addr11" id="old_visit_addr11" value="{{$visit->old_visit_addr11 ?? ''}}" autocomplete="off" title="지번주소" placeholder="지번주소" readonly>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th class="">주소 ②</th>
                            <td colspan="10">
                                <div class="row">
                                    <div class="input-group col-sm-4 pb-1">
                                        <input type="text" class="form-control" name="visit_zip2" id="visit_zip2" numberonly="true" value="{{$visit->visit_zip2 ?? ''}}" autocomplete="off" >
                                        <span class="input-group-btn">
                                            <button class="btn btn-default btn-sm ml-1" type="button" onclick="blankCheck(2) !== false ? DaumPost('visit_zip2', 'visit_addr21', 'visit_addr22', $('#visit_addr21').val()) : ''">검색</button>
                                        </span>
                                    </div>
                                    <div class="custom-control custom-radio m-0">
                                        <input class="custom-control-input custom-control-input-navy" type="radio" id="select_addr2_a" name="visit_addr2_div" value="1">
                                        <label for="select_addr2_a" id="label_addr2_a" class="custom-control-label"></label>
                                        <label for="select_addr2_a" class="mt-1" style="margin-left: -0.5rem;">실거주</label>
                                    </div>
                                    <div class="custom-control custom-radio m-0">
                                        <input class="custom-control-input custom-control-input-navy" type="radio" id="select_addr2_b" name="visit_addr2_div" value="2">
                                        <label for="select_addr2_b" id="label_addr2_b" class="custom-control-label ml-1"></label>
                                        <label for="select_addr2_b" class="mt-1" style="margin-left: -0.5rem;">등본</label>
                                    </div>
                                    <div class="custom-control custom-radio m-0">
                                        <input class="custom-control-input custom-control-input-navy" type="radio" id="select_addr3_c" name="visit_addr2_div" value="3">
                                        <label for="select_addr3_c" id="label_addr2_c" class="custom-control-label ml-1"></label>
                                        <label for="select_addr3_c" class="mt-1" style="margin-left: -0.5rem;">직장</label>
                                    </div>
                                    <div class="custom-control custom-radio m-0">    
                                        <input class="custom-control-input custom-control-input-navy" type="radio" id="select_addr4_d" name="visit_addr2_div" value="9" checked>
                                        <label for="select_addr4_d" id="label_addr2_d" class="custom-control-label ml-1"></label>
                                        <label for="select_addr4_d" class="mt-1" style="margin-left: -0.5rem;">직접입력</label>
                                    </div>
                                </div>
                                <div class="input-group">
                                    <input type="text" class="form-control mb-1 col-md-12 mr-1" name="visit_addr21" id="visit_addr21" value="{{$visit->visit_addr21 ?? ''}}" autocomplete="off" >
                                    <input type="text" class="form-control col-md-12 ml-1" name="visit_addr22" id="visit_addr22" value="{{$visit->visit_addr22 ?? ''}}" maxlength="100" autocomplete="off" >
                                </div>
                                <div class="input-group">
                                    <input type="text" class="form-control mb-1 col-md-6" name="old_visit_addr21" id="old_visit_addr21" value="{{$visit->old_visit_addr21 ?? ''}}" autocomplete="off" title="지번주소" placeholder="지번주소" readonly>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-sm-4"  style="">
                <table class="table table-sm loan-info-table card-secondary">
                    <thead>

                        <tr>
                            <th class="">방문요청메모</th>
                        </tr>

                    </thead>
                    <tbody>
                        <tr>
                            <td class="p-2">
                                <textarea class="form-control" rows="7" name="visit_req_memo" placeholder="" style="resize: none;">{{$visit->visit_req_memo ?? ''}}</textarea>
                                <textarea class="form-control" rows="7" name="before_visit_req_memo" hidden>{{$visit->visit_req_memo ?? ''}}</textarea>
                            </td>
                        </tr>
                        <tr><td colspan="10"></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if( isset($mode) && $mode == "INS" )
    <div class="card-footer row">
        <div class="col-md-11">
        </div>
        <div class="btn-group col-md-1">
            <button type="button" class="btn bg-lightblue btn-sm float-right ml-1 mr-1" onclick="sendit('{{$mode ?? ''}}');">등록</button>
        </div>
    </div>
    @endif
</div>




@if( isset($mode) && $mode == "UPD" )
<div class="card card-lightblue m-2" id="visitResDiv">

    <div class="card-header">
        <h2 class="card-title" > 
            <i class="fa fa-sm fa-house-user mr-2"></i> 방문 실행 정보
        </h2>
    </div>

    <div class="card-body pb-3">
        <div class="row">
            <div class="col-sm-8"  style="">
                <table class="table table-bordered table-input text-xs" id="" >
                    <colgroup>
                        <col width="13%">
                        <col width="22%">
                        <col width="13%">
                        <col width="22%">
                        <col width="13%">
                        <col width="17%">
                    </colgroup>
                    <tbody>
                        <tr>
                            <th class="">방문담당①</th>
                            <td >
                                <div class="input-group">
                                <select class="form-control form-control-sm selectpicker" data-size="10" data-live-search="true" name="visit_manager1" id="visit_manager1" title="선택">
                                {{ Func::printOption($arr_manager, $visit->visit_manager1 ?? '') }}   
                                </select>
                                </div>
                            </td>
                            <th class="">방문담당②</th>
                            <td >
                                <div class="input-group">
                                <select class="form-control form-control-sm selectpicker" data-size="10" data-live-search="true" name="visit_manager2" id="visit_manager2" title="선택">
                                {{ Func::printOption($arr_manager, $visit->visit_manager2 ?? '') }}   
                                </select>
                                </div>
                            </td>
                            <th class="">방문수금액</th>
                            <td colspan=2>
                                <div class="input-group">
                                    <input type="text" name="visit_take_money" class="form-control form-control-border border-width-1 form-control-sm text-right moneyformat"
                                        placeholder="수금액" value="{{$visit->visit_take_money ?? ''}}" />
                                    <label class="mt-1 mr-1">원</label>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th class="">방문일시</th>
                            <td colspan="3">
                                <div class="row">
                                    <div class="input-group date datetimepicker col-md-4">
                                        <input type="text" class="form-control form-control-sm text-right datetimepicker-input dateformat datetimepicker" name="visit_res_date" id="visit_res_date"
                                            placeholder="방문일" autocomplete="off" value="{{$visit->visit_res_date ?? '' }}">
                                        <div class="input-group-append" data-target="#visit_res_date" data-toggle="datetimepicker">
                                            <div class="input-group-text ml-1"><i class="fa fa-calendar" style="font-size: 0.8rem;"></i></div>
                                        </div>
                                    </div>
                                    <div class="input-group col-md-8">
                                        <input type="text" class="form-control form-control-sm text-right hourformat" name="visit_res_shour" id="visit_res_shour"
                                            placeholder="시" autocomplete="off" value="{{$visit->visit_res_shour ?? '' }}">
                                        <label for="" class="mt-1 ml-2 mr-1"> 시</label>
                                        <input type="text" class="form-control form-control-sm text-right hourformat" name="visit_res_smin" id="visit_res_smin"
                                            placeholder="분" autocomplete="off" value="{{$visit->visit_res_smin ?? '' }}">
                                        <label for="" class="mt-1 ml-2 mr-1"> 분</label>

                                        <label for="" class="mt-1 ml-2 mr-1">~</label>

                                        <input type="text" class="form-control form-control-sm text-right hourformat" name="visit_res_ehour" id="visit_res_ehour"
                                            placeholder="시" autocomplete="off" value="{{$visit->visit_res_ehour ?? '' }}">
                                        <label for="" class="mt-1 ml-2 mr-1"> 시</label>
                                        <input type="text" class="form-control form-control-sm text-right hourformat" name="visit_res_emin" id="visit_res_emin"
                                            placeholder="분" autocomplete="off" value="{{$visit->visit_res_emin ?? '' }}">
                                        <label for="" class="mt-1 ml-2 mr-1"> 분</label>
                                    </div>
                                </div>
                            </td>
                            <th class="">방문방법</th>
                            <td class="">
                                <div class="row pl-2">
                                    <div class="custom-control custom-radio m-0">
                                        <input class="custom-control-input custom-control-input-navy" type="radio" id="visit_method1" name="visit_method" value="A">
                                        <label for="visit_method1" class="custom-control-label"></label>
                                        <label for="visit_method1" class="mt-1"  style="margin-left: -0.5rem;">도보</label>
                                    </div>
                                    <div class="custom-control custom-radio m-0">
                                        <input class="custom-control-input custom-control-input-navy" type="radio" id="visit_method2" name="visit_method" value="B">
                                        <label for="visit_method2" class="custom-control-label ml-1"></label>
                                        <label for="visit_method2" class="mt-1" style="margin-left: -0.5rem;">차량</label>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <th>입금약속</th>
                            <td colspan=3>
                                <div class="row">
                                    <div class="input-group date datetimepicker col-md-4">
                                        <input type="hidden" class="" name="before_visit_prms_date" value="{{$visit->visit_prms_date ?? '' }}" />
                                        <input type="text" class="form-control form-control-sm text-right datetimepicker-input dateformat datetimepicker" name="visit_prms_date" id="visit_prms_date"
                                            placeholder="약속일" autocomplete="off" value="{{$visit->visit_prms_date ?? '' }}">
                                        <div class="input-group-append" data-target="#visit_prms_date" data-toggle="datetimepicker">
                                            <div class="input-group-text ml-1"><i class="fa fa-calendar" style="font-size: 0.8rem;"></i></div>
                                        </div>
                                    </div>
                                    <div class="input-group col-md-4">
                                        <input type="hidden" name="before_visit_prms_hour" value="{{$visit->visit_prms_hour ?? '' }}" />
                                        <input type="text" class="form-control form-control-sm text-right hourformat" name="visit_prms_hour" id="visit_prms_hour"
                                            placeholder="시" autocomplete="off" value="{{$visit->visit_prms_hour ?? '' }}">
                                        <label for="" class="mt-1 ml-2 mr-1"> 시</label>
                                        <input type="hidden" name="before_visit_prms_min" value="{{$visit->visit_prms_min ?? '' }}" />
                                        <input type="text" class="form-control form-control-sm text-right hourformat" name="visit_prms_min" id="visit_prms_min"
                                            placeholder="분" autocomplete="off" value="{{$visit->visit_prms_min ?? '' }}">
                                        <label for="" class="mt-1 ml-2 mr-1"> 분</label>
                                    </div>
                                    <div class="input-group col-md-4">
                                        <input type="hidden" name="before_visit_prms_money" value="{{$visit->visit_prms_money ?? ''}}" />
                                        <input type="text" name="visit_prms_money" class="form-control form-control-border border-width-1 form-control-sm text-right moneyformat"
                                            placeholder="약속금액" value="{{$visit->visit_prms_money ?? ''}}" />
                                        <label class="mt-1 mr-1">원</label>
                                    </div>
                                </div>
                            </td>
                            <th>접촉대상</th>
                            <td>
                                <select class="form-control form-control-sm selectpicker" data-size="10" data-live-search="true" name="contact_rel_cd" id="contact_rel_cd" title="선택">
                                <option value='' @if(empty($visit->contact_rel_cd)) selected @endif>본인</option>
                                {{ Func::printOption($configArr['relation_cd'], $visit->contact_rel_cd ?? '') }}   
                                </select>
                            </td>
                        </tr>
                        <tr>
                            <th>방문비용</th>
                            <td colspan=10>
                                <div class="input-group col-md-12 p-0">
                                    <label class="mt-1 mr-1 ml-1">주유비 : </label>
                                    <input type="text" name="cost_money1" class="form-control form-control-border border-width-1 form-control-sm text-right moneyformat"
                                        placeholder="주유비" value="{{$visit->cost_money1 ?? ''}}" />
                                    <label class="mt-1 mr-1">원,</label>

                                    <label class="mt-1 mr-1 ml-1">통행료 : </label>
                                    <input type="text" name="cost_money2" class="form-control form-control-border border-width-1 form-control-sm text-right moneyformat"
                                        placeholder="통행료" value="{{$visit->cost_money2 ?? ''}}" />
                                    <label class="mt-1 mr-1">원,</label>

                                    <label class="mt-1 mr-1 ml-1">숙박비 : </label>
                                    <input type="text" name="cost_money3" class="form-control form-control-border border-width-1 form-control-sm text-right moneyformat"
                                        placeholder="숙박비" value="{{$visit->cost_money3 ?? ''}}" />
                                    <label class="mt-1 mr-1">원,</label>
                                </div>
                                <div class="input-group col-md-8 p-0">
                                    <label class="mt-1 mr-1 ml-1">식비 : </label>
                                    <input type="text" name="cost_money4" class="form-control form-control-border border-width-1 form-control-sm text-right moneyformat"
                                        placeholder="식비" value="{{$visit->cost_money4 ?? ''}}" />
                                    <label class="mt-1 mr-1">원,</label>

                                    <label class="mt-1 mr-1 ml-1">기타 : </label>
                                    <input type="text" name="cost_money5" class="form-control form-control-border border-width-1 form-control-sm text-right moneyformat"
                                        placeholder="기타" value="{{$visit->cost_money5 ?? ''}}" />
                                    <label class="mt-1 mr-1">원</label>
                                </div>
                            </td>
                        </tr>
                        {{--<tr>
                            <th>확인사항</th>
                            <td colspan=10>
                                <select class="form-control form-control-sm selectpicker" data-size="10" data-live-search="true" name="visit_check_cd" id="visit_check_cd" title="(방문) 사용자코드1">
                                    {{ Func::printOption($configArr['visit_check_cd'], $visit->visit_check_cd ?? '') }}   
                                </select>
                                <select class="form-control form-control-sm selectpicker" data-size="10" data-live-search="true" name="visit_check_cd2" id="visit_check_cd2" title="(방문) 사용자코드2">
                                    {{ Func::printOption($configArr['visit_check_cd2'], $visit->visit_check_cd2 ?? '') }}   
                                </select>
                            </td>
                        </tr>--}}
                    </tbody>
                </table>
            </div>
            <div class="col-sm-4"  style="">
                <table class="table table-sm loan-info-table card-secondary">
                    <thead>

                        <tr>
                            <th class="">
                                방문결과메모
                            </th>
                        </tr>

                    </thead>
                    <tbody>
                        <tr>
                            <td>
                                <div class="input-group btn-group">
                                    <div class="custom-control custom-checkbox col-md-3">
                                        <input class="custom-control-input chkbox" type="checkbox" id="rs_memo_important_check" name="rs_memo_important_check" value="Y" 
                                                @if( !empty($visit->rs_memo_important_check) && $visit->rs_memo_important_check == "Y") checked @endif>
                                        <label for="rs_memo_important_check" class="custom-control-label mr-0"></label>
                                        <label for="rs_memo_important_check" class="mt-1 ml-0 p-0">중요메모</label>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <label class="mt-1 mr-1">색상 :</label>
                                        <input type="hidden" name="rs_memo_color" value="{{$visit->rs_memo_color ?? 'black'}}"/>
                                        <button type="button" class="btn btn-default btn-xs dropdown-toggle dropdown-icon" data-toggle="dropdown" aria-expanded="false" id="color_area" style="color:white; background-color: {{$visit->rs_memo_color ?? 'black'}};">
                                            <span class="sr-only">Toggle Dropdown</span>
                                        </button>
                                        <div class="dropdown-menu" role="menu">
                                            <a class="dropdown-item" style="background-color: red; color: white;" onclick="colorChange(this);">red</a>
                                            <a class="dropdown-item" style="background-color: orange; color: white;" onclick="colorChange(this);">orange</a>
                                            <a class="dropdown-item" style="background-color: green; color: white;" onclick="colorChange(this);">green</a>
                                            <a class="dropdown-item" style="background-color: blue; color: white;" onclick="colorChange(this);">blue</a>
                                            <a class="dropdown-item" style="background-color: blueviolet; color: white;" onclick="colorChange(this);">blueviolet</a>
                                            <a class="dropdown-item" style="background-color: fuchsia; color: white;" onclick="colorChange(this);">fuchsia</a>
                                            <a class="dropdown-item" style="background-color: black; color: white;" onclick="colorChange(this);">black</a>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        <tr>
                            <td class="p-2">
                                <textarea class="form-control" rows="5" name="rs_memo" id="rs_memo" placeholder="" style="resize: none; color: {{$visit->rs_memo_color ?? 'black'}};">{{$visit->rs_memo ?? ''}}</textarea>
                                <textarea class="form-control" rows="5" name="before_rs_memo" hidden>{{$visit->rs_memo ?? ''}}</textarea>
                            </td>
                        </tr>
                        <tr><td colspan="10"></td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if( isset($mode) && $mode == "UPD" )
    <div class="card-footer row">
        <div class="col-md-6">

        </div>
        <div class="col-md-5">
            <div class="input-group col-md-6 float-right">
                <input type="hidden" name="before_status" value="{{$visit->status ?? ''}}"/>
                <label class="mt-1 mr-1">방문상태 :</label>
                <select class="form-control form-control-sm" name="status" id="status">
                {{ Func::printOption($arr_visit_status, $visit->status ?? '') }}
                </select>
            </div>
        </div>
        <div class="btn-group col-md-1">
            <button type="button" class="btn bg-lightblue btn-sm float-right ml-1 mr-1" onclick="sendit('{{$mode ?? ''}}');">수정</button>
        <!-- <button type="button" class="btn bg-lightblue btn-sm float-right ml-1 mr-1" onclick="sendit('DEL');">삭제</button> -->
        </div>
    </div>
    @endif
</div>
@endif

</form>

<div class="card card-lightblue m-2">

    <div class="card-header">
        <h2 class="card-title" > 
            <i class="fas fa-clipboard-list mr-1"></i> 방문 로그
        </h2>
    </div>

    <div class="card-body" id="logDiv" style="overflow-y:scroll; max-height:200px;">

    </div>




</div>
@endsection




@section('javascript')
<script>
    window.resizeTo(1216 ,window.screen.availHeight);

    $(".datetimepicker").datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
        useCurrent: false,
    });

    $(function () {
        logs("{{$visit->cust_info_no ?? ''}}", "{{$visit->loan_info_no ?? ''}}");

        setInputMask('class', 'dateformat', 'date');
        setInputMask('class', 'moneyformat', 'money');
        setInputMask('class', 'ratioformat', 'ratio');
        $('.hourformat').inputmask('99');

        @if( isset($visit->visit_addr1_div) )
            @if( !empty($visit->visit_addr1_div) && $visit->visit_addr1_div != '9' )
            $("input[name='visit_addr1_div'][value='{{$visit->visit_addr1_div}}']").siblings('label').click();
            //@else
            //$("input[name='visit_addr1_div'][value='9']").siblings('label').click();
            @endif
        //@else
        //$("input[name='visit_addr1_div'][value='9']").siblings('label').click();
        @endif

        @if( isset($visit->visit_addr2_div) )
            @if( !empty($visit->visit_addr2_div) && $visit->visit_addr2_div != '9' )
            $("input[name='visit_addr2_div'][value='{{$visit->visit_addr2_div}}']").siblings('label').click();
            //@else
            //$("input[name='visit_addr2_div'][value='9']").siblings('label').click();
            @endif
        //@else
        //$("input[name='visit_addr2_div'][value='9']").siblings('label').click();
        @endif

        @if( isset($visit->important_degree) )
            @if( !empty($visit->important_degree) )
            $("input[name='important_degree'][value='{{$visit->important_degree}}']").siblings('label').click();
            @else
            $("input[name='important_degree'][value='B']").siblings('label').click();
            @endif
        @else
        $("input[name='important_degree'][value='B']").siblings('label').click();
        @endif

        @if( isset($visit->visit_method) )
            @if( !empty($visit->visit_method) )
            $("input[name='visit_method'][value='{{$visit->visit_method}}']").siblings('label').click();
            @else
            $("input[name='visit_method'][value='B']").siblings('label').click();
            @endif
        @else
        $("input[name='visit_method'][value='B']").siblings('label').click();
        @endif

        @if($mode=="UPD")
            $(".readonlys").prop('readonly', true);
        @endif


    });

    function blankCheck(num)
    {
        if( $('input[name="visit_addr'+num+'_div"][value="9"]').prop('checked') === false  )
        {
            return false;
        }
    }

    function colorChange(v)
    {
        var color = v.innerHTML;

        form.rs_memo_color.value = color;

        $('#color_area').css('background-color', color);
        $('#rs_memo').css('color', color);
    }


    $("input[name='visit_addr1_div']").on('change', function(){
        
        if( $(this).val() == "1" )
        {
            $('#visit_zip1').val('{{$visit->zip1 ?? ''}}');
            $('#visit_addr11').val('{{$visit->addr11 ?? ''}}');
            $('#old_visit_addr11').val('{{$visit->old_addr11 ?? ''}}');
            $('#visit_addr12').val('{{$visit->addr12 ?? ''}}');
            $('#visit_zip1').attr('readonly', true);
            $('#visit_addr11').attr('readonly', true);
            $('#visit_addr12').attr('readonly', true);
        }
        else if( $(this).val() == "2" )
        {
            $('#visit_zip1').val('{{$visit->zip2 ?? ''}}');
            $('#visit_addr11').val('{{$visit->addr21 ?? ''}}');
            $('#old_visit_addr11').val('{{$visit->old_addr21 ?? ''}}');
            $('#visit_addr12').val('{{$visit->addr22 ?? ''}}');
            $('#visit_zip1').attr('readonly', true);
            $('#visit_addr11').attr('readonly', true);
            $('#visit_addr12').attr('readonly', true);
        }
        else if( $(this).val() == "3" )
        {
            $('#visit_zip1').val('{{$visit->zip3 ?? ''}}');
            $('#visit_addr11').val('{{$visit->addr31 ?? ''}}');
            $('#old_visit_addr11').val('{{$visit->old_addr31 ?? ''}}');
            $('#visit_addr12').val('{{$visit->addr32 ?? ''}}');
            $('#visit_zip1').attr('readonly', true);
            $('#visit_addr11').attr('readonly', true);
            $('#visit_addr12').attr('readonly', true);
        }
        else
        {
            $('#visit_zip1').val('');
            $('#visit_addr11').val('');
            $('#old_visit_addr11').val('');
            $('#visit_addr12').val('');
            $('#visit_addr12').attr('readonly', false);
        }
    });

    $("input[name='visit_addr2_div']").on('change', function(){
        
        if( $(this).val() == "1" )
        {
            $('#visit_zip2').val('{{$visit->zip1 ?? ''}}');
            $('#visit_addr21').val('{{$visit->addr11 ?? ''}}');
            $('#old_visit_addr21').val('{{$visit->old_addr11 ?? ''}}');
            $('#visit_addr22').val('{{$visit->addr12 ?? ''}}');
            $('#visit_zip2').attr('readonly', true);
            $('#visit_addr21').attr('readonly', true);
            $('#visit_addr22').attr('readonly', true);
        }
        else if( $(this).val() == "2" )
        {
            $('#visit_zip2').val('{{$visit->zip2 ?? ''}}');
            $('#visit_addr21').val('{{$visit->addr21 ?? ''}}');
            $('#old_visit_addr21').val('{{$visit->old_addr21 ?? ''}}');
            $('#visit_addr22').val('{{$visit->addr22 ?? ''}}');
            $('#visit_zip2').attr('readonly', true);
            $('#visit_addr21').attr('readonly', true);
            $('#visit_addr22').attr('readonly', true);
        }
        else if( $(this).val() == "3" )
        {
            $('#visit_zip2').val('{{$visit->zip3 ?? ''}}');
            $('#visit_addr21').val('{{$visit->addr31 ?? ''}}');
            $('#old_visit_addr21').val('{{$visit->old_addr31 ?? ''}}');
            $('#visit_addr22').val('{{$visit->addr32 ?? ''}}');
            $('#visit_zip2').attr('readonly', true);
            $('#visit_addr21').attr('readonly', true);
            $('#visit_addr22').attr('readonly', true);
        }
        else
        {
            $('#visit_zip2').val('');
            $('#visit_addr21').val('');
            $('#old_visit_addr21').val('');
            $('#visit_addr22').val('');
            $('#visit_addr22').attr('readonly', false);
        }
    });

    function logs(cust_info_no, loan_info_no)
    {
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $('#logDiv').html(loadingString);

        $.ajax({
            url  : "/erp/visitLogs",
            type : "get",
            data : { cust_info_no : cust_info_no, loan_info_no : loan_info_no },
            success : function(result) {
                $('#logDiv').html(result);
            },
            error : function(xhr) {
                $('#logDiv').html('방문 로그를 가져오지 못했습니다.');
                //  alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }

    function sendit(mode)
    {
        if( !confirm("등록 하시겠습니까?") )
        {
            return false;
        }

        if(mode)
        {
            form.mode.value = mode;
        }

        if(ccCheck()) return;

        form.action = "/erp/visitrequestaction";
        form.submit();
    }

</script>
@endsection