@include('inc/listSimple2')


<div class="toasts-bottom-right fixed pr-2 pl-1 col-md-3" id="custRightInput" style="display:none; bottom:2px;" >
    {{-- <hr style="border: solid 1px black;"> --}}
    
    {{-- <table width="100%" style="background-color:#ffffff; border: 2px solid; border-color:red " class="mb-1">
    <tr>
        <td width="60" class="text-center bold ">[중요메모]
            <span id='imemo_save_id'>{{ $imemo['save_id'] ?? '' }}</span><br>
            <span id='imemo_save_time'>{!! $imemo['save_time'] ?? '' !!}</span><br>
            
            <input type='button' class='btn btn-xs btn-info' onclick="saveImemo();" value='등록'>
        </td>
        <td>
            <textarea class="form-control form-control-xs" name="imemo" id="imemo" placeholder="중요메모" rows="4" style="resize:none;" style="width:100%">{{ $imemo['memo'] ?? ''}}</textarea>
        </td>
    </tr>

    </table> --}}
    <div class="card card-outline primary" >
        <form name="memoForm" id="memoForm" method="post" action="">
            @csrf
        {{-- <div class="card-header flex-column status-border-right-none">
            <h5 class="card-title text-bold" id='memo_title' >
                메모 상세
            </h5>
        </div> --}}
            <div class=' text-white' id='memo_title'>
            &nbsp; 메모 입력
            </div>
            <table width="100%">
                <input type="hidden" name="no" value="{{ $no ?? '' }}" >
                <input type="hidden" name="cust_info_no" value="{{ $cust_info_no ?? '' }}" >
                <input type="hidden" name="loan_info_no" id="loan_info_no_custmemo" value="{{ $loan_info_no ?? '' }}" />
                <input type="hidden" name="mode" value="" >
                <input type="hidden" name="memo_color" id="memo_color" value="{{ $memo_color ?? ''}}" >
            <col width="60"></col>
            <col></col>
            <tr>
                <td class="text-center bold">구&nbsp;&nbsp;&nbsp;&nbsp;분</td>
                <td>
                    <table width="100%"><tr><td>
                        {!! Func::printChainOption('메모', $chain_memo_div, 'div', 'sub_div', '', '', '', '', 'N') !!}
                    </td><td width="20">
                        <input type="checkbox" class="form-control form-control-xs" name="important_check" id="important_check" value="Y">
                    </td><td width="60">
                        <label class="col-form-label text-xs" for="important_check">중요메모</label>
                    </td></tr></table>
                </td>
            </tr>

            <tr>
                <td class="text-center bold">연락처</td>
                <td>
                    <table><tr><td>
                        <select class="form-control text-xs form-control-xs" name="ph_cd" >
                            <option value="">연락처</option>
                            {{ Func::printOption($arr_ph_cd, $result['ph_cd']??'') }}
                        </select>
                    </td><td>
                        <input type="text" class="form-control form-control-xs" name="ph_no">
                    </td><td>
                        <select class="form-control text-xs form-control-xs" name="relation_cd" >
                            <option value="">연락대상</option>
                            {{ Func::printOption($arr_relation_cd, $result['relation_cd']??'') }}
                        </select>
                    </td></tr></table>
                </td>
            </tr>

            <tr>
                <td class="text-center bold">약속시간</td>
                <td>
                    <table><tr><td>
                        <div class="input-group date datetimepicker" id="rDateDiv" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm dateformat" name="promise_date" id="promise_date" size="10"  inputmode="text">
                            <div class="input-group-append" data-target="#rDateDiv" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </td><td>
                        <input type="text" class="form-control form-control-xs" maxlength="2" name="promise_hour" style="width:30px">
                    </td><td>
                        시
                    </td><td>
                        <input type="text" class="form-control form-control-xs" maxlength="2" name="promise_min" style="width:30px">
                    </td><td>
                        분
                    </td></tr></table>
                </td>
            </tr>

            <tr>
                <td class="text-center bold">약속금액</td>
                <td>
                    <table><tr><td>
                            <input type="text" class="form-control form-control-xs moneyformat" size=8 name="promise_money">
                    </td><td>
                            원
                    </td><td>
                        {{-- <input type="checkbox" class="form-control form-control-xs" name="promise_alarm" id="promise_alarm" value="Y" >
                    </td><td width="80">
                        <label class="col-form-label " for="promise_alarm">약속시간알람</label> --}}
                    </td></tr></table>
                </td>
            </tr>

            <tr>
                <td class="text-center bold">메&nbsp;&nbsp;&nbsp;&nbsp;모
                    <br>
                    <div class="input-group btn-group">
                        <button type="button" class="btn btn-default btn-xs dropdown-toggle dropdown-icon" data-toggle="dropdown" aria-expanded="false" id="color_area" style="color:white; background-color: {{$memo_color ?? 'black'}};">
                            <span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <div class="dropdown-menu" role="menu">
                            <a class="dropdown-item" style="background-color: red; color: white;" onclick="colorChange(this);">red</a>
                            <a class="dropdown-item" style="background-color: orange; color: white;" onclick="colorChange(this);">orange</a>
                            <a class="dropdown-item" style="background-color: green; color: white;" onclick="colorChange(this);">green</a>
                            <a class="dropdown-item" style="background-color: blue; color: white;" onclick="colorChange(this);">blue</a>
                            <a class="dropdown-item" style="background-color: blueviolet; color: white;" onclick="colorChange(this);">blueviolet</a>
                            <a class="dropdown-item" style="background-color: fuchsia; color: white;" onclick="colorChange(this);">fuchsia</a>
                            <a class="dropdown-item" style="background-color: black; color: white;" onclick="colorChange(this);">black</a>
                        </div>
                    </div>
                </td>
                <td>
                    <textarea class="form-control form-control-xs" name="memo" id="memo_memo" placeholder=" 메모입력...." rows="4" style="resize:none;" ></textarea>

                    <select class="form-control text-xs form-control-xs mr-1 mt-1" id="branch_memo" onchange="memoForm.memo.value = this.value;">
                        <option value="">자주쓰는메모</option>

                    </select>
                </td>
            </tr>
        </table>
        
        <!-- /.card-body -->
        <div class="card-footer  pt-1 pb-1" id="input_footer">
            {{-- 버튼 --}}
        </div>
        <!-- /.card-footer -->
        </div>
        </form>
    </div>



