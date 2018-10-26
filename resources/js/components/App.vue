<template>
  <div class="app-container">
    <notifications />
    <router-view></router-view>
  </div>
</template>

<style>
.app-container {
  height: 100%;
  width: 100%;
}

.vue-notification {
  margin-right: 1em;
  margin-top: 2em;
  padding: 1em;
  font-size: 1em;
}
</style>
