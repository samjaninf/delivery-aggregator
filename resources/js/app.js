/**
 * First we will load all of this project's JavaScript dependencies which
 * includes Vue and other libraries. It is a great starting point when
 * building robust, powerful web applications using Vue and Laravel.
 */

require("./bootstrap");

window.Vue = require("vue");
Vue.use(require("vue-infinite-loading"));
Vue.use(require("vue-axios"), axios);

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
        path: "/login",
        component: require("./components/Login.vue"),
        meta: {
            auth: false
        }
    },
    {
        path: "/",
        meta: {
            auth: true
        }
    },
    {
        path: "/settings",
        props: true,
        component: require("./components/Settings.vue"),
        children: [
            {
                path: "stores",
                component: require("./components/SettingsStores.vue")
            },
            {
                path: "users",
                component: require("./components/SettingsUsers.vue")
            }
        ],
        meta: {
            auth: true
        }
    },
    {
        path: "/:storeCode",
        props: true,
        component: require("./components/OrdersList.vue"),
        meta: {
            auth: true
        }
    }
];

const router = new VueRouter({
    mode: "history",
    routes
});

Vue.router = router;

// Auth

Vue.use(require("@websanova/vue-auth"), {
    auth: require("@websanova/vue-auth/drivers/auth/bearer.js"),
    http: require("@websanova/vue-auth/drivers/http/axios.1.x.js"),
    router: require("@websanova/vue-auth/drivers/router/vue-router.2.x.js"),
    loginData: { url: "/auth/login" }
});

// Global filters
Vue.filter("hour", v =>
    moment
        .unix(v)
        .utc()
        .format("H:mm")
);
Vue.filter("money", v => `€${(+v).toFixed(2)}`);

// Axios
axios.defaults.baseURL = "/api";

const app = new Vue({
    el: "#app",
    template: "<App/>",
    components: { App },
    router
});
