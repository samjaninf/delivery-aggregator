<template>
  <div>
    <h3>Utenti</h3>
    <b-row>
      <b-col md="6" class="mt-2">
        <b-list-group>
          <b-list-group-item v-for="user in users" :key="user.id" href="#" @click="editing = user.id" :active="editing === user.id">
            {{ user.name }}
          </b-list-group-item>
          <b-list-group-item href="#" @click="editing = null" :active="editing === null">
            <i class="fas fa-fw fa-plus"></i> Aggiungi
          </b-list-group-item>
        </b-list-group>
      </b-col>
      <b-col md="6" class="mt-2">
        <b-card class="p-1">
          <form @submit.prevent="handleSubmit">
            <b-form-group label="Nome">
              <b-form-input type="text" placeholder="Inserisci il nome" v-model="user.name" required></b-form-input>
            </b-form-group>
            <b-form-group label="Email" description="Assicurati di usare una email unica,">
              <b-form-input type="email" placeholder="Inserisci l'email" v-model="user.email" required></b-form-input>
            </b-form-group>
            <b-form-group label="Password" description="Inserisci una password per modificare quella esistente.">
              <b-form-input type="password" placeholder="Inserisci la password" v-model="user.password" :required="!editing"></b-form-input>
            </b-form-group>
            <b-form-group label="Firebase Registration ID" v-if="editing">
              <b-form-input type="text" class="form-control" v-model="user.fb_device_id" disabled></b-form-input>
            </b-form-group>
            <b-form-group>
              <b-form-checkbox v-model="user.is_admin" :disabled="$auth.user().id === user.id">
                Amministratore
              </b-form-checkbox>
            </b-form-group>
            <b-form-group label="Permessi">
              <b-form-checkbox-group v-model="user.permissions" :options="checkboxOptions">
              </b-form-checkbox-group>
            </b-form-group>
            <button type="submit" class="btn" :class="editing ? 'btn-primary' : 'btn-success'">
              {{ editing ? "Aggiorna" : "Salva nuovo" }}
            </button>
            <button type="button" class="btn btn-danger float-right" v-if="editing" @click="deleteUser">
              Elimina
            </button>
          </form>
        </b-card>
      </b-col>
    </b-row>
  </div>
</template>

<script>
const emptyUser = () => ({
  name: '',
  email: '',
  password: '',
  is_admin: false,
  permissions: [],
})

export default {
  data() {
    return {
      users: [],
      editing: null,
      user: emptyUser(),
    };
  },
  computed: {
    checkboxOptions() {
      return this.stores.map(s => ({ text: s.name, value: s.code }));
    }
  },
  watch: {
    editing() {
      if (!this.editing) {
        this.user = this.emptyUser();
        return;
      };

      this.$http
        .get(`users/${this.editing}`)
        .then((response) => {
          this.user = response.data;
          this.user.is_admin = !!this.user.is_admin;
        });
    }
  },
  methods: {
    emptyUser() {
      return emptyUser();
    },

    loadUsers() {
      this.$http
        .get('users')
        .then((response) => {
          this.users = response.data 
        });
    },

    handleSubmit() {
      if (this.editing)
        this.$http
          .put('users', this.user)
          .then(() => {
            const user = _.find(this.users, { id: this.editing });
            Object.assign(user, this.user);

            this.$notify({
              type: "success",
              text: "Utente aggiornato con successo"
            });
          })
          .catch((e) => {
            this.$notify({
              type: "error",
              text: "Errore durante il salvataggio"
            })
          });
      else
        this.$http
          .post('users', this.user)
          .then((response) => {
            this.users.push(response.data);
            this.editing = response.data.id;

            this.$notify({
              type: "success",
              text: "Utente salvato con successo"
            });
          })
          .catch((e) => {
            this.$notify({
              type: "error",
              text: "Errore durante il salvataggio"
            })
          });
    },

    deleteUser() {
      if (this.editing)
        this.$http
          .delete(`users/${this.editing}`)
          .then(() => {
            const idx = _.findIndex(this.users, { id: this.editing });
            this.users.splice(idx, 1);
            this.editing = null;

            this.$notify({
              type: "success",
              text: "Utente eliminato con successo"
            });
          })
          .catch((e) => {
            this.$notify({
              type: "error",
              text: "Errore durante l'eliminazione"
            })
          });
    }
  },
  mounted() {
    this.loadUsers();
  },
  props: ['stores']
}
</script>
