import { ApolloClient, HttpLink, InMemoryCache } from "apollo-boost";

const link = new HttpLink({
  uri: "/graphql",
  credentials: "include"
});

export default new ApolloClient({
  link,
  cache: new InMemoryCache()
});
