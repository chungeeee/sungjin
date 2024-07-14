<?php

namespace App\Http\Controllers\Erp;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;
use Vars;
use Auth;
use Log;
use App\Chung\Paging;

class SearchController extends Controller
{
    /**
     * 연계검사
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function search(Request $request)
    {
        
        $exp = explode('/', $_SERVER['REQUEST_URI']);
        $requestUri = $exp[1];

        $searchStr = (isset($request->searchStr)) ? $request->searchStr:'';
        $custInfoNo = (isset($request->custInfoNo)) ? $request->custInfoNo:'';
        $arrayBranch = Func::getBranch();
        $myBranch = Auth::user()->branch_code;
        
        // UC매니저에서 넘어오는 경우임.
        if($searchStr=='' && isset($request->custom_number))
        {
            $searchStr = $request->custom_number;
        }

        return view('erp.search')
                ->with('arrayBranch', $arrayBranch)
                ->with('myBranch', $myBranch)
                ->with('searchStr', $searchStr)
                ->with('custInfoNo', $custInfoNo)
                ->with('requestUri', $requestUri)
                ;
    }

    public function bondSearch(Request $request)
    {
        
        $exp = explode('/', $_SERVER['REQUEST_URI']);
        $requestUri = $exp[1];

        $searchStr = (isset($request->searchStr)) ? $request->searchStr:'';
        $custInfoNo = (isset($request->custInfoNo)) ? $request->custInfoNo:'';
        $arrayBranch = Func::getBranch();
        $myBranch = Auth::user()->branch_code;
        
        // UC매니저에서 넘어오는 경우임.
        if($searchStr=='' && isset($request->custom_number))
        {
            $searchStr = $request->custom_number;
        }

        return view('erp.bondsearch')
                ->with('arrayBranch', $arrayBranch)
                ->with('myBranch', $myBranch)
                ->with('searchStr', $searchStr)
                ->with('custInfoNo', $custInfoNo)
                ->with('requestUri', $requestUri);
    }

    /**
     * 신청원장 연계검사 용 ( 주민번호, 성명, 집전화, 핸드폰번호, 회사명 검색 )
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function searchAction2(Request $request)
    {
        if(isset($request->loanAppNo))
        {
            $custInfoNo   = 0;
            $loanAppNo    = $request->loanAppNo;
        }        
        else if(isset($request->custInfoNo))
        {
            $custInfoNo   = $request->custInfoNo;
            $loanAppNo    = 0;
        }

        if( isset($request->order_colm) && isset($request->order_type) )
        {
            $order_colm = $request->order_colm;
            $order_type = $request->order_type;
        }
        else
        {
            $order_colm = "NAME";
            $order_type = "ASC";
        }

        $searchTypeArr = array(
            'ci_ssn'        =>  array('list' => '회원정보',       'title' => '주민번호',  'eu' => 'ERP'),
            'lig_ssn'       =>  array('list' => '보증인정보',     'title' => '주민번호',  'eu' => 'ERP'),
            'la_ssn'        =>  array('list' => '심사회원정보',   'title' => '주민번호',  'eu' => 'UPS'),
            'lag_ssn'       =>  array('list' => '심사보증인정보', 'title' => '주민번호',  'eu' => 'UPS'),
            
            'ci_birth'      =>  array('list' => '회원정보',       'title' => '생년월일',  'eu' => 'ERP'),
            'lig_birth'     =>  array('list' => '보증인정보',     'title' => '생년월일',  'eu' => 'ERP'),
            'la_birth'      =>  array('list' => '심사회원정보',   'title' => '생년월일',  'eu' => 'UPS'),
            'lag_birth'     =>  array('list' => '심사보증인정보', 'title' => '생년월일',  'eu' => 'UPS'),

            'ci_ph_4'       =>  array('list' => '회원정보',       'title' => '번호뒷자리',  'eu' => 'ERP'),
            'lig_ph_4'      =>  array('list' => '보증인정보',     'title' => '번호뒷자리',  'eu' => 'ERP'),
            'la_ph_4'       =>  array('list' => '심사회원정보',   'title' => '번호뒷자리',  'eu' => 'UPS'),
            'lag_ph_4'      =>  array('list' => '심사보증인정보', 'title' => '번호뒷자리',  'eu' => 'UPS'),

            'ci_ph'         =>  array('list' => '회원정보',       'title' => '전화번호',  'eu' => 'ERP'),
            'lig_ph'        =>  array('list' => '보증인정보',     'title' => '전화번호',  'eu' => 'ERP'),
            'la_ph'         =>  array('list' => '심사회원정보',   'title' => '전화번호',  'eu' => 'UPS'),
            'lag_ph'        =>  array('list' => '심사보증인정보', 'title' => '전화번호',  'eu' => 'UPS'),

            'ci_ph2'         =>  array('list' => '회원정보',       'title' => '휴대폰번호',  'eu' => 'ERP'),
            'lig_ph2'        =>  array('list' => '보증인정보',     'title' => '휴대폰번호',  'eu' => 'ERP'),
            'la_ph2'         =>  array('list' => '심사회원정보',   'title' => '휴대폰번호',  'eu' => 'UPS'),
            'lag_ph2'        =>  array('list' => '심사보증인정보', 'title' => '휴대폰번호',  'eu' => 'UPS'),

            'ci_ph3'         =>  array('list' => '회원정보',       'title' => '직장전화',  'eu' => 'ERP'),
            'lig_ph3'        =>  array('list' => '보증인정보',     'title' => '직장전화',  'eu' => 'ERP'),
            'la_ph3'         =>  array('list' => '심사회원정보',   'title' => '직장전화',  'eu' => 'UPS'),
            'lag_ph3'        =>  array('list' => '심사보증인정보', 'title' => '직장전화',  'eu' => 'UPS'),

            'ci_ph4'         =>  array('list' => '회원정보',       'title' => '기타전화',  'eu' => 'ERP'),
            'lig_ph4'        =>  array('list' => '보증인정보',     'title' => '기타전화',  'eu' => 'ERP'),
            'la_ph4'         =>  array('list' => '심사회원정보',   'title' => '기타전화',  'eu' => 'UPS'),
            'lag_ph4'        =>  array('list' => '심사보증인정보', 'title' => '기타전화',  'eu' => 'UPS'),

            'ci_name'       =>  array('list' => '회원정보',       'title' => '성명',      'eu' => 'ERP'),
            'lig_name'      =>  array('list' => '보증인정보',     'title' => '성명',      'eu' => 'ERP'),
            'la_name'       =>  array('list' => '심사회원정보',   'title' => '성명',      'eu' => 'UPS'),
            'lag_name'      =>  array('list' => '심사보증인정보', 'title' => '성명',      'eu' => 'UPS'),

            'ci_com_name'   =>  array('list' => '회원정보',       'title' => '직장명',    'eu' => 'ERP'),
            'lig_com_name'  =>  array('list' => '보증인정보',     'title' => '직장명',    'eu' => 'ERP'),
            'la_com_name'   =>  array('list' => '심사회원정보',   'title' => '직장명',    'eu' => 'UPS'),
            'lag_com_name'  =>  array('list' => '심사보증인정보', 'title' => '직장명',    'eu' => 'UPS'),
        );

        if($loanAppNo!='')
        {
            $searchArr = DB::TABLE("LOAN_APP la")
                            ->LEFTJOIN('LOAN_APP_EXTRA lae', 'la.no', '=', 'lae.loan_app_no')
                            ->SELECT("la.ssn", "la.name", "la.pro_cd", "lae.ph31", "lae.ph32", "lae.ph33", "lae.ph41", "lae.ph42", "lae.ph43", "lae.ph21", "lae.ph22", "lae.ph23", "lae.com_name", "la.manager_code")
                            ->WHERE(["la.no" => $loanAppNo,"la.save_status"=>"Y"])->first();
            $searchArr = Func::chungDec(["LOAN_APP","LOAN_APP_EXTRA"], $searchArr);	// CHUNG DATABASE DECRYPT
        }
        else if($custInfoNo!='')
        {
            $searchArr = DB::TABLE("CUST_INFO la")
                            ->LEFTJOIN('CUST_INFO_EXTRA lae', 'la.no', '=', 'lae.cust_info_no')
                            ->SELECT("la.ssn", "la.name", "lae.ph31", "lae.ph32", "lae.ph33", "lae.ph41", "lae.ph42", "lae.ph43", "lae.ph21", "lae.ph22", "lae.ph23", "lae.com_name")
                            ->WHERE(["la.no" => $custInfoNo,"la.save_status"=>"Y"])->first();
            $searchArr = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA"], $searchArr);	// CHUNG DATABASE DECRYPT
        }

        $_erp_common = function(){
            $BOXC = DB::table("loan_usr_info as ci")->SELECT('*')->WHERE('ci.save_status', 'Y'); 
            return $BOXC;
        };

        // 검색하지 않을 것들
        if($searchArr->ph22=='0000' && $searchArr->ph23=='0000' || $searchArr->ph22=='1111' && $searchArr->ph23=='1111')
        {
            $searchArr->ph21 = $searchArr->ph22 = $searchArr->ph23 = '';
        }
        if($searchArr->com_name=='직장명')
        {
            $searchArr->com_name = '';
        }

        if($searchArr->ph32=='1212' && $searchArr->ph33=='3434')
        {
            $searchArr->ph31 = $searchArr->ph32 = $searchArr->ph33 = '';
        }

        
                
        $_ups_common = function(){
            return DB::TABLE('LOAN_APP la')
                    ->JOIN('LOAN_APP_EXTRA lae','la.no','=','lae.loan_app_no')
                    ->SELECT('la.no as loan_app_no', 'la.pro_cd', 'la.name', 'la.app_date', 'la.app_money', 'la.status', 'lae.ph31','lae.ph32','lae.ph33', 'lae.ph41','lae.ph42','lae.ph43', 'lae.ph21','lae.ph22','lae.ph23','lae.com_name', 'la.ssn', 'la.manager_code')
                    ->WHERE('la.save_status','Y')
                    ->ORDERBY('la.no','desc');
        };

        if( isset($searchArr) )
        {
            if( !empty($searchArr->ssn) )
            {
                $searchTypeArr['ci_ssn']['str']  = Func::ssnFormat($searchArr->ssn, 'Y');
                $searchTypeArr['lig_ssn']['str'] = Func::ssnFormat($searchArr->ssn, 'Y');
                $searchTypeArr['la_ssn']['str']  = Func::ssnFormat($searchArr->ssn, 'Y');
                $searchTypeArr['lag_ssn']['str'] = Func::ssnFormat($searchArr->ssn, 'Y');

                $result['ci_ssn'] = $_erp_common()->WHERE('ci.ssn', Func::chungEncOne($searchArr->ssn))->WHERE('ci.no','!=',$custInfoNo)->get()->toArray();
                $result['ci_ssn'] = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $result['ci_ssn']);	// CHUNG DATABASE DECRYPT
                
                $result['lig_ssn'] = $_erp_common()

                                    ->WHEREIN('li.no', function($query) use($searchArr){
                                        $query  ->SELECT('loan_info_no')
                                                ->FROM('LOAN_INFO_GUARANTOR')
                                                ->WHERE('SAVE_STATUS', 'Y')
                                                ->WHERE('ssn', Func::chungEncOne($searchArr->ssn));
                                    })->get()->toArray();
                $result['lig_ssn'] = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $result['lig_ssn']);	// CHUNG DATABASE DECRYPT

                $result['la_ssn'] = $_ups_common()->WHERE('la.ssn', Func::chungEncOne($searchArr->ssn))->WHERE('la.no','!=',$loanAppNo)->get()->toArray();
                $result['la_ssn'] = Func::chungDec(["LOAN_APP","LOAN_APP_EXTRA"], $result['la_ssn']);	// CHUNG DATABASE DECRYPT

                $result['lag_ssn'] = $_ups_common()
                                    ->WHEREIN('la.no', function($query) use($searchArr){
                                        $query  ->SELECT('loan_app_no')
                                                ->FROM('LOAN_APP_GUARANTOR')
                                                ->WHERE('save_status', 'Y')
                                                ->WHERE('ssn', Func::chungEncOne($searchArr->ssn));
                                    })->get()->toArray();
                $result['lag_ssn'] = Func::chungDec(["LOAN_APP","LOAN_APP_EXTRA"], $result['lag_ssn']);	// CHUNG DATABASE DECRYPT
            }

            if( !empty($searchArr->name) )
            {
                $searchTypeArr['ci_name']['str']  = $searchArr->name;
                $searchTypeArr['lig_name']['str'] = $searchArr->name;
                $searchTypeArr['la_name']['str']  = $searchArr->name;
                $searchTypeArr['lag_name']['str'] = $searchArr->name;

                //  이름검색
                // $result['ci_name'] = $_erp_common()->WHERE('ci.name', Func::chungEncOne($searchArr->name))->WHERE('ci.no','!=',$custInfoNo)->get()->toArray();
                $erp = $_erp_common();
                $erp = Func::encLikeSearch($erp, 'ci.name', $searchArr->name, 'all');
                $result['ci_name'] = $erp->WHERE('ci.no','!=',$custInfoNo)->get()->toArray();

                $result['ci_name'] = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $result['ci_name']);	// CHUNG DATABASE DECRYPT

                
                //  보증인 이름 검색
                $result['lig_name'] = $_erp_common()
                                        ->WHEREIN('li.no', function($query) use($searchArr){
                                            $query  ->SELECT('loan_info_no')
                                                    ->FROM('LOAN_INFO_GUARANTOR')
                                                    ->WHERE('SAVE_STATUS', 'Y')
                                                    ->WHERE('name', Func::chungEncOne($searchArr->name));
                                        })->get()->toArray();
                $result['lig_name'] = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $result['lig_name']);	// CHUNG DATABASE DECRYPT

                //  이름검색
                $result['la_name'] = $_ups_common()->WHERE('la.name', Func::chungEncOne($searchArr->name))->WHERE('la.no','!=',$loanAppNo)->get()->toArray();
                $result['la_name'] = Func::chungDec(["LOAN_APP","LOAN_APP_EXTRA"], $result['la_name']);	// CHUNG DATABASE DECRYPT
    
                //  보증인 이름 검색
                $result['lag_name'] = $_ups_common()
                                        ->WHEREIN('la.no', function($query) use($searchArr){
                                            $query  ->SELECT('loan_app_no')
                                                    ->FROM('LOAN_APP_GUARANTOR')
                                                    ->WHERE('SAVE_STATUS', 'Y')
                                                    ->WHERE('name', Func::chungEncOne($searchArr->name));
                                        })->get()->toArray();
                $result['lag_name'] = Func::chungDec(["LOAN_APP","LOAN_APP_EXTRA"], $result['lag_name']);	// CHUNG DATABASE DECRYPT                        
            }

            if( !empty($searchArr->ph41) && !empty($searchArr->ph42) && !empty($searchArr->ph43) )
            {
                $searchTypeArr['ci_ph4']['str']  = $searchArr->ph41."-".$searchArr->ph42."-".$searchArr->ph43;
                $searchTypeArr['lig_ph4']['str'] = $searchArr->ph41."-".$searchArr->ph42."-".$searchArr->ph43;
                $searchTypeArr['la_ph4']['str']  = $searchArr->ph41."-".$searchArr->ph42."-".$searchArr->ph43;
                $searchTypeArr['lag_ph4']['str'] = $searchArr->ph41."-".$searchArr->ph42."-".$searchArr->ph43;

                $result['ci_ph4'] = $_erp_common()->WHERE('cie.ph41',$searchArr->ph41)->WHERE('cie.ph42',Func::chungEncOne($searchArr->ph42))->WHERE('cie.ph43',Func::chungEncOne($searchArr->ph43))->WHERE('ci.no','!=',$custInfoNo)->get()->toArray();
                $result['ci_ph4'] = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $result['ci_ph4']);	// CHUNG DATABASE DECRYPT

                $result['lig_ph4'] = $_erp_common()->WHEREIN('li.no', function($query) use($searchArr){
                                            $query  ->SELECT('loan_info_no')
                                                    ->FROM('LOAN_INFO_GUARANTOR')
                                                    ->WHERE('SAVE_STATUS', 'Y')
                                                    ->WHERE('ph41',$searchArr->ph41)->WHERE('ph42',Func::chungEncOne($searchArr->ph42))->WHERE('ph43',Func::chungEncOne($searchArr->ph43));
                                        })->get()->toArray();
                $result['lig_ph4'] = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $result['lig_ph4']);	// CHUNG DATABASE DECRYPT

                $result['la_ph4'] = $_ups_common()->WHERE('lae.ph41',$searchArr->ph41)->WHERE('lae.ph42',Func::chungEncOne($searchArr->ph42))->WHERE('lae.ph43',Func::chungEncOne($searchArr->ph43))->WHERE('la.no','!=',$loanAppNo)->get()->toArray();
                $result['la_ph4'] = Func::chungDec(["LOAN_APP","LOAN_APP_EXTRA"], $result['la_ph4']);	// CHUNG DATABASE DECRYPT

                $result['lag_ph4'] = $_ups_common()->WHEREIN('la.no', function($query) use($searchArr){
                                            $query  ->SELECT('loan_app_no')
                                                    ->FROM('LOAN_APP_GUARANTOR')
                                                    ->WHERE('save_status', 'Y')
                                                    ->WHERE('ph41',$searchArr->ph41)->WHERE('ph42',Func::chungEncOne($searchArr->ph42))->WHERE('ph43',Func::chungEncOne($searchArr->ph43));
                                        })->get()->toArray();
                $result['lag_ph4'] = Func::chungDec(["LOAN_APP","LOAN_APP_EXTRA"], $result['lag_ph4']);	// CHUNG DATABASE DECRYPT
            }
            
            
            if( !empty($searchArr->ph21) && !empty($searchArr->ph22) && !empty($searchArr->ph23) )
            {
                $searchTypeArr['ci_ph2']['str']  = $searchArr->ph21."-".$searchArr->ph22."-".$searchArr->ph23;
                $searchTypeArr['lig_ph2']['str'] = $searchArr->ph21."-".$searchArr->ph22."-".$searchArr->ph23;
                $searchTypeArr['la_ph2']['str']  = $searchArr->ph21."-".$searchArr->ph22."-".$searchArr->ph23;
                $searchTypeArr['lag_ph2']['str'] = $searchArr->ph21."-".$searchArr->ph22."-".$searchArr->ph23;

                $result['ci_ph2'] = $_erp_common()->WHERE('cie.ph21',$searchArr->ph21)->WHERE('cie.ph22',Func::chungEncOne($searchArr->ph22))->WHERE('cie.ph23',Func::chungEncOne($searchArr->ph23))->WHERE('ci.no','!=',$custInfoNo)->get()->toArray();
                $result['ci_ph2'] = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $result['ci_ph2']);	// CHUNG DATABASE DECRYPT

                $result['lig_ph2'] = $_erp_common()->WHEREIN('li.no', function($query) use($searchArr){
                                            $query  ->SELECT('loan_info_no')
                                                    ->FROM('LOAN_INFO_GUARANTOR')
                                                    ->WHERE('SAVE_STATUS', 'Y')
                                                    ->WHERE('ph21',$searchArr->ph21)->WHERE('ph22',Func::chungEncOne($searchArr->ph22))->WHERE('ph23',Func::chungEncOne($searchArr->ph23));
                                        })->get()->toArray();
                $result['lig_ph2'] = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $result['lig_ph2']);	// CHUNG DATABASE DECRYPT

                $result['la_ph2'] = $_ups_common()->WHERE('lae.ph21',$searchArr->ph21)->WHERE('lae.ph22',Func::chungEncOne($searchArr->ph22))->WHERE('lae.ph23',Func::chungEncOne($searchArr->ph23))->WHERE('la.no','!=',$loanAppNo)->get()->toArray();
                $result['la_ph2'] = Func::chungDec(["LOAN_APP","LOAN_APP_EXTRA"], $result['la_ph2']);	// CHUNG DATABASE DECRYPT

                $result['lag_ph2'] = $_ups_common()->WHEREIN('la.no', function($query) use($searchArr){
                                            $query  ->SELECT('loan_app_no')
                                                    ->FROM('LOAN_APP_GUARANTOR')
                                                    ->WHERE('save_status', 'Y')
                                                    ->WHERE('ph21',$searchArr->ph21)->WHERE('ph22',Func::chungEncOne($searchArr->ph22))->WHERE('ph23',Func::chungEncOne($searchArr->ph23));
                                        })->get()->toArray();
                $result['lag_ph2'] = Func::chungDec(["LOAN_APP","LOAN_APP_EXTRA"], $result['lag_ph2']);	// CHUNG DATABASE DECRYPT
            }

            if( !empty($searchArr->com_name) )
            {
                $searchTypeArr['ci_com_name']['str']  = $searchArr->com_name;
                $searchTypeArr['lig_com_name']['str'] = $searchArr->com_name;
                $searchTypeArr['la_com_name']['str']  = $searchArr->com_name;
                $searchTypeArr['lag_com_name']['str'] = $searchArr->com_name;

                //  직장명검색
                $result['ci_com_name'] = $_erp_common()->WHERE('cie.com_name', $searchArr->com_name)->WHERE('ci.no','!=',$custInfoNo)->get()->toArray();
                $result['ci_com_name'] = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $result['ci_com_name']);	// CHUNG DATABASE DECRYPT
    
                //  보증인 직장명 검색
                $result['lig_com_name'] = $_erp_common()
                                            ->WHEREIN('li.no', function($query) use($searchArr){
                                                $query  ->SELECT('loan_info_no')
                                                        ->FROM('LOAN_INFO_GUARANTOR')
                                                        ->WHERE('SAVE_STATUS', 'Y')
                                                        ->WHERE('com_name', $searchArr->com_name);
                                            })->get()->toArray();
                $result['lig_com_name'] = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $result['lig_com_name']);	// CHUNG DATABASE DECRYPT
                
                //  직장명검색
                $result['la_com_name'] = $_ups_common()->WHERE('lae.com_name', $searchArr->com_name)->WHERE('la.no','!=',$loanAppNo)->get()->toArray();
                $result['la_com_name'] = Func::chungDec(["LOAN_APP","LOAN_APP_EXTRA"], $result['la_com_name']);	// CHUNG DATABASE DECRYPT

                //  보증인 직장명 검색
                $result['lag_com_name'] = $_ups_common()
                                            ->WHEREIN('la.no', function($query) use($searchArr){
                                                $query  ->SELECT('loan_app_no')
                                                        ->FROM('LOAN_APP_GUARANTOR')
                                                        ->WHERE('save_status', 'Y')
                                                        ->WHERE('com_name', $searchArr->com_name);
                                            })->get()->toArray();
                $result['lag_com_name'] = Func::chungDec(["LOAN_APP","LOAN_APP_EXTRA"], $result['lag_com_name']);	// CHUNG DATABASE DECRYPT
            }

            if( !empty($searchArr->ph31) && !empty($searchArr->ph32) && !empty($searchArr->ph33) )
            {
                $searchTypeArr['ci_ph3']['str']  = $searchArr->ph31."-".$searchArr->ph32."-".$searchArr->ph33;
                $searchTypeArr['lig_ph3']['str'] = $searchArr->ph31."-".$searchArr->ph32."-".$searchArr->ph33;
                $searchTypeArr['la_ph3']['str']  = $searchArr->ph31."-".$searchArr->ph32."-".$searchArr->ph33;
                $searchTypeArr['lag_ph3']['str'] = $searchArr->ph31."-".$searchArr->ph32."-".$searchArr->ph33;

                $result['ci_ph3'] = $_erp_common()->WHERE('cie.ph31',$searchArr->ph31)->WHERE('cie.ph32',Func::chungEncOne($searchArr->ph32))->WHERE('cie.ph33',Func::chungEncOne($searchArr->ph33))->WHERE('ci.no','!=',$custInfoNo)->get()->toArray();
                $result['ci_ph3'] = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $result['ci_ph3']);	// CHUNG DATABASE DECRYPT

                $result['lig_ph3'] = $_erp_common()->WHEREIN('li.no', function($query) use($searchArr){
                                            $query  ->SELECT('loan_info_no')
                                                    ->FROM('LOAN_INFO_GUARANTOR')
                                                    ->WHERE('SAVE_STATUS', 'Y')
                                                    ->WHERE('ph31',$searchArr->ph31)->WHERE('ph32',Func::chungEncOne($searchArr->ph32))->WHERE('ph33',Func::chungEncOne($searchArr->ph33));
                                        })->get()->toArray();
                $result['lig_ph3'] = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $result['lig_ph3']);	// CHUNG DATABASE DECRYPT

                $result['la_ph3'] = $_ups_common()->WHERE('lae.ph31',$searchArr->ph31)->WHERE('lae.ph32',Func::chungEncOne($searchArr->ph32))->WHERE('lae.ph33',Func::chungEncOne($searchArr->ph33))->WHERE('la.no','!=',$loanAppNo)->get()->toArray();
                $result['la_ph3'] = Func::chungDec(["LOAN_APP","LOAN_APP_EXTRA"], $result['la_ph3']);	// CHUNG DATABASE DECRYPT

                $result['lag_ph3'] = $_ups_common()->WHEREIN('la.no', function($query) use($searchArr){
                                            $query  ->SELECT('loan_app_no')
                                                    ->FROM('LOAN_APP_GUARANTOR')
                                                    ->WHERE('save_status', 'Y')
                                                    ->WHERE('ph31',$searchArr->ph31)->WHERE('ph32',Func::chungEncOne($searchArr->ph32))->WHERE('ph33',Func::chungEncOne($searchArr->ph33));
                                        })->get()->toArray();
                $result['lag_ph3'] = Func::chungDec(["LOAN_APP","LOAN_APP_EXTRA"], $result['lag_ph3']);	// CHUNG DATABASE DECRYPT
            }
        }
        else
        {
            return "<div class='text-center' style='height:100px; transform: translateY(40%);'><b>※ 검색 대상을 확인할 수 없습니다.</b></div>";
        }

        //  빈 배열 unset
        foreach($result as $tname => $arr)
        {
            if( empty($arr) )
            {
                unset($result[$tname]);
            }
        }

        $arrayBranch    = Func::getBranch();
        $myBranch       = Auth::user()->branch_code;
        $arrayProCd     = Func::getConfigArr('pro_cd');
                
        return view('ups.searchList')   ->with('result', $result)
                                        ->with('searchTypeArr', $searchTypeArr)
                                        ->with('order_type', $order_type)
                                        ->with('order_colm', $order_colm)
                                        ->with('arrayBranch', $arrayBranch)
                                        ->with('myBranch', $myBranch)
                                        ->with('arrayProCd', $arrayProCd)
                                        ;
    }

    /**
     * 연계검사 데이터 검색
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function searchAction(Request $request)
    {
        $exp = explode('/', $_SERVER['REQUEST_URI']);
        $requestUri = $exp[1];
        
        $searchStr = (isset($request->searchStr)) ? $request->searchStr:'';
        $searchStr = trim($searchStr);
        $originStr = $searchStr;
        $searchStr = str_replace("-", "", $searchStr);

        if( isset($request->order_colm) && isset($request->order_type) )
        {
            $order_colm = $request->order_colm;
            $order_type = $request->order_type;
        }
        else
        {
            $order_colm = "NAME";
            $order_type = "ASC";
        }        

        // 금지단어
        if(strstr($searchStr, '00000000') || strstr($searchStr, '11111111') || $searchStr=='직장명')
        {
            die("<div align='center'>검색 불가능한 단어가 있습니다.</div>");
        }

        // trim 후 공백 체크
        if(empty($searchStr))
        {
            return "<div class='text-center' style='height:100px; transform: translateY(40%);'><b>※ 검색어를 입력해주세요.</b></div>";
        }

        $searchTypeArr = array(
            'ci_ssn'        =>  array('list' => '고객정보',       'title' => '주민번호',  'eu' => 'ERP', 'str'=>$originStr),
            // 'lig_ssn'       =>  array('list' => '보증인정보',     'title' => '주민번호',  'eu' => 'ERP', 'str'=>$originStr),
            // 'la_ssn'        =>  array('list' => '심사고객정보',   'title' => '주민번호',  'eu' => 'UPS', 'str'=>$originStr),
            // 'lag_ssn'       =>  array('list' => '심사보증인정보', 'title' => '주민번호',  'eu' => 'UPS', 'str'=>$originStr),
            
            'ci_birth'      =>  array('list' => '고객정보',       'title' => '생년월일',  'eu' => 'ERP', 'str'=>$originStr),
            // 'lig_birth'     =>  array('list' => '보증인정보',     'title' => '생년월일',  'eu' => 'ERP', 'str'=>$originStr),
            // 'la_birth'      =>  array('list' => '심사고객정보',   'title' => '생년월일',  'eu' => 'UPS', 'str'=>$originStr),
            // 'lag_birth'     =>  array('list' => '심사보증인정보', 'title' => '생년월일',  'eu' => 'UPS', 'str'=>$originStr),

            'ci_ph_4'       =>  array('list' => '고객정보',       'title' => '번호뒷자리',  'eu' => 'ERP', 'str'=>$originStr),
            // 'lig_ph_4'      =>  array('list' => '보증인정보',     'title' => '번호뒷자리',  'eu' => 'ERP', 'str'=>$originStr),
            // 'la_ph_4'       =>  array('list' => '심사고객정보',   'title' => '번호뒷자리',  'eu' => 'UPS', 'str'=>$originStr),
            // 'lag_ph_4'      =>  array('list' => '심사보증인정보', 'title' => '번호뒷자리',  'eu' => 'UPS', 'str'=>$originStr),

            'ci_ph'         =>  array('list' => '고객정보',       'title' => '전화번호',  'eu' => 'ERP', 'str'=>$originStr),
            // 'lig_ph'        =>  array('list' => '보증인정보',     'title' => '전화번호',  'eu' => 'ERP', 'str'=>$originStr),
            // 'la_ph'         =>  array('list' => '심사고객정보',   'title' => '전화번호',  'eu' => 'UPS', 'str'=>$originStr),
            // 'lag_ph'        =>  array('list' => '심사보증인정보', 'title' => '전화번호',  'eu' => 'UPS', 'str'=>$originStr),

            'ci_ph2'         =>  array('list' => '고객정보',       'title' => '휴대폰번호',  'eu' => 'ERP', 'str'=>$originStr),
            // 'lig_ph2'        =>  array('list' => '보증인정보',     'title' => '휴대폰번호',  'eu' => 'ERP', 'str'=>$originStr),
            // 'la_ph2'         =>  array('list' => '심사고객정보',   'title' => '휴대폰번호',  'eu' => 'UPS', 'str'=>$originStr),
            // 'lag_ph2'        =>  array('list' => '심사보증인정보', 'title' => '휴대폰번호',  'eu' => 'UPS', 'str'=>$originStr),

            'ci_ph3'         =>  array('list' => '고객정보',       'title' => '직장전화',  'eu' => 'ERP', 'str'=>$originStr),
            // 'lig_ph3'        =>  array('list' => '보증인정보',     'title' => '직장전화',  'eu' => 'ERP', 'str'=>$originStr),
            // 'la_ph3'         =>  array('list' => '심사고객정보',   'title' => '직장전화',  'eu' => 'UPS', 'str'=>$originStr),
            // 'lag_ph3'        =>  array('list' => '심사보증인정보', 'title' => '직장전화',  'eu' => 'UPS', 'str'=>$originStr),

            'ci_ph4'         =>  array('list' => '고객정보',       'title' => '기타전화',  'eu' => 'ERP', 'str'=>$originStr),
            // 'lig_ph4'        =>  array('list' => '보증인정보',     'title' => '기타전화',  'eu' => 'ERP', 'str'=>$originStr),
            // 'la_ph4'         =>  array('list' => '심사고객정보',   'title' => '기타전화',  'eu' => 'UPS', 'str'=>$originStr),
            // 'lag_ph4'        =>  array('list' => '심사보증인정보', 'title' => '기타전화',  'eu' => 'UPS', 'str'=>$originStr),
            
            'ci_name'       =>  array('list' => '고객정보',       'title' => '성명',      'eu' => 'ERP', 'str'=>$originStr),
            // 'lig_name'      =>  array('list' => '보증인정보',     'title' => '성명',      'eu' => 'ERP', 'str'=>$originStr),
            // 'la_name'       =>  array('list' => '심사고객정보',   'title' => '성명',      'eu' => 'UPS', 'str'=>$originStr),
            // 'lag_name'      =>  array('list' => '심사보증인정보', 'title' => '성명',      'eu' => 'UPS', 'str'=>$originStr),
            
            // 'ci_com_name'   =>  array('list' => '고객정보',       'title' => '직장명',    'eu' => 'ERP', 'str'=>$originStr),
            // 'lig_com_name'  =>  array('list' => '보증인정보',     'title' => '직장명',    'eu' => 'ERP', 'str'=>$originStr),
            // 'la_com_name'   =>  array('list' => '심사고객정보',   'title' => '직장명',    'eu' => 'UPS', 'str'=>$originStr),
            // 'lag_com_name'  =>  array('list' => '심사보증인정보', 'title' => '직장명',    'eu' => 'UPS', 'str'=>$originStr),

            // 'ci_vir_ssn'    =>  array('list' => '고객정보',       'title' => '가상계좌번호',    'eu' => 'ERP', 'str'=>$originStr),
            // 'lig_vir_ssn'   =>  array('list' => '보증인정보',     'title' => '가상계좌번호',    'eu' => 'ERP', 'str'=>$originStr),

            'ci_no'         =>  array('list' => '고객정보',       'title' => '투자자번호',    'eu' => 'ERP', 'str'=>$originStr),
        );

        $_erp_common = function(){
            $BOXC = DB::table("loan_usr_info as ci")->select('*')->where('ci.save_status', 'Y'); 
            return $BOXC;
        };

        
        $_ups_common = function(){
            return DB::TABLE('LOAN_APP la')
                    ->JOIN('LOAN_APP_EXTRA lae','la.no','=','lae.loan_app_no')
                    ->SELECT('la.no as loan_app_no','la.name', 'la.app_date', 'la.pro_cd', 'la.app_money', 'la.status', 'lae.ph31','lae.ph32','lae.ph33', 'lae.ph41','lae.ph42','lae.ph43', 'lae.ph21','lae.ph22','lae.ph23','lae.com_name', 'la.ssn', 'la.manager_code')
                    ->WHERE('la.save_status','Y');
        };

        // 고객번호 검색이면 숫자로 보낸다.
        $chkAddCi = Func::stripCi($searchStr);
        if(is_numeric($chkAddCi))
        {
            $searchStr = $chkAddCi;
        }

        if( is_numeric($searchStr) )
        {
            if( strlen($searchStr) == 13 )      //  주민번호 13자리
            {
                $result['ci_ssn'] = $_erp_common()->WHERE('ci.ssn', Func::chungEncOne($searchStr))->get()->toArray();
                $result['ci_ssn'] = Func::chungDec(["loan_usr_info"], $result['ci_ssn']);	// CHUNG DATABASE DECRYPT
            }
            else if( strlen($searchStr) == 4 )      //  번호 뒷자리
            {
                $result['ci_ph_4'] = $_erp_common()->WHERE(function($query) use ($searchStr){
                                                    $query //-> WHERE('cie.ph13', $searchStr)
                                                        ->WHERE('ci.ph23', Func::chungEncOne($searchStr))
                                                        ->ORWHERE('ci.ph33', Func::chungEncOne($searchStr))
                                                        ->ORWHERE('ci.ph43', Func::chungEncOne($searchStr))
                                                        ->ORWHERE('ci.ph13', Func::chungEncOne($searchStr));
                                                    })->get()->toArray();
                $result['ci_ph_4'] = Func::chungDec(["loan_usr_info"], $result['ci_ph_4']);	// CHUNG DATABASE DECRYPT

                $searchTypeArr['ci_no']['str'] = Func::addCi($searchStr);
                $result['ci_no'] = $_erp_common()->where('ci.no', $searchStr)->get()->toArray();
                $result['ci_no'] = Func::chungDec(["loan_usr_info"], $result['ci_no']);	// CHUNG DATABASE DECRYPT
            }
            else if( strlen($searchStr) >= 8 && strlen($searchStr) <= 11 )     //  전화번호
            {                
                if (substr($searchStr,0,2) =='02' )  
                {
                    //  02로 시작하면
                    $tel = preg_replace("/([0-9]{2})([0-9]{3,4})([0-9]{4})$/","\\1-\\2-\\3", $searchStr);
                }
                else if( strlen($searchStr)=='8')
                {
                    //  지능망 번호 검색이거나, 혹은 휴대폰 중간, 뒷 번호 검색일 경우
                    $tel = preg_replace("/([0-9]{4})([0-9]{4})$/","\\1-\\2",$searchStr);
                } 
                else if( strlen($searchStr)=='10' || strlen($searchStr)=='11' )
                {
                    $tel = preg_replace("/([0-9]{3})([0-9]{3,4})([0-9]{4})$/","\\1-\\2-\\3" ,$searchStr);
                }
                else
                {
                    die("올바른 검색어를 입력하세요.");
                }

                $tels = explode("-", $tel);
                
                if( strlen($searchStr) == '8' )
                {
                    $ph1 = '';
                    $ph2 = $tels[0];
                    $ph3 = $tels[1];
                }
                else
                {
                    $ph1 = $tels[0];
                    $ph2 = $tels[1];
                    $ph3 = $tels[2];
                }
                DB::enableQueryLog();
                $result['ci_ph'] = $_erp_common()->WHERE(function($query) use ($ph1, $ph2, $ph3){
                    
                    $query -> /*WHERE(function($query) use ($ph1, $ph2, $ph3){
                        
                        if( $ph1 != '' )
                        {
                            $query -> WHERE('cie.ph11',$ph1);
                        }
                        $query -> WHERE('cie.ph12', $ph2) -> WHERE('cie.ph13', $ph3);

                    }) -> OR*/WHERE(function($query) use ($ph1, $ph2, $ph3){
                        
                        if( $ph1 != '' )
                        {
                            $query -> WHERE('ci.ph21', Func::chungEncOne($ph1));
                        }
                        $query -> WHERE('ci.ph22', Func::chungEncOne($ph2)) -> WHERE('ci.ph23', Func::chungEncOne($ph3));

                    }) -> ORWHERE(function($query) use ($ph1, $ph2, $ph3){
                        
                        if( $ph1 != '' )
                        {
                            $query -> WHERE('ci.ph11', Func::chungEncOne($ph1));
                        }
                        $query -> WHERE('ci.ph12', Func::chungEncOne($ph2)) -> WHERE('ci.ph13', Func::chungEncOne($ph3));
                    }) -> ORWHERE(function($query) use ($ph1, $ph2, $ph3){
                        
                        if( $ph1 != '' )
                        {
                            $query -> WHERE('ci.ph41', Func::chungEncOne($ph1));
                        }
                        $query -> WHERE('ci.ph42', Func::chungEncOne($ph2)) -> WHERE('ci.ph43', Func::chungEncOne($ph3));
                    });
                })->get()->toArray();
                
                $result['ci_ph'] = Func::chungDec(["loan_usr_info"], $result['ci_ph']);	// CHUNG DATABASE DECRYPT

                // 8자리까지는 고객번호 검색을 해보자.
                // if( strlen($searchStr)=='8')
                // {
                //     $searchTypeArr['ci_no']['str'] = Func::addCi($searchStr);
                //     $result['ci_no'] = $_erp_common()->where('ci.no', $searchStr)->get()->toArray();
                //     $result['ci_no'] = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA","LOAN_INFO"], $result['ci_no']);	// CHUNG DATABASE DECRYPT
                // }
                
            }
            // 단순숫자는 고객번호 검색
            else if( is_numeric($searchStr))
            {
                $searchTypeArr['ci_no']['str'] = $searchStr;
                $result['ci_no'] = $_erp_common()->where('ci.investor_no', $searchStr)->get()->toArray();
                $result['ci_no'] = Func::chungDec(["loan_usr_info"], $result['ci_no']);	// CHUNG DATABASE DECRYPT
            }
            else
            {
                return "<div class='text-center' style='height:100px; transform: translateY(40%);'><b>※ 올바른 검색어를 입력하세요.</b></div>";
            }

        }
        else                                //  성명, 보증인명, 직장명 검색
        {
            // -------------------------------- ERP 검색 --------------------------------

            $searchTypeArr['ci_name']['str'] = $searchStr;
            $erp = $_erp_common();
            $erp = $erp->where('ci.nick_name', 'like','%'.$searchStr.'%');
            // $erp = Func::encLikeSearch($erp, 'ci.name', $searchStr, 'all');
            // $result['ci_name'] = $_erp_common()->WHERE('ci.name', Func::chungEncOne($searchStr))->get()->toArray();
            // Log::info($erp->toSql(), $erp->getBindings());
            $result['ci_name'] = $erp->get()->toArray();
            $result['ci_name'] = Func::chungDec(["loan_usr_info"], $result['ci_name']);	// CHUNG DATABASE DECRYPT  
        }

        //  빈 배열 unset
        if(isset($result))
        {
            foreach($result as $tname => $arr)
            {
                if( empty($arr) )
                {
                    unset($result[$tname]);
                }
            }
        }

        $arrayBranch    = Func::getBranch();
        $myBranch       = Auth::user()->branch_code;
        $myBranch       = Auth::user()->branch_code;
        $arrayProCd     = Func::getConfigArr('pro_cd');
        
        // Log::info(print_r($result,true));

        // return json_encode($r);
        return view('erp.searchList')->with('result', $result)
                                     ->with('searchTypeArr', $searchTypeArr)
                                     ->with('order_type', $order_type)
                                     ->with('order_colm', $order_colm)
                                     ->with('arrayBranch', $arrayBranch)
                                     ->with('myBranch', $myBranch)
                                     ->with('requestUri', $requestUri)
                                     ->with('arrayProCd', $arrayProCd)
                                     ;
    }


    /**
     * 계약번호 바로가기
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function searchLoan(Request $request)
    {
        $loanInfoNo = $request->searchStr;
        
        if(!is_numeric($loanInfoNo))
        {
            echo "<script>alert('계약번호를 숫자로 정확히 입력해주세요.');window.close();</script>";
            exit;
        }

        // 계약번호로 고객번호 검색 
        $custInfoNo = DB::TABLE("LOAN_INFO")->where('no', $loanInfoNo)->where('save_status', 'Y')->value('cust_info_no');

        if($custInfoNo>0)
        {
            return redirect("/erp/custpop?cust_info_no=".$custInfoNo."&no=".$loanInfoNo);
        }
        else
        {
            echo "<script>alert('요청한 계약을 찾을 수 없습니다.');window.close();</script>";
        }
    }

    //채권번호 검색
    public function searchNo(Request $request) 
    {
        $exp = explode('/', $_SERVER['REQUEST_URI']);
        $requestUri = $exp[1];
        
        $searchStr = (isset($request->searchStr)) ? $request->searchStr:'';
        $searchStr = trim($searchStr);
        $originStr = $searchStr;

        if( isset($request->order_colm) && isset($request->order_type) )
        {
            $order_colm = $request->order_colm;
            $order_type = $request->order_type;
        }
        else
        {
            $order_colm = "NAME";
            $order_type = "ASC";
        }        

        // 금지단어
        if(strstr($searchStr, '00000000') || strstr($searchStr, '11111111') || $searchStr=='직장명')
        {
            die("<div align='center'>검색 불가능한 단어가 있습니다.</div>");
        }

        // trim 후 공백 체크
        if(empty($searchStr))
        {
            return "<div class='text-center' style='height:100px; transform: translateY(40%);'><b>※ 검색어를 입력해주세요.</b></div>";
        }

        $searchTypeArr = array(
            'bond_no'         =>  array('list' => '계약정보',       'title' => '채권번호',    'eu' => 'ERP', 'str'=>$originStr),
        );

        $_erp_common = function(){
            $BOXC = DB::table("loan_info as li")
                            ->join('cust_info as ci', 'li.cust_info_no', '=', 'ci.no')
                            ->join('loan_usr_info as ui', 'li.loan_usr_info_no', '=', 'ui.no')
                            ->select('li.*', 'ui.name', 'ci.name as cust_name')->where('li.save_status', 'Y'); 
            return $BOXC;
        };

        $searchTypeArr['bond_no']['str'] = $searchStr;
        if(strpos($searchStr, '-'))
        {
            $arr = explode('-', $searchStr);
            
            // investor_type 검색하는 것을 추가해야됨
            $arr[0] = preg_replace('/[^0-9]/', '', $arr[0]);

            if(isset($arr[0]) && $arr[0] != '')
            {
                if(isset($arr[1]) && $arr[1] != '')
                {
                    $result['bond_no'] = $_erp_common()->where('li.investor_no', $arr[0])->where('inv_seq', $arr[1])->get()->toArray();
                }
                else
                {
                    $result['bond_no'] = $_erp_common()->where('li.investor_no', $arr[0])->get()->toArray();
                }

                $result['bond_no'] = Func::chungDec(["loan_info", "cust_info", 'loan_usr_info'], $result['bond_no']);	// CHUNG DATABASE DECRYPT
            }
            else
            {
                return "<div class='text-center' style='height:100px; transform: translateY(40%);'><b>※ 채권번호 형식에 맞지 않습니다.</b></div>";
            }
        }
        else
        {
            return "<div class='text-center' style='height:100px; transform: translateY(40%);'><b>※ 채권번호 형식에 맞지 않습니다.</b></div>";
        } 

        if(isset($result))
        {
            foreach($result as $tname => $arr)
            {
                if( empty($arr) )
                {
                    unset($result[$tname]);
                }
            }
        }

        $arrayBranch    = Func::getBranch();
        $myBranch       = Auth::user()->branch_code;
        $arrayProCd     = Func::getConfigArr('pro_cd');
        $arrayConfig   = Func::getConfigArr();
        
        return view('erp.bondSearchList')->with('result', $result)
                                     ->with('searchTypeArr', $searchTypeArr)
                                     ->with('order_type', $order_type)
                                     ->with('order_colm', $order_colm)
                                     ->with('arrayBranch', $arrayBranch)
                                     ->with('myBranch', $myBranch)
                                     ->with('requestUri', $requestUri)
                                     ->with('arrayProCd', $arrayProCd)
                                     ->with('arrayConfig', $arrayConfig)
                                     ;
    }
}

