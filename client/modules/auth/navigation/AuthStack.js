import { createStackNavigator } from "react-navigation";

import SignInScreen from "../screens/SignInScreen/SignInScreen";

const AuthStack = createStackNavigator({ SignIn: SignInScreen });
export default AuthStack;
