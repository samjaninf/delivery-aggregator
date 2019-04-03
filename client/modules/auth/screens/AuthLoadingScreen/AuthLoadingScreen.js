import React from "react";
import { ActivityIndicator, AsyncStorage, StatusBar, View } from "react-native";

class AuthLoadingScreen extends React.Component {
  componentDidMount() {
    this.bootstrapAsync();
  }

  // Fetch the token from storage then navigate to our appropriate place
  bootstrapAsync = async () => {
    const {
      props: { navigation }
    } = this;
    const userToken = await AsyncStorage.getItem("userToken");
    navigation.navigate(userToken ? "CourierMain" : "Auth");
  };

  // Render any loading content that you like here
  render() {
    return (
      <View>
        <ActivityIndicator />
        <StatusBar barStyle="default" />
      </View>
    );
  }
}

export default AuthLoadingScreen;
