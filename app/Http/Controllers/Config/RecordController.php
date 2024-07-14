<?php

namespace App\Http\Controllers\config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DataList;
use App\Chung\Paging;
use Func;
use DB;
use Log;
use Auth;



class RecordController extends Controller
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
     * 녹취검색리스트 초기세팅
     *
     * @param  request
     * @return dataList
     */
    private function setRecordList(Request $request)
    {
        // 수신, 발신번호 검색 권한 체크. 권한이 없다면 readonly 활성화
        if( ($request->page_div == 'UPS' && !Func::funcCheckPermit("U030")) || ($request->page_div == 'ERP' && !Func::funcCheckPermit("E030")))
        {
            $readonly = "Y";
        }

        if(!isset($request->page_div) || $request->page_div=='')
        {
            exit;
        }
                
        $ph34 = DB::table('users')->select('ph34')->where('id',Auth::id())->value('ph34'); // 사용자 내선번호 세팅
        $ph34 = Func::chungDecOne($ph34);
        $list   = new DataList(Array("listName"=>"record","listAction"=>'/'.$request->path()));

        $list->setSearchDate('날짜검색',Array('calldate' => '통화시작일', ),'searchDt','N','Y',date("Y-m-d"),'','calldate');
        $list->setSearchDetail(Array('aa'=>'내선번호'),"aa", $ph34,Func::nvl($readonly,""));  

        
        $list->setHidden(array('page_div' => $request->page_div));
        $list->setRefresh("listRefresh();");

        return $list;
    }


    /**
     * 녹취검색 메인
     *
     * @param  request
     * @return view
     */
	public function record(Request $request)
    {

        $list   = $this->setRecordList($request);

        // listTitle : 표시할 컬럼 및 설정 (이차원배열, 설정값) - 리스트 세팅(key=>타이틀, html사용여부-1이면사용, 넓이 - % 또는 px, 정렬) 
        $list->setlistTitleCommon(Array
        (
            'src'           => Array('발신번호', 0, '', 'center', '', 'src'),
            'dst'           => Array('수신번호', 0, '', 'center', '', 'dst'),
            'starttime'     => Array('통화시작시간', 0, '', 'center', '', 'calltime'),
            'endtime'       => Array('통화종료시간', 0, '', 'center', '', 'endtime'),
            'billsec'       => Array('실통화시간(초)', 0, '', 'center', '', 'billsec'),
            // 'play'          => Array('재생', 0, '', 'center audio-simple', '', ''),
            'select'        => Array('선택', 0, '', 'center', '', ''),
        ));

        // log::info(print_R($list,true));
        $rslt['result'] = $list->getList();
        // log::info($rslt['result']);


        return view('config.recordList')->with($rslt); 

    }


    /**
     * 녹취검색리스트
     */
    public function recordList(Request $request)
    {
        // log::info($request);
        $list                   = $this->setRecordList($request);
        $param                  = $request->all();

        unset($param['searchDt'],$param['searchDtString']);
		$param['listOrder']     = isset($param['listOrder'])?$param['listOrder']:"calltime";
        $param['listOrderAsc']  = isset($param['listOrderAsc'])?$param['listOrderAsc']:"desc";
        
        $RECORD = DB::connection('record')->table('cdr')
                    ->select("*")
                    ->addSelect(DB::raw("date_part('year',calldate)||'/'||substr(CAST(calldate AS TEXT),1,7)||'/'||calldate||'/'||uniqueid||'.' as filepath"))
                    ->whereNotNull('uniqueid')
                    ;

        // 녹취서버의 컬럼이름이 _time이 아니라 따로 추가해야함
        if(!empty($request->searchDt) && !empty($request->searchDtString))
        {
            $RECORD = $RECORD->where($request->searchDt, '>=', $request->searchDtString);
            $RECORD = $RECORD->where($request->searchDt, '<=', $request->searchDtString);
        }
        else
        {
            return json_encode(['result'=>0, 'msg'=>'검색일을 입력해주세요']);
        }

        // 수신,발신번호 
        if($request->searchDetail=='' || $request->searchString=='')
        {
            return json_encode(['result'=>0, 'msg'=>'검색조건을 입력해주세요']);
        }

        unset($param['searchDetail'],$param['searchString']);
        $RECORD = $RECORD->WHERE(function($query) use($request){
            $query->where('dst',$request->searchString)
            ->orwhere('src',$request->searchString);
        });
        
        


        $RECORD = $list->getListQuery("E",'main',$RECORD,$param);
        $paging = new Paging($RECORD, $request->page, $request->listLimit, 10);

        //log::info(Func::printQuery($RECORD));
        $rslt      = $RECORD->GET();
        $cnt        = 0;
        foreach ($rslt as $v)
        {
            $filepath = "monitor/".$v->filepath.Func::getArrayName($this->arrayXflg, $v->xflg);
            $filename  = $v->uniqueid.'.'.Func::getArrayName($this->arrayXflg, $v->xflg);

            // 미리듣기 
            $yn_today = "N";
            if(str_contains($v->calldate,date("Y-m-d")) == true)
            {
                $yn_today = "Y";
            }
            
            $v->select = '파일없음';//('.$v->xflg.')';

            $v->starttime = Func::dateFormat($v->calldate.str_replace(':', '', $v->calltime));

            $r['v'][]  = $v;
            $cnt++;
        }

        // 페이징
        $r['pageList']  = $paging->getPagingHtml($request->path());
        $r['result']    = 1;
        $r['txt']       = $cnt;
        
        return json_encode($r);
    }
 
    
    /* 녹취파일 확장자
    1:  녹음 파일 없습니다.
    2:  팩스 (tif)
    3:  팩스 (pdf)
    4:  음성 (gsm)
    5:  음성 wav49 (WAV)
    6:  음성 wav (wav)
    7:  Stereo 음성 ogg (ogg)
    8:  음성 mp3 (mp3)
    */
    public $arrayXflg = Array("2"=>"tif","3"=>"pdf","4"=>"gsm","5"=>"WAV","6"=>"wav","7"=>"ogg","8"=>"mp3");
}
