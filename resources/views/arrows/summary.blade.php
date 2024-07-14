@extends('layouts.master')


@section('content')
{{-- Content Wrapper. Contains page content --}}
<div class="col-12" >
    {{-- Main content--}} 
    <section class="content">	

        <div class="col-md-12 pl-0">
            <div class="card card-lightblue card-outline">
                <div class="box box-{{ config('view.box') }}">
                <form id="form" method="post">
                    @csrf
                    <div class="card-header pt-2">
					* {{$result['month']}} 월 총 영업일수 : {{$result['all_day']}}<br>
					* {{$result['month']}} 월 잔여영업일수 : {{$result['remain_day']}}
                        <div class="card-tools form-inline" id="searchBox" style=" justify-content: flex-end;">
                            <div class="mr-1 mb-1 mt-1" >
							</div> 
                            <div class="input-group date mt-0 mb-0 datetimepicker" id="info_date" data-target-input="nearest">
                                <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#info_date" id="info_date_id" name="info_date" DateOnly="true"  value="{{ $result['info_date'] ?? date("Y-m-d",strtotime("-1 days")) }}" size="6">
                                <div class="input-group-append" data-target="#info_date" data-toggle="datetimepicker">
                                    <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                </div>
                            </div>
							&nbsp;
							<button type="button" class="btn btn-default" onclick="sendit();" style="font-size:15px; height:30px;">
								<i class="fa fa-search"></i>
							</button>
							&nbsp;
                        </div>
                    </div>

                        <div>
                            <div id="loading-area"></div>
                            <div id="data-area" class="card-body p-0 m-0" style=" width:100%;">
								<div class="all_div">
									<span class="font16px fontBold">1. 총대출현황</span>
									<span style="float:right;">(단위 : 백만원, %)</span>
	                                <table id="reason_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
		                                <thead class="text-center">
		                                    <tr>
		                                        <th class="pr-1 pl-1">구분</th>
		                                        <th class="pr-2 pl-2">대출목표</th>
		                                        <th class="pr-1 pl-1">일대출금</th>
		                                        <th class="pr-1 pl-1">총대출금</th>
		                                        <th class="pr-1 pl-1">달성율</th>
		                                    </tr>
		                                </thead>
										<tr class="text-right">
											<td class="fontBold text-center">{{$result['info_date']}}</td>
											<td class="fontBold">8,000</td>
											<td class="fontBold">361.3</td>
											<td class="fontBold">4,506.1</td>
											<td class="fontBold">56.3%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center">전월</td>	
											<td>9,000</td>
											<td>396.8</td>
											<td>4,808.3</td>
											<td>53,4%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center">증감</td>
											<td class="decrease">↓ 1,000</td>
											<td class="decrease">↓ 35.5</td>
											<td class="decrease">↓ 302.1</td>
											<td class="increase">↑ 2.9%</td>
										</tr>
	                                </table>
									<span class="tableBottomText">* 신용대출 + 부동산대출</span>
								</div>
								<div class="all_div">
									<span class="font16px fontBold">2. 세부대출현황</span>
									<span style="float:right;">(단위 : 백만원, %)</span>
	                                <table id="loan_detail_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
										<thead class="text-center">
											<tr>
												<th class="pr-1 pl-1">구분</th>
												<th class="pr-2 pl-2">대출목표</th>
												<th class="pr-1 pl-1">일대출금</th>
												<th class="pr-1 pl-1">총대출금</th>
												<th class="pr-1 pl-1">최고액LTV</th>
												<th class="pr-1 pl-1">원금LTV</th>
												<th class="pr-1 pl-1">달성율</th>
												<th class="pr-1 pl-1">일평균대출액</th>
												<th class="pr-1 pl-1">예상달성액</th>
												<th class="pr-1 pl-1">예상달성율</th>
											</tr>
										</thead>
										<tr class="text-right">
											<td class="text-center" style="background-color:#f4f6f9;">신용</td>
											<td>7,650.0</td>
											<td>361.3</td>
											<td>4,496.1</td>
											<td>-</td>
											<td>-</td>
											<td>58.77%</td>
											<td>374.7</td>
											<td>7,118.9</td>
											<td>56.3%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center" style="background-color:#f4f6f9;">부동산</td>
											<td>7,650.0</td>
											<td>361.3</td>
											<td>4,496.1</td>
											<td>72.32%</td>
											<td>69.40%</td>
											<td>58.77%</td>
											<td>374.7</td>
											<td>7,118.9</td>
											<td>56.3%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center" style="background-color:#f4f6f9;">합계</td>
											<td>7,650.0</td>
											<td>361.3</td>
											<td>4,496.1</td>
											<td></td>
											<td></td>
											<td>58.77%</td>
											<td>374.7</td>
											<td>7,118.9</td>
											<td>56.3%</td>
										</tr>
	                                </table>
									<span class="tableBottomText">* 증감 비교기준 : 전월 동영업일수 기준</span>
								</div>
								<div class="all_div">
									<span class="font16px fontBold">3. 형태별대출현황</span>
									<br>
									<b>신용대출</b>
									<span style="float:right;">(단위 : 백만원)</span>
	                                <table id="reason_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
		                                <thead class="text-center">
		                                    <tr>
		                                        <th class="pr-1 pl-1">구분</th>
		                                        <th class="pr-2 pl-2">대출목표</th>
		                                        <th class="pr-1 pl-1">일대출금</th>
		                                        <th class="pr-1 pl-1">총대출금</th>
		                                        <th class="pr-1 pl-1">달성율</th>
		                                    </tr>
		                                </thead>
										<tr class="text-right">
											<td class="fontBold text-center">{{$result['info_date']}}</td>
											<td class="fontBold">8,000</td>
											<td class="fontBold">361.3</td>
											<td class="fontBold">4,506.1</td>
											<td class="fontBold">56.3%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center">전월</td>	
											<td>9,000</td>
											<td>396.8</td>
											<td>4,808.3</td>
											<td>53,4%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center">증감</td>
											<td class="decrease">↓ 1,000</td>
											<td class="decrease">↓ 35.5</td>
											<td class="decrease">↓ 302.1</td>
											<td class="increase">↑ 2.9%</td>
										</tr>
	                                </table>
									<b>담보대출</b>
									<span style="float:right;">(단위 : 백만원, %)</span>
									<table id="reason_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
		                                <thead class="text-center">
		                                    <tr>
		                                        <th class="pr-1 pl-1">구분</th>
		                                        <th class="pr-2 pl-2">대출목표</th>
		                                        <th class="pr-1 pl-1">일대출금</th>
		                                        <th class="pr-1 pl-1">총대출금</th>
		                                        <th class="pr-1 pl-1">달성율</th>
		                                    </tr>
		                                </thead>
										<tr class="text-right">
											<td class="fontBold text-center">{{$result['info_date']}}</td>
											<td class="fontBold">8,000</td>
											<td class="fontBold">361.3</td>
											<td class="fontBold">4,506.1</td>
											<td class="fontBold">56.3%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center">전월</td>	
											<td>9,000</td>
											<td>396.8</td>
											<td>4,808.3</td>
											<td>53,4%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center">증감</td>
											<td class="decrease">↓ 1,000</td>
											<td class="decrease">↓ 35.5</td>
											<td class="decrease">↓ 302.1</td>
											<td class="increase">↑ 2.9%</td>
										</tr>
	                                </table>
									<span class="tableBottomText">* 증감 비교기준 : 전월 동영업일수 기준</span>
								</div>
								<div class="all_div">
									<span class="font16px fontBold">4. 채널별대출현황</span>
									<span style="float:right;">(단위 : 백만원,건, %)</span>
	                                <table id="reason_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
		                                <thead class="text-center">
		                                    <tr>
		                                        <th class="pr-1 pl-1">구분</th>
		                                        <th class="pr-2 pl-2">신청</th>
		                                        <th class="pr-1 pl-1">승인</th>
		                                        <th class="pr-1 pl-1">대출금</th>
		                                        <th class="pr-1 pl-1">전월대출금</th>
		                                        <th class="pr-1 pl-1">단가</th>
		                                        <th class="pr-1 pl-1">승인율</th>
		                                    </tr>
		                                </thead>
										<tr class="text-right">
											<td class="text-center">신규</td>
											<td>27</td>
											<td>6</td>
											<td>14.5</td>
											<td>7.0</td>
											<td>2.42</td>
											<td>22.2%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center">추가</td>	
											<td>194</td>
											<td>73</td>
											<td>98.0</td>
											<td>122.3</td>
											<td>1.34</td>
											<td>37.6%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center">재대출</td>
											<td>173</td>
											<td>127</td>
											<td>386.3</td>
											<td>258.2</td>
											<td>3.04</td>
											<td>73.4%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center">당월합계</td>
											<td class="fontBold">394</td>
											<td class="fontBold">206</td>
											<td class="fontBold">498.8</td>
											<td>387.5</td>
											<td class="fontBold">2.42</td>
											<td class="fontBold">52.3%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center">전월합계</td>
											<td>403</td>
											<td>190</td>
											<td>387.5</td>
											<td></td>
											<td>2.0</td>
											<td>47.1%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center">증감</td>
											<td class="decrease">↓ 9</td>
											<td class="increase">↑ 16</td>
											<td class="increase">↑ 111.4</td>
											<td></td>
											<td class="increase">↑ 0.38</td>
											<td class="increase">↑ 5.1%</td>
										</tr>
	                                </table>
									<span class="tableBottomText">* 증감 비교기준 : 전월 동영업일수 기준</span>
								</div>
								<div class="all_div">
									<span class="font16px fontBold">5. 연체현황</span>
									<span style="float:right;">(단위 : 백만원,건)</span>
	                                <table id="reason_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
		                                <thead class="text-center">
		                                    <tr>
		                                        <th class="pr-1 pl-1" colspan="2">구분</th>
		                                        <th class="pr-2 pl-2">무연체</th>
		                                        <th class="pr-1 pl-1">1~10일</th>
		                                        <th class="pr-1 pl-1">11~30일</th>
		                                        <th class="pr-1 pl-1">31일이상</th>
		                                        <th class="pr-1 pl-1">합계</th>
		                                        <th class="pr-1 pl-1">31일이상<br>연체율</th>
		                                    </tr>
		                                </thead>
										<tr class="text-right">
											<td class="text-center">신용</td>
											<td class="text-center">유효수<br>대출잔고</td>
											<td class="fontBold">28,781<br>73,794.4</td>
											<td>188<br>428.3</td>
											<td>190<br>427.8</td>
											<td>2,050<br>4,610.3</td>
											<td class="fontBold">31,209<br>79,235.9</td>
											<td class="fontBold">6.57%<br>5.82%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center">부동산</td>
											<td class="text-center">유효수<br>대출잔고</td>
											<td class="fontBold">28,781<br>73,794.4</td>
											<td>188<br>428.3</td>
											<td>190<br>427.8</td>
											<td>2,050<br>4,610.3</td>
											<td class="fontBold">31,209<br>79,235.9</td>
											<td class="fontBold">6.57%<br>5.82%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center">합계</td>
											<td class="text-center">유효수<br>대출잔고</td>
											<td class="fontBold">28,781<br>73,794.4</td>
											<td>188<br>428.3</td>
											<td>190<br>427.8</td>
											<td>2,050<br>4,610.3</td>
											<td class="fontBold">31,209<br>79,235.9</td>
											<td class="fontBold">6.57%<br>5.82%</td>
										</tr>
	                                </table>
									<span class="tableBottomText">* 기준 : 현재 거래고객	* 연체율 : 31일이상 연체 / 유효건수 또는 잔고</span>
								</div>
								<div class="all_div">
									<span class="font16px fontBold">6. 연체발생율</span>
									<span style="float:right;">(단위 : 백만원,건, %)</span>
	                                <table id="reason_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
		                                <thead class="text-center">
		                                    <tr>
		                                        <th class="pr-1 pl-1">구분</th>
		                                        <th class="pr-2 pl-2">전전월<br>무연체</th>
		                                        <th class="pr-1 pl-1">연체31일<br>이상발생</th>
		                                        <th class="pr-1 pl-1">연 체<br>발생율</th>
		                                        <th class="pr-1 pl-1">최대31일<br>이상가능</th>
		                                        <th class="pr-1 pl-1">최대발생율</th>
		                                    </tr>
		                                </thead>
										<tr class="text-right">
											<td class="text-center">유효건수</td>
											<td>26,769</td>
											<td class="fontBold">142</td>
											<td class="fontBold">0.53%</td>
											<td class="fontBold">269</td>
											<td class="fontBold">1.00%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center">대출잔고</td>
											<td>26,769</td>
											<td class="fontBold">142</td>
											<td class="fontBold">0.53%</td>
											<td class="fontBold">269</td>
											<td class="fontBold">1.00%</td>
										</tr>
	                                </table>
									<span class="tableBottomText">* 전전월말 무연체채권 중 현재 연체가 31일 이상인 채권의 비율</span>
								</div>
								<div class="all_div">
									<span class="font16px fontBold">7. 계약서징구율</span>
									<span style="float:right;">(단위 : 건, %)</span>
	                                <table id="reason_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
		                                <thead class="text-center">
		                                    <tr>
		                                        <th class="pr-1 pl-1">월별</th>
		                                        <th class="pr-2 pl-2">서류미비</th>
		                                        <th class="pr-1 pl-1">서류완료</th>
		                                        <th class="pr-1 pl-1">총합계</th>
		                                        <th class="pr-1 pl-1">징구율</th>
		                                    </tr>
		                                </thead>
										<tr class="text-right">
											<td class="text-center">12월</td>
											<td>61</td>
											<td>2,275</td>
											<td>2,353</td>
											<td class="fontBold">96.69%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center">01월</td>
											<td>61</td>
											<td>2,275</td>
											<td>2,353</td>
											<td class="fontBold">96.69%</td>
										</tr>
										<tr class="text-right">
											<td class="text-center">02월</td>
											<td>61</td>
											<td>2,275</td>
											<td>2,353</td>
											<td class="fontBold">96.69%</td>
										</tr>
	                                </table>
									<span class="tableBottomText">* 활동채권의 징구율</span>
								</div>
                            </div>
                        </div>

                        <div class="card-body p-0" id="footTable" style="max-height:450px; overflow-y: auto;">
                        </div>

                        <!-- 일괄처리 & 페이지 버튼 -->
                        <div class="card-footer mb-0 p-3">
                        </div>

                    </div>
                </div>
                </form>
            </div>
        </div>

    </section>
