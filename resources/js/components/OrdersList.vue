<template>
  <main role="main" class="container mt-4" v-if="activeStore">
    <h1 class="text-center mt-4 mb-4">Ordini — {{ activeStore.name }}</h1>
    <div>
      <div v-for="(dayOrders, day) in ordersByDate" class="mb-3" :key="day">
        <h3 class="text-primary">{{ day }}</h3>
        <hr />
        <div class="orders row">
          <div class="col-lg-4 col-md-6" v-for="order in dayOrders" :key="order.number">
            <order :order="order" @click="selectedOrder = order"></order>
          </div>
        </div>
      </div>
      <infinite-loading @infinite="loadNextPage" :identifier="activeStore.code"></infinite-loading>
      <div class="backdrop" v-if="selectedOrder" @click.self="selectedOrder = null">
        <order :order="selectedOrder" :detailed="true" @close="selectedOrder = null"></order>
      </div>
    </div>
  </main>
</template>

<script>
const CancelToken = axios.CancelToken;
const UPDATE_INTERVAL = 5 * 1000;

export default {
  data() {
    return {
      orders: [],
      page: 0,
      storeCancel: CancelToken.source(),
      selectedOrder: null,
      intervalHandle: null,
    };
  },
  props: ['storeCode', 'stores'],
  computed: {
    ordersByDate() {
      return _.groupBy(this.orders, o =>
        moment
          .unix(o.delivery_date)
          .utc()
          .format("LL")
      );
    },
    activeStore() {
      return _.find(this.stores, { code: this.storeCode });
    }
  },
  methods: {
    loadPage(page = 0, $state) {
      return this.$http
        .get(
          `stores/${this.activeStore.code}/orders?page=${page + 1}`,
          {
            cancelToken: this.storeCancel.token
          }
        )
        .then(response => {
          if (response.data.length == 0) {
            if ($state)
              $state.complete();
            return;
          }
          
          // Merge arrays
          for(let newOrder of response.data) {
            const old = _.find(this.orders, { 'number': newOrder.number });
            if (old) {
              Object.assign(old, newOrder);
            } else {
              this.orders.push(newOrder);
            }
          }

          if($state)
            $state.loaded();
        })
        .catch(e => {
          if (this.$http.isCancel(e)) {
            console.log("Request canceled:", e.message);
          } else {
            console.error(e);
          }
        });
    },
    loadNextPage($state) {
      this.loadPage(this.page, $state)
        .then(() => this.page += 1);
    },
    reset() {
      this.storeCancel.cancel("Changed store");

      this.page = 0;
      this.orders = [];
      this.selectedOrder = null;
      this.storeCancel = CancelToken.source();
    }
  },
  watch: {
    activeStore() {
      this.reset();
    }
  },
  mounted() {
    this.intervalHandle = setInterval(this.loadPage, UPDATE_INTERVAL);
  },
  beforeDestroy() {
    this.clearInterval(this.intervalHandle);
  },
  components: {
    Order: require('./Order.vue'),
  }
};

</script>

<style>
.text-primary + hr {
  border-color: var(--blue);
}

.orders .order.card {
  transition: all 0.3s ease;
  cursor: pointer;
}

.orders .order.card:hover {
  transform: translateY(-2px);
  box-shadow: #eeeeee 0px 3px 10px 2px;
}

.backdrop {
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.7);
  position: fixed;
  top: 0;
  left: 0;
  display: flex;
  align-items: center;
  justify-content: center;
}
</style>
