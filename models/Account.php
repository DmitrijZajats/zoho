<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "zoho_account".
 *
 * @property string $account_id
 * @property string $login
 * @property string $token
 */
class Account extends \yii\db\ActiveRecord
{
    const LOG_CATEGORY = 'account';
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%account}}';
    }

    public static function findZohoAccount()
    {
//        $account = self::find();
//        if ( empty($account) ) {
//
//            foreach ( Yii::$app->params['zoho.accounts'] as $login => $password ) {
//                $newAccount = new Account();
//                $newAccount->login = $login;
//                $newAccount->save();
//            }
//        }
    }

    public static function create($login)
    {
        Yii::info("Try to add account '{$login}'", self::LOG_CATEGORY);
        $account = self::find()->where('login = :login', [':login' => $login])->one();

        if ( empty($account) ) {
            $newAccount = new Account();
            $newAccount->login = $login;
            if ( $newAccount->save() ) {
                Yii::info("Account {$login} successfully added", self::LOG_CATEGORY);
                return true;
            }
        } else {
            Yii::info("Account '{$login}' was added before", self::LOG_CATEGORY);
            return true;
        }
        return false;
    }

    public static function clear()
    {
        Yii::$app->db->createCommand()->delete(self::tableName())->execute();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['login'], 'required'],
            [['login', 'token'], 'string', 'max' => 255],
            [['login'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'account_id' => 'Account ID',
            'login' => 'Login',
            'token' => 'Token',
        ];
    }
}
