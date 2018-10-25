<template>
  <div>
    <template v-if="$auth.ready()">
      <navbar :stores="stores"></navbar>
      <router-view :stores="stores"></router-view>
    </template>
  </div>
</template>

<script>
export default {
  data() {
    return {
      stores: [],
    };
  },
  methods: {
    loadStores: function() {
      this.$http.get("stores").then(response => {
        this.stores = response.data;

        if (this.stores.length > 0) {
          const firstStore = this.stores[0];
          this.$router.replace(`/${firstStore.code}`);
        }
      });
    },
  },
  mounted: function() {
    this.loadStores();
  },
  components: {
    OrdersList: require('./OrdersList.vue'),
    Navbar: require('./Navbar.vue'),
  }
}
</script>

<style scoped>
div {
  height: 100%;
  width: 100%;
}
</style>
