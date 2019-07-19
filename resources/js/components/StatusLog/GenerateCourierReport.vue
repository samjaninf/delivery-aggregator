<template>
  <b-button
    variant="primary"
    :disabled="loading"
    @click="startReport"
  >
    <i
      class="fas fa-fw fa-cog fa-spin"
      v-if="loading"
    ></i>
    <i
      class="fas fa-fw fa-clipboard-list"
      v-else
    ></i>
    <a ref="anchor"></a>
    Scarica report fattorini</b-button>
</template>

<script>
export default {
  data() {
    return {
      loading: false
    };
  },
  methods: {
    async startReport() {
      if (this.loading) return;

      const lastMonth = moment().subtract(1, "months");
      const url = `reports/${lastMonth.year()}/${lastMonth.month() + 1}`;

      this.loading = true;

      // Fetch file as blob
      const response = await this.$http.get(url, { responseType: "blob" });
      const blob = response.data;
      this.loading = false;

      // Download the file
      const monthPadded = `${lastMonth.month() + 1}`.padStart(2, "0");
      const objectUrl = window.URL.createObjectURL(blob);
      this.$refs.anchor.href = objectUrl;
      this.$refs.anchor.download = `report-${lastMonth.year()}-${monthPadded}.xlsx`;
      this.$refs.anchor.click();

      // Clear the allocated ObjectURL
      setTimeout(() => window.URL.revokeObjectURL(objectUrl), 1000);
    }
  }
};
</script>
