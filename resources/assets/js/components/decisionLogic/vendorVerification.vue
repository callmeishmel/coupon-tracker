<template>

  <div>

    <div class="row">
      <div class="col-md-12">

        <div class="row">
          <div class="col-md-6">

            <div class="panel panel-default">
              <div class="panel-heading">

                <div class="row">
                  <div class="col-md-12">

                    <h3 class="wm-no-margin">
                      <span
                        class="wm-bold"
                        @click="verificationForm.inputValue = activeRow.category_vendor_name"
                      >
                        {{ activeRow.category_vendor_name }}
                        <span style="font-size: 14px; color: #636b6f;">
                          {{ activeRow.category_name }}
                          <a
                            :href="'http://www.google.com/search?q=' + activeRow.category_vendor_name + '+' + activeRow.category_name"
                            target="_blank">
                            <i
                              style="color: #3097D1; cursor: pointer;"
                              class="glyphicon glyphicon-search"></i>
                          </a>
                        </span>
                      </span>
                      <br/>
                      <span v-if="activeRow.transaction_description">
                        <button
                          class="btn btn-default"
                          style="margin: 5px 5px 0 0;"
                          v-for="word in activeRow.transaction_description.toLowerCase().split(' ')"
                          v-if="word !== ''"
                          @click="_addWordToAssignedNameField(word)"
                        >
                          {{ word }}
                        </button>
                        <button
                          class="btn btn-primary pull-right"
                          style="margin: 5px 0 0 5px"
                          @click="verificationForm.inputValue = activeRow.category_vendor_name"
                        >
                          Reset
                        </button>
                        <button
                          class="btn btn-primary pull-right"
                          style="margin-top: 5px"
                          @click="verificationForm.inputValue = ''"
                        >
                          Clear
                        </button>
                      </span>
                    </h3>
                  </div>

                  <div
                    class="col-md-12"
                    style="border-top: 1px solid #d3e0e9; padding-top: 10px; margin-top: 10px;">
                    <form class="form-inline"

                      @submit.prevent="verifyVendor('new')">
                      <div class="form-group">
                        <label for="assignedVendorName">Name</label>
                        <input
                          class="form-control"
                          type="text"
                          name="assignedVendorName"
                          autocomplete="off"
                          v-model="verificationForm.inputValue"
                          @keyup="formatAssignedName"
                          @change="formatAssignedName"
                          required
                        />
                        <button class="form-control btn btn-primary">Assign Vendor Name</button>
                      </div>
                    </form>
                  </div>

                  <transition name="slide-fade">
                    <div
                      class="col-md-12"
                      v-show="pageMessages.vendorVerificationForm.msg !== ''"
                      style="border-top: 1px solid #d3e0e9; padding-top: 10px; margin-top: 10px;"
                    >
                      <div
                        style="margin-bottom: 0;"
                        :class="'alert ' + pageMessages.vendorVerificationForm.class"
                      >
                        <div style="margin-bottom: 10px;">
                          {{ pageMessages.vendorVerificationForm.msg }}
                        </div>
                        <table class="table table-bordered"
                          v-if="pageMessages.vendorVerificationForm.msgTable">
                          <thead>
                            <th>Assigned Name</th>
                            <th>Transaction Name</th>
                            <th>Category</th>
                            <th>Created At</th>
                            <th>Created By</th>
                            <th>Action</th>
                          </thead>
                          <tbody>
                            <tr>
                              <td>{{ pageMessages.vendorVerificationForm.msgTable.name_assigned }}</td>
                              <td>{{ pageMessages.vendorVerificationForm.msgTable.name_from_transaction }}</td>
                              <td>{{ pageMessages.vendorVerificationForm.msgTable.category_name_from_transaction }}</td>
                              <td>{{ pageMessages.vendorVerificationForm.msgTable.created_at }}</td>
                              <td>
                                <a :href="'mailto:' + pageMessages.vendorVerificationForm.msgTable.user_email">
                                  {{ pageMessages.vendorVerificationForm.msgTable.user_email }}
                                </a>
                              </td>
                              <td>
                                <button
                                  class="btn btn-warning"
                                  @click="verifyVendor('existing')"
                                >
                                  Assign to Existing Vendor
                                </button>
                              </td>
                            </tr>
                          </tbody>
                        </table>
                      </div>
                    </div>
                  </transition>

                </div>
              </div>
              <div class="panel-body">

                <div class="row">
                  <div class="col-md-12">
                    <table class="table table-bordered">
                      <tbody>
                        <tr>
                          <td><strong>DL Category</strong></td>
                          <td>{{ activeRow.category_name }}</td>
                        </tr>
                        <tr>
                          <td><strong>DL Description</strong></td>
                          <td>{{ activeRow.transaction_description }}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>

              </div>
              <div class="panel-footer">

                <div class="row">
                  <div class="col-md-12 text-right">
                    <div class="pull-left">
                      <button class="btn btn-warning" @click="setTransactionsIgnored(activeRow.transaction_table_id)">Ignore Transaction</button>
                    </div>
                    <button class="btn btn-primary" @click="moveListRow('indexDn',1)"><i class="glyphicon glyphicon-chevron-up"></i></button>
                    <button class="btn btn-primary" @click="moveListRow('indexUp',1)"><i class="glyphicon glyphicon-chevron-down"></i></button>
                  </div>
                </div>
              </div>
            </div>

          </div>

          <div class="col-md-6">

            <div
              class="panel panel-default">
              <div class="panel-heading">
                <div class="row">
                  <div class="col-md-8 pull-left">
                    <h4 class="wm-no-margin"><span class="wm-bold">Recent Vendors Verified</span></h4>
                  </div>
                </div>
              </div>
              <div class="panel-body">
                <table class="table table-hover">
                  <thead>
                    <th>Assigned Name</th>
                    <th>Transaction Name</th>
                    <th>Transaction Category</th>
                    <th>Created At</th>
                    <th>Created By</th>
                  </thead>
                  <tbody>
                    <tr v-for="vendor in verifiedVendorList.data">
                      <td>{{ vendor.assigned_name }}</td>
                      <td>{{ vendor.name_from_transaction }}</td>
                      <td>{{ vendor.category_name_from_transaction }}</td>
                      <td>{{ vendor.created_at }}</td>
                      <td><a :href="'mailto:' + vendor.user_email">{{ vendor.user_email }}</a></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="panel-footer" style="overflow: hidden;">
                <a :href="vendorTaggingRouteName" class="btn btn-success pull-right">
                  Go to Vendor Tagging
                </a>
              </div>
            </div>

          </div>
        </div>

        <div class="row">
          <div class="col-md-12">

            <table
              class="table table-hover">
              <thead>
                <tr>
                  <th>#</th>
                  <th>DL Vendor Name</th>
                  <th>DL Category</th>
                  <th>Transaction Count</th>
                  <th>Transaction Description</th>
                </tr>
              </thead>
              <tbody>
                  <tr
                    v-model="mutableUnverifiedTransactions"
                    v-for="(transaction,index) in mutableUnverifiedTransactions"
                    v-show="index >= transactionList.visibleFrom && index <= transactionList.visibleFrom + transactionList.rowsVisible"
                    v-bind:class="index === transactionList.curHighlight ? 'current-transaction' : ''"
                    @click="updateRowOnClick(transaction, index)"
                  >
                    <td>{{ index + 1 }}</td>
                    <td>{{ transaction.category_vendor_name }}</td>
                    <td>{{ transaction.category_name }}</td>
                    <td>{{ transaction.transaction_count }}</td>
                    <td>{{ transaction.transaction_description }}</td>
                  </tr>
              </tbody>
            </table>

            <div class="col-md-4 pull-right text-right">
              <button class="btn btn-primary" @click="moveListRow('indexDn',10)"><i class="glyphicon glyphicon-chevron-left"></i></button>
              <button class="btn btn-primary" @click="moveListRow('indexUp',10)"><i class="glyphicon glyphicon-chevron-right"></i></button>
            </div>

          </div>
        </div>

      </div>
    </div>

  </div>

