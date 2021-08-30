<?php

use yii\helpers\Html;
use wbraganca\dynamicform\DynamicFormWidget;
?>

<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_student', // required: only alphanumeric characters plus "_" [A-Za-z0-9_]
    'widgetBody' => '.container-student', // required: css class selector
    'widgetItem' => '.student', // required: css class
    'limit' => 10, // the maximum times, an element can be cloned (default 999)
    'min' => 1, // 0 or 1 (default 1)
    'insertButton' => '.add-student', // css class
    'deleteButton' => '.remove-student', // css class
    'model' => $groupStudents[0],
    'formId' => 'dynamic-form',
    'formFields' => [
        'first_name',
        'last_name',
        'matriculation_number',
        'email'
    ],
]); ?>

<div class="card mt-2 mb-2"><!-- widgetBody -->
    <div class="card-header text-white bg-dark">
        <div class="row">
            <div class="col-md-11">
                <h5>Students</h5>
            </div>
            <div class="col-md-1 text-right">
                <button type="button" class="add-student btn-success btn-xs"><i class="fas fa-plus"></i></button>
            </div>
        </div>
    </div>
    
    <div class="card-body container-student">
        <?php foreach ($groupStudents as $index => $student): ?>
        <div class="row student mt-2 border">
            <div class="col">
                <div class="row mt-1">
                    <div class="col-md-2 bg-light">
                        <span class="align-middle">First Name</span>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($student, "[{$index}]first_name")->label(false)->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-2 bg-light align-middle">
                        <span class="align-middle">Last Name</span>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($student, "[{$index}]last_name")->label(false)->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-2 text-right" rowspan="2">
                        <button type="button" class="remove-student btn-danger btn-xs"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 bg-light">
                        <span class="align-middle">Matriculation Number</span>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($student, "[{$index}]matric_number")->label(false)->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-2 bg-light align-middle">
                        <span class="align-middle">Email</span>
                    </div>
                    <div class="col-md-3">
                        <?= $form->field($student, "[{$index}]email")->label(false)->textInput(['maxlength' => true]) ?>
                    </div>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    
</div>
<?php DynamicFormWidget::end(); ?>  