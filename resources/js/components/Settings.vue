<template>
  <div class="container mt-4">
    <users-list></users-list>
    <hr>
    <stores-list :stores="stores"></stores-list>
  </div>
</template>

<script>
export default {
  props: ['stores'],
  components: {
    UsersList: require('./UsersList.vue'),
    StoresList: require('./StoresList.vue'),
  } 
}
</script>