</template>

<script>
    export default {

      props: [
        'csrfToken',
        'unverifiedTransactions',
        'verificationRouteName',
        'vendorTaggingRouteName',
        'getVerifiedVendorsRouteName',
        'ignoreTransactionsRouteName',
      ],

      data() {
        return {
          mutableUnverifiedTransactions: this.unverifiedTransactions,
          pageMessages: {
            vendorVerificationForm: {
              msg: '',
              msgTable: null,
              class: ''
            },
          },
          activeRow: {},
          transactionList: {
            visibleFrom: 0,
            rowsVisible: 9,
            curHighlight: 0
          },
          verificationForm: {
            inputValue: ''
          },
          verifiedVendorList: {
            showCount: 5,
            data: null
          }
        }
      },

      methods: {

        // TODO: Need to revision tool to handle 'Uncategorized' transactions

        moveListRow(dir, rows) {
          var vm = this;
          var list = vm.transactionList;

          if(dir === 'indexUp') {
            var nextStep = list.curHighlight + rows;

            // if 'index' step up is higher than the list set 'from' and 'index' to 0
            if(nextStep > vm.mutableUnverifiedTransactions.length - rows) {
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
              list.visibleFrom = vm.mutableUnverifiedTransactions.length - (list.rowsVisible + rows);
              list.curHighlight = vm.mutableUnverifiedTransactions.length - rows;
            } else {
              if(prevStep < list.visibleFrom) {
                list.visibleFrom -= (list.rowsVisible + 1);
              }
              list.curHighlight -= rows;
            }

          }

          vm.activeRow = vm.mutableUnverifiedTransactions[list.curHighlight];
          vm.verificationForm.inputValue = vm.activeRow['category_vendor_name'];

        },

        updateRowOnClick(data, index) {
          var vm = this;
          var formValue;

          vm.activeRow = data;
          vm.verificationForm.inputValue = vm.activeRow.category_vendor_name;
          vm.transactionList.curHighlight = index;
        },

        // Ajax call
        verifyVendor(type) {
          var vm = this;

          // Clear table data in case there's any
          vm.pageMessages.vendorVerificationForm.msgTable = null;

          $.ajax({
            method: "POST",
            url: vm.verificationRouteName,
            data: {
              _token: vm.csrfToken,
              verificationType: type,
              transactionId: vm.activeRow.transaction_table_id,
              assignedVendorName: vm.verificationForm.inputValue
            }
          })
           .done(function(response) {

             if(!response.success) {
                vm.pageMessages.vendorVerificationForm.msg = response.msg;
                vm.pageMessages.vendorVerificationForm.msgTable = response.data;
                vm.pageMessages.vendorVerificationForm.class = 'alert-warning';
                return;
             }

             // 1) Remove the row from the table
             vm.mutableUnverifiedTransactions.splice(vm.transactionList.curHighlight,1);
             // 2) Set the activeRow data from the new highlighted row
             vm.activeRow = vm.mutableUnverifiedTransactions[vm.transactionList.curHighlight];
             // 3) Set the new default input value from the highlighted vendor
             vm.verificationForm.inputValue = vm.activeRow.category_vendor_name;
             vm.pageMessages.vendorVerificationForm.msg = response.msg;
             vm.pageMessages.vendorVerificationForm.class = 'alert-success';
           })
           .fail(function(response) {
             vm.pageMessages.vendorVerificationForm = 'Ajax call failed.';
             vm.pageMessages.vendorVerificationForm.class = 'alert-warning';
           })
           .then(function(response) {
             vm.getVerifiedVendors(vm.verifiedVendorList.showCount);
           });

        },

        // Ajax call
        getVerifiedVendors(count) {
          var vm = this;

          $.ajax({
            method: "POST",
            url: vm.getVerifiedVendorsRouteName,
            data: {
              _token: vm.csrfToken,
              count: count
            }
          })
           .done(function(response) {
             vm.verifiedVendorList.data = response.data;
           })
           .fail(function(response) {
             console.log(response);
           });
        },

        setTransactionsIgnored(transactionId) {
          var vm = this;

          $.ajax({
            method: "POST",
            url: vm.ignoreTransactionsRouteName,
            data: {
              _token: vm.csrfToken,
              transactionId: transactionId
            }
          })
           .done(function(response) {
             if(!response.success) {
                vm.pageMessages.vendorVerificationForm.msg = response.msg;
                vm.pageMessages.vendorVerificationForm.msgTable = response.data;
                vm.pageMessages.vendorVerificationForm.class = 'alert-warning';
                return;
             }

             // 1) Remove the row from the table
             vm.mutableUnverifiedTransactions.splice(vm.transactionList.curHighlight,1);
             // 2) Set the activeRow data from the new highlighted row
             vm.activeRow = vm.mutableUnverifiedTransactions[vm.transactionList.curHighlight];
             // 3) Set the new default input value from the highlighted vendor
             vm.verificationForm.inputValue = vm.activeRow.category_vendor_name;
             vm.pageMessages.vendorVerificationForm.msg = response.msg;
             vm.pageMessages.vendorVerificationForm.class = 'alert-success';
           })
           .fail(function(response) {
             console.log(response);
           });
        },

        _addWordToAssignedNameField(word) {
          var vm = this;

          if(vm.verificationForm.inputValue == '') {
            vm.verificationForm.inputValue += word;
          } else {
            vm.verificationForm.inputValue += '-' + word;
          }
        },

        formatAssignedName() {
          var vm = this;

          vm.verificationForm.inputValue = vm.verificationForm.inputValue.replace(/\s+/g, '-').toLowerCase();
        }

      },

      mounted() {
        var vm = this;

        vm.getVerifiedVendors(vm.verifiedVendorList.showCount);

        vm.activeRow = vm.mutableUnverifiedTransactions[vm.transactionList.curHighlight];
        vm.verificationForm.inputValue = vm.activeRow.category_vendor_name;
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
