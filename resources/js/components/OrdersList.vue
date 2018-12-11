<template>
  <pull-to
    :top-load-method="refresh"
    :top-config="pullToConfig"
    @infinite-scroll="loadNextPage"
  >
    <template
      slot="top-block"
      slot-scope="props"
    >
      <div class="loading-bar">
        <span v-html="props.stateText"></span>
      </div>
    </template>
    <main
      role="main"
      class="container mt-4"
      v-if="activeStore"
    >
      <h1 class="text-center mt-4 mb-4">Ordini —
        <span style="white-space: pre;">{{ activeStore.name }}</span>
      </h1>
      <div>
        <div
          v-for="{dayOrders, day} in ordersByDate"
          class="mb-3"
          :key="day"
        >
          <div class="day-header">
            <h3 class="text-primary">{{ day }}</h3>
            <h3
              class="text-primary"
              v-if="$auth.check('admin')"
            >
              {{ dayOrders.reduce((acc, o) => acc + Number(o.total), 0) | money }}
            </h3>
          </div>
          <hr />
          <div class="orders row">
            <div
              class="col-lg-4 col-md-6"
              v-for="order in dayOrders"
              :key="order.number"
            >
              <order
                :order="order"
                @click="selectedOrder = order"
              ></order>
            </div>
          </div>
        </div>
        <div class="loading-bar infinite-loading-bar">
          <h6 v-if="noMoreOrders">Non sono presenti altri ordini</h6>
          <div
            class="lds-ring"
            v-else
          >
            <div></div>
            <div></div>
            <div></div>
            <div></div>
          </div>
        </div>
        <div
          class="backdrop"
          v-if="selectedOrder"
          @click.self="selectedOrder = null"
        >
          <order
            :order="selectedOrder"
            :detailed="true"
            :storeCode="storeCode"
            @close="selectedOrder = null"
          ></order>
        </div>
      </div>
    </main>
  </pull-to>
</template>

<script>
const CancelToken = axios.CancelToken;
const UPDATE_INTERVAL = 30 * 1000;
const ORDERS_PER_PAGE = 20;

export default {
  data() {
    return {
      orders: [],
      page: 0,
      storeCancel: CancelToken.source(),
      selectedOrder: null,
      intervalHandle: null,
      noMoreOrders: false
    };
  },
  props: ["storeCode", "stores"],
  computed: {
    ordersByDate() {
      return _(this.orders)
        .groupBy(o =>
          moment
            .unix(o.delivery_date)
            .utc()
            .format("LL")
        )
        .toPairs()
        .orderBy(v => v[1][0].delivery_date, "desc")
        .map(v => ({ day: v[0], dayOrders: v[1] }))
        .value();
    },
    activeStore() {
      return _.find(this.stores, { code: this.storeCode });
    },
    pullToConfig() {
      return pullToConfig;
    }
  },
  methods: {
    loadPage(page = 0) {
      return this.$http
        .get(`stores/${this.activeStore.code}/orders?page=${page + 1}`, {
          cancelToken: this.storeCancel.token
        })
        .then(response => {
          // Merge arrays
          for (let newOrder of response.data) {
            const old = _.find(this.orders, { number: newOrder.number });
            if (old) {
              Object.assign(old, newOrder);
            } else {
              this.orders.push(newOrder);
            }
          }

          if (response.data.length >= ORDERS_PER_PAGE) {
            return true; // loading successful
          } else {
            this.noMoreOrders = true;
            return false;
          }
        })
        .catch(e => {
          if (this.$http.isCancel(e)) {
            console.log("Request canceled:", e.message);
          } else {
            console.error(e);
          }

          return false; // loading failed
        });
    },
    loadNextPage() {
      if (this.noMoreOrders) return;

      const page = this.page;

      this.loadPage(page).then(success => {
        if (success) this.page = page + 1;
      });
    },
    reset() {
      this.storeCancel.cancel("Changed store");

      this.page = 0;
      this.orders = [];
      this.selectedOrder = null;
      this.storeCancel = CancelToken.source();
      this.noMoreOrders = false;

      if (this.activeStore) this.loadNextPage();
    },
    refresh(loaded) {
      this.loadPage(0).then(success => {
        if (loaded) loaded("done");
      });
    }
  },
  watch: {
    activeStore: {
      handler() {
        this.reset();
      },
      immediate: true,
      deep: true
    }
  },
  mounted() {
    this.intervalHandle = setInterval(this.loadPage, UPDATE_INTERVAL);
  },
  beforeDestroy() {
    clearInterval(this.intervalHandle);
  },
  components: {
    Order: require("./Order.vue"),
    PullTo: require("vue-pull-to")
  }
};

const pullToConfig = {
  pullText:
    '<i class="fas fa-arrow-down"></i> Scorri per aggiornare <i class="fas fa-arrow-down"></i>',
  triggerText:
    '<i class="fas fa-arrow-down"></i> Scorri per aggiornare <i class="fas fa-arrow-down"></i>',
  loadingText: `<div class="lds-ring"><div></div><div></div><div></div><div></div></div>`,
  doneText: '<i class="fas fa-check"></i>',
  failText: '<i class="fas fa-exclamation-circle"></i>',
  loadedStayTime: 400,
  stayDistance: 50,
  triggerDistance: 70
};
</script>

<style>
.day-header + hr {
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

.day-header {
  display: flex;
  justify-content: space-between;
}

.day-header h3 {
  margin-bottom: 0;
}

.loading-bar {
  width: 100%;
  text-align: center;
  margin-top: 1em;
}

.infinite-loading-bar {
  height: 3em;
  margin-top: 3em;
  margin-bottom: 5em;
}

.lds-ring {
  display: inline-block;
  position: relative;
  width: 64px;
  height: 64px;
}
.lds-ring div {
  box-sizing: border-box;
  display: block;
  position: absolute;
  width: 51px;
  height: 51px;
  margin: 6px;
  border: 6px solid #3490dc;
  border-radius: 50%;
  animation: lds-ring 1.2s cubic-bezier(0.5, 0, 0.5, 1) infinite;
  border-color: #3490dc transparent transparent transparent;
}
.lds-ring div:nth-child(1) {
  animation-delay: -0.45s;
}
.lds-ring div:nth-child(2) {
  animation-delay: -0.3s;
}
.lds-ring div:nth-child(3) {
  animation-delay: -0.15s;
}
@keyframes lds-ring {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}
</style>
