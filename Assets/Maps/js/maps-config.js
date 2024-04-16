const zoom = 13;
const lat = -0.21939544408695616;
const lng = -78.51056720972429;
const ALL_ZONES_PARSED = getParsedZones()


let configMap = {
  minZoom: 7,
  maxZoom: 18,
  zoomControl: false, // zoom control off
};

const options = {
  position: "topleft", // toolbar position, options are 'topleft', 'topright', 'bottomleft', 'bottomright'
  drawMarker: false, // adds button to draw markers
  drawPolygon: true, // adds button to draw a polygon
  drawPolyline: false, // adds button to draw a polyline
  drawCircle: true, // adds button to draw a cricle
  editPolygon: true, // adds button to toggle global edit mode
  deleteLayer: true, // adds a button to delete layers
  cutPolygon: false, // adds a button to cut a hole in a polygon
  drawCircleMarker: false, // adds a button to change a circle into a marker
  rotateMode: false, // adds a button to rotate the selected layer
};

function getCurrentZone () {
  const currentZone = $(".currentZones").val();
  if (!currentZone.length) {
    return null
  }
  let zoneObject = ALL_ZONES_PARSED.find(zone =>
    zone.id_zona === currentZone[currentZone.length - 1]
  )
  return {
    code: currentZone[currentZone.length - 1],
    zone: zoneObject
  }
}

function getParsedZones() {
  let zones = JSON.parse(atob(ALL_ZONES))
  let parsedZones = []

  zones.forEach((zone) => {
    let item = {...zone}
    if (zone.puntos_zona !== null && zone.puntos_zona !== "") {
      item.puntos_zona = JSON.parse(zone.puntos_zona)
    }
    parsedZones.push(item)
  })
  return parsedZones
}

