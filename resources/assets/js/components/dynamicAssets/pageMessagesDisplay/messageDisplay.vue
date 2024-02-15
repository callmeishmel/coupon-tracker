<template lang="html">

  <div>

    <div
      v-show="messageDisplay.visible"
      class="row">
      <div class="pg-msgs-wrapper-div">
        <span
          :class="messageDisplay.class"
          class="alert pg-msgs-wrapper-span"
        >
          {{ messageDisplay.msg }}
          <span style="float: right;">
            <i
              @click="messageDisplay.visible = false"
              class="close-btn glyphicon glyphicon-remove-circle"></i>
          </span>
        </span>
      </div>
    </div>

  </div>

</template>

<script>

export default {

  props: ['messageData'],


  // messageData requires the following data node structure
  // pageMessages: {
  //   visible: false (bool),
  //   class: null (string),
  //   msg: null (string)
  // }
  // NOTE: Data updated by parent component

  data() {
    return {
      messageDisplay: this.messageData
    }
  }

}
</script>

<style lang="css">

.close-btn {
  font-size: 20px;
  vertical-align: middle;
  cursor: pointer;
}

.pg-msgs-wrapper-div {
  text-align: center;
  font-size: 16px;
  font-weight: bold;
  border-bottom: 1px solid rgb(211, 224, 233);
  float: left;
  width: 100%;
}

.pg-msgs-wrapper-span {
  width: 100%;
  float: left;
  margin-bottom: 15px;
  border-radius: 0;
}

</style>
