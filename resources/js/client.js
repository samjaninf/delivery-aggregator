import { ApolloClient } from "apollo-client";
import { HttpLink } from "apollo-link-http";
import { InMemoryCache } from "apollo-cache-inmemory";

const link = new HttpLink({
  uri: "/graphql",
  credentials: "same-origin"
});

export default new ApolloClient({
  link,
  cache: new InMemoryCache(),
  credentials: "same-origin"
});
