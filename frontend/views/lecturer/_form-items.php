<?php

use yii\helpers\Html;
use wbraganca\dynamicform\DynamicFormWidget;
?>

<?php DynamicFormWidget::begin([
    'widgetContainer' => 'dynamicform_item',
    'widgetBody' => '.container-item',
    'widgetItem' => '.item',
    'limit' => 4,
    'min' => 1,
    'insertButton' => '.add-item',
    'deleteButton' => '.remove-item',
    'model' => $modelsItem[0],
    'formId' => 'dynamic-form',
    'formFields' => [
        'name',
        'max_mark_value',
        'item_type'
    ],
]); ?>

<?php foreach ($modelsItem as $indexItem => $modelItem) : ?>
<div class="container-item"><!-- widgetContainer -->
    <div class="item card mt-2"><!-- widgetBody -->
        <div class="card-header text-white bg-secondary">
            <div class="row">
                <div class="col-md-10">
                    <h3>Item</h3>
                </div>
                <div class="col-md-2 float-right">
                    <button type="button" class="add-item btn-success btn-xs"><span class="material-icons">add</span></button>
                    <button type="button" class="remove-item btn-danger"><span class="material-icons">remove</span></button>
                </div>
            </div>
        </div>
        <div class="card-body">
            <?php
                // necessary for update action.
                if (!$modelItem->isNewRecord) {
                    echo Html::activeHiddenInput($modelItem, "[{$indexSection}][{$indexItem}]id");
                }
            ?>
            <div class="row">
                <div class="col-sm-6">
                    <?= $form->field($modelItem, "[{$indexSection}][{$indexItem}]name")->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-sm-3">
                    <?= $form->field($modelItem, "[{$indexSection}][{$indexItem}]max_mark_value")->textInput(['maxlength' => true]) ?>
                </div>
                <div class="col-sm-3">
                    <?= $form->field($modelItem, "[{$indexSection}][{$indexItem}]item_type")->textInput(['maxlength' => true]) ?>
                </div>
            </div>
            <div class="row">
                <div class="col-sm-12">
                    <?= $this->render('_form-rubrics', [
                        'form' => $form,
                        'indexSection' => $indexSection,
                        'indexItem' => $indexItem,
                        'modelsRubric' => $modelsRubric[$indexSection][$indexItem],
                        ]) ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endforeach; ?>
<?php DynamicFormWidget::end(); ?>