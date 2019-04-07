import React from "react";
import { Platform } from "react-native";
import {
  createStackNavigator,
  createBottomTabNavigator
} from "react-navigation";

import TabBarIcon from "../../../components/TabBarIcon";
import MapScreen from "../screens/MapScreen/MapScreen";
import OrdersScreen from "../screens/OrdersScreen/OrdersScreen";
import SettingsScreen from "../screens/SettingsScreen/SettingsScreen";

const HomeStack = createStackNavigator({
  Home: MapScreen
});

HomeStack.navigationOptions = {
  tabBarLabel: "Mappa",
  tabBarIcon: ({ focused }) => (
    <TabBarIcon
      focused={focused}
      name={Platform.OS === "ios" ? `ios-navigate` : "md-navigate"}
    />
  )
};

const LinksStack = createStackNavigator({
  Links: OrdersScreen
});

LinksStack.navigationOptions = {
  tabBarLabel: "Ordini",
  tabBarIcon: ({ focused }) => (
    <TabBarIcon
      focused={focused}
      name={Platform.OS === "ios" ? "ios-basket" : "md-basket"}
    />
  )
};

const SettingsStack = createStackNavigator({
  Settings: SettingsScreen
});

SettingsStack.navigationOptions = {
  tabBarLabel: "Settings",
  tabBarIcon: ({ focused }) => (
    <TabBarIcon
      focused={focused}
      name={Platform.OS === "ios" ? "ios-options" : "md-options"}
    />
  )
};

export default createBottomTabNavigator({
  HomeStack,
  LinksStack,
  SettingsStack
});
