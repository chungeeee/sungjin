<?php
// 테스트용 Frame -> 사용안함
// Route::get('/config/userframe/{action}/{tno}/{contractNo?}',   'Config\UserController@userframe');
// Route::get('/config/userinfo/{tno}',                           'Config\UserController@userInfo');
// Route::view('/config/usermsg/{tno}',                           'config.userTestFrameMemo');
// Route::view('/config/usermsginput/{tno}',                      'config.userTestFrameMemoInput');

// 부서관리
Route::get ('/config/branch',               'Config\BranchController@branch')               ->name('부서관리 메인');
Route::post('/config/branchlist',           'Config\BranchController@branchList')           ->name('부서관리 메인리스트');
Route::post('/config/branchform',           'Config\BranchController@branchForm')           ->name('부서관리 입력창');      
Route::post('/config/branchaction',         'Config\BranchController@branchAction')         ->name('부서관리 입력저장');

// 직원관리
Route::get ('/config/user',                 'Config\UserController@user')                   ->name('직원관리 메인');
Route::post('/config/userlist',             'Config\UserController@userList')               ->name('직원관리 메인리스트');
Route::post('/config/userexcel',            'Config\UserController@userExcel')              ->name('직원관리 엑셀 다운로드');
Route::post('/config/userform',             'Config\UserController@userForm')               ->name('직원관리 입력창');
Route::post('/config/useraction',           'Config\UserController@userAction')             ->name('직원관리 입력저장');
Route::any('/config/usertest',              'Config\UserController@userTest')               ->name('직원관리 테스트 메인');
Route::any('/config/usertestlist',          'Config\UserController@userTestList')           ->name('직원관리 테스트 메인리스트');

// 직원접속기록
Route::get ('/config/userloginlog',         'Config\UserController@userLoginLog')           ->name('직원관리 로그인기록'); 
Route::post('/config/userloginloglist',     'Config\UserController@userLoginLogList')       ->name('직원관리 로그인기록 리스트'); 
Route::post('/config/userlogexcel',         'Config\UserController@userLogExcel')           ->name('직원관리 로그인기록 엑셀 다운로드'); 
Route::post('/config/userbranchdiv',        'Config\UserController@userBranchDiv')          ->name('직원관리 부서별 직원출력'); 

// 권한관리(부서별)
Route::get ('/config/permitbranch',         'Config\PermitController@permitBranch')         ->name('메뉴권한관리(부서별) 부서메인');
Route::post('/config/permitbranchmenus',    'Config\PermitController@permitBranchMenus')    ->name('메뉴권한관리(부서별) 메뉴권한리스트');
Route::post('/config/permitbranchaction',   'Config\PermitController@permitBranchAction')   ->name('메뉴권한관리(부서별) 메뉴권한저장');

// 권한관리(직원별)
Route::get ('/config/permituser',           'Config\PermitController@permitUser')           ->name('메뉴권한관리(직원별) 직원메인');
Route::post('/config/permituserlist',       'Config\PermitController@permitUserList')       ->name('메뉴권한관리(직원별) 직원 리스트');
Route::post('/config/permitusermenus',      'Config\PermitController@permitUserMenus')      ->name('메뉴권한관리(직원별) 메뉴권한리스트');
Route::post('/config/permituseraction',     'Config\PermitController@permitUserAction')     ->name('메뉴권한관리(직원별) 메뉴권한저장');

// 기능권한관리(직원별)
Route::get ('/config/funcpermituser',       'Config\PermitController@funcPermitUser')       ->name('기능권한관리(직원별) 직원메인');
Route::post('/config/funcpermitusermenus',  'Config\PermitController@funcPermitUserMenus')  ->name('기능권한관리(직원별) 기능권한리스트');
Route::post('/config/funcpermituseraction', 'Config\PermitController@funcPermitUserAction') ->name('기능권한관리(직원별) 기능권한저장');

// 기능권한변경내역
Route::get ('/config/changepermitinfo',     'Config\ChangeController@changePermitInfo')     ->name('기능권한변경내역 메인');
Route::post('/config/changepermitinfolist', 'Config\ChangeController@changePermitInfoList') ->name('기능권한변경내역 메인 리스트');

// 직원정보변경내역
Route::get ('/config/changeuserinfo',      'Config\ChangeController@changeUserInfo')        ->name('직원정보변경내역 메인');
Route::post('/config/changeuserinfolist',  'Config\ChangeController@changeUserInfoList')    ->name('직원정보변경내역 메인 리스트');
Route::post('/config/changeusertarget',    'Config\ChangeController@changeUserTarget')      ->name('직원정보변경내역 직원 리스트');

