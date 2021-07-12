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

    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
                <img src="/favicon.ico" width="32" height="32" alt="">
                <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarTogglerDemo01"
                    aria-controls="navbarTogglerDemo01" aria-expanded="false" aria-label="Toggle navigation">
                    <?php echo $this->title; ?>
                </button>
                <button class="navbar-toggler nav-right ml-auto" type="button" data-toggle="collapse"
                    data-target="#navbarTogglerDemo02" aria-controls="navbarTogglerDemo02" aria-expanded="false"
                    aria-label="Toggle navigation" onclick='$("#mainNav").toggle()'>
                    <span class="navbar-toggler-icon"></span>
                </button>

                <a class="ml-3 navbar-brand d-none d-md-block"><?php echo $this->title; ?></a>
           
                <?php if (!Yii::$app->user->isGuest): ?>

                <div class="collapse navbar-collapse" id="navbarTogglerDemo01">
                    <ul class="navbar-nav">
                        <li id="navbox" class="nav-item dropdown">

                            <?php if (Yii::$app->controller->id == 'interview' && Yii::$app->controller->action->id == 'view'): ?>
                            <a class="nav-link dropdown-toggle" href="http://example.com" id="questionTitle"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                            </a>
                            <ul class="dropdown-menu dropdown-menu-right bg-dark" aria-labelledby="navbarDropdownMenuLink"
                                 id="second"></ul>
                            <?php else: ?>
                            <?php endif; ?>

                        </li>
                    </ul>

                </div>
                <?php endif; ?>

                <?php if (!Yii::$app->user->isGuest): ?>

                <div class="collapse navbar-collapse" id="navbarTogglerDemo02">

                    <ul class="navbar-nav ml-auto">
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="http://example.com" id="navbarDropdownMenuLink"
                                data-toggle="dropdown" aria-haspopup="true" aria-expanded="true">
                                <?php echo Yii::$app->controller->id; ?>
                            </a>
                            <ul id="mainNav" class="dropdown-menu dropdown-menu-right bg-dark" aria-labelledby="navbarDropdownMenuLink">
                                <li class="dropdown-submenu"><a class="dropdown-item" href="/admin">Admin</a></li>
                                <li class="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#">New
                                        Interview</a>
                                    <ul class="dropdown-menu bg-dark">
                                        <?php foreach(Yii::$app->user->identity->studies as $study):?>
                                        <li><?php echo Html::a(substr($study->name,0,24), ["/interview/" . $study->id . "#/page/0"], ['class'=>'dropdown-item']); ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                <li class="dropdown-submenu"><a class="dropdown-item dropdown-toggle"
                                        href="#">Authoring</a>
                                    <ul class="dropdown-menu bg-dark">
                                        <?php foreach(Yii::$app->user->identity->studies as $study):?>
                                        <li><?php echo Html::a(substr($study->name,0,24), ["/authoring/" . $study->id], ['class'=>'dropdown-item']); ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu"><a class="dropdown-item dropdown-toggle" href="#">Data
                                        Processing</a>

                                    <ul class="dropdown-menu bg-dark">
                                        <?php foreach(Yii::$app->user->identity->studies as $study):?>
                                        <li><?php echo Html::a(substr($study->name,0,24), ["/data/" . $study->id], ['class'=>'dropdown-item']); ?>
                                        </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </li>
                                <li class="dropdown-submenu"><a class="dropdown-item" href="/import-export">Import /
                                        Export</a></li>

                                        <li class="dropdown-submenu"><a class="dropdown-item" href="/dyad">Alter Match</a></li>
                                <li class="dropdown-submenu"><a class="dropdown-item" href="/admin/user">User
                                        Admin</a></li>
                                <li class="dropdown-submenu"><a class="dropdown-item" href="/site/logout">Logout</a>
                                </li>

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

    <footer class="footer footer-copyright">
        <div class="container">
            <?php if (Yii::$app->controller->id != 'interview'): ?>
            EgoWeb Server [ Version <?php echo Yii::$app->params['version']; ?> ]
            <?php endif; ?>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>

</html>
<?php $this->endPage() ?>