<?php

namespace app\models;

use Yii;
use app\helpers\Tools;

/**
 * This is the model class for table "answer".
 *
 * @property int $id
 * @property int|null $active
 * @property int|null $questionId
 * @property int|null $interviewId
 * @property int|null $alterId1
 * @property int|null $alterId2
 * @property string|null $value
 * @property string|null $otherSpecifyText
 * @property string|null $skipReason
 * @property int|null $studyId
 * @property string|null $questionType
 * @property string|null $answerType
 */
class Answer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'answer';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active', 'questionId', 'interviewId', 'alterId1', 'alterId2', 'studyId'], 'integer'],
            [['value', 'otherSpecifyText', 'skipReason', 'questionType', 'answerType'], 'string'],
        ];
    }

    public function beforeSave($insert)
    {
        if($this->value != "")
            $this->value = Tools::encrypt( $this->value );
        if($this->otherSpecifyText != "")
            $this->otherSpecifyText = Tools::encrypt( $this->otherSpecifyText );

        return parent::beforeSave($insert);
    }

    public function afterFind()
    {
        if(strlen($this->value) >= 8)
            $this->value = Tools::decrypt( $this->value );
        if(strlen($this->otherSpecifyText) >= 8)
            $this->otherSpecifyText = Tools::decrypt($this->otherSpecifyText );

        return parent::afterFind();
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'active' => 'Active',
            'questionId' => 'Question ID',
            'interviewId' => 'Interview ID',
            'alterId1' => 'Alter Id1',
            'alterId2' => 'Alter Id2',
            'value' => 'Value',
            'otherSpecifyText' => 'Other Specify Text',
            'skipReason' => 'Skip Reason',
            'studyId' => 'Study ID',
            'questionType' => 'Question Type',
            'answerType' => 'Answer Type',
        ];
    }
}
