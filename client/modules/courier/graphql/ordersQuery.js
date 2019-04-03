import { gql } from "apollo-boost";

const ORDERS_QUERY = gql`
  query orders($id: ID!) {
    orders(id: $id, count: 10) {
      data {
        id
        number
        deliveryDate
        total
        firstName
        lastName
        phone
        address
      }
    }
  }
`;

export default ORDERS_QUERY;
