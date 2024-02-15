import Vue from 'vue'
import Vuex from 'vuex'

Vue.use(Vuex);

//Separate Module States
import vendorTaggingStore from './modules/vendorTagging/store';
import paginatedTableComponentStore from './modules/paginatedTableComponent/store';

export default new Vuex.Store({
    modules: {
        vendorTaggingStore: vendorTaggingStore,
        paginatedTableComponentStore: paginatedTableComponentStore
    }
})
