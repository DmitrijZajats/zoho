<?php
namespace app\models;

use Yii;
use yii\base\Exception;
use yii\db\ActiveRecord;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "zoho_contact_info".
 *
 * @property int $info_id
 * @property int $local_contact_id
 * @property string $contact_id
 * @property string $contact_name
 * @property string $company_name
 * @property string $contact_person_id
 * @property string $salutation
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 */
class ContactInfo extends ActiveRecord{
    /**
     * @inheritdoc
     */
    public static function tableName(){
        return '{{%contact_info}}';
    }

    /**
     * @inheritdoc
     */
    public function rules(){
        return [
            [['local_contact_id'], 'required'],
            [['local_contact_id'], 'integer'],
            [['salutation', 'first_name', 'last_name', 'debiteurnummer'], 'safe'],
            [['contact_id', 'contact_name', 'company_name', 'contact_person_id', 'email'], 'string', 'max' => 255]
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels(){
        return [
            'info_id' => 'Contact Info ID',
            'local_contact_id' => 'Local Contact ID',
            'contact_id' => 'Contact ID',
            'contact_name' => 'Contact Name',
            'company_name' => 'Company Name',
            'contact_person_id' => 'Contact Person ID',
            'salutation' => 'Salutation',
            'first_name' => 'First Name',
            'last_name' => 'Last Name',
            'email' => 'Email',
            'debiteurnummer'=> 'Debiteurnummer'
        ];
    }

    /**
     * @param array $contactInfo
     * @param $contactPersonId
     * @return array
     */
    private static function _getPersonInfo(array $contactInfo, $contactPersonId){
        $personsInfo=ArrayHelper::getValue($contactInfo, 'contact_persons', []);

        foreach($personsInfo as $personInfo){
            if(ArrayHelper::getValue($personInfo, 'contact_person_id')==$contactPersonId){
                return $personInfo;
            }
        }

        return array();
    }

    /**
     * @param array $contactInfo
     * @param $fieldLabel
     * @return null
     */
    private static function _getCustomField(array $contactInfo, $fieldLabel){
        $customFields=ArrayHelper::getValue($contactInfo, 'custom_fields', []);

        foreach($customFields as $field){
            if($field['label']!=$fieldLabel){
                continue;
            }

            return $field['value'];
        }

        return null;
    }

    /**
     * @param array $contactInfo
     * @return $this
     */
    public function loadFromInfo(array $contactInfo){
        $this->load($contactInfo, '');

        $this->contact_person_id=ArrayHelper::getValue($contactInfo, 'primary_contact_id');

        $contactPersonInfo=self::_getPersonInfo($contactInfo, $this->contact_person_id);

        if(!empty($contactPersonInfo)){
            $this->salutation=ArrayHelper::getValue($contactPersonInfo, 'salutation');
            $this->first_name=ArrayHelper::getValue($contactPersonInfo, 'first_name');
            $this->last_name=ArrayHelper::getValue($contactPersonInfo, 'last_name');
            $this->email=ArrayHelper::getValue($contactPersonInfo, 'email');
        }

        $this->debiteurnummer=self::_getCustomField($contactInfo, 'Debiteurnummer:');

        return $this;
    }

    /**
     * @param $localContactId
     * @param array $contactInfo
     * @return bool
     */
    public static function createContactInfo($localContactId, array $contactInfo){
        Yii::info("Try to create contact info for [{$localContactId}]", LOG_CATEGORY);

        $info=new ContactInfo();
        $info->local_contact_id=$localContactId;
        $info->loadFromInfo($contactInfo);

        if( $info->save() ){
            Yii::info('Contact info successfully created', LOG_CATEGORY);
            return true;
        }

        Yii::error('Can not create contact info', LOG_CATEGORY);
        return false;
    }

    /**
     * @param $localContactId
     * @param array $contactInfo
     * @return bool
     * @throws \yii\base\Exception
     */
    public static function updateContactInfo($localContactId, array $contactInfo){
        Yii::info("Try to update contact info for [{$localContactId}]", LOG_CATEGORY);

        $contact = self::findOne(['local_contact_id' => $localContactId]);
        if( empty($contact) ){
            throw new Exception("Contact info for local contact [{$localContactId}] was not found");
        }

        $contact->loadFromInfo($contactInfo);

        if( $contact->save(false) ){
            Yii::info('Contact info successfully updated', LOG_CATEGORY);

            return true;
        }

        Yii::error('Can not update contact info', LOG_CATEGORY);
        return false;
    }
} 