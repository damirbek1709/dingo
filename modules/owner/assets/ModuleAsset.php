<?php
/**
 * @link http://www.yiiframework.com/
 * @copyright Copyright (c) 2008 Yii Software LLC
 * @license http://www.yiiframework.com/license/
 */

namespace app\modules\owner\assets;

use yii\web\AssetBundle;

/**
 * Admin module asset bundle.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @since 2.0
 */
class ModuleAsset extends AssetBundle
{
    public $sourcePath = '@app/modules/owner/assets';
    public $baseUrl = '@web/owner';
    public $css = [
        'css/main_owner_version_2.css',
        'css/room_new_version_1.css',
        'css/navbar.css',
        'css/calendar_new_version_1.css',
    ];
    public $js = [
        //'js/app.js',
        'https://code.jquery.com/jquery-3.6.0.min.js'
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
