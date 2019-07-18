<template>
  <div>

    <template v-if="isDesktop">
      <navbar :desktop="isDesktop"></navbar>
      <div class="desktop-layout">
        <sidebar :stores="stores" />
        <slot></slot>
      </div>
    </template>

    <template v-else>
      <navbar :stores="stores"></navbar>
      <slot></slot>
    </template>

  </div>
</template>

<script>
export default {
  props: ["stores"],
  components: {
    Navbar: require("./Layout/Navbar.vue").default,
    Sidebar: require("./Layout/Sidebar.vue").default
  },
  computed: {
    isDesktop() {
      return this.$screens({ default: false, md: true });
    }
  }
};
</script>

<style scoped>
.desktop-layout {
  display: flex;
  flex-direction: row;
}
</style>


