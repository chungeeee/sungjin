<div class="p-2">

<b>변경내역</b>
<select class="form-control form-control-sm  col-md-1 float-right mb-1" name="searchSelect" id="searchSelect" onchange="getInvestorData('investorinfochange', '',this.value);">
{{ Func::printOption($array_select,$selected) }}
</select>
<table class="table table-sm table-hover loan-info-table card-secondary card-outline">

    <colgroup>
        <col width="12%"/>
        <col width="12%"/>
        <col width="12%"/>
        <col width="32%"/>
        <col width="32%"/>
    </colgroup>

    <thead>
        <tr>
            <th class="text-center">등록일시</th>
            <th class="text-center">등록자</th>
            <th class="text-center">구분</th>
            <th class="text-center">변경전내용</th>
            <th class="text-center">변경후내용</th>
        </tr>
    </thead>

    <tbody>
        @forelse( $r as $key => $val )
            @foreach( $val as $k => $v )
                @isset($v->save_time)
                <tr>
                    <td class="text-center">{{ !empty($v->save_time) ? Func::dateFormat($v->save_time) : '' }}</td>
                    <td class="text-center">{{ $v->save_id ?? ''}}</td>
                    <td class="text-center">{{ $array_select[$selected] }}</td>
                    <td class="text-center">{{ $v->{'pre_'.$selected} ?? '' }} </td> 
                    <td class="text-center">{{ $v->$selected }}</td>
                </tr>
                @endisset
            @endforeach
        @empty
        <tr>
            <td colspan="13" class='text-center p-4'>등록된 변경사항이 없습니다.</td>
        </tr>
        @endforelse 
    </tbody>

</table>


