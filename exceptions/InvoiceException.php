<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 01.07.2015
 * Time: 11:28
 */

namespace app\exceptions;


use yii\base\Exception;

class InvoiceException extends Exception {
    public function getName()
    {
        return 'InvoiceException';
    }
}