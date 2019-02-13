<template>
  <b-container class="mt-4">
    <h3>Fasce orarie</h3>
    <template v-if="stores.length > 1">
      <h5 class="mt-4">Negozio</h5>
      <b-form-select
        v-model="selectedStore"
        :options="storeOptions"
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

    <b-row>
      <b-col lg="6">
        <b-form-group label="Numero massimo consegne per fascia oraria">
          <b-form-radio-group
            :disabled="loading"
            buttons
            v-model="lockout"
            :options="lockoutOptions"
            button-variant="outline-primary"
            size="lg"
            class="button-radio-group"
          />
        </b-form-group>
      </b-col>

      <b-col lg="6">
        <b-form-group label="Distanza minima ordine">
          <b-form-radio-group
            :disabled="loading"
            buttons
            v-model="delay"
            :options="delayOptions"
            button-variant="outline-primary"
            size="lg"
            class="button-radio-group"
          />
        </b-form-group>
      </b-col>
    </b-row>

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
      lockout: null,
      delay: null
    };
  },
  props: ["stores"],
  computed: {
    storeOptions() {
      return this.stores.map(({ name, code }) => ({
        text: name,
        value: code
      }));
    },
    lockoutOptions() {
      return [
        { text: "1 ordine", value: 1 },
        { text: "2 ordini", value: 2 },
        { text: "3 ordini", value: 3 }
      ];
    },
    delayOptions() {
      return [
        { text: "10 minuti", value: 10 },
        { text: "20 minuti", value: 20 },
        { text: "30 minuti", value: 30 }
      ];
    }
  },
  mounted() {
    this.selectedStore = this.stores.length > 0 ? this.stores[0].code : null;
  },
  watch: {
    stores: {
      immediate: true,
      handler(stores) {
        this.selectedStore = stores.length > 0 ? stores[0].code : null;
      }
    },
    selectedStore(store) {
      if (!store) {
        this.lockout = null;
        this.delay = null;
        return;
      }

      if (this.loading) {
        this.storeCancel.cancel("Changed store");
        this.storeCancel = CancelToken.source();
      }

      return; //  ==== NYI ====

      this.loading = true;
      this.$http
        .get(`/stores/${store}/deliveryslots`, {
          cancelToken: this.storeCancel.token
        })
        .then(({ data }) => {
          const { lockout, delay } = data;
          this.lockout = lockout || null;
          this.delay = delay || null;
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
  }
};
</script>

<style>
.button-radio-group {
  width: 100%;
}
.button-radio-group > .btn {
  flex-grow: 1;
}
</style>
