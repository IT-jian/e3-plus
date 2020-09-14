<?php


namespace PlatformAdaptor\Generator\Common;


use Illuminate\Support\Str;
use PlatformAdaptor\Generator\Common\CommandData;

/**
 * 配置文件类
 * Class GeneratorConfig
 * @package PlatformAdaptor\Generator\Common
 */
class GeneratorConfig
{
    /* 命令空间变量 */
    public $nsApp;
    public $nsModel;
    public $nsModelExtend;

    public $nsController;
    public $nsBaseController;
    public $nsRequest;
    public $nsTransformer;

    /**
     * 路径变量
     */
    public $pathModel;
    public $pathViews;
    public $pathVueViews;

    public $pathController;
    public $pathRoutes;
    public $pathTests;

    /**
     * 各种model的名称
     */
    public $mName;
    public $mLabel; // 中文名称，没有则取 $mName
    public $mPlural;  // 复数形式
    public $mCamel;  // 驼峰
    public $mCamelPlural;
    public $mSnake;  // 下划线
    public $mSnakePlural;
    public $mDashed; // 破折号
    public $mDashedPlural;
    public $mSlash;  //斜杠
    public $mSlashPlural;
    public $mHuman;  // 可读
    public $mHumanPlural;

    public $mSnakeUpperCase; // 大写

    public $forceMigrate;

    public $options;

    public $prefixes;

    public $tableName;
    /** @var string */
    protected $primaryName;

    public static $availableOptions = [
        'fieldsFile', // 生成的文件
        'prefix', // 前缀
        'skip' // 跳过的操作
    ];

    // 格外的组件，比如 Swagger
    public $addOns;

    /**
     * 初始化命令行数据的配置信息
     * @param CommandData $commandData
     * @param null $options
     */
    public function init(CommandData &$commandData, $options = null)
    {
        if (!empty($options)) {
            self::$availableOptions = $options;
        }

        $this->mName = $commandData->modelName;
        // 中文名称
        $this->mLabel = $commandData->modelLabel ? $commandData->modelLabel : $this->mName;

        $this->prepareAddOns();
        $this->prepareOptions($commandData);
        $this->prepareModelNames();
        $this->preparePrefixes();
        $this->loadPaths();
        $this->prepareTableName();
        $this->preparePrimaryName();
        $this->loadNamespaces($commandData);
        // 加载需要动态替换的参数 key-value 对应值
        $this->loadDynamicVariables($commandData);
    }

    /**
     * 可用扩展插件: swagger, tests
     */
    public function prepareAddOns()
    {
        $this->addOns['swagger'] = config('generator.addOns.swagger', false);
        $this->addOns['tests'] = config('generator.addOns.tests', false);
        $this->addOns['laravel-permission'] = config('generator.addOns.laravel-permission', false);
    }

    public function prepareOptions(CommandData &$commandData)
    {
        // 从命令行获取命令参数
        foreach (self::$availableOptions as $option) {
            $this->options[$option] = $commandData->commandObj->option($option);
        }
        // 从配置获取是否软删除
        $this->options['softDelete'] = config('generator.options.softDelete', false);
        if (!empty($this->options['skip'])) {
            $this->options['skip'] = array_map('trim', explode(',', $this->options['skip']));
        }
    }

    public function prepareModelNames()
    {
        $this->mPlural = Str::plural($this->mName);
        $this->mCamel = Str::camel($this->mName);
        $this->mCamelPlural = Str::camel($this->mPlural);
        $this->mSnake = Str::snake($this->mName);
        $this->mSnakePlural = Str::snake($this->mPlural);
        $this->mDashed = str_replace('_', '-', Str::snake($this->mSnake));
        $this->mDashedPlural = str_replace('_', '-', Str::snake($this->mSnakePlural));
        $this->mSlash = str_replace('_', '/', Str::snake($this->mSnake));
        $this->mSlashPlural = str_replace('_', '/', Str::snake($this->mSnakePlural));
        $this->mHuman = Str::title(str_replace('_', ' ', Str::snake($this->mSnake)));
        $this->mHumanPlural = Str::title(str_replace('_', ' ', Str::snake($this->mSnakePlural)));

        // 下划线大写，vue url 变量名
        $this->mSnakeUpperCase = Str::upper($this->mSnake);

    }

