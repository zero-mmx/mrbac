<?php
/**
 * Created by PhpStorm.
 * User: chenqiang
 * Date: 9/25/2015
 * Time: 00:21
 */

use yii\bootstrap\Tabs;

mrbac\MrbacAsset::register($this);

echo Tabs::widget([
    'items' => [
        [
            'label' => '用户',
            'content' => $this->render('tab/roleToUser'),
            'headerOptions' => ['id'=>'tab1'],
            'active' => true
        ],
        [
            'label' => '角色',
            'content' => $this->render('tab/taskToRole'),
            'headerOptions' => ['id'=>'tab2'],
        ],
        [
            'label' => '任务',
            'content' => $this->render('tab/permissionToTask'),
            'headerOptions' => ['id'=>'tab3'],
        ]
    ],
]);