<!-- 메모내용 -->
<div class="col-md-12 p-0 m-0 " >
    <div class="card-header p-1" style="border-bottom:none !important;">
        <h6 class="card-title">메모</h6>
        <div class="card-tools pr-2">
        </div>
    </div>
    <div class="card-body" id="investmentinfoMemo">
        <div class="col-md-12">
            <table class="table table-sm card-secondary card-outline table-hover mt-0">
            <colgroup>
                <col width="10%">
                <col width="55%">
                <col width="20%">
                <col width="15%">
            <colgroup>
            <thead>
                <tr>
                    <th class="text-center">채권번호</th>
                    <th class="text-center">메모내용</th>
                    <th class="text-center">등록자</th>
                    <th class="text-center">등록시간</th>
                </tr>
            </thead>
            <tbody id="inputMbody">
                @forelse( $memos as $v )
                    <tr onclick="setcustRightInput({{$v->no}})" class="hand">
                        <td class="text-center">
                            {{ $v->investor_type.$v->investor_no }}-{{ $v->inv_seq }}
                        </td>
                        <td class="text-center">
                            {{ $v->memo }}
                        </td>
                        <td class="text-center">
                            {{ Func::getUserId($v->save_id)->name }}
                        </td>
                        <td class="text-center">
                            {{ Func::dateFormat($v->save_time) }}
                        </td>
                    </tr>
                @empty
                <tr>
                    <td colspan="15" class='text-center p-4'>등록된 메모가 없습니다.</td>
                </tr>
                @endforelse
            </tbody>
            </table>
            
            <form  class="mb-0" name="investment_memo_form" id="investment_memo_form" method="post" enctype="multipart/form-data">
            <input type="hidden" id="loan_info_no" name="loan_info_no" value="{{ $result['customer']['loan_info_no'] ?? '' }}">
            <input type="hidden" id="cust_info_no" name="cust_info_no" value="{{ $result['customer']['cust_info_no'] ?? '' }}">
            <input type="hidden" id="loan_usr_info_no" name="loan_usr_info_no" value="{{ $result['customer']['loan_usr_info_no'] ?? '' }}">
            <input type="hidden" name="mode" value="" >
            <input type="hidden" name="no" value="" >
                <div class="row">
                    <div class="col-md-12">
                        <table class="table table-sm table-bordered table-input text-xs col-md-5">
                        <colgroup>
                        <col width="20%"/>
                        <col width="80%"/>
                        </colgroup>
                        <tr>
                            <th class="text-center bold">메&nbsp;&nbsp;&nbsp;&nbsp;모</th>
                            <td>
                                <textarea class="form-control form-control-xs" name="memo" id="memo" placeholder=" 메모입력...." rows="4" style="resize:none;" ></textarea>
                            </td>
                        </tr>
                        </table>

                        <div class="pt-1 pb-1" id="input_footer">
                            <input type='button' class='btn btn-sm btn-info float-left' onclick="sendMemo('INS');" value='등록'>
                        </div>
                    </div>            
                </div>
            </form>
        </div>
    </div>
</div>

<script>

setcustRightInput();

function sendMemo(mode)
{
    if( mode=='DEL' && !confirm("정말로 삭제하시겠습니까?") )
    {
        return false;
    }

    investment_memo_form.mode.value = mode;
    
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#investment_memo_form').serialize();

    if(ccCheck()) return;

    $.ajax({
        url  : "/account/investmemoaction",
        type : "post",
        data : postdata,
        success : function(result) {
            globalCheck = false;
            
            // 성공알림 
            if(result.rs_code=="Y") 
            {
                alert(result.result_msg);
                getInvestmentData('investmentmemo');
            }
            // 실패알림
            else 
            {
                alert(result.result_msg);
            }            
        },
        error : function(xhr) {
            globalCheck = false;
            alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
}

function setcustRightInput(no)
{
    if( no == "" )
    {
        $('#input_footer').html("<input type='button' class='btn btn-sm btn-info float-right' onclick=\"sendMemo('INS');\" value='등록'>");
    }

    // 데이터 가져와서 memo 상세내역 채우기
    $.post("/account/investmemoinput", {no:no}, function(data) {
        
        var mode = JSON.parse(data)['mode'];
        var memo = JSON.parse(data)['data'];

        if( memo )
        {
            // 기존메모값 세팅
            $.each(memo, function(key, item)
            {
                $('#investment_memo_form :input[name='+key+']').val(item);
            })

            // 본인이 등록한것만 삭제 수정이 나오게 한다.
            var editDisabled = 'disabled';
            if(memo.save_id=='{{ Auth::id() }}')
            {
                editDisabled = '';
            }

            $('#input_footer').html("<input type='button' class='btn btn-sm btn-info float-right ml-2' onclick=\"sendMemo('INS');\" value='새로등록'><input type='button' class='btn btn-sm btn-secondary float-left' onclick=\"setcustRightInput('');\" value='취소'><input type='button' class='btn btn-sm btn-info float-right ml-2' onclick=\"sendMemo('UPD');\" value='수정' "+ editDisabled +"><input type='button' class='btn btn-sm btn-danger float-right' onclick=\"sendMemo('DEL');\" value='삭제' "+ editDisabled +">");
        }
    }); 
}

</script>