    public function preparePrefixes()
    {
        // 设置部分前缀，比如路由，视图等不同模块下对应不同目录
        $this->prefixes['route'] = explode('/', config('generator.prefixes.route', ''));
        $this->prefixes['path'] = explode('/', config('generator.prefixes.path', ''));
        $this->prefixes['view'] = explode('.', config('generator.prefixes.view', ''));
        $this->prefixes['public'] = explode('/', config('generator.prefixes.public', ''));
        if ($this->getOption('prefix')) {
            $multiplePrefixes = explode(',', $this->getOption('prefix'));
            $this->prefixes['route'] = array_merge($this->prefixes['route'], $multiplePrefixes);
            $this->prefixes['path'] = array_merge($this->prefixes['path'], $multiplePrefixes);
            $this->prefixes['view'] = array_merge($this->prefixes['view'], $multiplePrefixes);
            $this->prefixes['public'] = array_merge($this->prefixes['public'], $multiplePrefixes);
        }
        $this->prefixes['route'] = array_diff($this->prefixes['route'], ['']);
        $this->prefixes['path'] = array_diff($this->prefixes['path'], ['']);
        $this->prefixes['view'] = array_diff($this->prefixes['view'], ['']);
        $this->prefixes['public'] = array_diff($this->prefixes['public'], ['']);
        $routePrefix = '';
        foreach ($this->prefixes['route'] as $singlePrefix) {
            $routePrefix .= Str::camel($singlePrefix) . '.';
        }
        if (!empty($routePrefix)) {
            $routePrefix = substr($routePrefix, 0, strlen($routePrefix) - 1);
        }
        $this->prefixes['route'] = $routePrefix;
        $nsPrefix = '';
        foreach ($this->prefixes['path'] as $singlePrefix) {
            $nsPrefix .= Str::title($singlePrefix) . '\\';
        }
        if (!empty($nsPrefix)) {
            $nsPrefix = substr($nsPrefix, 0, strlen($nsPrefix) - 1);
        }
        $this->prefixes['ns'] = $nsPrefix;
        $pathPrefix = '';
        foreach ($this->prefixes['path'] as $singlePrefix) {
            $pathPrefix .= Str::title($singlePrefix) . '/';
        }
        if (!empty($pathPrefix)) {
            $pathPrefix = substr($pathPrefix, 0, strlen($pathPrefix) - 1);
        }
        $this->prefixes['path'] = $pathPrefix;
        $viewPrefix = '';
        foreach ($this->prefixes['view'] as $singlePrefix) {
            $viewPrefix .= Str::camel($singlePrefix) . '/';
        }
        if (!empty($viewPrefix)) {
            $viewPrefix = substr($viewPrefix, 0, strlen($viewPrefix) - 1);
        }
        $this->prefixes['view'] = $viewPrefix;
        $publicPrefix = '';
        foreach ($this->prefixes['public'] as $singlePrefix) {
            $publicPrefix .= Str::camel($singlePrefix) . '/';
        }
        if (!empty($publicPrefix)) {
            $publicPrefix = substr($publicPrefix, 0, strlen($publicPrefix) - 1);
        }
        $this->prefixes['public'] = $publicPrefix;
    }

    /**
     * 加载文件路径
     */
    public function loadPaths()
    {
        $prefix = $this->prefixes['path'];

        if (!empty($prefix)) {
            $prefix .= '/';
        }

        $viewPrefix = $this->prefixes['view'];

        if (!empty($viewPrefix)) {
            $viewPrefix .= '/';
        }

        $this->pathModel = config('generator.path.model', base_path('app/Models/')) . $prefix;
        if (config('generator.ignore_model_prefix', false)) {
            $this->pathModel = config('generator.path.model', base_path('app/Models/'));
        }

        $this->pathController = config('generator.path.controller', base_path('Http/Controllers/')) . $prefix;
        $this->pathRoutes = config('generator.path.routes', base_path('routes/web.php'));
        $this->pathTests = config('generator.path.test', base_path('tests/'));

        // 视图路径
        $this->pathViews = config('generator.path.views', base_path('resources/views/')) . $viewPrefix . $this->mSnake . '/';
        // vue 文件路径
        $this->pathVueViews = config('generator.path.vue_views', base_path('../web/src/views/')) . $viewPrefix . $this->mSnake . '/';
    }

    public function prepareTableName()
    {
        if ($this->getOption('tableName')) {
            $this->tableName = $this->getOption('tableName');
        } else {
            $this->tableName = $this->mSnakePlural;
        }
    }

    public function preparePrimaryName()
    {
        if ($this->getOption('primary')) {
            $this->primaryName = $this->getOption('primary');
        } else {
            $this->primaryName = 'id';
        }
    }

