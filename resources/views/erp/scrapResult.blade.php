@extends('layouts.masterPop')
@section('content')
@if (isset($plural))
	<form name="scrapForm" method="POST" action="/erp/plurality">
		@csrf
		<input type="hidden" id="addr" name="addr" value="{{ $addrArea['addr'] }}">
		<input type="hidden" id="area" name="area" value="{{ $addrArea['area'] }}">
		<input type="hidden" id="damboNo" name="damboNo" value="{{ $damboNo }}">
		<input type="hidden" id="scrap" name="scrap" value="">
		<div class="col-md-12 row p-2 m-0" style="overflow:hidden" >
			<div id='spinner_div'></div>

			<h6 class="card-title"><i class="fas fa-list-alt m-2"></i>검색된 주소 목록</h6>
			<table class="table table-sm table-bordered table-input text-xs p-2">
				<colgroup>
					<col width="80%"/>
					<col width="10%"/>
					<col width="10%"/>
				</colgroup>

				<tbody>
				<tr>
					<th colspan="3" style="text-align:center;">검색된 주소</th>
				</tr>
				@isset($plural)
					@foreach ($plural as $scrap)
						<tr onclick='selected(this);' class="hand" onmouseover="style.background='#DDE5F3'" onmouseout="style.background='#FFFFFF'" align="center" bgcolor=#FFFFFF>
							<td class="numTd" align="center">{{ $scrap['search_addr'] }}</td>
							<td><button type="button" class="btn btn-sm bg-lightblue" onclick="confirmSise('{{ $scrap['kb_url'] }}');">KB시세확인</button></td>
							<td><button type="button" class="btn btn-sm btn-info" onclick="kbSelectAddr({{ json_encode($scrap) }});">주소선택</button></td>
						</tr>
					@endforeach
				@endisset
				</tbody>
			</table>
		</div>
	</form>
	@endsection

	@section('javascript')
		<script>
			function confirmSise(link)
			{
				link = link.indexOf("https://") < 0 ? "https://"+link : link;

				var wnd = window.open("about:blank", "", "left=0,top=0,width=screen.availWidth,height=screen.availHeight,scrollbars=yes,resizable=yes");
				wnd.location.href = link;
				wnd.resizeTo(screen.availWidth, screen.availHeight);
				wnd.focus();
			}

			function kbSelectAddr(json)
			{
				if (confirm("해당 주소로 선택하시겠습니까?")) {
					json = JSON.stringify(json);
					document.getElementById("scrap").value = json;
					scrapForm.submit();
				} else {
					return;
				}
			}
		</script>
	@endsection

