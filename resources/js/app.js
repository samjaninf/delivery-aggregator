/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require("./bootstrap");

window.Vue = require("vue");
Vue.use(require("bootstrap-vue"));
Vue.use(require("vue-axios"), axios);
Vue.use(require("vue-notification").default);

const VueRouter = require("vue-router").default;
Vue.use(VueRouter);

import VCalendar from "v-calendar";
// Use v-calendar, v-date-picker & v-popover components
Vue.use(VCalendar, {
  firstDayOfWeek: 2,
  locale: "it",
  datePickerTintColor: "#3490dc",
  datePickerShowDayPopover: false,
  datePickerShowCaps: true
});

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const App = require("./components/App.vue").default;

const router = new VueRouter({
  mode: "history",
  routes: require("./routes.js")
});

Vue.router = router;

// Auth

Vue.use(require("@websanova/vue-auth"), {
  auth: require("@websanova/vue-auth/drivers/auth/bearer.js"),
  http: require("@websanova/vue-auth/drivers/http/axios.1.x.js"),
  router: require("@websanova/vue-auth/drivers/router/vue-router.2.x.js"),
  tokenStore: ["cookie", "localStorage"],
  rolesVar: "abilities"
});

// Global filters
const { formatTime } = require("./util/formatTime");
const { formatMoney } = require("./util/formatMoney");

Vue.filter("hour", formatTime);
Vue.filter("money", formatMoney);

// Axios
axios.defaults.baseURL = "/api";

const app = new Vue({
  el: "#app",
  template: "<App/>",
  components: { App },
  router
});
