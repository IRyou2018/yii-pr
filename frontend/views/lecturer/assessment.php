<?php

use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */

$this->title = "Assessment";
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="assessments-view">

    <div class="row">
        <div class="col-md-10">
            <p>
                <?= Html::a('Edit Details', ['update', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                <?php if ($model->assessment_type == 0 || $model->assessment_type == 1 || $model->assessment_type == 2) : ?>
                    <?= Html::a('Add Group', ['add-group', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                <?php elseif ($model->assessment_type == 3 || $model->assessment_type == 4) : ?>
                    <?= Html::a('Add Student', ['add-student', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
                <?php endif; ?>
                <?= Html::a('Assessment Results', ['view-result', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
            </p>
        </div>
        <div class="col-md-2 ml-auto">
            <p>
                <?= Html::a('Delete Assessment', ['delete', 'id' => $model->id], [
                    'class' => 'btn btn-danger btn-sm',
                    'data' => [
                        'confirm' => 'Are you sure you want to delete this assessment?',
                        'method' => 'post',
                    ],
                ]) ?>
            </p>
        </div>
    </div>

    <div class="container">
        <div class="row">
            <div class="col-md-2 text-white bg-dark">
                Assessment Name
            </div>
            <div class="col-md-10 bg-light">
                <?= $model->name ?>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2 text-white bg-dark">
                Assessment Type
            </div>
            <div class="col-md-10 bg-light">
                <?php
                    if ($model->assessment_type == 0) {
                        echo "(Group) Peer Review";
                    } else if ($model->assessment_type == 1) {
                        echo "(Group) Peer Assessment";
                    } else if ($model->assessment_type == 2) {
                        echo "(Group) Peer Review & Assessment";
                    } else if ($model->assessment_type == 3) {
                        echo "Self Assessment";
                    } else if ($model->assessment_type == 4) {
                        echo "Peer Marking";
                    }
                ?>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2 text-white bg-dark">
                Deadline
            </div>
            <div class="col-md-4 bg-light">
                <?= $model->deadline ?>
            </div>
            <div class="col-md-2 text-white bg-dark">
                Visibility
            </div>
            <div class="col-md-4 bg-light">
                <?php
                    if ($model->active == 1) {
                        echo "Active";
                    } else if ($model->active == 0) {
                        echo "Inactive";
                    }
                ?>
            </div>
        </div>
        <div class="row mt-2">
            <div class="col-md-2 text-white bg-dark">
                Coordinators
            </div>
            <div class="col-md-10 bg-light">
                <?php 
                    if(!empty($coorinators)) {
                        $count = count($coorinators);
                        for ($i=0; $i < $count; $i++) {
                            echo $coorinators[$i]["name"]." ";
                            if(($count-1)!=$i) echo ", ";
                        }
                    } else{
                        echo "None";
                    }
                ?>
            </div>
        </div>
    </div>

    
    <?php if ($model->assessment_type == 0 || $model->assessment_type == 1 || $model->assessment_type == 2) : ?>

    <div class="container">
        <div class="row mt-4 border-dark">
            <span class="title h4">Partly Completed Groups</span>
        </div>
    </div>
        <?php if (!empty($groupInfo['inconsistent'])) : ?>
        <?= GridView::widget([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => $groupInfo['inconsistent']
            ]),
            'tableOptions' => ['class' => 'table table-bordered'],
            'summary' => '',
            'columns' => [
                [
                    'attribute' => 'name',
                    'label' => 'Group Name',
                    'value' => 'name',
                    'contentOptions' =>['width' => '77%'],
                    'headerOptions' => ['class' => 'text-light bg-secondary']
                ],
                [
                    'attribute' => 'marked',
                    'contentOptions' =>['width' => '5%'],
                    'headerOptions' => ['class' => 'text-light bg-secondary text-center'],
                    'format' => 'html',
                    'value' => function ($model) {
                        if ($model['mark'] === null) {
                            return '<i class="material-icons mx-3" style="color:red">clear</i>';
                        } else {
                            return '<i class="material-icons mx-3" style="color:green">done</i>'; // check icon 
                        }
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'contentOptions' =>['width' => '18%'],
                    'headerOptions' => ['class' => 'text-light bg-secondary'],
                    'template' => '{manage-group}{result}',
                    'buttons'=>
                        [
                            'manage-group' => function ($url, $model, $key)
                            {     
                                $options = [
                                    'title' => Yii::t('yii', 'Manage Group'),
                                    'class' => 'btn'
                                ];
                                return Html::a('Manage Group', ['manage-group', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm']);
                            },
                            'result' => function ($url, $model, $key)
                            {     
                                $options = [
                                    'title' => Yii::t('yii', 'Result'),
                                    'class' => 'btn'
                                ];
                                return Html::a('Result', ['group-result', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm mx-2']);
                            }
                        ],
                ],
            ],
        ]); ?>
        <?php endif; ?>

    <div class="container">
        <div class="row mt-4 border-dark">
            <span class="title h4">Incompleted Groups</span>
        </div>
    </div>
    <?php if (!empty($groupInfo['incomplete'])) : ?>
        <?= GridView::widget([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => $groupInfo['incomplete']
            ]),
            'tableOptions' => ['class' => 'table table-bordered'],
            'summary' => '',
            'columns' => [
                [
                    'attribute' => 'name',
                    'label' => 'Group Name',
                    'value' => 'name',
                    'contentOptions' =>['width' => '77%'],
                    'headerOptions' => ['class' => 'text-light bg-secondary']
                ],
                [
                    'attribute' => 'marked',
                    'contentOptions' =>['width' => '5%'],
                    'headerOptions' => ['class' => 'text-light bg-secondary text-center'],
                    'format' => 'html',
                    'value' => function ($model) {
                        if ($model['mark'] === null) {
                            return '<i class="material-icons mx-3" style="color:red">clear</i>';
                        } else {
                            return '<i class="material-icons mx-3" style="color:green">done</i>'; // check icon 
                        }
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'contentOptions' =>['width' => '18%'],
                    'headerOptions' => ['class' => 'text-light bg-secondary'],
                    'template' => '{manage-group}{result}',
                    'buttons'=>
                        [
                            'manage-group' => function ($url, $model, $key)
                            {     
                                $options = [
                                    'title' => Yii::t('yii', 'Manage Group'),
                                    'class' => 'btn'
                                ];
                                return Html::a('Manage Group', ['manage-group', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm']);
                            },
                            'result' => function ($url, $model, $key)
                            {     
                                $options = [
                                    'title' => Yii::t('yii', 'Result'),
                                    'class' => 'btn'
                                ];
                                return Html::a('Result', ['group-result', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm mx-2']);
                            }
                        ],
                ],
            ],
        ]); ?>
    <?php endif; ?>

    <div class="container">
        <div class="row mt-4 border-dark">
            <span class="title h4">Completed Groups</span>
        </div>
    </div>
    <?php if (!empty($groupInfo['completed'])) : ?>
        <?= GridView::widget([
            'dataProvider' => new ArrayDataProvider([
                'allModels' => $groupInfo['completed']
            ]),
            'tableOptions' => ['class' => 'table table-bordered'],
            'summary' => '',
            'columns' => [
                [
                    'attribute' => 'name',
                    'label' => 'Group Name',
                    'value' => 'name',
                    'contentOptions' =>['width' => '77%'],
                    'headerOptions' => ['class' => 'text-light bg-secondary']
                ],
                [
                    'attribute' => 'marked',
                    'contentOptions' =>['width' => '5%'],
                    'headerOptions' => ['class' => 'text-light bg-secondary text-center'],
                    'format' => 'html',
                    'value' => function ($model) {
                        if ($model['mark'] === null) {
                            return '<i class="material-icons mx-3" style="color:red">clear</i>';
                        } else {
                            return '<i class="material-icons mx-3" style="color:green">done</i>'; // check icon 
                        }
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'contentOptions' =>['width' => '18%'],
                    'headerOptions' => ['class' => 'text-light bg-secondary'],
                    'template' => '{manage-group}{result}',
                    'buttons'=>
                        [
                            'manage-group' => function ($url, $model, $key)
                            {     
                                $options = [
                                    'title' => Yii::t('yii', 'Manage Group'),
                                    'class' => 'btn'
                                ];
                                return Html::a('Manage Group', ['manage-group', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm']);
                            },
                            'result' => function ($url, $model, $key)
                            {     
                                $options = [
                                    'title' => Yii::t('yii', 'Result'),
                                    'class' => 'btn'
                                ];
                                return Html::a('Result', ['group-result', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm mx-2']);
                            }
                        ],
                ],
            ],
        ]); ?>
    <?php endif; ?>

    <?php elseif ($model->assessment_type == 3) : ?>
        <div class="container">
            <div class="row mt-4 border-dark">
                <span class="title h4">Individual Mark Status</span>
            </div>
        </div>
        <?php if ($individualInfo->getTotalCount() > 0) : ?>
        <?= GridView::widget([
            'dataProvider' => $individualInfo,
            'tableOptions' => ['class' => 'table'],
            'summary' => '',
            'columns' => [
                [
                    'attribute' => 'work_student_name',
                    'label' => 'Student Name',
                    'value' => function ($model) {
                        return $model['work_student_name'];
                    },
                    'contentOptions' =>['width' => '40%'],
                    'headerOptions' => ['class' => 'text-light bg-secondary']
                ],
                [
                    'attribute' => 'marked',
                    'contentOptions' =>['width' => '5%'],
                    'headerOptions' => ['class' => 'text-light bg-secondary text-center'],
                    'format' => 'html',
                    'value' => function ($model) {
                        if ($model['marked'] == 0) {
                            return '<i class="material-icons mx-3" style="color:red">clear</i>';
                        } else {
                            return '<i class="material-icons mx-3" style="color:green">done</i>'; // check icon 
                        }
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'contentOptions' =>['width' => '5%'],
                    'headerOptions' => ['class' => 'text-light bg-secondary'],
                    'template' => '{result}',
                    'buttons'=>
                        [
                            'result' => function ($url, $model, $key)
                                {     
                                    if ($model['marked'] == 0) {
                                        return Html::a('Result', ['mark-individual', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm']);
                                    } else if ($model['marked'] == 1) {
                                        return Html::a('Result', ['individual-result', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm']);
                                    }
                                }
                        ],
                ],
            ],
        ]); ?>
        <?php endif; ?>

    <?php elseif ($model->assessment_type == 4) : ?>
        <div class="container">
            <div class="row mt-4 border-dark">
                <span class="title h4">Individual Mark Status</span>
            </div>
        </div>
        <?php if ($individualInfo->getTotalCount() > 0) : ?>
        <?= GridView::widget([
            'dataProvider' => $individualInfo,
            'tableOptions' => ['class' => 'table'],
            'summary' => '',
            'columns' => [
                [
                    'attribute' => 'work_student_name',
                    'label' => 'Work Student',
                    'value' => function ($model) {
                        return $model['work_student_name'];
                    },
                    'contentOptions' =>['width' => '40%'],
                    'headerOptions' => ['class' => 'text-light bg-secondary']
                ],
                [
                    'attribute' => 'marker_student_name',
                    'label' => 'Marker Student',
                    'value' => function ($model) {
                        return $model['marker_student_name'];
                    },
                    'contentOptions' =>['width' => '40%'],
                    'headerOptions' => ['class' => 'text-light bg-secondary']
                ],
                [
                    'attribute' => 'marked',
                    'contentOptions' =>['width' => '5%'],
                    'headerOptions' => ['class' => 'text-light bg-secondary text-center'],
                    'format' => 'html',
                    'value' => function ($model) {
                        if ($model['marked'] == 0) {
                            return '<i class="material-icons mx-3" style="color:red">clear</i>';
                        } else {
                            return '<i class="material-icons mx-3" style="color:green">done</i>'; // check icon 
                        }
                    },
                ],
                [
                    'class' => 'yii\grid\ActionColumn',
                    'contentOptions' =>['width' => '5%'],
                    'headerOptions' => ['class' => 'text-light bg-secondary'],
                    'template' => '{result}',
                    'buttons'=>
                        [
                            'result' => function ($url, $model, $key)
                                {     
                                    if ($model['marked'] == 0) {
                                        return Html::a('Result', ['mark-individual', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm']);
                                    } else if ($model['marked'] == 1) {
                                        return Html::a('Result', ['individual-result', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm']);
                                    }
                                }
                        ],
                ],
            ],
        ]); ?>
        <?php endif; ?>
    <?php endif; ?>
</div>
