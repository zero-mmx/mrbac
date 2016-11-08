<?php

namespace mrbac;

/**
 * AdminAsset
 *
 * @author Misbahul D Munir <misbahuldmunir@gmail.com>
 * @since 1.0
 */
class MrbacAsset extends \yii\web\AssetBundle
{
    /**
     * @inheritdoc
     */
    public $sourcePath = '@mrbac/assets';

    /**
     * @inheritdoc
     */
    public $js = [
        'yii.admin.js',
        'yii.admin.js',
    ];

    public $depends = [
        'yii\web\YiiAsset',
    ];
}
