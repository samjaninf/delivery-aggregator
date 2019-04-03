import React from "react";
import { View } from "react-native";
import {
  Flex,
  InputItem,
  WingBlank,
  Button,
  NoticeBar
} from "@ant-design/react-native";
import { Icon } from "expo";
import { Mutation } from "react-apollo";

import styles from "./styles";
import LOGIN_MUTATION from "../../graphql/loginMutation";

class LoginScreen extends React.Component {
  static navigationOptions = {
    title: "Login"
  };

  state = {
    email: "admin@prova.it",
    password: "password",
    showPassword: false
  };

  render() {
    const { email, password, showPassword } = this.state;
    const {
      props: { navigation }
    } = this;

    return (
      <Flex
        align="center"
        justify="start"
        direction="column"
        style={styles.container}
      >
        <View style={styles.form}>
          <WingBlank>
            <InputItem
              value={email}
              type="email-address"
              placeholder="Email"
              onChange={value => this.setState({ email: value.toLowerCase() })}
            >
              Email
            </InputItem>
            <InputItem
              value={password}
              type={showPassword ? "text" : "password"}
              placeholder="Password"
              onChange={value => this.setState({ password: value })}
              extra={
                showPassword ? (
                  <Icon.Ionicons name="ios-eye-off" size={20} />
                ) : (
                  <Icon.Ionicons name="ios-eye" size={20} />
                )
              }
              onExtraClick={() =>
                this.setState({ showPassword: !showPassword })
              }
              onBlur={() => this.setState({ showPassword: false })}
            >
              Password
            </InputItem>
            <Mutation mutation={LOGIN_MUTATION}>
              {(login, { loading, error }) => (
                <View>
                  <Button
                    type="primary"
                    style={styles.submitButton}
                    onPress={async () => {
                      try {
                        await login({
                          variables: { email, password }
                        });
                        navigation.navigate("CourierMain");
                      } catch (e) {
                        // invalid login
                      }
                    }}
                    loading={loading}
                  >
                    Accedi
                  </Button>
                  {error && (
                    <NoticeBar style={styles.errorNotice} icon={null}>
                      Errore durante il login
                    </NoticeBar>
                  )}
                </View>
              )}
            </Mutation>
          </WingBlank>
        </View>
      </Flex>
    );
  }
}

export default LoginScreen;
