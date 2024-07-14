@extends('layouts.masterPop')
@section('content')
<style>

/*///////////////////////////////////////////*/
/* 왼쪽 상단 메뉴 */
#lon_menu_left_top {
	position: fixed;
	top: 0;
	left: 0;
	right: 380px;
	width:100%;
	height:42px;
	overflow: hidden;
	background: #dbdbdb;
}

/* 오른쪽 상단 메뉴 */
#lon_menu_right_top {
	position: absolute;
	top: 0;
	right: 0;
	width: 380px;
	overflow: hidden;
	background: #dbdbdb;
}

/* 왼쪽 상단 컨텐츠 */
#lon_con_left_top {
	position: absolute;
	top: 41px;
    left: 0;
	bottom: 301px;
	right: 380px;
	overflow: auto;
	background: #ECF0F5;
}

/* 오른쪽 상단 컨텐츠 */
#lon_con_right_top {
	position: absolute;
	top: 41px;
	right: 0;
	bottom: 301px;
	width: 380px;
	overflow: auto;
	background: #ECF0F5;
}

/* 왼쪽 하단 메뉴 */
#lon_menu_left_bottom {
	position: absolute;
	bottom: 301px;
	right: 0px;
	left: 0;
	height: 41px;
	overflow: hidden;
	background: #dbdbdb;
}

/* 오른쪽 하단 메뉴 */
#lon_menu_right_bottom {
	position: absolute;
	right: 0;
	bottom: 301px;
	width: 380px;
	height: 41px;
	overflow: hidden;
	background: #dbdbdb;
}

/* 오른쪽 하단 컨텐츠 */
#lon_con_right_bottom {
	position: absolute;
	right: 0;
	bottom: 0;
	width: 380px;
	height: 301px;
	overflow: auto;
	background: #ECF0F5;
}

/* 왼쪽 하단 컨텐츠 */
#lon_con_left_bottom {
	position: absolute;
	bottom: 0;
	right: 0px;
	left: 0;
	height: 301px;
	overflow: auto;
	background: #ECF0F5;
}

.innertube {
	margin: 1px;
}

/* 고정메뉴 */
#fixedMenu {
    max-width: 500px;
    position: fixed;
    top: 0px;
    right: 0px;
}

/* 회원정보 끝 */
</style>

<div class="content-wrapper">
<input type="hidden" id="globalAction" value="{{ $result['action'] }}">
<input type="hidden" id="globalTno" value="{{ $result['tno'] }}">
<input type="hidden" id="globalCno" value="{{ $result['contractNo'] or '' }}">
    <!-- 왼쪽 상단 메뉴 --------->
    <nav id="lon_menu_left_top">
        <div class="row">
            <div style='margin-left:15px; margin-top:5px;'>
                <ul class="nav nav-pills">
                    <li class="nav-item" id='userInfo'><a class='nav-link active' href="#" onClick='getInfo("/config/userinfo", "conLeftTop")' data-toggle="tab">직원정보</a></li>
                    <li class="nav-item" id='loanappinfo'><a class='nav-link' href="#" onClick='getInfo("/config/userinfo", "conLeftTop")' data-toggle="tab">직원정보</a></li>
                    <li class="nav-item" id='loanappinfo'><a class='nav-link' href="#" onClick='getInfo("/config/userinfo", "conLeftTop")' data-toggle="tab">직원정보</a></li>
                </ul>
            </div>
        </div>
	</nav>

    <!-- 오른쪽 상단 메뉴 --------->
	<nav id="lon_menu_right_top">
        <div class="row">
            <div style='margin-left:15px; margin-top:5px;'>
                <ul class="nav nav-pills">
                    <li class="nav-item" id='lonappmemo'><a class='nav-link active' href="#" onClick='getInfo("/config/usermsg", "conRightTop")' data-toggle="tab">관리메모</a></li>
                    <li class="nav-item" id='lonappmemo'><a class='nav-link' href="#" onClick='getInfo("/config/usermsg", "conRightTop")' data-toggle="tab">관리메모</a></li>
                    <li class="nav-item" id='lonappmemo'><a class='nav-link' href="#" onClick='getInfo("/config/usermsg", "conRightTop")' data-toggle="tab">관리메모</a></li>
                    <li class="nav-item" id='lonappmemo'></li>
                </ul>
            </div>
        </div>
	</nav>

	<nav id="lon_con_left_top">
		<div class="innertube" id='conLeftTop'>
		

		</div>
	</nav>

	<nav id="lon_con_right_top">
		<div class="innertube" id='conRightTop'>

		</div>
	</nav>

	{{-- <nav id="lon_menu_left_bottom" >
        <div class="row" >
            <div style='margin-left:15px; margin-top:5px;'>
                <ul class="nav nav-pills">
                    <li id='lonappmemoinput'><a class='nav-link active' href="#" onClick='getInfo("/config/usermsginput", "conLeftBottom")' data-toggle="tab">관리메모입력</a></li>
                    <li id='loanemailinput'><a class='nav-link' href="#" onClick='getInfo("/config/usermsginput", "conLeftBottom")' data-toggle="tab">Email발송</a></li>
                </ul>
            </div>
        </div>
	</nav> --}}

	<nav id="lon_con_left_bottom" class="border-top">
		<div class="innertube" id='conLeftBottom'>

		</div>
	</nav>


	{{-- <nav id="lon_menu_right_bottom" >
        <div class="row" >
            <div style='margin-left:15px; margin-top:5px;'>
                <ul class="nav nav-pills">
                    <li id='lonappmemoinput'><a class='nav-link active' href="#" onClick='getInfo("/config/usermsginput", "conRightBottom")' data-toggle="tab">관리메모입력</a></li>
                    <li id='loanemailinput'><a class='nav-link' href="#" onClick='getInfo("/config/usermsginput", "conRightBottom")' data-toggle="tab">Email발송</a></li>
                </ul>
            </div>
        </div>
	</nav> --}}

	{{-- <nav id="lon_con_right_bottom">
		<div class="innertube" id='conRightBottom'>

		</div>
	</nav> --}}

