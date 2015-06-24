<?php

namespace app\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\db\Expression;
use yii\db\Query;
use yii\helpers\Json;

/**
 * This is the model class for table "zoho_contact".
 *
 * @property string $contact_id
 * @property string $remote_id
 * @property string $data
 * @property string $data_hash
 * @property string $update_frequency
 * @property string $update_at
 * @property string $create_at
 */
class Contact extends ActiveRecord
{
    const UPDATE_CONTACTS_LIMIT_PER_SESSION = 900;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contact}}';
    }

    public static function updateContacts()
    {
        Yii::info('Update invoices', LOG_CATEGORY);
        self::remove();

        $newContacts = (new Query())
            ->select(['cq.remote_id'])
            ->distinct()
            ->from(ContactQueue::tableName() . ' cq')
            ->leftJoin(self::tableName() . ' c', 'c.remote_id = cq.remote_id')
            ->where('c.remote_id IS NULL')
            ->limit(self::UPDATE_CONTACTS_LIMIT_PER_SESSION)
            ->column();

        $limit = self::UPDATE_CONTACTS_LIMIT_PER_SESSION - count($newContacts);
        if ( $limit >= 1 ) {
            $contactsToUpdate = (new Query())
                ->select(['cq.remote_id'])
                ->distinct()
                ->from(ContactQueue::tableName() . ' cq')
                ->leftJoin(self::tableName() . ' c', 'c.remote_id = cq.remote_id')
                ->where('c.remote_id IS NOT NULL')
                ->orderBy(['c.update_at' => SORT_ASC, 'c.update_frequency' => SORT_DESC])
                ->limit($limit)
                ->column();
        } else {
            $contactsToUpdate = [];
        }

        $contacts = array_merge($newContacts, $contactsToUpdate);
        foreach ($contacts as $contactId) {
            $fullContact = Yii::$app->zoho->contact($contactId);
            if ( self::updateContact($fullContact) ) {
                ContactQueue::deleteAll('remote_id = :remoteId', [':remoteId' => $contactId]);
            }
        }
    }

    private static function updateContact(array $contactArr)
    {
        Yii::info('Try to update contact', LOG_CATEGORY);
        if ( !isset($contactArr['contact_id']) ) {
            throw new Exception('Invalid contact parameters');
        }
        $contact = self::findOne(array('remote_id' => $contactArr['contact_id']));
        if ( !empty($contact) ) {
            $jsonData = Json::encode($contactArr);
            $dataHash = $contact->createDataHash($jsonData);

            if ( $dataHash != $contact->data_hash ) {
                $contact->data = $jsonData;
                $contact->data_hash = $dataHash;
                $contact->update_frequency = (int)$contact->update_frequency + 1;
            }

            $contact->update_at = new Expression('NOW()');
            if ( $contact->save() ) {
                Yii::info('Contact successfully updated', LOG_CATEGORY);
                return true;
            }
            Yii::error('Can not update contact. Contact remote id ' . $contact->remote_id, LOG_CATEGORY);
            return false;

        } else {
            Yii::info('The contact does not exist', LOG_CATEGORY);
            return self::createContact($contactArr);
        }
    }

    private static function createContact(array $contactArr)
    {
        Yii::info('Try to create new contact', LOG_CATEGORY);
        if ( !isset($contactArr['contact_id']) ) {
            throw new Exception('Invalid contact parameters');
        }

        $jsonData = Json::encode($contactArr);
        $contact = new Contact();
        $contact->remote_id = $contactArr['contact_id'];
        $contact->data = $jsonData;
        $contact->data_hash = $contact->createDataHash($jsonData);
        $contact->update_frequency = 0;
        $contact->create_at = new Expression('NOW()');

        if ( $contact->save() ) {
            Yii::info('New contact successfully created', LOG_CATEGORY);
            return true;
        }
        Yii::error('Can not create new contact', LOG_CATEGORY);
        return false;
    }

    public static function remove()
    {
        $deleteContacts = (new Query())
            ->select(['c.remote_id'])
            ->from(self::tableName() . ' c')
            ->leftJoin(ContactQueue::tableName() . ' cq', 'cq.remote_id = c.remote_id')
            ->where('cq.remote_id IS NULL')
            ->column();

        if ( !empty($deleteContacts) ) {
            self::deleteAll('remote_id IN (' . implode(',', $deleteContacts) . ')');
        }
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['remote_id', 'data', 'data_hash'], 'required'],
            [['data'], 'string'],
            [['update_frequency'], 'integer'],
            [['update_at', 'create_at'], 'safe'],
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
            'contact_id' => 'Contact ID',
            'remote_id' => 'Remote ID',
            'data' => 'Data',
            'data_hash' => 'Data hash',
            'update_frequency' => 'Update Frequency',
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
