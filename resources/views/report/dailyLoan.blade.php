@extends('layouts.master')


@section('content')
{{-- Content Wrapper. Contains page content --}}
<div class="col-12" >
    {{-- Main content--}} 
    <section class="content">	

        <div class="col-md-12 pl-0">
            <div class="card card-lightblue card-outline">
                <div class="box box-{{ config('view.box') }}">
                <form id="form_dailyloan">
                    @csrf
                    <input type="hidden" name="excelDownCd" id="excelDownCd{{ $result['listName'] }}">
                    <input type="hidden" name="excelUrl" id="excelUrl{{ $result['listName'] }}">
                    <input type="hidden" name="etc" id="etc{{ $result['listName'] }}">
                    <input type="hidden" name="down_div" id="down_div{{ $result['listName'] }}">
                    <input type="hidden" name="excel_down_div" id="excel_down_div{{ $result['listName'] }}">
                    <input type="hidden" name="down_filename" id="down_filename{{ $result['listName'] }}">


                    <div class="card-header pt-2">
                        <div class="card-tools form-inline" id="searchBox" style=" justify-content: flex-end;">
                        <div id="button-area">
                        <span class="pr-2" id="update_time"></span>
                        @if(Func::funcCheckPermit("H022"))
                        <button type="button" class="btn btn-sm btn-success" onclick="excelDownModal('/report/dailyloanexcel','form_dailyloan')">엑셀다운</button>
                        @endif
                        </div>
                            <div class="mr-1 mb-1 mt-1" ></div> 
                                <select class="form-control form-control-sm selectpicker mr-1 mb-1 mt-1" id="manager_code" name="manager_code" >
                                    <option value="">지점</option>
                                    {{ Func::printOption($array_branch,Func::nvl($result['manager_code'],""))  }}
                                </select>
                                <select class="form-control form-control-sm selectpicker mr-1 mb-1 mt-1" >
                                    <option value="info_date">기준일</option>
                                </select>
                                <div class="input-group date mt-0 mb-0 datetimepicker" id="info_date" data-target-input="nearest">
                                    <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#info_date" id="info_date_id" name="info_date" DateOnly="true" onchange="checkDate(this)" value="{{ $result['info_date'] ?? date("Y-m-d") }}" size="6">
                                    <div class="input-group-append" data-target="#info_date" data-toggle="datetimepicker">
                                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                                    </div>
                                </div>
                                <div class="input-group mt-1 mb-1  input-group-sm ml-1 align-center">
                                    <div class="input-group-append">
                                        <button type="button" class="btn btn-default" onclick="getDailyLoanList()" ><i class="fa fa-search"></i></button>
                                        {{-- <button type="button" class="btn btn-default" onclick="dailyLoanBatch()"><i class="fas fa-sync"></i></button> --}}
                                    </div>
                                </div>

                        </div>
                    </div>
                        <div>
                            <div id="loading-area"></div>
                            <div id="data-area" class="card-body m-0 p-0">
                            <div style="width:100%; display:flex;">
                            <div style="width:45%; margin: 2% 0 0 2%;">
                            <span class="font16px fontBold"><i class="fa fa-money-bill"></i>융자잔고</span>
                                <table id="dailyloan_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
                                <thead class="text-center">
                                <tr>
                                    <th class="pl-1 pr-1" rowspan=2>구분</th>
                                    <th class="pl-1 pr-1" colspan=2>전월말</th>
                                    <th class="pl-1 pr-1" colspan=2>당일</th>
                                    <th class="pl-1 pr-1" colspan=2>증감</th>
                                </tr>
                                <tr>
                                    <th class="pl-1 pr-1">건수</th>
                                    <th class="pl-1 pr-1">금액</th>
                                    <th class="pl-1 pr-1">건수</th>
                                    <th class="pl-1 pr-1">금액</th>
                                    <th class="pl-1 pr-1">건수</th>
                                    <th class="pl-1 pr-1">금액</th>
                                </tr>
                                </thead>
                                <tr class="text-right">
									<td class="text-center">일반</td>
									<td>594</td>
									<td>9,271,691,413</td>
									<td>594</td>
									<td>9,268,854,057</td>
									<td>0</td>
									<td>-2,837,356</td>
								</tr>
                                <tr class="text-right">
									<td class="text-center">화해</td>
									<td>215</td>
									<td>503,231,074</td>
									<td>215</td>
									<td>503,143,437</td>
									<td>0</td>
									<td>-87,637</td>
								</tr>
                                <tr class="text-right color1">
									<td class="text-center">합계</td>
									<td>809</td>
									<td>9,774,922,487</td>
									<td style="border: 2px solid #343a40;border-right: 1px solid #dee2e6;">809</td>
									<td style="border: 2px solid #343a40;border-left: 1px solid #dee2e6;">9,771,997,494</td>
									<td>0</td>
									<td>-2,924,993</td>
								</tr>
                                </table>
                                <span class="font16px fontBold">영업성장</span>
                                <table id="dailyloan_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
                                <thead class="text-center">
                                <tr>
                                    <th class="pl-1 pr-1" rowspan=2 colspan=2>구분</th>
                                    <th class="pl-1 pr-1" colspan=2>일계</th>
                                    <th class="pl-1 pr-1" colspan=2>월계</th>
                                </tr>
                                <tr>
                                    <th class="pl-1 pr-1" style="border-bottom: 2px solid #343a40;">건수</th>
                                    <th class="pl-1 pr-1" style="border-bottom: 2px solid #343a40;">금액</th>
                                    <th class="pl-1 pr-1">건수</th>
                                    <th class="pl-1 pr-1">금액</th>
                                </tr>
                                </thead>
                                <tr class="text-right">
									<td colspan=2 class="text-center">일일성장</td>
									<td style="border: 2px solid #343a40;border-right: 1px solid #dee2e6;">0</td>
									<td style="border: 2px solid #343a40;border-left: 1px solid #dee2e6;">-650,573</td>
									<td>0</td>
									<td>-2,924,993</td>
								</tr>
                                <tr class="text-right">
									<td rowspan=5 class="text-center title-center">대출</td>
									<td style="border-right: 2px solid #343a40;" class="text-center">신규</td>
									<td>0</td>
									<td style="border-right: 2px solid #343a40;">0</td>
									<td>0</td>
									<td>0</td>
								</tr>
                                <tr class="text-right">
									<td style="border-right: 2px solid #343a40;" class="text-center">재대출</td>
									<td>0</td>
									<td style="border-right: 2px solid #343a40;">0</td>
									<td>0</td>
									<td>0</td>
								</tr>
                                <tr class="text-right">
									<td style="border-right: 2px solid #343a40;" class="text-center">추가</td>
									<td>0</td>
									<td style="border-right: 2px solid #343a40;">0</td>
									<td>0</td>
									<td>0</td>
								</tr>
                                <tr class="text-right">
									<td style="border-right: 2px solid #343a40;" class="text-center">재계약</td>
									<td>0</td>
									<td style="border-right: 2px solid #343a40;">0</td>
									<td>0</td>
									<td>0</td>
								</tr>
                                <tr class="text-right">
									<td class="color1 border-right text-center">대출합계</td>
									<td class="color1" style="border-left: 2px solid #343a40;border-bottom: 2px solid #343a40;">0</td>
									<td class="color1" style="border-right: 2px solid #343a40;border-bottom: 2px solid #343a40;">0</td>
									<td class="color1">0</td>
									<td class="color1">0</td>
								</tr>
                                <tr class="text-right">
									<td rowspan=12 class="text-center title-center">상환</td>
									<td style="border-right: 2px solid #343a40;"  class="text-center">원금상환</td>
									<td>3</td>
									<td style="border-right: 2px solid #343a40;">650,573</td>
									<td>18</td>
									<td>2,924,993</td>
								</tr>
                                <tr class="text-right">
									<td style="border-right: 2px solid #343a40;" class="text-center">상각</td>
									<td>0</td>
									<td style="border-right: 2px solid #343a40;">0</td>
									<td>0</td>
									<td>0</td>
								</tr>
                                <tr class="text-right">
									<td class="color2 text-center" style="border-right: 2px solid #343a40;">원금감소분</td>
									<td class="color2" style="border-bottom: 2px solid #343a40;">3</td>
									<td style="border-right: 2px solid #343a40;border-bottom: 2px solid #343a40;"class="color2">650,573</td>
									<td class="color2">18</td>
									<td class="color2">2,924,993</td>
								</tr>
                                <tr class="text-right">
									<td style="border-right: 2px solid #343a40;" class="text-center">정상완제</td>
									<td>0</td>
									<td style="border-right: 2px solid #343a40;">0</td>
									<td>0</td>
									<td>0</td>
								</tr>
                                <tr class="text-right">
									<td style="border-right: 2px solid #343a40;" class="text-center">화해완제</td>
									<td>0</td>
									<td style="border-right: 2px solid #343a40;">0</td>
									<td>0</td>
									<td>0</td>
								</tr>
                                <tr class="text-right">
									<td class="color2 text-center">완제합계</td>
									<td class="color2" style="border-left: 2px solid #343a40;border-bottom: 2px solid #343a40;">0</td>
									<td class="color2" style="border-right: 2px solid #343a40;border-bottom: 2px solid #343a40;">0</td>
									<td class="color2">0</td>
									<td class="color2">0</td>
								</tr>
                                <tr class="text-right">
									<td class="text-center">정상이자</td>
									<td>3</td>
									<td>3,235,013</td>
									<td>19</td>
									<td>8,324,116</td>
								</tr>
                                <tr class="text-right">
									<td class="text-center">연체이자</td>
									<td>1</td>
									<td>58,906</td>
									<td>8</td>
									<td>288,994</td>
								</tr>
                                <tr class="text-right">
									<td class="text-center">화해이자</td>
									<td>0</td>
									<td>0</td>
									<td>2</td>
									<td>71,078</td>
								</tr>
                                <tr class="text-right">
									<td class="color2 text-center">이자상환합계</td>
									<td class="color2">4</td>
									<td class="color2">3,293,919</td>
									<td class="color2">29</td>
									<td class="color2">8,684,188</td>
								</tr>
                                <tr class="text-right">
									<td class="text-center">가수금</td>
									<td>0</td>
									<td>0</td>
									<td>0</td>
									<td>0</td>
								</tr>
                                <tr class="text-right">
									<td class="color1 text-center">상환합계</td>
									<td class="color1">4</td>
									<td class="color1">3,947,219</td>
									<td class="color1">32</td>
									<td class="color1">11,611,925</td>
								</tr>
                                <tr class="text-right">
									<td rowspan=6 title-center class="text-center title-center">가산취소</td>
									<td class="text-center">대출취소</td>
									<td>0</td>
									<td>0</td>
									<td>0</td>
									<td>0</td>
								</tr>
                                <tr class="text-right">
									<td class="text-center">원금상환취소</td>
									<td>0</td>
									<td>0</td>
									<td>0</td>
									<td>0</td>
								</tr>
                                <tr class="text-right">
									<td class="text-center">이자상환취소</td>
									<td>0</td>
									<td>0</td>
									<td>0</td>
									<td>0</td>
								</tr>
                                <tr class="text-right">
									<td class="text-center">상각취소</td>
									<td>0</td>
									<td>0</td>
									<td>0</td>
									<td>0</td>
								</tr>
                                <tr class="text-right">
									<td class="text-center">상각재조정</td>
									<td>0</td>
									<td>0</td>
									<td>0</td>
									<td>0</td>
								</tr>
                                <tr class="text-right">
									<td class="color1 text-center">기산취소</td>
									<td class="color1">0</td>
									<td class="color1">0</td>
									<td class="color1">0</td>
									<td class="color1">0</td>
								</tr>
                                </table>
                                
                                
                            </div>
                            
                            <div style="width:45%; margin: 2% 0 0 4%;">
                            <span class="font16px fontBold"><i class="fa fa-chart-bar"></i>융자잔고 추이와 월성장율 (단위:백만원)</span>
                            <div class="position-relative mb-5 mt-3">
                                <canvas id="chart-one" height="300"></canvas>
                            </div>
                            
                            <span class="font16px fontBold"><i class="fa fa-signal"></i>대출액,상환액 (단위:천원)</span>
                            <div style="display: flex;justify-content: space-between;">
                                <div class="position-relative mb-4 mt-3" style="width:380px;">
                                    <canvas id="chart-two" height="300"></canvas>
                                </div>
                                <div class="position-relative mb-4 mt-3" style="width:380px;">
                                    <canvas id="chart-three" height="300"></canvas>
                                </div>
                            </div>    
                            </div>
                            
                            </div>
                            
                            
                            <div style="width:90%; margin: 2% 0 0 2%;">
                            <span class="font16px fontBold">연체구간</span>
                                <table id="dailyloan_table" class="table table-sm table-hover loan-info-table table-bordered nowrap" style="width:100%;">
                                <thead class="text-center">
                                <tr>
                                    <th class="pl-1 pr-1" rowspan=2>구간</th>
                                    <th class="pl-1 pr-1" colspan=2>1일~3일</th>
                                    <th class="pl-1 pr-1" colspan=2>4일~10일</th>
                                    <th class="pl-1 pr-1" colspan=2>11일~30일</th>
                                    <th class="pl-1 pr-1" colspan=2>31일~60일</th>
                                    <th class="pl-1 pr-1" colspan=2>61일~90일</th>
                                    <th class="pl-1 pr-1" colspan=2>91일~180일</th>
                                    <th class="pl-1 pr-1" colspan=2>181일~</th>
                                    <th class="pl-1 pr-1" colspan=2>합계</th>
                                </tr>
                                <tr>
                                    <th class="pl-1 pr-1">건수</th>
                                    <th class="pl-1 pr-1">금액</th>
                                    <th class="pl-1 pr-1">건수</th>
                                    <th class="pl-1 pr-1">금액</th>
                                    <th class="pl-1 pr-1">건수</th>
                                    <th class="pl-1 pr-1">금액</th>
                                    <th class="pl-1 pr-1">건수</th>
                                    <th class="pl-1 pr-1">금액</th>
                                    <th class="pl-1 pr-1">건수</th>
                                    <th class="pl-1 pr-1">금액</th>
                                    <th class="pl-1 pr-1">건수</th>
                                    <th class="pl-1 pr-1">금액</th>
                                    <th class="pl-1 pr-1">건수</th>
                                    <th class="pl-1 pr-1">금액</th>
                                    <th class="pl-1 pr-1">건수</th>
                                    <th class="pl-1 pr-1">금액</th>
                                </tr>
                                </thead>
                                <tr class="text-right">
                                    <td class="text-center">일반채권</td>
                                    <td class="pl-1 pr-1">5</td>
                                    <td class="pl-1 pr-1">14,637,946</td>
                                    <td class="pl-1 pr-1">3</td>
                                    <td class="pl-1 pr-1">39,499,222</td>
                                    <td class="pl-1 pr-1">4</td>
                                    <td class="pl-1 pr-1">14,618,002</td>
                                    <td class="pl-1 pr-1">2</td>
                                    <td class="pl-1 pr-1">3,638,648</td>
                                    <td class="pl-1 pr-1">4</td>
                                    <td class="pl-1 pr-1">209,980,942</td>
                                    <td class="pl-1 pr-1">3</td>
                                    <td class="pl-1 pr-1">21,232,333</td>
                                    <td class="pl-1 pr-1">349</td>
                                    <td class="pl-1 pr-1">2,382,604,217</td>
                                    <td class="pl-1 pr-1">370</td>
                                    <td class="pl-1 pr-1">2,686,211,310</td>
                                </tr>
                                <tr class="text-right">
									<td class="text-center">연체율</td>
									<td class="pl-1 pr-1">0.62%</td>
                                    <td class="pl-1 pr-1">0.15%</td>
                                    <td class="pl-1 pr-1">0.37%</td>
                                    <td class="pl-1 pr-1">0.4%</td>
                                    <td class="pl-1 pr-1">0.49%</td>
                                    <td class="pl-1 pr-1">0.15%</td>
                                    <td class="pl-1 pr-1">0.25%</td>
                                    <td class="pl-1 pr-1">0.04%</td>
                                    <td class="pl-1 pr-1">0.49%</td>
                                    <td class="pl-1 pr-1">2.15%</td>
                                    <td class="pl-1 pr-1">0.37%</td>
                                    <td class="pl-1 pr-1">0.22%</td>
                                    <td class="pl-1 pr-1">43.14%</td>
                                    <td class="pl-1 pr-1">24.38%</td>
                                    <td class="pl-1 pr-1">45.74%</td>
                                    <td class="pl-1 pr-1">27.49%</td>
								</tr>
                                <tr class="text-right">
									<td class="text-center">화해채권</td>
									<td class="pl-1 pr-1">3</td>
                                    <td class="pl-1 pr-1">6,845,978</td>
                                    <td class="pl-1 pr-1">13</td>
                                    <td class="pl-1 pr-1">18,729,829</td>
                                    <td class="pl-1 pr-1">6</td>
                                    <td class="pl-1 pr-1">26,920,829</td>
                                    <td class="pl-1 pr-1">11</td>
                                    <td class="pl-1 pr-1">15,904,180</td>
                                    <td class="pl-1 pr-1">10</td>
                                    <td class="pl-1 pr-1">45,544,064</td>
                                    <td class="pl-1 pr-1">16</td>
                                    <td class="pl-1 pr-1">56,163,042</td>
                                    <td class="pl-1 pr-1">66</td>
                                    <td class="pl-1 pr-1">176,065,398</td>
                                    <td class="pl-1 pr-1">125</td>
                                    <td class="pl-1 pr-1">346,173,320</td>
								</tr>
                                <tr class="text-right">
									<td class="text-center">연체율</td>
									<td>0.37%</td>
                                    <td>0.07%</td>
                                    <td>1.61%</td>
                                    <td>0.19%</td>
                                    <td>0.74%</td>
                                    <td>0.28%</td>
                                    <td>1.36%</td>
                                    <td>0.16%</td>
                                    <td>1.24%</td>
                                    <td>0.47%</td>
                                    <td>1.98%</td>
                                    <td>0.57%</td>
                                    <td>8.16%</td>
                                    <td>1.8%</td>
                                    <td>15.45%</td>
                                    <td>3.54%</td>
								</tr>
                                <tr class="text-right color1">
									<td class="text-center">합계</td>
									<td>8</td>
                                    <td>21,483,924</td>
                                    <td>16</td>
                                    <td>58,229,051</td>
                                    <td>10</td>
                                    <td>41,538,831</td>
                                    <td>13</td>
                                    <td>19,542,828</td>
                                    <td>14</td>
                                    <td>255,525,006</td>
                                    <td>19</td>
                                    <td>77,395,375</td>
                                    <td>415</td>
                                    <td>2,558,669,615</td>
                                    <td>495</td>
                                    <td>3,032,384,630</td>
								</tr>
                                <tr class="text-right color1">
									<td class="text-center">연체율</td>
									<td>0.99%</td>
                                    <td>0.22%</td>
                                    <td>1.98%</td>
                                    <td>0.6%</td>
                                    <td>1.24%</td>
                                    <td>0.43%</td>
                                    <td>1.61%</td>
                                    <td>0.2%</td>
                                    <td>1.73%</td>
                                    <td>2.61%</td>
                                    <td>2.35%</td>
                                    <td>0.79%</td>
                                    <td>51.3%</td>
                                    <td>26.18%</td>
                                    <td>61.19%</td>
                                    <td>31.03%</td>
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
            </div>
        </div>


