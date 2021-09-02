<?php

use yii\helpers\Html;
use wbraganca\dynamicform\DynamicFormWidget;
?>

<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_rubric',
    'widgetBody' => '.container-rubric',
    'widgetItem' => '.rubric',
    'limit' => 10,
    'min' => 1,
    'insertButton' => '.add-rubric',
    'deleteButton' => '.remove-rubric',
    'model' => $modelsRubric[0][0][0],
    'formId' => 'dynamic-form',
    'formFields' => [
        'level',
        'weight',
        'description'
    ],
]); ?>

<div class="card mt-1"><!-- widgetBody -->
    <div class="card-header text-white bg-dark">
        <div class="row">
            <div class="col-md-11">
            <h5>Rubric (Optional)</h5>
            </div>
            <div class="col-md-1 text-right">
                <button type="button" class="add-rubric btn-success btn-xs"><i class="fas fa-plus"></i></button>
            </div>
        </div>
    </div>
    <?php foreach ($modelsRubric[$indexSection][$indexItem] as $indexRubric => $modelRubric) : ?>
    <div class="card-body container-rubric border-bottom">
        <div class="row rubric mt-2">
            <div class="col">
                <div class="row">
                    <div class="col-md-2">
                        <span class="align-middle">Name</span>
                    </div>
                    <div class="col-md-5">
                        <?= $form->field($modelRubric, "[{$indexSection}][{$indexItem}][{$indexRubric}]level")->label(false)->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-2">
                        <span class="align-middle">Weight</span>
                    </div>
                    <div class="col-md-2">
                        <?= $form->field($modelRubric, "[{$indexSection}][{$indexItem}][{$indexRubric}]weight")->label(false)->textInput(['maxlength' => true]) ?>
                    </div>
                    <div class="col-md-1 text-right" rowspan="2">
                        <button type="button" class="remove-rubric btn-danger btn-xs"><i class="fas fa-minus"></i></button>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-2 align-middle">
                        <span class="align-middle">Description</span>
                    </div>
                    <div class="col-md-9">
                        <?= $form->field($modelRubric, "[{$indexSection}][{$indexItem}][{$indexRubric}]description")->label(false)->textInput(['maxlength' => true]) ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
<?php DynamicFormWidget::end(); ?>