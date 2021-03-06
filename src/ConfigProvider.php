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

namespace Hyperf\EricTool;

class ConfigProvider
{
    public function __invoke()
    {
        return [
            'annotations' => [
                'scan' => [
                    'paths' => [
                        __DIR__,
                    ],
                ],
            ],
            'commands'    => [
                Generator\ControllerCommand::class,
                Generator\RequestCommand::class,
            ],
            'publish'     => [
                [
                    'id'          => 'config',
                    'description' => 'The config for devtool.',
                    'source'      => __DIR__ . '/../publish/SwaggerTemplate.php',
                    'destination' => BASE_PATH . '/app/Constants/SwaggerTemplate.php',
                ],
                [
                    'id'          => 'config',
                    'description' => 'The config for devtool.',
                    'source'      => __DIR__ . '/../publish/app_tool.php',
                    'destination' => BASE_PATH . '/app/config/autoload/app_tool.php',
                ],
            ],
        ];
    }
}
