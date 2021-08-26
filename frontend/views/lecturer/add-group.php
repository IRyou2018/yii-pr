<?php

use wbraganca\dynamicform\DynamicFormWidget;
use yii\bootstrap4\ActiveForm;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */
/* @var $group common\models\GroupAssessment */
/* @var $groupStudents common\models\GroupStudentInfo */

$this->title = 'Add Group';
$this->params['breadcrumbs'][] = ['label' => 'Assessments', 'url' => ['assessment', 'id' => $model->id]];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="add-group">

    <h4><?= Html::encode($this->title) ?></h4>

    <?php $form = ActiveForm::begin([
        'id' => 'dynamic-form'
    ]); ?>

    <?= $form->field($group, 'name')->label('Group Name')->textInput(['maxlength' => true]) ?>

    <?= $this->render('_form-student', [
        'form' => $form,
        'groupStudents' => $groupStudents,
    ]) ?>

    <div class="form-group">
        <?= Html::submitButton('Submit', ['class' => 'btn btn-success']) ?>
    </div>
    <?php ActiveForm::end(); ?>
</div>
