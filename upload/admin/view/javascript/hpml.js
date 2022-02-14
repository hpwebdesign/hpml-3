function initMap(row, lat, lng) {
    const myLatLng = {
        lat: lat,
        lng: lng
    };
    const map = new google.maps.Map(document.getElementById("map" + row), {
        zoom: 18,
        center: myLatLng,
    });

    let marker = new google.maps.Marker({
        map: map,
        draggable: true,
        position: myLatLng,
        title: ''
    });


    const input = document.getElementById("pac-input" + row);
    const searchBox = new google.maps.places.SearchBox(input);

    map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
    // Bias the SearchBox results towards current map's viewport.
    map.addListener("bounds_changed", () => {
        searchBox.setBounds(map.getBounds());

    });




    let markers = [];

    // Listen for the event fired when the user selects a prediction and retrieve
    // more details for that place.
    searchBox.addListener("places_changed", () => {
        const places = searchBox.getPlaces();

        if (places.length == 0) {
            return;
        }

        // Clear out the old markers.
        markers.forEach((marker) => {
            marker.setMap(null);
        });
        markers = [];

        // For each place, get the icon, name and location.
        const bounds = new google.maps.LatLngBounds();

        places.forEach((place) => {
            if (!place.geometry || !place.geometry.location) {
                console.log("Returned place contains no geometry");
                return;
            }

            const icon = {
                url: place.icon,
                size: new google.maps.Size(71, 71),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(17, 34),
                scaledSize: new google.maps.Size(25, 25),
            };

            // Create a marker for each place.
            markers.push(
                new google.maps.Marker({
                    map,
                    icon,
                    title: place.name,
                    position: place.geometry.location,
                })
            );
            if (place.geometry.viewport) {
                // Only geocodes have viewport.
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });
        map.fitBounds(bounds);
    });

    google.maps.event.addListenerOnce(map, 'tilesloaded', function () {
        $("#pac-input" + row).show();
    });
    google.maps.event.addListener(map, 'click', function (event) {

        marker.setPosition(event.latLng);
        setValueMap(row, event.latLng.lat(), event.latLng.lng());

    });

    marker.addListener('dragend', function (event) {
        setValueMap(row, event.latLng.lat(), event.latLng.lng());
    });


    $("#current-location" + row ).click(function () {
        if ("geolocation" in navigator) {
            // check if geolocation is supported/enabled on current browser
            navigator.geolocation.getCurrentPosition(

                function success(position) {

                    let current_location = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    marker.setPosition(current_location);

                    map.setCenter(current_location);

                    setValueMap(row, position.coords.latitude, position.coords.longitude);


                },
                function error(error_message) {

                    alert('We are not allowed to access your location, Please change in your settings!')
                });
        } else {

            alert('Browser not support geo location');
        }
    });

}

function setValueMap(row, lat, lng) {
    $(`input[name='address[${row}][map_location_lat]']`).val(lat);
    $(`input[name='address[${row}][map_location_lng]']`).val(lng);
}


function initMapStoreLocation(id, lat, lng) {
    const myLatLng = {
        lat: lat,
        lng: lng
    };
    const map = new google.maps.Map(document.getElementById(id), {
        zoom: 18,
        center: myLatLng,
    });

    let marker = new google.maps.Marker({
        map: map,
        draggable: true,
        position: myLatLng,
        title: ''
    });


    const input = document.getElementById("pac-input");
    const searchBox = new google.maps.places.SearchBox(input);

    map.controls[google.maps.ControlPosition.TOP_LEFT].push(input);
    // Bias the SearchBox results towards current map's viewport.
    map.addListener("bounds_changed", () => {
        searchBox.setBounds(map.getBounds());

    });




    let markers = [];

    // Listen for the event fired when the user selects a prediction and retrieve
    // more details for that place.
    searchBox.addListener("places_changed", () => {
        const places = searchBox.getPlaces();

        if (places.length == 0) {
            return;
        }

        // Clear out the old markers.
        markers.forEach((marker) => {
            marker.setMap(null);
        });
        markers = [];

        // For each place, get the icon, name and location.
        const bounds = new google.maps.LatLngBounds();

        places.forEach((place) => {
            if (!place.geometry || !place.geometry.location) {
                console.log("Returned place contains no geometry");
                return;
            }

            const icon = {
                url: place.icon,
                size: new google.maps.Size(71, 71),
                origin: new google.maps.Point(0, 0),
                anchor: new google.maps.Point(17, 34),
                scaledSize: new google.maps.Size(25, 25),
            };

            // Create a marker for each place.
            markers.push(
                new google.maps.Marker({
                    map,
                    icon,
                    title: place.name,
                    position: place.geometry.location,
                })
            );
            if (place.geometry.viewport) {
                // Only geocodes have viewport.
                bounds.union(place.geometry.viewport);
            } else {
                bounds.extend(place.geometry.location);
            }
        });
        map.fitBounds(bounds);
    });

    google.maps.event.addListenerOnce(map, 'tilesloaded', function () {
        $("#pac-input").show();
    });
    google.maps.event.addListener(map, 'click', function (event) {

        marker.setPosition(event.latLng);
        setValueMap(event.latLng.lat(), event.latLng.lng());

    });

    marker.addListener('dragend', function (event) {
        setValueMap(event.latLng.lat(), event.latLng.lng());
    });


    $("#current-location").click(function () {
        if ("geolocation" in navigator) {
            // check if geolocation is supported/enabled on current browser
            navigator.geolocation.getCurrentPosition(

                function success(position) {

                    let current_location = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude
                    };

                    marker.setPosition(current_location);

                    map.setCenter(current_location);

                    setValueMap(position.coords.latitude, position.coords.longitude);


                },
                function error(error_message) {

                    alert('We are not allowed to access your location, Please change in your settings!')
                });
        } else {

            alert('Browser not support geo location');
        }
    });


    function setValueMap(lat, lng) {
        $(`[name="map_location_lat"]`).val(lat);
        $(`[name="map_location_lng"]`).val(lng);
    }

}