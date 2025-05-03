<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\modules\admin\assets;

use yii\web\AssetBundle;

/**
 * Admin module asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ModuleAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/admin/assets';
    public $baseUrl = '@web';
    public $css = [
        'css/main.css',
        'css/navbar.css',
        'css/room.css',
        'css/dingo.css',
    ];
    public $js = [
        'js/app.js',
        //'js/cats.js',
    ];
    public $jsOptions = [
        'position' => \yii\web\View::POS_HEAD
    ];
    public $depends = [
        'yii\web\YiiAsset',
        'yii\bootstrap\BootstrapAsset',
    ];
}
