<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\PeerReview */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="peer-review-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'marker_student_id')->textInput() ?>

    <?= $form->field($model, 'individual_assessment_id')->textInput() ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
