<?php

use kartik\switchinput\SwitchInput;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\helpers\Url;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\LecturerAssessmentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = 'Dashboard';
?>
<div class="lecturer-assessment-index">

    <?= common\widgets\Alert::widget() ?>
    <div class="row">
        <div class="col-md-6 mb-3">
            <h4><?= Html::encode($this->title) ?></h4>
        </div>
        <div class="col-md-6 mb-3">
            <p>
                <?= Html::a('Create an Assessment', ['create'], ['class' => 'btn btn-success float-right']) ?>
            </p>
        </div>
    </div>

    <?php Pjax::begin() ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'tableOptions' => ['class' => 'table table-bordered'],
        'summary' => false,
        'columns' => [
            [
                'attribute' => 'name',
                'headerOptions' => ['class' => 'text-light bg-secondary'],
                'contentOptions' =>['width' => '74%']
            ],
            [
                'attribute' => 'deadline',
                'label' => 'Year',
                'format' => ['date', 'Y'],
                'headerOptions' => ['class' => 'text-center text-light bg-secondary'],
                'contentOptions' =>['width' => '8%', 'class' => 'text-center']
            ],
            [
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' =>['width' => '18%'],
                'headerOptions' => ['class' => 'text-light bg-secondary'],
                'template' => '{copy-create}{result}',
                'buttons'=>
                    [
                        'copy-create' => function ($url, $model, $key)
                            {     
                                $options = [
                                    'title' => Yii::t('yii', 'Result'),
                                    'class' => 'btn'
                                ];
                                return Html::a('Copy Create', ['copy-create', 'id'=>$model->id], ['class'=>'btn btn-primary btn-sm']);
                            },
                        'result' => function ($url, $model, $key)
                            {     
                                $options = [
                                    'title' => Yii::t('yii', 'Result'),
                                    'class' => 'btn'
                                ];
                                return Html::a('Result', ['brief-result', 'id'=>$model->id], ['class'=>'btn btn-primary btn-sm mx-2']);
                            }
                    ],
            ],
        ],
    ]); ?>
    <?php Pjax::end() ?>

</div>

<?php
$this->registerJs("

    $('td').click(function (e) {
        var id = $(this).closest('tr').data('key');
        if(e.target == this)
            location.href = '" . Url::to(['lecturer/assessment']) . "?id=' + id;
    });
    
    $('tr:has(td)').mouseover(function() {
        $(this).addClass('highlightRow');
    });
    
    $('tr').mouseout(function() {
        $(this).removeClass('highlightRow');
    });

");?>
<?php $style= <<< CSS

.highlightRow{
    background-color:lightgrey;
    cursor: pointer
}

CSS;
$this->registerCss($style);
?>