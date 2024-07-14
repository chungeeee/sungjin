<?php

namespace App\Http\Controllers\Lump;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;
use Auth;
use PhpParser\Node\Stmt\Else_;

class LumpMsgController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {

    }

    /**
    * 쪽지삭제
    *
    * @param  \Illuminate\Http\Request  $request
    * @return view
    */
    public function lumpMsgAction(Request $request)
    {
        // nos말고 no만 들어올 수 있다.
        if( !isset($request->nos) && isset($request->no) )
        {
            $request->nos = Array();
            $request->nos[0] = $request->no;
        }
        if( !isset($request->nos) || sizeof($request->nos)==0 )
        {
            $array_result['rst'] = "E";
            $array_result['msg'] = "선택된 메세지가 없습니다.";
            return $array_result;
        }
        $id = Auth::user()->id;

        DB::beginTransaction();
        $error_msg = "";

        // 읽음표시
        if( $request->mode=='RECV' )
        {
            $NOS = DB::TABLE("MESSAGES")->SELECT("NO")->whereIn('NO',$request->nos)->WHERE('RECV_ID',$id)->WHERENULL('RECV_TIME')->GET()->TOARRAY();
            if( $NOS )
            {
                $recv_time = date("YmdHis");
                foreach( $NOS as $v )
                {
                    $rslt = DB::dataProcess('UPD', 'MESSAGES', ['RECV_TIME'=>$recv_time], ['NO'=>$v->no]);
                    if($rslt != 'Y')
                    {
                        break;
                    }
                }
            }
            else
            {
                $error_msg = "읽음처리 할 쪽지가 없습니다.\n";
            }
        }
        // 삭제 - 받은쪽지
        else if( $request->mode=='RDEL' )
        {
            $NOS = DB::TABLE("MESSAGES")->SELECT("NO")->whereIn('NO',$request->nos)->WHERE('RECV_ID',$id)->GET()->TOARRAY();
            if( $NOS )
            {
                $recv_time = date("YmdHis");
                foreach( $NOS as $v )
                {
                    $rslt = DB::dataProcess('UPD', 'MESSAGES', ['RECV_STATUS'=>'N'], ['NO'=>$v->no]);
                    if($rslt != 'Y')
                    {
                        break;
                    }
                }
            }
            else
            {
                $error_msg = "삭제 할 받은쪽지가 없습니다.\n";
            }
        }
        // 삭제 - 보낸쪽지
        else if( $request->mode=='SDEL' )
        {
            $NOS = DB::TABLE("MESSAGES")->SELECT("NO")->whereIn('NO',$request->nos)->WHERE('SEND_ID',$id)->GET()->TOARRAY();
            if( $NOS )
            {
                $recv_time = date("YmdHis");
                foreach( $NOS as $v )
                {
                    $rslt = DB::dataProcess('UPD', 'MESSAGES', ['SEND_STATUS'=>'N'], ['NO'=>$v->no]);
                    if($rslt != 'Y')
                    {
                        break;
                    }
                }
            }
            else
            {
                $error_msg = "삭제 할 보낸쪽지가 없습니다.\n";
            }
        }


        if( isset($rslt) && $rslt=='Y' )
        {
            DB::commit();

            $array_result['rst'] = "Y";
            $array_result['msg'] = "처리에 성공하였습니다.";
        }   
        else
        {
            DB::rollback();

            $array_result['rst'] = "E";
            $array_result['msg'] = isset($error_msg) ? $error_msg : "처리에 실패하였습니다." ;
        }

        return $array_result;
    }
}
