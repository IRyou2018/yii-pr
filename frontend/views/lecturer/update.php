<?php

use common\models\Items;
use common\models\Rubrics;
use common\models\Sections;
use kartik\datetime\DateTimePicker;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */

?>
<div class="assessments-update">

    <?php $form = ActiveForm::begin(); ?>

    <div class="row">
        <div class="col-md-3">
            Assessment Name
        </div>
        <div class="col-md-9">
            <?= $form->field($model, 'name')->label(false)->textInput(['maxlength' => true]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            Deadline
        </div>
        <div class="col-md-9">
            <?= $form->field($model, 'deadline')->label(false)->widget(DateTimePicker::classname(), [
                'options' => ['placeholder' => 'Enter event time ...'],
                'pluginOptions' => [
                    'autoclose' => true
                ]
            ]) ?>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            Visibility
        </div>
        <div class="col-md-9">
            <?= $form->field($model, 'active')->label(false)->dropdownList(
                [
                    1 => 'Active', 
                    0 => 'Inactive'
                ],
                ['prompt'=>'Select Active Status']
            ) ?>
        </div>
    </div>

    <div class="form-group">
        <?= Html::submitButton('Update', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
