import React from "react";
import { ActivityIndicator, StatusBar, View } from "react-native";
import { withApollo } from "react-apollo";

import ME_QUERY from "../../graphql/meQuery";

class AuthLoadingScreen extends React.Component {
  async componentDidMount() {
    const { client, navigation } = this.props;
    const data = await client.query({
      query: ME_QUERY
    });
    if (data && data.data && data.data.me) {
      // already logged in
      navigation.navigate("CourierMain");
    } else {
      // need to relog
      navigation.navigate("Auth");
    }
  }

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

export default withApollo(AuthLoadingScreen);
