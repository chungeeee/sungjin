<?php

// 연체현황표 (요약)
Route::get  ('/report/delayloansummary',              'Report\DelayLoanController@delayLoanSummary')      ->name('연체현황표(요약) 메인');
Route::get  ('/report/delayloansummarylist',          'Report\DelayLoanController@delayLoanSummaryList')   ->name('연체현황표(요약) 리스트');
Route::post ('/report/delayloansummaryexcel',         'Report\DelayLoanController@delayLoanSummaryExcel')  ->name('연체현황표(요약) 엑셀');
Route::get('/report/delayloansummarybatch/{infoDate?}', function ($infoDate = null) {
            $infoDate = str_replace("-","",$infoDate);
            \Artisan::call('UpdateReport:DelayLoanSummary 39 '.$infoDate);
            echo $infoDate;
        })->name('연체현황표(요약) - 배치파일 호출');

// 연체현황표 (사유)
Route::get  ('/report/delayloanreason',              'Report\DelayLoanController@delayLoanReason')         ->name('연체현황표(사유) 메인');
Route::get  ('/report/delayloanreasonlist',          'Report\DelayLoanController@delayLoanReasonList')     ->name('연체현황표(사유) 리스트');
Route::post ('/report/delayloanreasonexcel',         'Report\DelayLoanController@delayLoanReasonExcel')    ->name('연체현황표(사유) 엑셀');
Route::post ('/report/delayloanreasonpop',           'Report\DelayLoanController@delayLoanReasonPop')     ->name('연체현황표(사유) 계약리스트 팝업');
Route::post ('/report/delayloanreasonpoplist',       'Report\DelayLoanController@delayLoanReasonPopList') ->name('연체현황표(사유) 계약리스트');

Route::get('/report/delayloanreasonbatch/{infoDate?}', function ($infoDate = null) {
            $infoDate = str_replace("-","",$infoDate);
            \Artisan::call('UpdateReport:DelayLoanReason 40 '.$infoDate);
            echo $infoDate;
        })->name('연체현황표(사유) - 배치파일 호출');


// 영업일보
Route::get  ('/report/dailyloan',              'Report\DailyLoanController@dailyLoan')         ->name('영업일보 메인');
Route::get  ('/report/dailyloanlist',          'Report\DailyLoanController@dailyLoanList')     ->name('영업일보 리스트');
Route::post ('/report/dailyloanexcel',         'Report\DailyLoanController@dailyLoanExcel')    ->name('영업일보 엑셀');
Route::get  ('/report/dailyloanbatch/{infoDate?}', function ($infoDate = null) {
    $infoDate = str_replace("-","",$infoDate);
    \Artisan::call('UpdateReport:DailyLoan 49 '.$infoDate);
    echo $infoDate;
})->name('영업일보 - 배치파일 호출');



// 채권 추심 횟수
Route::get ('/report/bondcollection',         'Report\BondController@bondCollection')         ->name('채권추심횟수 메인');
Route::post ('/report/bondcollectionlist',    'Report\BondController@bondCollectionList')     ->name('채권추심횟수 리스트');


?>