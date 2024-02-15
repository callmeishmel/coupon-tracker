<template lang="html">

  <div>

    <div class="row">
      <div
        class="col-md-4"
        v-if="currentRow !== null">
        <div class="panel panel-default">

          <div class="panel-heading">
            <h4 class="wm-no-margin wm-bold">Tag Vendor</h4>
          </div>

          <div class="panel-body" style="min-height: 216px;">

            <div
              v-if="currentRow">
              <h3 class="wm-no-margin wm-bold">
                {{ currentRow.assigned_name }}
                <span
                  style="font-size: 14px; color: #636b6f;">
                  {{ currentRow.category_name_from_transaction }}
                  <a
                    :href="'http://www.google.com/search?q=' + currentRow.name_assigned + '+' + currentRow.category_name_from_transaction"
                    target="_blank">
                    <i
                      style="color: #3097D1; cursor: pointer;"
                      class="glyphicon glyphicon-search"></i>
                  </a>
                </span>
                <span
                  class="btn btn-success pull-right"
                  v-show="currentVendorTags !== null"
                  @click="markVendorTagged">
                  Mark Vendor Tagged
                </span>
              </h3>
            </div>

            <hr />

            <div
              v-if="currentVendorTags !== null">
              <span
                class="btn btn-warning"
                style="margin: 0 10px 10px 0; font-size: 20px; font-weight: bold;"
                v-for="tag in currentVendorTags"
                @click="removeTagFromVendor(tag.id)">
                  {{ tag.tag_name }}
                  <i style="font-size: 17px;" class="glyphicon glyphicon-remove-circle"></i>
              </span>
            </div>
            <div
              v-else class="text-center">
              <span v-if="mutableTableRowsData.length > 0" class="box-content-msg">Vendor Has Not Been Tagged</span>
              <span v-else class="box-content-msg">No Untagged Vendors Found</span>
            </div>

          </div>

          <div class="panel-footer">
            <div class="row">
              <div
                class="col-md-12"
                style="overflow: hidden;"
                >
                <div class="form-group">
                  <input
                    class="form-control"
                    type="text"
                    autocomplete="off"
                    placeholder="Search tag terms"
                    v-model="tagSearchTerm"
                    @keyup="searchForTagTerm"
                    @change="searchForTagTerm"
                  />
                </div>

                <div>
                  <button
                    class="btn btn-default"
                    style="margin: 10px 10px 0 0;"
                    v-for="tagFound in tagsFound"
                    @click="addTagToVendor(tagFound.id)"
                  >
                    {{ tagFound.tag_name }}
                  </button>
                </div>
              </div>
            </div>

          </div>

        </div>

      </div>

      <div class="col-md-4">
        <div class="panel panel-default">

          <div class="panel-heading">
            <h4 class="wm-no-margin wm-bold">Recently Tagged Vendors</h4>
          </div>

          <div
            class="panel-body" style="min-height: 286px;">

            <table
              class="table table-hover"
              v-if="taggedVendorsList && taggedVendorsList.length > 0">
              <thead>
                <tr>
                  <th>Name Assigned</th>
                  <th>Category</th>
                  <th>Tagged By</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="taggedVendor in taggedVendorsList">
                  <td>{{ taggedVendor.assigned_name }}</td>
                  <td>{{ taggedVendor.category_name_from_transaction }}</td>
                  <td><a :href="'mailto:' + taggedVendor.user_email">{{ taggedVendor.user_email }}</a></td>
                </tr>
              </tbody>
            </table>
            <div
              class="text-center"
              v-else>
              <span class="box-content-msg">No Tagged Vendors Found</span>
            </div>

          </div>

        </div>
      </div>

      <div class="col-md-4">

        <div class="panel panel-default">

          <div class="panel-heading">
            <h4 class="wm-no-margin wm-bold">Add New Tag</h4>
          </div>

          <div class="panel-body" style="min-height: 286px;">

            <div
              class="row"
              style="border-bottom: 1px solid #d3e0e9; padding-bottom: 15px;">
              <div class="col-md-8" style="padding-right: 0;">
                <input
                  class="form-control"
                  style="width: 100%;"
                  type="text"
                  autocomplete="off"
                  placeholder="New tag"
                  v-model="newTagForm.input"
                  @keyup="formatNewTagInputValue"
                  @change="formatNewTagInputValue"
                />
              </div>
              <div class="col-md-4">
                <button
                  class="btn btn-primary"
                  style="width: 100%;"
                  @click="addNewTag">Add Tag</button>
              </div>
            </div>

            <div
              class="row"
              style="margin-top: 15px;"
              v-if="latestTags !== null">
              <div class="col-md-12">

                <div
                  class="label label-default pull-left"
                  style="margin: 10px 5px 0 0; font-size: 17px;"
                  v-for="latestTag in latestTags">
                  {{ latestTag['tag_name'] }}
                </div>

              </div>
            </div>

          </div>

        </div>

      </div>

    </div>

    <load-table
      :table-headers="{
        assigned_name: 'Assigned Name',
        name_from_transaction: 'Transaction Name',
        category_name_from_transaction: 'Transaction Category'
      }"
      :table-rows="untaggedVendors">
    </load-table>

  </div>

</template>

<script>

import {mapState, mapMutations, mapActions, mapGetters} from 'vuex';

Vue.component('loadTable', require('../dynamicAssets/dynamicTable/table.vue'));

