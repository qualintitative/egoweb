<?php
$this->pageTitle = $study->name;
$expressionTypes = array(
    "_form_expression_text" => "Simple",
    "_form_expression_compound" => "Compound",
    "_form_expression_counting" => "Counting",
    "_form_expression_comparison" => "Comparison",
    "_form_expression_name_gen" => "Name Generator",
);
?>

<div class="col-md-6">
<h1>Expressions</h1>
<?php
    $form = $this->beginWidget('CActiveForm', array(
        'id'=>'new-expression',
        'enableAjaxValidation'=>false,
        'method'=>'GET',

    ));

    echo CHtml::hiddenField("studyId", $studyId);

    echo CHtml::dropdownlist(
        'form',
        '_form_expression_text',
        $expressionTypes,
        array('empty' => 'Choose One')
    );

    echo CHtml::ajaxButton ("New Expression",
    	CController::createUrl('ajaxload'),
    	array( 'update' => '#Expression', 'data'=>'js:$("#new-expression").serialize()', 'method'=>'get'),
    	array( 'id'=>uniqid(), 'live'=>false, "class"=>"btn btn-success btn-xs")
    );

    $this->endWidget();




?>
<br>
<?php
        $this->widget('zii.widgets.CListView', array(
    	'dataProvider'=>$dataProvider,
    	'itemView'=>'_view_expression_list',
    	'template'=>"{sorter}\n{items}\n{pager}",
    ));
    ?>
</div>

<div class="col-md-6">
<div id="Expression">
</div>
</div>
