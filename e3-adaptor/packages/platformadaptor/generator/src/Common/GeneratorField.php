<?php


namespace PlatformAdaptor\Generator\Common;


use Illuminate\Support\Str;

class GeneratorField
{
    public $name; // 字段名称
    public $dbInput; //数据库字段
    public $dbComment; // 数据库字段备注，默认取 $fieldTitle
    public $htmlInput; // html页面字段
    public $htmlType; //字段类型
    public $fieldType;
    public $fieldTitle; // 字段显示的名称

    public $htmlValues;

    public $migrationText;
    public $foreignKeyText;
    public $validations;

    public $isFillable = true; // 是否可以填充
    public $isPrimary = false;
    public $inForm = true; // 是否在新增表单中
    public $inSearch = false; // 是否在搜索框中
    public $inIndex = false;

    public function parseDBType($dbInput)
    {
        $this->dbInput = $dbInput;
        $this->prepareMigrationText();
    }

    public function parseHtmlInput($htmlInput)
    {
        $this->htmlInput = $htmlInput;
        $this->htmlValues = [];

        if (empty($htmlInput)) {
            $this->htmlType = 'text';

            return;
        }

        $inputsArr = explode(',', $htmlInput);
        $this->htmlType = array_shift($inputsArr);

        if (count($inputsArr) > 0) {
            $this->htmlValues = $inputsArr;
        }
    }

    public function parseFieldTitle($fieldTitle)
    {
        if (empty($fieldTitle)) {
            $this->fieldTitle = Str::title(str_replace('_', ' ', $this->name));
        } else {
            $this->fieldTitle = $fieldTitle;
        }
    }

    public function parseOptions($options)
    {
        $options = strtolower($options);
        $optionsArr = explode(',', $options);
        if (in_array('s', $optionsArr)) {
            $this->inSearch = false;
        }
        if (in_array('p', $optionsArr)) {
            // if field is primary key, then its not searchable, fillable, not in index & form
            $this->isPrimary = true;
            $this->inSearch = false;
            $this->isFillable = false;
            $this->inForm = false;
            $this->inIndex = false;
        }
        if (in_array('f', $optionsArr)) {
            $this->isFillable = false;
        }
        if (in_array('if', $optionsArr)) {
            $this->inForm = false;
        }
        if (in_array('ii', $optionsArr)) {
            $this->inIndex = false;
        }
    }

    // 生成migration语句
    private function prepareMigrationText()
    {
        $inputsArr = explode(':', $this->dbInput);
        $this->migrationText = '$table->';
        $fieldTypeParams = explode(',', array_shift($inputsArr));
        $this->fieldType = array_shift($fieldTypeParams);
        $this->migrationText .= $this->fieldType . "('" . $this->name . "'";
        if ($this->fieldType == 'enum') {
            $this->migrationText .= ', [';
            foreach ($fieldTypeParams as $param) {
                $this->migrationText .= "'" . $param . "',";
            }
            $this->migrationText = substr($this->migrationText, 0, strlen($this->migrationText) - 1);
            $this->migrationText .= ']';
        } else {
            foreach ($fieldTypeParams as $param) {
                $this->migrationText .= ', ' . $param;
            }
        }
        $this->migrationText .= ')';
        foreach ($inputsArr as $input) {
            $inputParams = explode(',', $input);
            $functionName = array_shift($inputParams);
            if ($functionName == 'foreign') {
                $foreignTable = array_shift($inputParams);
                $foreignField = array_shift($inputParams);
                $this->foreignKeyText .= "\$table->foreign('" . $this->name . "')->references('" . $foreignField . "')->on('" . $foreignTable . "');";
            } else {
                $this->migrationText .= '->' . $functionName;
                $this->migrationText .= '(';
                $this->migrationText .= implode(', ', $inputParams);
                $this->migrationText .= ')';
            }
        }
        // 新增 comment
        if ($this->fieldTitle) {
            $this->migrationText .= "->comment('" . $this->fieldTitle . "')";
        }
        $this->migrationText .= ';';
    }


    public static function parseFieldFromFile($fieldInput)
    {
        $field = new self();
        // 组件大小
        $field->size = $fieldInput['size'] ?? 'small';
        $field->name = $fieldInput['name'];
        $field->parseFieldTitle($fieldInput['fieldTitle']);
        $field->dbComment = isset($fieldInput['dbComment']) ? $fieldInput['dbComment'] : $field->fieldTitle;
        $field->parseDBType($fieldInput['dbType']);
        $field->parseHtmlInput(isset($fieldInput['htmlType']) ? $fieldInput['htmlType'] : '');
        $field->validations = isset($fieldInput['validations']) ? $fieldInput['validations'] : '';
        $field->isFillable = isset($fieldInput['fillable']) ? $fieldInput['fillable'] : true;
        $field->isPrimary = isset($fieldInput['primary']) ? $fieldInput['primary'] : false;
        $field->inForm = isset($fieldInput['inForm']) ? $fieldInput['inForm'] : false;
        $field->inSearch = isset($fieldInput['inSearch']) ? $fieldInput['inSearch'] : false;
        $field->inIndex = isset($fieldInput['inIndex']) ? $fieldInput['inIndex'] : true;

        return $field;
    }

    public function __get($key)
    {
        return $this->$key;
    }
}