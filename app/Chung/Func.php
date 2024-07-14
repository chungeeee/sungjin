<?php
namespace App\Chung;

use App\Models\User;
use DB;
use DBD;
use Auth;
use Log;
use Storage;
use Sum;
use Cache;
use ZipArchive;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Events\SendMessage;
use App\Chung\ExcelCustomExport;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Excel;
use Decrypter;

class Func
{
	// 엑셀 다운로드시 권한 체크용
	public static $excelBatchId = null;

	/**
	* 내 메뉴 정보를 가져온다. - 현재는 전체 메뉴를 가져오지만, 이후 부서권한과, 개인권한을 반영하여 응답 예정
	*
	* @param  Void
	* @return Array[][]
	*/
	public static function getMyMenu()
	{
		$currURL = "/".request()->path();
		//$currURL = explode("?", $currURL)[0];
		$id = Auth::user()->id;
		$cd = Auth::user()->branch_code;
		if( !$id || !$cd )
		{
			return ['SIDE'=>[], 'HEAD'=>[], 'CURR'=>[]];
		}

		$my_mns = [];
		$hd_mns = [];
		// 직원 메뉴권한
		$mymobj = DB::TABLE('CONF_MENU_USER')->SELECT('menu_cd')->WHERE('USER_USE_YN', 'Y')->WHERE('user_id',$id)->get();
		foreach( $mymobj as $m )
		{
			$my_mns[] = $m->menu_cd;
		}
		// 부서 메뉴권한
		$mymobj = DB::TABLE('CONF_MENU_BRANCH')->SELECT('menu_cd')->WHERE('BRANCH_USE_YN', 'Y')->WHERE('branch_cd',$cd)->get();
		foreach( $mymobj as $m )
		{
			$my_mns[] = $m->menu_cd;
		}
		// HEAD MENU
		$mymobj = DB::TABLE('CONF_MENU_HEAD')->SELECT('*')->WHERE('user_id',$id)->ORDERBY("SEQ","DESC")->get();
		foreach( $mymobj as $m )
		{
			$hd_mns[$m->menu_cd] = $m->seq;
		}



		$rslt = Cache::remember('Func_getMyMenu_rslt', 3600, function()
		{
			$rslt = DB::TABLE("CONF_MENU")->SELECT("*")->WHERE('use_yn','Y')->ORWHERE('LENGTH(menu_cd)',3)->ORDERBY("SUBSTR(menu_cd,1,3)")->ORDERBY("COALESCE(menu_order,-1)")->ORDERBY("RPAD(menu_cd, 6, '0')")->GET();
			return $rslt;
		});

		$array_side_menu = [];
		$array_curr_menu = [];
		$array_head_temp = [];

		foreach( $rslt as $menu )
		{
			// 메뉴권한 없으면 넘겨
			if( (strlen($menu->menu_cd)!=3 && !in_array($menu->menu_cd, $my_mns) && $menu->menu_all_view!="Y")
				/*|| (strlen($menu->menu_cd)!=3 && !in_array($menu->menu_cd, $my_mns) && $menu->menu_all_view=="Y" && $cd == '99')*/
			)
			{
				continue;
			}

			$menu_info = [];
			$menu_info['code']  = $menu->menu_cd;
			$menu_info['name']  = $menu->menu_nm;
			$menu_info['icon']  = $menu->menu_icon;
			$menu_info['link']  = $menu->menu_uri;
			$menu_info['open']  = ( $currURL==$menu_info['link'] );

			if( strlen($menu->menu_cd)==3 )
			{
				$array_side_menu[$menu->menu_cd] = $menu_info;
				$array_side_menu[$menu->menu_cd]['sub'] = [];
			}
			else
			{
				$pcode = substr($menu->menu_cd,0,3);
				$array_side_menu[$pcode]['sub'][$menu->menu_cd] = $menu_info;

				if( $menu_info['open'] )
				{
					$array_side_menu[$pcode]['open'] = $menu_info['open'];

					$array_curr_menu['code'] = $menu_info['code'];
					$array_curr_menu['name'] = $menu_info['name'];
					$array_curr_menu['icon'] = $menu_info['icon'];
					$array_curr_menu['link'] = $menu_info['link'];
					$array_curr_menu['pmnm'] = $array_side_menu[$pcode]['name'];
				}
				if( isset($hd_mns[$menu->menu_cd]) )
				{
					$array_head_temp[$menu->menu_cd] = $menu_info;	// SEQ를 키로 쓴다.
				}
	
			}
		}

		$array_head_menu = [];
		if( sizeof($hd_mns)>0 && sizeof($array_head_temp)>0 )
		{
			foreach( $hd_mns as $k => $v )
			{
				if( isset($array_head_temp[$k]) )
				{
					$array_head_menu[$k] = $array_head_temp[$k];
				}
			}
		}

		// 권한없는 페이지 접근시 로그를 남긴다.
		if(!sizeof($array_curr_menu))
		{
			$user = Auth::user();
		
			// 로그인 시도 기록
			$DATA['id']             = $user->id;
			$DATA['branch_code']    = $user->branch_code;
			$DATA['access_agent']   = $currURL;
			$DATA['access_time']    = date("YmdHis");
			$DATA['login_success']  = 'A';
			
			// seq No 가져오기
			$maxSeq = DB::TABLE("users_login_history")->WHERE("id", $id)->max('seq');
			if(empty($maxSeq)) 
			{
				$maxSeq = 1;
			}
			else
			{
				$maxSeq+= 1;
			}

			$DATA['seq'] = $maxSeq;
			
			DB::dataProcess('INS', 'USERS_LOGIN_HISTORY', $DATA);			
		}

		log::debug($array_curr_menu);
		
		return ['SIDE'=>$array_side_menu,'HEAD'=>$array_head_menu, 'CURR'=>$array_curr_menu];
	}

	/**
	* 부서정보를 가져온다. 2차배열 형태 응답
	*
	* @param  Void
	* @return Array[][]
	*/
	public static function getBranchList()
	{
		// Cache::flush();
		$array_branch = Cache::remember('Func_getBranchList', 3600, function()
		{
			// $branches = DB::TABLE("BRANCH")->SELECT("code, branch_name, branch_depth, parent_code, close_date, center_code")->WHERE('save_status','Y')->ORDERBY("branch_order")->ORDERBY("close_date")->ORDERBY("branch_name")->GET();
			$branches = DB::table("branch")->select("code, branch_name, branch_depth, parent_code, close_date, center_code")->where('save_status','Y')/*->WHERE('code', '!=', '99')*/->whereRaw(" (close_date = ''  OR close_date is NULL )")->orderby("branch_order")->orderby("branch_div")->orderby("code")->get();
				
			foreach( $branches as $branch )
			{
				if( $branch->close_date!="" )
				{
					$branch->branch_name = "(폐쇄) ".$branch->branch_name;
				}
				$array_branch[$branch->code]['code']   = $branch->code;
				$array_branch[$branch->code]['branch_name']  = $branch->branch_name;
				$array_branch[$branch->code]['branch_depth'] = $branch->branch_depth;
				$array_branch[$branch->code]['depth']        = $branch->branch_depth;
				$array_branch[$branch->code]['parent_code']  = $branch->parent_code;
				$array_branch[$branch->code]['center_code']  = $branch->center_code;
			}
			return $array_branch;
		});
		
		return $array_branch;
	}

	/**
	* 부서정보를 가져온다. 1차배열 형태 응답
	*
	* @param  Void
	* @return Array[]
	*/
	public static function getBranch()
	{
		// Cache::flush();
		$array_branch = Cache::remember('Func_getBranch', 3600, function()
		{
			// $branches = DB::TABLE("BRANCH")->SELECT("code, branch_name, close_date")->WHERE('save_status','Y')->ORDERBY("close_date")->ORDERBY("branch_name")->GET();
			$branches = DB::TABLE("BRANCH")->SELECT("code, branch_name, close_date")->WHERE('save_status','Y')->WHERERAW("(close_date = ''  OR close_date is NULL ) ")->ORDERBY("BRANCH_AREA")->ORDERBY("BRANCH_DIV")->ORDERBY("CODE")->GET();
			foreach( $branches as $branch )
			{
				if( $branch->close_date!="" )
				{
					$branch->branch_name = "(폐쇄) ".$branch->branch_name;
				}
				$array_branch[$branch->code] = $branch->branch_name;
			}
			return $array_branch;
		});

		return $array_branch;
	}

	/**
	* 업무에 해당하는 부서정보를 가져온다. 
	*
	* @param  $branchDiv 지점업무 01:심사, 02:회수
	* @return Array[]
	*/
	public static function getBranchDiv($branchDiv='01')
	{
		// Cache::flush();
		$array_branch = Cache::remember('Func_getBranchDiv'.$branchDiv, 3600, function() use ($branchDiv)
		{		
			// $branches = DB::TABLE("BRANCH")->SELECT("code, branch_name, close_date")->WHERE('save_status','Y')->WHERE('branch_div', $branchDiv)->ORDERBY("close_date")->ORDERBY("branch_name")->GET();
			$branches = DB::table("branch")->select("code, branch_name, close_date")->where('save_status','Y')->where('branch_div', $branchDiv)->whereRaw("(close_date = ''  OR close_date is NULL ) ")->orderBy("parent_code")->orderBy("branch_order")->orderby("code")->get();
			// Log::debug(DB::getQueryLog());
			// log::debug(print_r($branches, true));
			foreach( $branches as $branch )
			{
				if( $branch->close_date!="" )
				{
					$branch->branch_name = "(폐쇄) ".$branch->branch_name;
				}
				$array_branch[$branch->code] = $branch->branch_name;
			}
			return $array_branch;
		});
					
		return $array_branch;
	}

	/**
	* 내 메뉴 정보를 상위부서코드를 반영하여 Tree형태로 가져온다.
	* 재귀호출 속도문제로 부서정보 변경 시에만 실행하여 정렬순서 및 depth 정보를 업데이트 한다.
	*
	* @param  array_branch - 응답에 추가할 배열
	* @param  pcode - 부모코드
	* @param  depth - 진행 depth
	* @return Array[][]
	*/
    public static function getBranchTree($array_branch=[], $pcode="TOP", $depth=0)
    {
        // $branches = DB::TABLE("BRANCH")->SELECT("code, branch_name, parent_code, close_date")->WHERE('save_status','Y')->WHERE('parent_code',$pcode)->WHERE('code','<>',$pcode)->ORDERBY("branch_name")->GET();
		$branches = DB::TABLE("BRANCH")->SELECT("code, branch_name, parent_code, close_date")->WHERE('save_status','Y')->WHERE('parent_code',$pcode)->WHERE('code','<>',$pcode)->WHERERAW(" (close_date = ''  OR close_date is NULL ) ")->orderBy("code")->GET();
        foreach( $branches as $branch )
        {
			if( $branch->close_date!="" )
			{
				$branch->branch_name = "(폐쇄) ".$branch->branch_name;
			}
            $array_branch[$branch->code]['code'] = $branch->code;
            $array_branch[$branch->code]['branch_name']  = $branch->branch_name;
            $array_branch[$branch->code]['parent_code']  = $branch->parent_code;
            $array_branch[$branch->code]['branch_depth'] = $depth;
            $array_branch = Func::getBranchTree($array_branch, $branch->code, $depth+1);
        }
        return $array_branch;
	}


	/**
	* SELECT 내 option 울 출력한다.
	*
	* @param  array_options - 코드값과 코드명으로 구성된 1차원 배열
	* @param  value - 선택되어진 코드값
	* @return Void
	*/
	public static function printOption($array_options, $value="", $echoFlag=true)
	{
		$echo_string = "";
		$value = (is_null($value)) ? '':$value;
		
		if( is_array($array_options) && sizeof($array_options)>0 )
		{
			foreach( $array_options as $key => $val )
			{
				$selected = ( !strcmp($value,$key) ) ? "selected='selected'" : "";
				$echo_string.= "<option value='".$key."' ".$selected." class='text-black'>".$val."</option>";
			}
		}
		if( $echoFlag )
		{
			echo $echo_string;
		}
		else
		{
			return $echo_string;
		}
	}

	/**
	* SELECT 내 option 울 출력한다. (2차원배열 용)
	*
	* @param  array_options - 코드값과 코드명으로 구성된 2차원 배열
	* @param  name_key - 출력할 코드명의 2차 key 값
	* @param  value - 선택되어진 코드값
	* @return Void
	*/
	public static function printOptionArray($array_options, $name_key, $value="")
	{
		$return_string = "";
		if( is_array($array_options) && sizeof($array_options)>0 )
		{
			foreach( $array_options as $key => $val )
			{
				if( is_object($val) )
				{
					$val = (Array) $val;
				}
				$selected = ( !strcmp($value,$key) ) ? "selected='selected'" : "";

				$padding  = "";
				if( isset($val['depth']) )
				{
					for( $i=1; $i<=$val['depth']; $i++ )
					{
						$padding.= "　";
					}
				}
				echo "<option value='".$key."' ".$selected.">".$padding." ".$val[$name_key]."</option>";
			}
		}
	}

	/**
	* SELECT 내 option 울 출력한다.multi
	*
	* @param  array_options - 코드값과 코드명으로 구성된 1차원 배열
	* @param  value - 선택되어진 json 형태의 코드값 ["01", "02"]
	* @return Void
	*/
	public static function printOptionMulti($array_options, $value="", $echoFlag=true)
	{
		$echo_string = "";

		$values = json_decode($value);
		
		if( is_array($array_options) && sizeof($array_options)>0 )
		{
			foreach( $array_options as $key => $val )
			{
				$selected = ( isset($values) && in_array((String)$key, $values, true) ) ? "selected='selected'" : "";
				$echo_string.= "<option value='".$key."' ".$selected.">".$val."</option>";
			}
		}
		if( $echoFlag )
		{
			echo $echo_string;
		}
		else
		{
			return $echo_string;
		}
	}

	/**
	* SELECT 내 option 울 출력한다.multi
	*
	* @param  array_options - 코드값과 코드명으로 구성된 1차원 배열
	* @param  value - 선택되어진 json 형태의 코드값 ["01", "02"]
	* @return Void
	*/
	public static function printOptionMulti2($array_options, $value="", $echoFlag=true)
	{
		$echo_string = "";

		$values = json_decode($value);
		
		if( is_array($array_options) && sizeof($array_options)>0 )
		{
			foreach( $array_options as $key => $val )
			{
				$selected = ( isset($values) && in_array((String)$key, $values, true) ) ? "selected='selected'" : "";
				$echo_string.= "<option value='".$key."' ".$selected.">".$val."</option>";
			}
		}
		if( $echoFlag )
		{
			echo $echo_string;
		}
		else
		{
			return $echo_string;
		}
	}

	/**
	* 코드관리의 코드배열을 반환한다.
	*
	* @param  code - 코드 테이블에서 검색할 code 값
	* @return Arr - 결과 코드 배열
	*/
	public static function getConfigArr($code = null)
	{
		Cache::flush();
		$configArr = Cache::remember('Func_ConfCode'.$code, 300, function() use ($code)
		{
			$builder = DB::TABLE("CONF_CODE")->SELECT("NAME, CODE, CAT_CODE")->WHERE('save_status', 'Y');
			if($code)
			{
				$builder->WHERE('CAT_CODE', $code);
			}
			$configs = $builder->ORDERBY('CAT_CODE')->ORDERBY('CODE_ORDER')->get();
			$configArr = [];
			foreach($configs as $config)
			{
				if($code)
				{
					if($config->cat_code=='sms_ups_cd' || $config->cat_code=='sms_erp_cd' || $config->cat_code=='sms_sys_cd')
						$configArr[$config->code] = $config->code.'.'.$config->name;
					else
						$configArr[$config->code] = $config->name;
				}
				else
				{
					if($config->cat_code=='sms_ups_cd' || $config->cat_code=='sms_erp_cd' || $config->cat_code=='sms_sys_cd')
						$configArr[$config->cat_code][$config->code] = $config->code.'.'.$config->name;
					else
						$configArr[$config->cat_code][$config->code] = $config->name;
				}
			}
			return $configArr;
		});

		return $configArr;
	}

