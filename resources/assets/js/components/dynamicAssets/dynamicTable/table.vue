<template lang="html">

  <div>

    <div class="row">
      <div class="col-md-4 pull-left text-left">
        <button class="btn btn-primary" @click="moveTableRow({dir:'indexDn', rows:1})"><i class="glyphicon glyphicon-chevron-up"></i></button>
        <button class="btn btn-primary" @click="moveTableRow({dir:'indexUp', rows:1})"><i class="glyphicon glyphicon-chevron-down"></i></button>
      </div>

      <div class="col-md-4 pull-right text-right">
        <button class="btn btn-primary" @click="moveTableRow({dir:'indexDn', rows:10})"><i class="glyphicon glyphicon-chevron-left"></i></button>
        <button class="btn btn-primary" @click="moveTableRow({dir:'indexUp', rows:10})"><i class="glyphicon glyphicon-chevron-right"></i></button>
      </div>
    </div>

    <br/>

    <div class="row">
      <div class="col-md-12">
        <table
          v-if="mutableTableRowsData"
          class="table table-hover">
          <thead>
            <tr>
              <th>#</th>
              <th v-for="columnHeader in tableHeaders">{{ columnHeader }}</th>
            </tr>
          </thead>
          <tbody>
            <tr
            v-for="(row, index) in mutableTableRowsData"
            v-show="index >= tableVisibleRows.visibleFrom &&
                    index <= tableVisibleRows.visibleFrom + tableVisibleRows.rowsVisible"
            v-bind:class="index === tableVisibleRows.curHighlight ? 'current-transaction' : ''"
            @click="updateRowOnClick({data:row, index:index})"
            >
              <td>{{ index + 1 }}</td>
              <td
                v-for="(data, key) in row"
                v-if="Object.keys(tableHeaders).indexOf(key) > -1"
              >
                {{ data }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>

</template>

<script>

/**
 * Sample way of calling this table component in other Vue components
 *
 *  <load-table
 *    :table-headers="{
 *      name_assigned: 'Assigned Name',
 *      name_from_transaction: 'Transaction Name',
 *      category_name_from_transaction: 'Transaction Category'
 *    }"
 *    :table-rows="untaggedVendors">
 *  </load-table>
 *
 * table-rows passes the bulk data
 *
 * table-headers passes key/value object of the table-rows keys
 * NOTE this requires BOTH key AND value, the value replaces the key as a header
 */

import {mapState, mapMutations, mapActions, mapGetters} from 'vuex';

export default {
  props: ['tableHeaders', 'tableRows'],

  data() {
    return {

    }
  },

  computed: {
    ...mapState('paginatedTableComponentStore',
    [
      'currentRow',
      'mutableTableRowsData',
      'tableVisibleRows'
    ]),
  },

  methods: {

    // Vuex Methods
    ...mapMutations(
      'paginatedTableComponentStore',[
        'moveTableRow',
        'updateRowOnClick',
        'passTableRowsToStore',
      ]
    ),

  },

  mounted() {
    var vm = this;

    vm.passTableRowsToStore(vm.tableRows);

  }
}
</script>

<style lang="css">
.current-transaction {
  background: #3097D1;
  color: #fff;
  border-color: none !important;
}

.current-transaction:hover {
  background: #3097D1 !important;
}
</style>
