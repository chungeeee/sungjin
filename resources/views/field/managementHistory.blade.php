
<div class="content-wrapper" style="margin-left:0px;">
    <form class="form-horizontal" role="form" name="form_history" id="form_history">
        <input type="hidden" id="contract_info_no" name="contract_info_no" value="{{ $v->no ?? '' }}">
        <section class="content" id="loading">
            <div class="row">
                <div class="col-md-12">
                    <div class="box box-{{ config('view.box') }}">
                        <div class="box-header with-border">
                            <button type="button" id="sub" class="btn btn-sm pull-left" onclick="this.blur();excelfileUpload()">엑셀업로드</button>
                            <button type="button" id="sub" class="btn btn-sm pull-left" onclick="this.blur();excelfileRemove()">엑셀업로드 제거</button>
                            <button type="button" id="sub" class="btn btn-sm pull-right" onclick="removeCode();">내역외 삭제</button>
                            <button type="button" id="sub" class="btn btn-sm pull-right" onclick="plusCode();">내역외 추가</button>
                        </div>
                        <div class="box-body">
                            <!-- ================= 실행내역서 ================= -->
                            <div class="col-md-12">
                                <table border='1' class='table table-bordered'>
                                    <thead>
                                        <tr align='center'>
                                            <td style="border-bottom-color: #000000; width:12%; vertical-align:middle;" rowspan=3 bgcolor='#C6EFCE'>품명</td>
                                            <td style="border-bottom-color: #000000; width:15%; vertical-align:middle;" rowspan=3 bgcolor='#C6EFCE'>규격</td>
                                            <td style="border-bottom-color: #000000; width:4%; vertical-align:middle;" rowspan=3 bgcolor='#C6EFCE'>단위</td>
                                            <td style="width:65%;" colspan=7 bgcolor='#C6EFCE'>실행내역서</td>
                                            <td style="border-bottom-color: #000000; width:4%; vertical-align:middle;" rowspan=3 bgcolor='#C6EFCE'>비고</td>
                                        </tr>
                                        <tr align='center'>
                                            <td style="border-bottom-color: #000000; width:5%; vertical-align:middle;" rowspan=2 bgcolor='#C6EFCE'>수량</td>
                                            <td style="width:20%;" colspan=2 bgcolor='#C6EFCE'>재료비</td>
                                            <td style="width:20%;" colspan=2 bgcolor='#C6EFCE'>노무비</td>
                                            <td style="width:20%;" colspan=2 bgcolor='#C6EFCE'>합계</td>
                                        </tr>
                                        <tr align='center'>
                                            <td style="border-bottom-color: #000000; width:10%;" bgcolor='#C6EFCE'>단가</td>
                                            <td style="border-bottom-color: #000000; width:10%;" bgcolor='#C6EFCE'>금액</td>
                                            <td style="border-bottom-color: #000000; width:10%;" bgcolor='#C6EFCE'>단가</td>
                                            <td style="border-bottom-color: #000000; width:10%;" bgcolor='#C6EFCE'>금액</td>
                                            <td style="border-bottom-color: #000000; width:10%;" bgcolor='#C6EFCE'>단가</td>
                                            <td style="border-bottom-color: #000000; width:10%;" bgcolor='#C6EFCE'>금액</td>
                                        </tr>
                                    </thead>
                                    <tbody id='tbodyCheck'>
                                        @if(!empty($v))
                                            @foreach($v as $key => $val)
                                                <tr align='center'>
                                                    <input type="hidden" name="val" value="">
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:left" id="name{{$key}}" name="name{{$key}}" value="{{ $val->name ?? '' }}">                         <!--품명-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:left" id="standard{{$key}}" name="standard{{$key}}" value="{{ $val->standard ?? '' }}">                 <!--규격-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:center" id="type{{$key}}" name="type{{$key}}" value="{{ $val->type ?? '' }}">                         <!--단위-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="volume{{$key}}" name="volume{{$key}}" value="{{ $val->volume ?? '' }}" onkeyup="countCheck({{$key}});"> <!--수량-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="material_price{{$key}}" name="material_price{{$key}}" value="{{ $val->material_price ?? '' }}" onkeyup="countCheck({{$key}});">     <!--재료비_단가-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="material_amount{{$key}}" name="material_amount{{$key}}" value="{{ $val->material_amount ?? '' }}" readonly>   <!--재료비_금액-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="labor_price{{$key}}" name="labor_price{{$key}}" value="{{ $val->labor_price ?? '' }}" onkeyup="countCheck({{$key}});">           <!--노무비_단가-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="labor_amount{{$key}}" name="labor_amount{{$key}}" value="{{ $val->labor_amount ?? '' }}" readonly>         <!--노무비_금액-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="sum_price{{$key}}" name="sum_price{{$key}}" value="{{ $val->sum_price ?? '' }}" readonly>               <!--합계_단가-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="sum_amount{{$key}}" name="sum_amount{{$key}}" value="{{ $val->sum_amount ?? '' }}" readonly>             <!--합계_금액-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:center" id="etc{{$key}}" name="etc{{$key}}" value="{{ $val->etc ?? '' }}">                           <!--비고-->
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <thead>
                                        <tr align='center'>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000;">[소 계]</td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000;"></td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000;"></td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000;"></td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000;"></td>
                                            <td style="background-color:#C6EFCE; border-color:#000000;">
                                                <input type="text" style="width:100%; background-color:#C6EFCE; border: 0; text-align:right" id="material_amount_sum" value="" readonly>
                                            </td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000;"></td>
                                            <td style="background-color:#C6EFCE; border-color:#000000;">
                                                <input type="text" style="width:100%; background-color:#C6EFCE; border: 0; text-align:right" id="labor_amount_sum" value="" readonly>
                                            </td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000;"></td>
                                            <td style="background-color:#C6EFCE; border-color:#000000;">
                                                <input type="text" style="width:100%; background-color:#C6EFCE; border: 0; text-align:right" id="sum_amount_sum" value="" readonly>
                                            </td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000;"></td>
                                        </tr>
                                    </thead>
                                    <tbody id='tableCheck'>
                                        @if(!empty($k))
                                            @foreach($k as $key => $val)
                                                <tr align='center'>
                                                    <input type="hidden" name="key" value="">
                                                    <input type="hidden" id="sub_code1_{{$key}}" name="sub_code1_{{$key}}" value="{{ $val->code1 ?? '' }}">
                                                    <input type="hidden" id="sub_code2_{{$key}}" name="sub_code2_{{$key}}" value="{{ $val->code2 ?? '' }}">
                                                    <input type="hidden" id="sub_code3_{{$key}}" name="sub_code3_{{$key}}" value="{{ $val->code3 ?? '' }}">
                                                    <input type="hidden" id="sub_code4_{{$key}}" name="sub_code4_{{$key}}" value="{{ $val->code4 ?? '' }}">
                                                    <input type="hidden" id="sub_code5_{{$key}}" name="sub_code5_{{$key}}" value="{{ $val->code5 ?? '' }}">
                                                    <input type="hidden" id="sub_code6_{{$key}}" name="sub_code6_{{$key}}" value="{{ $val->code6 ?? '' }}">
                                                    <input type="hidden" id="sub_code7_{{$key}}" name="sub_code7_{{$key}}" value="{{ $val->code7 ?? '' }}">
                                                    <input type="hidden" id="sub_code8_{{$key}}" name="sub_code8_{{$key}}" value="{{ $val->code8 ?? '' }}">
                                                    <input type="hidden" id="sub_code9_{{$key}}" name="sub_code9_{{$key}}" value="{{ $val->code9 ?? '' }}">
                                                    <input type="hidden" id="sub_code10_{{$key}}" name="sub_code10_{{$key}}" value="{{ $val->code10 ?? '' }}">
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:left" id="sub_name{{$key}}" name="sub_name{{$key}}" value="{{ $val->name ?? '' }}">                         <!--품명-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:left" id="sub_standard{{$key}}" name="sub_standard{{$key}}" value="{{ $val->standard ?? '' }}">                 <!--규격-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:center" id="sub_type{{$key}}" name="sub_type{{$key}}" value="{{ $val->type ?? '' }}">                         <!--단위-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="sub_volume{{$key}}" name="sub_volume{{$key}}" value="{{ $val->volume }}" onkeyup="subcountCheck({{$key}});"> <!--수량-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="sub_material_price{{$key}}" name="sub_material_price{{$key}}" value="{{ $val->material_price }}" onclick="code_sum_open({{$key}});" readonly>     <!--재료비_단가-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="sub_material_amount{{$key}}" name="sub_material_amount{{$key}}" value="{{ $val->material_amount }}" readonly>   <!--재료비_금액-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="sub_labor_price{{$key}}" name="sub_labor_price{{$key}}" value="{{ $val->labor_price }}" onkeyup="subcountCheck({{$key}});">           <!--노무비_단가-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="sub_labor_amount{{$key}}" name="sub_labor_amount{{$key}}" value="{{ $val->labor_amount }}" readonly>         <!--노무비_금액-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="sub_sum_price{{$key}}" name="sub_sum_price{{$key}}" value="{{ $val->sum_price }}" readonly>               <!--합계_단가-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="sub_sum_amount{{$key}}" name="sub_sum_amount{{$key}}" value="{{ $val->sum_amount }}" readonly>             <!--합계_금액-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:center" id="sub_etc{{$key}}" name="sub_etc{{$key}}" value="{{ $val->etc ?? '' }}">                           <!--비고-->
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <tbody id='UnitPlus' style="border-color:#000000">
                                        @if(!empty($n))
                                            @foreach($n as $key => $val)
                                                <tr align='center'>
                                                    <input type="hidden" name="num" value="">
                                                    <input type="hidden" id="plus_code1_{{$key}}" name="plus_code1_{{$key}}" value="{{ $val->code1 ?? '' }}">
                                                    <input type="hidden" id="plus_code2_{{$key}}" name="plus_code2_{{$key}}" value="{{ $val->code2 ?? '' }}">
                                                    <input type="hidden" id="plus_code3_{{$key}}" name="plus_code3_{{$key}}" value="{{ $val->code3 ?? '' }}">
                                                    <input type="hidden" id="plus_code4_{{$key}}" name="plus_code4_{{$key}}" value="{{ $val->code4 ?? '' }}">
                                                    <input type="hidden" id="plus_code5_{{$key}}" name="plus_code5_{{$key}}" value="{{ $val->code5 ?? '' }}">
                                                    <input type="hidden" id="plus_code6_{{$key}}" name="plus_code6_{{$key}}" value="{{ $val->code6 ?? '' }}">
                                                    <input type="hidden" id="plus_code7_{{$key}}" name="plus_code7_{{$key}}" value="{{ $val->code7 ?? '' }}">
                                                    <input type="hidden" id="plus_code8_{{$key}}" name="plus_code8_{{$key}}" value="{{ $val->code8 ?? '' }}">
                                                    <input type="hidden" id="plus_code9_{{$key}}" name="plus_code9_{{$key}}" value="{{ $val->code9 ?? '' }}">
                                                    <input type="hidden" id="plus_code10_{{$key}}" name="plus_code10_{{$key}}" value="{{ $val->code10 ?? '' }}">
                                                    <input type="hidden" id="plus_code{{$key}}" name="plus_code{{$key}}" value="{{ $val->code ?? '' }}">
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:left" id="plus_name{{$key}}" name="plus_name{{$key}}" value="{{ $val->name ?? '' }}" > <!--품명-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:left" id="plus_standard{{$key}}" name="plus_standard{{$key}}" value="{{ $val->standard ?? '' }}" >  <!--규격-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:center" id="plus_type{{$key}}" name="plus_type{{$key}}" value="{{ $val->type ?? '' }}" >            <!--단위-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="plus_volume{{$key}}" name="plus_volume{{$key}}" value="{{ $val->volume }}" onkeyup="unitCheck({{$key}});"> <!--수량-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="plus_material_price{{$key}}" name="plus_material_price{{$key}}" value="{{ $val->material_price }}" onclick="code_sum_open_plus({{$key}});" readonly>     <!--재료비_단가-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="plus_material_amount{{$key}}" name="plus_material_amount{{$key}}" value="{{ $val->material_amount }}" readonly>   <!--재료비_금액-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="plus_labor_price{{$key}}" name="plus_labor_price{{$key}}" value="{{ $val->labor_price }}" onkeyup="unitCheck({{$key}});"> <!--노무비_단가-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="plus_labor_amount{{$key}}" name="plus_labor_amount{{$key}}" value="{{ $val->labor_amount }}" readonly>         <!--노무비_금액-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="plus_sum_price{{$key}}" name="plus_sum_price{{$key}}" value="{{ $val->sum_price }}" readonly>               <!--합계_단가-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:right" id="plus_sum_amount{{$key}}" name="plus_sum_amount{{$key}}" value="{{ $val->sum_amount }}" readonly>             <!--합계_금액-->
                                                    </td>
                                                    <td style="border-color:#000000">
                                                        <input type="text" style="width:100%; border: 0; text-align:center" id="plus_etc{{$key}}" name="plus_etc{{$key}}" value="{{ $val->etc ?? '' }}"> <!--비고-->
                                                    </td>
                                                </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                    <thead>
                                        <tr align='center'>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000">[소 계]</td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000"></td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000"></td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000"></td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000"></td>
                                            <td style="background-color:#C6EFCE; border-color:#000000;">
                                                <input type="text" style="width:100%; background-color:#C6EFCE; border: 0; text-align:right" id="sub_material_amount_sum" value="" readonly>
                                            </td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000"></td>
                                            <td style="background-color:#C6EFCE; border-color:#000000;">
                                                <input type="text" style="width:100%; background-color:#C6EFCE; border: 0; text-align:right" id="sub_labor_amount_sum" value="" readonly>
                                            </td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000"></td>
                                            <td style="background-color:#C6EFCE; border-color:#000000;">
                                                <input type="text" style="width:100%; background-color:#C6EFCE; border: 0; text-align:right" id="sub_sum_amount_sum" value="" readonly>
                                            </td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000"></td>
                                        </tr>
                                        <tr align='center'>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000">[합 계]</td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000"></td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000"></td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000"></td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000"></td>
                                            <td style="background-color:#C6EFCE; border-color:#000000;">
                                                <input type="text" style="width:100%; background-color:#C6EFCE; border: 0; text-align:right" id="total_material_amount_sum" value="" readonly>
                                            </td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000"></td>
                                            <td style="background-color:#C6EFCE; border-color:#000000;">
                                                <input type="text" style="width:100%; background-color:#C6EFCE; border: 0; text-align:right" id="total_labor_amount_sum" value="" readonly>
                                            </td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000"></td>
                                            <td style="background-color:#C6EFCE; border-color:#000000;">
                                                <input type="text" style="width:100%; background-color:#C6EFCE; border: 0; text-align:right" id="total_sum_amount_sum" value="" readonly>
                                            </td>
                                            <td bgcolor='#C6EFCE' style="border-color:#000000"></td>
                                        </tr>
                                    </thead>
                                </table>
                            </div>
                            <!-- ================= 실행내역서 요약 ================= -->
                            <div id=loading class="col-sm-12">
                                <button type="button" style="margin-left:10px; margin-bottom:5px;" class="btn btn-warning btn-sm pull-left" onclick="this.blur();plusUnit()">내역추가</button> 
                                <button type="button" style="margin-left:10px; margin-bottom:5px;" class="btn btn-warning btn-sm pull-left" onclick="this.blur();removeUnit()">내역삭제</button> 
                                <button type="button" style="margin-right:10px margin-bottom:5px;;" class="btn btn-primary btn-sm pull-right" onclick="this.blur();getFrame('form_managementReport','/management/managementreportaction');">저장</button>
                            </div>
                        </div>
                    </div>         
                </div>
            </div>
        </section>
    </form>
