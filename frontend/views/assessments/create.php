<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */

$this->title = 'Create Assessments';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="assessments-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
        'modelsSection' => $modelsSection,
        'modelsItem' => $modelsItem,
    ]) ?>

</div>
