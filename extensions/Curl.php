<?php

namespace app\extensions;

use app\exceptions\CurlException;
use Yii;
use yii\helpers\Json;

class Curl
{

    private $responseCode = null;
    private $response = null;
    /**
     * @var array default curl options
     * Default curl options
     */
    private $_defaultOptions = [
        CURLOPT_USERAGENT => 'Zoho-curl',
        CURLOPT_TIMEOUT => 30,
        CURLOPT_CONNECTTIMEOUT => 30,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HEADER => false,
    ];

    private $_options = [];

    private $isJson = false;

    public function __construct($isJson = false)
    {
        $this->isJson = $isJson;
    }

    public function getResponseCode()
    {
        return $this->responseCode;
    }

    /**
     * Get all curl options
     * @return array
     */
    public function getOptions()
    {
        return $this->_options + $this->_defaultOptions;
    }

    /**
     * Set curl option
     * @param $key
     * @param $value
     * @return $this
     */
    public function setOption($key, $value)
    {
        $this->_options[$key] = $value;
        return $this;
    }

    /**
     * Unset option by key
     * @param $key
     * @return $this
     */
    public function unsetOption($key)
    {
        unset($this->_options[$key]);
        return $this;
    }

    /**
     * Unset all options
     * @return $this
     */
    public function unsetOptions()
    {
        $this->_options = [];
        return $this;
    }

    /**
     * Return response
     * @return mixed|null
     */
    public function response()
    {
        return $this->isJson ? Json::decode($this->response) : $this->response;
    }

    /**
     * Reset curl object
     */
    public function reset()
    {
        $this->unsetOptions();
        $this->responseCode = null;
        $this->response = null;

        return $this;
    }

    /**
     * Send POST request
     * @param $url
     * @param $params
     * @return bool|mixed|null
     * @throws CurlException
     */
    public function post($url, $params)
    {
        $this->setOption(CURLOPT_POST, true);
        $this->setOption(CURLOPT_POSTFIELDS, http_build_query($params));

        return $this->_request($url);
    }

    /**
     * Send GET request
     * @param $url
     * @param $params
     * @return bool|mixed|null
     * @throws CurlException
     */
    public function get($url, $params)
    {
        $url = $url . '?' . http_build_query($params);
        $this->setOption(CURLOPT_HTTPGET, true);
        return $this->_request($url);
    }

    protected function processResponse()
    {
        $response = $this->response();
        if ( $this->responseCode >= 200 && $this->responseCode < 300 ) {
            return $response;
        }
        $message = !empty($response['message']) ? $response['message'] : 'Request failed';
        throw new CurlException($message, $this->responseCode);
    }

    /**
     * Send curl request
     * @param $url
     * @return mixed|null
     * @throws CurlException
     */
    private function _request($url)
    {
        Yii::info('Start sending cURL-Request: ' . $url, LOG_CATEGORY);
        //
        $curl = curl_init($url);
        curl_setopt_array($curl, $this->getOptions());
        $this->response = curl_exec($curl);
        $this->responseCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);

        Yii::info('Close cURL-Request connection:' . $url, LOG_CATEGORY);
        //check if curl was successful
        if ( $this->response === false ) {
            throw new CurlException('Request failed: ' . curl_error($curl), curl_errno($curl));
        }
        curl_close($curl);

        return $this->processResponse();
    }
}
