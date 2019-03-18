import React from "react";
import ReactDOM from "react-dom";
import { ApolloProvider } from "react-apollo-hooks";
import { BrowserRouter } from "react-router-dom";

import "../sass/app.scss";
import client from "./client";
import Routes from "./routes/Routes";

ReactDOM.render(
  <BrowserRouter>
    <ApolloProvider client={client}>
      <Routes />
    </ApolloProvider>
  </BrowserRouter>,
  document.getElementById("app")
);
