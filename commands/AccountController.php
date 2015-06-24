<?php

namespace app\commands;

use app\models\Account;
use Yii;
use yii\console\Controller;
use yii\db\Exception;

class AccountController extends Controller
{
    public function actionAdd()
    {
        $login = Yii::$app->params['zoho.login'];

        Yii::info("Add account '{$login}'", Account::LOG_CATEGORY);
        Account::clear();

        try {
            if ( !Account::create($login) ) {
                Yii::error("Can't add account {$login}", Account::LOG_CATEGORY);
            }
        } catch (Exception $e) {
            Yii::error("Can't add account {$login}", Account::LOG_CATEGORY);
        }
    }
}