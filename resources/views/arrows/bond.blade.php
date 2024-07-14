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
							<div style="width:85%; margin: 2% 0 0 2%;">
                                <span class="font16px fontBold">전 상품 승인금액별 구성비</span>
                                <table id="reason_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
		                            <thead class="text-center">
		                                <tr>
		                                    <th class="pr-1 pl-1" rowspan="3">구분</th>
		                                    <th class="pr-1 pl-1" colspan=6>전월</th>
		                                    <th class="pr-1 pl-1" colspan=6>당월</th>
		                                </tr>
                                        <tr>
                                            <th class="pr-1 pl-1" colspan="3">신용대출</th>
                                            <th class="pr-1 pl-1" colspan="3">담보대출</th>
                                            <th class="pr-1 pl-1" colspan="3">신용대출</th>
                                            <th class="pr-1 pl-1" colspan="3">담보대출</th>
                                        </tr>
                                        <tr>
                                            <th class="pr-1 pl-1">대출건수</th>
                                            <th class="pr-1 pl-1">대출금액</th>
                                            <th class="pr-1 pl-1">구성비</th>
                                            <th class="pr-1 pl-1">대출건수</th>
                                            <th class="pr-1 pl-1">대출금액</th>
                                            <th class="pr-1 pl-1">구성비</th>
                                            <th class="pr-1 pl-1">대출건수</th>
                                            <th class="pr-1 pl-1">대출금액</th>
                                            <th class="pr-1 pl-1">구성비</th>
                                            <th class="pr-1 pl-1">대출건수</th>
                                            <th class="pr-1 pl-1">대출금액</th>
                                            <th class="pr-1 pl-1">구성비</th>
                                        </tr>
		                            </thead>
                                    <tr class="text-right">
										<td class="text-center">5,000,000</td>
										<td>671</td>
										<td></td>
										<td>19.00%</td>
										<td>784</td>
										<td></td>
										<td>20.50%</td>
                                        <td>671</td>
										<td></td>
										<td>19.00%</td>
                                        <td>784</td>
										<td></td>
										<td>20.50%</td>
									</tr>
                                    <tr class="text-right">
										<td class="text-center">10,000,000</td>
										<td>0</td>
										<td></td>
										<td>0.00%</td>
										<td>0</td>
										<td></td>
										<td>0.00%</td>
                                        <td>0</td>
										<td></td>
										<td>0.00%</td>
                                        <td>0</td>
										<td></td>
										<td>0.00%</td>
									</tr>
                                    <tr class="text-right">
										<td class="text-center">15,000,000</td>
										<td>19</td>
										<td></td>
										<td>0.50%</td>
										<td>26</td>
										<td></td>
										<td>0.70%</td>
                                        <td>19</td>
										<td></td>
										<td>0.50%</td>
                                        <td>26</td>
										<td></td>
										<td>0.70%</td>
									</tr>
                                    <tr class="text-right">
										<td class="text-center">20,000,000</td>
										<td>22</td>
										<td></td>
										<td>0.60%</td>
										<td>45</td>
										<td></td>
										<td>1.20%</td>
                                        <td>22</td>
										<td></td>
										<td>0.60%</td>
                                        <td>45</td>
										<td></td>
										<td>1.20%</td>
									</tr>
                                    <tr class="text-right">
										<td class="text-center">30,000,000</td>
										<td>7</td>
										<td></td>
										<td>0.20%</td>
										<td>8</td>
										<td></td>
										<td>0.20%</td>
                                        <td>7</td>
										<td></td>
										<td>0.20%</td>
                                        <td>8</td>
										<td></td>
										<td>0.20%</td>
									</tr>
                                    <tr class="text-right">
										<td class="text-center">40,000,000</td>
										<td>1</td>
										<td></td>
										<td>0.00%</td>
										<td>1</td>
										<td></td>
										<td>0.00%</td>
                                        <td>1</td>
										<td></td>
										<td>0.00%</td>
                                        <td>1</td>
										<td></td>
										<td>0.00%</td>
									</tr>
                                    <tr class="text-right">
										<td class="text-center">50,000,000</td>
										<td>237</td>
										<td></td>
										<td>6.70%</td>
										<td>295</td>
										<td></td>
										<td>7.70%</td>
                                        <td>237</td>
										<td></td>
										<td>6.70%</td>
                                        <td>295</td>
										<td></td>
										<td>7.70%</td>
									</tr>
                                    <tr class="text-right">
										<td class="text-center">5,000~1억</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
                                        <td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
									</tr>
                                    <tr class="text-right">
										<td class="text-center">1억~2억</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
                                        <td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
									</tr>
                                    <tr class="text-right">
										<td class="text-center">2억~3억</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
                                        <td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
									</tr>
                                    <tr class="text-right">
										<td class="text-center">3억이상</td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
                                        <td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
										<td></td>
									</tr>
                                    <tr class="text-right" style="background: #f4f6f9;">
										<td class="text-center pr-1 pl-1">전체</td>
										<td>3,539</td>
										<td></td>
										<td>100.00%</td>
										<td>3,830</td>
										<td></td>
										<td>100.00%</td>
                                        <td>3,539</td>
										<td></td>
										<td>100.00%</td>
										<td>3,830</td>
										<td></td>
										<td>100.00%</td>
									</tr>
                                </table>
                            </div>
                            
                            <div style="width:55%; margin: 2% 0 0 2%;">
								<span class="font16px fontBold">담보대출 LTV별 구성비 (원금)</span>
	                            <table id="reason_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">       
                                    <thead class="text-center">
		                                <tr>
		                                    <th class="pr-1 pl-1" rowspan="2">구분</th>
		                                    <th class="pr-1 pl-1" colspan=3>전월</th>
		                                    <th class="pr-1 pl-1" colspan=3>당월</th>
		                                </tr>
                                        <tr>
                                            <th class="pr-1 pl-1">대출건수</th>
                                            <th class="pr-1 pl-1">대출금액</th>
                                            <th class="pr-1 pl-1">구성비</th>
                                            <th class="pr-1 pl-1">대출건수</th>
                                            <th class="pr-1 pl-1">대출금액</th>
                                            <th class="pr-1 pl-1">구성비</th>
                                        </tr>
                                    </thead>
                                    <tr class="text-right">
                                        <td class="text-center">70% 이하</td>
                                        <td>784</td>
                                        <td></td>
                                        <td>20.50%</td>
                                        <td>784</td>
                                        <td></td>
                                        <td>20.50%</td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">70%~75%</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">75%~80%</td>
                                        <td>0</td>
                                        <td></td>
                                        <td>0.00%</td>
                                        <td>0</td>
                                        <td></td>
                                        <td>0.00%</td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">80%~85%</td>
                                        <td>26</td>
                                        <td></td>
                                        <td>0.70%</td>
                                        <td>26</td>
                                        <td></td>
                                        <td>0.70%</td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">85%~90%</td>
                                        <td>45</td>
                                        <td></td>
                                        <td>1.20%</td>
                                        <td>45</td>
                                        <td></td>
                                        <td>1.20%</td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">90%~95%</td>
                                        <td>8</td>
                                        <td></td>
                                        <td>0.20%</td>
                                        <td>8</td>
                                        <td></td>
                                        <td>0.20%</td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">95%~100%</td>
                                        <td>1</td>
                                        <td></td>
                                        <td>0.00%</td>
                                        <td>1</td>
                                        <td></td>
                                        <td>0.00%</td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">100% 이상</td>
                                        <td>295</td>
                                        <td></td>
                                        <td>7.70%</td>
                                        <td>295</td>
                                        <td></td>
                                        <td>7.70%</td>
								    </tr>
                                    <tr class="text-right" style="background: #f4f6f9;">
                                        <td class="text-center pr-1 pl-1">전체</td>
                                        <td>3,830</td>
                                        <td></td>
                                        <td>100.00%</td>
                                        <td>3,830</td>
                                        <td></td>
                                        <td>100.00%</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div style="width:55%; margin: 2% 0 0 2%;">
								<span class="font16px fontBold">담보대출 LTV별 구성비 (원금)</span>
	                            <table id="reason_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">       
                                    <thead class="text-center">
		                                <tr>
		                                    <th class="pr-1 pl-1" rowspan="2">구분</th>
		                                    <th class="pr-1 pl-1" colspan=3>전월</th>
		                                    <th class="pr-1 pl-1" colspan=3>당월</th>
		                                </tr>
                                        <tr>
                                            <th class="pr-1 pl-1">대출건수</th>
                                            <th class="pr-1 pl-1">대출금액</th>
                                            <th class="pr-1 pl-1">구성비</th>
                                            <th class="pr-1 pl-1">대출건수</th>
                                            <th class="pr-1 pl-1">대출금액</th>
                                            <th class="pr-1 pl-1">구성비</th>
                                        </tr>
                                    </thead>
                                    <tr class="text-right">
                                        <td class="text-center">70% 이하</td>
                                        <td>784</td>
                                        <td></td>
                                        <td>20.50%</td>
                                        <td>784</td>
                                        <td></td>
                                        <td>20.50%</td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">70%~75%</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">75%~80%</td>
                                        <td>0</td>
                                        <td></td>
                                        <td>0.00%</td>
                                        <td>0</td>
                                        <td></td>
                                        <td>0.00%</td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">80%~85%</td>
                                        <td>26</td>
                                        <td></td>
                                        <td>0.70%</td>
                                        <td>26</td>
                                        <td></td>
                                        <td>0.70%</td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">85%~90%</td>
                                        <td>45</td>
                                        <td></td>
                                        <td>1.20%</td>
                                        <td>45</td>
                                        <td></td>
                                        <td>1.20%</td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">90%~95%</td>
                                        <td>8</td>
                                        <td></td>
                                        <td>0.20%</td>
                                        <td>8</td>
                                        <td></td>
                                        <td>0.20%</td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">95%~100%</td>
                                        <td>1</td>
                                        <td></td>
                                        <td>0.00%</td>
                                        <td>1</td>
                                        <td></td>
                                        <td>0.00%</td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">100% 이상</td>
                                        <td>295</td>
                                        <td></td>
                                        <td>7.70%</td>
                                        <td>295</td>
                                        <td></td>
                                        <td>7.70%</td>
								    </tr>
                                    <tr class="text-right" style="background: #f4f6f9;">
                                        <td class="text-center pr-1 pl-1">전체</td>
                                        <td>3,830</td>
                                        <td></td>
                                        <td>100.00%</td>
                                        <td>3,830</td>
                                        <td></td>
                                        <td>100.00%</td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div style="width:55%; margin: 2% 0 10px 2%;">
								<span class="font16px fontBold">지역별 구성비 (담보대출)</span>
	                            <table id="reason_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">       
                                    <thead class="text-center">
		                                <tr>
		                                    <th class="pr-1 pl-1"rowspan=2>구분</th>
		                                    <th class="pr-1 pl-1"colspan=3>전월</th>
		                                    <th class="pr-1 pl-1"colspan=3>당월</th>
		                                </tr>
                                        <tr>
                                            <th class="pr-1 pl-1">대출건수</th>
                                            <th class="pr-1 pl-1">대출금액</th>
                                            <th class="pr-1 pl-1">구성비</th>
                                            <th class="pr-1 pl-1">대출건수</th>
                                            <th class="pr-1 pl-1">대출금액</th>
                                            <th class="pr-1 pl-1">구성비</th>
                                        </tr>
                                    </thead>
                                    <tr class="text-right">
                                        <td class="text-center">서울</td>
                                        <td>784</td>
                                        <td></td>
                                        <td>20.50%</td>
                                        <td>784</td>
                                        <td></td>
                                        <td>20.50%</td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">경기 1군</td>
                                        <td>10</td>
                                        <td></td>
                                        <td>33.33%</td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">경기 2군</td>
                                        <td>20</td>
                                        <td></td>
                                        <td>66.67%</td>
                                        <td>0</td>
                                        <td></td>
                                        <td>0.00%</td>
								    </tr>
                                    <tr class="text-right" style="background:#FFE5DC;">
                                        <td class="text-center">경기 소계</td>
                                        <td>30</td>
                                        <td></td>
                                        <td>0.70%</td>
                                        <td>26</td>
                                        <td></td>
                                        <td>0.70%</td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">인천</td>
                                        <td>45</td>
                                        <td></td>
                                        <td>1.20%</td>
                                        <td>45</td>
                                        <td></td>
                                        <td>1.20%</td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">광역시</td>
                                        <td>8</td>
                                        <td></td>
                                        <td>0.20%</td>
                                        <td>8</td>
                                        <td></td>
                                        <td>0.20%</td>
								    </tr>
                                    <tr class="text-right">
                                        <td class="text-center">지방</td>
                                        <td>1</td>
                                        <td></td>
                                        <td>0.00%</td>
                                        <td>1</td>
                                        <td></td>
                                        <td>0.00%</td>
								    </tr>
                                    <tr class="text-right" style="background: #f4f6f9;">
                                        <td class="text-center pr-1 pl-1">전체</td>
                                        <td>3,830</td>
                                        <td></td>
                                        <td>100.00%</td>
                                        <td>3,830</td>
                                        <td></td>
                                        <td>100.00%</td>
                                    </tr>
                                </table>
                            </div>
                    </div>
                </form>
                </div>
                <div class="card-body p-0" id="footTable" style="max-height:450px; overflow-y: auto;">
                        </div>

                        <!-- 일괄처리 & 페이지 버튼 -->
                        <div class="card-footer mb-0 p-3">
                        </div>
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
		form.action = "/arrows/mis_bond";
		form.submit();
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
</style>

@endsection