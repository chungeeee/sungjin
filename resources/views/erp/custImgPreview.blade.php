@extends('layouts.masterPop')
@section('content')

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
</script>

<div class="card card-lightblue">
    <div class="card-header-no-round">
    <h2 class="card-title">파일뷰어</h2>
    </div>
</div>

<div class="pt-0 p-2 ">
	<div class="" style="text-align:center;height:820px;">
		@if($ext == 'pdf')
		    <iframe src='/pdfjs/web/viewer.html?file=/erp/getcustimg/{{$no}}/{{$cust_info_no}}#page=30' style='width:100%;height:inherit'></iframe>
		@elseif($ext == 'tif' || $ext == 'tiff')
            <input type="hidden" class="page" name="page" id="page" value="">
            <input type="hidden" class="srcfilename" name="srcfilename" id="srcfilename" value="">
            <div id="img_page_div" style="width:100%; height:50px;"></div>
            <div id="img_show_div" style="width:100%; height:100%;"><div id="img_detail" style="width:100%;height:100%;"></div></div>
            <script>tiff_view("/erp/getcustimg?no={{$no}}&cust_info_no={{$cust_info_no}}", "0");</script>
		@else
        <img style="width:100%;" src='/erp/getcustimg?no={{$no}}&cust_info_no={{$cust_info_no}}'>
		@endif
	</div>
</div>

@endsection

@section('javascript')

<style>

</style>
<script type="text/javascript" src="/js/tiff.min.js"></script>


@endsection

