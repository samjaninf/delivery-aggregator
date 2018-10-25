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
            <li class="nav-item" v-for="store in stores" :class="{ active: activeStore.code === store.code }" :key="store.code">
              <a class="nav-link" @click="activeStore = store">
                {{ store.name }}
              </a>
            </li>
          </ul>
        </div>
      </div>
    </nav>
    <orders-list :activeStore="activeStore" @click="loadOrder"></orders-list>
    <div class="backdrop" v-if="order" @click.self="order = null">
      <order :order="order" :detailed="true"></order>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      order: null,
      stores: [],
      activeStore: null,
    };
  },
  methods: {
    loadStores: function() {
      axios.get("/api/stores").then(response => {
        this.stores = response.data;
        if (this.stores.length > 0) this.activeStore = this.stores[0];
      });
    },
    loadOrder: function(order) {
      this.order = order;
    },
  },
  mounted: function() {
    this.loadStores();
  },
  components: {
    OrdersList: require('./OrdersList.vue'),
    Order: require('./Order.vue')
  }
}
</script>
