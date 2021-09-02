<?php

use common\models\Rubrics;
use yii\bootstrap4\ActiveForm;
use yii\bootstrap4\Modal;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */

$this->title = $model->name;
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="submit-group">

    <h2><?= Html::encode($this->title) ?></h2>

    <?php $form = ActiveForm::begin(); ?>

    <?php if (!empty($modelsSection)) : ?>
        <?php foreach ($modelsSection as $indexSection => $modelSection): ?>
        <div class="card mt-2 mb-2">
            <div class="card-header text-white bg-dark">
                <h5><?= $modelSection->name ?></h5>
            </div>
            <div class="card-body">
                <?php foreach ($modelsItem[$indexSection] as $indexItem => $modelItem): ?>
                <div class="container border mb-2">
                <div class="row mb-1">
                    <div class="col-md-2 font-weight-bold text-white bg-primary">
                        Item/Functionality
                    </div>
                    <div class="col-md-10">
                        <?php
                            $query = Rubrics::find()
                                ->where('item_id = :id')
                                ->addParams([':id' => $modelItem->id]);
                            $dataProvider = new ActiveDataProvider([
                                'query' => $query,
                                'sort' => false,
                            ]);
                            Modal::begin([
                                'title' => 'Rubrics',
                                'toggleButton' => ['label' => $modelItem->name, 'tag' => 'a', 'class'=>'text-black h5'],
                                'size' => 'modal-lg',
                            ]);
                        ?>
                        <?= GridView::widget([
                            'dataProvider' => $dataProvider,
                            'id' => 'rubricsList',
                            'tableOptions' => ['class' => 'table table-bordered'],
                            'summary' => false,
                            'columns' => [
                                [
                                    'attribute' => 'level',
                                    'label' => 'Name',
                                    'value' => 'level',
                                    'headerOptions' => ['class' => 'text-light bg-primary'],
                                    'contentOptions' => ['class' => 'text-wrap']
                                ],
                                [
                                    'attribute' => 'weight',
                                    'value' => 'weight',
                                    'headerOptions' => ['class' => 'text-light bg-primary'],
                                    'contentOptions' => ['class' => 'text-wrap']
                                ],
                                [
                                    'attribute' => 'description',
                                    'value' => 'description',
                                    'headerOptions' => ['class' => 'text-light bg-primary'],
                                    'contentOptions' => ['class' => 'text-wrap']
                                ]
                            ],
                        ]); ?>
                        <?php Modal::end(); ?>
                    </div>
                </div>
                <div class="row mb-1">
                    <div class="col-md-2 font-weight-bold text-white bg-secondary">
                        Max Mark
                    </div>
                    <div class="col-md-2">
                        &nbsp&nbsp&nbsp&nbsp&nbsp&nbsp<?= $modelItem->max_mark_value ?>
                    </div>
                    <?php if ($model->assessment_type == 2) : ?>
                    <div class="col-md-2 text-white bg-secondary">
                        Proposed Mark
                    </div>
                    <div class="col-md-2">
                        <?= Html::activeHiddenInput($modelsGroupItemMark[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]item_max_mark"); ?>
                        <?= $form->field($modelsGroupItemMark[$indexSection][$indexItem], "[{$indexSection}][{$indexItem}]mark")->textInput(['style'=>'width:70px', 'class'=>'text-center'])->label(false) ?>
                    </div>
                    <?php endif; ?>
                </div>
                <?php if ($modelItem->item_type == 0) : ?>
                    <div class="row mb-1">
                        <div class="col-md-2 font-weight-bold bg-light">
                            Student Name
                        </div>
                        <div class="col-md-2 font-weight-bold bg-light">
                        <?php
                            if ($model->assessment_type == 0 || $model->assessment_type == 2) {
                                echo 'Contribution';
                            } else if ($model->assessment_type == 1) {
                                echo 'Proposed Mark';
                            }
                        ?>   
                        </div>
                        <div class="col-md-8 font-weight-bold bg-light">
                            Comment
                        </div>
                    </div>
                    <?php foreach ($modelsGroupAssessmentDetail[$indexSection][$indexItem] as $indexStudent => $groupDetail) : ?>
                    <div class="row mb-1">
                        <div class="col-md-2 bg-light">
                            <?= $groupDetail->studentName ?>
                        </div>
                        <div class="col-md-2">
                            <?= Html::activeHiddenInput($modelsGroupAssessmentDetail[$indexSection][$indexItem][$indexStudent], "[{$indexSection}][{$indexItem}][{$indexStudent}]item_id"); ?>
                            <?= Html::activeHiddenInput($modelsGroupAssessmentDetail[$indexSection][$indexItem][$indexStudent], "[{$indexSection}][{$indexItem}][{$indexStudent}]group_student_Info_id"); ?>
                            <?= Html::activeHiddenInput($modelsGroupAssessmentDetail[$indexSection][$indexItem][$indexStudent], "[{$indexSection}][{$indexItem}][{$indexStudent}]work_student_id"); ?>
                            <?php if ($model->assessment_type == 0 || $model->assessment_type == 2) : ?>
                                <?= $form->field($modelsGroupAssessmentDetail[$indexSection][$indexItem][$indexStudent], "[{$indexSection}][{$indexItem}][{$indexStudent}]contribution")->textInput(['style'=>'width:70px', 'class'=>'text-center', 'type' => 'number'])->label(false) ?>
                            <?php elseif ($model->assessment_type == 1) : ?>
                                <?= $form->field($modelsGroupAssessmentDetail[$indexSection][$indexItem][$indexStudent], "[{$indexSection}][{$indexItem}][{$indexStudent}]mark")->textInput(['style'=>'width:70px', 'class'=>'text-center'])->label(false) ?>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-8">
                            <?= $form->field($modelsGroupAssessmentDetail[$indexSection][$indexItem][$indexStudent], "[{$indexSection}][{$indexItem}][{$indexStudent}]comment")->textarea(['maxlength' => true])->label(false) ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php elseif ($modelItem->item_type == 1) : ?>
                    <div class="row mb-1">
                        <div class="col-md-2 font-weight-bold bg-light">
                            Proposed Mark
                        </div>
                        <div class="col-md-2">
                        <?= $form->field($modelsGroupAssessmentDetail[$indexSection][$indexItem][0], "[{$indexSection}][{$indexItem}][{0}]mark")->textInput(['style'=>'width:70px', 'class'=>'text-center'])->label(false) ?>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-2 font-weight-bold bg-light">
                            Comment
                        </div>
                        <div class="col-md-10">
                            <?= $form->field($modelsGroupAssessmentDetail[$indexSection][$indexItem][0], "[{$indexSection}][{$indexItem}][{0}]comment")->textarea(['maxlength' => true])->label(false) ?>
                        </div>
                    </div>
                <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>

</div>

<?php $style= <<< CSS

input[type=number]::-webkit-inner-spin-button {
  cursor: pointer;
}

CSS;
$this->registerCss($style);
?>

