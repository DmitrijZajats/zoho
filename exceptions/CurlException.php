<?php

namespace app\exceptions;

use yii\base\Exception;

class CurlException extends Exception
{
    public function getName()
    {
        return 'CurlException';
    }
}