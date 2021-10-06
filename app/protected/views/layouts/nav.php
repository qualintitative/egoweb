<?php
use yii\bootstrap4\NavBar;
use yii\bootstrap4\Nav;
echo Nav::widget([
    'items' => [
        [
            'label' => 'Settings', 
            'url' => ['/authoring/'  . $study['id']],
            'active' => in_array(\Yii::$app->controller->action->id, ['index']),

        ],
        [
            'label' => 'Ego ID', 
            'url' => ['/authoring/ego_id/'  . $study['id']],
            'active' => in_array(\Yii::$app->controller->action->id, ['ego_id']),

        ],
        [
            'label' => 'Questions', 
            'url' => ['/authoring/questions/'  . $study['id']],
            'active' => in_array(\Yii::$app->controller->action->id, ['questions']),

        ],
        [
            'label' => 'Expressions', 
            'url' => ['/authoring/expressions/'  . $study['id']],
            'active' => in_array(\Yii::$app->controller->action->id, ['expressions']),

        ],
        [
            'label' => 'Users & Participants', 
            'url' => ['/authoring/participants/'  . $study['id']],
            'active' => in_array(\Yii::$app->controller->action->id, ['participants']),

        ],
    ],
    'options' => ['class' => 'bg-dark nav nav-pills flex-column flex-sm-row nav-justified mb-3 authoring-nav fill-page'],
]);
?>