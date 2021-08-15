<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\LecturerAssessment */

$this->title = 'Create Lecturer Assessment';
$this->params['breadcrumbs'][] = ['label' => 'Lecturer Assessments', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="lecturer-assessment-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
