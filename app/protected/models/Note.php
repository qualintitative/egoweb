<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "notes".
 *
 * @property int $id
 * @property int $interviewId
 * @property int $expressionId
 * @property string|null $alterId
 * @property string|null $notes
 */
class Note extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'notes';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['interviewId', 'expressionId'], 'required'],
            [['interviewId', 'expressionId'], 'integer'],
            [['notes'], 'string'],
            [['alterId'], 'string', 'max' => 64],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'interviewId' => 'Interview ID',
            'expressionId' => 'Expression ID',
            'alterId' => 'Alter ID',
            'notes' => 'Notes',
        ];
    }
}
