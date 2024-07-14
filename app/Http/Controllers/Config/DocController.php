<?php

namespace App\Http\Controllers\Config;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Log;
use Func;
use Auth;
use App\Chung\Paging;

class DocController extends Controller
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
     * 첨부서류관리 메인화면
     *
     * @param  Void
     * @return view
     */
	public function doc(Request $request)
    {

        return view("config.doc");
    }

    /**
     * 첨부서류관리 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function docList(Request $request)
    {

    }

   
}
 