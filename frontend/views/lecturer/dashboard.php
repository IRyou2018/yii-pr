<?php

use kartik\switchinput\SwitchInput;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\widgets\Pjax;

/* @var $this yii\web\View */
/* @var $searchModel frontend\models\LecturerAssessmentSearch */
/* @var $dataProvider yii\data\ActiveDataProvider */
$this->title = 'Dashboard';
?>
<div class="lecturer-assessment-index">
    <div class="row">
        <div class="col-md-6">
            <h4><?= Html::encode($this->title) ?></h4>
        </div>
        <div class="col-md-6">
            <p>
                <?= Html::a('Create an Assessment', ['create'], ['class' => 'btn btn-success float-right']) ?>
            </p>
        </div>
    </div>

    <?php Pjax::begin() ?>
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'filterModel' => $searchModel,
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],
            [
                'attribute' => 'name',
                'contentOptions' =>['width' => '700px']
            ],
            [
                'attribute' =>'assessment_type',
                'value' => 'assessmentType',
                'filter'=> false
            ],
            // 'deadline',
            [
                'attribute' => 'active',
                'format' => 'raw',
                'value' => function ($data) {
                    return SwitchInput::widget(
                        [
                            'name' => 'active',
                            'pluginEvents' => [
                                'switchChange.bootstrapSwitch' => "function(e){sendRequest(e.currentTarget.checked, $data->id);}"
                            ],
                    
                            'pluginOptions' => [
                                'size' => '',
                                'onColor' => 'primary',
                                'offColor' => 'primary',
                                'onText' => 'ON',
                                'offText' => 'OFF'
                            ],
                            'value' => $data->active
                        ]
                    );
                }
            ],

            ['class' => 'yii\grid\ActionColumn'],
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
                // alert(data);
            },
            error:function(jqXhr,status,error){
                // alert(error);
            }
        });
    }
JS;

$this->registerJs($js, \yii\web\View::POS_READY);
?>