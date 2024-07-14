            <input type="hidden" name="code" value="{{ $code }}">
            <input type="hidden" name="no" value="{{ $no ?? '' }}">
            <input type="hidden" name="guarantor_no" value="{{ $guarantor_no ?? '' }}">
            <div class="form-inline mb-1">
                <label for="ssn" class="mr-1">주민등록번호: </label>
                @isset($ssn)
                <span id="ssn">{{substr($ssn, 0, 6)}}-{{substr($ssn, 6, 7)}}</span>
                @endisset
                @isset($name)
                <span class="ml-1" id="name">{{ $name }}</span>
                @endisset
                @if($code == 'CERT')
                    @if(!empty($guarantor_no))
                        <button type="button" class="btn btn-sm btn-info ml-2" id="getCreditBtn" onclick="getGuarantorCredit('{{$guarantor_no}}');">새로 조회하기</button>
                    @else
                        <button type="button" class="btn btn-sm btn-info ml-2" id="getCreditBtn" onclick="getCredit('{{$no}}');">새로 조회하기</button>
                    @endif
                @endif
            </div>
            @if($code != 'CERT')
                <div class="form-inline mb-1">
                    <label for="save_date" class="mr-1">조회날짜: </label>
                    <select class="form-control form-control-sm" name="save_date" id="save_date">
                        {{Func::printOption($save_date_arr, $list_no)}}
                    </select>
                    @if(!empty($guarantor_no))
                        <button type="button" class="btn btn-sm btn-info ml-2 mr-2" onclick="creditGuarantorFormSearch();">날짜 조회</button>
                    @else
                        <button type="button" class="btn btn-sm btn-info ml-2 mr-2" onclick="creditFormSearch();">날짜 조회</button>
                    @endif
                </div>
                <div class="form-inline mb-1">
                    <label for="info_ok" class="mr-1">조회동의: </label>
                    <select class="form-control form-control-sm" name="info_ok" id="info_ok">
                        {{Func::printOption($configArr['nice_info_ok_cd'], '4')}}
                    </select>
                </div>
                @if($code == '1F00D')
                    <div class="form-inline mb-1">
                        <label for="service_request_div" class="mr-1">요청구분: </label>
                        <select class="form-control form-control-sm" name="service_request_div" id="service_request_div">
                            <option value="">일반</option>
                            <option value="VS1">VS1</option>
                        </select>
                    </div>
                @endif
                <div class="form-inline">
                    <label for="search_reason" class="mr-1">조회사유: </label>
                    <select class="form-control form-control-sm" name="search_reason" id="search_reason">
                    @if($code == '1F005')
                        {{Func::printOption($configArr['nice_rsn_cb_cd'], '09')}}
                    @else
                        {{Func::printOption($configArr['nice_rsn_div_cd'], '71')}}
                    @endif
                    </select>
                    <button type="button" class="btn btn-sm btn-info ml-2" id="getCreditBtn"
                    onclick="getCredit('{{$no}}');"
                    >새로 조회하기</button>
                </div>
            @endif