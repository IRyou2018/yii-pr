<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\IndividualAssessmentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Individual Assessments';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="individual-assessment-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Individual Assessment', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'student_id',
            'mark',
            'marked',
            'file',
            //'assessment_id',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
