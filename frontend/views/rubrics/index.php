<?php

use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\RubricsSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Rubrics';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="rubrics-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <p>
        <?= Html::a('Create Rubrics', ['create'], ['class' => 'btn btn-success']) ?>
    </p>

    <?php // echo $this->render('_search', ['model' => $searchModel]); ?>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'id',
            'description',
            'value',
            'item_id',

            ['class' => 'yii\grid\ActionColumn'],
        ],
    ]); ?>


</div>
