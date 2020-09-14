<?php


namespace PlatformAdaptor\Generator\Generators;


use PlatformAdaptor\Generator\Common\CommandData;
use PlatformAdaptor\Generator\Utils\FileUtil;
use Illuminate\Support\Str;

class ModelGenerator extends BaseGenerator
{

    /**
     * 默认不生成的字段
     *
     * @var array
     */
    protected $exclude_fields = [
        'created_at',
        'updated_at',
    ];
    /**
     * @var CommandData
     */
    private $commandData;

    private $path;
    private $fileName;
    private $table;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = $commandData->config->pathModel;
        $this->fileName = $commandData->modelName . '.php';
        $this->table = $this->commandData->dynamicVars['$TABLE_NAME$'];
    }

    public function generate()
    {
        $templateData = get_template('model');

        $templateData = $this->fillTemplate($templateData);

        FileUtil::createFile($this->path, $this->fileName, $templateData);

        $this->commandData->commandComment("\nModel created: ");
        $this->commandData->commandInfo($this->fileName);
    }

    private function fillTemplate($templateData)
    {
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);

        $templateData = $this->fillSoftDeletes($templateData);

        $fillables = [];

        foreach ($this->commandData->fields as $field) {
            if ($field->isFillable) {
                $fillables[] = "'" . $field->name . "'";
            }
        }

        $templateData = $this->fillDocs($templateData);
        $templateData = $this->fillTimestamps($templateData);
        if ($this->commandData->getOption('primary')) {
            $primary = adaptor_tab() . "protected \$primaryKey = '" . $this->commandData->getOption('primary') . "';\n";
        } else {
            $primary = '';
        }
        $templateData = str_replace('$PRIMARY$', $primary, $templateData);
        $templateData = str_replace('$FIELDS$', implode(',' . adaptor_nl_tab(1, 2), $fillables), $templateData);
        $templateData = str_replace('$RULES$', implode(',' . adaptor_nl_tab(1, 2), $this->generateRules()), $templateData);
        $templateData = str_replace('$CAST$', implode(',' . adaptor_nl_tab(1, 2), $this->generateCasts()), $templateData);
        $templateData = str_replace('$GENERATE_DATE$', date('F j, Y, g:i a T'), $templateData);

        return $templateData;
    }

    // 填充软删除字段
    private function fillSoftDeletes($templateData)
    {
        if (!$this->commandData->getOption('softDelete')) {
            $templateData = str_replace('$SOFT_DELETE_IMPORT$', '', $templateData);
            $templateData = str_replace('$SOFT_DELETE$', '', $templateData);
            $templateData = str_replace('$SOFT_DELETE_DATES$', '', $templateData);
        } else {
            $templateData = str_replace(
                '$SOFT_DELETE_IMPORT$', "use Illuminate\\Database\\Eloquent\\SoftDeletes;\n",
                $templateData
            );
            $templateData = str_replace('$SOFT_DELETE$', adaptor_tab() . "use SoftDeletes;\n", $templateData);
            $deletedAtTimestamp = config('generator.timestamps.deleted_at', 'deleted_at');
            $templateData = str_replace(
                '$SOFT_DELETE_DATES$', adaptor_nl_tab() . "protected \$dates = ['" . $deletedAtTimestamp . "'];\n",
                $templateData
            );
        }

        return $templateData;
    }

    private function fillDocs($templateData)
    {
        if ($this->commandData->getAddOn('swagger')) {
            $templateData = $this->generateSwagger($templateData);
        } else {
            $docsTemplate = get_template('docs.model');
            $docsTemplate = fill_template($this->commandData->dynamicVars, $docsTemplate);
            $fillables = '';
            foreach ($this->commandData->fields as $field) {
                if ($field->isFillable) {
                    $fillables .= ' * @property ' . $this->getPHPDocType($field->fieldType) . ' ' . $field->name . PHP_EOL;
                }
            }
            $docsTemplate = str_replace('$GENERATE_DATE$', date('F j, Y, g:i a T'), $docsTemplate);
            $docsTemplate = str_replace('$PHPDOC$', $fillables, $docsTemplate);
            $templateData = str_replace('$DOCS$', $docsTemplate, $templateData);
        }

        return $templateData;
    }

    private function getPHPDocType($db_type)
    {
        switch ($db_type) {
            case 'datetime':
                return 'string|\Carbon\Carbon';
            case 'text':
                return 'string';
            default:
                return $db_type;
        }
    }

    public function generateSwagger($templateData)
    {
        $fieldTypes = SwaggerGenerator::generateTypes($this->commandData->fields);
        $template = get_template('model_docs.model', 'swagger-generator');
        $template = fill_template($this->commandData->dynamicVars, $template);
        $template = str_replace('$REQUIRED_FIELDS$',
                                '"' . implode('"' . ', ' . '"', $this->generateRequiredFields()) . '"', $template);
        $propertyTemplate = get_template('model_docs.property', 'swagger-generator');
        $properties = SwaggerGenerator::preparePropertyFields($propertyTemplate, $fieldTypes);
        $template = str_replace('$PROPERTIES$', implode(",\n", $properties), $template);
        $templateData = str_replace('$DOCS$', $template, $templateData);

        return $templateData;
    }

    private function generateRequiredFields()
    {
        $requiredFields = [];
        foreach ($this->commandData->fields as $field) {
            if (!empty($field->validations)) {
                if (Str::contains($field->validations, 'required')) {
                    $requiredFields[] = $field->name;
                }
            }
        }

        return $requiredFields;
    }

    private function fillTimestamps($templateData)
    {
        $replace = '';

        return str_replace('$TIMESTAMPS$', $replace, $templateData);
    }

    private function generateRules()
    {
        $rules = [];
        foreach ($this->commandData->fields as $field) {
            if (!empty($field->validations)) {
                $rule = "'" . $field->name . "' => '" . $field->validations . "'";
                $rules[] = $rule;
            }
        }

        return $rules;
    }

    public function generateCasts()
    {
        $casts = [];
        $timestamps = self::getTimestampFieldNames();
        foreach ($this->commandData->fields as $field) {
            if (in_array($field->name, $timestamps)) {
                continue;
            }
            $rule = "'" . $field->name . "' => ";
            switch ($field->fieldType) {
                case 'integer':
                    $rule .= "'integer'";
                    break;
                case 'double':
                    $rule .= "'double'";
                    break;
                case 'float':
                    $rule .= "'float'";
                    break;
                case 'boolean':
                    $rule .= "'boolean'";
                    break;
                case 'dateTime':
                case 'dateTimeTz':
                    $rule .= "'datetime'";
                    break;
                case 'date':
                    $rule .= "'date'";
                    break;
                case 'enum':
                case 'string':
                case 'char':
                case 'text':
                    $rule .= "'string'";
                    break;
                default:
                    $rule = '';
                    break;
            }
            if (!empty($rule)) {
                $casts[] = $rule;
            }
        }

        return $casts;
    }

    /**
     * Get timestamp columns from config.
     *
     * @return array the set of [created_at column name, updated_at column name]
     */
    public static function getTimestampFieldNames()
    {
        if (!config('generator.timestamps.enabled', true)) {
            return [];
        }

        $createdAtName = config('generator.timestamps.created_at', 'created_at');
        $updatedAtName = config('generator.timestamps.updated_at', 'updated_at');
        $deletedAtName = config('generator.timestamps.deleted_at', 'deleted_at');

        return [$createdAtName, $updatedAtName, $deletedAtName];
    }

    public function rollback()
    {
        if ($this->rollbackFile($this->path, $this->fileName)) {
            $this->commandData->commandComment('Model file deleted: ' . $this->fileName);
        }
    }
}