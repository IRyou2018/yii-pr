<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model frontend\models\IndividualAssessmentSearch */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="individual-assessment-search">

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <?= $form->field($model, 'id') ?>

    <?= $form->field($model, 'student_id') ?>

    <?= $form->field($model, 'mark') ?>

    <?= $form->field($model, 'marked') ?>

    <?= $form->field($model, 'file') ?>

    <?php // echo $form->field($model, 'assessment_id') ?>

    <div class="form-group">
        <?= Html::submitButton('Search', ['class' => 'btn btn-primary']) ?>
        <?= Html::resetButton('Reset', ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
