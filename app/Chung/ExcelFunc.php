<?php
namespace App\Chung;

use App\Models\User;
use DB;
use DBD;
use Auth;
use Log;
use Storage;
use Sum;
use Excel;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Chung\ExcelCustomExport;
use App\Chung\ExcelCustomImport;
use App\Chung\ExcelCustomSheets;
use PhpOffice\PhpSpreadsheet\Shared\Date;
use Func;
use FastExcel;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Style\Border;
class ExcelFunc
{
    /**
     * 엑셀 다운로드 항목 대상
     * 엑셀 헤더와 데이터 출력항목 선택 기능 구현으로 헤더와 데이터 간의 직접적으로 관계키를 구성할 요소가 없어서 일련번호를 기준으로 설정.
     * 엑셀 데이터 설정시 일련번호와 헤더의 일련번호를 일치시켜야함.
     * @param  호출구분
     * @return 엑셀헤더Array
     */
    public static function getExcelHeader($excelUrl, $tabsSelect="")
    {
        $arrayHeader =  array();
        // 회수관리 > 채권현황조회
        switch($excelUrl)
        {
            case "/erp/loan":
                $arrayHeader = array(
                    '차입자번호'           // HeaderIdx : 0
                    ,'계약번호'             // HeaderIdx : 1
                    ,'상품명'               // HeaderIdx : 2
                    ,'성명'                 // HeaderIdx : 3
                    ,'주민등록'             // HeaderIdx : 4
                    ,'관계'                 // HeaderIdx : 5
                    ,'성별'                 // HeaderIdx : 6
                    ,'연령'                 // HeaderIdx : 7
                    ,'자격구분'             // HeaderIdx : 8
                    ,'부서'                 // HeaderIdx : 9
                    ,'담당'                 // HeaderIdx : 10
                    ,'상태'                 // HeaderIdx : 11
                    ,'약정일'               // HeaderIdx : 12
                    ,'유형'                 // HeaderIdx : 13
                    ,'상품'                 // HeaderIdx : 14
                    ,'상환방법'             // HeaderIdx : 15
                    ,'최근대출'             // HeaderIdx : 16
                    ,'계약일'               // HeaderIdx : 17
                    ,'만기일'               // HeaderIdx : 18
                    ,'이율'                 // HeaderIdx : 19
                    ,'연체이율'             // HeaderIdx : 20
                    ,'한도'                 // HeaderIdx : 21
                    ,'월상환'               // HeaderIdx : 22
                    ,'최초대출'             // HeaderIdx : 23
                    ,'총대출'               // HeaderIdx : 24
                    ,'차기상환일'           // HeaderIdx : 25
                    ,'차기상환일 원리금'    // HeaderIdx : 26
                    ,'최근입금일'           // HeaderIdx : 27
                    ,'최근입금액'           // HeaderIdx : 28
                    ,'미수비용'             // HeaderIdx : 29
                    ,'총이자'               // HeaderIdx : 30
                    ,'총 투자원금상환'          // HeaderIdx : 31
                    ,'총 상환이자'          // HeaderIdx : 32
                    ,'총 상환비용'          // HeaderIdx : 33
                    ,'사용기간'             // HeaderIdx : 34
                    ,'잔액'                 // HeaderIdx : 35
                    ,'소득금액'             // HeaderIdx : 36
                    ,'용도'                 // HeaderIdx : 37
                    ,'지역'                 // HeaderIdx : 38
                    ,'신청경로'             // HeaderIdx : 39
                    ,'연체일'               // HeaderIdx : 40
                    ,'최대연체일'           // HeaderIdx : 41
                    ,'누적연체일'           // HeaderIdx : 42
                    ,'연체횟수'             // HeaderIdx : 43
                    ,'징구서류상태'         // HeaderIdx : 44
                    ,'실거주우편번호'       // HeaderIdx : 45
                    ,'실거지주소'           // HeaderIdx : 46
                    ,'핸드폰'               // HeaderIdx : 47
                    ,'중도상환수수료'       // HeaderIdx : 48
                    ,'중도상환수수료율'     // HeaderIdx : 49
                    ,'매입일'               // HeaderIdx : 50
                    ,'영업부채권'           // HeaderIdx : 51
                    ,'약속일'               // HeaderIdx : 52
                    ,'원채권사'             // HeaderIdx : 53
                );
                if(Func::funcCheckPermit("R006"))
                {
                    array_push( $arrayHeader, '매입가' );   // HeaderIdx : 54
                }
                array_push( $arrayHeader, '가수금' );       // HeaderIdx : 55
                break;
            case "/erp/guarantor":
                $arrayHeader = array(
                    '보증인번호'    // HeaderIdx : 0
                    ,'차입자번호' // HeaderIdx : 1
                    ,'계약번호'     // HeaderIdx : 2
                    ,'고객명'       // HeaderIdx : 3
                    ,'보증인명'     // HeaderIdx : 4
                    ,'주민등록'     // HeaderIdx : 5
                    ,'유효'         // HeaderIdx : 6
                    ,'상품명'       // HeaderIdx : 7
                    ,'동거'         // HeaderIdx : 8
                    ,'관계'         // HeaderIdx : 9
                    ,'집전화'       // HeaderIdx : 10
                    ,'휴대전화'     // HeaderIdx : 11
                    ,'직장전화'     // HeaderIdx : 12
                    ,'직장명'       // HeaderIdx : 13
                );
                break;
            case "/erp/settle":
                $arrayHeader = array(
                    '일련번호'      // HeaderIdx : 0
                    ,'차입자번호' // HeaderIdx : 1
                    ,'계약번호'     // HeaderIdx : 2
                    ,'신규여부'     // HeaderIdx : 3
                    ,'이름'         // HeaderIdx : 4
                    ,'생년월일'     // HeaderIdx : 5
                    ,'관리지점'     // HeaderIdx : 6
                    ,'상품구분'     // HeaderIdx : 7
                    ,'연체일수'     // HeaderIdx : 8
                    ,'화해상세구분' // HeaderIdx : 9
                    ,'화해사유'     // HeaderIdx : 10
                    ,'신청일'       // HeaderIdx : 11
                    ,'화해기준일'   // HeaderIdx : 12
                    ,'초입금금액'   // HeaderIdx : 13
                    ,'원금'         // HeaderIdx : 14
                    ,'화해금액'     // HeaderIdx : 15
                    ,'화해감면금액' // HeaderIdx : 16
                    ,'분납회차(회)' // HeaderIdx : 17
                    ,'결재상태'     // HeaderIdx : 18
                    ,'결재일'       // HeaderIdx : 19
                    ,'결재사번'     // HeaderIdx : 20
                );
                if( $tabsSelect=="DEL" )
                {
                    array_push( $arrayHeader, '취소일시' ); // HeaderIdx : 21
                }
                break;
            case "/erp/settleinfo":
                $arrayHeader = array(
                    '화해번호'      // HeaderIdx : 0
                    ,'차입자번호' // HeaderIdx : 1
                    ,'계약번호'     // HeaderIdx : 2
                    ,'이름'         // HeaderIdx : 3
                    ,'생년월일'     // HeaderIdx : 4
                    ,'상품명'       // HeaderIdx : 5
                    ,'관리지점'     // HeaderIdx : 6
                    ,'상태'         // HeaderIdx : 7
                    ,'연체일'       // HeaderIdx : 8
                    ,'상환방식'     // HeaderIdx : 9
                    ,'계약일'       // HeaderIdx : 10
                    ,'상환일'       // HeaderIdx : 11
                    ,'잔액'         // HeaderIdx : 12
                    ,'화해일자'     // HeaderIdx : 13
                    ,'화해금액'     // HeaderIdx : 14
                    ,'분납회차'     // HeaderIdx : 15
                    ,'다음상환회차' // HeaderIdx : 16
                    ,'화해회차'     // HeaderIdx : 17
                    ,'화해접수일'     // HeaderIdx : 18
                    ,'화해접수자'     // HeaderIdx : 19
                    ,'화해최종결재일' // HeaderIdx : 20
                    ,'화해최종결재자' // HeaderIdx : 21
                    ,'화해직전잔액'   // HeaderIdx : 22
                    ,'화해폐지일'     // HeaderIdx : 23
                );
                break;
            case "/erp/tradeinterest":
                $arrayHeader = array(
                    'NO'      // HeaderIdx : 0
                    ,'채권번호' // HeaderIdx : 1
                    ,'차입자명'     // HeaderIdx : 2
                    ,'투자자명'         // HeaderIdx : 3
                    ,'투자자 주민등록번호'     // HeaderIdx : 4
                    ,'상태'       // HeaderIdx : 5
                    ,'상품명'     // HeaderIdx : 6
                    ,'계약일'         // HeaderIdx : 7
                    ,'만기일'       // HeaderIdx : 8
                    ,'투자잔액'     // HeaderIdx : 9
                    ,'이자'       // HeaderIdx : 10
                    ,'차기수익지급일'       // HeaderIdx : 11
                    ,'차기수익지급금'         // HeaderIdx : 12
                );
                break;
            default:
                break;
        }
        
        return (sizeof($arrayHeader) > 0) ? $arrayHeader : null;
    }

