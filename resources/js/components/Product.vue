<template>
  <b-button
    :variant="variant"
    class="product"
  >{{ product.name }}</b-button>
</template>

<script>
export default {
  props: ["product"],
  computed: {
    variant() {
      return this.product.in_stock ? "success" : "danger";
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
  transition: color 1s ease;
}
</style>

