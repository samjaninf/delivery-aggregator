import { gql } from "apollo-boost";

export const ME_QUERY = gql`
  {
    me {
      email
    }
  }
`;

export const ORDERS_QUERY = gql`
  {
    orders(id: 1, count: 10) {
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
