<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "interviewers".
 *
 * @property int $id
 * @property int $studyId
 * @property int $interviewerId
 */
class Interviewer extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'interviewers';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['studyId', 'interviewerId'], 'required'],
            [['studyId', 'interviewerId'], 'integer'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'studyId' => 'Study ID',
            'interviewerId' => 'Interviewer ID',
        ];
    }
}
