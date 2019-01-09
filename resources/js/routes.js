module.exports = [
    {
        path: "/login",
        component: require("./components/Login.vue").default,
        meta: {
            auth: false
        }
    },
    {
        path: "/privacy",
        component: require("./components/PrivacyPolicy.vue").default,
        meta: {
            auth: null
        }
    },
    {
        path: "/",
        component: require("./components/Home.vue").default,
        meta: {
            auth: true
        },
        children: [
            {
                path: "/settings",
                props: true,
                component: require("./components/Settings.vue").default,
                children: [
                    {
                        path: "stores",
                        component: require("./components/SettingsStores.vue").default
                    },
                    {
                        path: "users",
                        component: require("./components/SettingsUsers.vue").default
                    }
                ],
                meta: {
                    auth: "admin"
                }
            },
            {
                path: "/statuslog",
                component: require("./components/StatusLog.vue").default,
                meta: {
                    auth: "admin"
                }
            },
            {
                path: "/products",
                props: true,
                component: require("./components/Products.vue").default,
                meta: {
                    auth: "manager"
                }
            },
            {
                path: "/:storeCode",
                props: true,
                component: require("./components/OrdersList.vue").default,
                meta: {
                    auth: true
                }
            }
        ]
    }
];
