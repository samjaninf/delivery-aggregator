<template>
  <div>
    <template v-if="$auth.ready()">
      <layout :stores="stores">
        <router-view :stores="stores"></router-view>
      </layout>
    </template>
  </div>
</template>

<script>
export default {
  data() {
    return {
      stores: []
    };
  },
  methods: {
    loadStores: function() {
      this.$http.get("stores").then(response => {
        this.stores = response.data;

        if (this.stores.length > 0 && this.$route.path === "/") {
          const firstStore = this.stores[0];
          this.$router.replace(`/${firstStore.code}`);
        }
      });
    }
  },
  mounted: function() {
    this.loadStores();
  },
  components: {
    OrdersList: require("./OrdersList.vue").default,
    Layout: require("./Layout.vue").default
  }
};
</script>

<style scoped>
div {
  height: 100%;
  width: 100%;
}
</style>