</div>

<div id="fixedMenu">
    <div class="card card-primary collapsed-card" id="fixedCard">
        <div class="card-header pl-0 pr-0 rounded-0" style="height: 40px;">
            <div class="card-tool">
                <div class="nav nav-tabs border-0 pt-1" id="fixedTabs">
                    <button class="btn btn-tool" data-default='Y' data-toggle="tab" href="#memo" role="tab" aria-controls="memo" aria-selected="false" onclick="clickFixedTabs(this);">
                        <i class="fas fa-pencil-alt"></i>
                    </button>
                    <button class="btn btn-tool" data-toggle="tab" href="#sms" role="tab" aria-controls="sms" aria-selected="false" onclick="clickFixedTabs(this);">
                        <i class="fas fa-sms"></i>
                    </button>
                    <button class="btn btn-tool" data-toggle="tab" href="#emailDiv" role="tab" aria-controls="emailDiv" aria-selected="false" onclick="clickFixedTabs(this);">
                        <i class="fas fa-envelope"></i>
                    </button>
                    {{-- <button class="btn btn-tool pr-1" data-card-widget="maximize" data-animation-speed="500" onclick="toggleMaximize();">
                        <i class="fas fa-expand"></i>
                    </button> --}}
                </div>
            </div>
        </div>
        <div class="card-body" id="fixedCardBody">
            <div class="tab-content">
                <div class="tab-pane fade show active" id="memo" role="tabpanel" aria-labelledby="memo-tab">
                    메모작성
                </div>
                <div class="tab-pane fade" id="sms" role="tabpanel" aria-labelledby="sms-tab">
                    sms보내기
                </div>
                <div class="tab-pane fade" id="emailDiv" role="tabpanel" aria-labelledby="emailDiv-tab">
                    email작성
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('javascript')
<script language='javascript'>
function toggleMaximize() {
    if ($("#fixedCard").hasClass('collapsed-card')) {
        if ($('button[data-default=Y]').hasClass('text-dark')) {
            $('button[data-default=Y]').removeClass('text-dark');
        } else {
            $('button[data-default=Y]').addClass('text-dark');
        }
    }
}

function clickFixedTabs(el) {

    $('#fixedTabs button').removeClass('text-dark');
    $(el).addClass('text-dark');
    if ($(el).attr('aria-selected') == 'true') {
        if ($("#fixedCard").hasClass('collapsed-card')) {
            // open
            $("#fixedCardBody").CardWidget('expand');
            $(el).addClass('text-dark');
        } else {
            // close
            if (!$("#fixedCard").hasClass('maximized-card')) {
                $('#fixedTabs button').removeClass('text-dark');
                $("#fixedCardBody").CardWidget('collapse');
            }       
        }
    } else {
        if ($("#fixedCard").hasClass('collapsed-card')) {
            // First open
            $("#fixedCardBody").CardWidget('expand');
        }
    }
}

