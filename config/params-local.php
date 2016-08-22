<?php
return [
    'adminEmail'=>'natkay.robert@nrcode.com',
    'isPartOfWline' => false, //wline or ced.hu?
    'maxtime'=>1800, //30min
    'cacheDuration'=>3600,
    'waters_version'=>3,
    'visited_version'=>3,
    'missions_version'=>3,
    'baits_version'=>1,
    'items_version'=>1,
    'parts_version'=>1,
    'parts_version'=>2,
    'listPerPage'=>10,
    'wlineHost'=>'//wline.local/',
    'packagesSms' => [
        //1=>['price'=>'508', 'descr'=>'25 arany', 'discount'=>0, 'amount'=>30, 'tel'=>'06-90-643-123'],
        //2=>['price'=>'1016', 'descr'=>'50 + 5 arany', 'discount'=>10, 'amount'=>55, 'tel'=>'06-90-888-340'],
        ],
    'cronPassword' => '123',
    'wlineUsersTable' => 'adatok',
    'wlineRefreshAttribute' => 'lastfresh',
    'smsDate' => 213,
    'smsText' => '<span class="success">Péntek 13:</span> ma egész nap 50 arannyal többet kapsz vásárláskor.',
];