</div>

<div class="modal fade" id="modalU" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="z-index:1000000;">
    <div class="modal-dialog" style="vertical-align:baseline;width:auto;max-width:800px; ">
        <div class="modal-content" id="modalContents">
            <div id="modalBody">
                <div class="modal-body">
                    <div class="row" style="padding:4px;">
                        <input type="hidden" name="unitcost_currentPage" value="1"/>
                        <input type="hidden" name="unitcost_search_no" id="unitcost_search_no"/>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <input type="text" class="input-sm" id="unitcost_searchdata" name="unitcost_searchdata" placeholder="검색어" style="width:90%;"
                                    onkeydown="if ( event.keyCode == 13){ unitcost_search();}"
                                    onkeyup="unitcost_search();">
                                <button type="button" style="float:right;" class="btn btn-primary btn-sm" id="searchbutton" onclick="unitcost_search()">검색</button>
                            </div>
                        </div>

                        <div class="form-group" style="padding-top:50px;">
                            <div class="col-sm-12">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>CODE</th>
                                            <th>품명</th>
                                            <th>규격(1)</th>
                                            <th>규격(2)</th>
                                            <th>단위</th>
                                            <th>수량</th>
                                            <th>단가</th>
                                            <th>금액</th>
                                            <th>자재총소요량</th>
                                            <th>비고</th>
                                        </tr>
                                    </thead>
                                    <tbody id="unitcost_list">
                                        @if(isset($unitcost))
                                            @foreach($unitcost as $key => $val)
                                            <tr onclick="unitcost_ListCheck();" style="cursor:pointer;">
                                                <td>{{ $val->code ?? '' }}</td>                                          <!-- 0 -->
                                                <td>{{ $val->name ?? '' }}</td>                                          <!-- 1 -->
                                                <td>{{ $val->standard1 ?? '' }}</td>                                     <!-- 2 -->
                                                <td>{{ $val->standard2 ?? '' }}</td>                                     <!-- 3 -->
                                                <td>{{ $val->type ?? '' }}</td>                                          <!-- 4 -->
                                                <td>{{ $val->volume ?? '' }}</td>                                              <!-- 5 -->
                                                <td>{{ $val->price ?? '' }}</td>                                               <!-- 6 -->
                                                <td>{{ $val->amount ?? '' }}</td>                                              <!-- 7 -->
                                                <td>{{ $val->material ?? '' }}</td>                                            <!-- 8 -->
                                                <td>{{ $val->etc ?? '' }}</td>                                           <!-- 9 -->
                                            </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <div id="unitcost_list" style=""></div>
                                <div id="unitcost_pageApi" style="text-align:center"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left btn-mm" data-dismiss="modal">닫기</button>
                    </div>
                </div>
            </div>
        </div>
	</div>
</div>

<div class="modal fade" id="modalS" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="z-index:1000000;">
    <div class="modal-dialog" style="vertical-align:baseline;width:auto;max-width:800px; ">
        <div class="modal-content" id="modalContents">
            <div id="modalBody">
                <div class="modal-body">
                    <div class="row" style="padding:4px;">
                        <input type="hidden" name="currentPage" value="1"/>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <input type="hidden" name="search_no" id="search_no"/>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <input type="text" class="input-sm" id="searchdata" name="searchdata" placeholder="검색어" style="width:90%;"
                                    onkeydown="if ( event.keyCode == 13){ search();}"
                                    onkeyup="search();">
                                <button type="button" style="float:right;" class="btn btn-primary btn-sm" id="searchbutton" onclick="search()">검색</button>
                            </div>
                        </div>

                        <div class="form-group" style="padding-top:50px;">
                            <div class="col-sm-12">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>CODE</th>
                                            <th>품명</th>
                                            <th>규격(1)</th>
                                            <th>규격(2)</th>
                                            <th>단위</th>
                                            <th>수량</th>
                                            <th>단가</th>
                                            <th>금액</th>
                                            <th>자재총소요량</th>
                                            <th>비고</th>
                                        </tr>
                                    </thead>
                                    <tbody id="list">
                                        @if(isset($unitcost))
                                            @foreach($unitcost as $key => $val)
                                            <tr onclick="receiverListCheck();" style="cursor:pointer;">
                                                <td>{{ $val->code ?? '' }}</td>                                          <!-- 0 -->
                                                <td>{{ $val->name ?? '' }}</td>                                          <!-- 1 -->
                                                <td>{{ $val->standard1 ?? '' }}</td>                                     <!-- 2 -->
                                                <td>{{ $val->standard2 ?? '' }}</td>                                     <!-- 3 -->
                                                <td>{{ $val->type ?? '' }}</td>                                          <!-- 4 -->
                                                <td>{{ $val->volume ?? '' }}</td>                                              <!-- 5 -->
                                                <td>{{ $val->price ?? '' }}</td>                                               <!-- 6 -->
                                                <td>{{ $val->amount ?? '' }}</td>                                              <!-- 7 -->
                                                <td>{{ $val->material ?? '' }}</td>                                            <!-- 8 -->
                                                <td>{{ $val->etc ?? '' }}</td>                                           <!-- 9 -->
                                            </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <div id="list" style=""></div>
                                <div id="pageApi" style="text-align:center"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left btn-mm" data-dismiss="modal">닫기</button>
                    </div>
                </div>
            </div>
        </div>
	</div>
</div>

