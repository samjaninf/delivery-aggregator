import { gql } from "apollo-boost";

export const LOGIN_MUTATION = gql`
  mutation login($username: String!, $password: String!) {
    login(data: { username: $username, password: $password }) {
      email
    }
  }
`;

export const LOGOUT_MUTATION = gql`
  mutation logout {
    logout {
      status
      message
    }
  }
`;
