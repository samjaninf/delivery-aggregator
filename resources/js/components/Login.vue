<template>
  <div class="cover text-center">
    <div class="form-container">
      <b-form
        class="form-signin"
        @submit.prevent="handleSubmit"
      >
        <h1 class="h3 mb-3 font-weight-normal">Effettua l'accesso</h1>
        <b-form-input
          type="email"
          placeholder="Indirizzo email"
          required
          autofocus
          v-model="email"
          id="login-email-input"
        ></b-form-input>
        <b-form-input
          type="password"
          placeholder="Password"
          required
          v-model="password"
          id="login-password-input"
        ></b-form-input>
        <b-button
          size="lg"
          variant="primary"
          block
          type="submit"
        >Accedi</b-button>
      </b-form>
    </div>

    <router-link to="/privacy">
      <i class="fas fa-file-alt mr-2"></i> Informativa Privacy
    </router-link>
  </div>
</template>

<script>
export default {
  data() {
    return {
      email: "",
      password: ""
    };
  },
  methods: {
    handleSubmit() {
      const data = { email: this.email, password: this.password };

      // Try to send the fb_device_id along with the credentials
      const fb_device_id = document.head.querySelector(
        'meta[name="registrationid"]'
      ).content;
      if (fb_device_id) data.fb_device_id = fb_device_id;

      this.$auth.login({
        data,
        rememberMe: true,
        success: () => {},
        error: e => {
          this.$notify({
            type: "error",
            text: "Credenziali invalide"
          });
        }
      });
    }
  }
};
</script>

<style scoped>
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
  height: 100%;
  width: 100%;
  flex-direction: column;
}

.form-container {
  flex-grow: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  width: 100%;
  max-width: 330px;
}

.form-signin {
  width: 100%;
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

