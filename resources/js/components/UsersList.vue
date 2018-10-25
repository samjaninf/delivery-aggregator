<template>
  <div>
    <h3>Utenti</h3>
    <div class="row">
      <div class="col-md-6 mt-2">
        <div class="list-group">
          <a v-for="user in users" :key="user.id" href="#" class="list-group-item list-group-item-action" @click="editing = user.id" :class="{ active: editing === user.id  }">
            {{ user.name }}
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
              <input type="text" class="form-control" placeholder="Inserisci il nome" v-model="user.name">
            </div>
            <div class="form-group">
              <label>Email</label>
              <input type="email" class="form-control" placeholder="Inserisci l'email" v-model="user.email">
              <small class="form-text text-muted">Assicurati di usare una email unica.</small>
            </div>
            <div class="form-group">
              <label>Password</label>
              <input type="password" class="form-control" placeholder="Inserisci la password" v-model="user.password">
              <small class="form-text text-muted">Inserisci una password per modificare quella esistente.</small>
            </div>
            <button type="submit" class="btn btn-primary">
              {{ editing ? "Aggiorna" : "Salva" }}
            </button>
            <button type="button" class="btn btn-danger float-right" v-if="editing" @click="deleteUser">
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
      users: [],
      editing: null,
      user: {},
    };
  },
  watch: {
    editing() {
      if (!this.editing) {
        this.user = {};
        return;
      };

      axios
        .get(`/api/users/${this.editing}`)
        .then((response) => {
          this.user = response.data;
        });
    }
  },
  methods: {
    loadUsers() {
      axios
        .get('/api/users')
        .then((response) => {
          this.users = response.data 
        });
    },

    handleSubmit() {
      if (this.editing)
        axios
          .put('/api/users', this.user)
          .then(() => {
            const user = _.find(this.users, { id: this.editing });
            Object.assign(user, this.user);
          });
      else
        axios
          .post('/api/users', this.user)
          .then((response) => {
            this.users.push(response.data);
            this.editing = response.data.id;
          });
    },

    deleteUser() {
      if (this.editing)
        axios
          .delete(`/api/users/${this.editing}`)
          .then(() => {
            const idx = _.findIndex(this.users, { id: this.editing });
            this.users.splice(idx, 1);
            this.editing = null;
          });
    }
  },
  mounted() {
    this.loadUsers();
  }
}
</script>
