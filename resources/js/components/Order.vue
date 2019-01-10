<template>
  <b-card
    class="order"
    @click="$emit('click')"
    :class="{ cancelled: order.status === 'cancelled', detailed: detailed, 'mb-4': !detailed }"
  >
    <div class="card-title">
      <button
        type="button"
        class="close d-block ml-4"
        v-if="detailed"
        @click="$emit('close')"
      >
        <span>&times;</span>
      </button>
      <span class="order-number">
        Ordine #{{ order.number }}
      </span>
      <h4
        v-if="order.status === 'cancelled'"
        class="text-center mb-4"
      >
        Annullato
      </h4>
      <h4 class="order-slot">
        <i
          v-if="order.status === 'cancelled'"
          class="fas fa-fw fa-exclamation-triangle"
        ></i>
        <i
          v-else-if="order.status === 'out-for-delivery'"
          class="fas fa-fw fa-motorcycle"
          style="color: #3490dc;"
        ></i>
        <i
          v-else-if="order.status === 'completed'"
          class="fas fa-fw fa-box"
          style="color: #28a745;"
        ></i>
        <i
          v-else
          class="far fa-fw fa-clock"
        ></i>
        {{ order.delivery_date | hour }}–{{ order.delivery_date_end | hour }}
      </h4>
    </div>
    <div class="card-text">
      <p><i class="fas fa-fw fa-user"></i><span>{{ order.first_name }} {{ order.last_name }}</span></p>
      <p><i class="fas fa-fw fa-map-pin"></i><span>{{ order.address }}, {{ order.city }}</span></p>
      <p v-if="order.phone"><i class="fas fa-fw fa-phone"></i>
        <a
          v-if="detailed"
          :href="`tel:${order.phone}`"
        ><span>{{ order.phone }}</span></a>
        <span v-else>{{ order.phone }}</span>
      </p>
      <p v-if="order.payment_method === 'cod'">
        <i class="fas fa-fw fa-money-bill-alt"></i>
        <span>{{ order.total | money }} (Contanti)</span>
      </p>
      <p v-if="order.payment_method === 'ppec_paypal'">
        <i class="fab fa-fw fa-paypal"></i>
        <span>{{ order.total | money }} (Paypal)</span>
      </p>
      <template v-if="detailed">
        <div
          v-for="coupon in order.coupons"
          :key="coupon.code"
        >
          <div>
            <p class="float-right">{{ coupon.discount | money }}</p>
            <h6 class="d-inline">
              <i class="fas fa-certificate"></i>
              Coupon: <strong class="ml-1">{{ coupon.code }}</strong>
            </h6>
          </div>
        </div>

        <div
          class="text-center mt-2"
          v-if="order.status === 'processing' && $auth.check(['set out for delivery', 'admin'])"
        >
          <b-button
            variant="primary"
            @click="setOutForDelivery"
          >
            <i class="fas fa-motorcycle"></i> In consegna
          </b-button>
        </div>
        <div
          class="text-center mt-2"
          v-if="order.status === 'out-for-delivery' && $auth.check(['set completed', 'admin'])"
        >
          <b-button
            variant="success"
            @click="setCompleted"
          >
            <i class="fas fa-box"></i> Consegnato
          </b-button>
        </div>

        <hr />
        <template v-if="order.notes">
          <h5>Note</h5>
          <p>{{ order.notes }}</p>
        </template>
        <h5>Prodotti</h5>
        <div
          class="media ml-2"
          v-for="(item, i) in order.items"
          :key="i"
        >
          <div class="media-body">
            <div>
              <p class="float-right">{{ item.total | money }}</p>
              <h6 class="d-inline">{{ item.quantity }} &times; {{ item.name }}</h6>
            </div>
            <ul class="meta">
              <li
                v-for="(value, key) in item.meta"
                class="mt-2"
                :key="key"
              >
                <span
                  class="key d-block"
                  v-html="`${key}: `"
                ></span>
                <em>{{ value && Array.isArray(value) ? value.join(", ") : "" }}</em>
              </li>
            </ul>
          </div>
        </div>
        <div
          class="media ml-2"
          v-if="order.shipping > 0"
        >
          <div class="media-body">
            <div>
              <p class="float-right">{{ order.shipping | money }}</p>
              <h6 class="d-inline font-italic">Costi di spedizione</h6>
            </div>
          </div>
        </div>
      </template>
    </div>
  </b-card>
</template>

<script>
const updateState = (state, endpoint) => {
  return function() {
    this.$http
      .post(`stores/${this.storeCode}/orders/${this.order.number}/${endpoint}`)
      .then(() => {
        this.order.status = state;

        this.$notify({
          type: "success",
          text: "Ordine aggiornato con successo"
        });
      })
      .catch(() => {
        this.$notify({
          type: "error",
          text: "Errore durante il cambio di stato"
        });
      });
  };
};

export default {
  props: ["order", "detailed", "storeCode"],
  methods: {
    setOutForDelivery: updateState("out-for-delivery", "outfordelivery"),
    setCompleted: updateState("completed", "completed")
  }
};
</script>

<style scoped>
.card .card-title {
  display: flex;
  flex-direction: row-reverse;
  flex-wrap: wrap;
}

.card .card-title > .order-slot {
  flex-grow: 1;
}

.card .card-text i {
  margin-right: 1em;
}

.card.cancelled {
  color: var(--danger);
  border-color: var(--danger);
}

.order.card p {
  display: flex;
}

.order.detailed {
  width: 400px;
  max-width: 100vw;
  max-height: 100vh;
  overflow-y: auto;
}

.meta {
  font-size: 0.8em;
  list-style: none;
}

.meta .key {
  font-weight: 500;
}

@media only screen and (max-width: 400px) {
  .card .card.detailed {
    border-radius: 0;
  }
}
</style>
