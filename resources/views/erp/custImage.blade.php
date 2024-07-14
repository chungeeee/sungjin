
<script src="/plugins/bs-custom-file-input/bs-custom-file-input.min.js"></script>
<script type="text/javascript" src="/plugins/tif/tiff.min.js"></script>
<style>
	#Wrap_spinner
	{
		position:absolute;
		filter:alpha(opacity:'075');
		background-color:#F0F5FF;
		z-index:99;
		top:0px;
		left:0px;
		display:none;
	}
	#spinner
	{
		width:200px;
		height:200px;
		top:25%;
		left:50%;
		margin-left:-100px;
		position:absolute;
		background:url('/img/Spinner.gif') no-repeat 0 0;
	}

	/* tiff viewer CSS */
	.page_wrap {
		text-align:center;
		font-size:0;
	}
	.page_nation {
		display:inline-block;
	}
	.page_nation .none {
		display:none;
	}
	.page_nation a {
		display:block;
		margin:0 3px;
		float:left;
		border:1px solid #e6e6e6;
		width:28px;
		height:28px;
		line-height:28px;
		text-align:center;
		background-color:#fff;
		font-size:13px;
		color:#999999;
		text-decoration:none;
	}
	.page_nation .arrow {
		border:1px solid #ccc;
	}
	.page_nation .pprev {
		background:#f8f8f8 url('img/page_pprev.png') no-repeat center center;
		margin-left:0;
	}
	.page_nation .prev {
		background:#f8f8f8 url('img/page_prev.png') no-repeat center center;
		margin-right:7px;
	}
	.page_nation .next {
		background:#f8f8f8 url('img/page_next.png') no-repeat center center;
		margin-left:7px;
	}
	.page_nation .nnext {
		background:#f8f8f8 url('img/page_nnext.png') no-repeat center center;
		margin-right:0;
	}
	.page_nation a.active {
		background-color:#42454c;
		color:#fff;
		border:1px solid #42454c;
	}

</style>

<script>

    function imgAction(mode)
    {
        if( !confirm("정말로 작업 하시겠습니까?") )
        {
            return false;
        }

        img_form.mode.value = mode;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        var postdata = new FormData($('#img_form')[0]);

        if( $('#customFile')[0].files[0] )
        {
            postdata.append('fileObj', $('#customFile')[0].files[0]);
        }

        if(ccCheck()) return;

        $.ajax({
            url  : "/erp/custimgaction",
            type : "post",
            data : postdata,
            processData : false,
            contentType : false,
            success : function(result) {
                globalCheck = false;
                alert(result);
                getCustData("image");
            },
            error : function(xhr) {
                globalCheck = false;
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }

    function fileDownload()
    {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
            }
        });

        var postdata = new FormData($('#img_form')[0]);

        if(ccCheck()) return;

        $.ajax({
            url  : "/erp/downcustimg",
            type : "post",
            data : postdata,
            processData : false,
            contentType : false,
            success : function(result) {
                globalCheck = false;
                if(result != 'E')
                {
                    location.href = result;
                }
                else
                {
                    alert('해당 파일이 존재하지않습니다.')
                }
                getCustData("image");
            },
            error : function(xhr) {
                globalCheck = false;
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });
    }


    // 발송사유 선택 시 해당 문자코드에 작성된 SMS문장 출력
    function change_div(div)
    {   
        var task_div = div.options[div.selectedIndex].value;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url  : "/erp/custtaskdiv",
            type : "post",
            data : {task_div:task_div},
                success : function(result)
                {
                    $("#img_div_cd").html(result);
                },
                error : function(xhr)
                {
                    alert("통신오류입니다. 관리자에게 문의해주세요.");
                }
            });   
    }

    function getFaxList()
    {
        $('#img_form').attr("action", '/config/faxrecvhistorypop');
        $('#img_form').attr("method", "post");
        $('#img_form').attr("target", "popOpen");
        window.open('팩스파일검색','popOpen','right=0,top=0,height=680,width=1445');
        $('#img_form').submit();
    }

    
	// TIFF 뷰어 - limit 추가 2022-05-13
	function tiff_view(src_filename, action)									
	{
		document.getElementById("srcfilename").value = src_filename;
		
		var n = document.getElementById("page").value;
		var n2 = document.getElementById("page").value;
		Tiff.initialize({TOTAL_MEMORY: 16777216 * 10});
		var xhr = new XMLHttpRequest();
		xhr.responseType = 'arraybuffer';
		xhr.open('GET', src_filename);
		xhr.onload = function (e) {
	
			var tiff = new Tiff({buffer: xhr.response});
			var total = tiff.countDirectory();	// 파일 전체장수
			// console.log(total);

			tiff.setDirectory(n);	// 보여줄 이미지 지정
			var canvas = tiff.toCanvas();	// Canvas 사용

			// 최초 진입							
			if(action==0)
			{
				n=0
				var next = 1;
				var prev = tiff.countDirectory()-1;
			}
			// 페이지 버튼 클릭 시
			else
			{
				var page_nation = document.getElementById("page_nation");
				document.getElementById("page_nation").remove(page_nation);

				var prev = (Number(n)-1);
				var next = (Number(n)+1);

				if(prev<0)
					prev = (total-1);
	
				if(next>=total)
					next = 0;
			}

			// 페이지 생성
			var page = document.createElement('span');
			page.className = "page_nation";
			page.id = "page_nation";
			page.innerHTML = "<a id='page_prev' class='arrow prev' href=javascript:show_img_div("+(prev)+");></a>";

			var limit = 10; // 페이지 당 갯수
			var startPage = parseInt(n / limit) * limit;
			var endPage = (startPage + limit > total) ? total : startPage + limit;
			for(var i=startPage; i<endPage; ++i)
			{
				page.innerHTML += "<a id='page"+i+"' href=\"javascript:show_img_div("+(i)+");\">"+(i+1)+"</a>";
			}

			page.innerHTML += "<a id='page_next' class='arrow next' href=javascript:show_img_div("+(next)+");></a>";
			document.getElementById("img_page_div").append(page);


			if(!n)	n=0;
				
			//클릭된 페이지 구분 [active] (css용도)
			var num_p;
			if(n>=0 && n<total)
			{
				num_p = "page"+n;
			}
			else
			{
				if(n>=total)
					num_p = "page0";
				else
					num_p = "page"+(total-1);
			}
										
			var d = document.getElementById(num_p).className = "active";
										
			var dataUrl = canvas.toDataURL();	// 문자열로 변경
			var img_detail = document.getElementById("img_detail");
			document.getElementById("img_detail").remove(img_detail);

			var thumbnail = dataUrl;
	
			var div_add = document.createElement('div');
			div_add.id = 'img_detail';
			div_add.style.width = "100%";

			document.getElementById("img_show_div").append(div_add);

			var img = document.createElement('img');
				img.id = 'show_img'+(i);
				img.className = 'show_img_class';
				img.src = thumbnail;
				img.style.width = "85%";
				img.style.height = "100%";
				img.style.overflow = "scroll";
				document.getElementById("img_detail").append(img);
		};
							
		xhr.send();
	}

	function show_img_div(page_num)									
	{
		document.getElementById("page").value = page_num;

		var srcfilename =  document.getElementById("srcfilename").value;
		tiff_view(srcfilename,"action");
	}

    function filePreview(no, cust_info_no, ext)
    {
        window.open('/erp/custimagepriview/'+ no + '/' + cust_info_no + '/' + ext, 'popOpen'+no, 'status=no, left=0,top=0, height=900, width=900');
    }

