<?php
namespace app\exceptions;

use yii\base\Exception;

class ContactException extends Exception{
    public function getName()
    {
        return 'ContactException';
    }
} 