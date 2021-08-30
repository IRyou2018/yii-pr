<?php

use yii\helpers\Html;
use yii\bootstrap4\ActiveForm;
use kartik\datetime\DateTimePicker;
use yii\bootstrap4\Modal;
use yii\grid\GridView;

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
                <div class="col-md-12">
                    <h5>Assessment</h5>
                </div>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'deadline')->widget(DateTimePicker::classname(), [
                        'options' => ['placeholder' => 'Enter event time ...'],
                        'pluginOptions' => [
                            'autoclose' => true
                        ]
                    ]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($model, 'assessment_type')->dropdownList(
                        [
                            0 => '(Group) Peer Review',
                            1 => '(Group) Peer Assessment',
                            2 => '(Group) Peer Review & Assessment',
                            3 => 'Self Assessment',
                            4 => 'Peer Marking'
                        ],
                        ['prompt'=>'Select Assessment Type', 'id' => 'assType'],
                    ) ?>
                </div>
                <div class="col-md-6">
                    <?= $form->field($model, 'active')->dropdownList(
                        [
                            1 => 'Active', 
                            0 => 'Inactive'
                        ],
                        ['prompt'=>'Select Active Status']
                    ) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <?= $form->field($modelUpload,'file')->fileInput(['multiple'=>'multiple']) ?>
                </div>
                <div class="col-md-4 mt-3">
                    <a id="downloadTemp" class="btn btn-secondary" href="#"><i class="fas fa-download"></i> Download Template</a>
                </div>
                <div class="col-md-2 mt-3">
                    <?php
                        $dataProvider = $coordinators;
                        Modal::begin([
                            'title' => 'Add coordinators',
                            'toggleButton' => ['label' => 'Add Coordinator', 'class' => 'btn btn-primary'],
                        ]);
                    ?>

                    <?= GridView::widget([
                        'dataProvider' => $dataProvider,
                        'id' => 'coordinatorList',
                        'tableOptions' => ['class' => 'table table-bordered'],
                        'summary' => false,
                        'columns' => [
                            [
                                'class' => 'yii\grid\CheckboxColumn',
                                'headerOptions' => ['class' => 'text-light bg-primary']
                            ],
                            [
                                'attribute' => 'first_name',
                                'value' => 'first_name',
                                'headerOptions' => ['class' => 'text-light bg-primary']
                            ],
                            [
                                'attribute' => 'last_name',
                                'value' => 'last_name',
                                'headerOptions' => ['class' => 'text-light bg-primary']
                            ]
                        ],
                    ]); ?>
                    <?php Modal::end(); ?>
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
        <?= Html::submitButton('Create', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$script = <<< JS

$(document).ready(function(){
    $('#assType').change(function(){
        var assType = $('#assType').val();
        $("select").filter(".itemType").each(function() {
            if (assType == 3 || assType == 4) {
                $(this).val(0);
                $(this).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
            }
        })        
    });

    $("button").click(function(){
        var assType = $('#assType').val();
        $("select").filter(".itemType").each(function() {
            if (assType == 3 || assType == 4) {
                $(this).val(0);
                $(this).prop('disabled', true);
            } else {
                $(this).prop('disabled', false);
            }
        })        
    });

});

    $('#selection_all').click(function(){
        $('input[name=selection_all]').click();

    });

    $('#Create').click(function(){
    var selection = $('#coordinatorList').yiiGridView('getSelectedRows');
    });

    $('#downloadTemp').click(function(){
        var typeValue = $('#assType').val();
        if (typeValue == '0' || typeValue == '1' || typeValue == '2') {
            $("#downloadTemp").attr("href", "/uploads/GroupTemplate.xlsx");
        } else if (typeValue == '3') {
            $("#downloadTemp").attr("href", "/uploads/SelfAssessmentTemplate.xlsx");
        } else if (typeValue == '4') {
            $("#downloadTemp").attr("href", "/uploads/PeerMarkingTemplate.xlsx");
        } else {
            alert('Please select an Assessment Type.');
        }
    });

JS;

$this->registerJS($script);
?>