<div class="modal fade" id="modalC" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" style="z-index:1000000;">
    <div class="modal-dialog" style="vertical-align:baseline;width:auto;max-width:800px; ">
        <div class="modal-content" id="modalContents">
            <div id="modalBody">
                <div class="modal-body">
                    <div class="row" style="padding:4px;">
                        <input type="hidden" name="currentPage_plus" value="1"/>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                        <input type="hidden" name="search_no_plus" id="search_no_plus"/>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <input type="text" class="input-sm" id="searchdata_plus" name="searchdata_plus" placeholder="검색어" style="width:90%;"
                                    onkeydown="if ( event.keyCode == 13){ search_plus();}"
                                    onkeyup="search_plus();">
                                <button type="button" style="float:right;" class="btn btn-primary btn-sm" id="searchbutton" onclick="search_plus()">검색</button>
                            </div>
                        </div>

                        <div class="form-group" style="padding-top:50px;">
                            <div class="col-sm-12">
                                <table class="table table-hover table-striped">
                                    <thead>
                                        <tr>
                                            <th>CODE</th>
                                            <th>품명</th>
                                            <th>규격(1)</th>
                                            <th>규격(2)</th>
                                            <th>단위</th>
                                            <th>수량</th>
                                            <th>단가</th>
                                            <th>금액</th>
                                            <th>자재총소요량</th>
                                            <th>비고</th>
                                        </tr>
                                    </thead>
                                    <tbody id="list_plus">
                                        @if(isset($unitcost))
                                            @foreach($unitcost as $key => $val)
                                            <tr onclick="plusListCheck();" style="cursor:pointer;">
                                                <td>{{ $val->code ?? '' }}</td>                                          <!-- 0 -->
                                                <td>{{ $val->name ?? '' }}</td>                                          <!-- 1 -->
                                                <td>{{ $val->standard1 ?? '' }}</td>                                     <!-- 2 -->
                                                <td>{{ $val->standard2 ?? '' }}</td>                                     <!-- 3 -->
                                                <td>{{ $val->type ?? '' }}</td>                                          <!-- 4 -->
                                                <td>{{ $val->volume ?? '' }}</td>                                              <!-- 5 -->
                                                <td>{{ $val->price ?? '' }}</td>                                               <!-- 6 -->
                                                <td>{{ $val->amount ?? '' }}</td>                                              <!-- 7 -->
                                                <td>{{ $val->material ?? '' }}</td>                                            <!-- 8 -->
                                                <td>{{ $val->etc ?? '' }}</td>                                           <!-- 9 -->
                                            </tr>
                                            @endforeach
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="col-sm-12">
                                <div id="list_plus" style=""></div>
                                <div id="pageApi_plus" style="text-align:center"></div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left btn-mm" data-dismiss="modal">닫기</button>
                    </div>
                </div>
            </div>
        </div>
	</div>
</div>

<div class="modal fade" id="modalA" style="padding-left:17px;">
	<div class="modal-dialog" id="modal-info" style="margin-top:50px; width: 1100px;">
        <div class="modal-content" id="modalContents">
            <div id="modalBody">
                <div class="modal-body">
                    <div class="row">
                        <form style="margin-top:40px;"class="form-horizontal">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                            <input type="hidden" id="code_no"/>
                            <input type="hidden" id="code_code1" name="code_code1" value="">
                            <input type="hidden" id="code_code2" name="code_code2" value="">
                            <input type="hidden" id="code_code3" name="code_code3" value="">
                            <input type="hidden" id="code_code4" name="code_code4" value="">
                            <input type="hidden" id="code_code5" name="code_code5" value="">
                            <input type="hidden" id="code_code6" name="code_code6" value="">
                            <input type="hidden" id="code_code7" name="code_code7" value="">
                            <input type="hidden" id="code_code8" name="code_code8" value="">
                            <input type="hidden" id="code_code9" name="code_code9" value="">
                            <input type="hidden" id="code_code10" name="code_code10" value="">

                            <div class="col-md-12">
                                <table border='1' class='table table-bordered'>
                                    <thead>
                                        <tr align='center'>
                                            <td colspan=12 bgcolor='#C6EFCE'>코드계산기</td>
                                        </tr>
                                        <tr align='center'>
                                            <td style="border-bottom-color: #000000;"></td>
                                            <td style="border-bottom-color: #000000;">1</td>
                                            <td style="border-bottom-color: #000000;">2</td>
                                            <td style="border-bottom-color: #000000;">3</td>
                                            <td style="border-bottom-color: #000000;">4</td>
                                            <td style="border-bottom-color: #000000;">5</td>
                                            <td style="border-bottom-color: #000000;">6</td>
                                            <td style="border-bottom-color: #000000;">7</td>
                                            <td style="border-bottom-color: #000000;">8</td>
                                            <td style="border-bottom-color: #000000;">9</td>
                                            <td style="border-bottom-color: #000000;">10</td>
                                            <td style="border-bottom-color: #000000;">합계</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr align='center'>
                                            <td style="width:15%; border-color:#000000;">코드명</td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name1" onclick="code_search(1);" readonly>
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name2" onclick="code_search(2);" readonly> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name3" onclick="code_search(3);" readonly>
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name4" onclick="code_search(4);" readonly>
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name5" onclick="code_search(5);" readonly> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name6" onclick="code_search(6);" readonly>
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name7" onclick="code_search(7);" readonly> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name8" onclick="code_search(8);" readonly>
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name9" onclick="code_search(9);" readonly>
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name10" onclick="code_search(10);" readonly> 
                                            </td>
                                            <td rowspan=2 style="vertical-align:middle; border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_sum" readonly> 
                                            </td>
                                        </tr>
                                        <tr align='center'>
                                            <td style="width:15%; border-color:#000000;">자재비단가</td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money1"> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money2"> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money3"> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money4">
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money5">
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money6"> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money7"> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money8"> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money9">
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money10">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left btn-sm" data-dismiss="modal">닫기</button>
                        <button type="button" class="btn btn-primary btn-sm" id="nodeForceBtn" onclick="codeEnter()">등록</button>
                    </div>
                </div>
            </div>
        </div>
	</div>
</div>

<div class="modal fade" id="modalP" style="padding-left:17px;">
	<div class="modal-dialog" id="modal-info" style="margin-top:50px; width: 1100px;">
        <div class="modal-content" id="modalContents">
            <div id="modalBody">
                <div class="modal-body">
                    <div class="row">
                        <form style="margin-top:40px;"class="form-horizontal">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}" />
                            <input type="hidden" id="code_no_plus"/>
                            <input type="hidden" id="code_plus1" name="code_plus1" value="">
                            <input type="hidden" id="code_plus2" name="code_plus2" value="">
                            <input type="hidden" id="code_plus3" name="code_plus3" value="">
                            <input type="hidden" id="code_plus4" name="code_plus4" value="">
                            <input type="hidden" id="code_plus5" name="code_plus5" value="">
                            <input type="hidden" id="code_plus6" name="code_plus6" value="">
                            <input type="hidden" id="code_plus7" name="code_plus7" value="">
                            <input type="hidden" id="code_plus8" name="code_plus8" value="">
                            <input type="hidden" id="code_plus9" name="code_plus9" value="">
                            <input type="hidden" id="code_plus10" name="code_plus10" value="">

                            <div class="col-md-12">
                                <table border='1' class='table table-bordered'>
                                    <thead>
                                        <tr align='center'>
                                            <td colspan=12 bgcolor='#C6EFCE'>코드계산기</td>
                                        </tr>
                                        <tr align='center'>
                                            <td style="border-bottom-color: #000000;"></td>
                                            <td style="border-bottom-color: #000000;">1</td>
                                            <td style="border-bottom-color: #000000;">2</td>
                                            <td style="border-bottom-color: #000000;">3</td>
                                            <td style="border-bottom-color: #000000;">4</td>
                                            <td style="border-bottom-color: #000000;">5</td>
                                            <td style="border-bottom-color: #000000;">6</td>
                                            <td style="border-bottom-color: #000000;">7</td>
                                            <td style="border-bottom-color: #000000;">8</td>
                                            <td style="border-bottom-color: #000000;">9</td>
                                            <td style="border-bottom-color: #000000;">10</td>
                                            <td style="border-bottom-color: #000000;">합계</td>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr align='center'>
                                            <td style="width:15%; border-color:#000000;">코드명</td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name1_plus" onclick="code_search_plus(1);" readonly>
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name2_plus" onclick="code_search_plus(2);" readonly> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name3_plus" onclick="code_search_plus(3);" readonly>
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name4_plus" onclick="code_search_plus(4);" readonly>
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name5_plus" onclick="code_search_plus(5);" readonly> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name6_plus" onclick="code_search_plus(6);" readonly>
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name7_plus" onclick="code_search_plus(7);" readonly> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name8_plus" onclick="code_search_plus(8);" readonly>
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name9_plus" onclick="code_search_plus(9);" readonly>
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_name10_plus" onclick="code_search_plus(10);" readonly> 
                                            </td>
                                            <td rowspan=2 style="vertical-align:middle; border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_sum_plus" readonly> 
                                            </td>
                                        </tr>
                                        <tr align='center'>
                                            <td style="width:15%; border-color:#000000;">자재비단가</td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money1_plus"> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money2_plus"> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money3_plus"> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money4_plus">
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money5_plus">
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money6_plus"> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money7_plus"> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money8_plus"> 
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money9_plus">
                                            </td>
                                            <td style="border-color:#000000">
                                                <input type="text" style="width:100%; border: 0; text-align:left" id="code_money10_plus">
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                        </form>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-default pull-left btn-sm" data-dismiss="modal">닫기</button>
                        <button type="button" class="btn btn-primary btn-sm" id="nodeForceBtn" onclick="codeEnter_plus()">등록</button>
                    </div>
                </div>
            </div>
        </div>
	</div>
</div>

<script>
    
pageMake('{{ $cnt ?? 0 }}');
unitcost_pageMake('{{ $cnt ?? 0 }}');
pageMake_plus('{{ $cnt ?? 0 }}');

var total_val = 0;
var total_key = 0;
var total_num = 0;

$("#fileInput").on('change', function(){  // 값이 변경되면
    if(window.FileReader)
    {  // modern browser
        var filename = $(this)[0].files[0].name;
    }
    else
    {  // old IE
        var filename = $(this).val().split('/').pop().split('\\').pop();  // 파일명만 추출
    }
    // 추출한 파일명 삽입
    $("#userfile").val(filename);
});

function commaInput(num)
{
	num = String(num);
    var parts = num.toString().split("."); 
	parts[0] = parts[0].replace(/,/g, "");
	parts[0] = parts[0].replace(/(\d)(?=(?:\d{3})+(?!\d))/g, '$1,');
    var number = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",") + (parts[1] ? "." + parts[1] : "");
	return number;
}

