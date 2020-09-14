<?php


namespace PlatformAdaptor\Generator\Generators;


use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use PlatformAdaptor\Generator\Common\CommandData;
use PlatformAdaptor\Generator\Utils\FileUtil;

class MigrationGenerator extends BaseGenerator
{
    /**
     * @var CommandData
     */
    private $commandData;

    private $path;

    public function __construct(CommandData $commandData)
    {
        $this->commandData = $commandData;
        $this->path = config('generator.path.migration', base_path('database/migrations/'));
    }

    public function generate()
    {
        $templateData = get_template('migration');
        $templateData = fill_template($this->commandData->dynamicVars, $templateData);
        $templateData = str_replace('$FIELDS$', $this->generateFields(), $templateData);

        $tableName = $this->commandData->dynamicVars['$TABLE_NAME$'];

        $fileName = date('Y_m_d_His') . '_' . 'create_' . $tableName . '_table.php';

        FileUtil::createFile($this->path, $fileName, $templateData);

        $this->commandData->commandComment("\nMigration created: ");
        $this->commandData->commandInfo($fileName);
    }

    private function generateFields()
    {
        $fields = [];
        $foreignKeys = [];
        $createdAtField = null;
        $updatedAtField = null;
        foreach ($this->commandData->fields as $field) {
            if ($field->name == 'created_at') {
                $createdAtField = $field;
                continue;
            } else {
                if ($field->name == 'updated_at') {
                    $updatedAtField = $field;
                    continue;
                }
            }
            $fields[] = $field->migrationText;
            if (!empty($field->foreignKeyText)) {
                $foreignKeys[] = $field->foreignKeyText;
            }
        }
        if ($createdAtField and $updatedAtField) {
            $fields[] = '$table->timestamps();';
        } else {
            if ($createdAtField) {
                $fields[] = $createdAtField->migrationText;
            }
            if ($updatedAtField) {
                $fields[] = $updatedAtField->migrationText;
            }
        }
        if ($this->commandData->getOption('softDelete')) {
            $fields[] = '$table->softDeletes();';
        }
        return implode(adaptor_nl_tab(1, 3), array_merge($fields, $foreignKeys));
    }

    // 删除相关的 migration 文件
    public function rollback()
    {
        $fileName = 'create_'.$this->commandData->config->tableName.'_table.php';
        /** @var Filesystem $allFiles */
        $allFiles = Filesystem::allFiles($this->path);
        $files = [];
        foreach ($allFiles as $file) {
            $files[] = $file->getFilename();
        }
        $files = array_reverse($files);
        foreach ($files as $file) {
            if (Str::contains($file, $fileName)) {
                if ($this->rollbackFile($this->path, $file)) {
                    $this->commandData->commandComment('Migration file deleted: '.$file);
                }
                break;
            }
        }
    }
}