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
        $codeMessage = null;
        $code = $exception->getCode();

        switch ($code) {
            case 400:
                $codeMessage = 'Bad Request';
                break;
            case 401:
                $codeMessage = 'Unauthorized (Invalid Authtoken)';
                break;
            case 404:
                $codeMessage = 'Not Found';
                break;
            case 405:
                $codeMessage = 'Method Not Allowed (Method you have called is not supported for this API)';
                break;
            case 429:
                $codeMessage = 'Rate Limit Exceeded (API usage limit exceeded)';
                break;
            case 500:
                $codeMessage = 'Internal Error';
                break;
            case 501:
                $codeMessage = 'Not Implemented (Method you have called is not implemented)';
                break;
        }

        $message = $exception->getMessage();
        if ( !empty($codeMessage) ) {
            $message .= ' ' .$codeMessage;
        }
        Yii::error($message, LOG_CATEGORY);
        parent::renderException($exception);
    }
}