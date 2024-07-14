@extends('layouts.master')


@section('content')
@include('inc/list')
<!-- 계약명세 모달 -->
@endsection


@section('lump')
일괄처리할거 입력
@endsection

@section('javascript')
<script>



function tradeInForm(n)
{
    //중앙위치 구해오기
    width  = 900;
    height = 1000;

    LeftPosition =(screen.width-width)/2;
    TopPosition  =(screen.height-height)/2;

    var wnd = window.open("/erp/tradeinform?no="+n, "tradeinpop","width="+width+", height="+height+",top="+TopPosition+",left="+LeftPosition+", scrollbars=yes");
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
    if(!confirm("선택하신 수익지급내역을 삭제하시겠습니까?\n삭제하시면 복구할 수 없으며 필요시 수기로 재등록해야합니다."))
    {
        return false;
    }

    var formData = new FormData($('#form_{{ $result['listName'] }}')[0]);
    formData.append("action_mode", "trade_in_DELETE");

    btn_obj.disabled = true;
    $("#"+btn_obj.id).html(loadingStringtxt);

    $.ajax({
        url  : "/erp/tradeindelete",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result['rslt'] == "Y" )
            {
                alert(result['msg']);
                listRefresh();
            }
            else
            {
                alert(result['msg']);
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

// 일괄처리 클릭
function deleteTradeInLump(lumpcd, btnName)
{
    if(!isCheckboxChecked('listChk[]'))
    {
        alert('선택한 고객이 없습니다. 고객을 선택 후 이용해 주세요.');
        return;
    }

    // 입금삭제 일괄처리의 경우 등록일이 동일할 경우만 가능함
    var old_td = '';
    var old_mc = '';
    var td = '';
    var mc = '';
    var tdChk = "Y";
    $("input:checkbox[name='listChk[]']:checked").each(function(i){  
        var tNo = $(this).val();
        td = $('#td_'+tNo).val();
        if(old_td && td != old_td)
        {
            tdChk = "N";
            return false;
        }
        old_td = td;
    });
    if(tdChk == "N")
    {
        // 날짜가 달라도 진행한다.
        // alert('선택건들의 등록일이 동일해야 합니다. 등록일을 확인해주세요');
        // return;
    }
    tdChk = 'Y';


     $("input:checkbox[name='listChk[]']:checked").each(function(i){  
        var tNo = $(this).val();
        mc = $('#mc_'+tNo).val();
        if(old_mc && mc != old_mc)
        {
            tdChk = "N";
            return false;
        }
        old_mc = mc;
    });
    if(tdChk == "N")
    {
        alert('선택건들의 관리지점이 동일해야합니다.관리지점을 확인해주세요');
        return;
    }



    // 탭 상태에 따라 보여줄 내용 결정.
    var nowTabs = $("#tabsSelect{{ $result['listName'] ?? '' }}").val();
    var none = true;    

    setStatusOption(nowTabs,td,mc);

    lump_btn_click(lumpcd, btnName);
    afterAjax();

}

function setStatusOption(tab,td,mc)
{
    var status_obj = {
    "Y":{"A":"삭제요청"},
    "D2":{"Y":"최종결재","N":"취소"},
    "N":{},
    };

    var arrConfirmId = @json($arr_confirm_id);
    if(td == "{{ date('Ymd') }}")
    {
        var div = "D";
    }
    else
    {
        var div = "L";
    }
    $('#trade_day_div').val(div);
    status_obj["D1"] = {"Y":"최종결재","N":"취소"};

    $('#lump_trade_date').val(td);
    $('.reset').addClass('d-none');
    $("#confirm_status").empty();
    $("#confirm_status").append("<option value=''>선택</option>");

    for(key in status_obj[tab])
    {
        $("#confirm_status").append("<option value='"+key+"'>"+status_obj[tab][key]+"</option>");
    }

    // 결재자세팅
    if(tab == "Y")
    {
        $("#confirm_id_1").empty();
        $("#confirm_id_1").append("<option value=''>선택</option>");
        $('.a_area').removeClass('d-none');
        for(key in arrConfirmId[div]['confirm_id_1'])
        {
            $("#confirm_id_1").append("<option value='"+key+"'>"+arrConfirmId[div]['confirm_id_1'][key]+"</option>");
        }
    }
    if(tab == "D1")
    {
        $('.b_area').removeClass('d-none');
    }    
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