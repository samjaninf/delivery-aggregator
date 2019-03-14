<template>
  <b-container class="mt-4">
    <h3>Disponibilità</h3>
    <template v-if="stores.length > 1">
      <h5 class="mt-4">Negozio</h5>
      <b-form-select
        v-model="selectedStore"
        :options="options"
      />
    </template>
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
    <template v-else>

      <h4 class="mt-4">Apertura negozio</h4>
      <div>
        <b-form-checkbox
          switch
          v-model="isOpen"
        >
          Negozio {{ isOpen ? 'aperto' : 'chiuso' }}
        </b-form-checkbox>
      </div>

      <h4 class="mt-4">Prodotti</h4>
      <div
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
    </template>
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
      products: [],
      isOpen: null
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
  methods: {
    async fetchData() {
      try {
        const productsPromise = this.$http.get(
          `/stores/${this.selectedStore}/products`,
          {
            cancelToken: this.storeCancel.token
          }
        );
        const isOpenPromise = this.$http.get(
          `/stores/${this.selectedStore}/isOpen`,
          {
            cancelToken: this.storeCancel.token
          }
        );

        const [products, isOpen] = await Promise.all([
          productsPromise,
          isOpenPromise
        ]);

        this.products = products && products.data;
        this.isOpen = isOpen.data && isOpen.data.isOpen;
        this.loading = false;
      } catch (e) {
        if (this.$http.isCancel(e)) {
          console.log("Request canceled:", e.message);
        } else {
          console.error(e);
        }
      }
    }
  },
  watch: {
    stores: {
      immediate: true,
      handler(stores) {
        this.selectedStore = stores.length > 0 ? stores[0].code : null;
      }
    },
    selectedStore: {
      immediate: true,
      handler(store) {
        if (!store) {
          this.products = [];
          return;
        }

        if (this.loading) {
          this.storeCancel.cancel("Changed store");
          this.storeCancel = CancelToken.source();
        }

        this.products = [];
        this.isOpen = null;
        this.loading = true;

        this.fetchData();
      }
    },
    async isOpen(isOpen, oldIsOpen) {
      if (oldIsOpen === null || isOpen == null) return;

      try {
        await this.$http.post(`/stores/${this.selectedStore}/isOpen`, {
          isOpen
        });
        this.$notify({
          type: "success",
          text: "Stato di apertura negozio aggiornato con successo!"
        });
      } catch (e) {
        this.isOpen = oldIsOpen;
        this.$notify({
          type: "error",
          text: "Errore durante la modifica dello stato del negozio"
        });
      }
    }
  },
  components: {
    Product: require("./Product.vue").default
  }
};
</script>