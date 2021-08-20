<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\LecturerAssessmentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = 'Archived';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="lecturer-assessment-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create an Assessment', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php Pjax::begin() ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'name',
                'contentOptions' =>['width' => '500px']
            ],
            [
                'attribute' =>'assessment_type',
                'value' => 'assessmentType',
                'filter'=> false
            ],
            // 'deadline',
            [
                'attribute' =>'active',
                'value' => 'activeStatus',
                'filter'=> false
            ],

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>
    <?php Pjax::end() ?>

</div>
