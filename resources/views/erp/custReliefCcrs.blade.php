<div class="p-2">

    <!-- BODY -->
    <b>신용회복 리스트</b>
    <button type="button" class="btn btn-xs btn-outline-info float-right mb-1" onclick="setCcrsInfo('');"><i class="fa fa-plus-square text-info mr-1"></i>수정조정 등록</button>
    <table class="table table-sm table-hover loan-info-table card-secondary card-outline">
        <thead>
            <tr>
                <th class="text-center">고객번호<br>계약번호</th>
                <th class="text-center">상품명</th>
                <th class="text-center">주채무구분</th>
                <th class="text-center">상태<br>연체일수</th>
                <th class="text-center">신청구분</th>
                <th class="text-center">진행상태</th>
                <th class="text-center">접수번호</th>
                <th class="text-center">접수통지일</th>
                <th class="text-center">확정일<br>(합의서체결일)</th>
                <th class="text-center">반송일</th>
                <th class="text-center">실효/포기일</th>
                <th class="text-center">수정조정확정일</th>
                <th class="text-center">상환방식</th>
                <th class="text-center">부동산담보대출여부</th>
                <th class="text-center">자동조회</th>
                <th class="text-center">조정후합계</th>
                <th class="text-center">인가율</th>
            </tr>
        </thead>
        <tbody id="ccrs_list">
            @forelse( $li as $idx => $v )
                <tr onclick="setCcrsInfo({{ $v->no }},{{ $v->loan_info_no }})" id="ccrs_row{{ $v->no }}">
                    <td class="text-center">{{ $v->cust_info_no }}<br>{{ $v->loan_info_no }}</td>
                    <td class="text-center">{{ Func::getArrayName($arrayProduct, $v->pro_cd) }}</td>
                    <td class="text-center">{{ Func::getArrayName($configArr['stl_target_cd'], $v->target_div) }}</td>
                    <td class="text-center">{{ Func::getArrayName($arrayContractSta, $v->status) }}<br>{{ $v->delay_term }}</td>
                    <td class="text-center">{{ $v->app_type }}</td>
                    <td class="text-center">{{ Func::getArrayName($arrayReliefCcrsStatus, $v->status_cd) }}</td>
                    <td class="text-center">{{ $v->event_no }}</td>
                    <td class="text-center">{{ Func::dateFormat($v->app_arrive_date) }}</td>
                    <td class="text-center">{{ Func::dateFormat($v->auth_date) }}</td>
                    <td class="text-center">{{ Func::dateFormat($v->return_date) }}</td>
                    <td class="text-center">{{ Func::dateFormat($v->cancel_date) }}</td>
                    <td class="text-center">{{ Func::dateFormat($v->edit_auth_date) }}</td>
                    <td class="text-center">{{ $v->return_method }}</td>
                    <td class="text-center">{{ $v->mortage_loan_yn }}</td>
                    <td class="text-center">{!! ( $v->auto_flag=='Y' ) ? "<i class='fas fa-check text-green'>" : "" !!}</td>
                    <td class="text-center">{{ isset($v->balance) ? number_format($v->balance) : 0 }}</td>
                    <td class="text-center">{{ $v->auth_ratio }}%</td>
                </tr>
            @empty
                <tr>
                    <td colspan="17" class='text-center p-4'><i class="fas fa-balance-scale m-2"></i>등록된 신용회복 내역이 없습니다.</td>
                </tr>
            @endforelse
            <tr><td colspan="17"></td></tr>
        </tbody>
    </table>
    
    <div class="needs-validation" id="ccrs_contents"></div>
    
    
    <script>
        function setCcrsInfo(no,loan_info_no)
        {
            $(".was-validated").removeClass("was-validated");
            $("#ccrs_list >tr").attr('style','background-color:');
            $("#ccrs_row"+no).attr('style','background-color:#FFDDDD');
        
            $("#ccrs_contents").html(loadingString);
            $.post("/erp/custreliefinfo","div=CCRS&no="+no+"&cust_info_no={{ $cust_info_no }}&loan_info_no="+loan_info_no, 
                function(data) {
                $("#ccrs_contents").html(data);
                afterAjax();
                setInputMask('class', 'moneyformat', 'money');
            });
        }

        function ccrsAction(mode, div_no)
        {
            //if(checkValue())
            //{
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
        
                $('#loan_info_no').attr("disabled",false);

                var postdata = $('#ccrs_form').serialize();
                postdata += '&mode='+mode;

                if( mode=="DEL" )
                {
                    if(!confirm('해당 채무조정이력을 삭제하시겠습니까?\n삭제된 정보는 복구할 수 없으며 필요시 재등록하셔야합니다.'))
                    {
                        return false;
                    }
                }
        
                $("#ccrs_contents").html(loadingString);
                $.post(
                    "/erp/custreliefaction", 
                    postdata, 
                    function(data) {
                        alert(data.rs_msg);
                        getCustData('ccrs', data.loan_info_no, '', data.no);
                });
            // }
            // else
            // {
            //     alert("필수입력값을 확인해주세요");
            // }
        }
    </script>