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
    .doc_info_table th {
        padding: 4px;
        text-align: center;
    }
    .doc_info_table td {
        padding: 4px;
        font-size: 0.8rem;
    }
    </style>
<form  name="doc_form" id="doc_form" method="post">
    <div class="content-wrapper needs-validation m-0">
        @csrf

        <input type="hidden" id="action_mode" name="action_mode" value="">
        <input type="hidden" name="cust_info_no" value="{{ $cust_info_no }}">
        <input type="hidden" name="loan_info_no" value="{{ $loan_info_no }}">

        <div class="col-12">
            <section class="content-header pl-3 pb-1">
            <h6 class="font-weight-bold text-sm"><i class="fas fa-user mr-2"></i> 계약번호 {{ $v->no }} / {{ $v->name }} 고객 서류관리 현황</h6>
            </section>
        </div>


        <div class="col-md-12">
            <div class="card card-outline card-secondary">
                <div class="card-header p-1">
                    <h3 class="card-title font-weight-bold text-sm"><i class="fas fa-edit m-2"></i>계약 기본정보</h3>
                </div>
                <div class="card-body p-1">
                    <table class="table table-sm table-bordered table-input text-xs doc_info_table">
                        <colgroup>
                        <col width="10%"/>
                        <col width="15%"/>
                        <col width="10%"/>
                        <col width="15%"/>
                        <col width="10%"/>
                        <col width="15%"/>
                        <col width="10%"/>
                        <col width="15%"/>
                        </colgroup>
                        <tbody>
                        <tr>
                            <th>고객번호</th>
                            <td class="text-center">{{ $cust_info_no }}</td>
                            <th>계약번호</th>
                            <td class="text-center">{{ $loan_info_no }}</td>
                            <th>이름</th>
                            <td class="text-center">{{ $v->name }} ( {{ substr($v->ssn,0,6)."-".substr($v->ssn,6,1)."XXXXXX" }} )</td>
                            <th>계약일자</th>
                            <td class="text-center">{{ Func::dateFormat($v->loan_date) }}</td>
                        </tr>
                        <tr>
                            <th>상품</th>
                            <td class="text-center">{{ $array_product[$v->pro_cd] }}</td>
                            <th>상환방법</th>
                            <td class="text-center">{{ $array_config['return_method_cd'][$v->return_method_cd] }}</td>
                            <th>약정일</th>
                            <td class="text-center">매월 {{ $v->contract_day }}일</td>
                            <th>만기일</th>
                            <td class="text-center">{{ Func::dateFormat($v->contract_end_date) }}</td>
                        </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>


        <div class="col-md-12">
            <div class="card card-outline card-secondary">
                <div class="card-header p-1">
                    <h3 class="card-title font-weight-bold text-sm">
                        <i class="fas fa-signature m-2"></i>서류관리 현황
                        @if( $v->doc_status_cd=="Y" )
                        <font class="text-success ml-1">(징구완료)</font>
                        @elseif( $v->doc_status_cd=="A" )
                        <font class="text-warning ml-1">(일부징구)</font>
                        @elseif( $v->doc_status_cd=="N" )
                        <font class="text-danger ml-1">(미징구)</font>
                        @endif
                    </h3>
                </div>
                <div class="card-body p-0 m-0" style="height:600px;">
                    <div class="table-responsive m-0 p-0" style="height:580px;">
                    <table class="table table-sm table-hover table-condensed text-xs doc_info_table table-head-fixed text-nowrap">

                        <colgroup>
                        <col width="3%"/>
                        <col width="20%"/>
                        <col width="3%"/>
                        <col width="10%"/>
                        <col width="10%"/>
                        <col width="10%"/>
                        <col width="3%"/>
                        <col width="3%"/>
                        <col />
                        </colgroup>

                        <thead>
                        <tr>
                        <th class="text-center">NO</th>
                        <th class="text-center">서류</th>
                        <th class="text-center">필수</th>
                        <th class="text-center">발송방법</th>
                        <th class="text-center">발송일</th>
                        <th class="text-center">도착일</th>
                        <th class="text-center">스캔</th>
                        <th class="text-center">보관</th>
                        <th class="text-center">메모</th>
                        </tr>
                        </thead>

                        <tbody>

                        <!-- 필수인애들 -->
                        @php
                        $i = 1;
                        @endphp
                        @foreach( $array_nec_doc as $n => $doc_cd )
                            @php
                            $doc_nm = $array_config['app_document'][$doc_cd];
                            if( isset($array_doc[$doc_cd]) )
                            {
                                $dinfo = $array_doc[$doc_cd];
                                $dinfo['necessary_chk'] = "Y";
                            }
                            else
                            {
                                $dinfo = [];
                                $dinfo['necessary_chk'] = "Y";
                                $dinfo['send_type_cd']  = "";
                                $dinfo['send_date']     = "";
                                $dinfo['arrival_date']  = "";
                                $dinfo['scan_chk']      = "N";
                                $dinfo['keep_chk']      = "N";
                                $dinfo['memo']          = "";
                            }
                            unset($array_config['app_document'][$doc_cd]);
                            @endphp
                            <tr>
                            <td class="text-center">{{ $i++ }}</td>
                            <td class="text-center">
                                {{ $doc_nm }}
                            </td>
                            <td class="text-center"><input type="checkbox" class="icheckbox_square-blue-sm" name="necessary_chk_{{ $doc_cd }}" id="necessary_chk_{{ $doc_cd }}" value="Y" {{ Func::echoChecked('Y', $dinfo['necessary_chk']) }}></td>
                            <td class="text-center">
                                <select class="form-control form-control-xs" name="send_type_cd_{{ $doc_cd }}" id="send_type_cd_{{ $doc_cd }}" >
                                <option value=''>선택</option>
                                {{ Func::printOption($array_config['send_type_cd'], $dinfo['send_type_cd']) }}   
                                </select>
                            </td>
                            <td class="text-center">
                                <div class="input-group form-control-xs date datetimepicker p-0 m-0" id="send_date_{{ $doc_cd }}" data-target-input="nearest">
                                <input type="text" class="form-control form-control-xs datetimepicker-input" style="height:25px;" data-target="#send_date_{{ $doc_cd }}" name="send_date_{{ $doc_cd }}" id="send_date_{{ $doc_cd }}" DateOnly="true" size="6" data-toggle="datetimepicker" value="{{ Func::dateFormat($dinfo['send_date']) }}">
                                <div class="input-group-append" data-target="#send_date_{{ $doc_cd }}" data-toggle="datetimepicker">
                                    <div class="input-group-text text-xs" style="height:25px;"><i class="fa fa-xs fa-calendar p-0"></i></div>
                                </div>
                                </div>
                            </td>
                            <td class="text-center">
                                <div class="input-group form-control-xs date datetimepicker p-0 m-0" id="arrival_date_{{ $doc_cd }}" data-target-input="nearest">
                                <input type="text" class="form-control form-control-xs datetimepicker-input" style="height:25px;" data-target="#arrival_date_{{ $doc_cd }}" name="arrival_date_{{ $doc_cd }}" id="arrival_date_{{ $doc_cd }}" DateOnly="true" size="6" data-toggle="datetimepicker" value="{{ Func::dateFormat($dinfo['arrival_date']) }}">
                                <div class="input-group-append" data-target="#arrival_date_{{ $doc_cd }}" data-toggle="datetimepicker">
                                    <div class="input-group-text text-xs" style="height:25px;"><i class="fa fa-xs fa-calendar p-0"></i></div>
                                </div>
                                </div>
                            </td>
                            <td class="text-center"><input type="checkbox" class="icheckbox_square-blue-sm" name="scan_chk_{{ $doc_cd }}" id="scan_chk_{{ $doc_cd }}" value="Y" {{ Func::echoChecked('Y', $dinfo['scan_chk']) }}></td>
                            <td class="text-center"><input type="checkbox" class="icheckbox_square-blue-sm" name="keep_chk_{{ $doc_cd }}" id="keep_chk_{{ $doc_cd }}" value="Y" {{ Func::echoChecked('Y', $dinfo['keep_chk']) }}></td>
                            <td class="text-center"><input type="text" class="form-control form-control-xs" style="height:25px;" data-target="#memo_{{ $doc_cd }}" name="memo_{{ $doc_cd }}" id="memo_{{ $doc_cd }}" value="{{ $dinfo['memo'] }}"></td>
                            </tr>

                        @endforeach




                        <!-- 필수가 아닌애들 -->
                        @foreach( $array_config['app_document'] as $doc_cd => $doc_nm )
                            @php
                            if( isset($array_doc[$doc_cd]) )
                            {
                                $dinfo = $array_doc[$doc_cd];
                            }
                            else
                            {
                                $dinfo = [];
                                $dinfo['necessary_chk'] = "N";
                                $dinfo['send_type_cd']  = "";
                                $dinfo['send_date']     = "";
                                $dinfo['arrival_date']  = "";
                                $dinfo['scan_chk']      = "N";
                                $dinfo['keep_chk']      = "N";
                                $dinfo['memo']          = "";
                            }
                            @endphp
                        <tr>
                        <td class="text-center">{{ $i++ }}</td>
                        <td class="text-center">
                            {{ $doc_nm }}
                        </td>
                        <td class="text-center"><input type="checkbox" class="icheckbox_square-blue-sm" name="necessary_chk_{{ $doc_cd }}" id="necessary_chk_{{ $doc_cd }}" value="Y" {{ Func::echoChecked('Y', $dinfo['necessary_chk']) }}></td>
                        <td class="text-center">
                            <select class="form-control form-control-xs" name="send_type_cd_{{ $doc_cd }}" id="send_type_cd_{{ $doc_cd }}" >
                            <option value=''>선택</option>
                            {{ Func::printOption($array_config['send_type_cd'], $dinfo['send_type_cd']) }}   
                            </select>
                        </td>
                        <td class="text-center">
                            <div class="input-group form-control-xs date datetimepicker p-0 m-0" id="send_date_{{ $doc_cd }}" data-target-input="nearest">
                            <input type="text" class="form-control form-control-xs datetimepicker-input" style="height:25px;" data-target="#send_date_{{ $doc_cd }}" name="send_date_{{ $doc_cd }}" id="send_date_{{ $doc_cd }}" DateOnly="true" size="6" data-toggle="datetimepicker" value="{{ Func::dateFormat($dinfo['send_date']) }}">
                            <div class="input-group-append" data-target="#send_date_{{ $doc_cd }}" data-toggle="datetimepicker">
                                <div class="input-group-text text-xs" style="height:25px;"><i class="fa fa-xs fa-calendar p-0"></i></div>
                            </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <div class="input-group form-control-xs date datetimepicker p-0 m-0" id="arrival_date_{{ $doc_cd }}" data-target-input="nearest">
                            <input type="text" class="form-control form-control-xs datetimepicker-input" style="height:25px;" data-target="#arrival_date_{{ $doc_cd }}" name="arrival_date_{{ $doc_cd }}" id="arrival_date_{{ $doc_cd }}" DateOnly="true" size="6" data-toggle="datetimepicker" value="{{ Func::dateFormat($dinfo['arrival_date']) }}">
                            <div class="input-group-append" data-target="#arrival_date_{{ $doc_cd }}" data-toggle="datetimepicker">
                                <div class="input-group-text text-xs" style="height:25px;"><i class="fa fa-xs fa-calendar p-0"></i></div>
                            </div>
                            </div>
                        </td>
                        <td class="text-center"><input type="checkbox" class="icheckbox_square-blue-sm" name="scan_chk_{{ $doc_cd }}" id="scan_chk_{{ $doc_cd }}" value="Y" {{ Func::echoChecked('Y', $dinfo['scan_chk']) }}></td>
                        <td class="text-center"><input type="checkbox" class="icheckbox_square-blue-sm" name="keep_chk_{{ $doc_cd }}" id="keep_chk_{{ $doc_cd }}" value="Y" {{ Func::echoChecked('Y', $dinfo['keep_chk']) }}></td>
                        <td class="text-center"><input type="text" class="form-control form-control-xs" style="height:25px;" data-target="#memo_{{ $doc_cd }}" name="memo_{{ $doc_cd }}" id="memo_{{ $doc_cd }}" value="{{ $dinfo['memo'] }}"></td>
                        </tr>
                        @endforeach
                        </tbody>

                    </table>
                    </div>
                </div>
                <div class="text-right p-2">
                    <button class="btn btn-sm bg-lightblue m-2" type="button" id="btn_doc_save" onclick="docAction('UPD')">저장</button>
                    </div>
            </div>
        </div>



    </div>