<script>

    $(document).ready(function() {
        // 리스트
        @if (isset($result) && gettype($result) == 'array' && isset($result['listAction']))
        // 진입시 데이터 가져오기
            //getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize(), 'FIRST');
            getMemoList();
        @endif
    });

    function getMemoList()
    {
        getDataList('{{ $result['listName'] }}', 1, '{{ $result['listAction'] }}list', $('#form_{{ $result['listName'] }}').serialize(), 'FIRST');
    }

    // $(function(){
    //     // 진입시 데이터 가져오기

    // });

    // 그냥 적용하는 것으로 변경
    $('.dropdown-toggle').dropdown();
    $('.dropdown-toggle').dropdown('toggle');

    setInputMask('class', 'dateformat', 'date');
    setInputMask('class', 'moneyformat', 'money');
    setInputMask('class', 'ratioformat', 'ratio');
    
    $('#custRightInput > .card').css('border-radius','0.5rem');
    $('#custRightInput > .card > .card-header').css('background-color',$("#status_color").val());
    $('#custRightInput > .card > .card-header > .card-title').css('color', '#FFFFFF');

    $('#memo_title').css('background-color',$("#status_color").val());

    function colorChange(v)
    {
        var color = v.innerHTML;

        memoForm.memo_color.value = color;

        $('#color_area').css('background-color', color);
        $('#memo_memo').css('color', color);
        $('#memo_memo').focus();
    }

    //  memo 상세내역 생성 또는 지우기. 입력창 고정사용안알시 일부 삭제 및 주석 제거
    function setMemo(no)
    {
        var memo_set_boolean = true;
        $.each($('#memoForm').serializeArray(), function(index, item)
        {
            if( item['name'] != "_token" && item['name'] != "cust_info_no" && item['name'] != "loan_info_no" && item['name'] != "div")
            {
                if(  $('#memoForm :input[name='+item['name']+']').val() != '' )
                {
                    memo_set_boolean = false;
                }
            }
        });

        if(memo_set_boolean)
        {
            if(no=='new')
            {
                // $('#custRightInput').fadeToggle(200, setcustRightInput(''));
                // listSizeSet("A");
                setcustRightInput('');  // 입력창 고정안할시 삭제
                return;
            }
            // 입력창 고정안할시 삭제
            else 
            {
                setcustRightInput(no); 
            }
        }
        // if( $('#custRightInput').css('display') === 'block' )
        // {
        //     no = "";
        //     $('#custRightInput').fadeToggle(0, setcustRightInput(no));
        //     listSizeSet("B");
        //     setcustRightInput(no)
        // }
        // else
        // {
        //     $('#custRightInput').fadeToggle(600, setcustRightInput(no));
        //     listSizeSet("A");
        // }
    }

       
    
    function setcustRightInput(no)
    {
        if( no != "" )
        {
            $('#input_footer').html('');
            $('#input_footer').append("<input type='button' class='btn btn-sm btn-info float-right ml-2' onclick=\"sendMemo('INS');\" value='새로등록'>");
            
            $('#input_footer').append("<input type='button' class='btn btn-sm btn-secondary float-left' onclick=\"setMemo('');\" value='취소'>");

            $('#memo_title').html('&nbsp; 메모 상세보기');
        }
        else
        {
            $('#input_footer').html('');
            $('#input_footer').append("<input type='button' class='btn btn-sm btn-info float-right' onclick=\"sendMemo('INS');\" value='등록'>");
            // $('#input_footer').append("<input type='button' class='btn btn-sm btn-secondary float-left' onclick=\"setMemo('');\" value='닫기'>");

            $('#memo_title').html('&nbsp; 메모 입력');
        }

        // 데이터 가져와서 memo 상세내역 채우기
        $.post("/erp/custmemoinput", {no:no}, function(data) {
            
            var mode = JSON.parse(data)['mode'];
            var memo = JSON.parse(data)['data'];
            var branch_memo = JSON.parse(data)['branch_memo'];

            if( memo )
            {

                // 본인이 등록한것만 삭제 수정이 나오게 한다.
                var editDisabled = 'disabled';
                if(memo.save_id=='{{ Auth::id() }}')
                {
                    editDisabled = '';
                }
                $('#input_footer').append("<input type='button' class='btn btn-sm btn-info float-right ml-2' onclick=\"sendMemo('UPD');\" value='수정' "+ editDisabled +">");
                $('#input_footer').append("<input type='button' class='btn btn-sm btn-danger float-right' onclick=\"sendMemo('DEL');\" value='삭제' "+ editDisabled +">");

                $.each(memo, function(key, item){
                    
                    //  데이터 셋팅
                    if( $('#memoForm :input[name='+key+']').attr('type') == "checkbox" )
                    {
                        if( item=="Y" )
                        {
                            $('#memoForm :input[name='+key+']').prop('checked', true);
                        }
                        else
                        {
                            $('#memoForm :input[name='+key+']').prop('checked', false);
                        }
                    }
                    else if( item != null )
                    {
                        $('#memoForm :input[name='+key+']').val(item);
                    }

                    if(key=='sub_div')
                    {
                        getSubSelect($('#select_id_div').val(), 'div', 'sub_div', '메모구분선택', item, 'N');
                    }

                });

                $('#color_area').css('background-color', memo['memo_color']);
                $('#memo_memo').css('color', memo['memo_color']);
                // $('#select_id_div').selectpicker('refresh'); 
                // $('#select_id_sub_div').selectpicker('refresh'); 
            }
            else
            {
                $.each($('#memoForm').serializeArray(), function(index, item)
                {
                    if( item['name'] != "_token" && item['name'] != "cust_info_no" && item['name'] != "loan_info_no" )
                    {
                        if( $('#memoForm :input[name='+item['name']+']').attr('type') == "checkbox" )
                        {
                            $('#memoForm :input[name='+item['name']+']').prop('checked', false);
                        }
                        else
                        {
                            $('#memoForm :input[name='+item['name']+']').val('');
                        }
                    }
                });
                
                // 기본 메모 구분 세팅. 13.독촉
                var defaultMemoCode = '13';
                $('#select_id_div').val(defaultMemoCode);
                // $('#select_id_div').selectpicker('refresh'); 
                getSubSelect($('#select_id_div').val(), 'div', 'sub_div', '메모구분선택', '', 'N');

                $('#color_area').css('background-color', 'black');
                $('#memo_memo').css('color', 'black');
                $('#memo_memo').focus();
            }



            if( branch_memo )
            {
                var str = "<option value=''>자주쓰는메모</option>";

                $.each(branch_memo, function(idx, v){

                    str += "<option value='"+v['memo_content']+"'>"+v['memo_content']+"</option>";

                    $('#branch_memo').html(str);
                })
            }

        }); 
    }

    function sendMemo(mode)
    {
        // 메모 구분은 필수로 넣어야 한다.
        if(mode=='INS' || mode=='UPD')
        {
            if(memoForm.div.value=='')
            {
                alert('메모구분을 선택해 주세요.');
                memoForm.div.focus();
                return false;
            }
        }
        
        if( mode=='DEL' && !confirm("정말로 삭제하시겠습니까?") )
        {
            return false;
        }

        memoForm.mode.value = mode;
        
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = $('#memoForm').serialize();

        if(ccCheck()) return;

        $.ajax({
            url  : "/erp/custmemoaction",
            type : "post",
            data : postdata,
            success : function(result) {
                globalCheck = false;
                
                if(result!='Y')
                {
                    alert(result);
                }
                setMemo('');
                getRightList('memo');
                
            },
            error : function(xhr) {
                globalCheck = false;
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }

    function saveImemo()
    {
        if(!confirm('중요메모를 저장하시겠습니까?'))
        {
            return false;
        }

        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = 'cust_info_no={{ $cust_info_no ?? '' }}&important_memo=' + $('#imemo').val();

        if(ccCheck()) return;

        $.ajax({
            url  : "/erp/custimportmemoaction",
            type : "post",
            data : postdata,
            success : function(result) {
                globalCheck = false;
                if(result.save_id=='')
                {
                    alert('중요메모 저장중 오류가 발생했습니다. 관리자에게 문의해 주세요.');
                }
                else 
                {
                    $('#imemo_save_id').text(result.save_id);
                    $('#imemo_save_time').html(result.save_time);
                    alert('저장되었습니다.');
                }
                
                
            },
            error : function(xhr) {
                globalCheck = false;
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }

    $('#custRightInput').fadeToggle(200, setcustRightInput(''));
    // listSizeSet("A");
    $('#rightList').css('max-height', '500px');
    $('#color_area').click();
    // $('#select_id_div').css('width', '100px');
    // $('#select_id_sub_div').css('width', '100px');
</script>