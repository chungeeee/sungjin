@extends('layouts.master')
@section('content')

<!-- Main content -->
<section class="content">
<div class="container-fluid">




    <div class="row">
    <div class="col-md-3">
            <div class="card card-lightblue">
                <div class="card-header">
                <h3 class="card-title" style="width:100%">
                직원정보 리스트
                </h3>
                </div>
                <div class="card-body" style="height: 720px;">

                  <form name="search_form" id="search_form" method="post" onsubmit="setUserList('',''); return false;">
                  <input type="hidden" id="order_colm" name="order_colm" value="">
                  <input type="hidden" id="order_type" name="order_type" value="">
                  <input type="hidden" name="permit_div" id="permit_div" value="A">

                  <div class="card-tools row">
                    <select class="form-control select2 col" id="branch_code" name="branch_code">
                    <option value=''>전체부서</option>
                    {{ Func::printOptionArray($array_branch, 'branch_name', '') }}
                    </select>

                    <div class="input-group input-group-sm col">
                      <input type="text" name="search_string" class="form-control float-right" placeholder="Search">
                      <div class="input-group-append">
                        <button type="button" class="btn btn-default" onclick="setUserList('','');"><i class="fas fa-search"></i></button>
                      </div>
                    </div>
                  </div>

                  </form>
                  
                  <div class="mt-2" id="permitUserList">
                  <div class="text-center pt-5">직원을 검색해주세요.</div>
                  </div>

                </div>
            </div>
          </div>
    <div class="col-md-9">
    <div class="card card-lightblue">
                <div class="card-header">
                <h3 class="card-title" style="width:100%">
                직원별 권한 관리
                </h3>
                </div>
                <div class="pt-2 pb-1 pr-3">
                {{-- @foreach ($array_confirm as $name => $permit)
                <button type="button" class="btn btn-xs btn-default float-right ml-1" onclick="setPermit('{{ $name }}','');"><i class="fas fa-user m-1 text-secondary" size="5px"></i>{{ $name }}</button>
                @endforeach --}}
                </div>

                <div class="card-body pt-1" id="permitBranchForm">
    

                <form class="form-horizontal" name="permit_form" id="permit_form">
                <input type="hidden" name="user_id" id="user_id" value="">
                <input type="hidden" name="permit_string" id="permit_string" value="">

                <div class="row"  style="overflow:scroll;  height:650px;">
                  <table class="table table-sm loan-info-table table-bordered" id="nsfPopTable">
                    <thead>
                      <tr class="text-center">
                        <th class="w-5" rowspan=2>No</th>
                        <th class="w-15" rowspan=2>메뉴명</th>
                        <th class="w-30" colspan=3>업무</th>
                        <th class="w-50" colspan=4>신청 및 승인 단계</th>
                      </tr>
                      <tr class="text-center">
                        <th class="w-20" colspan=2>업무명</th>
                        <th class="w-20">상세구분</th>
                        <th class="w-10">신청</th>
                        <th class="w-10">승인1</th>
                        <th class="w-10">승인2</th>
                        <th class="w-10">승인3</th>
                      </tr>
                    </thead>
                    <tbody>
                    <tr class="text-center">
                      <td>1</td>
                      <td rowspan=5>조건변경결재</td>
                      <td colspan=2>약정일변경</td>
                      <td>약정일변경</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A101" value="A101"><label class="form-check-label pl-1" for="id_A101" >A101</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A201" value="A201"><label class="form-check-label pl-1" for="id_A201" >A201</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A301" value="A301"><label class="form-check-label pl-1" for="id_A301" >A301</span></td>
                      <td>-</td>
                    </tr>
                    <tr class="text-center">
                      <td>2</td>
                      <td rowspan=2 colspan=2>금리변경</td>
                      <td>정상/연체금리 20% 이상</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A102" value="A102"><label class="form-check-label pl-1" for="id_A102" >A102</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A202" value="A202"><label class="form-check-label pl-1" for="id_A202" >A202</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A302" value="A302"><label class="form-check-label pl-1" for="id_A302" >A302</span></td>
                      <td>-</td>
                    </tr>
                    <tr class="text-center">
                      <td>3</td>
                      <td>정상/연체금리 20% 미만</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A103" value="A103"><label class="form-check-label pl-1" for="id_A103" >A103</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A203" value="A203"><label class="form-check-label pl-1" for="id_A203" >A203</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A303" value="A303"><label class="form-check-label pl-1" for="id_A303" >A303</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A403" value="A403"><label class="form-check-label pl-1" for="id_A403" >A403</span></td>
                    </tr>
                    <tr class="text-center">
                      <td>4</td>
                      <td colspan=2>월상환액변경</td>
                      <td>원리금납부금액 변경</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A104" value="A104"><label class="form-check-label pl-1" for="id_A104" >A104</span></td>
                      <td>-</td>
                      <td>-</td>
                      <td>-</td>
                    </tr>
                    <tr class="text-center">
                      <td>5</td>
                      <td colspan=2>상환일변경</td>
                      <td>-</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A105" value="A105"><label class="form-check-label pl-1" for="id_A105" >A105</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A205" value="A205"><label class="form-check-label pl-1" for="id_A205" >A205</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A305" value="A305"><label class="form-check-label pl-1" for="id_A305" >A305</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A405" value="A405"><label class="form-check-label pl-1" for="id_A405" >A405</span></td>
                    </tr>
                    <tr class="text-center">
                      <td>6</td>
                      <td>기한연장</td>
                      <td colspan=2>기한연장</td>
                      <td>승인/부결</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A121" value="A121"><label class="form-check-label pl-1" for="id_A121" >A121</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A221" value="A221"><label class="form-check-label pl-1" for="id_A221" >A221</span></td>
                      <td>-</td>
                      <td>-</td>
                    </tr>   
                    <tr class="text-center">
                      <td>7</td>
                      <td>무이자결재</td>
                      <td colspan=2>무이자기간부여</td>
                      <td>-</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A122" value="A122"><label class="form-check-label pl-1" for="id_A122" >A122</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A222" value="A222"><label class="form-check-label pl-1" for="id_A222" >A222</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A322" value="A322"><label class="form-check-label pl-1" for="id_A322" >A322</span></td>
                      <td>-</td>
                    </tr>                
                    <tr class="text-center">
                      <td>8</td>
                      <td>대출계약철회 결재</td>
                      <td colspan=2>대출계약철회</td>
                      <td>-</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A123" value="A123"><label class="form-check-label pl-1" for="id_A123" >A123</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A223" value="A223"><label class="form-check-label pl-1" for="id_A223" >A223</span></td>
                      <td>-</td>
                      <td>-</td>
                    </tr>    
                    <tr class="text-center">
                      <td>9</td>
                      <td rowspan=3>입금리스트</td>
                      <td rowspan=2 colspan=2>삭제처리</td>
                      <td>당일 거래</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A130" value="A130"><label class="form-check-label pl-1" for="id_A130" >A130</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A230" value="A230"><label class="form-check-label pl-1" for="id_A230" >A230</span></td>
                      <td>-</td>
                      <td>-</td>
                    </tr>                    
                    <tr class="text-center">
                      <td>10</td>
                      <td>당일 이전 거래</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A131" value="A131"><label class="form-check-label pl-1" for="id_A131" >A131</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A231" value="A231"><label class="form-check-label pl-1" for="id_A231" >A231</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A331" value="A331"><label class="form-check-label pl-1" for="id_A331" >A331</span></td>
                      <td>-</td>
                    </tr>                    
                    <tr class="text-center">
                      <td>11</td>
                      <td colspan=2>입금등록</td>
                      <td>-</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A132" value="A132"><label class="form-check-label pl-1" for="id_A132" >A132</span></td>
                      <td>-</td>
                      <td>-</td>
                      <td>-</td>
                    </tr>
                    <tr class="text-center">
                      <td>12</td>
                      <td rowspan=2>출금리스트</td>
                      <td colspan=2>출금등록</td>
                      <td>-</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A140" value="A140"><label class="form-check-label pl-1" for="id_A140" >A140</span></td>
                      <td>-</td>
                      <td>-</td>
                      <td>-</td>
                    </tr>
                    <tr class="text-center">
                      <td>13</td>
                      <td colspan=2>가수반환</td>
                      <td>송금(가수금)</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A142" value="A142"><label class="form-check-label pl-1" for="id_A142" >A142</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A242" value="A242"><label class="form-check-label pl-1" for="id_A242" >A242</span></td>
                      <td>-</td>
                      <td>-</td>
                    </tr>                               
                    <tr class="text-center">
                      <td>14</td>
                      <td rowspan=2>불명금리스트</td>
                      <td colspan=2>미처리입금등록</td>
                      <td>-</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A143" value="A143"><label class="form-check-label pl-1" for="id_A143" >A143</span></td>
                      <td>-</td>
                      <td>-</td>
                      <td>-</td>
                    </tr>                               
                    <tr class="text-center">
                      <td>15</td>
                      <td></td>
                      <td colspan=2>삭제/정리</td>
                      <td>-</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A144" value="A144"><label class="form-check-label pl-1" for="id_A144" >A144</span></td>
                      <td>-</td>
                      <td>-</td>
                      <td>-</td>
                    </tr>
                    <tr class="text-center">
                      <td>16</td>
                      <td rowspan=5>기타</td>
                      <td rowspan=2 colspan=2>담보설정해지</td>
                      <td>완제 건 설정 해지 (단건 or 일괄)</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A145" value="A145"><label class="form-check-label pl-1" for="id_A145" >A145</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A245" value="A245"><label class="form-check-label pl-1" for="id_A245" >A245</span></td>
                      <td>-</td>
                      <td>-</td>
                    </tr>                                      
                    <tr class="text-center">
                      <td>17</td>
                      <td>완제 이전 설정 해지</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A146" value="A146"><label class="form-check-label pl-1" for="id_A146" >A146</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A246" value="A246"><label class="form-check-label pl-1" for="id_A246" >A246</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A346" value="A346"><label class="form-check-label pl-1" for="id_A346" >A346</span></td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A446" value="A446"><label class="form-check-label pl-1" for="id_A446" >A446</span></td>
                    </tr>
                    <tr class="text-center">
                      <td>18</td>
                      <td colspan=2>개명</td>
                      <td>-</td>
                      <td class="reset"><input type="checkbox" class="icheckbox_square-blue-sm" name=permits[] id="id_A149" value="A149"><label class="form-check-label pl-1" for="id_A149" >A149</span></td>
                      <td>-</td>
                      <td>-</td>
                      <td>-</td>
                    </tr>
                    </tbody>
                  </table>
                </div>
                </form>

                </div>
                <form class="form-horizontal">
                <div class="card-footer">
                <button type="button" class="btn btn-sm btn-info float-right ml-2" id="user_btn" onclick="permitAction();">저장</button>
                <button type="button" class="btn btn-sm btn-secondary float-right ml-2" id="user_btn_all" onclick="setPermit('ALL');">전체선택</button>
                <button type="button" class="btn btn-sm btn-secondary float-right ml-2" id="user_btn_non" onclick="setPermit('');">전체해제</button>
                </div>
                </form>
            </div>
          </div>
    </div>    

