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

    <p>
        <?= Html::button('Add Grade', ['value' => Url::to('add-grade'), 'class' => 'btn btn-success', 'id' => 'modalButtonCG', 'data-toggle' => 'modal', 'data-target' => 'modalCG']) ?>
        <?php
            Modal::begin([
                'title' => 'Add Grade',
                'id' => 'modalCG',
                'size' => 'modal-md',
            ]);
            echo "<div id='modalContentCG'></div>";
            Modal::end();
        ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            // ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'grade',
                'contentOptions' =>['width' => '20%','class' => 'text-center'],
                'headerOptions' => ['class' => 'text-white bg-light text-center'],
            ],
            [
                'attribute' => 'weight',
                'contentOptions' =>['width' => '20%','class' => 'text-center'],
                'headerOptions' => ['class' => 'text-white bg-light text-center'],
            ],
            [
                'attribute' => 'min_mark',
                'contentOptions' =>['width' => '15%','class' => 'text-center'],
                'headerOptions' => ['class' => 'text-white bg-light text-center'],
            ],
            [
                'attribute' => 'max_mark',
                'contentOptions' =>['width' => '25%','class' => 'text-center'],
                'headerOptions' => ['class' => 'text-white bg-light text-center'],
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
                                    return Html::button('Update', ['value' => Url::to(['update-grade', 'id' => $model->id]), 'class' => 'btn modalButtonUG btn-primary btn-sm', 'id' => 'modalButtonUG']);
                                },
                                'delete' => function ($url, $model, $key)
                                {
                                    return Html::a('Delete', ['delete-grade', 'id'=>$model->id], [
                                        'class'=>'btn btn-primary btn-sm mx-2',
                                        'data' => [
                                            'confirm' => 'Are you sure you want to delete this item?',
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
            'title' => 'Update Grade',
            'id' => 'modalUG',
            'size' => 'modal-md',
        ]);
        echo "<div id='modalContentUG'></div>";
        Modal::end();
    ?>


</div>

<?php
$script = <<< JS

$(function(){
    $('.modalButtonUG').click(function() {
    // get the click of the create button
    $('#modalUG').modal('show')
        .find('#modalContentUG')
        .load($(this).attr('value'));
    });

    $('#modalButtonCG').click(function() {
    // get the click of the create button
    $('#modalCG').modal('show')
        .find('#modalContentCG')
        .load($(this).attr('value'));
    });
});

JS;

$this->registerJS($script);
?>
