@extends('layouts.master')
@section('content')

<!-- Main content -->
<section class="content">
<div class="row">
    <div class="col-md-12">
        <nav id="tabsBox">
            <div class="nav nav-pills border-bottom-0" role="tablist">
                <a class="nav-link active" id="nav-profile-tab" data-toggle="tab" href="#nav-erp" role="tab" aria-controls="nav-erp" aria-selected="false">
                    <i class="fas fa-mobile-alt mr-1"></i> 
                    <span>발송종류</span>
                </a>
                {{-- <a class="nav-link" id="nav-profile-tab" data-toggle="tab" href="#nav-sys" role="tab" aria-controls="nav-sys" aria-selected="false">
                    <i class="fas fa-mobile-alt mr-1"></i> 
                    <span>문장설정-시스템</span>
                </a> --}}
            </div>
        </nav>
        <div class="tab-content" id="nav-tabContent">

            <div class="tab-pane fade show active" id="nav-erp" role="tabpanel" aria-labelledby="nav-erp-tab">
                <div class="card card-lightblue card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                        <i class="fas fa-mobile-alt mr-1"></i> 
                            SMS 문장 설정
                        </h3>
                    </div>
                    <div class="card-body">
                        <div>
                            <p class="bg-info" style="padding:10px; font-size:11px; border-radius: 5px;">
                                ※사용가능 파싱열<br/>
                                @foreach(Vars::$arraySmsCommonParser as $parse => $value)
                                    {{$parse}}&nbsp;
                                @endforeach
                                @foreach(Vars::$arraySmsErpParser as $parse => $value)
                                    {{$parse}}&nbsp;
                                @endforeach
                            </p>
                        </div>
                        <form class="form-horizontal" role="form" name="sms_erp_form" id="sms_erp_form" method="post">
                            <input type="hidden" name="code_div" value="ERP">
                            @if(isset($arrayConf['sms_type_cd']))
                            @foreach( $arrayConf['sms_type_cd'] as $cd => $val )
                             @if(substr($cd,0,1) == "E")
                                <div class="card card-secondary">
                                    <div class="card-header pt-2 pb-2">
                                        <h3 class="card-title">
                                        <i class="fas fa-mobile-alt"></i>
                                        &nbsp; {{ $val }}
                                        </h3>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" onclick="addSmsMsg('{{$cd}}', 'ERP');">
                                                <i class="fas fa-plus"></i>&nbsp; 추가
                                            </button>
                                        </div>
                                    </div>
                                    {{-- 문자구분코드 별 내용 --}}
                                    <div class="row pl-2" id='addERP_{{$cd}}'>
                                        @if(isset($result[$cd]))
                                        @foreach( $result[$cd] as $v )
                                                <div class="card card-secondary mt-2 ml-4" style="width:150px;">
                                                    <select class="form-control select2 form-control-sm col"  name="sms_div[{{$v->no}}]">
                                                        <option value=''>발송구분</option>
                                                        {{ Func::printOption($arrayConf['sms_erp_cd'],$v->sms_div) }}
                                                    </select>
                                                    <textarea name="message_{{$v->sms_type}}[{{$v->no}}]" rows="8" style="border:0px; resize:none;background-color:#fff6db;">{{$v->message}}</textarea>
                                                </div>
                                        @endforeach
                                        @endif
                                    </div>
                                </div>
                                @endif
                            @endforeach
                            @endif
                        </form>
                    </div>
                    <div class="card-footer row m-0">
                        <div class="col-md-12 text-right">
                            @if( Func::funcCheckPermit("S031") )
                            <button type="button" class="btn btn-sm btn-info" onclick="smsAction('sms_erp_form');">저장</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="nav-sys" role="tabpanel" aria-labelledby="nav-sys-tab">
                <div class="card card-lightblue card-outline">
                    <div class="card-header">
                        <h3 class="card-title">
                        <i class="fas fa-mobile-alt mr-1"></i> 
                            SMS 문장 설정-시스템
                        </h3>
                    </div>
                    <div class="card-body">
                        <div>
                            <p class="bg-info" style="padding:10px; font-size:11px; border-radius: 5px;">
                                ※사용가능 파싱열<br/>
                                {{-- @foreach(Vars::$arraySmsCommonParser as $parse => $value)
                                    {{$parse}}&nbsp;
                                @endforeach --}}
                                {{-- @foreach(Vars::$arraySmsErpParser as $parse => $value)
                                    {{$parse}}&nbsp;
                                @endforeach --}}
                            </p>
                        </div>
                        <form class="form-horizontal" role="form" name="sms_sys_form" id="sms_sys_form" method="post">
                            <input type="hidden" name="code_div" value="SYS">
                            @if(isset($arrayConf['sms_type_cd']))
                            @foreach( $arrayConf['sms_type_cd'] as $cd => $val )
                             @if(substr($cd,0,1) == "S")
                                <div class="card card-secondary">
                                    <div class="card-header pt-2 pb-2">
                                        <h3 class="card-title">
                                        <i class="fas fa-mobile-alt"></i>
                                        &nbsp; {{ $val }}
                                        </h3>
                                        <div class="card-tools">
                                            <button type="button" class="btn btn-tool" onclick="addSmsMsg('{{$cd}}', 'SYS');">
                                                <i class="fas fa-plus"></i>&nbsp; 추가
                                            </button>
                                        </div>
                                    </div>
                                    {{-- 문자구분코드 별 내용 --}}
                                    <div class="row pl-2" id='addSYS_{{$cd}}'>
                                        @if(isset($result[$cd]))
                                        @foreach( $result[$cd] as $v )
                                                <div class="card card-secondary mt-2 ml-4" style="width:150px;">
                                                    <select class="form-control select2 form-control-sm col"  name="sms_div[{{$v->no}}]">
                                                        <option value=''>발송구분</option>
                                                        {{ Func::printOption($arrayConf['sms_sys_cd'],$v->sms_div) }}
                                                    </select>
                                                    <textarea name="message_{{$v->sms_type}}[{{$v->no}}]" rows="8" style="border:0px; resize:none;background-color:#fff6db;">{{$v->message}}</textarea>
                                                </div>
                                        @endforeach
                                        @endif
                                    </div>
                                </div>
                                @endif
                            @endforeach
                            @endif
                        </form>
                    </div>
                    <div class="card-footer row m-0">
                        <div class="col-md-12 text-right">
                            @if( Func::funcCheckPermit("S031") )
                            <button type="button" class="btn btn-sm btn-info" onclick="smsAction('sms_sys_form');">저장</button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            

        </div>
    </div>    
