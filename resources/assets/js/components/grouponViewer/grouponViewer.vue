<template lang="html">

<div>

  <div
    style="overflow: hidden;"
    v-show="displayComponent"
    v-if="grouponData !== null">

    <h3 style="margin-top: 0;">
      {{ grouponData.deals.length }}
      <span>{{ formattedSearchCategory }}</span>
      Grupons Found
    </h3>

    <swiper ref="mySwiper" id="carousel-deals-row">
      <!-- slides -->
      <swiper-slide
        class="carousel-deal-container"
        :style="'background-image: url(\'' + groupon.largeImageUrl + '\')'"
        v-for="groupon in grouponData.deals" :key="groupon.id">

        <div @click="setActiveDealData(groupon)" style="height: 300px; border: 1px solid #c3c3c3;">

          <div class="carousel-deal-value">
            {{ groupon.options[0].value.formattedAmount }}
          </div>
          <div class="carousel-deal-discount">
            {{ groupon.options[0].discountPercent }}% OFF
          </div>

          <div
            class="focus-toggle"
            :class="[ activeDeal.data.id === groupon.id ? 'focus-toggle-on' : 'focus-toggle-off' ]">
            <i
              v-if="activeDeal.data.id === groupon.id"
              class="glyphicon glyphicon-zoom-out"></i>
            <i v-else class="glyphicon glyphicon-zoom-in"></i>
          </div>

          <div class="carousel-deal-title">
            <span style="vertical-align: -webkit-baseline-middle;">{{ groupon.shortAnnouncementTitle }}</span>
          </div>
        </div>

      </swiper-slide>
      <!-- Optional controls -->
      <div class="swiper-button-prev" slot="button-prev"></div>
      <div class="swiper-button-next" slot="button-next"></div>
    </swiper>

    <div
      class="focused-deal-container"
      :class="[ activeDeal.visible ? 'focused-deal-open' : 'focused-deal-closed' ]">

      <div class="col-md-12" style="background: #3db0c2; margin-bottom: 20px; border-radius: 5px;">
        <span style="font-size: 25px; font-weight: bold;">
          {{ activeDeal.visible ? activeDeal.data.announcementTitle : null }}
          <i
            class="glyphicon glyphicon-remove pull-right focused-deal-close-btn"
            @click="resetActiveDeal()"
          ></i>
        </span>
      </div>

      <div class="col-md-6 focused-deal-left">
        <span v-html="activeDeal.visible ? activeDeal.data.pitchHtml : null"></span>
      </div>

      <div class="col-md-6 focused-deal-right">

        <img
          :src="activeDeal.visible ? activeDeal.data.largeImageUrl : null"
          :alt="activeDeal.visible ? activeDeal.data.largeImageUrl : null">

        <br/>
        <br/>

        <a
          href="#"
          class="btn btn-success"
          style="font-size: 27px; font-weight: bold;">
          BUY
        </a>

      </div>

    </div>

  </div>

  <div style="height: 200px;" v-if="!isDataLoaded">
    <load-spinner :on-display-msg="'Loading ' + formattedSearchCategory + ' Groupons'"></load-spinner>
  </div>

</div>

</template>

<script>

Vue.component('loadSpinner', require('../dynamicAssets/loadSpinner/spinner.vue'));

export default {

  props: ['csrfToken', 'grouponSearchCategory', 'getGrouponDataRoute'],

  data() {
    return {
      grouponData: null,
      isDataLoaded: false,
      displayComponent: true,
      activeDeal: {
        visible: false,
        data: {
          id: null
        }
      }
    }
  },

  computed: {
    formattedSearchCategory: function() {
      var vm = this;

      return vm.grouponSearchCategory.split('-')
      .map(function(word) {
        return word[0].toUpperCase() + word.substr(1);
      })
      .join(' ');

    }
  },

  methods: {

    setActiveDealData: function(data) {
      var vm = this;

      // Check if a deal has been clicked and focused on
      if(vm.activeDeal.visible) {
        // ONLY update the data if another deal was clicked
        if(vm.activeDeal.data.id !== data.id) {
          vm.activeDeal.data = data;
          return;
        }
        // Close the panel and reset the data if the same deal was clicked
        vm.resetActiveDeal();
      } else {
        // Make deal panel visible and store the groupon data
        vm.activeDeal.visible = true;
        vm.activeDeal.data = data;

      }

    },

    resetActiveDeal() {
      var vm = this;

      vm.activeDeal.visible = false;
      vm.activeDeal.data = {
        id: null
      }
    },

    getGrouponData() {
      var vm = this;

      $.ajax({
        method: "GET",
        url: vm.getGrouponDataRoute,
        data: {
          lat: 39.457665,
          lng: -111.364852,
          filters: {
            category: vm.grouponSearchCategory
          }
        },
        headers: {
          'X-CSRF-TOKEN': vm.csrfToken
        }
      })
       .done(function(response) {
         vm.grouponData = JSON.parse(response);
         vm.isDataLoaded = true;
         if(vm.grouponData.deals.length < 1) {
           vm.displayComponent = false;
         }
       })
       .fail(function(response) {
         console.log(response);
       });
    }

  },

  mounted() {
    var vm = this;

    vm.getGrouponData();

  }

}
</script>

