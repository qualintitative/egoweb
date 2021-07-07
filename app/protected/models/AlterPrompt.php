<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "alterPrompt".
 *
 * @property int $id
 * @property int $studyId
 * @property int $afterAltersEntered
 * @property string $display
 * @property int|null $questionId
 */
class AlterPrompt extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alterPrompt';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['studyId', 'afterAltersEntered', 'display'], 'required'],
            [['studyId', 'afterAltersEntered', 'questionId'], 'integer'],
            [['display'], 'string'],
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
            'afterAltersEntered' => 'After Alters Entered',
            'display' => 'Display',
            'questionId' => 'Question ID',
        ];
    }
}
