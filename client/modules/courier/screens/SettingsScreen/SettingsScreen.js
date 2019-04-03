import React from "react";
import { Button, WingBlank } from "@ant-design/react-native";

// eslint-disable-next-line import/no-unresolved
const RCTNetworking = require("RCTNetworking"); // not sure if it works on android

export default class SettingsScreen extends React.Component {
  static navigationOptions = {
    title: "app.json"
  };

  logout = () => {
    const { navigation } = this.props;
    RCTNetworking.clearCookies(() => {});
    navigation.navigate("Auth");
  };

  render() {
    /* Go ahead and delete ExpoConfigView and replace it with your
     * content, we just wanted to give you a quick view of your config */
    return (
      <WingBlank>
        <Button onPress={this.logout} type="warning">
          Logout
        </Button>
      </WingBlank>
    );
  }
}