// get으로 불러오는 페이지 공통함수
function getPage(url, id)
{
	$("#"+id).html(loadingString);

	$.ajaxSetup({
		headers: {
		'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});

	$.ajax({
		url: url,
		type:"GET",
		data: { },
		success : function(data){
			setTimeout(function(e) {
				$("#"+id).html(data);

				// selectpicker
				//$('.selectpicker').selectpicker({
				//    style: 'btn-info'
				//});
			}, 500
			);

		},
		error : function(xhr) {
			console.log(xhr.responseText);
			$("#"+id).html('데이터를 불러오지 못했습니다. 관리자에게 문의해 주세요');
		}
	});
}

function getInfo(url, id)
{
	// 계약정보 불러오기
	//if(url == "/erp/contractinfo")
	//	url = url + '/{{ $result['contractNo'] }}';
	//else if(url == "/config/usermsginput" || url == "/config/usermsg")
	//	url = url + '/{{ $result['tno'] }}/{{ $result['contractNo'] }}';
	//else

	url = url + '/{{ $result['tno'] }}';

	console.log(url);


	if(id=='popup')
		window.open(url, "", "top=0,left=0,height=800,width=1500,scrollbars=no");
	else
        getPage(url, id);
}

// 로딩시 action 별로 페이지를 불러온다.
$(document).ready(function() {

	/////////////////////////////////////////////////////
    // 왼쪽 상단 : 관리메모
    getInfo("/config/userinfo", "conLeftTop");
	/////////////////////////////////////////////////////
    // 오른쪽 상단 : 관리메모
    getInfo("/config/usermsg", "conRightTop");
	
	/////////////////////////////////////////////////////
	// 오른쪽 하단 : 관리메모입력
    // getInfo("/config/usermsginput", "conRightBottom");
	/////////////////////////////////////////////////////
	// 왼쪽 하단 : 
    getInfo("/config/usermsginput", "conLeftBottom");
});

// 액션처리후에 프레임 다시가져오기
function getFrame(id, url)
{
	if(url=="")
	{
		var url = $("#"+id).attr("action");
	}

	console.log(id);
	console.log( 'form value : ' + $('#'+id).serialize() );

    // var action = $("#"+id).attr("action");
	var method = $("#"+id).attr("method");
	console.log(method);
	var postdata = $('#'+id).serialize();
	if(ccCheck()) return;
	$.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

	$.ajax({
		url: url,
		type: 'post',
		data: postdata,
		success : function(result){
            if(result["code"] == "0")
            {
				// 정상 처리시 넘어오는 값
//				result["code"] = "0";						// 0일경우 성공
//				result["message"] = "OK";					// 에러일경우 에러메시지
//				result["url"] = "/loan/loanappinfo";	// 이동할 URL
//				result["active"] = "loanappinfo";			// 선택된 메뉴 ID
//				result["frame"] = "conLeftTop";				// 이동할 프레임

				globalCheck = false;
                alert("정상적으로 처리되었습니다.");

                // $('#'+result["active"]).addClass('active');

				$("#modal01").modal("hide");
				setTimeout(function(e){
	                getInfo(result["url"], result["frame"]);
				}, 500);
				
				// 메모입력시
				// if(result["active"] == "lonappmemoinput")
				//{
				//	$('#lonappmemo').addClass('active');
				//    getInfo("/config/usermsg", "conRightTop");
				//}
            }
            else
            {
				globalCheck = false;
                alert(result["message"]);
            }
		},
		error : function(xhr) {
			//console.log(xhr.responseText);
			globalCheck = false;
			alert("통신오류입니다. 관리자에게 문의해주세요.");
		}
	});

}
{{-- @if( config('app.mode')==='dev' )
	document.title = "[개발] {{ $result['member_name'] or ''}} 선정산신청정보";
@else
	document.title = "{{ $result['member_name'] or ''}} 선정산신청정보";
@endif --}}

window.moveTo( 0, -300 );
window.resizeTo( 1600, 950);
//	window.resizeTo( screen.availWidth, screen.availHeight );

@if(session('status') == "Y")
    alert("수정이 완료되었습니다.");
@endif

</script>

@endsection