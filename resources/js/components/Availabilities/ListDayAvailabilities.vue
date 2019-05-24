<template>
  <div>
    <h4>{{ dayHuman }}</h4>
    <b-card no-body>
      <b-list-group
        flush
        v-if="Array.isArray(availabilities) && availabilities.length > 0"
      >
        <b-list-group-item
          v-for="({group, time}) in availabilitiesByTime"
          :key="time"
          :style="{ borderLeft: `5px solid ${parseInt(time) > 14 ? 'var(--primary)' : '#dd6b20'}`}"
        >
          <h5>
            {{ time }}
          </h5>
          <ul style="list-style-type: none; padding-left: 1em; margin-bottom: 0;">
            <li
              v-for="({id, user_name, end}) in group"
              :key="id"
            >
              <a
                v-if="isFuture(end)"
                class="delete-availability"
                @click="$emit('deleteAvailability', id)"
                style="margin-right: 0.5em;"
              ><i class="fas fa-fw fa-trash"></i></a>
              <span>{{ user_name }}</span>
            </li>
          </ul>
        </b-list-group-item>
      </b-list-group>
      <em
        v-else
        style="margin: 1em; text-align: center; padding: 0;"
      >
        Nessuna disponibilità
      </em>
    </b-card>
  </div>
</template>

<script>
export default {
  props: ["day", "availabilities"],
  computed: {
    dayHuman() {
      return moment(this.day).format("dddd LL");
    },
    availabilitiesByTime() {
      return _(this.availabilities)
        .groupBy(
          a =>
            `${moment(a.start).format("HH:mm")} - ${moment(a.end).format(
              "HH:mm"
            )}`
        )
        .map((group, time) => ({
          time,
          group
        }))
        .sortBy("time")
        .value();
    }
  },
  methods: {
    isFuture(datetime) {
      return moment(datetime).isAfter();
    }
  }
};
</script>
