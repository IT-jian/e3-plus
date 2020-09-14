<?php


namespace PlatformAdaptor\Generator\Common;


use Exception;
use Illuminate\Console\Command;

class CommandData
{
    // $commandType的值，命令行数据类型，决定生成的代码模板
    public static $COMMAND_TYPE_API = 'api';
    public static $COMMAND_TYPE_SCAFFOLD = 'scaffold';

    public $modelName;
    public $modelLabel;
    public $commandType;

    // 配置
    public $config;

    // 字段
    public $fields = [];

    /**
     * @var Command
     */
    public $commandObj;

    public $dynamicVars = [];
    public $fieldNamesMapping = [];

    /**
     * @var CommandData
     */
    protected static $instance = null;

    public static function getInstance()
    {
        return self::$instance;
    }

    public function __construct(Command $commandObj, $commandType)
    {
        $this->commandObj = $commandObj;
        $this->commandType = $commandType;
        // form 表达替换
        $this->fieldNamesMapping = [
            '$FIELD_NAME_TITLE$' => 'fieldTitle',
            '$FIELD_TITLE$'      => 'fieldTitle',
            '$FIELD_NAME$'       => 'name',
            '$FIELD_SIZE$'       => 'size',
        ];

        $this->config = new GeneratorConfig();
    }

    // 输出命令行提示
    public function commandError($error)
    {
        $this->commandObj->error($error);
    }
    public function commandComment($message)
    {
        $this->commandObj->comment($message);
    }
    public function commandWarn($warning)
    {
        $this->commandObj->warn($warning);
    }
    public function commandInfo($message)
    {
        $this->commandObj->info($message);
    }

    // 初始化命令行数据--根据配置信息获取
    public function initCommandData()
    {
        $this->config->init($this);
    }

    // 操作配置方法
    public function getOption($option)
    {
        return $this->config->getOption($option);
    }

    public function getAddOn($option)
    {
        return $this->config->getAddOn($option);
    }

    public function setOption($option, $value)
    {
        $this->config->setOption($option, $value);
    }

    // 增加模板中的参数对应的值
    public function addDynamicVariable($name, $val)
    {
        $this->dynamicVars[$name] = $val;
    }

    /**
     * 仅支持导入json file 的格式
     */
    public function getFields()
    {
        $this->fields = [];
        if (!$this->getOption('fieldsFile')) {
            $this->commandError('仅支持fieldsFile格式的参数');
            exit;
        }

        $this->getInputFromFileOrJson();
    }

    private function getInputFromFileOrJson()
    {
        // fieldsFile option will get high priority than json option if both options are passed
        try {
            $fieldsFileValue = $this->getOption('fieldsFile');
            if (file_exists($fieldsFileValue)) {
                $filePath = $fieldsFileValue;
            } elseif (file_exists(base_path($fieldsFileValue))) {
                $filePath = base_path($fieldsFileValue);
            } else {
                $schemaFileDirector = config('generator.path.schema_files');
                $filePath = $schemaFileDirector.$fieldsFileValue;
            }
            if (!file_exists($filePath)) {
                $this->commandError('Fields file not found：' . $filePath);
                exit;
            }
            $fileContents = file_get_contents($filePath);
            $jsonData = json_decode($fileContents, true);
            $this->fields = [];
            // 处理字段
            foreach ($jsonData as $field) {
                $this->fields[] = GeneratorField::parseFieldFromFile($field);
            }
        } catch (Exception $e) {
            $this->commandError($e->getMessage());
            exit;
        }
    }
}