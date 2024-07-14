<?php

// 현장관리

// 현장정보창
Route::get ('/field/management',                    'Field\ManagementController@management')                    ->name('현장관리 메인');
Route::post('/field/managementlist',                'Field\ManagementController@managementList')                ->name('현장관리 리스트');
Route::post('/field/managementexcel',               'Field\ManagementController@managementExcel')               ->name('현장관리 엑셀다운');
Route::post('/field/managementform',                'Field\ManagementController@managementForm')                ->name('현장관리 입력 form');
Route::post('/field/managementformaction',          'Field\ManagementController@managementFormAction')          ->name('현장관리 입력 form 저장');
Route::post('/field/managementlumpdelete',          'Field\ManagementController@managementLumpDelete')          ->name('현장관리 일괄삭제');

Route::get ('/field/managementpop',                 'Field\ManagementController@managementPop')                 ->name('현장관리 팝업창');

Route::post('/field/managementinfo',                'Field\ManagementController@managementInfo')                ->name('현장관리 정보');
Route::post('/field/managementinfoaction',          'Field\ManagementController@managementInfoAction')          ->name('현장관리 저장');

Route::post('/field/managementhistory',             'Field\ManagementController@managementHistory')             ->name('현장내역 정보');
Route::post('/field/managementhistoryaction',       'Field\ManagementController@managementHistoryAction')       ->name('현장내역 저장');

Route::get ('/field/managementhistoryexcelsample',  'Field\ManagementController@managementHistoryExcelSample')	->name('현장내역 엑셀업로드샘플파일다운로드');
Route::post('/field/managementhistoryexcelform',    'Field\ManagementController@managementHistoryExcelForm')    ->name('현장내역 엑셀업로드');
Route::post('/field/managementhistoryexcelaction',  'Field\ManagementController@managementHistoryExcelAction')  ->name('현장내역 엑셀업로드액션');

Route::post('/field/managementcost',                'Field\ManagementController@managementCost')                ->name('일위대가 정보');
Route::post('/field/managementcostlist',            'Field\ManagementController@managementCostList')            ->name('일위대가 리스트');
Route::post('/field/managementcostexcel',           'Field\ManagementController@managementCostExcel')           ->name('일위대가 엑셀다운');
Route::post('/field/managementcostallclear',        'Field\ManagementController@managementCostAllClear')        ->name('일위대가 일괄삭제');
Route::post('/field/managementcostaction',          'Field\ManagementController@managementCostAction')          ->name('일위대가 저장');

Route::post('/field/managementcostform',            'Field\ManagementController@managementCostForm')            ->name('일위대가 form');
Route::post('/field/managementcostformaction',      'Field\ManagementController@managementCostFormAction')      ->name('일위대가 form 저장');
Route::post('/field/managementmaterialsearch',      'Field\ManagementController@managementMaterialSearch')      ->name('일위대가 자재단가표 찾기');
Route::get ('/field/managementcostpop',             'Field\ManagementController@managementCostPop')             ->name('일위대가 pop');
Route::post('/field/managementcostpopaction',       'Field\ManagementController@managementCostPopAction')       ->name('일위대가 pop 저장');

Route::get ('/field/managementcostexcelsample',     'Field\ManagementController@managementCostExcelSample') 	->name('일위대가 엑셀업로드샘플파일다운로드');
Route::post('/field/managementcostexcelform',       'Field\ManagementController@managementCostExcelForm')       ->name('일위대가 엑셀업로드');
Route::post('/field/managementcostexcelaction',     'Field\ManagementController@managementCostExcelAction')     ->name('일위대가 엑셀업로드액션');

Route::post('/field/managementmaterial',            'Field\ManagementController@managementMaterial')            ->name('자재관리 정보');
Route::post('/field/managementmateriallist',        'Field\ManagementController@managementMaterialList')        ->name('자재관리 리스트');
Route::post('/field/managementmaterialexcel',       'Field\ManagementController@managementMaterialExcel')       ->name('자재관리 엑셀다운');
Route::post('/field/managementmaterialallclear',    'Field\ManagementController@managementMaterialAllClear')    ->name('자재관리 일괄삭제');

Route::post('/field/managementmaterialform',        'Field\ManagementController@managementMaterialForm')        ->name('자재관리 form');
Route::post('/field/managementmaterialformaction',  'Field\ManagementController@managementMaterialFormAction')  ->name('자재관리 form 저장');

Route::get ('/field/managementmaterialexcelsample', 'Field\ManagementController@managementMaterialExcelSample')	->name('자재관리 엑셀업로드샘플파일다운로드');
Route::post('/field/managementmaterialexcelform',   'Field\ManagementController@managementMaterialExcelForm')   ->name('자재관리 엑셀업로드');
Route::post('/field/managementmaterialexcelaction', 'Field\ManagementController@managementMaterialExcelAction') ->name('자재관리 엑셀업로드액션');

?>