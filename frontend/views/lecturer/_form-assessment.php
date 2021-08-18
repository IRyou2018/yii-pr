<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="assessments-form">

    <?php $form = ActiveForm::begin([
        'id' => 'dynamic-form',
        'options' => ['enctype' => 'multipart/form-data']
    ]); ?>

    <div class="card"><!-- widgetBody -->
        <div class="card-header text-white bg-dark">
            <div class="row">
                <div class="col-md-10">
                    <h4>Assessment</h4>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col">
                    <?= $form->field($model, 'deadline')->textInput() ?>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <?= $form->field($model, 'assessment_type')->textInput() ?>
                </div>
                <div class="col">
                    <?= $form->field($model, 'active')->textInput() ?>
                </div>
            </div>
            <div class="row">
                <div class="col">
                    <?= $form->field($modelUpload,'file')->fileInput(['multiple'=>'multiple']) ?>
                </div>
            </div>
        </div>
    </div>
      

    <?= $this->render('_form-sections', [
        'form' => $form,
        'modelsSection' => $modelsSection,
        'modelsItem' => $modelsItem,
        'modelsRubric' => $modelsRubric,
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>