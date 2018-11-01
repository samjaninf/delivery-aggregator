module.exports = [
    {
        path: "/login",
        component: require("./components/Login.vue"),
        meta: {
            auth: false
        }
    },
    {
        path: "/privacy",
        component: require("./components/PrivacyPolicy.vue"),
        meta: {
            auth: null
        }
    },
    {
        path: "/",
        component: require("./components/Home.vue"),
        meta: {
            auth: true
        },
        children: [
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
                    auth: "admin"
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
        ]
    }
];