// 코드관리
Route::get ('/config/code',                 'Config\CodeController@code')                   ->name('코드관리 메인');
Route::post('/config/codelist',             'Config\CodeController@codeList')               ->name('코드관리 메인 리스트');
Route::post('/config/codeform',             'Config\CodeController@codeForm')               ->name('코드관리 입력창');
Route::post('/config/codeaction',           'Config\CodeController@codeAction')             ->name('코드관리 입력저장');
Route::post('/config/cacheclear',           'Config\CodeController@cacheClear')             ->name('캐시 초기화');
Route::post('/config/subcodeform',          'Config\CodeController@subCodeForm')		    ->name('코드관리 하위코드 입력폼');
Route::post('/config/subcodeaction',        'Config\CodeController@subCodeAction')		    ->name('코드관리 하위코드 저장');

// 영업일관리
Route::get ('/config/calendar',             'Config\CalendarController@calendar')           ->name('영업일관리 메인');
Route::post('/config/calendarholiday',      'Config\CalendarController@calendarHoliday')    ->name('영업일관리 메인 휴일리스트');
Route::post('/config/calendarinsert',       'Config\CalendarController@calendarInsert')     ->name('영업일관리 메인 휴일리스트저장');

// SMS관리
Route::get ('/config/sms',                  'Config\SmsController@sms')                     ->name('SMS관리 메인 리스트');
Route::post('/config/smsaction',            'Config\SmsController@smsAction')               ->name('SMS관리 입력저장');

// 문자발송내역
Route::get ('/config/smshistory',           'Config\SmsController@smsHistory')              ->name('문자발송내역 메인');
Route::post('/config/smshistorylist',       'Config\SmsController@smsHistoryList')          ->name('문자발송내역 메인 리스트');
Route::post('/config/smshistoryaction',     'Config\SmsController@smsHistoryAction')        ->name('문자발송내역 삭제');
Route::post('/config/smshistoryexcel',      'Config\SmsController@smsHistoryExcel')         ->name('문자발송내역 엑셀 다운로드');

// 문자발송제한
Route::get ('/config/smslimit',             'Config\SmsController@smsLimit')                ->name('문자발송건수 메인');
Route::post('/config/smslimitlist',       'Config\SmsController@smsLimitList')              ->name('문자발송내역 메인 리스트');
Route::post('/config/smslimitaction',       'Config\SmsController@smsLimitAction')          ->name('문자발송건수 저장');

// 첨부서류관리
Route::get ('/config/doc',                  'Config\DocController@doc')                     ->name('첨부서류관리 메인');
Route::post('/config/doclist',              'Config\DocController@docList')                 ->name('첨부서류관리 메인 리스트');

// 메뉴관리
Route::get ('/config/menu',                 'Config\MenuController@menu')                   ->name('메뉴관리 메인');
Route::post('/config/menulist',             'Config\MenuController@menuList')               ->name('메뉴관리 메인 리스트');
Route::post('/config/menuform',             'Config\MenuController@menuForm')               ->name('메뉴관리 메인 입력창');
Route::post('/config/menuaction',           'Config\MenuController@menuAction')             ->name('메뉴관리 메인 입력저장');

//직업코드
Route::get ('/config/jobcode',              'Config\JobController@jobCode')                 ->name('직업코드 메인');
Route::post('/config/jobcodelist',          'Config\JobController@jobCodeList')             ->name('직업코드 메인 리스트');
Route::get ('/config/jobcodepop',           'Config\JobController@jobCodePop')              ->name('직업코드 메인 입력창');
Route::post('/config/jobaction',            'Config\JobController@jobAction')               ->name('직업코드 메인 입력저장');

// 배치관리
Route::get ('/config/batch',                'Config\BatchController@batch')                 ->name('배치관리 메인');
Route::post('/config/batchlist',            'Config\BatchController@batchList')             ->name('배치관리 메인 리스트');
Route::get('/config/batchform',             'Config\BatchController@batchForm')             ->name('배치관리 메인 입력창');
Route::post('/config/batchformaction',      'Config\BatchController@batchFormAction')       ->name('배치관리 메인 입력저장');

// 배치로그
Route::get ('/config/batchlog',             'Config\BatchController@batchLog')              ->name('배치로그 메인');
Route::post('/config/batchloglist',         'Config\BatchController@batchLogList')          ->name('배치로그 메인 리스트');
Route::post('/config/batchlogexcel',        'Config\BatchController@batchLogExcel')         ->name('배치로그 엑셀 다운로드');

