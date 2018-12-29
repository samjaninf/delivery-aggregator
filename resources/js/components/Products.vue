<template>
  <b-container class="mt-4">
    <h3>Prodotti</h3>
    <h5 class="mt-4">Negozio</h5>
    <b-form-radio-group
      buttons
      v-model="selectedStore"
      :options="options"
      button-variant="outline-primary"
    />
    <div
      class="text-center mt-4"
      v-if="loading"
    >
      <div class="lds-ring">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
      </div>
    </div>
    <div
      v-else
      v-for="(products, category) in productsByCategory"
      :key="category"
    >
      <h5 class="mt-4">{{ category }} </h5>
      <b-row>
        <b-col
          md="6"
          lg="4"
          v-for="product in products"
          :key="product.id"
        >
          <product
            :store="selectedStore"
            :product="product"
          />
        </b-col>
      </b-row>
    </div>
  </b-container>
</template>

<script>
const CancelToken = axios.CancelToken;

export default {
  data() {
    return {
      storeCancel: CancelToken.source(),
      loading: false,
      selectedStore: null,
      products: []
    };
  },
  props: ["stores"],
  computed: {
    options() {
      return this.stores.map(({ name, code }) => ({
        text: name,
        value: code
      }));
    },
    productsByCategory() {
      return _.groupBy(this.products, p => p.category);
    }
  },
  mounted() {
    this.selectedStore = this.stores.length > 0 ? this.stores[0].code : null;
  },
  watch: {
    selectedStore(store) {
      if (!store) {
        this.products = [];
        return;
      }

      if (this.loading) {
        this.storeCancel.cancel("Changed store");
        this.storeCancel = CancelToken.source();
      }

      this.loading = true;
      this.$http
        .get(`/stores/${store}/products`, {
          cancelToken: this.storeCancel.token
        })
        .then(({ data }) => {
          this.products = data || [];
          this.loading = false;
        })
        .catch(e => {
          if (this.$http.isCancel(e)) {
            console.log("Request canceled:", e.message);
          } else {
            console.error(e);
          }

          return false; // loading failed
        });
    }
  },
  components: {
    Product: require("./Product.vue")
  }
};
</script>