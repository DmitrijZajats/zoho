<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;

/**
 * This is the model class for table "zoho_invoice_header".
 *
 * @property string $header_id
 * @property string $local_invoice_id
 * @property string $invoice_id
 * @property string $invoice_number
 * @property string $date
 * @property string $status
 * @property integer $payment_terms
 * @property string $payment_terms_label
 * @property string $due_date
 * @property string $payment_expected_date
 * @property string $reference_number
 * @property string $customer_id
 * @property string $customer_name
 * @property double $sub_total
 * @property double $tax_total
 * @property double $total
 * @property string $notes
 *
 * @property Invoice $localInvoice
 */
class InvoiceHeader extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%invoice_header}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['local_invoice_id'], 'required'],
            [['local_invoice_id', 'payment_terms'], 'integer'],
            [['date', 'due_date', 'payment_expected_date'], 'safe'],
            [['sub_total', 'tax_total', 'total'], 'number'],
            [['notes'], 'string'],
            [['invoice_id', 'invoice_number', 'status', 'payment_terms_label', 'reference_number', 'customer_id', 'customer_name'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'header_id' => 'Header ID',
            'local_invoice_id' => 'Local Invoice ID',
            'invoice_id' => 'Invoice ID',
            'invoice_number' => 'Invoice Number',
            'date' => 'Date',
            'status' => 'Status',
            'payment_terms' => 'Payment Terms',
            'payment_terms_label' => 'Payment Terms Label',
            'due_date' => 'Due Date',
            'payment_expected_date' => 'Payment Expected Date',
            'reference_number' => 'Reference Number',
            'customer_id' => 'Customer ID',
            'customer_name' => 'Customer Name',
            'sub_total' => 'Sub Total',
            'tax_total' => 'Tax Total',
            'total' => 'Total',
            'notes' => 'Notes',
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
     * @param $localInvoiceId
     * @param array $invoiceData
     * @return bool
     */
    public static function createInvoiceHeader($localInvoiceId, array $invoiceData){
        Yii::info('Try to create new invoice header', LOG_CATEGORY);

        $header=new InvoiceHeader();
        $header->local_invoice_id=$localInvoiceId;
        $header->load($invoiceData, '');

        if( $header->save() ){
            Yii::info('Invoice header successfully created', LOG_CATEGORY);
            return true;
        }
        Yii::error('Can not create invoice header', LOG_CATEGORY);
        return false;
    }

    /**
     * @param $localInvoiceId
     * @param array $invoiceData
     * @return bool
     * @throws \yii\base\Exception
     */
    public static function updateInvoiceHeader($localInvoiceId, array $invoiceData){
        Yii::info("Try to update invoice header for [{$localInvoiceId}]", LOG_CATEGORY);

        $header = self::findOne(['local_invoice_id' => $localInvoiceId]);
        if( empty($header) ){
            throw new Exception("Invoice header for local invoice [{$localInvoiceId}] was not found");
        }

        $header->load($invoiceData, '');

        if( $header->save(false) ){
            Yii::info('Invoice header successfully updated', LOG_CATEGORY);

            return true;
        }

        Yii::error('Can not update invoice header', LOG_CATEGORY);
        return false;
    }
}
