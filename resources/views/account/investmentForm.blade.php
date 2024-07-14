@extends('layouts.masterPop')
@section('content')

<? $star = "<font class='text-red'>*</font>"; ?>

<script>
    window.onload = function()
    {
        
    }
</script>





<form class="form-horizontal" name="investment_form" id="investment_form">
<div class="card card-lightblue">

    <div class="card-header">
    <h2 class="card-title">계약 등록  </h2>
    </div>
    
    <div class="card-body mr-3 p-3">
        
        <div style="border: 3px solid gray; padding: 20px">
            <div class="form-group row">
                <label for="cust_search_string" class="col-sm-2 col-form-label">차입자 검색</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control form-control-sm" id="cust_search_string" placeholder="차입자번호, 차입자이름" value="" />
                </div>
                <div class="col-sm-6 text-left">
                    <button type="button" class="btn btn-sm btn-info mr-3" onclick="searchCustInfo();">차입자 검색</button>
                </div>
            </div>

            <div class="form-group row collapse" id="custSearch">
            <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10" id="custSearchResult">
                </div>
            </div>

            <div class="form-group row mt-2">
                <label for="cust_info_no" class="col-sm-2 col-form-label">{!! $star !!}차입자번호</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control form-control-sm" id="cust_info_no" name="cust_info_no" readonly placeholder="" value="@if(isset($result->cust_info_no)) {{ $result->cust_info_no }} @endif"/>
                </div>
                <div class="col-sm-4">
                </div>
            </div>
            <div class="form-group row">
                <label for="cust_bank_name" class="col-sm-2 col-form-label">차입자이름</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control form-control-sm" id="cust_bank_name" name="cust_bank_name" readonly placeholder="" data-target="#cust_bank_name" value="@if(isset($result->name)) {{ $result->name }} @endif"/>
                </div>
                <label for="cust_ssn" class="col-sm-2 col-form-label">주민/법인번호</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control form-control-sm" id="cust_ssn" readonly placeholder="주민/법인번호" data-target="#cust_ssn" value="@if(isset($result->ssn) && $result->ssn != '') {{ $result->ssn }} @endif"/>
                </div>
            </div>
            <div class="form-group row">
                <label for="cust_bank_cd" class="col-sm-2 col-form-label">{!! $star !!}차입자은행</label>
                <div class="col-sm-4">
                    <div class="row">
                        <div class="col-sm-12">
                            <select class="form-control form-control-sm" name="cust_bank_cd" id="cust_bank_cd" >
                                <option value=''>차입자은행</option>
                                    {{ Func::printOption($arrayConfig['bank_cd'], "") }}
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="cust_bank_ssn" class="col-sm-2 col-form-label">{!! $star !!}차입자계좌번호</label>
                <div class="col-sm-4">
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="hidden" id="handle_code" name="handle_code" value="">
                            <input type="text" class="form-control form-control-sm" id="cust_bank_ssn" name="cust_bank_ssn"  placeholder="송금계좌번호 입력" value=""/>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <br>

        <div style="border: 3px solid gray; padding: 20px">
            <div class="form-group row">
                <label for="usr_search_string" class="col-sm-2 col-form-label">투자자 검색</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control form-control-sm" id="usr_search_string" placeholder="투자자번호, 투자자이름" value="" />
                </div>
                <div class="col-sm-6 text-left">
                    <button type="button" class="btn btn-sm btn-info mr-3" onclick="searchUsrInfo();">투자자 검색</button>
                </div>
            </div>

            <div class="form-group row collapse" id="usrSearch">
                <label class="col-sm-2 col-form-label"></label>
                <div class="col-sm-10" id="usrSearchResult">
                </div>
            </div>


            <div class="form-group row mt-2">
                <label for="loan_usr_info_no" class="col-sm-2 col-form-label">{!! $star !!}투자자번호</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control form-control-sm" id="investor_no" name="investor_no" readonly placeholder="" value="@if(isset($result->investor_no)) {{ $result->investor_no }} @endif"/>
                    <input type="hidden" id="loan_usr_info_no" name="loan_usr_info_no" value="@if(isset($result->loan_usr_info_no)) {{ $result->loan_usr_info_no }} @endif"/>
                </div>
                <label for="loan_usr_info_relation" class="col-sm-2 col-form-label">관계</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control form-control-sm" id="loan_usr_info_relation" placeholder="" data-target="#loan_usr_info_relation" value="@if(isset($result->loan_usr_info_relation)) {{ $result->loan_usr_info_relation }} @endif" readonly/>
                </div>
            </div>
            <div class="form-group row">
                <label for="loan_usr_info_name" class="col-sm-2 col-form-label">투자자이름</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control form-control-sm" id="loan_usr_info_name" readonly placeholder="" data-target="#loan_usr_info_name" value="@if(isset($result->loan_usr_info_name)) {{ $result->loan_usr_info_name }} @endif"/>
                </div>
                <label for="loan_usr_info_ssn" class="col-sm-2 col-form-label">주민/법인번호</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control form-control-sm" id="loan_usr_info_ssn" readonly placeholder="주민/법인번호" data-target="#loan_usr_info_ssn" value="@if(isset($result->loan_usr_info_ssn) && $result->loan_usr_info_ssn != '') {{ $result->loan_usr_info_ssn }} @endif"/>
                </div>
            </div>

            <div class="form-group row">
                <label for="loan_bank_cd" class="col-sm-2 col-form-label">{!! $star !!}투자자은행</label>
                <div class="col-sm-4">
                    <div class="row">
                        <div class="col-sm-12">
                            <select class="form-control form-control-sm" name="loan_bank_cd" id="loan_bank_cd" onchange="changeLoanBank()">
                                <option value=''>투자자은행</option>
                                    {{ Func::printOption($arrayConfig['bank_cd'], "") }}
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="form-group row">
                <label for="loan_bank_ssn" class="col-sm-2 col-form-label">{!! $star !!}투자자계좌번호</label>
                <div class="col-sm-4">
                    <div class="row">
                        <div class="col-sm-12">
                            <input type="text" class="form-control form-control-sm" id="loan_bank_ssn" name="loan_bank_ssn"  placeholder="송금계좌번호 입력" value="" onchange="changeLoanBank()"/>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 text-left">
                    <input type="hidden" id="loan_bank_status" name="loan_bank_status" value="N">
                    <button type="button" class="btn btn-sm btn-danger mr-3" id="loan_bank_btn" onclick="searchUsrBank('INS');">계좌실명조회</button>
                </div>
            </div>
            <div class="form-group row">
                <label for="loan_bank_name" class="col-sm-2 col-form-label">투자자예금주명</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control form-control-sm" id="loan_bank_name" name="loan_bank_name" data-target="#loan_bank_name" value="@if(isset($result->loan_bank_name)) {{ $result->loan_bank_name }} @endif" readonly/>
                </div>
                <label for="loan_bank_nick" class="col-sm-2 col-form-label">지급적요</label>
                <div class="col-sm-4">
                    <input type="text" class="form-control form-control-sm" id="loan_bank_nick" name="loan_bank_nick" data-target="#loan_bank_nick" value="@if(isset($result->loan_bank_nick)) {{ $result->loan_bank_nick }} @endif"/>
                </div>
            </div>
        </div>

        <br>

        <div class="form-group row">
            <label for="contract_date" id="loan_date_label1" class="col-sm-2 col-form-label">{!! $star !!}투자일</label>
            <label for="contract_date" id="loan_date_label2" class="col-sm-2 col-form-label" style="display: none">{!! $star !!}차입일</label>

            <div class="col-sm-4">        
                <div class="input-group date datetimepicker" id="div_contract_date" data-target-input="nearest">
                    <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#div_contract_date" name="contract_date" id="contract_date" DateOnly="true"  value=''  size="6">
                    <div class="input-group-append" data-target="#div_contract_date" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>
        
            <label for="contract_end_date" class="col-sm-2 col-form-label">{!! $star !!}만기일</label>
            <div class="col-sm-4">
                <div class="input-group date datetimepicker" id="div_contract_end_date" data-target-input="nearest">
                    <input type="text" class="form-control form-control-sm datetimepicker-input" data-target="#div_contract_end_date" name="contract_end_date" id="contract_end_date" DateOnly="true"  value=''  size="6">
                    <div class="input-group-append" data-target="#div_contract_end_date" data-toggle="datetimepicker">
                        <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                    </div>
                </div>
            </div>
        </div>


        <div class="form-group row">
            <label for="loan_term" id="loan_period1" class="col-sm-2 col-form-label">{!! $star !!}투자기간</label>
            <label for="loan_term" id="loan_period2" class="col-sm-2 col-form-label" style="display: none">{!! $star !!}차입기간</label>

            <div class="col-sm-4">
                <div class="row">
                    <div class="col-sm-3">
                    <input type="text" class="form-control form-control-sm text-right moneyformat" id="loan_term" name="loan_term" placeholder="개월" maxlength="2">
                    </div>
                    <div>개월</div>
                    <div>
                        <button type="button" class="btn btn-xs btn-primary ml-3" onclick="setContractEndDate();">적용</button>
                    </div>
                </div>
            </div>
            <label for="contract_day" class="col-sm-2 col-form-label">{!! $star !!}약정일</label>
            <div class="col-sm-4">
                <select class="form-control form-control-sm" name="contract_day" id="contract_day" >
                    <option value=''>약정일</option>
                        {{ Func::printOption($arrayConfig['contract_day'], "") }}
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label for="loan_money" id="loan_amount1" class="col-sm-2 col-form-label">{!! $star !!}투자금액</label>
            <label for="loan_money" id="loan_amount2" class="col-sm-2 col-form-label" style="display: none">{!! $star !!}차입금액</label>

            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm text-right moneyformat" id="loan_money" name="loan_money" placeholder="원단위 입력">
                    <div class="input-group-append">
                        <div class="input-group-text text-xs text-center" style="width:35px;"><i class="fas fa-won-sign"></i></div>
                    </div>
                </div>
            </div>
            <label for="pro_cd" class="col-sm-2 col-form-label">{!! $star !!}상품</label>
            <div class="col-sm-4" style="padding:9px">
                @foreach ($arrayConfig['pro_cd'] as $k => $v)
                    @if($k == "01")
                        <input type="radio" name="pro_cd" id="pro_cd" value="{{ $k }}" onClick="handleProdCd()" checked/>&nbsp;&nbsp;{{ $v }}&nbsp;&nbsp;
                    @else
                        <input type="radio" name="pro_cd" id="pro_cd" value="{{ $k }}" onClick="handleProdCd()"/>&nbsp;&nbsp;{{ $v }}&nbsp;&nbsp;
                    @endif
                @endforeach
            </div>
        </div>

        <div class="form-group row">
                <label for="return_method_cd" id="return_method1" class="col-sm-2 col-form-label">{!! $star !!}상환방법</label>
                <div class="col-sm-4" id="return_method2">
                <!-- 상품 : 사모사채, 우선주 -->
                <div id="return_method_cd_wrap1">
                    @foreach ($arrayConfig['return_method_cd'] as $k => $v)
                        @if($k == "F")
                            <input type="radio" name="return_method_cd" value="{{ $k }}" checked/>&nbsp;&nbsp;{{ $v }}&nbsp;&nbsp;
                        @else
                            <input type="radio" name="return_method_cd" value="{{ $k }}"/>&nbsp;&nbsp;{{ $v }}&nbsp;&nbsp;
                        @endif
                    @endforeach
                </div>

                <!-- 상품 : 기관차입 -->
                <div id="return_method_cd_wrap2" style="display: none">
                    @foreach ($arrayConfig['viewing_return_method'] as $k => $v)
                        @if($k == "F")
                            <input type="radio" name="viewing_return_method" value="{{ $k }}" checked/>{{ $v }}
                        @else
                            <input type="radio" name="viewing_return_method" value="{{ $k }}"/>{{ $v }}
                        @endif
                    @endforeach
                </div>
            </div>
            <label for="loan_pay_term" class="col-sm-2 col-form-label">{!! $star !!}이자지급주기</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm col-sm-3 text-right moneyformat" id="loan_pay_term" name="loan_pay_term" placeholder="개월" maxlength="2" value='1'>
                    <div>&nbsp;&nbsp;개월</div>
                </div>
            </div>
        </div>
        
        <div class="form-group row">
            <label for="invest_rate" id="interest_rate1" class="col-sm-2 col-form-label">{!! $star !!}수익률</label>
            <label for="invest_rate" id="interest_rate2" class="col-sm-2 col-form-label" style="display: none">{!! $star !!}이자율</label>

            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" name="invest_rate" id="invest_rate" class="form-control form-control-sm text-right floatnum" placeholder="수익률" autocomplete="off">
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fa fa-percent" style="font-size: 0.65rem;"></i></div>
                    </div>
                </div>
            </div>
            <label for="branch_cd" class="col-sm-2 col-form-label">담당</label>
            <div class="col-sm-4">
                <select class="form-control form-control-sm" name="branch_cd" id="branch_cd" >
                    <option value=''>담당</option>
                        {{ Func::printOption($chargeBranch, "") }}
                </select>
            </div>
        </div>

        <div class="form-group row">
            <label for="income_rate" class="col-sm-2 col-form-label">{!! $star !!}소득세율</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" name="income_rate" id="income_rate" class="form-control form-control-sm text-right floatnum" value="14" placeholder="소득세율" autocomplete="off">
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fa fa-percent" style="font-size: 0.65rem;"></i></div>
                    </div>
                </div>
            </div>
            <label for="local_rate" class="col-sm-2 col-form-label">{!! $star !!}지방 소득세율</label>
            <div class="col-sm-4">
                <div class="input-group">
                    <input type="text" name="local_rate" id="local_rate" class="form-control form-control-sm text-right floatnum" value="10" placeholder="지방 소득세율" autocomplete="off">
                    <div class="input-group-append">
                        <div class="input-group-text"><i class="fa fa-percent" style="font-size: 0.65rem;"></i></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-group row">
            <label for="loan_memo" class="col-sm-2 col-form-label">관리메모</label>
            <div class="col-sm-10">
                <textarea class="form-control form-control-xs" name="loan_memo" id="loan_memo" rows="4" style="resize:none;"></textarea>
            </div>
        </div>

        <div class="text-red ml-3 mt-3">
        {{-- ※ <b>상환방법(계산)</b> : 자유상환 이외의 상환방식은 스케줄 방식으로 이자연체료(지연배상금)가 계산되는 방식 --}}
        </div>


    </div>
    <div class="card-footer">
        <button type="button" class="btn btn-sm btn-info float-right mr-3" id="cate_btn" onclick="loanMngAction('');">계약등록</button>
    </div>
    
