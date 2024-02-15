import actions from './actions.js';
import mutations from './mutations.js';

const state = {
  mutableTableRowsData: null,
  currentRow: null,
  tableVisibleRows: {
    visibleFrom: 0,
    rowsVisible: 9,
    curHighlight: 0
  },
}

const getters = {

}

const module = {
    namespaced: true,
    state,
    getters,
    mutations,
    actions
};

export default module;
