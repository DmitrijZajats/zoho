<?php
namespace app\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\ArrayHelper;

class InvoiceLines extends ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%invoice_lines}}';
    }

    protected static $LINE_KEYS=['line_item_id', 'item_id', 'name', 'description', 'rate', 'quantity', 'discount_amount', 'item_total'];

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


        if(!empty($linesData)){
            $connection=Yii::$app->db;
            $transaction = $connection->beginTransaction();
            try{
                $insertKeys=array_merge(['local_invoice_id', 'invoice_id'], self::$LINE_KEYS);
                $updateKeys=self::$LINE_KEYS;

                foreach($linesData as $lineData){
                    $line=self::find()->where(['line_item_id'=>$lineData['line_item_id']])->one();

                    if(empty($line)){
                        $lineData['local_invoice_id']=$localInvoiceId;
                        $lineData['invoice_id']=$invoiceId;

                        $sql='INSERT INTO '.self::tableName().' SET '.self::getConditionString($insertKeys);
                        $connection->createCommand($sql)->bindValues(self::getValuesArray($insertKeys, $lineData))->execute();
                    }
                    else{
                        $sql='UPDATE '.self::tableName().' SET '.self::getConditionString($updateKeys).' WHERE line_item_id='.$line->line_item_id;
                        $connection->createCommand($sql)->bindValues(self::getValuesArray($updateKeys, $lineData))->execute();
                    }
                }

                $transaction->commit();
            }
            catch(\Exception $e){
                $transaction->rollBack();

                Yii::info("Invoice lines were not updated. Reason: {$e->getMessage()}", LOG_CATEGORY);
                return false;
            }
        }

        Yii::info('Invoice lines were successfully updated', LOG_CATEGORY);
        return true;
    }
} 