// 일위대가 산출 추가
function plusUnit()
{
    if($("input[name=num]")){
        total_num = $("input[name=num]").length;
    }

    var text = "<tr align='center'><input type='hidden' name='num'>";
    text += "<input type='hidden' name='no"+total_num+"' value=''>";
    text += "<input type='hidden' id='plus_code1_{{$key}}' name='plus_code1_{{$key}}' value=''>";
    text += "<input type='hidden' id='plus_code2_{{$key}}' name='plus_code2_{{$key}}' value=''>";
    text += "<input type='hidden' id='plus_code3_{{$key}}' name='plus_code3_{{$key}}' value=''>";
    text += "<input type='hidden' id='plus_code4_{{$key}}' name='plus_code4_{{$key}}' value=''>";
    text += "<input type='hidden' id='plus_code5_{{$key}}' name='plus_code5_{{$key}}' value=''>";
    text += "<input type='hidden' id='plus_code6_{{$key}}' name='plus_code6_{{$key}}' value=''>";
    text += "<input type='hidden' id='plus_code7_{{$key}}' name='plus_code7_{{$key}}' value=''>";
    text += "<input type='hidden' id='plus_code8_{{$key}}' name='plus_code8_{{$key}}' value=''>";
    text += "<input type='hidden' id='plus_code9_{{$key}}' name='plus_code9_{{$key}}' value=''>";
    text += "<input type='hidden' id='plus_code10_{{$key}}' name='plus_code10_{{$key}}' value=''>";
    text += "<input type='hidden' id='plus_code"+total_num+"' name='plus_code"+total_num+"' value=''>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:left' id='plus_name"+total_num+"' name='plus_name"+total_num+"' value=''></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:left' id='plus_standard"+total_num+"' name='plus_standard"+total_num+"' value='' ></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:center' id='plus_type"+total_num+"' name='plus_type"+total_num+"' value='' ></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='plus_volume"+total_num+"' name='plus_volume"+total_num+"' value='' onkeyup='unitCheck("+total_num+");'></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='plus_material_price"+total_num+"' name='plus_material_price"+total_num+"' value='' onclick='code_sum_open_plus("+total_num+");' readonly></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='plus_material_amount"+total_num+"' name='plus_material_amount"+total_num+"' value='' readonly></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='plus_labor_price"+total_num+"' name='plus_labor_price"+total_num+"' value='' onkeyup='unitCheck("+total_num+");'></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='plus_labor_amount"+total_num+"' name='plus_labor_amount"+total_num+"' value='' readonly></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='plus_sum_price"+total_num+"' name='plus_sum_price"+total_num+"' value='' readonly></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='plus_sum_amount"+total_num+"' name='plus_sum_amount"+total_num+"' value='' readonly></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:center' id='plus_etc"+total_num+"' name='plus_etc"+total_num+"' value=''></td></tr>";
    $('#UnitPlus:last').append(text);
}

// 일위대가 산출 제거
function removeUnit()
{
    total_val = $("input[name=num]").length-1;

    $('#UnitPlus >tr:last').remove();
}

// 내역서 산출 추가
function plusCode()
{
    if($("input[name=val]")){
        total_val = $("input[name=val]").length;
    }

    var text = "<tr align='center'><input type='hidden' name='val'>";
    text += "<input type='hidden' name='no"+total_val+"' value=''>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:left' id='name"+total_val+"' name='name"+total_val+"' value=''></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:left' id='standard"+total_val+"' name='standard"+total_val+"' value=''></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:center' id='type"+total_val+"' name='type"+total_val+"' value=''></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='volume"+total_val+"' name='volume"+total_val+"' value='' onkeyup='countCheck("+total_val+");'></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='material_price"+total_val+"' name='material_price"+total_val+"' value='' onkeyup='countCheck("+total_val+");'></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='material_amount"+total_val+"' name='material_amount"+total_val+"' value='' readonly></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='labor_price"+total_val+"' name='labor_price"+total_val+"' value='' onkeyup='countCheck("+total_val+");'></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='labor_amount"+total_val+"' name='labor_amount"+total_val+"' value='' readonly></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='sum_price"+total_val+"' name='sum_price"+total_val+"' value='' readonly></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='sum_amount"+total_val+"' name='sum_amount"+total_val+"' value='' readonly></td>";
    text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:center' id='etc"+total_val+"' name='etc"+total_val+"' value=''></td></tr>";
    $('#tbodyCheck:last').append(text);
}

// 내역서 산출 제거
function removeCode()
{
    total_val = $("input[name=val]").length-1;

    $('#tbodyCheck >tr:last').remove();
}

function getFrame(id, url)
{
    if(url=="")
    {
        var url = $("#"+id).attr("action");
    }

    var val = 0;
    var key = 0;
    var num = 0;
    if($("input[name=val]")){
        val = $("input[name=val]").length;
    }
    if($("input[name=key]")){
        key = $("input[name=key]").length;
    }
    if($("input[name=num]")){
        num = $("input[name=num]").length;
    }

    document.getElementById('total').value = val;
    document.getElementById('sub_total').value = key;
    document.getElementById('plus_total').value = num;

    var method = $("#"+id).attr("method"); 
    var postdata = $('#'+id).serialize();
    
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url: url,
        type: method,
        data: postdata,
        success : function(result){

            if(result["msg"] == "Y")
            {
                alert("정상적으로 처리되었습니다.");

                $('#managementInfo').removeClass('active');
                $('#managementReport').addClass('active');
                getInfo("/management/managementReport", "conLeftTop");
            }
            else
            {
                alert(result["msg"]);
            }
        },
        error : function(xhr) {
            // wconsole.log(xhr.responseText);
            alert("통신오류입니다. 관리자에게 문의해주세요.");
        }
    });
}

// 수량입력하면 자동계산
function countCheck(num)
{    
    if($("input[name=val]")){
        total_val = $("input[name=val]").length;
    }

    if(document.getElementById('volume'+num).value && document.getElementById('material_price'+num).value){
        document.getElementById('material_amount'+num).value = commaInput(document.getElementById('volume'+num).value.replace(/,/g, "") * document.getElementById('material_price'+num).value.replace(/,/g, ""));
    }
    
    if(document.getElementById('volume'+num).value && document.getElementById('labor_price'+num).value){
        document.getElementById('labor_amount'+num).value = commaInput(document.getElementById('volume'+num).value.replace(/,/g, "") * document.getElementById('labor_price'+num).value.replace(/,/g, ""));
    }
    
    if(document.getElementById('material_price'+num).value && document.getElementById('labor_price'+num).value){
        document.getElementById('sum_price'+num).value = commaInput(+(Number(document.getElementById('material_price'+num).value.replace(/,/g, "")) + Number(document.getElementById('labor_price'+num).value.replace(/,/g, ""))).toFixed(3));
    } else if(document.getElementById('material_price'+num).value){
        document.getElementById('sum_price'+num).value = commaInput(document.getElementById('material_price'+num).value.replace(/,/g, ""));
    } else if(document.getElementById('labor_price'+num).value){
        document.getElementById('sum_price'+num).value = commaInput(document.getElementById('labor_price'+num).value.replace(/,/g, ""));
    }
    
    if(document.getElementById('volume'+num).value && document.getElementById('sum_price'+num).value){
        document.getElementById('sum_amount'+num).value = commaInput(document.getElementById('volume'+num).value.replace(/,/g, "") * document.getElementById('sum_price'+num).value.replace(/,/g, ""));
    }

    if(document.getElementById('volume'+num).value == ''){
        document.getElementById('labor_amount'+num).value = 0;
        document.getElementById('material_amount'+num).value = 0;
        document.getElementById('sum_amount'+num).value = 0;
    }
    if(document.getElementById('material_price'+num).value == ''){
        document.getElementById('material_amount'+num).value = 0;
    }
    if(document.getElementById('labor_price'+num).value == ''){
        document.getElementById('labor_amount'+num).value = 0;
    }
    if(document.getElementById('sum_price'+num).value == ''){
        document.getElementById('sum_amount'+num).value = 0;
    }

    if(total_val > 0){
        var summary = 0;
        for (var i = 0; i < total_val; i++) {
            if(document.getElementById('material_amount'+i).value){
                summary += parseInt(document.getElementById('material_amount'+i).value.replace(/,/g, ""));
            }
        }
        document.getElementById('material_amount_sum').value = commaInput(summary);

        if(document.getElementById('sub_material_amount_sum').value){
            document.getElementById('total_material_amount_sum').value = commaInput(parseInt(document.getElementById('material_amount_sum').value.replace(/,/g, "")) + parseInt(document.getElementById('sub_material_amount_sum').value.replace(/,/g, "")));
        } else {
            document.getElementById('total_material_amount_sum').value = commaInput(document.getElementById('material_amount_sum').value.replace(/,/g, ""));
        }
    }

    if(total_val > 0){
        var summary = 0;
        for (var i = 0; i < total_val; i++) {
            if(document.getElementById('labor_amount'+i).value){
                summary += parseInt(document.getElementById('labor_amount'+i).value.replace(/,/g, ""));
            }
        }
        document.getElementById('labor_amount_sum').value = commaInput(summary);

        if(document.getElementById('sub_labor_amount_sum').value){
            document.getElementById('total_labor_amount_sum').value = commaInput(parseInt(document.getElementById('labor_amount_sum').value.replace(/,/g, "")) + parseInt(document.getElementById('sub_labor_amount_sum').value.replace(/,/g, "")));
        } else {
            document.getElementById('total_labor_amount_sum').value = commaInput(document.getElementById('labor_amount_sum').value.replace(/,/g, ""));
        }
    }

    if(total_val > 0){
        var summary = 0;
        for (var i = 0; i < total_val; i++) {
            if(document.getElementById('sum_amount'+i).value){
                summary += parseInt(document.getElementById('sum_amount'+i).value.replace(/,/g, ""));
            }
        }
        document.getElementById('sum_amount_sum').value = commaInput(summary);

        if(document.getElementById('sub_sum_amount_sum').value){
            document.getElementById('total_sum_amount_sum').value = commaInput(parseInt(document.getElementById('sum_amount_sum').value.replace(/,/g, "")) + parseInt(document.getElementById('sub_sum_amount_sum').value.replace(/,/g, "")));
        } else {
            document.getElementById('total_sum_amount_sum').value = commaInput(document.getElementById('sum_amount_sum').value.replace(/,/g, ""));
        }
    }
}

