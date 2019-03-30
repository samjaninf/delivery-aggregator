import React from "react";
import { useQuery } from "react-apollo-hooks";

import Order from "./Order";
import { ORDERS_QUERY } from "../graphql/queries";

const Orders = () => {
  const { data, error, loading } = useQuery(ORDERS_QUERY);

  if (loading) {
    return <div>Loading...</div>;
  }

  if (error) {
    return (
      <div>
        Error!
        {error.message}
      </div>
    );
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