</div>




@endsection
@section('javascript')
{{-- <script src="/plugins/datatables-buttons/js/dataTables.buttons.min.js"></script> --}}



<script src="/plugins/datatables/jquery.dataTables.js"></script>
<script src="/plugins/datatables-bs4/js/dataTables.bootstrap4.js"></script> 
<script src="/plugins/datatables-rowgroup/js/dataTables.rowGroup.js"></script> 
<script src="/plugins/datatables-fixedcolumns/js/dataTables.fixedColumns.js"></script> 
<script src="/plugins/datatables-responsive/js/dataTables.responsive.js"></script> 
<script src="/plugins/datatables-responsive/js/responsive.bootstrap4.js"></script> 

<script src="/plugins/datatables-buttons/js/dataTables.buttons.js"></script> 
<script src="/plugins/datatables-buttons/js/buttons.bootstrap4.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.html5.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.print.js"></script>
<script src="/plugins/datatables-buttons/js/buttons.colVis.js"></script>

<link rel="stylesheet" href="/plugins/datatables-bs4/css/dataTables.bootstrap4.css">
<link rel="stylesheet" href="/plugins/datatables-rowgroup/css/rowGroup.bootstrap4.css">
<link rel="stylesheet" href="/plugins/datatables-fixedcolumns/css/fixedColumns.bootstrap4.css">
<link rel="stylesheet" href="/plugins/datatables-responsive/css/responsive.bootstrap4.css">
<link rel="stylesheet" href="/plugins/datatables-buttons/css/buttons.bootstrap4.css">