// 엑셀 업로드한거 입력하면 자동계산
function subcountCheck(num)
{    
    if($("input[name=key]")){
        total_key = $("input[name=key]").length;
    }

    if(document.getElementById('sub_volume'+num).value && document.getElementById('sub_material_price'+num).value){
        document.getElementById('sub_material_amount'+num).value = commaInput(document.getElementById('sub_volume'+num).value.replace(/,/g, "") * document.getElementById('sub_material_price'+num).value.replace(/,/g, ""));
    }
    
    if(document.getElementById('sub_volume'+num).value && document.getElementById('sub_labor_price'+num).value){
        document.getElementById('sub_labor_amount'+num).value = commaInput(document.getElementById('sub_volume'+num).value.replace(/,/g, "") * document.getElementById('sub_labor_price'+num).value.replace(/,/g, ""));
    }

    if(document.getElementById('sub_material_price'+num).value && document.getElementById('sub_labor_price'+num).value){
        document.getElementById('sub_sum_price'+num).value = commaInput(+(Number(document.getElementById('sub_material_price'+num).value.replace(/,/g, "")) + Number(document.getElementById('sub_labor_price'+num).value.replace(/,/g, ""))).toFixed(3));
    } else if(document.getElementById('sub_material_price'+num).value){
        document.getElementById('sub_sum_price'+num).value = commaInput(document.getElementById('sub_material_price'+num).value.replace(/,/g, ""));
    } else if(document.getElementById('sub_labor_price'+num).value){
        document.getElementById('sub_sum_price'+num).value = commaInput(document.getElementById('sub_labor_price'+num).value.replace(/,/g, ""));
    }
    
    if(document.getElementById('sub_volume'+num).value && document.getElementById('sub_sum_price'+num).value){
        document.getElementById('sub_sum_amount'+num).value = commaInput(document.getElementById('sub_volume'+num).value.replace(/,/g, "") * document.getElementById('sub_sum_price'+num).value.replace(/,/g, ""));
    }

    if(document.getElementById('sub_volume'+num).value == ''){
        document.getElementById('sub_labor_amount'+num).value = 0;
        document.getElementById('sub_material_amount'+num).value = 0;
        document.getElementById('sub_sum_amount'+num).value = 0;
    }
    if(document.getElementById('sub_material_price'+num).value == ''){
        document.getElementById('sub_material_amount'+num).value = 0;
    }
    if(document.getElementById('sub_labor_price'+num).value == ''){
        document.getElementById('sub_labor_amount'+num).value = 0;
    }
    if(document.getElementById('sub_sum_price'+num).value == ''){
        document.getElementById('sub_sum_amount'+num).value = 0;
    }

    if(total_key > 0){
        var summary = 0;
        for (var i = 0; i < total_key; i++) {
            if(document.getElementById('sub_material_amount'+i).value){
                summary += parseInt(document.getElementById('sub_material_amount'+i).value.replace(/,/g, ""));
            }
        }
        document.getElementById('sub_material_amount_sum').value = commaInput(summary);

        if(document.getElementById('material_amount_sum').value){
            document.getElementById('total_material_amount_sum').value = commaInput(parseInt(document.getElementById('material_amount_sum').value.replace(/,/g, "")) + parseInt(document.getElementById('sub_material_amount_sum').value.replace(/,/g, "")));
        } else {
            document.getElementById('total_material_amount_sum').value = commaInput(document.getElementById('sub_material_amount_sum').value.replace(/,/g, ""));
        }
    }

    if(total_key > 0){
        var summary = 0;
        for (var i = 0; i < total_key; i++) {
            if(document.getElementById('sub_labor_amount'+i).value){
                summary += parseInt(document.getElementById('sub_labor_amount'+i).value.replace(/,/g, ""));
            }
        }
        document.getElementById('sub_labor_amount_sum').value = commaInput(summary);

        if(document.getElementById('labor_amount_sum').value){
            document.getElementById('total_labor_amount_sum').value = commaInput(parseInt(document.getElementById('labor_amount_sum').value.replace(/,/g, "")) + parseInt(document.getElementById('sub_labor_amount_sum').value.replace(/,/g, "")));
        } else {
            document.getElementById('total_labor_amount_sum').value = commaInput(document.getElementById('sub_labor_amount_sum').value.replace(/,/g, ""));
        }
    }

    if(total_key > 0){
        var summary = 0;
        for (var i = 0; i < total_key; i++) {
            if(document.getElementById('sub_sum_amount'+i).value){
                summary += parseInt(document.getElementById('sub_sum_amount'+i).value.replace(/,/g, ""));
            }
        }
        document.getElementById('sub_sum_amount_sum').value = commaInput(summary);

        if(document.getElementById('sum_amount_sum').value){
            document.getElementById('total_sum_amount_sum').value = commaInput(parseInt(document.getElementById('sum_amount_sum').value.replace(/,/g, "")) + parseInt(document.getElementById('sub_sum_amount_sum').value.replace(/,/g, "")));
        } else {
            document.getElementById('total_sum_amount_sum').value = commaInput(document.getElementById('sub_sum_amount_sum').value.replace(/,/g, ""));
        }
    }
}

//엑셀업로드
function excelfileUpload()
{
	$('#modalR').modal();
}

//엑셀업로드 액션
// function submit()
// {
//     if($("input[name=userfile]").val() == "")
//     {
//         alert("선택된 파일이 없습니다.");
//         return false;
//     }

//     var action = $("#form_managementFile").attr("action");
//     var method = $("#form_managementFile").attr("method");
//     var enctype = $("#form_managementFile").attr("enctype");
//     var postdata = new FormData($('#form_managementFile')[0]);

//     $.ajaxSetup({
//         headers: {
//         'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
//         }
//     });

//     $.ajax({
//         url: action,
//         type: method,
//         data: postdata,
//         enctype: enctype,
//         cache : false,
//         processData : false,
//         contentType : false,
//         success : function(result){
//             $("#modalR").modal('hide');
//             if(result){
//                 $.each(JSON.parse(result), function(key, value) {
//                     var text = "<tr align='center'><input type='hidden' name='key'>";
//                     text += "<input type='hidden' name='no"+key+"' value=''>"
//                     text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:left' id='sub_name"+key+"' name='sub_name"+key+"' value='"+value.name+"'></td>";
//                     text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:left' id='sub_standard"+key+"' name='sub_standard"+key+"' value='"+value.standard+"'></td>";
//                     text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:center' id='sub_type"+key+"' name='sub_type"+key+"' value='"+value.type+"'></td>";
//                     text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='sub_volume"+key+"' name='sub_volume"+key+"' value='"+commaInput(value.volume)+"' onkeyup='subcountCheck("+key+");'></td>";
//                     text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='sub_material_price"+key+"' name='sub_material_price"+key+"' value='' onclick='code_sum_open("+key+");' readonly></td>";
//                     text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='sub_material_amount"+key+"' name='sub_material_amount"+key+"' value='' readonly></td>";
//                     text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='sub_labor_price"+key+"' name='sub_labor_price"+key+"' value='' onkeyup='subcountCheck("+key+");'></td>";
//                     text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='sub_labor_amount"+key+"' name='sub_labor_amount"+key+"' value='' readonly></td>";
//                     text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='sub_sum_price"+key+"' name='sub_sum_price"+key+"' value='' readonly></td>";
//                     text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:right' id='sub_sum_amount"+key+"' name='sub_sum_amount"+key+"' value='' readonly></td>";
//                     text += "<td style='border-color:#000000'><input type='text' style='width:100%; border: 0; text-align:center' id='sub_etc"+key+"' name='sub_etc"+key+"' value=''></td>";
//                     $('#tableCheck:last').append(text);
//                 });
//             }
//         },
//         error : function(xhr) {
//             // console.log(xhr.responseText);
//             alert("통신오류입니다. 관리자에게 문의해주세요.");
//             globalCheck = false;
//         }
//     });
// }

//엑셀업로드 액션
function submit()
{
    if($("input[name=userfile]").val() == "")
    {
        alert("선택된 파일이 없습니다.");
        return false;
    }

    var action = $("#form_managementFile").attr("action");
    var method = $("#form_managementFile").attr("method");
    var enctype = $("#form_managementFile").attr("enctype");
    var postdata = new FormData($('#form_managementFile')[0]);

    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url: action,
        type: method,
        data: postdata,
        enctype: enctype,
        cache : false,
        processData : false,
        contentType : false,
        success : function(result){
            $("#modalR").modal('hide');
            if(result == "Y")
            {
                alert("정상적으로 처리되었습니다.");
                $('.modal-backdrop').remove();
                $('#managementInfo').removeClass('active');
                $('#managementReport').addClass('active');
                getInfo("/management/managementReport", "conLeftTop");
            }
            else
            {
                alert(result);
            }
        },
        error : function(xhr) {
            // console.log(xhr.responseText);
            alert("통신오류입니다. 관리자에게 문의해주세요.");
            globalCheck = false;
        }
    });
}

//코드검색창 생성(일위대가 금액)
function unitcost_open(no)
{
    document.getElementById('unitcost_search_no').value = no;
    document.getElementById('unitcost_searchdata').value = '';
    $('#unitcost_list').html('');
    $('#modalU').modal();
}

//코드검색창 생성(일위대가 단가)
function code_search(no)
{
    document.getElementById('search_no').value = no;
    document.getElementById('searchdata').value = '';
    $('#list').html('');
    $('#modalS').modal();
}

//코드검색창 생성(일위대가 단가)
function code_search_plus(no)
{
    document.getElementById('search_no_plus').value = no;
    document.getElementById('searchdata_plus').value = '';
    $('#list_plus').html('');
    $('#modalC').modal();
}

//코드계산기 생성
function code_sum_open(no)
{
    var management_no = document.getElementById('no').value;
    var sub_code1 = document.getElementById('sub_code1_'+no).value ?? '';
    var sub_code2 = document.getElementById('sub_code2_'+no).value ?? '';
    var sub_code3 = document.getElementById('sub_code3_'+no).value ?? '';
    var sub_code4 = document.getElementById('sub_code4_'+no).value ?? '';
    var sub_code5 = document.getElementById('sub_code5_'+no).value ?? '';
    var sub_code6 = document.getElementById('sub_code6_'+no).value ?? '';
    var sub_code7 = document.getElementById('sub_code7_'+no).value ?? '';
    var sub_code8 = document.getElementById('sub_code8_'+no).value ?? '';
    var sub_code9 = document.getElementById('sub_code9_'+no).value ?? '';
    var sub_code10 = document.getElementById('sub_code10_'+no).value ?? '';
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.post('/management/unitcodehistory', { code1:sub_code1, code2:sub_code2, code3:sub_code3, code4:sub_code4, code5:sub_code5, code6:sub_code6, code7:sub_code7, code8:sub_code8, code9:sub_code9, code10:sub_code10, management_no:management_no }, function(data) {        
        document.getElementById('code_name1').value = data.name1 ?? '';
        document.getElementById('code_name2').value = data.name2 ?? '';
        document.getElementById('code_name3').value = data.name3 ?? '';
        document.getElementById('code_name4').value = data.name4 ?? '';
        document.getElementById('code_name5').value = data.name5 ?? '';
        document.getElementById('code_name6').value = data.name6 ?? '';
        document.getElementById('code_name7').value = data.name7 ?? '';
        document.getElementById('code_name8').value = data.name8 ?? '';
        document.getElementById('code_name9').value = data.name9 ?? '';
        document.getElementById('code_name10').value = data.name10 ?? '';
        document.getElementById('code_money1').value = data.money1;
        document.getElementById('code_money2').value = data.money2;
        document.getElementById('code_money3').value = data.money3;
        document.getElementById('code_money4').value = data.money4;
        document.getElementById('code_money5').value = data.money5;
        document.getElementById('code_money6').value = data.money6;
        document.getElementById('code_money7').value = data.money7;
        document.getElementById('code_money8').value = data.money8;
        document.getElementById('code_money9').value = data.money9;
        document.getElementById('code_money10').value = data.money10;
        document.getElementById('code_sum').value = data.moneysum;
    });

    document.getElementById('code_no').value = no;
	$('#modalA').modal();
}

