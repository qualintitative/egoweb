<?php
use yii\helpers\Html;
use app\models\User;
use app\models\Interview;

?>


       

<div id="accordion" class="fill-page">
<div><h3>Multi-session Studies</h3></div>

    <?php foreach ($multiStudies as $index=>$study):?>
    <div class="card">
        <div class="card-header" id="heading-<?php echo $study->id; ?>">
            <h5 class="mb-0">
                <h3 class="btn btn-link btn-lg" data-toggle="collapse"
                    data-target="#collapse-<?php echo $study->id; ?>" aria-expanded="true"
                    aria-controls="collapse-<?php echo $study->id; ?>">
                    <?php echo $study->name; ?>
                </h3>
                (<?php
                    echo $multiIdQs[$study->name];
                ?>)
                <div class="btn-group float-right" role="group" aria-label="<?php echo $study->name; ?>">
                        <?php echo Html::a("Authoring", ["/authoring/" . $study->id], ["class"=>"btn btn-link btn-info text-light"]); ?>
                        <?php echo Html::a("Data Processing", ["/data/" . $study->id], ["class"=>"btn btn-link btn-secondary text-light"]); ?>
                    </div>
            </h5>
        </div>

        <div id="collapse-<?php echo $study->id; ?>" class="collapse"
            aria-labelledby="heading-<?php echo $study->id; ?>" data-parent="#accordion">
            <div class="card-body">
                <div class="row">
                <div class="col-sm-3" role="toolbar" aria-label="Toolbar with button groups">

                    <?php echo Html::a("Start new interview", ["/interview/" . $study->id . "#page/0"], ["class"=>"btn btn-link btn-primary text-light"]); ?>


                </div>
                <?php if (isset($interviews[$study->id]) && count($interviews[$study->id]) > 0): ?>
                    <div class="col-sm-9">
                <table id="study-<?php echo $study->id; ?>" class="table table-bordered table-list study-table">
                    <thead>
                    <tr><th class="bg-dark text-white">Continue incomplete interview</th></tr>
                </thead>
                    <tbody>
                    <?php foreach ($interviews[$study->id] as $interview): ?>
                    <tr><td><?php echo Html::a($egoIds[$interview->id], ["/interview/" . $study->id . "/" . $interview->id . "#page/" . $interview->completed ], ["class"=>"list-group-item list-group-item-action"]); ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                    </table>
                    </div>
                    <script>
$(function(){
    $('#study-<?php echo $study->id; ?>').DataTable( {paging: false, info: false,
        rowReorder: true,
        columnDefs: [
            { orderable: true, targets: 0 }
        ]
    });
});
</script>
                <?php endif; ?>
                    </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <div><br><h3>Single session Studies</h3></div>
    <?php foreach ($studies as $index=>$study):?>
    <div class="card">
        <div class="card-header" id="heading-<?php echo $study->id; ?>">
            <h5 class="mb-0">
                <h3 class="btn btn-link btn-lg" data-toggle="collapse"
                    data-target="#collapse-<?php echo $study->id; ?>" aria-expanded="true"
                    aria-controls="collapse-<?php echo $study->id; ?>">
                    <?php echo $study->name; ?>
                </h3>
                <div class="btn-group float-right" role="group" aria-label="<?php echo $study->name; ?>">
                        <?php echo Html::a("Authoring", ["/authoring/" . $study->id], ["class"=>"btn btn-link btn-info text-light"]); ?>
                        <?php echo Html::a("Data Processing", ["/data/" . $study->id], ["class"=>"btn btn-link btn-secondary text-light"]); ?>
                    </div>
            </h5>
        </div>

        <div id="collapse-<?php echo $study->id; ?>" class="collapse"
            aria-labelledby="heading-<?php echo $study->id; ?>" data-parent="#accordion">
            <div class="card-body">
                <div class="row">
                <div class="col-sm-3" role="toolbar" aria-label="Toolbar with button groups">

                    <?php echo Html::a("Start new interview", ["/interview/" . $study->id . "#page/0"], ["class"=>"btn btn-link btn-primary text-light"]); ?>


                </div>
                <?php if (isset($interviews[$study->id]) && count($interviews[$study->id]) > 0): ?>
                    <div class="col-sm-9">
                <table id="study-<?php echo $study->id; ?>" class="table table-bordered table-list study-table">
                    <thead>
                    <tr><th class="bg-dark text-white">Continue incomplete interview</th></tr>
                </thead>
                    <tbody>
                    <?php foreach ($interviews[$study->id] as $interview): ?>
                    <tr><td><?php echo Html::a($egoIds[$interview->id], ["/interview/" . $study->id . "/" . $interview->id . "#page/" . $interview->completed ], ["class"=>"list-group-item list-group-item-action"]); ?></td></tr>
                    <?php endforeach; ?>
                    </tbody>
                    </table>
                    </div>
                    <script>
$(function(){
    $('#study-<?php echo $study->id; ?>').DataTable( {paging: false, info: false,
        rowReorder: true,
        columnDefs: [
            { orderable: true, targets: 0 }
        ]
    });
});
</script>
                <?php endif; ?>
                    </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

</div>

<div class="card fill-page">
    <div class="card-header">
    <?= Html::beginForm(['/authoring/create'], 'post', [ 'id'=>'create', "class"=>"form-inline col-lg-6"]) ?>

  <div class="form-group col-sm-4 mb-3">
    <label><b>Create New Study</b></label>
  </div>
  <div class="form-group col-md-6 mb-2">
    <input type="text" id="Study_name" name="Study[name]" class="row form-control col-md-12 mb-2" placeholder="Study Name">
  </div>
  <button type="submit" class="btn btn-primary mb-3">Create</button>
  <?= Html::endForm() ?>
    </div>
</div>
<div class="site-index">
    <div class="body-content">
       

        <div class="row">
        <?php if (Yii::$app->user->identity->isAdmin()): ?>

            <div class="card col-sm-6">
                <div class="card-body">

                    <h3><?=Html::a('Import &amp; Export Studies', ["/import-export"])?></h3>
                    <p>
                        Save study and respondent data as files or
                        transfer to another server.<br>
                    </p>

                </div>
            </div>
            <?php endif; ?>

            <?php if (Yii::$app->user->identity->permissions >= 3): ?>

            <div class="card col-sm-6">
                <div class="card-body">

                    <h3><?=Html::a('Alter Matching', ["/dyad"])?></h3>
                    <p>
                        Match alters from related interviews<br><br>
                    </p>
                </div>
            </div>
            <?php endif;?>

            <?php if (Yii::$app->user->identity->isSuperAdmin()): ?>
            <div class="card col-sm-6">
                <div class="card-body">
                    <h3><?=Html::a('User Admin', ["/admin/user"])?></h3>
                    <p>
                        Add new users.<br><br>
                    </p>
                </div>
            </div>
            <?php endif; ?>



            <div class="card col-sm-6">
                <div class="card-body">
                    <h3><?=Html::a('Logout', ["/site/logout"])?></h3>
                    <p>
                        Logout of Admin Mode.<br><br>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
$this->registerAssetBundle(\yii\web\JqueryAsset::className(), \yii\web\View::POS_HEAD);
use app\assets\DataAsset;
DataAsset::register($this);
?>
