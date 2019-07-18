<template>
  <div>
    <navbar
      :desktop="isDesktop"
      :stores="stores"
    ></navbar>
    <div class="layout">
      <sidebar
        v-if="isDesktop"
        :stores="stores"
      />
      <slot></slot>
    </div>
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
.layout {
  display: flex;
  flex-direction: row;
  height: calc(100vh - 58px);
}
</style>


