import { createAppContainer, createSwitchNavigator } from "react-navigation";

import AuthLoadingScreen from "../../auth/screens/AuthLoadingScreen/AuthLoadingScreen";
import CourierTabNavigator from "../../courier/navigation/TabNavigator";
import AuthStack from "../../auth/navigation/AuthStack";

export default createAppContainer(
  createSwitchNavigator({
    // You could add another route here for authentication.
    // Read more at https://reactnavigation.org/docs/en/auth-flow.html
    AuthLoading: AuthLoadingScreen,
    Auth: AuthStack,
    CourierMain: CourierTabNavigator
  })
);