</script>

<div class="p-2 needs-validation">
<b>파일 ( 기타파일 업로드시 해당 파일은 다운로드만 가능합니다 )</b>
<!-- BODY -->
<button type="button" class="btn btn-xs btn-outline-info float-right mb-1" onclick="getCustData('image');"><i class="fa fa-plus-square text-info mr-1"></i>파일추가</button>
<table class="table table-sm table-hover loan-info-table card-secondary card-outline">
    <colgroup>
        <col width="15%"/>
        <col width="15%"/>
        <col width="15%"/>
        <col width="20%"/>
        <col width="15%"/>
    </colgroup>
    <thead>
        <tr>
            <th class="text-center">파일구분</th>
            <th class="text-center">계약번호</th>
            <th class="text-center">등록자</th>
            <th class="text-center">등록일시</th>
            <th class="text-center">파일</th>
        </tr>
    </thead>
    <tbody>
        @forelse( $img as $idx => $v )
            <tr onclick="getCustData('image','',{{ $v->no }});" @if( isset($selected_img[0]->no) && $selected_img[0]->no == $v->no ) bgcolor="FFDDDD" @endif >
                <td class="text-center">{{ Func::getArrayName($arr_task_name, $v->taskname) }}</td>
                <td class="text-center">{{ $v->loan_info_no }}</td>
                <td class="text-center">{{ $v->worker_id }}</td>
                <td class="text-center">{{ $v->save_time }}</td>
                <td class="text-center" onClick="event.cancelBubble=true;">{{$v->origin_filename}}</td>
            </tr>
        @empty
            <tr>
                <td colspan="13" class='text-center p-4'>등록된 파일이 없습니다.</td>
            </tr>
        @endforelse
        <tr><td colspan="13"></td></tr>
    </tbody>
</table>

