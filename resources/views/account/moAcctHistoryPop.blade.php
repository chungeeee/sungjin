@extends('layouts.masterPop')
@section('content')

<style>
    .bg-light-pink {
        background-color: #f7d2df;
    }
    .bg-light-blue {
        background-color: #E1F5FE;
    }
    .table-hover tbody tr:hover {
        background-color: #dbd9d9;
    }
    .modal-dialog-custom {
        max-width:650px; 
        width: 70%;
    }
</style>

<div class="card card-lightblue">
    <div class="card-header-no-round">
    <h2 class="card-title font-weight-bold">거래내역</h2>
    </div>
</div>

<div class="p-2">
    <table class="table table-sm table-hover loan-info-table card-secondary card-outline table-bordered">
    <colgroup>
        <col width="14%"/>
        <col width="16%"/>
        <col width="14%"/>
        <col width="14%"/>
        <col width="14%"/>
        <col width="14%"/>
        <col width="14%"/>
    </colgroup>
    <thead>
        <tr class="text-center">
            <th>은행명</th>
            <th>계좌번호</th>
            <th>통장구분</th>
            <th>거래지점</th>
            <th>최초잔액</th>
            <th>현재잔액</th>
            <th>저장일</th>
        </tr>
    </thead>
    <tbody>
        <tr class="text-center">
            <td>{{ $rslt->mo_bank_cd}}</td>
            <td>{{ $rslt->mo_ssn }}</td>
            <td>{{ $rslt->mo_acct_div_cd }}</td>
            <td>{{ $rslt->trade_branch_cd }}</td>
            <td>{{ $rslt->first_money }}</td>
            <td>{{ $rslt->now_money }}</td>
            <td>{{ Func::dateFormat2($rslt->save_time)  }}</td>
        </tr>
    </tbody>
    </table>
</div>

<div class="p-2">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h6 class="m-0">&nbsp;&nbsp;{{ $rslt->no }}번 계약 거래원장</h6>
        <div>
            <button type="button" class="btn btn-sm btn-info mr-1" onclick="setMoAcctInMoneyForm('{{ $rslt->no }}');">입금처리</button>
            <button type="button" class="btn btn-sm btn-danger" onclick="setMoAcctOutMoneyForm('{{ $rslt->no }}');">출금처리</button>
        </div>
    </div>
    <table class="table table-sm table-hover loan-info-table card-secondary card-outline table-bordered">
        <colgroup>
            <col width="10%"/>
            <col width="10%"/>
            <col width="15%"/>
            <col width="14%"/>
            <col width="15%"/>
            <col width="17%"/>
            <col width="13%"/>
            <col width="6%"/>
        </colgroup>
        <thead>
            <tr class="text-center">
                <th>처리</th>
                <th>구분</th>
                <th>입/출금액</th>
                <th>입/출금일</th>
                <th>현재잔액</th>
                <th>처리시간</th>
                <th>담당자</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse ($trade_rslt as $key => $v)
            <tr class="text-center {{ $v->section=='I' ? 'bg-light-blue' : 'bg-light-pink' }}">
                <td>
                    @if ($v->section=='I')
                        @if ($v->type=='0')
                            일반입금
                        @else
                            기타입금
                        @endif
                    @else
                        @if ($v->type=='0')
                            대출
                        @elseif ($v->type=='1')
                            기타출금
                        @else
                            가수금반환
                        @endif
                    @endif
                </td>
                <td>{{ $v->no }} {{ $v->section=='I' ? "입금" : "출금" }}</td>
                <td>{{ $v->section=='I' ? number_format($v->in_money) : number_format($v->out_money)  }}</td>
                <td>{{ $v->save_date }}</td>
                <td>{{ number_format($v->now_money) }}</td>
                <td>{{ Func::dateFormat($v->save_time) }}</td>
                <td>{{ $v->save_id }}</td>
                <td>
                    @if ($key == 0) <!-- 첫 번째 행인 경우에만 삭제 버튼 생성 -->
                        <form id='hiddenDelData' method="post" role="form">
                            <input type="hidden" name="hiddenNo" class="hiddenNo" value="{{ $rslt->no }}">
                            <input type="hidden" name="hiddenTradeNo" class="hiddenTradeNo" value="{{ $v->no }}">
                            <input type="hidden" name="hiddenSection" class="hiddenSection" value="{{ $v->section }}">
                            <input type="hidden" name="hiddenMoney" class="hiddenMoney" value="{{ $v->section=='I' ? $v->in_money : $v->out_money }}">
                        </form> 
                        <button type="button" class="btn btn-xs btn-danger" id="moAcct_btn_del" onclick="moAcctDelHistoryAction('');">삭제</button>
                    @endif
                </td>
            </tr>
            @empty
            <tr>
                <td class="text-center p-3" colspan="7">
                    등록된 거래내역이 없습니다.
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>
</div>
</form>