</div>
</section>
<!-- /.content -->

@endsection





@section('javascript')
<script>

// 체크박스 모양 
$('.reset').iCheck({
    checkboxClass: 'icheckbox_square-blue',
});

function checkPcd( pcd, ops )
{
  $(".pcd-"+pcd).each(function() {
    if( $(this).prop("disabled")==false )
    {
      $(this).prop("checked", ops)
    }
  });
}

function setUserList(oc, ot)
{

  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  $("#order_colm").val(oc);
  $("#order_type").val(ot);
  $("#permitUserList").html(loadingString);

  var postdata = $('#search_form').serialize();
  
  $.ajax({
		url  : "/config/permituserlist",
		type : "post",
		data : postdata,
    success : function(result)
    {
      $("#permitUserList").html(result);
		},
    error : function(xhr)
    {
      $("#permitUserList").html("통신오류입니다. 관리자에게 문의해주세요.");
		}
  });
  
}


function setPermitUserForm(id, cd)
{
  $("#permit_form #user_id").val(id);

  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  $("#user_btn").html(loadingStringtxt);
  $.post("/config/getconfirmpermit", {id:id,cd:cd}, function(data) {
    $("#user_btn").html('저장');
    setPermit('',data);
  });
}

// 선택된 직원의 승인권한 세팅
function setPermit(div,menus)
{
  $('.icheckbox_square-blue-sm').iCheck('uncheck');

  var array_confirm = @json($array_confirm);

  // 전체선택
  if( div=="ALL" )
  {
    $.each(array_confirm, function (key, arr) {
        $('#id_'+arr).iCheck('check');
      });
  }
  else if(div)
  {
    array_confirm[div].map(function(n) { 
      $('#id_'+n).iCheck('check');
    });
  }
  // 해당 직원의 승인권한
  else if(menus)
  {
    menus.split(",").map(function(n) { 
      if(n.indexOf('A') != -1) // 앞에 'A' 붙은게 승인권한 관련된것임
      {
        $('#id_'+n).iCheck('check');
      }
    });
  } 
}






//최상위메뉴 등록,수정
function permitAction()
{
  if( $("#permit_form #user_id").val()=="" )
  {
    alert("직원을 선택해주세요.");
    return false;
  }
  var i = $('input[name="permits[]"]:checked').length;
  if((i<=0 || !i) && !confirm("승인권한을 모두 해제하시겠습니까?"))
  {
    return;
  }

  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  var postdata = $('#permit_form').serialize();

  $.ajax({
		url  : "/config/confirmpermitaction",
		type : "post",
		data : postdata,
    success : function(result)
    {
      alert(result.msg);
      if(result.permit_string)
      {
        setPermit('',result.permit_string);
      }
		},
    error : function(xhr)
    {
      alert("통신오류입니다. 관리자에게 문의해주세요.");
		}
	});
  
}





$(document).on("click","#permitUserList div table tbody tr", function() {
  $(this).closest('table').find('tr').removeClass('bg-click');
  $(this).addClass('bg-click');
});
</script>


@endsection