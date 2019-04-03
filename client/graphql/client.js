import { ApolloClient, HttpLink, InMemoryCache } from "apollo-boost";
import { Constants } from "expo";

const { manifest } = Constants;
const uri =
  typeof manifest.packagerOpts === "object" && manifest.packagerOpts.dev
    ? `http://${manifest.debuggerHost.split(":").shift()}/graphql`
    : `https://deliveryaggregator.com/graphql`;

const link = new HttpLink({
  uri,
  credentials: "include"
});

export default new ApolloClient({
  link,
  cache: new InMemoryCache()
});
