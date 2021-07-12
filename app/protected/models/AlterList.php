<?php

namespace app\models;

use Yii;
use app\helpers\Tools;

/**
 * This is the model class for table "alterList".
 *
 * @property int $id
 * @property int $studyId
 * @property string|null $name
 * @property string|null $email
 * @property int $ordering
 * @property int $interviewerId
 * @property string|null $nameGenQIds
 */
class AlterList extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alterList';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['studyId', 'ordering'], 'required'],
            [['studyId', 'ordering'], 'integer'],
            [['name', 'email',"nameGenQIds"], 'string'],
            [['interviewerId'],'default','value'=>0],
        ];
    }

    public function beforeSave($insert)
    {
        if($this->name != "")
            $this->name = Tools::encrypt( $this->name );
        if($this->email != "")
            $this->email = Tools::encrypt( $this->email );

        return parent::beforeSave($insert);
    }

    public function afterFind()
    {
        if(strlen($this->name) >= 8)
            $this->name = Tools::decrypt( $this->name );
        if(strlen($this->email) >= 8)
            $this->email = Tools::decrypt( $this->email );
    
        return parent::afterFind();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'studyId' => 'Study ID',
            'name' => 'Name',
            'email' => 'Email',
            'ordering' => 'Ordering',
            'interviewerId' => 'Interviewer ID',
            'nameGenQIds' => 'Name Gen Q Ids',
        ];
    }
}
