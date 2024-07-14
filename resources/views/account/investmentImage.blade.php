<!-- 투자내역 -->
<div class="col-md-12 p-0 m-0 " >
    <div class="card-header p-1" style="border-bottom:none !important;">
        <h6 class="card-title">첨부파일</h6>
    </div>

    <br>
    <!-- <div class="card-body" id="investmentinfoInput" style='display:@if(isset($v->no)) block; @else none; @endif'> -->
    <form class="mb-0" id="img_form" name="img_form" method="post" enctype="multipart/form-data" action="">
    @csrf
    <input type="hidden" id="loan_info_no" name="loan_info_no" value="{{ $result['customer']['loan_info_no'] ?? '' }}">
    <input type="hidden" id="cust_info_no" name="cust_info_no" value="{{ $result['customer']['cust_info_no'] ?? '' }}">
    <input type="hidden" id="mode" name="mode" value="{{ $mode?? '' }}">
    <input type="hidden" id="no" name="no" value="{{ $no?? '' }}">
    <input type="hidden" id="loan_usr_info_no" name="loan_usr_info_no" value="{{ $v->loan_usr_info_no ?? '' }}">
        <div class="form-goup row">
            <!-- <b>첨부파일</b> -->
            <table class="table table-sm table-hover loan-info-table card-secondary card-outline">
                <thead>
                    <tr>
                        <th class="text-center">채권번호</th>
                        <th class="text-center">파일구분</th>
                        <th class="text-center">등록자</th>
                        <th class="text-center">등록일시</th>
                        <th class="text-center">파일</th>
                    </tr>
                </thead>
                <tbody id="loan_document">
                    @forelse( $img as $idx => $val )
                        <tr onclick="setimageInput({{ $val->no }}, {{ $val->loan_usr_info_no }})">
                            <td class="text-center">{{ $val->investor_type.$val->investor_no }}-{{ $val->inv_seq }}</td>
                            <td class="text-center">{{ Func::getArrayName($arr_image_div, $val->img_div_cd) }}</td>
                            <td class="text-center">{{ Func::getUserId($val->save_id)->name }}</td>
                            <td class="text-center">{{ Func::dateFormat($val->save_time) }}</td>
                            <td class="text-center" onClick="event.cancelBubble=true;">
                                <a href="/account/downinvestorimg/{{$val->no}}" download="{{$val->origin_filename}}" class="hand text-blue">
                                    <i class="fas fa-file-download pr-1"></i>
                                    {{$val->origin_filename}}
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class='text-center p-4'>등록된 파일이 없습니다.</td>
                        </tr>
                    @endforelse
                    <tr><td colspan="13"></td></tr>
                </tbody>
            </table>
        </div>
        <div class='row'>
            <div class="col-sm-6" @if(empty($userVar)) style="display:none" @endif>
                <b>첨부파일</b>
                <table class="table table-sm table-bordered table-input text-xs">
                    <colgroup>
                        <col width="20%" />
                        <col width="80%" />
                    </colgroup>
                    <tbody id="sel_document">
                        <tr id="tr_image_div">
                            <th>구분</th>
                            <td>
                                <select class="form-control form-control-sm text-xs col-md-3" onchange='change_div(this)' name="img_div_cd" id="img_div_cd">
                                <option value=''>구분선택</option>
                                    {{ Func::printOption($arr_image_div_select, isset($selected_img[0]->img_div_cd)? $selected_img[0]->img_div_cd : (isset($selected_img[0]->img_div_cd)? $selected_img[0]->img_div_cd : "")) }}
                                </select>
                            </td>
                        </tr>
                        {{-- <tr>
                            <th>구분</th>
                            <td>
                                <select class="form-control form-control-sm text-xs col-md-3" name="taskname" id="taskname">
                                <option value=''>구분선택</option>
                                    {{ Func::printOption($arr_task_name, isset($selected_img[0]->taskname)? $selected_img[0]->taskname : "") }}
                                </select>
                            </td>
                        </tr> --}}
                        <tr id="down_img">
                        </tr>
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
                            <td class="text-right" colspan=2 id='input_footer'>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-sm-6 text-center" id='img_preview'>
                
            </div>
        </div>
</div>

<script>
// getDataList('{{ $result['listName'] }}', '{{ $result['page'] ?? 1 }}', '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());
setimageInput('', '');
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
                $("#loan_document").html(data.loan_ducument_html);
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

