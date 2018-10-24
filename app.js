const stores = [
  { name: "Imburger", code: "imburger" },
  { name: "Da Scomposto", code: "dascomposto" }
];

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
      stores,
      activeStore: stores[0]
    };
  },
  methods: {
    loadPage: function() {
      if (this.busy || this.lastPage) return;

      this.busy = true;
      axios
        .get(
          `/api.php?store=${this.activeStore.code}&path=orders&page=${this
            .page + 1}`
        )
        .then(response => {
          if (response.data.length == 0) {
            this.lastPage = true;
            return;
          }
          this.orders = [...this.orders, ...response.data];
          this.page += 1;
          this.busy = false;
        });
    },
    loadOrder: function(order) {
      this.order = order;
    },
    reset: function() {
      this.busy = false;
      this.page = 0;
      this.lastPage = false;
      this.orders = [];
      this.order = null;
    }
  },
  watch: {
    activeStore: function(newValue, oldValue) {
      this.reset();
      this.loadPage();
    }
  },
  filters: {
    date: function(value) {
      return moment
        .unix(value)
        .utc()
        .format("H:mm — D MMMM YYYY");
    },
    money: function(value) {
      return `€${(+value).toFixed(2)}`;
    }
  }
});
