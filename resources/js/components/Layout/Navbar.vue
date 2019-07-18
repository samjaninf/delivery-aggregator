<template>
  <b-navbar
    toggleable="md"
    variant="dark"
    type="dark"
    class="navbar"
  >
    <b-container :style="{paddingLeft: desktop ? '250px' : 0}">
      <b-navbar-brand>Delivery Aggregator</b-navbar-brand>
      <b-navbar-toggle target="nav-collapse">
      </b-navbar-toggle>

      <b-collapse
        is-nav
        id="nav-collapse"
      >
        <b-navbar-nav
          class="navbar-nav mr-auto"
          v-if="!desktop"
        >

          <b-nav-item
            v-for="store in stores"
            :key="store.code"
            :to="`/${store.code}`"
          >
            {{ store.name }}
          </b-nav-item>
        </b-navbar-nav>

        <b-navbar-nav class="ml-auto">
          <b-nav-item-dropdown right>
            <template slot="button-content">
              <i class="fas fa-fw fa-cog"></i>
            </template>

            <template v-if="$auth.check(['manage own availabilities', 'admin'])">
              <b-dropdown-item to="/availabilities">
                <i class="fas fa-fw fa-business-time"></i> Disponibilità
              </b-dropdown-item>
              <div class="dropdown-divider"></div>
            </template>

            <b-dropdown-item
              to="/deliveryslots"
              v-if="$auth.check(['manage delivery slots', 'admin'])"
            >
              <i class="fas fa-fw fa-clock"></i> Fasce orarie
            </b-dropdown-item>

            <template v-if="$auth.check(['manage products', 'admin'])">
              <b-dropdown-item to="/managestore">
                <i class="fas fa-fw fa-box"></i> Gestione negozio
              </b-dropdown-item>
              <div class="dropdown-divider"></div>
            </template>

            <b-dropdown-item
              to="/statuslog"
              v-if="$auth.check(['view status log', 'admin'])"
            >
              <i class="fas fa-fw fa-history"></i> Registro consegne
            </b-dropdown-item>

            <template v-if="$auth.check(['manage settings', 'admin'])">
              <b-dropdown-item to="/settings/stores">
                <i class="fas fa-fw fa-store-alt"></i> Negozi
              </b-dropdown-item>
              <b-dropdown-item to="/settings/users">
                <i class="fas fa-fw fa-users"></i> Utenti
              </b-dropdown-item>
              <div class="dropdown-divider"></div>
            </template>
            <b-dropdown-item to="/privacy">
              Informativa Privacy
            </b-dropdown-item>
            <b-dropdown-item @click.prevent="$auth.logout()">
              Esci
            </b-dropdown-item>
          </b-nav-item-dropdown>
        </b-navbar-nav>
      </b-collapse>
    </b-container>
  </b-navbar>
</template>

<script>
export default {
  props: ["stores", "desktop"]
};
</script>

<style scoped>
.navbar {
  z-index: 1;
}
</style>
