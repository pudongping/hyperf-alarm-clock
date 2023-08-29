<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-08-29 23:40
 */
declare(strict_types=1);

namespace Pudongping\HyperfAlarmClock;

use Hyperf\HttpServer\Contract\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Container\ContainerInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\RequestHandlerInterface;

class AlarmClockMiddleware implements MiddlewareInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * @var RequestInterface
     */
    protected $request;

    public function __construct(
        ContainerInterface $container,
        RequestInterface   $request
    ) {
        $this->container = $container;
        $this->request = $request;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        $start = microtime(true);
        $e = null;

        try {
            $response = $handler->handle($request);
        } catch (\Throwable $exception) {
            $e = $exception;
            throw $exception;
        } finally {
            $end = microtime(true);
            $this->container->get(AlarmClock::class)->send($this->request, $e, $start, $end);
        }

        return $response;
    }

}
