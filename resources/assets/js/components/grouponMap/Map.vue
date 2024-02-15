<template lang="html">
  <div>
    <h1>Map Component</h1>

    <div class="map-container" >
      <gmap-map
        class="map-body"
        :center="{lat:mapData.pos.center.lat, lng:mapData.pos.center.lng}"
        :zoom="12">

        <span v-for="(groupon, key) in grouponData">
          <gmap-marker
            v-show="!mapData.infoWindow.isVisible"
            v-for="location in groupon.options[0].redemptionLocations"
            v-if="distanceFromSearch(location.lat, location.lng) <= mapData.searchRadius"
            @click="setInfoWindowData(groupon, location)"
            :animation="2"
            :position="{lat:location.lat, lng:location.lng}"
            :key="location.id">
          </gmap-marker>
        </span>

        <gmap-info-window
          :opened="mapData.infoWindow.isVisible"
          @closeclick="mapData.infoWindow.isVisible=false"
          :position="{lat:mapData.infoWindow.pos.lat, lng:mapData.infoWindow.pos.lng}">

          <h4 style="border-bottom: 1px solid #ddd; padding-bottom: 10px;"><a :href="mapData.infoWindow.content.link" target="_blank">{{ mapData.infoWindow.content.title }}</a></h4>
          <div class="info-window-img">
            <img :src="mapData.infoWindow.content.image" :alt="mapData.infoWindow.content.title">
          </div>
          <div class="info-window-content" v-html="mapData.infoWindow.content.pitch">
          </div>
        </gmap-info-window>

      </gmap-map>
    </div>

    <load-spinner :on-display-msg="'Searching'" v-if="!mapData.isLoaded"></load-spinner>
    <div
      v-else>

      <div>
        <label>Select Radius</label>
        <select
          @change="_updateVisibleDealList(grouponData)"
          v-model="mapData.searchRadius"
          class="">
          <option value="1">1</option>
          <option value="5">5</option>
          <option value="10">10</option>
          <option value="15">15</option>
        </select>
      </div>

      <div class="groupons">
        <h3>{{ mapData.dealsWithinRadius }} Groupons</h3>
        <div v-for="groupon in grouponData"
             v-if="groupon.isVisible">
          <a :href="groupon.dealUrl">{{ groupon.announcementTitle }}</a>
          <span
            v-for="location in groupon.options[0].redemptionLocations"
            v-if="distanceFromSearch(location.lat, location.lng) <= mapData.searchRadius">
            Distance = {{ distanceFromSearch(location.lat, location.lng) }} miles
            <button
              class="btn btn-primary"
              @click="setInfoWindowData(groupon, location)">
              View in Map
            </button>
          </span>
        </div>
      </div>

    </div>
  </div>
</template>

<script>

Vue.component('loadSpinner', require('../dynamicAssets/loadSpinner/spinner.vue'));

// Default to these coordinates in case device geolocation not available or blocked
var defaultLat = 40.3132047;
var defaultLng = -111.7092605;

