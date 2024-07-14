@extends('layouts.masterPop')
@section('content')

{{-- Action 처리 성공 여부 alert --}}
@if (Session::has('result'))
    <script> alert('{{Session::get("result")}}'); </script>
@endif

<!-- 화면 분할 (split.css) -->
<link rel="stylesheet" href="/css/split.css">

<style>

/* 상단고정 */
#fixed_top {    
  position: fixed;
  width: calc(75% - 10px);
  height: 38px;
  z-index:101;
  margin-left:0px;
  margin-bottom:0px;
  padding-left:0px;
}

/* 컨텐츠 높이 조정 */
#splitParent {
    top: 42.5px;
    z-index:100;
}

.pagination {
    margin-bottom:0px;
}
    .content::-webkit-scrollbar{
        width: 8px;
        height: 10px;
    }
    .content::-webkit-scrollbar-button {
        width: 8px;
    }
    .content::-webkit-scrollbar-thumb {
        background: #999;
        border: thin solid gray;
        border-radius: 10px;
    }
    .content::-webkit-scrollbar-track {
        background: #eee;
        border: thin solid lightgray;
        box-shadow: 0px 0px 3px #dfdfdf inset;
        border-radius: 10px;
    }


.user-nav-link {
  display: block;
  padding: 0.3rem 0.2rem;
  border: 1px solid transparent;
  border-top-left-radius: 0.25rem;
  border-bottom-left-radius: 0.25rem;
}
</style>


<!-- 메뉴 컬러 세팅  -->
<input type="hidden" name="status_color" id="status_color" value="{{ $array_contracts_status[$no] ?? '' }}">
<input type="hidden" name="cust_select" id="cust_select" value="">

