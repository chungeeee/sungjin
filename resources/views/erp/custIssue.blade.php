<script>

    function custIssueAction(mode)
    {
        issue_form.mode.value = mode;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        if( $("#issue_div_cd").val()=="" )
        {
            alert("발급서류를 선택해주세요.");
            return false;
        }
        if( $("#issue_date").val()=="" )
        {
            alert("발급일자를 입력해주세요.");
            return false;
        }
        /*
        if( $("#issue_fee_money").val()=="" )
        {
            alert("발급서류를 선택해주세요.");
            return false;
        }
        */


        var postdata = new FormData($('#issue_form')[0]);
        if(ccCheck()) return;

        $.ajax({
            url  : "/erp/custissueaction",
            type : "post",
            data : postdata,
            processData : false,
            contentType : false,
            success : function(result) {
                globalCheck = false;
                if( result=="Y" )
                {
                    alert("정상처리되었습니다.");
                    getCustData("issue");
                }
                else
                {
                    alert(result);
                }
            },
            error : function(xhr) {
                globalCheck = false;
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });

    }
</script>

<div class="p-2 needs-validation">
    <b>발급서류 및 수수료</b>
    <!-- BODY -->
    <button type="button" class="btn btn-xs btn-outline-info float-right mb-1" onclick="getCustData('issue');"><i class="fa fa-plus-square text-info mr-1"></i>발급서류추가</button>
    <table class="table table-sm table-hover loan-info-table card-secondary card-outline">
        <thead>
            <tr>
                <th class="text-center">순번</th>
                <th class="text-center">발급서류</th>
                <th class="text-center">발급일</th>
                <th class="text-center">발급수수료</th>
                <th class="text-center">메모</th>
                <th class="text-center">지점</th>
                <th class="text-center">등록일시</th>
                <th class="text-center">등록사번</th>
            </tr>
        </thead>
        <tbody>
            @forelse( $issue_list as $cur_seq => $v )
                <tr onclick="getCustData('issue','',{{ $v['seq'] }});" @if( $cur_seq==$seq ) bgcolor="FFDDDD" @endif >
                <td class="text-center">{{ $v['seq'] }}</td>
                <td class="text-center">{{ Func::nvl($array_issue_div_cd[$v['issue_div_cd']], $v['issue_div_cd']) }}</td>
                <td class="text-center">{{ Func::dateFormat($v['issue_date']) }}</td>
                <td class="text-center">{{ number_format($v['issue_fee_money']) }}</td>
                <td class="text-center">{{ $v['issue_memo'] }}</td>
                <td class="text-center">{{ $v['manager_code'] }}</td>
                <td class="text-center">{{ Func::dateFormat($v['save_time']) }}</td>
                <td class="text-center">{{ $v['save_id'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class='text-center p-4'>등록된 발급서류가 없습니다.</td>
                </tr>
            @endforelse
            <tr><td colspan="13"></td></tr>
        </tbody>
    </table>



    <form id="issue_form" name="issue_form" method="post" action="" onSubmit="return false;">
    @csrf
    <input type="hidden" name="cust_info_no" value="{{ $cust_info_no ?? '' }}">
    <input type="hidden" name="loan_info_no" value="{{ $loan_info_no ?? '' }}">
    <input type="hidden" name="mode" value="{{ $mode ?? '' }}">
    <input type="hidden" name="seq" value="{{ $issue_data['seq'] ?? '' }}">

    <div class="row">
        <div class="col-md-6">
            <table class="table table-sm table-bordered table-input text-xs">

                <colgroup>
                    <col width="25%"/>
                    <col width="75%"/>
                </colgroup>

                <tbody>
                    <tr>
                        <th>발급서류구분</th>
                        <td>
                            <select class="form-control text-xs form-control-sm col-md-4" name="issue_div_cd" id="issue_div_cd">
                            <option value=''>선택</option>
                                {{ Func::printOption($array_issue_div_cd, Func::nvl($issue_data['issue_div_cd'],"01")) }}
                            </select>
                        </td>
                    </tr>
                    <tr>
                        <th>발급일</th>
                        <td>
                            <div class="input-group date datetimepicker" id="div_issue_date" data-target-input="nearest">
                                <input type="text" class="form-control form-control-sm" id="issue_date" name="issue_date" DateOnly='true' placeholder="발급일" value="{{ Func::nvl($issue_data['issue_date'],date('Y-m-d')) }}"/>
                                <div class="input-group-append" data-target="#div_issue_date" data-toggle="datetimepicker">
                                    <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>발급수수료</th>
                        <td>
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm text-right moneyformat" id="issue_fee_money" name="issue_fee_money" placeholder="원단위 입력" value="{{ number_format(Func::nvl($issue_data['issue_fee_money'],5000)) }}">
                            <div class="input-group-append">
                                <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fas fa-won-sign"></i></div>
                            </div>
                        </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="col-md-6">
            <table class="table table-sm table-bordered table-input text-xs">
            <colgroup>
                <col width="25%"/>
                <col width="75%"/>
            </colgroup>
            <tbody>
            <tr>
                <th>메모</th>
                <td>
                    <textarea class="form-control form-control-xs" name="issue_memo" id="issue_memo" placeholder=" 메모입력...." rows="4" style="resize:none;" >{{ Func::nvl($issue_data['issue_memo'],'') }}</textarea>
                </td>
            </tr>
            <tr>
                <td class="text-right" colspan=2>
                    @if( !isset($mode) || ( isset($mode) && $mode == "INS" ) )
                    <button class="btn btn-sm btn-info" onclick="custIssueAction('INS');">저장</button>
                    @elseif( isset($mode) && $mode == "UPD" )
                    <button class="btn btn-sm btn-danger" onclick="custIssueAction('DEL');">삭제</button>
                    <!--<button class="btn btn-sm btn-info" onclick="custIssueAction('UPD');">수정</button>-->
                    @endif
                </td>
            </tr>            
            </tbody>
            </table>
        </div>
    </div>
    </form>

</div>
<script>

$('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
	    useCurrent: false,
});

setInputMask('class', 'moneyformat', 'money');

</script>