    /**
	* 엑셀 다운로드 로그 남기기
	*
	* @param  구분, 엑셀다운코드,파일이름,쿼리, 라인수, 일련번호
	* @return $mode ins일 경우 $no | upd 일경우 'E','Y','N'
	*/
    public static function setExcelDownLog($mode, $down_cd=null, $filename=null, $query=null, $line=0, $etc=null, $no=null, $down_filename=null, $excel_down_div="S", $request=null, $origin_filename=null)
    {
        // 실행시작 로그 
        if( $mode=='INS' ) 
        {  
            $_DATA['req_time']      = date("YmdHis");
            $_DATA['start_time']    = date("YmdHis");
            $_DATA['id']            = !empty(Auth::id())?Auth::id():"SYSTEM";
            $_DATA['branch']        = !empty(Auth::user()->branch_code)?Auth::user()->branch_code:"0000";
            $_DATA['filename']      = $filename; 
            $_DATA['down_filename'] = $down_filename; 
            $_DATA['query_string']  = $query;
            $_DATA['rsn_cd']        = $down_cd; 
            $_DATA['etc']           = $etc; 
            $_DATA['status']        = $excel_down_div;
            $_DATA['record_count']  = $line;
            if($excel_down_div == 'S'){
                $_DATA['request']  = $request;
            }
          
            $rs = DB::dataProcess($mode,'excel_down_log', $_DATA, null, $no);
            if(isset($rs) && $rs == 'Y')
            {
                return $no;
            }
            else
            {
                return false;
            }
        }
        // 바로실행완료 로그
        else if( $mode=="UPD" && isset($no) && $excel_down_div == "E" )
        {
            $_DATA['end_time']     = date("YmdHis");
            $_DATA['status']       = "E";
            $_DATA['record_count'] = $line;
            $_DATA['origin_filename'] = $origin_filename; // 이거 추가

            return DB::dataProcess($mode,'EXCEL_DOWN_LOG', $_DATA, ["NO"=>$no]);
        }
        //예약내역에서 실행으로 변경
        else if( $excel_down_div == "S" )
        {
            // 대기에서 진행으로 전환
            $_DATA['start_time']   = date("YmdHis");
            $_DATA['status']       = "A";

            return DB::dataProcess($mode,'EXCEL_DOWN_LOG', $_DATA, ["NO"=>$no]);
        }
        //예약내역에서 완료로 변경
        else if( $excel_down_div == "A" )
        {
            // 진행에서 완료로 전환
            $_DATA['end_time']     = date("YmdHis");
            $_DATA['status']       = "D";
            $_DATA['record_count'] = $line;
            $_DATA['origin_filename'] = $origin_filename; //요거 추가

            return DB::dataProcess($mode,'EXCEL_DOWN_LOG', $_DATA, ["NO"=>$no]);
        }
        else
        {
            return false;
        }
    }

/**
	* fast-excel 
	*
	* @param   excel_data(array)
	* @return boolean
	*/
    public static function fastexcelExport($excel_data,$excel_header,$file_name)
    {
        try {
            array_unshift($excel_data,$excel_header);
            return FastExcel::data($excel_data)->withoutHeaders()->export(Storage::path("excel/".$file_name));
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }
    
    /**
	* 기본 엑셀 다운로드 함수 시트하나에 data리스트를 뽑는다 
	*
	* @param  filename, head(array), data(array)
	* @return  boolean
	*/
    public static function storeExcel($fileName,$_head,$_DATA,$title='',$style=array())
    {
        try {
            return Excel::store(new ExcelCustomExport($_head,$_DATA,$title,$style),'/'.$fileName);
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    /**
	* 기본 엑셀 다운로드 함수 시트하나에 data리스트를 뽑는다 
	*
	* @param  filename, head(array), data(array)
	* @return  boolean
	*/
    public static function downExcel($fileName,$_head,$_DATA,$title='',$style=array())
    {
        try {
            return Excel::download(new ExcelCustomExport($_head,$_DATA,$title,$style),$fileName);
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

     /**
	* 기본 엑셀 다운로드 함수 시트하나에 data리스트를 뽑는다 
	*
	* @param  filename, head(array), data(array)
	* @return  boolean
	*/
    public static function downExcelSheet($_DATA,$fileName)
    {
        try {
            return (new ExcelCustomSheets($_DATA))->store('excel/'.$fileName);
        } catch (Exception $e) {
            report($e);
            return false;
        }
    }

    
    /**
	*   엑셀 읽어서 전달하기
	*
	* @param  file, colum명(array),읽을 시트번호
	* @return  boolean
	*/
    public static function readExcel($file,$colNm,$headNm=1,$sheet=0,$colHeader=array(),$max_cnt=0)
    {
        $data = array();

        $results =  Excel::toArray(new ExcelCustomImport, $file);
        if(count($colNm)>1){
            foreach($results[$sheet] as $i => $v){
                if($i<$headNm) continue;
                if($i==$headNm){
                    // 엑셀 형식 검증
                    if(count($colHeader)>1){
                        foreach($colHeader as $in => $head){
                            if(str_replace(" ","",$v[$in])!=str_replace(" ","",$head)){
                                Log::debug("EXCEL ERR:".str_replace(" ","",$v[$in])."!=".str_replace(" ","",$head));
                                Log::debug($v);
                                return null;
                            } 
                        }
                    }
                }else{
                    $row = [];
                    foreach($colNm as $col=>$num){
                        $row[$col] = $v[$num] ?? ''; // 해당 index 없는 경우 공백 return
                        if(strpos($col,"date_format")!== false ) 
                        {
                            echo $col." : ".$v[$num]."\n";
                            if($v[$num]!='')
                            {
                                if(strlen($v[$num])==10)
                                {
                                    $v[$num] = str_replace('-', '', $v[$num]);
                                }
                                else if(strlen($v[$num])==8)
                                {
                                    $v[$num] = $v[$num];
                                } 
                                else 
                                {
                                    $v[$num] = Date::excelToDateTimeObject($v[$num])->format("Ymd");
                                }
                                $row[str_replace("date_format","date",$col)] = $v[$num];
                            }
                        }
                    }
                    $data[$i]=$row;
                }
                if($max_cnt>0 && $max_cnt<$i) break;
            }
        }else{
            $data = $results[$sheet];    
        }
        return $data;
    }
}