function initialize() {

    var maps = document.getElementsByClassName("maparea-view");
    var i;
    
    for (i = 0; i < maps.length; i++) {
        var parent = maps[i].parentElement;
        var lat = parent.querySelector('.lat').value;
        var lng = parent.querySelector('.lng').value;
        
        var uluru = {lat: parseFloat(lat), lng: parseFloat(lng)};
      
        // The map, centered at Uluru
        var map = new google.maps.Map(
            maps[i], {zoom: 12, center: uluru});
        // The marker, positioned at Uluru
        var marker = new google.maps.Marker({position: uluru, map: map});
    }
}

google.maps.event.addDomListener(window, 'load', initialize);
