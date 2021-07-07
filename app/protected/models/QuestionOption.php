<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "questionOption".
 *
 * @property int $id
 * @property int|null $active
 * @property int|null $studyId
 * @property int|null $questionId
 * @property string|null $name
 * @property string|null $value
 * @property int|null $ordering
 * @property int|null $otherSpecify
 * @property int|null $single
 */
class QuestionOption extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'questionOption';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active', 'studyId', 'questionId', 'ordering'], 'integer'],
            [['name', 'value'], 'string'],
            [[ 'otherSpecify', 'single'], 'boolean'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'active' => 'Active',
            'studyId' => 'Study ID',
            'questionId' => 'Question ID',
            'name' => 'Name',
            'value' => 'Value',
            'ordering' => 'Ordering',
            'otherSpecify' => 'Other Specify',
            'single' => 'Single',
        ];
    }
}
