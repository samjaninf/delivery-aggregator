<template>
  <b-container
    class="mt-4"
    fluid
  >
    <h3>Registro Consegne</h3>

    <div class="d-flex justify-content-end flex-wrap mt-4 ">
      <generate-report
        label="Genera report fattorini"
        type="couriers"
      />
      <generate-report
        label="Genera report negozi"
        type="stores"
      />
    </div>

    <h5 class="mt-4">Negozio</h5>
    <b-form-select
      v-model="selectedStore"
      :options="options"
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
      stacked="lg"
      hover
      :items="items"
      :fields="fields"
    ></b-table>
    <div class="d-flex justify-content-end flex-wrap mt-4 ">
      <b-pagination
        class="mt-2"
        size="md"
        v-model="currentPage"
        :total-rows="total"
        :per-page="perPage"
      >
      </b-pagination>
    </div>
    <div
      class="text-center mt-4"
      v-if="loading"
    >
      <div class="lds-ring">
        <div></div>
        <div></div>
        <div></div>
        <div></div>
      </div>
    </div>
  </b-container>
</template>

<script>
import { formatMoney } from "../util/formatMoney";

const CancelToken = axios.CancelToken;

const date = d =>
  d
    ? moment
        .utc(d)
        .local()
        .format("DD/MM/YYYY HH:mm")
    : "—";

const fields = [
  {
    key: "number",
    label: "Ordine"
  },
  {
    key: "order.name",
    label: "Cliente",
    formatter: (v, k, i) =>
      i.order ? `${i.order.first_name} ${i.order.last_name}` : "—"
  },
  {
    key: "timeslot",
    label: "Slot",
    formatter: (v, k, i) => {
      const hour = Vue.filter("hour");
      return i.order
        ? `${hour(i.order.delivery_date)} - ${hour(i.order.delivery_date_end)}`
        : "—";
    }
  },
  {
    key: "out-for-delivery.date",
    label: "Data in consegna",
    formatter: date
  },
  {
    key: "completed.date",
    label: "Data completato",
    formatter: date
  },
  {
    key: "completed.user",
    label: "Corriere",
    formatter: s => s || "—"
  },
  {
    key: "time",
    label: "Tempo di consegna",
    formatter: (v, k, i) => {
      if (!i.completed || !i["out-for-delivery"]) return "—";

      const out = moment.utc(i["out-for-delivery"].date).local();
      const completed = moment.utc(i.completed.date).local();

      return `${completed.diff(out, "minutes")} minuti`;
    }
  },
  {
    key: "order.shipping",
    label: "Spese spedizione",
    formatter: formatMoney
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
          `/stores/${this.selectedStore}/statuslog?page=${this.currentPage}&filter=${this.filter}`,
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
    stores(stores) {
      if (Array.isArray(stores) && stores.length > 0) {
        this.selectedStore = stores[0].code;
      }
    },
    selectedStore: {
      immediate: true,
      handler(store, oldStore) {
        this.items = [];
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
  },
  components: {
    GenerateReport: require("./StatusLog/GenerateReport.vue").default
  }
};
</script>