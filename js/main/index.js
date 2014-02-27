function initDoc() {
    initMap(PLANEJSON);
    initPortlet();
} 
function initPortlet() {
    $j(function() {
        $j( ".column" ).sortable({
            connectWith: ".column",
//            handle: ".porlet-header"
        });
        $j( ".portlet" ).addClass( "ui-widget ui-widget-content ui-helper-clearfix ui-corner-all" )
            .find( ".portlet-header" )
                .addClass( "ui-widget-header ui-corner-all" )
                .prepend( "<span class='ui-icon ui-icon-minusthick'></span>")
                .end()
            .find( ".portlet-content" );
        $j( ".portlet-header .ui-icon" ).click(function() {
            $j( this ).toggleClass( "ui-icon-minusthick" ).toggleClass( "ui-icon-plusthick" );
            $j( this ).parents( ".portlet:first" ).find( ".portlet-content" ).toggle();
        });
//        $j( ".column" ).disableSelection();
        $j("select, input, a.button, button").uniform();
    });
}

function initMap(PLANEJSON) {
    var Zoom = 9;
    if (PLANEJSON != null) {
        var Planes = PLANEJSON;
//        var Planes = JSON.parse(PLANEJSON);
        Lat = Planes[0]['latitude'];
        Lon = Planes[0]['longitude'];

        var firstPoint = new L.LatLng(Lat,Lon);
        var map = L.map('map').setView(firstPoint, Zoom);
        
        L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        for (var i = 0; i < Planes.length; i++) {
                marker = new L.marker([Planes[i]['latitude'],Planes[i]['longitude']])
                        .bindPopup(Planes[i]['nameCPI'])
                        .addTo(map);
        }
        
    }
}

function makeMap(Lat, Lon, Zoom, PopupContent) {
    var Position = new L.LatLng(Lat, Lon);
    var map = L.map('map').setView(Position, Zoom);

    // add an OpenStreetMap tile layer
    L.tileLayer('http://{s}.tile.osm.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // add a marker in the given location, attach some popup content to it and open the popup
//    L.marker([51.5, -0.09]).addTo(map)
    L.marker(Position).addTo(map)
        .bindPopup(PopupContent)
//        .bindPopup('A pretty CSS3 popup. <br> Easily customizable.')
        .openPopup();
}