import React from "react";
import { useQuery } from "react-apollo-hooks";
import { Route, Redirect } from "react-router-dom";

import { ME_QUERY } from "../graphql/queries";

const PrivateRoute = ({ inverted, component: Component, ...rest }) => {
  const { data, loading, error } = useQuery(ME_QUERY);

  if (loading) return null;
  const loggedIn = !error && !!data.me;
  const authorized = inverted ? !loggedIn : loggedIn;

  return (
    <Route
      {...rest}
      render={props =>
        authorized ? (
          <Component {...props} />
        ) : (
          <Redirect
            to={{
              pathname: inverted ? "/" : "/login",
              state: { from: props.location }
            }}
          />
        )
      }
    />
  );
};

export default PrivateRoute;
