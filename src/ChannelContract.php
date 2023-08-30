<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-08-30 16:40
 */
declare(strict_types=1);

namespace Pudongping\HyperfAlarmClock;

interface ChannelContract
{

    public function notice(array $data);

    public function warning(array $data);

}
