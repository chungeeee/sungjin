<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Default Filesystem Disk
    |--------------------------------------------------------------------------
    |
    | Here you may specify the default filesystem disk that should be used
    | by the framework. The "local" disk, as well as a variety of cloud
    | based disks are available to your application. Just store away!
    |
    */

    'default' => env('FILESYSTEM_DRIVER', 'local'),

    /*
    |--------------------------------------------------------------------------
    | Filesystem Disks
    |--------------------------------------------------------------------------
    |
    | Here you may configure as many filesystem "disks" as you wish, and you
    | may even configure multiple disks of the same driver. Defaults have
    | been setup for each driver as an example of the required options.
    |
    | Supported Drivers: "local", "ftp", "sftp", "s3"
    |
    */

    'disks' => [
        'local' => [
            'driver' => 'local',
            'root' => storage_path('app'),
        ],

        'public' => [
            'driver' => 'local',
            'root' => storage_path('app/public'),
            'url' => env('APP_URL').'/storage',
            'visibility' => 'public',
        ],
      /*   'board' => [
            'driver' => 'local',
            'root' => env('BOARD_PATH'),
			'url' => env('BOARD_PATH'),
        ], */
        'board' => [
            'driver' => 'local',
            'root' => storage_path('app/public/board'),
            'url' => env('APP_URL').'/storage',
        ],
        's3' => [
            'driver' => 's3',
            'key' => env('AWS_ACCESS_KEY_ID'),
            'secret' => env('AWS_SECRET_ACCESS_KEY'),
            'region' => env('AWS_DEFAULT_REGION'),
            'bucket' => env('AWS_BUCKET'),
            'url' => env('AWS_URL'),
            'endpoint' => env('AWS_ENDPOINT'),
        ],

        'kfb' => [
            'driver' => 'local',
            'root' => storage_path('app/kfb'),
        ],

        'management' => [
            'driver' => 'local',
            'root' => storage_path('app/management'),
        ],

        'lumplog' => [
            'driver' => 'local',
            'root' => storage_path('app/lumplog'),
        ],

        'lumplog_U' => [
            'driver' => 'local',
            'root' => storage_path('app/lumplog/upload'),
            
        ],

        'nice_cb' => [
            'driver' => 'local',
            'root' => storage_path('app/nice_cb'),
        ],

        'nice_record' => [
            'driver' => 'local',
            'root' => storage_path('app/nicerecord'),
        ],
        
        // 2021.12.21 yjlee 신규 추가
        'nice_mydata' => [
            'driver' => 'local',
            'root' => storage_path('app/nice_mydata'),
        ],

        'nice_ftp' => [
            'driver' => 'ftp',
            'host' => env('NICE_FTP_IP'),
            'port' => 21,
            'username' => env('NICE_FTP_ID'),
            'password' => env('NICE_FTP_PW'),
            // 'passive' => false,
            // 'ignorePassiveAddress' => true,
            // 'timeout' => 30,
            // 'root' => env('NICE_SFTP_ROOT')
        ],
        'nice_ftp_dev' => [
            'driver' => 'ftp',
            'host' => env('NICE_FTP_DEV_IP'),
            'port' => 21,
            'username' => env('NICE_FTP_DEV_ID'),
            'password' => env('NICE_FTP_DEV_PW'),
            // 'passive' => false,
            // 'ignorePassiveAddress' => true,
            // 'timeout' => 30,
            // 'root' => env('NICE_SFTP_ROOT')
        ],
        'fax' => [
            'driver' => 'local',
            'root' => env('FAX_FILE_DIR'),
            'visibility' => 'public',
        ],
        'law' => [
            'driver' => 'local',
            'root' => storage_path('app/law'),
            'visibility' => 'public',
        ],
        'nsic' => [
            'driver' => 'local',
            'root' => storage_path('app/nsic'),
            'visibility' => 'public',
        ],
        'agent' => [
            'driver' => 'local',
            'root' => storage_path('app/agent'),
            'visibility' => 'public',
        ],
        'excel' => [
            'driver' => 'local',
            'root' => storage_path('app/excel'),
            'visibility' => 'public',
        ],
        'ups_data_img' => [
            'driver' => 'local',
            'root' => storage_path('app/UPS/data_img'),
            'visibility' => 'public',
        ],
        'ups_data_wav' => [
            'driver' => 'local',
            'root' => storage_path('app/UPS/data_wav'),
            'visibility' => 'public',
        ],
        'erp_data_img' => [
            'driver' => 'local',
            'root' => storage_path('app/ERP/data_img'),
            'visibility' => 'public',
        ],
        'erp_data_wav' => [
            'driver' => 'local',
            'root' => storage_path('app/ERP/data_wav'),
            'visibility' => 'public',
        ],
        'erp_data_usr_img' => [
            'driver' => 'local',
            'root' => storage_path('app/ERP/erp_data_usr_img'),
            'visibility' => 'public',
        ],
        'cms' => [
            'driver' => 'local',
            'root' => env('CMS_FILE_DIR'),
            'visibility' => 'public',
        ],
        'fax_image' => [
            'driver' => 'local',
            'root' => storage_path('app/fax_image'),
            'visibility' => 'public',
        ],
        'file_test' => [
            'driver' => 'local',
            'root' => 'config/file',
            'visibility' => 'public',
        ],

        'kfb' => [
            'driver' => 'local',
            'root' => storage_path('app/kfb'),
        ],
        'mig_data_img' => [
            'driver' => 'sftp',
            'host' => env('MIG_DATA_IP'),
            'port' => 22,
            'username' => env('MIG_DATA_ID'),
            'password' => env('MIG_DATA_PW'),
            'root' => env('MIG_DATA_DIV').'data_img'
        ],
        'mig_data_wav' => [
            'driver' => 'sftp',
            'host' => env('MIG_DATA_IP'),
            'port' => 22,
            'username' => env('MIG_DATA_ID'),
            'password' => env('MIG_DATA_PW'),
            'root' => env('MIG_DATA_DIV').'data_wav'
        ],
        'new_data_img' => [
            'driver' => 'sftp',
            'host' => env('NEW_DATA_IP'),
            'port' => 22,
            'username' => env('NEW_DATA_ID'),
            'password' => env('NEW_DATA_PW'),
            'root' => env('NEW_DATA_DIV').'data_img'
        ],
        'new_data_wav' => [
            'driver' => 'sftp',
            'host' => env('NEW_DATA_IP'),
            'port' => 22,
            'username' => env('NEW_DATA_ID'),
            'password' => env('NEW_DATA_PW'),
            'root' => env('NEW_DATA_DIV').'data_wav'
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Symbolic Links
    |--------------------------------------------------------------------------
    |
    | Here you may configure the symbolic links that will be created when the
    | `storage:link` Artisan command is executed. The array keys should be
    | the locations of the links and the values should be their targets.
    |
    */

    'links' => [
        public_path('storage') => storage_path('app/public'),
    ],

];
