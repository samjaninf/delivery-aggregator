import React from "react";
import { Platform, StatusBar, StyleSheet, View } from "react-native";
import { AppLoading, Asset, Font, Icon } from "expo";
import { Provider } from "@ant-design/react-native";
import { ApolloProvider } from "react-apollo";

import antoutline from "@ant-design/icons-react-native/fonts/antoutline.ttf";
import AppNavigator from "./modules/common/navigation/AppNavigator";
import client from "./graphql/client";
import theme from "./constants/theme";
import robotDev from "./assets/images/robot-dev.png";
import robotProd from "./assets/images/robot-prod.png";
import spaceMono from "./assets/fonts/SpaceMono-Regular.ttf";

const styles = StyleSheet.create({
  container: {
    flex: 1,
    backgroundColor: "#fff"
  }
});

export default class App extends React.Component {
  state = {
    isLoadingComplete: false
  };

  loadResourcesAsync = async () => {
    return Promise.all([
      Asset.loadAsync([robotDev, robotProd]),
      Font.loadAsync({
        ...Icon.Ionicons.font,
        "space-mono": spaceMono,
        antoutline
      })
    ]);
  };

  handleLoadingError = error => {
    // In this case, you might want to report the error to your error
    // eslint-disable-next-line no-console
    console.warn(error);
  };

  handleFinishLoading = () => {
    this.setState({ isLoadingComplete: true });
  };

  render() {
    const { skipLoadingScreen } = this.props;
    const { isLoadingComplete } = this.state;

    if (!isLoadingComplete && !skipLoadingScreen) {
      return (
        <AppLoading
          startAsync={this.loadResourcesAsync}
          onError={this.handleLoadingError}
          onFinish={this.handleFinishLoading}
        />
      );
    }
    return (
      <ApolloProvider client={client}>
        <Provider theme={theme}>
          <View style={styles.container}>
            {Platform.OS === "ios" && <StatusBar barStyle="default" />}
            <AppNavigator />
          </View>
        </Provider>
      </ApolloProvider>
    );
  }
}
