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

use Hyperf\Contract\ConfigInterface;
use Hyperf\Contract\StdoutLoggerInterface;
use Hyperf\Utils\Codec\Json;

class StdoutChannel implements ChannelContract
{

    /**
     * @var ConfigInterface
     */
    protected $config;

    /**
     * @var StdoutLoggerInterface
     */
    protected $logger;

    public function __construct(
        ConfigInterface       $config,
        StdoutLoggerInterface $logger
    ) {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function notice(array $data)
    {
        $message = $this->config->get('hyperf_alarm_clock.title') . ' : ' . Json::encode($data);
        $this->logger->notice($message);
    }

    public function warning(array $data)
    {
        $message = $this->config->get('hyperf_alarm_clock.title') . ' : ' . Json::encode($data);
        $this->logger->warning($message);
    }

}
