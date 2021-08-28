<?php

use yii\data\ArrayDataProvider;
use yii\grid\GridView;
use yii\helpers\Html;
use yii\helpers\Url;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

?>
<div class="student-dashboard">

    <h1><?= Html::encode($this->title) ?></h1>

    <div class="d-flex">
            <i class="material-icons fa-2x">feedback</i> &nbsp; 
            <span class="h4 align-self-center">Feedback</span>
    </div>
    <?php if (!empty($feedbacks)) : ?>
    <?= GridView::widget([
        'dataProvider' => new ArrayDataProvider([
            'allModels' => $feedbacks
        ]),
        'summary' => false,
        'tableOptions' => ['class' => 'table table-bordered'],
        'rowOptions' => function ($model, $key, $index, $grid) {
            return [
                'data-id' => $model['id'],
                'data-assessment_id' => $model['assessment_id'],
                'data-status' => 2
            ]; },
        'columns' => [
            [
                'attribute' => 'name',
                'contentOptions' =>['width' => '80%'],
                'headerOptions' => ['class' => 'text-light bg-secondary'],
            ],
            [
                'attribute' => 'mark',
                'contentOptions' =>['width' => '20%'],
                'headerOptions' => ['class' => 'text-light bg-secondary'],
            ],
        ],
    ]); ?>
    <?php endif; ?>
    <div class="d-flex">
        <div class="tr mb-3">
            <p class="text-muted"> &nbsp; Feedbacks that are given</p>
        </div>
    </div>

</div>

<?php
$this->registerJs("

    $('td').click(function (e) {
        var id = $(this).closest('tr').data('id');
        var assessment_id = $(this).closest('tr').data('assessment_id');
        
        if(e.target == this) {
            location.href = '" . Url::to(['student/view-feedback']) . "?id=' + id + '&assessment_id=' + assessment_id;
        }
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