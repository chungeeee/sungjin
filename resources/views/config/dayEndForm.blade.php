<form class="form-horizontal pr-5" role="form" name="dayend_form" id="dayend_form" method="post">
    <input type="hidden" id="mode" name="mode" value="{{ $mode }}">
    <input type="hidden" id="no" name="no" value="{{ $v->no ?? ''}}">

    <div class="form-group row">
        <label for="code" class="col-sm-2 col-form-label text-center">마감일</label>
        <div class="col-sm-10 mt-1">
            @if(isset($v->no) && $v->no>0)
                {{ $v->end_date ?? '' }}
            @else
                {{ date("Y-m-d") }}
            @endif 
        </div>
    </div>

    @if(!empty($contents) )
    <div class="form-group row">
        
        <label for="id" class="col-sm-2 col-form-label text-center">마감정보</label>
        <div class="col-sm-10 mt-1">
            <table class="table table-sm table-hover loan-info-table card-secondary card-outline table-bordered">
                <thead>
                    <tr class="text-center">
                        <th>순번</th>
                        <th>상품명</th>
                        <th>대출구분</th>
                        <th>대출건수</th>
                        <th>대출금액</th>
                    </tr>
                </thead>
                <tbody>
                    
                    @foreach( $contents as $no => $v2 )
                    <tr class="text-center">
                        <td>{{ $no+1 }}</td>
                        <td>{{ $v2['pro_cd'] }}</td>
                        <td>{{ $v2['app_type_cd'] }}</td>
                        <td>{{ $v2['cnt'] }}</td>
                        <td>{{ $v2['loan_money'] }}</td>
                    </tr>
                    @endforeach

                    <tr class="text-center bg-lightblue">
                        <td colspan="3">합계</td>
                        <td>{{ $v2['total_cnt'] }}</td>
                        <td>{{ $v2['total_money'] }}</td>
                    </tr>
                
                </tbody>
            </table>
            <div class="text-right">
            (상기금액은 실제 마감등록 시점에 따라 일부 변경될 수 있습니다)
            <p>
            </div>
        </div>
    </div>
    @endif
    
    <div class="form-group row">
        <label for="code" class="col-sm-2 col-form-label text-center">마감처리</label>
        <div class="col-sm-10">
            [{{ $manager_code_name }}] 
            @if($mode=='INS')
            <button type="button" class="btn btn-sm btn-info ml-2" onclick="dayendAction('{{$mode}}');">마감등록</button>
            @elseif($mode=='UPD')
            [ 마감등록시간 : {{ $v->save_time }} ]
            [ 마감등록자 : {{ $v->save_id }} ]
            <button type="button" class="btn btn-sm btn-danger ml-2" onclick="dayendAction('{{$mode}}');" @if($v->end_date!=date("Y-m-d")) disabled @endif>마감취소</button>
            @endif
        </div>
    </div>
    
    
    
    </form>
    