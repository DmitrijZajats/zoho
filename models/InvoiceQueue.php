<?php

namespace app\models;

use Yii;
use yii\helpers\VarDumper;

/**
 * This is the model class for table "zoho_invoice_queue".
 *
 * @property string $remote_id
 */
class InvoiceQueue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%invoice_queue}}';
    }

    public static function invoicesList()
    {
        Yii::info('Get all account invoices', LOG_CATEGORY);
        $page = 1;
        do {
            $invoices = Yii::$app->zoho->invoices($page);

            $sqlData = [];
            $sql = 'INSERT INTO ' . self::tableName() . ' (remote_id) VALUES ';
            foreach ($invoices as $invoice) {
                $sqlData[] = "({$invoice['invoice_id']})";
            }
            if ( !empty($sqlData) ) {
                $sql .= implode(',', $sqlData);
                $sql .= ' ON DUPLICATE KEY UPDATE remote_id = VALUES(remote_id)';
                Yii::$app->db->createCommand($sql)->execute();
            }
            $page += 1;
        } while( !empty($invoices) );
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['remote_id'], 'required'],
            [['remote_id'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'remote_id' => 'Remote ID',
        ];
    }
}
