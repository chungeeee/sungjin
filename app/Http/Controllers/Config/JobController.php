<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Log;

class JobController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(Request $request)
    {
        //
    }



    /**
     * 직업코드관리 메인화면
     *
     * @param  Void
     * @return view
     */
	public function jobCode()
    {
        return view('config.jobCode');
    }

    /**
     * 직업코드 리스트
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function jobCodeList(Request $request)
    {
        //상위코드 
        $jobCode = $request->input('jobcode');
        $nowCode = $jobCode;        
        $jobType = $jobDiv = "";
        
        // 하위 코드 검색 컬럼 만들기
        if(isset($jobCode)){
            $jobType = substr($jobCode,0,1);
            $jobDiv = rtrim(substr($jobCode,1),'0');

            // 첫자리는 대분류, 이후로는 2자리씩 중분류, 소분류로 잡는다. 10 과같이 rtrim 처리 후 1로 바뀌는 경우 아래로직으로 10으로 다시 치환한다.
            if(strlen($jobDiv) % 2 != 0)
            {
                $jobDiv = str_pad($jobDiv,(ceil(strlen($jobDiv)/2)*2),"0",STR_PAD_RIGHT);
            }
            $jobSeq = ceil(strlen($jobType.$jobDiv) / 2)+1;
            $col = $jobType.$jobDiv.'%'.str_pad("",(3-$jobSeq)*2,"0",STR_PAD_RIGHT);
        }
        else
        {
            $jobSeq = 1;
            $col = '%'.str_pad("",(3-$jobSeq)*2,"0",STR_PAD_RIGHT);
        }

        Log::info("jobType [".$jobType."] , jobDiv [".$jobDiv."] , jobSeq[".$jobSeq."] , JOBCODE like [".$col."] , JOBCODE != [".$nowCode."]");
        $rslt = DB::TABLE("CONF_JOBCODE")->SELECT("*")->where('JOBCODE', 'like',$col)->WHERE('JOBCODE','!=',$nowCode)->ORDERBY("JOBCODE")->GET();
        $rslt = Func::chungDec(["CONF_JOBCODE"], $rslt);	// CHUNG DATABASE DECRYPT

        return view('config.jobCodeList')->with(['jobCode'=>$jobCode,'result'=>$rslt,'jobSeq'=>$jobSeq]);
    }



    /**
     * 직업코드 추가, 수정
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function jobAction(Request $request)
    {
        $param      = $request->input();
        $oldCode    = $param['oldJobCode'];
        $code       = $param['jobCode'];
        $mode       = $param['mode'];
        
        //삭제
        if( $mode=="DEL" )
        {       
            $jobType = substr($code,0,1);
            $jobDiv = rtrim(substr($code,1),'0');

            // 첫자리는 대분류, 이후로는 2자리씩 중분류, 소분류로 잡는다. 10 과같이 rtrim 처리 후 1로 바뀌는 경우 아래로직으로 10으로 다시 치환한다.
            if(strlen($jobDiv) % 2 != 0)
            {
                $jobDiv = str_pad($jobDiv,(ceil(strlen($jobDiv)/2)*2),"0",STR_PAD_RIGHT);
            }
            $jobSeq = ceil(strlen($jobType.$jobDiv) / 2)+1;
            $col = $jobType.$jobDiv.'%'.str_pad("",(3-$jobSeq)*2,"0",STR_PAD_RIGHT);
            
            // 하위 코드 있을 경우 삭제 X
            $v = DB::TABLE("CONF_JOBCODE")->SELECT("count(*) as cnt")->WHERE('JOBCODE','like',$col)->WHERE('JOBCODE','!=',$code)->FIRST(); 
            if( $v->cnt>0 )
            {
                return "사용중인 하부코드가 존재하여 삭제할 수 없습니다.";
            }else{
                $rslt = DB::dataProcess('DEL', 'CONF_JOBCODE',array(),["jobcode"=>$code]);      
            }
        }else{
            if($oldCode!=$code){
                //존재하는 코드 여부 확인
                $v = DB::TABLE("CONF_JOBCODE")->SELECT("count(*) as cnt")->WHERE('JOBCODE',$code)->FIRST(); 
                if( $v->cnt>0 )
                {
                    return "사용중인 직업코드가 존재하여 변경할 수 없습니다.";
                }
            }
            if(!isset($oldCode)){  //INS
                $rslt = DB::dataProcess('INS', 'CONF_JOBCODE', $param);       
            }else{
                $rslt = DB::dataProcess('UPD', 'CONF_JOBCODE',Array('jobcode'=>$code,'jobname'=>$param['jobName']),["jobcode"=>$oldCode]);     
            }
        }
 
        //결과 메세지 구분
        switch ($rslt) {
            case "Y":
                $msg = "정상처리되었습니다.";
                break;
            case "N":
                $msg = "처리에 실패하였습니다.";
                break;
            case "E":
                $msg = "등록정보가 올바르지 않습니다.";
                break;
            default:
                $msg = "기타오류";
        }
        
        return $msg;
    }


    
    /**
     * 직업코드관리 팝업화면
     *
     * @param  Void
     * @return view
     */
	public function jobCodePop(Request $request)
    {

        $jobId          = $request->input('jobId');
        $arrCodeList    = null;
        
        $rslt = DB::TABLE("CONF_JOBCODE")->SELECT("*")->ORDERBY("JOBCODE")->GET();
        $rslt = Func::chungDec(["CONF_JOBCODE"], $rslt);	// CHUNG DATABASE DECRYPT
        
        foreach($rslt as $v){
            $jobType = substr($v->jobcode,0,1);
            $jobDiv = rtrim(substr($v->jobcode,1),'0');

            // 첫자리는 대분류, 이후로는 2자리씩 중분류, 소분류로 잡는다. 10 과같이 rtrim 처리 후 1로 바뀌는 경우 아래로직으로 10으로 다시 치환한다.
            if(strlen($jobDiv) % 2 != 0)
            {
                $jobDiv = str_pad($jobDiv,(ceil(strlen($jobDiv)/2)*2),"0",STR_PAD_RIGHT);
            }

            $jobSeq = ceil(strlen($jobType.$jobDiv) / 2);
            $pjobDiv = substr($v->jobcode, 0, 1+(($jobSeq-2)*2));
            
            if($jobSeq>1){
                $pcode                          = $pjobDiv.str_pad("",5-strlen($pjobDiv),"0",STR_PAD_RIGHT);
                $arrCodeList[$jobSeq][$pcode][]    = $v;
            }else{
                $arrCodeList[$jobSeq]['A'][]       = $v;
            }
        }

        Log::info(print_r($arrCodeList, true));

        return view('config.jobCodePop')->with(['arrCodeList'=>$arrCodeList,'jobId'=>$jobId]);
    }
}
