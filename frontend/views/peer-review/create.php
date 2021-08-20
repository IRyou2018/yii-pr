<?php

use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $model common\models\PeerReview */

$this->title = 'Create Peer Review';
$this->params['breadcrumbs'][] = ['label' => 'Peer Reviews', 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->title;
?>
<div class="peer-review-create">

    <h1><?= Html::encode($this->title) ?></h1>

    <?= $this->render('_form', [
        'model' => $model,
    ]) ?>

</div>
