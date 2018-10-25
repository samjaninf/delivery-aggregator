<template>
  <div>
    <nav class="navbar navbar-expand-md navbar-dark bg-dark">
      <div class="container">
        <a class="navbar-brand" href="#">Delivery Aggregator</a>
        <button class="navbar-toggler" type="button" data-toggle="collapse">
          <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse">
          <ul class="navbar-nav mr-auto">
            <li class="nav-item" v-for="store in stores" :key="store.code" :class="{ active: $route.params.storeCode == store.code }">
              <router-link :to="store.code" class="nav-link">
                {{ store.name }}
              </router-link>
            </li>
          </ul>
          <ul class="navbar-nav">
            <li class="nav-item" :class="{ active: $route.path == '/settings' }">
              <router-link to="/settings" class="nav-link">
                <i class="fas fa-fw fa-cog"></i> Settings
              </router-link>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <router-view :stores="stores"></router-view>
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
      axios.get("/api/stores").then(response => {
        this.stores = response.data;
      });
    },
  },
  mounted: function() {
    this.loadStores();
  },
  components: {
    OrdersList: require('./OrdersList.vue'),
  }
}
</script>
