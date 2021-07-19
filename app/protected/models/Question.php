<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "question".
 *
 * @property int $id
 * @property int|null $active
 * @property string|null $title
 * @property string|null $prompt
 * @property string|null $preface
 * @property string|null $citation
 * @property string|null $subjectType
 * @property string|null $answerType
 * @property int|null $askingStyleList
 * @property int|null $ordering
 * @property int|null $otherSpecify
 * @property string|null $noneButton
 * @property string|null $allButton
 * @property string|null $pageLevelDontKnowButton
 * @property string|null $pageLevelRefuseButton
 * @property int|null $dontKnowButton
 * @property int|null $refuseButton
 * @property string|null $allOptionString
 * @property string|null $uselfExpression
 * @property string|null $minLimitType
 * @property int|null $minLiteral
 * @property string|null $minPrevQues
 * @property string|null $maxLimitType
 * @property int|null $maxLiteral
 * @property string|null $maxPrevQues
 * @property int|null $minCheckableBoxes
 * @property int|null $maxCheckableBoxes
 * @property int|null $withListRange
 * @property string|null $listRangeString
 * @property int|null $minListRange
 * @property int|null $maxListRange
 * @property int|null $timeUnits
 * @property int|null $symmetric
 * @property int|null $keepOnSamePage
 * @property int|null $studyId
 * @property int|null $answerReasonExpressionId
 * @property int|null $networkRelationshipExprId
 * @property string|null $networkParams
 * @property int|null $networkNColorQId
 * @property int|null $networkNSizeQId
 * @property int|null $networkEColorQId
 * @property int|null $networkESizeQId
 * @property string|null $useAlterListField
 * @property string|null $javascript
 * @property int|null $restrictList
 * @property int|null $autocompleteList
 * @property int|null $prefillList
 */
class Question extends \yii\db\ActiveRecord
{
    const SUBJECTTYPES = [
        'EGO',
        'NAME_GENERATOR',
        'ALTER',
        'ALTER_PAIR',
        'NETWORK',
        'MERGE_ALTER',
        'PREVIOUS_ALTER',
    ];
    const ANSWERTYPES = [
        'TEXTUAL',
        'NUMERICAL',
        'MULTIPLE_SELECTION',
        'DATE',
        'TIME_SPAN',
        'TEXTUAL_PP',
        'NO_RESPONSE',
    ];
    const EGOID_ANSWERTYPES = [
        'TEXTUAL',
        'NUMERICAL',
        'MULTIPLE_SELECTION',
        'DATE',
        'TIME_SPAN',
        'TEXTUAL_PP',
        'RANDOM_NUMBER',
        'STORED_VALUE',
    ];
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'question';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['active', 'ordering', 'otherSpecify', 'minLiteral', 'maxLiteral', 'minCheckableBoxes', 'maxCheckableBoxes', 'minListRange', 'maxListRange', 'timeUnits', 'symmetric', 'studyId', 'answerReasonExpressionId', 'networkRelationshipExprId', 'networkNColorQId', 'networkNSizeQId', 'networkEColorQId', 'networkESizeQId'], 'integer'],
            [['title', 'prompt', 'preface', 'citation', 'subjectType', 'answerType', 'pageLevelDontKnowButton', 'pageLevelRefuseButton', 'allOptionString', 'uselfExpression', 'minLimitType', 'minPrevQues', 'maxLimitType', 'maxPrevQues', 'listRangeString', 'networkParams', 'useAlterListField', 'javascript'], 'string'],
            [['askingStyleList', 'dontKnowButton', 'refuseButton', 'restrictList', 'autocompleteList', 'prefillList', 'keepOnSamePage'], 'boolean'],
            ['preface','default', 'value'=>""],
            [['allButton', 'noneButton','withListRange'], 'default', 'value' => 0],
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
            'title' => 'Title',
            'prompt' => 'Prompt',
            'preface' => 'Preface',
            'citation' => 'Citation',
            'subjectType' => 'Subject Type',
            'answerType' => 'Answer Type',
            'askingStyleList' => 'Asking Style List',
            'ordering' => 'Ordering',
            'otherSpecify' => 'Other Specify',
            'noneButton' => 'None Button',
            'allButton' => 'All Button',
            'pageLevelDontKnowButton' => 'Page Level Dont Know Button',
            'pageLevelRefuseButton' => 'Page Level Refuse Button',
            'dontKnowButton' => 'Dont Know Button',
            'refuseButton' => 'Refuse Button',
            'allOptionString' => 'All Option String',
            'uselfExpression' => 'Uself Expression',
            'minLimitType' => 'Min Limit Type',
            'minLiteral' => 'Min Literal',
            'minPrevQues' => 'Min Prev Ques',
            'maxLimitType' => 'Max Limit Type',
            'maxLiteral' => 'Max Literal',
            'maxPrevQues' => 'Max Prev Ques',
            'minCheckableBoxes' => 'Min Checkable Boxes',
            'maxCheckableBoxes' => 'Max Checkable Boxes',
            'withListRange' => 'With List Range',
            'listRangeString' => 'List Range String',
            'minListRange' => 'Min List Range',
            'maxListRange' => 'Max List Range',
            'timeUnits' => 'Time Units',
            'symmetric' => 'Symmetric',
            'keepOnSamePage' => 'Keep On Same Page',
            'studyId' => 'Study ID',
            'answerReasonExpressionId' => 'Answer Reason Expression ID',
            'networkRelationshipExprId' => 'Network Relationship Expr ID',
            'networkParams' => 'Network Params',
            'networkNColorQId' => 'Network N Color Q ID',
            'networkNSizeQId' => 'Network N Size Q ID',
            'networkEColorQId' => 'Network E Color Q ID',
            'networkESizeQId' => 'Network E Size Q ID',
            'useAlterListField' => 'Use Alter List Field',
            'javascript' => 'Javascript',
            'restrictList' => 'Restrict List',
            'autocompleteList' => 'Autocomplete List',
            'prefillList' => 'Prefill List',
        ];
    }
}