</div>

</form>

@endsection

@section('javascript')

<script>

$('.datetimepicker').datetimepicker({
        format: 'YYYY-MM-DD',
        locale: 'ko',
	    useCurrent: false,
});
setInputMask('class', 'moneyformat', 'money');

function searchCustInfo()
{
    var cust_search_string = $("#cust_search_string").val();
    if( cust_search_string=="" )
    {
        alert("검색어를 입력해주세요.");
        $("#cust_search_string").focus();
        return false;
    }
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
    $("#custSearchResult").html(loadingStringtxt);
    $("#custSearch").collapse('show');
    $.post("/erp/loanmngcustsearch", {cust_search_string:cust_search_string}, function(data) {
        $("#custSearchResult").html(data);
    });
}

function selectLoanInfo(n)
{
    var cin = $("#cust_info_no_"+n).html();
    var cnm = $("#cust_name_"+n).html();
    var csn = $("#cust_ssn_"+n).html();
    var cbc = $("#cust_bank_cd_"+n).html();
    var cbs = $("#cust_bank_ssn_"+n).html();

    var van = $("#vir_acct_no_"+n).html();
    
    // 화면에 표시
    $("#cust_info_no").val(cin);
    $("#cust_bank_name").val(cnm);
    $("#cust_ssn").val(csn);
    $("#cust_bank_cd").val(cbc);
    $("#cust_bank_ssn").val(cbs);

    $("#handle_code").val(van);

    $("#custSearch").collapse('hide');
}

