import { gql } from "apollo-boost";

const ME_QUERY = gql`
  {
    me {
      email
    }
  }
`;

export default ME_QUERY;
