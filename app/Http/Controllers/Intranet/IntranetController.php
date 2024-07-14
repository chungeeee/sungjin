<?php

namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Auth;
use Loan;
use Log;
use Cache;

class IntranetController extends Controller
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

	public function mainContent(Request $request)
    {
        // 10분에 한번 캐시로 저장한다.
		$array_graph = Cache::remember('Main_GraphData', 600, function()
		{
            $edate = date("Ymd");
            $sdate = substr(Loan::addMonth($edate, -6),0,6)."01";

            $p_month = date("Ym", time()-86400*date("d"));
            $n_month = substr($edate,0,6);

            // 배열 초기화
            $array_graph = [];
            for( $m = substr($sdate,0,6); $m<=substr($edate,0,6); $m = substr(Loan::addMonth($m."01",1),0,6) )
            {
                $array_graph['labels'][$m] = substr($m,2,2)."/".substr($m,4,2);

                $array_graph['NR']['01'][$m] = 0;
                $array_graph['NR']['02'][$m] = 0;
                $array_graph['NR']['03'][$m] = 0;

                $array_graph['SD']['01'][$m] = 0;
                $array_graph['SD']['02'][$m] = 0;
                $array_graph['SD']['03'][$m] = 0;
            }

            $array_graph['tot_cnt'] = 0;
            $array_graph['tot_mny'] = 0;
            $array_graph['pre_cnt'] = 0;
            $array_graph['pre_mny'] = 0;

            // 주요자금
            $rst = DB::table("loan_info")->selectRaw("substr(contract_date,1,6) AS loan_month,pro_cd,count(contract_date) AS cnt,sum(loan_money) AS mny")
                                            ->whereRaw("(contract_date BETWEEN '".$sdate."' AND '".$edate."' )")
                                            ->where('save_status',"Y")
                                            ->groupBy("substr(contract_date,1,6)")
                                            ->groupBy("pro_cd")
                                            ->orderBy("substr(contract_date,1,6)")
                                            ->get();
            $rst = Func::chungDec(["loan_info"], $rst);	// CHUNG DATABASE DECRYPT

            foreach( $rst as $val )
            {
                $array_graph['NR'][$val->pro_cd][$val->loan_month] += $val->cnt;
                $array_graph['SD'][$val->pro_cd][$val->loan_month] += $val->mny;

                if( $val->loan_month==$n_month )
                {
                    $array_graph['tot_cnt']+= $val->cnt;
                    $array_graph['tot_mny']+= $val->mny;
                }
                else if( $val->loan_month==$p_month )
                {
                    $array_graph['pre_cnt']+= $val->cnt;
                    $array_graph['pre_mny']+= $val->mny;
                }
            }

            foreach ($array_graph['SD']['01'] as $key => $value)
            {
                $array_graph['SD']['01'][$key] = round($value/10000);
            }
            foreach ($array_graph['SD']['02'] as $key => $value)
            {
                $array_graph['SD']['02'][$key] = round($value/10000);
            }
            foreach ($array_graph['SD']['03'] as $key => $value)
            {
                $array_graph['SD']['03'][$key] = round($value/10000);
            }

            $array_graph['tot_mny'] = round($array_graph['tot_mny']/10000);
            $array_graph['pre_mny'] = round($array_graph['pre_mny']/10000);
            
			return $array_graph;
		});

        //* 하단 박스

        // 공지사항 게시판
        $notice  = DB::table("board")->select(["NO","TITLE","SAVE_TIME","SAVE_ID","CLICK"])->where("save_status","Y")->where("div",'notice')->orderBy("save_time","desc")->LIMIT(5)->GET();
        $notice  = Func::chungDec(["board"], $notice);	// CHUNG DATABASE DECRYPT

        // 읽지않은 메세지
        $message = DB::table("messages")->select("*")->where("RECV_ID",Auth::user()->id)->where('recv_status',"Y")->whereNull('RECV_TIME')->where('COALESCE(RESERVE_TIME,SEND_TIME)',"<=",date("YmdHis"))->orderBy("NO","DESC")->limit(5)->get();
        $message = Func::chungDec(["messages"], $message);	// CHUNG DATABASE DECRYPT

        return view('intranet.mainContent')->with('notice',$notice)->with('message',$message)->with('array_graph',$array_graph);
    }


	public function setHeadMenu(Request $request)
    {
        $id = Auth::user()->id;
        $code  = $request->input('code');
        $mode  = $request->input('mode');

        // 권한 검사

        if( $mode=="ADD" )
        {
            $mymn = DB::TABLE('CONF_MENU_HEAD')->SELECT(DB::raw('min(seq) as min_seq, max(seq) as max_seq, count(*) as cnt'))->WHERE('user_id',$id)->first();
            if( $mymn->cnt>=8 )
            {
                $rslt = DB::dataProcess('DEL', 'CONF_MENU_HEAD', [], ['user_id'=>$id,'seq'=>$mymn->min_seq]);
            }
            $seq = $mymn->max_seq + 1;
           
            $rslt = DB::dataProcess('UST', 'CONF_MENU_HEAD', ['user_id'=>$id,'menu_cd'=>$code,'seq'=>$seq]);
        }
        else if( $mode=="DEL" )
        {
            $rslt = DB::dataProcess('DEL', 'CONF_MENU_HEAD', [], ['user_id'=>$id,'menu_cd'=>$code]);
        }
        return $rslt;
    }

	public function getHeadMenu(Request $request)
    {
        $tmp = Func::getMyMenu();
        $array_head_menu = $tmp['HEAD'];
        
        $rslt = "";
        if( sizeof($array_head_menu)>0 )
        {
            foreach( $array_head_menu as $val )
            {
                $rslt.= '<li class="nav-item d-none d-sm-inline-block">';
                $rslt.= '<a href="'.$val['link'].'" class="nav-link">'.$val['name'].'</a>';
                $rslt.= '</li>';
            }
        }
        return $rslt;
    }

    public function mainDashBoard(Request $request)
    {

        // 10분에 한번 캐시로 저장한다.
		$array_loan_graph = Cache::remember('Main_LoanGraphData', 600, function()
		{

            $edate = date("Ymd");
            $sdate = substr(Loan::addMonth($edate, -6),0,6)."01";

            // 배열 초기화
            $array_loan_graph = [];
            for( $m = substr($sdate,0,6); $m<=substr($edate,0,6); $m = substr(Loan::addMonth($m."01",1),0,6) )
            {
                $array_loan_graph['labels'][$m] = substr($m,2,2)."/".substr($m,4,2);

                $d_money[$m] = 0;
                $d_cnt[$m] = 0;

                $c_money[$m] = 0;
                $c_cnt[$m] = 0;
            }

            $array_loan_graph['total_money'] = 0;
            $array_loan_graph['d_money'] = $d_money;
            $array_loan_graph['d_cnt'] = $d_cnt;
            $array_loan_graph['c_money'] = $c_money;
            $array_loan_graph['c_cnt'] = $c_cnt;
            $array_loan_graph['total_money'] = round($array_loan_graph['total_money']/1000000);
            
			return $array_loan_graph;
		});

        // 약정일
        $arr_contract_day = Func::getConfigArr('contract_day');


        // 하단 박스
        $notice  = DB::TABLE("BOARD")->SELECT(["NO","TITLE","SAVE_TIME","SAVE_ID","CLICK"])->WHERE("SAVE_STATUS","Y")->WHERE("DIV",'notice')->ORDERBY("SAVE_TIME","DESC")->LIMIT(5)->GET();
        $notice = Func::chungDec(["BOARD"], $notice);	// CHUNG DATABASE DECRYPT
        $message = DB::TABLE("MESSAGES")->SELECT("*")->WHERE("RECV_ID",Auth::user()->id)->WHERE('RECV_STATUS',"Y")->WHERENULL('RECV_TIME')->WHERE('COALESCE(RESERVE_TIME,SEND_TIME)',"<=",date("YmdHis"))->ORDERBY("NO","DESC")->limit(5)->get();
        $message = Func::chungDec(["MESSAGES"], $message);	// CHUNG DATABASE DECRYPT

        return view('intranet.mainDashBoard')->with('notice',$notice)->with('message',$message)->with('array_loan_graph',$array_loan_graph)->with('arr_contract_day', $arr_contract_day);
    }


}