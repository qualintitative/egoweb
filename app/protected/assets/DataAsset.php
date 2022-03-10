<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

class DataAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        '/www/css/dataTables.bootstrap4.min.css',
    ];
    public $js = [
        '/www/js/dataTables.bootstrap4.js',
        '/www/js/jquery.dataTables.js',
    ];
    public $jsOptions = [ 'position' => \yii\web\View::POS_HEAD ];

    public $depends = [
        'app\assets\AppAsset',
        ];
}
