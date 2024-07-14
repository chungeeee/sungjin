@extends('layouts.masterPop')

@section('content')
<form id="separate_config">
    <input type="hidden" name="no" value="{{ $v->no ?? '' }}" >
    <div class="card" id="container">
        <div class="card-header">
            <h3 class="card-title">
            <i class="fas fa-envelope mr-1"></i>
            분리보관 환경설정
            </h3>
            <button type="button" class="close" onclick="window.close()">
                <span aria-hidden="true">×</span>
            </button>
        </div>
        <div class="card-body">
            <table class="table table-sm">	
                <table cellpadding=1 cellspacing=1 bgcolor=666666 width="100%" style="margin-top:3px">
                    <tr bgcolor=EEEEEE height=18 align=center>
                        <td width=4%>구분</td>
                        <td width=32%>분리보관</td>
                        <td width=32%>파기</td>
                        <td width=32%>복원분리보관</td>
                    </tr>
                    <tr>
                        <td bgcolor=EEEEEE height=18 align=center>완제</td>
                        <td bgcolor=white height=18 align=center><input type='number' name='separate_y' value="{{ $v->separate_y ?? '' }}" style='width:150px;text-align:right;'>개월</td>
                        <td bgcolor=white height=18 align=center><input type='number' name='destruct_y' value="{{ $v->destruct_y ?? '' }}" style='width:150px;text-align:right;'>개월</td>
                        <td bgcolor=white height=18 align=center><input type='number' name='restore_y' value="{{ $v->restore_y ?? '' }}" style='width:150px;text-align:right;'>일</td>
                    </tr>						
                    <tr>
                        <td bgcolor=EEEEEE height=18 align=center>매각</td>
                        <td bgcolor=white height=18 align=center><input type='number' name='separate_m' value="{{ $v->separate_m ?? '' }}" style='width:150px;text-align:right;'>개월</td>
                        <td bgcolor=white height=18 align=center><input type='number' name='destruct_m' value="{{ $v->destruct_m ?? '' }}" style='width:150px;text-align:right;'>개월</td>
                        <td bgcolor=white height=18 align=center><input type='number' name='restore_m' value="{{ $v->restore_m ?? '' }}" style='width:150px;text-align:right;'>일</td>
                    </tr>
                    <tr>
                        <td bgcolor=EEEEEE height=18 align=center>환매</td>
                        <td bgcolor=white height=18 align=center><input type='number' name='separate_h' value="{{ $v->separate_h ?? '' }}" style='width:150px;text-align:right;'>개월</td>
                        <td bgcolor=white height=18 align=center><input type='number' name='destruct_h' value="{{ $v->destruct_h ?? '' }}" style='width:150px;text-align:right;'>개월</td>
                        <td bgcolor=white height=18 align=center><input type='number' name='restore_h' value="{{ $v->restore_h ?? '' }}" style='width:150px;text-align:right;'>일</td>
                    </tr>
                </table>
            </table>
            
            <div style="display: flex; justify-content: center;">
                <button type="button" style='margin-top:20px;' class="btn btn-sm btn-default" onclick="requestAction();">
                    저장
                </button>
            </div>
        </div>
    </div>
</form>
@endsection

@section('javascript')
<script>
// 로드시 스크롤위치 조정
$(document).ready(function(){
    $(window).scrollTop(0);
});

function requestAction()
{
    $.ajaxSetup({
      headers: {
        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });

    var postdata = $('#separate_config').serialize();
    
    $.ajax({
            url  : "/config/separaterequest",
            type : "post",
            data : postdata,
        success : function(result)
        {
            if(result == 'Y'){
                alert('저장 되었습니다.');  
                opener.document.location.reload();
                self.close();
            } else {
                alert('관리자에게 문의해주세요.');  
            }
        },
        error : function(xhr)
        {
            alert('관리자에게 문의해주세요.');  
        }
    });
}
</script>
@endsection