<!-- 엑셀 다운 모달 -->
<div class="modal fade" id="excelDownModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">엑셀 다운로드</h5>
                <button type="button" class="close" id="excelClose"data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="excelForm" name="excelForm" method="post" enctype="multipart/form-data" action="" onSubmit="return false;">
                    @csrf
                    <div class="row mt-1">
                        <span class="form-control-sm col-3" for="reason" style="font-weight:700; padding-top:3px; width:30px;">다운로드 사유 : </span> 
                        <select class="form-control form-control-sm text-xs col-md-6" name="excel_down_cd" id="excel_down_cd" onchange="etc_check();">
                            <option value=''>선택</option>
                                {{ Func::printOption(Func::getConfigArr("excel_down_cd")) }} 
                        </select>
                        <input class="form-control form-control-sm text-xs col-md-6"type="text" id="etc" style="display:none;margin-left:120px;"placeholder="사유를 입력해주세요">
                    </div>
                    <div class="row mt-1">
                        <div class="icheck-success d-inline">
                            <span class="form-control-sm col-3" for="execution" style="font-weight:700; margin-top:10px;">다운로드 실행구분 : </span> 
                            <label class="radio-block" style="width:110px; padding-left: 5px!important;">
                                <input type="radio" name="excel_down_div" id="reservation" value="S" checked onchange="input_filename()"> 예약실행 &nbsp;
                            </label>
                        </div>
                        <div class="icheck-success d-inline">
                            <label class="radio-block" style="padding-left: 5px!important;">
                                <input type="radio" name="excel_down_div" id="realtime" value="E" onchange="input_filename()"> 바로실행 &nbsp;
                            </label>
                        </div>
                        <input class="form-control form-control-sm text-xs col-md-6"type="text" id="down_filename" style="display:none;margin-left:120px;"placeholder="다운받을 파일명을 입력해주세요">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <span class="form-control-sm col-8 text-red" id='excelMsg' style="display:none;">* 다운로드 중 입니다. </span> 
                <button type="button" class="btn btn-sm btn-secondary" id="closeBtn" data-dismiss="modal" aria-hidden="true">닫기</button>
                <button type="button" class="btn btn-sm btn-primary" onclick="excelDown('form_dailyloan');">다운로드</button>
            </div>
        </div>
    </div>
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

