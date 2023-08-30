<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-08-30 15:13
 */
declare(strict_types=1);

namespace Pudongping\HyperfAlarmClock;

use Psr\Container\ContainerInterface;
use Hyperf\Logger\LoggerFactory;
use Hyperf\Contract\ConfigInterface;

class LoggingChannel implements ChannelContract
{

    protected $logger;

    public function __construct(
        protected ContainerInterface $container,
        protected ConfigInterface    $config
    ) {
        $logCfg = $this->config->get('hyperf_alarm_clock.channels.logging');
        $this->logger = $this->container->get(LoggerFactory::class)->get($logCfg['name'], $logCfg['group']);
    }

    public function notice(array $data)
    {
        $this->logger->notice($this->config->get('hyperf_alarm_clock.title'), $data);
    }

    public function warning(array $data)
    {
        $this->logger->warning($this->config->get('hyperf_alarm_clock.title'), $data);
    }

}
