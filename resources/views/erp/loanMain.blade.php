<?
if($loantab=='getLoanLog') {$active_d = 'ml-1'; $select_d = 'false';  $active_l = 'active'; $select_l = 'true'; } 
else { $active_d = 'active'; $select_d = 'true'; $active_l = 'ml-1';  $select_l = 'false'; }
?>
<div class="nav nav-pills border-bottom-0" role="tablist" style="background-color:#E6E7E8;padding:2px;" id="loanMainTab">
    {{-- <a class="nav-link nav-loan-detail text-xs {{$active_d}}"   role="button" data-toggle="pill" aria-selected="{{$select_d}}" onclick="getLoanDetail('{{ $no }}'); displaySplit(60, 40);">계약정보</a>
    <a class="nav-link nav-loan-detail text-xs ml-1"   role="button" data-toggle="pill" aria-selected="false" onclick="getLoanTrade('{{ $no }}'); displaySplit(0, 100);">거래원장</a> --}}
    {{-- <a class="nav-link nav-loan-detail text-xs ml-1"   role="button" data-toggle="pill" aria-selected="false" onclick="getLoanInterest('{{ $no }}'); displaySplit(0, 100);">이자조회</a> --}}
    
    {!! setBottomMenu('계약정보', 'loaninfo', 'displaySplit(41, 59);') !!}

    {!! setBottomMenu('거래원장', 'loantrade', 'displaySplit(0, 100);') !!}

    {{-- {!! setBottomMenu('이자조회', 'loaninterest', 'displaySplit(0, 100);') !!} --}}

    {{-- @if( $rslt->status=='C' || $rslt->status=='D' )
        <a class="nav-link nav-loan-detail text-xs ml-1"   role="button" data-toggle="pill" aria-selected="false" onclick="getLoanSettlePlan('{{ $no }}'); displaySplit(60, 40);">화해상환스케줄</a>
        {!! setBottomMenu('화해상환스케줄', 'loansettleplan', 'displaySplit(60, 40);') !!} --}}
    {{-- @if( $rslt->return_method_cd!='F' )
        <a class="nav-link nav-loan-detail text-xs ml-1"   role="button" data-toggle="pill" aria-selected="false" onclick="getLoanPlan('{{ $no }}'); displaySplit(41, 59);">상환스케줄</a>
        {!! setBottomMenu('상환스케줄', 'loanplan', 'displaySplit(41, 59);') !!}
    @endif --}}

    {{-- <a class="nav-link nav-loan-detail text-xs ml-1"   role="button" data-toggle="pill" aria-selected="false" onclick="getLoanGuarantor('{{ $no }}'); displaySplit(60, 40);">보증인정보</a> --}}
    {{-- {!! setBottomMenu('보증인정보', 'loanguarantor', 'displaySplit(60, 40)', $gu_rslt->g_cnt) !!} --}}

    {{-- <a class="nav-link nav-loan-detail text-xs ml-1"   role="button" data-toggle="pill" aria-selected="false" onclick="getLoanDoc('{{ $no }}'); displaySplit(60, 40);">징구서류</a>    
    <a class="nav-link nav-loan-detail text-xs ml-1"   role="button" data-toggle="pill" aria-selected="false" onclick="getLoanVirAccount('{{ $no }}','{{ $rslt->cust_info_no ?? ''}}'); displaySplit(60, 40);">가상계좌</a> --}}
    {{-- {!! setBottomMenu('징구서류', 'loandoc', 'displaySplit(60, 40)') !!} --}}
    {{-- {!! setBottomMenu('가상계좌', 'loanviraccount', 'displaySplit(60, 40)') !!} --}}

    <!--<a class="nav-link nav-loan-detail text-xs ml-1"   role="button" data-toggle="pill" aria-selected="false" onclick="getLoanCms('{{ $no }}','{{ $rslt->cust_info_no ?? ''}}')">CMS</a> -->
    <!--<a class="nav-link nav-loan-detail text-xs ml-1"   role="button" data-toggle="pill" aria-selected="false" onclick="">신청원장</a>-->
    {{-- <a class="nav-link nav-loan-detail text-xs {{$active_l}}"   role="button" data-toggle="pill" aria-selected="{{$select_l}}" onclick="getLoanLog('{{ $no }}'); displaySplit(60, 40);">변경내역</a>
    <a class="nav-link nav-loan-detail text-xs ml-1"   role="button" data-toggle="pill" aria-selected="false" onclick="getPaperPrint('{{ $no }}', '{{$rslt->cust_info_no ?? ''}}'); displaySplit(60, 40);">양식인쇄</a> --}}
    {!! setBottomMenu('변경내역', 'loanlog', 'displaySplit(41, 59)') !!}

    {{-- {!! setBottomMenu('양식인쇄', 'paperprint', 'displaySplit(60, 40)') !!} --}}

    {{-- {!! setBottomMenu('신복계좌번호', 'ccrsaccount', 'displaySplit(60, 40)') !!} --}}


    <div class="d-flex flex-row ml-auto p-1">
        {{-- @if( Func::funcCheckPermit("A124","A") )
        <a class="btn btn-xs btn-outline-secondary" onclick="getPopUp('/erp/sanggakoneinfo?loan_info_no={{$no}}','sanggakoneinfo','width=1200, height=800')"><i class="fa fa-sm fa-window-restore mr-2"></i>대손신청</a>
        @endif --}}
        <!-- <a class="btn btn-xs btn-outline-secondary ml-1" onclick="addApr('{{ $no }}');"><i class="fa fa-sm fa-window-restore mr-2"></i>추가승인</a> -->
        {{-- <a class="btn btn-xs btn-outline-secondary ml-1" onclick="changeContract('{{ $no }}');"><i class="fa fa-sm fa-window-restore mr-2"></i>조건변경</a> --}}
        {{-- <a class="btn btn-xs btn-outline-secondary ml-1" onclick="getPopUp('/erp/settleapplist?loan_info_no={{$no}}','settleapp','width=1000, height=500');"><i class="fa fa-sm fa-window-restore mr-2"></i>화해신청</a> --}}
        {{-- <a class="btn btn-xs btn-outline-secondary ml-1" onclick="visitReq('{{ $no }}');"><i class="fa fa-sm fa-window-restore mr-2"></i>방문요청</a> --}}
    </div>
