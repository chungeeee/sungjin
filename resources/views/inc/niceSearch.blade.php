@php
$arrayCreditLink = [
    //'CFS' => 'cfs',
    // '요약' => 'summary',
    // '신용조회' => 'credit',
    'CB조회'  => 'cb',
    // '신용인증송부' => 'cert',
    // 'DI신용조회' => 'di',
    //'SCAF' => 'scaf',
    //'사기대출' => 'hunter',
    //'맞춤형CB' => 'customizedcb',
    //'실시간대출조회' => 'liveloan',
    //'대출상담조회' => 'loancounsel',
    //'계약철회조회' => 'withdraw',
    //'조기경보' => 'ews',
    //'기업CB정보' => 'corpcb',
];
@endphp


{{-- 일반적인 include --}}
@if(!isset($isPopOver))
    <h6 class="card-title"><i class="fas fa-id-card m-2"></i>신용조회</h6>
    <div class="row col-md-12 mb-2">
    @foreach($arrayCreditLink as $title=>$link)
        <button type="button" class="btn btn-outline-dark btn-xs m-0 mr-1" onclick="creditForm('{{ isset($no) ? $no : (isset($loan_app->no) ? $loan_app->no : (isset($cust_info_no) ? $cust_info_no : 0)) }}', '', '{{ $link }}');"><i class="fa fa-search mr-1"></i>{{ $title }}</button>
        @if ($link == 'ews')
            <button type="button" class="btn btn-outline-dark btn-xs m-0 mr-1" onclick="bsSearch('{{ isset($no) ? $no : (isset($loan_app->no) ? $loan_app->no : (isset($cust_info_no) ? $cust_info_no : 0)) }}');"><i class="fa fa-search mr-1"></i>BS 조회</button>
        @endif
    @endforeach
    
    </div>
    <script>
        function printNiceAction(postCd, loanAppNo)
        {
            pJrfDir = '/home/laravel/'+"{{ strtolower(config("app.comp")) }}"+'/public/ubi4/files/';
            
            var formdata = {
                post_cd : postCd,
                loan_app_no : loanAppNo,
                post_addr_cd : '1',
                erp_ups : 'UPS',
            };

            var result = ubiPrint(formdata, "ups");

            console.log(result);
        }

        // 신용정보 상세보기 팝업창
        function creditForm(no, list_no, code) {
            var width = 1000;
            var height = 1000;
            if (list_no == undefined) {
                list_no = '';
            }

            // 조기경보 팝업
            if (code == 'ews') {
                width = 1200;
            }

            window.open("/erp/cust" + code + "pop?no=" + no + "&list_no=" + list_no, "erp_" + code + "pop_" + no, "left=0, top=0, width=" + width + ", height=" + height + ", scrollbars=yes");
        }
        
    </script>    
{{-- 바로가기 팝오버 --}}
@else
    <h6 class="popover-con">신용조회</h6>
    <div class="row popover-con mb-2">
    @foreach($arrayCreditLink as $title=>$link)
        <div class="btn btn-outline-dark btn-xs m-0 mr-1 mb-2 popover-con" id="link{{ $title }}">333<i class="fa fa-search mr-1"></i>{{ $title }}</div>
        @if ($link == 'ews')
            <div class="btn btn-outline-dark btn-xs m-0 mr-1 mb-2 popover-con" id="linkbs"><i class="fa fa-search mr-1"></i>BS 조회</div>
        @endif
    @endforeach
    </div>

    @section('javascriptTemp')
    <script>

    @foreach($arrayCreditLink as $title=>$link)
        $(document).on('click', '#link{{ $title }}', function(){
            //alert('{{$title}}');
            creditForm('{{ isset($no) ? $no : 0 }}' , '', '{{ $link }}');
        });                                
    @endforeach
        $(document).on('click', '#linkbs', function(){
            bsSearch('{{ isset($no) ? $no : (isset($loan_app->no) ? $loan_app->no : (isset($cust_info_no) ? $cust_info_no : 0)) }}');
        });                                
    </script>
    @endsection


    
@endif
