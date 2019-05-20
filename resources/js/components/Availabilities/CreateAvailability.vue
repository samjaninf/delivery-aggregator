<template>
  <div>
    <h4>Comunica disponibilità</h4>

    <b-card class="p-1">
      <b-form @submit.prevent="handleSubmit">
        <b-form-group label="Pasto">
          <b-form-radio-group
            class="meal-selector"
            v-model="meal"
            :options="mealOptions"
            @change="resetStartEnd()"
            buttons
            button-variant="outline-primary"
            size="sm"
          ></b-form-radio-group>
        </b-form-group>

        <b-form-checkbox
          class="mb-2"
          v-model="range"
          name="check-button"
          switch
        >
          Ripeti per periodo
        </b-form-checkbox>

        <b-form-group :label="range ? 'Periodo' : 'Data'">
          <v-date-picker
            :mode="range ? 'range' : 'single'"
            v-model='date'
            locale="it-IT"
            :min-date="minDate"
            :max-date="maxDate"
            :input-props="{
              class: 'form-control',
              style: 'background: white !important;',
              placeholder: `Seleziona ${range ? 'il periodo' : 'la data'}`,
              readonly: true,
            }"
          >
          </v-date-picker>
        </b-form-group>
        <b-form-row>
          <b-col>
            <b-form-group
              label="Inizio"
              :disabled="!meal"
            >
              <b-form-select
                v-model="start"
                :options="startOptions"
              ></b-form-select>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group label="Fine">
              <b-form-select
                v-model="end"
                :options="endOptions"
                :disabled="!meal || !start"
              ></b-form-select>
            </b-form-group>
          </b-col>
        </b-form-row>
        <div class="mt-2">
          <b-button
            type="submit"
            variant="success"
          >
            Salva disponibilità
          </b-button>
        </div>
      </b-form>
    </b-card>
  </div>
</template>

<script>
export default {
  data() {
    return {
      range: false,
      meal: null,
      date: null,
      start: null,
      end: null
    };
  },
  computed: {
    mealOptions() {
      return [
        { text: "Pranzo", value: "lunch" },
        { text: "Cena", value: "dinner" }
      ];
    },
    minDate() {
      return new Date();
    },
    maxDate() {
      return moment()
        .add(1, "months")
        .toDate();
    },
    timeOptions() {
      const padTime = s => s.toString().padStart(2, "0");
      const formatHHMM = x =>
        `${padTime(Math.floor(x / 100))}:${padTime(x % 100)}`;

      switch (this.meal) {
        case "lunch":
          return [1100, 1200, 1300, 1400, 1500].map(x => ({
            text: formatHHMM(x),
            value: x
          }));
        case "dinner":
          return [1800, 1900, 2000, 2100, 2200, 2300].map(x => ({
            text: formatHHMM(x),
            value: x
          }));
        default:
          return [];
      }
    },
    startOptions() {
      return this.timeOptions.slice(0, -1);
    },
    endOptions() {
      return this.timeOptions.filter(({ value }) => value > this.start);
    }
  },
  methods: {
    resetStartEnd() {
      this.start = null;
      this.end = null;
    },
    resetForm() {
      this.range = false;
      this.meal = null;
      this.date = null;
      this.start = null;
      this.end = null;
    }
  }
};
</script>


<style>
.btn-group-toggle.btn-group {
  width: 100%;
}
.btn-group-toggle.btn-group > .btn {
  flex-grow: 1;
  flex-basis: 0;
}
.meal-selector .btn-outline-primary:first-child:not(.disabled):active,
.meal-selector .btn-outline-primary:first-child:hover,
.meal-selector
  .btn-outline-primary:first-child:not(:disabled):not(.disabled).active {
  background-color: #dd6b20;
  border-color: #dd6b20;
  color: white;
}
.meal-selector .btn-outline-primary:first-child {
  color: #dd6b20;
  border-color: #dd6b20;
}
.meal-selector
  .btn-outline-primary:first-child:not(:disabled):not(.disabled).active:focus,
.meal-selector .btn-outline-primary:first-child.focus {
  box-shadow: 0 0 0 0.2rem rgba(221, 107, 32, 0.5);
}
</style>
