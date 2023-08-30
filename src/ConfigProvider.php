<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-08-30 17:36
 */
declare(strict_types=1);

namespace Pudongping\HyperfAlarmClock;

class ConfigProvider
{

    public function __invoke(): array
    {
        return [
            'dependencies' => [],
            'commands' => [],
            'listeners' => [],
            'aspects' => [],
            'publish' => [
                [
                    'id' => 'config',
                    'description' => '配置文件',
                    'source' => __DIR__ . '/../publish/hyperf_alarm_clock.php',
                    'destination' => BASE_PATH . '/config/autoload/hyperf_alarm_clock.php',
                ],
            ],
        ];
    }

}
