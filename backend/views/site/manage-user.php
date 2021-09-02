<?php

use yii\bootstrap4\Modal;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $searchModel backend\models\UserSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="user-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            // ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'first_name',
                'contentOptions' =>['width' => '20%','class' => 'text-center'],
                'headerOptions' => ['class' => 'text-white bg-light text-center'],
            ],
            [
                'attribute' => 'last_name',
                'contentOptions' =>['width' => '20%','class' => 'text-center'],
                'headerOptions' => ['class' => 'text-white bg-light text-center'],
            ],
            [
                'attribute' => 'matric_number',
                'contentOptions' =>['width' => '15%','class' => 'text-center'],
                'headerOptions' => ['class' => 'text-white bg-light text-center'],
            ],
            [
                'attribute' => 'email',
                'contentOptions' =>['width' => '25%','class' => 'text-center'],
                'headerOptions' => ['class' => 'text-white bg-light text-center'],
            ],
            [
                'attribute' => 'type',
                'contentOptions' =>['width' => '10%','class' => 'text-center'],
                'headerOptions' => ['class' => 'text-white bg-light text-center'],
                'value' => function ($model) {
                    if ($model->type == 1) {
                        return 'Student';
                    } else if ($model->type == 0) {
                        return 'Lecturer';
                    } else if ($model->type == 2) {
                        return 'Administrator';
                    }
                },
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' =>['width' => '10%','class' => 'text-center'],
                'headerOptions' => ['class' => 'text-white bg-light text-center'],
                'template' => '{update}{delete}',
                        'buttons'=>
                            [
                                'update' => function ($url, $model, $key)
                                {
                                    return Html::button('Update', ['value' => Url::to(['update-user', 'id' => $model->id]), 'class' => 'btn modalButtonAG btn-primary btn-sm', 'id' => 'modalButtonAG']);
                                },
                                'delete' => function ($url, $model, $key)
                                {
                                    return Html::a('Delete', ['delete-user', 'id'=>$model->id], [
                                        'class'=>'btn btn-primary btn-sm mx-2',
                                        'data' => [
                                            'confirm' => 'Are you absolutely sure ? You will lose all the information about this user with this action.',
                                            'method' => 'post',
                                        ],
                                    ]);
                                }
                            ],
            ],
        ],
    ]); ?>

    <?php
        Modal::begin([
            'title' => 'Update User',
            'id' => 'modalAG',
            'size' => 'modal-md',
        ]);
        echo "<div id='modalContentAG'></div>";
        Modal::end();
    ?>


</div>

<?php
$script = <<< JS

$(function(){
    $('.modalButtonAG').click(function() {
    // get the click of the create button
    $('#modalAG').modal('show')
        .find('#modalContentAG')
        .load($(this).attr('value'));
    });
});

JS;

$this->registerJS($script);
?>