	/**
	* 코드관리의 코드배열을 반환한다.
	* Name만 반환하지 않고 모든 컬럼을 반환한다.
	*
	* @param  code - 코드 테이블에서 검색할 code 값
	* @return Arr - 결과 코드 배열
	*/
	public static function getConfigArrAll($code)
	{
		$configArr = Cache::remember('Func_getConfigArrAll'.$code, 300, function() use ($code)
		{
			$builder = DB::TABLE("CONF_CODE")->SELECT("*")->WHERE('save_status', 'Y')->WHERE('cat_code', $code);
			$configs = $builder->ORDERBY('cat_code')->ORDERBY('code_order')->get();

			foreach($configs as $config)
			{
				$configArr[$config->code] = get_object_vars($config);
			}

			return $configArr;
		});
		return $configArr;
	}

	
	/**
	* 코드관리의 코드배열을 반환한다.
	* Name만 반환하지 않고 모든 컬럼을 반환한다.
	*
	* @param  code - 코드 테이블에서 검색할 code 값
	* @return Arr - 결과 코드 배열
	*/
	public static function getConfigCatNameArr($array_code)
	{
		// $configArr = Cache::remember('Func_getConfigCatNameArr', 300, function() use ($array_code)
		// {
			$builder = DB::TABLE("CONF_CATE")->SELECT("CAT_CODE,CAT_NAME")->WHERE('save_status', 'Y')->WHEREIN('cat_code', $array_code);
			$configs = $builder->ORDERBY('cat_code')->get();

			foreach($configs as $config)
			{
				$configArr[$config->cat_code] = $config->cat_name;
			}

			return $configArr;
		// });
		// return $configArr;
	}

	/**
	* 코드관리의 코드배열을 반환한다.
	* 조건을 array로 받는다
	*
	* @param  code - 코드 테이블에서 검색할 code 값
	* @return Arr - 결과 코드 배열
	*/
	public static function getConfigArrList($array_code)
	{
			$builder = DB::table("conf_code")->select("code, cat_code, name")->where('save_status', 'Y')->wherein('cat_code', $array_code);
			$configs = $builder->orderby('cat_code')->orderby('code')->get();
			
			foreach($configs as $config)
			{
				$configArr[$config->cat_code][$config->code] = $config->name;
			}

			return $configArr;
	}

	/**
	* 날짜포멧을 맞춰준다.
	*
	* @param  $ymdhis - 8자리->날자, 6자리->시간, 10자리->, 14자리 날짜시간
	* @return $string
	*/
	public static function dateFormat($ymdhis, $divider="")
	{
		$ymdhis = (is_null($ymdhis)) ? '':$ymdhis;
		$ymdhis = str_replace("-", "", $ymdhis);
		if( strlen($ymdhis)==6 )
		{
			if( !$divider )
			{
				$divider = ":";
			}
			$ymdhis = substr($ymdhis,0,2).$divider.substr($ymdhis,2,2).$divider.substr($ymdhis,4,2);
		}
		else if( strlen($ymdhis)==8 )
		{
			if( $divider == "kor" )
			{
				$ymdhis = substr($ymdhis,0,4)."년 ".substr($ymdhis,4,2)."월 ".substr($ymdhis,6,2)."일";
			}
			else
			{
				if( !$divider )
				{
					$divider = "-";
				}
				$ymdhis = substr($ymdhis,0,4).$divider.substr($ymdhis,4,2).$divider.substr($ymdhis,6,2);
			}
		}
		else if( strlen($ymdhis)==10 )
		{
			if($divider == '/')
			{
				$ymdhis = substr($ymdhis,0,4)."/".substr($ymdhis,5,2)."/".substr($ymdhis,-2);
			}
			else
			{
				$ymdhis = date("Y-m-d H:i:s", $ymdhis);
			}
		}
		else if( strlen($ymdhis)==14 )
		{
			$ymdhis = substr($ymdhis,0,4)."-".substr($ymdhis,4,2)."-".substr($ymdhis,6,2)." ".$divider.substr($ymdhis,8,2).":".substr($ymdhis,10,2).":".substr($ymdhis,12,2);
		}

		return $ymdhis;
	}
	public static function dateFormat2($ymdhis)
	{
		$ymdhis = str_replace("-", "", $ymdhis);
		if( strlen($ymdhis)==14 )
		{
			$ymdhis = substr($ymdhis,0,4)."-".substr($ymdhis,4,2)."-".substr($ymdhis,6,2);
		}
		return $ymdhis;
	}


	/**
	 * 은행별 계좌번호 표시(하이픈) 방법
	 *
	 * @param  $bank_cd - 3자리 은행코드
	 * @param  $ssn     - 계좌번호
	 * @return $string
	 */
	public static function bankSsnFormat($bank_cd, $ssn)
	{
		$tmp = Array();
		
		$bank_cd = str_replace(" ", "", $bank_cd);
		$ssn     = str_replace(" ", "", $ssn);
		$ssn_len = strlen($ssn);
		$key     = $bank_cd."_".$ssn_len;

		// 은행코드_계좌자리수 = 은행별 계좌번호 표시방법 설정
		$array_bank_ssn_format = Array
		(
			// 산업
			"002_11" => [3,2,5,1],
			"002_14" => [3,3,8],
			// 기업
			"003_10" => [8,2],
			"003_11" => [3,8],
			"003_12" => [3,2,6,1],
			"003_14" => [3,6,2,2,1],
			// 국민
			"004_11" => [11],
			"004_12" => [3,2,4,3],
			"004_14" => [4,2,7,1],
			// 하나
			"004_11" => [3,2,5,1],
			"004_12" => [3,6,3],
			// 수협중앙회
			"007_11" => [3,2,5,1],
			"007_12" => [3,8,1],
			"007_14" => [3,2,8,1],
			// 농협
			"011_11" => [3,2,5,1],
			"011_12" => [4,2,5,1],
			"011_13" => [3,4,4,1,1],
			"011_14" => [3,4,4,2,1],
			// 농협회원조합
			"012_13" => [3,4,4,1,1],
			"012_14" => [6,2,5,1],
			// 우리
			"020_13" => [4,3,6],
			"020_14" => [3,6,5],		// 3,6,2,2,1
			// 저축은행
			"050_14" => [3,2,2,6,1],
			// 하나
			"081_14" => [3,9,2],
			// 신한
			"088_12" => [3,8,1],
			"088_14" => [3,3,7,1],
		);
		// 지정되지 않았으면 기본값
		$default_format = [3,3,99];

		$size = ( isset($array_bank_ssn_format[$key]) ) ? $array_bank_ssn_format[$key] : $default_format;
		foreach( $size as $len )
		{
			$str = substr($ssn, 0, $len);
			if( $str=="" )
			{
				break;
			}
			$tmp[] = $str;
			$ssn = substr($ssn, $len);
		}
		// 자리수를 모자라게 설정하게되면, 남는걸 뒤로 몽땅 붙여준다.
		if( $ssn!="" )
		{
			$tmp[] = $ssn;
		}

		return implode("-", $tmp);
	}

	/**
	 * SMS/우편물발송시 신한은행 포맷
	 *
	 * @param  $bank_cd - 3자리 은행코드
	 * @param  $ssn     - 계좌번호
	 * @return $string
	 */
	public static function bankSsnSmsFormat($bank_cd, $ssn)
	{
		$ssn     = str_replace(" ", "", $ssn);

		if($bank_cd=='088')
			$size = [3,5,6];
		else 
			$size = [3,5,99];

		foreach( $size as $len )
		{
			$str = substr($ssn, 0, $len);
			if( $str=="" )
			{
				break;
			}
			$tmp[] = $str;
			$ssn = substr($ssn, $len);
		}
		// 자리수를 모자라게 설정하게되면, 남는걸 뒤로 몽땅 붙여준다.
		if( $ssn!="" )
		{
			$tmp[] = $ssn;
		}

		return implode("-", $tmp);
	}

	/**
	 * 권한 체크 함수
	 *
	 * @param String $permit 체크할 권한
	 * @return Boolean
	 */
	public static function checkMenuPermit($permit)
	{
		if(Auth::check())
		{
			$rslt = DB::TABLE("CONF_MENU_BRANCH")->WHERE('branch_use_yn','Y')->WHERE('branch_cd', Auth::user()->branch_code)->where('menu_cd', $permit)->exists();
			if( $rslt )
			{
				// 권한 존재
				return true;
			}
			$rslt = DB::TABLE("CONF_MENU_USER")->WHERE('user_use_yn','Y')->WHERE('user_id', Auth::id())->WHERE('menu_cd', $permit)->exists();
			if( $rslt )
			{
				// 권한 존재
				return true;
			}

			// 권한 없음
			return false;
		}
		else
		{
			// 로그인 안한 경우
			return false;
		}
	}