// 개인정보 조회 로그
Route::get ('/config/visitlog',             'Config\VisitController@visitLog')              ->name('개인정보 조회 로그 메인');
Route::post('/config/visitloglist',         'Config\VisitController@visitLogList')          ->name('개인정보 조회 로그 메인 리스트');


// 승인권한관리
Route::get ('/config/confirmpermit',         'Config\PermitController@confirmPermit')         ->name('승인권한관리 메인');
Route::post('/config/getconfirmpermit',      'Config\PermitController@getConfirmPermit')      ->name('승인권한 가져오기');
Route::post('/config/confirmpermitaction',   'Config\PermitController@ConfirmPermitAction')   ->name('승인권한 저장');

// 일마감
Route::get ('/config/dayend',                  'Config\DayEndController@dayend')              ->name('일마감 메인');
Route::post('/config/dayendlist',              'Config\DayEndController@dayendList')          ->name('일마감 리스트');
Route::post('/config/dayendform',              'Config\DayEndController@dayendForm')          ->name('일마감 입력창');      
Route::post('/config/dayendformaction',        'Config\DayEndController@dayendFormAction')    ->name('일마감 입력 저장');      
Route::post('/config/dayendexcel',             'Config\DayEndController@dayendExcel')         ->name('일마감 엑셀다운로드');

/*
// 분리보관관리
Route::get ('/config/separate',                 'Config\SeparateController@separate')         ->name('분리보관관리 메인');
Route::post('/config/separatelist',             'Config\SeparateController@separateList')     ->name('분리보관관리 리스트');
Route::get('/config/separatepop',               'Config\SeparateController@separatPop')       ->name('분리보관관리 환경설정');
Route::post('/config/separaterequest',          'Config\SeparateController@separateRequest')  ->name('분리보관관리 환경설정저장');
Route::post('/config/separaterestore',          'Config\SeparateController@separateRestore')  ->name('분리보관관리 복원');
Route::post('/config/separateexcel',            'Config\SeparateController@separateExcel')    ->name('분리보관관리 엑셀다운');
*/

// 등기부등본관리
Route::get ('/config/regist',                    'Config\RegistController@regist')            ->name('등기부등본관리 메인');
Route::post ('/config/registlist',               'Config\RegistController@registList')        ->name('등기부등본리스트');
Route::post ('/config/registform',               'Config\RegistController@registForm')        ->name('등기부등본리스트');
Route::post ('/config/registaction',             'Config\RegistController@registAction')      ->name('등기부등본 저장');
Route::get ('/config/getregistfile',             'Config\RegistController@getRegistFile')     ->name('등기부등본 미리보기');
Route::post ('/config/downregistfile',           'Config\RegistController@downRegistFile')    ->name('등기부등본 다운로드');
Route::post ('/config/registexcel',              'Config\RegistController@registExcel')       ->name('등기부등본 엑셀다운');


// 일괄처리로그 
Route::get  ('/config/lumplog',                   'Config\LumplogController@lumplog')         ->name('일괄처리로그 메인');
Route::post ('/config/lumploglist',               'Config\LumplogController@lumplogList')     ->name('일괄처리로그 리스트');
Route::post('/config/lumplogexcel',               'Config\LumplogController@lumplogExcel')    ->name('일괄처리로그  엑셀다운로드');
Route::get('/config/lumplogsample',               'Config\LumplogController@lumplogSample')	  ->name('일괄처리로그  샘플파일다운로드');
Route::get('/config/lumplogfail',                 'Config\LumplogController@lumplogFail')	  ->name('일괄처리로그  결과파일다운로드');

Route::get ('/config/lumplogpop/{div}',           'Config\LumplogController@lumplogPop')      ->name('일괄처리로그 팝업화면');
Route::post ('/config/lumplogpop/{div}/list',      'Config\LumplogController@lumplogPopList') ->name('일괄처리로그 팝업리스트');

Route::post ('/config/lumplogupload',             'Config\LumplogController@lumplogUpload')   ->name('일괄처리로그 엑셀업로드');
Route::post ('/config/lumplogAction',             'Config\LumplogController@lumplogAction')   ->name('일괄처리로그 파일첨부 저장');
Route::post ('/config/lumplogdownfile',           'Config\LumplogController@lumplogDownFile') ->name('일괄처리로그 등록한 파일 저장');


// 녹취서버 녹취리스트
Route::any('/config/record',                      'Config\RecordController@record')           ->name("녹취파일검색 메인");  
Route::post('/config/recordlist',                 'Config\RecordController@recordList')       ->name("녹취파일검색 리스트");  

?>