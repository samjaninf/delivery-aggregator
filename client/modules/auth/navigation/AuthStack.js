import { createStackNavigator } from "react-navigation";

import LoginScreen from "../screens/LoginScreen/LoginScreen";

const AuthStack = createStackNavigator({ Login: LoginScreen });
export default AuthStack;
