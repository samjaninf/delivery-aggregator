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
    <main role="main" class="container mt-4" v-if="activeStore">
      <h1 class="text-center mt-4 mb-4">Ordini {{ activeStore.name }}</h1>
      <div>
        <div v-for="(dayOrders, day) in ordersByDate" class="mb-3" :key="day">
          <h3 class="text-primary">{{ day }}</h3>
          <hr />
          <div class="orders row">
            <div class="col-lg-4 col-md-6" v-for="order in dayOrders" :key="order.number">
              <div class="order card mb-4" @click="loadOrder(order)" :class="{ cancelled: order.status === 'cancelled' }">
                <div class="card-body">
                  <div class="card-title mb-4">
                    <h4 v-if="order.status === 'cancelled' ">
                      <i class="fas fa-fw fa-exclamation-triangle"></i>
                      ANNULLATO
                    </h4>
                    <span class="float-right">
                      Ordine #{{ order.number }}
                    </span>
                    <h4 class="d-inline">
                      <i class="far fa-clock"></i>
                      {{ order.delivery_date | hour }}–{{ order.delivery_date_end | hour }}
                    </h4>
                  </div>
                  <div class="card-text">
                    <p><i class="fas fa-fw fa-user"></i><span>{{ order.first_name }} {{ order.last_name }}</span></p>
                    <p><i class="fas fa-fw fa-map-pin"></i><span>{{ order.address }}, {{ order.city }}</span></p>
                    <p v-if="order.phone"><i class="fas fa-fw fa-phone"></i><span>{{ order.phone }}</span></p>
                    <p v-if="order.payment_method === 'cod'">
                      <i class="fas fa-fw fa-money-bill-alt"></i><span>{{ order.total | money }} (Contanti)
                      </span></p>
                    <p v-if="order.payment_method === 'ppec_paypal'">
                      <i class="fab fa-fw fa-paypal"></i><span>{{ order.total | money }} (Paypal)
                      </span></p>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <infinite-loading @infinite="loadPage" :identifier="activeStore.code"></infinite-loading>
      </div>
    </main>
    <div class="backdrop" v-if="order" @click.self="order = null">
      <div class="selected-order order card" @click="loadOrder(order)" :class="{ cancelled: order.status === 'cancelled' }">
        <div class="card-body">
          <div class="card-title mb-4">
            <h4 v-if="order.status === 'cancelled' ">
              <i class="fas fa-fw fa-exclamation-triangle"></i>
              ANNULLATO
            </h4>
            <span class="float-right">
              Ordine #{{ order.number }}
            </span>
            <h4 class="d-inline">
              <i class="far fa-clock"></i>
              {{ order.delivery_date | hour }}–{{ order.delivery_date_end | hour }}
            </h4>
          </div>
          <div class="card-text">
            <p><i class="fas fa-fw fa-user"></i> {{ order.first_name }} {{ order.last_name }}</p>
            <p><i class="fas fa-fw fa-map-pin"></i> {{ order.address }}, {{ order.city }}</p>
            <p v-if="order.phone"><i class="fas fa-fw fa-phone"></i> {{ order.phone }}</p>
            <p v-if="order.payment_method === 'cod'">
              <i class="fas fa-fw fa-money-bill-alt"></i> {{ order.total | money }} (Contanti)
            </p>
            <p v-if="order.payment_method === 'ppec_paypal'">
              <i class="fab fa-fw fa-paypal"></i> {{ order.total | money }} (Paypal)
            </p>
            <hr />
            <h5>Prodotti</h5>
            <div class="media ml-2" v-for="item in order.items" :key="item.name">
              <div class="media-body">
                <div>
                  <p class="float-right">{{ item.total | money }}</p>
                  <h6 class="d-inline">{{ item.quantity }} &times; {{ item.name }}</h6>
                </div>
                <ul class="meta">
                  <li v-for="(value, key) in item.meta" class="mt-2" :key="key">
                    <span class="key d-block" v-html="`${key}: `"></span>
                    <em>{{ value }}</em>
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
const CancelToken = axios.CancelToken;
export default {
  data: function() {
    return {
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
      axios.get("/api/stores").then(response => {
        this.stores = response.data;
        if (this.stores.length > 0) this.activeStore = this.stores[0];
      });
    },
    loadPage: function($state) {
      console.log('Load Page');
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
    loadOrder: function(order) {
      this.order = order;
    },
    reset: function() {
      this.storeCancel.cancel("Changed store");

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
  },
}
</script>
