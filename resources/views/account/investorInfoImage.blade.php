
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
        let invNo  = $('#loan_info_no');
        let file   = $('#customFile');
        if((invNo.val() == null || invNo.val() == '') && imgDiv.val() != 'ETC' && mode == 'INS'){
            alert("투자번호를 선택하여 주십시오");
            return false;
        }
        if((invNo.val() == null || invNo.val() == '') && imgDiv.val() != 'ETC' && mode == 'UPD' && invNo.length != 0){
            alert("투자번호를 선택하여 주십시오");
            return false;
        }
        if(imgDiv.val() == null || imgDiv.val() == '' && mode != 'DEL'){
            alert("구분을 선택하여 주십시오");
            return false;
        }
        if(file.val() == null || file.val() == '' && mode == 'INS'){
            alert("파일을 선택하여 주십시오");
            return false;
        }

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
            url  : "/account/investorinfoimageaction",
            type : "post",
            data : postdata,
            processData : false,
            contentType : false,
            success : function(result) {
                globalCheck = false;
                alert(result);
                getInvestorData('investorinfoimage');
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
            url  : "/account/downcustimg",
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
                getInvestorData('investorinfoimage');
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
            url  : "/account/custtaskdiv",
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

    function filePreview(no, loan_usr_info_no, ext)
    {
        window.open('/account/usrimagepriview/'+ no + '/' + loan_usr_info_no + '/' + ext, 'popOpen'+no, 'status=no, left=0,top=0, height=900, width=900');
    }

    function changeInvNoList(pdName){
        let opts = document.getElementById("pd_loan_no_list").value;
        let loan_no_select = document.getElementById("loan_info_no");
        let pd_loan_no_list = JSON.parse(opts);
        let index = 0;
        loan_no_select.innerHTML = "";
        
        if(pdName == null || pdName == ''){
            var option = document.createElement("option");
            option.text = "투자번호선택";
            option.value = "";
            loan_no_select.add(option);
            exit();
        }
        
        for(var i in pd_loan_no_list){
            if(index == pdName){
                var option = document.createElement("option");
                option.text = "투자번호선택";
                option.value = "";
                loan_no_select.add(option);
                for(var j = 0; j < pd_loan_no_list[i].length; j++){
                    var option = document.createElement("option");
                    option.text = pd_loan_no_list[i][j];
                    option.value = pd_loan_no_list[i][j];
                    loan_no_select.add(option);
                }
            }
            index++;
        }
    }
</script>

<div class="p-2 needs-validation">
<b>파일</b>
<!-- BODY -->
{{-- <button type="button" class="btn btn-xs btn-outline-info float-right mb-1" onclick="getInvestorData('investorinfoimage');"><i class="fa fa-plus-square text-info mr-1"></i>파일추가</button> --}}
<table class="table table-sm table-hover loan-info-table card-secondary card-outline">
    <colgroup>
        <col width="17%"/>
        <col width="17%"/>
        <col width="17%"/>
        <col width="17%"/>
        <col width="17%"/>
    </colgroup>
    <thead>
        <tr>
        <th class="text-center">채권번호</th>
            <th class="text-center">파일구분</th>
            <th class="text-center">파일</th>
            <th class="text-center">등록자</th>
            <th class="text-center">등록일시</th>
        </tr>
    </thead>
    <tbody>
        @forelse( $img as $idx => $v )
            <tr onclick="getInvestorData('investorinfoimage','',{{ $v->no }});" @if( isset($selected_img[0]->no) && $selected_img[0]->no == $v->no ) bgcolor="FFDDDD" @endif >
                <td class="text-center">{{ $v->investor_type.$v->investor_no }}-{{ $v->inv_seq }}</td>
                <td class="text-center">{{ Func::getArrayName($arr_image_div, $v->img_div_cd) }}</td>
                <td class="text-center" onClick="event.cancelBubble=true;">
                    <a href="/account/downinvestorimg/{{$v->no}}" download="{{$v->origin_filename}}" class="hand text-blue">
                        <i class="fas fa-file-download pr-1"></i>
                        {{$v->origin_filename}}
                    </a>
                </td>
                <td class="text-center">{{ $v->save_id }}</td>
                <td class="text-center">{{ $v->save_time }}</td>
            </tr>
        @empty
            <tr>
                <td colspan="13" class='text-center p-4'>등록된 파일이 없습니다.</td>
            </tr>
        @endforelse
        <tr><td colspan="13"></td></tr>
    </tbody>
</table>

<script>
    bsCustomFileInput.init();
</script>