function getInvestorData(md,div_no,no,loan_usr_info_no)
{
    // CORS 방지
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    // 현재 선택된 메뉴가 무엇인지 저장
    $("#investor_select").val(md);

    // 전체 메뉴 색 입히고, 선택항목만 흰 배경으로 변경
    resetMenuColor();
    selectMenuColor('investor', md);

    // 고객정보 받아오기
    var url = "/account/"+md;
    var img_no = no;
    var no = loan_usr_info_no;
    $("#customer-contents").html(loadingString);       
    $.post(url, { mode:md, loan_usr_info_no:no, no:img_no }, function(data) {
        console.log(data);
        $("#customer-contents").html();
        afterAjax();
    });
}


function setimageInput(no, loan_usr_info_no)
{
    if( no == "" )
    {
        $('#sel_document :input[name=taskname]').val('');
        $('#down_img').html('');
        $('#sel_document :input[name=memo]').val('');
        $('#input_footer').html("<input type='button' class='btn btn-sm btn-info float-right' onclick=\"imgAction('INS');\" value='등록'>");
    }
    else{
        $('#tr_image_div').css("display", "none");
    }

    // 데이터 가져와서 memo 상세내역 채우기
    $.post("/account/investmentimageinput", {no:no}, function(data) {
        
        var mode = JSON.parse(data)['mode'];
        var memo = JSON.parse(data)['data'];

        if( memo )
        {
            var ino = '';
            var origin_filename = '';
            var loan_usr_info_no = '';
            var extension = '';
            
            // 기존메모값 세팅
            $.each(memo, function(key, item)
            {
                $('#sel_document :input[name='+key+']').val(item);
                if(key == 'no')
                {
                    ino = item;
                    img_form.no.value = item;
                }
                if(key == 'origin_filename')
                {
                    origin_filename = item;
                }
                if(key == 'loan_usr_info_no')
                {
                    loan_usr_info_no = item;
                }
                if(key == 'extension')
                {
                    extension = item;
                }
            })

            $('#down_img').html("<th>파일다운로드</th><td><a href='/account/downinvestorimg/"+ino+"' download='"+origin_filename+"'<span class='hand text-blue'><i class='fas fa-file-download pr-1'></i>"+origin_filename+"</span></a></td>");

            $('#input_footer').html("<input type='button' class='btn btn-sm btn-info float-right ml-2' onclick=\"imgAction('INS');\" value='새로등록'><input type='button' class='btn btn-sm btn-secondary float-left' onclick=\"setimageInput('', '');\" value='취소'><input type='button' class='btn btn-sm btn-info float-right ml-2' onclick=\"imgAction('UPD');\" value='수정' ><input type='button' class='btn btn-sm btn-danger float-right' onclick=\"imgAction('DEL');\" value='삭제'>");
            $('#img_preview').html('<a href=javascript:filePreview('+ino+','+loan_usr_info_no+',\''+extension+"')><i class='fa fa-plus-circle'></i> 크게보기</a>");
            if(extension == 'pdf')
            {
                $('#img_preview').append("<iframe src='/pdfjs/web/viewer.html?file=/account/getinvestorinfoimg/"+ino+"/"+loan_usr_info_no+"#page=30' style='width:100%;height:800px'></iframe>"); 
            }
            else {
                $('#img_preview').append("<img style='width:100%;height:100%;' src='/account/getinvestorinfoimg/"+ino+"/"+loan_usr_info_no+"'>");
            }

        }
    }); 
}

function imgAction(mode)
{
    var imgDiv = $('#img_div_cd');
    let file   = $('#customFile');
    let loan_info_no = $('#loan_info_no').val();
    if(imgDiv.val() == null || imgDiv.val() == '' && mode == 'INS'){
        alert("구분을 선택하여 주십시오");
        return false;
    }
    if(file.val() == null || file.val() == '' && mode == 'INS'){
        alert("파일을 선택하여 주십시오");
        return false;
    }

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
        url  : "/account/investmentimageaction",
        type : "post",
        data : postdata,
        processData : false,
        contentType : false,
        success : function(result) {
            globalCheck = false;
            // alert(result);
            // getInvestmentData('investmentimage');
            getInvestmentData('investmentimage','',loan_info_no,'','','','{{ $result['page'] ?? 1 }}');
        },
        error : function(xhr) {
            globalCheck = false;
            alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
}

function filePreview(no, loan_usr_info_no, ext)
{
    window.open('/account/usrimagepriview/'+ no + '/' + loan_usr_info_no + '/' + ext, 'popOpen'+no, 'status=no, left=0,top=0, height=900, width=900');
}

bsCustomFileInput.init();
</script>