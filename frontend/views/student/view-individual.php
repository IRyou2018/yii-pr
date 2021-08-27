<?php

use common\models\Rubrics;
use yii\bootstrap4\Modal;
use yii\data\ActiveDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */

$this->title = $model->name;
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="view-individual">

    <h2><?= Html::encode($this->title) ?></h2>

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
                        <div class="col-md-2 font-weight-bold text-white bg-secondary">
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
                                    'toggleButton' => ['label' => $modelItem->name, 'tag' => 'a', 'class'=>'text-black'],
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
                                        'headerOptions' => ['class' => 'text-light bg-primary']
                                    ],
                                    [
                                        'attribute' => 'weight',
                                        'value' => 'weight',
                                        'headerOptions' => ['class' => 'text-light bg-primary']
                                    ],
                                    [
                                        'attribute' => 'description',
                                        'value' => 'description',
                                        'headerOptions' => ['class' => 'text-light bg-primary']
                                    ]
                                ],
                            ]); ?>
                            <?php Modal::end(); ?>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-2 font-weight-bold text-white bg-secondary">
                            Proposed Mark
                        </div>
                        <div class="col-md-4">
                            <?= $modelsAssessmentDetail[$indexSection][$indexItem]->mark ?>
                        </div>
                        <div class="col-md-2 font-weight-bold text-white bg-secondary">
                            Max Mark
                        </div>
                        <div class="col-md-4">
                            <?= $modelItem->max_mark_value ?>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-2 font-weight-bold text-white bg-secondary">
                            Feedback
                        </div>
                        <div class="col-md-10">
                            <?= $modelsAssessmentDetail[$indexSection][$indexItem]->comment ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

</div>
