<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-08-30 00:36
 */
declare(strict_types=1);

namespace Pudongping\HyperfAlarmClock;

use Throwable;
use Hyperf\HttpServer\Contract\RequestInterface;
use Hyperf\Utils\ApplicationContext;
use Psr\Container\ContainerExceptionInterface;
use Psr\Container\NotFoundExceptionInterface;

class AlarmClock
{

    public function send(RequestInterface $request, ?Throwable $e, float $start, float $end)
    {
        $content = $this->buildContent($request, $e, $start, $end);
        $this->notification($content);
    }

    /**
     * @param RequestInterface $request
     * @param Throwable|null $e
     * @param float $start
     * @param float $end
     * @return array
     */
    private function buildContent(RequestInterface $request, ?Throwable $e, float $start, float $end): array
    {
        $data = [];
        $data['cost_time'] = $end - $start; // 代码执行耗时时间
        $data['start_time'] = $start; // 代码开始执行时间戳
        $data['end_time'] = $end; // 代码结束执行的时间戳

        $data['app_name'] = config('app_name'); // 当前 app 名称
        $data['app_env'] = config('app_env'); // 当前 app 运行环境

        $data['alarm_clock_time_float'] = microtime(true); // 报警时间戳
        $data['alarm_clock_time_date'] = date('Y-m-d H:i:s'); // 报警时间

        $data['method'] = $request->getMethod(); // 当前请求方法
        $data['full_url'] = $request->fullUrl(); // 当前请求的路由

        $data['uname'] = php_uname(); // 获取主机信息 `uname -a`
        $data['php_version'] = phpversion(); // 获取当前的 PHP 版本

        $data['swoole_version'] = phpversion('swoole'); // swoole 版本号
        $data['swoole_cpu_num'] = swoole_cpu_num(); // swoole 运行 cpu 数量

        $data['protocol_version'] = $request->getProtocolVersion(); // 协议版本
        $data['headers'] = $request->getHeaders(); // http 头部信息

        if (false === strpos($request->getHeaderLine('Content-Type'), 'multipart/form-data')) {
            $data['original_params'] = $request->all(); // 所有的参数
        } else {
            $data['original_params'] = 'The body contains boundary data, ignore it.';
        }

        $data['server_params'] = $request->getServerParams(); // 服务器相关参数

        if ($e instanceof Throwable) {
            $data['exception'] = sprintf('Err Msg [%s] File [%s] Line [%s]', $e->getMessage(), $e->getFile(), $e->getLine());
        }

        return $data;
    }

    /**
     * @param array $data
     * @return void|null
     * @throws AlarmClockException
     * @throws ContainerExceptionInterface
     * @throws NotFoundExceptionInterface
     */
    private function notification(array $data)
    {
        $configs = config('hyperf_alarm_clock', []);
        if (! $configs) {
            throw new AlarmClockException('lack of alarm clock config');
        }

        if (! $configs['enable']) {
            return null;
        }

        $channels = array_filter(explode(',', $configs['default']));
        if (! $channels) {
            return null;
        }

        $sendType = null;
        $warningTimeout = (float)$configs['timeout']['warning'];
        $noticeTimeout = (float)$configs['timeout']['notice'];

        // 代码执行时间
        if ($data['cost_time'] >= $warningTimeout) {
            $sendType = 'warning';
        }
        if ($data['cost_time'] >= $noticeTimeout && $data['cost_time'] < $warningTimeout) {
            $sendType = 'notice';
        }
        if (is_null($sendType)) {
            return null;
        }

        foreach ($channels as $channel) {
            co(function () use ($channel, $sendType, $data) {
                $class = $this->getChannelClass($channel);
                if (! $class) {
                    return null;
                }
                ApplicationContext::getContainer()->get($class)->{$sendType}($data);
            });
        }

    }

    /**
     * @param string $channel
     * @return string|null
     */
    private function getChannelClass(string $channel): ?string
    {
        $table = [
            'feishu' => FeiShuChannel::class,
            'logging' => LoggingChannel::class,
            'stdout' => StdoutChannel::class
        ];

        return $table[$channel] ?? null;
    }

}
