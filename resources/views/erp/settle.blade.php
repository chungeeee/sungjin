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

// 엔터막기
function enterClear()
{
    $('input[type="text"]').keydown(function() {
      if (event.keyCode === 13)
      {
        event.preventDefault();
        listRefresh();
      };
    });

    $("input[data-bootstrap-switch]").each(function() {
    $(this).bootstrapSwitch('state', $(this).prop('checked'));
  });
}

// 유효성 체크
function validateChk(mode)
{
  if($('#code').val() == 0)
  {
    alert('코드를 입력해주세요.');
    $('#code').focus();
    return false;
  }
  if($('#manager').val() == 0)
  {
    alert('담당자를 입력해주세요.');
    $('#manager').focus();
    return false;
  }
  if($('#id').val() == 0)
  {
    alert('아이디를 입력해주세요.');
    $('#id').focus();
    return false;
  }
  if(mode == 'INS')
  {
    if($('#passwd').val() == 0)
    {
      alert('패스워드를 입력해주세요.');
      $('#passwd').focus();
      return false;
    }
  }
  if($('#email').val() != 0)
  {
    var reg_email = /^([0-9a-zA-Z_\.-]+)@([0-9a-zA-Z_-]+)(\.[0-9a-zA-Z_-]+){1,2}$/;
    if (!reg_email.test($('#email').val())) 
    {
      alert('이메일 형식이 올바르지 않습니다.');
      return false;
    } 
  }
  if($('#ip').val() != 0)
  {
    var repl_ip = $('#ip').val().replaceAll(' ', '');
    var chk_ip = repl_ip.split(',');

    if( chk_ip ) 
    {
      for (var key in chk_ip) 
      {  
        var reg_ip = /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/g;

        if (!reg_ip.test(chk_ip[key])) 
        {
          alert('아이피 형식이 올바르지 않습니다.');
          return false;
        }
      }
    } 
  }

  return true;
}


// 영어, 숫자입력
function specialCharRemove(obj) 
{
  var val = obj.value;
  var pattern = /[^(a-zA-Z0-9)]/gi;
  if(pattern.test(val)){
      obj.value = val.replace(pattern,"");
  }
}

// 한글입력
function onlyKorean(obj) 
{
  var val = obj.value;
  var pattern = /[^(가-힣ㄱ-ㅎㅏ)]/gi;
  if(pattern.test(val)){
      obj.value = val.replace(pattern,"");
  }
}

// 숫자입력
function onlyNumber(obj) 
{
  var val = obj.value;
  var pattern = /[^0-9]/g;  
  if(pattern.test(val)){
      obj.value = val.replace(pattern,"");
  }
}

// 이율입력 ( '숫자', '.') 
function onlyRatio(obj) 
{
  var val = obj.value;
  var pattern = /[^(0-9_.)]/gi;
  if(pattern.test(val)){
      obj.value = val.replace(pattern,"");
  }
}

function lumpit()
{
    if(!confirm("일괄처리를 진행하시겠습니까?"))
    {
        return false;
    }
    else
    {
        if(checkOneMore() === false)
        {
            alert('체크박스를 선택해주세요');
            return false;
        }

        // 일괄처리할 배열
        var modeArr = [];
        modeArr['change_manager'] = ["branch_id","manage_id"];
        modeArr['downExcel'] = ["trade_detail_excel"];
        modeArr['print'] = ["loan_trade_detail","trade_detail"];
        console.log(modeArr);
        console.log(modeArr[$('#form_{{ $result['listName'] }} [name="mode"]').val()].length);
        return false;

        console.log($('#form_{{ $result['listName'] }}').serialize());
        console.log($('#form_{{ $result['listName'] }}_lump').serialize());

        // 리스트 form_loan input 값 가져오기
        var postdata = $('#form_{{ $result['listName'] }}').serialize();
        // 일괄처리 form_loan_lump 값 가져오기
        postdata = postdata + "&" + $('#form_{{ $result['listName'] }}_lump').serialize();
        var queryParam = $.getQueryParameters(postdata);

        console.log(postdata);
        //파라미터로 받는다.	
        var nm = queryParam['listName'];
        setLoading('start', nm);

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url: "{{ $result['listName'] }}lump",
            type: "POST",
            data: postdata,
            dataType: "json",
            success: function (data) {
                if (data.result == "1") {
                    $("#listError_" + nm).empty();
                    alert("성공!");
                }
                else if (data.result == "0" && data.msg != "") {
                    alert(data.msg);
                    setLoading('stop', nm);
                } else {
                    alert('데이터를 불러오지 못했습니다.');
                    setLoading('stop', nm);
                }

            },
            error: function (xhr) {
                // console.log(xhr.responseText);
                alert("통신오류입니다. 관리자에게 문의해주세요.");
                setLoading('stop', nm);
            }
        });
        
        getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize());
        return false;
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



// 일괄처리 클릭
function settleConfirmLump(lumpcd, btnName)
{
    console.log(lumpcd,btnName);

    if(!settleLumpChk())
    {
      return false;
    }
    // 탭 상태에 따라 보여줄 내용 결정.
    var nowTabs = $("#tabsSelect{{ $result['listName'] ?? '' }}").val();
    var none = true;    

    setStatusOption(nowTabs,cd);

    lump_btn_click(lumpcd, btnName);
    afterAjax();

}

function settleLumpChk()
{
   if(!isCheckboxChecked('listChk[]'))
    {
        alert('선택건이 없습니다. 처리하실 목록을 선택 후 이용해 주세요.');
        return false;
    }


    var old_td = '';
    var td = '';
    var chk = "Y";
    $("input:checkbox[name='listChk[]']:checked").each(function(i){  
        var tNo = $(this).val();
        cd = $('#td_'+tNo).val();
        console.log(cd);
        if(!(cd =="03" || cd =="04"))
        {
            msg = "개인회생신청,신용회복신청일 경우만 일괄처리 가능합니다.";
            chk = "N";
            return false;
        }
        if(old_td && cd != old_td)
        {
            msg = "선택건들의 화해상세구분이 동일해야 합니다. 선택건을 확인해주세요";
            chk = "N";
            return false;
        }
        old_td = cd;
    });
    if(chk == "N")
    {
        alert(msg);
        return false;
    }
    return true;
}

function setStatusOption(tab,td)
{
    var status_obj = {
    "A":{"Y":"결재완료","X":"부결",},
    };

    $('.reset').addClass('d-none');
    $("#confirm_status").empty();
    $("#confirm_status").append("<option value=''>선택</option>");

    for(key in status_obj[tab])
    {
        $("#confirm_status").append("<option value='"+key+"'>"+status_obj[tab][key]+"</option>");
    }

    
     // 결재자세팅
    if(tab == "A")
    {
        $('.a_area').removeClass('d-none');
    }
}



enterClear();

</script>
@endsection