//코드계산기 생성
function code_sum_open_plus(no_plus)
{
    var management_no = document.getElementById('no').value;
    var plus_code1 = '';
    var plus_code2 = '';
    var plus_code3 = '';
    var plus_code4 = '';
    var plus_code5 = '';
    var plus_code6 = '';
    var plus_code7 = '';
    var plus_code8 = '';
    var plus_code9 = '';
    var plus_code10 = '';

    if(document.getElementById('plus_code1_'+no_plus).value){
        plus_code1 = document.getElementById('plus_code1_'+no_plus).value;
    }
    if(document.getElementById('plus_code2_'+no_plus).value){
        plus_code2 = document.getElementById('plus_code2_'+no_plus).value;
    }
    if(document.getElementById('plus_code3_'+no_plus).value){
        plus_code3 = document.getElementById('plus_code3_'+no_plus).value;
    }
    if(document.getElementById('plus_code4_'+no_plus).value){
        plus_code4 = document.getElementById('plus_code4_'+no_plus).value;
    }
    if(document.getElementById('plus_code5_'+no_plus).value){
        plus_code5 = document.getElementById('plus_code5_'+no_plus).value;
    }
    if(document.getElementById('plus_code6_'+no_plus).value){
        plus_code6 = document.getElementById('plus_code6_'+no_plus).value;
    }
    if(document.getElementById('plus_code7_'+no_plus).value){
        plus_code7 = document.getElementById('plus_code7_'+no_plus).value;
    }
    if(document.getElementById('plus_code8_'+no_plus).value){
        plus_code8 = document.getElementById('plus_code8_'+no_plus).value;
    }
    if(document.getElementById('plus_code9_'+no_plus).value){
        plus_code9 = document.getElementById('plus_code9_'+no_plus).value;
    }
    if(document.getElementById('plus_code10_'+no_plus).value){
        plus_code10 = document.getElementById('plus_code10_'+no_plus).value;
    }
    
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.post('/management/unitcodehistory', { code1:plus_code1, code2:plus_code2, code3:plus_code3, code4:plus_code4, code5:plus_code5, code6:plus_code6, code7:plus_code7, code8:plus_code8, code9:plus_code9, code10:plus_code10, management_no:management_no }, function(data) {
        document.getElementById('code_name1_plus').value = data.name1 ?? '';
        document.getElementById('code_name2_plus').value = data.name2 ?? '';
        document.getElementById('code_name3_plus').value = data.name3 ?? '';
        document.getElementById('code_name4_plus').value = data.name4 ?? '';
        document.getElementById('code_name5_plus').value = data.name5 ?? '';
        document.getElementById('code_name6_plus').value = data.name6 ?? '';
        document.getElementById('code_name7_plus').value = data.name7 ?? '';
        document.getElementById('code_name8_plus').value = data.name8 ?? '';
        document.getElementById('code_name9_plus').value = data.name9 ?? '';
        document.getElementById('code_name10_plus').value = data.name10 ?? '';
        document.getElementById('code_money1_plus').value = data.money1;
        document.getElementById('code_money2_plus').value = data.money2;
        document.getElementById('code_money3_plus').value = data.money3;
        document.getElementById('code_money4_plus').value = data.money4;
        document.getElementById('code_money5_plus').value = data.money5;
        document.getElementById('code_money6_plus').value = data.money6;
        document.getElementById('code_money7_plus').value = data.money7;
        document.getElementById('code_money8_plus').value = data.money8;
        document.getElementById('code_money9_plus').value = data.money9;
        document.getElementById('code_money10_plus').value = data.money10;
        
        document.getElementById('code_sum_plus').value = data.moneysum;
    });

    document.getElementById('code_no_plus').value = no_plus;
	$('#modalP').modal();
}

// 일위대가단가 입력하면 자동계산
function unitCheck(num)
{    
    if($("input[name=key]")){
        total_key = $("input[name=key]").length;
    }

    if($("input[name=num]")){
        total_num = $("input[name=num]").length;
    }

    if(document.getElementById('plus_volume'+num).value && document.getElementById('plus_material_price'+num).value){
        document.getElementById('plus_material_amount'+num).value = commaInput(document.getElementById('plus_volume'+num).value.replace(/,/g, "") * document.getElementById('plus_material_price'+num).value.replace(/,/g, ""));
    }
    
    if(document.getElementById('plus_volume'+num).value && document.getElementById('plus_labor_price'+num).value){
        document.getElementById('plus_labor_amount'+num).value = commaInput(document.getElementById('plus_volume'+num).value.replace(/,/g, "") * document.getElementById('plus_labor_price'+num).value.replace(/,/g, ""));
    }

    if(document.getElementById('plus_material_price'+num).value && document.getElementById('plus_labor_price'+num).value){
        document.getElementById('plus_sum_price'+num).value = commaInput(+(Number(document.getElementById('plus_material_price'+num).value.replace(/,/g, "")) + Number(document.getElementById('plus_labor_price'+num).value.replace(/,/g, ""))).toFixed(3));
    } else if(document.getElementById('plus_material_price'+num).value){
        document.getElementById('plus_sum_price'+num).value = commaInput(document.getElementById('plus_material_price'+num).value.replace(/,/g, ""));
    } else if(document.getElementById('plus_labor_price'+num).value){
        document.getElementById('plus_sum_price'+num).value = commaInput(document.getElementById('plus_labor_price'+num).value.replace(/,/g, ""));
    }
    
    if(document.getElementById('plus_volume'+num).value && document.getElementById('plus_sum_price'+num).value){
        document.getElementById('plus_sum_amount'+num).value = commaInput(document.getElementById('plus_volume'+num).value.replace(/,/g, "") * document.getElementById('plus_sum_price'+num).value.replace(/,/g, ""));
    }

    if(document.getElementById('plus_volume'+num).value == ''){
        document.getElementById('plus_labor_amount'+num).value = 0;
        document.getElementById('plus_material_amount'+num).value = 0;
        document.getElementById('plus_sum_amount'+num).value = 0;
    }
    if(document.getElementById('plus_material_price'+num).value == ''){
        document.getElementById('plus_material_amount'+num).value = 0;
    }
    if(document.getElementById('plus_labor_price'+num).value == ''){
        document.getElementById('plus_labor_amount'+num).value = 0;
    }
    if(document.getElementById('plus_sum_price'+num).value == ''){
        document.getElementById('plus_sum_amount'+num).value = 0;
    }

    if(total_num > 0){
        var summary = 0;
        for (var i = 0; i < total_num; i++) {
            if(document.getElementById('plus_material_amount'+i).value){
                summary += parseInt(document.getElementById('plus_material_amount'+i).value.replace(/,/g, ""));
            }
        }
        var sub_summary = 0;
        if(total_key > 0){
            for (var i = 0; i < total_key; i++) {
                if(document.getElementById('sub_material_amount'+i).value){
                    sub_summary += parseInt(document.getElementById('sub_material_amount'+i).value.replace(/,/g, ""));
                }
            }
        }
        document.getElementById('sub_material_amount_sum').value = commaInput(summary + sub_summary);

        if(document.getElementById('material_amount_sum').value){
            document.getElementById('total_material_amount_sum').value = commaInput(parseInt(document.getElementById('material_amount_sum').value.replace(/,/g, "")) + parseInt(document.getElementById('sub_material_amount_sum').value.replace(/,/g, "")));
        } else {
            document.getElementById('total_material_amount_sum').value = commaInput(document.getElementById('sub_material_amount_sum').value.replace(/,/g, ""));
        }
    }

    if(total_num > 0){
        var summary = 0;
        for (var i = 0; i < total_num; i++) {
            if(document.getElementById('plus_labor_amount'+i).value){
                summary += parseInt(document.getElementById('plus_labor_amount'+i).value.replace(/,/g, ""));
            }
        }
        var sub_summary = 0;
        if(total_key > 0){
            for (var i = 0; i < total_key; i++) {
                if(document.getElementById('sub_material_amount'+i).value){
                    sub_summary += parseInt(document.getElementById('sub_material_amount'+i).value.replace(/,/g, ""));
                }
            }
        }
        document.getElementById('sub_labor_amount_sum').value = commaInput(summary + sub_summary);

        if(document.getElementById('labor_amount_sum').value){
            document.getElementById('total_labor_amount_sum').value = commaInput(parseInt(document.getElementById('labor_amount_sum').value.replace(/,/g, "")) + parseInt(document.getElementById('sub_labor_amount_sum').value.replace(/,/g, "")));
        } else {
            document.getElementById('total_labor_amount_sum').value = commaInput(document.getElementById('sub_labor_amount_sum').value.replace(/,/g, ""));
        }
    }

    if(total_num > 0){
        var summary = 0;
        for (var i = 0; i < total_num; i++) {
            if(document.getElementById('plus_sum_amount'+i).value){
                summary += parseInt(document.getElementById('plus_sum_amount'+i).value.replace(/,/g, ""));
            }
        }
        var sub_summary = 0;
        if(total_key > 0){
            for (var i = 0; i < total_key; i++) {
                if(document.getElementById('sub_material_amount'+i).value){
                    sub_summary += parseInt(document.getElementById('sub_material_amount'+i).value.replace(/,/g, ""));
                }
            }
        }
        document.getElementById('sub_sum_amount_sum').value = commaInput(summary + sub_summary);

        if(document.getElementById('sum_amount_sum').value){
            document.getElementById('total_sum_amount_sum').value = commaInput(parseInt(document.getElementById('sum_amount_sum').value.replace(/,/g, "")) + parseInt(document.getElementById('sub_sum_amount_sum').value.replace(/,/g, "")));
        } else {
            document.getElementById('total_sum_amount_sum').value = commaInput(document.getElementById('sub_sum_amount_sum').value.replace(/,/g, ""));
        }
    }
}

