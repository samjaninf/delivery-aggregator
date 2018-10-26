<template>
  <b-navbar toggleable variant="dark" type="dark">
    <b-container>
      <b-navbar-brand>Delivery Aggregator</b-navbar-brand>
      <b-navbar-toggle target="nav-collapse">
      </b-navbar-toggle>

      <b-collapse is-nav id="nav-collapse">
        <b-navbar-nav class="navbar-nav mr-auto">
          <b-nav-item v-for="store in stores" :key="store.code" :to="`/${store.code}`">
            {{ store.name }}
          </b-nav-item>
        </b-navbar-nav>

        <b-navbar-nav class="ml-auto">
          <b-nav-item-dropdown right>
            <template slot="button-content">
              <i class="fas fa-fw fa-cog"></i>
            </template>

            <template v-if="$auth.check('admin')">
              <b-dropdown-item to="/settings/stores">
                <i class="fas fa-fw fa-store-alt"></i> Negozi
              </b-dropdown-item>
              <b-dropdown-item to="/settings/users">
                <i class="fas fa-fw fa-users"></i> Utenti
              </b-dropdown-item>
              <div class="dropdown-divider"></div>
            </template>
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
  props: ['stores']
}
</script>