export default {

  data() {
    return {
      mapData: {
        isLoaded: false,
        searchRadius: 1, // In Miles
        dealsWithinRadius: 0,
        pos: {
          center: {
            lat: defaultLat,
            lng: defaultLng
          },
          search: {
            lat: defaultLat,
            lng: defaultLng
          },
        },
        infoWindow: {
          isVisible: false,
          pos: {
            lat: defaultLat,
            lng: defaultLng
          },
          content: {
            title: null,
            link: null,
            image: null,
            message: null,
            pitch: null
          }
        }
      },
      grouponData: null
    }
  },

  methods: {

    // Convert degress to radians
    _deg2rad (angle) {
      return angle * (Math.PI / 180);
    },

    // Using
    _updateVisibleDealList(deals) {
      var vm = this;
      vm.mapData.dealsWithinRadius = 0;

      for(var i in deals) {
        deals[i]['isVisible'] = false;
        var locationList = deals[i].options[0].redemptionLocations;
        for(var j in locationList) {
          var distance = vm.distanceFromSearch(locationList[j].lat, locationList[j].lng);
          if(distance <= vm.mapData.searchRadius) {
            deals[i]['isVisible'] = true;
            vm.mapData.dealsWithinRadius++;
            break;
          };
        }
      }

      vm.grouponData = deals;
    },

    /*
     * Calculates the great-circle distance between two points, with
     * the Haversine formula.
     */
    distanceFromSearch(latitudeTo, longitudeTo) {
      var vm = this;
      var earthRadius = 6371000;
      var latitudeFrom = vm.mapData.pos.search.lat;
      var longitudeFrom = vm.mapData.pos.search.lng;

      // convert from degrees to radians
      var latFrom = vm._deg2rad(latitudeFrom);
      var lonFrom = vm._deg2rad(longitudeFrom);
      var latTo = vm._deg2rad(latitudeTo);
      var lonTo = vm._deg2rad(longitudeTo);

      var latDelta = latTo - latFrom;
      var lonDelta = lonTo - lonFrom;

      var angle = 2 * Math.asin(Math.sqrt(Math.pow(Math.sin(latDelta / 2), 2) + Math.cos(latFrom) * Math.cos(latTo) * Math.pow(Math.sin(lonDelta / 2), 2)));

      var distanceInMiles = (angle * earthRadius) * 0.000621371192;

      return parseFloat(distanceInMiles.toFixed(1));
    },

    // Update map markers visibility
    setInfoWindowData(data, location) {
      var vm = this;

      vm.mapData.infoWindow.isVisible = true;

      // Re-center map on selected location
      vm.mapData.pos.center.lat = location.lat;
      vm.mapData.pos.center.lng = location.lng;

      vm.mapData.infoWindow.pos.lat = location.lat;
      vm.mapData.infoWindow.pos.lng = location.lng;
      vm.mapData.infoWindow.content.title = data.announcementTitle;
      vm.mapData.infoWindow.content.link = data.dealUrl;
      vm.mapData.infoWindow.content.image = data.grid4ImageUrl;
      vm.mapData.infoWindow.content.pitch = data.highlightsHtml;
    },

    // Call to local api calls in CouponsPageController->fetchGrouponData
    getGrouponsByLocation() {
      var vm = this;

      var lat = vm.mapData.pos.search.lat;
      var lng = vm.mapData.pos.search.lng;
      var rad = vm.mapData.searchRadius;
      var limit = 10;

      $.ajax("../coupons/groupon-data?lat=" + lat + "&lng=" + lng + "&radius=" + rad + "&limit=" + limit, {
        success: function(data) {
          data = JSON.parse(data);
          if(data) {
            vm._updateVisibleDealList(data.deals);
            vm.mapData.isLoaded = true;
          } else {
            // TODO Handle call not getting data
            console.log('Groupon data call failed.');
          }
        },
        error: function() {
          // TODO Handle ajax call failing
          console.log('Groupon data ajax call failed.');
        }
      });
    }

  },

  // On page load
  mounted() {
    var vm = this;

    // Try HTML5 geolocation to update map search coordinates based on
    // the devices current location
    if (navigator.geolocation) {
      navigator.geolocation.getCurrentPosition(function(position) {
        vm.mapData.pos.search.lat = position.coords.latitude;
        vm.mapData.pos.search.lng = position.coords.longitude;
      }, function() {
        // TODO Handle Geolocation blocked by user or not available in device
        console.log('Geolocation available but returned error');
        vm.mapData.infoWindow.content.message = 'Error: The Geolocation service failed.';
      });
    } else {
      // TODO Handle Browser doesn't support Geolocation error
      console.log('Geolocation not available therefor returned error');
      vm.mapData.infoWindow.content.message = 'Error: Your browser doesn\'t support geolocation.';
    }

    vm.getGrouponsByLocation();

  }

}
</script>

<style lang="css">

.map-body {
  height: 600px;
  width: 100%;
}

.info-window-img {
  float: left;
  margin-right: 20px;
}

.info-window-content {
  margin-left: 10px;
}

</style>
