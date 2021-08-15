<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\LecturerAssessmentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

// $this->title = 'Dashboard';
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="lecturer-assessment-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create an Assessment', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            'name',
            'assessment_type',
            'deadline',
            'active',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
