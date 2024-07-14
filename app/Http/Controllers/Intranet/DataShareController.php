<?php
 
namespace App\Http\Controllers\Intranet;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use DB;
use Func;  
use Auth;
use Log; 
use Storage;

class DataShareController extends Controller
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
     * 데이터 공유 메인
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
	public function dataShare(Request $request)
    {        
        $branchInfo = Func::getBranchById(Auth::id());
        //$branchInfo->code = '607';// 영업기획팀
        $branch = $this->getBranchShareInfo($branchInfo->code);
        
        $arrayFilesMon = null;
        $arrayFilesDay = null;
        if($branch!=null)
        {
            foreach($branch as $key=>$v)
            {
                if($v[1]=='M')
                {
                    $arrayFilesMon[$key] = $v[0];
                }
                else if($v[1]=='D')
                {
                    $arrayFilesDay[$key] = $v[0];
                }
            }
        }
        Log::debug($arrayFilesMon);
        Log::debug($arrayFilesDay);
        return view('intranet.dataShare')
                ->with('branch', $branchInfo)
                ->with('arrayFilesMon', $arrayFilesMon)
                ->with('arrayFilesDay', $arrayFilesDay)
                ;
    }

    private function getBranchShareInfo($branchCd)
    {

        // 부서별 다운로드 파일 세팅
        // 팀코드 > [파일코드 > [파일명, 월일구분(M/D), 디렉토리, 파일포맷]]
        $arrayBrnachFiles = [
            // 기획팀
            '906'=>[
                'all_custom'            =>['총등록좌수 및 고객수', 'M', 'pl', 'xlsx'],
            ],
            // 영업관리
            '638'=>[
                'app_crm_visit_adv'     =>['광고접수현황+방문고객현황', 'M', 'sm', 'xlsx'],
                'simple_bal'            =>['유효심플잔고', 'D', 'sp/data04', 'xlsx'],
            ],
            // 홍보팀
            '904'=>[
                'app_crm_visit_adv'     =>['광고접수현황+방문고객현황', 'M', 'sm', 'xlsx'],
            ],
            // 영업기획
            '607'=>[
                'val_cust_list'         =>['월마감_유효채권리스트', 'M', 'sp/data01', 'xlsx'],
                'real_estat'            =>['부동산담보채권현황', 'M', 'sp/data02', 'xlsx'],
                'sel_asp_hunter_cred'   =>['신용정보사용건수(ASP,신용조회,헌터)', 'M', 'sp/data03', 'xlsx'],
                'simple_bal'            =>['유효심플잔고', 'D', 'sp/data04', 'xlsx'],
            ],
            // 회계
            '903'=>[
                'neg_master'            =>['화해개인회생_회수율데이터_마스터', 'M', 'acc/data01', 'xlsx'],
                'rec_int_master'        =>['월마감_미수이자현황_마스터+상세', 'M', 'acc/data04', 'xlsx'],
                'val_list'              =>['월마감_유효채권리스트', 'M', 'acc/data06', 'xlsx'],
                'int_rep_dtl'           =>['월마감_이자상환내역', 'M', 'acc/data10', 'xlsx'],
                'acc_int'               =>['일괄대손_미수이자', 'M', 'acc/data11', 'xlsx'],
            ],
            // 계약서관리실
            '665'=>[
                'cont_yn'               =>['월마감_계약서여부확인', 'M', 'cm/data01', 'xlsx'],
                'cont_col'              =>['계약서회수현황', 'M', 'cm/data02', 'xlsx'],
            ],
        ];

        // 전산팀은 모두 나오게 변경한다.
        if($branchCd=='012')
        {
            $arrayTemp = null;
            foreach($arrayBrnachFiles as $bcd=>$arrayData)
            {
                if($arrayTemp==null)
                {
                    $arrayTemp = $arrayData;
                }
                else 
                {
                    $arrayTemp = array_merge($arrayTemp, $arrayData);
                }
            }
            return $arrayTemp;
        }
        else 
        {
            if(isset($arrayBrnachFiles[$branchCd]))
            {
                return $arrayBrnachFiles[$branchCd];
            }
            else 
            {
                return null;
            }
        }
    }

    /**
     * 파일 다운로드
     *
     * @param  \Illuminate\Http\Request  $request
     * @return view
     */
    public function dataShareDownload(Request $request)
    {
        $dir = env('DATA_SHARE_DIR');

        $param    = $request->input();
        Log::debug($param);

        $branchInfo = Func::getBranchById(Auth::id());
        //$branchInfo->code = '607';// 영업기획팀
        $branch = $this->getBranchShareInfo($branchInfo->code);

        if(!isset($branch[$param['sel_file']]))
        {
            return "<script>alert('다운받을 수 있는 파일이 아닙니다'); history.back();</script>";
        }

        $f = $branch[$param['sel_file']];
        $dt = str_replace("-", "", $param['sel_date']);

        // 파일명 세팅
        $origin = $dir."/".$f[2]."/".$dt."_".$param['sel_file'].".".$f[3];        
        $target = $dt."_".$f[0].".".$f[3];
        Log::debug($origin);
        if(!file_exists($origin))
        {
            return "<script>alert('선택한 파일을 찾을 수 없습니다. 관리자에게 문의해 주세요.'); history.back();</script>";
        }       
        
        return response()->download($origin, $target);
    }
}