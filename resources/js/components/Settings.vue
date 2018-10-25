<template>
  <div class="container mt-4">
    <router-view :stores="stores"></router-view>
  </div>
</template>

<script>
export default {
  props: ['stores'],
}
</script>