    public function loadNamespaces(CommandData $commandData)
    {
        $prefix = $this->prefixes['ns'];
        if (!empty($prefix)) {
            $prefix = '\\' . $prefix;
        }

        $this->nsApp = $commandData->commandObj->getLaravel()->getNamespace();
        $this->nsApp = substr($this->nsApp, 0, strlen($this->nsApp) - 1);
        $this->nsModel = config('generator.namespace.model', 'App\Models') . $prefix;
        if (config('generator.ignore_model_prefix', false)) {
            $this->nsModel = config('generator.namespace.model', 'App\Models');
        }
        $this->nsModelExtend = config(
            'generator.model_extend_class',
            'Illuminate\Database\Eloquent\Model'
        );
        $this->nsBaseController = config('generator.namespace.controller', 'App\Http\Controllers');
        $this->nsController = config('generator.namespace.controller', 'App\Http\Controllers') . $prefix;
    }

    public function loadDynamicVariables(CommandData $commandData)
    {
        $commandData->addDynamicVariable('$NAMESPACE_APP$', $this->nsApp);
        $commandData->addDynamicVariable('$NAMESPACE_MODEL$', $this->nsModel);
        $commandData->addDynamicVariable('$NAMESPACE_MODEL_EXTEND$', $this->nsModelExtend);

        $commandData->addDynamicVariable('$NAMESPACE_BASE_CONTROLLER$', $this->nsBaseController);
        $commandData->addDynamicVariable('$NAMESPACE_CONTROLLER$', $this->nsController);

        $commandData->addDynamicVariable('$TABLE_NAME$', $this->tableName);
        $commandData->addDynamicVariable('$PRIMARY_KEY_NAME$', $this->primaryName);

        $commandData->addDynamicVariable('$MODEL_NAME$', $this->mName);
        $commandData->addDynamicVariable('$MODEL_LABEL$', $this->mLabel);
        $commandData->addDynamicVariable('$MODEL_NAME_CAMEL$', $this->mCamel);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL$', $this->mPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_CAMEL$', $this->mCamelPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_SNAKE$', $this->mSnake);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_SNAKE$', $this->mSnakePlural);
        $commandData->addDynamicVariable('$MODEL_NAME_DASHED$', $this->mDashed);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_DASHED$', $this->mDashedPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_SLASH$', $this->mSlash);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_SLASH$', $this->mSlashPlural);
        $commandData->addDynamicVariable('$MODEL_NAME_HUMAN$', $this->mHuman);
        $commandData->addDynamicVariable('$MODEL_NAME_PLURAL_HUMAN$', $this->mHumanPlural);
        if (!empty($this->prefixes['route'])) {
            $commandData->addDynamicVariable('$ROUTE_NAMED_PREFIX$', $this->prefixes['route'] . '.');
            $commandData->addDynamicVariable('$ROUTE_PREFIX$', str_replace('.', '/', $this->prefixes['route']) . '/');
        } else {
            $commandData->addDynamicVariable('$ROUTE_PREFIX$', '');
            $commandData->addDynamicVariable('$ROUTE_NAMED_PREFIX$', '');
        }
        if (!empty($this->prefixes['ns'])) {
            $commandData->addDynamicVariable('$PATH_PREFIX$', $this->prefixes['ns'] . '\\');
        } else {
            $commandData->addDynamicVariable('$PATH_PREFIX$', '');
        }
        if (!empty($this->prefixes['view'])) {
            $commandData->addDynamicVariable('$VIEW_PREFIX$', str_replace('/', '.', $this->prefixes['view']) . '.');
        } else {
            $commandData->addDynamicVariable('$VIEW_PREFIX$', '');
        }
        if (!empty($this->prefixes['public'])) {
            $commandData->addDynamicVariable('$PUBLIC_PREFIX$', $this->prefixes['public']);
        } else {
            $commandData->addDynamicVariable('$PUBLIC_PREFIX$', '');
        }

        $commandData->addDynamicVariable(
            '$API_PREFIX$',
            config('generator.api_prefix', 'api')
        );
        $commandData->addDynamicVariable(
            '$API_VERSION$',
            config('generator.api_version', 'v1')
        );

        $commandData->addDynamicVariable('$MODEL_NAME_SNAKE_UPPER_CASE$', $this->mSnakeUpperCase);

        return $commandData;
    }

    public function getOption($option)
    {
        if (isset($this->options[$option])) {
            return $this->options[$option];
        }

        return false;
    }

    public function getAddOn($addOn)
    {
        if (isset($this->addOns[$addOn])) {
            return $this->addOns[$addOn];
        }

        return false;
    }

    public function setOption($option, $value)
    {
        $this->options[$option] = $value;
    }
}