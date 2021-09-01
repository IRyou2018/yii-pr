<?php

use yii\bootstrap4\Modal;
use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;
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
 
            <?= Html::a('Assessment Results', ['brief-result', 'id' => $model->id], ['class' => 'btn btn-primary btn-sm']) ?>
            <?= Html::button('Edit Assessmet', ['value' => Url::to(['update', 'id' => $model->id]), 'class' => 'btn modalButton2 btn-primary btn-sm', 'id' => 'modalButtonEA']) ?>
            <?php
                Modal::begin([
                    'title' => '<h4>Edit Assessmet</h4>',
                    'id' => 'modalEA',
                    'size' => 'modal-lg',
                ]);
                echo "<div id='modalContentEA'></div>";
                Modal::end();
            ?>
            <?php if ($model->assessment_type == 0 || $model->assessment_type == 1 || $model->assessment_type == 2) : ?>
                <?= Html::button('Add Group', ['value' => Url::to(['add-group', 'id' => $model->id]), 'class' => 'btn btn-primary btn-sm', 'id' => 'modalButtonAG']) ?>
                <?php
                    Modal::begin([
                        'title' => '<h4>Add Group</h4>',
                        'id' => 'modalAG',
                        'size' => 'modal-lg',
                    ]);
                    echo "<div id='modalContentAG'></div>";
                    Modal::end();
                ?>
            <?php endif; ?>

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
            'tableOptions' => ['class' => 'table'],
            'summary' => '',
            'columns' => [
                [
                    'attribute' => 'name',
                    'label' => 'Group Name',
                    'value' => 'name',
                    'contentOptions' =>['width' => '77%'],
                    'headerOptions' => ['class' => 'text-light bg-dark']
                ],
                [
                    'attribute' => 'marked',
                    'contentOptions' =>['width' => '5%'],
                    'headerOptions' => ['class' => 'text-light bg-dark text-center'],
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
                    'contentOptions' =>['width' => '18%'],
                    'headerOptions' => ['class' => 'text-light bg-dark'],
                    'template' => '{manage-group}{result}',
                    'buttons'=>
                        [
                            // 'manage-group' => function ($url, $model, $key)
                            // {
                            //     return Html::a('Manage Group', ['manage-group', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm']);
                            // },
                            'result' => function ($url, $model, $key)
                            {
                                if ($model['marked'] == 0) {
                                    return Html::a('Result', ['mark-group', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm mx-2']);
                                } else if ($model['marked'] == 1) {
                                    return Html::a('Result', ['group-result', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm mx-2']);
                                }
                            }
                        ],
                ],
            ],
        ]); ?>
        <?php endif; ?>

    <div class="container">
        <div class="row mt-4 border-dark">
            <span class="title h4">Incomplete Groups</span>
        </div>
    </div>
        <?php if (!empty($groupInfo['incomplete'])) : ?>
            <?= GridView::widget([
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $groupInfo['incomplete']
                ]),
                'tableOptions' => ['class' => 'table'],
                'summary' => '',
                'columns' => [
                    [
                        'attribute' => 'name',
                        'label' => 'Group Name',
                        'value' => 'name',
                        'contentOptions' =>['width' => '77%'],
                        'headerOptions' => ['class' => 'text-light bg-dark']
                    ],
                    [
                        'attribute' => 'marked',
                        'contentOptions' =>['width' => '5%'],
                        'headerOptions' => ['class' => 'text-light bg-dark text-center'],
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
                        'contentOptions' =>['width' => '18%'],
                        'headerOptions' => ['class' => 'text-light bg-dark'],
                        'template' => '{manage-group}{result}',
                        'buttons'=>
                            [
                                // 'manage-group' => function ($url, $model, $key)
                                // {
                                //     return Html::a('Manage Group', ['manage-group', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm']);
                                // },
                                'result' => function ($url, $model, $key)
                                {
                                    if ($model['marked'] == 0) {
                                        return Html::a('Result', ['mark-group', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm mx-2']);
                                    } else if ($model['marked'] == 1) {
                                        return Html::a('Result', ['group-result', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm mx-2']);
                                    }
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
                'tableOptions' => ['class' => 'table'],
                'summary' => '',
                'columns' => [
                    [
                        'attribute' => 'name',
                        'label' => 'Group Name',
                        'value' => 'name',
                        'contentOptions' =>['width' => '77%'],
                        'headerOptions' => ['class' => 'text-light bg-dark']
                    ],
                    [
                        'attribute' => 'marked',
                        'contentOptions' =>['width' => '5%'],
                        'headerOptions' => ['class' => 'text-light bg-dark text-center'],
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
                        'contentOptions' =>['width' => '18%'],
                        'headerOptions' => ['class' => 'text-light bg-dark'],
                        'template' => '{manage-group}{result}',
                        'buttons'=>
                            [
                                // 'manage-group' => function ($url, $model, $key)
                                // {
                                //     return Html::a('Manage Group', ['manage-group', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm']);
                                // },
                                'result' => function ($url, $model, $key)
                                {
                                    if ($model['marked'] == 0) {
                                        return Html::a('Result', ['mark-group', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm mx-2']);
                                    } else if ($model['marked'] == 1) {
                                        return Html::a('Result', ['group-result', 'id'=>$model['id']], ['class'=>'btn btn-primary btn-sm mx-2']);
                                    }
                                }
                            ],
                    ],
                ],
            ]); ?>
        <?php endif; ?>

    <?php elseif ($model->assessment_type == 3) : ?>
        <div class="container">
            <div class="row mt-4 border-dark">
                <span class="title h4">Self Assessment Status</span>
            </div>
        </div>
        <?php if (!empty($individualInfo)) : ?>
            <?= GridView::widget([
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $individualInfo
                ]),
                'tableOptions' => ['class' => 'table'],
                'summary' => '',
                'columns' => [
                    [
                        'attribute' => 'student_name',
                        'label' => 'Student Name',
                        'value' => function ($model) {
                            return $model['student_name'];
                        },
                        'contentOptions' =>['width' => '80%'],
                        'headerOptions' => ['class' => 'text-light bg-dark']
                    ],
                    [
                        'attribute' => 'marked',
                        'contentOptions' =>['width' => '5%'],
                        'headerOptions' => ['class' => 'text-light bg-dark text-center'],
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
                        'contentOptions' =>['width' => '15%'],
                        'headerOptions' => ['class' => 'text-light bg-dark'],
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
                <span class="title h4">Partly Completed Peer Marking</span>
            </div>
        </div>
        <?php if (!empty($individualInfo['inconsistent'])) : ?>
            <?= GridView::widget([
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $individualInfo['inconsistent']
                ]),
                'tableOptions' => ['class' => 'table'],
                'summary' => '',
                'columns' => [
                    [
                        'attribute' => 'student_name',
                        'label' => 'Student Name',
                        'value' => function ($model) {
                            return $model['student_name'];
                        },
                        'contentOptions' =>['width' => '80%'],
                        'headerOptions' => ['class' => 'text-light bg-dark']
                    ],
                    [
                        'attribute' => 'marked',
                        'contentOptions' =>['width' => '5%'],
                        'headerOptions' => ['class' => 'text-light bg-dark text-center'],
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
                        'contentOptions' =>['width' => '15%'],
                        'headerOptions' => ['class' => 'text-light bg-dark'],
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

        <div class="container">
            <div class="row mt-4 border-dark">
                <span class="title h4">Incomplete Peer Marking</span>
            </div>
        </div>
        <?php if (!empty($individualInfo['incomplete'])) : ?>
            <?= GridView::widget([
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $individualInfo['incomplete']
                ]),
                'tableOptions' => ['class' => 'table'],
                'summary' => '',
                'columns' => [
                    [
                        'attribute' => 'student_name',
                        'label' => 'Student Name',
                        'value' => function ($model) {
                            return $model['student_name'];
                        },
                        'contentOptions' =>['width' => '80%'],
                        'headerOptions' => ['class' => 'text-light bg-dark']
                    ],
                    [
                        'attribute' => 'marked',
                        'contentOptions' =>['width' => '5%'],
                        'headerOptions' => ['class' => 'text-light bg-dark text-center'],
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
                        'contentOptions' =>['width' => '15%'],
                        'headerOptions' => ['class' => 'text-light bg-dark'],
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

        <div class="container">
            <div class="row mt-4 border-dark">
                <span class="title h4">Completed Peer Marking</span>
            </div>
        </div>
        <?php if (!empty($individualInfo['completed'])) : ?>
            <?= GridView::widget([
                'dataProvider' => new ArrayDataProvider([
                    'allModels' => $individualInfo['completed']
                ]),
                'tableOptions' => ['class' => 'table'],
                'summary' => '',
                'columns' => [
                    [
                        'attribute' => 'student_name',
                        'label' => 'Student Name',
                        'value' => function ($model) {
                            return $model['student_name'];
                        },
                        'contentOptions' =>['width' => '80%'],
                        'headerOptions' => ['class' => 'text-light bg-dark']
                    ],
                    [
                        'attribute' => 'marked',
                        'contentOptions' =>['width' => '5%'],
                        'headerOptions' => ['class' => 'text-light bg-dark text-center'],
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
                        'contentOptions' =>['width' => '15%'],
                        'headerOptions' => ['class' => 'text-light bg-dark'],
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
<?php
$script = <<< JS

$(function(){
    $('#modalButton').click(function() {
    // get the click of the create button
    $('#modal').modal('show')
        .find('#modalContent')
        .load($(this).attr('value'));
    });

    $('#modalButtonEA').click(function() {
    // get the click of the create button
    $('#modalEA').modal('show')
        .find('#modalContentEA')
        .load($(this).attr('value'));
     });

    $('#modalButtonAG').click(function() {
    // get the click of the create button
    $('#modalAG').modal('show')
        .find('#modalContentAG')
        .load($(this).attr('value'));
    });

    $('#modalButtonAS').click(function() {
    // get the click of the create button
    $('#modalAS').modal('show')
        .find('#modalContentAS')
        .load($(this).attr('value'));
    });
});

JS;

$this->registerJS($script);
?>