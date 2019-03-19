import React from "react";
import { gql } from "apollo-boost";
import { useQuery } from "react-apollo-hooks";
import { Route, Redirect } from "react-router-dom";

const ME_QUERY = gql`
  {
    me {
      email
    }
  }
`;

const PrivateRoute = ({ component: Component, ...rest }) => {
  const { data, loading, error } = useQuery(ME_QUERY);

  if (loading) return null;

  return (
    <Route
      {...rest}
      render={props =>
        !error && data.me.email ? (
          <Component {...props} />
        ) : (
          <Redirect
            to={{
              pathname: "/login",
              state: { from: props.location }
            }}
          />
        )
      }
    />
  );
};

export default PrivateRoute;
