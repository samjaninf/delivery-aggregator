import React from "react";
import { View } from "react-native";

import styles from "./styles";
import CourierMapView from "../../../../components/CourierMapView";

export default class MapScreen extends React.Component {
  static navigationOptions = {
    header: null
  };

  render() {
    return (
      <View style={styles.container}>
        <CourierMapView style={{ flex: 1 }} />
      </View>
    );
  }
}
