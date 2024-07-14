
@if( isset($result) )

    @forelse( $result as $tname => $arr )
        @if( count($arr) > 0 )
        
            @if( $searchTypeArr[$tname]['eu'] == "ERP" )

            <span id='label_{{ $tname }}' style="display:none;">
                <b>{{ isset($searchTypeArr[$tname]) ? $searchTypeArr[$tname]['list']." - ".$searchTypeArr[$tname]['title']."(".$searchTypeArr[$tname]['str'].")" : '' }}</b>
                
                @if(sizeof($arr)>10 && $searchTypeArr[$tname]['title']=='성명') 
                <span>
                    <button type="button" class="btn btn-xs btn-outline-info float-right mb-1 ml-4" id="btn{{ $tname }}" onclick="$('#table{{ $tname }}').toggle();"><i class="fa fa-plus-square text-info mr-1"></i>총 {{ sizeof($arr) }}건 보기</button>   
                </span>
                @endif
                
            </span>

            <table id="tb_{{ $tname }}" data-order='[[ 4, "asc" ]]' class="table table-sm table-hover card-secondary card-outline mt-1 mb-1 datatable" width='90'>
                <colgroup>
                    <col width="90"/>
                    <col width="90"/>
                    <col width="120"/>
                    <col width="90"/>
                    <col width="100"/>
                    <col width="80"/>
                    <col width="90"/>
                    <col width="120"/>
                    <col width="90"/>
                    <col width="100"/>
                    <col width="80"/>
                    <col width="90"/>
                    <col width="120"/>
                    <col width="120"/>
                </colgroup>
                <thead>
                    <tr>
                        <th style="display:none"></th>
                        <th class="text-center">채권번호</th>
                        <th class="text-center">차입자명</th>
                        <th class="text-center">투자자명</th>
                        <th class="text-center">상품구분</th>
                        <th class="text-center">진행상태</th>
                        <th class="text-center">투자일자</th>
                        <th class="text-center">만기일자</th>
                        <th class="text-center">투자금액</th>
                        <th class="text-center">투자원금상환액</th>
                        <th class="text-center">투자잔액</th>
                        <th class="text-center">수익률(%)</th>
                        <th class="text-center">약정일</th>
                        <th class="text-center">차기수익지급일자</th>
                        <th class="text-center">차기수익지급금액</th>

                        @if(isset($requestUri) && $requestUri=='ipcc')
                        <th class="text-center">대출신청</th>
                        @endif
                    </tr>
                </thead>
                <tbody @if(sizeof($arr)>10 && $searchTypeArr[$tname]['title']=='성명')  id='table{{ $tname }}' style='display:none' @endif>
                    @foreach( $arr as $idx => $v )
                    @if(isset($v->no))
                    <tr style="cursor:pointer;" onclick="window.open('/account/investmentpop?no={{$v->no ?? ''}}','','left=0,top=0,width=1350,height=800,scrollbars=yes')">
                    @else
                    <tr style="cursor:pointer;" onclick="window.open('/erp/custpop?cust_info_no={{$v->cust_info_no ?? ''}}&no={{$v->loan_info_no ?? '' }}','','width=2000, height=1000, scrollbars=yes')">
                    @endif
                        <td class="text-center">{{($v->loan_usr_info_no ? $v->investor_type.$v->investor_no.'-'.$v->inv_seq : '')}}</td>
                        <td class="text-center">{{Func::nameMasking(Func::decrypt($v->cust_name, 'ENC_KEY_SOL'), 'N')}}</td>
                        <td class="text-center">{{$v->name ?? ''}}</td>
                        <td class="text-center">{{Func::getArrayName($arrayConfig['pro_cd'], $v->pro_cd)}}</td>
                        <td class="text-center">{{Func::getInvStatus($v->status)}}</td>
                        <td class="text-center">{{Func::dateFormat($v->contract_date)}}</td>
                        <td class="text-center">{{Func::dateFormat($v->contract_end_date)}}</td>
                        <td class="text-center">{{number_format($v->loan_money)}}</td>
                        <td class="text-center">{{number_format($v->return_origin_sum ?? 0)}}</td>
                        <td class="text-center">{{number_format($v->balance)}}</td>
                        <td class="text-center">{{sprintf('%0.2f',$v->invest_rate)}}</td>
                        <td class="text-center">{{$v->contract_day." 일"}}</td>
                        <td class="text-center">{{Func::dateFormat($v->return_date)}}</td>
                        <td class="text-center">{{number_format($v->return_money)}}</td>
                    </tr>
                    @endforeach
                    {{-- <tr><td colspan=20></td></tr> --}}
                </tbody>
            </table>

            @else
            
            <span id='label_{{ $tname }}' style="display:none;"><b>{{ isset($searchTypeArr[$tname]) ? $searchTypeArr[$tname]['list']." - ".$searchTypeArr[$tname]['title']."(".$searchTypeArr[$tname]['str'].")" : '' }}</b>
            
                @if(sizeof($arr)>10 && $searchTypeArr[$tname]['title']=='성명') 
                <span>
                    <button type="button" class="btn btn-xs btn-outline-info float-right mb-1 ml-4" id="btn{{ $tname }}" onclick="$('#table{{ $tname }}').toggle();"><i class="fa fa-plus-square text-info mr-1"></i>총 {{ sizeof($arr) }}건 보기</button>   
                </span>
                @endif
            </span>

            
            
            <table id="tb_{{ $tname }}" data-order='[[ 4, "asc" ]]' class="table table-sm table-hover card-secondary card-outline mt-1 mb-1 datatable" width='90'>
                <colgroup>
                    <col width="90"/>
                    <col width="90"/>
                    <col width="120"/>
                    <col width="90"/>
                    <col width="100"/>
                    <col width="80"/>
                    <col width="90"/>
                    <col width="90"/>
                    <col width="120"/>
                    <col width="90"/>
                    <col width="100"/>
                    <col width="80"/>
                    <col width="90"/>
                    <col width="90"/>
                    <col width="120"/>
                    <col width="120"/>
                </colgroup>
                <thead>
                    <tr>
                        <th class="text-center">채권번호</th>
                        <th class="text-center">차입자명</th>
                        <th class="text-center">투자자명</th>
                        <th class="text-center">상품구분</th>
                        <th class="text-center">진행상태</th>
                        <th class="text-center">투자일자</th>
                        <th class="text-center">만기일자</th>
                        <th class="text-center">투자개월</th>
                        <th class="text-center">투자금액</th>
                        <th class="text-center">투자원금상환액</th>
                        <th class="text-center">투자잔액</th>
                        <th class="text-center">수익률(%)</th>
                        <th class="text-center">수익지급주기</th>
                        <th class="text-center">약정일</th>
                        <th class="text-center">차기수익지급일자</th>
                        <th class="text-center">차기수익지급금액</th>
                    </tr>
                </thead>
                <tbody @if(sizeof($arr)>10 && $searchTypeArr[$tname]['title']=='성명')  id='table{{ $tname }}' style='display:none' @endif>
                    @foreach( $arr as $idx => $v )
                    @if(isset($v->no))
                    <tr style="cursor:pointer;" onclick="window.open('/account/investmentpop?no={{$v->no ?? ''}}','','left=0,top=0,width=1350,height=800,scrollbars=yes')">
                    @else
                    <tr style="cursor:pointer;" onclick="window.open('/erp/custpop?cust_info_no={{$v->cust_info_no ?? ''}}&no={{$v->loan_info_no ?? '' }}','','width=2000, height=1000, scrollbars=yes')">
                    @endif
                        <td class="text-center">{{($v->loan_usr_info_no ? $v->investor_no.'-'.$v->inv_seq : '')}}</td>
                        <td class="text-center">{{Func::nameMasking(Func::decrypt($v->cust_name, 'ENC_KEY_SOL'), 'N')}}</td>
                        <td class="text-center">{{$v->name ?? ''}}</td>
                        <td class="text-center">{{Func::getArrayName($arrayConfig['pro_cd'], $v->pro_cd)}}</td>
                        <td class="text-center">{{Func::getInvStatus($v->status)}}</td>
                        <td class="text-center">{{Func::dateFormat($v->contract_date)}}</td>
                        <td class="text-center">{{Func::dateFormat($v->contract_end_date)}}</td>
                        <td class="text-center">{{$v->loan_term}}</td>
                        <td class="text-center">{{number_format($v->loan_money)}}</td>
                        <td class="text-center">{{number_format($v->return_origin_sum ?? 0)}}</td>
                        <td class="text-center">{{number_format($v->balance)}}</td>
                        <td class="text-center">{{sprintf('%0.2f',$v->invest_rate)}}</td>
                        <td class="text-center">{{$v->pay_term." 개월"}}</td>
                        <td class="text-center">{{$v->contract_day." 일"}}</td>
                        <td class="text-center">{{Func::dateFormat($v->return_date)}}</td>
                        <td class="text-center">{{number_format($v->return_money)}}</td>

                    </tr>
                    @endforeach
                    {{-- <tr><td colspan=20></td></tr> --}}
                </tbody>
            </table>
            @endif
        @endif
    @empty
        <div class='text-center p-4'>
            등록된 정보가 없습니다.
        </div>
    @endforelse
