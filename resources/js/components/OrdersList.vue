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
          v-for="(dayOrders, day) in ordersByDate"
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
        <div
          class="loading-bar infinite-loading-bar"
          v-if="!noMoreOrders"
        >
          <div class="sk-folding-cube">
            <div class="sk-cube1 sk-cube"></div>
            <div class="sk-cube2 sk-cube"></div>
            <div class="sk-cube4 sk-cube"></div>
            <div class="sk-cube3 sk-cube"></div>
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
      return _.groupBy(this.orders, o =>
        moment
          .unix(o.delivery_date)
          .utc()
          .format("LL")
      );
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
          if (response.data.length == 0) {
            this.noMoreOrders = true;
            return false;
          }

          // Merge arrays
          for (let newOrder of response.data) {
            const old = _.find(this.orders, { number: newOrder.number });
            if (old) {
              Object.assign(old, newOrder);
            } else {
              this.orders.push(newOrder);
            }
          }

          return true; // loading successful
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

      this.loadNextPage();
    },
    refresh(loaded) {
      this.loadPage(0).then(success => {
        if (loaded) loaded("done");
      });
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
  loadingText: `<div class="sk-folding-cube">
      <div class="sk-cube1 sk-cube"></div>
      <div class="sk-cube2 sk-cube"></div>
      <div class="sk-cube4 sk-cube"></div>
      <div class="sk-cube3 sk-cube"></div>
    </div>`,
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

.sk-folding-cube {
  margin: 20px auto;
  width: 40px;
  height: 40px;
  position: relative;
  -webkit-transform: rotateZ(45deg);
  transform: rotateZ(45deg);
}

.sk-folding-cube .sk-cube {
  float: left;
  width: 50%;
  height: 50%;
  position: relative;
  -webkit-transform: scale(1.1);
  -ms-transform: scale(1.1);
  transform: scale(1.1);
}
.sk-folding-cube .sk-cube:before {
  content: "";
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: #3490dc;
  -webkit-animation: sk-foldCubeAngle 2.4s infinite linear both;
  animation: sk-foldCubeAngle 2.4s infinite linear both;
  -webkit-transform-origin: 100% 100%;
  -ms-transform-origin: 100% 100%;
  transform-origin: 100% 100%;
}
.sk-folding-cube .sk-cube2 {
  -webkit-transform: scale(1.1) rotateZ(90deg);
  transform: scale(1.1) rotateZ(90deg);
}
.sk-folding-cube .sk-cube3 {
  -webkit-transform: scale(1.1) rotateZ(180deg);
  transform: scale(1.1) rotateZ(180deg);
}
.sk-folding-cube .sk-cube4 {
  -webkit-transform: scale(1.1) rotateZ(270deg);
  transform: scale(1.1) rotateZ(270deg);
}
.sk-folding-cube .sk-cube2:before {
  -webkit-animation-delay: 0.3s;
  animation-delay: 0.3s;
}
.sk-folding-cube .sk-cube3:before {
  -webkit-animation-delay: 0.6s;
  animation-delay: 0.6s;
}
.sk-folding-cube .sk-cube4:before {
  -webkit-animation-delay: 0.9s;
  animation-delay: 0.9s;
}
@-webkit-keyframes sk-foldCubeAngle {
  0%,
  10% {
    -webkit-transform: perspective(140px) rotateX(-180deg);
    transform: perspective(140px) rotateX(-180deg);
    opacity: 0;
  }
  25%,
  75% {
    -webkit-transform: perspective(140px) rotateX(0deg);
    transform: perspective(140px) rotateX(0deg);
    opacity: 1;
  }
  90%,
  100% {
    -webkit-transform: perspective(140px) rotateY(180deg);
    transform: perspective(140px) rotateY(180deg);
    opacity: 0;
  }
}

@keyframes sk-foldCubeAngle {
  0%,
  10% {
    -webkit-transform: perspective(140px) rotateX(-180deg);
    transform: perspective(140px) rotateX(-180deg);
    opacity: 0;
  }
  25%,
  75% {
    -webkit-transform: perspective(140px) rotateX(0deg);
    transform: perspective(140px) rotateX(0deg);
    opacity: 1;
  }
  90%,
  100% {
    -webkit-transform: perspective(140px) rotateY(180deg);
    transform: perspective(140px) rotateY(180deg);
    opacity: 0;
  }
}
</style>
