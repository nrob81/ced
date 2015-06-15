<html>
    <body>
    <p>Szia <?= $model->username ?>! Az új jelszó beállítását ezen az oldalon végezheted el:<br/></p>
    <p>
        <?php
        $url=Yii::app()->createAbsoluteUrl('/account/completeResetPassword', array(
			'id'=>$model->id,
			'code'=>$model->resetPasswordCode,
		)); 
        echo CHtml::link($url, $url);
        ?>
    </p>
    <p>Üdvözlettel,<br/>
    Carp-e Diem</p>
	</body>
</html>

