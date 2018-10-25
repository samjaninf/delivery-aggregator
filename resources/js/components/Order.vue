<template>
  <div class="order card mb-4" @click="$emit('click')" :class="{ cancelled: order.status === 'cancelled', detailed: detailed }">
    <div class="card-body">
      <div class="card-title mb-4">
        <h4 v-if="order.status === 'cancelled' ">
          <i class="fas fa-fw fa-exclamation-triangle"></i>
          Annullato
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
        <template v-if="detailed">
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
        </template>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: ['order', 'detailed']
}
</script>
