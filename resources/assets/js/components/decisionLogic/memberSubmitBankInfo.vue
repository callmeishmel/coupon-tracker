<template lang="html">

  <div>

    <page-messages-display :message-data="pageMessages"></page-messages-display>

    <br v-show="pageMessages.visible" />

    <div class="row">

      <div class="col-md-4">

        <h4>Add New Bank Account</h4>
        <hr>

        <div v-if="processingDLCreateRequest4Call">
          <load-spinner :on-display-msg="'Creating Request'"></load-spinner>
        </div>
        <div v-else>
          <form @submit.prevent="ajaxDLCreateRequest4">
            <div class="form-group">
              <label for="acctNum">Account Number*</label>
              <input
                class="form-control"
                type="text"
                name="acctNum"
                autocomplete="off"
                v-model="bankAccountInfoForm.accountNumber"
                required
              >
            </div>

            <div
              class="form-group"
            >
              <label for="routNum">Route Number*</label>
              <input
                class="form-control"
                type="text"
                name="routNum"
                value=""
                autocomplete="off"
                v-model="bankAccountInfoForm.routeNumber"
                required
              >
            </div>

            <button class="btn btn-primary">Submit Bank Information</button>
          </form>

        </div>

      </div>

      <div class="col-md-8">
        <h4>Your Accounts</h4>
        <hr>
        <div>
          <div
            v-if="(userDlRequestData && userDlRequestData.length < 1) || userDlRequestData === null"
            class="box-content-msg"
            style="text-align:center;">
            <span>No Accounts Submitted</span>
          </div>

          <span v-else>
            <table class="table table-hover">
              <thead>
                <tr>
                  <th>Code</th>
                  <th>Request Status</th>
                  <th>Customer Name</th>
                  <th>Institution</th>
                  <th>Route Number</th>
                  <th>Account Number</th>
                  <th>Created At</th>
                  <th>Action</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="request in userDlRequestData">
                  <td>{{ request.dl_request_code }}</td>
                  <td
                    class="request-status-cell"
                    :style="'background:'+request.request_status_color+';'">
                    {{ request.request_status_text }}
                  </td>
                  <td>{{ request.name_found }}</td>
                  <td>{{ request.institution }}</td>
                  <td>{{ request.routing_number }}</td>
                  <td>{{ request.account_number }}</td>
                  <td>{{ request.dl_date_created }}</td>
                  <td>
                    <div v-show="request.request_status == 0">
                      <button
                        class="btn btn-primary"
                        v-if="request.dl_request_code !== verifyAccountIframe.requestCodeStr"
                        @click="displayVerifyAccountPane(request.id, request.dl_request_code, request.user_bank_account_id,)"
                      >
                        Verify Account
                      </button>
                      <span class="label label-danger" v-else>Active</span>
                    </div>
                    <div v-show="request.request_status == 3">

                      <a :href="dlViewRequestTransactionsRoute + '/' + request.id" class="btn btn-success">View</a>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </span>
        </div>
      </div>

    </div>

    <br/>

    <div class="row" v-if="verifyAccountIframe.visible">
      <div class="col-md-12">
        <h3>Verify Banking Information for Request
          <span style="font-weight: bold;">{{ verifyAccountIframe.requestCodeStr }}</span>
          <i
            @click="closeVerifyAccountIframe"
            style="color: #bf5329; vertical-align: inherit;"
            class="close-btn glyphicon glyphicon-remove-circle"></i>
        </h3>
        <hr>
        <div>
          <iframe id="IFrameHolder" frameborder="0" height="450" width="900"
            :src="'https://widget.decisionlogic.com/Service.aspx?requestCode=' + verifyAccountIframe.requestCodeStr">
          </iframe>
        </div>
      </div>
      <div class="col-md-12">
        {{ verifyAccountIframe.listenerOutput }}
      </div>
    </div>

  </div>

</template>

<script>

Vue.component('loadSpinner', require('../dynamicAssets/loadSpinner/spinner.vue'));
Vue.component('pageMessagesDisplay', require('../dynamicAssets/pageMessagesDisplay/messageDisplay.vue'));

