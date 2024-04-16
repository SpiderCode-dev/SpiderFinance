const map = L.map("view-map", configMap).setView([lat, lng], zoom);

map.pm.setLang("es");
map.pm.addControls(options);
map.pm.disableDraw("Polygon");


L.tileLayer("https://tile.openstreetmap.org/{z}/{x}/{y}.png", {
  attribution: '&copy; <a href="https://spidercode.dev/">Spidercode</a> contributors',
}).addTo(map);

L.control.zoom({ position: "topright" }).addTo(map);

var icono_myubi = new L.Icon({
  iconUrl: "https://cdn-icons-png.flaticon.com/512/14025/14025515.png",
  iconSize: [35, 35],
});

var icono_cajaNab = new L.Icon({
  iconUrl: "https://cdn-icons-png.flaticon.com/512/1991/1991118.png",
  iconSize: [35, 35],
});

var icono_cliente = new L.Icon({
  iconUrl: "https://cdn-icons-png.flaticon.com/512/9553/9553828.png",
  iconSize: [35, 35],
});


$(document).ready(function () {
  setInterval(get_my_ubi, 5000);
});
get_my_ubi();

function get_my_ubi() {
  if (navigator.geolocation) {
    navigator.geolocation.getCurrentPosition(
      function(position) {
        var latitud = position.coords.latitude;
        var longitud = position.coords.longitude;

        map.eachLayer(function (layer) {
          if (layer instanceof L.Marker) {
            if(layer.options.title == "Mubicacion"){
              map.removeLayer(layer);
            }
          }
        });

        var mrubi = L.marker([latitud, longitud],{title: "Mubicacion", icon: icono_myubi }).addTo(map);

        mrubi.bindTooltip("Mi Ubicación", {
          permanent: false,
          direction: "top",
          offset: [0, -15],
        });
      },
    );
  }    
}

function consultInfo(){

  consultCajaNap();
}

function consultCajaNap(){
  if ($('input[name="cajanap"]').is(':checked')) {
    data = {
			action: "get-naps-location",
			textcajanap: $('input[name="textcajanap"]').val(),
      idcajan: $('input[name="idcaja"]').val(),
      idzonen: $('input[name="idzone"]').val()
		}

    $.ajax({
      url: "/MapsInstallations",
      method: "POST",
      data: data,
      success: function (response) {
        console.log(response);
        result = JSON.parse(response);
        dibujaCajaNab(result);
      },
    });
  }
}


function dibujaCajaNab(data){
  map.eachLayer(function (layer) {
    if (layer instanceof L.Marker) {
      if(layer.options.title == "caNab"){
        map.removeLayer(layer);
      }
    }
  });

  $.each(data, function (key, val) {
    if (val.coords != "") {

      coordenadas = val.coords.replace(/\s/g, '');
      var partes = coordenadas.split(',');
      var lat = partes[0];
      var lon = partes[1];

      var mrkCajaNap = L.marker([lat,lon],{title: "caNab", icon: icono_cajaNab }).addTo(map);

      mrkCajaNap.bindTooltip("Caja: "+val.code, {
        permanent: true,
        direction: "top",
        offset: [0, -15],
      });

      mrkCajaNap.bindPopup(
        `<div class="popup-content">
          <h3>Code: ${val.code}</h3>
          <p>Dirección: ${val.address}</br>
          Número Nap: ${val.number_nap}</br>
          Distrito: ${val.district}</br>
          Número de Puertos: ${val.number_ports}</br>
          Observaciones: ${val.observations}</p>
        </div>`
        );
    }
  });
}