</div>

<div class="tab-content" id="loan-tabs-tabContent">
    <div class="tab-pane fade  show active pt-0 pl-1 pr-1 pb-1" id="loan-tabs-home" role="tabpanel" ></div>
</div>




<script>

    function getLoanData(uri, userVal)
    {
        var cno = currLoanNo;
        var ci_no = '{{ $rslt->cust_info_no }}';

        var liColName = 'no';
        var userCol = '';
        var jsonVars = { no:cno };
        // 보증인, 징구서류
        if(uri=='loanguarantor' || uri=='loandoc')
        {
            jsonVars = { loan_info_no:cno, no:userVal };
        }
        // 이자조회
        else if(uri=='loaninterest')
        {
            jsonVars = { no:cno, today:userVal };
        }
        // 화해스케줄
        else if(uri=='loanplan')
        {
            jsonVars = { no:cno, plan_trade_no:userVal };
        }
        // cms, 양식인쇄, 가상계좌
        else if(uri=='loancms' || uri=='paperprint' || uri=='loanviraccount' || uri=='ccrsaccount')
        {
            jsonVars = { loan_info_no:cno, cust_info_no:ci_no };
        }
        
        
        // CORS 예외처리
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#loan-tabs-home").html(loadingString);     
       
        $.post("/erp/"+uri, jsonVars, function(data) {
            $("#loan-tabs-home").html(data);
        });

        $('.nav-loan-detail').removeClass('active');
        $('#loan_'+uri).addClass('active');
    }

    
    // 새창으로 메뉴를 띄운다.
    function getLoanDataNew(menuTitle, opentab)
    {          
        var url = "/erp/custpopnew?zone=loan&cust_info_no={{ $rslt->cust_info_no }}&no={{ $no }}&opentab=" + opentab + "&menutitle=" + menuTitle;
        window.open(url, '', 'left=0, top=0, height=1000,width=1400, fullscreen=yes' );
    }


    // 계약정보
    function getLoanDetail(cno)
    {
        // CORS 예외처리
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#loan-tabs-home").html(loadingString);
        $.post("/erp/loaninfo", { no:cno }, function(data) {
            $("#loan-tabs-home").html(data);
        });
    }

    // 거래원장
    function getLoanTrade(cno)
    {
        // CORS 예외처리
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#loan-tabs-home").html(loadingString);
        $.post("/erp/loantrade", { no:cno }, function(data) {
            $("#loan-tabs-home").html(data);
        });
    }
    
    // 이자조회
    function getLoanInterest(cno, td)
    {
        // CORS 예외처리
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#loan-tabs-home").html(loadingString);
        $.post("/erp/loaninterest", { no:cno, today:td }, function(data) {
            $("#loan-tabs-home").html(data);
        });
    }

    // 상환스케줄
    function getLoanPlan(cno)
    {
        // CORS 예외처리
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var plan_trade_no = $("#plan_trade_no").val();

        $("#loan-tabs-home").html(loadingString);
        $.post("/erp/loanplan", { no:cno, plan_trade_no:plan_trade_no }, function(data) {
            $("#loan-tabs-home").html(data);
        });
    }

    // 화해상환스케줄
    function getLoanSettlePlan(cno)
    {
        // CORS 예외처리
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#loan-tabs-home").html(loadingString);
        $.post("/erp/loansettleplan", { no:cno }, function(data) {
            $("#loan-tabs-home").html(data);
        });
    }

    // 화해상환스케줄
    function getLoanLog(cno)
    {
        // CORS 예외처리
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#loan-tabs-home").html(loadingString);
        $.post("/erp/loanlog", { no:cno }, function(data) {
            $("#loan-tabs-home").html(data);
        });
    }

    // 보증인정보
    function getLoanGuarantor(loan_info_no,no)
    {
        // CORS 예외처리
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#loan-tabs-home").html(loadingString);
        $.post("/erp/loanguarantor", 
        { 
            loan_info_no:loan_info_no,
            no:no 
        }, 
        function(data) {
            $("#loan-tabs-home").html(data);
        });
    }


    // 징구서류
    function getLoanDoc(loan_info_no,no)
    {
        // CORS 예외처리
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#loan-tabs-home").html(loadingString);
        $.post("/erp/loandoc", 
        { 
            loan_info_no:loan_info_no,
            no:no 
        }, 
        function(data) {
            $("#loan-tabs-home").html(data);
            afterAjax();
        });
    }

    // cms
     function getLoanCms(loan_info_no,no)
    {
        // CORS 예외처리
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#loan-tabs-home").html(loadingString);
        $.post("/erp/loancms", 
        { 
            loan_info_no:loan_info_no,
            cust_info_no:no 
        }, 
        function(data) {
            $("#loan-tabs-home").html(data);
            afterAjax();
        });
    }

    // 양식인쇄
    function getPaperPrint(loan_info_no, cust_info_no)
    {
        // CORS 예외처리
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#loan-tabs-home").html(loadingString);

        $.get("/erp/paperprint", 
        { 
            loan_info_no:loan_info_no,
            cust_info_no:cust_info_no,
        }, 
        function(data) {
            $("#loan-tabs-home").html(data);
            afterAjax();
        });
    }

    // 가상계좌
    function getLoanVirAccount(loan_info_no,cust_info_no)
    {
        // CORS 예외처리
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#loan-tabs-home").html(loadingString);

        $.post("/erp/loanviraccount", 
        { 
            loan_info_no:loan_info_no,
            cust_info_no:cust_info_no,
        }, 
        function(data) {
            $("#loan-tabs-home").html(data);
            afterAjax();
        });
    }


