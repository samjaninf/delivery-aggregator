<template>
  <div>
    <h3>Negozi</h3>
    <div class="row">
      <div class="col-md-6 mt-2">
        <div class="list-group">
          <a v-for="store in stores" :key="store.code" href="#" class="list-group-item list-group-item-action" @click="editing = store.code" :class="{ active: editing === store.code  }">
            {{ store.name }}
          </a>
          <a href="#" class="list-group-item list-group-item-action" @click="editing = null" :class="{ active: editing === null  }">
            <i class="fas fa-fw fa-plus"></i> Aggiungi
          </a>
        </div>
      </div>
      <div class="col-md-6 mt-2">
        <div class="card p-4">
          <form @submit.prevent="handleSubmit">
            <div class="form-group">
              <label>Nome</label>
              <input type="text" class="form-control" placeholder="Inserisci il nome" v-model="store.name">
            </div>
            <div class="form-group">
              <label>Codice identificativo</label>
              <input type="text" class="form-control" placeholder="Inserisci il codice identificativo" v-model="store.code">
              <small class="form-text text-muted">Assicurati di usare un codice identificativo unico.</small>
            </div>
            <div class="form-group">
              <label>URL</label>
              <input type="text" class="form-control" placeholder="Inserisci l'URL" v-model="store.url">
            </div>
            <div class="form-group">
              <label>Consumer Key (CK)</label>
              <input type="text" class="form-control" placeholder="Inserisci la Consumer Key (CK)" v-model="store.consumer_key">
            </div>
            <div class="form-group">
              <label>Consumer Secret (CS)</label>
              <input type="text" class="form-control" placeholder="Inserisci il Consumer Secret (CS)" v-model="store.consumer_secret">
            </div>
            <button type="submit" class="btn" :class="editing ? 'btn-primary' : 'btn-success'">
              {{ editing ? "Aggiorna" : "Salva nuovo" }}
            </button>
            <button type="button" class="btn btn-danger float-right" v-if="editing" @click="deleteStore">
              Elimina
            </button>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  data() {
    return {
      editing: null,
      store: {},
    };
  },
  watch: {
    editing() {
      if (!this.editing) {
        this.store = {};
        return;
      };

      this.$http
        .get(`stores/${this.editing}`)
        .then((response) => {
          this.store = response.data;
        });
    }
  },
  methods: {
    handleSubmit() {
      if (this.editing)
        this.$http
          .put('stores', this.store)
          .then(() => {
            const store = _.find(this.stores, { code: this.editing });
            Object.assign(store, this.store);
          });
      else
        this.$http
          .post('stores', this.store)
          .then(() => {
            this.stores.push(this.store);
            this.editing = this.store.code;
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
          });
    }
  },
  props: ['stores'],
}
</script>