<style lang="css">

  #carousel-deals-row {
    transition: .5s transform;
    overflow: visible;
    margin-bottom: 25px;
    background: #fff;
  }

  #carousel-deals-row:hover {
    transform: translate3d(-10px, 0, 0);
  }

  .carousel-deal-container {
    height: 300px;
    -webkit-box-sizing: border-box;
    background-size: cover;
    -webkit-transform-origin: top left;
    transform-origin: top left;
    -webkit-transition: .5s transform;
    transition: .5s transform;
    -webkit-transition: .5s;
    transition: .5s;
    background-position: center;
    -webkit-box-shadow: 0px 2px 2px rgba(0,0,0,.4);
    cursor: pointer;
  }

  .carousel-deal-container:hover {
    transform: scale(1.07);
    -webkit-transition: .5s;
    transition: .5s;
  }

  .carousel-deal-container:hover ~ .carousel-deal-container {
    transform: translate3d(16px, 0, 0);
  }

  .carousel-deal-title {
    position: absolute;
    bottom: 10px;
    left: 0;
    right: 0;
    margin-left: auto;
    margin-right: auto;
    width: 100%;
    min-height: 50px;
    background: rgba(0,0,0,.7);
    color: #fff;
    text-align: center;
    font-size: 18px;
    font-weight: bold;
    line-height: 15px;
    border-top: 1px solid rgba(255,255,255,.35);
    border-bottom: 1px solid rgba(255,255,255,.35);
  }

  .carousel-deal-value {
    position: absolute;
    right: 0;
    background: #3db0c2;
    color: #fff;
    font-size: 26px;
    font-weight: bold;
    padding: 0 5px;
    text-align: center;
    box-shadow: 0px 1px 2px rgba(0, 0, 0, .6);
  }

  .carousel-deal-discount {
    position: absolute;
    top: 5px;
    left: 5px;
    background: #f1820b;
    border: 1px solid #ef7000;
    border-radius: 2px;
    font-size: 14px;
    font-weight: bold;
    text-align: center;
    color: #fff;
    padding: 0 5px;
  }

  .focused-deal-close-btn {
    position: absolute;
    top: 5px;
    right: 5px;
    font-size: 30px;
    cursor: pointer;
    -webkit-transition: .5s;
    transition: .5s;
  }

  .focused-deal-close-btn:hover {
    -webkit-transform: rotate(90deg);
    -moz-transform: rotate(90deg);
    -ms-transform: rotate(90deg);
    -o-transform: rotate(90deg);
    filter: progid:DXImageTransform.Microsoft.BasicImage(rotation=3);
    -webkit-transition: .5s;
    transition: .5s;
  }

  .focused-deal-container {
    width: 100%;
    color: #fff;
    border-radius: 5px;
    overflow: hidden;
    border: 1px solid #a3c6cb;
  }

  .focused-deal-container ul {
    list-style-type: none;
  }

  .focused-deal-open {
    height: auto;
    visibility: visible;
    padding: 15px;
    margin-bottom: 20px;
    max-height: 492px;
    transition: max-height .7s ease-in;
  }

  .focused-deal-closed {
    visibility: hidden;
    padding: 0;
    max-height: 0;
    transition: max-height .7s ease-out;
  }

  .focused-deal-left {
    height: 400px;
    color: #636b6f;
    overflow: auto;
  }

  .focused-deal-right {
    height: 400px;
    background-position: center;
    background-size: contain;
    background-repeat: no-repeat;
  }

  .focus-toggle {
    color: #fff;
    font-size: 100px;
    opacity: .8;
    text-align: center;
    margin-top: calc(50% - 30px);
    text-shadow: -1px 4px 8px rgba(0,0,0,.7);
  }

  .focus-toggle-off {
    display: none;
  }

  .focus-toggle-on {
    display: block;
  }

  .carousel-deal-container:hover .focus-toggle {
    display: block;
  }

</style>
