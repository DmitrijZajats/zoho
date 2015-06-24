<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "zoho_contact_queue".
 *
 * @property string $remote_id
 */
class ContactQueue extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%contact_queue}}';
    }

    public static function contactsList()
    {
        Yii::info('Get all account contacts', LOG_CATEGORY);
        $page = 1;
        do {
            $contacts = Yii::$app->zoho->contacts($page);

            $sqlData = [];
            $sql = 'INSERT INTO ' . self::tableName() . ' (remote_id) VALUES ';
            foreach ($contacts as $contact) {
                $sqlData[] = "({$contact['contact_id']})";
            }
            if ( !empty($sqlData) ) {
                $sql .= implode(',', $sqlData);
                $sql .= ' ON DUPLICATE KEY UPDATE remote_id = VALUES(remote_id)';
                Yii::$app->db->createCommand($sql)->execute();
            }
            $page += 1;
        } while( !empty($contacts) );
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
