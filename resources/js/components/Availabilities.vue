<template>
  <b-container class="mt-4">
    <h3 style="margin-bottom: 1em;">Disponibilità</h3>

    <b-row>
      <b-col
        cols="12"
        lg="8"
        class="mb-4"
      >
        <ShowAvailabilities />
      </b-col>
      <b-col
        cols="12"
        lg="4"
        class="mb-4"
      >
        <CreateAvailability />
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
export default {
  components: {
    CreateAvailability: require("./Availabilities/CreateAvailability.vue")
      .default,
    ShowAvailabilities: require("./Availabilities/ShowAvailabilities.vue")
      .default
  }
};
</script>