<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Application Name
    |--------------------------------------------------------------------------
    |
    | This value is the name of your application. This value is used when the
    | framework needs to place the application's name in a notification or
    | any other location as required by the application or its packages.
    |
    */

    'name' => env('APP_NAME', 'Laravel'),
    'comp' => env('COM_NAME', 'Laravel'),
    'sche' => env('DB_SCHEMA'),
    'company' => env('CORP_NAME'),
    
    // 차입자번호 앞에 붙여서 부여줄 영문자나 문자
    'addci' => env('ADD_CUST_INFO_NO', ''),

    // 개발부서 지정
    'dev_branch' => env('DEV_BRANCH'),

    // 금융사 주소
    'corp_name' => env('CORP_NAME'),
    'corp_name_only' => env('CORP_NAME'),
    'corp_company' => env('CORP_COMPANY_NUM1')."-".env('CORP_COMPANY_NUM2')."-".env('CORP_COMPANY_NUM3'),
    'corp_ceo_name' => env('CORP_CEO_NAME'),
    'corp_address' => env('CORP_ZIP').")".env('CORP_ADDR1').env('CORP_ADDR2'),
    'corp_address_only' => env('CORP_ADDR1').env('CORP_ADDR2'),
    'corp_addr1' => env('DB_SCHEMA'),
    'corp_addr2' => env('CORP_ADDR2'),
    'corp_post_zip' =>  env('CORP_POST_ZIP'),
    

    /*
    |--------------------------------------------------------------------------
    | Application Environment
    |--------------------------------------------------------------------------
    |
    | This value determines the "environment" your application is currently
    | running in. This may determine how you prefer to configure various
    | services the application utilizes. Set this in your ".env" file.
    |
    */

    'env' => env('APP_ENV', 'production'),

    /*
    |--------------------------------------------------------------------------
    | Application Debug Mode
    |--------------------------------------------------------------------------
    |
    | When your application is in debug mode, detailed error messages with
    | stack traces will be shown on every error that occurs within your
    | application. If disabled, a simple generic error page is shown.
    |
    */

    'debug' => (bool) env('APP_DEBUG', false),

    /*
    |--------------------------------------------------------------------------
    | Application URL
    |--------------------------------------------------------------------------
    |
    | This URL is used by the console to properly generate URLs when using
    | the Artisan command line tool. You should set this to the root of
    | your application so that it is used when running Artisan tasks.
    |
    */

    'url' => env('APP_URL', 'http://localhost'),
    'arch_url' => env('ARCH_URL'),

    'asset_url' => env('ASSET_URL', null),

    /*
    |--------------------------------------------------------------------------
    | Application Timezone
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default timezone for your application, which
    | will be used by the PHP date and date-time functions. We have gone
    | ahead and set this to a sensible default for you out of the box.
    |
    */

    'timezone' => 'Asia/Seoul',

    /*
    |--------------------------------------------------------------------------
    | Application Locale Configuration
    |--------------------------------------------------------------------------
    |
    | The application locale determines the default locale that will be used
    | by the translation service provider. You are free to set this value
    | to any of the locales which will be supported by the application.
    |
    */

    'locale' => 'ko',

    /*
    |--------------------------------------------------------------------------
    | Application Fallback Locale
    |--------------------------------------------------------------------------
    |
    | The fallback locale determines the locale to use when the current one
    | is not available. You may change the value to correspond to any of
    | the language folders that are provided through your application.
    |
    */

    'fallback_locale' => 'ko',

    /*
    |--------------------------------------------------------------------------
    | Faker Locale
    |--------------------------------------------------------------------------
    |
    | This locale will be used by the Faker PHP library when generating fake
    | data for your database seeds. For example, this will be used to get
    | localized telephone numbers, street address information and more.
    |
    */

    //'faker_locale' => 'en_US',
    'faker_locale' => 'ko_KR',

    /*
    |--------------------------------------------------------------------------
    | Encryption Key
    |--------------------------------------------------------------------------
    |
    | This key is used by the Illuminate encrypter service and should be set
    | to a random, 32 character string, otherwise these encrypted strings
    | will not be safe. Please do this before deploying an application!
    |
    */

    'key'        => env('APP_KEY'),
    'cipher'     => 'AES-256-CBC',
    'webKey'     => env('ENC_KEY_WEB'),
    'enKey'      => env('ENC_KEY_SOL'),
    'smsKey'     => env('ENC_KEY_SMS'),
    
    'nsfid'      => env('NSFID'),
    /*
    |--------------------------------------------------------------------------
    | Autoloaded Service Providers
    |--------------------------------------------------------------------------
    |
    | The service providers listed here will be automatically loaded on the
    | request to your application. Feel free to add your own services to
    | this array to grant expanded functionality to your applications.
    |
    */

    'providers' => [

        /*
         * Laravel Framework Service Providers...
         */
        Illuminate\Auth\AuthServiceProvider::class,
        Illuminate\Broadcasting\BroadcastServiceProvider::class,
        Illuminate\Bus\BusServiceProvider::class,
        Illuminate\Cache\CacheServiceProvider::class,
        Illuminate\Foundation\Providers\ConsoleSupportServiceProvider::class,
        Illuminate\Cookie\CookieServiceProvider::class,
        Illuminate\Database\DatabaseServiceProvider::class,
        Illuminate\Encryption\EncryptionServiceProvider::class,
        Illuminate\Filesystem\FilesystemServiceProvider::class,
        Illuminate\Foundation\Providers\FoundationServiceProvider::class,
        Illuminate\Hashing\HashServiceProvider::class,
        Illuminate\Mail\MailServiceProvider::class,
        Illuminate\Notifications\NotificationServiceProvider::class,
        Illuminate\Pagination\PaginationServiceProvider::class,
        Illuminate\Pipeline\PipelineServiceProvider::class,
        Illuminate\Queue\QueueServiceProvider::class,
        Illuminate\Redis\RedisServiceProvider::class,
        Illuminate\Auth\Passwords\PasswordResetServiceProvider::class,
        Illuminate\Session\SessionServiceProvider::class,
        Illuminate\Translation\TranslationServiceProvider::class,
        Illuminate\Validation\ValidationServiceProvider::class,
        Illuminate\View\ViewServiceProvider::class,

        /*
         * Package Service Providers...
         */
        Maatwebsite\Excel\ExcelServiceProvider::class, // 엑셀
        Rap2hpoutre\FastExcel\Providers\FastExcelServiceProvider::class, // 패스트엑셀
        /*
         * Application Service Providers...
         */
        App\Providers\AppServiceProvider::class,
        App\Providers\AuthServiceProvider::class,
        // App\Providers\BroadcastServiceProvider::class,
        App\Providers\EventServiceProvider::class,
        App\Providers\RouteServiceProvider::class,
        App\Providers\BroadcastServiceProvider::class,
        Barryvdh\DomPDF\ServiceProvider::class,

    ],

    /*
    |--------------------------------------------------------------------------
    | Class Aliases
    |--------------------------------------------------------------------------
    |
    | This array of class aliases will be registered when this application
    | is started. However, feel free to register as many as you wish as
    | the aliases are "lazy" loaded so they don't hinder performance.
    |
    */

    'aliases' => [

        'App'             => Illuminate\Support\Facades\App::class,
        'Arr'             => Illuminate\Support\Arr::class,
        'Artisan'         => Illuminate\Support\Facades\Artisan::class,
        'Auth'            => Illuminate\Support\Facades\Auth::class,
        'Blade'           => Illuminate\Support\Facades\Blade::class,
        'Broadcast'       => Illuminate\Support\Facades\Broadcast::class,
        'Bus'             => Illuminate\Support\Facades\Bus::class,
        'Cache'           => Illuminate\Support\Facades\Cache::class,
        'Config'          => Illuminate\Support\Facades\Config::class,
        'Cookie'          => Illuminate\Support\Facades\Cookie::class,
        'Crypt'           => Illuminate\Support\Facades\Crypt::class,
        'DB'              => Illuminate\Support\Facades\DB::class,
        'Eloquent'        => Illuminate\Database\Eloquent\Model::class,
        'Event'           => Illuminate\Support\Facades\Event::class,
        'File'            => Illuminate\Support\Facades\File::class,
        'Gate'            => Illuminate\Support\Facades\Gate::class,
        'Hash'            => Illuminate\Support\Facades\Hash::class,
        'Http'            => Illuminate\Support\Facades\Http::class,
        'Lang'            => Illuminate\Support\Facades\Lang::class,
        'Log'             => Illuminate\Support\Facades\Log::class,
        'Mail'            => Illuminate\Support\Facades\Mail::class,
        'Notification'    => Illuminate\Support\Facades\Notification::class,
        'Password'        => Illuminate\Support\Facades\Password::class,
        'Queue'           => Illuminate\Support\Facades\Queue::class,
        'Redirect'        => Illuminate\Support\Facades\Redirect::class,
        // 'Redis' => Illuminate\Support\Facades\Redis::class,
        'Request'         => Illuminate\Support\Facades\Request::class,
        'Response'        => Illuminate\Support\Facades\Response::class,
        'Route'           => Illuminate\Support\Facades\Route::class,
        'Schema'          => Illuminate\Support\Facades\Schema::class,
        'Session'         => Illuminate\Support\Facades\Session::class,
        'Storage'         => Illuminate\Support\Facades\Storage::class,
        'Str'             => Illuminate\Support\Str::class,
        'URL'             => Illuminate\Support\Facades\URL::class,
        'Validator'       => Illuminate\Support\Facades\Validator::class,
        'View'            => Illuminate\Support\Facades\View::class,
        'Carbon'            => Illuminate\Support\Carbon::class,

        'FastExcel'       => Rap2hpoutre\FastExcel\Facades\FastExcel::class,    // 패스트엑셀
        'Excel'           => Maatwebsite\Excel\Facades\Excel::class,            // 엑셀

        'Func'            => App\Chung\Func::class,              // CHUNG 사용자 정의 함수
        'Vars'            => App\Chung\Vars::class,              // 공통변수(일반 공통 변수는 코드관리를 가져다 쓰고 여기는 시스템 변수만 넣을것)
        'Loan'            => App\Chung\Loan::class,              // 계약관련 함수 클래스
        'Trade'           => App\Chung\Trade::class,             // 입출금 거래관련 함수 클래스
        'Invest'           => App\Chung\Invest::class,           // 투자관련 함수 클래스
        'ExcelFunc'       => App\Chung\ExcelFunc::class,         // 엑셀관련 함수
        'DataList'        => App\Chung\DataList::class,          // 리스트생성 함수
        'PaperPrint'      => App\Chung\PaperPrint::class,        // 문자파싱열 가져오기
        'PDF'             => Barryvdh\DomPDF\Facade::class,     // PDF 변환
        'Mig'             => App\Migration\Mig::class,          // 마이그레이션
        'Image'			  => App\Chung\Image::class,				// 이미지첨부 관련 함수 클래스
        'Decrypter'		  => App\Chung\Decrypter::class,			// 복호화 클래스
        'Ksnet'           => App\Chung\Ksnet::class,             // Ksnet 통신 관련
		'Debugbar' 	      => Barryvdh\Debugbar\Facades\Debugbar::class,
        
        'KFBCommon'          => App\Chung\KFBCommon::class,      // 신정원 공통 함수
        'KFBCommon_DL9011'   => App\Chung\KFBCommon_DL9011::class,      // 신정원 공통 함수
        'KFBCommon_QD9011'   => App\Chung\KFBCommon_QD9011::class,      // 신정원 공통 함수
        'KFBCommon_QS9011'   => App\Chung\KFBCommon_QS9011::class,      // 신정원 공통 함수
        'KFBCommon_DG9011'   => App\Chung\KFBCommon_DG9011::class,      // 신정원 공통 함수
        'KFBCommon_DG9033'   => App\Chung\KFBCommon_DG9033::class,      // 신정원 공통 함수
        'KFBCommon_QG9011'   => App\Chung\KFBCommon_QG9011::class,      // 신정원 공통 함수
        'KFBCommon_BF9011'   => App\Chung\KFBCommon_BF9011::class,      // 신정원 공통 함수
        'KFBCommon_LN9033'   => App\Chung\KFBCommon_LN9033::class,      // 신정원 공통 함수
        'KFBCommon_BF9044'   => App\Chung\KFBCommon_BF9044::class,      // 신정원 공통 함수
    ],

];