@else

	<style>
	A:link    { color:#333333; text-decoration:none; }
	A:visited { color:#333333; text-decoration:none; }
	A:active  { color:#333333; text-decoration:none; }
	A:hover   { color:#DD9A03; text-decoration:none; }
	#molit_search 
	{
		font-size:10px;	
	}
	.listTable
	{
		display:none;
	}
	.connoisseur > tbody > tr > td , .connoisseur
	{
		border: 1px solid #666666;
		border-collapse: collapse;
		border-spacing: 0;
	}
	.tbg
	{
		background: #ffffff
	}
	/*스피너*/
	#Wrap_spinner
	{
			position:absolute;
			filter:alpha(opacity:'075');
			background-color:#F0F5FF;
			z-index:99;
			top:0px;
			bottom:0px;
			display:none;
	}
	#spinner
	{
			width:200px;
			height:200px;
			top:25%;
			left:40%;
			z-index:999;
			
			position:fixed;
			background:url('/img/Spinner.gif') no-repeat 0 0;
	}

	</style>
	
	<div class="col-md-12 row p-2 m-0" style="overflow:hidden" >
		<div id='spinner_div'></div>
		<input id='loan_real_estate_no' type=hidden value='{{$loan_real_estate_no ?? ''}}'>
		<input id='loan_app_no' type=hidden value='{{$loan_app_no ?? ''}}'>
		<input id='loan_info_no' type=hidden value='{{$loan_info_no ?? ''}}'>
		<input id='fulladdr' type=hidden value=''>
		<input id='fullvalue' type=hidden value=''>
		<input id='molit_api_floor' type=hidden value=''>
		<input id='estate_member_no' type=hidden value='{{$v->no ?? ''}}'>
		<input id='lawd_cd' type=hidden value='{{$v->lawd_cd ?? ''}}'>
		<input id='goods_cd' type=hidden value='{{$v->goods_cd ?? ''}}'>
		<input id='addr_kor' type=hidden value='{{ isset($v->do_si)&&isset($v->gun_gu_si)&&isset($v->dong_li) ? $v->do_si." ".$v->gun_gu_si." ".$v->dong_li : ''}}'>
		<input id='house_name' type=hidden value='{{$v->house_name ?? ''}}'>
		<input id='search_yn' type=hidden value=''>
		<input id='kb_sise_money' type=hidden value=''>

		<h6 class="card-title"><i class="fas fa-list-alt m-2"></i>KB 시세</h6>
		<table class="table table-sm table-bordered table-input text-xs p-2">
			<colgroup>
				<col width="10%"/>
				<col width="10%"/>
				<col width="46%"/>
				<col width="10%"/>
				<col width="10%"/>
				<col width="10%"/>
			</colgroup>

			<tbody>
			<tr>
				<th rowspan="2" style="text-align:center;">시세조회일</th>
				<th rowspan="2" style="text-align:center;">면적(공급/전용)</th>
				<th rowspan="2" style="text-align:center;">건물명</th>
				<th colspan="3" style="text-align:center;">KB시세</th>
			</tr>
			<tr>
				<th style="text-align:center;">하한가</th>
				<th style="text-align:center;">일반가</th>
				<th style="text-align:center;">상한가</th>
			</tr> 
			@isset($clclt)
				<input type="hidden" id="real_floor" name="real_floor" value="{{ $clclt['real_floor'] }}">
				<tr onclick='selected(this);' class="hand" onmouseover="style.background='#DDE5F3'" onmouseout="style.background='#FFFFFF'" align="center" bgcolor=#FFFFFF>
					<td class="numTd" align="center">{{ $clclt['search_date'] }}</td>
					<td class="numTd" align="center">{{ $clclt['kb_supply_area'] }} / {{ $clclt['ccr_area'] }}</td>
					<td class="numTd" align="center">{{ $clclt['kb_rslt_addr'] }}</td>

					<td align="right" class="numTd" name='low_avg_price' id='low_avg_price'>{{ number_format($clclt['kb_sise_low']) }}</td>
					<td align="right" class="numTd" name='normal_avg_price' id='normal_avg_price'>{{ number_format($clclt['kb_sise']) }}</td>
					<td align="right" class="numTd" name='high_avg_price' id='high_avg_price'>{{ number_format($clclt['kb_sise_high']) }}</td>
		</tr>
			@endisset
			</tbody>
		</table>
		
		<div class="row m-0" id='estate_acount_view'>

			<h6 class="card-title"><i class="fas fa-donate m-2"></i>물건정보</h6>
			<table class="table table-sm table-bordered table-input text-xs p-2">
				<colgroup>
					<col width="10%">
					<col width="10%">
					<col width="10%">
					<col width="10%">
					<col width="10%">
					<col width="10%">
					<col width="10%">
					<col width="10%">
					<col width="10%">
					<col width="10%">
				</colgroup>

				<tbody id="KbSise">
					<tr>
						<th colspan="10" style="text-align:right">
							<span>(금액단위 : 만원)</span><span>(면적단위 : m<sup>2</sup>)</span>
						</th>
					</tr>
					<tr style="text-align:center">
						<th class="titleTd" rowspan="1" colspan="3">조회주소</th>
						<th class="titleTd" rowspan="1">시세갱신일 </th>
						<th class="titleTd" rowspan="1">면적(공급/전용)</th>
						<th class="titleTd" rowspan="1">준공년월({{ $clclt['kb_build_term'] }}년차)</th>
						<th class="titleTd" rowspan="1">총세대수</th>
						<th class="titleTd" rowspan="1">건설사</th>
						<th class="titleTd" rowspan="1">동개수</th>
						<th class="titleTd" rowspan="1">방개수</th>
					</tr>
					<tr id="realSise" onmouseover="style.background='#DDE5F3'" onmouseout="style.background='FFFFFF'" style="background: rgb(221, 229, 243);" align="center" bgcolor="#ffffff">
						<td colspan="3" onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name='kb_search_address' id='kb_search_address' >{{ $addrArea['addr'] }}</td>
						<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name='sise_update_date' id='sise_update_date'>{{ $raw['sise_update_date'] }}</td>
						<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name='land_area' id='land_area' >{{ $clclt['kb_supply_area'] }} / {{ $clclt['ccr_area'] }}</td>
						<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name='kb_build_date' id='kb_build_date' >{{ date('Y-m', strtotime($clclt['kb_build_date'])) }}</td>
						<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name='kb_households_cnt' id='kb_households_cnt' >{{ number_format($clclt['kb_households_cnt']) }}</td>
						<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name="kb_construct" id="kb_construct">{{ $clclt['kb_construct'] }}</td>
						<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name="kb_total_dong" id="kb_total_dong">{{ $clclt['kb_total_dong'] }}</td>
						<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name="kb_room_cnt" id="kb_room_cnt">{{ $clclt['kb_room_cnt'] }}</td>
					</tr>
				</tbody>
			</table>

			<div class="col-md-12 row p-0 m-0">
				<div class="col-md-6 p-0">
					<h6 class="card-title"><i class="fas fa-donate m-2"></i>실거래평가금액</h6>
					<table class="table table-sm table-bordered table-input text-xs">
						<colgroup>
							<col width="33%">
							<col width="33%">
							<col width="33%">
						</colgroup>

						<tbody id="changeRealTrade">
							<tr>
								<th colspan="4" style="text-align:right">
									<span>(금액단위 : 만원)</span><span>(면적단위 : m<sup>2</sup>)</span>
								</th>
							</tr>
							<tr style="text-align:center">
								<th class="titleTd">거래년월일</th>
								{{-- <th class="titleTd">건물명</th> --}}
								<th class="titleTd">층</th>
								<th class="titleTd">실거래평가금액</th>
							</tr>
							@foreach ($trade as $trade)
								<tr name='api_tr' onclick=""  class="tbg realTrade" onmouseover="style.background='#DDE5F3'" onmouseout="style.background='FFFFFF'" style="cursor:hand; background: rgb(221, 229, 243);">
									<td style="text-align:center;" onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name='api_date'>{{ $trade['kb_real_trade_date'] }}</td>
									{{-- <td style="text-align:center;" onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name='api_house_name'>-</td> --}}
									<td style="text-align:center;" onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name='api_house_floor'>{{ $trade['kb_real_trade_floor'] }}</td>
									<td style="text-align:right;" onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag"  name='api_money'>{{ number_format($trade['kb_real_trade_money']) }}</td>
								</tr>
							@endforeach
						</tbody>
					</table>
				</div>

				<div class="col-md-6 p-0">
					<h6 class="card-title"><i class="fas fa-donate m-2"></i>실거래기준가</h6>
					<table class="table table-sm table-bordered table-input text-xs">
						<colgroup>
							<col width="10%">
							<col width="10%">
							<col width="10%">
							<col width="10%">
						</colgroup>

						<tbody id="changeKbSiSe">
							<tr>
								<th colspan="4" style="text-align:right">
									<span>(금액단위 : 만원)</span><span>(면적단위 : m<sup>2</sup>)</span>
								</th>
							</tr>
							<tr style="text-align:center">
								<th class="titleTd" colspan="2">실거래 평균기준가(2층 초과)</th>
								<th class="titleTd" colspan="2">실거래 최저기준가(2층 이하)</th>
							</tr>
							<tr style="text-align:center">
								<th class="titleTd">평균거래가(일반)</th>
								<th class="titleTd">최저거래가(일반)</th>
								<th class="titleTd">평균거래가(하한)</th>
								<th class="titleTd">최저거래가(하한)</th>
							</tr>
							<tr name='sale_tr' class="kbSiSe"  onmouseover="style.background='#DDE5F3'" onmouseout="style.background='FFFFFF'" style="cursor:hand; background: rgb(221, 229, 243);" align="center" bgcolor="#ffffff" >
								<td align="right" onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name='kb_sale_house_name'>{{ number_format($clclt['avg_trade']) }}</td>
								<td align="right" onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name='kb_sale_house_floor'>{{ number_format($clclt['min_trade']) }}</td>
								<td align="right" onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name='kb_sale_area'>{{ number_format($clclt['avg_limit_trade']) }}</td>
								<td align="right" onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name='kb_sale_money'>{{ number_format($clclt['min_limit_trade']) }}</td>
							</tr>
						</tbody>
					</table>

					<h6 class="card-title"><i class="fas fa-donate m-2"></i>최근실거래가</h6>
					<table class="table table-sm table-bordered table-input text-xs">
						<colgroup>
							<col width="33%">
							<col width="33%">
							<col width="33%">
						</colgroup>

						<tbody id="changeKbSiSe">
							<tr>
								<th colspan="3" style="text-align:right">
									<span>(금액단위 : 만원)</span><span>(면적단위 : m<sup>2</sup>)</span>
								</th>
							</tr>
							<tr style="text-align:center">
								<th class="titleTd">최근 3개월 실거래가</th>
								<th class="titleTd">최근 6개월 실거래가</th>
								<th class="titleTd">최근 9개월 실거래가</th>
							</tr>
							<tr name='sale_tr' class="kbSiSe"  onmouseover="style.background='#DDE5F3'" onmouseout="style.background='FFFFFF'" style="cursor:hand; background: rgb(221, 229, 243);" align="center" bgcolor="#ffffff" >
								<td align="right" onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name='kb_sale_house_name'>{{ number_format($clclt['recent_trade_3']) }}</td>
								<td align="right" onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name='kb_sale_area'>{{ number_format($clclt['recent_trade_6']) }}</td>
								<td align="right" onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class="tbg numTd del_tag" name='kb_sale_house_floor'>{{ number_format($clclt['recent_trade_9']) }}</td>
							</tr>
						</tbody>
					</table>
				</div>
			</div>

			{{-- <h6 class="card-title"><i class="fas fa-donate m-2"></i>면적별 부동산 시세정보</h6>
			<table class="table table-sm table-bordered table-input text-xs p-2">
				<colgroup>
				<col width="10%"/>
				<col width="10%"/>
				<col width="10%"/>
				<col width="10%"/>
				<col width="10%"/>
				<col width="10%"/>
				<col width="10%"/>
				<col width="10%"/>
				<col width="10%"/>
				<col width="10%"/>
				</colgroup>

				<tr>
					<th rowspan="3" style="text-align:center;">건물이름</th>
					<th rowspan="3" style="text-align:center;">공급/전용( m<sup>2</sup>)</th>
					<th colspan="3" style="text-align:center;border-bottom:none !important;">
						매매가
					</th>
					<th colspan="3" style="text-align:center;border-bottom:none !important;">
						전세가
					</th>
					<th colspan="2" style="text-align:center;border-bottom:none !important;">
						월세가
					</th>
				</tr>
				<tr style="border-top:none !important;">
					<th colspan="3" style="text-align:right;border-top:none !important;">
						<span>(금액단위 : 만원)</span>
					</th>
					<th colspan="3" style="text-align:right;border-top:none !important;">
						<span>(금액단위 : 만원)</span>
					</th>
					<th colspan="2" style="text-align:right;border-top:none !important;">
						<span>(금액단위 : 만원)</span>
					</th>
				</tr> 
				<tr>
					<th style="text-align:center;">하위평균가</th>
					<th style="text-align:center;">일반평균가</th>
					<th style="text-align:center;">상위평균가</th>
					<th style="text-align:center;">하위평균가</th>
					<th style="text-align:center;">일반평균가</th>
					<th style="text-align:center;">상위평균가</th>
					<th style="text-align:center;">보증금</th>
					<th style="text-align:center;">월세</th>
				</tr> 
				<tbody id='kb_json_parents'> 
					
				</tbody>
			</table> --}}

			<div class="col-md-12 row m-0" style="text-align:right;">
				<div class="col-md-12">
					<button type="button" class="btn btn-sm btn-info" onclick="setOpenerVal();">적용</button>
					{{--<button type="button" class="btn btn-sm bg-lightblue" onclick="real_estate_save();">저장</button>--}}
				</div>
			</div>
		</div>
	</div>
	@endsection

	@section('javascript')

	<script>
	{{----------------------------- select box 초기화 함수 ------------------------}}
	function check_spin2()
	{
		var option_clear=document.getElementsByName("realestate_addr_div1")[0];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear.options.length = 1;
		var option_clear2=document.getElementsByName("realestate_addr_div1")[1];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear2.options.length = 1;
		var option_clear3=document.getElementsByName("realestate_addr_div1")[2];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear3.options.length = 1;
		var option_clear4=document.getElementsByName("realestate_addr_div2")[0];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear4.options.length = 1;
		var option_clear5=document.getElementsByName("realestate_addr_div2")[1];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear5.options.length = 1;
		var option_clear6=document.getElementsByName("realestate_addr_div2")[2];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear6.options.length = 1;
		var option_clear7=document.getElementsByName("estate_addr")[0];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear7.options.length = 1;
		var option_clear8=document.getElementsByName("estate_addr")[1];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear8.options.length = 1;
		var option_clear9=document.getElementsByName("estate_addr")[2];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear9.options.length = 1;
		var option_clear10=document.getElementsByName("realestate_area_div")[0];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear10.options.length = 1;
	}
	function check_spin3()
	{
		var option_clear=document.getElementsByName("realestate_addr_div1")[0];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear.options.length = 1;
		var option_clear2=document.getElementsByName("realestate_addr_div1")[1];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear2.options.length = 1;
		var option_clear3=document.getElementsByName("realestate_addr_div1")[2];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear3.options.length = 1;
		var option_clear4=document.getElementsByName("realestate_addr_div2")[0];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear4.options.length = 1;
		var option_clear5=document.getElementsByName("realestate_addr_div2")[1];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear5.options.length = 1;
		var option_clear6=document.getElementsByName("realestate_addr_div2")[2];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear6.options.length = 1;
		var option_clear7=document.getElementsByName("estate_addr")[0];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear7.options.length = 1;
		var option_clear8=document.getElementsByName("estate_addr")[1];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear8.options.length = 1;
		var option_clear9=document.getElementsByName("estate_addr")[2];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear9.options.length = 1;
		var option_clear10=document.getElementsByName("realestate_area_div")[0];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear10.options.length = 1;
	}
	function check_spin4()
	{
		var option_clear=document.getElementsByName("realestate_addr_div1")[0];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear.options.length = 1;
		var option_clear2=document.getElementsByName("realestate_addr_div1")[1];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear2.options.length = 1;
		var option_clear3=document.getElementsByName("realestate_addr_div1")[2];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear3.options.length = 1;
		var option_clear4=document.getElementsByName("realestate_addr_div2")[0];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear4.options.length = 1;
		var option_clear5=document.getElementsByName("realestate_addr_div2")[1];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear5.options.length = 1;
		var option_clear6=document.getElementsByName("realestate_addr_div2")[2];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear6.options.length = 1;
		var option_clear7=document.getElementsByName("estate_addr")[0];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear7.options.length = 1;
		var option_clear8=document.getElementsByName("estate_addr")[1];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear8.options.length = 1;
		var option_clear9=document.getElementsByName("estate_addr")[2];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear9.options.length = 1;
		var option_clear10=document.getElementsByName("realestate_area_div")[0];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear10.options.length = 1;
	}
	function check_spin5()
	{
		var option_clear=document.getElementsByName("realestate_addr_div1")[0];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear.options.length = 1;
		var option_clear2=document.getElementsByName("realestate_addr_div1")[1];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear2.options.length = 1;
		var option_clear3=document.getElementsByName("realestate_addr_div1")[2];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear3.options.length = 1;
		var option_clear=document.getElementsByName("realestate_addr_div2")[0];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear.options.length = 1;
		var option_clear2=document.getElementsByName("realestate_addr_div2")[1];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear2.options.length = 1;
		var option_clear3=document.getElementsByName("realestate_addr_div2")[2];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear3.options.length = 1;
		var option_clear10=document.getElementsByName("realestate_area_div")[0];
		for(var i=0; i<option_clear.options.length; i++) option_clear.options[i+1] = null;
		option_clear10.options.length = 1;
	}
	{{---------------------------------------------------------------- select box 초기화 함수 ------------------------------------------------------}}
	function insert()
	{
		$.ajax({
			url:"/ups/getkbarea",	
			type:"get",
			data:
			{
				
			},
			success:function(rs)
			{	
				if(rs.result == 'Y')
				{
					alert('성공입니다!');
				}
				else
				{
					alert('실패입니다ㅠ');
				}
			},
			error:function(request,status,error)
			{
				alert('에러입니다');
			}
		});	
	}
	//지역선택 군구시 동 옵션지우는 함수
	function gun_gu_si_dong_option_clear()
	{
		m=document.getElementById('gun_gu_si_div');
		k=document.getElementById('dong_div');
		for(var i=0; i<m.options.length; i++) 
		{
			m.options[i+1] = null;
		}
		m.options.length = 1;
		for(var i=0; i<k.options.length; i++) 
		{
			k.options[i+1] = null;
		}
		k.options.length = 1;
	}
	//div 생성 닫기
	function close_img()
	{
		var spinner = document.getElementById('spinner');
		spinner.parentNode.removeChild(spinner);
		var Wrap_spinner = document.getElementById('Wrap_spinner');
		Wrap_spinner.parentNode.removeChild(Wrap_spinner);
	}
	//div 생성 메서드
	function spinner_ck()
	{
		// 첫번째 DIV 생성
		var div1 = document.createElement("div");
			div1.setAttribute("id","Wrap_spinner");

		// 두번째 DIV 생성
		var div2 = document.createElement("div");
			div2.setAttribute("id","spinner");

		// 첫번째 DIV 안에 두번째 DIV 넣은 뒤 BODY에 붙여넣기
		div1.appendChild(div2);

		$("#spinner_div").prepend(div1);
		// 사이즈 체크
		var Wrap_h = document.body.scrollHeight;
		var Wrap_w = document.body.offsetWidth;
		// 스피너 처리
		document.getElementById('Wrap_spinner').style.height = Wrap_h;
		document.getElementById('Wrap_spinner').style.width = Wrap_w;
		document.getElementById('Wrap_spinner').style.display = 'block';
		console.log(Wrap_h);
		console.log(Wrap_w);
		console.log(document.getElementById('Wrap_spinner').style.display);
	}
	//체크박스 변경시 옵션 초기화
	function radio_check() 
	{	
		$('#siseTable').css("display", "none");
		$('#estate_acount_view').css("display", "none");
		$(".del_tag").html("");
		//라디오버튼 바뀔때마다 옵션값 초기화 
		d=document.getElementById('do_si_div');
		d.options[0].selected=true;
		m=document.getElementById('gun_gu_si_div');
		k=document.getElementById('dong_div');
		for(var i=0; i<m.options.length; i++) 
		{
			m.options[i+1] = null;
		}
		m.options.length = 1;
		for(var i=0; i<k.options.length; i++) 
		{
			k.options[i+1] = null;
		}
		k.options.length = 1;
		check_spin1();
		init_data();

		//라디오버튼에 따라 주소선택부 변경 시키기
		var house_div = document.getElementsByName("estate_type"); 
		for(var i=0;i<house_div.length;i++)
		{
			if(house_div[i].checked == true)
			{
				var chkhouse = house_div[i].value;
				if(chkhouse=='apart')
				{
					document.getElementsByName("estate_addr")[0].style.display='inline';
					document.getElementsByName("estate_addr")[1].style.display='none';
					document.getElementsByName("estate_addr")[2].style.display='none';
					document.getElementsByName("realestate_addr_div1")[0].style.display='inline';
					document.getElementsByName("realestate_addr_div1")[1].style.display='none';
					document.getElementsByName("realestate_addr_div1")[2].style.display='none';
					document.getElementsByName("realestate_addr_div2")[0].style.display='inline';
					document.getElementsByName("realestate_addr_div2")[1].style.display='none';
					document.getElementsByName("realestate_addr_div2")[2].style.display='none';
				}
				if(chkhouse=='villa')
				{
					document.getElementsByName("estate_addr")[0].style.display='none';
					document.getElementsByName("estate_addr")[1].style.display='inline';
					document.getElementsByName("estate_addr")[2].style.display='none';
					document.getElementsByName("realestate_addr_div1")[0].style.display='none';
					document.getElementsByName("realestate_addr_div1")[1].style.display='inline';
					document.getElementsByName("realestate_addr_div1")[2].style.display='none';
					document.getElementsByName("realestate_addr_div2")[0].style.display='none';
					document.getElementsByName("realestate_addr_div2")[1].style.display='inline';
					document.getElementsByName("realestate_addr_div2")[2].style.display='none';
				}
				if(chkhouse=='office')
				{
					document.getElementsByName("estate_addr")[0].style.display='none';
					document.getElementsByName("estate_addr")[1].style.display='none';
					document.getElementsByName("estate_addr")[2].style.display='inline';
					document.getElementsByName("realestate_addr_div1")[0].style.display='none';
					document.getElementsByName("realestate_addr_div1")[1].style.display='none';
					document.getElementsByName("realestate_addr_div1")[2].style.display='inline';
					document.getElementsByName("realestate_addr_div2")[0].style.display='none';
					document.getElementsByName("realestate_addr_div2")[1].style.display='none';
					document.getElementsByName("realestate_addr_div2")[2].style.display='inline';
				}
			}
		}
	}
	//첫번째주소 클릭시 
	function change_dosi(b)
	{	
		$('#siseTable').css("display", "none");
		$('#estate_acount_view').css("display", "none");
		spinner_ck();
		//옵션지우기
		gun_gu_si_dong_option_clear();
		check_spin2();

		//실거래평가금액 창 지우기 
		$(".del_tag").html("");
		init_data();
		
		for(var i=0; i<m.options.length; i++) 
		{
			if(i) m.options[i].style.backgroundColor ='FEEEE0'; 		
		}
		close_img();
	}
	//두번째 주소 클릭시
	function change_gun_gu_si(b)
	{
		spinner_ck();
		//실거래평가금액 창 지우기 
		$(".del_tag").html("");
		check_spin3();
		k=document.getElementById('dong_div');
		for(var i=0; i<k.options.length; i++) k.options[i+1] = null;
		k.options.length = 1;
		k=document.getElementById('dong_div');
		for(var i=0; i<k.options.length; i++) k.options[i+1] = null;
		k.options.length = 1;
		init_data();
		

		for(var i=0; i<k.options.length; i++) 
		{	
			if(i)	k.options[i].style.backgroundColor ='FEEEE0'; 		
		}
		close_img();
	}
	//세번째 주소클릭시 법정동코드와 한글주소 input tag 에 넣어두는 것
	function change_dong(b)
	{	
		$('#siseTable').css("display", "none");
		$('#estate_acount_view').css("display", "none");
		spinner_ck();
		//실거래평가금액 창 지우기 
		$(".del_tag").html("");
		//옵션지우기
		check_spin4();
		house_div_con();	
		init_data();
		var addr= document.getElementById('fulladdr');
		var law_code=document.getElementById('fullvalue');
		var firstaddr = document.getElementById('do_si_div').value;
		var secondaddr = document.getElementById('gun_gu_si_div').value;
		var fullvalue = document.getElementById('dong_div').value;
		var target = document.getElementById("dong_div");
		var thirdaddr = target.options[target.selectedIndex].text;
		// 법정동코드 공백제거
		law_code.value=fullvalue.replace(/ /gi, "");
		var ajax_law_code = law_code.value.substring(0,8);
		addr.value=secondaddr+" "+thirdaddr;

		console.log("법정동코드 : "+ajax_law_code);
		console.log("아파트오피스텔 다세대 구분" + chkhouse);	
		
		//3번째 주소클릭시 kb시세조회 건물이름 불러오는 ajax연동
		$.ajax({
			url:"/ups/getkbquick",	
			type:"get",
			data:
			{
				addr_cd:ajax_law_code,		// 법정동코드
				func_flag:'S1',				// 실행시킬 함수 플래그 
				opt1:chkhouse				// opt1 = 아파트, 오피스텔, 다세대 구분
			},
			success:function(rs)
			{	
				var addr_name_json = JSON.parse(rs);
				var addr_name = new Array();
				console.log(addr_name_json);
				for(var j=0;j<addr_name_json.주택목록.length;j++)
				{
					var op = new Option();
					addr_name[j]=addr_name_json.주택목록[j].단지명;
					op.innerHTML =addr_name_json.주택목록[j].단지명;
					op.value =addr_name_json.주택목록[j].물건식별자;
					estate_addr.appendChild(op);	
				}	
			close_img();
			},
			error:function(request,status,error)
			{
				alert('에러입니다');
				console.log("[status] "+request.status);
				console.log("[message] "+request.responseText);
				console.log("[error] "+error);
				close_img();
			}
		});	
	}
	//건물이름 눌렀을때 동 호 불러오는 함수	
	function change_house(b)
	{
		spinner_ck();
		//실거래평가금액 창 지우기 
		$(".del_tag").html("");
		//건물이름 클릭시 동, 호 옵션 지움
		check_spin5();
		document.getElementById('search_yn').value = "";
		var law_code=document.getElementById('fullvalue');
		var fullvalue = document.getElementById('dong_div').value;
		law_code.value=fullvalue;
		var ajax_law_code = law_code.value.substring(0,8);
		house_div_con();
		init_data();
		//건물이름 변경시 동,호 주소 받아오기 
		$.ajax({
			url:"/ups/getkbquick",	
			type:"get",
			data:
			{
				addr_cd:ajax_law_code,	// 법정동코드
				func_flag:'S2',					// 실행시킬 함수 플래그 
				goods_cd:b.value,			// 물건식별자
				opt1:chkhouse				// opt1 = 아파트, 오피스텔, 다세대 구분
			},
			success:function(rs)
			{	
				var addr_dongho_json = JSON.parse(rs);			
				console.log(addr_dongho_json);
				for(var j=0;j<addr_dongho_json.동명목록.length;j++)
				{
					var op = new Option();

					op.innerHTML =addr_dongho_json.동명목록[j].동명;
					op.value =addr_dongho_json.동명목록[j].동일련번호;
					realestate_addr_div1.appendChild(op);	
				}			

				for(var o=0;o<addr_dongho_json.호명목록.length;o++)
				{
					var op = new Option();
					op.innerHTML =addr_dongho_json.호명목록[o].호명;
					op.value =addr_dongho_json.호명목록[o].호일련번호+'/'+addr_dongho_json.호명목록[o].시세주택형일련번호;
					realestate_addr_div2.appendChild(op);	
				}	
				close_img();
			},
			error:function(request,status,error)
			{
				alert('에러입니다');
			}
		});	
		//면적종류 받아오기 
		$.ajax({
			url:"/ups/getkbarea",	
			type:"get",
			data:
			{
				addr_cd:ajax_law_code,	// 법정동코드
				goods_cd:b.value,			// 물건식별자
				opt1:chkhouse,				// opt1 = 아파트, 오피스텔, 다세대 구분
				func_flag:'S3'					// 실행시킬 함수 플래그 
			},
			success:function(rs)
			{	
				var addr_sise_json = JSON.parse(rs);	
				console.log(addr_sise_json);
				if(addr_sise_json.시세목록.length!=0)
				{
					for (var i=0;i<addr_sise_json.시세목록.length ;i++ )
					{
						var op = new Option();
						op.innerHTML = addr_sise_json.시세목록[i].면적+"/"+addr_sise_json.시세목록[i].전용면적;
						op.value = addr_sise_json.시세목록[i].주택형일련번호;
						document.getElementsByName("realestate_area_div")[0].appendChild(op);	
					}

				}
			},
			error:function(request,status,error)
			{
				alert('에러입니다');
			}
		});		
	}
	//아파트 빌라 오피스텔선택에 따라 부동산 선택 및 동호수 선택부 변경 함수 
	function house_div_con()
	{
		$('#siseTable').css("display", "");
		$('#estate_acount_view').css("display", "none");
		house_div = document.getElementsByName("estate_type");
		for(var i=0;i<house_div.length;i++)
		{	
			if(house_div[i].checked == true)
			{
				chkhouse = house_div[i].value;
				if(chkhouse=='apart')
				{
						realestate_addr_div1=document.getElementsByName("realestate_addr_div1")[0];
						realestate_addr_div2=document.getElementsByName("realestate_addr_div2")[0];
						estate_addr=document.getElementsByName("estate_addr")[0];
				}
				else if(chkhouse=='villa')
				{
						realestate_addr_div1=document.getElementsByName("realestate_addr_div1")[1];
						realestate_addr_div2=document.getElementsByName("realestate_addr_div2")[1];
						estate_addr=document.getElementsByName("estate_addr")[1];
				}
				else if(chkhouse=='office')
				{
						realestate_addr_div1=document.getElementsByName("realestate_addr_div1")[2];
						realestate_addr_div2=document.getElementsByName("realestate_addr_div2")[2];
						estate_addr=document.getElementsByName("estate_addr")[2];
				}
			}
		}
	}
	//건물이름 눌렀을때 동 호 불러오는 함수	
	function change_house(b)
	{
		spinner_ck();
		//실거래평가금액 창 지우기 
		$(".del_tag").html("");
		//건물이름 클릭시 동, 호 옵션 지움
		check_spin5();
		document.getElementById('search_yn').value = "";
		var law_code=document.getElementById('fullvalue');
		var fullvalue = document.getElementById('dong_div').value;
		law_code.value=fullvalue;
		var ajax_law_code = law_code.value.substring(0,8);
		house_div_con();
		init_data();
		//건물이름 변경시 동,호 주소 받아오기 
		$.ajax({
			url:"/ups/getkbquick",	
			type:"get",
			data:
			{
				addr_cd:ajax_law_code,	// 법정동코드
				func_flag:'S2',					// 실행시킬 함수 플래그 
				goods_cd:b.value,			// 물건식별자
				opt1:chkhouse				// opt1 = 아파트, 오피스텔, 다세대 구분
			},
			success:function(rs)
			{	
				var addr_dongho_json = JSON.parse(rs);			
				console.log(addr_dongho_json);
				for(var j=0;j<addr_dongho_json.동명목록.length;j++)
				{
					var op = new Option();

					op.innerHTML =addr_dongho_json.동명목록[j].동명;
					op.value =addr_dongho_json.동명목록[j].동일련번호;
					realestate_addr_div1.appendChild(op);	
				}			

				for(var o=0;o<addr_dongho_json.호명목록.length;o++)
				{
					var op = new Option();
					op.innerHTML =addr_dongho_json.호명목록[o].호명;
					op.value =addr_dongho_json.호명목록[o].호일련번호+'/'+addr_dongho_json.호명목록[o].시세주택형일련번호;
					realestate_addr_div2.appendChild(op);	
				}	
				close_img();
			},
			error:function(request,status,error)
			{
				alert('에러입니다');
			}
		});	
		//면적종류 받아오기 
		$.ajax({
			url:"/ups/getkbarea",	
			type:"get",
			data:
			{
				addr_cd:ajax_law_code,	// 법정동코드
				goods_cd:b.value,			// 물건식별자
				opt1:chkhouse,				// opt1 = 아파트, 오피스텔, 다세대 구분
				func_flag:'S3'					// 실행시킬 함수 플래그 
			},
			success:function(rs)
			{	
				var addr_sise_json = JSON.parse(rs);	
				console.log(addr_sise_json);
				if(addr_sise_json.시세목록.length!=0)
				{
					for (var i=0;i<addr_sise_json.시세목록.length ;i++ )
					{
						var op = new Option();
						op.innerHTML = addr_sise_json.시세목록[i].면적+"/"+addr_sise_json.시세목록[i].전용면적;
						op.value = addr_sise_json.시세목록[i].주택형일련번호;
						document.getElementsByName("realestate_area_div")[0].appendChild(op);	
					}

				}
			},
			error:function(request,status,error)
			{
				alert('에러입니다');
			}
		});		
	}
	function send_trade_money(nm, cnt)
	{

		var money = document.getElementsByName(nm)[cnt].innerHTML;
					alert(money);
		if(money=="" || money=="최근 6개월간 실거래가 없습니다." || money=="매물정보가 없습니다.")
		{
			alert("금액을 확인해주세요");
		}
		else
		{
			
			{{-- var agent_name = @json($arr_agent_name);
			var key_cnt=0;

			for( var key in agent_name )
			{
				key_cnt++;					
			}

			for(var i=0; i<key_cnt; ++i )
			{
				document.getElementById("real_trade_money").value = money;
				document.getElementsByName("2_money")[i].innerHTML = money;
			}
			--}}
			
			// make_limit_money();
		}
	}
	//면적 비교 함수 
	function get_chk_area(config_area, supply_area)
	{
		var arr_area = config_area.split("~");

		//일단 숫자로 바꿔보자 숫자면 false가 된다. 
		if(isNaN(supply_area))
		{
			//숫자가 아니면 뒤에 영어 빼주자
			supply_area = supply_area.replace(/[^(0-9).]/g,'');
		}
		if(parseFloat(arr_area[0]) < parseFloat(supply_area) && parseFloat(arr_area[1]) >= parseFloat(supply_area))
			return true;		
		else 
			return false;		
	}
	//세대수 비교 함수 
	function get_chk_households(config_households, house_holds)
	{
		//true false가 아니라 맞는 세대수를 반환.
		var arr_households = config_households.split("~");

		if(isNaN(house_holds))
		{
			//숫자만 가져온다.
			house_holds = house_holds.replace(/[^(0-9)]/g,'');
		}
		if(parseFloat(arr_households[0]) < parseFloat(house_holds) && parseFloat(arr_households[1]) >= parseFloat(house_holds))
		{
			if(arr_households[0] != "0")
			{
				return "normal";
			}
			else
			{
				return "special";
			}
		}
		else
			return null;
	}
	function init_data()
	{
		//선순위 관련 초기화
		var basic_money = document.getElementsByName("basic_money");
		var max_money = document.getElementsByName("max_money");
		var max_per = document.getElementsByName("max_per");

		for(var i = 0; i < basic_money.length; ++i)
		{
			if(basic_money[i].innerHTML != "")
			{
				basic_money[i].innerHTML = null;
				max_money[i].innerHTML = null;
				max_per[i].value = 120;
			}
		}
		//하단의 LTV, 한도, 금리, 최종한도 부분 초기화
		
		{{-- var agent_name = @json($arr_agent_name);
		var key_cnt = 0;

		var kb_use_ltv;			//운용LTV
		var kb_limit_money;		//한도
		var kb_use_ratio;		//금리

		var doc_length = document.getElementsByClassName("limit_money").length;
		for(var i =0; i < doc_length; ++i)
		{
			kb_use_ltv = document.getElementsByClassName("use_ltv")[i].childNodes[1];			//운용LTV
			kb_limit_money= document.getElementsByClassName("limit_money")[i].childNodes[1];	//한도
			kb_use_ratio= document.getElementsByClassName("use_ratio")[i].childNodes[1];		//금리

			kb_use_ltv.innerHTML = "-";
			kb_limit_money.innerHTML = "-";
			kb_use_ratio.innerHTML = "-";
		} --}}
		
	}
	//조회버튼클릭시
	function real_estate_account()
	{	
		$('#siseTable').css("display", "");
		$('#estate_acount_view').css("display", "");
			var house_div = document.getElementsByName("estate_type");
			var area_div = document.getElementsByName("realestate_area_div")[0];
			for(var i=0;i<house_div.length;i++)
			{	
				if(house_div[i].checked == true)
				{
					var chkhouse = house_div[i].value;
					if(chkhouse=='apart')
					{
						var realestate_addr_div1=document.getElementsByName("realestate_addr_div1")[0];
						var realestate_addr_div2=document.getElementsByName("realestate_addr_div2")[0];
						var estate_addr=document.getElementsByName("estate_addr")[0];
					}
					else if(chkhouse=='villa')
					{
						var realestate_addr_div1=document.getElementsByName("realestate_addr_div1")[1];
						var realestate_addr_div2=document.getElementsByName("realestate_addr_div2")[1];
						var estate_addr=document.getElementsByName("estate_addr")[1];
					}
					else if(chkhouse=='office')
					{
						var realestate_addr_div1=document.getElementsByName("realestate_addr_div1")[2];
						var realestate_addr_div2=document.getElementsByName("realestate_addr_div2")[2];
						var estate_addr=document.getElementsByName("estate_addr")[2];
					}
				}
			}
			var house_name=String(estate_addr.options[estate_addr.selectedIndex].text);	
			var area_name=String(area_div.options[area_div.selectedIndex].text);

			if(house_name=='선택')
			{
				alert('건물을 선택해주세요.');
				location.reload();
			}
			if(area_name=='면적선택')
			{
				alert('면적을 선택해주세요.');
				location.reload();
			}

			var array_area_name = area_name.split("/");
			var supply_area = array_area_name[0];		//공급면적
			var dedicated_area = array_area_name[1];	//전용면적
			var house_holds = "";

			document.getElementById('search_yn').value = "Y";

			$(".del_tag").html("");
			var visible=document.getElementById("estate_acount_view");
			visible.style.display="";

			var total_max_money = 0;

			var fullvalue = document.getElementById('dong_div').value;
			var law_code=document.getElementById('fullvalue');
			law_code.value=fullvalue;
			var LAWD_CD = law_code.value.substring(0,5);
			var estate_addr = document.getElementsByName("estate_addr")[0];
			var apart_addr_val = String(estate_addr.options[estate_addr.selectedIndex].text);
			var estate_addr2 = document.getElementsByName("estate_addr")[1];
			var villa_addr_val = String(estate_addr2.options[estate_addr2.selectedIndex].text);
			var estate_addr3 = document.getElementsByName("estate_addr")[2];
			var office_addr_val = String(estate_addr3.options[estate_addr3.selectedIndex].text);
			var addr_kor= document.getElementById('fulladdr').value;  //한글주소
			var value_split= realestate_addr_div2.value.split("/");
			var ho_cd = value_split[0];																			//호일련번호
			var sise_cd = value_split[1];																			//시세주택형일련번호

			var ajax_law_code = fullvalue.substring(0,8);																			//법정동코드 8자리
			var dong=String(realestate_addr_div1.options[realestate_addr_div1.selectedIndex].text);				//동이름
			var dong_cd=String(realestate_addr_div1.options[realestate_addr_div1.selectedIndex].value);		//동일련번호
			var goods_cd=String(estate_addr.options[estate_addr.selectedIndex].value);								//건물식별자
			var realestate_area_div = document.getElementsByName("realestate_area_div")[0];					//면적선택값(주택형일련번호)
			var realestate_area_div_val = String(realestate_area_div.options[realestate_area_div.selectedIndex].value);
			spinner_ck();

		
			//kb시세조회하기
			$.ajax({
				url:"/ups/getkbquick",	
				type:"get",
				data:
				{
					ho_cd:ho_cd,									// 호일련번호
					sise_cd:realestate_area_div_val,		// 시세주택형일련번호
					dong:dong,										// 동이름
					dong_cd:dong_cd,							// 동일려번호
					addr_cd:ajax_law_code,					// 법정동코드
					func_flag:'S3',									// 실행시킬 함수 플래그 
					goods_cd:goods_cd,							// 물건식별자
					opt1:chkhouse								// opt1 = 아파트, 오피스텔, 다세대 구분
				},
				async: false,
				success:function(rs)
				{	
					console.log(addr_sise_json);						
					var addr_sise_json = JSON.parse(rs);
					if(addr_sise_json!="")
					{
						document.getElementsByName("sise_renew")[0].innerHTML = '시세정보가 없습니다.';
						document.getElementsByName("kb_search_address")[0].innerHTML = '시세정보가 없습니다.';
						document.getElementsByName("kb_search_address_old")[0].innerHTML = '시세정보가 없습니다.';
						document.getElementsByName("kb_search_housename")[0].innerHTML = '시세정보가 없습니다.';
						document.getElementsByName("low_avg_price")[0].innerHTML = '시세정보가 없습니다.';
						document.getElementsByName("normal_avg_price")[0].innerHTML = '시세정보가 없습니다.';
						document.getElementsByName("kb_build_month")[0].innerHTML = '시세정보가 없습니다.';
						document.getElementsByName("kb_search_cnt")[0].innerHTML = '시세정보가 없습니다.';
						document.getElementsByName("high_avg_price")[0].innerHTML = '시세정보가 없습니다.';
						document.getElementsByName("land_area")[0].innerHTML = '시세정보가 없습니다.';
						for (var i=0;i<addr_sise_json.시세목록.length;i++ )
						{						
							if(addr_sise_json.시세목록.length!=0)
							{
								//선택한 면적값(주택일련번호)에 일치한 시세정보를 매칭
								if(addr_sise_json.시세목록[i].주택형일련번호 == realestate_area_div_val)
								{
									var renew = addr_sise_json.시세기준일.split('.');
									var sise_renew = renew[0]+"-"+renew[1]+"-"+renew[2];
									var build_month = addr_sise_json.시세목록[i].입주년월.substr(0,4)+"-"+addr_sise_json.시세목록[i].입주년월.substr(4,2);
									var kb_money = addr_sise_json.시세목록[i].매매일반거래가;
									
									if(document.getElementById("low_high_floor_chk").checked)
									{
										kb_money = addr_sise_json.시세목록[i].매매하한가;
									}
									document.getElementsByName("sise_renew")[0].innerHTML = sise_renew;
									document.getElementsByName("kb_search_address")[0].innerHTML = addr_sise_json.시세목록[i].주소;
									document.getElementsByName("kb_search_housename")[0].innerHTML = addr_sise_json.시세목록[i].단지명;
									document.getElementsByName("low_avg_price")[0].innerHTML = addr_sise_json.시세목록[i].매매하한가;
									document.getElementsByName("normal_avg_price")[0].innerHTML = addr_sise_json.시세목록[i].매매일반거래가;
									document.getElementsByName("kb_build_month")[0].innerHTML = build_month;
									document.getElementsByName("kb_search_cnt")[0].innerHTML = addr_sise_json.시세목록[i].총세대수.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
									house_holds = addr_sise_json.시세목록[i].총세대수.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
									document.getElementsByName("high_avg_price")[0].innerHTML = addr_sise_json.시세목록[i].매매상한가;
									document.getElementsByName("land_area")[0].innerHTML = addr_sise_json.시세목록[i].면적+"/"+addr_sise_json.시세목록[i].전용면적;

									break;
								}
							}
						}
					}
					$.ajax({		
						url:"/ups/getkbquick2",	
						type:"get",
						data:
						{
							addr_cd:ajax_law_code,				// 법정동코드
							goods_cd:goods_cd,					// 물건식별자
							area_cd:realestate_area_div_val,	// 주택형일련번호
							opt1:chkhouse						// 건물종류
						},
						async: false,
						success:function(rs)
						{	
							var addr_sise_json = JSON.parse(rs);
							console.log(addr_sise_json);
							if(addr_sise_json!="")
							{
								var api_cnt=0;
								//최대표시수
								var api_max=5;
								
								if(addr_sise_json.ARRAY수_매매.length!=0)
								{
									document.getElementsByName("api_money")[0].innerHTML = "최근 6개월간 실거래가 없습니다.";
									document.getElementsByName("api_date")[0].innerHTML = "최근 6개월간 실거래가 없습니다.";		
									document.getElementsByName("api_house_name")[0].innerHTML = "최근 6개월간 실거래가 없습니다.";
									document.getElementsByName("api_house_floor")[0].innerHTML = "최근 6개월간 실거래가 없습니다.";

									for(var i=0;i<addr_sise_json.ARRAY수_매매.length;i++)
									{	
										if(addr_sise_json.ARRAY수_매매[i].매매실거래금액!="-")
										{
											var renew = addr_sise_json.ARRAY수_매매[i].조회기간.split('.');
											var renew_api_date = renew[0]+"-"+renew[1]+"-"+renew[2];
												document.getElementsByName("api_money")[api_cnt].innerHTML = addr_sise_json.ARRAY수_매매[i].실거래금액;
												document.getElementsByName("api_date")[api_cnt].innerHTML = renew_api_date;
												document.getElementsByName("api_house_name")[api_cnt].innerHTML = house_name;
												document.getElementsByName("api_house_floor")[api_cnt].innerHTML = addr_sise_json.ARRAY수_매매[i].해당층수;
												//block으로 할 경우, 크롬브라우저에서 테이블이 깨짐. (table-row 가 익스,크롬 둘다 적용잘됨)
												document.getElementsByName('api_tr')[api_cnt].style.display = 'table-row';

											api_cnt++;

											//실거래평가금액 내역은 최대 5개까지만 보여준다
											if(api_cnt==api_max)
											{
												break;
											}

											//평가기준(실거래) 평가액은 여러가지 내역중 가장 최근의(처음나오는 매매가) 실거래가로 한다.
											if(api_cnt==1)
											{
												//document.getElementById("api_real_trade_money").innerHTML = addr_sise_json.ARRAY수_매매[i].실거래금액;
											}
										}
									}
									if(api_cnt<api_max)
									{
										if(api_cnt==0)
										{
											document.getElementsByName('api_tr')[api_cnt].style.display = 'table-row';
											api_cnt = 1;
										}
										for(var j=api_cnt;j<api_max;j++)
										{	
											document.getElementsByName('api_tr')[j].style.display = 'none';
										}
									}
								}
								else
								{				
									document.getElementsByName("api_money")[0].innerHTML = "최근 6개월간 실거래가 없습니다.";			
									document.getElementsByName("api_date")[0].innerHTML = "최근 6개월간 실거래가 없습니다.";		
									document.getElementsByName("api_house_name")[0].innerHTML = "최근 6개월간 실거래가 없습니다.";	
									document.getElementsByName("api_house_floor")[0].innerHTML = "최근 6개월간 실거래가 없습니다.";
									document.getElementsByName('api_tr')[0].style.display = 'table-row';

									for(var k=1; k<api_max; k++)
									{
										document.getElementsByName('api_tr')[k].style.display = 'none';
									}
								}
								//make_limit_money();
							}
							// 추가 KB매물시세 조회
							$.ajax({
							url:"/ups/getkbsale",	
							type:"get",
							data:
							{
								addr_cd:ajax_law_code,			//법정동코드
								build_name:house_name,			//건물명
								supply_area:supply_area,			//공급면적(면적)
								dedicated_area:dedicated_area	//전용면적
								//goods_cd:goods_cd				//물건식별자
							},
							success:function(rs)
							{
								var addr_sale_json = JSON.parse(rs);
								console.log(addr_sale_json);
									document.getElementsByName("kb_sale_house_name")[0].innerHTML = '매물정보가 없습니다.';
									document.getElementsByName("kb_sale_area")[0].innerHTML = '매물정보가 없습니다.';
									document.getElementsByName("kb_sale_house_floor")[0].innerHTML = '매물정보가 없습니다.';
									document.getElementsByName("kb_sale_money")[0].innerHTML = '-';
									document.getElementsByName('sale_tr')[0].style.display = 'table-row';

								//조회버튼 클릭시 운용LTV, 한도 초기화
								for(var i =0; i < document.getElementsByClassName("limit_money").length; ++i)
								{
									kb_use_ltv = document.getElementsByClassName("use_ltv")[i].childNodes[1]; // 운용LTV
									kb_limit_money= document.getElementsByClassName("limit_money")[i].childNodes[1];	//한도

									kb_use_ltv.innerHTML ="-";
									kb_limit_money.innerHTML ="-";
								}
								var cnt=0;
								var sale_max=10;

								if(addr_sale_json!="")
								{	
									var api_real_trade_money= $("#api_real_trade_money").text();

									for (var i=0;i<Object.keys(addr_sale_json.ARRAY수1).length;i++ )
									{		
										//주택형일현번호와 일치한데이터만 출력(선택한 면적에 관한 정보만)
										if(addr_sale_json.ARRAY수1[i].면적일련번호 == realestate_area_div_val)
										{		
											document.getElementsByName("kb_sale_house_name")[cnt].innerHTML = addr_sale_json.ARRAY수1[i].매물명;
											document.getElementsByName("kb_sale_area")[cnt].innerHTML = (Math.floor(addr_sale_json.ARRAY수1[i].공급면적*100))/100+"/"+(Math.floor(addr_sale_json.ARRAY수1[i].전용면적*100))/100;
											document.getElementsByName("kb_sale_house_floor")[cnt].innerHTML = addr_sale_json.ARRAY수1[i].해당층수+" / "+addr_sale_json.ARRAY수1[i].총지상층수;
											document.getElementsByName("kb_sale_money")[cnt].innerHTML = addr_sale_json.ARRAY수1[i].매매가.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
											document.getElementsByName('sale_tr')[cnt].style.display = 'table-row';

											if(api_real_trade_money=="최근 6개월간 실거래가 없습니다." && cnt==0)
											{
												document.getElementById("api_real_trade_money").innerHTML = document.getElementsByName("kb_sale_money")[cnt].innerHTML;
												// make_limit_money();
											}
											cnt++;
											if(cnt>=sale_max)
											{
												break;
											}
										}
									}

									if(cnt<sale_max)
									{
										if(cnt==0) cnt = 1;
										for(var j=cnt;j<sale_max;j++) document.getElementsByName('sale_tr')[j].style.display = 'none';
									}
								}
								else
								{
									document.getElementsByName("kb_sale_house_name")[0].innerHTML = '매물정보가 없습니다.';
									document.getElementsByName("kb_sale_area")[0].innerHTML = '매물정보가 없습니다.';
									document.getElementsByName("kb_sale_house_floor")[0].innerHTML = '매물정보가 없습니다.';
									document.getElementsByName("kb_sale_money")[0].innerHTML = '매물정보가 없습니다.';

									for(var k=1; k<sale_max; k++)
									{
										document.getElementsByName('sale_tr')[k].style.display = 'none';
									}
								}
								{{-- var agent_name = @json($arr_agent_name);
								var config =@json($v1);
								var config_value_land = @json($arr_value_land);
								var key_cnt=0;
								//최저, 고층 체크박스 
								var low_high_floor_chk = document.getElementById("low_high_floor_chk").checked;
								for(var j in agent_name)
								{
									var land = get_chk_land(ajax_law_code);

									for(var i = 0; i < config.length; i++)
									{
										if(config[i]['corp']==j)
										{//첫번째 if 제휴사(corp) 제휴사 받아와서 사용
											//두번째 if 급지(land) 
											if(config[i]['land'] == land)
											{
												if(get_chk_area(config[i]['area'], supply_area))
												{
													// 네번째 if문에서 세대수로 일반, 일반1 특수가 나뉘는데 일반일 땐 특수를 출력하면 안된다.
													// 마찬가지로 특수일 땐 일반을 출력하면 안된다.
													if("normal" == get_chk_households(config[i]['households'], house_holds))
													{//네번째 if 세대수(households)
														if(config[i]['ltv'] != '00')
														{
															document.getElementsByName(config[i]['div']+"_"+config[i]['households']+"_use_ltv")[key_cnt].innerHTML= config[i]['ltv'];
															document.getElementsByName(config[i]['div']+"_"+config[i]['households']+"_use_ratio")[key_cnt].innerHTML= config[i]['ratio'];
														}
														else{
															document.getElementsByName(config[i]['div']+"_"+config[i]['households']+"_use_ltv")[key_cnt].innerHTML="불가";
														}
													}
													else if("special" == get_chk_households(config[i]['households'], house_holds))
													{//네번째 if 세대수(households)
														document.getElementsByName(config[i]['div']+"_"+config[i]['households']+"_use_ltv")[key_cnt].innerHTML= config[i]['ltv'];
														document.getElementsByName(config[i]['div']+"_"+config[i]['households']+"_use_ratio")[key_cnt].innerHTML= config[i]['ratio'];
													}
												}
												// 데이터를 보내는 구간
											}
										}
									}
									
									if(!low_high_floor_chk)
										document.getElementsByName("1_money")[key_cnt].innerHTML = document.getElementById("normal_avg_price").innerHTML;
									else
										document.getElementsByName("1_money")[key_cnt].innerHTML = document.getElementById("low_avg_price").innerHTML;

									if(isNaN(document.getElementsByName("api_money")[0].innerHTML.replace(/,/g,"")))
										document.getElementById("real_trade_money").value = "-";
									else
										document.getElementById("real_trade_money").value = document.getElementsByName("api_money")[0].innerHTML;

									document.getElementsByName("2_money")[key_cnt].innerHTML = document.getElementById("real_trade_money").value;
									document.getElementsByName("3_money")[key_cnt].innerHTML = total_max_money.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
									key_cnt++;
								} --}}
								
									// make_limit_money();
									close_img();
								},
								error:function(request,status,error)
								{
									alert('에러입니다');
									close_img();
								}
							});
						},
						error:function(request,status,error)
						{
							alert('에러입니다');
						}
					});			
					
					//2019-09-27 추가 지번주소 가져오기 - ostin
					$.ajax({
						url:"/ups/getkbaddress",	
						type:"get",
						data:
						{
							addr_cd:ajax_law_code,		// 법정동코드
							goods_cd:goods_cd				// 물건식별자
						},
						async: false,
						success:function(rs)
						{	
							document.getElementsByName("kb_search_address_old")[0].innerHTML = rs;
						},
						error:function(request,status,error)
						{
							alert('에러입니다');
							close_img();
						}
					});
				},
				error:function(request,status,error)
				{
					alert('에러입니다');
					close_img();
				}
			});
			kb_sise_search();
	}
	//kb시세면적 조회
	function kb_sise_search()
	{	
		var fullvalue = document.getElementById('dong_div').value;		
		var law_code_kb = fullvalue.substring(0,8);	 //KB추가시세조회에 필요한 법정동 8자리
		var house_div = document.getElementsByName("estate_type"); 

		for(var i=0;i<house_div.length;i++) 
		{
			if(house_div[i].checked == true)
			{
				var chkhouse = house_div[i].value; // 아파트, 오피스텔, 빌라 구분자
				if(chkhouse=='apart')
				{
					var estate_addr=document.getElementsByName("estate_addr")[0];
				}
				else if(chkhouse=='office')
				{
					var estate_addr=document.getElementsByName("estate_addr")[2];
				}
			}
		}
		if(chkhouse=='villa')
		{
			return alert('빌라는 추가조회가 불가능합니다.');
		}
		var goods_cd=String(estate_addr.options[estate_addr.selectedIndex].value);		//건물식별자
		var house_name=String(estate_addr.options[estate_addr.selectedIndex].text);	//건물이름
		var addr_kor= document.getElementById('fulladdr').value;									//한글주소
		var estate_member_no = document.getElementById('estate_member_no').value;
		if(estate_member_no!=''&&goods_cd=='')
		{
			law_code_kb = document.getElementById('lawd_cd').value.substring(0,8);
			goods_cd = document.getElementById('goods_cd').value;
			addr_kor = document.getElementById('addr_kor').value;
			house_name=document.getElementById('house_name').value;
		}
		MarketArea(chkhouse, law_code_kb, goods_cd, addr_kor, house_name);
	}
	{{-- 시세정보 --}}
	function MarketArea(chkhouse, law_code_kb, goods_cd, addr_kor, house_name)
	{
		$("#kb_json_parents").empty()
		function sendChildValue(a,b,c,d,e,f,g,h,i,j,k,l,m)
		{
			opener.setChildValue2(a,b,c,d,e,f,g,h,i,j,k,l,m);
			window.close();
		}
		var house_name = house_name;
		var addr_kor = addr_kor;				
		var chkhouse = chkhouse;
		var LAWD_CD = law_code_kb;
		var goods_cd = goods_cd;
		//kb시세추가조회하기
		$.ajax({
			url:"/ups/getkbarea",
			type:"get",
			data:{
				addr_cd:LAWD_CD,			// 법정동코드 
				goods_cd:goods_cd,			// 물건식별자
				opt1:chkhouse,				// opt1 = 아파트, 오피스텔, 다세대 구분
				func_flag:'S3',					// 실행시킬 함수 플래그 
			},
			success:function(rs){	
				var addr_sise_json = JSON.parse(rs);	
				var marketPrice = "";
				if(addr_sise_json.시세목록.length!=0)
				{
					for (var i=0;i<addr_sise_json.시세목록.length ;i++ )
					{
						var renew_day = addr_sise_json.시세기준일.replace(/\./gi, "-");
						var str = "<tr onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' bgcolor=#FFFFFF><td  align=center >"+addr_sise_json.시세목록[i].단지명+"</td>";		
						str+="<td  align=center ><a href='javascript:sendChildValue(\""+addr_sise_json.시세목록[i].면적+"/"+addr_sise_json.시세목록[i].전용면적
							+"\",\""+addr_sise_json.시세목록[i].매매하한가
							+"\",\""+addr_sise_json.시세목록[i].매매일반거래가
							+"\",\""+addr_sise_json.시세목록[i].매매상한가
							+"\",\""+addr_sise_json.시세목록[i].주소
							+"\",\""+addr_sise_json.시세목록[i].단지명
							+"\",\""+renew_day
							+"\",\""+addr_sise_json.시세목록[i].총세대수
							+"\",\""+addr_sise_json.시세목록[i].입주년월
							+"\",\""+LAWD_CD
							+"\",\""+goods_cd
							+"\",\""+addr_sise_json.시세목록[i].주택형일련번호
							+"\",\""+chkhouse
							+"\")'>"+addr_sise_json.시세목록[i].면적
							+"/"+addr_sise_json.시세목록[i].전용면적
							+"</a></td>";		
						str+="<td align=right>"+addr_sise_json.시세목록[i].매매하한가+"</td>"
						str+="<td align=right>"+addr_sise_json.시세목록[i].매매일반거래가+"</td>"
						str+="<td align=right>"+addr_sise_json.시세목록[i].매매상한가+"</td>"
						str+="<td align=right>"+addr_sise_json.시세목록[i].전세하한가+"</td>"
						str+="<td align=right>"+addr_sise_json.시세목록[i].전세일반거래가+"</td>"
						str+="<td align=right>"+addr_sise_json.시세목록[i].전세상한가+"</td>"
						str+="<td align=right>"+addr_sise_json.시세목록[i].월세보증금액+"</td>"
						str+="<td align=right>"+addr_sise_json.시세목록[i].월세금액+"</td>"
						$("#kb_json_parents").append(str);	
						
						marketPrice += addr_sise_json.시세목록[i].주소+"|"
											+addr_sise_json.시세목록[i].단지명 +"|"
											+addr_sise_json.시세목록[i].면적+"/"+addr_sise_json.시세목록[i].전용면적 +"|"
											+addr_sise_json.시세목록[i].매매하한가+"|"
											+addr_sise_json.시세목록[i].매매일반거래가+"|"
											+addr_sise_json.시세목록[i].매매상한가+"|"
											+addr_sise_json.시세목록[i].전세하한가+"|"
											+addr_sise_json.시세목록[i].전세일반거래가+"|"
											+addr_sise_json.시세목록[i].전세상한가+"|"
											+addr_sise_json.시세목록[i].월세보증금액+"|"
											+addr_sise_json.시세목록[i].월세금액+"||";
											callmarketprice(marketPrice);
					}
					var renew_day = "시세갱신일 : "+ addr_sise_json.시세기준일.replace(/\./gi, "-");
					$("#sise_renew_day").append(renew_day);
				}
				else
				{
					var str = "<tr onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' bgcolor=#FFFFFF><td colspan='10' align=center >추가 시세조회 결과가 없습니다.</td>";
					$("#kb_json_parents").append(str);	
				}
			},
			error:function(request,status,error){
				alert('에러입니다');
			}
		});	
		function changeTrColor(trObj, oldColor, newColor) 
		{
			trObj.style.backgroundColor = newColor;
			trObj.onmouseout = function(){
				trObj.style.backgroundColor = oldColor;
			}
		}
	}
	var resultPrice= '';
	function callmarketprice(p)
	{
		resultPrice = p;
	}

	//api 조회 팝업창에서 나온 금액 넣는 부분
	function setChildValue(money,date,area,house_name,floor)
	{
		var money = money.trim();
		document.getElementById("api_money1").innerHTML = money.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		document.getElementById("api_date1").innerHTML = date;
		document.getElementById("api_house_name1").innerHTML = house_name+"("+floor+")";
		document.getElementById('molit_api_floor').value = floor;
		//한도 재계산
		var molit_limit_money = document.getElementsByName("molit_limit_money");
		var kb_limit_money = document.getElementsByName("kb_limit_money");
		var api_real_trade_money= $("#api_real_trade_money").text();
		api_real_trade_money=api_real_trade_money.replace(/,/gi,"");
		api_real_trade_money=api_real_trade_money.replace(" ","");
		var molit_use_ltv=document.getElementsByName("molit_use_ltv");
		var kb_use_ltv=document.getElementsByName("kb_use_ltv");	
		var final_limit_money=document.getElementsByName("final_limit_money_1");
		var kb_money = $("#kb_sise_money").text();
		kb_money=kb_money.replace(/,/gi,"");
		kb_money=kb_money.replace(" ","");

		{{-- var arr = @json($arr_agent);
		var key_cnt=0;
		
		for( var key in arr )
		{
			key_cnt++;					
		}
		for(var j=0;j<key_cnt;j++)
		{	
			var limit =	 ((api_real_trade_money*(molit_use_ltv[j].innerHTML)/100-first_loan_money)*10)/1000;
			molit_limit_money[j].innerHTML=(Math.floor(((api_real_trade_money*(molit_use_ltv[j].innerHTML)/100-first_loan_money)*10)/1000)*100).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			var kb_limit= ((kb_money*(kb_use_ltv[j].innerHTML)/100-first_loan_money)*10)/1000;
			if(parseInt(limit)<parseInt(kb_limit))
			{
				final_limit_money[j].innerHTML=(Math.floor(((api_real_trade_money*(molit_use_ltv[j].innerHTML)/100-first_loan_money)*10)/1000)*100).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			}
			else
			{
				final_limit_money[j].innerHTML=(Math.floor(((kb_money*(kb_use_ltv[j].innerHTML)/100-first_loan_money)*10)/1000)*100).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
			}
		} --}}
	} 
	//kb시세면적조회 팝업창 받아온값 넣기
	function setChildValue2(kb_area,kb_low_price,kb_normal_price,kb_high_price,kb_address,kb_housename,kb_renewday,kb_search,kb_build_month,law_cd,goods_cd,area_cd,chkhouse)
	{
		var kb_low_price = kb_low_price.trim();
		var kb_normal_price = kb_normal_price.trim();
		var kb_high_price = kb_high_price.trim();
		var build_month = kb_build_month.substr(0,4)+"-"+kb_build_month.substr(4,2);
		var kb_money = kb_normal_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");

		if(document.getElementById("low_high_floor_chk").checked)
		{
			kb_money = kb_low_price;
		}

		document.getElementsByName("sise_renew").innerHTML = kb_renewday;
		document.getElementsByName("kb_search_address").innerHTML = kb_address;
		document.getElementsByName("kb_search_housename").innerHTML = kb_housename;
		document.getElementsByName("land_area").innerHTML = kb_area;
		document.getElementsByName("low_avg_price").innerHTML = kb_low_price;
		document.getElementsByName("normal_avg_price").innerHTML = kb_normal_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		document.getElementsByName("kb_build_month").innerHTML = build_month;
		document.getElementsByName("kb_search_cnt").innerHTML = kb_search.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		var house_holds = kb_search.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		document.getElementsByName("high_avg_price").innerHTML = kb_high_price.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		document.getElementsByName("kb_sise_money").innerHTML = kb_money;

		//면적별시세 추가 (2019-10-18)
		//law_cd(법정동코드),goods_cd(물건식별자),area_cd(주택형일련번호)를 통해 조회한다.
		$.ajax({		
			url:"/ups/getkbquick2",	
			type:"get",
			data:
			{
				addr_cd:law_cd,					// 법정동코드
				goods_cd:goods_cd,				// 물건식별자
				area_cd:area_cd,					// 주택형일련번호
				opt1:chkhouse					// 건물종류
			},
			success:function(rs)
			{	
				var addr_sise_json = JSON.parse(rs);			
				console.log(addr_sise_json);
				if(addr_sise_json!="")
				{
					var api_cnt=0;
					//최대표시수
					var api_max=5;
					
					if(addr_sise_json.ARRAY수_매매.length!=0)
					{
						document.getElementsByName("api_money")[0].innerHTML = "최근 6개월간 실거래가 없습니다.";
						document.getElementsByName("api_date")[0].innerHTML = "최근 6개월간 실거래가 없습니다.";
						//document.getElementById("api_area").innerHTML = "최근 6개월간 실거래가 없습니다.";	
						document.getElementsByName("api_house_name")[0].innerHTML = "최근 6개월간 실거래가 없습니다.";
						document.getElementsByName("api_house_floor")[0].innerHTML = "최근 6개월간 실거래가 없습니다.";

						for(var i=0;i<addr_sise_json.ARRAY수_매매.length;i++)
						{	
							if(addr_sise_json.ARRAY수_매매[i].매매실거래금액!="-")
							{
								var renew = addr_sise_json.ARRAY수_매매[i].조회기간.split('.');
								var renew_api_date = renew[0]+"-"+renew[1]+"-"+renew[2];
									document.getElementsByName("api_money")[api_cnt].innerHTML = addr_sise_json.ARRAY수_매매[i].실거래금액;

									document.getElementsByName("api_date")[api_cnt].innerHTML = renew_api_date;

									document.getElementsByName("api_house_name")[api_cnt].innerHTML = kb_housename;

									document.getElementsByName("api_house_floor")[api_cnt].innerHTML = addr_sise_json.ARRAY수_매매[i].해당층수;

								//block으로 할 경우, 크롬브라우저에서 테이블이 깨짐. (table-row 가 익스,크롬 둘다 적용잘됨)
								document.getElementsByName('api_tr')[api_cnt].style.display = 'table-row';

								api_cnt++;

								//실거래평가금액 내역은 최대 5개까지만 보여준다
								if(api_cnt==api_max)
								{
									break;
								}
							}
						}

						if(api_cnt<api_max)
						{
							if(api_cnt==0)
							{
								document.getElementsByName('api_tr')[api_cnt].style.display = 'table-row';
								api_cnt = 1;
							}
		
							for(var j=api_cnt;j<api_max;j++)
							{	
								document.getElementsByName('api_tr')[j].style.display = 'none';
							}
						}
					}
					else
					{
						document.getElementsByName("api_money")[0].innerHTML = "최근 6개월간 실거래가 없습니다.";			
						document.getElementsByName("api_date")[0].innerHTML = "최근 6개월간 실거래가 없습니다.";		
						//document.getElementById("api_area").innerHTML = "최근 6개월간 실거래가 없습니다.";		
						document.getElementsByName("api_house_name")[0].innerHTML = "최근 6개월간 실거래가 없습니다.";	
						document.getElementsByName("api_house_floor")[0].innerHTML = "최근 6개월간 실거래가 없습니다.";
						document.getElementsByName("api_tr")[0].style.display = 'table-row';

						for(var k=1; k<api_max; k++)
						{
							document.getElementsByName('api_tr')[k].style.display = 'none';
						}
					}
						
					{{-- var agent_name = @json($arr_agent_name);
					var config =@json($v1);
					var config_value_land = @json($arr_value_land);
					var key_cnt=0;

					//최저, 고층 체크박스 
					var low_high_floor_chk = document.getElementById("low_high_floor_chk").checked;

					for(var j in agent_name)
					{
						if(!low_high_floor_chk)
							document.getElementsByName("1_money")[key_cnt].innerHTML = document.getElementsByName("normal_avg_price").innerHTML;
						else
							document.getElementsByName("1_money")[key_cnt].innerHTML = document.getElementsByName("low_avg_price").innerHTML;

						var land = get_chk_land(law_cd);

						for(var i = 0; i < config.length; i++)
						{	
							//첫번째 if 제휴사(corp) 제휴사 받아와서 사용
							if(config[i]['corp']==j)
							{
								//두번째 if 지역(land) 
								//이젠 지역이아니라 급지 별로 나눠야한다.
								if(config[i]['land']==land)
								{
									var temp_kb_area = kb_area.split("/");
									if(get_chk_area(config[i]['area'], temp_kb_area[0]))
									{
										// 세번째 if문에서 세대수로 일반, 일반1 특수가 나뉘는데 일반일 땐 특수를 출력하면 안된다.
										// 마찬가지로 특수일 땐 일반을 출력하면 안된다.
										if("normal" == get_chk_households(config[i]['households'], house_holds))
										{
											//네번째 if 세대수(households)
											if(config[i]['ltv'] != '00')
											{
												document.getElementsByName(config[i]['div']+"_"+config[i]['households']+"_use_ltv")[key_cnt].innerHTML= config[i]['ltv'];
												document.getElementsByName(config[i]['div']+"_"+config[i]['households']+"_use_ratio")[key_cnt].innerHTML= config[i]['ratio'];
											}
											else
											{
												document.getElementsByName(config[i]['div']+"_"+config[i]['households']+"_use_ltv")[key_cnt].innerHTML="불가";
											}
										}
										else if("special" == get_chk_households(config[i]['households'], house_holds))
										{
												//네번째 if 세대수(households)
												document.getElementsByName(config[i]['div']+"_"+config[i]['households']+"_use_ltv")[key_cnt].innerHTML= config[i]['ltv'];
												document.getElementsByName(config[i]['div']+"_"+config[i]['households']+"_use_ratio")[key_cnt].innerHTML= config[i]['ratio'];
										}
										if(isNaN(document.getElementsByName("api_money")[0].innerHTML.replace(/,/g,"")))
										{
											document.getElementById("real_trade_money").value = "-";
										}
										else
										{
											document.getElementById("real_trade_money").value = document.getElementsByName("api_money")[0].innerHTML;
										}
										document.getElementsByName("2_money")[key_cnt].innerHTML = document.getElementById("real_trade_money").value;
									}
								}
							}	
						}
						key_cnt++;
					} --}}
					// make_limit_money();
				}		
			},
			error:function(request,status,error)
			{
				console.log(error.message);
				alert('에러입니다');
			}
		});		
	}
	// 스크래핑 누적테이블 조회 & 시세정보
	function selected(p)
	{	
		$('#siseTable').css("display", "");
		$('#estate_acount_view').css("display", "");
		var st_kb_addr = $(p).find('#st_kb_addr').val()
		var st_kb_addr_old = $(p).find('#st_kb_addr_old').val();
		var st_kb_house_name = $(p).find('#st_kb_house_name').val();
		var st_kb_sise_renew = $(p).find('#st_kb_sise_renew').val();
		var st_kb_area = $(p).find('#st_kb_area').val();
		var st_kb_build_month = $(p).find('#st_kb_build_month').val();
		var st_kb_search_cnt = $(p).find('#st_kb_search_cnt').val();
		var st_kb_high_avg_money = $(p).find('#st_kb_high_avg_money').val();
		var st_kb_normal_avg_money = $(p).find('#st_kb_normal_avg_money').val();
		var st_kb_low_avg_money = $(p).find('#st_kb_low_avg_money').val();

		var st_molit_trade_date = $(p).find('#st_molit_trade_date').val().split("|");
		var st_molit_house_floor = $(p).find('#st_molit_house_floor').val().split("|");
		var st_molit_real_trade_money = $(p).find('#st_molit_real_trade_money').val().split("|");

		var st_kb_sale_house_name = $(p).find('#st_kb_sale_house_name').val().split("|");			
		var st_kb_sale_area = $(p).find('#st_kb_sale_area').val().split("|");
		var st_kb_sale_house_floor = $(p).find('#st_kb_sale_house_floor').val().split("|");
		var st_kb_sale_money = $(p).find('#st_kb_sale_money').val().split("|");

		$("#realSise").empty();
		var sstr = "<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF'  class='tbg numTd del_tag'>"+st_kb_addr+"</td>";
		sstr +=	"<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF'  class='tbg numTd del_tag' >"+st_kb_addr_old+"</td>";
		sstr +=	"<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF'  class='tbg numTd del_tag' >"+st_kb_house_name+"</td>";
		sstr +=	"<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF'  class='tbg numTd del_tag' >"+st_kb_sise_renew+"</td>";
		sstr +=	"<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' id='land_area'  class='tbg numTd del_tag' >"+st_kb_area+"</td>";
		sstr +=	"<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF'  class='tbg numTd del_tag' >"+st_kb_build_month+"</td>";
		sstr +=	"<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' id='kb_search_cnt'  class='tbg numTd del_tag' >"+st_kb_search_cnt+"</td>";
		sstr +=	"<td align='right' onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' id='high_avg_price' class='tbg numTd del_tag' >"+st_kb_high_avg_money+"</td>";
		sstr +=	"<td align='right' onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' id='normal_avg_price' class='tbg numTd del_tag' >"+st_kb_normal_avg_money+"</td>";
		sstr +=	"<td align='right' onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' id='low_avg_price' class='tbg numTd del_tag' >"+st_kb_low_avg_money+"</td>";	
		$('#realSise').append(sstr);
		var arrStr ="";
		$(".realTrade").empty();
		$('#changeRealTrade').empty();

		for(var i = 0 ;  i < st_molit_trade_date.length-1; i++)
		{
			if(i==0)
			{
			sstr="<tr bgcolor='EEEEEE' style='text-align:right;'><th colspan='4'><span>(금액단위 : 만원)</span></th></tr><tr style='text-align:center; '><th class='titleTd' rowspan='1'>거래년월일</th>	<th class='titleTd' rowspan='1'>건물명</th><th class='titleTd' rowspan='1'>층</th>	<th class='titleTd' rowspan='1'>실거래평가금액</th></tr>";
			}

			if( st_molit_real_trade_money[i] != "undefined" && st_molit_real_trade_money[i] != null)
			{
				sstr += "<tr style='text-align:center;'><td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF'  class='tbg numTd del_tag' >"+st_molit_trade_date[i]+"</td>";
				sstr += "<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF'  class='tbg numTd del_tag' '>"+st_kb_house_name+"</td>";
				sstr += "<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF'  class='tbg numTd del_tag' >"+st_molit_house_floor[i]+"</td>";
				sstr += "<td align='right' onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF'  class='tbg numTd del_tag' >"+st_molit_real_trade_money[i]+"</td></tr>";
			}
			$('#changeRealTrade').append(sstr);			
			sstr="";
		}

		console.log(st_kb_sale_area.length);
		sstr="";
		arrStr ="";
		$(".kbSiSe").empty();
		$('#changeKbSiSe').empty();
		for(var i = 0 ;  i < st_kb_sale_area.length-1; i++)
		{
			if(i==0)
			{
			sstr="<tr bgcolor='EEEEEE' style='text-align:right;'><th colspan='4'><span>(금액단위 : 만원)</span><span>(면적단위 : m<sup>2</sup>)</span></th></tr><tr style='text-align:center; '><th class='titleTd' rowspan='1'>매물명</th>	<th class='titleTd' rowspan='1'>면적(공급/전용)</th><th class='titleTd' rowspan='1'>층</th>	<th class='titleTd' rowspan='1'>매매가</th></tr>";
			}

			if(st_kb_sale_money[i] != "" && st_kb_sale_money[i] != null)
			{
			sstr += "<tr style='text-align:center;'><td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF'  class='tbg numTd del_tag' >"+st_kb_sale_house_name+"</td>";
			sstr +=	"<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class='tbg numTd del_tag' >"+st_kb_sale_area[i]+"</td>";
			sstr +=	"<td onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class='tbg numTd del_tag' >"+st_kb_sale_house_floor[i]+"</td>";
			sstr +=	"<td align='right' onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' class='tbg numTd del_tag' >"+st_kb_sale_money[i]+"</td></tr>";
			arrStr+=sstr;
			}
			$('#changeKbSiSe').append(sstr);
			sstr="";
		}
		
		$("#kb_json_parents").empty();
		var kbMarketPrice = $(p).find('#kb_marketPrice').val();
		var currnetNum = $(p).find('#stack_current_num').val();
		
		// 시세거래 조회
		var slice_marketPrice = new Array();
		var slice_market_arr = kbMarketPrice.split("||");
		var cnt_slice_market = kbMarketPrice.split("||").length;

		for(var i = 0 ; i <	cnt_slice_market-1 ; i++)
		{
			slice_marketPrice = slice_market_arr[i].split("|");

			var market_addr = slice_marketPrice[0];
			var market_home_name = slice_marketPrice[1];
			var market_area = slice_marketPrice[2];
			var market_bargain_low = slice_marketPrice[3];
			var market_bargain_mid = slice_marketPrice[4];
			var market_bargain_high = slice_marketPrice[5];
			var market_jeonse_low = slice_marketPrice[6];
			var market_jeonse_mid = slice_marketPrice[7];
			var market_jeonse_high = slice_marketPrice[8];
			var market_montly_deposit = slice_marketPrice[9];
			var market_montly_pay = slice_marketPrice[10];

			var str = "<tr onmouseover=this.style.background='#DDE5F3' onmouseout=this.style.background='#FFFFFF' bgcolor=#FFFFFF><td align=center>"+market_home_name+"</td>";		
			str+="<td align=center >"+market_area+"</td>";		
			str+="<td align=right>"+market_bargain_low+"</td>";
			str+="<td align=right>"+market_bargain_mid+"</td>";
			str+="<td align=right>"+market_bargain_high+"</td>";
			str+="<td align=right>"+market_jeonse_low+"</td>";
			str+="<td align=right>"+market_jeonse_mid+"</td>";
			str+="<td align=right>"+market_jeonse_high+"</td>";
			str+="<td align=right>"+market_montly_deposit+"</td>";
			str+="<td align=right>"+market_montly_pay+"</td></tr>";
			$("#kb_json_parents").append(str);	
		}
				
	}
	//조회데이터 저장
	function real_estate_save()
	{
		house_div_con();
		var loan_real_estate_no = document.getElementById('loan_real_estate_no').value;	
		var loan_app_no= document.getElementById('loan_app_no').value;
		var loan_info_no= document.getElementById('loan_info_no').value;
		var do_si_div=document.getElementById('do_si_div');
		var do_si =String(do_si_div.options[do_si_div.selectedIndex].text);													//도,시구분 ;
		var gun_gu_si_div = document.getElementById('gun_gu_si_div');
		var gun_gu_si =String(gun_gu_si_div.options[gun_gu_si_div.selectedIndex].text);								//군,구,시 구분 ;
		var dong_div=document.getElementById('dong_div');
		var dong_li = String(dong_div.options[dong_div.selectedIndex].text);													//동 구분 ;
		var house_name = String(estate_addr.options[estate_addr.selectedIndex].text);									//건물이름
		var search_yn = document.getElementById('search_yn').value;
		if(house_name=='선택' || search_yn!='Y')
		{
			alert('정보입력 및 조회 후 저장하시기 바랍니다.');
			return false;
		}
		var dong = String(realestate_addr_div1.options[realestate_addr_div1.selectedIndex].text);					//동이름
		if(dong == "동선택")
		{
			dong = "동정보가 없습니다.";
		}
		var ho = String(realestate_addr_div2.options[realestate_addr_div2.selectedIndex].text);						//호이름
		if(ho == "호선택")
		{
			ho = "호정보가 없습니다.";
		}
		var low_high_floor_chk="";																											//최저층/고층 여부
		if(document.getElementById("low_high_floor_chk").checked)
		{
			low_high_floor_chk="Y";
		}
		var kb_addr = document.getElementById('kb_search_address').innerHTML;											//kb 조회 주소
		var kb_addr_old = document.getElementById('kb_search_address_old').innerHTML;								//kb 조회 주소(지번)
		var kb_house_name = document.getElementById('kb_search_housename').innerHTML;							//kb조회건물이름
		var kb_sise_renew = document.getElementById('sise_renew').innerHTML;											//kb시세갱신일
		var kb_area = document.getElementById('land_area').innerHTML;
		var kb_build_month = document.getElementById('kb_build_month').innerHTML;									//kb준공월
		var kb_search_cnt = document.getElementById('kb_search_cnt').innerHTML;										//kb총세대수
		var kb_high_avg_money = document.getElementById('high_avg_price').innerHTML;								//kb상위평균가
		var kb_normal_avg_money = document.getElementById('normal_avg_price').innerHTML;						//kb일반평균가
		var kb_low_avg_money = document.getElementById('low_avg_price').innerHTML;									//kb하위평균가
		var doc_first_loan_money=document.getElementsByName("first_loan_money");									//선순위대출금액
		var first_loan_money="";

		var kb_appraised_value_high = kb_high_avg_money.replace(',','')*10000;
		var kb_appraised_value_middle =kb_normal_avg_money.replace(',','')*10000;
		var kb_appraised_value_low = kb_low_avg_money.replace(',','')*10000;
		for(var i = 0; i < doc_first_loan_money.length; ++i)
		{
			if(i == doc_first_loan_money.length -1)
			{
				first_loan_money += doc_first_loan_money[i].value;
			}
			else
			{
				first_loan_money += doc_first_loan_money[i].value + "|";
			}
		}
		var doc_max_per=document.getElementsByName('max_per');																//최고액 산출 %
		var doc_max_money=document.getElementsByName('max_money');
		var max_per = "";
		var max_money = "";
		for(var i = 0; i < doc_max_per.length; ++i)
		{
			if(i == doc_max_per.length-1)
			{
				max_per += doc_max_per[i].value;
				max_money += doc_max_money[i].innerHTML;
				alert(max_per);
				alert(max_money);
			}
			else
			{
				max_per += doc_max_per[i].value + "|";
				max_money += doc_max_money[i].innerHTML + "|";
				alert(max_per);
				alert(max_money);
			}
		}
		var molit_trade_date = "";
		var molit_house_floor = "";
		var molit_real_trade_money = "";
		for(var i=0; i<document.getElementsByName('api_money').length; i++)
		{
			molit_trade_date += document.getElementsByName('api_date')[i].innerHTML+"|";												//공공데이터 거래년월일
			molit_house_floor += document.getElementsByName('api_house_floor')[i].innerHTML+"|";									//공공데이터 층
			molit_real_trade_money += document.getElementsByName('api_money')[i].innerHTML+"|";									//공공데이터 실거래가
			if(document.getElementsByName('api_money')[i].innerHTML=="" || document.getElementsByName('api_money')[i].innerHTML=="최근 6개월간 실거래가 없습니다.")
			{
				break;
			}
		}
		var molit_house_name = document.getElementsByName('api_house_name')[0].innerHTML.split("(");						//공공데이터 건물이름
		molit_house_name=molit_house_name[0];
		var kb_sale_area = "";
		var kb_sale_house_floor = "";
		var kb_sale_money = "";
		for(var i=0; i<document.getElementsByName('kb_sale_money').length; i++)
		{
			kb_sale_area += document.getElementsByName('kb_sale_area')[i].innerHTML+"|";											//KB매물시세 면적
			kb_sale_house_floor += document.getElementsByName('kb_sale_house_floor')[i].innerHTML+"|";						//KB매물시세 층
			kb_sale_money += document.getElementsByName('kb_sale_money')[i].innerHTML+"|";									//KB매물시세 매매가
			if(document.getElementsByName('kb_sale_money')[i].innerHTML=="" || document.getElementsByName('kb_sale_money')[i].innerHTML=="최근 6개월간 실거래가 없습니다.")
			{
				break;
			}
		}
		var kb_sale_house_name = document.getElementsByName('kb_sale_house_name')[0].innerHTML;						//KB매물시세 매물명
		var goods_cd=String(estate_addr.options[estate_addr.selectedIndex].value);													//건물식별자
		var lawd_cd = document.getElementById('dong_div').value;																			//법정동코드	
		var estate_member_no2 = document.getElementById('estate_member_no').value;
		var kb_marketprice = resultPrice;
		// 인서트 또는 업데이트 로직수행  
		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		$.ajax({		
			url:"/ups/realestateinfoaction",	
			type:"post",
			data:
			{
				structure_div:chkhouse,										// 건물구분
				loan_app_no:loan_app_no,							// 회원번호
				loan_info_no:loan_info_no,							// 계약번호
				do_si:do_si,														// 도,시구분
				gun_gu_si:gun_gu_si,  										// 군,구,시구분
				dong_li:dong_li,  												// 동구분
				house_name:house_name, 									// 건물이름
				dong:dong,  														// 건물동이름
				ho:ho,   																// 건물호이름
				low_high_floor_chk:low_high_floor_chk,					// 최저층/고층 여부
				kb_addr:kb_addr,													// kb조회주소
				kb_addr_old:kb_addr_old,										// kb조회주소(지번)
				kb_house_name:kb_house_name,							// kb조회건물이름
				kb_sise_renew:kb_sise_renew,								// kb조회시세갱신일
				kb_area:kb_area,													// kb조회면적
				kb_build_month:kb_build_month,							// kb 준공월
				kb_search_cnt:kb_search_cnt,								// kb총세대수
				kb_high_avg_money:kb_high_avg_money,				// kb상위평균가
				kb_normal_avg_money:kb_normal_avg_money,		// kb일반평균가
				kb_low_avg_money:kb_low_avg_money,					// kb하위평균가
				max_per:max_per,												// 최고액 산출 %
				max_money:max_money,										// 최고액
				molit_real_trade_money:molit_real_trade_money,	// 공공api실거래가
				molit_trade_date:molit_trade_date,						// 공공api거래년월일
				molit_house_name:molit_house_name ,					// 공공api건물이름
				molit_house_floor:molit_house_floor,						// 공공api건물층
				kb_sale_area:kb_sale_area,									// KB매물시세 면적
				kb_sale_house_floor:kb_sale_house_floor,				// KB매물시세 층
				kb_sale_house_name:kb_sale_house_name,			// KB매물시세 매물명
				kb_sale_money:kb_sale_money,							// KB매물시세 가격
				goods_cd:goods_cd,												// 물건식별자
				lawd_cd:lawd_cd,												// 법정동코드
				kb_marketprice:kb_marketprice,								// 시세조회가
				loan_real_estate_no:loan_real_estate_no								// 담보리스트번호
			},
			success:function(rs)
			{	

				if(rs.result=='Y')
				{
					alert(rs.msg);
					location.reload(true);
				}
				else
				{
					alert(rs.msg);
				}

				resultPrice ='';
			},
			error:function(request,status,error)
			{
				alert('에러입니다');
			}
		});	
		
	}

	function setOpenerVal()
	{
		if(!confirm("적용하시겠습니까?"))
		{
			return false;
		}

		$.ajaxSetup({
			headers: {
				'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
			}
		});
		$.ajax({
			url:"/erp/kbupdate/{{ $damboNo }}",
			type:"POST",
			data:
			{
				kbObjId: "{{ $kbObjId }}",
				kbTypeNo: "{{ $kbTypeNo }}"
			},
			success:function(rs)
			{	
				console.log("success");
				console.log(rs);
			},
			error:function(request,status,error)
			{
				console.log("fail");
				console.log(request);
				console.log(status);
				console.log(error);
			}
		});

		var land_area = document.getElementById("land_area").innerHTML.split("/");
		for (let i = 0; i < land_area.length; i++) {
			land_area[i] = land_area[i].trim();
		}
		var cd = document.getElementById("kb_build_date").innerHTML.split("-");
		var nap = document.getElementById("normal_avg_price").innerHTML;
		var nap2 = nap.replace(/,/gi, "");
		var lap = document.getElementById("low_avg_price").innerHTML;
		var lap2 = lap.replace(/,/gi, "");
		var hap = document.getElementById("high_avg_price").innerHTML;
		var hap2 = hap.replace(/,/gi, "");

		opener.document.getElementById("supply_m").value = land_area[0];
		// opener.document.getElementById("jeonyong_m").value = land_area[1];
		opener.document.getElementById("completion_date").value = cd[0];
		opener.document.getElementById("item_floor").value = document.getElementById("real_floor").value;  // 해당 층
		// opener.document.getElementById("item_total_floor").value = document.getElementById("").innerHTML;  // 층 층수
		opener.document.getElementById("item_total_dong").value = document.getElementById("kb_total_dong").innerHTML;
		opener.document.getElementById("item_households").value = document.getElementById("kb_households_cnt").innerHTML;
		// opener.document.getElementById("households_sum_all").value = document.getElementById("kb_search_cnt").innerHTML;
		opener.document.getElementById("value_middle").value = nap2.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		opener.document.getElementById("value_low").value = lap2.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		opener.document.getElementById("value_high").value = hap2.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ",");
		//$("#select_id").val("1").prop("selected", true);
		//opener.document.getElementById("kb_value_div").value
		window.close();
	}
	</script>

	@endsection

@endif