export default {
  props: [
    'csrfToken',
    'untaggedVendors',
    'tagSearchRouteName',
    'getVendorTagsRouteName',
    'getLatestTagsRouteName',
    'addNewTagRouteName',
    'addTagToVendorRouteName',
    'getTaggedVendorsRouteName',
    'removeTagFromVendorRouteName',
    'markVendorTaggedRouteName',
  ],

  data() {
    return {
      tagSearchTerm: null,
      tagsFound: null,
      latestTags: null,
      currentVendorTags: null,
      taggedVendorsList: null,
      newTagForm: {
        input: null
      }
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
      'paginatedTableComponentStore',['updateCurrentRow']
    ),

    // Local Methods
    tagVendor() {
      var vm = this;

      console.log(vm.tagSearchTerm);
    },

    searchForTagTerm() {
      var vm = this;

      if(/\S/.test(vm.tagSearchTerm)) {
        $.ajax({
          method: "GET",
          url: vm.tagSearchRouteName + '/' + vm.tagSearchTerm,
          headers: {
            'X-CSRF-TOKEN': vm.csrfToken
          }
        })
         .done(function(response) {

           if(response.success) {
              vm.tagsFound = response.data;
           }
         })
         .fail(function(response) {
           console.log(response.msg);
         });
      } else {
        vm.tagsFound = null;
      }

    },

    getVendorTags() {
      var vm = this;

      $.ajax({
        method: "GET",
        url: vm.getVendorTagsRouteName + '/' + vm.currentRow.vendor_assigned_name_id,
        headers: {
          'X-CSRF-TOKEN': vm.csrfToken
        }
      })
      .done(function(response) {
        if(response.success) {
          if(Object.keys(response.data).length > 0) {
            vm.currentVendorTags = response.data;
          } else {
            vm.currentVendorTags = null;
          }
        }
      })
      .fail(function(response) {
        console.log(response);
      });
    },

    addTagToVendor(tagId) {
      var vm = this;

      $.ajax({
        method: "POST",
        url: vm.addTagToVendorRouteName,
        headers: {
          'X-CSRF-TOKEN': vm.csrfToken
        },
        data: {
          tag_id: tagId,
          vendor_name_id: vm.currentRow.vendor_assigned_name_id
        }
      })
       .done(function(response) {
         if(response.success) {
            console.log(response);
         }
       })
       .fail(function(response) {
         console.log(response.msg);
       })
       .then(function() {
         vm.getVendorTags();
         vm.tagSearchTerm = null;
         vm.tagsFound = null;
       });
    },

    removeTagFromVendor(tagId) {
      var vm = this;

      $.ajax({
        method: "POST",
        url: vm.removeTagFromVendorRouteName,
        headers: {
          'X-CSRF-TOKEN': vm.csrfToken
        },
        data: {
          tag_id: tagId,
          vendor_name_id: vm.currentRow.vendor_assigned_name_id
        }
      })
       .done(function(response) {
         if(response.success) {
            console.log(response);
         }
       })
       .fail(function(response) {
         console.log(response.msg);
       })
       .then(function() {
         vm.getVendorTags();
       });
    },

    markVendorTagged() {
      var vm = this;

      vm.currentVendorTags = null;

      $.ajax({
        method: "POST",
        url: vm.markVendorTaggedRouteName,
        headers: {
          'X-CSRF-TOKEN': vm.csrfToken
        },
        data: {
          vendor_id: vm.currentRow.vendor_id
        }
      })
       .done(function(response) {
         if(response.success) {
           vm.mutableTableRowsData.splice(vm.tableVisibleRows.curHighlight,1);
           vm.updateCurrentRow(vm.mutableTableRowsData[vm.tableVisibleRows.curHighlight]);
           console.log(response);
         }
       })
       .fail(function(response) {
         console.log(response.msg);
       })
       .then(function() {
         vm.getTaggedVendors();
       });
    },

    getTaggedVendors() {
      var vm = this;

      $.ajax({
        method: "GET",
        url: vm.getTaggedVendorsRouteName,
        headers: {
          'X-CSRF-TOKEN': vm.csrfToken
        }
      }).done(function(response) {
        if(response.success) {
          vm.taggedVendorsList = response.data;
          console.log(response);
        }
      }).fail(function(response) {
        console.log(response);
      }
      );

    },

    addNewTag() {
      var vm = this;

      if(vm.newTagForm.input === null || !/\S/.test(vm.newTagForm.input)) {
        return;
      }

      $.ajax({
        method: "POST",
        url: vm.addNewTagRouteName,
        headers: {
          'X-CSRF-TOKEN': vm.csrfToken
        },
        data: {
          tag_name: vm.newTagForm.input
        }
      })
      .done(function(response) {
        if(response.success) {
          console.log(response);
        }
      })
      .fail(function(response) {
        console.log(response.msg);
      })
      .then(function() {
        vm.newTagForm.input = null;
        vm.getLatestTags();
      });

    },

    getLatestTags() {
      var vm = this;

      $.ajax({
        method: "GET",
        url: vm.getLatestTagsRouteName,
        headers: {
          'X-CSRF-TOKEN': vm.csrfToken
        }
      })
      .done(function(response) {
        if(response.success) {
          vm.latestTags = response.data;
          console.log(response);
        }
      })
      .fail(function(response) {
        console.log(response.msg);
      });
    },

    formatNewTagInputValue() {
      var vm = this;

      vm.newTagForm.input = vm.newTagForm.input.replace(/\s+/g, '-').toLowerCase();
    }

  },

  watch: {
    currentRow: function(val, oldVal) {
      var vm = this;

      if(typeof vm.currentRow !== 'undefined') {
        this.getVendorTags(val);
      }
    },
  },

  mounted() {
    var vm = this;

    vm.getTaggedVendors();
    vm.getLatestTags();

  }
}
</script>

<style lang="css">

</style>
