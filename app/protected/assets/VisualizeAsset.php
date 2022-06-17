<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\assets;

use yii\web\AssetBundle;

class VisualizeAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
    ];
    public $js = [
'/www/js/1.0.3/sigma.min.js',
'/www/js/1.0.3/plugins/sigma.plugins.dragNodes.js',
'/www/js/1.0.3/plugins/shape-library.js',
'/www/js/1.0.3/plugins/sigma.renderers.customShapes.min.js',
'/www/js/1.0.3/plugins/sigma.layout.forceAtlas2.min.js',
'/www/js/plugins/sigma.notes.js',

    ];
    public $jsOptions = [ 'position' => \yii\web\View::POS_HEAD ];

    public $depends = [
        'app\assets\AppAsset',
        ];
}
