import { gql } from "apollo-boost";

const LOGIN_MUTATION = gql`
  mutation login($email: String!, $password: String!) {
    login(input: { username: $email, password: $password }) {
      email
    }
  }
`;

export default LOGIN_MUTATION;
