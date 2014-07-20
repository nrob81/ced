<html>
	<body>
        Köszöntelek a Carp-e Diem játékban!<br/>
        Már csak egy lépés választ el attól, hogy játszhass. Kattints az itt látható linkre és állíts be magadnak egy nevet és jelszót.<br/>
		<?php $url=Yii::app()->createAbsoluteUrl('/account/completeSignup', array(
			'id'=>$model->id,
			'code'=>$model->verifyCode,
		)); 
		echo CHtml::link($url, $url); ?>
	</body>
</html>
