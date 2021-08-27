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
        <table class="table table-bordered mt-2">
            <thead>
                <tr class="text-white bg-dark">
                    <th scope="col" colspan="12">
                        <?= $modelSection->name ?>
                    </th>
                </tr>
                <tr class="text-white bg-secondary">
                    <th class="align-middle">Item/Functionality</th>
                    <th class="align-middle text-center">Max Mark</th>
                    <th class="align-middle text-center">Proposed Mark</th>
                    <th class="align-middle">Feedback</th>
                </tr>
            </thead>

            <tbody class="container-section">
                <?php foreach ($modelsItem[$indexSection] as $indexItem => $modelItem): ?>
                <tr>
                    <td class="col-md-2"> 
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
                                'toggleButton' => ['label' => $modelItem->name, 'tag' => 'a'],
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
                    </td>
                    <td class="col-md-1 text-center">
                        <?= $modelItem->max_mark_value ?>
                    </td>
                    <td class="col-md-1">
                        <?= $modelsAssessmentDetail[$indexSection][$indexItem]->mark ?>
                    </td>
                    <td class="col-md-7">
                        <?= $modelsAssessmentDetail[$indexSection][$indexItem]->comment ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <?php endforeach; ?>
    <?php endif; ?>

</div>
