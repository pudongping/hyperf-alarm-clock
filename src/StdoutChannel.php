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
use Hyperf\Codec\Json;

class StdoutChannel implements ChannelContract
{

    public function __construct(
        protected ConfigInterface       $config,
        protected StdoutLoggerInterface $logger
    ) {
    }

    public function notice(array $data)
    {
        $this->logger->notice($this->message($data));
    }

    public function warning(array $data)
    {
        $this->logger->warning($this->message($data));
    }

    private function message(array $data): string
    {
        return $this->config->get('hyperf_alarm_clock.title') . ' : ' . Json::encode($data);
    }

}
