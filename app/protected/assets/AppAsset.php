<?php

namespace app\assets;

use yii\web\AssetBundle;

/**
 * Main protected application asset bundle.
 */
class AppAsset extends AssetBundle
{
    public $basePath = '@webroot';
    public $baseUrl = '@web';
    public $css = [
        'css/summernote-bs4.min.css',
        'www/css/main.css',
        'www/css/all.css',
        'www/css/bootstrap-vue-icons.min.css',
        'www/css/bootstrap-vue.min.css',
    ];
    public $js = [
        'www/js/egoweb.js',
       'js/vue.js',
       'js/vue-router.js',
        'js/summernote-bs4.js',
        'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-vue/2.21.2/bootstrap-vue.min.js',
        'https://cdnjs.cloudflare.com/ajax/libs/bootstrap-vue/2.21.2/bootstrap-vue-icons.min.js',
        'www/js/Sortable.min.js',
    ];
    public $jsOptions = [ 'position' => \yii\web\View::POS_HEAD ];

    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap4\BootstrapPluginAsset',
        'yii\bootstrap4\BootstrapAsset',
    ];
}
