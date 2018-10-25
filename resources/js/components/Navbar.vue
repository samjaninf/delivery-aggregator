<template>
  <nav class="navbar navbar-expand-md navbar-dark bg-dark" v-if="$auth.check()">
    <div class="container">
      <a class="navbar-brand" href="#">Delivery Aggregator</a>
      <button class="navbar-toggler" type="button" data-toggle="collapse">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse">
        <ul class="navbar-nav mr-auto">
          <li class="nav-item" v-for="store in stores" :key="store.code" :class="{ active: $route.params.storeCode == store.code }">
            <router-link :to="`/${store.code}`" class="nav-link">
              {{ store.name }}
            </router-link>
          </li>
        </ul>
        <ul class="navbar-nav">
          <li class="nav-item dropdown" :class="{ active: $route.path.startsWith('/settings')}">
            <a class="nav-link dropdown-toggle" href="#" role="button" data-toggle="dropdown">
              <i class="fas fa-fw fa-cog"></i>
            </a>

            <div class="dropdown-menu" aria-labelledby="navbarDropdownMenuLink">
              <template>
                <router-link to="/settings/stores" class="dropdown-item">
                  <i class="fas fa-fw fa-store-alt"></i> Negozi
                </router-link>
                <router-link to="/settings/users" class="dropdown-item">
                  <i class="fas fa-fw fa-users"></i> Utenti
                </router-link>
                <div class="dropdown-divider"></div>
              </template>
              <a href="#" class="dropdown-item" @click.prevent="$auth.logout()">Esci</a>
            </div>
          </li>
          <li class="nav-item ">

          </li>
        </ul>
      </div>
    </div>
  </nav>
</template>

<script>
export default {
  props: ['stores']
}
</script>

