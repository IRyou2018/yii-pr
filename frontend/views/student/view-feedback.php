<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */

$this->title = $model->name;
$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="view-feedback">

    <h2>Assessment: <?= Html::encode($this->title) ?></h2>
    <h2>Grade Mark: <?= $grade ?></h2>

    <?php if (!empty($modelsSection)) : ?>
        <?php foreach ($modelsSection as $indexSection => $modelSection): ?>
        <div class="card mt-2 mb-2">
            <div class="card-header text-white bg-dark">
                <h5><?= $modelSection->name ?></h5>
            </div>
            <div class="card-body">
                <?php foreach ($modelsItem[$indexSection] as $indexItem => $modelItem): ?>
                <div class="container border mb-2">
                    <div class="row mb-1">
                        <div class="col-md-2 text-white bg-primary">
                            Item/Functionality
                        </div>
                        <div class="col-md-10">
                            <?= $modelItem->name ?>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-2 text-white bg-secondary">
                            Mark
                        </div>
                        <div class="col-md-2">
                            <?= $feedbackDetail[$indexSection][$indexItem]['mark'] . " / " . $modelItem->max_mark_value ?>
                        </div>
                    </div>
                    <div class="row mb-1">
                        <div class="col-md-2 text-white bg-secondary">
                            Comment
                        </div>
                        <div class="col-md-10">
                            <?= $feedbackDetail[$indexSection][$indexItem]['comment'] ?>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>

</div>