<script src="/plugins/chart.js/Chart.min.js"></script>
<script>
$(function () {
  'use strict'

  var ticksStyle = {
    fontColor: '#495057',
    fontStyle: 'bold'
  }

  var mode = 'index'
  var intersect = true

  // 융자잔고 추이와 월상장율
  var $chartOne = $('#chart-one');
  var chartOne = new Chart($chartOne, {
    type: 'bar',
    data: {
      labels: ['22/04', '22/05', '22/06', '22/07', '22/08', '22/09', '22/10', '22/11', '22/12'],
      datasets: [{
        type: 'line',
        data: [5349, 5770, 7600, 7920, 8321, 8145, 7324, 7226, 7221 ], 
        backgroundColor: '#007bff',
        borderColor: '#007bff',
        fill: false,
        label: '융자잔고',
        yAxisID: 'y-left'
      },
      {
        type: 'bar',
        data: [0, 7.88, 24.10 ,10.60, 5.07, -2.12, -10.08, -1.33, -0.22 ], 
        backgroundColor: '#cd9ca0',
        borderColor: '#cd9ca0',
        pointBorderColor: '#bd5786',
        pointBackgroundColor: '#bd5786',
        fill: false,
        label: '성장율',
        yAxisID: 'y-right'
      }]
    },
    options: {
      maintainAspectRatio: false,
      tooltips: {
        mode: mode,
        intersect: intersect
      },
      hover: {
        mode: mode,
        intersect: intersect
      },
      legend: {
        labels: {
            fontColor: 'black'
        }
      },
      scales: {
        yAxes: [{
                    id: 'y-left',
                    type: 'linear',
                    position: 'left',
                    stacked: true,
                    ticks: {
                        min: 0,
                        max: 10000,
                        stepSize: 2000,
                        fontColor: '#000000',
                        callback: function(value, index, values){
                            return value;
                        }
                    }
                },
                {
                    id: 'y-right',
                    type: 'linear',
                    position: 'right',
                    ticks: {
                        min: -20,
                        max: 30,
                        stepSize: 10,
                        fontColor: '#000000',
                        callback: function(value, index, values){
                            return value + '.00';
                        }
                    },
                    scaleLabel: {
                        display: true
                    },
                }
        ],
        xAxes: [{
          display: true,
          gridLines: {
            display: true
          },
          ticks: ticksStyle
        }]
      }
    }
  });
  
  // 대출액
  var $charTwo = $("#chart-two")
  var charTwo = new Chart($charTwo, {
    type: 'bar',
    data: {
      labels: ['신규', '재대출', '추가', '재계약'],
      datasets: [{
        data: [0, 0, 0, 0],
        label: '대출액',
        backgroundColor: '#a3b5d4',
        borderColor: '#a3b5d4',
        pointBorderColor: '#007bff',
        pointBackgroundColor: '#007bff',
        fill: false
      }],
    },
    options: {
      maintainAspectRatio: false,
      tooltips: {
        mode: mode,
        intersect: intersect
      },
      hover: {
        mode: mode,
        intersect: intersect
      },
      legend: {
        labels: {
            fontColor: 'black'
        }
      },
      scales: {
        yAxes: [{
          // display: false,
          gridLines: {
            display: true,
            borderDashOffset: 0.2,
            // color: 'rgba(0, 0, 0, .2)',
            zeroLineColor: 'transparent'
          },
          ticks: $.extend({
            beginAtZero: true,
            suggestedMax: 10
          }, ticksStyle)
        }],
        xAxes: [{
          display: true,
          gridLines: {
            display: true
          },
          ticks: ticksStyle
        }]
      }
    }
  });
  
  // 상환액
  var $chartThree = $("#chart-three")
  var chartThree = new Chart($chartThree, {
    type: 'bar',
    data: {
      labels: ['원금상환', '이자상환', '상각', '가수금'],
      datasets: [
      {
        data: [94, 1607, 0 ,0 ],
        label: '상환액',
        backgroundColor: '#c4e0af',
        borderColor: '#c4e0af',
        fill: false
      }]
    },
    options: {
      maintainAspectRatio: false,
      tooltips: {
        mode: mode,
        intersect: intersect
      },
      hover: {
        mode: mode,
        intersect: intersect
      },
      legend: {
        labels: {
            fontColor: 'black'
        }
      },
      scales: {
        yAxes: [{
          gridLines: {
            display: true,
            borderDashOffset: 0.2,
            zeroLineColor: 'transparent'
          },
          ticks: $.extend({
            beginAtZero: true,
            stepSize: 500,
          }, ticksStyle)
        }],
        xAxes: [{
          display: true,
          gridLines: {
            display: true
          },
          ticks: ticksStyle
        }]
      }
    }
  });

    // 예약 다운 시 파일명 입력칸 보이기
    if($('input[name="excel_down_div"]:checked').val() == "S")
    {
        $('#down_filename').css('display', 'block');
    }
});  


