<?php

use yii\helpers\Html;


/* @var $this yii\web\View */
/* @var $model zero\mrbac\models\AuthItem */

$this->title = Yii::t('app', 'Create Auth Item');
$this->params['breadcrumbs'][] = ['label' => Yii::t('app', 'Auth Items'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="auth-item-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
