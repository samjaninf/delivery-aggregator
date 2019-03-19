import React, { useState } from "react";
import { useMutation } from "react-apollo-hooks";
import { gql } from "apollo-boost";

const LOGIN_MUTATION = gql`
  mutation login($username: String!, $password: String!) {
    login(data: { username: $username, password: $password })
  }
`;

const Login = () => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const login = useMutation(LOGIN_MUTATION, {
    variables: { username: email, password }
  });

  return (
    <form>
      <input
        type="text"
        placeholder="email"
        value={email}
        onChange={e => setEmail(e.target.value)}
      />
      <input
        type="text"
        placeholder="password"
        value={password}
        onChange={e => setPassword(e.target.value)}
      />
      <button type="button" onClick={login}>
        Login
      </button>
    </form>
  );
};

export default Login;
