@extends('layouts.masterPop')
@section('content')

{{-- Action 처리 성공 여부 alert --}}
@if (Session::has('result'))
    <script> alert('{{Session::get("result")}}'); </script>
@endif

<style>

    /* 상단고정 */
    #fixed_top {    
      position: fixed;
      width: calc(100% - 10px);
      height: 38px;
      z-index:101;
      margin-left:0px;
      margin-bottom:0px;
      padding-left:0px;
    }
</style>

<div class="col-md-12 p-1 pr-0">
<div class="card status-border" id="fixed_top">
    <div class="card-body p-1 pb-0">
        <table class="table table-sm table-borderless m-0">
            <col width="28%"></col>
            <col width="12%" style="background-color:#E7EAED;"></col>
            <col width="12%"></col>
            <col width="12%" style="background-color:#E7EAED;"></col>
            <col width="12%"></col>
            <col width="12%" style="background-color:#E7EAED;"></col>
            <col width="12%"></col>
            <tbody>
                <tr class="text-center">
                    <td class="bold text-blue">{{ $menuTitle }}</td>                    
                    <td>차입자번호</td>
                    <td>{{ $arrayCustomer['cust_info_no'] }}</td>
                    <td>이름</td>
                    <td>{{ $arrayCustomer['name'] }}</td>
                    <td>주민번호</td>
                    <td>{{ $arrayCustomer['ssn'] }}</td>
                </tr>
            </tbody>
        </table>
    </div>
</div>
</div>

<div class="mt-5" id="customer-contents"></div>
<div class="mt-5" id="loan-tabs-home"></div>
               
@endsection

@section('javascript')

<script>
    var currLoanNo      = "{{ $no }}";
    
    function divInit()
    {
        $("#customer-contents").html('');
        $("#loan-tabs-home").html('');
    }

    // 고객정보
    /*
        view : 화면 이름
        div  : 변경내역에서 사용 (구분값)
        no   : 각각 화면에서 사용하는 no값
    */
    function getCustData(view, div, no, nn, tab='', ino=0)
    {        
        // CORS 방지
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        var li_no = currLoanNo;
        
        if(view=="irl" || view=="ccrs")
        {
            div = view;
            view = "relief";
        }

        // 고객정보 받아오기
        var url = "/erp/cust"+view;
        var ci_no = "{{ $cust_info_no }}";

        divInit();
        $("#customer-contents").html(loadingString);
        $.post(url, { mode:view, cust_info_no:ci_no,loan_info_no:li_no,div:div,no:no }, function(data) {
            $("#customer-contents").html(data);
            afterAjax();

            if( view=='law' && nn>0 )
            {
                setLawInfo(nn,li_no,tab,ino);
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

        $("#customer-contents").html('');
        $.get("/erp/cust"+md, { cust_info_no:ci_no, page:rmp, loan_info_no:no }, function(data) {
            $("#customer-contents").html(data);
            afterAjax();
        });
    }


    function getLoanData(uri, userVal)
    {
        var cno = currLoanNo;
        var ci_no = "{{ $cust_info_no }}";
        
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
        else if(uri=='loancms' || uri=='paperprint' || uri=='loanviraccount')
        {
            jsonVars = { loan_info_no:cno, cust_info_no:ci_no };
        }
        
        
        // CORS 예외처리
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        divInit();
        $("#loan-tabs-home").html(loadingString);     
       
        $.post("/erp/"+uri, jsonVars, function(data) {
            $("#loan-tabs-home").html(data);
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

    


    // 상단 좌측탭 최초세팅
    @if($zone=='cust')
        getCustData('{{ $opentab }}');  
    @endif  
    
    @if($zone=='loan')
        getLoanData('{{ $opentab }}');
    @endif

    @if($zone=='right')
        getRightList('memo');
    @endif


    // 로드시 스크롤위치 조정
    $(document).ready(function(){
        window.moveTo(0,0);
        // window.resizeTo(screen.width, screen.availHeight);
    });    
    

    $(".status-border").css('border', '2px solid {{ $statusColor }}');

</script>
@endsection