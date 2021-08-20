<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\IndividualAssessment */

$this->title = 'Create Individual Assessment';
$this->params['breadcrumbs'][] = ['label' => 'Individual Assessments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="individual-assessment-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