function dailyLoanBatch()
{
    if(!confirm('기준일자 데이터를 재생성 하시겠습니까?'))
    {
        return; 
    }

    if(ccCheck()) return; //중복클릭방지
    // 기준일자 데이터 재생성 범위 수정(전일자까지만 재생성 가능)
    var info_date = $('#info_date_id').val();   // 검색일자 (2023-02-17)
    var bas_de = info_date.replace(/-/gi,"");   // 검색일자 (20230217)
    var enable_date = "{{ date('Ymd', strtotime( '-1 days') ) }}"; //어제 날짜 (20230216)
   // alert(enable_date);
   console.log(enable_date);
   console.log(bas_de);
    
    if(enable_date > bas_de )
    {
        getDailyLoanList();
        return;
    }

    $('#data-area').hide();
    $("#loading-area").append(loadingString);   

    $.get(
        "/report/dailyloanbatch/"+info_date, 
        "", 
        function(data) {
            globalCheck = false;
            $('#loading-area').empty();
            $('#data-area').show();
            getDailyLoanList();
    });
}

// getDailyLoanList();

function getDailyLoanList(div)
{
    $('#update_time').empty();
    var info_date    = $('#info_date_id').val();
    var manager_code = $('#manager_code').val();
    console.log(manager_code);
    var table = $('#dailyloan_table').DataTable( {
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
                leftColumns: 3,
            },
            ajax:   {
                    "type" : "get",
                    "url" : "/report/dailyloanlist?info_date="+info_date+"&manager_code="+manager_code,
                    "dataType": "JSON"
                    },

            rowsGroup: [0,1,2],
            createdRow: function(row, data, dataIndex) {
                // 마지막컬럼에서 두번째 있는 save_time으로 생성시간 세팅해줌 
                if(dataIndex == 0)
                {
                    var update_time = getTimestamp(data[data.length-2]);
                    if(update_time)
                    {
                        $('#update_time').html("생성시간 : "+update_time);
                    }
                }
                if(data[0].indexOf('총합계') != -1 || data[1].indexOf('합계') != -1 || data[2].indexOf('소계') != -1)
                {
                    $(row).css('background-color', '#f4f6f9');
                }
            },

            columnDefs: [
                    { targets: [0,1,2], createdCell: function (td, cellData, rowData, row, col) {
                        $(td).css('background-color', '#f4f6f9');
                        $(td).addClass('text-center');
                        $(td).addClass('pr-1');
                        $(td).addClass('pl-1');
                        }
                    },
                    { targets: '_all',createdCell: function (td, cellData, rowData, row, col) {
                        $(td).addClass('text-right');
                        }
                    }
            ],
        });
}




    </script>

<style>
.DTFC_LeftBodyLiner { 
    overflow-x: hidden; 
}
table.dataTable.no-footer {
    border-bottom: 0px;
}
.color1
{
    background-color: #FFE5DC;
}
.color2
{
    background-color: #DDDDFF;
}
.fa-money-bill, .fa-chart-bar, .fa-signal
{
    padding: 0 4px 0 1px;
}
.card-body.p-0 .table tbody > tr > td:first-of-type {
    padding-left: 0;
}
.font16px {
	font-size: 16px;
    font-weight: bold;
}
.title-center {
    vertical-align: middle;
}
</style>




@endsection