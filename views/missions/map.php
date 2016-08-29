<?php
$this->pageTitle='Térkép';
$this->pageID = 'index';
$baseUrl = Yii::app()->baseUrl;
$cs = Yii::app()->getClientScript();
$cs->registerScriptFile('https://maps.google.com/maps/api/js?key=AIzaSyBri9OCmH4uHnA46rwYTdMlGTDhaMDUZtk', CClientScript::POS_END);
$cs->registerScriptFile('https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-map/3.0-rc1/jquery.ui.map.extensions.min.js', CClientScript::POS_END);
$cs->registerScriptFile('https://cdnjs.cloudflare.com/ajax/libs/jquery-ui-map/3.0-rc1/min/jquery.ui.map.full.min.js', CClientScript::POS_END);
$cs->registerScript('mapSize', "
$(document).on('pageshow', '#index',function(e,data){
    $('#content').height(getRealContentHeight());
    $('#map_canvas').height(getRealContentHeight());
    function getRealContentHeight() {
        var header = $.mobile.activePage.find(\"div[data-role='header']:visible\");
        var footer = $.mobile.activePage.find(\"div[data-role='footer']:visible\");
        var content = $.mobile.activePage.find(\"div[data-role='content']:visible:visible\");
        var attributes = $.mobile.activePage.find(\"#attributes:visible\");
        var viewport_height = $(window).height();

        var content_height = viewport_height - header.outerHeight() - footer.outerHeight() - attributes.outerHeight();
        if((content.outerHeight() - header.outerHeight() - footer.outerHeight() - attributes.outerHeight()) <= viewport_height) {
            content_height -= (content.outerHeight() - content.height());
        }
        return content_height;
    }
});
", CClientScript::POS_END);

$cs->registerScript('map', "
$(function() {
    var myStyles = [
        {
            featureType: 'poi',
            elementType: 'labels',
            stylers: [
                  { visibility: 'off' }
            ]
        }
    ];

    var map = new google.maps.Map(document.getElementById('map_canvas'), {
        zoom: 8,
        minZoom: 7,
        maxZoom: 12,
        center: new google.maps.LatLng({$center}),
        mapTypeId: google.maps.MapTypeId.TERRAIN,
        disableDefaultUI: true,
        zoomControl: true,
        styles: myStyles,
    });

var locations = [{$locations}];
var last = [{$last}];
var infowindow = new google.maps.InfoWindow(), marker, i;
var mcolor;

console.log(last);

    for (i = 0; i < locations.length; i++) {
        mcolor = ($.inArray(locations[i][3], last) > -1) ? 'sepia' : 'red';
        marker = new google.maps.Marker({
        position: new google.maps.LatLng(locations[i][0], locations[i][1]),
        map: map,
        icon: '../images/marker_mini_' + mcolor + '.png'
    });

    google.maps.event.addListener(marker, 'click', (function(marker, i) {
    return function() {
      infowindow.setContent('<div class=\"info-window\"><strong>' + locations[i][2] + '</strong><p>'+ locations[i][4] +'</p><p><a href=\'/missions/list/' + locations[i][3] + '\' data-ajax=\'false\'>utazás</a></p></div>');
      infowindow.open(map, marker);
    }
  })(marker, i));
}

});
", CClientScript::POS_END);
?>

<div id="map_canvas" data-role="content" style="height:100%; width:100%;"></div>
