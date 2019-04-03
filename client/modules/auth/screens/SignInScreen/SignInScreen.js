import React from "react";
import { AsyncStorage, View, Button } from "react-native";

class SignInScreen extends React.Component {
  static navigationOptions = {
    title: "Please sign in"
  };

  signInAsync = async () => {
    await AsyncStorage.setItem("userToken", "abc");
    const {
      props: { navigation }
    } = this;

    navigation.navigate("Main");
  };

  render() {
    return (
      <View>
        <Button title="Sign in!" onPress={this.signInAsync} />
      </View>
    );
  }
}

export default SignInScreen;
