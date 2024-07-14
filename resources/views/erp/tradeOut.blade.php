@extends('layouts.master')


@section('content')
@include('inc/list')

<div style="display:none" id="headerArea">
    <h1 id="headerTitle"></h1>
</div>

<div class="modal fade" id="bankinfoModal" style="display: none;" aria-hidden="true">
<div class="modal-dialog modal-lg">
<div class="modal-content" id="tradeOutPrint">
    <div class="modal-header">
        <h4 class="modal-title">출금리스트 송금정보</h4>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
        <span aria-hidden="true">×</span>
        </button>
    </div>
    <div class="modal-body" id="bankinfoModalContent">
    </div>
    <div class="modal-footer justify-content-between">
        <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
        <button type="button" class="btn btn-sm btn-default" onClick="$('#tradeOutPrint').print();">인쇄</button>
        <div class="p-0">
            <button type="button" class="btn btn-sm btn-info" onclick="bankInfoUpdateAction();" id="btn_bankInfoUpdateAction">계좌변경요청</button>
        </div>
    </div>
</div>    
</div>    
</div>    


@endsection


@section('lump')
일괄처리할거 입력
@endsection

@section('javascript')
<script>


// 송금정보 modal show 동작
function bankInfoForm(cin, lin, ltn) {
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$("#bankinfoModal").modal('show');
	$("#bankinfoModalContent").html(loadingString);
	$.post("/erp/tradeoutbankinfo", { cust_info_no: cin, loan_info_no: lin, loan_info_trade_no: ltn }, function (data) {
		$("#bankinfoModalContent").html(data);
        if($('#modal_firmbank_status').val() == "N")
        {
            $('#btn_bankInfoUpdateAction').removeClass('d-none');
        }
        else
        {
            $('#btn_bankInfoUpdateAction').addClass('d-none');
        }
	});
}



function viewBankInfoForm(seq, bcd, bsn, bow, trm)
{
    $("#sub_seq").val(seq);
    $("#sub_bank_code").val(bcd);
    $("#sub_bank_ssn").val(bsn);
    $("#sub_bank_owner").val(bow);
    $("#sub_trade_sub_money").val(trm);

    $("#sub_bank_chk_btn").html("예금주조회");
    $("#sub_bank_chk_btn").addClass("btn-secondary");
    $("#sub_bank_chk_btn").removeClass("btn-success");
    $("#sub_bank_chk_yn").val("N");
    $("#sub_bank_chk_time").val("");
    $("#sub_bank_chk_id").val("");

    @if (Func::funcCheckPermit("A141","A")) // 펌뱅킹 송금계좌 변경 권한체크
    $("#sub_bank_code").prop('disabled', false);
    $("#sub_bank_ssn").prop('disabled', false);
    $("#sub_bank_owner").prop('disabled', false);
    $("#sub_bank_chk_btn").attr("disabled", false);
    @endif

    $('#collapseBankInfoForm').collapse('show');
}


// 예금주명 조회
function bankAccountcheck()
{

    var bank_cd    = $("#sub_bank_code").val();
    var bank_ssn   = $("#sub_bank_ssn").val();
    var bank_owner = $("#cust_name").html();

    $("#sub_bank_chk_btn").html("예금주조회");
    $("#sub_bank_chk_btn").attr("disabled", true);
    $("#sub_bank_chk_btn").removeClass("btn-success");
    $("#sub_bank_chk_btn").removeClass("btn-danger");
    $("#sub_bank_chk_btn").addClass("btn-secondary");

    $("#sub_bank_chk_yn").val("N");
    $("#sub_bank_chk_time").val("");
    $("#sub_bank_chk_id").val("");

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url  : "/erp/tradeoutbankchk",
        type : "post",
        data : { bank_cd:bank_cd, bank_ssn:bank_ssn, bank_owner:bank_owner },
        dataType: "json",
        success : function(data)
        {
            if( data.bank_chk_yn=="Y" )
            {
                $("#sub_bank_chk_btn").removeClass("btn-secondary");
                $("#sub_bank_chk_yn").val(data.bank_chk_yn);
                $("#sub_bank_chk_time").val(data.bank_chk_time);
                $("#sub_bank_chk_id").val(data.bank_chk_id);
                $("#sub_bank_owner").val(data.bank_chk_owner);       // 조회된 결과값으로 입력

                // 수정못하게 막기
                $("#sub_bank_code").attr("disabled", true);
                $("#sub_bank_ssn").attr("disabled", true);
                $("#sub_bank_owner").attr("disabled", true);

                // 판단
                if( data.bank_chk_owner_yn=="Y" )
                {
                    $("#sub_bank_chk_btn").html("조회완료(일치)");
                    $("#sub_bank_chk_btn").addClass("btn-success");
                }
                else
                {
                    $("#sub_bank_chk_btn").html("조회완료(불일치)");
                    $("#sub_bank_chk_btn").addClass("btn-danger");
                }
            }
            else
            {
                alert(data.bank_chk_msg);
            }
            $("#sub_bank_chk_btn").attr("disabled", false);
        },
        error : function(xhr)
        {
            alert("조회에 실패하였습니다.");
            $("#sub_bank_chk_btn").attr("disabled", false);
            console.log(xhr);
        }
    });
}



