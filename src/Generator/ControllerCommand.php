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
use Psr\Container\ContainerInterface;

/**
 * @Command
 */
class ControllerCommand extends GeneratorCommand
{
    public function __construct(ContainerInterface $container)
    {
        parent::__construct('make:controller');
        $this->setDescription('Create a new controller class');
    }

    protected function getStub(): string
    {
        return $this->getConfig()['stub'] ?? __DIR__ . '/stubs/controller.stub';
    }

    protected function getDefaultNamespace(): string
    {
        return $this->getConfig()['namespace'] ?? 'App\\Controller';
    }

    protected function getInput()
    {
        $arr           = parent::getInput();
        $name = $arr["name"];
        if(strpos('Controller',$name)){
            $name = str_replace('Controller','',$name);
        }
        $arr['prefix'] = $this->toUnderScore($name);

        return $arr;
    }
}
