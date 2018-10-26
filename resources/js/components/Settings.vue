<template>
  <b-container class="mt-4">
    <router-view :stores="stores"></router-view>
  </b-container>
</template>

<script>
export default {
  props: ['stores'],
}
</script>
