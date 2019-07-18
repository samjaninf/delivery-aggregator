<template>
  <div class="stores-sidebar">
    <h4>Lista negozi</h4>
    <b-list-group>
      <b-list-group-item
        v-for="store in stores"
        :key="store.code"
        :to="`/${store.code}`"
        active-class="active"
      >
        {{ store.name }}
      </b-list-group-item>
    </b-list-group>
  </div>
</template>

<script>
export default {
  props: ["stores"]
};
</script>

<style scoped>
.stores-sidebar h4 {
  margin: 0.8em 1.3em;
}

.stores-sidebar {
  width: 250px;
  position: sticky;
  top: 0;
  height: calc(100vh - 58px);
  background: white;
  border-right: 1px solid rgba(0, 0, 0, 0.125);
  flex-shrink: 0;
}

.stores-sidebar .list-group-item {
  border-radius: 0;
  border-left: 0;
  border-right: 0;
  padding-left: 2em;
}
</style>


