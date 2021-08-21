<?php

use common\models\Items;
use common\models\Rubrics;
use common\models\Sections;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */

$this->title = 'Update Assessments: ' . $model->name;
$this->params['breadcrumbs'][] = ['label' => 'Assessments', 'url' => ['index']];
$this->params['breadcrumbs'][] = ['label' => $model->name, 'url' => ['view', 'id' => $model->id]];
$this->params['breadcrumbs'][] = 'Update';
?>
<div class="assessments-update">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form-assessment_update', [
        'model' => $model,
        'modelsSection' => (empty($modelsSection)) ? [new Sections()] : $modelsSection,
        'modelsItem' => (empty($modelsItem)) ? [[new Items()]] : $modelsItem,
        'modelsRubric' => (empty($modelsRubric)) ? [[[new Rubrics()]]] : $modelsRubric,
    ]) ?>

</div>
