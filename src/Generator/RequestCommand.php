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

    protected function getResponse($table): string
    {
        $columns = $this->getTableColumns($table);
        $str     = "\n";
        foreach ($columns as $col) {
            $default = $col['type'];
            $default = sprintf('"%s"', $default);
            $str     .= "        '" . $col['name'] . "' => " . $default . ",\n";
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
            $default  = $col['type'];
            $notnull  = $col['notnull'];
            $required = $notnull ? 'required|' : '';
            if ($col['length'] > 0) {
                if ($col['type'] == "string") {
                    $max      = sprintf('max:%s|', $col['length']);
                    $required = sprintf('%s%s', $required, $max);
                }
            }
            $default = sprintf('"%s%s"', $required, $default);
            $str     .= "        '" . $col['name'] . "' => " . $default . ",\n";
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
        $arr               = parent::getInput();
        $arr['attributes'] = $this->getAttributes($arr["table"]);
        $arr['rules']      = $this->getRules($arr["table"]);
        $arr['response']   = $this->getResponse($arr["table"]);

        return $arr;
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Request';
    }
}
