<template>
  <div>
    <h3>Negozi</h3>
    <b-row>
      <b-col
        md="6"
        class="mt-2"
      >
        <b-list-group>
          <b-list-group-item
            href="#"
            @click="editing = null"
            :active="editing === null"
          >
            <i class="fas fa-fw fa-plus"></i> Aggiungi
          </b-list-group-item>
          <b-list-group-item
            v-for="store in stores"
            :key="store.code"
            href="#"
            @click="editing = store.code"
            :active="editing === store.code"
          >
            {{ store.name }}
          </b-list-group-item>
        </b-list-group>
      </b-col>
      <b-col
        md="6"
        class="mt-2 store-detail"
      >
        <b-card class="p-1">
          <b-form @submit.prevent="handleSubmit">
            <b-form-group label="Nome">
              <b-form-input
                type="text"
                placeholder="Inserisci il nome"
                v-model="store.name"
                required
              ></b-form-input>
            </b-form-group>
            <b-form-group
              label="Codice identificativo"
              description="Assicurati di usare un codice identificativo unico."
            >
              <b-form-input
                type="text"
                class="form-control"
                placeholder="Inserisci il codice identificativo"
                v-model="store.code"
                required
              ></b-form-input>
            </b-form-group>
            <b-form-group label="URL">
              <b-form-input
                type="url"
                class="form-control"
                placeholder="Inserisci l'URL"
                v-model="store.url"
                required
              ></b-form-input>
            </b-form-group>
            <b-form-group label="Consumer Key (CK)">
              <b-form-input
                type="text"
                class="form-control"
                placeholder="Inserisci la Consumer Key (CK)"
                v-model="store.consumer_key"
                required
              ></b-form-input>
            </b-form-group>
            <b-form-group label="Consumer Secret (CS)">
              <b-form-input
                type="text"
                class="form-control"
                placeholder="Inserisci il Consumer Secret (CS)"
                v-model="store.consumer_secret"
                required
              ></b-form-input>
            </b-form-group>
            <template v-if="editing">
              <b-form-group label="Firebase Notification Key">
                <b-form-input
                  type="text"
                  class="form-control"
                  v-model="store.fb_notification_key"
                  disabled
                ></b-form-input>
              </b-form-group>
              <b-form-checkbox
                v-model="store.is_superstore"
                style="margin-bottom: 0.5em;"
              >Superstore</b-form-checkbox>
              <b-form-group
                label="Negozi"
                v-if="store.is_superstore"
              >
                <b-form-checkbox-group
                  v-model="store.substores"
                  :options="substoresOptions"
                >
                </b-form-checkbox-group>
              </b-form-group>
            </template>
            <div style="margin-top: 2em;">
              <b-button
                type="submit"
                :variant="editing ? 'primary' : 'success'"
              >
                {{ editing ? "Aggiorna" : "Salva nuovo" }}
              </b-button>
              <b-button
                type="button"
                class="float-right"
                variant="danger"
                v-if="editing"
                @click="deleteStore"
              >
                Elimina
              </b-button>
            </div>
          </b-form>
        </b-card>
      </b-col>
    </b-row>
  </div>
</template>

<script>
export default {
  data() {
    return {
      editing: null,
      store: {}
    };
  },
  computed: {
    substoresOptions() {
      if (!this.editing) return [];
      return this.stores
        .filter(s => s.code != this.editing)
        .map(s => ({ text: s.name, value: s.code }));
    }
  },
  watch: {
    editing() {
      if (!this.editing) {
        this.store = {};
        return;
      }

      this.$http.get(`stores/${this.editing}`).then(response => {
        this.store = response.data;
      });
    }
  },
  methods: {
    handleSubmit() {
      if (this.editing)
        this.$http
          .put("stores", this.store)
          .then(() => {
            const store = _.find(this.stores, { code: this.editing });
            Object.assign(store, this.store);

            this.$notify({
              type: "success",
              text: "Negozio aggiornato con successo"
            });
          })
          .catch(e => {
            this.$notify({
              type: "error",
              text: "Errore durante il salvataggio"
            });
          });
      else
        this.$http
          .post("stores", this.store)
          .then(() => {
            this.stores.push(this.store);
            this.editing = this.store.code;

            this.$notify({
              type: "success",
              text: "Negozio salvato con successo"
            });
          })
          .catch(e => {
            this.$notify({
              type: "error",
              text: "Errore durante il salvataggio"
            });
          });
    },

    deleteStore() {
      if (this.editing)
        this.$http
          .delete(`stores/${this.editing}`)
          .then(() => {
            const idx = _.findIndex(this.stores, { code: this.editing });
            this.stores.splice(idx, 1);
            this.editing = null;

            this.$notify({
              type: "success",
              text: "Negozio eliminato con successo"
            });
          })
          .catch(e => {
            this.$notify({
              type: "error",
              text: "Errore durante l'eliminazione"
            });
          });
    }
  },
  props: ["stores"]
};
</script>

<style scoped>
.store-detail {
  position: sticky;
  top: 30px;
  height: 100%;
}
</style>
