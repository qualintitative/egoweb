<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "matchedAlters".
 *
 * @property int $id
 * @property int|null $studyId
 * @property int|null $alterId1
 * @property int|null $alterId2
 * @property string $matchedName
 * @property int|null $interviewId1
 * @property int|null $interviewId2
 * @property int|null $userId
 * @property string|null $notes
 */
class MatchedAlters extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'matchedAlters';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['studyId', 'alterId1', 'alterId2', 'interviewId1', 'interviewId2', 'userId'], 'integer'],
            [['matchedName'], 'required'],
            [['matchedName', 'notes'], 'string', 'max' => 255],
        ];
    }

    public function getMatchId()
    {
        $interview1 = Interview::findOne($this->interviewId1)->egoId;
        $interview2 = Interview::findOne($this->interviewId2)->egoId;

        if($this->interviewId1 > $this->interviewId2)
            return $interview2  . "_" . $interview1;
        else
            return $interview1  . "_" . $interview2;
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'studyId' => 'Study ID',
            'alterId1' => 'Alter Id1',
            'alterId2' => 'Alter Id2',
            'matchedName' => 'Matched Name',
            'interviewId1' => 'Interview Id1',
            'interviewId2' => 'Interview Id2',
            'userId' => 'User ID',
            'notes' => 'Notes',
        ];
    }
}
