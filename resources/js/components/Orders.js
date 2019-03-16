import React from "react";
import { gql } from "apollo-boost";
import { useQuery } from "react-apollo-hooks";

import Order from "./Order";

const GET_ORDERS = gql`
  {
    orders(storeId: 1, count: 10) {
      data {
        id
        number
        delivery_date
        total
        first_name
        last_name
        phone
        address
      }
    }
  }
`;

const Orders = () => {
  const { data, error, loading } = useQuery(GET_ORDERS);

  if (loading) {
    return <div>Loading...</div>;
  }

  if (error) {
    return <div>Error! {error.message}</div>;
  }

  return (
    <div style={{ marginTop: "1em" }}>
      <h2>Orders</h2>
      <div className="columns">
        {data.orders.data.map(order => (
          <div className="column" key={order.id}>
            <Order {...order} />
          </div>
        ))}
      </div>
    </div>
  );
};

export default Orders;