	/**
	 * 기능권한 체크 함수
	 *
	 * @param String $permit 체크할 권한
	 * @return Boolean
	 */
	public static function funcCheckPermit($permit,$div='')
	{
		
		if( Auth::check() || Func::$excelBatchId!=null )
		{
			$confirmPermit = '';
			$basePermit = '';	
				
			if(Func::$excelBatchId!=null)
			{
				$users = DB::table('users')->select('confirm_permit', 'permit')
						->where('id', Func::$excelBatchId)
						->WHERE('save_status','Y')
						->first();

				if(isset($users->permit))
				{
					$confirmPermit = $users->confirm_permit;
					$basePermit = $users->permit;	
				}
			}
			else 
			{
				$confirmPermit = Auth::user()->confirm_permit;
				$basePermit = Auth::user()->permit;
			}

			//$rslt = DB::TABLE("USERS")->SELECT('PERMIT')->WHERE('SAVE_STATUS','Y')->WHERE('ID', Auth::id())->FIRST();
			//$permit_cnt = substr_count($rslt->permit, $permit);
			if($div == "A") // 승인권한용!!!
			{
				$permit_cnt = substr_count($confirmPermit, $permit);
			}
			else
			{
				$permit_cnt = substr_count($basePermit, $permit);
			}
			if( $permit_cnt >= 1 )
			{
				// 권한 존재
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			// 로그인 안한 경우
			return false;
		}
	}

	/**
	 * 기능권한 체크 함수
	 *
	 * @param String $permit 체크할 권한
	 * @return Boolean
	 */
	public static function funcCheckPermit2($permit,$div='')
	{
		if( Auth::check() )
		{
			$rslt = DB::TABLE("USERS")->SELECT('PERMIT')->WHERE('SAVE_STATUS','Y')->WHERE('ID', Auth::id())->FIRST();
			$permit_cnt = substr_count($rslt->permit, $permit);
			if($div == "A") // 승인권한용!!!
			{
				$permit_cnt = substr_count(Auth::user()->confirm_permit, $permit);
			}
			else
			{
				$permit_cnt = substr_count(Auth::user()->permit, $permit);
			}
			if( $permit_cnt >= 1 )
			{
				// 권한 존재
				return true;
			}
			else
			{
				return false;
			}
		}
		else
		{
			// 로그인 안한 경우
			return false;
		}
	}

	/*
	* 부서별 직원정보를 가져온다. 2차배열 형태 응답
	*
	* @param String $uid user id 없으면 전체리스트
	* @return Array[][]
	*/
	public static function getBranchUserList($branch_div="")
	{
		$array_user = Cache::remember('Func_getBranchUserList'.$branch_div, 3600, function()
		{
			$users = DB::TABLE("USERS")->SELECT("ID, BRANCH_CODE, NAME")->WHERE('save_status','Y')->WHERERAW("( TOESA = '' OR TOESA is NULL )")->ORDERBY("BRANCH_CODE")->ORDERBY("NAME")->GET();
			$users = Func::chungDec(["USERS"], $users);	// CHUNG DATABASE DECRYPT

			foreach( $users as $u )
			{
				$array_user[$u->branch_code][$u->id]   = $u;
			}
			return $array_user;
		});

		return $array_user;
	}

	/*
	* 부서별 직원정보를 가져온다. 2차배열 형태 응답
	*
	* @param String $uid user id 없으면 전체리스트
	* @return Array[][]
	*/
	public static function getBranchUsers($branch_code)
	{
		$array_user = Cache::remember('Func_getBranchUsers'.$branch_code, 3600, function() use ($branch_code) 
		{
			// $users = DB::TABLE("USERS")->SELECT("ID, BRANCH_CODE, NAME")->WHERE('save_status','Y')->WHERE('BRANCH_CODE', $branch_code)->ORDERBY("NAME")->GET();
			$users = DB::table("users")->select("id, branch_code, name")->WHERE('save_status','Y')->WHERE('branch_code', $branch_code)->whereraw("( toesa = '' or toesa is NULL )")->orderby("name")->GET();
			$users = Func::chungDec(["users"], $users);	// CHUNG DATABASE DECRYPT
			$array_user = null;
			foreach( $users as $u )
			{
				$array_user[$u->id]   = $u->name;
			}
			return $array_user;
		});

		return $array_user;
	}

	/*
	* 직원정보를 가져온다. 2차배열 형태 응답
	*
	* @param String $uid user id 없으면 전체리스트
	* @return Array[][]
	*/
	public static function getUserList($uid='')
	{
		$array_user = Cache::remember('Func_getUserList'.$uid, 3600, function() use ($uid) 
		{
			if( isset($uid) && $uid!='' )
			{
				$users = DB::TABLE("USERS")->SELECT("*")->WHERE('id',$uid)->FIRST();	
				$users = Func::chungDec(["USERS"], $users);	// CHUNG DATABASE DECRYPT
				return $users;
			}
			else
			{
				// $users = DB::TABLE("USERS")->SELECT("ID","NAME","BRANCH_CODE")->WHERE('save_status','Y')->GET();
				$users = DB::TABLE("USERS")->SELECT("ID","NAME","BRANCH_CODE")->WHERE('save_status','Y')->WHERERAW("( TOESA = '' OR TOESA is NULL )")->GET();
				$users = Func::chungDec(["USERS"], $users);	// CHUNG DATABASE DECRYPT

				foreach( $users as $u )
				{
					$array_user[$u->id]   = $u;
				}
				return $array_user;	
			}
		});

		return $array_user;	
	}

	/*
	* 서류관리 직원정보
	*
	* @param String $uid user id 없으면 전체리스트
	* @return Array['id']
	*/
	public static function getDocManagerList()
	{
		
		$arrayUser = Cache::remember('getDocManagerList', 3600, function() 
		{
			$arrayUser = [];
			// $users = DB::TABLE("USERS")->SELECT("id, name")->WHERE("save_status",'Y')->WHERE('permit', 'like', '%E023%')->ORDERBY("NAME")->get();
			$users = DB::TABLE("USERS")->SELECT("id, name")->WHERE("save_status",'Y')->WHERE('permit', 'like', '%E023%')->WHERERAW("( TOESA = '' OR TOESA is NULL )")->ORDERBY("NAME")->get();
			$users = Func::chungDec(["USERS"], $users);	// CHUNG DATABASE DECRYPT

			foreach($users as $u)
			{
				$arrayUser[$u->id] = $u->name;
			}
			return $arrayUser;
		});
		return $arrayUser;
	}



	/*
	* 직원정보를 가져온다. 1차배열 형태 응답
	*
	* @param String $uid user id 없으면 전체리스트
	* @return Array['id']
	*/
	public static function getUserId($uid='')
	{
		$array_user = Cache::remember('Func_getUserId'.$uid, 3600, function() use ($uid) 
		{
			if( isset($uid) && $uid!='' )
			{
				$users = DB::TABLE("USERS")->SELECT("ID, NAME")->WHERE('id',$uid)->FIRST();	
				$users = Func::chungDec(["USERS"], $users);	// CHUNG DATABASE DECRYPT
				return $users;
			}
			else
			{
				// $users = DB::TABLE("USERS")->SELECT("ID, NAME")->WHERE('save_status','Y')->GET();
				$users = DB::TABLE("USERS")->SELECT("ID, NAME")->WHERE('save_status','Y')->WHERERAW("( TOESA = '' OR TOESA is NULL )")->GET();
				$users = Func::chungDec(["USERS"], $users);	// CHUNG DATABASE DECRYPT

				foreach( $users as $u )
				{
					$array_user[$u->id]   = $u->name;
				}
				return $array_user;
			}
		});

		return $array_user;				
	}

	public static function getCounselUserList($branchCd)
	{
		
		$arrayUser = Cache::remember('getCounselUserList'.$branchCd, 3600, function() use ($branchCd) 
		{
			// $users = DB::TABLE("USERS")->SELECT("id, name")->WHERE('branch_code', $branchCd)->WHERE('SAVE_STATUS','Y')->get();
			$users = DB::TABLE("USERS")->SELECT("id, name")->WHERE('branch_code', $branchCd)->WHERE('SAVE_STATUS','Y')->WHERERAW("( TOESA = '' OR TOESA is NULL )")->get();
			$users = Func::chungDec(["USERS"], $users);	// CHUNG DATABASE DECRYPT

			foreach($users as $u)
			{
				$arrayUser[$u->name] = $u->name;
			}
			return $arrayUser;
		});
		return $arrayUser;
	}


	/*
	* 심사관련된 직원정보를 가져온다
	* branchCd 있으면 해당 부서의 심사자만 가져옴. 
	* 지점장, 섹터장, 본부장은 모두 불러온다.
	*
	* @param String $branchCd 
	* @return 	Array['branch'] : branch와 심사자 
	* 			Array['manager'] : 심사자
	* 			Array['managerA'] : 지점장
	* 			Array['managerB'] : 섹터장
	* 			Array['managerC'] : 본부장
	*/
	public static function getUserLoan($branchCd='')
	{
		$arrayUser = Cache::remember('Func_getUserLoan'.$branchCd, 3600, function() use ($branchCd) 
		{
			// $users = DB::TABLE("USERS")->SELECT("id, name, permit, branch_code")->WHERE('save_status','Y')->orderBy('name')->get();
			$users = DB::TABLE("USERS")->SELECT("id, name, permit, branch_code")->WHERE('save_status','Y')->WHERERAW("( TOESA = '' OR TOESA is NULL )")->orderBy('name')->get();
			$users = Func::chungDec(["USERS"], $users);	// CHUNG DATABASE DECRYPT
			
			$arrayUser = null;
			$arrayUser['branch'] = null;
			$arrayUser['manager'] = null;
			$arrayUser['managerA'] = null;
			$arrayUser['myManager'] = '';

			foreach( $users as $u )
			{
				// 심사자
				if(strstr($u->permit, 'U011') && ($branchCd=='' || $branchCd==$u->branch_code))
				{
					$arrayUser['manager'][$u->id] = $u->name;
					$arrayUser['branch'][$u->branch_code][$u->id] = $u;
				}

				// 내 부서의 지점장
				if(strstr($u->permit, 'U012') && $branchCd==$u->branch_code)
				{
					$arrayUser['myManager'] = $u->id;
				}

				if(strstr($u->permit, 'U012') && ($branchCd=='' || $branchCd==$u->branch_code))
				{
					$arrayUser['managerA'][$u->id]   = $u->name;
				}
			}

			return $arrayUser;
		});

		return $arrayUser;	
	}
	
	/**
	* SELECT BOX, CHECK BOX 선택하기
	*
	* @param  target - 기준 값
	* @param  value - 비교할 값
	* @param  str - 출력해줄 메세지 (checked, selected)
	* @return 
	*/
	public static function echoChecked($target, $value, $str="checked")
	{
		if(is_array($target))
		{
			if(in_array($value, $target))
			{
				echo $str;
			}
		}
		else
		{
			if($target==$value)
			{
				echo $str;
			}
		}
	}

	/**
	*  이미지 파일 여부
	*
	* @param  extension - 비교할 값
	* @return 
	*/
	public static function checkImg($extension)
	{
		$img_ok = array("gif", "png", "jpg", "jpeg", "bmp");
		if(in_array(strtolower($extension), $img_ok)) return true;
		else return false;
	}

	
	/**
	 * 프로필 이미지 base64_encoding 후 반환하는 함수
	 *
	 * @param String $id // 이미지를 표시할 함수
	 * @return void
	 */
	public static function echoProfileImg($id)
	{
		$user = User::where('id', $id)->first();
		$user = Func::chungDec(["USERS"], $user);	// CHUNG DATABASE DECRYPT

		if(isset($user))
		{
			if(isset($user->profile_img_src) && Storage::disk('public')->exists($user->profile_img_src))
			{
				$profile_img = 'data:image;base64,'.base64_encode(Storage::disk('public')->get(Auth::user()->profile_img_src));
			}
			else
			{
				$profile_img = '/img/blank_profile.png';
			}

			return $profile_img;
		}
		else
		{
			// 유저를 찾을 수 없는 경우 x박스 표시 없애기 위한 대체 이미지
			return '/img/blank_profile.png';
		}
	}
	
	/**
	 * 리스트에서 탭마다 다른 컬럼을 보여주고 싶을 때
	 * 필요한 markup과 상단 header html 을 만들어주는 함수
	 *
	 * @param Array $listTitle 메뉴설정이 담긴 배열
	 * @param string $checkBox 체크박스 사용 유무 (Y)
	 * @param String $checkboxNm 체크박스 name에 추가할 Name
	 * @return Array header와 markup html이 담긴 배열
	 */
	public static function changeListCols($listTitle, $checkBox = 'N', $checkboxNm = null)
	{
		$returnStr = '';
		$markup = "";
		$markup = '<tr id="no_${no}" style="${line_style}" onclick="${onclick}">';

		// 체크박스가 필요한 경우
		if($checkBox == 'Y')
		{
			$returnStr .= '<th class="text-center" style="width:20px">';
			$returnStr .= '    <input type="checkbox" name="check-all" id="check-all" class="check-all">';
			$returnStr .= '</th>';

			$markup .= '<td class="text-center"><input type="checkbox" name="listChk[]" id="listChk${'.$checkboxNm.'}" class="list-check" value="${'.$checkboxNm.'}"></td>';
		}

		if(is_array($listTitle))
		{
			foreach ($listTitle as $key => $value) 
			{
				$markup .= '<td class="text-'.$value[3];
				$returnStr .= '<th class="text-center ';
				
				if(isset($value[4]) && $value[4] != '')
				{
					$returnStr .= 'rightline';
					$markup .= ' rightline';
				}
				$markup .= '">';

				if (isset($value[6]))
				{
					$markup .= '{{html '.$key.'}}';
					foreach ($value[6] as $k => $v) {
						$markup .= $v[2].'{{html '.$k.'}}';
					}
					$markup .= '</td>';
				} else {
					$markup .= '{{html '.$key.'}}</td>';
				}
				
				
				// style
				$returnStr .= '" style="';
				if(isset($value[2]))
				{
					$returnStr .= 'width: '.$value[2].';';
					if(isset($value[5]))
					{
						if(!isset($value[6]))
						{
							$returnStr .= 'cursor:pointer;';
						}
					}
				}
				$returnStr .= '" ';

				if(isset($value[6]))
				{
					$returnStr .= '><span ';
					
					if(isset($value[5]))
					{
						$returnStr .= 'style="cursor: pointer;" onclick="nameorder(\''.$value[5].'\', this);"';
					}
					
					$returnStr .= '>'.$value[0].' <i class="orderIcon"></i></span>';

					
					foreach ($value[6] as $k => $v) {
						$returnStr .= $v[2].'<span ';

						if(isset($v[1]))
						{
							$returnStr .= 'style="cursor: pointer;" onclick="nameorder(\''.$v[1].'\', this);"';
						}
						
						$returnStr .= '>'.$v[0].' <i class="orderIcon"></i></span>';
					}

					$returnStr .= '</th>';
				} 
				else
				{
					if(isset($value[5]))
					{
						$returnStr .= ' onclick="nameorder(\''.$value[5].'\', this);"';
					}
					
					$returnStr .= '>'.$value[0].' <i class="orderIcon"></i></th>';
				}

			}

		}

		$markup .= "</tr>";

		return Array('header' => $returnStr, 'markup' => $markup);
	}

	/**
	* 주민번호에 하이픈 추가출력.
	*
	* @param  String $ssn - 주민번호
	* @param  String $masking - '':111111-21111111, Y:111111-2******, N:111111-2
	* @return $string
	*/
	public static function ssnFormat($ssn, $masking='')
	{
		if(strlen($ssn)==13)
        {
			$ssn1 = substr($ssn,0,6);

			if($masking=='Y')
			{
				$ssn = $ssn1.'-'.substr($ssn,6,1).'******';
			}
			else if($masking=='N')
			{
				$ssn = $ssn1.'-'.substr($ssn,6,1);
			}
			else if($masking=='A')
			{
				$ssn = $ssn1.'-'.substr($ssn,6,7);
			}
			else if($masking=='SEX')
			{
				$s = substr($ssn,6,1);
				if($s=='1' || $s=='3' || $s=='5' || $s=='7')
					$ssn = '남';
				else if($s=='2' || $s=='4' || $s=='6' || $s=='8')
					$ssn = '여';
			}
			else
			{
				$ssn = $ssn1.'-'.substr($ssn,6);
			}
		}
	
		return $ssn;
	}

	/**
	* 핸드폰번호에 마스킹 추가출력.
	*
	* @param  String $ph - 휴대폰번호
	* @param  String $masking - '':010-1111-2222, Y:010-****-2222
	* @return $string
	*/
	public static function phMasking($ph1, $ph2, $ph3, $masking='')
	{
		if($masking=='Y')
		{
        	$ph = trim($ph1).'-****-'.trim($ph3);
		}
		else
		{
			$ph = trim($ph1).'-'.trim($ph2).'-'.trim($ph3);
		}
	
		return $ph;
	}

		/**
	* 이름에 마스킹 추가출력.
	*
	* @param  String $name - 휴대폰번호
	* @param  String $masking - '':홍길동, Y: 홍*동, 웨**지
	* @return $string
	*/
	public static function nameMasking($name, $masking='')
	{
		if($masking=='Y')
		{	
			$name_len = mb_strlen($name, 'utf-8');

			// 2글자
			if($name_len == '2')
			{
				$name = mb_substr($name, 0, 1, 'utf-8').'*';
			}
			// 3글자
			elseif($name_len == '3')
			{
				$name = mb_substr($name, 0, 1, 'utf-8').'*'.mb_substr($name, 2, 1, 'utf-8');
			}
			// 이상
			else
			{
				if(mb_substr($name, -4, 4, 'utf-8') == '주식회사')
				{
					$name = mb_substr($name, 0, -6, 'utf-8').'*';
				}
				elseif(mb_substr($name, -3, 3, 'utf-8') == '(주)')
				{
					$name = mb_substr($name, 0, -5, 'utf-8').'*';
				}
				else
				{
					$name = mb_substr($name, 0, -2, 'utf-8').'*';
				}
			}
		}

		return $name;
	}

	/**
	* 주민번호로 출생 년도 구하기
	*
	* @param  String $ssn - 주민번호 (숫자만)
	* @return $string
	*/
	public static function ssntoyears($ssn)
	{
		if( empty($ssn) || !is_numeric(substr($ssn, 0, 7)) || strlen($ssn) < 7 )
		{
			return false;
		}
		
		switch(substr($ssn, 6, 1))
		{
			case "1" :
			case "2" :
			case "5" :
			case "6" :				
				return intval(substr($ssn,0,2))+1900;

			case "3" :
			case "4" :
			case "7" :
			case "8" :
				return intval(substr($ssn,0,2))+2000;
		}

		return false;
	}

	/**
	* 만나이 구하기
	*
	* @param  String $ssn - 주민번호 (숫자만, 성별까지 최소 7자리)
	* @param  String $today - 기준일
	* @return int
	*/
	public static function getAge($ssn, $today='')
	{
		if($today=='')
			$today = date("Ymd");

		$y = Func::ssntoyears($ssn);

		if(!$y)
			return false;
			
		$d = substr($ssn, 2, 4);

		$todayY = substr($today, 0, 4);
		$todayD = substr($today, 4, 4);

		$age = $todayY - $y;

		if($todayD<$d)
			$age -= 1;

		return $age;
	}
	
	public static function getGender($ssn){
		$gen_div = substr($ssn, 6, 1);

		if($gen_div == '1' || $gen_div == '3' || $gen_div == '5' || $gen_div == '7')
		{
			$gender = '남';
		}
		else if($gen_div == '2' || $gen_div == '4' || $gen_div == '6' || $gen_div == '8')
		{
			$gender = '여';
		}
		else
		{
			$gender = '';
		}

		return $gender;
	}


	/**
	*  테이블 INSERT 후 시퀀스 CURRVAL 가져오기
	*
	* @param  seq_nm - 시퀀스 명
	* @param  sechma - 스키마 구분값
	* @return 
	*/
	public static function getSeqPrevval($seq_nm)
	{
		$RS = DB::table("sysibm.sysdummy1")->select(DB::raw('prevval FOR '.config('app.sche').'.'.$seq_nm.' as seq'))->first();

		$RS_VAL = $RS->seq;
			
		return $RS_VAL;
	}

	/**
	*  SMS 문장 가져오기
	*
	* @param  Void
	* @return Array[]
	*/
	public static function getSmsMessage()
	{
		$RS = DB::table("sms_msg")->SELECT("*")->WHERE('SAVE_STATUS', 'Y')->ORDERBY("NO")->GET();
		$RS = Func::chungDec(["sms_msg"], $RS);	// CHUNG DATABASE DECRYPT

		$array_msg = null;
		foreach( $RS as $sms )
        {
            $array_msg[$sms->sms_div][$sms->message] = $sms->message;
		}

		return $array_msg;
	}

	/**
	* 일자사이 일수 구하기
	*
	* @param  Date - 시작일
	* @param  Date - 종료일
	* @return Int  - 일수
	*/
	public static function dateTerm($s, $e)
	{
		$st = Func::dateToUnixtime($s);
		$et = Func::dateToUnixtime($e);
		return intval( ($et-$st)/(86400) );
	}
	/**
	* 특정일자의 Unixtime 구하기
	*
	* @param  Date - 일자
	* @return Int  - Unixtime
	*/
	public static function dateToUnixtime($ymd)
	{
		$ymd = str_replace("-","",$ymd);
		$y = substr($ymd,0,4);
		$m = substr($ymd,4,2);
		$d = substr($ymd,6,2);
		return mktime(0, 0, 0, $m, $d, $y);
	}
	
	/**
	 * 배열의 키값에 해당하는 값을 가져온다. 배열이 없거나 해당 키가 없으면 에러가 발생하는 것 방지. 
	 * 배열에 없을 경우는 key를 리턴
	 *
	 * @param  string $array_data = 기준배열
	 * @param  string $key = 키
	 * @return string
	 */
	public static function getArrayName($array_data, $key)
	{

		if(isset($array_data[$key]))
		{
			return $array_data[$key];
		}
		else
		{
			return $key;
		}
	}

	/**
	 * 특수문자 제거 함수
	 * @param string $str
	 * @param Array $arrReplace
	 * @return string
	 */
	public static function delChar($str,$arrReplace)
	{
		if(isset($str) && isset($arrReplace))
		{
			$str = str_replace($arrReplace, "", $str);
		}
		return $str;
	}

	/**
	 * 배열 value 특수문자 제거 함수
	 * @param Array $str
	 * @param Array $arrReplace
	 * @return Array
	 */
	public static function arrayDelChar($arr,$arrReplace)
	{
		if(isset($arr) && isset($arrReplace))
		{
			foreach($arr as $key => $value)
			{
				if($value)
				{
					$array_replace[$key] = str_replace($arrReplace, "", $value);
				}
			}
		}
		return $array_replace;
	}

	/**
	 * 메세지 발송 함수
	 * @param Array [recv_id, title] [contents, send_id, reserve_time, msg_level[success,error,warning,info], msg_type[M,N,S], msg_link]
	 * @param Array $arrReplace
	 * @return string
	 */
	public static function sendMessage($val)
	{
		if( !is_array($val) )
		{
			$val = $val->input();
		}
        // 데이터 점검
        if( !$val['recv_id'])
        {
            return "N";
        }

		// 기본값 셋팅
		if( !isset($val['title']) || $val['title']=="" )
		{
        	$val['title']   = "제목없음";
		}
		if( !isset($val['contents']) || $val['contents']=="" )
		{
        	$val['contents']   = "내용없음";
		}
		if( !isset($val['send_id']) || $val['send_id']=="" )
		{
        	$val['send_id']   = Auth::user()->id ?? 'SYSTEM';
		}

        $val['send_time']   = date("YmdHis");
        $val['send_status'] = "Y";
        $val['recv_status'] = "Y";

		if( !isset($val['reserve_time']) || $val['reserve_time']=="" )
		{
			$val['reserve_time'] = $val['send_time'];
		}

        // 메세지구분 - M:메세지(사용자간쪽지),N:공지,S:알람(시스템)
        if( !isset($val['msg_type']) || ( $val['msg_type']!="M" && $val['msg_type']!="N" && $val['msg_type']!="S" ) )
        {
            $val['msg_type'] = "M";
        }

		// 메세지 레벨
        if( !isset($val['msg_level']) || !$val['msg_level'] )
        {
            $val['msg_level'] = "gray";
        }
		// 예외처리
		if( $val['msg_level']=='danger' )
		{
			$val['msg_level'] = 'error';
		}
		if( ( $val['msg_type']=='S' ) && $val['msg_level']=='gray' )
		{
			$val['msg_level'] = 'info';
		}

        // DB에 등록
		$message_no = 0;
        $rslt = DB::dataProcess('INS', 'MESSAGES', $val, null, $message_no);

		if( $rslt=="Y" )
		{
			if( $val['send_time']==$val['reserve_time'] || ( $val['reserve_time']!="" && $val['reserve_time']<=date("YmdHis") ) )
			{
				$rs = event(new SendMessage($message_no));
				Log::debug('전송 OK');
				Log::debug($rs);
			}
			return "Y";
		}
		else
		{
			return "N";
		}

	}

	/**
	 * 에러 메세지 발송 - 시스템 관리자 system_error 로 메세지 보냄
	 * @param String
	 * @param String
	 */	
    public static function pushSystemErrorMessage($title, $contents="내용 없음")
    {
		$array_system_error = Func::getConfigArr('system_error');

		$msg = Array(
            'recv_id'  => '',
            'send_id'  => 'SYSTEM',
            'title'    => $title, 
            'contents' => $contents, 
            'msg_type' => 'S',
            'msg_level'=> 'error');

		if( sizeof($array_system_error)>0 )
		{
			foreach( $array_system_error as $id => $name )
			{
				$msg['recv_id'] = $id;
				Func::sendMessage($msg);
			}
		}
    }
	
	


	/**
	* 전화번호 출력양식, 추후 마스킹도 고려해야함.
	*
	* @param  String $ph1 - 지역번호
	* @param  String $ph2 - 국번
	* @param  String $ph2 - 번호
	* @return $string
	*/
	public static function phFormat($ph1, $ph2, $ph3)
	{
        $ph = trim($ph1).'-'.trim($ph2).'-'.trim($ph3);
		if($ph=='--')
			return '';
		else
			return $ph;
	}

	/**
	 * 숫자 아닌값 number_format 할때 에러 방지
	 * @param  String $val
	 * @return String $num
	 */
	public static function numberFormat($val)
	{
		if(is_numeric($val))
			return number_format($val);
		else
			return $val;
	}
	/**
	 * 보고서용. 숫자가 있는경우만 찍는다.
	 * @param  String $val
	 * @return String $num
	 */
	public static function numberReport($val)
	{
		if( is_numeric($val) && $val!="0" )
		{
			return number_format($val);
		}
		else
		{
			return "0";
		}
	}


	/**
	 * 배열의 키값에 해당하는 값을 가져온다. 배열이 없거나 해당 키가 없으면 에러가 발생하는 것 방지
	 *
	 * @param  string $array_data = 기준배열
	 * @param  string $keys = json으로 작성된 여러개의 키
	 * @param  string $div = 배열구분자
	 * @return string
	 */
	public static function getArrayNames($array_data, $keys, $div=', ')
	{
		$arrayKeys = json_decode($keys, true);
		if(is_null($arrayKeys))
			return '';

		$vals = null;
		if(count($arrayKeys)>0)
		{
			foreach($arrayKeys as $key)
			{
				$key = trim($key);
				if(isset($array_data[$key]))
				{
					$vals[] = $array_data[$key];
				}
			}
		}

		if(is_array($vals))
		{
			return implode($div, $vals);
		}
		else
		{
			return '';
		}
	}

	/**
	* 금액  , 제거 및 null 체크 
	*
	* @param  String - money
	* @return Int  - num
	*/
	public static function strToInt($money)
	{
		$num = 0;
		if(!empty($money)){
			$money = str_replace(",","",$money)*1;
			if(is_numeric($money)) $num = $money;
		}
		return $num;
	}

	/**
	* 숫자 한글로 변환
	*
	* @param  String - money
	* @return String - 한글숫자
	*/
	public static function numberToKor($number)
	{
		if( empty($number) )
		{
			return "";
		}

		if( !is_numeric($number) )
		{
			return "E";
		}

		$num = array('', '일', '이', '삼', '사', '오', '육', '칠', '팔', '구');
		$unit4 = array('', '만', '억', '조', '경');
		$unit1 = array('', '십', '백', '천');

		$res = array();

		$number = str_replace(',','',$number);
		$split4 = str_split(strrev((string)$number),4);

		for($i=0;$i<count($split4);$i++){
				$temp = array();
				$split1 = str_split((string)$split4[$i], 1);
				for($j=0;$j<count($split1);$j++){
						$u = (int)$split1[$j];
						if($u > 0) $temp[] = $num[$u].$unit1[$j];
				}
				if(count($temp) > 0) $res[] = implode('', array_reverse($temp)).$unit4[$i];
		}
		return implode('', array_reverse($res));
	}

	/**
	* 서류함(탭) 카운트 배열 만들어서 넘겨줌.
	*
	* @param  dbquery - count ==> item , cnt
	* @param  array - arrayTabs
	* @return array  - arrayTotalCnt
	*/
	public static function getTabsCnt($count, $arrayTabs)
	{
		//all에 자동으로 전체 cout 
		$arrayTotalCnt['ALL'] = 0;
		foreach ($count as $c)
			$arrayTotalCnt[$c->item] = $c->cnt;
		
		foreach($arrayTabs as $key=>$val)
		{
			if(!isset($arrayTotalCnt[$key]))
				$arrayTotalCnt[$key] = 0;

			$arrayTotalCnt['ALL']+= $arrayTotalCnt[$key];
		}
		return $arrayTotalCnt;
	}


	/**
	 * 컬럼명 가져오기
	 * 
	 * @param String $tblName
	 * @return stdClass
	 */
	public static function getColumns($tblName)
	{
		if(!isset($tblName))
		{
			return null;
		}
		$sch = config('app.sche');
		$columns = DB::select("SELECT COLUMN_NAME as colname FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_CATALOG = :sch AND TABLE_NAME = :tab ORDER BY ORDINAL_POSITION", Array('sch'=>$sch, 'tab'=>strtolower($tblName)));
		$null = NULL;
		$v = (object) $null;
		foreach( $columns as $col )
		{
			unset($tmp);
			$tmp = strtolower($col->colname);
			$v->$tmp = '';
		}
		return $v;
	}


	/**
	 * NVL
	 * 
	 * @param String $tblName
	 * @return stdClass
	 */
	public static function nvl(&$val1, $val2="")
	{
		return isset($val1) ? $val1 : $val2 ;
	}

	/**
	 * nvlEmpty
	 * 
	 * @param String $tblName
	 * @return stdClass
	 */
	public static function nvlEmpty(&$val1, $val2)
	{
		return !empty($val1) ? $val1 : $val2 ;
	}
	
	/**
	 * NVL 일반 값 매핑
	 * 
	 * @param String $tblName
	 * @return stdClass
	 */
	public static function nvl2($val1, $val2="")
	{
		return isset($val1) ? $val1 : $val2;
	}

	/**
	* 전체전화번호 출력양식
	*
	* @param  String $ph - 전체 전화번호
	* @return $string
	*/
	public static function fullPhFormat($ph)
	{
		$ph = trim($ph);
		$ph = str_replace(' ', '', $ph);
		$ph_2 = substr($ph, 0, 2);
		$ph_3 = substr($ph, 0, 3);
		$ph_len = strlen($ph);
		$ph_format = '';

		if ($ph_len == 0) {
			return '';
		}

		switch($ph_3)
		{
			case '010': case '011': case '016': case '017': case '018': case '019': case '070':
				if( $ph_len == 11 )
				{	
					$ph_format = substr($ph, 0, 3).'-'.substr($ph, 3, 4).'-'.substr($ph, 7, 4);
				}
				else if($ph_len == 10)
				{
					$ph_format = substr($ph, 0, 3).'-'.substr($ph, 3, 3).'-'.substr($ph, 6, 4);
				}
				break;

			case '050':
				$ph_format = substr($ph, 0, 4).'-'.substr($ph, 4, 3).'-'.substr($ph, 7, 4);
				break;
		
			default :
				// 서울
				if( $ph_2=='02' )
				{
					// 국번 3, 4자리 구분
					if( $ph_len==9 )
					{
						$ph_format = substr($ph, 0, 2).'-'.substr($ph, 2, 3).'-'.substr($ph, 5, 4);
					}
					else
					{
						$ph_format = substr($ph, 0, 2).'-'.substr($ph, 2, 4).'-'.substr($ph, 6, 4);
					}
				}
				// 지방
				else if(is_numeric($ph_2) && $ph_2*1 >= 3 && $ph_2*1 <= 6 )
				{
					// 국번 3, 4자리 구분
					if( $ph_len==10 )
					{
						$ph_format = substr($ph, 0, 3).'-'.substr($ph, 3, 3).'-'.substr($ph, 6, 4);
					}
					else
					{
						$ph_format = substr($ph, 0, 3).'-'.substr($ph, 3, 4).'-'.substr($ph, 7, 4);
					}
				}
		}	

		return $ph_format;
	}

	/**
	* 수정할 수 없는 심사상태 확인
	*
	* @param  char $status
	* @return bool
	*/
    public static function getEditStatus($status)
    {
        // 거절, 대출대상, 대출
        $array_status = ['X', 'F', 'Y'];

        // 수정불가
        if(in_array($status, $array_status))
            return false;
        else 
            return true;
    }


	/**
	* 메모 등록 + 약속 알람 등록 + 입금약속이 있으면 LOAN_INFO 업데이트
	*
	* @param  $_MEMO : 메모 배열 / $mode : 'INS', 'UPD', 'DEL' / $table : 메모 테이블명
	* @return String
	*		['Y' => 정상처리, 'E' => 데이터오류, 'MN' => 메모 등록 오류 'AN' => 알람 등록 오류  ]
	*/
	public static function saveMemo($_MEMO, $mode="INS", $table="CUST_INFO_MEMO")
	{
		if( !is_array($_MEMO) )
		{
			return "E";
		}

        if( isset($_MEMO['promise_date']) )
        {
            $_MEMO['promise_date']  = str_replace("-","",$_MEMO['promise_date']);
        }

        if( $mode == "DEL" )
        {
            $mode			      = "UPD";
            $_MEMO['del_time']    = $_MEMO['del_time']??date('YmdHis');
            $_MEMO['del_id']      = $_MEMO['del_id']??Auth::id();
            $_MEMO['save_status'] = "N";

			$whereArray = ['no' => $_MEMO['no']];

			// 삭제정보 불러오기
			$delInfo = DB::TABLE($table)->SELECT("*")->WHERE('no', $_MEMO['no'])->FIRST();
			$delInfo = Func::chungDec([$table], $delInfo);	// CHUNG DATABASE DECRYPT
			log::debug((array)$delInfo);
        }
        else
        {
			if( $mode=="INS" )
			{
            	$_MEMO['save_time']   = $_MEMO['save_time']??date('YmdHis');
            	$_MEMO['save_id']     = $_MEMO['save_id']??Auth::id();
				$_MEMO['save_status'] = "Y";
			}
			if( $mode == "UPD" )
			{
				$_MEMO['up_time']   = date('YmdHis');
            	$_MEMO['up_id']     = Auth::id();
				unset($_MEMO['save_time']);
				unset($_MEMO['save_id']);

				$whereArray = ['no' => $_MEMO['no']];
			}
			else
			{
				$whereArray = null;
			}
        }
        if( $mode == "INS" )
        {
            unset($_MEMO['no']);
        }
        $result = DB::dataProcess($mode, $table, $_MEMO, $whereArray);
		
        if( $result=="Y" && $mode!="DEL" )
        {
			//	알람 체크 시 알람 예약
            if( isset($_MEMO['promise_alarm']) && $_MEMO['promise_alarm']=="Y" && !isset($delInfo))
            {
                $_MSG = array
				(
                    'msg_type'     => 'S',
                    'send_id'      => 'SYSTEM',
                    'recv_id'      => Auth::id(),
                    'title'        => '입금약속 알람 (계약번호='.$_MEMO['loan_info_no'].')',
                    'contents'     => '입금약속 시간입니다<br>계약번호 = '.$_MEMO['loan_info_no'].'<br>약속금액 = '.number_format(str_replace(",","",empty($_MEMO['promise_money']) ? "0" : $_MEMO['promise_money'])).'원',
                    'reserve_time' => $_MEMO['promise_date'].$_MEMO['promise_hour'].$_MEMO['promise_min']."00",
					'msg_link'     => '/erp/custpop?cust_info_no='.$_MEMO['cust_info_no'].'&no='.$_MEMO['loan_info_no'].'&condition=&cnt='
                );
                $rslt = Func::sendMessage($_MSG);

                if( $rslt == "Y" )
                {
                    $msg = "Y";
                }
                else
                {
                    $msg = "AN";
                }
            }
            else
            {
                $msg = "Y";
            }
        }
        else if( $result=="N" )
        {
            $msg = "MN";
        }
        else
        {
            $msg = "E";
        }

		// 삭제처리 후
		if( $result == "Y" && isset($delInfo))
		{
			// 계약정보에서 최근 약속일자 불러오기
			$saveTime = $delInfo->save_time;
			$saveId = $delInfo->save_id;
			if(isset($delInfo->up_time) && $delInfo->up_time!='')
			{
				$saveTime = $delInfo->up_time;
				$saveId = $delInfo->up_id;
			}

			$msg = "Y";
		}

        return $msg;
	}


	public static function saveFile($_FILE, $mode="INS", $table="LOAN_USR_INFO_IMG")
	{
		if( !is_array($_FILE) )
		{
			return "E";
		}

        if( $mode == "DEL" )
        {
            $mode			      = "UPD";
            $_FILE['del_time']    = $_MEMO['del_time']??date('YmdHis');
            $_FILE['del_id']      = $_MEMO['del_id']??Auth::id();
            $_FILE['save_status'] = "N";

			$whereArray = ['no' => $_FILE['no']];

			// 삭제정보 불러오기
			$delInfo = DB::TABLE($table)->SELECT("*")->WHERE('no', $_FILE['no'])->FIRST();
			$delInfo = Func::chungDec([$table], $delInfo);	// CHUNG DATABASE DECRYPT
			log::debug((array)$delInfo);
        }
        else
        {
			if( $mode=="INS" )
			{
            	$_FILE['save_time']   = $_FILE['save_time']??date('YmdHis');
            	$_FILE['save_id']     = $_FILE['save_id']??Auth::id();
				$_FILE['save_status'] = "Y";
			}
			if( $mode == "UPD" )
			{
				$_FILE['up_time']   = date('YmdHis');
            	$_FILE['up_id']     = Auth::id();
				unset($_FILE['save_time']);
				unset($_FILE['save_id']);

				$whereArray = ['no' => $_FILE['no']];
			}
			else
			{
				$whereArray = null;
			}
        }
        if( $mode == "INS" )
        {
            unset($_FILE['no']);
        }
        $result = DB::dataProcess($mode, $table, $_FILE, $whereArray);
		
        if( $result=="Y" && $mode!="DEL" )
        {
			$msg = "Y";
        }
        else if( $result=="N" )
        {
            $msg = "MN";
        }
        else
        {
            $msg = "E";
        }

		// 삭제처리 후
		if( $result == "Y" && isset($delInfo))
		{
			// 계약정보에서 최근 약속일자 불러오기
			$saveTime = $delInfo->save_time;
			$saveId = $delInfo->save_id;
			if(isset($delInfo->up_time) && $delInfo->up_time!='')
			{
				$saveTime = $delInfo->up_time;
				$saveId = $delInfo->up_id;
			}

			$msg = "Y";
		}

        return $msg;
	}

	/**
 	* 개인정보  access 로그를 남긴다.
 	*
	* @param  string workType 일반적인 메뉴명을 넣는다.
	* @param  string ip
	* @param  string path
	* @param  string userAgent
	* @param  int loan_app_no
	* @param  int cust_info_no
 	* @return int
 	*/
	public static function setMemberAccessLog($workType, $ip, $path, $userAgent, $loan_app_no = null, $cust_info_no = null, $loan_info_no = null, $loan_usr_info_no = null)
	{
		if(Auth::id()) {
			$user_id = Auth::id();
		} else {
			$user_id = 'NONE';
		}

		$array_data = [
				'save_id' => $user_id,
				'loan_app_no' => $loan_app_no,
				'cust_info_no' => $cust_info_no,
				'ip' => $ip,
				'work_type' => $workType,
				'location' => $path,
				'year_month' => date("Ym"),
				'save_time' => date("YmdHis"),
				'user_agnt' => $userAgent,
				'loan_info_no' => $loan_info_no,
				'loan_usr_info_no' => $loan_usr_info_no,
		];

		$rs = DB::dataProcess('INS', 'users_access_log', $array_data);
		return $rs;
	}



	
	/**
	 * 쿼리확인 
	 * @param object $query_obj
	 * @return string
	 */
	public static function printQuery($query_obj)
	{
		$sql = $query_obj->toSql();
		$bindings = $query_obj->getBindings();

		if(isset($sql) && isset($bindings))
		{
			foreach($bindings as $binding)
			{
				// $value = is_numeric($binding) ? $binding : "'".$binding."'";
				$value = "'".$binding."'";
				$sql   = preg_replace('/\?/', $value, $sql, 1);
			}
		}
		else
		{
			return "ERROR";
		}

		return $sql;
	}	

	
	/**
	* 전문 문자열 셋팅 함수
	*
	* @param  char $status
	* @return bool
	*/
	public static function setstr($str, $size, $type,$encode='UTF-8')
	{
		$h = 0;
		if(mb_strlen($str,$encode)>$size)
		{
			$str = substr($str, 0, $size);

			for( $i=0; $i<$size ;$i++ )
			{
				if( ord($str[$i])>127 )
				{
					$h++; 
				}
			}
			if( $h%2==1 )
			{
				$str = substr($str, 0, -1);
			}
		}
		// 문자
		if($type=="AN" || $type=="C")
		{
			return str_pad($str, $size, " ");
		}
		// 숫자
		if($type=="N")
		{
			return str_pad($str, $size, "0", STR_PAD_LEFT);
		}

	}
	public static function setstrKR($str, $size, $type)
	{
		if( mb_detect_encoding($str, ['UTF-8','EUC-KR'], true)=="UTF-8" )
		{
			$str = iconv("UTF-8","EUC-KR",$str);
		}
		$h = 0;
		$str = substr($str, 0, $size);
		for( $i=0; $i<$size ;$i++ )
		{
			if(!isset($str[$i])) break;
			if( ord($str[$i])>127 )
			{
				$h++; 
			}
		}
		if( $h%2==1 )
		{
			$str = substr($str, 0, -1);
		}
		// 문자
		if($type=="AN")
		{
			return str_pad($str, $size, " ");
		}
		// 숫자
		if($type=="N")
		{
			return str_pad($str, $size, "0", STR_PAD_LEFT);
		}
	}
	
	/**
	* 영업일 가져오기
	*
	* @param  $today
	* @return $day
	*/
	public static function getBizDay($today)
	{
		// 휴일
		$holiday = Cache::remember('Func_getBizDay', 86400, function()
		{
			$rslt = DB::TABLE("DAY_CONF")->SELECT("*")->GET();
			foreach( $rslt as $v )
			{
				$day           = str_replace("-","",$v->day);
				$holiday[$day] = $day;
			}
			return $holiday;
		});

		while( in_array($today, $holiday) )
		{
			$today = date("Ymd", (Loan::dateToUnixtime($today) + (86400 * 1)));
		}
		return $today;
	}
	
	/**
	* 영업일 가져오기 (전날 기준)
	*
	* @param  $today
	* @return $day
	*/
	public static function getPrevBizDay($today)
	{
		// 휴일
		$holiday = Cache::remember('Func_getBizDay', 86400, function()
		{
			$rslt = DB::TABLE("DAY_CONF")->SELECT("*")->GET();
			foreach( $rslt as $v )
			{
				$day           = str_replace("-","",$v->day);
				$holiday[$day] = $day;
			}
			return $holiday;
		});

		while( in_array($today, $holiday) )
		{
			$today = date("Ymd", (Loan::dateToUnixtime($today) - (86400 * 1)));
		}
		return $today;
	}

	/**
	* 매매관리 양도업체를 가져온다. 2차배열 형태 응답
	*
	* @param  $com_div
	* @return Array[][]
	*/
	public static function getTradeManageList($com_div)
	{
		// Cache::flush();
		$array_trade_manage = Cache::remember('Func_getTradeManageList'.$com_div, 3600, function() use ($com_div) 
		{
			$trades = DB::TABLE("CONTRACT_TRADE_MANAGE")->SELECT("no","sell_com","buy_com")->WHERE('save_status','Y')->ORDERBY('no')->GET();
				
			foreach( $trades as $trade )
			{
				if($com_div == "sell_com")
				{
					$array_trade_manage[$trade->sell_com]   = $trade->sell_com;
				}
				else if($com_div == "buy_com")
				{
					$array_trade_manage[$trade->buy_com]   = $trade->buy_com;
				}
			}
			return $array_trade_manage;
		});
		
		return $array_trade_manage;
	}


	/**
	* curl 통신 함수
	*
	* @param  string  $url
	* @param  array  $postData
	* @param  bool  $isPost			// post 전송시 true
	* @param  bool  $returnJson 	// 결과를 json 받는 경우 true
	* @return array
	*/
	public static function ifCurl($url, $postData, $isPost=true, $returnJson=true, $timeout=0, $port='')
	{

		$curl = curl_init($url);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, $isPost);
		//curl_setopt($curl, CURLOPT_HTTPHEADER,array('Content-Type: application/json'));
		
		// 포트가 있을경우 = 쿠콘연동
		if($port!="")
		{
			curl_setopt($curl, CURLOPT_PORT, $port);
			curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
			curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($postData));
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		}
		else 
		{
			if($isPost)
			{
				curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($postData));
			}
		}

		if($timeout>0)
		{
			curl_setopt($curl, CURLOPT_TIMEOUT, $timeout);
		}

		// ssl 인증 안탐
		if(stristr($url, env('NR_URL', '')))
		{
			curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
		}

		$curl_response = curl_exec($curl);
		// $test = curl_getinfo($curl);
		// log::info(print_r($test,true));

		if ($curl_response === false) 
		{
			// 에러처리.
			// $info = curl_getinfo($curl);
			// Log::debug($info);
			Log::debug($url." : ".curl_error($curl));
			curl_close($curl);
		}

		if(is_resource($curl))
			curl_close($curl);
		
		if($returnJson)
			return json_decode($curl_response, true);
		else
			return $curl_response;
	}

	/**
	* curl 통신 비동기 함수(실제로는 socket 이용)
	*
	* @param  string  $url
	* @param  array  $postData
	* @param  bool  $type			// POST, GET
	* @return none
	*/
	public static function curlAsync($url, $postData, $type='POST')  
	{  
		foreach ($postData as $key => &$val)  
		{  
			if (is_array($val))  
			{
				$val = implode(',', $val);  
			}
			$postParams[] = $key.'='.urlencode($val);  
		}

		$postString = implode('&', $postParams);  
	
		$parts = parse_url($url);  
	
		if ($parts['scheme'] == 'http')  
		{  
			$fp = fsockopen($parts['host'], isset($parts['port']) ? $parts['port']:80, $errno, $errstr, 30);  
		}  
		else if ($parts['scheme'] == 'https')  
		{  
			$fp = fsockopen("ssl://" . $parts['host'], isset($parts['port']) ? $parts['port']:443, $errno, $errstr, 30);  
		}  
	
		// Data goes in the path for a GET request  
		if('GET' == $type)  
			$parts['path'] .= '?'.$postString;  
	
		$out = "$type ".$parts['path']." HTTP/1.1\r\n";  
		$out.= "Host: ".$parts['host']."\r\n";  
		$out.= "Content-Type: application/x-www-form-urlencoded\r\n";  
		$out.= "Content-Length: ".strlen($postString)."\r\n";  
		$out.= "Connection: Close\r\n\r\n";  

		// Data goes in the request body for a POST request  
		if ('POST' == $type && isset($postString))  
		{
			$out.= $postString;  
		}

		fwrite($fp, $out);  
		fclose($fp);  
	}  

	/**
	* 파일 압축 함수
	* @param  string  $createFileName 생성할 압축파일 경로 (확장자, 경로까지)
	* @param  arr  $files	압축할 파일 목록 경로 (파일 경로)
	* @return result
	*/
	public static function createZip($createFileName, $files)  
	{  
		$zip = new ZipArchive;

        if ($zip->open($createFileName, ZipArchive::CREATE) === TRUE)
        {
			foreach ($files as $key => $value) {
				$relativeNameInZipFile = mb_convert_encoding(basename($value),"euc-kr","UTF-8");
				$rs = $zip->addFile($value, $relativeNameInZipFile);
				if (!$rs) {
					return false;
				}
			}
            
            $zip->close();

			return true;
        } else {
			return false;
		}
	}  

	/**
	* 압축 해제 함수
	* @param  string  $unZipName 압축 파일 경로 (확장자, 경로까지)
	* @param  string  $unzipPath 압축해제할 폴더 경로
	* @return result
	*/
	public static function unZip($unZipName, $unzipPath)  
	{  
		$zip = new ZipArchive;
        if ($zip->open($unZipName) === TRUE)
        {
			$rs = $zip->extractTo($unzipPath);
			if (!$rs) {
				return false;
			}
		
            $zip->close();

			return true;
        } else {
			return false;
		}
	}  

	/**
	 * GZ 파일 압축 해제
	 * @param file 파일경로
	 */
    public static function createGZ($file) {
		try {
			exec('gzip -f ' . $file, $msg, $rs);
			return $rs;
		} catch ( Exception $e ) {
			Log::debug('압축해제 실패');
			return false;
		}
    }

	/**
	 * GZ 파일 압축 해제
	 * @param file 파일경로
	 */
    public static function unGZ($file) {
		try {
			exec('gunzip -f ' . $file, $msg, $rs);
			return $rs;
		} catch ( Exception $e ) {
			Log::debug('압축해제 실패');
			return false;
		}
    }

	/**
	* 부서정보를 가져온다. 1차배열 형태 응답
	*
	* @param  String
	* @return Array[]
	*/
	public static function getBranchById($id)
	{
		$branch = Cache::remember('Func_getBranchById'. $id, 3600, function() use ($id)
		{
			// $branch = DB::TABLE("BRANCH")->JOIN("USERS","USERS.BRANCH_CODE","=","BRANCH.CODE")->SELECT("*")->WHERE('USERS.ID',$id)->WHERE('USERS.SAVE_STATUS','Y')->WHERE('BRANCH.SAVE_STATUS','Y')->FIRST();
			$branch = DB::TABLE("BRANCH")->JOIN("USERS","USERS.BRANCH_CODE","=","BRANCH.CODE")->SELECT("*")->WHERE('USERS.ID',$id)->WHERE('USERS.SAVE_STATUS','Y')->WHERE('BRANCH.SAVE_STATUS','Y')->WHERERAW("(BRANCH.CLOSE_DATE= '' OR BRANCH.CLOSE_DATE IS NULL)")->FIRST();
			$branch = Func::chungDec(["BRANCH","USERS"], $branch);	// CHUNG DATABASE DECRYPT
			
			return $branch;
		});
		return $branch;
	}

	/**
	* 부서전화번호 가져오기
	*
	* @param  String
	* @return phone 
	*/
	public static function getBranchPh($branchCd)
	{
		$branch = Cache::remember('Func_getBranchPh'.$branchCd, 3600, function() use ($branchCd)
		{
			$branch = DB::TABLE("BRANCH")->SELECT("phone")->WHERE('CODE', $branchCd)->WHERE('SAVE_STATUS','Y')->FIRST();
			$branch = Func::chungDec(["BRANCH"], $branch);	// CHUNG DATABASE DECRYPT

			$ph = '';
			if(isset($branch->phone))
			{
				$ph = $branch->phone;
			}
			
			return $ph;
		});
		return $branch;
	}

	/**
	* 우편번호와 상품코드로 지점을 가져온다.
	*
	* @param  String
	* @return Array[]
	*/
	public static function getManagerCode($zipCd, $proCd='')
	{
		// 업무룰 정의 필요
		$v->branch_cd = '601';
		
		return $v;
	}

	
	/**
	* aes 암호화 함수
	*
	* @param  string $str
	* @param  string $enKey	
	* @return string
	*/
	public static function encrypt($str, $enKey='')
	{
		if($enKey=='ENC_KEY_SOL')
		{
			$key = config('app.enKey');
		}
		else
		{
			$key = env($enKey);
		}
		$str = trim($str);

		if(!$str || !$key)
			return $str;

		$rs = base64_encode(openssl_encrypt($str, config('app.cipher'), $key, true, str_repeat(chr(0), 16)));

		return $rs;
	}

   /**
	* aes 복호화
	*
	* @param  string $str
	* @param  string $enKey	
	* @return string
	*/
	public static function decrypt($str, $enKey)
	{
		$key = config('app.enKey');
		//$key = env($enKey);
		$str = (is_null($str)) ? '':$str;
		$str = trim($str);

		if(!$str || !$key)
			return $str;
		$rs = openssl_decrypt(base64_decode($str), config('app.cipher'), $key, true, str_repeat(chr(0), 16));

		return $rs;
	}

	/**
	 * 부동소수점 오류 방지를 위함.
	 *
	 * @return floor
	 */
    public static function floorCheck($value)
    {
        return (strstr($value, ".")) ? floor($value):$value*1;
    }

	/**
	*	IPCC 실명인증 송신키 생성
	*
	* @param  string $str
	* @param  string $enKey	
	* @return string
	*/
	public static function sendKeyCreate($presponseidNo, $KeyString)
	{
		$sDate = date("md");
		$day = substr($sDate,2);
		$mon = substr($sDate,0,2);

		$key1Mod = Func::MOD($mon * 30 + $day, 80);
		$key2Mod = Func::MOD($mon * 30 + substr($presponseidNo,3,3),80);
		$key3Mod = Func::MOD($mon * 30 + substr($presponseidNo,10,3),80);

		$PartKey1 = substr($KeyString,$key1Mod,Func::getAddKeyLen($key1Mod, 4));
		$PartKey2 = substr($KeyString,$key2Mod,Func::getAddKeyLen($key2Mod, 4));
		$PartKey3 = substr($KeyString,$key3Mod,Func::getAddKeyLen($key3Mod, 2));
		$PartKey4 = $PartKey1 . $PartKey2 . $PartKey3;
		$PartKey5 = substr($KeyString,0, 10 - strlen($PartKey4));

		$ret = $PartKey4 . $PartKey5;

		return $ret;
	}

	public static function MOD($a, $b) 
	{
        $rtn = $a - ($b *  floor($a / $b));
        return $rtn;
    }

	public static function getAddKeyLen($key, $maxLen)
	{
		if($maxLen == 4)
		{
			if ($key == 79){
                return 1;
            }else if ($key == 78){
                return 2;
            }else if ($key == 77){
                return 3;
            }else{
                return 4;
            }
        } else {
            if ($key == 79){
                return 1;
            }else{
                return 2;
            }
        }
		
	}

	/**
	 * 문자, 우편물 발송금지 체크
	 * $lumpYn == 'Y' 일경우는 화해-신복,개회를 막는다. 
	 * $lumpYn == 'N' 일경우는 화해-신복,개회 관계없이 마지막 법착 상태를 보고 판단한다.
	 * 
	 * CUST_INFO.PRHB_YN
	 * CUST_INFO.ATTRIBUTE_DELAY_CD
	 * LOAN_INFO_LAW.LAW_TYPE
	 * LOAN_INFO.MANAGER_CODE
	 */
	public static function getBanInfo($cust_info_no, $cust_ban_chk="Y", $lumpYn='Y')
	{

		$ban_sms  = "N";
		$ban_post = "N";
		$ban_anne = "N";

		// 고객정보
		$CSQL = "SELECT * FROM CUST_INFO WHERE SAVE_STATUS='Y' AND NO = ? ";
        $ci   = DB::select($CSQL, Array($cust_info_no))[0];


		// 0. 이미 ban_sms가 선택됨
		$cust_ban_sms   = ( $ci->ban_sms=="Y"  ) ? "Y " : "N" ;
		$cust_ban_post  = ( $ci->ban_post=="Y" ) ? "Y " : "N" ;
		$cust_ban_call  = ( $ci->ban_call=="Y" ) ? "Y " : "N" ;


		// 1. 대리인선임
		if( $ci->prhb_yn=="Y" )
		{
			Log::debug("CUST_INFO_NO = ".$cust_info_no." / BAN_INFO#1 (속성:대리인선임) = Y / ".$ci->prhb_yn);
			$ban_sms = "Y";
		}
		
		// 2. 채권구분1. 살아있는 채권중에 해당 코드가 있으면 막는다.
		$LSQL = "SELECT count(1) AS cnt FROM LOAN_INFO WHERE SAVE_STATUS='Y' AND CUST_INFO_NO = ? AND STATUS IN ('A','B','C','D','S')";
		$vcnt = DB::select($LSQL, [$cust_info_no])[0];
		Log::debug((array)$vcnt);
		if( $vcnt->cnt>0 )
		{
			Log::debug("CUST_INFO_NO = ".$cust_info_no." / BAN_INFO#2 (관리:채권구분코드1) = Y / ");
			$ban_sms = "Y";
		}

		// 안내문자제한 코드/일괄발송일경우만 적용한다.
		if($lumpYn=='Y')
		{
			// 일괄문자 일괄출력 일경우 개인회생 화해, 신용회복 화해 막아야함
			$LSQL = "SELECT COUNT(*) AS CNT FROM LOAN_INFO WHERE SAVE_STATUS='Y' AND CUST_INFO_NO = ? AND SETTLE_DIV_CD IN ('2','3') ";
			$vcnt = DB::select($LSQL, Array($cust_info_no))[0];
			if( $vcnt->cnt>0 )
			{
				Log::debug("CUST_INFO_NO = ".$cust_info_no." / BAN_INFO#4 (상태:개회신복화해) = Y / ".$vcnt->cnt);
				$ban_sms = "Y";
			}

			Log::debug($ci->sms_lmt_cd);
			if( $ci->sms_lmt_cd=="Y1" || $ci->sms_lmt_cd=="Y2" )
			{
				// 상환일
				$LSQL = "SELECT MIN(RETURN_DATE) AS RETURN_DATE FROM LOAN_INFO WHERE SAVE_STATUS='Y' AND CUST_INFO_NO = ? AND STATUS IN ('A','B','C','D','S')";
				$vcnt = DB::select($LSQL, Array($cust_info_no))[0];
				$return_date = $vcnt->return_date;

				// Y1 = 당일1회 = 상환일도래전 안내금지 (상환일 당일부터 문자가능)
				if( $ci->sms_lmt_cd=="Y1" )
				{
					$today = date("Ymd");
					if( $return_date > $today )
					{
						Log::debug("CUST_INFO_NO = ".$cust_info_no." / Y1 / RETURN_DATE = ".$return_date);
						$ban_anne = "Y";
					}
				}
				// Y2 = 전일1회 = 상환일 전영업일 전 안내금지 (상환일 전영업일부터 문자가능)
				if( $ci->sms_lmt_cd=="Y2" )
				{
					$today = Func::getBizDay(Loan::addDay(date("Ymd")));
					if( $return_date > $today )
					{
						Log::debug("CUST_INFO_NO = ".$cust_info_no." / Y2 / RETURN_DATE = ".$return_date);
						$ban_anne = "Y";
					}
				}

			}
		}

		// 우선 기준을 받을 때까지 우편물은 문자와 같이 간다.
		$ban_post = $ban_call = $ban_sms;

		// 기본값은 고객정보 ban_info 체크
		if( $cust_ban_chk=="Y" )
		{
			if( $cust_ban_sms=="Y" )
			{
				$ban_sms = $cust_ban_sms;
			}
			if( $cust_ban_post=="Y" )
			{
				$ban_post = $cust_ban_post;
			}
			if( $cust_ban_call=="Y" )
			{
				$ban_call = $cust_ban_call;
			}
		}

		return ["ban_sms"=>$ban_sms, "ban_anne"=>$ban_anne, "ban_post"=>$ban_post, "ban_call"=>$ban_call];
	}




	/**
	 * 우편물 불거주주소 체크 및 우편물주소 리턴
	 * 
	 * @return  Array
	 * rslt : Y - 불거주주소 (출력불가) // N - (출력가능)
	 * addr : 우편물주소 [zip, addr1, addr2]
	 */
	public static function getNliveInfo($cust_info_no, $post_addr="P")
	{
		$nlive_flag = "N";
		$msg = "";
		$ADDR = [];

		log::info("Nlive Chk");

		//	불거주체크가능 post_cd
		$nlive_check_cd = ['1','2','4'];
		
		if( $post_addr == "P" )
		{
			$post_send_cd = DB::TABLE("CUST_INFO_EXTRA")->SELECT("post_send_cd")->WHERE("cust_info_no", $cust_info_no)->first()->post_send_cd;
			$post_send_cd = $post_send_cd == "9"? "4" : $post_send_cd;
		}
		else
		{
			$post_send_cd = $post_addr;
		}

		$post_send_cd = trim($post_send_cd);

		log::info($post_send_cd);

		if( empty($post_send_cd) )
		{
			$post_send_cd = "2";
			// $nlive_flag = "Y";
			// $msg = "우편물주소 없음";
		}
		
		
		$info =  DB::TABLE("CUST_INFO ci")->LEFTJOIN("CUST_INFO_EXTRA cie", "ci.no", "=", "cie.cust_info_no");

		if( in_array($post_send_cd, ["1","2","3","4"]) )
		{
			$info->SELECT("cie.zip".$post_send_cd." as zip", "cie.addr".$post_send_cd."1 as addr1", "cie.addr".$post_send_cd."2 as addr2");
		}

		if( in_array($post_send_cd, $nlive_check_cd) ) 
		{
			$info->ADDSELECT("cie.addr".$post_send_cd."_nlive_yn as nlive_yn");
		}

		$info = $info->WHERE("ci.no", $cust_info_no)->first();
		$info = Func::chungDec(["CUST_INFO","CUST_INFO_EXTRA"], $info);	// CHUNG DATABASE DECRYPT

		if( isset($info->nlive_yn) && $info->nlive_yn == "Y" )
		{
			$nlive_flag = "Y";
			$msg = "불거주 지역으로 발송 불가";
		}
		else
		{
			$ADDR = ["zip"=>$info->zip ?? '', "addr1"=>$info->addr1 ?? '', "addr2"=>$info->addr2 ?? ''];
		}

		if( $nlive_flag == "Y" )
		{
			return ["rslt"=>$nlive_flag, "msg"=>$msg];
		}

		return ["rslt"=>$nlive_flag, "addr"=>$ADDR];
	}

	/*
	*	온라인 신청번호 만드는 함수
	*	규칙 : "UPS"."랜덤5자릿수". $loan_app_no."랜덤5자릿수"
	*/
	public static function getOnlineAppNo($loan_app_no, $div='UPS')
	{
		if( empty($loan_app_no) || !is_numeric($loan_app_no) )
		{
			return false;
		}

		return $div.sprintf('%05d',rand(1, 99999)).$loan_app_no.sprintf('%05d',rand(1, 99999));
	}

	/*
	*	온라인 신청번호 만드는 함수
	*	규칙 : "UPS"."랜덤5자릿수". $loan_app_no."랜덤5자릿수"
	*/
	public static function myPermitBranch()
	{
		$array_branch = Array();

		// 전지점
		if( Func::funcCheckPermit("E004") )
		{
			$array_branch = Func::getBranchDiv('02');

		}
		// 지역장
		else if( Func::funcCheckPermit("E031") )
		{
			// $SQL = "SELECT CODE, BRANCH_NAME, CLOSE_DATE FROM BRANCH WHERE SAVE_STATUS='Y' AND BRANCH_DIV='02' AND BRANCH_AREA=( SELECT BRANCH_AREA FROM BRANCH WHERE CODE = ? ) ORDER BY CLOSE_DATE, BRANCH_NAME";
			$SQL = "SELECT CODE, BRANCH_NAME, CLOSE_DATE FROM BRANCH WHERE SAVE_STATUS='Y' AND BRANCH_DIV='02'  AND (CLOSE_DATE = '' OR CLOSE_DATE IS NULL)  AND BRANCH_AREA=( SELECT BRANCH_AREA FROM BRANCH WHERE CODE = ? ) ORDER BY BRANCH_AREA,BRANCH_DIV,CODE";
			$rst = DB::SELECT($SQL, Array( Auth::user()->branch_code ));
			foreach( $rst as $branch )
			{
				if( $branch->close_date!="" )
				{
					$branch->branch_name = "(폐쇄) ".$branch->branch_name;
				}
				$array_branch[$branch->code]   = $branch->branch_name;
			}

		}
		// 내지점
		else
		{
			// $SQL = "SELECT CODE, BRANCH_NAME, CLOSE_DATE FROM BRANCH WHERE SAVE_STATUS='Y' AND CODE = ? ";
			$SQL = "SELECT CODE, BRANCH_NAME, CLOSE_DATE FROM BRANCH WHERE SAVE_STATUS='Y'  AND CODE = ? AND (CLOSE_DATE = '' OR CLOSE_DATE IS NULL) ";
			$rst = DB::SELECT($SQL, Array( Auth::user()->branch_code ));
			foreach( $rst as $branch )
			{
				if( $branch->close_date!="" )
				{
					$branch->branch_name = "(폐쇄) ".$branch->branch_name;
				}
				$array_branch[$branch->code]   = $branch->branch_name;
			}
		}


		return $array_branch;
	}

	/*
	*	계약별 필수서류 가져오기
	*	규칙 : "UPS"."랜덤5자릿수". $loan_app_no."랜덤5자릿수"
	*/
	public static function getLoanNecessaryDoc($no)
	{
		$docs = ['91','02','03'];   // 기본 필수서류 = 91자필, 02초본, 03신분증

        // 담보대출 필수서류 = 근저당설정계약서, 위임장 = 코드 추가해야 함.
        // $docs[] = "";
        // $docs[] = "";
        
        /*
        // 개별적으로 등록한 필수 서류 체크
        $di = DB::table('LOAN_INFO_DOC')->SELECT('app_document_cd')->WHERE('LOAN_INFO_NO',$no)->WHERE('SAVE_STATUS','Y')->WHERENOTIN('APP_DOCUMENT_CD',$docs)->WHERE('NECESSARY_CHK','Y')->get();
        foreach($di as $v)
        {
            if( !in_array($v->app_document_cd, $docs) )
            {
                $docs[] = $v->app_document_cd;
            }
        }
        */
        return $docs;
	}


	/*
		원장변경내역 입력

		$_DATA : 입력 배열
	*/
	public static function saveWonjangChgHist($_DATA)
	{
		if( !is_array($_DATA) )
		{
			return false;
		}

		if( empty($_DATA['loan_status']) || empty($_DATA['manager_code']) )
		{
			$loan = DB::TABLE("LOAN_INFO")->SELECT("STATUS, MANAGER_CODE")->WHERE("NO", $_DATA['loan_info_no'])->WHERE("SAVE_STATUS", "Y")->first();

			$_DATA['loan_status']  = empty($_DATA['loan_status']) ? $loan->status : $_DATA['loan_status'];
			$_DATA['manager_code'] = empty($_DATA['manager_code']) ? $loan->manager_code : $_DATA['manager_code'];
		}

		// 2021-09-03 div_nm 코드화 작업
		$arrayWonjanTrns = array_flip(Vars::$arrayWonjangHisCd);
		$_DATA['div_cd'] = $arrayWonjanTrns[$_DATA['div_nm']];

		$rslt = DB::dataProcess("INS", "wonjang_chg_hist", $_DATA);
		return $rslt;
	}

	/*
		리스트 검색 부분에 텍스트 추가

		$pageTxt : 검색 몇건...
		$addTxt : 추가할 단어
	*/
	public static function addTextPage($pageTxt, $addTxt)
	{
		return str_replace('검색', $addTxt.' 검색', $pageTxt);
	}

	/** 
	*	단계별 승인가능한 사용자 가져오기
	* 	@param Array  $arr_permit_no array[div]=>"코드" 코드는 예를들어 A101이라면 01 만 보내면 됨
	*	@param String $loan_info_no
	*	@return Array array[div][승인단계][id]=>name
	*/
	public static function getArrConfirmId($arr_permit_no,$loan_info_no=null)
	{
		// 채권의 관리지점 가져오기
		$manager_code = empty($loan_info_no)?Auth::user()->branch_code:DB::table("loan_info")->where("no",$loan_info_no)->value("manager_code");
		// $manager_code = Auth::user()->branch_code;

		// log::info($manager_code);

		foreach($arr_permit_no as $div_no => $permit_no)
		{
			$arr_con_level = Func::getArrConfirmLevel($permit_no);
	
			$select_sql = "";
			foreach($arr_con_level as $lv => $permit_str)
			{
				$select_sql .= "CASE WHEN CONFIRM_PERMIT LIKE '%".$permit_str."%' THEN 'Y' ELSE 'N' END ".$lv.",\r\n";
			}

		
			$permit = DB::table("USERS")
						->SELECT(DB::RAW(" BRANCH_CODE,".$select_sql."ID,NAME"))
						->WHERERAW(" CONFIRM_PERMIT LIKE '%A_".$permit_no."%'  AND ((permit LIKE '%E035%') OR (BRANCH_CODE='".$manager_code."')) ");
						// LOG::INFO(Func::printQuery($permit));
			$permit = $permit->get();
			$permit = Func::chungDec(["USERS"], $permit);	// CHUNG DATABASE DECRYPT
			
			// log::info(print_r($permit,true));
			unset($arr_id);
			if(sizeof($permit)>0)
			{
				foreach($permit as $idx => $obj)
				{
					foreach($arr_con_level as $col => $v)
					{
						$arr_id[$col]['unset']= "";

						if(isset($obj->$col) && $obj->$col == "Y")
						{
							$arr_id[$col][$obj->id] = $obj->name;	
						}
					
						unset($arr_id[$col]['unset']);
					}
				}
				$arr_confirm_id[$div_no] = $arr_id;
			}
			else
			{
				foreach($arr_con_level as $col => $v)
				{
					$arr_confirm_id[$div_no][$col] = "";
				}
			}
		}
		
		// log::info(print_R($arr_confirm_id,true));
		return $arr_confirm_id;
	}

	
	/** 
	*	업무별 승인단계 가져오기
	*
	*	
	*	
	*/
	public static function getArrConfirmLevel($permit_no)
	{
		$i = 0;
		foreach(Vars::$arrConfirmPermit as $p)
		{
			if(str_ends_with($p,$permit_no))
			{
				$col = str_starts_with($p,"A1")?"app_id":"confirm_id_".$i;
				$arrConLevel[$col] = $p;
				$i++;
			}
		}
		return $arrConLevel;
	}
	
	/*
	* 복호화대상 테이블 기준 복호화 반영
	*
	* @param array 암복호화 대상 테이블정보
	* @param object 검색 데이터 -> GET(), FIRST()
	* @return	object 복호화된 검색 데이터
	*/
	public static function chungDec($arrTbl, $data)
    {		
		$dec = Decrypter::all($data, $arrTbl);
		
		return $dec;
    }
	/**
	 * 문자열 암호화
	 * @param object 타겟 데이터
	 * @return	object 암호화 데이터
	 */
	public static function chungEncOne($data)
	{
		return Func::encrypt($data, 'ENC_KEY_SOL');
	}
	/**
	 * 해당 데이터 복호화 반영
	 * @param object 검색 데이터
	 * @return	object 복호화된 검색 데이터
	 */
	public static function chungDecOne($data)
	{
		return Func::decrypt($data, 'ENC_KEY_SOL');
	}
	/*
	* 2차원배열을 1차원배열로 변환
	*
	* @param array 2차원배열
	* @return array 1차원배열로 변환된 결과값
	*/
	public static function arrayMerge($arr)
    {
		$arrReturn = array();
		if(is_array($arr) && sizeof($arr) > 0)
		{
			foreach(Vars::$arrayLawType as $div => $arr) $arrReturn = array_merge($arrReturn, $arr);
		}
		return $arrReturn;
	}

	public static function getFloor($addr)
	{
        $tmpAddr = explode(" ", $addr);

        foreach ($tmpAddr as $k => $v) {
            if (preg_replace("/[0-9]/", "", $v) == "제층" )     $realFloor = preg_replace("/[^0-9]/", "", $v);
        }

        return isset($realFloor) ? $realFloor : 0;
    }

	public static function KBScrap($scrap, $addrArea)
	{
		$returnArr['addrArea'] = $addrArea;

		if (empty($scrap['kb_sise_date'])) {
			if (empty($scrap['building_kb_sise']) && empty($scrap['str_complex_no']) && empty($scrap['str_area'])) {
				return Func::alertAndClose('KB 시세조회 결과가 없습니다!');
			}
			if (!empty($scrap['building_kb_sise']['시세미제공사유'])) {
				return Func::alertAndClose('KB : '.$scrap['building_kb_sise']['시세미제공사유'].'('.$scrap['building_kb_sise']['시세미제공사유상세'].')');
			}		
			if (empty($scrap['str_complex_no'])) {
				return Func::alertAndClose('KB : 주소와 일치하는 건물을 찾지 못했습니다.');
			}		
			if (empty($scrap['str_area'])) {
				return Func::alertAndClose('KB : 면적정보와 일치하는 건물을 찾지 못했습니다.');
			}	
		} else {
			if ($scrap['str_slnd_perty_cd'] != '08' && $scrap['building_kb_sise']['시세미제공사유'] == '시세 미노출') {
				return Func::alertAndClose('KB 시세 미노출인 경우입니다.');
			}
			$returnArr['raw'] = [
				'search_date' => date('Y-m-d'),  // 시세 조회일
				'sise_update_date' => date('Y-m-d', strtotime($scrap['kb_sise_date']))  // kb시세 업데이트일
			];
		}

		$returnArr['kb_obj_id'] = $scrap['building_kb_sise']['물건식별자'];
		$returnArr['kb_type_no'] = $scrap['building_kb_sise']['주택형순번'];

		// 가장 최근 실거래일
		$latestTradeDate = '';

		// 평균실거래가 - 6개월 전 1일이 기준
		$sixMonthsAgo = date('Y-m-d', mktime(0, 0, 0, intval(date('m'))-6, 1, intval(date('Y'))));
		$generalArr = $limitArr = [];
		$avgTrade = $minTrade = $avgLimitTrade = $minLimitTrade = 0;

		// 최근실거래가 - 3/6/9개월 전 오늘이 기준
		$recentDate3 = date('Y-m-d', mktime(0, 0, 0, intval(date('m'))-3, intval(date('d')), intval(date('Y'))));
		$recentDate6 = date('Y-m-d', mktime(0, 0, 0, intval(date('m'))-6, intval(date('d')), intval(date('Y'))));
		$recentDate9 = date('Y-m-d', mktime(0, 0, 0, intval(date('m'))-9, intval(date('d')), intval(date('Y'))));
		$recentArr3 = $recentArr6 = $recentArr9 = [];
		$recentTrade3 = $recentTrade6 = $recentTrade9 = 0;

		// 준공년월일
		$kbBuildDate = !empty($scrap['kb_build_date']) ? str_replace('.', '-', $scrap['kb_build_date']) : '';

		$returnArr['clclt'] = [
			'kb_rslt_addr' => !empty($scrap['search_addr']) ? $scrap['search_addr'] : '',
			'kb_url' => !empty($scrap['kb_url']) ? $scrap['kb_url'] : '',
			'kb_households_cnt' => !empty($scrap['holder_cnt']) ? $scrap['holder_cnt'] : '',
			'kb_area_code' => !empty($scrap['area_cd']) ? $scrap['area_cd'] : '',
			'ccr_area' => $returnArr['addrArea']['area'],
			'kb_supply_area' => !empty($scrap['supply_area']) ? $scrap['supply_area'] : $scrap['building_kb_sise']['공급면적'],
			'kb_build_date' => $kbBuildDate,
			'kb_build_term' => ceil(Func::dateTerm($kbBuildDate, date('Y-m-d')) / 365),
			'kb_construct' => !empty($scrap['kb_construct']) ? $scrap['kb_construct'] : '',
			'kb_total_dong' => !empty($scrap['total_dong']) ? $scrap['total_dong'] : '',
			'kb_room_cnt' => !empty($scrap['room_cnt']) ? $scrap['room_cnt'] : '',
			'kb_sise' => !empty($scrap['building_kb_sise']['매매일반거래가']) ? $scrap['building_kb_sise']['매매일반거래가'] * 10000 : '',
			'kb_sise_high' => !empty($scrap['building_kb_sise']['매매상한가']) ? $scrap['building_kb_sise']['매매상한가'] * 10000 : '',
			'kb_sise_low' => !empty($scrap['building_kb_sise']['매매하한가']) ? $scrap['building_kb_sise']['매매하한가'] * 10000 : '',
			'real_floor' => Func::getFloor($returnArr['addrArea']['addr']),
			'is_low' => $scrap['is_low'] == 1 ? 'Y' : 'N',
			'is_top' => $scrap['is_top'] == '일반' ? 'N' : 'Y',
			'search_date' => date('Y-m-d')
		];


		if (empty($scrap['real_trade']) || isset($scrap['real_trade']['message'])) {
			// 실거래 내역 없음
		} else {
			if ($scrap['str_slnd_perty_cd'] != '08') {
				// 아파트/주상복합
				$realTradeArr = [];  // ['real_trade'] 밑으로 ['data'] 있는 경우와 없는 경우 분기 (KbScrap.php:654)
				if (isset($scrap['real_trade']['data']))	$realTradeArr = $scrap['real_trade']['data'];
				else										$realTradeArr = $scrap['real_trade'];

				foreach ($realTradeArr as $key => $val) {
					if (!empty($val['조회기간'])) {
						$tradeDate = date('Y-m-d', strtotime(explode('~', $val['조회기간'])[0]));
						$key == 0 ? $latestTradeDate = $tradeDate : false;
						$returnArr['trade'][] = [
							'kb_real_trade_date' => $tradeDate,
							'kb_real_trade_money' => $val['매매실거래금액'] * 10000,
							'kb_real_trade_floor' => $val['해당층수']
						];
						
						if ($tradeDate >= $sixMonthsAgo) {
							if ($val['해당층수'] > 2)		array_push($generalArr, $val['매매실거래금액']);
							else							array_push($limitArr, $val['매매실거래금액']);
						}
	
						if ($tradeDate >= $recentDate3)	array_push($recentArr3, $val['매매실거래금액']);
						if ($tradeDate >= $recentDate6)	array_push($recentArr6, $val['매매실거래금액']);
						if ($tradeDate >= $recentDate9)	array_push($recentArr9, $val['매매실거래금액']);
					}
				}
			} else {
				// 다세대(빌라)
				foreach ($scrap['real_trade'] as $key => $val) {
					if (!empty($val['계약일'])) {
						$tradeDate = str_replace('.', '-', $val['계약일']).'-01';
						$key == 0 ? $latestTradeDate = $tradeDate : false;
						$returnArr['trade'][] = [
							'kb_real_trade_date' => $tradeDate,
							'kb_real_trade_money' => $val['거래금액'] * 10000,
							'kb_real_trade_floor' => $val['층']
						];
						
						if ($tradeDate >= $sixMonthsAgo) {
							if ($val['층'] > 2)		array_push($generalArr, $val['거래금액']);
							else					array_push($limitArr, $val['거래금액']);
						}
	
						if ($tradeDate >= $recentDate3)	array_push($recentArr3, $val['거래금액']);
						if ($tradeDate >= $recentDate6)	array_push($recentArr6, $val['거래금액']);
						if ($tradeDate >= $recentDate9)	array_push($recentArr9, $val['거래금액']);
					}
				}
			}

			$sum = $sum2 = $sum3 = $sum4 = $sum5 = 0;
			if (sizeof($generalArr) != 0) {
				for ($i=0; $i < sizeof($generalArr); $i++) { 
					$sum += $generalArr[$i];
				}
				$avgTrade = floor($sum / sizeof($generalArr) / 10) * 10;  // 평균(일반)거래가
				$minTrade = min($generalArr);  // 최저(일반)거래가
			}
			if (sizeof($limitArr) != 0) {
				for ($i=0; $i < sizeof($limitArr); $i++) { 
					$sum2 += $limitArr[$i];
				}
				$avgLimitTrade = floor($sum2 / sizeof($limitArr) / 10) * 10;  // 평균(하한)거래가
				$minLimitTrade = min($limitArr);  // 최저(하한)거래가
			}

			if (sizeof($recentArr3) != 0) {
				for ($i=0; $i < sizeof($recentArr3); $i++) { 
					$sum3 += $recentArr3[$i];
				}
				$recentTrade3 = floor($sum3 / sizeof($recentArr3) / 10) * 10;  // 최근3개월실거래가
			}
			if (sizeof($recentArr6) != 0) {
				for ($i=0; $i < sizeof($recentArr6); $i++) { 
					$sum4 += $recentArr6[$i];
				}
				$recentTrade6 = floor($sum4 / sizeof($recentArr6) / 10) * 10;  // 최근6개월실거래가
			}
			if (sizeof($recentArr9) != 0) {
				for ($i=0; $i < sizeof($recentArr9); $i++) { 
					$sum5 += $recentArr9[$i];
				}
				$recentTrade9 = floor($sum5 / sizeof($recentArr9) / 10) * 10;  // 최근9개월실거래가
			}

			$returnArr['clclt']['avg_trade'] = $avgTrade == 0 ? $returnArr['clclt']['kb_sise'] : $avgTrade * 10000;
			$returnArr['clclt']['min_trade'] = $minTrade == 0 ? $returnArr['clclt']['kb_sise'] : $minTrade * 10000;
			$returnArr['clclt']['avg_limit_trade'] = $avgLimitTrade == 0 ? $returnArr['clclt']['kb_sise_low'] : $avgLimitTrade * 10000;
			$returnArr['clclt']['min_limit_trade'] = $minLimitTrade == 0 ? $returnArr['clclt']['kb_sise_low'] : $minLimitTrade * 10000;
			$returnArr['clclt']['recent_count_3'] = sizeof($recentArr3);
			$returnArr['clclt']['recent_trade_3'] = $recentTrade3 * 10000;
			$returnArr['clclt']['recent_count_6'] = sizeof($recentArr6);
			$returnArr['clclt']['recent_trade_6'] = $recentTrade6 * 10000;
			$returnArr['clclt']['recent_count_9'] = sizeof($recentArr9);
			$returnArr['clclt']['recent_trade_9'] = $recentTrade9 * 10000;
			$returnArr['clclt']['latest_trade_date'] = $latestTradeDate;
		}

		return $returnArr;
	}

	public static function alertAndClose($message)
	{
		return "<script>alert('$message'); self.close();</script>";
	}


	public static function multiArr($category,$contents) // select in 함수
	{
		unset($i);
		$i = 0;

		// 암호화 대상 컬럼 리스트 추출
		$arrayAllCol = array();
		$obj = new Decrypter();
		$arrayAllCol_list = $obj->arrayEncCol;
		foreach($arrayAllCol_list as $key => $val)
		{
			foreach($val as $value)
			{
				array_push($arrayAllCol, $value);
			}
		}

		$in_vlaue = "";
		for ($i = 0; $i < count($contents); $i++)
		{
			// 암호화 대상 조건 추가 - 2022-09-29
			// .name 일때는 무조건 암호화. 2023-04-28 neo
			if(stristr($category, '.name') || in_array($category, $arrayAllCol) )
			{
				LOG::debug('암호화 대상 : '.$category);
				$contents[$i] = Func::encrypt($contents[$i], 'ENC_KEY_SOL');
				if($i<count($contents)-1) $in_vlaue .= "'".$contents[$i]."',";
				else $in_vlaue .= "'".$contents[$i]."'";
			}
			else if( is_numeric($contents) )
			{
				if($i<count($contents)-1) $in_vlaue .= "".$contents[$i].",";
				else $in_vlaue .= "".$contents[$i]."";
			}
			else
			{
				if($i<count($contents)-1) $in_vlaue .= "'".$contents[$i]."',";
				else $in_vlaue .= "'".$contents[$i]."'";
			}
		}
		return $in_vlaue;
	}

	public static function multiContents($value, $detail='') // multi_contents 재정렬
	{
		if(!empty($detail) && stristr($detail, 'cust_info_no'))
		{
			$value = Func::stripCi($value);		
		}

		$trim_multi_content = preg_replace("/\s| /",',',$value);	
		$array_content = explode( ',', $trim_multi_content );
		$array_content = str_replace("-", "", $array_content);
		$array_content = array_filter($array_content);

		$i = 0;
		foreach($array_content as $key=>$val) 
		{
			unset($array_content[$key]);
			$new_key = $i;
			$array_content[$new_key] = $val;
			$i++;
		}

		return $array_content;
	}

	/**
	* account_info(입금계좌 환경설정) 테이블의 bank_code(bank_code), bank_ssn(계좌번호) 반환 
	* @return Arr - 결과 코드 배열
	*/
	public static function getAccountInfo()
	{
		$acc = DB::TABLE("account_info")->SELECT("no","bank_code","bank_ssn")->WHERE('save_status', 'Y');
		$configs = $acc->get();
		foreach($configs as $config)
		{
			$configArr[$config->no] = $config->bank_ssn;
		}
		return $configArr;
	}

		/**
     * 파일 URL 생성 
     *
     * @param string $table 조회파일 테이블
     * @param string $key	조회파일 키값
	 * @return string URL  
     */
	public static function setFileLink($table,$key)
	{	
		$url =  URL::signedRoute('filelink',[$table,$key]);
		//운영계는 리다이렉트 시켜야 파일 제대로 뜸 
		if(!Func::isDev()){
			$url = str_replace("http://","https://", $url);
		}
		return $url;
	}

	/**
	 * 일괄처리 실패엑셀 생성
	 *
	 * @param string $header	엑셀헤더
	 * @param string $body		엑셀데이터
	 * @param string $div		엑셀구분값	
	 * @return int boolean
	 */
	public static function failExcelMake($header, $body, $div)
	{	
		$result['filepath'] = "/upload_file/fail_".$div;
		$result['filename'] = "/lump_fail_".$div."_".date("YmdHis").".xls";	// 서버 저장파일명
		
		// 폴더가 없으면 생성
		if(!file_exists(Storage::path($result['filepath'])))
		{
			umask(0);
			mkdir(Storage::path($result['filepath']), "755", true);
		}
		
		// 헤더 
		array_push($header, "오류내용");
		$col_idx = Coordinate::stringFromColumnIndex(count($header));

		// 헤더전체 BORDER,스타일
		$style['custom'] = [
			'A1:'.$col_idx.'1'=> [
				'font' => ['bold'=>true], 
				'fill' => ['fillType' => Fill::FILL_SOLID, 'color' => ['argb' => 'ebebec']],
				'borders' => [
					'allBorders'=>['borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN],
				],
				'alignment' => [
					'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
				],
			],
		];

        Excel::store(new ExcelCustomExport($header, $body,'오류내용',$style), $result['filepath'].$result['filename']);

		return $result;
	}

	/**
	 * 특정 테이블의 컬럼 코멘트 가져오기
	 * @param String $tblName
	 * @return string
	 */
	public static function getComments($tblName) {
		if(!isset($tblName))
		{
			return null;
		}
		$sch = config('app.sche');
		
		$comments = DB::CONNECTION('nsf')->TABLE('pg_stat_all_tables as ps');
		$comments->JOIN('pg_description as pd', 'ps.relid', '=', 'pd.objoid');
		$comments->JOIN('pg_attribute as pa', 'pd.objoid', '=', 'pa.attrelid');
		$comments->SELECT('attname', 'description');
		$comments->WHERE('pd.objsubid', '=', DB::RAW('CAST(pa.attnum AS INTEGER)'));
		$comments->WHERE('ps.schemaname', '=', $sch);
		$comments->WHERE('ps.relname', '=', $tblName);
		$comments->ORDERBY('ps.relname', 'asc')->ORDERBY('pd.objsubid', 'asc');

		return $comments;
	}

	/**
	* 코드관리의 코드배열을 2차배열까지 반환한다.
	* 
	*
	* @param  code - 코드 테이블에서 검색할 code 값
	* @return Arr - 결과 코드 2차 배열
	*/
	public static function getConfigChain($code)
	{
		$configArr = Cache::remember('Func_getConfigChain'.$code, 300, function() use ($code)
		{
            // 서브코드를 먼저 가져온다.
            $subCode = DB::table("conf_sub_code")
                        ->select("*")
                        ->where('save_status', 'Y')->where('cat_code', $code)
                        ->orderby('code_order')->orderBy('sub_code')
                        ->get();
            foreach($subCode as $sub)
            {
                $subCodes[$sub->conf_code][$sub->sub_code] = $sub->sub_code_name;
            }

			$builder = DB::table("conf_code")->select("*")->where('save_status', 'Y')->where('cat_code', $code);
			$configs = $builder->orderby('code_order')->orderby('code')->get();

			foreach($configs as $config)
			{
				$configArr[$config->code]['name'] = $config->name;

                $configArr[$config->code]['subcode'] = null;
                if(isset($subCodes[$config->code]))
                {
                    $configArr[$config->code]['subcode'] = $subCodes[$config->code];
                }
			}

			return $configArr;
		 });
		return $configArr;
	}

    /**
	 * 2차 배열의 키값에 해당하는 값을 가져온다. 배열이 없거나 해당 키가 없으면 에러가 발생하는 것 방지. 
	 * 배열에 없을 경우는 key를 리턴
	 *
	 * @param  string $array_data = 기준 2차 배열 
	 * @param  string $key = 키
     * @param  string $key2 = 서브 키
     * @param  string $delemiter = 키와 서브키를 구분자
	 * @return string
	 */
	public static function getChainName($array_data, $key, $key2, $delemiter='.', $returnType='text')
	{
        $arrayTitle[0] = '';
		$arrayTitle[1] = '';
		if($key!='' && isset($array_data[$key]))
		{
			$arrayTitle[0] = $array_data[$key]['name'];

            if($key2!='' && isset($array_data[$key]['subcode'][$key2]))
            {
                $arrayTitle[1] = $array_data[$key]['subcode'][$key2];
            }
            else 
            {
                if($key2!='')
                {
                    $arrayTitle[1] = $key2;
                }
            }
		}
		else
		{
            $arrayTitle[0] = $key;
            if($key2!='')
            {
                $arrayTitle[1] = $key2;
            }
		}
		
		if($returnType=='text')
		{
			return $arrayTitle[0].$delemiter.$arrayTitle[1];
		}
		else 
		{
			return $arrayTitle;
		}

	}


	/**
	* 2단계 SELECT 구현. 현재 메모 사용중
	*
	* @param  codeTitle - select 타이틀
	* @param  arrayChain - 2차배열 $array[code][name]과 $array[code][subcode] 로 구성됨. getConfigChain()과 같이 사용
	* @param  mainCode - select 첫번째 코드명
	* @param  subCode - select 두번째 코드명
	* @param  codeVal - select 첫번째 선택값
	* @param  subCodeVal - select 두번째 선택값
	* @return String (html)
	*/
	public static function printChainOption($codeTitle, $arrayChain, $mainCode, $subCode, $codeVal='', $subCodeVal='', $subCodeAction='', $subCodeTitle='', $isSelectPicker='Y')
	{
		$rs = '';

		// 대분류 배열 만들고 소분류 데이터 json으로 만들어 놓는다.
		foreach($arrayChain as $code=>$vals)
		{
			// 대분류 지정
			$codes[$code] = $vals['name'];

			// 소분류 
			if(isset($vals['subcode']))
			{
				$subcodes[$code] = $vals['subcode'];
				// $rs.= 'var select_json_'.$mainCode.' = \''.json_encode($vals['subcode']).'\';\\n';\
				$rs.= '<div id="select_json_'.$mainCode.'_'.$code.'" style="display:none">'.json_encode($vals['subcode']).'</div>';
			}
			else 
			{
				$subcodes[$code] = null;
				// $rs.= 'var select_json_'.$mainCode.' = \'{}\';\\n';
				$rs.= '<div id="select_json_'.$mainCode.'_'.$code.'" style="display:none"></div>';
			}
		}
		
		if($isSelectPicker!='Y')
		{
			$rs.="<table><tr><td>";
		}
		
		// 대분류 
		$rs.= '<select class="form-control '.( ($isSelectPicker=='Y') ? ' form-control-sm selectpicker':' form-control-xs').' mr-1 mb-1 mt-1" name="'.$mainCode.'" id="select_id_'.$mainCode.'" onchange="getSubSelect(this.value, \''.$mainCode.'\', \''.$subCode.'\', \''.$codeTitle.'구분선택\', \'\', \''.$isSelectPicker.'\');" >';
		$rs.= '<option value="">'.$codeTitle.' 선택</option>';
		if(isset($codes))
		{
			$rs.= Func::printOption($codes, $codeVal, false);
		}
		$rs.= '</select>';

		if($isSelectPicker!='Y')
		{
			$rs.="</td><td>";
		}

		// 소분류
		$subCodeTitle = ($subCodeTitle=='') ? $codeTitle.'구분선택':$subCodeTitle;
		$rs.= '<select class="form-control '.( ($isSelectPicker=='Y') ? ' form-control-sm selectpicker':' form-control-xs').' mr-1 mb-1 mt-1" name="'.$subCode.'" id="select_id_'.$subCode.'" '.$subCodeAction.'>';
		$rs.= '<option value="">'.$subCodeTitle.'</option>';
		// 소분류 값이 있을때만
		if($codeVal!='' && isset($subcodes[$mainCode]) )
		{
			$rs.= Func::printOption($subcodes[$mainCode], $subCodeVal, false);
		}
		$rs.= '</select>';

		if($isSelectPicker!='Y')
		{
			$rs.="</td></tr></table>";
		}

		return $rs;
	}


	/**
	* 2단계 SELECT 멀티 선택 구현. 현재 메모 사용중
	*
	* @param  codeTitle - select 타이틀
	* @param  arrayChain - 2차배열 $array[code][name]과 $array[code][subcode] 로 구성됨. getConfigChain()과 같이 사용
	* @param  mainCode - select 첫번째 코드명
	* @param  subCode - select 두번째 코드명
	* @param  codeVal - select 첫번째 선택값
	* @param  subCodeVal - select 두번째 선택값
	* @return String (html)
	*/
	public static function printMultiChainOption($codeTitle, $arrayChain, $mainCode, $subCode, $codeVal='', $subCodeVal='', $subCodeAction='', $subCodeTitle='', $searchLive=true)
	{
		$rs = '';

		// 대분류 배열 만들고 소분류 데이터 json으로 만들어 놓는다.
		foreach($arrayChain as $code=>$vals)
		{
			// 대분류 지정
			$codes[$code] = $vals['name'];

			// 소분류 
			if(isset($vals['subcode']))
			{
				$subcodes[$code] = $vals['subcode'];
				// $rs.= 'var select_json_'.$mainCode.' = \''.json_encode($vals['subcode']).'\';\\n';\
				$rs.= '<div id="select_json_'.$mainCode.'_'.$code.'" style="display:none">'.json_encode($vals['subcode']).'</div>';
			}
			else 
			{
				$subcodes[$code] = null;
				// $rs.= 'var select_json_'.$mainCode.' = \'{}\';\\n';
				$rs.= '<div id="select_json_'.$mainCode.'_'.$code.'" style="display:none"></div>';
			}
		}

		$subCodeTitle = ($subCodeTitle=='') ? $codeTitle.'구분선택':$subCodeTitle;
		
		// 대분류 
		$rs.= '<select class="form-control form-control-sm selectpicker mr-1 mb-1 mt-1" name="'.$mainCode.'[]" id="select_id_'.$mainCode.'" onchange="getSubSelectMulti(\'select_id_'.$mainCode.'\', \''.$mainCode.'\', \''.$subCode.'\', \''.$subCodeTitle.'\');" multiple data-actions-box="true" data-none-selected-text="'.$codeTitle.'" data-live-search="'.$searchLive.'">';
		if(isset($codes))
		{
			$rs.= Func::printOption($codes, $codeVal, false);
		}
		$rs.= '</select>';
		
		// 소분류
		$rs.= '<select class="form-control form-control-sm selectpicker mr-1 mb-1 mt-1" name="'.$subCode.'[]" id="select_id_'.$subCode.'" '.$subCodeAction.' multiple data-actions-box="true" data-none-selected-text="'.$subCodeTitle.'" data-live-search="'.$searchLive.'">';
		// 소분류 값이 있을때만
		if($codeVal!='' && isset($subcodes[$mainCode]) )
		{
			$rs.= Func::printOption($subcodes[$mainCode], $subCodeVal, false);
		}
		$rs.= '</select>';
		return $rs;
	}

	/*
	*	내 권한이 있는 부서와 담당자를 2차배열로 리턴
	*	
	*/
	public static function myPermitBranchManager()
	{
		$array_branch = Array();

		$myBranch = Func::myPermitBranch();
		
		$branchManager = [];
		foreach($myBranch as $key=>$val)
		{
			$branchManager[$key]['name'] = $val;
			$branchManager[$key]['subcode'] = null;

			$userList = Func::getBranchUsers($key);
			if(!empty($userList))
			{
				$branchManager[$key]['subcode'] = $userList;
			}
		}
		return $branchManager;
	}

	// 차입자번호에 특정 문자를 첨가한다.
	public static function addCi($cust_info_no='')
	{
		return config('app.addci').$cust_info_no;
	}

	// 차입자번호에 특정 문자를 첨가한걸 삭제한다.
	public static function stripCi($txt)
	{
		return str_replace(config('app.addci'), '', $txt);
	}


	
	/*
	*	이자세부내역 타이틀 가져오기
	*	
	*/
	public static function getInterestTitle($arr)
	{
		$interest_title = "이자세부내역\n";
		if($arr['misu_money'])
		{
			$interest_title.= "미수:".Func::numberFormat($arr['misu_money'])."\n";
		}
		if($arr['lack_delay_money'])
		{
			$interest_title.= "부족:".Func::numberFormat($arr['lack_delay_money'])."\n";
		}
		if($arr['delay_interest'])
		{
			$interest_title.= "연체:".Func::numberFormat($arr['delay_interest'])."\n";
		}
		if($arr['interest'])
		{
			$interest_title.= "정상:".Func::numberFormat($arr['interest'])."\n";
		}
		if($arr['settle_interest'])
		{
			$interest_title.= "화해:".Func::numberFormat($arr['settle_interest'])."\n";
		}

		return $interest_title;
	}

	/*
	*	TR색상변화
	*	
	*/
	public static function trColor($color="DDE5F3", $color2="FFFFFF")
	{
		$str = " OnMouseOver=style.background='#".$color."' OnMouseOut=style.background='#".$color2."' bgcolor='#".$color2."'";
		return $str;
	}

	/*
	*	퍼센트
	*	
	*/
	public static function percentReport($bunja, $bunmo, $cut=2)
	{
		$bunja = str_replace(",", "", str_replace("%", "", $bunja));
		$bunmo = str_replace(",", "", str_replace("%", "", $bunmo));

		if($bunmo && $bunja)
		{
			return round($bunja/$bunmo*100,$cut)."%";
		}
	}

	/**
	* 암호화 컬럼 like 검색
	*
	* @param  $query - 쿼리객체
	* @param  $col - 검색 컬럼
	* @param  $val - 검색 값
	* @param  $option - before, after, all
	* @param  $sub - 복호화된 문자열 자를 길이
	* @return $query
	*/
	public static function encLikeSearch($query, $col, $val, $option='all', $sub=null)
	{
		$query->where($col, '=', Func::encrypt($val, 'ENC_KEY_SOL'));

		return $query;
	}
	

	/**
	* 암호화 컬럼 like 검색 문자열 반환
	*
	* @param  $col - 검색 컬럼
	* @param  $val - 검색 값
	* @param  $option - before, after, all
	* @param  $sub - 복호화된 문자열 자를 길이
	* @return $query
	*/
	public static function encLikeSearchString($col, $val, $option='all', $sub=null)
	{
		$str = $col."='".Func::encrypt($val, 'ENC_KEY_SOL')."'";
		
		return $str;
	}

	/**
	* 암호화 컬럼 order 
	*
	* @param  $query - 쿼리객체
	* @param  $col - 컬럼
	* @param  $asc - 정렬기준
	* @param  $sub - 복호화된 문자열 자를 길이
	* @return $query
	*/
	public static function encOrderBy($query, $col, $asc, $sub=null)
	{
		$query->orderBy($col, $asc);
		
		return $query;
	}

	/**
	*	문자열에 셀렉트박스 추가
	*	
	*/
	public static function selectStrAdd($arr, $ob_name="", $combo_bg_style="style='background:#FEEEE0'")
	{
		$str = "";
		if($arr)
		{
			foreach($arr as $key => $val)
			{
				$val=trim($val);
				if($ob_name!="")
				{
					$str.= "<option value='".$key."'";
					if(!strcmp($ob_name, $key)) $str.="selected";
					$str.= " $combo_bg_style>$val</option>";
				}
				else
				{
					$str.= "<option value='".$key.$combo_bg_style."> ".$val."</option>";
				}
			}
		}
		return $str;
	}

	/**
	*	채권상태 보여주기.
	*	
	*/
	public static function getLoanStatus($status, $isColor=false)
	{
		// 색상 여부
		$getStatus = ($isColor==true) ? Vars::$arrayContractStaColor:Vars::$arrayContractSta;

		$sta = Func::getArrayName($getStatus, $status);
			
		return $sta;
	}	

	/**
	 * OPB 계산 원금+비용
	 *
	 * @param  loaninfo, balance와 cost_money가 꼭 있어야 한다.
	 * @return array 
	*/
	public static function getOpb($loan)
	{
		// 오류는 -1로 준다.
		if(!isset($loan->balance) || !isset($loan->cost_money))
		{
			return -1;
		}


		return $loan->balance + $loan->cost_money;
	}

	/**
	* 일수 증가
	*
	* @param  Date  - 기준일자 YYYYMMDD
	* @param  Int   - 증가일수 (기본값 1)
	* @return Date  - 증가된일자 YYYYMMDD
	*/
	public static function addDay($today, $cnt=1)
	{
		return date("Ymd", (Loan::dateToUnixtime($today) + (86400 * $cnt)));
	}

	/**
	* 담당 이름 표시
	*
	* @param  Code  - 담당 부서 코드
	* @return Name  - 담당 부서 이름
	*/
	public static function getChargeDepartment($code)
	{
		if($code != null || $code != ''){
			return Vars::$arrayChargeTeam[$code];
		}
	}

	/**
	*	투자상태 보여주기
	*	
	*/
	public static function getInvStatus($status, $isColor=false)
	{
		// 색상 여부
		$getStatus = ($isColor==true) ? Vars::$arrayInvStaColor:Vars::$arrayInvSta;

		$sta = Func::getArrayName($getStatus, $status);
			
		return $sta;
	}	
}