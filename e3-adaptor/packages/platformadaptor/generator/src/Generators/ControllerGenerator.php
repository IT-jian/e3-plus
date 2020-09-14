<?php


namespace PlatformAdaptor\Generator\Generators;


use Illuminate\Support\Str;
use PlatformAdaptor\Generator\Common\CommandData;
use PlatformAdaptor\Generator\Generators\BaseGenerator;
use PlatformAdaptor\Generator\Utils\FileUtil;

/**
 * Class ControllerGenerator
 * @package PlatformAdaptor\Generator\Generators\Api
 */
class ControllerGenerator extends BaseGenerator
{
    /**
     * @var CommandData
     */
    private $commandData;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $fileName;

    /**
     * ControllerGenerator constructor.
     * @param CommandData $commandData
     */
    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathController;
        $this->fileName = $this->commandData->modelName . 'Controller.php';
    }

    public function generate()
    {
        $templateData = get_template('controller.controller');
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);
        $templateData = $this->fillDocs($templateData);
        $templateData = $this->fillWhere($templateData);
        FileUtil::createFile($this->path, $this->fileName, $templateData);
        $this->commandData->commandComment("\nController created: ");
        // 填充权限数据
        if (!$this->isSkip('permissions')) {
            $this->insertPermissions();
            $this->commandData->commandComment("\nPermissions Data inserted: ");
        }
        $this->commandData->commandInfo($this->fileName);
    }

    public function isSkip($skip)
    {
        if ($this->commandData->getOption('skip')) {
            return in_array($skip, (array) $this->commandData->getOption('skip'));
        }
        return false;
    }

    /**
     * 填充文档
     *
     * @param $templateData
     * @return mixed
     */
    private function fillDocs($templateData)
    {
        $methods = ['controller', 'store', 'show', 'update', 'destroy', 'index'];

        if ($this->commandData->getAddOn('swagger')) {
            $templatePrefix = 'controller_docs';
            $templateType = 'swagger-generator';
        } else {
            $templatePrefix = 'docs.controller';
            $templateType = 'laravel-vue-template';
        }

        foreach ($methods as $method) {
            $key = '$DOC_' . strtoupper($method) . '$';
            $docTemplate = get_template($templatePrefix . '.' . $method, $templateType);
            $docTemplate = fill_template($this->commandData->dynamicVars, $docTemplate);
            $templateData = str_replace($key, $docTemplate, $templateData);
        }

        return $templateData;
    }

    private function fillWhere($templateData)
    {
        $whereArr = [];
        foreach ($this->commandData->fields as $field) {
            if (!$field->inSearch) {
                continue;
            }
            $whereTemplate = get_template('controller.where');
            $whereTemplate = str_replace('$FIELD_NAME$', $field->name, $whereTemplate);
            $whereTemplate = str_replace('$FIELD_NAME_CAMEL$', Str::camel($field->name), $whereTemplate);
            $whereArr[] = $whereTemplate;
        }

        return str_replace('$WHERE$', implode("\n\n", $whereArr), $templateData);
    }

    /**
     * 插入权限代码
     */
    public function insertPermissions()
    {
        $permissions = ['view' => '查看', 'add' => '新增', 'edit' => '修改', 'delete' => '删除'];
        $permissionModel = new \Spatie\Permission\Models\Permission();
        $snakeName = $this->commandData->config->mSnake;
        $label = $this->commandData->modelLabel;
        $parent = $permissionModel::create(['name' => $snakeName . '_manage', 'desc' => $label . ' 管理', 'parent_id' => 1]);
        foreach ($permissions as $code => $text) {
            $permissionModel::create(['name' => $code . '_' . $snakeName, 'desc' => $text . ' ' . $label, 'parent_id' => $parent->id]);
        }
    }

    /**
     * 删除权限代码
     */
    public function deletePermissions()
    {
        $permissions = ['view' => '查看', 'add' => '新增', 'edit' => '修改', 'delete' => '删除'];
        $permissionModel = new \Spatie\Permission\Models\Permission();
        $snakeName = $this->commandData->config->mSnake;
        $permissionNames = [$snakeName . '_manage'];
        foreach ($permissions as $code => $text) {
            $permissionNames[] = $code . '_' . $snakeName;
        }
        $permissionModel::whereIn('name', $permissionNames)->delete();
    }

    /**
     * 回滚
     */
    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->deletePermissions();
            $this->commandData->commandComment("\nPermissions Data deleted: ");
            $this->commandData->commandComment('Controller file deleted: '.$this->fileName);
        }
    }
}