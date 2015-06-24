<?php

namespace app\components;

use app\exceptions\CurlException;
use app\extensions\Curl;
use app\models\Account;
use Yii;
use yii\base\Exception;
use yii\helpers\ArrayHelper;

class Zoho extends \yii\base\Component
{
    public $authUrl;
    public $baseUrl;
    /**
     * @var Account
     */
    private $_account;

    private $_defaultParams = [];

    const ZOHO_SCOPE = 'ZohoInvoice/invoiceapi';
    const ZOHO_PAGE_SIZE = 500;
    const ZOHO_SUCCESS_CODE = 0;
    const ZOHO_SUCCESS_MESSAGE = 'success';

    public function init()
    {
        $this->_account = Account::findOne(['login' => Yii::$app->params['zoho.login']]);
        if ( empty($this->_account->token) ) {
            $this->updateAccountToken();
        }
        $this->_defaultParams['authtoken'] = $this->_account->token;
    }

    /**
     * Get invoices list
     * @param $page
     * @return array
     * @throws Exception
     */
    public function invoices($page = 1)
    {
        $url = $this->baseUrl . '/invoices';
        Yii::info('Try to get account invoices', LOG_CATEGORY);

        $curl = new Curl(true);
        $response = $curl->get($url, ArrayHelper::merge($this->_defaultParams, [
            'per_page' => self::ZOHO_PAGE_SIZE,
            'page' => $page
        ]));

        if ( (int)$response['code'] != self::ZOHO_SUCCESS_CODE ) {
            throw new Exception('Invalid response', $response['code']);
        }

        $invoices = isset($response['invoices']) ? $response['invoices'] : [];

        return $invoices;
    }

    /**
     * Get invoice by id
     * @param $invoiceId
     * @return array
     * @throws Exception
     */
    public function invoice($invoiceId)
    {
        $url = $this->baseUrl . "/invoices/{$invoiceId}";
        $curl = new Curl(true);
        $response = $curl->get($url, ArrayHelper::merge($this->_defaultParams, []));

        if ( (int)$response['code'] != self::ZOHO_SUCCESS_CODE ) {
            throw new Exception('Invalid response', $response['code']);
        }

        return isset($response['invoice']) ? $response['invoice'] : [];
    }

    /**
     * @param $page
     * @return array
     * @throws Exception
     */
    public function contacts($page = 1)
    {
        $url = $this->baseUrl . '/contacts';
        Yii::info('Try to get account contacts', LOG_CATEGORY);

        $curl = new Curl(true);
        $response = $curl->get($url, ArrayHelper::merge($this->_defaultParams, [
            'per_page' => self::ZOHO_PAGE_SIZE,
            'page' => $page
        ]));

        if ( (int)$response['code'] != self::ZOHO_SUCCESS_CODE ) {
            throw new Exception('Invalid response', $response['code']);
        }

        $contacts = isset($response['contacts']) ? $response['contacts'] : [];

        return $contacts;
    }

    /**
     * @param $contactId
     * @return array
     * @throws Exception
     */
    public function contact($contactId)
    {
        $url = $this->baseUrl . "/contacts/{$contactId}";
        $curl = new Curl(true);
        $response = $curl->get($url, ArrayHelper::merge($this->_defaultParams, []));

        if ( (int)$response['code'] != self::ZOHO_SUCCESS_CODE ) {
            throw new Exception('Invalid response', $response['code']);
        }

        return isset($response['contact']) ? $response['contact'] : [];
    }


    private function updateAccountToken()
    {
        Yii::info('Try to update account token', LOG_CATEGORY);
        $curl = new Curl();

        $response = $curl->post($this->authUrl, [
            'SCOPE' => self::ZOHO_SCOPE,
            'EMAIL_ID' => Yii::$app->params['zoho.login'],
            'PASSWORD' => Yii::$app->params['zoho.password']
        ]);

        $data = $this->parseAuthResponse($response);
        if ( $data['result'] ) {
            $this->_account->token = $data['token'];
            if ( $this->_account->save() ) {
                Yii::info('Account token successfully updated', LOG_CATEGORY);
            } else {
                Yii::info('Can\'t update account token.', LOG_CATEGORY);
                throw new Exception('Can\'t update account token.', $curl->getResponseCode());
            }
        } else {
            throw new CurlException('Can not get account token', $curl->getResponseCode());
        }
    }

    private function parseAuthResponse($response)
    {
        $data = [];
        $matches = [];
        preg_match('#RESULT=(?<result>.*?)#U', $response, $matches);

        if ( isset($matches['result']) ) {
            $data['result'] = trim($matches['result']) == 'TRUE' ? true : false;
        }

        preg_match('#AUTHTOKEN=(?<token>.*?)#U', $response, $matches);
        $data['token'] = isset($matches['token']) ? trim($matches['token']) : null;

        preg_match('#CAUSE=(?<cause>.*?)#U', $response, $matches);
        $data['cause'] = isset($matches['cause']) ? trim($matches['cause']) : null;

        return $data;
    }
}
