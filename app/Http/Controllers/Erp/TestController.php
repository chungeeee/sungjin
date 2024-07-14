<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Auth;
use Func;
use DataList;
use Log;

class TestController extends Controller
{
    public function searchNo(Request $request) {
        $inputValue = $request->input('inputValue');
    
        if(strpos($inputValue, '-')) {
            $arr = explode('-', $inputValue);
            if($arr[1] or $arr[1] == 0) {
                $result = DB::table('loan_info')->select('no')->where('loan_usr_info_no', $arr[0])->where('inv_seq', $arr[1])->where('save_status', 'Y')->first();
            } else {
                $result = DB::table('loan_info')->select('no')->where('loan_usr_info_no', $arr[0])->where('save_status', 'Y')->first();
            }
        } 
        else {
            return null;
        }

        if ($result != null) {
            return $result->no;
        } else {
            return null;
        }
    }
    
}