function searchUsrInfo()
{
    var usr_search_string = $("#usr_search_string").val();
    if( usr_search_string=="" )
    {
        alert("검색어를 입력해주세요.");
        $("#usr_search_string").focus();
        return false;
    }
	$.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
    $("#usrSearchResult").html(loadingStringtxt);
    $("#usrSearch").collapse('show');
    $.post("/erp/loanmngusrsearch", {usr_search_string:usr_search_string}, function(data) {
        $("#usrSearchResult").html(data);
    });
}

function selectUsrInfo(n)
{
    var uin = $("#loan_usr_info_no_"+n).html();
    var uiv = $("#loan_usr_info_investor_no_"+n).html();
    var cnm = $("#loan_usr_info_name_"+n).html();
    var cnim = $("#loan_usr_info_relation_"+n).html();
    var csn = $("#loan_usr_info_ssn_"+n).html();
    var cbc = $("#loan_usr_info_bank_cd_"+n).html();
    var cbs = $("#loan_usr_info_bank_ssn_"+n).html();
    
    // 화면에 표시
    $("#loan_usr_info_no").val(uin);
    $("#investor_no").val(uiv);
    $("#loan_usr_info_name").val(cnm);
    $("#loan_usr_info_relation").val(cnim);
    $("#loan_usr_info_ssn").val(csn);

    $("#loan_bank_cd").val(cbc);
    $("#loan_bank_ssn").val(cbs);

    $("#usrSearch").collapse('hide');
}

