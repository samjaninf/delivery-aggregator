<template>
  <div>
    <b-button
      variant="success"
      @click="$emit('assigned')"
      v-if="$auth.check(['set out for delivery']) && shippingRequired && !order.assigned && order.status === 'processing'"
    >
      <i class="fas fa-check"></i> Accetto l'ordine
    </b-button>

    <template v-if="order.status === 'processing' && $auth.check(['set prepared', 'set out for delivery', 'admin'])">
      <b-button
        variant="primary"
        @click="$emit('prepared')"
        v-if="!order.prepared && $auth.check(['set prepared'])"
      >
        <i class="fas fa-check"></i> Preparato
      </b-button>
      <b-button
        variant="primary"
        @click="$emit('outForDelivery')"
        v-if="$auth.check(['set out for delivery', 'admin']) && shippingRequired && order.assigned"
      >
        <i class="fas fa-motorcycle"></i> In consegna
      </b-button>
    </template>

    <template v-if="order.status === 'out-for-delivery' && $auth.check(['set completed', 'admin']) && shippingRequired && order.assigned">
      <b-button
        variant="success"
        @click="$emit('completed')"
      >
        <i class="fas fa-box"></i> Consegnato
      </b-button>
    </template>

    <b-button
      variant="secondary"
      @click="$emit('print')"
      v-if="$auth.check(['print receipts', 'admin'])"
    >
      <i
        class="fas fa-print"
        style="margin-right: 0"
      ></i>
    </b-button>

    <b-button
      variant="danger"
      @click="lateButtonClick"
      v-if="$auth.check(['set out for delivery']) && shippingRequired && !order.late && order.status === 'processing'"
    >
      <span v-if="confirmLate">Vuoi segnalare ritardo ristorante?</span>
      <i
        v-else
        class="fas fa-stopwatch"
      ></i>
    </b-button>

    <b-button
      variant="secondary"
      @click="$emit('unassigned')"
      v-if="$auth.check(['set unassigned', 'admin']) && shippingRequired && order.assigned && order.status !== 'completed'"
    >
      <i class="fas fa-times"></i> Riassegna
    </b-button>
  </div>
</template>

<script>
export default {
  data() {
    return {
      confirmLate: false
    };
  },
  methods: {
    lateButtonClick() {
      if (this.confirmLate) {
        this.$emit("late");
      } else this.confirmLate = true;
    }
  },
  props: ["order", "shippingRequired"]
};
</script>

