<?php

namespace app\components;

use Yii;
use yii\console\ErrorHandler;

class ZohoError extends ErrorHandler
{
    /**
     * @param \Exception $exception
     */
    public function renderException($exception)
    {
        $message = null;
        $code = $exception->getCode();

        switch ($code) {
            case 400:
                $message = 'Bad Request';
                break;
            case 401:
                $message = 'Unauthorized (Invalid Authtoken)';
                break;
            case 404:
                $message = 'Not Found';
                break;
            case 405:
                $message = 'Method Not Allowed (Method you have called is not supported for this API)';
                break;
            case 429:
                $message = 'Rate Limit Exceeded (API usage limit exceeded)';
                break;
            case 500:
                $message = 'Internal Error';
                break;
            case 501:
                $message = 'Not Implemented (Method you have called is not implemented)';
                break;
        }

        if ( empty($message) ) {
            $message = $exception->getMessage();
        }
        Yii::error($message, LOG_CATEGORY);
        parent::renderException($exception);
    }
}