<?php

/* @var $this yii\web\View */
/* @var $form yii\bootstrap4\ActiveForm */
/* @var $model \common\models\LoginForm */

use yii\bootstrap4\Html;
use yii\bootstrap4\ActiveForm;

$this->title = 'Peer Review System';

?>
<div class="site-login">

    <div class="row">
        <div class="mt-3 offset-lg-3 col-lg-6">
            <h1><?= Html::encode($this->title) ?></h1>

            <p>Please fill out the following fields to login:</p>
            <?php $form = ActiveForm::begin(['id' => 'login-form']); ?>

                <?= $form->field($model, 'username')->textInput(['autofocus' => true]) ?>

                <?= $form->field($model, 'password')->passwordInput() ?>

                <?= $form->field($model, 'rememberMe')->checkbox() ?>

                <div class="form-group">
                    <?= Html::submitButton('Login', ['class' => 'btn btn-primary', 'name' => 'login-button']) ?>
                </div>

                <p style="color:red;"><br><b>NOTE</b>: If you have not logged into your computing account before, you will need to do that 
			    first. You can find details on My Dundee > Computing Specialist Resources (QMB) > How to Connect to a Computing LABPC remotely (no VPN). </p>

            <?php ActiveForm::end(); ?>
        </div>
    </div>
</div>