function setContractEndDate()
{
    var today = $('#contract_date').val();
    var loanTerm = $('#loan_term').val();

    if(!today)
    {
        alert('투자일을 선택해 주세요.');
        $('#contract_date').focus();
        return false;
    }

    if(!loanTerm)
    {
        alert('투자기간을 입력해 주세요.');
        $('#loan_term').focus();
        return false;
    }

    $.ajaxSetup({
		headers: {
			'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
		}
	});
    
    $.post("/erp/getaddmonth", {today:today, term:loanTerm}, function(data)
    {
        $('#contract_end_date').val(data);
    });
}

function loanMngAction(md)
{
    // 유효성 체크
    if(!$('#cust_info_no').val())
    {
        alert('차입자를 검색하여 선택 후 진행해 주세요.');
        $('#cust_search_string').focus();
        return false;
    }
    if(!$('#cust_bank_cd').val())
    {
        alert('차입자은행을 선택해주세요.');
        $('#cust_bank_cd').focus();
        return false;
    }
    if(!$('#cust_bank_ssn').val())
    {
        alert('차입자계좌번호를 입력해주세요.');
        $('#cust_bank_ssn').focus();
        return false;
    }

    if(!$('#loan_usr_info_no').val())
    {
        alert('투자자를 검색하여 선택 후 진행해 주세요.');
        $('#usr_search_string').focus();
        return false;
    }
    if(!$('#loan_bank_cd').val())
    {
        alert('투자자은행을 선택해주세요.');
        $('#loan_bank_cd').focus();
        return false;
    }
    if(!$('#loan_bank_ssn').val())
    {
        alert('투자자계좌번호를 입력해주세요.');
        $('#loan_bank_ssn').focus();
        return false;
    }
    if($('#loan_bank_status').val() != 'Y')
    {
        alert('계좌실명조회 버튼을 눌러주세요');
        return false;
    }
    if(!$('#loan_bank_name').val())
    {
        alert('투자자예금주명을 입력해주세요.');
        $('#loan_bank_name').focus();
        return false;
    }
    if(!$('#loan_bank_nick').val())
    {
        alert('지급적요를 입력해주세요.');
        $('#loan_bank_nick').focus();
        return false;
    }

    if(!$('#contract_date').val())
    {
        alert('투자일을 선택해주세요.');
        $('#contract_date').focus();
        return false;
    }
    if(!$('#contract_end_date').val())
    {
        alert('만기일을 선택해주세요.');
        $('#contract_end_date').focus();
        return false;
    }
    if($('#loan_term').val()=='' || $('#loan_term').val()==0)
    {
        alert('투자기간을 입력해주세요.');
        $('#loan_term').focus();
        return false;
    }
    if(!$('#contract_day').val())
    {
        alert('약정일을 선택해주세요.');
        $('#contract_day').focus();
        return false;
    }

    if(!$('#loan_money').val() || $('#loan_money').val()==0)
    {
        alert('투자금액을 입력해주세요.');
        $('#loan_money').focus();
        return false;
    }
    if (!$('input[name="pro_cd"]:checked').val())
    {
        alert('상품을 선택해주세요.');
        $('input[name="pro_cd"]').first().focus();
        return false;
    }
    if ($('input[name="pro_cd"]:checked').val() === '03')
    {
        if(!$('input[name="viewing_return_method"]:checked').val())
        {
            alert('상환방법을 선택해주세요.');
            $('input[name="viewing_return_method"]').first().focus();
            return false;
        }
    }
    else
    {
        if(!$('input[name="return_method_cd"]:checked').val())
        {
            alert('상환방법을 선택해주세요.');
            $('input[name="return_method_cd"]').first().focus();
            return false;
        }
    }

    if($('#loan_pay_term').val()=='' || $('#loan_pay_term').val()==0)
    {
        alert('수익지급주기를 입력해주세요.');
        $('#loan_pay_term').focus();
        return false;
    }
    if(( $('#loan_term').val() % $('#loan_pay_term').val() ) != 0)
    {
        alert('수익지급주기를 확인해주세요.');
        $('#loan_pay_term').focus();
        return false;
    }

    if($('#invest_rate').val()=='')
    {
        alert('수익률을 확인해주세요.');
        $('#invest_rate').focus();
        return false;
    }
    if($('#income_rate').val()=='')
    {
        alert('소득세율을 확인해주세요.');
        $('#income_rate').focus();
        return false;
    }
    if($('#local_rate').val()=='')
    {
        alert('지방 소득세율을 확인해주세요.');
        $('#local_rate').focus();
        return false;
    }

    var maxRatio = {{ Vars::$curMaxRate }};
    var invest_rate = $('#invest_rate').val() * 1;
    var income_rate = $('#income_rate').val() * 1;
    var local_rate = $('#local_rate').val() * 1;
    
    if(invest_rate>maxRatio)
    {
        alert('법정최고이율을 초과 했습니다. 다시 정확히 입력해 주세요.');
        $('#loan_ratio').focus();
        return false;
    }
    if(income_rate>maxRatio)
    {
        alert('법정최고이율을 초과 했습니다. 다시 정확히 입력해 주세요.');
        $('#loan_ratio').focus();
        return false;
    }
    if(local_rate>maxRatio)
    {
        alert('법정최고이율을 초과 했습니다. 다시 정확히 입력해 주세요.');
        $('#loan_ratio').focus();
        return false;
    }

    // 중복클릭 방지
    if(ccCheck()) return;
    
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var formData = new FormData($('#investment_form')[0]);

    $.ajax({
        url  : "/erp/loanmngaction",
        type : "post",
        data : formData,
        processData: false,
        contentType: false,
        success : function(result)
        {
            if( result=="Y" )
            {
                alert("계약등록이 완료됐습니다.");
                window.opener.listRefresh();
                self.close();
            }
            else
            {
                alert(result);
                globalCheck = false;
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
            globalCheck = false;
        }
    });
}

// 엔터막기
function enterClear()
{
    $('#cust_search_string').keydown(function() {
        if (event.keyCode === 13)
        {
            event.preventDefault();
            searchCustInfo();
        };
    });

    $('#usr_search_string').keydown(function() {
        if (event.keyCode === 13)
        {
            event.preventDefault();
            searchUsrInfo();
        };
    });

    $('#loan_bank_ssn').keydown(function() {
        if (event.keyCode === 13)
        {
            event.preventDefault();
            searchUsrBank('INS');
        };
    });
}

enterClear();

function searchUsrBank(div)
{
    var loan_usr_info_no    = $('#loan_usr_info_no').val();
    var loan_bank_cd        = $('#loan_bank_cd').val();
    var loan_bank_ssn       = $('#loan_bank_ssn').val();
    var loan_bank_status    = $('#loan_bank_status').val();
    var handle_code         = $('#handle_code').val();
    
    if(!handle_code)
    {
        alert('차입자를 검색하여 선택 후 진행해 주세요.');
        $('#cust_search_string').focus();
        return false;
    }
    if(!loan_usr_info_no)
    {
        alert('투자자를 검색하여 선택 후 진행해 주세요.');
        $('#usr_search_string').focus();
        return false;
    }
    if(!loan_bank_cd)
    {
        alert('투자자은행을 선택해 주세요.');
        $('#loan_bank_cd').focus();
        return false;
    }
    if(!loan_bank_ssn)
    {
        alert('투자자계좌번호를 입력해 주세요.');
        $('#loan_bank_ssn').focus();
        return false;
    }
    if(loan_bank_status == 'Y')
    {
        alert('계좌실명조회가 완료되었습니다.');
        return false;
    }

    // 중복클릭 방지
    if(ccCheck()) return;
    
    $.ajaxSetup({
        headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    $.ajax({
        url  : "/account/loanbanksearch",
        type : "post",
        data : { div : div, handle_code : handle_code, loan_bank_cd : loan_bank_cd, loan_bank_ssn : loan_bank_ssn},
        success : function(result)
        {
            if( result['rs_code'] == "Y" )
            {
                alert(result['result_msg']);
                $("#loan_bank_status").val('Y');
                $("#loan_bank_name").val(result['result_data']);
                $("#loan_bank_btn").attr("disabled", true);
                globalCheck = false;
            }
            else
            {
                alert(result['result_msg']);
                globalCheck = false;
            }
        },
        error : function(xhr)
        {
            alert("통신오류입니다.");
            globalCheck = false;
        }
    });
}

function changeLoanBank()
{
    $("#loan_bank_status").val('N');
    $("#loan_bank_btn").attr("disabled", false);
}

function handleProdCd()
{
    if ($('input[name="pro_cd"]:checked').val() === '03')
    {
        // 상환방법 > 기관차입
        $("#return_method_cd_wrap1").css('display', 'none');
        $("#return_method_cd_wrap2").css('display', 'block');

        // 투자일 <= 차입일
        $("#loan_date_label1").css('display', 'none');
        $("#loan_date_label2").css('display', 'block');

        // 투자기간 <= 차입기간
        $("#loan_period1").css('display', 'none');
        $("#loan_period2").css('display', 'block');

        // 투자금액 <= 차입금액
        $("#loan_amount1").css('display', 'none');
        $("#loan_amount2").css('display', 'block');

        // 수익률 <= 이자율
        $("#interest_rate1").css('display', 'none');
        $("#interest_rate2").css('display', 'block');
    }
    else
    {
        // 상환방법 > 기관차입
        $("#return_method_cd_wrap1").css('display', 'block');
        $("#return_method_cd_wrap2").css('display', 'none');

        // 투자일 => 차입일
        $("#loan_date_label1").css('display', 'block');
        $("#loan_date_label2").css('display', 'none');

        // 투자기간 => 차입기간
        $("#loan_period1").css('display', 'block');
        $("#loan_period2").css('display', 'none');

        // 투자금액 => 차입금액
        $("#loan_amount1").css('display', 'block');
        $("#loan_amount2").css('display', 'none');

        // 수익률 => 이자율
        $("#interest_rate1").css('display', 'block');
        $("#interest_rate2").css('display', 'none');
    }
}

</script>

@endsection
