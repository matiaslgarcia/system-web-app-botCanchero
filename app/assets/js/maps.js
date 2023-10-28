var markers = []; // Lista para mantener los marcadores

// Definir la función initMap en el ámbito global
function initMap() {
    // Coordenadas del centro de Argentina
    inputLatitud = document.querySelector('#Longitud')
    inputLongitude = document.querySelector('#Longitud')
    latitude  = (Boolean(inputLatitud.value))    ? inputLatitud.value   : -34.611781
    longitude = (Boolean( inputLongitude.value)) ? inputLongitude.value : -58.417309


    var argentinaCenter = {lat: latitude, lng: longitude};

    // Crear una instancia del mapa
    var map = new google.maps.Map(document.getElementById('map'), {
        center: argentinaCenter,
        zoom: 6 // Ajusta el nivel de zoom según tus preferencias
    });

    // Agregar un evento de clic al mapa
    map.addListener('click', function(event) {
        var latitud = event.latLng.lat();
        var longitud = event.latLng.lng();

        // Eliminar los marcadores anteriores
        clearMarkers();

        // Crear un nuevo marcador en la ubicación seleccionada
        var marker = new google.maps.Marker({
            position: event.latLng,
            map: map
        });

        // Agregar el nuevo marcador a la lista de marcadores
        markers.push(marker);

        // Mostrar la latitud y longitud en una ventana emergente (InfoWindow)
        var infowindow = new google.maps.InfoWindow({
            content: 'Latitud: ' + latitud + '<br>Longitud: ' + longitud
        });

        marker.addListener('click', function() {
            infowindow.open(map, marker);
        });

        document.querySelector('#Latitud').value = latitud
        document.querySelector('#Longitud').value = longitud
    });

    // Función para eliminar todos los marcadores de la lista
    function clearMarkers() {
        markers.forEach(function(marker) {
            marker.setMap(null);
        });

        markers = [];
    }
}