<div class="modal fade" id="moAcctInMoneyModal">
    <div class="modal-dialog modal-dialog-custom">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">입금 등록</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="moAcctInMoneyForm">
                [[[CONTENTS]]]
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
                <div class="p-0">
                    <button type="button" class="btn btn-sm btn-info" onclick="moAcctInMoneyAction('');">저장</button>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->

<div class="modal fade" id="moAcctOutMoneyModal">
    <div class="modal-dialog modal-dialog-custom">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">출금 등록</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="moAcctOutMoneyForm">
                [[[CONTENTS]]]
            </div>
            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
                <div class="p-0">
                    <button type="button" class="btn btn-sm btn-info" onclick="moAcctOutMoneyAction('');">저장</button>
                </div>
            </div>
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /.modal -->
@endsection




@section('javascript')
<script>

    /** 법인통장 입금처리 폼(모달) 세팅 */
    function setMoAcctInMoneyForm(no)
    {
        $("#moAcctInMoneyModal").modal('show');

        // CORS 에러방지
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // 로딩 스피너
        $("#moAcctInMoneyForm").html(loadingString);

        // 데이터 가져오기
        $.post("/account/moacctinmoneyform", {no:no}, function(data) {
            $("#moAcctInMoneyForm").html(data);
            enterClear();

            // Datepicker 설정
            $('#div_save_date').datetimepicker({
                format: 'YYYY-MM-DD',
                locale: 'ko',
                useCurrent: false,
            });
            afterAjax();
        });
    }

    
    /** 법인통장 입금처리 저장 */
    function moAcctInMoneyAction()
    {
        // if(md=="DEL")
        // {
        //     if(!confirm('정말삭제하시겠습니까?'))
        //     {
        //         return;
        //     }

        //     $("#moAcct_mode").val('DEL');        
        // }
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#mo_in_money_form').serialize();

        $.ajax({
            url  : "/account/moacctinmoneyaction",
            type : "post",
            data : postdata,
            success : function(result)
            {
                if(result.error) 
                {
                    alertErrorMsg(result.error);
                }
                
                if(result.rs_code=='Y')
                {
                    alert(result.rs_msg);
                    location.reload();
                }
                else if(result.rs_code=='N')
                {
                    alert(result.rs_msg);
                }
            },
            error : function(xhr)
            {
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }

    // 엔터막기
    function enterClear()
    {
        $('input[type="text"]').keydown(function() {
            if (event.keyCode === 13)
            {
                event.preventDefault();
            };
        });

        $("input[data-bootstrap-switch]").each(function() {
            $(this).bootstrapSwitch('state', $(this).prop('checked'));
        });
    }

    
    /** 법인통장 출금처리 폼(모달) 세팅 */
    function setMoAcctOutMoneyForm(no)
    {
        $("#moAcctOutMoneyModal").modal('show');

        // CORS 에러방지
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        
        // 로딩 스피너
        $("#moAcctOutMoneyForm").html(loadingString);

        // 데이터 가져오기
        $.post("/account/moacctoutmoneyform", {no:no}, function(data) {
            $("#moAcctOutMoneyForm").html(data);
            enterClear();

            // Datepicker 설정
            $('#div_save_date').datetimepicker({
                format: 'YYYY-MM-DD',
                locale: 'ko',
                useCurrent: false,
            });
            afterAjax();
        });
    }
   
    /** 법인통장 출금처리 저장 */
    function moAcctOutMoneyAction()
    {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#mo_out_money_form').serialize();

        $.ajax({
            url  : "/account/moacctoutmoneyaction",
            type : "post",
            data : postdata,
            success : function(result)
            {
                if(result.error) 
                {
                    alertErrorMsg(result.error);
                }
                
                if(result.rs_code=='Y')
                {
                    alert(result.rs_msg);
                    location.reload();
                }
                else if(result.rs_code=='N')
                {
                    alert(result.rs_msg);
                }
            },
            error : function(xhr)
            {
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }

    /** 법인통장 출금처리 저장 */
    function moAcctDelHistoryAction()
    {

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#hiddenDelData').serialize();

        $.ajax({
            url  : "/account/moacctdelhistoryaction",
            type : "post",
            data : postdata,
            success : function(result)
            {
                if(result.error) 
                {
                    alertErrorMsg(result.error);
                }
                
                if(result.rs_code=='Y')
                {
                    alert(result.rs_msg);
                    location.reload();
                }
                else if(result.rs_code=='N')
                {
                    alert(result.rs_msg);
                }
            },
            error : function(xhr)
            {
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }
</script>
@endsection

