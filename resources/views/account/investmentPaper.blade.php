<!-- 투자내역 -->
<div class="col-md-12 p-0 m-0 " >
    <div class="card-header p-1" style="border-bottom:none !important;">
        <h6 class="card-title">투자리스트</h6>
    </div>
    @include('inc/listSimple')

    <br>
    <div class="card-body" id="investmentinfoInput" style='display:@if(isset($v->no)) block; @else none; @endif'>
    <form class="mb-0" name="investmentPaper_form" id="investmentPaper_form" method="post" enctype="multipart/form-data">
    <input type="hidden" id="loan_info_no" name="loan_info_no" value="{{ $result['customer']['loan_info_no'] ?? '' }}">
    <input type="hidden" id="cust_info_no" name="cust_info_no" value="{{ $result['customer']['cust_info_no'] ?? '' }}">
    <input type="hidden" id="loan_usr_info_no" name="loan_usr_info_no" value="{{ $v->loan_usr_info_no ?? '' }}">
        <div class="form-goup row">
            <b>징구서류관리</b>
            <table class="table table-sm table-hover loan-info-table card-secondary card-outline">
                {{-- <thead>
                    <tr>
                        <th class="text-center"><input id="check_all" type="checkbox" class="icheckbox_square-blue-sm"></th>
                        <th class="text-center"><span class="text-danger font-weight-bold h6 mr-1">*</span>서류</th>
                        <th class="text-center w-10">최초인쇄일</th>
                        <th class="text-center w-10">발송방법</th>
                        <th class="text-center w-10">발송일</th>
                        <th class="text-center w-10">도착일</th>
                        <th class="text-center">스캔</th>
                        <th class="text-center">보관</th>
                        <th class="text-center w-20">메모</th>
                        <th class="text-center">작업자</th>
                        <th class="text-center">작업시간</th>
                    </tr>
                </thead>
                <tbody id="loan_document">
                    @foreach( Vars::$arrayInvestPaper as $key => $val )
                    @if($key != 03)
                    <tr>
                        <td class="text-center"><input type="checkbox" class="icheckbox_square-blue-sm docs_cd" id="docs_cd[]" name="docs_cd[]" value="{{ $key }}"></td>
                        <td class="text-center">{{ $val }}</td>
                        <td class="text-center">{{ isset($arrayDocData[$key])?Func::dateFormat($arrayDocData[$key]['print_date']):"" }}</td>
                        <td class="text-center">{{ Func::getArrayName($configArr['send_type_cd'], $arrayDocData[$key]['send_type_cd'] ?? '') }}</td>
                        <td class="text-center">{{ isset($arrayDocData[$key])?Func::dateFormat($arrayDocData[$key]['send_date']):"" }}</td>
                        <td class="text-center">{{ isset($arrayDocData[$key])?Func::dateFormat($arrayDocData[$key]['arrival_date']):"" }}</td>
                        <td class="text-center">@if(isset($arrayDocData[$key]) && $arrayDocData[$key]['scan_chk']=="Y")<i class='fas fa-check text-green'></i>@endif</td>
                        <td class="text-center">@if(isset($arrayDocData[$key]) && $arrayDocData[$key]['keep_chk']=="Y")<i class='fas fa-check text-green'></i>@endif</td>
                        <td class="text-center">{{ isset($arrayDocData[$key])?$arrayDocData[$key]['memo']:"" }}</td>
                        <td class="text-center">{{ Func::getArrayName($array_user,$arrayDocData[$key]['save_id'] ?? '') }}</td>
                        <td class="text-center">{{ isset($arrayDocData[$key])?Func::dateFormat($arrayDocData[$key]['save_time']):"" }}</td>
                    </tr>
                    @endif
                    @endforeach
                </tbody>
                <tbody>
                    <tr>
                        <th class="text-center"></th>
                        <th class="text-center"></th>
                        <th class="text-center"></th>
                        <th class="text-center">
                            <select class="form-control form-control-sm" name="send_type_cd" id="send_type_cd" >
                            <option value=''>선택</option>
                            {{ Func::printOption($configArr['send_type_cd']) }}   
                            </select>
                        </th>                        
                        <th class="text-center">
                            <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-1" id="send_date" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#send_date" name="send_date" id="send_date" DateOnly="true" size="6">
                            <div class="input-group-append" data-target="#send_date" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                            </div>
                            </div>
                        </th>
                        <th class="text-center">
                            <div class="input-group mt-0 mb-0 date datetimepicker mr-1 mb-1" id="arrival_date" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#arrival_date" name="arrival_date" id="arrival_date"  DateOnly="true" size="6">
                            <div class="input-group-append" data-target="#arrival_date" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-xs fa-calendar"></i></div>
                            </div>
                            </div>
                        </th>
                        <th class="text-center"><input type="checkbox" class="icheckbox_square-blue-sm" name="scan_chk" id="scan_chk" value="Y"></th>
                        <th class="text-center"><input type="checkbox" class="icheckbox_square-blue-sm" name="keep_chk" id="keep_chk" value="Y"></th>
                        <th class="text-center"><input class="form-control form-control-sm" type="text" name="memo"></th>
                        <th class="text-center" colspan=2><button onclick="docAction('UPDATE');" type="button" class="btn btn-sm bg-lightblue">선택적용</button></th>
                    </tr>
                </tbody> --}}
                <thead>
                    <tr>
                        <th class="text-center"><span class="text-danger font-weight-bold h6 mr-1">*</span>서류</th>
                        <th class="text-center">작업자</th>
                        <th class="text-center">작업시간</th>
                        <th class="text-center w-20">메모</th>
                    </tr>
                </thead>
                <tbody id="loan_document">
                    @foreach( $arrayPaper as $key => $val )
                    <tr>
                        <td class="text-center">{{ Vars::$arrayImageUploadDivision[explode('_', $key)[0]] }}</td>
                        <td class="text-center">{{ Func::getArrayName($array_user,$arrayPaper[$key]['save_id'] ?? ($arrayPaper[$key]['worker_id'] ?? '' )) }}</td>
                        <td class="text-center">{{ isset($arrayPaper[$key])?Func::dateFormat($arrayPaper[$key]['save_time']):"" }}</td>
                        <td class="text-center">{{ isset($arrayPaper[$key])?$arrayPaper[$key]['memo']:"" }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="form-group row col-sm-6" @if(empty($userVar)) style="display:none" @endif>
            <b>양식인쇄</b>
            <table class="table table-sm table-bordered table-input text-xs">
                <colgroup>
                    <col width="20%" />
                    <col width="80%" />
                </colgroup>
                <tbody>
                <tr>
                    <th>인쇄양식</th>
                    <td>
                        <div class="form-group row">
                            <select class="form-control form-control-sm col-sm-6 ml-2 mr-2" id="post_cd" name="post_cd" title="선택" onchange="checkPaper(this)">
                                <option value=''>선택</option>
                                {!! Func::printOption($paperForm); !!}
                            </select>
                        </div>
                    </td>
                </tr>

                <tr id='basis_date_tr'>
                    <th>기준일자</th>
                    <td>
                        <div class="input-group date datetimepicker" id="basis_date" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm col-sm-3 dateformat datetimepicker" name="print_basis_date" id="print_basis_date" inputmode="text" value="{{ date('Y-m-d') }}" DateOnly="true" size="6">
                            <div class="input-group-append" data-target="#print_basis_date" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </td>
                </tr>

                <tr id ="trade_date_tr" style="display:none">
                    <th>대출실행일</th>
                    <td>
                        <div class="input-group date datetimepicker" id="trade_date" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm col-sm-3 dateformat datetimepicker" name="print_trade_date" id="print_trade_date" inputmode="text" DateOnly="true" size="6">
                            <div class="input-group-append" data-target="#print_trade_date" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </td>
                </tr>
                
                <tr>
                    <th>마스킹여부</th>
                    <td>
                        <div class="input-group">
                            <input type="checkbox" class="icheckbox_square-blue-sm masking" id="masking" name="masking" value="Y"></div>
                        </div>
                    </td>
                </tr>

                <tr>
                    <th>받을 주소</th>
                    <td>
                        <div class="row">
                            <div class="input-group col-sm-4 pb-1">
                                <input type="hidden" id="post_addr_cd" name="post_addr_cd" value=""/>
                                <input type="text" class="form-control" name="zip" id="zip" numberonly="true" value="">
                            </div>
                            <div class="pl-0 p-1">
                                <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_1" onclick="setAddr('zip', 'addr1', 'addr2', '{{$v->zip1 ?? ''}}', '{{$v->addr11 ?? ''}}', '{{$v->addr12 ?? ''}}');">주소1</button>
                                <button type="button" class="btn btn-secondary btn-xs postBtn" id="postBtn_2" onclick="setAddr('zip', 'addr1', 'addr2', '{{$v->zip2 ?? ''}}', '{{$v->addr21 ?? ''}}', '{{$v->addr22 ?? ''}}');">주소2</button>
                                <button type="button" class="btn btn-secondary btn-xs" onclick="setAddr('zip', 'addr1', 'addr2', '', '', ''); ">지우기</button>
                            </div>
                        </div>
                        <input type="text" class="form-control mb-1 col-md-10" name="addr1" id="addr1" value="" >
                        <input type="text" class="form-control col-md-10" name="addr2" id="addr2" value="">
                    </td>
                </tr>
                
                <tr>
                    <td colspan=2 class="">
                        <button type="button" class="btn btn-sm btn-secondary  mb-1" onclick="printAction();">
                            <i class="fas fa-print"></i> 인쇄
                        </button>
                    </td>
                </tr>
            </table>
        </div>
</div>

<script>
getDataList('{{ $result['listName'] }}', '{{ $result['page'] ?? 1 }}', '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());

function docAction(mode)
{
    if(mode=='UPDATE')
    {
        if(checkValue() == false)
        {
            return false;
        }
    }

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var rs_code;
    var postdata = $('#investmentPaper_form').serialize();
    postdata += '&mode='+mode;
    $("#loan_document").html(loadingString);
    $.post(
        "/account/investmentpaperaction", 
        postdata, 
        function(data) {
            rs_code = data.rs_code;
            if(data.rs_code!="Y")
            {
                alert(data.result_msg);
            }
            else
            {
                $("#loan_document").html(data.loan_document_html);
            }

            return rs_code;
    });
}


// 유효성검사
function checkValue() 
{
    $(".was-validated").removeClass("was-validated");
    var result = false;

    $('input[name="docs_cd[]"]:checked').each(function() {
        result = true;
    });

    if(result == false)
    {
        alert("체크박스를 선택해주세요");
    }

    return result;
}

$(".datetimepicker").datetimepicker({
    format: 'YYYY-MM-DD',
    locale: 'ko',
    widgetPositioning: {
        horizontal: 'left',
        vertical: 'bottom'
    }
});

$('#check_all').click(function(){
    if($('#check_all').is(":checked"))
    {
        $(".docs_cd").prop("checked",true);
    }
    else
    {
        $(".docs_cd").prop("checked",false);
    }
});


$('input[id="scan_chk"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});
$('input[id="keep_chk"]').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});

// $('#print_basis_date').datetimepicker({
//     format: 'YYYY-MM-DD',
//     locale: 'ko',
//     useCurrent: false,
//     widgetPositioning:{
//         horizontal : 'auto',
//         vertical: 'bottom'
//     }
// });

function printAction()
{
    var postCd = $('#post_cd').val();
    if(!postCd)
    {
        alert('인쇄양식을 선택해주세요.');
        return false;
    }

    // 최초인쇄일 UPDATE
    docAction('PAPER');

    var urld  = "/lump/printview";
    var title = "printview";

    var formdata = $('#investmentPaper_form').serializeArray();
    var url = urld+"?fData="+JSON.stringify(formdata);
    var wnd = window.open(url, title,"width=900, height=800, scrollbars=yes");
    wnd.focus();
}

// 확약서 선택시 대출실행일 날짜 입력란 추가
function checkPaper(seletedPaper)
{
    var checkPaperArr = ['LC002','CN002','GP002','HW002','RD002','DB002','GL002', 'SA002'];   // 해당 계약서 양식 배열
    if(checkPaperArr.includes(seletedPaper.value))
    {
        $('#print_trade_date').val("{{ date('Y-m-d') }}")
        $('#trade_date_tr').show();
    }
    else
    {
        $('#print_trade_date').val(null)
        $('#trade_date_tr').hide();
    }
}

</script>