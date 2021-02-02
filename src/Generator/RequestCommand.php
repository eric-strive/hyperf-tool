<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace Hyperf\EricTool\Generator;

use Hyperf\Command\Annotation\Command;
use Hyperf\DbConnection\Db;

/**
 * @Command
 */
class RequestCommand extends GeneratorCommand
{
    public function __construct()
    {
        parent::__construct('make:request');
        $this->setDescription('Create a new form request class');
    }

    /**
     * Fetch table columns
     *
     * https://www.doctrine-project.org/projects/doctrine-dbal/en/2.10/reference/types.html#reference
     * https://www.doctrine-project.org/projects/doctrine-dbal/en/2.10/reference/schema-manager.html#schema-manager
     * @param $table
     *
     * @return array
     */
    public function getTableColumns($table): array
    {
        $connection = DB::connection('default');
        $schema     = $connection->getDoctrineSchemaManager();

        $cols = $schema->listTableColumns($table);
        //        $index   = $schema->listTableIndexes($table);
        //        $isExist = $schema->tablesExist($table);

        $columns = [];
        //        $indexs  = [];
        foreach ($cols as $col) {
            $columns[] = [
                'name'    => $col->getName(),
                'type'    => $col->getType()->getName() ?? '', // Use Doctrine convert type
                'default' => $col->getDefault() ?? '',
                'comment' => $col->getComment() ?? '',
                'length'  => $col->getLength() ?? '',
                'notnull' => $col->getNotnull() ?? 'false',
            ];
        }

        return $columns;
    }

    protected function getAttributes($table): string
    {
        $columns = $this->getTableColumns($table);
        $str     = "\n";
        foreach ($columns as $col) {
            $default = $col['comment'] ? : $col['name'];
            $default = explode(' ', $default)[0];
            $default = sprintf('"%s"', $default);
            $str     .= "        '" . $col['name'] . "' => " . $default . ",\n";
        }

        return $str;
    }

    protected function getFieldDescription($table): string
    {
        $columns = $this->getTableColumns($table);
        $str     = "\n";
        foreach ($columns as $col) {
            $default = $col['comment'] ? : $col['name'];
            $default = sprintf('"%s"', $default);
            $str     .= "        '" . $col['name'] . "' => " . $default . ",\n";
        }

        return $str;
    }
    private function randomFloat($min = 0, $max = 1) {
        return $min + mt_rand() / mt_getrandmax() * ($max - $min);
    }
    protected function getResponse($table): string
    {
        $columns = $this->getTableColumns($table);
        $str     = "\n";
        foreach ($columns as $col) {
            $default = $col['type'];
            switch ($col['type']) {
                case 'decimal':
                    $default = $this->randomFloat(10, 99999);
                    break;
                case 'boolean':
                    $default = 1;
                    break;
                case 'integer':
                    $default = rand(1, 99999);
                    break;
                case 'date':
                    $default = '2021-01-01';
                    break;
                case 'datetime':
                    $default = '2021-01-01 11:12:29';
                    break;
            }
            switch ($col['name']) {
                case 'id':
                    $default = 1;
                    break;
                case 'created_user':
                    $default = '张三';
                    break;
                case 'updated_user':
                    $default = '李四';
                    break;
                case 'deleted_at':
                    $default = null;
                    break;
                case 'created_at':
                case 'updated_at':
                    $default = '2021-01-01 11:12:29';
                    break;
            }
            $default = is_int($default) ? $default : sprintf('"%s"', $default);
            $str     .= "        '" . $col['name'] . "' => " . $default . ",\n";
        }

        return $str;
    }

    protected function getColumns($table): string
    {
        $columns = $this->getTableColumns($table);
        $str     = "\n";
        foreach ($columns as $col) {
            $default = $col['type'];
            $label   = sprintf('$attributeLabel["%s"]', $col['name']);
            $default = sprintf("[
                'label' => %s,
                'value' => '%s',
            ],", $label, $col['name']);
            $str     .= $default . "\n";
        }

        return $str;
    }

    protected function getRules($table): string
    {
        $columns = $this->getTableColumns($table);
        $str     = "\n";
        foreach ($columns as $col) {
            if (in_array($col['name'], ['id', '_id', 'created_at', 'updated_at', 'created_user', 'updated_user'])) {
                continue;
            }
            $notnull = $col['notnull'];
            $rules   = [];
            if ($col['notnull']) {
                $rules[] = 'required';
            }
            if ($col['length'] > 0) {
                if ($col['type'] == "string") {
                    $rules[] = sprintf('max:%s', $col['length']);
                }
            }
            //默认值
            $default = sprintf('%s', $col['type']);
            switch ($col['type']) {
                case 'decimal':
                    $default = 'numeric';
                    break;
                case 'boolean':
                    $default = 'integer';
                    break;
                case 'datetime':
                    $default = '';
                    break;
            }
            if (!empty($default)) {
                $rules[] = $default;
            }
            $default = implode('|', $rules);
            $str     .= "        '" . $col['name'] . "' => '" . $default . "',\n";
        }

        return $str;
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? __DIR__ . '/stubs/validation-request.stub';
    }

    /**
     * Get the desired class name from the input.
     *
     * @return array
     */
    protected function getInput()
    {
        $arr                      = parent::getInput();
        $arr['attributes']        = $this->getAttributes($arr["table"]);
//        $arr['field_description'] = $this->getFieldDescription($arr["table"]);
        $arr['rules']             = $this->getRules($arr["table"]);
        $arr['response']          = $this->getResponse($arr["table"]);
        $arr['columns']           = '';
        if ($arr["title"]) {
            $columns        = $this->getColumns($arr["table"]);
            $arr['columns'] = sprintf('
    /**
     * 导出模板
     */
    public static function getColumns(): array
    {
        $attributeLabel = self::$field;
        return [%s];
    }
', $columns);
        }
        //是否需要换继承
        $extends = config('devtool.commands.make:request.extends', '');
        if ($extends) {
            $arr['use']     = '';
            $arr['extends'] = $extends;
        } else {
            $arr['use']     = 'use Hyperf\Validation\Request\FormRequest;';
            $arr['extends'] = 'FormRequest';
        }

        return $arr;
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Request';
    }
}
