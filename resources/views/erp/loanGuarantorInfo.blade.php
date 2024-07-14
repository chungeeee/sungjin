<form id="guarantor_form" method="post" enctype="multipart/form-data">
    <table class="table table-sm table-bordered table-input text-xs">
        <input type="hidden" name="loan_info_no" id="loan_info_no" value="{{ $v->loan_info_no ?? '' }}">
        <input type="hidden" name="cust_info_no" id="cust_info_no" value="{{ $v->cust_info_no ?? '' }}">
        <input type="hidden" name="no" id="no" value="{{ $v->no ?? '' }}">
        <input type="hidden" name="mode" id="mode" value="{{ $v->mode ?? '' }}">

        <colgroup>
        <col width="10%"/>
        <col width="40%"/>
        <col width="10%"/>
        <col width="40%"/>
        </colgroup>

        <tbody>

        <tr>
            <th><span class="text-danger font-weight-bold h6 mr-1">*</span>이름</th>
            <td>
                <div class="row">
                    <div class="col-md-2 pr-0">
                        <input type="text" class="form-control  form-control-sm" name="name" id="name" value="{{$v->name ?? ''}}" required>
                    </div>
                    <div class="col-md-3 ml-2 p-0">
                        <select class="form-control form-control-sm" name="relation_cd" id="relation_cd" >
                        <option value=''>관계선택</option>
                        {{ Func::printOption($configArr['relation_cd'],isset($v->relation_cd)?$v->relation_cd:'') }}   
                        </select>
                    </div>
                    <div class="col-md-3 ml-2 p-0">
                        <select class="form-control form-control-sm " name="marry_status_cd" id="marry_status_cd">
                        <option value=''>결혼상태</option>
                        {{ Func::printOption($configArr['marry_status_cd'],isset($v->marry_status_cd)?$v->marry_status_cd:'') }}
                        </select>
                    </div>
                    <div class="col-md-3 align-self-center">
                        <div class="form-check mt-0 mb-0 align-self-center">
                        <input type="checkbox" class="form-check-input " id="live_together" name="live_together" 
                        {{ Func::echoChecked('Y',isset($v->live_together)?$v->live_together:'','checked') }}>
                        <label class="form-check-label" for="live_together">동거인</label>
                        </div>
                    </div>
                </div>
            </td>
            <th><span class="text-danger font-weight-bold h6 mr-1">*</span>주민등록번호</th>
            <td>
                <div class="row">
                
                    <div class="col-md-2 pr-0"><input type="text" class="form-control form-control-sm" name="ssn1" id="ssn1" onkeyup="onlyNumber(this);" value="{{$v->ssn1 ?? ''}}" maxlength=6 required></div>
                    <div class="col-md-2 pr-0"><input type="text" class="form-control form-control-sm" name="ssn2" id="ssn2" onkeyup="onlyNumber(this);" value="{{$v->ssn2 ?? ''}}" maxlength=7 required></div>
                    <div class="col-md-3">
                    <select class="form-control form-control-sm" name="status" id="status" onChange="alertGuarantor('{{ $v->status ?? '' }}', '{{ $v->no ?? '' }}')">
                        <option value=''>상태선택</option>
                        <option value='Y' {{ isset($v->status)&&$v->status=="Y"?"selected":"" }}>유효</option>
                        <option value='N'{{ isset($v->status)&&$v->status=="N"?"selected":"" }}>비유효</option>
                    </select>
                    </div>
                <div>
            </td>
        </tr> 

        <tr>
            <th>채권구분</th>
            <td>
                <div class="row">
                    
                        <div class="col-md-6 m-0 pr-0">
                        <select class="form-control form-control-sm" name="g_loan_cat_1_cd" id="g_loan_cat_1_cd">
                        <option value=''>채권구분1</option>
                        {{ Func::printOption($configArr['loan_cat_1_cd'], $v->g_loan_cat_1_cd ?? '') }}
                        </select>
                        </div>
                        <div class="col-md-6 m-0 pr-3">
                        <select class="form-control form-control-sm" name="g_loan_cat_2_cd" id="g_loan_cat_2_cd">
                        <option value=''>채권구분2</option>
                        {{ Func::printOption($configArr['loan_cat_2_cd'], $v->g_loan_cat_2_cd ?? '') }}
                        </select>
                        </div>
                    
                </div>
            </td>
            <th></th>
            <td>
                
            </td>
        </tr> 

        <tr>
            <th>집전화</th>
                <td>
                    <div class="row">
                        <div class="col-md-2 m-0 pr-0"><input type="text" class="form-control form-control-sm" name="ph11" id="ph11" value="{{$v->ph11 ?? ''}}" onkeyup="onlyNumber(this);" maxlength=3 ></div>
                        <div class="col-md-2 m-0 pr-0"><input type="text" class="form-control form-control-sm" name="ph12" id="ph12" value="{{$v->ph12 ?? ''}}" onkeyup="onlyNumber(this);" maxlength=4 ></div>
                        <div class="col-md-2 m-0 pr-0"><input type="text" class="form-control form-control-sm" name="ph13" id="ph13" value="{{$v->ph13 ?? ''}}" onkeyup="onlyNumber(this);" maxlength=4 ></div>
                        <div class="col-md-3 m-0 ">
                            <select class="form-control form-control-sm" name="ph1_status" id="ph1_status">
                            <option value=''>상태선택</option>
                            {{ Func::printOption($configArr['call_status_cd'],isset($v->ph1_status)?$v->ph1_status:'') }}
                            </select>
                        </div>
                    </div>
                </td>
            <th>집전화명의</th>
            <td>
                <div class="row">
                    <div class="col-md-2 pr-0">
                        <input type="text" class="form-control form-control-sm" name="ph1_name" id="ph1_name" value="{{$v->ph1_name ?? ''}}" >
                    </div>
                    <div class="col-md-3">
                    <select class="form-control form-control-sm" name="ph1_name_rel" id="ph1_name_rel">
                        <option value=''>관계선택</option>
                        {{ Func::printOption($configArr['relation_cd'],isset($v->ph1_name_rel)?$v->ph1_name_rel:'') }}
                    </select>
                    </div>
                <div>
            </td>
        </tr> 

        <tr>
            <th>휴대전화</th>
                <td>
                    <div class="row">
                        <div class="col-md-2 m-0 pr-0"><input type="text" class="form-control form-control-sm" name="ph21" id="ph21" value="{{$v->ph21 ?? ''}}" onkeyup="onlyNumber(this);" maxlength=3 ></div>
                        <div class="col-md-2 m-0 pr-0"><input type="text" class="form-control form-control-sm" name="ph22" id="ph22" value="{{$v->ph22 ?? ''}}" onkeyup="onlyNumber(this);" maxlength=4 ></div>
                        <div class="col-md-2 m-0 pr-0"><input type="text" class="form-control form-control-sm" name="ph23" id="ph23" value="{{$v->ph23 ?? ''}}" onkeyup="onlyNumber(this);" maxlength=4 ></div>
                        <div class="col-md-3 m-0 ">
                            <select class="form-control form-control-sm" name="ph2_status" id="ph2_status">
                            <option value=''>상태선택</option>
                            {{ Func::printOption($configArr['call_status_cd'],isset($v->ph2_status)?$v->ph2_status:'') }}                                     
                            </select>
                        </div>
                    </div>
                </td>
            <th>휴대전화명의</th>
            <td>
                <div class="row">
                    <div class="col-md-2 pr-0">
                        <input type="text" class="form-control form-control-sm" name="ph2_name" id="ph2_name" value="{{$v->ph2_name ?? ''}}">
                    </div>
                    <div class="col-md-3">
                    <select class="form-control form-control-sm" name="ph2_name_rel" id="ph2_name_rel">
                        <option value=''>관계선택</option>
                        {{ Func::printOption($configArr['relation_cd'],isset($v->ph2_name_rel)?$v->ph2_name_rel:'') }}                            
                    </select>
                    </div>
                <div>
            </td>
        </tr>

        <tr>
            <th>기타전화</th>
                <td>
                    <div class="row">
                        <div class="col-md-2 m-0 pr-0"><input type="text" class="form-control form-control-sm" name="ph41" id="ph41" value="{{$v->ph41 ?? ''}}" onkeyup="onlyNumber(this);" maxlength=3 ></div>
                        <div class="col-md-2 m-0 pr-0"><input type="text" class="form-control form-control-sm" name="ph42" id="ph42" value="{{$v->ph42 ?? ''}}" onkeyup="onlyNumber(this);" maxlength=4 ></div>
                        <div class="col-md-2 m-0 pr-0"><input type="text" class="form-control form-control-sm" name="ph43" id="ph43" value="{{$v->ph43 ?? ''}}" onkeyup="onlyNumber(this);" maxlength=4 ></div>
                        <div class="col-md-3 m-0 ">
                            <select class="form-control form-control-sm" name="ph4_status" id="ph4_status" >
                            <option value=''>상태선택</option>
                            {{ Func::printOption($configArr['call_status_cd'],isset($v->ph4_status)?$v->ph4_status:'') }}          
                            </select>
                        </div>
                    </div>
                </td>
            <th>기타전화명의</th>
            <td>
                <div class="row">
                    <div class="col-md-2 pr-0">
                        <input type="text" class="form-control form-control-sm" name="ph4_name" id="ph4_name" value="{{$v->ph4_name ?? ''}}">
                    </div>
                    <div class="col-md-3">
                    <select class="form-control form-control-sm" name="ph4_name_rel" id="ph4_name_rel">
                        <option value=''>관계선택</option>
                        {{ Func::printOption($configArr['relation_cd'],isset($v->ph4_name_rel)?$v->ph4_name_rel:'') }}              
                    </select>
                    </div>
                <div>
            </td>
        </tr>   

        <tr>
            <th>주거형태</th>
            <td>
                <div class="col-md-3 pl-0">
                <select class="form-control form-control-sm" name="house_type_cd" id="house_type_cd">
                    <option value=''>선택</option>
                    {{ Func::printOption($configArr['house_type_cd'],isset($v->house_type_cd)?$v->house_type_cd:'') }}              
                </select>
                </div>
            </td>  
            <th>주택구분</th>
            <td>
                <div class="col-md-3 pl-0">
                <select class="form-control form-control-sm" name="house_own_cd" id="house_own_cd">
                    <option value=''>선택</option>
                    {{ Func::printOption($configArr['house_own_cd'],isset($v->house_own_cd)?$v->house_own_cd:'') }}  
                </select>
                </div>
            </td>
        </tr>   
        <tr>
            <th>거주기간</th>
            <td>
                <div class="row m-0">
                    <input type="text" class="form-control form-control-sm col-md-1 mr-1" value="{{$v->stay_year ?? ''}}" onkeyup="onlyNumber(this);" name="stay_year" id="stay_year"><span class="pt-2">년</span>
                    <input type="text" class="form-control form-control-sm col-md-1 ml-2 mr-1" value="{{$v->stay_months ?? ''}}" onkeyup="onlyNumber(this);" name="stay_months" id="stay_months"><span class="pt-2">개월</span>
                </div>
            </td>
            <th>급여일</th>
            <td>
                <select class="form-control form-control-sm col-md-2" name="pay_day" id="pay_day">
                    <option value=''>선택</option>
                    @for ($i = 1; $i < 32; $i++)
                    <option value='{{ $i }}' {{ isset($v->pay_day) && $i==$v->pay_day ? "selected":""}}> {{ $i }}일</option>
                    @endfor
                </select>
            </td>                    
        </tr>
        <tr>
            <th>실거주주소</th>
            <td>
                <div class="row">
                    <div class="input-group col-sm-4 pb-1">
                    <input type="text" class="form-control" name="zip1" id="g_zip1" numberOnly="true" value="{{$v->zip1 ?? ''}}" readOnly>
                    <span class="input-group-btn input-group-append">
                    <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('g_zip1', 'g_addr11', 'g_addr12', '')">검색</button>
                    </span>
                    </div>
                    <div class="pl-0 p-1">
                    <button type="button" class="btn btn-secondary btn-xs" onclick="setAddr('g_zip1', 'g_addr11', 'g_addr12',$('#g_zip2').val(), $('#g_addr21').val(), $('#g_addr22').val());">등본</button>
                    <button type="button" class="btn btn-secondary btn-xs" onclick="setAddr('g_zip1', 'g_addr11', 'g_addr12',$('#g_zip4').val(), $('#g_addr41').val(), $('#g_addr42').val());">기타</button>
                    </div>
                </div>                         
                <input type="text" class="form-control mb-1 col-md-10" name="addr11" id="g_addr11" value="{{$v->addr11 ?? ''}}" readOnly>
                <input type="text" class="form-control col-md-10" name="addr12" id="g_addr12" value="{{$v->addr12 ?? ''}}" maxlength="100">
            </td>
            <th>등본주소</th>
            <td>
                <div class="row">
                    <div class="input-group col-sm-4 pb-1">
                    <input type="text" class="form-control" name="zip2" id="g_zip2" value="{{$v->zip2 ?? ''}}" numberOnly="true" readOnly>
                    <span class="input-group-btn input-group-append">
                    <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('g_zip2', 'g_addr21', 'g_addr22', '')">검색</button>
                    </span>
                    </div>
                    <div class="pl-0 p-1">
                    <button type="button" class="btn btn-secondary btn-xs" onclick="setAddr('g_zip2', 'g_addr21', 'g_addr22',$('#g_zip1').val(), $('#g_addr11').val(), $('#g_addr12').val());">실거주</button>
                    <button type="button" class="btn btn-secondary btn-xs" onclick="setAddr('g_zip2', 'g_addr21', 'g_addr22',$('#g_zip4').val(), $('#g_addr41').val(), $('#g_addr42').val());">기타</button>
                    </div>
                </div>
                <input type="text" class="form-control mb-1 col-md-10" value="{{$v->addr21 ?? ''}}" name="addr21" id="g_addr21" readOnly>
                <input type="text" class="form-control col-md-10" value="{{$v->addr22 ?? ''}}" name="addr22" id="g_addr22" maxlength="100">
            </td>
        </tr>   
        <tr>
            <th rowspan=3>기타주소</th>
            <td rowspan=3>
                <div class="row">
                    <div class="input-group col-sm-4 pb-1">
                    <input type="text" class="form-control" name="zip4" id="g_zip4" value="{{$v->zip4 ?? ''}}" numberOnly="true" readOnly>
                    <span class="input-group-btn input-group-append">
                    <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('g_zip4', 'g_addr41', 'g_addr42', '')">검색</button>
                    </span>
                    </div>
                    <div class="pl-0 p-1">
                    <button type="button" class="btn btn-secondary btn-xs" onclick="setAddr('g_zip4', 'g_addr41', 'g_addr42',$('#g_zip1').val(), $('#g_addr11').val(), $('#g_addr12').val());">실거주</button>
                    <button type="button" class="btn btn-secondary btn-xs" onclick="setAddr('g_zip4', 'g_addr41', 'g_addr42',$('#g_zip2').val(), $('#g_addr21').val(), $('#g_addr22').val());">등본</button>
                    </div>
                </div>
                <input type="text" class="form-control mb-1 col-md-10" value="{{$v->addr41 ?? ''}}" name="addr41" id="g_addr41" readOnly>
                <input type="text" class="form-control col-md-10" value="{{$v->addr42 ?? ''}}" name="addr42" id="g_addr42" maxlength="100">
            </td>
            <th>우편물주소</th>
            <td>
                <select class="form-control from-control-sm col-md-4" name="post_send_cd" id="post_send_cd" >
                <option value=''>선택</option>
                {{ Func::printOption($configArr['addr_cd'],isset($v->post_send_cd)?$v->post_send_cd:'') }}   
                </select>
            </td>
        </tr>   


        <tr>
            <th>이메일</th>
            <td><input type="text" class="form-control col-md-4" name="email" id="email" value="{{$v->email ?? ''}}" ></td>
        </tr>
        <tr>
            <th>직업구분</th>
            <td>
                <input type="text" class="form-control mb-1 col-md-10" value="{{$v->job_codestr ?? ''}}" name="job_codestr" id="g_job_codestr" onclick="getJobCode('g_job_code');" placeholder="직업구분 선택을 위해 클릭해주세요" readOnly>
                <input type="hidden" name="job_cd" id="g_job_code" value="{{$v->job_cd ?? ''}}">
            </td>
        </tr>
        <tr>
            <th>직장명</th>
            <td><input type="text" class="form-control col-md-4" name="com_name" id="com_name" value="{{$v->com_name ?? ''}}" ></td>
            <th>직장전화</th>
            <td>
                <div class="row">
                    <div class="col-md-2 m-0 pr-0"><input type="text" class="form-control" name="ph31" id="ph31" onkeyup="onlyNumber(this);" maxlength=3 value="{{$v->ph31 ?? ''}}" ></div>
                    <div class="col-md-2 m-0 pr-0"><input type="text" class="form-control" name="ph32" id="ph32" onkeyup="onlyNumber(this);" maxlength=4 value="{{$v->ph32 ?? ''}}" ></div>
                    <div class="col-md-2 m-0 pr-0"><input type="text" class="form-control" name="ph33" id="ph33" onkeyup="onlyNumber(this);" maxlength=4 value="{{$v->ph33 ?? ''}}" ></div>
                    <div class="col-md-2 m-0 pr-0"><input type="text" class="form-control" name="ph34" id="ph34" onkeyup="onlyNumber(this);" maxlength=4 value="{{$v->ph34 ?? ''}}" ></div>
                </div>
            </td>  
        </tr>
        <tr>
            <th rowspan=3>직장주소</th>
            <td rowspan=3>
                <div class="row">
                    <div class="input-group col-sm-4 pb-1">
                    <input type="text" class="form-control" name="zip3" id="g_zip3" numberOnly="true" value="{{$v->zip3 ?? ''}}" readOnly>
                    <span class="input-group-btn input-group-append">
                    <button class="btn btn-default btn-sm" type="button" onclick="DaumPost('g_zip3', 'g_addr31', 'g_addr32', '')">검색</button>
                    </span>
                    </div>
                </div>
                <input type="text" class="form-control mb-1 col-md-10" name="addr31" id="g_addr31" value="{{$v->addr31 ?? ''}}" readOnly>
                <input type="text" class="form-control col-md-10" name="addr32" id="g_addr32" value="{{$v->addr32 ?? ''}}" maxlength="100">
            </td>
            <th>근속기간</th>
            <td>
                <div class="row m-0">
                    <input type="text" class="form-control col-md-1 mr-1" onkeyup="onlyNumber(this);" value="{{$v->com_year ?? ''}}" name="com_year" id="com_year" ><span class="pt-2">년</span>
                    <input type="text" class="form-control col-md-1 ml-2 mr-1" onkeyup="onlyNumber(this);" value="{{$v->com_months ?? ''}}" name="com_months" id="com_months" ><span class="pt-2">개월</span>
                </div>
            </td>

        </tr>
        <tr>
            <th>연간소득</th>
            <td>
                <div class="row m-0">
                <input type="text" class="form-control col-md-1 mr-1" onkeyup="onlyNumber(this);" value="{{$v->year_income ?? ''}}" name="year_income" id="year_income" ><span class="pt-2">만원</span>
                </div>
            </td>
        </tr>
        <tr>
            <th>사업자번호</th>
            <td><input type="text" class="form-control col-md-3" onkeyup="onlyNumber(this);" value="{{$v->com_ssn ?? ''}}" name="com_ssn" id="com_ssn" ></td>
        </tr>

        <tr>
            <th>기한의이익상실<br>통지서 발송</th>
            <td>
                <div class="row">
                    <div class="col-md-3 m-0 pr-0">
                        <div class="input-group date datetimepicker" id="div_g_trigger_send_dt" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm datetimepicker-input " name="trigger_send_date" id="trigger_send_date" value="{{ isset($v->trigger_send_date) ? Func::dateFormat(Func::nvl($v->trigger_send_date,'')) : ''}}" dateonly="true" >
                            <div class="input-group-append" data-target="#div_g_trigger_send_dt" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 text-center pt-2 font-weight-bold">등기번호</div>
                    <div class="col-md-4 m-0">
                        <input type="text" class="form-control form-control-sm" name="trigger_reg_no" id="trigger_reg_no" onkeyup="onlyNumber(this);" maxlength=20 value="{{ $v->trigger_reg_no ?? '' }}">
                    </div>
                </div>
            </td>
            <th>채불등록예정<br>통보서 발송</th>
            <td>
                <div class="row">
                    <div class="col-md-3 m-0 pr-0">
                        <div class="input-group date datetimepicker" id="div_g_bad_send_dt" data-target-input="nearest">
                            <input type="text" class="form-control form-control-sm datetimepicker-input " name="bad_send_date" id="bad_send_date" value="{{ isset($v->bad_send_date) ? Func::dateFormat(Func::nvl($v->bad_send_date,'')) : ''}}" dateonly="true" >
                            <div class="input-group-append" data-target="#div_g_bad_send_dt" data-toggle="datetimepicker">
                                <div class="input-group-text"><i class="fa fa-calendar"></i></div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-2 text-center pt-2 font-weight-bold">등기번호</div>
                    <div class="col-md-4 m-0">
                        <input type="text" class="form-control form-control-sm" name="bad_reg_no" id="bad_reg_no" onkeyup="onlyNumber(this);" maxlength=20 value="{{ $v->bad_reg_no ?? '' }}">
                    </div>
                </div>
            </td>
        </tr>
        </tbody>
    </table>

    <table class="table table-sm table-bordered table-input text-xs">
        <tbody>
        </tbody>
        <tfoot>
            <tr>
                <td colspan="8" class="text-right">
                    <button class="btn btn-sm bg-lightblue" onclick="guarantorAction();">저장</button>
                    @if(isset($v->mode) && $v->mode == "UPD" && isset($v->auth) && $v->auth == 'Y')
                        <button class="btn btn-sm bg-danger" onclick="removeAction();">삭제</button>
                    @endif
                </td>
            </tr>
        </tfoot>
    </table>
</form>