<template>
  <div class="container mt-4">
    <stores-list :stores="stores"></stores-list>
  </div>
</template>

<script>
export default {
  props: ['stores'],
  components: {
    StoresList: require('./StoresList.vue')
  } 
}
</script>