@endif


<script>

// dataTable 세팅
@if( isset($result) )
    @foreach( $result as $tname => $arr )
        @if( count($arr) > 0 )
            $('#tb_{{ $tname }}').DataTable({
                "paging": false,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": false,
                "autoWidth": false,
                "responsive": true,
            });
            $('#tb_{{ $tname }}_wrapper').addClass('row justify-content-between');
            $('#tb_{{ $tname }}_filter').addClass('mt-2 mr-3');
            $('#tb_{{ $tname }}_wrapper').prepend("<div class='mt-4 ml-2'>"+ $('#label_{{ $tname }}').html()+'</div>');
        @endif
    @endforeach
@endif

// 자기지점만 검색
function branchSearch(chk)
{
@if( isset($result) )
    @foreach( $result as $tname => $arr )
        @if( count($arr) > 0 )
           
            var v{{ $tname }} = $('#tb_{{ $tname }}').DataTable();
            if(chk==true)
            {
                v{{ $tname }}
                .columns( 0 )
                .search('{{ $myBranch }}', false, false, false )
                .draw();
            }
            else
            {
                v{{ $tname }}
                .columns( 0 )
                .search('', false, false, false )
                .draw();
            }
        @endif
    @endforeach
@endif
}

@if (!isset($_COOKIE['only_my_branch']) || $_COOKIE['only_my_branch'] == 'Y') 
    branchSearch(true);
