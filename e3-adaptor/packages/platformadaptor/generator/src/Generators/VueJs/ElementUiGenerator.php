<?php


namespace PlatformAdaptor\Generator\Generators\VueJs;

use Illuminate\Support\Str;
use PlatformAdaptor\Generator\Common\CommandData;
use PlatformAdaptor\Generator\Generators\BaseGenerator;
use PlatformAdaptor\Generator\Utils\FileUtil;

class ElementUiGenerator extends BaseGenerator
{
    /**
     * @var CommandData
     */
    private $commandData;

    private $path;
    private $fileName;

    private $templateType;

    private $htmlFields;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathVueViews;

        $this->fileName = 'index.vue';

        $this->templateType = 'generator';
    }

    public function generate()
    {
        if (!file_exists($this->path)) {
            mkdir($this->path, 0755, true);
        }
        $this->commandData->commandComment("\nGenerating vue index.php...");
        $this->generateI18n();
        $this->generateSearchTable();
        $this->generateCommonItems();
        $this->commandData->commandComment('vue created: ');
    }

    private function generateSearchTable()
    {
        $templateData = get_template($this->getVueTemplateName('table.index'), $this->templateType);
        $templateData = $this->fillTable($templateData);
        $templateData = $this->fillForm($templateData);
        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandInfo('index.vue created');
    }

    /**
     * 国际化
     * @author linqihai
     * @since 2020/2/18 18:04
     */
    private function generateI18n()
    {
        $templateData = get_template($this->getVueTemplateName('table.local'), $this->templateType);
        $i18n = $this->getVueI18n();

        $templateData = str_replace('$I18N_ZH$', $i18n['zh'], $templateData);
        $templateData = str_replace('$I18N_EN$', $i18n['en'], $templateData);
        $templateData = str_replace('$CAMEL_NAME$', $this->commandData->config->mCamel, $templateData);

        $templateData = $this->fillTable($templateData);
        $templateData = $this->fillForm($templateData);
        FileUtil::createFile($this->path, 'local.js', $templateData);

        $this->commandData->commandInfo('local.js created');
    }

    private function fillTable($templateData)
    {
        // 替换 name
        $templateData = str_replace('$MODEL_NAME$', $this->commandData->config->mName, $templateData);
        // 替换 table 请求的内容
        $templateData = str_replace('$MODEL_NAME_SNAKE_UPPER_CASE$', $this->commandData->config->mSnakeUpperCase, $templateData);
        // 主键字段
        $templateData = str_replace('$PRIMARY_KEY$', $this->getPrimaryKey(), $templateData);
        // 搜索表单字段 -- 关联 type
        $templateData = str_replace('$SEARCH_FIELDS$', $this->getSearchFormField(), $templateData);
        // 列表字段
        $templateData = str_replace('$TABLE_COLUMNS$', $this->getTableColumns(), $templateData);
        // 替换权限名称
        $templateData = str_replace('$SNAKE_NAME$', $this->commandData->config->mSnake, $templateData);
        $templateData = str_replace('$CAMEL_NAME$', $this->commandData->config->mCamel, $templateData);

        return $templateData;
    }

    private function fillForm($templateData)
    {
        // 弹窗 form 表单字段
        $templateData = str_replace('$FORM_FIELDS$', $this->getFormField(), $templateData);
        // 初始数据
        $templateData = str_replace('$FORM_INIT$', $this->getInitFormData(), $templateData);

        return $templateData;
    }

    private function getRequestUrl()
    {
        return DIRECTORY_SEPARATOR . strtolower($this->commandData->config->prefixes['path']) .
            DIRECTORY_SEPARATOR . $this->commandData->config->mSnake;
    }

    private function getPrimaryKey()
    {
        foreach ($this->commandData->fields as $field) {
            if ($field->isPrimary) {
                return $field->name;
            }
        }

        return 'id';
    }

    private function getFormField()
    {
        $this->htmlFields = [];
        foreach ($this->commandData->fields as $field) {
            if (!$field->inForm) {
                continue;
            }
            switch ($field->htmlType) {
                case 'text':
                case 'textarea':
                case 'date':
                case 'datetime':
                case 'password':
                case 'number':
                    $fieldTemplate = get_template($this->getVueTemplateName('fields.' . $field->htmlType), $this->templateType);
                    break;
                case 'select':
                case 'enum':
                    $fieldTemplate = get_template($this->getVueTemplateName('fields.select'), $this->templateType);
                    //$inputsArr = explode(',', $field->htmlValues);
                    // @TODO 替换选项值
                    break;
                case 'radio':
                    $fieldTemplate = get_template($this->getVueTemplateName('fields.radio'), $this->templateType);
                    //$inputsArr = explode(',', $field->htmlValues);
                    // @TODO 替换选项值
                    break;
                case 'checkbox':
                    $fieldTemplate = get_template($this->getVueTemplateName('fields.checkbox'), $this->templateType);
                    //$inputsArr = explode(',', $field->htmlValues);
                    // @TODO 替换选项值
                    break;
                default:
                    $fieldTemplate = get_template($this->getVueTemplateName('fields.text'), $this->templateType);
                    break;
            }
            //组合form-item 和 field
            $formItemTemplate = get_template($this->getVueTemplateName('form.form_item'), $this->templateType);
            $fieldTemplate = str_replace('$FORM_ITEM_CONTENT$', $fieldTemplate, $formItemTemplate);
            // 处理rules
            $validationRules = $validationErrors = '';
            if (isset($field->validations) && !empty($field->validations)) {
                $validationRules = ' v-validate="\'' . $field->validations . '\'" :data-vv-as="translate(\'$FIELD_NAME$\')" ';
                $validationErrors = ' :error="errors.first(\'$FIELD_NAME$\')"';
            }
            $fieldTemplate = str_replace('$VALIDATION_RULES$', $validationRules, $fieldTemplate);
            $fieldTemplate = str_replace('$VALIDATION_ERRORS$', $validationErrors, $fieldTemplate);

            // 填充字段 name 和 title
            $fieldTemplate = fill_field_template(
                $this->commandData->fieldNamesMapping,
                $fieldTemplate,
                $field
            );

            $this->htmlFields[] = $fieldTemplate;
        }

        return implode(adaptor_nl(1), $this->htmlFields);
    }

    private function getInitFormData()
    {
        $str = '';
        foreach ($this->commandData->fields as $field) {
            if (!$field->inForm) {
                continue;
            }
            switch ($field->htmlType) {
                case 'checkbox': // 复选框
                    $htmlTypeString = "[]";
                    break;
                default:
                    $htmlTypeString = "''";
            }
            $str .= adaptor_nl(1) .adaptor_tabs(4, 2) . $field->name . ': ' . $htmlTypeString .",";
        }

        return rtrim($str, ',');
    }

    // 搜索框配置，可以支持更多扩展
    private function getSearchFormField()
    {
        $str = '';
        foreach ($this->commandData->fields as $field) {
            if (!$field->inSearch) {
                continue;
            }
            switch ($field->htmlType) {
                case 'date':
                case 'datetime':
                    $inputType = 'datetimerange';
                    break;
                case 'checkbox':
                case 'select':
                    $inputType = 'select';
                    break;
                case 'radio':
                    $inputType = 'radio';
                    break;
                default:
                    $inputType = 'input';
            }
            $str .= adaptor_nl(1) . adaptor_tabs(5, 2) . "{ prop: '{$field->name}', itemType: '{$inputType}' },";
        }

        return rtrim($str, ',');
    }

    private function getTableColumns()
    {
        $str = '';
        foreach ($this->commandData->fields as $field) {
            $str .= adaptor_nl(1) . adaptor_tabs(4, 2) . "{ prop: '{$field->name}' },";
        }

        return rtrim($str, ',');
    }

    public function generateCommonItems()
    {
        // 生成 vue api/url_variables 内的变量
        $url_variables_path = config('generator.path.vue_api', base_path('../web/src/api/url-variable.js'));
        $url_variables_contents = file_get_contents($url_variables_path);
        $urlVariablesTemplate = get_template($this->getVueTemplateName('routes.url'), $this->templateType);
        $url_variables_contents .= fill_template($this->commandData->dynamicVars, $urlVariablesTemplate);
        file_put_contents($url_variables_path, $url_variables_contents);
        $this->commandData->commandComment('vue api append');

        // @todo vue/router/modules/admin.js 自动新增
        $adminRouterPath = config('generator.path.vue_routes', base_path('../web/src/router/modules/admin.js'));
        $adminRouterContent = file_get_contents($adminRouterPath);
        $adminRouterTemplate = get_template($this->getVueTemplateName('routes.routes'), $this->templateType);
        $adminRouterTemplate = str_replace('$CAMEL_NAME$', $this->commandData->config->mCamel, $adminRouterTemplate);
        $insertContents = fill_template($this->commandData->dynamicVars, $adminRouterTemplate);
        $insertContentRows = explode(PHP_EOL, $adminRouterContent);
        $offset = 5;

        $insertPoint = count($insertContentRows) - ($offset + 1);
        $first = implode(PHP_EOL, array_slice($insertContentRows, 0, $insertPoint));
        $last = implode(PHP_EOL, array_slice($insertContentRows, -$offset));

        file_put_contents($adminRouterPath, $first);
        file_put_contents($adminRouterPath, PHP_EOL . $insertContents, FILE_APPEND);
        file_put_contents($adminRouterPath, PHP_EOL . $last, FILE_APPEND);

        $this->commandData->commandComment('vue routes append');
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Vue file deleted: ' . $this->fileName);
        }
    }

    public function getVueI18n()
    {
        $enString = $zhString = '';
        foreach ($this->commandData->fields as $field) {
            $enString .= adaptor_nl(1) . adaptor_tabs(3, 2) . $field->name . ": '" . Str::studly($field->name) . "',";
            $zhString .= adaptor_nl(1) . adaptor_tabs(3, 2) . $field->name . ": '" . $field->fieldTitle . "',";
        }

        return ['en' => trim($enString, ','), 'zh' => trim($zhString, ',')];
    }

    public function getVueTemplateName($subFix)
    {
        return config('generator.vue_scaffold_template', 'element-ui') . '.' . $subFix;
    }

}