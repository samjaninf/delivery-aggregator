<template>
  <div>
    <h4>Lista disponibilità</h4>
    <v-calendar
      :attributes="calendar"
      :columns="$screens({ default: 1, lg: 2 })"
      :rows="$screens({ default: 1, lg: 2 })"
      :is-expanded="true"
      style="border: 1px solid rgba(0, 0, 0, 0.125); border-radius: 0.25rem;"
      locale="it"
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
            class="delete-availability"
            @click="deleteAvailability(attr.customData.id); hide()"
          ><i class="fas fa-fw fa-times"></i></a>
        </v-popover-row>
      </div>
    </v-calendar>
  </div>
</template>

<script>
// Temp code
const gen = () => {
  const date = [2019, 4, Math.floor(Math.random() * 31) + 1];
  const start = Math.floor(Math.random() * 10) + 10;
  const end = Math.floor(Math.random() * 3) + start + 1;
  return {
    id: Math.floor(Math.random() * 100000).toString(),
    start: moment([...date, start]),
    end: moment([...date, end])
  };
};
export default {
  data() {
    return {
      data: [...Array(30)].map(_ => gen())
    };
  },
  computed: {
    calendar() {
      return this.data.map(({ id, start, end }) => ({
        key: id,
        dates: start.toDate(),
        bar: start.hours() > 14 ? "blue" : "orange",
        customData: { id, start, end },
        order: start.hours(),
        popover: {
          placement: "auto",
          isInteractive: true
        }
      }));
    }
  },
  methods: {
    deleteAvailability(id) {
      // fixme
      const index = this.data.findIndex(avail => avail.id === id);
      if (index) this.$delete(this.data, index);
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