function bankInfoUpdateAction()
{
    if( $('#collapseBankInfoForm').css("display")=="none" )
    {
        alert("변경할 송금정보를 선택해주세요.\n(처리상태가 송금실패인 경우만 수정 가능합니다.)");
        return false;
    }
    if( $("#sub_bank_chk_yn").val()!="Y" )
    {
        alert("예금주명 조회가 완료되지 않았습니다.");
        return false;
    }
    //$("#sub_bank_code").attr('disabled', false);
    //$("#sub_bank_ssn").attr('disabled', false);
    //$("#sub_bank_owner").attr('disabled', false);

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var formData = new FormData($('#trade_out_bankinfo_form')[0]);
    formData.append("sub_bank_code", $("#sub_bank_code").val());
    formData.append("sub_bank_ssn", $("#sub_bank_ssn").val());
    formData.append("sub_bank_owner", $("#sub_bank_owner").val());
    $("#btn_bankInfoUpdateAction").attr('disabled', true);

    $.ajax({
        url  : "/erp/tradeoutbankinfoaction",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result=="Y" )
            {
                alert("송금정보가 정상적으로 수정되었습니다.");
                $("#bankinfoModalContent").html("");
                $("#bankinfoModal").modal('hide');
                getDataList('tradeout', 1, '/erp/tradeoutlist', $('#form_tradeout').serialize());
            }
            else
            {
                alert(result);
            }
            $("#btn_bankInfoUpdateAction").attr('disabled', false);
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
            $("#btn_bankInfoUpdateAction").attr('disabled', false);
        }
    });

}

function tradeOutForm(n)
{
    //중앙위치 구해오기
    width  = 900;
    height = 800;

    LeftPosition =(screen.width-width)/2;
    TopPosition  =(screen.height-height)/2;

    var wnd = window.open("/erp/tradeoutform?no="+n, "tradeoutpop","width="+width+", height="+height+",top="+TopPosition+",left="+LeftPosition+", scrollbars=yes");
    wnd.focus();
}


// 엔터막기
function enterClear()
{
    $('input[type="text"]').keydown(function() {
      if( event.keyCode === 13 )
      {
        event.preventDefault();
        listRefresh();
      };
    });

    $("input[data-bootstrap-switch]").each(function() {
    $(this).bootstrapSwitch('state', $(this).prop('checked'));
  });
}


function lump_del(btn_obj)
{
    if( checkOneMore()===false )
    {
        alert('체크박스를 선택해주세요');
        return false;
    }
    if(!confirm("선택하신 출금 거래내역을 삭제하시겠습니까?\n삭제하시면 복구할 수 없으며 필요시 수기로 재등록해야합니다."))
    {
        return false;
    }

    var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
    formData.append("action_mode", "LUMP_TRADEOUT_DELETE");

    btn_obj.disabled = true;
    $("#"+btn_obj.id).html(loadingStringtxt);

    $.ajax({
        url  : "/erp/tradeoutdelete",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result=="Y" )
            {
                //alert(result);
                alert("출금 삭제처리 완료");
                listRefresh();
            }
            else
            {
                alert(result);
            }

            btn_obj.disabled = false;
            $("#"+btn_obj.id).html("삭제처리");
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
        }
    });

}

function checkOneMore()
{
    var checked = $('input[name="listChk[]"]:checked').length > 0;
    if(checked !== true)
    {
        return false;
    }
    else
    {
        return true;
    }
}

enterClear();

</script>
@endsection