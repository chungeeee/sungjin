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
                    <span class="font16px fontBold">⊙ 월 Vintage 분석자료</span>
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
                        <div style="width:95%; margin: 2% 0 0 2%;">
                                <span style="background: #f4f6f9;" class="font16px fontBold">재대출 채권 SUM</span>
								<span style="float:right;">(단위 : 백만원)</span>
                                <table id="bond_sum_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
		                            <thead class="text-center">
		                                <tr>
		                                    <th class="pr-2 pl-2">재대출 : 7,903.4</th>
		                                    <th class="pr-1 pl-1">08.05월</th>
		                                    <th class="pr-1 pl-1">08.06월</th>
                                            <th class="pr-1 pl-1">08.07월</th>
                                            <th class="pr-1 pl-1">08.08월</th>
                                            <th class="pr-1 pl-1">08.09월</th>
                                            <th class="pr-1 pl-1">08.10월</th>
                                            <th class="pr-1 pl-1">08.11월</th>
                                            <th class="pr-1 pl-1">08.12월</th>
                                            <th class="pr-1 pl-1">09.01월</th>
                                            <th class="pr-1 pl-1">09.02월</th>
                                            <th class="pr-1 pl-1">09.03월</th>
                                            <th class="pr-1 pl-1">09.04월</th>
                                            <th class="pr-1 pl-1">09.05월</th>
                                            <th class="pr-1 pl-1">09.06월</th>
                                            <th class="pr-1 pl-1">09.07월</th>
                                            <th class="pr-1 pl-1">09.08월</th>
                                            <th class="pr-1 pl-1">09.09월</th>
                                            <th class="pr-1 pl-1">09.10월</th>
                                            <th class="pr-1 pl-1">09.11월</th>
                                            <th class="pr-1 pl-1">09.12월</th>
                                            <th class="pr-1 pl-1">10.01월</th>
                                            <th class="pr-1 pl-1">10.02월</th>
                                            <th class="pr-1 pl-1">10.03월</th>
                                            <th class="pr-1 pl-1">10.04월</th>
                                            <th class="pr-1 pl-1">10.05월</th>
                                            <th class="pr-1 pl-1">10.06월</th>
                                            <th class="pr-1 pl-1">10.07월</th>
                                            <th class="pr-1 pl-1">10.08월</th>
                                            <th class="pr-1 pl-1">평균전이율</th>
		                                </tr>
		                            </thead>
                                    <tr class="text-right">
										<td style="background:#f4f6f9;">무연체</td>
										<td></td>
										<td>0.0</td>
										<td>13.0</td>
										<td>28.8</td>
										<td>65.6</td>
										<td>106.7</td>
                                        <td>149.2</td>
										<td>199.6</td>
										<td>264.2</td>
                                        <td>385.7</td>
										<td>587.5</td>
										<td>801.2</td>
                                        <td>928.2</td>
                                        <td>1,052.7</td>
                                        <td>1,381.5</td>
                                        <td>2,071.7</td>
                                        <td>2,711.4</td>
                                        <td>2,365.1</td>
                                        <td>3,528.6</td>
                                        <td>3,644.3</td>
                                        <td>3,751.1</td>
                                        <td>3,885.5</td>
                                        <td>3,911.3</td>
                                        <td>4,154.5</td>
                                        <td>4,947.6</td>
                                        <td>5,364.4</td>
                                        <td>5,915.9</td>
                                        <td>6,118.4</td>
                                        <td>1.18%</td>
									</tr>
                                    <tr class="text-right" style="background:#f4f6f9;">
										<td>1~10일</td>
										<td></td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
										<td>2.0</td>
                                        <td>0.0</td>
										<td>0.0</td>
										<td>9.5</td>
                                        <td>3.0</td>
										<td>2.0</td>
										<td>2.0</td>
                                        <td>5.4</td>
                                        <td>5.0</td>
                                        <td>7.0</td>
                                        <td>2.0</td>
                                        <td>9.6</td>
                                        <td>0.0</td>
                                        <td>23.7</td>
                                        <td>11.8</td>
                                        <td>18.7</td>
                                        <td>41.4</td>
                                        <td>20.8</td>
                                        <td>26.7</td>
                                        <td>44.1</td>
                                        <td>29.9</td>
                                        <td>28.4</td>
                                        <td>50.9</td>
                                        <td>77.85%</td>
									</tr>
                                    <tr class="text-right" style="background:#f4f6f9;">
										<td>11~30일</td>
										<td></td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
                                        <td>0.0</td>
										<td>2.0</td>
										<td>0.0</td>
                                        <td>4.5</td>
										<td>5.0</td>
										<td>0.0</td>
                                        <td>4.0</td>
                                        <td>2.0</td>
                                        <td>14.8</td>
                                        <td>2.0</td>
                                        <td>12.8</td>
                                        <td>3.0</td>
                                        <td>3.4</td>
                                        <td>15.7</td>
                                        <td>17.3</td>
                                        <td>16.8</td>
                                        <td>18.5</td>
                                        <td>51.4</td>
                                        <td>31.8</td>
                                        <td>22.4</td>
                                        <td>12.6</td>
                                        <td>34.1</td>
                                        <td>97.43%</td>
									</tr>
                                    <tr class="text-right">
										<td style="background:#f4f6f9;">31~60일</td>
										<td></td>
										<td></td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
                                        <td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
                                        <td>1.5</td>
										<td>6.5</td>
										<td>6.9</td>
                                        <td>2.0</td>
                                        <td>9.3</td>
                                        <td>7.0</td>
                                        <td>15.8</td>
                                        <td>3.9</td>
                                        <td>20.5</td>
                                        <td>3.0</td>
                                        <td>25.1</td>
                                        <td>19.6</td>
                                        <td>34.1</td>
                                        <td>39.4</td>
                                        <td>32.6</td>
                                        <td>63.4</td>
                                        <td>53.5</td>
                                        <td>35.2</td>
                                        <td>35.2</td>
                                        <td>97.43%</td>
									</tr>
                                    <tr class="text-right">
										<td style="background:#f4f6f9;">61~90일</td>
										<td></td>
										<td></td>
										<td></td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
                                        <td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
                                        <td>1.5</td>
										<td>6.5</td>
										<td>6.9</td>
                                        <td>2.0</td>
                                        <td>9.3</td>
                                        <td>7.0</td>
                                        <td>15.8</td>
                                        <td>3.9</td>
                                        <td>20.5</td>
                                        <td>3.0</td>
                                        <td>25.1</td>
                                        <td>19.6</td>
                                        <td>34.1</td>
                                        <td>39.4</td>
                                        <td>32.6</td>
                                        <td>63.4</td>
                                        <td>53.5</td>
                                        <td>35.2</td>
                                        <td>35.2</td>
                                        <td>97.43%</td>
									</tr>
                                    <tr class="text-right">
										<td style="background:#f4f6f9;">91~120일</td>
										<td></td>
										<td></td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
                                        <td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
                                        <td>1.5</td>
										<td>6.5</td>
										<td>6.9</td>
                                        <td>2.0</td>
                                        <td>9.3</td>
                                        <td>7.0</td>
                                        <td>15.8</td>
                                        <td>3.9</td>
                                        <td>20.5</td>
                                        <td>3.0</td>
                                        <td>25.1</td>
                                        <td>19.6</td>
                                        <td>34.1</td>
                                        <td>39.4</td>
                                        <td>32.6</td>
                                        <td>63.4</td>
                                        <td>53.5</td>
                                        <td>35.2</td>
                                        <td>35.2</td>
                                        <td>97.43%</td>
									</tr>
                                    <tr class="text-right">
										<td style="background:#f4f6f9;">121~150일</td>
										<td></td>
										<td></td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
                                        <td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
                                        <td>1.5</td>
										<td>6.5</td>
										<td>6.9</td>
                                        <td>2.0</td>
                                        <td>9.3</td>
                                        <td>7.0</td>
                                        <td>15.8</td>
                                        <td>3.9</td>
                                        <td>20.5</td>
                                        <td>3.0</td>
                                        <td>25.1</td>
                                        <td>19.6</td>
                                        <td>34.1</td>
                                        <td>39.4</td>
                                        <td>32.6</td>
                                        <td>63.4</td>
                                        <td>53.5</td>
                                        <td>35.2</td>
                                        <td>35.2</td>
                                        <td>97.43%</td>
									</tr>
                                    <tr class="text-right">
										<td style="background:#f4f6f9;">151~180일</td>
										<td></td>
										<td></td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
                                        <td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
                                        <td>1.5</td>
										<td>6.5</td>
										<td>6.9</td>
                                        <td>2.0</td>
                                        <td>9.3</td>
                                        <td>7.0</td>
                                        <td>15.8</td>
                                        <td>3.9</td>
                                        <td>20.5</td>
                                        <td>3.0</td>
                                        <td>25.1</td>
                                        <td>19.6</td>
                                        <td>34.1</td>
                                        <td>39.4</td>
                                        <td>32.6</td>
                                        <td>63.4</td>
                                        <td>53.5</td>
                                        <td>35.2</td>
                                        <td>35.2</td>
                                        <td>97.43%</td>
									</tr>
                                    <tr class="text-right">
										<td style="background:#f4f6f9;">181일 이상</td>
										<td></td>
										<td></td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
                                        <td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
                                        <td>1.5</td>
										<td>6.5</td>
										<td>6.9</td>
                                        <td>2.0</td>
                                        <td>9.3</td>
                                        <td>7.0</td>
                                        <td>15.8</td>
                                        <td>3.9</td>
                                        <td>20.5</td>
                                        <td>3.0</td>
                                        <td>25.1</td>
                                        <td>19.6</td>
                                        <td>34.1</td>
                                        <td>39.4</td>
                                        <td>32.6</td>
                                        <td>63.4</td>
                                        <td>53.5</td>
                                        <td>35.2</td>
                                        <td>35.2</td>
                                        <td>97.43%</td>
									</tr>
                                    <tr class="text-right">
										<td class="text-center" style="background:#f4f6f9;">계</td>
										<td></td>
										<td>0.0</td>
										<td>13.0</td>
										<td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
                                        <td>0.0</td>
										<td>0.0</td>
										<td>0.0</td>
                                        <td>1.5</td>
										<td>6.5</td>
										<td>6.9</td>
                                        <td>2.0</td>
                                        <td>9.3</td>
                                        <td>7.0</td>
                                        <td>15.8</td>
                                        <td>3.9</td>
                                        <td>20.5</td>
                                        <td>3.0</td>
                                        <td>25.1</td>
                                        <td>19.6</td>
                                        <td>34.1</td>
                                        <td>39.4</td>
                                        <td>32.6</td>
                                        <td>63.4</td>
                                        <td>53.5</td>
                                        <td>35.2</td>
                                        <td>35.2</td>
                                        <td>97.43%</td>
									</tr>
                                </table>
                            </div>
                        </div>
                        
                        <!-- 월평균 원금상환 -->
                        <div style="width:15%; margin: 2% 0 0 2%;">
                            <table id="mon_average_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
		                        <thead class="text-center">
		                            <tr>
		                                <th class="pr-2 pl-2">월평균<br>원금상환</th>
                                        <th class="pr-2 pl-2">Duration</th>
	                                </tr>
	                            </thead>
                                    <tr class="text-center">
                                        <td>0.88%</td>
                                        <td>9.4년</td>
                                    </tr>
                                    <tr class="text-center">
                                        <td>1.41%</td>
                                        <td>########</td>
                                    </tr>
                            </table>
                        </div>
                        
                        <div style="width:95%; margin: 2% 0 0 2%;">
                                <table id="repayment_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
		                            <thead class="text-center">
		                                <tr>
		                                    <th class="pr-2 pl-2 text-center">원금상환액</th>
		                                    <th class="pr-1 pl-1"></th>
		                                    <th class="pr-1 pl-1">10.0</th>
                                            <th class="pr-1 pl-1">0.0</th>
                                            <th class="pr-1 pl-1">0.2</th>
                                            <th class="pr-1 pl-1">4.3</th>
                                            <th class="pr-1 pl-1">14.4</th>
                                            <th class="pr-1 pl-1">10.6</th>
                                            <th class="pr-1 pl-1">25.5</th>
                                            <th class="pr-1 pl-1">19.5</th>
                                            <th class="pr-1 pl-1">35.0</th>
                                            <th class="pr-1 pl-1">57.8</th>
                                            <th class="pr-1 pl-1">74.2</th>
                                            <th class="pr-1 pl-1">84.1</th>
                                            <th class="pr-1 pl-1">98.5</th>
                                            <th class="pr-1 pl-1">103.4</th>
                                            <th class="pr-1 pl-1">193.5</th>
                                            <th class="pr-1 pl-1">249.1</th>
                                            <th class="pr-1 pl-1">207.5</th>
                                            <th class="pr-1 pl-1">285.6</th>
                                            <th class="pr-1 pl-1">386.9</th>
                                            <th class="pr-1 pl-1">-130.1</th>
                                            <th class="pr-1 pl-1">428.1</th>
                                            <th class="pr-1 pl-1">450.9</th>
                                            <th class="pr-1 pl-1">371.2</th>
                                            <th class="pr-1 pl-1">-843.2</th>
                                            <th class="pr-1 pl-1">-443.5</th>
                                            <th class="pr-1 pl-1">-566.3</th>
                                            <th class="pr-1 pl-1">-268.9</th>
		                                </tr>
		                            </thead>
                                    <tr class="text-right">
										<td class="text-center" style="background:#f4f6f9;">원금상환율(1)</td>
										<td></td>
										<td>200.00%</td>
										<td>0.00%</td>
										<td>0.49%</td>
										<td>7.17%</td>
										<td>13.20%</td>
                                        <td>6.48%</td>
										<td>11.23%</td>
										<td>6.23%</td>
                                        <td>9.79%</td>
										<td>16.13%</td>
										<td>14.76%</td>
                                        <td>11.09%</td>
                                        <td>11.34%</td>
                                        <td>9.44%</td>
                                        <td>10.98%</td>
                                        <td>9.38%</td>
                                        <td>6.67%</td>
                                        <td>9.19%</td>
                                        <td>12.44%</td>
                                        <td>-4.18%</td>
                                        <td>6.64%</td>
                                        <td>6.45%</td>
                                        <td>4.90%</td>
                                        <td>-10.67%</td>
                                        <td>-5.61%</td>
                                        <td>-7.17%</td>
                                        <td>-3.40%</td>
									</tr>
                                    <tr class="text-right">
										<td class="text-center" style="background:#f4f6f9;">원금상환율(2)</td>
										<td></td>
										<td>-</td>
										<td>-</td>
										<td>1.16%</td>
										<td>14.79%</td>
										<td>21.88%</td>
                                        <td>9.72%</td>
										<td>17.13%</td>
										<td>9.65%</td>
                                        <td>12.80%</td>
										<td>14.63%</td>
										<td>12.32%</td>
                                        <td>10.28%</td>
                                        <td>10.32%</td>
                                        <td>9.52%</td>
                                        <td>13.47%</td>
                                        <td>11.73%</td>
                                        <td>7.46%</td>
                                        <td>11.75%</td>
                                        <td>10.68%</td>
                                        <td>-3.46%</td>
                                        <td>11.1%</td>
                                        <td>11.08%</td>
                                        <td>9.03%</td>
                                        <td>-19.15%</td>
                                        <td>-8.46%</td>
                                        <td>-9.95%</td>
                                        <td>-4.30%</td>
									</tr>
                                </table>
                            </div>
                        </div>
                        
                        <div style="width:95%; margin: 2% 0 0 2%;">
                                <table id="delay_rate_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
                                    <thead class="text-center">
                                        <tr>
                                            <th class="pr-2 pl-2 text-center">연체율(1)</th>
                                            <th class="pr-1 pl-1"></th>
                                            <th class="pr-1 pl-1">-</th>
                                            <th class="pr-1 pl-1">0.00%</th>
                                            <th class="pr-1 pl-1">0.00%</th>
                                            <th class="pr-1 pl-1">0.00%</th>
                                            <th class="pr-1 pl-1">0.00%</th>
                                            <th class="pr-1 pl-1">0.00%</th>
                                            <th class="pr-1 pl-1">0.00%</th>
                                            <th class="pr-1 pl-1">0.00%</th>
                                            <th class="pr-1 pl-1">0.38%</th>
                                            <th class="pr-1 pl-1">1.32%</th>
                                            <th class="pr-1 pl-1">1.82%</th>
                                            <th class="pr-1 pl-1">1.77%</th>
                                            <th class="pr-1 pl-1">2.42%</th>
                                            <th class="pr-1 pl-1">2.31%</th>
                                            <th class="pr-1 pl-1">2.22%</th>
                                            <th class="pr-1 pl-1">1.69%</th>
                                            <th class="pr-1 pl-1">2.57%</th>
                                            <th class="pr-1 pl-1">1.81%</th>
                                            <th class="pr-1 pl-1">2.28%</th>
                                            <th class="pr-1 pl-1">2.58%</th>
                                            <th class="pr-1 pl-1">3.10%</th>
                                            <th class="pr-1 pl-1">3.89%</th>
                                            <th class="pr-1 pl-1">3.86%</th>
                                            <th class="pr-1 pl-1">4.23%</th>
                                            <th class="pr-1 pl-1">4.79%</th>
                                            <th class="pr-1 pl-1">4.77%</th>
                                            <th class="pr-1 pl-1">4.92%</th>
                                            <th class="pr-1 pl-1">월평균<br>연체발생률</th>
                                        </tr>
                                    </thead>    
                                    <tr class="text-right">
										<td class="text-center" style="background:#f4f6f9;">연체율(2)</td>
										<td></td>
										<td>-</td>
										<td>0.00%</td>
										<td>0.00%</td>
										<td>0.00%</td>
										<td>1.84%</td>
                                        <td>0.00%</td>
										<td>0.99%</td>
										<td>3.47%</td>
                                        <td>2.27%</td>
										<td>2.48%</td>
										<td>2.07%</td>
                                        <td>2.75%</td>
                                        <td>3.06%</td>
                                        <td>3.83%</td>
                                        <td>2.40%</td>
                                        <td>2.50%</td>
                                        <td>2.70%</td>
                                        <td>9.19%</td>
                                        <td>12.44%</td>
                                        <td>-4.18%</td>
                                        <td>6.64%</td>
                                        <td>6.45%</td>
                                        <td>4.90%</td>
                                        <td>-10.67%</td>
                                        <td>-5.61%</td>
                                        <td>-7.17%</td>
                                        <td>-3.40%</td>
                                        <td></td>
									</tr>
                                    <tr class="text-right">
										<td class="text-center" style="background:#f4f6f9;">연체발생율</td>
										<td></td>
										<td>200.00%</td>
										<td>0.00%</td>
										<td>0.49%</td>
										<td>7.17%</td>
										<td>13.20%</td>
                                        <td>6.48%</td>
										<td>11.23%</td>
										<td>6.23%</td>
                                        <td>9.79%</td>
										<td>16.13%</td>
										<td>14.76%</td>
                                        <td>11.09%</td>
                                        <td>11.34%</td>
                                        <td>9.44%</td>
                                        <td>10.98%</td>
                                        <td>9.38%</td>
                                        <td>6.67%</td>
                                        <td>9.19%</td>
                                        <td>12.44%</td>
                                        <td>-4.18%</td>
                                        <td>6.64%</td>
                                        <td>6.45%</td>
                                        <td>4.90%</td>
                                        <td>-10.67%</td>
                                        <td>-5.61%</td>
                                        <td>-7.17%</td>
                                        <td>-3.40%</td>
                                        <td>0.79%</td>
									</tr>
                                </table>
                            </div>
                        </div>
                        
                        <div style="width:95%; margin: 2% 0 20px 2%;">
                                <table id="mon_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
		                            <thead class="text-center">
		                                <tr>
		                                    <th class="pr-2 pl-2 text-center">월</th>
		                                    <th class="pr-1 pl-1">5월</th>
		                                    <th class="pr-1 pl-1">6월</th>
                                            <th class="pr-1 pl-1">7월</th>
                                            <th class="pr-1 pl-1">8월</th>
                                            <th class="pr-1 pl-1">9월</th>
                                            <th class="pr-1 pl-1">10월</th>
                                            <th class="pr-1 pl-1">11월</th>
                                            <th class="pr-1 pl-1">12월</th>
                                            <th class="pr-1 pl-1">09.01월</th>
                                            <th class="pr-1 pl-1">09.02월</th>
                                            <th class="pr-1 pl-1">09.03월</th>
                                            <th class="pr-1 pl-1">09.04월</th>
                                            <th class="pr-1 pl-1">09.05월</th>
                                            <th class="pr-1 pl-1">09.06월</th>
                                            <th class="pr-1 pl-1">09.07월</th>
                                            <th class="pr-1 pl-1">09.08월</th>
                                            <th class="pr-1 pl-1">09.09월</th>
                                            <th class="pr-1 pl-1">09.10월</th>
                                            <th class="pr-1 pl-1">09.11월</th>
                                            <th class="pr-1 pl-1">09.12월</th>
                                            <th class="pr-1 pl-1">10월.01월</th>
                                            <th class="pr-1 pl-1">10월.02월</th>
                                            <th class="pr-1 pl-1">10월.03월</th>
                                            <th class="pr-1 pl-1">10월.04월</th>
                                            <th class="pr-1 pl-1">10월.05월</th>
                                            <th class="pr-1 pl-1">10월.06월</th>
                                            <th class="pr-1 pl-1">10월.07월</th>
                                            <th class="pr-1 pl-1">10월.08월</th>
		                                </tr>
		                            </thead>
                                    <tr class="text-right">
										<td class="text-center" style="background:#f4f6f9;">월별재대출채권액</td>
										<td>0.0</td>
										<td>10.0</td>
										<td>13.0</td>
										<td>16.0</td>
										<td>41.0</td>
										<td>57.5</td>
                                        <td>51.0</td>
										<td>78.0</td>
										<td>91.5</td>
                                        <td>0.0</td>
										<td>0.0</td>
										<td>290.0</td>
                                        <td>220.4</td>
                                        <td>0.0</td>
                                        <td>453.9</td>
                                        <td>879.7</td>
                                        <td>907.2</td>
                                        <td>0.0</td>
                                        <td>0.0</td>
                                        <td>0.0</td>
                                        <td>0.0</td>
                                        <td>610.6</td>
                                        <td>491.2</td>
                                        <td>663.1</td>
                                        <td>0.0</td>
                                        <td>0.0</td>
                                        <td>0.0</td>
                                        <td>0.0</td>
									</tr>
                                    <tr class="text-right">
										<td class="text-center" style="background:#f4f6f9;">재대출 누계액</td>
										<td>0.0</td>
										<td>10.0</td>
										<td>23.0</td>
										<td>39.0</td>
										<td>80.0</td>
										<td>137.5</td>
                                        <td>188.5</td>
										<td>266.5</td>
										<td>358.0</td>
                                        <td>358.0</td>
										<td>358.0</td>
										<td>648.0</td>
                                        <td>868.4</td>
                                        <td>868.4</td>
                                        <td>1,322.3</td>
                                        <td>2,202.0</td>
                                        <td>3,109.2</td>
                                        <td>3,109.2</td>
                                        <td>3,109.2</td>
                                        <td>3,109.2</td>
                                        <td>3,109.2</td>
                                        <td>6,749.1</td>
                                        <td>7,240.3</td>
                                        <td>7,903.4</td>
                                        <td>7,903.4</td>
                                        <td>7,903.4</td>
                                        <td>7,903.4</td>
                                        <td>7,903.4</td>
									</tr>
                                </table>
                            </div>
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


    $(document).ready(function(){
        getMisVintage();
    });
	
    function getMisVintage()
    {
        var table = $('#bond_sum_table').DataTable( {
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
        
        var table2 = $("#repayment_table").DataTable( {
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
        
        var table3 = $("#delay_rate_table").DataTable( {
            destroy: true,
            scrollY:        "400px",
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
        
        var table4 = $("#mon_table").DataTable( {
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
#data-area table {
	margin-bottom: 0;
}
.DTFC_LeftBodyLiner { 
    overflow-x: hidden;
}
</style>

@endsection