<div class="card-body p-1">
    <table class="table table-sm table-bordered table-input text-xs">
    <!-- BODY -->
        <colgroup>
            {{-- <col width="15%"/>
            <col width="15%"/>
            <col width="15%"/>
            <col width="20%"/>
            <col width="15%"/>
            <col width="15%"/> --}}
        </colgroup>
        <thead>
            <tr><h3 class="card-title"><i class="fas fa-user m-2" size="9px"></i>KCB 신용정보조회</h3></tr>
            <tr>
                <th class="text-center">이름</th>
                <th class="text-center">주민번호</th>
                <th class="text-center">고객번호</th>
                <th class="text-center">조회자</th>
                <th class="text-center">조회일자</th>
                <th class="text-center">상세보기</th>
            </tr>
        </thead>
        <tbody>
            @forelse( $credit as $idx => $v )
                <tr >
                    <td class="text-center">{{ $v->name }}</td>
                    <td class="text-center">{{ $v->ssn }}</td>
                    <td class="text-center">{{ $v->cust_info_no }}</td>
                    <td class="text-center">{{ $v->worker_id }}</td>
                    <td class="text-center">{{ Func::dateFormat($v->save_time) }}</td>
                    <td class="text-center">
                            <button type="button" class="btn btn-xs btn-outline-info " onclick="custcredit('{{ $v->cust_info_no }}','K');">
                        <i class="fa fa-plus-square text-info -1"></i>상세보기</button>
                    </td>
                </tr>
            @empty
            @endforelse
            <tr><td colspan="13"></td></tr>
        </tbody>
    </table>
<br>
    <table class="table table-sm table-bordered table-input text-xs">
        <!-- BODY -->
            <colgroup>
                {{-- <col width="15%"/>
                <col width="15%"/>
                <col width="15%"/>
                <col width="20%"/>
                <col width="15%"/>
                <col width="15%"/> --}}
            </colgroup>
            <thead>
                <tr><h3 class="card-title"><i class="fas fa-user m-2" size="9px"></i>NICE 신용정보조회</h3></tr>
                <tr>
                    <th class="text-center">이름</th>
                    <th class="text-center">주민번호</th>
                    <th class="text-center">고객번호</th>
                    <th class="text-center">조회자</th>
                    <th class="text-center">조회일자</th>
                    <th class="text-center">상세보기</th>
                </tr>
            </thead>
            <tbody>
                @forelse( $N_credit as $idx => $v )
                    <tr >
                        <td class="text-center">{{ $v->name }}</td>
                        <td class="text-center">{{ $v->ssn }}</td>
                        <td class="text-center">{{ $v->cust_info_no }}</td>
                        <td class="text-center">{{ $v->worker_id }}</td>
                        <td class="text-center">{{ Func::dateFormat($v->save_time) }}</td>
                        <td class="text-center">
                                <button type="button" class="btn btn-xs btn-outline-info " onclick="custcredit('{{ $v->cust_info_no }}','N');">  {{--나중에 nice 화면으로 바꾸기 --}}
                            <i class="fa fa-plus-square text-info -1"></i>상세보기</button>
                        </td>
                    </tr>
                @empty
                @endforelse
                <tr><td colspan="13"></td></tr>
            </tbody>
        </table>