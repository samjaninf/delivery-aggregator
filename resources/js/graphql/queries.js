import { gql } from "apollo-boost";

export const ME_QUERY = gql`
  {
    me {
      email
    }
  }
`;
