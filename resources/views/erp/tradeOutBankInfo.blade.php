

<table class="table table-sm table-hover card-secondary card-outline mt-0 mb-1">
    <thead>
        <tr>
            <th class="text-center">고객번호</th>
            <th class="text-center">계약번호</th>
            <th class="text-center">이름</th>
            <th class="text-center">출금구분</th>
            <th class="text-center">출금일</th>
            <th class="text-center">출금액</th>
            <th class="text-center">등록일시</th>
        </tr>
    </thead>
    <tbody>
        <tr>
            <td class="text-center">{{ $info->cust_info_no }}</td>
            <td class="text-center">{{ $info->loan_info_no }}</td>
            <td class="text-center" id="cust_name">{{ $info->name }}</td>
            <td class="text-center">{{ $info->trade_type_nm }}</td>
            <td class="text-center">{{ Func::dateFormat($info->trade_date) }}</td>
            <td class="text-center">{{ number_format($info->trade_money) }}</td>
            <td class="text-center">{{ Func::dateFormat($info->save_time) }}</td>
        </tr>            
    </tbody>
</table>


<div class="form-group row mt-2">
<div class="col-sm-12">
    <table class="table table-sm table-hover card-secondary card-outline mt-0 mb-1">

    <col width="5%"/>
    <col width="15%"/>
    <col width="16%"/>
    <col width="13%"/>
    <col width="13%"/>
    <col width="8%"/>
    <col width=""/>
    <col width=""/>

        <thead>
            <tr>
                <th class="text-center">순번</th>
                <th class="text-center">은행</th>
                <th class="text-center">계좌번호</th>
                <th class="text-center">예금주명</th>
                <th class="text-center">금액</th>
                <th class="text-center">뱅킹</th>
                <th class="text-center">처리상태</th>
                <th class="text-center">처리시간</th>
                <!--<th class="text-center">결과코드</th>-->
            </tr>
        </thead>
        <tbody id="loanInfoBankList">
        @foreach( $bank as $val )
            <tr>
                <td class="text-center">{{ $val->seq }}</td>
                <td class="text-center">{{ $array_bank[$val->bank_code] }}</td>
                <td class="text-center">{{ $val->bank_ssn }}</td>
                <td class="text-center">{{ $val->bank_owner }}</td>
                <td class="text-center">{{ number_format($val->trade_sub_money) }}</td>
                <td class="text-center">
                    @if( $val->firmbank_yn=="Y" )
                        @if( $val->firmbank_status=="N" )
                            <i class='fas fa-won-sign {{ Vars::$arrayFirmbankStatusTextClass[$val->firmbank_status] }}' role="button" onclick="viewBankInfoForm( '{{ $val->seq }}', '{{ $val->bank_code }}', '{{ $val->bank_ssn }}', '{{ $val->bank_owner }}', '{{ number_format($val->trade_sub_money) }}' );"></i>
                        @else
                            <i class='fas fa-won-sign {{ Vars::$arrayFirmbankStatusTextClass[$val->firmbank_status] }}'></i>
                        @endif
                    @endif
                </td>
                <td class="text-center">
                    {{ Func::getArrayName(Vars::$arrayFirmbankStatus,$val->firmbank_status) }}
                    @if( $val->firmbank_status=="N" )
                    <a type="button" data-container="body" data-toggle="popover" data-html="true" data-placement="right" data-content='{{ $val->firmbank_status_code }} {{ Func::nvl(Vars::$arrayStebnkResultcode[$val->firmbank_status_code], Func::nvl(Vars::$arrayStebnkFirmcode[$val->firmbank_status_code],'')) }}'>
                    <i class='fas fa-info-circle text-gray'></i>
                    </a>
                    @endif
                </td>
                <td class="text-center">{{ Func::dateFormat($val->firmbank_status_time) }}</td>
            </tr>
        @endforeach
        </tbody>

        @if( $info->firmbank_status=="Z" || $info->firmbank_status=="S" || $info->cert_id!="" )

        <tfoot>
        <tr>
        <td colspan=8 class="text-right">
            @if( $info->firmbank_status=="Z" )

                @if( Func::funcCheckPermit("R003") && ( $info->trade_type=="11" || $info->trade_type=="12" || $info->trade_type=="13" ) )
                <button type='button' class='ml-2 mt-2 btn btn-sm btn-success' id="bank_trans_cert_check_btn" onclick="bankTransCertcheck({{ $info->no }});">고액대출 송금승인</button>
                @endif
                @if( Func::funcCheckPermit("R004") && ( $info->trade_type=="91" ) && Func::funcCheckPermit("A242","A") )
                <button type='button' class='ml-2 mt-2 btn btn-sm btn-success' id="bank_trans_cert_check_btn" onclick="bankTransCertcheck({{ $info->no }});">가수반환 송금승인</button>
                @endif

            @elseif(Func::funcCheckPermit("A241","A") && $info->firmbank_status=="S" )
                <button type='button' class='ml-2 mt-2 btn btn-sm btn-success' id="bank_trans_cert_check_btn" onclick="bankTransCertcheck({{ $info->no }});">송금계좌변경 승인</button>
            @else
            <div class="mt-2 font-weight-bold">
            <i class='fas fa-check text-success mr-2'></i>
                @if($info->trade_money>=10000000 && ($info->trade_type=="11" || $info->trade_type=="12" || $info->trade_type=="13"))
                고액대출승인
                @elseif($info->trade_type=="91")
                가수반환승인
                @else
                송금계좌변경 승인
                @endif
                {{ $info->cert_id }} / {{ Func::dateFormat($info->cert_time) }}
            </div> 
            @endif
        </td>
        </tr>
        </tfoot>

        @endif



    </table>

    <form class="form-horizontal" name="trade_out_bankinfo_form" id="trade_out_bankinfo_form">
    <input type='hidden' name='cust_info_no' id='sub_cust_info_no' value='{{ $info->cust_info_no }}'>
    <input type='hidden' name='loan_info_no' id='sub_loan_info_no' value='{{ $info->loan_info_no }}'>
    <input type='hidden' name='loan_info_trade_no' id='sub_loan_info_trade_no' value='{{ $info->no }}'>
    <input type='hidden' id='modal_firmbank_status' value='{{ $info->firmbank_status }}'>

    <div class="form-group row collapse p-2" id="collapseBankInfoForm">
    <label class="col-sm-12 col-form-label">송금정보 수정</label>

    <table class="table table-sm">
    <col width="5%"/>
    <col width="15%"/>
    <col width="16%"/>
    <col width="13%"/>
    <col width="13%"/>
    <col width=""/>

    <tr>
    <td><input type='text' class='form-control form-control-xs text-center' id='sub_seq' name='sub_seq' placeholder='구분' value='' readonly></td>
    <td>
        <select class='form-control form-control-xs' id='sub_bank_code' name='sub_bank_code' disabled>
        {{ Func::printOption($array_bank) }}
        </select>
    </td>
    <td><input type='text' class='form-control form-control-xs text-center' id='sub_bank_ssn' name='sub_bank_ssn' placeholder='계좌번호' value='' disabled></td>
    <td><input type='text' class='form-control form-control-xs text-center' id='sub_bank_owner' name='sub_bank_owner' placeholder='예금주명' value='' disabled></td>
    <td><input type='text' class='form-control form-control-xs text-center' id='sub_trade_sub_money' name='sub_trade_sub_money' placeholder='금액' value='' readonly></td>
    <td>
        <input type='hidden' name='sub_bank_chk_yn'   id='sub_bank_chk_yn'   value=''>
        <input type='hidden' name='sub_bank_chk_time' id='sub_bank_chk_time' value=''>
        <input type='hidden' name='sub_bank_chk_id'   id='sub_bank_chk_id'   value=''>
        <button type='button' class='btn btn-xs btn-secondary' id='sub_bank_chk_btn' onclick='bankAccountcheck();'>예금주조회</button>
    </td>
    </tr>
    </table>

    </div>
    </form>

