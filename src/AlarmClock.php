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

use Hyperf\Utils\Codec\Json;
use Throwable;
use Hyperf\HttpServer\Contract\RequestInterface;

class AlarmClock
{

    public function send(RequestInterface $request, ?Throwable $e, float $start, float $end)
    {
        $content = Json::encode('');
    }

    private function buildContent(RequestInterface $request, ?Throwable $e, float $start, float $end): array
    {
        $data = [];
        $data['uname'] = php_uname(); // 获取主机信息 `uname -a`
        $data['php_version'] = phpversion(); // 获取当前的 PHP 版本

        $data['alarm_clock_time_float'] = microtime(true); // 报警时间戳
        $data['alarm_clock_time_date'] = date('Y-m-d H:i:s'); // 报警时间

        $data['app_name'] = config('app_name'); // 当前 app 名称
        $data['app_env'] = config('app_env'); // 当前 app 运行环境
        $data['swoole_version'] = phpversion('swoole'); // swoole 版本号
        $data['swoole_cpu_num'] = swoole_cpu_num(); // swoole 运行 cpu 数量

        $data['cost_time'] = $end - $start; // 代码执行耗时时间
        $data['start_time'] = $start; // 代码开始执行时间戳
        $data['end_time'] = $end; // 代码结束执行的时间戳
        $data['method'] = $request->getMethod(); // 当前请求方法
        $data['full_url'] = $request->fullUrl(); // 当前请求的路由
        $data['protocol_version'] = $request->getProtocolVersion(); // 协议版本
        $data['headers'] = $request->getHeaders(); // http 头部信息

        if (! str_contains($request->getHeaderLine('Content-Type'), 'multipart/form-data')) {
            $data['original_params'] = $request->all(); // 所有的参数
        } else {
            $data['original_params'] = 'The body contains boundary data, ignore it.';
        }

        $data['server_params'] = $request->getServerParams(); // 服务器相关参数

        if (! is_null($e) && $e instanceof Throwable) {
            $data['exception'] = sprintf('Err Msg [%s] File [%s] Line [%s]', $e->getMessage(), $e->getFile(), $e->getLine());
        }

        return $data;
    }

}
