@extends('layouts.masterPop')
@section('content')

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


<div class="content-wrapper m-0" >
    <div class="row m-0">
    <input type="hidden" name="status_color" id="status_color" value="{{ $status_color }}">
        <!-- 왼쪽패널 -->
        <div class="col-md-12" >
            <!-- 고객요약정보 -->
            <div class="col-md-12 card mt-1 mb-2 p-1" style="border:2px solid {{ $status_color }};">
                <table class="table table-sm table-borderless m-0">
                    <colgroup>
                    <col width="3%"/>
                    <col width="7%"/>
                    <col width="3%"/>
                    <col width="7%"/>
                    <col width="3%"/>
                    <col width="7%"/>
                    <col width="3%"/>
                    <col width="7%"/>
                    <col width="3%"/>
                    <col width="7%"/>
                    </colgroup>
                    <tbody>
                        <tr>
                            <th class="text-center">투자자번호</th>
                            <td>{{ $lui->investor_no }}</td>
                            <th class="text-center">고객명</th>
                            <td>{{ $lui->name }}</td>
                            <th class="text-center">주민번호</th>
                            <td>{{ Func::ssnFormat($lui->ssn,'A') }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

                    
            <!-- 고객상세정보 -->
            <div class="row pr-2">
                {{-- 왼쪽메뉴 --}}
                <div class="col-md-1 pr-0"  style="border-right:2px solid {{ $status_color }}; ">
                    <div class="nav flex-column nav-tabs" id="investor-tab" role="tablist" aria-orientation="vertical" >
                        <a class="nav-link text-bold mb-1 hand" id="investor-nav-investorinfo-tab" data-toggle="pill" role="tab" aria-selected="true" onclick="getInvestorData('investorinfo');">투자자 정보</a>
                        <a class="nav-link text-bold mb-1 hand" id="investor-nav-investorinfodetail-tab" data-toggle="pill" role="tab" aria-selected="true" onclick="getInvestorData('investorinfodetail');">투자내역</a>
                        {{-- <a class="nav-link text-bold mb-1 hand" id="investor-nav-investorinfosms-tab" data-toggle="pill" role="tab" aria-selected="true" onclick="getInvestorData('investorinfosms');">문자</a> --}}
                        <a class="nav-link text-bold mb-1 hand" id="investor-nav-investorinfomemo-tab" data-toggle="pill" role="tab" aria-selected="true" onclick="getInvestorData('investorinfomemo');">메모</a>
                        <a class="nav-link text-bold mb-1 hand" id="investor-nav-investorinfoimage-tab" data-toggle="pill" role="tab" aria-selected="true" onclick="getInvestorData('investorinfoimage');">첨부파일</a>
                        <a class="nav-link text-bold mb-1 hand" id="investor-nav-investorinfochange-tab" data-toggle="pill" role="tab" aria-selected="true" onclick="getInvestorData('investorinfochange');">변경내역</a>
                    </div>
                </div>
                {{-- 메뉴클릭 출력화면 --}}
                <div class="col-md-11 pb-3 status-border-left-none bg-white " style='border-top-right-radius:4px; border-bottom-right-radius:4px;min-heignt:1000px;' style="min-heignt:1000px">
                    <div class="tab-content" id="investor-tabContent ">
                        <!--  상단 고객데이터 출력 영역 -->
                        <div class="fade show active p-2" id="investor-contents"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection




@section('javascript')
<script>
getInvestorData('investorinfo');
// 고객정보 (md:탭, div_no:탭에서 사용할 no)
function getInvestorData(md,div_no,select)
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
    var no = "{{ $lui->no }}";
    var investor_no = "{{ $lui->investor_no }}";
    var img_no = no;
    var selected = select;

    $("#investor-contents").html(loadingString);       
    $.post(url, { mode:md, loan_usr_info_no:no, loan_usr_info_investor_no:investor_no, no:img_no, selected:selected }, function(data) {
    // $.post(url, { mode:md, loan_usr_info_no:no, no:img_no, selected:selected }, function(data) {
        $("#investor-contents").html(data);
        afterAjax();
    });
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
    $("[id^='investor-nav-']").css('background-color', color);
    $("[id^='investor-nav-']").css('color', '#FFFFFF');

    // 고객메모정보 선택색 수정
    $(".card-investornav.card-outline-tabs > .card-header a.active ").css('border-top', '3px solid '+color);
    $(".card-investornav.card-outline-tabs > .card-header a.active ").css('background-color', color);
    $(".card-investornav.card-outline-tabs > .card-header a.active ").css('color', '#FFFFFF');
    $(".card-investornav.card-outline-tabs > .card-header a").css('border-top', '3px solid '+color);
    // $(".card-investornav.card-outline-tabs > .card-header a").css('color', color);

}


// 메뉴 선택 색상변경
function selectMenuColor(md, id)
{
    if(md=='right')
    {
        var color = $("#status_color").val();
        $(".card-investornav.card-outline-tabs > .card-header a").css('background-color', '#FFFFFF');
        $(".card-investornav.card-outline-tabs > .card-header a").css('color', 'black');
        $(".card-investornav.card-outline-tabs > .card-header a").css('border-top', '3px solid '+color);
        
        $("#right-menu-"+id+"-tab").css('background-color', color);
        $("#right-menu-"+id+"-tab").css('color', '#FFFFFF');
    }
    else
    {
        $("#"+md+"-nav-"+id+"-tab").css('background-color', '#FFFFFF');
        $("#"+md+"-nav-"+id+"-tab").css('color', 'black');
        $("#"+md+"-nav-"+id+"-tab").css('margin-right', '-3px');
        $("#"+md+"-nav-"+id+"-tab").css('border', '2px solid {{ $status_color }}');
        $("#"+md+"-nav-"+id+"-tab").css('border-right', 'none');
    }
}

</script>
@endsection