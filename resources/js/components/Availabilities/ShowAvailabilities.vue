<template>
  <div>
    <h4>Lista disponibilità</h4>
    <v-calendar
      :attributes="calendar"
      :rows="numberOfRows"
      :columns="numberOfRows"
      :is-expanded="true"
      style="border: 1px solid rgba(0, 0, 0, 0.125); border-radius: 0.25rem;"
      locale="it"
      @update:fromPage="fetchAvailabilities"
    >
      <div
        slot="day-popover"
        slot-scope="{ day, dayTitle, attributes, hide }"
      >
        <div class="text-xs text-gray-300 font-semibold text-center">
          {{ dayTitle }}
        </div>
        <v-popover-row
          v-for="attr in attributes"
          :key="attr.key"
          :attribute="attr"
        >
          <span style="font-variant-numeric: tabular-nums">
            {{attr.customData.start.format("HH:mm")}} - {{attr.customData.end.format("HH:mm")}}
          </span>
          <a
            v-if="attr.customData.end.isAfter()"
            class="delete-availability"
            @click="deleteAvailability(attr.customData.id); hide()"
          ><i class="fas fa-fw fa-times"></i></a>
        </v-popover-row>
      </div>
    </v-calendar>
  </div>
</template>

<script>
const CancelToken = axios.CancelToken;

export default {
  data() {
    return {
      data: [],
      pageCancel: null
    };
  },
  computed: {
    calendar() {
      return [
        ...this.data.map(({ id, start, end }) => {
          const startDate = moment(start);
          const endDate = moment(end);

          return {
            key: id,
            dates: startDate.toDate(),
            bar: startDate.hours() > 14 ? "blue" : "orange",
            customData: { id, start: startDate, end: endDate },
            order: startDate.hours(),
            popover: {
              placement: "auto",
              isInteractive: true
            }
          };
        }),
        {
          key: "today",
          dates: new Date(),
          highlight: true
        }
      ];
    },
    numberOfRows() {
      return this.$screens({ default: 1, lg: 2 });
    }
  },
  methods: {
    deleteAvailability(id) {
      // fixme
    },
    fetchAvailabilities({ year, month }) {
      const calendarMonths = this.numberOfRows ** 2;
      const date = moment([year, month - 1]);
      const fromDate = date.clone().add(-calendarMonths, "months");
      const toDate = date.clone().add(calendarMonths * 2, "months");

      const fromDateParam = fromDate.format("YYYY-MM-DD");
      const toDateParam = toDate.format("YYYY-MM-DD");

      if (this.pageCancel) this.pageCancel.cancel("Changed calendar page");
      this.pageCancel = CancelToken.source();

      this.$http
        .get(`availabilities?from=${fromDateParam}&to=${toDateParam}`, {
          cancelToken: this.pageCancel.token
        })
        .then(response => {
          this.data = response.data;
        })
        .catch(e => {
          if (this.$http.isCancel(e)) {
            console.log("Request canceled:", e.message);
          } else {
            this.$notify({
              type: "error",
              text: "Errore di connessione"
            });
            console.error(e);
          }

          return false;
        });
    },
    deleteAvailability(id) {
      this.$http
        .delete(`availabilities/${id}`)
        .then(() => {
          const index = this.data.findIndex(avail => avail.id === id);
          if (index) this.$delete(this.data, index);
          this.$notify({
            type: "success",
            text: "Disponibilità eliminata"
          });
        })
        .catch(e => {
          this.$notify({
            type: "error",
            text: "Errore durante l'eliminazione"
          });
          console.error(e);
        });
    }
  }
};
</script>

<style>
.delete-availability {
  margin-left: 2em;
  cursor: pointer;
  transition: color 0.25s;
}

.delete-availability:hover {
  color: red !important;
}
</style>
