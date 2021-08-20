<?php

use common\models\Items;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Sections */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="sections-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'assessment_id')->textInput() ?>

    <?= $form->field($model, 'section_type')->textInput() ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        // 'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'name',
            'max_mark_value',
            'item_type',
            'lecturer_only',
            //'section_id',

            ['class' => 'yii\grid\CheckboxColumn'],
        ],
    ]); ?>

    <div class="form-group">
        <?= Html::submitButton('Save', ['class' => 'btn btn-success']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>

<?php
$this->registerJS("
    $('#Save').click(function(){
    var selection = $('#w0').yiiGridView('getSelectedRows');
    }
");
?>
