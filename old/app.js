const CancelToken = axios.CancelToken;

Vue.use(infiniteScroll);

new Vue({
  el: "#app",
  data: function() {
    return {
      busy: false,
      page: 0,
      lastPage: false,
      orders: [],
      order: null,
      stores: [],
      activeStore: null,
      storeCancel: CancelToken.source()
    };
  },
  computed: {
    ordersByDate: function() {
      return this.orders.reduce(
        (
          r,
          v,
          i,
          a,
          k = moment
            .unix(v.delivery_date)
            .utc()
            .format("LL")
        ) => ((r[k] || (r[k] = [])).push(v), r),
        {}
      );
    }
  },
  methods: {
    loadStores: function() {
      this.busy = true;
      axios.get("/api.php?path=stores").then(response => {
        this.busy = false;
        this.stores = response.data;
        if (this.stores.length > 0) this.activeStore = this.stores[0];
      });
    },
    loadPage: function() {
      if (this.busy || this.lastPage || !this.activeStore) return;

      this.busy = true;
      axios
        .get(
          `/api.php?path=orders&store=${this.activeStore.code}&page=${this
            .page + 1}`,
          {
            cancelToken: this.storeCancel.token
          }
        )
        .then(response => {
          if (response.data.length == 0) {
            this.lastPage = true;
            return;
          }
          this.orders = [...this.orders, ...response.data];
          this.page += 1;
          this.busy = false;
        })
        .catch(e => {
          if (axios.isCancel(e)) {
            console.log("Request canceled:", e.message);
          } else {
            console.error(e);
          }
        });
    },
    loadOrder: function(order) {
      this.order = order;
    },
    reset: function() {
      this.storeCancel.cancel("Changed store");

      this.busy = false;
      this.page = 0;
      this.lastPage = false;
      this.orders = [];
      this.order = null;
      this.storeCancel = CancelToken.source();
    }
  },
  watch: {
    activeStore: function() {
      this.reset();
      this.loadPage();
    }
  },
  mounted: function() {
    this.loadStores();
  },
  filters: {
    hour: function(value) {
      return moment
        .unix(value)
        .utc()
        .format("H:mm");
    },
    money: function(value) {
      return `€${(+value).toFixed(2)}`;
    }
  }
});
