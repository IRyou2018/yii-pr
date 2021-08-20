<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\IndividualAssessment */

$this->title = 'Update Individual Assessment: ' . $model->id;
$this->params['breadcrumbs'][] = ['label' => 'Individual Assessments', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->id, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="individual-assessment-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