export default {

  props: [
    'userId',
    'userEmail',
    'csrfToken',
    'dlCreateRequestRoute',
    'dlGetUserRequestsRoute',
    'dlSaveIframeBankDataRoute',
    'dlSaveRequestTransactionsRoute',
    'dlViewRequestTransactionsRoute',
  ],

  data() {
    return {
      verifyAccountIframe: {
        visible: false,
        requestId: null,
        requestCodeStr: null,
        requestBankAccountId: null,
        listenerOutput: null
      },

      userDlRequestData: null,
      bankAccountInfoForm: {
        accountNumber: 1212,
        routeNumber: 999999963
      },
      pageMessages: {
        visible: false,
        class: null,
        msg: null
      },
      processingDLCreateRequest4Call: false,
    }
  },

  methods: {

    ajaxDLCreateRequest4() {
      var vm = this;
      vm.processingDLCreateRequest4Call = true;

      // TODO Need to add form data validation

      $.ajax({
        method: "POST",
        url: vm.dlCreateRequestRoute,
        data: {
          _token: vm.csrfToken,
          userData: {
            userId: vm.userId,
            userEmail: vm.userEmail,
            bankAccountNumber: vm.bankAccountInfoForm.accountNumber,
            bankRoutingNumber: vm.bankAccountInfoForm.routeNumber
          }
        }
      })
       .done(function(response) {

         vm.pageMessages.visible = true;
         vm.pageMessages.msg = response.msg;

         if(response.success) {
           vm.pageMessages.class = 'alert-success';
           vm.bankAccountInfoForm.accountNumber = null;
           vm.bankAccountInfoForm.routeNumber = null;
         } else {
           vm.pageMessages.class = 'alert-danger';
         }

       })
       .fail(function(response) {
         vm.pageMessages.msg = 'Asynchronous call failed';
         vm.pageMessages.class = 'btn-danger';
       })
       .then(function(response) {
         vm.getUserDLRequests();

         vm.processingDLCreateRequest4Call = false;
       });

    },

    // Get the user's Decision Logic reports
    getUserDLRequests() {
      var vm = this;

      $.ajax({
        method: "GET",
        url: vm.dlGetUserRequestsRoute + '/' + vm.userId,
        data: {
          _token: vm.csrfToken
        }
      })
      .done(function(response) {
        if(response.success) {
          vm.userDlRequestData = response.data;
        }
      })
      .fail(function(response) {
        console.log(response);
      });
    },

    displayVerifyAccountPane(requestId, requestCode, bankAccountId) {
      var vm = this;

      // Reset page message
      vm.pageMessages.msg = null;
      vm.pageMessages.class = null;
      vm.pageMessages.visible = false;

      // Set bank account information for the current verfication process
      vm.verifyAccountIframe.visible = true;
      vm.verifyAccountIframe.requestId = requestId;
      vm.verifyAccountIframe.requestCodeStr = requestCode;
      vm.verifyAccountIframe.requestBankAccountId = bankAccountId;

    },

    closeVerifyAccountIframe() {
      var vm = this;

      vm.verifyAccountIframe.visible = false;
      vm.verifyAccountIframe.requestId = null;
      vm.verifyAccountIframe.requestCodeStr = null;
      vm.verifyAccountIframe.requestBankAccountId = null;
    },

    saveIframeData(data) {
      var vm = this;

      $.ajax({
        method: "POST",
        url: vm.dlSaveIframeBankDataRoute,
        data: {
          _token: vm.csrfToken,
          userId: vm.userId,
          userEmail: vm.userEmail,
          requestId: vm.verifyAccountIframe.requestId,
          requestCode: vm.verifyAccountIframe.requestCodeStr,
          requestBankAccountId: vm.verifyAccountIframe.requestBankAccountId,
          bankData: data
        }
      })
      .done(function(response) {
        vm.pageMessages.visible = true;
        vm.pageMessages.msg = response.msg;
        vm.getUserDLRequests();

        if(response.success) {
          vm.pageMessages.class = 'alert-success';
        } else {
          vm.pageMessages.class = 'alert-danger';
        }
      })
      .fail(function(response) {
        console.log('saveIframeData ajax call failed.');
        console.log(response);
      })
      .then(function() {
        vm.fetchAndSaveIframeRequestTransactions();
        vm.closeVerifyAccountIframe();
      });

    },

    fetchAndSaveIframeRequestTransactions() {
      var vm = this;

      $.ajax({
        method: "GET",
        url: vm.dlSaveRequestTransactionsRoute + '/' + vm.verifyAccountIframe.requestCodeStr,
        headers: {
          'X-CSRF-TOKEN': vm.csrfToken
        }
      })
      .done(function(response) {
        console.log('fetchAndSaveIframeRequestTransactions ajax call succeeded.');
        console.log(response);
      })
      .fail(function(response) {
        console.log('saveIframeData ajax call failed.');
        console.log(response);
      });
    },

    // Decision Logic iframe listener functions
    processMessage(s) {
      var vm = this;

      var p = s.split("|");
      if (p.length == 2) {
        if (p[0] == "Redirect") {
          document.location.href = p[1];
        }
        if (p[0] == "JSON") {
          vm.processJSON(p[1]);
        }
      }
    },

    // Unused DL function left in for documentation purposes
    toArray(obj) {
      var vm = this;

      var result = [];
      for (var prop in obj) {
        var value = obj[prop];
        if (typeof value === 'object') {
          result.push(toArray(value));
        } else {
          result.push(value);
        }
      }
      return result;
    },

    processJSON(jsonString) {
      var vm = this;

      var json = unescape(jsonString);
      var obj = JSON.parse(json);
      vm.saveIframeData(obj);
    },

  },

  mounted() {
    var vm = this;

    // Decision Logic iframe listener
    var eventMethod = window.addEventListener ? "addEventListener" : "attachEvent";
    var eventer = window[eventMethod];
    var messageEvent = eventMethod == "attachEvent" ? "onmessage" : "message";

    eventer(messageEvent, function(e) {
      if (e.origin == 'https://widget.decisionlogic.com') {
        vm.processMessage(e.data);
      }
    }, false);

    // Get the customer's already submitted Decision Logic requests
    this.getUserDLRequests();

  }

}
</script>

<style lang="css">

  .request-status-cell {
     color: #fff;
     font-weight: bold;
     text-shadow: 0px 1px 3px rgba(0,0,0,.4);
  }

</style>
