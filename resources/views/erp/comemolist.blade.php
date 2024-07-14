
    <button type="button" class="btn btn-xs btn-outline-info float-right mb-1" onclick="getBranchMemo('{{$branch_code ?? '' }}')"><i class="fa fa-plus-square text-info mr-1"></i>메모 추가</button>
    <table class="table table-sm table-hover card-secondary card-outline">
        <colgroup>
            <col width="10%"/>
            <col width="80%"/>
            <col width="10%"/>
        </colgroup>
        <thead>
            <tr>
                <th class="text-center">부서</th>
                <th class="text-center">메모내용</th>
                <th class="text-center">순서</th>
            </tr>
        </thead>
        <tbody>
            @if( isset($branch_memo) && count($branch_memo) > 0 )
                @foreach( $branch_memo as $idx => $v )
                    <tr onclick="getBranchMemo('{{$branch_code ?? ''}}', {{$v->no ?? ''}})" >
                        <td class="text-center">{{isset($v->branch_code)? $array_branch[$v->branch_code]['branch_name'] : '' }}</td>
                        <td class="text-center">{{$v->memo_content ?? ''}}</td>
                        <td class="text-center">{{$v->memo_order ?? ''}}</td>
                    </tr>
                @endforeach
            @else
                <tr>
                    <td colspan="13" class='text-center p-4'>등록된 메모가 없습니다.</td>
                </tr>
            @endif
            <tr><td colspan="13"></td></tr>
        </tbody>
    </table>

    <form id="form" name="form" method="post" enctype="multipart/form-data" action="" onSubmit="return false;">
        @csrf
        <div class="row">
            <input type="hidden" name="branch_code" value="{{$branch_code ?? ''}}">
            <input type="hidden" name="mode" value="">
            <input type="hidden" name="no" value="{{$selected_memo->no ?? ''}}">
            <table class="table table-bordered table-input text-xs text-center">
                <colgroup>
                    <col width="11%"/>
                    <col width="79%"/>
                    <col width="10%"/>
                </colgroup>
                <tbody>
                    <tr>
                        <td>{{isset($selected_memo->branch_code)? $array_branch[$selected_memo->branch_code]['branch_name'] : ''}}</td>
                        <td>
                            <input type="text" name="memo_content" class="form-control" value="{{$selected_memo->memo_content ?? ''}}">
                        </td>
                        <td>
                            <input type="number" name="memo_order" class="form-control" max="99" min="0" placeholder="0~99 까지의 수" value="{{$selected_memo->memo_order ?? ''}}">
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </form>
    