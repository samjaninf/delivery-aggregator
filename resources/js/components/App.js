import React from "react";
import { ApolloProvider } from "react-apollo-hooks";

import { ApolloClient } from "apollo-client";
import { HttpLink } from "apollo-link-http";
import { InMemoryCache } from "apollo-cache-inmemory";

import Layout from "./Layout";

const client = new ApolloClient({
  link: new HttpLink(),
  cache: new InMemoryCache()
});

const App = () => (
  <ApolloProvider client={client}>
    <Layout />
  </ApolloProvider>
);

export default App;
