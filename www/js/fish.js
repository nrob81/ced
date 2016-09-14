$(document).on("mobileinit", function(){
    $.mobile.ajaxEnabled = false;
});

$(document).on("pageinit", function(event){
    updateRefillTime();
    advancementForm();
}); 


function advancementForm() {
    if (justAdvanced == true) {
        setTimeout(function () {
            //create a div for the popup
            var $popUp = $("<div/>").popup({
                transition: "pop",
                theme: 'b'
            }).on("popupafterclose", function () {
                //remove the popup when closing
                $(this).remove();
            }).css({
                'width': '290px',
                'padding': '15px',
            });

            //create a back button
            $("<a>", {
                text: "Bezárás",
                "data-rel": "back"
            }).buttonMarkup({
                theme: "b",
                icon: "delete",
                iconpos: "notext"
            }).addClass('ui-btn-right').appendTo($popUp);

            //create a title for the popup
            $("<h3/>", {
                text: "Fejlődtél!"
            }).appendTo($popUp);

            //create a message for the popup
            $("<p/>", {
                html: "A sok pecázás meghozta gyümölcsét!<br/>Szereztél pár státuszpontot, amit felhasználhatsz a karaktered fejlesztésére."
            }).appendTo($popUp);

            //create a back button
            $("<a>", {
                text: "Fejlesztés",
                href: "/player"
            }).buttonMarkup({
                role: "button",
                ajax: "false"
            }).appendTo($popUp);

            $popUp.popup('open').trigger("create");      
        }, 0);
    }
}

function updateRefillTime() {
    $('#refillTime').countdown({
        until: refillTime,
        layout: '{mnn}{sep}{snn} {desc}',
        description: 'múlva +' + rpi,
        expiryText: 'eleget pihentél',
    })
}
