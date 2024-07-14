@include('inc/listSimple2')

<div class="toasts-bottom-right fixed  pr-2  pl-1 col-md-3" id="custRightInput" style="display:none; bottom:2px;">
    <div class="card card-outline primary">
        <div class="card-header flex-column status-border-right-none">
            <h5 class="card-title text-bold" >
                우편물
            </h5>
        </div>
        <form name="postForm" id="postForm" method="post" action="">
            @csrf
            <div class="card-body">
                
                <input type="hidden" name="no" value="{{ $no ?? '' }}">
                <input type="hidden" name="cust_info_no" value="{{ $cust_info_no ?? '' }}">
                <input type="hidden" name="loan_info_no" value="{{ $loan_info_no ?? '' }}">

                <input type="hidden" name="mode" value="">

                <div class="form-group row">
                    <div class="col-md-2">
                        <label class="col-form-label">우편물</label>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control text-xs form-control-sm mr-1 mb-1 mt-1" name="post_cd" >
                            <option value="">선택</option>
                            {{Func::printOption($arr_post_cd, $result['post_cd'] ?? "")}}
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="col-form-label">발송방법</label>
                    </div>
                    <div class="col-md-4">
                        <select class="form-control text-xs form-control-sm mr-1 mb-1 mt-1" name="post_send_cd" >
                            <option value="">선택</option>
                            {{Func::printOption($arr_post_send_cd, $result['post_send_cd'] ?? "")}}
                        </select>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-2">
                        <label class="col-form-label">등기번호</label>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" class="form-control form-control-sm" name="post_no" id="post_no" dateonly="true" onkeyup="onlyNumber(this);"/>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <label class="col-form-label">발송일</label>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group date datetimepicker">
                            <input type="text" class="form-control form-control-sm dateformat" name="post_date" id="post_date" dateonly="true"/>
                            <div class="input-group-append" data-target="#post_date" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group row">
                    <div class="col-md-2">
                        <label class="col-form-label">메모</label>
                    </div>
                    <div class="col-md-10">
                        <textarea class="form-control" name="memo" placeholder=" 메모입력...." rows="7" style="resize:none;"></textarea>
                    </div>
                </div>

            </div>
            <!-- /.card-body -->
            <div class="card-footer" id="input_footer">
                <input type='button' class='btn-sm btn-default float-right' onclick="sendPost('INS');" value='등록'>
                <input type='button' class='btn-sm btn-default float-right' onclick="setPost('');" value='닫기'>
            </div>
            <!-- /.card-footer -->
        </form>
    </div>


</div>


<script>
    $(document).ready(function() {
        // 리스트
        @if (isset($result) && gettype($result) == 'array' && isset($result['listAction']))
        // 진입시 데이터 가져오기
        getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize(), 'FIRST');
        @endif
    });

    $('#post_date').datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
	    useCurrent: false,
    });

    $(function(){
        setInputMask('class', 'dateformat', 'date');
        setInputMask('class', 'moneyformat', 'money');
        setInputMask('class', 'ratioformat', 'ratio');

        $('#custRightInput > .card').css('border-radius','0.5rem');
        $('#custRightInput > .card > .card-header').css('background-color',$("#status_color").val());
        $('#custRightInput > .card > .card-header > .card-title').css('color', '#FFFFFF');
    });

    //  post Input 화면
    function setPost(no)
    {
        if( $('#custRightInput').css('display') === 'block' )
        {
            no = "";
            $('#custRightInput').fadeToggle(0, setcustRightInput(no));
            listSizeSet("B");
        }
        else
        {
            $('#custRightInput').fadeToggle(500, setcustRightInput(no));
            listSizeSet("A");
        }
    }

    function setcustRightInput(no)
    {
        if( no != "" )
        {
            $('#input_footer').html('');
            $('#input_footer').append("<input type='button' class='btn-sm btn-default float-right' onclick=\"sendPost('UPD');\" value='수정'>");
            $('#input_footer').append("<input type='button' class='btn-sm btn-default float-right' onclick=\"sendPost('DEL');\" value='삭제'>");
            $('#input_footer').append("<input type='button' class='btn-sm btn-default float-right' onclick=\"setPost('');\" value='닫기'>");

            // 데이터 가져와서 memo 상세내역 채우기
            $.post("/erp/custpostinput", {no:no}, function(data) {
                
                var mode = JSON.parse(data)['mode'];
                var post = JSON.parse(data)['data'];

                $.each(post, function(index, item){

                    //  데이터 셋팅
                    if( item != null )
                    {
                        $('#postForm :input[name='+index+']').val(item);
                    }
                })
            }); 
        }
        else
        {
            $.each($('#postForm').serializeArray(), function(index, item){

                if( item['name'] != "_token" && item['name'] != "cust_info_no" && item['name'] != "loan_info_no" )
                {
                    $('#postForm :input[name='+item['name']+']').val('');
                }
            })
            
            $('#input_footer').html('');
            $('#input_footer').append("<input type='button' class='btn-sm btn-default float-right' onclick=\"sendPost('INS');\" value='등록'>");
            $('#input_footer').append("<input type='button' class='btn-sm btn-default float-right' onclick=\"setPost('');\" value='닫기'>");
        }
    }


    //  post action
    function sendPost(mode)
    {
        if( mode == "DEL" )
        {
            if( !confirm("삭제 하시겠습니까?") )
            {
                return false;
            }
        }
        else
        {
            if( !confirm("정말로 작업하시겠습니까?") )
            {
                return false;
            }
        }


        
        postForm.mode.value = mode;
        
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#postForm').serialize();

        if(ccCheck()) return;

        $.ajax({
            url  : "/erp/custpostaction",
            type : "post",
            data : postdata,
            success : function(result) {
                globalCheck = false;
                alert(result);
                getRightList('post');

                setPost('');
            },
            error : function(xhr) {
                globalCheck = false;
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });

    }



</script>