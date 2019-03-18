import React, { useState } from "react";

const Login = () => {
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");

  return (
    <form>
      <input
        type="text"
        placeholder="email"
        value={email}
        onChange={setEmail}
      />
      <input
        type="text"
        placeholder="password"
        value={password}
        onChange={setPassword}
      />
      <button>Login</button>
    </form>
  );
};

export default Login;
