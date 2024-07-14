<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/', function () {
    return view('index');
});
Route::group(['prefix' => 'auth'], function () {
    Route::post('login',  'Auth\LoginController@login');
    Route::get ('logout', 'Auth\LoginController@logout');
});

Route::get('/swagent', function () {
    
    return view("swagger")->with("div","agent");
});
Route::get('/swhomepage', function () {
    
    return view("swagger")->with("div","homepage");
});

Route::get('/clear-cache', function () {
    Artisan::call('config:cache');
    return "CLEARCACHE";
 });

// 관리자 부서 계정은 환경설정페이지가 아닌 다른 페이지 접속 시 종합코드관리페이지로 redirect
// Route::middleware("auth.adminCheck") -> group(function() {
    # 인트라넷
    include 'web/intranet.php';

    # 회계관리
    include 'web/account.php';

    # 현장관리
    include 'web/field.php';

    # ERP관리
    include 'web/erp.php';

    # 보고서
    include 'web/report.php';

    # 환경설정
    include 'web/config.php';
// });