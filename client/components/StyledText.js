import React from "react";
import { Text } from "react-native";

// eslint-disable-next-line import/prefer-default-export
export const MonoText = ({ style, ...props }) => (
  <Text {...props} style={[style, { fontFamily: "space-mono" }]} />
);
