<?php

use yii\helpers\Html;
use yii\grid\GridView;

use mrbac\models\AuthItem;

use Yii;

/* @var $this yii\web\View */
/* @var $searchModel mrbac\models\searchs\AuthItemSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('app', 'Auth Items');
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-item-index">

    <h1><?= Html::encode($this->title) ?></h1>
    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <p>
        <?= Html::a(Yii::t('app', 'Create Auth Item'), ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            ['attribute'=>'name'],
            ['attribute'=>'type','value'=>function($model){
                return AuthItem::$TYPES[$model->type];
            },'filter'=>AuthItem::$TYPES],
            'rule_name',
            ['attribute'=>'created_at','filter'=>'','format'=>'date'],
            // 'created_at',
            // 'updated_at',
                ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>

</div>