</form>
@endsection



@section('javascript')
 
 
<script>
// 로드시 스크롤위치 조정
$(document).ready(function(){
    
    $(".datetimepicker").datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
        useCurrent: false,
    });

    $('input[id^="scan_chk"]').iCheck({
        checkboxClass: 'icheckbox_square-blue',
    });
    $('input[id^="keep_chk"]').iCheck({
        checkboxClass: 'icheckbox_square-blue',
    });
    $('input[id^="necessary_chk"]').iCheck({
        checkboxClass: 'icheckbox_square-blue',
    });

    window.resizeTo(1500, 1000 );
    $(window).scrollTop(0);

});






// 화해ACTION
function docAction() 
{
    if( !confirm("저장하시겠습니까?") )
    {
        return false;
    }

    var formData = new FormData($('#doc_form')[0]);
    formData.append("action_mode", "UPDATE_DOC_INFO");

    $("#btn_doc_save").prop("disabled",true);

    $.ajax({
        url  : "/erp/docinfoaction",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result=="Y" )
            {
                alert("정상처리 완료");
                window.opener.listRefresh();
                location.href = "/erp/docinfo?cust_info_no={{ $cust_info_no }}&loan_info_no={{ $loan_info_no }}";
            }
            else
            {
                alert(result);    
                $("#btn_doc_save").prop("disabled",false);
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
            $("#btn_doc_save").prop("disabled",false);
        }
    });
}



</script>
@endsection