<div class="row">
    <div class="col-md-5">
        <form id="img_form" name="img_form" method="post" enctype="multipart/form-data" action="">
        @csrf
            <input type="hidden" name="cust_info_no" value="{{ $cust_info_no ?? '' }}">
            <input type="hidden" name="mode" value="{{ $mode?? '' }}">
            <input type="hidden" name="no" value="{{ $selected_img[0]->no ?? '' }}">
            <input type="hidden" name="loan_info_no" value="{{ $loan_info_no ?? '' }}">
            <input type="hidden" name="deputy_no" value="{{ $selected_img[0]->deputy_no ?? '' }}">
            <table class="table table-sm table-bordered table-input text-xs">
                <colgroup>
                    <col width="25%"/>
                    <col width="75%"/>
                </colgroup>

                <tbody>
                    <tr>
                        <th>구분</th>
                        <td>
                            <select class="form-control form-control-sm text-xs col-md-3" onchange='change_div(this)' name="taskname" id="taskname">
                            <option value=''>구분선택</option>
                                {{ Func::printOption($arr_task_name, isset($selected_img[0]->taskname)? $selected_img[0]->taskname : "") }}
                            </select>
                        </td>
                    </tr>
                    {{--
                    <tr>
                        <th>파일서식</th>
                        <td>
                            <select class="form-control form-control-sm text-xs col-md-5" name="img_div_cd" id="img_div_cd">
                            <option value=''>파일서식선택</option>
                                @if(isset($selected_img[0]) && $selected_img[0]->taskname == 'COURT')
                                    {{ Func::printOption($arr_cot_div, isset($selected_img[0]->img_div_cd)? $selected_img[0]->img_div_cd : "") }}
                                @elseif(isset($selected_img[0]) && $selected_img[0]->taskname == 'LOAN')
                                    {{ Func::printOption($arr_lon_div, isset($selected_img[0]->img_div_cd)? $selected_img[0]->img_div_cd : "") }}
                                @elseif(isset($selected_img[0]) && $selected_img[0]->taskname == 'ETC')
                                    {{ Func::printOption($arr_etc_div, isset($selected_img[0]->img_div_cd)? $selected_img[0]->img_div_cd : "") }}
                                @endif
                            </select>
                        </td>
                    </tr>
                    --}}
                    @if(isset($selected_img[0]) && $selected_img[0]->filename)
                    <tr>
                        <th>파일다운로드</th>
                        <td>
                        <a href="/erp/downcustimg/{{$selected_img[0]->no}}" download="{{$selected_img[0]->origin_filename}}"><span class="hand text-blue"><i class="fas fa-file-download pr-1"></i>{{$selected_img[0]->origin_filename}}</span></a>
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
                        <th>메모</th>
                        <td>
                            <textarea class="form-control form-control-xs" name="memo" id="memo" placeholder=" 메모입력...." rows="4" style="resize:none;" >{{$selected_img[0]->memo ??"" }}</textarea>
                        </td>
                    </tr>
                    <tr>
                        <td class="text-right" colspan=2>
                            @if( !isset($mode) || (isset($mode) && $mode == "INS") )
                            <button type="button" class="btn btn-sm btn-info" onclick="imgAction('INS');">저장</button>
                            @elseif( isset($mode) && ($mode == "UPD") )
                            <button type="button" class="btn btn-sm btn-info" onclick="imgAction('DEL');">삭제</button>
                            <button type="button" class="btn btn-sm btn-info" onclick="imgAction('UPD');">수정</button>
                            @endif
                        </td>
                    </tr>
                </tbody>
            </table>
        </form>
    </div>
    <div class="col-md-7 text-center" >
        @if( isset($selected_img[0]->extension) )
            <a href="javascript:filePreview('{{$selected_img[0]->no}}', '{{$selected_img[0]->cust_info_no}}', '{{ strtolower($selected_img[0]->extension) }}')"><i class="fa fa-plus-circle"></i> 크게보기</a>
            @if( (strtolower($selected_img[0]->extension) == "pdf") )
                <iframe src='/pdfjs/web/viewer.html?file=/erp/getcustimg/{{$selected_img[0]->no}}/{{$selected_img[0]->cust_info_no}}#page=30' style='width:100%;height:800px'></iframe>

            @elseif((strtolower($selected_img[0]->extension) == "tif" || strtolower($selected_img[0]->extension) == "tiff"))

                <input type="hidden" class="page" name="page" id="page" value="">
                <input type="hidden" class="srcfilename" name="srcfilename" id="srcfilename" value="">
                <div id="img_page_div" style="width:100%; height:50px;"></div>
                <div id="img_show_div" style="width:100%; height:100%;"><div id="img_detail" style="width:100%;height:100%;"></div></div>
                <script>tiff_view("/erp/getcustimg?no={{$selected_img[0]->no}}&cust_info_no={{$selected_img[0]->cust_info_no}}", "0");</script>

            @else 
                <img style="width:100%;height:100%;" src='/erp/getcustimg?no={{$selected_img[0]->no}}&cust_info_no={{$selected_img[0]->cust_info_no}}'>
            @endif 
        @endif
    </div>
</div>
<script>
    bsCustomFileInput.init();
</script>