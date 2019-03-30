import { useEffect } from "react";
import { LOGOUT_MUTATION } from "../graphql/mutations";
import { ME_QUERY } from "../graphql/queries";
import { useMutation, useApolloClient } from "react-apollo-hooks";

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
      await client.clearStore();
    }

    doLogout();
  }, []);

  return null;
};

export default Logout;
