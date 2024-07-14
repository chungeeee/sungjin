
<script src="/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>


<div class="p-2 needs-validation">
    <b>녹취파일</b>
    <!-- BODY -->
    <button type="button" class="btn btn-xs btn-outline-info float-right mb-1" onclick="getCustData('wav');"><i class="fa fa-plus-square text-info mr-1"></i>녹취파일추가</button>
    <table class="table table-sm table-hover loan-info-table card-secondary card-outline">
        <colgroup>
            <col width="20%"/>
            <col width="15%"/>
            <col width="15%"/>
            <col width="20%"/>
            <col width="30%"/>
        </colgroup>
        <thead>
            <tr>
                <th class="text-center">구분</th>
            <th class="text-center">계약번호</th>
                <th class="text-center">등록자</th>
                <th class="text-center">등록일시</th>
                <th class="text-center">파일명</th>
            </tr>
        </thead>
        <tbody>
            @forelse( $wav as $idx => $v )
                <tr onclick="getCustData('wav','',{{ $v->no }});" @if( isset($selected_wav[0]->no) && $selected_wav[0]->no == $v->no ) bgcolor="FFDDDD" @endif >
                    <td class="text-center">{{ Func::getArrayName($arr_wav_div, $v->wav_div_cd) }}</td>
                    <td class="text-center">{{ $v->loan_info_no }}</td>
                    <td class="text-center">{{ $v->worker_id }}</td>
                    <td class="text-center">{{ $v->save_time }}</td>
                    <td class="text-center">{{ $v->origin_filename }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="13" class='text-center p-4'>등록된 녹취 파일이 없습니다.</td>
                </tr>
            @endforelse
            <tr><td colspan="13"></td></tr>
        </tbody>
    </table>

    <div class="row">
        <div class="col-md-6">
            <form id="wav_form" name="wav_form" method="post" enctype="multipart/form-data" action="" >
            @csrf
            <input type="hidden" name="page_div" value="ERP">
            <input type="hidden" name="cust_info_no" value="{{ $cust_info_no ?? '' }}">
            <input type="hidden" name="mode" value="{{ $mode?? '' }}">
            <input type="hidden" name="no" value="{{ $selected_wav[0]->no ?? '' }}">
            <input type="hidden" name="loan_info_no" value="{{ $loan_info_no ?? '' }}">
            <input type="hidden"name="folder_name" id="folder_name" value="{{ $selected_wav[0]->folder_name ?? '' }}">
            <input type="hidden"name="server_url" id="server_url" value="{{ $selected_wav[0]->server_url ?? '' }}">
            <table class="table table-sm table-bordered table-input text-xs">
                <colgroup>
                    <col width="25%"/>
                    <col width="75%"/>
                </colgroup>

                <tbody>
                    <tr>
                        <th>구분</th>
                        <td>
                            <select class="form-control text-xs form-control-xs col-md-4" name="wav_div_cd" id="wav_div_cd">
                            <option value=''>선택</option>
                                {{ Func::printOption($arr_wav_div, isset($selected_wav[0]->wav_div_cd)? $selected_wav[0]->wav_div_cd : "") }}
                            </select>
                        </td>
                    </tr>

                    @if( isset($selected_wav[0]) && $selected_wav[0]->origin_filename )
                    <tr>
                        <th>파일명</th>
                        <td class="pl-1" style="height:31px;">
                        <a href="/erp/downcustwav/{{$selected_wav[0]->no}}" download="{{$selected_wav[0]->origin_filename}}"><span class="hand text-blue"><i class="fas fa-file-download pr-1"></i>{{$selected_wav[0]->origin_filename}}</span></a>
                        </td>
                    </tr>
                    @endif
                    <tr>
                        <th>파일첨부</th>
                        <td>
                            <div class="input-group custom-file">
                                <input type="file" class="custom-file-input form-control-xs text-xs" id="customFile" name="customFile" style="cursor:pointer;">
                                <label class="custom-file-label mb-0 text-xs form-control-xs" for="customFile">Choose file</label>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>녹취서버</th>
                        <td>
                            <div class="input-group">
                                <!-- /btn-group -->
                                <input type="text" class="form-control form-control-xs" id="server_filename" name="server_filename" readonly>
                                <div class="input-group-append">
                                    <button type="button" class="btn btn-info btn-xs pl-3 pr-3" onclick="getRecordList();">찾기</button>
                                </div>
                            </div>
                        </td>
                    </tr>
                    <tr>
                        <th>메모</th>
                        <td>
                            <textarea class="form-control form-control-xs" name="memo" id="memo" placeholder=" 메모입력...." rows="4" style="resize:none;" >{{$selected_wav[0]->memo ??"" }}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right" colspan=2>
                            @if( !isset($mode) || (isset($mode) && $mode == "INS") )
                            <button class="btn btn-sm btn-info" type="button" onclick="wavAction('INS');">저장</button>
                            @elseif( isset($mode) && $mode == "UPD" )
                            <button class="btn btn-sm btn-info" type="button" onclick="wavAction('DEL');">삭제</button>
                            <button class="btn btn-sm btn-info" type="button" onclick="wavAction('UPD');">수정</button>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
            </form>
        </div>

        <div class="col-md-6 text-center" >
            @if( isset($selected_wav[0]->server_url_real) )
                <audio controls>
                    <source src="{{ $selected_wav[0]->server_url_real }}" type="audio/{{$selected_wav[0]->extension}}">
                </audio>
            @endif

        </div>
    </div>


</div>


<script>


    function wavAction(mode)
    {
        wav_form.mode.value = mode;
        
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = new FormData($('#wav_form')[0]);

        if(ccCheck()) return;

        $.ajax({
            url  : "/erp/custwavaction",
            type : "post",
            data : postdata,
            processData : false,
            contentType : false,
            success : function(result) {
                globalCheck = false;
                alert(result);
                getCustData("wav");
            },
            error : function(xhr) {
                globalCheck = false;
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });

    }

    function getRecordList()
    {
        $('#wav_form').attr("action", '/config/record');
        $('#wav_form').attr("method", "post");
        $('#wav_form').attr("target", "popOpen");
        window.open('녹취파일검색','popOpen','right=0,top=0,height=950,width=890,scrollbars=yes');
        $('#wav_form').submit();
    }

    bsCustomFileInput.init();
</script>