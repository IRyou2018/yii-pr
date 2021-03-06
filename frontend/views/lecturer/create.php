<?php

/* @var $this yii\web\View */
/* @var $model common\models\Assessments */

$this->title = 'Create Assessments';
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="assessments-create">

    <?= $this->render('_form-assessment', [
        'model' => $model,
        'coordinators' => $coordinators,
        'modelUpload' => $modelUpload,
        'modelsSection' => $modelsSection,
        'modelsItem' => $modelsItem,
        'modelsRubric' => $modelsRubric,
    ]) ?>

</div>
