<div class="p-2">

    <!-- BODY -->
    <b>회생/파산 리스트</b>
    <button type="button" class="btn btn-xs btn-outline-info float-right mb-1" onclick="setIrlInfo('');"><i class="fa fa-plus-square text-info mr-1"></i>추가등록</button>
    <table class="table table-sm table-hover loan-info-table card-secondary card-outline">
        <thead>
            <tr>
                <th class="text-center">고객번호<br>계약번호</th>
                <th class="text-center">상품명</th>
                <th class="text-center">상태<br>연체일수</th>
                <th class="text-center">주채무구분</th>
                <th class="text-center">구분</th>
                <th class="text-center">유형</th>
                <th class="text-center">법원</th>
                <th class="text-center">사건번호</th>
                <th class="text-center">금지결정일<br>금지결정도달일</th>
                <th class="text-center">게시(파산)결정일<br>변제인가일</th>
                <th class="text-center">종국일자<br>종국결과</th>
                <th class="text-center">자동조회</th>
                <th class="text-center">변제예정금액<br>인가일 전 잔액</th>
                <th class="text-center">인가율</th>
                <th class="text-center">수정일자<br>수정자</th>
            </tr>
        </thead>
        <tbody id="irl_list">
            @forelse( $li as $idx => $v )
                <tr onclick="setIrlInfo({{ $v->no }},{{ $v->loan_info_no }})" id="irl_row{{ $v->no }}">
                    <td class="text-center">{{ $v->cust_info_no }}<br>{{ $v->loan_info_no }}</td>
                    <td class="text-center">{{ Func::getArrayName($arrayProduct, $v->pro_cd) }}</td>
                    <td class="text-center">{{ Func::getArrayName($arrayContractSta, $v->status) }}<br>{{ $v->delay_term }}</td>
                    <td class="text-center">{{ Func::getArrayName($configArr['stl_target_cd'], $v->target_div) }}</td>
                    <td class="text-center">{{ Func::getArrayName($arrayReliefSubDiv[$v->div], $v->sub_div) }}</td>
                    <td class="text-center">{{ Func::getArrayName($arrayReliefIrlStatus, $v->status_cd) }}</td>
                    <td class="text-center">{{ Func::getArrayName($configArr['court_cd'], $v->court_cd) }}</td>
                    <td class="text-center">{{ $v->event_year }}{{ $v->event_cd }}{{ $v->event_no }}</td>
                    <td class="text-center">{{ Func::dateFormat($v->ban_date) }}<br>{{ Func::dateFormat($v->ban_arrive_date) }}</td>
                    <td class="text-center">{{ Func::dateFormat($v->start_date) }}<br>{{ Func::dateFormat($v->auth_date) }}</td>
                    <td class="text-center">{{ Func::dateFormat($v->end_date) }}<br>{{ $v->end_result }}</td>
                    <td class="text-center">{!! ( $v->auto_flag=='Y' ) ? "<i class='fas fa-check text-green'>" : "" !!}</td>
                    <td class="text-center">{{ isset($v->settle_money) ? number_format($v->settle_money) : 0 }}<br>{{ isset($v->settle_bef_origin) ? number_format($v->settle_bef_origin) : 0 }}</td>
                    <td class="text-center">{{ $v->auth_ratio }}%</td>
                    <td class="text-center">{{ Func::dateFormat($v->save_time) }}<br>{{ Func::getArrayName($arrayUserId, $v->save_id) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="15" class='text-center p-4'><i class="fas fa-balance-scale m-2"></i>등록된 회생/파산 내역이 없습니다.</td>
                </tr>
            @endforelse
            <tr><td colspan="15"></td></tr>
        </tbody>
    </table>
    
    <div class="needs-validation" id="irl_contents"></div>

    <script>
        function setIrlInfo(no,loan_info_no)
        {
            $(".was-validated").removeClass("was-validated");
            $("#irl_list >tr").attr('style','background-color:');
            $("#irl_row"+no).attr('style','background-color:#FFDDDD');
        
            $("#irl_contents").html(loadingString);
            $.post("/erp/custreliefinfo","div=IRL&no="+no+"&cust_info_no={{ $cust_info_no }}&loan_info_no="+loan_info_no, 
                function(data) {
                $("#irl_contents").html(data);
                afterAjax();
                setInputMask('class', 'moneyformat', 'money');
            });
        }

        function irlAction(mode, div_no)
        {
            // 필수값 체크
            if(!$('#sub_div').val() || $('#sub_div').val()=="")
            {
                alert('구분을 선택해주세요');
                return false;
            }

            if(!$('#status_cd option:selected').val() || $('#status_cd option:selected').val()=="")
            {
                alert('유형을 선택해주세요');
                return false;
            }

            if(!$('#target_div option:selected').val() || $('#target_div option:selected').val()=="")
            {
                alert('채무구분을 선택해주세요');
                return false;
            }

            if(!$('#court_cd option:selected').val() || $('#court_cd option:selected').val()=="")
            {
                alert('법원을 선택해주세요');
                return false;
            }

            if(!$('#event_cd option:selected').val() || $('#event_cd option:selected').val()=="" || !$('#event_year').val() || $('#event_year').val()=="" || !$('#event_no').val() || $('#event_no').val()=="")
            {
                alert('사건번호를 입력해주세요');
                return false;
            }


            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
    
            $('.c_readonly').attr("disabled",false);
            $('#loan_info_no').attr("disabled",false);
            $('#status_cd').attr("disabled",false);
    
            var postdata = $('#irl_form').serialize();
            postdata += '&mode='+mode;

            if( mode=="DEL" )
            {
                if(!confirm('해당 채무조정이력을 삭제하시겠습니까?\n삭제된 정보는 복구할 수 없으며 필요시 재등록하셔야합니다.'))
                {
                    return false;
                }
            }
    
            $("#irl_contents").html(loadingString);
            $.post(
                "/erp/custreliefaction", 
                postdata, 
                function(data) {
                    alert(data.rs_msg);
                    getCustData('irl',data.loan_info_no,'',data.no);
            });
        }
    </script>