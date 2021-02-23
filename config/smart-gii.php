<?php

return [
    'domain' => env('SMART_GII_DOMAIN', null),

    //是否启用gii
    'enabled' => env('SMART_GII_ENABLED', true),

    //gii路由前缀。提高gii路由的可辨识性，降低与其他路由重合的可能性。
    'prefix' => env('SMART_GII_ROUTE_PREFIX', 'smart-gii'),

    //后缀
    'suffix' => [
        //生成类文件的时候为类文件提供一个命名建议
        'class' => [
            'controller' => env('SMART_GII_CONTROLLER_SUFFIX', 'Controller'),
            'resource' => env('SMART_GII_RESOURCE_SUFFIX', 'Resource'),
            'formRequest' => env('SMART_GII_REQUEST_FORM_SUFFIX', 'Request'),
            'model' => env('SMART_GII_MODEL_SUFFIX', ''),
            'repository' => env('SMART_GII_MODEL_SUFFIX', 'Biz'),
        ],
    ],

    'model_path'=>[
        app_path().'/Models',
        app_path().'/Components/Migrate/Models',
    ],

    'middleware' => [
        'web',
    ],
];
