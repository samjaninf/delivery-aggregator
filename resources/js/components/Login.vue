<template>
  <div class="cover text-center">

    <form class="form-signin" @submit.prevent="handleSubmit">
      <h1 class="h3 mb-3 font-weight-normal">Effettua l'accesso</h1>
      <input type="email" class="form-control" placeholder="Indirizzo email" required autofocus v-model="email">
      <input type="password" class="form-control" placeholder="Password" required v-model="password">
      <button class="btn btn-lg btn-primary btn-block" type="submit">Accedi</button>
      <div class="alert alert-danger mt-4" v-if="error">
        Credenziali invalide.
      </div>
    </form>
  </div>
</template>

<script>
export default {
  data() {
    return {
      email: '',
      password: '',
      error: false
    }
  },
  methods: {
    handleSubmit() {
      this.$auth.login({
        data: { email: this.email, password: this.password },
        rememberMe: false,
        success: () => { this.error = false },
        error: () => { this.error = true },
      });
    }
  }
}
</script>

<style>
.cover {
  display: -ms-flexbox;
  display: -webkit-box;
  display: flex;
  -ms-flex-align: center;
  -ms-flex-pack: center;
  -webkit-box-align: center;
  align-items: center;
  -webkit-box-pack: center;
  justify-content: center;
  padding-top: 40px;
  padding-bottom: 40px;
  background-color: #f5f5f5;
}

.form-signin {
  width: 100%;
  max-width: 330px;
  padding: 15px;
  margin: 0 auto;
}
.form-signin .checkbox {
  font-weight: 400;
}
.form-signin .form-control {
  position: relative;
  box-sizing: border-box;
  height: auto;
  padding: 10px;
  font-size: 16px;
}
.form-signin .form-control:focus {
  z-index: 2;
}
.form-signin input[type="email"] {
  margin-bottom: -1px;
  border-bottom-right-radius: 0;
  border-bottom-left-radius: 0;
}
.form-signin input[type="password"] {
  margin-bottom: 10px;
  border-top-left-radius: 0;
  border-top-right-radius: 0;
}
</style>