// 코드계산기 수량입력되면 자동계산
function codeCheck(no)
{
    if(document.getElementById('code_money'+no).value){
        if(document.getElementById('code_sum').value){
            document.getElementById('code_sum').value = commaInput(+(Number(document.getElementById('code_sum').value.replace(/,/g, "")) + Number(document.getElementById('code_money'+no).value.replace(/,/g, ""))).toFixed(3));
        } else {
            document.getElementById('code_sum').value = commaInput(document.getElementById('code_money'+no).value.replace(/,/g, ""));
        }
    } else {
        if(document.getElementById('code_sum').value){
            document.getElementById('code_sum').value = commaInput(document.getElementById('code_sum').value.replace(/,/g, ""));
        } else {
            document.getElementById('code_sum').value = 0;
        }
    }
}

// 코드계산기 수량입력되면 자동계산
function plusCheck(no)
{
    if(document.getElementById('code_money'+no+'_plus').value){
        if(document.getElementById('code_sum_plus').value){
            document.getElementById('code_sum_plus').value = commaInput(+(Number(document.getElementById('code_sum_plus').value.replace(/,/g, "")) + Number(document.getElementById('code_money'+no+'_plus').value.replace(/,/g, ""))).toFixed(3));
        } else {
            document.getElementById('code_sum_plus').value = commaInput(document.getElementById('code_money'+no+'_plus').value.replace(/,/g, ""));
        }
    } else {
        if(document.getElementById('code_sum_plus').value){
            document.getElementById('code_sum_plus').value = commaInput(document.getElementById('code_sum_plus').value.replace(/,/g, ""));
        } else {
            document.getElementById('code_sum_plus').value = 0;
        }
    }
}

// 코드계산기 닫을때 합 가져오기
function codeEnter()
{
    var number = document.getElementById('code_no').value;
    if(document.getElementById('code_sum').value){
        document.getElementById('sub_code1_'+number).value = document.getElementById('code_code1').value;
        document.getElementById('sub_code2_'+number).value = document.getElementById('code_code2').value;
        document.getElementById('sub_code3_'+number).value = document.getElementById('code_code3').value;
        document.getElementById('sub_code4_'+number).value = document.getElementById('code_code4').value;
        document.getElementById('sub_code5_'+number).value = document.getElementById('code_code5').value;
        document.getElementById('sub_code6_'+number).value = document.getElementById('code_code6').value;
        document.getElementById('sub_code7_'+number).value = document.getElementById('code_code7').value;
        document.getElementById('sub_code8_'+number).value = document.getElementById('code_code8').value;
        document.getElementById('sub_code9_'+number).value = document.getElementById('code_code9').value;
        document.getElementById('sub_code10_'+number).value = document.getElementById('code_code10').value;
        document.getElementById('sub_material_price'+number).value = commaInput(document.getElementById('code_sum').value.replace(/,/g, ""));
        subcountCheck(number);
    }
    
    $("#modalA").modal('hide');
}

// 코드계산기 닫을때 합 가져오기
function codeEnter_plus()
{
    var number = document.getElementById('code_no_plus').value;
    if(document.getElementById('code_sum_plus').value){
        document.getElementById('plus_code1_'+number).value = document.getElementById('code_plus1').value;
        document.getElementById('plus_code2_'+number).value = document.getElementById('code_plus2').value;
        document.getElementById('plus_code3_'+number).value = document.getElementById('code_plus3').value;
        document.getElementById('plus_code4_'+number).value = document.getElementById('code_plus4').value;
        document.getElementById('plus_code5_'+number).value = document.getElementById('code_plus5').value;
        document.getElementById('plus_code6_'+number).value = document.getElementById('code_plus6').value;
        document.getElementById('plus_code7_'+number).value = document.getElementById('code_plus7').value;
        document.getElementById('plus_code8_'+number).value = document.getElementById('code_plus8').value;
        document.getElementById('plus_code9_'+number).value = document.getElementById('code_plus9').value;
        document.getElementById('plus_code10_'+number).value = document.getElementById('code_plus10').value;
        document.getElementById('plus_material_price'+number).value = commaInput(document.getElementById('code_sum_plus').value.replace(/,/g, ""));
        unitCheck(number);
    }
    
    $("#modalP").modal('hide');
}

function unitcost_pageMake(cnt)
{
    var total = cnt; // 총건수
	var pageNum = $('input[name=unitcost_currentPage]').val();// 현재페이지
	var pageStr = "";

    if(typeof total == "undefined")
    {
		$("#unitcost_pageApi").html("");
	}
    else
    {
        // $("#list").html(htmlStr);
        $("#unitcost_pageApi").html("");
		if(total > 1000)
        {
			total = 1000; //100페이지 까지만 가져오기
		}
		var pageBlock=10;
		var pageSize=10;
		var totalPages = Math.floor((total-1)/pageSize) + 1; // 총페이지
		var firstPage = Math.floor((pageNum-1)/pageBlock) * pageBlock + 1; // 리스트의 처음 ( (2-1)/10 ) * 10 + 1 // 1 11 21 31
		if(firstPage <= 0) firstPage = 1;	// 무조건 1
		var lastPage = firstPage-1 + pageBlock; // 리스트의 마지막 10 20 30 40 50
		if(lastPage > totalPages) lastPage = totalPages;	// 마지막페이지가 전체페이지보다 크면 전체페이지
		var nextPage = lastPage+1 ; // 11 21
		var prePage = firstPage-pageBlock ;

		if(firstPage > pageBlock)
        {
			pageStr +=  "<a class=\"btn btn-default\" href='javascript:unitcost_goPage("+prePage+");'>◁</a>  " ; // 처음 페이지가 아니면 <를 넣어줌
		}

		for(var i=firstPage; i<=lastPage; i++ )
        {
			if(pageNum == i)
				pageStr += "<a class=\"btn btn-info\" href='javascript:unitcost_goPage("+i+");'>" + i + "</a>  "; // 현재페이지 색넣어주기
			else
				pageStr += "<a class=\"btn btn-default\" href='javascript:unitcost_goPage("+i+");'>" + i + "</a>  ";
		}

		if(lastPage < totalPages)
        {
			pageStr +=  "<a class=\"btn btn-default\" href='javascript:unitcost_goPage("+nextPage+");'>▷</a>"; // 마지막페이지가 아니면 >를 넣어줌
		}
		$("#unitcost_pageApi").html(pageStr);
	}
}

function pageMake(cnt)
{
    var total = cnt; // 총건수
	var pageNum = $('input[name=currentPage]').val();// 현재페이지
	var pageStr = "";

    if(typeof total == "undefined")
    {
		$("#pageApi").html("");
	}
    else
    {
        // $("#list").html(htmlStr);
        $("#pageApi").html("");
		if(total > 1000)
        {
			total = 1000; //100페이지 까지만 가져오기
		}
		var pageBlock=10;
		var pageSize=10;
		var totalPages = Math.floor((total-1)/pageSize) + 1; // 총페이지
		var firstPage = Math.floor((pageNum-1)/pageBlock) * pageBlock + 1; // 리스트의 처음 ( (2-1)/10 ) * 10 + 1 // 1 11 21 31
		if(firstPage <= 0) firstPage = 1;	// 무조건 1
		var lastPage = firstPage-1 + pageBlock; // 리스트의 마지막 10 20 30 40 50
		if(lastPage > totalPages) lastPage = totalPages;	// 마지막페이지가 전체페이지보다 크면 전체페이지
		var nextPage = lastPage+1 ; // 11 21
		var prePage = firstPage-pageBlock ;

		if(firstPage > pageBlock)
        {
			pageStr +=  "<a class=\"btn btn-default\" href='javascript:goPage("+prePage+");'>◁</a>  " ; // 처음 페이지가 아니면 <를 넣어줌
		}

		for(var i=firstPage; i<=lastPage; i++ )
        {
			if(pageNum == i)
				pageStr += "<a class=\"btn btn-info\" href='javascript:goPage("+i+");'>" + i + "</a>  "; // 현재페이지 색넣어주기
			else
				pageStr += "<a class=\"btn btn-default\" href='javascript:goPage("+i+");'>" + i + "</a>  ";
		}

		if(lastPage < totalPages)
        {
			pageStr +=  "<a class=\"btn btn-default\" href='javascript:goPage("+nextPage+");'>▷</a>"; // 마지막페이지가 아니면 >를 넣어줌
		}
		$("#pageApi").html(pageStr);
	}
}

function pageMake_plus(cnt)
{
    var total = cnt; // 총건수
	var pageNum = $('input[name=currentPage_plus]').val();// 현재페이지
	var pageStr = "";

    if(typeof total == "undefined")
    {
		$("#pageApi_plus").html("");
	}
    else
    {
        // $("#list").html(htmlStr);
        $("#pageApi_plus").html("");
		if(total > 1000)
        {
			total = 1000; //100페이지 까지만 가져오기
		}
		var pageBlock=10;
		var pageSize=10;
		var totalPages = Math.floor((total-1)/pageSize) + 1; // 총페이지
		var firstPage = Math.floor((pageNum-1)/pageBlock) * pageBlock + 1; // 리스트의 처음 ( (2-1)/10 ) * 10 + 1 // 1 11 21 31
		if(firstPage <= 0) firstPage = 1;	// 무조건 1
		var lastPage = firstPage-1 + pageBlock; // 리스트의 마지막 10 20 30 40 50
		if(lastPage > totalPages) lastPage = totalPages;	// 마지막페이지가 전체페이지보다 크면 전체페이지
		var nextPage = lastPage+1 ; // 11 21
		var prePage = firstPage-pageBlock ;

		if(firstPage > pageBlock)
        {
			pageStr +=  "<a class=\"btn btn-default\" href='javascript:goPage_plus("+prePage+");'>◁</a>  " ; // 처음 페이지가 아니면 <를 넣어줌
		}

		for(var i=firstPage; i<=lastPage; i++ )
        {
			if(pageNum == i)
				pageStr += "<a class=\"btn btn-info\" href='javascript:goPage_plus("+i+");'>" + i + "</a>  "; // 현재페이지 색넣어주기
			else
				pageStr += "<a class=\"btn btn-default\" href='javascript:goPage_plus("+i+");'>" + i + "</a>  ";
		}

		if(lastPage < totalPages)
        {
			pageStr +=  "<a class=\"btn btn-default\" href='javascript:goPage_plus("+nextPage+");'>▷</a>"; // 마지막페이지가 아니면 >를 넣어줌
		}
		$("#pageApi_plus").html(pageStr);
	}
}