</div>
</div>


</form>


<script>


@if( Func::funcCheckPermit("R003") || Func::funcCheckPermit("R004") )

// 송금승인
function bankTransCertcheck(tn)
{
    $("#bank_trans_cert_check_btn").attr("disabled", true);

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    $.ajax({
        url  : "/erp/tradeoutcertchk",
        type : "post",
        data : { loan_info_trade_no:tn },
        success : function(rslt)
        {
            if( rslt=="Y" )
            {
                alert("정상적으로 승인처리되었습니다.");
                $("#bankinfoModalContent").html("");
                $("#bankinfoModal").modal('hide');
                getDataList('tradeout', 1, '/erp/tradeoutlist', $('#form_tradeout').serialize());
            }
            else
            {
                alert(rslt);
                $("#bank_trans_cert_check_btn").attr("disabled", false);
            }
        },
        error : function(xhr)
        {
            alert("승인처리에 실패하였습니다.");
            $("#bank_trans_cert_check_btn").attr("disabled", false);
            console.log(xhr);
        }
    });
}

@endif



$(function () {
        // Enables popover
        $("[data-toggle=popover]").popover();

        @if( $info->firmbank_status=="Z" )
        $("#btn_bankInfoUpdateAction").prop("disabled",true);
        @else 
        $("#btn_bankInfoUpdateAction").prop("disabled",false);
        @endif
    });
</script>