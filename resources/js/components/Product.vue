<template>
  <b-button
    :variant="variant"
    class="product"
    :class="{ loading: loading }"
    :disabled="loading"
    @click="toggleInStock"
  >{{ product.name }}</b-button>
</template>

<script>
export default {
  data() {
    return {
      loading: false
    };
  },
  props: ["store", "product"],
  computed: {
    variant() {
      return this.product.in_stock ? "success" : "danger";
    }
  },
  methods: {
    toggleInStock() {
      const { id, in_stock } = this.product;

      this.loading = true;
      this.$http
        .put(`/stores/${this.store}/products/${id}`, {
          in_stock: !in_stock
        })
        .then(({ data }) => {
          this.product.in_stock = data.in_stock;
        })
        .catch(() => {
          this.$notify({
            type: "error",
            text: "Errore durante il cambio di disponibilità"
          });
        })
        .finally(() => {
          this.loading = false;
        });
    }
  }
};
</script>

<style>
.product {
  margin: 0.5em;
  padding: 1em 0;
  width: 100%;
  white-space: normal;
  transition: all 0.5s ease;
}
</style>

