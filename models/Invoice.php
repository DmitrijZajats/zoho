<?php

namespace app\models;

use app\components\Zoho;
use app\exceptions\InvoiceException;
use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Json;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "zoho_invoice".
 *
 * @property string $invoice_id
 * @property string $remote_id
 * @property string $data
 * @property string $data_hash
 * @property string $update_frequency
 * @property string $invoice_url
 * @property string $update_at
 * @property string $create_at
 */
class Invoice extends ActiveRecord
{
    const UPDATE_INVOICES_LIMIT_PER_SESSION = 100;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%invoice}}';
    }

    /**
     * @throws Exception
     */
    public static function updateInvoices()
    {
        Yii::info('Update invoices', LOG_CATEGORY);
        self::remove();

        $newInvoices = (new Query())
            ->select(['iq.remote_id'])
            ->distinct()
            ->from(InvoiceQueue::tableName() . ' iq')
            ->leftJoin(self::tableName() . ' i', 'i.remote_id = iq.remote_id')
            ->where('i.remote_id IS NULL')
            ->limit(self::UPDATE_INVOICES_LIMIT_PER_SESSION)
            ->column();

        $limit = self::UPDATE_INVOICES_LIMIT_PER_SESSION - count($newInvoices);
        if ( $limit >= 1 ) {
            $invoicesToUpdate = (new Query())
                ->select(['iq.remote_id'])
                ->distinct()
                ->from(InvoiceQueue::tableName() . ' iq')
                ->leftJoin(self::tableName() . ' i', 'i.remote_id = iq.remote_id')
                ->where('i.remote_id IS NOT NULL')
                ->orderBy(['i.update_at' => SORT_ASC, 'i.update_frequency' => SORT_DESC])
                ->limit($limit)
                ->column();
        } else {
            $invoicesToUpdate = [];
        }

        $invoices = array_merge($newInvoices, $invoicesToUpdate);

        foreach ($invoices as $invoiceId) {
            try {
                $fullInvoice = Yii::$app->zoho->invoice($invoiceId);
                if ( self::updateInvoice($fullInvoice) ) {
                    InvoiceQueue::deleteAll('remote_id = :remoteId', [':remoteId' => $invoiceId]);
                }
            } catch ( \Exception $e ) {
                Yii::error($e->getMessage() . '. Invoice ID [' . $invoiceId . ']', LOG_CATEGORY);
            }
        }
    }

    /**
     * @param array $invoiceArr
     * @return bool
     * @throws Exception
     */
    public static function updateInvoice(array $invoiceArr)
    {
        if ( !isset($invoiceArr['invoice_id']) ) {
            throw new Exception('Invalid invoice parameters');
        }
        Yii::info("Try to update invoice [{$invoiceArr['invoice_id']}]", LOG_CATEGORY);

        $invoice = self::findOne(['remote_id' => $invoiceArr['invoice_id']]);
        if ( !empty($invoice) ) {
            $invoiceUrl = $invoiceArr['invoice_url'];
            unset($invoiceArr['invoice_url']);
            $jsonData = Json::encode($invoiceArr);
            $dataHash = $invoice->createDataHash($jsonData);

            $success = true;
            $transaction = Yii::$app->db->beginTransaction();

            try {
                if ( $dataHash != $invoice->data_hash ) {
                    $invoice->data_hash = $dataHash;
                    $invoice->update_frequency = (int)$invoice->update_frequency + 1;

                    $success = $success ? InvoiceHeader::updateInvoiceHeader($invoice->invoice_id, $invoiceArr) : false;

                    $success = $success ? InvoiceLines::updateInvoiceLines(
                        $invoice->invoice_id,
                        $invoice->remote_id,
                        ArrayHelper::getValue($invoiceArr, 'line_items', [])
                    ) : false;
                }

                $invoice->invoice_url = $invoiceUrl;
                $invoice->update_at = new Expression('NOW()');
                $success = $success ? $invoice->save() : false;
                if ( $success ) {
                    $transaction->commit();
                    Yii::info('Invoice successfully updated', LOG_CATEGORY);
                    return true;
                }
                $transaction->rollBack();
            } catch ( \yii\db\Exception $e ) {
                $transaction->rollBack();

                throw new InvoiceException('Can not update invoice');
            }

            Yii::error('Can not update invoice. Invoice remote id ' . $invoice->remote_id, LOG_CATEGORY);
            return false;
        } else {
            Yii::info('The invoice does not exist', LOG_CATEGORY);
            return self::createInvoice($invoiceArr);
        }
    }

    /**
     * @param array $invoiceArr
     * @return bool
     * @throws Exception
     */
    public static function createInvoice(array $invoiceArr)
    {
        Yii::info('Try to create new invoice', LOG_CATEGORY);
        if ( !isset($invoiceArr['invoice_id']) ) {
            throw new Exception('Invalid invoice parameters');
        }
        $invoiceUrl = $invoiceArr['invoice_url'];
        unset($invoiceArr['invoice_url']);
        $jsonData = Json::encode($invoiceArr);
        $invoice = new Invoice();
        $invoice->remote_id = $invoiceArr['invoice_id'];
        $invoice->data_hash = $invoice->createDataHash($jsonData);
        $invoice->update_frequency = 0;
        $invoice->invoice_url = $invoiceUrl;
        $invoice->create_at = new Expression('NOW()');

        $transaction = Yii::$app->db->beginTransaction();
        try {
            $success = $invoice->save();
            $success = $success ? InvoiceHeader::createInvoiceHeader($invoice->invoice_id, $invoiceArr) : false;
            $success = $success ? InvoiceLines::updateInvoiceLines(
                $invoice->invoice_id,
                $invoice->remote_id,
                ArrayHelper::getValue($invoiceArr, 'line_items', [])
            ) : false;

            if ( $success ) {
                $transaction->commit();
                Yii::info('New invoice successfully created', LOG_CATEGORY);
                return true;
            }
            $transaction->rollBack();
        } catch ( \yii\db\Exception $e ) {
            $transaction->rollBack();
            throw new InvoiceException('Can not create invoice');
        }
        Yii::error('Can not create new invoice', LOG_CATEGORY);
        return false;
    }

    public static function remove()
    {
        $deleteInvoices = (new Query())
            ->select(['i.remote_id'])
            ->from(self::tableName() . ' i')
            ->leftJoin(InvoiceQueue::tableName() . ' iq', 'iq.remote_id = i.remote_id')
            ->where('iq.remote_id IS NULL')
            ->column();

        if ( !empty($deleteInvoices) ) {
            self::deleteAll('remote_id IN (' . implode(',', $deleteInvoices) . ')');
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['remote_id', 'data_hash', 'invoice_url'], 'required'],
            [['update_frequency'], 'integer'],
            [['update_at', 'create_at'], 'safe'],
            [['invoice_url'], 'string', 'max' => 1024],
            [['remote_id'], 'string', 'max' => 255],
            [['data_hash'], 'string', 'max' => 32],
            [['remote_id'], 'unique']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'invoice_id' => 'Invoice ID',
            'remote_id' => 'Remote ID',
            'data' => 'Data',
            'hash_data' => 'Hash Data',
            'update_frequency' => 'Update Frequency',
            'invoice_url' => 'Invoice url',
            'update_at' => 'Update At',
            'create_at' => 'Create At',
        ];
    }

    private function createDataHash($data)
    {
        $dataToHash = $data;
        if ( is_array($dataToHash) ) {
            $dataToHash = Json::encode($data);
        }
        return md5($dataToHash);
    }
}
