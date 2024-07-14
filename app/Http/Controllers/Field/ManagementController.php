<?php
namespace App\Http\Controllers\Field;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Log;
use Auth;
use Func;
use Carbon;
use DataList;
use ExcelFunc;
use App\Chung\Sms;
use App\Chung\Vars;
use App\Chung\Paging;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

// php Spreadsheet 라이브러리
##################################################
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Cell\DataType;
##################################################

class ManagementController extends Controller
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
     * 현장관리 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setDataManagementList(Request $request)
    {
        $list   = new DataList(Array("listName"=>"management","listAction"=>'/'.$request->path()));

        $list->setSearchDate('날짜검색',Array('contract_date' => '공사시작일', 'contract_end_date' => '공사종료일'),'searchDt','Y');

        $list->setSearchType('div',Func::getConfigArr('management_div'),'현장구분', '', '', '', '', 'Y', '', true);
        
        $list->setPlusButton("managementForm('');");

        $list->setSearchDetail(Array(
            'code'          => '코드',
            'orderer'       => '발주처',
            'name'          => '현장명',
        ));

        return $list;
    }

    public function management(Request $request)
    {
        $list = $this->setDataManagementList($request);

        $list->setlistTitleCommon(Array
        (
            'code'                      => Array('코드', 1, '', 'center', '', 'code'),
            'orderer'                   => Array('발주처', 1, '', 'center', '', 'orderer'),
            'div'                       => Array('구분', 1, '', 'center', '', 'div'),
            'name'                      => Array('현장명', 1, '', 'center', '', 'name'),
            'balance'                   => Array('공사금액', 0, '', 'center', '', ''),
            'contract_date'             => Array('공사시작일', 1, '', 'center', '', 'contract_date'),
            'contract_end_date'         => Array('공사종료일', 1, '', 'center', '', 'contract_end_date'),
            'save_id'                   => Array('작업자', 0, '', 'center', '', 'save_id', ['save_time'=>['저장시간', 'save_time', '<br>']]),
        ));
        
        return view('field.management')->with('result', $list->getList());
    }   
    
    /**
     * 현장관리 리스트 (ajax부분화면)
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementList(Request $request)
    { 
        $list  = $this->setDataManagementList($request);

        $param = $request->all();

        // 메인쿼리
        $LOAN_LIST = DB::table("contract_info")->select("*")->where('save_status','Y');

        if(empty($param['listOrder']) && empty($param['listOrderAsc']))
        {
            $param['listOrder'] = 'no';
            $param['listOrderAsc'] = 'desc';
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='code' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('code', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='orderer' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('orderer', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $LOAN_LIST = $list->getListQuery('contract_info', 'main', $LOAN_LIST, $param);

        $target_sql = urlencode(encrypt(Func::printQuery($LOAN_LIST))); // 페이지 들어가기 전에 쿼리를 저장해야한다.
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN_LIST, $request->page, $request->listLimit, 10, $request->listName);

        $rslt = $LOAN_LIST->get();
        $rslt = Func::chungDec(["contract_info"], $rslt);	// CHUNG DATABASE DECRYPT

        $configArr   = Func::getConfigArr();
        $arrayUserId = Func::getUserId();

        $cnt = 0;

        foreach ($rslt as $v)
        {
            $v->onclick                  = 'popUpFull(\'/field/managementpop?no='.$v->no.'\', \'management'.$v->no.'\')';
            $v->line_style               = 'cursor: pointer;';
            
            $v->name                     = $v->name; 
            $v->contract_date            = Func::dateFormat($v->contract_date);

            $v->balance                  = number_format(0);

            $v->status                   = Func::getInvStatus($v->status, true);
            $v->contract_end_date        = Func::dateFormat($v->contract_end_date);
            $v->save_time                = Func::dateFormat($v->save_time);
            $v->save_id                  = Func::getArrayName($arrayUserId, $v->save_id);
            $v->div                      = Func::getArrayName($configArr['management_div'], $v->div);
 
            $r['v'][] = $v;
            $cnt ++;
        }
		
        // 페이징
        $r['pageList']  = $paging->getPagingHtml($request->path());
        $r['result']    = 1;
        $r['txt']       = $cnt;
        $r['targetSql'] = $target_sql;
        $r['totalCnt']  = $paging->getTotalCnt();

        return json_encode($r);
    }

    /**
     * 현장계약 등록 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementForm(Request $request)
    {
        $arrayConfig  = Func::getConfigArr();

        return view('field.managementForm')->with("arrayConfig", $arrayConfig);
    }

    /*
     *  현장계약 등록 액션

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function managementFormAction(Request $request)
    {
        $_DATA = $request->all();
        
        if(!empty($_DATA['contract_date']))
        {
            $_DATA['contract_date'] = preg_replace('/[^0-9]/', '', $_DATA['contract_date']);
        }
        if(!empty($_DATA['contract_end_date']))
        {
            $_DATA['contract_end_date'] = preg_replace('/[^0-9]/', '', $_DATA['contract_end_date']);
        }

        $_DATA['save_status']   = 'Y';
        $_DATA['save_id']       = Auth::id();
        $_DATA['save_time']     = date('Ymd');

        $result = DB::dataProcess('INS', 'contract_info', $_DATA);
        
        if( $result == "Y" )
        {
            $array_result['rs_code'] = "Y";
            $array_result['result_msg'] = "정상적으로 등록되었습니다.";
        }
        else
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "등록 오류";
        }

        return $array_result;
    }

    /**
     * 현장정보 - 팝업창
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementPop(Request $request)
    {
        $status_color = "#6c757d";
        $no = $request->no;
        
        $info = DB::table("contract_info")->select("*")->where("no",$no)->where("save_status", "Y")->first();
        $info = Func::chungDec(["contract_info"], $info);	// CHUNG DATABASE DECRYPT

        return view('field.managementPop')->with("info", $info)->with("status_color", $status_color);
    }

    /**
     * 현장정보 팝업창 - 상세정보
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementInfo(Request $request)
    {
        $array_config = Func::getConfigArr();

        $v = [];
        $no = $request->contract_info_no;
        
        if(is_numeric($no))
        {
            $v = DB::table("contract_info")->select("*")->where("no", $no)->where('save_status','Y')->first();
            $v = Func::chungDec(["contract_info"], $v);	// CHUNG DATABASE DECRYPT
        }

        return view('field.managementInfo')->with('v', $v)->with("configArr", $array_config);
    }

    /**
     * 현장정보 저장
     * 
     * @param  \Illuminate\Http\Request  $request
     * @return String
     */
    public function managementInfoAction(Request $request)
    {
        $_DATA = $request->all();

        if(!empty($_DATA['contract_date']))
        {
            $_DATA['contract_date'] = preg_replace('/[^0-9]/', '', $_DATA['contract_date']);
        }
        if(!empty($_DATA['contract_end_date']))
        {
            $_DATA['contract_end_date'] = preg_replace('/[^0-9]/', '', $_DATA['contract_end_date']);
        }
        
        if($_DATA['mode'] == 'UPD')
        {
            $_DATA['save_id']   = Auth::id();
            $_DATA['save_time'] = date('Ymd');
        }
        else
        {
            $_DATA['save_status'] = 'N';
            $_DATA['del_id']      = Auth::id();
            $_DATA['del_time']    = date('Ymd');
        }

        $result = DB::dataProcess('UPD', 'contract_info', $_DATA, ['no'=>$_DATA['contract_info_no']]);
        
        if( $result == "Y" )
        {
            $array_result['rs_code'] = "Y";
            $array_result['result_msg'] = "정상적으로 저장되었습니다.";
        }
        else
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "등록 오류";
        }

        return $array_result;
    }

    /**
     * 실행내역서 팝업창 - 상세정보
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementHistory(Request $request)
    {
        $array_config = Func::getConfigArr();

        $v = [];
        $no = $request->contract_info_no;
        
        if(is_numeric($no))
        {
            $v = DB::table("contract_info")->select("*")->where("no", $no)->where('save_status','Y')->first();
            $v = Func::chungDec(["contract_info"], $v);	// CHUNG DATABASE DECRYPT
        }

        return view('field.managementHistory')->with('v', $v)->with("configArr", $array_config);
    }

    /**
     * 실행내역서 엑셀업로드 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementHistoryExcelForm(Request $request)
    {
        $arrayConfig  = Func::getConfigArr();

        return view('field.managementHistoryExcelForm')->with("arrayConfig", $arrayConfig);
    }

    /**
     * 실행내역서 엑셀업로드 샘플
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementHistoryExcelSample(Request $request)
    {
        if(Storage::disk('management')->exists('historyExcelSample.xlsx'))
        {
            return Storage::disk('management')->download('historyExcelSample.xlsx', '실행내역서엑셀업로드예시파일.xlsx');
        }
        else
        {
            log::debug("샘플파일 없음");
        }
    }

    /**
     * 실행내역서 엑셀업로드 액션
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementHistoryExcelAction(Request $request)
    {
        if(empty($request->contract_info_no))
        {
            $r['rs_code'] = "N";
            $r['rs_msg']  = "현장번호가 존재하지 않습니다.";

            return $r;
        }

        if( $request->file('excel_data') )
        {
            // 엑셀 저장
            $file_path = $request->file('excel_data')->store("upload/".date("Ymd"), 'management');
            
            // 경로세팅 
            if(Storage::disk('management')->exists($file_path))
            {
                $colHeader  = array(
                    "구분",
                    "코드",
                    "품명",
                    "규격(1)",
                    "규격(2)",
                    "단위",
                    "단가",
                    "기타"
                );
                $colNm = array(
                    "category"      => "0",	      // 구분
                    "code"	        => "1",	      // 코드
                    "name"          => "2",       // 품명
                    "standard1"     => "3",       // 규격(1)
                    "standard2"     => "4",       // 규격(2)
                    "type"          => "5",       // 단위
                    "price"         => "6",       // 단가
                    "etc"           => "7",       // 기타
                );
                                    
                $file = Storage::path('/management/'.$file_path);
                
                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    // 파일경로
                    log::debug($file_path);

                    $r['rs_code'] = "N";
                    $r['rs_msg']  = "엑셀 유효성 검사를 실패하였습니다.";

                    return $r;
                }
                else
                {
                    foreach($excelData as $_DATA) 
                    {
                        unset($_INS);

                        // 데이터 정리
                        foreach($_DATA as $key => $val) 
                        {
                            $val = trim($val);
                            $_INS[$key] = $val;
                        }

                        // 데이터 추출 및 정리
                        foreach($_INS as $key => $val)
                        {
                            // 값이 없으면 unset
                            if($val == "")
                            {
                                unset($_INS[$key]);
                                continue;
                            }

                            // 값이 null 이면 빈값으로 업데이트
                            if($val == "null" || $val == "NULL")
                            {
                                $_INS[$key] = '';
                                continue;
                            }
                        }

                        if(!isset($_INS['category']) || $_INS['category'] == '')
                        {
                            continue;
                        }
                        if(!isset($_INS['code']) || $_INS['code'] == '')
                        {
                            continue;
                        }
                        if(!isset($_INS['name']) || $_INS['name'] == '')
                        {
                            continue;
                        }

                        if(!empty($_INS['price']))
                        {
                            $_INS['price'] = preg_replace('/[^0-9]/', '', $_INS['price']);
                        }

                        $_INS['contract_info_no'] = $request->contract_info_no;
                        $_INS['file_path']        = $file_path;

                        $_INS['save_status']      = 'Y';
                        $_INS['save_id']          = Auth::id();
                        $_INS['save_time']        = date('Ymd');

                        $rslt = DB::dataProcess('INS', 'material', $_INS);
                    }
                }

                $r['rs_code'] = "Y";
                $r['rs_msg']  = "엑셀 업로드를 성공하였습니다.";

                return $r;
            }
            else 
            {
                log::debug($file_path ?? '파일경로 없음');

                $r['rs_code'] = "N";
                $r['rs_msg']  = "엑셀 업로드를 실패했습니다.";

                return $r;
            }
        }
        else
        {
            $r['rs_code'] = "N";
            $r['rs_msg']  = "엑셀을 등록해주세요.";

            return $r;
        }
    }

    /**
     * 현장정보 팝업창 - 일위대가
     * 일위대가 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setCostDataList(Request $request)
    {
        $list = new DataList(Array("listName"=>"managementCost","listAction"=>'/'.$request->path()));

        $list->setSearchDetail(Array(
            'material.code'       => '코드',
            'material.name'       => '품명',
        ));

        return $list;
    }

    /**
     * 현장정보 팝업창 - 일위대가
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementCost(Request $request)
    {
        $list = $this->setCostDataList($request);
        
        $contract_info_no = $request->contract_info_no;

        $list->setButtonArray("일괄삭제", "managementCostAllClear('".$contract_info_no."')", "btn-danger");

        $list->setButtonArray("엑셀업로드", "managementCostExcelForm('".$contract_info_no."')", "btn-info");

        $list->setButtonArray("엑셀다운","excelDownModal('/field/managementcostexcel','form_managementCost')","btn-success");
        
        $list->setPlusButton("managementCostForm('".$contract_info_no."');");
        
        $list->setViewNum(false);

        if(is_numeric($contract_info_no))
        {
            $v = DB::table("contract_info")->SELECT("*")->WHERE('save_status','Y')->WHERE("no", $contract_info_no)->first();
            $v = Func::chungDec(["contract_info"], $v);	// CHUNG DATABASE DECRYPT
        }
        
        $list->setHidden(Array('contract_info_no' => $request->contract_info_no));

        $list->setlistTitleCommon(Array
        (
            'code'                  => Array('코드', 0, '', 'center', '', 'code'),
            'name'                  => Array('품명', 1, '', 'center', '', 'name'),
            'standard1'             => Array('규격(1)', 1, '', 'center', '', 'standard1'),
            'standard2'             => Array('규격(2)', 1, '', 'center', '', 'standard2'),
            'type'                  => Array('단위', 1, '', 'center', '', 'type'),
            'count'                 => Array('수량', 1, '', 'center', '', ''),
            'price'                 => Array('단가', 1, '', 'center', '', ''),
            'balance'               => Array('금액', 0, '', 'center', '', ''),
            'etc'                   => Array('기타', 1, '', 'center', '', 'etc'),
        ));

        return view('field.managementCost')->with('v', $v)->with('result', $list->getList());
    }

    /**
     * 현장정보 팝업창 - 자재단가표 리스트
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function managementCostList(Request $request)
    {
        $param = $request->all();

        $LOAN_LIST = DB::table("cost")->join("contract_info", "contract_info.no", "=", "cost.contract_info_no")
                            ->select("cost.*")
                            ->where('contract_info.no',$param['contract_info_no'])
                            ->where('contract_info.save_status','Y')
                            ->where('cost.save_status','Y');

        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('no','desc');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='cost.code' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('cost.code', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='cost.name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('cost.name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN_LIST, $request->page, $request->listLimit, 10, $request->listName);
        
        // 결과
        $LOAN_LIST = $LOAN_LIST->get();
        $LOAN_LIST = Func::chungDec(["cost"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $cnt = 0;
        foreach ($LOAN_LIST as $v)
        {
            $link        = 'javascript:window.open("/field/managementcostpop?contract_info_no='.$v->contract_info_no.'&cost_no='.$v->no.'","msgpop","width=2000, height=1000, scrollbars=yes")';
            $v->code     = "<a href='".$link.";'>".$v->code."</a>";

            $material    = DB::table("cost_extra")->join("material", "material.code", "=", "cost_extra.code")
                                                            ->select(DB::RAW("coalesce(sum(material.price),0) as sum_price"))
                                                            ->where('cost_extra.cost_no',$v->no)
                                                            ->where('cost_extra.save_status','Y')
                                                            ->where('material.save_status','Y')
                                                            ->first();
            
            $v->count    = 1;
            $v->price    = $material->sum_price ?? 0;
            $v->balance  = $v->count*$v->price;

            $v->price    = number_format($v->price ?? 0);
            $v->balance  = number_format($v->balance);

            $r['v'][] = $v;
            $cnt ++;
        }
		
        // 페이징
        $r['pageList']  = $paging->getPagingHtml($request->path());
        $r['result']    = 1;
        $r['txt']       = $cnt;
        $r['totalCnt']  = $paging->getTotalCnt();

        return json_encode($r);
    }

    /**
     * 자재단가표 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementCostExcel(Request $request)
    {   
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        
        $list           = $this->setCostDataList($request);

        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        $LOAN_LIST = DB::table("cost")->join("contract_info", "contract_info.no", "=", "cost.contract_info_no")
                                        ->select("cost.*")
                                        ->where('contract_info.no',$param['contract_info_no'])
                                        ->where('contract_info.save_status','Y')
                                        ->where('cost.save_status','Y');
        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('cost.no','desc');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='cost.code' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('cost.code', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='cost.name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('cost.name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $LOAN_LIST = $list->getListQuery('cost', 'main', $LOAN_LIST, $param);

        $target_sql = urlencode(encrypt(Func::printQuery($LOAN_LIST))); // 페이지 들어가기 전에 쿼리를 저장해야한다.               
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN_LIST, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "일위대가_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $request_all['work_id'] = Auth::id();
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no))
        {
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
            $origin_filename = $excel_no.'_'.$request->work_id.'_'.date("YmdHis").'.xlsx'; 
        }
        else
        {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $target_sql, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }

            $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
        }

        $LOAN_LIST = $LOAN_LIST->GET();
        $LOAN_LIST = Func::chungDec(["contract_info", "cost"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT
        
        // 엑셀 헤더
        $excel_header   = array('코드','품명', '규격(1)', '규격(2)','단위', '수량', '단가','금액', '비고');
        $excel_data     = [];

        $array_config   = Func::getConfigArr();
        $arrManager     = Func::getUserList();

        foreach ($LOAN_LIST as $v)
        {
            $material = DB::table("cost_extra")->join("material", "material.code", "=", "cost_extra.code")
                                                            ->select(DB::RAW("coalesce(sum(material.price),0) as sum_price"))
                                                            ->where('cost_extra.cost_no',$v->no)
                                                            ->where('cost_extra.save_status','Y')
                                                            ->where('material.save_status','Y')
                                                            ->first();
            $array_data = [
                $v->code,                                           //코드
                $v->name,                                           //품명
                $v->standard1,                                      //규격(1)
                $v->standard2,                                      //규격(2)
                $v->type,                                           //단위
                number_format($v->count ?? 1),                      //수량
                number_format($material->sum_price ?? 0),                      //단가
                number_format(($v->count ?? 0)*($material->sum_price ?? 0)),   //금액
                $v->etc,                                            //기타
            ];
            
            $excel_data[] = $array_data;
            
            $record_count++;
        }
        
        // 엑셀 익스포트
        ExcelFunc::fastexcelExport($excel_data, $excel_header, $origin_filename);
    
        // 파일 저장 여부 확인
        $exists = Storage::disk('excel')->exists($origin_filename);
        
        if( isset($exists) )
        {
            $array_result['etc']             = $etc;
            $array_result['result']          = 'Y';
            $array_result['filename']        = $file_name;
            $array_result['excel_no']        = $excel_no;
            $array_result['record_count']    = $record_count;
            $array_result['down_filename']   = $down_filename;
            $array_result['excel_down_div']  = $excel_down_div;
            $array_result['origin_filename'] = $origin_filename;
            
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div,null, $origin_filename);  
        }
        else
        {
           $array_result['result']    = 'N';
           $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }
        
        return $array_result;
    }
    
    /**
     * 일위대가 등록 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementCostForm(Request $request)
    {
        $contract_info_no = $request->contract_info_no;

        return view('field.managementCostForm')->with("contract_info_no", $contract_info_no);
    }

    /*
     *  일위대가 폼 등록 액션

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function managementCostFormAction(Request $request)
    {
        $_DATA = $request->all();

        $_DATA['save_status'] = 'Y';
        $_DATA['save_id']     = Auth::id();
        $_DATA['save_time']   = date('Ymd');

        $result = DB::dataProcess('INS', 'cost', $_DATA);

        if( $result == "Y" )
        {
            $array_result['rs_code'] = "Y";
            $array_result['result_msg'] = "정상적으로 저장되었습니다.";
        }
        else
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "등록 오류";
        }

        return $array_result;
    }

	/**
	 * 자재단가표 검색 모달
	 *
	 * @param  Request $request
	 * @return view
	 */
	public function managementMaterialSearch(Request $request)
	{
		$query = DB::table('contract_info')
                    ->join("material", "material.contract_info_no", "=", "contract_info.no")
					->select('material.*')
					->where('contract_info.no', $request->contract_info_no)
					->where('contract_info.save_status', 'Y')
					->where('material.save_status', 'Y');

		if(isset($request->material_search_string))
		{
			$keyword = $request->material_search_string;
			$query = $query->where(function($q) use ($keyword) {
				$q->where('material.name', 'like', '%'.$keyword.'%')
				->orWhere('material.code', 'like', '%'.$keyword.'%')
				->orWhere('material.standard1', 'like', '%'.$keyword.'%')
				->orWhere('material.standard2', 'like', '%'.$keyword.'%')
				->orWhere('material.etc', 'like', '%'.$keyword.'%');
			});
		}

		$query = $query->orderBy('no', 'desc');

		// 총건수
		$result['cnt']      = $query->count();
		
		// 한페이지.
		$result['material'] = $query->limit(10)->get();
		$result['material'] = Func::chungDec(["contract_info","material"], $result['material']);	// CHUNG DATABASE DECRYPT

        return $result;
	}

    /**
     * 일위대가 팝업
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementCostPop(Request $request)
    {
        // 메인
        $v = DB::table("cost")->join("contract_info", "contract_info.no", "=", "cost.contract_info_no")
                                ->select("cost.*")
                                ->where('contract_info.no',$request->contract_info_no)
                                ->where('cost.no',$request->cost_no)
                                ->where('contract_info.save_status','Y')
                                ->where('cost.save_status','Y')
                                ->first();
        $v = Func::chungDec(["contract_info","cost"], $v);	// CHUNG DATABASE DECRYPT

        // 서브
        $cost_extra = DB::table("cost_extra")->join("cost", "cost.no", "=", "cost_extra.cost_no")
                                ->join("contract_info", "contract_info.no", "=", "cost.contract_info_no")
                                ->join("material", "material.code", "=", "cost_extra.code")
                                ->select("cost_extra.seq", "cost_extra.code", "cost_extra.volume", "material.name", "material.standard1", "material.standard2", "material.type", "material.price", "cost_extra.etc")
                                ->where('contract_info.no',$request->contract_info_no)
                                ->where('cost.no',$request->cost_no)
                                ->where('contract_info.save_status','Y')
                                ->where('cost.save_status','Y')
                                ->where('cost_extra.save_status','Y')
                                ->where('material.save_status','Y')
                                ->orderBy('cost_extra.seq', 'desc')
                                ->get();
        $cost_extra = Func::chungDec(["contract_info","cost","cost_extra","material"], $cost_extra);	// CHUNG DATABASE DECRYPT

        return view('field.managementCostPop')->with("v", $v)->with("cost_extra", $cost_extra);
    }

    /**
     * 일위대가 팝업 저장
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementCostPopAction(Request $request)
    {
        $_DATA = $request->all();

        if($_DATA['mode'] == 'DEL')
        {
            $_DATA['save_status'] = 'N';
            $_DATA['del_id']      = Auth::id();
            $_DATA['del_time']    = date('Ymd');

            // 메인삭제
            $result = DB::dataProcess('UPD', 'cost', $_DATA, ['no'=>$_DATA['cost_no']]);

            // 서브삭제
            $result = DB::dataProcess('UPD', 'cost_extra', $_DATA, ['cost_no'=>$_DATA['cost_no']]);
        }
        else
        {
            $_COST = array();
            $_COST['code']      = $_DATA['code'] ?? '';
            $_COST['name']      = $_DATA['name'] ?? '';
            $_COST['standard1'] = $_DATA['standard1'] ?? '';
            $_COST['standard2'] = $_DATA['standard2'] ?? '';
            $_COST['type']      = $_DATA['type'] ?? '';
            $_COST['etc']       = $_DATA['etc'] ?? '';

            // 메인저장
            $result = DB::dataProcess('UPD', 'cost', $_COST, ['no'=>$_DATA['cost_no']]);

            $_DEL = array();
            $_DEL['save_status'] = 'N';
            $_DEL['del_id']      = Auth::id();
            $_DEL['del_time']    = date('Ymd');

            // 서브삭제
            $result = DB::dataProcess('UPD', 'cost_extra', $_DEL, ['cost_no'=>$_DATA['cost_no']]);

            if(isset($_DATA['extra_code']))
            {
                $seq = 0;
                for($i=0;$i<sizeof($_DATA['extra_code']);$i++)
                {
                    $seq++;
                    
                    // 변수정리
                    $_COST_EXTRA = array();
                    $_COST_EXTRA['cost_no']          = $_DATA['cost_no'];
                    $_COST_EXTRA['contract_info_no'] = $_DATA['contract_info_no'];
                    $_COST_EXTRA['seq']              = $seq;
                    $_COST_EXTRA['code']             = $_DATA['extra_code'][$i];
                    $_COST_EXTRA['volume']           = sprintf('%0.3f', str_replace(',', '', $_DATA['extra_volume'][$i] ?? 0));
                    $_COST_EXTRA['etc']              = $_DATA['extra_etc'][$i];
                    $_COST_EXTRA['save_time']        = date("YmdHis");
                    $_COST_EXTRA['save_id']          = Auth::id();
                    $_COST_EXTRA['save_status']      = 'Y';

                    $result = DB::dataProcess('INS', 'cost_extra', $_COST_EXTRA);
                }
            }
        }

        if( $result == "Y" )
        {
            $array_result['rs_code'] = "Y";
            $array_result['result_msg'] = "정상적으로 저장되었습니다.";
        }
        else
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "등록 오류";
        }

        return $array_result;
    }

    /*
     *  일위대가 일괄삭제

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function managementCostAllClear(Request $request)
    {
        $_DATA = $request->all();
        
        $_DATA['save_status'] = 'N';
        $_DATA['del_id']      = Auth::id();
        $_DATA['del_time']    = date('Ymd');

        // 메인
        $result = DB::dataProcess('UPD', 'cost', $_DATA, ['contract_info_no'=>$_DATA['contract_info_no']]);

        // 서브
        $result = DB::dataProcess('UPD', 'cost_extra', $_DATA, ['contract_info_no'=>$_DATA['contract_info_no']]);

        if( $result == "Y" )
        {
            $array_result['rs_code'] = "Y";
            $array_result['result_msg'] = "정상적으로 저장되었습니다.";
        }
        else
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "등록 오류";
        }

        return $array_result;
    }

    /**
     * 일위대가 엑셀업로드 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementCostExcelForm(Request $request)
    {
        return view('field.managementCostExcelForm')->with("contract_info_no", $request->contract_info_no);
    }

    /**
     * 일위대가 엑셀업로드 샘플
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementCostExcelSample(Request $request)
    {
        if(Storage::disk('management')->exists('costExcelSample.xlsx'))
        {
            return Storage::disk('management')->download('costExcelSample.xlsx', '일위대가업로드예시파일.xlsx');
        }
        else
        {
            log::debug("샘플파일 없음");
        }
    }

    /**
     * 일위대가 엑셀업로드 액션
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementCostExcelAction(Request $request)
    {
        if(empty($request->contract_info_no))
        {
            $r['rs_code'] = "N";
            $r['rs_msg']  = "현장번호가 존재하지 않습니다.";

            return $r;
        }

        if( $request->file('excel_data') )
        {
            // 엑셀 저장
            $file_path = $request->file('excel_data')->store("upload/".date("Ymd"), 'management');
            
            // 경로세팅 
            if(Storage::disk('management')->exists($file_path))
            {
                $colHeader  = array(
                    "구분",
                    "코드",
                    "품명",
                    "규격(1)",
                    "규격(2)",
                    "단위",
                    "단가",
                    "기타"
                );
                $colNm = array(
                    "category"      => "0",	      // 구분
                    "code"	        => "1",	      // 코드
                    "name"          => "2",       // 품명
                    "standard1"     => "3",       // 규격(1)
                    "standard2"     => "4",       // 규격(2)
                    "type"          => "5",       // 단위
                    "price"         => "6",       // 단가
                    "etc"           => "7",       // 기타
                );
                                    
                $file = Storage::path('/management/'.$file_path);
                
                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    // 파일경로
                    log::debug($file_path);

                    $r['rs_code'] = "N";
                    $r['rs_msg']  = "엑셀 유효성 검사를 실패하였습니다.";

                    return $r;
                }
                else
                {
                    foreach($excelData as $_DATA) 
                    {
                        unset($_INS);

                        // 데이터 정리
                        foreach($_DATA as $key => $val) 
                        {
                            $val = trim($val);
                            $_INS[$key] = $val;
                        }

                        // 데이터 추출 및 정리
                        foreach($_INS as $key => $val)
                        {
                            // 값이 없으면 unset
                            if($val == "")
                            {
                                unset($_INS[$key]);
                                continue;
                            }

                            // 값이 null 이면 빈값으로 업데이트
                            if($val == "null" || $val == "NULL")
                            {
                                $_INS[$key] = '';
                                continue;
                            }
                        }

                        if(!isset($_INS['category']) || $_INS['category'] == '')
                        {
                            continue;
                        }
                        if(!isset($_INS['code']) || $_INS['code'] == '')
                        {
                            continue;
                        }
                        if(!isset($_INS['name']) || $_INS['name'] == '')
                        {
                            continue;
                        }

                        if(!empty($_INS['price']))
                        {
                            $_INS['price'] = preg_replace('/[^0-9]/', '', $_INS['price']);
                        }

                        $_INS['contract_info_no'] = $request->contract_info_no;
                        $_INS['file_path']        = $file_path;

                        $_INS['save_status']      = 'Y';
                        $_INS['save_id']          = Auth::id();
                        $_INS['save_time']        = date('Ymd');

                        $rslt = DB::dataProcess('INS', 'material', $_INS);
                    }
                }

                $r['rs_code'] = "Y";
                $r['rs_msg']  = "엑셀 업로드를 성공하였습니다.";

                return $r;
            }
            else 
            {
                log::debug($file_path ?? '파일경로 없음');

                $r['rs_code'] = "N";
                $r['rs_msg']  = "엑셀 업로드를 실패했습니다.";

                return $r;
            }
        }
        else
        {
            $r['rs_code'] = "N";
            $r['rs_msg']  = "엑셀을 등록해주세요.";

            return $r;
        }
    }

    /**
     * 현장정보 팝업창 - 자재단가표
     * 자재단가표 리스트 공통 세팅 내용 
     *
     * @param  request
     * @return dataList
     */
    private function setMaterialDataList(Request $request)
    {
        $list = new DataList(Array("listName"=>"managementMaterial","listAction"=>'/'.$request->path()));

        // $list->setRangeSearchDetail(Array('material.price'=>'단가'),'','','단위(원)');

        $list->setSearchDetail(Array(
            'material.category'   => '구분',
            'material.code'       => '코드',
            'material.name'       => '품명',
        ));

        return $list;
    }

    /**
     * 현장정보 팝업창 - 자재단가표
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementMaterial(Request $request)
    {
        $list = $this->setMaterialDataList($request);
        
        $contract_info_no = $request->contract_info_no;

        $list->setButtonArray("일괄삭제", "managementMaterialAllClear('".$contract_info_no."')", "btn-danger");

        $list->setButtonArray("엑셀업로드", "managementMaterialExcelForm('".$contract_info_no."')", "btn-info");

        $list->setButtonArray("엑셀다운","excelDownModal('/field/managementmaterialexcel','form_managementMaterial')","btn-success");
        
        $list->setPlusButton("managementMaterialForm('".$contract_info_no."', '');");
        
        $list->setViewNum(false);

        if(is_numeric($contract_info_no))
        {
            $v = DB::table("contract_info")->SELECT("*")->WHERE('save_status','Y')->WHERE("no", $contract_info_no)->first();
            $v = Func::chungDec(["contract_info"], $v);	// CHUNG DATABASE DECRYPT
        }
        
        $list->setHidden(Array('contract_info_no' => $request->contract_info_no));

        $list->setlistTitleCommon(Array
        (
            'category'              => Array('구분', 1, '', 'center', '', 'category'),
            'code'                  => Array('코드', 0, '', 'center', '', 'code'),
            'name'                  => Array('품명', 1, '', 'center', '', 'name'),
            'standard1'             => Array('규격(1)', 1, '', 'center', '', 'standard1'),
            'standard2'             => Array('규격(2)', 1, '', 'center', '', 'standard2'),
            'type'                  => Array('단위', 1, '', 'center', '', 'type'),
            'volume'                => Array('수량', 1, '', 'center', '', ''),
            'price'                 => Array('단가', 1, '', 'center', '', 'price'),
            'balance'               => Array('금액', 0, '', 'center', '', ''),
            'etc'                   => Array('기타', 1, '', 'center', '', 'etc'),
        ));

        return view('field.managementMaterial')->with('v', $v)->with('result', $list->getList());
    }

    /**
     * 현장정보 팝업창 - 자재단가표 리스트
    *
    * @param  \Illuminate\Http\Request  $request
    * @return JSON
    */
    public function managementMaterialList(Request $request)
    {
        $param = $request->all();

        $LOAN_LIST = DB::TABLE("contract_info")
                            ->join("material", "material.contract_info_no", "=", "contract_info.no")
                            ->select("material.*")
                            ->where("contract_info.no",$param['contract_info_no'])
                            ->where('material.save_status','Y')
                            ->where('contract_info.save_status','Y');

        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('no','desc');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='material.category' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('material.category', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='material.code' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('material.code', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='material.name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('material.name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }
        
        // 페이징 (쿼리빌더, 현재 페이지, 표시 데이터 행, 표시 페이지 수)
        $paging = new Paging($LOAN_LIST, $request->page, $request->listLimit, 10, $request->listName);
        
        // 결과
        $LOAN_LIST = $LOAN_LIST->get();
        $LOAN_LIST = Func::chungDec(["contract_info", "material"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT

        // 뷰단 데이터 정리.
        $cnt = 0;
        foreach ($LOAN_LIST as $v)
        {
            $cost_extra             = DB::table("cost_extra")->select(DB::RAW("coalesce(sum(volume),0) as sum_volume"))
                                                            ->where('contract_info_no',$v->contract_info_no)
                                                            ->where('code',$v->code)
                                                            ->where('save_status','Y')
                                                            ->first();

            $v->volume              = $cost_extra->sum_volume;
            $v->price               = $v->price ?? 0;
            $v->balance             = $v->volume*$v->price;

            $v->price               = number_format($v->price ?? 0);
            $v->balance             = number_format($v->balance);

            $link_c                 = '<a class="hand" onClick="managementMaterialForm(\''.$v->contract_info_no.'\', \''.$v->no.'\')">';
            $v->code                = $link_c.$v->code;

            $r['v'][] = $v;
            $cnt ++;
        }
		
        // 페이징
        $r['pageList']  = $paging->getPagingHtml($request->path());
        $r['result']    = 1;
        $r['txt']       = $cnt;
        $r['totalCnt']  = $paging->getTotalCnt();

        return json_encode($r);
    }

    /**
     * 자재단가표 엑셀다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementMaterialExcel(Request $request)
    {   
        // 실행시간 제어
        ini_set('max_execution_time', 1200);
        ini_set('memory_limit','-1');
        
        $list           = $this->setMaterialDataList($request);

        $param          = $request->all();
        $down_div       = $request->down_div;
        $down_filename  = $request->down_filename;
        $excel_down_div = $request->excel_down_div;
        $etc            = $request->etc;

        $LOAN_LIST = DB::TABLE("contract_info")
                            ->join("material", "material.contract_info_no", "=", "contract_info.no")
                            ->select("material.*")
                            ->where("contract_info.no",$param['contract_info_no'])
                            ->where('material.save_status','Y')
                            ->where('contract_info.save_status','Y');

        // 정렬
        if($param['listOrder'])
        {
            $LOAN_LIST = $LOAN_LIST->orderBy($param['listOrder'], $param['listOrderAsc']);
        }
        else
        {
            $LOAN_LIST = $LOAN_LIST->orderBy('no','desc');
        }

        if(isset( $param['searchDetail']) && $param['searchDetail']=='material.category' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('material.category', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='material.code' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('material.code', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }
        if(isset( $param['searchDetail']) && $param['searchDetail']=='material.name' && !empty($param['searchString']) )
        {
            $LOAN_LIST = $LOAN_LIST->where('material.name', 'like','%'.$param['searchString'].'%');
            unset($param['searchString']);
        }

        // 상세검색 미선택 후 텍스트 입력시 텍스트 데이터 제거
        if(empty( $param['searchDetail']) && !empty($param['searchString']) )
        {
            unset($param['searchString']);
        }

        $LOAN_LIST = $list->getListQuery('contract_info', 'main', $LOAN_LIST, $param);

        $target_sql = urlencode(encrypt(Func::printQuery($LOAN_LIST))); // 페이지 들어가기 전에 쿼리를 저장해야한다.               
        
        // 현재 페이지 출력 
        if( $down_div=='now' )
        {
            // 페이징 가져와서 동일하게 엑셀도 출력 해주기 
            $paging = new Paging($LOAN_LIST, $request->nowPage, $request->listLimit, 10, $request->listName);
        }

        // 엑셀다운 로그 시작
        $record_count = 0;
        $file_name    = "자재단가표_".date("YmdHis").'_'.Auth::id().'.xlsx';
        $request_all  = $request->all();
        $request_all['class'] = __CLASS__;
        $request_all['work_id'] = Auth::id();
        $all_data     = json_encode($request_all, true);

        if(!empty($request->excel_no))
        {
            $file_name = $request->file_name;
            $excel_no = $request->excel_no;
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div);
            $excel_down_div = 'A';
            $origin_filename = $excel_no.'_'.$request->work_id.'_'.date("YmdHis").'.xlsx'; 
        }
        else
        {
            $excel_no       = ExcelFunc::setExcelDownLog("INS",$param['excelDownCd'],$file_name, $target_sql, $record_count,$param['etc'],null, $down_filename, $excel_down_div, $all_data);
            if($excel_down_div == 'S')
            {
                $yet['result']  = 'Y';
                return $yet;
            }

            $origin_filename = $excel_no.'_'.Auth::id().'_'.date("YmdHis").'.xlsx';
        }

        $LOAN_LIST = $LOAN_LIST->GET();
        $LOAN_LIST = Func::chungDec(["contract_info", "material"], $LOAN_LIST);	// CHUNG DATABASE DECRYPT
        
        // 엑셀 헤더
        $excel_header   = array('구분', '코드','품명', '규격(1)', '규격(2)','단위', '수량', '단가','금액','기타');
        $excel_data     = [];

        $array_config   = Func::getConfigArr();
        $arrManager     = Func::getUserList();

        foreach ($LOAN_LIST as $v)
        {
            $cost_extra = DB::table("cost_extra")->select(DB::RAW("coalesce(sum(volume),0) as sum_volume"))
                                                    ->where('contract_info_no',$v->contract_info_no)
                                                    ->where('code',$v->code)
                                                    ->where('save_status','Y')
                                                    ->first();
            $array_data = [
                $v->category,                                       //구분
                $v->code,                                           //코드
                $v->name,                                           //품명
                $v->standard1,                                      //규격(1)
                $v->standard2,                                      //규격(2)
                $v->type,                                           //단위
                number_format($cost_extra->sum_volume),             //수량
                number_format($v->price ?? 0),                      //단가
                number_format(($cost_extra->sum_volume)*($v->price ?? 0)),   //금액
                $v->etc,                                            //기타
            ];
            
            $excel_data[] = $array_data;
            
            $record_count++;
        }
        
        // 엑셀 익스포트
        ExcelFunc::fastexcelExport($excel_data, $excel_header, $origin_filename);
    
        // 파일 저장 여부 확인
        $exists = Storage::disk('excel')->exists($origin_filename);
        
        if( isset($exists) )
        {
            $array_result['etc']             = $etc;
            $array_result['result']          = 'Y';
            $array_result['filename']        = $file_name;
            $array_result['excel_no']        = $excel_no;
            $array_result['record_count']    = $record_count;
            $array_result['down_filename']   = $down_filename;
            $array_result['excel_down_div']  = $excel_down_div;
            $array_result['origin_filename'] = $origin_filename;
            
            ExcelFunc::setExcelDownLog("UPD", null, null, null, $record_count, null, $excel_no, $down_filename, $excel_down_div,null, $origin_filename);  
        }
        else
        {
           $array_result['result']    = 'N';
           $array_result['error_msg'] = "파일생성에 실패하였습니다. \n"; 
        }
        
        return $array_result;
    }

    /**
     * 자재단가표 등록 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementMaterialForm(Request $request)
    {
        $v = [];

        $contract_info_no = $request->contract_info_no;
        $material_no      = $request->material_no ?? 0;

        if(!empty($material_no))
        {
            $v = DB::table("material")->select("*")->where('no',$material_no)->where('save_status','Y')->first();
        }

        return view('field.managementMaterialForm')->with("contract_info_no", $contract_info_no)->with("v", $v);
    }

    /*
     *  자재단가표 폼 등록 액션

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function managementMaterialFormAction(Request $request)
    {
        $_DATA = $request->all();
        
        if($_DATA['mode'] == 'DEL')
        {
            $_DATA['save_status'] = 'N';
            $_DATA['del_id']      = Auth::id();
            $_DATA['del_time']    = date('Ymd');

            $result = DB::dataProcess('UPD', 'material', $_DATA, ['no'=>$_DATA['material_no']]);
        }
        else if($_DATA['mode'] == 'UPD')
        {
            $code = DB::table("material")->select("no")->where('contract_info_no',$_DATA['contract_info_no'])->where('code',$_DATA['code'])->where('save_status','Y')->first();
            if(!empty($code->no))
            {
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "중복된 코드 입니다.";
            }

            $_DATA['save_id']     = Auth::id();
            $_DATA['save_time']   = date('Ymd');

            $result = DB::dataProcess('UPD', 'material', $_DATA, ['no'=>$_DATA['material_no']]);
        }
        else
        {
            $code = DB::table("material")->select("no")->where('contract_info_no',$_DATA['contract_info_no'])->where('code',$_DATA['code'])->where('save_status','Y')->first();
            if(!empty($code->no))
            {
                $array_result['rs_code']    = "N";
                $array_result['result_msg'] = "중복된 코드 입니다.";
            }

            $_DATA['save_status'] = 'Y';
            $_DATA['save_id']     = Auth::id();
            $_DATA['save_time']   = date('Ymd');

            $result = DB::dataProcess('INS', 'material', $_DATA);
        }

        if( $result == "Y" )
        {
            $array_result['rs_code'] = "Y";
            $array_result['result_msg'] = "정상적으로 저장되었습니다.";
        }
        else
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "등록 오류";
        }

        return $array_result;
    }

    /*
     *  자재단가표 일괄삭제

        @param  \Illuminate\Http\Request  $request
        @return String
    */
    public function managementMaterialAllClear(Request $request)
    {
        $_DATA = $request->all();
        
        $_DATA['save_status'] = 'N';
        $_DATA['del_id']      = Auth::id();
        $_DATA['del_time']    = date('Ymd');

        $result = DB::dataProcess('UPD', 'material', $_DATA, ['contract_info_no'=>$_DATA['contract_info_no']]);

        if( $result == "Y" )
        {
            $array_result['rs_code'] = "Y";
            $array_result['result_msg'] = "정상적으로 저장되었습니다.";
        }
        else
        {
            $array_result['rs_code'] = "N";
            $array_result['result_msg'] = "등록 오류";
        }

        return $array_result;
    }

    /**
     * 자재단가표 엑셀업로드 폼
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementMaterialExcelForm(Request $request)
    {
        return view('field.managementMaterialExcelForm')->with("contract_info_no", $request->contract_info_no);
    }

    /**
     * 자재단가표 엑셀업로드 샘플
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementMaterialExcelSample(Request $request)
    {
        if(Storage::disk('management')->exists('materialExcelSample.xlsx'))
        {
            return Storage::disk('management')->download('materialExcelSample.xlsx', '자재단가표업로드예시파일.xlsx');
        }
        else
        {
            log::debug("샘플파일 없음");
        }
    }

    /**
     * 자재단가표 엑셀업로드 액션
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function managementMaterialExcelAction(Request $request)
    {
        if(empty($request->contract_info_no))
        {
            $r['rs_code'] = "N";
            $r['rs_msg']  = "현장번호가 존재하지 않습니다.";

            return $r;
        }

        if( $request->file('excel_data') )
        {
            // 엑셀 저장
            $file_path = $request->file('excel_data')->store("upload/".date("Ymd"), 'management');
            
            // 경로세팅 
            if(Storage::disk('management')->exists($file_path))
            {
                $colHeader  = array(
                    "구분",
                    "코드",
                    "품명",
                    "규격(1)",
                    "규격(2)",
                    "단위",
                    "단가",
                    "기타"
                );
                $colNm = array(
                    "category"      => "0",	      // 구분
                    "code"	        => "1",	      // 코드
                    "name"          => "2",       // 품명
                    "standard1"     => "3",       // 규격(1)
                    "standard2"     => "4",       // 규격(2)
                    "type"          => "5",       // 단위
                    "price"         => "6",       // 단가
                    "etc"           => "7",       // 기타
                );
                                    
                $file = Storage::path('/management/'.$file_path);
                
                $excelData  = ExcelFunc::readExcel($file, $colNm, 0, 0, $colHeader,0);

                // 엑셀 유효성 검사
                if(!isset($excelData))
                {
                    // 파일경로
                    log::debug($file_path);

                    $r['rs_code'] = "N";
                    $r['rs_msg']  = "엑셀 유효성 검사를 실패하였습니다.";

                    return $r;
                }
                else
                {
                    foreach($excelData as $_DATA) 
                    {
                        unset($_INS);

                        // 데이터 정리
                        foreach($_DATA as $key => $val) 
                        {
                            $val = trim($val);
                            $_INS[$key] = $val;
                        }

                        // 데이터 추출 및 정리
                        foreach($_INS as $key => $val)
                        {
                            // 값이 없으면 unset
                            if($val == "")
                            {
                                unset($_INS[$key]);
                                continue;
                            }

                            // 값이 null 이면 빈값으로 업데이트
                            if($val == "null" || $val == "NULL")
                            {
                                $_INS[$key] = '';
                                continue;
                            }
                        }

                        if(!isset($_INS['category']) || $_INS['category'] == '')
                        {
                            continue;
                        }
                        if(!isset($_INS['code']) || $_INS['code'] == '')
                        {
                            continue;
                        }
                        if(!isset($_INS['name']) || $_INS['name'] == '')
                        {
                            continue;
                        }

                        // 중복코드
                        $code = DB::table("material")->select("no")->where('contract_info_no',$_INS['contract_info_no'])->where('code',$_INS['code'])->where('save_status','Y')->first();
                        if(!empty($code->no))
                        {
                            continue;
                        }

                        if(!empty($_INS['price']))
                        {
                            $_INS['price'] = preg_replace('/[^0-9]/', '', $_INS['price']);
                        }

                        $_INS['contract_info_no'] = $request->contract_info_no;
                        $_INS['file_path']        = $file_path;

                        $_INS['save_status']      = 'Y';
                        $_INS['save_id']          = Auth::id();
                        $_INS['save_time']        = date('Ymd');

                        $rslt = DB::dataProcess('INS', 'material', $_INS);
                    }
                }

                $r['rs_code'] = "Y";
                $r['rs_msg']  = "엑셀 업로드를 성공하였습니다.";

                return $r;
            }
            else 
            {
                log::debug($file_path ?? '파일경로 없음');

                $r['rs_code'] = "N";
                $r['rs_msg']  = "엑셀 업로드를 실패했습니다.";

                return $r;
            }
        }
        else
        {
            $r['rs_code'] = "N";
            $r['rs_msg']  = "엑셀을 등록해주세요.";

            return $r;
        }
    }
}