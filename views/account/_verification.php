<html>
	<body>
    <p>Köszöntelek a Carp-e Diem játékban!<br/>
    Már csak egy lépés választ el attól, hogy játszhass. Kattints az itt látható linkre és állíts be magadnak egy nevet és jelszót.<br/></p>
    <p>
        <?php
        $url=Yii::app()->createAbsoluteUrl('/account/completeSignup', array(
            'id'=>$model->id,
            'code'=>$model->verifyCode,
        )); 
        echo CHtml::link($url, $url);
        ?>
    </p>
    <p>Üdvözlettel,<br/>
    Carp-e Diem</p>
	</body>
</html>
