<?php
/* @var $this SiteController */

$this->pageTitle=Yii::app()->name;
?>

<h1>Welcome to <i><?php echo CHtml::encode(Yii::app()->name); ?></i></h1>

<?php
    if(Yii::app()->user->isGuest)
        echo CHtml::link('Click here to log in', $this->createUrl("login"));
    else
        echo CHtml::link('Go to the admin section', $this->createUrl("/admin"));
?>