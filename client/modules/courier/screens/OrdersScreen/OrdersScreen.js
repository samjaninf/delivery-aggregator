import React from "react";
import { ScrollView, View, Text } from "react-native";
import { Query } from "react-apollo";
import { ActivityIndicator, WingBlank, Card } from "@ant-design/react-native";

import styles from "./styles";
import ORDERS_QUERY from "../../graphql/ordersQuery";

export default class OrdersScreen extends React.Component {
  static navigationOptions = {
    title: "Ordini"
  };

  render() {
    return (
      <View style={styles.container}>
        <ScrollView
          style={styles.container}
          contentContainerStyle={styles.contentContainer}
        >
          <WingBlank size="lg" style={{ marginTop: 30 }}>
            <Query query={ORDERS_QUERY} variables={{ id: 1 }}>
              {({ loading, error, data }) => {
                if (loading) return <ActivityIndicator />;
                if (error)
                  return (
                    <Text>
                      Error
                      {JSON.stringify(error)}
                    </Text>
                  );

                return data.orders.data.map(order => (
                  <Card style={{ marginTop: 20 }} key={order.id}>
                    <Card.Header
                      title={`Ordine #${order.id}`}
                      extra={`${(+order.total).toFixed(2)}€`}
                      thumbStyle={{ width: 30, height: 30 }}
                    />
                    <Card.Body>
                      <View style={{ height: 42 }}>
                        <Text style={{ marginLeft: 16 }}>
                          {`${order.firstName} ${order.lastName}`}
                        </Text>
                      </View>
                    </Card.Body>
                    <Card.Footer content="20:30 ~ 21:00" />
                  </Card>
                ));
              }}
            </Query>
          </WingBlank>
        </ScrollView>
      </View>
    );
  }
}