<script src="/plugins/jszip/jszip.min.js"></script>
<script src="/plugins/pdfmake/pdfmake.min.js"></script>
<script src="/plugins/pdfmake/vfs_fonts.js"></script>
<script>
	function sendit(){
		form.action = "/arrows/summary";
		form.submit();
	}
	
	$(document).ready(function(){
        getSummary();
    });
	
	function getSummary()
    {
        var table = $('#loan_detail_table').DataTable( {
            destroy: true,
            scrollY:        "600px",
            scrollX:        true,
            scrollCollapse: true,
            lengthChange:   false,          // 표시 건수기능 숨기기
            searching:      false,          // 검색 기능 숨기기
            ordering:       false,          // 정렬 기능 숨기기
            info:           false,          // 정보 표시 숨기기
            paging:         false,          // 페이징 기능 숨기기
            fixedColumns:   {
                leftColumns: 1,
            },
        } );
	}

</script>


<style>
.DTFC_LeftBodyLiner { 
    overflow-x: hidden; 
}
table.dataTable.no-footer {
    border-bottom: 0px;
}
.font16px {
	font-size: 16px;
}
.fontBold {
	font-weight: bold;
}
.tableBottomText {
	font-size: 11px;
}
.increase {
	color: blue;
}
.decrease {
	color: red;
}
#data-area table {
	margin-bottom: 0;
}
.all_div {
	width:70%; 
	margin: 2% 0 0 2%;
}
@media all and (max-width:720px){
    .all_div {
		width:90%;
	}
}

</style>




@endsection