<div class="row m-0">
    <!-- 왼쪽패널 -->
    <div class="col-md-9 p-1 pr-2">
        <!-- 고객요약정보 -->
        <div class="card status-border " id="fixed_top">
            <div class="card-body p-1 pb-0">
                <table class="table table-sm table-borderless m-0">
                    <col width="7%" style="background-color:#E7EAED;"></col>
                    <col width="11%"></col>
                    <col width="7%" style="background-color:#E7EAED;"></col>
                    <col width="11%"></col>
                    <col width="7%" style="background-color:#E7EAED;"></col>
                    <col width="11%"></col>
                    {{-- <col width="6%"></col> --}}

                    <col width="7%" style="background-color:#E7EAED;"></col>
                    <col width="11%"></col>
                    <col width="7%"></col>
                    <col width="11%"></col>
                    <col width="2%"></col>
                    <tbody>
                        <tr class="text-center">
                            <td>채권번호</td>
                            <td>{{ Func::addCi() }}{{ $array_customer['investor_no_inv_seq'] }}</td>
                            <td>차입자명</td>
                            <td>{{ $array_customer['name'] }}</td>
                            <td>주민번호</td>
                            <td>{{ $array_customer['ssn'] }}</td>
                            {{-- <td style="padding-top:2px;">
                                <button class="btn btn-xs btn-info mt-0" type="button" onClick="window.open('/erp/custloanappinfo?cust_info_no={{ $cust_info_no }}','','left=0,top=0,width=800,height=800')">대출결재정보</button> 
                            </td> --}}
                            <td>계약</td>
                            <td>유효{{ $array_customer['yu_cont_cnt'] }}건 / 총{{ $array_customer['tot_cont_cnt'] }}건</td>
                            {{-- <td>상품이용권유</td>
                            <td role="button" onclick="viewAgree('{{ $array_customer['cust_info_no'] }}');">
                                <div class="col-md-12 row text-center">
                                {!! ( isset($array_customer['agree']['agree_yn07']) && $array_customer['agree']['agree_yn07']=="Y" ) ? '<div class="col-md-3 text-center p-0">SMS</div>' : '' !!}
                                {!! ( isset($array_customer['agree']['agree_yn08']) && $array_customer['agree']['agree_yn08']=="Y" ) ? '<div class="col-md-3 text-center p-0">이메일</div>' : '' !!}
                                {!! ( isset($array_customer['agree']['agree_yn09']) && $array_customer['agree']['agree_yn09']=="Y" ) ? '<div class="col-md-3 text-center p-0">전화</div>' : '' !!}
                                {!! ( isset($array_customer['agree']['agree_yn10']) && $array_customer['agree']['agree_yn10']=="Y" ) ? '<div class="col-md-3 text-center p-0">DM</div>' : '' !!}

                                @if( Func::nvl($array_customer['agree']['agree_yn07'])!="Y" && Func::nvl($array_customer['agree']['agree_yn08'])!="Y" && Func::nvl($array_customer['agree']['agree_yn09'])!="Y" && Func::nvl($array_customer['agree']['agree_yn10'])!="Y" )
                                <div class="col-md-12 text-center text-danger font-weight-bold p-0">상품권유 비동의 고객</div>
                                @endif
                                </div>
                            </td> --}}
                            <td></td>
                            <td></td>
                            <td>                                
                                <span onClick="linkPopover()" id="linkPop" class="hand" title="자주가는 사이트"><i class="fas fa-link text-blue"></i></span>
                                <div id='linkPopHtml' class='collapse'>
                                    <div class="popover-con">
                                        @if( isset($linkBasic) )
                                        {{-- <h6 class="popover-con">기본조회</h6> --}}
                                        <div class="row popover-con ">
                                            @foreach($linkBasic as $ids=>$info)
                                            <div class="btn btn-outline-dark btn-block btn-xs col-md-2 m-0 mr-1 mb-2 popover-con" id="link{{ $ids }}"><i class="fa fa-{{ $info[0] }} mr-1"></i>{{ $ids }}</div>
                                            @endforeach
                                        </div>
                                        @endif

                                        <!-- 넓이 조정 -->
                                        &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
                                        &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
                                        &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
                                        &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
                                        &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
                                        &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; 
                                    </div>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- 고객정보 & 계약정보 -->
        <div class="col-md-12 p-0 split split-horizontal mb-1" id="splitParent" style="max-height:936px;">

            <!-- 고객상세정보 -->
            <div class="col-md-12 row p-0 m-0 split content" id="splitContentA" style="overflow-y: auto;">
                <div class="col-1 col-sm-1 p-0">
                    <div class="nav flex-column nav-tabs h-100 status-border-right" id="cust-tab" role="tablist" aria-orientation="vertical" >
                        
                        {!! setLeftMenu('차입자정보', 'info') !!}

                        {{-- {!! setLeftMenu('이미지', 'image') !!} --}}

                        {{-- {!! setLeftMenu('녹취', 'wav') !!} --}}

                        {!! setLeftMenu('변경내역', 'change') !!}
                        
                    </div>
                </div>
                <div class="col-11 col-sm-11 p-0 pb-3 status-border-left-none" style='border-top-right-radius:4px; border-bottom-right-radius:4px;' >
                    <div class="tab-content" id="customer-tabContent" style="overflow:none;">
                        <!--  상단 고객데이터 출력 영역 -->
                        <div class="tab-pane text-left fade show active p-2" id="customer-contents" role="tabpanel"></div>
                    </div>
                </div>
            </div>

            <!-- 계약상세정보 -->
			<div class="gutter gutter-vertical" style="height: 6px"></div>
            <div class="col-md-12 row p-0 m-0 mt-0 split content" id="splitContentB" style="overflow-y: auto;">
                <div class="col-1 col-sm-1 p-0">
                    <div class="nav flex-column nav-tabs h-100 status-border-right" id="cont-detail-nav" role="tablist" aria-orientation="vertical">
                        <a class="nav-link mb-1 text-bold active" id="cont-nav-{{ $no }}-tab" data-toggle="pill" role="tab" onclick="getContData('{{ $no }}')" 
                        style="color:#FFFFFF; border:2px solid {{ $array_contracts_status[$no] }};">{{ $no }} 계약</a>
                    </div>
                </div>
                <div class="col-11 col-sm-11 p-0 status-border-left-none" style='border-top-right-radius:4px; border-bottom-right-radius:4px;'>
                    <div class="tab-content" id="cont-detail-navContent">
                        <!--  하단 계약데이터 출력 영역 -->
                        <div class="tab-pane text-left fade show active" id="contract-contents" role="tabpanel" aria-labelledby="cont-nav-content-tab">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- 오른쪽패널 -->
    <div class="col-md-3 pl-1 pt-1 m-0">
        <div class="card card-custnav card-outline card-outline-tabs mb-0" style="box-shadow:none; height: 978px;">
            <div class="card-header p-0">
                <div class="row p-0 m-0">
                <ul class="col-md-5 nav nav-tabs" id="right-menu" role="tablist" style="border-bottom:none;">
                    <li class="nav-item">
                        <a class="nav-link-p0 active" id="right-menu-memo-tab" style="border-bottom:none;" data-toggle="pill" href="#right-menu-tab" role="tab" aria-controls="right-menu-tab" aria-selected="true" onclick="getRightList('memo');">메모</a>
                    </li>
                    {{-- <li class="nav-item">
                        <a class="nav-link-p0" id="right-menu-sms-tab" style="border-bottom:none;" data-toggle="pill" href="#right-menu-tab" role="tab" aria-controls="right-menu-tab" aria-selected="false" onclick="getRightList('sms');">문자</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link-p0" id="right-menu-post-tab" style="border-bottom:none;" data-toggle="pill" href="#right-menu-tab" role="tab" aria-controls="right-menu-tab" aria-selected="false" onclick="getRightList('post');">우편</a>
                    </li> --}}
                </ul>
                <div class="row col-md-7 p-0 pt-1 m-0" style="border-bottom:none;">

                    <table width="100%">
                    <form class="form-inline ml-0" onsubmit="window.open('/erp/search/'+$('#search_string').val(),'custsearch','left=0,top=0,width=1350,height=800,scrollbars=yes'); return false;">
                    <tr>
                        <td align="right">
                            <input class="form-control form-control-navbar" type="search" placeholder="투자자 검색" aria-label="Search" name="search_string"  id="search_string" style="width:100%">
                        </td>
                        <td width=20>
                            <button class="btn btn-navbar btn-xs" type="submit">
                            <i class="fas fa-search"></i>
                            </button>
                        </td>
                        <td width=100>

                    @if( $condition!="" )
                    
                    {{-- @if( isset($qik_btn['CNT']) && isset($qik_btn['TOTAL']) )
                        {{ $qik_btn['CNT']+1 }}/{{ $qik_btn['TOTAL'] }}
                    @endif --}}

                    @if( isset($qik_btn['PREV']) )
                    <button class="btn btn-xs bg-lightblue" onclick="location.href='/erp/custpop?cust_info_no={{ $qik_btn['PREV']->cust_info_no }}&no={{ $qik_btn['PREV']->no }}&condition={{ $condition }}&cnt={{ $qik_btn['PREV']->rrr }}&total={{ $qik_btn['TOTAL'] ?? '' }}'"><i class="fas fa-angle-double-left"></i> 이전</button>
                    @else
                    <button class="btn btn-xs bg-lightblue" disabled><i class="fas fa-angle-double-left"></i> 이전</button>
                    @endif
                    

                    @if( isset($qik_btn['NEXT']) )
                    <button class="btn btn-xs bg-lightblue" onclick="location.href='/erp/custpop?cust_info_no={{ $qik_btn['NEXT']->cust_info_no }}&no={{ $qik_btn['NEXT']->no }}&condition={{ $condition }}&cnt={{ $qik_btn['NEXT']->rrr }}&total={{ $qik_btn['TOTAL'] ?? '' }}'">다음 <i class="fas fa-angle-double-right"></i></button>
                    @else
                    <button class="btn btn-xs bg-lightblue" disabled>다음 <i class="fas fa-angle-double-right"></i></button>
                    @endif

                    

                    @else 

                    @endif

                    </td></tr>

                    </form>
                    </table>
                </div>
            </div>
            <div class="card-body p-1 status-border split m-0 content" id="rightList"  style="overflow-y:auto; max-height:931px;">
                <div class="tab-content" id="right-menu-tabContent" style='' >
                    <div class="tab-pane fade show active p-0" id="right-menu-tab" role="tabpanel" aria-labelledby="right-menu-tab"></div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 신용정보 모달 --}}
