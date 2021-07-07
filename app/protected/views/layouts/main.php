<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use yii\bootstrap4\Nav;
use yii\bootstrap4\NavBar;
use yii\widgets\Breadcrumbs;
use app\assets\AppAsset;
use app\models\Study;
use common\widgets\Alert;
AppAsset::register($this);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="en" ng-app="egowebApp">

<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php $this->registerCsrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
</head>

<body>
    <?php $this->beginBody() ?>
    <?php
                if (!Yii::$app->user->isGuest) {
                    $logout = Html::beginForm(['/site/logout'], 'post')
            . Html::submitButton(
                'Logout (' . Yii::$app->user->identity->name .')',
                ['class' => 'btn btn-link logout']
            )
            . Html::endForm();
                }
    
?>
 <nav class="navbar navbar-expand-md navbar-dark bg-dark">
                <img src="/favicon.ico" width="32" height="32" alt="">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo01"
                    aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
                    <?php echo $this->title; ?>
                </button>
                <button class="navbar-toggler nav-right ml-auto" type="button" data-toggle="collapse"
                    data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false"
                    aria-label="Toggle navigation">
                    <span class="navbar-toggler-icon"></span>
                </button>
                <?php if (Yii::$app->controller->id == 'interview' && Yii::$app->controller->action->id == 'view'): ?>

                <a class="ml-2 navbar-brand d-none d-md-block" href="/admin"><?php echo $this->title; ?></a>
                <?php else: ?>
                <a class="ml-2 navbar-brand d-none d-md-block" href="/admin">Egoweb 2.0</a>
                <?php endif; ?>
                <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                    <ul class="navbar-nav">
                        <li id="navbox" class="nav-item dropdown">
                            <?php if (Yii::$app->controller->id == 'interview' && Yii::$app->controller->action->id == 'view'): ?>
                            <a class="nav-link dropdown-toggle" href="http://example.com" id="questionTitle"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink"
                                class="dropdown-menu" id="second"></ul>
                            <?php else: ?>
                            <a class="navbar-brand" href="#"><?php echo $this->title; ?></a>
                            <?php endif; ?>

                        </li>
                    </ul>
                </div>

                <?php if (!Yii::$app->user->isGuest): ?>

                <div class="collapse navbar-collapse" id="navbarTogglerDemo02">

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="http://example.com" id="navbarDropdownMenuLink"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                <?php echo Yii::$app->controller->id; ?>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right" aria-labelledby="navbarDropdownMenuLink">
                                <li class="dropdown-submenu"><a class="dropdown-item" href="/admin">Admin</a></li>
                                <li class="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#">New
                                        Interview</a>
                                    <ul class="dropdown-menu">
                                        <?php foreach(Yii::$app->user->identity->studies as $study):?>
                                        <li><?php echo Html::a(substr($study->name,0,24), ["/interview/" . $study->id . "#/page/0"], ['class'=>'dropdown-item']); ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <li class="dropdown-submenu"><a class="dropdown-item dropdown-toggle"
                                        href="#">Authoring</a>
                                    <ul class="dropdown-menu">
                                        <?php foreach(Yii::$app->user->identity->studies as $study):?>
                                        <li><?php echo Html::a(substr($study->name,0,24), ["/authoring/" . $study->id], ['class'=>'dropdown-item']); ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#">Data
                                        Processing</a>

                                    <ul class="dropdown-menu">
                                        <?php foreach(Yii::$app->user->identity->studies as $study):?>
                                        <li><?php echo Html::a(substr($study->name,0,24), ["/data/" . $study->id], ['class'=>'dropdown-item']); ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu"><a class="dropdown-item" href="/importexport">Import /
                                        Export</a></li>
                                <li class="dropdown-submenu"><a class="dropdown-item" href="/importexport">User
                                        Admin</a></li>
                                <li class="dropdown-submenu"><a class="dropdown-item" href="/importexport">Mobile</a>
                                </li>

                                <li class="dropdown-submenu"><?php echo $logout; ?></li>
                            </ul>
                        </li>
                    </ul>


                </div>
                <?php endif; ?>
            </nav>
    <div class="container-lg">

        <?= Breadcrumbs::widget([
            'links' => isset($this->params['breadcrumbs']) ? $this->params['breadcrumbs'] : [],
        ]) ?>
        <main role="main">

        <?= Alert::widget() ?>

            <?= $content ?>

        </main>

    </div>

    <footer class="footer">
        <div class="container">

        </div>
    </footer>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>