<template>
  <div>
    <h4>Calendario disponibilità</h4>
    <v-calendar
      :attributes="calendar"
      :is-expanded="true"
      style="border: 1px solid rgba(0, 0, 0, 0.125); border-radius: 0.25rem;"
      locale="it"
      @update:fromPage="$emit('pageChanged', $event)"
      @dayclick="$emit('selectDay', $event.date)"
    >
      <div
        slot="day-popover"
        slot-scope="{ day, dayTitle, attributes, hide }"
      >
        <div class="text-xs text-gray-300 font-semibold text-center">
          {{ dayTitle }}
        </div>

        <!-- Courier popup -->
        <template>
          <v-popover-row
            v-for="attr in attributes"
            :key="attr.key"
            :attribute="attr"
            style="flex-wrap: wrap;"
          >
            <span style="font-variant-numeric: tabular-nums">
              {{attr.customData.start.format("HH:mm")}} - {{attr.customData.end.format("HH:mm")}}
            </span>
            <a
              v-if="attr.customData.end.isAfter()"
              class="delete-availability"
              @click="$emit('deleteAvailability', attr.customData.id); hide()"
              style="margin-left: 2em;"
            ><i class="fas fa-fw fa-times"></i></a>
          </v-popover-row>
        </template>
      </div>
    </v-calendar>
  </div>
</template>

<script>
export default {
  props: ["admin", "calendar"]
};
</script>

<style>
.delete-availability {
  cursor: pointer;
  transition: color 0.25s;
}

.delete-availability:hover {
  color: red !important;
}

.admin-popover {
  align-items: start !important;
}

.admin-popover .vc-day-popover-row-indicator {
  margin-top: 0.65em !important;
}
</style>
