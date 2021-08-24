<?php

use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="lecturer-assessment-index">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="d-flex">
            <i class="material-icons fa-2x">assignment</i> &nbsp; 
            <span class="h4 align-self-center">To be completed</span>
    </div>
    <?php if (!empty($unCompletedAssessment)) : ?>
    <?= GridView::widget([
        'dataProvider' => new ArrayDataProvider([
            'allModels' => $unCompletedAssessment
        ]),
        'tableOptions' => ['class' => 'table table-bordered'],
        'summary' => '',
        // 'hover'=>true,
        'columns' => [
            [
                'attribute' => 'name',
                'label' => 'Assessment',
                'value' => 'name',
                'contentOptions' =>['width' => '80%'],
                'headerOptions' => ['class' => 'text-light bg-secondary']
            ],
            [
                'attribute' => 'deadline',
                'contentOptions' =>['width' => '20%'],
                'headerOptions' => ['class' => 'text-light bg-secondary'],
            ],
        ],
    ]); ?>
    <?php endif; ?>
    <div class="d-flex">
        <div class="tr mb-3">
            <p class="text-muted"> &nbsp; Assessments that are not completed</p>
        </div>
    </div>

    <div class="d-flex">
            <i class="material-icons fa-2x">assignment_turned_in</i> &nbsp; 
            <span class="h4 align-self-center">Completed</span>
    </div>

    <?php if (!empty($completedAssessment)) : ?>
    <?= GridView::widget([
        'dataProvider' => new ArrayDataProvider([
            'allModels' => $completedAssessment
        ]),
        'summary' => false,
        'tableOptions' => ['class' => 'table table-bordered'],
        'rowOptions' => function ($model, $key, $index, $grid) { return ['data-assessment_id' => $model['assessment_id']]; },
        'columns' => [
            [
                'attribute' => 'name',
                'label' => 'Assessment',
                'value' => 'name',
                'contentOptions' =>['width' => '80%'],
                'headerOptions' => ['class' => 'text-light bg-secondary'],
            ],
            [
                'attribute' => 'deadline',
                'value' => function ($data) { return 'Finished';},
                'contentOptions' =>['width' => '20%'],
                'headerOptions' => ['class' => 'text-light bg-secondary'],
            ],
        ],
    ]); ?>
    <?php endif; ?>
    <div class="d-flex">
        <div class="tr mb-3">
            <p class="text-muted"> &nbsp; Peer Assessments that are completed</p>
        </div>
    </div>

    <div class="d-flex">
            <i class="material-icons fa-2x">feedback</i> &nbsp; 
            <span class="h4 align-self-center">Feedback</span>
    </div>
    <?php if ($feedbacks->getTotalCount() > 0) : ?>
    <?= GridView::widget([
        'dataProvider' => $feedbacks,
        'summary' => false,
        'tableOptions' => ['class' => 'table table-bordered'],
        'columns' => [
            [
                'attribute' => 'name',
                'contentOptions' =>['width' => '80%'],
                'headerOptions' => ['class' => 'text-light bg-secondary'],
            ],
            [
                'attribute' => 'deadline',
                'contentOptions' =>['width' => '20%'],
                'headerOptions' => ['class' => 'text-light bg-secondary'],
            ],
        ],
    ]); ?>
    <?php endif; ?>
    <div class="d-flex">
        <div class="tr mb-3">
            <p class="text-muted"> &nbsp; Feedbacks that are been given</p>
        </div>
    </div>

</div>

<?php
$this->registerJs("

    $('td').click(function (e) {
        var id = $(this).closest('tr').data('key');
        var assessment_id = $(this).closest('tr').data('assessment_id');
        if(e.target == this)
            location.href = '" . Url::to(['student/submit']) . "?id=' + id + '&assessment_id=' + assessment_id;
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