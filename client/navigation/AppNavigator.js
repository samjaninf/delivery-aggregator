import { createAppContainer, createSwitchNavigator } from "react-navigation";

import AuthLoadingScreen from "../screens/AuthLoadingScreen/AuthLoadingScreen";
import MainTabNavigator from "./MainTabNavigator";
import AuthStack from "./AuthStack";

export default createAppContainer(
  createSwitchNavigator({
    // You could add another route here for authentication.
    // Read more at https://reactnavigation.org/docs/en/auth-flow.html
    AuthLoading: AuthLoadingScreen,
    Auth: AuthStack,
    Main: MainTabNavigator
  })
);
