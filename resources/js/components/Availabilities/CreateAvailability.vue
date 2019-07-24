<template>
  <div>
    <h4>Comunica disponibilità</h4>

    <b-card class="p-1">
      <b-form @submit.prevent="handleSubmit">
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
              class: 'form-control v-calendar-input',
              style: 'background: white !important;',
              placeholder: `Seleziona ${range ? 'il periodo' : 'la data'}`,
              readonly: true,
            }"
          >
          </v-date-picker>
        </b-form-group>
        <b-form-row>
          <b-col>
            <b-form-group label="Inizio">
              <b-form-select
                v-model="start"
                :options="startOptions"
                :disabled="date === null"
              ></b-form-select>
            </b-form-group>
          </b-col>
          <b-col>
            <b-form-group label="Fine">
              <b-form-select
                v-model="end"
                :options="endOptions"
                :disabled="start === null"
              ></b-form-select>
            </b-form-group>
          </b-col>
        </b-form-row>
        <div class="mt-2">
          <b-button
            type="submit"
            variant="success"
            :disabled="date === null || start === null || end === null"
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
      date: null,
      start: null,
      end: null
    };
  },
  computed: {
    minDate() {
      return moment().toDate();
    },
    maxDate() {
      return moment()
        .add(1, "months")
        .toDate();
    },
    timeOptions() {
      // Used to check valid time options
      const dateStart = moment(this.range ? this.date.start : this.date);
      const now = moment();

      // Helper functions to format the time options properly
      const padTime = s => s.toString().padStart(2, "0");
      const formatHHMM = x =>
        `${padTime(Math.floor(x / 100))}:${padTime(x % 100)}`;

      return Array.from({ length: 24 }, (x, i) => i * 100)
        .filter(
          // Hide starting times in the past
          x =>
            dateStart
              .hours(x / 100)
              .minutes(x % 100)
              .diff(now) > 0
        )
        .map(x => ({
          text: formatHHMM(x),
          value: x
        }));
    },
    startOptions() {
      return this.timeOptions;
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
      this.date = null;
      this.start = null;
      this.end = null;
    },
    handleSubmit() {
      const { range, date, start, end } = this;
      const dateStart = moment(range ? date.start : date);
      const dateEnd = moment(range ? date.end : date);

      const body = {
        startDate: dateStart.format(),
        endDate: dateEnd.format(),
        startTime: start,
        endTime: end
      };

      this.$http
        .post("availabilities", body)
        .then(response => {
          this.$notify({
            type: "success",
            text: "Disponibilità salvata"
          });
          this.resetForm();

          const availabilities = response.data;
          this.$emit("createdAvailabilities", availabilities);
        })
        .catch(e => {
          this.$notify({
            type: "error",
            text: "Errore durante il salvataggio"
          });
        });
    }
  },
  watch: {
    timeOptions(options) {
      if (options.length >= 2) {
        this.start = options[0].value;
        this.end = options[options.length - 1].value;
      } else {
        this.resetStartEnd();
      }
    }
  }
};
</script>

<style>
.v-calendar-input {
  border: 1px solid #ced4da;
}
</style>
