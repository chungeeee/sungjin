            <div class="modal-header">
                <h4 class="modal-title">송달료 정보</h4>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body">
                <form name="lawPostageForm" id="lawPostageForm">
                    @csrf
                    <input type="hidden" name="no" id="postageNo" value="{{ $v->no ?? '' }}">
                    <input type="hidden" name="action" id="postageAction" value="">
                    <div class="row">
                        <div class="col-6">
                            <label class="mr-1">납부번호</label>: <span id="pay_no">{{ $v->pay_no ?? '' }}</span>
                        </div>
                        <div class="col-6">
                            <label class="mr-1">납부일자</label>: <span>{{ $v->pay_date ?? '' }}</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <label class="mr-1">납부금액</label>: <span id="pay_money">{{ $v->pay_money ?? '' }}</span>
                        </div>
                        <div class="col-6">
                            {{-- <label class="mr-1">잔액</label>: <span class="comma" id="pay_balance">{{ $v->pay_balance ?? '' }}</span> --}}
                            <label class="mr-1">사용여부</label>: <span>{!! $v->pay_balance ?? '' !!}</span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-6">
                            <label class="mr-1">등록자</label>: <span>{{ $v->reg_id ?? '' }}</span>
                        </div>
                        <div class="col-6">
                            @if(!empty($v->save_id))
                            <label class="mr-1">사용자</label>: <span>{{ $v->save_id ?? '' }}</span>
                            @endif
                        </div>
                    </div>

                    @if(!empty($v->using_nos))
                    <div class="row">
                        <div class="col-12">
                            <label class="mr-1">사용중인 계약번호</label>: <span>{!! $v->using_nos ?? '' !!}</span>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-6">
                            <label class="mr-1">사건번호</label>: <span><input type="text" class="form-control form-control-sm mr-1" name="event_no" maxlength=20 value="{{ $v->event_no ?? '' }}" placeholder="사건번호"></span>
                        </div>
                        <div class="col-6">
                            <label class="mr-1">법원명 : </label>
                            <span>                
                                <select class="form-control form-control-sm" data-size="10" name="court_cd" title="선택">
                                    <option value="">법원을 선택해주세요.</option>
                                    {{ Func::printOption($configArr['court_cd'], isset($v->court_cd) ? $v->court_cd : '') }}
                                </select>
                            </span>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-12">
                            <label class="mr-1">비고</label>: <span><textarea class="form-control form-control-sm" name="bigo">{{ $v->bigo ?? '' }}</textarea></span>
                        </div>
                    </div>

                    {{-- @if($type == 'Pop')
                    <div class="row mt-2">
                        <div class="col-12 form-inline">
                            <label class="mr-1" for="use_money">사용할 금액: </label>
                            <input type="text" class="form-control form-control-sm moneyformat" id="use_money" name="use_money" value="{{ $v->pay_balance ?? '' }}" onkeyup='onlyNumber(this);' inputmode="numeric">
                        </div>
                    </div>
                    @endif --}}
                </form>
            </div>

            <div class="modal-footer justify-content-between">
                <button type="button" class="btn btn-sm btn-default" data-dismiss="modal">Close</button>
                <div class="p-0">
                    <button type="button" class="btn btn-sm btn-primary" onclick="postageAction('UPD');">송달료 수정</button>
                    @if($type == 'Pop')
                    <button type="button" class="btn btn-sm btn-info" onclick="usePostage('{{ $v->no ?? '' }}', '{{ $v->pay_money ?? '' }}');">사용</button>
                    @else
                    <button type="button" class="btn btn-sm btn-danger" onclick="postageAction('DEL');">송달료 삭제</button>
                    @endif
                </div>
            </div>