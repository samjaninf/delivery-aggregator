import React from "react";
import { Route } from "react-router-dom";

import ProtectedRoute from "./PrivateRoute";
import App from "../pages/App";
import Login from "../pages/Login";

const Routes = () => (
  <div>
    <Route path="/login" component={Login} />
    <ProtectedRoute path="/" exact component={App} />
  </div>
);

export default Routes;
