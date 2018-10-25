<template>
  <main role="main" class="container mt-4" v-if="activeStore">
    <h1 class="text-center mt-4 mb-4">Ordini {{ activeStore.name }}</h1>
    <div>
      <div v-for="(dayOrders, day) in ordersByDate" class="mb-3" :key="day">
        <h3 class="text-primary">{{ day }}</h3>
        <hr />
        <div class="orders row">
          <div class="col-lg-4 col-md-6" v-for="order in dayOrders" :key="order.number">
            <order :order="order" @click="$emit('click', order)"></order>
          </div>
        </div>
      </div>
      <infinite-loading @infinite="loadPage" :identifier="activeStore.code"></infinite-loading>
    </div>
  </main>
</template>

<script>
const CancelToken = axios.CancelToken;

export default {
  data() {
    return {
      orders: [],
      page: 0,
      storeCancel: CancelToken.source()
    };
  },
  props: ["activeStore"],
  computed: {
    ordersByDate() {
      return _.groupBy(this.orders, o =>
        moment
          .unix(o.delivery_date)
          .utc()
          .format("LL")
      );
    }
  },
  methods: {
    loadPage($state) {
      axios
        .get(
          `/api/stores/${this.activeStore.code}/orders?page=${this.page + 1}`,
          {
            cancelToken: this.storeCancel.token
          }
        )
        .then(response => {
          if (response.data.length == 0) {
            $state.complete();
            return;
          }

          this.page += 1;
          this.orders = [...this.orders, ...response.data];
          $state.loaded();
        })
        .catch(e => {
          if (axios.isCancel(e)) {
            console.log("Request canceled:", e.message);
          } else {
            console.error(e);
          }
        });
    },
    reset() {
      this.storeCancel.cancel("Changed store");

      this.page = 0;
      this.orders = [];
      this.storeCancel = CancelToken.source();
    }
  },
  watch: {
    activeStore() {
      this.reset();
    }
  },
  components: {
    Order: require('./Order.vue')
  }
};

</script>
