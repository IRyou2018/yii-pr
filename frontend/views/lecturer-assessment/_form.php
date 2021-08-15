<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\LecturerAssessment */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="lecturer-assessment-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'lecturer_id')->textInput() ?>

    <?= $form->field($model, 'assessment_id')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
