<template>
  <b-container class="mt-4">
    <h3>Registro Consegne</h3>
    <div class="d-flex flex-row-reverse mt-4">
      <b-pagination
        size="md"
        v-model="currentPage"
        :total-rows="total"
        :per-page="perPage"
      >
      </b-pagination>
    </div>
    <b-table
      stacked="sm"
      striped
      hover
      :items="items"
      :fields="fields"
    ></b-table>
  </b-container>
</template>

<script>
const CancelToken = axios.CancelToken;

const statusTranslations = {
  "out-for-delivery": "In consegna"
};

const fields = [
  {
    key: "storeorder",
    label: "Ordine",
    formatter: (v, k, i) => `${i.store.name}/${i.order}`
  },
  {
    key: "status",
    label: "Stato",
    formatter: s => statusTranslations[s] || s
  },
  {
    key: "updated_at",
    label: "Data",
    formatter: d => moment(d).format("DD/MM/YYYY HH:mm")
  },
  {
    key: "user.name",
    label: "Utente"
  }
];

export default {
  data() {
    return {
      fields,
      items: [],
      pageCancel: CancelToken.source(),
      currentPage: 1,
      total: 0,
      perPage: 20,
      loading: false
    };
  },
  methods: {
    loadPage() {
      if (this.loading) {
        this.pageCancel.cancel("changed page");
        this.pageCancel = CancelToken.source();
      }

      this.loading = true;

      this.$http
        .get(`status_changes?page=${this.currentPage}`, {
          cancelToken: this.pageCancel.token
        })
        .then(response => {
          const { data, total, per_page } = response.data;
          this.items = data;
          this.total = total;
          this.perPage = per_page;
          this.loading = false;
        });
    }
  },
  watch: {
    currentPage: {
      immediate: true,
      handler(page, oldPage) {
        if (page !== oldPage) this.loadPage();
      }
    }
  }
};
</script>