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
                'contentOptions' =>['width' => '67%']
            ],
            [
                'attribute' => 'deadline',
                'headerOptions' => ['class' => 'text-center text-light bg-secondary'],
                'contentOptions' =>['width' => '16%']
            ],
            [
                'attribute' => 'active',
                'headerOptions' => ['class' => 'text-center text-light bg-secondary'],
                'format' => 'raw',
                'contentOptions' =>['width' => '10%'],
                'value' => function ($data) {
                    return SwitchInput::widget(
                        [
                            'name' => 'status_11',
                            'pluginEvents' => [
                                'switchChange.bootstrapSwitch' => "function(e){sendRequest(e.currentTarget.checked, $data->id);}"
                            ],
                            'pluginOptions' => [
                                'size' => 'small',
                                'onColor' => 'primary',
                                'offColor' => 'primary',
                                'onText' => 'ON',
                                'offText' => 'OFF',
                            ],
                            'value' => $data->active
                        ]
                    );
                }
            ],

            [
                'class' => 'yii\grid\ActionColumn',
                'contentOptions' =>['width' => '7%'],
                'headerOptions' => ['class' => 'text-light bg-secondary'],
                'template' => '{result}',
                'buttons'=>
                    [
                        'result' => function ($url, $model, $key)
                            {     
                                $options = [
                                    'title' => Yii::t('yii', 'Result'),
                                    'class' => 'btn'
                                ];
                                return Html::a('Result', ['brief-result', 'id'=>$model->id], ['class'=>'btn btn-primary btn-sm']);
                            }
                    ],
            ],
        ],
    ]); ?>
    <?php Pjax::end() ?>

</div>

<?php $js = <<< JS

    function sendRequest(status, id){
        
        $.ajax({
            url:'/lecturer/update-active',
            method:'post',
            data:{status:status,id:id},
            success:function(data){
                // alert(status);
            },
            error:function(jqXhr,status,error){
                // alert(status);
            }
        });
    }
JS;

$this->registerJs($js, \yii\web\View::POS_READY);
?>

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