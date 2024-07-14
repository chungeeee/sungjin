<?php

namespace App\Console\Commands\migration;

use Illuminate\Console\Command;
use DB;
use Func;
use Log;
use Arr;

class updateAttr extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'work:updateAttribute';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '채권구분 일괄업데이트';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $fp = fopen("/home/laravel/storage/logs/migration/updateAttr.log", "w");
        
        $arrayMapper = array(
            "attribute1_cat_no" => array(
                "1"=>array("tobe"=>"1001","target"=>"CG"),      // (As-Is) 화해채권(일반) [1] => (To-Be) 화해채권(일반) [1001]
                "1-1"=>array("tobe"=>"1003","target"=>"CG"),    // (As-Is) 화해폐지 [1-1] => (To-Be) 화해폐지확정(일반) [1003]
                "3-5"=>array("tobe"=>""),                       // (As-Is) 자료송부신청 [3-5] => (To-Be) 해제 
                "2"=>array("tobe"=>"1004","target"=>"CG"),      // (As-Is) 파산 [2] => (To-Be) 파산접수 [1004]
                "2-1"=>array("tobe"=>"1004","target"=>"CG"),    // (As-Is) 파산 접수 [2-1] => (To-Be) 파산접수 [1004]
                "2-2"=>array("tobe"=>"1005","target"=>"CG"),    // (As-Is) 파산 확정 [2-2] => (To-Be) 파산확정 [1005]
                "2-3"=>array("tobe"=>"1006","target"=>"CG"),    // (As-Is) 파산 기각 [2-3] => (To-Be) 파산기각 [1006]
                "2-4"=>array("tobe"=>"1007","target"=>"CG"),    // (As-Is) 파산면책 종국인용 [2-4] => (To-Be) 면책 종국인용 [1007]
                "2-5"=>array("tobe"=>"1008","target"=>"CG"),    // (As-Is) 파산 당사누락 [2-5] => (To-Be) 파산면책당사누락 [1008]
                "3"=>array("tobe"=>"1009","target"=>"CG"),      // (As-Is) 개인회생 [3] => (To-Be) 개인회생 접수 [1009]
                "3-1"=>array("tobe"=>"1010","target"=>"CG"),    // (As-Is) 개인회생 금지결정 [3-1] => (To-Be) 개인회생 금지결정 [1010]
                "3-2"=>array("tobe"=>"1011","target"=>"CG"),    // (As-Is) 개인회생 확정 [3-2] => (To-Be) 개인회생 인가 [1011]
                "3-3"=>array("tobe"=>"1013","target"=>"CG"),    // (As-Is) 개인회생 폐지 [3-3] => (To-Be) 개인회생 폐지 [1013]
                "3-4"=>array("tobe"=>"1009","target"=>"CG"),    // (As-Is) 개인회생 접수 [3-4] => (To-Be) 개인회생 접수 [1009]
                "13"=>array("tobe"=>"1014","target"=>"CG"),     // (As-Is) 신용회복 [13] => (To-Be) 신용회복 접수 [1014]
                "13-1"=>array("tobe"=>"1014","target"=>"CG"),   // (As-Is) 신용회복 접수 [13-1] => (To-Be) 신용회복 접수 [1014]
                "13-2"=>array("tobe"=>"1015","target"=>"CG"),   // (As-Is) 신용회복 확정 [13-2] => (To-Be) 신용회복 확정 [1015]
                "13-3"=>array("tobe"=>"1016","target"=>"CG"),   // (As-Is) 신용회복 실효 [13-3] => (To-Be) 신용회복 실효 [1016]
                "13-4"=>array("tobe"=>"1020","target"=>"CG"),   // (As-Is) 신용회복(시효완성) [13-4] => (To-Be) 소멸시효완성 [1020]
                "5"=>array("tobe"=>"1023","target"=>"CG"),      // (As-Is) 주민등록 말소 [5] => (To-Be) 주민등록말소 [1023]
                "4"=>array("tobe"=>""),                         // (As-Is) 결번 및 정지 [4] => (To-Be) 해제
                "6"=>array("tobe"=>""),                         // (As-Is) 행방불명 [6] => (To-Be) 해제
                "7"=>array("tobe"=>"1019","target"=>"CG"),      // (As-Is) 사기,허위대출 [7] => (To-Be) 법조치 [1019]
                "21"=>array("tobe"=>"1020","target"=>"CG"),     // (As-Is) 소멸시효완성 [21] => (To-Be) 소멸시효완성 [1020]
                "8"=>array("tobe"=>""),                         // (As-Is) 구속 [8] => (To-Be) 해제
                "9"=>array("tobe"=>"1018","target"=>"CG"),      // (As-Is) 사망 [9] => (To-Be) 사망 [1018]
                "10"=>array("tobe"=>""),                        // (As-Is) 통상채권(일반) [10] => (To-Be) 해제
                "11"=>array("tobe"=>""),                        // (As-Is) 양도처리 대상 [11] => (To-Be) 해제
                "12"=>array("tobe"=>""),                        // (As-Is) 대손처리 대상 [12] => (To-Be) 해제
                "18"=>array("tobe"=>""),                        // (As-Is) 수시교섭 [18] => (To-Be) 해제
                "14"=>array("tobe"=>""),                        // (As-Is) 상각팀이관요청 [14] => (To-Be) 해제
                "18-1"=>array("tobe"=>"1024","target"=>"CG"),   // (As-Is) 약속자 [18-1] => (To-Be) 입금약속 [1024]
                "18-2"=>array("tobe"=>""),                      // (As-Is) 위약자 [18-2] => (To-Be) 해제
                "18-3"=>array("tobe"=>""),                      // (As-Is) 수시전화자 [18-3] => (To-Be) 해제
                "19"=>array("tobe"=>""),                        // (As-Is) 신규일발 [19] => (To-Be) 해제
                "19-1"=>array("tobe"=>""),                      // (As-Is) 무입금자 [19-1] => (To-Be) 해제
                "19-2"=>array("tobe"=>""),                      // (As-Is)  3개월미만 [19-2] => (To-Be) 해제
                "20"=>array("tobe"=>""),                        // (As-Is) 법무팀 이관 [20] => (To-Be) 해제
                "20-1"=>array("tobe"=>"1019","target"=>"CG"),   // (As-Is) 법조치(지급명령) [20-1] => (To-Be) 법조치 [1019]
                "20-2"=>array("tobe"=>"1019","target"=>"CG"),   // (As-Is) 형사(고소) [20-2] => (To-Be) 법조치 [1019]
                "22"=>array("tobe"=>"1036","target"=>"CG"),     // (As-Is) 채무자대리인제도 [22] => (To-Be) 대리인선임 [1036]
                "20-3"=>array("tobe"=>"1019","target"=>"CG"),   // (As-Is) 민,형사진행 [20-3] => (To-Be) 법조치 [1019]
                "24"=>array("tobe"=>"1012","target"=>"CG"),     // (As-Is) 개인회생 종국 인용 [24] => (To-Be) 개인회생 종국인용 [1012]
                "25"=>array("tobe"=>""),                        // (As-Is) 법인 폐업 [25] => (To-Be) 해제
                "99"=>array("tobe"=>"")                         // (As-Is) 기타 [99] => (To-Be) 해제
            ),
            "attribute2_cat_no" => array(
                "1"=>array("tobe"=>""),                         // (As-Is) 본인 [1] => (To-Be) 해제
                "2"=>array("tobe"=>""),                         // (As-Is) 배우자 [2] => (To-Be) 해제
                "3"=>array("tobe"=>""),                         // (As-Is) 부친 [3] => (To-Be) 해제
                "4"=>array("tobe"=>""),                         // (As-Is) 모친 [4] => (To-Be) 해제
                "5"=>array("tobe"=>""),                         // (As-Is) 누나 [5] => (To-Be) 해제
                "6"=>array("tobe"=>""),                         // (As-Is) 언니 [6] => (To-Be) 해제
                "7"=>array("tobe"=>""),                         // (As-Is) 형 [7] => (To-Be) 해제
                "8"=>array("tobe"=>""),                         // (As-Is) 오빠 [8] => (To-Be) 해제
                "9"=>array("tobe"=>""),                         // (As-Is) 남동생 [9] => (To-Be) 해제
                "10"=>array("tobe"=>""),                        // (As-Is) 여동생 [10] => (To-Be) 해제
                "11"=>array("tobe"=>""),                        // (As-Is) 친인척 [11] => (To-Be) 해제
                "12"=>array("tobe"=>""),                        // (As-Is) 친구 [12] => (To-Be) 해제
                "13"=>array("tobe"=>""),                        // (As-Is) 보증인 [13] => (To-Be) 해제
                "14"=>array("tobe"=>""),                        // (As-Is) 어음공증 [14] => (To-Be) 해제
                "15-1"=>array("tobe"=>"1009","target"=>"G"),    // (As-Is) 개인회생접수(보증인) [15-1] => (To-Be) 개인회생 접수 [1009]
                "15"=>array("tobe"=>""),                        // (As-Is) 자료송부신청(보증인) [15] => (To-Be) 해제
                "15-2"=>array("tobe"=>"1010","target"=>"G"),    // (As-Is) 개인회생금지결정(보증인) [15-2] => (To-Be) 개인회생 금지결정 [1010]
                "15-3"=>array("tobe"=>"1011","target"=>"G"),    // (As-Is) 개인회생확정(보증인) [15-3] => (To-Be) 개인회생 인가 [1011]
                "15-4"=>array("tobe"=>"1013","target"=>"G"),    // (As-Is) 개인회생폐지(보증인) [15-4] => (To-Be) 개인회생 폐지 [1013]
                "16-1"=>array("tobe"=>"1014","target"=>"G"),    // (As-Is) 신용회복접수(보증인) [16-1] => (To-Be) 신용회복 접수 [1014]
                "16-2"=>array("tobe"=>"1015","target"=>"G"),    // (As-Is) 신용회복확정(보증인) [16-2] => (To-Be) 신용회복 확정 [1015]
                "16-3"=>array("tobe"=>"1016","target"=>"G"),    // (As-Is) 신용회복실효(보증인) [16-3] => (To-Be) 신용회복 실효 [1016]
                "16-4"=>array("tobe"=>""),                      // (As-Is) 신복위시효완성(보증인) [16-4] => (To-Be) 해제
                "17-1"=>array("tobe"=>"1004","target"=>"G"),    // (As-Is) 파산접수(보증인) [17-1] => (To-Be) 파산접수 [1004]
                "17-2"=>array("tobe"=>"1005","target"=>"G"),    // (As-Is) 파산확정(보증인) [17-2] => (To-Be) 파산확정 [1005]
                "17-3"=>array("tobe"=>"1006","target"=>"G"),    // (As-Is) 파산기각(보증인) [17-3] => (To-Be) 파산기각 [1006]
                "17-4"=>array("tobe"=>"1007","target"=>"G"),    // (As-Is) 파산면책종국인용(보증인) [17-4] => (To-Be) 면책 종국인용 [1007]
                "17-5"=>array("tobe"=>"1008","target"=>"G"),    // (As-Is) 파산당사누락 [17-5] => (To-Be) 파산면책당사누락 [1008]
                "18"=>array("tobe"=>""),                        // (As-Is) 사망 [18] => (To-Be) 해제
                "18-1"=>array("tobe"=>"1018","target"=>"G"),    // (As-Is) 보증인사망 [18-1]  => (To-Be) 사망 [1018]
                "19"=>array("tobe"=>"1022","target"=>"G"),      // (As-Is) 보증인면탈 [19] => (To-Be) 보증인 면탈 [1022]
                "20"=>array("tobe"=>"1036","target"=>"G"),      // (As-Is) 채무자대리인제도(보증인) [20] => (To-Be) 대리인선임 [1036]
                "21"=>array("tobe"=>"1012","target"=>"G"),      // (As-Is) 개인회생종국인용(보증인) [21] => (To-Be) 개인회생 종국인용 [1012]
                "22"=>array("tobe"=>""),                        // (As-Is) 법인폐업(보증인) [22] => (To-Be) 해제
                "23"=>array("tobe"=>""),                        // (As-Is) 주민등록말소(보증인) [23] => (To-Be) 해제
                "24"=>array("tobe"=>"1020","target"=>"G"),      // (As-Is) 소멸시효완성(보증인) [24] => (To-Be) 소멸시효완성
                "99"=>array("tobe"=>"")                         // (As-Is) 기타 [99] => (To-Be) 해제
            ),
        );
        $attr1 = Func::getConfigArr('loan_cat_1_cd');
        $attr2 = Func::getConfigArr('loan_cat_2_cd');

        $record = 0;
        $_LOG = array();
        $rs = DB::table('loan_info')
            ->select('no, loan_cat_1_cd, loan_cat_2_cd')
            ->where('save_status','Y')
            ->whereRaw("(loan_cat_1_cd!='' or loan_cat_2_cd!='')")
            ->orderby('no')->get();
        foreach($rs as $v)
        {
            $sql = "";
            $cType=$gType="";
            $att2_remove = false;
            unset($_LOG, $_detailInfo);
            $_LOG[] = $v->no;
            $_LOG[] = $v->loan_cat_1_cd;
            $_LOG[] = ($v->loan_cat_1_cd) ? strlen($v->loan_cat_1_cd) : "";
            $_LOG[] = $v->loan_cat_2_cd;
            $_LOG[] = ($v->loan_cat_2_cd) ? strlen($v->loan_cat_2_cd) : "";

            if($v->loan_cat_1_cd)
            {
                // 이미 Tobe 기준으로 변경된 데이터면 UPDATE 제외
                if(!isset($attr1[$v->loan_cat_1_cd]))
                {
                    if(isset($arrayMapper['attribute1_cat_no'][$v->loan_cat_1_cd]))
                    {
                        $cType = $arrayMapper['attribute1_cat_no'][$v->loan_cat_1_cd]['tobe'];

                        // 대상에 보증인도 있으면..
                        if(isset($arrayMapper['attribute1_cat_no'][$v->loan_cat_1_cd]['target']) && substr_count($arrayMapper['attribute1_cat_no'][$v->loan_cat_1_cd]['target'], "G"))
                        {
                            $gType = $arrayMapper['attribute1_cat_no'][$v->loan_cat_1_cd]['tobe'];
                        }
                    }
                }
            }

            if($v->loan_cat_2_cd)
            {
                // 이미 Tobe 기준으로 변경된 데이터면 UPDATE 제외
                if(!isset($attr2[$v->loan_cat_2_cd]))
                {
                    if(isset($arrayMapper['attribute2_cat_no'][$v->loan_cat_2_cd]))
                    {
                        $gType = $arrayMapper['attribute2_cat_no'][$v->loan_cat_2_cd]['tobe'];
                    }

                    $att2_remove = true;
                }
            }

            $_LOG[] = $cType;
            $_LOG[] = $gType;
            $guarantorCount = DB::table('loan_info_guarantor')->where('loan_info_no',$v->no)->where('save_status','Y')->COUNT();
            $_LOG[] = $guarantorCount;

            $_UP = array();
            // 채무자 기준 채권구분코드 변경대상일 경우
            if($cType || (isset($arrayMapper['attribute1_cat_no'][$v->loan_cat_1_cd]) && $cType==""))
            {
                $_UP['loan_cat_1_cd'] = $cType;
            }
            // 채권구분2 데이터 삭제대상인건
            if($att2_remove)
            {
                $_UP['loan_cat_2_cd'] = "";
            }

            // 채권의 업데이트 대상 컬럼이 있는 경우 업데이트 쿼리 실행
            if(sizeof($_UP) > 0)
            {
                $logStr = "";
                $cnt = 0;
                foreach($_UP as $key => $val)
                {
                    if($cnt>0) $logStr.= ", ".$key." = '".$val."'";
                    else $logStr.= $key." = '".$val."'";
                    $cnt++;
                }

                // 로그.. 식별용
                $sql.= "update loan_info set ".$logStr." where no = ".$v->no.";";
                
                DB::TABLE('loan_info')->where(['no'=>$v->no])->update($_UP);
            }

            // 보증인 기준 채권구분코드 변경대상인 경우, 보증인이 1건인 경우만 채권구분 업데이트 대상
            if($gType && $guarantorCount==1)
            {
                $gCnt = 0;
                $grs = DB::table('loan_info_guarantor')
                    ->select('no, status, save_status, g_loan_cat_1_cd')
                    ->where('save_status','Y')
                    ->where('loan_info_no',$v->no)
                    ->orderby('no')->get();
                foreach($grs as $gv)
                {
                    unset($_GUP);
                    $gCnt++;

                    $_LOG[] = $gv->no;
                    $_LOG[] = $gv->status;
                    $_LOG[] = $gv->save_status;
                    $_LOG[] = $gv->g_loan_cat_1_cd;
                    $_LOG[] = ($gv->g_loan_cat_1_cd) ? strlen($gv->g_loan_cat_1_cd) : "";

                    // 이미 Tobe에서 입력된 정보가 있다면 제외
                    if($gv->g_loan_cat_1_cd)
                    {
                        $_LOG[] = "미처리";
                    }
                    else
                    {
                        $_GUP['g_loan_cat_1_cd'] = $gType;
                        DB::TABLE('loan_info_guarantor')->where(['no'=>$gv->no])->update($_GUP);

                        $sql.= "update loan_info_guarantor set g_loan_cat_1_cd = '".$gType."' where no = ".$gv->no.";";
                        $_LOG[] = $gType;
                    }
                }
            }

            fwrite($fp, implode("\t", $_LOG)."\t".$sql."\n");

            echo ".";
            $record++;
            if($record%100==0) echo $record."\n";
        }
    }
}