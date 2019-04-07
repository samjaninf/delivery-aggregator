import React, { Component } from "react";
import { Platform, Constants } from "react-native";
import { MapView, Permissions, Location, Icon } from "expo";

import BubbleMarker from "./BubbleMarker";

const { Marker } = MapView;

export default class CourierMapView extends Component {
  state = {
    userLocation: null,
    markers: [
      {
        latitude: 43.537505,
        longitude: 10.331681
      }
    ]
  };

  componentWillMount = () => {
    if (Platform.OS !== "android" || Constants.isDevice) {
      this.getLocationAsync();
    }
  };

  getLocationAsync = async () => {
    const { status } = await Permissions.askAsync(Permissions.LOCATION);
    if (status !== "granted") {
      return;
    }

    const userLocation = await Location.getCurrentPositionAsync({});
    this.setState({ userLocation });
  };

  render() {
    const { userLocation, markers } = this.state;

    return (
      <MapView
        style={{ flex: 1 }}
        ref={ref => {
          this.map = ref;
        }}
      >
        {[userLocation && userLocation.coords, ...markers]
          .filter(pos => pos && pos.latitude && pos.longitude)
          .map((pos, i) => (
            <Marker
              key={`${pos.latitude}-${pos.longitude}`}
              coordinate={pos}
              identifier={i === 0 ? "user" : `${i}`}
            >
              <BubbleMarker>
                <Icon.Ionicons
                  name={i === 0 ? "ios-bicycle" : "ios-home"}
                  color="white"
                  size={20}
                />
              </BubbleMarker>
            </Marker>
          ))}
      </MapView>
    );
  }
}