// 검색 결과 리스트 생성
function makeUnitcostList(data)
{
    if(data){
        var tr = '';
        for(var i=0; i<data.length; i++)
        {
            tr += '<tr onclick="unitcost_ListCheck();" style="cursor:pointer;">';
    
            var code = '';
            var name = '';
            var standard1 = '';
            var standard2 = '';
            var type = '';
            var volume = 0;
            var price = 0;
            var amount = 0;
            var material = 0;
            var etc = '';
            if(data[i].code != null) code = data[i].code;
            if(data[i].name != null) name = data[i].name;
            if(data[i].standard1 != null) standard1 = data[i].standard1;
            if(data[i].standard2 != null) standard2 = data[i].standard2;
            if(data[i].type != null) type = data[i].type;
            if(data[i].volume != null) volume = data[i].volume;
            if(data[i].price != null) price = data[i].price;
            if(data[i].amount != null) amount = data[i].amount;
            if(data[i].material != null) material = data[i].material;
            if(data[i].etc != null) etc = data[i].etc;
    
            var td = '<td>' + code + '</td>';
            td += '<td>' + name + '</td>';
            td += '<td>' + standard1 + '</td>';
            td += '<td>' + standard2 + '</td>';
            td += '<td>' + type + '</td>';
            td += '<td>' + commaInput(volume) + '</td>';
            td += '<td>' + commaInput(price) + '</td>';
            td += '<td>' + commaInput(amount) + '</td>';
            td += '<td>' + commaInput(material) + '</td>';
            td += '<td>' + etc + '</td>';
            td += '<td>';
            tr += td + '</tr>';
        }
    } else {
        var tr = '<tr><th> 정보가 없습니다. </th></tr>';
    }

    return tr;
}

// 검색 결과 리스트 생성
function makeList(data)
{
    if(data){
        var tr = '';
        for(var i=0; i<data.length; i++)
        {
            tr += '<tr onclick="receiverListCheck();" style="cursor:pointer;">';
    
            var code = '';
            var name = '';
            var standard1 = '';
            var standard2 = '';
            var type = '';
            var volume = 0;
            var price = 0;
            var amount = 0;
            var material = 0;
            var etc = '';
            if(data[i].code != null) code = data[i].code;
            if(data[i].name != null) name = data[i].name;
            if(data[i].standard1 != null) standard1 = data[i].standard1;
            if(data[i].standard2 != null) standard2 = data[i].standard2;
            if(data[i].type != null) type = data[i].type;
            if(data[i].volume != null) volume = data[i].volume;
            if(data[i].price != null) price = data[i].price;
            if(data[i].amount != null) amount = data[i].amount;
            if(data[i].material != null) material = data[i].material;
            if(data[i].etc != null) etc = data[i].etc;
    
            var td = '<td>' + code + '</td>';
            td += '<td>' + name + '</td>';
            td += '<td>' + standard1 + '</td>';
            td += '<td>' + standard2 + '</td>';
            td += '<td>' + type + '</td>';
            td += '<td>' + commaInput(volume) + '</td>';
            td += '<td>' + commaInput(price) + '</td>';
            td += '<td>' + commaInput(amount) + '</td>';
            td += '<td>' + commaInput(material) + '</td>';
            td += '<td>' + etc + '</td>';
            td += '<td>';
            tr += td + '</tr>';
        }
    } else {
        var tr = '<tr><th> 정보가 없습니다. </th></tr>';
    }

    return tr;
}

// 검색 결과 리스트 생성
function makeList_plus(data)
{
    if(data){
        var tr = '';
        for(var i=0; i<data.length; i++)
        {
            tr += '<tr onclick="plusListCheck();" style="cursor:pointer;">';
    
            var code = '';
            var name = '';
            var standard1 = '';
            var standard2 = '';
            var type = '';
            var volume = 0;
            var price = 0;
            var amount = 0;
            var material = 0;
            var etc = '';
            if(data[i].code != null) code = data[i].code;
            if(data[i].name != null) name = data[i].name;
            if(data[i].standard1 != null) standard1 = data[i].standard1;
            if(data[i].standard2 != null) standard2 = data[i].standard2;
            if(data[i].type != null) type = data[i].type;
            if(data[i].volume != null) volume = data[i].volume;
            if(data[i].price != null) price = data[i].price;
            if(data[i].amount != null) amount = data[i].amount;
            if(data[i].material != null) material = data[i].material;
            if(data[i].etc != null) etc = data[i].etc;
    
            var td = '<td>' + code + '</td>';
            td += '<td>' + name + '</td>';
            td += '<td>' + standard1 + '</td>';
            td += '<td>' + standard2 + '</td>';
            td += '<td>' + type + '</td>';
            td += '<td>' + commaInput(volume) + '</td>';
            td += '<td>' + commaInput(price) + '</td>';
            td += '<td>' + commaInput(amount) + '</td>';
            td += '<td>' + commaInput(material) + '</td>';
            td += '<td>' + etc + '</td>';
            td += '<td>';
            tr += td + '</tr>';
        }
    } else {
        var tr = '<tr><th> 정보가 없습니다. </th></tr>';
    }

    return tr;
}

// 일위대가검색
function unitcost_search()
{
    var unitcost_searchdata = $('#unitcost_searchdata').val();
    unitcost_searchdata = unitcost_searchdata.replace(/\s/gi, ""); //공백제거

    if(unitcost_searchdata != ''){
        var unitcost_search_no = document.getElementById('unitcost_search_no').value;
        var management_no = document.getElementById('no').value;
    
        // 인젝션 검사
        var unitcost_check = checkSearchedWord(unitcost_searchdata);
        if(unitcost_check)
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
    
            $.post('/management/managementsearch', { type:'search', keyword:unitcost_searchdata, no:unitcost_search_no, management_no:management_no, page : $('input[name=unitcost_currentPage]').val() }, function(data) {
    
                var htmlStr = makeUnitcostList(data.unitcost);
                $('#unitcost_list').html(htmlStr);
                $('#unitcost_pageApi').html('');
    
                unitcost_pageMake(data.cnt);
            });
        }
    }
}

// 검색
function search()
{
    var searchdata = $('#searchdata').val();
    searchdata = searchdata.replace(/\s/gi, ""); //공백제거

    if(searchdata != ''){
        var no = document.getElementById('search_no').value;
        var management_no = document.getElementById('no').value;
    
        // 인젝션 검사
        var check = checkSearchedWord(searchdata);
        if(check)
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
    
            $.post('/management/managementsearch', { type:'search', keyword:searchdata, no:no, management_no:management_no, page : $('input[name=currentPage]').val() }, function(data) {
    
                var htmlStr = makeList(data.unitcost);
                $('#list').html(htmlStr);
                $('#pageApi').html('');
    
                pageMake(data.cnt);
            });
        }
    }
}

// 검색
function search_plus()
{
    var searchdata = $('#searchdata_plus').val();
    searchdata = searchdata.replace(/\s/gi, ""); //공백제거

    if(searchdata != ''){
        var no = document.getElementById('search_no_plus').value;
        var management_no = document.getElementById('no').value;
    
        // 인젝션 검사
        var check = checkSearchedWord(searchdata);
        if(check)
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
    
            $.post('/management/managementsearch', { type:'search', keyword:searchdata, no:no, management_no:management_no, page : $('input[name=currentPage_plus]').val() }, function(data) {
    
                var htmlStr = makeList_plus(data.unitcost);
                $('#list_plus').html(htmlStr);
                $('#pageApi_plus').html('');
    
                pageMake_plus(data.cnt);
            });
        }
    }
}

// sql 인젝션 , 특수문자 검색 방지
function checkSearchedWord(obj)
{
    if (obj!=null && obj!="")
    {
        //특수문자 제거
        var expText = /[%=><+!^*]/;
        if (expText.test(obj) == true)
        {
            alert("특수문자를 입력 할수 없습니다.");
            $("#searchdata").val(obj.replace(expText, ""));
            $("#searchdata").focus();
            return false;
        }

        var sqlArray = new Array("AND", "OR", "SELECT", "INSERT", "DELETE", "UPDATE", "CREATE", "ALTER", "DROP", "EXEC", "UNION", "FETCH", "DECLARE", "TRUNCATE", "SHUTDOWN");

        for (var i = 0; i < sqlArray.length; i++)
        {
            if (obj.match(sqlArray[i]))
            {
                alert(sqlArray[i] + "와(과) 같은 특정문자로 검색할 수 없습니다.");
                $("#searchdata").val(obj.replace(sqlArray[i], ""));
                $("#searchdata").focus();
                return false;
            }
        }
    }
    return true;
}

//페이지 이동
function unitcost_goPage(pageNum)
{
	$('input[name=unitcost_currentPage]').val(pageNum);
	unitcost_search();
}

//페이지 이동
function goPage(pageNum)
{
	$('input[name=currentPage]').val(pageNum);
	search();
}

//페이지 이동
function goPage_plus(pageNum)
{
	$('input[name=currentPage_plus]').val(pageNum);
	search_plus();
}

// 일위대가 항목 선택
function unitcost_ListCheck()
{
    var obj = event.srcElement;
    var tr = getTrValues(obj.parentNode.children);
    var no = $("input[name=unitcost_search_no]").val();
    $('#plus_code'+no).val(tr[0]); // 일위대가코드
    $('#plus_name'+no).val(tr[1]); // 일위대가명
    $('#plus_standard'+no).val(tr[2]); // 일위대가규격
    $('#plus_type'+no).val(tr[4]); // 일위대가단위
    $('#plus_material_price'+no).val(tr[6]); // 일위대가단가
    unitCheck(no);
    $('#modalU').modal('hide');
}

// 항목 선택
function receiverListCheck()
{
    var obj = event.srcElement;
    var tr = getTrValues(obj.parentNode.children);
    var no = $("input[name=search_no]").val();
    $('#code_code'+no).val(tr[0]); // 일위대가코드
    $('#code_name'+no).val(tr[1]); // 일위대가명
    $('#code_money'+no).val(tr[6]); // 일위대가단가
    codeCheck(no);
    $('#modalS').modal('hide');
}

// 항목 선택
function plusListCheck()
{
    var obj = event.srcElement;
    var tr = getTrValues(obj.parentNode.children);
    var no = $("input[name=search_no_plus]").val();
    $('#code_plus'+no).val(tr[0]); // 일위대가코드
    $('#code_name'+no+'_plus').val(tr[1]); // 일위대가명
    $('#code_money'+no+'_plus').val(tr[6]); // 일위대가단가
    plusCheck(no);
    $('#modalC').modal('hide');
}

function getTrValues(tr)
{
    var array = new Array();
    for(var i = 0; i<tr.length; i++)
    {
        if(tr[i].firstChild)
        {
            array.push(tr[i].firstChild.nodeValue);
        }
        else
        {
            array.push('');
        }
    }
    return array;
}

function excelfileRemove(management_no)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.post('/management/managementpopupremove', { management_no: management_no}, function(data) {
        if(data == 'Y'){
            alert("정상적으로 처리되었습니다.");
            $('#managementInfo').removeClass('active');
            $('#managementReport').addClass('active');
            getInfo("/management/managementReport", "conLeftTop");
        } else {
            alert("삭제할 데이터가 없습니다.");
        }
    });
}

</script>
