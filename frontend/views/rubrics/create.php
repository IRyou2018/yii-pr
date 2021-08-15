<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Rubrics */

$this->title = 'Create Rubrics';
$this->params['breadcrumbs'][] = ['label' => 'Rubrics', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="rubrics-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
