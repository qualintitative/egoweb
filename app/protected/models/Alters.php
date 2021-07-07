<?php

namespace app\models;

use Yii;
use app\helpers\Tools;

/**
 * This is the model class for table "alters".
 *
 * @property int $id
 * @property int|null $active
 * @property string|null $ordering
 * @property string|null $name
 * @property string $interviewId
 * @property string|null $alterListId
 * @property string|null $nameGenQIds
 */
class Alters extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'alters';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active'], 'integer'],
            [['name', 'interviewId', 'nameGenQIds'], 'string'],
            [['interviewId'], 'required'],
            [['ordering', 'alterListId'], 'string', 'max' => 500],
        ];
    }

    public function beforeSave($insert)
    {
        if($this->name != "")
            $this->name = Tools::encrypt( $this->name );

        return parent::beforeSave($insert);
    }

    public function afterFind()
    {
        if(strlen($this->name) >= 8)
            $this->name = Tools::decrypt( $this->name );
    
        return parent::afterFind();
    }

    public static function sortOrder($ordering, $interviewId, $nameGenQId)
	{
        $models = Alters::find()
        ->where(new \yii\db\Expression("FIND_IN_SET(" . $interviewId .", interviewId) AND FIND_IN_SET($nameGenQId, nameGenQIds)"))
        ->all();
		foreach($models as $index=>$model){
			if(is_numeric($model->ordering)){
				$nGorder = array($nameGenQId=>$index);
				$model->ordering = json_encode($nGorder);
				$model->save();
			}
		}
        $models = Alters::find()
        ->where(new \yii\db\Expression("FIND_IN_SET(" . $interviewId .", interviewId) AND JSON_EXTRACT(ordering, '$.\"$nameGenQId\"') > $ordering"))
        ->all();
        foreach ($models as $index=>$model) {
            Alters::moveUp($model->id, $nameGenQId);
        }
	}

    public static function moveUp($id, $nameGenQId)
	{
		$model = Alters::findOne($id);
		$nGorder = json_decode($model->ordering, true);
		if($model && $nGorder[$nameGenQId] > 0){
            $old_model = Alters::find()
            ->where(new \yii\db\Expression("JSON_EXTRACT(ordering, '$.$nameGenQId') = " . ($nGorder[$nameGenQId]-1)))
            ->one();
			if($old_model){
				$oldnGorder = json_decode($old_model->ordering, true);
				$oldnGorder[$nameGenQId] = $nGorder[$nameGenQId];
				$old_model->ordering = json_encode($oldnGorder);
				$old_model->save();
			}
			$nGorder[$nameGenQId]--;
			$model->ordering = json_encode($nGorder);
			$model->save();
		}
	}

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'active' => 'Active',
            'ordering' => 'Ordering',
            'name' => 'Name',
            'interviewId' => 'Interview ID',
            'alterListId' => 'Alter List ID',
            'nameGenQIds' => 'Name Gen Q Ids',
        ];
    }
}
