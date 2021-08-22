<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */

$this->title = $model->name;
// $this->params['breadcrumbs'][] = $this->title;
// \yii\web\YiiAsset::register($this);
?>
<div class="assessments-view">

    <h2><?= Html::encode($this->title) ?></h2>

    <?php if ($model->assessment_type == 0) : ?>
        <?= $this->render('_form-items', [
                    'model' => $model,
                    'modelsSection' => $modelsSection,
                    'modelsItem' => $modelsItem,
                    'modelsPeerReviewDetail' => $modelsPeerReviewDetail,
                ]) ?>
    <?php elseif ($model->assessment_type == 1) : ?>
        <?= $this->render('_form-peer-review', [
                    'model' => $model,
                    'modelsSection' => $modelsSection,
                    'modelsItem' => $modelsItem,
                    'modelsPeerReviewDetail' => $modelsPeerReviewDetail,
                ]) ?>
    <?php endif; ?>

</div>
