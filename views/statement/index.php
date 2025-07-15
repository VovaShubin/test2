<?php
use yii\widgets\ActiveForm;
use yii\helpers\Html;
?>
<h1>Загрузка отчета</h1>
<?php if (Yii::$app->session->hasFlash('error')): ?>
    <div class="alert alert-danger"><?= Yii::$app->session->getFlash('error') ?></div>
<?php endif; ?>
<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>
<?= $form->field($model, 'file')->fileInput(['accept' => '.html,.htm']) ?>
<button class="btn btn-primary">Загрузить</button>
<?php ActiveForm::end(); ?> 