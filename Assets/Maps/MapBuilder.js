class MapBuilder {
    constructor(id, lat, lng, zoom = 13, options = {}) {

        let myStyles =[
            {
                featureType: "poi",
                elementType: "labels",
                stylers: [
                    { visibility: "off" }
                ]
            }
        ];

        let myOptions = {
            center: new google.maps.LatLng(lat, lng),
            mapTypeId: google.maps.MapTypeId.ROADMAP,
            styles: myStyles,
            zoom: 13,
            ...options
        };

        this.map = new google.maps.Map(
            document.getElementById(id),
            myOptions
        );
    }

    addMarker(lat, lng, title, icon = null) {
        let mark = {
            position: new google.maps.LatLng(lat, lng),
            map: this.map,
            title: title,
        }
        if (icon) {
            mark.icon = icon;
        }

        let marker = new google.maps.Marker(mark);

    }


    addMarkerWithInfoWindow(lat, lng, title, content, icon = null) {
        let mark = {
            position: new google.maps.LatLng(lat, lng),
            map: this.map,
            title: title,
        }
        if (icon) {
            mark.icon = icon;
        }

        let marker = new google.maps.Marker(mark);

        let infoWindow = new google.maps.InfoWindow({
            content: content
        });

        marker.addListener('click', function() {
            infoWindow.open(this.map, marker);
        });
    }

    drawLine(lat1, lng1, lat2, lng2) {
        let line = new google.maps.Polyline({
            path: [
                new google.maps.LatLng(lat1, lng1),
                new google.maps.LatLng(lat2, lng2)
            ],
            strokeColor: "#FF0000",
            strokeOpacity: 1.0,
            strokeWeight: 2,
            map: this.map
        });
    }

    addMakers(markers) {
        markers.forEach((marker) => {
            this.addMarker(marker.lat, marker.lng, marker.title, marker.icon);
        });
    }
}