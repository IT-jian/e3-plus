<?php

return [
    'path' => [
        'model' => base_path('app/Models/'),
        'migration' => base_path('database/migrations/'),
        'controller' => base_path('app/Http/Controllers/'),
        'routes' => base_path('routes/api_routes.php'),
        'test' => base_path('test/'),
        'schema_files' => base_path('resources/generator_schemas/'),
        // 视图文件位置
        'views' => base_path('resources/assets/js/views/'),
        // vue 文件位置
        'vue' => base_path('resources/assets/js/views/'),
        // 可以自定义模板文件的存放位置，来覆盖默认的模板  base_path('resources/generator-templates/') @TODO 更改路径
        'templates_dir' => base_path('packages/platformadaptor/generator/templates/'),
    ],
    'namespace' =>[
        'model' => 'App\Models',
        'controller' => 'App\Http\Controllers',
    ],
    // 'model_extend_class' => 'Illuminate\Database\Eloquent\Model',


    /*
    |--------------------------------------------------------------------------
    | API routes prefix & version
    |--------------------------------------------------------------------------
    |
    */
    'api_prefix'  => 'api',
    'api_version' => 'v1',


    'options' => [
        'softDelete' => false
    ],
    'prefixes' => [
        'route' => '',
        'path' => 'admin',
        'view' => '',
        'public' => '',
    ],
    'timestamps' => [
        'enabled' => true
    ],
    'addOns' => [
        'swagger' => false,
        'tests' => false
    ],
    // 默认使用 主题模板
    'scaffold_templates' => 'element-ui-templates',
    // true，model 不受 prefix 前缀影响，默认为App/Models
    'ignore_model_prefix' => true,
];