@else
    branchSearch(false);
@endif


function selectLoanInfo(no)
{
    if(confirm(no+'번 계약번호로 고객정보를 가져오시겠습니까? '))
    {
        var url = "/ups/loanappgetloaninfo";
        var formdata = "loan_info_no="+no;
        
        jsonAction(url, 'POST', formdata, function (data) {
            var memo = '';
            if(data!=null)
            {
                console.log(data);

                // 데이터를 넣어준다.
                $('#jumin1', opener.document).val(data.ssn.substring(0,6));
                $('#jumin2', opener.document).val(data.ssn.substring(6));

                // 실거주
                $('#home_zip', opener.document).val(data.zip1);
                $('#home_addr1', opener.document).val(data.addr11);
                $('#old_home_addr1', opener.document).val(data.old_addr11);
                $('#home_addr2', opener.document).val(data.addr12);

                // 전화
                $('#home_phone', opener.document).val(data.ph11+''+data.ph12+''+data.ph13);
                $('#mobile_carrier', opener.document).val(data.ph2_com);
                $('#mobile_phone', opener.document).val(data.ph21+''+data.ph22+''+data.ph23);
                opener.setMoblieNo();
                
                // 직장                
                $('#job_cd_level1', opener.document).val(data.job_div_cd);
                $('#job_codestr', opener.document).val(data.job_codestr);
                $('#job_code', opener.document).val(data.job_cd);
                $('#company_name', opener.document).val(data.com_name);
                $('#office_phone', opener.document).val(data.ph31+''+data.ph32+''+data.ph33);                
                $('#office_zip', opener.document).val(data.zip3);
                $('#office_addr1', opener.document).val(data.addr31);
                $('#old_office_addr1', opener.document).val(data.old_addr31);
                $('#office_addr2', opener.document).val(data.addr32);
                opener.setIncome(data.year_income);
                $('#email1', opener.document).val(data.email1);
                $('#email2', opener.document).val(data.email2);
                $('#housing_type', opener.document).val(data.house_own_cd);
                $('#residence_type', opener.document).val(data.house_type_cd);
                $('#reside_year', opener.document).val(data.stay_year);
                $('#reside_month', opener.document).val(data.stay_months);
                $('#dual_income', opener.document).val(data.double_income_chk);
                $('#worked_year', opener.document).val(data.com_year);
                $('#worked_month', opener.document).val(data.com_months);

                $('#interest_pay_date', opener.document).val(data.contract_day);
            }
            else
            {
                memo = '결재로그 없음';
            }
        });

        $('.collapse').collapse('hide');
    }
    else 
    {

    }    
}
</script>