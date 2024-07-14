@extends('layouts.masterPop')

@section('content')
    <table width="100%" border="0" cellspacing="0" cellpadding="0" id='header'>
    <tr>
    <td height="30" align=right style="padding-right:5px;filter:alpha(opacity=70)" bgcolor="#AAAAAA">
        <a href="javascript:printOk();" style="color:black"><img src="/img/printer.png" border=0 align=absmiddle style="margin-bottom:3px"> 인쇄하기</a>
    </td>
    </tr>
    </table>

    @if( View::exists('print/'.$form) )
        @foreach($nos as $no=>$v)
            
            @include('print/'.$form)

            <H1 style="page-break-before: always;">
            <br style="height:0; line-height:0">
            </H1>
        @endforeach
    @endif
@endsection