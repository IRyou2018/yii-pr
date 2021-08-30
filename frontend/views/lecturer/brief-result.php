<?php

use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */

$this->title = 'Result';
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="brief-result">

    <div class="row">
        <div class="col-md-10">
            <h3>Assessment: <?= $model->name ?></h3>
        </div>
        <div class="col-md-2">
            
        </div>
    </div>

    <?php if ($model->assessment_type == 0
        || $model->assessment_type == 1
        || $model->assessment_type == 2) : ?>

        <?= GridView::widget([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => $briefResult
            ]),
            'tableOptions' => ['class' => 'table'],
            'summary' => '',
            'columns' => [
                [
                    'attribute' => 'name',
                    'contentOptions' =>['width' => '35%'],
                    'headerOptions' => ['class' => 'text-light bg-dark']
                ],
                [
                    'attribute' => 'group_name',
                    'label' => 'Group',
                    'contentOptions' =>['width' => '35%'],
                    'headerOptions' => ['class' => 'text-light bg-dark'],
                ],
                [
                    'attribute' => 'group_mark',
                    'value' => function ($model) {
                        if (empty($model['group_mark'])) {
                            return "-";
                        } else {
                            return $model['group_mark'];
                        }
                    },
                    'contentOptions' =>['width' => '15%'],
                    'headerOptions' => ['class' => 'text-light bg-dark']
                ],
                [
                    'attribute' => 'individual_mark',
                    'value' => function ($model) {
                        if (empty($model['individual_mark'])) {
                            return "-";
                        } else {
                            return $model['individual_mark'];
                        }
                    },
                    'contentOptions' =>['width' => '15%'],
                    'headerOptions' => ['class' => 'text-light bg-dark'],
                ],
            ],
        ]); ?>

    <?php elseif ($model->assessment_type == 3
        || $model->assessment_type == 4) : ?>
        
        <?= GridView::widget([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => $briefResult
            ]),
            'tableOptions' => ['class' => 'table'],
            'summary' => '',
            'columns' => [
                [
                    'attribute' => 'name',
                    'contentOptions' =>['width' => '80%'],
                    'headerOptions' => ['class' => 'text-light bg-dark']
                ],
                [
                    'attribute' => 'mark',
                    'contentOptions' =>['width' => '20%'],
                    'headerOptions' => ['class' => 'text-light bg-dark'],
                ],
            ],
        ]); ?>

    <?php endif; ?>

</div>
