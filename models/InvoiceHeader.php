<?php
namespace app\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

class InvoiceHeader extends ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%invoice_header}}';
    }

    /**
     * @param array $invoiceData
     */
    protected function fillAttributes(array $invoiceData){
        $requiredKeys=['invoice_id', 'invoice_number', 'date', 'status', 'payment_terms', 'payment_terms_label',
                       'due_date', 'payment_expected_date', 'reference_number', 'customer_id', 'customer_name',
                       'sub_total', 'tax_total', 'total', 'notes'];

        foreach($requiredKeys as $key){
            $this->{$key}=ArrayHelper::getValue($invoiceData, $key, new Expression('NULL'));
        }
    }

    /**
     * @param $localInvoiceId
     * @param array $invoiceData
     * @return bool
     * @throws \yii\base\Exception
     */
    public static function updateInvoiceHeader($localInvoiceId, array $invoiceData){
        Yii::info("Try to update invoice header for [{$localInvoiceId}]", LOG_CATEGORY);

        $header=self::findOne(array('local_invoice_id' => $localInvoiceId));
        if(empty($header)){
            throw new Exception("Invoice header for local invoice [{$localInvoiceId}] was not found");
        }

        $header->fillAttributes($invoiceData);

        if($header->save()){
            Yii::info('Invoice header successfully updated', LOG_CATEGORY);

            return $header;
        }

        Yii::error('Can not update invoice header', LOG_CATEGORY);

        return null;
    }

    /**
     * @param $localInvoiceId
     * @param array $invoiceData
     * @return bool
     */
    public static function createInvoiceHeader($localInvoiceId, array $invoiceData){
        Yii::info('Try to create new invoice header', LOG_CATEGORY);

        $header=new InvoiceHeader();
        $header->local_invoice_id=$localInvoiceId;
        $header->fillAttributes($invoiceData);

        if($header->save()){
            Yii::info('Invoice header successfully created', LOG_CATEGORY);

            return $header;
        }

        Yii::error('Can not create invoice header', LOG_CATEGORY);

        return null;
    }
} 