<?php
use yii\helpers\Html;

?>

<?php echo Html::beginForm('/data/savenote', 'post', array(
    'id'=>'note-form',
));?>

<div class="form-group">
	<label class="control-label">
	<?php
    if (is_numeric($model->alterId)) {
        echo $model->alter->name;
    } else {
        echo str_replace("graphNote-", "", $model->alterId);
    }
    ?>
	</label>
<?php echo $model->notes; ?>
	<?php echo Html::textArea('Note[notes]', $model->notes, array('class'=>'form-control', 'placeholder'=>'notes')); ?>
</div>

<?php echo Html::input('hidden', 'Note[id]', $model->id); ?>
<?php echo Html::input('hidden', 'Note[interviewId]', $model->interviewId); ?>
<?php echo Html::input('hidden', 'Note[expressionId]', $model->expressionId); ?>
<?php echo Html::input('hidden', 'Note[alterId]', $model->alterId); ?>

<button class="btn btn-primary pull-right" onclick="saveNote();return false;">Save Note</button>
<?php if (!$model->isNewRecord): ?>
<button class="btn btn-danger pull-right" onclick="deleteNote();return false;" style="margin-right:10px;">Delete Note</button>
<?php endif; ?>

<?php echo Html::endForm(); ?>