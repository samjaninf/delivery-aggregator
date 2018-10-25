/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require("./bootstrap");

window.Vue = require("vue");
Vue.use(require("vue-infinite-loading"));

const VueRouter = require("vue-router").default;
Vue.use(VueRouter);

/**
 * Next, we will create a fresh Vue application instance and attach it to
 * the page. Then, you may begin adding components to this application
 * or customize the JavaScript scaffolding to fit your unique needs.
 */

const App = require("./components/App.vue");

// Routes
const routes = [
    {
        path: "/settings",
        props: true,
        component: require("./components/Settings.vue")
    },
    {
        path: "/:storeCode",
        props: true,
        component: require("./components/OrdersList.vue")
    }
];

const router = new VueRouter({
    mode: "history",
    routes
});

// Global filters
Vue.filter("hour", v =>
    moment
        .unix(v)
        .utc()
        .format("H:mm")
);
Vue.filter("money", v => `€${(+v).toFixed(2)}`);

const app = new Vue({
    el: "#app",
    template: "<App/>",
    components: { App },
    router
});
