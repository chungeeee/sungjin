@extends('layouts.master')
@section('content')


<!-- Main content -->
<section class="content">
<div class="container-fluid">


    <div class="row" >
        <div class="col-md-3">
            <div class="card card-lightblue">
                <div class="card-header">
                    <h3 class="card-title" style="width:100%">부서 조직도</h3>
                </div>
                <div class="card-body" id="permitBranchList" style="height: 720px;">

                    <div class="card-body table-responsive p-0" style="height: 450px;">
                        <table class="table table-sm table-hover table-head-fixed text-nowrap">
                            <thead>
                                <tr>
                                    <th>부서코드</th>
                                    <th>부서명</th>
                                </tr>
                            </thead>
                            <tbody>

                            @forelse( $branch as $value )

                                <tr onclick="getBranchMemo('{{ $value['code'] }}');">
                                    <td>{{ $value['code'] }}</td>
                                    <td style="padding-left: {{ $value['branch_depth']*40 }}px;">{{ $value['branch_name'] }}</td>
                                </tr>

                            @empty

                                <tr>
                                <td colspan=2 class='text-center p-4'>등록된 부서정보가 없습니다.</td>
                                </tr>

                            @endforelse

                            </tbody>
                        </table>
                    </div>

                </div>
            </div>
        </div>

        <div class="col-md-9">
            <div class="card card-lightblue">
                
                <div class="card-header" >
                    <h3 class="card-title" style="width:100%">부서별 메모 관리</h3>
                </div>
                <div class="card-body" id="memoDiv"  style="height: 670px;">

                </div>
                
                <div class="card-footer text-right">
                
                    <button class="btn btn-sm btn-danger" onclick="comemoAction('DEL');">삭제</button>
                    <button class="btn btn-sm btn-info" onclick="comemoAction('');">저장</button>

                </div>
            </div>
        </div>
    </div>
</div>
</section>
@endsection


@section('javascript')
<script>

    function getBranchMemo(code, no)
    {
        $.ajaxSetup({
            headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $("#memoDiv").html(loadingString);

        $.post("/erp/comemolist", {code:code, no:no}, function(data) {

            $("#memoDiv").html(data);


        });

    }

    function comemoAction(mode)
    {
        if( !confirm("정말로 작업 하시겠습니까?") )
        {
            return false;
        }

        form.mode.value = mode;

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        $.ajax({
            url  : "/erp/comemoaction",
            type : "post",
            data : $('#form').serialize(),
            success : function(result) {

                var r = JSON.parse(result);

                alert(r['msg']);
                getBranchMemo(r['code']);
            },
            error : function(xhr) {
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
        });

    }



</script>
@endsection