<template>
  <b-container class="mt-4">
    <h3>Registro Consegne</h3>
    <h5 class="mt-4">Negozio</h5>
    <b-form-radio-group
      buttons
      v-model="selectedStore"
      :options="options"
      button-variant="outline-primary"
    />
    <div class="d-flex justify-content-between flex-wrap mt-4">
      <b-input
        class="mt-2"
        placeholder="Filtro"
        style="max-width: 300px"
        v-model="filter"
      ></b-input>
      <b-pagination
        class="mt-2"
        size="md"
        v-model="currentPage"
        :total-rows="total"
        :per-page="perPage"
      >
      </b-pagination>
    </div>
    <b-table
      stacked="sm"
      hover
      :items="items"
      :fields="fields"
    ></b-table>
  </b-container>
</template>

<script>
const CancelToken = axios.CancelToken;

const date = d =>
  d
    ? moment
        .utc(d)
        .local()
        .format("DD/MM/YYYY HH:mm")
    : null;

const fields = [
  {
    key: "order",
    label: "Ordine"
  },
  {
    key: "time",
    label: "Tempo di consegna",
    formatter: (v, k, i) => {
      if (!i.completed) return "–";

      const out = moment.utc(i["out-for-delivery"].date).local();
      const completed = moment.utc(i.completed.date).local();

      return `${completed.diff(out, "minutes")} minuti`;
    }
  },
  {
    key: "out-for-delivery.date",
    label: "Data in consegna",
    formatter: date,
    tdClass: "statuslog--td-out-for-delivery"
  },
  {
    key: "out-for-delivery.user",
    label: "Manager",
    tdClass: "statuslog--td-out-for-delivery"
  },
  {
    key: "completed.date",
    label: "Data completato",
    formatter: date,
    tdClass: "statuslog--td-completed"
  },
  {
    key: "completed.user",
    label: "Corriere",
    tdClass: "statuslog--td-completed"
  }
];

export default {
  data() {
    return {
      selectedStore: null,
      fields,
      items: [],
      pageCancel: CancelToken.source(),
      currentPage: 1,
      total: 0,
      perPage: 20,
      loading: false,
      filter: ""
    };
  },
  props: ["stores"],
  computed: {
    options() {
      return this.stores.map(({ name, code }) => ({
        text: name,
        value: code
      }));
    }
  },
  mounted() {
    this.selectedStore = this.stores.length > 0 ? this.stores[0].code : null;
  },
  methods: {
    loadPage() {
      if (this.loading) {
        this.pageCancel.cancel("changed page");
        this.pageCancel = CancelToken.source();
      }

      this.loading = true;

      this.$http
        .get(
          `/stores/${this.selectedStore}/statuslog?page=${
            this.currentPage
          }&filter=${this.filter}`,
          {
            cancelToken: this.pageCancel.token
          }
        )
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
    selectedStore: {
      immediate: true,
      handler(store, oldStore) {
        if (store !== oldStore && store !== null) this.loadPage();
      }
    },
    currentPage: {
      handler(page, oldPage) {
        if (page !== oldPage) this.loadPage();
      }
    },
    filter: _.debounce(function(filter, oldFilter) {
      if (filter !== oldFilter) this.loadPage();
    }, 500)
  }
};
</script>

<style>
.statuslog--td-out-for-delivery {
  box-shadow: inset 0 0 0 999px rgba(52, 144, 220, 0.05);
}

.statuslog--td-completed {
  box-shadow: inset 0 0 0 999px rgba(40, 167, 69, 0.05);
}
</style>
