
/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require('./bootstrap');

window.Vue = require('vue');

/**
 * Load vue filters
 */

window.vueFilter = require('vue-filter');
Vue.use(vueFilter);

/**
 * Load VueGoogleMaps
 */

const VueGoogleMaps = require('vue2-google-maps');
Vue.use(VueGoogleMaps, {
  load: {
    key: 'AIzaSyCRG9uCFlcAxbFUa4rUHE9rW_YCNcg8Azo',
    libraries: 'places'
  }
})

/**
 * Load Vuex and global store
 */
import Vuex from 'vuex';
import store from './vuex/store.js';
Vue.use(Vuex);

/**
 * Load VueAwesomeSwiper
 * http://idangero.us/swiper/api/
 */

import VueAwesomeSwiper from 'vue-awesome-swiper'

// VueAwesomeSwiper require styles
import 'swiper/dist/css/swiper.css'

Vue.use(VueAwesomeSwiper, {
  slidesPerView: 7,
  grabCursor: true,
  spaceBetween: 10,
  slidesPerGroup: 6,

  navigation: {
    nextEl: '.swiper-button-next',
    prevEl: '.swiper-button-prev',
  },

  breakpoints: {
    // when window width is <= 480px
    480: {
      slidesPerView: 1,
      spaceBetween: 10,
      slidesPerGroup: 1,
    },
    // when window width is <= 640px
    640: {
      slidesPerView: 2,
      spaceBetween: 10,
      slidesPerGroup: 2,
    },
    // when window width is <= 860px
    860: {
      slidesPerView: 3,
      spaceBetween: 10,
      slidesPerGroup: 2,
    },
    // when window width is <= 1160px
    1160: {
      slidesPerView: 4,
      spaceBetween: 10,
      slidesPerGroup: 3,
    },
    // when window width is <= 1400px
    1400: {
      slidesPerView: 5,
      spaceBetween: 10,
      slidesPerGroup: 4,
    }
  }
});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

Vue.component('grouponMap', require('./components/grouponMap/Map.vue'));
Vue.component('grouponViewer', require('./components/grouponViewer/grouponViewer.vue'));
Vue.component('vendorTagging', require('./components/vendorTagging/vendorTagging.vue'));
Vue.component('vendorVerification', require('./components/decisionLogic/vendorVerification.vue'));
Vue.component('memberSubmitBankInfo', require('./components/decisionLogic/memberSubmitBankInfo.vue'));

const app = new Vue({
    el: '#app',
    store: store
});
