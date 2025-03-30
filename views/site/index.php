<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\widgets\Pjax;

	/** @var yii\web\View $this */
/** @var app\models\Url $model */
/** @var ActiveForm $form */
?>
<div class="index">

    <?php $form = ActiveForm::begin(); ?>

	<?php echo $form->errorSummary($model); ?>

        <?= $form->field($model, 'website') ?>

        <div class="form-group">
            <?= Html::submitButton('Submit', ['class' => 'btn btn-primary']) ?>
        </div>
		<div class="qr"></div>
		<div class="shot"></div>
    <?php ActiveForm::end();
		$js = <<<JS
		$('form').on('beforeSubmit', function(){
		var data = $(this).serialize();
		$.ajax({
			url: '/',
			type: 'POST',
			data: data,
			success: function(res){
				if (res['error']) return alert(res['error']);				
			console.log(res);
			$('.qr').html(res['qr']);
			$('.shot').html('<br><a href="'+res['shot']+'">'+res['shot']+'</p>');
			},
			error: function(){
			alert('Error!');
			}
			});
			return false;
		});
		JS;
	$this->registerJs($js);
	?>


</div><!-- index -->
