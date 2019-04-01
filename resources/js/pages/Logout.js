import { useEffect } from "react";
import { useMutation, useApolloClient } from "react-apollo-hooks";
import { LOGOUT_MUTATION } from "../graphql/mutations";
import { ME_QUERY } from "../graphql/queries";

const Logout = () => {
  const client = useApolloClient();
  const logout = useMutation(LOGOUT_MUTATION, {
    refetchQueries: [
      {
        query: ME_QUERY
      }
    ],
    awaitRefetchQueries: true
  });

  useEffect(() => {
    async function doLogout() {
      await logout();
      await client.cache.reset();
    }

    doLogout();
  }, []);

  return null;
};

export default Logout;
