<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "graphs".
 *
 * @property int $id
 * @property int $interviewId
 * @property int $expressionId
 * @property string $nodes
 * @property string $params
 */
class Graph extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'graphs';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['interviewId', 'expressionId', 'nodes', 'params'], 'required'],
            [['interviewId', 'expressionId'], 'integer'],
            [['nodes', 'params'], 'string'],
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
            'nodes' => 'Nodes',
            'params' => 'Params',
        ];
    }
}
