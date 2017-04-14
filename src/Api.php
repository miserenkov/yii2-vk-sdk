<?php

/**
 * Created by PhpStorm.
 * Author: Misha Serenkov
 * Email: mi.serenkov@gmail.com
 * Date: 14.04.2017 9:59
 */

namespace miserenkov\vk;


use Yii;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\ArrayHelper;
use yii\httpclient\Client;
use yii\httpclient\Request;
use yii\httpclient\Response;

class Api extends Component
{
    public const LANG_RU = 'ru';
    public const LANG_UA = 'ua';
    public const LANG_BE = 'be';
    public const LANG_EN = 'en';
    public const LANG_ES = 'es';
    public const LANG_FI = 'fi';
    public const LANG_DE = 'de';
    public const LANG_IT = 'it';

    protected $supportedLanguages = [
        self::LANG_RU, self::LANG_UA, self::LANG_BE,
        self::LANG_EN, self::LANG_ES, self::LANG_FI,
        self::LANG_DE, self::LANG_IT,
    ];

    public const FORMAT_JSON = 'json';

    public const FORMAT_XML = 'xml';

    protected $supportedResponseTypes = [self::FORMAT_JSON, self::FORMAT_XML];

    public $accessTokens;

    public $useHttps = true;

    public $testMode = false;

    public $language = self::LANG_RU;

    public $apiVersion = '5.63';

    public $responseFormat = self::FORMAT_JSON;

    private $_client = null;

    public function init()
    {
        if (!in_array($this->responseFormat, $this->supportedResponseTypes)) {
            throw new InvalidConfigException("Format \"{$this->responseFormat}\" doesn't support on " . self::className());
        }

        if (!in_array($this->language, $this->supportedLanguages)) {
            throw new InvalidConfigException("Language \"{$this->language}\" doesn't support on " . self::className());
        }

        if (!is_array($this->accessTokens)) {
            $this->accessTokens = [$this->accessTokens];
        }
    }

    protected function _getClient()
    {
        if ($this->_client === null) {
            $this->_client = new Client(['baseUrl' => 'https://api.vk.com/method']);
        }

        return $this->_client;
    }

    /**
     * @param string $method
     * @param array $params
     * @return Request
     */
    protected function buildRequest(string $method, array $params = []): Request
    {
        $accessToken = $this->accessTokens[array_rand($this->accessTokens)];

        if ($this->responseFormat === self::FORMAT_XML) {
            $method .= '.xml';
        }

        return $this->_getClient()->get($method, ArrayHelper::merge($params, [
            'lang' => $this->language,
            'v' => $this->apiVersion,
            'test_mode' => (int) $this->testMode,
            'https' => (int) $this->useHttps,
            'accessToken' => $accessToken,
        ]));
    }

    protected function handleResponse(Response $response)
    {
        $response->setFormat($this->responseFormat);


    }

    public function sendRequest(string $method, array $params = [])
    {
        $request = $this->buildRequest($method, $params);
        return $this->handleResponse($request->send());
    }
}