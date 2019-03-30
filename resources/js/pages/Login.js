import React, { useState } from "react";
import { withRouter } from "react-router-dom";

import { useMutation } from "react-apollo-hooks";
import { LOGIN_MUTATION } from "../graphql/mutations";
import { ME_QUERY } from "../graphql/queries";

const Login = () => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  const login = useMutation(LOGIN_MUTATION, {
    variables: { username: email, password },
    refetchQueries: [
      {
        query: ME_QUERY
      }
    ],
    awaitRefetchQueries: true
  });

  return (
    <form
      onSubmit={e => {
        e.preventDefault();
        login();
      }}
    >
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
      <button type="submit">Login</button>
    </form>
  );
};

export default withRouter(Login);