<div class="modal" tabindex="-1" id="creditModal">
    <div class="modal-dialog modal-dialog-scrollable" style="max-width: 90%;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">신용정보</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="creditModalBody">


            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">닫기</button>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="ctcModal" aria-modal="true" role="dialog"></div>
@endsection




@section('javascript')
<!-- 화면 분할 (split.js) -->
<script type="text/javascript" src="/js/split.js"></script>
<script>

    // 화면분할
	function displaySplit(topHeight, bottomHeight) {
		$(".gutter").remove();
		Split(['#splitContentA', '#splitContentB'], {
			direction: 'vertical',
			sizes: [topHeight, bottomHeight],
			gutterSize: 6,
			cursor: 'row-resize',
		})
	}

	displaySplit(41, 59);

    function listSizeSet(mode)
    {
        if( mode == "A" )
        {
            $('#rightList').css('max-height', '570px');
        }
        else
        {
            $('#rightList').css('max-height', '978px');
        }
    }

    // 새창으로 메뉴를 띄운다.
    function getCustDataNew(menuTitle, opentab)
    {
        var url = "/erp/custpopnew?zone=cust&cust_info_no={{ $cust_info_no }}&no={{ $no }}&opentab=" + opentab + "&menutitle=" + menuTitle;
        window.open(url, '', 'left=0, top=0, height=1000,width=1200, fullscreen=yes' );
    }

    // 고객정보
    /*
        view : 화면 이름
        div  : 변경내역에서 사용 (구분값)
        no   : 각각 화면에서 사용하는 no값
    */
    function getCustData(view, selected, no, nn, tab='', ino=0)
    {
        // CORS 방지
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        // 현재 선택된 메뉴가 무엇인지 저장
        $("#cust_select").val(view);
        var nowV = $("#cust_select").val(view);
        // console.log(view);

        // 전체 메뉴 색 입히고, 선택항목만 흰 배경으로 변경
        resetMenuColor();
        selectMenuColor('cust', view);

        if(view=="irl" || view=="ccrs")
        {
            selected = view;
            view = "relief";
        }

        // 고객정보 받아오기
        if(view == 'info'){
            var url = "/erp/cust"+view;
        }
        else {
            var url = "/erp/customer"+view;
        }
        var ci_no = "{{ $cust_info_no }}";
        var li_no = currLoanNo;             // 실제로 지금 선택된 탭

        $("#customer-contents").html(loadingString);
        $.post(url, { mode:view, cust_info_no:ci_no,loan_info_no:li_no,selected:selected,no:no }, function(data) {
            $("#customer-contents").html(data);
            afterAjax();

            if( view=='law' && nn>0 )
            {
                setLawInfo(nn,li_no,tab,ino);
            }
        });
    }



    // 현재 계약별 메뉴
    var currLoanTabFunc = "";
    var currLoanNo      = "{{ $no }}";
     
    // 계약정보
    function getContData(cno, opt)
    {      
        // 예외처리
        if(cno=='')
        {
            return false;
        }

        // 이미 열려있고, loanMainTab이 선택되어 있다면 유지한다.
        var currLoanTab = $("#loanMainTab").children('a.active').first();
        if( currLoanTab.length>0 )
        {
            currLoanTabFunc = currLoanTab.attr("onclick").split("(")[0];
           
        }
       
        // 변수정리
        var target_no = cno;
        var url = "/erp/loanmain";
        var loantab = '{{$loanMainTab}}';
        if(cno=='total')
        {
            target_no = "{{ $cust_info_no }}";
            url = "/erp/loantotal";
        }
        else
        {
            currLoanNo  = cno;
        }

        $.post(url, { no:target_no, loantab:loantab}, function(data) {

            // 화면 뿌려주기
            $("#contract-contents").html(data);
            
            // 일반계약일 경우, 색 지정하기
            var array_contracts_status = @json($array_contracts_status);
            if(cno!='total')
            {
                $("#status_color").val(array_contracts_status[cno]);
            }
            // 계좌통합정보일경우 회색으로 고정
            else
            {
                $("#status_color").val('#6c757d');
            }

            // 전체 색상 색칠하기
            var status_color = $("#status_color").val();
            resetMenuColor(status_color);

            // 계약들 각각 색상 칠해주기
            for(key in array_contracts_status)
            {
                if(key!=cno)
                {
                    contMenuColor(key, array_contracts_status[key]);
                }
            }
            
            if(cno!='total')
            {
                contMenuColor('total', '#6c757d');
            }

            // 선택한 계약 색칠해주기
            selectMenuColor('cont', cno);

            // 고객정보 메뉴 색칠해주기
            var cust_select = $("#cust_select").val();
            selectMenuColor('cust', cust_select);

            //  메모창에 loan_info_no 저장하기
            if($('#loan_info_no_custmemo').length)
            {
                $('#loan_info_no_custmemo').val(target_no);
            }

            // 계약선택 바꿀때 고객정보창 선택되어있다면 리로드
            if( cust_select == 'info' && opt!='FIRSTTIME' )
            {
                getCustData(cust_select);
            }
            
        });
    }

    // 오른쪽 메뉴 리스트 호출
    function getRightList(md)
    {
        // 메뉴색상 변경
        selectMenuColor('right', md);
        listSizeSet("B");

        // 변수정리
        var ci_no = "{{ $cust_info_no }}";
        var no = "{{ $no }}";
        var rmp = $("#right_menu_page").val();

        $("#right-menu-tab").html('');
        $.get("/erp/cust"+md, { cust_info_no:ci_no, page:rmp, loan_info_no:no }, function(data) {
            $("#right-menu-tab").html(data);
            afterAjax();
        });
    }

    // 동의정보 팝업열기
    function viewAgree(cust_info_no)
    {
        var url = "/ups/custinfoagree?div=ERP&tableNo="+cust_info_no;
        window.open(url, '', 'left=0, top=0, height=600,width=1300, fullscreen=yes' );
    }

    //  조건변경 팝업열기
    function changeContract(loan_info_no)
    {
        var url = "/erp/conditionpop?loan_info_no="+loan_info_no;
        window.open(url, '', 'left=0, top=0, height=800,width=950, fullscreen=yes' );
    }

    //  추가승인 팝업열기
    function addApr(loan_info_no)
    {
        var url = "/ups/loanappform?loan_info_no="+loan_info_no;
        window.open(url, '', 'right=0,top=0,height=' + screen.height + ',width=' + screen.width*0.8 + 'fullscreen=yes' );
    }

    //  방문요청 팝업열기
    function visitReq(loan_info_no)
    {
        no = '';
        var url = "/erp/visitrequest?no="+no+"&loan_info_no="+loan_info_no;
        window.open(url, '', 'right=0,top=0,height=' + screen.height + ',width=' + screen.width*0.8 + 'fullscreen=yes' );
    }

    // 전체 메뉴 색상 변경
    function resetMenuColor(color)
    {
        if(!color)
        {
            color = $("#status_color").val();
        }
        
        // 고객상세정보 border 수정
        $(".status-border-right").css('border-right', '2px solid '+color);
        $(".status-border-left-none").css('border', '2px solid '+color);
        $(".status-border-left-none").css('border-left', 'none');
        $(".status-border-right-none").css('border', '2px solid '+color);
        $(".status-border-right-none").css('border-right', 'none');
        $(".status-border-right-none").css('background-color', color);
        $(".status-border").css('border', '2px solid '+color);

        // 고객상세정보 메뉴 배경 수정
        $("[id^='cust-nav-']").css('background-color', color);
        $("[id^='cust-nav-']").css('color', '#FFFFFF');

        // 고객메모정보 선택색 수정
        $(".card-custnav.card-outline-tabs > .card-header a.active ").css('border-top', '3px solid '+color);
        $(".card-custnav.card-outline-tabs > .card-header a.active ").css('background-color', color);
        $(".card-custnav.card-outline-tabs > .card-header a.active ").css('color', '#FFFFFF');
        $(".card-custnav.card-outline-tabs > .card-header a").css('border-top', '3px solid '+color);
        // $(".card-outline > .card-header").css('border-top', '3px solid '+color);
        // $(".card-custnav.card-outline-tabs > .card-header a").css('color', color);

    }

    // 메뉴 선택 색상변경
    function selectMenuColor(md, id)
    {
        if(md=='right')
        {
            var color = $("#status_color").val();
            $(".card-custnav.card-outline-tabs > .card-header a").css('background-color', '#FFFFFF');
            $(".card-custnav.card-outline-tabs > .card-header a").css('color', 'black');
            $(".card-custnav.card-outline-tabs > .card-header a").css('border-top', '3px solid '+color);
            
            $("#right-menu-"+id+"-tab").css('background-color', color);
            $("#right-menu-"+id+"-tab").css('color', '#FFFFFF');
        }
        else
        {
            $("#"+md+"-nav-"+id+"-tab").css('background-color', '#FFFFFF');
            $("#"+md+"-nav-"+id+"-tab").css('color', 'black');
            $("#"+md+"-nav-"+id+"-tab").css('margin-right', '-2px');
            $("#"+md+"-nav-"+id+"-tab").css('border-right', 'none');
        }
    }

    // 계약 별 색상변경
    function contMenuColor(id, color)
    {
        $("#cont-nav-"+id+"-tab").css('background-color', color);
        $("#cont-nav-"+id+"-tab").css('color', '#FFFFFF');
        $("#cont-nav-"+id+"-tab").css('margin-right', '-2px');
        $("#cont-nav-"+id+"-tab").css('border-right', 'none');
    }

    // 조기경보
    function ewsForm(no) {
        {{-- $("#dataBody").html(loadingString);
        $.post("/erp/custewsform", { no: no }, function (data) {
            $("#creditModalBody").html(data);
            $("#creditModal").modal();
        }).fail(function (jqXHR) {
            console.log(jqXHR);
        }); --}}
    }
    
    // BS 조회
    function bsSearch(no)
    {
        window.open('/erp/bs?no=' + no,'bssearch','left=0,top=0,width=1100,height=950,scrollbars=yes');
    }


    // 상단 좌측탭 최초세팅
    @if( $opentab=="law" && isset($law_no) )
        getCustData('law', '', '', '{{ $law_no }}');
    @elseif( $opentab=="change")
        getCustData('change', '', '', '');
    @else
        getCustData('{{ $opentab ?? 'info' }}');    
    @endif



    

    getContData('{{ $no }}','FIRSTTIME');

    getRightList('memo');

    @if( isset($array_customer['prhb_yn']) && $array_customer['prhb_yn']=="Y" )
    alert('해당 고객은 채무자 대리인을 선임한 고객입니다.\n채권관리에 주의하시기 바랍니다.');
    @endif


    // 로드시 스크롤위치 조정
    $(document).ready(function(){
        window.moveTo(0,0);
        window.resizeTo(screen.availWidth, screen.availHeight);
    });    

    // 자주쓰는 링크
    function linkPopover()
    {
        var item = '#linkPop';
        var title = "<b class='popover-content'>자주가는 사이트</b>";
        var memo = "<div class='popover-content'>" + $('#linkPopHtml').html() + "</div>";
        viewPopover(item, title, memo);
    }
    @if( isset($linkBasic) )
        @foreach($linkBasic as $ids=>$info)
            $(document).on('click', '#link{{ $ids }}', function(){
                {{!! $info[1] !!}}
            });                                
        @endforeach
    @endif

</script>
@endsection

@php
    function setLeftMenu($menuTitle, $menuCode, $cnt='')
    {
        $viewCnt = ($cnt!='') ? ' ('.$cnt.')':'';
        
        $menu = '
        <span class="user-nav-link mb-1 status-border-right-none"  id="cust-nav-'.$menuCode.'-tab" data-toggle="pill" role="tab" aria-selected="false" >
            <table width="100%"><tr>
                <td class="hand text-bold" onclick="getCustData(\''.$menuCode.'\');">'.$menuTitle.$viewCnt.'</td> 
                <td width="15" class="hand" onClick="getCustDataNew(\''.$menuTitle.'\', \''.$menuCode.'\');"><i class="fas fa-external-link-alt"></td>
            </tr></table>
        </span>';
                    
                        
        return $menu;
    }
@endphp