</div>
</section>
@endsection

@section('javascript')
<script>


var cnt = 0;
function addSmsMsg(code, div)
{
    var str = '';
    str+= '<div class="card card-secondary mt-2 ml-4" style="width:130px;">';
    str+= '<select class="form-control select2 form-control-sm col" name="sms_div[add'+cnt+']" >';
    str+= ' <option value="">발송구분</option>';
    if(div=='SYS') 
    {
        str+= "{{ Func::printOption($arrayConf['sms_sys_cd'],'') }}";
    }
    else
    {
        str+= "{{ Func::printOption($arrayConf['sms_erp_cd'],'') }}";
    }
    str+= '       </select>';
    str+= '<textarea name="message_'+code+'[add'+cnt+']" style="height:156px; border:0px; resize:none;"></textarea>';
    str+= '</div>';
    $('#add'+div+'_'+code).append(str);
    cnt++;
}

function smsAction(form_div)
{
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
    var postdata = $('#'+form_div).serialize();
    $.ajax({
        url  : "/config/smsaction",
        type : "post",
        data : postdata,
            success : function(result)
            {
                alert(result);
                location.reload();
            },
            error : function(xhr)
            {
                alert("통신오류입니다. 관리자에게 문의해주세요.");
            }
    });
}


</script>
@endsection