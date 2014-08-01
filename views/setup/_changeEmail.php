<html>
    <body>
    <p>Szia <?= $model->username ?>! Az új e-mail cím aktiválását ezen az oldalon végezheted el:<br/></p>
    <p>
        <?php
        $url=Yii::app()->createAbsoluteUrl('/account/completeChangeEmail', array(
			'id'=>$model->id,
			'code'=>$model->changeMailCode,
		)); 
        echo CHtml::link($url, $url);
        ?>
    </p>
    <p>Üdvözlettel,<br/>
    Carp-e Diem</p>
	</body>
</html>
