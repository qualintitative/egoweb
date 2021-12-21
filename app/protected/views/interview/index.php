<?php
use yii\helpers\Html;
use app\models\Interview;

?>
<div id="accordion">
<div class="card bg-dark text-light">
    <div class="card-header">
        Studies
</div>
</div>
<?php foreach (Yii::$app->user->identity->studies as $study):?>
    <div class="card">
    <div class="card-header" id="heading-<?php echo $study->id; ?>">
      <h5 class="mb-0">
        <button class="btn btn-link btn-lg" data-toggle="collapse" data-target="#collapse-<?php echo $study->id; ?>" aria-expanded="true" aria-controls="collapse-<?php echo $study->id; ?>">
          <?php echo $study->name; ?>
        </button>
      </h5>
    </div>

    <div id="collapse-<?php echo $study->id; ?>" class="collapse" aria-labelledby="heading-<?php echo $study->id; ?>" data-parent="#accordion">
      <div class="card-body">
      <div class="btn-toolbar justify-content-between" role="toolbar" aria-label="Toolbar with button groups">

      <?php echo Html::a("Start new interview", ["/interview/" . $study->id . "#page/0"], ["class"=>"btn btn-link btn-primary text-light"]); ?>

      <div class="btn-group float-right" role="group" aria-label="Basic example">
      <?php echo Html::a("Authoring", ["/authoring/" . $study->id], ["class"=>"btn btn-link btn-secondary text-light"]); ?>
      <?php echo Html::a("Data Processing", ["/data/" . $study->id], ["class"=>"btn btn-link btn-secondary text-light"]); ?>

</div>
</div>
<?php
$interviews = Interview::findAll([
    "studyId"=>$study->id
]);
?>
<?php if (count($interviews) > 0): ?>
    <div class="list-group">
<?php foreach ($interviews as $interview): ?>
    <?php if ($interview->completed > -1): ?>
    <?php echo Html::a($interview->egoId, ["/interview/" . $study->id . "/" . $interview->id . "#page/" . $interview->completed ], ["class"=>"list-group-item list-group-item-action"]); ?>
<?php endif; ?>
    <?php endforeach; ?>
</div>
<?php endif; ?>
    </div>
    </div>
  </div>
  <?php endforeach; ?>
  
</div>