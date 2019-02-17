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

    <b-row class="mt-4">
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
            v-model="cutoff"
            :options="cutoffOptions"
            button-variant="outline-primary"
            size="lg"
            class="button-radio-group"
          />
        </b-form-group>
      </b-col>
    </b-row>

    <b-button
      variant="success"
      size="lg"
      @click="updateLockoutAndCutoff()"
    >
      Salva
    </b-button>

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
      cutoff: null
    };
  },
  methods: {
    updateLockoutAndCutoff() {
      this.loading = true;
      this.$http
        .post(`/stores/${this.selectedStore}/deliveryslots`, {
          lockout: this.lockout,
          cutoff: this.cutoff
        })
        .then(() => {
          this.$notify({
            type: "success",
            text: "Fascia oraria aggiornata con successo"
          });
          this.loading = false;
        })
        .catch(e => {
          this.$notify({
            type: "error",
            text: "Errore durante la modifica"
          });
          this.loading = false;
        });
    }
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
    cutoffOptions() {
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
        this.cutoff = null;
        return;
      }

      if (this.loading) {
        this.storeCancel.cancel("Changed store");
        this.storeCancel = CancelToken.source();
      }

      this.loading = true;
      this.$http
        .get(`/stores/${store}/deliveryslots`, {
          cancelToken: this.storeCancel.token
        })
        .then(({ data }) => {
          const { lockout, cutoff } = data;
          this.lockout = lockout || null;
          this.cutoff = cutoff || null;
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
