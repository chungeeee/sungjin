<?
    // 환경설정
    $config_value = Func::getConfigArr();
?>

<form class="form-horizontal" role="form" name="mo_out_money_form" id="mo_out_money_form" method="post">
<input type="hidden" id="check_no" name="check_no" value="{{ $no }}">

<div class="form-group row">
    <label for="mo_bank_cd" class="col-sm-2 col-form-label"><font class='pl-1 pr-1' color='red'>*</font>은행명</label>
    <div class="col-sm-4">
        <input type="text" class="form-control form-control-sm" id="mo_bank_cd" name="mo_bank_cd" value="{{ $v->mo_bank_cd ?? '' }}" readonly >
    </div>

    <label for="mo_ssn" class="col-sm-2 col-form-label"><font class='pl-1 pr-1' color='red'>*</font>계좌번호</label>
    <div class="col-sm-4">
        <input type="text" class="form-control form-control-sm" id="mo_ssn" name="mo_ssn" value="{{ $v->mo_ssn ?? '' }}" readonly >
    </div>
</div>

<div class="form-group row">
    <label for="type" class="col-sm-2 col-form-label"><font class='pl-1 pr-1' color='red'>*</font>출금구분</label>
    <div class="col-sm-4">
        <select class="form-control form-control-sm col-sm-7" id="type" name="type">
            <option value=''>출금구분</option>
            <option value='0'>대출</option>
            <option value='1'>기타출금</option>
            <option value='2'>가수금반환</option>
        </select>
    </div>
    <label for="save_date" class="col-sm-2 col-form-label"><font class='pl-1 pr-1' color='red'>*</font>출금일</label>
    <div class="col-sm-4">
        <div class="input-group date" id="div_save_date" data-target-input="nearest">
            <input type="text" class="form-control form-control-sm datetimepicker-input dateformat col-sm-5" id="save_date" name="save_date" placeholder="출금일" data-target="#div_save_date" value="{{ $v->save_date ?? '' }}"/>
            <div class="input-group-append" data-target="#div_save_date" data-toggle="datetimepicker">
                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
            </div>
        </div>
    </div>
</div>

<div class="form-group row">
    <label for="branch_name" class="col-sm-2 col-form-label"><font class='pl-1 pr-1' color='red'>*</font>거래지점</label>
    <div class="col-sm-4">
        <input type="text" class="form-control form-control-sm" id="branch_name" value="{{ $v->branch_name ?? '' }}" readonly >
        <input type="hidden" id="trade_branch_cd" name="trade_branch_cd" value="{{ $v->trade_branch_cd ?? '' }}" readonly >
    </div>
    <label for="out_money" class="col-sm-2 col-form-label"><font class='pl-1 pr-1' color='red'>*</font>출금액</label>
    <div class="col-sm-4">
        <input type="text" class="form-control form-control-sm moneyformat" id="out_money" name="out_money" placeholder="원단위 입력">
    </div>
</div>
</form>

<!-- INPUT MASK 설정 -->
<script>
    $(function () {
        setInputMask('class', 'dateformat', 'date');
        setInputMask('class', 'moneyformat', 'money');
    });
</script>