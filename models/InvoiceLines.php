<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "zoho_invoice_lines".
 *
 * @property string $line_id
 * @property string $local_invoice_id
 * @property string $invoice_id
 * @property string $line_item_id
 * @property string $item_id
 * @property string $name
 * @property string $description
 * @property double $rate
 * @property double $quantity
 * @property double $discount_amount
 * @property double $item_total
 *
 * @property Invoice $localInvoice
 */
class InvoiceLines extends ActiveRecord
{
    protected static $LINE_KEYS=['name', 'description', 'rate', 'quantity', 'discount_amount', 'item_total'];
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'zoho_invoice_lines';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['local_invoice_id'], 'required'],
            [['local_invoice_id'], 'integer'],
            [['description'], 'string'],
            [['rate', 'quantity', 'discount_amount', 'item_total'], 'number'],
            [['invoice_id', 'line_item_id', 'item_id', 'name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'line_id' => 'Line ID',
            'local_invoice_id' => 'Local Invoice ID',
            'invoice_id' => 'Invoice ID',
            'line_item_id' => 'Line Item ID',
            'item_id' => 'Item ID',
            'name' => 'Name',
            'description' => 'Description',
            'rate' => 'Rate',
            'quantity' => 'Quantity',
            'discount_amount' => 'Discount Amount',
            'item_total' => 'Item Total',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getLocalInvoice()
    {
        return $this->hasOne(Invoice::className(), ['invoice_id' => 'local_invoice_id']);
    }

    /**
     * @param $keys
     * @return string
     */
    protected static function getKeysString($keys){
        return implode(',', $keys);
    }

    /**
     * @param $keys
     * @return string
     */
    protected static function getConditionString($keys){
        $condition=[];

        foreach($keys as $key){
            $condition[]=$key.'=:'.$key;
        }

        return implode(',', $condition);
    }

    protected static function getValuesArray($keys, $lineData){
        $values=[];

        foreach($keys as $key){
            $values[':'.$key]=ArrayHelper::getValue($lineData, $key, new Expression('NULL'));
        }

        return $values;
    }

    public static function updateInvoiceLines($localInvoiceId, $invoiceId, array $linesData){
        Yii::info("Try to update invoice lines for [{$localInvoiceId}]", LOG_CATEGORY);

        if( !empty($linesData) ){
            $insertKeys = array_merge(['local_invoice_id', 'invoice_id', 'line_item_id', 'item_id'], self::$LINE_KEYS);
            $updateKeys = self::$LINE_KEYS;

            foreach($linesData as $lineData){
                $line = self::findOne(['line_item_id'=>$lineData['line_item_id']]);

                if( empty($line) ) {
                    $lineData['local_invoice_id']=$localInvoiceId;
                    $lineData['invoice_id']=$invoiceId;

                    $sql='INSERT INTO '.self::tableName().' SET '.self::getConditionString($insertKeys);
                    Yii::$app->db->createCommand($sql)->bindValues(self::getValuesArray($insertKeys, $lineData))->execute();
                } else {
                    $sql='UPDATE '.self::tableName().' SET '.self::getConditionString($updateKeys).' WHERE line_item_id='.$line->line_item_id;
                    Yii::$app->db->createCommand($sql)->bindValues(self::getValuesArray($updateKeys, $lineData))->execute();
                }
            }
        }

        Yii::info('Invoice lines were successfully updated', LOG_CATEGORY);
        return true;
    }
}
