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
          auth: ["manage settings", "admin"]
        }
      },
      {
        path: "/statuslog",
        component: require("./components/StatusLog.vue").default,
        meta: {
          auth: ["view status log", "admin"]
        }
      },
      {
        path: "/managestore",
        props: true,
        component: require("./components/ManageStore.vue").default,
        meta: {
          auth: ["manage products", "admin"]
        }
      },
      {
        path: "/deliveryslots",
        props: true,
        component: require("./components/DeliverySlots.vue").default,
        meta: {
          auth: ["manage delivery slots", "admin"]
        }
      },
      {
        path: "/:storeCode",
        props: true,
        component: require("./components/OrdersList.vue").default,
        meta: {
          auth: ["view orders", "admin"]
        }
      }
    ]
  }
];
