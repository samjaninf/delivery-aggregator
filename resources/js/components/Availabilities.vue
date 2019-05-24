<template>
  <b-container class="mt-4">
    <h3 style="margin-bottom: 1em;">Disponibilità</h3>

    <b-row>
      <b-col
        cols="12"
        :lg="8"
        class="mb-4"
      >
        <ShowAvailabilities
          :admin="admin"
          :calendar="admin ? adminCalendar : calendar"
          @pageChanged="fetchAvailabilities"
          @deleteAvailability="deleteAvailability"
          @selectDay="selectedDay = $event"
        />
      </b-col>
      <b-col
        cols="12"
        lg="4"
        class="mb-4"
      >
        <template v-if="admin">
          <ListDayAvailabilities
            v-if="selectedDay"
            :day="selectedDay"
            :availabilities="availabilitiesInSelectedDay"
            @deleteAvailability="deleteAvailability"
          />
        </template>
        <CreateAvailability
          v-else
          @createdAvailabilities="createdAvailabilities"
        />
      </b-col>
    </b-row>
  </b-container>
</template>

<script>
const CancelToken = axios.CancelToken;

export default {
  data() {
    return {
      data: [],
      pageCancel: null,
      selectedDay: null,
      fromDate: null,
      toDate: null
    };
  },
  computed: {
    admin() {
      return this.$auth.check(["admin"]);
    },
    calendar() {
      return [
        ...this.dataFiltered.map(({ id, start, end }) => {
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
    dataFiltered() {
      return this.data.filter(a => {
        return (
          moment(a.start).isAfter(this.fromDate) &&
          moment(a.end).isBefore(this.toDate)
        );
      });
    },
    availabilitiesByDay() {
      return _(this.dataFiltered)
        .groupBy(a => moment(a.start).format("YYYY-MM-DD"))
        .value();
    },
    allDates() {
      if (
        !this.fromDate ||
        !this.toDate ||
        this.availabilitiesByDay.length == 0
      )
        return {};

      const all = {};
      for (
        const d = this.fromDate.clone();
        d.isBefore(this.toDate);
        d.add(1, "day")
      ) {
        all[d.format("YYYY-MM-DD")] = [];
      }

      return all;
    },
    adminCalendar() {
      return [
        ..._({
          ...(this.data.length > 0 ? this.allDates : []),
          ...this.availabilitiesByDay
        })
          .map((avails, key) => {
            const startDate =
              avails.length > 0 ? moment(avails[0].start) : moment(key);
            const endDate =
              avails.length > 0 ? moment(avails[0].end) : moment(key);

            const tresholds = [3, 2, 0];
            const colorIndex = tresholds.findIndex(t => avails.length >= t);
            const color = ["green", "yellow", "red"][colorIndex];

            return {
              key,
              dates: startDate.toDate(),
              highlight: color,
              customData: {
                key,
                start: startDate,
                end: endDate,
                availabilities: avails.map(a => ({
                  user: a.user_name,
                  id: a.id
                }))
              },
              order: startDate.hours()
            };
          })
          .value(),
        {
          key: "today",
          dates: new Date(),
          dot: true,
          order: 100
        },
        {
          key: "selected",
          dates: new Date(this.selectedDay),
          highlight: true,
          order: 100
        }
      ];
    },
    availabilitiesInSelectedDay() {
      if (!this.selectedDay) return [];
      const day = moment(this.selectedDay).format("YYYY-MM-DD");
      return this.availabilitiesByDay[day] || [];
    }
  },
  methods: {
    fetchAvailabilities({ year, month }) {
      const date = moment([year, month - 1]);
      this.fromDate = date.clone();
      this.toDate = date.clone().add(1, "month");

      const fromDateEx = this.fromDate.clone().add(-1, "months");
      const toDateEx = this.toDate.clone().add(1, "months");

      const fromDateParam = fromDateEx.format("YYYY-MM-DD");
      const toDateParam = toDateEx.format("YYYY-MM-DD");

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
    },
    createdAvailabilities(availabilities) {
      this.data = [...this.data, ...availabilities];
    }
  },
  components: {
    CreateAvailability: require("./Availabilities/CreateAvailability.vue")
      .default,
    ShowAvailabilities: require("./Availabilities/ShowAvailabilities.vue")
      .default,
    ListDayAvailabilities: require("./Availabilities/ListDayAvailabilities.vue")
      .default
  }
};
</script>