<?php
/**
 *
 *
 * Created by PhpStorm
 * User: Alex
 * Date: 2023-08-30 00:28
 */
declare(strict_types=1);

namespace Pudongping\HyperfAlarmClock;

use Hyperf\Guzzle\ClientFactory;
use Hyperf\Contract\ConfigInterface;
use Hyperf\Utils\Codec\Json;
use GuzzleHttp\Exception\GuzzleException;

class FeiShuChannel implements ChannelContract
{

    /**
     * @var ClientFactory
     */
    protected $clientFactory;

    /**
     * @var ConfigInterface
     */
    protected $config;

    public function __construct(
        ClientFactory   $clientFactory,
        ConfigInterface $config
    ) {
        $this->clientFactory = $clientFactory;
        $this->config = $config;
    }

    public function notice(array $data)
    {
        $this->message($data, 'blue');
    }

    public function warning(array $data)
    {
        $this->message($data, 'red');
    }

    public function message(array $data, string $template)
    {
        $content = $this->genMarkdownContent($data);
        $this->sendPost($template, $content);
    }

    protected function genMarkdownContent(array $data): string
    {
        $markdown = '';

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $value = Json::encode($value);
            }
            if (is_resource($value) || is_object($value)) {
                continue;
            }

            $markdown .= sprintf("**%s：** %s\n", $key, $value);
        }

        return $markdown;
    }

    /**
     * document link: https://open.feishu.cn/document/ukTMukTMukTM/uADOwUjLwgDM14CM4ATN
     *
     * @param string $template
     * @param string $content
     * @return string
     * @throws GuzzleException
     */
    protected function sendPost(string $template, string $content): string
    {
        $client = $this->clientFactory->create();
        $webHookSecret = $this->config->get('hyperf_alarm_clock.channels.feishu.webhook_secret');
        if (! $webHookSecret) {
            return '';
        }
        $url = 'https://open.feishu.cn/open-apis/bot/v2/hook/' . $webHookSecret;

        $response = $client->request('POST', $url, [
            'http_errors' => false,
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'msg_type' => 'interactive',
                'card' => [
                    'config' => [
                        'wide_screen_mode' => true,
                        'enable_forward' => true
                    ],
                    'header' => [
                        'template' => $template,
                        'title' => [
                            'content' => $this->config->get('hyperf_alarm_clock.title'),
                            'tag' => 'plain_text'
                        ],
                    ],
                    'elements' => [
                        [
                            'tag' => 'markdown',
                            'content' => $content
                        ]
                    ]
                ]
            ],
        ]);

        return $response->getBody()->getContents();
    }

}
