@extends('layouts.master')
@section('content')


<!-- Main content -->
<section class="content">
<div class="container-fluid">




    <div class="row">
    <div class="col-md-12">

    <div class="row">
        <div class="col-md-12">
            <div class="card card-lightblue">
                <div class="card-header">
                <h3 class="card-title"  style="width:100%">등기부등본 등록</h3>
                </div>

                <form class="form-horizontal" name="code_form" id="code_form">
                  @csrf
                        <div class="form-group row">
                                <div class="col-sm-12">
                                        <div class="card-body" id="codeForm">

                                        </div>
                                </div>
                        </div>
                  <div class="card-footer">
                  @if(!Func::funcCheckPermit("L001"))
                    <button type="button" class="btn btn-sm btn-info float-right ml-2" id="code_btn" onclick="codeAction();">저장</button>
                    <button type="button" class="btn btn-sm btn-danger float-right ml-2" id="code_btn_del" onclick="codeActionDel();">삭제</button>
                    <button type="button" class="btn btn-sm btn-secondary float-right" onclick="setCodeForm('');">입력초기화</button>
                  @endif
                  </div>
                </form>
            </div>
        </div>
    </div>

    <div class="card card-lightblue">
                <div class="card-header">
                  <h3 class="card-title" style="width:100%">
                  등기부등본 리스트
                  </h3>
                </div>
                <form class="form-horizontal" onsubmit="getDataList('regist', 1, '/config/registlist', $('#form_regist').serialize()); return false;" method="post" name="form_regist" id="form_regist">
                  <div class="card-body" id="codeList" style="height: 490px;"></div>
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

setCodeList();
setCodeForm();

function setCodeList()
{
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  $("#codeList").html(loadingString);
  $.post("/config/registlist", {}, function(data) {
      $("#codeList").html(data);
  });
}

function setCodeForm(no)
{

  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });
  $.post("/config/registform", {gubun:'CODE', no:no}, function(data) {
      $("#codeForm").html(data);
      $("#code_btn_del").attr("disabled", ( $("#code_form #mode").val()=="INS" ));
      if($("#code_form #mode").val()=="INS")
      {
        $("#code_btn").text('저장');
      }
      else
      {
        $("#code_btn").text('수정');
      }
  });
}

//서브메뉴 등록,수정
function codeAction()
{
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  var data = $('#code_form')[0];
  var postdata = new FormData(data);

  postdata.append("customFile", $("#customFile")[0].files[0]);

  $.ajax({
		url  : "/config/registaction",
		type : "post",
		data : postdata,
                processData : false,
		contentType : false,
    success : function(result)
    {
      alert(result);
      setCodeList();
      setCodeForm();
		},
    error : function(xhr)
    {
      alert("통신오류입니다. 관리자에게 문의해주세요.");
		}
	});
}

function codeActionDel()
{
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  if( !confirm("선택된 등기부등본 정보를 삭제하시겠습니까?") ) return false;

  var mode    = "DEL";
  var no = document.getElementById("no").value;

  $.post("/config/registaction", {mode:mode, no:no}, function(data) {
    setCodeList();
    setCodeForm();
    alert(data);
  });
}

$(document).on("click","#codeList div table tbody tr", function() {
  $(this).closest('table').find('tr').removeClass('bg-click');
  $(this).addClass('bg-click');
});

function listSearch()
{
  $.ajaxSetup({
      headers: {
      'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
      }
  });

  var data = $('#search_form')[0];
  var postdata = new FormData(data);

  $("#codeList").html(loadingString);

  $.ajax({
		url  : "/config/registlist",
		type : "post",
		data : postdata,
    processData : false,
		contentType : false,
    success : function(result)
    {
      $("#codeList").html(result);
		},
    error : function(xhr)
    {
      alert("통신오류입니다. 관리자에게 문의해주세요.");
		}
  });
}


function filePreview(no) {
        $.ajax({
            url  : '/config/getregistfile',
            type : 'get',
            data : {
                no:no
            },
            success : function(result)
            {
                window.open(result,'popOpen','right=0,top=0,height=950,width=890,scrollbars=yes');
            },
            error : function(result)
            {
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });   
    }

// 파일 못 찾아서 함수 안타고 직접 다운으로 변경 -> 현재 사용안하는 함수
function fileDownload()
{
    let data = document.getElementById('code_form');
    data.action = "/config/downregistfile";
    data.method = 'POST';
    data.submit();
}


</script>
@endsection