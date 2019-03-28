<?php

namespace infinitiweb\supervisorManager\assets\base;

use yii\bootstrap\BootstrapPluginAsset;
use yii\web\AssetBundle;
use yii\web\YiiAsset;

/**
 * Class BaseAsset
 *
 * @package infinitiweb\supervisorManager\assets\base
 */
class BaseAsset extends AssetBundle
{
    /** @inheritdoc */
    public $sourcePath = '@infinitiweb/supervisorManager/assets/base/assets';

    /** @inheritdoc */
    public $js = [
        'js/main.js',
    ];

    /** @inheritdoc */
    public $depends = [
        YiiAsset::class,
        BootstrapPluginAsset::class,
    ];
}