</script>


<script>

    // 계약을 선택했을때 열려진 하단 메뉴를 유지한다. (메뉴가 있다면...)
    if( currLoanTabFunc!="" && $("#loanMainTab").children('a[onclick^='+currLoanTabFunc+']').first().length>0 )
    {
        $("#loanMainTab").children('a[onclick^='+currLoanTabFunc+']').trigger('click');
    }
    else
    {
        lc = (opener.location.toString()).split("/");
        if( lc[3]=="erp" && lc[4]=="doc#" )
        {
            $("#loanMainTab").children('a[onclick^=getLoanDoc]').trigger('click');
        }
        else if( lc[3]=="erp" && ( lc[4]=="tradein#" || lc[4]=="tradeout#" ) )
        {
            $("#loanMainTab").children('a[onclick^=getLoanTrade]').trigger('click');
        }
        else
        {
                if('{{ $loantab }}'=='getLoanLog')
                {
                    getLoanLog('{{ $no }}');
                }
                else
                {
                    getLoanDetail('{{ $no }}');
                }
        }
    }
    
    // 초기 진입 메뉴
    getLoanData('loaninfo');
</script>



@php

    function setBottomMenu($menuTitle, $uri, $displaySplit='', $cnt='')
    {
        $viewCnt = ($cnt!='') ? ' ('.$cnt.')':'';
        
        $menu = '
        <span class="nav-link nav-loan-detail text-xs" role="button" data-toggle="" aria-selected="false" id="loan_'.$uri.'">
            <table width="100%"><tr>
                <td class="hand" onclick="getLoanData(\''.$uri.'\');'.$displaySplit.'">'.$menuTitle.$viewCnt.'</td> 
                <td width="20" class="hand" align="right" onClick="getLoanDataNew(\''.$menuTitle.'\', \''.$uri.'\');"><i class="fas fa-external-link-alt"></td>
            </tr></table>
        </span>';

        return $menu;
    }
@endphp