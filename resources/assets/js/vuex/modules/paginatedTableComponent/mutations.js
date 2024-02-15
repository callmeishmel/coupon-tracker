const mutations = {

  passTableRowsToStore(state, payload) {
    state.mutableTableRowsData = payload;
    state.currentRow = payload[0];
  },

  // Parameters are payload.dir payload.rows
  moveTableRow(state, payload) {
    var dir = payload.dir;
    var rows = payload.rows;
    var list = state.tableVisibleRows;

    if(dir === 'indexUp') {
      var nextStep = list.curHighlight + rows;

      // if 'index' step up is higher than the list set 'from' and 'index' to 0
      if(nextStep > state.mutableTableRowsData.length - rows) {
        list.visibleFrom = 0;
        list.curHighlight = 0;
      } else {
        // if 'index' is greater or equals to 'from' plus visible rows add visible rows to 'from'
        if(nextStep > list.visibleFrom + list.rowsVisible) {
          list.visibleFrom += (list.rowsVisible + 1);
        }
        list.curHighlight += rows;
      }

    } else if(dir === 'indexDn') {
      var prevStep = list.curHighlight - rows;

      // if 'index' step down is less than 0 set 'from' to be the length of the list minus the visible rows
      if(prevStep < 0) {
        list.visibleFrom = state.mutableTableRowsData.length - (list.rowsVisible + rows);
        list.curHighlight = state.mutableTableRowsData.length - rows;
      } else {
        if(prevStep < list.visibleFrom) {
          list.visibleFrom -= (list.rowsVisible + 1);
        }
        list.curHighlight -= rows;
      }

    }

    state.currentRow = state.mutableTableRowsData[list.curHighlight];
  },

  // Parameters are payload.data payload.index
  updateRowOnClick(state, payload) {
    state.currentRow = payload.data;
    state.tableVisibleRows.curHighlight = payload.index;
  },

  updateCurrentRow(state, payload) {
    state.currentRow = payload;
  },
  
}

export default mutations;
