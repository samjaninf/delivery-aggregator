<template>
  <div :class="{ 'backdrop-open' : selectedOrder }">
    <div
      class="backdrop"
      v-if="selectedOrder"
      v-on:touchmove.stop
      @click.self="selectedOrder = null"
    >
      <order
        :order="selectedOrder"
        :detailed="true"
        :storeCode="storeCode"
        @close="selectedOrder = null"
        @print="printReceipt(selectedOrder)"
      ></order>
    </div>
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
      <div class="scroll-container">
        <div
          v-for="{dayOrders, day} in ordersByDate"
          class="mb-3"
          :key="day"
        >
          <div class="day-header">
            <h3 class="text-primary">{{ day }}</h3>
            <h3
              class="text-primary"
              v-if="$auth.check(['view totals', 'admin'])"
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
                @click="selectOrder(order)"
              ></order>
            </div>
          </div>
        </div>
        <h6 v-if="noMoreOrders">Non sono presenti altri ordini</h6>
        <infinite-loading
          v-else-if="activeStore"
          @infinite="loadNextPage"
          :identifier="activeStore.code"
          spinner="circles"
        ></infinite-loading>
      </div>
    </main>
  </div>
</template>

<script>
const CancelToken = axios.CancelToken;
const UPDATE_INTERVAL = 30 * 1000;
const ORDERS_PER_PAGE = 30;

const makeReceipt = require("../util/makeReceipt.js");
const { formatDate } = require("../util/formatTime");

export default {
  data() {
    return {
      orders: [],
      page: 0,
      storeCancel: CancelToken.source(),
      selectedOrder: null,
      intervalHandle: null,
      noMoreOrders: false,
      loadingNextPage: false
    };
  },
  props: ["storeCode", "stores"],
  computed: {
    ordersByDate() {
      return _(this.orders)
        .groupBy(o => formatDate(o.delivery_date))
        .toPairs()
        .orderBy(v => v[1][0].delivery_date, "desc")
        .map(v => ({
          day: v[0],
          dayOrders: _.orderBy(v[1], v => v.delivery_date, "desc")
        }))
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
    loadNextPage($state = null) {
      if (this.noMoreOrders) return;

      this.loadingNextPage = true;
      const page = this.page;

      this.loadPage(page).then(success => {
        if (success) this.page = page + 1;
        this.loadingNextPage = false;

        if ($state) {
          if (this.noMoreOrders) $state.complete();
          else $state.loaded();
        }
      });
    },
    reset() {
      this.storeCancel.cancel("Changed store");

      this.page = 0;
      this.orders = [];
      this.selectedOrder = null;
      this.storeCancel = CancelToken.source();
      this.noMoreOrders = false;
    },
    refresh(loaded) {
      this.loadPage(0).then(success => {
        if (loaded) loaded("done");
      });
    },
    selectOrder(order) {
      this.selectedOrder = order;

      if (this.$auth.check("set seen")) {
        this.$http
          .post(`stores/${this.activeStore.code}/orders/${order.number}/seen`)
          .then(() => {
            order.seen = true;
          })
          .catch(e => {
            console.error(e);
          });
      }
    },
    printReceipt(order) {
      const receipt = makeReceipt(this.activeStore, order);
      window.location.href = `quickprinter://${encodeURI(receipt)}`;
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
    Order: require("./Order.vue").default,
    InfiniteLoading: require("vue-infinite-loading").default
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
  width: 100vw;
  height: 100vh;
  background-color: rgba(0, 0, 0, 0.8);
  position: fixed;
  top: 0;
  left: 0;
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1;
}

.backdrop-open .scroll-container {
  overflow: hidden !important;
  pointer-events: none;
  position: fixed;
  max-width: 1110px;
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
</style>
