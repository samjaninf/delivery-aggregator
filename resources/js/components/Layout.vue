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
      <div class="content">
        <div>
          <slot></slot>
        </div>
      </div>
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
  z-index: 0;
}

.content {
  width: 100%;
  height: auto;
  max-height: unset;
  overflow-y: auto;
}

@media only screen and (max-width: 768px) {
  .content {
    overflow-x: hidden;
    overflow-y: scroll;
    -webkit-overflow-scrolling: touch;
    padding-bottom: 120px;
  }
}
</style>


